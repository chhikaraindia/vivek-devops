<?php
/**
 * VSC Code Snippets Manager
 * Manages custom code snippets (CSS, JS, HTML, PHP)
 */

if (!defined('ABSPATH')) exit;

class VSC_Snippets {
    private static $instance = null;
    private $snippets_tree = array();

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Register custom post type
        add_action('init', [$this, 'register_post_type'], 1);

        // Register category taxonomy
        add_action('init', [$this, 'register_taxonomy'], 1);

        // Admin menu
        add_action('admin_menu', [$this, 'admin_menu']);

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);

        // Meta boxes
        add_action('add_meta_boxes_vsc_snippet', [$this, 'add_meta_boxes']);

        // Save post data
        add_action('save_post', [$this, 'save_snippet_data'], 10, 2);

        // Execute snippets on frontend/admin
        add_action('init', [$this, 'load_snippets']);

        // Register shortcode
        add_shortcode('vsc_snippet', [$this, 'snippet_shortcode']);

        // Custom columns
        add_filter('manage_vsc_snippet_posts_columns', [$this, 'custom_columns']);
        add_action('manage_vsc_snippet_posts_custom_column', [$this, 'custom_column_content'], 10, 2);

        // AJAX handlers
        add_action('wp_ajax_vsc_toggle_snippet', [$this, 'ajax_toggle_snippet']);
        add_action('wp_ajax_vsc_execute_snippet', [$this, 'ajax_execute_snippet']);
    }

    /**
     * Register custom post type for code snippets
     */
    public function register_post_type() {
        $labels = array(
            'name'               => __('Code Snippets', 'vivek-devops'),
            'singular_name'      => __('Code Snippet', 'vivek-devops'),
            'menu_name'          => __('Code Snippets', 'vivek-devops'),
            'add_new'            => __('Add New', 'vivek-devops'),
            'add_new_item'       => __('Add New Snippet', 'vivek-devops'),
            'edit_item'          => __('Edit Snippet', 'vivek-devops'),
            'new_item'           => __('New Snippet', 'vivek-devops'),
            'view_item'          => __('View Snippet', 'vivek-devops'),
            'search_items'       => __('Search Snippets', 'vivek-devops'),
            'not_found'          => __('No snippets found', 'vivek-devops'),
            'not_found_in_trash' => __('No snippets found in trash', 'vivek-devops'),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false, // We'll add it manually under Vivek DevOps
            'query_var'           => true,
            'rewrite'             => false,
            'capability_type'     => 'post',
            'capabilities'        => array(
                'edit_post'          => 'manage_options',
                'read_post'          => 'manage_options',
                'delete_post'        => 'manage_options',
                'edit_posts'         => 'manage_options',
                'edit_others_posts'  => 'manage_options',
                'delete_posts'       => 'manage_options',
                'publish_posts'      => 'manage_options',
                'read_private_posts' => 'manage_options',
            ),
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title', 'revisions'),
            'show_in_rest'        => false,
        );

        register_post_type('vsc_snippet', $args);
    }

    /**
     * Register taxonomy for snippet categories
     */
    public function register_taxonomy() {
        $labels = array(
            'name'              => __('Snippet Categories', 'vivek-devops'),
            'singular_name'     => __('Snippet Category', 'vivek-devops'),
            'search_items'      => __('Search Categories', 'vivek-devops'),
            'all_items'         => __('All Categories', 'vivek-devops'),
            'parent_item'       => __('Parent Category', 'vivek-devops'),
            'parent_item_colon' => __('Parent Category:', 'vivek-devops'),
            'edit_item'         => __('Edit Category', 'vivek-devops'),
            'update_item'       => __('Update Category', 'vivek-devops'),
            'add_new_item'      => __('Add New Category', 'vivek-devops'),
            'new_item_name'     => __('New Category Name', 'vivek-devops'),
            'menu_name'         => __('Categories', 'vivek-devops'),
        );

        register_taxonomy('vsc_snippet_category', array('vsc_snippet'), array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => false,
            'rewrite'           => false,
        ));
    }

    /**
     * Add admin menu items under Vivek DevOps
     */
    public function admin_menu() {
        global $submenu;

        // Main Code Snippets menu item
        add_submenu_page(
            'vsc-dashboard',
            __('Code Snippets', 'vivek-devops'),
            __('Code Snippets', 'vivek-devops'),
            'manage_options',
            'edit.php?post_type=vsc_snippet'
        );

        // Note: WordPress doesn't support nested submenus in the admin menu UI
        // We'll create a custom menu structure that appears as a dropdown on hover
        // This requires JavaScript to handle the interaction

        // Store submenu items for JavaScript to use
        $snippet_submenu = array(
            array(
                'title' => __('Add CSS', 'vivek-devops'),
                'url'   => admin_url('post-new.php?post_type=vsc_snippet&language=css')
            ),
            array(
                'title' => __('Add JS', 'vivek-devops'),
                'url'   => admin_url('post-new.php?post_type=vsc_snippet&language=js')
            ),
            array(
                'title' => __('Add HTML', 'vivek-devops'),
                'url'   => admin_url('post-new.php?post_type=vsc_snippet&language=html')
            ),
            array(
                'title' => __('Add PHP', 'vivek-devops'),
                'url'   => admin_url('post-new.php?post_type=vsc_snippet&language=php')
            ),
            array(
                'title' => __('Categories', 'vivek-devops'),
                'url'   => admin_url('edit-tags.php?taxonomy=vsc_snippet_category&post_type=vsc_snippet')
            ),
        );

        // Enqueue script to add submenu dropdown
        add_action('admin_footer', function() use ($snippet_submenu) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Find the Code Snippets menu item
                var $codeSnippetsMenu = $('#adminmenu a[href="edit.php?post_type=vsc_snippet"]').parent();

                if ($codeSnippetsMenu.length) {
                    // Create submenu HTML
                    var $submenu = $('<ul class="wp-submenu wp-submenu-wrap"></ul>');
                    $submenu.append('<li class="wp-submenu-head" aria-hidden="true">Code Snippets</li>');

                    <?php foreach ($snippet_submenu as $item): ?>
                    $submenu.append('<li class="wp-first-item"><a href="<?php echo esc_js($item['url']); ?>" class="wp-first-item"><?php echo esc_js($item['title']); ?></a></li>');
                    <?php endforeach; ?>

                    // Add submenu to the menu item
                    $codeSnippetsMenu.addClass('wp-has-submenu wp-not-current-submenu menu-top');
                    $codeSnippetsMenu.append($submenu);
                }
            });
            </script>
            <style>
            /* Make Code Snippets menu behave like other menus with submenus */
            #adminmenu a[href="edit.php?post_type=vsc_snippet"] {
                position: relative;
            }
            #adminmenu li.wp-has-submenu:hover .wp-submenu,
            #adminmenu li.wp-has-submenu.opensub .wp-submenu {
                display: block;
            }
            </style>
            <?php
        });
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts($hook) {
        $screen = get_current_screen();

        if ($screen->post_type != 'vsc_snippet') {
            return;
        }

        // Add security notice for PHP snippets
        add_action('admin_notices', function() {
            $screen = get_current_screen();
            if ($screen && $screen->post_type === 'vsc_snippet') {
                ?>
                <div class="notice notice-warning">
                    <p><strong>Security Notice:</strong> PHP snippet execution has been disabled for security. Use WordPress action/filter hooks in your theme's functions.php instead.</p>
                </div>
                <?php
            }
        });

        // CodeMirror
        wp_enqueue_code_editor(array('type' => 'text/html'));
        wp_enqueue_script('wp-theme-plugin-editor');
        wp_enqueue_style('wp-codemirror');

        // Custom admin styles
        wp_enqueue_style(
            'vsc-snippets-admin',
            VSC_URL . 'assets/css/vsc-snippets-admin.css',
            array(),
            VSC_VERSION
        );

        // Custom admin scripts
        wp_enqueue_script(
            'vsc-snippets-admin',
            VSC_URL . 'assets/js/vsc-snippets-admin.js',
            array('jquery', 'wp-codemirror'),
            VSC_VERSION,
            true
        );

        wp_localize_script('vsc-snippets-admin', 'vscSnippets', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('vsc_snippets_nonce'),
        ));
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        // Code editor meta box
        add_meta_box(
            'vsc_snippet_code',
            __('Code Editor', 'vivek-devops'),
            [$this, 'code_editor_meta_box'],
            'vsc_snippet',
            'normal',
            'high'
        );

        // Options meta box
        add_meta_box(
            'vsc_snippet_options',
            __('Snippet Options', 'vivek-devops'),
            [$this, 'options_meta_box'],
            'vsc_snippet',
            'side',
            'default'
        );

        // Description meta box
        add_meta_box(
            'vsc_snippet_description',
            __('Description', 'vivek-devops'),
            [$this, 'description_meta_box'],
            'vsc_snippet',
            'normal',
            'default'
        );

        // Remove slug meta box
        remove_meta_box('slugdiv', 'vsc_snippet', 'normal');
    }

    /**
     * Code editor meta box
     */
    public function code_editor_meta_box($post) {
        $code = get_post_meta($post->ID, '_vsc_snippet_code', true);
        $language = get_post_meta($post->ID, '_vsc_snippet_language', true);

        if (empty($language) && isset($_GET['language'])) {
            $language = sanitize_text_field($_GET['language']);
        }

        if (empty($language)) {
            $language = 'css';
        }

        wp_nonce_field('vsc_snippet_meta_box', 'vsc_snippet_meta_box_nonce');
        ?>
        <div class="vsc-code-editor-wrapper">
            <div class="vsc-language-selector" style="margin-bottom: 15px; padding: 10px; background: #0a0a0a; border: 1px solid #1a1a1a; border-radius: 4px;">
                <label for="vsc_snippet_language" style="display: block; margin-bottom: 8px; color: #ffffff; font-weight: 600;">
                    <?php _e('Snippet Language:', 'vivek-devops'); ?>
                </label>
                <select name="vsc_snippet_language" id="vsc_snippet_language" style="width: 100%; padding: 8px; background: #1a1a1a; border: 1px solid #2a2a2a; color: #ffffff; border-radius: 4px;">
                    <option value="css" <?php selected($language, 'css'); ?>>CSS</option>
                    <option value="js" <?php selected($language, 'js'); ?>>JavaScript</option>
                    <option value="html" <?php selected($language, 'html'); ?>>HTML</option>
                    <option value="php" <?php selected($language, 'php'); ?>>PHP</option>
                </select>
                <p class="description" style="margin-top: 8px; color: #cccccc;">
                    <?php _e('Select the language type for this snippet. The code editor will automatically adjust syntax highlighting.', 'vivek-devops'); ?>
                </p>
            </div>
            <textarea
                id="vsc_snippet_code"
                name="vsc_snippet_code"
                class="vsc-code-editor"
                rows="20"
                style="width: 100%; font-family: monospace; font-size: 13px;"
            ><?php echo esc_textarea($code); ?></textarea>
        </div>
        <script>
        jQuery(document).ready(function($) {
            if (typeof wp !== 'undefined' && wp.codeEditor) {
                var codeEditorInstance = null;

                var mimeTypes = {
                    'css': 'text/css',
                    'js': 'text/javascript',
                    'html': 'text/html',
                    'php': 'application/x-httpd-php'
                };

                // Initialize CodeMirror editor
                function initializeCodeEditor(language) {
                    var editorSettings = wp.codeEditor.defaultSettings ? _.clone(wp.codeEditor.defaultSettings) : {};

                    editorSettings.codemirror = _.extend(
                        {},
                        editorSettings.codemirror,
                        {
                            mode: mimeTypes[language] || 'text/css',
                            lineNumbers: true,
                            lineWrapping: true,
                            theme: 'monokai',
                            indentUnit: 4,
                            indentWithTabs: true,
                        }
                    );

                    // Remove existing editor if present
                    if (codeEditorInstance) {
                        codeEditorInstance.codemirror.toTextArea();
                    }

                    // Initialize new editor
                    codeEditorInstance = wp.codeEditor.initialize($('#vsc_snippet_code'), editorSettings);
                }

                // Initialize on page load
                var initialLanguage = $('#vsc_snippet_language').val();
                initializeCodeEditor(initialLanguage);

                // Re-initialize when language changes
                $('#vsc_snippet_language').on('change', function() {
                    var newLanguage = $(this).val();
                    initializeCodeEditor(newLanguage);
                });
            }
        });
        </script>
        <?php
    }

    /**
     * Options meta box
     */
    public function options_meta_box($post) {
        $active = get_post_meta($post->ID, '_vsc_snippet_active', true);
        $location = get_post_meta($post->ID, '_vsc_snippet_location', true);
        $position = get_post_meta($post->ID, '_vsc_snippet_position', true);
        $device = get_post_meta($post->ID, '_vsc_snippet_device', true);
        $priority = get_post_meta($post->ID, '_vsc_snippet_priority', true);

        if (empty($location)) $location = 'frontend';
        if (empty($position)) $position = 'header';
        if (empty($device)) $device = 'all';
        if (empty($priority)) $priority = 10;
        ?>
        <div class="vsc-snippet-options">
            <p>
                <label>
                    <input type="checkbox" name="vsc_snippet_active" value="1" <?php checked($active, '1'); ?>>
                    <strong><?php _e('Active', 'vivek-devops'); ?></strong>
                </label>
            </p>

            <p>
                <label><strong><?php _e('Location', 'vivek-devops'); ?></strong></label>
                <select name="vsc_snippet_location" style="width: 100%;">
                    <option value="frontend" <?php selected($location, 'frontend'); ?>><?php _e('Frontend Only', 'vivek-devops'); ?></option>
                    <option value="admin" <?php selected($location, 'admin'); ?>><?php _e('Admin Only', 'vivek-devops'); ?></option>
                    <option value="both" <?php selected($location, 'both'); ?>><?php _e('Both Frontend & Admin', 'vivek-devops'); ?></option>
                </select>
            </p>

            <p>
                <label><strong><?php _e('Position', 'vivek-devops'); ?></strong></label>
                <select name="vsc_snippet_position" style="width: 100%;">
                    <option value="header" <?php selected($position, 'header'); ?>><?php _e('Header', 'vivek-devops'); ?></option>
                    <option value="footer" <?php selected($position, 'footer'); ?>><?php _e('Footer', 'vivek-devops'); ?></option>
                </select>
            </p>

            <p>
                <label><strong><?php _e('Device', 'vivek-devops'); ?></strong></label>
                <select name="vsc_snippet_device" style="width: 100%;">
                    <option value="all" <?php selected($device, 'all'); ?>><?php _e('All Devices', 'vivek-devops'); ?></option>
                    <option value="desktop" <?php selected($device, 'desktop'); ?>><?php _e('Desktop Only', 'vivek-devops'); ?></option>
                    <option value="mobile" <?php selected($device, 'mobile'); ?>><?php _e('Mobile Only', 'vivek-devops'); ?></option>
                </select>
            </p>

            <p>
                <label><strong><?php _e('Priority', 'vivek-devops'); ?></strong></label>
                <input type="number" name="vsc_snippet_priority" value="<?php echo esc_attr($priority); ?>" min="1" max="100" style="width: 100%;">
                <small><?php _e('Lower numbers = earlier execution', 'vivek-devops'); ?></small>
            </p>
        </div>
        <?php
    }

    /**
     * Description meta box
     */
    public function description_meta_box($post) {
        $description = get_post_meta($post->ID, '_vsc_snippet_description', true);
        ?>
        <textarea name="vsc_snippet_description" rows="4" style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
        <p class="description"><?php _e('Optional description for this snippet', 'vivek-devops'); ?></p>
        <?php
    }

    /**
     * Save snippet data
     */
    public function save_snippet_data($post_id, $post) {
        // Check nonce
        if (!isset($_POST['vsc_snippet_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['vsc_snippet_meta_box_nonce'], 'vsc_snippet_meta_box')) {
            return;
        }

        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save code
        if (isset($_POST['vsc_snippet_code'])) {
            update_post_meta($post_id, '_vsc_snippet_code', $_POST['vsc_snippet_code']);
        }

        // Save language
        if (isset($_POST['vsc_snippet_language'])) {
            update_post_meta($post_id, '_vsc_snippet_language', sanitize_text_field($_POST['vsc_snippet_language']));
        }

        // Save options
        update_post_meta($post_id, '_vsc_snippet_active', isset($_POST['vsc_snippet_active']) ? '1' : '0');

        if (isset($_POST['vsc_snippet_location'])) {
            update_post_meta($post_id, '_vsc_snippet_location', sanitize_text_field($_POST['vsc_snippet_location']));
        }

        if (isset($_POST['vsc_snippet_position'])) {
            update_post_meta($post_id, '_vsc_snippet_position', sanitize_text_field($_POST['vsc_snippet_position']));
        }

        if (isset($_POST['vsc_snippet_device'])) {
            update_post_meta($post_id, '_vsc_snippet_device', sanitize_text_field($_POST['vsc_snippet_device']));
        }

        if (isset($_POST['vsc_snippet_priority'])) {
            update_post_meta($post_id, '_vsc_snippet_priority', absint($_POST['vsc_snippet_priority']));
        }

        if (isset($_POST['vsc_snippet_description'])) {
            update_post_meta($post_id, '_vsc_snippet_description', sanitize_textarea_field($_POST['vsc_snippet_description']));
        }
    }

    /**
     * Load and execute active snippets
     */
    public function load_snippets() {
        $args = array(
            'post_type'      => 'vsc_snippet',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_key'       => '_vsc_snippet_priority',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
        );

        $snippets = get_posts($args);

        foreach ($snippets as $snippet) {
            $active = get_post_meta($snippet->ID, '_vsc_snippet_active', true);

            if ($active !== '1') {
                continue;
            }

            $this->execute_snippet($snippet->ID);
        }
    }

    /**
     * Execute a single snippet
     */
    private function execute_snippet($snippet_id) {
        $language = get_post_meta($snippet_id, '_vsc_snippet_language', true);
        $code = get_post_meta($snippet_id, '_vsc_snippet_code', true);
        $location = get_post_meta($snippet_id, '_vsc_snippet_location', true);
        $position = get_post_meta($snippet_id, '_vsc_snippet_position', true);
        $priority = get_post_meta($snippet_id, '_vsc_snippet_priority', true);

        if (empty($code)) {
            return;
        }

        // Check location
        $is_admin = is_admin();
        if ($location == 'frontend' && $is_admin) return;
        if ($location == 'admin' && !$is_admin) return;

        // Execute based on language
        switch ($language) {
            case 'css':
                $hook = ($position == 'footer') ? 'wp_footer' : 'wp_head';
                if ($is_admin) {
                    $hook = 'admin_head';
                }
                add_action($hook, function() use ($code) {
                    echo "<style type='text/css'>\n" . $code . "\n</style>\n";
                }, $priority);
                break;

            case 'js':
                $hook = ($position == 'footer') ? 'wp_footer' : 'wp_head';
                if ($is_admin) {
                    $hook = ($position == 'footer') ? 'admin_footer' : 'admin_head';
                }
                add_action($hook, function() use ($code) {
                    echo "<script type='text/javascript'>\n" . $code . "\n</script>\n";
                }, $priority);
                break;

            case 'html':
                $hook = ($position == 'footer') ? 'wp_footer' : 'wp_head';
                if ($is_admin) {
                    $hook = ($position == 'footer') ? 'admin_footer' : 'admin_head';
                }
                add_action($hook, function() use ($code) {
                    echo $code . "\n";
                }, $priority);
                break;

            case 'php':
                // PHP execution disabled for security
                if (WP_DEBUG && current_user_can('manage_options')) {
                    echo '<!-- VSC: PHP snippets disabled for security. Use WordPress hooks in functions.php instead. -->';
                }
                break;
        }
    }

    /**
     * Snippet shortcode
     */
    public function snippet_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts);

        $snippet_id = absint($atts['id']);

        if (!$snippet_id) {
            return '';
        }

        $active = get_post_meta($snippet_id, '_vsc_snippet_active', true);

        if ($active !== '1') {
            return '';
        }

        $code = get_post_meta($snippet_id, '_vsc_snippet_code', true);
        $language = get_post_meta($snippet_id, '_vsc_snippet_language', true);

        ob_start();

        switch ($language) {
            case 'html':
                echo $code;
                break;
            case 'php':
                // PHP execution disabled for security
                if (WP_DEBUG && current_user_can('manage_options')) {
                    echo '<!-- VSC: PHP snippets disabled -->';
                }
                break;
        }

        return ob_get_clean();
    }

    /**
     * Custom columns
     */
    public function custom_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['language'] = __('Language', 'vivek-devops');
        $new_columns['location'] = __('Location', 'vivek-devops');
        $new_columns['active'] = __('Status', 'vivek-devops');
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    /**
     * Custom column content
     */
    public function custom_column_content($column, $post_id) {
        switch ($column) {
            case 'language':
                $language = get_post_meta($post_id, '_vsc_snippet_language', true);
                $colors = array(
                    'css' => '#3b82f6',
                    'js' => '#f59e0b',
                    'html' => '#ef4444',
                    'php' => '#6366f1',
                );
                $color = isset($colors[$language]) ? $colors[$language] : '#999';
                echo '<span style="display: inline-block; padding: 3px 8px; background: ' . $color . '; color: #fff; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase;">' . esc_html(strtoupper($language)) . '</span>';
                break;

            case 'location':
                $location = get_post_meta($post_id, '_vsc_snippet_location', true);
                $labels = array(
                    'frontend' => __('Frontend', 'vivek-devops'),
                    'admin' => __('Admin', 'vivek-devops'),
                    'both' => __('Both', 'vivek-devops'),
                );
                echo esc_html(isset($labels[$location]) ? $labels[$location] : $location);
                break;

            case 'active':
                $active = get_post_meta($post_id, '_vsc_snippet_active', true);
                if ($active == '1') {
                    echo '<span style="color: #10b981; font-weight: 600;">● ' . __('Active', 'vivek-devops') . '</span>';
                } else {
                    echo '<span style="color: #999;">○ ' . __('Inactive', 'vivek-devops') . '</span>';
                }
                break;
        }
    }

    /**
     * AJAX: Toggle snippet active status
     */
    public function ajax_toggle_snippet() {
        check_ajax_referer('vsc_snippets_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $snippet_id = isset($_POST['snippet_id']) ? absint($_POST['snippet_id']) : 0;

        if (!$snippet_id) {
            wp_send_json_error('Invalid snippet ID');
        }

        $active = get_post_meta($snippet_id, '_vsc_snippet_active', true);
        $new_status = ($active == '1') ? '0' : '1';

        update_post_meta($snippet_id, '_vsc_snippet_active', $new_status);

        wp_send_json_success(array(
            'active' => $new_status,
            'message' => ($new_status == '1') ? __('Snippet activated', 'vivek-devops') : __('Snippet deactivated', 'vivek-devops'),
        ));
    }

    /**
     * AJAX: Execute snippet on demand
     */
    public function ajax_execute_snippet() {
        check_ajax_referer('vsc_snippets_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }

        $snippet_id = isset($_POST['snippet_id']) ? absint($_POST['snippet_id']) : 0;

        if (!$snippet_id) {
            wp_send_json_error('Invalid snippet ID');
        }

        ob_start();
        $this->execute_snippet($snippet_id);
        $output = ob_get_clean();

        wp_send_json_success(array(
            'output' => $output,
            'message' => __('Snippet executed successfully', 'vivek-devops'),
        ));
    }
}
