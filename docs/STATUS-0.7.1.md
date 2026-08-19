# Store 0.7.1 test status

Store 0.7.1 is the hardening update for the international commerce engine introduced in 0.7.0.

## Upgrade target

The release must upgrade an installed Store 0.7.0 through Geeklog's standard plugin upgrade mechanism. Existing products, orders, payments, tax configuration and shipping configuration must be preserved.

## 0.7.1 checks

- Plugin metadata reports 0.7.1.
- `plugin_chkVersion_store()` reports 0.7.1 so Geeklog can detect the available update.
- `plugin_upgrade_store()` runs the dedicated 0.7.0 → 0.7.1 migration.
- Legacy monetary columns are normalized to `DECIMAL(12,4)` without recalculating historical totals.
- Country and tax-class lookup indexes are present on upgraded and fresh installations.
- PHP 5.6 and PHP 8.3 syntax checks remain part of CI.
- The central commerce calculator regression test remains enabled.
- The installable artifact is named `store_0.7.1.zip`.

## Runtime validation

Before merging, test the generated archive on a real Geeklog installation already running Store 0.7.0. Verify the plugin upgrade, product editor, tax/shipping administration, online checkout, order display and POS flow.
