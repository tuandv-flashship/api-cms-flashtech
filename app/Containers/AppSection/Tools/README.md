### Tools Container

Container path: `app/Containers/AppSection/Tools`

### Scope

- Data synchronization helpers for import/export workflows.
- Upload validation and example file download endpoints.
- Schema metadata endpoints for FE form rendering.

### API Routes

Route files: `app/Containers/AppSection/Tools/UI/API/Routes`

#### Schema & Discovery

| Method | URI | Description |
|---|---|---|
| GET | `/v1/tools/data-synchronize/types` | List all available types with labels and total counts |
| GET | `/v1/tools/data-synchronize/schema/{type}` | Full metadata (columns, filters, formats) for a type |

#### Export

| Method | URI | Permission |
|---|---|---|
| POST | `/v1/tools/data-synchronize/export/posts` | `posts.export` |
| POST | `/v1/tools/data-synchronize/export/pages` | `pages.export` |
| POST | `/v1/tools/data-synchronize/export/translations/model` | `post-translations.export` |
| POST | `/v1/tools/data-synchronize/export/translations/page` | `page-translations.export` |
| POST | `/v1/tools/data-synchronize/export/other-translations` | `other-translations.export` |

#### Import

| Method | URI | Permission |
|---|---|---|
| POST | `/v1/tools/data-synchronize/import/posts` | `posts.import` |
| POST | `/v1/tools/data-synchronize/import/pages` | `pages.import` |
| POST | `/v1/tools/data-synchronize/import/translations/model` | `post-translations.import` |
| POST | `/v1/tools/data-synchronize/import/translations/page` | `page-translations.import` |
| POST | `/v1/tools/data-synchronize/import/other-translations` | `other-translations.import` |

#### Validate & Example

Each import type has validate and download-example sub-routes:
- `POST .../validate` — validate file before import
- `POST .../download-example` — download example template

#### Upload

| Method | URI | Description |
|---|---|---|
| POST | `/v1/tools/data-synchronize/upload` | Upload file for import |

### Auth Notes

- All routes require `auth:api` middleware.
- Export/import routes require specific permissions (see table above).
- Schema/types endpoints require authentication only (no additional permissions).

### Main Config

- `app/Containers/AppSection/Tools/Configs/data-synchronize.php` — formats, chunk sizes
- `app/Containers/AppSection/Tools/Configs/permissions.php` — 10 permissions

### Architecture

- **Exporters** in `Exporters/` — define columns, rows, total count, filter schema
- **Importers** in `Importers/` — define columns, validation, examples, handle logic
- **Shared traits** in `Supports/Concerns/`:
  - `TranslationLocaleHelper` — locale/language helpers for translation exporters/importers
  - `ImportNormalizationHelper` — string/status normalization for importers
- **Registry** in `Supports/DataSynchronizeRegistry.php` — map-based type resolution

### Tests

- `Tests/Functional/API/DataSynchronizeTest.php` — auth, permissions, export, import, validate, schema

### Change Log

- `2026-02-07`: Added container README.
- `2026-03-18`: Added Pages and Page Translations export/import. Refactored registry to map-based. Added schema metadata endpoints. Optimized with cursor exports, shared traits, N+1 batch fix.
