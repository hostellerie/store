# Store 0.7.0 alpha test snapshot

This snapshot is the first installable test build of the **International Taxes, Shipping & Order Totals** milestone.

## What to test first

1. Upgrade an existing Store 0.6.5 installation through Geeklog's normal plugin update mechanism.
2. Confirm existing products, orders and payments are preserved.
3. Open **Store → Taxes & Shipping**.
4. Create a tax zone and add an ISO two-letter country location.
5. Add a tax rate to a product tax class.
6. Create a shipping zone for the same destination.
7. Add at least one shipping method.
8. Assign a tax class and weight to a physical product.
9. Run an online checkout and use **Update shipping and taxes** after entering the destination.
10. Confirm subtotal, shipping, tax and grand total before placing the order.
11. Confirm the same snapshots appear in the public order page and Store administration.
12. Run a POS sale and verify its tax snapshot and receipt.
13. Cancel a physical-goods order and verify stock restoration.

## Important configuration note

Store installs neutral tax classes, but **no national tax percentage**. Tax and shipping rules must be configured by the Store administrator.

`tax_enabled` and `shipping_enabled` are disabled by default after migration/fresh installation until the merchant configures the required rules.

## Calculation behavior in this alpha

- tax-exclusive prices are supported;
- tax-inclusive prices are supported;
- multiple rates are supported in priority order;
- compound rates are supported;
- shipping can itself use a configured tax class;
- digital-only carts do not require shipping;
- mixed physical/digital carts require shipping only for their physical products;
- the POS uses the configured default country code as its tax context and applies no shipping;
- monetary tax rounding is currently performed at line/component level.

The configuration model reserves subtotal tax rounding, but subtotal-rounding behavior is **not implemented in this alpha**.

## Shipping methods available

- Flat rate
- Free shipping
- Free above threshold
- Weight based
- Local pickup

Carrier APIs are intentionally outside this milestone.

## Historical orders

The 0.6.x → 0.7.0 migration does not invent tax or shipping values for historical orders. Existing final totals remain authoritative historical values.

## Automated checks

The development branch runs:

- PHP 5.6 syntax validation;
- PHP 8.3 syntax validation;
- no-tab indentation validation;
- tax-exclusive calculator regression tests;
- tax-inclusive calculator regression tests;
- compound-tax regression tests;
- installable ZIP construction after successful checks.

See `docs/TESTING-0.7.0-alpha.md` for the broader regression plan.
