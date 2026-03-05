# Language Container

> Container path: `app/Containers/AppSection/Language`

## Scope

Quản lý **ngôn ngữ hệ thống** — CRUD languages, set default, resolve locale, và cung cấp middleware `SetLocaleFromHeader` cho i18n.

### Core Features

- **CRUD Languages**: Create, update, delete, set default language
- **Locale Middleware**: `SetLocaleFromHeader` đọc `X-Locale` header → `App::setLocale()`
- **Locale Cache**: `LanguageLocaleCache` — long-term cache (`lang_code` ↔ `lang_locale` mapping)
- **Public + Private API**: List languages public (cho FE), CRUD private (cho admin)
- **Available/Supported Languages**: Predefined list of ~200 languages từ config

## Architecture

```
FE Request + Header "X-Locale: vi"
    ↓
SetLocaleFromHeader middleware
    → LanguageLocaleCache::resolveToLangLocale('vi')
    → Cache hit: 'vi' → 'vi' (or 'en-US' → 'en', etc.)
    → App::setLocale('vi')
    ↓
Controller / Validation / Views
    → trans() returns Vietnamese strings
```

## API Routes

### Public (no auth)

| Method | URI             | Description                       |
| ------ | --------------- | --------------------------------- |
| `GET`  | `/v1/languages` | List all languages (FE bootstrap) |

### Private (auth + permission required)

| Method   | URI                                   | Permission          | Description                    |
| -------- | ------------------------------------- | ------------------- | ------------------------------ |
| `GET`    | `/v1/languages`                       | `languages.index`   | List languages (authenticated) |
| `POST`   | `/v1/languages`                       | `languages.create`  | Create language                |
| `PATCH`  | `/v1/languages/{language_id}`         | `languages.edit`    | Update language                |
| `DELETE` | `/v1/languages/{language_id}`         | `languages.destroy` | Delete language                |
| `POST`   | `/v1/languages/{language_id}/default` | `languages.edit`    | Set default language           |
| `GET`    | `/v1/languages/current`               | `languages.index`   | Get current language           |
| `GET`    | `/v1/languages/available`             | `languages.index`   | List available locales         |
| `GET`    | `/v1/languages/supported`             | `languages.index`   | List supported languages       |

## Key Classes

| Class                            | Purpose                                                                                  |
| -------------------------------- | ---------------------------------------------------------------------------------------- |
| `Models/Language`                | Eloquent model (PK: `lang_id`, no timestamps), `HasFactory`, custom `LanguageCollection` |
| `Middleware/SetLocaleFromHeader` | Reads `X-Locale` header → sets Laravel locale                                            |
| `Supports/LanguageLocaleCache`   | `Cache::rememberForever` mapping `lang_code` ↔ `lang_locale`, with auto-invalidation     |
| `Policies/LanguagePolicy`        | Permission-based authorization (`languages.index`, `.create`, `.edit`, `.destroy`)       |
| `Data/Factories/LanguageFactory` | Factory with `vietnamese()`, `english()`, `rtl()`, `default()` states                    |

## Database

### Table: `languages`

| Column            | Type      | Description                       |
| ----------------- | --------- | --------------------------------- |
| `lang_id`         | BIGINT PK | Custom primary key                |
| `lang_name`       | VARCHAR   | Display name (e.g., "Tiếng Việt") |
| `lang_locale`     | VARCHAR   | Locale code (e.g., "vi")          |
| `lang_code`       | VARCHAR   | Language code (e.g., "vi")        |
| `lang_flag`       | VARCHAR   | Flag identifier                   |
| `lang_is_default` | BOOLEAN   | Is default language               |
| `lang_order`      | INT       | Sort order                        |
| `lang_is_rtl`     | BOOLEAN   | Right-to-left                     |

> **Note**: No `created_at` / `updated_at` columns. Model uses `$timestamps = false`.

## Middleware

### `SetLocaleFromHeader`

Registered globally in `bootstrap/app.php` for all API routes:

```php
->api(append: [
    SetLocaleFromHeader::class,
    // ...
])
```

**Flow**: `X-Locale` header → `LanguageLocaleCache::resolveToLangLocale()` → `App::setLocale()`

## Cache Strategy

| Cache Key               | TTL     | Invalidation                                    |
| ----------------------- | ------- | ----------------------------------------------- |
| `language_locale_cache` | Forever | On language create/update/delete (via Listener) |

`LanguageLocaleCache` caches the entire `lang_code → lang_locale` mapping in a single cache entry.

## Config

- `Configs/appSection-languages.php` — available locales list (~200 languages)
- `Configs/permissions.php` — permission definitions

## Tests

```bash
php artisan test app/Containers/AppSection/Language/Tests/
```

**12 tests, 172 assertions** covering:

- List languages (authenticated + public endpoint)
- CRUD operations (create, update, delete)
- Delete default language (reassignment logic)
- Set default language
- Get current language
- Available/supported languages
- Permission checks

## Change Log

- `2026-02-07`: Added container README
- `2026-03-05`: Refactored `HasOriginLang` trait → `LanguageLocaleCache` (centralized, long-term cache)
- `2026-03-05`: Added `LanguageFactory` with `vietnamese()`, `english()`, `rtl()`, `default()` states
- `2026-03-05`: Added `HasFactory` + `newFactory()` to Language model
- `2026-03-05`: Fixed public route `ListLanguagesRequest::authorize()` crash for unauthenticated access
- `2026-03-05`: Language.php cleanup (1300 → 200 lines)
- `2026-03-05`: Test suite (12 tests, 172 assertions)
