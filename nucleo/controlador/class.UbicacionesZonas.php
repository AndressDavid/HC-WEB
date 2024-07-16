<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UbicacionesZonas {
	private $aUbicacionesZonas = [];

    public function __construct() {
		$this->aUbicacionesZonas = $this->cargar();
    }

	private function cargar() {
		$laUbicacionesZonas = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA),1,1) CODIGO', 'SUBSTR(TRIM(DE1TMA),1,25) NOMBRE'];
			$laUbicacionesZonas = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=','3')
						->where('ESTTMA', '<>','1')
						->orderBy('DE1TMA')
						->getAll('array');
		}
		
		return $laUbicacionesZonas;
	}
	
	public function getUbicacionesZonas(){
		return $this->aUbicacionesZonas;
	}
}