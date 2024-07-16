<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.MailEnviar.php';
require_once __DIR__ . '/class.Cobros.php';
require_once __DIR__ . '/class.Evoluciones.php';
require_once __DIR__ . '/class.Cup.php';
require_once __DIR__ . '/class.OrdMedOxigeno.php';
require_once __DIR__ . '/class.Especialidad.php';
require_once __DIR__ . '/class.Diagnostico.php';
require_once __DIR__ . '/class.HL7_Enviar.php';
require_once __DIR__ . '/class.NoPosFunciones.php';
require_once __DIR__ . '/class.FeFunciones.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once __DIR__ . '/class.MedicamentoFormula.php';
require_once __DIR__ . '/class.Inventarios.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\AplicacionFunciones;
use NUCLEO\MailEnviar;
use NUCLEO\Cobros;
use NUCLEO\Evoluciones;
use NUCLEO\OrdMedOxigeno;
use NUCLEO\Especialidad;
use NUCLEO\Diagnostico;
use NUCLEO\HL7_Enviar;
use NUCLEO\NoPosFunciones;
use NUCLEO\FeFunciones;
use NUCLEO\FormulacionParametros;
use NUCLEO\MedicamentoFormula;
use NUCLEO\Inventarios;

class OrdenesMedicas
{
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cNombreUsuario = '';
	protected $cApellidoUsuario = '';
	protected $cApellidoUsuarioHl7 = '';
	protected $cEspecialidad = '';
	protected $cNombreEspecialidad = '';
	protected $cFechaHoraEvolucion = '';
	protected $cRegMed = '';
	protected $ccodigoViaIngreso = '';
	protected $cChrEnter = '';
	protected $cCupsInterconsulta = '';
	protected $cCupsGlucometria = '';
	protected $cTipoEstudiante = '';
	protected $cConsOrdEstudiante = '';
	protected $cDiagnosticosEstudiante = '';
	protected $cOpcional1Estudiante = '';
	protected $cOpcional2Estudiante = '';
	protected $nOpcional4Estudiante = 0;
	protected $cOpcional6Estudiante = '';
	protected $cEspecialidadCobra = '';
	protected $cEspecialidadBancoSangre = '';
	protected $cEspLaboratorioHexalis = '';
	protected $cCupsPruebaEsfuerzo = '';
	protected $cCupsMipres='';
	protected $cCupsGasesArteriales='';
	protected $cEspecialidadGasesArteriales='';
	protected $cTipoEntidadNopos='';
	protected $cDiagnosticoPrincipal='';
	protected $cTipoDocumentoInventario='SI';
	protected $cNroDocumentoInventario='';
	protected $cMedHipertensionPulmonar='';
	protected $nEstadoHemocomponente = 0;
	protected $nEdadMenor = 0;
	protected $nConsNopos = 0;
	protected $nConsCupsEstudiante = 0;
	protected $nConEstudiante = 0;
	protected $nConsFormula = 0;
	protected $nConsFormulaMed=0;
	protected $nConsAntibiotico=0;
	protected $nEstadoFormula=0;
	protected $nEstadoInicialFormula=0;
	protected $nConEvo = 0;
	protected $nConCit = 0;
	protected $nConFormula = 0;
	protected $nConMipres = 0;
	protected $nNoSuspendidos=0;
	protected $nFechaFinAntibiotico=0;
	protected $nFechaIngresoUsuario=0;
	protected $nValidaFechaIngreso=0;
	protected $aobjOblOM = [];
	protected $aInterconsulta = [];
	protected $aIngreso = [];
	protected $bReqAval = false;
	protected $aPrioridadInterconsultaOm = [];
	protected $aEVOLUC = [];
	protected $aEVOLUCO = [];
	protected $aRIAORD = [];
	protected $aRIADET = [];
	protected $aINTCON = [];
	protected $aORDPRO = [];
	protected $aRIANUTR = [];
	protected $aRIANUTRD = [];
	protected $aHISCLI = [];
	protected $aRIAFARDO = [];
	protected $aAUTANX = [];
	protected $aAUTAND = [];
	protected $aBANSAC = [];
	protected $aBANSAO = [];
	protected $aCUPELJUS = [];
	protected $aNPSMPEP = [];
	protected $aRIAFARM = [];
	protected $aFORMED = [];
	protected $aRIAFARD = [];
	protected $aRIAFARI = [];
	protected $aRIAFARDA = [];
	protected $aENADMMDT = [];
	protected $aENADMMD = [];
	protected $aMEDCONT = [];
	protected $aUSOANT = [];
	protected $aCupsCoagulacion = [];
	protected $cSL;


	protected $aError = [
				'Mensaje' => '',
				'Objeto' => '',
				'Valido' => true,
			];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->cSL = chr(13);
		$this->bReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->nFechaIngresoUsuario=intval(trim($ltAhora->format('Ymd')));
		$this->nValidaFechaIngreso=intval(trim($this->oDb->obtenerTabmae1('OP1TMA', 'FORMEDIC', "CL1TMA='INDFECOM' AND ESTTMA=''", null, 0)));
		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'CUPSINTC', 'ESTTMA'=>' ']);
		$this->cCupsInterconsulta = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
		$this->cCupsInterconsulta = empty(trim($this->cCupsInterconsulta))?'890402':$this->cCupsInterconsulta;

		$loTabmaeEsp = $this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCORD', 'ESTTMA'=>'']);
		$this->cCupsGlucometria = trim(AplicacionFunciones::getValue($loTabmaeEsp, 'DE2TMA', ''));

		$loTabmaeCoagulacion = $this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCCOA', 'ESTTMA'=>'']);
		$lcCupsCoagulacion=trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loTabmaeCoagulacion, 'DE2TMA', ''))));
		$lcCupsCoagulacion=trim(str_replace(' ', '', $lcCupsCoagulacion));
		$this->aCupsCoagulacion = explode(',',$lcCupsCoagulacion);

		$loTabmaeEsp = $this->oDb->ObtenerTabMae('DE2TMA', 'AGFASO', ['CL1TMA'=>'3', 'ESTTMA'=>'']);
		$this->cEspecialidadCobra = trim(AplicacionFunciones::getValue($loTabmaeEsp, 'DE2TMA', ''));

		$loTabmaeEdad = $this->oDb->obtenerTabmae('DE2TMA', 'EPICRIS', ['CL1TMA'=>'EDADMEN', 'ESTTMA'=>'']);
		$this->nEdadMenor = intval(trim(AplicacionFunciones::getValue($loTabmaeEdad, 'DE2TMA', '')));

		$loTabmaeEspHemocomponente = $this->oDb->obtenerTabmae('DE2TMA', 'BANSAN', ['CL1TMA'=>'ESPBANC', 'ESTTMA'=>'']);
		$this->cEspecialidadBancoSangre = trim(AplicacionFunciones::getValue($loTabmaeEspHemocomponente, 'DE2TMA', ''));

		$this->nEstadoHemocomponente=$this->oDb->obtenerTabmae1('OP3TMA', 'FORMEDIC', "CL1TMA='ESTHEMOC' AND ESTTMA=''", null, 0);
		$this->cCupsPruebaEsfuerzo=trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='CUPMNU' AND ESTTMA=''", null, ''));

		$this->cEspLaboratorioHexalis=trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='ESPLAB' AND ESTTMA=''", null, ''));

		$loTabmaeMipres=$this->oDb->ObtenerTabMae('DE2TMA', 'NOPOS', ['CL1TMA'=>'CONSULTA', 'CL2TMA'=>'CUPXCNT', 'ESTTMA'=>'']);
		$this->cCupsMipres=trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loTabmaeMipres, 'DE2TMA', ''))));
		$this->cCupsMipres=trim(str_replace(' ', '', $this->cCupsMipres));
		$this->cCupsGasesArteriales=trim($this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='RAPID' AND CL3TMA='CUPS' AND CL4TMA='GASES' AND ESTTMA=''", null, ''));
		$this->cEspecialidadGasesArteriales=trim($this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='RAPID' AND CL3TMA='GASES' AND CL4TMA='ESPRLZ' AND ESTTMA=''", null, ''));
	}

	public function consultaParametrosEntidad($tnIngreso=0, $tcCodigoPlan='')
	{
		$aParametros=[];
		$loNoPosFunciones=new NoPosFunciones();
		$laEntidadNopos=$loNoPosFunciones->entidadMipres($tcCodigoPlan);
		$this->cTipoEntidadNopos=substr($laEntidadNopos, 0, 1);
		$lcTipoEntidadMipres=substr($laEntidadNopos, 2, 1);
		$llObligarMipres=$loNoPosFunciones->obligarMipres();
		$lcPacienteExcluido=$loNoPosFunciones->PacienteExcluidoMipres($tnIngreso);

		$aParametros=[
				'tiponopos'=>$this->cTipoEntidadNopos,
				'tipomipres'=>$lcTipoEntidadMipres,
				'obligarmipres'=>$llObligarMipres,
				'pacienteexcluido'=>$lcPacienteExcluido,
			];

		return $aParametros;
	}

	public function consultaParametrosCups()
	{
		$aParametros = [];
		$lcEspPortatil = $lcTextoPortatil = $lcEspNoTraePlan = $lcCupsGlucometria = $lcCupsInterConsulta = '';
		$lcCupsFisioterapia = $lcEdadMenor = '';
		$lnConsecLaboratorio=0;

		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'FORMEDIC\' AND CL1TMA=\'PARAMRX\' AND ESTTMA=\' \'')
				->orderBy('CL2TMA')
				->getAll('array');

			if (is_array($laParametros) && count($laParametros)>0){
				foreach ($laParametros as $laDatos){
					switch ($laDatos['CODIGO']) {
						case '01':
							$lcEspPortatil = $laDatos['DESCRIPCION'];
							break;
						case '02':
							$lcTextoPortatil = $laDatos['DESCRIPCION'];
							break;
						case '03':
							$lcEspNoTraePlan = $laDatos['DESCRIPCION'];
							break;
						case '04':
							$lnConsecLaboratorio = intval(trim($laDatos['DESCRIPCION']));
							break;
					}
				}
			}

			$loTabmaeEsp = $this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCORD', 'ESTTMA'=>'']);
			$lcCupsGlucometria = trim(AplicacionFunciones::getValue($loTabmaeEsp, 'DE2TMA', ''));

			$loTabmaeInt = $this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'INTERC', 'ESTTMA'=>'']);
			$lcCupsInterConsulta = trim(AplicacionFunciones::getValue($loTabmaeInt, 'DE2TMA', ''));

			$loTabmaeEdad = $this->oDb->obtenerTabmae('DE2TMA', 'EPICRIS', ['CL1TMA'=>'EDADMEN', 'ESTTMA'=>'']);
			$lcEdadMenor = trim(AplicacionFunciones::getValue($loTabmaeEdad, 'DE2TMA', ''));

			$loTabmaeFisio = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'FISIOT', 'ESTTMA'=>'']);
			$lcCupsFisioterapia = trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loTabmaeFisio, 'DE2TMA', ''))));
			$lcCupsFisioterapia = trim(str_replace(' ', '', $lcCupsFisioterapia));
			$laCupsFisioterapia = explode(',', $lcCupsFisioterapia);

			$loTabmaeDiasProc = $this->oDb->obtenerTabmae('DE2TMA', 'BANSAN', ['CL1TMA'=>'DIAPRO', 'ESTTMA'=>'']);
			$lcDiasProcedimiento = trim(AplicacionFunciones::getValue($loTabmaeDiasProc, 'DE2TMA', ''));

			$loTabmaeNeumo = $this->oDb->ObtenerTabMae('DE2TMA', 'VALCUP', ['CL1TMA'=>'1', 'ESTTMA'=>'']);
			$lcCupsNeumo = trim(AplicacionFunciones::getValue($loTabmaeNeumo, 'DE2TMA', ''));
			$laCupsNeumo = explode(',', $lcCupsNeumo);
			$lcCupsMedicinaNuclear=$this->oDb->obtenerTabmae1('CL2TMA', 'FORMEDIC', "CL1TMA='CUPMNU' AND ESTTMA=''", null, '');
			$lcCupsNoInvasivos=trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='CUPMNU' AND ESTTMA=''", null, ''));
			$lcTipoMipres=trim($this->oDb->obtenerTabmae1('DE2TMA', 'NOPOS', "CL1TMA='MIPRES' AND CL2TMA='REGISTRO' AND ESTTMA=''", null, ''));
			$lcDosisOxigeno=trim($this->oDb->obtenerTabmae1('de2tma', 'FORMEDIC', "CL1TMA='OXIGENO' AND CL2TMA='DMINMAX' AND ESTTMA=''", null, ''));

			$aParametros=[
				'espportatil'=>$lcEspPortatil,
				'textoportatil'=>$lcTextoPortatil,
				'espnotraeplan'=>$lcEspNoTraePlan,
				'cupsglucometria'=>$lcCupsGlucometria,
				'cupsinterconsulta'=>$lcCupsInterConsulta,
				'cupsfisioterapia'=>$laCupsFisioterapia,
				'edadmenor'=>$lcEdadMenor,
				'esphemocomponente'=>$this->cEspecialidadBancoSangre,
				'diasprocedimiento'=>$lcDiasProcedimiento,
				'excepcionneumo'=>$laCupsNeumo,
				'cupsmedicinanuclear'=>$lcCupsMedicinaNuclear,
				'cupsnoinvasivos'=>$lcCupsNoInvasivos,
				'tiporegistromipres'=>$lcTipoMipres,
				'procedimientosmipres'=>$this->cCupsMipres,
				'conseclabAdicional'=>$lnConsecLaboratorio,
				'dosisminimaoxigeno'=>explode('~', $lcDosisOxigeno)[0],
				'dosismaximaoxigeno'=>explode('~', $lcDosisOxigeno)[1],
			];
		}
		unset($laParametros);
		return $aParametros;
	}

	public function consultaPacienteUrgencias($tcViaIngreso='', $tcSeccionActual='')
	{
		$lnValidarUrgencias = 0;
		$lnViaIngreso = intval($tcViaIngreso);
		$lcSeccionActual = $tcSeccionActual;
		$laParametros = [];

		if ($lnViaIngreso==1){ 	$lnValidarUrgencias = 1; }

		if ($lnValidarUrgencias==0){
			$laParametros = $this->oDb
				->select('trim(SECHAB) SECCIONURG')
				->from('FACHAB')
				->where('IDDHAB', '=', '0')
				->where('LIQHAB', '=', 'U')
				->where('SECHAB', '=', $lcSeccionActual)
				->groupBy('SECHAB')
				->getAll('array');
			if (is_array($laParametros) && count($laParametros)>0){
				$lnValidarUrgencias = 1;
			}
		}

		if ($lnValidarUrgencias==0 && $lnViaIngreso==5){
			$loTabmaeUrg = $this->oDb->ObtenerTabMae('DE2TMA', 'EVOLUC', ['CL1TMA'=>'SECURG', 'ESTTMA'=>'']);
			$lcTransitorioUrgencias = trim(AplicacionFunciones::getValue($loTabmaeUrg, 'DE2TMA', ''));
			$lcTransitorioUrgencias = str_replace('\'', '', trim($lcTransitorioUrgencias));
			if ($lcSeccionActual==$lcTransitorioUrgencias){ $lnValidarUrgencias = 1; }

			if ($lnValidarUrgencias==0){
				$lcUnidad = $this->oDb->obtenerTabmae1('DE2TMA', 'EVOLUC', "CL1TMA='SERIMAG' AND CL3TMA='$lcSeccionActual'", null, '');
				if (!empty($lcUnidad)) {
					$lnValidarUrgencias = 5;
				}
			}
		}

		if ($lnValidarUrgencias==1){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'EVOLUC')
				->where('CL1TMA', '=', 'SERIMAG')
				->where('CL3TMA', '=', '')
				->where('ESTTMA', '=', '')
				->orderBy('DE2TMA')
				->getAll('array');
		}else{
			$laParametros = $this->oDb
				->select('trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'EVOLUC')
				->where('CL1TMA', '=', 'SERIMAG')
				->where("(CL2TMA='2' OR CL3TMA='$lcSeccionActual')")
				->where('ESTTMA', '=', '')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultaAlertaMalNutricion($tnIngreso=0)
	{
		$laDatosAlerta=[];
		$lcSL = PHP_EOL;
		$lnValorAlerta = intval($this->oDb->obtenerTabmae1('OP3TMA', 'NUTRICIO', "CL1TMA='TAMIZAJE' AND CL2TMA='9902' AND ESTTMA=''", null, 0));

		if ($lnValorAlerta>0){
			$laDatosNutricion = $this->oDb
				->select('OP3NUT OPCIONAL3')
				->from('infnut')
				->where('ingnut', '=', $tnIngreso)
				->where('ccinut', '=', 0)
				->where('indnut', '=', 200)
				->where('cupnut', '=', 'TAM_NUTR')
				->orderBy('fecnut DESC, hornut DESC')
				->get('array');
			if ($this->oDb->numRows()>0){
				if (intval($laDatosNutricion['OPCIONAL3'])>=$lnValorAlerta){
					$lcLstCupNut=$this->oDb->obtenerTabmae('DE2TMA', 'NUTRICIO', "CL1TMA='TAMIZAJE' AND CL2TMA='990202' AND ESTTMA=''", null, "'890406'");
					$lcLstCupNut=trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($lcLstCupNut, 'DE2TMA', ''))));

					$laDatosOrden = $this->oDb
						->select('NINORD INGRESO')
						->from('RIAORD')
						->where('NINORD', '=', $tnIngreso)
						->in('COAORD', [$lcLstCupNut])
						->getAll('array');
					if($this->oDb->numRows()==0){
						$lcTextoNutricion=$this->oDb->obtenerTabmae1('de2tma || op5tma', 'NUTRICIO', "CL1TMA='TAMIZAJE' AND CL2TMA='990201' AND ESTTMA=''", null, '');
						$laMensaje = explode('|', $lcTextoNutricion);
						$laDatosAlerta=[
							'TITULO'=>$laMensaje[0],
							'DESCRIPCION'=>$laMensaje[1],
						];
					}
				}
			}
		}
		unset($laDatosNutricion);
		unset($laDatosOrden);
		return $laDatosAlerta;
	}
	
	public function verificaRegistroNutricion()
	{
		$llVerificar=true;
		$laDatosNutricion = $this->oDb
			->select('CONNTR CONSECUTIVO')
			->from('RIANUTR')
			->where('TIDNTR', '=', $this->aIngreso['cTipId'])
			->where('NIDNTR', '=', $this->aIngreso['nNumId'])
			->where('NINNTR', '=', $this->aIngreso['nIngreso'])
			->where('CONNTR', '=', $this->nConEvo)
			->get('array');
		if ($this->oDb->numRows()>0){
			$llVerificar=false;
		}
		unset($laDatosNutricion);
		return $llVerificar;
	}
	
	public function verificaDetalleEnfermeria($taDatosMedicamento=[])
	{
		$lnConsAdministracion=$taDatosMedicamento['consecutivonuevo'];
		$lnNroDosis=$taDatosMedicamento['numerodosis'];
		$lcCodmedicamento=$taDatosMedicamento['medicamento'];
		$tcViaAdministracion=$taDatosMedicamento['unidadvia'];
		$tcFormulaVia=$taDatosMedicamento['formulaporvia'];
		$llVerificar=true;
		
		if ($lcCodmedicamento!=''){
			if (!empty($tcFormulaVia)){
				$this->oDb->where('VIAADM', '=', $tcViaAdministracion);
			}
			$laDatosNutricion = $this->oDb
				->select('INGADM INGRESO')
				->from('ENADMMDT')
				->where('INGADM', '=', $this->aIngreso['nIngreso'])
				->where('CTUADM', '=', $this->nConsFormulaMed)
				->where('CEVADM', '=', $this->nConEvo)
				->where('CCOADM', '=', $lnConsAdministracion)
				->where('NDOADM', '=', $lnNroDosis)
				->where('MEDADM', '=', $lcCodmedicamento)
				->get('array');
				$llVerificar=$this->oDb->numRows()>0 ? false : true;
		}	
		unset($laDatosNutricion);
		return $llVerificar;
	}
	
	public function verificaRegistroFormula($tcCodigoMedicamento='',$tcFormulaVia='',$tcViaMedicamento='')
	{
		$llVerificar=true;
		if ($tcCodigoMedicamento!=''){
			
			if (!empty($tcFormulaVia)){
				$this->oDb->where('VIAFRD', '=', $tcViaMedicamento);
			}	
			
			$laDatosFormula = $this->oDb
			->select('NINFRD INGRESO')
			->from('RIAFARD')
			->where('NINFRD', '=', $this->aIngreso['nIngreso'])
			->where('CDNFRD', '=', $this->nConsFormulaMed)
			->where('MEDFRD', '=', $tcCodigoMedicamento)
			->get('array');
			$llVerificar=$this->oDb->numRows()>0 ? false : true;
		}
		unset($laDatosFormula);
		return $llVerificar;
	}

	public function consultaDatosEnfermeria($tnIngreso=0)
	{
		$lcEvolucionEnfermeria='';
		$laDatosEnfermeria = $this->oDb
			->select('TRIM(DESEVL) DESCRIPCION')
			->from('EVOLUCO')
			->where('NINEVL', '=', $tnIngreso)
			->where('CONEVL=(SELECT MAX(M.CONEVL) FROM EVOLUCO M WHERE M.NINEVL='.$tnIngreso.' AND (M.CNLEVL>1750 AND M.CNLEVL<1800))')
			->between('CNLEVL',1751,1799)
			->orderBy('CNLEVL')
			->getAll('array');
		if (is_array($laDatosEnfermeria) && count($laDatosEnfermeria)>0){
			foreach ($laDatosEnfermeria as $laDatos){
				$lcEvolucionEnfermeria.=$laDatos['DESCRIPCION'];
			}
		}
		unset($laDatosEnfermeria);
		return $lcEvolucionEnfermeria;
	}

	public function consultaProcedimientoUrgencias($tcProcedimiento='')
	{
		$lcCupsUrgencias = '';
		$laParametros = $this->oDb
			->select('trim(CL2TMA) CODIGO')
			->from('TABMAE')
			->where('TIPTMA', '=', 'EVOLUC')
			->where('CL1TMA', '=', 'CUPIMUR')
			->where('CL2TMA', '=', $tcProcedimiento)
			->where('CL3TMA', '=', 'D')
			->where('ESTTMA', '=', '')
			->get('array');

		if (is_array($laParametros) && count($laParametros)>0){
			$lcCupsUrgencias = $laParametros['CODIGO'];
		}
		return $lcCupsUrgencias;
	}

	public function consultaHemocomponenteOrdenado($tnIngreso=0, $tcProcedimiento='')
	{
		$laDatosOrdenado = [];
		$lnCantidadRegistros=0;
		$lcDatosOrdenado='';

		$laHemocomponenteOrdenado = $this->oDb
			->select('TRIM(B.DE1TMA) ESTADO_ORDEN')
			->from('RIAORD AS A')
			->leftJoin("TABMAE B", "A.ESTORD=INT(B.CL1TMA) AND B.TIPTMA='ESTPRORD'", null)
			->where('A.NINORD', '=', $tnIngreso)
			->where('A.COAORD', '=', $tcProcedimiento)
			->groupBy('B.DE1TMA')
			->orderBy('B.DE1TMA')
		->getAll('array');
		if (is_array($laHemocomponenteOrdenado) && count($laHemocomponenteOrdenado)>0){
			$lnCantidadRegistros = count($laHemocomponenteOrdenado);

			foreach ($laHemocomponenteOrdenado as $laDatos){
				$lcDatosOrdenado .= '&'.$laDatos['ESTADO_ORDEN'];
			}

			$laDatosOrdenado=[
				'CANTIDAD'=>$lnCantidadRegistros,
				'DESCRIPCION'=>$lcDatosOrdenado,
			];
		}

		return $laDatosOrdenado;
	}

	public function consultaNuclearNoInvasiva($tcProcedimiento='')
	{
		$laDatosNoInvasivos=$lcCupsInvasivos='';

		$laParametros = $this->oDb
			->select('trim(DE2TMA) CUPSINVASIVOS')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FORMEDIC')
			->where('CL1TMA', '=', 'CUPMNU')
			->where('CL2TMA', '=', $tcProcedimiento)
			->where('ESTTMA', '=', '')
			->get('array');

		if (is_array($laParametros) && count($laParametros)>0){
			$lcCupsInvasivos=trim($laParametros['CUPSINVASIVOS']);

			if (!empty($lcCupsInvasivos)){
				$laDatosCups = $this->oDb
				->select('trim(DESCUP) DESCRIPCION, trim(RF5CUP) POSNOPOS, trim(ESPCUP) ESPECIALIDAD')
				->from('RIACUPL11')
				->where(['CODCUP'=>$lcCupsInvasivos,])
				->get("array");
				if (is_array($laDatosCups) && count($laDatosCups)>0){;
					$laDatosNoInvasivos=[
						'CODIGO'=>$lcCupsInvasivos,
						'DESCRIPCION'=>trim($laDatosCups['DESCRIPCION']),
						'CANTIDAD'=>1,
						'POSNOPOS'=>trim($laDatosCups['POSNOPOS']),
						'ESPECIALIDAD'=>trim($laDatosCups['ESPECIALIDAD']),
					];
				}
			}
		}
		return $laDatosNoInvasivos;
	}

	public function consultaCaracteresJustificacion(){
		$loTabmaeCantidad = $this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'CANTJUST', 'ESTTMA'=>'']);
		$lcCantidad= trim(AplicacionFunciones::getValue($loTabmaeCantidad, 'DE2TMA', ''));
		return $lcCantidad;
	}

	public function consultaProcedimientoPos($tcCupsPos=''){
		$lcCupsPos = trim($this->oDb->obtenerTabmae1('CL1TMA', 'CUPSPOS', "CL1TMA='$tcCupsPos' AND ESTTMA=''", null, ''));
		return $lcCupsPos;
	}


	public function consultaLaboratorioCultivo($tcCups=''){
		$lcCultivo='';
		$lcCultivo=trim($this->oDb->obtenerTabmae1('CL2TMA', 'LABORTR', "CL1TMA='LABMCRB' AND CL2TMA='$tcCups' AND ESTTMA=''", null, ''));
		return $lcCultivo;
	}

	public function consultaHemocomponentes($tcCups='', $tcEspHemocomponente=''){
		$lcCupsHemocomponente = '';
		$laParametros = $this->oDb
				->select('(SELECT TRIM(C.CL4TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'BANSAN\' AND C.CL1TMA=\'APLICA\' AND C.CL2TMA=A.CODCUP AND C.ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS TIPO_HEMOCOMPONENTE')
				->select('(SELECT TRIM(C.CL2TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'BANSAN\' AND C.CL1TMA=\'EXCCUPS\' AND C.CL2TMA=A.CODCUP AND C.ESTTMA=\' \'  FETCH FIRST 1 ROWS ONLY) AS EXCEPCION')
				->tabla('RIACUP AS A')
				->where('CODCUP', '=', $tcCups)
				->where('IDDCUP', '=', '0')
				->where('RF1CUP', '=', 'DIAG')
				->where('RF2CUP', '=', 'B.SANG')
				->where('ESPCUP', '=', $tcEspHemocomponente)
				->get('array');

		if (is_array($laParametros) && count($laParametros)>0){
			$lcTipoHemocomponente = $laParametros['TIPO_HEMOCOMPONENTE'];
			$lcExcluido = $laParametros['EXCEPCION'];
			$lcCupsHemocomponente = ($lcTipoHemocomponente!='' && $lcExcluido=='') ? $lcTipoHemocomponente : '';
		}
		return $lcCupsHemocomponente;
	}

	public function obtenerCentroDeCosto($tcProcedimiento, $tcCentroServicio, $tcMedicorealiza='')
	{
		$laCentrosDeCosto=$laWhere=[];
		$lnMedicoRealiza=0;
		$lcValidaGasesArteriales=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GRABCON', "CL1TMA='CUPSME' AND CL2TMA='$tcProcedimiento' AND ESTTMA=''", null, ''));
		$lnMedicoRealiza=(!empty($lcValidaGasesArteriales) || empty($tcMedicorealiza))?0:intval($tcMedicorealiza);

		if (!empty($tcProcedimiento)) $laWhere['A.CODCPC'] = $tcProcedimiento;
		if (!empty($tcCentroServicio)) $laWhere['A.CSECPC'] = $tcCentroServicio;
		if ($lnMedicoRealiza!==0) $laWhere['A.MEDCPC'] = $lnMedicoRealiza;

		$laParametros = $this->oDb
			->select('TRIM(A.CODCPC) PROCEDIMIENTO, TRIM(A.CSECPC) CENTROSERVICIO, TRIM(A.CCSCPC) CENTROCOSTO, TRIM(A.MEDCPC) MEDICO')
			->select('(SELECT TRIM(TABDSC) FROM PRMTAB WHERE TABTIP=\'004\' AND TABCOD=A.CCSCPC FETCH FIRST 1 ROWS ONLY) AS DESCRIPCIONCENTROCOSTO')
			->select('(SELECT TRIM(NOMMED)||\' \'||TRIM(NNOMED) FROM RIARGMN WHERE NIDRGM=A.MEDCPC AND ESTRGM=1 FETCH FIRST 1 ROWS ONLY) AS NOMBREMEDICO')
			->tabla('RIACUPC AS A')
			->where($laWhere)
			->groupBy('A.CODCPC, A.CSECPC, A.CCSCPC, A.MEDCPC')
			->orderBy('A.CODCPC, A.CSECPC, A.CCSCPC, A.MEDCPC')
			->getAll('array');
		if($this->oDb->numRows()>0){
			$laCentrosDeCosto=$laParametros;
		}	
		unset($laParametros);
		return $laCentrosDeCosto;
	}

	public function consultaEspecialidadPediatria($tcProcedimiento='')
	{
		$lcEspecialidadPediatria = '';
		$laParametros = $this->oDb
			->select('trim(DE2TMA) ESPECIALIDAD')
			->from('TABMAE')
			->where('TIPTMA', '=', 'AGFASO')
			->where('CL1TMA', '=', '27')
			->where('CL2TMA', '=', $tcProcedimiento)
			->where('ESTTMA', '=', '')
			->get('array');

		if (is_array($laParametros) && count($laParametros)>0){
			$lcEspecialidadPediatria = $laParametros['ESPECIALIDAD'];
		}
		return $lcEspecialidadPediatria;
	}

	public function consultaAyudaTipoReserva()
	{
		$listaTempAyuda = '';
		$laayudaTipoDiagnostico = $this->oDb
			->select(['trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'BANSAN',
				'CL1TMA' => 'RESERWEB',
			])
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if(is_array($laayudaTipoDiagnostico)){
			foreach ($laayudaTipoDiagnostico as $ayudaTipoDiagnostico){
				$listaTempAyuda .=$ayudaTipoDiagnostico['TABDSC'];
			}
		}
		return $listaTempAyuda;
	}

	public function consultaAyudaRiesgoTransfucional()
	{
		$listaTempAyuda = '';
		$laayudaTipoDiagnostico = $this->oDb
			->select(['trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'BANSAN',
				'CL1TMA' => 'RIESGWEB',
			])
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if(is_array($laayudaTipoDiagnostico)){
			foreach ($laayudaTipoDiagnostico as $ayudaTipoDiagnostico){
				$listaTempAyuda .=$ayudaTipoDiagnostico['TABDSC'];
			}
		}
		return $listaTempAyuda;
	}

	public function consultaParametrosCantidades($tcEspecialidad='')
	{
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'BANSAN')
				->where('CL1TMA', '=', 'CANORDEN')
				->where('CL3TMA', '=', $tcEspecialidad)
				->where('ESTTMA', '=', '')
				->orderBy('CL2TMA')
				->getAll('array');

			if (!is_array($laParametros) || count($laParametros)==0){
				$laParametros = $this->oDb
					->select('trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
					->from('TABMAE')
					->where('TIPTMA', '=', 'BANSAN')
					->where('CL1TMA', '=', 'CANORDEN')
					->where('CL3TMA', '=', '')
					->where('ESTTMA', '=', '')
					->orderBy('CL2TMA')
					->getAll('array');
			}
		}
		return $laParametros;
	}

	public function consultaPlanManejo($tnIngreso=0)
	{
		$laDatosPlan = [];
		$lcDescripcion = '';

		if(isset($this->oDb)){
			$laDatosPlan = $this->oDb
				->select('CONEVL, TRIM(DESEVL) DESCRIPCION')
				->from('EVOLUC')
				->where('NINEVL', '=', $tnIngreso)
				->where('CONEVL=(SELECT MAX(M.conevl) FROM EVOLUC M WHERE M.ninevl='.$tnIngreso.' AND M.cnlevl=999)')
				->between('CNLEVL',1000,1100)
				->orderBy('CNLEVL ASC')
				->getAll('array');
			if (is_array($laDatosPlan)){
				if (count($laDatosPlan)>0){
					foreach ($laDatosPlan as $laDatos){
						$lcDescripcion .= $laDatos['DESCRIPCION'];
					}
				}else{
					$laDatosPlan = $this->oDb
						->select('TRIM(DESCRI) DESCRIPCION')
						->from('RIAHIS15')
						->where('NROING', '=', $tnIngreso)
						->where('INDICE', '=', 30)
						->in('PGMHIS', ['HCPPAL','HCPPALWEB'])
						->orderBy('CONSEC ASC')
						->getAll('array');

					if (is_array($laDatosPlan)){
						if (count($laDatosPlan)>0){
							foreach ($laDatosPlan as $laDatos){
								$lcDescripcion .= $laDatos['DESCRIPCION'];
							}
						}
					}
				}
			}
		}
		unset($laDatosPlan);
		unset($laDatos);
		return $lcDescripcion;
	}

	public function consultaTipoReserva()
	{
		$laParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) AS CODIGO, trim(DE2TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'BANSANR\' AND CL1TMA=\'TIPORES\' AND ESTTMA=\' \'')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultaHemoclasificacion()
	{
		$laParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) AS CODIGO, trim(DE2TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'BANSAN\' AND CL1TMA=\'LISGRSA\' AND ESTTMA=\' \'')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultariesgotransfucional()
	{
		$laParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL2TMA) AS CODIGO, trim(DE2TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'BANSAN\' AND CL1TMA=\'RIESGOS\' AND ESTTMA=\' \'')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultaListaJustificacion($tcProcedimiento='')
	{
		$laParametros = [];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL3TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'BANSAN')
				->where('CL1TMA', '=', 'JUSTIF')
				->where('CL2TMA', '=', $tcProcedimiento)
				->where('ESTTMA', '=', '')
				->getAll('array');
		}
		return $laParametros;
	}

	public function TablaPrioridadInterconsultas()
	{
		if(isset($this->oDb)){
			$laParams = $this->oDb
				->select('trim(CL1TMA) AS CODIGO, trim(DE2TMA) AS DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA=\'INCOPRIO\' AND CL2TMA=\'1\' AND ESTTMA=\' \'')
				->orderBy('DE2TMA')
				->getAll('array');
			if(is_array($laParams)==true){
				$this->aPrioridadInterconsultaOm=$laParams;
			}
		}
		return $this->aPrioridadInterconsultaOm;
	}

	public function listaMetodosOxigeno()
	{
		return (new OrdMedOxigeno())->listaMetodos();
	}

	public function listaMetodosGlucometria()
	{
		if(isset($this->oDb)){
			$laMetodoGlucometria = $this->oDb
				->select('trim(DE1TMA) DESCRIP, substr(CL2TMA, 1, 3) CODIGO, trim(substr(DE2TMA, 1, 50)) OBSERVA, int(OP1TMA) CANTIDAD')
				->from('TABMAE')
				->where('TIPTMA=\'GLCMTR\' AND CL1TMA=\'METODO\' AND ESTTMA=\' \'')
				->orderBy('OP3TMA')
				->getAll('array');
		}
		return $laMetodoGlucometria;
	}

	public function listaCupsGlucometria($tcGrupoCups='')
	{
		$laDatosCupsGlucometria = [];
		$lcCodigoCups = trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='$tcGrupoCups' AND ESTTMA=''", null, ''));

		$laDatosCups = $this->oDb->select('trim(DESCUP) DESCRIPCION, trim(RF5CUP) POSNOPOS, trim(ESPCUP) ESPECIALIDAD,
		trim(CLBCUP) HEXALIS')->from('RIACUPL11')->where(['CODCUP'=>$lcCodigoCups,])->get("array");
		if (is_array($laDatosCups)) if (count($laDatosCups)>0);
		$lcDescripcionCups = isset($laDatosCups['DESCRIPCION']) ? trim($laDatosCups['DESCRIPCION']) : '';
		$lcPosNopos = isset($laDatosCups['POSNOPOS']) ? (trim($laDatosCups['POSNOPOS'])=='NOPB' ? 'N' : 'P') : '';
		$lcCodigoEspecialidad = isset($laDatosCups['ESPECIALIDAD']) ? trim($laDatosCups['ESPECIALIDAD']) : '';
		$lcCodigoHexalis = isset($laDatosCups['HEXALIS']) ? trim($laDatosCups['HEXALIS']) : '';

		$laDatosCupsGlucometria=[
				'CODIGO'=>$lcCodigoCups,
				'DESCRIPCION'=>$lcDescripcionCups,
				'ESPECIALIDAD'=>$lcCodigoEspecialidad,
				'POSNOPOS'=>$lcPosNopos,
				'HEXALIS'=>$lcCodigoHexalis,
			];

		return $laDatosCupsGlucometria;
	}

	public function consultaGlucometriaDia($tnIngreso=0)
	{
		$lnRegistros = 0;
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFechaSistema = $ltAhora->format('Ymd');
		$lnFechaSistema = intval(trim($ltAhora->format('Ymd')));
		$lcFechaSistema = AplicacionFunciones::formatFechaHora('fecha',intval(trim($lcFechaSistema)),'/');
		$laCupsGlucometria = $this->listaCupsGlucometria('GLUCORD');
		$lcCupsGlucometria = $laCupsGlucometria['CODIGO'];

		$lcTabla = 'RIAORD';
		$laWhere = [
				'NINORD'=>$tnIngreso,
				'COAORD'=>$lcCupsGlucometria,
				'FCOORD'=>$lnFechaSistema,
			];
			$laRegistros = $this->oDb->count('*','NUMREG')->from($lcTabla)->where($laWhere)->get('array');
		$lnRegistros = intval(trim($laRegistros['NUMREG']));
		if ($lnRegistros>0){
			$lcMensaje = 'SHoy ya se ha ordenado ' .$lnRegistros .' Glucometría' .($lnRegistros>1 ? 's' : '').' al paciente (' .$lcFechaSistema .')';
		}else{
			$lcMensaje = 'NHoy no se ha ordenado Glucometrías al paciente (' .$lcFechaSistema .')';
		}
		return $lcMensaje;
	}

	public function consultaGlucometrias($tnIngreso=0)
	{
		$laGlucometrias	= [];
		$laCupsGlucometria = $this->listaCupsGlucometria('GLUCORD');
		$lcCupsGlucometria = $laCupsGlucometria['CODIGO'];

		$laWhere = [
				'NINORD'=>$tnIngreso,
				'COAORD'=>$lcCupsGlucometria,
				'ESTORD'=>3,
			];
		$laRegistros = $this->oDb
			->select('A.CCIORD CITA, A.FRLORD FECORDEN, A.HOCORD HORAORDEN')
			->select('A.FERORD FECREGISTRO, A.HRLORD HORAREGISTRO')
			->select('TRIM(A.SCAORD)||\'-\'||TRIM(A.NCAORD) HABITACION')
			->select('A.RMEORD REGMEDORDENA, TRIM(B.NNOMED)||\' \'||TRIM(B.NOMMED) MEDICOORDEN')
			->from('RIAORD AS A')
			->leftJoin('RIARGMN AS B', "TRIM(A.RMEORD)=TRIM(B.REGMED)", null)
			->where($laWhere)
			->orderBy('FERORD DESC, HRLORD DESC')
			->getAll('array');
			foreach ($laRegistros as $laDatos) {
				$laDatos['OBSERVACION'] = '';
				$laOrden = $this->oDb
					->select('DESPRO')
					->from('ORDPRO')
					->where([ 'INGPRO' => $tnIngreso, 'CUPPRO' => $lcCupsGlucometria, 'CORPRO' => $laDatos['CITA'],	])
					->getAll('array');
				if (is_array($laOrden)) {
					foreach ($laOrden as $laItem) {
						$laDatos['OBSERVACION'] .= $laItem['DESPRO'];
					}
				}

				$laDatos['VALORGLUC'] = $laDatos['OBSGLUCOME'] = '';
				$laOrden = $this->oDb
					->select('MAYGLU, MEDGLU, UMEGLU, OBSGLU')
					->from('ENGLUCO')
					->where([ 'INGGLU' => $tnIngreso, 'CNTGLU' => $laDatos['CITA'],	])
					->getAll('array');
				if (is_array($laOrden)) {
					foreach ($laOrden as $laItem) {
						$laDatos['VALORGLUC']=$laItem['MAYGLU'].' '.intval($laItem['MEDGLU']).' '.$laItem['UMEGLU'];
						$laDatos['OBSGLUCOME'] .= $laItem['OBSGLU'];
					}
				}
				$laGlucometrias[] = $laDatos;
			}
		return $laGlucometrias;
	}

	public function consultaMedicamentosSuspendidos($tnIngreso=0)
	{
		$laMedicamentosSuspendidos	= [];
		$laWhere = [
				'NINFMD'=>$tnIngreso,
				'ESTFMD'=>14,
			];
		$laMedicamentosSuspendidos = $this->oDb
			->select('TRIM(A.MEDFMD) MEDICAMENTO, A.DOSFMD CANTIDAD_DOSIS, TRIM(A.DDOFMD) UNIDAD_DOSIS')
			->select('A.FREFMD CANTIDAD_FRECUENCIA, TRIM(A.DFRFMD) UNIDAD_FRECUENCIA')
			->select('A.FIAFMD FECHA_INICIO_ANTIBIOTICO, A.FSAFMD FECHA_SUSPENDE_ANTIBIOTICO')
			->select('A.VIAFMD VIA, TRIM(A.OBSFMD) OBSERVACIONES, A.FECFMD FECHA_SUSPENDIDO, A.HORFMD HORA_SUSPENDIDO')
			->select('TRIM(B.DESDES) DESCRIPCION_MEDICAMENTO')
			->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDDOS\' AND CL2TMA=A.DDOFMD AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESC_UNIDAD_DOSIS')
			->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDFRE\' AND CL2TMA=A.DFRFMD AND CL3TMA=\'F\' AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESC_UNIDAD_FRECUENCIA')
			->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDVAD\' AND CL1TMA=A.VIAFMD AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESCR_VIA')
			->from('FORMED AS A')
			->leftJoin("INVDES B", "TRIM(A.MEDFMD)=TRIM(B.REFDES)", null)
			->where($laWhere)
			->orderBy('FECFMD DESC, HORFMD DESC')
			->getAll('array');
		return $laMedicamentosSuspendidos;
	}

	public function cupsInterconsultaFisioterapia()
	{
		$laEspecialidadesFisioterapia = [];
		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'INTFIS', 'ESTTMA'=>' ']);
		$lcEspFisioterapia = trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''))));
		$laEspecialidades = explode(',', $lcEspFisioterapia);

		foreach($laEspecialidades as $laListadoEspecialidades){
			$laEspecialidadesFisioterapia[]=[
				'CODIGO'=>trim($laListadoEspecialidades),
			];
		}
		return $laEspecialidadesFisioterapia;
	}

	public function justificarmipres($tnIngreso=0, $taCups='', $taMedicamentos='')
	{
		$laDatosMipres=[];
		$lnValorGrabacion=$lnValorJustificacion=0;

		if (is_array($taCups)){
			foreach($taCups as $lcListadoCups){
				$lcCupsConsultar = $lcListadoCups['CODIGO'];
				$lnCantidad = $lcListadoCups['TOTAL'];
				$laCupsMipres=explode(',', $this->cCupsMipres);

				if (in_array($lcCupsConsultar, $laCupsMipres)){
					$laDetalles = $this->oDb
						->select('SUM(QCOEST) SUMAGRAB')
						->from('RIAESTM')
						->where("INGEST=$tnIngreso AND (CUPEST='$lcCupsConsultar' OR ELEEST='$lcCupsConsultar') AND TINEST='400' AND RF5EST='NOPB' AND NPREST='0' AND ESFEST <> 5")
						->get('array');
					$lnValorGrabacion=(isset($laDetalles['SUMAGRAB']) && $laDetalles['SUMAGRAB']!='') ? intval(abs($laDetalles['SUMAGRAB'])):0;

					$laJustificacion = $this->oDb
						->select('trim(P.NPRWCA) NPRWCA, P.FEPWCA, S.CNSWCU, trim(S.CUPWCU) CUPWCU, S.CTOWCU')
						->select('(SELECT NUMNMP FROM NPOSMP WHERE NPRNMP=P.NPRWCA AND INGNMP=P.INGWCA AND CNSNMP=S.CNSWCU AND TICNMP=\'400\' ORDER BY FECNMP DESC, HORNMP DESC FETCH FIRST 1 ROWS ONLY) AS ESTADO')
						->from('WSNPCA P')
						->innerJoin('WSNPCU S', 'P.NPRWCA=S.NPRWCU', null)
						->where("P.INGWCA=$tnIngreso AND P.EPRWCA='4' AND S.CUPWCU='$lcCupsConsultar'")
						->getAll('array');

					foreach($laJustificacion as $laDatosJustificar){
						if ($laDatosJustificar['ESTADO']=='42' || empty($laDatosJustificar['ESTADO'])){
							$lnValorJustificacion+=intval($laDatosJustificar['CTOWCU']);
						}
					}
					$lnCantidad=$lnCantidad + $lnValorGrabacion - $lnValorJustificacion;
				}

				if ($lnCantidad>0){
					$laDatosCups = $this->oDb
					->select('trim(DESCUP) DESCRIPCION')
					->from('RIACUP')
					->where(['CODCUP'=>$lcCupsConsultar,])
					->get("array");
					if (is_array($laDatosCups) && count($laDatosCups)>0){;
						$laDatosMipres[]=[
							'CODIGO'=>$lcCupsConsultar,
							'DESCRIPCION'=>trim($laDatosCups['DESCRIPCION']),
							'CANTIDAD'=>$lnCantidad,
						];
					}
				}
			}
		}

		$lnValorGrabacion=$lnValorJustificacion=0;
		if (is_array($taMedicamentos)){
			foreach($taMedicamentos as $lcListadoMedicamentos){
				$lcMedicamentoConsultar=$lcListadoMedicamentos['CODIGO'];
				$lcDescripcionMedicamento=$lcListadoMedicamentos['DESCRIPCION'];

				$laDetalles = $this->oDb
				->select('SUM(QCOEST) SUMAGRAB')
				->from('RIAESTM38')
				->where("INGEST=$tnIngreso AND ELEEST='$lcMedicamentoConsultar' AND (RF4EST='NOPOS' OR RF5EST='NOPB')")
				->get('array');
				$lnValorGrabacion=(isset($laDetalles['SUMAGRAB']) && $laDetalles['SUMAGRAB']!='') ? intval(abs($laDetalles['SUMAGRAB'])):0;

				$laDetalles = $this->oDb
				->select('SUM(CANJMP) CANTPRESCRITA')
				->from('NPJSMP')
				->where("INGJMP=$tnIngreso AND CCOJMP='$lcMedicamentoConsultar' AND TCNJMP='500' AND ESTJMP='0'")
				->get('array');
				$lnValorJustificacion=(isset($laDetalles['CANTPRESCRITA']) && $laDetalles['CANTPRESCRITA']!='') ? intval(abs($laDetalles['CANTPRESCRITA'])):0;
				if ($lnValorJustificacion<=$lnValorGrabacion){
					$laDatosMipres[]=[
						'CODIGO'=>$lcMedicamentoConsultar,
						'DESCRIPCION'=>$lcDescripcionMedicamento,
						'CANTIDAD'=>0,
					];
				}
			}
		}
		return $laDatosMipres;
	}

	public function interconsultasSinResponder($tnIngreso=0, $tcEspecialidad='')
	{

		if ($tcEspecialidad!=''){
			$laInterconsultas = $this->oDb
				->select('TRIM(B.NNOMED)||\' \'||TRIM(B.NOMMED) NOMBRE_MEDICO')
				->select('trim(A.RMEORD) REGISTRO_MEDICO, A.FRLORD FECHA_SOLICITUD, A.HOCORD HORA_SOLICITUD')
				->from('RIAORD AS A')
				->leftJoin('RIARGMN AS B', "TRIM(A.RMEORD)=TRIM(B.REGMED)", null)
				->where('A.NINORD', '=', $tnIngreso)
				->where('A.CODORD', '=', $tcEspecialidad)
				->where("(A.COAORD LIKE '8904%')")
				->where('A.ESTORD', '=', '8')
				->orderBy('A.FRLORD, A.HOCORD')
			->getAll('array');
		}else{
			$laInterconsultas = $this->oDb
				->select('TRIM(B.DESESP) ESPECIALIDAD')
				->from('RIAORD AS A')
				->leftJoin('RIAESPE AS B', "TRIM(A.CODORD)=TRIM(B.CODESP)", null)
				->where('A.NINORD', '=', $tnIngreso)
				->where("(A.COAORD LIKE '8904%')")
				->where('A.ESTORD', '=', '8')
				->groupBy('B.DESESP')
			->getAll('array');
		}
		return $laInterconsultas;
	}

	public function ultimaFormulaOxigeno($tnIngreso=0)
	{
		return (new OrdMedOxigeno())->ultimaFormula($tnIngreso);
	}

	public function cobrarOxigeno($tcViaIngreso='', $tcSeccion='')
	{
		return (new OrdMedOxigeno())->seDebeCobrar($tcViaIngreso, $tcSeccion);
	}

	public function ObjetosObligatoriosOM($tcTitulo='')
	{
		$laCondiciones = ['TIPTMA'=>'PAROMWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblOM = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy ('OP3TMA')
			->getAll('array');
	}

	public function ObtenerPaquetesCups($tcListaCups=[],$tcGeneroPaciente='',$tnEdadAños=0)
	{
		$laCupsPaquete=$laDatosCentroCosto=[];
		$lcCentroDeCosto='';
		foreach($tcListaCups as $lcListadoCups){
			$lcCupsConsultar=$lcListadoCups['CODIGO'];
			$lcCentroServicio=$lcListadoCups['CENTROSERVICIO']??'';
			$lcMedicoRealiza=$lcListadoCups['MEDICOREALIZA']??'';

			$laParams = $this->oDb
				->select('trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'PAQLAB')
				->where('CL1TMA', '=', $lcCupsConsultar)
				->where('ESTTMA', '=', ' ')
				->orderBy('CL2TMA')
				->getAll('array');
			if (is_array($laParams)){
				if (count($laParams)>0){
					$laCupsExcluyen = $this->oDb->select(trim('DE2TMA'))->from('TABMAE')->where(['TIPTMA'=>'FORMEDIC','CL1TMA'=>'CUPSEXO','CL2TMA'=>$tcGeneroPaciente,])->get("array");
					$lcListaCupsExcluir = trim($laCupsExcluyen['DE2TMA']);
					$laCupsExcluir = explode(',',$lcListaCupsExcluir);

					foreach($laParams as $lcListados){
						$laProcedimientos = explode(',', $lcListados['DESCRIPCION']);
						foreach($laProcedimientos as $laListadoCups){
							$lcEspecialidadCupMenor='';
							if ($laListadoCups!=''){
								$lcCodigoCups = trim($laListadoCups);
								if (!in_array($lcCodigoCups, $laCupsExcluir)){
									$laDatosCups = $this->oDb->select('trim(DESCUP) DESCRIPCION, trim(RF1CUP) REFERENCIA1, trim(RF5CUP) POSNOPOS, trim(ESPCUP) ESPECIALIDAD, trim(CLBCUP) HEXALIS')->from('RIACUPL11')->where(['CODCUP'=>$lcCodigoCups,])->get("array");
									if (is_array($laDatosCups)) if (count($laDatosCups)>0);
									$lcDescripcionCups = isset($laDatosCups['DESCRIPCION'])?trim($laDatosCups['DESCRIPCION']):'';
									$lcPosNopos = isset($laDatosCups['POSNOPOS'])?trim($laDatosCups['POSNOPOS']):'';
									$lcEspecialidad = isset($laDatosCups['ESPECIALIDAD']) ? trim($laDatosCups['ESPECIALIDAD']) : '';
									$lcCodigoHexalis=isset($laDatosCups['HEXALIS'])?trim($laDatosCups['HEXALIS']):'';
									$lcReferencia1=isset($laDatosCups['REFERENCIA1'])?trim($laDatosCups['REFERENCIA1']):'';

									if (!empty($lcDescripcionCups)){
										$laDatosDetalle = $this->oDb->select(trim('DE2TMA'))->from('TABMAE')->where(['TIPTMA'=>$lcCupsConsultar,'CL1TMA'=>$lcCodigoCups,])->getAll("array");
										if (is_array($laDatosDetalle)){
											if (count($laDatosDetalle)>0){
												foreach($laDatosDetalle as $laListadoDetalle){
													$lcLaboratorioCultivo='';
													if (intval($tnEdadAños) < $this->nEdadMenor){
														$lcEspecialidadCupMenor=$this->consultaEspecialidadPediatria($lcCodigoCups);
													}
													$lcEspecialidad=trim($lcEspecialidadCupMenor)!='' ? $lcEspecialidadCupMenor : $lcEspecialidad;
													$lcEnviaAgafa=$this->verificaenviaagfa($lcCodigoCups,$lcEspecialidad);
													$lcNoposSiempre=$this->consultaSiempreNopos($lcCodigoCups);
													$lcJustificacionPos=$this->consultaProcedimientoPos($lcCodigoCups);
													$lcJustificacionPos=$lcPosNopos!='NOPB' ? $this->consultaProcedimientoPos($lcCodigoCups) : '';
													$lcEsHemocomponente=$this->consultaHemocomponentes($lcCodigoCups,$lcEspecialidad);
													$lcLaboratorioCultivo=$this->consultaLaboratorioCultivo($lcCodigoCups);
													$laDatosCentroCosto=$this->obtenerCentroDeCosto($lcCodigoCups,$lcCentroServicio,$lcMedicoRealiza);
													$lcCentroDeCosto=count($laDatosCentroCosto)>0?$laDatosCentroCosto[0]['CENTROCOSTO']:'';

													$laCupsPaquete[]=[
														'CODIGO'=>$lcCodigoCups,
														'DESCRIPCION'=>$lcDescripcionCups,
														'CANTIDAD'=>1,
														'OBSERVACIONES'=>trim($laListadoDetalle['DE2TMA']),
														'REFERENCIA1'=>$lcReferencia1,
														'POSNOPOS'=>$lcPosNopos,
														'ESPECIALIDAD'=>$lcEspecialidad,
														'TIPO'=>'P',
														'ENVIAAGFA'=>$lcEnviaAgafa,
														'SIEMPRENOPOS'=>$lcNoposSiempre,
														'JUSTIFICACIONPOS'=>$lcJustificacionPos,
														'HEMOCOMPONENTE'=>$lcEsHemocomponente,
														'HEXALIS'=>$lcCodigoHexalis,
														'LABESPEC'=>$lcLaboratorioCultivo,
														'CENTROCOSTO'=>$lcCentroDeCosto,
													];
												}
											}else{
												if (intval($tnEdadAños) < $this->nEdadMenor){
													$lcEspecialidadCupMenor=$this->consultaEspecialidadPediatria($lcCodigoCups);
												}
												$lcEspecialidad=trim($lcEspecialidadCupMenor)!='' ? $lcEspecialidadCupMenor : $lcEspecialidad;
												$lcEnviaAgafa = $this->verificaenviaagfa($lcCodigoCups,$lcEspecialidad);
												$lcNoposSiempre = $this->consultaSiempreNopos($lcCodigoCups);
												$lcJustificacionPos=$lcPosNopos!='NOPB' ? $this->consultaProcedimientoPos($lcCodigoCups) : '';
												$lcEsHemocomponente=$this->consultaHemocomponentes($lcCodigoCups,$lcEspecialidad);
												$lcLaboratorioCultivo=$this->consultaLaboratorioCultivo($lcCodigoCups);
												$laDatosCentroCosto=$this->obtenerCentroDeCosto($lcCodigoCups,$lcCentroServicio,$lcMedicoRealiza);
												$lcCentroDeCosto=count($laDatosCentroCosto)>0?$laDatosCentroCosto[0]['CENTROCOSTO']:'';

												$laCupsPaquete[]=[
													'CODIGO'=>$lcCodigoCups,
													'DESCRIPCION'=>$lcDescripcionCups,
													'CANTIDAD'=>1,
													'OBSERVACIONES'=>'',
													'REFERENCIA1'=>$lcReferencia1,
													'POSNOPOS'=>$lcPosNopos,
													'ESPECIALIDAD'=>$lcEspecialidad,
													'TIPO'=>'P',
													'ENVIAAGFA'=>$lcEnviaAgafa,
													'SIEMPRENOPOS'=>$lcNoposSiempre,
													'JUSTIFICACIONPOS'=>$lcJustificacionPos,
													'HEMOCOMPONENTE'=>$lcEsHemocomponente,
													'HEXALIS'=>$lcCodigoHexalis,
													'LABESPEC'=>$lcLaboratorioCultivo,
													'CENTROCOSTO'=>$lcCentroDeCosto,
												];
											}
										}
									}
								}
							}
						}
					}
				}else{
					if (!in_array($lcCupsConsultar, array_column($laCupsPaquete,'CODIGO'))){
						$laDatosCups = $this->oDb
										->select('trim(DESCUP) DESCRIPCION, trim(RF1CUP) REFERENCIA1, trim(RF5CUP) POSNOPOS, trim(ESPCUP) ESPECIALIDAD, trim(CLBCUP) HEXALIS')
										->from('RIACUPL11')
										->where(['CODCUP'=>$lcCupsConsultar,])
										->get("array");
						if (is_array($laDatosCups)) if (count($laDatosCups)>0);
							$lcEspecialidadCupMenor='';
							$lcEspecialidadCup=trim($laDatosCups['ESPECIALIDAD']);
							$lcCodigoHexalis=trim($laDatosCups['HEXALIS']);
							$lcPosNopos=trim($laDatosCups['POSNOPOS']);
							if (intval($tnEdadAños) < $this->nEdadMenor){
								$lcEspecialidadCupMenor=$this->consultaEspecialidadPediatria($lcCupsConsultar);
							}
							$lcEspecialidadCup=trim($lcEspecialidadCupMenor)!='' ? $lcEspecialidadCupMenor : $lcEspecialidadCup;
							$lcEnviaAgafa = $this->verificaenviaagfa($lcCupsConsultar,$lcEspecialidadCup);
							$lcNoposSiempre = $this->consultaSiempreNopos($lcCupsConsultar);
							$lcJustificacionPos=$lcPosNopos!='NOPB' ? $this->consultaProcedimientoPos($lcCupsConsultar) : '';
							$lcEsHemocomponente=$this->consultaHemocomponentes($lcCupsConsultar,$lcEspecialidadCup);
							$lcLaboratorioCultivo=$this->consultaLaboratorioCultivo($lcCupsConsultar);
							$laDatosCentroCosto=$this->obtenerCentroDeCosto($lcCupsConsultar,$lcCentroServicio,$lcMedicoRealiza);

							$laCupsPaquete[]=[
								'CODIGO'=>$lcCupsConsultar,
								'DESCRIPCION'=>trim($laDatosCups['DESCRIPCION']),
								'CANTIDAD'=>1,
								'OBSERVACIONES'=>'',
								'REFERENCIA1'=>trim($laDatosCups['REFERENCIA1']),
								'POSNOPOS'=>$lcPosNopos,
								'ESPECIALIDAD'=>$lcEspecialidadCup,
								'TIPO'=>'',
								'ENVIAAGFA'=>$lcEnviaAgafa,
								'SIEMPRENOPOS'=>$lcNoposSiempre,
								'JUSTIFICACIONPOS'=>$lcJustificacionPos,
								'HEMOCOMPONENTE'=>$lcEsHemocomponente,
								'HEXALIS'=>$lcCodigoHexalis,
								'LABESPEC'=>$lcLaboratorioCultivo,
								'CENTROCOSTO'=>$laDatosCentroCosto,
							];
					}
				}
			}
		}
		unset($laParams);
		unset($laDatosCups);
		unset($laCupsExcluyen);
		return $laCupsPaquete;
	}

	function verificarOM($taDatos=[])
	{
		$this->datosIngreso($taDatos['Ingreso']);
		$this->IniciaDatosIngreso($taDatos['Ingreso']);
		$this->aError = $this->validacion($taDatos['OrdenesMedicas']);
		return $this->aError;
	}

	function datosIngreso($tnIngreso=0)
	{
		$loIngreso=new Ingreso;
		$loIngreso->cargarIngreso($tnIngreso);

		$this->aIngreso = [
			'nIngreso' => $loIngreso->nIngreso,
			'cTipIde' => $loIngreso->oPaciente->aTipoId['ABRV'],
			'cTipId' => $loIngreso->cId,
			'nNumId' => $loIngreso->nId,
			'cNombre' => $loIngreso->oPaciente->getNombreCompleto(),
			'cSexo' => $loIngreso->oPaciente->cSexo,
			'aEdad' => $loIngreso->aEdad,
			'cCodVia' => $loIngreso->cVia,
			'cDesVia' => $loIngreso->cDescVia,
			'nEntidad' => $loIngreso->nEntidad,
			'cPlan' => $loIngreso->cPlan,
			'cPlanDsc' => $loIngreso->obtenerDescripcionPlan(),
			'cSeccion' => $loIngreso->oHabitacion->cSeccion,
			'cHabita' => $loIngreso->oHabitacion->cHabitacion,
			'nIngresoFecha' => $loIngreso->nIngresoFecha,
			'nIngresoHora' => $loIngreso->nIngresoHora,
			'nHistoria' => $loIngreso->oPaciente->nNumHistoria,
			'cPesoUnidad' => (!empty(trim($loIngreso->cTipoPeso)) ? $loIngreso->nPeso . ' ' . $loIngreso->cTipoPeso :''),
		];
	}

	function IniciaDatosIngreso($tnIngreso=0)
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'ORDMEDWEB';
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$loEspecialidad = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(true):[]);
		$this->cEspecialidad = $loEspecialidad=='' ? '' : $loEspecialidad->cId;
		$this->cNombreEspecialidad  = $loEspecialidad=='' ? '' : $loEspecialidad->cNombre;
		$this->cRegMed = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():'');
		$this->cNombreUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getNombreCompleto():'');
		$this->cApellidoUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getApellidosNombres():'');
		$this->cApellidoUsuarioHl7 = (isset($_SESSION[HCW_NAME])?mb_strtoupper($_SESSION[HCW_NAME]->oUsuario->cApellido1 .' ' .$_SESSION[HCW_NAME]->oUsuario->cApellido2 .'^'	.$_SESSION[HCW_NAME]->oUsuario->cNombre1.' ' .$_SESSION[HCW_NAME]->oUsuario->cNombre2):'');
		$this->cChrEnter = chr(13);
	}

	public function validacion($validacionDatos)
	{
		$loFormulacion = new FormulacionParametros();
		$loFormulacion->obtenerParametrosTodos();
		$lcDosisOxigenoParametros=trim($this->oDb->obtenerTabmae1('de2tma', 'FORMEDIC', "CL1TMA='OXIGENO' AND CL2TMA='DMINMAX' AND ESTTMA=''", null, ''));
		$lnDosisMinimaOxigeno=explode('~', $lcDosisOxigenoParametros)[0];
		$lnDosisMaximaOxigeno=explode('~', $lcDosisOxigenoParametros)[1];

		$laRetornar = [
		'Mensaje'=>'',
		'Objeto'=>'',
		'Valido'=>true,
		];
		$lbRevisar = true;

		if ($lbRevisar){
			$laDatosOrdenes = explode('~', $this->oDb->obtenerTabMae1("TRIM(DE2TMA) || '~' || TRIM(OP2TMA)", 'FORMEDIC', "CL1TMA='REPORDM' AND ESTTMA=''", null, ''));
			$lcDescripcionDiferencia=trim($laDatosOrdenes[0]);
			$lnDiferenciaEntreOrdenes=intval($laDatosOrdenes[1]??0);
					
			if ($lnDiferenciaEntreOrdenes>0){
				$laDatosEvolucion = $this->oDb
				->select('FECEVL, HOREVL')
				->from('EVOLUC')
				->where('NINEVL','=', $this->aIngreso['nIngreso'])
				->where('CNLEVL','=', 1)
				->where('USREVL','=', $this->cUsuCre)
				->where('PGMEVL','=', $this->cPrgCre)
				->orderBy('CONEVL DESC')
				->get('array');
				if($this->oDb->numRows()>0){
					$ldFechaActual = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$this->cFecCre . $this->cHorCre, '-', ':', 'T'));
					$ldFechaUltima = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora',$laDatosEvolucion['FECEVL'] . $laDatosEvolucion['HOREVL'], '-', ':', 'T'));
					$lcFechaHoraUltima = AplicacionFunciones::formatFechaHora('fecha', $laDatosEvolucion['FECEVL'], '/') .' '.AplicacionFunciones::formatFechaHora('hora12', $laDatosEvolucion['HOREVL']);
					$lcDescripcionDiferencia=trim(str_replace('<<fechahora>>', $lcFechaHoraUltima, $lcDescripcionDiferencia));
					$loDiff = $ldFechaActual->diff($ldFechaUltima);
					$lnSegundosDiferencias = $loDiff->s + ($loDiff->i * 60) + ($loDiff->h * 3600) + ($loDiff->days * 86400);
					
					if ($lnSegundosDiferencias<$lnDiferenciaEntreOrdenes){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = $lcDescripcionDiferencia;
						$laRetornar['Objeto'] = "ordengrabar";
						$lbRevisar = false;
					}
				}	
			}
		}
	
		if ($lbRevisar){
			if (empty($validacionDatos['CieOrdenMedica'])){
				$laRetornar['Valido'] = false;
				$laRetornar['Mensaje'] = 'NO EXISTE diagnóstico principal.';
				$laRetornar['Objeto'] = "txtCieOrdenMedica";
				$lbRevisar = false;
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['Oxigeno'])){
				$lcNecesitaOxigeno=isset($validacionDatos['Oxigeno']['ordOxiPacNececesita']) ? $validacionDatos['Oxigeno']['ordOxiPacNececesita']:'';
				$lcCupsOxigeno=(isset($validacionDatos['Oxigeno']['idMetodoOxigeno']) ? $validacionDatos['Oxigeno']['idMetodoOxigeno'] : '');
				$lcIdOxigeno=(isset($validacionDatos['Oxigeno']['tipoMetodoOxigeno']) ? $validacionDatos['Oxigeno']['tipoMetodoOxigeno'] : '');
				$lcDosisOxigeno=(isset($validacionDatos['Oxigeno']['dosisOxigeno']) ? $validacionDatos['Oxigeno']['dosisOxigeno'] : '');

				if (empty($lcNecesitaOxigeno)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'NO EXISTE paciente necesita oxígeno.';
					$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
					$lbRevisar = false;
				}

				if ($lcNecesitaOxigeno!='Si' && $lcNecesitaOxigeno!='No'){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Paciente necesita oxígeno NO corresponde.';
					$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
					$lbRevisar = false;
				}

				if ($lcNecesitaOxigeno=='No'){
					if (!empty($lcIdOxigeno) || !empty($lcCupsOxigeno) || !empty($lcDosisOxigeno)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'Se registro Paciente necesita oxígeno NO, existen datos que no corresponden.';
						$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
						$lbRevisar = false;
					}
				}

				if ($lcNecesitaOxigeno=='Si'){
					if ($lcDosisOxigeno<$lnDosisMinimaOxigeno){
						$lcTextoOxigeno='Dosis oxigeno ' .$lcDosisOxigeno .', no puede ser menor a dosis mímima ' .$lnDosisMinimaOxigeno .', revise por favor.';
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = $lcTextoOxigeno;
						$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
						$lbRevisar = false;
					}

					if ($lcDosisOxigeno>$lnDosisMaximaOxigeno){
						$lcTextoOxigeno='Dosis oxigeno ' .$lcDosisOxigeno .', no puede ser mayor a dosis máxima ' .$lnDosisMaximaOxigeno .', revise por favor.';
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = $lcTextoOxigeno;
						$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
						$lbRevisar = false;
					}

					if (empty($lcIdOxigeno) || empty($lcCupsOxigeno) || empty($lcDosisOxigeno)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'Se registro Paciente necesita oxígeno SI, NO existen datos que corresponden.';
						$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
						$lbRevisar = false;
					}

					if (!empty($lcIdOxigeno) && !empty($lcCupsOxigeno)){
						$laErrores = [];
						$lcTablaValida = 'TABMAE';
						$laWhere=['TIPTMA'=>'FORMEDIC','CL1TMA'=>'OXIGENO','CL2TMA'=>'CUPS','CL3TMA'=>$lcIdOxigeno,'OP2TMA'=>$lcCupsOxigeno,];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;
							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el identificador/procedimiento.";
								$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
								$lbRevisar = false;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'Falta registrar identificador/procedimeinto oxígeno.';
						$laRetornar['Objeto'] = "selOrdOxiPacNececesita";
						$lbRevisar = false;
					}

					if (!empty($lcCupsOxigeno)){
						$laErrores = [];
						$lcTablaValida = 'RIACUP';
							$laWhere=['IDDCUP'=>'0','CODCUP'=>$lcCupsOxigeno,];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;
							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el procedimiento/oxígeno en la base de datos.";
								$laRetornar['Objeto'] = "selTipoMetodoOxigeno";
								$lbRevisar = false;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'Falta procedimiento oxígeno.';
						$laRetornar['Objeto'] = "selTipoMetodoOxigeno";
						$lbRevisar = false;
					}
				}
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['Medicamentos']['Medicamentos'])){
				if ($validacionDatos['Medicamentos']['Medicamentos']!=''){
					foreach ($validacionDatos['Medicamentos']['Medicamentos'] as $valMedicamentos){

						$lcCodigoMedicamento=isset($valMedicamentos['CODIGO']) ? $valMedicamentos['CODIGO'] : '';
						$lcDescripcionMedicamento=isset($valMedicamentos['MEDICAMENTO']) ? $valMedicamentos['MEDICAMENTO'] : '';
						$lnDosisCantidad=isset($valMedicamentos['DOSIS']) ? $valMedicamentos['DOSIS'] : 0;
						$lcDosisCodigoUnidad=isset($valMedicamentos['CODUNIDADDOSIS']) ? $valMedicamentos['CODUNIDADDOSIS'] : '';
						$lnFrecuenciaCantidad=isset($valMedicamentos['FRECUENCIA']) ? intval($valMedicamentos['FRECUENCIA']) : 0;
						$lcFrecuenciaCodigoUnidad=isset($valMedicamentos['CODUNIDADFRECUENCIA']) ? $valMedicamentos['CODUNIDADFRECUENCIA'] : '';
						$lcViaCodigo=isset($valMedicamentos['VIA']) ? $valMedicamentos['VIA'] : '';
						$lcControlado=isset($valMedicamentos['CONTROLADO']) ? $valMedicamentos['CONTROLADO'] : '';
						$lnControladoCantidad=isset($valMedicamentos['CONTROLADOCANTIDAD']) ? intval($valMedicamentos['CONTROLADOCANTIDAD']) : 0;
						$lcControladoDiagnostico=isset($valMedicamentos['CONTROLADOCIE']) ? $valMedicamentos['CONTROLADOCIE'] : '';
						$lcEstadoMedicamento=isset($valMedicamentos['ESTDET']) ? $valMedicamentos['ESTDET'] : '';
						$lcGrupoFarmaceutico=isset($valMedicamentos['GRUPOCODFARMACEUTICO']) ? trim($valMedicamentos['GRUPOCODFARMACEUTICO']) : '';
						$lcHabilitado=isset($valMedicamentos['HABILITADO']) ? $valMedicamentos['HABILITADO'] : '';
						$lnSeFormula=isset($valMedicamentos['SEFORMULA']) ? intval($valMedicamentos['SEFORMULA']) : 0;
						$lnInmediato=isset($valMedicamentos['INMEDIATO']) ? intval($valMedicamentos['INMEDIATO']) : 0;
						$lnSuspender=isset($valMedicamentos['SUSPENDER']) ? intval($valMedicamentos['SUSPENDER']) : 0;
						$llEsAntibiotico=isset($valMedicamentos['ESANTIBIOTICO']) ? $valMedicamentos['ESANTIBIOTICO'] : false;
						$llEsAntibiotico=$llEsAntibiotico=='true' ? true : false;
						$lnDiasAntibiotico=isset($valMedicamentos['DUSOANTIBIOTICO']) ? intval($valMedicamentos['DUSOANTIBIOTICO']) : 0;
						$lcEsConciliacion=isset($valMedicamentos['CONCILIACION']) ? trim($valMedicamentos['CONCILIACION']): '';
						$lnInmediato=(!empty($lcEsConciliacion) && $lnSeFormula>0)?1:$lnInmediato;

						if ($lbRevisar && empty($lcCodigoMedicamento)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "NO existe código medicamento";
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}
						$laResultadoMedicamento = $loFormulacion->EstadoMedicamento($lcCodigoMedicamento);

						if (isset($laResultadoMedicamento['ESTADO'])){
							if ($lbRevisar && ($lnSeFormula>0 || $lnInmediato>0) && !empty($laResultadoMedicamento['ESTADO'])){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Medicamento inactivo, no puede formularse, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}else{
							if ($lbRevisar){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO existe medicamento, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && $lnDosisCantidad<=0 && $lnSeFormula>0){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Medicamento sin dosis, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && empty($lcDosisCodigoUnidad)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Unidad dosis invalida, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && !empty($lcDosisCodigoUnidad)){
							$lcDetalleUnidadDosis=$loFormulacion->unidadDosis($lcDosisCodigoUnidad);
							if (empty($lcDetalleUnidadDosis)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Unidad dosis no existe, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && $lnFrecuenciaCantidad<=0){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Medicamento sin frecuencia, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && ($lnSeFormula>0 || $lnInmediato>0 || $lnSuspender>0) && empty($lcFrecuenciaCodigoUnidad)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Unidad frecuencia invalida, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && !empty($lcFrecuenciaCodigoUnidad)){
							$lcDetalleUnidadFrecuencia=$loFormulacion->Frecuencia($lcFrecuenciaCodigoUnidad);
							if (empty($lcDetalleUnidadFrecuencia)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Unidad frecuencia no existe, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && empty($lcViaCodigo)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Unidad vía invalida, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && !empty($lcViaCodigo && $lnSeFormula>0)){
							$lcDetalleUnidadVia=$loFormulacion->viaAdmin($lcViaCodigo);
							if (empty($lcDetalleUnidadVia)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Unidad vía no existe, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && !empty($lcDosisCodigoUnidad) && $lnSeFormula>0){
							$lnDosisCodigoUnidad=intval($lcDosisCodigoUnidad);

							$lcTablaValida = 'INVMEDU';
							$laWhere=[
								'CODIGO'=>$lcCodigoMedicamento,
							];
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');

							if ($this->oDb->numRows()>0){
								$lcTablaValida = 'INVMEDU';
								$laWhere=[
									'CODIGO'=>$lcCodigoMedicamento,
									'UNIDAD'=>$lnDosisCodigoUnidad,
								];
								$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
								if ($this->oDb->numRows()<=0){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = "Debe indicar una Unidad de Dosis correcta para el medicamento <br>" .$lcDescripcionMedicamento;
									$laRetornar['Objeto'] = "cMedicamentoOM";
									$lbRevisar = false;
									break;
								}
							}
						}

						if ($lbRevisar && $lnSeFormula>0 && !empty($lcViaCodigo)){
							$loMedicamentoFormula=new MedicamentoFormula;
							$laViasAdministracion=$loMedicamentoFormula->consultaListaViaAdministracion($lcCodigoMedicamento);
							$llValidarVia=false;

							foreach ($laViasAdministracion as $laFiltroVias){
								$lcCodigoVia=$laFiltroVias['CODIGO'];
								if (intval($lcViaCodigo)==intval($lcCodigoVia)){
									$llValidarVia=true;
									break;
								}
							}

							if (!$llValidarVia){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Debe indicar una Vía de Administración correcta para el medicamento <br>" .$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && ($lnSeFormula>0 || $lnInmediato>0) && !empty($lcControlado)){
							if ($lnControladoCantidad<=0){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Cantidad controlado no existe, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}

							if (empty($lcControladoDiagnostico)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Diagnóstico controlado no valido, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}

							if ($lbRevisar && !empty($lcControladoDiagnostico)){
								$loCIE = new Diagnostico($lcControladoDiagnostico, 0);
								$lcDescripcionControlado = $loCIE->getTexto();

								if ($lbRevisar && empty($lcDescripcionControlado)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código diagnóstico controlado.';
									$laRetornar['Objeto'] = "cMedicamentoOM";
									$lbRevisar = false;
									break;
								}
							}
						}

						if ($lbRevisar && empty($lcEstadoMedicamento)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Estado medicamento invalido, " .$lcCodigoMedicamento ."-".$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}

						if ($lbRevisar && !empty($lcEstadoMedicamento)){
							$lcValidaEstado=$this->oDb->obtenerTabmae1('CL1TMA', 'ESTFORM', "CL1TMA='$lcEstadoMedicamento' AND ESTTMA=''", null, '');
							if (empty($lcValidaEstado)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO existe estado medicamento.";
								$laRetornar['Objeto'] = "cMedicamentoOM";
								$lbRevisar = false;
								break;
							}
						}

						if ($lbRevisar && !empty($lcGrupoFarmaceutico)){
							$lcTablaValida = 'INVATTR';
							$laWhere=[
								'REFDES'=>$lcCodigoMedicamento,
							];
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->get('array');
							if ($this->oDb->numRows()>0){
								$laWhere=[
									'REFDES'=>$lcCodigoMedicamento,
									'GRUDES'=>$lcGrupoFarmaceutico,
								];
								$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->get('array');

								if ($this->oDb->numRows()==0){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = "NO existe grupo farmaceutico.";
									$laRetornar['Objeto'] = "cMedicamentoOM";
									$lbRevisar = false;
									break;
								}
							}	
						}
						if ($lbRevisar && ($lnSeFormula>0 || $lnInmediato>0) && $llEsAntibiotico && $lnDiasAntibiotico==0){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "Deben indicarse los días de uso del Antibiótico <br>" .$lcDescripcionMedicamento;
							$laRetornar['Objeto'] = "cMedicamentoOM";
							$lbRevisar = false;
							break;
						}
					}
				}
			}
		}

		if ($lbRevisar){
			if (isset($validacionDatos['Medicamentos']['UsoAntibiotico'])){
				$loMedicamentoFormula=new MedicamentoFormula;

				$lcDiagnosticoInfeccioso=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['DIAGNOSTICOINFECCIOSO']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['DIAGNOSTICOINFECCIOSO'] : '';
				$lcDiagnosticoAnexo=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['DIAGNOSTICOANEXO']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['DIAGNOSTICOANEXO'] : '';
				$lcTipoTratamiento=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['TIPOTRATAMIENTO']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['TIPOTRATAMIENTO'] : '';
				$lcAjustes=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['AJUSTES']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['AJUSTES'] : '';
				$lcObservaciones=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['OBSERVACIONES']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['OBSERVACIONES'] : '';
				$lcOrigen=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['ORIGENMUESTRA']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['ORIGENMUESTRA'] : '';
				$lcResultado=isset($validacionDatos['Medicamentos']['UsoAntibiotico']['RESULTADO']) ? $validacionDatos['Medicamentos']['UsoAntibiotico']['RESULTADO'] : '';

				if ($lbRevisar && empty($lcDiagnosticoInfeccioso)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Diagnóstico infeccioso (Formato Antibiotico) no válido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcDiagnosticoInfeccioso) && empty($loMedicamentoFormula->validarAntibioticos('DIAGNOS',$lcDiagnosticoInfeccioso,'01'))){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Diagnóstico infeccioso (Formato Antibiotico) no existe.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcDiagnosticoAnexo)){
					if (empty($loMedicamentoFormula->validarAntibioticos('DIAGNOS',$lcDiagnosticoAnexo,'02'))){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'Diagnóstico infeccioso anexo (Formato Antibiotico) no válido.';
						$laRetornar['Objeto'] = "cMedicamentoOM";
						$lbRevisar = false;
					}
				}

				if ($lbRevisar && empty($lcTipoTratamiento)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Tipo de tratamiento (Formato Antibiotico) no válido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcTipoTratamiento) && empty($loMedicamentoFormula->validarAntibioticos('TIPOTRA',$lcTipoTratamiento,''))){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Tipo de tratamiento (Formato Antibiotico) no existe.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && empty($lcAjustes)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Ajustes (Formato Antibiotico) no válido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcAjustes) && empty($loMedicamentoFormula->validarAntibioticos('AJUSTES',$lcAjustes,''))){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Ajustes (Formato Antibiotico) no existe.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && empty($lcObservaciones)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Observaciones (Formato Antibiotico) no válido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}


				if ($lbRevisar && empty($lcOrigen)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Origen (Formato Antibiotico) no válido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcOrigen) && empty($loMedicamentoFormula->validarAntibioticos('MUESTRA',$lcOrigen,''))){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Origen (Formato Antibiotico) no existe.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}

				if ($lbRevisar && !empty($lcOrigen) && $lcOrigen!='00000001' && empty($lcResultado)){
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'Resultado (Formato Antibiotico) no valido.';
					$laRetornar['Objeto'] = "cMedicamentoOM";
					$lbRevisar = false;
				}
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['Procedimientos'])){
				foreach ($validacionDatos['Procedimientos'] as $valProcedimientos){
					$lcTipoCups=isset($valProcedimientos['TIPO']) ? $valProcedimientos['TIPO'] : '';
					$lcCodigoCups=isset($valProcedimientos['CODIGO']) ? $valProcedimientos['CODIGO'] : '';
					$lcCodigoEspecialidad=isset($valProcedimientos['ESPECIALIDAD']) ? $valProcedimientos['ESPECIALIDAD'] : '';
					$lcPosNopos=isset($valProcedimientos['POSNOPOS']) ? $valProcedimientos['POSNOPOS'] : '';
					$lcAgfa=isset($valProcedimientos['AGFA']) ? $valProcedimientos['AGFA'] : '';
					$lcObservaciones=isset($valProcedimientos['OBSERVACIONES']) ? $valProcedimientos['OBSERVACIONES'] : '';
					$lcModeloEquipo=isset($valProcedimientos['MODELOEQUIPO']) ? $valProcedimientos['MODELOEQUIPO'] : '';
					$lcTipoAdt=isset($valProcedimientos['TIPOADT']) ? $valProcedimientos['TIPOADT'] : '';
					$lcTipoMensaje=isset($valProcedimientos['TIPOMENSAJE']) ? $valProcedimientos['TIPOMENSAJE'] : '';
					$lcDiagnosticoNopos=isset($valProcedimientos['DIAGNOSTICONP']) ? $valProcedimientos['DIAGNOSTICONP'] : '';
					$lcSolicitadoNopos=isset($valProcedimientos['SOLICITADO']) ? $valProcedimientos['SOLICITADO'] : '';
					$lcObjetivoNopos=isset($valProcedimientos['OBJETIVO']) ? $valProcedimientos['OBJETIVO'] : '';
					$lcRiesgoNopos=isset($valProcedimientos['RIESGO']) ? $valProcedimientos['RIESGO'] : '';
					$lcResumenNopos=isset($valProcedimientos['RESUMEN']) ? $valProcedimientos['RESUMEN'] : '';
					$lcPacienteNopos=isset($valProcedimientos['PACIENTE']) ? $valProcedimientos['PACIENTE'] : '';
					$lcCodigoPosNopos=isset($valProcedimientos['CODIGOPOS']) ? $valProcedimientos['CODIGOPOS'] : '';
					$lcCiePrincipalPos=isset($valProcedimientos['CIEJUSTIFICAPOS']) ? $valProcedimientos['CIEJUSTIFICAPOS'] : '';
					$lcRelacionado1Pos=isset($valProcedimientos['CIEREL1JUSTIFICAPOS']) ? trim($valProcedimientos['CIEREL1JUSTIFICAPOS']) : '';
					$lcRelacionado2Pos=isset($valProcedimientos['CIEREL2JUSTIFICAPOS']) ? trim($valProcedimientos['CIEREL2JUSTIFICAPOS']) : '';
					$lcObservacionesPos=isset($valProcedimientos['OBSJUSTIFICAPOS']) ? trim($valProcedimientos['OBSJUSTIFICAPOS']) : '';
					$lcEsHemocomponente=isset($valProcedimientos['HEMOCOMPONENTE']) ? $valProcedimientos['HEMOCOMPONENTE'] : '';
					$lcEntidadNopos=isset($valProcedimientos['ENTIDADNOPOS']) ? $valProcedimientos['ENTIDADNOPOS'] : '';

					if ($lbRevisar && empty($lcTipoCups)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "NO existe código tipo procedimientos";
						$laRetornar['Objeto'] = "cProcedimientoOM";
						$lbRevisar = false;
						break;
					}

					if ($lbRevisar && empty($lcObservaciones) && $lcTipoCups!='INTER'){
						if ($lcCodigoCups!=$this->cCupsPruebaEsfuerzo){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "NO existe INFORMACIÓN CLINICA a los procedimientos.";
							$laRetornar['Objeto'] = "cProcedimientoOM";
							$lbRevisar = false;
							break;
						}
					}

					if ($lbRevisar && empty($lcCodigoCups)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "NO existe código procedimiento";
						$laRetornar['Objeto'] = "cProcedimientoOM";
						$lbRevisar = false;
						break;
					}else{
						$lcTablaValida = 'RIACUP';
						$laWhere=[
							'IDDCUP'=>'0',
							'CODCUP'=>$lcCodigoCups,
						];

						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)
							->where($laWhere)
							->getAll('array');

							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = 'NO se encontró código procedimiento.';
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcAgfa)){
						if ($lbRevisar && empty($lcModeloEquipo) || empty($lcTipoAdt) || empty($lcTipoMensaje)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = 'Datos insuficientes interfaz con AGFA.';
							$laRetornar['Objeto'] = "cProcedimientoOM";
							$lbRevisar = false;
							break;
						}
					}

					if ($lbRevisar && $lcPosNopos=='P' && $lcTipoCups=='CUPS'){
						$lcEsCupsPos=trim($this->consultaProcedimientoPos($lcCodigoCups));

						if ($lbRevisar && !empty($lcEsCupsPos)){
							if ($lbRevisar && empty($lcCiePrincipalPos)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Diagnóstico principal POS no existe.";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}

							if ($lbRevisar && empty($lcObservacionesPos)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "justificación POS no existe.";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}

							if ($lbRevisar && !empty($lcCiePrincipalPos)){
								$loCIE = new Diagnostico($lcCiePrincipalPos, 0);
								$lcDescripcionCiePrinPos = $loCIE->getTexto();

								if ($lbRevisar && empty($lcDescripcionCiePrinPos)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código diagnóstico principla POS.';
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}
							}

							if ($lbRevisar && !empty($lcRelacionado1Pos)){
								$loCIE = new Diagnostico($lcRelacionado1Pos, 0);
								$lcDescripcionCieRel1Pos = $loCIE->getTexto();

								if ($lbRevisar && empty($lcDescripcionCieRel1Pos)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código diagnóstico POS relacionado 1.';
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}
							}

							if ($lbRevisar && !empty($lcRelacionado2Pos)){
								$loCIE = new Diagnostico($lcRelacionado2Pos, 0);
								$lcDescripcionCieRel2Pos = $loCIE->getTexto();

								if ($lbRevisar && empty($lcDescripcionCieRel2Pos)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código diagnóstico POS relacionado 2.';
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}
							}
						}
					}

					if ($lbRevisar && !empty($lcCodigoCups)){
						$lcvalidaHemocomponente=$this->consultaHemocomponentes($lcCodigoCups,$lcCodigoEspecialidad);
						$laValidaDatosHemocomponente=isset($validacionDatos['Hemocomponente']) ? $validacionDatos['Hemocomponente'] : '';
						if ($lbRevisar && !empty($lcvalidaHemocomponente) && !empty($lcEsHemocomponente)){
							if ($lbRevisar && empty($lcEsHemocomponente)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Procedimiento Hemocomponente no valido";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}

							if ($lbRevisar && !empty($lcEsHemocomponente)){
								if ($lbRevisar && empty($laValidaDatosHemocomponente)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = "Procedimiento Hemocomponente sin datos registrados.";
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}
							}

							if ($lbRevisar && !empty($laValidaDatosHemocomponente) && empty($lcvalidaHemocomponente)){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Procedimiento no es Hemocomponente, revisar datos registrados.";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}
						}
					}

					if ($lbRevisar && !empty($laValidaDatosHemocomponente) && !empty($lcEsHemocomponente)){
						$lnIndicesHemocomponentes=0;
						foreach ($laValidaDatosHemocomponente as $valHemocomponentes){
							$lnIndiceHemocomponente=isset($valHemocomponentes['INDICE']) ? $valHemocomponentes['INDICE'] : 0;
							$lcDescripcionHemocomponente=isset($valHemocomponentes['DESCRIPCION']) ? $valHemocomponentes['DESCRIPCION'] : 0;
							$lcTipoHemocomponente=isset($valHemocomponentes['TIPOJUSTIFICACION']) ? $valHemocomponentes['TIPOJUSTIFICACION'] : 0;
							$lcCodigoHemocomponente=isset($valHemocomponentes['CODIGOJUSTIFICACION']) ? $valHemocomponentes['CODIGOJUSTIFICACION'] : 0;

							if (empty($lbRevisar && $lcDescripcionHemocomponente) &&$lnIndiceHemocomponente!=3){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Descripción Hemocomponente no valido.";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}
							if ($lnIndiceHemocomponente==1){ $lnIndicesHemocomponentes++; }
							if ($lnIndiceHemocomponente==2){ $lnIndicesHemocomponentes++; }

							if ($lnIndiceHemocomponente==3){
								if ($lbRevisar && empty($lcTipoHemocomponente)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = "Tipo Hemocomponente no valido.";
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}

								if ($lbRevisar && !empty($lcTipoHemocomponente)){
									$lcValidaTipoHemo=$this->oDb->obtenerTabmae1('CL2TMA', 'BANSAN', "CL1TMA='JUSTIF' AND CL3TMA='$lcTipoHemocomponente' AND CL4TMA='$lcCodigoHemocomponente' AND ESTTMA=''", null, '');
									if ($lbRevisar && empty($lcValidaTipoHemo)){
										$laRetornar['Valido'] = false;
										$laRetornar['Mensaje'] = "NO existe tipo Hemocomponente.";
										$laRetornar['Objeto'] = "cProcedimientoOM";
										$lbRevisar = false;
										break;
									}
								}

								if ($lbRevisar && empty($lcCodigoHemocomponente)){
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = "Código Hemocomponente no valido.";
									$laRetornar['Objeto'] = "cProcedimientoOM";
									$lbRevisar = false;
									break;
								}

								if ($lbRevisar && !empty($lcCodigoHemocomponente)){
									$lcValidaCodigoHemo=$this->oDb->obtenerTabmae1('CL2TMA', 'BANSAN', "CL1TMA='APLICA' AND CL4TMA='$lcCodigoHemocomponente' AND ESTTMA=''", null, '');
									if ($lbRevisar && empty($lcValidaCodigoHemo)){
										$laRetornar['Valido'] = false;
										$laRetornar['Mensaje'] = "NO existe código Hemocomponente.";
										$laRetornar['Objeto'] = "cProcedimientoOM";
										$lbRevisar = false;
										break;
									}
								}
								$lnIndicesHemocomponentes++;
							}
						}

						if ($lbRevisar){
							if ($lnIndicesHemocomponentes<3){
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "Registros insuficientes para guardar hemocomponentes.";
								$laRetornar['Objeto'] = "cProcedimientoOM";
								$lbRevisar = false;
								break;
							}
						}
					}
				}
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['Dieta'])){
				$lcCodigoDieta = $validacionDatos['Dieta']['tipoDietaMedicas'];

				if (!empty($lcCodigoDieta)){
					$laErrores = [];
					$lcTablaValida = 'TABMAE';
					$laWhere=['TIPTMA'=>'DIESHA','DE1TMA'=>$lcCodigoDieta,];
					try {
						$lbValidar = false;
						$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
						if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;
						if (!$lbValidar) {
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = "NO se encontro el código tipo dieta";
							$laRetornar['Objeto'] = "seltipoDietaMedicas";
							$lbRevisar = false;
						}
					} catch(Exception $loError){
						$laErrores[] = $loError->getMessage();
					} catch(PDOException $loError){
						$laErrores[] = $loError->getMessage();
					}
				}
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['Interconsultas'])){
				foreach ($validacionDatos['Interconsultas'] as $valInterconsultas){
					$lcCodigoEspecialidad=isset($valInterconsultas['ESPECIALIDAD']) ? $valInterconsultas['ESPECIALIDAD'] :'';
					$lcCodigoInterconsulta=isset($valInterconsultas['CODIGOTIPOINTERCONSULTA'])?$valInterconsultas['CODIGOTIPOINTERCONSULTA']:'';
					$lcCodigoPrioridad=isset($valInterconsultas['CODIGOPRIORIDADINTERCONSULTA'])?$valInterconsultas['CODIGOPRIORIDADINTERCONSULTA']:'';
					$lcCodigoCups=isset($valInterconsultas['CODIGO'])?$valInterconsultas['CODIGO']:'';
					$lcObservaciones=isset($valInterconsultas['OBSERVACIONES'])?$valInterconsultas['OBSERVACIONES']:'';

					if ($lbRevisar && empty($lcObservaciones)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "No se registro motivo interconsulta, revisar datos registrados.";
						$lbRevisar = false;
						break;
					}
							
					if (!empty($lcCodigoEspecialidad)){
						$laErrores = [];
						$lcTablaValida = 'RIAESPE';
						$laWhere=['CODESP'=>$lcCodigoEspecialidad,'UBIESP'=>''];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = 'NO se encontró el código especialidad.';
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO EXISTE código especialidad.';
						break;
					}

					if (!empty($lcCodigoInterconsulta)){
						$laErrores = [];
						$lcTablaValida = 'TABMAE';
						$laWhere=['TIPTMA'=>'FORMEDIC','CL1TMA'=>'TIPINTER','CL3TMA'=>$lcCodigoInterconsulta,'ESTTMA'=>''];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = 'NO se encontró el código tipo interconsulta.';
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO EXISTE código tipo interconsulta.';
						break;
					}

					if (!empty($lcCodigoPrioridad)){
						$laErrores = [];
						$lcTablaValida = 'TABMAE';
						$laWhere=['TIPTMA'=>'INCOPRIO','CL1TMA'=>$lcCodigoPrioridad,'ESTTMA'=>''];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = 'NO se encontró el código prioridad interconsulta.';
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO EXISTE código prioridad interconsulta.';
						break;
					}

		 			if (!empty($lcCodigoCups)){
						$laErrores = [];

						if ($lcCodigoCups==$this->cCupsInterconsulta){
							$lcTablaValida = 'RIACUP';
							$laWhere=[
								'IDDCUP'=>'0',
								'CODCUP'=>$this->cCupsInterconsulta,
							];
							try {
								$lbValidar = false;
								$laReg = $this->oDb->tabla($lcTablaValida)
								->where($laWhere)
								->where("(CODCUP LIKE '8904%')")
								->getAll('array');
								if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

								if (!$lbValidar) {
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código procedimiento interconsulta..';
									break;
								}
							} catch(Exception $loError){
								$laErrores[] = $loError->getMessage();
							} catch(PDOException $loError){
								$laErrores[] = $loError->getMessage();
							}
						}else{
							$lcTablaValida = 'RIACUP';
							$laWhere=[
								'IDDCUP'=>'0',
								'CODCUP'=>$lcCodigoCups,
								'ESPCUP'=>$lcCodigoEspecialidad
							];
							try {
								$lbValidar = false;
								$laReg = $this->oDb->tabla($lcTablaValida)
								->where($laWhere)
								->where("(CODCUP LIKE '8904%')")
								->getAll('array');
								if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

								if (!$lbValidar) {
									$laRetornar['Valido'] = false;
									$laRetornar['Mensaje'] = 'NO se encontró el código procedimiento interconsulta.';
									break;
								}
							} catch(Exception $loError){
								$laErrores[] = $loError->getMessage();
							} catch(PDOException $loError){
								$laErrores[] = $loError->getMessage();
							}
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO EXISTE código procedimiento interconsulta.';
						break;
					}
				}
			}
		}

		if ($lbRevisar){
			if (!empty($validacionDatos['CieOrdenMedica'])){
				$lcCodigoCieOrden=trim(explode('~', $validacionDatos['CieOrdenMedica'])[0]);

				$laErrores = [];
				$lcTablaValida = 'RIACIE';
				$laWhere=['ENFRIP'=>$lcCodigoCieOrden];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO se encontró el código de diagnóstico principal.';
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
				
				if ($lbRevisar){
					$lcLetraInicialCie=mb_substr($lcCodigoCieOrden,0,1,'UTF-8');
					$lbValidar = false;
					if (!empty($lcLetraInicialCie)){
						$laReg = $this->oDb
							->select('TRIM(OP5TMA) LETRAS')
							->from('TABMAE')
							->where("TIPTMA='DIAGRIPS' AND CL1TMA='VALCIEP' AND ESTTMA='' AND OP2TMA LIKE '%{$lcLetraInicialCie}%'")
							->orderBy('DE2TMA')
							->get('array');
						$lcValidaLetra=isset($laReg['LETRAS'])?trim($laReg['LETRAS']):'';						
						if (!empty($lcValidaLetra)){
							$laRetornar['Valido'] = false;
							$laRetornar['Mensaje'] = $lcValidaLetra;
							$laRetornar['Objeto'] = "txtCieOrdenMedica";
							$lbRevisar = false;
						}	
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO EXISTE diagnóstico principal en la orden médica.';
						$laRetornar['Objeto'] = "txtCieOrdenMedica";
						$lbRevisar = false;
					}
				}	
			}
		}
		
		if ($lbRevisar){
			$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
			$lnFechaActual = intval(trim($ltAhora->format('Ymd')));

			if ($this->nValidaFechaIngreso==1){
				if ($lnFechaActual<>$this->nFechaIngresoUsuario){
					$lbRevisar = false;
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = 'La fecha de ingreso al módulo de ordenes médicas (' .AplicacionFunciones::formatFechaHora('fecha',intval(trim($this->nFechaIngresoUsuario)),'/') 
											.'), es diferente a la fecha actual ('
											.AplicacionFunciones::formatFechaHora('fecha',intval(trim($lnFechaActual)),'/')
											 .'), por favor revisar la formula de medicamentos nuevamente.';
					$laRetornar['Objeto'] = "FECHADIF";
					$this->nFechaIngresoUsuario=$lnFechaActual;
				}
			}	
		}

		return $laRetornar;
	}

	public function GuardarOrdenesMedicas($taDatosOM=[])
	{
		if (!empty($taDatosOM)){
			$this->organizarDatosOM($taDatosOM);
			$this->guardarDatosOM($taDatosOM);
			$lcDestinosCorreo='';	
			$this->aError['dataOM'] = [
				'nIngreso'		=> $this->aIngreso['nIngreso'],
				'cTipDocPac'	=> $this->aIngreso['cTipIde'],
				'nNumDocPac'	=> $this->aIngreso['nNumId'],
				'cRegMedico'	=> $this->cRegMed,
				'cTipoDocum'	=> '3501',
				'cTipoProgr'	=> 'EV0018E',
				'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
				'nConsecCita'	=> 0,
				'nConsecCons'	=> 0,
				'nConsecEvol'	=> $this->nConEvo,
				'nConsecDoc'	=> 'ORDMEDWEB',
				'cCUP'			=> '',
				'cCodVia'		=> $this->aIngreso['cCodVia'],
				'cSecHab'		=> '',
			];

			if ($taDatosOM['OrdenesMedicas']['Procedimientos']!=''){
				$laDatosEnviaInterc=[];
				foreach($taDatosOM['OrdenesMedicas']['Procedimientos'] as $laDatosInterconsulta){
					$lcTipoRegistro = isset($laDatosInterconsulta['TIPO']) ? $laDatosInterconsulta['TIPO'] : '';
					$lcCodigoProcedimiento=isset($laDatosInterconsulta['CODIGO']) ? $laDatosInterconsulta['CODIGO'] : '';
					if (($lcTipoRegistro=='INTER') || (substr($lcCodigoProcedimiento, 0, 4)=='8904')){	
						$laDatosEnviaInterc[] = [
							'codigoespecialidad'=>isset($laDatosInterconsulta['ESPECIALIDAD'])?$laDatosInterconsulta['ESPECIALIDAD']:'',
							'especialidad'=>isset($laDatosInterconsulta['DESCRIPCION'])?$laDatosInterconsulta['DESCRIPCION']:'',
							'tipoInterconsulta'=>isset($laDatosInterconsulta['DESCRTIPOINTERCONSULTA'])?$laDatosInterconsulta['DESCRTIPOINTERCONSULTA']:'',
							'prioridad'=>isset($laDatosInterconsulta['DESCRPRIORIDADINTERCONSULTA'])?$laDatosInterconsulta['DESCRPRIORIDADINTERCONSULTA']:'',
							'observaciones'=>isset($laDatosInterconsulta['OBSERVACIONES'])?$laDatosInterconsulta['OBSERVACIONES']:'',
						];
					}
				}

				if ($laDatosEnviaInterc!=''){
					$this->destinosEnviarEmailInterconsulta($laDatosEnviaInterc);
				}
			}

			if (!empty($this->cMedHipertensionPulmonar)){
				$lcTipoEmail='MEDHIPUL';
				$lcTipoPlantilla='HIPPULM';
				$lcDestinosCorreo=$this->destinatariosEmail($lcTipoEmail,'');
				if (!empty($lcDestinosCorreo)){
					$this->enviarEmail($lcTipoEmail,$lcDestinosCorreo,$lcTipoPlantilla);
				}
			}
		}
		return $this->aError;
	}

	private function enviarEmail($lcTipoEmail='', $tcDestinos='', $tcPlantilla='')
	{
		$llEnvioMail = $this->oDb->obtenerTabMae1('OP1TMA', 'MAILSETT', "cl1tma='PARAMETR' AND cl2tma='$lcTipoEmail' AND cl3tma='MAIL' AND ESTTMA=''", null, '0')=='1';
		if ($llEnvioMail) {
			$tcDestinosConCopia='';
			$loMailEnviar = new MailEnviar();
			$loAplicacionFunciones = new AplicacionFunciones();
			$loMailEnviar->obtenerPlantilla($lcTipoEmail, $tcPlantilla);
			$lcPlantilla = $loMailEnviar->cPlantilla;
			$laConfigToda = $loMailEnviar->obtenerConfiguracion($lcTipoEmail);
			$laConfig = $laConfigToda['config'];
			$lcFechaPrescribe=$loAplicacionFunciones->formatFechaHora('fechahora12', $this->cFecCre.$this->cHorCre, '/', ':', (' '));

			// Reemplazar datos en la plantilla
			$laDatos = [
				'[[Nombre]]'=>$this->aIngreso['cNombre'],
				'[[Ingreso]]'=>$this->aIngreso['nIngreso'],
				'[[Fechaprescribe]]'=>$lcFechaPrescribe,
				'[[Habitacion]]'=>$this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
				'[[Medicamentos]]'=>$this->cMedHipertensionPulmonar,
			];
			$lcPlantilla = strtr($lcPlantilla, $laDatos);
			$laConfig['tcSubject'] = 'Alerta Hipertensión pulmonar ' .' - Ingreso ' .$this->aIngreso['nIngreso'];

			// Completa la configuración
			$laConfig['tcTO'] = $tcDestinos;
			$laConfig['tcBCC'] = '';
			$laConfig['tcBody'] = $lcPlantilla;

			// Enviar
			$lcResult = $loMailEnviar->enviar($laConfig);
		}
	}
	
	private function destinosEnviarEmailInterconsulta($taDatosInterconsulta)
	{
		if (is_array($taDatosInterconsulta)) {
			if (count($taDatosInterconsulta)>0) {
				foreach($taDatosInterconsulta as $laDatosInterconsulta){
					$lcDestinos = '';
					$lcCodigoEspecialidad = $laDatosInterconsulta['codigoespecialidad'];
					$lcDestinos = $this->destinatariosEmail('INTERCON',$lcCodigoEspecialidad);

					if (!empty($lcDestinos)){
						$laDatosEnviaInterc = [
							'especialidad'=>$laDatosInterconsulta['especialidad'],
							'tipoInteconsulta'=>$laDatosInterconsulta['tipoInterconsulta'],
							'prioridad'=>$laDatosInterconsulta['prioridad'],
							'observaciones'=>$laDatosInterconsulta['observaciones'],
							];
						$this->enviarEmailInterconsulta($laDatosEnviaInterc,$lcDestinos);
					}
				}
			}
		}
	}

	private function enviarEmailInterconsulta($taDatosInterconsulta=[], $tcDestinos='')
	{
		$llEnvioMail = $this->oDb->obtenerTabMae1('OP1TMA', 'MAILSETT', 'cl1tma=\'PARAMETR\' AND cl2tma=\'INTERCON\' AND cl3tma=\'MAIL\' AND ESTTMA=\'\'', null, '0')=='1';
		if ($llEnvioMail) {
			$tcDestinosConCopia='';
			$loMailEnviar = new MailEnviar();

			// Obtener plantilla desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PLANTILL'
			$loMailEnviar->obtenerPlantilla('INTERCON', 'INTERCON');
			$lcPlantilla = $loMailEnviar->cPlantilla;

			// Configuración desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PARAMETR'
			$laConfigToda = $loMailEnviar->obtenerConfiguracion('INTERCON');
			$laConfig = $laConfigToda['config'];

			// Reemplazar datos en la plantilla
			$laDatos = [
				'[[Nombre]]'=>$this->aIngreso['cNombre'],
				'[[Ingreso]]'=>$this->aIngreso['nIngreso'],
				'[[Habitacion]]'=>$this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
				'[[Via]]'=>$this->aIngreso['cDesVia'],
				'[[Interconsulta]]'=>$taDatosInterconsulta['especialidad'],
				'[[Opcion]]'=>$taDatosInterconsulta['tipoInteconsulta'],
				'[[Prioridad]]'=>$taDatosInterconsulta['prioridad'],
				'[[Observaciones]]'=>$taDatosInterconsulta['observaciones'],
				'[[Medico]]'=>$this->cApellidoUsuario,
				'[[Solicitante]]'=>$this->cNombreEspecialidad,
			];
			$lcPlantilla = strtr($lcPlantilla, $laDatos);
			$laConfig['tcSubject'] = 'Solicitud de interconsulta Paciente ' .$this->aIngreso['cNombre'] .' - Ingreso ' .$this->aIngreso['nIngreso'];
			$tcDestinosConCopia=trim($this->oDb->obtenerTabmae1('DE2TMA', 'MAILSETT', "CL1TMA='PARAMETR' AND CL2TMA='INTERCON' AND CL3TMA='BCC'", null, ''));

			// Completa la configuración
			$laConfig['tcTO'] = $tcDestinos;
			$laConfig['tcBCC'] = $tcDestinosConCopia;
			$laConfig['tcBody'] = $lcPlantilla;

			// Enviar
			$lcResult = $loMailEnviar->enviar($laConfig);
		}
	}

	public function destinatariosEmail($tcTipo='', $tcEspecialidad='')
	{
		$lcDestinatarios=$lcModoTest='';
		$lcModoTest=trim($this->oDb->obtenerTabmae1('DE2TMA', 'MAILSETT', "CL1TMA='PARAMETR' AND CL2TMA='$tcTipo' AND CL3TMA='SMTPTEST'", null, ''));

		if ($lcModoTest=='SI'){
			$lcDestinatarios=trim($this->oDb->obtenerTabmae1('DE2TMA', 'MAILSETT', "CL1TMA='PARAMETR' AND CL2TMA='$tcTipo' AND CL3TMA='TESTMAIL'", null, ''));
		}else{
			$lcOpcion = ($tcTipo=='NOTIFFAR' ? 'RECNOTFO' : ($tcTipo=='INTERCON' ? 'RECNOTFI' : ''));
			$lcWhere= (!empty($tcEspecialidad) ? " B.CODRGM='$tcEspecialidad'" : '');

			$laDestinatariosInterconsultas = $this->oDb
				->select('TRIM(A.CL2TMA) USUARIO')
				->select('(SELECT TRIM(C.DE1TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'MAIMED\' AND C.CL2TMA=A.CL3TMA FETCH FIRST 1 ROWS ONLY) AS EMAIL')
				->from('TABMAEL01 AS A')
				->leftJoin('RIARGMN AS B', "TRIM(A.CL3TMA)=TRIM(B.USUARI)", null)
				->where('A.TIPTMA', '=', 'NOTIFICA')
				->where('A.CL1TMA', '=', $lcOpcion)
				->where('A.CL2TMA', '=', 'EMAIL')
				->where('A.CL3TMA', '<>', '')
				->where('A.ESTTMA', '=', '')
				->where('B.ESTRGM', '=', '1')
				->where($lcWhere)
				->getAll('array');
			if (is_array($laDestinatariosInterconsultas)){
				if (count($laDestinatariosInterconsultas)>0){
					foreach($laDestinatariosInterconsultas as $lcDestinos){
						$lcDestinatarios .= (empty(trim($lcDestinatarios)) ? '' : ',') .$lcDestinos['EMAIL'];
					}
				}
			}
			unset($laDestinatariosInterconsultas);
		}
		return $lcDestinatarios;
	}

	function organizarDatosOM($taDatosOM=[])
	{
		$this->cDiagnosticoPrincipal='';
		$laDatosEvo=[
			'ingreso'	=> $this->aIngreso['nIngreso'],
			'seccion'	=> $this->aIngreso['cSeccion'],
			'cama'		=> $this->aIngreso['cHabita'],
			'usuario'	=> $this->cUsuCre,
			'programa'	=> $this->cPrgCre,
			'estado'	=> 3,
		];
		$loEvolucion = new Consecutivos();

		if ($this->bReqAval) {
			$this->nConEstudiante = $loEvolucion->fCalcularConsecutivoEstudiante($this->aIngreso['nIngreso']);
		}else{
			$this->nConEvo = $loEvolucion->obtenerConsecEvolucion($laDatosEvo);
		}
		$laDatosHemocomponente=isset($taDatosOM['OrdenesMedicas']['Hemocomponente']) ? $taDatosOM['OrdenesMedicas']['Hemocomponente'] : '';
		$loAplicacionFunciones = new AplicacionFunciones();
		$this->cFechaHoraEvolucion = $loAplicacionFunciones->formatFechaHora('fechahora12', $this->cFecCre.$this->cHorCre, '/', ':', (' '));
		$this->cDiagnosticoPrincipal=isset($taDatosOM['OrdenesMedicas']['CieOrdenMedica']) ? $taDatosOM['OrdenesMedicas']['CieOrdenMedica'] : '';
		$this->organizarEvolucion();
		$this->organizarOxigeno($taDatosOM['OrdenesMedicas']['Oxigeno']);

		if (isset($taDatosOM['OrdenesMedicas']['Medicamentos']['Medicamentos'])){
			$this->organizarMedicamentos($taDatosOM['OrdenesMedicas']['Medicamentos']['Medicamentos']);
		}

		if (isset($taDatosOM['OrdenesMedicas']['Procedimientos'])){
			if ($taDatosOM['OrdenesMedicas']['Procedimientos']!=''){
				$this->organizarProcedimientos($taDatosOM['OrdenesMedicas']['Procedimientos'],$laDatosHemocomponente);
			}
		}
		$this->organizarDieta($taDatosOM['OrdenesMedicas']['Dieta']);
		$this->organizarEnfermeria($taDatosOM['OrdenesMedicas']['Enfermeria']);

		if ($taDatosOM['OrdenesMedicas']['Mipres']!='' && isset($taDatosOM['OrdenesMedicas']['Mipres']['CupsMipres'])){
			$this->organizarMipres($taDatosOM['OrdenesMedicas']['Mipres']);
		}
	}

	function organizarEvolucion()
	{
		if ($this->bReqAval) {
			$lcTabla = 'REINCA';
			$lcDescripcion = '';
			$lnLinea = 0;
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');
		} else {
			$lcTabla = 'EVOLUC';
			$lnLinea = 1;
			$lcDescripcion = $this->nConEvo .' - ' .$this->cFechaHoraEvolucion .' ' .'Hab: ' .$this->aIngreso['cSeccion'] .'-' .$this->aIngreso['cHabita'];
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');

			$lnLinea = 1000;
			$lcDescripcion = ' Solicitud de ordenes medicas';
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');

			$lnLinea = 1501;
			$lcDescripcion = 'Dr. ' .mb_strtoupper($this->cNombreUsuario) .' - RME: ' .$this->cRegMed .' ' .$this->cNombreEspecialidad ;
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');

			$lnLinea = 900010;
			$lcDescripcion = $this->cEspecialidad;
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');

			if (!empty($this->cDiagnosticoPrincipal)){
				$lnLinea = 900020;
				$lcDescripcion = $this->cDiagnosticoPrincipal;
				$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, '');
			}
		}
	}


	function organizarOxigeno($taDatos=[])
	{
		if(!empty($taDatos)){
			if ($this->bReqAval) {
				$lcTabla = 'REINDE';
				$this->cTipoEstudiante = 'OX';
				$lnLongitud = 500;
				$lnLinea = 1;
				$this->cConsOrdEstudiante = $taDatos['tipoMetodoOxigeno'];
				$this->nConsCupsEstudiante = 0;
				$this->cOpcional1Estudiante = substr($taDatos['ordOxiPacNececesita'], 0, 1);
				$this->cOpcional2Estudiante = $taDatos['idMetodoOxigeno'] ?? '';
				$this->nOpcional4Estudiante = $taDatos['dosisOxigeno'];
				$this->cOpcional6Estudiante = $taDatos['suspende'];
				$lcDescripcion = trim(substr($taDatos['observacionesOxigeno'], 0, 500));
				$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, 0);
			}else{
				/*
				 *	Se manejan los mismos estados de formulación
				 *		11 - Formulado
				 *		13 - Formulado Modificado
				 *		14 - Suspendido
				 *		99 - No Formulado
				*/
				$loOrdMedOxi = new OrdMedOxigeno();
				$loOrdMedOxi->obtenerConfigIng($this->aIngreso);

				$loOrdMedOxi->cOxigeno = isset($taDatos['ordOxiPacNececesita'])?substr($taDatos['ordOxiPacNececesita'],0,1):'';
				$loOrdMedOxi->cCodCup = isset($taDatos['idMetodoOxigeno'])?$taDatos['idMetodoOxigeno']:'';
				$loOrdMedOxi->cRefProc = isset($taDatos['tipoMetodoOxigeno'])?$taDatos['tipoMetodoOxigeno']:'';
				$loOrdMedOxi->nDosis = isset($taDatos['dosisOxigeno'])?$taDatos['dosisOxigeno']:0;
				$loOrdMedOxi->cUnidadDosis = isset($taDatos['unidadDosis'])?$taDatos['unidadDosis']:'';
				if($taDatos['suspende']=='S'){
					$loOrdMedOxi->cOxigeno = 'N';
					$loOrdMedOxi->cCodCup = '';
					$loOrdMedOxi->cRefProc = '';
					$loOrdMedOxi->nDosis = 0;
					$loOrdMedOxi->cUnidadDosis = '';
					$loOrdMedOxi->cObservaciones = '';
					$loOrdMedOxi->cTexto = 'Suspender Oxígeno';
					$loOrdMedOxi->nEstado = 14;
					$loOrdMedOxi->cEstadoGraba = 'S';
				}else{
					if($loOrdMedOxi->cOxigeno=='S'){
						$loOrdMedOxi->cObservaciones = $taDatos['observacionesOxigeno'];
						$loOrdMedOxi->nEstado = $loOrdMedOxi->lPrimeraFormula? 11 : (in_array($loOrdMedOxi->nEstadoAntes,[11,13])? 13: 11);
						$loOrdMedOxi->obtenerDescripcion($taDatos['idMetodoOxigeno']);
						$loOrdMedOxi->cTexto = $loOrdMedOxi->cDescRef.' - Dosis: '.$loOrdMedOxi->nDosis.' '.$loOrdMedOxi->cUnidadDosisDsc. chr(13).$loOrdMedOxi->cObservaciones;
						$loOrdMedOxi->cEstadoGraba = ($loOrdMedOxi->cCodCup==$loOrdMedOxi->cCodCupAntes && $loOrdMedOxi->nFechaAntes==$this->cFecCre)? 'M': ($loOrdMedOxi->lCobrar_Consumos? 'C': 'U');
					}else{
						$loOrdMedOxi->cCodCup = '';
						$loOrdMedOxi->cRefProc = '';
						$loOrdMedOxi->cUnidadDosis = '';
						$loOrdMedOxi->cObservaciones = '';
						$loOrdMedOxi->nEstado = $loOrdMedOxi->lPrimeraFormula? 99: (in_array($loOrdMedOxi->nEstadoAntes,[11,13])? 14: 99);
						$loOrdMedOxi->cTexto = ($loOrdMedOxi->lPrimeraFormula || $loOrdMedOxi->nEstadoAntes==14)? 'No requiere Oxígeno': 'Suspender Oxígeno';
						$loOrdMedOxi->cEstadoGraba = 'N';
					}
				}

				$loOrdMedOxi->lGuardadoEnEvoluc = true;
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
				$loOrdMedOxi->guardarFormulacionOxigeno($laMedico, $laLog, $this->nConEvo, false);
			}
		}
	}

	function organizarDieta($taDatos=[])
	{
		if ($this->bReqAval) {
			if(!empty($taDatos['tipoDietaMedicas']) || !empty($taDatos['observacionDieta']) ){

			}
		}else{
			if(!empty($taDatos['tipoDietaMedicas'])){
				$laDatosProcedimiento=[
					'codigocups' => '',
					'tipodieta' => $taDatos['tipoDietaMedicas'],
				];

				$lcTabla = 'EVOLUCO';
				$lnLinea = 1700;
				$this->nConCit = $this->nConEvo;
				$lcDescripcion = $this->cChrEnter .'DIETA ' .$this->cChrEnter .$taDatos['descripcionDietaMedicas'];
				$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

				$llVerificarNutricion=$this->verificaRegistroNutricion();

				if ($llVerificarNutricion){
					$lcTabla = 'RIANUTR';
					$lnLinea = 0;
					$lcDescripcion = '';
					$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
				}	
			}

			if(!empty($taDatos['observacionDieta'])){
				$lcTabla = 'EVOLUCO';
				$lnLongitud = 220;
				$lnLinea = 1701;
				$this->nConCit = $this->nConEvo;
				$lcDescripcion = $taDatos['observacionDieta'];
				$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

				$lcTabla = 'RIANUDT';
				$lnLinea = 0;
				$lcDescripcion = $taDatos['observacionDieta'];
				$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
			}
		}
	}

	function organizarEnfermeria($taDatos=[])
	{
		if(!empty($taDatos['OrdMedEnfermeria']) || !empty($taDatos['OrdMedDatosOxigeno']) ){
			if ($this->bReqAval) {

			}else{
				$laDatosProcedimiento=[
					'codigocups' => '',
				];
				$lnLongitud = 220;
				$lcTabla = 'EVOLUCO';
				$lnLinea = 1750;
				$this->nConCit = $this->nConEvo;
				$lcDescripcion = 'ORDENES A ENFERMERIA DEL DR(a). ' .mb_strtoupper($this->cNombreUsuario) .' - ' .$this->cFechaHoraEvolucion ;
				$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

				if (!empty($taDatos['OrdMedEnfermeria'])){
					$lcTabla = 'EVOLUCO';
					$lnLinea = 1751;
					$this->nConCit = $this->nConEvo;
					$lcDescripcion = $taDatos['OrdMedEnfermeria'];
					$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
				}

				if (!empty($taDatos['OrdMedDatosOxigeno'])){
					$lcTabla = 'EVOLUCO';
					$lnLinea = 11501;
					$this->nConCit = $this->nConEvo;
					$lcDescripcion = $taDatos['OrdMedDatosOxigeno'];
					$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
				}
				$lcTabla = 'HISCLI';
				$lnLongitud = 220;
				$lnLinea = 1;
				$lcDescripcion = $taDatos['OrdMedEnfermeria'] .(!empty($taDatos['OrdMedDatosOxigeno']) ? ($this->cChrEnter .$taDatos['OrdMedDatosOxigeno']) : '');
				$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, '');
			}
		}
	}

	function organizarMipres($taDatos=[])
	{
		if(!empty($taDatos)){
			$lnConMipres=Consecutivos::fCalcularConsecutivoMipres($this->aIngreso['nIngreso']);

			$laDatosMipres=$taDatos['CupsMipres'];
			$lcRegistroMipres=$taDatos['RegistroMipres'];
			$lcTipoMipres=$taDatos['TipoMipres'];

			if ($laDatosMipres !== null) {

				if ($this->bReqAval) {

				}else{
					foreach($laDatosMipres as $laMipres){
						$lcCodigoProcedimiento=isset($laMipres['CODIGO']) ? $laMipres['CODIGO'] : '';
						$lcNumeroMipres=isset($laMipres['NUMMIPRES']) ? $laMipres['NUMMIPRES'] : '';
						$lnCantidadSinMipres=isset($laMipres['CANTIDADORDENADO']) ? $laMipres['CANTIDADORDENADO'] : '';
						$lnCantidadMipres=isset($laMipres['CANTMIPRES']) ? $laMipres['CANTMIPRES'] : '';

						if (!empty($lcCodigoProcedimiento) && !empty($lcRegistroMipres)){
							$laDatosProcedimiento=[
								'codigocups' => $lcCodigoProcedimiento,
								'numeromipres' => $lcNumeroMipres,
								'cantidadsinmipres' => $lnCantidadSinMipres,
								'cantidadmipres' => $lnCantidadMipres,
								'registrocupsmipres' => $lcRegistroMipres,
								'tipocupsmipres' => $lcTipoMipres,
							];

							$this->nConMipres = $lnConMipres++;
							$lcTabla = 'NPSMPEP';
							$lnLinea = 0;
							$lcDescripcion = '';
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
						}
					}
				}
			}
		}
	}

    function organizarMedicamentos($taDatos=[])
	{
		if(!empty($taDatos)){
			$this->cMedHipertensionPulmonar='';
			$lcSL=PHP_EOL;
			$lcEntidadPlan=$this->aIngreso['nEntidad'].'~'.$this->aIngreso['cPlanDsc'];
			$laEstadosDetalle=array(13, 15, 16);
			$laEstadosProgramacion=array(11, 12, 13);
			$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
			$lnFechaSistema=intval(trim($ltAhora->format('Ymd')));
			$this->nConsAntibiotico=$this->nFechaFinAntibiotico=$lnConsecConsulta=0;
			$llConsecutivoAntibiotico=false;

			if ($this->bReqAval) {
			}else{
				$laDatosConsecutivo=$this->consecutivoFormulacion();
				$this->noSuspendidos($taDatos);
				$this->nConsFormulaMed=$laDatosConsecutivo['consecutivo'];
				$this->nEstadoFormula=$this->nNoSuspendidos==0 ? 14 : $laDatosConsecutivo['estado'];
				$this->nEstadoInicialFormula=$this->nEstadoFormula;

				if (!$laDatosConsecutivo['llNuevoCnsFor']){
					$this->actualizaCabeceraFormula();
				}

				$laDatosFormulaCabecera=[
					'consecutivoformula' => $this->nConsFormulaMed,
					'estadoformula' => $this->nEstadoFormula,
				];

				$lcTabla = 'RIAFARM';
				$lnLinea = 0;
				$lcDescripcion = '';
				$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormulaCabecera);

				foreach($taDatos as $laMedicamentos){
					$lnCantidadFormularActual=$lnEstadoProgramacion=$lnEstadoSuspender=$lnConsecutivoFormato=$lnFechaInicioAntibiotico=0;
					$lnFechaSuspendeAntibiotico=$lnEstadoActual=$this->nConFormula=0;
					$lcTipoSuspenderAntibiotico=$lcTipoModificarAntibiotico=$lcTipoAntibiotico='';
					$lnConsecConsulta++;
					$llInsertar=$llFormuladoAnterior=false;
					$lCodigoMedicamento=isset($laMedicamentos['CODIGO']) ? $laMedicamentos['CODIGO']: '';
					$lnEstadoFormula=isset($laMedicamentos['ESTDET']) ? intval($laMedicamentos['ESTDET']): 0;
					$lnEstadoOrigen=isset($laMedicamentos['ESTDETORIG']) ? intval($laMedicamentos['ESTDETORIG']): 0;
					$lcDescripcionMedicamento=isset($laMedicamentos['MEDICAMENTO']) ? $laMedicamentos['MEDICAMENTO']: '';
					$lnSeformula=isset($laMedicamentos['SEFORMULA']) ? intval($laMedicamentos['SEFORMULA']): 0;
					$lcSuspender=isset($laMedicamentos['SUSPENDER']) ? (intval($laMedicamentos['SUSPENDER'])==1?'S':'') :'';
					$lnInmediato=isset($laMedicamentos['INMEDIATO']) ? intval($laMedicamentos['INMEDIATO']): 0;
					$lnSuspendido=isset($laMedicamentos['SUSPENDER']) ? intval($laMedicamentos['SUSPENDER']): 0;
					$lcEsConciliacion=isset($laMedicamentos['CONCILIACION']) ? trim($laMedicamentos['CONCILIACION']): '';
					$lnInmediato=(!empty($lcEsConciliacion) && $lnSeformula>0)?1:$lnInmediato;
					$lnEstadoFormulacion=$lnInmediato==1 ? 12 : ($lnSuspendido==1 ? 14 : $lnEstadoFormula);
					$lnDosis=isset($laMedicamentos['DOSIS']) ? number_format($laMedicamentos['DOSIS'], 2, '.', ''): 0;
					$lcUnidadDosis=isset($laMedicamentos['CODUNIDADDOSIS']) ? $laMedicamentos['CODUNIDADDOSIS']: '';
					$lcDescripUnidadDosis=isset($laMedicamentos['DESCRUNIDADDOSIS']) ? $laMedicamentos['DESCRUNIDADDOSIS']: '';
					$lnFrecuencia=isset($laMedicamentos['FRECUENCIA']) ? $laMedicamentos['FRECUENCIA']: 0;
					$lcUnidadFrecuencia=isset($laMedicamentos['CODUNIDADFRECUENCIA']) ? $laMedicamentos['CODUNIDADFRECUENCIA']: '';
					$lcDescripUnidadFrecuencia=isset($laMedicamentos['DESCRUNIDADFRECUENCIA']) ? $laMedicamentos['DESCRUNIDADFRECUENCIA']: '';
					$lcUnidadVia=isset($laMedicamentos['VIA']) ? $laMedicamentos['VIA']: '';
					$lnCantidadDiariaNOPOS=isset($laMedicamentos['CANTID']) ? $laMedicamentos['CANTID']: 0;
					$lcObservaciones=isset($laMedicamentos['OBSERVACIONES']) ? mb_substr($laMedicamentos['OBSERVACIONES'],0,220,'UTF-8'): '';
					$lcTextoInmediato=isset($laMedicamentos['TEXTOINMEDIATO']) ? mb_substr($laMedicamentos['TEXTOINMEDIATO'],0,220,'UTF-8'): '';
					$lcPosNoposMed=isset($laMedicamentos['POSNOPOS']) ? ($laMedicamentos['POSNOPOS']=='NOPOS' ? 'S' : 'N') :'';
					$lcAceptaCambio=isset($laMedicamentos['ACEPTACAMBIO']) ? $laMedicamentos['ACEPTACAMBIO']: '';
					$lcMedicamentoCambio=isset($laMedicamentos['MEDCAMBIO']) ? $laMedicamentos['MEDCAMBIO']: '';
					$lcControlAlertaAntibiotico=isset($laMedicamentos['CONTROLALERTAANTIB']) ? $laMedicamentos['CONTROLALERTAANTIB']: '';
					$lnFechaInicioFormulacion=isset($laMedicamentos['FECHACREACIONFORMULA']) ? intval($laMedicamentos['FECHACREACIONFORMULA']): 0;
					$lnFechaInicioFormulacion=$lnFechaInicioFormulacion>0 ? $lnFechaInicioFormulacion : $lnFechaSistema;
					$lnFechaFinFormulacion=$lnFechaInicioFormulacion;
					$lcEsControlado=isset($laMedicamentos['CONTROLADO']) ? trim($laMedicamentos['CONTROLADO']): '';
					$lnControladoCantidad=isset($laMedicamentos['CONTROLADOCANTIDAD']) ? intval($laMedicamentos['CONTROLADOCANTIDAD']): 0;
					$lnDiagnosticoControlado=isset($laMedicamentos['CONTROLADOCIE']) ? $laMedicamentos['CONTROLADOCIE']: '';
					$lnObservacionControlado=isset($laMedicamentos['CONTROLADOOBSERVACIONES']) ? mb_substr($laMedicamentos['CONTROLADOOBSERVACIONES'],0,500,'UTF-8'): '';
					$llEsAntibiotico=isset($laMedicamentos['ESANTIBIOTICO']) ? $laMedicamentos['ESANTIBIOTICO']: false;
					$llEsAntibiotico=$llEsAntibiotico=='true' ? true : false;
					$lnUsoAntFechaFin=isset($laMedicamentos['USOANTFECHAFIN']) ? $laMedicamentos['USOANTFECHAFIN']: 0;
					$lnDiasUsoAntibiotico=isset($laMedicamentos['DUSOANTIBIOTICO']) ? $laMedicamentos['DUSOANTIBIOTICO']: 0;
					$lnDiasUsadoAntibiotico=isset($laMedicamentos['DIASUSADOANTIB']) ? $laMedicamentos['DIASUSADOANTIB']: 0;
					$lnAntibval=isset($laMedicamentos['ANTIBVAL']) ? intval($laMedicamentos['ANTIBVAL']): 0;
					$lnSelec1=isset($laMedicamentos['SELEC1']) ? intval($laMedicamentos['SELEC1']): 0;
					$lnDiasUsoAdicionalAntibiotico=isset($laMedicamentos['DUSOADICION']) ? intval($laMedicamentos['DUSOADICION']): 0;
					$lnHrsinuso=isset($laMedicamentos['HRSINUSO']) ? intval($laMedicamentos['HRSINUSO']): 0;
					$lcUsoantbdiagnosticoinfeccioso=isset($laMedicamentos['USOANTBDIAGNOSTICOINFECCIOSO']) ? $laMedicamentos['USOANTBDIAGNOSTICOINFECCIOSO']: '';
					$lcUsoantbdiagnosticoanexo=isset($laMedicamentos['USOANTBDIAGNOSTICOANEXO']) ? $laMedicamentos['USOANTBDIAGNOSTICOANEXO']: '';
					$lcUsoantbotrosdiagnosticos=isset($laMedicamentos['USOANTBOTROSDIAGNOSTICOS']) ? $laMedicamentos['USOANTBOTROSDIAGNOSTICOS']: '';
					$lcUsoantbtipotratamiento=isset($laMedicamentos['USOANTBTIPOTRATAMIENTO']) ? $laMedicamentos['USOANTBTIPOTRATAMIENTO']: '';
					$lcUsoantbajustes=isset($laMedicamentos['USOANTBAJUSTES']) ? $laMedicamentos['USOANTBAJUSTES']: '';
					$lcUsoantbobservaciones=isset($laMedicamentos['USOANTBOBSERVACIONES']) ? $laMedicamentos['USOANTBOBSERVACIONES']: '';
					$lcUsoantborigenmuestra=isset($laMedicamentos['USOANTBORIGENMUESTRA']) ? $laMedicamentos['USOANTBORIGENMUESTRA']: '';
					$lcUsoantbresultado=isset($laMedicamentos['USOANTBRESULTADO']) ? $laMedicamentos['USOANTBRESULTADO']: '';
					$llFormuladoAnterior=isset($laMedicamentos['FORMULADO']) ? ($laMedicamentos['FORMULADO']==='' ? false : true) : false;
					$lcEsUnirs=isset($laMedicamentos['ESUNIRS']) ? $laMedicamentos['ESUNIRS']: '';
					$lcFormulaPorVia=$laMedicamentos['FORMULAPORVIA'] ?? '';

					if ($llEsAntibiotico){
						$lcTipoSuspenderAntibiotico=isset($laMedicamentos['TIPOSUSPENSDEANTIBIOTICO']) ? ($laMedicamentos['TIPOSUSPENSDEANTIBIOTICO']!=''?('S~'.$laMedicamentos['TIPOSUSPENSDEANTIBIOTICO']):'') : '';
						$lcTipoModificarAntibiotico=isset($laMedicamentos['TIPOMODIFICACIONANTIBIOTICO']) ? ($laMedicamentos['TIPOMODIFICACIONANTIBIOTICO']!=''?('M~'.$laMedicamentos['TIPOMODIFICACIONANTIBIOTICO']):''):'';
						$lcTipoAntibiotico=$lcTipoSuspenderAntibiotico!='' ? $lcTipoSuspenderAntibiotico : $lcTipoModificarAntibiotico;
						$lnFechaInicioAntibiotico=isset($laMedicamentos['FECINICIOANTIB']) ? intval($laMedicamentos['FECINICIOANTIB']): 0;
						$lnFechaSuspendeAntibiotico=$lcTipoSuspenderAntibiotico!='' ? $lnFechaSistema : 0;

						if (!$llFormuladoAnterior){
							$lcTipoAntibiotico='F~';
							$lnFechaInicioAntibiotico=$lnFechaSistema;
						}else{
							$lcTipoAntibiotico=$lcTipoAntibiotico!=''?$lcTipoAntibiotico : 'E~';
						}
					}

					if ($lnDiasUsoAntibiotico>0){
						$lcFechaFinFormulacion=FeFunciones::formatFecha($lnFechaFinFormulacion);
						$lnFechaFinFormulacion=date('Y-m-d', strtotime($lnFechaFinFormulacion."+ $lnDiasUsoAntibiotico days"));
						$lnFechaFinFormulacion=intval(trim(str_replace('-','',$lnFechaFinFormulacion)));
					}

					if ($lnEstadoFormula==0 && $lnEstadoFormula==$lnEstadoOrigen){ }else{
						
						if (!empty($lcFormulaPorVia)){
							$this->oDb->where('VIAFRD', '=', $lcUnidadVia);
						}
						$laRiafard=$this->oDb
						->select('ESTFRD ESTADO, CANFRD CANTIDADFORMULAR')
						->from('RIAFARD')
						->where('NINFRD', '=', $this->aIngreso['nIngreso'])
						->where('CDNFRD', '=', $this->nConsFormulaMed)
						->where('MEDFRD', '=', $lCodigoMedicamento)
						->orderBy('FECFRD DESC, HORFRD DESC')
						->getAll('array');
						if (is_array($laRiafard) && count($laRiafard)>0){
							$lnEstadoActual=$laRiafard[0]['ESTADO'];
							$lnCantidadFormularActual=$laRiafard[0]['CANTIDADFORMULAR'];
						}else{
							$llInsertar=true;
							$lnEstadoActual=$lnEstadoOrigen;
						}
						$lnEstadoOrigen=$lnEstadoActual;
						$lnEstadoFormulacionDetalle=($laDatosConsecutivo['llNuevoCnsFor'] && in_array($lnEstadoFormulacion, $laEstadosDetalle) ? 11 : $lnEstadoFormulacion);
						$lcMedCambio=($lnSuspendido>0 || ($lnSeformula>0 && $lcAceptaCambio=='N')) ? "" : $lcMedicamentoCambio;
						$lnCantidadFormular=$lnSuspendido>0 ? 0 : $lnCantidadFormularActual;
						$lcEstadoProgramacion='9';
						
						if (!$llInsertar){
							if ($lnSuspendido==0){
								if ($lnSeformula==0){
									$lnEstadoFormulacionDetalle=intval($lnEstadoActual)>0?intval($lnEstadoActual):$lnEstadoFormulacionDetalle;
								}	
							}	
						}

						$laDatosFormula=[
							'medicamento' => $lCodigoMedicamento,
							'consecutivoconsulta' => $lnConsecConsulta,
							'consecutivoformula' => $this->nConsFormulaMed,
							'consecutivonuevo' => 0,
							'numerodosis' => 0,
							'estadoformulacion' => $lnEstadoFormulacion,
							'estadoformulaciondetallle' => $lnEstadoFormulacionDetalle,
							'dosis' => $lnDosis,
							'unidaddosis' => $lcUnidadDosis,
							'frecuencia' => $lnFrecuencia,
							'unidadfrecuencia' => $lcUnidadFrecuencia,
							'unidadvia' => $lcUnidadVia,
							'diasusoantibiotico' => $lnDiasUsoAntibiotico,
							'cantidaddiarianopos' => $lnCantidadDiariaNOPOS,
							'cantidaddescripcion' => '',
							'observaciones' => $lcObservaciones,
							'textoinmediato' => $lcTextoInmediato,
							'posnopos' => $lcPosNoposMed,
							'suspenderet1' => $lcSuspender,
							'medicamentocambio' => $lcMedicamentoCambio,
							'medicamentodecambio' => $lcMedCambio,
							'cantidadformular' => $lnCantidadFormular,
							'estadoprogramacion' => $lcEstadoProgramacion,
							'fechaprogramacion' => 0,
							'horaprogramacion' => 0,
							'frecuenciainfusion' => 0,
							'tipoantibiotico' => $lcTipoAntibiotico,
							'fechainiciaantibiotico' => $lnFechaInicioAntibiotico,
							'fechasuspendeantibiotico' => $lnFechaSuspendeAntibiotico,
							'usuantbdiagnosticoinfeccioso' => $lcUsoantbdiagnosticoinfeccioso,
							'usuantbdiagnosticoanexo' => $lcUsoantbdiagnosticoanexo,
							'usuantbotrosdiagnosticos' => $lcUsoantbotrosdiagnosticos,
							'usuantbtipotratamiento' => $lcUsoantbtipotratamiento,
							'usuantbajustes' => $lcUsoantbajustes,
							'usuantbobservaciones' => $lcUsoantbobservaciones,
							'usuantborigenmuestra' => $lcUsoantborigenmuestra,
							'usuantbresultado' => $lcUsoantbresultado,
							'medicamentounirs' => $lcEsUnirs,
							'formulaporvia' => $lcFormulaPorVia,
						];

						if ($lnSeformula>0 || $lcSuspender=='S'){
							$lcTabla = 'FORMED';
							$lnLinea = 0;
							$lcDescripcion = '';
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
						}

						if ($lcSuspender=='S'){
							$lnEstadoSuspender=3;
							$lnActualizar=1;
							$this->actualizaAdministrarMedicamentos($laDatosFormula,$lnEstadoSuspender,$lnActualizar);
						}
						$llInsertar=($lnSeformula==0 && !empty($lcEsConciliacion))?false:$llInsertar;
						if ($llInsertar){
							$llVerificarFormulacion=$this->verificaRegistroFormula($lCodigoMedicamento,$lcFormulaPorVia,$lcUnidadVia);

							if ($llVerificarFormulacion){
								$lcTabla = 'RIAFARD';
								$lnLinea = 0;
								$lcDescripcion = '';
								$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
								
								if ($lnSeformula>0 || $lcSuspender=='S'){
									$this->actualizamedicamentosinsumos($laDatosFormula,2);
								}	
								
								if (!empty($lcEsUnirs)){
									$lcTabla = 'RIAFARDA';
									$lnLinea = 0;
									$lcDescripcion = '';
									$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
								}	
							}	
						}else{
							if ($lnSeformula==0 && ($lnEstadoFormula==0 || $lnEstadoFormula==99) && $lnEstadoFormula==$lnEstadoOrigen){
								$this->actualizaDetalleMedicamentos($laDatosFormula,2);
							}
							if ($lnSeformula>0 && $lnSuspendido>0){
								$lcMedCambio=($lnSuspendido>0 || ($lnSeformula>0 && $lcAceptaCambio=='N')) ? "" : $lcMedicamentoCambio;
								if (!empty($lcAceptaCambio)){
									$this->actualizaCambioMedicamento($lcAceptaCambio,$laDatosFormula);
									$this->actualizaAdministracionMedicamento($laDatosFormula,'9',1);

									if (!empty($lcMedicamentoCambio) && $lcAceptaCambio=='N'){
										$lnActualizar=2;
										$lnEstadoAdministracion=4;
										$this->actualizaAdministrarMedicamentos($laDatosFormula,$lnEstadoAdministracion,$lnActualizar);
									}
								}
								$this->actualizaDetalleMedicamentos($laDatosFormula,2);
							}else{
								$this->actualizaDetalleMedicamentos($laDatosFormula,1);
							}
						}

						$lnEstadoProgramacion=(in_array($lnEstadoFormulacionDetalle, $laEstadosProgramacion) && $this->nEstadoFormula!=12 ) ? $lnEstadoFormulacionDetalle : $this->nEstadoFormula;
						if ($lnEstadoFormulacionDetalle!=99 && $lnEstadoFormulacionDetalle!=14){
							if ($lnEstadoOrigen>0 && $lnSeformula>0 && $lnEstadoSuspender!=3){
								if ($laDatosFormula['unidadfrecuencia']=='10'){
									$lcTabla='ENADMMDT';
									$lnLinea = 0;
									$lcDescripcion = '';
									$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
								}else{
									$this->medicamentosReprogramar($laDatosFormula);
								}
							}else{
								if ($lnEstadoOrigen==0 && $lnSeformula>0 && $lnEstadoSuspender!=3){
									$this->verificarProgramacion($laDatosFormula);
								}
							}
						}
					}

					if ($lnSeformula>0 && $lcEsControlado=='CONTROLADO' && $lnControladoCantidad>0 && !empty($lnDiagnosticoControlado)){
						$lnConsecutivoFormato=$this->oDb->secuencia('SEQ_MEDCONT', 150);

						$laDatosControlado=[
							'medicamento' => $lCodigoMedicamento,
							'consecutivoformato' => $lnConsecutivoFormato,
							'solicituddevolucion' => 'S',
							'cantidad' => $lnControladoCantidad,
							'motivodevolucion' => '',
							'codigodiagnostico' => $lnDiagnosticoControlado,
							'observaciones' => $lnObservacionControlado,
							'descripcionotro' => '',
							'opcional1' => '0',
							'opcional3' => 0,
							'opcional4' => 0,
							'opcional5' => $lcEntidadPlan,
							'opcional6' => '',
						];
						$lcTabla = 'MEDCONT';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosControlado);
					}

					if ($lcControlAlertaAntibiotico=='S'){
						$this->actualizarUsoAntibiotico($laDatosFormula,$lnFechaInicioFormulacion,$lnFechaFinFormulacion);
					}

					if ($lnSeformula>0 && $llEsAntibiotico && $lcControlAlertaAntibiotico=='S' && $lcUsoantbdiagnosticoinfeccioso!=''){
						$lnFechaSistema=$ltAhora->format('Ymd');
						if ($lnUsoAntFechaFin==0){
							$lnTotalDiasAntibiotico=$lnDiasUsoAntibiotico - $lnDiasUsadoAntibiotico;
							$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
							$lnFechaFinAntibiotico=date('Y-m-d', strtotime($lnFechaSistema."+ $lnTotalDiasAntibiotico days"));
							$lnFechaFinAntibiotico=intval(trim(str_replace('-','',$lnFechaFinAntibiotico)));
						}else{
							$lnFechaFinAntibiotico=$lnUsoAntFechaFin;
						}
						$lnFechaSistema = intval(trim($ltAhora->format('Ymd')));
						$this->nFechaFinAntibiotico=$lnFechaFinAntibiotico;

						$laDatosUsoAntibiotico = $this->oDb
							->select('INGANT')
							->from('USOANT')
							->where('INGANT', '=', $this->aIngreso['nIngreso'])
							->where('MEDANT', '=', $lCodigoMedicamento)
							->where('DOSANT', '=', $lnDosis)
							->where('UDOANT', '=', $lcUnidadDosis)
							->where('FRCANT', '=', $lnFrecuencia)
							->where('UFRANT', '=', $lcUnidadFrecuencia)
							->where('VIAANT', '=', $lcUnidadVia)
							->between('FEFANT', $lnFechaSistema, $lnFechaFinAntibiotico)
							->getAll('array');
						if ($this->oDb->numRows()==0){
							if ($llConsecutivoAntibiotico){
								$this->nConsAntibiotico=$this->nConsAntibiotico+1;
							}else{
								$lnConsecutivoAntibiotico=Consecutivos::fCalcularConsecutivoAntibiotico($this->aIngreso['nIngreso']);
								$llConsecutivoAntibiotico=true;
								$this->nConsAntibiotico=$lnConsecutivoAntibiotico;
							}
							$lcTabla = 'USOANT';
							$lnLinea = 0;
							$lcDescripcion = '';
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
						}
					}
					
					if ($lnSeformula>0){
						$lcTablaValida = 'INVATTR';
						$laWhere=[
							'REFDES'=>$lCodigoMedicamento,
							'CL18DES'=>'HIPERTENSIÓNPULMONAR',
						];
						$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->get('array');
						if ($this->oDb->numRows()>0){
							$this->cMedHipertensionPulmonar.='* '.$lCodigoMedicamento.'-'.$lcDescripcionMedicamento.'. Dosis: '.$lnDosis.' '.$lcDescripUnidadDosis.'<br>';
						}
					}
				}

				if ($this->nEstadoFormula!=$this->nEstadoInicialFormula){
					$this->actualizaFormulaEstado($this->nEstadoFormula);
				}
			}
		}
	}

	public function actualizamedicamentosinsumos($taDatosMedicamento=[])
	{
		$lcTabla = 'RIAFARI';
		$lnLinea = 0;
		$lcDescripcion=$lcNroDocumentoInventario='';
		$lcMedicamento=$taDatosMedicamento['medicamento'];

		$laParametros = $this->oDb
			->select('TRIM(A.CINMIN) INSUMO, A.CANMIN CANTIDAD')
			->select('(SELECT TRIM(UNDDES) FROM INVDES AS C WHERE C.REFDES=A.CINMIN) AS UNIDAD')
			->from('MEDINS AS A')
			->where('A.CMEMIN', '=', $lcMedicamento)
			->where('A.ESTMIN', '=', '')
			->getAll('array');
		if ($this->oDb->numRows()>0){
			if (empty($this->cNroDocumentoInventario)){
				$loInventario= new Inventarios();
				$lcStProc='INVGA058CP';
				$lcAccion='01';
				$lcNroDocumentoInventario=$loInventario->numeroDocumentoTransaccion($lcStProc,$this->cTipoDocumentoInventario,$this->cUsuCre);
				$this->cNroDocumentoInventario=$lcNroDocumentoInventario['Retorno'];

				if (!empty($this->cNroDocumentoInventario)){
					$lcStProc='INVGA050CP';
					$laData =[
						'procedure' 	=>$lcStProc,
						'tipodocumento' =>$this->cTipoDocumentoInventario,
						'nrodocumento' 	=>$this->cNroDocumentoInventario,
						'nroingreso' 	=>str_pad(trim($this->aIngreso['nIngreso']), 8, '0', STR_PAD_LEFT),
						'usuariocrea' 	=>$this->cUsuCre,
						'programacrea' 	=>$this->cPrgCre,
						'fechacrea' 	=>$this->cFecCre,
						'horacrea' 		=>$this->cHorCre,
						'accion' 		=>$lcAccion,
					];
					$lcRetornar = $loInventario->cabeceraTransaccion($laData);
				}
			}	

			foreach ($laParametros as $laDatos) {
				$laDataInsumo =[
					'insumo'=>$laDatos['INSUMO'],
					'unidad'=>$laDatos['UNIDAD'],
					'cantidad'=>$laDatos['CANTIDAD'],
				];
				$this->InsertarRegistro($lcTabla, $laDataInsumo, $lnLinea, $taDatosMedicamento);
			}	
		}
		unset($laParametros);
	}

	public function actualizarUsoAntibiotico($taDatosMedicamento=[],$tnInicioFormulacion=0,$tnFinFormulacion=0)
	{
		$lcTabla = 'USOANT';
		$tcEstadoConsulta='2';
		$laDatos = [
			'ESTANT'=>'4',
			'FEVANT'=>$this->cFecCre,
			'HOVANT'=>$this->cHorCre,
			'USVANT'=>$this->cUsuCre,
			'PRVANT'=>$this->cPrgCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)
						->where("(FECANT>=$tnInicioFormulacion AND FECANT<=$tnFinFormulacion)")
						->where('INGANT', '=', $this->aIngreso['nIngreso'])->where('MEDANT', '=', $taDatosMedicamento['medicamento'])->where('ESTANT', '=', $tcEstadoConsulta)
						->actualizar($laDatos);
	}

	public function verificarProgramacion($taDatosMedicamento=[])
	{
		$lnIngreso=$this->aIngreso['nIngreso'];
		$lcMedicamento= $taDatosMedicamento['medicamento'];
		$lnFechaActual=intval($this->cFecCre);
		$lnHoraActual=intval($this->cHorCre);
		$lcFormulaVia=$taDatosMedicamento['formulaporvia'];
		$lcViaAdministracion=$taDatosMedicamento['unidadvia'];

		if (!empty($lcFormulaVia)){
			$this->oDb->where('VIAADM', '=', $lcViaAdministracion);
		}
		$laParametros = $this->oDb
			->select('INGADM')
			->from('ENADMMD')
			->where('INGADM', '=', $this->aIngreso['nIngreso'])
			->where('MEDADM', '=', $lcMedicamento)
			->where('ESTADM', '=', 4)
			->where("(INGADM=$lnIngreso AND MEDADM=$lcMedicamento AND ESTADM=4) AND ((FEPADM=$lnFechaActual AND HDPADM>=$lnHoraActual) OR (FEPADM>$lnFechaActual))")
			->getAll('array');
		if (is_array($laParametros) && count($laParametros)>0){
			$this->medicamentosReprogramar($taDatosMedicamento);
		}else{
			$this->medicamentosProgramacionInicial($taDatosMedicamento);
		}
	}

	public function medicamentosReprogramar($taDatosMedicamento=[])
	{
		$lnConsecutivo=$lnConsNue=$lnRegAdm=0;
		$lcFormulaVia=$taDatosMedicamento['formulaporvia'];
		$lcViaAdministracion=$taDatosMedicamento['unidadvia'];
		
		if (!empty($lcFormulaVia)){
			$this->oDb->where('VIAADM', '=',  $lcViaAdministracion);
		}
		$laParametros = $this->oDb
			->select('MAX(CCOADM) AS CONSEC')
			->from('ENADMMD')
			->where('INGADM','=', $this->aIngreso['nIngreso'])
			->where('MEDADM','=', $taDatosMedicamento['medicamento'])
			->get('array');
		if (is_array($laParametros) && count($laParametros)>0){
			$lnConsecutivo=intval($laParametros['CONSEC']);
		}

		if ($lnConsecutivo>0){
			$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
			$lnFechaSistema=$ltAhora->format('Ymd');
			$lnFecSig=date('Y-m-d', strtotime($lnFechaSistema."+ 1 days"));
			$lnFecSig=intval(trim(str_replace('-','',$lnFecSig)));
			$lnIngreso=intval($this->aIngreso['nIngreso']);
			$lcMedicamento=$taDatosMedicamento['medicamento'];
			$lnDosis=$taDatosMedicamento['dosis'];
			$lcUnidadDosis=$taDatosMedicamento['unidaddosis'];
			$lnFrecuenciaMed=intval($taDatosMedicamento['frecuencia']);
			$lcUnidadFrecuenciaMed=trim($taDatosMedicamento['unidadfrecuencia']);
			$lcUnidadVia=trim($taDatosMedicamento['unidadvia']);
			$lcFormulaPorVia=trim($taDatosMedicamento['formulaporvia']);
			$lnDiasUsoAntibiotico=intval($taDatosMedicamento['diasusoantibiotico']);
			$lcObservaciones=trim($taDatosMedicamento['observaciones']);
			$lnFechaSistema=intval($lnFechaSistema);
			$lnHoraSistema = intval(trim($ltAhora->format('His')));
			$lnEstadoAdm=4;
			$lcEstPrg='3';
			$lnHorOpc=60*60*1;
			$ltFecHorIni=date('Y-m-d H:i:s', strtotime($lnFechaSistema.$lnHoraSistema."+ $lnHorOpc seconds"));
			$lnHoraFin=intval(trim(str_replace(':', '', substr($ltFecHorIni, 11, 10))));
			$lnFreci=0;
			$lnInd=0;

			if ($lcFormulaVia==''){
				$laAdmninistracionRepr=$this->oDb
				->select('INGADM,CTUADM,CEVADM,CCOADM,NDOADM,FEPADM,HDPADM,ESTADM,FREADM,DFRADM,FICAMD')
				->from('ENADMMD')
				->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM>=$lnHoraSistema AND ESTADM=$lnEstadoAdm) OR (INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM>=$lnFecSig AND ESTADM=$lnEstadoAdm)")
				->getAll('array');
			}else{
				$laAdmninistracionRepr=$this->oDb
				->select('INGADM,CTUADM,CEVADM,CCOADM,NDOADM,FEPADM,HDPADM,ESTADM,FREADM,DFRADM,FICAMD')
				->from('ENADMMD')
				->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM>=$lnHoraSistema AND ESTADM=$lnEstadoAdm AND VIAADM=$lcUnidadVia) OR (INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM>=$lnFecSig AND ESTADM=$lnEstadoAdm AND VIAADM=$lcUnidadVia)")
				->getAll('array');
			}		
			if (is_array($laAdmninistracionRepr) && count($laAdmninistracionRepr)>0){
				$lnHorPrg=str_pad($laAdmninistracionRepr[0]['HDPADM'],6,'0',STR_PAD_LEFT);
				$lnFecPrg=$laAdmninistracionRepr[0]['FEPADM'];
				$lnEstPrg=intval($laAdmninistracionRepr[0]['ESTADM']);
				$lnFreAnt=intval($laAdmninistracionRepr[0]['FREADM']);
				$lcTipFre=trim($laAdmninistracionRepr[0]['DFRADM']);
				$lnFreInfusion=intval($laAdmninistracionRepr[0]['FICAMD']);

				if ($lnFreAnt<>$lnFrecuenciaMed){
					if ($lcFormulaVia==''){
						$laAdmninistracionHora=$this->oDb
						->select('HDPADM,FEPADM,ESTADM')
						->from('ENADMMD')
						->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraSistema) AND (ESTADM=2 OR ESTADM=5 OR ESTADM=4)")
						->orderBy('FEPADM DESC, HDPADM DESC')
						->getAll('array');
					}else{
						$laAdmninistracionHora=$this->oDb
						->select('HDPADM,FEPADM,ESTADM')
						->from('ENADMMD')
						->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraSistema AND VIAADM=$lcUnidadVia) AND (ESTADM=2 OR ESTADM=5 OR ESTADM=4)")
						->orderBy('FEPADM DESC, HDPADM DESC')
						->getAll('array');
					}	
					if (is_array($laAdmninistracionHora) && count($laAdmninistracionHora)>0){
						$lnRegAdm=count($laAdmninistracionHora);
						$lnHorPrg=str_pad($laAdmninistracionHora[0]['HDPADM'],6,'0',STR_PAD_LEFT);
						$lnFecPrg=$laAdmninistracionHora[0]['FEPADM'];
						$lnEstPrg=intval($laAdmninistracionHora[0]['ESTADM']);
					}
				}

				if ($lcTipFre==$lcUnidadFrecuenciaMed){
					switch ($lcTipFre) {
						case '1':
							$lnFrec=$lnFrecuenciaMed;
							break;
						case '10':
							$lnFrec=99;
							break;
						case '19':
							if ($lnFreInfusion==0){
								$lnFrec=99;
							}else{
								$lnFrec=$lnFreInfusion;
								$lnFreci=$lnFreInfusion;
							}
							break;
					}
				}else{
					switch ($lcUnidadFrecuenciaMed) {
						case '1':
							$lnFrec=$lnFrecuenciaMed;
							break;
						case '19':
							$lnFrec=24;
							$lnFreci=24;
							break;
						default:
							$lnFrec=24;
							$lnFreci=24;
							break;
					}
				}
				$lnConsNue=$lnConsecutivo + 1;
				$lnFrecu=60*60*$lnFrec;

				if ($lnRegAdm==0){
					$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrg.$lnHorPrg));
					$lnRegAdm=0;
				}else{
					$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrg.$lnHorPrg."+ $lnFrecu seconds"));
				}
				$lnFecPrg=intval(trim(str_replace('-', '', substr($ltFechaHoraProgramar, 0, 10))));
				$lnHorPrg=str_pad(intval(trim(str_replace(':', '', substr($ltFechaHoraProgramar, 11, 10)))),6,'0',STR_PAD_LEFT);

				foreach ($laAdmninistracionRepr as $laDatos) {
					$lnConPrg=0;
					$lnInd++;
					$lnNroIng=intval($laDatos['INGADM']);
					$lnConFor=intval($laDatos['CTUADM']);
					$lnConEvo=intval($laDatos['CEVADM']);
					$lnConPrg=intval($laDatos['CCOADM']);
					$lnNroDos=intval($laDatos['NDOADM']);
					$lnFreAnt=intval($laDatos['FREADM']);

					if ($lnFecPrg <= $lnFecSig){
						if ($lnFreAnt==$lnFrecuenciaMed){
							$lnFecPrg=$laDatos['FEPADM'];
							$lnHorPrg=str_pad($laDatos['HDPADM'],6,'0',STR_PAD_LEFT);
						}
						$lnActualizar=3;
						$laDatosFormula=[
							'medicamento' => $lcMedicamento,
							'consecutivoTurno' => $lnConFor,
							'consecutivoevolucion' => $lnConEvo,
							'consecutivoadministracion' => $lnConPrg,
							'consecutivonuevo' => $lnConsNue,
							'consecutivodosis' => $lnNroDos,
							'numerodosis' => $lnInd,
							'dosis' => $lnDosis,
							'unidaddosis' => $lcUnidadDosis,
							'frecuencia' => $lnFrecuenciaMed,
							'unidadfrecuencia' => $lcUnidadFrecuenciaMed,
							'unidadvia' => $lcUnidadVia,
							'diasusoantibiotico' => $lnDiasUsoAntibiotico,
							'fechaprogramacion' => $lnFecPrg,
							'horaprogramacion' => $lnHorPrg,
							'frecuenciainfusion' => $lnFreci,
							'estadoprogramacion' => $lcEstPrg,
							'estadoformulaciondetallle' => $lnEstadoAdm,
							'observaciones' => $lcObservaciones,
							'formulaporvia' => $lcFormulaPorVia,
						];
						$this->actualizaAdministrarMedicamentos($laDatosFormula,$lnEstadoAdm,$lnActualizar);

						$llVerificarEnfermeria=$this->verificaDetalleEnfermeria($laDatosFormula);
						if ($llVerificarEnfermeria){
							$lcTabla='ENADMMDT';
							$lnLinea = 0;
							$lcDescripcion = '';
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
						}	
					}else{
						if ((($lnFrec <> $lnFrecuenciaMed) && $lcUnidadFrecuenciaMed=='1') || ($lcTipFre<>$lcUnidadFrecuenciaMed)){
							$laDatosEliminar=[
								'medicamento' => $lcMedicamento,
								'consecutivoTurno' => $lnConFor,
								'consecutivoevolucion' => $lnConEvo,
								'consecutivoadministracion' => $lnConPrg,
								'numerodosis' => $lnNroDos,
								'formulaporvia' => $lcFormulaPorVia,
								'unidadvia' => $lcUnidadVia,
							];
							$this->eliminarAdministracion($laDatosEliminar);
						}
					}

					if ($lnFreAnt<>$lnFrecuenciaMed){
						$laDatosEliminar=[
							'medicamento' => $lcMedicamento,
							'consecutivoTurno' => $lnConFor,
							'consecutivoevolucion' => $lnConEvo,
							'consecutivoadministracion' => $lnConPrg,
							'numerodosis' => $lnNroDos,
							'formulaporvia' => $lcFormulaPorVia,
							'unidadvia' => $lcUnidadVia,
						];
						$this->eliminarAdministracion($laDatosEliminar);
					}
					$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrg.$lnHorPrg."+ $lnFrecu seconds"));
					$lnFecPrg=intval(trim(str_replace('-', '', substr($ltFechaHoraProgramar, 0, 10))));
					$lnHorPrg=str_pad(intval(trim(str_replace(':', '', substr($ltFechaHoraProgramar, 11, 10)))),6,'0',STR_PAD_LEFT);
				}

				while($lnFecPrg <= $lnFecSig){
					if (!empty($lcFormulaVia)){
						$this->oDb->where('VIAADM', '=', $lcUnidadVia);
					}
					$laAdmninistracionTMP=$this->oDb
					->select('INGADM')
					->from('ENADMMD')
					->where('INGADM','=', $this->aIngreso['nIngreso'])
					->where('MEDADM','=', $lcMedicamento)
					->where('ESTADM','=', 4)
					->where('FEPADM','=', $lnFecPrg)
					->where('HDPADM','=', $lnHorPrg)
					->getAll('array');
					if (is_array($laAdmninistracionTMP) && count($laAdmninistracionTMP)==0){
						$lnInd++;
						$laDatosFormula=[
							'medicamento' => $lcMedicamento,
							'consecutivonuevo' => $lnConsNue,
							'numerodosis' => $lnInd,
							'estadoformulaciondetallle' => $lnEstadoAdm,
							'dosis' => $lnDosis,
							'unidaddosis' => $lcUnidadDosis,
							'frecuencia' => $lnFrecuenciaMed,
							'unidadfrecuencia' => $lcUnidadFrecuenciaMed,
							'unidadvia' => $lcUnidadVia,
							'diasusoantibiotico' => $lnDiasUsoAntibiotico,
							'observaciones' => $lcObservaciones,
							'fechaprogramacion' => $lnFecPrg,
							'horaprogramacion' => $lnHorPrg,
							'frecuenciainfusion' => $lnFreci,
							'estadoprogramacion' => $lcEstPrg,
							'formulaporvia' => $lcFormulaPorVia,
						];

						$lcTabla='ENADMMD';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);

						$llVerificarEnfermeria=$this->verificaDetalleEnfermeria($laDatosFormula);
						if ($llVerificarEnfermeria){
							$lcTabla='ENADMMDT';
							$lnLinea = 0;
							$lcDescripcion = '';
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
						}	
					}
					$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrg.$lnHorPrg."+ $lnFrecu seconds"));
					$lnFecPrg=intval(trim(str_replace('-', '', substr($ltFechaHoraProgramar, 0, 10))));
					$lnHorPrg=str_pad(intval(trim(str_replace(':', '', substr($ltFechaHoraProgramar, 11, 10)))),6,'0',STR_PAD_LEFT);
				}
			}else{
				if ($lcUnidadFrecuenciaMed<>'10'){
					if ($lcFormulaVia==''){
						$laAdmninistracionAdm=$this->oDb
						->select('FEPADM,HDPADM,FICAMD')
						->from('ENADMMD')
						->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraFin) AND (ESTADM=2 OR ESTADM=5)")
						->orderBy('FEPADM DESC, HDPADM DESC')
						->getAll('array');
						if (is_array($laAdmninistracionAdm) && count($laAdmninistracionAdm)==0 && $lnFrecuenciaMed==24 && $lcUnidadFrecuenciaMed=='1'){
							$laAdmninistracionAdm=$this->oDb
							->select('FEPADM,HDPADM,FICAMD')
							->from('ENADMMD')
							->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraFin AND ESTADM=4)")
							->orderBy('FEPADM DESC, HDPADM DESC')
							->getAll('array');
						}
					}else{
						$laAdmninistracionAdm=$this->oDb
						->select('FEPADM,HDPADM,FICAMD')
						->from('ENADMMD')
						->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraFin AND VIAADM=$lcUnidadVia) AND (ESTADM=2 OR ESTADM=5)")
						->orderBy('FEPADM DESC, HDPADM DESC')
						->getAll('array');
						if (is_array($laAdmninistracionAdm) && count($laAdmninistracionAdm)==0 && $lnFrecuenciaMed==24 && $lcUnidadFrecuenciaMed=='1'){
							$laAdmninistracionAdm=$this->oDb
							->select('FEPADM,HDPADM,FICAMD')
							->from('ENADMMD')
							->where("(INGADM=$lnIngreso AND MEDADM='$lcMedicamento' AND FEPADM=$lnFechaSistema AND HDPADM<=$lnHoraFin AND VIAADM=$lcUnidadVia AND ESTADM=4)")
							->orderBy('FEPADM DESC, HDPADM DESC')
							->getAll('array');
						}
					}
					if (is_array($laAdmninistracionAdm) && count($laAdmninistracionAdm)>0){
						$lnConsNue=$lnConsecutivo + 1;
						$lnFecPrg=$laAdmninistracionAdm[0]['FEPADM'];
						$lnHorPrg=str_pad($laAdmninistracionAdm[0]['HDPADM'],6,'0',STR_PAD_LEFT);

						if ($lcUnidadFrecuenciaMed=='19'){
							if (intval($laAdmninistracionAdm[0]['FICAMD'])==0){
								$lnFreInfusion=24;
							}else{
								$lnFreInfusion=intval($laAdmninistracionAdm[0]['FICAMD']);
							}
							$lnFrecu=60*60*$lnFreInfusion;
						}else{
							$lnFreInfusion=0;
							$lnFrecu=60*60*$lnFrecuenciaMed;
						}
						$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrg.$lnHorPrg."+ $lnFrecu seconds"));
						$lnFecPrgAdmin=intval(trim(str_replace('-', '', substr($ltFechaHoraProgramar, 0, 10))));
						$lnHorPrgAdmin=str_pad(intval(trim(str_replace(':', '', substr($ltFechaHoraProgramar, 11, 10)))),6,'0',STR_PAD_LEFT);
						$lnInd=0;
						while($lnFecPrgAdmin <= $lnFecSig){
							if (!empty($lcFormulaVia)){
								$this->oDb->where('VIAADM', '=', $lcUnidadVia);
							}
							$laAdmninistracionTMP=$this->oDb
								->select('INGADM')
								->from('ENADMMD')
								->where('INGADM','=', $this->aIngreso['nIngreso'])
								->where('MEDADM','=', $lcMedicamento)
								->where('ESTADM','=', 4)
								->where('FEPADM','=', $lnFecPrgAdmin)
								->where('HDPADM','=', $lnHorPrgAdmin)
								->getAll('array');
								if (is_array($laAdmninistracionTMP) && count($laAdmninistracionTMP)==0){
									$lnInd++;
									$laDatosFormula=[
										'medicamento' => $lcMedicamento,
										'consecutivonuevo' => $lnConsNue,
										'numerodosis' => $lnInd,
										'estadoformulaciondetallle' => $lnEstadoAdm,
										'dosis' => $lnDosis,
										'unidaddosis' => $lcUnidadDosis,
										'frecuencia' => $lnFrecuenciaMed,
										'unidadfrecuencia' => $lcUnidadFrecuenciaMed,
										'unidadvia' => $lcUnidadVia,
										'diasusoantibiotico' => $lnDiasUsoAntibiotico,
										'observaciones' => $lcObservaciones,
										'fechaprogramacion' => $lnFecPrgAdmin,
										'horaprogramacion' => $lnHorPrgAdmin,
										'frecuenciainfusion' => $lnFreci,
										'estadoprogramacion' => $lcEstPrg,
										'formulaporvia' => $lcFormulaPorVia,
									];
									$lcTabla='ENADMMD';
									$lnLinea = 0;
									$lcDescripcion = '';
									$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);

									$llVerificarEnfermeria=$this->verificaDetalleEnfermeria($laDatosFormula);
									if ($llVerificarEnfermeria){
										$lcTabla='ENADMMDT';
										$lnLinea = 0;
										$lcDescripcion = '';
										$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosFormula);
									}	
								}
								$ltFechaHoraProgramar=date('Y-m-d H:i:s', strtotime($lnFecPrgAdmin.$lnHorPrgAdmin."+ $lnFrecu seconds"));
								$lnFecPrgAdmin=intval(trim(str_replace('-', '', substr($ltFechaHoraProgramar, 0, 10))));
								$lnHorPrgAdmin=str_pad(intval(trim(str_replace(':', '', substr($ltFechaHoraProgramar, 11, 10)))),6,'0',STR_PAD_LEFT);
						}
					}else{
						$this->medicamentosProgramacionInicial($taDatosMedicamento);
					}
				}else{
					$this->medicamentosProgramacionInicial($taDatosMedicamento);
				}
			}
		}else{
			$this->medicamentosProgramacionInicial($taDatosMedicamento);
		}
	}

	public function eliminarAdministracion($taDatosMedicamento=[])
	{
		$lcTabla = 'ENADMMD';
		$lcFormulaVia=$taDatosMedicamento['formulaporvia'];

		if ($lcFormulaVia==''){
			$laWhere = [
				'INGADM'=> $this->aIngreso['nIngreso'],
				'CTUADM'=>$taDatosMedicamento['consecutivoTurno'],
				'CEVADM'=>$taDatosMedicamento['consecutivoevolucion'],
				'CCOADM'=>$taDatosMedicamento['consecutivoadministracion'],
				'NDOADM'=>$taDatosMedicamento['numerodosis'],
				'MEDADM'=>$taDatosMedicamento['medicamento'],
			];
		}else{
			$laWhere = [
				'INGADM'=> $this->aIngreso['nIngreso'],
				'CTUADM'=>$taDatosMedicamento['consecutivoTurno'],
				'CEVADM'=>$taDatosMedicamento['consecutivoevolucion'],
				'CCOADM'=>$taDatosMedicamento['consecutivoadministracion'],
				'NDOADM'=>$taDatosMedicamento['numerodosis'],
				'MEDADM'=>$taDatosMedicamento['medicamento'],
				'VIAADM'=>$taDatosMedicamento['unidadvia'],
			];
		}	
		$this->oDb->from($lcTabla)->where($laWhere)->eliminar();
	}

	public function medicamentosProgramacionInicial($taDatosMedicamento=[])
	{
		$lcFormulaVia=trim($taDatosMedicamento['formulaporvia']) ?? '';
		$lnRegistros=0;
		
		if (!empty($lcFormulaVia)){
			$this->oDb->where('VIAADM', '=', $taDatosMedicamento['unidadvia']);
		}
		$laParametros = $this->oDb
			->select('INGADM INGRESO')
			->from('ENADMMDT')
			->where('INGADM','=', $this->aIngreso['nIngreso'])
			->where('MEDADM','=', $taDatosMedicamento['medicamento'])
			->where('ESPAMD','=', '9')
			->getAll('array');
		if (is_array($laParametros) && count($laParametros)>0){
			$lnRegistros=count($laParametros);
			if ($lnRegistros==1){
				$this->actualizaAdministracionMedicamento($taDatosMedicamento,'9',2);
			}
		}else{
			$lcTabla='ENADMMDT';
			$lnLinea = 0;
			$lcDescripcion = '';
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $taDatosMedicamento);
		}
	}

	public function actualizaAdministracionMedicamento($taDatosMedicamento=[],$tcEstadoProgramacion='',$tnActualizar=0)
	{
		$lcFormulaVia=trim($taDatosMedicamento['formulaporvia']) ?? '';
		$lcViaAdministracion=trim($taDatosMedicamento['unidadvia']) ?? '';
		
		$lcTabla = 'ENADMMDT';
		if ($tnActualizar==1){
			$laDatos = [
				'MEDADM'=>$taDatosMedicamento['medicamento'],
				'USMADM'=>$this->cUsuCre,
				'PMMADM'=>$this->cPrgCre,
				'FEMADM'=>$this->cFecCre,
				'HMRADM'=>$this->cHorCre,
			];
			if (empty($lcFormulaVia)){
				$llResultado = $this->oDb->tabla($lcTabla)
					->where(['INGADM'=>$this->aIngreso['nIngreso'],'MEDADM'=>$taDatosMedicamento['medicamentocambio'],'ESPAMD'=>$tcEstadoProgramacion,])->actualizar($laDatos);
			}else{
				$llResultado = $this->oDb->tabla($lcTabla)
					->where(['INGADM'=>$this->aIngreso['nIngreso'],'MEDADM'=>$taDatosMedicamento['medicamentocambio'],'ESPAMD'=>$tcEstadoProgramacion,'VIAADM'=>$lcViaAdministracion,])->actualizar($laDatos);
			}					
		}

		if ($tnActualizar==2){
			$laDatos = [
				'CTUADM'=>$taDatosMedicamento['consecutivoformula'],
				'CEVADM'=>$this->nConEvo,
				'DOSADM'=>$taDatosMedicamento['dosis'],
				'DDOADM'=>$taDatosMedicamento['unidaddosis'],
				'FREADM'=>$taDatosMedicamento['frecuencia'],
				'DFRADM'=>$taDatosMedicamento['unidadfrecuencia'],
				'VIAADM'=>$taDatosMedicamento['unidadvia'],
				'NDFADM'=>$taDatosMedicamento['diasusoantibiotico'],
				'OBMADM'=>$taDatosMedicamento['observaciones'],
				'USFADM'=>$this->cUsuCre,
				'FEOADM'=>$this->cFecCre,
				'HDOADM'=>$this->cHorCre,
				'USMADM'=>$this->cUsuCre,
				'PMMADM'=>$this->cPrgCre,
				'FEMADM'=>$this->cFecCre,
				'HMRADM'=>$this->cHorCre,
			];
			if (empty($lcFormulaVia)){
				$llResultado = $this->oDb->tabla($lcTabla)
								->where(['INGADM'=>$this->aIngreso['nIngreso'],'CCOADM'=>0,'NDOADM'=>0,'MEDADM'=>$taDatosMedicamento['medicamento'],'ESPAMD'=>$tcEstadoProgramacion,])->actualizar($laDatos);
			}else{
				$llResultado = $this->oDb->tabla($lcTabla)
					->where(['INGADM'=>$this->aIngreso['nIngreso'],'CCOADM'=>0,'NDOADM'=>0,'MEDADM'=>$taDatosMedicamento['medicamento'],'ESPAMD'=>$tcEstadoProgramacion,'VIAADM'=>$lcViaAdministracion,])->actualizar($laDatos);
			}					
		}
	}

	public function actualizaDetalleMedicamentos($taDatosMedicamento=[],$tnActualizar=0)
	{
		$lcTabla = 'RIAFARD';

		$lnUltimaFormula=$lnUltimoEvolucion=0;
		if ($tnActualizar==1){
			$laDatos = [
				'CEVFRD'=>$this->nConEvo,
				'ESTFRD'=>$taDatosMedicamento['estadoformulaciondetallle'],
				'DOSFRD'=>$taDatosMedicamento['dosis'],
				'UDOFRD'=>$taDatosMedicamento['unidaddosis'],
				'FRCFRD'=>$taDatosMedicamento['frecuencia'],
				'AUTFRD'=>'',
				'UFRFRD'=>$taDatosMedicamento['unidadfrecuencia'],
				'VIAFRD'=>$taDatosMedicamento['unidadvia'],
				'CANFRD'=>$taDatosMedicamento['cantidadformular'],
				'JUSFRD'=>$taDatosMedicamento['posnopos'],
				'DADFRD'=>intval($taDatosMedicamento['diasusoantibiotico']),
				'OBSFRD'=>$taDatosMedicamento['observaciones'],
				'MCDFRD'=>$taDatosMedicamento['medicamentodecambio'],
				'FEFFRD'=>$this->cFecCre,
				'UMOFRD'=>$this->cUsuCre,
				'PMOFRD'=>$this->cPrgCre,
				'FMOFRD'=>$this->cFecCre,
				'HMOFRD'=>$this->cHorCre,
			];
		}

		if ($tnActualizar==2){
			$laDatos = [
				'CEVFRD'=>$this->nConEvo,
				'AUTFRD'=>'',
				'UMOFRD'=>$this->cUsuCre,
				'PMOFRD'=>$this->cPrgCre,
				'FMOFRD'=>$this->cFecCre,
				'HMOFRD'=>$this->cHorCre,
			];
		}
		
		if ($taDatosMedicamento['formulaporvia']==''){
			$llResultado = $this->oDb->tabla($lcTabla)->where(['NINFRD'=>$this->aIngreso['nIngreso'],'CDNFRD'=>$this->nConsFormulaMed,'MEDFRD'=>$taDatosMedicamento['medicamento'],])->actualizar($laDatos);
		}else{
			$llResultado = $this->oDb->tabla($lcTabla)->where(['NINFRD'=>$this->aIngreso['nIngreso'],'CDNFRD'=>$this->nConsFormulaMed,'MEDFRD'=>$taDatosMedicamento['medicamento'],'VIAFRD'=>$taDatosMedicamento['unidadvia'],])->actualizar($laDatos);
		}	
		unset($laUtimaFormula);

		$lcTabla = 'RIAFARI';
		if ($tnActualizar==1){
			$laDatos = [
				'CEVRFI'=>$this->nConEvo,
				'ESTRFI'=>$taDatosMedicamento['estadoformulaciondetallle'],
				'USMRFI'=>$this->cUsuCre,
				'PGMRFI'=>$this->cPrgCre,
				'FEMRFI'=>$this->cFecCre,
				'HOMRFI'=>$this->cHorCre,
			];
		}
		
		if ($tnActualizar==2){
			$laDatos = [
				'CEVRFI'=>$this->nConEvo,
				'USMRFI'=>$this->cUsuCre,
				'PGMRFI'=>$this->cPrgCre,
				'FEMRFI'=>$this->cFecCre,
				'HOMRFI'=>$this->cHorCre,
			];
		}
		$llResultado = $this->oDb->tabla($lcTabla)->where(['INGRFI'=>$this->aIngreso['nIngreso'],'CFORFI'=>$this->nConsFormulaMed,'COMRFI'=>$taDatosMedicamento['medicamento'],])->actualizar($laDatos);
		
		if (!empty($taDatosMedicamento['medicamentounirs'])){
			$this->actualizaFormulaAlterna($taDatosMedicamento);
		}	
	}

	public function actualizaFormulaAlterna($taDatosMedicamento=[])
	{
		$lcTabla = 'RIAFARDA';
		$laRiafada=$this->oDb
			->select('TMUNIA')
			->from('RIAFARDA')
			->where('NINFRA', '=', $this->aIngreso['nIngreso'])
			->where('CDNFRA', '=', $this->nConsFormulaMed)
			->where('MEDFRA', '=', $taDatosMedicamento['medicamento'])
			->getAll('array');
		if($this->oDb->numRows()>0){	
			$laDatos = [
				'CEVFRA'=>$this->nConEvo,
				'TMUNIA'=>$taDatosMedicamento['medicamentounirs'],
				'USMRMA'=>$this->cUsuCre,
				'PGMRMA'=>$this->cPrgCre,
				'FEMRMA'=>$this->cFecCre,
				'HOMRMA'=>$this->cHorCre,
			];
			$llResultado = $this->oDb->tabla($lcTabla)->where(['NINFRA'=>$this->aIngreso['nIngreso'],'CDNFRA'=>$this->nConsFormulaMed,'MEDFRA'=>$taDatosMedicamento['medicamento'],])->actualizar($laDatos);
		}else{
			$lnLinea = 0;
			$lcDescripcion = '';
			$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $taDatosMedicamento);
		}
		unset($laRiafada);

	}
	public function actualizaAdministrarMedicamentos($taDatosMedicamento=[],$tnEstado=0,$tnActualizar=0)
	{
		$lcTabla = 'ENADMMD';
		$lcFormulaVia=$taDatosMedicamento['formulaporvia'];
		$lcViaAdmin=$taDatosMedicamento['unidadvia'];

		if ($tnActualizar==1){
			$lcobag='Medico suspende';
			$tnEstadoSuspende=4;
			$laDatos = [
				'ESTADM'=>$tnEstado,
				'OBSADM'=>$lcobag,
				'USMADM'=>$this->cUsuCre,
				'PMMADM'=>$this->cPrgCre,
				'FEMADM'=>$this->cFecCre,
				'HMRADM'=>$this->cHorCre,
			];
			if (!empty($lcFormulaVia)){
				$this->oDb->where('VIAADM', '=', $lcViaAdmin);
			}
			$llResultado = $this->oDb->tabla($lcTabla)
				->where('INGADM', '=', $this->aIngreso['nIngreso'])
				->where('MEDADM', '=', $taDatosMedicamento['medicamento'])
				->where('FEPADM', '>', $this->cFecCre)
				->where('ESTADM', '=', $tnEstadoSuspende)
				->actualizar($laDatos);
			$llResultado = $this->oDb->tabla($lcTabla)
				->where('INGADM', '=', $this->aIngreso['nIngreso'])
				->where('MEDADM', '=', $taDatosMedicamento['medicamento'])
				->where('FEPADM', '=', $this->cFecCre)
				->where('HDPADM', '>', $this->cHorCre)
				->where('ESTADM', '=', $tnEstadoSuspende)
				->actualizar($laDatos);
		}

		if ($tnActualizar==2){
			$lnFechaActual=intval($this->cFecCre);
			$lnHoraActual=intval($this->cHorCre);
			$laDatos = [
				'MEDADM'=>$taDatosMedicamento['medicamento'],
				'USMADM'=>$this->cUsuCre,
				'PMMADM'=>$this->cPrgCre,
				'FEMADM'=>$this->cFecCre,
				'HMRADM'=>$this->cHorCre,
			];
			if ($lcFormulaVia==''){
				$llResultado = $this->oDb->tabla($lcTabla)
					->where("(FEPADM=$lnFechaActual AND HDPADM>$lnHoraActual) OR FEPADM>$lnFechaActual")
					->where('INGADM', '=', $this->aIngreso['nIngreso'])
					->where('MEDADM', '=', $taDatosMedicamento['medicamentocambio'])
					->where('ESTADM', '=', $tnEstado)
					->actualizar($laDatos);
			}else{
				$llResultado = $this->oDb->tabla($lcTabla)
					->where("(FEPADM=$lnFechaActual AND HDPADM>$lnHoraActual) OR FEPADM>$lnFechaActual")
					->where('INGADM', '=', $this->aIngreso['nIngreso'])
					->where('MEDADM', '=', $taDatosMedicamento['medicamentocambio'])
					->where('ESTADM', '=', $tnEstado)
					->where('VIAADM', '=', $lcViaAdmin)
					->actualizar($laDatos);
			}				
		}

		if ($tnActualizar==3){
			$lnFechaActual=intval($this->cFecCre);
			$lnHoraActual=intval($this->cHorCre);
			$laDatos = [
				'CTUADM'=>$this->nConsFormulaMed,
				'CEVADM'=>$this->nConEvo,
				'CCOADM'=>$taDatosMedicamento['consecutivonuevo'],
				'NDOADM'=>$taDatosMedicamento['numerodosis'],
				'SCAADM'=>$this->aIngreso['cSeccion'],
				'NCAADM'=>$this->aIngreso['cHabita'],
				'ESTADM'=>$tnEstado,
				'DOSADM'=>$taDatosMedicamento['dosis'],
				'DDOADM'=>$taDatosMedicamento['unidaddosis'],
				'FREADM'=>$taDatosMedicamento['frecuencia'],
				'DFRADM'=>$taDatosMedicamento['unidadfrecuencia'],
				'VIAADM'=>$taDatosMedicamento['unidadvia'],
				'NDFADM'=>$taDatosMedicamento['diasusoantibiotico'],
				'OBMADM'=>$taDatosMedicamento['observaciones'],
				'FEPADM'=>$taDatosMedicamento['fechaprogramacion'],
				'HDPADM'=>$taDatosMedicamento['horaprogramacion'],
				'USFADM'=>$this->cUsuCre,
				'FEOADM'=>$this->cFecCre,
				'HDOADM'=>$this->cHorCre,
				'FICAMD'=>$taDatosMedicamento['frecuenciainfusion'],
				'USMADM'=>$this->cUsuCre,
				'PMMADM'=>$this->cPrgCre,
				'FEMADM'=>$this->cFecCre,
				'HMRADM'=>$this->cHorCre,
				'ESPAMD'=>$taDatosMedicamento['estadoprogramacion'],
			];
			if ($lcFormulaVia==''){
				$llResultado = $this->oDb
					->tabla($lcTabla)
					->where([
						'INGADM'=>$this->aIngreso['nIngreso'],
						'CTUADM'=>$taDatosMedicamento['consecutivoTurno'],
						'CEVADM'=>$taDatosMedicamento['consecutivoevolucion'],
						'CCOADM'=>$taDatosMedicamento['consecutivoadministracion'],
						'NDOADM'=>$taDatosMedicamento['consecutivodosis'],
						'MEDADM'=>$taDatosMedicamento['medicamento'],
						'ESTADM'=>$tnEstado,
					])
					->actualizar($laDatos);
			}else{
				$llResultado = $this->oDb
					->tabla($lcTabla)
					->where([
						'INGADM'=>$this->aIngreso['nIngreso'],
						'CTUADM'=>$taDatosMedicamento['consecutivoTurno'],
						'CEVADM'=>$taDatosMedicamento['consecutivoevolucion'],
						'CCOADM'=>$taDatosMedicamento['consecutivoadministracion'],
						'NDOADM'=>$taDatosMedicamento['consecutivodosis'],
						'MEDADM'=>$taDatosMedicamento['medicamento'],
						'ESTADM'=>$tnEstado,
						'VIAADM'=>$lcViaAdmin,
					])
					->actualizar($laDatos);
			}		
		}
	}

	public function actualizaCambioMedicamento($tcAceptaCambio='',$taDatosMedicamento=[])
	{
		$lcMedicamento=trim($taDatosMedicamento['medicamento']) ?? '';
		$lcViaAdministracion=trim($taDatosMedicamento['unidadvia']) ?? '';
		$lcTabla = 'RIAFARDIF';
		$laDatos = [
			'ACEFRD'=>$tcAceptaCambio,
			'UMOFRD'=>$this->cUsuCre,
			'PMOFRD'=>$this->cPrgCre,
			'FMOFRD'=>$this->cFecCre,
			'HMOFRD'=>$this->cHorCre,
		];
		$llResultado = $this->oDb->tabla($lcTabla)
						->where(['NINFRD'=>$this->aIngreso['nIngreso'],'CDNFRD'=>$this->nConsFormulaMed,'MEDFRD'=>$lcMedicamento,'VIAFRD'=>$lcViaAdministracion,])->actualizar($laDatos);
	}

	public function actualizaCabeceraFormula()
	{
		$lcTabla = 'RIAFARM';
		$laDatos = [
			'ESTFAR'=>-1,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['INGFAR'=>$this->aIngreso['nIngreso'],'CDNFAR'=>$this->nConsFormulaMed,])->actualizar($laDatos);
	}

	public function actualizaFormulaEstado($tnEstado=0)
	{
		$lcTabla = 'RIAFARM';
		$laDatos = [
			'ESTFAR'=>$tnEstado,
		];
		$llResultado = $this->oDb->tabla($lcTabla)
						->where(['INGFAR'=>$this->aIngreso['nIngreso'],'CDNFAR'=>$this->nConsFormulaMed,'EVOFAR'=>$this->nConEvo,])
						->actualizar($laDatos);
	}

	public function noSuspendidos($taDatos=[])
	{
		$this->nNoSuspendidos=0;
		foreach($taDatos as $laSuspendidos){
			if (intval($laSuspendidos['ESTDET'])!=14){
				$this->nNoSuspendidos++;
			}
		}
	}


	public function consecutivoFormulacion()
	{
		$laDatosFormula='';
		$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
		$lnFechaSistema=intval(trim($ltAhora->format('Ymd')));

		$laRiafarm=$this->oDb
			->select('CDNFAR CONSECUTIVO, ESTFAR ESTADO, FECFAR FECHAFORMULA')
			->from('RIAFARM')
			->where('INGFAR', '=', $this->aIngreso['nIngreso'])
			->orderBy('CDNFAR DESC FETCH FIRST 1 ROWS ONLY')
			->getAll('array');

		if (is_array($laRiafarm) && count($laRiafarm)>0){
			$lnConsecutivo=intval($laRiafarm[0]['CONSECUTIVO']);
			$lnEstado=intval($laRiafarm[0]['ESTADO']);
			$lnFechaFormula=intval($laRiafarm[0]['FECHAFORMULA']);
			$lnEstado=$lnEstado<=1?11:$lnEstado;

			if ($lnFechaFormula==$lnFechaSistema){
				$laDatosFormula=[
					'consecutivo' => $lnConsecutivo,
					'llNuevoCnsFor' => false,
					'estado' => $lnEstado,
				];
			}else{
				$laDatosFormula=[
					'consecutivo' => $lnConsecutivo+1,
					'llNuevoCnsFor' => true,
					'estado' => 99,
				];
			}
		}else{
			$laDatosFormula=[
				'consecutivo' => 1,
				'llNuevoCnsFor' => true,
				'estado' => 11,
			];
		}
		unset($laRiafarm);
		return $laDatosFormula;
	}


	function organizarProcedimientos($taDatos=[],$taHemocomponente=[])
	{
		if(!empty($taDatos)){
			$lnGuardaGluc=0;
			$laCupsHexalis = [];
			$lnConsecutivoOrden=0;
			$lnCodigoConsecOrden=911;
			$lnConsecutivoBancoSangre=0;
			$lnCodigoConsecBanco=933;
			$lcSeEnvioAdt=$lcRegistroHemocomponente='';
			$lnConMipres=Consecutivos::fCalcularConsecutivoMipres($this->aIngreso['nIngreso']);

			if ($this->bReqAval) {

			}else{
				foreach($taDatos as $laProcedimientos){
					$lcSalaAgfa='';
					$lnEstadoOrden=8;
					$lcTipoRegistro=isset($laProcedimientos['TIPO']) ? $laProcedimientos['TIPO'] : '';
					$lcCodigoProcedimiento=isset($laProcedimientos['CODIGO']) ? $laProcedimientos['CODIGO'] : '';
					$lcDescripcionProcedimiento=isset($laProcedimientos['DESCRIPCION']) ? $laProcedimientos['DESCRIPCION'] : '';
					$lcObservaciones=isset($laProcedimientos['OBSERVACIONES']) ? trim($laProcedimientos['OBSERVACIONES']) : '';
					$lcCodigoEspecialidad=isset($laProcedimientos['ESPECIALIDAD']) ? $laProcedimientos['ESPECIALIDAD'] : '';
					$lnCantidad=isset($laProcedimientos['CANTIDAD']) ? $laProcedimientos['CANTIDAD'] : 0;
					$lnConsecutivoLinea=isset($laProcedimientos['LINEA']) ? intval($laProcedimientos['LINEA']) : 0;
					$lcCupsPosNopos=isset($laProcedimientos['POSNOPOS']) ? $laProcedimientos['POSNOPOS'] : '';
					$lcCodigoHexalis=isset($laProcedimientos['HEXALIS']) ? $laProcedimientos['HEXALIS'] : $lcCodigoProcedimiento;
					$lcEnviaAgfa=isset($laProcedimientos['AGFA']) ? $laProcedimientos['AGFA'] : '';
					$lcModeloEquipo=isset($laProcedimientos['MODELOEQUIPO']) ? $laProcedimientos['MODELOEQUIPO'] : '';
					$lcTipoEvento=isset($laProcedimientos['TIPOADT']) ? $laProcedimientos['TIPOADT'] : '';
					$lcTipomensaje=isset($laProcedimientos['TIPOMENSAJE']) ? $laProcedimientos['TIPOMENSAJE'] : '';
					$lcGlucometriaSiNo=isset($laProcedimientos['NECESARIOGLUCOMETRIA']) ? substr($laProcedimientos['NECESARIOGLUCOMETRIA'], 0, 1) : '';
					$lcEsHemocomponente=isset($laProcedimientos['HEMOCOMPONENTE']) ? $laProcedimientos['HEMOCOMPONENTE'] : '';

					if ($lcTipoRegistro=='CUPS'){
						if (substr($lcCodigoProcedimiento, 0, 4)!='8904'){
							$lnEstadoOrden=$this->estadoProcedimiento($lcCodigoProcedimiento);
							$lnEstadoOrden=$lcEsHemocomponente!='' ? $this->nEstadoHemocomponente : $lnEstadoOrden;
						}	
					}

					$this->nConCit=Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cPrgCre);
					$lnCobraProcedimiento=($lcCodigoEspecialidad==$this->cEspecialidadCobra) ? 1 : 0;
					$lcCiePrincipalPos=isset($laProcedimientos['CIEJUSTIFICAPOS']) ? $laProcedimientos['CIEJUSTIFICAPOS'] : '';
					$lcDescCiePrincipalPos=isset($laProcedimientos['DESCRIPCIONCIEJUSTIFICAPOS']) ? $laProcedimientos['DESCRIPCIONCIEJUSTIFICAPOS'] : '';
					$lcRelacionado1Pos=isset($laProcedimientos['CIEREL1JUSTIFICAPOS']) ? trim($laProcedimientos['CIEREL1JUSTIFICAPOS']) : '';
					$lcDescRelacionado1Pos=isset($laProcedimientos['DESCRIPCIONCIEREL1JUSTIFICAPOS']) ? trim($laProcedimientos['DESCRIPCIONCIEREL1JUSTIFICAPOS']) : '';
					$lcRelacionado2Pos=isset($laProcedimientos['CIEREL2JUSTIFICAPOS']) ? trim($laProcedimientos['CIEREL2JUSTIFICAPOS']) : '';
					$lcDescRelacionado2Pos=isset($laProcedimientos['DESCRIPCIONCIEREL2JUSTIFICAPOS']) ? trim($laProcedimientos['DESCRIPCIONCIEREL2JUSTIFICAPOS']) : '';
					$lcObservacionesPos=isset($laProcedimientos['OBSJUSTIFICAPOS']) ? trim($laProcedimientos['OBSJUSTIFICAPOS']) : '';
					$lcEsHemocomponente=isset($laProcedimientos['HEMOCOMPONENTE']) ? $laProcedimientos['HEMOCOMPONENTE'] : '';
					$lcSolicitadoNopos=isset($laProcedimientos['SOLICITADO']) ? $laProcedimientos['SOLICITADO'] : '';
					$lcResumenNopos=isset($laProcedimientos['RESUMEN']) ? $laProcedimientos['RESUMEN'] : '';
					$lcRiesgoInminenteNopos=isset($laProcedimientos['RIESGO']) ? $laProcedimientos['RIESGO'] : '';
					$lcTipoRiesgoNopos=isset($laProcedimientos['TIPOR']) ? ($laProcedimientos['TIPOR']=='1' ? 'S' : 'N') : '';
					$lcDiagnosticoNopos=isset($laProcedimientos['DIAGNOSTICONP']) ? $laProcedimientos['DIAGNOSTICONP'] : '';
					$lcCodigoPosNopos=isset($laProcedimientos['CODIGOPOS']) ? $laProcedimientos['CODIGOPOS'] : '';
					$lnCantidadPosNopos=isset($laProcedimientos['CANTIDADPOS']) ? intval($laProcedimientos['CANTIDADPOS']) : 0;
					$lcRespuestaPosNopos=isset($laProcedimientos['RESPUESTA']) ? $laProcedimientos['RESPUESTA'] : '';
					$lcObjetivoNopos=isset($laProcedimientos['OBJETIVO']) ? $laProcedimientos['OBJETIVO'] : '';
					$lcBibliografiaNopos=isset($laProcedimientos['BIBLIOGRAFIA']) ? $laProcedimientos['BIBLIOGRAFIA'] : '';
					$lcEfectosSecundariosNopos=isset($laProcedimientos['PACIENTE']) ? $laProcedimientos['PACIENTE'] : '';
					$lcEntidadNopos=isset($laProcedimientos['ENTIDADNOPOS']) ? $laProcedimientos['ENTIDADNOPOS'] : '';
					$lcCupsSiempreNopos=isset($laProcedimientos['SIEMPRENOPOS']) ? $laProcedimientos['SIEMPRENOPOS'] : '';

					if ($lnConsecutivoBancoSangre==0 && ($lcCodigoEspecialidad==$this->cEspecialidadBancoSangre)){
						$lnConsecutivoBancoSangre = $this->consultaConsecutivoOrden($lnCodigoConsecBanco);
					}
					if ($lnConsecutivoOrden==0){ $lnConsecutivoOrden = $this->consultaConsecutivoOrden($lnCodigoConsecOrden); }

					$laDatosProcedimiento=[
						'nroingreso' => $this->aIngreso['nIngreso'],
						'codigocups' => $lcCodigoProcedimiento,
						'descripcion' => $lcDescripcionProcedimiento,
						'codigoespecialidad' => $lcCodigoEspecialidad,
						'estadoordencups' => $lnEstadoOrden,
						'cantidadcups' => $lnCantidad,
						'posnopos' => $lcCupsPosNopos,
						'hexalis' => $lcCodigoHexalis,
						'consecutivoorden' => $lnConsecutivoOrden,
						'consecutivobancosangre' => $lnConsecutivoBancoSangre,
						'solicitudinterconsulta' => isset($laProcedimientos['SOLICITUDINTERCONSULTA']) ? $laProcedimientos['SOLICITUDINTERCONSULTA'] : 'S',
						'codigotipointerconsulta' => isset($laProcedimientos['CODIGOTIPOINTERCONSULTA']) ? $laProcedimientos['CODIGOTIPOINTERCONSULTA'] : 'O',
						'codigoprioridadinterconsulta' => isset($laProcedimientos['CODIGOPRIORIDADINTERCONSULTA']) ? $laProcedimientos['CODIGOPRIORIDADINTERCONSULTA'] : '',
						'cobrarcups' => $lnCobraProcedimiento,
						'programacreacion' => $this->cPrgCre,
						'tipoglucometria' => $lcTipomensaje,
						'eventoglucometria' => $lcTipoEvento,
						'validarglucometria' => false,
						'cieprincipalpos' => $lcCiePrincipalPos,
						'cierelacionado1pos' => $lcRelacionado1Pos,
						'cierelacionado2pos' => $lcRelacionado2Pos,
						'observacionespos' => $lcObservacionesPos,
					];

					if ($lcTipoRegistro=='GLUCO' || $lcTipoRegistro=='CUPS' || $lcTipoRegistro=='INTER'){
						$lcTabla = 'RIAORD';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

						$lcTabla = 'RIADET';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

						$lnViaIngreso=intval($this->aIngreso['cCodVia']);
						if (($lcTipoRegistro=='CUPS' && $lcCodigoEspecialidad==$this->cEspLaboratorioHexalis) || ($lcTipoRegistro=='GLUCO' && in_array($lnViaIngreso, [1,2]))){
							if (!in_array($lcCodigoProcedimiento, $this->aCupsCoagulacion)){
								$laCupsHexalis[]=[
									'codigohexalis'=>$lcCodigoHexalis,
									'descripcioncups'=>$lcDescripcionProcedimiento,
									'consecutivocita'=>$this->nConCit,
									'observaciones'=>$lcObservaciones,
									'linea'=>$lnConsecutivoLinea,
								];
							}	
						}
					}

					if($lcEsHemocomponente!=''){
						$lcTabla = 'BANSAC';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

						if(!empty($taHemocomponente) && $lcRegistroHemocomponente==''){
							$lcRegistroHemocomponente='H';
							$lnLongitud = 220;
							foreach($taHemocomponente as $laHemocomponente){
								$lcTabla='BANSAO';
								$lnLinea = 1;
								$lcDescripcion=isset($laHemocomponente['DESCRIPCION'])?$laHemocomponente['DESCRIPCION']:'';
								$lcDescripcion=!empty($lcDescripcion)?$lcDescripcion:'.';
								$laDatosProcedimiento['indicehemocomponente'] = $laHemocomponente['INDICE'];
								$laDatosProcedimiento['tipojushemocomponente'] = $laHemocomponente['TIPOJUSTIFICACION'];
								$laDatosProcedimiento['codigojushemocomponente'] = $laHemocomponente['CODIGOJUSTIFICACION'];
								$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
							}
						}
					}

					if (($lcTipoRegistro=='INTER') || (substr($lcCodigoProcedimiento, 0, 4)=='8904')){
						$lcTabla = 'INTCON';
						$lnLinea = 1;
						$lnLongitud = 220;
						$lcDescripcion = $lcObservaciones;
						if (!empty($lcDescripcion)){
							$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
						}else{
							$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
						}	

						$lcTabla = 'INTCON';
						$lnLinea = 600;
						$lnLongitud = 220;
						$lcDescripcion = isset($laProcedimientos['CODIGOPRIORIDADINTERCONSULTA']) ? $laProcedimientos['CODIGOPRIORIDADINTERCONSULTA'] : '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);

						$lcTabla = 'EVOLUCO';
						$lnLongitud = 220;
						$lnLinea = 1500;
						$lcDescripcion = ' INTERCONSULTA DE ' .$lcDescripcionProcedimiento;
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
					}

					if ($lcTipoRegistro=='GLUCO' || $lcTipoRegistro=='CUPS'){
						$lcTabla = 'EVOLUCO';
						$lnLongitud = 220;
						$lnLinea = 1600;
						$loAplicacionFunciones = new AplicacionFunciones();
						$lcFechaHoraGlucometria = '. Fecha: ' .$loAplicacionFunciones->formatFechaHora('fecha', $this->cFecCre, '/', '', '')
						.' Hora: ' .$loAplicacionFunciones->formatFechaHora('hora', $this->cHorCre, '', ':', '');
						$lcDescripcion = $this->cChrEnter .$lcDescripcionProcedimiento .$lcFechaHoraGlucometria;
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
					}

					if ($lcObservaciones!=''){
						$lcTabla = 'EVOLUCO';
						$lnLongitud = 220;
						$lnLinea = $lcTipoRegistro=='INTER' ? 1501 : 1601;
						$lcDescripcion = $lcObservaciones;
						$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
					}

					if ($lcTipoRegistro!='INTER'){
						if ($lnCobraProcedimiento==1){
							$this->cobraProcedimiento($laDatosProcedimiento);
						}

						if ($lcObservaciones!='' || $lcCiePrincipalPos!=''){
							if (substr($lcCodigoProcedimiento, 0, 4)!='8904'){
								$lcTabla = 'ORDPRO';
								$lnLinea = 1;
								$lnLongitud = 220;
								$lcDescripcion = $lcObservaciones!='' ? $lcObservaciones : '';

								if ($lcCiePrincipalPos!=''){
									$lcRelacionado1=$lcRelacionado2='';
									if ($lcRelacionado1Pos!=''){ $lcRelacionado1='Diagnóstico Relacionado 1: ' .$lcRelacionado1Pos .'-'.$lcDescRelacionado1Pos .$this->cChrEnter; }
									if ($lcRelacionado2Pos!=''){ $lcRelacionado2='Diagnóstico Relacionado 2: ' .$lcRelacionado2Pos .'-'.$lcDescRelacionado2Pos .$this->cChrEnter; }
									$lcDescripcion = $lcDescripcion .$this->cChrEnter .'Justificación procedimiento POS: ' .$this->cChrEnter
									.'Diagnóstico Justificación: ' .$lcCiePrincipalPos .'-'.$lcDescCiePrincipalPos .$this->cChrEnter
									.$lcRelacionado1.$lcRelacionado2
									.'Justificación: ' .$lcObservacionesPos;
								}
								$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
							}	
						}
					}

					if ($lcTipoRegistro=='GLUCO' && $lnGuardaGluc==0 && $lcGlucometriaSiNo=='S' &&
						$this->cCupsGlucometria==$lcCodigoProcedimiento){
						$lnGuardaGluc = 1;
						$this->fnEnviarMensajeGlucometria($laDatosProcedimiento);
					}

					if ($lcCiePrincipalPos!=''){
						$lcTabla = 'AUTANX';
						$lnLinea = 0;
						$lcDescripcion = '';
						$this->InsertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
					}

					if ($lcObservacionesPos!=''){
						$lcTabla = 'AUTAND';
						$lnLinea = 1;
						$lnLongitud = 220;
						$lcDescripcion = $lcObservacionesPos;
						$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $laDatosProcedimiento);
					}

					if ($lcEnviaAgfa=='S'){
						if ($lcSeEnvioAdt==''){
							$lcSeEnvioAdt='S';
							$laDatosPaciente=[
								'nroingreso' => $this->aIngreso['nIngreso'],
								'codigocups' => $lcCodigoProcedimiento,
								'descripcioncups' => $lcDescripcionProcedimiento,
								'codigoespecialidad' => $lcCodigoEspecialidad,
								'modelo' => $lcModeloEquipo,
								'tipo' => $lcTipomensaje,
								'evento' => $lcTipoEvento,
								'salaagfa' => $lcSalaAgfa,
								'observaciones' => '',
							];
							$this->fnEnviarMensajeAgfa($laDatosPaciente);
						}
						$lcSalaAgfa=$this->consultaSalaAgfa($lcCodigoProcedimiento,$lcCodigoEspecialidad,$this->aIngreso['cCodVia']);

						$laDatosCup=[
							'nroingreso' => $this->aIngreso['nIngreso'],
							'codigocups' => $lcCodigoProcedimiento,
							'descripcioncups' => $lcDescripcionProcedimiento,
							'codigoespecialidad' => $lcCodigoEspecialidad,
							'modelo' => $lcModeloEquipo,
							'tipo' => 'ORM',
							'evento' => 'O01',
							'salaagfa' => $lcSalaAgfa,
							'observaciones' => $lcObservaciones,
						];
						$this->fnEnviarMensajeAgfa($laDatosCup);
					}

					if ($lcCodigoProcedimiento==$this->cCupsGasesArteriales && $lcCodigoEspecialidad==$this->cEspecialidadGasesArteriales){
						$laDatosCup=[
							'nroingreso' => $this->aIngreso['nIngreso'],
							'codigocups' => '',
							'descripcioncups' => $lcDescripcionProcedimiento,
							'codigoespecialidad' => $lcCodigoEspecialidad,
							'modelo' => 'RAPID',
							'tipo' => 'ADT',
							'evento' => 'A02',
							'observaciones' => $lcObservaciones,
							'consecutivoorden' => $lnConsecutivoOrden,
							'consecutivocita' => 0,
						];
						$this->fnEnviarMensajeGasesArteriales($laDatosCup);

						$laDatosCup=[
							'nroingreso' => $this->aIngreso['nIngreso'],
							'codigocups' => $lcCodigoProcedimiento,
							'descripcioncups' => $lcDescripcionProcedimiento,
							'codigoespecialidad' => $lcCodigoEspecialidad,
							'modelo' => 'RAPID',
							'tipo' => 'ORM',
							'evento' => 'O01',
							'observaciones' => $lcObservaciones,
							'consecutivoorden' => $lnConsecutivoOrden,
							'consecutivocita' => $this->nConCit,
						];
						$this->fnEnviarMensajeGasesArteriales($laDatosCup);
					}
				}

				if (is_array($laCupsHexalis)){
					if (count($laCupsHexalis)>0 && !empty($laCupsHexalis)){
						$lnLinea='linea';
						$laLaboratorios=$this->agruparLaboratorios($laCupsHexalis,$lnLinea);
						$this->fnEnviarMensajeHexalis($laLaboratorios);
					}
				}
			}
		}
	}

	public function agruparLaboratorios($array,$groupkey)
	{
		if (count($array)>0)
		{
			$keys = array_keys($array[0]);
			$removekey = array_search($groupkey, $keys);
			if ($removekey===false) {
				return array("Clave \"$groupkey\" no existe");
			} else {
				unset($keys[$removekey]);
			}
			$groupcriteria = array();
			$return=array();

			foreach($array as $value) {
				$item=null;
				foreach ($keys as $key) {
					$item[$key] = $value[$key];
				}
				$busca = array_search($value[$groupkey], $groupcriteria);
				if ($busca === false) {
					$groupcriteria[]=$value[$groupkey];
					$return[]=array($groupkey=>$value[$groupkey],'grupolinea'=>array());
					$busca=count($return)-1;
				}
				$return[$busca]['grupolinea'][]=$item;
			}
			return $return;
		} else {
			return array();
		}
	}

	public function fnconsultaUbicacionUrgencias()
	{
		$lcUbicacionUrgencias='';
		$lcPlan=trim($this->aIngreso['cPlan']);

		if (!empty($lcPlan)){
			$laParametros = $this->oDb
			->select('IN4CON INDICADOR')
			->from('FACPLNC')
			->where('PLNCON', '=', $lcPlan)
			->get('array');
			if($this->oDb->numRows()>0){
				$lcUbicacionUrgencias=intval($laParametros['INDICADOR'])==1 ? 'M.P.P. - ' : (intval($laParametros['INDICADOR'])==2 ? 'P.O.S. - ' :'');
			}
		}
		unset($laParametros);
		return $lcUbicacionUrgencias;
	}

			//$lcViaIngreso=!empty($lcViaIngreso)?$lcViaIngreso:$this->aIngreso['cCodVia'];
		//$lcNombreOrdena=!empty($lcNombreOrdena)?$lcNombreOrdena:trim($this->cApellidoUsuarioHl7);
		//$lcUsuarioOrdena=!empty($lcUsuarioOrdena)?$lcUsuarioOrdena:trim($this->cUsuCre);

	public function fnEnviarMensajeHexalis($laOrdenCup=[])
	{
		$lcUbicacionPaciente='';
		$lnNumeroIngreso=isset($laOrdenCup[0]['grupolinea'][0]['numeroingreso']) ? $laOrdenCup[0]['grupolinea'][0]['numeroingreso'] : $this->aIngreso['nIngreso'];
		$lcViaIngreso=isset($laOrdenCup[0]['grupolinea'][0]['viaingreso']) ? $laOrdenCup[0]['grupolinea'][0]['viaingreso'] : $this->aIngreso['cCodVia'];
		$lcNombreOrdena=isset($laOrdenCup[0]['grupolinea'][0]['nombreusuarioordena']) ? $laOrdenCup[0]['grupolinea'][0]['nombreusuarioordena'] : trim($this->cApellidoUsuarioHl7);
		$lcUsuarioOrdena=isset($laOrdenCup[0]['grupolinea'][0]['codigousuarioordena']) ? $laOrdenCup[0]['grupolinea'][0]['codigousuarioordena'] : trim($this->cUsuCre);
		$lcViaHexalis=$this->oDb->obtenerTabMae1('DE2TMA', 'HEXALIS', "cl1tma='VIAING' AND cl2tma='$lcViaIngreso' AND ESTTMA=''", null, '');

		if (intval($lcViaIngreso)==1){
			$lcUbicacionPaciente=$this->fnconsultaUbicacionUrgencias();
		}
		$lcModelo='HEXALIS';
		$lcTipoMensaje='ORM';
		$lcEvento='O01';
		$lnIngreso=$lnNumeroIngreso;
		$lnCita=$lnNumOrden=0;
		$lcMedico=$lcRegMedico='';
		$lnEnviar=1;
		$laDatosAdicionales=[
				'viaingresohexalis'=>trim($lcViaHexalis),
				'apellidosnombresusuario'=>$lcNombreOrdena,
				'usuarioordena'=>$lcUsuarioOrdena,
				'ubicacionpaciente'=>$lcUbicacionPaciente,
			];

		foreach($laOrdenCup as $laCups){
			$lcRespuesta = HL7_Enviar::fnGenerarEnviarHL7(
				$lcModelo,
				$lcTipoMensaje,
				$lcEvento,
				$lnIngreso,
				$lnCita,
				$lnNumOrden,
				$laCups,
				$lcRegMedico,
				$lcMedico,
				$lnEnviar,
				$laDatosAdicionales
			);
		}
	}

	public function fnEnviarMensajeGasesArteriales($taDatosRecibe=[])
	{
		$lcModelo = $taDatosRecibe['modelo'];
		if(HL7_Enviar::fnSeDebeEnviar($lcModelo)){
			$tcRegMedico=$this->cRegMed;
			$tcMedico=$this->cApellidoUsuarioHl7;
			$tnEnviar=1;

			$laOtrosParametros=[
				'codigoespecialidad'=>$taDatosRecibe['codigoespecialidad'],
				'descripcioncups'=>$taDatosRecibe['descripcioncups'],
				'observaciones'=>$taDatosRecibe['observaciones'],
				'usuarioordena'=>$this->cUsuCre,
			];

			$lcRespuesta = HL7_Enviar::fnGenerarEnviarHL7(
				$lcModelo,
				$taDatosRecibe['tipo'],
				$taDatosRecibe['evento'],
				$taDatosRecibe['nroingreso'],
				$taDatosRecibe['consecutivocita'],
				$taDatosRecibe['consecutivoorden'],
				$taDatosRecibe['codigocups'],
				$tcRegMedico,
				$tcMedico,
				$tnEnviar,
				$laOtrosParametros
			);
		}
	}

	public function fnEnviarMensajeAgfa($taDatosRecibe=[])
	{
		if (HL7_Enviar::fnSeDebeEnviar($taDatosRecibe['modelo'])) {
			$lcError = $lcResultado = '';
			$tnNumOrden=0;
			$tcRegMedico='';
			$tcMedico=$this->cApellidoUsuarioHl7;
			$tnEnviar=1;
			$laOtrosParametros=[
				'codigoespecialidad'=>$taDatosRecibe['codigoespecialidad'],
				'descripcioncups'=>$taDatosRecibe['descripcioncups'],
				'salarealiza'=>$taDatosRecibe['salaagfa'],
				'observaciones'=>$taDatosRecibe['observaciones'],
				'usuarioordena'=>$this->cUsuCre,
			];
			$lcRespuesta = HL7_Enviar::fnGenerarEnviarHL7(
				$taDatosRecibe['modelo'],
				$taDatosRecibe['tipo'],
				$taDatosRecibe['evento'],
				$taDatosRecibe['nroingreso'],
				$this->nConCit,$tnNumOrden,
				$taDatosRecibe['codigocups'],
				$tcRegMedico,
				$tcMedico,
				$tnEnviar,
				$laOtrosParametros
			);
		}
	}

	public function fnEnviarMensajeGlucometria($taDatosGlucometria=[])
	{
		$lcModelo = 'ROCHEGLC';
		if(HL7_Enviar::fnSeDebeEnviar($lcModelo)){

			$tbValidarGluc = $taDatosGlucometria['validarglucometria'];
			if ($tbValidarGluc){
				$laGlucometrias = $this->oDb->select('COUNT(*) CUENTA')
					->tabla('RIAORD A')
					->where('A.NINORD','=',$taDatosGlucometria['nroingreso'])
					->where('A.COAORD','=',$taDatosGlucometria['codigocups'])
					->where('A.ESTORD','=',8)
					->get('array');
				if($laGlucometrias['CUENTA']>0){
					return false;
				}
			}
			$lnConCit=$lnNumOrden=0;
			$lcRegMedico=$lcMedico='';
			$lnEnviar=1;
			$lcRespuesta = HL7_Enviar::fnGenerarEnviarHL7(
				$lcModelo,
				$taDatosGlucometria['tipoglucometria'],
				$taDatosGlucometria['eventoglucometria'],
				$taDatosGlucometria['nroingreso'],
				$lnConCit,
				$lnNumOrden,
				$taDatosGlucometria['codigocups'],
				$lcRegMedico,
				$lcMedico,
				$lnEnviar
			);
		}
	}

	public function estadoProcedimiento($tcCodigoCups='')
	{
		$lnEstado=8;
		$laParametros = $this->oDb
			->select('TRIM(RF1CUP) REFERENCIA1, TRIM(RF3CUP) REFERENCIA3, TRIM(RIPCUP) PAQUETE')
			->from('RIACUP')
			->where('CODCUP', '=', $tcCodigoCups)
			->get('array');
		if (is_array($laParametros) && count($laParametros)>0){
			if ($laParametros['REFERENCIA1']=='PAQUET' || $laParametros['REFERENCIA1']=='CIRUG.' || $laParametros['REFERENCIA1']=='S.AMBU' || $laParametros['REFERENCIA3']=='HEMODI' || $laParametros['PAQUETE']=='P')
			{
				$lnEstado=13;
			}
		}
		unset($laParametros);
		return $lnEstado;
	}

	public function consultaSalaAgfa($tcCodigoCups='', $tcEspecialidad='', $tcViaIngreso='')
	{
		$lcSalaAgfa='';
		$lcSalaAgfa=$this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL1TMA='4' AND CL2TMA='' AND ESTTMA='' AND OP3TMA='$tcEspecialidad'", null, '');
		if ($lcSalaAgfa==''){
			$lcSalaAgfa=$this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL1TMA='4' AND CL2TMA='HEMODI' AND ESTTMA='' AND OP3TMA='$tcEspecialidad'", null, '');
		}

		if ($lcSalaAgfa==''){
			$laParametros = $this->oDb
			->select('TRIM(RF1CUP) REFERENCIA1, TRIM(RF2CUP) REFERENCIA2, TRIM(RF3CUP) REFERENCIA3')
			->from('RIACUP')
			->where('CODCUP', '=', $tcCodigoCups)
			->get('array');

			if (is_array($laParametros) && count($laParametros)>0){
				$lcReferencia1= $laParametros['REFERENCIA1'];
				$lcReferencia2= $laParametros['REFERENCIA2'];
				$lcReferencia3= $laParametros['REFERENCIA3'];
				$lcSalaAgfa=$this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL2TMA='$lcReferencia1' AND CL3TMA='$lcReferencia2' AND CL4TMA='$lcReferencia3' AND OP2TMA='$tcViaIngreso' AND ESTTMA=''", null, '');

				if ($lcSalaAgfa==''){
					$lcSalaAgfa=$this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL2TMA='$lcReferencia1' AND CL3TMA='$lcReferencia2' AND CL4TMA='$lcReferencia3' AND ESTTMA=''", null, '');
				}
			}
		}

		if ($lcSalaAgfa==''){
			$lcSalaAgfa=$this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL1TMA='4' AND CL2TMA='SINSALA' AND ESTTMA=''", null, '');
		}
		return $lcSalaAgfa;
	}

	public function consultaSiempreNopos($tcCodigoCups='')
	{
		$lcSiempreNopos='';
		$lcSiempreNopos = $this->oDb->obtenerTabmae1('DE1TMA', 'NOPOS', "CL1TMA='MIPRES' AND CL2TMA='SIEMPRE' AND CL3TMA<>'' AND ESTTMA='' AND DE1TMA='$tcCodigoCups'", null, '');
		return $lcSiempreNopos;
	}

	public function verificaenviaagfa($tcCodigoCups='', $tcEspecialidad='')
	{
		$lcEnviaAgfa='';
		$lnNoAplicaCups=0;
		$laParametros = $this->oDb
			->select('TRIM(RF1CUP) REFERENCIA1, TRIM(RF3CUP) REFERENCIA3, TRIM(RIPCUP) PAQUETE')
			->from('RIACUP')
			->where('CODCUP', '=', $tcCodigoCups)
			->get('array');
		if (is_array($laParametros) && count($laParametros)>0){
			if ($laParametros['REFERENCIA1']=='PAQUET' || $laParametros['REFERENCIA1']=='CIRUG.' ||
				$laParametros['REFERENCIA1']=='S.AMBU' || $laParametros['PAQUETE']=='P')
			{
				$lnNoAplicaCups=1;
			}
			if ($lnNoAplicaCups==0){
				$laEspecialidadAgility = $this->oDb->select(trim('DE2TMA'))->from('TABMAE')->where(['TIPTMA'=>'AGFASO','CL1TMA'=>'24','ESTTMA'=>'',])->get("array");
				$lcListaEspecialidadAgility = trim($laEspecialidadAgility['DE2TMA']);
				$laEspecialidadePemitidas = explode(',',$lcListaEspecialidadAgility);

				if (in_array($tcEspecialidad, $laEspecialidadePemitidas)){
					$lcCupsExcepcion = $this->oDb->obtenerTabmae1('DE2TMA', 'AGFASO', "CL1TMA='2' AND CL3TMA='N' AND ESTTMA='' AND DE2TMA='$tcCodigoCups'", null, '');
					$lcEnviaAgfa = trim($lcCupsExcepcion)=='' ? 'S' : '';
				}

				if ($lcEnviaAgfa==''){
					$lcCupsExcepcion = $this->oDb->obtenerTabmae1('CL2TMA', 'AGFASO', "CL1TMA='APLICUP' AND CL2TMA='$tcCodigoCups' AND ESTTMA=''", null, '');
					$lcEnviaAgfa = trim($lcCupsExcepcion)!='' ? 'S' : '';
				}
			}
		}
		unset($laParametros);
		return $lcEnviaAgfa;
	}


	public function cobraProcedimiento($taDatosCobrar=[])
	{
		$laData = [
			'ingreso'       => $this->aIngreso['nIngreso'],
			'numIdPac'      => $this->aIngreso['nNumId'],
			'codCup'        => $taDatosCobrar['codigocups'],
			'codVia'        => $this->aIngreso['cCodVia'],
			'codPlan'       => $this->aIngreso['cPlan'],
			'regMedOrdena'  => $this->cRegMed,
			'regMedRealiza' => $this->cRegMed,
			'espMedRealiza' => $taDatosCobrar['codigoespecialidad'],
			'secCama'       => trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
			'cnsCita'       => $this->nConCit,
			'portatil'      => '',
		];
		$loCobros = new Cobros();
		$lbRet = $loCobros->cobrarProcedimiento($laData);
	}


	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnLinea=1, $taDatosComunes=[])
	{
		$laChar = AplicacionFunciones::mb_str_split(trim($tcTexto),$tnLongitud);
		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnLinea, $taDatosComunes);
					$tnLinea++;
				}
			}
		}
	}

	function InsertarRegistro($tcTabla='', $tcDescripcion='', $tnLinea=0, $taDatosComunes=[])
	{
		switch (true){
			case $tcTabla=='RIAORD' :
				$this->aRIAORD[]=[
					'TIDORD'=>$this->aIngreso['cTipId'],
					'NIDORD'=>$this->aIngreso['nNumId'],
					'EVOORD'=>$this->nConEvo,
					'NINORD'=>$this->aIngreso['nIngreso'],
					'CCIORD'=>$this->nConCit,
					'CODORD'=>$taDatosComunes['codigoespecialidad'],
					'COAORD'=>$taDatosComunes['codigocups'],
					'RMEORD'=>$this->cRegMed,
					'FCOORD'=>$this->cFecCre,
					'FRLORD'=>$this->cFecCre,
					'HOCORD'=>$this->cHorCre,
					'ESTORD'=>$taDatosComunes['estadoordencups'],
					'ENTORD'=>$this->aIngreso['nEntidad'],
					'VIAORD'=>$this->aIngreso['cCodVia'],
					'PLAORD'=>$this->aIngreso['cPlan'],
					'SCAORD'=>$this->aIngreso['cSeccion'],
					'NCAORD'=>$this->aIngreso['cHabita'],
					'AUTORD'=>$taDatosComunes['posnopos'],
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
					'CCIDET'=>$this->nConCit,
					'CUPDET'=>$taDatosComunes['codigocups'],
					'FERDET'=>$this->cFecCre,
					'HRRDET'=>$this->cHorCre,
					'ESTDET'=>$taDatosComunes['estadoordencups'],
					'MARDET'=>$taDatosComunes['cobrarcups'],
					'FL2DET'=>$taDatosComunes['consecutivoorden'],
					'USRDET'=>$this->cUsuCre,
					'PGMDET'=>$this->cPrgCre,
					'FECDET'=>$this->cFecCre,
					'HORDET'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='INTCON' :
				$this->aINTCON[]=[
					'INGINT'=>$this->aIngreso['nIngreso'],
					'CONINT'=>$this->nConEvo,
					'CORINT'=>$this->nConCit,
					'CUPINT'=>$taDatosComunes['codigocups'],
					'SORINT'=>$taDatosComunes['solicitudinterconsulta'],
					'OTCINT'=>$taDatosComunes['codigotipointerconsulta'],
					'CNLINT'=>$tnLinea,
					'DESINT'=>$tcDescripcion,
					'USRINT'=>$this->cUsuCre,
					'PGMINT'=>$this->cPrgCre,
					'FECINT'=>$this->cFecCre,
					'HORINT'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ORDPRO' :
				$this->aORDPRO[]=[
					'INGPRO'=>$this->aIngreso['nIngreso'],
					'CONPRO'=>$this->nConEvo,
					'CORPRO'=>$this->nConCit,
					'CUPPRO'=>$taDatosComunes['codigocups'],
					'CNLPRO'=>$tnLinea,
					'DESPRO'=>$tcDescripcion,
					'USRPRO'=>$this->cUsuCre,
					'PGMPRO'=>$this->cPrgCre,
					'FECPRO'=>$this->cFecCre,
					'HORPRO'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='EVOLUC' :
				$this->aEVOLUC[]=[
					'NINEVL'=>$this->aIngreso['nIngreso'],
					'CONEVL'=>$this->nConEvo,
					'CNLEVL'=>$tnLinea,
					'DESEVL'=>$tcDescripcion,
					'USREVL'=>$this->cUsuCre,
					'PGMEVL'=>$this->cPrgCre,
					'FECEVL'=>$this->cFecCre,
					'HOREVL'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='EVOLUCO' :
				$this->aEVOLUCO[]=[
					'NINEVL'=>$this->aIngreso['nIngreso'],
					'CONEVL'=>$this->nConEvo,
					'CCIEVL'=>$this->nConCit,
					'CNLEVL'=>$tnLinea,
					'PROEVL'=>$taDatosComunes['codigocups'],
					'DESEVL'=>$tcDescripcion,
					'USREVL'=>$this->cUsuCre,
					'PGMEVL'=>$this->cPrgCre,
					'FECEVL'=>$this->cFecCre,
					'HOREVL'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIANUTR' :
					$this->aRIANUTR[]=[
					'TIDNTR'=>$this->aIngreso['cTipId'],
					'NIDNTR'=>$this->aIngreso['nNumId'],
					'NINNTR'=>$this->aIngreso['nIngreso'],
					'CONNTR'=>$this->nConEvo,
					'RMENTR'=>$this->cRegMed,
					'FEENTR'=>$this->cFecCre,
					'HRRNTR'=>$this->cHorCre,
					'ESTNTR'=>8,
					'SCANTR'=>$this->aIngreso['cSeccion'],
					'NCANTR'=>$this->aIngreso['cHabita'],
					'USRNTR'=>$this->cUsuCre,
					'PGMNTR'=>$this->cPrgCre,
					'FECNTR'=>$this->cFecCre,
					'HORNTR'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIANUDT' :
					$this->aRIANUTRD[]=[
					'NINNDT'=>$this->aIngreso['nIngreso'],
					'CONNDT'=>$this->nConEvo,
					'RMENDT'=>$this->cRegMed,
					'FEENDT'=>$this->cFecCre,
					'HRRNDT'=>$this->cHorCre,
					'ESTNDT'=>8,
					'SCANDT'=>$this->aIngreso['cSeccion'],
					'NCANDT'=>$this->aIngreso['cHabita'],
					'DMDNDT'=>$taDatosComunes['tipodieta'],
					'CONSDT'=>$tnLinea,
					'ODDNTR'=>$tcDescripcion,
					'USRNDT'=>$this->cUsuCre,
					'PGMNDT'=>$this->cPrgCre,
					'FECNDT'=>$this->cFecCre,
					'HORNDT'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='HISCLI' :
					$this->aHISCLI[]=[
					'INGHCL'=>$this->aIngreso['nIngreso'],
					'CEVHCL'=>$this->nConEvo,
					'INDHCL'=>69,
					'CLNHCL'=>$tnLinea,
					'DESHCL'=>$tcDescripcion,
					'USRHCL'=>$this->cUsuCre,
					'PGMHCL'=>$this->cPrgCre,
					'FECHCL'=>$this->cFecCre,
					'HORHCL'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAFARDO' :
					$this->aRIAFARDO[]=[
					'INGRDO'=>$this->aIngreso['nIngreso'],
					'CFRRDO'=>$this->nConsFormula,
					'CEVRDO'=>$this->nConEvo,
					'CUPRDO'=>$taDatosComunes['codigocups'],
					'ESTRDO'=>$taDatosComunes['estado_formula'],
					'DOSRDO'=>$taDatosComunes['dosis'],
					'UDORDO'=>$taDatosComunes['unidad_dosis'],
					'OBSRDO'=>$taDatosComunes['observaciones'],
					'ESCRDO'=>$taDatosComunes['estado_grabacion'],
					'REFRDO'=>$taDatosComunes['id'],
					'USRRDO'=>$this->cUsuCre,
					'PGMRDO'=>$this->cPrgCre,
					'FECRDO'=>$this->cFecCre,
					'HORRDO'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='AUTANX' :
					$this->aAUTANX[]=[
					'INGAAX'=>$this->aIngreso['nIngreso'],
					'CITAAX'=>$this->nConCit,
					'PROAAX'=>$taDatosComunes['codigocups'],
					'CNLAAX'=>1,
					'FORAAX'=>$this->cFecCre,
					'ESTAAX'=>$taDatosComunes['estadoordencups'],
					'PLAAAX'=>$this->aIngreso['cPlan'],
					'NITAAX'=>$this->aIngreso['nEntidad'],
					'DIAGAX'=>$taDatosComunes['cieprincipalpos'],
					'DI1GAX'=>$taDatosComunes['cierelacionado1pos'],
					'DI2GAX'=>$taDatosComunes['cierelacionado2pos'],
					'USRAAX'=>$this->cUsuCre,
					'PGMAAX'=>$this->cPrgCre,
					'FECAAX'=>$this->cFecCre,
					'HORAAX'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='AUTAND' :
					$this->aAUTAND[]=[
					'INGAAD'=>$this->aIngreso['nIngreso'],
					'INDAAD'=>1,
					'CITAAD'=>$this->nConCit,
					'PROAAD'=>$taDatosComunes['codigocups'],
					'FGRAAD'=>$this->cFecCre,
					'HGRAAD'=>$this->cHorCre,
					'CNLAAD'=>$tnLinea,
					'ESTAAD'=>$taDatosComunes['estadoordencups'],
					'PLAAAD'=>$this->aIngreso['cPlan'],
					'NITAAD'=>$this->aIngreso['nEntidad'],
					'DIAAAD'=>$taDatosComunes['cieprincipalpos'],
					'DR1AAD'=>$taDatosComunes['cierelacionado1pos'],
					'DR2AAD'=>$taDatosComunes['cierelacionado2pos'],
					'OBSAAD'=>$tcDescripcion,
					'USRAAD'=>$this->cUsuCre,
					'PGMAAD'=>$this->cPrgCre,
					'FECAAD'=>$this->cFecCre,
					'HORAAD'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='BANSAC' :
				$this->aBANSAC[]=[
					'INGBSC'=>$this->aIngreso['nIngreso'],
					'CORBSC'=>$taDatosComunes['consecutivobancosangre'],
					'CCIBSC'=>$this->nConCit,
					'EVOBSC'=>$this->nConEvo,
					'INDBSC'=>1,
					'LINBSC'=>1,
					'ESTBSC'=>str_pad(trim(strval($taDatosComunes['estadoordencups'])),2,'0', STR_PAD_LEFT),
					'DESBSC'=>$taDatosComunes['codigocups'],
					'FREBSC'=>$this->cFecCre,
					'HREBSC'=>$this->cHorCre,
					'USRBSC'=>$this->cUsuCre,
					'PGMBSC'=>$this->cPrgCre,
					'FECBSC'=>$this->cFecCre,
					'HORBSC'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='BANSAO' :
				$this->aBANSAO[]=[
					'INGBSO'=>$this->aIngreso['nIngreso'],
					'CORBSO'=>$taDatosComunes['consecutivobancosangre'],
					'INDBSO'=>$taDatosComunes['indicehemocomponente'],
					'EVOBSO'=>$this->nConEvo,
					'LINBSO'=>$tnLinea,
					'JUSBSO'=>$taDatosComunes['codigojushemocomponente'],
					'TJUBSO'=>$taDatosComunes['tipojushemocomponente'],
					'ESTBSO'=>str_pad(trim(strval($taDatosComunes['estadoordencups'])),2,'0', STR_PAD_LEFT),
					'DESBSO'=>$tcDescripcion,
					'USRBSO'=>$this->cUsuCre,
					'PGMBSO'=>$this->cPrgCre,
					'FECBSO'=>$this->cFecCre,
					'HORBSO'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='CUPELJUS' :
				$this->aCUPELJUS[]=[
					'INGCLJ'=>$this->aIngreso['nIngreso'],
					'CNSCLJ'=>$this->nConsNopos,
					'CCTCLJ'=>$this->nConCit,
					'TIPCLJ'=>$taDatosComunes['tiponopos'],
					'REFCLJ'=>$taDatosComunes['codigocups'],
					'CLNCLJ'=>$tnLinea,
					'INDCLJ'=>$taDatosComunes['indicenopos'],
					'CANCLJ'=>$taDatosComunes['cantidadcups'],
					'SOLCLJ'=>$taDatosComunes['solicitadonopos'],
					'RINCLJ'=>$taDatosComunes['riesgoinminente'],
					'RIGCLJ'=>$taDatosComunes['tiporiesgo'],
					'UNICLJ'=>$taDatosComunes['diagnosticonopos'],
					'CPOCLJ'=>$taDatosComunes['codigoposnopos'],
					'CAPCLJ'=>$taDatosComunes['cantidadposnopos'],
					'REPCLJ'=>$taDatosComunes['respuestaposnopos'],
					'OBJCLJ'=>$taDatosComunes['objetivonopos'],
					'PIFCLJ'=>$taDatosComunes['efectossecundarios'],
					'RHCCLJ'=>$tcDescripcion,
					'RMDCLJ'=>$this->cRegMed,
					'USRCLJ'=>$this->cUsuCre,
					'PGMCLJ'=>$this->cPrgCre,
					'FECCLJ'=>$this->cFecCre,
					'HORCLJ'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='NPSMPEP' :
				$this->aNPSMPEP[]=[
					'TRGNME'=>$taDatosComunes['registrocupsmipres'],
					'INGNME'=>$this->aIngreso['nIngreso'],
					'CNSMNE'=>$this->nConMipres,
					'TIPNME'=>$taDatosComunes['tipocupsmipres'],
					'CODNME'=>$taDatosComunes['codigocups'],
					'CANNME'=>$taDatosComunes['cantidadsinmipres'],
					'MPRNME'=>$taDatosComunes['numeromipres'],
					'CMPNME'=>$taDatosComunes['cantidadmipres'],
					'USUNME'=>$this->cUsuCre,
					'PRGNME'=>$this->cPrgCre,
					'FECNME'=>$this->cFecCre,
					'HORNME'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAFARM' :
				$this->aRIAFARM[]=[
					'INGFAR'=>$this->aIngreso['nIngreso'],
					'CDNFAR'=>$taDatosComunes['consecutivoformula'],
					'EVOFAR'=>$this->nConEvo,
					'TIDFAR'=>$this->aIngreso['cTipId'],
					'NIDFAR'=>$this->aIngreso['nNumId'],
					'ENTFAR'=>$this->aIngreso['nEntidad'],
					'SECFAR'=>$this->aIngreso['cSeccion'],
					'NUMFAR'=>$this->aIngreso['cHabita'],
					'RMEFAR'=>$this->cRegMed,
					'FEFFAR'=>$this->cFecCre,
					'HMFFAR'=>$this->cHorCre,
					'ESTFAR'=>$taDatosComunes['estadoformula'],
					'AUTFAR'=>'',
					'USRFAR'=>$this->cUsuCre,
					'PGMFAR'=>$this->cPrgCre,
					'FECFAR'=>$this->cFecCre,
					'HORFAR'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='FORMED' :
				$this->aFORMED[]=[
					'NINFMD'=>$this->aIngreso['nIngreso'],
					'CCOFMD'=>$taDatosComunes['consecutivoconsulta'],
					'CEVFMD'=>$this->nConEvo,
					'MEDFMD'=>$taDatosComunes['medicamento'],
					'ESTFMD'=>$taDatosComunes['estadoformulacion'],
					'DOSFMD'=>$taDatosComunes['dosis'],
					'DDOFMD'=>$taDatosComunes['unidaddosis'],
					'FREFMD'=>$taDatosComunes['frecuencia'],
					'DFRFMD'=>$taDatosComunes['unidadfrecuencia'],
					'VIAFMD'=>$taDatosComunes['unidadvia'],
					'DIAFMD'=>$taDatosComunes['diasusoantibiotico'],
					'CANFMD'=>$taDatosComunes['cantidaddiarianopos'],
					'DCAFMD'=>$taDatosComunes['cantidaddescripcion'],
					'OBSFMD'=>$taDatosComunes['observaciones'],
					'OBIFMD'=>$taDatosComunes['textoinmediato'],
					'UD1FMD'=>trim(strval($taDatosComunes['diasusoantibiotico'])),
					'ET1FMD'=>$taDatosComunes['suspenderet1'],
					'TANFMD'=>$taDatosComunes['tipoantibiotico'],
					'FIAFMD'=>$taDatosComunes['fechainiciaantibiotico'],
					'FSAFMD'=>$taDatosComunes['fechasuspendeantibiotico'],
					'USRFMD'=>$this->cUsuCre,
					'PGMFMD'=>$this->cPrgCre,
					'FECFMD'=>$this->cFecCre,
					'HORFMD'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAFARD' :
				$this->aRIAFARD[]=[
					'NINFRD'=>$this->aIngreso['nIngreso'],
					'CDNFRD'=>$this->nConsFormulaMed,
					'CCOFRD'=>0,
					'CEVFRD'=>$this->nConEvo,
					'MEDFRD'=>$taDatosComunes['medicamento'],
					'ESTFRD'=>$taDatosComunes['estadoformulaciondetallle'],
					'DOSFRD'=>$taDatosComunes['dosis'],
					'UDOFRD'=>$taDatosComunes['unidaddosis'],
					'FRCFRD'=>$taDatosComunes['frecuencia'],
					'UFRFRD'=>$taDatosComunes['unidadfrecuencia'],
					'VIAFRD'=>$taDatosComunes['unidadvia'],
					'OBSFRD'=>$taDatosComunes['observaciones'],
					'FEFFRD'=>$this->cFecCre,
					'HMFFRD'=>$this->cHorCre,
					'DADFRD'=>intval($taDatosComunes['diasusoantibiotico']),
					'AUTFRD'=>'',
					'JUSFRD'=>$taDatosComunes['posnopos'],
					'USRFRD'=>$this->cUsuCre,
					'PGMFRD'=>$this->cPrgCre,
					'FECFRD'=>$this->cFecCre,
					'HORFRD'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAFARI' :
				$this->aRIAFARI[]=[
					'INGRFI'=>$this->aIngreso['nIngreso'],
					'CCORFI'=>0,
					'CFORFI'=>$this->nConsFormulaMed,
					'CEVRFI'=>$this->nConEvo,
					'COMRFI'=>$taDatosComunes['medicamento'],
					'COIRFI'=>$tcDescripcion['insumo'],
					'CANRFI'=>$tcDescripcion['cantidad'],
					'ESTRFI'=>$taDatosComunes['estadoformulaciondetallle'],
					'UNIRFI'=>$tcDescripcion['unidad'],
					'TDORFI'=>$this->cTipoDocumentoInventario,
					'NDORFI'=>$this->cNroDocumentoInventario,
					'USCRFI'=>$this->cUsuCre,
					'PGCRFI'=>$this->cPrgCre,
					'FECRFI'=>$this->cFecCre,
					'HOCRFI'=>$this->cHorCre,
				];
				break;
					
			case $tcTabla=='RIAFARDA' :
				$this->aRIAFARDA[]=[
					'NINFRA'=>$this->aIngreso['nIngreso'],
					'CDNFRA'=>$this->nConsFormulaMed,
					'CCOFRA'=>0,
					'CEVFRA'=>$this->nConEvo,
					'MEDFRA'=>$taDatosComunes['medicamento'],
					'TMUNIA'=>$taDatosComunes['medicamentounirs'],
					'USCRMA'=>$this->cUsuCre,
					'PGCRMA'=>$this->cPrgCre,
					'FECRMA'=>$this->cFecCre,
					'HOCRMA'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ENADMMDT' :
				$this->aENADMMDT[]=[
					'INGADM'=>$this->aIngreso['nIngreso'],
					'CTUADM'=>$this->nConsFormulaMed,
					'CEVADM'=>$this->nConEvo,
					'CCOADM'=>$taDatosComunes['consecutivonuevo'],
					'NDOADM'=>$taDatosComunes['numerodosis'],
					'SCAADM'=>$this->aIngreso['cSeccion'],
					'NCAADM'=>$this->aIngreso['cHabita'],
					'MEDADM'=>$taDatosComunes['medicamento'],
					'ESTADM'=>$taDatosComunes['estadoformulaciondetallle'],
					'DOSADM'=>$taDatosComunes['dosis'],
					'DDOADM'=>$taDatosComunes['unidaddosis'],
					'FREADM'=>$taDatosComunes['frecuencia'],
					'DFRADM'=>$taDatosComunes['unidadfrecuencia'],
					'VIAADM'=>$taDatosComunes['unidadvia'],
					'NDFADM'=>$taDatosComunes['diasusoantibiotico'],
					'OBMADM'=>$taDatosComunes['observaciones'],
					'FEPADM'=>$taDatosComunes['fechaprogramacion'],
					'HDPADM'=>$taDatosComunes['horaprogramacion'],
					'FICAMD'=>$taDatosComunes['frecuenciainfusion'],
					'USFADM'=>$this->cUsuCre,
					'FEOADM'=>$this->cFecCre,
					'HDOADM'=>$this->cHorCre,
					'ESPAMD'=>$taDatosComunes['estadoprogramacion'],
					'USRADM'=>$this->cUsuCre,
					'PGMADM'=>$this->cPrgCre,
					'FECADM'=>$this->cFecCre,
					'HORADM'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ENADMMD' :
				$this->aENADMMD[]=[
					'INGADM'=>$this->aIngreso['nIngreso'],
					'CTUADM'=>$this->nConsFormulaMed,
					'CEVADM'=>$this->nConEvo,
					'CCOADM'=>$taDatosComunes['consecutivonuevo'],
					'NDOADM'=>$taDatosComunes['numerodosis'],
					'SCAADM'=>$this->aIngreso['cSeccion'],
					'NCAADM'=>$this->aIngreso['cHabita'],
					'MEDADM'=>$taDatosComunes['medicamento'],
					'ESTADM'=>$taDatosComunes['estadoformulaciondetallle'],
					'DOSADM'=>$taDatosComunes['dosis'],
					'DDOADM'=>$taDatosComunes['unidaddosis'],
					'FREADM'=>$taDatosComunes['frecuencia'],
					'DFRADM'=>$taDatosComunes['unidadfrecuencia'],
					'VIAADM'=>$taDatosComunes['unidadvia'],
					'NDFADM'=>$taDatosComunes['diasusoantibiotico'],
					'OBMADM'=>$taDatosComunes['observaciones'],
					'FEPADM'=>$taDatosComunes['fechaprogramacion'],
					'HDPADM'=>$taDatosComunes['horaprogramacion'],
					'USFADM'=>$this->cUsuCre,
					'FEOADM'=>$this->cFecCre,
					'HDOADM'=>$this->cHorCre,
					'ESPAMD'=>$taDatosComunes['estadoprogramacion'],
					'FICAMD'=>$taDatosComunes['frecuenciainfusion'],
					'USRADM'=>$this->cUsuCre,
					'PGMADM'=>$this->cPrgCre,
					'FECADM'=>$this->cFecCre,
					'HORADM'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='MEDCONT' :
				$this->aMEDCONT[]=[
					'INGCON'=>$this->aIngreso['nIngreso'],
					'CFMCON'=>$this->nConsFormulaMed,
					'CEVCON'=>$this->nConEvo,
					'CONCON'=>$taDatosComunes['consecutivoformato'],
					'MEDCON'=>$taDatosComunes['medicamento'],
					'SCACON'=>$this->aIngreso['cSeccion'],
					'NCACON'=>$this->aIngreso['cHabita'],
					'TIPCON'=>$taDatosComunes['solicituddevolucion'],
					'CANCON'=>$taDatosComunes['cantidad'],
					'MOTCON'=>$taDatosComunes['motivodevolucion'],
					'DIACON'=>$taDatosComunes['codigodiagnostico'],
					'DIGCON'=>$taDatosComunes['observaciones'],
					'DOTCON'=>$taDatosComunes['descripcionotro'],
					'OP1CON'=>$taDatosComunes['opcional1'],
					'OP3CON'=>$taDatosComunes['opcional3'],
					'OP4CON'=>$taDatosComunes['opcional4'],
					'OP5CON'=>$taDatosComunes['opcional5'],
					'OP6CON'=>$taDatosComunes['opcional6'],
					'USRCON'=>$this->cUsuCre,
					'PGMCON'=>$this->cPrgCre,
					'FECCON'=>$this->cFecCre,
					'HORCON'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='USOANT' :
				$this->aUSOANT[]=[
					'INGANT'=>$this->aIngreso['nIngreso'],
					'CFOANT'=>$this->nConsFormulaMed,
					'EVOANT'=>$this->nConEvo,
					'CNOANT'=>$this->nConsAntibiotico,
					'MEDANT'=>$taDatosComunes['medicamento'],
					'DOSANT'=>$taDatosComunes['dosis'],
					'UDOANT'=>$taDatosComunes['unidaddosis'],
					'FRCANT'=>$taDatosComunes['frecuencia'],
					'UFRANT'=>$taDatosComunes['unidadfrecuencia'],
					'VIAANT'=>$taDatosComunes['unidadvia'],
					'OBMANT'=>$taDatosComunes['observaciones'],
					'DIGANT'=>$taDatosComunes['usuantbdiagnosticoinfeccioso'],
					'DIOANT'=>$taDatosComunes['usuantbdiagnosticoanexo'],
					'OTRANT'=>$taDatosComunes['usuantbotrosdiagnosticos'],
					'TRAANT'=>$taDatosComunes['usuantbtipotratamiento'],
					'AJUANT'=>$taDatosComunes['usuantbajustes'],
					'OBSANT'=>$taDatosComunes['usuantbobservaciones'],
					'MUEANT'=>$taDatosComunes['usuantborigenmuestra'],
					'RESANT'=>$taDatosComunes['usuantbresultado'],
					'DIAANT'=>$taDatosComunes['diasusoantibiotico'],
					'ESTANT'=>'9',
					'FECANT'=>$this->cFecCre,
					'FEFANT'=>$this->nFechaFinAntibiotico,
					'HORANT'=>$this->cHorCre,
					'USUANT'=>$this->cUsuCre,
					'PRGANT'=>$this->cPrgCre,
				];
				break;

			case $tcTabla=='REINCA' :
				$this->aESTUDIANTE[]=[
					'INGRIC'=>$this->aIngreso['nIngreso'],
					'TIPRIC'=>'SO',
					'CONRIC'=>$this->nConEstudiante,
					'USRRIC'=>$this->cUsuCre,
					'PGMRIC'=>$this->cPrgCre,
					'FECRIC'=>$this->cFecCre,
					'HORRIC'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='REINDE' :
				$this->aESTUDIANTEDET[]=[
					'INGRID'=>$this->aIngreso['nIngreso'],
					'TIPRID'=>$this->cTipoEstudiante,
					'CONRID'=>$this->nConEstudiante,
					'CEXRID'=>$this->nConsCupsEstudiante,
					'CORRID'=>$this->cConsOrdEstudiante,
					'CLIRID'=>$tnLinea,
					'DIARID'=>$this->cDiagnosticosEstudiante,
					'DESRID'=>$tcDescripcion,
					'OP1RID'=>$this->cOpcional1Estudiante,
					'OP2RID'=>$this->cOpcional2Estudiante,
					'OP4RID'=>$this->nOpcional4Estudiante,
					'OP6RID'=>$this->cOpcional6Estudiante,
					'USRRID'=>$this->cUsuCre,
					'PGMRID'=>$this->cPrgCre,
					'FECRID'=>$this->cFecCre,
					'HORRID'=>$this->cHorCre,
				];
				break;
		}
	}

	private function guardarDatosOM($taDatosHC=[])
	{
		$lnConsecutivoInventario=0;
		if ($this->bReqAval) {
			$lcTabla = 'REINCA';
			foreach($this->aESTUDIANTE  as $laESTUDIANTE){
				$llResultado = $this->oDb->tabla($lcTabla)->insertar($laESTUDIANTE);
			}

			$lcTabla = 'REINDE';
			foreach($this->aESTUDIANTEDET  as $laESTUDIANTEDET){
				$llResultado = $this->oDb->tabla($lcTabla)->insertar($laESTUDIANTEDET);
			}
		}else{
			if(is_array($this->aEVOLUC) && count($this->aEVOLUC)>0){
				$lcTabla = 'EVOLUC';
				foreach($this->aEVOLUC  as $laEVOLUC){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laEVOLUC);
				}
			}

			if(is_array($this->aEVOLUCO) && count($this->aEVOLUCO)>0){
				$lcTabla = 'EVOLUCO';
				foreach($this->aEVOLUCO  as $laEVOLUCO){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laEVOLUCO);
				}
			}

			if(is_array($this->aRIAORD) && count($this->aRIAORD)>0){
				$lcTabla = 'RIAORD';
				foreach($this->aRIAORD  as $laRIAORD){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAORD);
				}
			}

			if(is_array($this->aRIADET) && count($this->aRIADET)>0){
				$lcTabla = 'RIADET';
				foreach($this->aRIADET  as $laRIADET){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIADET);
				}
			}

			if(is_array($this->aINTCON) && count($this->aINTCON)>0){
				$lcTabla = 'INTCON';
				foreach($this->aINTCON  as $laINTCON){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laINTCON);
				}
			}

			if(is_array($this->aORDPRO) && count($this->aORDPRO)>0){
				$lcTabla = 'ORDPRO';
				foreach($this->aORDPRO  as $laORDPRO){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laORDPRO);
				}
			}

			if(is_array($this->aRIAFARDO) && count($this->aRIAFARDO)>0){
				$lcTabla = 'RIAFARDO';
				foreach($this->aRIAFARDO  as $laRIAFARDO){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAFARDO);
				}
			}

			if(is_array($this->aRIANUTR) && count($this->aRIANUTR)>0){
				$lcTabla = 'RIANUTR';
				foreach($this->aRIANUTR  as $laRIANUTR){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIANUTR);
				}
			}

			if(is_array($this->aRIANUTRD) && count($this->aRIANUTRD)>0){
				$lcTabla = 'RIANUDT';
				foreach($this->aRIANUTRD  as $laRIANUTRD){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIANUTRD);
				}
				$this->ActualizaDatosNutricion();
			}

			if(is_array($this->aHISCLI) && count($this->aHISCLI)>0){
				$lcTabla = 'HISCLI';
				foreach($this->aHISCLI  as $laHISCLI){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laHISCLI);
				}
			}

			if(is_array($this->aAUTANX) && count($this->aAUTANX)>0){
				$lcTabla = 'AUTANX';
				foreach($this->aAUTANX  as $laAUTANX){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laAUTANX);
				}
			}

			if(is_array($this->aAUTAND) && count($this->aAUTAND)>0){
				$lcTabla = 'AUTAND';
				foreach($this->aAUTAND  as $laAUTAND){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laAUTAND);
				}
			}

			if(is_array($this->aBANSAC) && count($this->aBANSAC)>0){
				$lcTabla = 'BANSAC';
				foreach($this->aBANSAC  as $laBANSAC){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laBANSAC);
				}
			}

			if(is_array($this->aBANSAO) && count($this->aBANSAO)>0){
				$lcTabla = 'BANSAO';
				foreach($this->aBANSAO  as $laBANSAO){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laBANSAO);
				}
			}

			if(is_array($this->aCUPELJUS) && count($this->aCUPELJUS)>0){
				$lcTabla = 'CUPELJUS';
				foreach($this->aCUPELJUS  as $laCUPELJUS){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laCUPELJUS);
				}
			}

			if(is_array($this->aNPSMPEP) && count($this->aNPSMPEP)>0){
				$lcTabla = 'NPSMPEP';
				foreach($this->aNPSMPEP  as $laNPSMPEP){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laNPSMPEP);
				}
			}

			if(is_array($this->aRIAFARM) && count($this->aRIAFARM)>0){
				$lcTabla = 'RIAFARM';
				foreach($this->aRIAFARM  as $laRIAFARM){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAFARM);
				}
			}

			if(is_array($this->aFORMED) && count($this->aFORMED)>0){
				$lcTabla = 'FORMED';
				foreach($this->aFORMED  as $laFORMED){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laFORMED);
				}
			}

			if(is_array($this->aRIAFARD) && count($this->aRIAFARD)>0){
				$lcTabla = 'RIAFARD';
				foreach($this->aRIAFARD  as $laRIAFARD){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAFARD);
				}
			}
			
			if(is_array($this->aRIAFARDA) && count($this->aRIAFARDA)>0){
				$lcTabla = 'RIAFARDA';
				foreach($this->aRIAFARDA  as $laRIAFARDA){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAFARDA);
				}
			}

			if(is_array($this->aENADMMDT) && count($this->aENADMMDT)>0){
				$lcTabla = 'ENADMMDT';
				foreach($this->aENADMMDT  as $laENADMMDT){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laENADMMDT);
				}
			}

			if(is_array($this->aENADMMD) && count($this->aENADMMD)>0){
				$lcTabla = 'ENADMMD';
				foreach($this->aENADMMD  as $laENADMMD){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laENADMMD);
				}
			}

			if(is_array($this->aMEDCONT) && count($this->aMEDCONT)>0){
				$lcTabla = 'MEDCONT';
				foreach($this->aMEDCONT  as $laMEDCONT){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laMEDCONT);
				}
			}

			if(is_array($this->aUSOANT) && count($this->aUSOANT)>0){
				$lcTabla = 'USOANT';
				foreach($this->aUSOANT  as $laUSOANT){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laUSOANT);
				}
			}
			
			if(is_array($this->aRIAFARI) && count($this->aRIAFARI)>0){
				$lcTabla = 'RIAFARI';
				foreach($this->aRIAFARI  as $laRIAFARI){
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laRIAFARI);
					$lnConsecutivoInventario=$lnConsecutivoInventario+1;
					$this->inventariosInsumos($laRIAFARI,$lnConsecutivoInventario);
				}
			}
		}
	}

	public function inventariosInsumos($taDatosInv=[],$tnConsecutivoInv=0)
	{
		$lcAccion='01';
		$loInventario= new Inventarios();
		if (!empty($this->cNroDocumentoInventario)){
			$lcStProc='INVGA060CP';
			$lnCantidadDetalle=1;
			$laData =[
				'procedure' 	=>$lcStProc,
				'tipodocumento' =>$this->cTipoDocumentoInventario,
				'nrodocumento' 	=>$this->cNroDocumentoInventario,
				'consecutivo' 	=>$tnConsecutivoInv,
				'codigoinsumo'	=>$taDatosInv['COIRFI'],
				'cantidad'		=>$lnCantidadDetalle,
				'unidad'		=>$taDatosInv['UNIRFI'],
				'usuariocrea' 	=>$this->cUsuCre,
				'programacrea' 	=>$this->cPrgCre,
				'fechacrea' 	=>$this->cFecCre,
				'horacrea' 		=>$this->cHorCre,
				'accion' 		=>$lcAccion,
			];
			$lcRetornar = $loInventario->detalleTransaccion($laData);
		}
	}

	public function ActualizaDatosNutricion()
	{
		$lcTabla = 'RIANTC';
		$laDatos = [
			'connut'=>$this->nConEvo,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['NINNTC'=>$this->aIngreso['nIngreso'],])->actualizar($laDatos);
	}

	public function ObjObligatoriosOM()
	{
		return $this->aobjOblOM;
	}

	public function consultaConsecutivoOrden($tnCodigoConsecutivo=0){
		$llAsignado = false;
		$lnConsecutivo = 0;
		$lcPrograma = $this->cPrgCre;

		global $goDb;
		if(isset($goDb)){
			while($llAsignado==false){
				$lnConsecutivo = $goDb->obtenerConsecRiacon($tnCodigoConsecutivo, $lcPrograma);
				$llAsignado = ($lnConsecutivo>0);
			}
		}
		return $lnConsecutivo;
	}

	
}
