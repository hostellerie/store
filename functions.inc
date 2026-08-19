<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | functions.inc                                                            |
// |                                                                          |
// | Geeklog Plugin API methods and common Store business logic.              |
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

global $_CONF, $_TABLES, $_STORE_CONF, $_DB_table_prefix;

// Register Store database tables for every request. Geeklog's autoinstall
// uses the table key names during installation, but runtime pages still need
// the corresponding $_TABLES entries to be defined by the plugin.
if (!isset($_TABLES['store_products'])) {
    $_TABLES['store_products'] = $_DB_table_prefix . 'store_products';
}
if (!isset($_TABLES['store_categories'])) {
    $_TABLES['store_categories'] = $_DB_table_prefix . 'store_categories';
}
if (!isset($_TABLES['store_product_images'])) {
    $_TABLES['store_product_images'] = $_DB_table_prefix . 'store_product_images';
}
if (!isset($_TABLES['store_orders'])) {
    $_TABLES['store_orders'] = $_DB_table_prefix . 'store_orders';
}
if (!isset($_TABLES['store_order_items'])) {
    $_TABLES['store_order_items'] = $_DB_table_prefix . 'store_order_items';
}
if (!isset($_TABLES['store_payments'])) {
    $_TABLES['store_payments'] = $_DB_table_prefix . 'store_payments';
}
if (!isset($_TABLES['store_tax_classes'])) {
    $_TABLES['store_tax_classes'] = $_DB_table_prefix . 'store_tax_classes';
}
if (!isset($_TABLES['store_tax_zones'])) {
    $_TABLES['store_tax_zones'] = $_DB_table_prefix . 'store_tax_zones';
}
if (!isset($_TABLES['store_tax_zone_locations'])) {
    $_TABLES['store_tax_zone_locations'] = $_DB_table_prefix . 'store_tax_zone_locations';
}
if (!isset($_TABLES['store_tax_rates'])) {
    $_TABLES['store_tax_rates'] = $_DB_table_prefix . 'store_tax_rates';
}
if (!isset($_TABLES['store_shipping_zones'])) {
    $_TABLES['store_shipping_zones'] = $_DB_table_prefix . 'store_shipping_zones';
}
if (!isset($_TABLES['store_shipping_zone_locations'])) {
    $_TABLES['store_shipping_zone_locations'] = $_DB_table_prefix . 'store_shipping_zone_locations';
}
if (!isset($_TABLES['store_shipping_methods'])) {
    $_TABLES['store_shipping_methods'] = $_DB_table_prefix . 'store_shipping_methods';
}

$store_plugin_path = $_CONF['path'] . 'plugins/store/';
$lang = isset($_CONF['language']) ? $_CONF['language'] : 'english';
$langfile = $store_plugin_path . 'language/' . $lang . '.php';
if (file_exists($langfile)) {
    require_once $langfile;
} else {
    require_once $store_plugin_path . 'language/english.php';
}

require_once $_CONF['path_system'] . 'classes/config.class.php';
$store_config = config::get_instance();
$_STORE_CONF = $store_config->get_config('store');

if (!is_array($_STORE_CONF) || !isset($_STORE_CONF['layout_blocks'])) {
    require_once $store_plugin_path . 'includes/config071.php';
    if (!store_config070_ui_ensure()) {
        COM_errorLog('Store Plugin: failed to update configuration tabs/layout settings.');
    }
    $_STORE_CONF = $store_config->get_config('store');
}

// Store 0.7.0 centralizes monetary, tax and shipping calculations here.
require_once $store_plugin_path . 'includes/commerce.php';


/**
 * Build Geeklog document options for public Store pages.
 *
 * layout_blocks describes the columns that remain visible:
 * none = full width, left = left only, right = right only, both = both.
 *
 * @param string $pageTitle
 * @return array
 */
function store_document_options($pageTitle)
{
    global $_STORE_CONF;

    $layout = isset($_STORE_CONF['layout_blocks']) ? (string) $_STORE_CONF['layout_blocks'] : 'none';
    if (!in_array($layout, array('none', 'left', 'right', 'both'), true)) {
        $layout = 'none';
    }

    $options = array('pagetitle' => $pageTitle);
    $options['what'] = ($layout === 'left' || $layout === 'both') ? 'menu' : 'none';
    $options['rightblock'] = ($layout === 'right' || $layout === 'both');
    return $options;
}

/**
 * Format a product measurement without unnecessary trailing zeroes.
 *
 * @param mixed $value
 * @return string
 */
function store_format_measurement($value)
{
    $formatted = number_format((float) $value, 4, '.', '');
    return rtrim(rtrim($formatted, '0'), '.');
}

function store_escape($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function store_slugify($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }

    if (function_exists('iconv')) {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($converted !== false) {
            $value = $converted;
        }
    }

    $value = strtolower($value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value);
    return trim($value, '-');
}


/**
 * Return true when inventory applies to a product.
 *
 * Digital products are not stock-limited in Store 0.1.x. Future versions may
 * add optional download/license limits without reusing the physical stock field.
 *
 * @param array $product Product row
 * @return bool
 */
function store_product_uses_stock($product)
{
    return !isset($product['product_type']) || $product['product_type'] !== 'digital';
}



/**
 * Return whether Geeklog's bundled File Manager is available.
 *
 * Store only depends on the public File Manager entry point. It does not call
 * RichFilemanager connectors directly, which keeps this integration isolated
 * from connector implementation details across supported Geeklog versions.
 *
 * @return bool
 */
function store_filemanager_available()
{
    global $_CONF;

    return !empty($_CONF['path_html'])
        && is_file(rtrim($_CONF['path_html'], '/\\') . '/filemanager/index.php');
}

/**
 * Return the Geeklog File Manager image browser URL.
 *
 * @return string
 */
function store_filemanager_url()
{
    global $_CONF;

    return rtrim($_CONF['site_url'], '/') . '/filemanager/index.php?Type=Image';
}

function store_valid_image_url($value)
{
    $value = trim((string) $value);
    if ($value === '') {
        return '';
    }
    if (substr($value, 0, 1) === '/') {
        return $value;
    }
    $parts = @parse_url($value);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host'])) {
        return '';
    }
    $scheme = strtolower($parts['scheme']);
    return ($scheme === 'http' || $scheme === 'https') ? $value : '';
}


function store_product_url($product)
{
    global $_CONF;

    $slug = is_array($product) && isset($product['slug']) ? $product['slug'] : (string) $product;
    return rtrim($_CONF['site_url'], '/') . '/store/index.php?product=' . rawurlencode($slug);
}

function store_get_product_images($product_id)
{
    global $_TABLES;

    $product_id = (int) $product_id;
    if ($product_id < 1) {
        return array();
    }
    $result = DB_query("SELECT * FROM {$_TABLES['store_product_images']} WHERE product_id = $product_id ORDER BY is_primary DESC, sort_order ASC, id ASC");
    $images = array();
    while ($row = DB_fetchArray($result)) {
        $images[] = $row;
    }
    return $images;
}

function store_set_primary_image($product_id, $image_id)
{
    global $_TABLES;

    $product_id = (int) $product_id;
    $image_id = (int) $image_id;
    if ($product_id < 1 || $image_id < 1) {
        return false;
    }
    $result = DB_query("SELECT image_url FROM {$_TABLES['store_product_images']} WHERE id = $image_id AND product_id = $product_id LIMIT 1");
    if (DB_numRows($result) !== 1) {
        return false;
    }
    $row = DB_fetchArray($result);
    DB_query("UPDATE {$_TABLES['store_product_images']} SET is_primary = 0 WHERE product_id = $product_id");
    DB_query("UPDATE {$_TABLES['store_product_images']} SET is_primary = 1 WHERE id = $image_id AND product_id = $product_id");
    $url = DB_escapeString($row['image_url']);
    DB_query("UPDATE {$_TABLES['store_products']} SET image_url = '$url', modified = NOW() WHERE id = $product_id");
    return !DB_error();
}

function store_add_product_image($product_id, $image_url, $make_primary = false)
{
    global $_TABLES;

    $product_id = (int) $product_id;
    $image_url = store_valid_image_url($image_url);
    if ($product_id < 1 || $image_url === '') {
        return false;
    }
    $url = DB_escapeString($image_url);
    $duplicate = DB_query("SELECT id FROM {$_TABLES['store_product_images']} WHERE product_id = $product_id AND image_url = '$url' LIMIT 1");
    if (DB_numRows($duplicate) > 0) {
        $row = DB_fetchArray($duplicate);
        if ($make_primary) {
            return store_set_primary_image($product_id, (int) $row['id']);
        }
        return true;
    }
    $order = (int) DB_getItem($_TABLES['store_product_images'], 'MAX(sort_order)', "product_id = $product_id") + 10;
    $primary = $make_primary || DB_count($_TABLES['store_product_images'], 'product_id', $product_id) == 0 ? 1 : 0;
    DB_query("INSERT INTO {$_TABLES['store_product_images']} (product_id,image_url,is_primary,sort_order,created) VALUES ($product_id,'$url',$primary,$order,NOW())");
    if (DB_error()) {
        return false;
    }
    if ($primary) {
        return store_set_primary_image($product_id, (int) DB_insertId());
    }
    return true;
}

function store_delete_product_image($product_id, $image_id)
{
    global $_TABLES;

    $product_id = (int) $product_id;
    $image_id = (int) $image_id;
    $result = DB_query("SELECT is_primary FROM {$_TABLES['store_product_images']} WHERE id = $image_id AND product_id = $product_id LIMIT 1");
    if (DB_numRows($result) !== 1) {
        return false;
    }
    $row = DB_fetchArray($result);
    $was_primary = (int) $row['is_primary'] === 1;
    DB_query("DELETE FROM {$_TABLES['store_product_images']} WHERE id = $image_id AND product_id = $product_id");
    if ($was_primary) {
        $next = DB_query("SELECT id FROM {$_TABLES['store_product_images']} WHERE product_id = $product_id ORDER BY sort_order ASC,id ASC LIMIT 1");
        if (DB_numRows($next) === 1) {
            $next_row = DB_fetchArray($next);
            store_set_primary_image($product_id, (int) $next_row['id']);
        } else {
            DB_query("UPDATE {$_TABLES['store_products']} SET image_url = '', modified = NOW() WHERE id = $product_id");
        }
    }
    return !DB_error();
}

function store_get_categories($active_only = false)
{
    global $_TABLES;
    $where = $active_only ? ' WHERE active = 1' : '';
    $result = DB_query("SELECT * FROM {$_TABLES['store_categories']}" . $where . " ORDER BY name ASC");
    $rows = array();
    while ($row = DB_fetchArray($result)) {
        $rows[] = $row;
    }
    return $rows;
}

function store_cart_start()
{
    if (session_id() === '') {
        @session_start();
    }
    if (!isset($_SESSION['store_cart']) || !is_array($_SESSION['store_cart'])) {
        $_SESSION['store_cart'] = array();
    }
}

function store_cart_get()
{
    store_cart_start();
    return $_SESSION['store_cart'];
}

function store_cart_add($product_id, $quantity)
{
    store_cart_start();
    $product_id = (int) $product_id;
    $quantity = max(1, (int) $quantity);
    if ($product_id < 1) {
        return;
    }
    $current = isset($_SESSION['store_cart'][$product_id]) ? (int) $_SESSION['store_cart'][$product_id] : 0;
    $_SESSION['store_cart'][$product_id] = $current + $quantity;
}

function store_cart_set($product_id, $quantity)
{
    store_cart_start();
    $product_id = (int) $product_id;
    $quantity = (int) $quantity;
    if ($product_id < 1) {
        return;
    }
    if ($quantity < 1) {
        unset($_SESSION['store_cart'][$product_id]);
    } else {
        $_SESSION['store_cart'][$product_id] = $quantity;
    }
}

function store_cart_count()
{
    $cart = store_cart_get();
    $count = 0;
    foreach ($cart as $quantity) {
        $count += (int) $quantity;
    }
    return $count;
}

function store_format_price($price, $currency)
{
    $amount = number_format((float) $price, 2, '.', ' ');
    return $amount . ' ' . store_escape($currency);
}

function store_get_product($id)
{
    global $_TABLES;

    $id = (int) $id;
    if ($id < 1) {
        return false;
    }

    $result = DB_query("SELECT * FROM {$_TABLES['store_products']} WHERE id = $id");
    if (DB_numRows($result) !== 1) {
        return false;
    }

    return DB_fetchArray($result);
}

function store_get_product_by_slug($slug)
{
    global $_TABLES;

    $slug = DB_escapeString((string) $slug);
    if ($slug === '') {
        return false;
    }

    $active_sql = SEC_hasRights('store.admin') ? '' : ' AND active = 1';
    $result = DB_query("SELECT * FROM {$_TABLES['store_products']} WHERE slug = '$slug'" . $active_sql);
    if (DB_numRows($result) !== 1) {
        return false;
    }

    return DB_fetchArray($result);
}

function store_cart_details()
{
    global $_STORE_CONF;

    $cart = store_cart_get();
    $details = array(
        'items' => array(),
        'total' => 0.0,
        'currency' => '',
        'error' => ''
    );

    foreach ($cart as $product_id => $quantity) {
        $product = store_get_product((int) $product_id);
        if (!$product || (int) $product['active'] !== 1) {
            continue;
        }

        $quantity = max(1, (int) $quantity);
        if (store_product_uses_stock($product) && $quantity > (int) $product['stock']) {
            $details['error'] = 'stock';
            return $details;
        }

        if ($details['currency'] === '') {
            $details['currency'] = $product['currency'];
        } elseif ($details['currency'] !== $product['currency']) {
            $details['error'] = 'currency';
            return $details;
        }

        $line_total = (float) $product['price'] * $quantity;
        $details['total'] += $line_total;
        $details['items'][] = array(
            'product' => $product,
            'quantity' => $quantity,
            'line_total' => $line_total
        );
    }

    if ($details['currency'] === '') {
        $details['currency'] = isset($_STORE_CONF['currency']) ? $_STORE_CONF['currency'] : 'EUR';
    }

    return $details;
}

/**
 * Return enabled payment methods.
 *
 * The core Store plugin owns the payment workflow while individual providers
 * can be added later without changing order creation logic.
 *
 * @return array
 */
function store_get_payment_methods()
{
    global $_STORE_CONF, $LANG_STORE;

    $methods = array();
    if (!isset($_STORE_CONF['manual_payment_enabled']) || (int) $_STORE_CONF['manual_payment_enabled'] === 1) {
        $label = isset($_STORE_CONF['manual_payment_label']) ? trim((string) $_STORE_CONF['manual_payment_label']) : '';
        if ($label === '') {
            $label = isset($LANG_STORE['payment_manual']) ? $LANG_STORE['payment_manual'] : 'Manual payment';
        }
        $methods['manual'] = array(
            'id' => 'manual',
            'label' => $label,
            'instructions' => isset($_STORE_CONF['manual_payment_instructions'])
                ? trim((string) $_STORE_CONF['manual_payment_instructions']) : ''
        );
    }

    return $methods;
}

function store_payment_method_exists($method)
{
    $methods = store_get_payment_methods();
    return isset($methods[$method]);
}

function store_payment_statuses()
{
    return array('pending', 'paid', 'failed', 'cancelled', 'refunded');
}

function store_get_payment($order_id)
{
    global $_TABLES;

    $order_id = (int) $order_id;
    if ($order_id < 1) {
        return false;
    }
    $result = DB_query("SELECT * FROM {$_TABLES['store_payments']} WHERE order_id = $order_id ORDER BY id DESC LIMIT 1");
    return DB_numRows($result) === 1 ? DB_fetchArray($result) : false;
}

function store_update_payment_status($order_id, $status, $reference = '')
{
    global $_TABLES;

    $order_id = (int) $order_id;
    $order = store_get_order($order_id);
    if (!$order || !in_array($status, store_payment_statuses(), true)) {
        return false;
    }

    $payment = store_get_payment($order_id);
    if (!$payment) {
        return false;
    }

    $status_db = DB_escapeString($status);
    $reference_db = DB_escapeString(trim((string) $reference));
    $paid_sql = $status === 'paid' ? 'NOW()' : 'NULL';
    DB_query("UPDATE {$_TABLES['store_payments']} SET status = '$status_db', reference = '$reference_db', "
        . "paid_at = $paid_sql, modified = NOW() WHERE id = " . (int) $payment['id'], 1);
    if (DB_error()) {
        return false;
    }

    DB_query("UPDATE {$_TABLES['store_orders']} SET payment_status = '$status_db', payment_reference = '$reference_db', "
        . "paid_at = $paid_sql, modified = NOW() WHERE id = $order_id", 1);
    if (DB_error()) {
        return false;
    }

    if ($status === 'paid' && $order['status'] === 'pending') {
        return store_update_order_status($order_id, 'paid');
    }

    return true;
}

function store_order_number($id)
{
    return 'ST' . date('Ymd') . '-' . str_pad((string) (int) $id, 6, '0', STR_PAD_LEFT);
}

function store_create_order($customer, $payment_method = 'manual')
{
    global $_TABLES, $_USER;

    $cart = store_cart_details();
    $payment_method = trim((string) $payment_method);
    if (!store_payment_method_exists($payment_method)) {
        return array('success' => false, 'error' => 'payment');
    }
    if (!$cart['items'] || $cart['error'] !== '') {
        return array('success' => false, 'error' => $cart['error'] !== '' ? $cart['error'] : 'empty');
    }

    $name = trim(isset($customer['name']) ? (string) $customer['name'] : '');
    $email = trim(isset($customer['email']) ? (string) $customer['email'] : '');
    $address1 = trim(isset($customer['address1']) ? (string) $customer['address1'] : '');
    $address2 = trim(isset($customer['address2']) ? (string) $customer['address2'] : '');
    $postal_code = trim(isset($customer['postal_code']) ? (string) $customer['postal_code'] : '');
    $city = trim(isset($customer['city']) ? (string) $customer['city'] : '');
    $country = trim(isset($customer['country']) ? (string) $customer['country'] : '');

    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'error' => 'customer');
    }

    $needs_shipping = false;
    foreach ($cart['items'] as $item) {
        if (store_product_uses_stock($item['product'])) {
            $needs_shipping = true;
            break;
        }
    }
    if ($needs_shipping && ($address1 === '' || $postal_code === '' || $city === '' || $country === '')) {
        return array('success' => false, 'error' => 'address');
    }

    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 1;
    if ($uid < 2) {
        $uid = 0;
    }

    $name_db = DB_escapeString($name);
    $email_db = DB_escapeString($email);
    $address1_db = DB_escapeString($address1);
    $address2_db = DB_escapeString($address2);
    $postal_db = DB_escapeString($postal_code);
    $city_db = DB_escapeString($city);
    $country_db = DB_escapeString($country);
    $currency_db = DB_escapeString($cart['currency']);
    $payment_method_db = DB_escapeString($payment_method);
    $total = number_format((float) $cart['total'], 2, '.', '');

    DB_query("INSERT INTO {$_TABLES['store_orders']} "
        . "(order_number,user_id,customer_name,customer_email,address1,address2,postal_code,city,country,status,payment_method,payment_status,payment_reference,paid_at,currency,total,stock_released,order_source,created,modified) VALUES "
        . "('', $uid, '$name_db', '$email_db', '$address1_db', '$address2_db', '$postal_db', '$city_db', '$country_db', 'pending', '$payment_method_db', 'pending', '', NULL, '$currency_db', '$total', 0, 'online', NOW(), NOW())", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    $order_id = (int) DB_insertId();
    $order_number = store_order_number($order_id);
    $order_number_db = DB_escapeString($order_number);
    DB_query("UPDATE {$_TABLES['store_orders']} SET order_number = '$order_number_db' WHERE id = $order_id", 1);

    DB_query("INSERT INTO {$_TABLES['store_payments']} "
        . "(order_id,provider,status,amount,currency,reference,transaction_id,details,created,modified,paid_at) VALUES "
        . "($order_id,'$payment_method_db','pending','$total','$currency_db','','','',NOW(),NOW(),NULL)", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    foreach ($cart['items'] as $item) {
        $product = $item['product'];
        $product_id = (int) $product['id'];
        $quantity = (int) $item['quantity'];
        $sku = DB_escapeString($product['sku']);
        $product_name = DB_escapeString($product['name']);
        $type = DB_escapeString($product['product_type']);
        $unit_price = number_format((float) $product['price'], 2, '.', '');
        $line_total = number_format((float) $item['line_total'], 2, '.', '');

        DB_query("INSERT INTO {$_TABLES['store_order_items']} "
            . "(order_id,product_id,sku,name,product_type,unit_price,quantity,line_total) VALUES "
            . "($order_id,$product_id,'$sku','$product_name','$type','$unit_price',$quantity,'$line_total')", 1);
        if (DB_error()) {
            return array('success' => false, 'error' => 'database');
        }

        if (store_product_uses_stock($product)) {
            DB_query("UPDATE {$_TABLES['store_products']} SET stock = stock - $quantity, modified = NOW() "
                . "WHERE id = $product_id AND stock >= $quantity", 1);
            if (DB_error()) {
                return array('success' => false, 'error' => 'stock');
            }
        }
    }

    store_cart_start();
    $_SESSION['store_cart'] = array();
    $_SESSION['store_last_order_id'] = $order_id;

    return array('success' => true, 'order_id' => $order_id, 'order_number' => $order_number);
}


/**
 * Return payment methods intended for point-of-sale orders.
 *
 * @return array
 */
function store_get_pos_payment_methods()
{
    global $LANG_STORE;

    return array(
        'pos_cash' => array('label' => $LANG_STORE['pos_payment_cash'], 'paid' => true),
        'pos_card' => array('label' => $LANG_STORE['pos_payment_card'], 'paid' => true),
        'pos_cheque' => array('label' => $LANG_STORE['pos_payment_cheque'], 'paid' => true),
        'pos_transfer' => array('label' => $LANG_STORE['pos_payment_transfer'], 'paid' => false),
        'pos_other' => array('label' => $LANG_STORE['pos_payment_other'], 'paid' => true)
    );
}

/**
 * Create an order from the point-of-sale interface.
 *
 * @param array  $requestedItems array(product_id => quantity)
 * @param string $paymentMethod  POS payment method id
 * @param array  $customer       optional customer information
 * @return array
 */
function store_create_pos_order($requestedItems, $paymentMethod, $customer = array())
{
    global $_TABLES, $_USER, $_STORE_CONF, $LANG_STORE;

    $methods = store_get_pos_payment_methods();
    if (!isset($methods[$paymentMethod])) {
        return array('success' => false, 'error' => 'payment');
    }
    if (!is_array($requestedItems) || count($requestedItems) < 1) {
        return array('success' => false, 'error' => 'empty');
    }

    $items = array();
    $currency = '';
    $total = 0.0;

    foreach ($requestedItems as $productId => $quantity) {
        $productId = (int) $productId;
        $quantity = (int) $quantity;
        if ($productId < 1 || $quantity < 1) {
            continue;
        }
        $product = store_get_product($productId);
        if (!$product || (int) $product['active'] !== 1) {
            return array('success' => false, 'error' => 'product');
        }
        if ($currency === '') {
            $currency = $product['currency'];
        } elseif ($currency !== $product['currency']) {
            return array('success' => false, 'error' => 'currency');
        }
        if (store_product_uses_stock($product) && (int) $product['stock'] < $quantity) {
            return array('success' => false, 'error' => 'stock');
        }
        $lineTotal = (float) $product['price'] * $quantity;
        $total += $lineTotal;
        $items[] = array('product' => $product, 'quantity' => $quantity, 'line_total' => $lineTotal);
    }

    if (!$items) {
        return array('success' => false, 'error' => 'empty');
    }

    $name = trim(isset($customer['name']) ? (string) $customer['name'] : '');
    $email = trim(isset($customer['email']) ? (string) $customer['email'] : '');
    if ($name === '') {
        $name = $LANG_STORE['pos_walk_in_customer'];
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'error' => 'customer');
    }

    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 0;
    $nameDb = DB_escapeString($name);
    $emailDb = DB_escapeString($email);
    $currencyDb = DB_escapeString($currency !== '' ? $currency : (isset($_STORE_CONF['currency']) ? $_STORE_CONF['currency'] : 'EUR'));
    $methodDb = DB_escapeString($paymentMethod);
    $totalDb = number_format($total, 2, '.', '');
    $isPaid = !empty($methods[$paymentMethod]['paid']);
    $paymentStatus = $isPaid ? 'paid' : 'pending';
    $orderStatus = $isPaid ? 'completed' : 'pending';
    $paidAt = $isPaid ? 'NOW()' : 'NULL';

    DB_query("INSERT INTO {$_TABLES['store_orders']} "
        . "(order_number,user_id,customer_name,customer_email,address1,address2,postal_code,city,country,status,"
        . "payment_method,payment_status,payment_reference,paid_at,currency,total,stock_released,order_source,created,modified) VALUES "
        . "('', $uid, '$nameDb', '$emailDb', '', '', '', '', '', '$orderStatus', '$methodDb', '$paymentStatus', '', "
        . "$paidAt, '$currencyDb', '$totalDb', 0, 'pos', NOW(), NOW())", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    $orderId = (int) DB_insertId();
    $orderNumber = store_order_number($orderId);
    DB_query("UPDATE {$_TABLES['store_orders']} SET order_number = '" . DB_escapeString($orderNumber)
        . "' WHERE id = $orderId", 1);

    DB_query("INSERT INTO {$_TABLES['store_payments']} "
        . "(order_id,provider,status,amount,currency,reference,transaction_id,details,created,modified,paid_at) VALUES "
        . "($orderId,'$methodDb','$paymentStatus','$totalDb','$currencyDb','','','POS',NOW(),NOW(),$paidAt)", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    foreach ($items as $item) {
        $product = $item['product'];
        $productId = (int) $product['id'];
        $quantity = (int) $item['quantity'];
        $sku = DB_escapeString($product['sku']);
        $productName = DB_escapeString($product['name']);
        $type = DB_escapeString($product['product_type']);
        $unitPrice = number_format((float) $product['price'], 2, '.', '');
        $lineTotal = number_format((float) $item['line_total'], 2, '.', '');

        DB_query("INSERT INTO {$_TABLES['store_order_items']} "
            . "(order_id,product_id,sku,name,product_type,unit_price,quantity,line_total) VALUES "
            . "($orderId,$productId,'$sku','$productName','$type','$unitPrice',$quantity,'$lineTotal')", 1);
        if (DB_error()) {
            return array('success' => false, 'error' => 'database');
        }

        if (store_product_uses_stock($product)) {
            DB_query("UPDATE {$_TABLES['store_products']} SET stock = stock - $quantity, modified = NOW() "
                . "WHERE id = $productId AND stock >= $quantity", 1);
            if (DB_error()) {
                return array('success' => false, 'error' => 'stock');
            }
        }
    }

    return array('success' => true, 'order_id' => $orderId, 'order_number' => $orderNumber,
        'payment_status' => $paymentStatus, 'status' => $orderStatus);
}

function store_get_order($order_id)
{
    global $_TABLES;

    $order_id = (int) $order_id;
    if ($order_id < 1) {
        return false;
    }
    $result = DB_query("SELECT * FROM {$_TABLES['store_orders']} WHERE id = $order_id LIMIT 1");
    return DB_numRows($result) === 1 ? DB_fetchArray($result) : false;
}

function store_get_order_items($order_id)
{
    global $_TABLES;

    $order_id = (int) $order_id;
    $items = array();
    if ($order_id < 1) {
        return $items;
    }
    $result = DB_query("SELECT * FROM {$_TABLES['store_order_items']} WHERE order_id = $order_id ORDER BY id ASC");
    while ($row = DB_fetchArray($result)) {
        $items[] = $row;
    }
    return $items;
}

function store_order_statuses()
{
    return array('pending', 'paid', 'processing', 'completed', 'cancelled');
}

function store_update_order_status($order_id, $status)
{
    global $_TABLES;

    $order = store_get_order($order_id);
    if (!$order || !in_array($status, store_order_statuses(), true)) {
        return false;
    }

    $order_id = (int) $order_id;
    $was_cancelled = $order['status'] === 'cancelled';
    $will_cancel = $status === 'cancelled';
    $stock_released = (int) $order['stock_released'];

    if ($will_cancel && !$was_cancelled && !$stock_released) {
        foreach (store_get_order_items($order_id) as $item) {
            if ($item['product_type'] === 'physical' && (int) $item['product_id'] > 0) {
                DB_query("UPDATE {$_TABLES['store_products']} SET stock = stock + " . (int) $item['quantity']
                    . ", modified = NOW() WHERE id = " . (int) $item['product_id'], 1);
            }
        }
        $stock_released = 1;
    } elseif (!$will_cancel && $was_cancelled && $stock_released) {
        foreach (store_get_order_items($order_id) as $item) {
            if ($item['product_type'] === 'physical' && (int) $item['product_id'] > 0) {
                $available = (int) DB_getItem($_TABLES['store_products'], 'stock', 'id = ' . (int) $item['product_id']);
                if ($available < (int) $item['quantity']) {
                    return false;
                }
            }
        }
        foreach (store_get_order_items($order_id) as $item) {
            if ($item['product_type'] === 'physical' && (int) $item['product_id'] > 0) {
                DB_query("UPDATE {$_TABLES['store_products']} SET stock = stock - " . (int) $item['quantity']
                    . ", modified = NOW() WHERE id = " . (int) $item['product_id'], 1);
            }
        }
        $stock_released = 0;
    }

    $status_db = DB_escapeString($status);
    DB_query("UPDATE {$_TABLES['store_orders']} SET status = '$status_db', stock_released = $stock_released, modified = NOW() WHERE id = $order_id", 1);
    return !DB_error();
}

function store_user_order_access($order)
{
    global $_USER;

    if (SEC_hasRights('store.admin')) {
        return true;
    }
    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 1;
    if ($uid > 1 && (int) $order['user_id'] === $uid) {
        return true;
    }
    store_cart_start();
    return $uid < 2 && isset($_SESSION['store_last_order_id'])
        && (int) $_SESSION['store_last_order_id'] === (int) $order['id'];
}

function plugin_getmenuitems_store()
{
    global $_CONF, $_STORE_CONF, $LANG_STORE;

    if (!empty($_STORE_CONF['hide_menu'])) {
        return false;
    }

    $items = array(
        $LANG_STORE['menu_store'] => $_CONF['site_url'] . '/store/index.php',
        $LANG_STORE['cart'] . ' (' . store_cart_count() . ')' => $_CONF['site_url'] . '/store/index.php?view=cart'
    );
    global $_USER;
    if (!empty($_USER['uid']) && (int) $_USER['uid'] > 1) {
        $items[$LANG_STORE['my_orders']] = $_CONF['site_url'] . '/store/index.php?view=orders';
    }
    return $items;
}

function plugin_geticon_store()
{
    global $_CONF;

    return $_CONF['site_admin_url'] . '/plugins/store/images/store.svg';
}

function plugin_cclabel_store()
{
    global $_CONF, $LANG_STORE;

    if (!SEC_hasRights('store.admin')) {
        return array();
    }

    return array(
        $LANG_STORE['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/store/index.php',
        plugin_geticon_store()
    );
}

function plugin_getadminoption_store()
{
    global $_CONF, $LANG_STORE;

    if (!SEC_hasRights('store.admin')) {
        return array();
    }

    return array(
        $LANG_STORE['plugin_name'],
        $_CONF['site_admin_url'] . '/plugins/store/index.php',
        ''
    );
}

function plugin_getwhatsnew_store()
{
    return '';
}

function plugin_getheadercode_store()
{
    $css = '<style>'
        . '.store-meta{color:#6b7280;font-size:.92em}'
        . '.store-primary-button,.store-secondary-button{display:inline-flex;align-items:center;justify-content:center;gap:6px;border-radius:6px;padding:9px 14px;text-decoration:none;cursor:pointer}'
        . '.store-primary-button{border:1px solid #5b2b86;background:#6f3a9b;color:#fff}.store-primary-button:hover{background:#5d2f83;color:#fff}'
        . '.store-secondary-button{border:1px solid #cbd5e1;background:#fff;color:#222}.store-secondary-button:hover{background:#f8fafc;color:#111}'
        . '.store-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:22px}'
        . '.store-card{display:flex;flex-direction:column;background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;box-shadow:0 3px 12px rgba(0,0,0,.05)}'
        . '.store-card-image-wrap{display:block;background:#f8fafc;border-radius:9px;overflow:hidden;margin-bottom:14px}'
        . '.store-image{display:block;width:100%;height:230px;object-fit:cover}'
        . '.store-card h2{margin:.25em 0 .55em;font-size:1.2em;line-height:1.25}.store-card h2 a{text-decoration:none}'
        . '.store-card-price{font-size:1.2em;font-weight:700;margin:auto 0 .8em;padding-top:.6em}'
        . '.store-category-nav{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 22px}.store-category-nav a{background:#fff;border:1px solid #ddd;border-radius:999px;padding:6px 11px;text-decoration:none}'
        . '.store-product-category{display:inline-block;margin:0 0 8px;color:#6b7280;font-size:.9em;font-weight:600;text-transform:uppercase;letter-spacing:.04em}'
        . '.store-product-detail{max-width:1080px;margin:0 auto}'
        . '.store-product-back{margin:0 0 16px}.store-product-back a{text-decoration:none}'
        . '.store-product-layout{display:grid;grid-template-columns:minmax(0,1.12fr) minmax(300px,.88fr);gap:36px;align-items:start}'
        . '.store-product-media,.store-product-summary,.store-product-description{background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.05)}'
        . '.store-product-media{padding:18px}.store-product-summary{padding:26px}.store-product-description{margin-top:28px;padding:24px}'
        . '.store-product-main-image-wrap{display:flex;align-items:center;justify-content:center;min-height:420px;background:#f8fafc;border-radius:10px;overflow:hidden}'
        . '.store-product-image{display:block;max-width:100%;max-height:560px;width:auto;height:auto;object-fit:contain}'
        . '.store-product-image-placeholder{display:flex;align-items:center;justify-content:center;min-height:360px;background:#f8fafc;border-radius:10px;color:#777}'
        . '.store-gallery-thumbs{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px}'
        . '.store-gallery-thumb{width:78px;height:68px;padding:3px;background:#fff;border:2px solid transparent;border-radius:7px;cursor:pointer;overflow:hidden}'
        . '.store-gallery-thumb img{width:100%;height:100%;object-fit:cover;border-radius:4px}.store-gallery-thumb-active{border-color:#6f3a9b}'
        . '.store-product-summary h1{font-size:2em;line-height:1.15;margin:.1em 0 .35em}'
        . '.store-product-sku{color:#6b7280;font-size:.9em;margin:0 0 16px}'
        . '.store-product-price{font-size:1.8em;font-weight:800;margin:.5em 0}'
        . '.store-stock-badge,.store-type-badge{display:inline-block;border-radius:999px;padding:5px 10px;font-size:.9em;font-weight:700}'
        . '.store-stock-in{background:#e8f7ec;color:#216e39}.store-stock-out{background:#fdecec;color:#a12626}.store-type-badge{background:#eef2ff;color:#4338ca}'
        . '.store-product-short-description{font-size:1.04em;line-height:1.6;margin:20px 0}'
        . '.store-add-cart-box{margin-top:24px;padding-top:20px;border-top:1px solid #e5e7eb}.store-add-cart-box label{display:block;font-weight:700;margin-bottom:8px}'
        . '.store-add-cart-controls{display:flex;gap:10px;align-items:stretch}.store-add-cart-controls input[type=number]{width:86px;box-sizing:border-box}'
        . '.store-product-description h2{margin-top:0}.store-product-description div{line-height:1.7}.store-product-technical{margin-top:18px;padding:20px 24px;background:#fff;border:1px solid #e5e7eb;border-radius:14px;box-shadow:0 4px 16px rgba(0,0,0,.04)}.store-product-technical h2{margin:0 0 14px}.store-product-technical-list{display:grid;grid-template-columns:max-content minmax(0,1fr);gap:8px 22px;margin:0}.store-product-technical-list dt{font-weight:700;color:#374151}.store-product-technical-list dd{margin:0;color:#111827}'
        . '.store-cart-table{width:100%;border-collapse:collapse}.store-cart-table th,.store-cart-table td{padding:10px;border-bottom:1px solid #ddd;text-align:left}'
        . '.store-cart-actions{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:16px}.store-checkout-layout{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(280px,.85fr);gap:24px;align-items:start}.store-checkout-panel,.store-order-card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;box-shadow:0 3px 12px rgba(0,0,0,.04)}.store-checkout-panel h2,.store-order-card h2{margin-top:0}.store-checkout-form label{display:block;font-weight:700;margin:12px 0 5px}.store-checkout-form input{width:100%;box-sizing:border-box}.store-checkout-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}.store-order-status{display:inline-block;border-radius:999px;padding:4px 9px;background:#eef2ff;color:#4338ca;font-weight:700;font-size:.88em}.store-order-meta{display:flex;gap:12px;flex-wrap:wrap;color:#6b7280;font-size:.92em}.store-payment-options{display:grid;gap:10px;margin:18px 0}.store-payment-option{display:block;border:1px solid #d9dee7;border-radius:10px;padding:12px;background:#fff}.store-payment-option input{width:auto;margin-right:8px}.store-payment-instructions{margin-top:12px;padding:12px;border-left:4px solid #6f3a9b;background:#f7f2fb;white-space:pre-line}.store-payment-status{display:inline-block;border-radius:999px;padding:4px 9px;background:#f3f4f6;color:#374151;font-weight:700;font-size:.88em}.store-orders-list{display:grid;gap:14px}.store-order-summary-table{width:100%;border-collapse:collapse}.store-order-summary-table th,.store-order-summary-table td{padding:8px;border-bottom:1px solid #e5e7eb;text-align:left}.store-admin-orders-link{margin-left:6px}.store-pos{max-width:1280px}.store-pos-layout{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(340px,.65fr);gap:18px;align-items:start}.store-pos-toolbar{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px}.store-pos-search{flex:1;min-width:220px;padding:11px 12px;border:1px solid #cfd5df;border-radius:8px}.store-pos-categories{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px}.store-pos-filter{border:1px solid #d6dbe3;background:#fff;color:#1f2937;border-radius:999px;padding:6px 11px;cursor:pointer}.store-pos-filter:hover{background:#f8fafc;color:#111}.store-pos-filter.is-active{background:#6f3a9b;color:#fff;border-color:#6f3a9b}.store-pos-products{display:grid;grid-template-columns:repeat(auto-fill,minmax(175px,1fr));gap:10px}.store-pos-product{display:flex;flex-direction:column;justify-content:space-between;min-height:210px;border:1px solid #dfe3e8;background:#fff;color:#1f2937;border-radius:10px;padding:10px;text-align:left;cursor:pointer}.store-pos-product:hover{border-color:#6f3a9b;box-shadow:0 3px 10px rgba(0,0,0,.06);color:#111}.store-pos-product[disabled]{opacity:.5;cursor:not-allowed}.store-pos-product-image{display:block;width:100%;height:100px;object-fit:cover;border-radius:7px;background:#f3f4f6;margin-bottom:9px}.store-pos-product-image-placeholder{display:flex;align-items:center;justify-content:center;width:100%;height:100px;border-radius:7px;background:#f3f4f6;color:#9ca3af;margin-bottom:9px;font-size:.82em}.store-pos-product-name{display:block;font-weight:800;color:#111827;font-size:1.02em;line-height:1.2}.store-pos-product-meta{display:block;font-size:.82em;color:#6b7280;margin-top:5px;line-height:1.3}.store-pos-product-stock{display:block;font-size:.8em;color:#4b5563;margin-top:4px}.store-pos-product-price{display:block;font-weight:800;font-size:1.08em;margin-top:10px;color:#111827}.store-pos-ticket{position:sticky;top:12px}.store-pos-lines{display:grid;gap:8px;margin:12px 0}.store-pos-line{display:grid;grid-template-columns:minmax(0,1fr) auto auto;gap:8px;align-items:center;border-bottom:1px solid #eceff3;padding:8px 0}.store-pos-qty{display:inline-flex;align-items:center;gap:4px}.store-pos-qty button{width:30px;height:30px;border:1px solid #ccd2da;background:#fff;color:#111827;border-radius:5px;cursor:pointer;font-weight:800;font-size:16px;line-height:1}.store-pos-qty button:hover{background:#f3f4f6;color:#111827}.store-pos-qty span{min-width:24px;text-align:center}.store-pos-remove{border:0;background:transparent;color:#b42318!important;cursor:pointer;font-size:1.15em;font-weight:800}.store-pos-remove:hover{color:#8f1d13!important}.store-pos-total{display:flex;justify-content:space-between;align-items:center;border-top:2px solid #2f3440;margin-top:10px;padding-top:12px;font-size:1.25em;font-weight:800}.store-pos-payment-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin:12px 0}.store-pos-payment{display:flex;align-items:center;gap:7px;border:1px solid #d9dee7;border-radius:8px;padding:9px;cursor:pointer}.store-pos-customer{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px}.store-pos-customer input{width:100%;box-sizing:border-box}.store-pos-submit{width:100%;font-size:1.05em;padding:12px 14px}.store-pos-empty{color:#6b7280;padding:14px 0}.store-source-badge{display:inline-block;border-radius:999px;padding:3px 8px;background:#f3f4f6;color:#374151;font-size:.82em;font-weight:700}.store-source-pos{background:#ecfdf3;color:#067647}.store-receipt{max-width:420px;margin:0 auto;background:#fff;padding:22px;border:1px solid #ddd}.store-receipt h1{text-align:center}.store-receipt-meta{text-align:center;color:#6b7280}.store-receipt-line{display:flex;justify-content:space-between;gap:12px;padding:6px 0}.store-receipt-total{font-weight:800;font-size:1.2em;border-top:2px solid #222;margin-top:10px;padding-top:10px}.store-receipt-actions{text-align:center;margin:16px 0}'
        . '.store-admin-table{width:100%;border-collapse:collapse}.store-admin-table th,.store-admin-table td{padding:9px;border-bottom:1px solid #ddd;text-align:left;vertical-align:middle}'
        . '.store-admin-home{max-width:1180px}.store-admin-home-head,.store-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:18px}.store-admin-home-head{margin-bottom:20px}.store-admin-home-head h1{margin-bottom:4px}.store-admin-home-actions,.store-editor-actions,.store-save-actions{display:flex;gap:8px;flex-wrap:wrap}.store-category-add-form{display:flex;gap:8px;max-width:680px}.store-category-add-form input[type=text]{flex:1;min-width:0}.store-category-chips{display:flex;flex-wrap:wrap;gap:7px;margin-top:12px}.store-category-chip{display:inline-flex;align-items:center;border:1px solid #d9dee7;background:#f8fafc;border-radius:999px;padding:5px 10px;font-size:.92em}.store-admin-table-wrap{overflow-x:auto}.store-products-panel{padding-bottom:8px}.store-version{margin-top:10px}.store-roadmap{max-width:1180px}.store-roadmap-doc{background:#fff;border:1px solid #e1e5ea;border-radius:12px;padding:24px 28px;line-height:1.65}.store-roadmap-doc h1{margin-top:0}.store-roadmap-doc h2{margin-top:1.8em;padding-top:.2em;border-top:1px solid #eceff3}.store-roadmap-doc h3{margin-top:1.35em}.store-roadmap-doc ul,.store-roadmap-doc ol{padding-left:1.5em}.store-roadmap-doc li{margin:.24em 0}.store-roadmap-doc code{background:#f3f4f6;border-radius:4px;padding:2px 5px;font-size:.94em}.store-roadmap-doc pre{background:#111827;color:#f9fafb;border-radius:8px;padding:14px;overflow:auto;white-space:pre-wrap}.store-roadmap-doc blockquote{margin:1em 0;padding:.2em 1em;border-left:4px solid #6f3a9b;background:#f8f5fb;color:#4b5563}.store-roadmap-doc hr{border:0;border-top:1px solid #e5e7eb;margin:1.7em 0}.store-roadmap-doc strong{font-weight:800}'
        . '.store-actions{white-space:nowrap;display:flex;align-items:center;gap:6px}.store-actions form{display:inline-flex;margin:0}'
        . '.store-action-button{display:inline-flex;align-items:center;justify-content:center;box-sizing:border-box;min-width:72px;min-height:34px;padding:6px 11px;border:1px solid #cbd5e1;border-radius:6px;background:#fff;color:#222;font:inherit;font-size:.92em;font-weight:600;line-height:1.2;text-decoration:none;cursor:pointer;vertical-align:middle}.store-action-button:hover{background:#f8fafc;color:#111;text-decoration:none}.store-action-edit{border-color:#b7c4d4}.store-action-primary{border-color:#6f3a9b;background:#6f3a9b;color:#fff}.store-action-primary:hover{background:#5d2f83;color:#fff}.store-action-delete{border-color:#c9291c;background:#c9291c;color:#fff}.store-action-delete:hover{background:#aa2117;color:#fff}'
        . '.store-admin-editor{max-width:1120px}.store-editor-actions{justify-content:flex-end}.store-admin-editor-head,.store-gallery-manager-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:16px}'
        . '.store-admin-editor-head h1,.store-gallery-manager-head h2{margin-bottom:4px}'
        . '.store-admin-columns{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(260px,.6fr);gap:18px}'
        . '.store-admin-panel{background:#fff;border:1px solid #ddd;border-radius:9px;padding:18px;margin:0 0 18px}'
        . '.store-admin-panel h2{margin-top:0;font-size:1.2em}'
        . '.store-form label{display:block;font-weight:bold;margin-top:12px}.store-checkbox{font-weight:normal!important}'
        . '.store-form input[type=text],.store-form input[type=url],.store-form input[type=number],.store-form textarea,.store-form select{width:100%;box-sizing:border-box}'
        . '.store-form textarea{min-height:100px}'
        . '.store-image-picker{display:flex;gap:8px;align-items:center}.store-image-picker input[type=url]{flex:1}'
        . '.store-primary-image-layout{display:grid;grid-template-columns:260px minmax(0,1fr);gap:20px;align-items:start}.store-primary-image-preview{background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px}.store-primary-image-preview img{display:block;width:100%;height:190px;object-fit:contain}'
        . '.store-admin-savebar{display:flex;align-items:center;justify-content:space-between;gap:14px;position:sticky;bottom:0;background:rgba(255,255,255,.96);border-top:1px solid #ddd;padding:12px 0;margin-bottom:18px;z-index:5}.store-admin-back-link{text-decoration:none}.store-editor-footer-nav{margin:6px 0 24px}'
        . '.store-gallery-admin-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;margin-top:18px}.store-gallery-admin-card{position:relative;border:1px solid #ddd;border-radius:8px;padding:9px;background:#fff}.store-gallery-admin-image{height:130px;background:#f8fafc;border-radius:6px;overflow:hidden}.store-gallery-admin-image img{width:100%;height:100%;object-fit:contain}.store-primary-badge{display:inline-block;margin-top:7px;border-radius:999px;padding:4px 8px;background:#e8f7ec;color:#216e39;font-size:.82em;font-weight:700}.store-gallery-admin-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}.store-gallery-admin-actions form{margin:0}'
        . '.store-pending-gallery{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0}.store-pending-image{position:relative;width:82px;height:68px;border:1px solid #ddd;border-radius:6px;background:#f8fafc;overflow:hidden}.store-pending-image img{width:100%;height:100%;object-fit:cover}.store-pending-image span{position:absolute;right:3px;bottom:3px;background:rgba(0,0,0,.7);color:#fff;border-radius:999px;min-width:18px;text-align:center;font-size:11px}'
        . '@media(max-width:780px){.store-product-layout,.store-admin-columns,.store-primary-image-layout,.store-checkout-layout,.store-checkout-row,.store-pos-layout{grid-template-columns:1fr}.store-admin-home-head,.store-admin-editor-head,.store-gallery-manager-head{align-items:flex-start;flex-direction:column}.store-admin-home-actions,.store-editor-actions{width:100%}.store-category-add-form{flex-direction:column}.store-category-add-form .store-secondary-button{align-self:flex-start}.store-admin-savebar{align-items:flex-start;flex-direction:column}.store-product-summary h1{font-size:1.65em}.store-product-main-image-wrap{min-height:280px}.store-add-cart-controls{flex-wrap:wrap}.store-add-cart-controls .store-primary-button{flex:1}.store-admin-savebar{position:static}.store-pos-ticket{position:static}.store-pos-payment-grid,.store-pos-customer{grid-template-columns:1fr}}'
        . '</style>';

    return $css;
}

function plugin_getversion_store()
{
    return '0.7.0';
}

function plugin_chkVersion_store()
{
    return '0.7.0';
}

function plugin_upgrade_store()
{
    global $_CONF, $_TABLES;

    $installed = DB_getItem($_TABLES['plugins'], 'pi_version', "pi_name = 'store'");
    if (version_compare($installed, '0.2.1', '<')) {
        $check = DB_query("SHOW COLUMNS FROM {$_TABLES['store_products']} LIKE 'category_id'", 1);
        if (!DB_error() && DB_numRows($check) === 0) {
            DB_query("ALTER TABLE {$_TABLES['store_products']} ADD category_id int(10) unsigned NOT NULL default '0' AFTER id", 1);
        }
        $check = DB_query("SHOW COLUMNS FROM {$_TABLES['store_products']} LIKE 'image_url'", 1);
        if (!DB_error() && DB_numRows($check) === 0) {
            DB_query("ALTER TABLE {$_TABLES['store_products']} ADD image_url varchar(1000) NOT NULL default '' AFTER description", 1);
        }
        DB_query("CREATE TABLE IF NOT EXISTS {$_TABLES['store_categories']} ("
            . "id int(10) unsigned NOT NULL auto_increment, slug varchar(190) NOT NULL default '', "
            . "name varchar(255) NOT NULL default '', description text NULL, active tinyint(1) NOT NULL default '1', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), UNIQUE KEY store_category_slug (slug), "
            . "KEY store_category_active (active)) ENGINE=MyISAM", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin: failed to migrate database to 0.2.1.');
            return false;
        }
    }

    if (version_compare($installed, '0.3.0', '<')) {
        DB_query("CREATE TABLE IF NOT EXISTS {$_TABLES['store_product_images']} ("
            . "id int(10) unsigned NOT NULL auto_increment, product_id int(10) unsigned NOT NULL, "
            . "image_url varchar(1000) NOT NULL default '', is_primary tinyint(1) NOT NULL default '0', "
            . "sort_order int(11) NOT NULL default '0', created datetime NULL, PRIMARY KEY (id), "
            . "KEY store_image_product (product_id), KEY store_image_primary (product_id,is_primary)) ENGINE=MyISAM", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin: failed to create product image table for 0.3.0.');
            return false;
        }
        $products = DB_query("SELECT id,image_url FROM {$_TABLES['store_products']} WHERE image_url <> ''");
        while ($product = DB_fetchArray($products)) {
            $product_id = (int) $product['id'];
            if (DB_count($_TABLES['store_product_images'], 'product_id', $product_id) == 0) {
                store_add_product_image($product_id, $product['image_url'], true);
            }
        }
    }

    if (version_compare($installed, '0.4.0', '<')) {
        DB_query("CREATE TABLE IF NOT EXISTS {$_TABLES['store_orders']} ("
            . "id int(10) unsigned NOT NULL auto_increment, order_number varchar(32) NOT NULL default '', "
            . "user_id int(10) unsigned NOT NULL default '0', customer_name varchar(255) NOT NULL default '', "
            . "customer_email varchar(255) NOT NULL default '', address1 varchar(255) NOT NULL default '', "
            . "address2 varchar(255) NOT NULL default '', postal_code varchar(32) NOT NULL default '', "
            . "city varchar(190) NOT NULL default '', country varchar(190) NOT NULL default '', "
            . "status varchar(20) NOT NULL default 'pending', currency char(3) NOT NULL default 'EUR', "
            . "total decimal(12,2) NOT NULL default '0.00', stock_released tinyint(1) NOT NULL default '0', "
            . "created datetime NULL, modified datetime NULL, PRIMARY KEY (id), UNIQUE KEY store_order_number (order_number), "
            . "KEY store_order_user (user_id), KEY store_order_status (status), KEY store_order_created (created)) ENGINE=MyISAM", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin: failed to create orders table for 0.4.0.');
            return false;
        }
        DB_query("CREATE TABLE IF NOT EXISTS {$_TABLES['store_order_items']} ("
            . "id int(10) unsigned NOT NULL auto_increment, order_id int(10) unsigned NOT NULL, "
            . "product_id int(10) unsigned NOT NULL default '0', sku varchar(64) NOT NULL default '', "
            . "name varchar(255) NOT NULL default '', product_type varchar(20) NOT NULL default 'physical', "
            . "unit_price decimal(12,2) NOT NULL default '0.00', quantity int(10) unsigned NOT NULL default '1', "
            . "line_total decimal(12,2) NOT NULL default '0.00', PRIMARY KEY (id), KEY store_item_order (order_id), "
            . "KEY store_item_product (product_id)) ENGINE=MyISAM", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin: failed to create order items table for 0.4.0.');
            return false;
        }
    }

    if (version_compare($installed, '0.5.0', '<')) {
        $columns = array(
            'payment_method' => "ADD payment_method varchar(32) NOT NULL default 'manual' AFTER status",
            'payment_status' => "ADD payment_status varchar(20) NOT NULL default 'pending' AFTER payment_method",
            'payment_reference' => "ADD payment_reference varchar(255) NOT NULL default '' AFTER payment_status",
            'paid_at' => "ADD paid_at datetime NULL AFTER payment_reference"
        );
        foreach ($columns as $column => $definition) {
            $column_db = DB_escapeString($column);
            $check = DB_query("SHOW COLUMNS FROM {$_TABLES['store_orders']} LIKE '$column_db'", 1);
            if (!DB_error() && DB_numRows($check) === 0) {
                DB_query("ALTER TABLE {$_TABLES['store_orders']} $definition", 1);
                if (DB_error()) {
                    COM_errorLog('Store Plugin: failed to add order payment column ' . $column . '.');
                    return false;
                }
            }
        }
        DB_query("CREATE TABLE IF NOT EXISTS {$_TABLES['store_payments']} ("
            . "id int(10) unsigned NOT NULL auto_increment, order_id int(10) unsigned NOT NULL, "
            . "provider varchar(32) NOT NULL default 'manual', status varchar(20) NOT NULL default 'pending', "
            . "amount decimal(12,2) NOT NULL default '0.00', currency char(3) NOT NULL default 'EUR', "
            . "reference varchar(255) NOT NULL default '', transaction_id varchar(255) NOT NULL default '', "
            . "details text NULL, created datetime NULL, modified datetime NULL, paid_at datetime NULL, "
            . "PRIMARY KEY (id), KEY store_payment_order (order_id), KEY store_payment_status (status), "
            . "KEY store_payment_provider (provider)) ENGINE=MyISAM", 1);
        if (DB_error()) {
            COM_errorLog('Store Plugin: failed to create payments table for 0.5.0.');
            return false;
        }

        require_once $_CONF['path_system'] . 'classes/config.class.php';
        $config = config::get_instance();
        $current = $config->get_config('store');
        if (!isset($current['manual_payment_enabled'])) {
            $config->add('manual_payment_enabled', 1, 'select', 0, 0, 0, 50, true, 'store', 0);
        }
        if (!isset($current['manual_payment_label'])) {
            $config->add('manual_payment_label', 'Bank transfer / manual payment', 'text', 0, 0, 0, 60, true, 'store', 0);
        }
        if (!isset($current['manual_payment_instructions'])) {
            $config->add('manual_payment_instructions', 'Please contact us or use the payment instructions provided by the store administrator.', 'text', 0, 0, 0, 70, true, 'store', 0);
        }

        $existing_orders = DB_query("SELECT id,total,currency,status FROM {$_TABLES['store_orders']} o "
            . "WHERE NOT EXISTS (SELECT 1 FROM {$_TABLES['store_payments']} p WHERE p.order_id=o.id)");
        while ($existing_order = DB_fetchArray($existing_orders)) {
            $oid = (int) $existing_order['id'];
            $amount = number_format((float) $existing_order['total'], 2, '.', '');
            $currency = DB_escapeString($existing_order['currency']);
            $legacy_paid = in_array($existing_order['status'], array('paid', 'processing', 'completed'), true);
            $payment_status = $legacy_paid ? 'paid' : 'pending';
            $paid_at = $legacy_paid ? 'NOW()' : 'NULL';
            DB_query("INSERT INTO {$_TABLES['store_payments']} "
                . "(order_id,provider,status,amount,currency,created,modified,paid_at) VALUES "
                . "($oid,'manual','$payment_status','$amount','$currency',NOW(),NOW(),$paid_at)", 1);
            DB_query("UPDATE {$_TABLES['store_orders']} SET payment_method='manual', payment_status='$payment_status', "
                . "paid_at=$paid_at WHERE id=$oid", 1);
        }
    }

    if (version_compare($installed, '0.6.0', '<')) {
        $sourceColumn = DB_query("SHOW COLUMNS FROM {$_TABLES['store_orders']} LIKE 'order_source'", 1);
        if (!DB_error() && DB_numRows($sourceColumn) < 1) {
            DB_query("ALTER TABLE {$_TABLES['store_orders']} ADD order_source varchar(20) NOT NULL default 'online' AFTER stock_released", 1);
            if (DB_error()) {
                COM_errorLog('Store Plugin: failed to add order_source for 0.6.0.');
                return false;
            }
        }
        DB_query("UPDATE {$_TABLES['store_orders']} SET order_source='online' WHERE order_source='' OR order_source IS NULL", 1);
    }

    // 0.6.5 is a stabilization/documentation release: no schema migration.

    if (version_compare($installed, '0.7.0', '<')) {
        require_once $_CONF['path'] . 'plugins/store/includes/upgrade070.php';
        if (!store_upgrade_to_070()) {
            COM_errorLog('Store Plugin: failed to migrate database to 0.7.0.');
            return false;
        }
    }

    DB_query("UPDATE {$_TABLES['plugins']} SET pi_version = '0.7.0', pi_gl_version = '2.1.1' WHERE pi_name = 'store'", 1);
    if (DB_error()) {
        COM_errorLog('Store Plugin: failed to update plugin metadata to 0.7.0.');
        return false;
    }
    return true;
}

function plugin_autouninstall_store()
{
    return array(
        'tables' => array(
            'store_shipping_methods',
            'store_shipping_zone_locations',
            'store_shipping_zones',
            'store_tax_rates',
            'store_tax_zone_locations',
            'store_tax_zones',
            'store_tax_classes',
            'store_payments',
            'store_order_items',
            'store_orders',
            'store_product_images',
            'store_products',
            'store_categories'
        ),
        'groups' => array('Store Admin'),
        'features' => array(
            'store.admin',
            'config.store.tab_main'
        ),
        'php_blocks' => array(),
        'vars' => array()
    );
}
