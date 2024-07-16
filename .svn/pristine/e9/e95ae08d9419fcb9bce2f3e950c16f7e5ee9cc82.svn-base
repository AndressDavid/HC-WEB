<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	$laVersionPHP = explode('.', phpversion());
	if (intval($laVersionPHP[0])<8) {
		require_once (__DIR__ .'/../../complementos/nusoap-php7/0.95/lib/nusoap.php') ;
		$loSoapServer = new soap_server();	
	} else {
		require_once (__DIR__ .'/../../complementos/nusoap-php8/1.124/nusoap.php');
		$loSoapServer = new soap_server_8();
	}

	$lcTitulo = "WebService Alertas tempranas";
	$lcNombreEspacio = 'default';
	define("WSTOKEN", "3cxoCrYc4DqcBDVqsZfB3BS11QM1RFZ0BSKgikLWChPLesNX1GZDK5JSheyXieN3mivp2rxTPF5QrrXqvWkihUR3zHsD0ni80CfLL8ANvKX8w03uKREKsHmqUJZRWCLp");


	/*
	MODIFICABLE
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/
	include (__DIR__ .'/../../../nucleo/controlador/class.SignosNews.php') ;
	// --- FIN DE CLASES Y OBJETOS ADICIONALES ---



	/*
	N O   M O D I F I C A B L E
	A continuación se realiza la configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
	*/
	if(isset($loSoapServer)==true){

		/*
		N O   M O D I F I C A B L E
		A continuación se realiza la configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
		*/
		$loSoapServer->configureWSDL($lcTitulo, 'urn:'.$lcNombreEspacio);

		/*
		MODIFICABLE
		 ######  ####### ####### ### #     # ###  #####  ### ####### #     #
		 #     # #       #        #  ##    #  #  #     #  #  #     # ##    #
		 #     # #       #        #  # #   #  #  #        #  #     # # #   #
		 #     # #####   #####    #  #  #  #  #  #        #  #     # #  #  #
		 #     # #       #        #  #   # #  #  #        #  #     # #   # #
		 #     # #       #        #  #    ##  #  #     #  #  #     # #    ##
		 ######  ####### #       ### #     # ###  #####  ### ####### #     #

		REGISTRO DE FUNCIONES Y DECLARACIÓN DE LAS MISMAS
		Las funciones se deben registrar en el webService para su uso, luego se deben declarar. Tenga en cuenta los parámetros de entrada y salida.
		*/
		// ---------------------------------------------------------------------------------------------------------------------------------------

		// REGISTRO DE FUNCIONES
		$lcNombreFuncion = 'fcRegistrarSignos';
		$loSoapServer->register($lcNombreFuncion,
								[
								 'tcToken' => 'xsd:string',
								 'tnIngreso' => 'xsd:integer',
								 'tcTipoId' => 'xsd:string',
								 'tnId' => 'xsd:integer',
								 'tcSignos' => 'xsd:string',
								 'tnEdad' => 'xsd:integer',
								 'tcUsuario' => 'xsd:string'],
								['return' => 'xsd:string'],
								'urn:'.$lcNombreEspacio,
								'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
								'rpc',
								'encoded',
								'Registra signos del paciente');

		$lcNombreFuncion = 'fcConsultarUltimosSignos';
		$loSoapServer->register($lcNombreFuncion,
								[
								 'tcToken' => 'xsd:string',
								 'tnIngreso' => 'xsd:integer',
								 'tcTipoId' => 'xsd:string',
								 'tnId' => 'xsd:integer'],
								['return' => 'xsd:string'],
								'urn:'.$lcNombreEspacio,
								'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
								'rpc',
								'encoded',
								'Consulta signos del paciente');

		// DECLARACIÓN DE FUNCIONES
		// Se debe especificar un TOKEN para validar que el cliente este autorizado
		function fcRegistrarSignos($tcToken='', $tnIngreso=0, $tcTipoId='', $tnId=0, $tcSignos='', $tnEdad=0, $tcUsuario='') {
			$laResultado=array('resultado'=>'', 'respuesta'=>'', 'puntaje'=>0, 'insertado'=>false, 'valores'=>'', 'signos'=>'');

			if(trim($tcToken)==WSTOKEN){
				$loSignosNews = new NUCLEO\SignosNews();
				if(isset($tcSignos)==true){
					$loSignosNews->medir(json_decode($tcSignos,true),$tnIngreso);

					$laResultado['valores']=$loSignosNews->getValores();
					$laResultado['signos']=$loSignosNews->getSignos();
					$laResultado['resultado']=$loSignosNews->getResultado();
					$laResultado['respuesta']=$loSignosNews->getRespuesta();
					$laResultado['puntaje']=$loSignosNews->getPuntaje();

					if($laResultado['signos']['fr']['valor']>0 &&
					  $laResultado['signos']['so2']['valor']>0 &&
					  $laResultado['signos']['t']['valor']>0 &&
					  $laResultado['signos']['tas']['valor']>0 &&
					  $laResultado['signos']['tad']['valor']>0 &&
					  $laResultado['signos']['fc']['valor']>0){
						$laResultado['insertado']=$loSignosNews->insertar($tnIngreso,$tcTipoId,$tnId,$tcUsuario);
					}else{
						$laResultado['insertado']=false;
					}
				}
			}

			// Esa función retorna un numero entero
			return json_encode($laResultado);
		}

		function fcConsultarUltimosSignos($tcToken='', $tnIngreso=0, $tcTipoId='', $tnId=0) {
			$laResultado=array();
			if(trim($tcToken)==WSTOKEN){
				$loSignosNews = new NUCLEO\SignosNews();
				$laResultado = $loSignosNews->consultarUltimos($tnIngreso, $tcTipoId, $tnId);
			}

			// Esa función retorna un numero entero
			return json_encode($laResultado);
		}

		// ---------------------------------------------------------------------------------------------------------------------------------------

		/*
		N O   M O D I F I C A B L E
		INICIO DEL SERVICIO
		Las instruccione abajo contenidas inician el servicio y procesan la solicitud
		*/
		$loSoapServer->service(file_get_contents("php://input"));
	}else{
		http_response_code(500);
	}
	exit();
?>