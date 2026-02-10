### CustomField Container

Container path: `app/Containers/AppSection/CustomField`

### Scope

- Manage custom field groups and custom field box data.
- Support CRUD and listing for field-group management.

### API Routes

Route files:
- `app/Containers/AppSection/CustomField/UI/API/Routes`

Main route groups:
- Field group list/find/create/update/delete.
- Custom field box listing.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/CustomField/Configs/custom-field.php`
- `app/Containers/AppSection/CustomField/Configs/permissions.php`

### Operational Notes

- Keep field schema validation centralized in Request/Task layers.
- Changes to field schema should be backward-compatible with stored values.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
