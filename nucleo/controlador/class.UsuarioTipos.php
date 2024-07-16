<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioTipos
{
    public $aTipos = array();

    public function __construct() {
		$this->cargar();
    }


	public function cargar() {
		global $goDb;
		if (isset($goDb)) {		
			$laTipos = $goDb
						->select('TABCOD AS ID,TABDSC AS NAME')
						->tabla('PRMTAB02')
						->where('TABTIP', '=', 'TUS')
						->getAll('array');
			if (is_array($laTipos)) {
				if (count($laTipos) > 0) {
					foreach ($laTipos as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipos[intval($laTipo['ID'])] = [
							'ID' => intval($laTipo['ID']),
							'NAME' =>trim($laTipo['NAME']),
							];
					}
				}
			}
		}
	}
	
	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aTipos)){
			if(count($this->aTipos)>0){
				if(isset($this->aTipos[$tcId])==true){
					$lcNombre=$this->aTipos[$tcId]['NAME'];
				}
			}
		}
		
		return $lcNombre;
	}
}
