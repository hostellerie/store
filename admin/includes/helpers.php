<?php

/* Reminder: always indent with 4 spaces (no tabs). */
// +---------------------------------------------------------------------------+
// | Store Plugin 0.7.0                                                       |
// +---------------------------------------------------------------------------+
// | helpers.php                                                              |
// |                                                                          |
// | Administration helper functions and Markdown rendering.                  |
// +---------------------------------------------------------------------------+
// | Copyright (C) 2026 by Geeklog Store contributors                         |
// +---------------------------------------------------------------------------+
// | Licensed under the GNU General Public License version 2 or later.         |
// +---------------------------------------------------------------------------+

/**
 * Redirect to Store administration, optionally keeping a product open.
 *
 * @param string $message
 * @param int    $productId
 */
function store_admin_redirect($message, $productId = 0)
{
    global $_CONF;

    $url = $_CONF['site_admin_url'] . '/plugins/store/index.php';
    if ((int) $productId > 0) {
        $url .= '?action=edit&id=' . (int) $productId;
        $url .= '&notice=' . rawurlencode($message);
    } else {
        $url .= '?notice=' . rawurlencode($message);
    }

    header('Location: ' . $url);
    exit;
}

function store_admin_category_select($selected)
{
    global $LANG_STORE;

    $html = '<select name="category_id">'
        . '<option value="0">' . store_escape($LANG_STORE['no_category']) . '</option>';

    foreach (store_get_categories(false) as $category) {
        $id = (int) $category['id'];
        $html .= '<option value="' . $id . '"'
            . ($id === (int) $selected ? ' selected' : '') . '>'
            . store_escape($category['name']) . '</option>';
    }

    return $html . '</select>';
}

/**
 * Render the bundled Store roadmap Markdown without external dependencies.
 *
 * The source file ships with Store and is escaped before the small supported
 * Markdown subset is converted to HTML.
 *
 * @param string $file
 * @return string
 */
function store_admin_render_markdown($file)
{
    if (!is_readable($file)) {
        return '';
    }

    $lines = file($file, FILE_IGNORE_NEW_LINES);
    if (!is_array($lines)) {
        return '';
    }

    $html = '';
    $paragraph = array();
    $listType = '';
    $inCode = false;
    $code = array();

    $inline = function ($text) {
        $text = store_escape($text);
        $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
        $text = preg_replace('/\\*\\*([^*]+)\\*\\*/', '<strong>$1</strong>', $text);
        return $text;
    };

    $flushParagraph = function () use (&$html, &$paragraph, $inline) {
        if ($paragraph) {
            $html .= '<p>' . $inline(implode(' ', $paragraph)) . '</p>';
            $paragraph = array();
        }
    };
    $closeList = function () use (&$html, &$listType) {
        if ($listType !== '') {
            $html .= '</' . $listType . '>';
            $listType = '';
        }
    };

    foreach ($lines as $line) {
        if (preg_match('/^```/', $line)) {
            $flushParagraph();
            $closeList();
            if ($inCode) {
                $html .= '<pre><code>' . store_escape(implode("\n", $code)) . '</code></pre>';
                $code = array();
                $inCode = false;
            } else {
                $inCode = true;
            }
            continue;
        }
        if ($inCode) {
            $code[] = $line;
            continue;
        }
        if (trim($line) === '') {
            $flushParagraph();
            $closeList();
            continue;
        }
        if (preg_match('/^(#{1,4})\\s+(.+)$/', $line, $match)) {
            $flushParagraph();
            $closeList();
            $level = strlen($match[1]);
            $html .= '<h' . $level . '>' . $inline($match[2]) . '</h' . $level . '>';
            continue;
        }
        if (preg_match('/^>\\s?(.*)$/', $line, $match)) {
            $flushParagraph();
            $closeList();
            $html .= '<blockquote>' . $inline($match[1]) . '</blockquote>';
            continue;
        }
        if (preg_match('/^[-*_]{3,}\\s*$/', $line)) {
            $flushParagraph();
            $closeList();
            $html .= '<hr>';
            continue;
        }
        if (preg_match('/^\\s*[-*]\\s+(.+)$/', $line, $match)) {
            $flushParagraph();
            if ($listType !== 'ul') {
                $closeList();
                $html .= '<ul>';
                $listType = 'ul';
            }
            $html .= '<li>' . $inline($match[1]) . '</li>';
            continue;
        }
        if (preg_match('/^\\s*\\d+\\.\\s+(.+)$/', $line, $match)) {
            $flushParagraph();
            if ($listType !== 'ol') {
                $closeList();
                $html .= '<ol>';
                $listType = 'ol';
            }
            $html .= '<li>' . $inline($match[1]) . '</li>';
            continue;
        }
        $paragraph[] = trim($line);
    }

    if ($inCode && $code) {
        $html .= '<pre><code>' . store_escape(implode("\n", $code)) . '</code></pre>';
    }
    $flushParagraph();
    $closeList();

    return $html;
}
