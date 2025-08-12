console.log( 'pandamusrex admin.js loaded successfully' );

jQuery( document ).ready( function( $ ){
    var custom_uploader;

    // Set initial visibility states based on image value
    image_id = $( '#pandamusrex_zelle_qr_code_img_id' ).val() | '';
    console.log( 'image_id = ', image_id );
    if ( image_id ) {
        console.log( 'truthy val' );
        $( '#pandamusrex_zelle_qr_code_remove' ).show();
        $( '#pandamusrex_zelle_qr_code_image' ).show();
        $( '#pandamusrex_zelle_qr_code_upload_button' ).hide();
    } else {
        console.log( 'falsy val' );
        $( '#pandamusrex_zelle_qr_code_remove' ).hide();
        $( '#pandamusrex_zelle_qr_code_image' ).hide();
        $( '#pandamusrex_zelle_qr_code_upload_button' ).show();
    }

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

        // When a file is selected
        custom_uploader.on( 'select', function() {
            attachment = custom_uploader.state().get( 'selection' ).first().toJSON();

            $( '#pandamusrex_zelle_qr_code_remove' ).show();
            $( '#pandamusrex_zelle_qr_code_img_id' ).val( attachment.id );
            $( '#pandamusrex_zelle_qr_code_image' ).attr( 'src', attachment.url );
            $( '#pandamusrex_zelle_qr_code_image' ).show();
            $( '#pandamusrex_zelle_qr_code_upload_button' ).hide();
        } );

        // Open the uploader dialog
        custom_uploader.open();
    } );

    $( 'body' ).on( 'click', '#pandamusrex_zelle_qr_code_remove', function( event ){
        event.preventDefault();

        $( '#pandamusrex_zelle_qr_code_remove' ).hide();
        $( '#pandamusrex_zelle_qr_code_img_id' ).val( '' );
        $( '#pandamusrex_zelle_qr_code_image' ).hide();
        $( '#pandamusrex_zelle_qr_code_upload_button' ).show();
	});
} );