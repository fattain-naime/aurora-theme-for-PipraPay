<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    $payment_details = pp_get_payment_link($paymentid);
    if ($payment_details['status'] !== true || empty($payment_details['response'][0])) {
        echo "<div style='max-width:540px;margin:40px auto;padding:24px;border:1px solid #eee;border-radius:18px;font-family:Inter,Arial,sans-serif;text-align:center;'>This payment link has expired.</div>";
        return;
    }

    $payment = $payment_details['response'][0];
    $payment_fields = pp_get_payment_link_items($paymentid);
    $settings = pp_get_settings();
    $site = $settings['response'][0] ?? [];

    $logo = $site['logo'] ?? '';
    if ($logo === '' || $logo === '--') {
        $logo = 'https://cdn.piprapay.com/media/logo.png';
    }
    $favicon = $site['favicon'] ?? 'https://cdn.piprapay.com/media/favicon.png';
    if ($favicon === '' || $favicon === '--') {
        $favicon = 'https://cdn.piprapay.com/media/favicon.png';
    }

    $currency = $payment['pl_currency'] ?? 'USD';
    $amount = number_format((float)$payment['pl_amount'], 2) . $currency;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($payment['pl_name']) ?> - <?= htmlspecialchars($site['site_name'] ?? 'PipraPay Merchant') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: <?= htmlspecialchars($settings['response'][0]['active_tab_color'] ?? '#6759ff') ?>;
            --accent-dark: <?= htmlspecialchars($settings['response'][0]['active_tab_text_color'] ?? '#fff') ?>;
            --text: #1d1e29;
            --muted: #6f7285;
            --border: #e7e8f2;
            --bg: #f5f6fb;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 32px 12px;
        }
        .payment-shell {
            max-width: 520px;
            margin: 0 auto;
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 25px 80px rgba(15,15,50,.08);
            overflow: hidden;
        }
        .hero {
            padding: 32px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
        }
        .hero img {
            height: 32px;
            margin-bottom: 18px;
        }
        .hero h1 {
            margin: 0;
            font-size: 1.8rem;
        }
        .hero p {
            margin: 8px 0 0;
            opacity: .9;
        }
        .amount-card {
            background: rgba(255,255,255,.15);
            border-radius: 18px;
            padding: 18px;
            margin-top: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .amount-label { font-size: .85rem; text-transform: uppercase; letter-spacing: .08em; opacity: .8; }
        .amount-value { font-size: 1.6rem; font-weight: 700; }
        .payment-body {
            padding: 32px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            font-size: .9rem;
        }
        input, select {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px 14px;
            font-size: 1rem;
            font-family: inherit;
            background: #fff;
            transition: border-color .2s ease;
        }
        input:focus, select:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(103,89,255,.12);
        }
        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
        }
        .btn-pay {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 16px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: var(--accent);
            cursor: pointer;
            margin-top: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .secure {
            margin-top: 20px;
            text-align: center;
            font-size: .9rem;
            color: var(--muted);
        }
        #response {
            margin-top: 16px;
        }
        .spinner {
            width: 1rem;
            height: 1rem;
            border: 0.15em solid rgba(255,255,255,.4);
            border-right-color: #fff;
            border-radius: 50%;
            animation: spin .65s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="payment-shell">
        <div class="hero">
            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
            <h1><?= htmlspecialchars($payment['pl_name']) ?></h1>
            <p><?= htmlspecialchars($payment['pl_description'] ?? 'Complete your secure payment') ?></p>
            <div class="amount-card">
                <div>
                    <div class="amount-label">Total due</div>
                    <div class="amount-value"><?= $amount ?></div>
                </div>
                <div style="text-align:right;">
                    <div class="amount-label">Reference</div>
                    <div style="font-weight:600;"><?= htmlspecialchars($payment['pl_id']) ?></div>
                </div>
            </div>
        </div>

        <div class="payment-body">
            <form id="aurora-payment-link-form" enctype="multipart/form-data">
                <input type="hidden" name="action" value="pp-invoice-payment-link">
                <input type="hidden" name="pp-paymentid" value="<?= htmlspecialchars($paymentid) ?>">

                <div class="grid-2">
                    <div class="form-group">
                        <label>Full name</label>
                        <input type="text" name="full-name" placeholder="Jane Doe" required>
                    </div>
                    <div class="form-group">
                        <label>Email or mobile</label>
                        <input type="text" name="email-mobile" placeholder="you@example.com" required>
                    </div>
                </div>

                <?php if ($payment_fields['status'] === true): ?>
                    <?php foreach ($payment_fields['response'] as $field): ?>
                        <div class="form-group">
                            <label><?= ucwords(str_replace('-', ' ', $field['pl_field_name'])) ?><?= $field['pl_is_require'] === 'yes' ? ' *' : '' ?></label>
                            <?php if ($field['pl_form_type'] === 'textarea'): ?>
                                <textarea name="<?= htmlspecialchars($field['pl_field_name']) ?>" rows="3" style="width:100%;border:1px solid var(--border);border-radius:12px;padding:12px;font-family:inherit;" <?= $field['pl_is_require'] === 'yes' ? 'required' : '' ?>></textarea>
                            <?php else: ?>
                                <input
                                    type="<?= htmlspecialchars($field['pl_form_type']) ?>"
                                    name="<?= $field['pl_form_type'] === 'file' ? htmlspecialchars($field['pl_field_name']) . '[]' : htmlspecialchars($field['pl_field_name']) ?>"
                                    <?= $field['pl_form_type'] === 'file' ? 'style="padding:10px;"' : '' ?>
                                    placeholder="<?= ucwords(str_replace('-', ' ', $field['pl_field_name'])) ?>"
                                    <?= $field['pl_is_require'] === 'yes' ? 'required' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div id="response"></div>

                <button class="btn-pay" id="aurora-paylink-btn">
                    <span>Pay <?= $amount ?></span>
                </button>
            </form>

            <div class="secure">
                <strong>PipraPay</strong> keeps this payment encrypted end-to-end.
            </div>
        </div>
    </div>

    <script>
        (function() {
            const form = document.getElementById('aurora-payment-link-form');
            const btn = document.getElementById('aurora-paylink-btn');
            if (!form || !btn) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span><span>Processing</span>';

                const formData = new FormData(form);
                fetch('', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.text())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Pay <?= $amount ?></span>';

                    try {
                        const response = JSON.parse(data);
                        if (response.status === false) {
                            document.getElementById('response').innerHTML =
                                '<div class="alert" style="padding:12px;border-radius:12px;background:#ffe8ea;color:#c6284f;">' + response.message + '</div>';
                        } else {
                            document.getElementById('response').innerHTML = '';
                            location.href = response.pp_url;
                        }
                    } catch (err) {
                        document.getElementById('response').innerHTML =
                            '<div class="alert" style="padding:12px;border-radius:12px;background:#ffe8ea;color:#c6284f;">Unexpected response. Please try again.</div>';
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = '<span>Pay <?= $amount ?></span>';
                    document.getElementById('response').innerHTML =
                        '<div class="alert" style="padding:12px;border-radius:12px;background:#ffe8ea;color:#c6284f;">Network error. Please retry.</div>';
                });
            });
        })();
    </script>
</body>
</html>
