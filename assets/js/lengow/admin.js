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
        /**
         * Switch toggle.
         */
        jQuery('body').on('change', '.lgw-switch', function (event) {
            var check = $(this);
            var checked = check.find('input').prop('checked');
            check.toggleClass('checked');
        });

        init_tooltip();

        var debug_exist = $('#lgw-debug').length;
        if (debug_exist > 0) {
            $("#lengow_feed_wrapper").addClass('activeDebug');
            $("#lengow_order_wrapper").addClass('activeDebug');
            $("#lengow_form_order_setting").addClass('activeDebug');
            $("#lengow_mainsettings_wrapper").addClass('activeDebug');
            $(".lengow_help_wrapper").addClass('activeDebug');
        }

        // open upgrade plugin modal
        $('.js-upgrade-plugin-modal-open').on('click', function() {
            var modalBox = $('#upgrade-plugin');
            modalBox.show();
            setTimeout(function() {
                modalBox.addClass('is-open');
            }, 250);
        });

        // close upgrade plugin modal
        function closeUpgradePluginModal() {
            var modalBox = $('#upgrade-plugin.is-open');
            modalBox.removeClass('is-open');
            setTimeout(function() {
                modalBox.hide();
            }, 250);
        }
        $('.js-upgrade-plugin-modal-close').on('click', closeUpgradePluginModal);

        // when the user clicks anywhere outside of the modal, close it
        $(document).on('click', function(event) {
            if (!$(event.target).closest('.lgw-modalbox-content').length) {
                closeUpgradePluginModal();
            }
        });

        // hide the display of the modal for 7 days
        $('.js-upgrade-plugin-modal-remind-me').on('click', function() {
            var data = {
                action: 'post_process_dashboard',
                do_action: 'remind_me_later',
            };
            $.post(ajaxurl, data, function() {
                var modalBox = $('#upgrade-plugin.is-open');
                modalBox.removeClass('is-open');
                setTimeout(function() {
                    $('.js-upgrade-plugin-modal-remind-me').hide();
                    modalBox.hide();
                }, 250);
            });
        });
    });
})(jQuery);

function init_tooltip() {
    jQuery('.lengow_link_tooltip').tooltip({
        'template': '<div class="lengow_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
}
