# Changelog

All notable changes to the NESSIA Omeka theme are documented in this file.

Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).
Versioning follows [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.4.1] — 2026-11-24
### Fixed
- Concurrent first visits could both seed categories, and concurrent page loads could both write Identifiers. Setup work now runs under a MySQL named lock (`GET_LOCK`), and the hash or timestamp is re-checked once the lock is held; each record is re-checked just before its Identifier is written.
- The tools page ran a query for every record missing an Identifier. Existing Identifiers are now loaded in one query per record type, and the preview lists the first 100 while still counting them all.

## [1.4.0] — 2026-11-24
### Changed
- **The NessiaSetup plugin is now built into the theme** (`lib/NessiaSetup.php`); there is no separate plugin to install.
  - Categories from `data/categories.php` are created on the first page view after activation, and again whenever that file changes. Setting: *Create Default Categories*.
  - Collections and items without a Dublin Core Identifier get a readable one from their title, 25 at a time, at most every 5 minutes. Setting: *Readable Addresses*.
  - Preview, reorganisation and "run now" buttons are on a private tools page: the homepage with `?nessia-tools`, visible to super users only, protected by a per-session token and marked `noindex`.

### Removed
- `plugin/NessiaSetup/`. If it was installed, uninstall it after updating the theme (its data stays).

## [1.3.1] — 2026-11-24
### Changed
- Requires **Omeka Classic 3.2 or later**. `theme.ini` and the NessiaSetup plugin now declare `omeka_minimum_version` 3.2 (target 3.2.2), so Omeka refuses to activate them on older versions.

## [1.3.0] — 2026-11-24
### Added
- Homepage template (`index.php`): intro text, featured item, recent items.
- Exhibit Builder browse override with card layout and featured badge.
- Dedicated audio/video presentation on item pages with download links.
- Editorial styling for Simple Pages, tile grid for subject/collection lists, redesigned contact form.
- `nessia_option()` defaults so the theme works without saving settings.

### Fixed
- Advanced search toggle never initialised (ran before DOM ready).
- Mega menu targeted `#top-nav` instead of `#primary-nav`.
- Browse thumbnails overflowing cards; featured item text squeezed; tag commas.
- Link hover and muted text colours now meet WCAG AA contrast.
- Header shrink scroll handler throttled; smooth scroll respects reduced motion.

### Changed
- Inline footer script moved to `nessia.js`; JS strings translatable.
- Dates use Omeka `format_date()` (locale aware).
- Image items no longer forced into 80vh viewer; header background image gets an overlay.

### Removed
- Non-functional "Items Per Page" setting (use Omeka Settings instead).

## [1.2.7] — 2026-02-14
### Changed
- File viewer restyled as a showcase frame: deep primary-coloured surround with rounded corners, a decorative gold gradient accent rule along the top, layered box shadow, and a dark inner container that clips the embed cleanly. Evokes the feel of a manuscript displayed in an archival setting.
- Mobile breakpoint: viewer padding and border-radius scale down, embed height reduced to `60vh`.

## [1.2.6] — 2026-02-14
### Changed
- **File viewer moved above metadata**: on item show pages the PDF/file viewer now appears immediately after the title, before the metadata and sidebar columns. The document is the primary content visitors came to see — it should not be buried below metadata.
- Removed card box styling from `#itemfiles`; the viewer is now a clean, full-width hero element.
- Removed the redundant "FILES" heading since the viewer is self-evident.

## [1.2.5] — 2026-02-14
### Fixed
- PDF viewer in `#itemfiles` now actually expands to full container width. Previous release changed layout position but the embed itself stayed small because: (a) `.element-text` was `display:flex` constraining the child, (b) Omeka's `.item-file` wrapper and inline `width`/`height` attributes on iframes were not addressed.
- Changed `.element-text` to `display:block`, added `.item-file { width:100% }`, applied `width:100% !important` on embeds, and added a JS fallback that strips inline `width`/`height` attributes from viewers.

## [1.2.4] — 2026-02-14
### Changed
- Advanced site-wide search is now a proper dropdown panel: hidden by default, opened via an accessible toggle button with `aria-expanded` / `aria-controls`.
- Panel uses the HTML `hidden` attribute instead of `display:none` toggling for consistent ARIA + visual state.
- Toggle button styled as a pill matching the search submit aesthetic.
- Panel closes on outside click or Escape key; focus moves into first field on open and returns to toggle on close.

### Added
- `.sr-only` visually-hidden utility class for accessible labels.
- Visually-hidden `<label>` injected for the search text input when Omeka omits one (JS fallback).
- `aria-label` added to submit controls for screen-reader clarity.
- `role="group"` and `aria-labelledby` on the `#advanced-form` panel.

## [1.2.3] — 2026-02-14
### Changed
- File viewer (PDF embeds, images) on item show pages now renders full-width below the metadata/sidebar columns instead of being constrained inside the narrow 66% content column.
- PDF embeds and iframes inside `#itemfiles` default to `min-height: 80vh` for comfortable reading.

## [1.2.2] — 2026-02-13
### Fixed
- Add separator dots (•) between Arabic/Hebrew/Syriac multilingual title items by updating `nessia_multilingual_names()` in `custom.php` (previous header-only patch was ineffective).
- CSS updated: multilingual names container uses `inline-flex` with separator styling for consistent spacing even on line wrap.

## [1.2.1] — 2026-02-13
### Fixed
- Search submit button text now renders correctly across Omeka outputs (empty `<input type="submit">` and empty `<button type="submit">`).
- Added a JS fallback that sets a visible submit label when server-side output is blank.

## [1.1.3] — 2026-02-13
### Fixed
- Header search submit button now always renders visible text (prevents blank submit controls on some installs).

## [1.1.2] — 2026-02-13

### Fixed
- Footer settings now read the correct option keys (`footer_text`, `display_footer_copyright`) instead of labels.
- Escaping added for header meta values and site title output (reduces XSS risk).
- Header background image option is now applied in the header template.
- Colour scheme option is sanitised to supported values only.
- JavaScript initialisers are guarded so missing DOM elements do not trigger errors.

## [1.1.1] — 2025-02-13

### Fixed

- **500 Internal Server Error on all pages** — A duplicate `return ''; }`
  block was left behind in `nessia_multilingual_names()` during the v1.1.0
  edit, producing a PHP parse error (`Unmatched '}'` on line 56). Removed
  the stray lines. (custom.php lines 40–41)

---

## [1.1.0] — 2025-02-13

### Fixed

- **Multilingual names not displaying** — Omeka returns `null` for checkbox
  options that have not been explicitly saved in the admin panel. The
  `nessia_multilingual_names()` function now treats `null` (unsaved) as enabled,
  so Arabic, Hebrew and Aramaic names display out of the box. Only an explicit
  uncheck (`'0'`) hides them.

- **Footer floating beside content** — On Exhibits Browse, Items Browse and
  other pages with floated children, the footer was rendered alongside content
  instead of below it. Added `clear: both` on `footer[role="contentinfo"]` and
  `::after` clearfix pseudo-elements on `#content` and `#wrap` for proper float
  containment.

- **Exhibit Browse page layout** — The desktop rule `.exhibits #content > *`
  was floating all children at 66% width on every exhibit page, including the
  browse listing which has no sidebar. Replaced with scoped rules:
  `.exhibits.show` gets the 2-column float layout; `.exhibits.browse` stays
  full-width.

- **Sort links displaying as bulleted vertical list** — Added explicit
  `#sort-links ul` and `#sort-links li` rules with `display: inline`,
  `list-style: none`, and dot separators between items.

- **Navigation bullet markers leaking** — The base `ul li { list-style: disc }`
  rule was affecting navigation contexts. Added a comprehensive `list-style:
  none` reset for `nav ul li`, `.secondary-nav`, `#sort-links`, `.pagination`,
  exhibit nav, and other navigation elements.

- **Pagination and Output Formats not clearing floats** — Added `clear: both`
  to `.pagination` and `#outputs` so they render below floated item lists.

### Added

- General `.secondary-nav` styles covering all Omeka pages (exhibit browse,
  items browse, tags).
- Exhibit browse card styles (`.exhibits .exhibit`) with surface background,
  border, hover shadow, and proper text contrast.
- Exhibit show page styles for `.exhibit-text`, `.exhibit-item`,
  `.exhibit-block`.
- Breadcrumb link styles.
- Collection Tree page styles (`#collection-tree`).
- `a:visited` colour reset on primary nav links to prevent browser-applied
  visited-state colours from creating false active highlights.
- `::after` clearfix on `#wrap` for global float containment.

---

## [1.0.0] — 2025-02-13

### Added

- **Initial release** of the NESSIA theme, derived from Omeka's Rhythm theme
  and completely redesigned for the Near Eastern and Semitic Information Archive.

#### Design

- Modernist, minimalist aesthetic with clean typography using Inter (UI) and
  Noto Serif (headings/prose).
- Three configurable colour schemes driven by CSS custom properties:
  - **Manuscript Gold** — warm gold accents on deep indigo (default)
  - **Lapis Indigo** — deep blue and teal palette
  - **Desert Sand** — warm brown earth tones
- Custom SVG logo: geometric 8-pointed star (Rub el Hizb) emblem.

#### Branding

- Multilingual header displaying the site name in Arabic (Noto Sans Arabic),
  Hebrew (Noto Sans Hebrew) and Syriac Aramaic (Noto Sans Syriac) with proper
  `dir="rtl"` and `lang` attributes.
- Configurable tagline with sensible default.
- Optional custom logo upload and header background image.

#### Layout & Templates

- Responsive two-column layout for item show pages (content + sidebar metadata).
- Featured item/collection/exhibit cards on homepage with hover effects.
- Items browse with creator display, description snippets, and tag pills.
- Clean item show page with element-text sections and sidebar metadata panel.
- Mobile-first responsive breakpoints at 768px (tablet) and 767px (mobile).
- Collapsible hamburger navigation on mobile with CSS transitions.
- Header shrink-on-scroll behaviour.

#### Admin Configuration

- Tabbed settings interface with five groups:
  - **Appearance** — colour scheme, logo, header background
  - **Branding & Identity** — tagline, multilingual names toggle
  - **Homepage** — recent items count, homepage text
  - **Footer** — footer text, copyright toggle
  - **Search & Browsing** — advanced search toggle, items per page

#### Asset Separation

- CSS in `/css/` (nessia.css, normalize.css)
- JavaScript in `/javascripts/` (nessia.js)
- Images in `/images/nessia/` (logo.svg)
- No inline styles or scripts in templates.

#### Accessibility

- Skip navigation link.
- ARIA landmarks on header, nav, main content, footer.
- Semantic HTML5 structure.
- Keyboard-navigable mega menu (via Omeka's jquery-accessibleMegaMenu).

#### Other

- Print stylesheet with non-essential UI hidden.
- Smooth-scroll for anchor links.
- Search input focus/blur effects.
- GPL license.

---

## [Unreleased]

_No unreleased changes._