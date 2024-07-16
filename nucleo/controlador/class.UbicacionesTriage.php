<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

use NUCLEO\Db;

class UbicacionesTriage{
	
	public $aUbicacionesTriage = array();
	
	public function __construct(){
		global $goDb;
		if(isset($goDb)){ 
			$laCampos = ['CL2TMA', 'DE1TMA'];
			$laCondiciones = ['TIPTMA' =>'DATING', 'CL1TMA'=>'DIGITURN',  'ESTTMA' =>' ' ];
			$laUbicacionesTriage = $goDb
													->select($laCampos)
													->from('TABMAE')
													->where($laCondiciones)
													->where('OP5TMA', '<>','')
													->Like('CL2TMA', '010101%')	
													->getAll('array');
			if(is_array($laUbicacionesTriage) == true){
				foreach($laUbicacionesTriage as $laUbicacionTriage){
					$laUbicacionTriage = array_map('trim', $laUbicacionTriage);
					$this->aUbicacionesTriage[$laUbicacionTriage['CL2TMA']] = array(
						'LCCLASIFICACION2' =>$laUbicacionTriage['CL2TMA'],
						'LCDESCRIPCION1' => $laUbicacionTriage['DE1TMA']
					);
				}
			}
		}
	}
}
?>
