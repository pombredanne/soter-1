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

	public function __get( $key ) {
		$key = strtolower( $key );
		$getter = "_get_{$key}";

		if ( method_exists( $this, $getter ) ) {
			return $this->{$getter}();
		}

		if ( isset( $this->types[ $key ] ) ) {
			$getter = "_get_{$this->types[ $key ]}_value";

			return $this->{$getter}( $key );
		}

		return null;
	}

	protected function _get_string_value( $key ) {
		if (
			! isset( $this->types[ $key ] )
			|| 'string' !== $this->types[ $key ]
		) {
			return null;
		}

		$default = array_key_exists( $key, $this->defaults )
			? $this->defaults[ $key ]
			: '';

		return trim( (string) $this->store->get( $key, $default ) );
	}

	protected function _get_boolean_value( $key ) {
		if (
			! isset( $this->types[ $key ] )
			|| 'boolean' !== $this->types[ $key ]
		) {
			return null;
		}

		$default = array_key_exists( $key, $this->defaults )
			? $this->defaults[ $key ]
			: true;

		// Values are stored as yes/no strings.
		return filter_var(
			$this->store->get( $key, $default ),
			FILTER_VALIDATE_BOOLEAN
		);
	}

	protected function _get_array_value( $key ) {
		if (
			! isset( $this->types[ $key ] )
			|| 'array' !== $this->types[ $key ]
		) {
			return null;
		}

		$default = array_key_exists( $key, $this->defaults )
			? $this->defaults[ $key ]
			: [];

		// @todo Could be taken a step further to array map strval?
		return (array) $this->store->get( $key, $default );
	}

	protected $types = [
		'email_enabled' => 'boolean',
		'email_type' => 'string',
		'ignored_plugins' => 'array',
		'ignored_themes' => 'array',
		'installed_version' => 'string',
		'last_scan_hash' => 'string',
		'should_nag' => 'boolean',
		'slack_enabled' => 'boolean',
		'slack_url' => 'string',
	];
	protected $defaults = [
		'email_type' => 'text',
		'slack_enabled' => false,
	];

	/**
	 * Get the currently configured email address.
	 *
	 * @return string
	 */
	protected function _get_email_address() {
		$current = $this->store->get( 'email_address' );

		// Might be null or an empty string...
		if ( ! $current ) {
			$current = get_bloginfo( 'admin_email' );
		}

		return trim( (string) $current );
	}

	protected function _get_ignored_packages() {
		return array_merge( $this->ignored_plugins, $this->ignored_themes );
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

		register_setting( 'soter_group', 'soter_email_enabled', [
			'default' => 'yes',
			'sanitize_callback' => [ $this, 'sanitize_boolean' ],
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
			'show_in_rest' => false,
		] );

		register_setting( 'soter_group', 'soter_ignored_themes', [
			'default' => [],
			'sanitize_callback' => [ $this, 'sanitize_ignored_themes' ],
			'show_in_rest' => false,
		] );

		register_setting( 'soter_group', 'soter_should_nag', [
			'default' => 'yes',
			'sanitize_callback' => [ $this, 'sanitize_boolean' ],
			'show_in_rest' => true,
		] );

		register_setting( 'soter_group', 'soter_slack_enabled', [
			'default' => 'no',
			'sanitize_callback' => [ $this, 'sanitize_boolean' ],
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

			return $this->email_address;
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

			return $this->email_type;
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

			return $this->ignored_plugins;
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

			return $this->ignored_themes;
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

			return $this->installed_version;
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
	public function sanitize_boolean( $value ) {
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

			return $this->slack_url;
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

			return $this->slack_url;
		}

		return $url;
	}
}
