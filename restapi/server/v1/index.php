<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

include_once(__DIR__ . '/../../../nucleo/publico/constantes.php');
include_once(__DIR__ . '/../comun/funciones.php');
require_once(__DIR__ . '/../../../nucleo/controlador/class.Db.php');

require_once(__DIR__ .'/../addons/Slim/Slim.php');
\Slim\Slim::registerAutoloader();

$loAppAPI = new \Slim\Slim();
$loAppAPI->log->setEnabled(true);

// METODOS DE LA API
// #     # ####### ####### ####### ######  #######  #####
// ##   ## #          #    #     # #     # #     # #     #
// # # # # #          #    #     # #     # #     # #
// #  #  # #####      #    #     # #     # #     #  #####
// #     # #          #    #     # #     # #     #       #
// #     # #          #    #     # #     # #     # #     #
// #     # #######    #    ####### ######  #######  #####

// Método de prueba
$loAppAPI->get('/hello', function () {
	$laResponse = array('args' => 'Hola');
	echoResponse(200, $laResponse);
});

// Estado de usuarios para prosodyctl
$loAppAPI->get('/prosodyctl', 'authenticate', function () {
	$lcTokenMetodo = 'jg' . md5('abc::' . date('Ymd'));

	verifyRequiredParams(['token']);
	$lcToken = strval(is_null(resquestGet('token')) ? '' : resquestGet('token'));

	global $goDb;

	$laResponse = array('error' => 'true', 'data' => 'Indefinido');

	if ($lcToken == $lcTokenMetodo) {
		$laResponseEncod = [];
		$laCampos = ['A.USUARI', 'A.PRDYES', 'A.PRDYPW', 'A.PRDYFE'];
		$laRegistros = $goDb->select($laCampos)->tabla('SISMENSEG A')->where('A.PRDYES', '=', 1)->getAll('array');

		foreach ($laRegistros as $laRegistro) {
			$laResponseEncode[] = ['USUARIO' => strtoupper(trim($laRegistro['USUARI'])), 'ESTADO' => intval($laRegistro['PRDYES']), 'PASSWORD' => encriptar(trim($laRegistro['PRDYPW'])), 'FECHA' => intval($laRegistro['PRDYFE'])];
		}
		$laResponse = array('error' => 'false', 'data' => $laResponseEncode, 'records' => count($laResponseEncode));

	} else {
		$laResponse = array('error' => 'true', 'data' => 'El token de metodo no es valido.');
	}

	echoResponse(200, $laResponse);
});


// Método de prueba
$loAppAPI->get('/obtenerEspecialidades', function () {
	validarExisteSesionActiva();
	$laResponse = $_SESSION[HCW_NAME]->oUsuario->getEspecialidades();
	echoResponse(200, $laResponse);
});


/*
 **************************************************
 *	RUTAS DON DOCTOR
 **************************************************
 */

$loAppAPI->group('/dd', function () use ($loAppAPI) {
	// Insertar o Modificar Paciente
	$loAppAPI->post('/paciente', function () use ($loAppAPI) {
		$lcIP = $loAppAPI->request->getIp();
		$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());

		$lcLogFecha = date('Y-m-d H:i:s');
		fnEscribirLog('PACIENTE DD: ' . $lcRequest, 'DonDoctor_');

		$laRequest = array_change_key_case(json_decode($lcRequest, true), CASE_UPPER);
		$laCamposReq = ['DOCUMENTTYPEID', 'DOCUMENT', 'NAME', 'LASTNAME', 'BIRTHDATE', 'GENRE', 'PHONE',];
		verifyRequiredFields($laRequest, $laCamposReq, true, 'DonDoctor_');

		require_once(__DIR__ . '/../../../nucleo/controlador/class.AgendaPacienteDD.php');
		$loAgenda = new NUCLEO\AgendaPacienteDD();
		$laRta = $loAgenda->validarIP($lcIP);
		if ($laRta['success']) {
			ini_set('max_execution_time', 10 * 60);
			$laRta = $loAgenda->pacienteDD($laRequest, 'APIREST', 'WBHKPCDD');
		}

		fnEscribirLog('Respuesta Paciente ' . ($laRequest['USERID'] ?? $lcLogFecha) . ': ' . json_encode($laRta), 'DonDoctor_');

		if ($laRta['success']) {
			echoResponse(200, $laRta);
		} else {
			echoResponse(406, $laRta);
		}
	}
	);

	// Agendar o Cancelar una Cita
	$loAppAPI->post('/cita', function () use ($loAppAPI) {
		$lcIP = $loAppAPI->request->getIp();
		$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());

		$lcLogFecha = date('Y-m-d H:i:s');
		fnEscribirLog('CITA DD: ' . $lcRequest, 'DonDoctor_');

		$laRequest = array_change_key_case(json_decode($lcRequest, true), CASE_UPPER);
		$laCamposReq = ['USERID', 'SERVICEID', 'DATE', 'FROM', 'TO', 'STATE', 'DOCTORID',];
		verifyRequiredFields($laRequest, $laCamposReq, true, 'DonDoctor_');

		require_once(__DIR__ . '/../../../nucleo/controlador/class.AgendaPacienteDD.php');
		$loAgenda = new NUCLEO\AgendaPacienteDD();
		$laRta = $loAgenda->validarIP($lcIP);
		if ($laRta['success']) {
			ini_set('max_execution_time', 15 * 60);
			$laRta = $loAgenda->citaDD($laRequest, 'APIREST', 'WBHKCTDD');
		}

		fnEscribirLog('Respuesta Cita ' . ($laRequest['APPOINTMENTID'] ?? $lcLogFecha) . ': ' . json_encode($laRta), 'DonDoctor_');

		if ($laRta['success']) {
			echoResponse(200, $laRta);
		} else {
			echoResponse(406, $laRta);
		}
	}
	);
});


/*
 **************************************************
 *	RUTAS AGENDAMIENTO
 **************************************************
 */
// Datos de parametrización de la agenda
$loAppAPI->get('/parametros_agenda/:tcTipo(/:tcParam)', 'authenticate', function ($tcTipo, $tcParam = '') {
	require_once(__DIR__ . '/../../../nucleo/controlador/class.AgendaPaciente.php');
	$laRta = (new NUCLEO\AgendaPaciente)->consultarParametros($tcTipo, $tcParam);
	echoResponse(200, $laRta);
});


/*
 **************************************************
 *	ALERTA INR - WARFARINA
 **************************************************
 */
// Datos de parametrización de la alerta INR-WARFARINA
$loAppAPI->get('/alertainr/:tnIngreso/:tcMedicamento', 'authenticate', function ($tnIngreso, $tcMedicamento) {
	require_once(__DIR__ . '/../../../nucleo/controlador/class.MedicamentoFormula.php');
	$laRta = (new NUCLEO\MedicamentoFormula)->consultaAlertaInr($tnIngreso, $tcMedicamento);
	echoResponse(200, $laRta);
});

/*
 **************************************************
 *	RUTA NUTRICION DIETAS
 **************************************************
 */

// Enviar Dietas a MedirestDiet
$loAppAPI->get('/nutricion/dietas/:tcTipo/:tcDato(/:tnEsSalida)', 'authenticate', function ($tcTipo, $tcDato, $tnEsSalida = '0') {
	require_once(__DIR__ . '/../../../nucleo/controlador/class.NutricionConsulta.php');
	$loNut = new NUCLEO\NutricionConsulta();
	$laRta = $loNut->generarEnviarDatosMedirest($tcTipo, $tcDato, $tnEsSalida == '1');
	echoResponse(200, $laRta);
});


/*
 **************************************************
 *	TRANSFIRIENDO
 **************************************************
 */

$loAppAPI->group('/transfiriendo', function () use ($loAppAPI) {
	// Emisión de documentos de facturación electrónica
	$loAppAPI->post('/emisionfe', function () use ($loAppAPI) {
		$laRta = ['success' => true];
		$lcIP = $loAppAPI->request->getIp();
		$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());

		fnEscribirLog($lcRequest, 'TransfEmisionFE_');

		$laRequest = json_decode($lcRequest, true);
		$laCamposReq = ['esExitoso',];
		verifyRequiredFields($laRequest, $laCamposReq, true, 'TransfEmisionFE_');

		require_once(__DIR__ . '/../../../nucleo/controlador/class.FacturaElectronica.php');
		$loFE = new NUCLEO\FacturaElectronica();
		$laRta = $loFE->validarIP($lcIP);
		if ($laRta['success']) {
			$laRta = $loFE->emisionFE($laRequest, 'APIREST', 'WBHKTRNS');
		}
		if ($laRta['success']) {
			echoResponse(200, $laRta);
		} else {
			echoResponse(400, $laRta);
		}
	});
});


/*
 **************************************************
 *	RUTAS CONSULTAS HC
 **************************************************
 */

$loAppAPI->group('/hc', function () use ($loAppAPI) {
		// Obtiene datos de un paciente por ingreso
	$loAppAPI->get('/datosingreso/:ingreso', function ($tcIngreso) use ($loAppAPI) {	
		validarExisteSesionActiva();			
		$laRta = ['success' => true];
		$laDatosIngreso=[];

		if (isset($tcIngreso) && ($tcIngreso < 999999 || $tcIngreso > 10000000)) {
			$lnEst = 200;
			$laRta = ['success' => false, 'error' => 'Número de ingreso incorrecto'];

		} else {
			require_once (__DIR__ . "/../../../nucleo/controlador/class.Ingreso.php");
			$loIngreso = new NUCLEO\Ingreso();
			$laRetorna = $loIngreso->cargarIngreso($tcIngreso);
			$lnValidarIngreso=$loIngreso->nIngreso;
			$lcError=$lnValidarIngreso==0 ? 'No existe ingreso':'';

			if (empty($lcError)) {
				$lnEst = 200;
				$laRta['datos'] = $loIngreso;
				unset($laRta['error']);
			} else {
				$lnEst = 200;
				$laRta = ['success' => false, 'error' => $lcError,];
			}
		}
		echoResponse($lnEst, $laRta);
	});
	
	// Obtiene los datos de los consumos de salas por ingreso, sala y tipo de consumo (500=Medicamentos, 600=Elementos), fecha/hora inicio, fecha/hora final
	$loAppAPI->post('/consumossalas/:tcToken', function ($tcToken) use ($loAppAPI) {
		validarToken($tcToken);

		$laRta = ['success' => true];
		$lcError='';
		$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
		$laRequest = json_decode($lcRequest, true);
		
		require_once (__DIR__ . '/../../../nucleo/controlador/class.DescripcionQuirurgica.php');
		$loDescripcionQx = new NUCLEO\DescripcionQuirurgica();
		$laRetorna = $loDescripcionQx->consultarConsumosSalas($laRequest);
		$laJsonResultado = json_decode($laRetorna[0]->DATOS);

		if (is_array($laJsonResultado) && count($laJsonResultado)===0){
			$lcError = "No existen datos a consultar";
		}	
	
		if (empty($lcError)) {
			$lnEst = 200;
			$laRta['datos'] = $laJsonResultado;
			unset($laRta['error']);
		} else {
			$lnEst = 400;
			$laRta = ['success' => false, 'error' => $lcError];
		}
		echoResponse($lnEst, $laRta);
	});

	
	$loAppAPI->group('/libro', function () use ($loAppAPI) {
		// Obtiene tipos de documento del libro de HC
		$loAppAPI->get('/tipos-doc/:tcToken', function ($tcToken) use ($loAppAPI) {
			validarToken($tcToken);

			$laRta = ['success' => true, 'tipos' => []];
			$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
			$laRequest = json_decode($lcRequest, true);

			require_once (__DIR__ . '/../../../nucleo/controlador/class.ListaDocumentos.php');
			$laRta['tipos'] = (new NUCLEO\ListaDocumentos())->obtenerDescripcionTipos();

			if (is_array($laRta['tipos']) && count($laRta['tipos']) > 0) {
				$lnEst = 200;
			} else {
				$lnEst = 400;
				$laRta = ['success' => false, 'error' => 'Error al obtener elementos del árbol'];
			}
			echoResponse($lnEst, $laRta);
		}
		);


		// Obtiene items del árbol de HC
		$loAppAPI->get('/items-tree/:tcToken', function ($tcToken) use ($loAppAPI) {
			validarToken($tcToken);

			$laRta = ['success' => true, 'tree' => []];
			$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
			$laRequest = json_decode($lcRequest, true);

			require_once (__DIR__ . '/../../../nucleo/controlador/class.ListaDocumentos.php');
			$laRta['tree'] = (new NUCLEO\ListaDocumentos())->obtenerItemsTreeNE();

			if (is_array($laRta['tree']) && count($laRta['tree']) > 0) {
				$lnEst = 200;
			} else {
				$lnEst = 400;
				$laRta = ['success' => false, 'error' => 'Error al obtener elementos del árbol'];
			}
			echoResponse($lnEst, $laRta);
		}
		);


		// Obtiene la lista de documentos por ingreso
		$loAppAPI->post('/listadocumentos/:tcToken', function ($tcToken) use ($loAppAPI) {
			validarToken($tcToken);

			$laRta = ['success' => true];
			$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
			$laRequest = json_decode($lcRequest, true);

			if (isset($laRequest['ingreso']) && ($laRequest['ingreso'] < 999999 || $laRequest['ingreso'] > 10000000)) {
				$lnEst = 400;
				$laRta = ['success' => false, 'error' => 'Número de ingreso incorrecto'];
			} else {
				require_once (__DIR__ . '/../../../nucleo/controlador/class.ListaDocumentos.php');
				$loListaDocs = new NUCLEO\ListaDocumentos();
				$laRetorna = $loListaDocs->listarDocumentos($laRequest['ingreso'] ?? 0, $laRequest['tipoId'] ?? '', $laRequest['numeroId'] ?? 0, true);
				$lcError = $laRetorna['error'] ?? 'Error al consultar lista de documentos';

				if (empty($lcError)) {
					$lnEst = 200;
					$laRta = array_merge($laRta, $laRetorna);
					unset($laRta['error']);
				} else {
					$lnEst = 400;
					$laRta = ['success' => false, 'error' => $lcError];
				}
			}

			echoResponse($lnEst, $laRta);
		}
		);


		// Retorna un solo documento en formato html
		$loAppAPI->post('/obtenerdocumento', function () use ($loAppAPI) {

			$laRta = ['success' => true];
			$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
			$laRequest = json_decode($lcRequest, true);
			$token = substr(getallheaders()['Authorization']?? '00000000X', 7);

			$llValidacion = $laRequest['validacion'] ?? false;

			if($llValidacion){
				validarToken($token);
			}

			if (!isset($laRequest['nIngreso']) && isset($laRequest['ingreso']))		$laRequest['nIngreso'] = $laRequest['ingreso'];
			if (!isset($laRequest['cTipDocPac']) && isset($laRequest['tipoId']))	$laRequest['cTipDocPac'] = $laRequest['tipoId'];
			if (!isset($laRequest['nNumDocPac']) && isset($laRequest['numeroId']))	$laRequest['nNumDocPac'] = $laRequest['numeroId'];
			if (!isset($laRequest['cRegMedico']) && isset($laRequest['medRegMd']))	$laRequest['cRegMedico'] = $laRequest['medRegMd'];
			if (!isset($laRequest['cTipoDocum']) && isset($laRequest['tipoDoc']))	$laRequest['cTipoDocum'] = $laRequest['tipoDoc'];
			if (!isset($laRequest['cTipoProgr']) && isset($laRequest['tipoPrg']))	$laRequest['cTipoProgr'] = $laRequest['tipoPrg'];
			if (!isset($laRequest['tFechaHora']) && isset($laRequest['fecha']))		$laRequest['tFechaHora'] = $laRequest['fecha'];
			if (!isset($laRequest['nConsecCita']) && isset($laRequest['cnsCita']))	$laRequest['nConsecCita'] = $laRequest['cnsCita'];
			if (!isset($laRequest['nConsecCons']) && isset($laRequest['cnsCons']))	$laRequest['nConsecCons'] = $laRequest['cnsCons'];
			if (!isset($laRequest['nConsecEvol']) && isset($laRequest['cnsEvo']))	$laRequest['nConsecEvol'] = $laRequest['cnsEvo'];
			if (!isset($laRequest['nConsecDoc']) && isset($laRequest['cnsDoc']))	$laRequest['nConsecDoc'] = $laRequest['cnsDoc'];
			if (!isset($laRequest['cCUP']) && isset($laRequest['codCup']))			$laRequest['cCUP'] = $laRequest['codCup'];
			if (!isset($laRequest['cCodVia']) && isset($laRequest['codvia']))		$laRequest['cCodVia'] = $laRequest['codvia'];
			if (!isset($laRequest['cSecHab']) && isset($laRequest['sechab']))		$laRequest['cSecHab'] = $laRequest['sechab'];

			$laCamposReq = ['nIngreso', 'cTipDocPac', 'nNumDocPac', 'cTipoDocum', 'cTipoProgr', 'tFechaHora', 'cCodVia'];
			verifyRequiredFields($laRequest, $laCamposReq);

			require_once (__DIR__ . '/../../../nucleo/controlador/class.Documento.php');
			$loDocLibro = new NUCLEO\Documento();
			$loDocLibro->obtenerDocumento($laRequest, $lConsultarIngreso = true, $lbDocumentoSolo = true, $tlLabHTML = true, $tlEsHTML = true);

			$laContenido = $loDocLibro->aContenido();
			//unset($loDocLibro);

			if (is_array($laContenido) && count($laContenido) > 0) {


				$isBase64 = $laRequest['isBase64'] ?? false;

				if($isBase64){
					//$loDocLibro = new NUCLEO\Documento();
					$lcDocumentoLibro = $loDocLibro->generarVariosPDF([$laRequest], [], "LibroHcShaio.pdf","S");
					$laRta['documento'] = base64_encode($lcDocumentoLibro);
				}else{
					require_once (__DIR__ . '/../../../nucleo/controlador/class.TextHC.php');
					$loHtml = new NUCLEO\TextHC();
					$loHtml->procesar($laContenido);
					$laRta['documento'] = $loHtml->Output('S');
				}

				if (mb_strlen($laRta['documento'], 'UTF-8') > 0) {
					$lnEst = 200;
				} else {
					$lnEst = 400;
					$laRta = ['success' => false, 'error' => 'El documento no se pudo generar en HTML'];
				}
			} else {
				$lnEst = 400;
				$laRta = ['success' => false, 'error' => 'El documento no se encontró'];
			}
			echoResponse($lnEst, $laRta);
		}
		);


		// Retorna historia clínica en PDF
		$loAppAPI->post('/obtenerdocumentoingreso/:tcToken(/:tcUsarPassword)', function ($tcToken) use ($loAppAPI) {
			validarToken($tcToken, 'VALTOKPA');

			$laRta = ['success' => true];
			$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
			$laRequest = json_decode($lcRequest, true);

			if (isset($laRequest['ingreso']) && ($laRequest['ingreso'] < 999999 || $laRequest['ingreso'] > 10000000)) {
				$lnEst = 400;
				$laRta = ['success' => false, 'error' => 'Número de ingreso incorrecto'];

			} else {
				require_once (__DIR__ . '/../../../nucleo/controlador/class.Documento.php');
				$loDocumentoLibro = new NUCLEO\Documento();
				$tcPassword = ($tcUsarPassword??'N')=='N' ? false : 'usardocumento';
				$lcDocumentoLibro = $loDocumentoLibro->generarPDFxIngreso($laRequest['ingreso'], "LibroHC_{$laRequest['ingreso']}.pdf", 'S', $tcPassword, true);
				$lcError = mb_strlen($lcDocumentoLibro, 'UTF-8') > 50 ? '' : 'Error al consultar documentos';

				if (empty($lcError)) {
					$lnEst = 200;
					$laRta = ['success' => true, 'documento' => base64_encode($lcDocumentoLibro)];

				} else {
					$lnEst = 400;
					$laRta = ['success' => false, 'error' => $lcError];
				}
			}

			echoResponse($lnEst, $laRta);
		}
		);

	}
	);
});

/*
 **************************************************
 *	DRAGER ENVIO
 **************************************************
 */
$loAppAPI->post('/getPatient', function () use ($loAppAPI) {
	require_once (__DIR__ . "/../../../nucleo/controlador/class.HL7_Enviar.php");
	$lcRequest = json_decode(utf8SiEsNecesario($loAppAPI->request->getBody()));
	$loDrager = new NUCLEO\HL7_Enviar;
	$laResponse = $loDrager->fnGenerarEnviarHL7(
		$lcRequest->tcMethod,
		$lcRequest->tcTypeMessage,
		$lcRequest->tcEvent,
		$lcRequest->tnIngreso,
		"",
		"SN",
		""
	);
	return echoResponse(200, $laResponse);
});

/*
 **************************************************
 *	GRUPO SHAIO MICROMEDEX    CYAB 12-09-2023
 **************************************************
 */

 $loAppAPI->group('/micromedex', function () use ($loAppAPI) {

	$loAppAPI->post('/interactions', function ()  use ($loAppAPI) {
		require_once (__DIR__ . "/../../../nucleo/controlador/class.consumirServiciosMicroMedexInteractions.php");

		$lConsumir = new ConsumirXML;
		$lIntegridadJson = $lConsumir->validacionIntegridadJson($loAppAPI->request->getBody());

		$loAuth = verificarTokenShaio(base64_decode(getallheaders()['Authorization']));

		if($loAuth["status"] !=200){
			return echoResponse($loAuth["status"], $loAuth);
		}

		if($lIntegridadJson["status"] != 200){
			return echoResponse($lIntegridadJson["status"], $lIntegridadJson);
		}

		$lConsumir->setJson(json_decode($loAppAPI->request->getBody(), true));


		$lRespuestaReglas = $lConsumir->reglasJsonExistencia();
		if($lRespuestaReglas["status"] != 200){
			return echoResponse($lRespuestaReglas["status"], $lRespuestaReglas);
		}

		$lRespuestaReglasGenerales= $lConsumir->ReglasJsonGenerales();
		if($lRespuestaReglasGenerales["status"] != 200){
		 	return echoResponse($lRespuestaReglasGenerales["status"], $lRespuestaReglasGenerales);
		}

		$lResponse = $lConsumir->ConsumirServicioMicroMexInteractions();
		echoResponse(200, $lResponse);
	});

	$loAppAPI->get('/interactions/detail/:id', function ($pId){

		require_once (__DIR__ . "/../../../nucleo/controlador/class.consumirServiciosDetailMicroMedex.php");

		$loAuth = verificarTokenShaio(base64_decode(getallheaders()['Authorization']));

		if($loAuth["status"] !=200){
			return echoResponse($loAuth["status"], $loAuth);
		}

		$oConsumirDetalle = new ConsumirXmlDetalle;
		$oConsumirDetalle->setidReferencia($pId);
		$oConsumirDetalle->construirXmlDetalle();
		$loRespuesta = $oConsumirDetalle->consumirServioDetalle();

		echoResponse(200, $loRespuesta);
	});

	$loAppAPI->get('/activo', function (){
		echoResponse(200, [
			'success' => true,
			'activo' => in_array(estadoAlertasMedicamentos(), ['A','S'])
		]);
	});

});


/* Inicio de la aplicación */
$loAppAPI->run();
