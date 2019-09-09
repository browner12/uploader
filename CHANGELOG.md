# Changelog

All notable changes to `browner12/uploader` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [UNRELEASED]

## [4.0.0] - 2019-09-08

### Added
- support for Laravel 6.0

### Removed
- support for Laravel 5.6, 5.7, and 5.8

## [3.1.0] - 2019-09-08

### Added
- support for Laravel 5.8

## [3.0.0] - 2019-01-07

### Changed
- Make PHP 7.1.3 the minimum version requirement.

### Removed
- drop support for Laravel < 5.6
- drop PHP 5.6 and 7.0 on Travis

## [2.3.0] - 2019-01-07

### Added
- support for Laravel v5.7

## [2.2.0] - 2018-02-19

### Added
- support Laravel auto-discovery
- support for Laravel v5.6

### Changed
- run Travis on PHP 7.1 and 7.2, and remove HHVM

## [2.1.1] - 2017-10-17

### Changed
- use the `||` logical operator instead of `OR`

## [2.1.0] - 2017-10-15

### Added
- added events so users can hook into process

### Changed
- use the `&&` logical operator instead of `AND`

### Fixed
- fix some docblocks to use the correct namespace for the exceptions

## [2.0.0] - 2017-9-03

### Added
- new marker exceptions for more granularity when dealing with errors.

## [1.1.0] - 2016-05-01

### Added
- new Artisan command to reprocess images.

### Changed
- creating optimized or thumbnail images will no longer overwrite existing files by default. an additional parameter, `overwrite`, has been added to force overwriting existing files. 

## [1.0.4] - 2016-02-29

### Fixed
- sanitize the filename if using the original filename

## [1.0.3] - 2016-02-29

### Fixed
- ensure that thumbnails and optimized are orientated correctly.

## [1.0.2] - 2016-02-28

### Fixed
- should not have been pulling in `symfony/symfony`. switched to use `symfony/http-foundation`.

## [1.0.1] - 2016-02-28

### Fixed
- `symfony/symfony` dependency was pointing to `~2` when we should also allow `~3`.

## 1.0.0 - 2016-02-26

### Added
- new uploader package

[unreleased]: https://github.com/browner12/uploader/compare/v3.1.0...HEAD
[3.1.0]: https://github.com/browner12/uploader/compare/v3.0.0...v3.1.0
[3.0.0]: https://github.com/browner12/uploader/compare/v2.3.0...v3.0.0
[2.3.0]: https://github.com/browner12/uploader/compare/v2.2.0...v2.3.0
[2.2.0]: https://github.com/browner12/uploader/compare/v2.1.1...v2.2.0
[2.1.1]: https://github.com/browner12/uploader/compare/v2.1.0...v2.1.1
[2.1.0]: https://github.com/browner12/uploader/compare/v2.0.0...v2.1.0
[2.0.0]: https://github.com/browner12/uploader/compare/v1.1.0...v2.0.0
[1.1.0]: https://github.com/browner12/uploader/compare/v1.0.4...v1.1.0
[1.0.4]: https://github.com/browner12/uploader/compare/v1.0.3...v1.0.4
[1.0.3]: https://github.com/browner12/uploader/compare/v1.0.2...v1.0.3
[1.0.2]: https://github.com/browner12/uploader/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/browner12/uploader/compare/v1.0.0...v1.0.1
