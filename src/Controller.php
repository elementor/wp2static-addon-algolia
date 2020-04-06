<?php

namespace WP2StaticAlgolia;

class Controller {
    public function run() : void {
        global $wpdb;

        add_filter( 'wp2static_add_menu_items', [ 'WP2StaticAlgolia\Controller', 'addSubmenuPage' ] );

        add_filter(
            'algolia_searchable_post_records',
            [ $this, 'modifyPostRecords' ],
            15,
            1
        );

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static algolia',
                [ 'WP2StaticAlgolia\CLI', 'algolia' ]
            );
        }
    }

    public static function renderAlgoliaPage() : void {
        $view = [];
        $view['nonce_action'] = 'wp2static-algolia-delete';
        $view['uploads_path'] = \WP2Static\SiteInfo::getPath( 'uploads' );
        $algolia_path = \WP2Static\SiteInfo::getPath( 'uploads' ) . 'wp2static-processed-site.algolia';

        $view['algolia_path'] = is_file( $algolia_path ) ? $algolia_path : false;

        if ( is_file( $algolia_path ) ) {
            $view['algolia_size'] = filesize( $algolia_path );
            $view['algolia_created'] = gmdate( 'F d Y H:i:s.', (int) filemtime( $algolia_path ) );
        }

        $view['algolia_url'] =
            is_file( $algolia_path ) ?
                \WP2Static\SiteInfo::getUrl( 'uploads' ) . 'wp2static-processed-site.algolia' : '#';

        require_once __DIR__ . '/../views/algolia-page.php';
    }


    public function modifyPostRecords( array $post_records, $post = null) : array {
        \WP2Static\WsLog::l( 'Algolia Addon modifying post records' );

        $site_url = rtrim( \WP2Static\SiteInfo::getURL( 'site' ), '/' ); 

        error_log('siteURL ' . $site_url);


        foreach ( $post_records as &$post_record ) {
            $post_record['permalink'] = str_replace(
                $site_url,
                '',
                $post_record['permalink']   
            );

            // TODO: iterate each potential post_author
        }

        return $post_records;
    }

    public static function activate_for_single_site() : void {
    }

    public static function deactivate_for_single_site() : void {
        error_log( 'deactivating algolia addon, maintaining options' );
    }

    public static function deactivate( bool $network_wide = null ) : void {
        error_log( 'deactivating algolia addon 2' );
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::deactivate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::deactivate_for_single_site();
        }
    }

    public static function activate( bool $network_wide = null ) : void {
        error_log( 'activating algolia addon' );
        if ( $network_wide ) {
            global $wpdb;

            $query = 'SELECT blog_id FROM %s WHERE site_id = %d;';

            $site_ids = $wpdb->get_col(
                sprintf(
                    $query,
                    $wpdb->blogs,
                    $wpdb->siteid
                )
            );

            foreach ( $site_ids as $site_id ) {
                switch_to_blog( $site_id );
                self::activate_for_single_site();
            }

            restore_current_blog();
        } else {
            self::activate_for_single_site();
        }
    }

    /**
     * Add sub menu to WP2Static menu
     *
     * @param mixed[] $submenu_pages array of loaded submenu pages
     * @return mixed[] array of submenu pages
     */
    public static function addSubmenuPage( $submenu_pages ) : array {
        $submenu_pages['algolia'] = [ 'WP2StaticAlgolia\Controller', 'renderAlgoliaPage' ];

        return $submenu_pages;
    }
}
