<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioDepartamentos
{
	public $aDepartamentos = array();

	public function __construct() {
		$this->cargar();
	}


	public function cargar() {
		global $goDb;
		if (isset($goDb)) {
			$laDepartamentos = $goDb
				->select('SUBSTR(TRIM(DE1TMA),1,30) DESDEP,SUBSTR(TRIM(CL2TMA),1,2) CODDEP')
				->tabla('TABMAEL01')
				->where('TIPTMA', '=', 'CODARE')
				->orderBy('DE1TMA')
				->getAll('array');
			if (is_array($laDepartamentos)) {
				if (count($laDepartamentos) > 0) {
					foreach ($laDepartamentos as $laEstado) {
						$aDepartamentos = array_map('trim', $laEstado);
						$this->aDepartamentos[$laEstado['CODDEP']] = [
							'ID' => $laEstado['CODDEP'],
							'NOMBRE' => trim($laEstado['DESDEP']),
							];
					}
				}
			}
		}
	}

	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aDepartamentos)){
			if(count($this->aDepartamentos)>0){
				if(isset($this->aDepartamentos[$tcId])==true){
					$lcNombre=$this->aDepartamentos[$tcId]['NOMBRE'];
				}
			}
		}

		return $lcNombre;
	}
}
