<?php
/**
 * Performs upgrades between plugin versions.
 *
 * @package soter
 */

namespace Soter;

use WP_Query;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class performs the operations necessary to adjust the state of a site as
 * needed when moving to a new version of the plugin.
 */
class Upgrader {
	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Class constructor.
	 *
	 * @param Options_Manager $options Options manager instance.
	 */
	public function __construct( Options_Manager $options ) {
		$this->options = $options;
	}

	/**
	 * Performs all necessary upgrade steps for current version.
	 *
	 * @return void
	 */
	public function perform_upgrade() {
		$this->upgrade_to_050();
	}

	/**
	 * Deletes any lingering soter_vulnerability posts.
	 *
	 * @return void
	 */
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

	/**
	 * Ensure ignored plugins setting only contains currently installed plugins.
	 *
	 * @param  array $plugins List of ignored plugins.
	 *
	 * @return array
	 */
	protected function prepare_ignored_plugins( array $plugins ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$valid_slugs = array_map( function( $file ) {
			if ( false === strpos( $file, '/' ) ) {
				$slug = basename( $file, '.php' );
			} else {
				$slug = dirname( $file );
			}

			return $slug;
		}, array_keys( get_plugins() ) );

		return array_values( array_intersect( $valid_slugs, $plugins ) );
	}

	/**
	 * Ensure ignored themes setting only contains currently installed themes.
	 *
	 * @param  array $themes List of ignored themes.
	 *
	 * @return array
	 */
	protected function prepare_ignored_themes( array $themes ) {
		$valid_slugs = array_values( wp_list_pluck(
			wp_get_themes(),
			'stylesheet'
		) );

		return array_values( array_intersect( $valid_slugs, $themes ) );
	}

	/**
	 * Required logic for upgrading to v0.5.0.
	 *
	 * @return void
	 */
	protected function upgrade_to_050() {
		if ( $this->options->installed_version ) {
			return;
		}

		$this->upgrade_cron();
		$this->upgrade_options();
		$this->upgrade_results();

		$this->delete_vulnerabilities();

		// Set installed version so upgrader does not run again.
		$this->options->get_store()->set( 'installed_version', '0.5.0' );
	}

	/**
	 * Upgrade to latest cron implementation.
	 *
	 * @return void
	 */
	protected function upgrade_cron() {
		// Delete pre-0.4.0 cron hook if it exists.
		if ( false !== wp_next_scheduled( 'SSNepenthe\\Soter\\run_check' ) ) {
			wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
		}

		// Create 0.4.0+ cron hook if it does not exist.
		if ( false === wp_next_scheduled( 'soter_run_check' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'soter_run_check' );
		}
	}

	/**
	 * Upgrade to latest options implementation.
	 *
	 * @return void
	 */
	protected function upgrade_options() {
		// Pre-0.4.0 options array to 0.5.0+ individual option entries.
		$old_options = (array) $this->options->get_store()->get( 'settings', [] );

		if ( isset( $old_options['email_address'] ) ) {
			$sanitized = sanitize_email( $old_options['email_address'] );

			if ( $sanitized ) {
				$this->options->get_store()->set( 'email_address', $old_options['email_address'] );
			}
		}

		if ( isset( $old_options['html_email'] ) && $old_options['html_email'] ) {
			$this->options->get_store()->set( 'email_type', 'html' );
		}

		if (
			isset( $old_options['ignored_plugins'] )
			&& is_array( $old_options['ignored_plugins'] )
		) {
			$ignored_plugins = $this->prepare_ignored_plugins( $old_options['ignored_plugins'] );

			if ( ! empty( $ignored_plugins ) ) {
				$this->options->get_store()->set( 'ignored_plugins', $ignored_plugins );
			}
		}

		if (
			isset( $old_options['ignored_themes'] )
			&& is_array( $old_options['ignored_themes'] )
		) {
			$ignored_themes = $this->prepare_ignored_themes( $old_options['ignored_themes'] );

			if ( ! empty( $ignored_themes ) ) {
				$this->options->get_store()->set( 'ignored_themes', $ignored_themes );
			}
		}

		// These options don't technically get set because they are the same as the
		// defaults we have defined in the options manager class...
		$this->options->get_store()->set( 'email_enabled', 'yes' );
		$this->options->get_store()->set( 'last_scan_hash', '' );
		$this->options->get_store()->set( 'should_nag', 'yes' );
		$this->options->get_store()->set( 'slack_enabled', 'no' );
		$this->options->get_store()->set( 'slack_url', '' );

		$this->options->get_store()->delete( 'settings' );
	}

	/**
	 * Delete lingering results.
	 *
	 * @return void
	 */
	protected function upgrade_results() {
		$this->options->get_store()->delete( 'results' );
	}
}
