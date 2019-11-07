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
        /**
         * Thing to do on load and after reload.
         */
        function load_reload() {
            /**
             * Adapts the width of the status column.
             */
            var column = $('.column-status');
            column.width(column.width() + 50);
            init_tooltip();
            $('.js-lengow_selection_order:checked, #cb-select-all-1').each(function () {
                $(this).attr('checked', false);
            });
            $('.js-lengow_toolbar').hide();
        }

        load_reload();

        /**
         * Ajax to synchronize orders.
         */
        $('#lengow_import_orders').on('click', function () {
            var data = {
                action: 'post_process_orders',
                do_action: 'import_all'
            };

            $('#lengow_charge_import_order').fadeIn(150);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (content) {
                    var data = JSON.parse(content);
                    $("#container_lengow_grid").load(location.href + ' #lengow_order_grid', function () {
                        $('#lengow_charge_import_order').fadeOut(150);
                        reload_informations(data, true);
                        load_reload();
                    });
                },
                error: function (content) {
                    $('#lengow_charge_import_order').fadeOut(150);
                }
            });
        });

        /**
         * Ajax to synchronize one order.
         */
        $(document).on('click', '.lengow_action', function (e) {
            e.preventDefault();
            var do_action = $(this).attr('data-action');
            if (do_action === 'none') {
                return;
            }
            var data = {
                action: 'post_process_orders',
                do_action: do_action,
                order_id: $(this).attr('data-order')
            };
            $('.lengow_tooltip').fadeOut(150);
            $('#lengow_charge_import_order').fadeIn(150);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (content) {
                    var data = JSON.parse(content);
                    $('#container_lengow_grid').load(location.href + ' #lengow_order_grid', function () {
                        reload_informations(data, false);
                        load_reload();
                    });
                },
                error: function (content) {
                    $('#lengow_charge_import_order').fadeOut(150);
                }
            });
        });

        /**
         * Mass action to reimport or resend orders.
         */
        $('.js-lengow_reimport_mass_action , .js-lengow_resend_mass_action').on('click', function () {
            $('#lengow_charge_import_order').fadeIn(150);

            var do_action = $(this).attr('data-action'),
                orders = [];

            // find all checked orders.
            $('#js-lengow_order_checkbox:checked').each(function () {
                orders.push($(this).attr('value'));
            });

            var data = {
                action: 'post_process_orders',
                do_action: do_action,
                orders: orders
            };
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: data,
                success: function (content) {
                    var data = JSON.parse(content);
                    $('#container_lengow_grid').load(location.href + ' #lengow_order_grid', function () {
                        reload_informations(data, true);
                        load_reload();
                    });
                },
                error: function (content) {
                    $('#lengow_charge_import_order').fadeOut(150);
                }
            });
        });

        /**
         * Check select all checkbox to display lengow toolbar and lengow select all products.
         */
        $(document).on('click', '#cb-select-all-1', function () {
            if ($(this).prop('checked')) {
                $('.js-lengow_selection_order:checked').each(function () {
                    $('.js-lengow_toolbar, .js-lengow_toolbar a').show();
                });
            } else {
                $('.js-lengow_toolbar, .js-lengow_toolbar a').hide();
            }
        });

        /**
         * Check for display mass actions.
         */
        $(document).on('click', '.js-lengow_selection_order', function () {
            var find_order_selected = false;
            $('.js-lengow_selection_order:checked').each(function () {
                find_order_selected = true;
                $('.js-lengow_toolbar, .js-lengow_toolbar a').show();
            });

            if (!find_order_selected) {
                $('.js-lengow_toolbar, .js-lengow_toolbar a').hide();
            }
        });

        function reload_informations(informations, show_messages) {
            var lengow_wrapper_message = $('#lengow_wrapper_messages');
            $("#lengow_order_with_error").html(informations.order_with_error);
            $("#lengow_order_to_be_sent").html(informations.order_to_be_sent);
            $('#lengow_last_import_date').html(informations.last_importation);
            $('#lengow_import_orders').html(informations.import_orders);
            lengow_wrapper_message.html(informations.message);
            $('#lengow_charge_import_order').fadeOut(150);
            if (show_messages) {
                lengow_wrapper_message.fadeIn(150);
            } else {
                lengow_wrapper_message.hide();
            }
        }
    });
})(jQuery);