<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lnNroIngreso = $_POST['lnIngreso'] ?? 0;
	$lcProcedimiento = $_POST['lcProcedimiento'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.OrdenesMedicas.php';
	$loOrdenesMedicas = new NUCLEO\OrdenesMedicas;
	
	if (in_array($lcAccion, ['Reglas', 'Label'])) {
		$lcTituloC = $_POST['lcTitulo'] ;
		$loOrdenesMedicas->ObjetosObligatoriosOM($lcTituloC);
		$laObjetos = $loOrdenesMedicas->ObjObligatoriosOM() ;
	}
	
	switch ($lcAccion) {
		case 'Reglas':
			$laRetorna['REGLAS'] = $laObjetos;
		break;
		
		case 'consultarHemocomponenteOrdenado':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaHemocomponenteOrdenado($lnNroIngreso,$lcProcedimiento) ;
			break;
			
		case 'consultarListaJustificacion':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaListaJustificacion($lcProcedimiento) ;
			break;
			
		case 'consultartiporeserva':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaTipoReserva() ;
			break;
		
		case 'consultarhemoclasificacion':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaHemoclasificacion() ;
			break;
		
		case 'consultarriesgotransfucional':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultariesgotransfucional() ;
			break;

		case 'ayudaTipoReserva':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaAyudaTipoReserva() ;
			break;

		case 'ayudaRiesgoTransfucional':
			$laRetorna['TIPOS']=$loOrdenesMedicas->consultaAyudaRiesgoTransfucional() ;
			break;
			
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
