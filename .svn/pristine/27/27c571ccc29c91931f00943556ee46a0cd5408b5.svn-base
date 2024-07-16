<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class EspecialidadesSalas
{
    public $aEspecialidades = array();

    public function __construct($tcOdenar='DESESP', $tlSoloActivas=false) {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['CODESP','TRIM(DESESP) DESESP, TRIM(PGCESP) INTERCONSULTA'];
			$laEspecialidades = $goDb
			->select($laCampos)
			->tabla('RIAESPEL01')
			->where('UBIESP=\' \'')
			->orderBy($tcOdenar)
			->getAll("array");
			if(is_array($laEspecialidades)==true){
				$this->aEspecialidades=$laEspecialidades;
			}
		}
	}
	
	public function Nombre($tnId=0){
		$tnId=intval($tnId);
		$lcNombre='';
		if(is_array($this->aEspecialidades)){
			if(count($this->aEspecialidades)>0){
				if(isset($this->aEspecialidades[$tnId])==true){
					$lcNombre=$this->aEspecialidades[$tnId];
				}
			}
		}
		
		return $lcNombre;
	}
}

?>
