<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class CentroCosto
{
    public $cId = null;
	public $nId = null;
    public $cNombre = null;

    public function __construct($tcId="") {
		$this->cargar($tcId);
    }

	public function cargar($tcId=""){
		$tcId=trim($tcId);
		global $goDb;
		if(isset($goDb)){
			if(!empty($tcId)){
				$laCampos = ['TABCOD', 'TABDSC'];
				$laCentros = $goDb->select($laCampos)->tabla('PRMTAB02')->where('TABTIP', '=', '004')->where('TABCOD', '=', $tcId)->get("array");
				if(is_array($laCentros)==true){
					if(count($laCentros)>0){
						$this->cId = trim($laCentros["TABCOD"]);
						$this->nId = $this->cId+0;
						$this->cNombre = trim($laCentros["TABDSC"]);
					}
				}
			}
		}
	}
}
?>