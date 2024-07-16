<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class SalasCirugia
{
    public $aSalasCirugia = array();

    public function __construct() {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['SECHAB','NUMHAB'];
			$laSalas = $goDb
			->select($laCampos)
			->tabla('FACHAB')
			->where('IDDHAB=\'0\'')
			->Like('SECHAB', 'S%')
			->orderBy('SECHAB, NUMHAB')
			->getAll("array");
			if(is_array($laSalas)==true){
				$this->aSalasCirugia=$laSalas;
			}
		}
	}
	
	public function Nombre($tcId=''){
		$lcNombre='';
		if(is_array($this->aSalasCirugia)){
			if(count($this->aSalasCirugia)>0){
				if(isset($this->aSalasCirugia[$tcId])==true){
					$lcNombre=$this->aSalasCirugia[$tcId];
				}
			}
		}
		return $lcNombre;
	}	
}
?>
