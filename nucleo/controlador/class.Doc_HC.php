<?php

namespace NUCLEO;

require_once('class.Conciliacion.php');
require_once('class.Diagnostico.php');
require_once('class.Doc_Ordenes.php');
require_once('class.Texto_Diagnostico.php');
require_once('class.Doc_NotasAclaratorias.php');
require_once('class.Doc_NIHSS.php');

use NUCLEO\Conciliacion;
use NUCLEO\Diagnostico;
use NUCLEO\Doc_Ordenes;
use NUCLEO\Texto_Diagnostico;
use NUCLEO\Doc_NotasAclaratorias;
use NUCLEO\Doc_NIHSS;

class Doc_HC
{
	protected $oDb;
	protected $aDocumentoHC = [];
	protected $aDolor = [];
	protected $aAntec = [];
	protected $aDatosCE  = [];
	protected $aRevisionCE  = [];
	protected $aRevision = [];
	protected $aExamen = [];
	protected $aConcilia = [];
	protected $aInterpreta = [];
	protected $aDiagnos = [];
	protected $lTituloVacios = false;
	protected $cTituloVacios = '';
	protected $cTipoDatos = '';
	protected $cTipoConsulta = '';
	protected $llDolor = false;
	protected $laDocumentoHC = [];
	protected $aUsuarioRealiza = [];
	protected $aTr = [];


	protected $aReporte = [
		'cTitulo' => 'HISTORIA CLINICA',
		'lMostrarFechaRealizado' => false,
		'lMostrarViaCama' => false,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
		'cTxtLuegoDeCup' => '',
		'aCuerpo' => [],
		'aFirmas' => [],
		'aNotas' => ['notas' => false, 'codproc' => 'NOTAHC'],
	];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData, $tlEpicrisis = false)
	{

		$this->consultarDatos($taData);
		$this->prepararInforme($taData, $tlEpicrisis);
		return $this->aReporte;
	}


	/*	Consulta los datos del documento desde la BD en el array $aDocumento */
	private function consultarDatos($taData)
	{
		$lcSL = "\n"; //PHP_EOL;
		$this->laDocumentoHC = $this->datosBlanco();

		/* Dolor Toracico */
		$this->aDolor = $this->oDb
			->select('CL1TMA TIPO, CL2TMA CODIGO, INT(CL3TMA) NIVEL, DE1TMA NOMBRE, DE2TMA DESCRIP, OP1TMA VALOR, TRIM(OP2TMA) POSANT, OP4TMA LINEA')
			->from('TABMAE')
			->where("TIPTMA='TORACICO'")
			->getAll('array');

		/* Antecedentes */
		$laAntecedentes = $this->oDb
			->select('DE1TMA NOMBRE, OP6TMA SINDICE, CL2TMA CODIGO, OP1TMA OBLIGA')
			->from('TABMAE')
			->where("TIPTMA='ITEMHC' AND CL1TMA='ANTECEDE' AND SUBSTR(CL2TMA,1,2)='02' AND LENGTH(TRIM(CL2TMA))=4")
			->orderBy('INT(OP3TMA)')
			->getAll('array');

		$laAntec = [];
		foreach ($laAntecedentes as $laAntecedente) {
			$laAntec[trim($laAntecedente['SINDICE'])] = [
				'titulo' => trim($laAntecedente['NOMBRE']),
				'detalle' => '',
			];
		}

		/* Revisión de sistemas */
		$laRevision = $this->oDb
			->select('DE1TMA NOMBRE, OP6TMA SINDICE, CL2TMA CODIGO, OP1TMA OBLIGA')
			->from('TABMAE')
			->where("TIPTMA='ITEMHC' AND CL1TMA='REVISION' AND CL3TMA='2'")
			->orderBy('INT(OP3TMA)')
			->getAll('array');

		$laRevSis = [];
		foreach ($laRevision as $laRev) {
			$laRevSis[trim($laRev['SINDICE'])] = [
				'titulo' => trim($laRev['NOMBRE']),
				'detalle' => '',
			];
		}

		/* Examen Físico */
		$laExamenF = $this->oDb
			->select('0 INDICA, DE1TMA NOMBRE, OP6TMA SINDICE, CL2TMA CODIGO, OP1TMA OBLIGA')
			->from('TABMAE')
			->where("TIPTMA='ITEMHC' AND CL1TMA='EXAMEN' AND OP5TMA='Pag0599'")
			->orderBy('INT(OP3TMA)')
			->getAll('array');

		$laExamen = [];
		foreach ($laExamenF as $laExF) {
			$this->aExamen[trim($laExF['SINDICE'])] = [
				'titulo' => trim($laExF['NOMBRE']),
				'detalle' => '',
			];
		}

		// Consecutivo de HC
		if (empty($taData['nConsecCons'])) {

			if ($taData['cTipoProgr'] == 'HCPPAL') {
				$laLista = ['HCPPAL', 'HCPPALWEB'];
			}

			$laConsecutivo = $this->oDb
				->select('MIN(CONCON) CONCON')
				->from('RIAHIS')
				->where(['NROING' => $taData['nIngreso']])
				->in('PGMHIS', $laLista)
				->get('array');
			if ($this->oDb->numRows() > 0) {
				$this->laDocumentoHC['nConsecHC'] = empty($laConsecutivo['CONCON']) ? 1 : $laConsecutivo['CONCON'];
			}
		} else {
			$this->laDocumentoHC['nConsecHC'] = $taData['nConsecCons'];
		}

		$lnReg = 0;

		// HISTORIA CLINICA URGENCIAS
		$this->cTipoConsulta = 'U';
		$lcPrg = 'HC0100';
		$laHC = $this->oDb
			->select('INGHCL INGRESO, CCOHCL CONSULTA, CEVHCL EVOLUCION, INDHCL INDICE, SUBHCL SUBINDICE')
			->select('CODHCL CODIGO, CLNHCL CLINEA, DESHCL DESCRIP, USRHCL USUARIO, PGMHCL PROGRAMA')
			->select('FECHCL FECHACRE, HORHCL HORACRE, FECHCL FECHAMOD, HORHCL HORAMOD')
			->from('HISCLIL01')
			->where([
				'INGHCL' => $taData['nIngreso'],
				'CCOHCL' => $this->laDocumentoHC['nConsecHC'],
				'PGMHCL' => $lcPrg,
			])
			->orderBy('INDHCL, SUBHCL, CODHCL, CLNHCL')
			->getAll('array');

		$lnReg = $this->oDb->numRows();
		if ($lnReg > 0) {
			foreach ($laHC as $lnKey => $laDato) {
				$laHC[$lnKey]['SUBPARTE'] = '';
				$laHC[$lnKey]['TRATAMIENTO'] = '';
			}
		} else {

			// HISTORIA CLINICA HOSPITALIZACION
			$this->cTipoConsulta = 'H';
			$lcPrg = 'HC0007U';

			$laHC = $this->oDb
				->select('INGHOS INGRESO, CCOHOS CONSULTA, INDHOS INDICE, SUBHOS SUBINDICE, CODHOS CODIGO, CLNHOS CLINEA, DESHOS DESCRIP')
				->select('USRHOS USUARIO, PGMHOS PROGRAMA, FECHOS FECHACRE, HORHOS HORACRE, FMOHOS FECHAMOD, HMOHOS HORAMOD')
				->from('HISHOSL3')
				->where([
					'INGHOS' => $taData['nIngreso'],
					'CCOHOS' => $this->laDocumentoHC['nConsecHC'],
					'PGMHOS' => $lcPrg,
				])
				->orderBy('INDHOS, SUBHOS, CODHOS, CLNHOS')
				->getAll('array');

			$lnReg = $this->oDb->numRows();
			if ($lnReg > 0) {
				foreach ($laHC as $lnKey => $laDato) {
					$laHC[$lnKey]['EVOLUCION'] = '';
					$laHC[$lnKey]['SUBPARTE'] = '';
					$laHC[$lnKey]['REGMED'] = '';
					$laHC[$lnKey]['OPCION'] = '';
					$laHC[$lnKey]['TRATAMIENTO'] = '';
				}
			}
		}

		// HISTORIA CLINICA CONSULTA EXTERNA
		if (empty($lnReg)) {

			$this->cTipoConsulta = 'C';
			$lcPrg = 'HC0007AN';

			$laHC = $this->oDb
				->select('NROING INGRESO, CONCON CONSULTA, CONHIS EVOLUCION, INDICE, SUBIND SUBINDICE, SUBORG SUBPARTE, CODIGO, CONSEC CLINEA, DESCRI DESCRIP')
				->select('FILLE3 TRATAMIENTO, USRHIS USUARIO, PGMHIS PROGRAMA, FECHIS FECHACRE, HORHIS HORACRE, FMOHIS FECHAMOD, HMOHIS HORAMOD')
				->from('RIAHISL6')
				->where([
					'NROING' => $taData['nIngreso'],
					'CONCON' => $this->laDocumentoHC['nConsecHC'],
					'PGMHIS' => $lcPrg,
				])
				->orderBy('INDICE, SUBIND, CODIGO, CONSEC')
				->getAll('array');

			$lnReg = $this->oDb->numRows();
			if ($lnReg > 0) {
				foreach ($laHC as $lnKey => $laDato) {
					$laHC[$lnKey]['REGMED'] = '';
					$laHC[$lnKey]['OPCION'] = '';
				}
				$this->aInterpreta = [];
			}
		}


		// HISTORIA CLINICA UNIFICACION
		if (empty($lnReg)) {

			$this->cTipoConsulta = 'F';

			$laHC = $this->oDb
				->select('NROING INGRESO, CONCON CONSULTA, CONHIS EVOLUCION, INDICE, SUBIND SUBINDICE, SUBORG SUBPARTE, CODIGO, CONSEC CLINEA, DESCRI DESCRIP')
				->select('FILLE3 TRATAMIENTO, USRHIS USUARIO, PGMHIS PROGRAMA, FECHIS FECHACRE, HORHIS HORACRE, FMOHIS FECHAMOD, HMOHIS HORAMOD')
				->from('RIAHIS')
				->where([
					'NROING' => $taData['nIngreso'],
					'CONCON' => $this->laDocumentoHC['nConsecHC'],
				])
				->orderBy('INDICE, SUBIND, CODIGO, CONSEC')
				->getAll('array');
			$lnReg = $this->oDb->numRows();

			$laUsuarioRealiza = $this->oDb

				->select('USRRIC,FECRIC,HORRIC')
				->select('NNOMED,NOMMED,REGMED,TPMRGM,CODRGM,DESESP')
				->from('REINCA')
				->leftJoin('RIARGMN', 'USRRIC = USUARI')
				->leftJoin('RIAESPE', 'CODRGM=CODESP')
				->where([
					'INGRIC' => $taData['nIngreso'],
					'TIPRIC' => 'HC',
					'ESTRIC' => 'VA',
					'CEVRIC' => 0,
				])
				->get('array');
			if ($this->oDb->numRows() > 0) {
				$laUsuarioRealiza = array_map('trim', $laUsuarioRealiza);
				$this->aUsuarioRealiza = [
					'nombre' => $laUsuarioRealiza['NNOMED'],
					'apellido' => $laUsuarioRealiza['NOMMED'],
					'rm' => $laUsuarioRealiza['REGMED'],
					'codtipo' => $laUsuarioRealiza['TPMRGM'],
					'codespecialidad' => $laUsuarioRealiza['CODRGM'],
					'especialidad' => $laUsuarioRealiza['DESESP'],
					
				];
			}
		}

		$this->cTipoDatos = $this->oDb->ObtenerTabMae('OP1TMA', 'HCPARAM', ['CL1TMA' => 'FECHORHC', 'ESTTMA' => '']);

		$this->fnCargarDatosCE();
		$this->aRevisionCE['titulo'] = '';
		$this->aRevisionCE['detalle'] = '';

		foreach ($laHC as $laData) {

			switch (true) {

				case $laData['INDICE'] == 2 || $laData['INDICE'] == 3:
					$this->fnActualizarFechaHora($laData);
					break;

				case $laData['INDICE'] == 4:
					$this->laDocumentoHC['cTipoFinal'] = $laData['SUBINDICE'];
					$this->laDocumentoHC['cDescFinal'] .= $laData['DESCRIP'];
					break;

				case $laData['INDICE'] == 6:
					$this->laDocumentoHC['cTextoCovid'] .= $laData['DESCRIP'];
					break;

				case $laData['INDICE'] == 5:
					$this->fnCargarCausaExterna($laData);
					break;

				case $laData['INDICE'] == 10 && $laData['SUBINDICE'] <= 15 && $this->cTipoConsulta == 'C':
					$this->fnCargarAntecedentesCE($laData);
					break;

				case $laData['INDICE'] == 10 && $laData['SUBINDICE'] == 15 && $this->cTipoConsulta !== 'C':
					if ($laData['CODIGO'] == 20) {
						//Parámetros de Discapacidad
						$laDiscapacidad = [];
						$laParam = $this->oDb
							->select('TRIM(CL3TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
							->from('TABMAE')
							->where("TIPTMA='CATAINHC' AND CL1TMA='DISCAPAC' AND CL2TMA='01' AND ESTTMA = ' '")
							->orderBy('CL3TMA')
							->getAll('array');

						if (is_array($laParam)) {
							foreach ($laParam as $laPar) {
								$laDiscapacidad[$laPar['CODIGO']] = $laPar['DESCRIP'];
							}
						}

						$lcInformacion = explode("¤", trim($laData['DESCRIP']));
						$lnReg = count($lcInformacion);
						$lcDescrip = '';
						if ($lcInformacion[0] == 'Si') {
							foreach ($lcInformacion as $lnKey => $lcValor) {
								$lcDescrip .= trim($lcValor == 'Si') ? 'Si: ' : $laDiscapacidad[$lcValor] . ($lnKey == $lnReg - 1 ? '.' : ' - ');
							}
						} else {
							$lcDescrip .= 'No.';
						}
						$laAntec[intval($laData['CODIGO'])]['detalle'] .= $lcDescrip;
					} else {
						$laAntec[intval($laData['CODIGO'])]['detalle'] .= $laData['DESCRIP'];
					}
					break;

				case $laData['INDICE'] == 10 && $laData['SUBINDICE'] < 13 && $this->cTipoConsulta !== 'C':
					$laRevSis[trim($laData['SUBINDICE'])]['detalle'] .= $laData['DESCRIP'];
					break;

				case $laData['INDICE'] == 15 && $laData['SUBINDICE'] < 13 && $this->cTipoConsulta == 'C':
					$this->fnCargarRevisionCE($laData);
					break;

				case $laData['INDICE'] == 20 && $this->cTipoConsulta == 'C':
					$this->fnCargarSignos($laData, $taData);
					$this->fnCargarExamenFisicoCE($laData);
					break;

				case $laData['INDICE'] == 20 && $this->cTipoConsulta != 'C':
					$this->fnCargarSignos($laData, $taData);
					$this->fnCargarExamenFisico($laData);
					break;

				case $laData['INDICE'] == 21:
					$this->fnCargarInterpretacion($laData);
					break;

				case $laData['INDICE'] == 25:
					$this->fnCargarDiagnostico($laData);
					break;

				case $laData['INDICE'] == 30:
					$this->laDocumentoHC['cDescPlan'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 35 && $laData['SUBINDICE'] == 1:
					$this->laDocumentoHC['cEscalaHA'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 35 && $laData['SUBINDICE'] == 2:
					$this->laDocumentoHC['cEscalaCH'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 35 && $laData['SUBINDICE'] == 3:
					$this->laDocumentoHC['cEscalaCR'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 35 && $laData['SUBINDICE'] == 4:
					$this->laDocumentoHC['cEscalaSad'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 40:
					$this->laDocumentoHC['nInformaPlan'] = trim($laData['DESCRIP']) == 'S' ? 1 : 2;
					break;

				case $laData['INDICE'] == 50:
					$this->laDocumentoHC['cInterpPlan'] .= trim($laData['DESCRIP']);
					$this->laDocumentoHC['nTuvoPlan'] = 1;
					break;

				case $laData['INDICE'] == 54:
					$this->laDocumentoHC['cConductaPlan'] .= trim($laData['CODIGO']);
					$this->laDocumentoHC['cNombreConducta'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 55:
					$this->laDocumentoHC['nEstadoPlan'] .= trim($laData['CODIGO']);
					$this->laDocumentoHC['cEstadoPlan'] .= trim($laData['DESCRIP']);
					break;

				case $laData['INDICE'] == 85:
					$this->laDocumentoHC['cMedRea'] .= trim(substr($laData['DESCRIP'], 0, 13));
					$this->laDocumentoHC['cEspRea'] .= trim(substr($laData['DESCRIP'], 37, 3));
					break;
			}
			$this->laDocumentoHC['nFecRea'] = empty($this->laDocumentoHC['nFecRea']) ? $laData['FECHACRE'] : $this->laDocumentoHC['nFecRea'];
			$this->laDocumentoHC['nHorRea'] = empty($this->laDocumentoHC['nHorRea']) ? $laData['HORACRE'] : $this->laDocumentoHC['nHorRea'];
			$this->laDocumentoHC['cUsuario'] = $laData['USUARIO'];
		}

		// Se adiciona vacuna covid19 que están en subíndice 24
		foreach ($laAntec as $lnCodigo => $laAnt) $laAntec[$lnCodigo]['detalle'] = trim($laAntec[$lnCodigo]['detalle']);
		if (isset($laAntec[24]['detalle'])) {
			$laAntec[4]['detalle'] .= (empty($laAntec[4]['detalle']) ? '' : PHP_EOL) . $laAntec[24]['detalle'];
			unset($laAntec[24]);
		}

		if (empty(trim($this->laDocumentoHC['cEstadoEst']))) {
			$this->fnCargarSignos([], $taData);
		}

		// Busca descripcion del tipo de la causa
		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'CODCEX', ['CL1TMA' => $this->laDocumentoHC['cTipoCausa'], 'ESTTMA' => ' ']);
		$this->laDocumentoHC['cDescTipoCausa'] = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));
		$this->laDocumentoHC['cViaHC'] = empty($this->laDocumentoHC['cViaHC']) ? $taData['oIngrPaciente']->cDescVia : $this->laDocumentoHC['cViaHC'];
		$this->laDocumentoHC['nRelacionCausa'] = empty($this->laDocumentoHC['nRelacionCausa']) ? 2 : 1;
		$this->laDocumentoHC['cInterpretaCausa'] = empty($this->laDocumentoHC['cInterpretaCausa']) ? 2 : 1;
		$this->laDocumentoHC['cMotivoCausa'] = trim($this->laDocumentoHC['cMotivoCausa']);
		$this->laDocumentoHC['cEventoCausa'] = trim($this->laDocumentoHC['cEventoCausa']);
		$this->laDocumentoHC['cRelacionCausa'] = trim($this->laDocumentoHC['cRelacionCausa']);
		$this->laDocumentoHC['cInterpretaCausa'] = trim($this->laDocumentoHC['cInterpretaCausa']);

		if (empty($lnReg) == false) {
			$laUsuario = $this->oDb
				->select('REGMED,TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE')
				->tabla('RIARGMN')
				->where(['USUARI' => $laData['USUARIO']])
				->get('array');
			if (!is_array($laUsuario)) {
				$laUsuario = [];
			}
			$this->aDocumentoHC = array_merge($this->laDocumentoHC, $laUsuario);
			$this->aAntec = array_merge($laAntec);
			$this->aRevision = array_merge($laRevSis);
		}
	}

	// Prepara array $aReporte con los datos para imprimir
	private function prepararInforme($taData = [], $tlEpicrisis = false)
	{
		$lnAnchoPagina = 90;
		$lcSL = "\n"; //PHP_EOL;
		$cVacios = $this->cTituloVacios;

		/* Encabezado */
		$laTr['cTxtAntesDeCup'] =
			str_pad('Realizado : ' . AplicacionFunciones::formatFechaHora('fechahora', $this->laDocumentoHC['nFecRea'] . ' ' . $this->laDocumentoHC['nHorRea']), $lnAnchoPagina / 3, ' ')
			. str_pad('   Vía: ' . $this->laDocumentoHC['cViaHC'], $lnAnchoPagina / 2, ' ');

		/* Cuerpo */

		if (!empty(trim($this->laDocumentoHC['cTextoCovid']))) {
			$laTr['aCuerpo'][] = ['texto9',	$this->laDocumentoHC['cTextoCovid']];
		}
		$laTr['aCuerpo'][] = ['titulo1', 'MOTIVO DE CONSULTA'];
		$laTr['aCuerpo'][] = ['txthtml9', '<b>Tipo de Causa: </b>' . $this->laDocumentoHC['cDescTipoCausa']];
		$laTr['aCuerpo'][] = ['titulo2', 'Motivo de Consulta'];
		$laTr['aCuerpo'][] = ['texto9',	$this->laDocumentoHC['cMotivoCausa']];
		if (empty($this->laDocumentoHC['cEventoCausa']) == false) {
			$laTr['aCuerpo'][] = ['titulo2', 'Enfermedad Actual'];
			$laTr['aCuerpo'][] = ['texto9',	$this->laDocumentoHC['cEventoCausa']];
		}

		if (empty($this->laDocumentoHC['cRelacionCausa']) == false) {
			$laTr['aCuerpo'][] = ['texto9', 'Ingreso por Remisión de otra IPS ? SI' . $lcSL];
			$laTr['aCuerpo'][] = ['texto9', $this->laDocumentoHC['cRelacionCausa'] . $lcSL];
		}

		if ($this->llDolor) {

			$lcDetalle = $lcDetalle1 = $lcDetalle2 = $lcDetalle3 = $lcDetalle4 = '';

			foreach ($this->aDolor as $laDolor) {

				if ($laDolor['VALOR'] == 1 && strlen(trim($laDolor['CODIGO'])) == 4) {

					switch (substr($laDolor['CODIGO'], 0, 2)) {

						case '01':
							$lcDetalle1 .= trim($laDolor['NOMBRE']) . ', ';
							break;
						case '02':
							$lcDetalle2 .= trim($laDolor['NOMBRE']) . ', ';
							break;
						case '03':
							$lcDetalle3 .= trim($laDolor['NOMBRE']) . ', ';
							break;
						case '04':
							$lcDetalle4 .= trim($laDolor['NOMBRE']) . ', ';
							break;
					}
				}
			}

			$lcDetalle1 = !empty($lcDetalle1) ? substr($lcDetalle1, 0, strlen(trim($lcDetalle1)) - 1) . '.' : '';
			$lcDetalle2 = !empty($lcDetalle2) ? substr($lcDetalle2, 0, strlen(trim($lcDetalle2)) - 1) . '.' : '';
			$lcDetalle3 = !empty($lcDetalle3) ? substr($lcDetalle3, 0, strlen(trim($lcDetalle3)) - 1) . '.' : '';
			$lcDetalle4 = !empty($lcDetalle4) ? substr($lcDetalle4, 0, strlen(trim($lcDetalle4)) - 1) . '.' : '';
			$lcDetalle  = !empty($lcDetalle1) ?         'CARACTERISTICAS : ' . trim($lcDetalle1) : '';
			$lcDetalle .= !empty($lcDetalle2) ? $lcSL . 'IRRADIACION     : ' . trim($lcDetalle2) : '';
			$lcDetalle .= !empty($lcDetalle3) ? $lcSL . 'SINTOMAS        : ' . trim($lcDetalle3) : '';
			$lcDetalle .= !empty($lcDetalle4) ? $lcSL . 'LOCALIZACION    : ' . trim($lcDetalle4) : '';
			$lcDetalle .= !empty($this->laDocumentoHC['nIntensidad']) ? $lcSL . 'INTENSIDAD      : ' . $this->laDocumentoHC['nIntensidad'] : '';

			$laTr['aCuerpo'][] = ['titulo2', 'Dolor torácico'];
			$laTr['aCuerpo'][] = ['texto9',	trim($lcDetalle)];
			$lcDetalle = '';

			if (
				!empty($this->laDocumentoHC['nSegDuracion']) || !empty($this->laDocumentoHC['nMinDuracion']) ||
				!empty($this->laDocumentoHC['nHoraDuracion']) || !empty($this->laDocumentoHC['nDiasDuracion'])
			) {
				$lcDetalle  = 'DURACION        :';
				$lcDetalle .= !empty($this->laDocumentoHC['nSegDuracion']) ? ' Segundos : ' . $this->laDocumentoHC['nSegDuracion'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nMinDuracion']) ? ' Minutos : ' . $this->laDocumentoHC['nMinDuracion'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nHoraDuracion']) ? ' Horas : ' . $this->laDocumentoHC['nHoraDuracion'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nDiasDuracion']) ? ' Días : ' . $this->laDocumentoHC['nDiasDuracion'] . ', ' : '';
			}

			$lcDetalle = (!empty($lcDetalle) ? substr($lcDetalle, 0, strlen(trim($lcDetalle)) - 1) . '.' . $lcSL : '');

			if (
				!empty($this->laDocumentoHC['nSegEvol']) || !empty($this->laDocumentoHC['nMinEvol']) ||
				!empty($this->laDocumentoHC['nHoraEvol']) || !empty($this->laDocumentoHC['nDiasEvol']) ||
				!empty($this->laDocumentoHC['nSemaEvol']) || !empty($this->laDocumentoHC['nMesEvol']) ||
				!empty($this->laDocumentoHC['nAñosEvol'])
			) {
				$lcDetalle .= 'EVOLUCION       :';
				$lcDetalle .= !empty($this->laDocumentoHC['nSegEvol']) ? ' Segundos : ' . $this->laDocumentoHC['nSegEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nMinEvol']) ? ' Minutos : ' . $this->laDocumentoHC['nMinEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nHoraEvol']) ? ' Horas : ' . $this->laDocumentoHC['nHoraEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nDiasEvol']) ? ' Días : ' . $this->laDocumentoHC['nDiasEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nSemaEvol']) ? ' Semanas : ' . $this->laDocumentoHC['nSemaEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nMesEvol']) ? ' Meses : ' . $this->laDocumentoHC['nMesEvol'] . ', ' : '';
				$lcDetalle .= !empty($this->laDocumentoHC['nAñosEvol']) ? ' Años : ' . $this->laDocumentoHC['nAñosEvol'] . ', ' : '';
			}
			$lcDetalle = (!empty($lcDetalle) ? substr($lcDetalle, 0, strlen(trim($lcDetalle)) - 1) . '.' : '');
			if (!empty($lcDetalle)) {
				$laTr['aCuerpo'][] = ['texto9',	$lcDetalle];
			}
		}

		$lltitulo = true;
		foreach ($this->aAntec as $laAntec) {
			if (!empty(trim($laAntec['detalle']))) {
				if ($lltitulo) {
					$lltitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'ANTECEDENTES'];
				}
				$laTr['aCuerpo'][] = ['titulo2', $laAntec['titulo']];
				$laTr['aCuerpo'][] = ['texto9',	trim($laAntec['detalle'])];
			}
		}

		$lltitulo = true;
		foreach ($this->aDatosCE as $laAntec) {
			if (!empty(trim($laAntec['detalle']))) {
				if ($lltitulo) {
					$lltitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'ANTECEDENTES'];
				}
				$laTr['aCuerpo'][] = ['titulo2', $laAntec['titulo']];
				$laTr['aCuerpo'][] = ['texto9',	trim($laAntec['detalle'])];
			}
		}

		$laDatosCM = [
			'ninand' => $taData['nIngreso'],
			'codand' => '17',
			'op7and' => $this->laDocumentoHC['nConsecHC'],
			'pgmand' => $taData['cTipoProgr']
		];

		$loConcilia = new Conciliacion($laDatosCM, 1);
		$this->aConcilia = $loConcilia->getTexto();

		if (!empty($this->aConcilia)) {
			$laTr['aCuerpo'][] = ['titulo2', 'CONCILIACION DE MEDICAMENTOS '];
			$laTr['aCuerpo'][] = ['texto9',	$this->aConcilia];
		}

		$lltitulo = True;
		foreach ($this->aRevision as $laRevSis) {
			if (!empty($laRevSis['detalle'])) {
				if ($lltitulo) {
					$lltitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'REVISION SISTEMA'];
				}
				$laTr['aCuerpo'][] = ['titulo2', $laRevSis['titulo']];
				$laTr['aCuerpo'][] = ['texto9',	$laRevSis['detalle']];
			}
		}

		if ($this->cTipoConsulta = 'C') {
			$lltitulo = True;
			foreach ($this->aRevisionCE as $laRevSis) {

				if (!empty($laRevSis['detalle'])) {
					if ($lltitulo) {
						$lltitulo = false;
						$laTr['aCuerpo'][] = ['titulo1', 'REVISION SISTEMAS'];
					}
					$laTr['aCuerpo'][] = ['titulo2', $laRevSis['titulo']];
					$laTr['aCuerpo'][] = ['texto9',	$laRevSis['detalle']];
				}
			}
		}

		$lcDetalle = '';
		$lltitulo = True;
		if (!empty($this->laDocumentoHC['cEstadoEst'])) {
			$lltitulo = false;
			$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
			$laTr['aCuerpo'][] = ['txthtml9', '<b>Estado General: </b>' . $this->laDocumentoHC['cEstadoEst']];
		}
		/* Signos Vitales */
		if (!(empty($this->laDocumentoHC['nIndTASEst']) && empty($this->laDocumentoHC['nTASEst'])
			&& empty($this->laDocumentoHC['nIndTADEst']) && empty($this->laDocumentoHC['nTADEst']))) {

			$lcDetalle .= 'Tensión Arterial       : ';
			$lcDetalle .= empty($this->laDocumentoHC['nIndTASEst']) ? $this->laDocumentoHC['nTASEst'] : 'AUSENTE';
			$lcDetalle .= " - " . (empty($this->laDocumentoHC['nIndTADEst']) ? $this->laDocumentoHC['nTADEst'] : 'AUSENTE') . $lcSL;
		}

		if (!((empty($this->laDocumentoHC['nIndFCEst']) && empty($this->laDocumentoHC['nFCEst'])))) {
			$lcDetalle .= 'Frecuencia Cardiáca    : ';
			$lcDetalle .= (empty($this->laDocumentoHC['nIndFCEst']) ? $this->laDocumentoHC['nFCEst'] . ' / min' : 'AUSENTE') . $lcSL;
		}

		if (!((empty($this->laDocumentoHC['nIndFREst']) && empty($this->laDocumentoHC['nFREst'])))) {
			$lcDetalle .= 'Frecuencia Respiratoria: ';
			$lcDetalle .= (empty($this->laDocumentoHC['nIndFREst']) ? $this->laDocumentoHC['nFREst'] . ' / min' : 'AUSENTE') . $lcSL;
		}

		$lcDetalle .= (!empty($this->laDocumentoHC['nTempEst']) ? 'Temperatura            : ' . $this->laDocumentoHC['nTempEst'] . ' °C' . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nTempREst']) ? 'Temperatura Rectal     : ' . $this->laDocumentoHC['nTempREst'] . ' °C' . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nPesoEst']) ? 'Peso                   : ' . $this->laDocumentoHC['nPesoEst'] . ' Kg' . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nTallaEst']) ? 'Talla                  : ' . $this->laDocumentoHC['nTallaEst'] . ' cm' . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nSupCEst']) ? 'Superficie Corporal    : ' . $this->laDocumentoHC['nSupCEst'] . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nMasaCEst']) ? 'Masa Corporal          : ' . $this->laDocumentoHC['nMasaCEst'] . ' Kg/m2' . $lcSL : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['nSaturaEst']) ? 'Saturación             : ' . $this->laDocumentoHC['nSaturaEst'] . ' %' . $lcSL : '');

		if (!empty($lcDetalle)) {
			if ($lltitulo) {
				$lltitulo = false;
				$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
			}
			$laTr['aCuerpo'][] = ['titulo2', 'Signos Vitales'];
			$laTr['aCuerpo'][] = ['texto9',	$lcDetalle];
		}

		foreach ($this->aExamen as $laExF) {
			if (!empty($laExF['detalle'])) {
				$laTr['aCuerpo'][] = ['titulo2', $laExF['titulo']];
				$laTr['aCuerpo'][] = ['texto9',	trim($laExF['detalle'])];
			}
		}

		if ($this->cTipoConsulta = 'C') {

			$llTitulo = true;

			if (!empty($this->laDocumentoHC['ExCabeza'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de la Cabeza'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExCabeza'])];
			}

			if (!empty($this->laDocumentoHC['ExCuello'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen del Cuello'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExCuello'])];
			}

			if (!empty($this->laDocumentoHC['ExTorax'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de Toráx'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExTorax'])];
			}

			if (!empty($this->laDocumentoHC['ExSeno'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de Seno'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExSeno'])];
			}

			if (!empty($this->laDocumentoHC['ExCorazon'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de Corazón'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExCorazon'])];
			}

			if (!empty($this->laDocumentoHC['ExPulmon'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de Pulmón'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExPulmon'])];
			}

			if (!empty($this->laDocumentoHC['ExAbdomen'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen del Abdomen'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExAbdomen'])];
			}

			if (!empty($this->laDocumentoHC['ExGenitoU'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen Genitourinario'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExGenitoU'])];
			}

			if (!empty($this->laDocumentoHC['ExExtrem'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen de las Extremidades'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExExtrem'])];
			}

			if (!empty($this->laDocumentoHC['ExNeurol'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen Neurológico'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExNeurol'])];
			}

			if (!empty($this->laDocumentoHC['ExOdonto'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen Odontológico'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExOdonto'])];
			}

			if (!empty($this->laDocumentoHC['ExOtorrino'])) {
				if ($llTitulo) {
					$llTitulo = false;
					$laTr['aCuerpo'][] = ['titulo1', 'EXAMEN FISICO'];
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Examen Otorrinolaringológico'];
				$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumentoHC['ExOtorrino'])];
			}
		}

		$lcDetalle = '';
		$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'CODNCO', ['CL1TMA' => $this->laDocumentoHC['cNivelCer'], 'ESTTMA' => '']);
		$cTemp = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

		$lcDetalle = (!empty($cTemp) ? 'Nivel de Conciencia :' . trim($cTemp) . $lcSL : '')
			. (!empty($this->laDocumentoHC['nGlasgowCer']) ? 'Glasgow             :' . $this->laDocumentoHC['nGlasgowCer'] . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cExamenCer'])) ? '<br><b>Examen Mental       :</b>' . trim($this->laDocumentoHC['cExamenCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cCranealesCer'])) ? '<br><b>Pares Craneales     :</b>' . trim($this->laDocumentoHC['cCranealesCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cMotorCer'])) ? '<br><b>Estado Motor        :</b>' . trim($this->laDocumentoHC['cMotorCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cSensitivoCer'])) ? '<br><b>Estado Sensitivo    :</b>' . trim($this->laDocumentoHC['cSensitivoCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cReflejosCer'])) ? '<br><b>Reflejos            :</b>' . trim($this->laDocumentoHC['cReflejosCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cSignosCer'])) ? '<br><b>Signos Meníngeos    :</b>' . trim($this->laDocumentoHC['cSignosCer']) . $lcSL : '')
			. (!empty(trim($this->laDocumentoHC['cNeuroCer'])) ? '<br><b>Neurovascular       :</b>' . trim($this->laDocumentoHC['cNeuroCer']) . $lcSL : '');

		if (!empty($lcDetalle)) {
			$laTr['aCuerpo'][] = ['titulo2', 'Neurológico'];
			$laTr['aCuerpo'][] = ['txthtml9',	$lcDetalle];
		}

		// Diagnostico
		$lcTipoDoc = trim($this->laDocumentoHC['cViaCod']) == '01' ? 'HU' : (trim($this->laDocumentoHC['cViaCod'] == '02' ? 'HC' : 'HP'));
		$loTextoDiag = new Texto_Diagnostico($this->aDiagnos, $taData['nIngreso'], $this->laDocumentoHC['nConsecHC']);
		$this->aDiagnos = $loTextoDiag->retornarDocumento($this->aDiagnos, $taData['nIngreso'], $this->laDocumentoHC['nConsecHC'], $lcTipoDoc);

		$lcDetalle = '';
		if (is_array($this->aDiagnos)) {

			if (count($this->aDiagnos) > 0) {

				foreach ($this->aDiagnos as $laDiagnos) {
					$lcDetalle .= $lcSL . trim($laDiagnos['DIAGNOS']) . '  ' . trim($laDiagnos['desc_d']) . $lcSL
						. (!empty($laDiagnos['TipoDiag']) ? '      Tipo diagnóstico   : ' . trim($laDiagnos['TipoDiag']) . $lcSL : '')
						. (!empty($laDiagnos['ClaseDiag']) ? '      Clase Diagnóstico  : ' . trim($laDiagnos['ClaseDiag']) . $lcSL : '')
						. (!empty($laDiagnos['TipoTrata']) ? '      Tratamiento        : ' . trim($laDiagnos['TipoTrata']) . $lcSL : '')
						. (!empty($laDiagnos['Analisis'])  && empty(trim($laDiagnos['Observa'])) ? '      Análisis - Conducta: ' . trim($laDiagnos['Analisis']) . $lcSL : '')
						. (!empty($laDiagnos['Descarte']) ? '      Justificación      : ' . trim($laDiagnos['Descarte']) . $lcSL : '')
						. (!empty(trim($laDiagnos['Observa'])) ? '      Observación        : ' . trim($laDiagnos['Observa']) . $lcSL : '');
				}

				if (!empty($lcDetalle)) {

					$laTr['aCuerpo'][] = ['titulo1', 'DIAGNOSTICOS'];
					$laTr['aCuerpo'][] = ['texto9',	$lcDetalle . $lcSL];
				}
			}
		}

		$lcDetalle = '<b>¿Tuvo Electrocardiograma?</b> ' . ($this->laDocumentoHC['nTuvoPlan'] == 1 ? 'SI' : 'NO') . '<br>';
		$lcDetalle .= ((!empty($this->laDocumentoHC['cInterpPlan'])) ? trim($this->laDocumentoHC['cInterpPlan']) . '<br>' : '');
		$lcDetalle .= (!empty($this->laDocumentoHC['cDescPlan'])) ? '<b>Descripción Plan de manejo:</b> ' . trim($this->laDocumentoHC['cDescPlan']) .  '<br>' : '';
		$lcDetalle .= (!empty($this->laDocumentoHC['cEscalaHA'])) ? trim($this->laDocumentoHC['cEscalaHA']) . '<br>' : '';
		$lcDetalle .= (!empty($this->laDocumentoHC['cEscalaCH'])) ? trim($this->laDocumentoHC['cEscalaCH']) . '<br>' : '';
		$lcDetalle .= (!empty($this->laDocumentoHC['cEscalaCR'])) ? trim($this->laDocumentoHC['cEscalaCR']) . '<br>' : '';
		$lcDetalle .= (!empty($this->laDocumentoHC['cEscalaSad'])) ? trim($this->laDocumentoHC['cEscalaSad']) . '<br>' : '';

		if (!empty($lcDetalle)) {
			$laTr['aCuerpo'][] = ['titulo1', 'PLAN DE MANEJO'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDetalle];
		}

		// Ordenes Ambulatorias
		if ($taData['cCodVia'] == '02') {

			$laDatos = [
				'nIngreso'		=> $taData['nIngreso'],
				'cTipDocPac' 	=> $taData['cTipDocPac'],
				'nNumDocPac' 	=> $taData['nNumDocPac'],
				'cTipDocDesc'   => $taData['oIngrPaciente']->oPaciente->aTipoId['NOMBRE'],
				'cSexoPaciente' => $taData['oIngrPaciente']->oPaciente->cSexo,
				'cTipoDocum' 	=> '',
				'cTipoProgr' 	=> 'ORDA01A',
				'tFechaHora'	=> '',
				'nConsecCita'	=> $taData['nConsecCita'],
				'nConsecCons'	=> $taData['nConsecCons'],
				'nConsecEvol'	=> '0',
				'nConsecDoc'	=> '',
				'cCUP'			=> '',
				'cCodVia'		=> $taData['cCodVia'],
				'cDescVia'		=> $taData['oIngrPaciente']->cDescVia,
				'cSecHab'		=> $taData['cSecHab'],
				'cPlan'		    => $taData['oIngrPaciente']->cPlanDescripcion,
				'cNombre'		=> $taData['oIngrPaciente']->oPaciente->getNombresApellidos(),
				'cFechaRealizado' => AplicacionFunciones::formatFechaHora('fecha', trim(mb_substr($taData['tFechaHora'], 0, 8)), '/'),
			];

			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], (new Doc_Ordenes())->ordenesHcEpi($laDatos));
		}

		$lcDetalle = (!empty($this->laDocumentoHC['cNombreConducta'])) ? 'Conducta a Seguir         : ' . trim($this->laDocumentoHC['cNombreConducta']) . $lcSL : '';
		$lcDetalle .= (!empty($this->laDocumentoHC['cEstadoPlan']) && !empty($this->laDocumentoHC['nEstadoPlan'])) ? 'Estado de Salida          : ' . trim($this->laDocumentoHC['cEstadoPlan']) . $lcSL : '';

		if (!empty($lcDetalle)) {
			$laTr['aCuerpo'][] = ['titulo1', 'CONDUCTA A SEGUIR'];
			$laTr['aCuerpo'][] = ['texto9', $lcDetalle];
		}

		/* Verifica si ingreso creado por TRAUMASOAT */
		$lcDetalle = '';
		if ($this->laDocumentoHC['cTipoCausa'] == '2') {
			$lcDetalle = 'Por los hallazgos clínicos y la anamnesis se deduce que la causa de los daños sufridos a la persona fue por accidente de transito.' . $lcSL;
		}

		if ($this->laDocumentoHC['nInformaPlan'] == 1) {
			$lcDetalle .= 'Se da información y educación al paciente y su familia sobre: Diagnóstico, Tratamiento, Pronóstico y se aclaran dudas ? SI';
			$laTr['aCuerpo'][] = ['lineah', ['x1' => 19, 'superior' => 8,]];
			$laTr['aCuerpo'][] = ['texto9', $lcDetalle];
		}

		$lcDetalle = '';
		if (!empty($this->laDocumentoHC['cTipoFinal'])) {
			$lcCodigoFinalidad = $this->laDocumentoHC['cTipoFinal'];
			$cTemp = trim($this->oDb->obtenerTabmae1('DE2TMA', 'CODFIN', "CL1TMA='$lcCodigoFinalidad'", null, ''));
			$lcDetalle = 'Finalidad: ' . $cTemp . $lcSL . $this->laDocumentoHC['cDescFinal'];

			if (!empty($lcDetalle)) {
				$laTr['aCuerpo'][] = ['titulo1', 'FINALIDAD'];
				$laTr['aCuerpo'][] = ['texto9', $lcDetalle];
			}
		}

		if (isset($this->aInterpreta)) {

			$lcDetalle = '';
			foreach ($this->aInterpreta as $laInter) {

				$lcDetalle .= '- ' . trim($laInter['descripcion'] ?? '') . $lcSL
					. '  RESULTADO: ' . ($laInter['interpreta'] == '1' ? 'NORMAL' : 'ANORMAL') . $lcSL
					. (!empty($laInter['observacion']) ? '  Interpretación: ' . trim($laInter['observacion']) . $lcSL : '');
			}

			if (!empty($lcDetalle)) {
				$laTr['aCuerpo'][] = ['titulo1', 'INTERPRETACION DE EXAMENES'];
				$laTr['aCuerpo'][] = ['texto9', $lcDetalle];
			}
		}

		//AQUI VA LA INFO DEL ESTUDIANTE

		//var_dump(count($this->aUsuarioRealiza));
		if (!empty($this->aUsuarioRealiza)) {

			$lcPrefijoUR = $this->oDb->obtenerTabmae1('DE1TMA', 'LIBROHC', "CL1TMA='FIRMATIP' AND CL2TMA='{$this->aUsuarioRealiza['codtipo']}' AND ESTTMA=''", null, '');
			$laTr['aCuerpo'][] = ['saltol', 5];
			$laTr['aCuerpo'][] = ['txthtml9', "<b>Realizado por:</b><br>{$lcPrefijoUR} {$this->aUsuarioRealiza['nombre']} {$this->aUsuarioRealiza['apellido']} - Documento: {$this->aUsuarioRealiza['rm']}"];
			$laTr['aCuerpo'][] = ['saltol', 5];
			$laTr['aCuerpo'][] = ['txthtml9', '<b>Avalado por:</b>'];
		}


		if (empty(trim($this->laDocumentoHC['cMedRea']))) {
			$laFirma = ['usuario' => $this->laDocumentoHC['cUsuario'] ?? '', 'prenombre' => 'Dr. ',];
		} else {
			$laFirma = ['registro' => $this->laDocumentoHC['cMedRea'], 'prenombre' => 'Dr. ', 'codespecialidad' => $this->laDocumentoHC['cEspRea'],];
		}

		if ($tlEpicrisis) {
			// Firma
			$laTr['Firmas'][] = $laFirma;
		} else {
			// Firma
			$laTr['aCuerpo'][] = ['firmas', [$laFirma,]];
		}

		// Notas Aclaratorias
		$laNotas = (new Doc_NotasAclaratorias())->notasAclaratoriasLibro($taData['nIngreso'], 'NOTAHC', 0);
		if (count($laNotas) > 0) {
			$laTr['aCuerpo'][] = ['lineah', []];
			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $laNotas);
		}


		// Escala
		$loEscalaNihss = new Doc_NIHSS($taData);

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	/* Array de datos de documento vacío */
	private function datosBlanco()
	{
		return [
			'cViaCod'			=> '',
			'cAceptadoPlan'		=> '',
			'cClaseDiag'		=> '',
			'cCodDiag'			=> '',
			'cConductaPlan'		=> '',
			'cCranealesCer'		=> '',
			'cDescFinal'		=> '',
			'cDescPlan'			=> '',
			'cDescTipoCausa'	=> '',
			'cDesDiag'			=> '',
			'cEscalaHA'			=> '',
			'cEscalaCH'			=> '',
			'cEscalaCR'			=> '',
			'cEscalaSad'		=> '',
			'cEspRea'			=> '',
			'cEstadoEst'		=> '',
			'cEstadoPlan'		=> '',
			'cEventoCausa'		=> '',
			'cExamenCer'		=> '',
			'cInformMed'		=> '',
			'cInterpPlan'		=> '',
			'cInterpretaCausa'	=> '',
			'cJustifiqueMed'	=> '',
			'cKeyActual'		=> '',
			'cMedRea'			=> '',
			'cMotivoCausa'		=> '',
			'cMotorCer'			=> '',
			'cNeuroCer'			=> '',
			'cNivelCer'			=> '',
			'cNombreConducta'	=> '',
			'cNomMedNC'			=> '',
			'cObsDiag'			=> '',
			'cObsFinal'			=> '',
			'cObsFinal'			=> '',
			'cPlan'				=> '',
			'cPrgCre'			=> '',
			'cReflejosCer'		=> '',
			'cRelacionCausa'	=> '',
			'cRemitidoPlan' 	=> '',
			'cSensitivoCer' 	=> '',
			'cSignosCer' 		=> '',
			'cTipoCausa' 		=> '',
			'cTipoDiag' 		=> '',
			'cTipoFinal' 		=> '',
			'cUsuCre' 			=> '',
			'cVia' 				=> '',
			'llhabilitar'		=> false,
			'lltraumasoat'		=> false,
			'nAñosEvol' 		=> 0,
			'nConsumeMed' 		=> 0,
			'nConsecHC' 		=> 0,
			'nDiasDuracion'		=> 0,
			'nDiasEvol' 		=> 0,
			'nEstadoPlan'		=> 0,
			'nFcest' 			=> 0,
			'nFrest'			=> 0,
			'nGlasgowCer' 		=> 0,
			'nHoraDuracion' 	=> 0,
			'nHoraEvol' 		=> 0,
			'nIndFCEst' 		=> 0,
			'nIndFREst' 		=> 0,
			'nIndTADEst' 		=> 0,
			'nIndTASEst' 		=> 0,
			'nInformaMed' 		=> 0,
			'nInformaPlan' 		=> 0,
			'nIntensidad' 		=> 0,
			'nInterpretaCausa' 	=> 0,
			'nMasacEst' 		=> 0,
			'nMesEvol' 			=> 0,
			'nMinDuracion'		=> 0,
			'nMinEvol' 			=> 0,
			'nNormalCer' 		=> 0,
			'nNormalEst' 		=> 0,
			'nNroIng' 			=> 0,
			'nPesoEst' 			=> 0,
			'nRegDiag' 			=> 0,
			'nRelacionCausa' 	=> 0,
			'nRemitidoPlan' 	=> 0,
			'nSaturaEst' 		=> 0,
			'nSegDuracion' 		=> 0,
			'nSegEvol' 			=> 0,
			'nSemaEvol' 		=> 0,
			'nSupcEst' 			=> 0,
			'nTADEst' 			=> 0,
			'nTallaEst' 		=> 0,
			'nTASEst' 			=> 0,
			'nTempEst' 			=> 0,
			'nTempREst' 		=> 0,
			'nTotalNishh' 		=> 0,
			'nTuvoPlan' 		=> 0,
			'Cita' 				=> 0,
			'Consecutivo' 		=> 0,
			'Consulta' 			=> 0,
			'Entidad' 			=> '',
			'Guardado'			=> false,
			'Ingreso'			=> 0,
			'Intervenciones'	=> 0,
			'Nombre' 			=> '',
			'Notify' 			=> '',
			'NumeroId' 			=> 0,
			'ObligaEscritura' 	=> false,
			'cPlan' 			=> '',
			'Sexo' 				=> '',
			'TipoId' 			=> 'C',
			'TipUsu' 			=> 10,
			'cViaHC' 			=> '',
			'nFecRea' 			=> 0,
			'nHorRea' 			=> 0,
			'cExCabeza' 		=> '',
			'cExCuello' 		=> '',
			'cExTorax' 			=> '',
			'cExSeno' 			=> '',
			'cExCorazon' 		=> '',
			'cExPulmon' 		=> '',
			'cExAbdomen' 		=> '',
			'cExGenitoU' 		=> '',
			'cExExtrem' 		=> '',
			'cExNeurol' 		=> '',
			'cExOdonto' 		=> '',
			'cExOtorrino' 		=> '',
			'cTextoCovid' 		=> '',
		];
	}


	function fnActualizarFechaHora($taDatos = [])
	{
		switch (true) {

			case $taDatos['INDICE'] == 2 && $taDatos['SUBINDICE'] == 1:
				$this->laDocumentoHC['cViaHC'] = trim(substr($taDatos['DESCRIP'], 5, 20));
				$this->laDocumentoHC['cViaCod'] = trim(substr($taDatos['DESCRIP'], 0, 2)) ?? '';
				break;

			case $taDatos['INDICE'] == 3 && $taDatos['INDICE'] == 1:

				switch ($this->cTipoDatos->OP1TMA) {
					case '0':
						$this->laDocumentoHC['nFecRea'] = 0;
						$this->laDocumentoHC['nHorRea'] = 0;
						break;
					case '1':
						$this->laDocumentoHC['nFecRea'] = $taDatos['FECHACRE'];
						$this->laDocumentoHC['nHorRea'] = $taDatos['HORACRE'];
						break;
					case '2':
						$this->laDocumentoHC['nFecRea'] = $taDatos['FECHAMOD'];
						$this->laDocumentoHC['nHorRea'] = $taDatos['HORAMOD'];
						break;
				}
				break;
		}
	}


	function fnCargarCausaExterna($taDatos = [])
	{
		if ($this->cTipoConsulta == 'C' || $this->cTipoConsulta == 'F') {
			$this->laDocumentoHC['cTipoCausa'] = $taDatos['SUBINDICE'] > 0 ? $taDatos['SUBINDICE'] : '';
		}

		switch (true) {

			case $taDatos['INDICE'] == 5 && $taDatos['SUBINDICE'] == 5 && empty($this->laDocumentoHC['cTipoCausa']):
				$this->laDocumentoHC['cTipoCausa'] = trim(substr($taDatos['DESCRIP'], 6, 4));
				$this->llDolor = intval(trim(substr($taDatos['DESCRIP'], 16, 4))) == 1 ? true : $this->llDolor;
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CLINEA'] < 1000 && ($taDatos['CODIGO'] == '1' || $taDatos['SUBINDICE'] == 10):
				$this->laDocumentoHC['cMotivoCausa'] .= $taDatos['DESCRIP'];
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CLINEA'] < 1000 && ($taDatos['CODIGO'] == '2' || $taDatos['SUBINDICE'] == 15):
				$this->laDocumentoHC['cEventoCausa'] .= $taDatos['DESCRIP'];
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CLINEA'] < 1000 && $taDatos['CODIGO'] == '3':
				$this->laDocumentoHC['cRelacionCausa'] .= $taDatos['DESCRIP'];
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CLINEA'] < 1000 && $taDatos['CODIGO'] == '4':
				$this->laDocumentoHC['cInterpretaCausa'] .= $taDatos['DESCRIP'];
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CLINEA'] == 1000:
				$this->llDolor = true;
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['SUBINDICE'] == 20 && $taDatos['CLINEA'] == 5:
				$this->laDocumentoHC['nIntensidad'] = intval(trim(substr($taDatos['DESCRIP'], 6, 4)));
				$this->laDocumentoHC['nSegDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 16, 4)));
				$this->laDocumentoHC['nMinDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 26, 4)));
				$this->laDocumentoHC['nSegDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 36, 4)));
				$this->laDocumentoHC['nDiasDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 46, 4)));
				$this->laDocumentoHC['nSegEvol'] = intval(trim(substr($taDatos['DESCRIP'], 56, 4)));
				$this->laDocumentoHC['nMinEvol'] = intval(trim(substr($taDatos['DESCRIP'], 66, 4)));
				$this->laDocumentoHC['nHoraEvol'] = intval(trim(substr($taDatos['DESCRIP'], 76, 4)));
				$this->laDocumentoHC['nDiasEvol'] = intval(trim(substr($taDatos['DESCRIP'], 86, 4)));
				$this->laDocumentoHC['nSemaEvol'] = intval(trim(substr($taDatos['DESCRIP'], 96, 4)));
				$this->laDocumentoHC['nMesEvol'] = intval(trim(substr($taDatos['DESCRIP'], 106, 4)));
				$this->laDocumentoHC['nAñosEvol'] = intval(trim(substr($taDatos['DESCRIP'], 116, 4)));
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['SUBINDICE'] == 20 && $taDatos['CLINEA'] < 5:
				for ($lnPosicion = 5; $lnPosicion <= 95; $lnPosicion += 10) {
					$lnValor = intval(trim(substr($taDatos['DESCRIP'], $lnPosicion, 4)));
					if (empty($lnValor) == false) {
						$lcCodigo = strval($taDatos['CLINEA'] . '-' . strval($lnPosicion + 1));
						$key = array_search($lcCodigo, array_column($this->aDolor, 'POSANT'));
						if (is_numeric($key)) {
							$this->aDolor[$key]['VALOR'] = 1;
						}
					}
				}
				break;

			case $taDatos['INDICE'] == 5 && $taDatos['CODIGO'] == '2' && $taDatos['CLINEA'] >= 30000:

				switch ($taDatos['CLINEA']) {

					case 30201:
						$this->laDocumentoHC['nSegDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30202:
						$this->laDocumentoHC['nMinDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30203:
						$this->laDocumentoHC['nHoraDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30204:
						$this->laDocumentoHC['nDiasDuracion'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30301:
						$this->laDocumentoHC['nIntensidad'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30601:
						$this->laDocumentoHC['nSegEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30602:
						$this->laDocumentoHC['nMinEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30603:
						$this->laDocumentoHC['nHoraEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30604:
						$this->laDocumentoHC['nDiasEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30605:
						$this->laDocumentoHC['nSemaEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30606:
						$this->laDocumentoHC['nMesEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					case 30607:
						$this->laDocumentoHC['nAñosEvol'] = intval(trim(substr($taDatos['DESCRIP'], 42, 8)));
						break;
					default:
						$key = array_search($taDatos['CLINEA'], array_column($this->aDolor, 'LINEA'));
						if (is_numeric($key)) {
							$this->aDolor[$key]['VALOR'] = 1;
						}
						break;
				}
				break;
			default:

				$this->laDocumentoHC['nFecRea'] = $taDatos['FECHACRE'];
				$this->laDocumentoHC['nHorRea'] = $taDatos['HORACRE'];
				break;
		}
	}


	function fnCargarAntecedentesCE($taDatos = [])
	{
		if ($taDatos['SUBINDICE'] == 12) {

			switch ($taDatos['CLINEA']) {
				case '1':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Edad de Menarquia: ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 3));
					break;

				case '2':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Embarazos: ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 3));
					break;

				case '3':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Parto ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 3));
					break;

				case '4':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Aborto ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 3));
					break;

				case '5':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Cesarea ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 3));
					break;

				case '6':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Ultima Regla: ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 51));
					break;

				case '7':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Planifica ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 2));
					break;

				case '8':
					$this->aDatosCE[$taDatos['CLINEA']]['titulo'] = 'Método ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 2));
					break;

				case '9':
					$this->aDatosCE['CLINEA']['titulo'] = 'Embarazo Ectópico ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 30, 40));
					break;

				case '10':
					$this->aDatosCE['CLINEA']['titulo'] = 'Ultima Citologia Vaginal ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 51));
					break;

				case '11':
					$this->aDatosCE['CLINEA']['titulo'] = 'Grupo Sanguineo ';
					$this->aDatosCE[$taDatos['CLINEA']]['detalle'] = trim(substr($taDatos['DESCRIP'], 21, 2));
					break;

				case '12':
					$this->aDatosCE['CLINEA']['titulo'] = 'Observaciones ';
					$this->aDatosCE['CLINEA']['detalle'] .= $taDatos['DESCRIP'];
					break;
			}
		} else {

			$laTituloCE = [
				'1' => 'CLINICO PATOLOGICO',
				'2' => 'NEONATAL',
				'3' => 'TRANSFUSIONALES',
				'4' => 'VACUNAS',
				'5' => '',
				'6' => 'QUIRURGICO',
				'7' => '',
				'8' => 'ALERGICOS',
				'9' => 'FARMACOLOGIA',
				'10' => 'TOXICOS',
				'11' => '',
				'12' => '',
				'13' => 'OCUPACIONALES',
				'14' => 'FAMILIARES',
				'15' => 'GENERAL',
			];

			$laDato = '';
			$laDato = $laTituloCE[$taDatos['SUBINDICE']];
			$key = array_search($laDato, array_column($this->aDatosCE, 'titulo'));

			if (!empty($key)) {
				$lnlinea = count($this->aDatosCE) + 1;
			} else {
				$lnlinea = $key + 1;
			}
			$this->aDatosCE[$lnlinea]['titulo'] = $laTituloCE[$taDatos['SUBINDICE']];
			if (!empty($lnlinea)) {
				$this->aDatosCE[$lnlinea]['detalle'] = ($this->aDatosCE[$lnlinea]['detalle'] ?? '') . $taDatos['DESCRIP'];
			}
		}
	}

	function fnCargarRevisionCE($taDatos = [])
	{

		$laTitulo = [
			'1' => 'VISION ',
			'2' => 'OTORRINO ',
			'3' => 'PULMONAR ',
			'4' => 'CARDIOVASCULAR ',
			'5' => 'GASTROINTESTINAL ',
			'6' => 'GENITURINARIO ',
			'7' => 'ENDOCRINO ',
			'8' => 'HEMATOPOPEYETICO ',
			'9' => 'DERMATOLOGICO ',
			'10' => 'OSTEO-MUSCULAR',
			'11' => 'NERVIOSO CENTRAL',
			'12' => 'PSIQUIATRICO',
			'13' => 'OTRO',
		];

		$this->aRevisionCE[$taDatos['SUBINDICE']]['titulo'] = $laTitulo[$taDatos['SUBINDICE']];
		$this->aRevisionCE[$taDatos['SUBINDICE']]['detalle'] = ($this->aRevisionCE[$taDatos['SUBINDICE']]['detalle'] ?? '') . $taDatos['DESCRIP'];
	}

	function fnCargarDatosCE()
	{

		$this->laDocumentoHC['ExCabeza'] = '';
		$this->laDocumentoHC['ExCuello'] = '';
		$this->laDocumentoHC['ExTorax'] = '';
		$this->laDocumentoHC['ExSeno'] = '';
		$this->laDocumentoHC['ExCorazon'] = '';
		$this->laDocumentoHC['ExPulmon'] = '';
		$this->laDocumentoHC['ExAbdomen'] = '';
		$this->laDocumentoHC['ExGenitoU'] = '';
		$this->laDocumentoHC['ExExtrem'] = '';
		$this->laDocumentoHC['ExNeurol'] = '';
		$this->laDocumentoHC['ExOdonto'] = '';
		$this->laDocumentoHC['ExOtorrino'] = '';
	}


	function fnCargarSignos($taDatos = [], $taDatosA = [])
	{

		$laSignosV = $this->oDb
			->from('RIAEXFL02')
			->where([
				'TIDEXF' => $taDatosA['cTipDocPac'],
				'NIDEXF' => $taDatosA['nNumDocPac'],
				'NIGEXF' => $taDatosA['nIngreso'],
				'CNSEXF' => $this->laDocumentoHC['nConsecHC'],
				'CCEEXF' => '0',
			])
			->get('array');
		if ($this->oDb->numRows() > 0) {
			$this->laDocumentoHC['nTASEst'] = $laSignosV['SSDEXF'] > 1 ? $laSignosV['SSDEXF'] : 0;
			$this->laDocumentoHC['nIndTASEst'] = $laSignosV['SSDEXF'] == 1 ? 1 : 0;
			$this->laDocumentoHC['nTADEst'] = $laSignosV['DSDEXF'] > 1 ? $laSignosV['DSDEXF'] : 0;
			$this->laDocumentoHC['nIndTADEst'] = $laSignosV['DSDEXF'] == 1 ? 1 : 0;
			$this->laDocumentoHC['nFCEst'] = $laSignosV['FRCEXF'] > 1 ? $laSignosV['FRCEXF'] : 0;
			$this->laDocumentoHC['nIndFCEst'] = $laSignosV['FRCEXF'] == 1 ? 1 : 0;
			$this->laDocumentoHC['nFREst'] = $laSignosV['FRREXF'] > 1 ? $laSignosV['FRREXF'] : 0;
			$this->laDocumentoHC['nIndFREst'] = $laSignosV['FRREXF'] == 1 ? 1 : 0;
			$this->laDocumentoHC['nTempEst'] = $laSignosV['TPREXF'];
			$this->laDocumentoHC['nTempREst'] = $laSignosV['TPTEXF'];
			$this->laDocumentoHC['nPesoEst'] = $laSignosV['PSOEXF'];
			$this->laDocumentoHC['nTallaEst'] = $laSignosV['TLLEXF'];
			$this->laDocumentoHC['nSupCEst'] = $laSignosV['SUPEXF'];
			$this->laDocumentoHC['nMasaCEst'] = $laSignosV['MASEXF'];
			$this->laDocumentoHC['nSaturaEst'] = $laSignosV['SATEXF'];
			$this->laDocumentoHC['cNivelCer'] = $laSignosV['NCNEXF'] == 9 ? '' : $laSignosV['NCNEXF'];
			$this->laDocumentoHC['nGlasgowCer'] = $laSignosV['GLCEXF'];
		}

		// Estado General

		if (isset($taDatos['INDICE'])) {
			switch (true) {
				case $taDatos['INDICE'] == 20 && $taDatos['SUBINDICE'] == 0 && $taDatos['CODIGO'] == 'OBS':
					if ($this->cTipoConsulta == 'U' or $this->cTipoConsulta == 'H') {
						$this->laDocumentoHC['cEstadoEst'] .=	$taDatos['DESCRIP'];
					}
					break;
				case $taDatos['INDICE'] == 20 && $taDatos['SUBINDICE'] == 0 && $taDatos['CODIGO'] == 1:
					if ($this->cTipoConsulta == 'C' or $this->cTipoConsulta == 'F') {
						$this->laDocumentoHC['cEstadoEst'] .=	$taDatos['DESCRIP'];
					}
					break;
			}
		}
	}

	function fnCargarExamenFisico($taDatos = [])
	{

		switch (true) {
			case $taDatos['INDICE'] == 20 && $taDatos['SUBINDICE'] > 6 && $taDatos['SUBINDICE'] <= 13 && $taDatos['SUBINDICE'] <> 10:

				if ($taDatos['CLINEA'] == 1) {
					$this->aExamen[trim($taDatos['SUBINDICE'])]['detalle'] .= (trim(substr($taDatos['DESCRIP'], 0, 1)) === '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) === '1') ? substr($taDatos['DESCRIP'], 1) : $taDatos['DESCRIP'];
				} else {
					$this->aExamen[trim($taDatos['SUBINDICE'])]['detalle'] .= $taDatos['DESCRIP'];
				}
				break;

			case $taDatos['INDICE'] == 20 && $taDatos['SUBINDICE'] == 10:

				switch ($taDatos['CODIGO']) {
					case '1':
						$this->laDocumentoHC['cMotorCer'] .= $taDatos['DESCRIP'];
						break;
					case '2':
						$this->laDocumentoHC['cSensitivoCer'] .= $taDatos['DESCRIP'];
						break;
					case '4':
						$this->laDocumentoHC['cExamenCer'] .= $taDatos['DESCRIP'];
						break;
					case '5':
						$this->laDocumentoHC['cCranealesCer'] .= $taDatos['DESCRIP'];
						break;
					case '6':
						$this->laDocumentoHC['cReflejosCer'] .= $taDatos['DESCRIP'];
						break;
					case '7':
						$this->laDocumentoHC['cSignosCer'] .= $taDatos['DESCRIP'];
						break;
					case '8':
						$this->laDocumentoHC['cNeuroCer'] .= $taDatos['DESCRIP'];
						break;
				}
				break;
		}
	}


	function fnCargarExamenFisicoCE($taDatos = [])
	{

		switch (true) {
				// Cabeza
			case $taDatos['SUBINDICE'] == 1 && $taDatos['CODIGO'] == 0 && empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) === '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) === '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExCabeza'] .= $lcDescrip;
				break;

				// Cuello
			case $taDatos['SUBINDICE'] == 2 && $taDatos['CODIGO'] == 0 && empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExCuello'] .= $lcDescrip;
				break;

				// Torax
			case $taDatos['SUBINDICE'] == 3 && $taDatos['CODIGO'] == 0 && $taDatos['SUBPARTE'] == '1':
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExTorax'] .= $lcDescrip;
				break;

				// Seno
			case $taDatos['SUBINDICE'] == 4 && $taDatos['CODIGO'] >= 1 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExSeno'] .= $lcDescrip;
				break;

				// Corazón
			case $taDatos['SUBINDICE'] == 5 && $taDatos['CODIGO'] >= 1 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExCorazon'] .= $lcDescrip;
				break;

				// Pulmon
			case $taDatos['SUBINDICE'] == 6 && $taDatos['CODIGO'] == 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExPulmon'] .= $lcDescrip;
				break;

				// Abdomen
			case $taDatos['SUBINDICE'] == 7 && $taDatos['CODIGO'] == 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExAbdomen'] .= $lcDescrip;
				break;

				// Genitourinario
			case $taDatos['SUBINDICE'] == 8 && $taDatos['CODIGO'] == 1 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExGenitoU'] .= $lcDescrip;
				break;

				// Extremidades
			case $taDatos['SUBINDICE'] == 9 && $taDatos['CODIGO'] >= 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExExtrem'] .= $lcDescrip;
				break;

				// Neurológico
			case $taDatos['SUBINDICE'] == 10 && $taDatos['CODIGO'] >= 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExNeurol'] .= $lcDescrip;
				break;

				// Odontológico
			case $taDatos['SUBINDICE'] == 12 && $taDatos['CODIGO'] == 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExOdonto'] .= $lcDescrip;
				break;

				// Otorrinnolaringológico
			case $taDatos['SUBINDICE'] == 13 && $taDatos['CODIGO'] == 0 &&  empty(trim($taDatos['SUBPARTE'])):
				$lcDescrip = trim(substr($taDatos['DESCRIP'], 0, 1)) == '0' || trim(substr($taDatos['DESCRIP'], 0, 1)) == '1' ? substr($taDatos['DESCRIP'], 1, 70) : $taDatos['DESCRIP'];
				$this->laDocumentoHC['ExOtorrino'] .= $lcDescrip;
				break;
		}
		$this->laDocumentoHC['ExCabeza'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExCabeza']);
		$this->laDocumentoHC['ExCuello'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExCuello']);
		$this->laDocumentoHC['ExTorax'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExTorax']);
		$this->laDocumentoHC['ExSeno'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExSeno']);
		$this->laDocumentoHC['ExCorazon'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExCorazon']);
		$this->laDocumentoHC['ExPulmon'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExPulmon']);
		$this->laDocumentoHC['ExAbdomen'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExAbdomen']);
		$this->laDocumentoHC['ExGenitoU'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExGenitoU']);
		$this->laDocumentoHC['ExExtrem'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExExtrem']);
		$this->laDocumentoHC['ExNeurol'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExNeurol']);
		$this->laDocumentoHC['ExOdonto'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExOdonto']);
		$this->laDocumentoHC['ExOtorrino'] = preg_replace('/\s[\s]+/', ' ', $this->laDocumentoHC['ExOtorrino']);
	}

	function fnCargarDiagnostico($taDatos = [])
	{

		$lcCodigoDiag = ($this->cTipoConsulta == 'U' or $this->cTipoConsulta == 'H') ? trim($taDatos['DESCRIP']) : trim($taDatos['SUBPARTE']);
		$lnReg = count($this->aDiagnos) + 1;
		$this->aDiagnos[$lnReg]['TRATAMIENTO'] = $taDatos['TRATAMIENTO'];
		$this->aDiagnos[$lnReg]['FECHADIA'] = $taDatos['FECHACRE'];
		$this->aDiagnos[$lnReg]['INDICE'] = $taDatos['SUBINDICE'];
		$this->aDiagnos[$lnReg]['DIAGNOS'] = $lcCodigoDiag;
		$this->aDiagnos[$lnReg]['CLIEDC'] = $taDatos['CODIGO'];
		$this->aDiagnos[$lnReg]['Observa'] = '';
		if (trim($lcCodigoDiag) != trim($taDatos['DESCRIP'])) {
			$this->aDiagnos[$lnReg]['Observa'] = $taDatos['DESCRIP'];
		}
	}

	function fnCargarInterpretacion($taDatos = [])
	{

		if (intval($taDatos['CLINEA']) == 1) {

			$lcCharReg = '¥';
			$laWordsReg = explode($lcCharReg, $taDatos['DESCRIP']);

			$this->aInterpreta[$taDatos['SUBINDICE']] = [
				'fecha' => $taDatos['FECHACRE'],
				'descripcion' => $laWordsReg[2],
				'interpreta' => $laWordsReg[0],
				'observacion' => '',
			];
		} else {
			$this->aInterpreta[$taDatos['SUBINDICE']]['observacion'] .= $taDatos['DESCRIP'];
		}
	}
}
