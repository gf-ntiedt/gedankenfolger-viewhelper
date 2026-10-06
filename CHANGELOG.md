# Changelog

All notable changes to this project will be documented in this file.

## [13.4.1] - 2026-10-06

### Fixed

- **namespace:** Register gfv namespace in ext_localconf.php for TYPO3 13 (d50fc12)


## [13.4.0] - 2026-10-05

### Added

- **ip:** Add secureMode argument resolving client IP via TYPO3 reverse-proxy trust (6e9c5f8)


### Changed

- **ip:** Drop redundant @return void to satisfy php-cs-fixer (9defc4f)


### Documentation

- **ip:** Document secureMode and warn about client-controlled headers in default mode (f99f6e1)


## [13.3.1] - 2026-06-26

### Added

- **viewhelper:** Add scheme argument to gfv:link.tel; fix preg_replace cast in uri.tel (382e24e)


## [13.3.0] - 2026-06-26

### Added

- **viewhelper:** ⚠ **BREAKING** Replace UrlschemeViewHelper with gfv:link.tel and gfv:uri.tel (68df563)
  - **BREAKING CHANGE:** gfv:link.urlscheme is removed; migrate to gfv:link.tel


## [13.2.10] - 2026-05-29

### Documentation

- **readme:** Add changelog and acknowledgements sections, remove misplaced code quality block (def4e70)


## [13.2.7] - 2026-05-26

### Changed

- **viewhelpers:** Remove redundant @version annotations from all ViewHelper classes (7b7796b)


## [13.2.6] - 2026-05-26

### Fixed

- **PackageInfoViewHelper:** Use TYPO3Fluid namespace for AbstractViewHelper (99b1972)


## [13.2.5] - 2026-05-26

### Changed

- **SvgInlineViewHelper:** Replace custom DOM sanitizer with TYPO3 Core SvgSanitizer (cfb775c)


## [13.2.4] - 2026-05-26

### Documentation

- **readme:** Add TER link, GitHub link and code quality section (24bf95e)


## [13.2.3] - 2026-05-22

### Added

- **icons:** Update Extension.svg icon (b0569fd)


## [13.2.2] - 2026-05-22

### Fixed

- Define $_EXTKEY, apply CGL and error handling improvements (397df1c)


### Security

- Fix XSS/injection vulnerabilities and harden ViewHelpers (13.2.1) (c57e020)


## [13.2.0] - 2026-01-23

### Added

- **viewhelper:** Add PackageInfoViewHelper for Composer metadata (6ba261c)


### Changed

- **viewhelper:** Improve phone number formatting logic (b00fe51)


### Documentation

- **readme:** Add PackageInfoViewHelper documentation. Extend and optimize documentation (a160708)


### Miscellaneous

- **package:** Bump version to 13.2.0 (8392f76)


## [13.1.0] - 2025-12-16

### Added

- **icons:** Add new SVG icon for extension (4aea914)


### Documentation

- **README:** Add notice on logo and trademark use (5ad0db9)


### Fixed

- Gitignore with actual logic (50f21e1)


### Miscellaneous

- **version:** Bump version to 13.0.5 (fe5f140)

- **version:** Update version to 13.1.0 (40497fa)


### Add

- **viewhelper:** With hardened inline svg rendering (f0628d5)



