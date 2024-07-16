<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Trabajos {
	private $aTrabajos = [];

    public function __construct() {
		$this->aTrabajos = $this->cargar();
    }

	private function cargar() {
		$laTrabajos = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA), 1, 2) CODIGO', 'SUBSTR(TRIM(DE1TMA), 1, 25) NOMBRE'];
			$laTrabajos = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=','6')
						->where('ESTTMA', '<>','1')
						->orderBy('DE1TMA')
						->getAll('array');
		}
		
		return $laTrabajos;
	}
	
	public function getTrabajos(){
		return $this->aTrabajos;
	}
}
