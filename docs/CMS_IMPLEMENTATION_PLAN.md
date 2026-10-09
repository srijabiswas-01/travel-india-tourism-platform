# Travel India CMS Implementation Plan

## Objective

Convert the existing Travel India MIS into a controlled content-management system where authorized administrators can manage public content and media without editing PHP, HTML, or CSS files.

The CMS must preserve responsive layouts, accessibility, light/dark themes, customer workflows, and the India tourism hierarchy:

```text
State or Union Territory
  -> Destination
    -> Package
```

## Design Boundary

The admin will control approved content fields and design tokens. It will not provide an unrestricted CSS or HTML editor because arbitrary code could break layouts or introduce stored cross-site scripting.

Editable design settings:

- Brand, accent, page, surface, text, and muted colors
- Heading and body font choices from an approved list
- Base font size and spacing density
- Border radius and shadow intensity
- Hero overlay strength and section visibility
- Public headings, descriptions, button labels, and footer text

Fixed by the application:

- Responsive breakpoints and structural layout
- Authentication and authorization markup
- Form validation and security controls
- Booking and payment business rules
- Admin navigation and destructive-action safeguards

## Phase 1: Media Library

### Database

Create `media_assets`:

```text
id
original_name
stored_name
relative_path
mime_type
extension
file_size
width
height
alt_text
caption
usage_type
status
uploaded_by
created_at
updated_at
```

The database stores metadata and relative paths. The binary image remains in a writable upload directory.

### Existing Media Migration

1. Scan the current `img/` directory.
2. Insert every existing image into `media_assets` without copying it.
3. Record dimensions, MIME type, size, and current relative path.
4. Link state, destination, package, logo, gallery, team, contact, and hero records to a media ID.
5. Keep old path columns temporarily for rollback.
6. Verify all public images render through media records.
7. Remove obsolete path dependencies only after verification.

### Upload Rules

- Images only: JPEG, PNG, WebP, GIF, and SVG where explicitly allowed.
- Detect MIME type with `finfo`; never trust the browser filename.
- Decode raster images with an image library before accepting them.
- Maximum CMS image size: 5 MB.
- Generate a random server filename; never use the original name as a path.
- Store files under `uploads/media/YYYY/MM/`.
- Deny PHP and executable extensions.
- Add an upload-directory `.htaccess` that disables script execution.
- Require meaningful alt text for public content images.
- Generate optimized WebP and thumbnail variants where GD support exists.
- Refuse deletion while an image is referenced; offer replacement or archive instead.

### Media Library Interface

- Search by filename, alt text, type, and usage.
- Grid and list views.
- Upload, preview, edit metadata, replace, archive, and delete-unused actions.
- Reference count showing where an asset is used.
- Reusable media picker for every admin content form.

## Phase 2: Region And Destination Editing

Extend the current States & UTs interface:

- Add Edit action.
- Replace image-path inputs with media upload and media picker controls.
- Edit name, type, slug, image, summary, SEO title, and SEO description.
- Add destination management beneath each region.
- Add, edit, enable, disable, and delete-unused destinations.
- Prevent deletion when packages or bookings depend on the record.

## Phase 3: Package Media And Editing

Extend package management:

- Replace image path with a featured-image media picker.
- Add a package gallery with ordering and captions.
- Edit state, destination, itinerary, inclusions, exclusions, duration, hotel rating, transport, INR price, and status.
- Provide Preview, Draft, Publish, Archive, and Delete-unused actions.
- Preserve a package snapshot on booking so later edits do not change historical prices or names.

## Phase 4: Page Content Management

Create:

```text
pages
page_sections
site_settings
navigation_items
social_links
```

Initial managed pages:

- Home
- Packages
- About
- Contact
- Login and registration
- Customer profile
- Footer

Each page is assembled from approved section types such as hero, state grid, text content, gallery, services, team, contact information, and call to action.

Admin capabilities:

- Edit section headings and supporting text.
- Select or replace section media.
- Reorder supported sections.
- Enable or disable optional sections.
- Preview changes before publishing.
- Restore the previous published revision.

## Phase 5: Theme Settings

Create `theme_settings` with validated design tokens rather than raw CSS.

Settings include:

- Light and dark palette values
- Approved heading and body fonts
- Button style
- Card radius
- Content width
- Header density
- Hero overlay
- Animation intensity

On publish, PHP exposes these values as escaped CSS custom properties. Invalid colors, unsupported font names, and values outside safe ranges are rejected.

## Phase 6: General Site Settings

Admin Settings sections:

- Branding: site name, logo, favicon
- Contact: address, phone, email, map URL
- Social links
- Navigation labels and ordering
- Footer text and copyright year
- Booking defaults and support messages
- SEO defaults
- Maintenance mode

Sensitive runtime configuration such as database credentials must remain outside this interface.

## Phase 7: Roles, Audit, And Publishing

Suggested permissions:

- Super Admin: all settings and destructive actions
- Content Manager: pages, packages, destinations, and media
- Support: bookings, customers, and enquiries

All create, update, publish, archive, replace, and delete actions are written to `audit_logs`.

Content publishing workflow:

```text
Draft -> Preview -> Published -> Archived
```

Page and theme settings should keep revisions so an administrator can roll back accidental changes.

## InfinityFree Deployment

The upload design is compatible with ordinary PHP file uploads on InfinityFree when these limits are respected:

- Keep each CMS image below 5 MB, even though the documented non-PHP file limit is 10 MB.
- Do not use the CMS to upload large videos.
- Host videos externally or deploy small optimized videos through FTP.
- Do not rely on changing `php.ini`; InfinityFree does not permit account-level changes.
- Verify that `uploads/media/` is writable after deployment.
- Use relative paths because the production domain differs from localhost.
- Export/import both MySQL data and uploaded media during deployment.
- Keep the application PHP files below the host's documented per-file PHP limit.

## Deployment Checklist

1. Back up the local database and `img/` directory.
2. Run media metadata migration locally.
3. Test uploads, replacements, references, and deletion guards.
4. Test malicious extensions, incorrect MIME types, oversized files, and SVG scripts.
5. Upload application files through FTP.
6. Import the production database through phpMyAdmin.
7. Upload `img/` and `uploads/` while preserving relative paths.
8. Configure production database credentials outside version control.
9. Confirm upload-directory permissions.
10. Test public media, admin upload, light/dark themes, and mobile layouts.

## Implementation Order

1. Media schema and secure upload service
2. Existing-image metadata migration
3. Media library and picker
4. State/UT and destination editing
5. Package image/gallery editing
6. Page and section content management
7. Theme token settings
8. Branding, navigation, and footer settings
9. Revision, preview, and rollback workflow
10. InfinityFree deployment test

## Acceptance Criteria

- No public content image requires a hardcoded PHP path.
- Every media record has validated type, size, path, and alt text.
- Referenced media cannot be accidentally deleted.
- Every state, destination, package, and managed section can select media from the library.
- Public text and approved theme values can be changed without editing source code.
- Light and dark themes remain readable after customization.
- Admin changes are audited and reversible where appropriate.
- Image upload works on the production host within the configured 5 MB application limit.
