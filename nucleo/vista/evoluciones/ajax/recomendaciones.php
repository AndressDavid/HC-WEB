<?php
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ?? '';
	
	require_once __DIR__ . '/../../../controlador/class.ParametrosConsulta.php';
	$loEvoluciones = new NUCLEO\ParametrosConsulta;

	switch ($lcAccion) {
			
		case 'listasRecomendaciones':
			$loEvoluciones->ObtenerGruposMedicamentosUci();
			$laRetorna['grupoMedicamentosUci'] = $loEvoluciones->TiposGrupoMedicamentoUci();
			break;
		
		case 'listaMedicamentos':	
			$loEvoluciones->ObtenerMedicamentosUci();
			$laRetorna['medicamentosUci'] = $loEvoluciones->TiposMedicamentosUci();
			break;
		
		case 'ConsultaMedicamentosUcc':	
			$lnIngreso = $_POST['lnIngreso'] ?? '';
			$laRetorna['medicamentosUCC'] = $loEvoluciones->ConsultarMedicamentosUcc($lnIngreso);
			break;
					
	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
