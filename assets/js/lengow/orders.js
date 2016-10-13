/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

(function ($) {
    $(document).ready(function () {

        $('#lengow_import_orders').on('click', function() {
            var button = $(this);
            var data = {do_action: 'import_all'};

            // $('#lengow_charge_import_order').fadeIn(150);
            //
            // $.getJSON(href, data, function(content) {
            //     lengow_jquery("#lengow_wrapper_messages").html(content['message']);
            //     lengow_jquery("#lengow_last_importation").html(content['last_importation']);
            //     lengow_jquery("#lengow_import_orders").html(content['import_orders']);
            //     lengow_jquery("#lengow_order_table_wrapper").html(content['list_order']);
            //
            //     init_tooltip();
            //     reload_table_js();
            //     $('#lengow_charge_import_order').fadeOut(150);
            //     setTimeout(function(){
            //         $('#lengow_wrapper_messages').fadeIn(250);
            //     }, 300);
            // }).fail(function() {
            //     $('#lengow_charge_import_order').fadeOut(150);
            // });
        });

    });

});