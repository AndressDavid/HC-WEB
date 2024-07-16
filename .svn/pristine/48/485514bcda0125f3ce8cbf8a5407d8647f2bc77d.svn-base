<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioCargos
{
    public $aCargos = array();

    public function __construct($tnTipo=0, $tnArea=0) {
		$this->cargar($tnTipo, $tnArea);
    }


	public function cargar($tnTipo=0, $tnArea=0) {
		global $goDb;
		if (isset($goDb)) {		
			$laCargos = $goDb
						->select('A.CONPRT AS ID, A.DESPRT AS CARGO, A.IN2PRT AS TIPO, B.DESPRT DEPTO')
						->tabla('PARTRA A')
						->leftJoin('PARTRA B', 'A.SISPRT', '=', 'B.CONPRT')
						->where('A.INDPRT', '=', '3')
						->where('A.DESPRT', '<>', '')
						->where('A.IN2PRT', '=', $tnTipo)
						->where('A.SISPRT', '=', $tnArea)
						->where('B.INDPRT', '=', 2)
						->orderBy('A.DESPRT')
						->getAll('array');
			if (is_array($laCargos)) {
				if (count($laCargos) > 0) {
					foreach ($laCargos as $laCargo) {
						$aCargos = array_map('trim', $laCargo);
						$this->aCargos[$laCargo['ID']] = [
							'ID' => $laCargo['ID'],
							'TIPO' => trim($laCargo['TIPO']),
							'NOMBRE' => trim($laCargo['CARGO']),
							'DEPARTAMENTO' => trim($laCargo['DEPTO']),
							];
					}
				}
			}
		}
	}

	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aCargos)){
			if(count($this->aCargos)>0){
				if(isset($this->aCargos[$tcId])==true){
					$lcNombre=$this->aCargos[$tcId]['NOMBRE'];
				}
			}
		}
		
		return $lcNombre;
	}	
}
