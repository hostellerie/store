<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                           |
// | Administration interface for products, orders, POS and commerce setup.   |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                           |
// | This program is free software; you can redistribute it and/or             |
// | modify it under the terms of the GNU General Public License               |
// | as published by the Free Software Foundation; either version 2            |
// | of the License, or (at your option) any later version.                    |
// |                                                                           |
// | This program is distributed in the hope that it will be useful,           |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
// | GNU General Public License for more details.                              |
// |                                                                           |
// | You should have received a copy of the GNU General Public License         |
// | along with this program; if not, write to the Free Software              |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA                |
// | 02111-1307, USA.                                                         |
// |                                                                           |
// +---------------------------------------------------------------------------+

require_once dirname(__FILE__) . '/../../../lib-common.php';
require_once dirname(__FILE__) . '/../../auth.inc.php';

global $_CONF, $_TABLES, $_STORE_CONF, $LANG_STORE, $MESSAGE;

if (!SEC_hasRights('store.admin')) {
    COM_accessLog('User tried to access Store administration without permission.');
    COM_output(COM_createHTMLDocument(
        COM_showMessageText($MESSAGE[29], $MESSAGE[30]),
        array('pagetitle' => $MESSAGE[30])
    ));
    exit;
}

require_once $_CONF['path'] . 'plugins/store/includes/commerce.php';
require_once $_CONF['path'] . 'plugins/store/includes/pos070.php';

// Alpha migration bootstrap. This keeps an existing 0.6.x test installation
// usable while the standard plugin_upgrade_store() wiring is finalized.
$installedStoreVersion = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name='store'");
if ($installedStoreVersion !== '' && version_compare($installedStoreVersion, '0.7.0', '<')) {
    require_once $_CONF['path'] . 'plugins/store/includes/upgrade070.php';
    if (!store_upgrade_to_070()) {
        COM_errorLog('Store Plugin 0.7.0: automatic alpha migration failed.');
        COM_output(COM_createHTMLDocument(
            COM_showMessageText('Store 0.7.0 database migration failed. Check error.log before continuing.', 'Store 0.7.0'),
            array('pagetitle' => 'Store 0.7.0')
        ));
        exit;
    }
    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version='0.7.0',pi_gl_version='2.1.1' WHERE pi_name='store'", 1);
}

$commerceLanguage = isset($_CONF['language']) ? $_CONF['language'] : 'english';
$commerceLanguageFile = $_CONF['path'] . 'plugins/store/language/commerce/' . $commerceLanguage . '.php';
if (!is_file($commerceLanguageFile) && strpos($commerceLanguage, 'french') === 0) {
    $commerceLanguageFile = $_CONF['path'] . 'plugins/store/language/commerce/french.php';
}
if (!is_file($commerceLanguageFile)) {
    $commerceLanguageFile = $_CONF['path'] . 'plugins/store/language/commerce/english.php';
}
require_once $commerceLanguageFile;

require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/editor.php';
require_once __DIR__ . '/includes/routes-commerce.php';
require_once __DIR__ . '/includes/routes-product-commerce.php';
require_once __DIR__ . '/includes/routes-core.php';
require_once __DIR__ . '/includes/routes-pos070.php';
require_once __DIR__ . '/includes/routes-orders070.php';
require_once __DIR__ . '/includes/routes-home070.php';
require_once __DIR__ . '/includes/routes-pos.php';
require_once __DIR__ . '/includes/routes-orders.php';
