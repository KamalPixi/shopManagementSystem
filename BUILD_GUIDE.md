# TECH PARISIEN - Build & Release Guide (NativePHP)

This guide documents the build commands, environment setup, and release procedures for **TECH PARISIEN** across macOS and Windows.

---

## 1. Building on macOS (Without Apple Developer Account)

When building on macOS without an official \$99/year Apple Developer certificate, code-signing auto-discovery must be disabled. This ensures `electron-builder` packages cleanly without attempting to notarize with Apple or corrupting the build midway.

### Apple Silicon (M1 / M2 / M3 / M4)
```bash
CSC_IDENTITY_AUTO_DISCOVERY=false php artisan native:build mac arm64
```
*Or using npm shortcut:*
```bash
npm run native:build:mac
```

### Intel Mac (x64)
```bash
CSC_IDENTITY_AUTO_DISCOVERY=false php artisan native:build mac x64
```
*Or using npm shortcut:*
```bash
npm run native:build:mac:x64
```

### Clean Build Cache (Reset)
If you ever encounter build cache glitches or want a completely fresh build:
```bash
php artisan native:reset
```
*Or:*
```bash
npm run native:reset
```

---

## 2. Opening on Client Mac (First-Time Installation)

Because the app is distributed directly (outside the Mac App Store without an Apple certificate), macOS Gatekeeper will show an alert on first launch:

1. Double-click the `.dmg` installer and drag **TECH PARISIEN** into **Applications**.
2. **First launch**:
   - Right-click (or Control-click) `TECH PARISIEN.app` $\rightarrow$ Click **Open** $\rightarrow$ Click **Open**.
   - *OR* Go to **System Settings $\rightarrow$ Privacy & Security** $\rightarrow$ Scroll down to Security $\rightarrow$ Click **Open Anyway**.
3. After this one-time confirmation, the app will open directly with a normal double-click forever.

---

## 3. Building for Windows

Windows builds do not require code-signing discovery flags:
```bash
php artisan native:build win
```
*Or using npm shortcut:*
```bash
npm run native:build:win
```
The resulting installer `TECH PARISIEN-1.0.0-setup.exe` will be located in:
```text
dist/win/
```

---

## 4. Internet Remote Access (Cloudflare Tunnel)

The application includes built-in Cloudflare Tunnel management:

- **Configuration:** Go to **Shop Settings $\rightarrow$ Internet Remote Access (Cloudflare Tunnel)**.
- **Controls:**
  - **Enable Remote Internet Access:** Master switch.
  - **Auto-start Tunnel when App Boots:** Automatically launches the background daemon when the desktop app starts.
  - **Custom Domain:** e.g. `pos.yourdomain.com`.
  - **Tunnel Token:** Zero Trust tunnel token from [dash.cloudflare.com](https://dash.cloudflare.com).
- **CLI Commands (for terminal management):**
  ```bash
  php artisan tunnel status
  php artisan tunnel start
  php artisan tunnel stop
  php artisan tunnel restart
  ```

---

## 5. First Run & Database Setup

The database uses SQLite at `database/database.sqlite`:
- Default Admin Credentials:
  - **Username:** `admin`
  - **Password:** `password`
- Reset and fresh seed command:
  ```bash
  php artisan migrate:fresh --seed
  ```
