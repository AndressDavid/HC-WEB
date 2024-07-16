<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	$lcAccion = $_POST['accion'] ?? '';

	switch ($lcAccion)
	{
		/*
		 *	Consulta procedimientos de un cup específico realizados a un ingreso
		 *	@param ingreso: número de ingreso a consultar
		 *	@param cup: código del procedimiento
		 */
		case 'consultaRealizados':
			$lcTipo = $_POST['tipo'] ?? '';
			$lnIngreso = $_POST['ingreso'] ?? 0;

			if ($lnIngreso>0) {
				require_once __DIR__ . '/../../../controlador/class.ListaDocumentos.php';
				$loLista = new NUCLEO\ListaDocumentos();
				$loLista->cargarDatos($lnIngreso, '', 0, '', '', false, false);
				$loLista->obtenerVia($lnIngreso);
				$loLista->obtenerHabitaciones($lnIngreso);

				switch ($lcTipo) {

					// Glucometrías
					case 'GLUCOMETRIAS':
						$lcCupGluc = trim($goDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='GLUCORD' AND ESTTMA=''", null, '903883'));
						$lcWhere = "o.COAORD='{$lcCupGluc}'";
						$loLista->consultarProcedimientos($lnIngreso, $lcWhere);
						$loLista->formatoFechaHora($lnIngreso);
						$laLista = $loLista->obtenerDocumentos();

						$laGlucometrias = $laLista[$lnIngreso]??[];
						foreach ($laGlucometrias as &$laDatos) {
							$laDatos += ['ObsMed'=>'', 'ValorGluc'=>'', 'ObsGluc'=>''];
							$laQuery = $goDb->select('DESPRO')->from('ORDPRO')->where(['INGPRO'=>$lnIngreso, 'CUPPRO'=>$lcCupGluc, 'CORPRO'=>$laDatos['cnsCita'],])->getAll('array');
							if ($goDb->numRows()>0) {
								foreach ($laQuery as $laItem) {
									$laDatos['ObsMed'] .= $laItem['DESPRO'];
								}
							}
							$laQuery = $goDb->select('MAYGLU, MEDGLU, UMEGLU, OBSGLU')->from('ENGLUCO')->where(['INGGLU'=>$lnIngreso, 'CNTGLU'=>$laDatos['cnsCita'],])->getAll('array');
							if ($goDb->numRows()>0) {
								foreach ($laQuery as $laItem) {
									$laDatos['ValorGluc']=$laItem['MAYGLU'].' '.intval($laItem['MEDGLU']).' '.$laItem['UMEGLU'];
									$laDatos['ObsGluc'] .= $laItem['OBSGLU'];
								}
							}
						}
						$laRetorna['REALIZADOS'] = [
							'LISTA' => $laGlucometrias,
							'TIPDOC' => $loLista->cTipoId(),
							'NUMDOC' => $loLista->nNumeroId()
						];
						break;

					// Gases arteriales últimas 48hr
					case 'GASESART48':
						$lnHoras = $_POST['horas'] ?? 48;
						$lcWhere = "o.COAORD='903839' AND (o.FERORD * 1000000 + o.HRLORD > REPLACE(REPLACE(SUBSTR(CHAR(NOW() - {$lnHoras} HOURS), 0, 20), '.', '') ,'-', ''))";
						$loLista->consultarProcedimientos($lnIngreso, $lcWhere);
						$loLista->formatoFechaHora($lnIngreso);
						$laLista = $loLista->obtenerDocumentos();
						$laRetorna['REALIZADOS'] = [
							'LISTA' => $laLista[$lnIngreso] ?? [],
							'TIPDOC' => $loLista->cTipoId(),
							'NUMDOC' => $loLista->nNumeroId()
						];
						break;

					default:
						$lcWhere = '';
						$laRetorna['error'] = 'Tipo de consulta incorrecto';
						break;
				}
				unset($loLista);

			} else {
				$laRetorna['error'] = 'Faltan datos para la consulta';
			}
			break;

		/*
		 *	Consulta procedimientos de un cup específico realizados a un ingreso
		 *	@param ingreso: número de ingreso a consultar
		 *	@param cup: código del procedimiento
		 */
		case 'consultaRealizadosCup':
			$lnIngreso = $_POST['ingreso'] ?? 0;
			$lcCup = $_POST['cup'] ?? '';
			if ($lnIngreso>0 && !empty($lcCup)) {
				require_once __DIR__ . '/../../../controlador/class.Procedimientos.php';
				$loProcedimientos = new NUCLEO\Procedimientos;
				$laRetorna['REALIZADOS'] = $loProcedimientos
					->cargarProcedimientosIngreso(
						$lnIngreso,
						[3, 50, 51, 52, 65, 66, 69],
						['O.COAORD'=>$lcCup]
					);
			} else {
				$laRetorna['error'] = 'Faltan datos para la consulta';
			}
			break;

		default:
			$laRetorna['error'] = 'Accíon no es correcta';
			break;
	}

}
include __DIR__ . '/../../../publico/headJSON.php';
echo json_encode($laRetorna);