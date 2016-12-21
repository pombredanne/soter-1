<?php
/**
 * Create the options page in wp-admin at settings > security.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Options;

use SSNepenthe\Soter\Template;

/**
 * This class registers/renders everything on the plugin options page.
 */
class Page {
	/**
	 * Settings object.
	 *
	 * @var SSNepenthe\Soter\Options\Settings
	 */
	protected $settings;

	protected $template;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings Plugin settings object.
	 */
	public function __construct( Settings $settings, Template $template ) {
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
			[ $this->settings, 'sanitize' ]
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
			'Security',
			'manage_options',
			'soter',
			[ $this, 'render_page_soter' ]
		);
	}

	/**
	 * Renders the email address field.
	 */
	public function render_email_address() {
		$this->template->output( 'options/email-address', [
			'current' => $this->settings->email_address,
			'default' => get_bloginfo( 'admin_email' ),
		] );
	}

	/**
	 * Renders the enable email field.
	 */
	public function render_enable_email() {
		$this->template->output( 'options/enable-email', [
			'enabled' => $this->settings->enable_email,
		] );
	}

	/**
	 * Renders the ignored plugins field.
	 */
	public function render_ignored_plugins() {
		$plugins = get_plugins();
		$plugins = array_map( function( $k, $v ) {
			// @todo Does WordPress store file with DIRECTORY_SEPARATOR or always /?
			list( $slug, $basename ) = explode( DIRECTORY_SEPARATOR, $k );

			return [ 'name' => $v['Name'], 'slug' => $slug ];
		}, array_keys( $plugins ), $plugins );

		$this->template->output( 'options/ignored-packages', [
			'ignored_packages' => $this->settings->ignored_plugins,
			'packages' => $plugins,
			'type' => 'plugins',
		] );
	}

	/**
	 * Renders the ignored themes field.
	 */
	public function render_ignored_themes() {
		$themes = array_map( function( $v ) {
			return [
				'name' => $v->display( 'Name' ),
				'slug' => $v->get_stylesheet(),
			];
		}, wp_get_themes() );

		$this->template->output( 'options/ignored-packages', [
			'ignored_packages' => $this->settings->ignored_themes,
			'packages' => $themes,
			'type' => 'themes',
		] );
	}

	/**
	 * Renders the full settigns page.
	 */
	public function render_page_soter() {
		echo '<div class="wrap">';
		echo '<h1>' . get_admin_page_title() . '</h1>';
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
}
