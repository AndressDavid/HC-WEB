<?php
require_once (__DIR__ .'/../../../nucleo/publico/constantes.php');
$laVersionPHP = explode('.', phpversion());
if (intval($laVersionPHP[0])<8) {
	require_once (__DIR__ .'/../../complementos/nusoap-php7/0.95/lib/nusoap.php') ;
	$loSoapServer = new soap_server();	
} else {
	require_once (__DIR__ .'/../../complementos/nusoap-php8/1.124/nusoap.php');
	$loSoapServer = new soap_server_8();
}


$lcTitulo = 'WebService Envío Mensajes HL7';
$lcNombreEspacio = 'EnviarHL7';
define('WSTOKEN', '7emyomv5nSKBEGGYdcVokQX8CXQkuwiLJoOcHBLZTvAYCtwiQROIb3mJfRpYyHu7sJG4bzYFrXW0aOPjYo6xJrWnP0wI42caeL9G0vxkjuZqkXcfRBiorva0fVshchZ5');



/*
	Configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
*/
if(isset($loSoapServer)==true){

	$loSoapServer->configureWSDL($lcTitulo, 'urn:'.$lcNombreEspacio);

	/*
		REGISTRO Y DECLARACIÓN DE FUNCIONES
	*/

	// ---------------------------------------------------------------------------------------------------------------------------------------

	// PARÁMETROS PARA ADT
	$datos_entrada_hl7 = [
		'token'		=> 'xsd:string',
		'modelo'	=> 'xsd:string',
		'tipo'		=> 'xsd:string',
		'evento'	=> 'xsd:string',
		'ingreso'	=> 'xsd:int',
		'cita'		=> 'xsd:int',
		'orden'		=> 'xsd:int',
		'cup'		=> 'xsd:string',
		'regmed'	=> 'xsd:string',
		'medico'	=> 'xsd:string',
		'enviar'	=> 'xsd:int',
	];
	$datos_salida_hl7 = [
		'mensaje'	=> 'xsd:string',
	];

	// PARÁMETROS PARA VALIDAR Y GUARDAR ORU
	$datos_entradaORU = [
		'token'		=> 'xsd:string',
		'modelo'	=> 'xsd:string',
		'mensajeOru'=> 'xsd:string',
		'codificado'=> 'xs:boolean',
	];


	// REGISTRO DE FUNCIONES
	$lcNombreFuncion = 'fnEnviarHL7';
	$loSoapServer->register(
		$lcNombreFuncion,
		$datos_entrada_hl7,
		$datos_salida_hl7,
		'urn:'.$lcNombreEspacio,
		'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
		'rpc',
		'encoded',
		'Crea y envia mensajes ADT'
	);

	$lcNombreFuncion = 'fnValidarORU';
	$loSoapServer->register(
		$lcNombreFuncion,
		$datos_entradaORU,
		$datos_salida_hl7,
		'urn:'.$lcNombreEspacio,
		'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
		'rpc',
		'encoded',
		'Validacion basica de mensajes ORU.<br/>Retorna:<br/>- Mensaje de respuesta para ser enviado al servicio correspondiente.<br/>- Indicador si es valido'
	);

	$lcNombreFuncion = 'fnGuardarORU';
	$loSoapServer->register(
		$lcNombreFuncion,
		$datos_entradaORU,
		$datos_salida_hl7,
		'urn:'.$lcNombreEspacio,
		'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
		'rpc',
		'encoded',
		'Guarda mensaje ORU. Retorna indicador si es valido y se guardo.'
	);


	// DECLARACIÓN DE FUNCIONES
	// ---------------------------------------------------------------------------------------------------------------------------------------

	/*
	 *	fnEnviarHL7
	 */
	function fnEnviarHL7(
		$tcToken='',
		$tcModelo='',
		$tcTipoMensaje='',
		$tcEvento='',
		$tnIngreso=0,
		$tnCita=0,
		$tnNumOrden=0,
		$tcCodCup='',
		$tcRegMedico='',
		$tcMedico='',
		$tnEnviar=1 )
	{
		$laResultado = ['error'=>'', 'resultado'=>''];
		if(trim($tcToken)==WSTOKEN){
			require_once __DIR__ . '/../../../nucleo/controlador/class.HL7_Enviar.php';
			$lcRespuesta = NUCLEO\HL7_Enviar::fnGenerarEnviarHL7($tcModelo, $tcTipoMensaje, $tcEvento, $tnIngreso, $tnCita, $tnNumOrden, $tcCodCup, $tcRegMedico, $tcMedico, $tnEnviar);
		} else {
			$laResultado['error'] = 'TOKEN Incorrecto';
		}

		return json_encode($laResultado);
	}


	/*
	 *	fnValidarORU
	 */
	function fnValidarORU(
		$tcToken='',
		$tcModelo='',
		$tcMensajeORU='',
		$tlMensajeCodificado=false )
	{
		$laResultado = ['error'=>'', 'resultado'=>''];
		if(trim($tcToken)==WSTOKEN){
			require_once __DIR__ . "/../config/class.HL7_{$tcModelo}_RecibeORU.php";

			$loORU = new HL7_RecibeORU($tcModelo);

			// Decodifica el mensaje enviado
			if ($tlMensajeCodificado) {
				$lcMensajeORU = base64_decode($tcMensajeORU);
			}

			//Crear y validar mensaje
			$loORU->fnCrearMensajeORU($lcMensajeORU);
			$loORU->fnValidaMensajeORU();

			//Crear mensaje de respuesta
			$lcMsgRta = $loORU->fnCrearRespuesta();

			// Codifica la respuesta
			if ($tlMensajeCodificado) {
				$lcMsgRta = base64_encode($lcMsgRta);
			}
			$laResultado['resultado'] = $lcMsgRta;
			unset($loORU);
		} else {
			$laResultado['error'] = 'TOKEN Incorrecto';
		}
		return json_encode($laResultado);
	}


	/*
	 *	fnGuardarORU
	 */
	function fnGuardarORU(
		$tcToken='',
		$tcModelo='',
		$tcMensajeORU='',
		$tlCodificado=false )
	{
		$lcResultado = ['error'=>'', 'resultado'=>''];
		if(trim($tcToken)==WSTOKEN){
			$lcMensajeORU = base64_decode($tcMensajeORU);
			require_once __DIR__ . '/../config/class.HL7_RecibeORU.php';

			$loORU = new HL7_RecibeORU($loConfig, $tcModelo);

			// Decodifica el mensaje enviado
			if ($tlMensajeCodificado) {
				$lcMensajeORU = base64_decode($tcMensajeORU);
			}

			// Crear mensaje
			$loORU->fnCrearMensajeORU($lcMensajeORU);
			$loORU->fnObtenerDatosORU(true);

			// Guarda resultados de gases arteriales y venosos
			$loORU->fnGuardarGases();
			$loORU->fnLogProcesadoORU();

			$laResultado['resultado'] = $loORU->cEstadoLog;
			unset($loORU);

		} else {
			$laResultado['error'] = 'TOKEN Incorrecto';
		}
		return json_encode($laResultado);
	}


	// ---------------------------------------------------------------------------------------------------------------------------------------

	// INICIO DEL SERVICIO
	$loSoapServer->service(file_get_contents("php://input"));
}else{
	http_response_code(500);
}
exit();