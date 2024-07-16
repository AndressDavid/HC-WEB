<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$laRetorna = array(
		'status' => true,
		'estadoHC'=> $_SESSION['token']
	);

}else{
	$laRetorna = array(
		'status' => false,
		'estadoHC'=> "0000000000"
	);
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);