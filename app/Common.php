<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
/**
 * Format question content for safe HTML display.
 * Automatically escapes HTML tag mentions (e.g. <a>, <div>) while preserving
 * valid Quill rich-text formatting tags (such as <p>, <br>, <b>, <i>, <pre>, <code>, <img>, etc.).
 */
/**
 * Format question content for safe and accurate display.
 * - Strips internal [hide]...[/hide] question tags.
 * - Preserves Quill rich-text formatting tags (<p>, <pre>, <code>, <img>, etc.) if it is a rich text block.
 * - Normalizes plain text / Aiken questions: decodes any entities and escapes all HTML tags
 *   so that tag mentions (<p>, <h1>, <div>, <ol>, etc.) render visibly as text instead of being parsed as DOM elements.
 */
function format_question_content(?string $content): string
{
    if ($content === null || $content === '') {
        return '';
    }

    // 1. Strip [hide]...[/hide] tags (including [hide 6.27] tags)
    $content = preg_replace('/\[hide(?:\s+[^\]]*)?\][\s\S]*?\[\/hide\]\s*/i', '', $content);
    $content = preg_replace('/\[\/?hide(?:\s+[^\]]*)?\]\s*/i', '', $content);
    $content = trim($content);

    if ($content === '') {
        return '';
    }

    // 2. Detect Quill Rich-Text HTML
    $isQuillHtml = (bool)preg_match('/^<(p|h[1-6]|pre|blockquote|ul|ol|table)\b/i', $content) &&
                   (bool)preg_match('/<\/(p|h[1-6]|pre|blockquote|ul|ol|table)>\s*$/i', $content);

    if ($isQuillHtml) {
        return $content;
    }

    // 3. Plain Text / Aiken question:
    // Decode pre-existing entities (e.g. &lt;title&gt; -> <title>) to get raw text
    $decoded = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Preserve <img> tags if present
    if (preg_match('/<img\s+[^>]*src=[^>]*>/i', $decoded)) {
        $images = [];
        $withPlaceholders = preg_replace_callback('/<img\s+[^>]*src=[^>]*>/i', function ($m) use (&$images) {
            $images[] = $m[0];
            return '___IMG_PLACEHOLDER_' . (count($images) - 1) . '___';
        }, $decoded);

        $escaped = nl2br(htmlspecialchars($withPlaceholders, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        foreach ($images as $idx => $img) {
            $escaped = str_replace('___IMG_PLACEHOLDER_' . $idx . '___', $img, $escaped);
        }
        return $escaped;
    }

    // Escape all HTML special chars so that <p>, <h1>, <div> are shown as text, not DOM tags
    return nl2br(htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
}

/**
 * Format option choice content for safe HTML display.
 * - Strips [hide] tags if any.
 * - Allows standalone <img> tags.
 * - Normalizes HTML entities and escapes so options like <h1>, <p>, &lt;ol&gt; display visibly.
 */
function format_option_content(?string $option): string
{
    if ($option === null || $option === '') {
        return '';
    }

    $option = preg_replace('/\[hide(?:\s+[^\]]*)?\][\s\S]*?\[\/hide\]\s*/i', '', $option);
    $option = preg_replace('/\[\/?hide(?:\s+[^\]]*)?\]\s*/i', '', $option);
    $option = trim($option);

    // Allow standalone <img> tags
    if (preg_match('/^\s*<img\s+[^>]*src=[^>]*>\s*$/i', $option)) {
        return $option;
    }

    $decoded = html_entity_decode($option, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return htmlspecialchars($decoded, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Format question preview for table lists (preserves HTML tag mentions like <a>, <div> as visible text).
 */
function format_preview_content(?string $content): string
{
    if ($content === null || $content === '') {
        return '';
    }
    $formatted = format_question_content($content);
    return strip_tags($formatted, '<img><code><pre><b><i><strong>');
}

/**
 * Clean text for Aiken export while preserving HTML tag mentions.
 */
function aiken_export_text(?string $text): string
{
    if ($text === null || $text === '') {
        return '';
    }
    $text = preg_replace('/<br\s*\/?>/i', "\n", $text);
    $text = preg_replace('/<\/p>/i', "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim(preg_replace('/\s+/', ' ', $text));
}
