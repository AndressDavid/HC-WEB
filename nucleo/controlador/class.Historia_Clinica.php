<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';
require_once __DIR__ . '/class.Conciliacion.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once __DIR__ . '/class.Doc_NIHSS.php';
require_once __DIR__ . '/class.Cobros.php';
require_once __DIR__ . '/class.Diagnostico.php';
require_once __DIR__ . '/class.DatosPlanManejo.php';
require_once __DIR__ . '/class.OrdenHospitalizacion.php';
require_once __DIR__ . '/class.EscalasRiesgoSangrado.php';
require_once __DIR__ . '/class.EscalaSadPersons.php';
require_once __DIR__ . '/class.DatosAmbulatorios.php';
require_once __DIR__ . '/class.Cup.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.SmbClient.php';
require_once __DIR__ . '/class.EscalaActividadFisica.php';
require_once __DIR__ . '/class.ConsultaUrgencias.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\Historia_Clinica_Ingreso;
use NUCLEO\ParametrosConsulta;
use NUCLEO\Conciliacion;
use NUCLEO\FormulacionParametros;
use NUCLEO\Doc_NIHSS;
use NUCLEO\Cobros;
use NUCLEO\Diagnostico;
use NUCLEO\DatosPlanManejo;
use NUCLEO\OrdenHospitalizacion;
use NUCLEO\EscalasRiesgoSangrado;
use NUCLEO\EscalaSadPersons;
use NUCLEO\Cup;
use NUCLEO\EscalaActividadFisica;
use NUCLEO\ConsultaUrgencias;

class Historia_Clinica
{
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $cRegMed = '';
	protected $cEspecialidad = '';
	protected $cCodPro = '';
	protected $cConEvo = '';
	protected $nConCon = 0;
	protected $nConAval= 0;
	protected $nConCit = 0;
	protected $nConEvo = 0;
	protected $nFecAud = 0;
	protected $nHorAud = 0;
	protected $aDatOrdenAmb = [];
	protected $cConductaSeguir = '';
	protected $cEstadoSalida = '';
	protected $cDescripcionConducta = '';
	protected $cTipoNihss = '';
	protected $cViaInicial = '';
	protected $cDescViaInicial = '';
	protected $cPrincipalDiagnostico = '';
	protected $cTipoDiagnostico = '';
	protected $cCobroElectrocardiograma = '';
	protected $aRIAHIS = [];
	protected $aANTPAC = [];
	protected $aANTPAD = [];
	protected $aRIAEXF = [];
	protected $aHISINT = [];
	protected $aEXFINT = [];
	protected $aServidor = [];
	protected $aDxXML = [];
	protected $aRIAORD = [];
	protected $aRIADET = [];
	protected $aRIAHISL0 = [];
	protected $aCupElectro = [];
	protected $aViasElectro = [];

	protected $aIngreso = [];
	protected $oHcIng = null;
	protected $oDb = null;
	protected $bReqAval = false;
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
		$this->datosElectrocardiograma();
		$this->viasElectrocardiograma();
		$this->parametrosDeControl();
	}

	public function parametrosDeControl()
	{
		if(isset($this->oDb)){
			$this->cCobroElectrocardiograma=trim($this->oDb->obtenerTabmae1('OP1TMA', 'CONTPRO', "CL1TMA='COBELEC' AND ESTTMA=''", null, ''));
		}
	}	
	
	public function datosElectrocardiograma()
	{
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('TRIM(A.DE2TMA) CUPS, TRIM(B.ESPCUP) ESPECIALIDAD, B.CAPCUP FINALIDAD, TRIM(C.CL1TMA) FINALIDADHOMOLOGO')
				->from('TABMAE A')
				->leftJoin("RIACUP B", "A.DE2TMA=B.CODCUP", null)
				->leftJoin("TABMAE C", "CHAR(B.CAPCUP)=C.CL3TMA", null)
				->where('A.TIPTMA', '=', 'HCPARAM')
				->where('A.CL1TMA', '=', 'CUPELEC')
				->where('A.ESTTMA', '=', '')
				->where('C.TIPTMA', '=', 'CODFIN')
				->get('array');
			$this->aCupElectro=$laParametros;
			unset($laParametros);
		}
	}

	public function viasElectrocardiograma()
	{
		if(isset($this->oDb)){
			$loTabmae=$this->oDb->ObtenerTabMae('DE2TMA', 'HCPARAM', ['CL1TMA'=>'VIAELEC', 'ESTTMA'=>'']);
			$lcViasElectrocardiogramas=trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''))));
			$lcViasElectrocardiogramas = trim(str_replace(' ', '', $lcViasElectrocardiogramas));
			$this->aViasElectro=explode(',', $lcViasElectrocardiogramas);
		}
	}	

	public function verificarHC($taDatos=[])
	{
		$this->IniciaDatosIngreso($taDatos['Ingreso']);
		$this->cViaInicial = $this->aIngreso['cCodVia'];
		$this->cDescViaInicial = $this->aIngreso['cDesVia'];
		$this->ParaAvalar = ($taDatos['PorAvalar']??'No')=='Si';
		$this->nConAval = $taDatos['nConCons'];
		$lcCup = $taDatos['cCodCup'];
		$lcCondicionCup = '$llCondicionCup='. trim($this->oDb->obtenerTabMae1('OP5TMA', 'HCPARAM', 'CL1TMA=\'PROCCEXT\' AND ESTTMA=\'\'', null, 'substr($lcCup,0,4)==\'8903\'')).';';
		eval($lcCondicionCup);
		$lbCondicionCE = ($this->aIngreso['cCodVia']=='02' || $llCondicionCup) && empty($taDatos['nConCons']) && $taDatos['cFormAnterior']!=='hos';
		if ($lbCondicionCE) {
			$this->aIngreso['cCodVia']='02';
			$this->aIngreso['cDesVia']='CONSULTA EXT.';
		}

		if(!$this->verificarMotivo($taDatos['MotivoC']['Causa'])) {
			return $this->aError;
		}

		if(!$this->verificarAntecedentes($taDatos['Antecedentes']))  {
			return $this->aError;
		}

		if(!$this->verificarActividadfisica($taDatos['Actividadfisica']))  {
			return $this->aError;
		}

		if(!$this->verificarConciliacion($taDatos['Conciliacion']))  {
			return $this->aError;
		}

		if($this->aIngreso['cCodVia']!='02'){
			if(!$this->verificarExamen($taDatos['Examen']))  {
				return $this->aError;
			}
		}

		if(!empty($taDatos['Nihss']['TotalN'])){
			if(!$this->verificarNIHSS($taDatos['Nihss']))  {
				return $this->aError;
			}
		}

		if(!$this->verificarDiagnosticos($taDatos['Diagnostico']))  {
			return $this->aError;
		}

		if(!$this->verificarPlanManejo($taDatos['Planmanejo']))  {
			return $this->aError;
		}

		if(!$this->verificarOrdenHospitalizacion($taDatos['Planmanejo']['OrdenHospitalizacion']))  {
			return $this->aError;
		}

		if(!empty($taDatos['escalaHasbled'])){
			if(!$this->verificarEscalaHasbled($taDatos['Diagnostico'], $taDatos['escalaHasbled']))  {
				return $this->aError;
			}
		}

		if(!empty($taDatos['escalaChadsvas'])){
			if(!$this->verificarEscalaChadsvas($taDatos['Diagnostico'], $taDatos['escalaChadsvas'])){
				return $this->aError;
			}
		}

		if(!empty($taDatos['escalaCrusade'])){
			if(!$this->verificarEscalaCrusade($taDatos['Diagnostico'], $taDatos['escalaCrusade'])){
				return $this->aError;
			}
		}

		if(!empty($taDatos['DatosSadPersons'])){
			if(!$this->verificarEscalaSadPersons($taDatos['Diagnostico'], $taDatos['DatosSadPersons'])){
				return $this->aError;
			}
		}

		if (isset($taDatos['Ambulatorio'])) {
			if(!$this->verificarAmbulatorios($taDatos['Ambulatorio'],$taDatos['Diagnostico'][0]['CODIGO']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['Finalidad'])) {
			if(!$this->verificarFinalidad($taDatos['Finalidad']['finalidad']))  {
				return $this->aError;
			}
		}

		if (isset($taDatos['InterpretaExam'])) {
			if(!$this->verificarInterpretaExam($taDatos['InterpretaExam']))  {
				return $this->aError;
			}
		}

		return $this->aError;
	}

	function verificarMotivo($tcDato='')
	{
		$loObjHC = new ParametrosConsulta();
		$loObjHC->ObtenerTipoCausa();
		$laResultado = $loObjHC->tipoCausa($tcDato);
		if(empty($laResultado)){
			$this->aError = [
				'Mensaje'=>'No existe Tipo de causa en la base de datos',
				'Objeto'=>'selTipoCausa',
				'Valido'=>false,
			];
		}
		return $this->aError['Valido'];
	}

	function verificarAntecedentes($tcDato='')
	{
		extract($this->aIngreso);

		// Validar objetos obligatorios
		$loParCon = new ParametrosConsulta();
		$loParCon->ObjetosObligatoriosHC('Antece');
		$loObjetos = $loParCon->ObjObligatoriosHC();
		unset($loParCon);
		foreach($loObjetos as $loObj){
			$lbObliga=true;
			if(!empty($loObj['REQUIERE'])){
				$lcEval='$lbObliga='.$loObj['REQUIERE'].';';
				eval($lcEval);
			}
			if($lbObliga){
				$laReglas=json_decode($loObj['REGLAS'],true);
				$lcClave=array_keys($laReglas)[0];
				$laRegla=array_values($laReglas)[0];
				if($laRegla['required']??false){
					$lbValido=true;
					if(isset($tcDato[$lcClave])){
						if(empty($tcDato[$lcClave])){
							$lbValido=false;
						}
					}else{
						$lbValido=false;
					}
					if(!$lbValido){
						$this->aError = [
							'Mensaje'=>"Antecedente $lcClave es obligatorio.",
							'Objeto'=>'FormAntecedentes',
							'Valido'=>false,
						];
					}
				}
			}
		}

		// Validar Vacuna Covid19
		if($this->aError['Valido']){
			$lcObligar=$this->oDb->obtenerTabMae1('TRIM(DE2TMA)','COVID19',['CL1TMA'=>'VACANTEC','CL2TMA'=>'OBLIGAR','CL3TMA'=>'01','ESTTMA'=>''],null,'')=='SI';
			if($lcObligar){
				if(count($tcDato['antVacunaCovid'])<1){
					$this->aError = [
						'Mensaje'=>"Antecedente Vacuna Covid 19 es obligatorio.",
						'Objeto'=>'selVacunaCovid',
						'Valido'=>false,
					];
				}
			}
		}
		return $this->aError['Valido'];
	}

	function verificarActividadfisica($taDatos=[])
	{
		$loActividadFisica = new EscalaActividadFisica();
		$this->aError = $loActividadFisica->validacion($taDatos);
		return $this->aError['Valido'];
	}

	function verificarConciliacion($taDatos=[])
	{
		$loObjHC = new Conciliacion();
		$this->aError = $loObjHC->verificarDatosC($taDatos,$this->aIngreso['aEdad']);
		return $this->aError['Valido'];
	}

	function verificarExamen($taDatos=[])
	{
		// Validación rango TAS entre 40 y 300
		$lnIndTAS = $taDatos['chk_TAS'] ?? 0;
		if($lnIndTAS==0 && $this->aIngreso['aEdad']['y'] > 17){
			if($taDatos['tas']<40 || $taDatos['tas']>300){
				$this->aError = [
					'Mensaje'=>'El valor de la tensión arterial sistólica debe estar entre 40 y 300',
					'Objeto'=>'txtTAS',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Validación rango TAD entre 20 y 150
		$lnIndTAD = $taDatos['chk_TAD'] ?? 0;
		if($lnIndTAD==0 && $this->aIngreso['aEdad']['y'] > 17){
			if($taDatos['tad']<20 || $taDatos['tad']>150){
				$this->aError = [
					'Mensaje'=>'El valor de la tensión arterial diastólica debe estar entre 20 y 150',
					'Objeto'=>'txtTAD',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Validación rango FC entre 20 y 300
		$lnIndFC = $taDatos['chk_FC'] ?? 0;
		if($lnIndFC==0){
			if($taDatos['fc']<20 || $taDatos['fc']>300){
				$this->aError = [
					'Mensaje'=>'El valor de la frecuencia cardiáca debe estar entre 20 y 300',
					'Objeto'=>'txtFC',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Validación rango FR entre 10 y 80
		$lnIndFR = $taDatos['chk_FR'] ?? 0;
		if($lnIndFR==0){
			if($taDatos['fr']<10 || $taDatos['fr']>80){
				$this->aError = [
					'Mensaje'=>'El valor de la frecuencia respiratoria debe estar entre 10 y 80',
					'Objeto'=>'txtFR',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Validación rango TEMPERATURA entre 34 y 42.99
		if($taDatos['temp']<34 || $taDatos['temp']>=43){
			$this->aError = [
				'Mensaje'=>'El valor de la temperatura debe estar entre 34 y 42.99',
				'Objeto'=>'txtTemp',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Validación rango SATURACION entre 0 y 100
		if($taDatos['so2'] > 100){
			$this->aError = [
				'Mensaje'=>'El valor de la saturación debe ser menor a 100',
				'Objeto'=>'txtSo2',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		return $this->aError['Valido'];
	}

	function verificarNIHSS($taDatos=[])
	{
		$loObjHC = new Doc_NIHSS();
		$this->aError = $loObjHC->verificarDatosN($taDatos);

		return $this->aError['Valido'];
	}

	function verificarDiagnosticos($taDatos=[])
	{
		$loDiagnostico = new Diagnostico();
		$this->aError = $loDiagnostico->validacion($taDatos, $this->aIngreso['cCodVia'], 'H');
		return $this->aError['Valido'];
	}

	function verificarPlanManejo($taDatos=[])
	{
		$loObjHC = new ParametrosConsulta();

		if(!empty($taDatos['conductaSeguir'])){
			$loObjHC->ObtenerConductaSeguir($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia'],$this->aIngreso['cSeccion'],'');
			$laResultado = $loObjHC->tipoConductaSeguir($taDatos['conductaSeguir']);

			if(empty($laResultado)){
				$this->aError = [
					'Mensaje'=>'No existe tipo conducta a seguir en la base de datos',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}
		}

		if(!empty($taDatos['tuvoElectro'])){
			if(trim($taDatos['tuvoElectro']) == 'Si' && trim($taDatos['TuvoElectrocardiograma']) == '' ){
				$this->aError = [
					'Mensaje'=>'Descripción electrocardiograma NO valido',
					'Objeto'=>'txtTuvoElectrocardiograma',
					'Valido'=>false,
				];
			}
		}

		if(trim($taDatos['conductaSeguir']) == '01' && trim($taDatos['estadoSalidaPlan']) == '' ){
			$this->aError = [
				'Mensaje'=>'Estado salida NO valido',
				'Objeto'=>'selEstadoSalidaPlan',
				'Valido'=>false,
			];
		}

		if(trim($taDatos['conductaSeguir']) == '01' && trim($taDatos['estadoSalidaPlan']) != '' ){
			$loObjHC = new ParametrosConsulta();
			$loObjHC->ObtenerEstadoSalida();
			$laResultado = $loObjHC->estadoSalida($taDatos['estadoSalidaPlan']);

			if(empty($laResultado)){
				$this->aError = [
					'Mensaje'=>'No existe tipo estado salida en la base de datos',
					'Objeto'=>'selEstadoSalidaPlan',
					'Valido'=>false,
				];
			}
		}

		if(trim($taDatos['doctorInforma']) != 'N' && trim($taDatos['doctorInforma']) != 'S'){
			$this->aError = [
				'Mensaje'=>'No existe tipo conducta a seguir en la base de datos',
				'Objeto'=>'SelDoctorInforma',
				'Valido'=>false,
			];
		}

		if(!empty($taDatos['ModalidadGrupo'])){
			$loObjHC->ObtenerModalidadGrupoServicio();
			$laResultado = $loObjHC->tipoModalidadGrupoServicio($taDatos['ModalidadGrupo']);

			if(empty($laResultado)){
				$this->aError = [
					'Mensaje'=>'No existe tipo modalidad grupo servicio en la base de datos',
					'Objeto'=>'SelModalidadGrupo',
					'Valido'=>false,
				];
			}
		}

		if (isset($taDatos['SelAtencionDomiciliaria'])) {
			if(trim($taDatos['SelAtencionDomiciliaria']) != 'N' && trim($taDatos['SelAtencionDomiciliaria']) != 'S'){
				$this->aError = [
					'Mensaje'=>'No existe Atención derivada domiciliaria en la base de datos',
					'Objeto'=>'SelAtencionDomiciliaria',
					'Valido'=>false,
				];
			}
		}
		return $this->aError['Valido'];
	}

	function verificarOrdenHospitalizacion($taDatos=[])
	{
		$loObjHC = new OrdenHospitalizacion();
		$this->aError = $loObjHC->validacion($taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaHasbled($taDx=[], $taDatos=[])
	{
		$loObjHC = new EscalasRiesgoSangrado();
		$this->aError = $loObjHC->validarEscalaHasbled($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaChadsvas($taDx=[], $taDatos=[])
	{
		$loObjHC = new EscalasRiesgoSangrado();
		$this->aError = $loObjHC->validarEscalaChadsvas($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaCrusade($taDx=[], $taDatos=[])
	{
		$loObjHC = new EscalasRiesgoSangrado();
		$this->aError = $loObjHC->validarEscalaCrusade($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarEscalaSadPersons($taDx=[], $taDatos=[])
	{
		$loObjHC = new EscalaSadPersons();
		$this->aError = $loObjHC->validarEscalaSadPersons($taDx, $taDatos);
		return $this->aError['Valido'];
	}

	function verificarAmbulatorios($taDatos=[], $tcDxPrinc)
	{
		$loAmbulatorio = new DatosAmbulatorios();
		$loAmbulatorio->setIngreso($this->aIngreso);
		$loAmbulatorio->setDxPrincipal($tcDxPrinc);
		$this->aError = $loAmbulatorio->validacion($taDatos);
		return $this->aError['Valido'];
	}

	private function verificarFinalidad($tcDato='')
	{
		$loObjHC = new ParametrosConsulta();
		$loObjHC->ObtenerFinalidad('C');
		if(empty($loObjHC->finalidad($tcDato))){
			$this->aError = [
				'Mensaje'=>'No existe Finalidad en la base de datos',
				'Objeto'=>'selFinalidad',
				'Valido'=>false,
			];
		}
		return $this->aError['Valido'];
	}

	private function verificarInterpretaExam($taDatos=[])
	{
		$loCup = new Cup();

		foreach($taDatos as $laDato) {

			// valida cups válidos
			if (!empty($laDato['cup'])) {
				$loCup->cargarDatos($laDato['cup']);
				if (empty($loCup->cCup)) {
					$this->aError = [
						'Mensaje'=>"Código Cup {$laDato['cup']}, no encontrado (procedimiento {$laDato['cup']})",
						'Objeto'=>'txtIntrExamProc',
						'Valido'=>false,
					];
					break;
				}
			}
			if (empty($laDato['fecha'])) {
				$this->aError = [
					'Mensaje'=>"Falta fecha en el procedimiento {$laDato['procedimiento']}",
					'Objeto'=>'txtIntrExamProc',
					'Valido'=>false,
				];
				break;
			}
			if (empty($laDato['procedimiento'])) {
				$this->aError = [
					'Mensaje'=>'Falta descripción de uno de los procedimientos.',
					'Objeto'=>'txtIntrExamProc',
					'Valido'=>false,
				];
				break;
			}
			if (!in_array($laDato['codresult'], ['1','2'])) {
				$this->aError = [
					'Mensaje'=>"Resultado incorrecto en el procedimiento {$laDato['procedimiento']}",
					'Objeto'=>'txtIntrExamProc',
					'Valido'=>false,
				];
				break;
			}
			if ($laDato['codresult']=='2' && empty($laDato['interpreta'])) {
				$this->aError = [
					'Mensaje'=>"Falta la interpretación del procedimiento {$laDato['procedimiento']}, que tiene resultado Anormal",
					'Objeto'=>'txtIntrExamProc',
					'Valido'=>false,
				];
				break;
			}
		}

		return $this->aError['Valido'];
	}
	
	public function GuardarHC($taDatos=[])
	{
		// Validar si existe HC
		$llRetorno = $this->fExisteHC();
 		if($llRetorno){
			if ($this->bReqAval) {
				$this->nConCon = Consecutivos::fCalcularConsecutivoEstudiante($this->aIngreso['nIngreso']);
				if($this->nConCon>0){
					$this->organizarDatosHC($taDatos, 'HISINT');
					$this->guardarDatosAVAL($taDatos);
				}
			}else{
				$this->cCodPro = $taDatos['cCodCup'];
				$this->nConEvo = $taDatos['nConEvol'];

				// Calcular consecutivo de consulta
				$this->nConCon = Consecutivos::fCalcularConsecutivoConsulta($this->aIngreso, $this->cPrgCre);

				$llCondicionCE = $this->aIngreso['cCodVia']=='02';		// condicion para las HC de consulta externa
				if($llCondicionCE){
					//INSERTA EL REGISTRO EN LA TABLA RIACHC
					$lcTabla = 'RIACHC';
					$laDatos = [
						'TIDCHC'=>$this->aIngreso['cTipId'],
						'NIDCHC'=>$this->aIngreso['nNumId'],
						'NINCHC'=>$this->aIngreso['nIngreso'],
						'RMECHC'=>$this->cRegMed,
						'CCUCHC'=>$this->cCodPro,
						'FCOCHC'=>$this->cFecCre,
						'CCOCHC'=>$this->nConCon,
						'USRCHC'=>$this->cUsuCre,
						'PGMCHC'=>$this->cPrgCre,
						'FECCHC'=>$this->cFecCre,
						'HORCHC'=>$this->cHorCre,
					];
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
				}

				// Calcular consecutivo de cita
				$this->cCodPro = $taDatos['cCodCup'];
				$taDatos['nConCita']=$taDatos['nConCita']??0;
				if($this->ParaAvalar && ($this->aIngreso['cCodVia']=='01' || $this->aIngreso['cCodVia']=='04')){
					$laCondiciones = ['NINORD'=>$this->aIngreso['nIngreso'], 'COAORD'=>'890702', 'ESTORD'=>8];
					$laTempHC = $this->oDb
						->select('CCIORD')
						->from('RIAORD')
						->where($laCondiciones)
						->get('array');
					$this->nConCit = $laTempHC['CCIORD']??$this->nConCit;
				}else{
					$this->nConCit = empty($taDatos['nConCita'])?(Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cPrgCre)):$taDatos['nConCita'];
				}

				if($this->nConCon>0 && $this->nConCit>0){
					$this->obtenerConducta($taDatos);
					$this->organizarDatosHC($taDatos, 'RIAHIS');
					$this->guardarDatosHC($taDatos);

					// retorna datos para consultar la historia clínica
					$this->aError['dataHC'] = [
						'nIngreso'		=> $taDatos['Ingreso'],
						'cTipDocPac'	=> $this->aIngreso['cTipId'],
						'nNumDocPac'	=> $this->aIngreso['nNumId'],
						'cRegMedico'	=> $this->cRegMed,
						'cTipoDocum'	=> '2000',
						'cTipoProgr'	=> 'HCPPAL',
						'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
						'nConsecCita'	=> $this->aIngreso['cCodVia']=='02'? $this->nConCit: '',
						'nConsecCons'	=> $this->nConCon,
						'nConsecEvol'	=> $this->cConEvo,
						'nConsecDoc'	=> $this->nConCon,
						'cCUP'			=> $this->aIngreso['cCodVia']=='02'? $taDatos['cCodCup']: '',
						'cCodVia'		=> $this->aIngreso['cCodVia'],
						'cSecHab'		=> $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
					];
					if($taDatos['Nihss']['TotalN'] !== ""){
						$this->aError['dataNihss'] = [
							'nIngreso'		=> $taDatos['Ingreso'],
							'cTipDocPac'	=> $this->aIngreso['cTipId'],
							'nNumDocPac'	=> $this->aIngreso['nNumId'],
							'cRegMedico'	=> $this->cRegMed,
							'cTipoDocum'	=> '3900',
							'cTipoProgr'	=> 'ESCNIHSS',
							'tFechaHora'	=> $this->aError['dataHC']['tFechaHora'],
							'nConsecCita'	=> '',
							'nConsecCons'	=> '',
							'nConsecEvol'	=> '',
							'nConsecDoc' 	=> $this->cTipoNihss.'-'.$this->nConCon,
							'cCodVia'		=> $this->aIngreso['cCodVia'],
							'cSecHab'		=> '',
						];
					}
					if (!empty($this->aDatOrdenAmb)) {
						$this->aError['dataOA'] = $this->aDatOrdenAmb['dataOA'];
					}
				}
				if (!empty($this->aDatOrdenAmb)) {
					$this->aError['dataOA'] = $this->aDatOrdenAmb['dataOA'];
				}
			}
		} else {
			$this->aError = [
				'Mensaje'=>"El paciente ya tiene historia clínica para el ingreso {$taDatos['Ingreso']}",
				'Objeto'=>'selTipoCausa',
				'Valido'=>false,
			];
		}
		return $this->aError;
	}

	function IniciaDatosIngreso($tnIngreso=0)
	{
		$this->aIngreso = $this->oHcIng->datosIngreso($tnIngreso);
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'HCPPALWEB';
		$this->cEspecialidad  = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
		$this->cRegMed  = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():'');
	}

	function fExisteHC()
	{
		$this->aError = $this->oHcIng->validaExisteHC($this->aIngreso['nIngreso'], $this->aIngreso['cCodVia']) + ['Objeto'=>'selTipoCausa',];
		return $this->aError['Valido'];
	}

	function obtenerConducta($taDatosCond=[])
	{
		if (!empty($taDatosCond['Planmanejo']['conductaSeguir'])){
			$this->cConductaSeguir = $taDatosCond['Planmanejo']['conductaSeguir'];
			$loObjHC = new ParametrosConsulta();
			$loObjHC->ObtenerConductaSeguir($this->aIngreso['nIngreso'],$this->aIngreso['cCodVia'],$this->aIngreso['cSeccion'],'','');
			$this->cDescripcionConducta = $loObjHC->tipoConductaSeguir($taDatosCond['Planmanejo']['conductaSeguir'])['desc'];
		}

		if (!empty($taDatosCond['Planmanejo']['estadoSalidaPlan'])){
			$this->cEstadoSalida = substr($taDatosCond['Planmanejo']['estadoSalidaPlan'], 1, 2);
		}

	}

	function organizarDatosHC($taDatosHC=[], $tcTabla='RIAHIS')
	{
		$cDiagnosticoPrincipal=trim($this->obtenerCiePrincipal($taDatosHC['Diagnostico']));
		$this->cPrincipalDiagnostico=$cDiagnosticoPrincipal;
		$this->cTipoDiagnostico=trim($this->obtenerTipoDiagnosticoPrincipal($taDatosHC['Diagnostico']));

		$this->organizarDatosIng($taDatosHC['Auditoria'], $tcTabla);
		// Adicionar texto predeterminado por pandemia COVID
		$this->OrganizarTextoAdicional($taDatosHC['ctxtPandemia'], $tcTabla);
		$this->organizarMotivo($taDatosHC['MotivoC'], $tcTabla);
		$this->organizarAntecedentes($taDatosHC['Antecedentes'], $tcTabla);
		$this->organizarRevision($taDatosHC['Revision'], $tcTabla);
		$this->organizarExamen($taDatosHC['Examen'], $tcTabla);
		$this->organizarPlanmanejo($taDatosHC['Planmanejo'], $tcTabla);
		$this->organizarDiagnostico($taDatosHC['Diagnostico'], $tcTabla);

		if (!$this->bReqAval) {
			if (isset($taDatosHC['escalaHasbled'])) {
				$this->organizarEscalaHasbled($taDatosHC['escalaHasbled']);
			}

			if (isset($taDatosHC['escalaChadsvas'])) {
				$this->organizarEscalaChadsvas($taDatosHC['escalaChadsvas']);
			}

			if (isset($taDatosHC['escalaCrusade'])) {
				$this->organizarEscalaCrusade($taDatosHC['escalaCrusade']);
			}
		}

		if (isset($taDatosHC['Finalidad']))		{ $this->organizarFinalidad($taDatosHC['Finalidad'], $tcTabla); }
		if (isset($taDatosHC['InterpretaExam'])){ $this->organizarInterpretaExam($taDatosHC['InterpretaExam'],  $tcTabla); }
		if (isset($taDatosHC['Actividadfisica'])){ $this->organizarActividadFisica($taDatosHC['Actividadfisica'],  $tcTabla); }
	}

	function organizarActividadFisica($taDatos=[], $tcTabla='RIAHIS')
	{
		$lcDescrip=isset($taDatos['Respuesta']) ? $taDatos['Respuesta'] : '';
		$lnIndice=10;
		$lnSubInd=15;
		$lnCodigo=25;
		$lnLinea=1;
		if (!empty($lcDescrip)){
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}
	}

	function organizarDatosIng($taDatos=[], $tcTabla='RIAHIS')
	{
		//Guarda via de ingreso con la que se realiza la HC
		$lcDescrip = $this->aIngreso['cCodVia'] . ' - ' . $this->aIngreso['cDesVia'];
		$lnIndice = 2;
		$lnSubInd = 1;
		$lnCodigo = 0;
		$lnLinea = 0;
		$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);

		//Información de fecha y hora que inica la atención de la HISTORIA CLINICA
		$this->nFecAud = $taDatos['cFechaAud'];
		$this->nHorAud = $taDatos['cHoraAud'];

		$lcDescrip = 'REGISTRO PARA AUDITORIA';
		$lnIndice = 3;
		$lnSubInd = 1;
		$lnCodigo = 0;
		$lnLinea = 0;
		$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
	}

	function organizarMotivo($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnIndice = 5;
		$lnSubInd = intval($taDatos['Causa']);
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnLinea = 1;

		//	Insertar Motivo de consulta
		if(!empty($taDatos['Motivo'])){
			$lnCodigo = 1;
			$lcDescrip = $taDatos['Motivo'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// Insertar evento que origino la atencion
		if(!empty($taDatos['Evento'])){
			$lnCodigo = 2;
			$lcDescrip = $taDatos['Evento'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// Insertar relación de recibido
		if(!empty($taDatos['Relacion'])){
			$lnCodigo = 3;
			$lcDescrip = $taDatos['Relacion'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// DOLOR TORACICO
		$loObjHC = new ParametrosConsulta();
		$loObjHC->getDolor_Toracico();
		$laResultado = $loObjHC->DolorToracico();
		$llDolor = false;
		$lnCodigo = 2;

		// Guarda la información para Caracteristicas - Irradiación - Sintomas - Localización
		for ($lnNivel=1; $lnNivel<=4; $lnNivel++){
			$lnNum = ($lnNivel==1?10:($lnNivel==2?8:($lnNivel==3?7:5)));
			for ($lnInd=1; $lnInd<=$lnNum; $lnInd++){
				$lcElemento = str_pad((strval($lnNivel)),2,'0',STR_PAD_LEFT).str_pad((strval($lnInd)),2,'0',STR_PAD_LEFT);
				$taDatos[$lcElemento]=$taDatos[$lcElemento]??'';
				if(!empty($taDatos[$lcElemento])){
					$llDolor = true;
					$lnLinea = intval( $laResultado[$lcElemento]['LINEA']);
					$lcDescrip = $laResultado[$lcElemento]['DESCRIP'];
					$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				}
			}
		}

		if($llDolor){
			$lnLinea = 1000;
			$lcDescrip = 'Dolor Toraxico';
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// DURACION - SEGUNDOS
		if(!empty($taDatos['Dsegundos'])){
			$lnValor = number_format($taDatos['Dsegundos'], 2, '.', '');
			$lcDescrip = 'DURACION / SEGUNDOS  . . . . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30201;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// DURACION - MINUTOS
		if(!empty($taDatos['Dminutos'])){
			$lnValor = number_format($taDatos['Dminutos'], 2, '.', '');
			$lcDescrip = 'DURACION / MINUTOS . . . . . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30202;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// DURACION - HORAS
		if(!empty($taDatos['Dhoras'])){
			$lnValor = number_format($taDatos['Dhoras'], 2, '.', '');
			$lcDescrip = 'DURACION / HORAS . . . . . . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30203;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// DURACION - DIAS
		if(!empty($taDatos['Ddias'])){
			$lnValor = number_format($taDatos['Ddias'], 2, '.', '');
			$lcDescrip = 'DURACION / DIAS  . . . . . . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30204;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// INTENSIDAD
		if(!empty($taDatos['Intensidad'])){
			$lnValor = number_format($taDatos['Intensidad'], 2, '.', '');
			$lcDescrip = 'INTENSIDAD / ESCALA 1/10 . . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30301;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - SEGUNDOS
		if(!empty($taDatos['Tsegundos'])){
			$lnValor = number_format($taDatos['Tsegundos'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / SEGUNDOS . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30601;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - MINUTOS
		if(!empty($taDatos['Tminutos'])){
			$lnValor = number_format($taDatos['Tminutos'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / MINUTOS. . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30602;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - HORAS
		if(!empty($taDatos['Thoras'])){
			$lnValor = number_format($taDatos['Thoras'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / HORAS. . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30603;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - DIAS
		if(!empty($taDatos['Tdias'])){
			$lnValor = number_format($taDatos['Tdias'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / DIAS . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30604;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - SEMANAS
		if(!empty($taDatos['Tsemanas'])){
			$lnValor = number_format($taDatos['Tsemanas'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / SEMANAS. . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30605;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - MESES
		if(!empty($taDatos['Tmeses'])){
			$lnValor = number_format($taDatos['Tmeses'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / MESES. . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30606;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// TIEMPO DE EVOLUCION - AÑOS
		if(!empty($taDatos['Tanos'])){
			$lnValor = number_format($taDatos['Tanos'], 2, '.', '');
			$lcDescrip = 'TIEMPO DE EVOLUCION / AÑOS . . . . . . : '.str_pad($lnValor,8,' ',STR_PAD_LEFT);
			$lnLinea = 30607;
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}
	}

	function organizarAntecedentes($taDatos=[], $tcTabla='RIAHIS')
	{
		$laAntecedentes = [
			 1=>'antPatologicos',
			 3=>'antTransfusionales',
			 4=>'antVacunas',
			 6=>'antQuirurgicos',
			 7=>'antTraumaticos',
			 8=>'antAlergicos',
			10=>'antToxicos',
			12=>'antGineco',
			14=>'antFamiliares',
			18=>'antHospitalarios',
			20=>'antDiscapacidad',
			21=>'edadgestacional',
			22=>'nroprenatales',
		];
		$lnIndice = 10;
		$lnSubInd = 15;
		$lnLenRiaHis = ($tcTabla=='RIAHIS')?70:220;
		$lnLenAntPac = 220;

		foreach($laAntecedentes as $lnCodigo=>$lcAntecedente){
			$lnLinea = 1;
			if($lnCodigo==4 && isset($taDatos['antVacunaCovid'])){
				$lcDescrip = $lcDescriV = trim($taDatos[$lcAntecedente]);
				if(!empty($lcDescrip) && !$this->bReqAval){
					$this->InsertarDescripcion('ANTPAC', $lnLenAntPac, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
					$this->InsertarDescripcion('ANTPAD', $lnLenAntPac, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				}else{
					if($this->bReqAval){
						$this->InsertarDescripcion($tcTabla, $lnLenAntPac, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
					}

				}
				if(count($taDatos['antVacunaCovid'])>0){
					$lnSubIndV=4;
					$lnCodigoV=$taDatos['antVacunaCovid'][0]['vacunac']; // 24
					if($this->bReqAval){$lcDescriV='';}else{$lcDescriV=chr(13);}
					$lcDescriV.=$taDatos['antVacunaCovid'][0]['vacuna'].':';
					$lcSep='';
					foreach($taDatos['antVacunaCovid'] as $laVacCov){
						$lnLinea++;
						$lcDscrVCv=($laVacCov['aplicac']=='SI'? $lcSep.'- Lab. '.$laVacCov['labrt'].' - '.$laVacCov['dosis'].(empty($laVacCov['fecha'])? '': ' - '.$laVacCov['fecha']): $laVacCov['aplica']);
						$lcDescriV.=$lcDscrVCv;
						unset($laVacCov['vacuna']); unset($laVacCov['vacunac']); unset($laVacCov['id']);
						$lcDscCodi=json_encode($laVacCov);
						if (!$this->bReqAval){
							$this->InsertarDescripcion('ANTPAC', $lnLenAntPac, $lcDscrVCv, $lnIndice, $lnSubIndV, $lnCodigoV, $lnLinea, 0, $lcDscCodi);
							$this->InsertarDescripcion('ANTPAD', $lnLenAntPac, $lcDscrVCv, $lnIndice, $lnSubIndV, $lnCodigoV, $lnLinea, 0, $lcDscCodi);
						}else{
							$lnCodigo = $lnCodigoV;
							$this->InsertarDescripcion($tcTabla, $lnLenAntPac, $lcDescriV, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, 0, '', 0, $lcDscCodi);
						}
						$lcSep=' ';
					}
					if(!empty($taDatos['antVacunas'])){
						$lnLinea = ceil(mb_strlen($taDatos['antVacunas'])/$lnLenRiaHis);
					}
				}
				if(!empty($lcDescriV) && !$this->bReqAval){
					$this->InsertarDescripcion($tcTabla, $lnLenRiaHis, $lcDescriV, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				}
			}else{
				$lcDescrip = trim($taDatos[$lcAntecedente]);
				if($lnCodigo==20 && $lcDescrip =='Si'){
					for ($lnInd = 1; $lnInd <= 6; $lnInd++) {
						$lcCodigo = str_pad($lnInd, 2, "0", STR_PAD_LEFT);
						if (!empty(trim($taDatos[$lcCodigo]??''))){
							$lcDescrip .= '¤' . $lcCodigo;
						}
					}
				}

				if(!empty($lcDescrip)){
					$this->InsertarDescripcion($tcTabla, $lnLenRiaHis, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
					if (!$this->bReqAval){
						$this->InsertarDescripcion('ANTPAC', $lnLenAntPac, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
						$this->InsertarDescripcion('ANTPAD', $lnLenAntPac, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
					}
				}
			}
		}
	}

	function organizarRevision($taDatos=[], $tcTabla='RIAHIS')
	{
		$laRevisionSis = [
			 1=>'sisVisual',
			 2=>'sisOtorrino',
			 3=>'sisPulmonar',
			 4=>'sisCardiovascular',
			 5=>'sisGastrointestinal',
			 6=>'sisGenitourinario',
			 7=>'sisEndocrino',
			 8=>'sisHematologico',
			 9=>'sisDermatologico',
			10=>'sisOseo',
			11=>'sisNervioso',
			12=>'sisSiquico',
		];
		$lnIndice = 10;
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnLinea = 1;
		$lnCodigo = 0;

		foreach($laRevisionSis  as $lnKey=>$lcRevision){
			$lcDescrip = trim($taDatos[$lcRevision]);
			if(!empty($lcDescrip)){
				$lnSubInd = $lnKey;
				$lnLinea = 1;
				$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			}
		}
	}

	function organizarExamen($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnTAS = empty($taDatos['tas'])?($taDatos['chk_TAS']??'0'):$taDatos['tas'];
		$lnTAD = empty($taDatos['tad'])?($taDatos['chk_TAD']??'0'):$taDatos['tad'];
		$lnFC = empty($taDatos['fc'])?($taDatos['chk_FC']??'0'):$taDatos['fc'];
		$lnFR = empty($taDatos['fr'])?($taDatos['chk_FR']??'0'):$taDatos['fr'];
		$lnGlasgow = (empty(trim($taDatos['escalaG']))?0:intval($taDatos['escalaG']));
		$lnTempR = (empty(trim($taDatos['tempR']))?0:floatval($taDatos['tempR']));
		$lnNivelC =  (empty(trim($taDatos['nivelC']))?9:intval($taDatos['nivelC']));
		$lnMasaC = round(intval($taDatos['masaC']??0),0);

		if ($this->bReqAval){
			$this->aEXFINT[]=[
				'TIDEFI'=>$this->aIngreso['cTipId'],
				'NIDEFI'=>$this->aIngreso['nNumId'],
				'NIGEFI'=>$this->aIngreso['nIngreso'],
				'REGEFI'=>$this->cRegMed,
				'CDPEFI'=>$this->cCodPro,
				'CCEEFI'=>$this->nConEvo,
				'FESEFI'=>$this->cFecCre,
				'CNSEFI'=>$this->nConCon,
				'NCNEFI'=>$lnNivelC,
				'GLCEFI'=>$lnGlasgow,
				'SSDEFI'=>$lnTAS,
				'DSDEFI'=>$lnTAD,
				'FRCEFI'=>$lnFC,
				'FRREFI'=>$lnFR,
				'TPREFI'=>round(floatval($taDatos['temp']??0),1),
				'TPTEFI'=>$lnTempR,
				'TLLEFI'=>intval($taDatos['talla']??0),
				'PSOEFI'=>round(floatval($taDatos['peso']??0),2),
				'MASEFI'=>$lnMasaC,
				'SUPEFI'=>intval($taDatos['supC']??0),
				'FILEFI'=>intval($taDatos['so2']??0),
				'USREFI'=>$this->cUsuCre,
				'PGMEFI'=>$this->cPrgCre,
				'FECEFI'=>$this->cFecCre,
				'HOREFI'=>$this->cHorCre,

			];
		}else{
			$this->aRIAEXF[]=[
				'TIDEXF'=>$this->aIngreso['cTipId'],
				'NIDEXF'=>$this->aIngreso['nNumId'],
				'NIGEXF'=>$this->aIngreso['nIngreso'],
				'REGEXF'=>$this->cRegMed,
				'CDPEXF'=>$this->cCodPro,
				'FESEXF'=>$this->cFecCre,
				'CNSEXF'=>$this->nConCon,
				'NCNEXF'=>$lnNivelC,
				'GLCEXF'=>$lnGlasgow,
				'SSDEXF'=>$lnTAS,
				'DSDEXF'=>$lnTAD,
				'FRCEXF'=>$lnFC,
				'FRREXF'=>$lnFR,
				'TPREXF'=>round(floatval($taDatos['temp']??0),1),
				'TPTEXF'=>$lnTempR,
				'TLLEXF'=>intval($taDatos['talla']??0),
				'PSOEXF'=>round(floatval($taDatos['peso']??0),2),
				'MASEXF'=>$lnMasaC,
				'SUPEXF'=>intval($taDatos['supC']??0),
				'USREXF'=>$this->cUsuCre,
				'PGMEXF'=>$this->cPrgCre,
				'FECEXF'=>$this->cFecCre,
				'HOREXF'=>$this->cHorCre,
				'SATEXF'=>intval($taDatos['so2']??0),
			];
		}

		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnIndice = 20;
		$lnSubInd = 0;
		$lnCodigo = 1;
		$lnLinea = 1;

		// Estado General
		if(!empty(trim($taDatos['estado']))){
			$lcDescrip = trim($taDatos['estado']);
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		// Examen Físico General
		// CABEZA - CUELLO *!* ORGANOS DE LOS SENTIDOS *!* TORAX CARDIO PULMONAR *!* ABDOMEN *!* GENITO - URINARIO *!* EXTREMIDADES *!*
		$laExamenFisico= [
			 7=>'exAbdomen',
			 8=>'exGenito',
			 9=>'exExtremidades',
			11=>'exCabeza',
			12=>'exTorax',
			13=>'exOrganos',
		];
		$lnCodigo = 2;
		foreach($laExamenFisico as $lnSubInd=>$lcExamen){
			$lcDescrip = trim($taDatos[$lcExamen]);
			if(!empty($lcDescrip)){
				$lcDescrip = '0'.$lcDescrip;
				$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			}
		}

		// Examen Físico Neurológico
		$laExamenFisico= [
			1=>'exMotor',
			2=>'exSensitivo',
			4=>'exMental',
			5=>'exCraneales',
			6=>'exReflejos',
			7=>'exMeningeos',
			8=>'exNeurovascular',
		];
		$lnSubInd = 10;
		foreach($laExamenFisico as $lnCodigo=>$lcExamen){
			$lcDescrip = trim($taDatos[$lcExamen]);
			if(!empty($lcDescrip)){
				$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			}
		}
	}

	function organizarDiagnostico($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnIndice = 25;
		$lnSubInd = 0;
		$lnSubHis = 0;
		$lnConHis = 0;
		$lnLinea = 1;

		if(is_array($taDatos)){
			if(count($taDatos)>0){
				foreach ($taDatos as $diagnostico){
					$lcCodigoDiagnostico = $diagnostico['CODIGO'];
					$lnSubInd = intval($diagnostico['CODTIPO']);
					$lnCodigo = intval($diagnostico['CODCLASE']);
					$lcTratamientoDiagnostico = $diagnostico['CODTRATA'];
					$lcDescrip = trim($diagnostico['OBSER']);

					if (!empty($lcDescrip)){
						$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, $lnSubHis, $lcCodigoDiagnostico, $lnConHis, $lcTratamientoDiagnostico);
					}else{
						$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, $lnSubHis, $lcCodigoDiagnostico, $lnConHis, $lcTratamientoDiagnostico);
					}

					$lnIndiceDx = 0;
					if($diagnostico['CODTIPO']=='1'){
						$this->aDxXML[0]['CODIGO'] = $diagnostico['CODIGO'];
						$this->aDxXML[0]['DESCRIP'] = $diagnostico['DESCRIP'];
					}else{
						$lnIndiceDx++;
						$this->aDxXML[$lnIndiceDx]['CODIGO'] = $diagnostico['CODIGO'];
						$this->aDxXML[$lnIndiceDx]['DESCRIP'] = $diagnostico['DESCRIP'];
					}
					$lnLinea++;
				}
			}
		}
	}

	function organizarPlanmanejo($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnIndice = 30;
		$lnSubInd = 0;
		$lnLinea = 1;

		//	Analisis y plan de manejo
		if(!empty($taDatos['analisisPlan'])){
			$lnCodigo = 0;
			$lcDescrip = $taDatos['analisisPlan'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Doctor informa
		if(!empty($taDatos['doctorInforma'])){
			$lnIndice = 40;
			$lnCodigo = 0;
			$lcDescrip = $taDatos['doctorInforma'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Tuvo electrocardiograma
		if(!empty($taDatos['TuvoElectrocardiograma'])){
			$lnIndice = 50;
			$lnCodigo = 0;
			$lcDescrip = $taDatos['TuvoElectrocardiograma'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			
			if ($this->cCobroElectrocardiograma=='1'){
				if (trim($taDatos['tuvoElectro']) == 'Si' && !$this->bReqAval){
					if (in_array($this->aIngreso['cCodVia'], $this->aViasElectro)){
						$lnIndice = 70;
						$lnCodigo = 0;
						$lnLineacups = 1;
						$lnConCita = Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cPrgCre);
						$this->InsertarRegistro('RIAORD', '', $lnConCita, 0, 0, 0);
						$this->InsertarRegistro('RIADET', '', $lnConCita, 0, 0, 0);
						$this->InsertarDescripcion('RIAHISL0', $lnLongitud, $lcDescrip, $lnIndice, $lnConCita, $lnCodigo, $lnLineacups);
						
						$lnLineacups = 101;
						$lcTexto = str_pad((strval($this->cRegMed)), 13,' ',STR_PAD_RIGHT)." "
								  .str_pad((strval($this->cPrincipalDiagnostico)), 4,'0',STR_PAD_LEFT)." ".'0000'." "
								  .str_pad((strval($this->cEspecialidad)), 3,'0',STR_PAD_LEFT)." "
								  .str_pad((strval($this->aCupElectro['FINALIDADHOMOLOGO'])), 4,' ',STR_PAD_LEFT)." "
								  .str_pad((strval($this->cTipoDiagnostico)), 4,' ',STR_PAD_LEFT);
						$this->InsertarRegistro('RIAHISL0', $lcTexto, $lnIndice, $lnConCita, $lnCodigo, $lnLineacups);
						$this->cobrarProcedimiento($lnConCita);
					}
				}	
			}	
		}

		//	Conducta a seguir
		if(!empty($taDatos['conductaSeguir'])){
			$lnIndice = 54;
			$lnCodigo = intval($taDatos['conductaSeguir']);
			$lcDescrip = $this->cDescripcionConducta;
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Estado salida
		if(!empty($taDatos['estadoSalidaPlan'])){
			$lnIndice = 55;
			$loObjHC = new ParametrosConsulta();
			$loObjHC->ObtenerEstadoSalida();
			$lnCodigo = intval($taDatos['estadoSalidaPlan']);
			$lcDescrip = $loObjHC->estadoSalida($taDatos['estadoSalidaPlan'])['desc'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Registro médico/especialidad
		if(!empty($this->cRegMed)){
			$lnIndice = 85;
			$lnCodigo = 0;
			$lcDescrip = trim($this->cRegMed).'          Especialidad: '.trim($this->cEspecialidad);
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Registro médico/especialidad
		if(!empty($this->cRegMed)){
			$lnIndice = 86;
			$lnCodigo = 0;
			$lcDescrip = trim($this->cRegMed).'          Especialidad: '.trim($this->cEspecialidad);
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Reingreso misma causa
		if(!empty($taDatos['Reingreso'])){
			$lnIndice = 88;
			$lnCodigo = 0;
			$lcDescrip = $taDatos['Reingreso'];
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}

		//	Modalidad grupo servicio o Atención derivada domiciliaria
		$lcAtencionDomiciliaria=isset($taDatos['AtencionDomiciliaria'])?$taDatos['AtencionDomiciliaria']:'';

		if(!empty($taDatos['ModalidadGrupo']) || !empty($lcAtencionDomiciliaria)){
			$lnIndice = 90;
			$lnCodigo = 0;
			$lcDescrip = $taDatos['ModalidadGrupo'].'~'.$lcAtencionDomiciliaria;
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}
	}

	function obtenerCiePrincipal($taDatos=[])
	{
		$lcCodigoPrincipal = '';
		foreach ($taDatos as $obtenerPrincipal){
			$lcTipoValidar = trim($obtenerPrincipal['CODTIPO']);

			if ($lcTipoValidar=='1'){
				$lcCodigoPrincipal = strtoupper(trim($obtenerPrincipal['CODIGO']));
				break;
			}
		}
		return $lcCodigoPrincipal;
	}
	
	function obtenerTipoDiagnosticoPrincipal($taDatos=[])
	{
		$lcCodigoTipoDiagnostico='';
		foreach ($taDatos as $obtenerPrincipal){
			$lcTipoValidar = trim($obtenerPrincipal['CODTIPO']);

			if ($lcTipoValidar=='1'){
				$lcCodigoTipoDiagnostico = trim($obtenerPrincipal['CODCLASE']);
				break;
			}
		}
		return $lcCodigoTipoDiagnostico;
	}

	private function organizarEscalaHasbled($taDatos=[])
	{
		$lnIndice = 35;
		$lnSubInd = 1;
		$lnCodigo = 0;
		$lnLinea = 1;
		$lcTexto = 'Puntaje HASBLED: ' . str_repeat(' ', 3) . $taDatos[0]['lnPuntaje'] . str_repeat(' ',4) . substr($taDatos[0]['lcInterpretacion'],0,45);

		$this->InsertarRegistro('RIAHIS', $lcTexto, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
	}

	private function organizarEscalaChadsvas($taDatos=[])
	{
		$lnIndice = 35;
		$lnSubInd = 2;
		$lnCodigo = 0;
		$lnLinea = 1;
		$lcTexto = 'Puntaje CHA2DS2VAS: ' . $taDatos[0]['lnPuntaje'] . str_repeat(' ', 4) . substr($taDatos[0]['lcInterpretacion'],0,45);
		$this->InsertarRegistro('RIAHIS', $lcTexto, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
	}

	private function organizarEscalaCrusade($taDatos=[])
	{
		$lnIndice = 35;
		$lnSubInd = 3;
		$lnCodigo = 0;
		$lnLinea = 1;
		$lcTexto = 'Puntaje CRUSADE: ' . str_repeat(' ', 3) . $taDatos['lnPuntaje'] . str_repeat(' ', 3) . substr($taDatos['lcInterpretacion'],0,45);
		$this->InsertarRegistro('RIAHIS', $lcTexto, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
	}

	private function organizarFinalidad($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lcDescrip = !empty($taDatos['finalidadObs'])?$taDatos['finalidadObs']:'';
		$lnIndice = 4;
		$lnSubInd = intval($taDatos['finalidad']);
		$lnCodigo = 0;
		$lnLinea = 1;

		if (!empty($lcDescrip)){
			$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}else{
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}
	}

	private function organizarInterpretaExam($taDatos=[], $tcTabla='RIAHIS')
	{
		$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
		$lnIndice = 21;
		$lnCodigo = $lnSubInd = 0;

		foreach ($taDatos as $laDato) {
			$lnSubInd++;
			$lnLinea = 1;
			$lcDescrip = $laDato['codresult'] . '¥' . str_pad($laDato['cup'],6,' ',STR_PAD_LEFT) . '¥' . mb_substr($laDato['procedimiento'], 0, 61);
			$this->InsertarRegistro($tcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			if (!empty($laDato['interpreta'])) {
				$lnLinea = 2;
				$lcDescrip = $laDato['interpreta'];
				$this->InsertarDescripcion($tcTabla, $lnLongitud, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
			}
		}
	}

	private function OrganizarTextoAdicional($tcTexto='', $tcTabla='RIAHIS')
	{
		if(!empty(trim($tcTexto))){
			$lnLongitud = ($tcTabla=='RIAHIS')?70:220;
			$lnIndice = 6;
			$lnSubInd = 1;
			$lnLinea = 1;
			$lnCodigo = 0;
			$this->InsertarDescripcion($tcTabla, $lnLongitud, trim($tcTexto), $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
		}
	}

	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnIndice=0, $tnSubInd=0, $tnCodigo=0, $tnLinea=1, $tnSubHis=0, $tcSubOrg='', $tnConHis=0, $tcFille3='')
	{
		$laChar = AplicacionFunciones::mb_str_split(trim($tcTexto),$tnLongitud);
		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnIndice, $tnSubInd, $tnCodigo, $tnLinea, $tnSubHis, $tcSubOrg, $tnConHis, $tcFille3);
					$tnLinea++;
				}
			}
		}
	}

	function InsertarRegistro($tcTabla='', $tcDescrip='', $tnIndice=0, $tnSubInd=0, $tnCodigo=0, $tnConsec=0, $tnSubHis=0, $tcSuborg='', $tnConHis=0, $tcFille3='')
	{
		switch (true){

			case $tcTabla=='RIAHIS' :
				$lnFechaAud = 0;
				$lnHoraAud = 0;
				if($tnIndice==3 && $tnSubInd==1){
					$lnFechaAud = $this->cFecCre;
					$lnHoraAud = $this->cHorCre;
					$this->cFecCre = $this->nFecAud;
					$this->cHorCre = $this->nHorAud;
				}

				$this->aRIAHIS[]=[
					'NROING'=>$this->aIngreso['nIngreso'],
					'CONCON'=>$this->nConCon,
					'INDICE'=>$tnIndice,
					'SUBIND'=>$tnSubInd,
					'CODIGO'=>$tnCodigo,
					'SUBHIS'=>$tnSubHis,
					'SUBORG'=>$tcSuborg,
					'CONSEC'=>$tnConsec,
					'CONHIS'=>$tnConHis,
					'DESCRI'=>$tcDescrip,
					'NITENT'=>$this->aIngreso['nEntidad'],
					'TIDHIS'=>$this->aIngreso['cTipId'],
					'NIDHIS'=>$this->aIngreso['nNumId'],
					'FILLE3'=>$tcFille3,
					'USRHIS'=>$this->cUsuCre,
					'PGMHIS'=>$this->cPrgCre,
					'FECHIS'=>$this->cFecCre,
					'HORHIS'=>$this->cHorCre,
					'FMOHIS'=>$lnFechaAud,
					'HMOHIS'=>$lnHoraAud,
				];
				if($tnIndice==3 && $tnSubInd==1){
					 $this->cFecCre = $lnFechaAud;
					$this->cHorCre = $lnHoraAud;
				}
				break;

			case $tcTabla=='ANTPAC' :

				$this->aANTPAC[]=[
					'TIDANT'=>$this->aIngreso['cTipId'],
					'NIDANT'=>$this->aIngreso['nNumId'],
					'NINANT'=>$this->aIngreso['nIngreso'],
					'CODANT'=>$tnSubInd,
					'SANANT'=>$tnCodigo,
					'INDANT'=>$tnIndice,
					'LINANT'=>$tnConsec,
					'DESANT'=>$tcDescrip,
					'OP5ANT'=>$tcSuborg,
					'USRANT'=>$this->cUsuCre,
					'PGMANT'=>$this->cPrgCre,
					'FECANT'=>$this->cFecCre,
					'HORANT'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ANTPAD' :

				$this->aANTPAD[]=[
					'TIDAND'=>$this->aIngreso['cTipId'],
					'NIDAND'=>$this->aIngreso['nNumId'],
					'NINAND'=>$this->aIngreso['nIngreso'],
					'FDCAND'=>$this->cFecCre,
					'HDCAND'=>$this->cHorCre,
					'CODAND'=>$tnSubInd,
					'SANAND'=>$tnCodigo,
					'INDAND'=>$tnIndice,
					'LINAND'=>$tnConsec,
					'DESAND'=>$tcDescrip,
					'OP5AND'=>$tcSuborg,
					'OP7AND'=>$this->nConCon,
					'USRAND'=>$this->cUsuCre,
					'PGMAND'=>$this->cPrgCre,
					'FECAND'=>$this->cFecCre,
					'HORAND'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='HISINT' :
				$lcTipo = 'HC';
				$lcOp5hin = ($tnIndice==10 && $tnSubInd==15 && $tnCodigo==24?$tcFille3:'');
				$lcTipoTratamiento = (empty(trim($lcOp5hin))?$tcFille3:'');

				$this->aHISINT[]=[
					'INGHIN'=>$this->aIngreso['nIngreso'],
					'TIPHIN'=>$lcTipo,
					'CCOHIN'=>$this->nConCon,
					'INDHIN'=>$tnIndice,
					'SUBHIN'=>$tnSubInd,
					'CODHIN'=>$tnCodigo,
					'CLNHIN'=>$tnConsec,
					'DESHIN'=>$tcDescrip,
					'OP2HIN'=>$tcSuborg,
					'OP5HIN'=>$lcOp5hin,
					'OP6HIN'=>$lcTipoTratamiento,
					'USRHIN'=>$this->cUsuCre,
					'PGMHIN'=>$this->cPrgCre,
					'FECHIN'=>$this->cFecCre,
					'HORHIN'=>$this->cHorCre,
				];
				break;
				
			case $tcTabla=='RIAORD' :
				$this->aRIAORD[]=[
					'TIDORD'=>$this->aIngreso['cTipId'],
					'NIDORD'=>$this->aIngreso['nNumId'],
					'NINORD'=>$this->aIngreso['nIngreso'],
					'CCIORD'=>$tnIndice,
					'CODORD'=>$this->aCupElectro['ESPECIALIDAD'],
					'COAORD'=>$this->aCupElectro['CUPS'],
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
					'CUPDET'=>$this->aCupElectro['CUPS'],
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
					'CONCON'=>$tnSubInd,
					'INDICE'=>$tnIndice,
					'SUBORG'=>$this->aCupElectro['CUPS'],
					'CONSEC'=>$tnConsec,
					'CONHIS'=>$tnConHis,
					'DESCRI'=>$tcDescrip,
					'NITENT'=>$this->aIngreso['nEntidad'],
					'TIDHIS'=>$this->aIngreso['cTipId'],
					'NIDHIS'=>$this->aIngreso['nNumId'],
					'FILLE3'=>$tcFille3,
					'USRHIS'=>$this->cUsuCre,
					'PGMHIS'=>$this->cPrgCre,
					'FECHIS'=>$this->cFecCre,
					'HORHIS'=>$this->cHorCre,
				];
				break;
		}
	}

	private function guardarDatosHC($taDatosHC=[])
	{
		// Actualizar Datos en tabla TRIAGU
		$lcTabla = 'TRIAGU';
		$laDatos = [
			'OP6TRI'=>'',
			'ESTTRI'=>31,
			'UMOTRI'=>$this->cUsuCre,
			'PMOTRI'=>$this->cPrgCre,
			'FMOTRI'=>$this->cFecCre,
			'HMOTRI'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['NIGTRI'=>$this->aIngreso['nIngreso'],])->actualizar($laDatos);

		// Insertar registros a la tabla de AS400 RIAHIS
		$lcTabla = 'RIAHIS';
		foreach($this->aRIAHIS  as $laRIAHIS){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAHIS);
		}

		// Insertar registros a la tabla de AS400 ANTPAC
		$lcTabla = 'ANTPAC';
		$lcCodAnt=0;
		foreach($this->aANTPAC  as $laANTPAC){
			if(in_array($laANTPAC['CODANT'],[4,15]) && $lcCodAnt!==$laANTPAC['SANANT']){
				// Eliminar el ultimo registro en la ANTPAC
				$laWhere = [
					'TIDANT'=>$this->aIngreso['cTipId'],
					'NIDANT'=>$this->aIngreso['nNumId'],
					'CODANT'=>$laANTPAC['CODANT'],
					'SANANT'=>$laANTPAC['SANANT'],
					'INDANT'=>10,
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where($laWhere)->eliminar();
				$lcCodAnt=$laANTPAC['SANANT'];
			}
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laANTPAC);
		}

		// Insertar registros a la tabla de AS400 ANTPAD
		$lcTabla = 'ANTPAD';
		foreach($this->aANTPAD  as $laANTPAD){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laANTPAD);
		}

		// Insertar registros a la tabla de AS400 RIAEXF
		$lcTabla = 'RIAEXF';
		foreach($this->aRIAEXF  as $laRIAEXF){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAEXF);
		}

		// Guardar Datos conciliacion
		$loObjHC = new Conciliacion();
		$laResultado = $loObjHC->guardarDatosC($taDatosHC['Conciliacion'], $this->aIngreso, $this->cPrgCre, $this->nConCon);

		// Guardar Datos Nihss
		if($taDatosHC['Nihss']['TotalN']!==""){
			$this->cTipoNihss = ($this->aIngreso['cCodVia']=='01'?'HCUR':($this->aIngreso['cCodVia']=='02'?'HCCE':'HCHO'));
			$loObjHC = new Doc_NIHSS();
			$laResultado = $loObjHC->guardarDatosN($taDatosHC['Nihss'], $this->aIngreso['nIngreso'], $this->cTipoNihss, $this->nConCon, $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		//	GUARDAR DATOS DIAGNOSTICO
		$lcTipoDocVia = $this->aIngreso['cCodVia'] == '01' ? 'HU' : ($this->aIngreso['cCodVia'] == '02' ? 'HC' : 'HP');
		$loObjHC = new Diagnostico();
		$laResultado = $loObjHC->guardarDiagnostico($taDatosHC['Diagnostico'],$this->aIngreso['nIngreso'],$this->nConCon,$this->aIngreso['cTipId'],$this->aIngreso['nNumId'],$this->aIngreso['nEntidad'],$lcTipoDocVia,$this->cConductaSeguir,$this->cDescripcionConducta,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre, $this->cEstadoSalida, '');

		if(!empty($taDatosHC['escalaHasbled'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsHasbledHC($taDatosHC['escalaHasbled'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['escalaChadsvas'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsChadsvasHC($taDatosHC['escalaChadsvas'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['escalaCrusade'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsCrusadeHC($taDatosHC['escalaCrusade'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['DatosSadPersons'])){
			$loObjHC = new EscalaSadPersons();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEscSadPersonsHC($taDatosHC['DatosSadPersons'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		if(!empty($taDatosHC['Ambulatorio'])){
			$lcCiePrincipal = trim($this->obtenerCiePrincipal($taDatosHC['Diagnostico']));
			$lcCodVia = $this->aIngreso['cCodVia'] ;
			$lcPlanIngreso = $this->aIngreso['cPlan'] ;
			$loAmbulatorio = new DatosAmbulatorios();
			$this->aDatOrdenAmb = $loAmbulatorio->GuardarOrdenesAmbulatorias($taDatosHC['Ambulatorio'],$this->aIngreso,$this->nConCon,$this->nConCit,$lcCiePrincipal,$this->nConEvo,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre, $this->cRegMed);
		}

		if(!empty($taDatosHC['Actividadfisica'])){
			$this->guardarActividadFisica($taDatosHC);
		}

		if(!empty($taDatosHC['Planmanejo']['OrdenHospitalizacion']['EspecialidadOrden'])){
			$loObjHC = new OrdenHospitalizacion();
			$cDiagnosticoPrincipal = trim($this->obtenerCiePrincipal($taDatosHC['Diagnostico']));
			$laResultado = $loObjHC->guardarOrdenHospitalizacion($taDatosHC['Planmanejo']['OrdenHospitalizacion'],$this->aIngreso['nIngreso'],$this->aIngreso['nNumId'],$cDiagnosticoPrincipal,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre);
		}

		$lcTabla = 'RIAORD';
		foreach($this->aRIAORD  as $laRIAORD){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAORD);
		}
		
		$lcTabla = 'RIADET';
		foreach($this->aRIADET  as $laRIADET){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIADET);
		}
		
		$lcTabla = 'RIAHISL0';
		foreach($this->aRIAHISL0  as $laRIAHISL0){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAHISL0);
		}
		
		// Si es HC para avalar actualiza estado de encabezado
		if($this->ParaAvalar){
			$lcTabla = 'REINCA';
			$laDatos = [
				'ESTRIC'=>'VA',
				'UMORIC'=>$this->cUsuCre,
				'PMORIC'=>$this->cPrgCre,
				'FMORIC'=>$this->cFecCre,
				'HMORIC'=>$this->cHorCre,
			];

			$llResultado = $this->oDb->tabla($lcTabla)->where(['INGRIC'=>$this->aIngreso['nIngreso'],'CONRIC'=>$this->nConAval,'TIPRIC'=>'HC',])->actualizar($laDatos);
		}

		// Actualizar información dependiendo la vía de ingreso
		switch (true){
			case $this->aIngreso['cCodVia'] == '01' || $this->aIngreso['cCodVia'] == '04':
				$this->GuardarDatosUR($taDatosHC['MotivoC']['Causa']);
				break;
			case $this->aIngreso['cCodVia'] == '02' :
				$this->GuardarDatosCE();
				break;
			case $this->aIngreso['cCodVia'] == '05' || $this->aIngreso['cCodVia'] == '06' :
				$this->GuardarDatosHO();
				break;
		}

		// ACTUALIZA INGRESO - DESBLOQUEA
		$lcTabla = 'RIAINGL15';
		$laDatos = [
			'CREING'=>0,
			'UMOING'=>$this->cUsuCre,
			'PMOING'=>$this->cPrgCre,
			'FMOING'=>$this->cFecCre,
			'HMOING'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['NIGING'=>$this->aIngreso['nIngreso'],])->actualizar($laDatos);
	}

	private function guardarActividadFisica($taDatosHC=[]){
		$lnConEvo = 0;
		$aActividadFisica = [
			'Datos' => $taDatosHC['Actividadfisica'],
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
			'Tiporeg' => 'HC',
			'Indice' => 10,
			'SubIndice' => 15,
			'CodigoAntec' => 19,
			'SubCodigoAntec' => '15',
		];
		$loActividadFisica = new EscalaActividadFisica();
		$laResultado = $loActividadFisica->guardarDatosAF($aActividadFisica);
	}

	private function guardarDatosAVAL($taDatosHC=[]){

		// Insertar conciliacion
		$laDatos = $taDatosHC['Conciliacion'];
		$lcTabla = 'HISINT';
		$lcCabecera = 'CONSUME: ' . $laDatos['Consume'] . ' INFORMA: ' . $laDatos['Informa'] . ' MEDICO: ' . $this->cRegMed . ' MED. ESP: ' . $this->cEspecialidad .
					  ' INFORMANTE: ' . (empty(trim($laDatos['Informante']))?'No Registra':trim($laDatos['Informante']));

		if (!empty($lcCabecera)){
			$lnIndice = 17;
			$lnCodigo = 0;
			$lnLinea = 1;
			$lnSubInd = 1;
			$lcDato = isset($laDatos['NoConsume']) ? (empty($laDatos['NoConsume'])?'':'0'.$laDatos['NoConsume']) : '';
			$this->InsertarRegistro($lcTabla, $lcCabecera, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, 0, $lcDato);
		}

		if(count($laDatos['Medicamentos']??[])>0){

			foreach($laDatos['Medicamentos'] as $laConcilia){
				$lcNombre = substr($laConcilia['MEDICA'],0,30);
				$lcContina = (trim($laConcilia['CONTINUA'])== 'Continua'?'1 0 0 ':(trim($laConcilia['CONTINUA'])== 'Suspende'?'0 1 0 ':(trim($laConcilia['CONTINUA'])== 'Modifica'?'0 0 1 ':'')));
				$lcDescrip = str_pad(trim($lcNombre),30,' ',STR_PAD_RIGHT) . str_pad(trim($laConcilia['DOSIS']),7,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['TIPODCOD']),8,' ',STR_PAD_RIGHT) . str_pad(trim($laConcilia['VIACOD']),8,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['FRECUENCIA']),4,' ',STR_PAD_RIGHT) . str_repeat(' ',16) . $lcContina . str_repeat(' ',2) .
							trim($laDatos['Informa']) . trim($laDatos['Consume']) . str_pad(trim($laConcilia['TIPOF']),20,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['CODIGO']),11,' ',STR_PAD_RIGHT);

				$lnCodigo = 1;
				$this->InsertarRegistro($lcTabla, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);

				if(!empty(trim($laConcilia['OBSERVA']))){
					$lnCodigo = 2;
					$lcObserva = trim($laConcilia['OBSERVA']);
					$this->InsertarDescripcion($lcTabla, 220, $lcObserva, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				}
				$lnSubInd++;
			}
		}

		// Crear registro de Encabezado de aval en tabla REINCA
		$lcTabla = 'REINCA';
		$lcTipo = 'HC';
		$laDatos = [
			'INGRIC'=>$this->aIngreso['nIngreso'],
			'TIPRIC'=>$lcTipo,
			'CONRIC'=>$this->nConCon,
			'USRRIC'=>$this->cUsuCre,
			'PGMRIC'=>$this->cPrgCre,
			'FECRIC'=>$this->cFecCre,
			'HORRIC'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		// Insertar registros a la tabla de AS400 HISINT
		$lcTabla = 'HISINT';
		foreach($this->aHISINT  as $laHISINT){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laHISINT);
		}

		// Insertar registros a la tabla de AS400 $this->aEXFINT
		$lcTabla = 'EXFINT';
		foreach($this->aEXFINT as $laEXFINT){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laEXFINT);
		}

		// Inserta registro de actividad Física
		if(!empty($taDatosHC['Actividadfisica'])){
			$this->guardarActividadFisica($taDatosHC);
		}

		//Insertar registros de la escala NIHSS
		if($taDatosHC['Nihss']['TotalN']!==""){
			$loObjHC = new Doc_NIHSS();
			$laResultado = $loObjHC->guardarDatosN($taDatosHC['Nihss'], $this->aIngreso['nIngreso'], $this->cTipoNihss, $this->nConCon, $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['escalaHasbled'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsHasbledHC($taDatosHC['escalaHasbled'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['escalaChadsvas'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsChadsvasHC($taDatosHC['escalaChadsvas'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['escalaCrusade'])){
			$loObjHC = new EscalasRiesgoSangrado();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEsCrusadeHC($taDatosHC['escalaCrusade'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon, $lnConEvo,
					$this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre, $this->bReqAval);
		}

		if(!empty($taDatosHC['DatosSadPersons'])){
			$loObjHC = new EscalaSadPersons();
			$lnConEvo = 0;
			$laResultado = $loObjHC->guardarDatosEscSadPersonsHC($taDatosHC['DatosSadPersons'], $this->aIngreso['nIngreso'], $this->aIngreso['cCodVia'], $this->nConCon,
					$lnConEvo, $this->aIngreso['nEntidad'], $this->aIngreso['cTipId'], $this->aIngreso['nNumId'], $this->cFecCre, $this->cHorCre, $this->cUsuCre, $this->cPrgCre);
		}

		if(!empty($taDatosHC['Planmanejo']['OrdenHospitalizacion']['EspecialidadOrden'])){
			$loObjHC = new OrdenHospitalizacion();
			$cDiagnosticoPrincipal = trim($this->obtenerCiePrincipal($taDatosHC['Diagnostico']));
			$laResultado = $loObjHC->guardarOrdenAval($taDatosHC['Planmanejo']['OrdenHospitalizacion'],$this->aIngreso['nIngreso'],$this->aIngreso['nNumId'],$cDiagnosticoPrincipal,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre, $this->nConCon);
		}
	}

	function GuardarDatosUR($tcCausa)
	{
		// Actualiza tabla RIAORD
		$lnEstIng = 0;
		$laTempHC = $this->oDb
			->select('ESTING')
			->from('RIAING')
			->where('NIGING', '=', $this->aIngreso['nIngreso'])
			->get('array');
		if(is_array($laTempHC)){
			if(count($laTempHC)>0){
				$lnEstIng = $laTempHC['ESTING'];
			}
		}

		$lcCondicion = [];
		if($lnEstIng==8 && ($this->cEspecialidad=='390' || $this->cEspecialidad=='889')){
			$lcCondicion = ['COAORD'=>'890701'];
		}

		$lcTabla = 'RIAPACL02';
		$lnEstado = 3;

		//ACTUALIZA EL CONSECUTIVO DE CONSULTA EN LA TABLA RIAORD
		$lcTabla = 'RIAORDL5';
		$laDatos = $lcCondicion + [
			'CCOORD'=>$this->nConCon,
			'FERORD'=>$this->cFecCre,
			'HRLORD'=>$this->cHorCre,
			'ESTORD'=>$lnEstado,
			'RMRORD'=>$this->cRegMed,
			'UMOORD'=>$this->cUsuCre,
			'PMOORD'=>$this->cPrgCre,
			'FMOORD'=>$this->cFecCre,
			'HMOORD'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['NINORD'=>$this->aIngreso['nIngreso'],'CCIORD'=>$this->nConCit,])->actualizar($laDatos);

		$lcPlan = trim($this->aIngreso['cPlan']);
		// Enviar ANEXO 2
		if(!empty($lcPlan)){

			$laTempHC = $this->oDb
				->select('PLNCON')
				->from('FACPLNC4')
				->where('PLNCON', '=', $lcPlan)
				->get('array');
			if(is_array($laTempHC)){
				if(count($laTempHC)>0){
					$this->crear_XML($lcPlan,$tcCausa);
				}
			}
		}

		if($lcPlan!='745'){

			$laTempHC = $this->oDb
				->select('PLNCON, IN4CON')
				->from('FACPLNC4')
				->where(['PLNCON'=>$lcPlan,'IN5CON'=>0,])
				->get('array');
			if(is_array($laTempHC)){
				if(count($laTempHC)>0 && $laTempHC['IN4CON']==1){

					$laTempHC = $this->oDb
						->select('PLAEPP')
						->from('RIAEPP')
						->where([
							'TIDEPP'=>$this->aIngreso['cTipId'],
							'NIDEPP'=>$this->aIngreso['nNumId'],
						])
						->where('PLAEPP', '<>', 'SHAIO1')
						->where('PLAEPP', '<>', $lcPlan)
						->getAll('array');

					if(is_array($laTempHC)){
						if(count($laTempHC)>0){

							$lcPlanEpp = '';
							foreach($laTempHC as $laPlanes){

								$lcPlanEpp = $laPlanes['PLAEPP'];
								$laTempAuxHC = $this->oDb
									->select('PLNCON')
									->from('FACPLNC4')
									->where([
										'PLNCON'=>$lcPlanEpp,
										'TENCON'=>'05',
									])
									->get('array');
								if(is_array($laTempAuxHC)){
									if(count($laTempAuxHC)>0){
										$this->crear_XML($lcPlanEpp,$tcCausa);
									}
								}
							}
						}
					}
				}
			}
		}
		else{
			$this->crear_XML($lcPlan, $tcCausa);
		}

		$laTempHC = $this->oDb
			->select('ESTING')
			->from('RIAINGL15')
			->where('NIGING', '=', $this->aIngreso['nIngreso'])
			->get('array');
		if(is_array($laTempHC)){
			if(count($laTempHC)>0){
				$lnEstIng = $laTempHC['ESTING'];
			}
		}

		$lnFechaEgreso = 0;

		// Crear registro en la RIAINGD
		$lcTabla = 'RIAINGD';
		$laDatos = [
			'TIDIND'=>$this->aIngreso['cTipId'],
			'NIDIND'=>$this->aIngreso['nNumId'],
			'NIGIND'=>$this->aIngreso['nIngreso'],
			'VIAIND'=>$this->aIngreso['cCodVia'],
			'FEIIND'=>$this->cFecCre,
			'FEEIND'=>$lnFechaEgreso,
			'HREIND'=>$this->cHorCre,
			'ESTIND'=>$lnEstIng,
			'USRIND'=>$this->cUsuCre,
			'PGMIND'=>$this->cPrgCre,
			'FECIND'=>$this->cFecCre,
			'HORIND'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		$this->cobrarProcedimiento(0);
		$this->guardarCensoUrgencias();
	}

	function guardarCensoUrgencias()
	{
		$llPacienteHD=false;
		$loConsultaUrgencias = new ConsultaUrgencias();
		$lnIngreso=$this->aIngreso['nIngreso'];
		$llPacienteHD=$this->verificarPacienteHDia($lnIngreso);

		if (!$llPacienteHD){
			$lnTiporegistro=10;
			$lnEstado=intval($this->cConductaSeguir)==1?9:0;
			$lcTabla='CENURC';
			$lcUbicacionCenso=$loConsultaUrgencias->ubicacionCensoPacientes($lnIngreso);
			$lcDatosregistro=$this->cRegMed.'~'.$lcUbicacionCenso.'~'.$lnEstado;

			$laTempHC = $this->oDb
				->select('INGURC')
				->from('CENURC')
				->where('INGURC', '=', $lnIngreso)
				->get('array');
			if ($this->oDb->numRows()==0){
				$laParametrosCab=[
					'ingreso'=>$lnIngreso,
					'registroguarda'=>$this->cRegMed,
					'programaguarda'=>$this->cPrgCre,
					'ubicacion'=>$lcUbicacionCenso,
					'estadocenso'=>$lnEstado,
					'estadoenfermeria'=>intval($this->cConductaSeguir)==1?'':'E',
				];
				$laResultado = $loConsultaUrgencias->crearRegistroCenso($laParametrosCab);
			}else{
				$laDatosUpd = [
					'UBPURC'=>$lcUbicacionCenso,
					'ESTURC'=>$lnEstado,
					'MEDURC'=>$this->cRegMed,
					'UMOURC'=>$this->cUsuCre, 'PMOURC'=>$this->cPrgCre, 'FMOURC'=>$this->cFecCre, 'HMOURC'=>$this->cHorCre,
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where('INGURC', '=', $lnIngreso)->actualizar($laDatosUpd);
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

	function GuardarDatosHO()
	{
		if($this->aIngreso['cCodVia'] == '05'){
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'HCPARAM', ['CL1TMA'=>'RUTAXML', 'ESTTMA'=>' ']);
			$lcCupsUrgencias = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
			$lcCupsUrgencias = empty($lcCupsUrgencias)? "'890701', '890702'": $lcCupsUrgencias;

			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'CUPSURG', 'ESTTMA'=>' ']);
			$lcCupsUrgencias = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
			$lcCupsUrgencias = empty($lcCupsUrgencias)? "'890701', '890702'": $lcCupsUrgencias;
			$laCups = explode(',', str_replace("'", '', $lcCupsUrgencias));

			$laTemp = $this->oDb
				->select('NINORD')
				->from('RIAORD')
				->where([
					'NINORD'=>$this->aIngreso['nIngreso'],
					'ESTORD'=>8,
				])
				->in('COAORD', $laCups)
				->getAll('array');

			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$lcTabla = 'RIAORD';
					$laDatos = [
						'CODORD'=>$this->cEspecialidad,
						'RMRORD'=>$this->cRegMed,
						'FERORD'=>$this->cFecCre,
						'HRLORD'=>$this->cHorCre,
						'ESTORD'=>3,
						'UMOORD'=>$this->cUsuCre,
						'PMOORD'=>$this->cPrgCre,
						'FMOORD'=>$this->cFecCre,
						'HMOORD'=>$this->cHorCre
					];
					$llResultado = $this->oDb->tabla($lcTabla)->where(['NINORD'=>$this->aIngreso['nIngreso'],'ESTORD'=>8])->in('COAORD', [$lcCupsUrgencias])->actualizar($laDatos);

					// Cobrar Prodecimiento
					$this->cobrarProcedimiento(0);
				}
			}
			$this->guardarCensoUrgencias();
		}
	}

	function GuardarDatosCE()
	{
		$lnEstado = 3;

		// Actualiza RIAORD
		$lcTabla = 'RIAORD';
		$laDatos = [
			'RMRORD'=>$this->cRegMed,
			'FERORD'=>$this->cFecCre,
			'HRLORD'=>$this->cHorCre,
			'ESTORD'=>$lnEstado,
			'CCOORD'=>$this->nConCon,
			'UMOORD'=>$this->cUsuCre,
			'PMOORD'=>$this->cPrgCre,
			'FMOORD'=>$this->cFecCre,
			'HMOORD'=>$this->cHorCre
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['NINORD'=>$this->aIngreso['nIngreso'],'CCIORD'=>$this->nConCit])->actualizar($laDatos);

		// Actualiza RIACIT
		$lcTabla = 'RIACITL01';
		$laDatos = [
			'ESTCIT'=>$lnEstado,
			'UMOCIT'=>$this->cUsuCre,
			'PMOCIT'=>$this->cPrgCre,
			'FMOCIT'=>$this->cFecCre,
			'HMOCIT'=>$this->cHorCre
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['TIDCIT'=>$this->aIngreso['cTipId'],'NIDCIT'=>$this->aIngreso['nNumId'],'CCICIT'=> $this->nConCit,'NINCIT'=>$this->aIngreso['nIngreso']])->actualizar($laDatos);

		// Actualiza RIAESTM
		$lcTabla = 'RIAESTM45';
		$laDatos = [
			'RMEEST'=>$this->cRegMed,
			'DPTEST'=>$this->cEspecialidad,
			'UMOEST'=>$this->cUsuCre,
			'PMOEST'=>$this->cPrgCre,
			'FMOEST'=>$this->cFecCre,
			'HMOEST'=>$this->cHorCre
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['INGEST'=>$this->aIngreso['nIngreso'],'CNOEST'=> $this->nConCit])->actualizar($laDatos);

		// Actualia o crea en la RIACID
		$lnUnidad = 0;
		$laTemp = $this->oDb
			->select('CODPME')
			->from('UNIPMEL01')
			->where([
				'REGPME'=>$this->cRegMed,
				'ESTPME'=>' ',
			])
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lnUnidad = $laTemp['CODPME']; settype($lnUnidad,'integer');
			}
		}

		$lcCodPro = $this->cCodPro;
		$lnFecOrd = $lnHorOrd = 0;

		$laTemp = $this->oDb
			->select('NIDCID')
			->from('RIACIDL1')
			->where([
				'TIDCID'=>$this->aIngreso['cTipId'],
				'NIDCID'=>$this->aIngreso['nNumId'],
				'CCICID'=>$this->nConCit,
				'CCUCID'=>$this->nConCon,
				'PGMCID'=>'HC0007',
			])
			->get('array');

		if(!is_array($laTemp)){$laTemp=[];}
		if(count($laTemp)==0){
			//INSERTA EL REGISTRO EN LA TABLA RIACHC
			$lcTabla = 'RIACIDL1';
			$laDatos = [
				'TIDCID'=>$this->aIngreso['cTipId'],
				'NIDCID'=>$this->aIngreso['nNumId'],
				'UNICID'=>$lnUnidad,
				'CCICID'=>$this->nConCit,
				'FGRCID'=>$this->cFecCre,
				'HGRCID'=>$this->cHorCre,
				'CLICID'=>1,
				'CUPCID'=>$lcCodPro,
				'ESTCID'=>$lnEstado,
				'NINCID'=>$this->aIngreso['nIngreso'],
				'CODCID'=>$this->cEspecialidad,
				'RMECID'=>$this->cRegMed,
				'FRLCID'=>$lnFecOrd,
				'HOCCID'=>$lnHorOrd,
				'VIACID'=>$this->aIngreso['cCodVia'],
				'USRCID'=>$this->cUsuCre,
				'PGMCID'=>$this->cPrgCre,
				'FECCID'=>$this->cFecCre,
				'HORCID'=>$this->cHorCre
			];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
		}


		$laTemp = $this->oDb
			->select('ORDCID, CCICID')
			->from('RIACIDL1')
			->where([
				'TIDCID'=>$this->aIngreso['cTipId'],
				'NIDCID'=>$this->aIngreso['nNumId'],
				'UNICID'=>$lnUnidad,
				'CCICID'=>$this->nConCit,
			])
			->where('ORDCID','>',0)
			->get('array');
		$lnConCita = 0;
		$lnOrdCita = 0;

		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lnConCita = $laTemp['CCICID'];
				$lnOrdCita = $laTemp['ORDCID'];

				// Actualiza RIAORD
				$lcTabla = 'RIAORDLU';
				$laDatos = [
					'FERORD'=>$this->cFecCre,
					'HRLORD'=>$this->cHorCre,
					'ESTORD'=>$lnEstado,
					'CCOORD'=>$this->nConCon,
					'UMOORD'=>$this->cUsuCre,
					'PMOORD'=>$this->cPrgCre,
					'FMOORD'=>$this->cFecCre,
					'HMOORD'=>$this->cHorCre
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where(['NINORD'=>$this->aIngreso['nIngreso'],'CCIORD'=>$lnConCita,])->actualizar($laDatos);

				// Actualiza RIACIT
				$lcTabla = 'RIACITL01';
				$laDatos = [
					'ESTCIT'=>$lnEstado,
					'UMOCIT'=>$this->cUsuCre,
					'PMOCIT'=>$this->cPrgCre,
					'FMOCIT'=>$this->cFecCre,
					'HMOCIT'=>$this->cHorCre
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where(['TIDCIT'=>$this->aIngreso['cTipId'],'NIDCIT'=>$this->aIngreso['nNumId'],'CCICIT'=>$lnConCita,'NINCIT'=>$this->aIngreso['nIngreso'],])->actualizar($laDatos);

				$laTemp = $this->oDb
					->select('NIDCID')
					->from('RIACIDL1')
					->where([
						'TIDCID'=>$this->aIngreso['cTipId'],
						'NIDCID'=>$this->aIngreso['nNumId'],
						'CCICID'=>$lnConCita,
						'CCUCID'=>$this->nConCon,
					])
					->where('ORDCID','>',0)
					->get('array');
				if(!is_array($laTemp)){ $laTemp=[]; }

				if(count($laTemp)==0){
					$lcTabla = 'RIACIDL1';
					$laDatos = [
						'TIDCID'=>$this->aIngreso['cTipId'],
						'NIDCID'=>$this->aIngreso['nNumId'],
						'UNICID'=>$lnUnidad,
						'CCICID'=>$lnConCita,
						'CCUCID'=>$this->nConCon,
						'FGRCID'=>$this->cFecCre,
						'HGRCID'=>$this->cHorCre,
						'CLICID'=>1,
						'CUPCID'=>$lcCodPro,
						'ESTCID'=>$lnEstado,
						'NINCID'=>$this->aIngreso['nIngreso'],
						'CODCID'=>$this->cEspecialidad,
						'RMECID'=>$this->cRegMed,
						'FRLCID'=>$lnFecOrd,
						'HOCCID'=>$lnHorOrd,
						'VIACID'=>$this->aIngreso['cCodVia'],
						'USRCID'=>$this->cUsuCre,
						'PGMCID'=>$this->cPrgCre,
						'FECCID'=>$this->cFecCre,
						'HORCID'=>$this->cHorCre
					];
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
				}
			}
		}
	}

	public function cobrarProcedimiento($tnCitaCups=0)
	{
		$lcCobrarCups=$lcCodPro='';
		$lnConsecutivoCita=0;
		if ($tnCitaCups==0){
			$laTemp = $this->oDb
				->select('CUPEST')
				->from('RIAESTM')
				->where(['INGEST'=>$this->aIngreso['nIngreso']])
				->where("(CUPEST LIKE '8907%' OR ELEEST LIKE '8907%')")
				->get('array');
			if(!is_array($laTemp)){$laTemp=[];}
			if(count($laTemp)==0){
				$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'COBRURG', 'CL2TMA'=>$this->cEspecialidad, 'ESTTMA'=>' ']);
				$lcCodPro = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
				$lcCodPro = empty(trim($lcCodPro))?'890702':$lcCodPro;
				$lcCobrarCups='S';
				$lnConsecutivoCita=$this->nConCit;
			}
		}else{
			$lcCodPro=$this->aCupElectro['CUPS'];
			$lcCobrarCups='S';
			$lnConsecutivoCita=$tnCitaCups;
		}

		// COBRAR PROCEDIMIENTO
		if ($lcCobrarCups=='S'){
			$laData = [
				'ingreso'       => $this->aIngreso['nIngreso'],
				'numIdPac'      => $this->aIngreso['nNumId'],
				'codCup'        => $lcCodPro,
				'codVia'        => $this->aIngreso['cCodVia'],
				'codPlan'       => $this->aIngreso['cPlan'],
				'regMedOrdena'  => $this->cRegMed,
				'regMedRealiza' => $this->cRegMed,
				'espMedRealiza' => $this->cEspecialidad,
				'secCama'       => trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
				'cnsCita'       => $lnConsecutivoCita,
				'portatil'      => '',
			];
			$loCobros = new Cobros();
			$lbRet = $loCobros->cobrarProcedimiento($laData);
		}		
	}

	public function verificarPacienteHDia($tnIngreso=0)
	{
		$llHospitalDia=false;

		$laTempHab = $this->oDb
			->select('A.INGHAB INGRESO')
			->from('FACHAB A')
			->leftJoin("TABMAE B", "A.SECHAB=B.CL1TMA", null)
			->where('A.INGHAB', '=', $tnIngreso)
			->where('B.TIPTMA', '=', 'SECHAB')
			->where('B.CL4TMA', '=', 'URGEN')
			->where('B.DE2TMA', '=', 'HOSPITALIZACION MIXTO')
			->getAll('array');
		if ($this->oDb->numRows()>0){
			$llHospitalDia=true;
		}
		return $llHospitalDia;
	}

	public function consultaSeccionesHd()
	{
		$lcListaSeccionesHd='';
		$laParametros = $this->oDb
			->select('trim(CL1TMA) SECCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'SECHAB')
			->where('CL4TMA', '=', 'URGEN')
			->where('DE2TMA', '=', 'HOSPITALIZACION MIXTO')
			->where('ESTTMA', '=', '')
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcListaSeccionesHd=$laParametros['SECCION'];
		}
		unset($laParametros);
		return $lcListaSeccionesHd;
	}

	private function obtenerServidor()
	{
		// Ruta principal para anexo 2
		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA,OP5TMA', 'HCPARAM', ['CL1TMA'=>'HCWEB', 'CL2TMA'=>'RUTAXML', 'CL3TMA'=>'001', 'ESTTMA'=>'']);
		$lcRutaPrincipal = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
		$lcDirPrincipal = trim(AplicacionFunciones::getValue($loTabmae, 'OP5TMA', ''));
		$lcServerPrincipal = strstr(substr($lcRutaPrincipal, 2),'/',true);
		$laConfigPrincipal = $this->oDb->configServer($lcServerPrincipal);

		// Ruta backup para anexo 2
		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA,OP5TMA', 'HCPARAM', ['CL1TMA'=>'HCWEB', 'CL2TMA'=>'RUTAXML', 'CL3TMA'=>'002', 'ESTTMA'=>'']);
		$lcRutaBackup = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
		$lcDirBackup = trim(AplicacionFunciones::getValue($loTabmae, 'OP5TMA', ''));
		$lcServerBackup = strstr(substr($lcRutaBackup, 2),'/',true);
		$laConfigBackup = $this->oDb->configServer($lcServerPrincipal);

		$this->aServidor = [
			'principal'=>[
				'ruta'=>$lcRutaPrincipal,
				'carpeta'=>$lcDirPrincipal,
				'server'=>$lcServerPrincipal,
				'wrkg'=>$laConfigPrincipal['workgroup'],
				'user'=>$laConfigPrincipal['user'],
				'pass'=>$laConfigPrincipal['pass'],
			],
			'backup'=>[
				'ruta'=>$lcRutaBackup,
				'carpeta'=>$lcDirBackup,
				'server'=>$lcServerBackup,
				'wrkg'=>$laConfigBackup['workgroup'],
				'user'=>$laConfigBackup['user'],
				'pass'=>$laConfigBackup['pass'],
			],
		];
	}

	// crea carpeta para backup de anexo2
	private function crearCarpetas()
	{
		$lbReturn = false;
		$lcServer = $this->aServidor['backup']['ruta'];
		$lcRuta = $this->aServidor['backup']['carpeta'].$this->cFecCre.'/';

		if ($this->oDb->soWindows) {
			$lbReturn = file_exists($lcServer.$lcRuta);

		} else {
			$loSmbClient = new \SmbClient($lcServer, $this->aServidor['backup']['user'], $this->aServidor['backup']['pass']);
			$lbReturn = $loSmbClient->file_exists($lcRuta) == 'dir';
			$loSmbClient = null;
			unset($loSmbClient);
		}

		// No existe la ruta, debe ser creada
		if (!$lbReturn) {
			try {
				if ($this->oDb->soWindows) {
					// En windows mkdir recursivo
					$lbReturn = mkdir($lcServer.$lcRuta, 0777, true);

				} else {
					// Linux con cliente samba
					$loSmbClient = new \SmbClient($lcServer, $this->aServidor['backup']['user'], $this->aServidor['backup']['pass']);

					$lbDisplayErr = ini_get('display_errors');
					ini_set('display_errors', '0');

					if ($loSmbClient->mkdir($this->cFecCre)) {
						if ($loSmbClient->mkdir($lcRuta)) {
							$lbReturn = $loSmbClient->file_exists($this->cFecCre) == 'dir';
						}
					}
					$loSmbClient = null;
					unset($loSmbClient);
					ini_set('display_errors', $lbDisplayErr);
				}
			} catch ( \Exception $loError ) {
				$this->aError = [
					'Mensaje' => "El usuario no tiene permisos para crear archivo XML $lcRuta",
					'Valido' => false,
				];
			}
		}
		$this->aServidor['backup']['carpeta'] = $lcRuta;

		return $lbReturn;
	}

	private function crear_XML($tcPlan, $tcCausaE)
	{
		//Crear directorio si no existe
		$this->obtenerServidor();
	//	$this->crearCarpetas();
		$lcNomArchivo = $this->aIngreso['nIngreso'] . $this->cFecCre . $this->cHorCre . '.xml';


		// Variables - Datos del pagador - Entidad
		$lcNitEnt = $lcRipEnt = $lcNomEnt = '';
		if($tcPlan=='745'){
			$lcNitEnt = str_pad('800130907',13,'0',STR_PAD_LEFT);
			$lcRipEnt = 'EPS002';
			$lcNomEnt = 'SALUD TOTAL EPS   POS';
		}
		else{

			$laTemp = $this->oDb
				->select('NI1CON, RIACON')
				->from('FACPLNC4')
				->where('PLNCON', '=', $tcPlan)
				->get('array');
			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$lcNitEnt = str_pad($laTemp['NI1CON'],13,'0',STR_PAD_LEFT);
					$lcRipEnt = trim(substr($laTemp['RIACON'],0,6));

					$laTemp = $this->oDb
						->select('TE1SOC')
						->from('PRMTE107')
						->where('TE1COD', '=', $lcNitEnt)
						->get('array');
					if(is_array($laTemp)){
						if(count($laTemp)>0){
							$lcNomEnt = trim(substr($laTemp['TE1SOC'],0,150));
						}
					}
				}
			}
		}

		// Datos Remision
		$lcCiRem = $lcDips = $lcDepRem = $lcCiuRem = $lcPvRem = '';
		$lcPvre = '2';

		$laTemp = $this->oDb
			->select('A.*, B.DS1IPS')
			->from('RIAINADL01 AS A')
			->leftJoin('TABIPSL01 AS B', 'A.OP2INA=B.CODIPS', null)
			->where('A.INGINA', '=', $this->aIngreso['nIngreso'])
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){

				$lcPvRem = trim($laTemp['OP1INA'])=='S'? '1': '2';
				$lcCiRem = trim($laTemp['OP2INA']);

				$lcDepRem = trim(substr($laTemp['OP3INA'],9,8))==0? '': trim(substr($laTemp['OP3INA'],9,8));
				$lcCiuRem = $lcDips ='';
				if(!empty($lcDepRem)){
					$lcCiuRem = ($lcDepRem='11'? '001': substr($laTemp['OP3INA'],19,8));
					$lcDips = substr($laTemp['DS1IPS'],0,150);
				}

				if($lcDepRem == '25' && $lcCiuRem == '097'){
					$lcCiuRem = '001';
				}
			}
		}

		$lcDepRem = str_pad(trim($lcDepRem),2,'0',STR_PAD_LEFT);
		$lcCiuRem = str_pad(trim($lcCiuRem),3,'0',STR_PAD_LEFT);

		// Variables - Datos del paciente
		$lcTipoDoc = $lcNomPac1 = $lcNomPac2 = $lcApePac1 = $lcApePac2 = $lcDireccion = $lcTelefono = '';
		$lcFecIng = $lcHorIng = $lcCltr = $lcTipoUsr = $lcNomUsuario = $lcAbrevDoc = $lcDpto = $lcMunicipio = '';
		$lcTipoAfi = $lcTriage = $lcFecNac = '';

		$lnNroDoc = $lnDpto = $lnMunicipio = $lnFecNac = $lnTidf = $lnTia2 = $lnFecIng = 0;

		$loIngreso=new Ingreso;
		$loIngreso->cargarIngreso($this->aIngreso['nIngreso']);
		$lcTipoDoc = $loIngreso->cId;
		$lnNroDoc = $loIngreso->nId;
		$lnFecNac = $loIngreso->oPaciente->nNacio;
		$lcFecNac = substr($lnFecNac,0,4) . '-' . substr($lnFecNac,4,2) . '-' . substr($lnFecNac,6,2);
		$lcNomPac1 = trim($loIngreso->oPaciente->cNombre1);
		$lcNomPac2 = (empty(trim($loIngreso->oPaciente->cNombre2))?'NOTIENE':trim($loIngreso->oPaciente->cNombre2));
		$lcApePac1 = trim($loIngreso->oPaciente->cApellido1);
		$lcApePac2 = (empty(trim($loIngreso->oPaciente->cApellido2))?'NOTIENE':trim($loIngreso->oPaciente->cApellido2));
		$lcDireccion = trim($loIngreso->oPaciente->cDireccion);
		$lcTelefono = strlen($loIngreso->oPaciente->cTelefono)!=7?'1000000':$loIngreso->oPaciente->cTelefono;
		$lnPais = $loIngreso->oPaciente->nPais;
		$lcDpto = ($loIngreso->oPaciente->cDepartamento=='0'?'':$loIngreso->oPaciente->cDepartamento);
		$lcDpto = str_pad(trim($lcDpto),2,'0',STR_PAD_LEFT);
		$lcMunicipio = $loIngreso->oPaciente->cMunicipio;
		$lnFecIng = $loIngreso->nIngresoFecha;
		$lnHorIng = $loIngreso->nIngresoHora;
		$lcTipoUsr = (empty($loIngreso->cAfiliadoUsuario)?'C':$loIngreso->cAfiliadoUsuario);
		$lcFecIng = substr($lnFecIng,0,4) . '-' . substr($lnFecIng,4,2) . '-' . substr($lnFecIng,6,2);
		$lctemp = str_pad(trim($lnHorIng),6,'0',STR_PAD_LEFT);
		$lcHorIng = substr($lctemp,0,2).':'.substr($lctemp,2,2).':'.substr($lctemp,4,2);

		// Valida Dirección
		$lcDireccion = str_replace('>',' ',$lcDireccion);
		$lcDireccion = str_replace('<',' ',$lcDireccion);

		if($lnPais!=101){
			$lcDpto = '11';
			$lcMunicipio = '1';
		}
		else{
			if($lcDpto=='11'){
				$lcMunicipio = '1';
			}
			if($lcDpto=='25' && trim($lcMunicipio)=='097'){
				$lcMunicipio = '1';
			}
		}
		$lcMunicipio = str_pad(trim($lcMunicipio),3,'0',STR_PAD_LEFT);
		// Datos del usuario
		$lcNomUsuario = $lcTriage = $lcAbrevDoc = $lcTipoAfi = '';
		$laUsuario = $this->oDb
			->select('ESTRGM, TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE')
			->tabla('RIARGMN')
			->where('USUARI', '=', $this->cUsuCre)
			->get('array');

		if(is_array($laUsuario)){
			if(count($laUsuario)>0){
				$lcNomUsuario = $laUsuario['ESTRGM']=='1'?$laUsuario['NOMBRE']:'USUARIO FACTURACION';
			}
		}
		$lcNomUsuario = str_replace('ñ','n',$lcNomUsuario);
		$lcNomUsuario = str_replace('Ñ','ñ',$lcNomUsuario);

		// Tipo de Identificación
		$laTemp = $this->oDb
			->select('DOCUME')
			->from('RIATIL01')
			->where('TIPDOC', '=', $lcTipoDoc)
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lcAbrevDoc = trim($laTemp['DOCUME']);
			}
		}

		// Tipo de afiliado
		$laTemp = $this->oDb
			->select('USRTUS')
			->from('RIATIUS')
			->where('CODTUS', '=', $lcTipoUsr)
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lcTipoAfi = trim($laTemp['USRTUS']);
			}
		}

		$laTemp = $this->oDb
			->select('CLMTRI, PRCTRI, CLRTRI')
			->from('TRIAGUL01')
			->where('NIGTRI', '=', $this->aIngreso['nIngreso'])
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lcTriage =(!empty(trim($laTemp['CLMTRI']))?(trim($laTemp['CLMTRI'])):(!empty(trim($laTemp['PRCTRI']))?(trim($laTemp['PRCTRI'])):(!empty(trim($laTemp['CLRTRI']))?(trim($laTemp['CLRTRI'])):'2')));
			}
		}

		// Descripción Tipo causa
		$laTemp = $this->oDb
			->select('SUBSTR(DE1TMA,1,30) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA=\'CODCEX\' AND ESTTMA<>\'1\'')
			->where('CL1TMA', '=',$tcCausaE)
			->orderBy('DE1TMA')
			->get('array');
		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lcMotivoCons = substr(trim($laTemp['DESCRIPCION']),0,199);
			}
		}

		$lcCausaExt = str_pad(trim($tcCausaE),2,'0',STR_PAD_LEFT);

		if ($lcCausaExt=='00' || $lcCausaExt=='03' || $lcCausaExt=='04' || $lcCausaExt=='05' ||
			$lcCausaExt=='07' || $lcCausaExt=='08' || $lcCausaExt=='09' || $lcCausaExt=='10' ||
			$lcCausaExt=='11' || $lcCausaExt=='12' || $lcCausaExt=='15' ){
			$lcCausaExt = '13';
		}

		// Actualiza o crea consecutivo de anexo 2
		$lnConsec = 0;
		$lcEstAud = 'C';

		$laConsecutivo = $this->oDb->max('CONAUC', 'MAXIMO')->from('ANXURCL01')->where(['FEAAUC'=>$this->cFecCre])->get('array');

		if(is_array($laConsecutivo)){
			if(count($laConsecutivo)>0){
				$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
			}
		}
		unset($laConsecutivo);
		$lnConsec = $lnConsec+1;
		$lcTabla = 'ANXURCL01';

		if($lnConsec == 1){
			$laDatos = [
				'FEAAUC'=>$this->cFecCre,
				'CONAUC'=>$lnConsec,
				'USRAUC'=>$this->cUsuCre,
				'PGMAUC'=>$this->cPrgCre,
				'FECAUC'=>$this->cFecCre,
				'HORAUC'=>$this->cHorCre
			];
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
		}
		else{

			$laDatos = [
				'CONAUC'=>$lnConsec,
				'UMOAUC'=>$this->cUsuCre,
				'PMOAUC'=>$this->cPrgCre,
				'FMOAUC'=>$this->cFecCre,
				'HMOAUC'=>$this->cHorCre
			];
			$llResultado = $this->oDb->tabla($lcTabla)->where(['FEAAUC'=>$this->cFecCre])->actualizar($laDatos);
		}

		// Detalle consecutivo
		$lcTabla = 'ANXURDL01';
		$laDatos = [
			'INGAUD'=>$this->aIngreso['nIngreso'],
			'FEAAUD'=>$this->cFecCre,
			'CONAUD'=>$lnConsec,
			'ESTAUD'=>$lcEstAud,
			'USRAUD'=>$this->cUsuCre,
			'PGMAUD'=>$this->cPrgCre,
			'FECAUD'=>$this->cFecCre,
			'HORAUD'=>$this->cHorCre
		];
		$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);

		// Datos del prestador SHAIO
		$lcNombreEnt = $lcNitEnt = $lcDigEnt = $lcCodEnt = $lcDirEnt = $lcTelEnt = $lcExtEnt = $lcCelEnt = '';

		$laTemp = $this->oDb
			->from('TABMAE')
			->where('TIPTMA=\'DATOS\' AND ESTTMA<>\'1\'')
			->get('array');

		if(is_array($laTemp)){
			if(count($laTemp)>0){
				$lcNombreEnt = trim($laTemp['DE1TMA']);
				$laWordsReg = explode(',', $laTemp['OP5TMA']);
				$lcNitEnt = trim($laWordsReg[0]);
				$lcCodEnt = trim($laWordsReg[1]);
				$lcDigEnt = $laTemp['OP1TMA'];
				$lcDirEnt = trim($laTemp['DE2TMA']);
				$lcTelEnt = trim($laTemp['OP2TMA']);
				$lcExtEnt = trim($laTemp['OP3TMA']);
				$lcCelEnt = trim($laTemp['OP4TMA']);
			}
		}

		// Datos de diagnosticos
		$lcDxCod01 = $lcDxCod02 = $lcDxCod03 = $lcDxCod04 = $lcDxDes01 = $lcDxDes02 = $lcDxDes03 = $lcDxDes04 = '';

		$lcDxCod01 = $this->aDxXML[0]['CODIGO'];
		$lcDxDes01 = $this->aDxXML[0]['DESCRIP'];
		$lcDxCod02 = $this->aDxXML[1]['CODIGO'] ?? '';
		$lcDxDes02 = $this->aDxXML[1]['DESCRIP'] ?? '';
		$lcDxCod03 = $this->aDxXML[2]['CODIGO'] ?? '';
		$lcDxDes03 = $this->aDxXML[2]['DESCRIP'] ?? '';
		$lcDxCod04 = $this->aDxXML[3]['CODIGO'] ?? '';
		$lcDxDes04 = $this->aDxXML[3]['DESCRIP'] ?? '';

		// Inicio archivo XML
		$lcChar = "\n"; // en fox es CHR(13)+CHR(10)
		$lcArchXML =  '<InformeUrgencias>' . $lcChar
					. ' <General>' . $lcChar
					. '  <Numero>' . $lnConsec . '</Numero>' . $lcChar
					. '  <Fecha>' . substr($this->cFecCre,0,4) . '-' . substr($this->cFecCre,4,2) . '-' . substr($this->cFecCre,6,4) . '</Fecha>' . $lcChar
					. '  <Hora>' . substr(str_pad(trim($this->cHorCre),6,'0',STR_PAD_LEFT),0,2).':'.substr(str_pad(trim($this->cHorCre),6,'0',STR_PAD_LEFT),2,2).':'.substr(str_pad(trim($this->cHorCre),6,'0',STR_PAD_LEFT),4,2) . '</Hora>' . $lcChar
					. '  <Prestador>' . $lcNombreEnt . '</Prestador>' . $lcChar
					. '  <IdPrestador>' . $lcNitEnt . '</IdPrestador>' . $lcChar
					. '  <TipoIdPrestador>NI</TipoIdPrestador>' . $lcChar
					. '  <DigVerif>' . $lcDigEnt . '</DigVerif>' . $lcChar
					. '  <CodPrestador>' . $lcCodEnt .'</CodPrestador>' . $lcChar
					. '  <DireccionPrestador>' . $lcDirEnt . '</DireccionPrestador>' . $lcChar
					. '  <IndicTelefPrestador>1</IndicTelefPrestador>' . $lcChar
					. '  <TelefonoPrestador>' . $lcTelEnt . '</TelefonoPrestador>' . $lcChar
					. '  <DepartamentoPrestador>11</DepartamentoPrestador>' . $lcChar
					. '  <MunicipioPrestador>001</MunicipioPrestador>' . $lcChar
					. ' </General>' . $lcChar;

		// Datos del pagador - ENTIDAD
		$lcArchXML .= ' <Pagador>' . $lcChar
					. '  <EntidadResponsable>' . trim($lcNomEnt) . '</EntidadResponsable>' . $lcChar
					. '  <CodigoEntidad>' . trim($lcRipEnt) . '</CodigoEntidad>' . $lcChar
					. ' </Pagador>' . $lcChar;

		// Datos del paciente
		$lcArchXML .= ' <Paciente>' . $lcChar
					. '  <Nombre>' . $lcChar
					. '   <PrimerApellido>' . trim($lcApePac1) . '</PrimerApellido>' . $lcChar
					. '   <SegundoApellido>' . trim($lcApePac2) . '</SegundoApellido>' . $lcChar
					. '   <PrimerNombre>' . trim($lcNomPac1) . '</PrimerNombre>' . $lcChar
					. '   <SegundoNombre>' . trim($lcNomPac2) . '</SegundoNombre>' . $lcChar
					. '  </Nombre>' . $lcChar
					. '  <Identificacion>' . $lcChar
					. '   <TipoIdentificacion>' . trim($lcAbrevDoc) . '</TipoIdentificacion>' . $lcChar
					. '   <NumeroIdentificacion>' . trim($lnNroDoc) . '</NumeroIdentificacion>' . $lcChar
					. '  </Identificacion>' . $lcChar
					. '  <DatosPersonales>' . $lcChar
					. '   <FechaNacimiento>' . trim($lcFecNac) . '</FechaNacimiento>' . $lcChar
					. '   <Ubicacion>' . $lcChar
					. '    <DireccionResidencia>' . trim($lcDireccion) . '</DireccionResidencia>' . $lcChar
					. '    <TelefonoFijo>' . str_pad(trim($lcTelefono),7,'0',STR_PAD_LEFT) . '</TelefonoFijo>' . $lcChar
					. '    <Departamento>' . $lcDpto . '</Departamento>' . $lcChar
					. '    <Ciudad>' . $lcMunicipio . '</Ciudad>' . $lcChar
					. '  </Ubicacion>' . $lcChar
					. '  </DatosPersonales>' . $lcChar
					. ' </Paciente>' . $lcChar;

		// Datos del Ingreso - COBERTURA
		$lcArchXML .= ' <CoberturaSalud>' . trim($lcTipoAfi) . '</CoberturaSalud>' . $lcChar
					. ' <OrigenAtencion>' . trim($lcCausaExt) . '</OrigenAtencion>' . $lcChar
					. ' <ClasificacionTriage>' . trim($lcTriage) . '</ClasificacionTriage>' . $lcChar
					. ' <FechaIngreso>' . trim($lcFecIng) . '</FechaIngreso>' . $lcChar
					. ' <HoraIngreso>' . trim($lcHorIng) . '</HoraIngreso>' . $lcChar;

		// Datos del paciente remitido
		$lcArchXML .= ' <PacienteRemitido>' . trim($lcPvRem) . '</PacienteRemitido>' . $lcChar
					. ' <PrestadorRemite>' . $lcChar
					. '  <CodigoPrestador>' . trim($lcCiRem) . '</CodigoPrestador>' . $lcChar
					. '  <NombrePrestador>' . trim($lcDips) . '</NombrePrestador>' . $lcChar
					. '  <DepartamentoPR>' . $lcDepRem . '</DepartamentoPR>' . $lcChar
					. '  <MunicipioPR>' . $lcCiuRem . '</MunicipioPR>' . $lcChar
					. ' </PrestadorRemite>' . $lcChar;

		// Datos HC motivo de consulta y diagnosticos
		$lcArchXML .= ' <MotivoConsulta>' . $lcMotivoCons . '</MotivoConsulta>' . $lcChar
					. ' <ImpresionDiagnostica>' . $lcChar
					. '  <CodigoCIE10Principal>' . trim($lcDxCod01) . '</CodigoCIE10Principal>' . $lcChar
					. '  <DescripcionPrincipal>' . trim($lcDxDes01) . '</DescripcionPrincipal>' . $lcChar
					. '  <CodigoCIE101>' . trim($lcDxCod02) . '</CodigoCIE101>' . $lcChar
					. '  <Descripcion1>' . trim($lcDxDes02) . '</Descripcion1>' . $lcChar
					. '  <CodigoCIE102>' . trim($lcDxCod03) . '</CodigoCIE102>' . $lcChar
					. '  <Descripcion2>' . trim($lcDxDes03) . '</Descripcion2>' . $lcChar
					. '  <CodigoCIE103>' . trim($lcDxCod04) . '</CodigoCIE103>' . $lcChar
					. '  <Descripcion3>' . trim($lcDxDes04) . '</Descripcion3>' . $lcChar
					. ' </ImpresionDiagnostica>' . $lcChar;

		// Datos destino paciente siempre 2
		$lcArchXML .= ' <DestinoPaciente>2</DestinoPaciente>' . $lcChar;

		//Datos Informante
		$lcArchXML .= ' <Informante>' . $lcChar
					. '  <Nombre>' . $lcNomUsuario .'</Nombre>' . $lcChar
					. '  <Cargo>Auxiliar Administrativo</Cargo>' . $lcChar
					. '  <IndicaTel>1</IndicaTel>' . $lcChar
					. '  <Telefono>' . $lcTelEnt . '</Telefono>' . $lcChar
					. '  <ExtTele>' . $lcExtEnt . '</ExtTele>' . $lcChar
					. '  <CelularInstitucional>' . $lcCelEnt . '</CelularInstitucional>' . $lcChar
					. ' </Informante>' . $lcChar;

		//FIN
		$lcArchXML .= '</InformeUrgencias>' . $lcChar;

		if ($this->oDb->soWindows) {

			// Principal
			$lcRuta = $this->aServidor['principal']['ruta'] . $this->aServidor['principal']['carpeta'];
			$lcNomArc = $lcRuta . $lcNomArchivo;
			if (!is_dir($lcRuta)) { mkDir($lcRuta, 0777, true); }
			$lnFileXml = fopen($lcNomArc, 'a');
			fputs($lnFileXml, $lcArchXML);
			fclose($lnFileXml);
		} else {
			// Crea un archivo temporal
			$lcRutaTmp = sys_get_temp_dir() . '/' . uniqid('anx',true) . '.xml';
			$lnFileXml = fopen($lcRutaTmp, 'a');
			fputs($lnFileXml, $lcArchXML);
			fclose($lnFileXml);

			if (file_exists($lcRutaTmp)) {
				$lbDisplayErr = ini_get('display_errors');
				ini_set('display_errors', '0');

				// Principal
				$lcNomArc = $this->aServidor['principal']['carpeta'] . $lcNomArchivo;
				$loSmbClient = new \SmbClient($this->aServidor['principal']['ruta'], $this->aServidor['principal']['user'], $this->aServidor['principal']['pass']);
				if (!$loSmbClient->put($lcRutaTmp, $lcNomArc)) {
					$lcError = "No se pudo copiar el archivo {$lcNomArc} al servidor.";
				}
				$loSmbClient = null;

				// Backup
				$lcNomArc = $this->aServidor['backup']['carpeta'] . $lcNomArchivo;
				$loSmbClient = new \SmbClient($this->aServidor['backup']['ruta'], $this->aServidor['backup']['user'], $this->aServidor['backup']['pass']);
				if (!$loSmbClient->put($lcRutaTmp, $lcNomArc)) {
					$lcError = "No se pudo copiar el archivo {$lcNomArc} al servidor.";
				}
				$loSmbClient = null;
				unset($loSmbClient);

				// Eliminar el archivo temporal
				$lnFileSizeLinux = filesize($lcRutaTmp)??0;
				$lcCreadoLinux = date('Y/m/d H:i:s', fileatime($lcRutaTmp));
				unlink($lcRutaTmp);
				ini_set('display_errors', $lbDisplayErr);
			}
		}
	}
}
