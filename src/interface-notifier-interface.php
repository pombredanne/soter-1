<?php

namespace SSNepenthe\Soter;

interface Notifier_Interface {
	public function notify();
	public function set_data( array $data );
}
