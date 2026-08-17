<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | orders070.php                                                            |
// |                                                                           |
// | Persist orders from the unified tax, shipping and totals calculator.      |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Build the normalized calculation context used for an online order.
 *
 * @param array $customer
 * @param int   $shippingMethodId
 * @return array
 */
function store_order070_context($customer, $shippingMethodId)
{
    return array(
        'country_code' => isset($customer['country_code']) ? $customer['country_code'] : '',
        'region_code' => isset($customer['region']) ? $customer['region'] : '',
        'shipping_method_id' => (int) $shippingMethodId,
        'channel' => 'online'
    );
}

/**
 * Return whether cart items require a delivery method.
 *
 * @param array $items
 * @return bool
 */
function store_order070_requires_shipping($items)
{
    foreach ($items as $item) {
        if (!empty($item['product']) && store_product_uses_stock($item['product'])) {
            return true;
        }
    }
    return false;
}

/**
 * Validate and normalize international customer data.
 *
 * @param array $customer
 * @param bool  $requiresShipping
 * @return array
 */
function store_order070_customer($customer, $requiresShipping)
{
    $normalized = array(
        'name' => trim(isset($customer['name']) ? (string) $customer['name'] : ''),
        'email' => trim(isset($customer['email']) ? (string) $customer['email'] : ''),
        'address1' => trim(isset($customer['address1']) ? (string) $customer['address1'] : ''),
        'address2' => trim(isset($customer['address2']) ? (string) $customer['address2'] : ''),
        'postal_code' => trim(isset($customer['postal_code']) ? (string) $customer['postal_code'] : ''),
        'city' => trim(isset($customer['city']) ? (string) $customer['city'] : ''),
        'region' => trim(isset($customer['region']) ? (string) $customer['region'] : ''),
        'country' => trim(isset($customer['country']) ? (string) $customer['country'] : ''),
        'country_code' => store_country_code(isset($customer['country_code']) ? $customer['country_code'] : '')
    );

    if ($normalized['name'] === '' || $normalized['email'] === ''
        || !filter_var($normalized['email'], FILTER_VALIDATE_EMAIL)) {
        return array('success' => false, 'error' => 'customer', 'customer' => $normalized);
    }

    if ($requiresShipping) {
        if ($normalized['address1'] === '' || $normalized['city'] === '' || $normalized['country_code'] === '') {
            return array('success' => false, 'error' => 'address', 'customer' => $normalized);
        }
        // Postal codes are not universal, so Store does not require one globally.
        if ($normalized['country'] === '') {
            $normalized['country'] = $normalized['country_code'];
        }
    }

    return array('success' => true, 'error' => '', 'customer' => $normalized);
}

/**
 * Create a 0.7.0 online order from the current session cart.
 *
 * All monetary values persisted here come from store_calculate_totals().
 * Browser-submitted totals are never trusted.
 *
 * @param array  $customer
 * @param string $paymentMethod
 * @param int    $shippingMethodId
 * @return array
 */
function store_create_order070($customer, $paymentMethod = 'manual', $shippingMethodId = 0)
{
    global $_TABLES, $_USER;

    $cart = store_cart_details();
    if (!$cart['items'] || $cart['error'] !== '') {
        return array('success' => false, 'error' => $cart['error'] !== '' ? $cart['error'] : 'empty');
    }

    $paymentMethod = trim((string) $paymentMethod);
    if (!store_payment_method_exists($paymentMethod)) {
        return array('success' => false, 'error' => 'payment');
    }

    $requiresShipping = store_order070_requires_shipping($cart['items']);
    $customerResult = store_order070_customer($customer, $requiresShipping);
    if (!$customerResult['success']) {
        return array('success' => false, 'error' => $customerResult['error']);
    }
    $customer = $customerResult['customer'];

    $context = store_order070_context($customer, $shippingMethodId);
    $totals = store_calculate_totals($cart['items'], $context);
    if (!$totals['success']) {
        return array('success' => false, 'error' => $totals['error']);
    }

    if ($requiresShipping) {
        global $_STORE_CONF;
        $shippingEnabled = !isset($_STORE_CONF['shipping_enabled']) || (int) $_STORE_CONF['shipping_enabled'] === 1;
        if ($shippingEnabled && empty($totals['shipping_method'])) {
            return array('success' => false, 'error' => 'shipping');
        }
    }

    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 1;
    if ($uid < 2) {
        $uid = 0;
    }

    $nameDb = DB_escapeString($customer['name']);
    $emailDb = DB_escapeString($customer['email']);
    $address1Db = DB_escapeString($customer['address1']);
    $address2Db = DB_escapeString($customer['address2']);
    $postalDb = DB_escapeString($customer['postal_code']);
    $cityDb = DB_escapeString($customer['city']);
    $regionDb = DB_escapeString($customer['region']);
    $countryDb = DB_escapeString($customer['country']);
    $countryCodeDb = DB_escapeString($customer['country_code']);
    $currencyDb = DB_escapeString($totals['currency']);
    $paymentMethodDb = DB_escapeString($paymentMethod);

    $shippingCode = '';
    $shippingLabel = '';
    if (!empty($totals['shipping_method'])) {
        $shippingCode = isset($totals['shipping_method']['code']) ? $totals['shipping_method']['code'] : '';
        $shippingLabel = isset($totals['shipping_method']['name']) ? $totals['shipping_method']['name'] : '';
    }
    $shippingCodeDb = DB_escapeString($shippingCode);
    $shippingLabelDb = DB_escapeString($shippingLabel);

    $itemsSubtotal = store_money_normalize($totals['items_subtotal']);
    $discountTotal = store_money_normalize($totals['discount_total']);
    $shippingSubtotal = store_money_normalize($totals['shipping_subtotal']);
    $shippingTax = store_money_normalize($totals['shipping_tax']);
    $taxTotal = store_money_normalize($totals['tax_total']);
    $grandTotal = store_money_normalize($totals['grand_total']);
    $pricesIncludeTax = !empty($totals['prices_include_tax']) ? 1 : 0;

    DB_query("INSERT INTO {$_TABLES['store_orders']} "
        . "(order_number,user_id,customer_name,customer_email,address1,address2,postal_code,city,region,country,country_code,"
        . "status,payment_method,payment_status,payment_reference,paid_at,currency,items_subtotal,discount_total,"
        . "shipping_method,shipping_label,shipping_subtotal,shipping_tax,tax_total,total,prices_include_tax,"
        . "stock_released,order_source,created,modified) VALUES "
        . "('', $uid, '$nameDb', '$emailDb', '$address1Db', '$address2Db', '$postalDb', '$cityDb', '$regionDb', "
        . "'$countryDb', '$countryCodeDb', 'pending', '$paymentMethodDb', 'pending', '', NULL, '$currencyDb', "
        . "'$itemsSubtotal', '$discountTotal', '$shippingCodeDb', '$shippingLabelDb', '$shippingSubtotal', "
        . "'$shippingTax', '$taxTotal', '$grandTotal', $pricesIncludeTax, 0, 'online', NOW(), NOW())", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    $orderId = (int) DB_insertId();
    $orderNumber = store_order_number($orderId);
    $orderNumberDb = DB_escapeString($orderNumber);
    DB_query("UPDATE {$_TABLES['store_orders']} SET order_number='$orderNumberDb' WHERE id=$orderId", 1);

    DB_query("INSERT INTO {$_TABLES['store_payments']} "
        . "(order_id,provider,status,amount,currency,reference,transaction_id,details,created,modified,paid_at) VALUES "
        . "($orderId,'$paymentMethodDb','pending','$grandTotal','$currencyDb','','','',NOW(),NOW(),NULL)", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    foreach ($totals['items'] as $item) {
        $product = $item['product'];
        $productId = (int) $product['id'];
        $quantity = (int) $item['quantity'];
        $taxClassId = isset($item['tax_class_id']) ? (int) $item['tax_class_id'] : 0;
        $skuDb = DB_escapeString(isset($product['sku']) ? $product['sku'] : '');
        $nameItemDb = DB_escapeString(isset($product['name']) ? $product['name'] : '');
        $typeDb = DB_escapeString(isset($product['product_type']) ? $product['product_type'] : 'physical');
        $taxLabelDb = DB_escapeString(isset($item['tax_label']) ? $item['tax_label'] : '');
        $unitPrice = store_money_normalize($item['unit_price']);
        $lineSubtotal = store_money_normalize($item['line_subtotal']);
        $taxRate = number_format(isset($item['tax_rate']) ? (float) $item['tax_rate'] : 0, 4, '.', '');
        $taxTotalItem = store_money_normalize($item['tax_total']);
        $unitTaxMinor = $quantity > 0
            ? (int) round(store_money_to_minor($item['tax_total']) / $quantity)
            : 0;
        $unitTax = store_money_from_minor($unitTaxMinor);
        $lineTotal = store_money_normalize($item['line_total']);

        DB_query("INSERT INTO {$_TABLES['store_order_items']} "
            . "(order_id,product_id,tax_class_id,sku,name,product_type,unit_price,quantity,line_subtotal,tax_label,"
            . "tax_rate,unit_tax,tax_total,line_total) VALUES "
            . "($orderId,$productId,$taxClassId,'$skuDb','$nameItemDb','$typeDb','$unitPrice',$quantity,"
            . "'$lineSubtotal','$taxLabelDb','$taxRate','$unitTax','$taxTotalItem','$lineTotal')", 1);
        if (DB_error()) {
            return array('success' => false, 'error' => 'database');
        }

        if (store_product_uses_stock($product)) {
            DB_query("UPDATE {$_TABLES['store_products']} SET stock=stock-$quantity,modified=NOW() "
                . "WHERE id=$productId AND stock >= $quantity", 1);
            if (DB_error()) {
                return array('success' => false, 'error' => 'stock');
            }
        }
    }

    store_cart_start();
    $_SESSION['store_cart'] = array();
    $_SESSION['store_last_order_id'] = $orderId;

    return array(
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'totals' => $totals
    );
}

/**
 * Calculate a checkout preview without creating an order.
 *
 * @param array $customer
 * @param int   $shippingMethodId
 * @return array
 */
function store_checkout070_preview($customer, $shippingMethodId = 0)
{
    $cart = store_cart_details();
    if (!$cart['items'] || $cart['error'] !== '') {
        return array('success' => false, 'error' => $cart['error'] !== '' ? $cart['error'] : 'empty');
    }

    $requiresShipping = store_order070_requires_shipping($cart['items']);
    $normalized = store_order070_customer($customer, false);
    $customerData = $normalized['customer'];
    $context = store_order070_context($customerData, $shippingMethodId);
    $totals = store_calculate_totals($cart['items'], $context);

    if (!$totals['success']) {
        return $totals;
    }

    $totals['shipping_methods'] = array();
    if ($requiresShipping && $customerData['country_code'] !== '') {
        $totals['shipping_methods'] = store_get_shipping_methods(
            $customerData['country_code'],
            $customerData['region'],
            $totals['weight'],
            $totals['items_subtotal']
        );
    }

    return $totals;
}
