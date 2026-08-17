# Changelog

## 0.7.0 alpha - International taxes, shipping and order totals

This development snapshot introduces Store's first international commerce calculation layer. It is intended for testing before the 0.7.0 milestone is finalized.

### Added

- Unified order-total calculator shared by online checkout and POS
- Tax classes
- Tax zones with country and optional region matching
- Merchant-configured tax rates
- Compound tax-rate calculation in priority order
- Tax-inclusive and tax-exclusive product pricing modes
- Shipping zones independent from tax zones
- Flat-rate shipping
- Free shipping
- Free shipping above a threshold
- Weight-based shipping
- Local pickup
- Product tax class, weight and dimension fields
- International order address snapshots with region and ISO country code
- Order subtotal, shipping and tax snapshots
- Order-item tax class, label, rate and tax snapshots
- Administration pages for taxes and shipping
- Public checkout destination/quote refresh flow
- 0.7.0 order detail and POS receipt totals
- 0.7.0 architecture and alpha test documentation
- Automated PHP 5.6 / PHP 8.3 syntax and calculator regression checks
- Complete English and French labels for all Store configuration parameters
- Store configuration tabs for General, Catalogue, Checkout and payment, Tax and shipping, and Display
- Public Store layout setting: no side columns, left only, right only, or both
- Public product display of configured weight and dimensions for physical products

### Changed

- Existing 0.7.0 alpha installations automatically receive the clearer configuration-tab organization without resetting saved values.
- Public Store pages now consistently use the configured Geeklog block-column layout.
- Physical product dimensions are shown only when at least one configured measurement is greater than zero.

### Upgrade

The normal Geeklog `plugin_upgrade_store()` path migrates Store 0.6.x installations to the new 0.7.0 schema.

Historical orders are preserved. Store does not invent historical tax or shipping amounts that older versions did not record.

### International design

Store 0.7.0 ships no national tax percentage, country-specific legal rule or carrier-specific shipping rule. Merchants configure the rules that apply to their own Store installation.

### Current alpha limitation

Line-level tax rounding is implemented and tested. The configuration model reserves subtotal tax rounding for a later refinement; subtotal rounding should not yet be considered implemented.

## 0.6.5 - Stabilization and documentation

This release freezes the first functional Store baseline before the international tax, shipping and order-total work starts in 0.7.0.

### Changed

- Standardized the headers of all PHP and `functions.inc` files.
- Updated plugin metadata and documentation to 0.6.5.
- Cleaned minor formatting in the upgrade routine.
- Documented that 0.6.5 requires no database schema migration.
- Added a functional baseline document for pre-release testing and future regression checks.

### Functional baseline retained

- Product catalogue and categories
- Physical and digital products
- Product image galleries and Geeklog File Manager selection
- Cart and checkout
- Orders and order history
- Stock management
- Manual payment records
- POS / cash-register workflow
- Cash, card, cheque, bank transfer and other POS payment records
- Printable POS receipts
- Administration roadmap viewer

### Database

No schema changes from 0.6.4.

## 0.6.x

The 0.6.x series established the Store core, payments, POS and project documentation. See `docs/ROADMAP.md` for the planned development path.
