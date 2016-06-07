<?php

class RoboFile extends \Robo\Tasks {
    /**
	 * Update plugin version.
	 *
	 * @param string $version The new plugin version.
	 */
	public function versionBump( $version ) {
		$this->taskReplaceInFile( sprintf( '%s/soter.php', __DIR__ ) )
			->regex( '/Version:.*$/m' )
			->to( sprintf( 'Version: %s', $version ) )
			->run();

		$this->taskReplaceInFile( sprintf( '%s/src/HTTP/WPClient.php', __DIR__ ) )
			->regex( '/\$version =.*$/m' )
			->to( sprintf( '$version = \'%s\';', $version ) )
			->run();
	}
}
