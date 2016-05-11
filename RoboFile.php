<?php

class RoboFile extends \Robo\Tasks {
    /**
	 * Update package version.
	 *
	 * @param string $version The new plugin version.
	 */
	public function versionBump( $version ) {
		$this->taskReplaceInFile( sprintf( '%s/src/Config.php', __DIR__ ) )
			->regex( '/const VERSION.*$/m' )
			->to( sprintf( 'const VERSION = \'%s\';', $version ) )
			->run();
	}
}
