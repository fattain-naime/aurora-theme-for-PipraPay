<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    if (isset($_GET['cancel']) && !empty($payment_id ?? '')) {
        pp_set_transaction_status($payment_id, 'failed');
    }

    function aurora_theme_slug() {
        return 'aurora';
    }

    function aurora_theme_defaults() {
        return [
            'accent_color'    => '#6759ff',
            'accent_gradient' => 'linear-gradient(120deg, #6759ff 0%, #ff7ce5 100%)',
            'hero_title'      => 'Complete your payment',
            'hero_message'    => 'Aurora keeps your checkout short, clean, and fully secure.',
            'support_tagline' => 'Need help? We are online 24/7.',
            'success_cta'     => 'Return to merchant',
            'show_faq'        => 'yes',
            'auto_redirect'   => 'Disabled'
        ];
    }

    function aurora_get_theme_settings() {
        $saved = pp_get_theme_setting(aurora_theme_slug());
        if (!is_array($saved)) {
            $saved = [];
        }

        return array_merge(aurora_theme_defaults(), $saved);
    }

    function aurora_render_view($viewFile, array $data = []) {
        $path = __DIR__ . '/views/' . $viewFile;
        if (!file_exists($path)) {
            echo '<!-- Aurora view ' . htmlspecialchars($viewFile) . ' not found -->';
            return;
        }

        extract($data, EXTR_SKIP);
        include $path;
    }

    function aurora_build_gateway_tabs($payment_id) {
        $categories = [
            ['key' => 'mobile', 'label' => 'Mobile Banking', 'category' => 'Mobile Banking'],
            ['key' => 'ibanking', 'label' => 'Internet Banking', 'category' => 'IBanking'],
            ['key' => 'international', 'label' => 'International', 'category' => 'International']
        ];

        $tabs = [];
        foreach ($categories as $category) {
            $tabs[$category['key']] = [
                'label'    => $category['label'],
                'gateways' => pp_get_payment_gateways($category['category'], $payment_id)
            ];
        }

        return $tabs;
    }

    function aurora_checkout_load($payment_id) {
        $transaction = pp_get_transation($payment_id);
        if ($transaction['status'] !== true || empty($transaction['response'][0])) {
            echo "<div class='alert alert-danger m-4'>Transaction not found.</div>";
            return;
        }

        $payment       = $transaction['response'][0];
        $settings      = aurora_get_theme_settings();
        $site_settings = pp_get_settings();
        $faq_list      = pp_get_faq();
        $support_links = pp_get_support_links();
        $gateway_tabs  = aurora_build_gateway_tabs($payment_id);

        $context = [
            'payment_id'    => $payment_id,
            'payment'       => $payment,
            'settings'      => $settings,
            'site_settings' => $site_settings,
            'faq_list'      => $faq_list,
            'support_links' => $support_links,
            'gateway_tabs'  => $gateway_tabs
        ];

        $status = strtolower($payment['transaction_status']);

        if ($status === 'initialize' && isset($_GET['method'])) {
            $rawMethod = explode('/', $_GET['method'])[0];
            $cleanMethod = preg_replace('/[^a-z0-9_-]/i', '', $rawMethod);

            if (!empty($cleanMethod)) {
                payment_gateway_include($cleanMethod, $payment_id);
                return;
            }
        }

        $viewMap = [
            'initialize' => 'initialize-ui.php',
            'pending'    => 'pending-ui.php',
            'completed'  => 'completed-ui.php',
            'failed'     => 'failed-ui.php',
            'refunded'   => 'refunded-ui.php'
        ];

        $view = $viewMap[$status] ?? 'status-generic.php';
        $context['status'] = $status;

        aurora_render_view($view, $context);
    }

    function aurora_admin_page() {
        $context = [
            'theme_slug' => aurora_theme_slug(),
            'settings'   => aurora_get_theme_settings()
        ];

        aurora_render_view('admin-ui.php', $context);
    }
?>
