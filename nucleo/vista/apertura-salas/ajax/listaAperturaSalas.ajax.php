<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.SalasAperturaSimple.php');	
	require_once (__DIR__ .'/../../../controlador/class.Ingreso.php');	
	
	$lcAccion = trim(strval(isset($_GET['accion'])?$_GET['accion']:''));
		
	$loSalasAperturaSimple = new NUCLEO\SalasAperturaSimple();

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch($lcAccion){			
				case 'apertura-saladas-obtener-paciente':
					$lnIngreso = intval(isset($_GET['nIngreso'])?$_GET['nIngreso']:0);
					$lcDocumento = (isset($_GET['cDocumento'])?strval($_GET['cDocumento']):'');
					$lnDocumento = (isset($_GET['nDocumento'])?intval($_GET['nDocumento']):0);					
					
					if($lnIngreso>0){
						$loIngreso = new NUCLEO\Ingreso;
						$loIngreso->cargarIngreso($lnIngreso);
						$laTabla = ['TIPO'=>$loIngreso->oPaciente->aTipoId['TIPO'],'NUMERO'=>$loIngreso->oPaciente->nId, 'NOMBRE'=>$loIngreso->oPaciente->getNombreCompleto()];
					}else{
						$loPaciente = new NUCLEO\Paciente;
						$loPaciente->cargarPaciente($lcDocumento, $lnDocumento, $lnIngreso);
						$laTabla = ['TIPO'=>$loPaciente->aTipoId['TIPO'],'NUMERO'=>$loPaciente->nId, 'NOMBRE'=>$loPaciente->getNombreCompleto()];
					}
					
					break;
					
				default:
					$lnIngreso = (isset($_GET['nIngreso'])?intval($_GET['nIngreso']):0); 
					$lcDocumento = (isset($_GET['cDocumento'])?strval($_GET['cDocumento']):'');
					$lnDocumento = (isset($_GET['nDocumento'])?intval($_GET['nDocumento']):0);
					$lcInicio = (isset($_GET['inicio'])?strval($_GET['inicio']):'');
					$lcFin = (isset($_GET['fin'])?strval($_GET['fin']):'');
					$lcEstado = (isset($_GET['cEstado'])?strval($_GET['cEstado']):'');
					$lcSala = (isset($_GET['cSala'])?strval($_GET['cSala']):'');
					$lcCentroServicio = (isset($_GET['cCentroServicio'])?strval($_GET['cCentroServicio']):'');
					
					$laTabla=$loSalasAperturaSimple->buscarCirugias($lnIngreso, $lcDocumento, $lnDocumento, $lcInicio, $lcFin, $lcEstado, $lcSala, $lcCentroServicio);
					break;
			}
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
