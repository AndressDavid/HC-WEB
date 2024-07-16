<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../../controlador/class.Formulas.php';
	$lcFormula = $_POST['formula'];

	switch($lcFormula)
	{
		case 'SuperficieCorporal':
			$lnPeso = $_POST['peso'];
			$lnTalla = $_POST['talla']??0;
			$lcMetodo = $_POST['metodo']??'';
			$laRetorna['valor'] = NUCLEO\Formulas::SuperficieCorporal($lnPeso, $lnTalla, $lcMetodo);
			break;

		case 'IMC':
			$lnPeso = $_POST['peso'];
			$lnTalla = $_POST['talla'];
			$laRetorna['valor'] = NUCLEO\Formulas::IMC($lnPeso, $lnTalla);
			break;

		case 'PesoIdeal':
			$lnTalla = $_POST['talla'];
			$lcSexo = $_POST['sexo']??'';
			$lcMetodo = $_POST['metodo']??'';
			$laRetorna['valor'] = NUCLEO\Formulas::PesoIdeal($lnTalla, $lcSexo, $lcMetodo);
			break;

		case 'PesoAjustado':
			$lnPeso = $_POST['peso'];
			$lnPesoIdeal = $_POST['pesoideal'];
			$laRetorna['valor'] = NUCLEO\Formulas::PesoAjustado($lnPeso, $lnPesoIdeal);
			break;

		case 'PesoIdealAjustado':
			$lnPeso = $_POST['peso'];
			$lnTalla = $_POST['talla'];
			$lcSexo = $_POST['sexo']??'';
			$lcMetodo = $_POST['metodo']??'';
			$laRetorna['valor'] = NUCLEO\Formulas::PesoIdealAjustado($lnPeso, $lnTalla, $lcSexo, $lcMetodo);
			break;

	}
	unset($laObjetos);
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
