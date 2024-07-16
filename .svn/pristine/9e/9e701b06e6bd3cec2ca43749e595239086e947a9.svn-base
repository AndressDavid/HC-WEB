<?php
namespace NUCLEO;

class Medicos
{

	public function buscarListaMedicos($tcEspecialidad='', $tcListaTipos ='', $tlIncluirTodosLosEstados=false)
	{
		$laMedicos = $this->buscarListaMedicosNombreRegistro('', '', $tcEspecialidad, $tcListaTipos, $tlIncluirTodosLosEstados);
		return $laMedicos;
	}

	public function buscarListaMedicosNombreRegistro($tvNombre='', $tcRegistro='', $tcEspecialidad='', $tcListaTipos ='', $tlIncluirTodosLosEstados=false)
	{
		if(is_array($tvNombre)==false){
			$tvNombre = array(trim(strval($tvNombre)));
		}
		$tcRegistro = trim(strval($tcRegistro));
		$tcRegistro = mb_strtoupper(!empty($tcRegistro)?'%'.$tcRegistro.'%':'');

		$tcListaTipos = trim(strval($tcListaTipos)); $tcListaTipos = (empty($tcListaTipos)?'1, 3, 4, 5, 6, 10, 12, 13, 16, 18':$tcListaTipos);
		$tcEspecialidad	=trim(strval($tcEspecialidad)); $tcEspecialidad = ($tcEspecialidad!='*'?$tcEspecialidad:'');
		$laMedicos = array();

		global $goDb;
		$laCampos = [
			"TRIM(R.NOMMED) || ' ' || TRIM(R.NNOMED) MEDICO",
			(!empty($tcEspecialidad)?"(SELECT COUNT(A.ESPTUS) FROM MEDTUS AS A WHERE A.REGTUS=R.REGMED AND A.ESPTUS='".$tcEspecialidad."') ESPECIALIDADES":"0 ESPECIALIDADES"),
			'R.REGMED AS REGISTRO',
			'R.CODRGM AS ESPECIALIDAD',
			'R.ESTRGM AS ESTADO',
			'R.NIDRGM AS IDENTIFICAICON',
			'R.USUARI AS USUARIO',
			'TRIM(CHAR(R.TPMRGM)) AS TIPO'
		];
		$lcWhere = 'R.TPMRGM IN('.$tcListaTipos.')'.($tlIncluirTodosLosEstados==true?'':' AND R.ESTRGM=1');

		if(count($tvNombre)>0){
			$lcWhereAux = '';
			foreach($tvNombre as $lcNombre){
				if(!empty($lcNombre) && $lcNombre!=='*'){
					$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
					$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("( TRANSLATE(UPPER(R.NOMMED),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ') OR TRANSLATE(UPPER(R.NNOMED),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ') )", $lcNombre, $lcNombre);
				}
			}
			$lcWhere.= (!empty($lcWhereAux)?" AND (".$lcWhereAux.")":'');
		}
		$lcWhere.= (!empty($tcRegistro)?sprintf(" AND (R.REGMED LIKE '%s')", $tcRegistro):'');
		$lcOrden='R.NOMMED ASC, R.NNOMED ASC';

		$laMedicosAux = $goDb
			->select($laCampos)
			->from('RIARGMN3 R')
			->where($lcWhere)
			->orderBy($lcOrden)
			->getAll('array');

		if(!empty($tcEspecialidad)){
			foreach($laMedicosAux as $laMedico){
				if($laMedico['ESPECIALIDAD']==$tcEspecialidad || $laMedico['ESPECIALIDADES']>0){
					$laMedicos[]=$laMedico;
				}
			}
		}else{
			$laMedicos = $laMedicosAux;
		}

		return $laMedicos;
	}
}