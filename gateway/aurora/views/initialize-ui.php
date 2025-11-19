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
    $fee    = (float) ($payment['transaction_fee'] ?? 0);
    $total  = $amount + $fee;
    $currency = $payment['transaction_currency'] ?? 'USD';
    $reference = $payment['pp_id'] ?? $payment_id;

    $availableTabs = [];
    foreach ($gateway_tabs as $key => $tab) {
        if (!empty($tab['gateways']['response'])) {
            $availableTabs[] = $key;
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($siteName) ?> - Aurora Checkout</title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: <?= htmlspecialchars($settings['accent_color']) ?>;
            --accent-gradient: <?= htmlspecialchars($settings['accent_gradient']) ?>;
            --surface: #ffffff;
            --body-bg: #f4f5fb;
            --border: rgba(103,89,255,.15);
            --border-strong: rgba(15,16,38,.08);
            --text: #121327;
            --muted: #6d708c;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--body-bg);
            color: var(--text);
            padding: 32px 18px;
        }
        .aurora-shell {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: minmax(0, 380px) minmax(0, 1fr);
            gap: 28px;
        }
        .panel {
            background: var(--surface);
            border-radius: 24px;
            padding: 28px;
            box-shadow: 0 25px 60px rgba(15,15,40,.08);
        }
        .left-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .hero {
            background: var(--accent-gradient);
            color: #fff;
            border-radius: 24px;
            padding: 32px;
            position: relative;
            overflow: hidden;
            min-height: 220px;
        }
        .hero::after {
            content: "";
            position: absolute;
            right: -40px;
            top: -40px;
            width: 180px;
            height: 180px;
            background: rgba(255,255,255,.18);
            border-radius: 30% 70% 70% 30% / 30% 45% 55% 70%;
            filter: blur(6px);
        }
        .hero-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .hero-subtitle {
            opacity: .92;
            line-height: 1.6;
            max-width: 360px;
        }
        .hero-actions {
            position: absolute;
            top: 24px;
            right: 24px;
            display: flex;
            gap: 10px;
            z-index: 2;
        }
        .hero-header {
            position: relative;
            z-index: 1;
        }
        .hero-icon {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: rgba(255,255,255,.2);
            border: 1px solid rgba(255,255,255,.35);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            cursor: pointer;
            transition: transform .2s ease, background .2s ease;
        }
        .hero-icon:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,.35);
        }
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,.25);
            color: #fff;
            font-weight: 500;
            letter-spacing: .02em;
        }
        .cancel-link {
            color: rgba(255,255,255,.9);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: .9rem;
            text-decoration: none;
            margin-top: 18px;
            font-weight: 600;
        }
        .summary {
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px 20px 10px;
            background: #fff;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-weight: 500;
            color: var(--muted);
        }
        .summary-row span:last-child { color: var(--text); }
        .summary-total {
            font-size: 2.2rem;
            font-weight: 700;
            margin-top: 12px;
        }
        .support-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .support-item {
            display: flex;
            align-items: center;
            gap: 14px;
            border: 1px dashed var(--border);
            padding: 12px 16px;
            border-radius: 14px;
            color: inherit;
            text-decoration: none;
        }
        .support-item img { width: 34px; height: 34px; }
        .faq-item {
            border-bottom: 1px solid var(--border-strong);
            padding: 14px 0;
        }
        .faq-item:last-child { border-bottom: none; }
        .faq-question { font-weight: 600; }
        .faq-answer { color: var(--muted); margin-top: 6px; }
        .collapsible-panel {
            display: none;
        }
        .collapsible-panel.active {
            display: block;
            animation: fadeIn .2s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .right-column {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }
        .tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        .tab-btn {
            flex: 1;
            min-width: 150px;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 14px 18px;
            text-align: center;
            background: #fff;
            color: var(--muted);
            font-weight: 600;
            cursor: pointer;
            transition: all .2s ease;
        }
        .tab-btn.active {
            background: var(--accent);
            border-color: transparent;
            color: #fff;
            box-shadow: 0 15px 30px rgba(103,89,255,.3);
        }
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }
        @media (max-width: 960px) {
            .aurora-shell {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            body { padding: 24px 12px; }
            .tab-btn {
                flex: 1 1 calc(50% - 12px);
            }
            .gateway-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (max-width: 420px) {
            .gateway-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .gateway-card {
            background: #f8f8ff;
            border-radius: 16px;
            padding: 18px 12px;
            text-align: center;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all .2s ease;
        }
        .gateway-card img {
            width: 58px;
            height: 58px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .gateway-card:hover {
            border-color: var(--border);
            transform: translateY(-4px);
            background: #fff;
            box-shadow: 0 12px 30px rgba(103,89,255,.15);
        }
    </style>
</head>
<body>
    <div class="aurora-shell">
        <div class="left-column">
            <div class="hero panel" style="padding:0">
                <div style="padding:32px;position:relative;min-height:240px;" class="hero-header">
                    <div class="hero-actions">
                        <button class="hero-icon" type="button" data-panel="support-panel" title="Support">?</button>
                        <button class="hero-icon" type="button" data-panel="faq-panel" title="Quick help">!</button>
                    </div>
                    <div class="status-pill">
                        <span>Secure checkout</span>
                    </div>
                    <h1 class="hero-title"><?= htmlspecialchars($settings['hero_title']) ?></h1>
                    <p class="hero-subtitle"><?= htmlspecialchars($settings['hero_message']) ?></p>
                    <a class="cancel-link" href="?cancel">
                        <span>Cancel and start over</span>
                    </a>
                </div>
            </div>

            <div class="panel summary">
                <div class="summary-row">
                    <span>Amount</span>
                    <span><?= number_format($amount, 2) . ' ' . htmlspecialchars($currency) ?></span>
                </div>
                <div class="summary-row">
                    <span>Fee</span>
                    <span><?= number_format($fee, 2) . ' ' . htmlspecialchars($currency) ?></span>
                </div>
                <div class="summary-row">
                    <span>Reference</span>
                    <span>#<?= htmlspecialchars($reference) ?></span>
                </div>
                <div class="summary-total">
                    <?= number_format($total, 2) . ' ' . htmlspecialchars($currency) ?>
                </div>
            </div>

            <div class="panel collapsible-panel" id="support-panel">
                <h3 style="margin-top:0;">Support</h3>
                <p style="color:var(--muted); margin-top:4px;"><?= htmlspecialchars($settings['support_tagline']) ?></p>
                <div class="support-list">
                    <?php foreach ($support_links as $link): ?>
                        <?php $url = trim($link['url']); ?>
                        <?php if ($url !== '' && $url !== '--'): ?>
                            <a class="support-item" href="<?= htmlspecialchars($url) ?>" target="_blank" rel="noopener">
                                <img src="<?= htmlspecialchars($link['image']) ?>" alt="icon">
                                <div><?= htmlspecialchars($link['text']) ?></div>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (($settings['show_faq'] ?? 'yes') === 'yes' && !empty($faq_list['response'])): ?>
                <div class="panel collapsible-panel" id="faq-panel">
                    <h3 style="margin-top:0;">Quick help</h3>
                    <?php $count = 0; ?>
                    <?php foreach ($faq_list['response'] as $faq): ?>
                        <?php if ($count === 4) { break; } ?>
                        <div class="faq-item">
                            <div class="faq-question"><?= htmlspecialchars($faq['title'] ?? '') ?></div>
                            <div class="faq-answer"><?= htmlspecialchars($faq['description'] ?? '') ?></div>
                        </div>
                        <?php $count++; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="right-column">
            <div class="panel">
                <?php if (!empty($availableTabs)): ?>
                    <div class="tabs" id="aurora-tabs">
                        <?php $activeKey = $availableTabs[0]; ?>
                    <?php foreach ($availableTabs as $key): ?>
                        <button class="tab-btn <?= $key === $activeKey ? 'active' : '' ?>" data-target="tab-<?= $key ?>">
                            <?= htmlspecialchars($gateway_tabs[$key]['label']) ?>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php foreach ($availableTabs as $key): ?>
                    <?php $isActive = $key === $activeKey; ?>
                    <div class="gateway-grid" id="tab-<?= $key ?>" style="display: <?= $isActive ? 'grid' : 'none' ?>">
                        <?php foreach ($gateway_tabs[$key]['gateways']['response'] as $gateway): ?>
                            <div class="gateway-card" onclick="location.href='?method=<?= urlencode($gateway['plugin_slug']) ?>'">
                                <img src="<?= htmlspecialchars($gateway['plugin_logo']) ?>" alt="<?= htmlspecialchars($gateway['plugin_name']) ?>">
                                <div><?= htmlspecialchars($gateway['plugin_name']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="summary">
                    <p>No payment gateway is available for this amount yet. Please contact support.</p>
                </div>
            <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.tab-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(function(el){ el.classList.remove('active'); });
                document.querySelectorAll('.gateway-grid').forEach(function(grid){ grid.style.display = 'none'; });

                btn.classList.add('active');
                var target = document.getElementById(btn.getAttribute('data-target'));
                if (target) {
                    target.style.display = 'grid';
                }
            });
        });

        document.querySelectorAll('.hero-icon').forEach(function(icon){
            icon.addEventListener('click', function(){
                const target = document.getElementById(icon.getAttribute('data-panel'));
                if (!target) return;
                target.classList.toggle('active');
                document.querySelectorAll('.collapsible-panel').forEach(function(panel){
                    if (panel.id !== icon.getAttribute('data-panel')) {
                        panel.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>
</html>
