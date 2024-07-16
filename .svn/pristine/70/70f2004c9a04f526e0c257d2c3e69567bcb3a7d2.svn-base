<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ;

	switch ($lcAccion) {

		// Carga de datos inicial
		case 'cargaini':
			require_once __DIR__ .'/../../../controlador/class.CensoBitacora.php';
			$loCenso = new NUCLEO\CensoBitacora();
			$laRetorna['TiposPermisos'] = $loCenso->tiposPermisos();
			$laRetorna['PlanesManejoAdm'] = $loCenso->planesManejoAdmCenso();
			$laRetorna['PlanesManejoMed'] = $loCenso->planesManejoMedCenso();
			$laRetorna['UbicacionesSeccion'] = $loCenso->ubicacionesSeccion();
			$laRetorna['TiposDieta'] = $loCenso->tiposDieta();
			unset($loCenso);
			break;

		// Cargar datos de un ingreso
		case 'datosing':
			$lnIngreso = $_POST['ingreso'] ?? 0;
			$lcDxPrin = $_POST['dxprin'] ?? '';
			$lcPlanDx = $_POST['conducta'] ?? '';
			require_once __DIR__ .'/../../../controlador/class.CensoBitacora.php';
			$loCenso = new NUCLEO\CensoBitacora();
			$laRetorna['InfoCenso'] = $loCenso->infoCensoHC($lnIngreso, $lcDxPrin, $lcPlanDx);
			$laRetorna['DatosIng'] = $loCenso->cargarInfoIngreso($lnIngreso);
			break;

		// Guardar Registro
		case 'guardarreg':
			require_once __DIR__ .'/../../../controlador/class.CensoBitacora.php';
			$loCenso = new NUCLEO\CensoBitacora();
			$laValidar = $loCenso->validarRegistro($_POST);
			if ($laValidar['datosValidos']) {
				$laRetorna['guardar'] = $loCenso->guardarRegistro($_POST);
			} else {
				$laRetorna['objeto'] = $laValidar['objeto'];
				$laRetorna['error'] = $laValidar['error'] ?? 'Error inesperado';
			}
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
