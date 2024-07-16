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
	$laRetorna['error'] = 'Error en la sesión.  Intente nuevamente.';
	$nSalir = 1;
}
if($nSalir == 0){
	$lcUsuario = ($_SESSION[HCW_NAME]->oUsuario->getUsuario());
	$lnIngreso = (isset($_POST['ingreso']) ? intval($_POST['ingreso']) : 0);
	$lcAccion = (isset($_POST['accion']) ? $_POST['accion'] : '');
	require_once __DIR__ .'/../../controlador/class.Ingreso.php';
	$loIngreso = new NUCLEO\Ingreso;
	switch($lcAccion){
		case 'ingreso':
			$loIngreso->cargarIngreso($lnIngreso);
			$laRetorna['cEstado']=$loIngreso->cEstado;
			$laRetorna['nIngreso']=$loIngreso->nIngreso;
			$laRetorna['cNombre']=$loIngreso->oPaciente->getNombreCompleto();
			$laRetorna['nEdad']=$loIngreso->oPaciente->getEdad();
			$loIngreso->obtenerPesoTalla($lnIngreso);
			$laRetorna['nPeso']=$loIngreso->nPeso;
			$laRetorna['cTipoPeso']=$loIngreso->cTipoPeso;
			$laRetorna['aTalla']=$loIngreso->aTalla;
			$laRetorna['cSexo']=$loIngreso->oPaciente->getGenero();
			$laRetorna['cUbicacion']=$loIngreso->oHabitacion->cUbicacion;
			$laRetorna['cVia']=$loIngreso->cVia;
			$laRetorna['cDescVia']=$loIngreso->cDescVia;
			unset($loIngreso);
			break;

			case 'Antecedentes':
			$lcTipo = (isset($_POST['tipoAnte']) ? ($_POST['tipoAnte']) : 0);
			$loIngreso->obtenerAntecedentes($lnIngreso, $lcTipo);
			$laRetorna['nIngreso']=$loIngreso->nIngreso;
			$laRetorna['cEstado']=$loIngreso->cEstado;
			$laRetorna['cTipoAntecedente']=$loIngreso->aAntecedentes['tipoAnte'];
			$laRetorna['cDescripcion']=$loIngreso->aAntecedentes['descripcion'];
			unset($loIngreso);
			break;
	}
}
include __DIR__ .'/../../publico/headJSON.php';
echo json_encode($laRetorna);