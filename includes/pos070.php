<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | pos070.php                                                               |
// |                                                                           |
// | POS order persistence through the unified Store total calculator.        |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Create a POS order with the 0.7.0 calculator.
 *
 * POS does not require shipping in 0.7.0. Taxes use the configured default
 * country code when available; otherwise no destination tax rate is assumed.
 *
 * @param array  $requestedItems
 * @param string $paymentMethod
 * @param array  $customer
 * @return array
 */
function store_create_pos_order070($requestedItems, $paymentMethod, $customer = array())
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
        if (store_product_uses_stock($product) && (int) $product['stock'] < $quantity) {
            return array('success' => false, 'error' => 'stock');
        }
        $items[] = array('product' => $product, 'quantity' => $quantity);
    }
    if (!$items) {
        return array('success' => false, 'error' => 'empty');
    }

    $countryCode = isset($_STORE_CONF['default_country_code'])
        ? store_country_code($_STORE_CONF['default_country_code']) : '';
    $totals = store_calculate_totals($items, array(
        'country_code' => $countryCode,
        'region_code' => '',
        'shipping_method_id' => 0,
        'channel' => 'pos'
    ));
    if (!$totals['success']) {
        return array('success' => false, 'error' => $totals['error']);
    }

    // POS never adds shipping in 0.7.0, even when the cart contains physical goods.
    $shippingMinor = store_money_to_minor($totals['shipping_subtotal']) + store_money_to_minor($totals['shipping_tax']);
    if ($shippingMinor !== 0) {
        return array('success' => false, 'error' => 'shipping');
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
    $currencyDb = DB_escapeString($totals['currency']);
    $methodDb = DB_escapeString($paymentMethod);
    $itemsSubtotal = store_money_normalize($totals['items_subtotal']);
    $taxTotal = store_money_normalize($totals['tax_total']);
    $grandTotal = store_money_normalize($totals['grand_total']);
    $pricesIncludeTax = !empty($totals['prices_include_tax']) ? 1 : 0;
    $isPaid = !empty($methods[$paymentMethod]['paid']);
    $paymentStatus = $isPaid ? 'paid' : 'pending';
    $orderStatus = $isPaid ? 'completed' : 'pending';
    $paidAt = $isPaid ? 'NOW()' : 'NULL';

    DB_query("INSERT INTO {$_TABLES['store_orders']} "
        . "(order_number,user_id,customer_name,customer_email,address1,address2,postal_code,city,region,country,country_code,"
        . "status,payment_method,payment_status,payment_reference,paid_at,currency,items_subtotal,discount_total,"
        . "shipping_method,shipping_label,shipping_subtotal,shipping_tax,tax_total,total,prices_include_tax,"
        . "stock_released,order_source,created,modified) VALUES "
        . "('', $uid, '$nameDb', '$emailDb', '', '', '', '', '', '', '$countryCode', '$orderStatus', '$methodDb', "
        . "'$paymentStatus', '', $paidAt, '$currencyDb', '$itemsSubtotal', '0.0000', '', '', '0.0000', '0.0000', "
        . "'$taxTotal', '$grandTotal', $pricesIncludeTax, 0, 'pos', NOW(), NOW())", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    $orderId = (int) DB_insertId();
    $orderNumber = store_order_number($orderId);
    DB_query("UPDATE {$_TABLES['store_orders']} SET order_number='" . DB_escapeString($orderNumber)
        . "' WHERE id=$orderId", 1);

    DB_query("INSERT INTO {$_TABLES['store_payments']} "
        . "(order_id,provider,status,amount,currency,reference,transaction_id,details,created,modified,paid_at) VALUES "
        . "($orderId,'$methodDb','$paymentStatus','$grandTotal','$currencyDb','','','POS',NOW(),NOW(),$paidAt)", 1);
    if (DB_error()) {
        return array('success' => false, 'error' => 'database');
    }

    foreach ($totals['items'] as $item) {
        $product = $item['product'];
        $productId = (int) $product['id'];
        $quantity = (int) $item['quantity'];
        $taxClassId = isset($item['tax_class_id']) ? (int) $item['tax_class_id'] : 0;
        $skuDb = DB_escapeString(isset($product['sku']) ? $product['sku'] : '');
        $productNameDb = DB_escapeString(isset($product['name']) ? $product['name'] : '');
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
            . "($orderId,$productId,$taxClassId,'$skuDb','$productNameDb','$typeDb','$unitPrice',$quantity,"
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

    return array(
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'payment_status' => $paymentStatus,
        'status' => $orderStatus,
        'totals' => $totals
    );
}
