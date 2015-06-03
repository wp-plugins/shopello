=== Plugin Name ===
Contributors: Shopello AB
Donate link: http://shopello.se/
Tags: affiliate, shopping, e-commerce, shopping comparison, shopping search, fashion search engine
Requires at least: 4.2.0
Tested up to: 4.2.2
Stable tag: 2.6.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A neat plugin that gives you two shortcodes and enables you to make money
through affiliate by providing the whole product list of shopello.se!



== Description ==
This plugin enables your wordpress website to make use of a listing shortcode.
Mainly focused on enabling You to display one or more selections of products
from the products available at Shopello.se, Shopello.no and Shopello.dk. Using
you API key you get access to a simplified admin interface to compose lists of
products which are fetched directly from Shopello's JSON API.

Read more at https://www.shopelloapi.com/

If you'd like to extend or suggest functionality - please notify us at
partner@shopello.se! To get your unique API-key start earn money contact same
email adress.



== Installation ==
How to install and configure the plugin:

1. Extract the folder "shopello" in your wordpress installations plugin folder.
2. Login to your site and go to the Plugins section.
3. Enable the plugin "Shopello API".
4. Go to "Shopello" and fill in the API-key and API-endpoint.
5. Synchronize the categories before you start using the plugin.

If you do not have an API key, please visit https://www.shopelloapi.com/ to
request one.



== Frequently Asked Questions ==
= Why cannot I use any of the shortcodes? =
Make sure you have entered a correct API-key and API-endpoint. If you have
problems with this, please contact partner@shopello.se



== Changelog ==
= 2.6.0 =
* Added mediaquerys for the product grid to work better on cellphones.

= 2.5.2 =
* Fixed regression in the Listing Management.
* Repaired a small case of database corruption.

= 2.5.1 =
* Fixed regression in the "Test API" button in settings.

= 2.5.0 =
* Some users experienced issues regarding "json_decode", fixed the cause of the
issue and added migration to repair the database.

= 2.4.4 =
* PHP 5.3 Regression
* Solved big issue in all AJAX-Calls on servers running some kind of
eAccelerator or other PHP-Extension that removes comments from the code.
* Resolved issue with the pagination if a custom pagesize is chosen.
* This update shouldn't affect any features, it's a big refactor of how we store
our listings. The migration should happen automaticly.

= 2.3.0 =
* Removed Bootstrap, this did have effect on the look of the entire page with
is the themes job and not this plugin's job. This release might affect the look
of your page. But this will cause less conflicts with your theme.

= 2.2.0 =
* Implemented Caching of API Requests, this will improve the pages responsetime
by a great deal.
* Code improvements

= 2.1.3 =
* Fixed regression in PHP 5.3
* Tested with Wordpress 4.2.2
* Hide errormessage from widget when visiting a page without listing.
* Documentation improvements
* Renamed some scripts to make sense
* Stopped including unused scripts
* Code improvements and polish
* Introducing the Danish translation

= 2.0.5 =
* Hide query in filters
* Updated to support Wordpress 4.2
* Minor change of the anchor-links for the product item, you can click anywhere
on the product in the listing now.
* Category filtering fix
* Small CSS fix for product listing
* Color filtering fix
* Rewrite of the entire productlisting and filter handling
* Major code improvements of AJAX related backend
* Updated translations (still only English and Swedish available).

= 1.10.2 =
* Improvements in fetching of products
* Improved handling of 404 errors from API, now it won't destroy the entire
page.
* Major code improvements
* Migrated from LESSCSS to SCSS
* Internationalization and Localization support, we currently have English and
Swedish.

= 1.9.8 =
* Major code improvements, no new features.
* Added initial support for autoloading with composer
* Added adminpage to check for CURL as dependency and to Ping the API.
* Minor changes to readme, just layouting of text.
* Added Screenshots to readme
* Readme and cosmetic fixes, no code or feature changes.
* More small fixes. We're progressing to get to version 2.0.0 which will be the
first public stable version.
* Small fixes.
* Major cleanup.

= 1.0.2 =
* First stable version released.

= 0.9 =
* First version released for Beta testing



== Upgrade Notice ==
When you update your plugin, please test your listings to make sure everything
still works.



== Usage Documentation ==
Shopello API Plugin Dokumentation

= Index =
1. [shopello_products]
2. [shopello_filters]

= 1. Product Listing =
The concept of lists is that you create a product-list in the Shopello settings
part of Wordpress Admin, then you have to create a post or page and add the
shortcode [shopello_products] and choose which listing to display in this post
or page.

= 2. Filters =
To display filters for a listing you (may) choose which filters to display when
creating the listing.

Then you can use the shortcode [shopello_filters] to include the filters or just
add the Widget to the layout.



== Screenshots ==
1. Example Frontend
2. Creating Searches
3. API Connection Settings
4. System Test
