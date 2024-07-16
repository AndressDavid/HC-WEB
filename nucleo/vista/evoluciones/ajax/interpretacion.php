<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lnIngreso = $_POST['ingreso'] ;
	$laRetorna['DATOS']=[];
	$lcTipo = $_POST['tipo'] ;
	
	switch($lcTipo)
	{
		case 'Procedimientos':
			require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
			$loObjeto = (new NUCLEO\Procedimientos($lnIngreso));
			$laRetorna['DATOS']=$loObjeto->getDatosInterpretacion();
			break;
	
		case 'Interpretacion':
			$lcCups = $_POST['Cups'] ;
			$lcEstado = $_POST['Estado'] ;
			$lcCodCit = $_POST['CodCita'] ;
			$lcCodEsp = $_POST['CodEspec'] ;
			require_once __DIR__ . '/../../../controlador/class.Interpretacion.php';
			$loObjeto = (new NUCLEO\Interpretacion($lcCups));
			$laRetorna['RUTA']=$loObjeto->VerificarTipoInterpreta($lcCups, $lnIngreso, $lcEstado, $lcCodCit, $lcCodEsp);
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
