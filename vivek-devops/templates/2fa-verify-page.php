<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Verify 2FA</title>
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

        .verify-container {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }

        .verify-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .verify-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #4a9eff 0%, #3a7edf 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .verify-icon svg {
            width: 48px;
            height: 48px;
            fill: #ffffff;
        }

        .verify-header h1 {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 10px;
        }

        .verify-header p {
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
            position: relative;
        }

        .step.active {
            background: #4a9eff;
            color: #ffffff;
        }

        .step.completed {
            background: #2a4a2a;
            color: #6ad66a;
        }

        .step.completed::after {
            content: '✓';
            position: absolute;
            font-size: 20px;
        }

        .error-message {
            background: #2a1a1a;
            border: 1px solid #ff4444;
            border-radius: 4px;
            color: #ff6666;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
        }

        .info-box {
            background: #1a2a3a;
            border: 1px solid #2a3a4a;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 20px;
            text-align: center;
        }

        .info-box p {
            color: #b0b0b0;
            font-size: 13px;
            line-height: 1.6;
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
            text-align: center;
        }

        input[type="text"] {
            width: 100%;
            padding: 16px;
            background: #0f0f0f;
            border: 2px solid #2a2a2a;
            border-radius: 8px;
            color: #e0e0e0;
            font-size: 28px;
            text-align: center;
            letter-spacing: 8px;
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

        .submit-btn:disabled {
            background: #2a2a2a;
            cursor: not-allowed;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #888;
            font-size: 13px;
            text-decoration: none;
        }

        .back-link:hover {
            color: #4a9eff;
        }

        .digit-boxes {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }

        .digit-box {
            width: 50px;
            height: 60px;
            background: #0f0f0f;
            border: 2px solid #2a2a2a;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-family: 'Courier New', monospace;
            color: #4a9eff;
            transition: border-color 0.3s ease;
        }

        .digit-box.filled {
            border-color: #4a9eff;
        }

        .countdown {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #666;
        }

        .countdown.warning {
            color: #ff9966;
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-header">
            <div class="verify-icon">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10h7c-.53 4.12-3.28 7.79-7 8.94V11H5V6.3l7-3.11V11z"/>
                    <circle cx="12" cy="14" r="1.5"/>
                    <circle cx="12" cy="9" r="1.5"/>
                    <circle cx="12" cy="19" r="1.5"/>
                </svg>
            </div>
            <h1>Two-Factor Authentication</h1>
            <p>Enter the 6-digit code from your authenticator app</p>
        </div>

        <div class="step-indicator">
            <div class="step completed">1</div>
            <div class="step active">2</div>
        </div>

        <?php
        if (isset($_SESSION['vsc_error'])) {
            echo '<div class="error-message">' . esc_html($_SESSION['vsc_error']) . '</div>';
            unset($_SESSION['vsc_error']);
        }
        ?>

        <div class="info-box">
            <p>Open Google Authenticator on your device and enter the current code</p>
        </div>

        <form method="POST" action="" id="verify-form">
            <?php wp_nonce_field('vsc_verify_2fa', 'vsc_2fa_nonce'); ?>
            <input type="hidden" name="vsc_login_action" value="verify_2fa">

            <div class="digit-boxes" id="digit-display">
                <div class="digit-box"></div>
                <div class="digit-box"></div>
                <div class="digit-box"></div>
                <div class="digit-box"></div>
                <div class="digit-box"></div>
                <div class="digit-box"></div>
            </div>

            <div class="form-group">
                <input type="text" id="code" name="code" maxlength="6" pattern="[0-9]{6}" required autofocus autocomplete="off">
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">Verify Code</button>

            <div class="countdown" id="countdown">
                Code refreshes in <span id="timer">30</span> seconds
            </div>
        </form>

        <a href="<?php echo esc_url(site_url('/vsc')); ?>" class="back-link">← Back to login</a>
    </div>

    <script>
        const codeInput = document.getElementById('code');
        const digitBoxes = document.querySelectorAll('.digit-box');
        const submitBtn = document.getElementById('submit-btn');
        const form = document.getElementById('verify-form');

        // Update digit display
        codeInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');

            // Update visual boxes
            const digits = this.value.split('');
            digitBoxes.forEach((box, index) => {
                if (digits[index]) {
                    box.textContent = digits[index];
                    box.classList.add('filled');
                } else {
                    box.textContent = '';
                    box.classList.remove('filled');
                }
            });

            // Auto-submit when 6 digits entered
            if (this.value.length === 6) {
                submitBtn.textContent = 'Verifying...';
                submitBtn.disabled = true;
                setTimeout(() => {
                    form.submit();
                }, 300);
            }
        });

        // Paste handling
        codeInput.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = pastedText.replace(/[^0-9]/g, '').substr(0, 6);
            this.value = cleaned;
            this.dispatchEvent(new Event('input'));
        });

        // Countdown timer
        function updateCountdown() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = 30 - (now % 30);
            const timerElement = document.getElementById('timer');
            const countdownElement = document.getElementById('countdown');

            timerElement.textContent = remaining;

            if (remaining <= 10) {
                countdownElement.classList.add('warning');
            } else {
                countdownElement.classList.remove('warning');
            }
        }

        setInterval(updateCountdown, 1000);
        updateCountdown();

        // Fade-in animation
        document.querySelector('.verify-container').style.opacity = '0';
        document.querySelector('.verify-container').style.transform = 'translateY(20px)';

        setTimeout(() => {
            const container = document.querySelector('.verify-container');
            container.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            container.style.opacity = '1';
            container.style.transform = 'translateY(0)';
        }, 100);

        // Focus on input
        codeInput.focus();
    </script>
</body>
</html>
