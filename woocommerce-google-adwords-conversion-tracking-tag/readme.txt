=== Plugin Name ===
Contributors: alekv
Donate link: http://www.wolfundbaer.ch/donations/
Tags: woocommerce, woocommerce conversion tracking, google adwords, adwords, conversion, conversion value, conversion tag, conversion pixel, conversion value tracking, conversion tracking, conversion tracking adwords, conversion tracking pixel, conversion tracking script, track conversions, tracking code manager
Requires at least: 3.1
Tested up to: 4.5.3
Stable tag: 1.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Track the dynamic order value in AdWords from WooCommerce

== Description ==

This plugin <strong>tracks the value of WooCommerce orders in Google AdWords</strong>. With this you can optimize all your AdWords campaings to achieve maximum efficiency.

<strong>Highlights</strong>

* Very accurate by preventing duplicate reporting effectively, excluding admins and shop managers from tracking, and not counting failed payments.
* Very easy to install and maintain.

<strong>Requirements</strong>

* The payment gateway **must** support on-site payments. If you want to use an off-site payment solution like the free PayPal extension you need to make sure that the visitor is being redirected back to the WooCommerce thankyou page after the successful transaction. Only if the redirection is set up properly and the visitor doesn't stop the redirection, only then the conversion will be counted.

<strong>Other plugins</strong>

If you like this plugin you might like that one too: https://wordpress.org/plugins/woocommerce-google-dynamic-retargeting-tag/

<strong>Supported languages</strong>

* English
* German
* Serbian ( by Adrijana Nikolic http://webhostinggeeks.com )
 
== Installation ==

1. Upload the WGACT Plugin directory into your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Get the AdWords conversion ID and the conversion label. You will find both values in the AdWords conversion tracking code. 
4. In the WordPress admin panel go to settings and then into the WGACT Plugin Menu. Please enter the conversion ID and the conversion label into their respective fields.
5. Delete any other instances of the AdWords tracking code which tracks sales. (You might have several AdWords tracking codes, eg. tracking newsletter applications. Keep those.)
6. Delete the cache on your server and on your browser.
7. Check if the AdWords tag is running fine by placing a test order (ideally click on one of your AdWords ads first) and then check with the Google Tag Assistant browser plugin if the tag has been inserted correctly on the thank you page. Bear in mind that the code is only visible if you are not logged in as admin or shop manager. You will have to log out first.

== Frequently Asked Questions ==

= How do I check if the plugin is working properly? =

1. Log out of the shop.
2. Search for one of your keywords and click on one of your ads.
3. Purchase an item from your shop.
4. Wait until the conversion shows up in AdWords.

With the Google Tag Assistant you will also be able to see the tag fired on the thankyou page.

= Where can I report a bug or suggest improvements? =

Please post your problem in the WGACT Support forum: http://wordpress.org/support/plugin/woocommerce-google-adwords-conversion-tracking-tag
You can send the link to the front page of your shop too if you think it would be of help.

== Screenshots ==

== Changelog ==

= 1.3.3 =
* Tweak: Refurbishment of the settings page
= 1.3.2 =
* New: Uninstall routine
= 1.3.1 =
* New: Keep old deduplication logic in the code as per recommendation by AdWords
= 1.3.0 =
* New: AdWords native order ID deduplication variable
= 1.2.2 =
* New: Filter for the conversion value
= 1.2.1 =
* Fix: wrong conversion value fix
= 1.2 =
* New: Filter for the conversion value
= 1.1 =
* Tweak: Code cleanup
* Tweak: To avoid over reporting only insert the retargeting code for visitors, not shop managers and admins
= 1.0.6 =
* Tweak: Switching single pixel function from transient to post meta
= 1.0.5 =
* Fix: Adding session handling to avoid duplications
= 1.0.4 =
* Fix: Skipping a tag version
= 1.0.3 =
* Fix: Implement different logic to exclude failed orders as the old one is too restrictive
= 1.0.2 =
* Fix: Exclude orders where the payment has failed
= 1.0.1 =
* New: Banner and icon
* Update: Name change
= 1.0 =
* New: Translation into Serbian by Adrijana Nikolic from http://webhostinggeeks.com
* Update: Release of version 1.0!
= 0.2.4 =
* Update: Minor update to the internationalization
= 0.2.3 =
* Update: Minor update to the internationalization
= 0.2.2 =
* New: The plugin is now translation ready
= 0.2.1 =
* Update: Improving plugin security
* Update: Moved the settings to the submenu of WooCommerce
= 0.2.0 =
* Update: Further improving cross browser compatibility
= 0.1.9 =
* Update: Implemented a much better workaround tor the CDATA issue
* Update: Implemented the new currency field
* Fix: Corrected the missing slash dot after the order value
= 0.1.8 =
* Fix: Corrected the plugin source to prevent an error during activation 
= 0.1.7 =
* Significantly improved the database access to evaluate the order value.
= 0.1.6 =
* Added some PHP code to the tracking tag as recommended by Google. 
= 0.1.5 =
* Added settings field to the plugin page.
* Visual improvements to the options page.
= 0.1.4 =
* Changed the woo_foot hook to wp_footer to avoid problems with some themes. This should be more compatible with most themes as long as they use the wp_footer hook. 
= 0.1.3 =
* Changed conversion language to 'en'. 
= 0.1.2 =
* Disabled the check if WooCommerce is running. The check doesn't work properly with multisite WP installations, though the plugin does work with the multisite feature turned on. 
* Added more description in the code to explain why I've build a workaround to not place the tracking code into the thankyou template of WC.
= 0.1.1 =
* Some minor changes to the code
= 0.1 =
* This is the initial release of the plugin. There are no known bugs so far.
