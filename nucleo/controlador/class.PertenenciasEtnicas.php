<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class PertenenciasEtnicas {
	private $aPertenencias = [];

    public function __construct() {
		$this->aPertenencias = $this->cargar();
    }

	private function cargar() {
		$laPertenencias = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA),1,2) CODIGO', 'SUBSTR(TRIM(DE2TMA),1, 70) NOMBRE'];
			$laPertenencias = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=', 'PERETNI')
						->where('ESTTMA', '<>','1')
						->orderBy('DE2TMA')
						->getAll('array');
		}
		
		return $laPertenencias;
	}
	
	public function getPertenencias(){
		return $this->aPertenencias;
	}
}
