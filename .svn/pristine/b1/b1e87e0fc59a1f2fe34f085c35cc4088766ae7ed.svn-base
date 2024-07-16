<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class CentrosServicios
{
    public $aCentros = array();

    public function __construct() {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TABCOD', 'TABDSC'];
			$laCentros = $goDb->select($laCampos)->tabla('PRMTAB02')->where('TABTIP', '=', 'CSE')->getAll("array");
			if(is_array($laCentros)==true){
				foreach($laCentros as $laCentro){
					$laCentro = array_map('trim',$laCentro);
					$this->aCentros[] = ['ID'=>intval($laCentro["TABCOD"]), 'CODIGO'=>$laCentro["TABCOD"], 'NOMBRE'=>$laCentro["TABDSC"]];
				}
			}
		}
	}
}
?>