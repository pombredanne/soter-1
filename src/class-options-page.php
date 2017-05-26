<?php
/**
 * Create the options page in wp-admin at settings > security.
 *
 * @package soter
 */

namespace Soter;

use League\Plates\Engine;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class registers/renders everything on the plugin options page.
 */
class Options_Page {
	/**
	 * Options manager instance.
	 *
	 * @var Options_Manager
	 */
	protected $options;

	/**
	 * Template engine instance.
	 *
	 * @var Engine
	 */
	protected $template;

	/**
	 * Class constructor.
	 *
	 * @param Options_Manager $options  Options manager instance.
	 * @param Engine          $template Template engine instance.
	 */
	public function __construct( Options_Manager $options, Engine $template ) {
		$this->options = $options;
		$this->template = $template;
	}

	/**
	 * Registers settings, sections and fields.
	 *
	 * @return void
	 */
	public function admin_init() {
		add_settings_section(
			'soter_general',
			'General Settings',
			[ $this, 'render_section_general' ],
			'soter'
		);

		add_settings_section(
			'soter_email',
			'Email Settings',
			[ $this, 'render_section_email' ],
			'soter'
		);

		add_settings_section(
			'soter_slack',
			'Slack Settings',
			[ $this, 'render_section_slack' ],
			'soter'
		);

		add_settings_field(
			'soter_should_nag',
			'Notification Frequency',
			[ $this, 'render_should_nag' ],
			'soter',
			'soter_general'
		);

		add_settings_field(
			'soter_ignored_plugins',
			'Ignored Plugins',
			[ $this, 'render_ignored_plugins' ],
			'soter',
			'soter_general'
		);

		add_settings_field(
			'soter_ignored_themes',
			'Ignored Themes',
			[ $this, 'render_ignored_themes' ],
			'soter',
			'soter_general'
		);

		add_settings_field(
			'soter_email_enabled',
			'Send Email Notifications',
			[ $this, 'render_email_enabled' ],
			'soter',
			'soter_email'
		);

		add_settings_field(
			'soter_email_address',
			'Email Address',
			[ $this, 'render_email_address' ],
			'soter',
			'soter_email'
		);

		add_settings_field(
			'soter_email_type',
			'Email Type',
			[ $this, 'render_email_type' ],
			'soter',
			'soter_email'
		);

		add_settings_field(
			'soter_slack_enabled',
			'Send Slack Notifications',
			[ $this, 'render_slack_enabled' ],
			'soter',
			'soter_slack'
		);

		add_settings_field(
			'soter_slack_url',
			'WebHook URL',
			[ $this, 'render_slack_url' ],
			'soter',
			'soter_slack'
		);
	}

	/**
	 * Registers the options page.
	 *
	 * @return void
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
	 *
	 * @return void
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

	public function render_email_enabled() {
		echo $this->template->render( 'options/boolean', [
			'checked' => $this->options->email_enabled(),
			'label' => 'Enable email notifications',
			'setting' => 'soter_email_enabled',
		] );
	}

	/**
	 * Renders the html email field.
	 *
	 * @return void
	 */
	public function render_email_type() {
		echo $this->template->render( 'options/email-type', [
			'type' => $this->options->email_type(),
		] );
	}

	/**
	 * Renders the ignored plugins field.
	 *
	 * @return void
	 */
	public function render_ignored_plugins() {
		$plugins = get_plugins();
		$plugins = array_map( function( $key, $value ) {
			$parts = explode( DIRECTORY_SEPARATOR, $key );
			$slug = reset( $parts );

			return [
				'name' => $value['Name'],
				'slug' => $slug,
			];
		}, array_keys( $plugins ), $plugins );

		echo $this->template->render( 'options/ignored-packages', [
			'ignored_packages' => $this->options->ignored_plugins(),
			'packages' => $plugins,
			'type' => 'plugins',
		] );
	}

	/**
	 * Renders the ignored themes field.
	 *
	 * @return void
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
	 *
	 * @return void
	 */
	public function render_page_soter() {
		echo $this->template->render( 'options/page', [
			'group' => 'soter_group',
			'page' => 'soter',
			'title' => get_admin_page_title(),
		] );
	}

	/**
	 * Renders the general page section.
	 *
	 * @return void
	 */
	public function render_section_general() {
		echo $this->template->render( 'options/general-section' );
	}

	/**
	 * Renders the email page section.
	 *
	 * @return void
	 */
	public function render_section_email() {
		echo $this->template->render( 'options/email-section' );
	}

	/**
	 * Renders the Slack page section.
	 *
	 * @return void
	 */
	public function render_section_slack() {
		echo $this->template->render( 'options/slack-section' );
	}

	/**
	 * Render the nag/notification frequency setting.
	 *
	 * @return void
	 */
	public function render_should_nag() {
		echo $this->template->render( 'options/should-nag', [
			'should_nag' => $this->options->should_nag(),
		] );
	}

	public function render_slack_enabled() {
		echo $this->template->render( 'options/boolean', [
			'checked' => $this->options->slack_enabled(),
			'label' => 'Enable slack notifications',
			'setting' => 'soter_slack_enabled',
		] );
	}

	/**
	 * Render the Slack WebHook URL setting.
	 *
	 * @return void
	 */
	public function render_slack_url() {
		echo $this->template->render( 'options/slack-url', [
			'placeholder' => 'Slack WebHook URL',
			'value' => $this->options->slack_url(),
		] );
	}
}
