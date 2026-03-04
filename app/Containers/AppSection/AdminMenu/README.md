# AdminMenu Container

Dynamic admin sidebar menu management with full CRUD, multi-language support, and optimized query performance.

## Overview

This container replaces the static `admin-menu.php` config with a database-driven system, allowing administrators to manage sidebar menu items at runtime through API endpoints.

### Key Features

- **Full CRUD** — Create, Read, Update, Delete menu items via REST API
- **Bulk Save** — Sync entire menu tree in a single request
- **Multi-Language** — DB-backed translations via `HasLanguageTranslations` trait
- **Soft Deletes** — Safely delete items with restore capability
- **Configurable Depth** — Max nesting depth via `ADMIN_MENU_MAX_DEPTH` env var (default: 3)
- **Optimized Queries** — Single flat query + O(n) in-memory tree build (2 queries total)
- **Config Fallback** — Reads from `admin-menu.php` config if DB table is empty
- **Cache Integration** — Uses existing versioned cache system with automatic invalidation

## Database Schema

### `admin_menu_items`

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint PK | Auto-increment |
| `parent_id` | bigint nullable FK | Self-reference for tree structure |
| `key` | string(100) unique | Stable identifier (e.g., `cms-core-dashboard`) |
| `name` | string(255) | Default-locale label |
| `icon` | string(120) nullable | Tabler icon class (e.g., `ti ti-home`) |
| `route` | string(255) nullable | Frontend route path (e.g., `/dashboard`) |
| `permissions` | json nullable | Array of permission strings for access control |
| `children_display` | string(20) | `sidebar` (default) or `panel` |
| `description` | string(255) nullable | Description for panel-type items |
| `priority` | unsigned int | Sort order (lower = higher priority) |
| `is_active` | boolean | Toggle item visibility |
| `deleted_at` | timestamp nullable | Soft delete |

### `admin_menu_item_translations`

| Column | Type | Description |
|--------|------|-------------|
| `lang_code` | string(20) | Language code (composite PK) |
| `admin_menu_items_id` | bigint FK | Reference to parent item (composite PK) |
| `name` | string(255) nullable | Translated label |
| `description` | string(255) nullable | Translated description |

## API Endpoints

All endpoints require `auth:api` middleware.

| Method | URI | Permission | Description |
|--------|-----|------------|-------------|
| `GET` | `/v1/admin-menus` | `admin-menus.index` | List all items as tree |
| `GET` | `/v1/admin-menus/{id}` | `admin-menus.show` | Find item by ID |
| `POST` | `/v1/admin-menus` | `admin-menus.create` | Create item |
| `PUT` | `/v1/admin-menus/{id}` | `admin-menus.update` | Update item |
| `DELETE` | `/v1/admin-menus/{id}` | `admin-menus.delete` | Soft-delete item |
| `PATCH` | `/v1/admin-menus/{id}/restore` | `admin-menus.update` | Restore deleted item |
| `PUT` | `/v1/admin-menus/bulk` | `admin-menus.update` | Sync entire tree |
| `PATCH` | `/v1/admin-menus/{id}/translations` | `admin-menus.update` | Update translation |

### Create Item Example

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

### Create Child Item

```http
POST /v1/admin-menus
Authorization: Bearer {token}

{
    "parent_id": "{hashed_parent_id}",
    "key": "cms-plugins-newsletter-campaigns",
    "name": "Campaigns",
    "icon": "ti ti-send",
    "route": "/newsletter/campaigns",
    "permissions": ["newsletter.campaigns.index"],
    "priority": 10
}
```

### Bulk Save Example

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
            "key": "cms-plugins-blog",
            "name": "Blog",
            "icon": "ti ti-article",
            "priority": 3,
            "children_display": "sidebar",
            "children": [
                {
                    "key": "cms-plugins-blog-post",
                    "name": "Posts",
                    "icon": "ti ti-file-text",
                    "route": "/blog/posts",
                    "permissions": ["posts.index"],
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
    "name": "Bảng điều khiển",
    "description": "Trang chính của hệ thống quản trị"
}
```

## Configuration

### Max Depth

Set via environment variable:

```env
ADMIN_MENU_MAX_DEPTH=3
```

Config file: `Configs/admin-menu-container.php`

### Permissions

Defined in `Configs/permissions.php`, under `core.system`:

- `admin-menus.index` — View admin menu items
- `admin-menus.show` — View single item
- `admin-menus.create` — Create items
- `admin-menus.update` — Update items, translations, bulk save, restore
- `admin-menus.delete` — Soft-delete items

### Multi-Language

Registered in `LanguageAdvanced` config with translatable columns: `name`, `description`.

Use `X-Locale` header in API requests to get translated values.

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

## Architecture

```
AdminMenu/
├── Actions/              # 8 actions (CRUD + Bulk + Restore + Translation)
├── Configs/
│   ├── admin-menu-container.php  # max_depth config
│   └── permissions.php
├── Data/
│   ├── Migrations/       # 2 migrations (items + translations)
│   ├── Repositories/
│   └── Seeders/          # Import from config
├── Models/
│   ├── AdminMenuItem.php            # HasLanguageTranslations + SoftDeletes
│   └── AdminMenuItemTranslation.php # Composite PK model
├── Tasks/                # 8 tasks (CRUD + tree build + translation)
└── UI/API/
    ├── Controllers/      # 8 invokable controllers
    ├── Requests/         # 8 request classes with depth validation
    ├── Routes/           # 8 route files (v1.private)
    └── Transformers/     # Dual-mode: model + array support
```

## Testing

```bash
# Run container tests
php artisan test app/Containers/AppSection/AdminMenu/Tests/

# Run AdminMenu support class tests
php artisan test --filter=AdminMenuTest
```
