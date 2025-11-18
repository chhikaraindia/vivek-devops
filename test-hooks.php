<?php
// Add this to test what hooks are firing
add_action('admin_enqueue_scripts', function($hook) {
    error_log("ADMIN ENQUEUE HOOK: " . $hook);
    file_put_contents('/tmp/admin-hooks.txt', date('Y-m-d H:i:s') . " Hook: " . $hook . "\n", FILE_APPEND);
}, 1);
?>
