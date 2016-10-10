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

        $('#doaction').on('click', function () {
            $.ajax({
                url: ajaxurl,
                type: "POST",
                dataType: "JSON",
                complete: function() {
                    $(".products-exported").load(location.href + ' #js-lengow_exported');
                }
            });
        });

        $('.js-lengow_switch_product').on('change', function (e) {
            e.preventDefault();
            var action = $(this).attr('data-action'),
            id_product = $(this).attr('data-id_product'),
            state = $(this).prop('checked'),
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
                success: function(content) {
                    reloadTotal(content);
                }
            });
        });

        $('#cb-select-all-1').on('click', function () {
            if ($(this).prop('checked')) {
                $('.lengow_toolbar a').show();
                $('.js-lengow_select_all').show();
            } else {
                $('.lengow_toolbar a').hide();
                $('.js-lengow_select_all').hide();
            }
        });

        $('.lengow_add_to_export').on('click', function () {
            var message = $(this).attr('data-message'),
                check = $('#select_all_shop').prop('checked'),
                products = [];
            //find all checked products
            $('#js-lengow_product_checkbox:checked').each(function() {
                products.push($(this).attr('value'));
            });

            var data = {
                action: 'post_process',
                do_action: 'add_to_export',
                select_all: check,
                product: products
            };
            if (!check || (check && confirm(message))) {
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: data,
                    success: function(content) {
                        var data = JSON.parse(content);
                        if (data.message) {
                            alert(data.message);
                        } else {
                            $.each(data.product_id, function (idx, p_id) {
                                $("#lengow_product_" + p_id + "").parents(".lgw-switch").addClass("checked");
                            });
                            reloadTotal(data);
                        }
                    }
                });
            }
            return false;
        });
    });
})(jQuery);