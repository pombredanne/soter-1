<?php

namespace Soter;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class Button_Extension implements ExtensionInterface {
	protected $engine;

	public function register(Engine $engine) {
		$this->engine = $engine;

		$this->engine->registerFunction( 'button', [ $this, 'get_instance' ] );
	}

	public function get_instance() {
		return $this;
	}

	public function blue( $text, $url ) {
		return $this->render( $text, $url, 'blue' );
	}

	public function fallback( $url ) {
		return $this->engine->render( 'emails/components/button-fallback.php', compact( 'url' ) );
	}

	public function green( $text, $url ) {
		return $this->render( $text, $url, 'green' );
	}

	public function red( $text, $url ) {
		return $this->render( $text, $url, 'red' );
	}

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
