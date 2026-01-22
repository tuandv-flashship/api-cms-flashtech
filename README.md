
### Get Started
``` 
php artisan migrate
php artisan passport:install
php artisan passport:client --password --name="Web Client"
```

```
FORCE_SETTINGS_SEED=true php artisan db:seed --class=App\\Containers\\AppSection\\Setting\\Data\\Seeders\\SettingsSeeder_1

```