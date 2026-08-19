<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.1                                                       |
// +---------------------------------------------------------------------------+
// | upgrade071.php                                                           |
// |                                                                           |
// | Database hardening migration from Store 0.7.0 to 0.7.1.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

function store_upgrade071_column_type($table, $column, $definition)
{
    $columnDb = DB_escapeString($column);
    $check = DB_query("SHOW COLUMNS FROM {$table} LIKE '$columnDb'", 1);
    if (DB_error() || DB_numRows($check) !== 1) {
        return false;
    }

    DB_query("ALTER TABLE {$table} MODIFY {$definition}", 1);
    return !DB_error();
}

function store_upgrade071_add_index($table, $indexName, $columns)
{
    $indexDb = DB_escapeString($indexName);
    $check = DB_query("SHOW INDEX FROM {$table} WHERE Key_name='$indexDb'", 1);
    if (DB_error()) {
        return false;
    }
    if (DB_numRows($check) > 0) {
        return true;
    }

    DB_query("ALTER TABLE {$table} ADD KEY {$indexName} ({$columns})", 1);
    return !DB_error();
}

/**
 * Upgrade an installed Store 0.7.0 database to 0.7.1.
 *
 * 0.7.1 does not invent or recalculate historical commercial data. It only
 * normalizes monetary precision and adds lookup indexes required by the 0.7
 * tax/shipping engine and administration screens.
 *
 * @return bool
 */
function store_upgrade_to_071()
{
    global $_TABLES;

    $precisionColumns = array(
        array('store_products', 'price', "price decimal(12,4) NOT NULL default '0.0000'"),
        array('store_orders', 'total', "total decimal(12,4) NOT NULL default '0.0000'"),
        array('store_order_items', 'unit_price', "unit_price decimal(12,4) NOT NULL default '0.0000'"),
        array('store_order_items', 'line_total', "line_total decimal(12,4) NOT NULL default '0.0000'"),
        array('store_payments', 'amount', "amount decimal(12,4) NOT NULL default '0.0000'")
    );

    foreach ($precisionColumns as $column) {
        if (!isset($_TABLES[$column[0]])
            || !store_upgrade071_column_type($_TABLES[$column[0]], $column[1], $column[2])) {
            COM_errorLog('Store Plugin 0.7.1: failed to normalize ' . $column[0] . '.' . $column[1] . '.');
            return false;
        }
    }

    $indexes = array(
        array('store_products', 'store_tax_class', 'tax_class_id'),
        array('store_order_items', 'store_item_tax_class', 'tax_class_id'),
        array('store_orders', 'store_order_country', 'country_code'),
        array('store_shipping_methods', 'store_shipping_method_tax_class', 'tax_class_id')
    );

    foreach ($indexes as $index) {
        if (!isset($_TABLES[$index[0]])
            || !store_upgrade071_add_index($_TABLES[$index[0]], $index[1], $index[2])) {
            COM_errorLog('Store Plugin 0.7.1: failed to add index ' . $index[1] . '.');
            return false;
        }
    }

    return true;
}
