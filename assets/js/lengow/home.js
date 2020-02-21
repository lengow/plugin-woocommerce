/**
 * Copyright 2016 Lengow SAS.
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
 * @copyright 2016 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function ($) {
    $(document).ready(function () {
        var syncLink = $('#lengow_sync_link').val();
        var isoCode = $('#lengow_lang_iso').val();
        var lengowUrl = $('#lengow_url').val();
        var syncIframe = document.getElementById('lengow_iframe');
        if (syncIframe) {
            syncIframe.onload = function () {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {do_action: 'get_sync_data', action: 'post_process_dashboard'},
                    dataType: 'json',
                    success: function (data) {
                        var targetFrame = document.getElementById("lengow_iframe").contentWindow;
                        targetFrame.postMessage(data, '*');
                    }
                });
            };
            syncIframe.src = syncLink ? '//cms.'+lengowUrl+'/sync/' : '//cms.'+lengowUrl+'/';
            syncIframe.src = syncIframe.src + '?lang=' + isoCode + '&clientType=woocommerce';
            $('#frame_loader').hide();
            $('#lengow_iframe').show();
        }

        window.addEventListener('message', receiveMessage, false);

        function receiveMessage(event) {
            switch (event.data.function) {
                case 'sync':
                    // store lengow information into Wordpress :
                    // account_id
                    // access_token
                    // secret_token
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {do_action: 'sync', data: event.data.parameters, action: 'post_process_dashboard'},
                        dataType: 'script'
                    });
                    break;
                case 'sync_and_reload':
                    // store lengow information into Wordpress and reload it
                    // account_id
                    // access_token
                    // secret_token
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {do_action: 'sync', data: event.data.parameters, action: 'post_process_dashboard'},
                        dataType: 'script',
                        success: function () {
                            location.reload();
                        }
                    });
                    break;
                case 'reload':
                    // reload the parent page (after sync is ok)
                    location.reload();
                    break;
                case 'cancel':
                    // reload Dashboard page
                    var hrefCancel = location.href.replace('&isSync=true', '');
                    window.location.replace(hrefCancel);
                    break;
            }
        }
    });
})(jQuery);