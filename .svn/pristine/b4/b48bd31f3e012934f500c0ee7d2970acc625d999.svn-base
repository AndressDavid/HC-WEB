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
$loAppAPI->get('/saludo', function () {
	$laResponse = ['arg'=>'Hola', 'success'=>true];
	echoResponse(200, $laResponse);
});


$loAppAPI->group('/paciente', function () use ($loAppAPI) {

	$loAppAPI->get('/consultarDatosCabecera/:numeroIngreso', 'validarExisteSesionActiva', function ($tnIngreso) use ($loAppAPI) {
		require_once(__DIR__ . '/../../../nucleo/controlador/class.Historia_Clinica_Ingreso.php');
		$loIngreso = new NUCLEO\Historia_Clinica_Ingreso();

		// validar ingreso
		try {
			$laDatos = $loIngreso->datosIngreso($tnIngreso);
		} catch (\Throwable $th) {
			//throw $th;
			$laDatos = [];
			$lcErrorMsg = $th->getMessage();
			$lcErrorCode = $th->getCode();
		} finally {
			if (count($laDatos)>0) {
				$lnEstado = 200;
				$laRetorna = [
					'fechahora'=>date('Y-m-d H:i:s'),
					'success'=>true,
					'datos'=>$laDatos,
				];
			} else {
				$lnEstado = 406;
				$laRetorna = [
					'fechahora'=>date('Y-m-d H:i:s'),
					'success'=>false,
					'error'=>"Error: $lcErrorCode - $lcErrorMsg",
				];
			}
			echoResponse($lnEstado, $laRetorna);
		}
	});

	$loAppAPI->get('/consultarPorDocumento/:tipoId/:numeroId', 'validarExisteSesionActiva', function ($tcTipoId, $tcNumeroId) use ($loAppAPI) {
		retornarDatosPaciente($tcTipoId, $tcNumeroId);
	});

	$loAppAPI->get('/consultarDatosPaciente/:tipoId/:numeroId/:token', function ($tcTipoId, $tcNumeroId, $tcToken) use ($loAppAPI) {
		validarToken($tcToken,'VALTOKPA');
		retornarDatosPaciente($tcTipoId, $tcNumeroId);
	});

	$loAppAPI->post('/actualizarDatosPaciente/:programa/:usuario/:token', function ($tcPrograma, $tcUsuario, $tcToken) use ($loAppAPI) {
		validarToken($tcToken,'VALTOKPA');

		$lcRequest = utf8SiEsNecesario($loAppAPI->request->getBody());
		$laRequest = json_decode($lcRequest, true);
		// $laCamposReq = ['tipoId', 'numeroId', 'nombre1', 'nombre2', 'apellido1', 'apellido2', 'telefono', 'telefono2', 'correo', 'celular', 'celular2', 'direccion'];
		$laCamposReq = ['tipoId', 'numeroId', 'nombre1', 'apellido1', 'correo', 'celular', 'direccion'];
		verifyRequiredFields($laRequest, $laCamposReq);

		actualizarDatosPaciente($laRequest, mb_substr($tcPrograma, 0, 10, 'UTF8'), mb_substr($tcUsuario, 0, 10, 'UTF8'));
	});

});


$loAppAPI->group('/documentos', function () use ($loAppAPI) {
	$loAppAPI->post('/pdf', function ()  use ($loAppAPI) {


		$lcArchivo = 'librohc.pdf';
		$laDatosDoc = $laDatosPortada = [];
		$lbTodoLab = false;

		require_once __DIR__ .'/../../../nucleo/controlador/class.Documento.php';
		$loDocLibro = new NUCLEO\Documento();

		

		$datos = json_decode($loAppAPI->request->getBody(), true)['datos'];

		var_dump($datos);

		foreach ( $datos as $lcClave => $luValor) {
			if ($lcClave=='datos') {
				$laDatosDoc = json_decode($luValor, true);
			} elseif ($lcClave=='portada') {
				$laDatosPortada = json_decode($luValor, true);
			} elseif ($lcClave=='filename') {
				if (is_string($luValor)) $lcArchivo = $luValor;
			} else {
				$laDatosPortada[$lcClave] = $luValor;
			}
		}

		$lcPassword = null;
		$lbSinUsuario = false;
		$lcUsuario = '';
		$lbIncluirAdjExtraInst = false;

		$response = $loDocLibro->generarVariosPDF($loAppAPI, $laDatosPortada, $lcArchivo, 'S', $lcPassword, $lbSinUsuario, $lcUsuario, $lbIncluirAdjExtraInst, $lbTodoLab);

		var_dump($response);

		echoResponse(200, "asdadad");
	});
});

/* Inicio de la aplicación */
$loAppAPI->run();


function retornarDatosPaciente($tcTipoId, $tcNumeroId)
{
	require_once(__DIR__ . '/../../../nucleo/controlador/class.AgendaPaciente.php');
	$loAgenda = new NUCLEO\AgendaPaciente();
	$laRta = $loAgenda->consultarPacientePorDocumento($tcTipoId, $tcNumeroId);
	echoResponse($laRta['success'] ? 200 : 406, $laRta);
}


function actualizarDatosPaciente($taDatosPaciente, $tcPrograma, $tcUsuario)
{
	require_once(__DIR__ . '/../../../nucleo/controlador/class.AgendaPaciente.php');
	$loAgenda = new NUCLEO\AgendaPaciente();
	$laRta = $loAgenda->actualizarPaciente($taDatosPaciente, $tcPrograma, $tcUsuario);
	echoResponse($laRta['success'] ? 200 : 406, $laRta);
}