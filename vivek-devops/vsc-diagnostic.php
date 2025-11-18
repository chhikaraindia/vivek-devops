<?php
/**
 * VSC Backup Diagnostic Script
 *
 * Upload this file to your WordPress root directory and access it via browser:
 * http://yoursite.com/wp-content/plugins/vivek-devops/vsc-diagnostic.php
 *
 * This will create a diagnostic-log.txt file in the same directory
 */

// Start output buffering
ob_start();

// Set up direct file logging (doesn't rely on WordPress)
$log_file = __DIR__ . '/diagnostic-log.txt';

function diagnostic_log($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    echo "<p>$message</p>\n";
    flush();
    ob_flush();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>VSC Backup Diagnostics</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #00ff00; }
        h1 { color: #00ff00; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #ffff00; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
<h1>VSC Backup Diagnostic Script</h1>
<p class="info">This script will test each component and write results to diagnostic-log.txt</p>
<hr>

<?php

diagnostic_log("=== VSC BACKUP DIAGNOSTIC START ===");

// Test 1: PHP Version
diagnostic_log("Test 1: Checking PHP Version...");
diagnostic_log("PHP Version: " . PHP_VERSION);
if (version_compare(PHP_VERSION, '5.6.0', '<')) {
    diagnostic_log("ERROR: PHP version is too old. Minimum required: 5.6.0");
} else {
    diagnostic_log("SUCCESS: PHP version is compatible");
}

// Test 2: WordPress Constants
diagnostic_log("\nTest 2: Checking WordPress Constants...");
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_load_paths = [
        __DIR__ . '/../../../../wp-load.php',
        __DIR__ . '/../../../wp-load.php',
        __DIR__ . '/../../wp-load.php',
    ];

    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            diagnostic_log("Found WordPress at: $path");
            require_once $path;
            $wp_loaded = true;
            break;
        }
    }

    if (!$wp_loaded) {
        diagnostic_log("ERROR: Could not load WordPress");
        die();
    }
}
diagnostic_log("SUCCESS: WordPress loaded");

// Test 3: Plugin Constants
diagnostic_log("\nTest 3: Checking Plugin Constants...");
if (defined('VSC_PATH')) {
    diagnostic_log("SUCCESS: VSC_PATH = " . VSC_PATH);
} else {
    diagnostic_log("ERROR: VSC_PATH not defined");
    die();
}

// Test 4: File Existence Checks
diagnostic_log("\nTest 4: Checking Critical Files...");
$critical_files = [
    'class-vsc-backup.php' => VSC_PATH . 'includes/class-vsc-backup.php',
    'constants.php' => VSC_PATH . 'includes/backup/constants.php',
    'exceptions.php' => VSC_PATH . 'includes/backup/exceptions.php',
    'functions.php' => VSC_PATH . 'includes/backup/functions.php',
    'Bandar.php' => VSC_PATH . 'includes/backup/lib/vendor/bandar/bandar/lib/Bandar.php',
];

foreach ($critical_files as $name => $path) {
    if (file_exists($path)) {
        diagnostic_log("SUCCESS: Found $name");
    } else {
        diagnostic_log("ERROR: Missing $name at: $path");
    }
}

// Test 5: Try to Load Backup Class
diagnostic_log("\nTest 5: Attempting to Load VSC_Backup Class...");
try {
    if (!class_exists('VSC_Backup')) {
        if (file_exists(VSC_PATH . 'includes/class-vsc-backup.php')) {
            diagnostic_log("Loading class-vsc-backup.php...");
            require_once VSC_PATH . 'includes/class-vsc-backup.php';
            diagnostic_log("SUCCESS: class-vsc-backup.php loaded");
        } else {
            diagnostic_log("ERROR: class-vsc-backup.php not found");
        }
    }

    if (class_exists('VSC_Backup')) {
        diagnostic_log("SUCCESS: VSC_Backup class exists");
    } else {
        diagnostic_log("ERROR: VSC_Backup class not found after loading file");
    }
} catch (Throwable $e) {
    diagnostic_log("ERROR: Exception while loading VSC_Backup class");
    diagnostic_log("  Message: " . $e->getMessage());
    diagnostic_log("  File: " . $e->getFile());
    diagnostic_log("  Line: " . $e->getLine());
    diagnostic_log("  Trace: " . $e->getTraceAsString());
}

// Test 6: Try to Initialize Backup Module
diagnostic_log("\nTest 6: Attempting to Initialize VSC_Backup...");
try {
    if (class_exists('VSC_Backup')) {
        diagnostic_log("Calling VSC_Backup::get_instance()...");
        $instance = VSC_Backup::get_instance();
        diagnostic_log("SUCCESS: VSC_Backup initialized successfully!");
    } else {
        diagnostic_log("SKIPPED: VSC_Backup class not available");
    }
} catch (Throwable $e) {
    diagnostic_log("ERROR: Exception during VSC_Backup initialization");
    diagnostic_log("  Message: " . $e->getMessage());
    diagnostic_log("  File: " . $e->getFile());
    diagnostic_log("  Line: " . $e->getLine());
    diagnostic_log("  Trace: " . $e->getTraceAsString());
}

// Test 7: PHP Extensions
diagnostic_log("\nTest 7: Checking Required PHP Extensions...");
$required_extensions = ['zip', 'mysqli', 'json'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        diagnostic_log("SUCCESS: $ext extension loaded");
    } else {
        diagnostic_log("WARNING: $ext extension not loaded (may cause issues)");
    }
}

// Test 8: File Permissions
diagnostic_log("\nTest 8: Checking File Permissions...");
$storage_path = VSC_PATH . 'storage/backups';
if (is_writable(VSC_PATH . 'storage')) {
    diagnostic_log("SUCCESS: Storage directory is writable");
} else {
    diagnostic_log("WARNING: Storage directory is not writable: " . VSC_PATH . 'storage');
}

diagnostic_log("\n=== VSC BACKUP DIAGNOSTIC END ===");
diagnostic_log("Full log saved to: $log_file");

?>

<hr>
<p class="info"><strong>Diagnostic Complete!</strong></p>
<p class="info">Log file created at: <?php echo $log_file; ?></p>
<p class="info">Please download the diagnostic-log.txt file and share it for analysis.</p>

</body>
</html>
<?php
ob_end_flush();
?>
