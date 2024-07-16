<?php
NAMESPACE NUCLEO;

require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Paciente.php';
	$loPaciente = new Paciente();

	$lcAccion=AplicacionFunciones::fnSanitizar($_POST['accion'] ?? '');

	switch ($lcAccion) {

		case 'consulta':
			$lcTipoDoc = AplicacionFunciones::fnSanitizar($_POST['tipodoc'] ?? '');
			$lnNumeroDoc = AplicacionFunciones::fnSanitizar($_POST['numerodoc'] ?? '');
			$loPaciente->cargarPaciente($lcTipoDoc, $lnNumeroDoc, 0, false);
			$laRetorna['datos'] = [
				'nombre1' => $loPaciente->cNombre1,
				'nombre2' => $loPaciente->cNombre2,
				'apellido1' => $loPaciente->cApellido1,
				'apellido2' => $loPaciente->cApellido2,
				'correo' => $loPaciente->cEmail,
			];
			break;

		case 'guardar':
			$lcTipoDoc = AplicacionFunciones::fnSanitizar($_POST['tipodoc'] ?? '');
			$lnNumeroDoc = AplicacionFunciones::fnSanitizar($_POST['numerodoc'] ?? '');
			$lcCorreoNuevo = AplicacionFunciones::fnSanitizar($_POST['correo'] ?? '');
			$laRetorna['data'] = $loPaciente->actualizarCorreo($lcTipoDoc, $lnNumeroDoc, $lcCorreoNuevo);
			break;

	}
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);