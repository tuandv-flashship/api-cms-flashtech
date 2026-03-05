# LanguageAdvanced Container

> Container path: `app/Containers/AppSection/LanguageAdvanced`

## Scope

Quản lý **content translations** (bản dịch nội dung entity) — khác với Translation container (UI translations). Cho phép dịch slugs, posts, pages, categories sang nhiều ngôn ngữ.

### Core Features

- **Slug Translation**: Dịch URL slugs cho từng entity (post, page, category)
- **HasLanguageTranslations Trait**: Cung cấp relationship `translations()` cho models
- **Cascade Delete**: Tự động xóa translations khi entity bị xóa

## Architecture

```
Post (entity) ──→ PostTranslation (content per locale)
   ↓                    ↓
   slug ──→ SlugTranslation (slug per locale)
```

## API Routes

### Private (auth required)

| Method  | URI                                | Description             |
| ------- | ---------------------------------- | ----------------------- |
| `PATCH` | `/v1/slugs/{slug_id}/translations` | Update slug translation |

> **Note**: Content translation update routes (posts, pages, categories, etc.) are defined in their respective containers (Blog, Page, Gallery, etc.), not here.

## Key Classes

| Class                                         | Purpose                                            |
| --------------------------------------------- | -------------------------------------------------- |
| `Traits/HasLanguageTranslations`              | Trait for models that support content translations |
| `Controllers/UpdateSlugTranslationController` | PATCH slug translations                            |

## Config

- `Configs/language-advanced.php` — container configuration

## Related Containers

| Container             | Relationship                                                    |
| --------------------- | --------------------------------------------------------------- |
| **Language**          | Provides locale list, middleware, default language              |
| **Translation**       | UI translations (validation messages, labels) — different scope |
| **Slug**              | Slug generation, this container handles slug translations       |
| **Blog/Page/Gallery** | Content entities that use `HasLanguageTranslations` trait       |

## Change Log

- `2026-02-07`: Added container README
- `2026-03-05`: Updated documentation with architecture and relationship to other containers
