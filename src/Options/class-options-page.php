<?php
/**
 * Create the options page in wp-admin at settings > security.
 *
 * @package soter
 */

namespace Soter\Options;

use League\Plates\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class registers/renders everything on the plugin options page.
 */
class Options_Page {
	protected $options;

	protected $template;

	/**
	 * Class constructor.
	 *
	 * @param Template   $template Template object.
	 */
	public function __construct( Options_Manager $options, Engine $template ) {
		$this->options = $options;
		$this->template = $template;
	}

	/**
	 * Registers settings, sections and fields.
	 */
	public function admin_init() {
		add_settings_section(
			'soter_main',
			'Main Settings',
			[ $this, 'render_section_main' ],
			'soter'
		);

		add_settings_field(
			'soter_should_nag',
			'Notification Frequency',
			[ $this, 'render_should_nag' ],
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
			'soter_email_type',
			'Email Type',
			[ $this, 'render_email_type' ],
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
		// @todo
		$placeholder = get_bloginfo( 'admin_email' );
		$current = $this->options->email_address();
		$value = $placeholder === $current ? '' : $current;

		echo $this->template->render(
			'options/email-address',
			compact( 'placeholder', 'value' )
		);
	}

	/**
	 * Renders the html email field.
	 */
	public function render_email_type() {
		$type = $this->options->email_type();

		echo $this->template->render( 'options/email-type', [
			'html_checked' => 'html' === $type,
			'text_checked' => 'text' === $type,
		] );
	}

	/**
	 * Renders the ignored plugins field.
	 */
	public function render_ignored_plugins() {
		$plugins = get_plugins();
		$plugins = array_map( function( $key, $value ) {
			$parts = explode( DIRECTORY_SEPARATOR, $key );
			$slug = reset( $parts );

			return [ 'name' => $value['Name'], 'slug' => $slug ];
		}, array_keys( $plugins ), $plugins );

		echo $this->template->render( 'options/ignored-packages', [
			'ignored_packages' => $this->options->ignored_plugins(),
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

		echo $this->template->render( 'options/ignored-packages', [
			'ignored_packages' => $this->options->ignored_themes(),
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

		settings_fields( 'soter_group' );
		do_settings_sections( 'soter' );
		submit_button();

		echo '</form>';
		echo '</div>';
	}

	/**
	 * Renders the main page section.
	 */
	public function render_section_main() {
		// @todo Move this into a template file?
		echo '<p>The main settings for the Soter Security Checker plugin.</p>';
	}

	public function render_should_nag() {
		echo $this->template->render( 'options/should-nag', [
			'should_nag' => $this->options->should_nag(),
		] );
	}
}
