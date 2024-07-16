<?php

// manejo de copiar y pegar
$laProp = $_SESSION[HCW_NAME]->oUsuario->getPropiedades();
$lcCopyPaste = (isset($laProp['OPCOPPEG']) ? ($laProp['OPCOPPEG']['TABMAE'] ?? false ) : false) ? '' : 'nocopypaste';


$lcOpcion = $_REQUEST['cp'] ?? '';

switch ($lcOpcion) {
	case 'urg':
		(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'HCURG_WEB', 'INICIO', 0, 'INGRESO HC CONSULTA URGENCIAS', 'HCURG', '', 0);
		include __DIR__ . '/../hc-urgencias/index.php';
		break;

	case 'cex':
		(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'HCCONEXT_WEB', 'INICIO', 0, 'INGRESO HC CONSULTA EXTERNA', 'HCCONEXT', '', 0);
		include __DIR__ . '/../hc-cons-externa/index.php';
		break;

	case 'int':
		(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'INTERCON_WEB', 'INICIO', 0, 'INGRESO HC INTERCONSULTAS', 'INTERCON', '', 0);
		include __DIR__ . '/../hc-cons-externa/index.php';
		break;

	case 'hos':
		(new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'HCHOSP_WEB', 'INICIO', 0, 'INGRESO HC CONSULTA HOSPITALIZADOS', 'HCHOSP', '', 0);
		include __DIR__ . '/../hc-hospitalizado/index.php';
		break;

	case 'epi':
		include __DIR__ . '/../epicrisis/index.php';
		break;

	case 'evo':
		include __DIR__ . '/../evoluciones/index.php';
		break;

	case 'orm':
		include __DIR__ . '/../ordenes-medicas/index.php';
		break;

	case 'rint':
		include __DIR__ . '/../hc-interconsultas/index.php';
		break;

	case 'proest':
		include __DIR__ . '/../hc-cons-externa/index.php';
		break;
	case 'rprocont':
		include __DIR__ . '/../proce-control/index.php';
		break;
	case 'ciru':
		include __DIR__ . '/../cirugia/index.php';
		break;
	default:

		$btnVolver = '<a class="btn btn-secondary btn-sm" href="javascript: history.back()">Volver</a>';
		$divMensaje = '<div class="container-fluid"><div class="card mt-3"><div class="card-header alert-danger"><div class="row"><div class="col"><h5>%s</h5>%s</div></div></div></div></div>';
		$lbReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();

		require_once __DIR__ . '/../../controlador/class.AplicacionFunciones.php';
		require_once __DIR__ . '/../../vista/comun/modalAlertaFallece.php';
		require_once __DIR__ . '/../../vista/comun/modalOrdenHospitalizacion.php';

		$lnIngreso = isset($_SESSION[HCW_DATA]) ? $_SESSION[HCW_DATA]['ingreso'] : NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? 0);

		if ($lnIngreso > 0) {
			$lcDescripcionCup = '';
			require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';

			if (isset($_SESSION[HCW_DATA])) {
				$lcTipoDoc = $_SESSION[HCW_DATA]['tipodoc'];
				$lnNumDoc = $_SESSION[HCW_DATA]['numdoc'];
				$lnCnsCita = $_SESSION[HCW_DATA]['cita'];
				$lnCnsCons = $_SESSION[HCW_DATA]['cons'];
				$lnCnsEvol = $_SESSION[HCW_DATA]['evol'];
				$lcCup = $_SESSION[HCW_DATA]['cup'];
				$lcVia = $_SESSION[HCW_DATA]['via'];
				$lcForm = $_SESSION[HCW_DATA]['form'];
				$lcCodesp = $_SESSION[HCW_DATA]['codesp'] ?? '';
				$lcFecrea = $_SESSION[HCW_DATA]['fecrea'] ?? '';
				$lcMedRealiza = $_SESSION[HCW_DATA]['medrea'] ?? '';
				$llAvalar = $_SESSION[HCW_DATA]['Avalar'] ?? false;
				$llAvalar = is_string($llAvalar) ? $llAvalar=='true' : $llAvalar;
				unset($_SESSION[HCW_DATA]);
			} else {
				$lcTipoDoc = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['tipodoc'] ?? '');
				$lnNumDoc = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['numdoc'] ?? '');
				$lnCnsCita = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cita'] ?? '');
				$lnCnsCons = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cons'] ?? '');
				$lnCnsEvol = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['evol'] ?? '');
				$lcCup = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cup'] ?? '');
				$lcVia = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['via'] ?? '');
				$lcMedRealiza = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['medrealiza'] ?? '');
				$lcForm = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['form'] ?? '');
				$lcCodesp = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['codesp'] ?? '');
				$lcFecrea = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['fecrea'] ?? '');
				$llAvalar = isset($_POST['Avalar']) ? $_POST['Avalar']==='true' : false;
			}

			require_once __DIR__ . '/../../controlador/class.Cup.php';
			$loCup = new NUCLEO\Cup;
			if (!empty($lcCup)){
				$loCup->cargarDatos($lcCup);
				$lcDescripcionCup = $loCup->cDscrCup;
			}

			// Variable que indica teleconsulta
			$lcTeleconsulta = '';

			// Es consulta externa
			$lcCondicionCup = '$llCondicionCup='. trim($goDb->obtenerTabMae1('OP5TMA', 'HCPARAM', 'CL1TMA=\'PROCCEXT\' AND ESTTMA=\'\'', null, 'substr($lcCup,0,4)==\'8903\'')).';';
			eval($lcCondicionCup);
			$lbCondicionCE = ($lcVia=='02' || $llCondicionCup) && empty($lnCnsCons); // AND oVar.cPrgAnt<>'EV0036'

			// verificar si ya tiene historia clínica
			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
			if ($lcForm=='cex') {
				require_once __DIR__ . '/../../controlador/class.ConsultaExterna.php';
				$loConExt = new NUCLEO\ConsultaExterna();
				$loHcCex = $loConExt->validarNuevaConsulta($lnIngreso, $lcCodesp, $lcFecrea);
				$lbExisteHC = !isset($loHcCex['concon']);
				if ($lbExisteHC) {
					$lcTeleconsulta = $loConExt->esTeleconsulta($lnIngreso, $lcTipoDoc, $lnNumDoc, $lnCnsCita);
				}

			} else {
				$laExisteHC = $loHcIng->validaExisteHC($lnIngreso, $lcVia);
				$lbExisteHC = $laExisteHC['Valido'];
			}

			if ($lbExisteHC) {

				(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, $lnCnsCons, $lnCnsCita, $lcCup, 'HISCLI', 'INICIO', 0, 'INGRESO HISTORIA CLÍNICA', 'HISCLI', $lcTipoDoc, $lnNumDoc);

				// Validar que la cita y el cup corresponden al ingreso


				// Datos para Historia Clínica
				$laRetorna = $loHcIng->datosIngreso($lnIngreso);
				if (strlen(trim($laRetorna['cNombre']??''))>0) {
					$laRetorna['nConCita'] = $lnCnsCita;
					$laRetorna['nConCons'] = $lnCnsCons;
					$laRetorna['nConEvol'] = $lnCnsEvol;
					$laRetorna['cCodCup'] = $lcCup;
					$laRetorna['cMedRealiza'] = $lcMedRealiza;
					$laRetorna['cDescripcionCup'] = $lcDescripcionCup;
					$laRetorna['cFormAnterior'] = $lcForm;
					$laRetorna['cCodVia'] = $lbCondicionCE ? '02' : $laRetorna['cCodVia'];
					$laRetorna['Avalar'] = $llAvalar;

					// Datos de Auditoría
					$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
					$laAuditoria['cFechaAud'] = $ltAhora->format('Ymd');
					$laAuditoria['cHoraAud'] = $ltAhora->format('His');
					$laAuditoria['lRequiereAval'] = $lbReqAval;

					// Menu y objetos de HC
					require_once __DIR__ . '/../../controlador/class.ParametrosConsulta.php';
					$laMenu = (new NUCLEO\ParametrosConsulta())->menuHC();
					$lcMenu = '';
					$laObjetos = [];
					$lcActive = 'active';
					$lcActiveo = 'show active';
					$lcSelected = 'aria-selected="true"';
					foreach ($laMenu as $lnClave => $laElem) {
						$lcCondicion = '$llCondicion='.$laElem['CONDICION'].';';
						eval($lcCondicion);
						if ($llCondicion) {
							$laVst = explode('¤',$laElem['TEXTO']);
							$laObj = explode('¤',$laElem['OBJETOS']);
							$lcMenu .= "<a class=\"nav-link $lcActive text-dark\" id=\"{$laObj[0]}\" data-toggle=\"pill\" href=\"#{$laObj[1]}\" role=\"tab\" aria-controls=\"{$laObj[1]}\" $lcSelected data-focus=\"#{$laObj[2]}\">"
									. "<span class=\"fas fa-{$laVst[1]}\"></span> {$laVst[0]}</a>" . PHP_EOL;

							$laIncludes = explode('¬',$laObj[4]);
							$laIncObject = [];
							foreach ($laIncludes as $laInclude) {
								$laIncObject[] = $laObj[3] . $laInclude . '.php';
							}
							$laObjetos[] = [
								"<div class=\"tab-pane $lcActiveo\" id=\"{$laObj[1]}\" role=\"tabpanel\" aria-labelledby=\"{$laObj[0]}\">" . PHP_EOL,
								$laIncObject,
								'</div>' . PHP_EOL,
							];
							$lcActiveo = $lcActive = $lcSelected = '';
						}
					}

					// Datos para vista previa del triage
					$laTriage = [];
					if ($lcVia=='01') {
						$laTabla = $goDb
							->select('TIDTRI,NIDTRI,CNSTRI,FETTRI,HRTTRI,RMETRI')
							->select('(SELECT COUNT(*) FROM TRIAGU WHERE NIGTRI='.$lnIngreso.') NUMEVO')
							->from('TRIAGU')
							->where(['NIGTRI'=>$lnIngreso])
							->orderBy('CNSTRI DESC')
							->get('array');
						if (is_array($laTabla)) {
							$laTriage = [
								'nIngreso' => $lnIngreso,
								'cTipDocPac' => $laTabla['TIDTRI'],
								'nNumDocPac' => $laTabla['NIDTRI'],
								'tFechaHora' => NUCLEO\AplicacionFunciones::formatFechaHora('fechahora',$laTabla['FETTRI']*1000000+$laTabla['HRTTRI']),
								'cTipoDocum' => '2800',
								'cTipoProgr' => 'TRI010',
								'cCUP' => '',
								'cCodVia' => '01',
								'cRegMedico' => $laTabla['RMETRI'],
								'nConsecCita' => '',
								'nConsecCons' => '',
								'nConsecDoc' => $laTabla['CNSTRI'],
								'nConsecEvol' => $laTabla['NUMEVO'],
								'cSecHab' => '',
							];
						}
					}

					// Formulario de HC
					include __DIR__ . '/historiaclinica.php';
					echo '<script type="text/javascript">' . PHP_EOL;
					echo 'var gbEsCE = '.(($laRetorna['cCodVia']=='02' || $lbCondicionCE)?'true;':'false;');
					echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
					echo 'var aAuditoria = btoObj(\'' . base64_encode(json_encode($laAuditoria)) . '\');' . PHP_EOL;
					echo 'var gaTriage = btoObj(\'' . base64_encode(json_encode($laTriage)) . '\');' . PHP_EOL;
					echo '</script>' . PHP_EOL;

				} else {
					printf($divMensaje, 'No se pudieron obtener los datos del paciente. Por favor intente nuevamente ingresar a la historia clínica.', $btnVolver);
				}


			// Mostrar la HC en PDF
			} else {

				if ($lcVia=='02' || $llCondicionCup) {
					$lnCnsCita = '';
					$lcCup = '';
					$lnCnsEvol = '';
				}

				// Obtiene lista de HC del paciente
				require_once __DIR__ . '/../../controlador/class.ListaDocumentos.php';
				$loLista = new NUCLEO\ListaDocumentos();
				$loLista->cargarDatos($lnIngreso, '', 0, '', '', false);
				$loLista->obtenerVia($lnIngreso);
				$loLista->obtenerHabitaciones($lnIngreso);
				$loLista->consultarHistoria($lnIngreso);
				$loLista->consultarHcUrg($lnIngreso);
				$loLista->consultarHcHos($lnIngreso);
				$loLista->consultarHcCex($lnIngreso);
				$laDocs = $loLista->obtenerDocumentos();

				if (count($laDocs[$lnIngreso])>0) {
					foreach ($laDocs[$lnIngreso] as $laDoc) {
						if ($lcForm || ($laDoc['cnsCita']==$lnCnsCita && $laDoc['cnsCons']==$lnCnsCons && $laDoc['cnsEvo']==$lnCnsEvol && $laDoc['codCup']==$lcCup)) {
							$laDatos = [
								'nIngreso'		=> $lnIngreso,
								'cTipDocPac'	=> $lcTipoDoc,
								'nNumDocPac'	=> $lnNumDoc,
								'cRegMedico'	=> '',
								'cTipoDocum'	=> '2000',
								'cTipoProgr'	=> 'HCPPAL',
								'tFechaHora'	=> date('Y-m-d H:i:s', strtotime($laDoc['fecha'])),
								'nConsecCita'	=> $lnCnsCita,
								'nConsecCons'	=> $laDoc['cnsCons'],
								'nConsecEvol'	=> $laDoc['cnsEvo'],
								'nConsecDoc'	=> $laDoc['cnsDoc'],
								'cCUP'			=> $lcCup,
								'cCodVia'		=> $laDoc['codvia'],
								'cSecHab'		=> $laDoc['sechab'],
							];
?>
							<script type="text/javascript">
							$(function () {
								var laEnvio = btoObj( <?php echo '\'' . base64_encode(json_encode($laDatos)) . '\''; ?> ),
									lcEnvio = JSON.stringify([laEnvio]);
								formPostTemp('nucleo/vista/documentos/vistaprevia.php', {'datos':lcEnvio}, true);
							});
							</script>
<?php
							printf($divMensaje, "El paciente con ingreso $lnIngreso ya tiene Historia Clínica", $btnVolver);
							break;
						}
					}

				} else {
					printf($divMensaje, "No se encuentra la Historia Clínica del paciente con ingreso $lnIngreso", $btnVolver);
				}
			}

		} else {
			printf($divMensaje, 'Datos insuficientes para acceder a la historia clínica', $btnVolver);
		}
}
