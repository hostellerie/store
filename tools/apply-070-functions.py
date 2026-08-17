from pathlib import Path

# One-time migration helper for the 0.7.0 development branch.
path = Path('functions.inc')
text = path.read_text(encoding='utf-8')

replacements = []
replacements.append((
    '// | Store Plugin 0.6.5                                                       |',
    '// | Store Plugin 0.7.0                                                       |'
))

old_tables = """if (!isset($_TABLES['store_payments'])) {
    $_TABLES['store_payments'] = $_DB_table_prefix . 'store_payments';
}
"""
new_tables = old_tables + """if (!isset($_TABLES['store_tax_classes'])) {
    $_TABLES['store_tax_classes'] = $_DB_table_prefix . 'store_tax_classes';
}
if (!isset($_TABLES['store_tax_zones'])) {
    $_TABLES['store_tax_zones'] = $_DB_table_prefix . 'store_tax_zones';
}
if (!isset($_TABLES['store_tax_zone_locations'])) {
    $_TABLES['store_tax_zone_locations'] = $_DB_table_prefix . 'store_tax_zone_locations';
}
if (!isset($_TABLES['store_tax_rates'])) {
    $_TABLES['store_tax_rates'] = $_DB_table_prefix . 'store_tax_rates';
}
if (!isset($_TABLES['store_shipping_zones'])) {
    $_TABLES['store_shipping_zones'] = $_DB_table_prefix . 'store_shipping_zones';
}
if (!isset($_TABLES['store_shipping_zone_locations'])) {
    $_TABLES['store_shipping_zone_locations'] = $_DB_table_prefix . 'store_shipping_zone_locations';
}
if (!isset($_TABLES['store_shipping_methods'])) {
    $_TABLES['store_shipping_methods'] = $_DB_table_prefix . 'store_shipping_methods';
}
"""
replacements.append((old_tables, new_tables))

old_config = """$store_config = config::get_instance();
$_STORE_CONF = $store_config->get_config('store');

function store_escape($value)
"""
new_config = """$store_config = config::get_instance();
$_STORE_CONF = $store_config->get_config('store');

// Store 0.7.0 centralizes monetary, tax and shipping calculations here.
require_once $store_plugin_path . 'includes/commerce.php';

function store_escape($value)
"""
replacements.append((old_config, new_config))

replacements.append(("return '0.6.5';", "return '0.7.0';"))

old_upgrade = """    // 0.6.5 is a stabilization/documentation release: no schema migration.
    DB_query(\"UPDATE {$_TABLES['plugins']} SET pi_version = '0.6.5', pi_gl_version = '2.1.1' WHERE pi_name = 'store'\", 1);
    if (DB_error()) {
        COM_errorLog('Store Plugin: failed to update plugin metadata to 0.6.5.');
        return false;
    }
"""
new_upgrade = """    // 0.6.5 is a stabilization/documentation release: no schema migration.

    if (version_compare($installed, '0.7.0', '<')) {
        require_once $_CONF['path'] . 'plugins/store/includes/upgrade070.php';
        if (!store_upgrade_to_070()) {
            COM_errorLog('Store Plugin: failed to migrate database to 0.7.0.');
            return false;
        }
    }

    DB_query(\"UPDATE {$_TABLES['plugins']} SET pi_version = '0.7.0', pi_gl_version = '2.1.1' WHERE pi_name = 'store'\", 1);
    if (DB_error()) {
        COM_errorLog('Store Plugin: failed to update plugin metadata to 0.7.0.');
        return false;
    }
"""
replacements.append((old_upgrade, new_upgrade))

old_uninstall = """        'tables' => array('store_payments', 'store_order_items', 'store_orders', 'store_product_images', 'store_products', 'store_categories'),
"""
new_uninstall = """        'tables' => array(
            'store_shipping_methods',
            'store_shipping_zone_locations',
            'store_shipping_zones',
            'store_tax_rates',
            'store_tax_zone_locations',
            'store_tax_zones',
            'store_tax_classes',
            'store_payments',
            'store_order_items',
            'store_orders',
            'store_product_images',
            'store_products',
            'store_categories'
        ),
"""
replacements.append((old_uninstall, new_uninstall))

for old, new in replacements:
    if old not in text:
        raise SystemExit('Expected patch target not found:\n' + old[:160])
    if old == "return '0.6.5';":
        text = text.replace(old, new, 2)
    else:
        text = text.replace(old, new, 1)

path.write_text(text, encoding='utf-8')
