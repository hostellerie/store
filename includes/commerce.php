<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | commerce.php                                                             |
// |                                                                           |
// | International tax, shipping and unified order-total calculation engine.  |
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
// +---------------------------------------------------------------------------+

if (!defined('STORE_COMMERCE_LOADED')) {
    define('STORE_COMMERCE_LOADED', 1);
}

global $_TABLES, $_DB_table_prefix, $_STORE_CONF;

$store070Tables = array(
    'store_tax_zones',
    'store_tax_zone_locations',
    'store_tax_classes',
    'store_tax_rates',
    'store_shipping_zones',
    'store_shipping_zone_locations',
    'store_shipping_methods'
);
foreach ($store070Tables as $store070Table) {
    if (!isset($_TABLES[$store070Table])) {
        $_TABLES[$store070Table] = $_DB_table_prefix . $store070Table;
    }
}
unset($store070Tables, $store070Table);

/**
 * Return configured monetary precision.
 *
 * Store keeps this merchant-configurable instead of assuming a particular
 * national currency exponent. The supported range is deliberately bounded.
 *
 * @return int
 */
function store_money_decimals()
{
    global $_STORE_CONF;

    $decimals = isset($_STORE_CONF['currency_decimals']) ? (int) $_STORE_CONF['currency_decimals'] : 2;
    if ($decimals < 0) {
        $decimals = 0;
    } elseif ($decimals > 4) {
        $decimals = 4;
    }
    return $decimals;
}

/**
 * Normalize a decimal monetary value without using locale-specific formatting.
 *
 * @param mixed $value
 * @return string
 */
function store_money_normalize($value)
{
    return number_format((float) $value, store_money_decimals(), '.', '');
}

/**
 * Convert an amount to integer minor units for deterministic additions.
 *
 * @param mixed $value
 * @return int
 */
function store_money_to_minor($value)
{
    $factor = pow(10, store_money_decimals());
    return (int) round(((float) $value) * $factor);
}

/**
 * Convert integer minor units back to a normalized decimal string.
 *
 * @param int $minor
 * @return string
 */
function store_money_from_minor($minor)
{
    $factor = pow(10, store_money_decimals());
    return number_format(((int) $minor) / $factor, store_money_decimals(), '.', '');
}

/**
 * Normalize an ISO-style country code.
 *
 * Store accepts an empty value for POS or stores where destination is unknown.
 *
 * @param string $countryCode
 * @return string
 */
function store_country_code($countryCode)
{
    $countryCode = strtoupper(trim((string) $countryCode));
    return preg_match('/^[A-Z]{2}$/', $countryCode) ? $countryCode : '';
}

/**
 * Find the most specific active zone matching a destination.
 *
 * @param string $zoneTable
 * @param string $locationTable
 * @param string $countryCode
 * @param string $regionCode
 * @return int
 */
function store_match_zone($zoneTable, $locationTable, $countryCode, $regionCode = '')
{
    global $_TABLES;

    if (!isset($_TABLES[$zoneTable], $_TABLES[$locationTable])) {
        return 0;
    }

    $countryCode = store_country_code($countryCode);
    $regionCode = strtoupper(trim((string) $regionCode));
    if ($countryCode === '') {
        return 0;
    }

    $countryDb = DB_escapeString($countryCode);
    $regionDb = DB_escapeString($regionCode);

    if ($regionCode !== '') {
        $sql = "SELECT z.id FROM {$_TABLES[$zoneTable]} z "
            . "INNER JOIN {$_TABLES[$locationTable]} l ON l.zone_id=z.id "
            . "WHERE z.active=1 AND l.country_code='$countryDb' AND l.region_code='$regionDb' "
            . "ORDER BY z.priority ASC,z.id ASC LIMIT 1";
        $result = DB_query($sql, 1);
        if (!DB_error() && DB_numRows($result) === 1) {
            $row = DB_fetchArray($result);
            return (int) $row['id'];
        }
    }

    $sql = "SELECT z.id FROM {$_TABLES[$zoneTable]} z "
        . "INNER JOIN {$_TABLES[$locationTable]} l ON l.zone_id=z.id "
        . "WHERE z.active=1 AND l.country_code='$countryDb' AND (l.region_code='' OR l.region_code IS NULL) "
        . "ORDER BY z.priority ASC,z.id ASC LIMIT 1";
    $result = DB_query($sql, 1);
    if (!DB_error() && DB_numRows($result) === 1) {
        $row = DB_fetchArray($result);
        return (int) $row['id'];
    }

    return 0;
}

function store_match_tax_zone($countryCode, $regionCode = '')
{
    return store_match_zone('store_tax_zones', 'store_tax_zone_locations', $countryCode, $regionCode);
}

function store_match_shipping_zone($countryCode, $regionCode = '')
{
    return store_match_zone('store_shipping_zones', 'store_shipping_zone_locations', $countryCode, $regionCode);
}

/**
 * Fetch the configured tax rate for one class and destination.
 *
 * Multiple rates may later be compounded. 0.7.0 returns all active rates so
 * the calculator can apply them in priority order without hard-coded labels.
 *
 * @param int    $taxClassId
 * @param string $countryCode
 * @param string $regionCode
 * @return array
 */
function store_get_tax_rates($taxClassId, $countryCode, $regionCode = '')
{
    global $_TABLES;

    $taxClassId = (int) $taxClassId;
    if ($taxClassId < 1) {
        return array();
    }

    $zoneId = store_match_tax_zone($countryCode, $regionCode);
    if ($zoneId < 1) {
        return array();
    }

    $result = DB_query("SELECT * FROM {$_TABLES['store_tax_rates']} WHERE active=1 "
        . "AND zone_id=$zoneId AND tax_class_id=$taxClassId ORDER BY priority ASC,id ASC", 1);
    if (DB_error()) {
        return array();
    }

    $rates = array();
    while ($row = DB_fetchArray($result)) {
        $rates[] = $row;
    }
    return $rates;
}

/**
 * Calculate tax for an amount using a sequence of configured rates.
 *
 * @param int   $baseMinor
 * @param array $rates
 * @param bool  $pricesIncludeTax
 * @return array
 */
function store_calculate_tax_minor($baseMinor, $rates, $pricesIncludeTax)
{
    $baseMinor = (int) $baseMinor;
    if ($baseMinor <= 0 || !$rates) {
        return array('net' => $baseMinor, 'tax' => 0, 'gross' => $baseMinor, 'label' => '', 'rate' => '0.0000');
    }

    $combinedRate = 0.0;
    $labels = array();
    foreach ($rates as $rate) {
        $rateValue = isset($rate['rate']) ? (float) $rate['rate'] : 0.0;
        if ($rateValue <= 0) {
            continue;
        }
        $combinedRate += $rateValue;
        if (!empty($rate['name'])) {
            $labels[] = $rate['name'];
        }
    }

    if ($combinedRate <= 0) {
        return array('net' => $baseMinor, 'tax' => 0, 'gross' => $baseMinor, 'label' => implode(' + ', $labels), 'rate' => '0.0000');
    }

    if ($pricesIncludeTax) {
        $net = (int) round($baseMinor / (1 + ($combinedRate / 100)));
        $tax = $baseMinor - $net;
        $gross = $baseMinor;
    } else {
        $net = $baseMinor;
        $tax = (int) round($baseMinor * ($combinedRate / 100));
        $gross = $baseMinor + $tax;
    }

    return array(
        'net' => $net,
        'tax' => $tax,
        'gross' => $gross,
        'label' => implode(' + ', $labels),
        'rate' => number_format($combinedRate, 4, '.', '')
    );
}

/**
 * Return eligible shipping methods for a cart and destination.
 *
 * @param string $countryCode
 * @param string $regionCode
 * @param mixed  $cartWeight
 * @param mixed  $itemsSubtotal
 * @return array
 */
function store_get_shipping_methods($countryCode, $regionCode, $cartWeight, $itemsSubtotal)
{
    global $_TABLES, $_STORE_CONF;

    if (isset($_STORE_CONF['shipping_enabled']) && (int) $_STORE_CONF['shipping_enabled'] !== 1) {
        return array();
    }

    $zoneId = store_match_shipping_zone($countryCode, $regionCode);
    if ($zoneId < 1) {
        return array();
    }

    $weight = (float) $cartWeight;
    $subtotalMinor = store_money_to_minor($itemsSubtotal);
    $result = DB_query("SELECT * FROM {$_TABLES['store_shipping_methods']} WHERE active=1 AND zone_id=$zoneId "
        . "ORDER BY sort_order ASC,id ASC", 1);
    if (DB_error()) {
        return array();
    }

    $methods = array();
    while ($row = DB_fetchArray($result)) {
        $minimumWeight = (float) $row['minimum_weight'];
        $maximumWeight = (float) $row['maximum_weight'];
        if ($minimumWeight > 0 && $weight < $minimumWeight) {
            continue;
        }
        if ($maximumWeight > 0 && $weight > $maximumWeight) {
            continue;
        }

        $priceMinor = store_money_to_minor($row['price']);
        if ($row['method_type'] === 'free' || $row['method_type'] === 'pickup') {
            $priceMinor = 0;
        } elseif ($row['method_type'] === 'free_above') {
            $threshold = store_money_to_minor($row['free_above']);
            if ($threshold > 0 && $subtotalMinor >= $threshold) {
                $priceMinor = 0;
            }
        } elseif ($row['method_type'] === 'weight') {
            $priceMinor += store_money_to_minor(((float) $row['weight_rate']) * $weight);
        }

        $row['calculated_price'] = store_money_from_minor($priceMinor);
        $methods[] = $row;
    }

    return $methods;
}

/**
 * Fetch one shipping method and verify that it is eligible for the context.
 *
 * @param int    $methodId
 * @param string $countryCode
 * @param string $regionCode
 * @param mixed  $cartWeight
 * @param mixed  $itemsSubtotal
 * @return array|false
 */
function store_get_eligible_shipping_method($methodId, $countryCode, $regionCode, $cartWeight, $itemsSubtotal)
{
    $methodId = (int) $methodId;
    foreach (store_get_shipping_methods($countryCode, $regionCode, $cartWeight, $itemsSubtotal) as $method) {
        if ((int) $method['id'] === $methodId) {
            return $method;
        }
    }
    return false;
}

/**
 * Calculate unified Store totals.
 *
 * Input items must contain `product` and `quantity`. Product `price` is treated
 * according to the merchant's `prices_include_tax` setting. The returned item
 * rows are immutable snapshots suitable for order persistence.
 *
 * Context keys:
 * - country_code
 * - region_code
 * - shipping_method_id
 * - channel (`online`, `pos`, ...)
 *
 * @param array $items
 * @param array $context
 * @return array
 */
function store_calculate_totals($items, $context = array())
{
    global $_STORE_CONF;

    $countryCode = isset($context['country_code']) ? store_country_code($context['country_code']) : '';
    $regionCode = isset($context['region_code']) ? strtoupper(trim((string) $context['region_code'])) : '';
    $shippingMethodId = isset($context['shipping_method_id']) ? (int) $context['shipping_method_id'] : 0;
    $pricesIncludeTax = !empty($_STORE_CONF['prices_include_tax']);
    $taxEnabled = !isset($_STORE_CONF['tax_enabled']) || (int) $_STORE_CONF['tax_enabled'] === 1;

    $result = array(
        'success' => true,
        'error' => '',
        'currency' => '',
        'items' => array(),
        'items_subtotal' => store_money_from_minor(0),
        'items_tax' => store_money_from_minor(0),
        'discount_total' => store_money_from_minor(0),
        'shipping_subtotal' => store_money_from_minor(0),
        'shipping_tax' => store_money_from_minor(0),
        'tax_total' => store_money_from_minor(0),
        'grand_total' => store_money_from_minor(0),
        'requires_shipping' => false,
        'shipping_method' => false,
        'weight' => 0.0,
        'prices_include_tax' => $pricesIncludeTax ? 1 : 0
    );

    $itemsNetMinor = 0;
    $itemsTaxMinor = 0;
    $itemsGrossMinor = 0;
    $cartWeight = 0.0;

    foreach ($items as $item) {
        if (empty($item['product']) || !is_array($item['product'])) {
            continue;
        }
        $product = $item['product'];
        $quantity = isset($item['quantity']) ? max(1, (int) $item['quantity']) : 1;

        if ($result['currency'] === '') {
            $result['currency'] = isset($product['currency']) ? $product['currency'] : '';
        } elseif (isset($product['currency']) && $result['currency'] !== $product['currency']) {
            $result['success'] = false;
            $result['error'] = 'currency';
            return $result;
        }

        $unitMinor = store_money_to_minor(isset($product['price']) ? $product['price'] : 0);
        $lineBaseMinor = $unitMinor * $quantity;
        $taxClassId = isset($product['tax_class_id']) ? (int) $product['tax_class_id'] : 0;
        $rates = ($taxEnabled && $countryCode !== '') ? store_get_tax_rates($taxClassId, $countryCode, $regionCode) : array();
        $tax = store_calculate_tax_minor($lineBaseMinor, $rates, $pricesIncludeTax);

        $itemsNetMinor += $tax['net'];
        $itemsTaxMinor += $tax['tax'];
        $itemsGrossMinor += $tax['gross'];

        $physical = !isset($product['product_type']) || $product['product_type'] !== 'digital';
        if ($physical) {
            $result['requires_shipping'] = true;
            $weight = isset($product['weight']) ? max(0, (float) $product['weight']) : 0.0;
            $cartWeight += $weight * $quantity;
        }

        $result['items'][] = array(
            'product' => $product,
            'quantity' => $quantity,
            'unit_price' => store_money_from_minor($unitMinor),
            'line_subtotal' => store_money_from_minor($tax['net']),
            'tax_class_id' => $taxClassId,
            'tax_label' => $tax['label'],
            'tax_rate' => $tax['rate'],
            'tax_total' => store_money_from_minor($tax['tax']),
            'line_total' => store_money_from_minor($tax['gross'])
        );
    }

    $result['weight'] = $cartWeight;
    $result['items_subtotal'] = store_money_from_minor($itemsNetMinor);
    $result['items_tax'] = store_money_from_minor($itemsTaxMinor);

    $shippingNetMinor = 0;
    $shippingTaxMinor = 0;
    if ($result['requires_shipping'] && $shippingMethodId > 0) {
        $method = store_get_eligible_shipping_method(
            $shippingMethodId,
            $countryCode,
            $regionCode,
            $cartWeight,
            store_money_from_minor($itemsNetMinor)
        );
        if (!$method) {
            $result['success'] = false;
            $result['error'] = 'shipping';
            return $result;
        }

        $shippingBaseMinor = store_money_to_minor($method['calculated_price']);
        $shippingTaxClass = isset($method['tax_class_id']) ? (int) $method['tax_class_id'] : 0;
        $shippingRates = ($taxEnabled && $countryCode !== '')
            ? store_get_tax_rates($shippingTaxClass, $countryCode, $regionCode) : array();
        $shippingTax = store_calculate_tax_minor($shippingBaseMinor, $shippingRates, $pricesIncludeTax);
        $shippingNetMinor = $shippingTax['net'];
        $shippingTaxMinor = $shippingTax['tax'];
        $result['shipping_method'] = $method;
    }

    // 0.8.0 promotion engine will populate this while preserving the same API.
    $discountMinor = 0;
    $taxTotalMinor = $itemsTaxMinor + $shippingTaxMinor;
    $grandMinor = $itemsGrossMinor + $shippingNetMinor + $shippingTaxMinor - $discountMinor;

    $result['discount_total'] = store_money_from_minor($discountMinor);
    $result['shipping_subtotal'] = store_money_from_minor($shippingNetMinor);
    $result['shipping_tax'] = store_money_from_minor($shippingTaxMinor);
    $result['tax_total'] = store_money_from_minor($taxTotalMinor);
    $result['grand_total'] = store_money_from_minor($grandMinor);

    return $result;
}
