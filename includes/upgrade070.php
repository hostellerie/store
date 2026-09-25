<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | upgrade070.php                                                           |
// |                                                                           |
// | Database and Configuration API migration from the 0.6.x baseline.        |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+

if (!function_exists('store_upgrade_add_column')) {
    function store_upgrade_add_column($table, $column, $definition)
    {
        $columnDb = DB_escapeString($column);
        $check = DB_query("SHOW COLUMNS FROM {$table} LIKE '$columnDb'", 1);
        if (DB_error()) {
            return false;
        }
        if (DB_numRows($check) < 1) {
            DB_query("ALTER TABLE {$table} ADD {$definition}", 1);
            if (DB_error()) {
                return false;
            }
        }
        return true;
    }
}

/**
 * Upgrade Store from the 0.6.x commercial baseline to 0.7.0.
 *
 * Historical totals are intentionally preserved. New snapshot columns are
 * initialized without attempting to reconstruct taxes or shipping that were
 * not recorded by older Store versions.
 *
 * @return bool
 */
function store_upgrade_to_070()
{
    global $_CONF, $_TABLES;

    $tableKeys = array(
        'store_tax_classes',
        'store_tax_zones',
        'store_tax_zone_locations',
        'store_tax_rates',
        'store_shipping_zones',
        'store_shipping_zone_locations',
        'store_shipping_methods'
    );
    foreach ($tableKeys as $key) {
        if (!isset($_TABLES[$key])) {
            COM_errorLog('Store Plugin 0.7.0: missing table registration for ' . $key . '.');
            return false;
        }
    }

    $queries = array(
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_tax_classes']} ("
            . "id int(10) unsigned NOT NULL auto_increment, code varchar(64) NOT NULL default '', "
            . "name varchar(190) NOT NULL default '', active tinyint(1) NOT NULL default '1', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), "
            . "UNIQUE KEY store_tax_class_code (code), KEY store_tax_class_active (active)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_tax_zones']} ("
            . "id int(10) unsigned NOT NULL auto_increment, name varchar(190) NOT NULL default '', "
            . "active tinyint(1) NOT NULL default '1', priority int(11) NOT NULL default '100', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), "
            . "KEY store_tax_zone_active (active), KEY store_tax_zone_priority (priority)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_tax_zone_locations']} ("
            . "id int(10) unsigned NOT NULL auto_increment, zone_id int(10) unsigned NOT NULL, "
            . "country_code char(2) NOT NULL default '', region_code varchar(64) NOT NULL default '', "
            . "postal_pattern varchar(190) NOT NULL default '', PRIMARY KEY (id), "
            . "KEY store_tax_location_zone (zone_id), KEY store_tax_location_country (country_code), "
            . "KEY store_tax_location_region (country_code,region_code)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_tax_rates']} ("
            . "id int(10) unsigned NOT NULL auto_increment, zone_id int(10) unsigned NOT NULL, "
            . "tax_class_id int(10) unsigned NOT NULL, name varchar(190) NOT NULL default '', "
            . "rate decimal(9,4) NOT NULL default '0.0000', priority int(11) NOT NULL default '100', "
            . "compound tinyint(1) NOT NULL default '0', active tinyint(1) NOT NULL default '1', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), "
            . "KEY store_tax_rate_zone (zone_id), KEY store_tax_rate_class (tax_class_id), "
            . "KEY store_tax_rate_lookup (zone_id,tax_class_id,active)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_shipping_zones']} ("
            . "id int(10) unsigned NOT NULL auto_increment, name varchar(190) NOT NULL default '', "
            . "active tinyint(1) NOT NULL default '1', priority int(11) NOT NULL default '100', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), "
            . "KEY store_shipping_zone_active (active), KEY store_shipping_zone_priority (priority)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_shipping_zone_locations']} ("
            . "id int(10) unsigned NOT NULL auto_increment, zone_id int(10) unsigned NOT NULL, "
            . "country_code char(2) NOT NULL default '', region_code varchar(64) NOT NULL default '', "
            . "postal_pattern varchar(190) NOT NULL default '', PRIMARY KEY (id), "
            . "KEY store_shipping_location_zone (zone_id), KEY store_shipping_location_country (country_code), "
            . "KEY store_shipping_location_region (country_code,region_code)) ENGINE=MyISAM",
        "CREATE TABLE IF NOT EXISTS {$_TABLES['store_shipping_methods']} ("
            . "id int(10) unsigned NOT NULL auto_increment, zone_id int(10) unsigned NOT NULL, "
            . "code varchar(64) NOT NULL default '', name varchar(190) NOT NULL default '', "
            . "method_type varchar(32) NOT NULL default 'flat', price decimal(12,4) NOT NULL default '0.0000', "
            . "free_above decimal(12,4) NOT NULL default '0.0000', weight_rate decimal(12,4) NOT NULL default '0.0000', "
            . "minimum_weight decimal(12,4) NOT NULL default '0.0000', maximum_weight decimal(12,4) NOT NULL default '0.0000', "
            . "tax_class_id int(10) unsigned NOT NULL default '0', active tinyint(1) NOT NULL default '1', "
            . "sort_order int(11) NOT NULL default '100', created datetime NULL, modified datetime NULL, "
            . "PRIMARY KEY (id), KEY store_shipping_method_zone (zone_id), KEY store_shipping_method_active (active), "
            . "KEY store_shipping_method_code (code)) ENGINE=MyISAM"
    );

    foreach ($queries as $query) {
        DB_query($query, 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin 0.7.0: failed to create international commerce tables.');
            return false;
        }
    }

    if (DB_count($_TABLES['store_tax_classes']) < 1) {
        DB_query("INSERT INTO {$_TABLES['store_tax_classes']} (code,name,active,created,modified) VALUES "
            . "('standard','Standard',1,NOW(),NOW()),('reduced','Reduced',1,NOW(),NOW()),"
            . "('zero','Zero rate',1,NOW(),NOW()),('exempt','Exempt',1,NOW(),NOW())", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin 0.7.0: failed to create neutral tax classes.');
            return false;
        }
    }

    $productColumns = array(
        'tax_class_id' => "tax_class_id int(10) unsigned NOT NULL default '0' AFTER category_id",
        'weight' => "weight decimal(12,4) NOT NULL default '0.0000' AFTER stock",
        'length' => "length decimal(12,4) NOT NULL default '0.0000' AFTER weight",
        'width' => "width decimal(12,4) NOT NULL default '0.0000' AFTER length",
        'height' => "height decimal(12,4) NOT NULL default '0.0000' AFTER width"
    );
    foreach ($productColumns as $column => $definition) {
        if (!store_upgrade_add_column($_TABLES['store_products'], $column, $definition)) {
            COM_errorLog('Store Plugin 0.7.0: failed to add product column ' . $column . '.');
            return false;
        }
    }

    $orderColumns = array(
        'region' => "region varchar(190) NOT NULL default '' AFTER city",
        'country_code' => "country_code char(2) NOT NULL default '' AFTER country",
        'items_subtotal' => "items_subtotal decimal(12,4) NOT NULL default '0.0000' AFTER currency",
        'discount_total' => "discount_total decimal(12,4) NOT NULL default '0.0000' AFTER items_subtotal",
        'shipping_method' => "shipping_method varchar(64) NOT NULL default '' AFTER discount_total",
        'shipping_label' => "shipping_label varchar(190) NOT NULL default '' AFTER shipping_method",
        'shipping_subtotal' => "shipping_subtotal decimal(12,4) NOT NULL default '0.0000' AFTER shipping_label",
        'shipping_tax' => "shipping_tax decimal(12,4) NOT NULL default '0.0000' AFTER shipping_subtotal",
        'tax_total' => "tax_total decimal(12,4) NOT NULL default '0.0000' AFTER shipping_tax",
        'prices_include_tax' => "prices_include_tax tinyint(1) NOT NULL default '0' AFTER total"
    );
    foreach ($orderColumns as $column => $definition) {
        if (!store_upgrade_add_column($_TABLES['store_orders'], $column, $definition)) {
            COM_errorLog('Store Plugin 0.7.0: failed to add order column ' . $column . '.');
            return false;
        }
    }

    $itemColumns = array(
        'tax_class_id' => "tax_class_id int(10) unsigned NOT NULL default '0' AFTER product_id",
        'line_subtotal' => "line_subtotal decimal(12,4) NOT NULL default '0.0000' AFTER quantity",
        'tax_label' => "tax_label varchar(190) NOT NULL default '' AFTER line_subtotal",
        'tax_rate' => "tax_rate decimal(9,4) NOT NULL default '0.0000' AFTER tax_label",
        'unit_tax' => "unit_tax decimal(12,4) NOT NULL default '0.0000' AFTER tax_rate",
        'tax_total' => "tax_total decimal(12,4) NOT NULL default '0.0000' AFTER unit_tax"
    );
    foreach ($itemColumns as $column => $definition) {
        if (!store_upgrade_add_column($_TABLES['store_order_items'], $column, $definition)) {
            COM_errorLog('Store Plugin 0.7.0: failed to add order item column ' . $column . '.');
            return false;
        }
    }

    // Preserve historical 0.6.x totals without inventing taxes or shipping.
    DB_query("UPDATE {$_TABLES['store_orders']} SET items_subtotal=total "
        . "WHERE items_subtotal=0 AND discount_total=0 AND shipping_subtotal=0 AND tax_total=0", 1);
    DB_query("UPDATE {$_TABLES['store_order_items']} SET line_subtotal=line_total "
        . "WHERE line_subtotal=0 AND tax_total=0", 1);

    require_once $_CONF['path_system'] . 'classes/config.class.php';
    $config = config::get_instance();
    $current = $config->get_config('store');
    $settings = array(
        'currency_decimals' => array(2, 'text', 25),
        'tax_enabled' => array(0, 'select', 110),
        'prices_include_tax' => array(0, 'select', 120),
        'tax_basis' => array('shipping', 'text', 130),
        'tax_rounding' => array('line', 'text', 140),
        'shipping_enabled' => array(0, 'select', 150),
        'default_country_code' => array('', 'text', 160),
        'weight_unit' => array('kg', 'text', 170),
        'dimension_unit' => array('cm', 'text', 180)
    );
    foreach ($settings as $name => $setting) {
        if (!isset($current[$name])) {
            $config->add($name, $setting[0], $setting[1], 0, 0, 0, $setting[2], true, 'store', 0);
        }
    }

    return true;
}
