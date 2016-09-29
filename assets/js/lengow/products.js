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

        function checkShop() {
            var status = $('.lengow_check_shop');
            var data = {
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
                    selector.html("");

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

        $('.js-lengow_switch_option').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action');
            var state = $(this).prop('checked');
            var data = {
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

        $('.lgw-container').on('change', '.lengow_switch_product', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action');
            var id_product = $(this).attr('data-id_product');
            var state = $(this).prop('checked');
            var data = {
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
                success: function(content) {
                    reloadTotal(content);
                }
            });
        });


        // $('#lengow_feed_wrapper').on('click', '.lengow_select_all', function () {
        //     var id_shop = $(this).attr('id').split('_')[2];
        //     if ($(this).prop('checked')) {
        //         $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', true);
        //         $('#table_shop_' + id_shop + ' tbody tr').addClass('select');
        //         $('#block_' + id_shop + ' .lengow_toolbar a').show();
        //         $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop').show();
        //     } else {
        //         $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', false);
        //         $('#table_shop_' + id_shop + ' tbody tr').removeClass('select');
        //         $('#block_' + id_shop + ' .lengow_toolbar a').hide();
        //         $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop').hide();
        //     }
        // });

        // $('#lengow_feed_wrapper').on('click', '.lengow_selection', function () {
        //     var id_shop = $(this).parents('table').attr('id').split('_')[2];
        //     $('#block_' + id_shop + ' .lengow_toolbar a').show();
        //
        //     if ($(this).prop('checked')) {
        //         $(this).parents('tr').addClass('select');
        //     } else {
        //         $('#block_' + id_shop + ' .lengow_toolbar .lengow_select_all_shop input').prop('checked', false);
        //         $(this).parents('tr').removeClass('select');
        //
        //     }
        //     var findProductSelected = false;
        //     $(this).parents('table').find('.lengow_selection').each(function (index) {
        //         if ($(this).prop('checked')) {
        //             findProductSelected = true;
        //         }
        //     });
        //     if (!findProductSelected) {
        //         $('#block_' + id_shop + ' .lengow_toolbar a').hide();
        //     }
        // });

        // $('#lengow_feed_wrapper').on('click', '.lengow_add_to_export', function () {
        //     var href = $(this).attr('data-href');
        //     var id_shop = $(this).attr('data-id_shop');
        //     var message = $(this).attr('data-message');
        //     var form = $('#form_table_shop_' + id_shop).serialize();
        //     var url = href + "&" + form;
        //     var check = $('#select_all_shop_' + id_shop).prop('checked');
        //     var data = {
        //         action: 'add_to_export',
        //         id_shop: id_shop,
        //         select_all: check
        //     };
        //     if (!check || (check && confirm(message))) {
        //         $.getJSON(url, data, function(content) {
        //             if (content['message']) {
        //                 alert(content['message']);
        //             } else {
        //                 $.each(content['product_id'], function(idx, p_id) {
        //                     lengow_jquery("#shop_" + id_shop + "_" + p_id + " .lgw-switch").addClass("checked");
        //                 });
        //                 reloadTotal(content, id_shop);
        //             }
        //         });
        //     }
        //     return false;
        // });

        // $('#lengow_feed_wrapper').on('click', '.lengow_remove_from_export', function () {
        //     var href = $(this).attr('data-href');
        //     var id_shop = $(this).attr('data-id_shop');
        //     var message = $(this).attr('data-message');
        //     var form = $('#form_table_shop_' + id_shop).serialize();
        //     var url = href + '&' + form;
        //     var check = $('#select_all_shop_' + id_shop).prop('checked');
        //     var data = {
        //         action: 'remove_from_export',
        //         id_shop: id_shop,
        //         select_all: check
        //     };
        //     if (!check || (check && confirm(message))) {
        //         $.getJSON(url, data, function(content) {
        //             if (content['message']) {
        //                 alert(content['message']);
        //             } else {
        //                 $.each(content['product_id'], function(idx, p_id) {
        //                     lengow_jquery("#shop_" + id_shop + "_" + p_id + " .lgw-switch").removeClass("checked");
        //                 });
        //                 reloadTotal(content, id_shop);
        //             }
        //         });
        //     }
        //     return false;
        // });

        // $('#lengow_feed_wrapper').on('click', '.lengow_select_all_shop input', function () {
        //     var id_shop = $('.lengow_select_all').attr('id').split('_')[2];
        //     if ($(this).prop('checked')) {
        //         $('#table_shop_' + id_shop + ' tbody .lengow_selection').prop('checked', true);
        //         $('.lengow_selection').parents('tr').addClass('select');
        //     }
        // });

        // $('.lengow_table').on('click', '.table_row td:not(.no-link)', function(){
        //     var url = $(this).closest('.table_row').find('.feed_name a').attr('href');
        //     window.open(url, '_blank');
        //     return false;
        // });
    });
})(jQuery);