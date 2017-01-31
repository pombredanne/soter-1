<?php

namespace SSNepenthe\Soter\Notices;

use DateTime;
use WP_Query;
use SSNepenthe\Soter\Views\Template;
use SSNepenthe\Soter\Cache\Cache_Interface;
use SSNepenthe\Soter\Register_Vulnerability_Post_Type;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

class Vulnerable_Site {
	protected $cache;
	protected $template;
	protected $vuln_ids;

	public function __construct(
		Template $template,
		Cache_Interface $cache,
		array $vuln_ids = []
	) {
		$this->template = $template;
		$this->cache = $cache;
		$this->vuln_ids = array_map( 'absint', $vuln_ids );
	}

	public function init() {
		add_action( 'admin_notices', [ $this, 'print_notice' ] );
	}

	public function print_notice() {
		if ( 'settings_page_soter' !== get_current_screen()->id ) {
			return;
		}

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		if ( empty( $this->vuln_ids ) ) {
			return;
		}

		$this->template->output(
			'notices/vulnerable-site',
			$this->generate_template_data()
		);
	}

	protected function generate_template_data() {
		$vulnerabilities = $this->generate_vulnerabilities_array();
		$count = count( $vulnerabilities );
		$label = 1 < $count ? 'vulnerabilities' : 'vulnerability';

		return compact( 'count', 'label', 'vulnerabilities' );
	}

	protected function generate_vulnerabilities_array() {
		$key = 'vulnerabilities_list_' . implode( '', $this->vuln_ids );

		if ( $this->cache->contains( $key ) ) {
			return $this->cache->fetch( $key );
		}

		$query = new WP_Query( [
			'meta_query' => [
				'compare' => 'IN',
				'key' => 'soter_id',
				'type' => 'NUMERIC',
				'value' => $this->vuln_ids,
			],
			'no_found_rows' => true,
			'post_type' => Register_Vulnerability_Post_Type::POST_TYPE,
			'post_status' => Register_Vulnerability_Post_Type::POST_STATUS,
			'posts_per_page' => Register_Vulnerability_Post_Type::POSTS_PER_PAGE,
			'update_post_term_cache' => false,
		] );

		$vulnerabilities = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$fixed_in = get_post_meta( get_the_ID(), 'soter_fixed_in', true );
				$id = get_post_meta( get_the_ID(), 'soter_id', true );
				$published = get_post_meta(
					get_the_ID(),
					'soter_published_date',
					true
				);
				$title = get_the_title();

				if ( $published ) {
					$published = new DateTime( $published );
				}

				$vulnerabilities[] = compact(
					'fixed_in',
					'id',
					'published',
					'title'
				);
			}

			wp_reset_postdata();
		}

		$this->cache->save( $key, $vulnerabilities, HOUR_IN_SECONDS );

		return $vulnerabilities;
	}
}
