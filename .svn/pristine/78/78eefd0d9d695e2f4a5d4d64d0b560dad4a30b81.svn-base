<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = (isset($_POST['accion']) ? $_POST['accion'] : '');
	switch ($lcAccion) {

		// Retorna documento en formato html
		case 'dochtml':

			$lConsultarIngreso = true;
			$lbDocumentoSolo = true;
			$laDatos = json_decode($_POST['datos'] ?? '[]', true);

			$lnIngreso		= $laDatos['nIngreso'] ?? 0;
			$lcTipoId		= $cTipDocPac['cTipId'] ?? '';
			$lnNumeroId		= $laDatos['nNumDocPac'] ?? 0;
			$lnConsulta		= $laDatos['nConsecCons'] ?? 0;
			$lnCita			= $laDatos['nConsecCita'] ?? 0;
			$lcCup			= $laDatos['cCUP'] ?? 0;
			$lcModulo		= ($_POST['mod'] ?? 'VISTAPREVIA').'_WEB';
			$lcObjeto		= 'BTNVISTAPREVIA';
			$lnImprime		= 0;
			$lcDescripcion	= mb_strtoupper($_POST['dsc'] ?? 'VISTA PREVIA DOCUMENTO','UTF-8');
			$lcProgramaCrea	= $_POST['mod'] ?? 'VISPREVIA';
			require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, $lnConsulta, $lnCita, $lcCup, $lcModulo, $lcObjeto, $lnImprime, $lcDescripcion, $lcProgramaCrea, $lcTipoId, $lnNumeroId);

			require_once __DIR__ .'/../../../controlador/class.Documento.php';
			$loDocLibro = new NUCLEO\Documento();
			$loDocLibro->obtenerDocumento($laDatos, $lConsultarIngreso, $lbDocumentoSolo, true, true);
			$loDocLibro->generarHTML();
			exit;

			break;

	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
