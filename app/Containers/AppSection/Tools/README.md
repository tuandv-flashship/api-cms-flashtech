### Tools Container

Container path: `app/Containers/AppSection/Tools`

### Scope

- Data synchronization helpers for import/export workflows.
- Upload validation and example file download endpoints.

### API Routes

Route files:
- `app/Containers/AppSection/Tools/UI/API/Routes`

Main route groups:
- Export posts/post-translations/other-translations.
- Import and validate posts/post-translations/other-translations.
- Upload import files and download template examples.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Tools/Configs/data-synchronize.php`
- `app/Containers/AppSection/Tools/Configs/permissions.php`

### Operational Notes

- Import operations should be rate-limited and logged for traceability.
- Validate file format and schema strictly before data write.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
