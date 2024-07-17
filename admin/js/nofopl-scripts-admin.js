/**
 * Admin Js
 * @package Nofollow Post Links
 * @subpackage nofopl-scripts-admin.js
 * @since 1.0.0
 */

const success = nofopl_args.message_success;
const failed = nofopl_args.message_failed;
const e_harmonize = nofopl_args.message_e_harmonize;

jQuery(function($) {

    /**
     * Ajax action nofollow
     */
    $('#nofopl-ajax-nofollow').on('click', function() {
        $.post( 
            ajaxurl,
            {   
                action: 'massiveNoFollow',
                nofoplMassive: $(this).val(),
                nofoplNonce: $(this).attr('data-nonce')
            },
            function(data, response) {

                console.log(data);

                if( data == 1) {
                    $('#nofopl-ajax-nofollow').prop('disabled', true ).addClass('disabled');
                    $('<span class="nofopl-message nofopl-success"><span class="dashicons dashicons-yes"></span> ' + success + '</span>').insertAfter('#nofopl-ajax-nofollow').fadeOut(3000);
                    $('#nofopl-ajax-dofollow, #nofopl-ajax-restore').prop('disabled', false ).removeClass('disabled');
                    $('#nofopl-ajax-map, #nofopl-ajax-harmonize').prop('disabled', true ).addClass('disabled');
                } else {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + failed + '</span>').insertAfter('#nofopl-ajax-nofollow').fadeOut(3000);
                }
            
            }
        ); 
    });

    /**
     * Ajax action dofollow
     */
    $('#nofopl-ajax-dofollow').on('click', function() {
        $.post( 
            ajaxurl,
            {   
                action: 'massiveDoFollow',
                nofoplMassiveDo: $(this).val(),
                nofoplNonce: $(this).attr('data-nonce')
            },
            function(data, response) {

                console.log(data);

                if( data == 1) {
                    $('#nofopl-ajax-dofollow').prop('disabled', true ).addClass('disabled');
                    $('<span class="nofopl-message nofopl-success"><span class="dashicons dashicons-yes"></span> ' + success + '</span>').insertAfter('#nofopl-ajax-dofollow').fadeOut(3000);
                    $('#nofopl-ajax-nofollow, #nofopl-ajax-restore').prop('disabled', false ).removeClass('disabled');
                    $('#nofopl-ajax-map, #nofopl-ajax-harmonize').prop('disabled', true ).addClass('disabled');
                } else {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + failed + '</span>').insertAfter('#nofopl-ajax-nofollow').fadeOut(3000);
                }  
            
            }
        ); 
    });

    /**
     * Ajax action restore
     */
    $('#nofopl-ajax-restore').on('click', function() {
        $.post( 
            ajaxurl,
            {   
                action: 'restorePostSettings',
                nofoplRestore: $(this).val(),
                nofoplNonce: $(this).attr('data-nonce')
            },
            function(data, response) {

                console.log(data);

                if( data == 1) {
                    $('#nofopl-ajax-restore').prop('disabled', true).addClass('disabled');
                    $('<span class="nofopl-message nofopl-success"><span class="dashicons dashicons-yes"></span> ' + success + '</span>').insertAfter('#nofopl-ajax-restore').fadeOut(3000);
                    $('#nofopl-ajax-dofollow, #nofopl-ajax-nofollow, #nofopl-ajax-map, #nofopl-ajax-harmonize').prop('disabled', false ).removeClass('disabled');
                } else {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + failed + '</span>').insertAfter('#nofopl-ajax-restore').fadeOut(3000);
                }  
            
            }
        ); 
    });

    /**
     * Ajax action map
     */
    $('#nofopl-ajax-map').on('click', function() {
        $.post( 
            ajaxurl,
            {   
                action: 'mapNofollow',
                nofoplMap: $(this).val(),
                nofoplNonce: $(this).attr('data-nonce')
            },
            function(data, response) {

                console.log(data);

                if( data == 1) {
                    $('#nofopl-ajax-map').prop('disabled', true).addClass('disabled');
                    $('<span class="nofopl-message nofopl-success"><span class="dashicons dashicons-yes"></span> ' + success + '</span>').insertAfter('#nofopl-ajax-map').fadeOut(3000);
                } else {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + failed + '</span>').insertAfter('#nofopl-ajax-map').fadeOut(3000);
                }  
            
            }
        ); 
    });

    /**
     * Ajax action harmonize
     */
    $('#nofopl-ajax-harmonize').on('click', function() {
        $.post( 
            ajaxurl,
            {   
                action: 'harmonizeFollow',
                nofoplHarmonize: $(this).val(),
                nofoplHarmonizeDomain: $('#harmonize-follow-domain').val(),
                nofoplHarmonizeType: $('.harmonize-follow-type[type=radio]:checked').val(),
                nofoplNonce: $(this).attr('data-nonce')
            },
            function(data, response) {

                console.log(data);

                if( data == 1) {
                    $('<span class="nofopl-message nofopl-success"><span class="dashicons dashicons-yes"></span> ' + success + '</span>').insertAfter('#nofopl-ajax-harmonize').fadeOut(3000);
                } 
                else if( data == -4 ) {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + e_harmonize + '</span>').insertAfter('#nofopl-ajax-harmonize').fadeOut(3000);
                }
                else {
                    $('<span class="nofopl-message nofopl-failed"><span class="dashicons dashicons-no"></span> ' + failed + '</span>').insertAfter('#nofopl-ajax-harmonize').fadeOut(3000);
                }  
            
            }
        ); 
    });

});