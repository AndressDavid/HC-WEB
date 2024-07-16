<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Planes
{
    public $aPlanes = array();

    public function __construct() {
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(PLNCON) PLNCON','TRIM(DSCCON) DSCCON'];
			$laPlanes = $goDb->select($laCampos)->tabla('FACPLNC')->where('ESTCON','=','A')->orderBy('DSCCON')->getAll("array");
			if(is_array($laPlanes)==true){
				$this->aPlanes=$laPlanes;
			}
		}
	}
	
	public function buscarListaPlanesNombreCodigo($tvNombre='', $tcCodigo='', $tlIncluirTodosLosEstados=false) {
		
		if(is_array($tvNombre)==false){
			$tvNombre = array(trim(strval($tvNombre)));
		}
		$tcCodigo = trim(strval($tcCodigo));
		$tcCodigo = mb_strtoupper(!empty($tcCodigo)?'%'.$tcCodigo.'%':'');
		
		$laPlanes = array();		

		global $goDb;		
		$laCampos = ['TRIM(P.PLNCON) CODIGO','TRIM(P.DSCCON) NOMBRE'];
		
		$lcWhere = ($tlIncluirTodosLosEstados==true?'':" P.ESTCON='A'");
		
		if(count($tvNombre)>0){
			$lcWhereAux = '';
			foreach($tvNombre as $lcNombre){
				if(!empty($lcNombre) && $lcNombre!=='*' ){
					$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
					$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("TRANSLATE(UPPER(P.DSCCON),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')", $lcNombre);
				}
			}
			$lcWhere .= (!empty($lcWhereAux)?(empty($lcWhere)?'':' AND ')."(".$lcWhereAux.")":'');
		}
		$lcWhere.= (!empty($tcCodigo)?sprintf(" AND (P.PLNCON LIKE '%s')", $tcCodigo):'');		
		$lcOrden='P.DSCCON ASC';
		
		$laPlanes = $goDb
					->select($laCampos)
					->from('FACPLNC P')
					->where($lcWhere)
					->orderBy($lcOrden)
					->getAll('array');		
					
					
		return $laPlanes;
	}	
	
}
?>