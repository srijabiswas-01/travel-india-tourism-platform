# Travel India MIS

Travel India is an academic travel-management information system built with PHP and MySQL. It includes a public travel catalogue, customer profiles and booking requests, and a role-aware administration portal.

> **Payment note:** the checkout is a clearly labelled training flow. It records simulated success, failure, and cancellation outcomes, but it does not connect to a payment provider, collect or store card/UPI credentials, or move money.

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Local installation](#local-installation)
- [Configuration](#configuration)
- [Customer workflow](#customer-workflow)
- [Booking and pricing rules](#booking-and-pricing-rules)
- [Payment and invoices](#payment-and-invoices)
- [Admin portal](#admin-portal)
- [Notifications](#notifications)
- [Database](#database)
- [Security and limitations](#security-and-limitations)
- [Verification](#verification)
- [Project layout](#project-layout)

## Features

- Public home, package catalogue, offer catalogue, destination detail, about, and contact pages.
- Customer registration, login, profile, family traveller management, and password changes.
- Booking requests with dates, travellers, hotel selection, package offers, PWD support details, and special requests.
- Standard hotel included in package price; other hotel categories can have an admin-configured per-traveller, per-trip surcharge.
- My Trips page with booking status, trip details, itemized price summary, admin updates, and paid invoices.
- Public, customer-targeted, and admin-team in-app notifications with read tracking.
- Admin dashboards and modules for packages, destinations/regions, hotels, transport, offers, bookings, customers, messages, reports, notifications, and admin accounts.
- Light/dark themes in the customer site and admin portal.
- Responsive layout and shared inline browser-side form validation.

## Requirements

- PHP 8.2 or later (PHP 8.4 is used for development).
- MySQL 8 or a compatible MySQL/MariaDB server.
- PHP extensions: `mysqli` and `fileinfo`.
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

The root `.htaccess` provides the domain-root entry point, HTTPS redirect rules for non-local requests, and baseline security headers. On shared hosting, use the exact database host and credentials shown in the provider control panel, import the SQL, run or trigger the schema migration, and ensure `uploads/` is writable. Remove or restrict `admin/setup.php` after first-admin creation.

## Customer workflow

1. Visitors can browse destinations, packages, and offers without signing in.
2. Registration requires a valid email and a password of 8–72 characters with matching confirmation. Login requires a valid email and at least 8 characters.
	Booking and contact-form submissions require a signed-in customer.
3. A signed-in customer selects a published package and submits a booking request. No payment is taken at request time.
4. The booking starts as `pending`. The customer can follow status and messages in My Trips and in the header notification bell.
5. If an admin requests information, the customer notification opens the prefilled edit form. The customer can revise the booking; the same booking reference is retained, amounts and travellers are updated, and the request returns to `pending` for review.
6. Once confirmed, the customer can open checkout. If payment is recorded as paid, an invoice becomes available from My Trips.

## Booking and pricing rules

- The end date is set from the package duration, counting the start day as day one. The server validates the same duration.
- Standard hotels are treated as included in the package. Other hotel categories may have an admin-managed upgrade surcharge per traveller for the full trip.
- The booking stores the package unit price and hotel surcharge snapshots so later catalogue edits do not change historic bills.
- Package subtotal is package unit price × travellers. Hotel upgrade is surcharge per traveller × travellers. Offers are calculated from the package subtotal; the hotel upgrade is added outside the offer calculation. The stored total is package subtotal + hotel upgrade − offer discount, and is used for checkout and invoices.
- Travellers can be the account holder, saved family members, manually entered travellers, or a permitted combination. Traveller details and PWD assistance flags are snapshotted with the booking.
- A `needs_information` booking can only be edited by its owner while it remains unpaid. The edit handler rechecks ownership and status on submission.

## Payment and invoices

Checkout requires a confirmed booking. The page offers UPI, card, and net-banking presentations, but it is not connected to a gateway. It displays one **Demonstration mode** label and explicitly says not to enter real credentials.

- UPI's QR encodes a booking reference only; it is not a payable UPI QR.
- Cardholder, card number, expiry, CVV, UPI ID, and bank selection controls are client-only. They have no submitted field names, are cleared before the request, and are not stored.
- Only the booking ID, selected method, CSRF token, and selected practice outcome reach the server.
- The server locks and verifies the signed-in customer's confirmed booking, writes a payment-attempt record, and updates the booking only on a successful selected outcome.
- A successful outcome marks the payment `paid`, moves the booking to `in_progress`, writes status history, and notifies the customer. Failure/cancellation are recorded as attempts and leave the booking eligible for retry.
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

The Bookings module supports status filtering, status/payment updates, customer-update messages, and an eye-icon detail view. Status updates follow a transition map; notes are required for needs-information, cancellation, and rejection. If an admin marks a confirmed booking as paid, it is moved to `in_progress` and both changes are recorded in history.

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
| `payments` | Payment attempts and references |
| `booking_status_history`, `audit_logs` | Workflow and admin audit records |
| `notifications`, `notification_reads` | Customer/admin notifications and read state |
| `admins`, `login_attempts` | Admin accounts and authentication throttling |

## Security and limitations

- Customer and admin sessions are separate. Admin modules check server-side permissions.
- Mutating forms use CSRF tokens. Passwords use `password_hash()`/`password_verify()`; legacy customer MD5 hashes are upgraded at login.
- Authentication uses rate limiting. Session cookies use strict mode, `HttpOnly`, `SameSite=Lax`, and `Secure` on HTTPS.
- Database operations for user-controlled booking and notification data use prepared statements. Uploaded images are type/size checked and stored under `uploads/`.
- Client-side validation improves usability but does not replace server-side validation.
- The checkout is a simulation, not a payment service. Do not enter real payment credentials.
- Before production, configure HTTPS, restricted database credentials, secure error logging, backups, payment-provider integration if needed, and end-to-end acceptance tests.

## Verification

From PowerShell in the project root:

```powershell
php db/migrate.php
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }
node --check js/form-validation.js
node --check js/trip-details.js
node --check js/practice-checkout.js
node --check js/admin-booking-workflow.js
```

Manually verify registration/login, profile/family edits, new booking submission, admin notification bell, needs-information edit/review, confirmation, successful/failed/cancelled checkout outcomes, invoice viewing/printing, notification read/expiry behavior, and light/dark responsive layouts.

## Project layout

```text
admin/                  Secured admin pages and shared admin layout
css/                    Customer/admin styles and themes
db/travel_india.sql     Initial SQL import
db/migrate.php          CLI schema migration
docs/                   MIS specification and implementation checklist
img/, media/            Images and media assets
js/                     Theme, validation, booking and admin interactions
uploads/                Uploaded catalogue/profile images
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
