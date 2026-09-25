# Store 0.7.0 architecture — International taxes, shipping and order totals

This document defines the implementation contract for Store 0.7.0.

The objective is to introduce a single international calculation engine shared by online checkout, POS and future sales channels before external payment gateways are added.

## Non-negotiable principles

- No country-specific tax rules in the Store core.
- No assumption that prices are tax-inclusive or tax-exclusive.
- No assumption about currency, address format, shipping carrier or tax naming.
- Digital-only carts must not require shipping.
- Historical orders must preserve the values used at checkout even if tax or shipping settings later change.
- PayPal, Stripe and future gateways will receive a final Store-calculated amount. They do not calculate Store business rules.
- POS must use the same total calculator as online checkout.
- PHP 5.6-compatible syntax remains the target while compatibility with modern PHP is maintained.

## Calculation pipeline

Every sales channel will prepare a calculation context and call the same service.

```text
Items
  ↓
Product subtotal
  ↓
Discount subtotal (0.7.0: reserved, 0.8.0: promotion engine)
  ↓
Shipping selection
  ↓
Tax calculation
  ↓
Grand total / amount due
```

The calculator returns a normalized result containing at least:

```text
currency
items_subtotal
items_tax
shipping_subtotal
shipping_tax
discount_total
tax_total
grand_total
requires_shipping
selected_shipping_method
```

Money calculations will use decimal strings / integer minor-unit helpers where possible. Floating point values must not become the authoritative persisted representation for commercial totals.

## Data model

### Tax zones

`store_tax_zones`

- `id`
- `name`
- `active`
- `priority`
- timestamps

A tax zone is a merchant-defined geographic grouping. Examples may be supplied in documentation, but Store must never ship legal assumptions as mandatory rules.

### Tax zone locations

`store_tax_zone_locations`

- `id`
- `zone_id`
- `country_code` (ISO 3166-1 alpha-2 where possible)
- `region_code` (optional state/province/region code)
- `postal_pattern` (reserved for later refinement)

Zone matching order:

1. country + region
2. country
3. fallback/default zone

### Tax classes

`store_tax_classes`

- `id`
- `name`
- `code`
- `active`

Products reference a tax class. Store will create neutral defaults such as `standard`, `reduced`, `zero`, `exempt`, without assigning jurisdiction-specific percentages.

### Tax rates

`store_tax_rates`

- `id`
- `zone_id`
- `tax_class_id`
- `name`
- `rate`
- `priority`
- `compound`
- `active`

Rates are administrator-defined.

### Shipping zones

`store_shipping_zones`

- `id`
- `name`
- `active`
- `priority`

### Shipping zone locations

`store_shipping_zone_locations`

- `id`
- `zone_id`
- `country_code`
- `region_code`
- `postal_pattern`

Shipping geography is intentionally independent from tax geography.

### Shipping methods

`store_shipping_methods`

- `id`
- `zone_id`
- `code`
- `name`
- `method_type`
- `price`
- `free_above`
- `weight_rate`
- `minimum_weight`
- `maximum_weight`
- `tax_class_id`
- `active`
- `sort_order`

Initial method types:

- `flat`
- `free`
- `free_above`
- `weight`
- `pickup`

Carrier APIs remain future adapters and must not alter the core shipping contract.

## Product extensions

Physical products gain:

- `tax_class_id`
- `weight`
- `length`
- `width`
- `height`

Dimensions are reserved now even if 0.7.0 initially calculates only with weight.

Digital products:

- may have a tax class
- do not require shipping
- ignore weight/dimensions for delivery calculations

## International address extension

Orders will gain additional address snapshot fields:

- `region`
- `country_code`

`country` remains available as the human-readable snapshot.

Country selection in the checkout should use an ISO country code internally while retaining a readable label in the order snapshot.

## Order total snapshots

`store_orders` will gain:

- `items_subtotal`
- `discount_total`
- `shipping_method`
- `shipping_label`
- `shipping_subtotal`
- `shipping_tax`
- `tax_total`
- `total`
- `prices_include_tax`

Existing `total` remains the final amount due for backward compatibility.

`store_order_items` will gain:

- `tax_class_id`
- `tax_label`
- `tax_rate`
- `unit_tax`
- `tax_total`
- `line_subtotal`
- `line_total`

`line_total` becomes the final line amount according to the configured inclusive/exclusive price model, while the new snapshot columns explain how it was derived.

## Store configuration

0.7.0 introduces configuration options such as:

- `prices_include_tax`
- `tax_enabled`
- `tax_basis` (`shipping`, later optionally `billing` or `store`)
- `tax_rounding` (`line`, `subtotal`)
- `shipping_enabled`
- `default_country_code`
- `weight_unit`
- `dimension_unit`

Defaults must be neutral. Store must not silently assume France or any national tax percentage.

## Checkout changes

The online checkout will:

1. collect an international destination
2. determine whether the cart requires shipping
3. list eligible shipping methods
4. calculate totals through the unified calculator
5. display subtotal, shipping, taxes and amount due before order submission
6. re-run the calculator server-side when the order is submitted
7. persist snapshots from that calculation

Client-side totals are presentation only. The server calculation is authoritative.

## POS behavior

POS uses the same calculator.

Default POS behavior for 0.7.0:

- no shipping unless the operator explicitly creates a delivery order in a later version
- destination may be omitted
- taxes use the configured POS/store tax context
- tax snapshots are still stored on every order

This keeps cash-register reporting compatible with online tax reporting.

## Administration

0.7.0 administration will add dedicated sections for:

- Tax classes
- Tax zones and locations
- Tax rates
- Shipping zones and locations
- Shipping methods

The UI should provide empty-state guidance instead of preloading country-specific rates.

## Upgrade strategy

Upgrade from 0.6.5 must:

- preserve every existing product, order and payment
- add new schema without rewriting historical totals
- assign existing products to a neutral default tax class only when required by the schema
- leave historical orders with zero tax/shipping snapshot values unless explicit old data exists
- keep existing order `total` untouched

## Implementation sequence

### Step 1 — schema and table registration

Create generic tax/shipping tables and extend product/order snapshots.

### Step 2 — money and total calculator

Create testable calculation helpers independent from the checkout UI.

### Step 3 — tax matching engine

Resolve destination → zone → class → rate and calculate line/shipping taxes.

### Step 4 — shipping engine

Resolve destination and cart weight → eligible methods and costs.

### Step 5 — administration

CRUD for tax and shipping configuration.

### Step 6 — online checkout

Integrate destination, shipping selection and authoritative totals.

### Step 7 — POS

Route POS totals through the calculator and store tax snapshots.

### Step 8 — regression pass

Re-run the 0.6.5 functional baseline and specifically verify:

- physical-only carts
- digital-only carts
- mixed carts
- tax-inclusive pricing
- tax-exclusive pricing
- zero/exempt tax classes
- no matching tax zone
- no matching shipping zone
- pickup
- free shipping threshold
- weight shipping
- cancelled orders and stock restoration
- POS sales

## Explicitly out of scope for 0.7.0

These remain scheduled for later milestones:

- coupons and promotion rules (0.8.0)
- carrier APIs
- PayPal / Stripe
- marketplace vendor taxes
- vendor-specific shipping methods
- automated legal/tax advice
- invoices and credit notes
- returns and refund allocation

The 0.7.0 core must nevertheless avoid choices that would block those features later.
