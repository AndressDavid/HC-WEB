<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Estratos {
	private $aEstratos = [];

    public function __construct() {
		$this->aEstratos = $this->cargar();
    }

	private function cargar() {
		$laEstratos = [];
		
		global $goDb;
		if (isset($goDb)) {
			$laCampos = ['TRIM(TABCOD) CODIGO', 'TRIM(TABDSC ) NOMBRE'];
			$laEstratos = $goDb
						->select($laCampos)
						->from('PRMTAB')
						->where('TABTIP', '=', 'ETR')
						->where('TABCOD', '>',0)
						->orderBy('TABCOD')
						->getAll('array');
		}
		
		return $laEstratos;
	}
	
	public function getEstratos(){
		return $this->aEstratos;
	}
}