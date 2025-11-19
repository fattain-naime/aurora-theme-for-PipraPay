=== Aurora Gateway ===
Contributors: Fattain Naime
Tags: checkout, responsive, minimal, aurora
Requires PHP: 8.1
Requires at least: 1.0
Stable tag: 1.0.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

== Description ==
Aurora is a lightweight payment theme for PipraPay. It focuses on a quick payment summary, clean method grid, and an elegant status screen once the payment moves out of the initialize phase.

== Features ==
* Responsive card layout for the checkout summary and method grid.
* Support block that mirrors the global support links configured in Settings -> General.
* Minimal FAQ block (optional) fed by the global FAQ list.
* Customizable hero texts, accent color, and gradient from Appearance -> Customize.

== Installation ==
1. In the PipraPay dashboard open Appearance -> Themes and click Add New.
2. Upload the aurora and aurora-invoice ZIP one by one, wait for the success toast, then activate the theme from the Themes list.

== Frequently Asked Questions ==
= Can I hide the FAQ block? =
Yes. Switch off "Show FAQ block" under Appearance -> Customize while Aurora is active.

= Can I add custom scripts? =
Add them to views/initialize-ui.php. Keep the pp_allowed_access guard at the top intact.

== Changelog ==
= 1.0.0 =
* Initial release.
