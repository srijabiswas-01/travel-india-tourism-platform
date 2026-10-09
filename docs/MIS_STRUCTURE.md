# Travel India Management Information System

## 1. Purpose

The Travel India MIS connects customer-facing travel services with administrative operations and management reporting. It captures customer, package, booking, payment, and enquiry data; converts operational records into useful information; and provides controlled workflows for customers, administrators, and management.

## 2. System Boundary

The complete MIS contains three layers:

```text
Presentation Layer
Public website | Customer account | Admin dashboard | Management reports

Application Layer
Authentication | Package service | Booking workflow | Enquiries | Payments | Reports

Data Layer
Users | Admins | Packages | Bookings | Payments | Messages | Status history | Audit logs
```

External actors may include customers, payment providers, email/SMS services, and system administrators.

## 3. Roles

| Role | Main responsibility | Access |
|---|---|---|
| Visitor | Explore destinations and packages | Public pages only |
| Customer | Maintain an account and request travel services | Customer actions and own records |
| Booking Admin | Review and process booking requests | Bookings and customer contact data |
| Package Admin | Maintain tour packages | Package create, update, publish, archive |
| Manager | Monitor performance and trends | Dashboards, summaries, and reports |
| System Admin | Manage administrators, permissions, configuration, and audit records | Full controlled access |

One person may hold multiple admin roles, but permissions should be assigned explicitly.

## 4. Customer Workflow

### 4.1 Public discovery

```text
Visitor opens Home
  -> Browses destinations
  -> Opens Packages
  -> Views package details
  -> Reads About or Contact information
```

No login is required for discovery.

### 4.2 Registration and authentication

```text
Visitor selects Sign Up
  -> Enters name, email, password, address, and phone
  -> Server validates input
  -> Email uniqueness is checked
  -> Password is hashed
  -> Customer record is created
  -> Customer signs in
  -> Session is created
```

Alternative outcomes include validation failure, duplicate email, invalid credentials, unavailable database, logout, and expired session.

### 4.3 Booking

```text
Customer selects package
  -> Views details
  -> Selects Book Now
  -> Unauthenticated customer is redirected to Sign In
  -> Authenticated customer enters dates, destination, hotel, and travellers
  -> Server validates dates and values
  -> Booking is saved with status Pending
  -> Customer receives a booking reference
  -> Admin reviews the request
  -> Status becomes Confirmed, Cancelled, or Needs Information
  -> Confirmed trip becomes Completed after travel
```

Recommended lifecycle:

```text
Pending -> Needs Information -> Pending
Pending -> Confirmed -> Completed
Pending -> Cancelled
Confirmed -> Cancelled
```

Every status change should record the actor, timestamp, old status, new status, and optional note.

### 4.4 Contact enquiry

```text
Customer enters contact details and message
  -> Authentication and input are validated
  -> Enquiry is stored as Open
  -> Admin assigns or reviews it
  -> Admin replies outside or inside the system
  -> Enquiry becomes Resolved or Closed
```

## 5. Admin Workflow

### 5.1 Admin authentication

```text
Admin opens /admin/login.php
  -> Credentials are verified against admins
  -> Active status and role are checked
  -> Admin session is created
  -> Successful and failed attempts are logged
  -> Admin is redirected to the dashboard
```

Customer accounts must never grant admin access. Admin pages must verify the admin session and permission on every request.

### 5.2 Dashboard

The dashboard should show:

- Total customers
- Active packages
- Pending, confirmed, completed, and cancelled bookings
- Open enquiries
- Booking value and received payments
- Recent bookings and status changes
- Popular destinations
- Monthly booking trend
- Alerts requiring action

Dashboard values must come from database queries, not hardcoded arrays.

### 5.3 Package management

```text
Package Admin opens Packages
  -> Searches or filters packages
  -> Creates or edits package information
  -> Uploads and validates an image
  -> Adds price, duration, destination, hotel, transport, capacity, and description
  -> Saves as Draft
  -> Publishes package
  -> Archives expired package when necessary
```

Package states: `Draft`, `Published`, `Archived`.

### 5.4 Booking management

```text
Booking Admin opens Pending Bookings
  -> Reviews customer and itinerary
  -> Checks package availability and pricing
  -> Adds an internal note
  -> Confirms, requests information, or cancels
  -> Records payment state
  -> System records status history and audit event
```

### 5.5 Customer and enquiry management

Admins can search customers, view booking history, disable an account when authorized, assign enquiries, record responses, and close resolved enquiries. Passwords must never be displayed.

### 5.6 Reports

Managers should be able to filter reports by date range, destination, package, booking status, and payment status. Recommended reports:

- Daily and monthly booking summary
- Booking-status report
- Package-performance report
- Destination-popularity report
- Customer-registration trend
- Payment and outstanding-balance report
- Cancellation report
- Enquiry-resolution report
- Admin activity report

CSV/PDF export is a later enhancement after report queries are verified.

## 6. Current and Target Modules

| Module | Current state | Target MIS state |
|---|---|---|
| Public pages | Database-driven packages and tour details | Add destination taxonomy and availability |
| Customer authentication | Secure login, registration, CSRF, active state | Add verification, reset, and throttling |
| Booking | References, customer/package link, price, status and history | Add capacity and notification automation |
| Contact enquiries | Status, response, and timestamps | Add staff assignment and customer notifications |
| Admin authentication | Separate protected administrator session | Add finer role permissions and login-event history |
| Package admin | Create, edit, publish, draft, and archive | Add validated image uploads |
| Booking admin | Queue, filters, notes, payment and status actions | Add transition rules and notifications |
| Dashboard | Live operational KPIs and recent bookings | Add interactive charts |
| Reports | Monthly and destination reports | Add advanced filters and CSV/PDF exports |
| Audit trail | Admin package, booking, customer, and enquiry actions | Add append-only database permissions |

## 7. Database Design

### 7.1 Original tables

`users`

- `email` primary key
- `name`
- `password`
- `address`
- `phone`

`bookings`

- `id` primary key
- `from_date`, `to_date`
- `destination`, `hotel`
- `people`
- `customer_name`
- `created_at`

`message`

- `email`, `fname`, `lname`
- `sub`
- `message`

### 7.2 Implemented MIS tables and extensions

`admins`

- `id`, `name`, `email`, `password_hash`
- `role`, `is_active`
- `last_login_at`, `created_at`, `updated_at`

`packages`

- `id`, `name`, `slug`, `destination`
- `description`, `duration_days`, `price`
- `hotel_rating`, `transport_mode`, `capacity`
- `image_path`, `status`
- `created_by`, `created_at`, `updated_at`

`bookings` additions

- `booking_reference`
- `user_email` foreign key
- `package_id` foreign key
- `status`, `total_amount`, `payment_status`
- `customer_note`, `admin_note`, `updated_at`

`payments`

- `id`, `booking_id`, `amount`, `method`
- `transaction_reference`, `status`, `paid_at`, `created_at`

`messages` additions

- `id`, `status`, `assigned_admin_id`
- `admin_response`, `created_at`, `resolved_at`

`booking_status_history`

- `id`, `booking_id`, `old_status`, `new_status`
- `changed_by_admin_id`, `note`, `created_at`

`audit_logs`

- `id`, `admin_id`, `action`, `entity_type`, `entity_id`
- `metadata`, `ip_address`, `created_at`

### 7.3 Relationships

```text
users 1 ----- many bookings
packages 1 -- many bookings
bookings 1 -- many payments
bookings 1 -- many booking_status_history
admins 1 --- many packages
admins 1 --- many message assignments
admins 1 --- many audit_logs
```

Use numeric IDs as primary keys for new tables. Keep emails unique, but do not use mutable business fields as foreign-key identities in the final schema.

## 8. Data Flow

### Level 0

```text
Customer -> Travel India MIS -> Booking/Account Information -> Customer
Admin    -> Travel India MIS -> Operational Queues/Reports  -> Admin
Manager  -> Travel India MIS -> Management Information      -> Manager
```

### Level 1

```text
Customer -> Authentication -> Users
Customer -> Package Search -> Packages
Customer -> Booking Process -> Bookings -> Status History
Customer -> Payment Process -> Payments
Customer -> Enquiry Process -> Messages

Admin -> Package Management -> Packages
Admin -> Booking Management -> Bookings + Status History
Admin -> Enquiry Management -> Messages
Admin -> Reporting -> Aggregated operational data
Admin actions -> Audit Service -> Audit Logs
```

## 9. Access-Control Matrix

| Capability | Visitor | Customer | Booking Admin | Package Admin | Manager | System Admin |
|---|---:|---:|---:|---:|---:|---:|
| Browse public pages | Yes | Yes | Yes | Yes | Yes | Yes |
| Submit booking | No | Yes | No | No | No | Optional |
| View own bookings | No | Yes | No | No | No | Optional |
| Process bookings | No | No | Yes | No | View | Yes |
| Manage packages | No | No | No | Yes | View | Yes |
| Resolve enquiries | No | No | Yes | No | View | Yes |
| View reports | No | No | Limited | Limited | Yes | Yes |
| Manage admins | No | No | No | No | No | Yes |
| View audit logs | No | No | No | No | Limited | Yes |

## 10. Business Rules

1. Public browsing is available without login.
2. Booking and protected submissions require an authenticated customer.
3. The end date cannot precede the start date.
4. Traveller count must be positive and within package capacity.
5. Only published packages can receive new bookings.
6. Only authorized admins can change package or booking status.
7. Confirmed bookings require an available package and agreed price.
8. Payment totals cannot exceed the booking total without an authorized refund workflow.
9. Sensitive admin actions must generate an audit record.
10. Deletion should normally be archival or soft deletion to preserve reports.

## 11. Security Controls

- `password_hash()` and `password_verify()` for every password
- Prepared statements for every query
- Separate customer and admin sessions
- Role and permission checks on every admin endpoint
- CSRF tokens on all state-changing forms
- Session regeneration after login
- Secure, HTTP-only, same-site cookies
- Login throttling and temporary lockout
- Server-side validation and output escaping
- MIME/type/size validation for uploads
- HTTPS in deployment
- Environment-based credentials
- Audit logs and minimal database privileges
- Generic user-facing errors with detailed server-side logs

## 12. Implementation Roadmap

### Phase 1: Foundation

1. Create migrations instead of repeatedly importing the full SQL dump.
2. Add `admins` and a separate admin login/logout flow.
3. Add reusable authorization middleware.
4. Add CSRF protection and centralized validation.

### Phase 2: Package operations

1. Create `packages` and migrate hardcoded package data.
2. Build package list, create, edit, publish, archive, and image upload.
3. Render public package pages from the database.

### Phase 3: Booking operations

1. Relate bookings to users and packages.
2. Add booking references, statuses, amounts, and notes.
3. Build customer booking history.
4. Build the admin booking queue and status history.

### Phase 4: Enquiries and payments

1. Add enquiry IDs, assignment, status, and response tracking.
2. Add payment records and payment-status workflow.
3. Integrate an external payment provider only after the internal workflow is tested.

### Phase 5: Management information

1. Build verified aggregate queries.
2. Add dashboard KPIs and charts.
3. Add filters and drill-down views.
4. Add CSV/PDF exports.
5. Add audit reporting.

## 13. Acceptance Tests

Customer tests:

- Visitor can open every public page.
- Duplicate registration is rejected.
- Valid customer can sign in and sign out.
- Invalid credentials do not reveal account details.
- Unauthenticated booking redirects to Sign In.
- Invalid date ranges and traveller counts are rejected.
- Valid booking and enquiry are saved once.
- Light/dark mode and responsive navigation work.

Admin tests:

- Customer credentials cannot open admin pages.
- Disabled admin cannot sign in.
- Role restrictions prevent unauthorized actions.
- Package changes appear correctly on public pages.
- Every booking status change creates history.
- Dashboard totals match database queries.
- Reports respect filters and do not expose passwords.
- Sensitive actions create audit records.

## 14. Demonstration Scenario

1. Open Home as a visitor and browse destinations.
2. Open a package and its details.
3. Select Book Now and demonstrate the authentication requirement.
4. Register a customer and sign in.
5. Submit a booking and show the database record.
6. Sign in as an admin after the admin module is implemented.
7. Confirm the booking and show status history.
8. Open the dashboard and show the updated totals.
9. Generate a filtered booking report.
10. Show the audit event for the admin action.

This sequence demonstrates how operational transactions become management information.

For repository-specific implementation tasks, use [IMPLEMENTATION_CHECKLIST.md](IMPLEMENTATION_CHECKLIST.md).
