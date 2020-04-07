<?php

namespace WP2StaticAlgolia;

class Client {
    public static function list_indices() : array {
        // get credentials out of Algolia plugin / WP options
        $admin_api_key = get_option( 'algolia_api_key' );
        $search_api_key = get_option( 'algolia_search_api_key' );
        $app_id = get_option( 'algolia_application_id' );

        $client = \Algolia\AlgoliaSearch\SearchClient::create(
          $app_id,
          $admin_api_key
        );

        $index = $client->initIndex('wp_searchable_posts');

        $indices = $client->listIndices();

        return $indices;
    }

    public static function list_objects() : array {
        $admin_api_key = get_option( 'algolia_api_key' );
        $search_api_key = get_option( 'algolia_search_api_key' );
        $app_id = get_option( 'algolia_application_id' );

        $client = \Algolia\AlgoliaSearch\SearchClient::create(
          $app_id,
          $admin_api_key
        );

		// default index: wp_searchable_posts
        $index = $client->initIndex('wp_searchable_posts');

        $objects = [];

        $iterator = $index->browseObjects();

        foreach ( $iterator as $hit ) {
            $objects[] = [
                'permalink' => $hit['permalink'],
                'post_title' => $hit['post_title'],
                'post_id' => $hit['post_id'],
                'author_user_url' => $hit['post_author']['user_url'],
            ];
        }

        return $objects;
    }
}
