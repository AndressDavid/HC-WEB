<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class EstadosCiviles {
	private $aEstados = [];

    public function __construct() {
		$this->aEstados = $this->cargar();
    }

	private function cargar() {
		$laEstados = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['SUBSTR(TRIM(TABCOD),1,1) CODIGO', 'TRIM(TABDSC) NOMBRE'];
			$laEstados = $goDb
						->select($laCampos)
						->from('PRMTAB02')
						->where('TABTIP', '=', 'ECI')
						->orderBy('TABDSC')
						->getAll('array');
		}
		
		return $laEstados;
	}
	
	public function getEstados(){
		return $this->aEstados;
	}
}
