<?php
/**
 * Performs upgrades between plugin versions.
 *
 * @package soter
 */

namespace Soter;

use WP_Query;
use Soter\Jobs\Check_Site;
use Soter\Options\Options_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class performs the operations necessary to adjust the state of a site as
 * needed when moving to a new version of the plugin.
 */
class Upgrader {
	protected $options;

	/**
	 * Class constructor.
	 */
	public function __construct( Options_Manager $options ) {
		$this->options = $options;
	}

	/**
	 * Performs all necessary upgrade steps for current version.
	 */
	public function perform_upgrade() {
		$this->upgrade_to_050();
	}

	protected function delete_vulnerabilities() {
		$query = new WP_Query( [
			'fields' => 'ids',
			'no_found_rows' => true,
			'post_type' => 'soter_vulnerability',
			'post_status' => 'private',
			// @todo Vulnerabilities were previously garbage collected on a daily
			// basis so it shouldn't be a problem to get all of them - worth testing.
			'posts_per_page' => -1,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		] );

		// Can be empty.
		foreach ( $query->posts as $id ) {
			wp_delete_post( $id );
		}
	}

	protected function upgrade_to_050() {
		if ( $this->options->installed_version() ) {
			return;
		}

		$this->upgrade_cron();
		$this->upgrade_options();
		$this->upgrade_results();

		$this->delete_vulnerabilities();

		// Set installed version so upgrader does not run again.
		$this->options->set_installed_version( '0.5.0' );
	}

	protected function upgrade_cron() {
		// Delete pre-0.4.0 cron hook if it exists.
		if ( false !== wp_next_scheduled( 'SSNepenthe\\Soter\\run_check' ) ) {
			wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
		}

		// Create 0.4.0+ cron hook if it does not exist.
		if ( false === wp_next_scheduled( Check_Site::get_hook() ) ) {
			wp_schedule_event( time(), 'twicedaily', Check_Site::get_hook() );
		}
	}

	protected function upgrade_options() {
		// Pre-0.4.0 options array to 0.5.0+ individual option entries.
		$old_options = get_option( 'soter_settings' );
		$old_options = (array) $this->options->get_store()->get( 'settings' );

		if ( isset( $old_options['email_address'] ) ) {
			$this->options->set_email_address( $old_options['email_address'] );
		}

		if ( isset( $old_options['html_email'] ) && $old_options['html_email'] ) {
			$this->options->set_email_type( 'html' );
		}

		if (
			isset( $old_options['ignored_plugins'] )
			&& is_array( $old_options['ignored_plugins'] )
		) {
			$this->options->set_ignored_plugins( $old_options['ignored_plugins'] );
		}

		if (
			isset( $old_options['ignored_themes'] )
			&& is_array( $old_options['ignored_themes'] )
		) {
			$this->options->set_ignored_themes( $old_options['ignored_themes'] );
		}

		$this->options->set_should_nag( 'yes' );

		$this->options->get_store()->delete( 'settings' );
	}

	protected function upgrade_results() {
		$this->options->get_store()->delete( 'results' );
	}
}
