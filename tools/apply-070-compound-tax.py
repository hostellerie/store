from pathlib import Path

path = Path('includes/commerce.php')
text = path.read_text(encoding='utf-8')

old = r'''function store_calculate_tax_minor($baseMinor, $rates, $pricesIncludeTax)
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
'''

new = r'''function store_calculate_tax_minor($baseMinor, $rates, $pricesIncludeTax)
{
    $baseMinor = (int) $baseMinor;
    if ($baseMinor <= 0 || !$rates) {
        return array('net' => $baseMinor, 'tax' => 0, 'gross' => $baseMinor, 'label' => '', 'rate' => '0.0000');
    }

    $labels = array();
    $normalizedRates = array();
    foreach ($rates as $rate) {
        $rateValue = isset($rate['rate']) ? (float) $rate['rate'] : 0.0;
        if ($rateValue <= 0) {
            continue;
        }
        $normalizedRates[] = array(
            'rate' => $rateValue,
            'compound' => !empty($rate['compound'])
        );
        if (!empty($rate['name'])) {
            $labels[] = $rate['name'];
        }
    }

    if (!$normalizedRates) {
        return array('net' => $baseMinor, 'tax' => 0, 'gross' => $baseMinor, 'label' => implode(' + ', $labels), 'rate' => '0.0000');
    }

    // Build the effective multiplier first. Non-compound rates always apply to
    // the net base; compound rates apply to the net base plus taxes accumulated
    // before them in priority order.
    $effectiveTaxFactor = 0.0;
    foreach ($normalizedRates as $rate) {
        $rateFactor = $rate['rate'] / 100;
        $taxableFactor = $rate['compound'] ? (1.0 + $effectiveTaxFactor) : 1.0;
        $effectiveTaxFactor += $taxableFactor * $rateFactor;
    }

    if ($pricesIncludeTax) {
        // Reverse the configured tax chain from a gross catalogue price.
        $net = (int) round($baseMinor / (1.0 + $effectiveTaxFactor));
        $tax = $baseMinor - $net;
        $gross = $baseMinor;
    } else {
        // Apply each rate in priority order and round each tax component to the
        // configured currency precision. This is Store's 0.7.0 line-rounding
        // policy; subtotal rounding remains reserved for a later refinement.
        $net = $baseMinor;
        $tax = 0;
        foreach ($normalizedRates as $rate) {
            $taxableMinor = $rate['compound'] ? ($net + $tax) : $net;
            $tax += (int) round($taxableMinor * ($rate['rate'] / 100));
        }
        $gross = $net + $tax;
    }

    return array(
        'net' => $net,
        'tax' => $tax,
        'gross' => $gross,
        'label' => implode(' + ', $labels),
        'rate' => number_format($effectiveTaxFactor * 100, 4, '.', '')
    );
}
'''

if old not in text:
    raise SystemExit('Expected store_calculate_tax_minor() implementation not found')

path.write_text(text.replace(old, new, 1), encoding='utf-8')
