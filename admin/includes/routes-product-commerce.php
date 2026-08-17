<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-product-commerce.php                                              |
// |                                                                           |
// | Product-level tax class, weight and dimensions administration.           |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if (!isset($action)) {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
}

if ($action === 'save_product_commerce' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_admin_redirect($LANG_STORE['invalid_token']);
    }

    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    $taxClassId = isset($_POST['tax_class_id']) ? max(0, (int) $_POST['tax_class_id']) : 0;
    $weight = number_format(max(0, isset($_POST['weight']) ? (float) $_POST['weight'] : 0), 4, '.', '');
    $length = number_format(max(0, isset($_POST['length']) ? (float) $_POST['length'] : 0), 4, '.', '');
    $width = number_format(max(0, isset($_POST['width']) ? (float) $_POST['width'] : 0), 4, '.', '');
    $height = number_format(max(0, isset($_POST['height']) ? (float) $_POST['height'] : 0), 4, '.', '');

    if ($productId > 0) {
        DB_query("UPDATE {$_TABLES['store_products']} SET tax_class_id=$taxClassId,weight='$weight',length='$length',"
            . "width='$width',height='$height',modified=NOW() WHERE id=$productId", 1);
    }

    header('Location: ' . $_CONF['site_admin_url'] . '/plugins/store/index.php?action=product_commerce&id=' . $productId
        . '&notice=' . rawurlencode($LANG_STORE_COMMERCE['saved']));
    exit;
}

if ($action === 'product_commerce') {
    $productId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $product = store_get_product($productId);
    if (!$product) {
        COM_output(COM_createHTMLDocument(COM_showMessageText($LANG_STORE['product_not_found'], $LANG_STORE['admin_title'])));
        exit;
    }

    $notice = isset($_GET['notice']) ? (string) $_GET['notice'] : '';
    $weightUnit = isset($_STORE_CONF['weight_unit']) ? $_STORE_CONF['weight_unit'] : 'kg';
    $dimensionUnit = isset($_STORE_CONF['dimension_unit']) ? $_STORE_CONF['dimension_unit'] : 'cm';

    $content = '<div class="store-admin-editor">';
    $content .= '<div class="store-admin-editor-head"><div><h1>' . store_escape($product['name']) . '</h1><p class="store-meta">'
        . store_escape($LANG_STORE_COMMERCE['product_tax_shipping']) . '</p></div><div class="store-editor-actions">'
        . '<a class="store-secondary-button" href="index.php?action=commerce">← ' . store_escape($LANG_STORE_COMMERCE['commerce']) . '</a>'
        . '<a class="store-secondary-button" href="index.php?action=edit&id=' . $productId . '">' . store_escape($LANG_STORE['edit_product']) . '</a></div></div>';
    if ($notice !== '') {
        $content .= COM_showMessageText($notice, $LANG_STORE['admin_title']);
    }
    $content .= '<section class="store-admin-panel"><form class="store-form" method="post">'
        . '<input type="hidden" name="action" value="save_product_commerce">'
        . '<input type="hidden" name="product_id" value="' . $productId . '">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
        . '<label>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</label>'
        . store_commerce_tax_class_select(isset($product['tax_class_id']) ? $product['tax_class_id'] : 0, 'tax_class_id')
        . '<div id="store-product-shipping-fields">'
        . '<label>' . store_escape($LANG_STORE_COMMERCE['weight']) . ' (' . store_escape($weightUnit) . ')</label>'
        . '<input type="number" min="0" step="0.0001" name="weight" value="' . store_escape(isset($product['weight']) ? $product['weight'] : '0.0000') . '">'
        . '<p class="store-meta">' . store_escape($LANG_STORE_COMMERCE['physical_shipping_help']) . '</p>'
        . '<h3>' . store_escape($LANG_STORE_COMMERCE['dimensions']) . ' (' . store_escape($dimensionUnit) . ')</h3>'
        . '<div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE_COMMERCE['length']) . '</label><input type="number" min="0" step="0.0001" name="length" value="' . store_escape(isset($product['length']) ? $product['length'] : '0.0000') . '"></div>'
        . '<div><label>' . store_escape($LANG_STORE_COMMERCE['width']) . '</label><input type="number" min="0" step="0.0001" name="width" value="' . store_escape(isset($product['width']) ? $product['width'] : '0.0000') . '"></div></div>'
        . '<label>' . store_escape($LANG_STORE_COMMERCE['height']) . '</label><input type="number" min="0" step="0.0001" name="height" value="' . store_escape(isset($product['height']) ? $product['height'] : '0.0000') . '">'
        . '</div><button class="store-primary-button" type="submit">' . store_escape($LANG_STORE['save']) . '</button></form></section></div>';

    if (isset($product['product_type']) && $product['product_type'] === 'digital') {
        $content .= '<script>(function(){var el=document.getElementById("store-product-shipping-fields");if(el){el.style.opacity="0.55";}})();</script>';
    }

    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE_COMMERCE['product_tax_shipping'])));
    exit;
}
