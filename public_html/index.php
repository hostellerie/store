<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | index.php                                                                |
// |                                                                          |
// | Public catalogue, cart, checkout and customer order pages.               |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// |                                                                          |
// | This program is free software; you can redistribute it and/or            |
// | modify it under the terms of the GNU General Public License              |
// | as published by the Free Software Foundation; either version 2           |
// | of the License, or (at your option) any later version.                   |
// |                                                                          |
// | This program is distributed in the hope that it will be useful,          |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
// | GNU General Public License for more details.                             |
// |                                                                          |
// | You should have received a copy of the GNU General Public License        |
// | along with this program; if not, write to the Free Software              |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA                |
// | 02111-1307, USA.                                                         |
// |                                                                          |
// +---------------------------------------------------------------------------+

require_once dirname(__FILE__) . '/../lib-common.php';

global $_CONF, $_TABLES, $_STORE_CONF, $LANG_STORE;

$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['store_action'])) {
    if (SEC_checkToken()) {
        if ($_POST['store_action'] === 'add') {
            $id = isset($_POST['product_id']) ? (int) $_POST['product_id'] : 0;
            $qty = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;
            $product = store_get_product($id);
            if ($product && (int) $product['active'] === 1
                && (!store_product_uses_stock($product) || (int) $product['stock'] > 0)) {
                if (store_product_uses_stock($product)) {
                    $qty = min(max(1, $qty), (int) $product['stock']);
                }
                store_cart_add($id, $qty);
            }
            header('Location: ' . $_CONF['site_url'] . '/store/index.php?view=cart');
            exit;
        }

        if ($_POST['store_action'] === 'update'
            && isset($_POST['qty']) && is_array($_POST['qty'])) {
            foreach ($_POST['qty'] as $id => $qty) {
                $product = store_get_product((int) $id);
                $qty = (int) $qty;
                if ($product && store_product_uses_stock($product) && $qty > (int) $product['stock']) {
                    $qty = (int) $product['stock'];
                }
                store_cart_set((int) $id, $qty);
            }
            $next = isset($_POST['checkout']) ? 'checkout' : 'cart';
            header('Location: ' . $_CONF['site_url'] . '/store/index.php?view=' . $next);
            exit;
        }

        if ($_POST['store_action'] === 'place_order') {
            $customer = array(
                'name' => isset($_POST['customer_name']) ? $_POST['customer_name'] : '',
                'email' => isset($_POST['customer_email']) ? $_POST['customer_email'] : '',
                'address1' => isset($_POST['address1']) ? $_POST['address1'] : '',
                'address2' => isset($_POST['address2']) ? $_POST['address2'] : '',
                'postal_code' => isset($_POST['postal_code']) ? $_POST['postal_code'] : '',
                'city' => isset($_POST['city']) ? $_POST['city'] : '',
                'country' => isset($_POST['country']) ? $_POST['country'] : ''
            );
            $payment_method = isset($_POST['payment_method']) ? (string) $_POST['payment_method'] : '';
            $created = store_create_order($customer, $payment_method);
            if (!empty($created['success'])) {
                header('Location: ' . $_CONF['site_url'] . '/store/index.php?view=order&id=' . (int) $created['order_id']
                    . '&created=1');
                exit;
            }
            $checkout_error = isset($created['error']) ? $created['error'] : 'database';
        }
    }
}

$view = isset($_GET['view']) ? (string) $_GET['view'] : '';
$slug = isset($_GET['product']) ? trim((string) $_GET['product']) : '';

if ($view === 'cart') {
    $cart = store_cart_get();
    $content .= '<div class="store-cart"><h1>' . store_escape($LANG_STORE['cart']) . '</h1>';

    if (!$cart) {
        $content .= '<p>' . store_escape($LANG_STORE['cart_empty']) . '</p>';
    } else {
        $details = store_cart_details();
        if ($details['error'] === 'stock') {
            $content .= COM_showMessageText($LANG_STORE['checkout_stock_error'], $LANG_STORE['cart']);
        } elseif ($details['error'] === 'currency') {
            $content .= COM_showMessageText($LANG_STORE['checkout_currency_error'], $LANG_STORE['cart']);
        }
        $content .= '<form method="post">'
            . '<input type="hidden" name="store_action" value="update">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
            . store_escape(SEC_createToken()) . '">'
            . '<table class="store-cart-table"><tr>'
            . '<th>' . store_escape($LANG_STORE['name']) . '</th>'
            . '<th>' . store_escape($LANG_STORE['quantity']) . '</th>'
            . '<th>' . store_escape($LANG_STORE['price']) . '</th>'
            . '<th>' . store_escape($LANG_STORE['total']) . '</th></tr>';

        foreach ($details['items'] as $item) {
            $product = $item['product'];
            $content .= '<tr><td><a href="' . store_escape(store_product_url($product)) . '">'
                . store_escape($product['name']) . '</a></td>'
                . '<td><input type="number" min="0" name="qty[' . (int) $product['id'] . ']" value="'
                . (int) $item['quantity'] . '"></td>'
                . '<td>' . store_format_price($product['price'], $product['currency']) . '</td>'
                . '<td>' . store_format_price($item['line_total'], $product['currency']) . '</td></tr>';
        }

        $content .= '<tr><th colspan="3">' . store_escape($LANG_STORE['total']) . '</th><th>'
            . store_format_price($details['total'], $details['currency'])
            . '</th></tr></table><div class="store-cart-actions">'
            . '<button type="submit" class="store-secondary-button">' . store_escape($LANG_STORE['update_cart']) . '</button>';
        if ($details['items'] && $details['error'] === '') {
            $content .= '<button type="submit" name="checkout" value="1" class="store-primary-button">'
                . store_escape($LANG_STORE['checkout']) . '</button>';
        }
        $content .= '</div></form>';
    }

    $content .= '<p><a href="' . store_escape($_CONF['site_url'] . '/store/index.php') . '">'
        . store_escape($LANG_STORE['continue_shopping']) . '</a></p></div>';
} elseif ($view === 'checkout') {
    $details = store_cart_details();
    $content .= '<h1>' . store_escape($LANG_STORE['checkout']) . '</h1>';
    if (!$details['items']) {
        $content .= '<p>' . store_escape($LANG_STORE['cart_empty']) . '</p>';
    } elseif ($details['error'] !== '') {
        $content .= COM_showMessageText(
            $details['error'] === 'currency' ? $LANG_STORE['checkout_currency_error'] : $LANG_STORE['checkout_stock_error'],
            $LANG_STORE['checkout']
        );
    } else {
        if (!empty($checkout_error)) {
            $error_key = 'checkout_' . $checkout_error . '_error';
            $error_text = isset($LANG_STORE[$error_key]) ? $LANG_STORE[$error_key] : $LANG_STORE['checkout_database_error'];
            $content .= COM_showMessageText($error_text, $LANG_STORE['checkout']);
        }
        $default_name = !empty($_USER['fullname']) ? $_USER['fullname'] : (!empty($_USER['username']) ? $_USER['username'] : '');
        $default_email = !empty($_USER['email']) ? $_USER['email'] : '';
        $payment_methods = store_get_payment_methods();
        if (!$payment_methods) {
            $content .= COM_showMessageText($LANG_STORE['checkout_payment_error'], $LANG_STORE['checkout']);
        }
        $content .= '<p>' . store_escape($LANG_STORE['checkout_intro']) . '</p><div class="store-checkout-layout">';
        $content .= '<section class="store-checkout-panel"><h2>' . store_escape($LANG_STORE['customer_details']) . '</h2>'
            . '<form method="post" class="store-checkout-form">'
            . '<input type="hidden" name="store_action" value="place_order">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
            . '<label>' . store_escape($LANG_STORE['customer_name']) . '</label><input type="text" name="customer_name" required value="'
            . store_escape(isset($_POST['customer_name']) ? $_POST['customer_name'] : $default_name) . '">'
            . '<label>' . store_escape($LANG_STORE['customer_email']) . '</label><input type="email" name="customer_email" required value="'
            . store_escape(isset($_POST['customer_email']) ? $_POST['customer_email'] : $default_email) . '">'
            . '<label>' . store_escape($LANG_STORE['address1']) . '</label><input type="text" name="address1" value="'
            . store_escape(isset($_POST['address1']) ? $_POST['address1'] : '') . '">'
            . '<label>' . store_escape($LANG_STORE['address2']) . '</label><input type="text" name="address2" value="'
            . store_escape(isset($_POST['address2']) ? $_POST['address2'] : '') . '">'
            . '<div class="store-checkout-row"><div><label>' . store_escape($LANG_STORE['postal_code']) . '</label><input type="text" name="postal_code" value="'
            . store_escape(isset($_POST['postal_code']) ? $_POST['postal_code'] : '') . '"></div><div><label>'
            . store_escape($LANG_STORE['city']) . '</label><input type="text" name="city" value="'
            . store_escape(isset($_POST['city']) ? $_POST['city'] : '') . '"></div></div>'
            . '<label>' . store_escape($LANG_STORE['country']) . '</label><input type="text" name="country" value="'
            . store_escape(isset($_POST['country']) ? $_POST['country'] : '') . '">';
        $content .= '<h2>' . store_escape($LANG_STORE['payment']) . '</h2><div class="store-payment-options">';
        $selected_payment = isset($_POST['payment_method']) ? (string) $_POST['payment_method'] : '';
        $first_payment = true;
        foreach ($payment_methods as $payment_id => $payment_method) {
            $checked = ($selected_payment === $payment_id || ($selected_payment === '' && $first_payment)) ? ' checked' : '';
            $content .= '<label class="store-payment-option"><input type="radio" name="payment_method" value="'
                . store_escape($payment_id) . '"' . $checked . '> <strong>' . store_escape($payment_method['label']) . '</strong></label>';
            $first_payment = false;
        }
        $content .= '</div>';
        if ($payment_methods) {
            $content .= '<p><button class="store-primary-button" type="submit">' . store_escape($LANG_STORE['place_order']) . '</button></p>';
        }
        $content .= '</form></section>';
        $content .= '<aside class="store-order-card"><h2>' . store_escape($LANG_STORE['order_details']) . '</h2><table class="store-order-summary-table">';
        foreach ($details['items'] as $item) {
            $content .= '<tr><td>' . store_escape($item['product']['name']) . ' × ' . (int) $item['quantity'] . '</td><td>'
                . store_format_price($item['line_total'], $item['product']['currency']) . '</td></tr>';
        }
        $content .= '<tr><th>' . store_escape($LANG_STORE['total']) . '</th><th>'
            . store_format_price($details['total'], $details['currency']) . '</th></tr></table></aside></div>';
    }
} elseif ($view === 'orders') {
    $uid = isset($_USER['uid']) ? (int) $_USER['uid'] : 1;
    $content .= '<h1>' . store_escape($LANG_STORE['my_orders']) . '</h1>';
    if ($uid < 2) {
        $content .= '<p>' . store_escape($LANG_STORE['login_for_history']) . '</p>';
    } else {
        $orders = DB_query("SELECT * FROM {$_TABLES['store_orders']} WHERE user_id = $uid ORDER BY created DESC,id DESC");
        if (DB_numRows($orders) < 1) {
            $content .= '<p>' . store_escape($LANG_STORE['no_orders']) . '</p>';
        } else {
            $content .= '<div class="store-orders-list">';
            while ($order = DB_fetchArray($orders)) {
                $status_key = 'status_' . $order['status'];
                $content .= '<article class="store-order-card"><div class="store-order-meta"><strong>'
                    . store_escape($order['order_number']) . '</strong><span>' . store_escape($order['created']) . '</span><span class="store-order-status">'
                    . store_escape(isset($LANG_STORE[$status_key]) ? $LANG_STORE[$status_key] : $order['status']) . '</span><span class="store-payment-status">'
                    . store_escape(isset($LANG_STORE['payment_status_' . $order['payment_status']]) ? $LANG_STORE['payment_status_' . $order['payment_status']] : $order['payment_status']) . '</span></div>'
                    . '<p><strong>' . store_format_price($order['total'], $order['currency']) . '</strong></p>'
                    . '<a class="store-secondary-button" href="?view=order&id=' . (int) $order['id'] . '">'
                    . store_escape($LANG_STORE['order_details']) . '</a></article>';
            }
            $content .= '</div>';
        }
    }
} elseif ($view === 'order') {
    $order_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $order = store_get_order($order_id);
    if (!$order || !store_user_order_access($order)) {
        $content .= COM_showMessageText($LANG_STORE['product_not_found'], $LANG_STORE['order']);
    } else {
        if (!empty($_GET['created'])) {
            $content .= COM_showMessageText(sprintf($LANG_STORE['order_created'], $order['order_number']), $LANG_STORE['order']);
        }
        $status_key = 'status_' . $order['status'];
        $content .= '<h1>' . store_escape($LANG_STORE['order']) . ' ' . store_escape($order['order_number']) . '</h1>'
            . '<div class="store-checkout-layout"><section class="store-order-card"><div class="store-order-meta"><span>'
            . store_escape($order['created']) . '</span><span class="store-order-status">'
            . store_escape(isset($LANG_STORE[$status_key]) ? $LANG_STORE[$status_key] : $order['status']) . '</span></div>'
            . '<table class="store-order-summary-table"><tr><th>' . store_escape($LANG_STORE['name']) . '</th><th>'
            . store_escape($LANG_STORE['quantity']) . '</th><th>' . store_escape($LANG_STORE['total']) . '</th></tr>';
        foreach (store_get_order_items($order_id) as $item) {
            $content .= '<tr><td>' . store_escape($item['name']) . '</td><td>' . (int) $item['quantity'] . '</td><td>'
                . store_format_price($item['line_total'], $order['currency']) . '</td></tr>';
        }
        $content .= '<tr><th colspan="2">' . store_escape($LANG_STORE['total']) . '</th><th>'
            . store_format_price($order['total'], $order['currency']) . '</th></tr></table></section>'
            . '<aside class="store-order-card"><h2>' . store_escape($LANG_STORE['customer']) . '</h2><p><strong>'
            . store_escape($order['customer_name']) . '</strong><br>' . store_escape($order['customer_email']) . '</p><p>'
            . store_escape($order['address1']) . ($order['address2'] !== '' ? '<br>' . store_escape($order['address2']) : '')
            . '<br>' . store_escape(trim($order['postal_code'] . ' ' . $order['city'])) . '<br>' . store_escape($order['country']) . '</p>';
        $payment = store_get_payment($order_id);
        $methods = store_get_payment_methods();
        $payment_label = isset($methods[$order['payment_method']]['label']) ? $methods[$order['payment_method']]['label'] : $order['payment_method'];
        $payment_status_key = 'payment_status_' . $order['payment_status'];
        $content .= '<h2>' . store_escape($LANG_STORE['payment']) . '</h2><p><strong>' . store_escape($payment_label) . '</strong><br><span class="store-payment-status">'
            . store_escape(isset($LANG_STORE[$payment_status_key]) ? $LANG_STORE[$payment_status_key] : $order['payment_status']) . '</span></p>';
        if ($order['payment_reference'] !== '') {
            $content .= '<p>' . store_escape($LANG_STORE['payment_reference']) . ': ' . store_escape($order['payment_reference']) . '</p>';
        }
        if ($order['payment_method'] === 'manual' && $order['payment_status'] === 'pending' && isset($methods['manual']) && $methods['manual']['instructions'] !== '') {
            $content .= '<div class="store-payment-instructions">' . store_escape($methods['manual']['instructions']) . '</div>';
        }
        $content .= '</aside></div>';
    }
} elseif ($slug !== '') {
    $product = store_get_product_by_slug($slug);

    if (!$product) {
        $content .= COM_showMessageText($LANG_STORE['product_not_found'], $LANG_STORE['catalog_title']);
    } else {
        $images = store_get_product_images((int) $product['id']);
        $mainImage = !empty($product['image_url']) ? $product['image_url'] : '';
        if ($mainImage === '' && $images) {
            $mainImage = $images[0]['image_url'];
        }

        $content .= '<article class="store-product-detail">';
        $content .= '<p class="store-product-back"><a href="'
            . store_escape($_CONF['site_url'] . '/store/index.php') . '">← '
            . store_escape($LANG_STORE['back_catalog']) . '</a></p>';
        $content .= '<div class="store-product-layout">';

        $content .= '<section class="store-product-media">';
        if ($mainImage !== '') {
            $content .= '<div class="store-product-main-image-wrap">'
                . '<img id="store-main-product-image" class="store-product-image" src="'
                . store_escape($mainImage) . '" alt="' . store_escape($product['name']) . '"></div>';
        } else {
            $content .= '<div class="store-product-image-placeholder">'
                . store_escape($LANG_STORE['no_product_image']) . '</div>';
        }

        if (count($images) > 1) {
            $content .= '<div class="store-gallery-thumbs" aria-label="'
                . store_escape($LANG_STORE['product_images']) . '">';
            foreach ($images as $image) {
                $activeThumb = $image['image_url'] === $mainImage ? ' store-gallery-thumb-active' : '';
                $content .= '<button type="button" class="store-gallery-thumb' . $activeThumb
                    . '" data-image="' . store_escape($image['image_url']) . '">'
                    . '<img src="' . store_escape($image['image_url']) . '" alt=""></button>';
            }
            $content .= '</div>';
        }
        $content .= '</section>';

        $content .= '<section class="store-product-summary">';
        if (!empty($product['category_id'])) {
            $categoryName = DB_getItem($_TABLES['store_categories'], 'name', 'id = ' . (int) $product['category_id']);
            if ($categoryName !== '') {
                $content .= '<p class="store-product-category">' . store_escape($categoryName) . '</p>';
            }
        }
        $content .= '<h1>' . store_escape($product['name']) . '</h1>';

        if ($product['sku'] !== '') {
            $content .= '<p class="store-product-sku">' . store_escape($LANG_STORE['sku']) . ': '
                . store_escape($product['sku']) . '</p>';
        }

        $content .= '<p class="store-product-price">'
            . store_format_price($product['price'], $product['currency']) . '</p>';

        if (!empty($_STORE_CONF['show_stock']) && store_product_uses_stock($product)) {
            if ((int) $product['stock'] > 0) {
                $content .= '<p><span class="store-stock-badge store-stock-in">'
                    . (int) $product['stock'] . ' ' . store_escape($LANG_STORE['in_stock']) . '</span></p>';
            } else {
                $content .= '<p><span class="store-stock-badge store-stock-out">'
                    . store_escape($LANG_STORE['out_of_stock']) . '</span></p>';
            }
        } elseif (!store_product_uses_stock($product)) {
            $content .= '<p><span class="store-type-badge">' . store_escape($LANG_STORE['digital']) . '</span></p>';
        }

        if ($product['short_description'] !== '') {
            $content .= '<div class="store-product-short-description">'
                . nl2br(store_escape($product['short_description'])) . '</div>';
        }

        if (!store_product_uses_stock($product) || (int) $product['stock'] > 0) {
            $content .= '<form method="post" class="store-add-cart-box">'
                . '<input type="hidden" name="store_action" value="add">'
                . '<input type="hidden" name="product_id" value="' . (int) $product['id'] . '">'
                . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
                . store_escape(SEC_createToken()) . '">'
                . '<label for="store-product-quantity">' . store_escape($LANG_STORE['quantity']) . '</label>'
                . '<div class="store-add-cart-controls">'
                . '<input id="store-product-quantity" type="number" name="quantity" min="1" value="1">'
                . '<button type="submit" class="store-primary-button">'
                . store_escape($LANG_STORE['add_to_cart']) . '</button></div></form>';
        }

        $content .= '</section></div>';

        if ($product['description'] !== '') {
            $content .= '<section class="store-product-description"><h2>'
                . store_escape($LANG_STORE['description']) . '</h2><div>'
                . nl2br(store_escape($product['description'])) . '</div></section>';
        }

        $content .= '</article>';

        if (count($images) > 1) {
            $content .= '<script>(function(){var main=document.getElementById("store-main-product-image");'
                . 'var buttons=document.querySelectorAll(".store-gallery-thumb");'
                . 'for(var i=0;i<buttons.length;i++){buttons[i].addEventListener("click",function(){'
                . 'if(main){main.src=this.getAttribute("data-image");}'
                . 'for(var j=0;j<buttons.length;j++){buttons[j].className=buttons[j].className.replace(/ store-gallery-thumb-active/g,"");}'
                . 'this.className+=" store-gallery-thumb-active";});}})();</script>';
        }
    }
} else {
    $limit = isset($_STORE_CONF['products_per_page']) ? (int) $_STORE_CONF['products_per_page'] : 12;
    if ($limit < 1 || $limit > 100) {
        $limit = 12;
    }
    $page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
    $offset = ($page - 1) * $limit;
    $category = isset($_GET['category']) ? store_slugify($_GET['category']) : '';
    $where = 'p.active = 1';
    if ($category !== '') {
        $categoryDb = DB_escapeString($category);
        $where .= " AND c.slug = '$categoryDb'";
    }

    $result = DB_query("SELECT p.*,c.name AS category_name,c.slug AS category_slug "
        . "FROM {$_TABLES['store_products']} p LEFT JOIN {$_TABLES['store_categories']} c "
        . "ON c.id=p.category_id WHERE $where ORDER BY p.name ASC LIMIT $offset,$limit");

    $content .= '<h1>' . store_escape($LANG_STORE['catalog_title']) . '</h1>';
    $categories = store_get_categories(true);
    if ($categories) {
        $content .= '<nav class="store-category-nav"><a href="'
            . store_escape($_CONF['site_url'] . '/store/index.php') . '">'
            . store_escape($LANG_STORE['all_products']) . '</a>';
        foreach ($categories as $categoryRow) {
            $content .= '<a href="?category=' . rawurlencode($categoryRow['slug']) . '">'
                . store_escape($categoryRow['name']) . '</a>';
        }
        $content .= '</nav>';
    }

    if (DB_numRows($result) < 1) {
        $content .= '<p>' . store_escape($LANG_STORE['catalog_empty']) . '</p>';
    } else {
        $content .= '<div class="store-grid">';
        while ($product = DB_fetchArray($result)) {
            $url = $_CONF['site_url'] . '/store/index.php?product=' . rawurlencode($product['slug']);
            $content .= '<article class="store-card">';
            if (!empty($product['image_url'])) {
                $content .= '<a class="store-card-image-wrap" href="' . store_escape($url) . '">'
                    . '<img class="store-image" src="' . store_escape($product['image_url']) . '" alt="'
                    . store_escape($product['name']) . '"></a>';
            }
            if (!empty($product['category_name'])) {
                $content .= '<p class="store-product-category">' . store_escape($product['category_name']) . '</p>';
            }
            $content .= '<h2><a href="' . store_escape($url) . '">' . store_escape($product['name']) . '</a></h2>';
            if ($product['short_description'] !== '') {
                $content .= '<p>' . nl2br(store_escape($product['short_description'])) . '</p>';
            }
            $content .= '<p class="store-card-price">'
                . store_format_price($product['price'], $product['currency']) . '</p>';
            $content .= '<p><a class="store-secondary-button" href="' . store_escape($url) . '">'
                . store_escape($LANG_STORE['details']) . '</a></p></article>';
        }
        $content .= '</div>';
    }
}

COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['catalog_title'])));
