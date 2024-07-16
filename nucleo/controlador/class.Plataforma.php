<?php

namespace NUCLEO;

class Plataforma {
	function __construct() {
	}

	public function getPhpVersion() {
		return phpversion();
	}

	public function getIntSize() {
		return PHP_INT_SIZE;
	}

	public function getOS($tcMode="s") {
		return php_uname($tcMode);
	}

}
