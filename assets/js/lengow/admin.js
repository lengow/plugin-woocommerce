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

        var preprod_exist = $('#lgw-preprod').length;
        if (preprod_exist > 0) {
            $("#lengow_feed_wrapper").addClass('activePreprod');
            $("#lengow_order_wrapper").addClass('activePreprod');
            $("#lengow_form_order_setting").addClass('activePreprod');
            $("#lengow_mainsettings_wrapper").addClass('activePreprod');
            $(".lengow_help_wrapper").addClass('activePreprod');
        }
    });
})(jQuery);

function init_tooltip() {
    jQuery('.lengow_link_tooltip').tooltip({
        'template': '<div class="lengow_tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
    });
}
