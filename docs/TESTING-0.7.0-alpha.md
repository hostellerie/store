# Store 0.7.0 alpha — test plan

This checklist is the acceptance plan for the first international taxes, shipping and order-total alpha.

## Upgrade

- Upgrade an existing 0.6.5 installation without losing products, orders or payments.
- Confirm previous order totals remain unchanged.
- Confirm new historical snapshot columns default to zero except `items_subtotal`, which mirrors the old total.
- Confirm neutral tax classes are created without national percentages.

## Product setup

- Assign a tax class to a physical product.
- Assign a tax class to a digital product.
- Add weight to a physical product.
- Store length, width and height.
- Confirm digital products do not require shipping.

## Tax administration

- Create a country-wide tax zone.
- Create a country + region tax zone with higher specificity.
- Create one tax rate for `standard`.
- Create another rate for `reduced`.
- Leave `zero` and `exempt` without a positive rate and verify zero tax.
- Disable a rate and verify it is no longer used.

## Shipping administration

- Create a shipping zone.
- Add a country-wide location.
- Add flat-rate shipping.
- Add free shipping.
- Add free-above-threshold shipping.
- Add weight-based shipping.
- Add local pickup.
- Verify minimum and maximum weight restrictions.

## Online checkout

### Physical cart

- Add one physical product.
- Enter a two-letter country code.
- Use **Update shipping and taxes**.
- Confirm eligible shipping methods appear.
- Select a shipping method and refresh the quote.
- Confirm subtotal, shipping, taxes and grand total.
- Place the order.
- Confirm the order stores destination, shipping and tax snapshots.
- Confirm payment amount equals the stored grand total.
- Confirm stock is decremented once.

### Digital-only cart

- Add a digital product.
- Confirm no shipping method is required.
- Confirm a digital product may still receive a configured tax rate.
- Place the order and verify no shipping snapshot is added.

### Mixed cart

- Add physical + digital products in the same currency.
- Confirm only physical items contribute to shipping weight.
- Confirm both items may have independent tax classes.
- Confirm one final grand total is persisted.

### Error cases

- Invalid customer email.
- Missing destination for a physical order.
- Country with no shipping zone while shipping is enabled.
- Selected shipping method not eligible for destination.
- Stock changes between quote and order submission.
- Mixed currencies remain rejected.

## Inclusive and exclusive pricing

- With `prices_include_tax = 0`, verify tax is added to configured product prices.
- With `prices_include_tax = 1`, verify configured product price remains the gross line amount and tax is extracted.
- Verify tax snapshots remain unchanged after changing a tax rate later.

## POS

- Create a POS cash sale.
- Create a POS card sale.
- Create a POS bank-transfer sale and verify pending payment status.
- Confirm POS uses the configured default country code for tax context.
- Confirm POS shipping remains zero in 0.7.0.
- Confirm tax snapshots are stored on POS lines and orders.
- Cancel a POS order and verify physical stock restoration.

## Order history

- View old 0.6.x orders after upgrade.
- View new 0.7.0 online orders.
- View new 0.7.0 POS orders.
- Confirm subtotal, shipping, taxes and total are readable.
- Confirm later product/tax/shipping changes do not alter old order snapshots.

## Compatibility regression

Repeat the Store 0.6.5 baseline tests for:

- catalogue
- categories
- product galleries
- cart updates
- order history
- payment status updates
- order cancellation / reopening
- POS product search and category filters
- printable receipt
- roadmap viewer
- French and English UI

## Explicit alpha limitations

The first 0.7.0 alpha does not yet include:

- coupons/promotions
- external carrier APIs
- PayPal or Stripe
- automated jurisdiction/legal tax determination
- marketplace vendor-specific taxes or shipping
- invoices or credit notes
- refunds/returns allocation

These are intentionally outside this milestone or scheduled later in the roadmap.
