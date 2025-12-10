<p align="center">
  <img src="assets/logo/smart-renew-tracker-logo-light.png" alt="Smart Renew Tracker Logo" width="180">
</p>

<h1 align="center">Smart Renew Tracker</h1>

<p align="center">
  A lightweight, enterprise-grade WordPress plugin to track domain, hosting, and SSL renewals securely.
</p>

<p align="center">
  <a href="https://madebyiman.com" target="_blank">🌐 madebyiman.com</a> • 
  <a href="https://github.com/madebyiman/smart-renew-tracker" target="_blank">GitHub</a> • 
  <a href="https://www.linkedin.com/in/iman-hossein-gholizadeh" target="_blank">LinkedIn</a>
</p>

---

## 🚀 Overview

**Smart Renew Tracker** allows agencies to manage client domain, hosting, and SSL renewals directly inside WordPress without relying on insecure spreadsheets.  
Get clear dashboard alerts before expirations and export reports instantly.

---

## ✨ Features

- 🧭 **Centralized Hub:** Manage all renewals (domain, hosting, SSL) in one list.
- ⚡ **Automated Alerts:** Color-coded dashboard widgets for upcoming expirations.
- ⚙️ **Custom Thresholds:** Set your own alert timeline (e.g., 30 days before expiry).
- 🧩 **Zero Dependencies:** No external API calls. No bloat.
- 🔒 **Data Sovereignty:** 100% local data storage.

---

## 🛡️ Engineering & Security

Built with **WordPress VIP** coding standards in mind:

- **Strict Sanitization:** All inputs are sanitized using `sanitize_text_field` and custom validation.
- **Late Escaping:** All outputs use late escaping (`esc_html`, `esc_attr`) to prevent XSS.
- **Namespaced:** Fully namespaced (`MadeByIman\SmartRenewTracker`) to ensure zero conflicts.
- **Secure Nonces:** Admin actions are protected with WordPress Nonces.

---

## 📂 Project Structure

```text
smart-renew-tracker/
├── assets/          # CSS, JS & Images
├── includes/        # Logic classes (Admin, Alerts, Libs)
├── smart-renew-tracker.php  # Main bootstrapper
└── readme.txt       # WordPress.org repository definition
```

🛠 Installation
Download the .zip file from the Releases page.

Go to Plugins > Add New > Upload Plugin.

Activate Smart Renew Tracker.

<p align="center">Developed with ❤️ by <a href="https://madebyiman.com">Iman</a></p>