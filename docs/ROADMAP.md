# Geeklog Store roadmap

This document is the living development roadmap for Geeklog Store. It is bundled with the plugin and can be displayed from Store administration.

Store must remain **international by design**. Geeklog is used worldwide, so no Store core feature may assume France, the euro, French VAT, a specific address format, a specific carrier, or a specific payment provider.

## Permanent architecture principles

### International first

Store must support configurable countries, zones, currencies, tax systems, shipping methods, address formats, number/date formats and units. Country-specific behavior belongs in configuration or optional adapters, never in hard-coded core rules.

### One commerce engine

Online checkout, POS, payment links, QR sales and future sales channels must share the same products, stock, orders, payments and total calculation engine.

### Payment-provider independent

PayPal, Stripe and future gateways are payment drivers. They must not own Store business logic. Store calculates products, discounts, taxes, shipping and the final amount before sending a payment request to a provider.

### Immutable commercial history

Orders, invoices, refunds and returns must keep snapshots of the commercial values that existed at transaction time: product name, SKU, price, tax, discount, shipping and seller information. Later catalogue changes must not rewrite history.

### Single-store simple, marketplace ready

A normal Geeklog site must remain easy to use as a single store. Marketplace concepts must be optional, while the data model should avoid decisions that would make future multi-vendor support impossible.

### Security and privacy

All write operations require permissions and CSRF protection. Digital files must stay outside the public web root. Payment secrets must never be logged. Store must collect only data required for commerce and must not include telemetry or installation reporting.

### Theme independence

Store must work with standard Geeklog themes. Theme-specific enhancements may exist, but core functionality must not depend on Eclipse or another theme.

---

# Completed baseline: 0.1.x to 0.6.x

The 0.6.5 stabilization snapshot includes:

- physical and digital products
- categories
- product images and galleries
- Geeklog File Manager image selection
- public catalogue and product pages
- cart and checkout
- persistent orders and immutable order-item price snapshots
- order statuses and customer history
- stock reservation and restoration on cancellation
- provider-independent payment records
- manual / bank-transfer payment
- POS / cash register
- cash, card, cheque, bank transfer and other POS payment records
- online/POS source distinction
- printable POS receipts
- French and English interfaces
- integrated roadmap and project documentation

0.6.5 is the reference regression baseline before the commercial calculation engine is expanded.

---

# Phase A — International commerce core

## 0.7.0 — International taxes, shipping and order totals

This is the next structural milestone and must be completed before external payment gateways.

### Unified total calculator

Every sales channel must call one calculation service producing, at minimum:

```text
Product subtotal
- discounts
+ shipping
+ taxes
= amount due
```

The result must be reusable by checkout, POS, admin-created orders, payment links, PayPal, Stripe, invoices, refunds and marketplace orders.

### International tax engine

Introduce generic concepts rather than country-specific tax code:

- tax zones
- tax classes
- tax rates
- inclusive or exclusive pricing
- zero/exempt classes
- customer destination and configurable tax basis
- tax labels
- tax rounding strategy
- product tax class
- optional shipping tax class
- immutable tax snapshots on order lines and totals

Store may provide examples for common countries, but it must never claim to determine a merchant's legal tax obligations automatically.

### Shipping engine

Introduce:

- shipping zones
- shipping methods
- flat-rate shipping
- free shipping
- free shipping above an order threshold
- shipping by weight
- shipping by destination
- local pickup / Click & Collect
- physical-product-only shipping requirements
- configurable availability per country/zone

Products should reserve fields for weight and dimensions even if the first implementation uses only weight.

Future carrier integrations may include postal services, pickup networks and international carriers through adapters.

### Address model

Do not assume a French postal address. Keep an extensible international model supporting optional state/region/province fields and country-specific presentation rules.

## 0.8.0 — Promotions and coupons

Add a promotion engine on top of the unified calculator:

- percentage discounts
- fixed discounts
- minimum order value
- category/product restrictions
- validity dates
- usage limits
- per-customer limits
- free shipping coupons
- automatic rules
- buy X get Y
- quantity discounts
- POS manual discount with permission control

Discount details must be snapshotted on the order.

---

# Phase B — QR commerce and conversion tools

## 0.9.0 — QR & Quick Sales

Generate QR codes for:

- product pages
- quick-buy pages
- promotional campaigns
- pre-filled carts
- sales links

Allow optional campaign/source identifiers so Store can later report scans, carts and sales attributed to a stand, flyer, event or campaign.

### Customer cart to POS

A customer should be able to build a cart on a phone, display a QR code, and let the seller scan it into Store POS for payment.

## 0.10.0 — Digital receipts and customer QR

- receipt QR after POS purchase
- receipt retrieval without requiring email
- optional customer QR tied to a Geeklog account
- fast POS customer identification
- foundation for loyalty functions

## 0.11.0 — Sales Page Builder

Each product may have a normal catalogue page and an optional conversion-oriented sales page.

Reusable/reorderable blocks may include:

- hero
- heading and text
- image
- video
- gallery
- benefits/features
- product price
- buy button
- testimonials
- FAQ
- related products
- countdown / time-limited offer
- custom HTML for trusted administrators

The builder must preserve theme independence and responsive output.

## Product FAQ

FAQ must become a Store object rather than plain text so the same questions can appear on product pages and sales pages. Plan for:

- question/answer ordering
- publication state
- product and possibly category scope
- future structured-data integration where appropriate

## Public full-width Store layout

Store configuration should let administrators choose Geeklog layout behavior for public commerce views:

- theme default
- hide left column
- hide right column
- hide both columns / full width

The option should eventually be configurable independently for catalogue, categories, products, cart, checkout, customer orders and sales pages.

---

# Phase C — Catalogue expansion and payments

## 0.12.0 — Bundles, related products and variants

### Bundles

Allow packs containing several products while stock remains attached to each physical component.

### Related commerce

- related products
- alternatives
- cross-sell
- upsell
- add-together offers

### Variants

Prepare product variants such as size, colour or material with optional variant SKU, price adjustment and stock.

## 0.13.0 — Payment/order links

Administrators should be able to prepare an order and generate a secure link for a customer. Typical uses:

- email
- messaging
- telephone sales
- simple quotations
- remote payment

## 0.14.0 — PayPal driver

Integrate PayPal as a driver using Store's final calculated total.

Required architecture:

- create payment
- return/cancel handling
- server-side payment verification
- webhook handling
- idempotency
- transaction identifiers
- capture/refund foundation

## 0.15.0 — Stripe driver

Implement Stripe against the same payment-driver contract. Store must remain capable of supporting additional providers without core rewrites.

## 0.16.0 — Secure digital delivery

- files outside web root
- entitlement created only from an eligible order/payment state
- signed/unguessable download URLs
- expiry
- maximum download count
- download log
- administrator revocation

## 0.17.0 — Paid Geeklog access

Products may grant access to Geeklog resources after payment, for example:

- group membership
- private content
- forums
- protected pages
- courses/training areas

Revocation/expiry rules must be configurable.

---

# Phase D — Customer value, accounting and after-sales

## 0.18.0 — Gift cards and store credit

- fixed or variable gift-card value
- secure code and QR representation
- partial use and remaining balance
- online and POS use
- audit trail

## 0.19.0 — Loyalty

- configurable points rules
- points balance
- POS identification through customer QR
- redemption rules
- transaction history

## 0.20.0 — Invoices and credit notes

Add formal commercial documents independent from order numbers:

- invoice numbering
- credit notes
- seller/customer snapshot
- line totals
- discounts
- shipping
- tax breakdown
- payment information
- printable HTML and later PDF output

Legal numbering/tax presentation must remain configurable by jurisdiction rather than hard-coded for one country.

## 0.21.0 — Quotes

```text
Quote -> accepted -> order -> payment
```

Support expiry dates, customer acceptance and conversion without rewriting the original quote.

## Returns / RMA

Add a proper return workflow:

```text
Return requested
-> authorized
-> received
-> inspected
-> refund/exchange decision
-> restock, quarantine or damaged disposition
```

Important: returning an item, refunding money and putting an item back into sellable stock are separate operations.

Support partial returns and keep a full audit trail.

## Customer reviews

- star/rating value
- written review
- verified-purchase indicator
- moderation
- publish/unpublish
- abuse reporting
- optional seller response
- rating average and count
- anti-spam/anti-abuse measures
- future structured-data support where appropriate

---

# Phase E — Suppliers, purchasing and inventory operations

Marketplace sellers and suppliers are different concepts:

- **Seller/Vendor:** sells through the Store marketplace.
- **Supplier:** supplies inventory to the merchant or a seller.

## Supplier management

Store supplier records may include:

- name and contact details
- supplier product reference
- purchase cost
- supplier currency
- preferred supplier
- minimum order quantity
- package quantity
- lead time
- notes and status

Products should be able to have multiple suppliers.

## Inventory planning

Introduce concepts such as:

- sellable stock
- reserved stock
- incoming stock
- reorder threshold
- target stock
- reorder quantity

Example:

```text
Current stock: 3
Reorder threshold: 5
Target stock: 20
Suggested purchase: 17
```

Suggestions require administrator approval; Store must not automatically place supplier orders by default.

## Purchase orders

- draft supplier purchase order
- group replenishment needs by supplier
- send/print purchase order
- expected delivery
- partial receipt
- backordered quantity
- stock increase on receipt
- purchase-cost history

Future extensions may include multiple warehouses, dropshipping, lots and serial numbers.

## POS cash sessions

For more professional POS use:

- open register
- opening cash balance
- cash movements
- sales by payment type
- expected cash
- counted cash
- discrepancy
- close register
- immutable session history

---

# Phase F — Events, scanning and analytics

## Ticketing and QR validation

Sell event/workshop tickets and generate unique QR tickets. A validation interface can mark a ticket as used and reject duplicate entry.

## Mobile event catalogue

A visitor scans a stand/event QR, browses a mobile catalogue, builds a cart and presents the cart QR to the seller for POS loading.

## Product scanning at POS

Use device cameras for QR scanning and later support standard barcodes/EAN where practical.

## Commercial dashboard

Unified analytics should include configurable periods and currencies, with metrics such as:

- revenue
- orders
- average order value
- online vs POS
- payment methods
- top products/categories
- discounts
- taxes collected
- shipping charged
- returns/refunds
- stock alerts

Analytics must not send data to an external Store telemetry service.

---

# Phase G — Marketplace / multi-vendor

Marketplace support is a major optional phase. It must extend Store rather than make ordinary single-store installations complicated.

## Sellers / vendors

Plan for:

- vendor profiles
- approval/status workflow
- vendor ownership of products
- vendor-specific permissions
- products in the global catalogue
- optional dedicated vendor storefronts
- vendor branding/profile information
- vendor dashboards

A future `vendor_id` concept must be considered in schema evolution before marketplace functionality is activated.

## Multi-vendor orders

A customer should still see one coherent checkout. Internally, Store may need to split fulfilment/accounting by seller while preserving a parent customer order.

Plan for:

- seller sub-orders or fulfilment groups
- per-seller item totals
- per-seller shipping when required
- per-seller tax snapshots
- returns by seller
- platform-level customer service visibility

## Platform commissions

Commission rules may support:

- percentage commission
- fixed commission
- fixed + percentage
- different rates by seller
- category/product overrides
- subscription/vendor-plan rules later

Commission values must be snapshotted when the order is created.

## Vendor balances and payouts

Plan for a ledger, not a single mutable balance:

- sale credit
- platform commission
- refund debit
- return adjustment
- manual adjustment with audit reason
- payout
- pending/available balance

Actual payout providers should remain adapters. Store should not promise automated payout availability in every country.

## Marketplace stores

Products may appear:

- in the global Store catalogue
- in a dedicated vendor store
- in both

Search, categories and sales pages must remain compatible with this model.

---

# Additional reserved extensions

The architecture should keep room for features that may be scheduled according to demand:

- subscriptions and recurring billing
- deposits and split payments
- services and booking products
- wishlists
- customer groups and B2B pricing
- multi-currency presentation and settlement rules
- multiple warehouses
- carrier tracking
- fulfilment integrations
- dropshipping
- import/export tools
- REST/API endpoints where appropriate
- webhooks for external systems
- privacy/export/delete assistance for Store customer data
- accessibility and keyboard/touch optimization

---

# Development rule for future versions

Before each major milestone:

1. Re-run the `docs/STATUS-0.6.5.md` regression baseline where still applicable.
2. Keep upgrade paths idempotent and safe.
3. Update this roadmap when architectural decisions change.
4. Update `CHANGELOG.md` and README.
5. Preserve international configuration and avoid country-specific assumptions in the core.
6. Keep payment, shipping, tax, marketplace and supplier integrations modular.
7. Do not introduce telemetry.

## Immediate next milestone

**0.7.0 — International Taxes, Shipping & Order Totals** is the next development phase. External gateways such as PayPal and Stripe should follow only after Store can calculate and snapshot a correct final payable amount independently of the payment provider.
