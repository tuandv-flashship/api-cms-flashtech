### Blog Container

Container path: `app/Containers/AppSection/Blog`

### Scope

- Manage blog posts, categories, tags, and translations.
- Provide listing, detail, CRUD, and report/view recording endpoints.

### API Routes

Route files:
- `app/Containers/AppSection/Blog/UI/API/Routes`

Main route groups:
- Post CRUD and translation update.
- Category CRUD, tree listing, and translation update.
- Tag CRUD and translation update.
- Blog report and post view record.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Blog/Configs/blog.php`
- `app/Containers/AppSection/Blog/Configs/permissions.php`

### Operational Notes

- Keep translation update flows separated from base entity update flows.
- Permission flags should stay aligned with route-level authorization.

### Tests

Available tests:
- `app/Containers/AppSection/Blog/Tests`

Run:

```bash
php artisan test app/Containers/AppSection/Blog/Tests
```

### Change Log

- `2026-02-07`: Added container README.
