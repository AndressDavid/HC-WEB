<?php
	
	require_once __DIR__.'/../../publico/constantes.php';
	$laRetorna['error'] ='';
	$nSalir = 0;
	if(isset($_SESSION[HCW_NAME])){
		if($_SESSION[HCW_NAME]->oUsuario->getSesionActiva() !== true){
			$laRetorna['error'] = 'El usuario no tiene sesión activa';
			$nSalir = 1;
		}else{
			$laTiposUsuariosAutorizados = [7, 9, 91];
			$lnTipoUsuario = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
			if(in_array($lnTipoUsuario, $laTiposUsuariosAutorizados)){
				$nSalir = 0;
			}else{
				$laRetorna['error'] = 'El tipo de usuario no esta autorizado para acceder';
				echo 'El tipo de usuario no esta autorizado para acceder';
				$nSalir = 1;
			}
		}
	}else{
		$laRetorna['error'] = 'Error en la sesion. Intente nuevamente.';
		$nSalir = 1;
	}
	if($nSalir == 0 )	{
		
		//$lcAccion = isset($_POST['accion']) ?? '';
		$lcAccion = $_POST['accion'] ?? '';

		
		switch ($lcAccion){
			
			case	'listaUbicaciones':
				require_once __DIR__ .'/../../controlador/class.UbicacionesTriage.php';
				$loUbicacionesTriage = new NUCLEO\UbicacionesTriage;
				$laRetorna = $loUbicacionesTriage->aUbicacionesTriage;
				unset($loUbicacionesTriage);
				break;
				
			/*	
			case 'listaUbicaciones':
				require_once __DIR__ .'/../../controlador/class.Dolor_Toracico.php';
				$loResultado = new NUCLEO\Dolor_Toracico;
				$laRetorna = $loResultado->aDolorToracico;
				unset($loResultado);
				break;			
			*/
/* 			case 'listaUbicaciones':
				require_once __DIR__ .'/../../controlador/class.ConciliacionMedicamentos.php';
				$loResultado = new NUCLEO\ConciliacionMedicamentos;
				$laRetorna = $loResultado->aMedicamentos;
				unset($loResultado);
				break; */
			
		}
/* 		$fp = fopen("Z1.txt", "a" );
					fputs($fp, print_r($laRetorna, true));
					fclose($fp)	; */
	}
	include __DIR__ .'/../../publico/headJSON.php';
	echo json_encode($laRetorna);
?>