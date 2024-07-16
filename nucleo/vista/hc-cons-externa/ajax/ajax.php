<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {


	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {
		
		case 'pacientes':
			require_once __DIR__ . '/../../../controlador/class.ConsultaExterna.php';


			switch($_POST['origSol']){
				case 'int':
					$lsParamBusqueda ='HIS001';
					break;
				case 'cex':
					$lsParamBusqueda ='RIA100ORD';
					break;
				case 'proest':
					$lsParamBusqueda ='933601';
					break;
			} 

			// if( $_POST['origSol'] == 'int'){$lsParamBusqueda ='HIS001';}
			// if( $_POST['origSol'] == 'cex'){$lsParamBusqueda ='RIA100ORD';}
		

			$laRetorna['datos'] = (new NUCLEO\ConsultaExterna())
				->consultaPacientes(
					$_POST['ingreso']??0,
					$_POST['tipoId']??'',
					$_POST['numId']??0,
					str_replace('-','',$_POST['fechaini']??''),
					str_replace('-','',$_POST['fechafin']??''),
					'', '',  $_POST['codesp']??'',
					$_POST['regmed']??'',
					$_POST['estado']??''
					,'',$lsParamBusqueda,$_POST['origSol']
				);

			$ltAhora = new \DateTime($goDb->fechaHoraSistema());
			$laRetorna['fechahora'] = str_replace(' ','T',$ltAhora->format('Y-m-d H:i:s'));
			break;

		case 'consultas':
			require_once __DIR__ . '/../../../controlador/class.Historia_Clinica_Ingreso.php';
			$laRetorna['ingreso'] = (new NUCLEO\Historia_Clinica_Ingreso())
				->datosIngreso($_POST['ingreso']??0);
			require_once __DIR__ . '/../../../controlador/class.ConsultaExterna.php';
			$laRetorna['datos'] = (new NUCLEO\ConsultaExterna())
				->consultasPorPaciente(
					$_POST['tipoId']??'',
					$_POST['numId']??0
				);
			break;

		case 'medicos':
			require_once __DIR__ . '/../../../controlador/class.Medicos.php';
			$laRetorna['datos'] = (new NUCLEO\Medicos())->buscarListaMedicos($_POST['codesp']??'', $_POST['tipos']??'');
			break;

		case 'validarNueva':
			require_once __DIR__ . '/../../../controlador/class.ConsultaExterna.php';
			$laRetorna['datos'] = (new NUCLEO\ConsultaExterna())
				->validarNuevaConsulta(
					$_POST['ingreso']??0,
					$_POST['codesp']??'',
					$_POST['fecrea']??''
				);
			if (! $laRetorna['datos']['valido']) {
				$laRetorna['error'] = $laRetorna['datos']['mensaje'];
			}
			break;
		case 'listas':
			require_once __DIR__ . '/../../../controlador/class.ConsultaExterna.php';
			$laRetorna['estados'] = (new NUCLEO\ConsultaExterna())->estadosProcedimientos();
			require_once __DIR__ . '/../../../controlador/class.Especialidades.php';
			$laRetorna['especialidades'] = (new NUCLEO\Especialidades('DESESP', true))->aEspecialidades;
			require_once __DIR__ . '/../../../controlador/class.Medicos.php';
			$laRetorna['medicos'] = (new NUCLEO\Medicos())->buscarListaMedicos($_POST['codesp']??'', $_POST['tipos']??'');
			break;
			case 'consultarInterconsultaAtendida':
				require_once __DIR__ . '/../../../controlador/class.Doc_Interconsulta.php';
				$laRetorna['datos'] = (new NUCLEO\Doc_Interconsulta())
				->consultarInterconsultaAtendida($_POST);						
			break;				
	}
}

include __DIR__ .'/../../../publico/headJSON.php';

echo json_encode($laRetorna);
