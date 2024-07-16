<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class PlanesTipo {
	private $aPlanesTipo = [];

    public function __construct() {
		$this->aPlanesTipo = $this->cargar();
    }

	private function cargar() {
		$laPlanesTipo = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA),1,1) CODIGO', 'TRIM(DE1TMA) NOMBRE'];
			$laPlanesTipo = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=','1')
						->where('ESTTMA', '<>','1')
						->orderBy('DE1TMA')
						->getAll('array');
		}
		
		return $laPlanesTipo;
	}
	
	public function getPlanesTipo(){
		return $this->aPlanesTipo;
	}
}
