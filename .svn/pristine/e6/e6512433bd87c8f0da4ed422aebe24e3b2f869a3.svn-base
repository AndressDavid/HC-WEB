<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['lcListadosOrdenHosp'] ;
	
	if (isset($_POST['lcDatosRecibeGuardar'])) {
		$lcDatosPlanGuarda = $_POST['lcDatosRecibeGuardar'] ;
	}	
	
	if (isset($_POST['lcCodigoEnviar'])) {
		$lcCodigoAEnvia =  $_POST['lcCodigoEnviar'] ;
	}
	
	require_once __DIR__ . '/../../../controlador/class.OrdenHospitalizacion.php';
	$loPlanManejo = new NUCLEO\OrdenHospitalizacion;
	$retornaValor = true;
	$laRetorna['TIPOS']=[];

	switch($lcAccion)
	{
		case 'cargarEspecialidadesOrdenHos':
			$retornaValor = false;
			$laRetorna['TIPOS'] = $loPlanManejo->ObtenerEspecialidadesOrdenHospitaliza();
			break;
	
		case 'cargarAreasOrdenHos':
			$retornaValor = false;
			$laRetorna['TIPOS'] = $loPlanManejo->ObtenerAreaHospitaliza();
			break;
	
		case 'cargarMedicosOrdenHos':
			$laTipos = $loPlanManejo->ObtenerMedicosOrdenHospitaliza($lcCodigoAEnvia);
			break;
	
		case 'cargarUbicacionOrdenHos':
			$laTipos = $loPlanManejo->ObtenerUbicacionOrdenHospitaliza($lcCodigoAEnvia);
			break;
		
		case 'verificarOrdenH':
			$retornaValor = false;
			$lnIngreso = $_POST['Ingreso'] ;
			$laRetorna['TIPOS'] = $loPlanManejo->verificarExisteOH($lnIngreso);
			break;

	}
	unset($loPlanManejo);
		
	if ($retornaValor==true){
		foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $laTipo['desc'] ;
		unset($laTipos);		
	}	
	
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
