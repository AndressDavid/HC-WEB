<?php
require_once __DIR__ .'/../../publico/constantes.php';
$laRetorna['error'] = '';
$nSalir = 0;
if(isset($_SESSION[HCW_NAME])){
	if($_SESSION[HCW_NAME]->oUsuario->getSesionActiva() !== true){
		$laRetorna['error'] = 'El usuario no tiene sesión activa';
		$nSalir = 1;
	}
}else{
	$laRetorna['error'] = 'Error en la sesion.  Intente nuevamente.';
	$nSalir = 1;
}
if($nSalir == 0){

	require_once __DIR__ .'/../../controlador/class.MedicamentoFormula.php';
	$loMediFor = new NUCLEO\MedicamentoFormula;
	$lcUsuario = ($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lcAccion = (isset($_POST['accion']) ? $_POST['accion'] : '');
	$lcFechaFormula = (isset($_POST['fecha']) ? $_POST['fecha'] : '');
	$lnNumeroIngreso = (isset($_POST['ingreso']) ? $_POST['ingreso'] : '');
	$lnCodBodega = (isset($_POST['bodega']) ? $_POST['bodega'] : '');
	$lnCenCostos = (isset($_POST['cenCostos']) ? $_POST['cenCostos'] : '');
	$lnCodigoQr = (isset($_POST['codigoQr']) ? $_POST['codigoQr'] : '');
	$laCodigosQr = (isset($_POST['codigosQr']) ? $_POST['codigosQr'] : '');
	$lnConsecutivo = (isset($_POST['consecutivo']) ? $_POST['consecutivo'] : '');
	switch($lcAccion){
		case 'listaSecciones':
			require_once __DIR__ .'/../../controlador/class.SeccionesHabitacion.php';
			$loSecciones = new NUCLEO\SeccionesHabitacion;
			$laRetorna = $loSecciones->aSecciones;
			unset($loSecciones);
			break;
		case 'listaVias':
			require_once __DIR__ .'/../../controlador/class.Via.php';
			$loVias = new NUCLEO\Via;
			$laRetorna = $loVias->aVias;
			unset($loVias);
			break;
		case 'listarIngresos':
			$lcSeccion = (isset($_POST['seccion']) ? $_POST['seccion'] : '');
			$lnvia = (isset($_POST['via']) ? $_POST['via'] : '');
			$loMediFor->consultarIngresosConFormula($lcFechaFormula, $lcSeccion, $lnvia);
			$laRetorna = $loMediFor->aIngresosConFormula;
			unset($loMediFor);
			break;
		case 'cargarMedicamentosFormulados':
			$loMediFor->cargarMedicamentosFormulados($lcFechaFormula, $lnNumeroIngreso, $lnCodBodega);
			$laRetorna = $loMediFor->aMediFormulados;
			unset($loMediFor);
			break;

		case 'cargarConsecutivo':
			$loMediFor->consecutivoDispensacion($lcFechaFormula, $lnNumeroIngreso);
			$laRetorna['CONDISP'] = $loMediFor->nConsecutivoDispensacion;
			unset($loMediFor);
			break;
		case 'dispensarMedicamentos':
			$laRetorna = $loMediFor->faDispensarMedicamentos($lcFechaFormula, $lnNumeroIngreso, $lnCodBodega, $lnCodigoQr);
			unset($loMediFor);
			break;
		case 'guardarDispensacion':
			$laRetorna = $loMediFor->faGuardarDispensacion($lcFechaFormula, $lnNumeroIngreso, $lnCodBodega, $lnCenCostos, $laCodigosQr, $lnConsecutivo, $lcUsuario);
			unset($loMediFor);
			break;
	}
}
include __DIR__ .'/../../publico/headJSON.php';
echo json_encode($laRetorna);