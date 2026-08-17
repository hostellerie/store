<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-commerce.php                                                      |
// |                                                                           |
// | Administration routes for international taxes and shipping.              |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if (!isset($action)) {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
}

function store_commerce_redirect($notice)
{
    global $_CONF;

    header('Location: ' . $_CONF['site_admin_url'] . '/plugins/store/index.php?action=commerce&notice=' . rawurlencode($notice));
    exit;
}

function store_commerce_active($value)
{
    return !empty($value) ? 1 : 0;
}

function store_commerce_country_code($value)
{
    return store_country_code($value);
}

if ($action === 'commerce_save_tax_class' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
    $code = strtolower(trim(isset($_POST['code']) ? (string) $_POST['code'] : ''));
    $code = preg_replace('/[^a-z0-9_-]+/', '-', $code);
    $code = trim($code, '-');
    if ($name === '' || $code === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['required_fields']);
    }
    $nameDb = DB_escapeString($name);
    $codeDb = DB_escapeString($code);
    $active = store_commerce_active(isset($_POST['active']));
    DB_query("INSERT INTO {$_TABLES['store_tax_classes']} (name,code,active,created,modified) "
        . "VALUES ('$nameDb','$codeDb',$active,NOW(),NOW())", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_tax_zone' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
    if ($name === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['required_fields']);
    }
    $nameDb = DB_escapeString($name);
    $priority = isset($_POST['priority']) ? (int) $_POST['priority'] : 100;
    $active = store_commerce_active(isset($_POST['active']));
    DB_query("INSERT INTO {$_TABLES['store_tax_zones']} (name,active,priority,created,modified) "
        . "VALUES ('$nameDb',$active,$priority,NOW(),NOW())", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_tax_location' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $country = store_commerce_country_code(isset($_POST['country_code']) ? $_POST['country_code'] : '');
    $region = strtoupper(trim(isset($_POST['region_code']) ? (string) $_POST['region_code'] : ''));
    $postal = trim(isset($_POST['postal_pattern']) ? (string) $_POST['postal_pattern'] : '');
    if ($zoneId < 1 || $country === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['invalid_country_code']);
    }
    $countryDb = DB_escapeString($country);
    $regionDb = DB_escapeString($region);
    $postalDb = DB_escapeString($postal);
    DB_query("INSERT INTO {$_TABLES['store_tax_zone_locations']} (zone_id,country_code,region_code,postal_pattern) "
        . "VALUES ($zoneId,'$countryDb','$regionDb','$postalDb')", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_tax_rate' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $classId = isset($_POST['tax_class_id']) ? (int) $_POST['tax_class_id'] : 0;
    $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
    $rate = isset($_POST['rate']) ? max(0, (float) $_POST['rate']) : 0;
    $priority = isset($_POST['priority']) ? (int) $_POST['priority'] : 100;
    $compound = store_commerce_active(isset($_POST['compound']));
    $active = store_commerce_active(isset($_POST['active']));
    if ($zoneId < 1 || $classId < 1 || $name === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['required_fields']);
    }
    $nameDb = DB_escapeString($name);
    $rateDb = number_format($rate, 4, '.', '');
    DB_query("INSERT INTO {$_TABLES['store_tax_rates']} "
        . "(zone_id,tax_class_id,name,rate,priority,compound,active,created,modified) VALUES "
        . "($zoneId,$classId,'$nameDb','$rateDb',$priority,$compound,$active,NOW(),NOW())", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_shipping_zone' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
    if ($name === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['required_fields']);
    }
    $nameDb = DB_escapeString($name);
    $priority = isset($_POST['priority']) ? (int) $_POST['priority'] : 100;
    $active = store_commerce_active(isset($_POST['active']));
    DB_query("INSERT INTO {$_TABLES['store_shipping_zones']} (name,active,priority,created,modified) "
        . "VALUES ('$nameDb',$active,$priority,NOW(),NOW())", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_shipping_location' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $country = store_commerce_country_code(isset($_POST['country_code']) ? $_POST['country_code'] : '');
    $region = strtoupper(trim(isset($_POST['region_code']) ? (string) $_POST['region_code'] : ''));
    $postal = trim(isset($_POST['postal_pattern']) ? (string) $_POST['postal_pattern'] : '');
    if ($zoneId < 1 || $country === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['invalid_country_code']);
    }
    $countryDb = DB_escapeString($country);
    $regionDb = DB_escapeString($region);
    $postalDb = DB_escapeString($postal);
    DB_query("INSERT INTO {$_TABLES['store_shipping_zone_locations']} (zone_id,country_code,region_code,postal_pattern) "
        . "VALUES ($zoneId,'$countryDb','$regionDb','$postalDb')", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_save_shipping_method' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
    $code = strtolower(trim(isset($_POST['code']) ? (string) $_POST['code'] : ''));
    $code = preg_replace('/[^a-z0-9_-]+/', '-', $code);
    $type = isset($_POST['method_type']) ? (string) $_POST['method_type'] : 'flat';
    $allowed = array('flat', 'free', 'free_above', 'weight', 'pickup');
    if (!in_array($type, $allowed, true)) {
        $type = 'flat';
    }
    if ($zoneId < 1 || $name === '' || $code === '') {
        store_commerce_redirect($LANG_STORE_COMMERCE['required_fields']);
    }
    $nameDb = DB_escapeString($name);
    $codeDb = DB_escapeString($code);
    $price = number_format(max(0, isset($_POST['price']) ? (float) $_POST['price'] : 0), 4, '.', '');
    $freeAbove = number_format(max(0, isset($_POST['free_above']) ? (float) $_POST['free_above'] : 0), 4, '.', '');
    $weightRate = number_format(max(0, isset($_POST['weight_rate']) ? (float) $_POST['weight_rate'] : 0), 4, '.', '');
    $minWeight = number_format(max(0, isset($_POST['minimum_weight']) ? (float) $_POST['minimum_weight'] : 0), 4, '.', '');
    $maxWeight = number_format(max(0, isset($_POST['maximum_weight']) ? (float) $_POST['maximum_weight'] : 0), 4, '.', '');
    $taxClassId = isset($_POST['tax_class_id']) ? (int) $_POST['tax_class_id'] : 0;
    $sortOrder = isset($_POST['sort_order']) ? (int) $_POST['sort_order'] : 100;
    $active = store_commerce_active(isset($_POST['active']));
    DB_query("INSERT INTO {$_TABLES['store_shipping_methods']} "
        . "(zone_id,code,name,method_type,price,free_above,weight_rate,minimum_weight,maximum_weight,tax_class_id,active,sort_order,created,modified) VALUES "
        . "($zoneId,'$codeDb','$nameDb','" . DB_escapeString($type) . "','$price','$freeAbove','$weightRate','$minWeight','$maxWeight',$taxClassId,$active,$sortOrder,NOW(),NOW())", 1);
    store_commerce_redirect($LANG_STORE_COMMERCE['saved']);
}

if ($action === 'commerce_delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_commerce_redirect($LANG_STORE['invalid_token']);
    }
    $type = isset($_POST['type']) ? (string) $_POST['type'] : '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $map = array(
        'tax_class' => 'store_tax_classes',
        'tax_zone' => 'store_tax_zones',
        'tax_location' => 'store_tax_zone_locations',
        'tax_rate' => 'store_tax_rates',
        'shipping_zone' => 'store_shipping_zones',
        'shipping_location' => 'store_shipping_zone_locations',
        'shipping_method' => 'store_shipping_methods'
    );
    if ($id > 0 && isset($map[$type])) {
        DB_query("DELETE FROM {$_TABLES[$map[$type]]} WHERE id=$id", 1);
    }
    store_commerce_redirect($LANG_STORE_COMMERCE['deleted']);
}

if ($action === 'commerce') {
    $token = store_escape(SEC_createToken());
    $content = '<div class="store-admin-home"><div class="store-admin-home-head"><div><h1>'
        . store_escape($LANG_STORE_COMMERCE['commerce_title']) . '</h1><p class="store-meta">'
        . store_escape($LANG_STORE_COMMERCE['commerce_intro']) . '</p></div><div class="store-admin-home-actions">'
        . '<a class="store-secondary-button" href="index.php">← ' . store_escape($LANG_STORE_COMMERCE['back_store']) . '</a></div></div>';
    $content .= '<p class="store-payment-instructions">' . store_escape($LANG_STORE_COMMERCE['neutral_tax_notice']) . '</p>';

    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE_COMMERCE['tax_classes']) . '</h2>';
    $result = DB_query("SELECT * FROM {$_TABLES['store_tax_classes']} ORDER BY name ASC", 1);
    $content .= '<div class="store-admin-table-wrap"><table class="store-admin-table"><thead><tr><th>'
        . store_escape($LANG_STORE_COMMERCE['name']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['tax_class_code'])
        . '</th><th>' . store_escape($LANG_STORE_COMMERCE['active']) . '</th><th></th></tr></thead><tbody>';
    while ($row = DB_fetchArray($result)) {
        $content .= '<tr><td>' . store_escape($row['name']) . '</td><td><code>' . store_escape($row['code'])
            . '</code></td><td>' . ((int) $row['active'] === 1 ? store_escape($LANG_STORE['yes']) : store_escape($LANG_STORE['no']))
            . '</td><td><form method="post"><input type="hidden" name="action" value="commerce_delete"><input type="hidden" name="type" value="tax_class"><input type="hidden" name="id" value="' . (int) $row['id'] . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><button class="store-action-button store-action-delete">' . store_escape($LANG_STORE['delete']) . '</button></form></td></tr>';
    }
    $content .= '</tbody></table></div><form class="store-form" method="post"><input type="hidden" name="action" value="commerce_save_tax_class"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['name']) . '</label><input type="text" name="name" required></div><div><label>' . store_escape($LANG_STORE_COMMERCE['tax_class_code']) . '</label><input type="text" name="code" required></div></div><label class="store-checkbox"><input type="checkbox" name="active" value="1" checked> ' . store_escape($LANG_STORE_COMMERCE['active']) . '</label><button class="store-primary-button">' . store_escape($LANG_STORE_COMMERCE['add_tax_class']) . '</button></form></section>';

    $content .= store_commerce_render_zone_section('tax', $token);
    $content .= store_commerce_render_rate_section($token);
    $content .= '<p class="store-payment-instructions">' . store_escape($LANG_STORE_COMMERCE['neutral_shipping_notice']) . '</p>';
    $content .= store_commerce_render_zone_section('shipping', $token);
    $content .= store_commerce_render_shipping_methods($token);
    $content .= '</div>';
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE_COMMERCE['commerce_title'])));
    exit;
}

function store_commerce_render_zone_section($kind, $token)
{
    global $_TABLES, $LANG_STORE, $LANG_STORE_COMMERCE;

    $isTax = $kind === 'tax';
    $zoneTable = $isTax ? 'store_tax_zones' : 'store_shipping_zones';
    $locTable = $isTax ? 'store_tax_zone_locations' : 'store_shipping_zone_locations';
    $title = $isTax ? $LANG_STORE_COMMERCE['tax_zones'] : $LANG_STORE_COMMERCE['shipping_zones'];
    $saveZone = $isTax ? 'commerce_save_tax_zone' : 'commerce_save_shipping_zone';
    $saveLoc = $isTax ? 'commerce_save_tax_location' : 'commerce_save_shipping_location';
    $deleteType = $isTax ? 'tax_zone' : 'shipping_zone';
    $deleteLocType = $isTax ? 'tax_location' : 'shipping_location';
    $addLabel = $isTax ? $LANG_STORE_COMMERCE['add_tax_zone'] : $LANG_STORE_COMMERCE['add_shipping_zone'];

    $html = '<section class="store-admin-panel"><h2>' . store_escape($title) . '</h2>';
    $zones = DB_query("SELECT * FROM {$_TABLES[$zoneTable]} ORDER BY priority ASC,name ASC", 1);
    while ($zone = DB_fetchArray($zones)) {
        $zoneId = (int) $zone['id'];
        $html .= '<div class="store-order-card"><div class="store-panel-head"><div><strong>' . store_escape($zone['name'])
            . '</strong> <span class="store-meta">#' . $zoneId . ' · ' . store_escape($LANG_STORE_COMMERCE['priority']) . ' ' . (int) $zone['priority'] . '</span></div>'
            . '<form method="post"><input type="hidden" name="action" value="commerce_delete"><input type="hidden" name="type" value="' . $deleteType . '"><input type="hidden" name="id" value="' . $zoneId . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><button class="store-action-button store-action-delete">' . store_escape($LANG_STORE['delete']) . '</button></form></div>';
        $locations = DB_query("SELECT * FROM {$_TABLES[$locTable]} WHERE zone_id=$zoneId ORDER BY country_code,region_code", 1);
        $html .= '<div class="store-category-chips">';
        while ($loc = DB_fetchArray($locations)) {
            $label = $loc['country_code'] . ($loc['region_code'] !== '' ? '-' . $loc['region_code'] : '');
            $html .= '<span class="store-category-chip">' . store_escape($label)
                . '<form method="post" style="display:inline;margin-left:6px"><input type="hidden" name="action" value="commerce_delete"><input type="hidden" name="type" value="' . $deleteLocType . '"><input type="hidden" name="id" value="' . (int) $loc['id'] . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><button type="submit" style="border:0;background:transparent;cursor:pointer">×</button></form></span>';
        }
        $html .= '</div><form class="store-form" method="post"><input type="hidden" name="action" value="' . $saveLoc . '"><input type="hidden" name="zone_id" value="' . $zoneId . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['country_code']) . '</label><input type="text" name="country_code" maxlength="2" required></div><div><label>' . store_escape($LANG_STORE_COMMERCE['region_code']) . '</label><input type="text" name="region_code"></div></div><button class="store-secondary-button">' . store_escape($LANG_STORE_COMMERCE['add_location']) . '</button></form></div>';
    }
    $html .= '<form class="store-form" method="post"><input type="hidden" name="action" value="' . $saveZone . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['name']) . '</label><input type="text" name="name" required></div><div><label>' . store_escape($LANG_STORE_COMMERCE['priority']) . '</label><input type="number" name="priority" value="100"></div></div><label class="store-checkbox"><input type="checkbox" name="active" value="1" checked> ' . store_escape($LANG_STORE_COMMERCE['active']) . '</label><button class="store-primary-button">' . store_escape($addLabel) . '</button></form></section>';
    return $html;
}

function store_commerce_render_rate_section($token)
{
    global $_TABLES, $LANG_STORE, $LANG_STORE_COMMERCE;

    $html = '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE_COMMERCE['tax_rates']) . '</h2><div class="store-admin-table-wrap"><table class="store-admin-table"><thead><tr><th>' . store_escape($LANG_STORE_COMMERCE['tax_zone']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['tax_rate_name']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['tax_rate']) . '</th><th></th></tr></thead><tbody>';
    $rates = DB_query("SELECT r.*,z.name AS zone_name,c.name AS class_name FROM {$_TABLES['store_tax_rates']} r LEFT JOIN {$_TABLES['store_tax_zones']} z ON z.id=r.zone_id LEFT JOIN {$_TABLES['store_tax_classes']} c ON c.id=r.tax_class_id ORDER BY z.name,c.name,r.priority", 1);
    while ($row = DB_fetchArray($rates)) {
        $html .= '<tr><td>' . store_escape($row['zone_name']) . '</td><td>' . store_escape($row['class_name']) . '</td><td>' . store_escape($row['name']) . '</td><td>' . store_escape($row['rate']) . '%</td><td><form method="post"><input type="hidden" name="action" value="commerce_delete"><input type="hidden" name="type" value="tax_rate"><input type="hidden" name="id" value="' . (int) $row['id'] . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><button class="store-action-button store-action-delete">' . store_escape($LANG_STORE['delete']) . '</button></form></td></tr>';
    }
    $html .= '</tbody></table></div><form class="store-form" method="post"><input type="hidden" name="action" value="commerce_save_tax_rate"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><label>' . store_escape($LANG_STORE_COMMERCE['tax_zone']) . '</label>' . store_commerce_zone_select('tax', 'zone_id') . '<label>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</label>' . store_commerce_tax_class_select(0, 'tax_class_id') . '<div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['tax_rate_name']) . '</label><input type="text" name="name" required></div><div><label>' . store_escape($LANG_STORE_COMMERCE['tax_rate']) . '</label><input type="number" name="rate" min="0" step="0.0001" value="0"></div></div><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['priority']) . '</label><input type="number" name="priority" value="100"></div><div><label class="store-checkbox"><input type="checkbox" name="active" value="1" checked> ' . store_escape($LANG_STORE_COMMERCE['active']) . '</label></div></div><button class="store-primary-button">' . store_escape($LANG_STORE_COMMERCE['add_tax_rate']) . '</button></form></section>';
    return $html;
}

function store_commerce_render_shipping_methods($token)
{
    global $_TABLES, $LANG_STORE, $LANG_STORE_COMMERCE;

    $html = '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE_COMMERCE['shipping_methods']) . '</h2><div class="store-admin-table-wrap"><table class="store-admin-table"><thead><tr><th>' . store_escape($LANG_STORE_COMMERCE['shipping_zone']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['name']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['shipping_method_type']) . '</th><th>' . store_escape($LANG_STORE_COMMERCE['shipping_price']) . '</th><th></th></tr></thead><tbody>';
    $methods = DB_query("SELECT m.*,z.name AS zone_name FROM {$_TABLES['store_shipping_methods']} m LEFT JOIN {$_TABLES['store_shipping_zones']} z ON z.id=m.zone_id ORDER BY z.name,m.sort_order,m.name", 1);
    while ($row = DB_fetchArray($methods)) {
        $html .= '<tr><td>' . store_escape($row['zone_name']) . '</td><td>' . store_escape($row['name']) . '</td><td>' . store_escape($row['method_type']) . '</td><td>' . store_escape($row['price']) . '</td><td><form method="post"><input type="hidden" name="action" value="commerce_delete"><input type="hidden" name="type" value="shipping_method"><input type="hidden" name="id" value="' . (int) $row['id'] . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><button class="store-action-button store-action-delete">' . store_escape($LANG_STORE['delete']) . '</button></form></td></tr>';
    }
    $html .= '</tbody></table></div><form class="store-form" method="post"><input type="hidden" name="action" value="commerce_save_shipping_method"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . $token . '"><label>' . store_escape($LANG_STORE_COMMERCE['shipping_zone']) . '</label>' . store_commerce_zone_select('shipping', 'zone_id') . '<div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['name']) . '</label><input type="text" name="name" required></div><div><label>' . store_escape($LANG_STORE_COMMERCE['tax_class_code']) . '</label><input type="text" name="code" required></div></div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_method_type']) . '</label><select name="method_type"><option value="flat">' . store_escape($LANG_STORE_COMMERCE['shipping_flat']) . '</option><option value="free">' . store_escape($LANG_STORE_COMMERCE['shipping_free']) . '</option><option value="free_above">' . store_escape($LANG_STORE_COMMERCE['shipping_free_above']) . '</option><option value="weight">' . store_escape($LANG_STORE_COMMERCE['shipping_weight']) . '</option><option value="pickup">' . store_escape($LANG_STORE_COMMERCE['shipping_pickup']) . '</option></select><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_price']) . '</label><input type="number" name="price" min="0" step="0.0001" value="0"></div><div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_free_threshold']) . '</label><input type="number" name="free_above" min="0" step="0.0001" value="0"></div></div><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_weight_rate']) . '</label><input type="number" name="weight_rate" min="0" step="0.0001" value="0"></div><div><label>' . store_escape($LANG_STORE_COMMERCE['sort_order']) . '</label><input type="number" name="sort_order" value="100"></div></div><div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_min_weight']) . '</label><input type="number" name="minimum_weight" min="0" step="0.0001" value="0"></div><div><label>' . store_escape($LANG_STORE_COMMERCE['shipping_max_weight']) . '</label><input type="number" name="maximum_weight" min="0" step="0.0001" value="0"></div></div><label>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</label>' . store_commerce_tax_class_select(0, 'tax_class_id') . '<label class="store-checkbox"><input type="checkbox" name="active" value="1" checked> ' . store_escape($LANG_STORE_COMMERCE['active']) . '</label><button class="store-primary-button">' . store_escape($LANG_STORE_COMMERCE['add_shipping_method']) . '</button></form></section>';
    return $html;
}

function store_commerce_zone_select($kind, $name)
{
    global $_TABLES;
    $table = $kind === 'tax' ? 'store_tax_zones' : 'store_shipping_zones';
    $html = '<select name="' . store_escape($name) . '" required><option value="0">—</option>';
    $result = DB_query("SELECT id,name FROM {$_TABLES[$table]} WHERE active=1 ORDER BY priority,name", 1);
    while ($row = DB_fetchArray($result)) {
        $html .= '<option value="' . (int) $row['id'] . '">' . store_escape($row['name']) . '</option>';
    }
    return $html . '</select>';
}

function store_commerce_tax_class_select($selected, $name)
{
    global $_TABLES, $LANG_STORE_COMMERCE;
    $html = '<select name="' . store_escape($name) . '"><option value="0">' . store_escape($LANG_STORE_COMMERCE['no_tax_class']) . '</option>';
    $result = DB_query("SELECT id,name FROM {$_TABLES['store_tax_classes']} WHERE active=1 ORDER BY name", 1);
    while ($row = DB_fetchArray($result)) {
        $id = (int) $row['id'];
        $html .= '<option value="' . $id . '"' . ($id === (int) $selected ? ' selected' : '') . '>' . store_escape($row['name']) . '</option>';
    }
    return $html . '</select>';
}
