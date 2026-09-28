# LeaveFlow — Leave Request Management System

A Laravel 12 leave management system with three roles: **Administrator, Manager, Staff**.

## Workflow

`Staff → Submit → Pending → Assigned Manager → Approve/Reject → Notification → History`

## Demo accounts

- Administrator: `admin@example.com` / `password123`
- Manager: `manager@example.com` / `password123`
- Staff: `staff@example.com` / `password123`

## Setup

```bash
composer install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

The included `.env.example` uses SQLite and `MAIL_MAILER=log`, so password-reset emails are written to `storage/logs/laravel.log` during local development.

## Main features

- Secure login, registration, logout, forgot/reset password
- Public registration always creates Staff
- Administrator role/user/manager assignment management
- Manager-scoped leave approvals
- Leave balance tracking
- Working-day calculation and overlap validation
- Approval history/audit trail
- Database notifications
- Search, filters, pagination
- Leave types
- Responsive dashboard and calendar
- Private iCalendar feed for the user's approved leave
