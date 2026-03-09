### CustomField Container

Container path: `app/Containers/AppSection/CustomField`

### Scope

- Manage **custom field groups** and **field items** (ACF-like system).
- Store **custom field values** against any supported model (polymorphic).
- Support **conditional rules** to show/hide field groups based on context.
- Provide **API-first** CRUD + listing for FE (Next.js) admin panels.

### Architecture

```
FieldGroup (1) ──► (N) FieldItem (tree: parent_id)
                          │
                          ▼
CustomField (N) ──► CustomFieldTranslation (per locale)
  use_for       = Model class (polymorphic)
  use_for_id    = Model instance ID
  field_item_id = FieldItem ID
```

### HasCustomFields Trait

Models that support custom fields should use the `HasCustomFields` trait:

```php
use App\Containers\AppSection\CustomField\Traits\HasCustomFields;

final class Post extends Model
{
    use HasCustomFields;
}
```

**Provides:**

- **Cascade delete** — custom fields + translations are auto-deleted when the model is deleted.
- **Helper** — `$model->getCustomFieldBoxes(?string $locale, array $rules)` returns exported data.

**Currently applied to:** `Post`, `Category`, `Page`.

### Supported Field Types (17)

| Type       | Description                     |
| ---------- | ------------------------------- |
| `text`     | Single-line text                |
| `number`   | Numeric input                   |
| `email`    | Email input                     |
| `password` | Password input                  |
| `url`      | URL input                       |
| `date`     | Date picker                     |
| `datetime` | Date + time                     |
| `time`     | Time only                       |
| `color`    | Color picker                    |
| `textarea` | Multi-line text                 |
| `checkbox` | Multiple choices                |
| `radio`    | Single choice                   |
| `select`   | Dropdown                        |
| `image`    | Media image (returns `thumb`)   |
| `file`     | Media file (returns `full_url`) |
| `wysiwyg`  | Rich text editor                |
| `repeater` | Nested repeatable group         |

### Conditional Rules Engine

Field groups are shown/hidden based on rules evaluated against context:

| Rule Key                                 | Description                 |
| ---------------------------------------- | --------------------------- |
| `page_template`                          | Current page template value |
| `Page::class`                            | Specific page ID            |
| `Category::class`                        | Specific category ID        |
| `Post::class_post_format`                | Post format                 |
| `Post::class_post_with_related_category` | Post's category IDs         |
| `logged_in_user`                         | Current user ID             |
| `logged_in_user_has_role`                | Current user's roles        |
| `model_name`                             | Reference type class name   |

Rules use AND within groups, OR between groups.

### API Routes

All routes are **private** (`auth:api` middleware).

| Method   | Endpoint                                 | Permission              | Description                                      |
| -------- | ---------------------------------------- | ----------------------- | ------------------------------------------------ |
| `GET`    | `/v1/custom-field-options`               | `custom-fields.index`   | Get options (statuses, field_types, rule_groups) |
| `GET`    | `/v1/custom-field-groups`                | `custom-fields.index`   | List all field groups                            |
| `GET`    | `/v1/custom-field-groups/{id}`           | `custom-fields.index`   | Find field group by ID                           |
| `POST`   | `/v1/custom-field-groups`                | `custom-fields.create`  | Create field group                               |
| `PUT`    | `/v1/custom-field-groups/{id}`           | `custom-fields.edit`    | Update field group                               |
| `DELETE` | `/v1/custom-field-groups/{id}`           | `custom-fields.destroy` | Delete field group                               |
| `POST`   | `/v1/custom-field-groups/{id}/duplicate` | `custom-fields.create`  | Deep clone field group                           |
| `GET`    | `/v1/custom-fields/boxes`                | `custom-fields.index`   | Get custom field boxes for a model               |

### Supported Models

Configured in `Configs/custom-field.php`:

```php
'supported' => [
    'page'     => Page::class,
    'post'     => Post::class,
    'category' => Category::class,
],
```

### Caching

- `exportCustomFieldsData()` is cached per `{model}:{id}:{locale}`, TTL 1 hour.
- Cache is auto-invalidated when `saveCustomFieldsForModel()` is called.
- Public `invalidateCache(string $referenceType, ?int $referenceId)` available for manual invalidation.

### Permissions

```
custom-fields.index   — View field groups and boxes
custom-fields.create  — Create and duplicate field groups
custom-fields.edit    — Update field groups
custom-fields.destroy — Delete field groups
```

### Validation

Field group items are validated:

- `title` — required, string, max 255
- `type` — required, must be one of 17 supported types
- `slug` — optional, string, max 255
- `instructions` — optional, string, max 1000
- `options` — optional (JSON)

### Tests

- `Tests/Unit/UI/API/Transformers/CustomFieldBoxTransformerTest.php`
- `Tests/Unit/UI/API/Transformers/FieldGroupTransformerTest.php`

### Operational Notes

- Keep field schema validation centralized in Request/Task layers.
- Changes to field schema should be backward-compatible with stored values.
- When adding a new model to `custom-field.supported`, also add `use HasCustomFields` to the model.

### Change Log

- `2026-02-07`: Added container README.
- `2026-03-09`: Optimized — HasCustomFields trait, caching, validation hardening, duplication endpoint.
