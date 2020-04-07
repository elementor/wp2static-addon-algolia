<?php

/**
 * Plugin Name:       WP2Static Add-on: Algolia Search
 * Plugin URI:        https://wp2static.com
 * Description:       Algolia search add-on for WP2Static.
 * Version:           1.0-alpha-2
 * Author:            Leon Stafford
 * Author URI:        https://ljs.dev
 * License:           Unlicense
 * License URI:       http://unlicense.org
 * Text Domain:       wp2static-addon-algolia
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'WP2STATIC_ALGOLIA_PATH', plugin_dir_path( __FILE__ ) );
define( 'WP2STATIC_ALGOLIA_VERSION', '1.0-alpha-2' );

require WP2STATIC_ALGOLIA_PATH . 'vendor/autoload.php';

function run_wp2static_addon_algolia() {
    $controller = new WP2StaticAlgolia\Controller();
    $controller->run();
}

register_activation_hook(
    __FILE__,
    [ 'WP2StaticAlgolia\Controller', 'activate' ]
);

register_deactivation_hook(
    __FILE__,
    [ 'WP2StaticAlgolia\Controller', 'deactivate' ]
);

function force_search_template_for_path( $query ) {
    if ( isset( $query->query['name'] ) && $query->query['name'] === 'search' ) {
        $query->is_search = true;

        // TODO: if no 's' query, add it to avoid notice in algolia plugin
        // if ( ! isset( $query->query['s'] ) ) {
        // 	$query->s = '';
        // }
    }
}

add_action( 'parse_query', 'force_search_template_for_path' );

add_action('wp_footer','modify_search_form_action');

// override search template form action from / to /search/
function modify_search_form_action(){
    $site_url = rtrim( \WP2Static\SiteInfo::getURL( 'site' ), '/' ); 

    if ( ! is_admin() ) {

$form_action_modifier_js = <<<EOD
<script>
    document.querySelectorAll('form').forEach((form, index) => {
        const formAction = form.getAttribute('action');

        // TODO: check after stripping Site URL that result is '/' or '', to not affect
        // forms targeting /wp-admin.php etc
        const replacedFormAction = formAction.replace('$site_url', '' );

        console.log('replaced form action: ' + replacedFormAction);

        if ( replacedFormAction === '' || replacedFormAction === '/' ) {
            form.setAttribute( 'action', '/search/' );
        }
    });

</script>

EOD;

    echo $form_action_modifier_js;

    }

}

run_wp2static_addon_algolia();

