<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Admin</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            overflow: hidden;
            background: #000000;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        #vsc-admin-wrapper {
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: #000000;
        }

        /* ===== TOOLBAR ===== */
        .vsc-toolbar {
            background: #0a0a0a;
            border-bottom: 1px solid #1a1a1a;
            height: 50px;
            display: flex;
            align-items: center;
            padding: 0 20px;
            justify-content: space-between;
            flex-shrink: 0;
        }

        .vsc-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 16px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .vsc-logo-icon {
            width: 28px;
            height: 28px;
        }

        .vsc-logo-icon path {
            fill: url(#vsc-logo-gradient);
        }

        .vsc-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .vsc-user img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid #1a1a1a;
        }

        .vsc-user-name {
            color: #cccccc;
            font-size: 14px;
        }

        .vsc-logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .vsc-logout-btn:hover {
            transform: translateY(-2px);
        }

        /* ===== HORIZONTAL MENU ===== */
        .vsc-menu {
            background: #0a0a0a;
            border-bottom: 1px solid #1a1a1a;
            padding: 0 20px;
            overflow: visible;
            flex-shrink: 0;
            position: relative;
        }

        .vsc-menu > ul {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
            gap: 5px;
        }

        .vsc-menu > ul > li {
            position: relative;
        }

        .vsc-menu > ul > li > a {
            display: block;
            padding: 12px 16px;
            color: #3b82f6;
            font-size: 13px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.3s ease;
            border-bottom: 2px solid transparent;
        }

        .vsc-menu > ul > li > a:hover {
            color: #ffffff;
            background: #1a1a1a;
            border-bottom-color: #3b82f6;
        }

        /* Dropdown Arrow */
        .vsc-menu li.has-submenu > a::after {
            content: "▼";
            font-size: 10px;
            margin-left: 6px;
            opacity: 0.7;
        }

        /* Dropdown Submenu */
        .vsc-submenu {
            display: none !important;
            position: absolute;
            top: 100%;
            left: 0;
            background: #0a0a0a;
            border: 1px solid #1a1a1a;
            border-radius: 8px;
            min-width: 200px;
            z-index: 9999;
            margin-top: 2px;
            padding: 8px 0;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            flex-direction: column;
        }

        .vsc-menu li.has-submenu:hover > .vsc-submenu {
            display: flex !important;
        }

        .vsc-submenu li {
            width: 100%;
            display: block;
        }

        .vsc-submenu li a {
            display: block;
            padding: 10px 16px;
            color: #3b82f6;
            font-size: 13px;
            text-decoration: none;
            border-bottom: none;
            border-left: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .vsc-submenu li a:hover {
            background: #1a1a1a;
            border-left-color: #3b82f6;
            color: #ffffff;
        }

        #vsc-iframe {
            flex: 1;
            width: 100%;
            border: none;
            background: #1e2022;
        }
    </style>
</head>
<body>
    <div id="vsc-admin-wrapper">
        <!-- Toolbar -->
        <div class="vsc-toolbar">
            <div class="vsc-logo">
                <svg class="vsc-logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="vsc-logo-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#0ea5e9;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#0284c7;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm0 10.99h7c-.53 4.12-3.28 7.79-7 8.94V12H5V6.3l7-3.11v8.8z"/>
                </svg>
                <span>Vivek DevOps</span>
            </div>

            <div class="vsc-user">
                <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>">
                <span class="vsc-user-name"><?php echo esc_html($current_user->display_name); ?></span>
                <a href="<?php echo esc_url($logout_url); ?>" class="vsc-logout-btn">Logout</a>
            </div>
        </div>

        <!-- Horizontal Menu with Dropdowns -->
        <div class="vsc-menu">
            <ul>
                <!-- Vivek DevOps -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=vsc-dashboard'))); ?>" target="vsc-iframe">Vivek DevOps</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=vsc-dashboard'))); ?>" target="vsc-iframe">Home</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('update-core.php'))); ?>" target="vsc-iframe">Updates</a></li>
                    </ul>
                </li>

                <!-- Blogs (Posts) -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php'))); ?>" target="vsc-iframe">Blogs</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php'))); ?>" target="vsc-iframe">All Blogs</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('post-new.php'))); ?>" target="vsc-iframe">Add New</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit-tags.php?taxonomy=category'))); ?>" target="vsc-iframe">Categories</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit-tags.php?taxonomy=post_tag'))); ?>" target="vsc-iframe">Tags</a></li>
                    </ul>
                </li>

                <!-- Media -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('upload.php'))); ?>" target="vsc-iframe">Media</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('upload.php'))); ?>" target="vsc-iframe">Library</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('media-new.php'))); ?>" target="vsc-iframe">Add New</a></li>
                    </ul>
                </li>

                <!-- Pages -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php?post_type=page'))); ?>" target="vsc-iframe">Pages</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php?post_type=page'))); ?>" target="vsc-iframe">All Pages</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('post-new.php?post_type=page'))); ?>" target="vsc-iframe">Add New</a></li>
                    </ul>
                </li>

                <!-- Comments -->
                <li>
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit-comments.php'))); ?>" target="vsc-iframe">Comments</a>
                </li>

                <?php
                // Add WooCommerce menu if active
                if (class_exists('WooCommerce')) :
                ?>
                <!-- Store (WooCommerce) -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=wc-admin'))); ?>" target="vsc-iframe">Store</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php?post_type=shop_order'))); ?>" target="vsc-iframe">Orders</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php?post_type=product'))); ?>" target="vsc-iframe">Products</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=wc-admin&path=/customers'))); ?>" target="vsc-iframe">Customers</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=wc-settings'))); ?>" target="vsc-iframe">Settings</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php
                // Add Elementor menu if active
                if (did_action('elementor/loaded')) :
                ?>
                <!-- Elementor -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=elementor'))); ?>" target="vsc-iframe">Elementor</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('edit.php?post_type=elementor_library'))); ?>" target="vsc-iframe">Templates</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('admin.php?page=elementor-system-info'))); ?>" target="vsc-iframe">System Info</a></li>
                    </ul>
                </li>
                <?php endif; ?>

                <?php if ($current_user->user_email === 'support@chhikara.in'): ?>
                <!-- Appearance -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('themes.php'))); ?>" target="vsc-iframe">Appearance</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('themes.php'))); ?>" target="vsc-iframe">Themes</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('customize.php'))); ?>" target="vsc-iframe">Customize</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('widgets.php'))); ?>" target="vsc-iframe">Widgets</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('nav-menus.php'))); ?>" target="vsc-iframe">Menus</a></li>
                    </ul>
                </li>

                <!-- Plugins -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('plugins.php'))); ?>" target="vsc-iframe">Plugins</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('plugins.php'))); ?>" target="vsc-iframe">Installed Plugins</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('plugin-install.php'))); ?>" target="vsc-iframe">Add New</a></li>
                    </ul>
                </li>

                <!-- Users -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('users.php'))); ?>" target="vsc-iframe">Users</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('users.php'))); ?>" target="vsc-iframe">All Users</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('user-new.php'))); ?>" target="vsc-iframe">Add New</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('profile.php'))); ?>" target="vsc-iframe">Profile</a></li>
                    </ul>
                </li>

                <!-- Tools -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('tools.php'))); ?>" target="vsc-iframe">Tools</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('tools.php'))); ?>" target="vsc-iframe">Available Tools</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('import.php'))); ?>" target="vsc-iframe">Import</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('export.php'))); ?>" target="vsc-iframe">Export</a></li>
                    </ul>
                </li>

                <!-- Settings -->
                <li class="has-submenu">
                    <a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-general.php'))); ?>" target="vsc-iframe">Settings</a>
                    <ul class="vsc-submenu">
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-general.php'))); ?>" target="vsc-iframe">General</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-writing.php'))); ?>" target="vsc-iframe">Writing</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-reading.php'))); ?>" target="vsc-iframe">Reading</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-discussion.php'))); ?>" target="vsc-iframe">Discussion</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-media.php'))); ?>" target="vsc-iframe">Media</a></li>
                        <li><a href="<?php echo esc_url(add_query_arg('vsc_iframe', '1', admin_url('options-permalink.php'))); ?>" target="vsc-iframe">Permalinks</a></li>
                    </ul>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- iframe containing actual WordPress admin -->
        <iframe
            id="vsc-iframe"
            name="vsc-iframe"
            src="<?php echo esc_url($iframe_url); ?>"
            frameborder="0">
        </iframe>
    </div>
</body>
</html>
