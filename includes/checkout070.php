<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | checkout070.php                                                          |
// |                                                                           |
// | Public checkout helpers for international destination and shipping.      |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Return normalized checkout input from request data.
 *
 * @param array $source
 * @return array
 */
function store_checkout070_input($source)
{
    global $_STORE_CONF;

    $defaultCountry = isset($_STORE_CONF['default_country_code'])
        ? store_country_code($_STORE_CONF['default_country_code']) : '';

    return array(
        'name' => isset($source['customer_name']) ? trim((string) $source['customer_name']) : '',
        'email' => isset($source['customer_email']) ? trim((string) $source['customer_email']) : '',
        'address1' => isset($source['address1']) ? trim((string) $source['address1']) : '',
        'address2' => isset($source['address2']) ? trim((string) $source['address2']) : '',
        'postal_code' => isset($source['postal_code']) ? trim((string) $source['postal_code']) : '',
        'city' => isset($source['city']) ? trim((string) $source['city']) : '',
        'region' => isset($source['region']) ? trim((string) $source['region']) : '',
        'country' => isset($source['country']) ? trim((string) $source['country']) : '',
        'country_code' => isset($source['country_code'])
            ? store_country_code($source['country_code']) : $defaultCountry,
        'shipping_method_id' => isset($source['shipping_method_id']) ? (int) $source['shipping_method_id'] : 0,
        'payment_method' => isset($source['payment_method']) ? trim((string) $source['payment_method']) : ''
    );
}

/**
 * Return true when enough destination data exists to quote shipping/tax.
 *
 * Country code alone is sufficient for country-wide zones. Region improves
 * specificity but is intentionally optional because address formats vary.
 *
 * @param array $input
 * @return bool
 */
function store_checkout070_can_quote($input)
{
    return !empty($input['country_code']);
}

/**
 * Build a checkout quote and eligible methods.
 *
 * @param array $input
 * @return array
 */
function store_checkout070_quote($input)
{
    $customer = array(
        'name' => isset($input['name']) ? $input['name'] : '',
        'email' => isset($input['email']) ? $input['email'] : '',
        'address1' => isset($input['address1']) ? $input['address1'] : '',
        'address2' => isset($input['address2']) ? $input['address2'] : '',
        'postal_code' => isset($input['postal_code']) ? $input['postal_code'] : '',
        'city' => isset($input['city']) ? $input['city'] : '',
        'region' => isset($input['region']) ? $input['region'] : '',
        'country' => isset($input['country']) ? $input['country'] : '',
        'country_code' => isset($input['country_code']) ? $input['country_code'] : ''
    );

    return store_checkout070_preview(
        $customer,
        isset($input['shipping_method_id']) ? (int) $input['shipping_method_id'] : 0
    );
}

/**
 * Select the first eligible shipping method when none is selected yet.
 *
 * This is only a UI convenience. Order creation always validates eligibility
 * server-side again.
 *
 * @param array $quote
 * @param int   $selectedId
 * @return int
 */
function store_checkout070_default_shipping_method($quote, $selectedId)
{
    $selectedId = (int) $selectedId;
    if ($selectedId > 0) {
        foreach (isset($quote['shipping_methods']) ? $quote['shipping_methods'] : array() as $method) {
            if ((int) $method['id'] === $selectedId) {
                return $selectedId;
            }
        }
    }

    if (!empty($quote['shipping_methods'][0]['id'])) {
        return (int) $quote['shipping_methods'][0]['id'];
    }
    return 0;
}

/**
 * Recalculate a quote after selecting a shipping method.
 *
 * @param array $input
 * @param int   $shippingMethodId
 * @return array
 */
function store_checkout070_quote_with_shipping($input, $shippingMethodId)
{
    $input['shipping_method_id'] = (int) $shippingMethodId;
    return store_checkout070_quote($input);
}
