<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../controlador/class.Citas.php');
	require_once (__DIR__ .'/../../../controlador/class.Cups.php');	
	require_once (__DIR__ .'/../../../controlador/class.Medicos.php');	
	require_once (__DIR__ .'/../../../controlador/class.Cups.php');	
	require_once (__DIR__ .'/../../../controlador/class.Paciente.php');		
	
	$lcAccion = trim(strval(isset($_GET['accion'])?$_GET['accion']:''));
		
	$loCitasTelemedicina = new NUCLEO\Citas();

	$laTabla=array();
	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			switch($lcAccion){
				case 'procedimientos-especialidad':
					if(isset($_POST['ESPECIALIDAD'])==true){
						if(empty($_POST['ESPECIALIDAD'])==false){
							$laTabla = (new NUCLEO\Cups())->buscarCups('0',true,$_POST['ESPECIALIDAD']);
						}
					}
					break;
					
				case 'medicos-especialidad':
					if(isset($_POST['ESPECIALIDAD'])==true && $_POST['TIPOS']){
						$_POST['ESPECIALIDAD'] = (empty($_POST['ESPECIALIDAD'])?'*':$_POST['ESPECIALIDAD']);
						if(empty($_POST['TIPOS'])==false){
							$laTabla = (new NUCLEO\Medicos())->buscarListaMedicos($_POST['ESPECIALIDAD'],$_POST['TIPOS']);
						}
					}
					break;			

				case 'listar-citas-no-telemedicina':
					$lcDocumentoAgregar = (isset($_GET['cDocumentoAgregar'])?strval($_GET['cDocumentoAgregar']):'');
					$lnDocumentoAgregar = (isset($_GET['nDocumentoAgregar'])?intval($_GET['nDocumentoAgregar']):0);	
					
					if(empty($lcDocumentoAgregar)==false && empty($lnDocumentoAgregar)==false){
						$laTabla=$loCitasTelemedicina->buscarCitasPaciente($lcDocumentoAgregar, $lnDocumentoAgregar);
					}
					break;
					
				case 'programa-cita-telemedicina-obtener-paciente':					
					$lcDocumentoAgregar = (isset($_POST['cDocumentoAgregar'])?strval($_POST['cDocumentoAgregar']):'');
					$lnDocumentoAgregar = (isset($_POST['nDocumentoAgregar'])?intval($_POST['nDocumentoAgregar']):0);	
					
					if(empty($lcDocumentoAgregar)==false && empty($lnDocumentoAgregar)==false){
						$loPaciente = new NUCLEO\Paciente();
						$loPaciente->cargarPaciente($lcDocumentoAgregar, $lnDocumentoAgregar);
						$laTabla=['NOMBRE'=>$loPaciente->getNombreCompleto()];
					}
					break;		

				case 'programa-citas-telemedicina':					
					if(isset($_POST['CITAS'])){
						foreach($_POST['CITAS'] as $laCita){
							$llResult = $loCitasTelemedicina->crearCitaTelemedicina($_SESSION[HCW_NAME]->oUsuario->getUsuario(),
																		$laCita['DOCUMENTO_TIPO'], $laCita['DOCUMENTO'], $laCita['CITA'], $laCita['CONSULTA'], $laCita['EVOLUCION'], $laCita['INGRESO'], $laCita['ESP_CODIGO_CITA'], 
																		$laCita['PRO_CODIGO'], $laCita['FCOCIT'], $laCita['CITA_FECHA'], $laCita['CITA_HORA'], $laCita['REGISTRO_MEDICO'], $laCita['RELIZA_FECHA'], $laCita['RELIZA_HORA'], 
																		$laCita['CONCIT'], $laCita['ESTCIT'], $laCita['ESCCIT'], $laCita['INSCIT'], $laCita['VIACIT']);
							$laTabla[]=['add'=>$llResult];
						}
					}
					break;						
					
				default:
					$lnIngreso = (isset($_GET['nIngreso'])?intval($_GET['nIngreso']):0); 
					$lcDocumento = (isset($_GET['cDocumento'])?strval($_GET['cDocumento']):'');
					$lnDocumento = (isset($_GET['nDocumento'])?intval($_GET['nDocumento']):0);
					$lcEspecialidad = (isset($_GET['cEspecialidad'])?strval($_GET['cEspecialidad']):'');
					$lcProcedimiento = (isset($_GET['cProcedimiento'])?strval($_GET['cProcedimiento']):'');
					$lcMedico = (isset($_GET['cMedico'])?strval($_GET['cMedico']):'');
					$lcInicio = (isset($_GET['inicio'])?strval($_GET['inicio']):'');
					$lcFin = (isset($_GET['fin'])?strval($_GET['fin']):'');
					$lcEstado = (isset($_GET['cEstado'])?strval($_GET['cEstado']):'');
					$lcEspeciales = (isset($_GET['cEspeciales'])?strval($_GET['cEspeciales']):'');
					
					$laTabla=$loCitasTelemedicina->buscarCitasTelemedicina($lnIngreso, $lcDocumento, $lnDocumento, $lcEspecialidad, $lcProcedimiento, $lcInicio, $lcFin, $lcEstado, $lcEspeciales, $lcMedico);
					break;
			}
		}
	}

	include (__DIR__ .'/../../../publico/headJSON.php');
	print(json_encode($laTabla??''));

?>
