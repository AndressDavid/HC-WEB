<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class InstitutosPrestadoresSalud {
	private $aIPSs = [];

    public function __construct() {
		$this->aIPSs = $this->cargar();
    }

	private function cargar() {
		$laIPSs = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(CODIPS) CODIGO', 'TRIM(DS1IPS) NOMBRE'];
			$laIPSs = $goDb
						->select($laCampos)
						->from('TABIPSL01')
						->orderBy('DS1IPS')
						->getAll('array');
		}
		
		return $laIPSs;
	}
	
	public function getIPSs(){
		return $this->aIPSs;
	}
}
