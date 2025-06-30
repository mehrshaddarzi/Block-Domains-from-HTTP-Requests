<?php
/*
Plugin Name: Block Domains from HTTP Requests
Description: بلاک کردن درخواست‌های HTTP به دامنه‌های مشخص‌شده توسط کاربر.
Version: 1.0
Author: شما
*/

// افزودن فیلد تنظیمات به صفحه تنظیمات عمومی
add_action('admin_init', function () {
    add_settings_field(
        'block_domain',
        'دامنه‌های بلاک‌شده',
        'block_domain_field_html',
        'general'
    );

    register_setting('general', 'block_domain', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '',
    ]);
});

// خروجی HTML فیلد تنظیمات
function block_domain_field_html() {
    $value = get_option('block_domain', '');
    echo '<textarea name="block_domain" rows="10" cols="50" class="large-text code" placeholder="example.com&#10;api.example.org">' . esc_textarea($value) . '</textarea>';
    echo '<p class="description">در هر خط یک دامنه وارد کنید که می‌خواهید درخواست‌های HTTP به آن‌ها بلاک شود.</p>';
}

// فیلتر کردن درخواست‌های HTTP با استفاده از pre_http_request
add_filter('pre_http_request', function ($pre, $parsed_args, $url) {
    $blocked_domains_raw = get_option('block_domain', '');
    if (empty($blocked_domains_raw)) {
        return $pre; // اگر لیستی نیست، ادامه بده
    }

    // استخراج دامنه از URL
    $host = parse_url($url, PHP_URL_HOST);
    if (!$host) {
        return $pre;
    }

    // پردازش لیست دامنه‌های بلاک‌شده
    $blocked_domains = array_filter(array_map('trim', explode("\n", $blocked_domains_raw)));

    foreach ($blocked_domains as $domain) {
        if (stripos($host, $domain) !== false) {
            // بلاک کردن درخواست
            return new WP_Error('blocked_http_request', 'درخواست به دامنه‌ی مسدود شده انجام شد: ' . esc_html($domain));
        }
    }

    return $pre; // در غیر اینصورت، ادامه بده
}, 80, 3);
