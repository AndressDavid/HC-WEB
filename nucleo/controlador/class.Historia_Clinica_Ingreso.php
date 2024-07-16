<?php
namespace NUCLEO;

require_once ('class.Db.php');
require_once ('class.Ingreso.php');

use NUCLEO\Db;
use NUCLEO\Ingreso;

class Historia_Clinica_Ingreso
{
	public function datosIngreso($tnIngreso=0)
	{
		$loIngreso=new Ingreso;
		$loIngreso->cargarIngreso($tnIngreso);
		$loIngreso->obtenerPesoTalla();
		return [
			'nIngreso' => $loIngreso->nIngreso,
			'cTipId' => $loIngreso->cId,
			'nNumId' => $loIngreso->nId,
			'cNombre' => $loIngreso->oPaciente->getNombreCompleto(),
			'cSexo' => $loIngreso->oPaciente->cSexo,
			'cDescSexo' => $loIngreso->oPaciente->cDescSexo,
			'aEdad' => $loIngreso->aEdad,
			'cCodVia' => $loIngreso->cVia,
			'cDesVia' => $loIngreso->cDescVia,
			'nEntidad' => $loIngreso->nEntidad,
			'cPlan' => $loIngreso->cPlan,
			'cPlanDsc' => $loIngreso->obtenerDescripcionPlan(),
			'cSeccion' => $loIngreso->oHabitacion->cSeccion,
			'cHabita' => $loIngreso->oHabitacion->cHabitacion,
			'cTipoPlan' => $loIngreso->oPlanIngreso->cTipoEntidad,
			'nIngresoFecha' => $loIngreso->nIngresoFecha,
			'nIngresoHora' => $loIngreso->nIngresoHora,
			'nEgresoFecha' =>$loIngreso->nEgresoFecha,
			'nEgresoHora' =>$loIngreso->nEgresoHora,
			'nHistoria' => $loIngreso->oPaciente->nNumHistoria,
			'cPesoUnidad' => (!empty(trim($loIngreso->cTipoPeso)) ? $loIngreso->nPeso . ' ' . $loIngreso->cTipoPeso :''),
			'cEstado' => $loIngreso->cEstado,
			'cRegistroMedicoTratante' => $loIngreso->oMedicoTratante['REGISTRO'],
			'cNombreMedicoTratante' => $loIngreso->oMedicoTratante['NOMBRE'],
			'cEspecialidadMedicoTratante' => $loIngreso->oMedicoTratante['ESPECIALIDAD'],
			'cNombreEspecialidadMedicoTratante' => $loIngreso->oMedicoTratante['ESPECIALIDAD_NOMBRE'],
		];
	}

	/*
	 *	Retorna true si no existe la HC. Si se presenta error al consultar retorna false.
	 */
	function validaExisteHC($tnIngreso=0, $tcVia='')
	{
		global $goDb;
		$lnIndice = 2;

		// Vía consulta externa
		if($tcVia == '02'){
			$laReturn = [
				'Mensaje'=>'',
				'Valido'=>true,
			];

		// Vías diferentes a consulta externa
		} elseif (in_array($tcVia, ['01','03','04','05','06'])) {

			$laHC = $goDb
				->count('A.HORHIS','CUENTA')
				->from('RIAHIS A')
				->where([
					'A.NROING'=>$tnIngreso,
					'A.INDICE'=>$lnIndice,
				])
				->get('array');

			if (is_array($laHC)) {
				if (($laHC['CUENTA']??0)>0) {
					$laHC = $goDb
						->select('A.FECHIS, A.HORHIS, TRIM(B.NOMMED) NOMMED, TRIM(B.NNOMED) NNOMED')
						->from('RIAHIS A')
						->leftJoin('RIARGMN B', 'A.USRHIS=B.USUARI', null)
						->where([
							'A.NROING'=>$tnIngreso,
							'A.INDICE'=>$lnIndice,
						])
						->get('array');

					$laReturn = [
						'Mensaje'=>'Ya existe una Historia Clinica para este paciente por el médico ' . ($laHC['NNOMED']??'') . ' ' . ($laHC['NOMMED']??''),
						'Valido'=>false,
						'Existe'=>true,
					];
				} else {
					$laReturn = [
						'Mensaje'=>'',
						'Valido'=>true,
					];
				}
			} else {
				$laReturn = [
					'Mensaje'=>'Error al consultar si existe la HC',
					'Valido'=>false,
				];
			}
			unset($laHC);
		}

		return $laReturn;
	}
}
