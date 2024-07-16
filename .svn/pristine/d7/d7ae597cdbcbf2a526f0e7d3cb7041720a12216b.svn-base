<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Bodega
{
    public $cId = '';
	public $nId = 0;
    public $cNombre = '';
	public $cTipo = '';
	public $cCentroCosto = '';
	public $cEstado = '';

    public function __construct($tcId="") {
		$this->cargar($tcId);
    }

	public function cargar($tcId=""){
		$tcId=trim($tcId);
		global $goDb;
		if(isset($goDb)){
			if(!empty($tcId)){
				$laCampos = ['CDGBOD','DESBOD','TIPBOD','CCOBOD','STSBOD'];
				$laBodegas = $goDb->select($laCampos)->tabla('INVBOD')->where('CDGBOD', '=', $tcId)->get("array");
				if(is_array($laBodegas)==true){
					if(count($laBodegas)>0){
						$this->cId = trim($laBodegas['CDGBOD']);
						$this->nId = $this->cId+0;
						$this->cNombre = trim($laBodegas['DESBOD']);
						$this->cTipo = trim($laBodegas['TIPBOD']);
						$this->cCentroCosto = trim($laBodegas['CCOBOD']);
						$this->cEstado = trim($laBodegas['STSBOD']);
					}
				}
			}
		}
	}
}
?>