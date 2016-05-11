<?php

namespace SSNepenthe\Soter\Contracts;

interface Http {
	public function get( $endpoint );
	public function set_url_root( $url_root );
	public function set_user_agent( $user_agent );
}
