<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class Ocupacion
{
    public $cId = null;
    public $cNombre = null;


    public function __construct($tcId='') {
		$this->cargar($tcId);
    }

    /**
     * Obtiene la descripción de la coupación, a partir del código
     *
     * @access public
     * @param char $tcId: código de la ocupación
     */
	public function cargar($tcId=''){
		$tcId=trim($tcId);
		if(!empty($tcId)){
			global $goDb;
			$laOcupacion = $goDb->select('CL1TMA CODOCU,DE2TMA DESOCU')->tabla('TABMAEL01')->where(['TIPTMA'=>'CODOCU','CL1TMA'=>$tcId,])->get('array');
			if(is_array($laOcupacion)){
				if(count($laOcupacion)>0){
					$this->cId = trim($laOcupacion['CODOCU']);
					$this->cNombre = trim($laOcupacion['DESOCU']);
				}
			}
		}
	}
}
