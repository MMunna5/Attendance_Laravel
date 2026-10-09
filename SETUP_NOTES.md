# Setup notes

This is a full Laravel 11 project, merged from the official `laravel/laravel`
skeleton (downloaded fresh from GitHub) + all the attendance-app code.
I already applied these fixes for you inside the zip:
- `bootstrap/app.php` — registered the `admin` middleware alias
- `config/services.php` — added the `bdbulksms.token` entry
- `config/app.php` — timezone defaults to `Asia/Dhaka`
- `.env.example` — added SCHOOL_SHORT_NAME / SCHOOL_FULL_NAME /
  DEFAULT_TEACHER_PASSWORD / BDBULKSMS_TOKEN / APP_TIMEZONE
- `composer.json` — added `maatwebsite/excel` and `phpoffice/phpspreadsheet`
- Every `.php` file here has been run through `php -l` (syntax check) —
  all pass with **no syntax errors**. I could not run `composer install`
  or boot the app itself (no network access to packagist.org from my
  environment), so logic bugs / wrong Blade variable names are still
  possible — only syntax is verified, not behavior.

## To run it on your machine

```bash
cd final-project
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite   # or configure MySQL/Postgres in .env
php artisan migrate
php artisan serve
```

In a second terminal, start the SMS queue worker (this replaces Django's
`process_sms_queue` background command):
```bash
php artisan queue:work --tries=5 --backoff=300
```

Then create your first admin user (no seeder is included yet):
```bash
php artisan tinker
>>> \App\Models\User::create(['username' => 'admin', 'name' => 'Admin', 'email' => 'admin@local.test', 'password' => bcrypt('changeme'), 'is_admin' => true]);
```

## Still to do yourself
1. **Logo**: copy `attendance/static/attendance/logo.jpeg` from the Django
   repo into `public/images/logo.jpeg` (the layout expects it at that path).
2. **Test the Excel import**: `maatwebsite/excel`'s header-detection may
   behave slightly differently from openpyxl on edge cases (merged cells,
   extra whitespace) — upload a real student/teacher file and check.
3. **Test the SMS gateway call**: `SmsService::send()` mirrors the Django
   `sms_utils.py` request shape and response parsing, but bdbulksms.net's
   exact response format should be re-verified against a live token.
4. **Row-locking under real concurrency**: `AttendanceController::store()`
   uses `lockForUpdate()` inside `DB::transaction()`, same idea as Django's
   `select_for_update()` — test two simultaneous submits once deployed.
5. Everything else in the original SETUP_NOTES below is background/context,
   already applied where it said "add this."
