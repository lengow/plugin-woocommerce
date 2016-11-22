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
         * Ajax for check synchronization with Lengow Solution
         */
        function checkShop() {
            var status = $('.lengow_check_shop'),
                data = {
                    //action call php function
                    action: 'post_process',
                    do_action: 'check_shop'
                };

            status.html('<i class="fa fa-circle-o-notch fa-spin"></i>');

            $.ajax({
                url: ajaxurl,
                //use 'type' not 'method' for Wordpress compatibility
                type: "POST",
                dataType: "JSON",
                data: data,
                success: function (shop) {
                    var selector = $(".lengow_check_shop");
                    selector.attr("data-original-title", shop['tooltip']);

                    var title = shop['original_title'];

                    if (shop['check_shop'] === true) {
                        selector.removeClass('lengow_check_shop_no_sync').addClass('lengow_check_shop_sync');
                        selector.attr("id", "lengow_shop_sync");
                    } else {
                        selector.attr("id", "lengow_shop_no_sync");
                        $(".lengow_feed_block_header_title").append(shop['header_title']);
                        title = shop['header_title'];
                    }
                    selector.html('<i class="icon icon-circle"></i>');

                    $(".lengow_shop_status_label").html(title);

                    init_tooltip();
                }
            });
        }

        checkShop();

        /**
         * Refresh total product/product exported
         * @param data Number of products exported and total products
         */
        function reloadTotal(data) {
            $(".js-lengow_exported").html(data['total_export_product']);
            $(".js-lengow_total").html(data['total_product']);
        }

        /**
         * Ajax for switch options (product variations / out of stock / specific product)
         */
        $('.js-lengow_switch_option').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action'),
                state = $(this).prop('checked'),
                data = {
                    //action call php function
                    action: 'post_process',
                    state: state ? 1 : 0,
                    do_action: action
                };

            $.ajax({
                url: ajaxurl,
                type: "POST",
                dataType: "JSON",
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
         * Checkbox to include a product in export (lengow column)
         */
        $('.js-lengow_switch_product').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action'),
                state = $(this).prop('checked'),
                id_product = $(this).attr('data-id_product'),
                data = {
                    action: 'post_process',
                    state: state ? 1 : 0,
                    do_action: action,
                    id_product: id_product
                };
            $.ajax({
                url: ajaxurl,
                type: "POST",
                dataType: "JSON",
                data: data,
                success: function (content) {
                    reloadTotal(content);
                }
            });
        });

        /**
         * Check select all checkbox to display lengow toolbar and lengow select all products
         */
        $('#cb-select-all-1 ,#cb-select-all-2').on('click', function () {
            if ($(this).prop('checked')) {
                $('.js-lengow_toolbar a').show();
                $('.js-lengow_select_all').show();
            } else {
                $('.js-lengow_toolbar a').hide();
                $('.js-lengow_select_all').hide();
                $('#js-select_all_shop').attr('checked', false);
            }
        });

        /**
         * Mass action to export products or not
         */
        $('.js-lengow_add_to_export , .js-lengow_remove_from_export').on('click', function () {
            var message = $(this).attr('data-message'),
                do_action = $(this).attr('data-action'),
                export_action = $(this).attr('data-export-action'),
                check = $('#js-select_all_shop').prop('checked'),
                products = [];

            //find all checked products
            $('#js-lengow_product_checkbox:checked').each(function () {
                products.push($(this).attr('value'));
            });

            var data = {
                action: 'post_process',
                do_action: do_action,
                export_action: export_action,
                select_all: check,
                product: products
            };
            if (!check || (check && confirm(message))) {
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: data,
                    success: function (content) {
                        var data = JSON.parse(content);
                        if (data.message) {
                            alert(data.message);
                        } else {
                            $.each(data.product_id, function (idx, p_id) {
                                if (export_action == 'add_to_export') {
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
         * Check for display mass actions
         */
        $('.js-lengow_selection').on('click', function () {

            if ($(this).prop('checked') == false) {
                $('#js-select_all_shop').attr('checked', false);
            }

            var findProductSelected = false;

            $('.js-lengow_selection:checked').each(function () {
                findProductSelected = true;
                $('.js-lengow_toolbar a').show();
            });

            if (!findProductSelected) {
                $('.js-lengow_toolbar a').hide();
            }
        });

        /**
         * Check all checkbox when check lengow select all table
         */
        $('#js-select_all_shop').on('click', function () {
            $('.js-lengow_selection').attr('checked', true);
        });

    });
})(jQuery);