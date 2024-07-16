<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Especialidad
{
    public $cId = null;
	public $nId = null;
    public $cNombre = null;

    public function __construct($tcId='') {
		$this->cargar($tcId);
    }

	public function cargar($tcId=''){
		$tcId=trim($tcId);
		global $goDb;
		if(isset($goDb)){
			if(!empty($tcId)){
				$laEspecialidades = $goDb->select('CODESP,DESESP')->tabla('RIAESPE')->where('CODESP', '=', $tcId)->get('array');
				if(is_array($laEspecialidades)){
					if(count($laEspecialidades)>0){
						$this->cId = trim($laEspecialidades['CODESP']);
						$this->nId = intval($this->cId);
						$this->cNombre = trim($laEspecialidades['DESESP']);
					}
				}
			}
		}
	}
}
