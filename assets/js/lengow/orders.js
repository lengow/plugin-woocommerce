/**
 * Copyright 2019 Lengow SAS.
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
 * @copyright 2019 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
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
        hide_select_all();

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
         * Check select all checkbox to display lengow toolbar and lengow select all orders.
         */
        $('#lengow_order_grid #cb-select-all-1 , #lengow_order_grid #cb-select-all-2').on('click', function () {
            if ($(this).prop('checked')) {
                $('.js-lengow_toolbar, .js-lengow_toolbar a').show();
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

        /**
         * Hide select all checkbox when there is no action on the order.
         */
        function hide_select_all() {
            var order_action_exist = false;
            $('.js-lengow_selection_order').each(function () {
                order_action_exist = true;
            });
            if (!order_action_exist) {
                $('#lengow_order_grid #cb-select-all-1,  #lengow_order_grid #cb-select-all-2').hide();
            }
        }

        /**
         * Display informations for header.
         */
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