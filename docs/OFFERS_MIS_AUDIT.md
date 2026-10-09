# Offers and Traveller Pricing MIS Audit

## Current status

The core offer workflow is operational. Offers are administered separately from packages, normal package prices remain unchanged, eligible offers are checked on the server, and the applied price is stored on the booking.

## Implemented

- Separate Admin Offers module and package assignment.
- Draft, active, paused, and archived administrative states.
- Date-controlled public visibility and automatic hiding after expiry.
- Percentage and fixed-INR discounts.
- Booking-total and per-traveller calculation scopes.
- Minimum traveller and minimum booking-value requirements.
- Maximum discount, total usage limit, and per-customer limit.
- Automatic offers and optional coupon codes.
- One best-value offer per booking; offers do not stack.
- Public Offers page, empty state, offer imagery, package links, and booking links.
- Package-card badges, original prices, discounted display prices, and expiry dates.
- Immutable booking snapshots for unit price, base amount, offer details, discount, and final amount.
- Redemption records and admin revenue/discount metrics.
- Offer preview, edit, duplicate, activate/pause, and safe deletion.
- Offers with redemptions cannot be deleted.
- PWD classification and support details for the customer, family members, and ad hoc booking travellers.
- Date-of-birth-based child, adult, and senior traveller classification.

## Partially implemented

- Scheduled and expired states are derived by dates for visibility and dashboard counts, but are not persisted as separate database statuses.
- Admin analytics include redemption, discount, and revenue totals, but do not yet include conversion rate or per-package charts.
- Package eligibility is implemented; destination, state, and country scopes are not implemented.
- Offer notifications use the existing notification infrastructure, but automatic new-offer and expiry campaigns are not scheduled.

## Not implemented yet

- Travel-date eligibility range separate from booking dates.
- New-customer-only eligibility.
- Destination and region offer mapping tables.
- Traveller-category promotional offers.
- Personalized offers and controlled stacking.
- Dedicated offer analytics detail page and exports.
- Permanent child and PWD concession calculation.

## Permanent concession rule

Child or PWD concessions must be maintained separately from temporary package offers. A safe implementation requires an administrator-controlled Traveller Pricing Policy containing:

- Child minimum and maximum age.
- Child concession percentage or fixed INR amount.
- PWD concession percentage or fixed INR amount.
- Whether proof verification is required.
- Whether child and PWD concessions can combine for one traveller.
- Whether permanent concessions apply before or after a promotional offer.
- Effective dates and active status.

Every booking should snapshot the policy, eligible traveller counts, category concession amount, promotional discount, and final amount. No concession percentage should be hard-coded without an approved business rule.

## Recommended calculation order

1. Calculate normal package amount per traveller.
2. Apply one permanent traveller-category concession per eligible traveller.
3. Calculate the eligible package offer against the concession-adjusted subtotal.
4. Apply the offer maximum-discount rule.
5. Prevent the final amount from becoming negative.
6. Store every component in the booking snapshot.

This order avoids accidental double-discounting while keeping both concessions and marketing offers auditable.
