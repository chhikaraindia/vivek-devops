<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Alert - Access Denied</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background: linear-gradient(135deg, #1a0000, #330000, #1a0000);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .alert-container {
            background: rgba(26, 0, 0, 0.9);
            border: 3px solid #ff0000;
            border-radius: 12px;
            box-shadow: 0 0 50px rgba(255, 0, 0, 0.5), 0 0 100px rgba(255, 0, 0, 0.3);
            width: 100%;
            max-width: 600px;
            padding: 50px 40px;
            text-align: center;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 0 50px rgba(255, 0, 0, 0.5), 0 0 100px rgba(255, 0, 0, 0.3);
            }
            50% {
                box-shadow: 0 0 70px rgba(255, 0, 0, 0.7), 0 0 140px rgba(255, 0, 0, 0.5);
            }
        }

        .warning-icon {
            width: 100px;
            height: 100px;
            margin: 0 auto 30px;
            background: linear-gradient(135deg, #ff0000, #cc0000);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: shake 0.5s ease-in-out infinite;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px) rotate(-5deg); }
            75% { transform: translateX(5px) rotate(5deg); }
        }

        .warning-icon svg {
            width: 60px;
            height: 60px;
            fill: #ffffff;
        }

        h1 {
            font-size: 48px;
            color: #ff0000;
            margin-bottom: 20px;
            font-weight: 800;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 0 0 20px rgba(255, 0, 0, 0.8);
        }

        .warning-message {
            font-size: 18px;
            line-height: 1.8;
            color: #ffcccc;
            margin-bottom: 30px;
        }

        .warning-message strong {
            color: #ffffff;
            font-weight: 700;
            font-size: 20px;
        }

        .details-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid #ff4444;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .details-box h3 {
            font-size: 16px;
            color: #ff6666;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #440000;
            font-size: 14px;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #ff9999;
            font-weight: 600;
        }

        .detail-value {
            color: #ffffff;
            font-family: 'Courier New', monospace;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #996666;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff4444;
            border-radius: 4px;
            padding: 10px 15px;
            margin-top: 20px;
            font-size: 13px;
            color: #ff6666;
            font-weight: 600;
        }

        .security-badge svg {
            width: 18px;
            height: 18px;
            fill: #ff6666;
        }
    </style>
</head>
<body>
    <div class="alert-container">
        <div class="warning-icon">
            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2L1 21h22L12 2zm0 3.99L19.53 19H4.47L12 5.99zM11 10v4h2v-4h-2zm0 6v2h2v-2h-2z"/>
            </svg>
        </div>

        <h1>BUSTED</h1>

        <div class="warning-message">
            You are attempting to hack into a secured system.<br>
            <strong>Stop immediately.</strong><br><br>
            Your IP and activity are recorded.<br>
            Further intrusion attempts will result in legal action and referral to law enforcement.
        </div>

        <div class="details-box">
            <h3>Incident Details</h3>
            <div class="detail-item">
                <span class="detail-label">IP Address:</span>
                <span class="detail-value"><?php echo esc_html($ip_address); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Timestamp:</span>
                <span class="detail-value"><?php echo esc_html(current_time('Y-m-d H:i:s')); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">User Agent:</span>
                <span class="detail-value"><?php echo esc_html(substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 50)); ?>...</span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Incident ID:</span>
                <span class="detail-value"><?php echo esc_html($incident_id); ?></span>
            </div>
        </div>

        <div class="security-badge">
            <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M10 2L3 5v6c0 4.42 3.05 8.54 7 9.54 3.95-1 7-5.12 7-9.54V5l-7-3zm0 14.5c-2.76-1.01-5-4.23-5-7.5V6.3l5-2.19 5 2.19V9c0 3.27-2.24 6.49-5 7.5z"/>
                <path d="M10 7c-1.1 0-2 .9-2 2v2c0 1.1.9 2 2 2s2-.9 2-2V9c0-1.1-.9-2-2-2zm0 4c-.55 0-1-.45-1-1V9c0-.55.45-1 1-1s1 .45 1 1v2c0 .55-.45 1-1 1z"/>
            </svg>
            Protected by <?php echo esc_html(VSC_NAME); ?> <?php echo esc_html(VSC_VERSION); ?>
        </div>

        <div class="footer">
            This system is protected by advanced security measures.<br>
            All unauthorized access attempts are logged and monitored.
        </div>
    </div>

    <script>
        // Add entrance animation
        document.querySelector('.alert-container').style.opacity = '0';
        document.querySelector('.alert-container').style.transform = 'scale(0.8)';

        setTimeout(() => {
            const container = document.querySelector('.alert-container');
            container.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            container.style.opacity = '1';
            container.style.transform = 'scale(1)';
        }, 100);
    </script>
</body>
</html>
