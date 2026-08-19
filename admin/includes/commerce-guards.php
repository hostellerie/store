<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | commerce-guards.php                                                      |
// |                                                                           |
// | Reference and input guards for tax and shipping administration.          |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

function store_commerce_guard_redirect($message)
{
    global $_CONF;

    header('Location: ' . $_CONF['site_admin_url'] . '/plugins/store/index.php?action=commerce&notice=' . rawurlencode($message));
    exit;
}

function store_commerce_guard_exists($tableKey, $id, $activeOnly)
{
    global $_TABLES;

    $id = (int) $id;
    if ($id < 1 || !isset($_TABLES[$tableKey])) {
        return false;
    }

    $where = 'id = ' . $id;
    if ($activeOnly) {
        $where .= ' AND active = 1';
    }

    return (int) DB_getItem($_TABLES[$tableKey], 'id', $where) === $id;
}

function store_commerce_guard_count($tableKey, $where)
{
    global $_TABLES;

    if (!isset($_TABLES[$tableKey])) {
        return 0;
    }

    $result = DB_query("SELECT COUNT(*) AS total FROM {$_TABLES[$tableKey]} WHERE $where", 1);
    if (DB_error() || DB_numRows($result) !== 1) {
        return 0;
    }
    $row = DB_fetchArray($result);
    return isset($row['total']) ? (int) $row['total'] : 0;
}

function store_commerce_guard_duplicate($tableKey, $field, $value, $extraWhere)
{
    global $_TABLES;

    if (!isset($_TABLES[$tableKey])) {
        return false;
    }

    $valueDb = DB_escapeString($value);
    $where = "$field='$valueDb'";
    if ($extraWhere !== '') {
        $where .= ' AND ' . $extraWhere;
    }

    return DB_getItem($_TABLES[$tableKey], 'id', $where) !== '';
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    return;
}

$guardAction = isset($_POST['action']) ? (string) $_POST['action'] : '';

if ($guardAction === 'commerce_save_tax_class') {
    $code = strtolower(trim(isset($_POST['code']) ? (string) $_POST['code'] : ''));
    $code = trim(preg_replace('/[^a-z0-9_-]+/', '-', $code), '-');
    if ($code !== '' && store_commerce_guard_duplicate('store_tax_classes', 'code', $code, '')) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['duplicate_tax_class']);
    }
}

if ($guardAction === 'commerce_save_tax_location') {
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    if (!store_commerce_guard_exists('store_tax_zones', $zoneId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_tax_zone']);
    }
}

if ($guardAction === 'commerce_save_tax_rate') {
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $classId = isset($_POST['tax_class_id']) ? (int) $_POST['tax_class_id'] : 0;
    $rate = isset($_POST['rate']) ? (float) $_POST['rate'] : -1;
    $priority = isset($_POST['priority']) ? (int) $_POST['priority'] : 100;

    if (!store_commerce_guard_exists('store_tax_zones', $zoneId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_tax_zone']);
    }
    if (!store_commerce_guard_exists('store_tax_classes', $classId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_tax_class']);
    }
    if ($rate < 0 || $rate > 1000) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_tax_rate']);
    }
    if ($priority < 0 || $priority > 65535) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_priority']);
    }
}

if ($guardAction === 'commerce_save_shipping_location') {
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    if (!store_commerce_guard_exists('store_shipping_zones', $zoneId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_shipping_zone']);
    }
}

if ($guardAction === 'commerce_save_shipping_method') {
    $zoneId = isset($_POST['zone_id']) ? (int) $_POST['zone_id'] : 0;
    $taxClassId = isset($_POST['tax_class_id']) ? (int) $_POST['tax_class_id'] : 0;
    $code = strtolower(trim(isset($_POST['code']) ? (string) $_POST['code'] : ''));
    $code = trim(preg_replace('/[^a-z0-9_-]+/', '-', $code), '-');
    $minimumWeight = max(0, isset($_POST['minimum_weight']) ? (float) $_POST['minimum_weight'] : 0);
    $maximumWeight = max(0, isset($_POST['maximum_weight']) ? (float) $_POST['maximum_weight'] : 0);

    if (!store_commerce_guard_exists('store_shipping_zones', $zoneId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_shipping_zone']);
    }
    if ($taxClassId > 0 && !store_commerce_guard_exists('store_tax_classes', $taxClassId, true)) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_tax_class']);
    }
    if ($maximumWeight > 0 && $maximumWeight < $minimumWeight) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['invalid_weight_range']);
    }
    if ($code !== '' && store_commerce_guard_duplicate(
        'store_shipping_methods',
        'code',
        $code,
        'zone_id=' . $zoneId
    )) {
        store_commerce_guard_redirect($LANG_STORE_COMMERCE['duplicate_shipping_method']);
    }
}

if ($guardAction === 'commerce_delete') {
    $type = isset($_POST['type']) ? (string) $_POST['type'] : '';
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

    if ($id < 1) {
        return;
    }

    if ($type === 'tax_class') {
        $usedByProducts = store_commerce_guard_count('store_products', 'tax_class_id=' . $id);
        $usedByRates = store_commerce_guard_count('store_tax_rates', 'tax_class_id=' . $id);
        $usedByShipping = store_commerce_guard_count('store_shipping_methods', 'tax_class_id=' . $id);
        if ($usedByProducts + $usedByRates + $usedByShipping > 0) {
            store_commerce_guard_redirect($LANG_STORE_COMMERCE['delete_tax_class_in_use']);
        }
    }

    if ($type === 'tax_zone') {
        $locations = store_commerce_guard_count('store_tax_zone_locations', 'zone_id=' . $id);
        $rates = store_commerce_guard_count('store_tax_rates', 'zone_id=' . $id);
        if ($locations + $rates > 0) {
            store_commerce_guard_redirect($LANG_STORE_COMMERCE['delete_tax_zone_in_use']);
        }
    }

    if ($type === 'shipping_zone') {
        $locations = store_commerce_guard_count('store_shipping_zone_locations', 'zone_id=' . $id);
        $methods = store_commerce_guard_count('store_shipping_methods', 'zone_id=' . $id);
        if ($locations + $methods > 0) {
            store_commerce_guard_redirect($LANG_STORE_COMMERCE['delete_shipping_zone_in_use']);
        }
    }
}
