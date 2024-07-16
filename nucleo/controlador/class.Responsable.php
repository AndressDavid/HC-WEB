<?php
namespace NUCLEO;

require_once 'class.Persona.php';

use NUCLEO\Persona;

class Responsable
    extends Persona {

	public $cNombre1 = '';
	public $cNombre2 = '';
	public $cApellido1 = '';
	public $cApellido2 = '';
	public $cDireccionResp = '';
	public $cTelefonoResp = '';

    public function __construct (){
    }

    public function cargarResponsable($tnIngreso=0)
	{

		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$laResponsable = $goDb
					->select([ 'P.NM1RES','P.NM2RES','P.AP1RES','P.AP2RES','P.DR1RES','P.TELRES', ])
					->from('RIARES P')
					->where(['P.NIGRES'=>$tnIngreso])
					->get('array');
 				if(is_array($laResponsable)==true){
					if(count($laResponsable)>0){
						$this->cNombre1 = trim($laResponsable['NM1RES']);
						$this->cNombre2 = trim($laResponsable['NM2RES']);
						$this->cApellido1 = trim($laResponsable['AP1RES']);
						$this->cApellido2 = trim($laResponsable['AP2RES']);
						$this->cDireccionResp = trim($laResponsable['DR1RES']);
						$this->cTelefonoResp = trim($laResponsable['TELRES']);
					}
				}
			}
		}
    }

}
?>