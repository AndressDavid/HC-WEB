<?php
NAMESPACE NUCLEO;

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.DocumentosCM.php';
	require_once __DIR__ . '/../../../controlador/class.Via.php';
	$loDocCM = new DocumentosCM();

	$lcAccion=AplicacionFunciones::fnSanitizar($_POST['accion'] ?? '');

	switch ($lcAccion) {

		case 'consultaSoportes':
			$laFiltros = [];
			if (isset($_POST['ingreso'])) $laFiltros['ingreso'] = AplicacionFunciones::fnSanitizar($_POST['ingreso']);
			if (isset($_POST['via'])) $laFiltros['via'] = AplicacionFunciones::fnSanitizar($_POST['via']);
			if (isset($_POST['entidad'])) $laFiltros['entidad'] = AplicacionFunciones::fnSanitizar($_POST['entidad']);
			if (isset($_POST['facturador'])) $laFiltros['facturador'] = AplicacionFunciones::fnSanitizar($_POST['facturador']);
			if (isset($_POST['estado'])) $laFiltros['estado'] = AplicacionFunciones::fnSanitizar($_POST['estado']);
			$laFiltros['fechatipo'] = AplicacionFunciones::fnSanitizar($_POST['fechatipo'] ?? 'factura');
			if (isset($_POST['fechaini'])) $laFiltros['fechaini'] = AplicacionFunciones::fnSanitizar($_POST['fechaini']);
			if (isset($_POST['fechafin'])) $laFiltros['fechafin'] = AplicacionFunciones::fnSanitizar($_POST['fechafin']);
			$laRetorna = $loDocCM->consultaListaSoportes((object) $laFiltros);
			break;

		case 'listaSoportes':
			$lcTipoSoporte = AplicacionFunciones::fnSanitizar($_POST['tipo'] ?? '');
			$laRetorna['soportes'] = $loDocCM->listaSoportes($lcTipoSoporte);
			break;

		case 'addSoportesIngreso':
			$lnIngreso = intval(AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? '0'));
			$lcTipoSoporte = AplicacionFunciones::fnSanitizar($_POST['tipo'] ?? '');
			$laSoportes = $_POST['soportes'];
			$laRetorna = $loDocCM->insertarGenerar($lnIngreso, $lcTipoSoporte, $laSoportes);
			break;

		case 'generarSoportes':
			$lcTipoSop = AplicacionFunciones::fnSanitizar($_POST['tipo'] ?? '');
			$lbTodos = intval(AplicacionFunciones::fnSanitizar($_POST['todos'] ?? '0')) == 1;
			$laSoportes = $lbTodos ? [] : $_POST['soportes'];
			$lnIngreso = intval(AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? '0'));
			$lbGuardarTransfiriendo = intval(AplicacionFunciones::fnSanitizar($_POST['guardatransf'] ?? '0')) == 1;

			ini_set('max_execution_time', 60*60);
			$loDocCM->estableceTipoSop($lcTipoSop);
			$laRetorna = $loDocCM->generarDocumentosIngreso($lnIngreso, $laSoportes);
			break;

		case 'actualizarEstadoFecha':
			$lnIngreso = AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? 0);
			$lcTipoSoporte = AplicacionFunciones::fnSanitizar($_POST['tipo'] ?? '');
			$laSoportes = $_POST['soportes'] ?? [];
			$lnFecha = AplicacionFunciones::fnSanitizar($_POST['fecha'] ?? false);
			$lcEstado = AplicacionFunciones::fnSanitizar($_POST['estado'] ?? false);
			$laRetorna = $loDocCM->actualizarEstadoSoporte($lnIngreso, $lcTipoSoporte, $laSoportes, $lcEstado, $lnFecha);
			break;

		case 'listaTipos':
			$laRetorna['tipos'] = $loDocCM->listaTipos();
			break;

		case 'listaEstadosSoportes':
			$laRetorna['estados'] = $loDocCM->listaEstados();
			$laRetorna['soportes'] = $loDocCM->listaSoportesCM();
			$laRetorna['entidades'] = $loDocCM->listaEntidades();
			$laRetorna['vias'] = (new Via())->obtenerListaVias();
			$laRetorna['diasAdd'] = $goDb->obtenerTabMae1('OP3TMA', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='DIASPAUS' AND ESTTMA=''", null, 10);
			break;

		case 'listaEstados':
			$laRetorna['estados'] = $loDocCM->listaEstados();
			break;

		case 'listaSoportesCM':
			$laRetorna['soportes'] = $loDocCM->listaSoportesCM();
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);