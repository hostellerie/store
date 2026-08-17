from pathlib import Path


def replace_once(path, old, new):
    p = Path(path)
    text = p.read_text(encoding='utf-8')
    if old not in text:
        raise SystemExit('Expected target not found in %s:\n%s' % (path, old[:180]))
    p.write_text(text.replace(old, new, 1), encoding='utf-8')


# Fresh-install configuration: clearer tabs plus the public block-column layout.
replace_once(
    'install_defaults.php',
    "$_STORE_DEFAULT['dimension_unit'] = 'cm';\n",
    "$_STORE_DEFAULT['dimension_unit'] = 'cm';\n$_STORE_DEFAULT['layout_blocks'] = 'none';\n"
)

old_config = """        $c->add('tab_main', null, 'tab', 0, 0, null, 0, true, 'store', 0);
        $c->add('fs_main', null, 'fieldset', 0, 0, null, 0, true, 'store', 0);
        $c->add('hide_menu', $_STORE_DEFAULT['hide_menu'], 'select', 0, 0, 0, 10, true, 'store', 0);
        $c->add('currency', $_STORE_DEFAULT['currency'], 'text', 0, 0, 0, 20, true, 'store', 0);
        $c->add('currency_decimals', $_STORE_DEFAULT['currency_decimals'], 'text', 0, 0, 0, 25, true, 'store', 0);
        $c->add('products_per_page', $_STORE_DEFAULT['products_per_page'], 'text', 0, 0, 0, 30, true, 'store', 0);
        $c->add('show_stock', $_STORE_DEFAULT['show_stock'], 'select', 0, 0, 0, 40, true, 'store', 0);
        $c->add('manual_payment_enabled', $_STORE_DEFAULT['manual_payment_enabled'], 'select', 0, 0, 0, 50, true, 'store', 0);
        $c->add('manual_payment_label', $_STORE_DEFAULT['manual_payment_label'], 'text', 0, 0, 0, 60, true, 'store', 0);
        $c->add('manual_payment_instructions', $_STORE_DEFAULT['manual_payment_instructions'], 'text', 0, 0, 0, 70, true, 'store', 0);

        $c->add('fs_commerce', null, 'fieldset', 0, 0, null, 100, true, 'store', 0);
        $c->add('tax_enabled', $_STORE_DEFAULT['tax_enabled'], 'select', 0, 0, 0, 110, true, 'store', 0);
        $c->add('prices_include_tax', $_STORE_DEFAULT['prices_include_tax'], 'select', 0, 0, 0, 120, true, 'store', 0);
        $c->add('tax_basis', $_STORE_DEFAULT['tax_basis'], 'text', 0, 0, 0, 130, true, 'store', 0);
        $c->add('tax_rounding', $_STORE_DEFAULT['tax_rounding'], 'text', 0, 0, 0, 140, true, 'store', 0);
        $c->add('shipping_enabled', $_STORE_DEFAULT['shipping_enabled'], 'select', 0, 0, 0, 150, true, 'store', 0);
        $c->add('default_country_code', $_STORE_DEFAULT['default_country_code'], 'text', 0, 0, 0, 160, true, 'store', 0);
        $c->add('weight_unit', $_STORE_DEFAULT['weight_unit'], 'text', 0, 0, 0, 170, true, 'store', 0);
        $c->add('dimension_unit', $_STORE_DEFAULT['dimension_unit'], 'text', 0, 0, 0, 180, true, 'store', 0);
"""
new_config = """        $c->add('tab_main', null, 'tab', 0, 0, null, 0, true, 'store', 0);
        $c->add('tab_catalog', null, 'tab', 0, 0, null, 10, true, 'store', 1);
        $c->add('tab_checkout', null, 'tab', 0, 0, null, 20, true, 'store', 2);
        $c->add('tab_commerce', null, 'tab', 0, 0, null, 30, true, 'store', 3);
        $c->add('tab_display', null, 'tab', 0, 0, null, 40, true, 'store', 4);

        $c->add('fs_main', null, 'fieldset', 0, 0, null, 0, true, 'store', 0);
        $c->add('hide_menu', $_STORE_DEFAULT['hide_menu'], 'select', 0, 0, 0, 10, true, 'store', 0);

        $c->add('fs_catalog', null, 'fieldset', 0, 0, null, 0, true, 'store', 1);
        $c->add('currency', $_STORE_DEFAULT['currency'], 'text', 0, 0, 0, 10, true, 'store', 1);
        $c->add('currency_decimals', $_STORE_DEFAULT['currency_decimals'], 'text', 0, 0, 0, 20, true, 'store', 1);
        $c->add('products_per_page', $_STORE_DEFAULT['products_per_page'], 'text', 0, 0, 0, 30, true, 'store', 1);
        $c->add('show_stock', $_STORE_DEFAULT['show_stock'], 'select', 0, 0, 0, 40, true, 'store', 1);
        $c->add('weight_unit', $_STORE_DEFAULT['weight_unit'], 'text', 0, 0, 0, 50, true, 'store', 1);
        $c->add('dimension_unit', $_STORE_DEFAULT['dimension_unit'], 'text', 0, 0, 0, 60, true, 'store', 1);

        $c->add('fs_checkout', null, 'fieldset', 0, 0, null, 0, true, 'store', 2);
        $c->add('manual_payment_enabled', $_STORE_DEFAULT['manual_payment_enabled'], 'select', 0, 0, 0, 10, true, 'store', 2);
        $c->add('manual_payment_label', $_STORE_DEFAULT['manual_payment_label'], 'text', 0, 0, 0, 20, true, 'store', 2);
        $c->add('manual_payment_instructions', $_STORE_DEFAULT['manual_payment_instructions'], 'text', 0, 0, 0, 30, true, 'store', 2);
        $c->add('default_country_code', $_STORE_DEFAULT['default_country_code'], 'text', 0, 0, 0, 40, true, 'store', 2);

        $c->add('fs_commerce', null, 'fieldset', 0, 0, null, 0, true, 'store', 3);
        $c->add('tax_enabled', $_STORE_DEFAULT['tax_enabled'], 'select', 0, 0, 0, 10, true, 'store', 3);
        $c->add('prices_include_tax', $_STORE_DEFAULT['prices_include_tax'], 'select', 0, 0, 0, 20, true, 'store', 3);
        $c->add('tax_basis', $_STORE_DEFAULT['tax_basis'], 'text', 0, 0, 0, 30, true, 'store', 3);
        $c->add('tax_rounding', $_STORE_DEFAULT['tax_rounding'], 'text', 0, 0, 0, 40, true, 'store', 3);
        $c->add('shipping_enabled', $_STORE_DEFAULT['shipping_enabled'], 'select', 0, 0, 0, 50, true, 'store', 3);

        $c->add('fs_display', null, 'fieldset', 0, 0, null, 0, true, 'store', 4);
        $c->add('layout_blocks', $_STORE_DEFAULT['layout_blocks'], 'select', 0, 0, 1, 10, true, 'store', 4);
"""
replace_once('install_defaults.php', old_config, new_config)

# Existing alpha installs get the same organization automatically once.
replace_once(
    'functions.inc',
    "$_STORE_CONF = $store_config->get_config('store');\n\n// Store 0.7.0 centralizes monetary, tax and shipping calculations here.\n",
    "$_STORE_CONF = $store_config->get_config('store');\n\nif (!is_array($_STORE_CONF) || !isset($_STORE_CONF['layout_blocks'])) {\n    require_once $store_plugin_path . 'includes/config071.php';\n    if (!store_config070_ui_ensure()) {\n        COM_errorLog('Store Plugin: failed to update configuration tabs/layout settings.');\n    }\n    $_STORE_CONF = $store_config->get_config('store');\n}\n\n// Store 0.7.0 centralizes monetary, tax and shipping calculations here.\n"
)

layout_helper = r'''
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

'''
replace_once('functions.inc', 'function store_escape($value)\n{', layout_helper + 'function store_escape($value)\n{')

# Add permissions for the new configuration tabs on fresh installs.
old_features = """            'store.admin' => 'Full access to Store administration',
            'config.store.tab_main' => 'Access to Store configuration'
"""
new_features = """            'store.admin' => 'Full access to Store administration',
            'config.store.tab_main' => 'Access to Store configuration',
            'config.store.tab_catalog' => 'Access to Store catalogue configuration',
            'config.store.tab_checkout' => 'Access to Store checkout configuration',
            'config.store.tab_commerce' => 'Access to Store tax and shipping configuration',
            'config.store.tab_display' => 'Access to Store display configuration'
"""
replace_once('autoinstall.php', old_features, new_features)
old_mappings = """            'store.admin' => array($pi_admin),
            'config.store.tab_main' => array($pi_admin)
"""
new_mappings = """            'store.admin' => array($pi_admin),
            'config.store.tab_main' => array($pi_admin),
            'config.store.tab_catalog' => array($pi_admin),
            'config.store.tab_checkout' => array($pi_admin),
            'config.store.tab_commerce' => array($pi_admin),
            'config.store.tab_display' => array($pi_admin)
"""
replace_once('autoinstall.php', old_mappings, new_mappings)

# Complete the configuration labels in all primary language files.
def patch_language(path, french=False):
    p = Path(path)
    text = p.read_text(encoding='utf-8')
    start = text.index("$LANG_confignames['store'] = array(")
    end = text.index("\n\n$LANG_STORE = array(", start)
    if french:
        block = r'''$LANG_confignames['store'] = array(
    'hide_menu' => 'Masquer Store dans le menu principal',
    'currency' => 'Devise par défaut (code ISO 4217)',
    'currency_decimals' => 'Nombre de décimales pour les montants',
    'products_per_page' => 'Produits par page',
    'show_stock' => 'Afficher le stock sur les pages publiques',
    'manual_payment_enabled' => 'Activer le paiement manuel / virement',
    'manual_payment_label' => 'Libellé du moyen de paiement manuel',
    'manual_payment_instructions' => 'Instructions de paiement manuel affichées après la commande',
    'tax_enabled' => 'Activer le calcul des taxes',
    'prices_include_tax' => 'Les prix du catalogue incluent les taxes',
    'tax_basis' => 'Base de calcul des taxes',
    'tax_rounding' => 'Méthode d’arrondi des taxes',
    'shipping_enabled' => 'Activer les méthodes de livraison',
    'default_country_code' => 'Code pays par défaut (ISO 3166-1 alpha-2)',
    'weight_unit' => 'Unité de poids',
    'dimension_unit' => 'Unité de dimensions',
    'layout_blocks' => 'Colonnes Geeklog affichées sur les pages Store'
);
$LANG_configsubgroups['store'] = array('sg_main' => 'Paramètres Store');
$LANG_fs['store'] = array(
    'fs_main' => 'Paramètres généraux',
    'fs_catalog' => 'Catalogue',
    'fs_checkout' => 'Commande et paiement',
    'fs_commerce' => 'Taxes et livraison',
    'fs_display' => 'Affichage public'
);
$LANG_tab['store'] = array(
    'tab_main' => 'Général',
    'tab_catalog' => 'Catalogue',
    'tab_checkout' => 'Commande et paiement',
    'tab_commerce' => 'Taxes et livraison',
    'tab_display' => 'Affichage'
);
$LANG_configselects['store'] = array(
    0 => array('Oui' => 1, 'Non' => 0),
    1 => array(
        'Aucune colonne (pleine largeur)' => 'none',
        'Colonne gauche uniquement' => 'left',
        'Colonne droite uniquement' => 'right',
        'Colonnes gauche et droite' => 'both'
    )
);'''
    else:
        block = r'''$LANG_confignames['store'] = array(
    'hide_menu' => 'Hide Store from the main menu',
    'currency' => 'Default currency (ISO 4217 code)',
    'currency_decimals' => 'Number of decimal places for monetary amounts',
    'products_per_page' => 'Products per page',
    'show_stock' => 'Display stock on public pages',
    'manual_payment_enabled' => 'Enable manual / bank-transfer payment',
    'manual_payment_label' => 'Manual payment method label',
    'manual_payment_instructions' => 'Manual payment instructions shown after checkout',
    'tax_enabled' => 'Enable tax calculation',
    'prices_include_tax' => 'Catalogue prices include tax',
    'tax_basis' => 'Tax calculation basis',
    'tax_rounding' => 'Tax rounding method',
    'shipping_enabled' => 'Enable shipping methods',
    'default_country_code' => 'Default country code (ISO 3166-1 alpha-2)',
    'weight_unit' => 'Weight unit',
    'dimension_unit' => 'Dimension unit',
    'layout_blocks' => 'Geeklog columns displayed on Store pages'
);
$LANG_configsubgroups['store'] = array('sg_main' => 'Store Settings');
$LANG_fs['store'] = array(
    'fs_main' => 'General settings',
    'fs_catalog' => 'Catalogue',
    'fs_checkout' => 'Checkout and payment',
    'fs_commerce' => 'Tax and shipping',
    'fs_display' => 'Public display'
);
$LANG_tab['store'] = array(
    'tab_main' => 'General',
    'tab_catalog' => 'Catalogue',
    'tab_checkout' => 'Checkout and payment',
    'tab_commerce' => 'Tax and shipping',
    'tab_display' => 'Display'
);
$LANG_configselects['store'] = array(
    0 => array('Yes' => 1, 'No' => 0),
    1 => array(
        'No columns (full width)' => 'none',
        'Left column only' => 'left',
        'Right column only' => 'right',
        'Left and right columns' => 'both'
    )
);'''
    text = text[:start] + block + text[end:]
    additions = (
        "    'weight' => 'Poids',\n    'dimensions' => 'Dimensions',\n    'physical_details' => 'Caractéristiques physiques',\n"
        if french else
        "    'weight' => 'Weight',\n    'dimensions' => 'Dimensions',\n    'physical_details' => 'Physical details',\n"
    )
    marker = "    'stock' => 'Stock',\n"
    if marker not in text:
        raise SystemExit('Language insertion marker missing in ' + path)
    text = text.replace(marker, marker + additions, 1)
    p.write_text(text, encoding='utf-8')

patch_language('language/english.php', False)
patch_language('language/french.php', True)
patch_language('language/french_france.php', True)

# Apply the selected Geeklog block layout to every public Store response.
for path in ('public_html/index.php', 'includes/public-order070.php'):
    p = Path(path)
    text = p.read_text(encoding='utf-8')
    text = text.replace("array('pagetitle' => $LANG_STORE['checkout'])", "store_document_options($LANG_STORE['checkout'])")
    text = text.replace("array('pagetitle' => $LANG_STORE['catalog_title'])", "store_document_options($LANG_STORE['catalog_title'])")
    text = text.replace("array('pagetitle' => $LANG_STORE['order'])", "store_document_options($LANG_STORE['order'])")
    p.write_text(text, encoding='utf-8')

# Show weight and dimensions for physical products when values exist.
product_price = """        $content .= '<p class=\"store-product-price\">'
            . store_format_price($product['price'], $product['currency']) . '</p>';

"""
product_specs = product_price + r'''        if (store_product_uses_stock($product)) {
            $weight = isset($product['weight']) ? (float) $product['weight'] : 0;
            $length = isset($product['length']) ? (float) $product['length'] : 0;
            $width = isset($product['width']) ? (float) $product['width'] : 0;
            $height = isset($product['height']) ? (float) $product['height'] : 0;
            $weightUnit = isset($_STORE_CONF['weight_unit']) ? $_STORE_CONF['weight_unit'] : 'kg';
            $dimensionUnit = isset($_STORE_CONF['dimension_unit']) ? $_STORE_CONF['dimension_unit'] : 'cm';
            if ($weight > 0 || $length > 0 || $width > 0 || $height > 0) {
                $content .= '<div class="store-meta store-product-measurements"><strong>'
                    . store_escape($LANG_STORE['physical_details']) . '</strong>';
                if ($weight > 0) {
                    $content .= '<br>' . store_escape($LANG_STORE['weight']) . ': '
                        . store_escape(store_format_measurement($weight) . ' ' . $weightUnit);
                }
                if ($length > 0 || $width > 0 || $height > 0) {
                    $dimensions = array(
                        store_format_measurement($length),
                        store_format_measurement($width),
                        store_format_measurement($height)
                    );
                    $content .= '<br>' . store_escape($LANG_STORE['dimensions']) . ': '
                        . store_escape(implode(' × ', $dimensions) . ' ' . $dimensionUnit);
                }
                $content .= '</div>';
            }
        }

'''
replace_once('public_html/index.php', product_price, product_specs)

print('Store 0.7.0 alpha UI feedback patch applied.')
