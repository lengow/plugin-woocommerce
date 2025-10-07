# Changelog

## [2.8.0](https://github.com/lengow/plugin-woocommerce/compare/v2.7.2...v2.8.0) (2025-10-07)


### Features

* **fast-orders:** add minutes param ([#33](https://github.com/lengow/plugin-woocommerce/issues/33)) ([4b0ced0](https://github.com/lengow/plugin-woocommerce/commit/4b0ced07f5d456d5deb69ecdeaa55f8ab05feb20))

## [2.7.2](https://github.com/lengow/plugin-woocommerce/compare/v2.7.1...v2.7.2) (2025-10-03)


### Bug Fixes

* **import:** add mysql lock, add transaction in order creation ([#34](https://github.com/lengow/plugin-woocommerce/issues/34)) ([bd7ac36](https://github.com/lengow/plugin-woocommerce/commit/bd7ac369c682478c66685fdb9bdeaf3dc6cb70f1))

## [2.7.1](https://github.com/lengow/plugin-woocommerce/compare/v2.7.0...v2.7.1) (2025-08-27)


### Bug Fixes

* **orders:** [PCMT-776] Prevent order resume email from being sent wh… ([#31](https://github.com/lengow/plugin-woocommerce/issues/31)) ([de35533](https://github.com/lengow/plugin-woocommerce/commit/de35533577dee99a3ffca2221bd9288c33da3fb5))

## [2.7.0](https://github.com/lengow/plugin-woocommerce/compare/v2.6.6...v2.7.0) (2025-03-03)


### Features

* **carrier-matching:** import order with a mapped shipping method ([#26](https://github.com/lengow/plugin-woocommerce/issues/26)) ([dd2476d](https://github.com/lengow/plugin-woocommerce/commit/dd2476dc1504cd82e80bec194cf34933975ca419))
* **carrier-matching:** re-organize admin settings ([#30](https://github.com/lengow/plugin-woocommerce/issues/30)) ([45a0c9d](https://github.com/lengow/plugin-woocommerce/commit/45a0c9d2f3ad087c206d910b4b4ab4a9b515344d))


### Bug Fixes

* **eport:** [PST-22214] woo commerce catalogue is blocked because of error in the export file ([#27](https://github.com/lengow/plugin-woocommerce/issues/27)) ([0b26377](https://github.com/lengow/plugin-woocommerce/commit/0b263772405013bb95cab643635a168b5df252ef))
* **order:** [TSB-1276] fix incorrect technical error order state translation ([#29](https://github.com/lengow/plugin-woocommerce/issues/29)) ([d84e07a](https://github.com/lengow/plugin-woocommerce/commit/d84e07a6483de2a19f54fcef8dbb23a3b3fc7b05))
* **settings:** [PST-21910] change preprod to sandbox word ([#25](https://github.com/lengow/plugin-woocommerce/issues/25)) ([0b46855](https://github.com/lengow/plugin-woocommerce/commit/0b46855ab9583bcccdee496530fb8431c2971fe9))

## [2.6.6](https://github.com/lengow/plugin-woocommerce/compare/v2.6.5...v2.6.6) (2024-10-14)


### Bug Fixes

* **connector:** [ECP-113] rate limit request for woo commerce ([#23](https://github.com/lengow/plugin-woocommerce/issues/23)) ([67de112](https://github.com/lengow/plugin-woocommerce/commit/67de1125d398270d8776ec8c3636c4ed7c5ee76a))

## [2.6.5](https://github.com/lengow/plugin-woocommerce/compare/v2.6.4...v2.6.5) (2024-09-25)


### Bug Fixes

* **cicd:** Add a job to generate plugin checksums ([#13](https://github.com/lengow/plugin-woocommerce/issues/13)) ([daf2fce](https://github.com/lengow/plugin-woocommerce/commit/daf2fcea16de73d2a0d092ef37c6380ca1c6066f))
* **cicd:** Correct the checksums generation ([#19](https://github.com/lengow/plugin-woocommerce/issues/19)) ([f07bac9](https://github.com/lengow/plugin-woocommerce/commit/f07bac91467a572a2e2fbe90fe18b4ab54facb34))
* **lengow:** [ECP-109] Change lengow's logo for the new logo ([#10](https://github.com/lengow/plugin-woocommerce/issues/10)) ([b26c799](https://github.com/lengow/plugin-woocommerce/commit/b26c7993a92e030e2421068f83d86ee769978ed3))


### Miscellaneous

* **ci-cd:** automatically update release-please version in files ([#12](https://github.com/lengow/plugin-woocommerce/issues/12)) ([5fa8a77](https://github.com/lengow/plugin-woocommerce/commit/5fa8a770c7dec41136cd6d0b0f64799294d4a606))

## [2.6.4](https://github.com/lengow/plugin-woocommerce/compare/v2.6.3...v2.6.4) (2024-08-22)


### Features

* **cicd:** Add a besic CI to the project ([#6](https://github.com/lengow/plugin-woocommerce/issues/6)) ([a1a6cc8](https://github.com/lengow/plugin-woocommerce/commit/a1a6cc8669074e2d2d235f698f6323ffee864bba))


### Miscellaneous

* add plugin version in composer.json, remove unusued readme.txt ([#9](https://github.com/lengow/plugin-woocommerce/issues/9)) ([a908fe2](https://github.com/lengow/plugin-woocommerce/commit/a908fe2f8aaae5e0d605ed4f013ba005e5370621))
* **clean:** Remove obsolete files ([a1a6cc8](https://github.com/lengow/plugin-woocommerce/commit/a1a6cc8669074e2d2d235f698f6323ffee864bba))
* Correct Changelog ([#8](https://github.com/lengow/plugin-woocommerce/issues/8)) ([95b257b](https://github.com/lengow/plugin-woocommerce/commit/95b257b7661d8ce2e552c788818208f1e103b97c))
* **docs:** Precise Changelog file type ([a1a6cc8](https://github.com/lengow/plugin-woocommerce/commit/a1a6cc8669074e2d2d235f698f6323ffee864bba))

## Changelog

========================================================
Version 2.6.3
========================================================

    - Feature: Change route api plan to restriction
    - Feature: E-mail anonyzation choice md5() or plaintext
    - BugFix: Hydrate address for FBA orders (amazon_us)
    - BugFix: WooCommerce compatibility

========================================================
Version 2.6.2
========================================================

    - BugFix:  Meta box not showing up in order edit page when using the old woocommerce storing engine
    - BugFix:  Fix a crash that could occur in a specific case when exporting a catalogue
    - BugFix:  Fix some characters that shouldn't be escaped in the catalogue export

========================================================
Version 2.6.1
========================================================

    - Feature: Add phone number to the order information in the “Lengow“ tab
    - BugFix:  Add missing shipping phone in the woocommerce order shipping address
    - Feature: Remove deprecated tracking option

========================================================
Version 2.6.0
========================================================

    - Feature: Return tracking carrier during shipment (Otto)
    - Feature: Return tracking number during shipment (Zalando)
    - Feature: Add composer.json to make the plugin composer installable

========================================================
Version 2.5.5
========================================================

    - BugFix:  PHP Fatal error occurring when the plugin checks if there is a new version (and there is one)
    - BugFix:  Make the plugin compliant with the WordPress coding standard

========================================================
Version 2.5.4
========================================================

    - BugFix:  Optimise the catalog export time
    - BufFix:  Update some help center links
    - BugFix:  Fix broken footer settings url

========================================================
Version 2.5.3
========================================================

    - Feature:  Partially refunded status
    - Feature:  Switch env preprod/prod from config
    - Feature:  Toolbox file details
    - Feature:  Log import params initialized
    - Feature:  Anonymize customers emails config
    - Bugfix:   Vat number sync update
    - Bugfix:   Order duplicate when delivery_address_id changes
    - Bugfix:   php8.1 address strings data not be null
    - BugFix:   Order not post but WC_Order

========================================================
Version 2.5.2
========================================================

    - Feature: Removal of compatibility with WooCommerce versions lower than 4.0
    - Feature: Integration of an internal toolbox with all Lengow information for support
    - Feature: Adding the PHP version in the toolbox
    - Feature: Modification of the fallback urls of the Lengow Help Center
    - Feature: Adding extra field update date in external toolbox

========================================================
Version 2.5.1
========================================================

    - Bugfix: Loading of all dependencies in the webservices

========================================================
Version 2.5.0
========================================================

    - Feature: Integration of order synchronization in the toolbox webservice
    - Feature: Retrieving the status of an order in the toolbox webservice

========================================================
Version 2.4.1
========================================================

    - Feature: Outsourcing of the toolbox via webservice
    - Feature: Setting up a modal for the plugin update
    - Bugfix: [export] Added security on product tax recovery
    - Bugfix: [export] Fix fields and attribute retrieval for product variation
    - Bugfix: [export] Prevent wrongly formatted post_meta from being exported

========================================================
Version 2.4.0
========================================================

    - Feature: Integration of the new connection process
    - Bugfix: Save customer_vat_number value in lengow order table
    - Bugfix: remove NULL value from export images array

========================================================
Version 2.3.3
========================================================

    - Feature: Adding new links to the Lengow Help Center and Support
    - Feature: B2B orders can now be imported without taxes (optional)
    - Bugfix: [import] Handling of b2b order shipping tax
    - Bugfix: New security for WordPress version 5.5.x
    - Bugfix: [export] Checks if the child description is empty or null
    - Bugfix: Always load iframe over https

========================================================
Version 2.3.2
========================================================

    - Feature: [import] Addition of order types in the order management screen
    - Feature: [import] Addition on the currency conversion in imported order
    - Feature: [import] Integration of the region code in the delivery and billing addresses
    - Bugfix: Update of the access token when recovering an http 401 code

========================================================
Version 2.3.1
========================================================

    - Bugfix: Addition of the http 201 code in the success codes

========================================================
Version 2.3.0
========================================================

    - Feature: Refactoring and optimization of the connector class
    - Feature: [import] Protection of the import of anonymized orders
    - Feature: [import] Protection of the import of orders older than 3 months
    - Feature: Optimization of API calls for synchronisation of orders and actions
    - Feature: Display of an alert when the plugin is no longer up to date
    - Feature: Renaming from Preprod Mode to Debug Mode
    - Bugfix: Refactoring and optimization of dates with the correct locale
    - Bugfix: [import] Enhanced security for orders that change their marketplace name

========================================================
Version 2.2.1
========================================================

    - Feature: Adding compatibility with php 7.3
    - Feature: Update of the GNU General Public License in version 3
    - Feature: Add readme.txt file for WordPress validation
    - Bugfix: Change Plugin URI for WordPress validation

========================================================
Version 2.2.0
========================================================

    - Feature: [import] Added import order management
    - Feature: [action]  Manage ship and cancel actions on order
    - Feature: Implementation of the new Lengow order management screen
    - Feature: [action] Automatic verification of actions sent to the marketplace
    - Feature: [action] Automatic sending of action if the first shipment was a failure
    - Feature: Setting up the simple Lengow tracker
    - Feature: Sending a report email with order import and action upload errors
    - Feature: Viewing Lengow Information on the WooCommerce order
    - Feature: [action] Adding a Lengow send block on the WooCommerce order to populate carrier data
    - Feature: Adding a order resynchronisation button with Lengow
    - Feature: [import] Adding a reimport command button and Lengow technical error status

========================================================
Version 2.1.1
========================================================

    - Feature: [import] Optimization of the order recovery system
    - Feature: [import] Setting up a cache for synchronizing catalogs ids
    - Feature: [export] Recovering parent images for the variation
    - Bugfix: [export] Recovering all distinct attributes in SQL query

========================================================
Version 2.1.0
========================================================

    - Feature: Registering marketplace data in a json file
    - Feature: Optimization of API calls between PrestaShop and Lengow
    - Bugfix: [export] Caching legacy export data
    - Bugfix: [export] Improved deletion of duplicate headers
    - Bugfix: [export] Correction of the counter on the total number of products

========================================================
Version 2.0.2
========================================================

    - Feature: Adding links to the new Lengow help center
    - Feature: [Export] Adding shipping_class field to the export feed
    - Bugfix: [Export] Management of duplicate fields
    - Bugfix: [Export] Checking for the description if a parent product is null
    - Bugfix: Instantiation of the wpdb component for the update process
    - Bugfix: [Export] Exclusion of variations without parent from export feed
    - Bugfix: [import] Improved security to avoid duplicate synchronization

========================================================
Version 2.0.1
========================================================

    - Feature: Complete refactoring of the installation and update processes
    - Feature: Protocol change to https for API calls
    - Bugfix: Add indexes to database to speed up the display of product grid
    - Bugfix: Deleting the indefinite index user_id in the connector

========================================================
Version 2.0.0
========================================================

	- Feature: Full rewrite for the new platform Lengow
	- Feature: Add new lengow Dashboard
	- Feature: Add new product selection
	- Feature: Add new stock synchronisation page
	- Feature: Add new help page
	- Feature: Add new Toolbox page with all Lengow informations
	- Feature: Add new legals page
	- Feature: New lengow settings with cleaning old options
	- Feature: Creating new accounts directly from the module

========================================================
Version 1.0.6
========================================================

    - Feature: Get description of product & variation for variation
    - Bugfix: Transforms array to json element for post-meta data
    - Bugfix: Added a new check for the server's addr constant

========================================================
Version 1.0.5
========================================================

    - Feature: Add compatibility for WooCommerce 3.0
    - Feature: Add retro-compatibility for WooCommerce 2.0
    - Feature: Add new export parameters (limit, offset and product_ids)
    - Feature: Get image for product variation in export
    - Feature: Add all post meta field in export
    - Feature: Clean import process with new logs and verifications
    - Feature: Delete Lengow dashboard widget
    - Bugfix: Fix bug with Lengow simple tracker and tag capsule
    - Bugfix: Fix bug for product category in export
    - Bugfix: Fix bug for product sku in export

========================================================
Version 1.0.4
========================================================

	- Feature: Add logs and export directory

========================================================
Version 1.0.3
========================================================

	- Feature: Fix compatibility with 2.4
	- Bugfix: Fix bugs duplicate product

========================================================
Version 1.0.2
========================================================

	- Feature: Add TagCapsule and SimpleTag support
	- Bugfix: Fix compatibility with 2.2

========================================================
Version 1.0.1
========================================================

	- Feature: Add internal IP
	- Bugfix: Fix warning in admin
	- Bugfix: Fix wrong product id in export

========================================================
Version 1.0.0 Beta
========================================================

	- Feature: Export product
	- Feature: Manage feeds
	- Feature: Order import
	- Feature: Add compatibility with WP-CLI
