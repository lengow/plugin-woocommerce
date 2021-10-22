/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function ($) {
    $(document).ready(function () {
        /**
         * Refresh total product/product exported.
         *
         * @param data Number of products exported and total products
         */
        function reloadTotal(data) {
            $(".js-lengow_exported").html(data['total_export_product']);
            $(".js-lengow_total").html(data['total_product']);
        }

        /**
         * Ajax for switch options (product variations / out of stock / specific product).
         */
        $('.js-lengow_switch_option').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action'),
                state = $(this).prop('checked'),
                data = {
                    // action call php function
                    action: 'post_process_products',
                    state: state ? 1 : 0,
                    do_action: action
                };

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (content) {
                    var selector = $('.js-lengow_feed_block_footer_content');

                    reloadTotal(content);

                    if (content['state'] != null) {
                        if (content['state'] === true) {
                            selector.slideDown(150);
                        } else {
                            selector.slideUp(150);
                        }
                    }
                }
            });
        });

        /**
         * Checkbox to include a product in export (lengow column).
         */
        $('.js-lengow_switch_product').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action'),
                state = $(this).prop('checked'),
                id_product = $(this).attr('data-id_product'),
                data = {
                    action: 'post_process_products',
                    state: state ? 1 : 0,
                    do_action: action,
                    id_product: id_product
                };
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: data,
                success: function (content) {
                    reloadTotal(content);
                }
            });
        });

        /**
         * Check select all checkbox to display lengow toolbar and lengow select all products.
         */
        $('#lengow_product_grid #cb-select-all-1 ,#lengow_product_grid #cb-select-all-2').on('click', function () {
            if ($(this).prop('checked')) {
                $('.js-lengow_toolbar, .js-lengow_toolbar a, .js-lengow_select_all').show();
            } else {
                $('.js-lengow_toolbar, .js-lengow_toolbar a, .js-lengow_select_all').hide();
                $('#js-select_all_shop').attr('checked', false);
            }
        });

        /**
         * Mass action to export products or not.
         */
        $('.js-lengow_add_to_export , .js-lengow_remove_from_export').on('click', function () {
            var message = $(this).attr('data-message'),
                do_action = $(this).attr('data-action'),
                export_action = $(this).attr('data-export-action'),
                check = $('#js-select_all_shop').prop('checked'),
                products = [];

            // find all checked products
            $('#js-lengow_product_checkbox:checked').each(function () {
                products.push($(this).attr('value'));
            });

            var data = {
                action: 'post_process_products',
                do_action: do_action,
                export_action: export_action,
                select_all: check,
                product: products
            };
            if (!check || (check && confirm(message))) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (content) {
                        var data = JSON.parse(content);
                        if (data.message) {
                            alert(data.message);
                        } else {
                            $.each(data.product_id, function (idx, p_id) {
                                if (export_action === 'add_to_export') {
                                    $("#js-lengow_product_" + p_id + "").parents(".lgw-switch").addClass("checked");
                                    $('.js-lengow_switch_product').prop("checked", true);
                                } else {
                                    $("#js-lengow_product_" + p_id + "").parents(".lgw-switch").removeClass("checked");
                                    $('.js-lengow_switch_product').prop("checked", false);
                                }
                            });
                            reloadTotal(data);
                        }
                    }
                });
            }
            return false;
        });

        /**
         * Check for display mass actions.
         */
        $('.js-lengow_selection').on('click', function () {
            if ($(this).prop('checked') == false) {
                $('#js-select_all_shop').attr('checked', false);
            }
            var findProductSelected = false;
            $('.js-lengow_selection:checked').each(function () {
                findProductSelected = true;
                $('.js-lengow_toolbar, .js-lengow_toolbar a, .js-lengow_select_all').show();
            });
            if (!findProductSelected) {
                $('.js-lengow_toolbar, .js-lengow_toolbar a, .js-lengow_select_all').hide();
            }
        });

        /**
         * Check all checkbox when check lengow select all table.
         */
        $('#js-select_all_shop').on('click', function () {
            $('.js-lengow_selection').attr('checked', true);
        });

    });
})(jQuery);