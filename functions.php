<?php
/**
 * Jovadd LC — functions.php
 * Bootstrap 5 + LiveCanvas starter kit
 * Author: Giovanni Caserta
 * Author URI: https://github.com/jovadd
 * Theme URI: https://github.com/jovadd/jovadd-lc
 */

// DE-ENQUEUE PARENT BOOTSTRAP JS SE PRESENTE
add_action( 'wp_print_scripts', function(){
    wp_dequeue_script( 'bootstrap5' );
}, 100 );

// ENQUEUE BOOTSTRAP JS
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_script(
        'bootstrap5-jovadd-lc',
        get_template_directory_uri() . "/js/bootstrap.bundle.min.js",
        array(),
        null,
        array('strategy' => 'defer', 'in_footer' => true)
    );
}, 101);

// ENQUEUE CUSTOM JS
add_action( 'wp_enqueue_scripts', function() {

    // Globale — decommentare per attivare
    // wp_enqueue_script(
    //     'jovadd-lc-custom',
    //     get_template_directory_uri() . '/js/custom.js',
    //     array(),
    //     null,
    //     array('strategy' => 'defer', 'in_footer' => true)
    // );

    // Solo su pagina specifica — decommentare e sostituire slug
    // if (is_page('slug-pagina')) {
    //     wp_enqueue_script(
    //         'jovadd-lc-custom',
    //         get_template_directory_uri() . '/js/custom.js',
    //         array(),
    //         null,
    //         array('strategy' => 'defer', 'in_footer' => true)
    //     );
    // }

}, 102);

// REGISTRA MENU AGGIUNTIVI — decommentare se necessario
// register_nav_menus( array(
//     'secondary' => __( 'Secondary Menu', 'jovadd-lc' ),
//     'footer'    => __( 'Footer Menu', 'jovadd-lc' ),
// ));

// SICUREZZA: DISABILITA APPLICATION PASSWORDS — decommentare se necessario
// add_filter( 'wp_is_application_passwords_available', '__return_false' );

// FONT SELF-HOSTED (GDPR) — Inter da /fonts/ locale, nessuna richiesta esterna
add_action( 'wp_head', function() {
    $font_url = get_template_directory_uri() . '/fonts/inter-latin-wght-normal.woff2';
    echo '<style>
@font-face {
    font-family: "Inter";
    font-style: normal;
    font-display: swap;
    font-weight: 100 900;
    src: url(' . esc_url( $font_url ) . ') format("woff2-variations");
    unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
}
</style>' . "\n";
}, 1 );

// CUSTOM CODE —————————————————————————————————————
