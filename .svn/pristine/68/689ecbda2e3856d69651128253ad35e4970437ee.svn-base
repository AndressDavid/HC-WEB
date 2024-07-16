<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../../controlador/class.SalasAperturaSimple.php');
	require_once (__DIR__ .'/../../../controlador/class.Ingreso.php');
	
	$lcAccion = trim(strval(isset($_GET['accion'])?$_GET['accion']:''));
	$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
	$loSalasAperturaSimple = new NUCLEO\SalasAperturaSimple();
	
	$lnIngreso = intval(isset($_GET['nIngreso'])?$_GET['nIngreso']:0);
	$lcSala = strval(isset($_GET['cSala'])?$_GET['cSala']:0);

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch($lcAccion){
				case 'paciente':
					if($lnIngreso>0){
						$loIngreso = new NUCLEO\Ingreso;
						$loIngreso->cargarIngreso($lnIngreso);
						$laTabla = ['FECHA'=>$loAplicacionFunciones->formatFechaHora('fecha',$loIngreso->nIngresoFecha),
									'TIPO'=>$loIngreso->oPaciente->aTipoId['TIPO'],
									'NUMERO'=>$loIngreso->oPaciente->nId, 
									'NOMBRE'=>$loIngreso->oPaciente->getNombreCompleto(),
									'NACIO'=>$loAplicacionFunciones->formatFechaHora('fecha',$loIngreso->oPaciente->nNacio),
									'EDAD'=>($loIngreso->aEdad['y']." años, ".$loIngreso->aEdad['m']." meses, ".$loIngreso->aEdad['d']." días"),
									'GENERO'=>$loIngreso->oPaciente->getGenero(),
									'HABITACION'=>$loIngreso->oHabitacion->cSeccion."-".$loIngreso->oHabitacion->cHabitacion];
					}
					
					break;
					
				case 'salas':
					if(!empty($lcSala)){
						$laTabla = $loSalasAperturaSimple->salasPorTipo($lnIngreso, $lcSala);
					}					
					break;	
					
				case 'salas-abiertas':
					if(!empty($lcSala) && $lnIngreso>0){
						$laTabla = $loSalasAperturaSimple->buscarCirugiasTipoSala($lnIngreso, $lcSala);
					}					
					break;	
					
				case 'cse':
					if(!empty($lcSala)){
						$laTabla = $loSalasAperturaSimple->salaCentroServicio($lcSala, 1);
					}					
					break;						
					
				case 'guardar':
					if(isset($_POST['nIngreso'])==true && isset($_POST['cSala'])==true && isset($_POST['cSalaNumero'])==true && isset($_POST['cCentroServicio'])==true){
						$laTabla = $loSalasAperturaSimple->guardarAperturaSalaSimple(intval($_POST['nIngreso']), $_POST['cSala'], $_POST['cSalaNumero'], $_POST['cCentroServicio'], $_SESSION[HCW_NAME]->oUsuario->getUsuario());
					}
					break;		
			}
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
