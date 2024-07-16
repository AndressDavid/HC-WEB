<?php

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';

class Dolor_Toracico{
	
	public $aDolorToracico = [];
	
	public function __construct(){
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['CL1TMA', 'CL2TMA', 'CL3TMA', 'DE1TMA', 
									 'DE2TMA', 'OP1TMA', 'OP2TMA', 'OP4TMA'];
			$laCondiciones = ['TIPTMA'=>'TORACICO'];
			$laDoloresToracico = $goDb
											->select($laCampos)
											->from('TABMAE')
											->where($laCondiciones)
											->orderBy('INT(OP3TMA)')
											->getAll('array');
			if(is_array($laDoloresToracico) == true){
				foreach($laDoloresToracico as $laDolorToracico){
					$laDolorToracico = array_map('trim', $laDolorToracico);
					$this->aDolorToracico[$laDolorToracico['CL1TMA']] = array(
						'TIPO'=>$laDolorToracico['CL1TMA'],
						'CODIGO'=>$laDolorToracico['CL2TMA'],
						'NIVEL'=>intval($laDolorToracico['CL3TMA']),
						'NOMBRE'=>$laDolorToracico['DE1TMA'],
						'DESCRIP'=>$laDolorToracico['DE2TMA'],
						'VALOR'=>$laDolorToracico['OP1TMA'],
						'POSANT'=>$laDolorToracico['OP2TMA'],
						'LINEA'=>$laDolorToracico['OP4TMA'],	
					);
				}
			}
		}					
	}
}
?>