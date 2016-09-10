[![Code Climate](https://codeclimate.com/repos/541a8a00e30ba0357e00b705/badges/97029fc517243d0ad87f/gpa.svg)](https://codeclimate.com/repos/541a8a00e30ba0357e00b705/feed)  [![Build Status](https://magnum.travis-ci.com/Yoast/ga-ecommerce.svg?token=ueDfspef2n9Zr4KvPQTU&branch=master)](https://magnum.travis-ci.com/Yoast/ga-ecommerce)

Google Analytics eCommerce
==========================

Google Analytics eCommerce addon for Google Analytics by MonsterInsights.

https://www.monsterinsights.com/pricing/

Changelog
---------
### 5.5.2: July 7th, 2016
* Compatibility with the new license manager update
* Added compatibility for shiny updates v2

### 5.5: May 1st, 2016
* Introduces better way to track orders into GA, which fixes negative order total issues in GA.
* Fixes an issue where sometimes the base plugin version wasn't comapared correctly, resulting in the output of an error message needlessly.
* Raises the required main plugin version to 5.4.9 or higher (currently both base plugins have a latest version of 5.5).

### 5.4.9: April 15th, 2016
* Compatibility fix with base plugins

### 5.4.8: April 14th, 2016
* Compatibility fix with base plugins

### 5.4.7: April 14th, 2016
* Fixes compatibility issue with WordPress 4.5.
* Adds ground work for new features to come.

### 3.0.5: July 15th, 2015
* Fixes a bug where the GA cookie was no longer correctly read, resulting in transaction sources always being tracked as direct.
* Adds 4 translations: bg_BG, en_AU, lt_LT, pt_BR.

### 3.0.4: March 30th, 2015
* Fixes a bug for WooCommerce eCommerce tracking where an incorrect item price was sent to GA in case of multiple items being added to the basket.
* Adds translations for German (de_DE).

### 3.0.3: February 19th, 2014
* Shows a notice if universal tracking is disabled
* Fixes a bug where discounts weren't taken into account with EDD orders.
* Fixes an encoding issue with the order name for EDD orders.
* Makes sure the license activation link points to the right url.
* Contains a few string improvements for en_US.
* Adds translations for 15 languages.

### 3.0.2: October 14th, 2014
* Support for multiple order statuses to be measured as completed.

* WooCommerce tracking: fixes compatibility with plugins changing the order number like WooCommerce Sequential Order number.
* WooCommerce tracking: orders set to "processing" will now show up as completed in Analytics too.

### 3.0.1: September 19th, 2014
* Fixes version_compare bug where a warning displays when using the minimal required version of Google Analytics by Yoast.

### 3.0: September 17th, 2014
* Full rewrite of the tracking to work with Google Analytics Universal. This means the plugins tracking should now no longer miss _any_ transactions.
* Added support for WooCommerce tracking and renamed to Google Analytics eCommerce tracking.

### 1.0

* Initial release.
