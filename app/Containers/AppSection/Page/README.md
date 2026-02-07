### Page Container

Container path: `app/Containers/AppSection/Page`

### Scope

- Manage pages and page translations.
- Provide page listing, detail, and CRUD APIs.

### API Routes

Route files:
- `app/Containers/AppSection/Page/UI/API/Routes`

Main route groups:
- Page list/find/create/update/delete.
- Page translation update.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Page/Configs/page.php`
- `app/Containers/AppSection/Page/Configs/permissions.php`

### Operational Notes

- Ensure slug/localization interactions remain consistent with `Slug` and `Translation`.
- Keep content validation and publication fields centralized in Request/Task layers.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
