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
function format_question_content(?string $content): string
{
    if ($content === null || $content === '') {
        return '';
    }

    $hasTags = (bool)preg_match('/<[a-z\/][^>]*>/i', $content);
    if (!$hasTags) {
        return nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false));
    }

    $hasStructuralHtml = (bool)preg_match('/<\/?(p|br|h[1-6]|ul|ol|li|pre|code|blockquote|table|tbody|tr|td|th)\b/i', $content);
    if (!$hasStructuralHtml) {
        return nl2br(htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false));
    }

    $openRealLinks = 0;
    $pattern = '/<(\/?[a-zA-Z0-9_\-]+)([^>]*)>/s';
    return preg_replace_callback($pattern, function ($matches) use (&$openRealLinks) {
        $fullTag   = $matches[0];
        $tagName   = strtolower($matches[1]);
        $attrs     = $matches[2];
        $isClosing = str_starts_with($tagName, '/');
        $rawName   = $isClosing ? substr($tagName, 1) : $tagName;

        $allowedFormatting = [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
            'sub', 'sup', 'blockquote', 'pre', 'code', 'ol', 'ul', 'li',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'tbody', 'thead', 'tr', 'td', 'th'
        ];

        if (in_array($rawName, $allowedFormatting, true)) {
            return $fullTag;
        }

        if ($rawName === 'span' && ($isClosing || preg_match('/\b(style|class)=/i', $attrs))) {
            return $fullTag;
        }

        if ($rawName === 'img' && preg_match('/\bsrc=/i', $attrs)) {
            return $fullTag;
        }

        if ($rawName === 'a') {
            if (!$isClosing && preg_match('/\bhref=/i', $attrs)) {
                $openRealLinks++;
                return $fullTag;
            }
            if ($isClosing && $openRealLinks > 0) {
                $openRealLinks--;
                return $fullTag;
            }
        }

        return htmlspecialchars($fullTag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }, $content);
}

/**
 * Format option choice content for safe HTML display.
 * Prevents HTML tags (such as <a>, <div>, <p>) from being parsed into invisible empty DOM elements.
 */
function format_option_content(?string $option): string
{
    if ($option === null || $option === '') {
        return '';
    }
    // Allow standalone <img> tags
    if (preg_match('/^\s*<img\s+[^>]*src=[^>]*>\s*$/i', $option)) {
        return $option;
    }
    return htmlspecialchars($option, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
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
