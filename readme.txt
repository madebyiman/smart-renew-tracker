=== Smart Renew Tracker ===
Contributors: madebyiman
Tags: renewals, hosting, domain, reminder, alert, notification, expiry
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.0.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight plugin to track and manage your domain, hosting, and SSL renewals directly inside WordPress.

== Description ==

Smart Renew Tracker helps you stay on top of your clients’ renewals.
Never lose track of domain, hosting, or SSL expiry dates again — the plugin shows you clear dashboard alerts before your renewals expire.

Perfect for:
- Freelancers who manage multiple client sites
- Web agencies with recurring hosting or domain renewals
- Business owners who want automatic reminders

**Main Features:**
- Add and manage renewals (domain, hosting, SSL, etc.)
- Dashboard alert for upcoming renewals
- Customizable alert threshold (number of days before expiry)
- Clean, minimal design and easy-to-use interface

This plugin runs entirely inside your WordPress dashboard — no third-party APIs or external dependencies.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/smart-renew-tracker/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Renew Tracker → Add New Renewal** to create your first record.
4. Optionally set your alert threshold under **Renew Tracker → Settings**.

== Screenshots ==
1. Renewals list inside WordPress admin.
2. Meta box for entering renewal details.
3. Dashboard alert for upcoming expirations.
4. Settings screen for alert days configuration.

== Frequently Asked Questions ==

= Can I track renewals for multiple clients? =
Yes. You can create as many renewal entries as you need, and categorize them by type (domain, hosting, SSL, etc).

= Can I customize how many days before expiry I get alerts? =
Yes, under **Renew Tracker → Settings**, you can define the number of days in advance you want to receive alerts.

= Does this plugin send any data externally? =
No. All data stays inside your WordPress database.

== Changelog ==

= 1.0.1 =
* Security Fix: Improved sanitization and escaping for inputs and outputs.
* Fix: Updated prefixes to ensure compatibility and prevent conflicts.
* Fix: Resolved generic function names issue.

= 1.0 =
* Initial release.
* Track renewals for domain, hosting, SSL.
* Dashboard alert for upcoming expirations.
* Customizable alert threshold.

== Upgrade Notice ==

= 1.0.1 =
Security update: Fixed sanitization and escaping issues. Recommended update.

== License ==
This plugin is licensed under the GPLv2 or later.
You are free to modify, share, and redistribute it under the same license.