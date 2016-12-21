<?php

namespace SSNepenthe\Soter;

/**
 * @todo This *may* not be necessary. Orphaned transients should only pop up from
 *       uninstalled themes or plugins and WP upgrades. WordPress already cleans out
 *       all expired transients on DB upgrade which is more than likely sufficient.
 */
class Garbage_Collection_Task {
	/**
	 * Piggybacks on the daily wp_scheduled_delete task to ensure any orphaned
	 * transients are properly cleaned up.
	 */
	public function init() {
		add_action( 'wp_scheduled_delete', [ $this, 'run_task' ] );
	}

	/**
	 * Mostly swiped from populate_options() in wp-admin/includes/schema.php.
	 *
	 * @todo Inject soter_ prefix, should be shared with transient cache class.
	 */
	public function run_task() {
		global $wpdb;

		// Only needs to run if site is storing transients in database.
		if ( wp_using_ext_object_cache() ) {
			return;
		}

		$time = time();

		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_soter_', SUBSTRING( a.option_name, 18 ) )
			AND b.option_value < %d";

		$wpdb->query( $wpdb->prepare(
			$sql,
			$wpdb->esc_like( '_transient_soter_' ) . '%',
			$wpdb->esc_like( '_transient_timeout_soter_' ) . '%',
			$time
		) );
	}
}
