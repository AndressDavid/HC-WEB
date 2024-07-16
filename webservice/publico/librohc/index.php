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

	$lcTitulo = 'WebService Libro HC';
	$lcNombreEspacio = 'default';
	define('WSTOKEN', 'a6e1cc77230e953ee89d8c94c72f1438da74e034e43b84766b78ca2707eaa6a198080abd35941c4badf573813cacbb4c1cdce68b9d7b0c669d4c53b9d345cd1a');

	/*
		--- CLASES Y OBJETOS ADICIONALES ----
		Utilice esta sección del bloque para incluir clases y declarar objetos adicionales
	*/
	include (__DIR__ .'/../../../nucleo/controlador/class.ListaDocumentos.php');
	// --- FIN DE CLASES Y OBJETOS ADICIONALES ---



	/*
		N O   M O D I F I C A B L E
		CONFIGURACIÓN DEL SERVICIO, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
	*/
	if(isset($loSoapServer)==true){

		$loSoapServer->configureWSDL($lcTitulo, 'urn:'.$lcNombreEspacio);

		/*
			MODIFICABLE
			REGISTRO Y DECLARACIÓN DE FUNCIONES
		*/
		// ---------------------------------------------------------------------------------------------------------------------------------------

		// REGISTRO DE FUNCIONES

		$lcNombreFuncion = 'fcConsultarListaPorDocumento';
		$loSoapServer->register($lcNombreFuncion,
			[
				'tcToken' => 'xsd:string',
				'tnIngreso' => 'xsd:integer',
				'tcTipDoc' => 'xsd:string',
				'tnNumDoc' => 'xsd:integer',
			],
			[
				'return' => 'xsd:string',
			],
			'urn:'.$lcNombreEspacio,
			'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
			'rpc',
			'encoded',
			'Retorna lista de documentos de un paciente por ingreso o documento, ordenados por fecha descendentemente<br><pre>
Parámetros de entrada:
    tcToken:     string	    128	   token
    tnIngreso:   integer      8    Número de ingreso
    tcTipDoc:    string       1    Tipo de documento del paciente
    tnNumDoc:    integer     13    Número de documento del paciente
Parámetros de salida:
    return       string     json con lista de documentos
</pre>');

		// DECLARACIÓN DE FUNCIONES

		// Consultar lista de documentos para un paciente por ingreso o documento
		function fcConsultarListaPorDocumento($tcToken='', $tnIngreso=0, $tcTipDoc='', $tnNumDoc=0)
		{
			if(trim($tcToken)==WSTOKEN){
				try {
					$loListaDoc = new NUCLEO\ListaDocumentos(true);
					$loListaDoc->cargarDatos($tnIngreso, $tcTipDoc, $tnNumDoc, ['fecha', 'descrip'], [SORT_DESC, SORT_ASC]);
					$laResultado = $loListaDoc->obtenerDocumentos();
				} catch (Exception $loError) {
					$laResultado = ['error'=>$loError->getMessage(),'resultado'=> false];
				}
			} else {
				$laResultado = ['error'=>'TOKEN','resultado'=> false];
			}
			return json_encode($laResultado);
		}


		// ---------------------------------------------------------------------------------------------------------------------------------------

		/*
			N O   M O D I F I C A B L E
			INICIO DEL SERVICIO
		*/
		$loSoapServer->service(file_get_contents('php://input'));
	}else{
		http_response_code(500);
	}
	exit();
