# Geeklog Store

![Coming soon](docs/images/geeklog-store-plugin-coming-soon.png)

Geeklog Store is a modular e-commerce plugin for the Geeklog CMS.

It provides a modern commerce foundation without tying Geeklog to a single payment provider, country, tax model or shipping carrier.

**Store 0.7.1** hardens the international tax, shipping and unified order-total engine introduced in 0.7.0. It also provides an explicit Geeklog upgrade path for installations already running the distributed 0.7.0 test archive.

The project is designed to evolve toward a complete commerce platform supporting online sales, physical sales, digital products, QR-based commerce, advanced shipping and tax rules, sales pages, suppliers and marketplace features.

## Main features

Current features include:

- Physical and digital products
- Product categories
- Product images and galleries
- Product stock management
- Shopping cart
- International checkout
- Customer orders and order history
- Order and payment statuses
- Manual payment / bank transfer
- Point of Sale (POS)
- POS payment methods: cash, card, cheque, bank transfer and other
- Printable POS receipts
- Shared stock between online sales and POS
- Product and order administration
- Product image selection through the Geeklog File Manager
- French and English language files
- Geeklog Configuration API integration
- Built-in development roadmap
- Configurable tax classes
- Configurable tax zones and destination matching
- Configurable tax rates, including compound rates
- Tax-inclusive or tax-exclusive catalogue pricing
- Separate shipping zones
- Flat-rate, free, threshold-based, weight-based and local-pickup shipping methods
- Product tax class, weight and dimension fields
- Unified Store total calculator shared by online checkout and POS
- Immutable subtotal, shipping and tax snapshots on new orders

## Philosophy

### International by design

Geeklog is used internationally, so Store must not assume a specific country, currency, tax model, shipping carrier or address format. Tax and shipping rules are merchant-configured rather than hard-coded for a particular jurisdiction.

### Payment-provider independent

PayPal, Stripe and other payment services are intended to be payment drivers, not the core of Store.

The Store core remains responsible for products, prices, taxes, shipping, discounts, orders, stock, customers, refunds and commercial records. Payment providers handle payment-related operations only.

### One commerce engine

Online checkout, POS, payment links and future sales channels should all use the same products, stock, orders, payments, tax calculations, shipping calculations and total calculation engine.

### Single-store simple, marketplace ready

Store is currently a single-store system. Future marketplace functions must remain optional so a normal Geeklog site does not inherit unnecessary multi-vendor complexity.

## Point of Sale

Store includes an administrator-only Point of Sale interface suitable for shops, markets, trade shows, associations, events and temporary stands.

Products can be added quickly to a sale and paid using cash, card, cheque, bank transfer or another manually recorded method. POS sales use the same stock, order and payment records as online sales. The 0.7 commerce engine also shares the same tax and total calculator with POS.

## Taxes and shipping

Store deliberately installs **no national tax percentages and no carrier-specific rules**.

The merchant configures tax classes, tax zones, tax rates, shipping zones and shipping methods. Destination matching currently supports country and optional region/state/province codes. Postal-pattern storage is reserved for later routing refinement.

The current 0.7 engine uses **line-level monetary rounding**. The configuration model reserves subtotal rounding for future refinement; subtotal tax rounding should not yet be considered implemented.

## Store 0.7.1

0.7.1 focuses on hardening the 0.7 commerce foundation:

- Explicit upgrade from Store 0.7.0
- Monetary database precision normalized to `DECIMAL(12,4)`
- Additional country and tax-class lookup indexes
- Stronger tax/shipping reference validation
- Reference-safe deletion of tax and shipping configuration
- Tax class, weight and dimensions integrated into the main product editor
- Digital products keep shipping weight and dimensions at zero
- Physical technical details displayed below the full product description
- Order totals no longer present shipping tax as an additional line when it is already included in the stored tax total

Historical commercial snapshots are preserved. Store does not recalculate old tax or shipping totals during the 0.7.0 → 0.7.1 migration.

## Roadmap

The roadmap includes, among other features:

- Promotions and coupons
- QR codes for products, carts, receipts and tickets
- Customer-to-POS QR cart transfer
- Sales page builder
- Product FAQs and customer reviews
- Bundles and product variants
- Payment links
- PayPal and Stripe drivers
- Secure digital downloads
- Paid access to Geeklog groups and content
- Gift cards and loyalty
- Invoices, credit notes and quotes
- Returns and RMA management
- Suppliers and purchase orders
- Automatic restocking suggestions
- Cash register sessions
- Commercial analytics
- Ticketing
- Barcode and QR product scanning
- Marketplace mode
- Multi-vendor catalogs and vendor stores
- Platform commissions
- Vendor balances and payouts
- Public Store pages capable of using full-width Geeklog layouts

The detailed roadmap is available in `docs/ROADMAP.md` and can also be viewed from Store administration.

## Marketplace direction

Future marketplace features may include multiple sellers, seller-specific storefronts, products in a shared catalog, vendor-specific administration, platform commissions, multi-vendor orders, seller balances, payouts and vendor reporting.

## Suppliers and inventory

Store distinguishes marketplace sellers from inventory suppliers. Planned supplier management includes supplier references, purchase prices, preferred suppliers, reorder thresholds, target stock levels, purchase orders, partial deliveries, stock replenishment and supplier price history.

## Requirements

Store is intended to support:

- Geeklog 2.1.1 and later
- PHP 5.6 and later where technically possible

Development and testing focus on modern Geeklog installations while maintaining backward compatibility as long as it remains technically reasonable.

The Store 0.7.1 CI workflow checks PHP 5.6 and PHP 8.3 syntax plus commerce-calculator regression tests. This is not a substitute for a complete Geeklog runtime test on every PHP/Geeklog combination.

## Installation

Install Store like a standard Geeklog plugin:

1. Copy the plugin files into the appropriate Geeklog directories.
2. Open the Geeklog Plugin Administration page.
3. Install Store.
4. Configure the plugin from the Geeklog Configuration interface.
5. Open Store administration to create products and configure taxes/shipping.

## Upgrade

When upgrading from Store 0.6.x, use Geeklog's normal plugin update mechanism. The 0.7.0 migration creates the international commerce tables and extends product/order snapshots before the 0.7.1 hardening migration is applied.

When upgrading from the distributed **Store 0.7.0** archive, use the same Geeklog plugin update mechanism. Store 0.7.1 preserves existing products, orders, payments, tax configuration and shipping configuration while normalizing the remaining legacy monetary columns and indexes.

## Development status

Store 0.7.1 remains development/testing software while the international commerce milestone is stabilized.

See `docs/STATUS-0.6.5.md` for the frozen pre-0.7 regression baseline, `docs/ARCHITECTURE-0.7.0.md` for the 0.7 architecture, `docs/TESTING-0.7.0-alpha.md` for the commerce test plan, and `CHANGELOG.md` for release history.

The project should be considered development / testing software until the 1.0 release.

## License

Store is distributed under the GNU General Public License version 2 or, at your option, any later version. See `LICENSE` for details.
