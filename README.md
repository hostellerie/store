# Geeklog Store

Geeklog Store is a modular e-commerce plugin for the Geeklog CMS.

It is designed to provide a modern commerce foundation for Geeklog without tying the plugin to a single payment provider or a single country.

Store currently includes a product catalog, shopping cart, order management, stock handling, payment records and a Point of Sale interface for in-person sales.

The project is designed to evolve toward a complete commerce platform supporting online sales, physical sales, digital products, QR-based commerce, advanced shipping and tax rules, sales pages, suppliers and marketplace features.

## Main features

Current features include:

- Physical and digital products
- Product categories
- Product images and galleries
- Product stock management
- Shopping cart
- Checkout
- Customer orders
- Order history
- Order statuses
- Payment records
- Manual payment / bank transfer
- Point of Sale (POS)
- POS payment methods:
  - Cash
  - Card
  - Cheque
  - Bank transfer
  - Other
- Printable POS receipts
- Shared stock between online sales and POS
- Product administration
- Order administration
- Product image selection through the Geeklog File Manager
- French and English language files
- Geeklog Configuration API integration
- Built-in development roadmap

## Philosophy

Geeklog Store follows several important principles.

### International by design

Geeklog is used internationally, so Store must not assume:

- a specific country
- the euro
- French VAT rules
- a specific tax model
- a specific shipping carrier
- a specific address format

Future tax, shipping, currency and marketplace features will therefore be implemented through configurable and extensible systems.

### Payment-provider independent

PayPal, Stripe and other payment services are intended to be payment drivers, not the core of Store.

The Store core remains responsible for:

- products
- prices
- taxes
- shipping
- discounts
- orders
- stock
- customers
- refunds
- accounting records

Payment providers only handle payment-related operations.

### One commerce engine

Online checkout, POS, payment links and future sales channels should all use the same:

- products
- stock
- orders
- payments
- tax calculations
- shipping calculations
- total calculation engine

This avoids duplicated business logic.

## Point of Sale

Store includes an administrator-only Point of Sale interface suitable for:

- shops
- markets
- trade shows
- associations
- events
- temporary stands

Products can be added quickly to a sale and paid using cash, card, cheque, bank transfer or another manually recorded method.

POS sales use the same stock and order tables as online sales.

## Planned development

The roadmap includes, among other features:

- International tax engine
- Shipping zones and shipping methods
- Promotions and coupons
- QR codes for products, carts, receipts and tickets
- Customer-to-POS QR cart transfer
- Sales page builder
- Bundles and product variants
- Payment links
- PayPal
- Stripe
- Secure digital downloads
- Paid access to Geeklog groups and content
- Gift cards
- Loyalty program
- Invoices and credit notes
- Quotes
- Returns and RMA management
- Product reviews
- Product FAQs
- Suppliers
- Purchase orders
- Automatic restocking suggestions
- Cash register sessions
- Commercial analytics
- Ticketing
- Barcode and QR product scanning
- Marketplace mode
- Multi-vendor catalogs
- Vendor stores
- Platform commissions
- Vendor balances and payouts

The detailed roadmap is available in:

```text
docs/ROADMAP.md
