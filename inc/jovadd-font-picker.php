<?php
/**
 * Jovadd LC — Font Picker con self-hosting GDPR
 * Customizer section "Tipografia" con download on-demand da fontsource.
 */

defined( 'ABSPATH' ) || exit;

// ── Catalogo font ─────────────────────────────────────────────────────────────

function jovadd_lc_font_catalog() {
    return [
        // Sans-serif
        'Inter'           => [ 'slug' => 'inter',           'variable' => true  ],
        'DM Sans'         => [ 'slug' => 'dm-sans',         'variable' => true  ],
        'Outfit'          => [ 'slug' => 'outfit',          'variable' => true  ],
        'Nunito'          => [ 'slug' => 'nunito',          'variable' => true  ],
        'Raleway'         => [ 'slug' => 'raleway',         'variable' => true  ],
        'Montserrat'      => [ 'slug' => 'montserrat',      'variable' => true  ],
        'Open Sans'       => [ 'slug' => 'open-sans',       'variable' => true  ],
        'Poppins'         => [ 'slug' => 'poppins',         'variable' => false ],
        'Lato'            => [ 'slug' => 'lato',            'variable' => false ],
        'Roboto'          => [ 'slug' => 'roboto',          'variable' => false ],
        // Serif
        'Playfair Display'=> [ 'slug' => 'playfair-display','variable' => true  ],
        'Merriweather'    => [ 'slug' => 'merriweather',    'variable' => false ],
        'Lora'            => [ 'slug' => 'lora',            'variable' => true  ],
        'Source Serif 4'  => [ 'slug' => 'source-serif-4', 'variable' => true  ],
    ];
}

function jovadd_lc_font_filename( $slug, $variable ) {
    return $variable
        ? "{$slug}-latin-wght-normal.woff2"
        : "{$slug}-latin-400-normal.woff2";
}

// ── @font-face in wp_head ─────────────────────────────────────────────────────

add_action( 'wp_head', function() {
    $font_name = get_theme_mod( 'jovadd_lc_font', 'Inter' );
    $catalog   = jovadd_lc_font_catalog();

    if ( ! isset( $catalog[ $font_name ] ) ) return;

    $info   = $catalog[ $font_name ];
    $file   = jovadd_lc_font_filename( $info['slug'], $info['variable'] );
    $path   = get_template_directory() . '/fonts/' . $file;

    if ( ! file_exists( $path ) ) return;

    $url    = get_template_directory_uri() . '/fonts/' . esc_attr( $file );
    $format = $info['variable'] ? 'woff2-variations' : 'woff2';
    $weight = $info['variable'] ? '100 900' : '400';

    echo "<style id=\"jovadd-font-face\">\n";
    echo "@font-face {\n";
    echo "    font-family: \"" . esc_html( $font_name ) . "\";\n";
    echo "    font-style: normal;\n";
    echo "    font-display: swap;\n";
    echo "    font-weight: {$weight};\n";
    echo "    src: url('" . esc_url( $url ) . "') format('{$format}');\n";
    echo "    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;\n";
    echo "}\n";
    echo "</style>\n";
}, 1 );

// ── Registrazione customizer ──────────────────────────────────────────────────

add_action( 'customize_register', function( $wp_customize ) {
    $catalog = jovadd_lc_font_catalog();
    $choices = array_combine( array_keys( $catalog ), array_keys( $catalog ) );

    $wp_customize->add_section( 'jovadd_lc_typography', [
        'title'    => __( 'Tipografia', 'jovadd-lc' ),
        'priority' => 38,
    ] );

    $wp_customize->add_setting( 'jovadd_lc_font', [
        'default'           => 'Inter',
        'transport'         => 'postMessage',
        'sanitize_callback' => function( $val ) use ( $choices ) {
            return isset( $choices[ $val ] ) ? $val : 'Inter';
        },
    ] );

    $wp_customize->add_control( 'jovadd_lc_font', [
        'label'       => __( 'Font famiglia', 'jovadd-lc' ),
        'description' => __( 'Il font viene scaricato e servito localmente (GDPR).', 'jovadd-lc' ),
        'section'     => 'jovadd_lc_typography',
        'type'        => 'select',
        'choices'     => $choices,
    ] );
} );

// ── JS preview iframe ─────────────────────────────────────────────────────────

add_action( 'customize_preview_init', function() {
    wp_enqueue_script(
        'jovadd-font-preview',
        get_template_directory_uri() . '/inc/customizer-assets/jovadd-font-preview.js',
        [ 'customize-preview', 'jquery' ],
        null,
        true
    );

    $catalog   = jovadd_lc_font_catalog();
    $font_data = [];

    foreach ( $catalog as $name => $info ) {
        $file = jovadd_lc_font_filename( $info['slug'], $info['variable'] );
        $font_data[ $name ] = [
            'slug'     => $info['slug'],
            'variable' => $info['variable'],
            'file'     => $file,
            'url'      => file_exists( get_template_directory() . '/fonts/' . $file )
                            ? get_template_directory_uri() . '/fonts/' . $file
                            : null,
            'format'   => $info['variable'] ? 'woff2-variations' : 'woff2',
            'weight'   => $info['variable'] ? '100 900' : '400',
        ];
    }

    wp_localize_script( 'jovadd-font-preview', 'jovaddFontData', [
        'fonts'   => $font_data,
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'jovadd_font_download' ),
    ] );
} );

// ── AJAX: download font on-demand ─────────────────────────────────────────────

add_action( 'wp_ajax_jovadd_lc_download_font', function() {
    check_ajax_referer( 'jovadd_font_download', 'nonce' );

    if ( ! current_user_can( 'edit_theme_options' ) ) {
        wp_send_json_error( 'Unauthorized' );
    }

    $font_name = sanitize_text_field( $_POST['font'] ?? '' );
    $catalog   = jovadd_lc_font_catalog();

    if ( ! isset( $catalog[ $font_name ] ) ) {
        wp_send_json_error( "Font non valido: {$font_name}" );
    }

    $info  = $catalog[ $font_name ];
    $file  = jovadd_lc_font_filename( $info['slug'], $info['variable'] );
    $dest  = get_template_directory() . '/fonts/' . $file;
    $url   = get_template_directory_uri() . '/fonts/' . $file;

    // Già presente
    if ( file_exists( $dest ) && filesize( $dest ) > 0 ) {
        wp_send_json_success( [
            'url'    => $url,
            'format' => $info['variable'] ? 'woff2-variations' : 'woff2',
            'weight' => $info['variable'] ? '100 900' : '400',
        ] );
    }

    // Scarica
    $slug        = $info['slug'];
    $remote_url  = $info['variable']
        ? "https://cdn.jsdelivr.net/fontsource/fonts/{$slug}:vf@latest/latin-wght-normal.woff2"
        : "https://cdn.jsdelivr.net/fontsource/fonts/{$slug}@latest/latin-400-normal.woff2";

    $response = wp_remote_get( $remote_url, [ 'timeout' => 20 ] );

    if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
        wp_send_json_error( "Download fallito per: {$font_name}" );
    }

    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        wp_send_json_error( 'File vuoto' );
    }

    wp_mkdir_p( get_template_directory() . '/fonts' );
    file_put_contents( $dest, $body );

    wp_send_json_success( [
        'url'    => $url,
        'format' => $info['variable'] ? 'woff2-variations' : 'woff2',
        'weight' => $info['variable'] ? '100 900' : '400',
    ] );
} );
