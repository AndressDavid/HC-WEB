<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Ocupaciones {
	private $aOcupaciones = [];

    public function __construct() {
		$this->aOcupaciones = $this->cargar();
    }

	private function cargar() {
		$laOcupaciones = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL1TMA),1,4) CODIGO', 'TRIM(DE2TMA) NOMBRE'];
			$laOcupaciones = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'CODOCU')
						->where('ESTTMA', '<>','1')
						->orderBy('DE2TMA')
						->getAll('array');
		}
		
		return $laOcupaciones;
	}
	
	public function getOcupaciones(){
		return $this->aOcupaciones;
	}
}
