<?php
/*  CONSULTA CURL A MIPRES  */

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class MiPresCurlRequest
{
	private $oCurl;

	/**
	 * Iniciar sesión CURL
	 *
	 * $parasms = [
	 *		'url'			=> '',
	 *		'method'		=> '',	// 'GET', 'PUT', 'POST'
	 *		'header'		=> '',
	 *		'post_fields'	=> '',	// texto con los datos en formato json
	 *	];
	 */
	public function fnInit($params)
	{
		$this->oCurl = curl_init();
		$tcHeader = isset($params['header']) ? $params['header'] : [
//			'Content-Type: application/json; charset=utf-8',
			'Content-Type: application/json',
			'Accept: application/json',
		];

		switch ($params['method']) {
			case 'POST':
				@curl_setopt($this->oCurl, CURLOPT_CUSTOMREQUEST, 'POST');
				@curl_setopt($this->oCurl, CURLOPT_POST, true);
				if ($params['post_fields'])
					@curl_setopt($this->oCurl, CURLOPT_POSTFIELDS, $params['post_fields']);
				break;

			case 'PUT':
				@curl_setopt($this->oCurl, CURLOPT_CUSTOMREQUEST, 'PUT');
				if ($params['post_fields'])
					@curl_setopt($this->oCurl, CURLOPT_POSTFIELDS, $params['post_fields']);
				break;

			case 'GET':
				@curl_setopt($this->oCurl, CURLOPT_CUSTOMREQUEST, 'GET');
				@curl_setopt($this->oCurl, CURLOPT_POST, false);
				if ($params['post_fields'])
					$params['url'] = sprintf("%s?%s", $params['url'], http_build_query($params['post_fields']));
				break;
		}

		@curl_setopt($this->oCurl, CURLOPT_URL, $params['url']);
		@curl_setopt($this->oCurl, CURLOPT_SSL_VERIFYPEER, false);
		@curl_setopt($this->oCurl, CURLOPT_SSL_VERIFYHOST, false);

		@curl_setopt($this->oCurl, CURLOPT_RETURNTRANSFER, true);
		@curl_setopt($this->oCurl, CURLOPT_HEADER, true);
		@curl_setopt($this->oCurl, CURLOPT_HTTPHEADER, $tcHeader);
	}

	/**
	 * Realizar requerimiento CURL
	 *
	 * @return array  'header', 'body', 'curl_error', 'http_code', 'last_url'
	 */
	public function fnEjecutar()
	{
		$response = curl_exec($this->oCurl);
		$error = curl_error($this->oCurl);
		$result = [
			'header' => '',
			'body' => '',
			'curl_error' => '',
			'http_code' => '',
			'last_url' => ''
		];

		if ( $error != "" ) {
			$result['curl_error'] = $error;
			return $result;
		}

		$header_size = curl_getinfo($this->oCurl, CURLINFO_HEADER_SIZE);
		$result['header'] = substr($response, 0, $header_size);
		$result['body'] = substr($response, $header_size);
		$result['http_code'] = curl_getinfo($this->oCurl, CURLINFO_HTTP_CODE);
		$result['last_url'] = curl_getinfo($this->oCurl, CURLINFO_EFFECTIVE_URL);
		return $result;
	}
}
