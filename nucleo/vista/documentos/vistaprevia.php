<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ .'/../../controlador/class.Documento.php';
	$loDocLibro = new NUCLEO\Documento();
	$lcArchivo = 'librohc.pdf';

	ini_set('max_execution_time', 60*60); // 60 minutos de consulta

	$laDatosDoc = $laDatosPortada = [];

	// Viene de HC Fox
	if (isset($_SESSION[HCW_DATA]['datos'])) {
		foreach($_SESSION[HCW_DATA]['datos'] as $loValor){
			$laDatosDoc[] = (array) $loValor;
		}
		if (isset($_SESSION[HCW_DATA]['portada'])) {
			$laDatosPortada = (array) $_SESSION[HCW_DATA]['portada'];
		}
		if (isset($_SESSION[HCW_DATA]['filename'])) {
			if (is_string($_SESSION[HCW_DATA]['filename'])) $lcArchivo = $_SESSION[HCW_DATA]['filename'];
		}
		$lbTodoLab = false;

	// Viene de HC Web
	} else {

		foreach ($_POST as $lcClave => $luValor) {
			if ($lcClave=='datos') {
				$laDatosDoc = json_decode($luValor, true);
			} elseif ($lcClave=='portada') {
				$laDatosPortada = json_decode($luValor, true);
			} elseif ($lcClave=='filename') {
				if (is_string($luValor)) $lcArchivo = $luValor;
			} else {
				$laDatosPortada[$lcClave] = $luValor;
			}
		}

		$lbTodoLab = isset($laDatosPortada['cFiltro']) ? strlen($laDatosPortada['cFiltro'])==0 || in_array($laDatosPortada['cFiltro'], ['DOCUMENTO(S): Adjuntos','DOCUMENTO(S): Laboratorio']) : false;
	}
	unset($_SESSION[HCW_DATA]);

	$lcPassword = null;
	$lbSinUsuario = false;
	$lcUsuario = '';
	$lbIncluirAdjExtraInst = false;
	$loDocLibro->generarVariosPDF($laDatosDoc, $laDatosPortada, $lcArchivo, 'I', $lcPassword, $lbSinUsuario, $lcUsuario, $lbIncluirAdjExtraInst, $lbTodoLab);


} else {
	$lcLocation = 'Location: '.($goDb->soWindows ? '../../../index.php?sesion=cerrada' : '/salir');
	header($lcLocation);
	die();
}
