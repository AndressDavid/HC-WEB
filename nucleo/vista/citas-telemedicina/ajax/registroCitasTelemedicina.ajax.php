<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Cita.php');
	require_once ( __DIR__ .'/../../../../restapi/client/addons/RestClient/restclient.php');
	
	$lcAccion = trim(strval(isset($_GET['accion'])?$_GET['accion']:''));
	$lcId = (isset($_GET['p'])?$_GET['p']:'');
	$lnId = (isset($_GET['q'])?$_GET['q']:0);
	$lnCita = (isset($_GET['r'])?$_GET['r']:0);
	$lnConsulta = (isset($_GET['s'])?$_GET['s']:0);
	$lnEvolucion = (isset($_GET['t'])?$_GET['t']:0);	
	
	$laWebResoucesConfig	= require __DIR__ . '/../../../privada/webResoucesConfig.php';

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch($lcAccion){
				case 'guardar':
					$laTabla = ['error'=>true, 'status'=>'Par&aacute;metros incompletos o no validos'];
					$loCita = new NUCLEO\Cita('JTM',$lcId, $lnId, $lnCita, $lnConsulta, $lnEvolucion);
					if($loCita->getIngreso()>0){
						if(isset($_POST['ESTADO'])==true && isset($_POST['OBSERVACION'])==true){
							if(intval($_POST['ESTADO'])>=0){
								$lcError = '';
								if($loCita->actualizarEstadoObservacionCita('JTM', $lcId, $lnId, $lnCita, $lnConsulta, $lnEvolucion, $lcError, $_POST['ESTADO'], $_POST['VALORACION'], $_POST['OBSERVACION'], $_POST['FECHA_REALIZA'], $_POST['HORA_REALIZA'])==true){
									$laTabla = ['error'=>false, 'status'=>'Estado y Observaci&oacute;n guardadas'];
								}else{
									$laTabla = ['error'=>true, 'status'=>$lcError];
								}
							}
						}						
					}
					
					break;
				
				default:
					$lcTokenMetodo = 'jg'.md5('ytr::'.date('Ymd'));
					$loAppAPI = new RestClient(['base_url' => $laWebResoucesConfig['pp']['url'].'restapi/server/v1', 'headers' => ['Authorization' => $laWebResoucesConfig['pp']['rest-api-key']], ]);
					$loResult = $loAppAPI->get("uploadesFiles", ['token' => $lcTokenMetodo, 'tipo'=>$lcId, 'documento'=>$lnId, 'cita'=>$lnCita, 'consulta'=>$lnConsulta, 'evolucion'=>$lnEvolucion]);
					
					if(is_object($loResult)==true){
						if($loResult->info->http_code == 200){
							$loResponse = $loResult->decode_response();
							if(is_object($loResponse)==true){
								if($loResponse->error=="false"){
									$laResponse = $loResponse->data;
									if(is_array($laResponse)==true){
										$laTabla = $laResponse;
									}
								}					
							}
						}
					}
								
			}
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
