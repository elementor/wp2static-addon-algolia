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

    // TODO: override search template form action from / to /search/

    // TODO: add /search/ page if not existant

    // error_log(print_r($query, true));

    // add logic to do this only on /search/ url
    if ( isset( $query->query['pagename'] ) && $query->query['pagename'] === 'search' ) {
        $query->is_search = true;

        // // if no 's' query, add it to avoid notice in algolia plugin
        // if ( ! isset( $query->query['s'] ) ) {
        // 	$query->s = '';
        // }
    }
}

add_action( 'parse_query', 'force_search_template_for_path' );

run_wp2static_addon_algolia();

