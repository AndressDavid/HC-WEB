<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion= (isset($_GET['accion'])?$_GET['accion']:(isset($_POST['accion'])?$_POST['accion']:''));
	$lnFactura= $_POST['lnNroFactura'] ?? 0;
	$lcDocumentoTipo= $_POST['lcTipoDocumento'] ?? '';

	require_once __DIR__ . '/../../../controlador/class.Rips_factura.php';
	$loFacturacion = new NUCLEO\Rips_factura();
	
	switch ($lcAccion) {
		case 'consultarips':
			$laRetorna['TIPOS']=$loFacturacion->consultarDatosRips($lnFactura,$lcDocumentoTipo);
			break;
			
		case 'consultatipodocumentos':
			$laRetorna['TIPOS']=$loFacturacion->consultarTiposDocumentos();
			break;

	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
