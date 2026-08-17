<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.6.5                                                       |
// +---------------------------------------------------------------------------+
// | editor.php                                                               |
// |                                                                          |
// | Product editor and image gallery administration UI.                      |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

function store_admin_editor($product, $message)
{
    global $_CONF, $_STORE_CONF, $LANG_STORE;

    $id = isset($product['id']) ? (int) $product['id'] : 0;
    $currency = !empty($product['currency'])
        ? $product['currency']
        : (isset($_STORE_CONF['currency']) ? $_STORE_CONF['currency'] : 'EUR');
    $type = isset($product['product_type']) ? $product['product_type'] : 'physical';
    $active = !isset($product['active']) || (int) $product['active'] === 1;
    $imageValue = isset($product['image_url']) ? $product['image_url'] : '';

    $html = $message !== ''
        ? COM_showMessageText($message, $LANG_STORE['admin_title'])
        : '';

    $html .= '<div class="store-admin-editor">';
    $html .= '<div class="store-admin-editor-head">';
    $html .= '<div><h1>' . store_escape($id ? $LANG_STORE['edit_product'] : $LANG_STORE['add_product']) . '</h1>';
    if ($id > 0) {
        $html .= '<p class="store-meta">' . store_escape($LANG_STORE['edit_product_help']) . '</p>';
    }
    $html .= '</div>';
    $html .= '<div class="store-editor-actions">';
    $html .= '<a class="store-secondary-button" href="'
        . store_escape($_CONF['site_admin_url'] . '/plugins/store/index.php') . '">← '
        . store_escape($LANG_STORE['back_to_products']) . '</a>';
    if ($id > 0) {
        $html .= '<a class="store-secondary-button" href="' . store_escape(store_product_url($product))
            . '" target="_blank" rel="noopener">' . store_escape($LANG_STORE['view_product']) . '</a>';
    }
    $html .= '</div></div>';

    $html .= '<form class="store-form store-product-form" method="post">';
    $html .= '<input type="hidden" name="action" value="save">';
    $html .= '<input type="hidden" name="id" value="' . $id . '">';
    $html .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
        . store_escape(SEC_createToken()) . '">';

    $html .= '<div class="store-admin-columns">';
    $html .= '<section class="store-admin-panel">';
    $html .= '<h2>' . store_escape($LANG_STORE['product_information']) . '</h2>';

    $fields = array('name', 'slug', 'sku');
    foreach ($fields as $field) {
        $html .= '<label>' . store_escape($LANG_STORE[$field]) . '</label>';
        $html .= '<input type="text" name="' . $field . '" value="'
            . store_escape(isset($product[$field]) ? $product[$field] : '') . '"'
            . ($field === 'name' ? ' required' : '') . '>';
    }

    $html .= '<label>' . store_escape($LANG_STORE['category']) . '</label>'
        . store_admin_category_select(isset($product['category_id']) ? $product['category_id'] : 0);
    $html .= '<label>' . store_escape($LANG_STORE['short_description']) . '</label>'
        . '<textarea name="short_description" rows="4">'
        . store_escape(isset($product['short_description']) ? $product['short_description'] : '')
        . '</textarea>';
    $html .= '<label>' . store_escape($LANG_STORE['description']) . '</label>'
        . '<textarea name="description" rows="9">'
        . store_escape(isset($product['description']) ? $product['description'] : '')
        . '</textarea>';
    $html .= '</section>';

    $html .= '<aside class="store-admin-panel">';
    $html .= '<h2>' . store_escape($LANG_STORE['sales_settings']) . '</h2>';
    $html .= '<label>' . store_escape($LANG_STORE['price']) . '</label>'
        . '<input type="number" name="price" min="0" step="0.01" value="'
        . store_escape(isset($product['price']) ? $product['price'] : '0.00') . '">';
    $html .= '<label>' . store_escape($LANG_STORE['currency']) . '</label>'
        . '<input type="text" name="currency" maxlength="3" value="'
        . store_escape($currency) . '">';
    $html .= '<label>' . store_escape($LANG_STORE['product_type']) . '</label>';
    $html .= '<select name="product_type" id="store-product-type">'
        . '<option value="physical"' . ($type === 'physical' ? ' selected' : '') . '>'
        . store_escape($LANG_STORE['physical']) . '</option>'
        . '<option value="digital"' . ($type === 'digital' ? ' selected' : '') . '>'
        . store_escape($LANG_STORE['digital']) . '</option></select>';
    $html .= '<div id="store-stock-row"><label>' . store_escape($LANG_STORE['stock']) . '</label>'
        . '<input type="number" name="stock" min="0" step="1" value="'
        . (isset($product['stock']) ? (int) $product['stock'] : 0) . '">'
        . '<p class="store-meta">' . store_escape($LANG_STORE['stock_physical_only']) . '</p></div>';
    $html .= '<label class="store-checkbox"><input type="checkbox" name="active" value="1"'
        . ($active ? ' checked' : '') . '> ' . store_escape($LANG_STORE['active']) . '</label>';
    $html .= '</aside>';
    $html .= '</div>';

    $html .= '<section class="store-admin-panel store-primary-image-panel">';
    $html .= '<h2>' . store_escape($LANG_STORE['primary_image']) . '</h2>';
    $html .= '<div class="store-primary-image-layout">';
    $html .= '<div id="store-image-preview-wrap" class="store-primary-image-preview"'
        . ($imageValue === '' ? ' style="display:none"' : '') . '>';
    $html .= '<img id="store-image-preview" src="' . store_escape($imageValue) . '" alt="">';
    $html .= '</div>';
    $html .= '<div class="store-primary-image-controls">';
    $html .= '<label>' . store_escape($LANG_STORE['image_url']) . '</label>';
    $html .= '<div class="store-image-picker"><input id="store-image-url" type="url" name="image_url" value="'
        . store_escape($imageValue) . '">';
    if (store_filemanager_available()) {
        $html .= '<button type="button" id="store-filemanager-button" data-filemanager-url="'
            . store_escape(store_filemanager_url()) . '">'
            . store_escape($LANG_STORE['choose_image']) . '</button>';
    }
    $html .= '</div>';
    $html .= '<p class="store-meta">' . store_escape($LANG_STORE['image_url_help']) . '</p>';
    $html .= '</div></div></section>';

    $html .= '<div class="store-admin-savebar">';
    $html .= '<div class="store-save-actions">';
    $html .= '<button type="submit" class="store-primary-button">' . store_escape($LANG_STORE['save']) . '</button>';
    $html .= '<button type="submit" name="save_and_close" value="1" class="store-secondary-button">'
        . store_escape($LANG_STORE['save_and_close']) . '</button>';
    $html .= '</div>';
    $html .= '<a class="store-admin-back-link" href="'
        . store_escape($_CONF['site_admin_url'] . '/plugins/store/index.php') . '">← '
        . store_escape($LANG_STORE['back_to_products']) . '</a>';
    $html .= '</div>';
    $html .= '</form>';

    if ($id > 0) {
        $html .= '<section class="store-admin-panel store-gallery-manager">';
        $html .= '<div class="store-gallery-manager-head"><div><h2>'
            . store_escape($LANG_STORE['product_images']) . '</h2>';
        $html .= '<p class="store-meta">' . store_escape($LANG_STORE['multiple_images_help']) . '</p></div>';
        if (store_filemanager_available()) {
            $html .= '<button type="button" id="store-gallery-filemanager-button" class="store-secondary-button" data-filemanager-url="'
                . store_escape(store_filemanager_url()) . '">'
                . store_escape($LANG_STORE['choose_images']) . '</button>';
        }
        $html .= '</div>';

        $html .= '<form method="post" class="store-form" id="store-gallery-add-form">';
        $html .= '<input type="hidden" name="action" value="add_images">';
        $html .= '<input type="hidden" name="product_id" value="' . $id . '">';
        $html .= '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
            . store_escape(SEC_createToken()) . '">';
        $html .= '<label>' . store_escape($LANG_STORE['selected_images']) . '</label>';
        $html .= '<textarea id="store-gallery-image-urls" name="gallery_image_urls" rows="5" '
            . 'placeholder="https://...&#10;https://..."></textarea>';
        $html .= '<p class="store-meta">' . store_escape($LANG_STORE['selected_images_help']) . '</p>';
        $html .= '<div id="store-pending-gallery" class="store-pending-gallery"></div>';
        $html .= '<label class="store-checkbox"><input type="checkbox" name="make_primary" value="1"> '
            . store_escape($LANG_STORE['first_image_primary']) . '</label>';
        $html .= '<button type="submit" class="store-primary-button">'
            . store_escape($LANG_STORE['add_selected_images']) . '</button>';
        $html .= '</form>';

        $images = store_get_product_images($id);
        if ($images) {
            $html .= '<div class="store-gallery-admin-grid">';
            foreach ($images as $image) {
                $html .= '<article class="store-gallery-admin-card">';
                $html .= '<div class="store-gallery-admin-image"><img src="'
                    . store_escape($image['image_url']) . '" alt=""></div>';
                if ((int) $image['is_primary'] === 1) {
                    $html .= '<span class="store-primary-badge">'
                        . store_escape($LANG_STORE['primary_image']) . '</span>';
                }
                $html .= '<div class="store-gallery-admin-actions">';
                if ((int) $image['is_primary'] !== 1) {
                    $html .= '<form method="post"><input type="hidden" name="action" value="set_primary_image">'
                        . '<input type="hidden" name="product_id" value="' . $id . '">'
                        . '<input type="hidden" name="image_id" value="' . (int) $image['id'] . '">'
                        . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
                        . store_escape(SEC_createToken()) . '">'
                        . '<button type="submit" class="store-action-button store-action-primary">' . store_escape($LANG_STORE['make_primary']) . '</button></form>';
                }
                $html .= '<form method="post"><input type="hidden" name="action" value="delete_image">'
                    . '<input type="hidden" name="product_id" value="' . $id . '">'
                    . '<input type="hidden" name="image_id" value="' . (int) $image['id'] . '">'
                    . '<input type="hidden" name="' . CSRF_TOKEN . '" value="'
                    . store_escape(SEC_createToken()) . '">'
                    . '<button type="submit" class="store-action-button store-action-delete" onclick="return confirm(\''
                    . store_escape($LANG_STORE['confirm_delete_image']) . '\')">'
                    . store_escape($LANG_STORE['delete']) . '</button></form>';
                $html .= '</div></article>';
            }
            $html .= '</div>';
        }
        $html .= '</section>';
        $html .= '<p class="store-editor-footer-nav"><a class="store-secondary-button" href="'
            . store_escape($_CONF['site_admin_url'] . '/plugins/store/index.php') . '">← '
            . store_escape($LANG_STORE['back_to_products']) . '</a></p>';
    }

    $html .= '</div>';

    $html .= '<script>(function(){'
        . 'var field=document.getElementById("store-image-url");'
        . 'var preview=document.getElementById("store-image-preview");'
        . 'var wrap=document.getElementById("store-image-preview-wrap");'
        . 'var button=document.getElementById("store-filemanager-button");'
        . 'var galleryField=document.getElementById("store-gallery-image-urls");'
        . 'var galleryButton=document.getElementById("store-gallery-filemanager-button");'
        . 'var pending=document.getElementById("store-pending-gallery");'
        . 'var typeField=document.getElementById("store-product-type");'
        . 'var stockRow=document.getElementById("store-stock-row");'
        . 'var pickerWindow=null;var galleryPickerWindow=null;'
        . 'function updatePreview(){if(!field||!preview||!wrap){return;}var v=field.value||"";preview.src=v;wrap.style.display=v?"block":"none";}'
        . 'function splitUrls(){if(!galleryField){return [];}var raw=galleryField.value.split(/\\r?\\n|,/);var out=[];for(var i=0;i<raw.length;i++){var v=raw[i].replace(/^\\s+|\\s+$/g,"");if(v&&out.indexOf(v)===-1){out.push(v);}}return out;}'
        . 'function addGalleryUrl(url){if(!galleryField||!url){return;}var urls=splitUrls();if(urls.indexOf(url)===-1){urls.push(url);}galleryField.value=urls.join("\\n");renderPending();}'
        . 'function renderPending(){if(!pending){return;}while(pending.firstChild){pending.removeChild(pending.firstChild);}var urls=splitUrls();for(var i=0;i<urls.length;i++){var box=document.createElement("div");box.className="store-pending-image";var img=document.createElement("img");img.src=urls[i];img.alt="";var num=document.createElement("span");num.appendChild(document.createTextNode(String(i+1)));box.appendChild(img);box.appendChild(num);pending.appendChild(box);}}'
        . 'function updateStock(){if(!typeField||!stockRow){return;}stockRow.style.display=typeField.value==="digital"?"none":"block";}'
        . 'if(field){field.addEventListener("change",updatePreview);field.addEventListener("input",updatePreview);}'
        . 'if(galleryField){galleryField.addEventListener("change",renderPending);galleryField.addEventListener("input",renderPending);renderPending();}'
        . 'if(typeField){typeField.addEventListener("change",updateStock);updateStock();}'
        . 'if(button){button.addEventListener("click",function(){if(pickerWindow&&!pickerWindow.closed){pickerWindow.focus();return;}pickerWindow=window.open(button.getAttribute("data-filemanager-url"),"store_filemanager","width=1100,height=760,resizable=yes,scrollbars=yes");if(pickerWindow){pickerWindow.focus();}});}'
        . 'if(galleryButton){galleryButton.addEventListener("click",function(){if(galleryPickerWindow&&!galleryPickerWindow.closed){galleryPickerWindow.focus();return;}galleryPickerWindow=window.open(galleryButton.getAttribute("data-filemanager-url"),"store_gallery_filemanager","width=1100,height=760,resizable=yes,scrollbars=yes");if(galleryPickerWindow){galleryPickerWindow.focus();}});}'
        . 'window.addEventListener("message",function(event){if(event.origin!==window.location.origin){return;}var data=event.data;if(!data||data.source!=="richfilemanager"||!data.preview_url){return;}if(galleryPickerWindow&&event.source===galleryPickerWindow&&galleryField){addGalleryUrl(data.preview_url);if(galleryPickerWindow&&!galleryPickerWindow.closed){galleryPickerWindow.focus();}return;}if(field){field.value=data.preview_url;updatePreview();}if(pickerWindow&&!pickerWindow.closed){pickerWindow.close();}else if(event.source&&typeof event.source.close==="function"){try{event.source.close();}catch(e){}}pickerWindow=null;});'
        . '})();</script>';

    return $html;
}
