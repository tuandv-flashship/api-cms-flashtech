# AdminMenu Container

Dynamic admin sidebar menu management with full CRUD, multi-language support, section grouping, and optimized query performance.

## Overview

This container replaces the static `admin-menu.php` config with a database-driven system, allowing administrators to manage sidebar menu items at runtime through API endpoints.

### Key Features

- **Full CRUD** — Create, Read, Update, Delete menu items via REST API
- **Bulk Save** — Sync entire menu tree in a single request
- **Section Grouping** — Group children by `section` (e.g., "Localization", "Monitoring") for panel-style pages
- **Multi-Language** — DB-backed translations via `HasLanguageTranslations` trait, including `section` labels
- **Soft Deletes** — Safely delete items with restore capability
- **Configurable Depth** — Max nesting depth via `ADMIN_MENU_MAX_DEPTH` env var (default: 3)
- **Optimized Queries** — Single flat query + O(n) in-memory tree build (2 queries total)
- **Config Fallback** — Reads from `admin-menu.php` config if DB table is empty
- **Cache Integration** — Uses existing versioned cache system with automatic invalidation

---

## Database Schema

### `admin_menu_items`

| Column             | Type                 | Description                                        |
| ------------------ | -------------------- | -------------------------------------------------- |
| `id`               | bigint PK            | Auto-increment                                     |
| `parent_id`        | bigint nullable FK   | Self-reference for tree structure (cascade delete) |
| `key`              | string(100) unique   | Stable identifier (e.g., `cms-core-dashboard`)     |
| `name`             | string(255)          | Default-locale label                               |
| `icon`             | string(120) nullable | Tabler icon class (e.g., `ti ti-home`)             |
| `route`            | string(255) nullable | Frontend route path (e.g., `/dashboard`)           |
| `permissions`      | json nullable        | Array of permission strings for access control     |
| `children_display` | string(20)           | `sidebar` (default) or `panel`                     |
| `section`          | string(100) nullable | Section group name for panel-type children         |
| `description`      | string(255) nullable | Description for panel-type items                   |
| `priority`         | unsigned int         | Sort order (lower = higher priority)               |
| `is_active`        | boolean              | Toggle item visibility                             |
| `deleted_at`       | timestamp nullable   | Soft delete                                        |

### `admin_menu_item_translations`

| Column                | Type                 | Description                                             |
| --------------------- | -------------------- | ------------------------------------------------------- |
| `lang_code`           | string(20)           | Language code (composite PK)                            |
| `admin_menu_items_id` | bigint FK            | Reference to parent item (composite PK, cascade delete) |
| `name`                | string(255) nullable | Translated label                                        |
| `description`         | string(255) nullable | Translated description                                  |
| `section`             | string(100) nullable | Translated section group name                           |

---

## API Endpoints

All endpoints require `auth:api` middleware.

| Method   | URI                                 | Permission           | Description                                   |
| -------- | ----------------------------------- | -------------------- | --------------------------------------------- |
| `GET`    | `/v1/admin-menus`                   | `admin-menus.index`  | List all items as tree                        |
| `GET`    | `/v1/admin-menus/{id}`              | `admin-menus.show`   | Find item by ID                               |
| `POST`   | `/v1/admin-menus`                   | `admin-menus.create` | Create item                                   |
| `PUT`    | `/v1/admin-menus/{id}`              | `admin-menus.update` | Update item                                   |
| `DELETE` | `/v1/admin-menus/{id}`              | `admin-menus.delete` | Soft-delete item                              |
| `PATCH`  | `/v1/admin-menus/{id}/restore`      | `admin-menus.update` | Restore deleted item                          |
| `PUT`    | `/v1/admin-menus/bulk`              | `admin-menus.update` | Sync entire tree                              |
| `PATCH`  | `/v1/admin-menus/{id}/translations` | `admin-menus.update` | Update translation                            |
| `GET`    | `/v1/admin-menus/sections`          | `admin-menus.index`  | List distinct sections grouped by parent menu |

---

### i18n

- **Đọc (GET)**: gửi `X-Locale` header → response trả `title`, `description`, `section` đã translate.
- **Ghi (PATCH translations)**: gửi `lang_code` + fields cần dịch (`name`, `description`, `section`).

---

### Response Node Format (List / Find)

Mỗi node trong response có các fields sau:

| Field              | Type         | Mô tả                                                |
| ------------------ | ------------ | ---------------------------------------------------- |
| `id`               | string       | Hashed ID (dùng cho PUT/DELETE/PATCH)                |
| `key`              | string       | Unique key (e.g., `cms-core-settings`)               |
| `name`             | string       | Original name (default locale)                       |
| `title`            | string       | Translated name (theo `X-Locale`)                    |
| `icon`             | string/null  | Tabler icon class                                    |
| `route`            | string/null  | FE route path                                        |
| `permissions`      | array/null   | Permission strings                                   |
| `children_display` | string       | `sidebar` hoặc `panel`                               |
| `section`          | string/null  | Section group name (translated)                      |
| `description`      | string/null  | Translated description                               |
| `priority`         | int          | Sort order (lower = higher)                          |
| `is_active`        | bool         | Active status                                        |
| `deleted_at`       | string/null  | ISO 8601 timestamp nếu soft-deleted                  |
| `children`         | array        | Flat list tất cả children                            |
| `sections`         | array/absent | **Chỉ xuất hiện khi `children_display === "panel"`** |

#### `sections` format (auto-grouped by BE)

```json
"sections": [
    {
        "name": "Bản địa hóa",
        "items": [
            { "title": "Ngôn ngữ", "icon": "ti ti-language", ... },
            { "title": "Bản dịch", "icon": "ti ti-a-b", ... }
        ]
    },
    {
        "name": "Hệ thống",
        "items": [...]
    }
]
```

> FE chỉ cần loop `sections` để render grouped layout — **không cần `groupBy`**.

### Create Item

```http
POST /v1/admin-menus
Authorization: Bearer {token}
Content-Type: application/json

{
    "key": "cms-plugins-newsletter",
    "name": "Newsletter",
    "icon": "ti ti-mail",
    "route": "/newsletter",
    "permissions": ["newsletter.index"],
    "priority": 60,
    "is_active": true
}
```

### Create Child Item (with section grouping)

```http
POST /v1/admin-menus
Authorization: Bearer {token}

{
    "parent_id": "{hashed_parent_id}",
    "key": "cms-settings-email",
    "name": "Email",
    "description": "Manage email settings and templates",
    "icon": "ti ti-mail",
    "route": "/settings/email",
    "permissions": ["settings.email"],
    "section": "General",
    "priority": 10
}
```

> `section` chỉ có ý nghĩa cho children của parent có `children_display: "panel"`. Dùng để gom nhóm các items trên cùng trang panel.

### Update Item

```http
PUT /v1/admin-menus/{id}
Authorization: Bearer {token}

{
    "name": "Updated Name",
    "icon": "ti ti-edit",
    "section": "Localization",
    "priority": 5
}
```

### Bulk Save (Sync entire tree)

```http
PUT /v1/admin-menus/bulk
Authorization: Bearer {token}

{
    "items": [
        {
            "key": "cms-core-dashboard",
            "name": "Dashboard",
            "icon": "ti ti-home",
            "route": "/dashboard",
            "priority": 1,
            "children": []
        },
        {
            "key": "cms-core-settings",
            "name": "Settings",
            "icon": "ti ti-settings",
            "route": "/settings",
            "children_display": "panel",
            "priority": 9999,
            "children": [
                {
                    "key": "cms-settings-languages",
                    "name": "Languages",
                    "icon": "ti ti-language",
                    "route": "/settings/languages",
                    "section": "Localization",
                    "priority": 10
                }
            ]
        }
    ]
}
```

### Update Translation

```http
PATCH /v1/admin-menus/{id}/translations
Authorization: Bearer {token}

{
    "lang_code": "vi",
    "name": "Ngôn ngữ",
    "description": "Quản lý ngôn ngữ và bản địa hoá",
    "section": "Bản địa hóa"
}
```

---

### List Sections (Combobox data for FE)

Trả danh sách distinct `section` đang dùng, grouped by parent admin menu. Dùng cho FE render combobox khi admin tạo/edit menu item — tránh typo section name.

```http
GET /v1/admin-menus/sections
Authorization: Bearer {token}
X-Locale: vi
```

**Response (vi):**

```json
{
    "data": [
        {
            "id": "cms-core-settings",
            "title": "Cài đặt",
            "sections": ["Bản địa hóa"]
        },
        {
            "id": "cms-core-system",
            "title": "Quản trị hệ thống",
            "sections": ["Người dùng & Quyền", "Giám sát", "Hệ thống"]
        }
    ]
}
```

**Response (en):**

```json
{
    "data": [
        {
            "id": "cms-core-settings",
            "title": "Settings",
            "sections": ["Localization"]
        },
        {
            "id": "cms-core-system",
            "title": "Platform Administration",
            "sections": ["Users & Permissions", "Monitoring", "System"]
        }
    ]
}
```

---

## Section Grouping — FE Integration

### Concept

Trong ảnh Botble CMS, trang "Cài đặt" gom các menu con thành **section groups** ("Chung", "Bản địa hóa", "Khác"). Container hỗ trợ pattern này qua field `section` trên children items:

- Parent có `children_display: "panel"` → children hiển thị dạng grid thay vì sidebar
- Children có cùng `section` → FE gom vào group, hiển thị section header

### Display Modes

| `children_display` | Hiển thị                                                      | Section có ý nghĩa? |
| ------------------ | ------------------------------------------------------------- | ------------------- |
| `sidebar`          | Dropdown trong sidebar (Blog → Posts, Categories...)          | ❌ Không            |
| `panel`            | Grid trên trang riêng (Settings → Languages, Translations...) | ✅ Có               |

### FE Rendering Logic

```javascript
// 1. Lấy menu tree
const menu = await fetch("/v1/admin-menus", {
    headers: { "X-Locale": locale },
});

// 2. Khi user click parent (children_display === 'panel'), render grid:
const grouped = Object.groupBy(
    parent.children,
    (item) => item.section ?? "General",
);

// 3. Render
Object.entries(grouped).forEach(([sectionName, items]) => {
    // Render section header: <h3>{sectionName}</h3>
    // Render items grid: icon + title + description
});
```

### API Response Example (panel parent)

```json
{
    "id": "cms-core-system",
    "title": "Quản trị hệ thống",
    "children_display": "panel",
    "children": [
        {
            "title": "Người dùng",
            "section": "Người dùng & Quyền",
            "icon": "ti ti-user",
            "description": "Quản lý người dùng..."
        },
        {
            "title": "Vai trò & Quyền",
            "section": "Người dùng & Quyền",
            "icon": "ti ti-lock",
            "description": "Quản lý vai trò..."
        },
        {
            "title": "Nhật ký hoạt động",
            "section": "Giám sát",
            "icon": "ti ti-clipboard-list",
            "description": "Theo dõi hoạt động..."
        },
        {
            "title": "Nhật ký yêu cầu",
            "section": "Giám sát",
            "icon": "ti ti-report-analytics",
            "description": "Giám sát nhật ký..."
        },
        {
            "title": "Thông tin hệ thống",
            "section": "Hệ thống",
            "icon": "ti ti-info-circle",
            "description": "Xem môi trường..."
        },
        {
            "title": "Quản lý bộ nhớ đệm",
            "section": "Hệ thống",
            "icon": "ti ti-database",
            "description": "Xóa và quản lý cache..."
        }
    ]
}
```

FE `Object.groupBy()` → 3 groups: **Người dùng & Quyền** (2), **Giám sát** (2), **Hệ thống** (2).

---

## Configuration

### Max Depth

Set via environment variable:

```env
ADMIN_MENU_MAX_DEPTH=3
```

Config file: `Configs/admin-menu-container.php`

### Permissions

Defined in `Configs/permissions.php`, under `core.system`:

- `admin-menus.index` — View admin menu items + sections
- `admin-menus.show` — View single item
- `admin-menus.create` — Create items
- `admin-menus.update` — Update items, translations, bulk save, restore
- `admin-menus.delete` — Soft-delete items

### Multi-Language

Registered in `LanguageAdvanced` config with translatable columns: `name`, `description`, `section`.

Use `X-Locale` header in API requests to get translated values.

---

## Setup

### Migration

```bash
php artisan migrate
```

### Seed Data (import from config)

```bash
php artisan db:seed --class="App\Containers\AppSection\AdminMenu\Data\Seeders\AdminMenuSeeder_1"
```

### Sync Permissions

```bash
php artisan apiato:permissions-sync
```

---

## Architecture

```
AdminMenu/
├── Actions/              # 9 actions (CRUD + Bulk + Restore + Translation + Sections)
├── Configs/
│   ├── admin-menu.php           # Default menu structure
│   ├── admin-menu-container.php # max_depth, cache_ttl config
│   └── permissions.php
├── Data/
│   ├── Migrations/       # 3 migrations (items + translations + section)
│   ├── Repositories/
│   └── Seeders/          # Import from config with section + translations
├── Models/
│   ├── AdminMenuItem.php            # HasLanguageTranslations + SoftDeletes
│   └── AdminMenuItemTranslation.php # Composite PK model
├── Supports/
│   └── AdminMenu.php     # Tree build + permission filter + cache + section output
├── Tasks/                # 8 tasks
└── UI/API/
    ├── Controllers/      # 9 invokable controllers
    ├── Requests/         # 8 request classes with depth validation
    ├── Routes/           # 9 route files (v1.private)
    └── Transformers/     # Dual-mode: model + array support
```

## Testing

```bash
# Run container tests
php artisan test app/Containers/AppSection/AdminMenu/Tests/

# Run AdminMenu support class tests
php artisan test --filter=AdminMenuTest
```
