console.log( 'pandamusrex admin.js loaded successfully' );

jQuery( document ).ready( function( $ ){
    var custom_uploader;

    $( '#pandamusrex_zelle_qr_code_upload_button' ).click( function( e ) {
        e.preventDefault();
        // If the uploader object has already been created, reopen the dialog
        if ( custom_uploader ) {
            custom_uploader.open();
            return;
        }
        // Extend the wp.media object
        custom_uploader = wp.media( {
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
            multiple: false
        } );
        // When a file is selected, grab the URL and set it as the text field's value
        custom_uploader.on( 'select', function() {
            attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
            $( '#pandamusrex_zelle_qr_code_img_id' ).val( attachment.id );
        } );
        // Open the uploader dialog
        custom_uploader.open();
    } );
} );