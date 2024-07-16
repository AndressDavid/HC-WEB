<?php
namespace NUCLEO;

class Cups {
    function __construct() {
    }

	public function buscarCups($tcEstado='0', $lLista=false, $tcEspecialidad='') {
		$tcEstado = trim(strval($tcEstado)); // '0'=Activo, '1'=Inactivo, ''=Todos los estados
		$tcEspecialidad = trim(strval($tcEspecialidad));
		$laCups = array();
		
		$lcWhere = '';
		$lcWhere .= (!empty($tcEstado)?sprintf("%s C.IDDCUP='%s'",(empty($lcWhere)?'':' AND '),$tcEstado):'');
		$lcWhere .= (!empty($tcEspecialidad)?sprintf("%s C.ESPCUP='%s'",(empty($lcWhere)?'':' AND '),$tcEspecialidad):'');
		$lcWhere = (empty($lcWhere)?'1=1':$lcWhere);
		
		global $goDb;		
		if($lLista==true){
			$laCampos = ['C.IDDCUP ESTADO','TRIM(C.CODCUP) CODIGO','TRIM(C.DESCUP) DESCRIPCION'];
			$lcOrden='C.DESCUP ASC';
		}else{
			$laCampos = ['C.IDDCUP ESTADO',
						 'TRIM(C.CODCUP) CODIGO',
						 'C.RF1CUP REF1',
						 'C.RF2CUP REF2',
						 'C.RF3CUP REF3',
						 'C.RF4CUP REF4',
						 'C.RF5CUP REF5',
						 'C.RF6CUP REF6',
						 'TRIM(C.DESCUP) DESCRIPCION',
						 'C.PROCUP USAR_PROCEDIMIENTO',
						 'C.PGRCUP PROGRAMA',
						 'C.RIPCUP SI_RIPS',
						 'C.MARCUP MARCA',
						 'C.ESPCUP ESPECIALIDAD',
						 'C.SEXCUP SEXO',
						 'C.CADCUP ARC_ADS',
						 'C.CAPCUP ARCH_PROCEDIMIENTO',
						 'C.CATCUP OTROS',
						 'C.CLBCUP CODIGO_LABORATORIO'];
			$lcOrden='C.CODCUP ASC';
		}
		$laCups = $goDb
					->select($laCampos)
					->from('RIACUP C')
					->where($lcWhere)
					->orderBy($lcOrden)
					->getAll('array');
		return $laCups;
	}

}