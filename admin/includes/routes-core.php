<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | routes-core.php                                                          |
// |                                                                          |
// | Core product, category, image and roadmap actions.                       |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

$action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
$message = isset($_GET['notice']) ? (string) $_GET['notice'] : '';

if ($action === 'save_category' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        $message = $LANG_STORE['invalid_token'];
    } else {
        $name = trim(isset($_POST['category_name']) ? (string) $_POST['category_name'] : '');
        if ($name === '') {
            $message = $LANG_STORE['category_name_required'];
        } else {
            $slug = store_slugify($name);
            $nameDb = DB_escapeString($name);
            $slugDb = DB_escapeString($slug);
            DB_query("INSERT INTO {$_TABLES['store_categories']} (slug,name,active,created,modified) "
                . "VALUES ('$slugDb','$nameDb',1,NOW(),NOW())");
            store_admin_redirect($LANG_STORE['category_saved']);
        }
    }
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
        $categoryId = isset($_POST['category_id']) ? (int) $_POST['category_id'] : 0;
        $short = trim(isset($_POST['short_description']) ? (string) $_POST['short_description'] : '');
        $description = trim(isset($_POST['description']) ? (string) $_POST['description'] : '');
        $imageUrl = store_valid_image_url(isset($_POST['image_url']) ? $_POST['image_url'] : '');
        $price = max(0, isset($_POST['price']) ? (float) $_POST['price'] : 0);
        $currency = strtoupper(trim(isset($_POST['currency']) ? (string) $_POST['currency'] : 'EUR'));
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            $currency = 'EUR';
        }
        $stock = max(0, isset($_POST['stock']) ? (int) $_POST['stock'] : 0);
        $type = (isset($_POST['product_type']) && $_POST['product_type'] === 'digital')
            ? 'digital'
            : 'physical';
        $active = isset($_POST['active']) ? 1 : 0;

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
                $priceDb = sprintf('%.2f', $price);

                if ($id > 0) {
                    DB_query("UPDATE {$_TABLES['store_products']} SET category_id=$categoryId,"
                        . "sku='$skuDb',slug='$slugDb',name='$nameDb',short_description='$shortDb',"
                        . "description='$descriptionDb',image_url='$imageDb',price='$priceDb',"
                        . "currency='$currencyDb',stock=$stock,product_type='$typeDb',active=$active,"
                        . "modified=NOW() WHERE id=$id");
                } else {
                    DB_query("INSERT INTO {$_TABLES['store_products']} "
                        . "(category_id,sku,slug,name,short_description,description,image_url,price,"
                        . "currency,stock,product_type,active,created,modified) VALUES "
                        . "($categoryId,'$skuDb','$slugDb','$nameDb','$shortDb','$descriptionDb',"
                        . "'$imageDb','$priceDb','$currencyDb',$stock,'$typeDb',$active,NOW(),NOW())");
                }

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

        $action = 'edit';
        $_GET['id'] = $id;
    }
}

if ($action === 'add_images' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if (!SEC_checkToken()) {
        store_admin_redirect($LANG_STORE['invalid_token'], $productId);
    }

    $raw = isset($_POST['gallery_image_urls']) ? (string) $_POST['gallery_image_urls'] : '';
    $parts = preg_split('/[\r\n,]+/', $raw);
    $makePrimary = isset($_POST['make_primary']);
    $added = 0;
    $firstAdded = true;

    if (is_array($parts)) {
        foreach ($parts as $part) {
            $url = trim($part);
            if ($url === '') {
                continue;
            }
            if (store_add_product_image($productId, $url, $makePrimary && $firstAdded)) {
                $added++;
                $firstAdded = false;
            }
        }
    }

    if ($added > 0) {
        store_admin_redirect(sprintf($LANG_STORE['images_added'], $added), $productId);
    }
    store_admin_redirect($LANG_STORE['invalid_image_url'], $productId);
}

if ($action === 'set_primary_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if (!SEC_checkToken()) {
        store_admin_redirect($LANG_STORE['invalid_token'], $productId);
    }
    $imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
    store_set_primary_image($productId, $imageId);
    store_admin_redirect($LANG_STORE['primary_updated'], $productId);
}

if ($action === 'delete_image' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
    if (!SEC_checkToken()) {
        store_admin_redirect($LANG_STORE['invalid_token'], $productId);
    }
    $imageId = isset($_POST['image_id']) ? (int) $_POST['image_id'] : 0;
    store_delete_product_image($productId, $imageId);
    store_admin_redirect($LANG_STORE['image_deleted'], $productId);
}

if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        store_admin_redirect($LANG_STORE['invalid_token']);
    }
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    if ($id > 0) {
        DB_query("DELETE FROM {$_TABLES['store_product_images']} WHERE product_id=$id");
        DB_query("DELETE FROM {$_TABLES['store_products']} WHERE id=$id");
    }
    store_admin_redirect($LANG_STORE['deleted']);
}

$content = '';

if ($action === 'roadmap') {
    $roadmapFile = isset($_CONF['path']) ? $_CONF['path'] . 'plugins/store/docs/ROADMAP.md' : '';
    if ($roadmapFile === '' || !is_file($roadmapFile)) {
        $fallbackRoadmapFile = dirname(__FILE__) . '/../../docs/ROADMAP.md';
        if (is_file($fallbackRoadmapFile)) {
            $roadmapFile = $fallbackRoadmapFile;
        }
    }
    $roadmapHtml = store_admin_render_markdown($roadmapFile);
    $content = '<div class="store-admin-home store-roadmap"><div class="store-admin-home-head"><div><h1>'
        . store_escape($LANG_STORE['roadmap_title']) . '</h1><p class="store-meta">'
        . store_escape($LANG_STORE['roadmap_intro']) . '</p></div><div class="store-admin-home-actions">'
        . '<a class="store-secondary-button" href="index.php">← ' . store_escape($LANG_STORE['back_to_products']) . '</a>'
        . '</div></div>';
    if ($roadmapHtml === '') {
        $content .= COM_showMessageText($LANG_STORE['roadmap_file_missing'], $LANG_STORE['roadmap_title']);
    } else {
        $content .= '<section class="store-roadmap-doc">' . $roadmapHtml . '</section>';
    }
    $content .= '</div>';
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['roadmap_title'])));
    exit;
}
