<?php
NAMESPACE NUCLEO;

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion=$_REQUEST['accion'] ?? '';

	$laNoClase = ['campos'];
	if(!in_array($lcAccion,$laNoClase)){
		require_once __DIR__ . '/../../../controlador/class.ConsultasAs400.php';
		$loExpo=new ConsultasAs400();
	}

	switch ($lcAccion) {

		case 'listaConsultas':
			$laRetorna['lista']=$loExpo->listaConsultas();
			break;

		case 'obtenerParam':
			$laRetorna['param']=$loExpo->obtenerParametrosJson($_POST['codigo'] ?? '', $_POST['titulo'] ?? '');
			$laRetorna['error']=$loExpo->cErr;
			break;

		case 'query':
			$lnCodigo=$_REQUEST['c'] ?? '';
			$laVariables=json_decode($_REQUEST['v'] ?? []);
			$lnOffSet=$_REQUEST['offset'] ?? 0;
			$lnLimit=$lnOffSet+intval($_REQUEST['limit'] ?? 0);
			$laParam=[
				'search'	=> $_REQUEST['search'] ?? '',
				'sort'		=> $_REQUEST['sort'] ?? '',
				'order'		=> $_REQUEST['order'] ?? '',
				'searchable'=> $_REQUEST['searchable'] ?? '',
			];
			$laRetorna+=$loExpo->consultaDatos($lnCodigo, $laVariables, $lnOffSet, $lnLimit, $laParam);
			$laRetorna['error']=$loExpo->cErr;
			break;

		case 'campos':
			$lnCodigo=$_REQUEST['c'] ?? '';
			$lcFileJsonCol = __DIR__ ."/../json/campos{$lnCodigo}.json";
			$laRetorna['campos'] = file_exists($lcFileJsonCol) ? file_get_contents($lcFileJsonCol) : '[]';
			break;

		case 'exportxlsx':
			$lnCodigo=$_REQUEST['c'] ?? '';
			$laVariables=json_decode($_REQUEST['v'] ?? []);
			$laDatos=$loExpo->consultaDatos($lnCodigo, $laVariables);
			if(empty($loExpo->cErr)){
				if (is_array($laDatos)) {
					if (count($laDatos)>0) {
						$loExpo->exportar($laDatos);
						if(empty($loExpo->cErr)){
							exit;
						}
						$laRetorna['error']=$loExpo->cErr;
					} else {
						$laRetorna['error']='No existen datos para exportar.';
					}
				} else {
					$laRetorna['error']='No existen datos para exportar, la consulta no se generó.';
				}
			}
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
