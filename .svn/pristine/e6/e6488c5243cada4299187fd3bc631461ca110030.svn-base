<?php
	require_once (__DIR__ .'/../../../nucleo/publico/constantes.php') ;
	require_once (__DIR__ .'/../../complementos/nusoap-php7/0.95/lib/nusoap.php') ;

	$lcTitulo = "WebService de prueba";
	$lcNombreEspacio = 'default';

	/*
	MODIFICABLE
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/
	// --- FIN DE CLASES Y OBJETOS ADICIONALES ---



	/*
	N O   M O D I F I C A B L E
	A continuación se realiza la configuración del servicio, si se cambia el urn o namespace el cliente debe cambiarlo al utilizarlo
	*/
	$loSoapServer = new soap_server();
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
		$lcNombreFuncion = 'funcionRetornaInteger';
		$loSoapServer->register($lcNombreFuncion,
								[
								 'tcToken' => 'xsd:string',
								 'tnInteger' => 'xsd:integer'],
								['return' => 'xsd:integer'],
								'urn:'.$lcNombreEspacio,
								'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
								'rpc',
								'encoded',
								'Metodo de funcionRetornaInteger');

		$lcNombreFuncion = 'funcionRetornaString';
		$loSoapServer->register($lcNombreFuncion,
								[
								 'tcToken' => 'xsd:string',
								 'tnInteger' => 'xsd:integer',
								 'tnFloat' => 'xsd:float',
								 'tlBoolean' => 'xsd:boolean',
								 'tnDecimal' => 'xsd:decimal',
								 'tnDouble' => 'xsd:double',
								 'tdDateTime' => 'xsd:dateTime',
								 'ttTime' => 'xsd:time',
								 'tdDate' => 'xsd:date'
								],
								['return' => 'xsd:string'],
								'urn:'.$lcNombreEspacio,
								'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
								'rpc',
								'encoded',
								'Metodo de funcionRetornaString');								

		// DECLARACIÓN DE FUNCIONES
		// Se debe especificar un TOKEN para validar que el cliente este autorizado
		function funcionRetornaInteger($tcToken='', $tnInteger=0) {
			$lnResultado=0;
			if(trim($tcToken)=='TokenParaValidarClienteAutorizado'){
				$lnResultado = $tnInteger+1;
			}
			
			// Esa función retorna un numero entero
			return $lnResultado;
		}

		function funcionRetornaString($tcToken='', $tnInteger=0, $tnFloat=0.0, $tlBoolean=false, $tnDecimal= 0.0, $tnDouble=0, $tdDateTime, $ttTime, $tdDate) {
			$laResultado=[];

			if(trim($tcToken)=='TokenParaValidarClienteAutorizado'){
				$laResultado = ['tcToken'=>$tcToken,
								'tnInteger'=>$tnInteger,
								'tnFloat'=>$tnFloat,
								'tlBoolean'=>$tlBoolean,
								'tnDecimal'=>$tnDecimal,
								'tnDouble'=>$tnDouble,
								'tdDateTime'=>$tdDateTime,
								'ttTime'=>$ttTime,
								'tdDate'=>$tdDate];
			}
			// Esta función retorna un string con un en JSON
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