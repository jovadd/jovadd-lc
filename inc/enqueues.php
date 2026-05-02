<?php
/**
 * Enqueue the CSS and JS files
 *
 * @package jovadd-lc
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//SUPPORT FUNCTIONS FOR DETERMINING THE RIGHT CSS BUNDLE FILENAME AND LOCATION
function jovadd-lc_get_css_url (){
    //onboarding: if no CSS custom bundle was created, serve the default one
    if (get_theme_mod("css_bundle_version_number", 0) == 0) return get_stylesheet_directory_uri() . '/'. jovadd-lc_get_css_optional_subfolder_name() . jovadd-lc_get_base_css_filename(); 

    //standard case
    return get_stylesheet_directory_uri() . '/' . jovadd-lc_get_css_optional_subfolder_name() . jovadd-lc_get_complete_css_filename(); 

}

if (!function_exists('jovadd-lc_get_css_optional_subfolder_name')):
    function jovadd-lc_get_css_optional_subfolder_name() { return "css-output/"; }
endif;

if (!function_exists('jovadd-lc_get_base_css_filename')):
    function jovadd-lc_get_base_css_filename() { return "bundle.css"; }
endif;

if (!function_exists('jovadd-lc_get_complete_css_filename')):
    function jovadd-lc_get_complete_css_filename() { 
        $filename = jovadd-lc_get_base_css_filename();
        if (is_multisite()) $filename = str_replace('.', '-' . get_current_blog_id() . '.', $filename );
        return $filename;
    }
endif;

//HELPER FUNCTION TO GET CSS BUNDLE VERSION
function jovadd-lc_get_css_version(){ 
    return(get_theme_mod ('css_bundle_version_number'));
}

//ADD THE MAIN CSS FILE
add_action( 'wp_enqueue_scripts',  function  () {
 
    //ENQUEUE THE CSS FILE
    wp_enqueue_style( 'jovadd-lc-styles', jovadd-lc_get_css_url() . '#handlecsserror', array(), jovadd-lc_get_css_version()); 
    
});

///ADD THE MAIN JS FILES
//enqueue js in footer, async
add_action( 'wp_enqueue_scripts', function() {

    //MAIN BOOTSTRAP JS
    //want to override file in child theme? use get_stylesheet_directory_uri in place of get_template_directory_uri 
    //this was done for compatibility reasons towards older child themes
    wp_enqueue_script( 'bootstrap5', get_template_directory_uri() . "/js/bootstrap.bundle.min.js", array(), null, array('strategy' => 'defer', 'in_footer' => true) );

    //DARK MODE SWITCH SUPPORT
    if (get_theme_mod('enable_dark_mode_switch')) wp_enqueue_script( 'dark-mode-switch', get_template_directory_uri() . "/js/dark-mode-switch.js", array(), null,  array('strategy' => 'defer', 'in_footer' => true) );
    
} ,100);

// PREVENT FOUC IN DARK MODE PAGE RELOAD
add_action('wp_head', function () {
    if (!get_theme_mod('enable_dark_mode_switch')) return;
    ?>
    <script>
        (function setThemeFromPreference() {
            const docEl = document.documentElement;
            const defaultTheme = 'light';

            try {
                let theme = localStorage.getItem('theme');

                if (!theme) {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    theme = prefersDark ? 'dark' : defaultTheme;
                }

                docEl.setAttribute('data-bs-theme', theme);
            } catch (error) {
                docEl.setAttribute('data-bs-theme', defaultTheme);
                    console.error('Theme detection failed:', error);
                }
        })();
    </script>
    <?php
}, 1);  

//ADD THE CUSTOM HEADER CODE (SET IN CUSTOMIZER)
add_action( 'wp_head', 'jovadd-lc_add_header_code' );
function jovadd-lc_add_header_code() {
    if (!get_theme_mod("jovadd-lc_fonts_header_code_disable")) {
        echo  get_theme_mod("jovadd-lc_fonts_header_code")." ";
    }
    echo get_theme_mod("jovadd-lc_header_code");
}

//ADD THE CUSTOM FOOTER CODE (SET IN CUSTOMIZER)
add_action( 'wp_footer', 'jovadd-lc_add_footer_code' );
function jovadd-lc_add_footer_code() {
	  //if (!current_user_can('administrator'))
      echo get_theme_mod("jovadd-lc_footer_code");
}

//ADD THE CUSTOM CHROME COLOR TAG (SET IN CUSTOMIZER)
add_action( 'wp_head', 'jovadd-lc_add_header_chrome_color' );
function jovadd-lc_add_header_chrome_color() {
	 if (get_theme_mod('jovadd-lc_header_chrome_color')!=""):
        ?><meta name="theme-color" content="<?php echo get_theme_mod('jovadd-lc_header_chrome_color'); ?>" />
	<?php endif;
}

//CSS error handling ENQUEUE: if CSS bundle file is not found, trigger recompile
function jovadd-lc_add_css_error_handling($url){
    if ( strpos( $url, '#handlecsserror') === false )
        return $url;
    else if ( !current_user_can('administrator') or isset($_GET['compile_sass']))
        return str_replace( '#handlecsserror', '', $url );
    else
	return str_replace( '#handlecsserror', '', $url )."' onerror='alert(\"CSS bundle not found. Rebuilding.\");location.href=\"?compile_sass=1&sass_nocache=1\""; 
    }
add_filter( 'clean_url', 'jovadd-lc_add_css_error_handling', 11, 1 );

//UNRENDER-BLOCK CSS 
// as per https://www.phpied.com/faster-wordpress-rendering-with-3-lines-of-configuration/

function jovadd-lc_get_headers(){
    //add link to preload CSS bundle
    $headers = "link: <".jovadd-lc_get_css_url()."?ver=".jovadd-lc_get_css_version().">; rel=preload; as=style";
    //if relevant, add the CSS for Gutenberg blocks
    if (!get_theme_mod("disable_gutenberg") OR
        ( function_exists('lc_plugin_option_is_set') && lc_plugin_option_is_set('gtblocks') )
        ) $headers.=", <".includes_url()."css/dist/block-library/style.min.css?ver=".get_bloginfo( 'version' ).">; rel=preload; as=style";
    return $headers;
}

if(!function_exists('jovadd-lc_hints')):
    function jovadd-lc_hints() {  
        header(jovadd-lc_get_headers());
    }
endif;

if  (!get_theme_mod("disable_bootstrap")) {
    add_action('send_headers', 'jovadd-lc_hints'); 
}

//for testing
add_action ("template_redirect",function(){
    if(!current_user_can("administrator") or !isset($_GET['debug_headers'])) return;
    echo "<pre style='font-size:16px;'>";
    echo "<br><br>jovadd-lc_get_headers:<br><br>". str_replace(",","<br>",htmlentities(jovadd-lc_get_headers()));
    echo "<br><br><br>Original demo:<br><br>". str_replace(",","<br>",htmlentities("link: </wp-content/themes/phpied2/style.css>; rel=preload, </wp-includes/css/dist/block-library/style.min.css?ver=5.4.1>; rel=preload"));
    echo "</pre>";
    die;
});

//DE - ENQUEUE BOOTSTRAP, IF DESIRED 
add_action('wp_enqueue_scripts',  'pico_dequeue_bootstrap', 300);

function  pico_dequeue_bootstrap() {
    if (get_theme_mod("disable_bootstrap") ):
        // Dequeue styles
        wp_dequeue_style('jovadd-lc-styles');
        
        // Dequeue scripts
        wp_dequeue_script('bootstrap5');
        wp_dequeue_script('bootstrap5-childtheme');
        wp_dequeue_script('dark-mode-switch');
    endif;
}



