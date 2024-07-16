<?php

/*
**********************************************************************
**  FUNCIONES COMUNES
**********************************************************************
*/

function verificarTokenShaio($pToke){

	if (estadoAlertasMedicamentos()=='S') {
		return ['status'=> 200];
	}

	require_once "../../../nucleo/controlador/class.ApiAutenticacion.php";

	$lFiltroToken1 = substr($pToke,9, strlen($pToke));
	$lFlitroToken2 = substr($lFiltroToken1,1, strlen($lFiltroToken1)-3);

	$_SESSION['token'] = $lFlitroToken2;
	$lAuth = new ApiAutenticacion;

	if(!$lAuth->validarToken()){
		return array(
			'status'=> 401,
			'respuesta'=> "Token no valido",
			'mensaje'=> 'El token enviado no es valido o ya expiro'
		);
	}

	return ['status'=> 200];
}


function verificarToken($pToke){

	require_once "../../../nucleo/controlador/class.ApiAutenticacion.php";

	$_SESSION['token'] = $pToke;
	$lAuth = new ApiAutenticacion;

	if(!$lAuth->validarToken()){
		return array(
			'status'=> 401,
			'respuesta'=> "Token no valido",
			'mensaje'=> 'El token enviado no es valido o ya expiro'
		);
	}

	return ['status'=> 200];
}



function verifyRequiredParams($taRequiredFields)
{
	$llError = false;
	$lcErrorFields = '';
	$laRequestParams = $_REQUEST;

	if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
		$loAppAPI = \Slim\Slim::getInstance();
		parse_str($loAppAPI->request()->getBody(), $laRequestParams);
	}
	foreach ($taRequiredFields as $lcField) {
		if (!isset($laRequestParams[$lcField]) || strlen(trim($laRequestParams[$lcField])) <= 0) {
			$llError = true;
			$lcErrorFields .= $lcField . ', ';
		}
	}

	if ($llError) {
		$laResponse = array();
		$loAppAPI = \Slim\Slim::getInstance();
		$laResponse['error'] = true;
		$laResponse['status'] = 'Campo(s) requerido(s) ' . substr($lcErrorFields, 0, -2) . ' no existen o estan en blanco';
		echoResponse(400, $laResponse);

		$loAppAPI->stop();
	}
}


function verifyRequiredFields($taRequest, $taRequiredFields, $tbLog = false, $tcLog = '')
{
	$llError = false;
	$lcErrorFields = '';
	$lnNumError = 0;
	foreach ($taRequiredFields as $lcField) {
		if (
			!isset($taRequest[$lcField]) ||
			(is_array($taRequest[$lcField]) && count($taRequest[$lcField]) == 0) ||
			(!is_array($taRequest[$lcField]) && !is_bool($taRequest[$lcField]) && strlen(trim($taRequest[$lcField])) <= 0)
		) {
			$llError = true;
			$lcErrorFields .= $lcField . ', ';
			$lnNumError++;
		}
	}
	if ($llError) {
		$loAppAPI = \Slim\Slim::getInstance();
		$laResponse = [
			'success' => false,
			'message' => $lnNumError == 1 ?
			"Campo requerido $lcErrorFields no existe o está vacío." :
			"Campos requeridos $lcErrorFields no existen o están vacíos.",
		];

		if ($tbLog)
			fnEscribirLog('verifyRequiredFields: ' . json_encode($laResponse), $tcLog);

		echoResponse(400, $laResponse);

		$loAppAPI->stop();
	}
}


function estadoAlertasMedicamentos()
{
	require_once (__DIR__ . "/../../../nucleo/controlador/class.Db.php");
	global $goDb;
	return strtoupper($goDb->obtenerTabMae1('TRIM(OP1TMA)', 'FORMEDIC', "CL1TMA='ALERTMED' AND CL2TMA='ACTIVO' AND ESTTMA=''", null, 'N'));
}


function echoResponse($status_code, $laResponse)
{
	$loAppAPI = \Slim\Slim::getInstance();

	$loAppAPI->status($status_code);
	$loAppAPI->contentType('application/json');

	echo json_encode($laResponse);
}


function authenticate(\Slim\Route $route)
{
	$laHeaders = apache_request_headers();
	$laResponse = array();
	$loAppAPI = \Slim\Slim::getInstance();

	if (isset($laHeaders['Authorization'])) {

		$lcToken = $laHeaders['Authorization'];
		$laWebResoucesConfig = require(__DIR__ . '/../../../nucleo/privada/webResoucesConfig.php');
		$lcTokenMain = $laWebResoucesConfig['pp']['rest-api-key'];

		if (!($lcToken == $lcTokenMain)) {
			$laResponse['error'] = true;
			$laResponse['status'] = 'Acceso denegado. Token invalido ' . json_encode($laWebResoucesConfig);
			echoResponse(401, $laResponse);
			$loAppAPI->stop();
		// } else { //procede utilizar el recurso o método del llamado
		}
	} else {
		$laResponse['error'] = true;
		$laResponse['status'] = 'Falta token de autorización';
		echoResponse(400, $laResponse);

		$loAppAPI->stop();
	}
}


function validarToken($tcToken, $tcTipo='VALTOKEN')
{
	global $goDb;
	if (!is_string($tcToken))
		$tcToken = 'x';
	$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => $tcTipo, 'CL2TMA' => 'URL', 'ESTTMA' => ''], null, ''));
	if (empty($lcUrl)) {
		echoResponse(400, [
			'success' => false,
			'error' => true,
			'msg' => 'Falta definir URL para validar token',
		]);
	}

	$loCURL = curl_init();
	curl_setopt_array($loCURL, [
		CURLOPT_URL => $lcUrl . '?token=' . $tcToken,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
	]);
	$lcResponse = curl_exec($loCURL);
	curl_close($loCURL);

	$laValida = json_decode($lcResponse, true);
	if (
		!(
			in_array($tcTipo, ['VALTOKEN','VALTOKPA']) && (
				($tcTipo=='VALTOKEN' && isset($laValida['data']) && $laValida['data'] == 'valido') ||
				($tcTipo=='VALTOKPA' && isset($laValida['data']) && isset($laValida['data']['data']) && $laValida['data']['data'] == 'valido')
			)
		)
	) {
		echoResponse(400, [
			'success' => false,
			'error' => true,
			'msg' => ($laValida['errorMessage'] ?? 'Token no válido.'),
		]);
		$loAppAPI = \Slim\Slim::getInstance();
		$loAppAPI->stop();
	}
}


function validarExisteSesionActiva()
{
	// $_SESSION[HCW_NAME]->getHashServer()
	$loAppAPI = \Slim\Slim::getInstance();
	if( empty($_SESSION[HCW_NAME]->oUsuario->getUsuario()) || $_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==false ){
		echoResponse(400, [
			'error'=>true,
			'status'=>'No existe una sesión activa'
		]);
		$loAppAPI->stop();
	}
}


function resquestGet($tcGet = '', &$tcError = '')
{
	$lvData = null;
	$loAppAPI = \Slim\Slim::getInstance();
	try {
		$lvData = $loAppAPI->request->get($tcGet);
	} catch (Exception $e) {
		$lvData = null;
		$tcError = $e->getMessage();
	}

	return $lvData;
}


function resquestPost($tcPost = '', &$tcError = '')
{
	$lvData = null;
	$loAppAPI = \Slim\Slim::getInstance();
	try {
		$lvData = $loAppAPI->request->post($tcPost);
	} catch (Exception $e) {
		$lvData = null;
		$tcError = $e->getMessage();
	}

	return $lvData;
}


function utf8SiEsNecesario($tcTexto)
{
	return mb_check_encoding($tcTexto, 'UTF-8') ? $tcTexto : utf8_encode($tcTexto);
}


function encriptar($tcValor = '', $tcEncriptadoMetodo = 'aes-256-cbc', $tcEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06', $tcEncriptadoIV = '6451117dcff3fe2b')
{
	return base64_encode(openssl_encrypt(strval($tcValor), $tcEncriptadoMetodo, $tcEncriptadoClave, false, $tcEncriptadoIV));
}


function desencriptar($tcValor = '', $tcEncriptadoMetodo = 'aes-256-cbc', $tcEncriptadoClave = 'b43f06ae4a409702102d01b0a39d2c06', $tcEncriptadoIV = '6451117dcff3fe2b')
{
	$tcValor = base64_decode(strval($tcValor));
	return openssl_decrypt($tcValor, $tcEncriptadoMetodo, $tcEncriptadoClave, false, $tcEncriptadoIV);
}


function fnEscribirLog($tcMensaje, $tcLog = '', $tcPeriodo = 'mes')
{
	$tcPeriodo = strtolower($tcPeriodo);
	$lcPeriodo = $tcPeriodo == 'dia' ? 'Ymd' : ($tcPeriodo == 'mes' ? 'Ym' : ($tcPeriodo == 'año' ? 'Y' : 'Ym'));
	$lcFileLog = __DIR__ . '/../../../logs/Log_' . $tcLog . date($lcPeriodo) . '.log';
	$lcFecha = (new DateTime())->format('Y-m-d H:i:s.u');
	$lcMensaje = $lcFecha . ' | ' . $tcMensaje . PHP_EOL;
	$lnFile = fopen($lcFileLog, 'a');
	chmod($lcFileLog, 0777);
	fputs($lnFile, $lcMensaje);
	fclose($lnFile);
}