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
            $(".lengow-nav").reload();
        });

        // MODAL

        // Open modal
        $('.lgw-modal-delete').click(function(){
            window.location.hash = 'delete';
            return false;
        });

        // Open modal on loading

        if(window.location.hash) {
            openModal();
        }

        // Delete modal
        $('.js-close-this-modal').click(function(){
            window.location.hash = '';
            return false;
        });

        var hash = window.location.hash;
        setInterval(function(){
            if (window.location.hash != hash) {
                hash = window.location.hash;
                if( hash.length < 1){
                    killModal();
                }
                else{
                    if( $('.lgw-modal.open').length == 0 ){
                        openModal();
                    }
                }
            }
        }, 100);

        function killModal(){
            window.location.hash = '';
            $('body').removeClass('unscrollable');
            $('.lgw-modal').removeClass('open');
            $('.js-confirm-delete').val('');
            $('.lengow_submit_delete_module')
                    .addClass('lgw-btn-disabled')
                    .removeClass('lgw-btn-red');
        }

        function openModal(){
            window.location.hash = 'delete';
            $('body').addClass('unscrollable');
            $('.lgw-modal').addClass('open');
        }


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

        // ORDER IMPORT
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
