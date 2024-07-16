<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';
	require_once __DIR__ . '/../../../controlador/class.EscalasRiesgoSangrado.php';
	$loCrusade = new NUCLEO\EscalasRiesgoSangrado;

	switch ($lcAccion){

		case 'cargarDatosEsCrusade':
			$lnIngreso = $_POST['ingreso'];
			$laRetorna = $loCrusade->cargarDatosEsCrusade($lnIngreso);
			unset($loCrusade);
			break;

		case 'calcularPuntajeCreatinina':
			$lnValorCreatinina = $_POST['lnValorCreatinina'];
			$lnPeso = $_POST['lnPeso'];
			$lnEdad = $_POST['lnEdad'];
			$lcSexo = $_POST['lcSexo'];
			$laRetorna = $loCrusade->calcularPuntajeCreatinina($lnValorCreatinina, $lnPeso, $lnEdad, $lcSexo);
			unset($loCrusade);
			break;

		case 'ConsultarEscala':
			$lnIngreso = $_POST['ingreso'] ?? '';
			$laRetorna['DATOS'] = $loCrusade->ConsultarEscalaSangrado($lnIngreso, 3);
			unset($loCrusade);
			break;
	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);