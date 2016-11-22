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
        var sync_link = $('#lengow_sync_link').val();
        var sync_iframe = document.getElementById('lengow_iframe');
        if (sync_iframe) {
            sync_iframe.onload = function () {
                $.ajax({
                    url: ajaxurl,
                    type: "POST",
                    data: {do_action: 'get_sync_data', action: 'post_process_dashboard'},
                    dataType: 'json',
                    success: function (data) {
                        var targetFrame = document.getElementById("lengow_iframe").contentWindow;
                        targetFrame.postMessage(data, '*');
                    }
                });
            };
            if (sync_link) {
                // sync_iframe.src = 'http://cms.lengow.io/sync/';
                // sync_iframe.src = 'http://cms.lengow.net/sync/';
                sync_iframe.src = 'http://cms.lengow.rec/sync/';
                // sync_iframe.src = 'http://cms.lengow.dev/sync/';
            } else {
                // sync_iframe.src = 'http://cms.lengow.io/';
                // sync_iframe.src = 'http://cms.lengow.net/';
                sync_iframe.src = 'http://cms.lengow.rec/';
                // sync_iframe.src = 'http://cms.lengow.dev/';
            }
            $('#frame_loader').hide();
            $('#lengow_iframe').show();
        }

        window.addEventListener('message', receiveMessage, false);

        function receiveMessage(event) {
            switch (event.data.function) {
                case 'sync':
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {do_action: 'sync', data: event.data.parameters, action: 'post_process_dashboard'},
                        dataType: 'script'
                    });
                    break;
                case 'sync_and_reload':
                    $.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: {do_action: 'sync', data: event.data.parameters, action: 'post_process_dashboard'},
                        dataType: 'script',
                        success: function () {
                            location.reload();
                        }
                    });
                    break;
                case 'reload':
                    location.reload();
                    break;
            }
        }
    });
})(jQuery);