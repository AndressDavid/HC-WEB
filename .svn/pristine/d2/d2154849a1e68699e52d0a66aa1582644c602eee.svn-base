<?php

	namespace NUCLEO;
	
	require_once __DIR__ .'/class.Db.php';
	
	use NUCLEO\Db;

	class EscalaHasbled{

		protected $oDb = null;
		public $aHasbled = [];
		
		public function __construct(){
			global $goDb;
			$this->oDb = $goDb;
			$this->cargarDatos();
		}
		
	
		public function cargarDatos(){
			
			// Obtiene tabla de dato		
			$laCampos = ['CL2TMA', 'CL3TMA', 'DE2TMA', 'OP3TMA', 'OP4TMA', 'OP5TMA', 'OP6TMA', 'OP7TMA'];
			$laCondiciones = ['TIPTMA'=>'BLDSCORE', 'CL1TMA'=>'HASBLED'];
			$laHasbled = $this->oDb
								->select($laCampos)
								->from('TABMAE')
								->where($laCondiciones)
								->getAll('array');
			if(is_array($laHasbled ) == true){
				if(count($laHasbled)>0){
					foreach($laHasbled as $lcClave=>$itemHasbled ){
						$laHasbled[$lcClave] = array_map('trim', $itemHasbled);
					}
					$this->aHasbled = $laHasbled;
					return $laHasbled;	
				}						
			}
		}

	}
?>