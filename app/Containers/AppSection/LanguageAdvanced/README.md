### LanguageAdvanced Container

Container path: `app/Containers/AppSection/LanguageAdvanced`

### Scope

- Advanced language behaviors beyond base language management.
- Handle advanced translation operations such as slug translation updates.

### API Routes

Route files:
- `app/Containers/AppSection/LanguageAdvanced/UI/API/Routes`

Main route groups:
- Slug translation update.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/LanguageAdvanced/Configs/language-advanced.php`

### Operational Notes

- Keep slug update logic synchronized with `Slug` and `Translation` containers.
- Validate locale and entity binding before writing translated slugs.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
