<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Bodegas
{
    public $aBodegas = array();

    public function __construct($tcOdenar='DESBOD') {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['CDGBOD','DESBOD','TIPBOD','CCOBOD','STSBOD'];
			$laBodegas = $goDb->select($laCampos)->tabla('INVBOD')->orderBy($tcOdenar)->getAll("array");
			if(is_array($laBodegas)==true){
				foreach($laBodegas as $laBodega){
					$laBodega = array_map('trim',$laBodega);
					$lnId=$laBodega["CDGBOD"]; settype($lnId,"integer");
					$this->aBodegas[$lnId] = array("NOMBRE"=>$laBodega['DESBOD'],"TIPO"=>$laBodega['TIPBOD'],"CENTRO"=>$laBodega['CCOBOD'],"ESTADO"=>$laBodega['STSBOD']);
				}
			}
		}
	}
}
?>