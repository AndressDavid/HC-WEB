<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

$lcSessionStorage ='';

if ($lnContinuar) {
	$loUsuHCWeb=[
		'usuario'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
	//	'nombre'=>$_SESSION[HCW_NAME]->oUsuario->getNombres(),
	//	'apellido'=>$_SESSION[HCW_NAME]->oUsuario->getApellidos(),
	//	'registro'=>$_SESSION[HCW_NAME]->oUsuario->getRegistro(),
		'tipo'=>$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(false),
		'especialidad'=>$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(false),
	];

    $lcSessionStorage = base64_encode(json_encode($loUsuHCWeb));
}

include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($lcSessionStorage);