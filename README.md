# NESSIA — Omeka Theme

**A modernist, minimalist theme for the [Near Eastern and Semitic Information Archive](https://nessia.org)**

Version 1.3.0 · Omeka Classic 3.x · GPL License

---

## Overview
## What changed in 1.2.6

### Changed
- File viewer (PDF embed) now appears immediately after the item title, before the metadata and sidebar. The document is the primary content — it belongs at the top, not buried at the bottom.

## What changed in 1.2.5

### Fixed
- PDF viewer now fills the full content width. The v1.2.3 fix moved the files section below the columns but the embed itself stayed small due to flex layout, missing `.item-file` width rules, and Omeka's inline `width`/`height` attributes on iframes. All three issues are now addressed in CSS and JS.

## What changed in 1.2.4

### Changed
- Advanced site-wide search is now a proper accessible dropdown: hidden by default, toggled via a button with `aria-expanded`, closes on Escape or outside click, with focus management on open/close.

### Added
- Screen-reader support: visually-hidden labels for search input, `aria-label` on submit controls, `role="group"` on the advanced panel.
- `.sr-only` CSS utility class.

## What changed in 1.2.3

### Changed
- File viewer (PDF embeds) on item show pages now renders full-width below the metadata and sidebar columns, giving documents much more readable space.

## What changed in 1.2.2

### Fixed
- Separator dots (•) now appear between Arabic, Hebrew and Syriac titles in the multilingual header branding, implemented at the source in `nessia_multilingual_names()`.
- Multilingual title now uses `inline-flex` layout with dedicated separator spans for consistent spacing on wrap.

## What changed in 1.2.1

### Fixed
- Search submit button text: handles both `<input type="submit">` and `<button type="submit">` outputs, including cases where Omeka renders them empty.
- Front-end safety net: JS now enforces a visible submit label if the server-side output is blank.


## Requirements

- **Omeka Classic** 3.0 or later
- A modern web browser (Chrome, Firefox, Safari, Edge)

---

## Installation

1. Download the latest release zip from the [Releases](#) section or from your build.
2. Extract the `nessia/` folder into your Omeka installation's `themes/` directory:
   ```
   omeka/themes/nessia/
   ```
3. Log in to the Omeka admin panel.
4. Navigate to **Appearance → Themes**.
5. Click **Use this theme** next to "NESSIA".
6. Go to **Configure Theme** to adjust settings.

---

## Configuration

All settings are available under **Appearance → Configure Theme** in the Omeka admin panel, organised into tabbed groups.

### Appearance

| Setting | Type | Description |
|---|---|---|
| Colour Scheme | Dropdown | Choose from **Manuscript Gold** (warm gold + indigo, default), **Lapis Indigo** (deep blue + teal), or **Desert Sand** (warm earth tones). |
| Custom Logo | File upload | Replace the default SVG emblem. Recommended max width: 80px. SVG or PNG preferred. |
| Header Background | File upload | Optional background image for the site header. |

### Branding & Identity

| Setting | Type | Description |
|---|---|---|
| Site Tagline | Text | Displayed below the site title. Default: *Exploring Semitic Religion, Language and Heritage*. |
| Display Multilingual Names | Checkbox | Show the site name in Arabic, Hebrew and Aramaic below the English title. Enabled by default. |

### Homepage

| Setting | Type | Description |
|---|---|---|
| Homepage Recent Items | Number | Number of recently added items displayed on the homepage. Default: 5. |
| Homepage Text | Textarea (HTML) | Introductory text for the homepage. |

### Footer

| Setting | Type | Description |
|---|---|---|
| Footer Text | Textarea (HTML) | Custom text in the footer area. |
| Display Copyright | Checkbox | Show the site's copyright information in the footer. |

### Search & Browsing

| Setting | Type | Description |
|---|---|---|
| Advanced Search | Checkbox | Enable site-wide search by record type with boolean methods. Enabled by default. |
| Items Per Page | Number | Number of items per page when browsing. Default: 12. |

---

## Theme Structure

```
nessia/
├── common/
│   ├── header.php          # Site header with multilingual branding
│   └── footer.php          # Site footer with navigation and credits
├── css/
│   ├── normalize.css       # CSS reset (Nicolas Gallagher)
│   └── nessia.css          # Main stylesheet with colour scheme tokens
├── images/
│   └── nessia/
│       └── logo.svg        # Default SVG logo (8-pointed star)
├── items/
│   ├── browse.php          # Items browse template with creator display
│   └── show.php            # Item detail template with sidebar metadata
├── javascripts/
│   └── nessia.js           # Mobile nav, smooth scroll, header shrink
├── .gitignore
├── CHANGELOG.md
├── config.ini              # Theme settings with tabbed groups
├── custom.php              # Helper functions (tagline, multilingual, logo)
├── LICENSE                 # GPL license
├── README.md               # This file
└── theme.ini               # Theme metadata
```

---

## Colour Schemes

All colours are defined as CSS custom properties on the `<html>` element class, making it straightforward to create additional schemes or fine-tune existing ones.

### Manuscript Gold (default)
Deep indigo header/footer with warm gold accents. Evokes illuminated manuscript aesthetics.

### Lapis Indigo
Navy and teal palette. A cooler, more contemporary feel while maintaining scholarly gravitas.

### Desert Sand
Warm brown earth tones. Recalls the landscapes and materials of the Near East.

### Creating a Custom Scheme

To add a new colour scheme:

1. Add a new option in `config.ini` under the `colour_scheme` multiOptions.
2. Add a corresponding CSS custom property block in `css/nessia.css` following the pattern `.nessia-scheme-{name} { ... }`.
3. The `nessia_colour_scheme()` function in `custom.php` will automatically apply the class.

---

## Multilingual Branding

The header displays the NESSIA name in three Semitic scripts:

| Language | Script | Font |
|---|---|---|
| Arabic | أرشيف معلومات الشرق الأدنى والسامية | Noto Sans Arabic |
| Hebrew | ארכיון מידע המזרח הקרוב והשמי | Noto Sans Hebrew |
| Syriac Aramaic | ܐܪܟܝܒ ܝܕ̈ܥܬܐ ܕܡܕܢܚܐ ܩܪܝܒܐ ܘܫܡܝ̈ܐ | Noto Sans Syriac |

All rendered with proper `dir="rtl"` and `lang` attributes for accessibility and correct text rendering. This feature can be toggled off in the admin panel.

---

## Browser Support

- Chrome / Edge (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Mobile Safari / Chrome (iOS / Android)

---

## Development

### Prerequisites

No build tools are required. The theme uses vanilla CSS and JavaScript with no preprocessors or bundlers. Edit files directly and refresh.

### Fonts

Fonts are loaded from Google Fonts CDN:
- **Inter** — UI text, navigation, labels
- **Noto Serif** — Headings, body prose
- **Noto Sans Arabic / Hebrew / Syriac** — Multilingual header names

### CSS Architecture

The stylesheet is organised in this order:

1. CSS custom properties (colour scheme tokens)
2. Base reset and typography
3. Form elements
4. Layout (header, nav, content, footer)
5. Page-specific styles (homepage, items, exhibits, collections)
6. Utility components (pagination, tags, output formats)
7. Responsive breakpoints (768px tablet, 767px mobile)
8. Print styles

---

## Credits

- **Theme design and development** — NESSIA
- **Base theme** — Derived from [Rhythm](https://omeka.org/classic/themes/rhythm/) by the Omeka Team
- **CSS Reset** — [normalize.css](https://necolas.github.io/normalize.css/) by Nicolas Gallagher
- **Fonts** — [Google Fonts](https://fonts.google.com/) (Inter, Noto Serif, Noto Sans Arabic/Hebrew/Syriac)
- **Platform** — [Omeka Classic](https://omeka.org/)

---

## License

This theme is released under the [GNU General Public License v3.0](LICENSE).

Copyright © 2025 NESSIA (202503137729).


## 1.1.3
- Fix: ensure the header search submit button always renders a visible label.
