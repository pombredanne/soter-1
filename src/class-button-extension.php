<?php
/**
 * Button extension for Plates class.
 *
 * @package soter
 */

namespace Soter;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

/**
 * Defines the button extension class.
 */
class Button_Extension implements ExtensionInterface {
	/**
	 * Plates engine instance.
	 *
	 * @var Engine
	 */
	protected $engine;

	/**
	 * Register this extension with Plates.
	 *
	 * @param  Engine $engine Plates engine instance.
	 *
	 * @return void
	 */
	public function register( Engine $engine ) {
		$this->engine = $engine;

		$this->engine->registerFunction( 'button', [ $this, 'get_instance' ] );
	}

	/**
	 * Instance getter.
	 *
	 * @return $this
	 */
	public function get_instance() {
		return $this;
	}

	/**
	 * Render a blue button.
	 *
	 * @param  string $text Button text.
	 * @param  string $url  Link URL.
	 *
	 * @return string
	 */
	public function blue( $text, $url ) {
		return $this->render( $text, $url, 'blue' );
	}

	/**
	 * Render the button fallback text.
	 *
	 * @param  string $url Button URL.
	 *
	 * @return string
	 */
	public function fallback( $url ) {
		return $this->engine->render( 'emails/components/button-fallback.php', compact( 'url' ) );
	}

	/**
	 * Render a green button.
	 *
	 * @param  string $text Button text.
	 * @param  string $url  Link URL.
	 *
	 * @return string
	 */
	public function green( $text, $url ) {
		return $this->render( $text, $url, 'green' );
	}

	/**
	 * Render a red button.
	 *
	 * @param  string $text Button text.
	 * @param  string $url  Link URL.
	 *
	 * @return string
	 */
	public function red( $text, $url ) {
		return $this->render( $text, $url, 'red' );
	}

	/**
	 * Render a button.
	 *
	 * @param  string $text  Button text.
	 * @param  string $url   Link URL.
	 * @param  string $color Button color - one of red, green or blue.
	 *
	 * @return string
	 */
	protected function render( $text, $url, $color = 'blue' ) {
		if ( ! in_array( $color, [ 'blue', 'green', 'red' ], true ) ) {
			$color = 'blue';
		}

		return $this->engine->render(
			'emails/components/button.php',
			compact( 'text', 'url', 'color' )
		);
	}
}
