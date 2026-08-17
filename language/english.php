<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | english.php                                                              |
// |                                                                          |
// | English language strings and Store configuration labels.                 |
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

$LANG_configsections['store'] = array('label' => 'Store', 'title' => 'Store Plugin Configuration');
$LANG_confignames['store'] = array(
    'hide_menu' => 'Hide Store from the main menu',
    'currency' => 'Default currency (ISO 4217 code)',
    'products_per_page' => 'Products per page',
    'show_stock' => 'Display stock on public pages',
    'manual_payment_enabled' => 'Enable manual / bank-transfer payment',
    'manual_payment_label' => 'Manual payment method label',
    'manual_payment_instructions' => 'Manual payment instructions shown after checkout',
);
$LANG_configsubgroups['store'] = array('sg_main' => 'Main Settings');
$LANG_fs['store'] = array('fs_main' => 'Store Settings');
$LANG_tab['store'] = array('tab_main' => 'Main Settings');
$LANG_configselects['store'] = array(
    0 => array('Yes' => 1, 'No' => 0)
);

$LANG_STORE = array(
    'plugin_name' => 'Store',
    'menu_store' => 'Store',
    'catalog_title' => 'Store',
    'catalog_empty' => 'No products are available yet.',
    'product_not_found' => 'Product not found.',
    'price' => 'Price',
    'stock' => 'Stock',
    'stock_physical_only' => 'Stock applies to physical products only. Digital products are not stock-limited.',
    'in_stock' => 'in stock',
    'out_of_stock' => 'Out of stock',
    'sku' => 'SKU',
    'details' => 'View product',
    'back_catalog' => 'Back to the store',
    'admin_title' => 'Store administration',
    'admin_intro' => 'This first Store version lets you create and publish a simple product catalogue.',
    'add_product' => 'Add product',
    'edit_product' => 'Edit product',
    'edit_product_help' => 'Save changes without leaving this product, then manage its gallery below.',
    'product_information' => 'Product information',
    'sales_settings' => 'Sales settings',
    'products' => 'Products',
    'name' => 'Name',
    'slug' => 'Slug',
    'short_description' => 'Short description',
    'description' => 'Description',
    'currency' => 'Currency',
    'product_type' => 'Product type',
    'physical' => 'Physical',
    'digital' => 'Digital',
    'active' => 'Published',
    'yes' => 'Yes',
    'no' => 'No',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'confirm_delete' => 'Delete this product?',
    'saved' => 'The product has been saved.',
    'deleted' => 'The product has been deleted.',
    'invalid_token' => 'The security token has expired. Please try again.',
    'name_required' => 'The product name is required.',
    'slug_exists' => 'This slug is already used by another product.',
    'categories' => 'Categories',
    'category' => 'Category',
    'no_category' => 'No category',
    'add_category' => 'Add category',
    'category_saved' => 'The category has been saved.',
    'category_name_required' => 'The category name is required.',
    'image_url' => 'Main image URL',
    'image_url_help' => 'Choose an image with Geeklog File Manager or enter an http(s) URL or site-relative path.',
    'choose_image' => 'Choose image',
    'choose_images' => 'Choose images',
    'cart' => 'Cart',
    'add_to_cart' => 'Add to cart',
    'cart_empty' => 'Your cart is empty.',
    'quantity' => 'Quantity',
    'total' => 'Total',
    'update_cart' => 'Update cart',
    'continue_shopping' => 'Continue shopping',
    'added_to_cart' => 'The product has been added to your cart.',
    'view_product' => 'View product',
    'product_images' => 'Product images',
    'add_image' => 'Add image',
    'add_selected_images' => 'Add selected images',
    'selected_images' => 'Images to add',
    'selected_images_help' => 'Select several images successively in Geeklog File Manager or enter one image URL per line.',
    'multiple_images_help' => 'The File Manager stays available while you select images. Each selection is added to this list; save them together when finished.',
    'first_image_primary' => 'Use the first added image as the primary image',
    'make_primary' => 'Make primary',
    'primary_image' => 'Primary image',
    'confirm_delete_image' => 'Delete this image?',
    'image_added' => 'The image has been added.',
    'images_added' => '%d image(s) have been added.',
    'image_deleted' => 'The image has been deleted.',
    'primary_updated' => 'The primary image has been updated.',
    'invalid_image_url' => 'Please choose or enter a valid image URL.',
    'configuration' => 'Configuration',
    'back_to_products' => 'Back to products',
    'save_and_close' => 'Save and close',
    'categories_help' => 'Organize the catalogue with simple product categories.',
    'category_name' => 'Category name',
    'no_product_image' => 'No product image',
    'all_products' => 'All products',
    'checkout' => 'Checkout',
    'checkout_intro' => 'Review your cart and enter the customer details required to create the order.',
    'customer_details' => 'Customer details',
    'customer_name' => 'Full name',
    'customer_email' => 'Email address',
    'address1' => 'Address',
    'address2' => 'Address line 2',
    'postal_code' => 'Postal code',
    'city' => 'City',
    'country' => 'Country',
    'place_order' => 'Place order',
    'order_created' => 'Your order %s has been created.',
    'order' => 'Order',
    'orders' => 'Orders',
    'my_orders' => 'My orders',
    'no_orders' => 'No orders are available.',
    'order_number' => 'Order number',
    'order_date' => 'Date',
    'order_status' => 'Status',
    'order_total' => 'Total',
    'order_details' => 'Order details',
    'customer' => 'Customer',
    'status_pending' => 'Pending',
    'status_paid' => 'Paid',
    'status_processing' => 'Processing',
    'status_completed' => 'Completed',
    'status_cancelled' => 'Cancelled',
    'update_status' => 'Update status',
    'status_updated' => 'The order status has been updated.',
    'status_update_failed' => 'The order status could not be updated. Check available stock if reopening a cancelled order.',
    'checkout_customer_error' => 'Enter a valid name and email address.',
    'checkout_address_error' => 'Enter the shipping address for physical products.',
    'checkout_stock_error' => 'One or more requested quantities are no longer available. Update your cart.',
    'checkout_currency_error' => 'A single order cannot currently contain products in different currencies.',
    'checkout_database_error' => 'The order could not be created. Please try again.',
    'login_for_history' => 'Sign in to view your order history.',
    'payment' => 'Payment',
    'payment_method' => 'Payment method',
    'payment_manual' => 'Bank transfer / manual payment',
    'payment_status' => 'Payment status',
    'payment_reference' => 'Payment reference',
    'update_payment' => 'Update payment',
    'payment_updated' => 'The payment status has been updated.',
    'payment_update_failed' => 'The payment status could not be updated.',
    'payment_status_pending' => 'Awaiting payment',
    'payment_status_paid' => 'Paid',
    'payment_status_failed' => 'Failed',
    'payment_status_cancelled' => 'Cancelled',
    'payment_status_refunded' => 'Refunded',
    'checkout_payment_error' => 'No valid payment method is currently available.',
    'pos' => 'Point of Sale',
    'pos_title' => 'Point of Sale',
    'pos_intro' => 'Create an in-person sale using the same catalogue, stock and orders as the online store.',
    'pos_search' => 'Search products...',
    'pos_ticket' => 'Current sale',
    'pos_empty' => 'No products in this sale yet.',
    'pos_payment' => 'Payment method',
    'pos_payment_cash' => 'Cash',
    'pos_payment_card' => 'Card',
    'pos_payment_cheque' => 'Cheque',
    'pos_payment_transfer' => 'Bank transfer',
    'pos_payment_other' => 'Other',
    'pos_customer_optional' => 'Customer (optional)',
    'pos_walk_in_customer' => 'Walk-in customer',
    'pos_validate' => 'Complete sale',
    'pos_sale_created' => 'Sale %s has been created.',
    'pos_sale_error' => 'The sale could not be created.',
    'pos_product_error' => 'One of the selected products is no longer available.',
    'pos_receipt' => 'Receipt',
    'pos_print_receipt' => 'Print receipt',
    'pos_new_sale' => 'New sale',
    'pos_source' => 'Source',
    'source_online' => 'Online',
    'source_pos' => 'POS',
    'pos_stock' => 'Stock: %d',
    'pos_digital' => 'Digital product',
    'pos_no_image' => 'No image',
    'pos_sku' => 'SKU: %s',
    'roadmap' => 'Roadmap',
    'roadmap_title' => 'Store development roadmap',
    'roadmap_intro' => 'Living technical roadmap for the international development of Geeklog Store.',
    'roadmap_file_missing' => 'The Store roadmap file could not be found.',
    'roadmap_updated' => 'Roadmap included with Store %s.',
    'version' => 'Version 0.6.5'
);
