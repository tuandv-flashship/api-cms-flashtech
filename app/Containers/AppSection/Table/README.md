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
    → 4. Enrich searchable + search_operator từ Repository $fieldSearchable
    → 5. Auto-generate CRUD actions từ permission_prefix
    → 6. Filter ALL by user permissions ($user->can())
    → 7. Merge user column visibility prefs (settings table)
    → 8. Translate ALL labels via trans()
    → 9. Cache result
    ↓
FE nhận JSON: columns[], header_actions[], row_actions[], bulk_actions[], bulk_changes[]
    → Render table, render action buttons, render bulk dropdown
    → Dynamic search UI dựa trên search_operator
    → Zero permission logic phía client
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
        "options": { "published": "Đã xuất bản", "draft": "Bản nháp" } },
      { "key": "is_featured", "title": "Nổi bật", "type": "boolean", "sortable": true,
        "searchable": true, "search_operator": "=",
        "visible": true, "width": 100, "align": "center", "priority": 6 },
      { "key": "created_at", "title": "Ngày tạo", "type": "date", "sortable": true,
        "searchable": false,
        "visible": true, "width": 160, "align": "center", "priority": 99 }
    ],
    "header_actions": [
      { "name": "create", "label": "Tạo mới", "icon": "ti-plus", "color": "primary",
        "type": "link", "url_pattern": "/blog/posts/create",
        "permission": "posts.create", ... }
    ],
    "row_actions": [
      { "name": "edit", "label": "Sửa", "type": "link",
        "url_pattern": "/blog/posts/{id}/edit",
        "permission": "posts.edit", ..., "confirmation": null },
      { "name": "delete", "label": "Xoá", "type": "action",
        "method": "DELETE", "url_pattern": "/v1/blog/posts/{id}",
        "permission": "posts.destroy", ...,
        "confirmation": { "title": "Xác nhận xoá", ... } }
    ],
    "bulk_actions": [
      { "action": "delete", "label": "Xoá đã chọn", "icon": "ti-trash", "color": "danger",
        "permission": "posts.destroy",
        "confirmation": { "title": "Xác nhận xoá hàng loạt", ... } }
    ],
    "bulk_changes": [
      { "key": "status", "title": "Trạng thái", "type": "select",
        "choices": { "published": "Đã xuất bản", "draft": "Bản nháp" },
        "permission": "posts.edit" },
      { "key": "name", "title": "Tên", "type": "text", "placeholder": "Nhập tên...",
        "permission": "posts.edit" }
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

## Auto-Detection Rules

### Columns

| Model Property            | Factory Method                         | Column Config                                          |
| ------------------------- | -------------------------------------- | ------------------------------------------------------ |
| Primary key               | `ColumnDefinition::id()`               | type: `id`, width: 70, center, priority: 0             |
| `'image'` in fillable     | `ColumnDefinition::image()`            | type: `image`, width: 70, center, priority: 1          |
| `'name'` in fillable      | `ColumnDefinition::name()`             | type: `text`, searchable, emptyState, priority: 2      |
| `'email'` in fillable     | `ColumnDefinition::email()`            | type: `email`, searchable, `mailto:` link, priority: 3 |
| `'phone'` in fillable     | `ColumnDefinition::phone()`            | type: `phone`, searchable, `tel:` link, priority: 4    |
| `'status'` Enum cast      | `ColumnDefinition::status($enum)`      | type: `status`, options from enum, priority: 5         |
| `'is_featured'` bool cast | `ColumnDefinition::boolean()`          | type: `boolean`, width: 100, priority: 6               |
| `usesTimestamps()`        | `ColumnDefinition::date('created_at')` | type: `date`, width: 160, emptyState, priority: 99     |

> **Note:** `searchable` và `search_operator` **không phụ thuộc** vào auto-detection rules ở trên. Chúng được resolve tự động từ Repository `$fieldSearchable` (Step 4 trong pipeline). Nếu config có `repository` key → field nào nằm trong `$fieldSearchable` sẽ có `searchable: true` + `search_operator`.

### Search Operators

Danh sách tất cả operators hỗ trợ bởi l5-repository `RequestCriteria`:

| Operator  | SQL equivalent              | Mô tả                                  | FE nên render              |
| --------- | --------------------------- | --------------------------------------- | -------------------------- |
| `=`       | `WHERE f = v`               | So sánh chính xác                       | Dropdown (nếu có `options`) / Text input |
| `like`    | `WHERE f LIKE %v%`          | Tìm kiếm gần đúng (tự thêm `%`)        | Text input (free-text)     |
| `ilike`   | `WHERE f ILIKE %v%`         | Like không phân biệt hoa thường (PgSQL) | Text input                 |
| `in`      | `WHERE f IN (v1, v2, ...)`  | Nhiều giá trị, phân cách bằng `,`       | Multi-select               |
| `between` | `WHERE f BETWEEN v1 AND v2` | Khoảng, 2 giá trị phân cách bằng `,`   | Range picker               |
| `>`       | `WHERE f > v`               | Lớn hơn                                | Number input + label       |
| `<`       | `WHERE f < v`               | Nhỏ hơn                                | Number input + label       |
| `>=`      | `WHERE f >= v`              | Lớn hơn hoặc bằng                      | Number input + label       |
| `<=`      | `WHERE f <= v`              | Nhỏ hơn hoặc bằng                      | Number input + label       |
| `<>`      | `WHERE f <> v`              | Khác                                    | Text input + label         |

**FE search request format** (sử dụng `RequestCriteria` của l5-repository):

```
# Search chung tất cả searchable fields
GET /v1/blog/posts?search=hello

# Search theo field cụ thể
GET /v1/blog/posts?search=name:hello;status:published

# Kết hợp AND (mặc định OR)
GET /v1/blog/posts?search=name:hello;status:published&searchJoin=and

# Override operator từ client
GET /v1/blog/posts?search=hello&searchFields=name:like;status:=

# Search IN (nhiều giá trị)
GET /v1/blog/posts?search=status:published,draft&searchFields=status:in

# Search BETWEEN (khoảng)
GET /v1/blog/posts?search=created_at:2024-01-01,2024-12-31&searchFields=created_at:between
```

> **Lưu ý:** Operators `>`, `<`, `>=`, `<=`, `<>` cần được thêm vào `config/repository.php` → `acceptedConditions` nếu muốn client override qua `searchFields` param. Khi define trong `$fieldSearchable` thì server-side default luôn hoạt động.

### Display Properties (FE metadata)

| Property          | Method                       | JSON Output                              | FE Renders         |
| ----------------- | ---------------------------- | ---------------------------------------- | ------------------ |
| `copyable`        | `->copyable()`               | `"copyable": true`                       | Copy button        |
| `maskable`        | `->maskable('*', 4)`         | `"mask": {"char":"*","length":4}`        | `****1234`         |
| `emptyState`      | `->emptyState('—')`          | `"empty_state": "—"`                     | "—" when null      |
| `linkable`        | `->linkable('/edit/{id}')`   | `"link": {"url_pattern":"..."}`          | Clickable link     |
| `dateFormat`      | `->dateFormat('DD/MM/YYYY')` | `"date_format": "DD/MM/YYYY"`            | Formatted date     |
| `limit`           | `->limit(50)`                | `"limit": 50`                            | Truncated text     |
| `icon`            | `->icon('ti-star')`          | `"icon": {"name":"ti-star"}`             | Icon prefix/suffix |
| `color`           | `->color('danger')`          | `"color": "danger"`                      | Colored text       |
| `searchOperator`  | `->searchOperator('like')`   | `"search_operator": "like"`              | Search UI type     |

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
            'repository' => PostRepository::class,  // ← enables search_operator sync
            'permission_prefix' => 'posts',
            'default_sort' => ['key' => 'created_at', 'direction' => 'desc'],
            'columns' => [
                ColumnDefinition::number('views', 'table::columns.views')
                    ->visible(false)->width(80)->align('right'),
            ],
        ],

        'category' => [
            'model' => Category::class,
            'repository' => CategoryRepository::class,
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

- `2026-03-23`: Search operator sync — auto-enrich `searchable` + `search_operator` từ Repository `$fieldSearchable`
- `2026-03-06`: Initial implementation — Botble CMS table module migration (API-first)
- `2026-03-06`: Added full BulkChange types (Text, Select, Email, Phone, Number, Date, CreatedAt)
- `2026-03-06`: Refactor: config `models` key nesting, `SelectBulkChange` inheritance, `TableServiceProvider` singleton, documented partial failure design
