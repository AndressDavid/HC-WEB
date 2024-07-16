<?php

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';

use NUCLEO\Db;

class Euroscore{

	protected $oDb = null;
	public $aDatosEuroscore = [];
	public $aGruposEuroscore = [];
	public $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct(){
		global $goDb;
		$this->oDb = $goDb;
	}

	public function cargarDatosEuroscore(){
		$laCampos = ['INT(CL3TMA) CODIGO', 'DE1TMA DESCRP', 'de2tma || op5tma ToolTip', 'INT(OP1TMA) PUNTOS', 'op4tma/10000000 BETA', '000 TOTAL'];
		$laCondiciones = ['TIPTMA'=>'ESCALAS', 'CL1TMA'=>'EUROSCOR', 'CL2TMA'=>'DATOS', 'ESTTMA'=>'', ]; 
		$laParametros = $this->oDb
							->select($laCampos)
							->from('TABMAE')
							->where($laCondiciones)
							->getAll('array');
		
		if(is_array($laParametros ) == true){
			if(count($laParametros)>0){
				foreach($laParametros as $lcClave=>$itemEuroscore ){
					$laParametros[$lcClave] = array_map('trim', $itemEuroscore);
				}
				$this->aDatosEuroscore = $laParametros;
				return $laParametros;
			}
		}
	}
	
	public function cargarGruposEuroscore(){
		$laCampos = ['INT(CL3TMA) CODIGO', 'SUBSTR(DE1TMA,1,20) GRUPO', 'SUBSTR(DE2TMA,1,20) MORTAL', 'OP4TMA MINIMO', 'OP7TMA MAXIMO'];
		$laCondiciones = ['TIPTMA'=>'ESCALAS', 'CL1TMA'=>'EUROSCOR', 'CL2TMA'=>'GRPRSG', 'ESTTMA'=>'', ]; 
		$laParametros = $this->oDb
							->select($laCampos)
							->from('TABMAE')
							->where($laCondiciones)
							->getAll('array');
		
		if(is_array($laParametros ) == true){
			if(count($laParametros)>0){
				foreach($laParametros as $lcClave=>$itemEuroscore ){
					$laParametros[$lcClave] = array_map('trim', $itemEuroscore);
				}
				$this->aGruposEuroscore = $laParametros;
				return $laParametros;
			}
		}
	}

	public function validarDatosEuroscore($taDatos=[])
	{

		return $this->aError;
	}
	
	public function guardarDatosEuroscore($taDatos=[], $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='')
	{
		
	}
}