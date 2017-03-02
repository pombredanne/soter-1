<?php
/**
 * Run scheduled transient cleanups.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Tasks;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class handles garbage collecting plugin-specific orphaned transients.
 *
 * This *may* not be necessary. Sources of orphaned transients include uninstalling
 * plugins or themes, upgrading WordPress and the query which produces the vulnerable
 * site report in wp-admin.
 *
 * WordPress already cleans out expired transients on DB upgrade which is more than
 * likely sufficient.
 */
class Transient_Garbage_Collection {
	/**
	 * Transient prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Class constructor.
	 *
	 * @param string $prefix Transient prefix.
	 */
	public function __construct( $prefix ) {
		$this->prefix = substr( (string) $prefix, 0, 12 ) . '_';
	}

	/**
	 * Hooks the task functionality in to WordPress.
	 *
	 * Specifically, this piggybacks on the daily wp_scheduled_delete task.
	 */
	public function init() {
		add_action( 'wp_scheduled_delete', [ $this, 'run_task' ] );
	}

	/**
	 * Deletes all transients from the database with the specified prefix.
	 *
	 * Mostly swiped from populate_options() in wp-admin/includes/schema.php.
	 */
	public function run_task() {
		global $wpdb;

		// Only needs to run if site is storing transients in database.
		if ( wp_using_ext_object_cache() ) {
			return;
		}

		$time = time();

		$transient_prefix = '_transient_' . $this->prefix;
		$timeout_prefix = '_transient_timeout_' . $this->prefix;
		$length = strlen( $transient_prefix ) + 1;

		$sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( %s, SUBSTRING( a.option_name, %d ) )
			AND b.option_value < %d";

		$wpdb->query( $wpdb->prepare(
			$sql,
			$wpdb->esc_like( $transient_prefix ) . '%',
			$wpdb->esc_like( $timeout_prefix ) . '%',
			$timeout_prefix,
			$length,
			$time
		) );
	}
}
