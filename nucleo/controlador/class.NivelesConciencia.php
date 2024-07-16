<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class NivelesConciencia
{
	public $aNiveles = array();

	public function __construct($tbSoloActivos=true) {
		global $goDb;
		if(isset($goDb)){
			if ($tbSoloActivos==true) {
				$goDb->where('ACTENF','<>','1');
			}
			$laNiveles = $goDb
				->select('REFENF, ESTENF, DESENF')
				->tabla('TABENF')
				->where(['TIPENF'=>'4', 'VARENF'=>'5'])
				->getAll("array");
			if($goDb->numRows()>0){
				foreach($laNiveles as $laNivelConciencia){
					$laNivelConciencia = array_map('trim',$laNivelConciencia);
					if(isset($this->aNiveles[$laNivelConciencia['REFENF']])==false){
						$this->aNiveles[$laNivelConciencia['REFENF']] = array('TIPO' => $laNivelConciencia["ESTENF"], 'NOMBRE' => $laNivelConciencia["DESENF"]);
					}
				}
			}
		}
	}
}
