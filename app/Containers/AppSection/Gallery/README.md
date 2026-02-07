### Gallery Container

Container path: `app/Containers/AppSection/Gallery`

### Scope

- Manage gallery entities and gallery translations.
- Provide CRUD and listing APIs for gallery administration.

### API Routes

Route files:
- `app/Containers/AppSection/Gallery/UI/API/Routes`

Main route groups:
- Gallery list/find/create/update/delete.
- Gallery translation update.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Gallery/Configs/permissions.php`

### Operational Notes

- Media linkage and localization consistency should be validated in Tasks.
- Translation updates should not overwrite base gallery fields unexpectedly.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
