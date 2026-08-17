# Store 0.6.5 functional baseline

Store 0.6.5 is the stabilization snapshot of the project before development of the international tax, shipping and order-total engine.

Its purpose is to preserve a known functional state that can be used for regression testing while Store evolves.

## Supported baseline

The plugin is designed for Geeklog 2.1.1 and later, while keeping PHP 5.6-compatible syntax where practical. Modern Geeklog/PHP versions should also remain supported.

## Features available at this snapshot

### Catalogue

- Product CRUD in administration
- Physical and digital product types
- SKU, slug, descriptions, price, currency and stock
- Categories
- Main image and product gallery
- Image selection through the Geeklog File Manager
- Public catalogue and product pages

### Cart and checkout

- Session cart
- Quantity changes and removal
- Stock validation for physical products
- Checkout customer details
- Order creation
- Immutable order-line product name, SKU, type and price snapshots

### Orders

- Order administration
- Customer order history
- Order statuses
- Payment status and references
- Stock reservation and restoration on cancellation
- Online/POS order source distinction

### Payments

- Provider-independent payment records
- Manual / bank-transfer workflow
- Administrator payment confirmation
- POS recording methods: cash, card, cheque, bank transfer and other

Store does not yet process PayPal, Stripe or another external gateway.

### POS

- Administrator-only cash-register interface
- Product search and category filters
- Product images, SKU, category, price and stock display
- Tap/click to add products
- Quantity adjustment
- Optional customer name and email
- Shared catalogue and stock with online orders
- Printable receipt

### Documentation

- README
- Changelog
- Detailed roadmap
- Roadmap accessible from Store administration

## Intentionally not included yet

These functions belong to later roadmap phases and should not be considered regressions in 0.6.5:

- Tax engine
- Shipping engine
- Unified advanced order totals
- Coupons and promotions
- PayPal / Stripe
- Secure digital delivery
- Product variants and bundles
- QR commerce
- Sales-page builder
- Returns / RMA
- Product reviews and product FAQ
- Supplier and purchasing management
- Marketplace / multi-vendor operation

## Regression checklist

Before a later release is considered stable, verify that it still supports the following 0.6.5 flows:

1. Install Store on a clean Geeklog site.
2. Create a category and physical product.
3. Add several product images and select a primary image.
4. Create a digital product.
5. Add products to the public cart.
6. Place an online order.
7. Confirm that physical stock decreases.
8. Cancel the order and confirm stock restoration.
9. Record a manual payment.
10. Create a POS sale with cash/card/cheque.
11. Create a POS sale with bank transfer and verify pending payment status.
12. Print a POS receipt.
13. Open the Store roadmap from administration.
14. Verify French and English interfaces.

## Next development phase

0.7.0: international taxes, shipping zones/methods and a unified order-total calculation engine.
