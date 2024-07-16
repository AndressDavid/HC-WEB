<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$laRetorna = array(
		'status' => true,
		'IP'=> $_SESSION[HCW_NAME]->oUsuario->getIP(),
        'yearVersion'=>date('Y')
	);

}else{
	$laRetorna = array(
		'status' => false,
		'body'=> "No existe una session activa"
	);
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);