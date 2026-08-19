<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-orders.php                                                        |
// |                                                                          |
// | Order/payment actions and main administration views.                     |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if ($action === 'order_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $status = isset($_POST['status']) ? (string) $_POST['status'] : '';
    if (!SEC_checkToken()) {
        $message = $LANG_STORE['invalid_token'];
    } elseif (store_update_order_status($order_id, $status)) {
        $message = $LANG_STORE['status_updated'];
    } else {
        $message = $LANG_STORE['status_update_failed'];
    }
    $action = 'order';
    $_REQUEST['id'] = $order_id;
}

if ($action === 'payment_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $payment_status = isset($_POST['payment_status']) ? (string) $_POST['payment_status'] : '';
    $payment_reference = isset($_POST['payment_reference']) ? (string) $_POST['payment_reference'] : '';
    if (!SEC_checkToken()) {
        $message = $LANG_STORE['invalid_token'];
    } elseif (store_update_payment_status($order_id, $payment_status, $payment_reference)) {
        $message = $LANG_STORE['payment_updated'];
    } else {
        $message = $LANG_STORE['payment_update_failed'];
    }
    $action = 'order';
    $_REQUEST['id'] = $order_id;
}

if ($action === 'orders') {
    $content = '<div class="store-admin-home"><div class="store-admin-home-head"><div><h1>'
        . store_escape($LANG_STORE['orders']) . '</h1></div><div class="store-admin-home-actions"><a class="store-secondary-button" href="index.php">← '
        . store_escape($LANG_STORE['back_to_products']) . '</a></div></div>';
    $orders = DB_query("SELECT * FROM {$_TABLES['store_orders']} ORDER BY created DESC,id DESC");
    if (DB_numRows($orders) < 1) {
        $content .= '<p>' . store_escape($LANG_STORE['no_orders']) . '</p>';
    } else {
        $content .= '<section class="store-admin-panel"><div class="store-admin-table-wrap"><table class="store-admin-table"><tr><th>'
            . store_escape($LANG_STORE['order_number']) . '</th><th>' . store_escape($LANG_STORE['customer']) . '</th><th>'
            . store_escape($LANG_STORE['order_date']) . '</th><th>' . store_escape($LANG_STORE['pos_source']) . '</th><th>' . store_escape($LANG_STORE['order_status']) . '</th><th>'
            . store_escape($LANG_STORE['payment']) . '</th><th>' . store_escape($LANG_STORE['order_total']) . '</th><th></th></tr>';
        while ($order = DB_fetchArray($orders)) {
            $status_key = 'status_' . $order['status'];
            $content .= '<tr><td><strong>' . store_escape($order['order_number']) . '</strong></td><td>'
                . store_escape($order['customer_name']) . '<br><span class="store-meta">' . store_escape($order['customer_email']) . '</span></td><td>'
                . store_escape($order['created']) . '</td><td><span class="store-source-badge ' . ($order['order_source'] === 'pos' ? 'store-source-pos' : '') . '">' . store_escape($order['order_source'] === 'pos' ? $LANG_STORE['source_pos'] : $LANG_STORE['source_online']) . '</span></td><td><span class="store-order-status">'
                . store_escape(isset($LANG_STORE[$status_key]) ? $LANG_STORE[$status_key] : $order['status']) . '</span></td><td><span class="store-payment-status">'
                . store_escape(isset($LANG_STORE['payment_status_' . $order['payment_status']]) ? $LANG_STORE['payment_status_' . $order['payment_status']] : $order['payment_status']) . '</span></td><td>'
                . store_format_price($order['total'], $order['currency']) . '</td><td><a class="store-action-button store-action-edit" href="?action=order&id='
                . (int) $order['id'] . '">' . store_escape($LANG_STORE['order_details']) . '</a></td></tr>';
        }
        $content .= '</table></div></section>';
    }
    $content .= '</div>';
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['orders'])));
    exit;
}

if ($action === 'order') {
    $order_id = isset($_REQUEST['id']) ? (int) $_REQUEST['id'] : 0;
    $order = store_get_order($order_id);
    if (!$order) {
        store_admin_redirect($LANG_STORE['no_orders']);
    }
    $status_key = 'status_' . $order['status'];
    $content = '<div class="store-admin-editor"><div class="store-admin-editor-head"><div><h1>'
        . store_escape($LANG_STORE['order']) . ' ' . store_escape($order['order_number']) . '</h1><p class="store-meta">'
        . store_escape($order['created']) . '</p></div><div class="store-editor-actions"><a class="store-secondary-button" href="?action=orders">← '
        . store_escape($LANG_STORE['orders']) . '</a>'
        . ($order['order_source'] === 'pos' ? '<a class="store-secondary-button" href="?action=receipt&id=' . (int) $order['id'] . '">' . store_escape($LANG_STORE['pos_receipt']) . '</a>' : '')
        . '</div></div>';
    if (!empty($message)) {
        $content .= COM_showMessageText($message, $LANG_STORE['orders']);
    }
    $content .= '<div class="store-admin-columns"><section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['order_details'])
        . '</h2><table class="store-order-summary-table"><tr><th>' . store_escape($LANG_STORE['name']) . '</th><th>'
        . store_escape($LANG_STORE['quantity']) . '</th><th>' . store_escape($LANG_STORE['price']) . '</th><th>'
        . store_escape($LANG_STORE['total']) . '</th></tr>';
    foreach (store_get_order_items($order_id) as $item) {
        $content .= '<tr><td>' . store_escape($item['name']) . '</td><td>' . (int) $item['quantity'] . '</td><td>'
            . store_format_price($item['unit_price'], $order['currency']) . '</td><td>' . store_format_price($item['line_total'], $order['currency']) . '</td></tr>';
    }
    $content .= '<tr><th colspan="3">' . store_escape($LANG_STORE['total']) . '</th><th>'
        . store_format_price($order['total'], $order['currency']) . '</th></tr></table></section>';
    $content .= '<aside><section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['customer']) . '</h2><p><strong>'
        . store_escape($order['customer_name']) . '</strong><br>' . store_escape($order['customer_email']) . '</p><p>'
        . store_escape($order['address1']) . ($order['address2'] !== '' ? '<br>' . store_escape($order['address2']) : '') . '<br>'
        . store_escape(trim($order['postal_code'] . ' ' . $order['city'])) . '<br>' . store_escape($order['country']) . '</p></section>';
    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['order_status']) . '</h2><p><span class="store-order-status">'
        . store_escape(isset($LANG_STORE[$status_key]) ? $LANG_STORE[$status_key] : $order['status']) . '</span></p>'
        . '<form method="post"><input type="hidden" name="action" value="order_status"><input type="hidden" name="order_id" value="'
        . $order_id . '"><input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '"><select name="status">';
    foreach (store_order_statuses() as $status) {
        $key = 'status_' . $status;
        $content .= '<option value="' . store_escape($status) . '"' . ($status === $order['status'] ? ' selected' : '') . '>'
            . store_escape(isset($LANG_STORE[$key]) ? $LANG_STORE[$key] : $status) . '</option>';
    }
    $content .= '</select><p><button class="store-primary-button" type="submit">' . store_escape($LANG_STORE['update_status'])
        . '</button></p></form></section>';
    $payment = store_get_payment($order_id);
    $content .= '<section class="store-admin-panel"><h2>' . store_escape($LANG_STORE['payment']) . '</h2>';
    if ($payment) {
        $methods = $order['order_source'] === 'pos' ? store_get_pos_payment_methods() : store_get_payment_methods();
        $method_label = isset($methods[$order['payment_method']]['label']) ? $methods[$order['payment_method']]['label'] : $order['payment_method'];
        $content .= '<p><strong>' . store_escape($method_label) . '</strong></p><form method="post">'
            . '<input type="hidden" name="action" value="payment_status"><input type="hidden" name="order_id" value="' . $order_id . '">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
            . '<label>' . store_escape($LANG_STORE['payment_status']) . '</label><select name="payment_status">';
        foreach (store_payment_statuses() as $payment_status) {
            $key = 'payment_status_' . $payment_status;
            $content .= '<option value="' . store_escape($payment_status) . '"' . ($payment_status === $order['payment_status'] ? ' selected' : '') . '>'
                . store_escape(isset($LANG_STORE[$key]) ? $LANG_STORE[$key] : $payment_status) . '</option>';
        }
        $content .= '</select><label>' . store_escape($LANG_STORE['payment_reference']) . '</label><input type="text" name="payment_reference" value="'
            . store_escape($order['payment_reference']) . '"><p><button class="store-primary-button" type="submit">'
            . store_escape($LANG_STORE['update_payment']) . '</button></p></form>';
    }
    $content .= '</section></aside></div></div>';
    COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['orders'])));
    exit;
}

if ($action === 'new' || $action === 'edit') {
    $product = array();
    if ($action === 'edit') {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $product = store_get_product($id);
        if (!$product) {
            $product = array();
        }
    }
    if (isset($_POST['name'])) {
        $product = $_POST;
    }
    $content .= store_admin_editor($product, $message);
} else {
    if ($message !== '') {
        $content .= COM_showMessageText($message, $LANG_STORE['admin_title']);
    }

    $content .= '<div class="store-admin-home">';
    $content .= '<div class="store-admin-home-head"><div><h1>' . store_escape($LANG_STORE['admin_title']) . '</h1>';
    $content .= '<p class="store-meta">' . store_escape($LANG_STORE['admin_intro']) . '</p></div>';
    $content .= '<div class="store-admin-home-actions"><a class="store-primary-button" href="?action=new">'
        . store_escape($LANG_STORE['add_product']) . '</a>'
        . '<a class="store-secondary-button" href="?action=pos">' . store_escape($LANG_STORE['pos']) . '</a>' . '<a class="store-secondary-button" href="?action=orders">' . store_escape($LANG_STORE['orders']) . '</a>'
        . '<a class="store-secondary-button" href="?action=roadmap">' . store_escape($LANG_STORE['roadmap']) . '</a>'
        . '<a class="store-secondary-button" href="'
        . store_escape($_CONF['site_admin_url'] . '/configuration.php?conf_group=store') . '">'
        . store_escape($LANG_STORE['configuration']) . '</a></div></div>';

    $content .= '<section class="store-admin-panel store-category-panel"><div class="store-panel-head"><div><h2>'
        . store_escape($LANG_STORE['categories']) . '</h2><p class="store-meta">'
        . store_escape($LANG_STORE['categories_help']) . '</p></div></div>';
    $content .= '<form method="post" class="store-category-add-form">'
        . '<input type="hidden" name="action" value="save_category">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
        . '<input type="text" name="category_name" placeholder="' . store_escape($LANG_STORE['category_name']) . '" required>'
        . '<button type="submit" class="store-secondary-button">' . store_escape($LANG_STORE['add_category']) . '</button></form>';

    $categories = store_get_categories(false);
    if ($categories) {
        $content .= '<div class="store-category-chips">';
        foreach ($categories as $category) {
            $content .= '<span class="store-category-chip">' . store_escape($category['name']) . '</span>';
        }
        $content .= '</div>';
    }
    $content .= '</section>';

    $result = DB_query("SELECT p.*,c.name AS category_name FROM {$_TABLES['store_products']} p "
        . "LEFT JOIN {$_TABLES['store_categories']} c ON c.id=p.category_id ORDER BY p.name ASC");

    $content .= '<section class="store-admin-panel store-products-panel"><div class="store-panel-head"><h2>'
        . store_escape($LANG_STORE['products']) . '</h2></div><div class="store-admin-table-wrap">';
    $content .= '<table class="store-admin-table"><tr>'
        . '<th>' . store_escape($LANG_STORE['name']) . '</th>'
        . '<th>' . store_escape($LANG_STORE['category']) . '</th>'
        . '<th>' . store_escape($LANG_STORE['price']) . '</th>'
        . '<th>' . store_escape($LANG_STORE['stock']) . '</th>'
        . '<th>' . store_escape($LANG_STORE['active']) . '</th><th></th></tr>';

    while ($row = DB_fetchArray($result)) {
        $stock = store_product_uses_stock($row) ? (int) $row['stock'] : '—';
        $publicUrl = store_product_url($row);
        $content .= '<tr><td><strong>' . store_escape($row['name']) . '</strong><br>'
            . '<span class="store-meta"><a href="' . store_escape($publicUrl)
            . '" target="_blank" rel="noopener">' . store_escape($LANG_STORE['view_product'])
            . '</a></span></td>'
            . '<td>' . store_escape($row['category_name'] !== null ? $row['category_name'] : $LANG_STORE['no_category']) . '</td>'
            . '<td>' . store_format_price($row['price'], $row['currency']) . '</td>'
            . '<td>' . $stock . '</td>'
            . '<td>' . ((int) $row['active'] ? $LANG_STORE['yes'] : $LANG_STORE['no']) . '</td>'
            . '<td class="store-actions"><a class="store-action-button store-action-edit" href="?action=edit&id=' . (int) $row['id'] . '">'
            . store_escape($LANG_STORE['edit']) . '</a> '
            . '<form method="post" style="display:inline">'
            . '<input type="hidden" name="action" value="delete">'
            . '<input type="hidden" name="id" value="' . (int) $row['id'] . '">'
            . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
            . '<button type="submit" class="store-action-button store-action-delete" onclick="return confirm(\'' . store_escape($LANG_STORE['confirm_delete']) . '\')">'
            . store_escape($LANG_STORE['delete']) . '</button></form></td></tr>';
    }

    $content .= '</table></div></section><p class="store-meta store-version">' . store_escape($LANG_STORE['version']) . '</p></div>';
}

COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['admin_title'])));
