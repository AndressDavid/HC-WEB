<?php
NAMESPACE NUCLEO;

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.ImportarAS400.php';
	$loImpo=new ImportarAS400();

	$lcAccion=$_POST['accion'] ?? '';

	switch ($lcAccion) {

		case 'listaConsultas':
			$laRetorna['lista']=$loImpo->listaConsultas();
			break;

		case 'obtenerParam':
			$loImpo->cargarConfig($_POST['codigo'] ?? '');
			$laRetorna['param']=($loImpo->aConfig())['origen'];
			$laRetorna['error']=$loImpo->cErr;
			break;

		case 'importar':
			$lcCodigo = $_POST['codigo'] ?? '';
			$laDatos = json_decode($_POST['datos'] ?? [], true);
			if ($loImpo->validar($lcCodigo, $laDatos)) {
				$laRetorna['data']=$loImpo->importar($laDatos);
			}
			$laRetorna['error']=$loImpo->cErr;
			$laRetorna['aerror']=$loImpo->aErr;
			break;

		case 'temporal':
			$laRetorna['data']=$loImpo->temporal();
			$laRetorna['error']=$loImpo->cErr;
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
