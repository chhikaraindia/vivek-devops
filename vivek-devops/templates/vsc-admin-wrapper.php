<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Admin</title>

    <?php
    // Load WordPress admin styles
    do_action('admin_enqueue_scripts');
    do_action('admin_print_styles');
    do_action('admin_print_scripts');
    do_action('admin_head');
    ?>

    <style>
        /* VSC Admin Wrapper Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --vsc-bg-primary: #000000;
            --vsc-bg-secondary: #0a0a0a;
            --vsc-bg-tertiary: #121212;
            --vsc-bg-card: #1a1a1a;
            --vsc-text-primary: #ffffff;
            --vsc-text-secondary: #999999;
            --vsc-text-muted: #666666;
            --vsc-accent: #0ea5e9;
            --vsc-accent-hover: #60c5ff;
            --vsc-border: #1a1a1a;
            --vsc-border-light: #2a2a2a;
            --vsc-success: #10b981;
            --vsc-warning: #f59e0b;
            --vsc-error: #ef4444;
            --vsc-toolbar-height: 50px;
            --vsc-menu-height: 46px;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            background: var(--vsc-bg-primary);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        }

        #frame-vsc-app {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: var(--vsc-bg-primary);
        }

        /* Progress Bar */
        .vsc-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--vsc-bg-secondary);
            z-index: 999999;
        }

        .vsc-indeterminate {
            background: var(--vsc-accent);
            height: 100%;
            animation: indeterminate 1.5s infinite;
        }

        @keyframes indeterminate {
            0% { width: 0; margin-left: 0; }
            50% { width: 50%; margin-left: 25%; }
            100% { width: 0; margin-left: 100%; }
        }

        /* Toolbar */
        .vsc-toolbar-wrapper {
            background: var(--vsc-bg-secondary);
            border-bottom: 1px solid var(--vsc-border);
            height: var(--vsc-toolbar-height);
            display: flex;
            align-items: center;
            padding: 0 20px;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .frame-vsc-logo {
            display: flex;
            align-items: center;
        }

        .frame-vsc-logo a {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--vsc-text-primary);
            font-weight: 600;
            font-size: 16px;
        }

        .vsc-logo-icon {
            font-size: 24px;
            color: var(--vsc-accent);
        }

        .vsc-logo-text {
            color: var(--vsc-text-primary);
        }

        /* User Account */
        .vsc-admin-bar-top-secondary {
            display: flex;
            align-items: center;
        }

        .vsc-user-account {
            position: relative;
        }

        .vsc-user-avatar {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            position: relative;
        }

        .vsc-user-avatar img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid var(--vsc-border);
        }

        .vsc-user-status {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background: var(--vsc-success);
            border-radius: 50%;
            border: 2px solid var(--vsc-bg-secondary);
        }

        .vsc-user-wrapper {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            background: var(--vsc-bg-card);
            border: 1px solid var(--vsc-border);
            border-radius: 8px;
            min-width: 250px;
            display: none;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .vsc-user-account:hover .vsc-user-wrapper {
            display: block;
        }

        .vsc-user-info {
            padding: 16px;
            border-bottom: 1px solid var(--vsc-border);
        }

        .vsc-user-name {
            color: var(--vsc-text-primary);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .vsc-user-info span {
            color: var(--vsc-text-secondary);
            font-size: 12px;
        }

        .vsc-user-wrapper ul {
            list-style: none;
            padding: 8px;
            margin: 0;
        }

        .vsc-user-wrapper hr {
            border: none;
            border-top: 1px solid var(--vsc-border);
            margin: 4px 0;
        }

        .vsc-user-wrapper li a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            color: var(--vsc-text-secondary);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .vsc-user-wrapper li a:hover {
            background: var(--vsc-bg-secondary);
            color: var(--vsc-text-primary);
        }

        .vsc-logout {
            color: var(--vsc-error) !important;
        }

        /* Horizontal Menu */
        .vsc-horizontal-menu {
            background: var(--vsc-bg-primary);
            border-bottom: 1px solid var(--vsc-border);
            height: var(--vsc-menu-height);
            flex-shrink: 0;
        }

        .vsc-frame-content {
            display: flex;
            flex-direction: column;
            height: calc(100vh - var(--vsc-toolbar-height));
        }

        .vsc-horizontal-menu-wrapper {
            background: var(--vsc-bg-secondary);
            border-bottom: 1px solid var(--vsc-border);
            overflow-x: auto;
            overflow-y: hidden;
        }

        .vsc-horizontal-menu-wrapper::-webkit-scrollbar {
            height: 4px;
        }

        .vsc-horizontal-menu-wrapper::-webkit-scrollbar-track {
            background: var(--vsc-bg-primary);
        }

        .vsc-horizontal-menu-wrapper::-webkit-scrollbar-thumb {
            background: var(--vsc-border-light);
            border-radius: 2px;
        }

        .vsc-horizontal-menu-wrapper ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 2px;
        }

        .frame-vsc-menu-item {
            position: relative;
        }

        .frame-vsc-menu-item > a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 16px;
            color: var(--vsc-text-secondary);
            text-decoration: none;
            white-space: nowrap;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .frame-vsc-menu-item > a:hover {
            color: var(--vsc-text-primary);
            background: var(--vsc-bg-primary);
            border-bottom-color: var(--vsc-accent);
        }

        .frame-vsc-menu-item-wrap {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .frame-vsc-menu-icon {
            font-size: 18px;
        }

        .frame-vsc-menu-icon img {
            width: 18px;
            height: 18px;
        }

        .frame-vsc-submenu-arrow {
            margin-left: 4px;
            opacity: 0.5;
        }

        .frame-vsc-submenu-wrapper {
            position: absolute;
            top: 100%;
            left: 0;
            background: var(--vsc-bg-card);
            border: 1px solid var(--vsc-border);
            border-radius: 4px;
            min-width: 200px;
            display: none;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .frame-vsc-menu-item:hover .frame-vsc-submenu-wrapper {
            display: block;
        }

        .frame-vsc-submenu {
            list-style: none;
            padding: 4px;
            margin: 0;
        }

        .frame-vsc-submenu-item a {
            display: block;
            padding: 10px 12px;
            color: var(--vsc-text-secondary);
            text-decoration: none;
            border-radius: 4px;
            transition: all 0.2s;
        }

        .frame-vsc-submenu-item a:hover {
            background: var(--vsc-bg-secondary);
            color: var(--vsc-text-primary);
        }

        /* iframe */
        #frame-vsc-app--iframe {
            flex: 1;
            width: 100%;
            border: none;
            background: #1e2022;
        }
    </style>
</head>
<body class="vsc-admin-wrapper">
    <div id="frame-vsc-app" class="frame-vsc-app">
        <!-- Progress Bar -->
        <div class="vsc-progress" id="VSC-PreLoaderBar" style="display: none;">
            <div class="vsc-indeterminate"></div>
        </div>

        <!-- Custom Toolbar -->
        <div class="vsc-toolbar-wrapper vsc-horizontal-toolbar">
            <!-- Logo -->
            <div class="frame-vsc-logo">
                <a href="<?php echo esc_url(VSC_Admin_Router::get_menu_url('index.php')); ?>" target="vsc-iframe">
                    <span class="dashicons dashicons-shield-alt vsc-logo-icon"></span>
                    <span class="vsc-logo-text">Vivek DevOps</span>
                </a>
            </div>

            <!-- Toolbar -->
            <div class="vsc-toolbar">
                <!-- Right Side: User Account Only -->
                <div class="vsc-admin-bar-top-secondary">
                    <!-- User Account -->
                    <div class="vsc-user-account">
                        <button class="vsc-user-avatar">
                            <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" width="36" height="36">
                            <span class="vsc-user-status"></span>
                        </button>
                        <div class="vsc-user-wrapper">
                            <div class="vsc-user-info">
                                <h3 class="vsc-user-name"><?php echo esc_html($current_user->display_name); ?></h3>
                                <span><?php echo esc_html($current_user->user_email); ?></span>
                            </div>
                            <ul>
                                <hr>
                                <li><a href="<?php echo esc_url(VSC_Admin_Router::get_menu_url('profile.php')); ?>" target="vsc-iframe">
                                    <span class="dashicons dashicons-admin-users"></span>
                                    <span>View Profile</span>
                                </a></li>
                                <hr>
                                <li><a href="<?php echo esc_url($logout_url); ?>" class="vsc-logout">
                                    <span class="dashicons dashicons-exit"></span>
                                    <span>Log Out</span>
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Horizontal Menu -->
        <div class="vsc-frame-wrapper vsc-horizontal-menu">
            <div class="vsc-frame-content">
                <div class="vsc-horizontal-menu-wrapper">
                    <ul>
                        <?php
                        // Render menu items
                        if (!empty($menu) && is_array($menu)) {
                        foreach ($menu as $key => $item) {
                            // Skip if not valid menu item
                            if (!is_array($item) || empty($item[2])) {
                                continue;
                            }

                            // Skip separators
                            if (isset($item[4]) && strpos($item[4], 'wp-menu-separator') !== false) {
                                continue;
                            }

                            // Skip Dashboard menu (index.php)
                            if ($item[2] === 'index.php') {
                                continue;
                            }

                            // Check capabilities
                            if (isset($item[1]) && !current_user_can($item[1])) {
                                continue;
                            }

                            $has_submenu = !empty($submenu[$item[2]]);
                            $submenu_class = $has_submenu ? ' frame-vsc-has-submenu' : '';

                            // Menu URL with iframe param
                            $href = VSC_Admin_Router::get_menu_url($item[2]);

                            // Icon
                            $icon = '';
                            if (!empty($item[6])) {
                                if (preg_match('/^dashicons/', $item[6])) {
                                    $icon = '<span class="frame-vsc-menu-icon dashicons ' . esc_attr($item[6]) . '"></span>';
                                } else {
                                    $icon = '<span class="frame-vsc-menu-icon"><img src="' . esc_url($item[6]) . '" alt=""></span>';
                                }
                            }

                            echo '<li class="frame-vsc-menu-item' . $submenu_class . '">';
                            echo '<a href="' . esc_url($href) . '" target="vsc-iframe">';
                            echo '<div class="frame-vsc-menu-item-wrap">';
                            echo $icon;
                            echo '<span class="frame-vsc-menu-item-name">' . wp_kses_post(isset($item[0]) ? $item[0] : '') . '</span>';
                            echo '</div>';

                            if ($has_submenu) {
                                echo '<span class="frame-vsc-submenu-arrow">';
                                echo '<span class="dashicons dashicons-arrow-down-alt2"></span>';
                                echo '</span>';
                            }
                            echo '</a>';

                            // Submenu
                            if ($has_submenu) {
                                echo '<div class="frame-vsc-submenu-wrapper">';
                                echo '<ul class="frame-vsc-submenu">';
                                foreach ($submenu[$item[2]] as $sub_item) {
                                    // Skip if not valid submenu item
                                    if (!is_array($sub_item) || empty($sub_item[2])) {
                                        continue;
                                    }

                                    // Check capabilities
                                    if (isset($sub_item[1]) && !current_user_can($sub_item[1])) {
                                        continue;
                                    }

                                    // Submenu URL with iframe param
                                    $suburl = $sub_item[2];
                                    if (file_exists(WP_PLUGIN_DIR . "/{$sub_item[2]}") || get_plugin_page_hook($sub_item[2], $item[2])) {
                                        $suburl = admin_url('admin.php?page=' . $sub_item[2]);
                                    } else {
                                        $suburl = admin_url($sub_item[2]);
                                    }
                                    $suburl = add_query_arg('vsc_iframe', '1', $suburl);

                                    echo '<li class="frame-vsc-submenu-item">';
                                    echo '<a href="' . esc_url($suburl) . '" target="vsc-iframe">' . wp_kses_post(isset($sub_item[0]) ? $sub_item[0] : '') . '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                                echo '</div>';
                            }

                            echo '</li>';
                        }
                        }
                        ?>
                    </ul>
                </div>

                <!-- iframe containing actual WordPress admin -->
                <iframe
                    width="100%"
                    height="100%"
                    id="frame-vsc-app--iframe"
                    name="vsc-iframe"
                    src="<?php echo esc_url($iframe_url); ?>"
                    frameborder="0"
                    style="border: none;">
                </iframe>
            </div>
        </div>
    </div>

    <?php do_action('admin_footer'); ?>
</body>
</html>
