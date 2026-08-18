=== COD Verify for WooCommerce ===
Contributors: protechnoid
Tags: cash on delivery, cod, order verification, woocommerce, fraud prevention
Requires at least: 6.8
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
Requires Plugins: woocommerce
WC requires at least: 8.0
WC tested up to: 10.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Reduce fake and abandoned Cash on Delivery orders by requiring customers to verify their order by email before it's processed.

== Description ==

COD Verify for WooCommerce adds a simple verification step to Cash on Delivery (COD) orders, so store owners can filter out fake, mistyped, or abandoned COD orders before they reach fulfilment.

When a customer places a COD order, the order is held in a **Pending Confirmation** status instead of moving straight to Processing. The customer receives an email with a secure verification link. If they click it within your chosen time limit, the order moves to Processing as normal. If they don't, the order is automatically cancelled — no manual cleanup required.

= How it works =

1. Customer places a Cash on Delivery order.
2. The order is moved to **Pending Confirmation** and a verification email is sent.
3. The customer clicks the link in the email to confirm the order is genuinely theirs.
4. Confirmed orders move to **Processing**. Orders left unconfirmed past your timeout are automatically cancelled.

= Key features =

* Automatic Pending Confirmation status for COD orders, separate from your normal order flow.
* Secure, single-use verification links with a configurable expiry (minutes or hours).
* Automatic cancellation of unconfirmed orders via WooCommerce's Action Scheduler — no cron plugin needed.
* **Resend verification link** — merchants can resend a fresh link to the customer directly from the order screen if the original wasn't received or has expired.
* **Manual confirmation override** — if a customer confirms by phone, WhatsApp, or another channel outside the plugin, the merchant can confirm the order on their behalf with one click, keeping a clear record that it was merchant-verified rather than customer-verified.
* At-a-glance verification status shown directly on the order edit screen, alongside WooCommerce's own order actions.
* Configurable email notifications for both store owner and customer — the core verification email is always sent (it's essential to the plugin working), everything else is optional.
* Fully compatible with WooCommerce High-Performance Order Storage (HPOS).
* Works alongside Rank Math and Yoast SEO — the verification page is automatically excluded from indexing.

= Why use order verification for COD? =

Cash on Delivery is popular in many markets, but it also attracts a disproportionate share of fake orders, prank orders, and orders placed with typos in the phone number or address — all of which cost you packaging, shipping, and return-handling fees when they fail at the doorstep. A short email confirmation step filters most of these out before they ever reach your warehouse, while leaving genuine customers largely unaffected.

== Installation ==

1. Make sure WooCommerce is installed and active.
2. Upload the `cod-verify-for-woocommerce` folder to `/wp-content/plugins/`, or install directly through the WordPress Plugins screen.
3. Activate the plugin through the **Plugins** screen in WordPress.
4. Go to **COD Verify → Settings** to configure the verification timeout and choose which email notifications you'd like enabled.
5. Place a test Cash on Delivery order to confirm the verification email arrives and the link works as expected.

== Frequently Asked Questions ==

= Does this work with High-Performance Order Storage (HPOS)? =

Yes. The plugin declares full compatibility with WooCommerce's custom order tables (HPOS) and has been tested with HPOS both enabled and disabled.

= What happens if the customer never clicks the verification link? =

The order is automatically cancelled once your configured timeout is reached. You can adjust this timeout (in minutes or hours) from the plugin's settings page.

= Can I resend the verification email if the customer says they didn't get it? =

Yes. Open the order and choose **Resend COD verification link** from the Order Actions dropdown. This issues a fresh link and invalidates the previous one.

= A customer confirmed their order over the phone instead of clicking the link — what do I do? =

Open the order and choose **Confirm order (verified by admin)** from the Order Actions dropdown. This confirms the order the same way a customer's own click would, but records that it was confirmed by the store owner rather than the customer, so you always have an accurate record.

= Can I turn off the verification email? =

No — the verification email is the mechanism the entire plugin relies on, so it's always sent and can't be disabled. All other notification emails (order confirmed, order cancelled, and the optional "new order awaiting verification" alert to the store owner) can be turned on or off individually from the plugin's settings page.

= Does this plugin work with caching plugins? =

Yes. The verification page sends no-cache headers and is excluded from search engine indexing automatically, including when using Rank Math or Yoast SEO.

= What happens to plugin data when I uninstall the plugin? =

[FILL IN: describe here exactly what your uninstall.php or uninstall routine does — e.g. whether order meta, custom order status registrations, and plugin settings are removed on uninstall, or left in place. This needs to match your actual uninstall behavior once you build it.]

== Screenshots ==

1. [FILL IN once you have the actual screenshot: e.g. "The COD Verify settings page, showing the verification timeout and email notification options."]
2. [FILL IN: e.g. "The customer-facing verification email with the confirmation link."]
3. [FILL IN: e.g. "The COD Verification status box shown on the WooCommerce order edit screen."]
4. [FILL IN: e.g. "Resend and manual confirmation actions available in the Order Actions dropdown."]

== Changelog ==

= 1.0.0 =
* Initial release.
* Cash on Delivery order verification via email with configurable timeout.
* Automatic cancellation of unconfirmed orders.
* Admin resend verification link.
* Admin manual confirmation override.
* Order-screen verification status indicator.
* Configurable per-email notification settings.
* Full WooCommerce HPOS compatibility.

== Upgrade Notice ==

= 1.0.0 =
Initial release.