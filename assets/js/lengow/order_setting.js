/**
 * Copyright 2019 Lengow SAS.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 *
 * @author    Team Connector <team-connector@lengow.com>
 * @copyright 2019 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

(function ($) {
    $(document).ready(function () {

        // submit form
        $( ".lengow_form" ).submit(function( event ) {
            event.preventDefault();
            var form = this;

            $('.lengow_form button[type="submit"]').addClass('loading');

            setTimeout(function () {
                $('.lengow_form button[type="submit"]').removeClass('loading');
                $('.lengow_form button[type="submit"]').addClass('success');
                form.submit();
            }, 1000);
        });

        // enable stock mp
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
