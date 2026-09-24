<?php

declare(strict_types=1);

namespace App\Tools;

use App\Core\Database;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Bulk text search & replace, dynamically scoped to every table in this
 * database -- built for maintenance tasks such as rewriting hardcoded
 * absolute URLs after moving the app to a new domain, not as a general
 * data editor for every column.
 *
 * Tables and their text-like columns are discovered live via
 * information_schema (never hardcoded), so any table added to the schema
 * later is automatically searchable with no code change here. Two automatic
 * safety nets apply no matter what the admin selects, and cannot be bypassed
 * from the UI:
 *
 * 1. Type filtering: only genuinely text-typed columns (char/varchar/*text/
 *    json) are ever touched. Numeric, date, and enum columns are never
 *    candidates for substring replacement.
 * 2. PROTECTED_COLUMNS: a small, explicit denylist for the handful of
 *    text-typed columns that are still unsafe to touch -- either because
 *    they are security-critical (password hashes, verification/reset
 *    tokens, certificate numbers) or because they are tightly coupled to
 *    something outside the database that a blind text edit would
 *    desynchronize from (media_items.filename is the literal name of a file
 *    on disk; editing the column without renaming the file breaks every
 *    reference to it).
 *
 * Matching/replacing is always exact, case-sensitive PHP str_replace()/
 * substr_count() (never MySQL's collation-dependent LIKE/REPLACE()), so the
 * preview the admin reviews is exactly what gets written on apply.
 *
 * Every table/column name used in a raw SQL identifier position is always
 * one this class itself just read back from information_schema for the
 * current database -- request input only ever selects *which* of those
 * already-validated names to use, so there is no identifier-injection
 * surface here even though identifiers can't be bound as PDO parameters.
 */
class SearchReplaceService
{
    /**
     * table => [column, ...] pairs that are never searched/replaced,
     * regardless of table selection. See class doc comment for why each one
     * is here.
     */
    private const PROTECTED_COLUMNS = [
        'users' => ['password', 'email_verification_token', 'password_reset_token'],
        'compliance_records' => ['verification_token', 'certificate_number', 'legacy_id'],
        'media_items' => ['filename'],
    ];

    /**
     * Only these MySQL column data types are ever treated as searchable
     * text.
     */
    private const TEXT_TYPES = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'json'];

    private const SNIPPET_RADIUS = 40;
    private const MAX_SNIPPETS_PER_ROW = 3;

    private PDO $db;
    private string $databaseName;

    public function __construct()
    {
        $this->db = Database::connection();
        $this->databaseName = (string) $this->db->query('SELECT DATABASE()')->fetchColumn();
    }

    /**
     * @return array<int, string> every table in this database, alphabetical.
     */
    public function tables(): array
    {
        $stmt = $this->db->prepare(
            "SELECT TABLE_NAME FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = :db AND TABLE_TYPE = 'BASE TABLE'
             ORDER BY TABLE_NAME"
        );
        $stmt->execute(['db' => $this->databaseName]);

        return array_map('strval', array_column($stmt->fetchAll(), 'TABLE_NAME'));
    }

    /**
     * Read-only preview: which rows contain $find, how many times, and short
     * highlighted-context snippets. Never writes to the database.
     *
     * @param array<int, string> $tables
     * @return array{
     *     tables: array<string, array{
     *         columns: array<string, array{rows: array<int, array{pk: string, value: string, count: int, snippets: array<int, array{before: string, match: string, after: string}>}>, occurrences: int}>,
     *         occurrences: int
     *     }>,
     *     totalOccurrences: int,
     *     totalRows: int
     * }
     */
    public function search(array $tables, string $find): array
    {
        $result = ['tables' => [], 'totalOccurrences' => 0, 'totalRows' => 0];

        if ($find === '') {
            return $result;
        }

        foreach ($this->resolveTables($tables) as $table) {
            $pk = $this->primaryKeyColumn($table);
            if ($pk === null) {
                continue;
            }

            $tableOccurrences = 0;
            $columns = [];

            foreach ($this->searchableColumns($table) as $column) {
                $rows = $this->matchingRows($table, $column, $pk, $find);
                if ($rows === []) {
                    continue;
                }

                $previewRows = array_map(static fn (array $row): array => [
                    'pk' => $row['pk'],
                    'value' => $row['pkValue'],
                    'count' => $row['count'],
                    'snippets' => $row['snippets'],
                ], $rows);

                $columnOccurrences = array_sum(array_column($rows, 'count'));
                $columns[$column] = ['rows' => $previewRows, 'occurrences' => $columnOccurrences];
                $tableOccurrences += $columnOccurrences;
                $result['totalRows'] += count($rows);
            }

            if ($columns !== []) {
                $result['tables'][$table] = ['columns' => $columns, 'occurrences' => $tableOccurrences];
                $result['totalOccurrences'] += $tableOccurrences;
            }
        }

        return $result;
    }

    /**
     * Applies the replacement to every matching row across every selected
     * table inside one transaction -- either everything commits or nothing
     * does. Any database rejection (a JSON column that would become invalid,
     * a UNIQUE constraint violation, or anything else) aborts the whole
     * transaction with no partial changes anywhere.
     *
     * @param array<int, string> $tables
     * @return array{success: bool, errors: array<string, string>, updated: array<string, int>, totalUpdated: int}
     */
    public function replace(array $tables, string $find, string $replace): array
    {
        $resolved = $this->resolveTables($tables);

        if ($resolved === []) {
            return ['success' => false, 'errors' => ['tables' => 'Select at least one table.'], 'updated' => [], 'totalUpdated' => 0];
        }

        if ($find === '') {
            return ['success' => false, 'errors' => ['find' => 'Enter text to find.'], 'updated' => [], 'totalUpdated' => 0];
        }

        $updated = [];
        $totalUpdated = 0;

        $this->db->beginTransaction();

        try {
            foreach ($resolved as $table) {
                $updated[$table] = 0;
                $pk = $this->primaryKeyColumn($table);
                if ($pk === null) {
                    continue;
                }

                foreach ($this->searchableColumns($table) as $column) {
                    $isJson = $this->columnType($table, $column) === 'json';

                    foreach ($this->matchingRows($table, $column, $pk, $find) as $row) {
                        $newContent = str_replace($find, $replace, $row['content']);

                        if ($isJson && !$this->isValidJson($newContent)) {
                            throw new RuntimeException(sprintf(
                                'Replacement was not applied: it would break the JSON structure in `%s`.`%s` (%s = %s). No changes were made anywhere.',
                                $table,
                                $column,
                                $pk,
                                $row['pkValue']
                            ));
                        }

                        $stmt = $this->db->prepare(sprintf(
                            'UPDATE `%s` SET `%s` = :content WHERE `%s` = :pk',
                            $table,
                            $column,
                            $pk
                        ));
                        $stmt->execute(['content' => $newContent, 'pk' => $row['pkValue']]);
                        $updated[$table]++;
                        $totalUpdated++;
                    }
                }
            }

            $this->db->commit();
        } catch (RuntimeException $e) {
            $this->db->rollBack();
            return ['success' => false, 'errors' => ['form' => $e->getMessage()], 'updated' => [], 'totalUpdated' => 0];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'errors' => [
                'form' => 'Replacement was not applied because the database rejected a change (e.g. a uniqueness constraint). No changes were made anywhere.',
            ], 'updated' => [], 'totalUpdated' => 0];
        }

        return ['success' => true, 'errors' => [], 'updated' => $updated, 'totalUpdated' => $totalUpdated];
    }

    /**
     * @param array<int, string> $tables
     * @return array<int, string> only the requested names that are actually
     *     real tables in this database right now.
     */
    private function resolveTables(array $tables): array
    {
        $valid = $this->tables();
        return array_values(array_intersect($tables, $valid));
    }

    /**
     * @return string|null the primary key column name, or null if the table
     *     has none (nothing safely updatable without one).
     */
    private function primaryKeyColumn(string $table): ?string
    {
        if (!in_array($table, $this->tables(), true)) {
            return null;
        }

        $stmt = $this->db->query(sprintf("SHOW KEYS FROM `%s` WHERE Key_name = 'PRIMARY'", $table));
        $row = $stmt->fetch();

        return $row ? (string) $row['Column_name'] : null;
    }

    /**
     * @return array<int, string> searchable (text-typed, not protected)
     *     column names for a table, in schema order.
     */
    private function searchableColumns(string $table): array
    {
        if (!in_array($table, $this->tables(), true)) {
            return [];
        }

        $stmt = $this->db->prepare(
            'SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table
             ORDER BY ORDINAL_POSITION'
        );
        $stmt->execute(['db' => $this->databaseName, 'table' => $table]);

        $protected = self::PROTECTED_COLUMNS[$table] ?? [];
        $columns = [];

        foreach ($stmt->fetchAll() as $col) {
            $name = (string) $col['COLUMN_NAME'];
            if (in_array($name, $protected, true)) {
                continue;
            }
            if (!in_array(strtolower((string) $col['DATA_TYPE']), self::TEXT_TYPES, true)) {
                continue;
            }
            $columns[] = $name;
        }

        return $columns;
    }

    private function columnType(string $table, string $column): ?string
    {
        $stmt = $this->db->prepare(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table AND COLUMN_NAME = :column'
        );
        $stmt->execute(['db' => $this->databaseName, 'table' => $table, 'column' => $column]);
        $type = $stmt->fetchColumn();

        return $type !== false ? strtolower((string) $type) : null;
    }

    /**
     * Fetches every row of one column (this app's realistic scale makes a
     * full scan fine -- no WHERE/pagination needed) and keeps only the ones
     * that actually contain $find as an exact substring.
     *
     * @return array<int, array{pk: string, pkValue: string, content: string, count: int, snippets: array<int, array{before: string, match: string, after: string}>}>
     */
    private function matchingRows(string $table, string $column, string $pk, string $find): array
    {
        $sql = sprintf('SELECT `%s` AS pk_value, `%s` AS content FROM `%s`', $pk, $column, $table);

        $rows = [];
        foreach ($this->db->query($sql)->fetchAll() as $row) {
            $content = (string) $row['content'];
            $count = substr_count($content, $find);
            if ($count === 0) {
                continue;
            }

            $rows[] = [
                'pk' => $pk,
                'pkValue' => (string) $row['pk_value'],
                'content' => $content,
                'count' => $count,
                'snippets' => $this->buildSnippets($content, $find),
            ];
        }

        return $rows;
    }

    /**
     * Splits each match into before/match/after pieces (rather than one
     * flattened string) so the view can escape each piece independently and
     * safely wrap only the "match" piece in a highlight, even if $find
     * itself contains HTML-special characters.
     *
     * @return array<int, array{before: string, match: string, after: string}>
     */
    private function buildSnippets(string $content, string $find): array
    {
        $snippets = [];
        $offset = 0;
        $findLen = strlen($find);
        $contentLen = strlen($content);

        while (count($snippets) < self::MAX_SNIPPETS_PER_ROW) {
            $pos = strpos($content, $find, $offset);
            if ($pos === false) {
                break;
            }

            $start = max(0, $pos - self::SNIPPET_RADIUS);
            $end = min($contentLen, $pos + $findLen + self::SNIPPET_RADIUS);

            $snippets[] = [
                'before' => ($start > 0 ? '…' : '') . substr($content, $start, $pos - $start),
                'match' => substr($content, $pos, $findLen),
                'after' => substr($content, $pos + $findLen, $end - ($pos + $findLen)) . ($end < $contentLen ? '…' : ''),
            ];

            $offset = $pos + $findLen;
        }

        return $snippets;
    }

    private function isValidJson(string $value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
