<?php
/**
 * Options_Manager class.
 *
 * @package soter
 */

namespace Soter;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Defines the options manager class.
 */
class Options_Manager {
	/**
	 * Options store instance.
	 *
	 * @var Options_Store
	 */
	protected $store;

	/**
	 * Class constructor.
	 *
	 * @param Options_Store $store Options store instance.
	 */
	public function __construct( Options_Store $store ) {
		$this->store = $store;
	}

	/**
	 * Get the currently configured email address.
	 *
	 * @return string
	 */
	public function email_address() {
		$current = $this->store->get( 'email_address' );

		// Might be null or an empty string...
		if ( ! $current ) {
			$current = get_bloginfo( 'admin_email' );
		}

		return trim( (string) $current );
	}

	/**
	 * Get the currently configured email type.
	 *
	 * @return string
	 */
	public function email_type() {
		return trim( (string) $this->store->get( 'email_type', 'text' ) );
	}

	/**
	 * Get the options store instance.
	 *
	 * @return Options_Store
	 */
	public function get_store() {
		return $this->store;
	}

	/**
	 * Get the list of ignored package slugs.
	 *
	 * @return string[]
	 */
	public function ignored_packages() {
		return array_merge( $this->ignored_plugins(), $this->ignored_themes() );
	}

	/**
	 * Get the list of ignored plugins.
	 *
	 * @return string[]
	 */
	public function ignored_plugins() {
		return (array) $this->store->get( 'ignored_plugins', [] );
	}

	/**
	 * Get the list of ignored themes.
	 *
	 * @return string[]
	 */
	public function ignored_themes() {
		return (array) $this->store->get( 'ignored_themes', [] );
	}

	/**
	 * Get the currently installed plugin version.
	 *
	 * @return string
	 */
	public function installed_version() {
		return trim( (string) $this->store->get( 'installed_version', '' ) );
	}

	/**
	 * Get the hash of the last scan.
	 *
	 * @return string
	 */
	public function last_scan_hash() {
		return trim( (string) $this->store->get( 'last_scan_hash', '' ) );
	}

	/**
	 * Register all plugin settings.
	 *
	 * @return void
	 */
	public function register_settings() {
		register_setting( 'soter_backend', 'soter_installed_version', [
			'default' => '',
			'sanitize_callback' => [ $this, 'sanitize_installed_version' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_backend', 'soter_last_scan_hash', [
			'default' => '',
			'sanitize_callback' => 'strval',
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_email_address', [
			'default' => get_bloginfo( 'admin_email' ),
			'sanitize_callback' => [ $this, 'sanitize_email_address' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_email_type', [
			'default' => 'text',
			'sanitize_callback' => [ $this, 'sanitize_email_type' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_ignored_plugins', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_ignored_plugins' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_ignored_themes', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_ignored_themes' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_should_nag', [
			'default' => 'yes',
			'sanitize_callback' => [ $this, 'sanitize_should_nag' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_slack_url', [
			'default' => '',
			'sanitize_callback' => [ $this, 'sanitize_slack_url' ],
			'show_in_rest' => true,
		] );
	}

	/**
	 * Sanitize the email address setting.
	 *
	 * @param  string $value Email address submitted by user.
	 *
	 * @return string
	 */
	public function sanitize_email_address( $value ) {
		$value = trim( (string) $value );

		// Allow user to unset by providing an empty string.
		if ( ! $value ) {
			return '';
		}

		$new_value = sanitize_email( $value );

		if ( ! $new_value ) {
			add_settings_error(
				'soter_email_address',
				'invalid_soter_email_address',
				sprintf(
					'The email address provided [%s] does not appear to be valid.',
					esc_html( $value )
				)
			);

			return $this->email_address();
		}

		return $new_value;
	}

	/**
	 * Sanitize the email type setting.
	 *
	 * @param  string $value The value provided by the user.
	 *
	 * @return string
	 */
	public function sanitize_email_type( $value ) {
		$value = trim( (string) $value );
		$new_value = '';

		if ( in_array( $value, [ 'html', 'text' ], true ) ) {
			$new_value = $value;
		}

		if ( ! $new_value ) {
			add_settings_error(
				'soter_email_type',
				'invalid_soter_email_type',
				sprintf(
					'Email type must be one of "text" or "html" - "%s" given.',
					esc_html( $value )
				)
			);

			return $this->email_type();
		}

		return $new_value;
	}

	/**
	 * Sanitize the ignored plugins list.
	 *
	 * @param  string[] $value The value provided by the user.
	 *
	 * @return string[]
	 */
	public function sanitize_ignored_plugins( $value ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$value = (array) $value;
		$valid_slugs = array_map( function( $file ) {
			if ( false === strpos( $file, '/' ) ) {
				$slug = basename( $file, '.php' );
			} else {
				$slug = dirname( $file );
			}

			return $slug;
		}, array_keys( get_plugins() ) );
		$invalid_slugs = array_diff( $value, $valid_slugs );

		if ( ! empty( $invalid_slugs ) ) {
			add_settings_error(
				'soter_ignored_plugins',
				'invalid_soter_ignored_plugins',
				sprintf(
					'Only currently installed plugins can be ignored. The following invalid plugin slugs were provided: %s',
					implode( ', ', array_map( 'esc_html', $invalid_slugs ) )
				)
			);

			return $this->ignored_plugins();
		}

		return $value;
	}

	/**
	 * Sanitize the ignored themes setting.
	 *
	 * @param  string[] $value The value provided by the user.
	 *
	 * @return string[]
	 */
	public function sanitize_ignored_themes( $value ) {
		$value = (array) $value;
		$valid_slugs = array_values( wp_list_pluck(
			wp_get_themes(),
			'stylesheet'
		) );
		$invalid_slugs = array_diff( $value, $valid_slugs );

		if ( ! empty( $invalid_slugs ) ) {
			add_settings_error(
				'soter_ignored_plugins',
				'invalid_soter_ignored_plugins',
				sprintf(
					'Only currently installed themes can be ignored. The following invalid theme slugs were provided: %s',
					implode( ', ', array_map( 'esc_html', $invalid_slugs ) )
				)
			);

			return $this->ignored_themes();
		}

		return $value;
	}

	/**
	 * Sanitize the installed version setting.
	 *
	 * @param  string $value The value provided by the user.
	 *
	 * @return string
	 */
	public function sanitize_installed_version( $value ) {
		$value = trim( (string) $value );

		if ( (bool) preg_match( '/[^\d\.]/', $value ) ) {
			add_settings_error(
				'soter_installed_version',
				'invalid_soter_installed_version',
				'Installed version can only contain the following characters: 0123456789.'
			);

			return $this->installed_version();
		}

		return $value;
	}

	/**
	 * Sanitize the nag setting.
	 *
	 * @param  string $value The value provided by the user.
	 *
	 * @return string
	 */
	public function sanitize_should_nag( $value ) {
		return filter_var( $value, FILTER_VALIDATE_BOOLEAN ) ? 'yes' : 'no';
	}

	/**
	 * Sanitize the Slack WebHook URL setting.
	 *
	 * @param  string $value The value provided by the user.
	 *
	 * @return string
	 */
	public function sanitize_slack_url( $value ) {
		$value = trim( (string) $value );

		if ( ! $value ) {
			return '';
		}

		$url = filter_var( $value, FILTER_VALIDATE_URL );

		if ( ! $url ) {
			add_settings_error(
				'soter_slack_url',
				'invalid_soter_slack_url',
				'Provided Slack webhook URL does not appear to be a valid URL.'
			);

			return $this->slack_url();
		}

		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( 'hooks.slack.com' !== $host ) {
			add_settings_error(
				'soter_slack_url',
				'invalid_soter_slack_url',
				sprintf(
					'Slack webhook URL host must be hooks.slack.com - given %s.',
					$host
				)
			);

			return $this->slack_url();
		}

		return $url;
	}

	/**
	 * Set the email address setting.
	 *
	 * @param string $value An email address.
	 *
	 * @return boolean
	 */
	public function set_email_address( $value ) {
		return $this->store->set( 'email_address', $value );
	}

	/**
	 * Set the email type setting.
	 *
	 * @param string $value Eamil type - 'text' or 'html'.
	 *
	 * @return boolean
	 */
	public function set_email_type( $value ) {
		return $this->store->set( 'email_type', $value );
	}

	/**
	 * Set the ignored plugins list.
	 *
	 * @param string[] $value List of pluign slugs.
	 *
	 * @return boolean
	 */
	public function set_ignored_plugins( $value ) {
		return $this->store->set( 'ignored_plugins', $value );
	}

	/**
	 * Set the ignored themes list.
	 *
	 * @param string[] $value List of theme slugs.
	 *
	 * @return boolean
	 */
	public function set_ignored_themes( $value ) {
		return $this->store->set( 'ignored_themes', $value );
	}

	/**
	 * Set the installed version.
	 *
	 * @param string $value Version string.
	 *
	 * @return boolean
	 */
	public function set_installed_version( $value ) {
		return $this->store->set( 'installed_version', $value );
	}

	/**
	 * Set the last scan hash setting.
	 *
	 * @param string $value Unique hash representing the results of a scan.
	 *
	 * @return boolean
	 */
	public function set_last_scan_hash( $value ) {
		return $this->store->set( 'last_scan_hash', $value );
	}

	/**
	 * Set the nag setting.
	 *
	 * @param string $value Should nagging notification be enabled - 'yes' or 'no'.
	 *
	 * @return boolean
	 */
	public function set_should_nag( $value ) {
		return $this->store->set( 'should_nag', $value );
	}

	/**
	 * Set the Slack WebHook URL settings.
	 *
	 * @param string $value The WebHook URL.
	 */
	public function set_slack_url( $value ) {
		return $this->store->set( 'slack_url', $value );
	}

	/**
	 * Get the nag setting.
	 *
	 * @return boolean
	 */
	public function should_nag() {
		// Option is stored as yes/no.
		return filter_var(
			$this->store->get( 'should_nag', true ),
			FILTER_VALIDATE_BOOLEAN
		);
	}

	/**
	 * Get the Slack WebHook URL setting.
	 *
	 * @return string
	 */
	public function slack_url() {
		return trim( (string) $this->store->get( 'slack_url', '' ) );
	}
}
