<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | install_defaults.php                                                     |
// |                                                                          |
// | Default Store configuration and Configuration API setup.                 |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software              |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA                |
// | 02111-1307, USA.                                                         |
// |                                                                          |
// +---------------------------------------------------------------------------+

if (isset($_SERVER['PHP_SELF']) && stripos($_SERVER['PHP_SELF'], basename(__FILE__)) !== false) {
    die('This file can not be used on its own!');
}

global $_STORE_DEFAULT;
$_STORE_DEFAULT = array();
$_STORE_DEFAULT['hide_menu'] = 0;
$_STORE_DEFAULT['currency'] = 'EUR';
$_STORE_DEFAULT['products_per_page'] = 12;
$_STORE_DEFAULT['show_stock'] = 1;
$_STORE_DEFAULT['manual_payment_enabled'] = 1;
$_STORE_DEFAULT['manual_payment_label'] = 'Bank transfer / manual payment';
$_STORE_DEFAULT['manual_payment_instructions'] = 'Please contact us or use the payment instructions provided by the store administrator.';

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
        $c->add('products_per_page', $_STORE_DEFAULT['products_per_page'], 'text', 0, 0, 0, 30, true, 'store', 0);
        $c->add('show_stock', $_STORE_DEFAULT['show_stock'], 'select', 0, 0, 0, 40, true, 'store', 0);
        $c->add('manual_payment_enabled', $_STORE_DEFAULT['manual_payment_enabled'], 'select', 0, 0, 0, 50, true, 'store', 0);
        $c->add('manual_payment_label', $_STORE_DEFAULT['manual_payment_label'], 'text', 0, 0, 0, 60, true, 'store', 0);
        $c->add('manual_payment_instructions', $_STORE_DEFAULT['manual_payment_instructions'], 'text', 0, 0, 0, 70, true, 'store', 0);
    }

    return true;
}
