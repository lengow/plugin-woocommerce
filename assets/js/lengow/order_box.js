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
        $(document).on('click', '#lgw-order-resend', function () {
            if (confirm($(this).attr('data-message'))) {
                var data = {
                    action: 'post_process_order_box',
                    do_action: $(this).attr('data-action'),
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function (content) {
                        console.log(content);
                        var data = JSON.parse(content);
                        console.log(data);
                        alert('c\'est good');
                    }
                });
            }
        });
    });
})(jQuery);
