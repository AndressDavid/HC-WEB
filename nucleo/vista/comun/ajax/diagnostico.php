<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	require_once __DIR__ . '/../../../controlador/class.Diagnostico.php';

	$lcTipoDiag = $_REQUEST['lcTipoDiagnostico']??[];
	$laRetorna['TIPOS'] = [];

	switch($lcTipoDiag)
	{
		case 'tipo':
			$laTipos = (new NUCLEO\Diagnostico())->TipoDiagnostico();
			break;

		case 'clase':
			$laTipos = (new NUCLEO\Diagnostico())->ClaseDiagnostico();
			break;

		case 'tratamiento':
			$laTipos = (new NUCLEO\Diagnostico())->TratamientoDiagnostico();
			break;

		case 'descarte':
			$laTipos = (new NUCLEO\Diagnostico())->TiposDescarte();
			break;

		case 'ayudaTipoCie':
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->AyudaTipoDiagnostico();
			break;

		case 'ayudaClaseCie':
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->AyudaClaseDiagnostico();
			break;

		case 'ayudaTratamientoCie':
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->AyudaTratamientoDiagnostico();
			break;

		case 'validarDiagnosticos':
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->validacion($lcListaDiagnosticos);
			break;

		case 'consultarValidaCiePrincipal':
			$lcTipoHc = $_REQUEST['lcTipoHc']??[];
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->validacionCiePrincipal($lcTipoHc);
			break;

		case 'consultarValidaCieOrden':
			$lcTipoFiltro =  $_POST['lcTipoCie'] ?? '';
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->validacionCiePrincipalOrden($lcTipoFiltro);
			break;

		case 'consultaDiagnostico':
			$lnIngreso =  $_POST['lnNroIngreso'] ?? 0;
			$lcTipoFiltro =  $_POST['lcTipoCie'] ?? '';
			$laRetorna['TIPOS'] = (new NUCLEO\Diagnostico())->consultaDiagnosticos($lnIngreso, $lcTipoFiltro);
			break;

		case 'consultarDiagnosticos':
			$lcDatosPaciente = $_REQUEST['lcDatosPacientes']??[];
			$lcNombre = NUCLEO\AplicacionFunciones::fnSanitizar(trim($_REQUEST['nombre']??''));
			if(!empty($lcNombre)){
				$lcNombre = explode(' ', str_replace("'", '', $lcNombre));
			}
			$laRetorna = (new NUCLEO\Diagnostico())->consultaListaDiagnosticos($lcNombre,'',false,$lcDatosPaciente);
			break;
	}

	if (in_array($lcTipoDiag, ['tipo','clase','tratamiento','descarte',])) {
		foreach($laTipos as $lcTipo=>$laTipo) {
			$laRetorna['TIPOS'][$laTipo['TABCOD']] = $laTipo['TABDSC'];
		}
		unset($laTipos);
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
