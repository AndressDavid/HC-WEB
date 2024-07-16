<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['lcIngresoPaciente'] ;

	if (isset($_POST['nIngreso'])) {
		$lnIngreso =  $_POST['nIngreso'] ;
	}


	switch($lcAccion)
	{
		case 'consultarIngreso':
			require_once __DIR__ . '/../../../controlador/class.Ingreso.php';
			$loIngreso=new NUCLEO\Ingreso;
			$loIngreso->cargarIngreso($lnIngreso);
			$laRetorna['nIngreso']=$loIngreso->nIngreso;
			$laRetorna['nEntidad']=$loIngreso->nEntidad;
			$laRetorna['cTipId']=$loIngreso->oPaciente->aTipoId['ABRV'];
			$laRetorna['nNumId']=$loIngreso->nId;
			$laRetorna['cDocumento']=$loIngreso->oPaciente->aTipoId['ABRV'] .' '. $loIngreso->nId;
			$laRetorna['cNombre']=$loIngreso->oPaciente->getNombreCompleto();
			$laRetorna['cVia']=$loIngreso->cVia;
			$laRetorna['cDescVia']=$loIngreso->cDescVia;
			$laRetorna['cSexo']=$loIngreso->oPaciente->cSexo;
			$laRetorna['nNacio']=$loIngreso->oPaciente->nNacio;
			$laRetorna['aEdad']=$loIngreso->aEdad;
			$laRetorna['nIngresoFecha']=$loIngreso->nIngresoFecha;
			$laRetorna['cPlan']=$loIngreso->cPlan;
			$laRetorna['Historia']=$loIngreso->oPaciente->nNumHistoria;
			break;

	}

}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
