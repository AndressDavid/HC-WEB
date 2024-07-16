<?php

require_once 'class.ApiAutenticacion.php';

class ApiConsentimientoInformado
{

	public function consultarConsentimientosPaciente($tnIngreso)
	{
		global $goDb;
		$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'DOCMONGO', 'CL2TMA' => '000002', 'ESTTMA' => ''], null, ''));

		if (empty($lcUrl)) {
			return [];
		}

		if (!(new ApiAutenticacion())->extenderTiempoToken()){
			return [];
		}

		$laResponse = (new ApiHelper())->get(
			$lcUrl,
			[
				'token' => $_SESSION['token'],
				'nroIngreso' => $tnIngreso
			]
		);

		if (empty($laResponse) || (isset($laResponse['errorCode']) && $laResponse['errorCode'] != 0)) {
			return [];
		}

		return $laResponse['data'];
	}


	public function consultarDocumento($tnDocumento)
	{
		global $goDb;
		$lcUrl = trim($goDb->ObtenerTabMae1('DE2TMA||OP5TMA', 'REST_PHP', ['CL1TMA' => 'DOCMONGO', 'CL2TMA' => '000003', 'ESTTMA' => ''], null, ''));

		if (empty($lcUrl)) {
			return [];
		}

		if (!(new ApiAutenticacion())->extenderTiempoToken()){
			return [];
		}

		$laResponse = (new ApiHelper())->get(
			$lcUrl,
			[
				'token' => $_SESSION['token'],
				'idDocumento' => $tnDocumento
			]
		);

		if (empty($laResponse) || (isset($laResponse['errorCode']) && $laResponse['errorCode'] != 0)) {
			return [];
		}

		return $laResponse['data'];
	}
}