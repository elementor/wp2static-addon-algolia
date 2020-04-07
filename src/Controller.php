<?php

namespace WP2StaticAlgolia;

class Controller {
    public function run() : void {
        global $wpdb;

        add_filter( 'wp2static_add_menu_items', [ 'WP2StaticAlgolia\Controller', 'addSubmenuPage' ] );

		// main search function index
        add_filter(
            'algolia_searchable_post_records',
            [ $this, 'modifyPostRecords' ],
            15,
            1
        );

        add_filter(
            'algolia_post_records',
            [ $this, 'modifyPostRecords' ],
            15,
            1
        );


		// all other post_type indices Algolia plugin uses
		$post_type_indices = [
			'post',
			'page',
			'attachment',
			'custom_css',
			'customize_changeset',
			'oembed_cache',
			'user_request',
			'wp_block',
		];

		foreach ( $post_type_indices as $post_type) {
			add_filter(
				"algolia_searchable_post_${post_type}_records",
				[ $this, 'modifyPostRecords' ],
				15,
				1
			);

			add_filter(
				"algolia_post_${post_type}_records",
				[ $this, 'modifyPostRecords' ],
				15,
				1
			);
		}

        if ( defined( 'WP_CLI' ) ) {
            \WP_CLI::add_command(
                'wp2static algolia',
                [ 'WP2StaticAlgolia\CLI', 'algolia' ]
            );
        }
    }

    public static function renderAlgoliaPage() : void {
        // $view = [];
        // $view['nonce_action'] = 'wp2static-algolia-options';

        require_once __DIR__ . '/../views/algolia-page.php';
    }

    public function modifyPostRecords( array $post_records, $post = null) : array {
        \WP2Static\WsLog::l( 'Algolia Addon modifying post records' );

        $site_url = rtrim( \WP2Static\SiteInfo::getURL( 'site' ), '/' ); 

        foreach ( $post_records as &$post_record ) {
			if ( isset( $post_record['permalink'] ) ) {
				$post_record['permalink'] = str_replace(
					$site_url,
					'',
					$post_record['permalink']   
				);
			}

			if ( isset( $post_record['post_author'] ) ) {

				if ( isset( $post_record['post_author']['user_url'] ) ) {
					$modified_user_url = str_replace(
						$site_url,
						'',
						$post_record['post_author']['user_url']   
					);

					$post_record['post_author']['user_url'] =
						$modified_user_url === '' ? '/' : $modified_user_url;

				}
			}
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
