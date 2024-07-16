<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Parentescos {
	private $aParentescos = [];

    public function __construct() {
		$this->aParentescos = $this->cargar();
    }

	private function cargar() {
		$laParentescos = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(CL1TMA,1,2) CODIGO', 'SUBSTR(DE1TMA,1,40) NOMBRE'];
			$laParentescos = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'CODPAR')
						->where('ESTTMA', '<>', '1')
						->orderBy('DE1TMA')
						->getAll('array');
		}
		
		return $laParentescos;
	}
	
	public function getParentescos(){
		return $this->aParentescos;
	}
}
