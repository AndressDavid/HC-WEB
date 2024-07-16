<?php

require_once __DIR__ . '/../../controlador/class.AplicacionFunciones.php';

$btnVolver = '<a class="btn btn-secondary" href="javascript: history.back()">Volver</a>';
$divMensaje = '<div class="container-fluid"><div class="card mt-3"><div class="card-header alert-danger"><div class="row"><div class="col"><h5>%s</h5>%s</div></div></div></div></div>';
$lbReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();

if (isset($_SESSION[HCW_DATA])) {
	$lnIngreso = $_SESSION[HCW_DATA]['ingreso'];
	$lcTipo = $_SESSION[HCW_DATA]['tipoev'];
	$lnCnsCons = $_SESSION[HCW_DATA]['cons'] ?? 0;
	$llAvalar = $_SESSION[HCW_DATA]['Avalar'] ?? false;
	unset($_SESSION[HCW_DATA]);
} else {
	$lnIngreso = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['ingreso'] ?? '');
	$lcTipo = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['tipoev'] ?? '');
	$lnCnsCons = NUCLEO\AplicacionFunciones::fnSanitizar($_POST['cons'] ?? 0);
	$llAvalar = isset($_POST['Avalar']) ? $_POST['Avalar']==='true' : false;
}

if ($lnIngreso > 0) {

	require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';

	// Datos para evolución
	$loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
	$laRetorna = $loHcIng->datosIngreso($lnIngreso);

	if (strlen(trim($laRetorna['cNombre']??''))>0) {

		require_once __DIR__ . '/../../controlador/class.Diagnostico.php';
		$laRetorna['DxPpal'] = (new NUCLEO\Diagnostico())->DxPpal($lnIngreso);
		(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'EVOLUCIONES_WEB', 'INICIO', 0, 'INGRESO EVOLUCIÓN '.$lcTipo, 'EVOLUCION', $laRetorna['cTipId'], $laRetorna['nNumId']);

		$laRetorna['TipoEV'] = $lcTipo;
		$laRetorna['ActivarDxProc'] = false;
		$laRetorna['nConCons'] = $lnCnsCons;
		$laRetorna['Avalar'] = $llAvalar;

		require_once __DIR__ . '/../../controlador/class.ParametrosConsulta.php';
		if($laRetorna['cSeccion']=='CC' || $laRetorna['cSeccion']=='CV'){
			$laRetorna['ActivarDxProc'] = (new NUCLEO\ParametrosConsulta())->VerificarDxProc($laRetorna['nIngreso']);
		}

		$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
		$laAuditoria['cEspUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getEspecialidad();
		$laAuditoria['cUsuario'] = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
		$laAuditoria['lRequiereAval'] = $lbReqAval;

		// Menu de evoluciones
		$laMenu = (new NUCLEO\ParametrosConsulta())->menuEV($lcTipo, $laRetorna['cCodVia'], $laRetorna['cSeccion']);
		$lcMenu = '';
		$laObjetos = [];
		$lcActive = 'active';
		$lcActiveo = 'show active';
		$lcSelected = 'aria-selected="true"';

		foreach ($laMenu as $lnClave => $laElem) {
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

		// Vistas de Evoluciones
		include __DIR__ . '/evoluciones.php';
		echo '<script type="text/javascript">' . PHP_EOL;
		echo 'var aDatosIngreso = btoObj(\'' . base64_encode(json_encode($laRetorna)) . '\');' . PHP_EOL;
		echo 'var aAuditoria = btoObj(\'' . base64_encode(json_encode($laAuditoria)) . '\');' . PHP_EOL;
		echo '</script>' . PHP_EOL;

	} else {
		printf($divMensaje, 'No se pudieron obtener los datos del paciente. Por favor intente nuevamente ingresar a la evolución.', $btnVolver);
	}

} else {
	printf($divMensaje, 'Datos insuficientes para acceder a la evolución', $btnVolver);
}
