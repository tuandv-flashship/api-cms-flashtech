# Menu Container

Navigation menu management for the CMS. Supports hierarchical menu nodes with multi-language translations, reference linking (Pages, Posts, Categories, Tags), location-based retrieval, and built-in caching.

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Database Schema](#database-schema)
- [API Endpoints](#api-endpoints)
- [Models](#models)
- [Configuration](#configuration)
- [Caching Strategy](#caching-strategy)
- [Events & Listeners](#events--listeners)
- [Support Classes](#support-classes)
- [Permissions](#permissions)
- [Testing](#testing)

---

## Architecture Overview

```
Menu Container
├── Actions/          # Business logic orchestration
├── Configs/          # menu.php, permissions.php
├── Data/
│   ├── Migrations/   # 4 tables: menus, menu_locations, menu_nodes, menu_nodes_translations
│   ├── Repositories/ # Eloquent repositories
│   └── Seeders/      # Default menu data
├── Events/           # MenuSavedEvent, MenuDeletedEvent, MenuNodeTranslationUpdatedEvent
├── Listeners/        # Cache invalidation, URL sync, deleted reference cleanup
├── Models/           # Menu, MenuLocation, MenuNode, MenuNodeTranslation
├── Providers/        # MenuServiceProvider (event registration)
├── Supports/         # MenuCache, MenuNodeResolver
├── Tasks/            # Granular DB operations
├── Tests/            # Unit + Functional tests
└── UI/API/
    ├── Controllers/  # 8 controllers
    ├── Requests/     # Form validation
    ├── Routes/       # Public + Private endpoints
    └── Transformers/ # API response formatting
```

---

## Database Schema

### `menus`
| Column   | Type   | Description          |
|----------|--------|----------------------|
| id       | bigint | Primary key          |
| name     | string | Menu name            |
| slug     | string | URL-friendly slug    |
| status   | string | `published` / `draft`|

### `menu_locations`
| Column   | Type   | Description               |
|----------|--------|---------------------------|
| id       | bigint | Primary key               |
| menu_id  | bigint | FK → menus.id             |
| location | string | Slot name (e.g. `main-menu`, `footer`) |

### `menu_nodes`
| Column         | Type    | Description                            |
|----------------|---------|----------------------------------------|
| id             | bigint  | Primary key                            |
| menu_id        | bigint  | FK → menus.id                          |
| parent_id      | bigint  | Self-referencing FK (null = root node) |
| reference_type | string  | Model class shorthand (`page`, `post`, `category`, `tag`) |
| reference_id   | bigint  | FK to the referenced model             |
| url            | string  | Custom URL (used when no reference)    |
| title          | string  | Display text                           |
| url_source     | string  | Resolved URL from reference            |
| title_source   | string  | Resolved title from reference          |
| icon_font      | string  | Icon class (e.g. `ti ti-home`)         |
| css_class      | string  | Custom CSS class                       |
| target         | string  | Link target (`_self`, `_blank`)        |
| has_child      | boolean | Pre-computed flag for tree queries      |
| position       | integer | Sort order within siblings             |

### `menu_nodes_translations`
| Column        | Type   | Description            |
|---------------|--------|------------------------|
| lang_code     | string | Language code           |
| menu_nodes_id | bigint | FK → menu_nodes.id     |
| title         | string | Translated title        |
| url           | string | Translated URL          |

---

## API Endpoints

### Admin (Private — requires `auth:api`)

| Method   | Endpoint                              | Controller                         | Permission       | Description                       |
|----------|---------------------------------------|------------------------------------|-------------------|-----------------------------------|
| `GET`    | `/v1/menus`                           | ListMenusController                | `menus.index`     | List all menus                    |
| `GET`    | `/v1/menus/{id}`                      | FindMenuByIdController             | `menus.show`      | Get menu by ID (with nodes tree)  |
| `POST`   | `/v1/menus`                           | CreateMenuController               | `menus.create`    | Create menu with nodes & locations|
| `PUT`    | `/v1/menus/{id}`                      | UpdateMenuController               | `menus.update`    | Update menu, nodes & locations    |
| `DELETE` | `/v1/menus/{id}`                      | DeleteMenuController               | `menus.delete`    | Delete menu                       |
| `GET`    | `/v1/menus/options`                   | GetMenuOptionsController           | `menus.index`     | Get available reference types     |
| `PUT`    | `/v1/menus/{id}/nodes/{nodeId}/translation` | UpdateMenuNodeTranslationController | `menus.update` | Update node translation           |

### Public (No auth required)

| Method | Endpoint                          | Controller                   | Description                        |
|--------|-----------------------------------|------------------------------|------------------------------------|
| `GET`  | `/v1/menus/location/{location}`   | GetMenuByLocationController  | Get menu tree by location slug     |

#### Public API — Query Parameters

| Parameter   | Type   | Default | Description                     |
|-------------|--------|---------|---------------------------------|
| `locale`    | string | app locale | Language code for translations |

#### Public API — Response Example

```json
{
  "data": {
    "id": "hashed_id",
    "name": "Main Navigation",
    "slug": "main-navigation",
    "nodes": [
      {
        "id": "hashed_node_id",
        "title": "Home",
        "url": "/",
        "icon_font": "ti ti-home",
        "target": "_self",
        "position": 0,
        "children": [
          {
            "id": "hashed_child_id",
            "title": "About",
            "url": "/about",
            "children": []
          }
        ]
      }
    ]
  }
}
```

---

## Models

### Menu
- **Relationships**: `locations()` → HasMany MenuLocation, `nodes()` → HasMany MenuNode
- **Scopes**: `scopePublished()` — filter by `status = 'published'`

### MenuNode
- **Relationships**: `menu()`, `parent()`, `children()` (ordered by position), `translations()`
- **Translations**: `title` and `url` are auto-translated via `HasLanguageTranslations` trait
- **Casts**: `has_child` → boolean, `position` / `reference_id` / `menu_id` / `parent_id` → integer

### MenuLocation
- Links a menu to a named location slot (e.g. `main-menu`, `footer`)

### MenuNodeTranslation
- i18n support for menu node `title` and `url` per language code

---

## Configuration

**`Configs/menu.php`**

```php
return [
    // Models that can be linked as menu node references
    'reference_types' => [
        'page'     => Page::class,
        'category' => Category::class,
        'post'     => Post::class,
        'tag'      => Tag::class,
    ],

    // Pre-defined menu location slots
    'locations' => [
        'main-menu' => 'Main Navigation',
        'footer'    => 'Footer Menu',
    ],

    // Cache settings
    'cache' => [
        'ttl_seconds' => env('MENU_CACHE_TTL_SECONDS', 86400), // 24 hours
    ],
];
```

---

## Caching Strategy

Cached per **location + locale + version**. Uses versioned keys for lock-free invalidation.

| Aspect          | Detail                                         |
|-----------------|------------------------------------------------|
| **Cache Key**   | `menu:location:{location}:version:{v}:locale:{locale}` |
| **TTL**         | Configurable via `MENU_CACHE_TTL_SECONDS` (default: 86400s = 24h) |
| **Invalidation**| Version-based — incrementing version makes old keys expire naturally |

### Invalidation Triggers

| Event                              | Action                          |
|------------------------------------|---------------------------------|
| Menu created/updated               | `MenuCache::forgetByMenuId()`   |
| Menu deleted                       | `MenuCache::forgetByLocation()` |
| Menu node translation updated      | `MenuCache::forgetByMenuId()`   |
| Referenced model updated (Page, Post, Category, Tag) | URL re-resolved + cache invalidated |
| Referenced model deleted           | Node cleaned up + cache invalidated |

### Class: `MenuCache`

```php
$menuCache = app(MenuCache::class);

// Cache a menu query by location
$tree = $menuCache->rememberByLocation('main-menu', 'vi', fn () => $buildTreeTask->run(...));

// Invalidate for a specific menu
$menuCache->forgetByMenuId($menuId);

// Invalidate for a specific location
$menuCache->forgetByLocation('main-menu');
```

---

## Events & Listeners

### Events

| Event                            | Payload      | Fired When                      |
|----------------------------------|--------------|---------------------------------|
| `MenuSavedEvent`                 | `int $menuId`| Menu created or updated         |
| `MenuDeletedEvent`               | `int $menuId, array $locations` | Menu deleted  |
| `MenuNodeTranslationUpdatedEvent`| `int $menuId`| Node translation updated        |

### Listeners

| Listener                                  | Handles                         | Purpose                           |
|-------------------------------------------|---------------------------------|-----------------------------------|
| `InvalidateMenuCacheListener`             | MenuSaved, MenuDeleted, TranslationUpdated | Clears location cache  |
| `UpdateMenuNodeUrlListener`               | PageUpdated, PostUpdated, CategoryUpdated, TagUpdated | Re-resolves `url_source` on referenced model changes |
| `HandleDeletedReferenceForMenuNodeListener`| PageDeleted, PostDeleted, CategoryDeleted, TagDeleted | Nullifies reference on deleted models |

### Event Registration

All events are registered in `MenuServiceProvider::boot()` using `Event::listen()`. Registration is guarded by a static flag to prevent duplicate binding.

---

## Support Classes

### `MenuNodeResolver`

Resolves reference data (title, URL) for menu nodes. Handles batch resolution for performance.

```php
$resolver = app(MenuNodeResolver::class);

// Single reference
$data = $resolver->resolve('page', 5);
// → ['reference_type' => 'App\...\Page', 'title' => 'About Us', 'url' => '/about-us']

// Batch resolution (N+1 safe)
$data = $resolver->resolveMany([
    ['reference_type' => 'page', 'reference_id' => 5],
    ['reference_type' => 'post', 'reference_id' => 12],
]);
```

Key features:
- **Normalizes** shorthand types (`'page'` → `Page::class`) via config
- **Batch queries** — groups by type, single query per model class
- **Translation-aware** — applies `LanguageAdvancedManager` to queries
- **Slug resolution** — extracts URL from `slugable` relationship if available

---

## Permissions

Hierarchical permission structure under `core.cms → plugins.menu`:

```
core.cms
└── plugins.menu
    └── menus.index       — List menus
        ├── menus.show    — View single menu
        ├── menus.create  — Create menu
        ├── menus.update  — Update menu & nodes
        └── menus.delete  — Delete menu
```

---

## Testing

```bash
# Run all Menu tests
php artisan test --filter=Menu

# Run specific test suites
php artisan test app/Containers/AppSection/Menu/Tests/Functional/API/MenuCrudTest.php
php artisan test app/Containers/AppSection/Menu/Tests/Functional/API/PublicMenuApiTest.php
php artisan test app/Containers/AppSection/Menu/Tests/Functional/API/MenuPerformanceContractTest.php
```

### Test Structure

```
Tests/
├── ContainerTestCase.php              # Base test case with helpers
├── FunctionalTestCase.php             # Functional test base
├── Functional/
│   ├── ApiTestCase.php                # API test base with auth setup
│   └── API/
│       ├── MenuCrudTest.php           # CRUD operations (create, update, delete, list, find)
│       ├── MenuPerformanceContractTest.php  # N+1 query prevention
│       └── PublicMenuApiTest.php      # Public location-based API
└── Unit/
    └── ...                            # Unit tests for tasks, supports, etc.
```
