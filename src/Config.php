<?php
/**
 * Package config. object.
 *
 * @package soter
 */

namespace SSNepenthe\Soter;

/**
 * This class provides easy access to the package config.
 */
class Config {
	const NAME = 'Soter Security Checker';
	const VERSION = '0.2.0';
	const URL = 'https://github.com/ssnepenthe/soter';

	/**
	 * Singleton instance.
	 *
	 * @var null|Config
	 */
	protected static $instance = null;

	/**
	 * List of properties which can be modified with the add method.
	 *
	 * @var array
	 */
	protected $addable = [ 'package.ignored' ];

	/**
	 * Default package config.
	 *
	 * @var array
	 */
	protected $defaults;

	/**
	 * User defined config overrides.
	 *
	 * @var array
	 */
	protected $user = [];

	/**
	 * JSON encoded user config.
	 *
	 * @var string
	 */
	protected $json;

	/**
	 * Path to the user defined config file.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * List of properties which can be modified with the set method.
	 *
	 * @var array
	 */
	protected $settable = [ 'cache.directory', 'cache.ttl', 'http.useragent' ];

	/**
	 * Config constructor.
	 *
	 * @param string $path Path to user config file.
	 *
	 * @throws \InvalidArgumentException When $path is not a string.
	 */
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
			],
		];

		$this->path = $path;
		$this->load();
	}

	/**
	 * Private __clone method to prevent cloning the object.
	 */
	private function __clone() {
		// No diggity.
	}

	/**
	 * Private __wakeup method to prevent unserializing the object.
	 */
	private function __wakeup() {
		// No doubt.
	}

	/**
	 * Getter for singleton instance.
	 *
	 * @return Config
	 */
	public static function instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static( sprintf(
				'%s/config.json',
				dirname( __DIR__ )
			) );
		}

		return static::$instance;
	}

	/**
	 * Add an entry to an addable property.
	 *
	 * @param string $key   Config property key.
	 * @param mixed  $value Config value.
	 *
	 * @return array The new value of the specified property.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not an addable property.
	 */
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

		if ( ! in_array(
			$value,
			static::instance()->user[ $namespace ][ $property ],
			true
		) ) {
			static::instance()->user[ $namespace ][ $property ][] =
				static::instance()->ensure_correct_type( $key, $value );
		}

		return static::get( $key );
	}

	/**
	 * Add multiple entries to an addable property.
	 *
	 * @param string $key    Config property key.
	 * @param array  $values Array of values to add.
	 *
	 * @return array The new value of the specified property.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not an addable property.
	 */
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

	/**
	 * Config property getter.
	 *
	 * @param  string $key Config property key.
	 *
	 * @return mixed       Specified config value.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not a valid config property.
	 */
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

	/**
	 * Determine if the config property is an addable property.
	 *
	 * @param  string $key Config property key.
	 *
	 * @return boolean
	 */
	public static function is_addable( $key ) {
		return in_array( $key, static::instance()->addable, true );
	}

	/**
	 * Determine if the config property is a settable property.
	 *
	 * @param  string $key Config property key.
	 *
	 * @return boolean
	 */
	public static function is_settable( $key ) {
		return in_array( $key, static::instance()->settable, true );
	}

	/**
	 * Removes a single entry from the user config.
	 *
	 * @param  string $key   Config property key.
	 * @param  mixed  $value Value to set on the property.
	 *
	 * @return mixed         The config value.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not an addable property.
	 */
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

	/**
	 * Reset/unset a user config value.
	 *
	 * @param  string $key Config value property.
	 *
	 * @return mixed       Default value of the config property.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not a valid config property.
	 */
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

	/**
	 * Save the user config to a file.
	 */
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

	/**
	 * Set a value to the user config.
	 *
	 * @param string $key   Config property key.
	 * @param mixed  $value The value to set for the config property.
	 *
	 * @return mixed The new value of the given property.
	 *
	 * @throws \InvalidArgumentException When $key is not a string.
	 * @throws \OutOfBoundsException When $key is not a settable property.
	 */
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

		static::instance()->user[ $namespace ][ $property ] =
			static::instance()->ensure_correct_type( $key, $value );

		return static::get( $key );
	}

	/**
	 * Load the config file.
	 *
	 * @throws \RuntimeException When file can't be read or JSON decoded.
	 */
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
					$this->ensure_correct_type(
						$key,
						$object[ $namespace ][ $property ]
					);
			}
		}
	}

	/**
	 * Deletes the config file.
	 */
	protected function maybe_delete_config() {
		if ( is_file( $this->path ) ) {
			unlink( $this->path );
		}
	}

	/**
	 * Ensures cache.ttl is an integer.
	 *
	 * @param  string $key   Config property key.
	 * @param  mixed  $value Config value.
	 *
	 * @return mixed
	 */
	protected function ensure_correct_type( $key, $value ) {
		if ( 'cache.ttl' === $key ) {
			$value = abs( intval( $value ) );
		}

		return $value;
	}
}
