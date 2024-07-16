<script type="text/javascript">
<?php
	$laUser = [
		'user'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
		'regm'=>$_SESSION[HCW_NAME]->oUsuario->getRegistro(),
		'name'=>$_SESSION[HCW_NAME]->oUsuario->getNombreCompleto(),
		'cesp'=>$_SESSION[HCW_NAME]->oUsuario->getEspecialidad(),
		'tipo'=>$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(),
	];
	if ($lbObtenerVias??false) {
		$laUser['vias']=explode(',',$goDb->obtenerTabMae1('DE2TMA', 'FORMEDIC', 'CL1TMA=\'VIASPERM\' AND ESTTMA=\'\'', null, '5,6'));
	}
	echo 'var goUser = btoObj(\'' . base64_encode(json_encode($laUser)) . '\');' . PHP_EOL;
?>
</script>