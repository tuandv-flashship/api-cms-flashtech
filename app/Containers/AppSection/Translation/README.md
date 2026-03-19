# Translation Container

> Container path: `app/Containers/AppSection/Translation`

## Scope

Quản lý **hệ thống bản dịch UI** (validation messages, labels, etc.) cho cả server-side Laravel và client-side NextJS FE.

### Core Features

- **Hybrid DB + File**: DB translations override file translations, file là fallback
- **Public API**: NextJS FE lấy tất cả translations qua REST API (không cần auth)
- **Admin CRUD**: Quản lý groups, locales, JSON translations qua admin API
- **Server-side pagination + search**: Bảng dịch hỗ trợ phân trang & tìm kiếm server-side
- **Import Command**: Import từ local `lang/` files hoặc `botble/translations` GitHub repo
- **HTTP Cache**: ETag + Cache-Control cho public API, `Cache::rememberForever` cho DB queries

## Architecture

```
Request + Header "X-Locale: vi"
    ↓
SetLocaleFromHeader middleware (Language container)
    → App::setLocale('vi')
    ↓
trans('validation.required')
    ↓
TranslationLoaderManager (extends FileLoader)
    → 1. Load from file: lang/vi/validation.php
    → 2. Load from DB:   translations table WHERE locale='vi', group_key='validation'
    → 3. Merge: DB overrides file (array_replace_recursive)
    ↓
"Trường này không được bỏ trống."
```

## API Routes

### Public (no auth, for NextJS FE)

| Method | URI                                 | Description                                  |
| ------ | ----------------------------------- | -------------------------------------------- |
| `GET`  | `/v1/translations/{locale}`         | All translations cho 1 locale (FE bootstrap) |
| `GET`  | `/v1/translations/{locale}/{group}` | Single group translations                    |

**Response** `GET /v1/translations/vi`:

```json
{
    "data": {
        "validation": { "required": "Trường này không được bỏ trống." },
        "blog": { "post.title": "Tiêu đề bài viết" },
        "*": { "Hello": "Xin chào" }
    },
    "meta": { "locale": "vi", "total_keys": 487 }
}
```

**Headers**: `Cache-Control: public, max-age=300` + `ETag`

### Private (auth + permission required)

| Method   | URI                                          | Permission              | Description                                  |
| -------- | -------------------------------------------- | ----------------------- | -------------------------------------------- |
| `GET`    | `/v1/translations/locales`                   | `translations.index`    | List installed + available locales            |
| `POST`   | `/v1/translations/locales`                   | `translations.create`   | Install new locale                            |
| `DELETE` | `/v1/translations/locales/{locale}`          | `translations.destroy`  | Remove locale                                 |
| `GET`    | `/v1/translations/locales/{locale}/download` | `translations.download` | Download locale archive                       |
| `GET`    | `/v1/translations/{locale}/groups`           | `translations.index`    | List translation groups                       |
| `GET`    | `/v1/translations/{locale}/group`            | `translations.index`    | Get group translations (paginated + search)   |
| `PATCH`  | `/v1/translations/{locale}/group`            | `translations.edit`     | Update group (file + DB)                      |
| `PATCH`  | `/v1/translations/{locale}/json`             | `translations.edit`     | Update JSON translations (file + DB)          |

### Get Group Translations (Paginated + Search)

```
GET /v1/translations/vi/group?group=app/actions&search=accept&page=1&limit=20
```

| Query param | Type    | Required | Mô tả                                                              |
| ----------- | ------- | -------- | ------------------------------------------------------------------ |
| `group`     | string  | No       | Tên group (e.g. `app/actions`). Bỏ trống → trả tất cả groups      |
| `search`    | string  | No       | Tìm theo key, EN value, hoặc translated value                      |
| `page`      | integer | No       | Trang hiện tại (default: `1`)                                      |
| `limit`     | integer | No       | Số bản ghi/trang (default: `10`, max: `100`)                       |

**Response:**

```json
{
    "data": [
        {
            "group": "app/actions",
            "key": "Accept",
            "en": "Accept",
            "value": "Chấp nhận"
        },
        {
            "group": "app/actions",
            "key": "Add",
            "en": "Add",
            "value": "Thêm vào"
        }
    ],
    "meta": {
        "locale": "vi",
        "group": "app/actions",
        "search": "accept",
        "pagination": {
            "total": 42,
            "count": 10,
            "per_page": 10,
            "current_page": 1,
            "total_pages": 5
        }
    }
}
```

| Response field     | Type    | Mô tả                                |
| ------------------ | ------- | ------------------------------------- |
| `data[].group`     | string  | Translation group name                |
| `data[].key`       | string  | Translation key                       |
| `data[].en`        | string? | Giá trị gốc EN (null nếu chỉ có bản dịch) |
| `data[].value`     | string? | Giá trị đã dịch (null nếu chưa dịch) |
| `meta.pagination`  | object  | Format chuẩn Apiato pagination        |

## Artisan Commands

```bash
# Import local lang/ files vào DB
php artisan translations:import

# Import từ botble/translations GitHub repo
php artisan translations:import --from-repo

# Chỉ import specific locales
php artisan translations:import --locale=vi --locale=en

# Fresh import (truncate + reimport)
php artisan translations:import --fresh

# Dry run
php artisan translations:import --dry-run
```

## Key Classes

| Class                                  | Purpose                                                                                         |
| -------------------------------------- | ----------------------------------------------------------------------------------------------- |
| `Models/Translation`                   | Eloquent model, `Cache::rememberForever` per group/locale, auto-invalidation on save/delete     |
| `Supports/TranslationLoaderManager`    | Extends `FileLoader`, merges DB over file translations                                          |
| `Supports/TranslationFilesystem`       | File-based CRUD (legacy, still used for file writes)                                            |
| `Providers/TranslationServiceProvider` | Registers `TranslationLoaderManager` via `$app->extend('translator')`, registers import command |
| `Commands/ImportTranslationsCommand`   | `artisan translations:import` — local + repo import                                             |

## Database

### Table: `translations`

| Column       | Type         | Description                                |
| ------------ | ------------ | ------------------------------------------ |
| `id`         | BIGINT PK    | Auto increment                             |
| `locale`     | VARCHAR(20)  | `vi`, `en`, etc.                           |
| `group_key`  | VARCHAR(100) | `validation`, `blog`, `*` (JSON flat keys) |
| `item_key`   | VARCHAR(255) | `required`, `post.title`, etc.             |
| `value`      | TEXT         | Translated string                          |
| `created_at` | TIMESTAMP    |                                            |
| `updated_at` | TIMESTAMP    |                                            |

**Indexes**: UNIQUE(`locale`, `group_key`, `item_key`), INDEX(`locale`, `group_key`)

## Config

- `Configs/appSection-translation.php` — repo/branch for remote import
- `Configs/permissions.php` — permission definitions

## Cache Strategy

| Cache Key                                       | TTL     | Invalidation                                    |
| ----------------------------------------------- | ------- | ----------------------------------------------- |
| `translations.{locale}.{group}`                  | Forever | On `Translation` save/delete                    |
| `translations.{locale}._all`                     | Forever | On `Translation` save/delete                    |
| `translations.admin_rows.{locale}.{group}`       | 5 min   | On `UpdateTranslationGroupTask` upsert          |
| `translations.admin_rows.{locale}._all`          | 5 min   | On `UpdateTranslationGroupTask` upsert          |

## Tests

```bash
php artisan test app/Containers/AppSection/Translation/Tests/
```

**9 tests, 16 assertions** covering:

- Locale CRUD (list, create, delete, download)
- Group operations (list, get with pagination/search, update)
- Permission checks
- Route security (auth required for private endpoints)

## Change Log

- `2026-02-07`: Added container README
- `2026-03-05`: DB-driven translation system (TranslationLoaderManager, Translation model, import command, public API)
- `2026-03-05`: HTTP cache headers (ETag + Cache-Control) on public endpoints
- `2026-03-05`: Admin CRUD auto-sync to DB (UpdateTranslationGroupTask upsert)
- `2026-03-05`: Test suite (9 tests, 16 assertions)
- `2026-03-19`: Server-side pagination + search cho Get Group Translations endpoint (`?search=&page=&limit=`)
- `2026-03-19`: All-groups support (`group` optional — bỏ trống trả tất cả groups)
- `2026-03-19`: Admin rows cache (5-min TTL) with auto-invalidation on update
