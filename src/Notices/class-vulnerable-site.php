<?php
/**
 * Full vulnerable site notice.
 *
 * @package soter
 */

namespace Soter\Notices;

use DateTime;
use WP_Query;
use Soter\Views\Template;
use Soter_Core\Cache_Interface;
use Soter\Register_Vulnerability_Post_Type;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class adds an admin notice with details of all current vulnerabilities.
 */
class Vulnerable_Site {
	/**
	 * Cache instance.
	 *
	 * @var Cache_Interface
	 */
	protected $cache;

	/**
	 * Template instance.
	 *
	 * @var Template
	 */
	protected $template;

	/**
	 * List of vulnerability IDs that currently affect the site.
	 *
	 * @var int[]
	 */
	protected $vuln_ids;

	/**
	 * Class constructor.
	 *
	 * @param Template        $template Template instance.
	 * @param Cache_Interface $cache    Cache instance.
	 * @param int[]           $vuln_ids List of vulnerability IDs for notice.
	 */
	public function __construct(
		Template $template,
		Cache_Interface $cache,
		array $vuln_ids = []
	) {
		$this->template = $template;
		$this->cache = $cache;
		$this->vuln_ids = array_map( 'absint', $vuln_ids );
	}

	/**
	 * Prints the actual notice.
	 */
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

	/**
	 * Generates the data array to be used by the template.
	 *
	 * @return array
	 */
	protected function generate_template_data() {
		$vulnerabilities = $this->generate_vulnerabilities_array();
		$count = count( $vulnerabilities );
		$label = 1 === $count ? 'vulnerability' : 'vulnerabilities';

		return compact( 'count', 'label', 'vulnerabilities' );
	}

	/**
	 * Generates and caches the vulnerabilities array for the template data.
	 *
	 * @return array
	 */
	protected function generate_vulnerabilities_array() {
		$key = 'vulnerabilities_list_' . implode( '', $this->vuln_ids );
		$cached = $this->cache->get( $key );

		if ( ! is_null( $cached ) ) {
			return $cached;
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

		$this->cache->put( $key, $vulnerabilities, HOUR_IN_SECONDS );

		return $vulnerabilities;
	}
}
