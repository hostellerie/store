<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-editor070.php                                                     |
// |                                                                           |
// | Main product editor routes with 0.7.0 tax and logistics persistence.     |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if (!isset($action)) {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
}

if ($action === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        $message = $LANG_STORE['invalid_token'];
    } else {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = trim(isset($_POST['name']) ? (string) $_POST['name'] : '');
        $slug = store_slugify(isset($_POST['slug']) ? $_POST['slug'] : '');
        if ($slug === '') {
            $slug = store_slugify($name);
        }
        if ($slug === '') {
            $slug = 'product-' . time();
        }

        $sku = trim(isset($_POST['sku']) ? (string) $_POST['sku'] : '');
        $categoryId = isset($_POST['category_id']) ? max(0, (int) $_POST['category_id']) : 0;
        $short = trim(isset($_POST['short_description']) ? (string) $_POST['short_description'] : '');
        $description = trim(isset($_POST['description']) ? (string) $_POST['description'] : '');
        $imageUrl = store_valid_image_url(isset($_POST['image_url']) ? $_POST['image_url'] : '');
        $price = max(0, isset($_POST['price']) ? (float) $_POST['price'] : 0);
        $currency = strtoupper(trim(isset($_POST['currency']) ? (string) $_POST['currency'] : 'EUR'));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            $currency = isset($_STORE_CONF['currency']) ? strtoupper((string) $_STORE_CONF['currency']) : 'EUR';
        }
        $stock = max(0, isset($_POST['stock']) ? (int) $_POST['stock'] : 0);
        $type = (isset($_POST['product_type']) && $_POST['product_type'] === 'digital') ? 'digital' : 'physical';
        $active = isset($_POST['active']) ? 1 : 0;

        $taxClassId = isset($_POST['tax_class_id']) ? max(0, (int) $_POST['tax_class_id']) : 0;
        if ($taxClassId > 0 && !store_commerce_guard_exists('store_tax_classes', $taxClassId, true)) {
            $taxClassId = 0;
        }

        $weight = number_format(max(0, isset($_POST['weight']) ? (float) $_POST['weight'] : 0), 4, '.', '');
        $length = number_format(max(0, isset($_POST['length']) ? (float) $_POST['length'] : 0), 4, '.', '');
        $width = number_format(max(0, isset($_POST['width']) ? (float) $_POST['width'] : 0), 4, '.', '');
        $height = number_format(max(0, isset($_POST['height']) ? (float) $_POST['height'] : 0), 4, '.', '');
        if ($type === 'digital') {
            $weight = $length = $width = $height = '0.0000';
        }

        if ($categoryId > 0 && (int) DB_getItem($_TABLES['store_categories'], 'id', 'id = ' . $categoryId) !== $categoryId) {
            $categoryId = 0;
        }

        if ($name === '') {
            $message = $LANG_STORE['name_required'];
        } else {
            $slugDb = DB_escapeString($slug);
            $where = "slug = '$slugDb'" . ($id > 0 ? " AND id <> $id" : '');
            $duplicate = DB_query("SELECT id FROM {$_TABLES['store_products']} WHERE $where LIMIT 1");
            if (DB_numRows($duplicate) > 0) {
                $message = $LANG_STORE['slug_exists'];
            } else {
                $values = array_map('DB_escapeString', array(
                    $sku, $name, $short, $description, $imageUrl, $currency, $type
                ));
                list($skuDb, $nameDb, $shortDb, $descriptionDb, $imageDb, $currencyDb, $typeDb) = $values;
                $priceDb = number_format($price, 4, '.', '');

                if ($id > 0) {
                    DB_query("UPDATE {$_TABLES['store_products']} SET category_id=$categoryId,"
                        . "sku='$skuDb',slug='$slugDb',name='$nameDb',short_description='$shortDb',"
                        . "description='$descriptionDb',image_url='$imageDb',price='$priceDb',"
                        . "currency='$currencyDb',stock=$stock,product_type='$typeDb',active=$active,"
                        . "tax_class_id=$taxClassId,weight='$weight',length='$length',width='$width',height='$height',"
                        . "modified=NOW() WHERE id=$id", 1);
                } else {
                    DB_query("INSERT INTO {$_TABLES['store_products']} "
                        . "(category_id,sku,slug,name,short_description,description,image_url,price,currency,stock,"
                        . "product_type,active,tax_class_id,weight,length,width,height,created,modified) VALUES "
                        . "($categoryId,'$skuDb','$slugDb','$nameDb','$shortDb','$descriptionDb','$imageDb',"
                        . "'$priceDb','$currencyDb',$stock,'$typeDb',$active,$taxClassId,'$weight','$length','$width',"
                        . "'$height',NOW(),NOW())", 1);
                }

                if (DB_error()) {
                    $message = $LANG_STORE_COMMERCE['save_failed'];
                } else {
                    if ($id < 1) {
                        $id = (int) DB_insertId();
                    }
                    if ($imageUrl !== '') {
                        store_add_product_image($id, $imageUrl, true);
                    }
                    if (isset($_POST['save_and_close'])) {
                        store_admin_redirect($LANG_STORE['saved']);
                    }
                    store_admin_redirect($LANG_STORE['saved'], $id);
                }
            }
        }

        $product = $_POST;
        $product['id'] = $id;
        $product['slug'] = $slug;
        $product['category_id'] = $categoryId;
        $product['tax_class_id'] = $taxClassId;
        $product['weight'] = $weight;
        $product['length'] = $length;
        $product['width'] = $width;
        $product['height'] = $height;
        $product['product_type'] = $type;
        $product['active'] = $active;
        $content = store_admin_editor070($product, $message);
        COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['admin_title'])));
        exit;
    }
}

if ($action === 'new') {
    $product = array(
        'id' => 0,
        'category_id' => 0,
        'sku' => '',
        'slug' => '',
        'name' => '',
        'short_description' => '',
        'description' => '',
        'image_url' => '',
        'price' => '0.0000',
        'currency' => isset($_STORE_CONF['currency']) ? $_STORE_CONF['currency'] : 'EUR',
        'stock' => 0,
        'product_type' => 'physical',
        'active' => 1,
        'tax_class_id' => 0,
        'weight' => '0.0000',
        'length' => '0.0000',
        'width' => '0.0000',
        'height' => '0.0000'
    );
    $content = store_admin_editor070($product, $message);
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['add_product'])));
    exit;
}

if ($action === 'edit') {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $product = store_get_product($id);
    if (!$product) {
        COM_output(COM_createHTMLDocument(
            COM_showMessageText($LANG_STORE['product_not_found'], $LANG_STORE['admin_title']),
            array('pagetitle' => $LANG_STORE['admin_title'])
        ));
        exit;
    }
    $content = store_admin_editor070($product, $message);
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['edit_product'])));
    exit;
}
