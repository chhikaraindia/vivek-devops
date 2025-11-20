<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Setup 2FA</title>
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

        .setup-container {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 500px;
            padding: 40px;
        }

        .setup-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .setup-header h1 {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .setup-header p {
            font-size: 14px;
            color: #888;
            line-height: 1.6;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #2a2a2a;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
        }

        .step.active {
            background: #4a9eff;
            color: #ffffff;
        }

        .qr-section {
            background: #ffffff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        #qr-code {
            margin: 0 auto;
        }

        .secret-key {
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            padding: 12px;
            margin: 20px 0;
            text-align: center;
        }

        .secret-key label {
            display: block;
            margin-bottom: 8px;
            color: #b0b0b0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .secret-key code {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            color: #4a9eff;
            letter-spacing: 2px;
            word-break: break-all;
        }

        .instructions {
            background: #1a2a3a;
            border: 1px solid #2a3a4a;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 20px;
        }

        .instructions h3 {
            font-size: 14px;
            color: #4a9eff;
            margin-bottom: 12px;
        }

        .instructions ol {
            padding-left: 20px;
            color: #b0b0b0;
            font-size: 13px;
            line-height: 1.8;
        }

        .instructions li {
            margin-bottom: 8px;
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

        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            background: #0f0f0f;
            border: 1px solid #2a2a2a;
            border-radius: 4px;
            color: #e0e0e0;
            font-size: 18px;
            text-align: center;
            letter-spacing: 4px;
            font-family: 'Courier New', monospace;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus {
            outline: none;
            border-color: #4a9eff;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #4a9eff;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background: #3a8eef;
        }

        .submit-btn:active {
            background: #2a7edf;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
</head>
<body>
    <div class="setup-container">
        <div class="setup-header">
            <h1>Setup Two-Factor Authentication</h1>
            <p>Secure your account with Google Authenticator</p>
        </div>

        <div class="step-indicator">
            <div class="step active">1</div>
            <div class="step">2</div>
        </div>

        <?php
        if (isset($_SESSION['vsc_error'])) {
            echo '<div class="error-message">' . esc_html($_SESSION['vsc_error']) . '</div>';
            unset($_SESSION['vsc_error']);
        }
        ?>

        <div class="instructions">
            <h3>Follow these steps:</h3>
            <ol>
                <li>Install <strong>Google Authenticator</strong> on your mobile device</li>
                <li>Open the app and tap "+" to add a new account</li>
                <li>Scan the QR code below OR manually enter the secret key</li>
                <li>Enter the 6-digit code from the app to verify</li>
            </ol>
        </div>

        <div class="qr-section">
            <div id="qr-code"></div>
        </div>

        <div class="secret-key">
            <label>Manual Entry Key</label>
            <code><?php echo esc_html(VSC_Base32::encode($secret_key)); ?></code>
        </div>

        <form method="POST" action="">
            <?php wp_nonce_field('vsc_setup_2fa', 'vsc_setup_nonce'); ?>
            <input type="hidden" name="vsc_login_action" value="verify_setup">

            <div class="form-group">
                <label for="code">Enter 6-Digit Code</label>
                <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" required autofocus>
            </div>

            <button type="submit" class="submit-btn">Verify & Continue</button>
        </form>
    </div>

    <script>
        // Generate QR code
        new QRCode(document.getElementById("qr-code"), {
            text: "<?php echo esc_js($qr_url); ?>",
            width: 256,
            height: 256,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });

        // Auto-format code input
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Auto-submit when 6 digits entered
        codeInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });

        // Fade-in animation
        document.querySelector('.setup-container').style.opacity = '0';
        document.querySelector('.setup-container').style.transform = 'translateY(20px)';

        setTimeout(() => {
            const container = document.querySelector('.setup-container');
            container.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);
    </script>
</body>
</html>
