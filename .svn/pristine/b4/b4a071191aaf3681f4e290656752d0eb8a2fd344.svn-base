<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioPerfiles
{
    public $aPerfiles = array();

    public function __construct() {
		$this->cargar();
    }


	public function cargar() {
		global $goDb;
		if (isset($goDb)) {		
			$laTipos = $goDb
						->select('CL4TMA,CL5TMA,DE1TMA,ESTTMA')
						->tabla('SISMENPAR')
						->where('TIPTMA','=','MENPER')
						->where('CL3TMA','=','PROFILE')
						->orderBy('ESTTMA,CL5TMA,DE1TMA')
						->getAll('array');
			if (is_array($laTipos)) {
				if (count($laTipos) > 0) {
					foreach ($laTipos as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aPerfiles[$laTipo['CL4TMA']] = [
							'ID' => $laTipo['CL4TMA'],
							'TYPE' => trim($laTipo['CL5TMA']),
							'NAME' =>trim($laTipo['DE1TMA']),
							'STATE' => trim($laTipo['ESTTMA']),
							];
					}
				}
			}
		}
	}
	
	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aPerfiles)){
			if(count($this->aPerfiles)>0){
				if(isset($this->aPerfiles[$tcId])==true){
					$lcNombre=$this->aPerfiles[$tcId]['NAME'];
				}
			}
		}
		
		return $lcNombre;
	}
}
