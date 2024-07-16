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


	$lcTitulo = "WebService Laboratorio";
	$lcNombreEspacio = 'default';
	define("WSTOKEN", "2qvd4B9qDpAVOjGtnBVorlNcLR7M0CmI8PeQAc6GlMl7ZaBjhUNZRolRaoxzg70dn4kETGySs9cjtl4G6oY7yufvuvYdtku1N4F8YrieYo9qJ0SbgvHr10j6zHmiGPzu");

	/*
	MODIFICABLE
	--- CLASES Y OBJETOS ADICIONALES ----
	Utilice esta sección del bloque para incluir clases y declarar objetos adicionales*/
	include (__DIR__ .'/../../../nucleo/controlador/class.Laboratorio.php') ;
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
		$lcNombreFuncion = 'fcAgregarParametro';
		$loSoapServer->register($lcNombreFuncion,
			[
				'tcToken' => 'xsd:string',
				'tcCodigoVariable' => 'xsd:string',
				'tcDescripcion' => 'xsd:string',
				'tnMaximo' => 'xsd:double',
				'tnMinimo' => 'xsd:double',
				'tcUnidad' => 'xsd:string',
			],
			[
				'return' => 'xsd:string',
			],
			'urn:'.$lcNombreEspacio,
			'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
			'rpc',
			'encoded',
			'Funcion para agregar/actuaizar las variables');


		$lcNombreFuncion = 'fcReportartResultado';
		$loSoapServer->register($lcNombreFuncion,
			[
				'tcToken' => 'xsd:string',
				'tnIngreso' => 'xsd:integer',
				'tnOrden' => 'xsd:integer',
				'tcIdEstudio' => 'xsd:string',
				'tcCUPEstudio' => 'xsd:string',
				'tcTecnica' => 'xsd:string',
				'tcCodigoVariable' => 'xsd:string',
				'tcDescVariable' => 'xsd:string',
				'tcResultado' => 'xsd:string',
				'tnMaximo' => 'xsd:double',
				'tnMinimo' => 'xsd:double',
				'tcUnidad' => 'xsd:string',
				'tcMicroorganismo' => 'xsd:string',
				'tcAntiobiograma' => 'xsd:string',
				'tcAntibiotico' => 'xsd:string',
				'tcSensibilidad' => 'xsd:string',
				'tnIdMedico' => 'xsd:integer',
				'tcRegistroMedico' => 'xsd:string',
				'tcFechaValida' => 'xsd:string',
				'tcHoraValida' => 'xsd:string',
				'tnDatoCritico' => 'xsd:integer',
			],
			[
				'return' => 'xsd:string',
			],
			'urn:'.$lcNombreEspacio,
			'urn:'.$lcNombreEspacio.'#'.$lcNombreFuncion,
			'rpc',
			'encoded',
			'Registra los resultados del estudio<br><pre>
Parámetros de entrada:
    tcToken:            string	   128	  token
    tnIngreso:          integer      8    número de ingreso del paciente
    tnOrden:            integer     13    consecutivo de cita Shaio
    tcIdEstudio:        string      13    identificador del estudio
    tcCUPEstudio:       string       8    CUP del laboratorio
    tcTecnica:          string      60    Técnica utilizada en el estudio
    tcCodigoVariable:   string      10    Código de la variable
    tcDescVariable:     string      30    Descripción variable
    tcResultado:        string     250    Resultado
    tnMaximo:           double    15,5    Valor máximo de la variable
    tnMinimo:           double    15,5    Valor mínimo de la variable
    tcUnidad:           string      25    Unidad de medida de la variable
    tcMicroorganismo:   string      60    Microorganismo
    tcAntiobiograma:    string      60    Antiobiograma
    tcAntibiotico:      string      80    Antibiótico
    tcSensibilidad:     string     250    Sensibilidad
    tnIdMedico:         integer      6    Id Médico Bacteriólogo
    tcRegistroMedico:   string     250    Registro Médico Bacteriólogo
    tcFechaValida:      string       8    Fecha validación AAAAMMDD
    tcHoraValida:       string       6    Hora validación HHMMSS
    tnDatoCritico:      integer      1    Dato Crítico (0=Normal, 1=Monitoreo, 2=Amarillo, 3=Rojo)
Parámetros de salida:
    return              string
</pre>');

		// DECLARACIÓN DE FUNCIONES
		// Se debe especificar un TOKEN para validar que el cliente este autorizado
		function fcAgregarParametro (
			$tcToken='',
			$tcCodigoVariable='',
			$tcDescripcion='',
			$tnMaximo=0,
			$tnMinimo=0,
			$tcUnidad='')
		{
			$laResultado =['error'=>'TOKEN','resultado'=> false];
			if(trim($tcToken)==WSTOKEN){
				$loLaboratorio = new NUCLEO\Laboratorio();
				$llResultado = $loLaboratorio->definirParametro('','',$tcCodigoVariable, $tcDescripcion, $tnMaximo, $tnMinimo, $tcUnidad,'WS');
				$laResultado = $llResultado == true ?[
								'tcCodigoVariable' => $tcCodigoVariable,
								'tcDescripcion'    => $tcDescripcion,
								'tnMaximo'         => $tnMaximo,
								'tnMinimo'         => $tnMinimo,
								'tcUnidad'         => $tcUnidad,
								'resultado'		   => $llResultado,
								]: ['error'=>'Transaccion','resultado'=> $llResultado];
			}
			return json_encode($laResultado);
		}


		function fcReportartResultado (
			$tcToken='',
			$tnIngreso=0,
			$tnOrden=0,
			$tcIdEstudio='',
			$tcCUPEstudio='',
			$tcTecnica='',
			$tcCodigoVariable='',
			$tcDescVariable='',
			$tcResultado='',
			$tnMaximo=0,
			$tnMinimo=0,
			$tcUnidad='',
			$tcMicroorganismo='',
			$tcAntiobiograma='',
			$tcAntibiotico='',
			$tcSensibilidad='',
			$tnIdMedico=0,
			$tcRegistroMedico='',
			$tcFechaValida='0',
			$tcHoraValida='0',
			$tnDatoCritico=0
			)
		{
			$laResultado =['error'=>'TOKEN','resultado'=> false];
			if(trim($tcToken)==WSTOKEN){
				$loLaboratorio = new NUCLEO\Laboratorio();
				$lnConsecutivo = $loLaboratorio->registrarResultado(
						$tnIngreso,
						$tnOrden,
						$tcIdEstudio,
						$tcCUPEstudio,
						$tcTecnica,
						$tcCodigoVariable,
						$tcDescVariable,
						$tcResultado,
						$tnMaximo,
						$tnMinimo,
						$tcUnidad,
						$tcMicroorganismo,
						$tcAntiobiograma,
						$tcAntibiotico,
						$tcSensibilidad,
						$tnDatoCritico,
						$tnIdMedico,
						$tcRegistroMedico,
						$tcFechaValida,
						$tcHoraValida,
						'WS'
					);
				$laResultado = $lnConsecutivo>0 ? [
						'lnConsecutivo'		=> $lnConsecutivo,
						'tnIngreso'			=> $tnIngreso,
						'tnOrden'			=> $tnOrden,
						'tcIdEstudio'		=> $tcIdEstudio,
						'tcCUPEstudio'		=> $tcCUPEstudio,
						'tcTecnica'			=> $tcTecnica,
						'tcCodigoVariable'	=> $tcCodigoVariable,
						'tcDescVariable'	=> $tcDescVariable,
						'tcResultado'		=> $tcResultado,
						'tnMaximo'			=> $tnMaximo,
						'tnMinimo'			=> $tnMinimo,
						'tcUnidad'			=> $tcUnidad,
						'tcMicroorganismo'	=> $tcMicroorganismo,
						'tcAntiobiograma'	=> $tcAntiobiograma,
						'tcAntibiotico'		=> $tcAntibiotico,
						'tcSensibilidad'	=> $tcSensibilidad,
						'tnIdMedico'		=> $tnIdMedico,
						'tcRegistroMedico'	=> $tcRegistroMedico,
						'tnDatoCritico'		=> $tnDatoCritico,
						'resultado'			=> true,
					] : [
						'error'=>'Transaccion',
						'resultado'=> false
					];
			}
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