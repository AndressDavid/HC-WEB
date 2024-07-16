<?php
	require_once (__DIR__ .'/../../publico/headJSON.php') ;
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ .'/../../controlador/class.Ingreso.php') ;
		
	$lnIngreso = (isset($_POST['ingreso'])?$_POST['ingreso']+0:0); settype($lnIngreso,'Integer');
	$laIngreso = array("nIngreso"=>0,"cNombre"=>"", "cEdad"=>"", "nEdad"=>0, "cUbicacion"=>"-", "cEstado"=>"", "cTipoId"=>"", "nId"=>0);
		
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			$loIngreso=new NUCLEO\Ingreso;
			$loIngreso->cargarIngreso($lnIngreso);
						
			$laIngreso["nIngreso"]=$loIngreso->nIngreso;
			$laIngreso["cNombre"]=$loIngreso->oPaciente->getNombreCompleto();
			$laIngreso["cEdad"]=$loIngreso->oPaciente->getEdad();
			$laIngreso["nEdad"]=$loIngreso->oPaciente->getEdad(date('Y-m-d'),'%y');
			$laIngreso["cEstado"]=$loIngreso->cEstado;
			$laIngreso["cTipoId"]=$loIngreso->oPaciente->aTipoId["TIPO"];
			$laIngreso["nId"]=$loIngreso->oPaciente->nId;
			$laIngreso["cUbicacion"]=$loIngreso->oHabitacion->cUbicacion;
		}
	}
	
	echo json_encode($laIngreso);
?>