<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Especialidades
{
    public $aEspecialidades = array();

    public function __construct($tcOdenar='CODESP', $tlSoloActivas=false) {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['CODESP','DESESP', "CODESP, CASE WHEN UBIESP <> ' ' THEN CAST(TRANSLATE(UBIESP) AS DECIMAL(2 , 0)) ELSE 0 END AS ESTESP"];
			$laEspecialidades = $goDb->select($laCampos)->tabla('RIAESPEL01')->orderBy($tcOdenar)->getAll("array");
			if(is_array($laEspecialidades)==true){
				foreach($laEspecialidades as $laEspecialidad){
					if ($tlSoloActivas==true?(intval($laEspecialidad['ESTESP'])<>1):true){					
						$laEspecialidad = array_map('trim',$laEspecialidad);
						$lnId=$laEspecialidad["CODESP"]; settype($lnId,"integer");
						$this->aEspecialidades[$lnId] = $laEspecialidad["DESESP"];
					}
				}
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