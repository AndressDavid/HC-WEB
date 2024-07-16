<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Bitacoras.php');
	
	$lnConsecutivo = intval(isset($_GET['p'])?$_GET['p']:'0');
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lnIngreso = intval(isset($_GET['r'])?$_GET['r']:'0');
	$loBitacoras = new NUCLEO\Bitacoras($lcTipoBitacora, $lnConsecutivo, $lnIngreso);

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			if(isset($_GET['cabecera'])==true){
				$lnConsecutivo=0;
				
				if(isset($_POST['cEstado'])==true){
					$loBitacoras->setEstado(intval($_POST['cEstado']));
					$lnConsecutivo = ($loBitacoras->insertar($_SESSION[HCW_NAME]->oUsuario->getUsuario())==true?$loBitacoras->getConsecutivo():0);
						
					if($loBitacoras->getConsecutivo()>0 && !empty($_POST['cObservacion'])){
						$loBitacoras->insertarObservacion(0, $_POST['cObservacion'], $_SESSION[HCW_NAME]->oUsuario->getUsuario());
					}
				}
				print($lnConsecutivo);
				
			}else if(isset($_GET['proveedor'])==true){
				if(isset($_POST['cProveedorCodigo'])==true && isset($_POST['cProveedorNombre'])==true){
					$loBitacoras->insertarProveedor(intval($_POST['cProveedorCodigo']), $_POST['cProveedorNombre'],$_SESSION[HCW_NAME]->oUsuario->getUsuario());
				}
				
				include (__DIR__ .'/../../../publico/headJSON.php');
				print(json_encode($loBitacoras->getProveedores()??''));		
				
			}else if(isset($_GET['proveedorEspecifico'])==true){
				$laProveedor = array();
				if(isset($_POST['PROVEEDOR'])==true){
					$laProveedor = $loBitacoras->buscarProveedor(intval($_POST['PROVEEDOR']));
				}
				
				if((is_array($laProveedor)?count($laProveedor)>0:false)==true){
					printf('Existe como <b>%s - %s</b>.',$laProveedor['CODIGO'],$laProveedor['DESCRIPCION']);
				}else{
					print('');
				}
		
				
			}else{
				$lcResultado = 'No se guardo el seguimiento';

				$laSegumientoInicio = explode(' ',(isset($_POST['cSegumientoInicio'])?$_POST['cSegumientoInicio']:'0 0'));
				$laSegumientoFin = explode(' ',(isset($_POST['cSegumientoConfirmacion'])?$_POST['cSegumientoConfirmacion']:'0 0'));							

				$lnIniFecha =  setStrFecha2Int(count($laSegumientoInicio)==2?$laSegumientoInicio[0]:'');
				$lnIniHora = setStrHora2Int(count($laSegumientoInicio)==2?$laSegumientoInicio[1]:'');

				$lnFinFecha = setStrFecha2Int(count($laSegumientoFin)==2?$laSegumientoFin[0]:'');
				$lnFinHora = setStrHora2Int(count($laSegumientoFin)==2?$laSegumientoFin[1]:'');
				
				if(isset($_POST['nSegumientoConsecutivo'])==true && isset($_POST['cSegumientoEstado'])==true && isset($_POST['cSegumientoProveedor'])==true && isset($_POST['cSegumientoObservacion'])==true){				
					$lcResultado=sprintf('%s',($loBitacoras->insertarDetalle(intval($_POST['nSegumientoConsecutivo']), $_POST['cSegumientoEstado'], (isset($_POST['cSegumientoEntidad'])?$_POST['cSegumientoEntidad']:''), $_POST['cSegumientoProveedor'], (isset($_POST['cSegumientoTipo'])?$_POST['cSegumientoTipo']:''), $lnIniFecha, $lnIniHora, $lnFinFecha, $lnFinHora, $_POST['cSegumientoObservacion'],$_SESSION[HCW_NAME]->oUsuario->getUsuario())==true?'Seguimiento guardado':$lcResultado));
				}
				print($lcResultado);
			}
		}
	}
	
	function setStrFecha2Int($tcFecha){
		return intval(str_replace('-','',$tcFecha));
	}
	
	function setStrHora2Int($tcHora){
		$tcHora = trim(strval($tcHora));
		$tcHora = trim(str_replace(':','',$tcHora));	
		$tcHora = trim(str_pad($tcHora,6,'0',STR_PAD_RIGHT));		
		$tcHora = trim(substr($tcHora,0,4));		
		$tcHora = $tcHora.'00';	
		return intval($tcHora);
	}
?>
