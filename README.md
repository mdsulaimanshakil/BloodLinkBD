# BloodLinkBD

An emergency blood donor finder and management platform for Bangladesh. Built with Laravel 11, Tailwind CSS, and Alpine.js.

## Features

- **Donor Registration:** OTP-verified registrations to ensure data quality.
- **Smart Cooldowns:** Donors are automatically hidden from search results for 90 days after donating.
- **Emergency Requests:** Post emergency blood requests with reCAPTCHA v2 protection and rate limiting.
- **Auto-Expiry:** Critical requests expire in 48h, urgent in 4 days, and normal in 7 days.
- **Smart Notifications:** Eligible donors in the same district receive email and database notifications, and WhatsApp click-to-chat links.
- **Admin Dashboard:** Verify donors, moderate requests, and view platform statistics.
- **Hospital Directory:** A built-in directory of local hospitals and blood banks with an OpenStreetMap integration.
- **Localization:** English and Bangla support.

## Architecture & Cost Strategy

This platform uses a **Zero-Cost Notification Strategy**:
Instead of relying on a paid SMS provider (which can be expensive for a non-profit), the application utilizes:
- Email Notifications
- Database (in-app) Notifications
- WhatsApp `wa.me` Click-to-chat links

No paid API keys (Maps, SMS) are required to deploy and run this application. OpenStreetMap is used for the hospital directory map.

## Requirements

- PHP 8.2+
- Composer
- Node.js & NPM
- MySQL or MariaDB

## Local Setup

1. Clone the repository and install dependencies:
   ```bash
   git clone <repo-url>
   cd BloodLinkBD
   composer install
   npm install
   ```

2. Copy the `.env.example` file and generate an app key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. Configure your `.env` file:
   - Set up your Database credentials (`DB_*`).
   - Add your Google reCAPTCHA v2 keys (`RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`).
   - Configure a mail driver (e.g., Mailtrap for local development).

4. Run migrations and seed the database with demo data:
   ```bash
   php artisan migrate:fresh --seed
   ```

5. Build frontend assets and start the local server:
   ```bash
   npm run dev
   php artisan serve
   ```

6. Start the queue worker (for background notifications):
   ```bash
   php artisan queue:work
   ```

## Production Deployment (VPS Setup)

When deploying to a production VPS (Ubuntu/Debian), you need to configure Supervisor to keep the queue worker running and Cron for the Laravel Scheduler.

### 1. Queue Worker (Supervisor)

Install supervisor:
```bash
sudo apt-get install supervisor
```

Create a configuration file `/etc/supervisor/conf.d/bloodlink.conf`:
```ini
[program:bloodlink-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/BloodLinkBD/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/BloodLinkBD/storage/logs/worker.log
stopwaitsecs=3600
```

Update supervisor and start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start bloodlink-worker:*
```

### 2. Laravel Scheduler (Cron)

The scheduler handles the 90-day auto-cooldown reactivation and the blood request auto-expiry.

Open your server's crontab:
```bash
crontab -e
```

Add the following line (replace `/path/to/BloodLinkBD` with your actual path):
```bash
* * * * * cd /path/to/BloodLinkBD && php artisan schedule:run >> /dev/null 2>&1
```

## Security & Maintenance

- Public forms are protected by rate limiting (`throttle:10,1`).
- reCAPTCHA v2 is integrated on the emergency request form to prevent spam.
- Phone numbers in public search are masked (last 3 digits only) until the viewer logs in as a verified donor.
