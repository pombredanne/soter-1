<?php
/**
 * Performs upgrades between plugin versions.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

use SSNepenthe\Soter\Tasks\Check_Site;
use SSNepenthe\Soter\Options\Map_Option;
use SSNepenthe\Soter\Options\List_Option;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class performs the operations necessary to adjust the state of a site as
 * needed when moving to a new version of the plugin.
 */
class Upgrader {
	/**
	 * Scan results object.
	 *
	 * @var List_Option
	 */
	protected $results;

	/**
	 * Plugin settings object.
	 *
	 * @var Map_Option
	 */
	protected $settings;

	/**
	 * Class constructor.
	 *
	 * @param List_Option $results  Scan results object.
	 * @param Map_Option  $settings Plugin settings object.
	 */
	public function __construct( List_Option $results, Map_Option $settings ) {
		$this->results = $results;
		$this->settings = $settings;
	}

	/**
	 * Hooks the class in to WordPress.
	 */
	public function init() {
		add_action( 'plugins_loaded', [ $this, 'perform_upgrade' ] );
	}

	/**
	 * Performs all necessary upgrade steps for current version.
	 */
	public function perform_upgrade() {
		$this->upgrade_to_040();
	}

	/**
	 * Performs all necessary upgrade steps up version 0.4.0.
	 */
	protected function upgrade_to_040() {
		if ( $this->settings->get( 'version', false ) ) {
			return;
		}

		// Results array is formatted differently form 0.3.0 to 0.4.0 so start fresh.
		$this->results->reset();
		$this->results->save();

		// Two new options were added from 0.3.0 to 0.4.0.
		$this->settings->set( 'html_email', false );
		$this->settings->set( 'version', '0.4.0' );

		// Re-index ignored plugins and themes arrays because it makes me feel good.
		$this->settings->set(
			'ignored_plugins',
			array_values( $this->settings->get( 'ignored_plugins', [] ) )
		);

		$this->settings->set(
			'ignored_themes',
			array_values( $this->settings->get( 'ignored_themes', [] ) )
		);

		$this->settings->save();

		// Cron hook name changed from 0.3.0 to 0.4.0.
		if ( false !== wp_next_scheduled( 'SSNepenthe\\Soter\\run_check' ) ) {
			wp_clear_scheduled_hook( 'SSNepenthe\\Soter\\run_check' );
		}

		if ( false === wp_next_scheduled( Check_Site::HOOK ) ) {
			wp_schedule_event( time(), 'twicedaily', Check_Site::HOOK );
		}
	}
}