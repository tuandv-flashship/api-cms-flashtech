### Revision Container

Container path: `app/Containers/AppSection/Revision`

### Scope

- Store and expose revision history for supported resources.
- Provide revision listing APIs for auditing and rollback workflows.

### API Routes

Route files:
- `app/Containers/AppSection/Revision/UI/API/Routes`

Main route groups:
- Revision list endpoints.

Auth notes:
- Routes are private and intended for authenticated admin/staff APIs.

### Main Config

- `app/Containers/AppSection/Revision/Configs/revision.php`
- `app/Containers/AppSection/Revision/Configs/permissions.php`

### Operational Notes

- Revision retention and payload size should be monitored in production.
- Avoid storing sensitive fields in raw revision snapshots.

### Tests

No dedicated container test suite yet.

### Change Log

- `2026-02-07`: Added container README.
