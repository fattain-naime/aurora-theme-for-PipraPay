<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    $invoice_details = pp_get_invoice($invoice_id);
    if ($invoice_details['status'] !== true || empty($invoice_details['response'][0])) {
        echo "<div style='max-width:600px;margin:40px auto;padding:20px;border:1px solid #eee;border-radius:12px;font-family:Inter,Arial,sans-serif;text-align:center;'>This invoice is no longer available.</div>";
        return;
    }

    $invoice = $invoice_details['response'][0];
    $invoice_items = pp_get_invoice_items($invoice_id);
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

    $statusMap = [
        'paid'     => ['label' => 'Paid',     'class' => 'pill-paid',     'hint' => 'Payment received'],
        'unpaid'   => ['label' => 'Unpaid',   'class' => 'pill-due',      'hint' => 'Payment required'],
        'pending'  => ['label' => 'Pending',  'class' => 'pill-pending',  'hint' => 'Waiting for confirmation'],
        'refunded' => ['label' => 'Refunded', 'class' => 'pill-refunded', 'hint' => 'Amount refunded'],
        'canceled' => ['label' => 'Canceled', 'class' => 'pill-canceled', 'hint' => 'Invoice canceled'],
    ];
    $statusCode = strtolower($invoice['i_status']);
    $statusMeta = $statusMap[$statusCode] ?? $statusMap['pending'];

    $subtotal = 0;
    $total_discount = 0;
    $total_vat = 0;
    if ($invoice_items['status'] === true) {
        foreach ($invoice_items['response'] as $item) {
            $line_subtotal = (float)$item['amount'] * (float)$item['quantity'];
            $line_discount = min((float)$item['discount'], $line_subtotal);
            $line_net = $line_subtotal - $line_discount;
            $line_vat = $line_net * ((float)$item['vat'] / 100);

            $subtotal += $line_subtotal;
            $total_discount += $line_discount;
            $total_vat += $line_vat;
        }
    }

    $shipping_cost = isset($invoice['i_amount_shipping']) ? (float)$invoice['i_amount_shipping'] : 0;
    $currency = $invoice['i_currency'] ?? 'USD';
    $grand_total = round($subtotal - $total_discount + $total_vat + $shipping_cost, 2);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= htmlspecialchars($invoice['i_id']) ?> - <?= htmlspecialchars($site['site_name'] ?? 'PipraPay') ?></title>
    <link rel="icon" type="image/png" href="<?= htmlspecialchars($favicon) ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --accent: <?= htmlspecialchars($settings['response'][0]['active_tab_color'] ?? '#6759ff') ?>;
            --accent-dark: <?= htmlspecialchars($settings['response'][0]['active_tab_text_color'] ?? '#fff') ?>;
            --text: #1d1e29;
            --muted: #73768c;
            --border: #eceef4;
            --bg: #f5f6fb;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 32px 16px;
        }
        .invoice-shell {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 28px;
            box-shadow: 0 25px 80px rgba(15,15,50,.08);
            overflow: hidden;
        }
        .invoice-header {
            padding: 36px 40px;
            background: linear-gradient(135deg, var(--accent) 0%, var(--accent-dark) 100%);
            color: #fff;
            position: relative;
        }
        .invoice-header img {
            height: 36px;
        }
        .status-pill {
            position: absolute;
            top: 30px;
            right: 40px;
            padding: 8px 18px;
            border-radius: 999px;
            font-weight: 600;
            font-size: .9rem;
        }
        .pill-paid { background: rgba(25,214,161,.25); color: #0c9b71; }
        .pill-due { background: rgba(255,99,132,.25); color: #c73759; }
        .pill-pending { background: rgba(255,193,7,.25); color: #c78400; }
        .pill-refunded { background: rgba(91,96,222,.2); color: #2f35c8; }
        .pill-canceled { background: rgba(120,129,145,.25); color: #4b5161; }
        .invoice-title {
            margin-top: 24px;
            font-size: 2.2rem;
            font-weight: 700;
        }
        .subtitle {
            opacity: .85;
            margin-top: 4px;
        }
        .invoice-body {
            padding: 40px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
        }
        .card {
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 20px;
            background: #fff;
        }
        .card h4 {
            margin: 0 0 12px;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
        }
        .card p {
            margin: 4px 0;
            font-weight: 500;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 32px;
        }
        thead th {
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: var(--muted);
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }
        tbody td {
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
        }
        tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .amount-highlight {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        .totals {
            margin-top: 24px;
            padding: 24px;
            border: 1px dashed var(--border);
            border-radius: 16px;
            display: grid;
            gap: 12px;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-weight: 500;
        }
        .actions {
            margin-top: 30px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .btn {
            border: none;
            border-radius: 14px;
            padding: 14px 20px;
            font-weight: 600;
            cursor: pointer;
            font-size: .95rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-print {
            background: #f1f3f9;
            color: var(--text);
        }
        .btn-pay {
            background: var(--accent);
            color: #fff;
        }
        .secure-note {
            margin: 32px auto 10px;
            text-align: center;
            color: var(--muted);
            font-size: .9rem;
        }
        #response {
            margin-top: 18px;
        }
        @media (max-width: 720px) {
            .invoice-header {
                padding: 28px 24px 120px;
            }
            .status-pill {
                position: static;
                margin-top: 18px;
                display: inline-flex;
            }
            .invoice-body {
                padding: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-shell">
        <div class="invoice-header">
            <img src="<?= htmlspecialchars($logo) ?>" alt="Logo">
            <div class="status-pill <?= $statusMeta['class'] ?>"><?= htmlspecialchars($statusMeta['label']) ?></div>
            <div class="invoice-title">Invoice #<?= htmlspecialchars($invoice['i_id']) ?></div>
            <div class="subtitle"><?= htmlspecialchars($statusMeta['hint']) ?></div>
        </div>

        <div class="invoice-body">
            <div class="grid">
                <div class="card">
                    <h4>Issued by</h4>
                    <p><?= htmlspecialchars($site['site_name'] ?? 'PipraPay Merchant') ?></p>
                    <p><?= htmlspecialchars($site['support_email_address'] ?? '') ?></p>
                    <p><?= htmlspecialchars($site['support_phone_number'] ?? '') ?></p>
                </div>
                <div class="card">
                    <h4>Billed to</h4>
                    <p><?= htmlspecialchars($invoice['c_name']) ?></p>
                    <p><?= htmlspecialchars($invoice['c_email_mobile']) ?></p>
                </div>
                <div class="card">
                    <h4>Dates</h4>
                    <p>Issued: <?= htmlspecialchars(convertToReadableDate($invoice['created_at'])) ?></p>
                    <p>Due: <?= htmlspecialchars(convertToReadableDate($invoice['i_due_date'])) ?></p>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoice_items['status'] === true): ?>
                        <?php foreach ($invoice_items['response'] as $item): ?>
                            <?php $line_total = (float)$item['amount'] * (float)$item['quantity']; ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['description']) ?></strong>
                                    <?php if (!empty($item['note']) && $item['note'] !== '--'): ?>
                                        <div style="color:var(--muted);font-size:.85rem;margin-top:4px;"><?= htmlspecialchars($item['note']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= (int)$item['quantity'] ?></td>
                                <td class="text-right"><?= number_format((float)$item['amount'], 2) . $currency ?></td>
                                <td class="text-right"><?= number_format($line_total, 2) . $currency ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center" style="padding:24px 0;color:var(--muted);">No line items found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="totals">
                <div class="totals-row"><span>Subtotal</span><span><?= number_format($subtotal, 2) . $currency ?></span></div>
                <?php if ($total_discount > 0): ?>
                    <div class="totals-row"><span>Discount</span><span>-<?= number_format($total_discount, 2) . $currency ?></span></div>
                <?php endif; ?>
                <?php if ($total_vat > 0): ?>
                    <div class="totals-row"><span>VAT</span><span><?= number_format($total_vat, 2) . $currency ?></span></div>
                <?php endif; ?>
                <?php if ($shipping_cost > 0): ?>
                    <div class="totals-row"><span>Shipping</span><span><?= number_format($shipping_cost, 2) . $currency ?></span></div>
                <?php endif; ?>
                <div class="totals-row" style="font-size:1.2rem;">
                    <span>Total due</span>
                    <span class="amount-highlight"><?= number_format($grand_total, 2) . $currency ?></span>
                </div>
            </div>

            <?php if (!empty($invoice['i_note']) && $invoice['i_note'] !== '--'): ?>
                <div class="card" style="margin-top:24px;">
                    <h4>Notes</h4>
                    <p><?= nl2br(htmlspecialchars($invoice['i_note'])) ?></p>
                </div>
            <?php endif; ?>

            <div id="response"></div>

            <div class="actions">
                <button class="btn btn-print" onclick="window.print()">Print invoice</button>
                <?php if ($statusCode === 'unpaid'): ?>
                    <button class="btn btn-pay" id="aurora-pay">
                        <span>Pay <?= number_format($grand_total, 2) . $currency ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="secure-note">
        Payments are powered by <a href="https://piprapay.com" target="_blank" style="color:var(--accent);text-decoration:none;font-weight:600;">PipraPay</a> with 256-bit encryption.
    </div>

    <?php if ($statusCode === 'unpaid'): ?>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script>
            (function() {
                const btn = document.getElementById('aurora-pay');
                if (!btn) return;
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    btn.disabled = true;
                    btn.innerHTML = '<div style="display:inline-flex;align-items:center;gap:8px;"><span class="spinner-border spinner-border-sm"></span> Processing...</div>';

                    $.post('', { action: 'pp-invoice-link', 'pp-invoiceid': '<?= $invoice_id ?>' }, function(data) {
                        btn.disabled = false;
                        btn.innerHTML = 'Pay <?= number_format($grand_total, 2) . $currency ?>';

                        try {
                            const response = JSON.parse(data);
                            if (response.status === false) {
                                $('#response').html('<div class="alert alert-danger" style="margin-top:16px;">' + response.message + '</div>');
                            } else {
                                $('#response').html('');
                                location.href = response.pp_url;
                            }
                        } catch (err) {
                            $('#response').html('<div class="alert alert-danger" style="margin-top:16px;">Unable to start payment. Please try again.</div>');
                        }
                    });
                });
            })();
        </script>
        <style>
            .spinner-border {
                width: 1.1rem;
                height: 1.1rem;
                border: 0.15em solid rgba(255,255,255,.3);
                border-right-color: #fff;
                border-radius: 50%;
                animation: spin .65s linear infinite;
            }
            @keyframes spin {
                to { transform: rotate(360deg); }
            }
        </style>
    <?php endif; ?>
</body>
</html>
