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
         * Resend action or resynchronize order.
         */
        $(document).on('click', '#lgw-order-resend, #lgw-order-synchronize', function () {
            if (confirm($(this).attr('data-message'))) {
                var success = $(this).attr('data-success');
                var error = $(this).attr('data-error');
                var data = {
                    action: 'post_process_order_box',
                    do_action: $(this).attr('data-action'),
                    order_lengow_id: $(this).attr('data-id'),
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (content) {
                        var data = JSON.parse(content);
                        data.success ? alert(success) : alert(error);
                    }
                });
            }
        });

        /**
         * Reimport order.
         */
        $(document).on('click', '#lgw-order-reimport', function () {
            if (confirm($(this).attr('data-message'))) {
                var error = $(this).attr('data-error');
                var data = {
                    action: 'post_process_order_box',
                    do_action: $(this).attr('data-action'),
                    order_lengow_id: $(this).attr('data-id'),
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (content) {
                        var data = JSON.parse(content);
                        if (!data.success) {
                            alert(error);
                        } else {
                            document.location.href = data.url.replace('&amp;', '&');
                        }
                    }
                });
            }
        });
    });
})(jQuery);
