### {Container} Container

Container path: `app/Containers/{Section}/{Container}`

### Scope

- Main responsibility 1.
- Main responsibility 2.

### API Routes

Route files:
- `app/Containers/{Section}/{Container}/UI/API/Routes/...`

Auth/permission notes:
- Guards/middleware used by this container routes.

### Main Config

- `app/Containers/{Section}/{Container}/Configs/...`

Common env keys:
- `EXAMPLE_KEY_1`
- `EXAMPLE_KEY_2`

### Operational Notes

- Pagination, throttling, queue, cache, or security notes.
- Any production caveats or defaults.

### Tests

Available tests:
- `app/Containers/{Section}/{Container}/Tests/...`

If no test suite exists yet, use:
- `No dedicated container test suite yet.`

Run:

```bash
php artisan test app/Containers/{Section}/{Container}/Tests
```

### Change Log

- `YYYY-MM-DD`: Initial container README.
