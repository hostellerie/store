<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | checkout070-view.php                                                     |
// |                                                                           |
// | Render the international Store checkout and authoritative total preview. |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Load public 0.7.0 commerce strings.
 *
 * @return array
 */
function store_checkout070_language()
{
    global $_CONF;

    $language = isset($_CONF['language']) ? $_CONF['language'] : 'english';
    $file = $_CONF['path'] . 'plugins/store/language/checkout070/' . $language . '.php';
    if (!is_file($file) && strpos($language, 'french') === 0) {
        $file = $_CONF['path'] . 'plugins/store/language/checkout070/french.php';
    }
    if (!is_file($file)) {
        $file = $_CONF['path'] . 'plugins/store/language/checkout070/english.php';
    }
    $LANG_STORE_CHECKOUT070 = array();
    if (is_file($file)) {
        require $file;
    }
    return $LANG_STORE_CHECKOUT070;
}

/**
 * Render Store 0.7.0 checkout.
 *
 * @param array  $input
 * @param string $error
 * @return string
 */
function store_render_checkout070($input, $error = '')
{
    global $_USER, $LANG_STORE, $_STORE_CONF;

    $lang = store_checkout070_language();
    $cart = store_cart_details();
    $html = '<h1>' . store_escape($LANG_STORE['checkout']) . '</h1>';

    if (!$cart['items']) {
        return $html . '<p>' . store_escape($LANG_STORE['cart_empty']) . '</p>';
    }
    if ($cart['error'] !== '') {
        $text = $cart['error'] === 'currency'
            ? $LANG_STORE['checkout_currency_error'] : $LANG_STORE['checkout_stock_error'];
        return $html . COM_showMessageText($text, $LANG_STORE['checkout']);
    }

    if ($error !== '') {
        $errors = array(
            'customer' => isset($LANG_STORE['checkout_customer_error']) ? $LANG_STORE['checkout_customer_error'] : 'Invalid customer details.',
            'address' => isset($lang['address_error']) ? $lang['address_error'] : 'Enter a valid shipping destination.',
            'shipping' => isset($lang['shipping_error']) ? $lang['shipping_error'] : 'Select an available shipping method.',
            'payment' => isset($LANG_STORE['checkout_payment_error']) ? $LANG_STORE['checkout_payment_error'] : 'Select a payment method.',
            'stock' => isset($LANG_STORE['checkout_stock_error']) ? $LANG_STORE['checkout_stock_error'] : 'Stock changed.',
            'currency' => isset($LANG_STORE['checkout_currency_error']) ? $LANG_STORE['checkout_currency_error'] : 'Currency mismatch.',
            'database' => isset($LANG_STORE['checkout_database_error']) ? $LANG_STORE['checkout_database_error'] : 'The order could not be created.'
        );
        $html .= COM_showMessageText(isset($errors[$error]) ? $errors[$error] : $errors['database'], $LANG_STORE['checkout']);
    }

    $defaultName = !empty($_USER['fullname']) ? $_USER['fullname'] : (!empty($_USER['username']) ? $_USER['username'] : '');
    $defaultEmail = !empty($_USER['email']) ? $_USER['email'] : '';
    if ($input['name'] === '') {
        $input['name'] = $defaultName;
    }
    if ($input['email'] === '') {
        $input['email'] = $defaultEmail;
    }

    $initialQuote = store_checkout070_quote($input);
    $selectedShipping = store_checkout070_default_shipping_method(
        $initialQuote,
        isset($input['shipping_method_id']) ? $input['shipping_method_id'] : 0
    );
    $quote = $selectedShipping > 0
        ? store_checkout070_quote_with_shipping($input, $selectedShipping)
        : $initialQuote;

    $paymentMethods = store_get_payment_methods();
    $selectedPayment = isset($input['payment_method']) ? $input['payment_method'] : '';

    $html .= '<p>' . store_escape(isset($lang['intro']) ? $lang['intro'] : $LANG_STORE['checkout_intro']) . '</p>';
    $html .= '<div class="store-checkout-layout">';
    $html .= '<section class="store-checkout-panel"><form method="post" class="store-checkout-form">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">';

    $html .= '<h2>' . store_escape($LANG_STORE['customer_details']) . '</h2>'
        . '<label>' . store_escape($LANG_STORE['customer_name']) . '</label>'
        . '<input type="text" name="customer_name" required value="' . store_escape($input['name']) . '">'
        . '<label>' . store_escape($LANG_STORE['customer_email']) . '</label>'
        . '<input type="email" name="customer_email" required value="' . store_escape($input['email']) . '">';

    $requiresShipping = store_order070_requires_shipping($cart['items']);
    if ($requiresShipping) {
        $html .= '<h2>' . store_escape(isset($lang['shipping_address']) ? $lang['shipping_address'] : 'Shipping address') . '</h2>'
            . '<label>' . store_escape($LANG_STORE['address1']) . '</label>'
            . '<input type="text" name="address1" value="' . store_escape($input['address1']) . '">'
            . '<label>' . store_escape($LANG_STORE['address2']) . '</label>'
            . '<input type="text" name="address2" value="' . store_escape($input['address2']) . '">'
            . '<div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE['postal_code']) . '</label>'
            . '<input type="text" name="postal_code" value="' . store_escape($input['postal_code']) . '"></div>'
            . '<div><label>' . store_escape($LANG_STORE['city']) . '</label>'
            . '<input type="text" name="city" value="' . store_escape($input['city']) . '"></div></div>'
            . '<label>' . store_escape(isset($lang['region']) ? $lang['region'] : 'Region / state / province') . '</label>'
            . '<input type="text" name="region" value="' . store_escape($input['region']) . '">'
            . '<div class="store-checkout-row"><div><label>' . store_escape(isset($lang['country_name']) ? $lang['country_name'] : $LANG_STORE['country']) . '</label>'
            . '<input type="text" name="country" value="' . store_escape($input['country']) . '"></div>'
            . '<div><label>' . store_escape(isset($lang['country_code']) ? $lang['country_code'] : 'Country code') . '</label>'
            . '<input type="text" maxlength="2" name="country_code" value="' . store_escape($input['country_code']) . '" placeholder="US"></div></div>';

        if ($input['country_code'] === '') {
            $html .= '<p class="store-meta">' . store_escape(isset($lang['country_hint']) ? $lang['country_hint'] : 'Enter the two-letter country code to calculate shipping and taxes.') . '</p>';
        } else {
            $methods = isset($quote['shipping_methods']) ? $quote['shipping_methods'] : array();
            $html .= '<h2>' . store_escape(isset($lang['shipping_method']) ? $lang['shipping_method'] : 'Shipping method') . '</h2>';
            if (!$methods) {
                $html .= '<p>' . store_escape(isset($lang['no_shipping']) ? $lang['no_shipping'] : 'No shipping method is available for this destination.') . '</p>';
            } else {
                $html .= '<div class="store-payment-options">';
                foreach ($methods as $method) {
                    $methodId = (int) $method['id'];
                    $checked = $selectedShipping === $methodId ? ' checked' : '';
                    $price = store_format_price($method['calculated_price'], $cart['currency']);
                    $html .= '<label class="store-payment-option"><input type="radio" name="shipping_method_id" value="'
                        . $methodId . '"' . $checked . '> <strong>' . store_escape($method['name']) . '</strong> — '
                        . $price . '</label>';
                }
                $html .= '</div>';
            }
        }
    } else {
        $html .= '<input type="hidden" name="country_code" value="' . store_escape($input['country_code']) . '">';
    }

    $html .= '<p><button class="store-secondary-button" type="submit" name="store_action" value="quote070">'
        . store_escape(isset($lang['update_quote']) ? $lang['update_quote'] : 'Update shipping and taxes') . '</button></p>';

    $html .= '<h2>' . store_escape($LANG_STORE['payment']) . '</h2><div class="store-payment-options">';
    $firstPayment = true;
    foreach ($paymentMethods as $paymentId => $paymentMethod) {
        $checked = ($selectedPayment === $paymentId || ($selectedPayment === '' && $firstPayment)) ? ' checked' : '';
        $html .= '<label class="store-payment-option"><input type="radio" name="payment_method" value="'
            . store_escape($paymentId) . '"' . $checked . '> <strong>' . store_escape($paymentMethod['label']) . '</strong></label>';
        $firstPayment = false;
    }
    $html .= '</div>';

    $canSubmit = (bool) $paymentMethods;
    if ($requiresShipping && !empty($_STORE_CONF['shipping_enabled'])) {
        $canSubmit = $canSubmit && $selectedShipping > 0;
    }
    if ($canSubmit) {
        $html .= '<p><button class="store-primary-button" type="submit" name="store_action" value="place_order070">'
            . store_escape($LANG_STORE['place_order']) . '</button></p>';
    }
    $html .= '</form></section>';

    $html .= '<aside class="store-order-card"><h2>' . store_escape($LANG_STORE['order_details']) . '</h2>'
        . '<table class="store-order-summary-table">';
    foreach (isset($quote['items']) ? $quote['items'] : array() as $item) {
        $html .= '<tr><td>' . store_escape($item['product']['name']) . ' × ' . (int) $item['quantity'] . '</td><td>'
            . store_format_price($item['line_total'], $quote['currency']) . '</td></tr>';
    }
    if (!empty($quote['success'])) {
        $html .= '<tr><th>' . store_escape(isset($lang['subtotal']) ? $lang['subtotal'] : 'Subtotal') . '</th><th>'
            . store_format_price($quote['items_subtotal'], $quote['currency']) . '</th></tr>';
        if (store_money_to_minor($quote['shipping_subtotal']) !== 0 || !empty($quote['shipping_method'])) {
            $shippingLabel = !empty($quote['shipping_method']['name']) ? $quote['shipping_method']['name'] : (isset($lang['shipping']) ? $lang['shipping'] : 'Shipping');
            $html .= '<tr><th>' . store_escape($shippingLabel) . '</th><th>'
                . store_format_price($quote['shipping_subtotal'], $quote['currency']) . '</th></tr>';
        }
        if (store_money_to_minor($quote['tax_total']) !== 0 || !empty($_STORE_CONF['tax_enabled'])) {
            $html .= '<tr><th>' . store_escape(isset($lang['taxes']) ? $lang['taxes'] : 'Taxes') . '</th><th>'
                . store_format_price($quote['tax_total'], $quote['currency']) . '</th></tr>';
        }
        $html .= '<tr><th>' . store_escape($LANG_STORE['total']) . '</th><th>'
            . store_format_price($quote['grand_total'], $quote['currency']) . '</th></tr>';
    }
    $html .= '</table>';
    if (!empty($quote['prices_include_tax'])) {
        $html .= '<p class="store-meta">' . store_escape(isset($lang['tax_included']) ? $lang['tax_included'] : 'Configured prices include tax.') . '</p>';
    }
    $html .= '</aside></div>';

    return $html;
}
