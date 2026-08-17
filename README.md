# Geeklog Store

![Coming soon](docs/images/geeklog-store-plugin-coming-soon.png)

Geeklog Store is a modular e-commerce plugin for the Geeklog CMS.

It is designed to provide a modern commerce foundation for Geeklog without tying the plugin to a single payment provider or a single country.

**Store 0.7.0 alpha** introduces the first international tax, shipping and unified order-total engine on top of the stabilized 0.6.5 commerce baseline.

The project is designed to evolve toward a complete commerce platform supporting online sales, physical sales, digital products, QR-based commerce, advanced shipping and tax rules, sales pages, suppliers and marketplace features.

## Main features

Current features include:

- Physical and digital products
- Product categories
- Product images and galleries
- Product stock management
- Shopping cart
- International checkout
- Customer orders
- Order history
- Order statuses
- Payment records
- Manual payment / bank transfer
- Point of Sale (POS)
- POS payment methods: cash, card, cheque, bank transfer and other
- Printable POS receipts
- Shared stock between online sales and POS
- Product administration
- Order administration
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

Geeklog is used internationally, so Store must not assume a specific country, currency, tax model, shipping carrier or address format. Tax and shipping rules are therefore merchant-configured rather than hard-coded for a particular jurisdiction.

### Payment-provider independent

PayPal, Stripe and other payment services are intended to be payment drivers, not the core of Store.

The Store core remains responsible for products, prices, taxes, shipping, discounts, orders, stock, customers, refunds and commercial records. Payment providers handle payment-related operations only.

### One commerce engine

Online checkout, POS, payment links and future sales channels should all use the same products, stock, orders, payments, tax calculations, shipping calculations and total calculation engine.

### Single-store simple, marketplace ready

Store is currently a single-store system. Future marketplace functions must remain optional so a normal Geeklog site does not inherit unnecessary multi-vendor complexity.

## Point of Sale

Store includes an administrator-only Point of Sale interface suitable for shops, markets, trade shows, associations, events and temporary stands.

Products can be added quickly to a sale and paid using cash, card, cheque, bank transfer or another manually recorded method. POS sales use the same stock, order and payment records as online sales. Starting with 0.7.0, POS sales also use the shared Store tax and total calculator.

## Taxes and shipping in 0.7.0 alpha

Store 0.7.0 deliberately installs **no national tax percentages and no carrier-specific rules**.

The merchant can configure tax classes, tax zones, tax rates, shipping zones and shipping methods. Destination matching currently supports country and optional region/state/province codes. Postal-pattern storage is reserved for later routing refinement.

The current alpha uses **line-level monetary rounding**. The configuration model reserves a subtotal-rounding option for future refinement, but subtotal tax rounding should not yet be considered implemented.

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

The detailed roadmap is available in:

```text
docs/ROADMAP.md
```

It can also be viewed directly from the Store administration interface.

## Marketplace direction

Future marketplace features may include multiple sellers, seller-specific storefronts, products in a shared catalog, vendor-specific administration, platform commissions, multi-vendor orders, seller balances, payouts and vendor reporting.

## Suppliers and inventory

Store distinguishes marketplace sellers from inventory suppliers. Planned supplier management includes supplier references, purchase prices, preferred suppliers, reorder thresholds, target stock levels, purchase orders, partial deliveries, stock replenishment and supplier price history.

## Requirements

Store is intended to support:

- Geeklog 2.1.1 and later
- PHP 5.6 and later where technically possible

Development and testing focus on modern Geeklog installations while maintaining backward compatibility as long as it remains technically reasonable.

The 0.7.0 development branch is automatically syntax-checked against PHP 5.6 and PHP 8.3.

## Installation

Install Store like a standard Geeklog plugin:

1. Copy the plugin files into the appropriate Geeklog directories.
2. Open the Geeklog Plugin Administration page.
3. Install Store.
4. Configure the plugin from the Geeklog Configuration interface.
5. Open the Store administration page to create products and configure taxes/shipping.

When upgrading from Store 0.6.x, use Geeklog's normal plugin update mechanism. Store 0.7.0 adds the international commerce tables and extends product/order snapshots without rewriting historical tax or shipping data that did not previously exist.

## Development status

Store 0.7.0 alpha is a development/testing snapshot of the international tax, shipping and unified order-total milestone.

See `docs/STATUS-0.6.5.md` for the frozen pre-0.7 regression baseline, `docs/ARCHITECTURE-0.7.0.md` for the 0.7 architecture, `docs/TESTING-0.7.0-alpha.md` for the alpha test plan, and `CHANGELOG.md` for release history.

The project should be considered development / testing software until the 1.0 release.

## License

Store is distributed under the GNU General Public License version 2 or, at your option, any later version. See `LICENSE` for details.
