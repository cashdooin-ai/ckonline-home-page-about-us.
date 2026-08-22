<?php
// Shared CMS-settings loader for header.php/footer.php - so the contact
// info/social links/tagline admins edit on Site Settings > Footer actually
// show up somewhere, instead of header.php/footer.php hardcoding their own
// separate (and already visibly drifted) copies of the same strings.
//
// index.php already defines its own cms()/$cms/$cms_defaults for the
// homepage/carousel fields, populated BEFORE it includes header.php - the
// function_exists() guard means that copy wins on index.php (it has more
// fields) and this file is a no-op there, while every other page gets a
// working cms() for the first time via header.php's require of this file.
if (!function_exists('cms')) {
    $cms = [];
    $cms_defaults = [
        'footer_tagline'   => "India's trusted online college discovery platform. Compare 500+ UGC-approved universities and apply in minutes.",
        'contact_phone'    => '1800-123-4567',
        'contact_email'    => 'info@collegekampus.in',
        'contact_hours'    => 'Mon–Sat 9am–7pm',
        'social_facebook'  => 'https://www.facebook.com/collegekampus',
        'social_instagram' => 'https://www.instagram.com/collegekampus',
        'social_twitter'   => 'https://twitter.com/collegekampus',
        'social_linkedin'  => 'https://www.linkedin.com/company/collegekampus',
        'social_youtube'   => 'https://www.youtube.com/collegekampus',
    ];

    try {
        if (!defined('SITE_BASE')) {
            require_once dirname(__DIR__) . '/config/db.php';
        }
        $__pdo = getDB();
        $__rows = $__pdo->query("SELECT `key`, `value` FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($__rows as $k => $v) {
            $cms[$k] = $v;
        }
    } catch (Throwable $e) {
        // Table may not exist yet - silently use defaults
    }

    function cms(string $key) {
        global $cms, $cms_defaults;
        return $cms[$key] ?? $cms_defaults[$key] ?? '';
    }
}
