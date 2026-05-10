# Changelog

All notable changes to MailPurse will be documented in this file.

## v1.7.7.1 - 2026-04-27

### Added
- Addon management for admins, including ZIP upload, installation, activation, deactivation, and uninstall flows.
- Support for addon manifests via `addon.json` and storage-based addon extraction under `storage/app/addons/{slug}`.
- SuperScrape addon with scraper job/lead storage, customer scraping workflows, admin settings, permissions, CSV export, and list push actions.
- Email Warmup module with warmup configuration, tracking tables, service layer, processing job, customer CRUD screens, and dashboard navigation.
- Public homepage variant routing with configurable Home 1-4 navigation visibility and per-variant text replacement maps.
- Configurable public home page title in admin general settings.
- Customer role backfill Artisan command for assigning default customer groups to existing customers.
- Public password reset flow for both users and customers.
- Public storage fallback handling for hosting environments where `/storage/*` static delivery is unreliable.

### Changed
- Application version updated to `1.7.7.1`.
- Update server integration now reads changelog and product metadata more defensively and uses changelog versions as a fallback when resolving the latest available version.
- Campaign preflight now validates the campaign-level bounce server first, with backward-compatible fallback to the delivery server bounce configuration.
- Customer delivery server forms now expose bounce server selection in both create and edit screens.
- Public API docs now rely on the frontend docs surface and use `/openapi` to avoid hosting environments that do not forward `.json` routes correctly.
- Homepage variant settings were simplified to use configurable text maps instead of the previous editor-based approach.

### Fixed
- Unified login `Forgot Password?` flow now points to the correct reset request route.
- Password reset support works for both admin/user and customer accounts.
- API documentation access works on environments where `/openapi.json` or `/api/openapi.json` returned `404`.
- Storage asset access works on cPanel/LiteSpeed-style deployments where symlink or direct static storage serving can fail.
- Bounce server validation no longer incorrectly requires the delivery server bounce setting when a campaign-specific bounce server is selected.

### Notes
- Queue workers and the scheduler remain required for campaign processing, imports, updates, warmups, and scraping jobs.
- Addons are installed from uploaded ZIP packages rather than a hardcoded marketplace catalog.
