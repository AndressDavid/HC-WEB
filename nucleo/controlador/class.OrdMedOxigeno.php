<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Cobros.php';

use NUCLEO\Db;
use NUCLEO\AplicacionFunciones;
use NUCLEO\Cobros;

class OrdMedOxigeno
{
	public $aIngreso = [];
	public $lGrabarPorHoras = false;
	public $nMinutos_Limite_Cobro = 0;
	public $lCobrar_Consumos = true;
	public $aTiposUsuario = [];
	public $lPuedeFormular = false;
	public $nPrimerDia = 0;
	public $lPrimeraFormula = false;
	public $bReqAval = false;
	public $lGuardadoEnEvoluc = false;

	public $nEstado = 0;
	public $cEstadoGraba = '';
	public $cCodCup = '';
	public $cRefProc = '';
	public $cDescRef = '';
	public $nDosis = 0;
	public $cUnidadDosis = '';
	public $cUnidadDosisDsc = 'L/min';
	public $cObservaciones = '';
	public $cOxigeno = '';
	public $nConsFormula = 0;
	public $cTexto = '';

	public $cOxigenoAntes = '';
	public $cCodCupAntes = '';
	public $cRefProcAntes = '';
	public $nDosisAntes = 0;
	public $cUnidadDosisAntes = '';
	public $cObservacionesAntes = '';
	public $nConsFormulaAntes = 0;
	public $nEstadoAntes = 0;
	public $cEstadoGrabaAntes = '';
	public $nFechaAntes = 0;


	/*
	 *	Retorna lista de métodos activos para administrar oxígeno
	 *	@return array, lista de métodos configurados en TABMAE
	 */
	public function listaMetodos()
	{
		global $goDb;
		$laCups = $goDb
			->select('trim(CL3TMA) AS CODREF, trim(DE1TMA) AS DESCUP, trim(OP2TMA) AS CODCUP')
			->from('TABMAE')
			->where('TIPTMA=\'FORMEDIC\' AND CL1TMA=\'OXIGENO\' AND CL2TMA=\'CUPS\' AND ESTTMA=\'\'')
			->orderBy('CL3TMA')
			->getAll('array');
		return is_array($laCups)?$laCups:[];
	}

	/*
	 *	Establece la configuración para el ingreso
	 *	@param $taIngreso objeto class.Historia_Clinica_Ingreso, datos del ingreso
	 *	@return array asociativo, últimos datos de formulación de oxígeno
	 */
	public function obtenerConfigIng($taIngreso)
    {
		global $goDb;
		$lcVia = $taIngreso['cCodVia'];
		$this->aIngreso=$taIngreso;
		$this->nMinutos_Limite_Cobro = $goDb->obtenerTabMae1('OP3TMA', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'MINUCBR','ESTTMA'=>''], null, 0);
		$this->lGrabarPorHoras = $goDb->obtenerTabMae1('OP1TMA', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'GRABHORA','ESTTMA'=>''], null, '0')=='1';
		if(!$this->lGrabarPorHoras){
			$llGrabarPorHoras = '$this->lGrabarPorHoras='.$goDb->obtenerTabMae1('TRIM(OP5TMA)', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'GRABHORA','ESTTMA'=>''], null, 'false').';';
			eval($llGrabarPorHoras);
		}

		$this->seDebeCobrar($taIngreso['cCodVia'], $taIngreso['cSeccion']);
		$this->aTiposUsuario = explode(',',$goDb->obtenerTabMae1('TRIM(DE1TMA)', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'TIPOUSU','ESTTMA'=>''], null, ''));
		$this->lPuedeFormular = in_array((isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario() : 0), $this->aTiposUsuario);

		// Día de la primer formulación de oxígeno
		$laQuery = $goDb
			->select('FECRDO')
			->from('RIAFARDO')
			->where('INGRDO','=',$taIngreso['nIngreso'])
			->notWhere('ESCRDO','=','A')
			->orderBy('FECRDO ASC, HORRDO ASC')
			->get('array');
		$this->nPrimerDia = $goDb->numRows()>0 ? $laQuery['FECRDO'] : 0;

		// Verifica si es la primera formulación
		$laQuery = $goDb
			->select('DOSRDO,CUPRDO,REFRDO,UDORDO,OBSRDO,CFRRDO,ESTRDO,ESCRDO,FECRDO')
			->from('RIAFARDO')
			->where('INGRDO','=',$taIngreso['nIngreso'])
			->notIn('ESCRDO',['A','N'])
			->orderBy('FECRDO DESC, HORRDO DESC')
			->get('array');
		$this->lPrimeraFormula = $goDb->numRows()==0;

		if(!$this->lPrimeraFormula){
			$this->cOxigeno = $laQuery['DOSRDO']==0? 'N': 'S';
			$this->cCodCup = trim($laQuery['CUPRDO']);
			$this->cRefProc = trim($laQuery['REFRDO']);
			$this->nDosis = $laQuery['DOSRDO'];
			$this->cUnidadDosis = trim($laQuery['UDORDO']);
			$this->cObservaciones = trim($laQuery['OBSRDO']);
			$this->nConsFormula = $laQuery['CFRRDO'];
			$this->nEstado = $laQuery['ESTRDO'];

			$this->cOxigenoAntes = $this->cOxigeno;
			$this->cCodCupAntes = $this->cCodCup;
			$this->cRefProcAntes = $this->cRefProc;
			$this->nDosisAntes = $this->nDosis;
			$this->cUnidadDosisAntes = $this->cUnidadDosis;
			$this->cObservacionesAntes = $this->cObservaciones;
			$this->nConsFormulaAntes = $this->nConsFormula;
			$this->nEstadoAntes = $this->nEstado;
			$this->cEstadoGrabaAntes = $laQuery['ESCRDO'];
			$this->nFechaAntes = $laQuery['FECRDO'];
		}

		$this->bReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
		$this->cEstadoGraba = $this->bReqAval? 'A': ($this->lCobrar_Consumos? 'C': 'U');
    }

	/*
	 *	Retorna última formulación de oxígeno
	 *	@param $tnIngreso número, ingreso del paciente
	 *	@return array asociativo, últimos datos de formulación de oxígeno
	 */
	public function ultimaFormula($tnIngreso)
	{
		global $goDb;
		$laUltimaFormula=[];
		$laParametros = $goDb
			->select('CFRRDO CONS_FORMULA, CEVRDO CONS_EVOL, DOSRDO DOSIS_FORMULA, TRIM(CUPRDO) CUPS, TRIM(REFRDO) REFERENCIA_CUPS, TRIM(UDORDO) UNIDAD_DOSIS, TRIM(OBSRDO) OBSERVACIONES, ESTRDO ESTADO, FECRDO FECHA_ANTES, ESCRDO ESTADO_GRABACION_ANTES')
			->from('RIAFARDO')
			->where('INGRDO', '=', $tnIngreso)
			->notIn('ESCRDO', ['A','N'])
			->orderBy('FECRDO DESC, HORRDO DESC')
			->get('array');
		
		if ($goDb->numRows()>0){
			$laUltimaFormula=[
					'CONS_EVOL'=>$laParametros['CONS_EVOL'],
					'CONS_FORMULA'=>$laParametros['CONS_FORMULA'],
					'CUPS'=>$laParametros['CUPS'],
					'DOSIS_FORMULA'=>number_format($laParametros['DOSIS_FORMULA'],2,'.',''),
					'ESTADO'=>$laParametros['ESTADO'],
					'ESTADO_GRABACION_ANTES'=>$laParametros['ESTADO_GRABACION_ANTES'],
					'FECHA_ANTES'=>$laParametros['FECHA_ANTES'],
					'OBSERVACIONES'=>$laParametros['OBSERVACIONES'],
					'REFERENCIA_CUPS'=>$laParametros['REFERENCIA_CUPS'],
					'UNIDAD_DOSIS'=>$laParametros['UNIDAD_DOSIS'],
				];
		}		
			
		return $laUltimaFormula;
	}

	/*
	 *	Guarda la formulación de oxígeno y llama a cobro
	 *		Antes se debe llamar a $this->obtenerConfigIng($taIngreso) y establecer valor a las propiedades:
	 *		$this->cCodCup, $this->cRefProc, $this->nEstado, $this->cEstadoGraba, $this->nDosis, $this->cUnidadDosis y $this->cObservaciones
	 *	@param $taMedico array asociativo con datos del médico que ordena:
	 *		- regmed: registro médico
	 *		- codesp: código de la especialidad
	 *	@param $taLog array asociativo con los siguientes elementos
	 *		- usuario: string, usuario
	 *		- programa: string, programa desde el que se guarda
	 *		- fecha: integer, fecha en formato YYYYMMD
	 *		- hora: integer, hora en formato HHmmSS
	 *	@param $tnConsEvoluc número, Consecutivo de evolución
	 *	@param $tnConsFormula número, Consecutivo de fórmula (si no se envía se calcula)
	 */
	public function guardarFormulacionOxigeno($taMedico, $taLog, $tnConsEvoluc, $tnConsFormula)
	{
		if(!$this->lGuardadoEnEvoluc){
			return;
		}
		global $goDb;

		$tnConsFormula = is_numeric($tnConsFormula)? $tnConsFormula: $this->Obtener_Cns_Formula($this->aIngreso['nIngreso']);
		if(!is_array($taLog) || count($taLog)==0){
			$taLog = $this->obtenerLog();
		}
		$lcEstGrb = (!$this->lCobrar_Consumos && $this->cEstadoGraba=='C')? 'U':
					(($this->lCobrar_Consumos && $this->cEstadoGrabaAntes=='U' AND $this->cEstadoGraba=='M')? 'C':
					$this->cEstadoGraba);

		// Consulta si en la evolución ya se había guardado
		$laTempOx = $goDb
			->select('COUNT(*) AS CTAFOROX')
			->from('RIAFARDO')
			->where([
				'INGRDO'=>$this->aIngreso['nIngreso'],
				'CFRRDO'=>$tnConsFormula,
				'CEVRDO'=>$tnConsEvoluc,
			])
			->get('array');

		if($goDb->numRows()>0){
			if($laTempOx['CTAFOROX']==0){
				// Inserta el registro
				$laDatos = [
					'INGRDO'=>$this->aIngreso['nIngreso'],
					'CFRRDO'=>$tnConsFormula,
					'CEVRDO'=>$tnConsEvoluc,
					'CUPRDO'=>$this->cCodCup,
					'REFRDO'=>$this->cRefProc,
					'DOSRDO'=>$this->nDosis,
					'UDORDO'=>$this->cUnidadDosis,
					'OBSRDO'=>$this->cObservaciones,
					'ESTRDO'=>$this->nEstado,
					'ESCRDO'=>$lcEstGrb,
					'USRRDO'=>$taLog['usuario'],
					'PGMRDO'=>$taLog['programa'],
					'FECRDO'=>$taLog['fecha'],
					'HORRDO'=>$taLog['hora'],
				];
				$laRta = $goDb
					->from('RIAFARDO')
					->insertar($laDatos);
			}else{
				$laDatos = [
					'CUPRDO'=>$this->cCodCup,
					'REFRDO'=>$this->cRefProc,
					'ESTRDO'=>$this->nEstado,
					'DOSRDO'=>$this->nDosis,
					'UDORDO'=>$this->cUnidadDosis,
					'OBSRDO'=>$this->cObservaciones,
					'ESCRDO'=>$lcEstGrb,
					'USRRDO'=>$taLog['usuario'],
					'PGMRDO'=>$taLog['programa'],
					'FECRDO'=>$taLog['fecha'],
					'HORRDO'=>$taLog['hora'],
				];
				$laRta = $goDb
					->from('RIAFARDO')
					->where([
						'INGRDO'=>$this->aIngreso['nIngreso'],
						'CFRRDO'=>$tnConsFormula,
						'CEVRDO'=>$tnConsEvoluc,
					])
					->actualizar($laDatos);
			}
			$this->lGuardadoEnBD = true;
		}

		if(!$this->bReqAval){
			$this->CobroOxigeno($taMedico, $taLog);
		}
	}

	/*
	 *	Retorna consecutivo de fórmula para oxígeno
	 *	@param $tnIngreso número, ingreso del paciente
	 *	@return número, nuevo consecutivo
	 */
	public function Obtener_Cns_Formula($tnIngreso)
	{
		global $goDb;
		$laCnsForm = $goDb
			->select('CDNFAR,FECFAR')
			->from('RIAFARM')
			->where('ingfar', '=', $tnIngreso)
			->orderBy('CDNFAR DESC')
			->get('array');
		if($goDb->numRows()>0){
			$lnFechaSis = str_replace('-','',str_replace('/','',substr($goDb->fechaHoraSistema(),0,10)));
			if($laCnsForm['FECFAR']==$lnFechaSis){
				$lnCnsFor = $laCnsForm['CDNFAR'];
				$llNuevoCnsFor = false;
			}else{
				$lnCnsFor = $laCnsForm['CDNFAR'] + 1;
				$llNuevoCnsFor = true;
			}
		}else{
			$lnCnsFor = 1;
			$llNuevoCnsFor = true;
		}
		return $lnCnsFor;
	}

	/*
	 *	Retorna la descripción del método de oxigenación
	 *	@param $tcMetodo string, código del método o referencia
	 *	@return string, con la descripción del método seleccionado
	 */
	public function obtenerDescripcion($tcMetodo='')
	{
		global $goDb;
		$laMetodo = $goDb
			->select('trim(DE1TMA) AS DESCUP, trim(OP2TMA) AS CODCUP')
			->from('TABMAE')
			->where("TIPTMA='FORMEDIC' AND CL1TMA='OXIGENO' AND CL2TMA='CUPS' AND ESTTMA=''")
			->where(['CL3TMA'=>$tcMetodo])
			->get('array');
		$this->cDescRef = $goDb->numRows()>0 ? $laMetodo['DESCUP'] : '';
		return $this->cDescRef;
	}

	/*
	 *	Valida si se debe cobrar el oxígeno
	 *	@param $tcViaIngreso string, código de la vía de ubicación del paciente
	 *	@param $tcSeccion string, sección de ubicación paciente
	 *	@return boolean
	 */
	public function seDebeCobrar($tcViaIngreso='', $tcSeccion='')
	{
		// Necesarios para evaluar la condición almacenada en FORMEDIC - OXIGENO - CNDCOBRA
		$lcVia = $tcViaIngreso;
		$lcSecCam = $tcSeccion;

		global $goDb;
		$lcCondicion = '$this->lCobrar_Consumos='.$goDb->obtenerTabMae1('TRIM(OP5TMA)', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'CNDCOBRA','ESTTMA'=>''], null, 'true').';';
		eval($lcCondicion);

		return $this->lCobrar_Consumos;
	}

	/*
	 *	Llama cobro de oxígeno
	 *		Antes se debe llamar a $this->obtenerConfigIng($toIngreso) y establecer valor a las propiedades: $this->nEstado y $this->cEstadoGraba
	 *	@param $taMedico array asociativo con datos del médico que ordena:
	 *		- regmed: registro médico
	 *		- codesp: código de la especialidad
	 *	@param $taLog array asociativo con los siguientes elementos
	 *		- usuario: string, usuario
	 *		- programa: string, programa desde el que se guarda
	 *		- fecha: integer, fecha en formato YYYYMMD
	 *		- hora: integer, hora en formato HHmmSS
	 */
	public function CobroOxigeno($taMedico, $taLog=[])
	{
		//if(!$this->lCobrar_Consumos) return;
		global $goDb;

		if(!is_array($taLog) || count($taLog)==0){
			$taLog = $this->obtenerLog();
		}
		$ltFechaHora = new \DateTime(AplicacionFunciones::formatFechaHora($tcFormato='fecha', $taLog['fecha']*1000000+$taLog['hora']));
		$ltFechaHora->sub(new \DateInterval('P1D'));
		$lnFechaAnterior = $ltFechaHora->format('Ymd');

		// *** Cobrar lo de los días anteriores ****

		$laTempOx = $goDb
			->select('CFRRDO, CEVRDO, CUPRDO, ESCRDO, HORRDO, FECRDO')
			->from('RIAFARDO')
			->where(['INGRDO'=>$this->aIngreso['nIngreso']])
			->where('FECRDO', '<', $taLog['fecha'])
			->orderBy('FECRDO, HORRDO')
			->getAll('array');
		if($goDb->numRows()>0){
			$laFechas = $goDb->distinct()
				->select('FECRDO')
				->from('RIAFARDO')
				->where([
					'INGRDO'=>$this->aIngreso['nIngreso'],
					'ESCRDO'=>'C',
				])
				->where('FECRDO', '<', $taLog['fecha'])
				->orderBy('FECRDO')
				->getAll('array');
			if($goDb->numRows()>0){
				foreach($laFechas as $laDatoFecha){
					$lnNumConsumo=0;
					$lnNumGrabSusp=0;
					foreach($laTempOx as $laSumas){
						if($laSumas['FECRDO']==$laDatoFecha['FECRDO']){
							if($laSumas['ESCRDO']=='C'){
								$lnNumConsumo++;
								if ($lnNumConsumo==1){
									$lcCupRdo = $laSumas['CUPRDO'];
									$lcCfrRdo = $laSumas['CFRRDO'];
									$lcCevRdo = $laSumas['CEVRDO'];
									$lcHoraGr = $laSumas['HORRDO'];
								}
							}elseif(in_array($laSumas['ESCRDO'],['G','S'])){
								$lnNumGrabSusp++;
							}
						}
					}
					if($lnNumConsumo>0){
						if($this->lGrabarPorHoras || $lnNumGrabSusp>0){
							$lnHoras = $this->Diferencia_Horas($lcHoraGr, $taLog['hora'], true);
							$lnHoras = $lnHoras>24?24:$lnHoras;
						}else{
							$lnHoras = 24;
						}
						$laDatos = [
							'ingreso'		=>$this->aIngreso['nIngreso'],
							'numIdPac'		=>$this->aIngreso['nNumId'],
							'codCup'		=>$lcCupRdo,
							'codVia'		=>$this->aIngreso['cCodVia'],
							'codPlan'		=>$this->aIngreso['cPlan'],
							'regMedOrdena'	=>$taMedico['regmed'],
							'regMedRealiza'	=>'',
							'espMedRealiza'	=>'',
							'secCama'		=>trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
							'cnsCita'		=>'0',
							'portatil'		=>'',
							'cantidad'		=>$lnHoras,
						];
						$lbRet = (new Cobros())->cobrarProcedimientoCantidad($laDatos);

						// Cambia estado de consumo del registro de oxígeno
						$llResultado = $goDb
							->tabla('RIAFARDO')
							->where([
								'INGRDO'=>$this->aIngreso['nIngreso'],
								'CFRRDO'=>$lcCfrRdo,
								'CEVRDO'=>$lcCevRdo,
								'CUPRDO'=>$lcCupRdo,
							])
							->actualizar([
								'ESCRDO'=>'G',
								'NHORDO'=>$lnHoras,
								'UMORDO'=>$taLog['usuario'],
								'PMORDO'=>$taLog['programa'],
								'FMORDO'=>$taLog['fecha'],
								'HMORDO'=>$taLog['hora'],
							]);
					}
				}
			}
		}


		// *** Si se cambia cup o si se suspende, cobrar lo del día ****

		if($this->nEstado==14 || (!empty($this->cCodCupAntes) AND $this->cCodCup!==$this->cCodCupAntes)){
			$laWhere=['INGRDO'=>$this->aIngreso['nIngreso'], 'FECRDO'=>$taLog['fecha'], 'ESCRDO'=>'C'];
			if($this->nEstado!==14){
				$laWhere['CUPRDO']=$this->cCodCupAntes;
			}
			$laTempOx = $goDb
				->select('CFRRDO, CEVRDO, CUPRDO, ESCRDO, HORRDO, FECRDO')
				->from('RIAFARDO')
				->where($laWhere)
				->orderBy('HORRDO')
				->getAll('array');
			if($goDb->numRows()>0){
				// Se graba por horas
				$lnHoras = $this->Diferencia_Horas($laTempOx[0]['HORRDO'], $taLog['hora']);
				$laDatos = [
					'ingreso'		=>$this->aIngreso['nIngreso'],
					'numIdPac'		=>$this->aIngreso['nNumId'],
					'codCup'		=>$laTempOx[0]['CUPRDO'],
					'codVia'		=>$this->aIngreso['cCodVia'],
					'codPlan'		=>$this->aIngreso['cPlan'],
					'regMedOrdena'	=>$taMedico['regmed'],
					'regMedRealiza'	=>'',
					'espMedRealiza'	=>'',
					'secCama'		=>trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
					'cnsCita'		=>'0',
					'portatil'		=>'',
					'cantidad'		=>$lnHoras,
				];
				$lbRet = (new Cobros())->cobrarProcedimientoCantidad($laDatos);

				// Cambia estado de consumo del registro de oxígeno
				$llResultado = $goDb
					->tabla('RIAFARDO')
					->where([
						'INGRDO'=>$this->aIngreso['nIngreso'],
						'CFRRDO'=>$laTempOx[0]['CFRRDO'],
						'CEVRDO'=>$laTempOx[0]['CEVRDO'],
						'CUPRDO'=>$laTempOx[0]['CUPRDO'],
					])
					->actualizar([
						'ESCRDO'=>'G',
						'NHORDO'=>$lnHoras,
						'UMORDO'=>$taLog['usuario'],
						'PMORDO'=>$taLog['programa'],
						'FMORDO'=>$taLog['fecha'],
						'HMORDO'=>$taLog['hora'],
					]);
			}
		}
	}

	/*
	 *	Retorna el log para guardar
	 *	@return array asociativo, con valores para usuario, programa, fecha y hora
	 */
	private function obtenerLog()
	{
		global $goDb;
		$ltAhora = $goDb->fechaHoraSistema();
		return [
			'usuario'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
			'programa'=>'CBROXIWEB',
			'fecha'=>str_replace('-','',str_replace('/','',substr($ltAhora,0,10))),
			'hora'=>str_replace(':','',substr($ltAhora,11,8)),
		];
	}

	/*
	 *	Valida si se debe cobrar el oxígeno
	 *	@param $tnHoraInicial numero, hora inicial de consumo
	 *	@param $tnHoraFinal numero, hora final de consumo
	 *	@param $tlDiaDif boolean, se tiene en cuenta la diferencia de días?
	 *	@return numero, diferencia en horas, entre las dos horas enviadas
	 */
	private function Diferencia_Horas($tnHoraInicial=0, $tnHoraFinal=0, $tlDiaDif=false)
	{
		$lnDifHoras = 0;
		if($tlDiaDif && $tnHoraInicial>$tnHoraFinal){
			$lnTemp = $tnHoraInicial;
			$tnHoraInicial = $tnHoraFinal;
			$tnHoraFinal = $lnTemp;
		}
		$ltFecha1 = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora', 20000101 . str_pad($tnHoraInicial,6,'0',STR_PAD_LEFT)));
		$ltFecha2 = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora', ($tlDiaDif?20000102:20000101) . str_pad($tnHoraFinal,6,'0',STR_PAD_LEFT)));
		$loIntervalo = $ltFecha1->diff($ltFecha2);
		$lnDifHoras = ($loIntervalo->d * 24) + $loIntervalo->h + ($loIntervalo->i > $this->nMinutos_Limite_Cobro? 1: 0);
		return $lnDifHoras;
	}
}