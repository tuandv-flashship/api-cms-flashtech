### Translation Container

Container path: `app/Containers/AppSection/Translation`

### Scope

- Manage translation groups, locales, and translation JSON payloads.
- Provide translation locale download/list/create/update/delete flows.

### API Routes

Route files:
- `app/Containers/AppSection/Translation/UI/API/Routes`

Main route groups:
- Translation group listing/get/update.
- Translation locale listing/create/delete/download.
- Translation JSON update.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Translation/Configs/appSection-translation.php`
- `app/Containers/AppSection/Translation/Configs/permissions.php`

### Operational Notes

- Locale normalization must be consistent with `Language` container.
- Translation JSON updates should preserve structure expected by clients.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
