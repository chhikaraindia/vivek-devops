# VSC Backup V-30.9 - Diagnostic Version

## Critical Error Troubleshooting Guide

This version includes **TRIPLE LOGGING** to capture the error no matter what:

### Three Logging Methods:

1. **WordPress Debug Log** (`/wp-content/debug.log`)
2. **Direct File Logs** (in plugin directory)
3. **Standalone Diagnostic Script**

---

## Method 1: WordPress Debug Log (Standard)

### Enable in wp-config.php:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

**Log Location:** `/wp-content/debug.log`

---

## Method 2: Direct File Logs (NEW in V-30.9)

These logs are written directly to files in the plugin directory, **bypassing WordPress error_log** entirely.

After uploading and activating the plugin, check for these files in `/wp-content/plugins/vivek-devops/`:

### Log Files to Check:

1. **backup-load-error.txt**
   - Created if the backup class file fails to load
   - Contains parse errors, syntax errors, missing dependencies

2. **backup-init.txt**
   - Tracks whether VSC_Backup class was found
   - Logs initialization attempt and result

3. **backup-constructor.txt**
   - Detailed step-by-step log of the backup module constructor
   - Shows exactly which step fails (constants, dependencies, hooks)

### How to Access:

- **Via FTP/SFTP:** Download the .txt files from the plugin directory
- **Via cPanel:** File Manager → wp-content/plugins/vivek-devops/ → download .txt files
- **Via WordPress File Manager Plugin:** Navigate to the directory and view files

---

## Method 3: Standalone Diagnostic Script (Most Powerful)

This script can be run **independently** to test the backup module without activating the plugin.

### Step 1: Access the Diagnostic Script

Navigate to:
```
http://yoursite.com/wp-content/plugins/vivek-devops/vsc-diagnostic.php
```

### Step 2: Review the Results

The diagnostic script will:
- ✓ Check PHP version
- ✓ Verify WordPress is loaded
- ✓ Check for critical files
- ✓ Test loading the backup class
- ✓ Test initializing the backup module
- ✓ Check PHP extensions
- ✓ Verify file permissions

### Step 3: Download the Log

After running the diagnostic script, download:
```
/wp-content/plugins/vivek-devops/diagnostic-log.txt
```

This file contains the complete diagnostic output.

---

## What to Share

Please share **ALL** of the following files:

1. `/wp-content/debug.log` (if it exists)
2. `/wp-content/plugins/vivek-devops/backup-load-error.txt` (if it exists)
3. `/wp-content/plugins/vivek-devops/backup-init.txt` (if it exists)
4. `/wp-content/plugins/vivek-devops/backup-constructor.txt` (if it exists)
5. `/wp-content/plugins/vivek-devops/diagnostic-log.txt` (if you ran the diagnostic script)

**Even if a file doesn't exist, that tells us something!**

---

## Expected Outcomes

### If the plugin activates successfully:

- `backup-init.txt` should show: "VSC_Backup class found" and "SUCCESS: VSC_Backup initialized"
- `backup-constructor.txt` should show: "SUCCESS: Constructor completed"
- No `backup-load-error.txt` file should exist

### If there's an error:

- One or more log files will contain error details
- Error message will include: Message, File path, Line number, Stack trace

---

## Quick Troubleshooting

### No log files at all?

- Check file permissions on `/wp-content/plugins/vivek-devops/` (should be writable)
- Try running the standalone diagnostic script
- Your server may have disabled file writing

### Diagnostic script shows 404?

- Plugin may not be uploaded correctly
- Check that `vsc-diagnostic.php` exists in the plugin directory

### Still getting "critical error"?

- The error is happening BEFORE the logging code runs
- This suggests a **parse error** (syntax error in the code)
- Check your PHP version: **Minimum required is PHP 5.6**
- Run the diagnostic script to see PHP version

---

## PHP Version Requirement

**Minimum:** PHP 5.6
**Recommended:** PHP 7.4 or higher

To check your PHP version, run the diagnostic script or add this to a test.php file:

```php
<?php phpinfo(); ?>
```

Upload it to your site root and access: `http://yoursite.com/test.php`

---

## Next Steps

1. **Upload** Vivek-DevOps-V-30.9.zip
2. **Activate** the plugin
3. **Check** for the log files listed above
4. **Run** the diagnostic script
5. **Share** all log files

With these three logging methods, we WILL capture the error!
