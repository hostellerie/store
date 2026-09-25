<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.1                                                       |
// +---------------------------------------------------------------------------+
// | routes-home070.php                                                       |
// |                                                                           |
// | Store 0.7 administration home with commerce configuration shortcuts.      |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

if (!isset($action)) {
    $action = isset($_REQUEST['action']) ? (string) $_REQUEST['action'] : '';
}

if ($action !== '') {
    return;
}

$content = '';
if (!empty($message)) {
    $content .= COM_showMessageText($message, $LANG_STORE['admin_title']);
}

$content .= '<div class="store-admin-home">';
$content .= '<div class="store-admin-home-head"><div><h1>' . store_escape($LANG_STORE['admin_title']) . '</h1>';
$content .= '<p class="store-meta">' . store_escape($LANG_STORE['admin_intro']) . '</p></div>';
$content .= '<div class="store-admin-home-actions">'
    . '<a class="store-primary-button" href="?action=new">' . store_escape($LANG_STORE['add_product']) . '</a>'
    . '<a class="store-secondary-button" href="?action=pos">' . store_escape($LANG_STORE['pos']) . '</a>'
    . '<a class="store-secondary-button" href="?action=orders">' . store_escape($LANG_STORE['orders']) . '</a>'
    . '<a class="store-secondary-button" href="?action=commerce">' . store_escape($LANG_STORE_COMMERCE['commerce']) . '</a>'
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

$result = DB_query("SELECT p.*,c.name AS category_name,tc.name AS tax_class_name FROM {$_TABLES['store_products']} p "
    . "LEFT JOIN {$_TABLES['store_categories']} c ON c.id=p.category_id "
    . "LEFT JOIN {$_TABLES['store_tax_classes']} tc ON tc.id=p.tax_class_id ORDER BY p.name ASC");

$content .= '<section class="store-admin-panel store-products-panel"><div class="store-panel-head"><h2>'
    . store_escape($LANG_STORE['products']) . '</h2></div><div class="store-admin-table-wrap">';
$content .= '<table class="store-admin-table"><tr>'
    . '<th>' . store_escape($LANG_STORE['name']) . '</th>'
    . '<th>' . store_escape($LANG_STORE['category']) . '</th>'
    . '<th>' . store_escape($LANG_STORE['price']) . '</th>'
    . '<th>' . store_escape($LANG_STORE_COMMERCE['tax_class']) . '</th>'
    . '<th>' . store_escape($LANG_STORE['stock']) . '</th>'
    . '<th>' . store_escape($LANG_STORE['active']) . '</th><th></th></tr>';

while ($row = DB_fetchArray($result)) {
    $stock = store_product_uses_stock($row) ? (int) $row['stock'] : '—';
    $publicUrl = store_product_url($row);
    $taxClass = !empty($row['tax_class_name']) ? $row['tax_class_name'] : $LANG_STORE_COMMERCE['no_tax_class'];
    $content .= '<tr><td><strong>' . store_escape($row['name']) . '</strong><br>'
        . '<span class="store-meta"><a href="' . store_escape($publicUrl)
        . '" target="_blank" rel="noopener">' . store_escape($LANG_STORE['view_product']) . '</a></span></td>'
        . '<td>' . store_escape($row['category_name'] !== null ? $row['category_name'] : $LANG_STORE['no_category']) . '</td>'
        . '<td>' . store_format_price($row['price'], $row['currency']) . '</td>'
        . '<td>' . store_escape($taxClass) . '</td>'
        . '<td>' . $stock . '</td>'
        . '<td>' . ((int) $row['active'] ? store_escape($LANG_STORE['yes']) : store_escape($LANG_STORE['no'])) . '</td>'
        . '<td class="store-actions"><a class="store-action-button store-action-edit" href="?action=edit&id=' . (int) $row['id'] . '">'
        . store_escape($LANG_STORE['edit']) . '</a>'
        . '<a class="store-action-button" href="?action=product_commerce&id=' . (int) $row['id'] . '">'
        . store_escape($LANG_STORE_COMMERCE['product_tax_shipping']) . '</a>'
        . '<form method="post" onsubmit="return confirm(\'' . store_escape($LANG_STORE['confirm_delete']) . '\')">'
        . '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . (int) $row['id'] . '">'
        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="' . store_escape(SEC_createToken()) . '">'
        . '<button class="store-action-button store-action-delete" type="submit">' . store_escape($LANG_STORE['delete']) . '</button></form></td></tr>';
}

$content .= '</table></div></section>';
$content .= '<p class="store-version store-meta">Version ' . store_escape(plugin_getversion_store()) . '</p></div>';

COM_output(COM_createHTMLDocument($content, array('pagetitle' => $LANG_STORE['admin_title'])));
exit;
