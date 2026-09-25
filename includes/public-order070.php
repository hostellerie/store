<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | public-order070.php                                                      |
// |                                                                           |
// | Public order detail rendering with persisted tax and shipping snapshots. |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Load public order strings.
 *
 * @return array
 */
function store_public_order070_language()
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

/**
 * Intercept the public order detail route for 0.7.0.
 *
 * @return void
 */
function store_public_order070_route()
{
    global $_CONF, $_TABLES, $LANG_STORE;

    $view = isset($_GET['view']) ? (string) $_GET['view'] : '';
    if ($view !== 'order') {
        return;
    }

    $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $order = store_get_order($orderId);
    if (!$order || !store_user_order_access($order)) {
        $content = COM_showMessageText($LANG_STORE['product_not_found'], $LANG_STORE['order']);
        COM_output(COM_createHTMLDocument($content, store_document_options($LANG_STORE['order'])));
        exit;
    }

    $lang = store_public_order070_language();
    $statusKey = 'status_' . $order['status'];
    $content = '';

    if (!empty($_GET['created'])) {
        $content .= COM_showMessageText(sprintf($LANG_STORE['order_created'], $order['order_number']), $LANG_STORE['order']);
    }

    $content .= '<h1>' . store_escape($LANG_STORE['order']) . ' ' . store_escape($order['order_number']) . '</h1>'
        . '<div class="store-checkout-layout"><section class="store-order-card"><div class="store-order-meta"><span>'
        . store_escape($order['created']) . '</span><span class="store-order-status">'
        . store_escape(isset($LANG_STORE[$statusKey]) ? $LANG_STORE[$statusKey] : $order['status']) . '</span></div>'
        . '<table class="store-order-summary-table"><tr><th>' . store_escape($LANG_STORE['name']) . '</th><th>'
        . store_escape($LANG_STORE['quantity']) . '</th><th>' . store_escape($LANG_STORE['total']) . '</th></tr>';

    foreach (store_get_order_items($orderId) as $item) {
        $content .= '<tr><td>' . store_escape($item['name']);
        if (!empty($item['tax_label']) || (isset($item['tax_rate']) && (float) $item['tax_rate'] > 0)) {
            $taxLabel = trim((string) $item['tax_label']);
            if ($taxLabel === '') {
                $taxLabel = isset($lang['tax']) ? $lang['tax'] : 'Tax';
            }
            $content .= '<br><span class="store-meta">' . store_escape($taxLabel) . ' '
                . store_escape(number_format((float) $item['tax_rate'], 4, '.', '') . '%') . '</span>';
        }
        $content .= '</td><td>' . (int) $item['quantity'] . '</td><td>'
            . store_format_price($item['line_total'], $order['currency']) . '</td></tr>';
    }

    $itemsSubtotal = isset($order['items_subtotal']) && (float) $order['items_subtotal'] > 0
        ? $order['items_subtotal'] : $order['total'];
    $content .= '<tr><th colspan="2">' . store_escape(isset($lang['subtotal']) ? $lang['subtotal'] : 'Subtotal') . '</th><th>'
        . store_format_price($itemsSubtotal, $order['currency']) . '</th></tr>';

    if (!empty($order['shipping_method']) || (isset($order['shipping_subtotal']) && (float) $order['shipping_subtotal'] != 0)) {
        $shippingLabel = !empty($order['shipping_label']) ? $order['shipping_label']
            : (isset($lang['shipping']) ? $lang['shipping'] : 'Shipping');
        $content .= '<tr><th colspan="2">' . store_escape($shippingLabel) . '</th><th>'
            . store_format_price($order['shipping_subtotal'], $order['currency']) . '</th></tr>';
    }

    if (isset($order['tax_total']) && (float) $order['tax_total'] != 0) {
        $content .= '<tr><th colspan="2">' . store_escape(isset($lang['taxes']) ? $lang['taxes'] : 'Taxes') . '</th><th>'
            . store_format_price($order['tax_total'], $order['currency']) . '</th></tr>';
    }

    $content .= '<tr><th colspan="2">' . store_escape(isset($lang['grand_total']) ? $lang['grand_total'] : $LANG_STORE['total']) . '</th><th>'
        . store_format_price($order['total'], $order['currency']) . '</th></tr></table>';

    if (!empty($order['prices_include_tax'])) {
        $content .= '<p class="store-meta">' . store_escape(isset($lang['prices_include_tax'])
            ? $lang['prices_include_tax'] : 'Configured prices include tax.') . '</p>';
    }
    $content .= '</section>';

    $content .= '<aside class="store-order-card"><h2>' . store_escape($LANG_STORE['customer']) . '</h2><p><strong>'
        . store_escape($order['customer_name']) . '</strong><br>' . store_escape($order['customer_email']) . '</p>';
    if ($order['address1'] !== '' || !empty($order['country_code'])) {
        $content .= '<p>' . store_escape($order['address1']);
        if ($order['address2'] !== '') {
            $content .= '<br>' . store_escape($order['address2']);
        }
        $content .= '<br>' . store_escape(trim($order['postal_code'] . ' ' . $order['city']));
        if (!empty($order['region'])) {
            $content .= '<br>' . store_escape($order['region']);
        }
        if (!empty($order['country'])) {
            $content .= '<br>' . store_escape($order['country']);
        }
        if (!empty($order['country_code'])) {
            $content .= ' (' . store_escape($order['country_code']) . ')';
        }
        $content .= '</p>';
    }

    $payment = store_get_payment($orderId);
    $methods = store_get_payment_methods();
    $paymentLabel = isset($methods[$order['payment_method']]['label'])
        ? $methods[$order['payment_method']]['label'] : $order['payment_method'];
    $paymentStatusKey = 'payment_status_' . $order['payment_status'];
    $content .= '<h2>' . store_escape($LANG_STORE['payment']) . '</h2><p><strong>'
        . store_escape($paymentLabel) . '</strong><br><span class="store-payment-status">'
        . store_escape(isset($LANG_STORE[$paymentStatusKey]) ? $LANG_STORE[$paymentStatusKey] : $order['payment_status']) . '</span></p>';
    if ($payment && isset($payment['amount'])) {
        $content .= '<p>' . store_format_price($payment['amount'], $payment['currency']) . '</p>';
    }
    if ($order['payment_reference'] !== '') {
        $content .= '<p>' . store_escape($LANG_STORE['payment_reference']) . ': '
            . store_escape($order['payment_reference']) . '</p>';
    }
    if ($order['payment_method'] === 'manual' && $order['payment_status'] === 'pending'
        && isset($methods['manual']) && $methods['manual']['instructions'] !== '') {
        $content .= '<div class="store-payment-instructions">'
            . store_escape($methods['manual']['instructions']) . '</div>';
    }
    $content .= '</aside></div>';

    COM_output(COM_createHTMLDocument($content, store_document_options($LANG_STORE['order'])));
    exit;
}

store_public_order070_route();
