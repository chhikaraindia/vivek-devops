# Vivek DevOps - Claude Spec

## 🧩 Project Overview

**Vivek DevOps** is a custom WordPress plugin designed as an all-in-one DevSecOps layer for enterprise-grade site administration. It consolidates functionality from multiple commercial plugins (like Adminify, UI Press, Code Snippets, SMTP, Activity Log, and 2FA tools) into one efficient, security-focused plugin. The core features include visual admin rebranding, access control, integrity validation, logging, code injection, secure login flows, SMTP integration, and 2FA. It is designed to be deployed across all WordPress websites managed by the owner (Mighty-Vivek) and acts as both a productivity enhancer and security enforcer.

---

## 🔧 Core Modules (Summary)

1. **Admin UI Rebranding + Menu Control**
2. **Activity Log (Global Audit Logging)**
3. **Login Control + Honeypot Trap**
4. **Admin Role-Based Restrictions**
5. **File Integrity Checker**
6. **Custom Code Injector (PHP/CSS/JS)**
7. **SMTP Mail Router (Brevo Integration)**
8. **Two-Factor Authentication (TOTP)**
9. **API Key + REST Monitor Endpoint**
10. **Backup & Migration System**

---

## 🖥️ Detailed Feature Specs

### 1. Admin UI Rebranding

* **Complete OLED Dark Theme:**
  * Pure black (#000000) backgrounds for battery optimization
  * White (#ffffff) and light gray (#b0b0b0) text
  * 21:1 contrast ratio for accessibility
  * CSS variables for consistent theming
  * Covers all WordPress admin areas: sidebar, top bar, content, forms, tables

* **Menu Structure:**
  * **Vivek DevOps** (Top position) → submenus:
    * Dashboard = main VSC dashboard
    * Settings = 4-tab interface (General, 2FA, Security, SMTP)
    * Activity Log = full audit viewer with filtering
    * Updates = core + plugin + theme update status
    * Backup & Migration = complete backup/restore system
  * WordPress Dashboard menu = **REMOVED COMPLETELY**
  * "Posts" → "Blogs" (renamed throughout admin)
  * "WooCommerce" → "Store"

* **Menu Hiding for Non-Master Admins:**
  * Hidden: Appearance, Plugins, Users, Tools, Settings (general)
  * Shown: Vivek DevOps, Blogs, Media, Pages, Comments, Store, Products
  * Master Admin (Mighty-Vivek): sees everything

* **Login page customization:**
  * Accessible only at `/vsc`
  * Default URLs (`/wp-login.php`, `/wp-admin`) act as honeypot traps
  * Login Title: "Welcome to {Site Title}"
  * Subtitle: "Powered by Vivek Chhikara"
  * **Session Security:**
    * No "Remember Me" checkbox
    * Session-only cookies (auto logout on browser close)
    * Redirect to VSC dashboard after successful login

### 2. Activity Logging (Global Scope)

* **Comprehensive Logging:**
  * Plugin/theme changes (install, activate, deactivate, delete, update)
  * User logins, failed logins, logouts
  * Snippet saves, SMTP sends
  * VSC setting edits
  * Content changes (posts, pages, products)
  * System events

* **Advanced UI Features:**
  * Full log table with pagination (50 entries per page)
  * **Filter by Severity:** Info, Warning, Critical
  * **Filter by Action Type:** Dynamic list from database
  * **Search Functionality:** By object name, details, or IP address
  * **Statistics Panel:** Total logs, 24-hour counts by severity
  * **Color-Coded Rows:** Visual severity indicators
  * Real-time updates

* DB Table: `wp_vsc_logs`
* UI: Visible under Vivek DevOps > Activity Log
* Export option via CSV (coming soon)

### 3. Login Control (Honeytrap + Custom URL)

* Login URL = `/vsc`
* All default WP login routes remain exposed as honeypots
* Behavior:

  * If brute-force login fails ≥ 1 times:

    * First strike: IP blocked for 24 hours
    * Second strike: IP permanently blacklisted
* IP stored in DB table `wp_vsc_ip_blocks`

### 4. Admin Role Restrictions

* Master Admin (Mighty-Vivek): full unrestricted access with forced step 2 verification as soon as plugin is activated
* Other admins:

  * Cannot:

    * Add/delete/edit other admins
    * Install/delete/activate themes or plugins
    * Access Elementor system settings
    * Change permalink/general settings
  * Can:

    * Edit content, products, orders
    * View VSC dashboard, but **not edit settings**

### 5. Integrity Checker

* Activated via toggle (admin only)
* Stores baseline hashes of plugin files + `wp-config.php`
* Compares current hash → triggers:

  * DB log entry
  * Email alert (via SMTP)
* Run periodically (daily cron)
* Table: `vsc_integrity_baseline`

### 6. Code Snippet Injector

* Admin-only interface for:

  * PHP
  * CSS
  * JS
* Saved in DB table: `vsc_code_snippets`
* Activation toggle per snippet
* Backend:

  * Validates syntax before saving
  * Errors logged if eval fails

### 7. SMTP Manager

* Pre-integrated with **Brevo**:

  * Host: `smtp-relay.brevo.com`
  * Port: 587 (TLS)
  * Default sender: [support@chhikara.in](mailto:support@chhikara.in)
  * Limit: 300/day
  * give option to add these details in the form 
* Mail types:

  * Security: failed logins, modified files, etc. (immediate)
  * Digest: plugin/theme/core updates (daily 9 AM)
  * WooCommerce: new orders/inquiries
* Logs available under SMTP tab

### 8. Two-Factor Authentication

* For all admins
* Implemented via TOTP standard (e.g., Google Authenticator, Authy)
* QR Code Generation:

  * Must use proven TOTP PHP library (e.g.,Google’s PHP QR library)
* Flow:

  * Login with password
  * Redirect to `site.com/vsc-2fa`
  * Require 6-digit TOTP

### 9. REST API + API Key Control

* Endpoint: `/wp-json/vsc/v1/status`
* Header: `X-VSC-KEY`
* Response:

  ```json
  {
    "ok": true,
    "site": "Site Title",
    "time": "2025-11-14 09:00:00"
  }
  ```

### 10. Backup & Migration System

* **Complete Backup Functionality:**
  * Database backup to SQL file with manual generation
  * File backup (uploads, themes, plugins) using ZIP compression
  * Local storage in protected `wp-content/vsc-backups` directory
  * `.htaccess` protection for backup directory
  * Configurable backup components (database, uploads, themes, plugins)

* **Scheduled Backups:**
  * WP-Cron integration for automated backups
  * Configurable frequency per site (hourly, daily, weekly)
  * Retention policy with auto-cleanup of old backups
  * Configurable maximum backups to keep

* **Google Drive Integration:**
  * Automatic upload to Google Drive after backup creation
  * OAuth2 authentication (placeholder ready for implementation)
  * Seamless cloud storage synchronization

* **Restore Functionality:**
  * One-click restore from any backup
  * Database restoration from SQL file
  * File restoration with directory replacement
  * Progress indicators during restore process

* **Admin UI Features:**
  * Complete backup list table with file details
  * Download backups as ZIP files
  * Delete individual backups
  * AJAX-powered operations (create, restore, delete)
  * Real-time progress bars during operations
  * Settings panel for scheduled backups and retention
  * File size and date information for each backup

* **Security:**
  * Protected backup directory (non-web-accessible)
  * Master admin only access
  * Activity logging for all backup operations
  * Safe file operations with error handling

* UI: Visible under Vivek DevOps > Backup & Migration
* Provides same functionality as All-in-One WP Migration plugin

---

## 🔄 Activation Behavior

* Creates asset folder `/assets` under plugin dir (CSS/JS bundled)
* DB Options Set:

  * `vsc_master_username = Mighty-Vivek`
  * `vsc_master_email = support@chhikara.in`
  * `vsc_api_key` = generated token
  * `vsc_enable_integrity = off by default`
* On activation:

  * Create all DB tables
  * Generate baseline if integrity is on
  * Redirect logged-in user to `/vsc-dashboard`

---

## 🎨 Branding & UI

* **OLED Dark Theme (Complete Implementation):**
  * Pure black (#000000) primary background for battery optimization
  * Dark variations (#0a0a0a, #141414) for layering
  * White (#ffffff) primary text with light gray (#b0b0b0) secondary text
  * Blue (#3b82f6) for primary actions and links
  * Semantic colors: Red (#ef4444) for critical, Yellow (#f59e0b) for warnings, Green (#22c55e) for success
  * 21:1 contrast ratio for WCAG AAA compliance
  * CSS variables for centralized theming

* **Font & Typography:**
  * System UI stack for optimal performance
  * Consistent line heights and spacing
  * Clear hierarchy with size variations

* **UI Components:**
  * Buttons: Gradient backgrounds with hover states
  * Inputs: Dark backgrounds (#1a1a1a) with white text
  * Tables: Striped rows with hover effects
  * Alerts: Colored left border with appropriate backgrounds
  * Cards: Subtle borders with dark backgrounds
  * Forms: Clean, minimal design with clear labels
  * Badges: Colored backgrounds for status indicators

* **Settings Page Structure:**
  * Tabbed interface with 4 sections:
    1. **General:** Site settings and preferences
    2. **2FA:** TOTP activation status with setup/disable options
    3. **Security:** IP blocking statistics and security metrics
    4. **SMTP:** Brevo integration with daily usage tracking

* **Responsive Design:**
  * Mobile-first approach
  * Breakpoints at 768px and 1024px
  * Touch-friendly interface elements

* References: `vivek-security-final-colors.html`, `vivek-security-core-styles.css`, `vsc-ui-rebranding.css`

---

## 📋 Versioning Policy

* **Start**: `V-1`
* **Update After Each Sprint**: `V-1.1`, `V-1.2`, etc.
* **Visible In Plugin UI**

---

## ✅ Deliverables Checklist for Devs

* [x] Plugin activates cleanly
* [x] Redirects to `/vsc-dashboard`
* [x] Master admin set and stored
* [x] All modules stubbed or operational
* [x] Asset folder autogenerates
* [x] Version number set in UI
* [x] QR code flow tested with proven library
* [x] UI matches uploaded spec
* [x] Complete OLED dark theme implemented
* [x] Session-only cookies (auto logout on browser close)
* [x] Login redirects to VSC dashboard
* [x] WordPress Dashboard menu removed
* [x] Posts renamed to Blogs
* [x] Vivek DevOps menu moved to top position
* [x] Settings page with 4 tabs (General, 2FA, Security, SMTP)
* [x] Activity Log viewer with filtering and search
* [x] Updates page under VSC menu
* [x] Backup & Migration system with full functionality
* [x] Menu hiding for non-master admins
* [x] 2FA activation UI in Settings
* [ ] Google Drive OAuth implementation (placeholder ready)
* [ ] Honeypot trap for /wp-admin and /wp-login.php (pending testing)
* [ ] Custom post management UI (future enhancement)

---

## 📎 Linked UI Resources

* `vivek-security-final-colors.html`
* `vivek-security-core-styles.css`
* `OPTIMIZATION-SUMMARY.md`
* `COLOR-SCHEME-REFERENCE.md`

---

Let me know if you’d like a `.md` export or PDF version of this document for offline sharing.
