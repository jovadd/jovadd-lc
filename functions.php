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

// FONT PICKER — customizer UI + self-hosting GDPR
require_once get_template_directory() . '/inc/jovadd-font-picker.php';

// CUSTOM CODE —————————————————————————————————————
