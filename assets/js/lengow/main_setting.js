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

        // Multibox selection
        $(".js-multiple-select").select2({
            closeOnSelect: false
        });
        $(".js-select").select2();


        // SUMBIT FORM
        $( ".lengow_form" ).submit(function( event ) {
            event.preventDefault();
            var form = this;

            $('.lengow_form button[type="submit"]').addClass('loading');

            setTimeout(function () {
                $('.lengow_form button[type="submit"]').removeClass('loading');
                $('.lengow_form button[type="submit"]').addClass('success');
                form.submit();
            }, 1000);
        });

        // CONFIRM DELETE
        $('.js-confirm-delete').keyup(function(){
            var confirm = $(this).data('confirm');
            if( $(this).val() == confirm ){
                $('.lengow_submit_delete_module')
                    .removeClass('lgw-btn-disabled')
                    .addClass('lgw-btn-red');
            }
            else{
                $('.lengow_submit_delete_module')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-red');
            }
        });

        // ENABLE AUTHORIZED IP
        displayAuthorizedIpMode();
        $("input[name='lengow_ip_enabled']").on('change', function () {
            displayAuthorizedIpMode();
        });

        function displayAuthorizedIpMode() {
            if ($("input[name='lengow_ip_enabled'][type='checkbox']").prop('checked')) {
                $('#lengow_wrapper_authorized_ip').slideDown(150);
            } else {
                $('#lengow_wrapper_authorized_ip').slideUp(150);
            }
        }

        // ENABLE PREPROD MODE
        displayPreProdMode();
        $("input[name='lengow_preprod_enabled']").on('change', function () {
            displayPreProdMode();
        });

        function displayPreProdMode() {
            if ($("input[name='lengow_preprod_enabled'][type='checkbox']").prop('checked')) {
                $('#lengow_wrapper_preprod').slideDown(150);
            } else {
                $('#lengow_wrapper_preprod').slideUp(150);
            }
        }

        // ENABLE ORDER IMPORT
        displayOrderImportMode();
        $("input[name='lengow_import_enabled']").on('change', function () {
            displayOrderImportMode();
        });

        function displayOrderImportMode() {
            if ($("input[name='lengow_import_enabled'][type='checkbox']").prop('checked')) {
                $('#lengow_wrapper_import').slideDown(150);
            } else {
                $('#lengow_wrapper_import').slideUp(150);
            }
        }

        // DOWNLOAD LOGS
        $('.js-log-select').change(function(){
            if ($('.js-log-select').val() !== null) {
                $(".js-log-btn-download" ).show();
            }
        });

        $('.js-log-btn-download').on('click', function() {
            if ($('.js-log-select').val() !== null) {
                window.location.href = $('.js-log-select').val();
            }
        });

    });

})(jQuery);
