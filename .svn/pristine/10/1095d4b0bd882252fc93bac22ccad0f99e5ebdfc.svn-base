<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Especialidades
{
    public $aCentros = array();

    public function __construct($tcOdenar='CODESP') {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TABCOD', 'TABDSC'];
			$laCentros = $goDb->select($laCampos)->tabla('PRMTAB02')->where('TABTIP', '=', '004')->getAll("array");
			if(is_array($laCentros)==true){
				foreach($laCentros as $laCentro){
					$laCentro = array_map('trim',$laCentro);
					$lnId=$laCentro["TABCOD"]; settype($lnId,"integer");
					$this->aCentros[$lnId] = $laCentro["TABDSC"];
				}
			}
		}
	}
}
?>