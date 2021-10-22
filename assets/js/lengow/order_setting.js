/**
 * Copyright 2019 Lengow SAS.
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
 * @copyright 2019 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function ($) {
    $(document).ready(function () {
        /**
         * Submit form.
         */
        $(".lengow_form").submit(function (event) {
            event.preventDefault();
            var form = this;

            $('.lengow_form button[type="submit"]').addClass('loading');

            setTimeout(function () {
                $('.lengow_form button[type="submit"]').removeClass('loading');
                $('.lengow_form button[type="submit"]').addClass('success');
                form.submit();
            }, 1000);
        });

        /**
         * Enable stock mp.
         */
        $("input[name='lengow_import_ship_mp_enabled']").on('change', function () {
            displayStockMP();
        });

        function displayStockMP() {
            var selector = $('.lengow_import_stock_ship_mp');
            if ($("input[name='lengow_import_ship_mp_enabled'][type='checkbox']").prop('checked')) {
                selector.slideDown(150);
                var divLegend = selector.next('.legend');
                divLegend.css('display', 'block');
                divLegend.show();
            } else {
                selector.slideUp(150);
                selector.next('.legend').hide();
            }
        }
    });

})(jQuery);
