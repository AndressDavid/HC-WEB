<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';
require_once __DIR__ . '/class.Diagnostico.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.MailEnviar.php';
require_once __DIR__ . '/class.NoPosFunciones.php';
require_once __DIR__ . '/class.Rips.php';
require_once __DIR__ . '/class.NutricionConsulta.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\Historia_Clinica_Ingreso;
use NUCLEO\ParametrosConsulta;
use NUCLEO\Diagnostico;
use NUCLEO\Consecutivos;
use NUCLEO\MailEnviar;
use NUCLEO\NoPosFunciones;
use NUCLEO\Rips;
use NUCLEO\NutricionConsulta;

class Epicrisis
{
	protected $cFecEpi = '';
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $cRegMed = '';
	protected $cCodPro = '';
	protected $cEstadoSalida = '';
	protected $cEspecialidad = '';
	protected $nConCit = 0;
	protected $nConCon = 0;
	protected $nConEpi = 0;
	protected $aRIAEPI = [];
	protected $aRIAEPIA = [];
	protected $aIngreso = [];
	protected $oHcIng = null;
	protected $oDb = null;
	protected $cEmailPac = '';
	protected $aDatOrdenAmb = [];

	protected $aError = [
				'Mensaje' => '',
				'Objeto' => '',
				'Valido' => true,
			];

	protected $aDiagnosticos = [
				'Principal' => '',
				'Relacionado1' => '',
				'Relacionado2' => '',
				'Relacionado3' => '',
			];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->oHcIng = new Historia_Clinica_Ingreso();
	}

	public function verificarEPI($taDatos=[])
	{
		$this->IniciaDatosIngreso($taDatos['Ingreso']);
		$llRetorno = true;

		$llRetorno = $this->verificarAnalisis($taDatos['Analisis']??[]);
		if(!$llRetorno){
			return $this->aError;
		}

		$llRetorno = $this->verificarDiagnosticos($taDatos['Diagnostico']);
		if(!$llRetorno){
			return $this->aError;
		}

		$llRetorno = $this->verificarEgreso($taDatos['Egreso']);
		if(!$llRetorno){
			return $this->aError;
		}

		if (isset($taDatos['Ambulatorio'])){
			$loAmbulatorio = new DatosAmbulatorios();
			$loAmbulatorio->setIngreso($this->aIngreso);
			$loAmbulatorio->setDxPrincipal($taDatos['Diagnostico'][0]['CODIGO']);
			$this->aError = $loAmbulatorio->validacion($taDatos['Ambulatorio']);
		}

		if (!$this->aError['Valido']) {
			return $this->aError;
		}

		return $this->aError;
	}

	public function IniciaDatosIngreso($tnIngreso=0)
	{
		$this->aIngreso = $this->oHcIng->datosIngreso($tnIngreso);
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'EPIPPALWEB';
		$this->cEspecialidad  = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
		$this->cRegMed  = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():'');
	}

	function verificarAnalisis($taDatos=[])
	{
		if(count($taDatos)==0){
			$this->aError = [
				'Mensaje'=>'No existen datos de Analisis para epicrisis ',
				'Objeto'=>'edtAnalisis',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		if(empty(trim($taDatos['Analisis']))){

			$this->aError = [
				'Mensaje'=>'Analisis para epicrisis es obligaorio ',
				'Objeto'=>'edtAnalisis',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Fibrilación Auricular
		$lnCantidad = 0;
		for ($lnIndice=1; $lnIndice<=8; $lnIndice++){

			$lcTemp = str_pad(strval($lnIndice),2,'0',STR_PAD_LEFT);
			$lcRespuesta = 'Respuesta'.$lcTemp;

			if($taDatos[$lcRespuesta]=='Si' || $taDatos[$lcRespuesta]=='No'){
				$lnCantidad++;
			}
		}

		if($lnCantidad>0 && $lnCantidad<8){

			for ($lnIndice=1; $lnIndice<=8; $lnIndice++){

				$lcTemp = str_pad(strval($lnIndice),2,'0',STR_PAD_LEFT);
				$lcObjeto = 'selRespuesta'.$lcTemp;
				$lcRespuesta = 'Respuesta'.$lcTemp;

				if($taDatos[$lcRespuesta]!='Si' && $taDatos[$lcRespuesta]!='No'){

					$lcMensaje = empty(trim($taDatos[$lcRespuesta]))?'La escala de Fibrilación Auricular debe ser diligenciada en su totalidad':'Error en la respuesta '.$lnIndice.' de la escala Fibrilación Auricular';
					$this->aError = [
						'Mensaje'=>$lcMensaje,
						'Objeto'=>$lcObjeto,
						'Valido'=>false,
					];
					break;
				}
			}
		}
		return $this->aError['Valido'];
	}

	function verificarDiagnosticos($taDatos=[])
	{
		$loDiagnostico = new Diagnostico();
		$this->aError = $loDiagnostico->validacion($taDatos, $this->aIngreso['cCodVia'], 'F');
		return $this->aError['Valido'];
	}

	function verificarEgreso($taDatos=[])
	{
		$this->cFecEpi = substr($taDatos['FechaEgreso'],0,4).substr($taDatos['FechaEgreso'],5,2).substr($taDatos['FechaEgreso'],8,2);

		//Verificar Dx Fallece si trae la información
		if(!empty(trim($taDatos['cCodigoDxFallece']))){

			$lcCodigoDx = mb_substr($taDatos['cCodigoDxFallece'], 0, 5);
			$loDiagnostico = new Diagnostico();
			$this->aError['Valido'] = $loDiagnostico->buscarDX($lcCodigoDx);

			if(!$this->aError['Valido']){
				$this->aError = [
					'Mensaje'=>'Error en el Diagnostico Fallece Digitado. Revise por favor!',
					'Objeto'=>'buscarDxFallece',
					'Valido'=>false
				];
				return $this->aError['Valido'];
			}

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
		}

		// Verifica Condiciones generales
		if(empty(trim($taDatos['Condicion']))){
			$this->aError = [
				'Mensaje'=>'Condiciones Generales obligatorio. Revise por favor!',
				'Objeto'=>'edtCondiciones',
				'Valido'=>false
			];
			return $this->aError['Valido'];
		}

		// Verifica Plan de manejo
		if(empty(trim($taDatos['Manejo']))){
			$this->aError = [
				'Mensaje'=>'Plan de Manejo obligatorio. Revise por favor!',
				'Objeto'=>'edtManejo',
				'Valido'=>false
			];
			return $this->aError['Valido'];
		}

		// Verifica Fecha de Egreso
		if(empty(trim($taDatos['FechaEgreso']))){
			$this->aError = [
				'Mensaje'=>'Fecha de Egreso obligatorio. Revise por favor!',
				'Objeto'=>'lcFechaEgreso',
				'Valido'=>false
			];
			return $this->aError['Valido'];
		}

		// Validar estado
		$loObjHC = new ParametrosConsulta();
		$loObjHC->ObtenerEstadoEpicrisis();
		$laResultado = $loObjHC->estadoEpicrisis($taDatos['Estado']);
		if(empty($laResultado)){
			$this->aError = [
				'Mensaje'=>'No existe Estado de Salida en la base de datos',
				'Objeto'=>'selEstado',
				'Valido'=>false,
			];
			return $this->aError['Valido'];
		}

		// Valida Fecha y Hora Fallece
		if($taDatos['Estado'] =='03' || $taDatos['Estado'] =='04' || $taDatos['Estado'] =='06'){
			$lcFechaFallece =  str_replace("-", '', $taDatos['FechaFallece']);
			if(empty($lcFechaFallece)){
				$this->aError = [
					'Mensaje'=>'Error en la Fecha Fallece',
					'Objeto'=>'FechaFallece',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}

			$lcHoraFallece = str_replace(":", '', $taDatos['HoraFallece']);
			if(empty($lcHoraFallece)){
				$this->aError = [
					'Mensaje'=>'Error en la Hora Fallece',
					'Objeto'=>'HoraFallece',
					'Valido'=>false,
				];
				return $this->aError['Valido'];
			}
		}

		// Verifica Condición destino Egreso
		if(empty(trim($taDatos['CondicionEgreso']))){
			$this->aError = [
				'Mensaje'=>'Condición destino Egreso obligatorio. Revise por favor!',
				'Objeto'=>'selCondicionEgreso',
				'Valido'=>false
			];
			return $this->aError['Valido'];
		}

		if(!empty($taDatos['CondicionEgreso'])){
			$lcEstado=($taDatos['Estado'] =='03' || $taDatos['Estado'] =='04' || $taDatos['Estado'] =='06')?'F':'';
			$loObjHC->ObtenerCondicionDestinoEgreso($lcEstado);
			$laResultado = $loObjHC->tipoCondicionDestinoEgreso($taDatos['CondicionEgreso']);

			if(empty($laResultado)){
				$this->aError = [
					'Mensaje'=>'No existe tipo condición destino egreso en la base de datos',
					'Objeto'=>'selCondicionEgreso',
					'Valido'=>false,
				];
			}
		}

		return $this->aError['Valido'];
	}

	public function GuardarEPI($taDatos=[])
	{
		$this->IniciaDatosIngreso($taDatos['Ingreso']);
		$this->obtenerDiagnosticos($taDatos['Diagnostico']);
		$this->OrganizarDatosEPI($taDatos);
		$this->guardarDatosEPI($taDatos);

		// Salida pacientes nutrición
		$loNutricion = new NutricionConsulta();
		$laNutSal = $loNutricion->generarDatosMedirest('INGRESO', $taDatos['Ingreso'], true);
		if (isset($laNutSal['success']) && $laNutSal['success']) {
			$laNutEnv = $loNutricion->enviarDatosMedirest($laNutSal['datos']);
		}

		if (in_array($taDatos['Egreso']['Estado'],['03','04','06'])) {
			$this->enviarMailFalleceNoPOS();
		}

		// Datos para consultar la EPICRISIS
		$laDataEPI = [
			'nIngreso'		=> $taDatos['Ingreso'],
			'cTipDocPac'	=> $this->aIngreso['cTipId'],
			'nNumDocPac'	=> $this->aIngreso['nNumId'],
			'cRegMedico'	=> $this->cRegMed,
			'cTipoDocum'	=> '4100',
			'cTipoProgr'	=> 'EPI002',
			'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($this->cFecCre.$this->cHorCre)),
			'nConsecCita'	=> '',
			'nConsecCons'	=> $this->nConCon,
			'nConsecEvol'	=> '',
			'nConsecDoc'	=> $this->nConEpi,
			'cCUP'			=> '',
			'cCodVia'		=> $this->aIngreso['cCodVia'],
			'cSecHab'		=> $this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
		];
		$this->enviarEmailPaciente($laDataEPI);

		$this->aError['dataEPI'] = $laDataEPI;
		if (!empty($this->aDatOrdenAmb)) {
			$this->aError['dataOA'] = $this->aDatOrdenAmb['dataOA'];
		}

		return $this->aError;
	}

	public function OrganizarDatosEPI($taDatos=[])
	{
		//pendiente validacion
		$this->cCodPro = '';
		$taDatos['nConCita']=$taDatos['nConCita']??0;

		// Calcular consecutivos de consulta, cita y epicrisis
		$this->nConCon = Consecutivos::fCalcularConsecutivoConsulta($this->aIngreso, $this->cPrgCre, false, $this->cCodPro);
		$this->nConCit = empty($taDatos['nConCita'])?(Consecutivos::fCalcularConsecutivoCita($this->aIngreso, $this->cPrgCre)):$taDatos['nConCita'];
		$this->nConEpi = (Consecutivos::fCalcularConsecutivoEPI($this->aIngreso['nIngreso'])) + 1;
		$lnLongitud = 220;

		// Organiza datos para analisis
		if(!empty(trim($taDatos['Analisis']['Analisis']))){
			$lcTabla = 'RIAEPIA';
			$lcDescrip = trim($taDatos['Analisis']['Analisis']);
			$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescrip, 1);
		}

		// Organiza datos para Interpretación de examenes 
		if(!empty(trim($taDatos['Analisis']['Interpreta']))){
			$lnLinea = 6000;
			$lcTabla = 'RIAEPI';
			$lcDescrip = trim( $taDatos['Analisis']['Interpreta']);
			$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescrip, 1, $lnLinea);
		}

		// Datos Fibrilacion Auricular
		$lnCantidad=0;
		for ($lnIndice=1; $lnIndice<=8; $lnIndice++){

			$lcTemp = str_pad(strval($lnIndice),2,'0',STR_PAD_LEFT);
			$lcRespuesta = 'Respuesta'.$lcTemp;

			if($taDatos['Analisis'][$lcRespuesta]=='Si' || $taDatos['Analisis'][$lcRespuesta]=='No'){
				$lnCantidad++;
			}
		}

		if($lnCantidad==8){

			$lcTabla = 'RIAEPI';
			$lnIndice = 0;

			// Fibrilación Auricular
			$lnLinea = 5000;
			$lctexto = '* * * * FIBRILACION AURICULAR * * * *';
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Registro en BLANCO
			$lnLinea = 5001;
			$lctexto = '';
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Fibrilación Auricular POP
			$lcTAB = str_repeat(chr(9),4);
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta01']));
			$lnLinea = $lcResp=='SI'?5002:5003;
			$lctexto = 'FIBRILACION AURICULAR POP :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Beta Bloqueo PREOP
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta02']));
			$lnLinea = $lcResp=='SI'?5004:5005;
			$lctexto = 'BETA BLOQUEO PREOP :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Hidrocortisona
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta03']));
			$lnLinea = $lcResp=='SI'?5006:5007;
			$lctexto = 'HIDROCORTISONA :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Amiodarona
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta04']));
			$lnLinea = $lcResp=='SI'?5008:5009;
			$lctexto = 'HIDROCORTISONA :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Cardioversión Eléctrica
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta05']));
			$lnLinea = $lcResp=='SI'?5010:5011;
			$lctexto = 'CARDIOVERSIÓN ELECTRICA :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Aticuagulación
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta06']));
			$lnLinea = $lcResp=='SI'?5012:5013;
			$lctexto = 'ANTICOAGULACIÓN :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			//Beta bloqueo POP
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta07']));
			$lnLinea = $lcResp=='SI'?5014:5015;
			$lctexto = 'BETA BLOQUEO POP :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

			// Egreso con Fibrilación Auricular
			$lcResp = strtoupper(trim($taDatos['Analisis']['Respuesta08']));
			$lnLinea = $lcResp=='SI'?5016:5017;
			$lctexto = 'EGRESO CON FIBRILACION AURICULAR :  ' . $lcTAB . $lcResp ;
			$this->InsertarRegistro($lcTabla, $lctexto, $lnIndice, $lnLinea);

		}

		// Condiciones Generales
		$lcTabla = 'RIAEPI';
		if(!empty(trim($taDatos['Egreso']['Condicion']))){
			$lcDescrip = trim($taDatos['Egreso']['Condicion']);
			$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescrip, 0, 2101);
		}

		// Plan de manejo
		if(!empty(trim($taDatos['Egreso']['Manejo']))){
			$lcDescrip = trim($taDatos['Egreso']['Manejo']);
			$this->InsertarDescripcion($lcTabla, $lnLongitud, $lcDescrip, 0, 2201);
		}

		// Guarda especialidad médico que realiza la epicrisis
		$lctexto = trim($this->cRegMed . '          ' . 'Especialidad: ' . $this->cEspecialidad);
		$lnLinea = 9000;
		$this->InsertarRegistro($lcTabla, $lctexto, 0, $lnLinea);

		//	Guarda diagnósticos y estado salida
		if(!empty(trim($taDatos['Egreso']['Estado']))){
			$lcPrincipalEgreso = $this->aDiagnosticos['Principal'];
			$lcRelacionado1Egreso = $this->aDiagnosticos['Relacionado1'];
			$lcRelacionado2Egreso = $this->aDiagnosticos['Relacionado2'];
			$lcRelacionado3Egreso = $this->aDiagnosticos['Relacionado3'];
			$this->cEstadoSalida = trim($taDatos['Egreso']['Estado']);

			$lctexto = '{"Ingreso":{"DxPr":"","DxR1":"","DxR2":"","DxR3":""},'
			.'"Egreso":{"DxPr":"'.$lcPrincipalEgreso.'",'
			.'"DxR1":"'.$lcRelacionado1Egreso.'",'
			.'"DxR2":"'.$lcRelacionado2Egreso.'",'
			.'"DxR3":"'.$lcRelacionado3Egreso.'","DxCp":""},'
			.'"EstSali":"'.$this->cEstadoSalida.'"}';
			$lnLinea = 9001;
			$this->InsertarRegistro($lcTabla, $lctexto, 0, $lnLinea);

			$lcMuerteEncefalica=isset($taDatos['Egreso']['MuerteEncefalica']) ? trim($taDatos['Egreso']['MuerteEncefalica']) : '';
			if (!empty($lcMuerteEncefalica)){
				$lnLinea = 9010;
				$this->InsertarRegistro($lcTabla, $lcMuerteEncefalica, 0, $lnLinea);
			}

			$lcCondicionEgreso=isset($taDatos['Egreso']['CondicionEgreso']) ? trim($taDatos['Egreso']['CondicionEgreso']) : '';
			if (!empty($lcCondicionEgreso)){
				$lnLinea = 9020;
				$this->InsertarRegistro($lcTabla, $lcCondicionEgreso, 0, $lnLinea);
			}
		}
	}

	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnIndice=0, $tnLinea=1)
	{
		$laChar = AplicacionFunciones::mb_str_split(trim($tcTexto),$tnLongitud);

		if(is_array($laChar)){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnIndice, $tnLinea);
					$tnLinea++;
				}
			}
		}
	}

	function InsertarRegistro($tcTabla='', $tcDescrip='', $tnIndice=0, $tnLinea=1, $tcTipoEvol='')
	{
		switch (true){

			case $tcTabla=='RIAEPI' :

				$this->aRIAEPI[]=[
					'TIDEPI'=>$this->aIngreso['cTipId'],
					'NIDEPI'=>$this->aIngreso['nNumId'],
					'NINEPI'=>$this->aIngreso['nIngreso'],
					'CCNEPI'=>$this->nConEpi,
					'CONEPI'=>$this->nConCon,
					'COSEPI'=>$tnLinea,
					'DESEPI'=>$tcDescrip,
					'USREPI'=>$this->cUsuCre,
					'PGMEPI'=>$this->cPrgCre,
					'FECEPI'=>$this->cFecCre,
					'HOREPI'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='RIAEPIA' :

				$this->aRIAEPIA[]=[
					'TIDEPA'=>$this->aIngreso['cTipId'],
					'NIDEPA'=>$this->aIngreso['nNumId'],
					'NINEPA'=>$this->aIngreso['nIngreso'],
					'CCNEPA'=>$this->nConEpi,
					'CONEPA'=>$this->nConCon,
					'COLEPA'=>$tnLinea,
					'DESEPA'=>$tcDescrip,
					'USREPA'=>$this->cUsuCre,
					'PGMEPA'=>$this->cPrgCre,
					'FECEPA'=>$this->cFecCre,
					'HOREPA'=>$this->cHorCre,
				];
				break;

		}

	}

	function obtenerDiagnosticos($taDatos=[])
	{
		$lcRelacionado1 = $lcRelacionado2 = $lcRelacionado3 = '';
		foreach ($taDatos as $obtenerDiagnostico){
			$lcTipoValidar = trim($obtenerDiagnostico['CODTIPO']);

			if ($lcTipoValidar=='1'){
				$lcPrincipal= strtoupper($obtenerDiagnostico['CODIGO']);
			}

			if ($lcTipoValidar=='2'){
				if (empty($lcRelacionado1)){
					$lcRelacionado1= strtoupper($obtenerDiagnostico['CODIGO']);
					continue;
				}
				if (empty($lcRelacionado2)){
					$lcRelacionado2= strtoupper($obtenerDiagnostico['CODIGO']);
					continue;
				}
				if (empty($lcRelacionado3)){
					$lcRelacionado3= strtoupper($obtenerDiagnostico['CODIGO']);
					continue;
				}
			}
		}
		$this->aDiagnosticos = [
					'Principal'=>$lcPrincipal,
					'Relacionado1'=>$lcRelacionado1,
					'Relacionado2'=>$lcRelacionado2,
					'Relacionado3'=>$lcRelacionado3,
				];

		return $this->aDiagnosticos;
	}

	function guardarDatosEPI($taDatos=[])
	{
		// Insertar registros a la tabla de AS400 RIAEPI
		$lcTabla = 'RIAEPI';
		foreach($this->aRIAEPI  as $laRIAEPI){
			$llResultado = $this->oDb->from($lcTabla)->insertar($laRIAEPI);
		}

		// Insertar registros a la tabla de AS400 RIAEPIA
		$lcTabla = 'RIAEPIA';
		foreach($this->aRIAEPIA  as $laRIAEPIA){
			$llResultado = $this->oDb->from($lcTabla)->insertar($laRIAEPIA);
		}

		//	GUARDAR DATOS DIAGNOSTICO
		if(!empty($taDatos['Diagnostico'])){
			$lcTipoDocVia = 'RF';
			$lcDxFallece = mb_substr($taDatos['Egreso']['cCodigoDxFallece']??'', 0, 5);

			$laDatosFallece = [
				'HorFallece' => $taDatos['Egreso']['HoraFallece']??'',
				'FecFallece' => $taDatos['Egreso']['FechaFallece']??'',
				'CieFallece' => $lcDxFallece,
				];
			$loObjHC = new Diagnostico();
			$laResultado = $loObjHC->guardarDiagnostico(
				$taDatos['Diagnostico'],
				$this->aIngreso['nIngreso'],
				$this->nConEpi,
				$this->aIngreso['cTipId'],
				$this->aIngreso['nNumId'],
				$this->aIngreso['nEntidad'],
				$lcTipoDocVia,
				'',
				'',
				$this->cUsuCre,
				$this->cPrgCre,
				$this->cFecCre,
				$this->cHorCre,
				$this->cEstadoSalida,
				$laDatosFallece);
		}

		if (isset($taDatos['Ambulatorio'])){
			if(!empty($taDatos['Ambulatorio'])){
				$lcCiePrincipal = $this->aDiagnosticos['Principal'];
				$lcCodVia = $this->aIngreso['cCodVia'] ;
				$lcPlanIngreso = $this->aIngreso['cPlan'] ;
				$loAmbulatorio = new DatosAmbulatorios();
				$this->aDatOrdenAmb = $loAmbulatorio->GuardarOrdenesAmbulatorias($taDatos['Ambulatorio'],$this->aIngreso,$this->nConCon,$this->nConCit,$lcCiePrincipal,0,$this->cUsuCre, $this->cPrgCre,$this->cFecCre, $this->cHorCre, $this->cRegMed);
			}
		}
		$this->ActualizaDatosEPI();
	}

	function ActualizaDatosEPI()
	{
		$lnFechaIngreso = 0;
		$lcEstadoIngreso = '';

		$laTempEPI = $this->oDb
				->select('FEIING, ESTING')
				->from('RIAINGL15')
				->where('NIGING', '=', $this->aIngreso['nIngreso'])
				->get('array');

			if(is_array($laTempEPI)){
				if(count($laTempEPI)>0){
					$lnFechaIngreso  = $laTempEPI['FEIING'];
					$lcEstadoIngreso = trim($laTempEPI['ESTING']);
				}
			}

		// Consulta si ya existe registro
		$llCrear = true ;
		$laTempEPI = $this->oDb
			->select('COUNT(*) CONTAR')
				->from('RIAINGD')
				->where(['NIGIND'=>$this->aIngreso['nIngreso'],
						 'FECIND'=>$this->cFecCre,
						 'HORIND'=>$this->cHorCre,
			   			])
				->get('array');

		if(is_array($laTempEPI)){
			if($laTempEPI['CONTAR']>0){
				$llCrear = false ;
			}
		}

		if($llCrear == true){
			// Crea registro ingreso detalle
			$lcTabla = 'RIAINGD';
			$laDatos = [
				'TIDIND'=>$this->aIngreso['cTipId'],
				'NIDIND'=>$this->aIngreso['nNumId'],
				'NIGIND'=>$this->aIngreso['nIngreso'],
				'VIAIND'=>$this->aIngreso['cCodVia'],
				'FEIIND'=>$lnFechaIngreso,
				'FEEIND'=>$this->cFecEpi,
				'HREIND'=>$this->cHorCre,
				'ESTIND'=>$lcEstadoIngreso,
				'USRIND'=>$this->cUsuCre,
				'PGMIND'=>$this->cPrgCre,
				'FECIND'=>$this->cFecCre,
				'HORIND'=>$this->cHorCre,
			];
			$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);
		}

		// Actualiza archivo CONINC
		$laTempEPI = $this->oDb
			->select('COUNT(*) CONTAR')
				->from('CONINC')
				->where('INGINC', '=', $this->aIngreso['nIngreso'])
				->get('array');

		if(is_array($laTempEPI)){

			if($laTempEPI['CONTAR']>0){

				// Actualiza registro
				$lcTabla = 'CONINC';
				$laDatos = [
					'FEPINC'=>$this->cFecEpi,
					'UEPINC'=>$this->cUsuCre,
					'UMOINC'=>$this->cUsuCre,
					'PMOINC'=>$this->cPrgCre,
					'FMOINC'=>$this->cFecCre,
					'HMOINC'=>$this->cHorCre,
					];

				$llResultado = $this->oDb->from($lcTabla)->where(['INGINC'=>$this->aIngreso['nIngreso']])->actualizar($laDatos);

			}else{

				// Crea Registro
				$lcTabla = 'CONINC';
				$laDatos = [
						'INGINC'=>$this->aIngreso['nIngreso'],
						'FEPINC'=>$this->cFecEpi,
						'UEPINC'=>$this->cUsuCre,
						'USRINC'=>$this->cUsuCre,
						'PGMINC'=>$this->cPrgCre,
						'FECINC'=>$this->cFecCre,
						'HORINC'=>$this->cHorCre,
						];
				$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);

				//Crea detalle
				$lcAccion= '30';
				$lcTabla = 'CONIND';
				$laDatos = [
						'INGIND'=>$this->aIngreso['nIngreso'],
						'ACCIND'=>$lcAccion,
						'UACIND'=>$this->cUsuCre,
						'USRIND'=>$this->cUsuCre,
						'PGMIND'=>$this->cPrgCre,
						'FECIND'=>$this->cFecCre,
						'HORIND'=>$this->cHorCre,
						];
				$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);

			}
		}

		$lcEspSal='';
		$laTempEPI = $this->oDb
				->select('CODRGM')
				->from('RIARGMN')
				->where('REGMED', '=', $this->cRegMed)
				->get('array');

		if(is_array($laTempEPI)){
			if(count($laTempEPI)>0){
				$lcEspSal = $laTempEPI['CODRGM'];
			}
		}

		$lcTabla = 'RIAEPHD';
		$laDatos = [
				'NINEPD'=>$this->aIngreso['nIngreso'],
				'CCNEPD'=>$this->nConEpi,
				'CLNEPD'=>1,
				'ESTEPD'=>'R',
				'RMEEPD'=>$this->cRegMed,
				'FEEEPD'=>$this->cFecEpi,
				'HRREPD'=>$this->cHorCre,
				'OP1EPD'=>$lcEspSal,
				'USREPD'=>$this->cUsuCre,
				'PGMEPD'=>$this->cPrgCre,
				'FECEPD'=>$this->cFecCre,
				'HOREPD'=>$this->cHorCre,
				];

		$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);

		// Guarda datos en archivo epicrisis RIAEPH
		$laTempEPI = $this->oDb
			->select('COUNT(*) CONTAR')
				->from('RIAEPH')
				->where([
					'TIDEPH'=>$this->aIngreso['cTipId'],
					'NIDEPH'=>$this->aIngreso['nNumId'],
					'NINEPH'=>$this->aIngreso['nIngreso'],
					'CCNEPH'=>$this->nConEpi,
					'CONEPH'=>$this->nConCon,
				])
				->get('array');

		if(!is_array($laTempEPI)){$laTempEPI=[];}
		if(is_array($laTempEPI)){
			$lnEstEpi = 3;
			if($laTempEPI['CONTAR']>0){

				// Actualiza registro
				$lcTabla = 'RIAEPH';
				$laDatos = [
					'RMEEPH'=>$this->cRegMed,
					'RMSEPH'=>$this->cRegMed,
					'ESTEPH'=>$lnEstEpi,
					'UMOEPH'=>$this->cUsuCre,
					'PMOEPH'=>$this->cPrgCre,
					'FMOEPH'=>$this->cFecCre,
					'HMOEPH'=>$this->cHorCre,
					];
				$llResultado = $this->oDb
					->from($lcTabla)
					->where([
						'TIDEPH'=>$this->aIngreso['cTipId'],
						'NIDEPH'=>$this->aIngreso['nNumId'],
						'NINEPH'=>$this->aIngreso['nIngreso'],
						'CCNEPH'=>$this->nConEpi,
						'CONEPH'=>$this->nConCon,])
					->actualizar($laDatos);
			}else{

				//Crea registro
				$lcTabla = 'RIAEPH';
				$laDatos = [
						'TIDEPH'=>$this->aIngreso['cTipId'],
						'NIDEPH'=>$this->aIngreso['nNumId'],
						'NINEPH'=>$this->aIngreso['nIngreso'],
						'CCNEPH'=>$this->nConEpi,
						'CONEPH'=>$this->nConCon,
						'RMEEPH'=>$this->cRegMed,
						'FEEEPH'=>$this->cFecEpi,
						'HRREPH'=>$this->cHorCre,
						'ESTEPH'=>$lnEstEpi,
						'RMSEPH'=>$this->cRegMed,
						'USREPH'=>$this->cUsuCre,
						'PGMEPH'=>$this->cPrgCre,
						'FECEPH'=>$this->cFecCre,
						'HOREPH'=>$this->cHorCre,
						];
				$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);
			}
		}

		// Actualizar estado de las habitaciones
		$laTempEPI = $this->oDb
			->select('SECHAB, NUMHAB')
				->from('FACHAB')
				->where('INGHAB', '=', $this->aIngreso['nIngreso'])
				->getAll('array');

		if(is_array($laTempEPI)){
			if(count($laTempEPI)>0){

				$lcEstado = '';
				foreach($laTempEPI as $laHabita){

					$lcEstado = ($laHabita['SECHAB']==$this->aIngreso['cSeccion'] && $laHabita['NUMHAB']==$this->aIngreso['cHabita'])?'9':'0';
					$this->CrearRegHabLog($laHabita['SECHAB'], $laHabita['NUMHAB'],$lcEstado);

				}
			}
		}
		// Validación Rips de salida
		$llUrgencias = false;

		if( $this->aIngreso['cSeccion']=='TU'){
			$laTempEPI = $this->oDb
				->select('INGIUR')
					->from('INGURGTL01')
					->where('INGIUR', '=', $this->aIngreso['nIngreso'])
					->getAll('array');

			if(is_array($laTempEPI)){
				if(count($laTempEPI)>0){
					$llUrgencias = true;
				}
			}
			unset($laTempEPI);
		}

		$llViaUrgencias = (($this->aIngreso['cCodVia']=='01') || (($this->aIngreso['cCodVia']=='05') &&  $this->aIngreso['cSeccion']=='TU') || ($llUrgencias == true));
		if(!$llViaUrgencias){
			// Crear RIPS de salida
			$loObjEPI = new Rips();
			$loObjEPI->crearSalida($this->aIngreso,$this->cRegMed,$this->cPrgCre);
		}

		// Actualiza las camas ocupadas por el ingreso
		$lcEstado = '0';
		$lcTabla = 'FACHAB';
		$laDatos = [
			'ESTHAB'=>$lcEstado,
			'INGHAB'=>0,
			'TIDHAB'=>'',
			'NIDHAB'=>0,
			'USMHAB'=>$this->cUsuCre,
			'DTMHAB'=>$this->cFecCre,
			'HRMHAB'=>$this->cHorCre,
		];
		$llResultado = $this->oDb
			->from($lcTabla)
			->where('INGHAB', '=', $this->aIngreso['nIngreso'])
			->where('SECHAB', '<>', $this->aIngreso['cSeccion'])
			->where('NUMHAB', '<>', $this->aIngreso['cHabita'])
			->actualizar($laDatos);


		// Actualiza la cama actal ocupada por el ingreso a estado salida
		$lcEstado = '9';
		$lcTabla = 'FACHAB';
		$laDatos = [
			'ESTHAB'=>$lcEstado,
			'USMHAB'=>$this->cUsuCre,
			'DTMHAB'=>$this->cFecCre,
			'HRMHAB'=>$this->cHorCre,
		];
		$llResultado = $this->oDb
			->from($lcTabla)
			->where([
				'INGHAB'=>$this->aIngreso['nIngreso'],
				'SECHAB'=>$this->aIngreso['cSeccion'],
				'NUMHAB'=>$this->aIngreso['cHabita']
			])
			->actualizar($laDatos);

		// Actualiza archivo de ingreso
		$lcTabla = 'RIAING';
		$laDatos = [
				'FEEING'=>$this->cFecEpi,
				'HREING'=>$this->cHorCre,
				'UMOING'=>$this->cUsuCre,
				'PMOING'=>$this->cPrgCre,
				'FMOING'=>$this->cFecCre,
				'HMOING'=>$this->cHorCre,
				];
		$llResultado = $this->oDb->from($lcTabla)->where(['NIGING'=>$this->aIngreso['nIngreso']])->actualizar($laDatos);
	}

	function CrearRegHabLog($tcSecHab='', $tcHabita='', $tcEstado='')
	{
		$lcTabla = 'HABLOG';
		$laDatos = [
				'FMVLOG'=>$this->cFecCre,
				'HMVLOG'=>$this->cHorCre,
				'SECLOG'=>$tcSecHab,
				'NUMLOG'=>$tcHabita,
				'ESTLOG'=>$tcEstado,
				'INGLOG'=>$this->aIngreso['nIngreso'],
				'TIDLOG'=>$this->aIngreso['cTipId'],
				'NIDLOG'=>$this->aIngreso['nNumId'],
				'USRLOG'=>$this->cUsuCre,
				'PGMLOG'=>$this->cPrgCre,
				'FECLOG'=>$this->cFecCre,
				'HORLOG'=>$this->cHorCre,
				];
		$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);
	}


	/*
	 *	Envía correo con epicrisis al paciente. Previo se deben obtener datos del paciente.
	 *	@param array $taDatosEpi: datos para consultar la epicrisis
	 *	@param string $tcMail: dirección de correo a la que se debe enviar
	 */
	private function enviarEmailPaciente($taDatosEpi, $tcMail='')
	{
		$llEnvioMail = $this->oDb->obtenerTabMae1('OP1TMA', 'EPICRIS', 'cl1tma=\'MAIL\' AND ESTTMA=\'\'', null, '0')=='1';
		if ($llEnvioMail) {

			if ($this->validarEmailPaciente($tcMail)) {

				$loMailEnviar = new MailEnviar();

				// Obtener plantilla desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PLANTILL'
				$loMailEnviar->obtenerPlantilla('EPICRIS', 'EPICRIS');
				$lcPlantilla = $loMailEnviar->cPlantilla;

				// Configuración desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PARAMETR'
				$laConfigToda = $loMailEnviar->obtenerConfiguracion('EPICRIS');
				$laConfig = $laConfigToda['config'];

				// Reemplazar datos en la plantilla
				$laDatos = [
					'[[Nombre]]'=>$this->aIngreso['cNombre'],
				];
				$lcPlantilla = strtr($lcPlantilla, $laDatos);
				//$laConfig['tcSubject'] = strtr($laConfig['tcSubject'], $laDatos);

				// Completa la configuración
				$laConfig['tcTO'] = $this->cEmailPac;
				$laConfig['tcBody'] = $lcPlantilla;

				// Adicionar epicrisis
				$lnIngreso=$this->aIngreso['nIngreso'];
				//$laDatosEpi = $loMailEnviar->obtenerDatosEpicrisis($lnIngreso);
				$lcRutaAdj = $loMailEnviar->adicionarAdjuntoLibro([$taDatosEpi], "ResumenHC_$lnIngreso.pdf") ? $loMailEnviar->cDirAdjutos(): '';
				$laConfig['tcAttachServerFilesID'] = $lcRutaAdj;

				// Enviar
				$lcResult = $loMailEnviar->enviar($laConfig);
				if (!empty($lcResult)) {
					$this->registrarEnvioEmail('');
				}
			} else {
				if (!empty($tcMail)) {
					// Dirección de correo no válida
					$this->registrarEnvioEmail('Dirección de correo no válida - '.$tcMail);
				}
			}
		}
	}


	/*
	 *	Valida el correo a enviar
	 *	@param string $tcMail: dirección de correo a la que se debe enviar
	 */
	private function validarEmailPaciente($tcMail='')
	{
		if (empty($tcMail)) {
			$lcEmailPac = '';
			$llEmailPac = false;

			//	Si no viene dirección de correo obtiene la registrada del paciente
			$laTabla = $this->oDb
				->select('P.MAIPAC,A.MAIPAL,A.OP10AL')
				->from('RIAPAC P')
				->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
				->where([
					'P.TIDPAC'=>$this->aIngreso['cTipId'],
					'P.NIDPAC'=>$this->aIngreso['nNumId'],
				])
				->get('array');
			if (is_array($laTabla)) {
				if (count($laTabla)>0) {
					$lcEmailPac = trim($laTabla['MAIPAC']??'');
					$lcEmailPac = empty($lcEmailPac)? trim($laTabla['MAIPAL']??''): $lcEmailPac;
					$llEmailPac = $laTabla['OP10AL']==1 && !empty($lcEmailPac);
				}
			}

		} else {
			$llEmailPac = true;
			$lcEmailPac = $tcMail;
		}

		if ($llEmailPac) {
			$loMailEnviar = new MailEnviar();
			$llEmailPac = $loMailEnviar->validarEmail($lcEmailPac);
			$this->cEmailPac = $llEmailPac? $lcEmailPac: '';
			unset($loMailEnviar);
		}

		return $llEmailPac;
	}


	/*
	 *	Guardar registro del correo enviado
	 */
	private function registrarEnvioEmail($tcMsgError)
	{
		$lcTabla = 'EVMAIL';
		$laTabla = $this->oDb
			->max('CNSEVM','CNSEVM')
			->from($lcTabla)
			->where('INGEVM','=',$this->aIngreso['nIngreso'])
			->getAll('array');
		if (is_array($laTabla)) {
			if (count($laTabla)>0) {
				$lnCns = $laTabla[0]['CNSEVM'] + 1;
				$lnEstado = empty($tcMsgError)? 2: 3;

				$ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
				$lnFecha = $ltAhora->format('Ymd');
				$lnHora = $ltAhora->format('His');
				$lcUsuWin = $lcEqu = $lcObs = '';

				$laData = [
					'INGEVM'=>$this->aIngreso['nIngreso'],
					'CNSEVM'=>$lnCns,
					'ESTEVM'=>$lnEstado,
					'EMAEVM'=>$this->cEmailPac,
					'TDCEVM'=>'EPICRISIS',
					'FEVEVM'=>$lnFecha,
					'HEVEVM'=>$lnHora,
					'USEEVM'=>$this->cUsuCre,
					'USWEVM'=>$lcUsuWin,
					'EQUEVM'=>$lcEqu,
					'MEREVM'=>$tcMsgError,
					'OBSEVM'=>$lcObs,
					'USUEVM'=>$this->cUsuCre,
					'PRGEVM'=>$this->cPrgCre,
					'FECEVM'=>$lnFecha,
					'HOREVM'=>$lnHora,
				];
				$this->oDb->from($lcTabla)->insertar($laData);
			}
		}
	}

	/*
	 *	Envía correo de alerta al depto de NoPOS cuando un paciente fallece
	 */
	private function enviarMailFalleceNoPOS()
	{
		$llEnviar = $this->oDb->obtenerTabMae1('OP1TMA', 'NOPOS', 'CL1TMA=\'MIPRES\' AND CL2TMA=\'MAILFALL\' AND ESTTMA=\'\'', null, '0')=='1';
		if (!$llEnviar) {
			return false;
		}
		$loNoPOS = new NoPosFunciones();
		$lcSolicitaCtcMipres = $loNoPOS->entidadMipres($this->aIngreso['cPlan']);
		$laSolicitaCtcMipres = explode('-',$lcSolicitaCtcMipres);

		// Verifica si el paciente necesita prescripciones por MiPres
		if ($laSolicitaCtcMipres[1]!='S') {
			return false;
		}

		$loMailEnviar = new MailEnviar();

		// Obtener plantilla desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PLANTILL'
		$loMailEnviar->obtenerPlantilla('EPICRIS', 'SFLLNPOS');
		$lcPlantilla = $loMailEnviar->cPlantilla;

		// Configuración desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PARAMETR'
		$laConfigToda = $loMailEnviar->obtenerConfiguracion('SFLLNPOS');
		$laConfig = $laConfigToda['config'];

		// Reemplazar datos en la plantilla
		$laDatos = [
			'[[Ingreso]]'=>$this->aIngreso['nIngreso'],
			'[[Nombre]]'=>$this->aIngreso['cNombre'],
			'[[Medico]]'=>$_SESSION[HCW_NAME]->oUsuario->getNombreCompleto(),
			'[[Habitacion]]'=>$this->aIngreso['cSeccion'].'-'.$this->aIngreso['cHabita'],
			'[[Plan]]'=>$this->aIngreso['cPlan'].' - '.$this->aIngreso['cPlanDsc'],
		];
		$lcPlantilla = strtr($lcPlantilla, $laDatos);
		$laConfig['tcSubject'] = strtr($laConfig['tcSubject'], $laDatos);

		// Completa la configuración
		$laConfig['tcBody'] = $lcPlantilla;

		// Enviar
		$lcResult = $loMailEnviar->enviar($laConfig);

		return true;
	}

	public function consultaMuerteEncefalica($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laPacientesEncefalica = [];
		if(empty($tnFechaInicio)){ return $laPacientesEncefalica; }
		if(empty($tnFechaFinal)){ return $laPacientesEncefalica; }
		$tnFechaInicio=intval($tnFechaInicio);
		$tnFechaFinal=intval($tnFechaFinal);

		$laPacientesEncefalica = $this->oDb
			->select("Q.INGEDC INGRESO, A.TIDING TIPOIDE, A.NIDING IDENTIFICACION")
			->select("IFNULL(TRIM(B.NM1PAC), '') PRIMER_NOMBRE")
			->select("IFNULL(TRIM(B.NM2PAC), '') SEGUNDO_NOMBRE")
			->select("IFNULL(TRIM(B.AP1PAC), '') PRIMER_APELLIDO")
			->select("IFNULL(TRIM(B.AP2PAC), '') SEGUNDO_APELLIDO")
			->select("INT(SUBSTR(Q.OP5EDC, LOCATE('=', Q.OP5EDC) + 1, 8)) FECHA_FALLECE")
			->select("CASE WHEN TRIM(M.DESEPI)='S' THEN 'Si' ELSE 'No' END MUERTE_ENCEFALICA")
			->from('EVODIA Q')
			->leftJoin('RIAING A', 'A.NIGING=Q.INGEDC')
			->leftJoin('RIAPAC  B', 'B.TIDPAC=A.TIDING AND B.NIDPAC=A.NIDING')
			->leftJoin('RIAEPI M', 'M.NINEPI=Q.INGEDC AND M.CCNEPI=Q.EVOEDC')
			->where('Q.TIPEDC', '=', 'RF')
			->where('Q.INDEDC', '=', 1)
			->where('Q.OP5EDC', '<>', '')
			->where('Q.DCAEDC', '=', '')
			->where('M.COSEPI', '=', 9010)
			->where("(SUBSTR(Q.OP5EDC, LOCATE('=', Q.OP5EDC) + 1, 8)>='$tnFechaInicio' AND SUBSTR(Q.OP5EDC, LOCATE('=', Q.OP5EDC) + 1, 8)<='$tnFechaFinal') ")
			->getAll('array');
		return $laPacientesEncefalica;

	}

}
