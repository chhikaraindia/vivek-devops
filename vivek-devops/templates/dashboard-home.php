<?php
/**
 * Vivek DevOps Dashboard Home Template
 *
 * Variables available:
 * - $current_user (WP_User object)
 * - $features (array of feature data)
 */

// Safety checks
if (!defined('ABSPATH')) exit;
if (!isset($current_user) || !isset($features)) {
    echo '<div style="padding: 20px;">Error: Dashboard data not loaded properly.</div>';
    return;
}
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .vsc-dashboard {
            max-width: 1400px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .vsc-dashboard-header {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            color: white;
            padding: 40px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .vsc-dashboard-header-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .vsc-dashboard-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));
        }

        .vsc-dashboard-text {
            flex: 1;
        }

        .vsc-dashboard-header h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: 700;
        }

        .vsc-dashboard-header p {
            margin: 0;
            font-size: 16px;
            opacity: 0.9;
        }

        .vsc-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .vsc-stat-card {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            padding: 25px;
            text-align: center;
        }

        .vsc-stat-value {
            font-size: 28px;
            font-weight: 700;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            line-height: 1.2;
        }

        .vsc-stat-label {
            font-size: 12px;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.5;
        }

        .vsc-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .vsc-feature-card {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            padding: 24px;
            transition: all 0.3s ease;
        }

        .vsc-feature-card:hover {
            transform: translateY(-5px);
            border-color: #2a2a2a;
        }

        .vsc-feature-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .vsc-feature-name {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
            margin: 0;
        }

        .vsc-feature-status {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .vsc-feature-status.active {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #ffffff;
        }

        .vsc-feature-status.partial {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
        }

        .vsc-feature-status.pending {
            background: #1a1a1a;
            color: #999999;
        }

        .vsc-feature-description {
            color: #cccccc;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 12px 0;
        }

        .vsc-feature-version {
            font-size: 12px;
            color: #999999;
            font-weight: 500;
        }

        .vsc-info-box {
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-left: 3px solid #0ea5e9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .vsc-info-box h3 {
            margin: 0 0 10px 0;
            color: #ffffff;
            font-size: 16px;
        }

        .vsc-info-box p {
            margin: 0 0 10px 0;
            color: #cccccc;
            font-size: 14px;
            line-height: 1.6;
        }

        .vsc-info-box code {
            background: #1a1a1a;
            padding: 2px 8px;
            border-radius: 4px;
            color: #0ea5e9;
            font-size: 13px;
        }

        .vsc-info-box ul {
            margin: 10px 0 0 20px;
            color: #cccccc;
        }

        .vsc-info-box ul li {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="vsc-dashboard">
        <!-- Header -->
        <div class="vsc-dashboard-header">
            <div class="vsc-dashboard-header-content">
                <img src="<?php echo esc_url(VSC_URL . 'assets/images/logo-vsc-devops.png'); ?>"
                     alt="Vivek DevOps Logo"
                     class="vsc-dashboard-logo">
                <div class="vsc-dashboard-text">
                    <h1>🛡️ Vivek DevOps Security Layer</h1>
                    <p>Comprehensive security and development platform for developers</p>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="vsc-stats-grid">
            <div class="vsc-stat-card">
                <div class="vsc-stat-value"><?php
                    $active = array_filter($features, function($f) { return $f['status'] === 'active'; });
                    echo count($active);
                ?></div>
                <div class="vsc-stat-label">Active Features</div>
            </div>

            <div class="vsc-stat-card">
                <div class="vsc-stat-value"><?php
                    $pending = array_filter($features, function($f) { return $f['status'] === 'pending'; });
                    echo count($pending);
                ?></div>
                <div class="vsc-stat-label">Pending</div>
            </div>

            <div class="vsc-stat-card">
                <div class="vsc-stat-value"><?php echo VSC_VERSION; ?></div>
                <div class="vsc-stat-label">Current Version</div>
            </div>

            <div class="vsc-stat-card">
                <div class="vsc-stat-value"><?php echo esc_html($current_user->display_name); ?></div>
                <div class="vsc-stat-label">Logged In</div>
            </div>
        </div>

        <!-- Info Box -->
        <div class="vsc-info-box">
            <h3>📋 Quick Access</h3>
            <p><strong>Custom Login:</strong> <code><?php echo esc_url(site_url('/vsc')); ?></code></p>
            <p><strong>Custom Admin:</strong> <code><?php echo esc_url(site_url('/vsc-admin')); ?></code></p>
            <p style="margin-top: 15px;"><strong>Security Notes:</strong></p>
            <ul>
                <li>Never share your 2FA QR code or secret key</li>
                <li>Default /wp-login.php should redirect to honeypot (coming soon)</li>
                <li>Only support@chhikara.in has full admin access</li>
            </ul>
        </div>

        <!-- Features Grid -->
        <h2 style="margin-bottom: 20px; color: #ffffff;">Feature Status</h2>
        <div class="vsc-features-grid">
            <?php foreach ($features as $feature): ?>
                <div class="vsc-feature-card">
                    <div class="vsc-feature-header">
                        <h3 class="vsc-feature-name"><?php echo esc_html($feature['name']); ?></h3>
                        <span class="vsc-feature-status <?php echo esc_attr($feature['status']); ?>">
                            <?php echo esc_html($feature['status']); ?>
                        </span>
                    </div>
                    <p class="vsc-feature-description"><?php echo esc_html($feature['description']); ?></p>
                    <?php if ($feature['version']): ?>
                        <div class="vsc-feature-version">Version: <?php echo esc_html($feature['version']); ?></div>
                    <?php else: ?>
                        <div class="vsc-feature-version">Not yet implemented</div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
