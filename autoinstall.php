<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | autoinstall.php                                                          |
// |                                                                          |
// | Geeklog automatic installation metadata for the Store plugin.            |
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

function plugin_autoinstall_store($pi_name)
{
    $pi_name = 'store';
    $pi_display_name = 'Store';
    $pi_admin = $pi_display_name . ' Admin';

    return array(
        'info' => array(
            'pi_name'         => $pi_name,
            'pi_display_name' => $pi_display_name,
            'pi_version'      => '0.6.5',
            'pi_gl_version'   => '2.1.1',
            'pi_homepage'     => 'https://github.com/Geeklog-Plugins/store'
        ),
        'groups' => array(
            $pi_admin => 'Users in this group can administer the Store plugin'
        ),
        'features' => array(
            'store.admin' => 'Full access to Store administration',
            'config.store.tab_main' => 'Access to Store configuration'
        ),
        'mappings' => array(
            'store.admin' => array($pi_admin),
            'config.store.tab_main' => array($pi_admin)
        ),
        'tables' => array(
            'store_products',
            'store_categories',
            'store_product_images',
            'store_orders',
            'store_order_items',
            'store_payments'
        )
    );
}

function plugin_load_configuration_store($pi_name)
{
    global $_CONF;

    $base_path = $_CONF['path'] . 'plugins/' . $pi_name . '/';
    require_once $_CONF['path_system'] . 'classes/config.class.php';
    require_once $base_path . 'install_defaults.php';

    return plugin_initconfig_store();
}

function plugin_compatible_with_this_version_store($pi_name)
{
    global $_CONF, $_DB_dbms;

    $db_file = $_CONF['path'] . 'plugins/' . $pi_name . '/sql/' . $_DB_dbms . '_install.php';
    if (!file_exists($db_file)) {
        return false;
    }

    if (!function_exists('SEC_createToken') || !function_exists('SEC_checkToken')) {
        return false;
    }

    if (!function_exists('COM_createHTMLDocument')) {
        return false;
    }

    return true;
}
