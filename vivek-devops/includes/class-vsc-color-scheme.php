<?php
/**
 * VSC Custom Admin Color Scheme
 * Replaces WordPress default colors with Vivek DevOps branding
 */

if (!defined('ABSPATH')) exit;

class VSC_Color_Scheme {
    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Inject inline color overrides using Adminify's approach
        // Using admin_enqueue_scripts with priority 99999 ensures our styles load last
        add_action('admin_enqueue_scripts', [$this, 'inject_color_overrides'], 99999);

        // Also apply to login page
        add_action('login_enqueue_scripts', [$this, 'inject_color_overrides'], 99999);
    }

    /**
     * Inject color overrides using Adminify's approach
     * Uses admin_enqueue_scripts with priority 99999 to ensure styles load last
     */
    public function inject_color_overrides() {
        $css = '';

        // Override WordPress default blue colors globally
        $css .= 'a{color:#3b82f6!important}';
        $css .= 'a:hover,a:active,a:focus{color:#4a92ff!important}';

        // Admin Menu Colors
        $css .= '#adminmenu,#adminmenuback,#adminmenuwrap{background:#000000!important}';
        $css .= '#adminmenu .wp-submenu{background:#0a0a0a!important}';
        $css .= '#adminmenu a{color:#cccccc!important}';
        $css .= '#adminmenu li.menu-top:hover,#adminmenu li.opensub>a.menu-top,#adminmenu li>a.menu-top:focus{background:#1a1a1a!important;color:#ffffff!important}';
        $css .= '#adminmenu .wp-has-current-submenu .wp-submenu,#adminmenu .wp-has-current-submenu .wp-submenu.sub-open,#adminmenu .wp-has-current-submenu.opensub .wp-submenu,#adminmenu a.wp-has-current-submenu:focus+.wp-submenu{background:#0a0a0a!important}';
        $css .= '#adminmenu li.wp-has-current-submenu a.wp-has-current-submenu,#adminmenu li.current a.menu-top,#adminmenu .wp-menu-arrow,#adminmenu .wp-menu-arrow div{background:linear-gradient(135deg,#3b82f6,#1e40af)!important;color:#ffffff!important}';
        $css .= '#adminmenu .wp-submenu a:hover,#adminmenu .wp-submenu a:focus{background:#1a1a1a!important;color:#3b82f6!important}';
        $css .= '#adminmenu .wp-submenu li.current a,#adminmenu .wp-submenu li.current a:hover,#adminmenu .wp-submenu li.current a:focus{color:#3b82f6!important;font-weight:600}';
        $css .= '#adminmenu .wp-not-current-submenu .wp-submenu li>a{color:#cccccc!important}';

        // Menu Icons
        $css .= '#adminmenu div.wp-menu-image:before{color:#cccccc!important}';
        $css .= '#adminmenu li.menu-top:hover div.wp-menu-image:before,#adminmenu li.opensub>a.menu-top div.wp-menu-image:before{color:#3b82f6!important}';
        $css .= '#adminmenu li.wp-has-current-submenu div.wp-menu-image:before,#adminmenu li.current div.wp-menu-image:before{color:#ffffff!important}';

        // Primary Buttons
        $css .= '.button-primary,.wp-core-ui .button-primary,.wp-core-ui .button-primary.focus,.wp-core-ui .button-primary.hover,.wp-core-ui .button-primary:focus,.wp-core-ui .button-primary:hover,input[type="submit"],input[type="button"].button-primary,button.button-primary{background:linear-gradient(135deg,#3b82f6,#1e40af)!important;border-color:#3b82f6!important;color:#ffffff!important;box-shadow:none!important;text-shadow:none!important}';
        $css .= '.button-primary:active,.wp-core-ui .button-primary:active{background:linear-gradient(135deg,#2a72df,#0e30af)!important}';

        // Form Focus States
        $css .= 'input[type="text"]:focus,input[type="search"]:focus,input[type="radio"]:focus,input[type="tel"]:focus,input[type="time"]:focus,input[type="url"]:focus,input[type="week"]:focus,input[type="password"]:focus,input[type="checkbox"]:focus,input[type="color"]:focus,input[type="date"]:focus,input[type="datetime"]:focus,input[type="datetime-local"]:focus,input[type="email"]:focus,input[type="month"]:focus,input[type="number"]:focus,select:focus,textarea:focus{border-color:#3b82f6!important;box-shadow:0 0 0 1px #3b82f6!important}';

        // Checkboxes & Radio Buttons
        $css .= 'input[type="checkbox"]:checked::before{background-color:#3b82f6!important}';
        $css .= 'input[type="radio"]:checked::before{background-color:#3b82f6!important}';

        // WordPress Admin Bar
        $css .= '#wpadminbar{background:#0a0a0a!important}';
        $css .= '#wpadminbar .ab-item,#wpadminbar a.ab-item,#wpadminbar>#wp-toolbar span.ab-label,#wpadminbar>#wp-toolbar span.noticon{color:#cccccc!important}';
        $css .= '#wpadminbar .ab-top-menu>li:hover>.ab-item,#wpadminbar .ab-top-menu>li.hover>.ab-item{background:#1a1a1a!important;color:#3b82f6!important}';
        $css .= '#wpadminbar .menupop .ab-sub-wrapper{background:#0a0a0a!important}';

        // Tabs
        $css .= '.nav-tab-active,.nav-tab-active:hover,.nav-tab-active:focus{border-bottom-color:#3b82f6!important;color:#3b82f6!important}';

        // Pagination
        $css .= '.tablenav .tablenav-pages a:hover,.tablenav-pages .current{background:#3b82f6!important;border-color:#3b82f6!important;color:#ffffff!important}';

        // Update/Plugin counts
        $css .= '.update-plugins,.plugin-count,.update-count{background-color:#3b82f6!important;color:#ffffff!important}';

        // Post states
        $css .= '.post-state{color:#3b82f6!important}';

        // Notice links
        $css .= '.notice a,.update-message a{color:#3b82f6!important}';

        // Screen options toggle
        $css .= '#screen-meta-links .show-settings{color:#3b82f6!important}';
        $css .= '#screen-meta-links .show-settings:hover{color:#4a92ff!important}';

        // Media grid selected items
        $css .= '.attachment.selected,.attachment.details .check{background:#3b82f6!important;border-color:#3b82f6!important}';

        // WordPress Editor (Gutenberg)
        $css .= '.editor-styles-wrapper .block-editor-block-list__block.is-selected .block-editor-block-list__block-edit::before{outline:1px solid #3b82f6!important}';
        $css .= '.components-button.is-primary,.components-button.is-secondary:active{background:linear-gradient(135deg,#3b82f6,#1e40af)!important;color:#ffffff!important;border-color:#3b82f6!important}';

        // Widget areas
        $css .= '.widget-top{background:#0a0a0a!important;color:#ffffff!important}';
        $css .= '.widget-inside{background:#000000!important;border:1px solid #1a1a1a!important}';

        // Row Actions (Edit, View, Trash, etc.) - Override #53bfff default
        $css .= '.row-actions a,.row-actions span a{color:#3b82f6!important}';
        $css .= '.row-actions a:hover,.row-actions span a:hover{color:#4a92ff!important}';
        $css .= '.row-actions .edit a:hover,.row-actions .view a:hover,.row-actions .inline-edit a:hover{color:#4a92ff!important}';

        // Plugin & Theme Action Links
        $css .= '.plugin-action-buttons a,.theme-actions a,.plugin-title strong>a{color:#3b82f6!important}';
        $css .= '.plugin-action-buttons a:hover,.theme-actions a:hover{color:#4a92ff!important}';

        // Table Links & Hover States
        $css .= '.widefat thead a,.widefat tfoot a{color:#3b82f6!important}';
        $css .= '.widefat a:hover{color:#4a92ff!important}';

        // Pagination Links (all states)
        $css .= '.tablenav-pages a,.tablenav-pages .button{color:#3b82f6!important;border-color:#3b82f6!important}';
        $css .= '.tablenav-pages a:hover,.tablenav-pages .button:hover{background:#3b82f6!important;color:#ffffff!important}';
        $css .= '.tablenav-pages span.current{background:#3b82f6!important;border-color:#3b82f6!important;color:#ffffff!important}';

        // Dashboard Widget Links
        $css .= '#dashboard_right_now a,.activity-block a{color:#3b82f6!important}';
        $css .= '#dashboard_right_now a:hover,.activity-block a:hover{color:#4a92ff!important}';

        // User List & Profile Links
        $css .= '.user-profile-picture,.user-admin-color a{color:#3b82f6!important}';
        $css .= 'table.users a{color:#3b82f6!important}';
        $css .= 'table.users a:hover{color:#4a92ff!important}';

        // Help Tabs & Screen Meta
        $css .= '#contextual-help-link,.contextual-help-tabs a{color:#3b82f6!important}';
        $css .= '#contextual-help-link:hover,.contextual-help-tabs a:hover{color:#4a92ff!important}';

        // Post Edit Screen Links
        $css .= '#delete-action a,#wp-admin-bar-archive a{color:#3b82f6!important}';
        $css .= '#delete-action a:hover,#wp-admin-bar-archive a:hover{color:#4a92ff!important}';

        // Bulk Actions & Select All
        $css .= '.check-column input[type="checkbox"]:checked{accent-color:#3b82f6!important}';
        $css .= 'thead .check-column input[type="checkbox"]:checked,tfoot .check-column input[type="checkbox"]:checked{accent-color:#3b82f6!important}';

        // Media Library & Attachment Details
        $css .= '.attachment-info .edit-attachment,.attachment-info .delete-attachment{color:#3b82f6!important}';
        $css .= '.attachment-info .edit-attachment:hover,.attachment-info .delete-attachment:hover{color:#4a92ff!important}';

        // Comment Actions
        $css .= '.comment-edit-link,.comment-reply-link,.comment-quick-edit-link{color:#3b82f6!important}';
        $css .= '.comment-edit-link:hover,.comment-reply-link:hover,.comment-quick-edit-link:hover{color:#4a92ff!important}';

        // Menu Editor Links
        $css .= '.menu-item-edit-active,.menu-item-settings a{color:#3b82f6!important}';
        $css .= '.menu-item-settings a:hover{color:#4a92ff!important}';

        // Settings & Options Page Links
        $css .= '.form-table a,.option-site-visibility a{color:#3b82f6!important}';
        $css .= '.form-table a:hover,.option-site-visibility a:hover{color:#4a92ff!important}';

        // Search Results Highlighted
        $css .= '.search-highlight{background-color:#3b82f6!important;color:#ffffff!important}';

        // Toggle Switches & Checkmarks
        $css .= '.components-form-toggle.is-checked .components-form-toggle__track{background-color:#3b82f6!important}';
        $css .= '.components-checkbox-control__checked{background:#3b82f6!important;border-color:#3b82f6!important}';

        // Spinner & Loading States
        $css .= '.spinner,.spinner::before{border-top-color:#3b82f6!important}';

        // Highlighted Text & Selections
        $css .= '::selection{background:#3b82f6!important;color:#ffffff!important}';
        $css .= '::-moz-selection{background:#3b82f6!important;color:#ffffff!important}';

        // Universal Override for #53bfff (WordPress default blue)
        $css .= '*[style*="#53bfff"],*[style*="#0073aa"]{color:#3b82f6!important;border-color:#3b82f6!important}';

        // Nuclear Override for "Wrong" Blues (#35d9ff and #53bfff)
        $css .= '[style*="#35d9ff"],[style*="#53bfff"],.wp-core-ui .button-link,.wp-core-ui .button-link:hover,.wp-core-ui .button-link:focus{color:#3b82f6!important}';

        // Force Brand Blue on Table Data Links
        $css .= '.wp-list-table a,.wp-list-table .row-title,.wp-list-table td.column-title a,.wp-list-table .plugin-title strong,.wp-list-table th.sortable a,.wp-list-table th.sorted a{color:#3b82f6!important}';

        // Fix "Add New" buttons next to titles
        $css .= '.wrap .page-title-action,.wrap .add-new-h2{background-color:#3b82f6!important;border-color:#3b82f6!important;color:#ffffff!important}';

        // Fix WooCommerce specific light blues
        $css .= '.wc-featured::before,.wc-featured a::before,.star-rating span::before,.view-order,.order-view,.order-number a{color:#3b82f6!important}';

        // Ensure Hover States are High Contrast (White)
        $css .= '.wp-list-table a:hover,.row-actions span a:hover{color:#ffffff!important}';

        // Pagination Links
        $css .= '.tablenav-pages a{color:#3b82f6!important}';

        // AGGRESSIVE #53bfff ELIMINATION - Target all possible WordPress elements
        // Override inline styles with #53bfff
        $css .= '[style*="color:#53bfff"],[style*="color: #53bfff"]{color:#3b82f6!important}';
        $css .= '[style*="background:#53bfff"],[style*="background-color:#53bfff"]{background:#3b82f6!important;background-color:#3b82f6!important}';
        $css .= '[style*="border-color:#53bfff"]{border-color:#3b82f6!important}';

        // Target all anchor elements across the entire admin
        $css .= '.wp-admin a:not(.button-primary){color:#3b82f6!important}';
        $css .= '.wp-admin a:not(.button-primary):visited{color:#3b82f6!important}';
        $css .= '.wp-admin a:not(.button-primary):hover{color:#ffffff!important}';

        // Target all table links and content
        $css .= '.wp-list-table tbody tr td a,.wp-list-table tbody tr .row-title{color:#3b82f6!important}';
        $css .= '.wp-list-table tbody tr td a:hover{color:#ffffff!important}';
        $css .= '.wp-list-table td.column-title strong,.wp-list-table td.plugin-title strong{color:#3b82f6!important}';
        $css .= '.wp-list-table .row-actions a,.wp-list-table .row-actions span{color:#3b82f6!important}';

        // Target plugin and theme row actions
        $css .= 'tr .plugin-title a,tr .theme-title a,.plugins .inactive a{color:#3b82f6!important}';
        $css .= '.plugin-action-buttons a,.theme-actions a{color:#3b82f6!important}';

        // Target post and page list tables
        $css .= '.type-post .row-title,.type-page .row-title{color:#3b82f6!important}';
        $css .= '.post-state,.post-count,.count{color:#3b82f6!important}';

        // Target settings and option page links
        $css .= '.form-table a,.option-site-visibility a{color:#3b82f6!important}';

        // Target dashboard widgets
        $css .= '#dashboard-widgets a,.activity-block a{color:#3b82f6!important}';
        $css .= '.rss-widget a,.dashboard-comment-wrap a{color:#3b82f6!important}';

        // Target subsubsub filter links
        $css .= '.subsubsub a,.subsubsub .current{color:#3b82f6!important}';

        // Target view and edit links
        $css .= '.view a,.edit a,.delete a{color:#3b82f6!important}';

        // Nuclear option - Override WordPress default link color globally
        $css .= 'body.wp-admin a{color:#3b82f6!important}';
        $css .= 'body.wp-admin a:hover{color:#ffffff!important}';
        $css .= 'body.wp-admin .button-primary{background:#3b82f6!important;color:#ffffff!important}';

        // Minify the CSS (remove comments and extra whitespace)
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $css = preg_replace('/\s*([{}|:;,])\s+/', '$1', $css);
        $css = preg_replace('/\s\s+(.*)/', '$1', $css);

        // Output the CSS
        printf('<style id="vsc-color-overrides-inline">%s</style>', wp_strip_all_tags($css));
    }
}
