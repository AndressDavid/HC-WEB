<?php

require_once 'class.ApiHelper.php';

class ApiAutenticacion
{

	public function generarToken($tcUsuario, $tcPassword) : bool
	{
		$_SESSION['token'] = "";
		global $goDb;
		$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'DOCMONGO', 'CL2TMA' => '000001', 'ESTTMA' => ''], null, ''));
		if (empty($lcUrl)) {
			echo 'Falta definir URL para obtener token';
			return false;
		}
		$lcDatosUsuario = base64_encode($tcUsuario . ':' . $tcPassword);
		$laParametros = [
			'Authorization: Basic ' . $lcDatosUsuario
		];
		$laOptionalParams = [];
		$laResponse = (new ApiHelper($laParametros))->post($lcUrl, $laOptionalParams);
		if((isset($laResponse['errorCode']) && $laResponse['errorCode'] != 0)){
			echo $laResponse['errorMessage'];
			return false;
		}
		$_SESSION['token'] = $laResponse['data']['token'];

		return true;
	}


	public function validarToken() : bool
	{
		global $goDb;
		if (isset($_SESSION['token'])  && $_SESSION['token'] != '')
			$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'VALTOKEN', 'CL2TMA' => 'URL', 'ESTTMA' => ''], null, ''));
		if (empty($lcUrl)) {
			return false;
		}
		$laParametros = [
			'token' => $_SESSION['token']
		];
		$laResponse = (new ApiHelper())->get($lcUrl, $laParametros);

		if($laResponse['errorCode'] != 0){
			return false;
		}
		return true;
	}


	public function renovarToken() : bool
	{
		global $goDb;
		if (isset($_SESSION['token'])  && $_SESSION['token'] != '')
			$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'RENTOKEN', 'CL2TMA' => 'URL', 'ESTTMA' => ''], null, ''));
		if (empty($lcUrl)) {
			return false;
		}
		$laParametros = [
			'token' => $_SESSION['token']
		];
		$laResponse = (new ApiHelper())->post($lcUrl, $laParametros);
		if($laResponse['errorCode'] != 0){
			return false;
		}

		$_SESSION['token'] = $laResponse['data']['token'];
		return true;
	}


	public function extenderTiempoToken()
	{
		global $goDb;
		if (isset($_SESSION['token']) && $_SESSION['token'] != '')
			$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'EXT_TIME', 'CL2TMA' => 'URL', 'ESTTMA' => ''], null, ''));

		if (empty($lcUrl)) {
			return false;
		}

		$laResponse = (new ApiHelper())->post(
			$lcUrl,
			[
				'token' => $_SESSION['token']
			]
		);

		if($laResponse['errorCode']??1 != 0){
			return false;
		}

		$_SESSION['token'] = $laResponse['data']['token'];
		return true;
	}

}
