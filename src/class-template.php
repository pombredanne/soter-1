<?php

namespace SSNepenthe\Soter;

class Template {
	protected $locator_stack;

	public function __construct( Template_Locator_Interface $locator ) {
		$this->locator = $locator;
	}

	public function locator() {
		return $this->locator;
	}

	public function output( $name, $data = [] ) {
		if ( ! $template = $this->locator->locate( $this->candidates( $name ) ) ) {
			return;
		}

		global $comment,
			   $id,
			   $posts,
			   $post,
			   $user_ID,
			   $wp,
			   $wp_did_header,
			   $wp_query,
			   $wp_rewrite,
			   $wp_version,
			   $wpdb;

		if ( is_array( $wp_query->query_vars ) ) {
			extract( $wp_query->query_vars, EXTR_SKIP );
		}

		if ( isset( $s ) ) {
			$s = esc_attr( $s );
		}

		// Explicitly passed data may overwrite global data...
		extract( $data );

		include $template;
	}

	public function render( $name, $data = [] ) {
		ob_start();

		$this->output( $name, $data );

		$view = ob_get_contents();
		ob_end_clean();

		return $view;
	}

	protected function candidates( $template ) {
		if ( '.php' !== substr( $template, -4 ) ) {
			$template .= '.php';
		}

		$candidates = (array) $template;

		if ( 'templates/' !== substr( $template, 0, 6 ) ) {
			array_unshift( $candidates, 'templates/' . $template );
		}

		return $candidates;
	}
}
