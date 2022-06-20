=== Rave Woocommerce Payment Gateway ===
Contributors: theflutterwave
Tags: rave,flutterwave, woocommerce, payments, nigeria, mastercard, visa, target,Naira,payments,verve,donation,church,shop,store, ghana, kenya, international, mastercard, visa
Requires at least: 3.1
Tested up to: 6.0
Stable tag: 2.2.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Accept Credit card, Debit card and Bank account payment directly on your store with the Flutterwave's Rave payment gateway for Wordpress Woo commerce.

== Description ==

Accept Credit card, Debit card and Bank account payment directly on your store with the official Rave payment gateway for Woo commerce.

Rave is available in:

* __Nigeria__
* __Ghana__
* __Kenya__
* __Uganda__
* __Tanzania__
* __Rwanda__
* __South Africa__
* __Zambia__
* __Sierra Leone__

= Plugin Features =

*   __Accept payment__ via MasterCard, Visa, Mpesa, TIGO, Mobile money, Bank accounts and Barter.


= Suggestions / Feature Request =

Kindly read through the article [here](https://support.flutterwave.com/en/articles/3632754-how-to-integrate-flutterwave-into-woocommerce) for instructions 
on how to install the plugin

If you have suggestions or a new feature request, feel free to get in touch with us via [https://rave.flutterwave.com](https://rave.flutterwave.com)

You can also follow us on Twitter! **[@theflutterwave](https://twitter.com/theflutterwave)**


== Installation ==
= Requirements =
Make sure you have the following plugins already installed
*   WooCommerce 
*   WooCommerce Shipping & Tax 
*   Facebook for WooCommerce 
*   Google Ads & Marketing by Kliken

= Automatic Installation =
*   Login to your WordPress Dashboard.
*   Click on "Plugins > Add New" from the left menu.
*   In the search box type __Rave Woocommerce Payment Gateway__.
*   Click on __Install Now__ on __Rave Woocommerce Payment Gateway__ to install the plugin on your site.
*   Confirm the installation.
*   Activate the plugin.
*   Click on "WooCommerce > Settings" from the left menu and click the "Checkout" tab.
*   Click on the __Rave__ link from the available Checkout Options
*   Configure your __Rave Payment Gateway__ settings accordingly.

= Manual Installation =
1.  Download the plugin zip file.
2.  Login to your WordPress Admin. Click on "Plugins > Add New" from the left menu.
3.  Click on the "Upload" option, then click "Choose File" to select the zip file you downloaded. Click "OK" and "Install Now" to complete the installation.
4.  Activate the plugin.
5.  Click on "WooCommerce > Settings" from the left menu and click the "Checkout" tab.
6.  Click on the __Rave__ link from the available Checkout Options
7. Configure your __Rave Payment Gateway__ settings accordingly.

= Best Practices =
1. When in doubt about a transaction, always check the Flutterwave Dashboard to confirm the status of a transaction.
2. Always ensure you keep your API keys securely and privately. Do not share with anyone
3. Ensure you change from the default secret hash on the Wordpress admin and apply same on the Flutterwave Dashboard
4. Always ensure you install the most recent version of the Flutterwave Wordpress plugin

For FTP manual installation, [check here](http://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Configure the plugin =
To configure the plugin, go to __WooCommerce > Settings__ from the left menu, click __Checkout__ tab. Click on __Rave__.

* __Enable/Disable__ - check the box to enable Rave Payment Gateway.
* __Pay Button Public Key__ - Enter your public key which can be retrieved from the "Pay Buttons" page on your Rave account dashboard.
* __Modal Title__ - (Optional) customize the title of the Pay Modal. Default is Rave.
* Click __Save Changes__ to save your changes.

== Webhooks ==
Handle Webhooks from Flutterwave with two new actions in WooCommerce.
* flw_webhook_after_action : This action is fired after a transaction is completed and returns the transaction details (json).
* flw_webhook_transaction_failure_action : This action is fired when a transaction fails and returns the transaction details (json).



== Frequently Asked Questions ==

= What Do I Need To Use The Plugin =

1. You need to open an account on [Rave](https://rave.flutterwave.com)




== Changelog ==
= 2.2.8 =
*  Fixed: Woocommerce Subscription processing function error.
*  New Feat: Switched to WC-Logger class for logging.
= 2.2.7 =
* fix: on payment completion redirect to order reciept page (redirect Method)
* fix: PHP 8.0 compatibility ( optional method parameter )
= 2.2.0 =
* Use one base URL for live and test mode.

* Merchants can get their [test and live](https://developer.flutterwave.com/docs/api-keys) keys [here](https://rave.flutterwave.com/dashboard/settings/apis)

* Using test keys keeps you in test mode, to move to live mode add live keys.

* Support for Woocommerce recurring, this allows merchants to collect recurring payments in woocommerce.

= 2.1.0 =
* Support for Woocommerce recurring, this allows merchants to collect recurring payments in woocommerce.

= 2.0.0 =
* Support for new currencies (ZMW, UGX, RWF, TZS, SLL).

= 1.0.1 =
*   Add redirect style with admin toogle for redirect or popup payment style 
*   Custom gateway name 
*   Bug fixes for country 

= 1.0.0 =
*   First release

== Screenshots ==



== Other Notes ==