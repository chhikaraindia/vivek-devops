<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Secure Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: #0a0a0a;
            color: #e0e0e0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .login-header p {
            font-size: 14px;
            color: #888;
        }

        .error-message {
            background: #2a1a1a;
            border: 1px solid #ff4444;
            border-radius: 4px;
            color: #ff6666;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #b0b0b0;
            font-size: 14px;
            font-weight: 500;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            color: #e0e0e0;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #4a92ff, #2e50bf);
            transform: translateY(-1px);
        }

        .submit-btn:active {
            background: linear-gradient(135deg, #2a72df, #0e30af);
            transform: translateY(0);
        }

        .login-footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #1a2a1a;
            border: 1px solid #2a4a2a;
            border-radius: 4px;
            padding: 8px 12px;
            margin-top: 20px;
            font-size: 12px;
            color: #6ad66a;
        }

        .security-badge svg {
            width: 16px;
            height: 16px;
            fill: #6ad66a;
        }

        .logo {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo svg {
            width: 36px;
            height: 36px;
            fill: #ffffff;
        }

        .logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <?php
                // Try to get site icon (favicon)
                $site_icon_url = get_site_icon_url(60);
                if ($site_icon_url) {
                    echo '<img src="' . esc_url($site_icon_url) . '" alt="' . esc_attr(get_bloginfo('name')) . '">';
                } else {
                    // Fallback to shield SVG if no site icon
                    echo '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2C5.589 2 2 5.589 2 10s3.589 8 8 8 8-3.589 8-8-3.589-8-8-8zm0 14c-3.309 0-6-2.691-6-6s2.691-6 6-6 6 2.691 6 6-2.691 6-6 6z"/>
                        <path d="M10 6c-2.206 0-4 1.794-4 4h2c0-1.103.897-2 2-2s2 .897 2 2-.897 2-2 2v2c2.206 0 4-1.794 4-4s-1.794-4-4-4z"/>
                    </svg>';
                }
                ?>
            </div>
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <p>DevSecOps Secure Access</p>
        </div>

        <?php
        session_start();
        if (isset($_SESSION['vsc_error'])) {
            echo '<div class="error-message">' . esc_html($_SESSION['vsc_error']) . '</div>';
            unset($_SESSION['vsc_error']);
        }
        ?>

        <form method="POST" action="">
            <?php wp_nonce_field('vsc_login', 'vsc_login_nonce'); ?>
            <input type="hidden" name="vsc_login_action" value="login">

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit" class="submit-btn">Sign In</button>

            <div style="text-align: center;">
                <div class="security-badge">
                    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 2L3 5v6c0 4.42 3.05 8.54 7 9.54 3.95-1 7-5.12 7-9.54V5l-7-3zm0 14.5c-2.76-1.01-5-4.23-5-7.5V6.3l5-2.19 5 2.19V9c0 3.27-2.24 6.49-5 7.5z"/>
                        <path d="M9 11.59l-2.29-2.3-1.42 1.42L9 14.41l6-6-1.41-1.41z"/>
                    </svg>
                    2FA Protected
                </div>
            </div>
        </form>

        <div class="login-footer">
            Powered by <?php echo esc_html(VSC_NAME); ?> <?php echo esc_html(VSC_VERSION); ?><br>
            <?php
            $core = VSC_Core::get_instance();
            $ip = method_exists($core, 'get_user_ip') ? '' : '';
            ?>
        </div>
    </div>

    <script>
        // Auto-focus on email field
        document.getElementById('email').focus();

        // Add subtle animations
        document.querySelector('.login-container').style.opacity = '0';
        document.querySelector('.login-container').style.transform = 'translateY(20px)';

        setTimeout(() => {
            const container = document.querySelector('.login-container');
            container.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);
    </script>
</body>
</html>
