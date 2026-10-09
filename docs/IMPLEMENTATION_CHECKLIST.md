# MIS Implementation Checklist

This checklist tracks the implemented MIS baseline and the remaining production hardening work. Checked items are present in the current codebase; unchecked items still require operational verification or an extension.

## Phase 0: Preserve the Current Application

- [ ] Back up the `travel_india` database.
- [ ] Initialize Git and commit the current working customer application.
- [ ] Confirm PHP loads `mysqli` and `pdo_mysql`.
- [ ] Test registration, login, logout, booking, and contact submission.
- [ ] Record the current database schema with `SHOW CREATE TABLE`.

## Phase 1: Database Migrations

- [ ] Create a `db/migrations/` directory.
- [ ] Create numbered migration files instead of reimporting `travel_india.sql`.
- [ ] Add numeric customer IDs while keeping `users.email` unique.
- [x] Create `admins` with role, active state, and timestamps.
- [x] Create `packages` with publication status and image path.
- [x] Extend `bookings` with user, package, reference, status, amount, and notes.
- [x] Add `payments`, `booking_status_history`, and `audit_logs`.
- [ ] Add foreign keys and indexes used by dashboard queries.
- [ ] Add `created_at` and `updated_at` consistently.

## Phase 2: Application Foundation

- [x] Move session initialization into a shared bootstrap file.
- [x] Add customer authentication helper functions.
- [x] Add separate admin authentication helper functions.
- [x] Add role/permission authorization helpers.
- [x] Add CSRF token generation and validation.
- [ ] Add centralized input validation.
- [ ] Log detailed errors server-side and show generic UI messages.
- [x] Configure secure session cookies for deployment.

Suggested files:

```text
app/bootstrap.php
app/auth/customer_auth.php
app/auth/admin_auth.php
app/security/csrf.php
app/validation.php
app/repositories/
```

## Phase 3: Admin Authentication

- [x] Replace `admin/login.php` prototype with one login form.
- [ ] Create `admin/logins.php` using prepared statements.
- [x] Verify passwords with `password_verify()`.
- [x] Reject inactive administrators.
- [x] Store admin identity and role in a separate admin session.
- [x] Regenerate the session ID after login.
- [x] Protect every operational `admin/*.php` page.
- [x] Create admin logout.
- [ ] Record successful and failed login events.

Exit criteria: customer credentials cannot access any admin route.

## Phase 4: Package Management

- [x] Import existing hardcoded package information into `packages`.
- [ ] Build an admin package table with search and status filters.
- [x] Implement Create Package.
- [x] Implement Edit Package.
- [x] Implement Publish/Unpublish.
- [x] Implement Archive instead of destructive deletion.
- [x] Validate uploaded file type, extension, size, and generated filename.
- [x] Render `Package.php` and `View.php` from database records.
- [ ] Return a proper not-found page for invalid package IDs.

Replace or refactor:

```text
admin/package.php
admin/detail.php
admin/addpackage.php
Package.php
View.php
```

## Phase 5: Booking Workflow

- [ ] Link every booking to `users.id` and `packages.id`.
- [x] Generate a unique booking reference.
- [x] Save the package price at booking time.
- [x] Default every new booking to `Pending`.
- [x] Create customer booking-history page.
- [x] Create admin booking queue with filters.
- [x] Add Confirm, Needs Information, Cancel, and Complete actions.
- [x] Require an admin note for cancellation.
- [x] Write every status change to `booking_status_history`.
- [x] Write every admin action to `audit_logs`.

Exit criteria: booking totals and status history remain correct after repeated updates.

## Phase 6: Enquiries and Payments

- [x] Add ID, status, response, and timestamps to enquiries.
- [ ] Build Open/Assigned/Resolved/Closed enquiry views.
- [ ] Add payment records independently of booking status.
- [ ] Support Unpaid, Partial, Paid, Failed, and Refunded states.
- [ ] Prevent payment totals from silently exceeding booking totals.
- [ ] Do not integrate a real gateway until the internal workflow passes tests.

## Phase 7: Dashboard and Reports

- [x] Build KPI queries for customers, bookings, enquiries, and paid revenue.
- [ ] Add recent-booking and action-required lists.
- [ ] Add monthly booking trend chart.
- [x] Add destination popularity reports.
- [ ] Add cancellation and outstanding-payment reports.
- [ ] Add date, package, destination, and status filters.
- [ ] Verify report totals against direct SQL queries.
- [ ] Add CSV export after query verification.
- [ ] Add PDF export last.

## Phase 8: Testing and Deployment

- [ ] Run every customer acceptance test in `MIS_STRUCTURE.md`.
- [ ] Run every admin acceptance test in `MIS_STRUCTURE.md`.
- [ ] Test authorization by manually requesting restricted URLs.
- [ ] Test duplicate form submissions.
- [ ] Test invalid IDs, dates, amounts, file uploads, and status transitions.
- [ ] Test desktop, tablet, and mobile layouts in both themes.
- [ ] Enable HTTPS and production PHP settings.
- [ ] Use a restricted MySQL account instead of `root`.
- [ ] Disable detailed error display in production.
- [ ] Schedule database backups and verify restoration.

## Definition of Done

The project can be presented as a complete MIS only when:

- [ ] Public data is database-driven.
- [ ] Customer and admin authentication are separate.
- [ ] Admin permissions are enforced server-side.
- [ ] Packages, bookings, enquiries, and payments have complete workflows.
- [ ] Booking status changes and sensitive admin actions are auditable.
- [ ] Dashboard and report totals are verified.
- [ ] Security and acceptance tests pass.
- [ ] Documentation matches the implemented behavior.
