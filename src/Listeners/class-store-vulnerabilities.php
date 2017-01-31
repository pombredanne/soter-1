<?php

namespace SSNepenthe\Soter\Listeners;

use WP_Post;
use WP_Query;
use SSNepenthe\Soter\WPScan\Vulnerability;
use SSNepenthe\Soter\Register_Vulnerability_Post_Type;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Store_Vulnerabilities {
	public function init() {
		add_action(
			'soter_check_packages_complete',
			[ $this, 'store_vulnerabilities' ]
		);
	}

	public function store_vulnerabilities( array $vulnerabilities ) {
		if ( empty( $vulnerabilities ) ) {
			return;
		}

		// We may already have this vuln. in the database.
		$existing_vulnerabilities = $this->get_existing_vuln_posts_from_api_ids(
			wp_list_pluck( $vulnerabilities, 'id' )
		);

		foreach ( $vulnerabilities as $vulnerability ) {
			$post_array = $this->create_post_array_from_vuln( $vulnerability );

			// If vuln already exists in DB, set the ID so post is kept in sync with
			// API. More ideal approach would be to minimize queries by comparing
			// vuln data to post data and only update post meta if it changed.
			if ( $post = $this->vulnerability_in_post_array(
				$vulnerability,
				$existing_vulnerabilities
			) ) {
				$post_array['ID'] = $post->ID;
			}

			wp_insert_post( $post_array, true );
		}
	}

	protected function create_post_array_from_vuln( Vulnerability $vulnerability ) {
		$array = [
			'comment_status' => 'closed',
			'meta_input' => [
				'soter_id' => $vulnerability->id,
				'soter_created_at' => $vulnerability->get_data()['created_at'],
				'soter_updated_at' => $vulnerability->get_data()['updated_at'],
				'soter_vuln_type' => $vulnerability->vuln_type,
			],
			'ping_status' => 'closed',
			'post_status' => Register_Vulnerability_Post_Type::POST_STATUS,
			'post_title' => $vulnerability->title,
			'post_type' => Register_Vulnerability_Post_Type::POST_TYPE,
		];

		if ( $vulnerability->published_date ) {
			$array['meta_input']['soter_published_date'] = $vulnerability->get_data()['published_date'];
		}

		if ( $vulnerability->fixed_in ) {
			$array['meta_input']['soter_fixed_in'] = $vulnerability->fixed_in;
		}

		return $array;
	}

	protected function get_existing_vuln_posts_from_api_ids( array $vuln_ids ) {
		$vuln_ids = array_map( 'absint', $vuln_ids );

		$query = new WP_Query( [
			'meta_query' => [
				[
					'compare' => 'IN',
					'key' => 'soter_id',
					'type' => 'NUMERIC',
					'value' => $vuln_ids,
				],
			],
			'no_found_rows' => true,
			'post_status' => Register_Vulnerability_Post_Type::POST_STATUS,
			'post_type' => Register_Vulnerability_Post_Type::POST_TYPE,
			'posts_per_page' => Register_Vulnerability_Post_Type::POSTS_PER_PAGE,
			'update_post_term_cache' => false,
		] );

		return $query->posts;
	}

	protected function vulnerability_in_post_array(
		Vulnerability $vulnerability,
		array $posts
	) {
		foreach ( $posts as $post ) {
			if ( $this->vulnerability_matches_post( $vulnerability, $post ) ) {
				return $post;
			}
		}

		return false;
	}

	protected function vulnerability_matches_post(
		Vulnerability $vulnerability,
		WP_Post $post
	) {
		$post_api_id = intval( get_post_meta( $post->ID, 'soter_id', true ) );

		return $vulnerability->id === $post_api_id;
	}
}
