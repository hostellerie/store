<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | config071.php                                                            |
// |                                                                           |
// | Configuration tab/layout migration for the 0.7.0 alpha series.           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Ensure the clearer Store configuration layout exists on upgraded installs.
 *
 * This is intentionally idempotent. Existing values are preserved; only their
 * tab placement is changed and the new public layout option is added.
 *
 * @return bool
 */
function store_config070_ui_ensure()
{
    global $_TABLES, $_STORE_CONF;

    $c = config::get_instance();

    $tabs = array(
        1 => array('tab_catalog', 'fs_catalog'),
        2 => array('tab_checkout', 'fs_checkout'),
        3 => array('tab_commerce', 'fs_commerce'),
        4 => array('tab_display', 'fs_display')
    );

    foreach ($tabs as $tabId => $definition) {
        $tabName = $definition[0];
        $fieldsetName = $definition[1];
        if (!$c->tab_exists('store', $tabName)) {
            $c->add($tabName, null, 'tab', 0, 0, null, $tabId * 10, true, 'store', $tabId);
        }

        $fieldsetDb = DB_escapeString($fieldsetName);
        $existingFieldset = DB_getItem(
            $_TABLES['conf_values'],
            'name',
            "name='$fieldsetDb' AND group_name='store'"
        );
        if ($existingFieldset === '') {
            $c->add($fieldsetName, null, 'fieldset', 0, 0, null, 0, true, 'store', $tabId);
        }
    }

    if (!isset($_STORE_CONF['layout_blocks'])) {
        $c->add('layout_blocks', 'none', 'select', 0, 0, 1, 10, true, 'store', 4);
    }

    $placements = array(
        1 => array('currency', 'currency_decimals', 'products_per_page', 'show_stock', 'weight_unit', 'dimension_unit'),
        2 => array('manual_payment_enabled', 'manual_payment_label', 'manual_payment_instructions', 'default_country_code'),
        3 => array('tax_enabled', 'prices_include_tax', 'tax_basis', 'tax_rounding', 'shipping_enabled'),
        4 => array('layout_blocks')
    );

    foreach ($placements as $tabId => $names) {
        foreach ($names as $name) {
            $nameDb = DB_escapeString($name);
            DB_query("UPDATE {$_TABLES['conf_values']} SET tab=$tabId,fieldset=0 "
                . "WHERE group_name='store' AND name='$nameDb'", 1);
        }
    }

    // Configuration tabs have individual Geeklog permissions. Add the new
    // features for existing installations and grant them to Store Admin.
    $featureNames = array(
        'config.store.tab_catalog' => 'Access to Store catalogue configuration',
        'config.store.tab_checkout' => 'Access to Store checkout configuration',
        'config.store.tab_commerce' => 'Access to Store tax and shipping configuration',
        'config.store.tab_display' => 'Access to Store display configuration'
    );
    $groupId = (int) DB_getItem($_TABLES['groups'], 'grp_id', "grp_name='Store Admin'");

    foreach ($featureNames as $featureName => $description) {
        $featureDb = DB_escapeString($featureName);
        $featureId = (int) DB_getItem($_TABLES['features'], 'ft_id', "ft_name='$featureDb'");
        if ($featureId < 1) {
            $descriptionDb = DB_escapeString($description);
            DB_query("INSERT INTO {$_TABLES['features']} (ft_name,ft_descr,ft_gl_core) "
                . "VALUES ('$featureDb','$descriptionDb',0)", 1);
            if (DB_error()) {
                return false;
            }
            $featureId = (int) DB_insertId();
        }

        if ($groupId > 0) {
            $accessId = DB_getItem(
                $_TABLES['access'],
                'acc_ft_id',
                "acc_ft_id=$featureId AND acc_grp_id=$groupId"
            );
            if ($accessId === '') {
                DB_query("INSERT INTO {$_TABLES['access']} (acc_ft_id,acc_grp_id) VALUES ($featureId,$groupId)", 1);
                if (DB_error()) {
                    return false;
                }
            }
        }
    }

    $_STORE_CONF = $c->get_config('store');
    return true;
}
