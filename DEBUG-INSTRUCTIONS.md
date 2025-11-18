# VSC Backup Module Debug Instructions

Version 30.8 includes **comprehensive error logging** to help diagnose activation issues.

## Step 1: Enable WordPress Debug Logging

Add these lines to your `wp-config.php` file (before the line that says `/* That's all, stop editing! Happy publishing. */`):

```php
// Enable WP_DEBUG mode
define('WP_DEBUG', true);

// Enable Debug logging to /wp-content/debug.log
define('WP_DEBUG_LOG', true);

// Disable display of errors (logs to file instead)
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

## Step 2: Upload and Activate Plugin

1. Delete old Vivek DevOps plugin from WordPress
2. Upload `Vivek-DevOps-V-30.8.zip`
3. Activate the plugin

## Step 3: Check Debug Log

The debug log will be created at: `/wp-content/debug.log`

You can access it via:
- FTP/SFTP client
- cPanel File Manager
- WordPress plugin like "WP Debugging" or "Debug Log Manager"

## Step 4: Find the Error

Look for these log entries in `debug.log`:

```
VSC Backup: Constructor called
VSC Backup: Defining constants
VSC Backup: Constants defined
VSC Backup: Loading dependencies
VSC Backup: Starting load_dependencies()
VSC Backup: Loading constants.php
...
```

**If there's an error**, you'll see:

```
VSC Backup CRITICAL ERROR in load_dependencies():
  Message: [error message here]
  File: [file path]
  Line: [line number]
  Trace: [stack trace]
```

Or:

```
VSC Backup FATAL ERROR in constructor:
  Message: [error message here]
  File: [file path]
  Line: [line number]
  Trace: [stack trace]
```

## Step 5: Share the Error Log

Once you have the `debug.log` file:

1. **Copy the relevant VSC Backup error lines** (from "VSC Backup:" messages)
2. **Share the complete error message** including:
   - Message
   - File path
   - Line number
   - Stack trace

This will help us identify the exact cause of the critical error.

## What V-30.8 Logs

Every step is now logged:

- ✓ Constructor called
- ✓ Constants definition
- ✓ Loading constants.php
- ✓ Loading exceptions.php
- ✓ Loading functions.php
- ✓ Loading vendor files
- ✓ Loading model files
- ✓ Loading controller files
- ✓ Main controller instantiation
- ✓ Hooks initialization

**Any error will be logged with complete details**, including:
- Error message
- File location
- Line number
- Full stack trace

## Disabling Debug Mode (After Diagnosis)

Once we've identified the issue, **remove or comment out** the debug lines from `wp-config.php`:

```php
// define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', true);
// define('WP_DEBUG_DISPLAY', false);
```

Or change to:

```php
define('WP_DEBUG', false);
```
