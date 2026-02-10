### Slug Container

Container path: `app/Containers/AppSection/Slug`

### Scope

- Generate and persist unique slugs for supported resources.
- Provide slug creation and support services for other containers.

### API Routes

Route files:
- `app/Containers/AppSection/Slug/UI/API/Routes`

Main route groups:
- Slug creation.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Slug/Configs/slug.php`

### Operational Notes

- Slug uniqueness constraints should remain aligned with DB indexes.
- Coordinate slug translation behavior with `LanguageAdvanced` and `Translation`.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
