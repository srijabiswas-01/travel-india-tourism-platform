# Travel India MIS

Travel India MIS is an academic travel management information system designed to manage the complete customer journey and operational workflow of a travel business. It brings together public tour information, customer registration, booking requests, invoice tracking, and administrative approvals into one connected system.

This README focuses on the purpose and business logic of the platform rather than implementation details. The goal is to explain what the system is for, how the information flows across customers and administrators, and why such a system is necessary in a real travel organisation.

> **Payment note:** the checkout is a clearly labelled training flow. It records simulated success, failure, and cancellation outcomes, but it does not connect to a payment provider, collect or store card/UPI credentials, or move money.

## Live portals

- **Public website:** [https://travelindiaguide.infinityfreeapp.com/main.php](https://travelindiaguide.infinityfreeapp.com/main.php)
- **Admin login:** [https://travelindiaguide.infinityfreeapp.com/admin/login.php](https://travelindiaguide.infinityfreeapp.com/admin/login.php)

Admin access requires an authorized administrator account. Do not publish administrator credentials in this README or the GitHub repository.

## Purpose of the system

The purpose of Travel India MIS is to make travel operations more organised, transparent, and efficient. In a tourism business, customer requests, package availability, room allocations, pricing, and administrative decisions often get scattered across different channels. Without a controlled system, the organisation may face:

- repeated customer queries and duplicate bookings
- poor tracking of booking stages and approval status
- inconsistent pricing and unclear payment records
- delayed communication between customers and staff
- limited reporting for management decisions

This MIS solves those issues by creating a single source of operational information for customers, admins, and managers.

## Why this project is needed

Travel services depend on accurate, timely information. A tour operator must know:

- which packages are available and active
- which customers have booked trips and when
- whether a booking is pending, confirmed, cancelled, or needs more information
- how pricing is calculated and what has been paid
- which customer requests require admin attention
- how business performance is progressing over time

Without an MIS, these activities are handled manually and are vulnerable to errors, delays, and poor visibility. The system is needed to standardise operations, reduce administrative workload, and give management reliable information for planning and control.

## How this MIS works

The system is designed around a simple information flow:

1. A visitor explores the public website and views destinations, packages, and offers.
2. A customer registers and creates a personal account.
3. The customer selects a package, enters trip details, and submits a booking request.
4. The booking is stored with a status such as pending, confirmed, cancelled, or needs information.
5. Admin staff review and process the request, request more information if needed, or confirm the trip.
6. Once the trip is confirmed, the system supports payment simulation, invoice generation, and booking updates.
7. Both customers and admins receive notifications about updates, decisions, and required actions.
8. Management can use the reporting side of the MIS to review booking trends and operational performance.

In practical terms, the MIS acts as the digital backbone of the travel business. It connects the public-facing website to internal business processes without requiring the team to rely on disconnected spreadsheets, manual messages, or unstructured records.

## Main business areas

The MIS covers the core travel-management processes:

- Public package discovery and promotional information
- Customer registration and personal profile management
- Booking requests and trip details
- Pricing, offers, and invoice totals
- Customer notifications and admin messages
- Admin approval and booking decision workflows
- Package and operational management by staff
- Reporting and performance monitoring

## Stakeholders

The system is built for multiple user groups:

- Visitors: browse packages and destination information
- Customers: register, manage accounts, and request trips
- Booking administrators: review and approve bookings
- Package administrators: manage travel products and offers
- Managers: monitor performance and operational trends
- System administrators: manage user access and system configuration

## What the system manages

The MIS handles the information needed for travel operations, including:

- customer identities and login access
- destinations and package details
- booking records and trip dates
- travellers, pricing components, and discounts
- payment status and invoice records
- admin notes and follow-up actions
- enquiries and messages
- reports for operational review

## Documentation and project references

This repository contains both the working project and supporting project documentation. For detailed system design and process mapping, see:

- [docs/MIS_STRUCTURE.md](docs/MIS_STRUCTURE.md)
- [docs/IMPLEMENTATION_CHECKLIST.md](docs/IMPLEMENTATION_CHECKLIST.md)
- [docs/OFFERS_MIS_AUDIT.md](docs/OFFERS_MIS_AUDIT.md)

The documentation in this repository is intentionally focused on the management information system and its operational purpose, rather than low-level application code.

## Features

- Public home, package catalogue, offer catalogue, destination detail, about, and contact pages.
- Customer registration, login, profile, family traveller management, and password changes.
- Booking requests with dates, travellers, hotel selection, package offers, PWD support details, and special requests.
- Standard hotel included in package price; other hotel categories can have an admin-configured per-traveller, per-trip surcharge.
- My Trips page with booking status, trip details, itemized price summary, admin updates, and paid invoices.
- Booking-overlap warnings, confirmed-trip conflict protection, date-amendment requests, and cancellation/refund workflows.
- Traveller age categories, child and senior pricing, medical information, and optional PWD assistance details.
- Public, customer-targeted, and admin-team in-app notifications with read tracking.
- Admin dashboards and modules for packages, destinations/regions, hotels, transport, offers, bookings, customers, messages, reports, notifications, and admin accounts.
- Light/dark themes in the customer site and admin portal.
- Responsive layout and shared inline browser-side form validation.

## Requirements

- PHP 8.2 or later (PHP 8.4 is used for development).
- MySQL 8 or a compatible MySQL/MariaDB server.
- Required PHP extension: `mysqli`. The `fileinfo` extension is recommended; image validation falls back to `getimagesize()` when it is unavailable.
- JavaScript enabled in the browser. The site loads Bootstrap, Font Awesome, jQuery, and the QR renderer from CDNs, so internet access is needed for those assets.
- A web server or PHP's built-in development server.

Check PHP and extensions:

```powershell
php -v
php -m | Select-String "mysqli|fileinfo"
```

## Local installation

1. Create a database and import the starter SQL. Replace the path with the actual workspace path:

```sql
CREATE DATABASE IF NOT EXISTS travel_india CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then import `db/travel_india.sql` using MySQL Workbench or the MySQL command-line client. For the command-line option, create the database and open it:

```powershell
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS travel_india CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql -u root -p travel_india
```

At the `mysql>` prompt, import the SQL file (adjust the path if your workspace is elsewhere), then exit:

```text
SOURCE "D:/SB/SB/Travel India/db/travel_india.sql";
exit
```

2. Configure the database using either environment variables or `config.local.php`. See [Configuration](#configuration).
3. Run the additive schema migration from the project root:

```powershell
php db/migrate.php
```

Back up an existing database before applying migrations. The migration command is CLI-only and updates the schema needed by the current code, including hotel upgrades, bookings, travellers, notifications, and payment attempts.

4. Start the local server from the project root:

```powershell
php -S 127.0.0.1:8000
```

5. Open `http://127.0.0.1:8000/main.php`.
6. Create the first administrator at `http://127.0.0.1:8000/admin/setup.php`. This one-time route closes after an administrator exists. Sign in at `http://127.0.0.1:8000/admin/login.php`.

The built-in PHP server is for local development and does not provide HTTPS. Do not use it as a production web server.

## Configuration

The connection helper reads `TRAVEL_INDIA_DB_HOST`, `TRAVEL_INDIA_DB_USER`, `TRAVEL_INDIA_DB_PASSWORD`, and `TRAVEL_INDIA_DB_NAME`. Defaults are `localhost`, `root`, an empty password, and `travel_india` respectively.

PowerShell example for the current terminal session:

```powershell
$env:TRAVEL_INDIA_DB_HOST = "localhost"
$env:TRAVEL_INDIA_DB_USER = "root"
$securePassword = Read-Host "MySQL password" -AsSecureString
$env:TRAVEL_INDIA_DB_PASSWORD = [System.Net.NetworkCredential]::new("", $securePassword).Password
$env:TRAVEL_INDIA_DB_NAME = "travel_india"
php -S 127.0.0.1:8000
```

Alternatively, copy `config.local.example.php` to `config.local.php` and enter your local connection settings there. `config.local.php` takes precedence over environment values in this project; do not commit or share it. Use a restricted database user, not `root`, for a hosted deployment.

The root `.htaccess` provides the domain-root entry point, HTTPS redirect rules for non-local requests, and baseline security headers. On shared hosting, use the exact database host and credentials shown in the provider control panel and ensure `uploads/` is writable. Remove or restrict `admin/setup.php` after first-admin creation.

## Customer workflow

1. Visitors can browse destinations, packages, and offers without signing in.
2. Registration requires a valid email and a password of 8-72 characters with matching confirmation. Login requires a valid email and at least 8 characters.
	Booking and contact-form submissions require a signed-in customer.
3. A signed-in customer selects a published package and submits a booking request. No payment is taken at request time.
4. The booking starts as `pending`. The customer can follow status and messages in My Trips and in the header notification bell.
5. If an admin requests information, the customer notification opens the prefilled edit form. The customer can revise the booking; the same booking reference is retained, amounts and travellers are updated, and the request returns to `pending` for review.
6. Once confirmed, the customer can open checkout. If payment is recorded as paid, an invoice becomes available from My Trips.
7. Date selection warns about pending overlaps and blocks conflicts with confirmed or active trips.
8. Eligible customers can request a date amendment or cancellation. The admin reviews the request before booking dates, status, payment, or refund state changes.

## Booking and pricing rules

- The end date is set from the package duration, counting the start day as day one. The server validates the same duration.
- Standard hotels are treated as included in the package. Other hotel categories may have an admin-managed upgrade surcharge per traveller for the full trip.
- The booking stores the package unit price and hotel surcharge snapshots so later catalogue edits do not change historic bills.
- Package subtotal is package unit price multiplied by travellers. Hotel upgrade is surcharge per traveller multiplied by travellers. Offers are calculated from the package subtotal; the hotel upgrade is added outside the offer calculation. The stored total is package subtotal + hotel upgrade - offer discount, and is used for checkout and invoices.
- Travellers can be the account holder, saved family members, manually entered travellers, or a permitted combination. Traveller details and PWD assistance flags are snapshotted with the booking.
- A `needs_information` booking can only be edited by its owner while it remains unpaid. The edit handler rechecks ownership and status on submission.
- Traveller age is recalculated from date of birth. Current demonstration package rates are adult 100%, child 50%, and senior 90%.
- One best eligible offer is applied to a booking; offers do not stack and never overwrite the package's normal price.
- Existing bookings retain package, hotel, offer, traveller, discount, and total snapshots when catalogue data changes.

## Payment and invoices

Checkout requires a confirmed booking. The page offers UPI, card, and net-banking presentations, but it is not connected to a gateway. It displays one **Demonstration mode** label and explicitly says not to enter real credentials.

- UPI's QR encodes a booking reference only; it is not a payable UPI QR.
- Cardholder, card number, expiry, CVV, UPI ID, and bank selection controls are client-only. They have no submitted field names, are cleared before the request, and are not stored.
- Only the booking ID, selected method, CSRF token, and selected practice outcome reach the server.
- The server locks and verifies the signed-in customer's confirmed booking, writes a payment-attempt record, and updates the booking only on a successful selected outcome.
- A successful outcome marks the payment `paid`, keeps the booking `confirmed` until travel starts, writes status history, and notifies the customer. Failure/cancellation are recorded as attempts and leave the booking eligible for retry.
- The `payments` table stores the attempt amount, method, reference, status, and timestamp. The `bookings.payment_status` stores the current overall booking payment state.
- Paid customers can view an owner-protected invoice from My Trips. The invoice includes customer, booking, trip, traveller, itemized price, payment method/reference details, and a print/Save as PDF control. Admins can view/print the invoice from booking details.

No real payment provider, bank, or payment credential is contacted. A real gateway requires a merchant account, provider credentials, signed webhook verification, idempotency, reconciliation, and refund handling; none are implemented here.

## Admin portal

Sign in at `/admin/login.php`. The first administrator is created at `/admin/setup.php`; subsequent administrators can be added from the **Admins** module by a super administrator. Passwords are hashed and the available roles are:

| Role | Main access |
|---|---|
| `super_admin` | All modules, including administrator management |
| `manager` | Dashboard, catalogue, bookings, notifications, messages, customers, reports |
| `booking_admin` | Dashboard, bookings, messages, customer viewing |
| `package_admin` | Dashboard and catalogue |

The Bookings module supports status filtering, status/payment updates, customer-update messages, and an eye-icon detail view. Status updates follow a transition map; notes are required for needs-information, cancellation, and rejection. Payment and booking status remain separate: a paid booking stays `confirmed` until its start date, when an administrator can move it to `in_progress`.

The detail page shows package/trip data, saved travellers, customer update/status history, payment attempts, and an invoice panel when paid. Admin updates and sensitive actions are written to `audit_logs`.

## Notifications

Notifications are persisted in `notifications`; read state is stored in `notification_reads` per account.

- Customer notices appear in the customer header bell. A read notice stays in the list for 24 hours, then is hidden; unread notices remain visible.
- Admins have a separate header bell and per-admin read state. Admin-team notices do not appear as customer notices. Read notices remain listed for 24 hours before being hidden.
- New booking requests, customer booking revisions, and contact enquiries create admin-team alerts.
- The Notifications module can publish to everyone, one registered customer, or the admin team.

## Database

`db/travel_india.sql` is the initial import. `db/migrate.php` is the supported CLI schema updater. The application does not automatically apply runtime migrations on every request; run the migration when installing/upgrading before using newly added schema fields.

Main tables include:

| Table | Purpose |
|---|---|
| `users`, `family_members` | Customer accounts and saved travellers |
| `countries`, `states`, `destinations` | Destination catalogue |
| `packages`, `hotels`, `transport_categories` | Travel catalogue and accommodation pricing |
| `offers`, `offer_packages`, `offer_redemptions` | Promotion setup and redemption history |
| `bookings`, `booking_travellers` | Booking snapshots and traveller details |
| `booking_amendments`, `cancellation_policies`, `cancellation_requests` | Date-change and cancellation workflows |
| `booking_reviews` | Moderated customer package reviews |
| `payments` | Payment attempts and references |
| `booking_status_history`, `audit_logs` | Workflow and admin audit records |
| `notifications`, `notification_reads` | Customer/admin notifications and read state |
| `admins`, `login_attempts` | Admin accounts and authentication throttling |

## Testing

Run the migration and automated workflow checks from the project root:

```powershell
php db/migrate.php
php tests/booking-workflow-smoke.php
php tests/data-integrity-audit.php
php tests/reporting-smoke.php
```

Validate every PHP and JavaScript file:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
Get-ChildItem js -Filter *.js | ForEach-Object { node --check $_.FullName }
```

The workflow smoke test uses a database transaction and rolls back its test records. The integrity audit checks duplicate booking references, orphaned records, traveller-count mismatches, payment ledgers, unfinished test users, and cancellation-policy availability.

Manually verify registration/login, profile and family edits, package filtering, new booking submission, overlap warnings, admin review, needs-information edits, confirmation, simulated checkout outcomes, amendments, cancellation/refund processing, invoices, notification read state, and responsive light/dark layouts.

## InfinityFree deployment

InfinityFree can run this PHP/MySQL project, but free hosting does not provide terminal access. Use FileZilla FTP for the project and phpMyAdmin for the database.

1. Create the InfinityFree website and MySQL database.
2. Run `php db/migrate.php` locally before preparing the hosted database export.
3. Export the complete migrated local database from MySQL or phpMyAdmin.
4. Import that complete SQL export through InfinityFree phpMyAdmin.
5. Upload the project contents into `/htdocs`, preserving all directories.
6. Create a production `/htdocs/config.local.php` using the exact MySQL host, database, username, and password shown by InfinityFree.
7. Upload the required `uploads/` directories and ensure PHP can write to them.
8. Enable SSL and test the HTTPS domain.
9. Create the first administrator through `/admin/setup.php`, then remove or restrict that setup route.

Do **not** open `/db/migrate.php` in a browser. It is intentionally CLI-only and returns `404` for web requests. InfinityFree installations must import a complete database export that already contains the current schema.

### Upload location and filenames

Website files belong inside `/htdocs`, not beside it. InfinityFree uses a case-sensitive Linux filesystem, so every reference must exactly match the uploaded filename:

```text
Correct: /htdocs/css/package.css
Wrong:   /htdocs/css/Package.css
```

The application references lowercase `css/package.css`. A case mismatch can work on Windows but produce missing styles on InfinityFree.

After replacing CSS or PHP files, use `Ctrl + F5` or an Incognito window to bypass browser cache. The current deployed cache references include `package.css?v=2` and `admin.css?v=16`.

### Hosted media and email

- Uploaded image paths are stored in MySQL, but image files remain under `uploads/`.
- Back up both the database and uploaded files.
- PHP `mail()` is heavily restricted on InfinityFree. Real email should use PHPMailer and an external SMTP provider.
- Database-backed header notifications work without email.

## GitHub guidance

The included `.gitignore` excludes local credentials, environment files, runtime uploads, root SQL exports, logs, caches, editor metadata, and deployment archives. Static files under `img/`, `media/`, `css/`, and `js/` remain trackable.

Create and publish the repository:

```powershell
git init
git add .
git status
git commit -m "Initial Travel India MIS"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPOSITORY.git
git push -u origin main
```

Before committing, confirm that `config.local.php`, `.env`, customer uploads, and database exports containing customer data are absent from `git status`.

GitHub Pages cannot host this application because it does not execute PHP or provide MySQL. Use GitHub for source control and InfinityFree or another PHP host to run the website.

## Security and limitations

- Customer and admin sessions are separate. Admin modules check server-side permissions.
- Mutating forms use CSRF tokens. Passwords use `password_hash()`/`password_verify()`; legacy customer MD5 hashes are upgraded at login.
- Authentication uses rate limiting. Session cookies use strict mode, `HttpOnly`, `SameSite=Lax`, and `Secure` on HTTPS.
- Database operations for user-controlled booking and notification data use prepared statements. Uploaded images are type/size checked and stored under `uploads/`.
- Client-side validation improves usability but does not replace server-side validation.
- The checkout is a simulation, not a payment service. Do not enter real payment credentials.
- Before production, configure HTTPS, restricted database credentials, secure error logging, backups, payment-provider integration if needed, and end-to-end acceptance tests.

## Project layout

```text
admin/                  Secured admin pages and shared admin layout
css/                    Customer/admin styles and themes
db/travel_india.sql     Initial SQL import
db/migrate.php          CLI schema migration
docs/                   MIS specification and implementation checklist
img/, media/            Images and media assets
js/                     Theme, validation, booking and admin interactions
uploads/                Runtime uploaded media (ignored by Git)
tests/                  Workflow and data-integrity checks
booking-workflow.php    Date-overlap and cancellation helpers
booking-request.php     Customer amendment/cancellation request form
booking-request-submit.php Request validation and persistence
booking.php             New and needs-information booking form
bookingins.php          Booking creation/edit processing
my-bookings.php         Customer trip list, totals and actions
payment.php             Demonstration checkout interface
payment-process.php     CSRF-protected simulated payment processing
invoice.php             Paid-customer invoice, printable to PDF
notifications.php       Shared notification persistence/read helpers
admin/bookings.php      Admin booking workflow
admin/booking-details.php Admin booking details, history and paid invoice
```

Further MIS diagrams and acceptance criteria are in [docs/MIS_STRUCTURE.md](docs/MIS_STRUCTURE.md) and [docs/IMPLEMENTATION_CHECKLIST.md](docs/IMPLEMENTATION_CHECKLIST.md).

## License

Academic project. Add an explicit license before public distribution.
