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
