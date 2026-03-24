# Table Container

> Container path: `app/Containers/AppSection/Table`

## Scope

Hệ thống **table metadata + form metadata + bulk operations** tập trung, migrate từ Botble CMS `platform/core/table`. Phục vụ API-first cho phía FE data table + dynamic form rendering.

### Core Features

- **Dynamic Column Definitions**: FE nhận cấu trúc cột từ API — type, sortable, searchable, visible, width, align
- **Dynamic Form Metadata**: FE nhận cấu trúc form từ API — field types, validation rules, groups, options
- **Permission-Gated Actions**: header actions (Create), row actions (Edit/Delete), bulk actions, bulk changes — chỉ trả những gì user có quyền
- **Convention Over Configuration**: Auto-detect columns/actions/bulk changes từ model casts/fillable + permission prefix
- **Distributed Config**: Mỗi container tự quản lý model config riêng (Porto SAP principle)
- **Per-User Column Visibility**: Mỗi user lưu preference ẩn/hiện cột riêng
- **Bulk Actions**: Xoá hàng loạt với per-record model events + audit logging
- **Bulk Changes**: Thay đổi field hàng loạt (status, name, is_featured, etc.)
- **Partial Failure Handling**: Response chi tiết `{ success, failed, errors[] }`
- **Validation Rule Parsing**: Auto-detect validation rules từ Laravel Request classes → react-hook-form format
- **Translation Forms**: Support `translate` action cho multilingual content
- **Enum Integration**: Auto-populate options từ PHP Backed Enums (label + value)
- **i18n**: Tất cả text qua `trans()` keys + `X-Locale` header
- **Caching**: Table metadata cached per `user:model:locale`, form metadata per `model:action`

## Architecture

### Table Meta Flow

```
FE mounts data table
    ↓
GET /v1/table-meta?model=post  +  X-Locale: vi
    ↓
BulkActionRegistry
    → 1. Auto-discover model config from container table-models.php
    → 2. Permission check (model-level)
    → 3. Auto-detect columns từ Model::getCasts() / getFillable()
    → 4. Merge config columns (additive)
    → 5. Merge model trait columns (highest priority)
    → 6. Enrich searchable + search_operator từ Repository $fieldSearchable
    → 7. Auto-generate CRUD actions từ permission_prefix
    → 8. Filter ALL by user permissions ($user->can())
    → 9. Merge user column visibility prefs (settings table)
    → 10. Translate ALL labels via trans()
    → 11. Cache result
    ↓
FE nhận JSON: columns[], header_actions[], row_actions[], bulk_actions[], bulk_changes[], pagination
```

### Form Meta Flow

```
FE opens create/edit/translate form
    ↓
GET /v1/form-meta?model=post&action=create
    ↓
GetFormMetaAction
    → 1. Resolve model config (auto-discover from table-models.php)
    → 2. Permission check (form-level)
    → 3. Instantiate Request class, parse rules() + messages()
    → 4. ValidationRuleParser: Laravel rules → FormFieldDefinition[]
    → 5. Merge config overrides (groups, order, colSpan, type override)
    → 6. Serialize all fields with validation → JSON
    → 7. Cache result
    ↓
FE nhận JSON: groups[], fields[] (with validation), submit{method, url}
```

### Override Hierarchy (most specific wins)

```
1. Model trait method (getTableColumns(), etc.)    ← highest priority
2. Config key (columns, row_actions, etc.)         ← explicit override
3. Auto-detection (from permission_prefix, casts)  ← convention default
```

## API Routes

| Method | URI                            | Description                                        |
| ------ | ------------------------------ | -------------------------------------------------- |
| `GET`  | `/v1/table-meta?model=post`    | Full table metadata cho 1 model                    |
| `GET`  | `/v1/table-meta`               | Danh sách tất cả model keys đã đăng ký             |
| `GET`  | `/v1/form-meta?model=post&action=create` | Form metadata cho 1 model + action      |
| `POST` | `/v1/bulk-actions`             | Dispatch bulk action (delete, etc.)                |
| `POST` | `/v1/bulk-changes`             | Dispatch bulk field update (status, name, etc.)    |
| `PUT`  | `/v1/table-columns-visibility` | Lưu column visibility + order preferences per user |

### Response `GET /v1/table-meta?model=post`

```json
{
  "data": {
    "model": "post",
    "columns": [
      { "key": "id", "title": "ID", "type": "id", "sortable": true, "searchable": true,
        "search_operator": "=",
        "visible": true, "width": 70, "align": "center", "priority": 0 },
      { "key": "name", "title": "Tên", "type": "text", "sortable": true, "searchable": true,
        "search_operator": "like",
        "visible": true, "width": null, "align": "left", "priority": 2 },
      { "key": "status", "title": "Trạng thái", "type": "status", "sortable": true,
        "searchable": true, "search_operator": "=",
        "visible": true, "width": 120, "align": "center", "priority": 5,
        "options": { "published": "Đã xuất bản", "draft": "Nháp", "pending": "Chờ duyệt" } }
    ],
    "header_actions": [ "..." ],
    "row_actions": [ "..." ],
    "bulk_actions": [ "..." ],
    "bulk_changes": [ "..." ],
    "default_sort": { "key": "created_at", "direction": "desc" },
    "max_bulk_items": 100,
    "pagination": { "default_limit": 15, "limits": [15, 30, 50, 100] }
  }
}
```

### Response `GET /v1/form-meta?model=post&action=create`

```json
{
  "data": {
    "model": "post",
    "action": "create",
    "groups": [
      { "key": "basic", "label": "Thông tin cơ bản", "order": 0 },
      { "key": "content", "label": "Nội dung", "order": 1 },
      { "key": "settings", "label": "Cài đặt", "order": 2 },
      { "key": "media", "label": "Hình ảnh", "order": 3 }
    ],
    "fields": [
      {
        "key": "name", "label": "Tên", "type": "text",
        "group": "basic", "order": 0, "col_span": 1,
        "validation": {
          "required": "Name là bắt buộc",
          "maxLength": { "value": 255, "message": "Tối đa 255 ký tự" }
        }
      },
      {
        "key": "status", "label": "Trạng thái", "type": "select",
        "group": "settings", "order": 0, "col_span": 1,
        "options": { "published": "Đã xuất bản", "draft": "Nháp", "pending": "Chờ duyệt" }
      },
      {
        "key": "is_featured", "label": "Nổi bật", "type": "boolean",
        "group": "settings", "order": 1, "col_span": 1
      },
      {
        "key": "seo_meta", "label": "Seo Meta", "type": "json",
        "order": 7, "col_span": 1
      }
    ],
    "submit": { "method": "POST", "url": "/v1/blog/posts" }
  }
}
```

### Response `GET /v1/form-meta?model=post&action=translate`

```json
{
  "data": {
    "model": "post",
    "action": "translate",
    "groups": [
      { "key": "translation", "label": "Thông tin cơ bản", "order": 0 }
    ],
    "fields": [
      { "key": "lang_code", "type": "hidden", "group": "translation", "order": 0,
        "validation": { "required": "Lang Code là bắt buộc" } },
      { "key": "name", "type": "text", "group": "translation", "order": 1 },
      { "key": "description", "type": "textarea", "group": "translation", "order": 2, "col_span": 2 },
      { "key": "content", "type": "textarea", "group": "translation", "order": 3, "col_span": 2 },
      { "key": "slug", "type": "text", "group": "translation", "order": 4 }
    ],
    "submit": { "method": "PUT", "url": "/v1/blog/posts/{id}/translations" }
  }
}
```

### Request `POST /v1/bulk-actions`

```json
{ "model": "post", "action": "delete", "ids": ["hashed1", "hashed2"] }
```

Response:

```json
{
    "message": "Đã hoàn thành thao tác hàng loạt.",
    "data": { "success": 2, "failed": 1 },
    "errors": [{ "id": 123, "reason": "not_found" }]
}
```

### Request `POST /v1/bulk-changes`

```json
{
    "model": "post",
    "ids": ["hashed1", "hashed2"],
    "key": "status",
    "value": "published"
}
```

### Request `PUT /v1/table-columns-visibility`

```json
{
    "model": "post",
    "columns": {
        "id": { "visible": true, "order": 0 },
        "name": { "visible": true, "order": 1 },
        "status": { "visible": true, "order": 2 },
        "views": { "visible": false, "order": 6 },
        "created_at": { "visible": true, "order": 99 }
    }
}
```

## Config — Distributed Pattern

> **Convention**: Mỗi container tạo `Configs/table-models.php` → auto-discovered bởi Table container.

### Global Config: `Table/Configs/appSection-table.php`

```php
return [
    'cache_ttl'      => env('TABLE_META_CACHE_TTL', 3600),
    'max_bulk_items' => 100,
    'models'         => [],  // auto-discovered from container configs
];
```

### Container Config Example: `Blog/Configs/table-models.php`

```php
return [
    'post' => [
        'model'             => Post::class,
        'repository'        => PostRepository::class,
        'permission_prefix' => 'posts',
        'permission'        => 'posts.index',
        'api_prefix'        => '/v1/blog/posts',
        'fe_prefix'         => '/blog/posts',
        'default_sort'      => ['key' => 'created_at', 'direction' => 'desc'],
        'pagination'        => ['default_limit' => 15, 'limits' => [15, 30, 50, 100]],

        'columns' => [
            ColumnDefinition::make('status', 'table::columns.status')
                ->searchable()->enum(ContentStatus::class)->width(100),
        ],

        'forms' => [
            'create' => [
                'request'    => CreatePostRequest::class,
                'permission' => 'posts.create',
                'submit'     => ['method' => 'POST', 'url' => '/v1/blog/posts'],
                'groups'     => [/* ... */],
                'overrides'  => [/* FormFieldDefinition::text('name')->group('basic')... */],
            ],
            'update' => [
                'request'    => UpdatePostRequest::class,
                'permission' => 'posts.edit',
                'submit'     => ['method' => 'PATCH', 'url' => '/v1/blog/posts/{id}'],
            ],
            'translate' => [
                'request'    => UpdatePostTranslationRequest::class,
                'permission' => 'posts.edit',
                'submit'     => ['method' => 'PUT', 'url' => '/v1/blog/posts/{id}/translations'],
            ],
        ],
    ],
];
```

### Registered Models

| Key | Model | Container | Permission | Actions |
|---|---|---|---|---|
| `post` | Post | Blog | `posts.*` | create, update, translate |
| `category` | Category | Blog | `categories.*` | create, update, translate |
| `tag` | Tag | Blog | `tags.*` | create, update, translate |
| `page` | Page | Page | `pages.*` | create, update, translate |

## Key Classes

| Class | Purpose |
| --- | --- |
| `Abstracts/ColumnDefinition` | Fluent builder cho column metadata + `enum()` support |
| `Abstracts/ActionDefinition` | Fluent builder cho action metadata + confirmation modal |
| `Abstracts/FormFieldDefinition` | Fluent builder cho form field metadata (11+ types) |
| `Supports/BulkActionRegistry` | Core engine: auto-discover + permission filter + cache |
| `Supports/ValidationRuleParser` | Laravel Request rules() → react-hook-form validation |
| `Actions/GetFormMetaAction` | Form metadata resolver: auto-detect + merge + cache |
| `Actions/GetTableMetaAction` | Table metadata resolver |
| `Traits/HasTableConfig` | Optional model trait to override auto-detection |

### FormFieldDefinition Types

| Type | Factory Method | FE Renders |
|---|---|---|
| `text` | `::text()` | Text input |
| `textarea` | `::textarea()` | Textarea |
| `number` | `::number()` | Number input |
| `select` | `::select()` | Select dropdown |
| `boolean` | `::boolean()` | Toggle/Checkbox |
| `relation` | `::relation()` | Async select (API endpoint) |
| `color` | `::color()` | Color picker |
| `icon` | `::icon()` | Icon picker |
| `date` | `::date()` | Date picker |
| `datetime` | `::datetime()` | DateTime picker |
| `hidden` | `::hidden()` | Hidden input |
| `json` | auto-detected | JSON editor |

### ValidationRuleParser Mapping

| Laravel Rule | react-hook-form Rule | Notes |
|---|---|---|
| `required` | `required: "message"` | Custom messages from `Request::messages()` |
| `max:N` (string) | `maxLength: {value, message}` | |
| `max:N` (number) | `max: {value, message}` | |
| `min:N` (string) | `minLength: {value, message}` | |
| `min:N` (number) | `min: {value, message}` | |
| `regex:/pattern/` | `pattern: {value, message}` | |
| `email` | `pattern: {email regex}` | |
| `Rule::enum(Cls)` | type → `select`, options auto | Reflection-based |
| `Rule::in(a,b)` | type → `select`, options auto | |
| `array` | type → `json` | Nested rules skipped |
| `boolean` | type → `boolean` | |
| `exists:table,col` | type → `relation` | |

## i18n

Lang files tại `Resources/lang/{en,vi}/`:

| File | Nội dung |
| --- | --- |
| `actions.php` | Action labels, confirmation messages |
| `columns.php` | Column titles |
| `fields.php` | Form field labels |
| `groups.php` | Form group labels |
| `bulk_changes.php` | Bulk change placeholders |
| `statuses.php` | Status enum labels |

## Cache Strategy

| Cache Key | TTL | Invalidation |
| --- | --- | --- |
| `table_meta:{user_id}:{model}:{locale}` | `TABLE_META_CACHE_TTL` | Column visibility save, permission change |
| `form_meta:{model}:{action}` | `TABLE_META_CACHE_TTL` | Config change, `cache:clear` |

## Extending

### Thêm model mới (Porto SAP way)

```php
// NewContainer/Configs/table-models.php
return [
    'new_model' => [
        'model'             => NewModel::class,
        'repository'        => NewModelRepository::class,
        'permission_prefix' => 'new-models',
        'permission'        => 'new-models.index',
        'api_prefix'        => '/v1/new-models',
        'fe_prefix'         => '/new-models',
        'forms' => [
            'create' => [
                'request'    => CreateNewModelRequest::class,
                'permission' => 'new-models.create',
            ],
        ],
    ],
];
```

→ Auto-discovered. Available at `/v1/table-meta?model=new_model` + `/v1/form-meta?model=new_model&action=create`.

## Change Log

- `2026-03-24`: **Distributed config** — model configs moved to container `table-models.php`
- `2026-03-24`: **Form-meta API** — `GET /v1/form-meta` with validation rules, groups, field types
- `2026-03-24`: **Translation forms** — `translate` action for post/category/tag/page
- `2026-03-24`: **ValidationRuleParser** — skip nested rules, array→json type, safe try-catch
- `2026-03-24`: **ColumnDefinition** — `enum()` method, options serialization fix
- `2026-03-24`: **BulkActionRegistry** — permission check, pagination, auto-discover
- `2026-03-24`: **ContentStatus enum** — added `label()` + `options()` methods
- `2026-03-23`: Search operator sync — auto-enrich `searchable` + `search_operator` từ Repository
- `2026-03-06`: Initial implementation — Botble CMS table module migration (API-first)
