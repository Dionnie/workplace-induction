<?php

declare(strict_types=1);

namespace App\MediaLibrary;

class MediaLibraryService
{
    /**
     * Image types an administrator is allowed to enable in settings. SVG is
     * deliberately excluded -- it can embed scripts and would be served
     * back from the same origin.
     */
    private const AVAILABLE_TYPES = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    private const MIN_FILE_SIZE_MB = 1;
    private const MAX_FILE_SIZE_MB = 50;

    private const UPLOAD_DIR = __DIR__ . '/../../assets/uploads/media-library/';
    private const UPLOAD_URL = '/assets/uploads/media-library/';

    private MediaCategoryRepository $categories;
    private MediaItemRepository $items;
    private MediaSettingsRepository $settings;

    public function __construct()
    {
        $this->categories = new MediaCategoryRepository();
        $this->items = new MediaItemRepository();
        $this->settings = new MediaSettingsRepository();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listCategories(): array
    {
        return $this->categories->all();
    }

    /**
     * @return array{success: bool, errors: array<string, string>, id?: int}
     */
    public function createCategory(string $name): array
    {
        $name = trim($name);

        if ($name === '') {
            return ['success' => false, 'errors' => ['name' => 'Category name is required.']];
        }

        if (mb_strlen($name) > 100) {
            return ['success' => false, 'errors' => ['name' => 'Category name must be 100 characters or fewer.']];
        }

        if ($this->categories->findByName($name)) {
            return ['success' => false, 'errors' => ['name' => 'A category with this name already exists.']];
        }

        $id = $this->categories->create($name);
        return ['success' => true, 'errors' => [], 'id' => $id];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function renameCategory(int $id, string $name): array
    {
        if (!$this->categories->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Category not found.']];
        }

        $name = trim($name);

        if ($name === '') {
            return ['success' => false, 'errors' => ['name' => 'Category name is required.']];
        }

        if (mb_strlen($name) > 100) {
            return ['success' => false, 'errors' => ['name' => 'Category name must be 100 characters or fewer.']];
        }

        $existing = $this->categories->findByName($name);
        if ($existing && (int) $existing['id'] !== $id) {
            return ['success' => false, 'errors' => ['name' => 'A category with this name already exists.']];
        }

        $this->categories->rename($id, $name);
        return ['success' => true, 'errors' => []];
    }

    /**
     * Deleting a category never deletes its files -- they just become
     * uncategorized (enforced by the media_items foreign key).
     *
     * @return array{success: bool, errors: array<string, string>}
     */
    public function deleteCategory(int $id): array
    {
        if (!$this->categories->find($id)) {
            return ['success' => false, 'errors' => ['form' => 'Category not found.']];
        }

        $this->categories->delete($id);
        return ['success' => true, 'errors' => []];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listItems(?int $categoryId = null, ?string $search = null): array
    {
        return $this->items->all($categoryId, $search);
    }

    public function settings(): array
    {
        return $this->settings->get();
    }

    /**
     * @return array{extensions: array<int, string>}
     */
    public function availableTypes(): array
    {
        return ['extensions' => array_keys(self::AVAILABLE_TYPES)];
    }

    /**
     * @param array<int, string> $data
     * @return array{success: bool, errors: array<string, string>}
     */
    public function updateSettings(array $data): array
    {
        $errors = [];

        $maxFileSizeMb = $data['max_file_size_mb'] ?? '';
        if (!ctype_digit((string) $maxFileSizeMb)
            || (int) $maxFileSizeMb < self::MIN_FILE_SIZE_MB
            || (int) $maxFileSizeMb > self::MAX_FILE_SIZE_MB
        ) {
            $errors['max_file_size_mb'] = sprintf(
                'Enter a max file size between %d and %d MB.',
                self::MIN_FILE_SIZE_MB,
                self::MAX_FILE_SIZE_MB
            );
        }

        $allowedTypes = array_values(array_intersect(
            array_map('strtolower', (array) ($data['allowed_types'] ?? [])),
            array_keys(self::AVAILABLE_TYPES)
        ));

        if (empty($allowedTypes)) {
            $errors['allowed_types'] = 'Select at least one allowed file type.';
        }

        if ($errors) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->settings->update([
            'max_file_size_mb' => (int) $maxFileSizeMb,
            'allowed_types' => implode(',', $allowedTypes),
        ]);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Validates and stores a single uploaded image, then records it against
     * the given category (or uncategorized when null).
     *
     * @param array<string, mixed> $file one entry of $_FILES
     * @return array{success: bool, errors: array<string, string>, item?: array<string, mixed>}
     */
    public function upload(array $file, ?int $categoryId): array
    {
        if ($categoryId !== null && !$this->categories->find($categoryId)) {
            return ['success' => false, 'errors' => ['category_id' => 'Selected category was not found.']];
        }

        if (empty($file) || !isset($file['error'])) {
            return ['success' => false, 'errors' => ['file' => 'No file was uploaded.']];
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'errors' => ['file' => $this->uploadErrorMessage((int) $file['error'])]];
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return ['success' => false, 'errors' => ['file' => 'Invalid upload.']];
        }

        $settings = $this->settings->get();
        $allowedTypes = array_filter(array_map('trim', explode(',', (string) $settings['allowed_types'])));
        $maxBytes = (int) $settings['max_file_size_mb'] * 1024 * 1024;

        $originalName = (string) $file['name'];
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));

        if ($extension === '' || !in_array($extension, $allowedTypes, true) || !isset(self::AVAILABLE_TYPES[$extension])) {
            return ['success' => false, 'errors' => ['file' => sprintf(
                '"%s" is not an allowed file type. Allowed types: %s.',
                $originalName,
                implode(', ', $allowedTypes)
            )]];
        }

        if ((int) $file['size'] <= 0 || (int) $file['size'] > $maxBytes) {
            return ['success' => false, 'errors' => ['file' => sprintf(
                '"%s" exceeds the maximum file size of %d MB.',
                $originalName,
                (int) $settings['max_file_size_mb']
            )]];
        }

        $imageInfo = @getimagesize($file['tmp_name']);
        $expectedMime = self::AVAILABLE_TYPES[$extension];
        if ($imageInfo === false || $imageInfo['mime'] !== $expectedMime) {
            return ['success' => false, 'errors' => ['file' => sprintf(
                '"%s" is not a valid image file.',
                $originalName
            )]];
        }

        if (!is_dir(self::UPLOAD_DIR) && !mkdir(self::UPLOAD_DIR, 0755, true) && !is_dir(self::UPLOAD_DIR)) {
            return ['success' => false, 'errors' => ['file' => 'Unable to prepare the upload directory.']];
        }

        $storedFilename = bin2hex(random_bytes(16)) . '.' . $extension;
        $destination = self::UPLOAD_DIR . $storedFilename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => false, 'errors' => ['file' => 'Unable to save the uploaded file.']];
        }

        $id = $this->items->create([
            'category_id' => $categoryId,
            'filename' => $storedFilename,
            'original_filename' => $originalName,
            'mime_type' => $imageInfo['mime'],
            'size' => (int) $file['size'],
        ]);

        $item = $this->items->find($id);

        return [
            'success' => true,
            'errors' => [],
            'item' => [
                'id' => $item['id'],
                'category_id' => $item['category_id'],
                'original_filename' => $item['original_filename'],
                'mime_type' => $item['mime_type'],
                'size' => (int) $item['size'],
                'url' => self::UPLOAD_URL . $item['filename'],
                'created_at' => $item['created_at'],
            ],
        ];
    }

    /**
     * Moves a batch of items into a category (or uncategorizes them when
     * $categoryId is null) in one query.
     *
     * @param array<int, mixed> $ids
     * @return array{success: bool, errors: array<string, string>, moved: int}
     */
    public function moveItems(array $ids, ?int $categoryId): array
    {
        if ($categoryId !== null && !$this->categories->find($categoryId)) {
            return ['success' => false, 'errors' => ['category_id' => 'Selected category was not found.'], 'moved' => 0];
        }

        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            fn (int $id): bool => $id > 0
        )));

        if (empty($ids)) {
            return ['success' => false, 'errors' => ['form' => 'Select at least one file to move.'], 'moved' => 0];
        }

        $moved = $this->items->updateCategoryForIds($ids, $categoryId);

        return ['success' => true, 'errors' => [], 'moved' => $moved];
    }

    /**
     * @return array{success: bool, errors: array<string, string>}
     */
    public function deleteItem(int $id): array
    {
        $item = $this->items->find($id);
        if (!$item) {
            return ['success' => false, 'errors' => ['form' => 'File not found.']];
        }

        $path = self::UPLOAD_DIR . $item['filename'];
        if (is_file($path)) {
            unlink($path);
        }

        $this->items->delete($id);

        return ['success' => true, 'errors' => []];
    }

    /**
     * Deletes a batch of items, removing each one's file from disk too.
     *
     * @param array<int, mixed> $ids
     * @return array{success: bool, errors: array<string, string>, deleted: int}
     */
    public function deleteItems(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(
            array_map('intval', $ids),
            fn (int $id): bool => $id > 0
        )));

        if (empty($ids)) {
            return ['success' => false, 'errors' => ['form' => 'Select at least one file to delete.'], 'deleted' => 0];
        }

        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->deleteItem($id)['success']) {
                $deleted++;
            }
        }

        return ['success' => true, 'errors' => [], 'deleted' => $deleted];
    }

    private function uploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The file exceeds the maximum upload size.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            default => 'The file could not be uploaded.',
        };
    }
}
