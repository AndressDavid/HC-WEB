<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class PlanesTipoAfiliado {
	private $aPlanesTipoAfiliado = [];

    public function __construct() {
		$this->aPlanesTipoAfiliado = $this->cargar();
    }

	private function cargar() {
		$laPlanesTipoAfiliado = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(CL2TMA),1,1) CODIGO', 'SUBSTR(TRIM(DE1TMA),1,20) NOMBRE'];
			$laPlanesTipoAfiliado = $goDb
						->select($laCampos)
						->from('TABMAEL01')
						->where('TIPTMA', '=', 'DATING')
						->where('CL1TMA', '=','2')
						->where('ESTTMA', '<>','1')
						->orderBy('DE1TMA')
						->getAll('array');
		}
		
		return $laPlanesTipoAfiliado;
	}
	
	public function getPlanesTipoAfiliado(){
		return $this->aPlanesTipoAfiliado;
	}
}