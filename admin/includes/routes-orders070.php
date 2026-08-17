<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-orders070.php                                                     |
// |                                                                           |
// | 0.7.0 order detail and POS receipt views with commercial snapshots.       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if (!isset($action)) {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
}

/**
 * Render normalized order totals from persisted snapshots.
 *
 * @param array $order
 * @param array $lang
 * @return string
 */
function store_admin_order_totals070($order, $lang)
{
    $currency = isset($order['currency']) ? $order['currency'] : '';
    $html = '';

    $itemsSubtotal = isset($order['items_subtotal']) ? $order['items_subtotal'] : $order['total'];
    $discountTotal = isset($order['discount_total']) ? $order['discount_total'] : 0;
    $shippingSubtotal = isset($order['shipping_subtotal']) ? $order['shipping_subtotal'] : 0;
    $shippingTax = isset($order['shipping_tax']) ? $order['shipping_tax'] : 0;
    $taxTotal = isset($order['tax_total']) ? $order['tax_total'] : 0;

    $html .= '<tr><th colspan="3">' . store_escape($lang['subtotal']) . '</th><th>'
        . store_format_price($itemsSubtotal, $currency) . '</th></tr>';

    if (store_money_to_minor($discountTotal) !== 0) {
        $html .= '<tr><th colspan="3">' . store_escape($lang['discount']) . '</th><th>− '
            . store_format_price($discountTotal, $currency) . '</th></tr>';
    }

    if (store_money_to_minor($shippingSubtotal) !== 0 || !empty($order['shipping_method'])) {
        $shippingLabel = !empty($order['shipping_label']) ? $order['shipping_label'] : $lang['shipping'];
        $html .= '<tr><th colspan="3">' . store_escape($shippingLabel) . '</th><th>'
            . store_format_price($shippingSubtotal, $currency) . '</th></tr>';
    }

    if (store_money_to_minor($shippingTax) !== 0) {
        $html .= '<tr><th colspan="3">' . store_escape($lang['shipping_tax']) . '</th><th>'
            . store_format_price($shippingTax, $currency) . '</th></tr>';
    }

    if (store_money_to_minor($taxTotal) !== 0) {
        $html .= '<tr><th colspan="3">' . store_escape($lang['taxes']) . '</th><th>'
            . store_format_price($taxTotal, $currency) . '</th></tr>';
    }

    $html .= '<tr class="store-order-grand-total"><th colspan="3">' . store_escape($lang['grand_total']) . '</th><th>'
        . store_format_price($order['total'], $currency) . '</th></tr>';

    return $html;
}

/**
 * Load order-view strings with a French fallback.
 *
 * @return array
 */
function store_admin_order_language070()
{
    global $_CONF;

    $language = isset($_CONF['language']) ? $_CONF['language'] : 'english';
    $file = $_CONF['path'] . 'plugins/store/language/orders070/' . $language . '.php';
    if (!is_file($file) && strpos($language, 'french') === 0) {
        $file = $_CONF['path'] . 'plugins/store/language/orders070/french.php';
    }
    if (!is_file($file)) {
        $file = $_CONF['path'] . 'plugins/store/language/orders070/english.php';
    }

    $LANG_STORE_ORDERS070 = array();
    if (is_file($file)) {
        require $file;
    }
    return $LANG_STORE_ORDERS070;
}

if ($action === 'order') {
    $lang = store_admin_order_language070();
    $orderId = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $order = store_get_order($orderId);
    if (!$order) {
        store_admin_redirect($LANG_STORE['no_orders']);
    }

    $content = '<div class="store-admin-editor"><div class="store-admin-editor-head"><div><h1>'
        . store_escape($LANG_STORE['order']) . ' ' . store_escape($order['order_number']) . '</h1><p class="store-meta">'
        . store_escape($order['created']) . '</p></div><div class="store-editor-actions"><a class="store-secondary-button" href="?action=orders">← '
        . store_escape($LANG_STORE['orders']) . '</a>';
    if ($order['order_source'] === 'pos') {
        $content .= '<a class="store-secondary-button" href="?action=receipt&id=' . $orderId . '">'
            . store_escape($LANG_STORE['pos_receipt']) . '</a>';
    }
    $content .= '</div></div>';

    if (!empty($message)) {
        $content .= COM_showMessageText($message, $LANG_STORE['orders']);
    }

    $content .= '<div class="store-admin-columns"><section class="store-admin-panel"><h2>'
        . store_escape($LANG_STORE['order_details']) . '</h2><table class="store-order-summary-table"><tr><th>'
        . store_escape($LANG_STORE['name']) . '</th><th>' . store_escape($LANG_STORE['quantity']) . '</th><th>'
        . store_escape($LANG_STORE['price']) . '</th><th>' . store_escape($LANG_STORE['total']) . '</th></tr>';

    foreach (store_get_order_items($orderId) as $item) {
        $content .= '<tr><td>' . store_escape($item['name']);
        if (!empty($item['tax_label']) || (isset($item['tax_rate']) && (float) $item['tax_rate'] > 0)) {
            $taxLabel = trim((string) $item['tax_label']);
            if ($taxLabel === '') {
                $taxLabel = $lang['tax'];
            }
            $content .= '<br><span class="store-meta">' . store_escape($taxLabel) . ' '
                . store_escape(number_format((float) $item['tax_rate'], 4, '.', '') . '%') . '</span>';
        }
        $content .= '</td><td>' . (int) $item['quantity'] . '</td><td>'
            . store_format_price($item['unit_price'], $order['currency']) . '</td><td>'
            . store_format_price($item['line_total'], $order['currency']) . '</td></tr>';
    }

    $content .= store_admin_order_totals070($order, $lang) . '</table>';
    if (!empty($order['prices_include_tax'])) {
        $content .= '<p class="store-meta">' . store_escape($lang['prices_include_tax']) . '</p>';
    }
    $content .= '</section><aside>';

    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['customer']) . '</h2><p><strong>'
        . store_escape($order['customer_name']) . '</strong><br>' . store_escape($order['customer_email']) . '</p>';
    if ($order['address1'] !== '' || $order['country_code'] !== '') {
        $content .= '<p>' . store_escape($order['address1'])
            . ($order['address2'] !== '' ? '<br>' . store_escape($order['address2']) : '')
            . '<br>' . store_escape(trim($order['postal_code'] . ' ' . $order['city']));
        if (!empty($order['region'])) {
            $content .= '<br>' . store_escape($order['region']);
        }
        $content .= '<br>' . store_escape($order['country']);
        if (!empty($order['country_code'])) {
            $content .= ' (' . store_escape($order['country_code']) . ')';
        }
        $content .= '</p>';
    }
    $content .= '</section>';

    $statusKey = 'status_' . $order['status'];
    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['order_status']) . '</h2><p><span class="store-order-status">'
        . store_escape(isset($LANG_STORE[$statusKey]) ? $LANG_STORE[$statusKey] : $order['status']) . '</span></p>'
        . '<form method="post"><input type="hidden" name="action" value="order_status"><input type="hidden" name="order_id" value="'
        . $orderId . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '"><select name="status">';
    foreach (store_order_statuses() as $status) {
        $key = 'status_' . $status;
        $content .= '<option value="' . store_escape($status) . '"' . ($status === $order['status'] ? ' selected' : '') . '>'
            . store_escape(isset($LANG_STORE[$key]) ? $LANG_STORE[$key] : $status) . '</option>';
    }
    $content .= '</select><p><button class="store-primary-button" type="submit">'
        . store_escape($LANG_STORE['update_status']) . '</button></p></form></section>';

    $payment = store_get_payment($orderId);
    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['payment']) . '</h2>';
    if ($payment) {
        $methods = $order['order_source'] === 'pos' ? store_get_pos_payment_methods() : store_get_payment_methods();
        $methodLabel = isset($methods[$order['payment_method']]['label'])
            ? $methods[$order['payment_method']]['label'] : $order['payment_method'];
        $content .= '<p><strong>' . store_escape($methodLabel) . '</strong><br>'
            . store_format_price($payment['amount'], $payment['currency']) . '</p><form method="post">'
            . '<input type="hidden" name="action" value="payment_status"><input type="hidden" name="order_id" value="' . $orderId . '">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
            . '<label>' . store_escape($LANG_STORE['payment_status']) . '</label><select name="payment_status">';
        foreach (store_payment_statuses() as $paymentStatus) {
            $key = 'payment_status_' . $paymentStatus;
            $content .= '<option value="' . store_escape($paymentStatus) . '"'
                . ($paymentStatus === $order['payment_status'] ? ' selected' : '') . '>'
                . store_escape(isset($LANG_STORE[$key]) ? $LANG_STORE[$key] : $paymentStatus) . '</option>';
        }
        $content .= '</select><label>' . store_escape($LANG_STORE['payment_reference']) . '</label><input type="text" name="payment_reference" value="'
            . store_escape($order['payment_reference']) . '"><p><button class="store-primary-button" type="submit">'
            . store_escape($LANG_STORE['update_payment']) . '</button></p></form>';
    }
    $content .= '</section></aside></div></div>';

    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['orders'])));
    exit;
}

if ($action === 'receipt') {
    $lang = store_admin_order_language070();
    $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $order = store_get_order($orderId);
    if (!$order || $order['order_source'] !== 'pos') {
        store_admin_redirect($LANG_STORE['no_orders']);
    }

    $methods = store_get_pos_payment_methods();
    $paymentLabel = isset($methods[$order['payment_method']]['label'])
        ? $methods[$order['payment_method']]['label'] : $order['payment_method'];

    $content = '<div class="store-receipt-actions"><a class="store-secondary-button" href="?action=pos">'
        . store_escape($LANG_STORE['pos_new_sale']) . '</a> <a class="store-secondary-button" href="?action=order&id=' . $orderId . '">'
        . store_escape($LANG_STORE['order_details']) . '</a> <button type="button" class="store-primary-button" onclick="window.print()">'
        . store_escape($LANG_STORE['pos_print_receipt']) . '</button></div><section class="store-receipt"><h1>'
        . store_escape($_CONF['site_name']) . '</h1><div class="store-receipt-meta">' . store_escape($LANG_STORE['order']) . ' '
        . store_escape($order['order_number']) . '<br>' . store_escape($order['created']) . '</div><hr>';

    foreach (store_get_order_items($orderId) as $item) {
        $content .= '<div class="store-receipt-line"><span>' . (int) $item['quantity'] . ' × ' . store_escape($item['name'])
            . '</span><span>' . store_format_price($item['line_total'], $order['currency']) . '</span></div>';
    }

    $content .= '<hr><div class="store-receipt-line"><span>' . store_escape($lang['subtotal']) . '</span><span>'
        . store_format_price(isset($order['items_subtotal']) ? $order['items_subtotal'] : $order['total'], $order['currency']) . '</span></div>';
    if (store_money_to_minor(isset($order['tax_total']) ? $order['tax_total'] : 0) !== 0) {
        $content .= '<div class="store-receipt-line"><span>' . store_escape($lang['taxes']) . '</span><span>'
            . store_format_price($order['tax_total'], $order['currency']) . '</span></div>';
    }
    $content .= '<div class="store-receipt-line store-receipt-total"><span>' . store_escape($lang['grand_total']) . '</span><span>'
        . store_format_price($order['total'], $order['currency']) . '</span></div><p><strong>'
        . store_escape($LANG_STORE['payment_method']) . ':</strong> ' . store_escape($paymentLabel) . '</p>';
    if (!empty($order['prices_include_tax'])) {
        $content .= '<p class="store-meta">' . store_escape($lang['prices_include_tax']) . '</p>';
    }
    $content .= '</section><style media="print">body *{visibility:hidden}.store-receipt,.store-receipt *{visibility:visible}.store-receipt{position:absolute;left:0;top:0;width:100%;max-width:none;border:0}.store-receipt-actions{display:none}</style>';

    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['pos_receipt'])));
    exit;
}
