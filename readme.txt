=== Lengow for WooCommerce ===
Contributors: lengowcompany
Tags: woocommerce, ecommerce, feed, orders, marketplace, amazon, google shopping, facebook, product catalog, feed management, lengow
Requires at least: 5.3
Tested up to: 5.8
Requires PHP: 5.5
Stable tag: 2.5.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Lengow is the e-commerce automation solution that helps brands and distributors improve their performance, automate their business processes, and grow internationally.

== Description ==

Lengow is the e-commerce automation solution that helps brands and distributors improve their performance, automate their business processes, and grow internationally. The Lengow platform is the key to strong profitability and visibility for products sold by online retailers around the world on all distribution channels: marketplaces, comparison shopping engines, affiliate platforms and display/retargeting platforms. Since 2009, Lengow has integrated more than 1,600 partners into its solution to provide a powerful platform to its 4,600 retailers and brands in 42 countries around the world.

Major features in Lengow include:

- Easily import your product data from WordPress / WooCommerce
- Use Lengow to target and exclude the right products for the right channels and tools (marketplaces, price comparison engines, product ads, retargeting, affiliation) and automate the process of product diffusion.
- Manipulate your feeds (categories, titles, descriptions, rules…) - no need for technical knowledge.
- Lengow takes care of the centralisation of orders received from marketplaces and synchronises inventory data with your WordPress backoffice. Track your demands accurately and set inventory rules to avoid running out of stock.
- Monitor and control your ecommerce activity using detailed, yet easy to understand graphs and statistics. Track clicks, sales, CTR, ROI and tweak your campaigns with automatic rules according to your cost of sales / profitability targets.
- Thanks to our API, Lengow is compatible with many applications so you can access the functionality of all your ecommerce tools on a single platform. There are already more than 40 available applications: marketing platform, translation, customer review, email, merchandise, price watch, web-to-store, product recommendation and many more

The Lengow plugin for WooCommerce is free to download and it enables you to export your product catalogs and manage your orders. It is compatible only with the new version of our platform.
A Lengow account is created during the extension installation and you will have free access to our platform for 15 days. To benefit from all the functionalities of Lengow, this requires you to pay for an account on the Lengow platform.

== Frequently Asked Questions ==

= Where can I find Lengow documentation and user guides? =

For help setting up and configuring Lengow plugin please refer to our [user guide](https://help.lengow.com/hc/en-us/articles/360011968912)

= Where can I get support? =

To make a support request to Lengow, use [our helpdesk](https://help.lengow.com/hc/en-us/requests/new).

== Installation ==

1. Make sure that WooCommerce plugin is installed and activated. If it is not installed, install [WooCommerce](https://wordpress.org/plugins/woocommerce/) first because it is necessary for this plugin
2. Upload the plugin files to the `/wp-content/plugins` directory, or install the plugin through the WordPress plugins screen directly
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Log in with your Lengow credentials and configure the plugin

== Changelog ==

= 2.5.2 - xxxx-xx-xx =
* Feature: Removal of compatibility with WooCommerce versions lower than 4.0
* Feature: Integration of an internal toolbox with all Lengow information for support

= 2.5.1 - 2021-12-20 =
* Bugfix: Loading of all dependencies in the webservices

= 2.5.0 - 2021-10-26 =
* Feature: Integration of order synchronization in the toolbox webservice
* Feature: Retrieving the status of an order in the toolbox webservice

= 2.4.1 - 2021-07-19 =
* Feature: Outsourcing of the toolbox via webservice
* Feature: Setting up a modal for the plugin update
* Bugfix: [export] Added security on product tax recovery
* Bugfix: [export] Fix fields and attribute retrieval for product variation
* Bugfix: [export] Prevent wrongly formatted post_meta from being exported

= 2.4.0 - 2021-03-24 =
* Feature: Integration of the new connection process
* Bugfix: Save customer_vat_number value in lengow order table
* Bugfix: remove NULL value from export images array

= 2.3.3 - 2020-10-26 =
* Feature: Adding new links to the Lengow Help Center and Support
* Feature: B2B orders can now be imported without taxes (optional)
* Bugfix: [import] Handling of b2b order shipping tax
* Bugfix: New security for WordPress version 5.5.x
* Bugfix: [export] Checks if the child description is empty or null
* Bugfix: Always load iframe over https

= 2.3.2 - 2020-06-09 =
* Feature: [import] Addition of order types in the order management screen
* Feature: [import] Addition on the currency conversion in imported order
* Feature: [import] Integration of the region code in the delivery and billing addresses
* Bugfix: Update of the access token when recovering an http 401 code

= 2.3.1 - 2020-03-13 =
* Bugfix: Addition of the http 201 code in the success codes

= 2.3.0 - 2020-03-04 =
* Feature: Refactoring and optimization of the connector class
* Feature: [import] Protection of the import of anonymized orders
* Feature: [import] Protection of the import of orders older than 3 months
* Feature: Optimization of API calls for synchronisation of orders and actions
* Feature: Display of an alert when the plugin is no longer up to date
* Feature: Renaming from Preprod Mode to Debug Mode
* Bugfix: Refactoring and optimization of dates with the correct locale
* Bugfix: [import] Enhanced security for orders that change their marketplace name

= 2.2.1 - 2020-01-06 =
* Feature: Adding compatibility with php 7.3
* Feature: Update of the GNU General Public License in version 3
* Feature: Add readme.txt file for WordPress validation
* Bugfix: Change Plugin URI for WordPress validation

= 2.2.0 - 2019-12-05 =
* Feature: [import] Added import order management
* Feature: [action]  Manage ship and cancel actions on order
* Feature: Implementation of the new Lengow order management screen
* Feature: [action] Automatic verification of actions sent to the marketplace
* Feature: [action] Automatic sending of action if the first shipment was a failure
* Feature: Setting up the simple Lengow tracker
* Feature: Sending a report email with order import and action upload errors
* Feature: Viewing Lengow Information on the WooCommerce order
* Feature: [action] Adding a Lengow send block on the WooCommerce order to populate carrier data
* Feature: Adding a order resynchronisation button with Lengow
* Feature: [import] Adding a reimport command button and Lengow technical error status

= 2.1.1 - 2019-07-24 =
* Feature: [import] Optimization of the order recovery system
* Feature: [import] Setting up a cache for synchronizing catalogs ids
* Feature: [export] Recovering parent images for the variation
* Bugfix: [export] Recovering all distinct attributes in SQL query

= 2.1.0 - 2019-06-16 =
* Feature: Registering marketplace data in a json file
* Feature: Optimization of API calls between PrestaShop and Lengow
* Bugfix: [export] Caching legacy export data
* Bugfix: [export] Improved deletion of duplicate headers
* Bugfix: [export] Correction of the counter on the total number of products

= 2.0.2 - 2019-01-24 =
* Feature: Adding links to the new Lengow help center
* Feature: [Export] Adding shipping_class field to the export feed
* Bugfix: [Export] Management of duplicate fields
* Bugfix: [Export] Checking for the description if a parent product is null
* Bugfix: Instantiation of the wpdb component for the update process
* Bugfix: [Export] Exclusion of variations without parent from export feed
* Bugfix: [import] Improved security to avoid duplicate synchronization

= 2.0.1 - 2018-07-02 =
* Feature: Complete refactoring of the installation and update processes
* Feature: Protocol change to https for API calls
* Bugfix: Add indexes to database to speed up the display of product grid
* Bugfix: Deleting the indefinite index user_id in the connector

= 2.0.0 - 2017-12-12 =
* Feature: Full rewrite for the new platform Lengow
* Feature: Add new lengow Dashboard
* Feature: Add new product selection
* Feature: Add new stock synchronisation page
* Feature: Add new help page
* Feature: Add new Toolbox page with all Lengow informations
* Feature: Add new legals page
* Feature: New lengow settings with cleaning old options
* Feature: Creating new accounts directly from the module

= 1.0.6 - 2017-10-15 =
* Feature: Get description of product & variation for variation
* Bugfix: Transforms array to json element for post-meta data
* Bugfix: Added a new check for the server's addr constant

= 1.0.5 - 2017-05-02 =
* Feature: Add compatibility for WooCommerce 3.0
* Feature: Add retro-compatibility for WooCommerce 2.0
* Feature: Add new export parameters (limit, offset and product_ids)
* Feature: Get image for product variation in export
* Feature: Add all post meta field in export
* Feature: Clean import process with new logs and verifications
* Feature: Delete Lengow dashboard widget
* Bugfix: Fix bug with Lengow simple tracker and tag capsule
* Bugfix: Fix bug for product category in export
* Bugfix: Fix bug for product sku in export

= 1.0.4 - 2015-12-23 =
* Feature: Add logs and export directory

= 1.0.3 - 2015-11-24 =
* Feature: Fix compatibility with 2.4
* Bugfix: Fix bugs duplicate product

= 1.0.2 - 2015-12-23 =
* Feature: Add TagCapsule and SimpleTag support
* Bugfix: Fix compatibility with 2.2

= 1.0.1 - 2015-01-26 =
* Feature: Add internal IP
* Bugfix: Fix warning in admin
* Bugfix: Fix wrong product id in export

= 1.0.0 - 2014-04-29 =
* Feature: Export product
* Feature: Manage feeds
* Feature: Order import
* Feature: Add compatibility with WP-CLI

== Upgrade Notice ==

= 2.0 =
2.0 is a major update. Be careful, this version only concerns customers running on the new version of Lengow.
