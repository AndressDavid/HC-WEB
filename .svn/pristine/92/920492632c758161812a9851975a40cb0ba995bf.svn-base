<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class UsuarioTipo
{
    public $nId = null;
    public $cNombre = null;

    public function __construct($tnId=0) {
		$this->cargar($tnId);
    }

	public function cargar($tnId=0){
		settype($tnId,"integer");

		global $goDb;
		if(isset($goDb)){
			if(!empty($tnId)){
				// Buscando tipo de usuario
				$laCampos = ['TABCOD','TABDSC'];
				$laTipo = $goDb->select($laCampos)->tabla('PRMTAB02')->where('TABTIP', '=', 'TUS')->where('TABCOD', '=', $tnId)->get("array");
				if(is_array($laTipo)==true){
					if(count($laTipo)>0){
						$this->nId = intval(trim($laTipo["TABCOD"]));
						$this->cNombre = trim($laTipo["TABDSC"]);
					}
				}
			}
		}
	}
}
?>