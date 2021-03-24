/**
 * Copyright 2016 Lengow SAS.
 *
 * NOTICE OF LICENSE
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * at your option) any later version.
 *
 * It is available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2016 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function ($) {
    $(document).ready(function () {

        $('.js-multiple-select').select2({
            closeOnSelect: false
        });
        $('.js-select').select2();


        /**
         * Submit form.
         */
        $('.lengow_form').submit(function (event) {
            event.preventDefault();
            var form = this;

            $('.lengow_form button[type="submit"]').addClass('loading');

            setTimeout(function () {
                var submitButton = $('.lengow_form button[type="submit"]');
                submitButton.removeClass('loading');
                submitButton.addClass('success');
                form.submit();
            }, 1000);
        });

        /**
         * Enable report mail.
         */
        $('input[name="lengow_report_mail_enabled"]').change(function () {
            displayReportMailAddress();
        });

        function displayReportMailAddress() {
            if ($('input[name="lengow_report_mail_enabled"][type="checkbox"]').prop('checked')) {
                $('#lengow_wrapper_report_mail_address').slideDown(150);
            } else {
                $('#lengow_wrapper_report_mail_address').slideUp(150);
            }
        }

        /**
         * Enable authorized ip.
         */
        $('input[name="lengow_ip_enabled"]').change(function() {
            displayAuthorizedIpMode();
        });

        function displayAuthorizedIpMode() {
            if ($('input[name="lengow_ip_enabled"][type="checkbox"]').prop('checked')) {
                $('#lengow_wrapper_authorized_ip').slideDown(150);
            } else {
                $('#lengow_wrapper_authorized_ip').slideUp(150);
            }
        }

        /**
         * Enable tracking ip.
         */
        $('input[name="lengow_tracking_enabled"]').change(function() {
            displayTrackingIdMode();
        });

        function displayTrackingIdMode() {
            if ($('input[name="lengow_tracking_enabled"][type="checkbox"]').prop('checked')) {
                $('#lengow_wrapper_tracking_id').slideDown(150);
            } else {
                $('#lengow_wrapper_tracking_id').slideUp(150);
            }
        }

        /**
         * Enable shop management.
         */
        $('input[name="lengow_store_enabled"]').change(function() {
            displayShopManagement();
        });

        function displayShopManagement() {
            if ($('input[name="lengow_store_enabled"][type="checkbox"]').prop('checked')) {
                $('#lengow_wrapper_catalog_id').slideDown(150);
            } else {
                $('#lengow_wrapper_catalog_id').slideUp(150);
            }
        }

        /**
         * Enable debug mode.
         */
        $('input[name="lengow_debug_enabled"]').change(function() {
            displayDebugModeMode();
        });

        function displayDebugModeMode() {
            if ($('input[name="lengow_debug_enabled"][type="checkbox"]').prop('checked')) {
                $('#lengow_wrapper_debug').slideDown(150);
            } else {
                $('#lengow_wrapper_debug').slideUp(150);
            }
        }

        /**
         * Download log.
         */
        $('.js-log-select').change(function() {
            if ($('.js-log-select').val() !== null) {
                $('.js-log-btn-download').show();
            }
        });

        $('.js-log-btn-download').click(function() {
            var logSelect = $('.js-log-select');
            if (logSelect.val() !== null) {
                window.location.href = logSelect.val();
            }
        });

    });

})(jQuery);
