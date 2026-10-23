# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/)
and this project adheres to the following versioning pattern:

Given a version number MAJOR.MINOR.PATCH, increment:

- MAJOR version when the **API** version is incremented. This may include backwards incompatible changes;
- MINOR version when **breaking changes** are introduced OR **new functionalities** are added in a backwards compatible manner;
- PATCH version when backwards compatible bug **fixes** are implemented.


## [Unreleased]

## [0.8.0] - 2026-10-23
### Changed
- PHP requirement to `~8.2.35 || ~8.3.36 || ~8.4.27 || >=8.5.12`. Earlier PHP versions can no longer install this library
- Distributions that backport the fix without changing the PHP version number may be blocked by this requirement; use `--ignore-platform-req=php` only after confirming the fix in the distribution's advisory
### Security
- Require PHP versions that fix CVE-2026-109618 and CVE-2026-109619 (PHP2Shell), which affect PHP 5.4 through 8.5.11 and allow unauthenticated remote code execution

## [0.7.0] - 2026-10-15
### Added
- plural of resource names ending in "ch", "sh" or "x", which now take "es" (speech becomes speeches)
- text outside Latin-1 (em dash, euro sign, emoji, CJK) being replaced by "?" in request bodies and query strings
- upgrade note: such characters in externalIds, tags and other strings used to reach the API as "?" and are now sent verbatim, so a retry spanning the upgrade is not caught as a repeated externalId, "query by tags/externalIds, create if not found" checks no longer match records created before it, and fields with strict character sets may now return 400

## [0.6.0] - 2026-04-29
### Changed
- starkbank-ecdsa to v2.2.0

## [0.5.1] - 2026-02-04
### Fixed
- postSubResource method

## [0.5.0] - 2025-02-18
### Added
- postSubResource method

## [0.4.1] - 2024-10-08
### Fixed
- URL encode method

## [0.4.0] - 2024-07-17
### Added
- Marketplace app user type

## [0.3.0] - 2024-07-16
### Added
- raw methods

## [0.2.1] - 2024-03-06
### Added
- put method

## [0.2.0] - 2023-06-21
### Added
- Add declarations to constructors

## [0.1.0] - 2023-04-26
### Added
- postRaw method

## [0.0.3] - 2022-10-27
### Fixed
- ecdsa dependency missing classmap

## [0.0.2] - 2022-10-26
### Fixed
- repeated autoload requirement

## [0.0.1] - 2022-09-28
### Added
- starkcore API basic functionalities for starkbank and starkinfra 
