<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';
require_once __DIR__ . '/class.Conciliacion.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once __DIR__ . '/class.Doc_NIHSS.php';
require_once __DIR__ . '/class.Diagnostico.php';
require_once __DIR__ . '/class.OrdenHospitalizacion.php';
require_once __DIR__ . '/class.EscalasRiesgoSangrado.php';
require_once __DIR__ . '/class.Cup.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.Especialidades.php';
require_once __DIR__ . '/class.OrdMedOxigeno.php';
require_once __DIR__ . '/class.ConsultaUrgencias.php';
require_once __DIR__ . '/class.EscalaActividadFisica.php';
require_once __DIR__ . '/class.Historia_Clinica.php';
require_once __DIR__ . '/class.EscalaSadPersons.php';
require_once __DIR__ . '/class.Cobros.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\Historia_Clinica_Ingreso;
use NUCLEO\ParametrosConsulta;
use NUCLEO\Conciliacion;
use NUCLEO\FormulacionParametros;
use NUCLEO\Doc_NIHSS;
use NUCLEO\Diagnostico;
use NUCLEO\OrdenHospitalizacion;
use NUCLEO\EscalasRiesgoSangrado;
use NUCLEO\Cup;
use NUCLEO\Especialidades;
use NUCLEO\OrdMedOxigeno;
use NUCLEO\ConsultaUrgencias;
use NUCLEO\EscalaActividadFisica;
use NUCLEO\Historia_Clinica;
use NUCLEO\EscalaSadPersons;
use NUCLEO\Cobros;
class Evoluciones
{
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $cRegMed = '';
	protected $cEspecialidad = '';
	protected $cTipoUsuario = '';
	protected $cCodPro = '';
	protected $cConEvo = '';
	protected $nConCon = 0;
	protected $nConCit = 0;
	protected $nConEpd = 0;
	protected $nConEvo = 0;
	protected $nConAval = 0;
	protected $cTipoEvol = '';
	protected $cInicialEvol = '';
	protected $cSL;
	protected $cConductaSeguir = '';
	protected $cDescripcionConducta = '';
	protected $cDxPpal = '';
	protected $cClaseDiagnosticoPrincipal = '';
	protected $cCupsCuidadoDiario = '';
	protected $nTiemposEntreEvoluciones = 0;
	protected $cCupsTratanteCuidado = '';
	protected $cCupsConsultaCuidado = '';
	protected $nValidarHorasCuidado = 0;
	protected $nValidarDiasCuidado = 0;
	protected $cEspecialidadMedicotratante = '';
	protected $aANTPAC = [];
	protected $aANTPAD = [];
	protected $aINFEDE = [];
	protected $aINFECA = [];
	protected $aEVOLUC = [];
	protected $aEVOUNI = [];
	protected $aANAEPI = [];
	protected $aRIAORDUP = [];
	protected $aRIAORD = [];
	protected $aRIADET = [];
	protected $aRIAHISL0 = [];
	protected $aIngreso = [];
	protected $aViasCenso = [];
	protected $aREINDE = [];
	protected $aREINOB = [];
	protected $aCUPGRA = [];
	protected $oHcIng = null;
	protected $oIngreso = null;
	protected $bReqAval = false;
	protected $lGuardarEPD = false;
	protected $lPacienteUrgencias = false;
	protected $ParaAvalar = false;

	protected $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->oHcIng = new Historia_Clinica_Ingreso();
		$this->bReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
		$this->oIngreso = new Ingreso();
		$loParamConsulta = new ParametrosConsulta();
		$this->aViasCenso=$loParamConsulta->consultarViasCenso();
	}

	public function verificarEvolucion($taDatos=[])
	{
		$this->IniciaDatosIngreso($taDatos['Ingreso'],$taDatos['Tipo']);
		$this->nConAval = $taDatos['nConCons'];
		$this->ParaAvalar = ($taDatos['PorAvalar']??'No')=='Si';

		$oTabmae = $this->oDb->ObtenerTabMae('OP1TMA', 'PAREVWEB', ['CL1TMA'=>'TRASLADO','ESTTMA'=>'']);
		$lcVerifica = trim(AplicacionFunciones::getValue($oTabmae, 'OP1TMA', '')) ;

		if($lcVerifica == '1'){
			if(!($this->aIngreso['cSeccion'] == $taDatos['Seccion']) && !($taDatos['Analisis']['Seguir']=='01')){
				if (!$this->verificarSeccion($taDatos['Seccion'])){
					return $this->aError;
				}
			}
		}

		if (isset($taDatos['Diagnostico'])) {
			if(!$this->verificarDiagnosticos($taDatos['Diagnostico']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['EvolucionP'])) {
			if(!$this->verificarEvolucionPiso($taDatos['EvolucionP'])) {
				return $this->aError;
			}
		}

		$taDatos['Interpretacion']=$taDatos['Interpretacion']??[];
		if(!$this->verificarInterpretacion($taDatos['Interpretacion'], $taDatos['Analisis']['Seguir'] ?? '') ) {
			return $this->aError;
		}

		if (isset($taDatos['Conciliacion'])) {
			if(($taDatos['Conciliacion']['Modifica']??'false')=='true'){
				if(!$this->verificarConciliacion($taDatos['Conciliacion']))  {
					return $this->aError;
				}
			}
		}

		if (isset($taDatos['Nihss'])) {
			if(!empty($taDatos['Nihss']['TotalN'])){
				if(!$this->verificarNIHSS($taDatos['Nihss']))  {
					return $this->aError;
				}
			}
		}

		if (isset($taDatos['RegistroUci'])) {
			if(!$this->verificarRegistroUci($taDatos['RegistroUci']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['ProcedimientosUci'])) {
			if(!$this->verificarProcedimientosUci($taDatos['ProcedimientosUci']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['Analisis'])) {
			if(!$this->verificarAnalisis($taDatos['Analisis'],$taDatos['Tipo'])){
				return $this->aError;
			}
			$taDatos['Analisis']['OrdenHospitalizacion']=$taDatos['Analisis']['OrdenHospitalizacion']??[];
			if(count($taDatos['Analisis']['OrdenHospitalizacion'])>0){
				if(!$this->verificarOrdenHospitalizacion($taDatos['Analisis']['OrdenHospitalizacion'],$taDatos['Analisis']['Seguir']))  {
					return $this->aError;
				}
			}
		}

		if (isset($taDatos['RecomendacionesUCC'])) {
			if(!$this->verificarRecomendacionesUCC($taDatos['RecomendacionesUCC'],$taDatos['Analisis']['Seguir'], $taDatos['Analisis']['Estado'])){
				return $this->aError;
			}
		}

		if (isset($taDatos['Eventualidad'])) {
			if(!$this->verificarEventualidad($taDatos['Eventualidad']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['Actividadfisica'])) {
			if(!$this->verificarActividadfisica($taDatos['Actividadfisica']))  {
				return $this->aError;
			}
		}

		if(!empty($taDatos['DatosSadPersons'])){
			$loObjEV = new EscalaSadPersons();
			$this->aError = $loObjEV->validarEscalaSadPersons($taDatos['Diagnostico'], $taDatos['DatosSadPersons']);
			return $this->aError;
		}
		return $this->aError;
	}

	function verificarActividadfisica($taDatos=[])
	{
		$loActividadFisica = new EscalaActividadFisica();
		$this->aError = $loActividadFisica->validacion($taDatos);
		return $this->aError['Valido'];
	}
	function verificarDiagnosticos($taDatos=[])
	{
		$loDiagnostico = new Diagnostico();
		$this->aError = $loDiagnostico->validacion($taDatos, $this->aIngreso['cCodVia'], '');
		return $this->aError['Valido'];
	}

	function verificarSeccion($tcSeccionInicial='')
	{
		$oTabmae = $this->oDb->ObtenerTabMae('OP2TMA', 'SECHAB', ['CL1TMA'=>$tcSeccionInicial,'ESTTMA'=>'']);
		$lcServicioInicial = trim(AplicacionFunciones::getValue($oTabmae, 'OP2TMA', '')) ;

		$oTabmae = $this->oDb->ObtenerTabMae('OP2TMA', 'SECHAB', ['CL1TMA'=>$this->aIngreso['cSeccion'],'ESTTMA'=>'']);
		$lcServicioFinal = trim( AplicacionFunciones::getValue($oTabmae, 'OP2TMA', '') );

		if($lcServicioInicial !== $lcServicioFinal){
			$this->aError = [
				'Mensaje'=>'Existe un Traslado, Favor actualizar conducta a seguir cambio ',
				'Objeto'=>'selConductaSeguirAct-'. $this->aIngreso['cSeccion'],
				'Valido'=>false,
			];
		}
		return $this->aError['Valido'];
	}

	function obtenerConducta($taDatos=[], $tnTipo='P')
	{
		if (!empty($taDatos['Analisis']['Seguir'])){
			$lcSeccion = $this->aIngreso['cSeccion'];
			$lcTipo = 'EVPISO';
			if($tnTipo=='C' || $tnTipo=='V' ||  ($tnTipo=='P' && ($lcSeccion=='CC' || $lcSeccion=='CV' || $lcSeccion=='CI' || $lcSeccion=='CA' ))){
				$lcTipo= 'EVUNID';
			}
			$this->cConductaSeguir = $taDatos['Analisis']['Seguir'];
			$loObjEV = new ParametrosConsulta();
			$loObjEV->ObtenerConductaSeguir($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia'],$this->aIngreso['cSeccion'],$lcTipo);
			$this->cDescripcionConducta = $loObjEV->tipoConductaSeguir($taDatos['Analisis']['Seguir'])['desc'];
		}
	}

	function verificarEvolucionPiso($taDatos=[])
	{
		// Verificación de información Objetivo - Subjetivo
		if(empty(trim($taDatos['Objetivo']))){
			$this->aError = [
				'Mensaje'=>'Objetivo - Subjetivo es un dato obligatorio',
				'Objeto'=>'edtObjetivo',
				'Valido'=>false,
			];
		}
		return $this->aError['Valido'];
	}

	function verificarInterpretacion($taDatos=[], $tcConductaSeguir='')
	{
		foreach($taDatos as $lnKey=>$laInterpreta){
			//Valida que el registro se interprete
			if($tcConductaSeguir=='01'){
				if($laInterpreta['NORMAL']==0 && $laInterpreta['ANORMAL']==0 && $laInterpreta['OBLIGATORIO']=='SI'){
					$this->aError = [
						'Mensaje'=>'El Exámen ' . trim($laInterpreta['DESCRIPCION']) . ' NO ha sido Interpretado',
						'Objeto'=>'tblInterpretacion',
						'Valido'=>false,];
						break;
				}
			} else {
				if($laInterpreta['NORMAL']==0 && $laInterpreta['ANORMAL']==0 && $laInterpreta['OBLIGATORIO']=='SI' && $laInterpreta['ESPSOL']== $laInterpreta['MEDACT'] ){
					$this->aError = [
						'Mensaje'=>'El Exámen ' . trim($laInterpreta['DESCRIPCION']) . ' NO ha sido Interpretado',
						'Objeto'=>'tblInterpretacion',
						'Valido'=>false,];
						break;
				}
			}
			//Valida que el registro interpretado tenga observaciones
			if(($laInterpreta['NORMAL']==1 || $laInterpreta['ANORMAL']==1) && empty(trim($laInterpreta['OBSERVA']))){
				$this->aError = [
					'Mensaje'=>'Observaciones para el Exámen ' . trim($laInterpreta['DESCRIPCION']) . ' es obligatorio',
					'Objeto'=>'tblInterpretacion',
					 'Valido'=>false,];
						break;
			}
		}
		return $this->aError['Valido'];
	}

	function verificarConciliacion($taDatos=[])
	{
		$loObjEV = new Conciliacion();
		$this->aError = $loObjEV->verificarDatosC($taDatos,$this->aIngreso['aEdad']);
		return $this->aError['Valido'];
	}

	function verificarNIHSS($taDatos=[])
	{
		$loObjEV = new Doc_NIHSS();
		$this->aError = $loObjEV->verificarDatosN($taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaHasbled($taDx=[], $taDatos=[]){
		$loObjEV = new EscalasRiesgoSangrado();
		$this->aError = $loObjEV->validarEscalaHasbled($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaChadsvas($taDx=[], $taDatos=[]){
		$loObjEV = new EscalasRiesgoSangrado();
		$this->aError = $loObjEV->validarEscalaChadsvas($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaCrusade($taDx=[], $taDatos=[]){
		$loObjEV = new EscalasRiesgoSangrado();
		$this->aError = $loObjEV->validarEscalaCrusade($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarRegistroUci($taDatos=[])
	{
		if(empty(trim($taDatos['SubjetivoUci']))){
			$this->aError = [
				'Mensaje'=>'Subjetivo es un dato obligatorio',
				'Objeto'=>'edtSubjetivoUci',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		if(empty(trim($taDatos['ResultadosLaboratorioUci']))){
			$this->aError = [
				'Mensaje'=>'Resultados de laboratorio es un dato obligatorio',
				'Objeto'=>'edtResultadosLaboratorioUci',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		if(empty(trim($taDatos['ExamenFisicoUci']))){
			$this->aError = [
				'Mensaje'=>'Exámen físico es un dato obligatorio',
				'Objeto'=>'edtExamenFisicoUci',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		if(empty(trim($taDatos['ExamenSolicitarUci']))){
			$this->aError = [
				'Mensaje'=>'Examenes y procedimientos a solicitar es un dato obligatorio',
				'Objeto'=>'edtExamenSolicitarUci',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		if(empty(trim($taDatos['PronosticoUci']))){
			$this->aError = [
				'Mensaje'=>'Pronostico es un dato obligatorio',
				'Objeto'=>'edtPronosticoUci',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}
		return $this->aError['Valido'];
	}

	function verificarProcedimientosUci($taDatos=[])
	{
		return $this->aError['Valido'];
	}

	function verificarAnalisis($taDatos=[], $tnTipo='P')
	{
		// Valida dato plan de manejo
		if(empty(trim($taDatos['Manejo']))){
			$this->aError = [
				'Mensaje'=>'Plan de manejo es un dato obligatorio',
				'Objeto'=>'edtManejo',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Valida dato Analisis para epicrisis
		if(empty(trim($taDatos['Analisis'])) && $this->cTipoEvol !== 'ER'){
			$this->aError = [
				'Mensaje'=>'Analisis para epicrisis es un dato obligatorio',
				'Objeto'=>'edtAnalisis',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Valida dato Conducta a seguir
		$lcSeccion = $this->aIngreso['cSeccion'];
		$lcTipo= 'EVPISO';
		if($tnTipo=='C' || $tnTipo=='V' ||  ($tnTipo=='P' && ($lcSeccion=='CC' || $lcSeccion=='CV' || $lcSeccion=='CI' || $lcSeccion=='CA' ))){
			$lcTipo= 'EVUNID';
		}

		$loObjEV = new ParametrosConsulta();
		$loObjEV->ObtenerConductaSeguir($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia'],$this->aIngreso['cSeccion'],$lcTipo);
		$laResultado = $loObjEV->tipoConductaSeguir($taDatos['Seguir']);

		if(empty($laResultado)){
			$this->aError = [
				'Mensaje'=>'No existe tipo conducta a seguir en la base de datos',
				'Objeto'=>'selConductaSeguir',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Si la conducta a seguir es salida valida que exista estado de salida
		if(trim($taDatos['Seguir']) == '01' && trim($taDatos['Estado']) == '' ){
			$this->aError = [
				'Mensaje'=>'Estado salida es un dato obligatorio',
				'Objeto'=>'selEstado',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// si conducta a seguir no es salida valida que el estado de salida se encuentre vacio
		if(trim($taDatos['Seguir']) !== '01' && trim($taDatos['Estado']) !== '' ){
			$this->aError = [
				'Mensaje'=>'Error en el Estado salida',
				'Objeto'=>'selEstado',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// valida estado de salida
		if(!empty(trim($taDatos['Estado']))){
			$loObjEV->ObtenerEstadoSalida();
			$laResultado = $loObjEV->estadoSalida($taDatos['Estado']);
			if(empty($laResultado)){
				$this->aError = [
					'Mensaje'=>'No existe Estado de Salida en la base de datos',
					'Objeto'=>'selEstado',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Cuando estado de salida es fallece valida fecha y hora fallece y que exista diagnostico de fallece
		if(trim($taDatos['Estado']) == '002'){

			// Valida fecha y hora
			$lcFechaHoraFallece = date('Y-m-d H:i:s', strtotime($taDatos['FechaFallece'].$taDatos['HoraFallece']));
			$lcFechaHoraActual = date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre));

			if($lcFechaHoraFallece>$lcFechaHoraActual) {
				$this->aError = [
					'Mensaje'=>'El dato Fecha - Hora Fallece es mayor a la Fecha - Hora actual. Revise por favor!',
					'Objeto'=>'buscarDxFallece',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}

			if(trim($taDatos['cCodigoDxFallece']) == ''){
				$this->aError = [
					'Mensaje'=>'El dato Diagnóstico Fallece es obligatorio',
					'Objeto'=>'buscarDxFallece',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// valida si el estado de salida es diferente a fallece que no este diligenciado el diagnostico fallece
		if(trim($taDatos['Estado']) !== '002' && trim($taDatos['cCodigoDxFallece']) !== '' ){
			$this->aError = [
				'Mensaje'=>'Error en el Dato diagnóstico Fallece ',
				'Objeto'=>'buscarDxFallece',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		//Verificar Dx Fallece si trae la información
		if(trim($taDatos['Estado']) == '002'  && trim($taDatos['cCodigoDxFallece']) !== '' ){
			$lcCodigoDx = mb_substr($taDatos['cCodigoDxFallece'], 0, 5);
			$loDiagnostico = new Diagnostico();
			$this->aError['Valido'] = $loDiagnostico->buscarDX($lcCodigoDx);

			if(!$this->aError['Valido']){
				$this->aError = [
					'Mensaje'=>'Error en el diagnóstico Fallece Digitado. Revise por favor!',
					'Objeto'=>'buscarDxFallece',
					'Valido'=>false
				];
				return $this->aError['Valido'];
			}else{
				$this->aError['Valido'] = $loDiagnostico->buscarDxFallece($lcCodigoDx);

				if(!$this->aError['Valido']){
					$this->aError = [
						'Mensaje'=>'Error en la consulta de diagnóstico Fallece, revise por favor.',
						'Objeto'=>'buscarDxFallece',
						'Valido'=>false
					];
					return $this->aError['Valido'];
				}
			}
		}
		return $this->aError['Valido'];
	}

	function verificarOrdenHospitalizacion($taDatos=[], $tcConducta=''){

		if($tcConducta=='03' && count($taDatos)==0){
			$this->aError = [
					'Mensaje'=>'No existen datos para la Orden de Hospitalización, Revise por favor.',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false
			];
				return $this->aError['Valido'];
		}

		if($tcConducta !='03' && count($taDatos)>0){
			$this->aError = [
					'Mensaje'=>'Error en datos de Orden de Hospitalización, Revise por favor.',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false
			];
			return $this->aError['Valido'];
		}

		$loObjEV = new OrdenHospitalizacion();
		$this->aError = $loObjEV->validacion($taDatos);
		return $this->aError['Valido'];
	}

	function verificarRecomendacionesUCC($taDatos=[], $tcConductaSeguir='', $tcEstadoSalida='')
	{
		$llverifica = ($tcConductaSeguir=='01' && $tcEstadoSalida=='001');

		if($llverifica){
			if(!is_array($taDatos)){$taDatos=[];}
			if (count($taDatos)==0) {
				$this->aError = [
					'Mensaje'=>'Se requiere información de Recomendaciones UCC, Revise por favor.',
					'Objeto'=>'adicionarGrupoMedicamento',
					'Valido'=>false
				];
				return $this->aError['Valido'];
			}else{

				if($taDatos['Presion_S']<20 || $taDatos['Presion_S']>300){
					$this->aError = [
						'Mensaje'=>'El valor de la tensión arterial sistólica debe estar entre 40 y 300',
						'Objeto'=>'Presion_S',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Presion_D']<20 || $taDatos['Presion_D']>150){
					$this->aError = [
						'Mensaje'=>'El valor de la tensión arterial diastólica debe estar entre 20 y 150',
						'Objeto'=>'Presion_D',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Hemoglobi']<1 || $taDatos['Hemoglobi']>99){
					$this->aError = [
						'Mensaje'=>'Error en el rango de la Hemoglobina glicosilada, Revise por favor.',
						'Objeto'=>'Hemoglobi',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Glicem_An']<20|| $taDatos['Glicem_An']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango de la Glicemina en ayunas, Revise por favor.',
						'Objeto'=>'Glicem_An',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Glicem_Po']<20 || $taDatos['Glicem_Po']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango de la Glicemina POST carga 75 gr glucosa, Revise por favor.',
						'Objeto'=>'Glicem_Po',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				// Tabaquismo
				$taDatos['Tabaquism']=$taDatos['Tabaquism'] ?? 0;
				if($taDatos['Tabaquism']!=0 && $taDatos['Tabaquism']!=1){
					$this->aError = [
						'Mensaje'=>'Debe seleccionar dato Tabaquisismo, Revise por favor.',
						'Objeto'=>'Tabaquism',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Coleste_T']<10 || $taDatos['Coleste_T']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango del Colesterol Total, Revise por favor.',
						'Objeto'=>'Coleste_T',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Coleste_B']<10 || $taDatos['Coleste_B']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango del Colesterol HDL Bueno, Revise por favor.',
						'Objeto'=>'Coleste_B',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Coleste_M']<10 || $taDatos['Coleste_M']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango del Colesterol LDL Malo, Revise por favor.',
						'Objeto'=>'Coleste_M',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Triglicer']<10 || $taDatos['Triglicer']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango de los Trigliceridos, Revise por favor.',
						'Objeto'=>'Triglicer',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if($taDatos['Perimet_A']<10 || $taDatos['Perimet_A']>999){
					$this->aError = [
						'Mensaje'=>'Error en el rango del Perímetro Abdominal,Revise por favor.',
						'Objeto'=>'Perimet_A',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				$taDatos['Ejercicio']=$taDatos['Ejercicio'] ?? 0;
				if($taDatos['Ejercicio']!=0 && $taDatos['Ejercicio']!=1){
					$this->aError = [
						'Mensaje'=>'Error en el dato Ejercicio Regular, Revise por favor.',
						'Objeto'=>'Ejercicio',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				$taDatos['Dieta_Sal']=$taDatos['Dieta_Sal'] ?? 0;
				if($taDatos['Dieta_Sal']!=0 && $taDatos['Dieta_Sal']!=1){
					$this->aError = [
						'Mensaje'=>'Error en el dato Dieta Saludable, Revise por favor.',
						'Objeto'=>'Dieta_Sal',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				$taDatos['Prog_Reha']=$taDatos['Prog_Reha'] ?? 0;
				if($taDatos['Prog_Reha']!=0 && $taDatos['Prog_Reha']!=1){
					$this->aError = [
						'Mensaje'=>'Error en el dato Programa de Rehabilitación Cariovascular, Revise por favor.',
						'Objeto'=>'Prog_Reha',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				$taDatos['Tratamien']=$taDatos['Tratamien'] ?? 0;
				if($taDatos['Tratamien']!=0 && $taDatos['Tratamien']!=1){
					$this->aError = [
						'Mensaje'=>'Error en el dato No automodificar tratamiento médico, Revise por favor.',
						'Objeto'=>'Tratamien',
						'Valido'=>false,
					];
					return $this->aError['Valido'];
				}

				if (count($taDatos['Recomendaciones'])>0){
					$loObjC = new FormulacionParametros() ;
					$loObjEV = new ParametrosConsulta();
					$loObjEV->ObtenerMedicamentosUci();
					$lnOrden = 0;
					foreach($taDatos['Recomendaciones'] as $lnKey=>$laRecomenda){
						$lnOrden += 1;
						//Valida que el medicamento codificado exista
						$lcObjeto = 'adicionarGrupoMedicamento';
						if(empty($laRecomenda['CODMEDICA'])){
							$this->aError = ['Mensaje'=>'Error en el código del medicamento: '.trim($laRecomenda['DESMEDICA']),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
							break;
						}

						if(empty(trim($laRecomenda['DESMEDICA']))){
							$this->aError = ['Mensaje'=>'Error en la descripción del medicamento codificado: '.trim($laRecomenda['CODMEDICA']),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
							break;
						}

						$laResultado = $loObjC->BuscarMedicamento(trim($laRecomenda['CODMEDICA']));
						if(empty($laResultado)){
							$this->aError = ['Mensaje'=>'No existe en la base de datos el medicamento codificado: '.trim($laRecomenda['DESMEDICA']),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
							break;
						}

						if($laResultado != trim($laRecomenda['DESMEDICA'])){
							$this->aError = ['Mensaje'=>'Error en la descripción del medicamento: '.trim($laRecomenda['CODMEDICA']),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
							break;

						}

						if(empty(trim($laRecomenda['INDICADO']))){
							$this->aError = ['Mensaje'=>'El dato Indicado para del medicamento: '.trim($laRecomenda['DESMEDICA'] . ' es obligatorio'),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
							break;
						}

						$laResultado = $loObjEV->DatosMedicamentoUCC(trim($laRecomenda['CODMEDICA']));
						if(isset($laResultado)){
							if($laResultado['CODGRP']!==$laRecomenda['CODGRUPMED'] || $laResultado['DESGRP']!==$laRecomenda['DESGRUPOMED'] ){
									$this->aError = ['Mensaje'=>'Error en el grupo del medicamento: '.trim($laRecomenda['DESMEDICA'] . ' es obligatorio'),
											 'Objeto'=>$lcObjeto,
											 'Valido'=>false,];
								break;
							}
						}
					}
				}
			}
		}
		return $this->aError['Valido'];
	}

	function verificarEventualidad($taDatos=[])
	{
		// Valida dato Eventualidad
		if(empty(trim($taDatos['Eventualidad']))){
			$this->aError = [
				'Mensaje'=>'Nueva Eventualidad es un dato obligatorio',
				'Objeto'=>'edtEventualidad',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Valida dato Analisis de la eventualidad
		if(empty(trim($taDatos['AnalisisE']))){
			$this->aError = [
				'Mensaje'=>'Analisis para Epicrisis es un dato obligatorio',
				'Objeto'=>'edtAnalisisE',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		return $this->aError['Valido'];
	}

	public function GuardarEV($taDatos=[])
	{
		$llRetorno = false;
		$this->IniciaDatosIngreso($taDatos['Ingreso'], $taDatos['Tipo']);

		if($this->bReqAval){
			$this->nConCon = Consecutivos::fCalcularConsecutivoEstudiante($this->aIngreso['nIngreso']);
			if($this->nConCon>0){
				$this->obtenerConducta($taDatos,  $taDatos['Tipo']);
				$this->organizarDatosEV($taDatos, 'REINDE');
				$this->guardarDatosAVAL($taDatos);
			}
		}else{
			$laDatos=[
				'ingreso'	=> $this->aIngreso['nIngreso'],
				'seccion'	=> $this->aIngreso['cSeccion'],
				'cama'		=> $this->aIngreso['cHabita'],
				'usuario'	=> $this->cUsuCre,
				'programa'	=> $this->cPrgCre,
				'estado'	=> 3,
			];
			$loEvolucion = new Consecutivos();
			$lnCns = $loEvolucion->obtenerConsecEvolucion($laDatos);
			if ($lnCns===false){
				// No se pudo obtener consecutivo ...
				$this->aError = [
					'Mensaje'=>'Error del sistema no se pudo obtener consecutivo de evolución.',
					'Objeto'=>'buscarDxFallece',
					'Valido'=>false
				];
				return $this->aError['Valido'];
			} else {
				$this->nConEvo = $lnCns;
				$llRetorno = true;
			}

			 if($llRetorno){
				$this->obtenerConducta($taDatos,  $taDatos['Tipo']);
				$this->organizarDatosEV($taDatos, 'EVOLUC');
				$this->guardarDatosEV($taDatos);

				// retorna datos para consultar la Evolución
				$this->aError['dataEV'] = [
					'nIngreso'		=> $taDatos['Ingreso'],
					'cTipDocPac'	=> $this->aIngreso['cTipId'],
					'nNumDocPac'	=> $this->aIngreso['nNumId'],
					'cRegMedico'	=> $this->cRegMed,
					'cTipoDocum'	=> '3000',
					'cTipoProgr'	=> 'EV0018E',
					'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
					'nConsecCita'	=> '',
					'nConsecCons'	=> '',
					'nConsecEvol'	=> $this->nConEvo,
					'nConsecDoc'	=> $this->cPrgCre,
					'cCUP'			=> '',
					'cCodVia'		=> $this->aIngreso['cCodVia'],
					'cSecHab'		=> $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
				];

				if (!empty($taDatos['RecomendacionesUCC']['Presion_S'])) {
					$this->aError['dataRecom'] = [
						'nIngreso'		=> $taDatos['Ingreso'],
						'cTipDocPac'	=> $this->aIngreso['cTipId'],
						'nNumDocPac'	=> $this->aIngreso['nNumId'],
						'cRegMedico'	=> $this->cRegMed,
						'cTipoDocum'	=> '3200',
						'cTipoProgr'	=> 'RECOMEN',
						'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
						'nConsecCita'	=> '',
						'nConsecCons'	=> '',
						'nConsecEvol'	=> $this->nConEvo,
						'nConsecDoc' 	=> '',
						'cCodVia'		=> $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
						'cSecHab'		=> '',
					];
				}

				if (isset($taDatos['Nihss'])) {
					if($taDatos['Nihss']['TotalN'] !== ""){
						$lcTipoEV = $taDatos['Tipo'];
						$lcSeccion = $this->aIngreso['cSeccion'];
						$lcTipo = 'EVPL';
						if($lcTipoEV=='C' || $lcTipoEV=='V' || ($lcTipoEV=='P' && ($lcSeccion=='CC' || $lcSeccion=='CV' || $lcSeccion=='CI' || $lcSeccion=='CA' ))){
							$lcTipo= 'EVUC';
						}
						$this->aError['dataNihss'] = [
							'nIngreso'		=> $taDatos['Ingreso'],
							'cTipDocPac'	=> $this->aIngreso['cTipId'],
							'nNumDocPac'	=> $this->aIngreso['nNumId'],
							'cRegMedico'	=> $this->cRegMed,
							'cTipoDocum'	=> '3900',
							'cTipoProgr'	=> 'ESCNIHSS',
							'tFechaHora'	=>  date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
							'nConsecCita'	=> '',
							'nConsecCons'	=> '',
							'nConsecEvol'	=> '',
							'nConsecDoc' 	=> $lcTipo.'-'.$this->nConEvo,
							'cCodVia'		=> $this->aIngreso['cCodVia'],
							'cSecHab'		=> '',
						];
					}
				}
				return $this->aError;
			}
		}
	}

	function IniciaDatosIngreso($tnIngreso=0, $tnTipoEv='P')
	{
		$this->aIngreso = $this->oHcIng->datosIngreso($tnIngreso);
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cEspecialidad = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
		$this->cTipoUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario():'');
		$this->cRegMed = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():'');
		$this->cNombreUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getNombreCompleto():'');
		$this->cSL = chr(13);
		$this->cInicialEvol = $tnTipoEv;
		$this->cEspecialidadMedicotratante=$this->aIngreso['cEspecialidadMedicoTratante'];

		switch($tnTipoEv){
			case 'P':	// Evolución de Piso
				$this->cTipoEvol = 'EP';
				$this->cPrgCre = 'EVOPIWEB';
				break;
			case 'E':	// Eventualidad
				$this->cTipoEvol = 'ET';
				$this->cPrgCre = 'EVOEVWEB';
				break;
			case 'U':	// Evolución de Urgencias
				$this->cTipoEvol = 'ER';
				$this->cPrgCre = 'EVOURWEB';
				break;
			case 'C': case 'V':	// Evolución de unidades
				$this->cTipoEvol = 'EU';
				$this->cPrgCre = 'EVOUNWEB';
				break;
		}
	}

	function organizarDatosEV($taDatosEV=[], $tcTabla = 'EVOLUC')
	{
		if($this->bReqAval){
			$this->nConCon = Consecutivos::fCalcularConsecutivoEstudiante($this->aIngreso['nIngreso']);
		}else{
			// verifica si se guarda en epidemiologia
			$laTempEV = $this->oDb
				->select('TABCOD')
				->from('PRMTAB02')
				->where(['TABTIP'=>'ESP','TABCOD'=>$this->cEspecialidad,])
				->get('array');
			if(is_array($laTempEV)){
				if(count($laTempEV)>0) {
					$this->lGuardarEPD = true;

					$laConsecutivo = $this->oDb->max('CONINC', 'MAXIMO')->from('INFECAL01')->where(['INGINC'=>$this->aIngreso['nIngreso']])->get('array');
					if(is_array($laConsecutivo)){
						if(count($laConsecutivo)>0){
							$this->nConEpd = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
							$this->nConEpd ++;
						}
					}
					unset($laConsecutivo);
				}
			}
			$this->organizarEncabezado();
		}

		$this->OrganizarTextoAdicional(trim($taDatosEV['ctxtPandemia']), $tcTabla);

		if (isset($taDatosEV['EvolucionP'])) {$this->organizarEvolucionPiso($taDatosEV['EvolucionP']);}

		if (isset($taDatosEV['Analisis'])) {
			$this->organizarAnalisis($taDatosEV['Analisis'], $taDatosEV['Tipo'], $tcTabla);
		}

		if (isset($taDatosEV['RegistroUci'])) {
			$this->organizarRegistroUci($taDatosEV['RegistroUci']);
		}

		if (isset($taDatosEV['ProcedimientosUci'])) {
			$this->organizarProcedimientosUci($taDatosEV['ProcedimientosUci']);
		}

		if (isset($taDatosEV['Conciliacion'])) {
			if(!$this->bReqAval){
				if(($taDatosEV['Conciliacion']['Modifica']??'')=='true'){
					$this->organizarConciliacion($taDatosEV['Conciliacion'], $tcTabla);
				}
			}
		}

		if (isset($taDatosEV['Diagnostico'])) {
			$this->organizarDiagnostico($taDatosEV['Diagnostico']);
		}

		if (isset($taDatosEV['Eventualidad'])) {
			$this->organizarEventualidad($taDatosEV['Eventualidad'], $tcTabla);
		}

		if (isset($taDatosEV['RecomendacionesUCC'])) {
			if(!empty($taDatosEV['RecomendacionesUCC']['Presion_S'])){
				$this->organizarRecomendacionesUCC($taDatosEV['RecomendacionesUCC']);
			}
		}

		if(!$this->bReqAval){

			if (isset($taDatosEV['escalaHasbled'])) {
				$this->organizarEscalaHasbled($taDatosEV['escalaHasbled']);
			}

			if (isset($taDatosEV['escalaChadsvas'])) {
				$this->organizarEscalaChadsvas($taDatosEV['escalaChadsvas']);
			}

			if (isset($taDatosEV['escalaCrusade'])) {
				if(count($taDatosEV['escalaCrusade'])>0){
					$this->organizarEscalaCrusade($taDatosEV['escalaCrusade']);
				}
			}
		}

		if (isset($taDatosEV['Interpretacion'])) {
			$taDatosEV['Interpretacion']=$taDatosEV['Interpretacion']??[];
			if(count($taDatosEV['Interpretacion'])>0){
				$this->organizarInterpretacion($taDatosEV['Interpretacion']);
			}
		}

		if(!$this->bReqAval){
			if (isset($taDatosEV['Actividadfisica'])) {
				$this->organizarActividadFisica($taDatosEV['Actividadfisica']);
			}
		}
		$this->organizarRegistroMedico();

		if(!$this->bReqAval){
			$lcPlanDeManejo=(isset($taDatosEV['Analisis']['Analisis']) && !empty($taDatosEV['Analisis']['Analisis']))?$taDatosEV['Analisis']['Analisis']:$taDatosEV['Analisis']['Manejo'];
			$this->validarCuidadosDiarios($lcPlanDeManejo);
		}	
	}
	
	function validarCuidadosDiarios($tcPlanDeManejo){
		$lcTipoSeccionPaciente=$lcCupsCobrar='';
		if (trim($this->oDb->obtenerTabmae1('OP1TMA', 'TIPPROG', "CL1TMA='$this->cPrgCre' AND ESTTMA=''", null, ''))=='C'){
			$lcTipoSeccionPaciente=$this->oIngreso->tipoSeccionPaciente($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia']);
			$lcCuidadoDiario=trim($this->oDb->obtenerTabmae1('de2tma', 'EVOLUC', "CL1TMA='CUPCUI' AND ESTTMA=''", null, ''));
			$this->cCupsCuidadoDiario=trim(explode('~', $lcCuidadoDiario)[0]);
			$this->nTiemposEntreEvoluciones=intval(trim(explode('~', $lcCuidadoDiario)[1]));
			$this->cCupsTratanteCuidado=trim(explode('~', $lcCuidadoDiario)[2]);
			$this->cCupsConsultaCuidado=trim(explode('~', $lcCuidadoDiario)[3]);
			$this->nValidarHorasCuidado=intval(trim(explode('~', $lcCuidadoDiario)[4]));
			$this->nValidarDiasCuidado=intval(trim(explode('~', $lcCuidadoDiario)[5]));
			
			$laUrgencias = $this->oDb
				->select('DE2TMA')->from('TABMAE')
				->where('TIPTMA', '=', 'EVOLUC')->where('CL1TMA', '=', 'TSEURG')->where('ESTTMA', '=', '')->where("DE2TMA like '%$lcTipoSeccionPaciente%'")
				->get('array');
			if ($this->oDb->numRows()>0){
				$laCups = $this->oDb
				->select('trim(DE2TMA) CUPS')
					->from('TABMAE')
					->where('TIPTMA', '=', 'EVOLUC')->where('CL1TMA', '=', 'COBCUI')->where("CL2TMA='$this->cTipoUsuario'")
					->where("CL3TMA='$this->cEspecialidad'")->where('ESTTMA', '=', '')->orderBy('DE2TMA')
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcCupsCobrar=$laCups['CUPS'];
					$this->organizarCuidadoDiarioUrgencias($tcPlanDeManejo,$lcCupsCobrar);
				}else{
					$lcCupsCobrar=$this->obtieneCupsOtraEspecialidad($this->cCupsTratanteCuidado,$this->cCupsConsultaCuidado);
					$this->organizarCuidadoDiarioUrgencias($tcPlanDeManejo,$lcCupsCobrar);
				}
			}
		}
		unset($laUrgencias, $laCups, $laHospitalizacion);			
	}
	
	function organizarCuidadoDiarioUrgencias($tcPlanManejo='',$tcCupsCobrar='')
	{
		$lcTipoEntidadPlan=isset($this->aIngreso['cTipoPlan'])?trim($this->aIngreso['cTipoPlan']):'';
		$laHoras = $this->oDb
			->select(' trim(OP2TMA) HORAS')
			->from('TABMAE')
			->where('TIPTMA', '=', 'EVOLUC')
			->where('CL1TMA', '=', 'TPLCUI')->where('ESTTMA', '=', '')->where("DE2TMA like '%$lcTipoEntidadPlan%'")
			->get('array');
		if ($this->oDb->numRows()>0){
			$this->nValidarHorasCuidado=intval($laHoras['HORAS']);
		}
		$this->validarCobroUrgencias($tcPlanManejo,$tcCupsCobrar);
		unset($laHoras);
	}
	
	function validarCobroUrgencias($tcPlanManejo,$tcCupsCobrar)
	{
		$laCupsCobrar=[];
		$lnNroIngreso=$this->aIngreso['nIngreso'];
		$ldFechaActual = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$this->cFecCre . $this->cHorCre, '-', ':', 'T'));

		$laCobroCups = $this->oDb
			->select('FINEST FECHAULTIMO, HINEST HORAULTIMO')
			->from('RIAESTM')
			->where('INGEST', '=', $lnNroIngreso)->where("(CUPEST='$tcCupsCobrar' OR ELEEST='$tcCupsCobrar')")
			->where('DPTEST', '=', $this->cEspecialidad)->where('ESFEST', '<>', 5)
			->get('array');
		if ($this->oDb->numRows()==0){
			$lcWhere= "INGEPC='$lnNroIngreso' AND (STREPC LIKE 'U%' OR SACEPC LIKE 'U%')";
			$laSeccion = $this->oDb->select('FECEPC, HOREPC')->from('RIAEPC')
			->where($lcWhere)->orderBy('FECEPC, HOREPC')->get('array');
			if ($this->oDb->numRows()>0){
				$ldFechaUltima = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$laSeccion['FECEPC'] . $laSeccion['HOREPC'], '-', ':', 'T'));
				$loDiff = $ldFechaActual->diff($ldFechaUltima);
				$lnHorasDiferencias = ($loDiff->h * 1) + ($loDiff->days * 24);
				if ($lnHorasDiferencias>=$this->nValidarHorasCuidado){
					$laCupsCobrar[]=trim($tcCupsCobrar);
					$laCupsCobrar[]=trim($this->cCupsCuidadoDiario);
				}
			}
		}else{
			$laUltimoCupsCobrado = $this->oDb
			->select('CUPEST, DPTEST, FINEST FECHAULTIMO, HINEST HORAULTIMO')
			->from('RIAESTM')
			->where('INGEST', '=', $lnNroIngreso)->where("(CUPEST='$tcCupsCobrar' OR ELEEST='$tcCupsCobrar')")
			->where('DPTEST', '=', $this->cEspecialidad)->where('ESFEST', '<>', 5)->orderBy('FINEST DESC, HINEST DESC')->get('array');
			if ($this->oDb->numRows()>0){
				$ldFechaUltima = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$laUltimoCupsCobrado['FECHAULTIMO'] . $laUltimoCupsCobrado['HORAULTIMO'], '-', ':', 'T'));
				$loDiffEvo = $ldFechaActual->diff($ldFechaUltima);
				$lnHorasDiferencias = ($loDiffEvo->h * 1) + ($loDiffEvo->days * 24);
				if ($lnHorasDiferencias>=$this->nTiemposEntreEvoluciones){
					$laCupsCobrar[]=trim($tcCupsCobrar);
					$laCupsCobrar[]=trim($this->cCupsCuidadoDiario);
				}
			}	
		}
		if(count($laCupsCobrar)>0){	
			$this->registraDatosClinicos($laCupsCobrar,$tcPlanManejo);	
		}
		unset($laCobroCups, $laSeccion, $laUltimoCupsCobrado);
	}
	
	function verificaCupsQuirugico()
	{
		$lcFiltroCirugias='';
		$lnIngreso=$this->aIngreso['nIngreso'];
		$aParametros = [];
		$loTabmaeCirugias = $this->oDb->ObtenerTabMae('DE2TMA', 'EVOLUC', ['CL1TMA'=>'FILCCIR', 'ESTTMA'=>'']);
		$lcFiltroCirugias = trim(AplicacionFunciones::getValue($loTabmaeCirugias, 'DE2TMA', ''));
		
		if (!empty($lcFiltroCirugias)) {
			$this->oDb->where("$lcFiltroCirugias");
	
			$laVerificaQx = $this->oDb
			->select('A.FRLORD FECHA, TRIM(A.CODORD) ESPECIALIDAD')
			->from('RIAORD AS A')
			->leftJoin("RIACUP B", "A.COAORD=B.CODCUP", null)
			->where("A.NINORD=$lnIngreso AND A.ESTORD=3")
			->orderBy('A.FRLORD DESC')
			->get('array');
			if ($this->oDb->numRows()>0){
				$aParametros=[
					'FECHA'=>$laVerificaQx['FECHA'],
					'ESPECIALIDAD'=>trim($laVerificaQx['ESPECIALIDAD']),
				];
			}
		}
		return $aParametros;
	}
	
	function verificaCobroDiario($lcCupsCobrar='',$tcPlanManejo='')
	{
		$laCupsCobrar=[];
		$lnNroIngreso=$this->aIngreso['nIngreso'];
		$laVerificaCobro = $this->oDb
		->select('CUPEST, DPTEST, FINEST FECHAULTIMO, HINEST HORAULTIMO')
		->from('RIAESTM')
		->where('INGEST', '=', $lnNroIngreso)
		->where('FINEST', '=', $this->cFecCre)
		->where("(CUPEST='$lcCupsCobrar' OR ELEEST='$lcCupsCobrar')")
		->where('DPTEST', '=', $this->cEspecialidad)
		->where('ESFEST', '<>', 5)
		->get('array');
		if ($this->oDb->numRows()==0){
			$laCupsCobrar[]=trim($lcCupsCobrar);
			$this->registraDatosClinicos($laCupsCobrar,$tcPlanManejo);
		}
		unset($laVerificaCobro);
	}

	function registraDatosClinicos($taDatosCups=[],$tcPlanManejo='')
	{
		foreach($taDatosCups as $laListadoCups){
			$lcCodigoCups=trim($laListadoCups);
			$lcFinalidadCups='';
			
			$laFinalidadCups = $this->oDb
			->select("TRIM(IFNULL(B.CL1TMA,'')) FINALIDAD")
			->from('RIACUP A')
			->leftJoin("TABMAE B", "B.TIPTMA='CODFIN' AND CHAR(A.CAPCUP)=B.CL3TMA AND B.ESTTMA=''", null)
			->where('A.CODCUP', '=', $lcCodigoCups)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcFinalidadCups=trim($laFinalidadCups['FINALIDAD']);
			}
			$lnConsecutivoCita = Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cPrgCre);
			$this->InsertarRegistro('RIAORD', '', '', $lnConsecutivoCita, 0, '', $lcCodigoCups);
			$this->InsertarRegistro('RIADET', '', '', $lnConsecutivoCita, 0, '', $lcCodigoCups);
			$this->InsertarDescripcion('RIAHISL0', 70, 1, $tcPlanManejo, $lnConsecutivoCita, 70, '', $lcCodigoCups);
			
			$lnLineacups = 101;
			$lcTexto = str_pad((strval($this->cRegMed)), 13,' ',STR_PAD_RIGHT)." "
					  .str_pad((strval($this->cDxPpal)), 4,'0',STR_PAD_LEFT)." ".'0000'." "
					  .str_pad((strval($this->cEspecialidad)), 3,'0',STR_PAD_LEFT)." "
					  .str_pad((strval($lcFinalidadCups)), 4,' ',STR_PAD_LEFT)." "
					  .str_pad((strval($this->cClaseDiagnosticoPrincipal)), 4,' ',STR_PAD_LEFT);
			$this->InsertarRegistro('RIAHISL0', $lnLineacups, $lcTexto, $lnConsecutivoCita, 70, '', $lcCodigoCups);					
			$this->cobraProcedimiento($lcCodigoCups,$lnConsecutivoCita);
		}
		unset($laFinalidadCups);	
	}
	
	function obtieneCupsOtraEspecialidad($tcCupsTratante='',$tcCupsConsulta='')
	{
		$lcCupsCobrar=$lcEspecialidadTratante='';

		if (!empty($this->cEspecialidadMedicotratante)){
			if ($this->cEspecialidadMedicotratante==$this->cEspecialidad){
				$lcCupsCobrar=$tcCupsTratante;
			}else{
				$lcCupsCobrar=$this->obtieneCupsConsulta($tcCupsConsulta);
			}
		}else{
			$lcCupsCobrar=$this->obtieneCupsConsulta($tcCupsConsulta);
		}
		unset($laCupsInterconsulta, $laTratante);
		return $lcCupsCobrar;
	}
	
	function obtieneCupsEspecialidadHospitalizado($tcCupsTratante='',$tcCupsConsulta='')
	{
		$lcCupsCobrar='';
		if (!empty($this->cEspecialidadMedicotratante)){
			if ($this->cEspecialidadMedicotratante==$this->cEspecialidad){
				$lcCupsCobrar=$tcCupsTratante;
			}else{
				$lcCupsCobrar=$this->obtieneCupsConsulta($tcCupsConsulta);
			}
		}else{
			$lcCupsCobrar=$this->obtieneCupsConsulta($tcCupsConsulta);
		}	
		unset($laTratante);
		return $lcCupsCobrar;
	}
	
	function obtieneCupsConsulta($tcCupsConsulta='')
	{
		$lcCupsCobrar='';
		$laCupsConsulta = $this->oDb
			->select('TRIM(CODCUP) CUPS')
			->from('RIACUP')
			->where('IDDCUP', '=', '0')
			->where("(CODCUP LIKE '8903%')")
			->where('ESPCUP', '=', $this->cEspecialidad)
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcCupsCobrar=trim($laCupsConsulta['CUPS']);
		}else{
			$lcCupsCobrar=$tcCupsConsulta;
		}	
		unset($laCupsConsulta);
		return $lcCupsCobrar;
	}
	
	function cobraProcedimiento($tcCodigoCups='',$tnConsecutivocita=0)
	{
		$laData = [
			'ingreso'       => $this->aIngreso['nIngreso'],
			'numIdPac'      => $this->aIngreso['nNumId'],
			'codCup'        => $tcCodigoCups,
			'codVia'        => $this->aIngreso['cCodVia'],
			'codPlan'       => $this->aIngreso['cPlan'],
			'regMedOrdena'  => $this->cRegMed,
			'regMedRealiza' => $this->cRegMed,
			'espMedRealiza' => $this->cEspecialidad,
			'secCama'       => trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
			'cnsCita'       => $tnConsecutivocita,
			'portatil'      => '',
		];

		$loCobros = new Cobros();
		$lbRet = $loCobros->cobrarProcedimiento($laData);
	}

	function organizarEncabezado()
	{
		$ltFechaHoraIng = AplicacionFunciones::formatFechaHora('fechahora12', $this->cFecCre.' '.$this->cHorCre,'/');
		$lcDescrip = $this->nConEvo . ' - '. $ltFechaHoraIng . ' Hab: ' . $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'] . $this->cSL ;

		$lcTabla ='EVOLUC';
		$lnLinea = 1;
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

		// Inserta registro encabezado epidemiologia
		if ($this->lGuardarEPD){
			$lcTabla = 'INFEDE';
			$lnLinea = 1;
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
		}
	}

	private function OrganizarTextoAdicional($tcTexto='', $tcTabla='EVOLUC')
	{
		if(!empty($tcTexto)){
			$lcTabla = $tcTabla;
			$lnLongitud = ($lcTabla=='EVOLUC'?220:500);
			$lnIndice = 0;
			$lnIndice2 = 0;
			$lcTitulo = '';
			$lnLinea = 90000;
			if($tcTabla=='REINDE'){
				$lnIndice = 22;
				$lnLinea = 1;
				$lcTitulo = ' TEXTO PANDEMIA:';
			}
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
		}
	}

	function organizarEvolucionPiso($taDatos=[])
	{
		$lnLongitud = ($this->bReqAval?500:220);
		$lcTabla = ($this->bReqAval?'REINDE':'EVOLUC');
		$lnLinea = 2;
		if(!$this->bReqAval){
			// Insertar titulo OBJETIVO - SUBJETIVO
			$lcDescrip = ' OBJETIVO - SUBJETIVO' . $this->cSL . $this->cSL;
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

			// Insertar texto OBJETIVO - SUBJETIVO
			$lnLinea = 3;
			$lcDescrip = trim($taDatos['Objetivo']);
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($lcDescrip));
		}else{
			$lnIndice = 1;
			$lnIndice2 = 2;
			$lcTitulo = ' OBJETIVO - SUBJETIVO:';
			$lcDescrip = trim($taDatos['Objetivo']);
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
		}

		// Inserta registro epidemiologia
		if ($this->lGuardarEPD && !$this->bReqAval){
			$lcTabla = 'INFEDE';
			$lnLinea = 2;
			// Insertar titulo OBJETIVO - SUBJETIVO de epidemiologia
			$lcDescrip = 'OBJETIVO - SUBJETIVO' . $this->cSL . $this->cSL ;
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

			// Insertar texto OBJETIVO - SUBJETIVO de epidemiologia
			$lnLinea = 3;
			$lcDescrip = trim($taDatos['Objetivo']);
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
		}
	}

	function organizarActividadFisica($taDatos=[])
	{
		$tcTexto='';
		if (!empty($taDatos['Respuesta'])){
			$tcTexto=$this->cSL . ' REALIZA ACTIVIDAD FÍSICA ' . $this->cSL .$taDatos['Respuesta'] . $this->cSL;
			$lcTabla = 'EVOLUC';
			$lnLinea = 6999;
			$this->InsertarRegistro($lcTabla, $lnLinea, $tcTexto);
		}
	}

	function organizarDiagnostico($taDatos=[])
	{
		if(is_array($taDatos)){
			if(count($taDatos)>0){
				$lcTabla=($this->bReqAval?'REINDE':'EVOLUC');
				if(!$this->bReqAval){
					if($this->cTipoEvol=='EU'){
						$lnLinea = 500;
						$lcSepara = '';
					}else{
						$lnLinea = 5990;
						$lcSepara = $this->cSL;
					}

					$lnConsecutivoCie = 0;
					$lchr9 = chr(9);
					$lcDescrip = $this->cSL. $this->cSL . ' DIAGNÓSTICO(S): ';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

					foreach ($taDatos as $diagnostico){
						if($diagnostico['CODTIPO']=='1'){
							$this->cDxPpal=$diagnostico['CODIGO'];
							$this->cClaseDiagnosticoPrincipal=$diagnostico['CODCLASE'];
						}
						$lnConsecutivoCie = $lnConsecutivoCie + 1;
						$lnLinea = $lnLinea + 1;
						$lcDescrip = $lcSepara . ($lnConsecutivoCie ==1 ? '': $this->cSL) .$lnConsecutivoCie .'. ' .$diagnostico['CODIGO'] . '-' .$diagnostico['DESCRIP'];
						$lcDescrip = mb_substr($lcDescrip, 0, 220, 'UTF-8');
						$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

						$lnLinea = $lnLinea + 1;
						$lcDescrip = $lcSepara . $lchr9 . $lchr9 .'Tipo diagnóstico: ' .$diagnostico['TIPO'];
						$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

						$lnLinea = $lnLinea + 1;
						$lcDescrip = $lcSepara . $lchr9 . $lchr9 .'Clase diagnóstico: ' .$diagnostico['CLASE'];
						$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

						$lnLinea = $lnLinea + 1;
						$lcDescrip = $lcSepara . $lchr9 . $lchr9 .'Tratamiento: ' .$diagnostico['TRATA'];
						$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

						if (!empty(trim($diagnostico['OBSER']))){
							$lnLinea = $lnLinea + 1;
							$lcDescrip = $lcSepara . $lchr9 . $lchr9 . 'Análisis: ' .$diagnostico['OBSER'];
							$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
						}

						if ($diagnostico['DESCARTE']){
							$lnLinea = $lnLinea + 1;
							$lcDescrip = $lchr9 . $lchr9 .'Tipo descarte: ' .$diagnostico['DESCARTE'];
							$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

							$lnLinea = $lnLinea + 1;
							$lcDescrip = $lchr9 . $lchr9 . 'Justificación: ' .$diagnostico['JUSTIFICACIONDESCARTE'];
							$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
						}
					}
				}else{
					$lnLinea=0;
					$lnLongitud = 500;
					foreach ($taDatos as $diagnostico){
						if($diagnostico['CODTIPO']=='1'){
							$this->cDxPpal=$diagnostico['CODIGO'];
						}
						$lnLinea++;
						$lcTabla = 'REINDE';
						$lnIndice = 4;
						$lnIndice2 = 5991;
						$lcTitulo = 'DIAGNÓSTICO:';
						$lcTexto = $diagnostico['CODIGO'] . ' - ' . trim($diagnostico['DESCRIP']) . ' - ' . trim($diagnostico['CODTIPO'])
									. ' - ' . trim($diagnostico['CODCLASE']) . ' - ' . trim($diagnostico['CODTRATA']) ;
						$this->InsertarRegistro($lcTabla, $lnLinea, trim($lcTexto), $lnIndice, $lnIndice2, $lcTitulo);

						if (!empty(trim($diagnostico['OBSER']))){
							$lcTabla = 'REINOB';
							$lcTitulo = 'OB';
							$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($diagnostico['OBSER']), $lnIndice, $lnIndice2, $lcTitulo, $diagnostico['CODIGO']);
						}

						if (!empty(trim($diagnostico['JUSTIFICACIONDESCARTE']))){
							$lcTabla = 'REINOB';
							$lcTitulo = 'JD';
							$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($diagnostico['JUSTIFICACIONDESCARTE']), $lnIndice, $lnIndice2, $lcTitulo, $diagnostico['CODIGO']);
						}
					}
				}
			}
		}
	}

	function organizarConciliacion($taDatos=[])
	{
		$lcTabla='EVOLUC';
		if($this->cTipoEvol=='EU'){
			$lnLinea = 6000;
			$lcDescrip = $this->cSL . $this->cSL . 'CONCILIACION DE MEDICAMENTOS' . $this->cSL;
		}else{
			$lnLinea = 6100;
			$lcDescrip = $this->cSL . 'CONCILIACION DE MEDICAMENTOS';
		}

		// Insertar titulo CONCILIACION DE MEDICAMENTOS
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
		$loObjEV = new Conciliacion([],2);
		$loObjEV->TextoConciliacion($taDatos);
		$lcDescrip = $loObjEV->getTexto();

		$lnLongitud = 220;
		$lnLinea++;
		$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
	}

	function organizarInterpretacion($taDatos=[])
	{
		$lnConsecutivoGrabar=0;
		if($this->bReqAval){
			$lcTabla = 'REINDE';
			$lcDescrip = 'INTERPRETACIÓN DE EXAMENES' ;
			$lnLongitud = 500;
			$lnIndice = $lnLinea = $lnIndice2 = 1;
			$lcTitulo = '';
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $lnIndice, $lnIndice2, $lcTitulo);
		}else{
			$lcTabla = 'EVOLUC';
			$lnLinea = 7000;
			$lnLongitud = 220;
			$lnIndice2 = 0;
			// Insertar titulo INTERPRETACION DE EXAMENES
			$lcDescrip = $this->cTipoEvol=='EU'?'':$this->cSL ;
			$lcDescrip .= $this->cSL . 'INTERPRETACIÓN DE EXAMENES' . $this->cSL;
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
		}

		foreach ($taDatos as $laInterpretacion){
			if ($laInterpretacion['NORMAL']=='1' || $laInterpretacion['ANORMAL']=='1'){
				$laInterpretacion['CODCIT'] = intval($laInterpretacion['CODCIT']);
				if ($laInterpretacion['CODCIT']>0) {
					$lcDescrip = $lcInterpreta = '';
					$lnLinea++;
					// Inserta titulo de procedimiento
					$lcDescrip = '  ' . $laInterpretacion['CUPS'] . '-' .  mb_substr($laInterpretacion['DESCRIPCION'], 0, 69);
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $laInterpretacion['CODCIT'], $lnIndice2);

					// Inserta observación del procedimiento
					$lnLinea++;
					if ($laInterpretacion['NORMAL']=='1'){
						$lcDescrip = '  Interpretación: ' . $laInterpretacion['OBSERVA'];
						$lcInterpreta = '1';
					}

					if ($laInterpretacion['ANORMAL']=='1'){
						$lcDescrip = '  *Interpretación: ' . $laInterpretacion['OBSERVA'];
						$lcInterpreta = '2';
					}
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip, $laInterpretacion['CODCIT'], $lnIndice2);
					// ACTUALIZA RIAORD
					if(!$this->bReqAval){
						$lcTablaR = 'RIAORDUP';
						$lcDescrip = $laInterpretacion['CODCIT'] . '¥' . $lcInterpreta;
						$this->InsertarRegistro($lcTablaR, $lnLinea, $lcDescrip);

						$lcTablaC = 'CUPGRA';
						$laDatosCie =[
							'diagnostico' 	=>$this->cDxPpal,
							'medicorealiza' =>$this->cRegMed,
							'especrealiza' 	=>$this->cEspecialidad,
						];
						$this->InsertarRegistro($lcTablaC, 0, $laDatosCie, $laInterpretacion['CODCIT'], 0, '', $laInterpretacion['CUPS']);
					}
				}
			}
		}
	}

	function organizarAnalisis($taDatos=[], $tnTipo='P')
	{
		$lcTabla=($this->bReqAval?'REINDE':'EVOLUC');
		// Insertar titulo PLAN DE MANEJO
		if (!$this->bReqAval){
			if($this->cTipoEvol=='EU'){
				$lnLinea = 2800;
				$lcDescrip = $this->cSL . ' PLAN DE MANEJO:' ;
			}else{
				$lnLinea = 999;
				$lcDescrip = $this->cSL . $this->cSL . ' PLAN DE MANEJO' . $this->cSL . $this->cSL;
			}
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

			// Insertar Descripción Plan de Manejo
			$lnLinea++;
			$lnLongitud = 220;
			$lcDescrip = $taDatos['Manejo'];
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

			// si conducta a segir es hospitalizado se inserta la justificación
			if($taDatos['Seguir']=='03'){
				if (!empty(trim($taDatos['OrdenHospitalizacion']['JustificacionordenHos']))){

					// Inserta titulo justificación de orden de hospitalización
					$lnLinea++;
					$lcDescrip = $this->cSL . $this->cSL . ' ORDEN DE HOSPITALIZACIÓN' . $this->cSL . $this->cSL;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lnLongitud = 220;
					$lcDescrip = trim($taDatos['OrdenHospitalizacion']['JustificacionordenHos']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				}
			}
		}else{
			$lcDescrip = $taDatos['Manejo'];
			$lnLongitud = 500;
			$lnIndice = 13;
			$lnIndice2 = 999;
			$lcTitulo = ' PLAN DE MANEJO:';
			$lnLinea = 1 ;
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
		}

		if (!$this->bReqAval){
			// Insertar titulo ANALISIS PARA EPICRISIS
			if($this->cTipoEvol !== 'ER'){
				$lnLinea = 5500;
				$lcDescrip = $this->cSL . $this->cSL . ' ANALISIS PARA EPICRISIS' . $this->cSL . $this->cSL;
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				// Insertar Descripción Analisis para epicrisis
				$lnLinea++;
				$lnLongitud = 220;
				$lcDescrip = $taDatos['Analisis'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
			}
		}else{
			$lcDescrip = $taDatos['Analisis'];
			$lnLongitud = 500;
			$lnIndice = 18;
			$lnIndice2 = 5500;
			$lcTitulo = 'ANALISIS PARA EPICRISIS';
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
		}

		// Insertar Conducta a Seguir
		$lcTipo= 'EVPISO';
		$lcSeccion = $this->aIngreso['cSeccion'];
		if($tnTipo=='C' || $tnTipo=='V' ||  ($tnTipo=='P' && ($lcSeccion=='CC' || $lcSeccion=='CV' || $lcSeccion=='CI' || $lcSeccion=='CA' ))){
			$lcTipo= 'EVUNID';
		}
		$loObjEV = new ParametrosConsulta();
		$loObjEV->ObtenerConductaSeguir($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia'],$this->aIngreso['cSeccion'],$lcTipo,'');
		$laResultado = $loObjEV->tipoConductaSeguir($taDatos['Seguir']);

		if (!$this->bReqAval){
			$lnLinea = $this->cTipoEvol=='EU'?5995:6995;
			$lcDescrip = $this->cSL . $this->cSL . ' CONDUCTA A SEGUIR: ' . $this->cSL . trim($laResultado['desc']) . $this->cSL;

			if($taDatos['Seguir']=='01'){
				$loObjEV->ObtenerEstadoSalida();
				$laResultado = $loObjEV->estadoSalida($taDatos['Estado']);
				$lcDescrip .= ' - Estado Salida: ' . trim($laResultado['desc']) . $this->cSL ;
			}
			$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
		}else{
			$lcDescrip = trim($laResultado['desc']);
			if($taDatos['Seguir']=='01'){
				$loObjEV->ObtenerEstadoSalida();
				$laResultado = $loObjEV->estadoSalida($taDatos['Estado']);
				$lcDxFallece = substr($taDatos['cCodigoDxFallece']??'',0,5);
				$lcDescrip .= ' - Estado Salida: ' . trim($laResultado['desc']);
				$lcDescrip .= $taDatos['Estado']==2?' ¥'.$taDatos['FechaFallece'].'¤'.$taDatos['HoraFallece'].'¤'. $lcDxFallece:'';
			}
			$lnLinea = 1;
			$lnIndice = 21;
			$lnIndice2 = 5995;
			$lcTitulo = 'CONDUCTA A SEGUIR:';
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
		}

		// Insertar Descripción Analisis para epicrisis en la tabla ANAEPI
		if (!$this->bReqAval){
			$lcTabla = 'ANAEPI';
			$lnLinea = 1;
			$lnLongitud = 220;
			if($this->cTipoEvol=='ER'){
				$lcDescrip = $taDatos['Manejo'];
			}else{
				$lcDescrip = $taDatos['Analisis'];
			}
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

			// Inserta registro epidemiologia
			if ($this->lGuardarEPD){
				$lcTabla = 'INFEDE';
				$lnLongitud = 220;

				// Insertar titulo PLAN DE MANEJO
				$lnReg = count($this->aINFEDE)-1;
				$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
				$lcDescrip = ' PLAN DE MANEJO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				// Insertar texto PLAN DE MANEJO
				$lnLinea ++;
				$lcDescrip = trim($taDatos['Manejo']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				// Insertar titulo ANALISIS PARA EPICRISIS
				$lnLinea++;
				$lcDescrip = ' ANÁLISIS PARA EPICRISIS:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				// Insertar Descripción Analisis para epicrisis
				$lnLinea++;
				$lcDescrip = $taDatos['Analisis'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
			}
		}
	}

	function organizarRegistroUci($taDatos=[])
	{
		$lnLongitud = ($this->bReqAval?500:220);
		if (!empty(trim($taDatos['AntecedentesUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1200;
				$lcDescrip =$this->cSL . ' ANTECEDENTES DE UCI:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['AntecedentesUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' ANTECEDENTES DE UCI:' ;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['AntecedentesUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			}else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 2;
				$lnIndice2 = 1200;
				$lcTitulo = ' ANTECEDENTES DE UCI:';
				$tcTexto = $taDatos['AntecedentesUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if(!empty(trim($taDatos['ResultadosLaboratorioUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1300;
				$lcDescrip =$this->cSL . ' RESULTADOS DE LABORATORIO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['ResultadosLaboratorioUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' RESULTADOS DE LABORATORIO:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['ResultadosLaboratorioUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 3;
				$lnIndice2 = 1300;
				$lcTitulo = ' RESULTADOS DE LABORATORIO:';
				$tcTexto = $taDatos['ResultadosLaboratorioUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if(!empty(trim($taDatos['EcgUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1400;
				$lcDescrip =$this->cSL . ' ECG:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['EcgUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' ECG:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['EcgUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 4;
				$lnIndice2 = 1400;
				$lcTitulo = ' ECG:';
				$tcTexto = $taDatos['EcgUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if(!empty(trim($taDatos['RxToraxUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1500;
				$lcDescrip =$this->cSL . ' RX DE TORAX:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['RxToraxUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' RX DE TORAX:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['RxToraxUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 5;
				$lnIndice2 = 1500;
				$lcTitulo = ' RX DE TORAX:';
				$tcTexto = $taDatos['RxToraxUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if(!empty(trim($taDatos['GasimetriaAvUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1600;
				$lcDescrip =$this->cSL . ' GASIMETRIA A/V:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['GasimetriaAvUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' GASIMETRIA A/V:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['GasimetriaAvUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 6;
				$lnIndice2 = 1600;
				$lcTitulo = ' GASIMETRIA A/V:';
				$tcTexto = $taDatos['GasimetriaAvUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if(!empty(trim($taDatos['PerfilHemodinamicoUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1700;
				$lcDescrip =$this->cSL . ' PERFIL HEMODINÁMICO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['PerfilHemodinamicoUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' PERFIL HEMODINÁMICO:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['PerfilHemodinamicoUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 7;
				$lnIndice2 = 1700;
				$lcTitulo = ' PERFIL HEMODINÁMICO:';
				$tcTexto = $taDatos['PerfilHemodinamicoUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if (!empty(trim($taDatos['SubjetivoUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 1800;
				$lcDescrip = $this->cSL . ' SUBJETIVO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['SubjetivoUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' SUBJETIVO:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['SubjetivoUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 8;
				$lnIndice2 = 1800;
				$lcTitulo = ' SUBJETIVO:';
				$tcTexto = $taDatos['SubjetivoUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// SIGNOS VITALES
		if (!empty(trim($taDatos['Fcuci'])) || !empty(trim($taDatos['Fruci'])) || !empty(trim($taDatos['Pasuci'])) || !empty(trim($taDatos['Paduci'])) ||
			!empty(trim($taDatos['Pamuci'])) || !empty(trim($taDatos['Pvcuci'])) || !empty(trim($taDatos['Pcpuci'])) || !empty(trim($taDatos['Icuci']))){
			$lcDescrip = 'F.C.: ' . str_pad(trim($taDatos['Fcuci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'F.R.: ' . str_pad(trim($taDatos['Fruci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'P.A.S.: ' . str_pad(trim($taDatos['Pasuci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'P.A.D.: ' . str_pad(trim($taDatos['Paduci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'P.A.M.: ' . str_pad(trim($taDatos['Pamuci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'P.V.C.: ' . str_pad(trim($taDatos['Pvcuci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'P.C.P.: ' . str_pad(trim($taDatos['Pcpuci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						 'I.C.: ' . str_pad(trim($taDatos['Icuci']), 7, " ", STR_PAD_RIGHT) . ' ' ;
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 2000;
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 9;
				$lnIndice2 = 1900;
				$lcTitulo = ' SIGNOS VITALES:';
				$lnLinea = $this->InsertarRegistro($lcTabla, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// EXAMEN FISICO
		if (!empty(trim($taDatos['ExamenFisicoUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 2100;
				$lcDescrip = $this->cSL . ' EXAMEN FÍSICO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['ExamenFisicoUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' EXAMEN FÍSICO:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['ExamenFisicoUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 10;
				$lnIndice2 = 2000;
				$lcTitulo = ' EXAMEN FISICO:';
				$tcTexto = $taDatos['ExamenFisicoUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// MANEJO ACTUAL
		if (!empty(trim($taDatos['ManejoActualUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 2200;
				$lcDescrip = $this->cSL . ' MANEJO ACTUAL:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['ManejoActualUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' MANEJO ACTUAL:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['ManejoActualUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 11;
				$lnIndice2 = 2100;
				$lcTitulo = ' MANEJO ACTUAL:';
				$tcTexto = $taDatos['ManejoActualUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// INFORMACION EXAMENES Y PROC. A SOLICITAR
		if(!empty(trim($taDatos['ExamenSolicitarUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3100;
				$lcDescrip = $this->cSL . ' EXAMENES Y PROC. A SOLICITAR:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['ExamenSolicitarUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' EXAMENES Y PROC. A SOLICITAR:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['ExamenSolicitarUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 14;
				$lnIndice2 = 3100;
				$lcTitulo = ' EXAMENES Y PROC. A SOLICITAR:';
				$tcTexto = $taDatos['ExamenSolicitarUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// APACHE, SOFA, PARSONETT
		if (!empty(trim($taDatos['ApacheUci'])) || !empty(trim($taDatos['SofaUci'])) || !empty(trim($taDatos['ParsonettUci'])) || !empty(trim($taDatos['PocasUci']))){
			$lcDescrip = 'APACHE: ' . str_pad(trim($taDatos['ApacheUci']), 7, " ", STR_PAD_RIGHT) . ' ' .
							'SOFA: ' . str_pad(trim($taDatos['SofaUci']), 7, " ", STR_PAD_RIGHT) . ' ' .
							'PARSONETT: ' . str_pad(trim($taDatos['ParsonettUci']), 7, " ", STR_PAD_RIGHT) .
							'POCAS: ' . str_pad(trim($taDatos['PocasUci']), 7, " ", STR_PAD_RIGHT) ;
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3500;
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}
				// GUARDA APACHE EN EVOUNI
				if (!empty(trim($taDatos['ApacheUci']))){
					$lcTabla = 'EVOUNI';
					$lnLinea = 1;
					$lnIndice = 50;
					$lcDescrip = trim($taDatos['ApacheUci']);
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $lnIndice);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 15;
				$lnIndice2 = 3500;
				$lcTitulo = '';
				$this->InsertarRegistro($lcTabla, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		if (!empty(trim($taDatos['TimiUci'])) || !empty(trim($taDatos['TissUci']))){
			$lcDescrip = 'TIMI: ' . str_pad(trim($taDatos['TimiUci']), 7, " ", STR_PAD_RIGHT) . ' ' .
						'TISS-28: ' . str_pad(trim($taDatos['TissUci']), 7, " ", STR_PAD_RIGHT) ;
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3600;
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}
			} else{
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 16;
				$lnIndice2 = 3600;
				$lcTitulo = '';
				$this->InsertarRegistro($lcTabla, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}

		// INFORMACION PRONOSTICO
		if(!empty(trim($taDatos['PronosticoUci']))){
			if(!$this->bReqAval){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3700;
				$lcDescrip = $this->cSL . ' PRONOSTICO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea++;
				$lcDescrip = trim($taDatos['PronosticoUci']);
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

				if ($this->lGuardarEPD){
					$lcTabla = 'INFEDE';
					$lnReg = count($this->aINFEDE)-1;
					$lnLinea = $this->aINFEDE[$lnReg]['LININD'] + 1 ;
					$lcDescrip = ' PRONOSTICO:';
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
					$lnLinea++;
					$lcDescrip = trim($taDatos['PronosticoUci']);
					$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
				}
			} else {
				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 17;
				$lnIndice2 = 3700;
				$lcTitulo = '  PRONOSTICO:';
				$tcTexto = $taDatos['PronosticoUci'];
				$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, trim($tcTexto), $lnIndice, $lnIndice2, $lcTitulo);
			}
		}
	}

	function organizarProcedimientosUci($taDatos=[])
	{
		if(!$this->bReqAval){
			if(!empty(trim($taDatos['DatosCieCups']['codigoProcedimientoUci']))){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3800;
				$lcDescrip = $this->cSL . ' DIAGNOSTICO DE PROCEDIMIENTO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea = 3801;

				$lcDescrip = trim($taDatos['DatosCieCups']['codigoProcedimientoUci']) .' - ' .trim($taDatos['DatosCieCups']['descripcionProcedimientoUci']);
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				$lcTabla = 'EVOUNI';
				$lnLinea = 1;
				$lnIndice = 1;
				$lcDescrip = trim(explode('-', $lcDescrip)[0]);
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $lnIndice);

			}

			if(trim($taDatos['DatosCieCups']['TotUci']??'')=='1' || trim($taDatos['DatosCieCups']['CvcUci']??'')=='1' || trim($taDatos['DatosCieCups']['sVtUci']??'')=='1' || trim($taDatos['DatosCieCups']['ningunoUci']??'')=='1'){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3810;
				$lcDescrip = ' INVASION:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

				$taDatos['DatosCieCups']['TotUci'] = $taDatos['DatosCieCups']['TotUci'] ?? 0;
				$lcDescrip = trim($taDatos['DatosCieCups']['TotUci'])==1 ? '    TOT':'';
				if(!empty($lcDescrip)){
					$lnLinea = 3811;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}

				$taDatos['DatosCieCups']['CvcUci'] = $taDatos['DatosCieCups']['CvcUci'] ?? 0;
				$lcDescrip = trim($taDatos['DatosCieCups']['CvcUci'])==1 ? '    CVC':'';
				if(!empty($lcDescrip)){
					$lnLinea = 3812;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}

				$taDatos['DatosCieCups']['sVtUci'] = $taDatos['DatosCieCups']['sVtUci'] ?? 0;
				$lcDescrip = trim($taDatos['DatosCieCups']['sVtUci'])==1 ? '    SV':'';
				if(!empty($lcDescrip)){
					$lnLinea = 3813;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}

				$taDatos['DatosCieCups']['ningunoUci'] = $taDatos['DatosCieCups']['ningunoUci'] ?? 0;
				$lcDescrip = trim($taDatos['DatosCieCups']['ningunoUci'])==1 ? '    NINGUNO':'';
				if(!empty($lcDescrip)){
					$lnLinea = 3813;
					$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				}

				$lcDescrip = str_pad(trim($taDatos['DatosCieCups']['TotUci']), 3, " ", STR_PAD_RIGHT) . ' ' .
							str_pad(trim($taDatos['DatosCieCups']['CvcUci']), 3, " ", STR_PAD_RIGHT) . ' ' .
							str_pad(trim($taDatos['DatosCieCups']['sVtUci']), 3, " ", STR_PAD_RIGHT) . ' ' .
							str_pad(trim($taDatos['DatosCieCups']['ningunoUci']), 3, " ", STR_PAD_RIGHT) . ' ';

				$lcTabla = 'EVOUNI';
				$lnLinea = 1;
				$lnIndice = 10;
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $lnIndice);
			}

			if(!empty(trim($taDatos['DatosCieCups']['InfeccionUci']))){
				$lcTabla = 'EVOLUC';
				$lnLinea = 3820;
				$lcDescrip = ' INFECCIÓN: ';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea = 3821;
				$lcDescrip = (trim($taDatos['DatosCieCups']['InfeccionUci'])=='Si'?'SI':'NO');
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lcTabla = 'EVOUNI';
				$lnLinea = 1;
				$lnIndice = 20;
				$lcDescrip = substr(trim($taDatos['DatosCieCups']['InfeccionUci']),0,1);
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip, $lnIndice);
			}

			$lcTabla = 'EVOLUC';
			if(!empty(trim($taDatos['DatosCieCups']['NefroproteccionUci']))){
				$lnLinea = 3822;
				$lcDescrip = ' NEFROPROTECCIÓN: ';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea = 3823;
				$lcDescrip = (trim($taDatos['DatosCieCups']['NefroproteccionUci'])=='Si'?'SI':'NO');
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
			}

			if(!empty(trim($taDatos['DatosCieCups']['ProfilaxisUci']))){
				$lnLinea = 3824;
				$lcDescrip = ' PROFILAXIS FA: ';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea = 3825;
				$lcDescrip = (trim($taDatos['DatosCieCups']['ProfilaxisUci'])=='Si'?'SI':'NO');
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
			}

			if(!empty(trim($taDatos['DatosCieCups']['EuroescoreUci']))){
				$lnLinea = 3826;
				$lcDescrip = ' EUROSCORE: ' . trim($taDatos['DatosCieCups']['EuroescoreUci']);
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
			}

			$lcTabla = 'EVOUNI';
			$lnIndice = 25;
			$lcDescrip = str_pad(substr(trim($taDatos['DatosCieCups']['NefroproteccionUci']),0,1), 3, " ", STR_PAD_RIGHT) . ' ' .
						str_pad(trim($taDatos['DatosCieCups']['ProfilaxisUci']), 3, " ", STR_PAD_RIGHT) . ' ' .
						str_pad(trim($taDatos['DatosCieCups']['EuroescoreUci']), 3, " ", STR_PAD_RIGHT) . ' ' ;
			$this->InsertarRegistro($lcTabla, 1, $lcDescrip, $lnIndice);

			// COMPLICACIONES
			$taDatos['DatosCieCups']['SinComplicaciones'] = isset($taDatos['DatosCieCups']['SinComplicaciones'])?'1':'';
			$lcTabla = 'EVOLUC';
			if($taDatos['DatosCieCups']['SinComplicaciones']=='1'){
				$lnLinea = 3830;
				$lcDescrip = ' COMPLICACIONES:';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
				$lnLinea = 3900;
				$lcDescrip = '    NINGUNA';
				$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
			}else{

				if(isset($taDatos['ListadoComplicaciones'])){
					$lcCadena = '';
					$llCabecera = false;
					$lnReg = 0;
					foreach($taDatos['ListadoComplicaciones'] as $laComplica) {
						$lnReg++;
						if($laComplica['SELECCION']==='true'){
							if($llCabecera === false){
								$lnLinea = 3830;
								$lcDescrip = ' COMPLICACIONES:';
								$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
								$lnLinea = 3899;
								$llCabecera = true;
							}
							$lnLinea++;
							$lcDescrip = '    '.$laComplica['DESCRIPCION'];
							$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
						}
						$lcCadena .= str_pad(($laComplica['SELECCION']==true?'1':'0'), 3, " ", STR_PAD_RIGHT) ;
					}
					if(!empty(trim($lcCadena))){
						$lcTabla = 'EVOUNI';
						$lnLinea = 1;
						$lnIndice = 45;
						$this->InsertarRegistro($lcTabla, $lnLinea, $lcCadena, $lnIndice);
					}
				}
			}
		} else {
			if(!empty(trim($taDatos['DatosCieCups']['codigoProcedimientoUci']))){

				$lcDescrip = str_pad(trim($taDatos['DatosCieCups']['codigoProcedimientoUci']), 15, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['TotUci']??'0'), 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['CvcUci']??'0'), 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['sVtUci']??'0'), 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['ningunoUci']??'0'), 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['InfeccionUci']??'NO')=='Si'?'S':'N', 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['NefroproteccionUci']??'NO')=='Si'?'S':'N', 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['ProfilaxisUci']??'NO')=='Si'?'S':'N', 5, " ", STR_PAD_RIGHT) . ' ' .
							 str_pad(trim($taDatos['DatosCieCups']['EuroescoreUci']), 3, " ", STR_PAD_RIGHT) . ' - ' .
							 trim($taDatos['DatosCieCups']['descripcionProcedimientoUci']);

				$lcTabla = 'REINDE';
				$lnLinea = 1;
				$lnIndice = 19;
				$lnIndice2 = 3800;
				$lcTitulo = ' DIAGNOSTICO DE PROCEDIMIENTO:';
				$this->InsertarRegistro($lcTabla, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);

				if(isset($taDatos['ListadoComplicaciones'])){
					$lcDescrip = '';
					foreach($taDatos['ListadoComplicaciones'] as $laComplica) {
						$lcDescrip .= str_pad(($laComplica['SELECCION']==true?'1':'0'), 3, " ", STR_PAD_RIGHT) ;
					}
					if(!empty(trim($lcDescrip))){
						$lnLinea = 1;
						$lnIndice = 20;
						$lnIndice2 = 3830;
						$lcTitulo = ' COMPLICACIONES:';
						$this->InsertarRegistro($lcTabla, $lnLinea, trim($lcDescrip), $lnIndice, $lnIndice2, $lcTitulo);
					}
				}
			}
		}
	}

	function organizarRecomendacionesUCC($taDatos=[])
	{
		$lcTabla = 'EVOLUC';
		$lnLongitud = 220;
		$lnLinea = 8000;

		// Linea 8000 se guarda los parámetros
		$lcDescrip = 'Presion S:'. str_pad($taDatos['Presion_S'],3,' ',STR_PAD_LEFT) .
					 ' Presion D:'. str_pad($taDatos['Presion_D'],3,' ',STR_PAD_LEFT) .
					 ' Hemoglobi:'. str_pad($taDatos['Hemoglobi'],3,' ',STR_PAD_LEFT) .
					 ' Glicem An:'. str_pad($taDatos['Glicem_An'],3,' ',STR_PAD_LEFT) .
					 ' Glicem Po:'. str_pad($taDatos['Glicem_Po'],3,' ',STR_PAD_LEFT) .
					 ' Tabaquism:'. str_pad($taDatos['Tabaquism']??0,3,' ',STR_PAD_LEFT) .
					 ' Coleste T:'. str_pad($taDatos['Coleste_T'],3,' ',STR_PAD_LEFT) .
					 ' Coleste B:'. str_pad($taDatos['Coleste_B'],3,' ',STR_PAD_LEFT) .
					 ' Coleste M:'. str_pad($taDatos['Coleste_M'],3,' ',STR_PAD_LEFT) .
					 ' Triglicer:'. str_pad($taDatos['Triglicer'],3,' ',STR_PAD_LEFT) .
					 ' Perimet A:'. str_pad($taDatos['Perimet_A'],3,' ',STR_PAD_LEFT) .
					 ' Ejercicio:'. str_pad($taDatos['Ejercicio']??0,3,' ',STR_PAD_LEFT) .
					 ' Dieta Sal:'. str_pad($taDatos['Dieta_Sal']??0,3,' ',STR_PAD_LEFT) .
					 ' Prog Reha:'. str_pad($taDatos['Prog_Reha']??0,3,' ',STR_PAD_LEFT) .
					 ' Tratamien:'. str_pad($taDatos['Tratamien']??0,3,' ',STR_PAD_LEFT) ;

		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

		// Organiza informacion de la tabla de medicamentos para recomendaciones
		$lcXMLinicial = '<?xml version="1.0" encoding="Windows-1252" standalone="yes" ?> <VFPData />';
		$loSXML = new \SimpleXMLElement($lcXMLinicial);

		if (isset($taDatos['Recomendaciones'])){
			if (count($taDatos['Recomendaciones'])>0){
				$lnLinea = 8001;
				foreach($taDatos['Recomendaciones'] as $lcKey=>$lcDato) {
					$loMedica = $loSXML->addChild('ccurmedica');
					$loMedica->addAttribute('selecc', '0');
					$loMedica->addAttribute('codmed', trim($lcDato['CODMEDICA']));
					$loMedica->addAttribute('nommed', trim($lcDato['DESMEDICA']));
					$loMedica->addAttribute('codgrp', trim($lcDato['CODGRUPMED']));
					$loMedica->addAttribute('nomgrp', trim($lcDato['DESGRUPOMED']));
					$loMedica->addAttribute('indmed', trim($lcDato['INDICADO']));
				}
			}
			$lcDescrip = mb_convert_encoding($loSXML->asXML(), 'UTF-8', 'Windows-1252');
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
		}

		if($taDatos['edtRecomendacionesUCC']??''){
			$lnLinea = 8050;
			$lcDescrip = trim($taDatos['edtRecomendacionesUCC']);
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
		}
	}

	function organizarEventualidad($taDatos=[], $tcTabla='EVOLUC')
	{
		$lcTabla = $tcTabla;
		$lnLongitud = ($lcTabla=='EVOLUC'?220:500);
		$lnLinea = 5000;
		// Insertar titulo EVENTUALIDAD
		$lcDescrip = ' EVENTUALIDAD:';
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

		// Insertar descripción de la nueva EVENTUALIDAD
		$lnLinea++;
		$lcDescrip = trim($taDatos['Eventualidad']);
		$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

		$lnLinea = 5500;
		// Insertar titulo ANALISIS PARA EPICRISIS DE LA EVENTUALIDAD
		$lcDescrip = ' ANALISIS PARA EPICRISIS';
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

		// Insertar descripción del ANALISIS PARA EPICRISIS EVENTUALIDAD
		$lnLinea++;
		$lcDescrip = trim($taDatos['AnalisisE']);
		$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);

		// Insertar Descripción Analisis para epicrisis de la eventualidad en la tabla ANAEPI
		if(!$this->bReqAval){
			$lcTabla = 'ANAEPI';
			$lnLinea = 1;
			$lnLongitud = 220;
			$lcDescrip = $taDatos['AnalisisE'];
			$lnLinea = $this->InsertarDescripcion($lcTabla, $lnLongitud, $lnLinea, $lcDescrip);
		}
	}

	private function organizarEscalaHasbled($taDatos=[])
	{
		$lcTabla='EVOLUC';
		$lnLinea = 5951;
		$lcTexto = $this->cSL . 'Puntaje HASBLED: ' . str_repeat(' ', 3) . $taDatos[0]['lnPuntaje'] . str_repeat(' ',4) . $taDatos[0]['lcInterpretacion'];
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcTexto);
	}

	private function organizarEscalaChadsvas($taDatos=[])
	{
		$lcTabla = 'EVOLUC';
		$lnLinea = 5952;
		$lcTexto = $this->cSL . 'Puntaje CHA2DS2VAS: ' . $taDatos[0]['lnPuntaje'] . str_repeat(' ', 4) . $taDatos[0]['lcInterpretacion'];
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcTexto);
	}

	private function organizarEscalaCrusade($taDatos=[])
	{
		$lcTabla = 'EVOLUC';
		$lnLinea = 5953;
		$lcTexto = $this->cSL . 'Puntaje CRUSADE: ' . str_repeat(' ', 3) . $taDatos['lnPuntaje'] . str_repeat(' ', 3) . $taDatos['lcInterpretacion'];
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcTexto);
	}

	function organizarRegistroMedico()
	{
		$lcTabla = 'EVOLUC';
		// busca nombre de la especialidad
		$loObjEV = new Especialidad($this->cEspecialidad);
		$laResultado = $loObjEV->cNombre;

		// Inserta registro médico
		$lnLinea = $this->cTipoEvol=='EU'?7495:7999;
		$lcDescrip = $this->cSL .  'Dr. ' . trim($this->cNombreUsuario) . ' - RME: '. $this->cRegMed . ' ' . trim($laResultado) . $this->cSL;
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);

		$lnLinea = 900010;
		$lcDescrip = $this->cEspecialidad . ' - ' .  $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'] ;
		$this->InsertarRegistro($lcTabla, $lnLinea, $lcDescrip);
	}

	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tnLinea=1, $tcTexto='', $tnIndice=0, $tnIndice2=0, $tcTitulo='', $tcCodigo='')
	{
		$laChar = AplicacionFunciones::mb_str_split($tcTexto,$tnLongitud,'UTF8');

		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $tnLinea, $laDato, $tnIndice, $tnIndice2, $tcTitulo, $tcCodigo);
					$tnLinea++;
				}
				return $tnLinea - 1;
			}
		}
	}

	function InsertarRegistro($tcTabla='', $tnLinea='', $tcDescrip='', $tnIndice=0, $tnIndice2=0, $tcTitulo='', $tcCodigo='')
	{
		switch (true){
			case $tcTabla=='EVOLUC' :
				$this->aEVOLUC[]=[
					'NINEVL'=>$this->aIngreso['nIngreso'],
					'CONEVL'=>$this->nConEvo,
					'CCIEVL'=>$tnIndice,
					'CNLEVL'=>$tnLinea,
					'DESEVL'=>$tcDescrip,
					'USREVL'=>$this->cUsuCre,
					'PGMEVL'=>$this->cPrgCre,
					'FECEVL'=>$this->cFecCre,
					'HOREVL'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAORDUP' :
				$lcCharReg = '¥';
				$laWordsReg = explode($lcCharReg, $tcDescrip);
				$lcDatoCid = $laWordsReg[0];
				$lcDatoInt = $laWordsReg[1];
				$this->aRIAORDUP[]=[
					'NINORD'=>$this->aIngreso['nIngreso'],
					'CCIORD'=>$lcDatoCid,
					'INTORD'=>$lcDatoInt,
					'UMOORD'=>$this->cUsuCre,
					'PMOORD'=>$this->cPrgCre,
					'FMOORD'=>$this->cFecCre,
					'HMOORD'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ANAEPI' :
				$this->aANAEPI[]=[
					'INGAEP'=>$this->aIngreso['nIngreso'],
					'TIPAEP'=>$this->cTipoEvol,
					'CEVAEP'=>$this->nConEvo,
					'INDAEP'=>0,
					'CNLAEP'=>$tnLinea,
					'DESAEP'=>$tcDescrip,
					'USRAEP'=>$this->cUsuCre,
					'PGMAEP'=>$this->cPrgCre,
					'FECAEP'=>$this->cFecCre,
					'HORAEP'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='INFEDE' :
				$lcTipo = 'EVO';
				$lcHabita = $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'];
				$this->aINFEDE[]=[
					'INGIND'=>$this->aIngreso['nIngreso'],
					'CONIND'=>$this->nConEpd,
					'TIPIND'=>$lcTipo,
					'LININD'=>$tnLinea,
					'HABIND'=>$lcHabita,
					'DESIND'=>$tcDescrip,
					'USRIND'=>$this->cUsuCre,
					'PGMIND'=>$this->cPrgCre,
					'FECIND'=>$this->cFecCre,
					'HORIND'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='EVOUNI' :
				$this->aEVOUNI[]=[
					'INGEUN'=>$this->aIngreso['nIngreso'],
					'CEVEUN'=>$this->nConEvo,
					'INDEUN'=>$tnIndice,
					'CLIEUN'=>$tnLinea,
					'SECEUN'=>$this->aIngreso['cSeccion'],
					'CAMEUN'=>$this->aIngreso['cHabita'],
					'DESEUN'=>$tcDescrip,
					'USREUN'=>$this->cUsuCre,
					'PGMEUN'=>$this->cPrgCre,
					'FECEUN'=>$this->cFecCre,
					'HOREUN'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='REINDE' :
				$lnValorAux = ($tnIndice2==5991?$tnLinea:0);
				$lnConductaSeguir =  ($tnIndice2==5995?intval($this->cConductaSeguir):0);
				$this->aREINDE[]=[
					'INGRID'=>$this->aIngreso['nIngreso'],
					'TIPRID'=>$this->cTipoEvol,
					'CONRID'=>$this->nConCon,
					'CEXRID'=>$lnValorAux,
					'CLIRID'=>$tnLinea,
					'INDRID'=>$tnIndice,
					'IN2RID'=>$tnIndice2,
					'IN2RID'=>$tnIndice2,
					'DIARID'=>$tcTitulo,
					'DESRID'=>$tcDescrip,
					'OP3RID'=>$lnConductaSeguir,
					'USRRID'=>$this->cUsuCre,
					'PGMRID'=>$this->cPrgCre,
					'FECRID'=>$this->cFecCre,
					'HORRID'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='REINOB' :
				$lcTipoObserva = 'OB';
				$this->aREINOB[]=[
					'INGOBS'=>$this->aIngreso['nIngreso'],
					'TIPOBS'=>$tcTitulo,
					'CONOBS'=>$this->nConCon,
					'CIEOBS'=>$tcCodigo,
					'CDXOBS'=>$tnIndice,
					'LINOBS'=>$tnIndice2,
					'DESOBS'=>$tcDescrip,
					'USROBS'=>$this->cUsuCre,
					'PGMOBS'=>$this->cPrgCre,
					'FECOBS'=>$this->cFecCre,
					'HOROBS'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='CUPGRA' :
				$this->aCUPGRA[]=[
					'CONCGR'=>0,
					'INGCGR'=>$this->aIngreso['nIngreso'],
					'CCICGR'=>$tnIndice,
					'CUPCGR'=>$tcCodigo,
					'CIECGR'=>$tcDescrip['diagnostico'],
					'FINCGR'=>'',
					'MEDCGR'=>$tcDescrip['medicorealiza'],
					'ESPCGR'=>$tcDescrip['especrealiza'],
					'USCCGR'=>$this->cUsuCre,
					'PGCCGR'=>$this->cPrgCre,
					'FECCGR'=>$this->cFecCre,
					'HOCCGR'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAORD' :
				$this->aRIAORD[]=[
					'TIDORD'=>$this->aIngreso['cTipId'],
					'NIDORD'=>$this->aIngreso['nNumId'],
					'NINORD'=>$this->aIngreso['nIngreso'],
					'CCIORD'=>$tnIndice,
					'CODORD'=>$this->cEspecialidad,
					'COAORD'=>$tcCodigo,
					'RMEORD'=>$this->cRegMed,
					'FCOORD'=>$this->cFecCre,
					'FRLORD'=>$this->cFecCre,
					'HOCORD'=>$this->cHorCre,
					'RMRORD'=>$this->cRegMed,
					'FERORD'=>$this->cFecCre,
					'HRLORD'=>$this->cHorCre,
					'ESTORD'=>3,
					'ENTORD'=>$this->aIngreso['nEntidad'],
					'VIAORD'=>$this->aIngreso['cCodVia'],
					'PLAORD'=>$this->aIngreso['cPlan'],
					'SCAORD'=>$this->aIngreso['cSeccion'],
					'NCAORD'=>$this->aIngreso['cHabita'],
					'USRORD'=>$this->cUsuCre,
					'PGMORD'=>$this->cPrgCre,
					'FECORD'=>$this->cFecCre,
					'HORORD'=>$this->cHorCre,
				];
				break;	
				
			case $tcTabla=='RIADET' :
				$this->aRIADET[]=[
					'TIDDET'=>$this->aIngreso['cTipId'],
					'NIDDET'=>$this->aIngreso['nNumId'],
					'INGDET'=>$this->aIngreso['nIngreso'],
					'CCIDET'=>$tnIndice,
					'CUPDET'=>$tcCodigo,
					'FERDET'=>$this->cFecCre,
					'HRRDET'=>$this->cHorCre,
					'ESTDET'=>3,
					'USRDET'=>$this->cUsuCre,
					'PGMDET'=>$this->cPrgCre,
					'FECDET'=>$this->cFecCre,
					'HORDET'=>$this->cHorCre,
				];
				break;	

			case $tcTabla=='RIAHISL0' :
				$this->aRIAHISL0[]=[
					'NROING'=>$this->aIngreso['nIngreso'],
					'CONCON'=>$tnIndice,
					'INDICE'=>$tnIndice2,
					'SUBORG'=>$tcCodigo,
					'CONSEC'=>$tnLinea,
					'CONHIS'=>$this->nConEvo,
					'DESCRI'=>$tcDescrip,
					'NITENT'=>$this->aIngreso['nEntidad'],
					'TIDHIS'=>$this->aIngreso['cTipId'],
					'NIDHIS'=>$this->aIngreso['nNumId'],
					'USRHIS'=>$this->cUsuCre,
					'PGMHIS'=>$this->cPrgCre,
					'FECHIS'=>$this->cFecCre,
					'HORHIS'=>$this->cHorCre,
				];
				break;

		}
	}

	function guardarDatosEV($taDatosEV=[])
	{
		// Insertar registros a la tabla de AS400 EVOLUC
		$lcTabla = 'EVOLUC';
		foreach($this->aEVOLUC  as $laEVOLUC){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laEVOLUC);
		}

		// Insertar registros a la tabla de AS400 EVOUNI
		$lcTabla = 'EVOUNI';
		foreach($this->aEVOUNI  as $laEVOUNI){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laEVOUNI);
		}

		// Insertar registros a la tabla de AS400 ANAEPI
		$lcTabla = 'ANAEPI';
		foreach($this->aANAEPI  as $laANAEPI){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laANAEPI);
		}

		//Guarda Orden Hospitalizacion
		if (isset($taDatosEV['Analisis'])) {
			if($taDatosEV['Analisis']['Seguir']=='03'){
				$loObjEV = new OrdenHospitalizacion();
				$laResultado = $loObjEV->guardarOrdenHospitalizacion($taDatosEV['Analisis']['OrdenHospitalizacion'],$this->aIngreso['nIngreso'],$this->aIngreso['nNumId'],$this->cDxPpal,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre);
			}
		}

		// Actualiza información en RIORD
		if(count($this->aRIAORDUP)>0){
			$lcTabla = "RIAORD";
			foreach($this->aRIAORDUP  as $laRIAORD){
				$laDatos = [
					'INTORD'=>$laRIAORD['INTORD'],
					'UMOORD'=>$laRIAORD['UMOORD'],
					'PMOORD'=>$laRIAORD['PMOORD'],
					'FMOORD'=>$laRIAORD['FMOORD'],
					'HMOORD'=>$laRIAORD['HMOORD'],
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where(['NINORD'=>$laRIAORD['NINORD'],'CCIORD'=>$laRIAORD['CCIORD']])->actualizar($laDatos);
			}
		}

		// Actualiza tabla REINCA cuando se esta avalando
		if($this->ParaAvalar){
			$lcTabla = 'REINCA';
			$laDatos = [
				'ESTRIC'=>'VA',
				'CEVRIC'=>$this->nConEvo,
				'UMORIC'=>$this->cUsuCre,
				'PMORIC'=>$this->cPrgCre,
				'FMORIC'=>$this->cFecCre,
				'HMORIC'=>$this->cHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->where(['INGRIC'=>$this->aIngreso['nIngreso'],'CONRIC'=>$this->nConAval,'TIPRIC'=>$this->cTipoEvol,])->actualizar($laDatos);
		}
		// Guarda datos epidemiologia
		if ($this->lGuardarEPD){
			// Crear registro en la INFECA
			$lcTabla = 'INFECAL01';
			$laDatos = [
				'INGINC'=>$this->aIngreso['nIngreso'],
				'CONINC'=>$this->nConEpd,
				'USRINC'=>$this->cUsuCre,
				'PGMINC'=>$this->cPrgCre,
				'FECINC'=>$this->cFecCre,
				'HORINC'=>$this->cHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

			// Insertar registros a la tabla de AS400 INFEDE
			$lcTabla = 'INFEDE';
			foreach($this->aINFEDE  as $laINFEDE){
				$llResultado = $this->oDb->tabla($lcTabla)->insertar($laINFEDE);
			}
		}

		// Guardar datos Conciliación
		if (isset($taDatosEV['Conciliacion'])) {
			if(($taDatosEV['Conciliacion']['Modifica']??'')=='true'){
				// Guardar Datos conciliacion
				$loObjEV = new Conciliacion();
				$laResultado = $loObjEV->guardarDatosC($taDatosEV['Conciliacion'], $this->aIngreso, $this->cPrgCre, $this->nConEvo);
			}
		}

		// Guardar Datos Nihss
		if (isset($taDatosEV['Nihss'])) {
			if($taDatosEV['Nihss']['TotalN'] !== ""){
				$lcTipoEV = $taDatosEV['Tipo'];
				$lcSeccion = $this->aIngreso['cSeccion'];
				$lcTipo = 'EVPL';
				if($lcTipoEV=='C' || $lcTipoEV=='V' || ($lcTipoEV=='P' && ($lcSeccion=='CC' || $lcSeccion=='CV' || $lcSeccion=='CI' || $lcSeccion=='CA' ))){
					$lcTipo= 'EVUC';
				}
				$loObjEV = new Doc_NIHSS();
				$laResultado = $loObjEV->guardarDatosN($taDatosEV['Nihss'], $this->aIngreso['nIngreso'], $lcTipo, $this->nConEvo, $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
			}
		}

		// Guardar Escalas
		if(!empty($taDatosEV['escalaHasbled'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsHasbledHC($taDatosEV['escalaHasbled'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo , $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		if(!empty($taDatosEV['escalaChadsvas'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsChadsvasHC($taDatosEV['escalaChadsvas'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		if(!empty($taDatosEV['escalaCrusade'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsCrusadeHC($taDatosEV['escalaCrusade'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		// Guarda Diagnosticos
		if (isset($taDatosEV['Diagnostico'])) {
			$lcDxFallece = substr($taDatosEV['Analisis']['cCodigoDxFallece']??'',0,5);
			$laDatosFallece = [
							'HorFallece' => $taDatosEV['Analisis']['HoraFallece']??'',
							'FecFallece' => $taDatosEV['Analisis']['FechaFallece']??'',
							'CieFallece' => $lcDxFallece,
							];
			$lcTipoDocVia = $this->aIngreso['cCodVia'] == '01' ? 'ER' : ($this->aIngreso['cCodVia'] == '05' ? 'EP' : ($this->aIngreso['cCodVia'] == '06' ? 'EC' : ''));
			$loObjEV = new Diagnostico();
			$laResultado = $loObjEV->guardarDiagnostico($taDatosEV['Diagnostico'],$this->aIngreso['nIngreso'],$this->nConEvo,$this->aIngreso['cTipId'],$this->aIngreso['nNumId'],$this->aIngreso['nEntidad'],$lcTipoDocVia,$this->cConductaSeguir,$this->cDescripcionConducta,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre, $taDatosEV['Analisis']['Estado'], $laDatosFallece);
		}

		// Una vez guarda diagnosticos si el paciente fallece se actualiza los registros
		if (isset($taDatosEV['Analisis'])) {
			// Guarda historico del ingreso para via urgencias y conducta es salida
			if($taDatosEV['Analisis']['Seguir']=='01'){
				$lnEstado = 14;
				$lcEstadoGraba = "S";
				$this->CobroOxigeno($lnEstado, $lcEstadoGraba);
				$this->PacienteUrgencias();
				$this->guardarCensoUrgencias('S');

				if($this->lPacienteUrgencias){
					// Crear al registro Historico de Ingreso
					$laTempEV = $this->oDb
						->select('PLAING, VIAING, FEIING, HORING, FEEING, HREING, ESTING')
						->from('RIAINGL15')
						->where('NIGING', '=', $this->aIngreso['nIngreso'])
						->get('array');

					if(is_array($laTempEV)){
						if(count($laTempEV)>0){

							// Crear registro en la INFECA
							$lcTabla = 'RIAINGD';
							$laDatos = [
								'TIDIND'=>$this->aIngreso['cTipId'],
								'NIDIND'=>$this->aIngreso['nNumId'],
								'NIGIND'=>$this->aIngreso['nIngreso'],
								'NHCIND'=>$this->aIngreso['nHistoria'],
								'PLAIND'=>$laTempEV['PLAING'],
								'VIAIND'=>$laTempEV['VIAING'],
								'FEIIND'=>$laTempEV['FEIING'],
								'HININD'=>$laTempEV['HORING'],
								'FEEIND'=>$laTempEV['FEEING'],
								'HREIND'=>$laTempEV['HREING'],
								'ESTIND'=>$laTempEV['ESTING'],
								'USRIND'=>$this->cUsuCre,
								'PGMIND'=>$this->cPrgCre,
								'FECIND'=>$this->cFecCre,
								'HORIND'=>$this->cHorCre,
							];
							$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
						}
					}
					unset($laTempEV);
				}
			}else{
				$this->guardarCensoUrgencias('');
			}
		}

		if(!empty($taDatosEV['Actividadfisica'])){
			$this->guardarActividadFisica($taDatosEV);
		}

		if(!empty($taDatosEV['DatosSadPersons'])){
			$loObjEV = new EscalaSadPersons();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEscSadPersonsHC($taDatosEV['DatosSadPersons'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		$lcTabla = 'CUPGRA';
		$loGrabarCups = new Consecutivos();
		foreach($this->aCUPGRA  as $laCUPGRA){
			$laCUPGRA['CONCGR'] = $loGrabarCups->fCalcularGrabarProcedimentos();
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laCUPGRA);
		}

		$lcTabla = 'RIAORD';
		foreach($this->aRIAORD  as $laRIAORDC){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAORDC);
		}
		
		$lcTabla = 'RIADET';
		foreach($this->aRIADET  as $laRIADET){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIADET);
		}
		
		$lcTabla = 'RIAHISL0';
		foreach($this->aRIAHISL0  as $laRIAHISL0){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAHISL0);
		}	}

	private function guardarDatosAVAL($taDatosEV=[])
	{
		// Crear registro de Encabezado de aval en tabla REINCA
		$lcTabla = 'REINCA';
		$laDatos = [
			'INGRIC'=>$this->aIngreso['nIngreso'],
			'TIPRIC'=>$this->cTipoEvol,
			'CONRIC'=>$this->nConCon,
			'OP1RIC'=>$this->cInicialEvol,
			'USRRIC'=>$this->cUsuCre,
			'PGMRIC'=>$this->cPrgCre,
			'FECRIC'=>$this->cFecCre,
			'HORRIC'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		// Insertar registros a la tabla de AS400 REINDE
		$lcTabla = 'REINDE';
		foreach($this->aREINDE  as $laREINDE){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laREINDE);
		}

		// Insertar registros de caracterización de diagnósticos
		$lcTabla = 'REINOB';
		foreach($this->aREINOB  as $laREINOB){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laREINOB);
		}

		if(!empty($taDatosEV['escalaHasbled'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsHasbledHC($taDatosEV['escalaHasbled'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosEV['escalaChadsvas'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsChadsvasHC($taDatosEV['escalaChadsvas'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosEV['escalaCrusade'])){
			$loObjEV = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEsCrusadeHC($taDatosEV['escalaCrusade'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		// Inserta registro de actividad Física
		if(!empty($taDatosEV['Actividadfisica'])){
			$this->guardarActividadFisica($taDatosEV);
		}

		if(!empty($taDatosEV['DatosSadPersons'])){
			$loObjEV = new EscalaSadPersons();
			$lnConEvo = 0;
			$laResultado = $loObjEV->guardarDatosEscSadPersonsHC($taDatosEV['DatosSadPersons'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		// Orden de Hospitalización
		if(!empty($taDatosEV['Analisis']['OrdenHospitalizacion'])){
			$loObjEV = new OrdenHospitalizacion();
			$laResultado = $loObjEV->guardarOrdenAval($taDatosEV['Analisis']['OrdenHospitalizacion'],$this->aIngreso['nIngreso'],$this->aIngreso['nNumId'],$this->cDxPpal,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre,$this->nConCon);
		}

		//Insertar registros de la escala NIHSS
		$llevento = isset($taDatos['Eventualidad']);

		if (isset($taDatos['Eventualidad'])) {
			if($taDatosEV['Nihss']['TotalN']!==""){
				$loObjEV = new Doc_NIHSS();
				$laResultado = $loObjEV->guardarDatosN($taDatosEV['Nihss'], $this->aIngreso['nIngreso'],  $this->cTipoEvol, $this->nConCon, $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
			}
		}
	}

	function guardarCensoUrgencias($tcEstado='')
	{
		$llPacienteHD=false;
		$loHistoriaClinica = new Historia_Clinica();
		$lnIngreso=$this->aIngreso['nIngreso'];
		$llPacienteHD=$loHistoriaClinica->verificarPacienteHDia($lnIngreso);

		if (in_array(strval(intval($this->aIngreso['cCodVia'])), $this->aViasCenso)) {
			if (!$llPacienteHD){
				$lnTiporegistro=10;
				$lnEstado=$tcEstado=='S' ? 9 : 0;
				$loConsultaUrgencias = new ConsultaUrgencias();
				$laParametros=[];
				$lcUbicacionCenso=$loConsultaUrgencias->ubicacionCensoPacientes($lnIngreso);
				$lcDatosregistro=$this->cRegMed.'~'.$lcUbicacionCenso.'~'.$lnEstado;

				$laDatosCenso = $this->oDb
					->select('ESTURC')
					->from('CENURC')
					->where('INGURC', '=', $lnIngreso)
					->get('array');
				if($this->oDb->numRows()>0){
					$lnEstado=$tcEstado=='S' ? 9 : intval($laDatosCenso['ESTURC']);
					$lcTabla='CENURC';
					$laDatosUpd = [
						'UBPURC'=>$lcUbicacionCenso,
						'ESTURC'=>$lnEstado,
						'MEDURC'=>$this->cRegMed,
						'UMOURC'=>$this->cUsuCre, 'PMOURC'=>$this->cPrgCre, 'FMOURC'=>$this->cFecCre, 'HMOURC'=>$this->cHorCre,
					];
					$llResultado = $this->oDb->tabla($lcTabla)->where('INGURC', '=', $lnIngreso)->actualizar($laDatosUpd);
				}else{
					$laParametrosCab=[
						'ingreso'=>$lnIngreso,
						'registroguarda'=>$this->cRegMed,
						'programaguarda'=>$this->cPrgCre,
						'ubicacion'=>$lcUbicacionCenso,
						'estadocenso'=>$lnEstado,
					];
					$laResultado = $loConsultaUrgencias->crearRegistroCenso($laParametrosCab);
				}
				$laParametros=[
					'ingreso'=>$lnIngreso,
					'tiporegistro'=>$lnTiporegistro,
					'datosregistro'=>$lcDatosregistro,
					'altatemprana'=>'',
					'programaguarda'=>$this->cPrgCre,
				];
				$laResultado = $loConsultaUrgencias->registrarInformacion($laParametros);
			}
		}
	}

	function CobroOxigeno($tnEstado=0, $tcEstadoGraba='')
	{
		$loOrdMedOxi = new OrdMedOxigeno();
		$loOrdMedOxi->obtenerConfigIng($this->aIngreso);
		$loOrdMedOxi->nEstado = $tnEstado;
		$loOrdMedOxi->cEstadoGraba = $tcEstadoGraba;
		$laMedico = [
			'regmed'=>$this->cRegMed,
			'codesp'=>$this->cEspecialidad,
		];
		$laLog = [
			'usuario'=>$this->cUsuCre,
			'programa'=>$this->cPrgCre,
			'fecha'=>$this->cFecCre,
			'hora'=>$this->cHorCre,
		];
		$loOrdMedOxi->CobroOxigeno($laMedico, $laLog);
	}

	public function consultatextofallece()
	{
		$lcTextoFallece = '';
		$laParametros = $this->oDb
			->select('trim(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FORMEDIC')
			->where('CL1TMA', '=', 'MFALLECE')
			->where('ESTTMA', '=', '')
			->get('array');

		if (is_array($laParametros) && count($laParametros)>0){
			$lcTextoFallece = $laParametros['DESCRIPCION'];
		}
		return $lcTextoFallece;
	}

	public function consultaReconocimiento()
	{
		$aParametros = [];
		$lcHabilidar=$laUsuarios='';

		if(isset($this->oDb)){
			$laParametros = $this->oDb
			->select('trim(OP1TMA) HABILITAR, trim(DE2TMA) USUARIOS')
			->from('TABMAE')
			->where('TIPTMA=\'FORMEDIC\' AND CL1TMA=\'USURECON\' AND ESTTMA=\' \'')
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcHabilidar=$laParametros['HABILITAR'];
				$laUsuarios=explode(',', $laParametros['USUARIOS']);
			}
			$aParametros=[
				'habilitar'=>$lcHabilidar,
				'usuarios'=>$laUsuarios,
			];
		}
		unset($laParametros);
		return $aParametros;
	}

	public function PacienteUrgencias()
	{
		$loObjEV = new ConsultaUrgencias();
		$this->lPacienteUrgencias = $loObjEV->esPacienteUrgencias($this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->aIngreso['cSeccion']);
	}

	function Diferencia_Horas($tnHoraInicial=0, $tnHoraFinal=0, $tlDiaDif=false)
	{
		$lnDifHoras = $lnTemp = 0;
		$oTabmae = $this->oDb->ObtenerTabMae('OP3TMA', 'FORMEDIC', ['CL1TMA'=>'OXIGENO','CL2TMA'=>'MINUCBR','ESTTMA'=>'']);
		$lnMinutosLimite = AplicacionFunciones::getValue($oTabmae, 'OP3TMA', 0) ;

		if($tlDiaDif && $tnHoraInicial>$tnHoraFinal){
			$lnTemp = $tnHoraInicial;
			$tnHoraInicial = $tnHoraFinal;
			$tnHoraFinal = $lnTemp;
		}

		$lcFecha1 = AplicacionFunciones::formatFechaHora('fechahora', 20000101 . $tnHoraInicial);
		$lnFecha2 = ($tlDiaDif==true?20000102:20000101);
		$lcFecha2 = AplicacionFunciones::formatFechaHora('fechahora', $lnFecha2 . $tnHoraFinal);
		$fecha1 = new \DateTime($lcFecha1);
		$fecha2 = new \DateTime($lcFecha2);
		$loIntervalo = $fecha1->diff($fecha2);
		$lnDifHoras = ($loIntervalo->d * 24) + $loIntervalo->h + ($loIntervalo->i>$lnMinutosLimite?1:0);
		return $lnDifHoras;
	}

	function datoPacienteUrgencias()
	{
		return $this->lPacienteUrgencias ;
	}

	public function ConsultarAntecedentesUCI($tnIngreso=0)
	{
		$lcDescrip = '';
		if($tnIngreso>0){
			$laTempEV = $this->oDb
				->select('CONEVL, DESEVL')
				->from('EVOLUC')
				->where("NINEVL=$tnIngreso AND (CNLEVL BETWEEN 1201 AND 1299)")
				->orderBy('CONEVL DESC, CNLEVL ASC')
				->getAll('array');

			if(is_array($laTempEV)){
				if(count($laTempEV)>0){
					$lnConsec = $laTempEV[0]['CONEVL'];
					foreach($laTempEV as $laDescrip){
						if($laDescrip['CONEVL']==$lnConsec){
							$lcDescrip .= $laDescrip['DESEVL'];
						}
					}
				}
			}
		}
		return $lcDescrip;
	}

	private function guardarActividadFisica($taDatosEV=[])
	{
		$lnConEvo = 0;
		$aActividadFisica = [
			'Datos' => $taDatosEV['Actividadfisica'],
			'Ingreso' => $this->aIngreso['nIngreso'],
			'Via' => $this->aIngreso['cCodVia'],
			'ConsecConsulta' => $this->nConCon,
			'ConsecEvolucion' => $lnConEvo,
			'Entidad' => $this->aIngreso['nEntidad'],
			'TipoIde' => $this->aIngreso['cTipId'],
			'NroIde' => $this->aIngreso['nNumId'],
			'UsuarioCrea' => $this->cUsuCre,
			'ProgramaCrea' => $this->cPrgCre,
			'FechaCrea' => $this->cFecCre,
			'HoraCrea' => $this->cHorCre,
			'Tiporeg' => $this->cTipoEvol,
			'Indice' => 10,
			'SubIndice' => 15,
			'CodigoAntec' => 19,
			'SubCodigoAntec' => '15',
		];
		$loActividadFisica = new EscalaActividadFisica();
		$laResultado = $loActividadFisica->guardarDatosAF($aActividadFisica);
	}

}