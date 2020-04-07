<?php

namespace WP2StaticAlgolia;

use WP_CLI;


/**
 * WP2StaticAlgolia WP-CLI commands
 *
 * Registers WP-CLI commands for WP2StaticAlgolia under main wp2static cmd
 *
 * Usage: wp wp2static algolia list_indices
 */
class CLI {

    /**
     * Algolia commands
     *
     * @param string[] $args CLI args
     * @param string[] $assoc_args CLI args
     */
    public function algolia(
        array $args,
        array $assoc_args
    ) : void {
        $action = isset( $args[0] ) ? $args[0] : null;

        if ( empty( $action ) ) {
            WP_CLI::error( 'Missing required argument: <list_indices|list_objects>' );
        }

        if ( $action === 'list_indices' ) {
            $indices = Client::list_indices();

            WP_CLI::line( json_encode( $indices, JSON_PRETTY_PRINT ) );
        }

        if ( $action === 'list_objects' ) {
            $objects = Client::list_objects();

            WP_CLI::line( json_encode( $objects, JSON_PRETTY_PRINT ) );
        }
    }
}

