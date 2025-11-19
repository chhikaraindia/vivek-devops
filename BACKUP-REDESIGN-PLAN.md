# VSC Backup Module Redesign Plan

## Current Architecture (BROKEN)

```
VSC_Backup (class-vsc-backup.php)
├── Loads controller files (but doesn't instantiate them!)
├── Creates submenu: ?page=vsc-backup
├── Renders page with tabs
└── Calls Controller::index() methods directly

VSC_Backup_Main_Controller (NOT INSTANTIATED)
├── Has admin_menu hook (creates duplicate menu - never runs)
├── Has admin_enqueue_scripts hook (enqueues JS/CSS - never runs)
├── Has admin_init hook → router() → registers AJAX handlers (never runs!)
└── Result: NO AJAX handlers registered!
```

**Why Export Hangs:**
1. User clicks "File" in dropdown
2. JavaScript tries to call `admin-ajax.php?action=vsc_backup_export`
3. WordPress looks for `wp_ajax_vsc_backup_export` hook
4. Hook doesn't exist (Main Controller's router() never ran)
5. AJAX request fails/times out
6. Export stuck on "Preparing to export..."

## Correct Architecture (FIX)

```
VSC_Backup (Integration Layer)
├── Defines constants
├── Loads dependencies
├── Initializes Main Controller ← FIX: Add this!
├── Creates submenu under VSC Dashboard
└── Delegates everything to Main Controller

VSC_Backup_Main_Controller (Coordinator)
├── Registers AJAX handlers (router method)
├── Enqueues scripts/styles based on current page
├── Coordinates between Export/Import/Backups controllers
└── Handles all WordPress hooks

VSC_Backup_Export_Controller (Handles Export)
VSC_Backup_Import_Controller (Handles Import)
VSC_Backup_Backups_Controller (Handles Backups)
VSC_Backup_Status_Controller (Handles Status Checks)
```

## Implementation Changes

### 1. VSC_Backup - Remove Menu Creation
Since Main Controller already has menu logic, disable VSC_Backup's menu and delegate to Main Controller.

**OR** (Better approach):

### 2. VSC_Backup - Initialize Main Controller
Keep VSC_Backup's simple submenu, but initialize Main Controller so its hooks run.

```php
private function init_hooks() {
    // Setup storage
    add_action('admin_init', array($this, 'setup_storage'));

    // Add submenu
    add_action('admin_menu', array($this, 'add_menu'), 20);

    // ✅ NEW: Initialize Main Controller (this registers AJAX + enqueues)
    VSC_Backup_Main_Controller::get_instance();
}
```

### 3. Main Controller - Disable Duplicate Menu
Prevent Main Controller from creating its own top-level menu since VSC_Backup already has submenu.

```php
public function __construct() {
    // ❌ REMOVE: add_action( 'admin_menu', array( $this, 'admin_menu' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
    add_action( 'admin_init', array( $this, 'router' ) ); // ✅ KEEP: Registers AJAX
    // ... rest of hooks
}
```

### 4. Main Controller - Fix Enqueue Hook Check
Update enqueue functions to match VSC_Backup's page hook.

```php
public function enqueue_export_scripts_and_styles( $hook ) {
    // ✅ Match VSC_Backup's submenu hook
    if ( $hook !== 'vivek-devops_page_vsc-backup' ) {
        return;
    }
    // ... enqueue scripts
}
```

### 5. VSC_Backup - Delegate Enqueuing to Main Controller
Remove VSC_Backup's enqueue_assets() since Main Controller handles it.

```php
private function init_hooks() {
    add_action('admin_init', array($this, 'setup_storage'));
    add_action('admin_menu', array($this, 'add_menu'), 20);
    // ❌ REMOVE: add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

    // ✅ Initialize Main Controller (handles enqueuing)
    VSC_Backup_Main_Controller::get_instance();
}
```

## Summary of Changes

| File | Change | Why |
|------|--------|-----|
| `class-vsc-backup.php` | Add `VSC_Backup_Main_Controller::get_instance()` in `init_hooks()` | Initialize Main Controller so hooks run |
| `class-vsc-backup.php` | Remove `enqueue_assets()` hook | Main Controller handles enqueuing |
| `class-vsc-backup-main-controller.php` | Remove `admin_menu` hook | VSC_Backup already creates menu |
| `class-vsc-backup-main-controller.php` | Fix enqueue hook checks from `'vsc-backup'` to exact match `'vivek-devops_page_vsc-backup'` | Match VSC_Backup's submenu hook |
| `class-vsc-backup-main-controller.php` | Keep `admin_init` → `router()` hook | Registers AJAX handlers |
| `class-vsc-backup-main-controller.php` | Keep `admin_enqueue_scripts` hook | Enqueues scripts on backup pages |

## Result

After V-30.17 fix:
1. ✅ VSC_Backup creates submenu under VSC Dashboard
2. ✅ Main Controller initializes and registers AJAX handlers
3. ✅ Main Controller enqueues scripts on backup pages
4. ✅ Export/Import work because AJAX handlers exist
5. ✅ No duplicate menus
6. ✅ Clean architecture with clear responsibilities

---

## V-30.19-20 Additional Fixes

### V-30.19: Added Singleton Pattern
**Problem:** VSC_Backup called `VSC_Backup_Main_Controller::get_instance()` but method didn't exist.

**Fix:** Added singleton pattern to Main Controller:
- Added `private static $instance` property
- Added `public static get_instance()` method
- Ensures single instance of controller

### V-30.20: Fixed plugins_loaded Timing Issue (CRITICAL)
**Problem:** Export buttons and commands weren't being registered!

**Root Cause:**
```
plugins_loaded (priority 10) fires
  └─ vsc_init() runs
     └─ VSC_Backup::get_instance()
        └─ Main Controller::get_instance()
           └─ Main Controller::__construct()
              └─ activate_actions()
                 └─ add_action('plugins_loaded', ..., 10) ❌ TOO LATE!
```

We were trying to register `plugins_loaded` hooks WHILE ALREADY INSIDE `plugins_loaded` at the same priority!

**Result:**
- ❌ `vsc_backup_buttons()` never ran → Export buttons filter not registered
- ❌ `vsc_backup_commands()` never ran → Export/import pipeline not registered
- ❌ `vsc_backup_loaded()` never ran → HTTP headers not registered
- ❌ Export/import completely broken

**Fix (V-30.20):**
Call these methods directly in constructor instead of waiting for hook:
```php
// In Main Controller constructor (lines 73-85)
$this->vsc_backup_loaded();
$this->vsc_backup_commands();  // Registers export/import filter pipeline
$this->vsc_backup_buttons();   // Registers export/import buttons filter
```

Commented out duplicate `plugins_loaded` hooks in `activate_actions()` with explanation.

**Why This Works:**
- Methods are called immediately during initialization
- Filters are registered before pages render
- Export buttons appear in dropdown
- Export/import AJAX works end-to-end

## Complete Architecture (V-30.20)

```
WordPress Initialization
├─ plugins_loaded (priority 10)
│  └─ vsc_init()
│     ├─ VSC_Core::get_instance()
│     ├─ VSC_Backup::get_instance()
│     │  └─ VSC_Backup::__construct()
│     │     └─ init_hooks()
│     │        ├─ add_action('admin_init', setup_storage)
│     │        ├─ add_action('admin_menu', add_menu, 20)
│     │        └─ VSC_Backup_Main_Controller::get_instance() ✅
│     │           └─ Main Controller::__construct()
│     │              ├─ activate_actions() → registers admin_init/enqueue hooks
│     │              ├─ activate_filters() → registers plugin_row_meta
│     │              ├─ vsc_backup_loaded() ✅ → registers HTTP headers
│     │              ├─ vsc_backup_commands() ✅ → registers export/import pipeline
│     │              └─ vsc_backup_buttons() ✅ → registers button filters
│     └─ Other VSC modules...
│
├─ admin_init
│  ├─ Main Controller::init()
│  ├─ Main Controller::router() ✅ → registers ALL AJAX handlers
│  │  ├─ wp_ajax_vsc_backup_export
│  │  ├─ wp_ajax_vsc_backup_import
│  │  ├─ wp_ajax_vsc_backup_status
│  │  └─ etc.
│  ├─ Main Controller::wp_importing()
│  ├─ Main Controller::setup_backups_folder()
│  ├─ Main Controller::setup_storage_folder()
│  ├─ Main Controller::setup_secret_key() ✅
│  └─ Main Controller::check_user_role_capability()
│
├─ admin_menu (priority 20)
│  └─ VSC_Backup::add_menu() → creates "Backup and Restore" submenu
│
└─ admin_enqueue_scripts (priority 5)
   ├─ Main Controller::register_scripts_and_styles()
   ├─ Main Controller::enqueue_export_scripts_and_styles()
   │  ├─ Checks: $hook === 'vivek-devops_page_vsc-backup' ✅
   │  ├─ Enqueues: vsc_backup_export.js
   │  ├─ Localizes: ai1wm_export ✅
   │  ├─ Localizes: ai1wm_locale ✅
   │  └─ Localizes: ai1wm_feedback ✅
   └─ Main Controller::enqueue_import_scripts_and_styles()
```

## Final Verification Checklist

1. ✅ **Singleton Pattern** - Main Controller has get_instance() method (V-30.19)
2. ✅ **Initialization** - Main Controller initialized in VSC_Backup::init_hooks() (V-30.17)
3. ✅ **Buttons Registered** - vsc_backup_buttons() called in constructor (V-30.20)
4. ✅ **Commands Registered** - vsc_backup_commands() called in constructor (V-30.20)
5. ✅ **AJAX Handlers** - router() registered on admin_init (V-30.17)
6. ✅ **Scripts Enqueued** - Correct hook check 'vivek-devops_page_vsc-backup' (V-30.17)
7. ✅ **JS Objects** - Localized as ai1wm_export, ai1wm_locale (V-30.15)
8. ✅ **Dropdown Toggle** - JavaScript toggles .ai1wm-open class (V-30.16)
9. ✅ **No Duplicate Menus** - Main Controller menu disabled (V-30.17)
10. ✅ **Storage Setup** - Folders and secret key created on admin_init (V-30.17)
