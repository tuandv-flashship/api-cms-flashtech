# Table Container

> Container path: `app/Containers/AppSection/Table`

## Scope

Hệ thống **table metadata + bulk operations** tập trung, migrate từ Botble CMS `platform/core/table`. Phục vụ API-first cho phía FE data table.

### Core Features

- **Dynamic Column Definitions**: FE nhận cấu trúc cột từ API — type, sortable, searchable, visible, width, align
- **Permission-Gated Actions**: header actions (Create), row actions (Edit/Delete), bulk actions, bulk changes — chỉ trả những gì user có quyền
- **Convention Over Configuration**: Auto-detect columns/actions/bulk changes từ model casts/fillable + permission prefix
- **Per-User Column Visibility**: Mỗi user lưu preference ẩn/hiện cột riêng
- **Bulk Actions**: Xoá hàng loạt với per-record model events + audit logging
- **Bulk Changes**: Thay đổi field hàng loạt (status, name, is_featured, etc.)
- **Partial Failure Handling**: Response chi tiết `{ success, failed, errors[] }`
- **i18n**: Tất cả text qua `trans()` keys + `X-Locale` header
- **Caching**: Table metadata cached per `user:model:locale`

## Architecture

```
FE mounts data table
    ↓
GET /v1/table-meta?model=post  +  X-Locale: vi
    ↓
BulkActionRegistry
    → 1. Auto-detect columns từ Model::getCasts() / getFillable()
    → 2. Merge config columns (additive)
    → 3. Merge model trait columns (highest priority)
    → 4. Auto-generate CRUD actions từ permission_prefix
    → 5. Filter ALL by user permissions ($user->can())
    → 6. Merge user column visibility prefs (settings table)
    → 7. Translate ALL labels via trans()
    → 8. Cache result
    ↓
FE nhận JSON: columns[], header_actions[], row_actions[], bulk_actions[], bulk_changes[]
    → Render table, render action buttons, render bulk dropdown
    → Zero permission logic phía client
```

### Override Hierarchy (most specific wins)

```
1. Model trait method (getTableColumns(), etc.)    ← highest priority
2. Config key (columns, row_actions, etc.)         ← explicit override
3. Auto-detection (from permission_prefix, casts)  ← convention default
```

## API Routes

| Method | URI                            | Description                                     |
| ------ | ------------------------------ | ----------------------------------------------- |
| `GET`  | `/v1/table-meta?model=post`    | Full table metadata cho 1 model                 |
| `GET`  | `/v1/table-meta`               | Danh sách tất cả model keys đã đăng ký          |
| `POST` | `/v1/bulk-actions`             | Dispatch bulk action (delete, etc.)             |
| `POST` | `/v1/bulk-changes`             | Dispatch bulk field update (status, name, etc.) |
| `PUT`  | `/v1/table-columns-visibility` | Lưu column visibility preferences per user      |

### Response `GET /v1/table-meta?model=post`

```json
{
  "data": {
    "model": "post",
    "columns": [
      { "key": "id", "title": "ID", "type": "id", "sortable": true, "searchable": false,
        "visible": true, "width": 70, "align": "center" },
      { "key": "name", "title": "Tên", "type": "text", "sortable": true, "searchable": true,
        "visible": true, "width": null, "align": "left" },
      { "key": "status", "title": "Trạng thái", "type": "status", "sortable": true,
        "visible": true, "width": 120, "align": "center",
        "options": { "published": "Đã xuất bản", "draft": "Bản nháp" } }
    ],
    "header_actions": [
      { "name": "create", "label": "Tạo mới", "icon": "ti-plus", "color": "primary",
        "type": "link", "url_pattern": "/admin/posts/create", ... }
    ],
    "row_actions": [
      { "name": "edit", "label": "Sửa", ..., "confirmation": null },
      { "name": "delete", "label": "Xoá", ...,
        "confirmation": { "title": "Xác nhận xoá", "message": "Bạn chắc chắn muốn xoá \"{name}\"?",
          "confirm_button": { "label": "Xoá", "color": "danger", "icon": "ti-trash" },
          "cancel_button": { "label": "Huỷ", "color": "secondary" } } }
    ],
    "bulk_actions": [
      { "action": "delete", "label": "Xoá đã chọn", "icon": "ti-trash", "color": "danger",
        "confirmation": { "title": "Xác nhận xoá hàng loạt", ... } }
    ],
    "bulk_changes": [
      { "key": "status", "title": "Trạng thái", "type": "select",
        "choices": { "published": "Đã xuất bản", "draft": "Bản nháp" } },
      { "key": "name", "title": "Tên", "type": "text", "placeholder": "Nhập tên..." }
    ],
    "default_sort": { "key": "created_at", "direction": "desc" },
    "max_bulk_items": 100
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
{ "model": "post", "columns": { "id": true, "views": false } }
```

## Auto-Detection Rules

### Columns

| Model Property            | Factory Method                         | Column Config                             |
| ------------------------- | -------------------------------------- | ----------------------------------------- |
| Primary key               | `ColumnDefinition::id()`               | type: `id`, width: 70, center             |
| `'image'` in fillable     | `ColumnDefinition::image()`            | type: `image`, width: 70, center          |
| `'name'` in fillable      | `ColumnDefinition::name()`             | type: `text`, searchable, emptyState      |
| `'email'` in fillable     | `ColumnDefinition::email()`            | type: `email`, searchable, `mailto:` link |
| `'phone'` in fillable     | `ColumnDefinition::phone()`            | type: `phone`, searchable, `tel:` link    |
| `'status'` Enum cast      | `ColumnDefinition::status($enum)`      | type: `status`, options from enum cases   |
| `'is_featured'` bool cast | `ColumnDefinition::boolean()`          | type: `boolean`, width: 100               |
| `usesTimestamps()`        | `ColumnDefinition::date('created_at')` | type: `date`, width: 160, emptyState      |

### Display Properties (FE metadata)

| Property     | Method                       | JSON Output                       | FE Renders         |
| ------------ | ---------------------------- | --------------------------------- | ------------------ |
| `copyable`   | `->copyable()`               | `"copyable": true`                | Copy button        |
| `maskable`   | `->maskable('*', 4)`         | `"mask": {"char":"*","length":4}` | `****1234`         |
| `emptyState` | `->emptyState('—')`          | `"empty_state": "—"`              | "—" when null      |
| `linkable`   | `->linkable('/edit/{id}')`   | `"link": {"url_pattern":"..."}`   | Clickable link     |
| `dateFormat` | `->dateFormat('DD/MM/YYYY')` | `"date_format": "DD/MM/YYYY"`     | Formatted date     |
| `limit`      | `->limit(50)`                | `"limit": 50`                     | Truncated text     |
| `icon`       | `->icon('ti-star')`          | `"icon": {"name":"ti-star"}`      | Icon prefix/suffix |
| `color`      | `->color('danger')`          | `"color": "danger"`               | Colored text       |

**Bulk changes** auto-detect:

| Model Property            | BulkChange Class       | Input Type   | Validation                  |
| ------------------------- | ---------------------- | ------------ | --------------------------- |
| `'status'` Enum cast      | `StatusBulkChange`     | `select`     | `in:enum_values`            |
| `'name'` in fillable      | `NameBulkChange`       | `text`       | `required\|string\|max:250` |
| `'email'` in fillable     | `EmailBulkChange`      | `text`       | `required\|email\|max:120`  |
| `'phone'` in fillable     | `PhoneBulkChange`      | `text`       | Phone regex                 |
| `'is_featured'` bool cast | `IsFeaturedBulkChange` | `select`     | `in:0,1`                    |
| `usesTimestamps()`        | `CreatedAtBulkChange`  | `datePicker` | `required\|date`            |

**Base classes** (for custom bulk changes):

| Base Class         | FE Type                    | Use For                             |
| ------------------ | -------------------------- | ----------------------------------- |
| `TextBulkChange`   | `text`                     | Any text input                      |
| `SelectBulkChange` | `select` / `select-search` | Dropdown, supports callback choices |
| `NumberBulkChange` | `number`                   | Integer input                       |
| `DateBulkChange`   | `date`                     | Date input                          |

**Actions** auto-generate từ `permission_prefix`: create header action + edit/delete row actions + delete bulk action.

## Key Classes

| Class                              | Purpose                                                                                                                |
| ---------------------------------- | ---------------------------------------------------------------------------------------------------------------------- |
| `Abstracts/ColumnDefinition`       | Fluent builder cho column metadata (factory methods: `id()`, `image()`, `status()`, `date()`, `boolean()`, `number()`) |
| `Abstracts/ActionDefinition`       | Fluent builder cho action metadata (factory methods: `link()`, `action()`) + confirmation modal                        |
| `Abstracts/BulkActionAbstract`     | Base cho bulk actions, dispatch pattern + lifecycle hooks                                                              |
| `Abstracts/BulkChangeAbstract`     | Base cho bulk field changes, validation rules, metadata                                                                |
| `Supports/BulkActionRegistry`      | Core engine: auto-detect, permission filter, cache, resolve                                                            |
| `Traits/HasTableConfig`            | Optional model trait để override auto-detected config                                                                  |
| `BulkActions/DeleteBulkAction`     | Xoá per-record (model events + audit log)                                                                              |
| `BulkChanges/TextBulkChange`       | Base: text input (`max:255`)                                                                                           |
| `BulkChanges/SelectBulkChange`     | Base: dropdown (static/callback choices, searchable)                                                                   |
| `BulkChanges/NumberBulkChange`     | Number input (`integer`, `min:0`)                                                                                      |
| `BulkChanges/DateBulkChange`       | Date input                                                                                                             |
| `BulkChanges/StatusBulkChange`     | Update status (auto-populate từ enum)                                                                                  |
| `BulkChanges/NameBulkChange`       | Update name (extends Text)                                                                                             |
| `BulkChanges/EmailBulkChange`      | Update email (extends Text, email validation)                                                                          |
| `BulkChanges/PhoneBulkChange`      | Update phone (extends Text, phone regex)                                                                               |
| `BulkChanges/IsFeaturedBulkChange` | Toggle featured (Yes/No select)                                                                                        |
| `BulkChanges/CreatedAtBulkChange`  | Update created_at (datePicker type)                                                                                    |
| `Providers/TableServiceProvider`   | Singleton binding cho `BulkActionRegistry` + register `table` translation namespace                                    |
| `Events/BulkActionCompleted`       | Event fired sau bulk action                                                                                            |
| `Events/BulkChangeCompleted`       | Event fired sau bulk change                                                                                            |

## Config

`Configs/appSection-table.php` — registry cho tất cả models:

```php
return [
    'max_bulk_items' => 100,

    'models' => [
        'post' => [
            'model' => Post::class,
            'permission_prefix' => 'posts',
            'default_sort' => ['key' => 'created_at', 'direction' => 'desc'],
            'columns' => [
                ColumnDefinition::number('views', 'table::columns.views')
                    ->visible(false)->width(80)->align('right'),
            ],
        ],

        'category' => [
            'model' => Category::class,
            'permission_prefix' => 'categories',
        ],

        // tag, page — same minimal pattern
    ],
];
```

**~5 dòng/model** nhờ convention-over-configuration.

## i18n

Lang files tại `Resources/lang/{en,vi}/`:

| File               | Nội dung                                         |
| ------------------ | ------------------------------------------------ |
| `actions.php`      | Action labels, confirmation messages             |
| `columns.php`      | Column titles                                    |
| `bulk_changes.php` | Bulk change placeholders (select, enter name...) |
| `statuses.php`     | Status enum labels                               |

FE gửi `X-Locale: vi` → nhận text đã dịch. Namespace: `table::` (registered trong `TableServiceProvider`).

```bash
php artisan translations:import --locale=en --locale=vi
```

## Cache Strategy

| Cache Key                               | TTL    | Invalidation                              |
| --------------------------------------- | ------ | ----------------------------------------- |
| `table_meta:{user_id}:{model}:{locale}` | 1 hour | Column visibility save, permission change |

## Events

| Event                 | Fired When               | Properties                                                              |
| --------------------- | ------------------------ | ----------------------------------------------------------------------- |
| `BulkActionCompleted` | Sau bulk action hoàn tất | `action`, `modelKey`, `successIds`, `successCount`, `failedCount`       |
| `BulkChangeCompleted` | Sau bulk change hoàn tất | `modelKey`, `key`, `value`, `successIds`, `successCount`, `failedCount` |

## Extending

### Thêm model mới

```php
// Configs/appSection-table.php
'product' => [
    'model' => Product::class,
    'permission_prefix' => 'products',
],
```

### Thêm custom BulkChange

```php
final class DescriptionBulkChange extends BulkChangeAbstract
{
    protected string $name = 'description';
    protected string $title = 'table::columns.description';
    protected string $type = 'textarea';
    protected array|string|null $validate = 'required|string|max:1000';
}
```

### Override config từ Model

```php
class Product extends Model
{
    use HasTableConfig;

    public function getTableRowActions(): array
    {
        return [
            ActionDefinition::link('edit', 'table::actions.edit')
                ->icon('ti-edit')->permission('products.edit'),
            // No delete action for products
        ];
    }
}
```

## Change Log

- `2026-03-06`: Initial implementation — Botble CMS table module migration (API-first)
- `2026-03-06`: Added full BulkChange types (Text, Select, Email, Phone, Number, Date, CreatedAt)
- `2026-03-06`: Refactor: config `models` key nesting, `SelectBulkChange` inheritance, `TableServiceProvider` singleton, documented partial failure design
