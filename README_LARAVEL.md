# Laravel Guide For This Project

If you are new to Laravel, read this file first.

## 1) Laravel Request Flow (Simple)
- Route receives the URL (`routes/api.php`, `routes/admin.php`, `routes/web.php`).
- Middleware checks permissions/auth (`app/Http/Middleware/*`).
- Controller validates request and calls service (`app/Http/Controllers/*`).
- Service applies business rules (`app/Services/*`).
- Model reads/writes MongoDB (`app/Models/*`).
- Response is returned as JSON (API) or Blade HTML view (admin pages).

## 2) Folder Map (What Each Folder Means)
- `app/Http/Controllers/Api`: JSON API controllers used by frontoffice.
- `app/Http/Controllers/Admin`: backoffice controllers for admin panel.
- `app/Http/Controllers/Auth`: web login/logout for admin web session.
- `app/Http/Middleware`: role checks and employee-accepted checks.
- `app/Services`: business logic (core of project behavior).
- `app/Models`: MongoDB Eloquent models.
- `app/Mail`: mailable classes (email templates wiring).
- `resources/views/admin`: admin UI pages.
- `resources/views/emails`: email blade templates.
- `resources/views/pdf`: PDF templates.
- `routes/api.php`: token-based API endpoints.
- `routes/admin.php`: admin web endpoints.
- `routes/web.php`: public/login/download web routes.

## 3) Auth In This Project
- API auth: Sanctum bearer token.
- Admin backoffice auth: web session (`auth:web` middleware).
- Employee portal access is blocked until `isEmployeeAccepted = true`.
- Role checks use middleware aliases like `role:admin`, `role:employee`, `admin`, `employee.accepted`.

## 4) How To Read Any Backend Feature
- Open route in `routes/api.php` or `routes/admin.php`.
- Find controller method.
- Read validation rules first.
- Jump to called service class/method.
- Inspect model fields/relations used by that service.
- Check blade/email/pdf view only at the end (presentation layer).

## 5) Common Tasks: Where To Change Code
- Add API endpoint:
Edit `routes/api.php` + create/update `app/Http/Controllers/Api/*`.
- Add admin page:
Edit `routes/admin.php` + `app/Http/Controllers/Admin/*` + `resources/views/admin/*`.
- Change business rule:
Edit `app/Services/*` first.
- Change data fields:
Ediot model in `app/Mdels/*` and validation in controller.
- Add email:
Create/update `app/Mail/*` + `resources/views/emails/*` + call `Mail::to()->send(...)`.
- Add PDF output:
Use `resources/views/pdf/*` and corresponding controller/service.

## 6) Local Run Checklist
- Install deps:
`composer install`
- Configure env (`.env`):
MongoDB, mail, app URL.
- Start server:
`php artisan serve`
- Useful checks:
`php artisan route:list`
`php artisan config:clear`
`php artisan test`

## 7) Fast Debug Strategy
- If route does not work: run `php artisan route:list` and check URI/middleware.
- If auth fails: check token/session and middleware chain.
- If logic is wrong: inspect service methods, not just controllers.
- If UI mismatch in admin: check Blade view + controller data passed to view.
- If mail not sent: check `.env` mail settings + run `php artisan config:clear`.

