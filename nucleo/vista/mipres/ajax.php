<?php
namespace NUCLEO;

// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ . '/../../controlador/class.MiPresFunciones.php';

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';

	switch ($lcAccion) {

		// Consulta a MiPres y retorna resultado
		case 'mipres':
			$laRetorna = MiPresFunciones::fnConsumirMiPres($_POST, 'GET');
			if (isset($_POST['url'])) {
				if (stristr($_POST['url'],'DireccionamientoX')) {
					if (isset($laRetorna['MIPRES'])) {
						$laResp = MiPresFunciones::fcGuardarDireccionamiento($laRetorna['MIPRES']);
					}
				}
			}
			break;

		// PUT a MiPres Dispensador Proveedor
		case 'mipresput':
			$laRetorna = MiPresFunciones::fnConsumirMiPres($_POST, 'PUT');
			break;

		// Retorna los diferentes tipos de consultas
		case 'tipos':
			$laRetorna = MiPresFunciones::fcListaTiposConsulta($_POST['cod']);
			break;

		// Retorna opciones para un tipo de consulta
		case 'opctipo':
			$laRetorna =  MiPresFunciones::fcOpcionesTipoConsulta($_POST['cod']);
			break;

		// Retorna datos de controles PUT
		case 'ctrlput':
			$laRetorna = MiPresFunciones::fcControlesPUT($_POST['cod']);
			break;

		// Retorna variables
		case 'variables':
			$lnTipo = $_POST['tipo'] ?? 1 ;
			$laRetorna = MiPresFunciones::fcObtenerVariables('0100000'.$lnTipo);
			break;

		// Retorna url MiPres
		case 'urlmipres':
			$lnTipo = $_POST['tipo'] ?? 1 ;
			$laRetorna =  MiPresFunciones::fcObtenerUrlMiPres('0100000'.$lnTipo);
			break;

		// Retorna token temporal
		case 'tkntmp':
			$lcTipo = $_POST['tipo'] ?? '';
			$laRetorna = MiPresFunciones::fcObtenerTokenTmp($lcTipo);
			break;

		// Nuevo token temporal
		case 'gentkntmp':
			$lcTipo = $_POST['tipo'] ?? '';
			$laRetorna['URL'] = MiPresFunciones::fcGenerarTokenTmp($lcTipo);
			break;

		// Exportar rango de fechas a excel
		case 'expoXls':
			$lnFecIni = $_POST['fi'] ?? 0;
			$lnFecFin = $_POST['ff'] ?? 0;

			if (!empty($lnFecIni) && !empty($lnFecFin)) {

				$laResumen = $laAnulaciones = $laDispensacion = [];
				if ($lbRta = MiPresFunciones::datosExportar($lnFecIni, $lnFecFin, $laResumen, $laDispensacion, $laAnulaciones)) {
					MiPresFunciones::exportarExcel($laResumen, $laDispensacion, $laAnulaciones);
					exit;
				} else {
					$laRetorna['error'] = 'No se pudieron consultar los datos para exportar.';
				}
			} else {
				$laRetorna['error'] = 'No se recibieron correctamente las fechas de consulta.';
			}
			break;

		// Obtiene datos de prescripción para facturación
		case 'datpres':
			$lcNumPrs = trim($_POST['numprs']);
			$lcTipTec = trim($_POST['tiptec']);
			$lnConTec = trim($_POST['contec']);
			$laDir = MiPresFunciones::obtenerDirDesdePrsc($lcNumPrs, $lcTipTec, $lnConTec);
			$laEnt = MiPresFunciones::obtenerEntDesdePrsc($lcNumPrs, $lcTipTec, $lnConTec);
			$laPrs = MiPresFunciones::obtenerPrescripcion($lcNumPrs, $lcTipTec, $lnConTec);
			$laRetorna = [
				'error'=>'',
				'TipoIDPaciente'=>$laPrs['TIPOIDPACIENTE'] ?? $laDir['TIPOIDPACIENTE'] ?? $laEnt['TIPOIDPACIENTE'] ?? '',
				'NoIDPaciente'=>$laPrs['NOIDPACIENTE'] ?? $laDir['NOIDPACIENTE'] ?? $laEnt['NOIDPACIENTE'] ?? '',
				'NoIDEPS'=>$laDir['NOIDEPS'] ?? '',
				'CodEPS'=>$laDir['CODEPS'] ?? $laPrs['CODEPS'] ?? '',
				'CodSerTecAEntregado'=>$laEnt['CODSERTEC'] ?? '',
			];
			break;

		// Obtiene datos de factura
		case 'datfac':
			$lcNumFac = trim($_POST['numfac']);
			$laFac = MiPresFunciones::obtenerDatosFac($lcNumFac);
			$laRetorna = [
				'error'=>'',
				'Nit'=>$laFac['NIT'] ?? '',
				'CuotaM'=> (( $laFac['CODIGO']??'' ) == 'CUOTAM') ? ( $laFac['VALOR'] ?? 0 ) : 0,
				'Copago'=> (( $laFac['CODIGO']??'' ) == 'COPAGO') ? ( $laFac['VALOR'] ?? 0 ) : 0,
				'Ingreso'=>$laFac['INGRESO'] ?? '',
			];
			break;

		// Obtener datos de facturacion de un ingreso
		case 'datfacing':
			$lnIngreso = $_POST['ingreso']??0;
			if (!empty($lnIngreso)) {
				$laFac =  MiPresFunciones::obtenerDatosFacIng($lnIngreso);
				$laRetorna = [
					'error'=>'',
					'Nit'=>$laFac['NIT'] ?? '',
					'Ingreso'=>$laFac['INGRESO'] ?? '',
					'Factura'=>$laFac['FACTURA'] ?? '',
					'CuotaM'=> (( $laFac['CODIGO']??'' ) == 'CUOTAM') ? ( $laFac['VALOR'] ?? 0 ) : 0,
					'Copago'=> (( $laFac['CODIGO']??'' ) == 'COPAGO') ? ( $laFac['VALOR'] ?? 0 ) : 0,
				];
			} else {
				$laRetorna = ['error'=>'¡Se debe indicar un ingreso para consultar facturación!',];
			}
			break;

		// Obtener datos de facturacion de un ingreso tecnología
		case 'datfactec':
			$lnIngreso = $_POST['ingreso']??0;
			if (!empty($lnIngreso)) {
				$laFac =  MiPresFunciones::obtenerDatosFacTecno($lnIngreso, $_POST['numpres']??'', $_POST['tiptec']??'', $_POST['contec']??'', $_POST['codigo']??'');
				$laRetorna = [
					'error'=>'',
					'Nit'=>$laFac['NIT'] ?? '',
					'Ingreso'=>$laFac['INGRESO'] ?? '',
					'Factura'=>$laFac['FACTURA'] ?? '',
					'ValorUd'=>($laFac['VALORUD'] ?? '0').'',
					'DifVal'=>$laFac['DIFVR'] ?? 'N',
					'Cantidad'=>$laFac['CANT'] ?? '0',
					'CuotaM'=> (( $laFac['CODIGO']??'' ) == 'CUOTAM') ? ( $laFac['VALOR'] ?? '0' ).'' : '0',
					'Copago'=> (( $laFac['CODIGO']??'' ) == 'COPAGO') ? ( $laFac['VALOR'] ?? '0' ).'' : '0',
				];
			} else {
				$laRetorna = ['error'=>'¡Se debe indicar un ingreso para consultar facturación!',];
			}
			break;

		// Retorna los diferentes tipos de documento
		case 'tiposid':
			require_once __DIR__ . '/../../controlador/class.TiposDocumento.php';
			$laTipos = (new TiposDocumento())->aTipos;
			$laRetorna['TIPOS']=[];
			$lcModo = $_POST['modo'] ?? 'TD'; // T=tipo, D=descripción, TD=tipo y descripción. TD por defecto
			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $lcModo=='T' ? $laTipo['ABRV'] : ($lcModo=='D' ? $laTipo['NOMBRE'] : $laTipo['ABRV'].' - '.$laTipo['NOMBRE']);
			unset($laTipos);
			break;

		// Prescripciones
		case 'prescripciones':
			$laRetorna['data'] = MiPresFunciones::getPrescripciones(
				str_replace('-', '', $_POST['fecini'] ?? ''),
				str_replace('-', '', $_POST['fecfin'] ?? ''),
				$_POST['numprs'] ?? '',
				$_POST['ingres'] ?? '',
				$_POST['tipdoc'] ?? '',
				$_POST['numdoc'] ?? '',
				$_POST['codeps'] ?? '',
				$_POST['ambito'] ?? [],
				$_POST['tiptec'] ?? '',
				$_POST['cnstec'] ?? 0
			);
			break;

		// Direccionamientos
		case 'direccionamientos':
			$laRetorna['data'] = MiPresFunciones::getDireccionamientos(
				str_replace('-', '', $_POST['fecini'] ?? ''),
				str_replace('-', '', $_POST['fecfin'] ?? ''),
				$_POST['numprs'] ?? '',
				$_POST['ingres'] ?? '',
				$_POST['tipdoc'] ?? '',
				$_POST['numdoc'] ?? '',
				$_POST['codeps'] ?? '',
				$_POST['ambito'] ?? [],
				$_POST['tiptec'] ?? '',
				$_POST['cnstec'] ?? 0,
				$_POST['numdir'] ?? 0
			);
			break;

		// Datos de Programaciones, Entregas, Reportes de entrega y Reportes de factura
		case 'datosacc':
			$laRetorna['data'] = MiPresFunciones::getAcciones($_POST['tipo'] ?? '', $_POST['numid'] ?? '');
			break;

		// CUM de un medicamento - ingreso
		case 'cum':
			$laRetorna['data'] = MiPresFunciones::obtenerCUM($_POST['ingreso'] ?? 0, $_POST['numpres'] ?? '', $_POST['tipo'] ?? '', $_POST['consec'] ?? 0, $_POST['codsha'] ?? '');
			break;

		// CUM de un medicamento - ingreso
		case 'cantent':
			$laRetorna['data'] = MiPresFunciones::cantidadFactPres($_POST['ingreso'] ?? 0, $_POST['numpres'] ?? '', $_POST['tipo'] ?? '', $_POST['consec'] ?? 0, $_POST['codsha'] ?? '');
			break;

		// Retorna lista de EPS
		case 'eps':
			$laRetorna['data'] = MiPresFunciones::getListaEPS();
			break;

		// Actualiza entrega codigos
		case 'actEntCod':
			$laRetorna['data'] = MiPresFunciones::actualizaEntCod($_POST['numprs'] ?? '', $_POST['ident'] ?? '');
			break;

		// Retorna fecha de egreso para un ingreso dado
		case 'fecegre':
			global $goDb;
			$lnIngreso =  $_POST['ingreso'] ?? 0;
			$laIngreso = $goDb->select('FEEING')->from('RIAING')->where(['NIGING'=>$lnIngreso])->get('array');
			$laRetorna['data'] = ['INGRESO'=>$lnIngreso, 'FECHA_EGRESO'=>$laIngreso['FEEING']];
			break;

	}
}

include __DIR__ .'/../../publico/headJSON.php';
echo json_encode($laRetorna);
