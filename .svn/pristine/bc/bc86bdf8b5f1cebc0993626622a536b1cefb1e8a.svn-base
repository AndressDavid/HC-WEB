<?php

	$btnVolver = '<a class="btn btn-secondary btn-sm" href="javascript: history.back()">Volver</a>';
	$divMensaje = '<div class="container-fluid"><div class="card mt-3"><div class="card-header alert-danger"><div class="row"><div class="col"><h5>%s</h5>%s</div></div></div></div></div>';
	$lbReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();

	if (!$lbReqAval) {
		require_once __DIR__ . '/../../controlador/class.AplicacionFunciones.php';
		require_once __DIR__ . '/../../vista/comun/modalAlertaFallece.php';

		$lnIngreso = isset($_SESSION[HCW_DATA]) ? $_SESSION[HCW_DATA]['ingreso'] : NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? 0);

		if ($lnIngreso > 0) {
			$lcDescripcionCup = '';
			require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';

			if (isset($_SESSION[HCW_DATA])) {
				$lcTipoDoc = $_SESSION[HCW_DATA]['tipodoc'] ?? '';
				$lnNumDoc = $_SESSION[HCW_DATA]['numdoc'] ?? 0;
				$lcVia = $_SESSION[HCW_DATA]['via'] ?? '';
				$lcForm = $_SESSION[HCW_DATA]['form'] ?? '';
				$lcCodesp = $_SESSION[HCW_DATA]['codesp'] ?? '';
				$lcFecrea = $_SESSION[HCW_DATA]['fecrea'] ?? '';
				$lcMedRealiza = $_SESSION[HCW_DATA]['medrea'] ?? '';
				$lcEspRea = $_SESSION[HCW_DATA]['cEspRea'] ?? '';
				unset($_SESSION[HCW_DATA]);
			} else {
				$lcTipoDoc = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['tipodoc'] ?? '');
				$lnNumDoc = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['numdoc'] ?? '');
				$lcVia = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['via'] ?? '');
				$lcMedRealiza = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['medrealiza'] ?? '');
				$lcForm = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['form'] ?? '');
				$lcCodesp = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['codesp'] ?? '');
				$lcFecrea = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['fecrea'] ?? '');
				$lcEspRea = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cEspRea'] ?? '');
			}

			$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();

			if (isset($loHcIng)) {

				(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'ORDMED_WEB', 'INICIO', 0, 'INGRESO ÓRDENES MÉDICAS', 'ORDMED', $lcTipoDoc, $lnNumDoc);

				$laRetorna = $loHcIng->datosIngreso($lnIngreso);
				$laRetorna['cMedRealiza'] = $lcMedRealiza;
				$laRetorna['cDescripcionCup'] = $lcDescripcionCup;
				$laRetorna['cFormAnterior'] = $lcForm;

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$laAuditoria['cFechaAud'] = $ltAhora->format('Ymd');
				$laAuditoria['cHoraAud'] = $ltAhora->format('His');
				$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();

				include __DIR__ . '/ordenes_medicas.php';
				echo '<script type="text/javascript">' . PHP_EOL;
				echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
				echo 'var aAuditoria = btoObj(\'' . base64_encode(json_encode($laAuditoria)) . '\');' . PHP_EOL;
				echo '</script>' . PHP_EOL;

			// Mostrar la HC en PDF
			} else {

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
							<script type="text/javascript" src="vista-comun/js/comun.js"></script>
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
					printf($divMensaje, "No se encuentra la orden médica del paciente con ingreso $lnIngreso", $btnVolver);
				}
			}

		} else {
			printf($divMensaje, 'Datos insuficientes para acceder a la orden médica', $btnVolver);
		}

	} else {
		printf($divMensaje, 'El usuario requiere AVAL, no puede realizar orden médica por este formulario.', $btnVolver);
	}

