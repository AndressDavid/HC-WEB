<?php
namespace NUCLEO;
require_once __DIR__ . '/class.FeConsultar06FA.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.Epicrisis_Ingreso.php';
require_once __DIR__ . '/class.FuncionesInv.php';

use NUCLEO\FeConsultar06FA;
use NUCLEO\Epicrisis_Ingreso;
use ZipArchive;

class Rips_factura
{
	protected $cCodigoHabilitacion='';
	protected $cNitShaio='';
	protected $cFechaHoraEgreso='';
	protected $cFechaHoraEgresoCompara='';
	protected $cConsecutivoCabecera='';
	protected $cTipoPagoModerador='';
	protected $cTipoPagoModeradorEstandar='';
	protected $cUsuarioCreacion = '';
	protected $cProgramaCreacion = '';
	protected $cFechaCreacion = '';
	protected $cHoraCreacion = '';
	protected $cLlaveUnicaUsuarios = '';
	protected $cRutaArchivoRipsJson = '';
	protected $cTipoDocumento = '';
	protected $cGeneraZip = '';
	protected $cFacturaPagoModerador = '';
	protected $cTipoIdeMedicoCups = '';
	protected $cNumeroIdeMedicoCups = '';
	protected $cDiagnosticoCups = '';
	protected $nConsecutivoConsulta=0;
	protected $nFechaIngreso=0;
	protected $nHoraIngreso=0;
	protected $nFechaEgreso=0;
	protected $nHoraEgreso=0;
	protected $nValorCopagoCuota=0;
	protected $nAplicaCopagoCuota=0;
	protected $nUltimoConsecutivoCups=0;
	protected $nNumeroIngreso=0;
	protected $nNumeroFacturaJson=0;
	protected $nValorParaPruebas=0;
	protected $nCantidadProcedimientosEnRips=0;
	protected $nCantidadProcedimientosFacturados=0;
	protected $aReacaudoConsulta=[];
	protected $aReacaudoProcedimiento=[];
	protected $aReacaudoMedicamento=[];
	protected $aReacaudoOtrosServicios=[];
	protected $aEstadosCrearJson=[];
	protected $aProcedimentosConsulta=[];
	protected $aProcedimentosProcedimientos=[];
	protected $aProcedimentosOtrosServicios=[];
	protected $llRipsConError=false;
	
	protected $aDatoFallece=[			
			'codigodiagnosticofallece'=>"",
			'fechahorafallece'=>"",
		];
	
	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->crearParametrosRips();
    }

	public function crearMenuRips()
	{
		$laRetornaMenuFacturacion=[];
		global $goDb;
		$laFacturacion = $goDb
			->select('TRIM(CL1TMA) CLASIFICACION1, TRIM(DE2TMA) DESCRIPCION, TRIM(OP5TMA) TITULO')
			->from('TABMAE')
			->where('TIPTMA', '=', 'MEFACWEB')
			->where('ESTTMA', '=', '')
			->orderBy('CL1TMA')
			->getAll('array');
		if($goDb->numRows()>0){
			foreach ($laFacturacion as $laDatos){
				$lcUrl=explode('~', $laDatos['DESCRIPCION'])[0];
				$lcIcono=explode('~', $laDatos['DESCRIPCION'])[1];
				$lcTexto=explode('~', $laDatos['DESCRIPCION'])[2];
							
				$laRetornaMenuFacturacion[]=[
					'URL'=> $lcUrl,
					'ICONO'=> $lcIcono,
					'TITULO'=> $lcTexto,
					'TEXTO'=>  $laDatos['TITULO'],
				];
			}	
		}
		return $laRetornaMenuFacturacion;
	}
	
	public function consultarDatosRips($tnFactura=0,$tcTipoDocumento='')
	{
		global $goDb;
		$laRetornarDatosRips=[];
		$lcTipoDocumento=substr($tcTipoDocumento, 0, 2);
		if ($tnFactura>0){
			if ($lcTipoDocumento=='06'){
			$laRipsFactura = $goDb
				->select('TRIM(B.TIPODATO) TIPO, B.CONSECUTIVODATO CONSECUTIVO, TRIM(B.DESCRIPCION) DESCRIPCION')
				->from('RIPREC AS A')
				->leftJoin("RIPRES B", "A.IDFACTURA=B.IDFACTURA", null)
				->where('A.NUMEROFACTURA', '=', $tnFactura)->where('A.TIPORIPS', '=', $tcTipoDocumento)->orderBy('A.TIPORIPS, B.CONSECUTIVODATO')
				->getAll('array');
			}else{
				$laRipsFactura = $goDb
				->select('TRIM(B.TIPODATO) TIPO, B.CONSECUTIVODATO CONSECUTIVO, TRIM(B.DESCRIPCION) DESCRIPCION')
				->from('RIPREC AS A')
				->leftJoin("RIPRES B", "A.IDFACTURA=B.IDFACTURA", null)
				->where('A.NUMERONOTA', '=', $tnFactura)->where('A.TIPORIPS', '=', $tcTipoDocumento)->orderBy('A.TIPORIPS, B.CONSECUTIVODATO')
				->getAll('array');
			}	
			if($goDb->numRows()>0){
				$laRetornarDatosRips=$this->obtenerDatosRips($laRipsFactura);
			}		
		}
		return $laRetornarDatosRips;
	}
	
	public function obtenerDatosRips($taRipsFactura)
	{
		$laRetornarDatosRips=[];
		global $goDb;
		
		foreach($taRipsFactura as $lcClave=>$laOpc){
			$lcDscProc=$lcCodigoCiePrincipal=$lcDscCiePrincipal=$lcCodigoCieRelacionado1=$lcDscCieRelacionado1='';
			$lcEspecialidadCups='';
			$lcTipoRips=$laOpc['TIPO'];
			$laDescripcion=json_decode(trim($laOpc['DESCRIPCION']),true);
			
 			if ($lcTipoRips=='AF'){
				$laDescripcion['numDocumentoldObligado']=isset($laDescripcion['numDocumentoldObligado'])?trim($laDescripcion['numDocumentoldObligado']):'';
				$laDescripcion['numFactura']=isset($laDescripcion['numFactura'])?trim($laDescripcion['numFactura']):'';
				$laDescripcion['TipoNota']=isset($laDescripcion['TipoNota'])?trim($laDescripcion['TipoNota']):'';
				$laDescripcion['numNota']=isset($laDescripcion['numNota'])?trim($laDescripcion['numNota']):'';
			}	
			
			if ($lcTipoRips=='AH' || $lcTipoRips=='AP'){
				$laDescripcion['codComplicacion']=isset($laDescripcion['codComplicacion'])?trim($laDescripcion['codComplicacion']):'';
			}	
			
			if ($lcTipoRips=='AM' || $lcTipoRips=='AP' || $lcTipoRips=='AN'){
				$laDescripcion['codDiagnosticoPrincipal']=isset($laDescripcion['codDiagnosticoPrincipal'])?trim($laDescripcion['codDiagnosticoPrincipal']):'';
				$lcCodigoCiePrincipal=isset($laDescripcion['codDiagnosticoPrincipal'])?trim($laDescripcion['codDiagnosticoPrincipal']):'';
				$laDescripcion['descripcieprincipal']=!empty($lcCodigoCiePrincipal)?$this->consultarDescripcionDiagnostico($lcCodigoCiePrincipal):'';
				$laDescripcion['codDiagnosticoRelacionado']=isset($laDescripcion['codDiagnosticoRelacionado'])?trim($laDescripcion['codDiagnosticoRelacionado']):'';
				$lcCieRelacionado=isset($laDescripcion['codDiagnosticoRelacionado'])?trim($laDescripcion['codDiagnosticoRelacionado']):'';
				$laDescripcion['descripcierelacionado']=!empty($lcCieRelacionado)?$this->consultarDescripcionDiagnostico($lcCieRelacionado):'';
			}	

			if ($lcTipoRips=='AC' || $lcTipoRips=='AP'){
				$laDescripcion['codServicio']=isset($laDescripcion['codServicio'])?trim($laDescripcion['codServicio']):'';
				$lcServicio=isset($laDescripcion['codServicio'])?trim($laDescripcion['codServicio']):'';
				if ($lcTipoRips=='AC'){
					$lcTipoServicio='C';
					$lcCodigoCups=isset($laDescripcion['codConsulta'])?trim($laDescripcion['codConsulta']):'';
					$lcTipoDiagnosticoPrincipal=isset($laDescripcion['tipoDiagnosticoPrincipal'])?trim($laDescripcion['tipoDiagnosticoPrincipal']):'';
					$laDescripcion['descripcodtipodiagnosticoprincipal']=!empty($lcTipoDiagnosticoPrincipal)?$this->consultarDescripcionTipoDiagnostico($lcTipoDiagnosticoPrincipal):'';
				}else{
					$lcTipoServicio='P';
					$lcCodigoCups=isset($laDescripcion['codProcedimiento'])?trim($laDescripcion['codProcedimiento']):'';
				}	
				$laDescripcion['descripprocedimiento']=!empty($lcCodigoCups)?$this->consultarDescripcionCups($lcCodigoCups):'';
				$lcEspecialidadCups=$this->obtenerEspecialidadProcedimiento($lcCodigoCups);
				$laDescripcion['descripcodServicio']=!empty($lcServicio)?$this->consultarDescripcionServicio($lcServicio,$lcTipoServicio,$lcEspecialidadCups):'';
			}	

			if ($lcTipoRips=='US'){
				$lcCodigoTipoUsuario=isset($laDescripcion['tipoUsuario'])?trim($laDescripcion['tipoUsuario']):'';
				$lcCodigoSexo=isset($laDescripcion['codSexo'])?trim($laDescripcion['codSexo']):'';
				$lcCodigoPaisResidencia=isset($laDescripcion['codPaisResidencia'])?trim($laDescripcion['codPaisResidencia']):'';
				$lcCodigoCiudadResidencia=isset($laDescripcion['codMunicipioResidencia'])?trim($laDescripcion['codMunicipioResidencia']):'';
				$lcCodigoPaisNacimiento=isset($laDescripcion['codPaisOrigen'])?trim($laDescripcion['codPaisOrigen']):'';
				$lcCodigoZonaTerritorial=isset($laDescripcion['codZonaTerritorialResidencia'])?trim($laDescripcion['codZonaTerritorialResidencia']):'';
				$laDescripcion['descriptipousuario']=!empty($lcCodigoTipoUsuario)?$this->consultarDescripcionTipoUsuario($lcCodigoTipoUsuario):'';
				$laDescripcion['descripsexo']=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'SEXPAC', "CL1TMA='$lcCodigoSexo' AND ESTTMA=''", null, '')));
				$laDescripcion['descrippaisresidencia']=!empty($lcCodigoPaisResidencia)?$this->consultarDescripcionPais($lcCodigoPaisResidencia):'';
				$laDescripcion['descripciudadresidencia']=!empty($lcCodigoPaisResidencia)?$this->consultarDescripcionCiudad($lcCodigoPaisResidencia,$lcCodigoCiudadResidencia):'';
				$laDescripcion['descrippaisnacimiento']=!empty($lcCodigoPaisNacimiento)?$this->consultarDescripcionPais($lcCodigoPaisNacimiento):'';
				$laDescripcion['descripzonaterritorial']=!empty($lcCodigoZonaTerritorial)?$this->consultaDescripcionZonaTerritorial($lcCodigoZonaTerritorial):'';
				$laDescripcion['nombrepaciente']=!empty($laDescripcion['numDocumentoIdentificacion'])?$this->consultarNombrePaciente($laDescripcion['tipoDocumentoIdentificacion'],$laDescripcion['numDocumentoIdentificacion']):'';
			}	
			
			if ($lcTipoRips=='AN'){
				$lcCodigoSexo=isset($laDescripcion['codSexoBiologico'])?trim($laDescripcion['codSexoBiologico']):'';
				$laDescripcion['descripsexo']=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'SEXPAC', "CL1TMA='$lcCodigoSexo' AND ESTTMA=''", null, '')));
			}
			
			if ($lcTipoRips=='AC' || $lcTipoRips=='AU' || $lcTipoRips=='AH'){
				$lcCodigoCausaExterna=isset($laDescripcion['causaMotivoAtencion'])?trim($laDescripcion['causaMotivoAtencion']):'';
				$laDescripcion['descripcausaexterna']=!empty($lcCodigoCausaExterna)?$this->consultarDescripcionCausaExterna($lcCodigoCausaExterna):'';
				$lcCodigoCiePrincipal=isset($laDescripcion['codDiagnosticoPrincipal'])?trim($laDescripcion['codDiagnosticoPrincipal']):'';
				$laDescripcion['descripcieprincipal']=!empty($lcCodigoCiePrincipal)?$this->consultarDescripcionDiagnostico($lcCodigoCiePrincipal):'';

				if ($lcTipoRips=='AC'){
					$lcCodigoCieRelacionado1=isset($laDescripcion['codDiagnosticoRelacionado1'])?trim($laDescripcion['codDiagnosticoRelacionado1']):'';
					$laDescripcion['codDiagnosticoRelacionado1']=$lcCodigoCieRelacionado1;
					$laDescripcion['descripcierelacionado1']=!empty($lcCodigoCieRelacionado1)?$this->consultarDescripcionDiagnostico($lcCodigoCieRelacionado1):'';
					$lcCodigoCieRelacionado2=isset($laDescripcion['codDiagnosticoRelacionado2'])?trim($laDescripcion['codDiagnosticoRelacionado2']):'';
					$laDescripcion['codDiagnosticoRelacionado2']=$lcCodigoCieRelacionado2;
					$laDescripcion['descripcierelacionado2']=!empty($lcCodigoCieRelacionado2)?$this->consultarDescripcionDiagnostico($lcCodigoCieRelacionado2):'';
					$lcCodigoCieRelacionado3=isset($laDescripcion['codDiagnosticoRelacionado3'])?trim($laDescripcion['codDiagnosticoRelacionado3']):'';
					$laDescripcion['codDiagnosticoRelacionado3']=$lcCodigoCieRelacionado3;
					$laDescripcion['descripcierelacionado3']=!empty($lcCodigoCieRelacionado3)?$this->consultarDescripcionDiagnostico($lcCodigoCieRelacionado3):'';
				}	
			}	
			
			if ($lcTipoRips=='AP' || $lcTipoRips=='AC' || $lcTipoRips=='AH'){
				if ($lcTipoRips=='AP' || $lcTipoRips=='AH'){
					$laDescripcion['viaIngresoServicioSalud']=isset($laDescripcion['viaIngresoServicioSalud'])?trim($laDescripcion['viaIngresoServicioSalud']):'';
					$lcViaIngresoCups=isset($laDescripcion['viaIngresoServicioSalud'])?trim($laDescripcion['viaIngresoServicioSalud']):'';
					$laDescripcion['descripviaingreso']=!empty($lcViaIngresoCups)?$this->consultarDescripcionViaIngreso($lcViaIngresoCups):'';
				}	
				$lcCodigoModalidadAtencion=isset($laDescripcion['modalidadGrupoServicioTecSal'])?trim($laDescripcion['modalidadGrupoServicioTecSal']):'';
				$lcCodigoGrupoServicios=isset($laDescripcion['grupoServicios'])?trim($laDescripcion['grupoServicios']):'';
				$laDescripcion['descripmodalidadatencion']=!empty($lcCodigoModalidadAtencion)?$this->consultarDescripcionModalidadAtencion($lcCodigoModalidadAtencion):'';
				$laDescripcion['descripgruposervicio']=!empty($lcCodigoGrupoServicios)?$this->consultarDescripcionGrupoServicios($lcCodigoGrupoServicios):'';
				$lcFinalidadCups=isset($laDescripcion['finalidadTecnologiaSalud'])?trim($laDescripcion['finalidadTecnologiaSalud']):'';
				$laDescripcion['descripFinalidad']=!empty($lcFinalidadCups)?$this->consultarDescripcionFinalidad($lcFinalidadCups):'';
			}	
			
			if ($lcTipoRips=='AT'){
				$lcTipoOtrosServicio=isset($laDescripcion['tipoOS'])?trim($laDescripcion['tipoOS']):'';
				$lcCodigoTecnologia=isset($laDescripcion['codTecnologiaSalud'])?trim($laDescripcion['codTecnologiaSalud']):'';
				$laDescripcion['descriptipootroservicio']=!empty($lcTipoOtrosServicio)?$this->consultarDescripcionOtrosServicios($lcTipoOtrosServicio):'';
			}
			
			if ($lcTipoRips=='AU' || $lcTipoRips=='AH' || $lcTipoRips=='AN'){
				$lcCieEgreso=isset($laDescripcion['codDiagnosticoPrincipalE'])?trim($laDescripcion['codDiagnosticoPrincipalE']):'';
				$laDescripcion['descripcieegreso']=!empty($lcCieEgreso)?$this->consultarDescripcionDiagnostico($lcCieEgreso):'';
				$lcCieEgresoRel1=isset($laDescripcion['codDiagnosticoRelacionadoE1'])?trim($laDescripcion['codDiagnosticoRelacionadoE1']):'';
				$laDescripcion['descripcieegresorelacionado1']=!empty($lcCieEgresoRel1)?$this->consultarDescripcionDiagnostico($lcCieEgresoRel1):'';
				$lcCieEgresoRel2=isset($laDescripcion['codDiagnosticoRelacionadoE2'])?trim($laDescripcion['codDiagnosticoRelacionadoE2']):'';
				$laDescripcion['descripcieegresorelacionado2']=!empty($lcCieEgresoRel2)?$this->consultarDescripcionDiagnostico($lcCieEgresoRel2):'';
				$lcCieEgresoRel3=isset($laDescripcion['codDiagnosticoRelacionadoE3'])?trim($laDescripcion['codDiagnosticoRelacionadoE3']):'';
				$laDescripcion['descripcieegresorelacionado3']=!empty($lcCieEgresoRel3)?$this->consultarDescripcionDiagnostico($lcCieEgresoRel3):'';
				$lcCieFallece=isset($laDescripcion['codDiagnosticoCausaMuerte'])?trim($laDescripcion['codDiagnosticoCausaMuerte']):'';
				$laDescripcion['descripciefallece']=!empty($lcCieFallece)?$this->consultarDescripcionDiagnostico($lcCieFallece):'';
				$lcCondicionDestinoEgreso=isset($laDescripcion['condicionDestinoUsuarioEgreso'])?trim($laDescripcion['condicionDestinoUsuarioEgreso']):'';
				$laDescripcion['descripcioncondicionegreso']=!empty($lcCondicionDestinoEgreso)?trim($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='CONUSEGR' AND CL2TMA='$lcCondicionDestinoEgreso'", null, '')):'';
			}
			
			if ($lcTipoRips=='AP' || $lcTipoRips=='AC' || $lcTipoRips=='AT' || $lcTipoRips=='AM'){
				$lcTipoModeradora=isset($laDescripcion['conceptoRecaudo'])?trim($laDescripcion['conceptoRecaudo']):'';
				$laDescripcion['descripciontipomoderadora']=trim($goDb->obtenerTabmae1('OP5TMA', 'GENRIPS', "CL1TMA='TPAGMOD' AND CL2TMA='$lcTipoModeradora'", null, ''));
			}	
			if ($lcTipoRips=='AM'){
				$laDescripcion['fechaprescripcion']=isset($laDescripcion['fechaDispensAdmon'])?trim($laDescripcion['fechaDispensAdmon']):'';
				$laDescripcion['codTecnologiaSalud']=isset($laDescripcion['codTecnologiaSalud'])?trim($laDescripcion['codTecnologiaSalud']):'';
				$lcTipoMedicamento=isset($laDescripcion['tipoMedicamento'])?trim($laDescripcion['tipoMedicamento']):'';
				$laDescripcion['descripcTipoMedicamento']=!empty($lcTipoMedicamento)?$this->consultarDescripcionTipoMedicamento($lcTipoMedicamento):'';
				$lcFormaFarmaceutica=isset($laDescripcion['formaFarmaceutica'])?trim($laDescripcion['formaFarmaceutica']):'';
				$laDescripcion['descripcFormaFarmaceutica']=!empty($lcFormaFarmaceutica)?$this->consultarDescripcionFormaFarmaceutica($lcFormaFarmaceutica):'';
				$lcCodigoUnidMinimaDispensacion=isset($laDescripcion['unidadMinDispensa'])?trim($laDescripcion['unidadMinDispensa']):'';
				$laDescripcion['descripcUnidMinimaDispensacion']=!empty($lcCodigoUnidMinimaDispensacion)?$this->consultaDescripcionUnidadMinimaDispensacion($lcCodigoUnidMinimaDispensacion):'';
				$lcCodigoUnidadMedida=isset($laDescripcion['unidadMedida'])?trim($laDescripcion['unidadMedida']):'';
				$laDescripcion['descripcUnidadMedida']=!empty($lcCodigoUnidadMedida)?$this->consultaDescripcionUnidadmedida($lcCodigoUnidadMedida):'';
			}	
			$laRetornarDatosRips[$lcTipoRips][]=$laDescripcion; 
		}
		return $laRetornarDatosRips;
	}

	public function consultarTiposDocumentos()
	{
		global $goDb;
		$laTiposDocumentos=[];
		$laTiposDocumentos = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FACTELE')
			->where('CL1TMA', '=', 'TIPODOC')
			->where('OP1TMA', '=', 'R'
			)->getAll('array');
		return $laTiposDocumentos;
	}	
	
	public function crearRipJson()
	{
		$laRipsJson=[];
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cUsuarioCreacion = 'RIPSUSER';
		$this->cProgramaCreacion = 'RIPSFACW';
		$this->cFechaCreacion = $ltAhora->format('Ymd');
		$this->cHoraCreacion = $ltAhora->format('His');

		$laFacturasRips = $this->oDb
			->select('IDFACTURA, NUMEROFACTURA, TIPORIPS, NUMERONOTA')
			->from('RIPREC')
			->where('ESTADOFACTURA', '=', '00')
			->orderBy('IDFACTURA')
			->getAll('array');	
		$laRipsJson=$laFacturasRips;
		if ($this->oDb->numRows()>0){
			foreach($laFacturasRips as $laDatos){
				$this->cConsecutivoCabecera=trim($laDatos['IDFACTURA']);
				$lnFactura=$laDatos['NUMEROFACTURA'];
				$tcTipoDocumento=$laDatos['TIPORIPS'];
				$tcNroNota=$laDatos['NUMERONOTA'];
				$this->crearDatosRips($lnFactura,$tcTipoDocumento,$tcNroNota);
			}	
		}	
		unset($laFacturasRips);
		return $laRipsJson;
	}

	public function crearDatosRips($tnFactura=0,$tcTipoDocumento='',$tnNroNota=0)
	{
		$laRetornaUsuarios=$laRetornaTransacciones=[];
		$lcEstadoRips='01';
		$lcValidarCantidadProcedimientos=$this->cTipoIdeMedicoCups=$this->cNumeroIdeMedicoCups=$this->cDiagnosticoCups='';
		$this->llRipsConError=false;
		$this->nCantidadProcedimientosEnRips=$this->nCantidadProcedimientosFacturados=0;
		$lcTextoProceso='Crear registros json en RIPRES';
		$tcDocumentoFactura=trim(substr($tnFactura, 1, 6));
		$lcTipoFacturaNota=(substr($tcTipoDocumento, 0, 2)=='06')?'FAC':'NOT';
		if ($lcTipoFacturaNota=='FAC'){
			$laFactura = $this->oDb
			->select('FRACAB FACTURA, INGCAB INGRESO, FEFCAB FECHA_FACTURA, VAFCAB VALOR_FACTURA, TRIM(PLNCAB) PLAN')
			->from('FACCABF')
			->where('FRACAB', '=', $tnFactura)
			->where('DOCCAB', '=', $tcDocumentoFactura)
			->get('array');
		}else{
			$laFactura = $this->oDb
			->select('INGGLC INGRESO, VLRACC VALOR_ACEPTADO')
			->from('AMGLCAB')
			->where('FRAGLC', '=', $tnFactura)
			->where('NOTGLC', '=', $tnNroNota)
			->get('array');
		}	
		if ($this->oDb->numRows()>0){
			$lnIngreso=isset($laFactura['INGRESO'])?intval($laFactura['INGRESO']):0;
			$this->obtenerConsecutivoConsulta($lnIngreso);
			$this->obtenerCopagoCuota($lnIngreso,$tnFactura);
			$this->obtenerCantidadProcedimientosFacturados($lnIngreso,$tnFactura);
			$this->consultaDiagnosticoFallece($lnIngreso);
			$this->obtenerFechaEgreso($lnIngreso);

			$laParametrosEnvia=[
				'ingreso'=>$lnIngreso,
				'factura'=>$tnFactura,
				'tiporips'=>$tcTipoDocumento,
				'numeronota'=>$tnNroNota,
				'tipofacturanota'=>$lcTipoFacturaNota,
			];

			$laRetornaTransacciones=$this->crearRipsTransacciones($laParametrosEnvia);
			$laRetornaUsuarios=$this->crearRipsUsuarios($laParametrosEnvia);
			$laRetornaConsulta=$this->crearRipsConsulta($laParametrosEnvia);
			$laRetornaProcedimientos=$this->crearRipsProcedimientos($laParametrosEnvia);
			$laRetornaProcedimientos=$this->crearRipsPaquete($laParametrosEnvia);
			$laRetornaOtrosServicios=$this->crearRipsOtrosServicios($laParametrosEnvia);
			$laRetornaUrgenciasObservacion=$this->crearRipsUrgencias($laParametrosEnvia);
			$laRetornaHospitalizacion=$this->crearRipsHospitalizacion($laParametrosEnvia);
			$laRetornaMedicamentos=$this->crearRipsMedicamentos($laParametrosEnvia);
			$laRetornaRecienNacido=$this->crearRipsRecienNacido($laParametrosEnvia);

			if ($lcTipoFacturaNota=='FAC'){
				$lcValidarCantidadProcedimientos=$this->verificarCantidadProcedimientos();
			}
			$lcEstadoRips=($this->llRipsConError || !empty($lcValidarCantidadProcedimientos))?'50':$lcEstadoRips;
			$this->actualizarCabeceraRips($lcEstadoRips);
			$this->crearTrazabilidadRips($lcTextoProceso,$lcEstadoRips);
		}	
		unset($laControlFactura, $laFactura, $laHospitalizacion);
	}	

	public function reenviarRipJson()
	{
		$laRipsJson=[];
		$laFacturasRips = $this->oDb
			->select('IDFACTURA, NUMEROFACTURA, TIPORIPS, NUMERONOTA')
			->from('RIPREC')
			->where("ESTADOFACTURA like '9%'")
			->orderBy('IDFACTURA')
			->getAll('array');	
		$laRipsJson=$laFacturasRips;
		if ($this->oDb->numRows()>0){
			foreach($laFacturasRips as $laDatos){
				$this->cConsecutivoCabecera=trim($laDatos['IDFACTURA']);
				$lnFactura=$laDatos['NUMEROFACTURA'];
				$tcTipoDocumento=$laDatos['TIPORIPS'];
				$tcNroNota=$laDatos['NUMERONOTA'];
				$this->crearArchivoRips($lnFactura,$tcTipoDocumento,$tcNroNota);
			}
		}
		unset($laFacturasRips);
		return $laRipsJson;	
	}

	public function crearArchivosJson()
	{
		$laRipsJson=[];

		$laFacturasRips = $this->oDb
			->select('IDFACTURA, NUMEROFACTURA, TIPORIPS, NUMERONOTA')
			->from('RIPREC')
			->in('ESTADOFACTURA', $this->aEstadosCrearJson)
			->orderBy('IDFACTURA')
			->getAll('array');	
		$laRipsJson=$laFacturasRips;
		if ($this->oDb->numRows()>0){
			foreach($laFacturasRips as $laDatos){
				$this->cConsecutivoCabecera=trim($laDatos['IDFACTURA']);
				$lnFactura=$laDatos['NUMEROFACTURA'];
				$tcTipoDocumento=$laDatos['TIPORIPS'];
				$tcNroNota=$laDatos['NUMERONOTA'];
				$this->crearArchivoRips($lnFactura,$tcTipoDocumento,$tcNroNota);
			}
		}
		unset($laFacturasRips);
		return $laRipsJson;	
	}
	
	public function actualizarCabeceraRips($tcEstadoRips='')
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lfFechaHoraActual=$ltAhora->format("Y-m-d H:i:s.u");

		$lcTabla = 'RIPREC';
		$laDatos = [
			'ESTADOFACTURA'=>$tcEstadoRips,
			'USUARIOMODIFICA'=>$this->cUsuarioCreacion,
			'PROGRAMAMODIFICA'=>$this->cProgramaCreacion,
			'FECHAHORAMODIFICA'=>$lfFechaHoraActual,
		];
		$llResultado = $this->oDb->tabla($lcTabla)->where(['IDFACTURA'=>$this->cConsecutivoCabecera,])->actualizar($laDatos);
	}

	function crearTrazabilidadRips($tcTextoProceso='',$tcEstadoRips='')
	{
		$laDatos=$laDatosTabla=[];
		$lcLlaveUnica = $this->oDb->obtenerLlaveUnicaTabla();
		$lcTipoConsumo=$lcTipoDato='';
		$lnConsecutivo=0;
		$laDatos=[
			'llaveunica'=>$lcLlaveUnica,
			'descripcion'=>$tcTextoProceso,
			'estadorips'=>$tcEstadoRips,
		];
		$this->insertarDatos('RIPREE',$laDatosTabla,$laDatos);
		return $laDatos;
	}
	
	function crearRipsTransacciones($taDatosRecibe)
	{
		$laDatos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tnNroNota=$taDatosRecibe['numeronota'] ?? 0;
		$this->nNumeroIngreso=0;
		$lcTipoNota=substr($tcTipoRips, 2, 1)=='N'?substr($tcTipoRips, 2, 2) : null;
		$lcNroNota=$tnNroNota>0?$tnNroNota:'null';
		$lcNumeroFactura=$this->nValorParaPruebas>0?'SETT'.strval(intval($tnFactura)-5000000):$tnFactura;

		$laDatos=[
			'numDocumentoIdObligado'=>$this->cNitShaio,
			'numFactura'=>$lcNumeroFactura,
			'tipoNota'=>$lcTipoNota,
			'numNota'=>$lcNroNota,
			'consecutivo'=>0,
		];

		$laDatosTabla=[
			'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
			'tipodato'=>'AF',
			'tipoconsumo'=>'',
			'consecutivoconsumo'=>1,
		];
		$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);
		return $laDatos;
	}

	function crearRipsUsuarios($taDatosRecibe)
	{
		$laDatos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$this->nNumeroIngreso=$tnIngreso;
		$laConfig = require __DIR__ . '/../privada/fe_config.php';
		$loFactura=new FeConsultar06FA($laConfig, '');
		$loFactura->consultaCabecera($tnFactura);
		$lcTipoUsuario=$loFactura->obtenerTipoUsuario();
		$laFacturaPaciente=$this->obtenerDatosFacturaPaciente($tnFactura);
		$lcFechaNacimiento=AplicacionFunciones::formatFechaHora('fecha',$laFacturaPaciente['FECHANACIMIENTO'],'-');
		$lcCiudadResidencia=str_pad($laFacturaPaciente['DEPTRESIDENCIA'], 2, '0', STR_PAD_LEFT) .str_pad($laFacturaPaciente['CIUDADRESIDENCIA'], 3, '0', STR_PAD_LEFT);
		$lcZonaResidencia=$this->obtieneZonaTerritorial($laFacturaPaciente['ZONAPACIENTE']);
		$lcTieneIncapacidad=$this->obtieneIncapacidadPaciente($tnIngreso);
		
		$laDatos =[
			'tipoDocumentoIdentificacion'=>$laFacturaPaciente['TIPOIDE_HOMOLOGO'],
			'numDocumentoIdentificacion'=>$laFacturaPaciente['IDEPACIENTE'],
			'tipoUsuario'=>$lcTipoUsuario,
			'fechaNacimiento'=>$lcFechaNacimiento,
			'codSexo'=>isset($laFacturaPaciente['SEXOPACIENTE'])?$laFacturaPaciente['SEXOPACIENTE']:'',
			'codPaisResidencia'=>isset($laFacturaPaciente['HOMPAISRESIDENCIA'])?$laFacturaPaciente['HOMPAISRESIDENCIA']:'',
			'codMunicipioResidencia'=>$lcCiudadResidencia,
			'codZonaTerritorialResidencia'=>$lcZonaResidencia,
			'incapacidad'=>$lcTieneIncapacidad,
			'codPaisOrigen'=>isset($laFacturaPaciente['HOMPAISNACIMIENTO'])?$laFacturaPaciente['HOMPAISNACIMIENTO']:'',
			'consecutivo'=>1,
		];

		$laDatosTabla=[
			'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
			'tipodato'=>'US',
			'tipoconsumo'=>'',
			'consecutivoconsumo'=>1,
		];
		$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);
		return $laDatos;
	}

	function crearRipsRecienNacido($taDatosRecibe)
	{
		$laDatos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lcViaRecienNacido='04';
		$lcPesoPaciente=$lcEdadGestacionalPaciente=$lcNroPrenatalesPaciente='';

		$laFacturaPaciente=$this->obtenerDatosFacturaPaciente($tnFactura);
		$lcViaIngreso=isset($laFacturaPaciente['VIA_INGRESO'])?$laFacturaPaciente['VIA_INGRESO']:'';
		if ($lcViaIngreso==$lcViaRecienNacido){		
			$lcFechaNacimiento=AplicacionFunciones::formatFechaHora('fecha',$laFacturaPaciente['FECHANACIMIENTO'],'-');
			$laDiagnosticos=$this->consultarDiagnosticoConsulta($tnIngreso,0,$this->nConsecutivoConsulta,'');
			$lcCiePrincipal=$laDiagnosticos['principal'];
			$lcDiagnosticoFallece=$this->aDatoFallece['codigodiagnosticofallece'];
			$lcFechaHoraFallece=$this->aDatoFallece['fechahorafallece'];
			$lcCondicionEgreso=$this->consultarCondicionDestinoEgreso($tnIngreso,$lcViaIngreso,$lcDiagnosticoFallece,'U');
			$lcFechHoraEgreso=!empty($lcDiagnosticoFallece)?$lcFechaHoraFallece:$this->consultarFechaEgresoUrgencias($tnIngreso);
			$laDatosRecienNacido=$this->consultarDatosRecienNacido($tnIngreso,$this->nConsecutivoConsulta);
			$lcPesoPaciente=$laDatosRecienNacido['pesopaciente'];
			$lcEdadGestacionalPaciente=$laDatosRecienNacido['edadgestacional'];
			$lcNroPrenatalesPaciente=$laDatosRecienNacido['consultasprenatales'];
			
			$laDatos=[
				'codPrestador'=>$this->cCodigoHabilitacion,
				'tipoDocumentoIdentificacion'=>$laFacturaPaciente['TIPOIDE_HOMOLOGO'],
				'numDocumentoIdentificacion'=>$laFacturaPaciente['IDEPACIENTE'],
				'fechaNacimiento'=>$lcFechaNacimiento,
				'edadGestacional'=>$lcEdadGestacionalPaciente,
				'numConsultasCPrenatal'=>$lcNroPrenatalesPaciente,
				'codSexoBiologico'=>isset($laFacturaPaciente['SEXOPACIENTE'])?$laFacturaPaciente['SEXOPACIENTE']:'',
				'peso'=>$lcPesoPaciente,
				'codDiagnosticoPrincipal'=>$lcCiePrincipal,
				'condicionDestinoUsuarioEgreso'=>$lcCondicionEgreso,
				'codDiagnosticoCausaMuerte'=>$lcDiagnosticoFallece,
				'fechaEgreso'=>$lcFechHoraEgreso,
				'consecutivo'=>1,
			];

			$laDatosTabla=[
				'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
				'tipodato'=>'AN',
				'tipoconsumo'=>'',
				'consecutivoconsumo'=>$this->nConsecutivoConsulta,
			];
			$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);
		}
		return $laDatos;
	}

	function obtenerDatosRipsConsulta($taParametrosRecibe)
	{
		$laDatosConsultas=[];
		$lcTipoFacturaNota=$taParametrosRecibe['tipofacturanota'] ?? '';
		$lnFactura=$taParametrosRecibe['factura'] ?? 0;
		$lnIngresoOrigen=$taParametrosRecibe['ingreso'] ?? 0;
		$lnNumeroNota=$taParametrosRecibe['numeronota'] ?? 0;
		$lcTipoConsumo=$taParametrosRecibe['tipoconsumo'] ?? '';
		if ($lcTipoFacturaNota=='FAC'){
			$laDatosConsultas = $this->oDb
				->select('trim(A.CUPDFA) CUPS, trim(A.ELEDFA) AUXILIAR, C.CNOEST CONSECCITA, INT(A.RMEDFA) REGISTRO_MEDICO')
				->select('A.VPRDFA VALOR, A.FINDFA FECHA_COBRO')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO, A.CNSDFA CONSECUTIVOCONSUMO, A.VNGDFA VIAINGRESO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('FACDETF A')
				->leftJoin("RIAESTM C", "A.INGDFA=C.INGEST AND A.CNSDFA=C.CNSEST AND A.TINDFA=C.TINEST", null)
				->leftJoin("RIARGMN D", "A.RMEDFA=D.REGMED", null)
				->where('A.INGDFA', '=', $lnIngresoOrigen)
				->where('A.NFADFA', '=', $lnFactura)
				->where('A.TINDFA', '=', $lcTipoConsumo)
				->notLike('A.DOCDFA', 'A%')
				->getAll('array');
		}else{
			$laDatosConsultas = $this->oDb
				->select('trim(A.CUPDET) CUPS, trim(A.ELEDET) AUXILIAR, C.CNOEST CONSECCITA, A.RMEDET REGISTRO_MEDICO, A.VLRADET VALOR')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO')
				->select('C.VNGEST VIAINGRESO, A.FREDET FECHA_COBRO, A.HREDET HORA_COBRO, C.CNSEST CONSECUTIVOCONSUMO')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('AMGLDET A')
				->leftJoin("RIAESTM C", "A.INGDET=C.INGEST AND A.CNSDET=C.CNSEST AND A.TIPDET=C.TINEST", null)
				->leftJoin("RIARGMN D", "A.RMEDET=D.NIDRGM", null)
				->where('A.FRADET', '=', $lnFactura)
				->where('A.INGDET', '=', $lnIngresoOrigen)
				->where('A.TIPDET', '=', $lcTipoConsumo)
				->where('A.NOTDET', '=', $lnNumeroNota)
				->getAll('array');
		}	
		return $laDatosConsultas;
	}
		
	function crearRipsConsulta($taDatosRecibe)
	{
		$laDatos=$laDatosRipsConsulta=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$tnNumeroNota=$taDatosRecibe['numeronota'] ?? 0;
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lnIncrementarConsultas=$lnFechaOrdenado=0;
		$lcTipoConsumo='400';
		$lnIngresoOrigen=$tnIngreso;
		$lcTipoDatoRips='AC';
		
		$laDatosEnviar=[
			'tipofacturanota'=>$tcTipoFacturaNota,
			'factura'=>$tnFactura,
			'ingreso'=>$lnIngresoOrigen,
			'tipoconsumo'=>$lcTipoConsumo,
			'numeronota'=>$tnNumeroNota,
		];
		$laDatosRipsConsulta=$this->obtenerDatosRipsConsulta($laDatosEnviar);
		
		if (is_array($laDatosRipsConsulta) && count($laDatosRipsConsulta)>0) {	
			foreach($laDatosRipsConsulta as $lalistaConsultas){
				$tnIngreso=$lnIngresoOrigen;
				$lcFechaHoraCita=$llFechaFormula=$lcNumeroAutorizacion=$lcCausaExterna=$laDiagnosticos=$lcGrupoServicio='';
				$lcCiePrincipal=$lcCieRelacionado1=$lcCieRelacionado2=$lcCieRelacionado3=$lcTipoDiagnostico=$lcFinalidadConsulta='';
				$lcCodigoServicio=$lcEspecialidadCups=$lcCupsOriginal=$lcTextoError='';
				$lnConsecutivoConsulta=$lnConsecutivoEvolucion=0;
				$lnConsConsumo=intval($lalistaConsultas['CONSECUTIVOCONSUMO']);
				$lnConsecCita=isset($lalistaConsultas['CONSECCITA'])?intval($lalistaConsultas['CONSECCITA']):0;
				$lnFechaConsumo=isset($lalistaConsultas['FECHA_COBRO'])?intval($lalistaConsultas['FECHA_COBRO']):0;
				$lcRegistroMedico=isset($lalistaConsultas['REGISTRO_MEDICO'])?$lalistaConsultas['REGISTRO_MEDICO']:'';
				$lcTipoRegistroMedico=isset($lalistaConsultas['TIPOIDE_HOMOLOGO'])?$lalistaConsultas['TIPOIDE_HOMOLOGO']:'';
				$lcViaIngreso=isset($lalistaConsultas['VIAINGRESO'])?$lalistaConsultas['VIAINGRESO']:'';
				$laDatosTraslados=$this->consultarTrasladoConsumos($tnIngreso,$lnConsConsumo);
				$tnIngreso=isset($laDatosTraslados['ingresotraslado'])?$laDatosTraslados['ingresotraslado']:$tnIngreso;
				$lnConsecCita=isset($laDatosTraslados['citatraslado'])?$laDatosTraslados['citatraslado']:$lnConsecCita;
				$lcGrupoServicio=$this->consultarGrupoServicioCups($tnIngreso,$lcViaIngreso,'C');
				
				if (isset($lalistaConsultas['CUPS']) || isset($lalistaConsultas['AUXILIAR'])){
					$lcCodigoCups=mb_substr(trim($lalistaConsultas['CUPS']),0,1)=='C'?trim($lalistaConsultas['AUXILIAR']):trim($lalistaConsultas['CUPS']);
					$lcCupsOriginal=trim($lcCodigoCups);
					$lcCodigoCups=mb_substr(trim($lcCodigoCups),0,6);
					$lnValorConsulta=mb_substr(trim($lalistaConsultas['CUPS']),0,1)=='C'?0:(isset($lalistaConsultas['VALOR'])?intval($lalistaConsultas['VALOR']):0);
				}

				$laAplicaCups = $this->oDb
					->select('CAPCUP FINALIDAD_CUP, TRIM(RF1CUP) CLASIFICACION1')
					->from('RIACUP')
					->where('CODCUP', '=', $lcCodigoCups)
					->in('CADCUP', $this->aProcedimentosConsulta)
					->get('array');
				if ($this->oDb->numRows()>0){
					if ($lnConsecCita>0){
						$laFechaHoraCita = $this->oDb
						->select('NINORD, CCIORD, COAORD, EVOORD, CODORD, CCOORD, FRLORD, HOCORD, FERORD, HRLORD')
						->from('RIAORD')
						->where('NINORD', '=', $tnIngreso)
						->where('CCIORD', '=', $lnConsecCita)
						->get('array');
						if ($this->oDb->numRows()>0){
							$lcEspecialidadCups=isset($laFechaHoraCita['CODORD'])?trim($laFechaHoraCita['CODORD']):'';
							$lnConsecutivoConsulta=intval($laFechaHoraCita['CCOORD']);
							$lnConsecutivoEvolucion=isset($laFechaHoraCita['EVOORD'])?intval($laFechaHoraCita['EVOORD']):0;
							$lnFechaOrdenado=isset($laFechaHoraCita['FRLORD'])?$laFechaHoraCita['FRLORD']:0;
							$lnFechaOrdenado=$lnFechaOrdenado==0?$laFechaHoraCita['FERORD']:$lnFechaOrdenado;
							$lnHoraOrdenado=isset($laFechaHoraCita['HOCORD'])?str_pad($laFechaHoraCita['HOCORD'], 6, '0', STR_PAD_LEFT):0;
							$lnHoraOrdenado=$lnHoraOrdenado==0?str_pad($laFechaHoraCita['HRLORD'], 6, '0', STR_PAD_LEFT):$lnHoraOrdenado;
							$lcFHRealizaCompara=date('Y-m-d H:i:s', strtotime($lnFechaOrdenado.$lnHoraOrdenado));
							
							if($lcFHRealizaCompara>$this->cFechaHoraEgresoCompara) {
								$lcFechaHoraCita=$this->cFechaHoraEgreso;
							}else{
								$llFechaFormulaOrdenado=$lnFechaOrdenado.$lnHoraOrdenado;
								$lcFechaHoraCita=date_format(date_create_from_format('YmdHis', $llFechaFormulaOrdenado), 'Y-m-d H:i');
							}
						}
						$lcNumeroAutorizacion=$this->consultaAutorizacionConsumo($tnIngreso,$lnConsecCita,'',$lcCodigoCups);
					}
					$lnFechaConsumo=intval($lnFechaOrdenado)>0?$lnFechaOrdenado:$lnFechaConsumo;
					$lcModalidadGrupoServicio=$this->consultarModalidadGrupoServicio($tnIngreso,$lnConsecutivoConsulta,'C',$lcCodigoCups);
					$lcCausaExterna=$this->obtenerCausaExterna($tnIngreso,$lnConsecCita);
					$laDiagnosticos=$this->consultarDiagnosticoConsulta($tnIngreso,$lnConsecCita,$lnConsecutivoConsulta,$lcCupsOriginal);
					$lcCiePrincipal=$laDiagnosticos['principal'];
					$lcCieRelacionado1=$laDiagnosticos['relacionado1'];
					$lcCieRelacionado2=$laDiagnosticos['relacionado2'];
					$lcCieRelacionado3=$laDiagnosticos['relacionado3'];
					$lcTipoDiagnostico=$laDiagnosticos['tipodiagnostico'];
					$lcFinalidadConsulta=$laDiagnosticos['finalidad'];
					
					if(empty($lcFinalidadConsulta)){
						$lcFinalidadConsulta=$this->consultarProcedimientoFinalidad($tnIngreso,'',$lnConsecutivoConsulta);
					}	
					$laDiagnosticosInt=$this->consultarDiagnosticoProcedimiento($tnIngreso,$lcCodigoCups,$lnConsecCita,$lnConsecutivoEvolucion,$lnFechaConsumo);
					$lcCiePrincipal=(empty($lcCiePrincipal) && !empty($laDiagnosticosInt['principal']))?$laDiagnosticosInt['principal']:$lcCiePrincipal;
					$lcTipoDiagnostico=(empty($lcTipoDiagnostico) && !empty($laDiagnosticosInt['tipodiagnostico']))?$laDiagnosticosInt['tipodiagnostico']:$lcTipoDiagnostico;
					$lcTipoDiagnostico=!empty($lcTipoDiagnostico)?$lcTipoDiagnostico:$this->consultaTipoDiagnostico($tnIngreso,$lnConsecutivoConsulta);

					if ($this->nValorCopagoCuota>0 && $this->nAplicaCopagoCuota==0 && (in_array($this->cTipoPagoModerador, $this->aReacaudoConsulta))){
						$lnValorPagoModerador=$this->nValorCopagoCuota;
						$lcTipoPagoModerador=$this->cTipoPagoModerador;
						$lcFacturaModeradora=$this->cFacturaPagoModerador;
						$this->nAplicaCopagoCuota=1;
					}else{
						$lnValorPagoModerador=0;
						$lcTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
						$lcFacturaModeradora='';
					}
					
					if (empty($lcEspecialidadCups)){
						$lcEspecialidadCups=$this->obtenerEspecialidadProcedimiento($lcCodigoCups);
					}	
					$lcCodigoServicio=$this->obtenerServicioProcedimiento($lcCodigoCups,$lcEspecialidadCups,'C','');
					
					if (intval($lcRegistroMedico)==0){
						if (!empty($lcEspecialidadCups)){
							$laTratante=$this->consultarMedicoRealizaEspecialidad($lcEspecialidadCups);
							$lcTipoRegistroMedico=$laTratante['tipoiderealiza'];
							$lcRegistroMedico=$laTratante['nroiderealiza'];
						}	
					}
					$lnIncrementarConsultas++;
					$laDatos=[			
							'codPrestador'=>$this->cCodigoHabilitacion,
							'fechaInicioAtencion'=>$lcFechaHoraCita,
							'numAutorizacion'=>$lcNumeroAutorizacion,
							'codConsulta'=>$lcCodigoCups,
							'modalidadGrupoServicioTecSal'=>$lcModalidadGrupoServicio,
							'grupoServicios'=>$lcGrupoServicio,
							'codServicio'=>$lcCodigoServicio,
							'finalidadTecnologiaSalud'=>$lcFinalidadConsulta,
							'causaMotivoAtencion'=>$lcCausaExterna,
							'codDiagnosticoPrincipal'=>$lcCiePrincipal,
							'codDiagnosticoRelacionado1'=>!empty($lcCieRelacionado1)?$lcCieRelacionado1:null,
							'codDiagnosticoRelacionado2'=>!empty($lcCieRelacionado2)?$lcCieRelacionado2:null,
							'codDiagnosticoRelacionado3'=>!empty($lcCieRelacionado3)?$lcCieRelacionado3:null,
							'tipoDiagnosticoPrincipal'=>$lcTipoDiagnostico,
							'tipoDocumentoIdentificacion'=>$lcTipoRegistroMedico,
							'numDocumentoIdentificacion'=>$lcRegistroMedico,
							'vrServicio'=>$lnValorConsulta,
							'conceptoRecaudo'=>$lcTipoPagoModerador,
							'valorPagoModerador'=>$lnValorPagoModerador,
							'numFEVPagoModerador'=>$lcFacturaModeradora,
							'consecutivo'=>$lnIncrementarConsultas,
					];
					$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
					$lcTextoError=!empty($lcTextoError)?$lcCodigoCups.'~'.$lcTextoError:'';
					$this->nCantidadProcedimientosEnRips++;
					$laDatosTabla=[
						'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
						'tipodato'=>$lcTipoDatoRips,
						'tipoconsumo'=>$lcTipoConsumo,
						'consecutivoconsumo'=>$lnConsConsumo,
						'error'=>$lcTextoError,
					];
					$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

					if (!empty($lcTextoError)){
						$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
					}
				}	
			}
		} 
		unset($laDatosTraslados,$laFechaHoraCita);
		return $laDatos;
	}
	
	function verificarCantidadProcedimientos()
	{
		$lcCantidadError='';
		$laDatosEnviar=[];

		if ($this->nCantidadProcedimientosFacturados != $this->nCantidadProcedimientosEnRips){
			$lcCantidadError="Existen procedimiento(s) sin registrar en rips";
			$laDatosTabla=[
				'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
				'tipodato'=>'',
				'tipoconsumo'=>'',
				'consecutivoconsumo'=>0,
				'error'=>$lcCantidadError,
			];
			$this->insertarDatos('RIPREA',$laDatosTabla,$laDatosEnviar);
		}
		return $lcCantidadError;
	}

	function verificarDatosObligatorios($taDatosVerificar,$tcTipoRips)
	{
		$lcTextoVerificar='';

		if ($tcTipoRips=='AC'){
			$lcTextoVerificar=empty($taDatosVerificar['tipoDiagnosticoPrincipal'])?$lcTextoVerificar.'tipo diagnostico principal~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AC' || $tcTipoRips=='AP'){
			$lcTextoVerificar=empty($taDatosVerificar['grupoServicios'])?$lcTextoVerificar.'grupo servicio~':$lcTextoVerificar;
			$lcTextoVerificar=empty($taDatosVerificar['codServicio'])?$lcTextoVerificar.'codigo servicio~':$lcTextoVerificar;
			$lcTextoVerificar=empty($taDatosVerificar['finalidadTecnologiaSalud'])?$lcTextoVerificar.'finalidad tecnologia~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AC' || $tcTipoRips=='AT' || $tcTipoRips=='AM' || $tcTipoRips=='AP'){
			$lcTextoVerificar=empty($taDatosVerificar['tipoDocumentoIdentificacion'])?$lcTextoVerificar.'tipo identificacion~':$lcTextoVerificar;
			$lcTextoVerificar=empty($taDatosVerificar['numDocumentoIdentificacion'])?$lcTextoVerificar.'numero identificacion~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AC' || $tcTipoRips=='AH' || $tcTipoRips=='AU'){
			$lcTextoVerificar=empty($taDatosVerificar['causaMotivoAtencion'])?$lcTextoVerificar.'motivo atencion~':$lcTextoVerificar;
		} 
 	
		if ($tcTipoRips=='AC' || $tcTipoRips=='AH' || $tcTipoRips=='AU' || $tcTipoRips=='AM' || $tcTipoRips=='AP'){
			$lcTextoVerificar=empty($taDatosVerificar['codDiagnosticoPrincipal'])?$lcTextoVerificar.'diagnostico principal~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AH' || $tcTipoRips=='AU'){
			$lcTextoVerificar=empty($taDatosVerificar['condicionDestinoUsuarioEgreso'])?$lcTextoVerificar.'condicion destino usuario~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AU'){
			$lcTextoVerificar=empty($taDatosVerificar['fechaEgreso'])?$lcTextoVerificar.'fecha egreso~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AT'){
			$lcTextoVerificar=empty($taDatosVerificar['tipoOS'])?$lcTextoVerificar.'tipo otros servicios~':$lcTextoVerificar;
			$lcTextoVerificar=empty($taDatosVerificar['codTecnologiaSalud'])?$lcTextoVerificar.'codigo tecnologia~':$lcTextoVerificar;
			$lcTextoVerificar=$taDatosVerificar['cantidadOS']==0?$lcTextoVerificar.'cantidad~':$lcTextoVerificar;
		}

		if ($tcTipoRips=='AM'){
			$lcTextoVerificar=$taDatosVerificar['unidadMinDispensa']==0?$lcTextoVerificar.'unidad minima dispensacion~':$lcTextoVerificar;
			$lcTextoVerificar=$taDatosVerificar['cantidadMedicamento']==0?$lcTextoVerificar.'cantidad~':$lcTextoVerificar;
		}	

		if (!$this->llRipsConError && !empty($lcTextoVerificar)){
			$this->llRipsConError=true;
		}
		return $lcTextoVerificar;
	}

	function crearRipsPaquete($taDatosRecibe)
	{
		$laDatos=$laDatosProfesional=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$tnNroNota=$taDatosRecibe['numeronota'] ?? 0;
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$this->nUltimoConsecutivoCups=$this->nUltimoConsecutivoCups+1;
		$lcMipresCups=$lcNumeroAutorizacion=$lcViaIngresoProcedimiento=$lcGrupoServicio=$lcCodigoServicio='';
		$lcFinalidadConsulta=$lcRegistroMedico=$lcCiePrincipal=$lcTipoRegistroMedico='';
		$lcModalidadGrupoServicio=$lcEspecialidadCups=$lcTipoCups='';
		$laFacturaPaciente=$this->obtenerDatosFacturaPaciente($tnFactura);
		$lcViaIngreso=isset($laFacturaPaciente['VIA_INGRESO'])?$laFacturaPaciente['VIA_INGRESO']:'';
		$lcViaIngresoProcedimiento=$this->obtieneViaIngresoUsuarioServicio($tnIngreso,$lcViaIngreso,'P','');
		$lcGrupoServicio=$this->consultarGrupoServicioCups($tnIngreso,$lcViaIngreso,'P');
		$lcTipoDatoRips='AP';

		if ($tcTipoFacturaNota=='FAC'){
			$lcTextoError='';
			$laPaquete = $this->oDb
				->select('trim(A.CUPDFA) CUPS, SUM(VPRDFA) SUMA')
				->from('FACDETF A')->where('A.INGDFA', '=', $tnIngreso)
				->where('A.NFADFA', '=', $tnFactura)
				->Like('A.CUPDFA', 'C%')
				->notLike('A.DOCDFA', 'A%')
				->groupBy('A.CUPDFA')->get('array');
			if ($this->oDb->numRows()>0){	
				$lcCodigoCups=mb_substr(trim($laPaquete['CUPS']),1,6);
				$lnValorConsulta=intval($laPaquete['SUMA']);
				$lcModalidadGrupoServicio=$this->consultarModalidadGrupoServicio($tnIngreso,0,'P',$lcCodigoCups);
				
				$laAplicaCups = $this->oDb
					->select('CAPCUP FINALIDAD_CUP, TRIM(RF1CUP) CLASIFICACION1, TRIM(ESPCUP) ESPECIALIDADCUP')
					->from('RIACUP')
					->where('CODCUP', '=', $lcCodigoCups)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcTipoCups=isset($laAplicaCups['CLASIFICACION1'])?trim($laAplicaCups['CLASIFICACION1']):'';
					$lcEspecialidadCups=isset($laAplicaCups['ESPECIALIDADCUP'])?trim($laAplicaCups['ESPECIALIDADCUP']):'';
				}	
				
				$laDatosProfesional=[
						'nrofactura'=>$tnFactura,
						'nroingreso'=>$tnIngreso,
						'consecutivocita'=>0,
						'codigoconsumo'=>$lcCodigoCups,
						'fechaconsumo'=>0,
						'tipoconsumo'=>'400',
						'consecutivocirugia'=>0,
						'especialidadprocedimiento'=>$lcEspecialidadCups,
				];
				$lcCodigoServicio=$this->obtenerServicioProcedimiento($lcCodigoCups,$lcEspecialidadCups,'P',$lcTipoCups);
				$lcFinalidadConsulta=$this->consultarProcedimientoFinalidad($tnIngreso,$lcCodigoCups,0);
				$laRegistroMedico=$this->consultarProfesionalRealiza($laDatosProfesional);
				$lcTipoRegistroMedico=$laRegistroMedico['tipoiderealiza'];
				$lcRegistroMedico=$laRegistroMedico['nroiderealiza'];
				$laDiagnosticosInt=$this->consultarDiagnosticoProcedimiento($tnIngreso,$lcCodigoCups,0,0,0);
				$lcCiePrincipal=!empty($laDiagnosticosInt['principal'])?$laDiagnosticosInt['principal']:'';

				if ($this->nValorCopagoCuota>0 && $this->nAplicaCopagoCuota==0 && (in_array($this->cTipoPagoModerador, $this->aReacaudoProcedimiento))){
					$lnValorPagoModerador=$this->nValorCopagoCuota;
					$lcTipoPagoModerador=$this->cTipoPagoModerador;
					$lcFacturaModeradora=$this->cFacturaPagoModerador;
					$this->nAplicaCopagoCuota=1;
				}else{
					$lnValorPagoModerador=0;
					$lcTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
					$lcFacturaModeradora='';
				}

				$laDatos=[
						'codPrestador'=>$this->cCodigoHabilitacion,
						'fechaInicioAtencion'=>$this->cFechaHoraEgreso,
						'idMIPRES'=>$lcMipresCups,
						'numAutoriacion'=>$lcNumeroAutorizacion,
						'codProcedimiento'=>$lcCodigoCups,
						'viaIngresoServicioSalud'=>$lcViaIngresoProcedimiento,
						'modalidadGrupoServicioTecSal'=>$lcModalidadGrupoServicio,
						'grupoServicios'=>$lcGrupoServicio,
						'codServicio'=>$lcCodigoServicio,
						'finalidadTecnologiaSalud'=>$lcFinalidadConsulta,
						'tipoDocumentoIdentificacion'=>$lcTipoRegistroMedico,
						'numDocumentoIdentificacion'=>$lcRegistroMedico,
						'codDiagnosticoPrincipal'=>$lcCiePrincipal,
						'codDiagnosticoRelacionado'=>null,
						'codComplicacion'=>null,
						'vrServicio'=>$lnValorConsulta,
						'conceptoRecaudo'=>$lcTipoPagoModerador,
						'valorPagoModerador'=>$lnValorPagoModerador,
						'numFEVPagoModerador'=>$lcFacturaModeradora,
						'consecutivo'=>$this->nUltimoConsecutivoCups,
				];
				$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
				$lcTextoError=!empty($lcTextoError)?$lcCodigoCups.'~'.$lcTextoError:'';

				$laDatosTabla=[
					'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
					'tipodato'=>$lcTipoDatoRips,
					'tipoconsumo'=>'400',
					'consecutivoconsumo'=>0,
					'error'=>$lcTextoError,
				];
				$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

				if (!empty($lcTextoError)){
					$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
				}
			}	
		}
		unset($laPaquete);
		return $laDatos;
	}
	
	function obtenerDatosRipsProcedimientos($taParametrosRecibe)
	{
		$laDatosProcedimientos=[];
		$lcTipoFacturaNota=$taParametrosRecibe['tipofacturanota'] ?? '';
		$lnFactura=$taParametrosRecibe['factura'] ?? 0;
		$lnIngresoOrigen=$taParametrosRecibe['ingreso'] ?? 0;
		$lnNumeroNota=$taParametrosRecibe['numeronota'] ?? 0;
		$lcTipoConsumo=$taParametrosRecibe['tipoconsumo'] ?? '';
		
		if ($lcTipoFacturaNota=='FAC'){
			$laDatosProcedimientos = $this->oDb
			->select('trim(A.CUPDFA) CUPS, trim(A.ELEDFA) AUXILIAR, C.CNOEST CONSECCITA, INT(A.RMEDFA) REGISTRO_MEDICO, A.VPRDFA VALOR')
			->select('A.VNGDFA VIAINGRESO, A.FINDFA FECHA_COBRO, A.HINDFA HORA_COBRO, A.CNSDFA CONSECUTIVOCONSUMO, C.CNCEST CONSECCIRUGIA')
			->select('trim(D.TIDRGM) TIPOIDE_MEDICO, TRIM(C.DPTEST) ESPECIALIDADGRABA')
			->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
			->select('(SELECT TRIM(RMRLAB) FROM LABRES WHERE NIGING=A.INGDFA AND CONORD=C.CNOEST ORDER BY CONLAB DESC FETCH FIRST 1 ROW ONLY) AS REG_LABORATORIO')
			->from('FACDETF A')
			->leftJoin("RIACUP B", "A.CUPDFA=B.CODCUP", null)
			->leftJoin("RIAESTM C", "A.INGDFA=C.INGEST AND A.CNSDFA=C.CNSEST AND A.TINDFA=C.TINEST", null)
			->leftJoin("RIARGMN D", "A.RMEDFA=D.REGMED", null)
			->where('A.INGDFA', '=', $lnIngresoOrigen)
			->where('A.NFADFA', '=', $lnFactura)
			->where('A.TINDFA', '=', $lcTipoConsumo)
			->notLike('A.DOCDFA', 'A%')
			->getAll('array');
		}else{
			$laDatosProcedimientos = $this->oDb
			->select('trim(A.CUPDET) CUPS, trim(A.ELEDET) AUXILIAR, C.CNOEST CONSECCITA, A.RMEDET REGISTRO_MEDICO, A.VLRADET VALOR')
			->select('C.VNGEST VIAINGRESO, A.FREDET FECHA_COBRO, A.HREDET HORA_COBRO, C.CNSEST CONSECUTIVOCONSUMO, C.CNCEST CONSECCIRUGIA')
			->select('trim(D.TIDRGM) TIPOIDE_MEDICO, TRIM(C.DPTEST) ESPECIALIDADGRABA')
			->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
			->select('(SELECT TRIM(RMRLAB) FROM LABRES WHERE NIGING=A.INGDET AND CONORD=C.CNOEST ORDER BY CONLAB DESC FETCH FIRST 1 ROW ONLY) AS REG_LABORATORIO')
			->from('AMGLDET A')
			->leftJoin("RIACUP B", "A.CUPDET=B.CODCUP", null)
			->leftJoin("RIAESTM C", "A.INGDET=C.INGEST AND A.CNSDET=C.CNSEST AND A.TIPDET=C.TINEST", null)
			->leftJoin("RIARGMN D", "A.RMEDET=D.NIDRGM", null)
			->where('A.FRADET', '=', $lnFactura)
			->where('A.INGDET', '=', $lnIngresoOrigen)
			->where('A.TIPDET', '=', $lcTipoConsumo)
			->where('A.NOTDET', '=', $lnNumeroNota)
			->getAll('array');
		}		
		return $laDatosProcedimientos;
	}
	
	function crearRipsProcedimientos($taDatosRecibe)
	{
		$laDatos=$laDatosRipsProcedimientos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$tnNumeroNota=$taDatosRecibe['numeronota'] ?? 0;
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lcTipoConsumo='400';
		$this->nUltimoConsecutivoCups=0;
		$lnIngresoOrigen=$tnIngreso;
		$lcTipoDatoRips='AP';
		
		$laDatosEnviar=[
			'tipofacturanota'=>$tcTipoFacturaNota,
			'factura'=>$tnFactura,
			'ingreso'=>$lnIngresoOrigen,
			'tipoconsumo'=>$lcTipoConsumo,
			'numeronota'=>$tnNumeroNota,
		];
		$laDatosRipsProcedimientos=$this->obtenerDatosRipsProcedimientos($laDatosEnviar);
		if (is_array($laDatosRipsProcedimientos) && count($laDatosRipsProcedimientos)>0) {				
			foreach($laDatosRipsProcedimientos as $lalistaCups){
				$tnIngreso=$lnIngresoOrigen;
				$laDatos=$laDatosProfesional=[];
				$lcTipoIdeLaboratorio=$lcNroIdeLaboratorio=$lcFinalidadConsulta=$lcTextoError='';
				$lcNumeroAutorizacion=$lcViaIngresoProcedimiento=$lcGrupoServicio=$lcCieRelacionadoCups=$lcCiePrincipal='';
				$lcCieComplicacionCups=$lcFechaHoraCups=$lcCodigoCups=$lcEspecialidadCups=$lcCodigoServicio='';
				$lnConsecutivoEvolucion=0;
				$lnConsConsumo=intval($lalistaCups['CONSECUTIVOCONSUMO']);
				$lnConsConsumoInicial=$lnConsConsumo;
				$lcEspecialidadCupsCobra=isset($lalistaCups['ESPECIALIDADGRABA'])?trim($lalistaCups['ESPECIALIDADGRABA']):'';
				$lnFechaConsumo=isset($lalistaCups['FECHA_COBRO'])?intval($lalistaCups['FECHA_COBRO']):0;
				$lnHoraConsumo=isset($lalistaCups['HORA_COBRO'])?str_pad($lalistaCups['HORA_COBRO'], 6, '0', STR_PAD_LEFT):0;
				$lnConsecutivoCirugia=$lalistaCups['CONSECCIRUGIA'] ?? 0;

				if (isset($lalistaCups['CUPS']) || isset($lalistaCups['AUXILIAR'])){
					$lcCodigoCups=mb_substr(trim($lalistaCups['CUPS']),0,1)=='C'?trim($lalistaCups['AUXILIAR']):trim($lalistaCups['CUPS']);
					$lcCodigoCups=mb_substr(trim($lcCodigoCups),0,6);
					$lnValorConsulta=mb_substr(trim($lalistaCups['CUPS']),0,1)=='C'?0:(isset($lalistaCups['VALOR'])?intval($lalistaCups['VALOR']):0);
				}
				
				$laAplicaCups = $this->oDb
					->select('CAPCUP FINALIDAD_CUP, TRIM(RF1CUP) CLASIFICACION1, TRIM(ESPCUP) ESPECIALIDADCUP')
					->from('RIACUP')
					->where('CODCUP', '=', $lcCodigoCups)
					->in('CADCUP', $this->aProcedimentosProcedimientos)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcTipoCups=isset($laAplicaCups['CLASIFICACION1'])?trim($laAplicaCups['CLASIFICACION1']):'';
					$lnConsecCita=isset($lalistaCups['CONSECCITA'])?intval($lalistaCups['CONSECCITA']):0;
					$lcEspecialidadProcedimiento=isset($laAplicaCups['ESPECIALIDADCUP'])?trim($laAplicaCups['ESPECIALIDADCUP']):'';
					$lcDatosRegLaboratorio=isset($lalistaCups['REG_LABORATORIO'])?trim($lalistaCups['REG_LABORATORIO']):'';
					$laDatosTraslados=$this->consultarTrasladoConsumos($tnIngreso,$lnConsConsumo);
					$tnIngreso=isset($laDatosTraslados['ingresotraslado'])?$laDatosTraslados['ingresotraslado']:$tnIngreso;
					$lnConsConsumo=isset($laDatosTraslados['consumotraslado'])?$laDatosTraslados['consumotraslado']:$lnConsConsumo;
					$lnConsecCita=isset($laDatosTraslados['citatraslado'])?$laDatosTraslados['citatraslado']:$lnConsecCita;

					if (!empty($lcDatosRegLaboratorio)){
						$laDatosLaboratorio=explode('-',$lcDatosRegLaboratorio);
						$laIdeLaboratorio=isset($laDatosLaboratorio[1])?trim($laDatosLaboratorio[1]):'';
						if (!empty($laIdeLaboratorio)){
							$laDatosIdeLaboratorio=explode(' ',$laIdeLaboratorio);
							$lcTipoIdeLaboratorio=$laDatosIdeLaboratorio[0];
							$lcNroIdeLaboratorio=$laDatosIdeLaboratorio[1];
						}
					}
					$lcRegistroMedico=!empty($lcNroIdeLaboratorio)?$lcNroIdeLaboratorio:(isset($lalistaCups['REGISTRO_MEDICO'])?$lalistaCups['REGISTRO_MEDICO']:'');
					$lcTipoRegistroMedico=!empty($lcTipoIdeLaboratorio)?$lcTipoIdeLaboratorio:(isset($lalistaCups['TIPOIDE_HOMOLOGO'])?$lalistaCups['TIPOIDE_HOMOLOGO']:'');
					
					if (empty($lcRegistroMedico)){
						$laDatosProfesional=[
								'nrofactura'=>$tnFactura,
								'nroingreso'=>$tnIngreso,
								'consecutivocita'=>$lnConsecCita,
								'codigoconsumo'=>$lcCodigoCups,
								'fechaconsumo'=>$lnFechaConsumo,
								'tipoconsumo'=>$lcTipoConsumo,
								'consecutivocirugia'=>$lnConsecutivoCirugia,
								'especialidadprocedimiento'=>$lcEspecialidadProcedimiento,
						];
						$laRegistroMedico=$this->consultarProfesionalRealiza($laDatosProfesional);
						$lcTipoRegistroMedico=$laRegistroMedico['tipoiderealiza'];
						$lcRegistroMedico=$laRegistroMedico['nroiderealiza'];
					}

					$lcViaIngreso=isset($lalistaCups['VIAINGRESO'])?$lalistaCups['VIAINGRESO']:'';
					$lcGrupoServicio=$this->consultarGrupoServicioCups($tnIngreso,$lcViaIngreso,'P');
					$lcViaIngresoProcedimiento=$this->obtieneViaIngresoUsuarioServicio($tnIngreso,$lcViaIngreso,'P','');
					$this->nUltimoConsecutivoCups++;
					
					if ($lnConsecCita>0){
						$laFechaHoraCita = $this->oDb
						->select('EVOORD, CODORD, CCOORD, FERORD, HRLORD')
						->from('RIAORD')
						->where('NINORD', '=', $tnIngreso)
						->where('COAORD', '=', $lcCodigoCups)
						->where('CCIORD', '=', $lnConsecCita)
						->get('array');
						if ($this->oDb->numRows()>0){
							$lcEspecialidadCups=isset($laFechaHoraCita['CODORD'])?trim($laFechaHoraCita['CODORD']):'';
							$lnConsecutivoEvolucion=isset($laFechaHoraCita['EVOORD'])?intval($laFechaHoraCita['EVOORD']):0;
							$lnFechaOrdenado=isset($laFechaHoraCita['FERORD'])?$laFechaHoraCita['FERORD']:0;
							$lnHoraOrdenado=isset($laFechaHoraCita['HRLORD'])?str_pad($laFechaHoraCita['HRLORD'], 6, '0', STR_PAD_LEFT):0;
							if (intval($lnFechaOrdenado)>0){
								$lcFHRealizaCompara=date('Y-m-d H:i:s', strtotime($lnFechaOrdenado.$lnHoraOrdenado));
								
								if($lcFHRealizaCompara>$this->cFechaHoraEgresoCompara) {
									$lcFechaHoraCups=$this->cFechaHoraEgreso;
								}else{
									$llFechaFormulaOrdenado=$lnFechaOrdenado.$lnHoraOrdenado;
									$lcFechaHoraCups=date_format(date_create_from_format('YmdHis', $llFechaFormulaOrdenado), 'Y-m-d H:i');
								}	
							}else{
								$lcFechaHoraCups=$this->cFechaHoraEgreso;	
							}	
						}else{
							$lcFHRealizaCompara=date('Y-m-d H:i:s', strtotime($lnFechaConsumo.$lnHoraConsumo));
							if($lcFHRealizaCompara>$this->cFechaHoraEgresoCompara) {
								$lcFechaHoraCups=$this->cFechaHoraEgreso;
							}else{
								$llFechaFormulaOrdenado=$lnFechaConsumo.$lnHoraConsumo;
								$lcFechaHoraCups=date_format(date_create_from_format('YmdHis', $llFechaFormulaOrdenado), 'Y-m-d H:i');
							}	
						}
					}
					$lcFinalidadConsulta=$this->consultarProcedimientoFinalidad($tnIngreso,$lcCodigoCups,$lnConsecCita);
					$laDiagnosticos=$this->consultarDiagnosticoProcedimiento($tnIngreso,$lcCodigoCups,$lnConsecCita,$lnConsecutivoEvolucion,$lnFechaConsumo);
					$lcCiePrincipal=$laDiagnosticos['principal'];
					$lcCieRelacionadoCups=$laDiagnosticos['relacionado1'];
					
					if ($this->nValorCopagoCuota>0 && $this->nAplicaCopagoCuota==0 && (in_array($this->cTipoPagoModerador, $this->aReacaudoProcedimiento))){	
						$lnValorPagoModerador=$this->nValorCopagoCuota;
						$lcTipoPagoModerador=$this->cTipoPagoModerador;
						$lcFacturaModeradora=$this->cFacturaPagoModerador;
						$this->nAplicaCopagoCuota=1;
					}else{
						$lnValorPagoModerador=0;
						$lcTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
						$lcFacturaModeradora='';
					}

					if (empty($lcEspecialidadCups)){
						$lcEspecialidadCups=$this->obtenerEspecialidadProcedimiento($lcCodigoCups);
					}
					$lcEspecialidadCups=empty($lcEspecialidadCups)?$lcEspecialidadCupsCobra:$lcEspecialidadCups;
					$lcCodigoServicio=$this->obtenerServicioProcedimiento($lcCodigoCups,$lcEspecialidadCups,'P',$lcTipoCups);
					$lcModalidadGrupoServicio=$this->consultarModalidadGrupoServicio($tnIngreso,0,'P',$lcCodigoCups);
					$laMipres=$this->obtenerMipres($tnIngreso,$lcCodigoCups,$lcTipoConsumo,$lnConsecCita);
					$lcMipresCups=isset($laMipres['idmipres'])?$laMipres['idmipres']:'';
					$lcNumeroAutorizacion=isset($laMipres['numeromipres'])?$laMipres['numeromipres']:'null';

					if (empty($this->cTipoIdeMedicoCups) && !empty($lcTipoRegistroMedico)){
						$this->cTipoIdeMedicoCups=$lcTipoRegistroMedico;
					}

					if (empty($this->cNumeroIdeMedicoCups) && !empty($lcRegistroMedico)){
						$this->cNumeroIdeMedicoCups=$lcRegistroMedico;
					}

					if (empty($this->cDiagnosticoCups) && !empty($lcCiePrincipal)){
						$this->cDiagnosticoCups=$lcCiePrincipal;
					}

					$laDatos=[
							'codPrestador'=>$this->cCodigoHabilitacion,
							'fechaInicioAtencion'=>$lcFechaHoraCups,
							'idMIPRES'=>$lcMipresCups,
							'numAutoriacion'=>$lcNumeroAutorizacion,
							'codProcedimiento'=>$lcCodigoCups,
							'viaIngresoServicioSalud'=>$lcViaIngresoProcedimiento,
							'modalidadGrupoServicioTecSal'=>$lcModalidadGrupoServicio,
							'grupoServicios'=>$lcGrupoServicio,
							'codServicio'=>$lcCodigoServicio,
							'finalidadTecnologiaSalud'=>$lcFinalidadConsulta,
							'tipoDocumentoIdentificacion'=>$lcTipoRegistroMedico,
							'numDocumentoIdentificacion'=>$lcRegistroMedico,
							'codDiagnosticoPrincipal'=>$lcCiePrincipal,
							'codDiagnosticoRelacionado'=>!empty($lcCieRelacionadoCups)?$lcCieRelacionadoCups:null,
							'codComplicacion'=>!empty($lcCieComplicacionCups)?$lcCieComplicacionCups:null,
							'vrServicio'=>$lnValorConsulta,
							'conceptoRecaudo'=>$lcTipoPagoModerador,
							'valorPagoModerador'=>$lnValorPagoModerador,
							'numFEVPagoModerador'=>$lcFacturaModeradora,
							'consecutivo'=>$this->nUltimoConsecutivoCups,
					];
					$this->nCantidadProcedimientosEnRips++;
					$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
					$lcTextoError=!empty($lcTextoError)?$lcCodigoCups.'~'.$lcTextoError:'';

					$laDatosTabla=[
						'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
						'tipodato'=>$lcTipoDatoRips,
						'tipoconsumo'=>$lcTipoConsumo,
						'consecutivoconsumo'=>$lnConsConsumoInicial,
						'error'=>$lcTextoError,
					];
					$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

					if (!empty($lcTextoError)){
						$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
					}
				}	
			}
		} 
		unset($laProcedimientos, $laFechaHoraCita);
		return $laDatos;
	}

	function obtenerDatosRipsMedicamentos($taDatosRecibe)
	{
		$laDatosMedicamentos=[];
		$lcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$lnFactura=$taDatosRecibe['factura'] ?? 0;
		$lnIngresoOrigen=$taDatosRecibe['ingreso'] ?? 0;
		$lnNumeroNota=$taDatosRecibe['numeronota'] ?? 0;
		$lcTipoConsumo=$taDatosRecibe['tipoconsumo'] ?? '';
		if ($lcTipoFacturaNota=='FAC'){
			$laDatosMedicamentos = $this->oDb
				->select('trim(A.CUPDFA) CUPS, trim(A.ELEDFA) AUXILIAR, INT(A.RMEDFA) REGISTRO_MEDICO')
				->select('A.TINDFA TIPOCONSUMO, A.VNGDFA VIAINGRESO, A.FINDFA FECHA_COBRO, A.HINDFA HORA_COBRO')
				->select('A.QCODFA CANTIDAD, A.VUNDFA VALOR_UNITARIO, A.VPRDFA VALOR_TOTAL, A.CNCDFA CONSECCIRUGIA')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO, A.CNSDFA CONSECUTIVOCONSUMO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('FACDETF A')
				->leftJoin("RIARGMN D", "A.RMEDFA=D.REGMED AND D.REGMED<>'0000000000000'", null)
				->where('A.INGDFA', '=', $lnIngresoOrigen)
				->where('A.NFADFA', '=', $lnFactura)
				->where('A.TINDFA', '=', $lcTipoConsumo)
				->notLike('A.DOCDFA', 'A%')
				->getAll('array');	
		}else{
			$laDatosMedicamentos = $this->oDb
				->select('trim(A.CUPDET) CUPS, trim(A.ELEDET) AUXILIAR, A.RMEDET REGISTRO_MEDICO')
				->select('A.TIPDET TIPOCONSUMO, C.VNGEST VIAINGRESO, A.FREDET FECHA_COBRO, A.HREDET HORA_COBRO')
				->select('A.QGLDET CANTIDAD, A.VUNDET VALOR_UNITARIO, A.VLRADET VALOR_TOTAL')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO, A.CNSDET CONSECUTIVOCONSUMO, C.CNOEST CONSECCITA, C.CNCEST CONSECCIRUGIA')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('AMGLDET A')
				->leftJoin("RIACUP B", "A.CUPDET=B.CODCUP", null)
				->leftJoin("RIAESTM C", "A.INGDET=C.INGEST AND A.CNSDET=C.CNSEST AND A.TIPDET=C.TINEST", null)
				->leftJoin("RIARGMN D", "A.RMEDET=D.NIDRGM", null)
				->where('A.FRADET', '=', $lnFactura)
				->where('A.INGDET', '=', $lnIngresoOrigen)
				->where('A.TIPDET', '=', $lcTipoConsumo)
				->where('A.NOTDET', '=', $lnNumeroNota)
				->getAll('array');
		}
		return $laDatosMedicamentos;
	}
	
	function crearRipsMedicamentos($taDatosRecibe)
	{
		$laDatos=$laDatosRipsMedicamentos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$tnNumeroNota=$taDatosRecibe['numeronota'] ?? 0;
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lnIncrementar=0;
		$lcTipoConsumo='500';
		$lnIngresoOrigen=$tnIngreso;
		$lcTipoDatoRips='AM';

		$laDatosEnviar=[
			'tipofacturanota'=>$tcTipoFacturaNota,
			'factura'=>$tnFactura,
			'ingreso'=>$lnIngresoOrigen,
			'tipoconsumo'=>$lcTipoConsumo,
			'numeronota'=>$tnNumeroNota,
		];
		$laDatosRipsMedicamentos=$this->obtenerDatosRipsMedicamentos($laDatosEnviar);
		if (is_array($laDatosRipsMedicamentos) && count($laDatosRipsMedicamentos)>0) {			
			foreach($laDatosRipsMedicamentos as $laListaMedicamentos){
				$tnIngreso=$lnIngresoOrigen;
				$lcMipres=$lcCodigoPaquete=$lcTextoError='';
				$lcCodigoPaquete=isset($laListaMedicamentos['CUPS'])?(mb_substr(trim($laListaMedicamentos['CUPS']),0,1)=='C'?'P':''):'';
				$lcCodigoShaio=isset($laListaMedicamentos['AUXILIAR'])?trim($laListaMedicamentos['AUXILIAR']):'';
				$lnConsConsumo=isset($laListaMedicamentos['CONSECUTIVOCONSUMO'])?intval($laListaMedicamentos['CONSECUTIVOCONSUMO']):0;
				$lnConsConsumoInicial=$lnConsConsumo;
				$lnCantidadMedicamento=isset($laListaMedicamentos['CANTIDAD'])?intval($laListaMedicamentos['CANTIDAD']):0;
				$lnValorUnitario=$lcCodigoPaquete==''?(isset($laListaMedicamentos['VALOR_UNITARIO'])?intval($laListaMedicamentos['VALOR_UNITARIO']):0):0;
				$lnValorTotal=$lcCodigoPaquete==''?(isset($laListaMedicamentos['VALOR_TOTAL'])?intval($laListaMedicamentos['VALOR_TOTAL']):0):0;
				$lnValorUnitario=$tcTipoFacturaNota=='FAC'?$lnValorUnitario:$lnValorTotal;
				$lnFechaConsumo=isset($laListaMedicamentos['FECHA_COBRO'])?intval($laListaMedicamentos['FECHA_COBRO']):0;
				$lnHoraConsumo=isset($laListaMedicamentos['HORA_COBRO'])?intval($laListaMedicamentos['HORA_COBRO']):0;
				$lnConsecutivoCirugia=$laListaMedicamentos['CONSECCIRUGIA'] ?? 0;
				$laDatosTraslados=$this->consultarTrasladoConsumos($tnIngreso,$lnConsConsumo);
				$tnIngreso=isset($laDatosTraslados['ingresotraslado'])?$laDatosTraslados['ingresotraslado']:$tnIngreso;
				$lnConsConsumo=isset($laDatosTraslados['consumotraslado'])?$laDatosTraslados['consumotraslado']:$lnConsConsumo;
				$laDatosMedicamentos=$this->consultarDatosMedicamento($tnIngreso,$lcCodigoShaio,$lnConsConsumo,$lnFechaConsumo,$lnHoraConsumo,$lnConsecutivoCirugia);
				$lcFechaHoraDispensacion=isset($laDatosMedicamentos['fechahoradispensacion'])?$laDatosMedicamentos['fechahoradispensacion']:'';
				$lcCiePrincipal=isset($laDatosMedicamentos['diagnosticoprincipal'])?$laDatosMedicamentos['diagnosticoprincipal']:'';
				$lcCodigoMedicamento=isset($laDatosMedicamentos['codigomedicamento'])?$laDatosMedicamentos['codigomedicamento']:'';
				$lcNombreMedicamento=isset($laDatosMedicamentos['nombremedicamento'])?$laDatosMedicamentos['nombremedicamento']:'';
				$lcTipoMedicamento=isset($laDatosMedicamentos['tipomedicamento'])?$laDatosMedicamentos['tipomedicamento']:'';
				$lcConcentracion=isset($laDatosMedicamentos['concentracionmedicamento'])?$laDatosMedicamentos['concentracionmedicamento']:'';
				$lcUnidadMedida=isset($laDatosMedicamentos['unidadmedida'])?$laDatosMedicamentos['unidadmedida']:'';
				$lcFormaFarmaceutica=isset($laDatosMedicamentos['formafarmaceutica'])?$laDatosMedicamentos['formafarmaceutica']:null;
				$lcUnidadDispensacion=isset($laDatosMedicamentos['unidadmindispensa'])?$laDatosMedicamentos['unidadmindispensa']:0;
				$lcDiasTratamiento=isset($laDatosMedicamentos['diastratamiento'])?$laDatosMedicamentos['diastratamiento']:'FALTA';
				$lcTipoIde=isset($laDatosMedicamentos['tipodocumentoldentificacion'])?$laDatosMedicamentos['tipodocumentoldentificacion']:'';
				$lcNroIde=isset($laDatosMedicamentos['numdocumentoldentificacion'])?$laDatosMedicamentos['numdocumentoldentificacion']:'';
				$lcMipres=isset($laDatosMedicamentos['mipres'])?$laDatosMedicamentos['mipres']:'';
				$lcAutorizacionMedicamento=isset($laDatosMedicamentos['autorizacionmed'])?$laDatosMedicamentos['autorizacionmed']:'null';
				$lcEsMedicamentoOtros=$this->consultaMedicamentoInsumo($lcCodigoShaio);
				
				if (empty($lcEsMedicamentoOtros)){
					$lnIncrementar++;
					
					if ($this->nValorCopagoCuota>0 && $this->nAplicaCopagoCuota==0 && (in_array($this->cTipoPagoModerador, $this->aReacaudoMedicamento)))
					{	
						$lnValorPagoModerador=$this->nValorCopagoCuota;
						$lcTipoPagoModerador=$this->cTipoPagoModerador;
						$lcFacturaModeradora=$this->cFacturaPagoModerador;
						$this->nAplicaCopagoCuota=1;
					}else{
						$lnValorPagoModerador=0;
						$lcTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
						$lcFacturaModeradora='';
					}
					$lcTipoIde=empty($lcTipoIde)?$this->cTipoIdeMedicoCups:$lcTipoIde;
					$lcNroIde=empty($lcNroIde)?$this->cNumeroIdeMedicoCups:$lcNroIde;
					$lcCiePrincipal=empty($lcCiePrincipal)?$this->cDiagnosticoCups:$lcCiePrincipal;

					$laDatos =[
						'codPrestador'=>$this->cCodigoHabilitacion,
						'numAutorizacion'=>$lcAutorizacionMedicamento,
						'idMIPRES'=>$lcMipres,
						'fechaDispensAdmon'=>$lcFechaHoraDispensacion,
						'codDiagnosticoPrincipal'=>$lcCiePrincipal,
						'codDiagnosticoRelacionado'=>null,
						'tipoMedicamento'=>$lcTipoMedicamento,
						'codTecnologiaSalud'=>$lcCodigoMedicamento,
						'nomTecnologiaSalud'=>$lcNombreMedicamento,
						'concentracionMedicamento'=>$lcConcentracion,
						'unidadMedida'=>$lcUnidadMedida,
						'formaFarmaceutica'=>$lcFormaFarmaceutica,
						'unidadMinDispensa'=>$lcUnidadDispensacion,
						'cantidadMedicamento'=>$lnCantidadMedicamento,
						'diasTratamiento'=>$lcDiasTratamiento,
						'tipoDocumentoIdentificacion'=>$lcTipoIde,
						'numDocumentoIdentificacion'=>$lcNroIde,
						'vrUnitMedicamento'=>$lnValorUnitario,
						'vrServicio'=>$lnValorTotal,
						'conceptoRecaudo'=>$lcTipoPagoModerador,
						'valorPagoModerador'=>$lnValorPagoModerador,
						'numFEVPagoModerador'=>$lcFacturaModeradora,
						'consecutivo'=>$lnIncrementar,
					];
					$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
					$lcTextoError=!empty($lcTextoError)?$lcCodigoShaio.'~'.$lcTextoError:'';

					$laDatosTabla=[
						'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
						'tipodato'=>$lcTipoDatoRips,
						'tipoconsumo'=>$lcTipoConsumo,
						'consecutivoconsumo'=>$lnConsConsumoInicial,
						'error'=>$lcTextoError,
					];
					$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

					if (!empty($lcTextoError)){
						$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
					}
				}
			}
		}		
		return $laDatos;
	}

	function obtenerDatosOtrosServicios($taParametrosRecibe)
	{
		$laDatosOtrosServicios=[];
		$lcTipoFacturaNota=$taParametrosRecibe['tipofacturanota'] ?? '';
		$lnFactura=$taParametrosRecibe['factura'] ?? 0;
		$lnIngresoOrigen=$taParametrosRecibe['ingreso'] ?? 0;
		$lnNumeroNota=$taParametrosRecibe['numeronota'] ?? 0;
		
		if ($lcTipoFacturaNota=='FAC'){
			$laDatosOtrosServicios = $this->oDb
				->select('trim(A.CUPDFA) CUPS, trim(A.ELEDFA) AUXILIAR, INT(A.RMEDFA) REGISTRO_MEDICO')
				->select('A.TINDFA TIPOCONSUMO, A.VNGDFA VIAINGRESO, A.FINDFA FECHA_COBRO, A.HINDFA HORA_COBRO')
				->select('A.QCODFA CANTIDAD, A.VUNDFA VALOR_UNITARIO, A.VPRDFA VALOR_TOTAL')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO, A.CNSDFA CONSECUTIVOCONSUMO, C.CNOEST CONSECCITA, C.CNCEST CONSECCIRUGIA')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('FACDETF A')
				->leftJoin("RIAESTM C", "A.INGDFA=C.INGEST AND A.CNSDFA=C.CNSEST AND A.TINDFA=C.TINEST", null)
				->leftJoin("RIARGMN D", "A.RMEDFA=D.REGMED AND D.REGMED<>'0000000000000'", null)
				->where('A.INGDFA', '=', $lnIngresoOrigen)
				->where('A.NFADFA', '=', $lnFactura)
				->in('A.TINDFA', ['400', '500', '600'])
				->notLike('A.DOCDFA', 'A%')
				->getAll('array');
		}else{
			$laDatosOtrosServicios = $this->oDb
				->select('trim(A.CUPDET) CUPS, trim(A.ELEDET) AUXILIAR, A.RMEDET REGISTRO_MEDICO')
				->select('A.TIPDET TIPOCONSUMO, C.VNGEST VIAINGRESO, A.FREDET FECHA_COBRO, A.HREDET HORA_COBRO')
				->select('A.QGLDET CANTIDAD, A.VUNDET VALOR_UNITARIO, A.VLRADET VALOR_TOTAL')
				->select('trim(D.TIDRGM) TIPOIDE_MEDICO, A.CNSDET CONSECUTIVOCONSUMO, C.CNOEST CONSECCITA, C.CNCEST CONSECCIRUGIA')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=D.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('AMGLDET A')
				->leftJoin("RIACUP B", "A.CUPDET=B.CODCUP", null)
				->leftJoin("RIAESTM C", "A.INGDET=C.INGEST AND A.CNSDET=C.CNSEST AND A.TIPDET=C.TINEST", null)
				->leftJoin("RIARGMN D", "A.RMEDET=D.NIDRGM", null)
				->where('A.FRADET', '=', $lnFactura)
				->where('A.INGDET', '=', $lnIngresoOrigen)
				->in('A.TIPDET', ['400', '500', '600'])
				->where('A.NOTDET', '=', $lnNumeroNota)
				->getAll('array');
		}
		return $laDatosOtrosServicios;
	}
		
	function crearRipsOtrosServicios($taDatosRecibe)
	{
		$laDatos=$laDatosRipsOtrosServicios=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tcTipoFacturaNota=$taDatosRecibe['tipofacturanota'] ?? '';
		$tnNumeroNota=$taDatosRecibe['numeronota'] ?? 0;
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lnIncrementarCups=0;
		$lnIngresoOrigen=$tnIngreso;
		$lcTipoDatoRips='AT';
		
		$laDatosEnviar=[
			'tipofacturanota'=>$tcTipoFacturaNota,
			'factura'=>$tnFactura,
			'ingreso'=>$lnIngresoOrigen,
			'numeronota'=>$tnNumeroNota,
			
		];
		$laDatosRipsOtrosServicios=$this->obtenerDatosOtrosServicios($laDatosEnviar);
		if (is_array($laDatosRipsOtrosServicios) && count($laDatosRipsOtrosServicios)>0) {	
			foreach($laDatosRipsOtrosServicios as $lalistaCups){
				$tnIngreso=$lnIngresoOrigen;
				$lcMipresCups=$lcCupsNoAplica=$lcCodigoInsumo=$lcEspecialidadProcedimiento=$lcTextoError='';
				$lnConsConsumo=isset($lalistaCups['CONSECUTIVOCONSUMO'])?intval($lalistaCups['CONSECUTIVOCONSUMO']):0;
				$lnConsConsumoInicial=$lnConsConsumo;
				$lcNumeroAutorizacion=$lcFechaHoraCups=$lcTipoConsumo=$lcCodigoConsumo=$lcNombreConsumo='';
				$lcTipoOtrosServicios=$lcCantidadConsumo=$lcTipoRegistroMedico=$lcRegistroMedico=$lcEsMedicamentoOtros='';
				$lnConsecCita=$lnAplicaConsumo=$lnConsecEvolucion=0;
				$lcTipoConsumo=isset($lalistaCups['TIPOCONSUMO'])?trim($lalistaCups['TIPOCONSUMO']):'';
				$lcCantidadConsumo=isset($lalistaCups['CANTIDAD'])?intval(trim($lalistaCups['CANTIDAD'])):'';
				$lcValorUnitario=mb_substr(trim($lalistaCups['CUPS']),0,1)=='C'?'':(isset($lalistaCups['VALOR_UNITARIO'])?intval(trim($lalistaCups['VALOR_UNITARIO'])):'');
				$lcValorTotal=mb_substr(trim($lalistaCups['CUPS']),0,1)=='C'?'':(isset($lalistaCups['VALOR_TOTAL'])?intval(trim($lalistaCups['VALOR_TOTAL'])):'');
				$lcValorUnitario=$tcTipoFacturaNota=='FAC'?$lcValorUnitario:$lcValorTotal;
				$lnConsecutivoCirugia=$lalistaCups['CONSECCIRUGIA'] ?? 0;

				if (!empty($lcTipoConsumo)){
					$lnConsecCita=isset($lalistaCups['CONSECCITA'])?intval($lalistaCups['CONSECCITA']):0;
					$laDatosTraslados=$this->consultarTrasladoConsumos($tnIngreso,$lnConsConsumo);
					$tnIngreso=isset($laDatosTraslados['ingresotraslado'])?$laDatosTraslados['ingresotraslado']:$tnIngreso;
					$lnConsConsumo=isset($laDatosTraslados['consumotraslado'])?$laDatosTraslados['consumotraslado']:$lnConsConsumo;
					$lnConsecCita=isset($laDatosTraslados['citatraslado'])?$laDatosTraslados['citatraslado']:$lnConsecCita;

					if ($lcTipoConsumo=='400'){
						if (isset($lalistaCups['CUPS'])){
							$lcCodigoConsumo=mb_substr(trim($lalistaCups['CUPS']),0,1)=='C'?trim($lalistaCups['AUXILIAR']):trim($lalistaCups['CUPS']);
							$lcCodigoConsumo=mb_substr(trim($lcCodigoConsumo),0,6);
							$lcCupsNoAplica=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='CUPNAPL' AND CL2TMA='$lcCodigoConsumo' AND ESTTMA=''", null, ''));
							$lcCodigoInsumo=$lcCodigoConsumo;
							if ($lcCupsNoAplica==''){

								$laAplicaCups = $this->oDb
									->select('CATCUP TIPO_SERVICIO, CAPCUP FINALIDAD_CUP, TRIM(RF1CUP) CLASIFICACION1, TRIM(ESPCUP) ESPECIALIDADCUPS')
									->from('RIACUP')
									->where('CODCUP', '=', $lcCodigoConsumo)
									->in('CADCUP', $this->aProcedimentosOtrosServicios)
									->get('array');
								if ($this->oDb->numRows()>0){
									$lnAplicaConsumo=1;
									$lnIncrementarCups++;
									$lcTipoOtrosServicios=str_pad($laAplicaCups['TIPO_SERVICIO'], 2, '0', STR_PAD_LEFT);
									$lcEspecialidadProcedimiento=$laAplicaCups['ESPECIALIDADCUPS'];
								}
								$laMipres=$this->obtenerMipres($tnIngreso,$lcCodigoConsumo,$lcTipoConsumo,$lnConsecCita);
								$lcMipresCups=isset($laMipres['idmipres'])?$laMipres['idmipres']:'';
								$lcNumeroAutorizacion=isset($laMipres['numeromipres'])?$laMipres['numeromipres']:'';
							}	
						}
					}else{
						$lcCodigoConsumo=isset($lalistaCups['AUXILIAR'])?trim($lalistaCups['AUXILIAR']):'';
						if ($lcTipoConsumo=='600'){
							$lcCodigoInsumo=isset($lalistaCups['AUXILIAR'])?trim($lalistaCups['AUXILIAR']):'';
							$lnAplicaConsumo=1;
							$lnIncrementarCups++;
						}else{
							$lcEsMedicamentoOtros=$this->consultaMedicamentoInsumo($lcCodigoConsumo);
							
							if (!empty($lcEsMedicamentoOtros)){
								$lnAplicaConsumo=1;
								$lnIncrementarCups++;
							}
						}
						if ($lnAplicaConsumo==1){
							$lcTipoOtrosServicios='01';
						}	
					}

					if ($lnAplicaConsumo==1){
						$lnFechaOrdenado=isset($lalistaCups['FECHA_COBRO'])?$lalistaCups['FECHA_COBRO']:0;
						$lnHoraOrdenado=isset($lalistaCups['HORA_COBRO'])?str_pad($lalistaCups['HORA_COBRO'], 6, '0', STR_PAD_LEFT):0;
						$lcFHRealizaCompara=date('Y-m-d H:i:s', strtotime($lnFechaOrdenado.$lnHoraOrdenado));
								
						if($lcFHRealizaCompara>$this->cFechaHoraEgresoCompara) {
							$lcFechaHoraCups=$this->cFechaHoraEgreso;
						}else{
							$llFechaFormulaOrdenado=$lnFechaOrdenado.$lnHoraOrdenado;
							$lcFechaHoraCups=date_format(date_create_from_format('YmdHis', $llFechaFormulaOrdenado), 'Y-m-d H:i');
						}
						
						if ($this->nValorCopagoCuota>0 && $this->nAplicaCopagoCuota==0 && (in_array($this->cTipoPagoModerador, $this->aReacaudoOtrosServicios)))
						{	
							$lnValorPagoModerador=$this->nValorCopagoCuota;
							$lcTipoPagoModerador=$this->cTipoPagoModerador;
							$lcFacturaModeradora=$this->cFacturaPagoModerador;
							$this->nAplicaCopagoCuota=1;
						}else{
							$lnValorPagoModerador=0;
							$lcTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
							$lcFacturaModeradora='';
						}

						$lcNombreConsumo=$lcTipoOtrosServicios==='01'?$this->consultaDatosConsumo($lcCodigoConsumo,$lcTipoConsumo):'null';
						$lcCodigoConsumo=($lcTipoOtrosServicios==='04' && !empty($lcMipresCups))?$lcMipresCups:$lcCodigoConsumo;
						$lcMipresCups=!empty($lcMipresCups)?$lcMipresCups:null;
						$laDocumentoCt=$this->consultaDocumentoCt($tnIngreso,$lnConsConsumo,$lcCodigoInsumo,$lnFechaOrdenado,$lnHoraOrdenado);
						$lnConsecEvolucion=$laDocumentoCt['nroevolucion'];
						$laMedicoFormulo=$this->consultarMedicoFormula($tnIngreso,$lcCodigoInsumo,$lnConsecEvolucion,$lnFechaOrdenado,$lnConsecutivoCirugia);
						$lcTipoRegistroMedico=$laMedicoFormulo['tidoide'];
						$lcRegistroMedico=$laMedicoFormulo['numeroide'];

						if (empty($lcRegistroMedico)){
							$laDatosProfesional=[
									'nrofactura'=>$tnFactura,
									'nroingreso'=>$tnIngreso,
									'consecutivocita'=>$lnConsecCita,
									'codigoconsumo'=>$lcCodigoInsumo,
									'fechaconsumo'=>$lnFechaOrdenado,
									'tipoconsumo'=>$lcTipoConsumo,
									'consecutivocirugia'=>$lnConsecutivoCirugia,
									'especialidadprocedimiento'=>$lcEspecialidadProcedimiento,
							];
							$laRegistroMedico=$this->consultarProfesionalRealiza($laDatosProfesional);
							$lcTipoRegistroMedico=$laRegistroMedico['tipoiderealiza'];
							$lcRegistroMedico=$laRegistroMedico['nroiderealiza'];
						}
						$lcTipoRegistroMedico=empty($lcTipoRegistroMedico)?$this->cTipoIdeMedicoCups:$lcTipoRegistroMedico;
						$lcRegistroMedico=empty($lcRegistroMedico)?$this->cNumeroIdeMedicoCups:$lcRegistroMedico;

						$laDatos=[
							'codPrestador'=>$this->cCodigoHabilitacion,
							'numAutorizacion'=>$lcNumeroAutorizacion,
							'idMIPRES'=>$lcMipresCups,
							'fechaSuministroTecnologia'=>$lcFechaHoraCups,
							'tipoOS'=>$lcTipoOtrosServicios,
							'codTecnologiaSalud'=>$lcCodigoConsumo,
							'nomTecnologiaSalud'=>$lcNombreConsumo,
							'cantidadOS'=>$lcCantidadConsumo,
							'tipoDocumentoIdentificacion'=>$lcTipoRegistroMedico,
							'numDocumentoIdentificacion'=>$lcRegistroMedico,
							'vrUnitOS'=>$lcValorUnitario,
							'vrServicio'=>$lcValorTotal,
							'conceptoRecaudo'=>$lcTipoPagoModerador,
							'valorPagoModerador'=>$lnValorPagoModerador,
							'numFEVPagoModerador'=>$lcFacturaModeradora,
							'consecutivo'=>$lnIncrementarCups,
						];
						$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
						$lcTextoError=!empty($lcTextoError)?$lcCodigoConsumo.'~'.$lcTextoError:'';
						
						if ($lcTipoConsumo=='400'){
							$this->nCantidadProcedimientosEnRips++;
						}	
					
						$laDatosTabla=[
							'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
							'tipodato'=>$lcTipoDatoRips,
							'tipoconsumo'=>$lcTipoConsumo,
							'consecutivoconsumo'=>$lnConsConsumoInicial,
							'error'=>$lcTextoError,
						];
						$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

						if (!empty($lcTextoError)){
							$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
						}
					}	
				}
			}
		} 
		unset($laProcedimientos,$laFechaHoraCita,$laAplicaCups);
		return $laDatos;
	}

	function crearRipsUrgencias($taDatosRecibe)
	{
		$laDatos=$laDatosTabla=[];
		$lcTextoError='';
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lcCausaExterna=$lcCiePrincipal=$lcCieEgreso=$lcCieEgresoRelac1=$lcCieEgresoRelac2=$lcCieEgresoRelac3='';
		$lcCondicionEgreso=$lcDiagnosticoFallece=$lcFechHoraEgreso=$lcViaIngreso=$lcFechaHoraFallece='';
		$lcTipoDatoRips='AU';

		$laWhere = [
			'A.INGDFA'=>$tnIngreso,
			'A.NFADFA'=>$tnFactura,
			'A.TINDFA'=>'400',
			'B.TIPTMA'=>'FORMEDIC',
			'B.CL1TMA'=>'COBRURG',
		];
		
		$laConsultaUrgencia= $this->oDb
		->select('A.CUPDFA')
		->from('FACDETF AS A')
		->leftJoin('TABMAE AS B', "TRIM(A.CUPDFA)=TRIM(B.DE2TMA)", null)
		->where($laWhere)
		->getAll('array');
		if ($this->oDb->numRows()>0){
			$laUrgencia = $this->oDb
				->select('A.CONCON CONSECCONSULTA, trim(A.CODIGO) CODIGO, A.FECHIS FECHA_ATENCION, A.HORHIS HORA_ATENCION')
				->from('RIAHIS A')
				->where('A.NROING', '=', $tnIngreso)
				->where('A.INDICE', '=', 54)
				->in('A.CODIGO', [2, 3])
				->get('array');
			if ($this->oDb->numRows()>0){
				$llFechaFormula=$laUrgencia['FECHA_ATENCION'].str_pad($laUrgencia['HORA_ATENCION'], 6, '0', STR_PAD_LEFT);
				$lcFechaHoraAtencion=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
				$lcCausaExterna=$this->obtenerCausaExterna($tnIngreso,0);
				$laDiagnosticos=$this->consultarDiagnosticoConsulta($tnIngreso,0,$this->nConsecutivoConsulta,'');
				$lcCiePrincipal=$laDiagnosticos['principal'];
				$laDiagnosticosSalida=$this->consultarDiagnosticoSalida($tnIngreso);
				$lcCieEgreso=$laDiagnosticosSalida['egresoprincipal'];
				$lcCieEgresoRelac1=$laDiagnosticosSalida['egresorelacionado1'];
				$lcCieEgresoRelac2=$laDiagnosticosSalida['egresorelacionado2'];
				$lcCieEgresoRelac3=$laDiagnosticosSalida['egresorelacionado3'];
				$lcDiagnosticoFallece=$this->aDatoFallece['codigodiagnosticofallece'];
				$lcFechaHoraFallece=$this->aDatoFallece['fechahorafallece'];
				$lcCondicionEgreso=$this->consultarCondicionDestinoEgreso($tnIngreso,$lcViaIngreso,$lcDiagnosticoFallece,'U');
				$lcFechHoraEgreso=!empty($lcDiagnosticoFallece)?$lcFechaHoraFallece:$this->consultarFechaEgresoUrgencias($tnIngreso);

				$laDatos=[
					'codPrestador'=>$this->cCodigoHabilitacion,
					'fechaInicioAtencion'=>$lcFechaHoraAtencion,
					'causaMotivoAtencion'=>$lcCausaExterna,
					'codDiagnosticoPrincipal'=>$lcCiePrincipal,
					'codDiagnosticoPrincipalE'=>$lcCieEgreso,
					'codDiagnosticoRelacionadoE1'=>!empty($lcCieEgresoRelac1)?$lcCieEgresoRelac1:null,
					'codDiagnosticoRelacionadoE2'=>!empty($lcCieEgresoRelac2)?$lcCieEgresoRelac2:null,
					'codDiagnosticoRelacionadoE3'=>!empty($lcCieEgresoRelac3)?$lcCieEgresoRelac3:null,
					'condicionDestinoUsuarioEgreso'=>$lcCondicionEgreso,
					'codDiagnosticoCausaMuerte'=>!empty($lcDiagnosticoFallece)?$lcDiagnosticoFallece:null,
					'fechaEgreso'=>$lcFechHoraEgreso,
					'consecutivo'=>1,
				];
				$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
				$lcTextoError=!empty($lcTextoError)?strval($tnIngreso).'~'.$lcTextoError:'';

				$laDatosTabla=[
					'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
					'tipodato'=>$lcTipoDatoRips,
					'tipoconsumo'=>'',
					'consecutivoconsumo'=>$this->nConsecutivoConsulta,
					'error'=>$lcTextoError,
				];
				$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

				if (!empty($lcTextoError)){
					$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
				}
			}
		}	
		unset($laUrgencia, $laConsultaUrgencia);
		return $laDatos;
	}

	function crearRipsHospitalizacion($taDatosRecibe)
	{
		$laDatos=$laDatosTabla=[];
		$tnFactura=$taDatosRecibe['factura'] ?? '';
		$tcTipoRips=$taDatosRecibe['tiporips'] ?? '';
		$tnIngreso=$taDatosRecibe['ingreso'] ?? 0;
		$lcViaingresoHospitalizacion='05';
		$lcViaIngresoSalud=$lcFechaHoraInicioAtencion=$lcCieComplicacion=$lcNumeroAutorizacion=$lcCausaExterna='';
		$lcCiePrincipal=$lcCieEgreso=$lcCieEgresoRelac1=$lcCieEgresoRelac2=$lcCieEgresoRelac3=$lcDiagnosticoFallece='';
		$lcFechaHoraFallece=$lcFechaHoraEgreso='';
		$lnConsecutivoConsulta=0;
		$lcTipoDatoRips='AH';
		
		$laHospitalizacion = $this->oDb
			->select('TIDING TIPOIDE, VIAING VIAINGRESO, FEEING FECHA_EGRESO, HREING HORA_EGRESO')
			->from('RIAING')->where('NIGING', '=', $tnIngreso)->where('VIAING', '=', $lcViaingresoHospitalizacion)->get('array');
		if ($this->oDb->numRows()>0){
			$lcFechaHoraInicioAtencion=$this->consultarFechaIngresoHospitalizacion($tnIngreso);
			if (!empty($lcFechaHoraInicioAtencion)){
				$lcTextoError='';
				$lcViaIngreso=isset($laHospitalizacion['VIAINGRESO'])?$laHospitalizacion['VIAINGRESO']:'';
				$lcTipoidentificacion=isset($laHospitalizacion['TIPOIDE'])?$laHospitalizacion['TIPOIDE']:'';
				$lcViaIngresoSalud=$this->obtieneViaIngresoUsuarioServicio($tnIngreso,$lcViaIngreso,'H',$lcTipoidentificacion);
				$lcNumeroAutorizacion=$this->consultaAutorizacionConsumo($tnIngreso,0,'H','');
				$lcCausaExterna=$this->obtenerCausaExterna($tnIngreso,0);
				$lcDiagnosticoFallece=$this->aDatoFallece['codigodiagnosticofallece'];
				$lcFechaHoraFallece=$this->aDatoFallece['fechahorafallece'];
				$lcCondicionEgreso=$this->consultarCondicionDestinoEgreso($tnIngreso,$lcViaingresoHospitalizacion,$lcDiagnosticoFallece,'H');

				if (!empty($lcDiagnosticoFallece)){
					$lcFechaHoraEgreso=$lcFechaHoraFallece;
				}else{
					if (isset($laHospitalizacion['FECHA_EGRESO']) && isset($laHospitalizacion['HORA_EGRESO'])){
						$llFechaFormula=$laHospitalizacion['FECHA_EGRESO'].str_pad($laHospitalizacion['HORA_EGRESO'], 6, '0', STR_PAD_LEFT);
						$lcFechaHoraEgreso=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
					}	
				}	
				$laDiagnosticos=$this->consultarDiagnosticoConsulta($tnIngreso,0,$this->nConsecutivoConsulta,'');
				$lcCiePrincipal=$laDiagnosticos['principal'];
				$laDiagnosticosSalida=$this->consultarDiagnosticoSalida($tnIngreso);
				$lcCieEgreso=$laDiagnosticosSalida['egresoprincipal'];
				$lcCieEgresoRelac1=isset($laDiagnosticosSalida['egresorelacionado1'])?$laDiagnosticosSalida['egresorelacionado1']:'';
				$lcCieEgresoRelac2=isset($laDiagnosticosSalida['egresorelacionado2'])?$laDiagnosticosSalida['egresorelacionado2']:'';
				$lcCieEgresoRelac3=$laDiagnosticosSalida['egresorelacionado3'];

				$laDatos=[
					'codPrestador'=>$this->cCodigoHabilitacion,
					'viaIngresoServicioSalud'=>$lcViaIngresoSalud,
					'fechaInicioAtencion'=>$lcFechaHoraInicioAtencion,
					'numAutorizacion'=>$lcNumeroAutorizacion,
					'causaMotivoAtencion'=>$lcCausaExterna,
					'codDiagnosticoPrincipal'=>$lcCiePrincipal,
					'codDiagnosticoPrincipalE'=>$lcCieEgreso,
					'codDiagnosticoRelacionadoE1'=>!empty($lcCieEgresoRelac1)?$lcCieEgresoRelac1:null,
					'codDiagnosticoRelacionadoE2'=>!empty($lcCieEgresoRelac2)?$lcCieEgresoRelac2:null,
					'codDiagnosticoRelacionadoE3'=>!empty($lcCieEgresoRelac3)?$lcCieEgresoRelac3:null,
					'codComplicacion'=>!empty($lcCieComplicacion)?$lcCieComplicacion:null,
					'condicionDestinoUsuarioEgreso'=>!empty($lcCondicionEgreso)?$lcCondicionEgreso:null,
					'codDiagnosticoCausaMuerte'=>$lcDiagnosticoFallece,
					'fechaEgreso'=>$lcFechaHoraEgreso,
					'consecutivo'=>1,
				];
				$lcTextoError=$this->verificarDatosObligatorios($laDatos,$lcTipoDatoRips);
				$lcTextoError=!empty($lcTextoError)?intval($tnIngreso).'~'.$lcTextoError:'';
		
				$laDatosTabla=[
					'llaveunica'=>$this->oDb->obtenerLlaveUnicaTabla(),
					'tipodato'=>$lcTipoDatoRips,
					'tipoconsumo'=>'',
					'consecutivoconsumo'=>$this->nConsecutivoConsulta,
					'error'=>$lcTextoError,
				];
				$this->insertarDatos('RIPRES',$laDatosTabla,$laDatos);

				if (!empty($lcTextoError)){
					$this->insertarDatos('RIPREA',$laDatosTabla,$laDatos);
				}
			}	
		}	
		unset($laHospitalizacion);
		return $laDatos;
	}

	function obtenerDatosFacturaPaciente($tnFactura=0)
	{
		$laFacturaPaciente = [];
		$lcNumeroDocumentoFactura = substr(str_pad($tnFactura, 8, '0', STR_PAD_LEFT), 2, 6);

		$laFacturaPaciente = $this->oDb
			->select('P.FNAPAC FECHANACIMIENTO, P.PARPAC PAISRESIDENCIA, P.DERPAC DEPTRESIDENCIA, P.MURPAC CIUDADRESIDENCIA')
			->select('S.CN1PAI HOMPAISRESIDENCIA, P.ZORPAC ZONAPACIENTE, I.NIDING IDEPACIENTE, P.SEXPAC SEXOPACIENTE')
			->select('P.PAIPAC PAISNACIMIENTO, T.CN1PAI HOMPAISNACIMIENTO, I.VIAING VIA_INGRESO')
			->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=I.TIDING) AS TIPOIDE_HOMOLOGO')
			->from('FACCABF AS F')
			->innerJoin('RIAING  AS I', 'F.INGCAB=I.NIGING', null)
			->innerJoin('RIAPAC  AS P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->leftJoin( 'COMPAI  AS S', 'P.PARPAC=S.CODPAI', null)
			->leftJoin( 'COMPAI  AS T', 'P.PAIPAC=T.CODPAI', null)
			->where([ 'F.FRACAB' => $tnFactura, 'F.DOCCAB' => $lcNumeroDocumentoFactura ])
			->get('array');
		return $laFacturaPaciente;
	}	
	
	function consultaAutorizacionConsumo($tnIngreso=0,$tnConsecutivoCita=0,$tcTipo='',$tcProcedimiento='')
	{
		$lcAutorizacion='';
		if ($tnIngreso>0){
			if ($tcTipo=='H'){
				$laAutorizacion = $this->oDb
				->select('SUBSTR(TRIM(A.DESAUS), 19, 30) AUTORIZACION')
				->from('AUTASE A')
				->leftJoin("RIACUP B", "A.CUPAUS=B.CODCUP", null)
				->where('A.INGAUS', '=', $tnIngreso)
				->where('A.INDAUS', '=', 2)
				->where('A.CNLAUS', '=', 2)
				->where('B.RF2CUP', '=', 'ESTANC')
				->where('B.RF3CUP', '=', 'PISO')
				->where('B.RF6CUP', '<>', '******')
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcAutorizacion=trim($laAutorizacion['AUTORIZACION']);
				}
			}
			if ($tcTipo==''){
				$laAutorizacion = $this->oDb
				->select('SUBSTR(TRIM(DESAUS), 19, 30) AUTORIZACION')
				->from('AUTASE')
				->where('INGAUS', '=', $tnIngreso)
				->where('CCIAUS', '=', $tnConsecutivoCita)
				->where('INDAUS', '=', 2)
				->where('CNLAUS', '=', 2)
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcAutorizacion=trim($laAutorizacion['AUTORIZACION']);
				}else{
					$laAutorizacion = $this->oDb
					->select('SUBSTR(TRIM(DESAUS), 19, 30) AUTORIZACION')
					->from('AUTASE')
					->where('INGAUS', '=', $tnIngreso)
					->where('CUPAUS', '=', $tcProcedimiento)
					->where('INDAUS', '=', 2)
					->where('CNLAUS', '=', 2)
					->get('array');
					if ($this->oDb->numRows()>0){
						$lcAutorizacion=trim($laAutorizacion['AUTORIZACION']);
					}
				}
			}	
		}	
		unset($laAutorizacion);
		return $lcAutorizacion;
	}
	
	function consultarTrasladoConsumos($tnIngreso=0, $tnConsecConsumo=0)
	{
		$laDatosTraslados=[];
		if ($tnIngreso>0){
			$laTraslado = $this->oDb
			->select('INGTRA, CNSTRA, CNOTRA')
			->from('INGTRASL')
			->where('INTTRA', '=', $tnIngreso)
			->where('CNTTRA', '=', $tnConsecConsumo)
			->get('array');
			if ($this->oDb->numRows()>0){
				$laDatosTraslados=[			
					'ingresotraslado'=>$laTraslado['INGTRA'],
					'consumotraslado'=>$laTraslado['CNSTRA'],
					'citatraslado'=>$laTraslado['CNOTRA'],
				];
			}
		}	
		unset($laTraslado);
		return $laDatosTraslados;
	}
	
	function consultaMedicamentoInsumo($tcConsumo='')
	{
		$lcEsConsumo='';
		
		if (!empty($tcConsumo)){
			$laConsumo = $this->oDb
			->select('TRIM(CL11DES) ES_INSUMO')
			->from('INVATTR')
			->where('REFDES', '=', $tcConsumo)
			->where('CL11DES', '=', 'INSUMO')
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcEsConsumo=trim($laConsumo['ES_INSUMO']);
			}
		}	
		unset($laConsumo);
		return $lcEsConsumo;
	}
	
	function consultaDatosConsumo($tcConsumo='',$tcTipoConsumo='')
	{
		$lcDescripcion='';
		if (!empty($tcConsumo)){
			if ($tcTipoConsumo=='400'){
				$laConsumo = $this->oDb
				->select('TRIM(DESCUP) DESCRIPCION')
				->from('RIACUP')
				->where('CODCUP', '=', $tcConsumo)
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcDescripcion=trim($laConsumo['DESCRIPCION']);
				}
			}
			
			if ($tcTipoConsumo=='600' || $tcTipoConsumo=='500'){
				$laConsumo = $this->oDb
				->select('TRIM(DESDES) DESCRIPCION')
				->from('INVDES')
				->where('REFDES', '=', $tcConsumo)
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcDescripcion=$tcTipoConsumo=='500'?trim(substr(trim($laConsumo['DESCRIPCION']), 0, 30)):trim(substr(trim($laConsumo['DESCRIPCION']), 0, 60));
				}
			}	
		}
		$lcDescripcion=str_replace('\ ', "", str_replace('/', "", str_replace('"', "'", str_replace('\\', " ", $lcDescripcion))));
		unset($laConsumo);
		return $lcDescripcion;
	}

	function consultarGrupoServicioCups($tnIngreso=0,$tcViaIngreso='',$tcTipoRips='')
	{
		$lcGrupoServicioCups='';
		$lcViaCupsImagenes=trim($this->oDb->obtenerTabmae1('CL3TMA', 'GENRIPS', "CL1TMA='GRUSERV' AND CL2TMA='02' AND ESTTMA=''", null, ''));
		
		if (empty($tcViaIngreso)){
			$laViaIngreso = $this->oDb
			->select('VIAING')
			->from('RIAING')
			->where('NIGING', '=', $tnIngreso)
			->get('array');
			if ($this->oDb->numRows()>0){
				$tcViaIngreso=trim($laViaIngreso['VIAING']);
			}
		}	
		
		if (!empty($tcViaIngreso)){
			$tcViaIngreso=$tcViaIngreso=='02'?($tcTipoRips=='C'?$tcViaIngreso:$lcViaCupsImagenes):$tcViaIngreso;
			$lcGrupoServicioCups=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='GRUSERV' AND CL3TMA='$tcViaIngreso' AND ESTTMA=''", null, ''));
		}
		unset($laViaIngreso);
		return $lcGrupoServicioCups;
	}
	
	function consultarModalidadGrupoServicio($tnIngreso=0,$tnConsecConsulta=0,$tcTipoRips='',$tcProcedimiento='')
	{
		$lcModalidadGrupoServicio='';
		if ($tcTipoRips=='C'){
			$laModalidad = $this->oDb
				->select('SUBSTR(DESCRI, 1, 2) MODALIDAD')
				->from('RIAHIS')
				->where('NROING', '=', $tnIngreso)
				->where('CONCON', '=', $tnConsecConsulta)
				->where('INDICE', '=', 90)
				->get('array');
			if ($this->oDb->numRows()>0){
				$lcModalidadGrupoServicio=trim($laModalidad['MODALIDAD']);
			}
		}
		
		if (empty($lcModalidadGrupoServicio) && !empty($tcProcedimiento)){
			$lcModalidadGrupoServicio=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='MODATEC' AND CL2TMA='$tcProcedimiento' AND ESTTMA=''", null, ''));
		}
		
		if (empty($lcModalidadGrupoServicio)){
			$lcModalidadGrupoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='MODATEN' AND OP1TMA='P' AND ESTTMA=''", null, ''));
		}
		unset($laModalidad); 
		return $lcModalidadGrupoServicio;
	}
	
	function consultarProcedimientoFinalidad($tnIngreso=0,$tcProcedimiento='',$tnConsecConsulta=0)
	{
		$lcFinalidad='';
		if ($tnIngreso>0 && $tnConsecConsulta>0){
			if (empty($tcProcedimiento)){
				$laFinalidad = $this->oDb
					->select('SUBIND FINALIDAD')->from('RIAHIS')->where('NROING', '=', $tnIngreso)->where('CONCON', '=', $tnConsecConsulta)->where('INDICE', '=', 4)->get('array');
				if ($this->oDb->numRows()>0){
					$lcFinalidad=trim($laFinalidad['FINALIDAD']);
				}
			}	

			if (empty($lcFinalidad)){
				$laFinalidad = $this->oDb
					->select('SUBSTR(DESCRI, 31, 2) FINALIDAD')->from('RIAHIS')->where('NROING', '=', $tnIngreso)->where('CONCON', '=', $tnConsecConsulta)->where('INDICE', '=', 70)->where('SUBORG', '=', $tcProcedimiento)
					->where('CONSEC', '=', 101)->get('array');
				if ($this->oDb->numRows()>0){
					$lcFinalidad=trim($laFinalidad['FINALIDAD']);
				}
			}	
			
			if (empty($lcFinalidad)){
				$laFinalidad = $this->oDb
				->select('FINCGR FINALIDAD')->from('CUPGRA')->where('INGCGR', '=', $tnIngreso)->where('CCICGR', '=', $tnConsecConsulta)->orderBy('CONCGR DESC')->get('array');
				if ($this->oDb->numRows()>0){
					$lcFinalidad=trim($laFinalidad['FINALIDAD']);
				}
			}

			if (empty($lcFinalidad)){
				$laFinalidad = $this->oDb
				->select('SUBSTR(DESCRI, 13, 2) FINALIDAD')->from('RIAHIS')
				->where('NROING', '=', $tnIngreso)->where('CONCON', '=', $tnConsecConsulta)->where('INDICE', '=', 70)->where('SUBORG', '=', '22')->where('CONSEC', '=', 20)->get('array');
				if ($this->oDb->numRows()>0){
					$lcHomologoFinalidad=trim($laFinalidad['FINALIDAD']);
					if (!empty($lcHomologoFinalidad)){
						$lcFinalidad=trim($this->oDb->obtenerTabmae1('CL2TMA', 'CODFIN', "OP5TMA='$lcHomologoFinalidad' AND ESTTMA=''", null, ''));
					}	
				}
			}	
			
			if (empty($lcFinalidad)){
				$laFinalidad = $this->oDb
				->select('FPRAPS FINALIDAD')->from('RIPAPS')->where('INGAPS', '=', $tnIngreso)->where('CSCAPS', '=', $tnConsecConsulta)->get('array');
				if ($this->oDb->numRows()>0){
					$lcHomologoFinalidad=trim($laFinalidad['FINALIDAD']);
					if (!empty($lcHomologoFinalidad)){
						$lcFinalidad=trim($this->oDb->obtenerTabmae1('CL2TMA', 'CODFIN', "OP5TMA='$lcHomologoFinalidad' AND ESTTMA=''", null, ''));
					}	
				}
			}
		}	

		if (!empty($lcFinalidad)){
			$lcFinalidad=intval($this->oDb->obtenerTabmae1('OP4TMA', 'CODFIN', "CL1TMA='$lcFinalidad'", null, ''));
			$lcFinalidad=trim(strval($lcFinalidad)); 
		}
		
		if (empty($lcFinalidad)){
			$lcFinalidad=intval($this->oDb->obtenerTabmae1('CL2TMA', 'CODFIN', "OP1TMA='D' AND ESTTMA=''", null, ''));
			$lcFinalidad=trim(strval($lcFinalidad)); 
		}
		unset($laFinalidad); 
		return $lcFinalidad;
	}
	
	function consultarProfesionalRealiza($taDatos)
	{
		$laDatosProfesional=[];
		$lcRegistroMedico='0';
		$lcTipoIde='';
		$lnFechaIngreso=$this->nFechaIngreso;
		$tnFactura=$taDatos['nrofactura'] ?? 0;
		$tnIngreso=$taDatos['nroingreso'] ?? 0;
		$tnConsecCita=$taDatos['consecutivocita'] ?? 0;
		$tcCups=$taDatos['codigoconsumo'] ?? '';
		$tcEspecialidadCups=$taDatos['especialidadprocedimiento'] ?? '';
		$tnFechaConsumo=$taDatos['fechaconsumo'] ?? 0;
		$tcTipoConsumo=$taDatos['tipoconsumo'] ?? '';
		$tnConsecCirugia=$taDatos['consecutivocirugia'] ?? 0;

		if (($tcTipoConsumo=='400')){
			$laMedicoRealiza = $this->oDb
				->select('trim(B.TIDRGM) TIPOIDE, B.NIDRGM NROIDE')->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('FACDETF A')->leftJoin("RIARGMN B", "A.RMEDFA=B.REGMED", null)
				->where('A.INGDFA', '=', $tnIngreso)->where('A.NFADFA', '=', $tnFactura)
				->where('A.TINDFA', '=', $tcTipoConsumo)->where('A.RMEDFA', '<>', '0000000000000')->where('A.ELEDFA', '=', $tcCups)->get('array');
				if ($this->oDb->numRows()>0){
					$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
					$lcRegistroMedico=trim($laMedicoRealiza['NROIDE']);
				}
				
			if (intval($lcRegistroMedico)==0){
				$laMedicoRealiza = $this->oDb
				->select('A.MEDCGR REGISTROMEDICO')->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('CUPGRA A')->leftJoin("RIARGMN B", "A.MEDCGR=B.REGMED", null)
				->where('A.INGCGR', '=', $tnIngreso)->where('A.CCICGR', '=', $tnConsecCita)->orderBy('A.CONCGR DESC')->get('array');
				if ($this->oDb->numRows()>0){
					$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
					$lcRegistroMedico=trim($laMedicoRealiza['REGISTROMEDICO']);
				}
			}	
		}

		if (($tcTipoConsumo=='600')){
			$lcIngresoDoc='0'.$tnIngreso;

			$laMedicoRealiza = $this->oDb
				->select('C.NIDRGM REGISTROMEDICO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=C.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('INVCTRL A')
				->leftJoin("INVDET B", "A.TDOCTR=B.TIDDET AND A.NDOCTR=B.NDODET", null)
				->leftJoin("RIARGMN C", "A.USRCTR=C.USUARI", null)
				->where("A.ND1CTR LIKE '%{$tnIngreso}%'")
				->where('A.TDOCTR', '=', 'SI')
				->where('B.PR1DET', '=', $tcCups)
				->between('A.FE1CTR', $lnFechaIngreso, $tnFechaConsumo)
				->orderBy('A.FE1CTR DESC')
				->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
				$lcRegistroMedico=$laMedicoRealiza['REGISTROMEDICO'];
			}
		}	
		
		if (intval($lcRegistroMedico)==0){
			$laMedicoCirugia=$this->consultarProfesionalCirugia($tnIngreso,$tnConsecCirugia);
			$lcTipoIde=$laMedicoCirugia['tipoiderealiza'];
			$lcRegistroMedico=$laMedicoCirugia['nroiderealiza'];
		}		
		
		if (intval($lcRegistroMedico)==0){
			$laTratante=$this->consultarTratanteRealiza($tnIngreso,$tnFechaConsumo);
			$lcTipoIde=$laTratante['tipoiderealiza'];
			$lcRegistroMedico=$laTratante['nroiderealiza'];
		}	
		if (intval($lcRegistroMedico)==0){
			if (!empty($tcEspecialidadCups)){
				$laTratante=$this->consultarMedicoRealizaEspecialidad($tcEspecialidadCups);
				$lcTipoIde=$laTratante['tipoiderealiza'];
				$lcRegistroMedico=$laTratante['nroiderealiza'];
			}	
		}	
		$laDatosProfesional=[			
			'tipoiderealiza'=>$lcTipoIde,
			'nroiderealiza'=>$lcRegistroMedico,
		];
		unset($laMedicoRealiza); 
		return $laDatosProfesional;
	}

	function consultarMedicoRealizaEspecialidad($tcEspecialidadCups)
	{
		$laDatosTratante=[];
		$lcTipoIde=$lcRegistroMedico='';
		$laMedicoRealiza=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='RIPSESP' AND CL2TMA='$tcEspecialidadCups' AND ESTTMA=''", null, ''));

		if ($laMedicoRealiza!=''){
			$lcTipoIde=explode('~', $laMedicoRealiza)[0];
			$lcRegistroMedico=explode('~', $laMedicoRealiza)[1];
		}

		$laDatosTratante=[			
			'tipoiderealiza'=>$lcTipoIde,
			'nroiderealiza'=>$lcRegistroMedico,
		];
		unset($laMedicoRealiza); 
		return $laDatosTratante;
	}
	
	function consultarProfesionalCirugia($tnIngreso=0,$tnConsecutivoCirugia=0)
	{
		$laDatosMedico=[];
		$lcRegistroMedico='';
		$lcTipoIde='';
		
		$laMedicoRealiza = $this->oDb
		->select('A.ESPCRQ REGISTROMEDICO')
		->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
		->from('FACCIRQ A')
		->leftJoin("RIARGMN B", "A.ESPCRQ=B.NIDRGM", null)
		->where('A.INGCRQ', '=', $tnIngreso)->where('A.CNSCRQ', '=', $tnConsecutivoCirugia)->orderBy('A.EQPCRQ')->get('array');
		if ($this->oDb->numRows()>0){
			$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
			$lcRegistroMedico=trim($laMedicoRealiza['REGISTROMEDICO']);
		}
		
		$laDatosMedico=[			
			'tipoiderealiza'=>$lcTipoIde,
			'nroiderealiza'=>$lcRegistroMedico,
		];
		unset($laMedicoRealiza); 
		return $laDatosMedico;
	}
	
	function consultarTratanteRealiza($tnIngreso=0,$tnFechaConsumo=0)
	{
		$laDatosTratante=[];
		$lcRegistroMedico='';
		$lcTipoIde='';
		$lnFechaIngreso=$this->nFechaIngreso;
		
		$laMedicoRealiza = $this->oDb
			->select('A.MEDDME REGISTROMEDICO, A.FTRDME FECHA')
			->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
			->from('RIAINGTD A')
			->leftJoin("RIARGMN B", "A.MEDDME=B.NIDRGM", null)
			->where('A.INGDME', '=', $tnIngreso)
			->between('A.FTRDME', $lnFechaIngreso, $tnFechaConsumo)
			->orderBy('A.FTRDME DESC')
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
			$lcRegistroMedico=trim($laMedicoRealiza['REGISTROMEDICO']);
		}	
		
		if (intval($lcRegistroMedico)==0){
			$laMedicoRealiza = $this->oDb
				->select('A.MEDINT REGISTROMEDICO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('RIAINGT A')
				->leftJoin("RIARGMN B", "A.MEDINT=B.NIDRGM", null)
				->where('A.NIGINT', '=', $tnIngreso)
				->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
				$lcRegistroMedico=$laMedicoRealiza['REGISTROMEDICO'];
			}
		}		
		$laDatosTratante=[			
			'tipoiderealiza'=>$lcTipoIde,
			'nroiderealiza'=>$lcRegistroMedico,
		];
		unset($laMedicoRealiza); 
		return $laDatosTratante;
	}
	
	function consultarMedicoEvoluciono($tnIngreso=0,$tnFechaConsumo=0)
	{
		$laDatosEvoluciono=[];
		$lcRegistroMedico='';
		$lcTipoIde='';
		$lnFechaIngreso=$this->nFechaIngreso;
		
		$laMedicoRealiza = $this->oDb
			->select('B.NIDRGM REGISTROMEDICO, A.FECEVL FECHA')
			->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
			->from('EVOLUC A')
			->leftJoin("RIARGMN B", "A.USREVL=B.USUARI", null)
			->where('A.NINEVL', '=', $tnIngreso)
			->between('A.FECEVL', $lnFechaIngreso, $tnFechaConsumo)
			->orderBy('A.FECEVL DESC')
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
			$lcRegistroMedico=trim($laMedicoRealiza['REGISTROMEDICO']);
		}	
		
		if (intval($lcRegistroMedico)==0){
			$laMedicoRealiza = $this->oDb
				->select('B.NIDRGM REGISTROMEDICO')
				->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')
				->from('RIAHIS A')
				->leftJoin("RIARGMN B", "A.USRHIS=B.USUARI", null)
				->where('A.NROING', '=', $tnIngreso)
				->where("A.PGMHIS like 'HCPPAL%'")
				->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoIde=trim($laMedicoRealiza['TIPOIDE_HOMOLOGO']);
				$lcRegistroMedico=$laMedicoRealiza['REGISTROMEDICO'];
			}
		}		
		$laDatosEvoluciono=[			
			'tipoiderealiza'=>$lcTipoIde,
			'nroiderealiza'=>$lcRegistroMedico,
		];
		unset($laMedicoRealiza); 
		return $laDatosEvoluciono;
	}
	
	function obtieneViaIngresoUsuarioServicio($tnIngreso=0,$tcViaIngreso='',$tcTipoRips='',$tcTipoIde='')
	{
		$lcViaIngresoServicio='';
		if ($tcTipoRips=='P'){
			if (!empty($tcViaIngreso)){
				$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$tcViaIngreso' AND ESTTMA=''", null, ''));
			}else{
				$laViaIngreso = $this->oDb
				->select('VIAING')->from('RIAING')->where('NIGING', '=', $tnIngreso)->get('array');
				if ($this->oDb->numRows()>0){
					$tcViaIngreso=trim($laViaIngreso['VIAING']);
				}
				
				if (!empty($tcViaIngreso)){
					$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$tcViaIngreso' AND ESTTMA=''", null, ''));
				}
			}
		}

		if ($tcTipoRips=='H'){
			$laHospitalizado = $this->oDb
			->select('DESCRI')->from('RIAHIS')->where('NROING', '=', $tnIngreso)->where('INDICE', '=', 90)->Like('DESCRI', '%S%')
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND OP1TMA='D' AND ESTTMA=''", null, ''));
			}	

			if (empty($lcViaIngresoServicio) && !empty($tcTipoIde)){
				$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND OP2TMA='$tcTipoIde' AND ESTTMA=''", null, ''));
			}
			
			if (empty($lcViaIngresoServicio)){
				$lcViaConsultar='01';
				$laViaIngreso = $this->oDb->select('VIAIND')->from('RIAINGD')->where('NIGIND', '=', $tnIngreso)->where('VIAIND', '=', $lcViaConsultar)->get('array');
				if ($this->oDb->numRows()>0){
					$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$lcViaConsultar' AND ESTTMA=''", null, ''));
				}
			}	
				
			if (empty($lcViaIngresoServicio)){
				$lcViaConsultar='02';
				$laViaIngreso = $this->oDb->select('VIAIND')->from('RIAINGD')->where('NIGIND', '=', $tnIngreso)->where('VIAIND', '=', $lcViaConsultar)->get('array');
				if ($this->oDb->numRows()>0){
					$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$lcViaConsultar' AND ESTTMA=''", null, ''));
				}
			}
			
			if (empty($lcViaIngresoServicio)){
				$lcViaConsultar='06';
				$laViaIngreso = $this->oDb->select('VIAIND')->from('RIAINGD')->where('NIGIND', '=', $tnIngreso)->where('VIAIND', '=', $lcViaConsultar)->get('array');
				if ($this->oDb->numRows()>0){
					$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$lcViaConsultar' AND ESTTMA=''", null, ''));
				}
			}
			
			if (empty($lcViaIngresoServicio)){
				$laViaIngreso = $this->oDb->select('VIAIND')->from('RIAINGD')->where('NIGIND', '=', $tnIngreso)->where('VIAIND', '=', $lcViaConsultar)->get('array');
				if ($this->oDb->numRows()>0){
					$lcViaIngresoServicio=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL3TMA='$tcViaIngreso' AND ESTTMA=''", null, ''));
				}
			}
		}	
		unset($laHospitalizado);
		unset($laViaIngreso);
		return $lcViaIngresoServicio;
	}
	
	function consultarCondicionDestinoEgreso($tnIngreso=0,$tcViaIngreso='',$tcCieFallece='',$tcTipoRips='')
	{
		$lcCondicionDestino='';
		
		if (!empty($tcCieFallece)){
			$lcCondicionDestino=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='CONUSEGR' AND OP1TMA='F' AND ESTTMA=''", null, ''));
		}else{
			$laCondicion = $this->oDb
				->select('DESEPI')
				->from('RIAEPI')
				->where('NINEPI', '=', $tnIngreso)
				->where('COSEPI', '=', 9020)
				->get('array');
			if ($this->oDb->numRows()>0){
				$lcCondicionDestino=trim($laCondicion['DESEPI']);
			}		
			
			if (empty($lcCondicionDestino)){
				$lcCondicionDestino=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='CONUSEGR' AND OP6TMA='D' AND ESTTMA=''", null, ''));
			}
		}	
		unset($laCondicion);
		return $lcCondicionDestino;
	}
	
	function consultaDiagnosticoFallece($tnIngreso=0)
	{
		$lcCieFallece=$lcFechaHoraFallece='';
		$loEpicrisis= new Epicrisis_Ingreso();
		$laDatosEpicrisis=$loEpicrisis->obtenerEstadosTodos($tnIngreso);
		
		if (isset($laDatosEpicrisis['CieFallece']) && isset($laDatosEpicrisis['FecFallece'])){
			$lcCieFallece=$laDatosEpicrisis['CieFallece'];
			$lcFechaHoraMuere=$laDatosEpicrisis['FecFallece'].str_pad($laDatosEpicrisis['HorFallece'], 6, '0', STR_PAD_RIGHT);
			$lcFechaHoraFallece=date_format(date_create_from_format('YmdHis', $lcFechaHoraMuere), 'Y-m-d H:i');		
		}
		
		$this->aDatoFallece=[			
			'codigodiagnosticofallece'=>$lcCieFallece,
			'fechahorafallece'=>$lcFechaHoraFallece,
		];
	}
	
	function consultarFechaEgresoUrgencias($tnIngreso=0)
	{
		$lcFechaHoraEgresoUrg='';
		if ($tnIngreso>0){
			$lafechaEgresoUrgencias = $this->oDb
				->select('FECLLE FECHA, HORLLE HORA')
				->from('ENENTRA')
				->where('INGLLE', '=', $tnIngreso)
				->orderBy('CONLLE DESC FETCH FIRST 1 ROWS ONLY')
				->getAll('array');
				if ($this->oDb->numRows()>0){
					$llFechaFormula=$lafechaEgresoUrgencias[0]['FECHA'].str_pad($lafechaEgresoUrgencias[0]['HORA'], 6, '0', STR_PAD_LEFT);
					$lcFechaHoraEgresoUrg=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
				}else{
					$lafechaEgresoUrgencias = $this->oDb
					->select('FEEEPH FECHA, HRREPH HORA')
					->from('RIAEPH')
					->where([ 'NINEPH'=>$tnIngreso, 'ESTEPH'=>3,])
					->In('PGMEPH',['EPIPPAL', 'EPIPPALWEB'])
					->get('array');
					if ($this->oDb->numRows()>0){
						$llFechaFormula=$lafechaEgresoUrgencias['FECHA'].str_pad($lafechaEgresoUrgencias['HORA'], 6, '0', STR_PAD_LEFT);
						$lcFechaHoraEgresoUrg=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
					}	
				}
			if (empty($lcFechaHoraEgresoUrg)){
				$lafechaEgresoUrgencias = $this->oDb
				->select('FEEING FECHA, HREING HORA')
				->from('RIAING')
				->where([ 'NIGING'=>$tnIngreso])
				->get('array');
				if ($this->oDb->numRows()>0){
					$llFechaFormula=$lafechaEgresoUrgencias['FECHA'].str_pad($lafechaEgresoUrgencias['HORA'], 6, '0', STR_PAD_LEFT);
					$lcFechaHoraEgresoUrg=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
				}
			}
		}
		unset($lafechaEgresoUrgencias);
		return $lcFechaHoraEgresoUrg;
	}
	
	function consultarFechaIngresoHospitalizacion($tnIngreso=0)
	{
		$lcFechaHoraIngresoHosp='';
		if ($tnIngreso>0){
			$lafechaIngresoHospitalizacion = $this->oDb
				->select('FECLLE FECHA, HORLLE HORA')
				->from('ENENTRA')
				->where('INGLLE', '=', $tnIngreso)
				->orderBy('CONLLE')
				->getAll('array');
				if ($this->oDb->numRows()>0){
					$lcFechaIngreso=$lafechaIngresoHospitalizacion[0]['FECHA'];
					$lcHoraIngreso=$lafechaIngresoHospitalizacion[0]['HORA'];
					$llFechaFormula=$lcFechaIngreso.str_pad($lcHoraIngreso, 6, '0', STR_PAD_LEFT);
					$lcFechaHoraIngresoHosp=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
				}
		}
		unset($lafechaIngresoHospitalizacion);
		return $lcFechaHoraIngresoHosp;
	}

	function obtenerCausaExterna($tnIngreso=0,$tnConsecutivoCita=0)
	{
		$lcCausaExterna='';
		if ($tnIngreso>0){
			if ($tnConsecutivoCita>0){
				$laCausaExterna = $this->oDb
					->select('TRIM(DESVAN) DESCRIPCION')->from('ANEVAL')
					->where('INGVAN', '=', $tnIngreso)->where('CCIVAN', '=', $tnConsecutivoCita)->where('IN1VAN', '=', 10)->get('array');
					if ($this->oDb->numRows()>0){
						$lcDescripcion=trim($laCausaExterna['DESCRIPCION']);
						$laDescripcion = explode('~', $lcDescripcion)[2];
						$lcCausaExterna=explode(':', $laDescripcion)[1];
					}
			}else{
				$laCausaExterna = $this->oDb
				->select('TRIM(DESVAN) DESCRIPCION')->from('ANEVAL')
				->where('INGVAN', '=', $tnIngreso)->where('IN1VAN', '=', 10)->get('array');
				if ($this->oDb->numRows()>0){
					$lcDescripcion=trim($laCausaExterna['DESCRIPCION']);
					$laDescripcion = explode('~', $lcDescripcion)[2];
					$lcCausaExterna=explode(':', $laDescripcion)[1];
				}
			}
					
			if(empty($lcCausaExterna)){
				$laCausaExterna = $this->oDb
				->select('TRIM(CHAR(SUBIND)) CAUSAEXTERNA')->from('RIAHIS26')
				->where('NROING', '=', $tnIngreso)->where('INDICE', '=', 5)->where('CODIGO', '=', 1)->get('array');
				if ($this->oDb->numRows()>0){
					$lcCausaExterna=trim($laCausaExterna['CAUSAEXTERNA']);
				}
			}
		}
		
		if(empty($lcCausaExterna)){
			$lcCausaExterna=trim($this->oDb->obtenerTabmae1('CL1TMA', 'CODCEX', "OP7TMA=1", null, ''));
		}
		
		if(!empty($lcCausaExterna)){
			$lcCausaExterna=trim($this->oDb->obtenerTabmae1('CL2TMA', 'CODCEX', "CL1TMA='$lcCausaExterna'", null, ''));
		}
		unset($laCausaExterna);		
		return $lcCausaExterna;
	}
	
	function obtenerServicioProcedimiento($tcProcedimiento='',$tcEspecialidadCups='',$tcTipoRips='',$tcTipoCups='')
	{
		$lcServicioCodigo='';
		if ($tcTipoRips=='C'){
			if (!empty($tcProcedimiento)){
				$lcServicioCodigo=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='SERVICIO' AND CL2TMA='CONSUL' AND CL4TMA='$tcProcedimiento'", null, ''));
			}	
		}
		
		if ($tcTipoRips=='P'){
			if ($tcTipoCups=='CIRUG.'){
				$lcServicioCodigo=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='SERVICIO' AND CL2TMA='CUPS'  AND CL3TMA='QUIRUR' AND CL4TMA='$tcEspecialidadCups'", null, ''));
			}else{
				$lcServicioCodigo=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='SERVICIO' AND CL2TMA='CUPS'  AND CL3TMA='APOYOD' AND CL4TMA='$tcEspecialidadCups'", null, ''));
			}
		}	
		
		if (empty($lcServicioCodigo)){
			if (!empty($tcEspecialidadCups)){
				$lcServicioCodigo=trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='SERVICIO' AND CL2TMA='CONSUL' AND CL4TMA='$tcEspecialidadCups'", null, ''));
			}	
		}
		unset($laCausaExterna);		
		return $lcServicioCodigo;
	}

	function obtenerEspecialidadProcedimiento($tcProcedimiento='')
	{
		$lcEspecialidadProcedimiento='';

		$laCups = $this->oDb
		->select('ESPCUP')
		->from('RIACUP')->where('CODCUP', '=', $tcProcedimiento)
		->get('array');
		if ($this->oDb->numRows()>0){
			$lcEspecialidadProcedimiento=isset($laCups['ESPCUP'])?trim($laCups['ESPCUP']):'';
		}	
		unset($laCups);		
		return $lcEspecialidadProcedimiento;
	}
	
	function obtenerConsecutivoConsulta($tnIngreso=0)
	{
		$this->nConsecutivoConsulta=0;
		$laHospitalizacion = $this->oDb
			->select('CONCON CONSECCONSULTA')
			->from('RIAHIS')
			->where('NROING', '=', $tnIngreso)
			->where('INDICE', '=', 54)
			->get('array');
		if ($this->oDb->numRows()>0){
			$this->nConsecutivoConsulta=isset($laHospitalizacion['CONSECCONSULTA'])?$laHospitalizacion['CONSECCONSULTA']:0;
		}	
		unset($laHospitalizacion);		
	}
	
	function obtenerFechaEgreso($tnIngreso=0)
	{
		$this->nFechaIngreso=$this->nHoraIngreso=$this->nFechaEgreso=$this->nHoraEgreso=0;
		$this->cFechaHoraEgreso='';

		$laEgreso = $this->oDb
			->select('FEIING FECHAINGRESO, HORING HORAINGRESO, FEEING FECHAEGRESO, HREING HORAEGRESO')
			->from('RIAING')
			->where('NIGING', '=', $tnIngreso)
			->get('array');
		if ($this->oDb->numRows()>0){
			$this->nFechaIngreso=isset($laEgreso['FECHAINGRESO'])?$laEgreso['FECHAINGRESO']:0;
			$this->nHoraIngreso=isset($laEgreso['HORAINGRESO'])?str_pad($laEgreso['HORAINGRESO'], 6, '0', STR_PAD_LEFT):0;
			$this->nFechaEgreso=isset($laEgreso['FECHAEGRESO'])?$laEgreso['FECHAEGRESO']:0;
			$this->nHoraEgreso=isset($laEgreso['HORAEGRESO'])?str_pad($laEgreso['HORAEGRESO'], 6, '0', STR_PAD_LEFT):0;
			$this->cFechaHoraEgresoCompara=date('Y-m-d H:i:s', strtotime($this->nFechaEgreso.$this->nHoraEgreso));
			
			if ($this->nFechaEgreso>0 && $this->nHoraEgreso>0){
				$llFechaFormula=$this->nFechaEgreso.$this->nHoraEgreso;
				$this->cFechaHoraEgreso=date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i');
			}	
		}	
		unset($laEgreso);		
	}
	
	function obtenerCopagoCuota($tnIngreso=0,$tnFactura=0)
	{
		$this->nValorCopagoCuota=$this->nAplicaCopagoCuota=0;
		$this->cTipoPagoModerador=$this->cFacturaPagoModerador='';
		$this->cTipoPagoModeradorEstandar=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='TPAGMOD' AND DE2TMA='' AND ESTTMA=''", null, ''));

		$laCopagoCuota = $this->oDb
			->select('VPRDFA VALORCOPAGO, TRIM(ELEDFA) TIPOMODERADORA')
			->from('FACDETF')
			->where('INGDFA', '=', $tnIngreso)
			->where('NFADFA', '=', $tnFactura)
			->where('TINDFA', '=', '900')
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcTipoModeradora=isset($laCopagoCuota['TIPOMODERADORA'])?trim($laCopagoCuota['TIPOMODERADORA']):'';
			$this->nValorCopagoCuota=isset($laCopagoCuota['VALORCOPAGO'])?intval(abs($laCopagoCuota['VALORCOPAGO'])):0;

			$laFacturaMopderador = $this->oDb
				->select('A.NFADFA FACTURA')
				->from('FACDETF AS A')
				->leftJoin("FACCABF B", "A.INGDFA=B.INGCAB AND A.NFADFA=B.FRACAB", null)
				->where('A.INGDFA', '=', $tnIngreso)
				->where('A.VPRDFA', '>', 0)
				->where('A.TINDFA', '=', '900')
				->where('B.MA1CAB', '=', '')
				->get('array');
			if ($this->oDb->numRows()>0){
				$this->cFacturaPagoModerador=isset($laFacturaMopderador['FACTURA'])?trim($laFacturaMopderador['FACTURA']):'';
			}
			if (!empty($lcTipoModeradora)){
				$this->cTipoPagoModerador=trim($this->oDb->obtenerTabmae1('CL2TMA', 'GENRIPS', "CL1TMA='TPAGMOD' AND DE2TMA='$lcTipoModeradora' AND ESTTMA=''", null, ''));
			}	
		}
		
		if (empty($this->cTipoPagoModerador)){
			$this->cTipoPagoModerador=$this->cTipoPagoModeradorEstandar;
		}
		unset($laCopagoCuota,$laFacturaMopderador);		
	}

	function obtenerCantidadProcedimientosFacturados($tnIngreso=0,$tnFactura=0)
	{
		$laProcedimientos = $this->oDb
			->select('COUNT(*) CANTIDAD')
			->from('FACDETF')
			->where('INGDFA', '=', $tnIngreso)
			->where('NFADFA', '=', $tnFactura)
			->where('TINDFA', '=', '400')
			->where('ELEDFA', '<>', 'X99999')
			->get('array');
		if ($this->oDb->numRows()>0){
			$this->nCantidadProcedimientosFacturados=intval($laProcedimientos['CANTIDAD']);
		}
		unset($laProcedimientos);		
	}

	function consultarDiagnosticoConsulta($tnIngreso=0,$tnConsecutivoCita=0,$tnConsecutivoConsulta=0,$tcCups='')
	{
		$laCieDevuelve=[];
		$lcDiagnosticoPrincipal=$lcDiagnosticoRelacionado1=$lcDiagnosticoRelacionado2=$lcDiagnosticoRelacionado3='';
		$lcTipoDiagnosticoPrinc=$lcFinalidad='';
		if ($tnIngreso>0){
			if ($tnConsecutivoCita>0){
				$laDiagnostico = $this->oDb
				->select('trim(SUBSTR(TRIM(DESVAN), 8, 4)) DESCRIPCION')
				->from('ANEVAL')
				->where('INGVAN', '=', $tnIngreso)
				->where('CCIVAN', '=', $tnConsecutivoCita)
				->where('IN1VAN', '=', 90)
				->where('IN2VAN', '=', 2)
				->where('CNLVAN', '=', 1)
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['DESCRIPCION']);
				}
			}
			
			//	INTERCONSULTAS
			if (empty($lcDiagnosticoPrincipal)){
				if ($tnConsecutivoCita>0){
					$laDiagnostico = $this->oDb
					->select('SUBSTR(DESINT, 1, 4) CIE, SUBSTR(DESINT, 5, 2) TIPOCIE')
					->from('INTCON')
					->where('INGINT', '=', $tnIngreso)
					->where('CORINT', '=', $tnConsecutivoCita)
					->where('CNLINT', '=', 90000)
					->get('array');
					if ($this->oDb->numRows()>0){
						$lcDiagnosticoPrincipal=trim($laDiagnostico['CIE']);
						$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIE']);
					}
				}	
			}
			
			//	JUNTAS MEDICAS
			if (empty($lcDiagnosticoPrincipal)){
				if ($tnConsecutivoCita>0){
					$laDiagnostico = $this->oDb
					->select('SUBSTR(CA3JUN, 1, 4) CIE, SUBSTR(CA3JUN, 11, 2) TIPOCIE, SUBSTR(CA3JUN, 14, 2) FINALIDAD')
					->from('RIAJUN')
					->where('INGJUN', '=', $tnIngreso)
					->where('CCIJUN', '=', $tnConsecutivoCita)
					->where('CNIJUN', '=', 1)
					->get('array');
					if ($this->oDb->numRows()>0){
						$lcDiagnosticoPrincipal=trim($laDiagnostico['CIE']);
						$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIE']);
						$lcFinalidad=trim($laDiagnostico['FINALIDAD']);
					}
				}	
			}
			
			//	PROCEDIMIENTOS CONSULTA
			if ($tnConsecutivoCita>0 && !empty($tcCups)){
				$laDiagnostico = $this->oDb
					->select('SUBSTR(DESCRI, 15, 4) CIEPRINCIPAL, SUBSTR(DESCRI, 31, 2) FINALIDAD, SUBSTR(DESCRI, 37, 1) TIPOCIEPRINCIPAL')
					->from('RIAHIS')
					->where('NROING', '=', $tnIngreso)
					->where('CONCON', '=', $tnConsecutivoCita)
					->where('INDICE', '=', 70)
					->where('SUBORG', '=', $tcCups)
					->where('CONSEC', '=', 101)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
					$lcFinalidad=trim($laDiagnostico['FINALIDAD']);
					$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIEPRINCIPAL']);
				}
			}
			
			//	CONSULTA NUTRICION
			if ($tnConsecutivoCita>0 && !empty($tcCups)){
				$laDiagnostico = $this->oDb
					->select('SUBSTR(DESNUT, 1, 4) CIEPRINCIPAL')
					->from('INFNUT')
					->where('INGNUT', '=', $tnIngreso)
					->where('CCINUT', '=', $tnConsecutivoCita)
					->where('INDNUT', '=', 10)
					->where('IN2NUT', '>', 1)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
				}
			}

			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
				->select('TRIM(CIECGR) DESCRIPCION')
				->from('CUPGRA')
				->where('INGCGR', '=', $tnIngreso)
				->where('CCICGR', '=', $tnConsecutivoCita
				)->orderBy('CONCGR DESC')
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['DESCRIPCION']);
				}
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				if ($tnConsecutivoConsulta>0){
					$laDiagnostico = $this->oDb
					->select('SUBIND SUBINDICE, TRIM(SUBORG) CIE, CODIGO CLASE')
					->from('RIAHIS26')
					->where('NROING', '=', $tnIngreso)
					->where('CONCON', '=', $tnConsecutivoConsulta)
					->where('INDICE', '=', 25)
					->getAll('array');
					if ($this->oDb->numRows()>0){
						foreach ($laDiagnostico as $laDiagnosticos){
							$lcSubindice=trim($laDiagnosticos['SUBINDICE']);
							
							if ($lcSubindice=='1'){
								$lcDiagnosticoPrincipal=trim($laDiagnosticos['CIE']);
								$lcTipoDiagnosticoPrinc=trim($laDiagnosticos['CLASE']);
							}
							
							if ($lcSubindice=='2' && empty($lcDiagnosticoRelacionado1)){
								$lcDiagnosticoRelacionado1=trim($laDiagnosticos['CIE']);
							}	

							if ($lcSubindice=='2' && empty($lcDiagnosticoRelacionado2)){
								$lcDiagnosticoRelacionado2=trim($laDiagnosticos['CIE']);
							}
							
							if ($lcSubindice=='2' && empty($lcDiagnosticoRelacionado3)){
								$lcDiagnosticoRelacionado3=trim($laDiagnosticos['CIE']);
							}	
						}	
					}
				}	
			}
		}
		$laCieDevuelve=[			
			'principal'=>$lcDiagnosticoPrincipal,
			'relacionado1'=>$lcDiagnosticoRelacionado1,
			'relacionado2'=>$lcDiagnosticoRelacionado2,
			'relacionado3'=>$lcDiagnosticoRelacionado3,
			'tipodiagnostico'=>$lcTipoDiagnosticoPrinc,
			'finalidad'=>$lcFinalidad,
		];
		unset($laDiagnostico);	
		unset($laDiagnosticoRelacionados);	
		return $laCieDevuelve;
	}
	
	function consultarDiagnosticoProcedimiento($tnIngreso=0,$tcCups='',$tnConsecCita=0,$tnConsecEvolucion=0,$tnFechaConsumo=0)
	{
		$laCieDevuelve=[];
		$lcDiagnosticoPrincipal=$lcDiagnosticoRelacionado1=$lcDiagnosticoRelacionado2=$lcDiagnosticoRelacionado3='';
		$lcTipoDiagnosticoPrinc='';
		
		if ($tnIngreso>0){
			if ($tnConsecCita>0){
				$laDiagnostico = $this->oDb
					->select('SUBSTR(DESCRI, 15, 4) CIEPRINCIPAL, SUBSTR(DESCRI, 37, 1) TIPOCIEPRINCIPAL')
					->from('RIAHIS')
					->where('NROING', '=', $tnIngreso)
					->where('CONCON', '=', $tnConsecCita)
					->where('INDICE', '=', 70)
					->where('SUBORG', '=', $tcCups)
					->where('CONSEC', '=', 101)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
					$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIEPRINCIPAL']);
				}

				if (empty($lcDiagnosticoPrincipal)){
					$laDiagnostico = $this->oDb
						->select('TRIM(SUBSTR(DESCRI, 31, 11)) CIEPRINCIPAL')
						->from('RIAHIS')
						->where('NROING', '=', $tnIngreso)
						->where('CONCON', '=', $tnConsecCita)
						->where('INDICE', '=', 70)
						->where('CONSEC', '=', 401)
						->get('array');
					if ($this->oDb->numRows()>0){
						$lcDiagnosticoPrincipal=substr(trim($laDiagnostico['CIEPRINCIPAL']), 0, 4);
					}
				}	

				if (empty($lcDiagnosticoPrincipal)){
					$laDiagnostico = $this->oDb
						->select('TRIM(SUBSTR(CA3JUN, 0, 5)) CIEPRINCIPAL, SUBSTR(CA3JUN, 11, 1) TIPOCIEPRINCIPAL')
						->from('RIAJUN')
						->where('INGJUN', '=', $tnIngreso)
						->where('CCIJUN', '=', $tnConsecCita)
						->where('CNIJUN', '=', 1)
						->get('array');
					if ($this->oDb->numRows()>0){
						$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
						$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIEPRINCIPAL']);
					}
				}	
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
					->select('trim(SUBSTR(TRIM(DESEVL), 0, 5)) DESCRIPCION')
					->from('EVOLUC')
					->where('NINEVL', '=', $tnIngreso)
					->where('CONEVL', '=', $tnConsecEvolucion)
					->where('CNLEVL', '=', 900020)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['DESCRIPCION']);
				}
			}

			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
					->select('trim(SUBSTR(TRIM(DESEVL), 0, 5)) DESCRIPCION')
					->from('EVOLUC')
					->where('NINEVL', '=', $tnIngreso)
					->where('FECEVL', '=', $tnFechaConsumo)
					->where('CNLEVL', '=', 900020)
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['DESCRIPCION']);
				}
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
					->select('TRIM(CIECGR) DESCRIPCION')
					->from('CUPGRA')
					->where('INGCGR', '=', $tnIngreso)
					->where('CCICGR', '=', $tnConsecCita)
					->orderBy('CONCGR DESC')
					->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['DESCRIPCION']);
				}
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico=$this->oDb
					->select('TRIM(CIEINC) DESCRIPCION')
					->from('RIAINGC')
					->where('INGINC', '=', $tnIngreso)
					->orderBy('IDEINC DESC FETCH FIRST 1 ROWS ONLY')
					->getAll('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=$laDiagnostico[0]['DESCRIPCION'];
				}
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
					->select('SUBSTR(DESCRI, 15, 4) CIEPRINCIPAL, SUBSTR(DESCRI, 37, 1) TIPOCIEPRINCIPAL')->from('RIAHIS')
					->where('NROING', '=', $tnIngreso)->where('INDICE', '=', 70)->where('SUBORG', '=', $tcCups)->where('CONSEC', '=', 101)->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
					$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIEPRINCIPAL']);
				}
			}
			
			if (empty($lcDiagnosticoPrincipal)){
				$laDiagnostico = $this->oDb
					->select('DIPEDC CIEPRINCIPAL, CLIEDC TIPOCIEPRINCIPAL')->from('EVODIA')
					->where('INGEDC', '=', $tnIngreso)->where('INDEDC', '=', 1)->orderBy('FECEDC DESC, HOREDC DESC')->get('array');
				if ($this->oDb->numRows()>0){
					$lcDiagnosticoPrincipal=trim($laDiagnostico['CIEPRINCIPAL']);
					$lcTipoDiagnosticoPrinc=trim($laDiagnostico['TIPOCIEPRINCIPAL']);
				}
			}
		}
		
		$laCieDevuelve=[			
			'principal'=>$lcDiagnosticoPrincipal,
			'relacionado1'=>$lcDiagnosticoRelacionado1,
			'relacionado2'=>$lcDiagnosticoRelacionado2,
			'relacionado3'=>$lcDiagnosticoRelacionado3,
			'tipodiagnostico'=>$lcTipoDiagnosticoPrinc,
		];
		unset($laDiagnostico);	
		return $laCieDevuelve;
	}	
			
	function consultarDiagnosticoSalida($tnIngreso=0)
	{
		$laCieSalidaDevuelve=[];
		$lcCieEgresoPrincipal=$lcCieRelacionado1=$lcCieRelacionado2=$lcCieRelacionado3='';
		
		if ($tnIngreso>0){
			$laDiagnosticoRelacionados = $this->oDb
				->select('trim(DEGDIA) EGRESO, trim(DE1DIA) RELACIONADO1, trim(DE2DIA) RELACIONADO2, trim(DE3DIA) RELACIONADO3')
				->from('RIADIA')
				->where('INGDIA', '=', $tnIngreso)
				->get('array');
				if ($this->oDb->numRows()>0){
					$lcCieEgresoPrincipal=trim($laDiagnosticoRelacionados['EGRESO']);
					$lcCieRelacionado1=trim($laDiagnosticoRelacionados['RELACIONADO1']);
					$lcCieRelacionado2=trim($laDiagnosticoRelacionados['RELACIONADO2']);
					$lcCieRelacionado3=trim($laDiagnosticoRelacionados['RELACIONADO3']);
				}
		}		
		$laCieSalidaDevuelve=[			
			'egresoprincipal'=>$lcCieEgresoPrincipal,
			'egresorelacionado1'=>$lcCieRelacionado1,
			'egresorelacionado2'=>$lcCieRelacionado2,
			'egresorelacionado3'=>$lcCieRelacionado3,
		];
		unset($laDiagnosticoRelacionados);	
		return $laCieSalidaDevuelve;
	}
	
	function consultaTipoDiagnostico($tnIngreso=0,$tnConsecutivoConsulta=0)
	{
		$lcTipoDiagnostricoHc='';
		if ($tnIngreso>0){
			$laTipoDiagnostico = $this->oDb
			->select('CODIGO CLASE')
			->from('RIAHIS')
			->where('NROING', '=', $tnIngreso)
			->where('INDICE', '=', 25)
			->where('SUBIND', '=', 1)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoDiagnostricoHc=trim($laTipoDiagnostico['CLASE']);
			}
		}
		$lcTipoDiagnostricoHc=!empty($lcTipoDiagnostricoHc)?$lcTipoDiagnostricoHc:trim($this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='TIPDIAG' AND ESTTMA=''", null, ''));
		unset($laTipoDiagnostico);	
		return $lcTipoDiagnostricoHc;
	}
	
	function consultarDatosMedicamento($tnIngreso=0,$tcMedicamento='',$tnConsecutivoConsumo=0,$tnFechaConsumo=0,$tnHoraConsumo=0,$tnConsecutivoCirugia=0)
	{
		$laDatoMedicamento=[];
		$lcFechaHoraDispensacion=$lcTipoMedicamento=$lcCums=$llFechaFormula=$lcCiePrincipal=$lcNombreMedicamento='';
		$lcMipresMedicamento=$lcNumeroAutorizacion=$lcTipoIdePrescribe=$lcNumIdePrescribe='';
		$lcConcentracionMedicamento=$lcUnidadMedida=$lcFormaFarmaceutica=$lcDocumentoCt=$lcCodigoIum='';
		$lnDiasTratamiento=$lnConsecEvolucion=$lcUnidadMinDispensa=0;
		$laDocumentoCt=$this->consultaDocumentoCt($tnIngreso,$tnConsecutivoConsumo,$tcMedicamento,$tnFechaConsumo,$tnHoraConsumo);
		$tnFechaConsumo=intval($laDocumentoCt['fechaconsumo'])>0?$laDocumentoCt['fechaconsumo']:$tnFechaConsumo;
		$llFechaFormula=$laDocumentoCt['fechaformula'];
		$lcCums=$laDocumentoCt['nrocums'];
		$lnConsecEvolucion=$laDocumentoCt['nroevolucion'];
		$lcFechaHoraDispensacion=!empty($llFechaFormula)?date_format(date_create_from_format('YmdHis', $llFechaFormula), 'Y-m-d H:i'):'';
		$laDiagnosticos=$this->consultarDiagnosticoProcedimiento($tnIngreso,'',0,$lnConsecEvolucion,$tnFechaConsumo);
		$lcCiePrincipal=$laDiagnosticos['principal'];
		
		if (empty($lcCums)){
			$lcCums = FuncionesInv::fObtenerCUM($tnIngreso, $tcMedicamento, $tnConsecutivoConsumo, true, true)['CUM'];
		}

		if (!empty($lcCums)){
			$laDatosIum = FuncionesInv::fObtenerIum($lcCums);
			$lcCodigoIum=$laDatosIum['codigoium'];
			$lcTipoMedicamento=$laDatosIum['tipomedicamento'];
		}	
		$lcCodigoMedicamento=!empty($lcCodigoIum)?$lcCodigoIum:$lcCums;
		$lcCodigoMedicamento=!empty($lcCodigoMedicamento)?$lcCodigoMedicamento:$tcMedicamento;
		
		$laMipres=$this->obtenerMipres($tnIngreso,$tcMedicamento,'500',0);
		$lcMipresMedicamento=isset($laMipres['idmipres'])?$laMipres['idmipres']:'';
		$lcNumeroAutorizacion=isset($laMipres['numeromipres'])?$laMipres['numeromipres']:'null';

		if ($lcTipoMedicamento=='03'){
			$lcNombreMedicamento=$this->consultaDatosConsumo($tcMedicamento,'500');

			$laConcentracion = $this->oDb
			->select('trim(CONCE) CONCENTRACION, UNDFOX UNIDMEDIDA')
			->select('(SELECT OP3TMA FROM TABMAE AS C WHERE C.TIPTMA=\'MEDDOS\' AND CL2TMA=A.UNDFOX FETCH FIRST 1 ROWS ONLY) AS UNIDADMEDIDA')
			->from('INVMEDA AS A')
			->where('CODIGO', '=', $tcMedicamento)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcConcentracionMedicamento=isset($laConcentracion['CONCENTRACION'])?trim($laConcentracion['CONCENTRACION']):'';;
				$lcUnidadMedida=isset($laConcentracion['UNIDADMEDIDA'])?trim($laConcentracion['UNIDADMEDIDA']):'';;
			}	
			
			$laFormaFarmaceutica = $this->oDb
			->select('trim(FFMINB) FORMAFARMACEUTICA')
			->from('INVMEDB')
			->where('CODINB', '=', $tcMedicamento)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcFormaFarmaceutica=isset($laFormaFarmaceutica['FORMAFARMACEUTICA'])?trim($laFormaFarmaceutica['FORMAFARMACEUTICA']):'';;
			}	
		}else{
			$lcNombreMedicamento='null';
			$lcConcentracionMedicamento=0;
			$lcUnidadMedida=0;
			$lcFormaFarmaceutica=null;
		}
		
		$laUnidadMinimaDispensacion = $this->oDb
			->select('(SELECT TRIM(OP2TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'MEDPRE\' AND DE1TMA=A.PRESE FETCH FIRST 1 ROWS ONLY) AS UMINIMADISPENSACION')
			->tabla('INVMEDA AS A')
			->where('CODIGO', '=', $tcMedicamento)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcUnidadMinDispensa=isset($laUnidadMinimaDispensacion['UMINIMADISPENSACION'])?intval(trim($laUnidadMinimaDispensacion['UMINIMADISPENSACION'])):0;
			}	
		$lnDiasTratamiento=1;
		$laMedicoFormulo=$this->consultarMedicoFormula($tnIngreso,$tcMedicamento,$lnConsecEvolucion,$tnFechaConsumo,$tnConsecutivoCirugia);
		$lcTipoIdePrescribe=$laMedicoFormulo['tidoide'];
		$lcNumIdePrescribe=$laMedicoFormulo['numeroide'];
		
		$laDatoMedicamento=[			
			'fechahoradispensacion'=>$lcFechaHoraDispensacion,
			'codigomedicamento'=>$lcCodigoMedicamento,
			'tipomedicamento'=>$lcTipoMedicamento,
			'diagnosticoprincipal'=>$lcCiePrincipal,
			'nombremedicamento'=>$lcNombreMedicamento,
			'concentracionmedicamento'=>$lcConcentracionMedicamento,
			'unidadmedida'=>$lcUnidadMedida,
			'formafarmaceutica'=>$lcFormaFarmaceutica,
			'unidadmindispensa'=>$lcUnidadMinDispensa,
			'diastratamiento'=>$lnDiasTratamiento,
			'tipodocumentoldentificacion'=>$lcTipoIdePrescribe,
			'numdocumentoldentificacion'=>$lcNumIdePrescribe,
			'mipres'=>$lcMipresMedicamento,
			'autorizacionmed'=>$lcNumeroAutorizacion,
		];
		unset($laMedicamento,$laConcentracion,$laFormaFarmaceutica);	
		return $laDatoMedicamento;
	}

	function consultaDocumentoCt($tnIngreso=0,$tnConsecutivoConsumo=0,$tcMedicamento='',$tnFechaConsumo=0,$tnHoraConsumo=0)
	{
		$laDatosDocumentoCt=[];
		$lnConsecEvolucion=$lnFechaConsumo=0;
		$lcCums=$llFechaFormula='';
		
		if ($tnIngreso>0){
			$laMedicamento = $this->oDb
				->select('trim(CODCUM) CUMS, FECCRE, HORCRE, TRIM(NI3DET) DOCUMENTOCT')
				->from('SIMNIMOV')
				->where('TIPDOC', '=', 'CE')
				->where('INGRES', '=', $tnIngreso)
				->where('CONSEC', '=', $tnConsecutivoConsumo)
				->where('CODSHA', '=', $tcMedicamento)
				->get('array');
			if ($this->oDb->numRows()>0){
				$lnFechaConsumo=$laMedicamento['FECCRE'];
				$llFechaFormula=$lnFechaConsumo.str_pad($laMedicamento['HORCRE'], 6, '0', STR_PAD_LEFT);
				$lcCums=isset($laMedicamento['CUMS'])?trim($laMedicamento['CUMS']):'';
				$lcDocumentoCt=isset($laMedicamento['DOCUMENTOCT'])?trim($laMedicamento['DOCUMENTOCT']):'';
			}else{
				$llFechaFormula=$tnFechaConsumo.str_pad($tnHoraConsumo, 6, '0', STR_PAD_LEFT);
			}
		}
		
		if (!empty($lcDocumentoCt)){
			$laEvolucion = $this->oDb
				->select('ND3CTR CONSECEVOLUCION')
				->from('SIMNCONC')
				->where('TDOCTR', '=', 'CT')
				->where('NDOCTR', '=', $lcDocumentoCt)
				->get('array');
			if ($this->oDb->numRows()>0){
				$lnConsecEvolucion=isset($laEvolucion['CONSECEVOLUCION'])?intval($laEvolucion['CONSECEVOLUCION']):0;		
			}
		}

		$laDatosDocumentoCt=[			
			'nrocums'=>$lcCums,
			'fechaconsumo'=>$lnFechaConsumo,
			'nroevolucion'=>$lnConsecEvolucion,
			'fechaformula'=>$llFechaFormula,
		];
		unset($laMedicamento,$laEvolucion);	
		return $laDatosDocumentoCt;
	}
	
	function consultarMedicoFormula($tnIngreso=0,$tcMedicamento='',$tnEvolucion=0,$tnFechaConsumo=0,$tnConsecutivoCirugia=0)
	{
		$laMedicoFormula=[];
		$lcTipoIde=$lcNroIde='';
		$lnFechaIngreso=$this->nFechaIngreso;

		$laFormulacion = $this->oDb
		->select('trim(B.TIDRGM) TIPOIDE, B.NIDRGM NROIDE')->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')->from('FORMED A')
		->leftJoin("RIARGMN B", "A.USRFMD=B.USUARI", null)
		->where('A.NINFMD', '=', $tnIngreso)->where('A.CEVFMD', '=', $tnEvolucion)->where('A.MEDFMD', '=', $tcMedicamento)->get('array');
		if ($this->oDb->numRows()>0){
			$lcTipoIde=trim($laFormulacion['TIPOIDE_HOMOLOGO']);
			$lcNroIde=trim($laFormulacion['NROIDE']);
		}				

		if (empty($lcNroIde)){
			$laMedicoCirugia=$this->consultarProfesionalCirugia($tnIngreso,$tnConsecutivoCirugia);
			$lcTipoIde=$laMedicoCirugia['tipoiderealiza'];
			$lcNroIde=$laMedicoCirugia['nroiderealiza'];
		}
		
		if (empty($lcNroIde)){
			$laFormulacion = $this->oDb
			->select('trim(B.TIDRGM) TIPOIDE, B.NIDRGM NROIDE')->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')->from('FORMED A')
			->leftJoin("RIARGMN B", "A.USRFMD=B.USUARI", null)
			->where('A.NINFMD', '=', $tnIngreso)->where('A.MEDFMD', '=', $tcMedicamento)->where('A.FECFMD', '=', $tnFechaConsumo)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoIde=trim($laFormulacion['TIPOIDE_HOMOLOGO']);
				$lcNroIde=trim($laFormulacion['NROIDE']);
			}				
		}	

		if (empty($lcNroIde)){
			$laFormulacion = $this->oDb
			->select('trim(B.TIDRGM) TIPOIDE, B.NIDRGM NROIDE')->select('(SELECT TRIM(DOCUME) FROM RIATI WHERE TIPDOC=B.TIDRGM) AS TIPOIDE_HOMOLOGO')->from('RIAFARI A')
			->leftJoin("RIARGMN B", "A.USCRFI=B.USUARI", null)
			->where('A.INGRFI', '=', $tnIngreso)->where('A.CEVRFI', '=', $tnEvolucion)->where('A.COIRFI', '=', $tcMedicamento)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lcTipoIde=trim($laFormulacion['TIPOIDE_HOMOLOGO']);
				$lcNroIde=trim($laFormulacion['NROIDE']);
			}		
		}

		if (empty($lcNroIde)){
			$laMedicoEvoluciono=$this->consultarMedicoEvoluciono($tnIngreso,$tnFechaConsumo);
			$lcTipoIde=$laMedicoEvoluciono['tipoiderealiza'];
			$lcNroIde=$laMedicoEvoluciono['nroiderealiza'];	
		}	

		if (empty($lcNroIde)){
			$laTratante=$this->consultarTratanteRealiza($tnIngreso,$tnFechaConsumo);
			$lcTipoIde=$laTratante['tipoiderealiza'];
			$lcNroIde=$laTratante['nroiderealiza'];	
		}	
		
		$laMedicoFormula=[			
			'tidoide'=>$lcTipoIde,
			'numeroide'=>$lcNroIde,
		];
		unset($laFormulacion);	
		return $laMedicoFormula;
	}
	
	function obtieneZonaTerritorial($tcZonaActual='')
	{
		$lcZonaTerritorial='';
		$lcZonaTerritorial = trim($this->oDb->obtenerTabmae1('OP2TMA', 'DATING', "CL1TMA='3' AND CL2TMA='$tcZonaActual' AND ESTTMA=''", null, ''));
		return $lcZonaTerritorial;
	}
	
	function obtieneIncapacidadPaciente($tnIngreso=0)
	{
		$lcConIncapacidad='';
		if (intval($tnIngreso)>0){
			$laParametros = $this->oDb
			->select('INGORA, trim(SUBSTR(trim(DESORA), 94, 1)) DESCRIPCION')->from('ORDAMB')
			->where('INGORA', '=', $tnIngreso)->where('INDORA', '=', 1)->orderBy('CORORA')->getAll('array');
			if ($this->oDb->numRows()>0){
				foreach ($laParametros as $laDatosIncapacidad){
					$lcConIncapacidad=trim($laDatosIncapacidad['DESCRIPCION']);
					
					if (!empty($lcConIncapacidad)){
						break;
					}	
				}	
			}	
		}					
		$lcConIncapacidad=!empty($lcConIncapacidad)?'SI':'NO';
		unset($laParametros);	
		return $lcConIncapacidad;
	}
	
	function consultarDatosRecienNacido($tnIngreso=0, $tnCosnecutivoConsulta=0)
	{
		$laRecienNacido=[];
		$lcPeso=$lcEdadGestacional=$lcConsultasPrenatales='';
		
		if ($tnIngreso>0){
			$laConsultaRecien = $this->oDb
			->select('PSOEXF PESO')->from('RIAEXFL01')->where('NIGEXF', '=', $tnIngreso)->get('array');
			if ($this->oDb->numRows()>0){
				$lcPeso=trim($laConsultaRecien['PESO']);
			}
				
			$laHcRecien = $this->oDb
			->select('CODIGO, TRIM(DESCRI) DESCRIPCION')->from('RIAHIS')->where('NROING', '=', $tnIngreso)->where('CONCON', '=', 1)->where('INDICE', '=', 10)->where('SUBIND', '=', 15)->in('CODIGO', [21,22])->getAll('array');
			if ($this->oDb->numRows()>0){
				foreach ($laHcRecien as $laDatos){
					if ($laDatos['CODIGO']=='21'){
						$lcEdadGestacional=trim($laDatos['DESCRIPCION']);
					}

					if ($laDatos['CODIGO']=='22'){
						$lcConsultasPrenatales=trim($laDatos['DESCRIPCION']);
					}
				}
			}
		}		
		$laRecienNacido=[			
			'pesopaciente'=>$lcPeso,
			'edadgestacional'=>$lcEdadGestacional,
			'consultasprenatales'=>$lcConsultasPrenatales,
		];
		unset($laConsultaRecien,$laHcRecien);	
		return $laRecienNacido;
	}
	
	function nuevoID($tnIndex=0)
	{
		$lnRipsConsec = $this->oDb->secuencia('SEQ_RIPSAL');
		return $lnRipsConsec;
	}
	
	function ValidarSalida()
	{
		$llResultado = !($this->ExisteRipsSalida(1,0,0));
		return $llResultado ;
	}
	
	function ExisteRipsSalida($tnIndRis=1,$tnConRis=0,$tnCnlRis=0)
	{
		$llexisteRips = false ;
		$laTemp = $this->oDb
			->select('COUNT(CONRIS) AS FIELD')
			->from('RIPSAL')
			->where(['INDRIS'=>$tnIndRis,
				   'INGRIS'=>$this->nIngreso
				 ])
			->in('ESTRIS', [' ','CERRADO'])
			->get('array');
		if(is_array($laTemp)){
			if($laTemp['FIELD']>0){
				$llexisteRips = true;
			}
		}
		unset($laTemp);
		return $llexisteRips;
	}
	
	function consultaDescripcionZonaTerritorial($tcZonaActual='')
	{
		global $goDb;
		$lcDescripcionZonaTerritorial='';
		if (!empty($tcZonaActual)){
			$lcDescripcionZonaTerritorial = trim($goDb->obtenerTabmae1('DE1TMA', 'DATING', "CL1TMA='3' AND OP2TMA='$tcZonaActual' AND ESTTMA=''", null, ''));
		}	
		return $lcDescripcionZonaTerritorial;
	}
	
	function consultaDescripcionUnidadMinimaDispensacion($tcUnidadMinimaDispensacion='')
	{
		global $goDb;
		$lcDescripcionUnidadMinimaDispensacion='';
		if (!empty($tcUnidadMinimaDispensacion)){
			$lcDescripcionUnidadMinimaDispensacion = trim($goDb->obtenerTabmae1('DE1TMA', 'MEDPRE', "OP2TMA='$tcUnidadMinimaDispensacion'", null, ''));
		}	
		return $lcDescripcionUnidadMinimaDispensacion;
	}

	function consultaDescripcionUnidadmedida($tcUnidadMedida='')
	{
		global $goDb;
		$lcDescripcionUnidadMedida='';
		$lnUnidadMedida=intval($tcUnidadMedida);
		if ($lnUnidadMedida>0){
			$lcDescripcionUnidadMedida = trim($goDb->obtenerTabmae1('DE1TMA', 'MEDDOS', "OP3TMA=$lnUnidadMedida", null, ''));
		}	
		return $lcDescripcionUnidadMedida;
	}

	function consultarDescripcionTipoMedicamento($tcTipoMedicamento='')
	{
		global $goDb;
		$lcDescripcionTipoMedicamento='';
		if (!empty($tcTipoMedicamento)){
			$lcDescripcionTipoMedicamento = trim($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='TIPMED' AND CL2TMA='$tcTipoMedicamento'", null, ''));
		}	
		return $lcDescripcionTipoMedicamento;
	}

	function consultarDescripcionFormaFarmaceutica($tcFormaFarmaceutica='')
	{
		global $goDb;
		$lcDescripcionFormaFarmaceutica='';
		if (!empty($tcFormaFarmaceutica)){
			$lcDescripcionFormaFarmaceutica = trim($goDb->obtenerTabmae1('DE2TMA', 'FORFARM', "CL1TMA='$tcFormaFarmaceutica'", null, ''));
		}	
		return $lcDescripcionFormaFarmaceutica;
	}
	
	function consultarNombrePaciente($tcTipoIde='', $tnIdentificacion=0)
	{
		global $goDb;
		$lcNombrepaciente='';
		if (!empty($tcTipoIde)){
			$laTipoIden=$goDb->select('TIPDOC')->from('RIATI')->where(['DOCUME'=>$tcTipoIde])->get('array');
			$lcTipoIde=$goDb->numRows()>0 ? trim($laTipoIden['TIPDOC']) : '';
			
			if (!empty($lcTipoIde) && !empty($tnIdentificacion)){
				$laNombrePaciente=$goDb
				->select("IFNULL(TRIM(NM1PAC)||' '||TRIM(NM2PAC)||' '||TRIM(AP1PAC)||' '||TRIM(AP2PAC),'') NOMBRE_PACIENTE")->from('RIAPAC')
				->where('TIDPAC', '=', $lcTipoIde)->where('NIDPAC', '=', $tnIdentificacion)->get('array');
				$lcNombrepaciente=$goDb->numRows()>0 ? trim($laNombrePaciente['NOMBRE_PACIENTE']) : '';
			}
		}
		unset($laTipoIden,$laNombrePaciente);
		return $lcNombrepaciente;
	}
	
	function consultarDescripcionCups($tcCodigoCups='')
	{
		global $goDb;
		$lcDescripcionCups='';
		$laDescripcionCups=$goDb->select('DESCUP')->from('RIACUPL0')->where(['CODCUP'=>$tcCodigoCups])->get('array');
		$lcDescripcionCups=$goDb->numRows()>0 ? trim($laDescripcionCups['DESCUP']) : '';
		unset($laDescripcionCups);
		return $lcDescripcionCups;
	}
	
	function consultarDescripcionCausaExterna($tcCausaExterna='')
	{
		global $goDb;
		$lcDescripcionCausaExterna='';
		
		if (!empty($tcCausaExterna)){
			$lcDescripcionCausaExterna=trim($goDb->obtenerTabmae1('DE2TMA', 'CODCEX', "CL2TMA='$tcCausaExterna' AND ESTTMA=''", null, ''));
		}	
		return $lcDescripcionCausaExterna;
	}
	
	function consultarDescripcionModalidadAtencion($tcModalidadAtencion='')
	{
		global $goDb;
		$lcDescripcionModalidadAtencion='';
		
		if (!empty($tcModalidadAtencion)){
			$lcDescripcionModalidadAtencion=trim(strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='MODATEN' AND CL2TMA='$tcModalidadAtencion'", null, '')));
		}	
		return $lcDescripcionModalidadAtencion;
	}
	
	function consultarDescripcionGrupoServicios($tcGrupoServicios='')
	{
		global $goDb;
		$lcDescripcionGrupoServicios='';
		
		if (!empty($tcGrupoServicios)){
			$lcDescripcionGrupoServicios=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='GRUSERV' AND CL2TMA='$tcGrupoServicios' AND ESTTMA=''", null, '')));
		}	
		return $lcDescripcionGrupoServicios;
	}
	
	function consultarDescripcionOtrosServicios($tcOtrosServicios='')
	{
		global $goDb;
		$lcDescripcionOtrosServicios='';
		
		if (!empty($tcOtrosServicios)){
			$lcDescripcionOtrosServicios=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='TIPOTROS' AND CL2TMA='$tcOtrosServicios' AND ESTTMA=''", null, '')));
		}	
		return $lcDescripcionOtrosServicios;
	}
	
	function consultarDescripcionViaIngreso($tcViaIngreso='')
	{
		global $goDb;
		$lcDescripcionViaIngreso='';
		
		if (!empty($tcViaIngreso)){
			$lcDescripcionViaIngreso=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='VIAINGUS' AND CL2TMA='$tcViaIngreso' AND ESTTMA=''", null, '')));
		}	
		return $lcDescripcionViaIngreso;
	}
	
	function consultarDescripcionFinalidad($tcFinalidad='')
	{
		global $goDb;
		$lcDescripcionFinalidad='';
		
		if (!empty($tcFinalidad)){
			$lnFinalidad=intval($tcFinalidad);
			$lcDescripcionFinalidad=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'CODFIN', "OP4TMA=$lnFinalidad", null, '')));
		}	
		return $lcDescripcionFinalidad;
	}
	
	function consultarDescripcionServicio($tcServicio='',$tcTipoServicio='',$tcTipoCups='')
	{
		global $goDb;
		$lcDescripcionServicio='';
		
		if ($tcTipoServicio=='C'){
			if (!empty($tcServicio)){
				$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='CONSEXT' AND CL4TMA='CONSUL'", null, '')));
				
				if (empty($lcDescripcionServicio)){
					$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='INMED' AND CL4TMA='CONSUL'", null, '')));
				}

				if (empty($lcDescripcionServicio)){
					$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='APOYOD' AND CL4TMA='CUPS'", null, '')));
				}
			}	
		}
		
		if ($tcTipoServicio=='P'){
			if (!empty($tcServicio)){
				
				if ($tcTipoCups=='CIRUG.'){
					$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='QUIRUR' AND CL4TMA='CUPS'", null, '')));
				}else{
					$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='APOYOD' AND CL4TMA='CUPS'", null, '')));
				}	

				if (empty($lcDescripcionServicio)){
					$lcDescripcionServicio=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='REFSERV' AND CL2TMA='$tcServicio' AND CL3TMA='CONSEXT' AND CL4TMA='CONSUL'", null, '')));
				}
			}	
		}	
		return $lcDescripcionServicio;
	}
	
	function consultarDescripcionTipoUsuario($tcTipoUsuario='')
	{
		global $goDb;
		$lcDescripcionTipoUsuario='';
		
		if (!empty($tcTipoUsuario)){
			$lcDescripcionTipoUsuario=trim(mb_strtoupper($goDb->obtenerTabmae1('DE2TMA', 'DATING', "CL1TMA='TIPUSUA' AND CL2TMA='$tcTipoUsuario' AND ESTTMA=''", null, '')));
		}	
		return $lcDescripcionTipoUsuario;
	}
	
	function consultarDescripcionPais($tcPais='')
	{
		global $goDb;
		$lcDescripcionPais='';
		
		if (!empty($tcPais)){
			$laDescripcionPais=$goDb->select('DESPAI')->from('COMPAI')->where(['CN1PAI'=>$tcPais])->get('array');
			$lcDescripcionPais=$goDb->numRows()>0 ? trim($laDescripcionPais['DESPAI']) : '';
		}
		unset($laDescripcionPais);		
		return $lcDescripcionPais;
	}
	
	function consultarDescripcionCiudad($tcPais='',$tcCiudad='')
	{
		global $goDb;
		$lcDescripcionCiudad='';

		if (!empty($tcPais)){
			$laDescripcionPais=$goDb->select('CODPAI')->from('COMPAI')->where(['CN1PAI'=>$tcPais])->get('array');
			$lcPaisHomologo=$goDb->numRows()>0 ? trim($laDescripcionPais['CODPAI']) : '';
			if (!empty($lcPaisHomologo)){
				$lcDptoResidencia=intval(substr($tcCiudad, 0, 2));
				$lcCiudadResidencia=intval(substr($tcCiudad, 2, 3));
				
				$laDescripcionCiudad = $goDb
					->select('TRIM(DESCIU) DESCIU')->from('COMCIU')
					->where('PAICIU', '=', $lcPaisHomologo)->where('DEPCIU', '=', $lcDptoResidencia)->where('CODCIU', '=', $lcCiudadResidencia)
					->get('array');
				$lcDescripcionCiudad=$goDb->numRows()>0 ? trim($laDescripcionCiudad['DESCIU']) : '';
			}
		}
		unset($laDescripcionPais,$laDescripcionCiudad);		
		return $lcDescripcionCiudad;
	}
	
	function consultarDescripcionDiagnostico($tcCodigoCie='')
	{
		global $goDb;
		$lcDescripcionDiagnostico='';
		
		if (!empty($tcCodigoCie)){
			$laDescripcionCie=$goDb
			->select('DESRIP')
			->from('RIACIE')
			->where(['ENFRIP'=>$tcCodigoCie])
			->get('array');
			$lcDescripcionDiagnostico=$goDb->numRows()>0 ? trim($laDescripcionCie['DESRIP']) : '';						
		}	
		unset($laDescripcionCie);
		return $lcDescripcionDiagnostico;
	}
	
	function consultarDescripcionTipoDiagnostico($tcTipoDiagnostico='')
	{
		global $goDb;
		$lcDescripcionTipoDiagnostico='';
		$lcTipoCodigo='B'.$tcTipoDiagnostico;
		
		if (!empty($lcTipoCodigo)){
			$laDescripcionTipo=$goDb
			->select('TABDSC')->from('PRMTAB')
			->where('TABTIP', '=', 'TDX')->where('TABCOD', '=', $lcTipoCodigo)->get('array');
			$lcDescripcionTipoDiagnostico=$goDb->numRows()>0 ? trim($laDescripcionTipo['TABDSC']) : '';
		}	
		unset($laDescripcionTipo);
		return $lcDescripcionTipoDiagnostico;
	}
	
	function obtenerMipres($tnIngreso=0, $tcCodigoConsumo='', $tcTipoConsumo='', $tnConsecCita=0)
	{
		global $goDb;
		$laDatoMipres=[];
		$lcIdmipres=$lcNroPrescripcion='';

		if ($tnIngreso>0 && !empty($tcCodigoConsumo) && !empty($tcTipoConsumo)){
			if ($tnConsecCita>0){
				$laIdMipres=$goDb
					->select('NPRJMP, CNSJMP')->from('NPJSMP')
					->where('INGJMP', '=', $tnIngreso)->where('CCOJMP', '=', $tcCodigoConsumo)->where('TCNJMP', '=', $tcTipoConsumo)
					->where('CCIJMP', '=', $tnConsecCita)->where('ESTJMP', '=', 0)->get('array');
				$lcNroPrescripcion=$goDb->numRows()>0 ? trim($laIdMipres['NPRJMP']):'';	
				$lnConsecPrescripcion=$goDb->numRows()>0 ? intval($laIdMipres['CNSJMP']):0;	
			}else{
				$laIdMipres=$goDb
					->select('NPRJMP, CNSJMP')->from('NPJSMP')
					->where('INGJMP', '=', $tnIngreso)->where('CCOJMP', '=', $tcCodigoConsumo)->where('TCNJMP', '=', $tcTipoConsumo)
					->where('ESTJMP', '=', 0)->get('array');
				$lcNroPrescripcion=$goDb->numRows()>0 ? trim($laIdMipres['NPRJMP']):'';	
				$lnConsecPrescripcion=$goDb->numRows()>0 ? intval($laIdMipres['CNSJMP']):0;	
			}	
			
			if (!empty($lcNroPrescripcion) && $lnConsecPrescripcion>0){
				$laIdMipres=$goDb
					->select('IDENTR')->from('MIPRENT')
					->where('NUMPRS', '=', $lcNroPrescripcion)->where('CONTEC', '=', $lnConsecPrescripcion)->get('array');
				$lcIdmipres=$goDb->numRows()>0 ? trim($laIdMipres['IDENTR']):'';	
			}
		}	
		$laDatoMipres=[			
			'numeromipres'=>$lcNroPrescripcion,
			'idmipres'=>$lcIdmipres,
		];
		unset($laIdMipres);
		return $laDatoMipres;
	}
	
	function insertarDatos($tcTabla='',$taDatosTabla=[],$taDatosComunes=[])
	{
		switch (true){
			case $tcTabla=='RIPRES' :
				$lnEstado=0;

				$laData=[
					'IDUNICO' => $taDatosTabla['llaveunica'],
					'IDFACTURA' => $this->cConsecutivoCabecera,
					'INGRESO' => $this->nNumeroIngreso,
					'TIPODATO' =>$taDatosTabla['tipodato'],
					'TIPOCONSUMO' => $taDatosTabla['tipoconsumo'],
					'CONSECUTIVODATO' => $taDatosTabla['consecutivoconsumo'],
					'CONSECUTIVORIPS' => $taDatosComunes['consecutivo'],
					'ESTADO' => '',
					'DESCRIPCION' => json_encode($taDatosComunes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
					'USUARIOCREA' => $this->cUsuarioCreacion,
					'PROGRAMACREA' => $this->cProgramaCreacion,
				];
				$this->oDb->tabla($tcTabla)->insertar($laData);
			break;	

			case $tcTabla=='RIPREE' :
				$laData=[
					'IDFACTURA' => $this->cConsecutivoCabecera,
					'IDUNICO' => $taDatosComunes['llaveunica'],
					'ESTADO' =>  $taDatosComunes['estadorips'],
					'DESCRIPCION' => $taDatosComunes['descripcion'],
					'USUARIOCREA' => $this->cUsuarioCreacion,
					'PROGRAMACREA' => $this->cProgramaCreacion,
				];
				$this->oDb->tabla($tcTabla)->insertar($laData);
			break;	

			case $tcTabla=='RIPREA' :
				$laData=[
					'IDUNICO' => $taDatosTabla['llaveunica'],
					'IDFACTURA' => $this->cConsecutivoCabecera,
					'DESCRIPCION' => $taDatosTabla['error'],
					'USUARIOCREA' => $this->cUsuarioCreacion,
					'PROGRAMACREA' => $this->cProgramaCreacion,
				];
				$this->oDb->tabla($tcTabla)->insertar($laData);
			break;	
		}	
	}
	
	function crearParametrosRips()
	{

		$this->cCodigoHabilitacion=$this->cRutaArchivoRipsJson='';
		$laParametros = $this->oDb
			->select('trim(CL1TMA) CLASIFICACION, trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION, TRIM(OP1TMA) OPCIONAL1')
			->from('TABMAE')
			->where('TIPTMA', '=', 'GENRIPS')
			->in('CL1TMA', ['CODADMI', 'TIPMODER', 'FILARC', 'FILARP', 'FILART'])
			->where('ESTTMA', '=', '')
			->orderBy('CL1TMA, CL2TMA')
			->getAll('array');
		if ($this->oDb->numRows()>0){	
			foreach ($laParametros as $laDatos){
				if ($laDatos['CLASIFICACION']=='CODADMI'){
					if ($laDatos['CODIGO']=='1'){
						$this->cCodigoHabilitacion=$laDatos['DESCRIPCION'];
					}

					if ($laDatos['CODIGO']=='4'){
						$this->cNitShaio=$laDatos['DESCRIPCION'];
					}

					if ($laDatos['CODIGO']=='RUTJSON'){
						$this->cRutaArchivoRipsJson=$laDatos['DESCRIPCION'];
					}

					if ($laDatos['CODIGO']=='GENZIP'){
						$this->cGeneraZip=$laDatos['OPCIONAL1'];
					}

					if ($laDatos['CODIGO']=='FACSETT'){
						$this->nValorParaPruebas=intval($laDatos['DESCRIPCION']);
					}

					if ($laDatos['CODIGO']=='ESTARCJS'){
						$lcEstadosCrearJson=$laDatos['DESCRIPCION'];
						$this->aEstadosCrearJson=explode(',', str_replace("'", '', $lcEstadosCrearJson));
					} 
				}
				
				if ($laDatos['CLASIFICACION']=='TIPMODER'){
					if ($laDatos['CODIGO']=='CONSULTA'){
						$this->aReacaudoConsulta=explode(',', $laDatos['DESCRIPCION']);
					}	
				
					if ($laDatos['CODIGO']=='PROCEDIM'){
						$this->aReacaudoProcedimiento=explode(',', $laDatos['DESCRIPCION']);
					}	

					if ($laDatos['CODIGO']=='MEDICAME'){
						$this->aReacaudoMedicamento=explode(',', $laDatos['DESCRIPCION']);
					}	

					if ($laDatos['CODIGO']=='OTROSSER'){
						$this->aReacaudoOtrosServicios=explode(',', $laDatos['DESCRIPCION']);
					}	
				}	

				if ($laDatos['CLASIFICACION']=='FILARC'){
					$this->aProcedimentosConsulta=explode(',', $laDatos['DESCRIPCION']);
				}

				if ($laDatos['CLASIFICACION']=='FILARP'){
					$this->aProcedimentosProcedimientos=explode(',', $laDatos['DESCRIPCION']);
				}

				if ($laDatos['CLASIFICACION']=='FILART'){
					$this->aProcedimentosOtrosServicios=explode(',', $laDatos['DESCRIPCION']);
				}
			}	
		}	
		unset($laParametros);
	}
	
	public function crearArchivoRips($tnFactura=0,$tcTipoDocumento='',$tnNota=0)
	{
		
		global $goDb;
		$lcArchivoGenera='';
		$this->nNumeroFacturaJson=0;
		$lcEstadoRips='02';
		$this->cTipoDocumento=$tcTipoDocumento;
		$lcTextoProceso='Crear archivo json';
		$this->cUsuarioCreacion = 'RIPSUSER';
		$this->cProgramaCreacion = 'RIPSARCJS';
		$laDatosFacturacion=$laDatosUsuarios=$laDatosOtrosServicios=$laDatosConsultas=$laDatosMedicamentos=$laDatosProcedimientos=[];
		$laDatosUrgencias=$laDatosHospitalizacion=$laDatosRecienNacidos=[];

		$laRipsFactura = $goDb
			->select('TRIM(A.IDFACTURA) IDFACTURA, TRIM(B.TIPODATO) TIPO, B.CONSECUTIVODATO CONSECUTIVO, TRIM(B.DESCRIPCION) DESCRIPCION')
			->from('RIPREC AS A')
			->leftJoin("RIPRES B", "A.IDFACTURA=B.IDFACTURA", null)
			->where('A.NUMEROFACTURA', '=', $tnFactura)
			->where('A.TIPORIPS', '=', $tcTipoDocumento)
			->where('B.ESTADO', '=', '')
			->orderBy('B.TIPODATO, B.CONSECUTIVORIPS')
			->getAll('array');
			if($goDb->numRows()>0){
				foreach($laRipsFactura as $laDatos){
					$lcTipoRips=$laDatos['TIPO'];
					$this->cConsecutivoCabecera=trim($laDatos['IDFACTURA']);
					$laDescripcion=json_decode($laDatos['DESCRIPCION'], true);
					if ($lcTipoRips=='AF'){	$laDatosFacturacion=$laDescripcion;	}	
					if ($lcTipoRips=='US'){ $laDatosUsuarios=$laDescripcion; }	
					if ($lcTipoRips=='AC'){ $laDatosConsultas[]=$laDescripcion; }
					if ($lcTipoRips=='AT'){ $laDatosOtrosServicios[]=$laDescripcion; }
					if ($lcTipoRips=='AM'){ $laDatosMedicamentos[]=$laDescripcion; }
					if ($lcTipoRips=='AP'){ $laDatosProcedimientos[]=$laDescripcion; }
					if ($lcTipoRips=='AU'){ $laDatosUrgencias[]=$laDescripcion; }
					if ($lcTipoRips=='AH'){ $laDatosHospitalizacion[]=$laDescripcion; }
					if ($lcTipoRips=='AN'){ $laDatosRecienNacidos[]=$laDescripcion; }
				}
				$lcDatosDelToken ="";
				$laDatosFinales=$laDatosFacturacion;

				$lnNumUsuario = -1;
				if (!empty($laDatosUsuarios)) {
					$lnNumUsuario++;
					$laDatosFinales['usuarios'][$lnNumUsuario]=$laDatosUsuarios;

					if (!empty($laDatosConsultas)) { 
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['consultas']=$laDatosConsultas;
					}
					
					if (!empty($laDatosMedicamentos)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['medicamentos']=$laDatosMedicamentos;
					}
					
					if (!empty($laDatosProcedimientos)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['procedimientos']=$laDatosProcedimientos;
					}
	
					if (!empty($laDatosUrgencias)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['urgencias']=$laDatosUrgencias;
					}
	
					if (!empty($laDatosHospitalizacion)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['hospitalizacion']=$laDatosHospitalizacion;
					}
	
					if (!empty($laDatosRecienNacidos)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['recienNacidos']=$laDatosRecienNacidos;
					}
					
					if (!empty($laDatosOtrosServicios)) {
						$laDatosFinales['usuarios'][$lnNumUsuario]['servicios']['otrosServicios']=$laDatosOtrosServicios;
					}
				}
				$lcArchivoGenera = json_encode($laDatosFinales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
				$this->actualizarCabeceraRips($lcEstadoRips);
				$this->crearTrazabilidadRips($lcTextoProceso,$lcEstadoRips);
			}

			if (!empty($lcArchivoGenera)){
				$this->nNumeroFacturaJson=$tnFactura;
				$this->crearArchivoJsonRespaldo($lcArchivoGenera, false);
			}	
	}

	function crearArchivoJsonRespaldo($tcMensaje, $tbEcho=false)
	{
		$loZip = new ZipArchive();

		$lcRuta = $this->cRutaArchivoRipsJson.date('Ym');
		if (!is_dir($lcRuta)) { mkDir($lcRuta, 0777, true); }

		$lcFilelog = $lcRuta .'/'.$this->nNumeroFacturaJson.'_'.$this->cTipoDocumento.'_'.date('Ymdhis').'.json';
		
		$lcMensaje= $tcMensaje;
		$lnFile = fOpen($lcFilelog, 'a');
		fPuts($lnFile, $lcMensaje);
		fClose($lnFile);
		if ($tbEcho) { echo $lcMensaje; }

		if ($this->cGeneraZip=='S'){
			$nombrearchivo=$lcRuta .'/'.$this->nNumeroFacturaJson.'_'.$this->cTipoDocumento.'_'.date('Ymdhis').".zip";

			if ($loZip->open($nombrearchivo, ZipArchive::CREATE) === TRUE) {
				$loZip->addFile($lcFilelog,
				$this->nNumeroFacturaJson.'_'.$this->cTipoDocumento	.'_'.date('Ymdhis').".json");
				$loZip->close();
			}
			unlink($lcFilelog);
		}
	}


}
