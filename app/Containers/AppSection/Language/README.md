### Language Container

Container path: `app/Containers/AppSection/Language`

### Scope

- Manage system languages and default/current language behavior.
- Provide supported and available language listing APIs.

### API Routes

Route files:
- `app/Containers/AppSection/Language/UI/API/Routes`

Main route groups:
- Language list/create/update/delete/find.
- Set default language and get current language.
- List supported and available languages.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Language/Configs/appSection-languages.php`
- `app/Containers/AppSection/Language/Configs/permissions.php`

### Operational Notes

- Default language changes can impact translation fallback behavior.
- Ensure language code normalization is consistent across containers.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
