<?php
    if (!defined('pp_allowed_access')) {
        die('Direct access not allowed');
    }

    $theme_slug = $theme_slug ?? 'aurora';
    $settings = $settings ?? aurora_get_theme_settings();
?>

<form id="auroraThemeForm">
    <div class="page-header">
        <div class="row align-items-end">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title">Aurora theme settings</h1>
                <p class="text-muted mb-0">Tweak hero texts, accent colors, and FAQ visibility for the Aurora checkout.</p>
            </div>
        </div>
    </div>

    <input type="hidden" name="action" value="theme_update-submit">
    <input type="hidden" name="theme_slug" value="<?= htmlspecialchars($theme_slug) ?>">

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0">Copy</h2>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Hero title</label>
                        <input type="text" name="hero_title" class="form-control" value="<?= htmlspecialchars($settings['hero_title']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hero message</label>
                        <input type="text" name="hero_message" class="form-control" value="<?= htmlspecialchars($settings['hero_message']) ?>" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Support tagline</label>
                        <input type="text" name="support_tagline" class="form-control" value="<?= htmlspecialchars($settings['support_tagline']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Success CTA text</label>
                        <input type="text" name="success_cta" class="form-control" value="<?= htmlspecialchars($settings['success_cta']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Show FAQ block</label>
                        <select name="show_faq" class="form-select">
                            <?php $faqValue = $settings['show_faq'] ?? 'yes'; ?>
                            <option value="yes" <?= $faqValue === 'yes' ? 'selected' : '' ?>>Yes</option>
                            <option value="no" <?= $faqValue === 'no' ? 'selected' : '' ?>>No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0">Styles</h2>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-4">
                        <label class="form-label">Accent color</label>
                        <input type="color" name="accent_color" class="form-control form-control-color" value="<?= htmlspecialchars($settings['accent_color']) ?>">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Accent gradient (CSS)</label>
                        <input type="text" name="accent_gradient" class="form-control" value="<?= htmlspecialchars($settings['accent_gradient']) ?>">
                        <small class="text-muted">Example: linear-gradient(120deg, #6759ff 0%, #ff7ce5 100%)</small>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0">Behavior</h2>
                </div>
                <div class="card-body row g-4">
                    <div class="col-md-6">
                        <label class="form-label">Auto redirect after payment</label>
                        <select name="auto_redirect" class="form-select">
                            <?php $autoRedirect = $settings['auto_redirect'] ?? 'Disabled'; ?>
                            <option value="Enable" <?= $autoRedirect === 'Enable' ? 'selected' : '' ?>>Enable</option>
                            <option value="Disabled" <?= $autoRedirect === 'Disabled' ? 'selected' : '' ?>>Disabled</option>
                        </select>
                        <small class="text-muted">Automatically redirect customers to the merchant website after payment events.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title h4 mb-0">Save</h2>
                </div>
                <div class="card-body">
                    <div id="auroraResponse" class="mb-3"></div>
                    <button type="submit" class="btn btn-primary w-100" id="auroraSubmit">
                        Save settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    (function(){
        const form = document.getElementById('auroraThemeForm');
        const submitBtn = document.getElementById('auroraSubmit');
        const responseBox = document.getElementById('auroraResponse');

        form.addEventListener('submit', function(e){
            e.preventDefault();
            responseBox.className = '';
            responseBox.textContent = '';
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading...</span></div>';

            const formData = new FormData(form);
            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save settings';
                responseBox.className = data.status ? 'alert alert-success' : 'alert alert-danger';
                responseBox.textContent = data.message || (data.status ? 'Saved' : 'Failed to save');
            })
            .catch(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save settings';
                responseBox.className = 'alert alert-danger';
                responseBox.textContent = 'Unable to save settings. Please try again.';
            });
        });
    })();
</script>
