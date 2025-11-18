# Vivek DevOps - 2FA Login System

## Overview
Custom secure login system with Two-Factor Authentication (2FA) using Google Authenticator for administrator accounts.

## Features

### 1. Custom Login URL
- **Primary URL**: `yoursite.com/vsc`
- **Alternative URL**: `yoursite.com/vsc-login`
- Clean, dark-themed login interface
- Session-based authentication (expires on browser close)

### 2. Two-Factor Authentication
- **Google Authenticator Integration**: TOTP-based (Time-based One-Time Password)
- **6-digit codes**: Refreshes every 30 seconds
- **Administrator-only**: Only enforced for users with administrator role
- **Code replay protection**: Recently used codes are blocked

### 3. Login Flow

#### First-Time Admin Login:
1. Visit `yoursite.com/vsc`
2. Enter email and password
3. Redirected to QR code setup page
4. Scan QR code with Google Authenticator app
5. Enter 6-digit verification code
6. Setup complete - logged into dashboard

#### Subsequent Logins:
1. Visit `yoursite.com/vsc`
2. Enter email and password
3. Enter current 6-digit code from Google Authenticator
4. Logged into dashboard

### 4. Session Management
- **Browser-close expiration**: Sessions automatically expire when browser/tab closes
- **Server-side timeout**: 2-hour maximum session lifetime
- **No "Remember Me"**: Security-focused design

## Security Features

### Encryption
- Secret keys encrypted using AES-128-CBC
- Encryption key derived from WordPress `AUTH_KEY` constant + user ID
- IV (Initialization Vector) randomly generated per encryption

### Code Validation
- **Time window**: Accepts codes from current window + 2 previous + 1 future (90 seconds total)
- **Replay protection**: Last 3 codes are stored and rejected if reused
- **Constant-time comparison**: Uses `hash_equals()` to prevent timing attacks

### Database Storage
User meta keys:
- `vsc_2fa_secret` - Encrypted TOTP secret key
- `vsc_2fa_enabled` - Boolean flag for 2FA activation status
- `vsc_2fa_recent_codes` - Array of recently used code hashes
- `vsc_2fa_last_login` - Timestamp of last successful login

## Technical Implementation

### Libraries Used
1. **VSC_HOTP** - HOTP/TOTP code generation (RFC 4226/6238 compliant)
2. **VSC_Base32** - Base32 encoding for Google Authenticator compatibility
3. **QRCode.js** - Client-side QR code generation

### File Structure
```
vivek-devops/
├── includes/
│   ├── class-vsc-auth.php           # Main authentication class
│   └── lib/
│       ├── class-vsc-hotp.php       # HOTP/TOTP implementation
│       └── class-vsc-base32.php     # Base32 encoder/decoder
├── templates/
│   ├── login-page.php               # Main login form
│   ├── qr-setup-page.php            # QR code setup page
│   └── 2fa-verify-page.php          # 2FA verification page
└── vivek-devops.php                 # Main plugin file
```

### URL Routing
Custom URL routing implemented via WordPress `init` action:
- `/vsc` or `/vsc-login` → Login page
- `/vsc/setup-2fa` → QR code setup (protected by session)
- `/vsc/verify-2fa` → 2FA verification (protected by session)

## Manual 2FA Reset

If an administrator loses access to their Google Authenticator:

### Method 1: Database (Recommended)
```sql
DELETE FROM wp_usermeta
WHERE user_id = [USER_ID]
AND meta_key IN ('vsc_2fa_secret', 'vsc_2fa_enabled', 'vsc_2fa_recent_codes');
```

### Method 2: PHP Code (via function)
```php
$auth = VSC_Auth::get_instance();
$auth->reset_2fa($user_id);
```

Next login will prompt the user to set up 2FA again with a new QR code.

## Browser Compatibility
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers supported

## Dependencies
- PHP 7.0+ (7.4+ recommended)
- OpenSSL extension (for encryption)
- WordPress 5.0+

## Future Enhancements (Not Yet Implemented)
- Honeypot system for wp-login.php and wp-admin
- IP-based blocking with database storage
- Email recovery workflow
- Emergency backup codes
- Multi-device support
- Login attempt logging

## Changelog

### V-2.0.0 (Current)
- ✅ Custom login page at /vsc
- ✅ Google Authenticator 2FA integration
- ✅ QR code setup flow
- ✅ Session management with browser-close expiration
- ✅ Administrator-only enforcement
- ✅ Dark theme UI matching plugin design
- ✅ Code replay protection
- ✅ Manual 2FA reset capability

### V-1.1.2 (Previous Stable)
- iframe-based WordPress admin wrapper
- Dark Reader integration
- Custom horizontal menu
- User avatar positioning
- Logo/text alignment fixes

---

**Author**: Mighty-Vivek
**Website**: https://chhikara.in
**Version**: V-2.0.0
**Last Updated**: 2025-11-15
