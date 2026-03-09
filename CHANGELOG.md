# Changelog

All notable changes to this project will be documented in this file.

## [2.1.0] - 2026-03-09

### Added
- **Publishable config file** (`config/statamic-iconify.php`) for global restrictions
  - `allowed_prefixes` — restrict available icon sets site-wide
  - `allowed_categories` — restrict to specific Iconify categories
  - `allowed_licenses` — restrict to specific license types (e.g. MIT, Apache 2.0)
  - `default_store_as` — set default storage format for new fields
- **Field-level filtering** via blueprint config dropdowns
  - Icon Sets — multi-select populated from Iconify API (filtered by global config)
  - Category — single select dropdown
  - Licenses — multi-select dropdown
  - Field config can only narrow within global config bounds
- **Filter badges** in the search modal showing active restrictions with color coding
  - Purple for icon sets, blue for category, green for licenses
  - Grouped with truncation (shows 2 items + "N more" with tooltip)
- **Dark mode support** for icon grid hover labels using Statamic UI Badge component
- **Pest test suite** (PHP) — config, controller, fieldtype, and tag tests
- **Vitest test suite** (JS) — filtering logic, URL building, icon mapping
- **CP route** `GET /iconify/config` for frontend config access
- Collections data cached for 24 hours to minimize API calls

### Fixed
- Icon tag now handles plain string values correctly (was crashing on `->raw()`)
- Grid card overflow clipping for rounded corners

## [2.0.0] - 2026-01-19

### Changed
- Updated for Statamic 6 (Vue 3)

## [1.3.0] - 2024-05-27

### Changed
- Added Statamic 5.3 dark mode support

## [1.2.0] - 2024-05-09

### Changed
- Added Statamic 5 support

## [1.1.0] - 2024-05-02

### Fixed
- Bug when using the fieldtype in a global

## [1.0.0] - 2023-10-02

### Added
- Initial stable release
- Iconify fieldtype with search and select
- Store as icon name or SVG data
- Antlers tag for rendering stored SVGs
