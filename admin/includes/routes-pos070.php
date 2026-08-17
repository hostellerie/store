<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | routes-pos070.php                                                        |
// |                                                                           |
// | Route POS order creation through the shared Store commerce calculator.   |
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
        $created = store_create_pos_order070($items, $paymentMethod, $customer);

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
