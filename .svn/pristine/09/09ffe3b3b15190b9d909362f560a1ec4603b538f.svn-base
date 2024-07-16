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

$lcTitulo = 'WebService Retorna Documentos PDF';
$lcNombreEspacio = 'GetDocPdf';
define('WSTOKEN', 'JBJZ7QXD69BO6EPCQIHO1AULWBHZPR74CQT3G2X8CS9EHND9Q0MQYSWR4EXLGE34Y3X0QHL12OS60JPI4AJ488O796R3LGM92UCJ5O4RGYZ9SA5IPGETSBXXB3B3Y6ZR');


if(isset($loSoapServer)==true){

	$loSoapServer->configureWSDL($lcTitulo, 'urn:'.$lcNombreEspacio);

	/*
		REGISTRO Y DECLARACIÓN DE FUNCIONES
	*/

	$lcNombreFuncion = 'fnObtenerDocPdf';
	$loSoapServer->register(
		$lcNombreFuncion,
		[
			'token' => 'xsd:string',
			'datos' => 'xsd:string',
			'usuario' => 'xsd:string',
		],
		[
			'retorno' => 'xsd:string',
		],
		'urn:'.$lcNombreEspacio,
		'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
		'rpc',
		'encoded',
		'Retorna PDF de documento(s) en texto'
	);


	// DECLARACIÓN DE FUNCIONES
	// ---------------------------------------------------------------------------------------------------------------------------------------

	/*
	 *	fnObtenerDocPdf
	 *
	 *	@param	$tcToken string - token
	 *	@param	$tcDatosJson string - datos en formato json para la consulta
	 */
	function fnObtenerDocPdf(
		$tcToken='',
		$tcDatosJson='',
		$tcUsuario=''
	)
	{
		$laResultado = ['error'=>'', 'resultado'=>''];
		if(trim($tcToken)==WSTOKEN) {
			if(!empty($tcDatosJson)) {
				ini_set('max_execution_time', 60*600); // 600 minutos de consulta

				require_once __DIR__ .'/../../../nucleo/controlador/class.Documento.php';
				$loDocLibro = new NUCLEO\Documento();
				$laDatos = json_decode(utf8_encode(base64_decode($tcDatosJson)), true);

				if (isset($laDatos['datos'])) {
					$laDatosPortada = $laDatosDoc = [];
					$laDatos['datos'] = (array) $laDatos['datos'];
					foreach($laDatos['datos'] as $loValor){
						$laDatosDoc[] = (array) $loValor;
					}
					if (isset($laDatos['portada'])) {
						$laDatos['portada'] = (array) $laDatos['portada'];
						$laDatosPortada = (array) $laDatos['portada'];
					}
					return base64_encode( $loDocLibro->generarVariosPDF($laDatosDoc, $laDatosPortada, 'librohc.pdf', 'S', null, false, $tcUsuario) );

				} else {
					$laResultado['error'] = 'No hay datos de documentos para retornar';
				}

			} else {
				$laResultado['error'] = 'Faltan datos';
			}

		} else {
			$laResultado['error'] = 'TOKEN Incorrecto';
		}

		return json_encode($laResultado);
	}

	// ---------------------------------------------------------------------------------------------------------------------------------------

	// INICIO DEL SERVICIO
	$loSoapServer->service(file_get_contents("php://input"));

} else {
	http_response_code(500);
}
exit();