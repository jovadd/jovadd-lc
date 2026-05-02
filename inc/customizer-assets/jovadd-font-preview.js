/**
 * Jovadd LC — Font live preview nel customizer iframe
 * Ascolta il cambio setting, scarica il font se necessario, inietta @font-face + CSS.
 */
( function ( $ ) {

    wp.customize( 'jovadd_lc_font', function ( setting ) {
        setting.bind( function ( fontName ) {
            var info = jovaddFontData.fonts[ fontName ];
            if ( ! info ) return;

            if ( info.url ) {
                injectFont( fontName, info );
            } else {
                // Font non ancora scaricato — download AJAX
                showLoadingIndicator( fontName );

                $.post( jovaddFontData.ajaxUrl, {
                    action: 'jovadd_lc_download_font',
                    font:   fontName,
                    nonce:  jovaddFontData.nonce
                } )
                .done( function ( response ) {
                    if ( response.success ) {
                        info.url    = response.data.url;
                        info.format = response.data.format;
                        info.weight = response.data.weight;
                        jovaddFontData.fonts[ fontName ] = info;
                        injectFont( fontName, info );
                    } else {
                        console.warn( 'Jovadd Font: download fallito per ' + fontName );
                    }
                } )
                .always( function () {
                    removeLoadingIndicator();
                } );
            }
        } );
    } );

    function injectFont( name, info ) {
        $( '#jovadd-font-preview-style' ).remove();

        var fontFace = '@font-face {'
            + ' font-family: "' + name + '";'
            + ' font-style: normal;'
            + ' font-display: swap;'
            + ' font-weight: ' + info.weight + ';'
            + ' src: url("' + info.url + '") format("' + info.format + '");'
            + ' }';

        var apply = 'body, body *, h1, h2, h3, h4, h5, h6, .navbar, .btn {'
            + ' font-family: "' + name + '", sans-serif !important; }';

        $( '<style id="jovadd-font-preview-style">' )
            .text( fontFace + '\n' + apply )
            .appendTo( 'head' );
    }

    function showLoadingIndicator( fontName ) {
        $( '#jovadd-font-loading' ).remove();
        $( '<div id="jovadd-font-loading" style="'
            + 'position:fixed;bottom:16px;right:16px;z-index:99999;'
            + 'background:#111;color:#fff;padding:8px 14px;border-radius:4px;'
            + 'font-size:13px;font-family:sans-serif;">'
            + '⏳ Scarico ' + fontName + '…</div>' )
            .appendTo( 'body' );
    }

    function removeLoadingIndicator() {
        $( '#jovadd-font-loading' ).remove();
    }

} )( jQuery );
