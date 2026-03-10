### CustomField Container

Container path: `app/Containers/AppSection/CustomField`

### Scope

- Manage **custom field groups** and **field items** (ACF-like system).
- Store **custom field values** against any supported model (polymorphic).
- Support **conditional rules** to show/hide field groups based on context.
- Provide **API-first** CRUD + listing for FE (Next.js) admin panels.
- Full **multilingual** support (3 translation layers).

### Architecture

```
FieldGroup (1) ──► (N) FieldItem (tree: parent_id)
  │                       │
  ▼                       ▼
FieldGroupTranslation   FieldItemTranslation
  (title per locale)      (title, instructions, options per locale)
                          │
                          ▼
CustomField (N) ──► CustomFieldTranslation (value per locale)
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

---

### Multilingual (i18n) — FE Integration Guide

#### Nguyên tắc chính

| Header / Field      | Vai trò                                   | Mô tả                                             |
| ------------------- | ----------------------------------------- | ------------------------------------------------- |
| `X-Locale` (header) | **Đọc** — ngôn ngữ hiển thị UI            | FE user chọn English → `X-Locale: en` mọi request |
| `lang_code` (body)  | **Ghi** — ngôn ngữ đang cập nhật bản dịch | Admin đang sửa bản dịch VI → `"lang_code": "vi"`  |

> **Quan trọng**: `X-Locale` là ngôn ngữ display của user, luôn gửi theo cài đặt ngôn ngữ hiện tại. `lang_code` là ngôn ngữ **target** khi admin nhập/sửa bản dịch cho ngôn ngữ khác.

#### 3 Translation Layers

| Layer           | Table                        | Columns translate                  | Mô tả                                         |
| --------------- | ---------------------------- | ---------------------------------- | --------------------------------------------- |
| **FieldGroup**  | `field_groups_translations`  | `title`                            | Tên nhóm trường hiển thị trong form builder   |
| **FieldItem**   | `field_items_translations`   | `title`, `instructions`, `options` | Label, hướng dẫn, lựa chọn trong form         |
| **CustomField** | `custom_fields_translations` | `value`                            | Giá trị user nhập (text, textarea, select...) |

---

#### 1. Đọc dữ liệu — `X-Locale` header

FE gửi `X-Locale` theo ngôn ngữ hiển thị của user. BE tự trả data đúng locale.

**Request (user đang xem tiếng Việt):**

```http
GET /v1/custom-field-groups?include=items
Authorization: Bearer {{access_token}}
X-Locale: vi
```

**Response:**

```json
{
    "data": [
        {
            "type": "FieldGroup",
            "id": "v9jEX5zd5kDdnW2O",
            "title": "Thông tin bổ sung bài viết",
            "items": {
                "data": [
                    {
                        "title": "Loại bài viết",
                        "slug": "post_type",
                        "type": "select",
                        "instructions": "Chọn loại bài viết",
                        "options": {
                            "selectChoices": "article:Bài viết\nnews:Tin tức\ntutorial:Hướng dẫn",
                            "defaultValue": "article"
                        }
                    }
                ]
            }
        }
    ]
}
```

**Cùng request với `X-Locale: en` (hoặc không gửi):**

```json
{
    "title": "Post Additional Information",
    "items": {
        "data": [
            {
                "title": "Post Type",
                "instructions": "Select the type of post",
                "options": {
                    "selectChoices": "article:Article\nnews:News\ntutorial:Tutorial"
                }
            }
        ]
    }
}
```

> Response structure **giống hệt nhau** giữa các locales. FE không cần branch logic theo locale.

---

#### 2. Đọc Custom Field Boxes — `X-Locale` header

```http
GET /v1/custom-fields/boxes?model=post&reference_id=v9jEX5zd5kDdnW2O
Authorization: Bearer {{access_token}}
X-Locale: vi
```

3 layers translate **đồng thời**:

- **FieldGroup title**: "Thông tin bổ sung bài viết"
- **FieldItem**: title, instructions, options đều tiếng Việt
- **CustomField value**: Giá trị user đã nhập cho locale `vi`

---

#### 3. Lưu giá trị Custom Field — `lang_code` trong body

Khi admin edit custom fields của một Post/Page, dùng `lang_code` trong body để chỉ định target locale.

**Lưu cho default locale (en) — không cần `lang_code`:**

```http
PATCH /v1/posts/{id}
Authorization: Bearer {{access_token}}
X-Locale: en

{
  "custom_fields": [
    { "slug": "post_type", "type": "select", "value": "tutorial" }
  ]
}
```

→ Value `"tutorial"` lưu vào `custom_fields.value` (main table)

**Lưu cho locale khác (vi) — gửi `lang_code`:**

```http
PATCH /v1/posts/{id}
Authorization: Bearer {{access_token}}
X-Locale: en

{
  "lang_code": "vi",
  "custom_fields": [
    { "slug": "post_type", "type": "select", "value": "hướng dẫn" }
  ]
}
```

→ Value `"hướng dẫn"` lưu vào `custom_fields_translations` (locale vi)

> `X-Locale: en` vì admin đang dùng UI tiếng Anh. `lang_code: vi` vì đang cập nhật bản dịch tiếng Việt.

---

#### 4. Tạo & cập nhật FieldGroup translations — Workflow

**Bước 1: Tạo bản ghi gốc (POST) — KHÔNG gửi `lang_code`**

```http
POST /v1/custom-field-groups
Authorization: Bearer {{access_token}}
X-Locale: en

{
  "title": "Post Additional Information",
  "status": "published",
  "group_items": [
    { "title": "Post Type", "slug": "post_type", "type": "select" }
  ]
}
```

→ Tạo `field_groups` + `field_items` ở default locale (en). Trả về ID.

**Bước 2: Thêm bản dịch (PUT) — GỬI `lang_code`**

```http
PUT /v1/custom-field-groups/{id}
Authorization: Bearer {{access_token}}
X-Locale: en

{
  "lang_code": "vi",
  "title": "Thông tin bổ sung bài viết",
  "group_items": [
    {
      "title": "Loại bài viết",
      "slug": "post_type",
      "type": "select",
      "instructions": "Chọn loại bài viết",
      "options": "{\"selectChoices\":\"article:Bài viết\\nnews:Tin tức\"}"
    }
  ]
}
```

→ Lưu vào `field_groups_translations` + `field_items_translations` (locale vi)

> **Lưu ý**: `lang_code` chỉ dùng ở **PUT** (update). **POST** (create) luôn tạo bản ghi default locale. Phải có bản ghi gốc trước mới tạo được bản dịch.

---

#### 5. Options i18n — FE parse

Options **không cố định** cấu trúc, phụ thuộc field type:

| Field Type                    | Options keys cần translate | Ví dụ                              |
| ----------------------------- | -------------------------- | ---------------------------------- |
| `select`, `radio`, `checkbox` | `selectChoices`            | `"key:Label\nkey2:Label2"`         |
| `text`, `textarea`, `number`  | `placeholderText`          | `"Nhập tóm tắt..."`                |
| `image`, `file`               | —                          | Không có text cần translate        |
| Tất cả                        | `defaultValue`             | **KHÔNG translate** (đây là value) |

```javascript
// Parse selectChoices cho FE
const choices = options.selectChoices.split("\n").map((line) => {
    const [value, ...rest] = line.split(":");
    return { value, label: rest.join(":") };
});
// → [{ value: "article", label: "Bài viết" }, ...]
```

> FE **luôn dùng `value` (key)** để submit, chỉ hiển thị `label`. Keys giữ nguyên giữa các locales.

---

#### 6. Edge Cases

| Tình huống                      | Hành vi                                                                           |
| ------------------------------- | --------------------------------------------------------------------------------- |
| Locale không có translation     | Trả về giá trị default locale (fallback)                                          |
| `lang_code` không gửi           | Cập nhật default locale                                                           |
| `X-Locale` trùng default locale | Đọc từ main table, không query translations                                       |
| Export field group              | Trả về data default locale + key `translations` chứa tất cả bản dịch              |
| Import field group              | Tạo data default locale + auto-import translations nếu JSON có key `translations` |
| Duplicate field group           | Deep clone kèm tất cả translations (FieldGroup + FieldItems)                      |

---

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

- **Đọc (GET)**: dùng `X-Locale` header để nhận response đa ngôn ngữ.
- **Ghi (POST/PUT/PATCH)**: dùng `lang_code` trong body để chỉ định ngôn ngữ target.

| Method   | Endpoint                                 | Permission              | Description                                      |
| -------- | ---------------------------------------- | ----------------------- | ------------------------------------------------ |
| `GET`    | `/v1/custom-field-options`               | `custom-fields.index`   | Get options (statuses, field_types, rule_groups) |
| `GET`    | `/v1/custom-field-groups`                | `custom-fields.index`   | List all field groups                            |
| `GET`    | `/v1/custom-field-groups/{id}`           | `custom-fields.index`   | Find field group by ID                           |
| `POST`   | `/v1/custom-field-groups`                | `custom-fields.create`  | Create field group                               |
| `PUT`    | `/v1/custom-field-groups/{id}`           | `custom-fields.edit`    | Update field group                               |
| `DELETE` | `/v1/custom-field-groups/{id}`           | `custom-fields.destroy` | Delete field group                               |
| `POST`   | `/v1/custom-field-groups/{id}/duplicate` | `custom-fields.create`  | Deep clone field group                           |
| `GET`    | `/v1/custom-field-groups/{id}/export`    | `custom-fields.index`   | Export field group as portable JSON              |
| `POST`   | `/v1/custom-field-groups/import`         | `custom-fields.create`  | Import field group from JSON                     |
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
- Translation models use composite PK — use `DB::table()->upsert()` in seeders, not `Model::updateOrCreate()`.

### Change Log

- `2026-02-07`: Added container README.
- `2026-03-09`: Optimized — HasCustomFields trait, caching, validation hardening, duplication endpoint.
- `2026-03-09`: Added import/export endpoints.
- `2026-03-09`: Added multilingual support — FieldGroup title, FieldItem title/instructions/options translations.
- `2026-03-09`: Corrected i18n docs — separated `X-Locale` (read/display) from `lang_code` (write/save target).
