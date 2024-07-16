<?php

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';
require_once __DIR__ . '/../../../controlador/class.Doc_Interconsulta.php';
require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/../../../controlador/class.Medico.php';
require_once __DIR__ . '/../../../controlador/class.Especialidad.php';

if ($lnContinuar) {
	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {

		case 'atiende_interconsulta':
			
			$laRetorna['ingreso'] = (new NUCLEO\Historia_Clinica_Ingreso())
				->datosIngreso($_POST['ingreso']??0);
			
			$loInterconsultas = new NUCLEO\Doc_Interconsulta();
			$loInterconsultas->consultarDatos( 
				array('nIngreso' =>	$_POST['ingreso'], 
				'nConsecCita' => 	$_POST['numOrd']??0, 
				'cCUP' => 			$_POST['numCUP']??0, 
				'nRegmedRealiza' => $_POST['nRegmedRealiza']??0,					
			));			
			$laRetorna['datosInterconsulta'] = $loInterconsultas->aVar;
			$lnEspeMedSoliOrden = $loInterconsultas->obtieneEspeMedSolilOrden($_POST['ingreso'],$_POST['numOrd']);

			$loEspecialidadMedidcoActual = new NUCLEO\Especialidad( $_SESSION[HCW_NAME]->oUsuario->getEspecialidad() );
			$loMedicoSolicitoInterconsulta =  new NUCLEO\Medico($_POST['RMeOrd']);
			$loMedicoRespondioInterconsulta =  new NUCLEO\Medico($_POST['RMROrd']); 

			$laRetorna['datosUsuarioActual']['Usuario']					 		= $_SESSION[HCW_NAME]->oUsuario->getUsuario();
			$laRetorna['datosUsuarioActual']['Tipousuario'] 					= $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
			$laRetorna['datosUsuarioActual']['EspecialidadMedicoActual'] 		= $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
			$laRetorna['datosUsuarioActual']['NombreEspecialidadMedicoActual'] 	= $loEspecialidadMedidcoActual->cNombre;
			$laRetorna['datosUsuarioActual']['RegMedicoActual'] 				= $_SESSION[HCW_NAME]->oUsuario->getRegistro();
			$laRetorna['datosUsuarioActual']['RequiereAval'] 					= $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
			$laRetorna['datosUsuarioActual']['EsEstudiante'] 					= $loInterconsultas->esEstudiante($_SESSION[HCW_NAME]->oUsuario->getTipoUsuario());
			$laRetorna['datosUsuarioActual']['EsProfesor'] 						= $loInterconsultas->esProfesor($_SESSION[HCW_NAME]->oUsuario->getTipoUsuario());
			$laRetorna['datosUsuarioActual']['soloLectura']                     = $loInterconsultas->modoLectura($_SESSION[HCW_NAME]->oUsuario->getTipoUsuario());
			$laRetorna['datosUsuarioActual']['tieneOtraEspecialidadPermitida'] 	= $loInterconsultas->tieneOtraEspecialidadPermitida($_SESSION[HCW_NAME]->oUsuario->getEspecialidad(),$_POST['codOrd']);
	
				$laRetorna['datosMedicoSolicito']['NombreCompleto'] 			= $loMedicoSolicitoInterconsulta->getNombreCompleto(); 
				$laRetorna['datosMedicoSolicito']['Especialidad'] 				= $loMedicoSolicitoInterconsulta->getEspecialidad(); 
				$laRetorna['datosMedicoSolicito']['RegMedico'] 					= $loMedicoSolicitoInterconsulta->getRegistroMedico();
			
			$laRetorna['datosMedicoRespondio']['NombreCompleto'] 				= $loMedicoRespondioInterconsulta->getNombreCompleto(); 
			$laRetorna['datosMedicoRespondio']['Especialidad'] 					= $loMedicoRespondioInterconsulta->getEspecialidad(); 
			$laRetorna['datosMedicoRespondio']['RegMedico'] 					= $loMedicoRespondioInterconsulta->getRegistroMedico();
				$laRetorna['datosMedicoSolicito']['EspeMedSoliOrden']['cod'] 	= $lnEspeMedSoliOrden['cod'];
				$laRetorna['datosMedicoSolicito']['EspeMedSoliOrden']['nombre']	= $lnEspeMedSoliOrden['nombre'];
				$laRetorna['datosRtasPorAvalar'] 								= $loInterconsultas->consultarUltimRtasParAvalar($_POST['ingreso'], $_POST['numOrd']);
			break;

		case 'guardarRespuestasSeguimiento':

			$laRetorna['datos'] = (new NUCLEO\Doc_Interconsulta())
			->guardarRespuestasSeguimiento($_POST);	
			break;
	}
}

include __DIR__ .'/../../../publico/headJSON.php';

echo json_encode($laRetorna);