### Tools Container

Container path: `app/Containers/AppSection/Tools`

### Scope

- Data synchronization helpers for import/export workflows.
- Upload validation and example file download endpoints.
- Schema metadata endpoints for FE form rendering.

### API Routes

Route files: `app/Containers/AppSection/Tools/UI/API/Routes`

All data-synchronize routes use a uniform `{type}` parameter:
`posts` | `pages` | `post-translations` | `page-translations` | `other-translations`

#### Schema & Discovery

| Method | URI | Description |
|---|---|---|
| GET | `/v1/tools/data-synchronize/types` | List all types with labels, total, descriptions |
| GET | `/v1/tools/data-synchronize/schema/{type}` | Full metadata (columns, filters, formats, i18n) |

#### Export

| Method | URI | Permission |
|---|---|---|
| POST | `/v1/tools/data-synchronize/export/{type}` | `{type}.export` |

#### Import

| Method | URI | Permission |
|---|---|---|
| POST | `/v1/tools/data-synchronize/import/{type}` | `{type}.import` |
| POST | `/v1/tools/data-synchronize/import/{type}/validate` | `{type}.import` |
| POST | `/v1/tools/data-synchronize/import/{type}/download-example` | `{type}.import` |

#### Upload

| Method | URI | Description |
|---|---|---|
| POST | `/v1/tools/data-synchronize/upload` | Upload file for import |

### Auth Notes

- All routes require `auth:api` middleware.
- Export/import routes require specific permissions (see table above).
- Schema/types endpoints require authentication only (no specific permissions).

### i18n Support

Responses support `X-Locale` header through `lang/{locale}/data-synchronize.php` translation files.
Labels, descriptions, columns, filters, and rule descriptions are all translated.

### Main Config

- `app/Containers/AppSection/Tools/Configs/data-synchronize.php` — formats, chunk sizes
- `app/Containers/AppSection/Tools/Configs/permissions.php` — 10 permissions

### Architecture

- **Generic Controllers** — 4 controllers handle all types via `{type}` route parameter
- **Exporters** in `Exporters/` — define columns, rows, total count, filter schema
- **Importers** in `Importers/` — define columns, validation, examples, handle logic
- **Shared traits** in `Supports/Concerns/`:
  - `TranslationLocaleHelper` — locale helpers for translation types
  - `ImportNormalizationHelper` — string/status normalization
- **Registry** in `Supports/DataSynchronizeRegistry.php` — type resolution + permission mapping

### Tests

- `Tests/Functional/API/DataSynchronizeTest.php` — 25 tests, 76 assertions

### Change Log

- `2026-02-07`: Added container README.
- `2026-03-18`: Refactored to generic controllers with `{type}` route parameter. Added i18n support. Added schema/types metadata endpoints. Optimized with cursor exports, shared traits, N+1 batch fix.
