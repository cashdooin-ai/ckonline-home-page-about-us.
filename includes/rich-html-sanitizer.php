<?php
/**
 * Rich-HTML sanitizer for admin-authored content rendered to every public
 * visitor (currently: colleges.description, via college-detail.php).
 *
 * Ported from cashdooin-ai/ckampus-dasboard's shared/includes/blog-sanitizer.php
 * (used there for the exact same problem: admin-authored HTML rendered raw
 * to the public) rather than reinvented, since that implementation is
 * already exercised in production there. Function names kept identical to
 * the source for easy comparison/future syncing.
 *
 * description is authored through admin/colleges.php by anyone with
 * ck_admin_auth - and college-detail.php renders it as raw HTML to every
 * visitor, logged in or not, once it contains any tag (Codex finding on
 * PR #20: strip_tags() alone only detects markup, it doesn't sanitize it -
 * a value like `<img src=x onerror=...>` would otherwise execute for every
 * reader). This sanitizes at render time (called right before output)
 * rather than at save time, so it protects every current and future reader
 * regardless of how a row got written - a description column with no
 * dedicated save-time validation of its own today, and no guarantee of one
 * tomorrow either.
 */

/** Tags allowed to remain in sanitized content, each with its own attribute allowlist below. */
function richHtmlAllowedTags(): array
{
    return [
        'h2', 'h3', 'h4', 'h5', 'p', 'ul', 'ol', 'li', 'strong', 'b', 'em', 'i', 'u',
        'a', 'img', 'br', 'hr', 'blockquote', 'code', 'pre', 'span', 'div',
        'table', 'thead', 'tbody', 'tr', 'td', 'th', 'iframe',
    ];
}

/**
 * Tags whose entire subtree (including text content) must be dropped, not
 * just unwrapped - the content itself is the dangerous part (script source,
 * stylesheet rules, embedded documents), unlike an arbitrary disallowed tag
 * such as a stray <section> where keeping the inner text is safe and reads
 * naturally after the wrapping tag is stripped.
 */
function richHtmlDangerousTags(): array
{
    return ['script', 'style', 'object', 'embed', 'form', 'svg', 'link', 'meta', 'noscript', 'template'];
}

function richHtmlAllowedAttributes(string $tag): array
{
    switch ($tag) {
        case 'a':
            return ['href', 'title', 'target', 'rel'];
        case 'img':
            return ['src', 'alt', 'title', 'loading', 'width', 'height'];
        case 'table':
            return ['colspan', 'rowspan'];
        case 'td':
            return ['colspan', 'rowspan', 'style'];
        case 'th':
            return ['colspan', 'rowspan', 'style'];
        case 'span':
            return ['style'];
        case 'iframe':
            return ['src', 'width', 'height', 'frameborder', 'allow', 'allowfullscreen', 'title'];
        default:
            return [];
    }
}

/**
 * True only for a `style` attribute value that is exactly one or two
 * semicolon-separated color/background-color declarations with a safe
 * value (#hex, rgb()/rgba(), or a plain CSS color keyword). Never a generic
 * CSS sanitizer: anything else (url(), expression(), @import,
 * position/behavior properties, etc.) is rejected outright.
 */
function richHtmlStyleAttributeIsSafe(string $style): bool
{
    $style = trim($style);
    if ($style === '') return false;
    $declarations = array_filter(array_map('trim', explode(';', $style)), fn($d) => $d !== '');
    if (!$declarations || count($declarations) > 2) return false;
    $colorValue = '(#[0-9a-fA-F]{3}|#[0-9a-fA-F]{6}|#[0-9a-fA-F]{8}|rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[\d.]+\s*)?\)|[a-zA-Z]{3,20})';
    foreach ($declarations as $decl) {
        if (!preg_match('/^(color|background-color|background)\s*:\s*' . $colorValue . '$/', $decl)) return false;
    }
    return true;
}

/** True if a URL is safe to place in href/src - no javascript:/data:/vbscript: schemes. */
function richHtmlUrlIsSafe(string $url): bool
{
    $url = trim($url);
    if ($url === '') return false;
    // Browsers strip ASCII tab/newline/CR while parsing a URL before ever
    // looking at the scheme, so "java\nscript:alert(1)" would otherwise
    // fail the scheme regex below (no match => treated as a safe relative
    // URL) even though a click reassembles it into a real javascript: URL.
    if (preg_match('/[\x00-\x1F\x7F]/', $url)) return false;
    if (!preg_match('#^([a-zA-Z][a-zA-Z0-9+.\-]*):#', $url, $m)) return true;
    $scheme = strtolower($m[1]);
    return in_array($scheme, ['http', 'https', 'mailto', 'tel'], true);
}

/**
 * True only for a YouTube/YouTube-nocookie/Vimeo *embed* URL - an iframe
 * src is a much bigger attack surface than href/img src even over https,
 * so this allowlists specific hosts and embed paths.
 */
function richHtmlIframeSrcIsSafe(string $src): bool
{
    $src = trim($src);
    if ($src === '') return false;
    $parts = parse_url($src);
    if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) return false;
    $host = strtolower($parts['host']);
    $path = $parts['path'] ?? '';
    $youtubeHosts = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com'];
    if (in_array($host, $youtubeHosts, true)) return (bool)preg_match('#^/embed/[A-Za-z0-9_-]{6,20}$#', $path);
    if ($host === 'player.vimeo.com') return (bool)preg_match('#^/video/\d+$#', $path);
    return false;
}

/**
 * Sanitizes admin-authored HTML for public rendering: strips every tag not
 * on the allowlist (dropping dangerous ones with their content entirely,
 * unwrapping harmless ones to keep their text), strips every attribute not
 * on that tag's own allowlist (which inherently removes all on*= event
 * handler attributes, style=, and anything else not explicitly permitted),
 * and blocks javascript:/data: URLs in href/src.
 */
function sanitizeRichHtml(string $html): string
{
    if (trim($html) === '') return '';

    $allowedTags = array_flip(richHtmlAllowedTags());
    $dangerousTags = array_flip(richHtmlDangerousTags());

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $wrapped = '<?xml encoding="utf-8"?><div id="__rich_html_sanitize_root__">' . $html . '</div>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $root = $doc->getElementById('__rich_html_sanitize_root__');
    if (!$root) return '';

    richHtmlSanitizeNode($doc, $root, $allowedTags, $dangerousTags);

    $out = '';
    foreach (iterator_to_array($root->childNodes) as $child) {
        $out .= $doc->saveHTML($child);
    }
    return $out;
}

/** Recursively sanitizes $node's children in place (mutates $doc's tree). */
function richHtmlSanitizeNode(DOMDocument $doc, DOMNode $node, array $allowedTags, array $dangerousTags): void
{
    $children = iterator_to_array($node->childNodes);

    foreach ($children as $child) {
        if ($child->nodeType === XML_COMMENT_NODE) {
            $node->removeChild($child);
            continue;
        }
        if ($child->nodeType !== XML_ELEMENT_NODE) {
            continue;
        }

        $tag = strtolower($child->nodeName);

        if (isset($dangerousTags[$tag])) {
            $node->removeChild($child);
            continue;
        }

        if (!isset($allowedTags[$tag])) {
            richHtmlSanitizeNode($doc, $child, $allowedTags, $dangerousTags);
            while ($child->firstChild) {
                $node->insertBefore($child->firstChild, $child);
            }
            $node->removeChild($child);
            continue;
        }

        $allowedAttrs = array_flip(richHtmlAllowedAttributes($tag));
        foreach (iterator_to_array($child->attributes ?? []) as $attr) {
            $attrName = strtolower($attr->nodeName);
            if (!isset($allowedAttrs[$attrName])) {
                $child->removeAttribute($attr->nodeName);
            }
        }

        if (($tag === 'span' || $tag === 'td' || $tag === 'th') && $child->hasAttribute('style') && !richHtmlStyleAttributeIsSafe($child->getAttribute('style'))) {
            $child->removeAttribute('style');
        }
        if ($tag === 'a' && $child->hasAttribute('href') && !richHtmlUrlIsSafe($child->getAttribute('href'))) {
            $child->removeAttribute('href');
        }
        if ($tag === 'img' && $child->hasAttribute('src') && !richHtmlUrlIsSafe($child->getAttribute('src'))) {
            $child->removeAttribute('src');
        }
        if ($tag === 'iframe' && $child->hasAttribute('src') && !richHtmlIframeSrcIsSafe($child->getAttribute('src'))) {
            $child->removeAttribute('src');
        }
        if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
            $child->setAttribute('rel', 'noopener noreferrer');
        }

        richHtmlSanitizeNode($doc, $child, $allowedTags, $dangerousTags);
    }
}
