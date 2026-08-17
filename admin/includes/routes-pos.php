<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | routes-pos.php                                                           |
// |                                                                          |
// | Point of Sale actions, interface and printable receipt.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if ($action === 'pos_create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!SEC_checkToken()) {
        $message = $LANG_STORE['invalid_token'];
        $action = 'pos';
    } else {
        $itemsJson = isset($_POST['pos_items']) ? (string) $_POST['pos_items'] : '';
        $decoded = json_decode($itemsJson, true);
        $items = array();
        if (is_array($decoded)) {
            foreach ($decoded as $row) {
                if (isset($row['id'], $row['qty'])) {
                    $items[(int) $row['id']] = (int) $row['qty'];
                }
            }
        }
        $customer = array(
            'name' => isset($_POST['customer_name']) ? $_POST['customer_name'] : '',
            'email' => isset($_POST['customer_email']) ? $_POST['customer_email'] : ''
        );
        $paymentMethod = isset($_POST['payment_method']) ? (string) $_POST['payment_method'] : '';
        $created = store_create_pos_order($items, $paymentMethod, $customer);
        if (!empty($created['success'])) {
            header('Location: ?action=receipt&id=' . (int) $created['order_id']);
            exit;
        }
        $error = isset($created['error']) ? $created['error'] : 'database';
        if ($error === 'stock') {
            $message = $LANG_STORE['checkout_stock_error'];
        } elseif ($error === 'currency') {
            $message = $LANG_STORE['checkout_currency_error'];
        } elseif ($error === 'customer') {
            $message = $LANG_STORE['checkout_customer_error'];
        } elseif ($error === 'product') {
            $message = $LANG_STORE['pos_product_error'];
        } else {
            $message = $LANG_STORE['pos_sale_error'];
        }
        $action = 'pos';
    }
}

if ($action === 'pos') {
    $products = array();
    $result = DB_query("SELECT p.*,c.name AS category_name FROM {$_TABLES['store_products']} p "
        . "LEFT JOIN {$_TABLES['store_categories']} c ON c.id=p.category_id "
        . "WHERE p.active=1 ORDER BY c.name ASC,p.name ASC");
    while ($row = DB_fetchArray($result)) {
        $products[] = $row;
    }

    $content = '<div class="store-admin-home store-pos"><div class="store-admin-home-head"><div><h1>'
        . store_escape($LANG_STORE['pos_title']) . '</h1><p class="store-meta">'
        . store_escape($LANG_STORE['pos_intro']) . '</p></div><div class="store-admin-home-actions">'
        . '<a class="store-secondary-button" href="index.php">← ' . store_escape($LANG_STORE['back_to_products']) . '</a>'
        . '<a class="store-secondary-button" href="?action=orders">' . store_escape($LANG_STORE['orders']) . '</a></div></div>';
    if ($message !== '') {
        $content .= COM_showMessageText($message, $LANG_STORE['pos_title']);
    }

    $content .= '<div class="store-pos-layout"><section class="store-admin-panel"><div class="store-pos-toolbar">'
        . '<input id="store-pos-search" class="store-pos-search" type="search" placeholder="'
        . store_escape($LANG_STORE['pos_search']) . '"></div><div class="store-pos-categories">'
        . '<button type="button" class="store-pos-filter is-active" data-category="">' . store_escape($LANG_STORE['all_products']) . '</button>';
    foreach (store_get_categories(true) as $category) {
        $content .= '<button type="button" class="store-pos-filter" data-category="' . (int) $category['id'] . '">'
            . store_escape($category['name']) . '</button>';
    }
    $content .= '</div><div class="store-pos-products" id="store-pos-products">';
    foreach ($products as $product) {
        $disabled = store_product_uses_stock($product) && (int) $product['stock'] < 1;
        $searchText = strtolower($product['name'] . ' ' . $product['sku'] . ' ' . $product['category_name']);
        $usesStock = store_product_uses_stock($product);
        $metaParts = array();
        if (trim($product['sku']) !== '') {
            $metaParts[] = sprintf($LANG_STORE['pos_sku'], $product['sku']);
        }
        if (trim($product['category_name']) !== '') {
            $metaParts[] = $product['category_name'];
        }
        $stockText = $usesStock
            ? sprintf($LANG_STORE['pos_stock'], (int) $product['stock'])
            : $LANG_STORE['pos_digital'];
        $imageHtml = !empty($product['image_url'])
            ? '<img class="store-pos-product-image" src="' . store_escape($product['image_url']) . '" alt="">'
            : '<span class="store-pos-product-image-placeholder">' . store_escape($LANG_STORE['pos_no_image']) . '</span>';
        $content .= '<button type="button" class="store-pos-product" data-id="' . (int) $product['id']
            . '" data-name="' . store_escape($product['name']) . '" data-sku="' . store_escape($product['sku'])
            . '" data-price="' . store_escape($product['price']) . '" data-currency="' . store_escape($product['currency'])
            . '" data-stock="' . (int) $product['stock'] . '" data-uses-stock="' . ($usesStock ? '1' : '0')
            . '" data-category="' . (int) $product['category_id'] . '" data-search="' . store_escape($searchText) . '"'
            . ($disabled ? ' disabled' : '') . '>' . $imageHtml . '<span><span class="store-pos-product-name">'
            . store_escape($product['name']) . '</span><span class="store-pos-product-meta">'
            . store_escape(implode(' · ', $metaParts)) . '</span><span class="store-pos-product-stock">'
            . store_escape($stockText) . '</span></span><span class="store-pos-product-price">'
            . store_format_price($product['price'], $product['currency']) . '</span></button>';
    }
    $content .= '</div></section>';

    $content .= '<aside class="store-admin-panel store-pos-ticket"><h2>' . store_escape($LANG_STORE['pos_ticket']) . '</h2>'
        . '<form method="post" id="store-pos-form"><input type="hidden" name="action" value="pos_create">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
        . '<input type="hidden" name="pos_items" id="store-pos-items" value="[]">'
        . '<div id="store-pos-lines" class="store-pos-lines"><div class="store-pos-empty">'
        . store_escape($LANG_STORE['pos_empty']) . '</div></div>'
        . '<div class="store-pos-total"><span>' . store_escape($LANG_STORE['total']) . '</span><span id="store-pos-total">0.00</span></div>'
        . '<h3>' . store_escape($LANG_STORE['pos_payment']) . '</h3><div class="store-pos-payment-grid">';
    $first = true;
    foreach (store_get_pos_payment_methods() as $methodId => $method) {
        $content .= '<label class="store-pos-payment"><input type="radio" name="payment_method" value="'
            . store_escape($methodId) . '"' . ($first ? ' checked' : '') . '> ' . store_escape($method['label']) . '</label>';
        $first = false;
    }
    $content .= '</div><h3>' . store_escape($LANG_STORE['pos_customer_optional']) . '</h3><div class="store-pos-customer">'
        . '<input type="text" name="customer_name" placeholder="' . store_escape($LANG_STORE['customer_name']) . '">'
        . '<input type="email" name="customer_email" placeholder="' . store_escape($LANG_STORE['customer_email']) . '"></div>'
        . '<p><button type="submit" id="store-pos-submit" class="store-primary-button store-pos-submit" disabled>'
        . store_escape($LANG_STORE['pos_validate']) . '</button></p></form></aside></div></div>';

    $emptyText = json_encode($LANG_STORE['pos_empty']);
    $posScript = <<<'STOREPOSJS'
(function () {
    var cart = {};
    var products = document.querySelectorAll('.store-pos-product');
    var lines = document.getElementById('store-pos-lines');
    var items = document.getElementById('store-pos-items');
    var total = document.getElementById('store-pos-total');
    var submit = document.getElementById('store-pos-submit');
    var search = document.getElementById('store-pos-search');
    var activeCategory = '';
    var emptyText = __STORE_POS_EMPTY__;

    function esc(value) {
        var node = document.createElement('div');
        node.textContent = String(value);
        return node.innerHTML;
    }

    function money(value, currency) {
        return Number(value).toFixed(2) + ' ' + currency;
    }

    function render() {
        var ids = Object.keys(cart);
        var sum = 0;
        var currency = '';
        var html = '';

        if (!ids.length) {
            lines.innerHTML = '<div class="store-pos-empty">' + esc(emptyText) + '</div>';
            items.value = '[]';
            total.textContent = '0.00';
            submit.disabled = true;
            return;
        }

        ids.forEach(function (id) {
            var item = cart[id];
            var lineTotal = item.price * item.qty;
            sum += lineTotal;
            currency = item.currency;
            html += '<div class="store-pos-line">'
                + '<div><strong>' + esc(item.name) + '</strong><div class="store-meta">'
                + (item.sku ? esc(item.sku) + ' · ' : '') + money(item.price, item.currency) + '</div></div>'
                + '<div class="store-pos-qty"><button type="button" data-op="minus" data-id="' + id + '">−</button>'
                + '<span>' + item.qty + '</span><button type="button" data-op="plus" data-id="' + id + '">+</button></div>'
                + '<div>' + money(lineTotal, item.currency)
                + ' <button type="button" class="store-pos-remove" data-op="remove" data-id="' + id + '">×</button></div></div>';
        });

        lines.innerHTML = html;
        items.value = JSON.stringify(ids.map(function (id) {
            return {id: parseInt(id, 10), qty: cart[id].qty};
        }));
        total.textContent = money(sum, currency);
        submit.disabled = false;
    }

    Array.prototype.forEach.call(products, function (button) {
        button.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var limit = parseInt(this.getAttribute('data-stock') || '0', 10);
            var usesStock = this.getAttribute('data-uses-stock') === '1';

            if (!cart[id]) {
                cart[id] = {
                    name: this.getAttribute('data-name'),
                    sku: this.getAttribute('data-sku') || '',
                    price: parseFloat(this.getAttribute('data-price')),
                    currency: this.getAttribute('data-currency'),
                    qty: 0,
                    stock: limit,
                    usesStock: usesStock
                };
            }
            if (!usesStock || cart[id].qty < limit) {
                cart[id].qty++;
                render();
            }
        });
    });

    lines.addEventListener('click', function (event) {
        var button = event.target;
        while (button && button !== lines && !button.getAttribute('data-op')) {
            button = button.parentNode;
        }
        if (!button || button === lines) {
            return;
        }
        var id = button.getAttribute('data-id');
        var operation = button.getAttribute('data-op');
        var item = cart[id];
        if (!item) {
            return;
        }
        if (operation === 'plus' && (!item.usesStock || item.qty < item.stock)) {
            item.qty++;
        } else if (operation === 'minus') {
            item.qty--;
        } else if (operation === 'remove') {
            item.qty = 0;
        }
        if (item.qty < 1) {
            delete cart[id];
        }
        render();
    });

    function filterProducts() {
        var query = (search.value || '').toLowerCase();
        Array.prototype.forEach.call(products, function (button) {
            var category = button.getAttribute('data-category');
            var searchData = button.getAttribute('data-search') || '';
            var matchesCategory = !activeCategory || category === activeCategory;
            var matchesText = !query || searchData.indexOf(query) !== -1;
            button.style.display = matchesCategory && matchesText ? 'flex' : 'none';
        });
    }

    search.addEventListener('input', filterProducts);
    Array.prototype.forEach.call(document.querySelectorAll('.store-pos-filter'), function (button) {
        button.addEventListener('click', function () {
            Array.prototype.forEach.call(document.querySelectorAll('.store-pos-filter'), function (item) {
                item.classList.remove('is-active');
            });
            this.classList.add('is-active');
            activeCategory = this.getAttribute('data-category');
            filterProducts();
        });
    });

    render();
}());
STOREPOSJS;
    $content .= '<script>' . str_replace('__STORE_POS_EMPTY__', $emptyText, $posScript) . '</script>';

    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['pos_title'])));
    exit;
}

if ($action === 'receipt') {
    $orderId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
    $order = store_get_order($orderId);
    if (!$order || $order['order_source'] !== 'pos') {
        store_admin_redirect($LANG_STORE['no_orders']);
    }
    $methods = store_get_pos_payment_methods();
    $paymentLabel = isset($methods[$order['payment_method']]) ? $methods[$order['payment_method']]['label'] : $order['payment_method'];
    $content = '<div class="store-receipt-actions"><a class="store-secondary-button" href="?action=pos">'
        . store_escape($LANG_STORE['pos_new_sale']) . '</a> <a class="store-secondary-button" href="?action=order&id=' . (int) $order['id'] . '">'
        . store_escape($LANG_STORE['order_details']) . '</a> <button type="button" class="store-primary-button" onclick="window.print()">'
        . store_escape($LANG_STORE['pos_print_receipt']) . '</button></div><section class="store-receipt"><h1>'
        . store_escape($_CONF['site_name']) . '</h1><div class="store-receipt-meta">' . store_escape($LANG_STORE['order']) . ' '
        . store_escape($order['order_number']) . '<br>' . store_escape($order['created']) . '</div><hr>';
    foreach (store_get_order_items($orderId) as $item) {
        $content .= '<div class="store-receipt-line"><span>' . (int) $item['quantity'] . ' × ' . store_escape($item['name'])
            . '</span><span>' . store_format_price($item['line_total'], $order['currency']) . '</span></div>';
    }
    $content .= '<div class="store-receipt-line store-receipt-total"><span>' . store_escape($LANG_STORE['total']) . '</span><span>'
        . store_format_price($order['total'], $order['currency']) . '</span></div><p><strong>'
        . store_escape($LANG_STORE['payment_method']) . ':</strong> ' . store_escape($paymentLabel) . '</p></section>'
        . '<style media="print">body *{visibility:hidden}.store-receipt,.store-receipt *{visibility:visible}.store-receipt{position:absolute;left:0;top:0;width:100%;max-width:none;border:0}.store-receipt-actions{display:none}</style>';
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['pos_receipt'])));
    exit;
}
