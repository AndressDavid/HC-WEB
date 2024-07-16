<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.HL7_Enviar.php';
require_once __DIR__ . '/class.OrdenesMedicas.php';
require_once __DIR__ . '/class.Cobros.php';

use NUCLEO\AplicacionFunciones;
use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\HL7_Enviar;
use NUCLEO\OrdenesMedicas;
use NUCLEO\Cobros;

class GrabarProcedimientos
{

	protected $aIngreso = [];
	protected $aDatosGasesArterialesAdt = [];
	protected $aDatosGasesArterialesOrm = [];
	protected $aDatosGlucometria = [];
	protected $aDatosLaboratorio = [];
	protected $aProcedimientosCoagulacion = [];
	protected $aViasIngresoLaboratorio = [];
	protected $aViasIngresoOrden = [];
	protected $aCentroCostoOrden = [];
	protected $aProcedimientosGlucometrias = [];
	protected $aProcedimientosLaboratorio = [];
	protected $aProcedimientosCobrar = [];
	protected $cFechaCreacion = '';
	protected $cHoraCreacion = '';
	protected $cUsuarioCreacion = '';
	protected $cProgramaCreacion = '';
	protected $cCupsGasesArteriales='';
	protected $cCupsGlucometria='';
	protected $cApellidoUsuarioHl7='';
	protected $cAccederGasesArteriales='';
	protected $cEspecialidadGasesArteriales='';
	protected $cEnviarGasesArteriales='';
	protected $cEspecialidadLaboratorio='';
	protected $nConsecutivoOrden=0;
	protected $oDatosIngreso = null;
	protected $aRIAORD = [];
	protected $aRIADET = [];
	protected $aCUPGRA = [];
	protected $aORDPRO = [];
	protected $oDb = null;

	protected $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
		'Consecutivo' => 0,
	];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->oDatosIngreso = new Ingreso();
		$this->crearParametrosIniciales();
	}

	function crearParametrosIniciales()
	{
		global $goDb;
		$laParametrosEnviar=[];

		$this->cCupsGasesArteriales=trim($goDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='RAPID' AND CL3TMA='CUPS' AND CL4TMA='GASES' AND ESTTMA=''", null, ''));
		$this->cEspecialidadGasesArteriales=trim($goDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='RAPID' AND CL3TMA='GASES' AND CL4TMA='ESPRLZ' AND ESTTMA=''", null, ''));
		$this->cEnviarGasesArteriales=trim($goDb->obtenerTabmae1('OP1TMA', 'HL7_PRM', "CL1TMA='MOD_PRV' AND CL2TMA='RAPID' AND ESTTMA=''", null, ''));
		$this->cCupsGlucometria=trim($goDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='GLUCORD' AND ESTTMA=''", null, ''));
		$loEstadoGases = $goDb->obtenerPrmtab('trim(TABDSC) ESTADO','PSM', ['TABCOD'=>'2', 'TABDSC'=>'1',], null, '');
		$this->cAccederGasesArteriales=trim(AplicacionFunciones::getValue($loEstadoGases, 'ESTADO', ''));
		$this->cEspecialidadLaboratorio=trim($goDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='ESPLAB' AND ESTTMA=''", null, ''));
		$loCoagulacion = $goDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCCOA', 'ESTTMA'=>'']);
		$lcCupsCoagulacion=trim(str_replace(' ', '', trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loCoagulacion, 'DE2TMA', ''))))));
		$this->aProcedimientosCoagulacion = explode(',',$lcCupsCoagulacion);
		$loGlucometrias = $goDb->obtenerTabmae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'GLUCPAQ', 'ESTTMA'=>'']);
		$lcCupGlucometrias=trim(str_replace(' ', '', trim(str_replace('\'', '', trim(AplicacionFunciones::getValue($loGlucometrias, 'DE2TMA', ''))))));
		$this->aProcedimientosGlucometrias = explode(',',$lcCupGlucometrias);
		$laParametrosEnviar=$this->enviarParametros();
		return $laParametrosEnviar;
	}

	function enviarParametros()
	{
		global $goDb;
		$aParametrosEnviar=[];
		$lcEspecialidadMedicos= $goDb->obtenerTabMae1('DE2TMA', 'DATING', "CL1TMA='TIPMED' AND ESTTMA=''", null, '1','11','3','4','6','16');
		$lcCentroCostoGasesArteriales=trim($goDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='RAPID' AND CL3TMA='GASES' AND CL4TMA='CENTCOST' AND ESTTMA=''", null, ''));

		$laParametros = $this->oDb
			->select('trim(CL1TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA=\'GRABCON\' AND ESTTMA=\' \'')
			->orderBy('CL1TMA')
			->getAll('array');
		if (is_array($laParametros) && count($laParametros)>0){
			foreach ($laParametros as $laDatos){
				switch ($laDatos['CODIGO']) {
					case 'CENCMEN':
						$this->aCentroCostoOrden = explode(',',$laDatos['DESCRIPCION']);
						break;

					case 'REF1NOP':
						$laReferencia1NoPermitida = $laDatos['DESCRIPCION'];
						break;

					case 'REF3NOP':
						$laReferencia3NoPermitida = $laDatos['DESCRIPCION'];
						break;

					case 'VIASMEN':
						$this->aViasIngresoOrden = explode(',',$laDatos['DESCRIPCION']);
						break;

					case 'VIASLAB':
						$this->aViasIngresoLaboratorio = explode(',',$laDatos['DESCRIPCION']);
						break;

					case 'TIPCONS':
						$laTipoCausaExterna = $laDatos['DESCRIPCION'];
						break;
					}
			}
		}

		$aParametrosEnviar=[
			'procedimientogasesarteriales'=>$this->cCupsGasesArteriales,
			'especialidadgasesarteriales'=>$this->cEspecialidadGasesArteriales,
			'especialidadmedicos'=>$lcEspecialidadMedicos,
			'centrocostogasesarteriales'=>$lcCentroCostoGasesArteriales,
			'especialidadlaboratorio'=>$this->cEspecialidadLaboratorio,
			'referencia1nopermitida'=>$laReferencia1NoPermitida,
			'referencia3nopermitida'=>$laReferencia3NoPermitida,
			'tipoconsumocausaexterna'=>$laTipoCausaExterna,
		];
		return $aParametrosEnviar;
	}
	
	function verificarDatosProcedimientos($taDatos=[])
	{
		$this->datosIngreso($taDatos['numeroingreso']);
		$this->IniciaDatosIngreso($taDatos['numeroingreso']);
		$this->aError = $this->validacionProcedimientos($taDatos);
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
		$this->cFechaCreacion = $ltAhora->format('Ymd');
		$this->cHoraCreacion = $ltAhora->format('His');
		$this->cUsuarioCreacion = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cProgramaCreacion = 'GRCUPWEB';
		$this->cApellidoUsuarioHl7 = (isset($_SESSION[HCW_NAME])?mb_strtoupper($_SESSION[HCW_NAME]->oUsuario->cApellido1 .' ' .$_SESSION[HCW_NAME]->oUsuario->cApellido2 .'^'	.$_SESSION[HCW_NAME]->oUsuario->cNombre1.' ' .$_SESSION[HCW_NAME]->oUsuario->cNombre2):'');
	}

	public function validacionProcedimientos($validacionDatos=[])
	{
		$laRetornar = [
			'Valido'=>true,
			'Mensaje'=>'',
			'Objeto'=>'cProcedimientoBuscar',
			'Consecutivo'=>0,
		];
		$lbRevisar = true;
		$lnNumeroIngreso=intval($this->aIngreso['nIngreso']);
		$lcObjeto = "cProcedimientoBuscar";

		if ($lnNumeroIngreso>0){
			$laErrores = [];
			$laWhere=['NIGING'=>$lnNumeroIngreso,'ESTING'=>2,];
			try {
				$lbValidar = false;
				$laRegistros = $this->oDb->from('RIAING')->where($laWhere)->get('array');
				if ($this->oDb->numRows()>0) $lbValidar = true;

				if (!$lbValidar) {
					$laRetornar['Valido'] = false;
					$laRetornar['Mensaje'] = "Ingreso cerrado/factura, no puede grabar consumos, revise por favor. ";
					$laRetornar['Objeto'] = $lcObjeto;
					$lbRevisar = false;
				}
			} catch(\Exception $loError){
				$laErrores[] = $loError->getMessage();
			} catch(\PDOException $loError){
				$laErrores[] = $loError->getMessage();
			}
		}else{
			$laRetornar['Valido'] = false;
			$laRetornar['Mensaje'] = "NO existe ingreso, revise por favor. ";
			$laRetornar['Objeto'] = $lcObjeto;
			$lbRevisar = false;
		}

		if ($lbRevisar){
			if(count($validacionDatos['procedimientos'])>0){
				foreach($validacionDatos['procedimientos'] as $lnKey=>$laProcedimientos){
					$lcProcedimiento=$laProcedimientos['CUPS']??'';
					$lcCentroServicio=$laProcedimientos['CENTROSERVICIO']??'';
					$lcCausaExterna=$laProcedimientos['CAUSAEXTERNA']??'';
					$lcFinalidad=$laProcedimientos['FINALIDAD']??'';
					$lcDiagnostico=$laProcedimientos['DIAGNOSTICO']??'';
					$lcTipoDiagnostico=$laProcedimientos['TIPODIAGNOSTICO']??'';
					$lcMedicoRealiza=$laProcedimientos['MEDICOREALIZA']??'';
					$lcEspecialidadMedico=$laProcedimientos['ESPECIALIDADMEDICO']??'';
					$lcEspecialidadProcedimiento=$laProcedimientos['ESPECIALIDADPROCEDIMIENTO']??'';

					if (!empty($lcProcedimiento)){
						$laErrores = [];
						$laWhere=['IDDCUP'=>'0','CODCUP'=>$lcProcedimiento,];
						try {
							$lbValidar = false;
							$laRegistros = $this->oDb->from('RIACUP')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el PROCEDIMIENTO ". $lcProcedimiento;
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}else{
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "NO existe procedimiento ". $lcProcedimiento;
						$laRetornar['Objeto'] = $lcObjeto;
						$lbRevisar = false;
					}

					if ($lbRevisar && !empty($lcCentroServicio)){
						$laErrores = [];
						$laWhere=['TABTIP'=>'CSE','TABCOD'=>$lcCentroServicio,];
						try {
							$lbValidar = false;
							$laRegistros = $this->oDb->from('PRMTAB')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el CENTRO DE SERVICIO ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcCausaExterna)){
						$laErrores = [];
						$laWhere=['TIPTMA'=>'CODCEX','CL1TMA'=>$lcCausaExterna,'ESTTMA'=>'',];
						try {
							$lbValidar = false;
							$laRegistros = $this->oDb->from('TABMAE')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro la CAUSA EXTERNA ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcFinalidad)){
						$laErrores = [];
						try {
							$lbValidar = false;
							$laRegistros = $this->oDb->from('TABMAE')->where("TIPTMA='CODFIN' AND CL1TMA=$lcFinalidad AND ESTTMA='' AND OP2TMA LIKE '%P%'")->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro la FINALIDAD ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcDiagnostico)){
						$laErrores = [];
						try {
							$lbValidar = false;
							$laWhere=['ENFRIP'=>$lcDiagnostico];
							$laRegistros = $this->oDb->from('RIACIE')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el DIAGNÓSTICO ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcTipoDiagnostico)){
						$laErrores = [];
						$lcTipoDiagnostico='B'.$lcTipoDiagnostico;
						$laWhere=['TABTIP'=>'TDX','TABCOD'=>$lcTipoDiagnostico,];
						try {
							$lbValidar = false;
							$laRegistros = $this->oDb->from('PRMTAB')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el TIPO DE DIAGNÓSTICO ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcMedicoRealiza)){
						$laErrores = [];
						try {
							$lbValidar = false;
							$laWhere=['REGMED'=>$lcMedicoRealiza,'ESTRGM'=>1,];
							$laRegistros = $this->oDb->from('RIARGMN')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro el PROFESIONAL ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcEspecialidadMedico)){
						$laErrores = [];
						try {
							$lbValidar = false;
							$laWhere=['CODESP'=>$lcEspecialidadMedico];
							$laRegistros = $this->oDb->from('RIAESPE')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro la ESPECIALIDAD del médico ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					if ($lbRevisar && !empty($lcEspecialidadProcedimiento)){
						$laErrores = [];
						try {
							$lbValidar = false;
							$laWhere=['CODESP'=>$lcEspecialidadProcedimiento];
							$laRegistros = $this->oDb->from('RIAESPE')->where($laWhere)->get('array');
							if (is_array($laRegistros)) if (count($laRegistros)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = "NO se encontro la ESPECIALIDAD para el procedimiento ";
								$laRetornar['Objeto'] = $lcObjeto;
								$lbRevisar = false;
								break;
							}
						} catch(\Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(\PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}
				}	
			}else{
				$laRetornar['Valido'] = false;
				$laRetornar['Mensaje'] = "No existen datos de procedimientos a registrar";
				$laRetornar['Objeto'] = $lcObjeto;
				$lbRevisar = false;
			}
		}	
		return $laRetornar; 
	}

	public function guardarProcedimientos($taDatosGuardar=[])
	{
		if (!empty($taDatosGuardar)){
			$this->organizarDatos($taDatosGuardar);
			$this->guardarDatosProcedimientos();
			$this->aError['Consecutivo'] = $this->nConsecutivoOrden;
		}
		return $this->aError;
	}

	function organizarDatos($taDatosGuardar=[])
	{
		$lnConsecutivoCita=$lnConsecutivoOrden=$lnGuardarGlucumetria=0;
		$lnCodigoConsecutivoOrden=911;
		$llEnviarGasesArteriales=$llEnviarGlucometria=$llConsecutivoOrden=true;
		$this->aProcedimientosLaboratorio=[];
		
		if(!empty($taDatosGuardar['procedimientos'])){
			foreach($taDatosGuardar['procedimientos'] as $laProcedimientos){
				$lnConsecutivoCita=Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cProgramaCreacion);
				$lcEspecialidadMedico=$laProcedimientos['ESPECIALIDADMEDICO']??'';
				$lcCodigoProcedimiento=$laProcedimientos['CUPS']??'';
				$lcDescripcionProcedimiento=$laProcedimientos['DESCRIPCIONCUPS']??'';
				$lcRegistroMedico=$laProcedimientos['MEDICOREALIZA']??'';
				$lcPosNopos=$laProcedimientos['POSNOPOS']??'';
				$lcEspecialidadProcedimiento=$laProcedimientos['ESPECIALIDADPROCEDIMIENTO']??'';
				$lcInformacionClinica=$laProcedimientos['INFORMACIONCLINICA']??'';
				$lcCodigoAlternoLaboratorio=$laProcedimientos['CODIGOHEXALIS']??'';
				$lnConsecutivoLaboratorio=$laProcedimientos['LINEA']??'';
				$lcCodigoDianostico=$laProcedimientos['DIAGNOSTICO']??'';
				$lcCodigoFinalidad=$laProcedimientos['FINALIDAD']??'';
				$lcCentroDeCosto=$laProcedimientos['CENTRODECOSTO']??'';
				$lcCausaExterna=$laProcedimientos['CAUSAEXTERNA']??'';
				$lcTipoDiagnostico=$laProcedimientos['TIPODIAGNOSTICO']??'';

				if ($lnConsecutivoOrden==0){ 
					$lnConsecutivoOrden = Consecutivos::fCalcularConsecutivoOrdenProcedimientos($lnCodigoConsecutivoOrden, $this->cProgramaCreacion);
				}

				if ($llConsecutivoOrden && in_array($this->aIngreso['cCodVia'], $this->aViasIngresoOrden) && in_array($lcCentroDeCosto, $this->aCentroCostoOrden)){
					$llConsecutivoOrden=false;
					$this->nConsecutivoOrden=$lnConsecutivoOrden;
				}

				$laDatosProcedimiento=[
					'numeroingreso' => $this->aIngreso['nIngreso'],
					'viaingreso' => $this->aIngreso['cCodVia'],
					'consecutivocita' => $lnConsecutivoCita,
					'especialidadrealiza' => $lcEspecialidadMedico,
					'causaexterna' => $lcCausaExterna,
					'tipodiagnostico' => $lcTipoDiagnostico,
					'codigoprocedimiento' => $lcCodigoProcedimiento,
					'descripcionprocedimiento' => $lcDescripcionProcedimiento,
					'codigolaboratorio' => $lcCodigoAlternoLaboratorio,
					'consecutivolaboratorio' => $lnConsecutivoLaboratorio,
					'especialidadprocedimiento' => $lcEspecialidadProcedimiento,
					'informacionclinica' => $lcInformacionClinica,
					'consecutivoorden' => $lnConsecutivoOrden,
					'registromedico' => $lcRegistroMedico,
					'estadoprocedimiento' => 8,
					'posnopos' => $lcPosNopos,
					'codigodiagnostico' => $lcCodigoDianostico,
					'codigofinalidad' => $lcCodigoFinalidad,
					'centrocosto' => $lcCentroDeCosto,
					'validarglucometria' => false,
				];
				$this->registrarDatos($laDatosProcedimiento);

				//	ENVIAR INTERFAZ GLUCOMETRIA
				if ($llEnviarGlucometria && $lcCodigoProcedimiento==$this->cCupsGlucometria){
					$llEnviarGlucometria=false;
					$this->crearDatosGlucometria($laDatosProcedimiento);
				}

				//	ENVIAR INTERFAZ GASES ARTERIALES - OK
				if ($llEnviarGasesArteriales && $this->cAccederGasesArteriales=='1' && $this->cEnviarGasesArteriales=='1' && 
					$lcCodigoProcedimiento==$this->cCupsGasesArteriales && $lcEspecialidadProcedimiento==$this->cEspecialidadGasesArteriales){
						$llEnviarGasesArteriales=false;
						$this->crearDatosGasesArteriales($laDatosProcedimiento);	
				}
		
				//	ENVIAR LABORATORIOS
				if ($lcEspecialidadProcedimiento==$this->cEspecialidadLaboratorio){
					$this->registrarDatosLaboratorio($laDatosProcedimiento);
				}
			}

			if (count($this->aProcedimientosLaboratorio)>0){
				$this->agruparProcedimientosLaboratorio($this->aProcedimientosLaboratorio);	
			}
		}	
	}	

	function registrarDatosLaboratorio($taDatosProcedimiento=[])
	{
		$lcCodigoProcedimiento=$taDatosProcedimiento['codigoprocedimiento'];
		$lcRegistroMedico=$taDatosProcedimiento['registromedico'];
		$laDatosMedico = $this->oDb
		->select("TRIM(USUARI) USUARIO, UPPER(TRIM(NOMMED) || '^' || TRIM(NNOMED)) NOMBREMEDICO")
		->from('RIARGMN')
		->where(['REGMED'=>$lcRegistroMedico,])
		->get('array');
		$lcNombreMedico=$this->oDb->numRows()>0 ? $laDatosMedico['NOMBREMEDICO'] : '';
		$lcCodigoUsuario=$this->oDb->numRows()>0 ? $laDatosMedico['USUARIO'] : '';

		if (in_array($this->aIngreso['cCodVia'], $this->aViasIngresoLaboratorio)){
			if (!in_array($lcCodigoProcedimiento, $this->aProcedimientosCoagulacion)){
				$this->aProcedimientosLaboratorio[]=[
					'codigohexalis'=>$taDatosProcedimiento['codigolaboratorio'],
					'descripcioncups'=>$taDatosProcedimiento['descripcionprocedimiento'],
					'consecutivocita'=>$taDatosProcedimiento['consecutivocita'],
					'observaciones'=>$taDatosProcedimiento['informacionclinica'],
					'linea'=>$taDatosProcedimiento['consecutivolaboratorio'],
					'numeroingreso'=>$this->aIngreso['nIngreso'],
					'viaingreso'=>$this->aIngreso['cCodVia'],
					'nombreusuarioordena'=>$lcNombreMedico,
					'codigousuarioordena'=>$lcCodigoUsuario,
				];
			}
		}else{
			if (!in_array($lcCodigoProcedimiento, $this->aProcedimientosGlucometrias)){
				$this->aProcedimientosLaboratorio[]=[
					'codigohexalis'=>$taDatosProcedimiento['codigolaboratorio'],
					'descripcioncups'=>$taDatosProcedimiento['descripcionprocedimiento'],
					'consecutivocita'=>$taDatosProcedimiento['consecutivocita'],
					'observaciones'=>$taDatosProcedimiento['informacionclinica'],
					'linea'=>$taDatosProcedimiento['consecutivolaboratorio'],
					'numeroingreso'=>$this->aIngreso['nIngreso'],
					'viaingreso'=>$this->aIngreso['cCodVia'],
					'nombreusuarioordena'=>$lcNombreMedico,
					'codigousuarioordena'=>$lcCodigoUsuario,
				];
			}	
		}
	}	
	 
	function registrarDatos($taDatosProcedimiento=[])
	{
		
		$lcTabla = 'RIAORD';
		$lnLinea = 0;
		$lcDescripcion = '';
		$this->insertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $taDatosProcedimiento);

		$lcTabla = 'RIADET';
		$lnLinea = 0;
		$lcDescripcion = '';
		$this->insertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $taDatosProcedimiento);

		$lcTabla = 'CUPGRA';
		$lnLinea = 0;
		$lcDescripcion = '';
		$this->insertarRegistro($lcTabla, $lcDescripcion, $lnLinea, $taDatosProcedimiento);
		
		if (!empty($taDatosProcedimiento['informacionclinica'])){
			$lcTabla = 'ORDPRO';
			$lnLongitud = 220;
			$lnLinea = 1;
			$lcDescripcion = $taDatosProcedimiento['informacionclinica'];
			$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescripcion, $lnLinea, $taDatosProcedimiento);
		}
		$this->insertarProcedimientosCobrar($taDatosProcedimiento);
	}

	function insertarProcedimientosCobrar($taDatosProcedimiento=[])
	{
		$this->aProcedimientosCobrar[]=[
			'codigoprocedimiento'=>$taDatosProcedimiento['codigoprocedimiento'],
			'medicorealiza'=>$taDatosProcedimiento['registromedico'],
			'especialidadrealiza'=>$taDatosProcedimiento['especialidadrealiza'],
			'consecutivocita'=>$taDatosProcedimiento['consecutivocita'],
			'centrocosto'=>$taDatosProcedimiento['centrocosto'],
		];
	}

	function agruparProcedimientosLaboratorio($taDatosLaboratorio=[])
	{
		$loOrdenesMedicas=new OrdenesMedicas;
		$lnLinea='linea';
		$this->aDatosLaboratorio=$loOrdenesMedicas->agruparLaboratorios($taDatosLaboratorio,$lnLinea);
	}
	
	function crearDatosGasesArteriales($taDatosProcedimientos=[])
	{
		$this->aDatosGasesArterialesAdt=[
			'nroingreso' => $this->aIngreso['nIngreso'],
			'codigocups' => '',
			'descripcioncups' => $taDatosProcedimientos['descripcionprocedimiento'],
			'codigoespecialidad' => $taDatosProcedimientos['especialidadprocedimiento'],
			'modelo' => 'RAPID',
			'tipo' => 'ADT',
			'evento' => 'A02',
			'observaciones' => $taDatosProcedimientos['informacionclinica'],
			'consecutivoorden' => $taDatosProcedimientos['consecutivoorden'],
			'consecutivocita' => 0,
			'registromedico' => $taDatosProcedimientos['registromedico'],
		];

		$this->aDatosGasesArterialesOrm=[
			'nroingreso' => $this->aIngreso['nIngreso'],
			'codigocups' => $taDatosProcedimientos['codigoprocedimiento'],
			'descripcioncups' => $taDatosProcedimientos['descripcionprocedimiento'],
			'codigoespecialidad' => $taDatosProcedimientos['especialidadprocedimiento'],
			'modelo' => 'RAPID',
			'tipo' => 'ORM',
			'evento' => 'O01',
			'observaciones' => $taDatosProcedimientos['informacionclinica'],
			'consecutivoorden' => $taDatosProcedimientos['consecutivoorden'],
			'consecutivocita' => $taDatosProcedimientos['consecutivocita'],
			'registromedico' => $taDatosProcedimientos['registromedico'],
		];
	}

	function crearDatosGlucometria($taDatosProcedimientos=[])
	{
		$this->aDatosGlucometria=[
			'nroingreso' => $this->aIngreso['nIngreso'],
			'validarglucometria' => $taDatosProcedimientos['validarglucometria'],
			'codigocups' => $taDatosProcedimientos['codigoprocedimiento'],
			'tipoglucometria' => 'ADT',
			'eventoglucometria' => 'A01',
		];
	}	
	
	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnLinea=1, $taDatosComunes=[])
	{
		$laChar = AplicacionFunciones::mb_str_split($tcTexto,$tnLongitud,'UTF8');
		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnLinea, $taDatosComunes);
					$tnLinea++;
				}
				return $tnLinea - 1;
			}
		}
	}

	function insertarRegistro($tcTabla='', $tcDescripcion='', $tnLinea=0, $taDatosComunes=[])
	{
		switch (true){
			case $tcTabla=='RIAORD' :
				$this->insertarRiaord($taDatosComunes);
				break;

			case $tcTabla=='RIADET' :
				$this->insertarRiadet($taDatosComunes);
				break;		

			case $tcTabla=='CUPGRA' :
				$this->insertarCupgra($taDatosComunes);
				break;		

			case $tcTabla=='ORDPRO' :
				$this->insertarOrdenProcedimiento($tcDescripcion,$tnLinea,$taDatosComunes);
				break;		
		}
	}

	function insertarOrdenProcedimiento($tcDescripcion='', $tnLinea=0, $taDatosComunes=[])
	{
		$this->aORDPRO[]=[
			'INGRESO'=>$this->aIngreso['nIngreso'],
			'CONSECUTIVOCITA'=>$taDatosComunes['consecutivocita'],
			'CUPS'=>$taDatosComunes['codigoprocedimiento'],
			'LINEA'=>$tnLinea,
			'DESCRIPCION'=>$tcDescripcion,
			'USUARIOCREACION'=>$this->cUsuarioCreacion,
			'PROGRAMACREACION'=>$this->cProgramaCreacion,
			'FECHACREACION'=>intval($this->cFechaCreacion),
			'HORACREACION'=>intval($this->cHoraCreacion),
		];
	}

	function insertarCupgra($taDatosComunes=[])
	{
		$this->aCUPGRA[]=[
			'INGRESO'=>$this->aIngreso['nIngreso'],
			'CONSECUTIVOCITA'=>$taDatosComunes['consecutivocita'],
			'CUPS'=>$taDatosComunes['codigoprocedimiento'],
			'CAUSAEXTERNA'=>$taDatosComunes['causaexterna'],
			'TIPODIAGNOSTICO'=>$taDatosComunes['tipodiagnostico'],
			'DIAGNOSTICO'=>$taDatosComunes['codigodiagnostico'],
			'FINALIDAD'=>$taDatosComunes['codigofinalidad'],
			'MEDICOREALIZA'=>$taDatosComunes['registromedico'],
			'ESPECIALIDADREALIZA'=>$taDatosComunes['especialidadrealiza'],
			'USUARIOCREACION'=>$this->cUsuarioCreacion,
			'PROGRAMACREACION'=>$this->cProgramaCreacion,
			'FECHACREACION'=>intval($this->cFechaCreacion),
			'HORACREACION'=>intval($this->cHoraCreacion),
		];
	}

	function insertarRiaord($taDatosComunes=[])
	{
		$this->aRIAORD[]=[
			'TIPOIDE'=>$this->aIngreso['cTipId'],
			'IDENTIFICACION'=>$this->aIngreso['nNumId'],
			'INGRESO'=>$this->aIngreso['nIngreso'],
			'CONSECUTIVOCITA'=>$taDatosComunes['consecutivocita'],
			'CODIGOESPECIALIDAD'=>$taDatosComunes['especialidadprocedimiento'],
			'CUPS'=>$taDatosComunes['codigoprocedimiento'],
			'MEDICOORDENA'=>$taDatosComunes['registromedico'],
			'MEDICOORDENA'=>$taDatosComunes['registromedico'],
			'FECHAORDENA'=>intval($this->cFechaCreacion),
			'FECHACITA'=>intval($this->cFechaCreacion),
			'HORACITA'=>intval($this->cHoraCreacion),
			'MEDICOREALIZA'=>$taDatosComunes['registromedico'],
			'ESTADO'=>$taDatosComunes['estadoprocedimiento'],
			'ENTIDAD'=>intval($this->aIngreso['nEntidad']),
			'VIAINGRESO'=>$this->aIngreso['cCodVia'],
			'PLANCONSUMOS'=>$this->aIngreso['cPlan'],
			'SECCION'=>$this->aIngreso['cSeccion'],
			'HABITACION'=>$this->aIngreso['cHabita'],
			'POSNOPOS'=>$taDatosComunes['posnopos'],
			'USUARIOCREACION'=>$this->cUsuarioCreacion,
			'PROGRAMACREACION'=>$this->cProgramaCreacion,
			'FECHACREACION'=>intval($this->cFechaCreacion),
			'HORACREACION'=>intval($this->cHoraCreacion),
		];
	}

	function insertarRiadet($taDatosComunes=[])
	{
		$this->aRIADET[]=[
			'TIPOIDE'=>$this->aIngreso['cTipId'],
			'IDENTIFICACION'=>$this->aIngreso['nNumId'],
			'INGRESO'=>$this->aIngreso['nIngreso'],
			'CONSECUTIVOCITA'=>$taDatosComunes['consecutivocita'],
			'CUPS'=>$taDatosComunes['codigoprocedimiento'],
			'ESTADO'=>$taDatosComunes['estadoprocedimiento'],
			'MARCACOBRO'=>1,
			'CONSECUTIVOORDEN'=>$taDatosComunes['consecutivoorden'],
			'USUARIOCREACION'=>$this->cUsuarioCreacion,
			'PROGRAMACREACION'=>$this->cProgramaCreacion,
			'FECHACREACION'=>intval($this->cFechaCreacion),
			'HORACREACION'=>intval($this->cHoraCreacion),
		];
	}

	private function guardarDatosProcedimientos()
	{
		$loOrdenesMedicas=new OrdenesMedicas;

		if(is_array($this->aRIAORD) && count($this->aRIAORD)>0){
			foreach($this->aRIAORD  as $laRIAORD){
				$lcRetornar='';
				$lcJsonOrdenes = json_encode($laRIAORD);
				$laParamEntrada = [ 'datosjson'	=> [$lcJsonOrdenes??'' , \PDO::PARAM_STR],];
				$laParamSalida = ['Retorno'=>[\PDO::PARAM_STR, 2]];
				$lcRetornar= $this->oDb->storedProcedure('F_INSERTAR_ORDENES_PROCEDIMIENTOS', $laParamEntrada, $laParamSalida);
			}
		}

		if(is_array($this->aRIADET) && count($this->aRIADET)>0){
			foreach($this->aRIADET  as $aRIADET){
				$lcRetornar='';
				$lcJsonOrdenes = json_encode($aRIADET);
				$laParamEntrada = [ 'datosjson'	=> [$lcJsonOrdenes??'' , \PDO::PARAM_STR],];
				$laParamSalida = ['Retorno'=>[\PDO::PARAM_STR, 2]];
				$lcRetornar= $this->oDb->storedProcedure('F_INSERTAR_HISTORICO_PROCEDIMIENTOS', $laParamEntrada, $laParamSalida);
			}
		}

		if(is_array($this->aCUPGRA) && count($this->aCUPGRA)>0){
			foreach($this->aCUPGRA  as $aCUPGRA){
				$lcRetornar='';
				$aCUPGRA['CONSECUTIVOUNICO'] = Consecutivos::fCalcularGrabarProcedimentos();
				$lcJsonOrdenes = json_encode($aCUPGRA);
				$laParamEntrada = [ 'datosjson'	=> [$lcJsonOrdenes??'' , \PDO::PARAM_STR],];
				$laParamSalida = ['Retorno'=>[\PDO::PARAM_STR, 2]];
				$lcRetornar= $this->oDb->storedProcedure('F_INSERTAR_GRABAR_PROCEDIMIENTOS', $laParamEntrada, $laParamSalida);
			}
		}

		if(is_array($this->aORDPRO) && count($this->aORDPRO)>0){
			foreach($this->aORDPRO  as $aORDPRO){
				$lcRetornar='';
				$lcJsonOrdenes = json_encode($aORDPRO);
				$laParamEntrada = [ 'datosjson'	=> [$lcJsonOrdenes??'' , \PDO::PARAM_STR],];
				$laParamSalida = ['Retorno'=>[\PDO::PARAM_STR, 2]];
				$lcRetornar= $this->oDb->storedProcedure('F_INSERTAR_OBSERVACION_PROCEDIMIENTOS', $laParamEntrada, $laParamSalida);
			}
		}

		if(is_array($this->aProcedimientosCobrar) && count($this->aProcedimientosCobrar)>0){
			foreach($this->aProcedimientosCobrar  as $aProcedimientos){
				$this->cobraProcedimiento($aProcedimientos);
			}
		}

		if(is_array($this->aDatosGasesArterialesAdt) && count($this->aDatosGasesArterialesAdt)>0){
			$loOrdenesMedicas->fnEnviarMensajeGasesArteriales($this->aDatosGasesArterialesAdt);
		}	

		if(is_array($this->aDatosGasesArterialesOrm) && count($this->aDatosGasesArterialesOrm)>0){
			$loOrdenesMedicas->fnEnviarMensajeGasesArteriales($this->aDatosGasesArterialesOrm);
		}	

		if(is_array($this->aDatosGlucometria) && count($this->aDatosGlucometria)>0){
			$loOrdenesMedicas->fnEnviarMensajeGlucometria($this->aDatosGlucometria);
		}	

		if(is_array($this->aDatosLaboratorio) && count($this->aDatosLaboratorio)>0){
			$loOrdenesMedicas->fnEnviarMensajeHexalis($this->aDatosLaboratorio);
		}	
	}

	function cobraProcedimiento($taDatosCobrar=[])
	{
		$laData = [
			'ingreso'       => $this->aIngreso['nIngreso'],
			'numIdPac'      => $this->aIngreso['nNumId'],
			'codCup'        => $taDatosCobrar['codigoprocedimiento'],
			'codVia'        => $this->aIngreso['cCodVia'],
			'codPlan'       => $this->aIngreso['cPlan'],
			'regMedOrdena'  => $taDatosCobrar['medicorealiza'],
			'regMedRealiza' => $taDatosCobrar['medicorealiza'],
			'espMedRealiza' => $taDatosCobrar['especialidadrealiza'],
			'secCama'       => trim($this->aIngreso['cSeccion']).trim($this->aIngreso['cHabita']),
			'cnsCita'       =>$taDatosCobrar['consecutivocita'],
			'centroCosto'   =>$taDatosCobrar['centrocosto'],
			'portatil'      => '',
		];
		$loCobros = new Cobros();
		$lbRet = $loCobros->cobrarCupsCentroServicio($laData);
	}

}
