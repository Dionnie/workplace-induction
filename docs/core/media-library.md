# Media Library

**Core.** Where administrators upload images and group them into categories. Other features pick images from it: content block images and galleries, exam question diagrams, and the company logo.

Admin-only: `/admin/media-library/`.

---

## 1. Data

| Table | Holds |
| --- | --- |
| `media_items` | One row per uploaded file: `filename` (the random name on disk), `original_filename` (display only), MIME type, size, optional category |
| `media_categories` | A flat list of names, unique, sorted alphabetically. No nesting. |
| `media_settings` | One row (`id = 1`): maximum file size and allowed file types |

Files are stored in `assets/uploads/media-library/<random>.<ext>` and served from `/assets/uploads/media-library/…`. A file belongs to one category or none ("Uncategorized").

---

## 2. The Page

- **Library**: grid or list view, search by filename, a category filter. Select files with their checkboxes, then **Move to Category** or **Delete Selected** (asks first). There are no delete buttons on individual files (`docs/rules/design.md` §13).
- **Upload**: drag and drop, or click to browse; several files at once. New uploads go into the category currently shown.
- **Categories**: add, rename and delete. A category row only shows its name and an Edit button; **Delete category** appears in its edit state and asks first. Deleting a category never deletes its files: they become uncategorized (the foreign key is `ON DELETE SET NULL`).
- **Upload Settings**: maximum file size (1–50 MB) and allowed types.

---

## 3. Upload Rules (`MediaLibraryService::upload()`)

A file is accepted only when:

1. its extension is enabled in Upload Settings **and** is one the code allows: `jpg`, `jpeg`, `png`, `gif`, `webp`. SVG is never allowed, since it can carry scripts and would be served from this site;
2. it is a genuine upload (`is_uploaded_file()`) within the size limit;
3. `getimagesize()` reads it as an image whose MIME type matches the extension.

It is saved under a random name. Each file in a multi-file upload is accepted or refused on its own, with its own message.

The server's PHP limits (`upload_max_filesize`, `post_max_size`) also apply. If they are lower than the Upload Settings maximum, the server refuses the file first.

---

## 4. The Picker (`assets/js/media-picker.js`)

Any admin page can open the library as a modal:

```js
MediaPicker.open({ multiple: false, title: 'Select a Logo', csrfToken: '…' })
    .then(function (items) { /* [] when cancelled */ });
```

It lists files through `admin/media-library/list.php` (GET, JSON) and can upload through `admin/media-library/upload.php` (POST, JSON) without leaving the page. Each item is `{ id, category_id, original_filename, mime_type, size, url, created_at }`.

| Used by | Selection |
| --- | --- |
| Content Blocks Studio: image block, gallery block | One / several |
| Exam Blocks Studio: question diagram | One |
| Settings → General: company logo | One |

A plain URL typed into these fields works too.

### How picked URLs are stored

- **Content blocks and exam diagrams** store the picked image as an **absolute URL** built from the browser's current address (`https://your-site/assets/uploads/media-library/…`). Moving the site to another domain leaves them pointing at the old one; rewrite them with Tools → Search & Replace (`docs/core/tools.md` §3).
- **The company logo** is stored as a root-relative path (`SiteSettingsService` strips the domain), so it survives a domain change.

---

## 5. Deleting Files

Deleting removes the row and the file on disk. It doesn't check where the file is used: a content block, exam diagram or logo pointing at it will show a broken image. Check before deleting a file that may be in use.

---

## 6. Files

| File | Role |
| --- | --- |
| `app/MediaLibrary/MediaLibraryService.php` | Uploads, categories, moves, deletes, settings |
| `app/MediaLibrary/MediaItemRepository.php`, `MediaCategoryRepository.php`, `MediaSettingsRepository.php` | SQL |
| `admin/media-library/index.php`, `views/admin/media-library/index.php` | The page |
| `admin/media-library/upload.php`, `list.php` | JSON endpoints for the page and the picker |
| `admin/media-library/bulk-move.php`, `bulk-delete.php`, `delete.php`, `settings.php`, `categories/*.php` | Actions |
| `assets/js/media-library.js`, `assets/js/media-picker.js` | Page behaviour, picker modal |
