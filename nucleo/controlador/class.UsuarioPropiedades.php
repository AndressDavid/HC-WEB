<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class UsuarioPropiedades
{
    public $aOpciones = array();

    public function __construct() {
		$this->cargar();
    }

	public function cargar() {
		global $goDb;
		if (isset($goDb)) {		
			$laCampos=array('TIPTMA as TIPO', 'CL4TMA as ID', 'DE1TMA as NAME', 'DE2TMA as DESCRIPTION', 'OP2TMA as TYPE', 'OP5TMA as VALUE', 'ESTTMA as STATE');
			$laOpciones = $goDb
						->select($laCampos)
						->tabla('SISMENPAR')
						->where('TIPTMA', '=', 'MENUSU')
						->where('CL1TMA', '=', '')
						->where('CL2TMA', '=', '')
						->where('CL3TMA', '=', 'PROPER')
						->where('CL5TMA', '=', '')
						->where('ESTTMA', '=', 'A')
						->orderBy('DE1TMA')
						->getAll('array');
						
			if (is_array($laOpciones)) {
				if (count($laOpciones) > 0) {
					foreach ($laOpciones as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aOpciones[$laTipo['ID']] = [
							'ID' => $laTipo['ID'],
							'TIPO' => $laTipo['TIPO'],
							'NAME' =>trim($laTipo['NAME']),
							'DESCRIPTION' => trim($laTipo['DESCRIPTION']),
							'TYPE' => trim($laTipo['TYPE']),
							'VALUE' => $laTipo['VALUE'],
							'STATE' => $laTipo['STATE'],
							'ASSIGNED' => NULL,
							];
					}
				}
			}
		}
	}
	
	public function Nombre($tcId=''){
		$tcId=trim(sprintf('%s',$tcId));
		$lcNombre='';
		if(is_array($this->aOpciones)){
			if(count($this->aOpciones)>0){
				if(isset($this->aOpciones[$tcId])==true){
					$lcNombre=$this->aOpciones[$tcId]['NAME'];
				}
			}
		}
		
		return $lcNombre;
	}
}
