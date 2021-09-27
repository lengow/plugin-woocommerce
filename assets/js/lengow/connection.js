/**
 * Copyright 2021 Lengow SAS.
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
 * @copyright 2021 Lengow SAS
 * @license   https://www.gnu.org/licenses/gpl-3.0
 */

(function($) {
    $(document).ready(function() {

        var connectionContent = $('#lgw-connection-content');

        // go to credentials form
        connectionContent.on('click', '.js-go-to-credentials', function() {
            var data = {
                action: 'post_process_connection',
                do_action: 'go_to_credentials',
            };
            $.post(ajaxurl, data, function(response) {
                $('#lgw-connection-content').html(response);
            });
        });

        // go to catalog form
        connectionContent.on('click', '.js-go-to-catalog', function() {
            var retry = $(this).attr('data-retry') !== 'false';
            var data = {
                action: 'post_process_connection',
                do_action: 'go_to_catalog',
                retry: retry,
            };
            $.post(ajaxurl, data, function(response) {
                $('#lgw-connection-content').html(response);
                $('#lgw-connection-content select').select2();
            });
        });

        // active check credentials button
        connectionContent.on('change', '.js-credentials-input', function() {
            var accessToken = $('input[name=lgwAccessToken]').val();
            var secret = $('input[name=lgwSecret]').val();
            if (accessToken !== '' && secret !== '') {
                $('.js-connect-cms')
                    .removeClass('lgw-btn-disabled')
                    .addClass('lgw-btn-green');
            } else{
                $('.js-connect-cms')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-green');
            }
        });

        // check api credentials
        connectionContent.on('click', '.js-connect-cms', function() {
            var accessToken = $('input[name=lgwAccessToken]');
            var secret = $('input[name=lgwSecret]');
            $('.js-connect-cms').addClass('loading');
            accessToken.prop('disabled', true);
            secret.prop('disabled', true);
            var data = {
                action: 'post_process_connection',
                do_action: 'connect_cms',
                access_token: accessToken.val(),
                secret: secret.val(),
            };
            $.post(ajaxurl, data, function(response) {
                $('#lgw-connection-content').html(response);
            });
        });

        // link catalog ids
        connectionContent.on('click', '.js-link-catalog', function() {
            var catalogSelected = [];
            var shopSelect = $('.js-catalog-linked');
            shopSelect.each(function() {
                if ($(this).val() !== null) {
                    var catalogIds = $(this).val();
                    $.each(catalogIds, function(key, value) {
                        catalogSelected.push(parseInt(value, 10))
                    })
                }
            });
            $('.js-link-catalog').addClass('loading');
            shopSelect.prop('disabled', true);
            var data = {
                action: 'post_process_connection',
                do_action: 'link_catalogs',
                catalog_selected: catalogSelected,
            };
            $.post(ajaxurl, data, function(response) {
                var success;
                try {
                    var data = JSON.parse(response);
                    success = data.success;
                } catch (e) {
                    success = false;
                }
                if (success) {
                    location.reload();
                } else {
                    $('#lgw-connection-content').html(response);
                }
            });
        });
    });
})(jQuery);