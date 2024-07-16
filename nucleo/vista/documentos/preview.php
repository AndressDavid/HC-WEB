<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	require_once __DIR__ .'/../../controlador/class.Documento.php';
	$loListaDocs = new NUCLEO\Documento();

	//	****************************** Un solo documento ******************************  //
	if ( !isset($_POST['nNumId']) ) {


		$lConsultarIngreso = true;
		$lbDocumentoSolo = true;
		$laDatos = organizaDatos(json_decode($_POST['datos'] ?? '[]', true));
		$loListaDocs->obtenerDocumento($laDatos, $lConsultarIngreso, $lbDocumentoSolo);
		$loListaDocs->generarPDF();



	//	****************************** Varios documentos ******************************  //
	} else {

		ini_set('max_execution_time', 60*30); // 30 minutos de consulta

		foreach ($_POST as $lcClave => $luValor) {
			if ($lcClave=='datos') {
				$aDocs = json_decode($luValor, true);
			} else {
				${$lcClave} = $luValor;
			}
		}

		// Ordenar documentos por fecha hora
		require_once __DIR__ .'/../../controlador/class.AplicacionFunciones.php';
		NUCLEO\AplicacionFunciones::ordenarArrayMulti($aDocs, 'tFecHor');

		// Organiza adjuntos al final
		$laDocs = []; $laAdjs = []; $lnNumAdj = 0;
		foreach ($aDocs as $aDoc) {
			if ($aDoc['cTipPrg']=='ADJUNTOS') {
				// Adjuntos extrainstitucionales no deben aparecer en el libro
				if ($aDoc['cTipDoc']!=='9600') {
					$lnNumAdj++;
					$aDoc['cCUP'] = $lnNumAdj.') '.$aDoc['tFecHor'].' - '.$aDoc['cCUP'];
					$laAdjs[] = organizaDatos($aDoc);
				}
			}
			else
				$laDocs[] = organizaDatos($aDoc);
		}
		$aDocs = null;


		// Objeto PDF
		require_once __DIR__ .'/../../controlador/class.PdfHC.php';
		$loPdf = new NUCLEO\PdfHC(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$loPdf->adicionarPortada($cTipId, $nNumId, $cNomPac, $nIngreso, $cFiltro);

		$lConsultarIngreso = true;
		$lbDocumentoSolo = false;

		// Procesar Documentos en AS400
		foreach ($laDocs as $laDoc) {
			// Por el momento evita laboratorios
			if (!($laDoc['cTipoDocum']=='1100')) {
				$loListaDocs->obtenerDocumento($laDoc, $lConsultarIngreso, $lbDocumentoSolo);
				$laDatDoc = $loListaDocs->retornarDocumento();
				$loPdf->procesar($laDatDoc);
			}
		}

		// Procesar Adjuntos
		if (count($laAdjs)>0) {
			$loPdf->listaAdjuntos($laAdjs);
			foreach ($laAdjs as $laDoc) {
				$loListaDocs->obtenerDocumento($laDoc, $lConsultarIngreso, $lbDocumentoSolo);
				$laDatDoc = $loListaDocs->retornarDocumento();
				$loPdf->procesar($laDatDoc);
			}
		}

		// Genera PDF final
		$loPdf->Output('librohc.pdf', 'I');
	}
}


function organizaDatos($taDatos) {
	return [
		'nIngreso'		=> $taDatos['nIngreso']	?? '0',
		'cTipDocPac'	=> $taDatos['cTipoId']	?? '',
		'nNumDocPac'	=> $taDatos['nNumrId']	?? '0',
		'cRegMedico'	=> $taDatos['cRegMed']	?? '',
		'cTipoDocum'	=> $taDatos['cTipDoc']	?? '',
		'cTipoProgr'	=> $taDatos['cTipPrg']	?? '',
		'tFechaHora'	=> $taDatos['tFecHor']	?? '',
		'nConsecCita'	=> $taDatos['nCnsCit']	?? '0',
		'nConsecCons'	=> $taDatos['nCnsCon']	?? '0',
		'nConsecEvol'	=> $taDatos['nCnsEvo']	?? '0',
		'nConsecDoc'	=> $taDatos['nCnsDoc']	?? '0',
		'cCUP'			=> $taDatos['cCUP']		?? '',
		'cCodVia'		=> $taDatos['cCodVia']	?? '',
		'cSecHab'		=> $taDatos['cSecHab']	?? '',
	];
}
