<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class NivelesEducativos {
	private $aNiveles = [];

    public function __construct() {
		$this->aNiveles = $this->cargar();
    }

	private function cargar() {
		$laNiveles = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA),1,2) CODIGO', 'SUBSTR(TRIM(DE2TMA),1, 50) NOMBRE'];
			$laNiveles = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=', 'NIVEDU')
						->where('ESTTMA', '<>','1')
						->orderBy('DE2TMA')
						->getAll('array');
		}
		
		return $laNiveles;
	}
	
	public function getNiveles(){
		return $this->aNiveles;
	}
}
