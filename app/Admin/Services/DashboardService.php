<?php

declare(strict_types=1);

namespace App\Admin\Services;

use App\Admin\Repositories\DashboardRepository;
use App\Compliance\ComplianceRepository;
use App\Induction\InductionRepository;
use DateTimeImmutable;

class DashboardService
{
    private const EXPIRING_SOON_DAYS = 30;
    private const EXPIRING_SOON_LIMIT = 10;
    private const ISSUED_QUARTERS = 16;
    private const FORECAST_QUARTERS = 4;
    private const BREAKDOWN_ROWS = 9;

    private DashboardRepository $dashboard;

    public function __construct()
    {
        $this->dashboard = new DashboardRepository();
    }

    /**
     * @return array<string, mixed>
     */
    public function metrics(): array
    {
        (new ComplianceRepository())->expireLapsed();

        return [
            'total_inductees' => $this->dashboard->countActiveInductees(),
            'active_inductions' => $this->dashboard->countActiveInductions(),
            'expiring_soon' => $this->dashboard->countExpiringSoon(self::EXPIRING_SOON_DAYS),
            'expired' => $this->dashboard->countComplianceByStatus('expired'),
            'expiring_soon_days' => self::EXPIRING_SOON_DAYS,
            'expiring_soon_list' => $this->dashboard->expiringSoon(self::EXPIRING_SOON_DAYS, self::EXPIRING_SOON_LIMIT),
            'induction_breakdown' => $this->inductionBreakdown(),
            'issued_per_quarter' => $this->issuedPerQuarter(),
            'expiring_per_quarter' => $this->expiringPerQuarter(),
            'inductee_breakdowns' => [
                'employment_type' => $this->inducteeBreakdown('employment_type', 'Other types'),
                'company' => $this->inducteeBreakdown('company', 'Other companies'),
            ],
        ];
    }

    /**
     * @return array<int, array{label: string, count: int}> the quarters of the last 16 (this one included) with records issued
     */
    private function issuedPerQuarter(): array
    {
        $from = $this->quarterStart(1 - self::ISSUED_QUARTERS);
        $counts = $this->dashboard->countIssuedPerQuarter($from->format('Y-m-d'));
        $quarters = $this->quarters($from, self::ISSUED_QUARTERS, $counts);

        return array_values(array_filter($quarters, fn (array $quarter): bool => $quarter['count'] > 0));
    }

    /**
     * @return array<int, array{label: string, count: int}> this quarter (from today) and the next 3
     */
    private function expiringPerQuarter(): array
    {
        $before = $this->quarterStart(self::FORECAST_QUARTERS);
        $counts = $this->dashboard->countActiveExpiringPerQuarter($before->format('Y-m-d'));

        return $this->quarters($this->quarterStart(0), self::FORECAST_QUARTERS, $counts);
    }

    /** First day of the quarter $offset quarters from the current one. */
    private function quarterStart(int $offset): DateTimeImmutable
    {
        $today = new DateTimeImmutable('today');
        $month = intdiv((int) $today->format('n') - 1, 3) * 3 + 1;

        return $today->setDate((int) $today->format('Y'), $month, 1)->modify(sprintf('%+d months', $offset * 3));
    }

    /**
     * @param array<string, int> $counts keyed 'YYYY-Q'
     * @return array<int, array{label: string, count: int}>
     */
    private function quarters(DateTimeImmutable $from, int $count, array $counts): array
    {
        $quarters = [];
        for ($i = 0; $i < $count; $i++) {
            $start = $from->modify(sprintf('+%d months', $i * 3));
            $year = $start->format('Y');
            $quarter = intdiv((int) $start->format('n') - 1, 3) + 1;

            $quarters[] = ['label' => "Q{$quarter} {$year}", 'count' => $counts["{$year}-{$quarter}"] ?? 0];
        }

        return $quarters;
    }

    /**
     * Active inductees per value of a profile field, largest first. Past
     * BREAKDOWN_ROWS values, the smallest fold into one $restLabel row.
     *
     * @return array{rows: array<int, array{label: string, count: int, share: int}>, not_set: int}
     */
    private function inducteeBreakdown(string $field, string $restLabel): array
    {
        $counts = $this->dashboard->countActiveInducteesByProfileField($field);
        $notSet = $counts[''] ?? 0;
        unset($counts['']);
        arsort($counts);
        $total = array_sum($counts);

        if (count($counts) > self::BREAKDOWN_ROWS) {
            $top = array_slice($counts, 0, self::BREAKDOWN_ROWS - 1, true);
            $top[$restLabel] = $total - array_sum($top);
            $counts = $top;
        }

        $rows = [];
        foreach ($counts as $label => $count) {
            $rows[] = [
                'label' => (string) $label,
                'count' => $count,
                'share' => (int) round($count / $total * 100),
            ];
        }

        return ['rows' => $rows, 'not_set' => $notSet];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function inductionBreakdown(): array
    {
        $inductions = (new InductionRepository())->all('active');
        $counts = $this->dashboard->perInductionComplianceCounts();

        $rows = [];
        foreach ($inductions as $induction) {
            $inductionCounts = $counts[(int) $induction['id']] ?? ['active' => 0, 'expired' => 0];
            $total = $inductionCounts['active'] + $inductionCounts['expired'];

            $rows[] = [
                'title' => $induction['title'],
                'code' => $induction['code'],
                'compliant' => $inductionCounts['active'],
                'expired' => $inductionCounts['expired'],
                'rate' => $total > 0 ? round(($inductionCounts['active'] / $total) * 100) : null,
            ];
        }

        return $rows;
    }
}
