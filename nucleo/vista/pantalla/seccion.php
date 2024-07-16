<?php
	$lcSeccion=(isset($lcSeccion)?'?seccion='.$lcSeccion:'');
	$laUrl=array(
				 array('vista-alerta-temprana/monitoreo.php'.$lcSeccion,120)
				);
	echo json_encode($laUrl);
?>