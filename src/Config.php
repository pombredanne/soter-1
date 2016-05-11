<?php

namespace SSNepenthe\Soter;

class Config {
	const NAME = 'Soter Security Checker';
	const VERSION = '0.2.0';
	const URL = 'https://github.com/ssnepenthe/soter';

	protected static $instance = null;

	protected $addable = [ 'package.ignored' ];
	protected $defaults;
	protected $user = [];
	protected $json;
	protected $path;
	protected $settable = [ 'cache.directory', 'cache.ttl', 'http.useragent' ];

	protected function __construct( $path ) {
		if ( ! is_string( $path ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The path parameter is required to be string, was: %s',
				gettype( $path )
			) );
		}

		$this->defaults = [
			'cache' => [
				'directory' => sprintf( '%s/.cache', dirname( __DIR__ ) ),
				'ttl' => 60 * 60 * 12,
			],
			'http' => [
				'useragent' => sprintf(
					'%s | v%s | %s',
					self::NAME,
					self::VERSION,
					self::URL
				),
			],
			'package' => [
				'ignored' => [],
			]
		];

		$this->path = $path;
		$this->load();
	}

	private function __clone() { /* No diggity. */ }

	private function __wakeup() { /* No doubt. */ }

	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static( sprintf(
				'%s/config.json',
				dirname( __DIR__ )
			) );
		}

		return static::$instance;
	}

	public static function add( $key, $value ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if ( ! static::is_addable( $key ) ) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not an addable config property',
				$key
			) );
		}

		list( $namespace, $property ) = explode( '.', $key );

		if ( ! isset( static::instance()->user[ $namespace ][ $property ] ) ) {
			static::instance()->user[ $namespace ][ $property ] = [];
		}

		// @todo Some sort of validation.
		if ( ! in_array(
			$value,
			static::instance()->user[ $namespace ][ $property ]
		) ) {
			static::instance()->user[ $namespace ][ $property ][] = $value;
		}

		return static::get( $key );
	}

	public static function add_many( $key, array $values ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if ( ! static::is_addable( $key ) ) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not an addable config property',
				$key
			) );
		}

		foreach ( $values as $value ) {
			static::add( $key, $value );
		}

		return static::get( $key );
	}

	public static function get( $key ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if (
			! static::is_addable( $key ) &&
			! static::is_settable( $key )
		) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not a valid config property',
				$key
			) );
		}

		list( $namespace, $property ) = explode( '.', $key );

		if ( isset( static::instance()->user[ $namespace ][ $property ] ) ) {
			return static::instance()->user[ $namespace ][ $property ];
		}

		return static::instance()->defaults[ $namespace ][ $property ];
	}

	public static function is_addable( $key ) {
		return in_array( $key, static::instance()->addable );
	}

	public static function is_settable( $key ) {
		return in_array( $key, static::instance()->settable );
	}

	public static function remove( $key, $value ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if ( ! static::is_addable( $key ) ) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not an addable config property',
				$key
			) );
		}

		list( $namespace, $property ) = explode( '.', $key );

		$index = array_search(
			$value,
			static::instance()->user[ $namespace ][ $property ]
		);

		if ( false !== $index ) {
			unset(
				static::instance()->user[ $namespace ][ $property ][ $index ]
			);

			// Re-index the array.
			static::instance()->user[ $namespace ][ $property ] = array_values(
				static::instance()->user[ $namespace ][ $property ]
			);
		}

		return static::get( $key );
	}

	public static function reset( $key ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if (
			! static::is_addable( $key ) &&
			! static::is_settable( $key )
		) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not a valid config property',
				$key
			) );
		}

		list( $namespace, $property ) = explode( '.', $key );

		unset( static::instance()->user[ $namespace ][ $property ] );

		if ( empty( static::instance()->user[ $namespace ] ) ) {
			unset( static::instance()->user[ $namespace ] );
		}

		return static::get( $key );
	}

	public static function save() {
		if ( empty( static::instance()->user ) ) {
			static::instance()->maybe_delete_config();

			return;
		}

		static::instance()->json = json_encode(
			static::instance()->user,
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);

		file_put_contents( static::instance()->path, static::instance()->json );
	}

	public static function set( $key, $value ) {
		if ( ! is_string( $key ) ) {
			throw new \InvalidArgumentException( sprintf(
				'The key parameter is required to be string, was: %s',
				gettype( $key )
			) );
		}

		if ( ! static::is_settable( $key ) ) {
			throw new \OutOfBoundsException( sprintf(
				'%s is not a settable config property',
				$key
			) );
		}

		list( $namespace, $property ) = explode( '.', $key );

		// @todo Some sort of validation.
		static::instance()->user[ $namespace ][ $property ] = $value;

		return static::get( $key );
	}

	protected function load() {
		if ( ! is_file( $this->path ) || ! is_readable( $this->path ) ) {
			return;
		}

		$this->json = file_get_contents( $this->path );

		if ( ! $this->json ) {
			throw new \RuntimeException( sprintf(
				'Unable to read file at %s',
				$this->path
			) );
		}

		$object = json_decode( $this->json, true );

		if ( null === $object || JSON_ERROR_NONE !== json_last_error() ) {
			throw new \RuntimeException( sprintf(
				'The file at %s does not appear to contain valid JSON.',
				$this->path
			) );
		}

		foreach ( array_merge( $this->addable, $this->settable ) as $key ) {
			list ( $namespace, $property ) = explode( '.', $key );

			if ( isset( $object[ $namespace ][ $property ] ) ) {
				$this->user[ $namespace ][ $property ] =
					$object[ $namespace ][ $property ];
			}
		}
	}

	protected function maybe_delete_config() {
		if ( is_file( $this->path ) ) {
			unlink( $this->path );
		}
	}
}
