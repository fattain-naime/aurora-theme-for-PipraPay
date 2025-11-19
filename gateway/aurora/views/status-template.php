<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    $siteInfo = $site_settings['response'][0] ?? [];
    $siteName = $siteInfo['site_name'] ?? 'PipraPay Merchant';
    $favicon = $siteInfo['favicon'] ?? 'https://cdn.piprapay.com/media/favicon.png';
    if ($favicon === '' || $favicon === '--') {
        $favicon = 'https://cdn.piprapay.com/media/favicon.png';
    }

    $amount = (float) ($payment['transaction_amount'] ?? 0);
    $total  = $amount + (float) ($payment['transaction_fee'] ?? 0);
    $currency = $payment['transaction_currency'] ?? 'USD';
    $reference = $payment['pp_id'] ?? $payment_id;

    $title = $status_title ?? 'Payment update';
    $description = $status_description ?? 'Your payment status has been updated.';
    $statusClass = strtolower($status_class ?? 'neutral');
    $redirectUrl = $payment['transaction_redirect_url'] ?? '';
    if ($redirectUrl === '--') {
        $redirectUrl = '';
    }
    $returnType = strtoupper($payment['transaction_return_type'] ?? 'GET');
    $shouldAutoRedirect = (($settings['auto_redirect'] ?? 'Disabled') === 'Enable') && !empty($redirectUrl);

    $palette = [
        'success' => ['tag' => '#19d6a1', 'bg' => 'linear-gradient(135deg, #1dc5a0 0%, #0f9c78 100%)'],
        'pending' => ['tag' => '#f1a545', 'bg' => 'linear-gradient(135deg, #f7c34c 0%, #f08b2c 100%)'],
        'failed'  => ['tag' => '#f2546b', 'bg' => 'linear-gradient(135deg, #ff7d92 0%, #df2b43 100%)'],
        'refunded'=> ['tag' => '#6d7bff', 'bg' => 'linear-gradient(135deg, #7f8bff 0%, #4e5bdb 100%)'],
        'canceled'=> ['tag' => '#8892a6', 'bg' => 'linear-gradient(135deg, #a6aec4 0%, #737c93 100%)'],
        'neutral' => ['tag' => '#6d7bff', 'bg' => 'linear-gradient(135deg, #7f8bff 0%, #4e5bdb 100%)'],
    ];
    $colors = $palette[$statusClass] ?? $palette['neutral'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?> - Payment status</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: <?= htmlspecialchars($colors['tag']) ?>;
            --gradient: <?= htmlspecialchars($colors['bg']) ?>;
            --text: #131435;
            --muted: #6c708c;
            --border: #eceef4;
            --bg: #f4f5fb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 32px 12px;
        }
        .status-shell {
            max-width: 960px;
            width: 100%;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 20px 80px rgba(15,17,50,.12);
            overflow: hidden;
        }
        .status-hero {
            background: var(--gradient);
            color: #fff;
            padding: 40px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .status-pill {
            align-self: flex-start;
            padding: 8px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,.2);
            font-weight: 600;
            letter-spacing: .04em;
            font-size: .85rem;
        }
        .status-title {
            font-size: 2.4rem;
            margin: 0;
        }
        .status-description {
            margin: 0;
            opacity: .9;
            line-height: 1.6;
            max-width: 560px;
        }
        .auto-redirect-note {
            margin: 6px 0 0;
            font-weight: 600;
        }
        .status-body {
            padding: 36px 40px 42px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 24px 32px;
        }
        .info-card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 18px 20px;
        }
        .info-label {
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
            margin-bottom: 4px;
        }
        .info-value {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text);
        }
        .actions {
            grid-column: 1 / -1;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 12px;
        }
        .actions form {
            display: inline-flex;
            margin: 0;
        }
        .btn {
            border: none;
            border-radius: 14px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary {
            background: var(--gradient);
            color: #fff;
        }
        .btn-secondary {
            background: #f3f4fb;
            color: var(--text);
            border: 1px solid var(--border);
        }
        .support-inline {
            grid-column: 1 / -1;
            color: var(--muted);
            font-size: .95rem;
        }
        @media (max-width: 600px) {
            .status-hero { padding: 32px; }
            .status-title { font-size: 2rem; }
            .status-body { padding: 32px; }
        }
    </style>
</head>
<body>
    <div class="status-shell">
        <div class="status-hero">
            <div class="status-pill"><?= strtoupper(htmlspecialchars($statusClass)) ?></div>
            <h1 class="status-title"><?= htmlspecialchars($title) ?></h1>
            <p class="status-description"><?= htmlspecialchars($description) ?></p>
            <?php if ($shouldAutoRedirect): ?>
                <p class="auto-redirect-note">Redirecting in <span id="status-countdown">4</span> seconds…</p>
            <?php endif; ?>
        </div>
        <div class="status-body">
            <div class="info-card">
                <div class="info-label">Total amount</div>
                <div class="info-value"><?= number_format($total, 2) . ' ' . htmlspecialchars($currency) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Reference</div>
                <div class="info-value">#<?= htmlspecialchars($reference) ?></div>
            </div>
            <div class="info-card">
                <div class="info-label">Status</div>
                <div class="info-value" style="text-transform: capitalize;"><?= htmlspecialchars($status ?? 'updated') ?></div>
            </div>

            <div class="actions">
                <?php if (!empty($redirectUrl)): ?>
                    <form id="status-redirect-form" action="<?= htmlspecialchars($redirectUrl) ?>" method="<?= $returnType === 'POST' ? 'POST' : 'GET' ?>">
                        <input type="hidden" name="pp_id" value="<?= htmlspecialchars($payment['pp_id']) ?>">
                        <button class="btn btn-primary" type="submit" id="status-redirect-btn">
                            <?= htmlspecialchars($settings['success_cta'] ?? 'Return to merchant') ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="btn btn-primary" href="<?= htmlspecialchars(pp_get_site_url()) ?>">
                        <?= htmlspecialchars($settings['success_cta'] ?? 'Return to merchant') ?>
                    </a>
                <?php endif; ?>
                <a class="btn btn-secondary" href="mailto:<?= htmlspecialchars($siteInfo['support_email_address'] ?? 'support@piprapay.com') ?>">Contact support</a>
            </div>
            <div class="support-inline">
                Need something else? Reply to the confirmation email or reach us through your dashboard support links.
            </div>
        </div>
    </div>
    <?php if ($shouldAutoRedirect): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var countdownEl = document.getElementById('status-countdown');
                var redirectBtn = document.getElementById('status-redirect-btn');
                if (!countdownEl || !redirectBtn) {
                    return;
                }
                var remaining = 4;
                var timer = setInterval(function() {
                    remaining--;
                    if (remaining <= 0) {
                        clearInterval(timer);
                        redirectBtn.click();
                    } else {
                        countdownEl.textContent = remaining;
                    }
                }, 1000);
            });
        </script>
    <?php endif; ?>
</body>
</html>
