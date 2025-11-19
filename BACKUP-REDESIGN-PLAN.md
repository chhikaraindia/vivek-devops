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

After fix:
1. ✅ VSC_Backup creates submenu under VSC Dashboard
2. ✅ Main Controller initializes and registers AJAX handlers
3. ✅ Main Controller enqueues scripts on backup pages
4. ✅ Export/Import work because AJAX handlers exist
5. ✅ No duplicate menus
6. ✅ Clean architecture with clear responsibilities
