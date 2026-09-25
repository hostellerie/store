<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | commerce070_test.php                                                     |
// |                                                                           |
// | Minimal dependency-free regression tests for Store monetary tax logic.   |
// +---------------------------------------------------------------------------+

$_TABLES = array();
$_DB_table_prefix = 'gl_';
$_STORE_CONF = array('currency_decimals' => 2);

require_once dirname(__FILE__) . '/../includes/commerce.php';

function store_test_assert_equal($expected, $actual, $label)
{
    if ((string) $expected !== (string) $actual) {
        fwrite(STDERR, $label . ': expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$standard20 = array(
    array('name' => 'Tax 20', 'rate' => '20.0000', 'compound' => 0)
);

$exclusive = store_calculate_tax_minor(10000, $standard20, false);
store_test_assert_equal(10000, $exclusive['net'], 'exclusive net');
store_test_assert_equal(2000, $exclusive['tax'], 'exclusive tax');
store_test_assert_equal(12000, $exclusive['gross'], 'exclusive gross');
store_test_assert_equal('20.0000', $exclusive['rate'], 'exclusive effective rate');

$inclusive = store_calculate_tax_minor(12000, $standard20, true);
store_test_assert_equal(10000, $inclusive['net'], 'inclusive net');
store_test_assert_equal(2000, $inclusive['tax'], 'inclusive tax');
store_test_assert_equal(12000, $inclusive['gross'], 'inclusive gross');

$compound = array(
    array('name' => 'Tax A', 'rate' => '10.0000', 'compound' => 0),
    array('name' => 'Tax B', 'rate' => '10.0000', 'compound' => 1)
);

$compoundExclusive = store_calculate_tax_minor(10000, $compound, false);
store_test_assert_equal(10000, $compoundExclusive['net'], 'compound exclusive net');
store_test_assert_equal(2100, $compoundExclusive['tax'], 'compound exclusive tax');
store_test_assert_equal(12100, $compoundExclusive['gross'], 'compound exclusive gross');
store_test_assert_equal('21.0000', $compoundExclusive['rate'], 'compound effective rate');

$compoundInclusive = store_calculate_tax_minor(12100, $compound, true);
store_test_assert_equal(10000, $compoundInclusive['net'], 'compound inclusive net');
store_test_assert_equal(2100, $compoundInclusive['tax'], 'compound inclusive tax');
store_test_assert_equal(12100, $compoundInclusive['gross'], 'compound inclusive gross');

$zero = store_calculate_tax_minor(1234, array(), false);
store_test_assert_equal(1234, $zero['net'], 'zero-rate net');
store_test_assert_equal(0, $zero['tax'], 'zero-rate tax');
store_test_assert_equal(1234, $zero['gross'], 'zero-rate gross');

echo "Store 0.7.0 commerce calculator tests passed." . PHP_EOL;
