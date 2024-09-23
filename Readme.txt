=== Fusion Pay Payment Gateway ===
Contributors: Yayadev
Donate link: https://www.pay.moneyfusion.net/Faire_un_don_1726979068528/
Tags: paiements, payments, gateway, fusionpay, ecommerce, api, wave, wave-ci, moneyfusion, mtn-money, orange-money, moov-money, woocommerce
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 0.0.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Fusion Pay Payment Gateway allows you to seamlessly integrate the Fusion Pay payment system into your WordPress website and WooCommerce store.

== Description ==

Fusion Pay Payment Gateway is a custom WordPress plugin that integrates the Fusion Pay payment system into your WordPress website and WooCommerce store. This plugin provides a user-friendly interface for configuring payment settings and offers seamless integration with WooCommerce.

= Key Features =
* Easy installation and setup
* Customizable API settings for payment integration
* WooCommerce integration for easy e-commerce payments
* Ability to define articles and total price in the payment form
* Multilingual support (English and French)
* Shortcode support for embedding payment form on any page
* Dynamic handling of articles in the payment form

== Installation ==

1. Download the plugin from the [Fusion Pay Plugin Repository](https://wordpress.org/plugins/fusionpay).
2. Upload the `fusionpay` folder to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. If using WooCommerce, navigate to WooCommerce > Settings > Payments to configure the Fusion Pay gateway.
5. For standalone use, navigate to the "Fusion Pay" settings page in the admin dashboard to configure your API and payment settings.

== Frequently Asked Questions ==

= How do I configure the Fusion Pay plugin for standalone use? =
After activating the plugin, go to the Fusion Pay menu in the WordPress admin panel. Fill in your API URL, Return URL, and set your articles and total price. You can also choose the form language (English or French).

= How do I configure Fusion Pay for WooCommerce? =
Go to WooCommerce > Settings > Payments, find Fusion Pay in the list of payment methods, and click "Manage". Enter your API URL and Return URL, then save the settings.

= How do I display the standalone payment form? =
Use the shortcode `[fusion_pay_form]` on any page or post to display the Fusion Pay form.

= Is Fusion Pay available as a payment method in WooCommerce checkout? =
Yes, once configured, Fusion Pay will appear as a payment option during the WooCommerce checkout process.

= How can I contribute to the development of this plugin? =
You can visit the [GitHub Repository](https://github.com/Yaya12085/wp-fusionpay) to contribute to the development of this plugin.

= How can I report a bug or request a feature? =
You can report bugs or request features on the [GitHub Issues Page](https://github.com/Yaya12085/wp-fusionpay/issues).

== Screenshots ==

1. Admin settings panel for configuring API and payment settings.
2. Frontend payment form displaying articles and total price.
3. WooCommerce payment settings page showing Fusion Pay configuration.
4. Fusion Pay option in WooCommerce checkout.

== Changelog ==

= 0.0.2 =
* Added WooCommerce integration.
* Updated settings page to include WooCommerce-specific options.

= 0.0.1 =
* Initial release of Fusion Pay Payment Gateway plugin.