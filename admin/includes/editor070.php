<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | editor070.php                                                            |
// |                                                                           |
// | 0.7.0 product editor integration for tax and logistics fields.           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

function store_admin_editor070($product, $message)
{
    global $_STORE_CONF, $LANG_STORE_COMMERCE;

    $html = store_admin_editor($product, $message);
    $id = isset($product['id']) ? (int) $product['id'] : 0;
    $weightUnit = isset($_STORE_CONF['weight_unit']) ? $_STORE_CONF['weight_unit'] : 'kg';
    $dimensionUnit = isset($_STORE_CONF['dimension_unit']) ? $_STORE_CONF['dimension_unit'] : 'cm';

    $panel = '<section class="store-admin-panel store-product-commerce-panel">'
        . '<h2>' . store_escape($LANG_STORE_COMMERCE['product_tax_shipping']) . '</h2>'
        . '<p class="store-meta">' . store_escape($LANG_STORE_COMMERCE['tax_class_help']) . '</p>'
        . '<label>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</label>'
        . store_commerce_tax_class_select(isset($product['tax_class_id']) ? $product['tax_class_id'] : 0, 'tax_class_id')
        . '<div id="store-product-logistics-fields">'
        . '<p class="store-meta">' . store_escape($LANG_STORE_COMMERCE['physical_shipping_help']) . '</p>'
        . '<div class="store-checkout-row">'
        . '<div><label>' . store_escape($LANG_STORE_COMMERCE['weight']) . ' (' . store_escape($weightUnit) . ')</label>'
        . '<input type="number" min="0" step="0.0001" name="weight" value="'
        . store_escape(isset($product['weight']) ? $product['weight'] : '0.0000') . '"></div>'
        . '<div><label>' . store_escape($LANG_STORE_COMMERCE['length']) . ' (' . store_escape($dimensionUnit) . ')</label>'
        . '<input type="number" min="0" step="0.0001" name="length" value="'
        . store_escape(isset($product['length']) ? $product['length'] : '0.0000') . '"></div></div>'
        . '<div class="store-checkout-row">'
        . '<div><label>' . store_escape($LANG_STORE_COMMERCE['width']) . ' (' . store_escape($dimensionUnit) . ')</label>'
        . '<input type="number" min="0" step="0.0001" name="width" value="'
        . store_escape(isset($product['width']) ? $product['width'] : '0.0000') . '"></div>'
        . '<div><label>' . store_escape($LANG_STORE_COMMERCE['height']) . ' (' . store_escape($dimensionUnit) . ')</label>'
        . '<input type="number" min="0" step="0.0001" name="height" value="'
        . store_escape(isset($product['height']) ? $product['height'] : '0.0000') . '"></div></div>'
        . '</div>';

    if ($id > 0) {
        $panel .= '<p class="store-meta"><a href="?action=product_commerce&id=' . $id . '">'
            . store_escape($LANG_STORE_COMMERCE['advanced_commerce_settings']) . '</a></p>';
    }
    $panel .= '</section>';

    $marker = '<section class="store-admin-panel store-primary-image-panel">';
    if (strpos($html, $marker) !== false) {
        $html = str_replace($marker, $panel . $marker, $html);
    }

    $html .= '<script>(function(){var type=document.getElementById("store-product-type");'
        . 'var fields=document.getElementById("store-product-logistics-fields");'
        . 'function sync(){if(!type||!fields){return;}fields.style.display=type.value==="digital"?"none":"block";}'
        . 'if(type){type.addEventListener("change",sync);}sync();})();</script>';

    return $html;
}
