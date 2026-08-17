<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                           |
// | Default Store configuration and Configuration API setup.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// +---------------------------------------------------------------------------+

if (isset($_SERVER['PHP_SELF']) && stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own!');
}

global $_STORE_DEFAULT;
$_STORE_DEFAULT = array();
$_STORE_DEFAULT['hide_menu'] = 0;
$_STORE_DEFAULT['currency'] = 'EUR';
$_STORE_DEFAULT['currency_decimals'] = 2;
$_STORE_DEFAULT['products_per_page'] = 12;
$_STORE_DEFAULT['show_stock'] = 1;
$_STORE_DEFAULT['manual_payment_enabled'] = 1;
$_STORE_DEFAULT['manual_payment_label'] = 'Bank transfer / manual payment';
$_STORE_DEFAULT['manual_payment_instructions'] = 'Please contact us or use the payment instructions provided by the store administrator.';

// International commerce defaults. These settings are deliberately neutral:
// no country, tax percentage, shipping carrier or jurisdiction is assumed.
$_STORE_DEFAULT['tax_enabled'] = 0;
$_STORE_DEFAULT['prices_include_tax'] = 0;
$_STORE_DEFAULT['tax_basis'] = 'shipping';
$_STORE_DEFAULT['tax_rounding'] = 'line';
$_STORE_DEFAULT['shipping_enabled'] = 0;
$_STORE_DEFAULT['default_country_code'] = '';
$_STORE_DEFAULT['weight_unit'] = 'kg';
$_STORE_DEFAULT['dimension_unit'] = 'cm';

function plugin_initconfig_store()
{
    global $_STORE_DEFAULT;

    $c = config::get_instance();
    if (!$c->group_exists('store')) {
        $c->add('sg_main', null, 'subgroup', 0, 0, null, 0, true, 'store', 0);
        $c->add('tab_main', null, 'tab', 0, 0, null, 0, true, 'store', 0);
        $c->add('fs_main', null, 'fieldset', 0, 0, null, 0, true, 'store', 0);
        $c->add('hide_menu', $_STORE_DEFAULT['hide_menu'], 'select', 0, 0, 0, 10, true, 'store', 0);
        $c->add('currency', $_STORE_DEFAULT['currency'], 'text', 0, 0, 0, 20, true, 'store', 0);
        $c->add('currency_decimals', $_STORE_DEFAULT['currency_decimals'], 'text', 0, 0, 0, 25, true, 'store', 0);
        $c->add('products_per_page', $_STORE_DEFAULT['products_per_page'], 'text', 0, 0, 0, 30, true, 'store', 0);
        $c->add('show_stock', $_STORE_DEFAULT['show_stock'], 'select', 0, 0, 0, 40, true, 'store', 0);
        $c->add('manual_payment_enabled', $_STORE_DEFAULT['manual_payment_enabled'], 'select', 0, 0, 0, 50, true, 'store', 0);
        $c->add('manual_payment_label', $_STORE_DEFAULT['manual_payment_label'], 'text', 0, 0, 0, 60, true, 'store', 0);
        $c->add('manual_payment_instructions', $_STORE_DEFAULT['manual_payment_instructions'], 'text', 0, 0, 0, 70, true, 'store', 0);

        $c->add('fs_commerce', null, 'fieldset', 0, 0, null, 100, true, 'store', 0);
        $c->add('tax_enabled', $_STORE_DEFAULT['tax_enabled'], 'select', 0, 0, 0, 110, true, 'store', 0);
        $c->add('prices_include_tax', $_STORE_DEFAULT['prices_include_tax'], 'select', 0, 0, 0, 120, true, 'store', 0);
        $c->add('tax_basis', $_STORE_DEFAULT['tax_basis'], 'text', 0, 0, 0, 130, true, 'store', 0);
        $c->add('tax_rounding', $_STORE_DEFAULT['tax_rounding'], 'text', 0, 0, 0, 140, true, 'store', 0);
        $c->add('shipping_enabled', $_STORE_DEFAULT['shipping_enabled'], 'select', 0, 0, 0, 150, true, 'store', 0);
        $c->add('default_country_code', $_STORE_DEFAULT['default_country_code'], 'text', 0, 0, 0, 160, true, 'store', 0);
        $c->add('weight_unit', $_STORE_DEFAULT['weight_unit'], 'text', 0, 0, 0, 170, true, 'store', 0);
        $c->add('dimension_unit', $_STORE_DEFAULT['dimension_unit'], 'text', 0, 0, 0, 180, true, 'store', 0);
    }

    return true;
}
