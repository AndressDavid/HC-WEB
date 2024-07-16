<?php
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {
	$lcAccion = $_POST['accion'] ;

	switch($lcAccion)
	{
		case 'regMovAud':
			$lnIngreso = $_POST['nIngreso'] ?? 0;
			$lcTipoId = $_POST['cTipId'] ?? '';
			$lnNumeroId = $_POST['nNumId'] ?? '';
			$lnConsulta = $_POST['nConsulta'] ?? 0;
			$lnCita = $_POST['nCita'] ?? 0;
			$lcCup = $_POST['cCup'] ?? '';
			$lcModulo = $_POST['cModulo'] ?? '';
			$lcObjeto = $_POST['cObjeto'] ?? '';
			$lnImprime = $_POST['nImprime'] ?? 0;
			$lcDescripcion = $_POST['cDescrip'] ?? '';
			$lcProgramaCrea = $_POST['cPrgCrea'] ?? '';
			require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, $lnConsulta, $lnCita, $lcCup, $lcModulo, $lcObjeto, $lnImprime, $lcDescripcion, $lcProgramaCrea, $lcTipoId, $lnNumeroId);
			break;
	}
}


include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
