=== Smart Renew Tracker ===
Contributors: madebyiman
Tags: renewals, hosting, domain, reminder, alert, notification, expiry
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.1.2
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight plugin to track and manage your domain, hosting, and SSL renewals directly inside WordPress.

== Description ==

Smart Renew Tracker helps you stay on top of your clients’ renewals.
Never lose track of domain, hosting, or SSL expiry dates again — the plugin shows you clear dashboard alerts and sends automated email notifications before your renewals expire.

Perfect for:
- Freelancers who manage multiple client sites
- Web agencies with recurring hosting or domain renewals
- Business owners who want automatic reminders

**Main Features:**
- Add and manage renewals (domain, hosting, SSL, etc.)
- Dashboard alerts for upcoming renewals
- **New:** Automated Email Notification system
- **New:** AJAX-powered "Send Test Email" for instant verification
- Customizable alert threshold (number of days before expiry)
- Clean, minimal design and easy-to-use interface

This plugin runs entirely inside your WordPress dashboard — no third-party APIs or external dependencies.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/smart-renew-tracker/` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Renew Tracker → Add New Renewal** to create your first record.
4. Set your email and alert threshold under **Renew Tracker → Settings**.

== Screenshots ==
1. Renewals list inside WordPress admin.
2. Meta box for entering renewal details.
3. Dashboard alert for upcoming expirations.
4. Settings screen for alert days and email configuration.

== Frequently Asked Questions ==

= Can I track renewals for multiple clients? =
Yes. You can create as many renewal entries as you need, and categorize them by type (domain, hosting, SSL, etc).

= How do email notifications work? =
The plugin uses WP-Cron to check for expiring services daily and sends an HTML email to the address specified in settings.

= Can I customize how many days before expiry I get alerts? =
Yes, under **Renew Tracker → Settings**, you can define the number of days in advance you want to receive alerts.

== Changelog ==

= 1.1.1 =
* Fix: Improved synchronization between GitHub and WordPress.org repository.
* Fix: Minor UI adjustments for the "Send Test Email" button.

= 1.1.0 =
* New: Added an automated Email Notification system to alert users before renewals expire.
* New: Added an AJAX-powered "Send Test Email" button in the settings for instant verification.
* Improved: Completely refactored the admin logic into a clean, Object-Oriented (OOP) structure.
* Improved: Updated Settings UI with dedicated fields for notification email and alert days.
* Improved: Integrated WP-Cron for reliable, automated daily checks of expiring services.

= 1.0.1 =
* Security Fix: Improved sanitization and escaping for inputs and outputs.
* Fix: Updated prefixes to ensure compatibility and prevent conflicts.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 1.1.2 =
Minor bug fixes and repository sync improvements. Recommended for all users.

= 1.1.0 =
Major Update: Added automated email notifications and major performance improvements via OOP refactoring.

== License ==
This plugin is licensed under the GPLv2 or later.