<?php
/**
 * Create the options page in wp-admin at settings > security.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Options;

use SSNepenthe\Soter\Views\Template;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class registers/renders everything on the plugin options page.
 */
class Options_Page {
	/**
	 * Settings object.
	 */
	protected $settings;

	protected $template;

	/**
	 * Constructor.
	 */
	public function __construct( Map_Option $settings, Template $template ) {
		$this->settings = $settings;
		$this->template = $template;
	}

	/**
	 * Hooks the class in to WordPress.
	 */
	public function init() {
		add_action( 'admin_init', [ $this, 'admin_init' ] );
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );
	}

	/**
	 * Registers settings, sections and fields.
	 */
	public function admin_init() {
		register_setting(
			'soter_settings_group',
			'soter_settings',
			[ $this, 'sanitize' ]
		);

		add_settings_section(
			'soter_main',
			'Main Settings',
			[ $this, 'render_section_main' ],
			'soter'
		);

		add_settings_field(
			'soter_enable_email',
			'Enable Email',
			[ $this, 'render_enable_email' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_email_address',
			'Email Address',
			[ $this, 'render_email_address' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_html_email',
			'Html Email',
			[ $this, 'render_html_email' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_ignored_plugins',
			'Ignored Plugins',
			[ $this, 'render_ignored_plugins' ],
			'soter',
			'soter_main'
		);

		add_settings_field(
			'soter_ignored_themes',
			'Ignored Themes',
			[ $this, 'render_ignored_themes' ],
			'soter',
			'soter_main'
		);
	}

	/**
	 * Registers the options page.
	 */
	public function admin_menu() {
		add_options_page(
			'Soter Configuration',
			'Soter',
			'manage_options',
			'soter',
			[ $this, 'render_page_soter' ]
		);
	}

	/**
	 * Renders the email address field.
	 */
	public function render_email_address() {
		$current = $this->settings->get( 'email_address', '' );

		$this->template->output( 'options/email-address', [
			'current' => $current,
			'default' => get_bloginfo( 'admin_email' ),
		] );
	}

	public function render_html_email() {
		$enabled = $this->settings->get( 'html_email', false );

		$this->template->output( 'options/html-email', compact( 'enabled' ) );
	}

	/**
	 * Renders the enable email field.
	 */
	public function render_enable_email() {
		$enabled = $this->settings->get( 'enable_email', false );

		$this->template->output( 'options/enable-email', compact( 'enabled' ) );
	}

	/**
	 * Renders the ignored plugins field.
	 */
	public function render_ignored_plugins() {
		$plugins = get_plugins();
		$plugins = array_map( function( $key, $value ) {
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $key );

			return [ 'name' => $value['Name'], 'slug' => $slug ];
		}, array_keys( $plugins ), $plugins );

		$ignored = $this->settings->get( 'ignored_plugins', [] );

		$this->template->output( 'options/ignored-packages', [
			'ignored_packages' => $ignored,
			'packages' => $plugins,
			'type' => 'plugins',
		] );
	}

	/**
	 * Renders the ignored themes field.
	 */
	public function render_ignored_themes() {
		$themes = array_map( function( $value ) {
			return [
				'name' => $value->display( 'Name' ),
				'slug' => $value->get_stylesheet(),
			];
		}, wp_get_themes() );

		$ignored = $this->settings->get( 'ignored_themes', [] );

		$this->template->output( 'options/ignored-packages', [
			'ignored_packages' => $ignored,
			'packages' => $themes,
			'type' => 'themes',
		] );
	}

	/**
	 * Renders the full settings page.
	 */
	public function render_page_soter() {
		// @todo Move this to a template file?
		echo '<div class="wrap">';
		echo '<h1>' . esc_html( get_admin_page_title() ) . '</h1>';
		echo '<form action="options.php" method="POST">';

		settings_fields( 'soter_settings_group' );
		do_settings_sections( 'soter' );
		submit_button();

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Renders the main page section.
	 */
	public function render_section_main() {
		echo '<p>The main settings for the Soter Security Checker plugin.</p>';
	}

	public function sanitize( array $values ) {
		$sanitized = [];

		// Array of installed plugin slugs.
		$valid_plugins = array_map( function( $value ) {
			// Does WP use DIRECTORY_SEPARATOR or is it always /?
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $value );

			return $slug;
		}, array_keys( get_plugins() ) );

		// Array of installed theme slugs.
		$valid_themes = array_values( wp_list_pluck(
			wp_get_themes(),
			'stylesheet'
		) );

		$sanitized['enable_email'] = filter_var(
			isset( $values['enable_email'] ) ? $values['enable_email'] : false,
			FILTER_VALIDATE_BOOLEAN
		);
		$sanitized['email_address'] = sanitize_email(
			isset( $values['email_address'] ) ? $values['email_address'] : ''
		);
		$sanitized['html_email'] = filter_var(
			isset( $values['html_email'] ) ? $values['html_email'] : false,
			FILTER_VALIDATE_BOOLEAN
		);
		$sanitized['ignored_plugins'] = array_values( array_intersect(
			$valid_plugins,
			isset( $values['ignored_plugins'] ) ? $values['ignored_plugins'] : []
		) );
		$sanitized['ignored_themes'] = array_values( array_intersect(
			$valid_themes,
			isset( $values['ignored_themes'] ) ? $values['ignored_themes'] : []
		) );
		// Version would normally be unset since there is no input for it so instead
		// we will just grab the previous value and use it as our sanitiezd value.
		$sanitized['version'] = $this->settings->get( 'version', false );

		return $sanitized;
	}
}
