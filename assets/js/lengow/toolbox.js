/**
 * Copyright 2022 Lengow SAS.
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2022 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function ($) {
    $(document).ready(function () {

        var globalContentSelector = $('input[name="see_global_content"]');
        var exportContentSelector = $('input[name="see_export_content"]');
        var checksumContentSelector = $('input[name="see_checksum_content"]');

        displayGlobalContent();
        globalContentSelector.change(function () {
            displayGlobalContent();
        });

        displayExportContent();
        exportContentSelector.change(function () {
            displayExportContent();
        });

        displayChecksumContent();
        checksumContentSelector.change(function () {
            displayChecksumContent();
        });

        function displayGlobalContent() {
            var selector = $('.js-lgw-global-content');
            if (globalContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }

        function displayExportContent() {
            var selector = $('.js-lgw-export-content');
            if (exportContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }

        function displayChecksumContent() {
            var selector = $('.js-lgw-checksum-content');
            if (checksumContentSelector.prop('checked')) {
                selector.slideDown(150);
            } else {
                selector.slideUp(150);
            }
        }
    });
})(jQuery);
