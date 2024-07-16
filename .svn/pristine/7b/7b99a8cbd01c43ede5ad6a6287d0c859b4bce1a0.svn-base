<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Doc_Ordenes.php';

use NUCLEO\Doc_Ordenes;

class Doc_OrdenesAmb
{
	protected $oDb;
	protected $cTituloVacios='';
	protected $cPlanAmbulatorio='';
	protected $cPlanIngreso='';
	protected $laDocumento = [];
	protected $aReporte = [
				'cTitulo' => '',
				'lMostrarEncabezado' => true,
				'lMostrarEncabezadoPrimeraPagina' => true,
				'lMostrarFechaRealizado' => true,
				'lMostrarViaCama' => false,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cPlan' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aFirmas' => [],
				'aNotas' => ['notas'=>false,],
			];


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }


	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*	Consulta los datos del documento desde la BD en el array $aDocumento */
	private function consultarDatos($taData)
	{

		$laPlanAmbulatorio = $this->oDb
			->select('TRIM(DSCCON) PLAN_ORDEN_AMBULATORIA')
			->from('ORDAMB AS A')
			->leftJoin('FACPLNC AS B', "TRIM(A.PLAORA)=TRIM(B.PLNCON)", null)
			->where([
					'TIDORA'=>$taData['cTipDocPac'],
					'NIDORA'=>$taData['nNumDocPac'],
					'CORORA'=>$taData['nConsecDoc'],
					'INDORA'=>1,
					])
			->get('array');

		if (is_array($laPlanAmbulatorio)){
			if (count($laPlanAmbulatorio)>0){
				$this->cPlanAmbulatorio = $laPlanAmbulatorio['PLAN_ORDEN_AMBULATORIA'];
			}
		}


		/* Fecha Hora de Ingreso y Egreso */
		$laEpicrisis = $this->oDb
			->select('FEIING, HORING, FEEING, HREING')
			->from('RIAINGL15')
			->where(['NIGING'=>$taData['nIngreso'],
					])
			->get('array');

		if (is_array($laEpicrisis)){
			if (count($laEpicrisis)>0){
				$this->laDocumento['nFechaIngreso'] = $laEpicrisis['FEIING'];
				$this->laDocumento['nHoraIngreso'] = $laEpicrisis['HORING'];
				$this->laDocumento['nFechaEgreso'] = $laEpicrisis['FEEING'];
				$this->laDocumento['nHoraEgreso'] = $laEpicrisis['HREING'];
			}
		}
	}


	/* Prepara array $aReporte con los datos para imprimir */
	private function prepararInforme($taData)
	{
		$this->cPlanIngreso = $taData['oIngrPaciente']->cPlanDescripcion;

		$laDatos = [
			'nIngreso'		=> $taData['nIngreso'],
			'cTipDocPac' 	=> $taData['cTipDocPac'],
			'cTipDocDesc'   => $taData['oIngrPaciente']->oPaciente->aTipoId['NOMBRE'],
			'cSexoPaciente' => $taData['oIngrPaciente']->oPaciente->cSexo,
			'nNumDocPac' 	=> $taData['nNumDocPac'],
			'cTipoDocum' 	=> '5000',
			'cTipoProgr' 	=> 'ORDA01A',
			'tFechaHora'	=> '',
			'nConsecCita'	=> $taData['nConsecCita'],
			'nConsecCons'	=> $taData['nConsecCons'],
			'nConsecEvol'	=> $taData['nConsecEvol'],
			'nConsecDoc'	=> $taData['nConsecDoc'],
			'cCUP'			=> '',
			'cCodVia'		=> $taData['oIngrPaciente']->cVia,
			'cDescVia'		=> $taData['oIngrPaciente']->cDescVia,
			'cSecHab'		=> $taData['cSecHab'],
			'cPlan'		    =>!empty($this->cPlanAmbulatorio) ? $this->cPlanAmbulatorio : $this->cPlanIngreso,
			'cNombre'		=> $taData['oIngrPaciente']->oPaciente->getNombresApellidos(),
			'nFechaIngreso' => $this->laDocumento['nFechaIngreso'],
			'nFechaEgreso'  => $this->laDocumento['nFechaEgreso'],
			'cFechaRealizado' => AplicacionFunciones::formatFechaHora('fecha', substr(str_replace('/','',str_replace('-','',$taData['tFechaHora'])), 0, 8), '/'),
		];
		$laOrdenes = (new Doc_Ordenes())->retornarDocumento($laDatos, 2);
		$laTr=[];

		//ORDENES AMBULATORIAS UNA A UNA
		if (count($laOrdenes['datos'])>0){
			$lnKey=1;
			$laTr['aCuerpo']=[];
			foreach ($laOrdenes['datos'] as $laCartaO){
				if (count($laCartaO['cuerpo'])>0){
					if($lnKey==1){
						$this->aReporte['cTitulo']=$laCartaO['titulop'];
						$this->aReporte['cPlan']=!empty($this->cPlanAmbulatorio) ? $this->cPlanAmbulatorio : $this->cPlanIngreso;
						$this->aReporte['lMostrarEncabezadoPrimeraPagina']=$laCartaO['encabezado'];
					} else {
						$laTr['aCuerpo'][] = ['saltop', ['titulo'=>$laCartaO['titulop'],'encabezado'=>$laCartaO['encabezado']]];
					}

					if (!empty($laCartaO['titulo']??'')) $laTr['aCuerpo'][] = ['titulo1', $laCartaO['titulo']];

					$lcSubTitulo=$laCartaO['subtitulo']??'';
					if (!empty($lcSubTitulo)) $laTr['aCuerpo'][] = ['titulo1', $lcSubTitulo];

					$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $laCartaO['cuerpo']);

					if ($laCartaO['firma']) {
						$laTr['aCuerpo'][] = ['firmas', substr($laCartaO['titulop'],0,11)=='INCAPACIDAD' ? [$laOrdenes['firmaCC']] : [$laOrdenes['firma']]];
					}
					$lnKey++;
				}
			}
		}

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

}