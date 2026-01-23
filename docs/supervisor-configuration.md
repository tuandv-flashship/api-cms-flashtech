
## Supervisor Configuration
Dưới đây là hướng dẫn setup **Supervisor** để chạy background worker cho project **Laravel `api-cms-flashtech`** (thường dùng cho `queue:work`, có thể kèm `schedule:work` nếu bạn muốn chạy scheduler kiểu daemon).

---

## 1) Cài Supervisor (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install -y supervisor
sudo systemctl enable supervisor
sudo systemctl start supervisor
sudo systemctl status supervisor
```

---

## 2) Xác định thông tin cần có trước khi cấu hình

Bạn cần biết:

* **Đường dẫn project** (ví dụ):
  `/var/www/api-cms-flashtech`
* **User chạy PHP** (thường): `www-data` hoặc user deploy (vd: `deploy`)
* **Đường dẫn PHP**:
  chạy `which php` để lấy path (vd: `/usr/bin/php`)
* **Queue driver** đang dùng trong `.env`: `QUEUE_CONNECTION=redis` (phổ biến) hoặc `database`

> Nếu dùng Redis: đảm bảo Redis đã chạy + Laravel cấu hình kết nối OK.

---

## 3) Tạo file Supervisor config cho Queue Worker

Tạo file:

```bash
sudo nano /etc/supervisor/conf.d/api-cms-flashtech-queue.conf
```

Dán mẫu (chỉnh lại path/user cho đúng):

```ini
[program:api-cms-flashtech-queue]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/api-cms-flashtech/artisan queue:work --sleep=3 --tries=3 --timeout=120
autostart=true
autorestart=true
stopwaitsecs=3600
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/api-cms-flashtech-queue.log
stdout_logfile_maxbytes=50MB
stdout_logfile_backups=10
directory=/var/www/api-cms-flashtech
environment=APP_ENV="production",APP_DEBUG="false"
```

### Gợi ý cấu hình quan trọng

* `numprocs=2`: chạy 2 worker song song (tùy tải, tăng/giảm).
* `--timeout=120`: nếu job lâu hơn có thể tăng.
* `--tries=3`: số lần retry trước khi fail.
* `stopwaitsecs=3600`: cho phép job dài được “stop” mềm.

> Nếu bạn có job rất nặng, cân nhắc tách queue theo `--queue=high,default` và tạo nhiều program khác nhau.

---

## 4) (Tuỳ chọn) Chạy Scheduler bằng daemon

Laravel scheduler chuẩn là chạy bằng cron mỗi phút. Nhưng nếu bạn muốn dùng `schedule:work` (Laravel 9+), bạn có thể tạo thêm config:

```bash
sudo nano /etc/supervisor/conf.d/api-cms-flashtech-schedule.conf
```

```ini
[program:api-cms-flashtech-schedule]
command=/usr/bin/php /var/www/api-cms-flashtech/artisan schedule:work
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/api-cms-flashtech-schedule.log
directory=/var/www/api-cms-flashtech
environment=APP_ENV="production",APP_DEBUG="false"
```

### Cách chuẩn (khuyến nghị): dùng Cron

Nếu bạn dùng cron (phổ biến và ổn định), chỉ cần:

```bash
crontab -e
```

Thêm dòng:

```cron
* * * * * cd /var/www/api-cms-flashtech && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

---

## 5) Reload Supervisor và chạy chương trình

```bash
sudo supervisorctl reread
sudo supervisorctl update

sudo supervisorctl status
sudo supervisorctl start api-cms-flashtech-queue:*
# (nếu có schedule)
sudo supervisorctl start api-cms-flashtech-schedule
```

Restart khi cần:

```bash
sudo supervisorctl restart api-cms-flashtech-queue:*
```

---

## 6) Xem log

```bash
tail -f /var/log/supervisor/api-cms-flashtech-queue.log
# hoặc
sudo supervisorctl tail -f api-cms-flashtech-queue
```

---

## 7) Checklist hay gặp lỗi

1. **Sai path php hoặc artisan**

* kiểm tra `which php`
* kiểm tra project có đúng `/var/www/api-cms-flashtech/artisan`

2. **Permission / user chạy không có quyền**

* đảm bảo user có quyền đọc project + ghi `storage/` và `bootstrap/cache/`

```bash
sudo chown -R www-data:www-data /var/www/api-cms-flashtech/storage /var/www/api-cms-flashtech/bootstrap/cache
sudo chmod -R 775 /var/www/api-cms-flashtech/storage /var/www/api-cms-flashtech/bootstrap/cache
```

3. **Queue driver không đúng**

* `.env` có `QUEUE_CONNECTION=redis` hoặc `database`
* nếu `database`: đảm bảo đã chạy migrate bảng jobs/failed_jobs

4. **Job chạy lâu bị kill**

* tăng `--timeout` và `stopwaitsecs`