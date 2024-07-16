<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioEstados
{
    public $aEstados = array();

    public function __construct() {
		$this->cargar();
    }


	public function cargar() {
		global $goDb;
		if (isset($goDb)) {		
			$laEstados = $goDb
						->select('CL1TMA,DE1TMA')
						->tabla('TABMAEL01')
						->where('TIPTMA', '=', 'CODESU')
						->getAll('array');
			if (is_array($laEstados)) {
				if (count($laEstados) > 0) {
					foreach ($laEstados as $laEstado) {
						$aEstados = array_map('trim', $laEstado);
						$this->aEstados[$laEstado['CL1TMA']] = [
							'CODESU' => $laEstado['CL1TMA'],
							'DESESU' => substr(trim($laEstado['DE1TMA']),0,20),
							];
					}
				}
			}
		}
	}

	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aEstados)){
			if(count($this->aEstados)>0){
				if(isset($this->aEstados[$tcId])==true){
					$lcNombre=$this->aEstados[$tcId]['DESESU'];
				}
			}
		}
		
		return $lcNombre;
	}	
}
