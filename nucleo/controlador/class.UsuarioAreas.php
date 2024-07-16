<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioAreas
{
    public $aAreas = array();

    public function __construct() {
		$this->cargar();
    }


	public function cargar() {
		global $goDb;
		if (isset($goDb)) {		
			$laAreas = $goDb
						->select('CONPRT AS ID, DESPRT AS AREA, IN2PRT AS TIPO')
						->tabla('PARTRA')
						->where('INDPRT', '=', '2')
						->where('DESPRT', '<>', '')
						->orderBy('IN2PRT,DESPRT')
						->getAll('array');
			if (is_array($laAreas)) {
				if (count($laAreas) > 0) {
					foreach ($laAreas as $laArea) {
						$aAreas = array_map('trim', $laArea);
						$this->aAreas[$laArea['ID']] = [
							'ID' => $laArea['ID'],
							'TIPO' => trim($laArea['TIPO']),
							'NOMBRE' => trim($laArea['AREA']),
							];
					}
				}
			}
		}
	}

	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aAreas)){
			if(count($this->aAreas)>0){
				if(isset($this->aAreas[$tcId])==true){
					$lcNombre=$this->aAreas[$tcId]['NOMBRE'];
				}
			}
		}
		
		return $lcNombre;
	}	
}
