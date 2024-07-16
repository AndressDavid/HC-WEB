<?php
namespace NUCLEO;

require_once ('class.Diagnostico.php');
use NUCLEO\Diagnostico;

class Doc_Fisio_Fonoaudiologia
{
	protected $oDb;
	protected $cCodDx = '';
	protected $nCnsDeglucion = 0;
	protected $nCnsVoz = 0;
	protected $lExisteDeglucion = false;
	protected $lExisteVoz = false;
	protected $lExisteLenguaje = false;
	protected $lExisteHabla = false;
	protected $aDocumento = [];
	protected $aParametros = [];
	protected $aParImpresion = [];
	protected $aDiagnosticos = [];
	protected $aLenguajeConversacional = [];
	protected $aVrControlMotor = [
					1=>'L ',
					2=>'LP',
					3=>'NL',
				];
	protected $aVrDeglucion = [
					1=>'SI',
					2=>'NO',
				];
	protected $aReporte = [
					'cTitulo' => 'VALORACIÓN FONOAUDIOLOGÍA',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>false,],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	 //Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();

		$laLstTipos = [
			'AFE','ANA','ART','ATR','AUP','BRM','CNV','DFI','ECN','EDE','FFN','FOG','FOH','FSB',
			'FSN','FSP','HAH','IOD','LNP','MQR','MRA','MRC','NDN','NQQ','OOD','POA','PRS','RMV',
			'ROI','SIM','SIN','SON','TCF','TPD','ALI','ALF','DNT','CAR','CAS','TOS','NAU','CRG',
			'TON','TIG','TIS','ATA','CUE','FIL','ERT','MDR','AFA','LMS','DAH','DIA','SIG','ING'
			];
		$laParametros = $this->oDb
			->select('TABCOD, TABDSC, TABTIP')
			->from('PRMTAB')
			->where('TABCOD', '<>', '')
			->in('TABTIP', $laLstTipos)
			->getAll('array');
		foreach($laParametros as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aParametros[$laPar['TABTIP']][$laPar['TABCOD']] = $laPar['TABDSC'];
		}

		$laListaObjetivos = $this->oDb
			->select('trim(CONDOM) CODIGO, trim(LITDOM) LITERAL, trim(DESDOM) DESCRIPCION')
			->from('DOMFIS')
			->where('CONDOM', '<>', 0)
			->where('INDDOM', '=', 9)
			->in('LITDOM',['A','B','C','D'])
			->orderBy ('LITDOM, DESDOM')
			->getAll('array');
		$this->aListaObjetivos = $laListaObjetivos;

		$laControlMotor = $this->oDb
			->select('trim(A.CONDOM) ID, trim(B.TABCOD) AS CODIGO_GRUPO, trim(B.TABDSC) AS GRUPO, trim(A.DESDOM) DESCRIPCION')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', 'A.SISDOM=B.TABCOD', null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 10)
			->where('B.TABTIP', '=', 'FO1')
			->where('B.TABCOD', '<>', '')
			->orderBy ('SISDOM, CONDOM')
			->getAll('array');
		$this->aControlMotor = $laControlMotor;

		$laListaDeglucion = $this->oDb
			->select('trim(A.CONDOM) ID, trim(B.TABCOD) AS CODIGO_GRUPO, trim(B.TABDSC) AS GRUPO, trim(substr(trim(A.DESDOM), 1, 38)) DESCRIPCION')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', 'A.SISDOM=B.TABCOD', null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 11)
			->where('B.TABTIP', '=', 'FO2')
			->where('B.TABCOD', '<>', '')
			->orderBy ('SISDOM, CONDOM')
			->getAll('array');
		$this->aListaDeglucion = $laListaDeglucion;

		$laListaLenguaje = $this->oDb
			->select('trim(B.CONDOM) ID, trim(substr(trim(B.DESDOM), 1, 37)) AS DESCRIPCION, A.LITDOM AS LITERAL, SUBSTR(TRIM(A.LITDOM), 2, 1) AS SUB_LITERAL, A.IN2DOM AS CODIGO_GRUPO, trim(substr(trim(A.DESDOM), 1, 60)) DESCRIPCION_GRUPO')
			->from('DOMFIS AS A')
			->leftJoin('DOMFIS AS B', 'A.LITDOM=B.LITDOM', null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 12)
			->where('B.INDDOM', '=', 13)
			->orderBy ('A.IN2DOM, A.DESDOM')
			->getAll('array');
		$this->aLenguajeConversacional = $laListaLenguaje;

		$laListaImpresion = $this->oDb
			->select('CONDOM, DESDOM, LITDOM')
			->from('DOMFIS')
			->where('CONDOM', '<>', 0)
			->where('INDDOM', '=', 8)
			->in('LITDOM',['A','B','C','D'])
			->getAll('array');
		foreach($laListaImpresion as $laParImpre) {
			$laParImpre = array_map('trim', $laParImpre);
			$this->aParImpresion[$laParImpre['LITDOM']][$laParImpre['CONDOM']] = $laParImpre['DESDOM'];
		}

		$lnIndice = intval($taData['nConsecDoc']);
		if (!empty($lnIndice)) {
			$this->oDb->where(['IN2FNA'=>$lnIndice]);
		}
		$laFonoaudiologia = $this->oDb
			->select('IN2FNA, INDFNA, DESFNA, trim(USRFNA) USRFNA')
			->from('FISFNA')
			->where([
				'INGFNA'=>$taData['nIngreso'],
				'CEVFNA'=>$taData['nConsecEvol'],
				'CCIFNA'=>$taData['nConsecCita'],
			])
			->getAll('array');

		$lcObjetivosSugerenciasVoz = '';


		foreach($laFonoaudiologia as $laData) {
			//	VALORACIÓN FONOAUDIOLOGÍA DEGLUCIÓN
			if ($laData['IN2FNA']==1) {
				$this->lExisteDeglucion = true;
				switch (true) {
					case $laData['INDFNA']==1:
						$laDocumento['cFechaEvaluacion'] = trim(substr($laData['DESFNA'],0,14));
						$laDocumento['cEstadoConciencia'] = trim(substr($laData['DESFNA'],15,4));
						$laDocumento['cSeestaAsperandoDeglucion'] = trim(substr($laData['DESFNA'],25,1));
						$laDocumento['cAlimentacionDeglucion'] = trim(substr($laData['DESFNA'],29,1));
						$laDocumento['cSialorreaDeglucion'] = trim(substr($laData['DESFNA'],33,1));
						$laDocumento['cSealimentaenformaDeglucion'] = trim(substr($laData['DESFNA'],37,1));
						$laDocumento['cTraqueostomiaDeglucion'] = trim(substr($laData['DESFNA'],41,1));
						$laDocumento['cCanulaDeglucion'] = trim(substr($laData['DESFNA'],45,1));
						$laDocumento['cVenturyDeglucion'] = trim(substr($laData['DESFNA'],49,1));
						$laDocumento['cVentilacionmecanicaDeglucion'] = trim(substr($laData['DESFNA'],53,1));
						$laDocumento['cIntubacionDeglucion'] = trim(substr($laData['DESFNA'],57,1));
						$laDocumento['cEstadoDeglucion'] = trim(substr($laData['DESFNA'],61,1));
						$laDocumento['cDenticionDeglucion'] = trim(substr($laData['DESFNA'],65,1));
						$laDocumento['cCaraenreposoDeglucion'] = trim(substr($laData['DESFNA'],69,1));
						$laDocumento['cCaraensonrisaDeglucion'] = trim(substr($laData['DESFNA'],73,1));
						$laDocumento['cTosDeglucion'] = trim(substr($laData['DESFNA'],77,1));
						$laDocumento['cNauseasDeglucion'] = trim(substr($laData['DESFNA'],81,1));
						$laDocumento['cCierregloticoDeglucion'] = trim(substr($laData['DESFNA'],85,1));
						$laDocumento['cFrecuenciaDeglucion'] = trim(substr($laData['DESFNA'],89,1));
						$laDocumento['cImpresionDiagnostica'] = trim(substr($laData['DESFNA'],94,4));
						$laDocumento['cUsuarioRealiza'] = $laData['USRFNA'];
					break;

					case $laData['INDFNA']==2:
						$laDocumento['cDiagnosticoDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==3:
						$laDocumento['cTiempointubacionDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==4:
						$laDocumento['cObservaValoracionDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==5:
						$laDocumento['cObservaCtrMotorDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==6:
						$laDocumento['cObservaOrofunDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==7:
						$laDocumento['cPronosticoDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==8:
						$laDocumento['cIntervencionDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==9:
						$laDocumento['cObjetivosDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==10:
						$laDocumento['cControlMotor'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==11:
						$laDocumento['cDetalleDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==12:
						$laDocumento['cRecomendacionesDeglucion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==13:
						$laTemp = explode("~",trim($laData['DESFNA']));
						$laDocumento['cCumplioObjetivosDeglucion'] = $laTemp[0];
						$laDocumento['cSugerenciasDeglucion'] = $laTemp[1];
					break;

					case $laData['INDFNA']==30:
						$laDocumento['cTextoPandemiaDeglucion'] .= $laData['DESFNA'];
					break;
				}
			}

			//	VALORACIÓN FONOAUDIOLOGÍA VOZ
			if ($laData['IN2FNA']==2) {
				$this->lExisteVoz = true;

				switch (true) {
					case $laData['INDFNA']==1:
						$laDocumento['cFechaEvaluacionVoz'] = trim(substr($laData['DESFNA'],0,14));
						$laDocumento['cEstadoConcienciaVoz'] = trim(substr($laData['DESFNA'],15,4));
						$laDocumento['cSeestaAsperandoVoz'] = trim(substr($laData['DESFNA'],25,1));
						$laDocumento['cAlimentacionVoz'] = trim(substr($laData['DESFNA'],29,1));
						$laDocumento['cSialorreaVoz'] = trim(substr($laData['DESFNA'],33,1));
						$laDocumento['cSealimentaenformaVoz'] = trim(substr($laData['DESFNA'],37,1));
						$laDocumento['cTraqueostomiaVoz'] = trim(substr($laData['DESFNA'],41,1));
						$laDocumento['cCanulaVoz'] = trim(substr($laData['DESFNA'],45,1));
						$laDocumento['cVenturyVoz'] = trim(substr($laData['DESFNA'],49,1));
						$laDocumento['cVentilacionmecanicaVoz'] = trim(substr($laData['DESFNA'],53,1));
						$laDocumento['cIntubacionVoz'] = trim(substr($laData['DESFNA'],57,1));
						$laDocumento['cTonopercepcionVoz'] = trim(substr($laData['DESFNA'],61,1));
						$laDocumento['cTimbreFuentegloticaVoz'] = trim(substr($laData['DESFNA'],65,1));
						$laDocumento['cTimbreFiltrosresonVoz'] = trim(substr($laData['DESFNA'],69,1));
						$laDocumento['cConfidencialVoz'] = trim(substr($laData['DESFNA'],73,1));
						$laDocumento['cConversacionalVoz'] = trim(substr($laData['DESFNA'],77,1));
						$laDocumento['cProyectadaVoz'] = trim(substr($laData['DESFNA'],81,1));
						$laDocumento['cEsfuerzovocalVoz'] = trim(substr($laData['DESFNA'],85,1));
						$laDocumento['cIngurgitacionvenosaVoz'] = trim(substr($laData['DESFNA'],89,1));
						$laDocumento['cVozdellamadaVoz'] = trim(substr($laData['DESFNA'],93,1));
						$laDocumento['cUsuarioRealiza'] = $laData['USRFNA'];
					break;

					case $laData['INDFNA']==2:
						$laDocumento['cADerecha'] = trim(substr($laData['DESFNA'],0,4));
						$laDocumento['cAIzquierda'] = trim(substr($laData['DESFNA'],4,4));
						$laDocumento['cEDerecha'] = trim(substr($laData['DESFNA'],8,4));
						$laDocumento['cEIzquierda'] = trim(substr($laData['DESFNA'],12,4));
						$laDocumento['cIDerecha'] = trim(substr($laData['DESFNA'],16,4));
						$laDocumento['cIIzquierda'] = trim(substr($laData['DESFNA'],20,4));
						$laDocumento['cODerecha'] = trim(substr($laData['DESFNA'],24,4));
						$laDocumento['cOIzquierda'] = trim(substr($laData['DESFNA'],28,4));
						$laDocumento['cUDerecha'] = trim(substr($laData['DESFNA'],32,4));
						$laDocumento['cUIzquierda'] = trim(substr($laData['DESFNA'],36,4));
						$laDocumento['cMDerecha'] = trim(substr($laData['DESFNA'],40,4));
						$laDocumento['cMIzquierda'] = trim(substr($laData['DESFNA'],44,4));
						$laDocumento['cNDerecha'] = trim(substr($laData['DESFNA'],48,4));
						$laDocumento['cNIzquierda'] = trim(substr($laData['DESFNA'],52,4));
						$laDocumento['cAtaqueVoz'] = trim(substr($laData['DESFNA'],56,4));
						$laDocumento['cCuerpoVoz'] = trim(substr($laData['DESFNA'],60,4));
						$laDocumento['cFilaturaVoz'] = trim(substr($laData['DESFNA'],64,4));
						$laDocumento['cAMaximo1'] = trim(substr($laData['DESFNA'],68,4));
						$laDocumento['cAMaximo2'] = trim(substr($laData['DESFNA'],72,4));
						$laDocumento['cAMaximo3'] = trim(substr($laData['DESFNA'],76,4));
						$laDocumento['cAMaximo4'] = trim(substr($laData['DESFNA'],80,4));
						$laDocumento['cSCompetencia1'] = trim(substr($laData['DESFNA'],84,4));
						$laDocumento['cSCompetencia2'] = trim(substr($laData['DESFNA'],88,4));
						$laDocumento['cSCompetencia3'] = trim(substr($laData['DESFNA'],92,4));
						$laDocumento['cSCompetencia4'] = trim(substr($laData['DESFNA'],96,4));
						$laDocumento['cZMaximo1'] = trim(substr($laData['DESFNA'],100,4));
						$laDocumento['cZMaximo2'] = trim(substr($laData['DESFNA'],104,4));
						$laDocumento['cZMaximo3'] = trim(substr($laData['DESFNA'],108,4));
						$laDocumento['cZMaximo4'] = trim(substr($laData['DESFNA'],112,4));
						$laDocumento['cIncordRespiratoriaVoz'] = trim(substr($laData['DESFNA'],116,4));
						$laDocumento['cSealteraTonoVoz'] = trim(substr($laData['DESFNA'],120,4));
						$laDocumento['cHayesfuerzovocalVoz'] = trim(substr($laData['DESFNA'],124,4));
						$laDocumento['cImprecisionarticularVoz'] = trim(substr($laData['DESFNA'],128,4));
						$laDocumento['cRespiratorioTipoVoz'] = trim(substr($laData['DESFNA'],132,4));
						$laDocumento['cRespiratorioReposoVoz'] = trim(substr($laData['DESFNA'],136,4));
						$laDocumento['cRespiratorioFonacionVoz'] = trim(substr($laData['DESFNA'],140,4));
						$laDocumento['cImpresionDiagnosticaVoz'] = trim(substr($laData['DESFNA'],144,10));
					break;

					case $laData['INDFNA']==3:
						$laDocumento['cDiagnosticoVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==4:
						$laDocumento['cTiempointubacionVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==5:
						$laDocumento['cPronosticoVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==6:
						$laDocumento['cIntervencionVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==7:
						$laDocumento['cObjetivosVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==8:
						$laDocumento['cRecomendacionesVoz'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==9:
						$lcObjetivosSugerenciasVoz .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==30:
						$laDocumento['cTextoPandemiaVoz'] .= $laData['DESFNA'];
					break;
				}
			}

			//	VALORACIÓN FONOAUDIOLOGÍA LENGUAJE
			if ($laData['IN2FNA']==3) {
				$this->lExisteLenguaje = true;
				switch (true) {
					case $laData['INDFNA']==1:
						$laDocumento['cFechaEvaluacionLenguaje'] = trim(substr($laData['DESFNA'],0,14));
						$laDocumento['cEstadoConcienciaLenguaje'] = trim(substr($laData['DESFNA'],15,4));
						$laDocumento['cTrastornoLenguaje'] = trim(substr($laData['DESFNA'],22,4));
						$laDocumento['cTipoTrastornoLenguaje'] = trim(substr($laData['DESFNA'],26,4));
						$laDocumento['cApraxiaOralLenguaje'] = trim(substr($laData['DESFNA'],30,4));
						$laDocumento['cImpresionDiagnosticaLenguaje'] = trim(substr($laData['DESFNA'],34,4));
						$laDocumento['cUsuarioRealiza'] = $laData['USRFNA'];
					break;

					case $laData['INDFNA']==2:
						$laDocumento['cDiagnosticoLenguaje'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==3:
						$laDocumento['cObsLenguajeConversacion'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==4:
						$laDocumento['cObsLenguajeComprension'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==5:
						$laDocumento['cObsLenguajeExpesionOral'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==6:
						$laDocumento['cObsLenguajeValoracionLectura'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==7:
						$laDocumento['cObsLenguajeValoracionEscritura'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==8:
						$laDocumento['cObsLenguajeSignosLinguisticos'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==9:
						$laDocumento['cPronosticoLenguaje'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==10:
						$laDocumento['cIntervencionLenguaje'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==11:
						$laDocumento['cObjetivosLenguaje'] = $laData['DESFNA'];
					break;

					case $laData['INDFNA']==12:
						$laDocumento['cLenguajeConversacional'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==13:
						$laDocumento['cRecomendacionesLenguaje'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==14:
						$laTemp = explode("~",trim($laData['DESFNA']));
						$laDocumento['cCumplioObjetivosLenguaje'] = $laTemp[0];
						$laDocumento['cSugerenciasLenguaje'] = $laTemp[1];
					break;

					case $laData['INDFNA']==30:
						$laDocumento['cTextoPandemiaLenguaje'] .= $laData['DESFNA'];
					break;
				}
			}

			//	VALORACIÓN FONOAUDIOLOGÍA HABLA
			if ($laData['IN2FNA']==4) {
				$this->lExisteHabla = true;
				switch (true) {
					case $laData['INDFNA']==1:
						$laDocumento['cFechaEvaluacionHabla'] = trim(substr($laData['DESFNA'],0,14));
						$laDocumento['cEstadoConcienciaHabla'] = trim(substr($laData['DESFNA'],15,4));
						$laDocumento['cTrastornoHabla'] = trim(substr($laData['DESFNA'],22,4));
						$laDocumento['cTipoTrastornoHabla'] = trim(substr($laData['DESFNA'],26,4));
						$laDocumento['cDiadococinesis'] = trim(substr($laData['DESFNA'],30,4));
						$laDocumento['cIntegibilidad'] = trim(substr($laData['DESFNA'],34,4));
						$laDocumento['cImpresionDiagnosticaHabla'] = trim(substr($laData['DESFNA'],38,4));
						$laDocumento['cUsuarioRealiza'] = $laData['USRFNA'];
					break;

					case $laData['INDFNA']==2:
						$laDocumento['cDiagnosticoHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==3:
						$laDocumento['cValoraLabioDentalesHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==4:
						$laDocumento['cValoraLinguoDentalesHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==5:
						$laDocumento['cValoraLinguAlveolaresHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==6:
						$laDocumento['cValoraPalatalesHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==7:
						$laDocumento['cValoraVelaresHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==8:
						$laDocumento['cValoraVibraSimpleHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==9:
						$laDocumento['cValoraVibraMultiHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==10:
						$laDocumento['cValoraBilabialesHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==11:
						$laDocumento['cValoraObservacionHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==12:
						$laDocumento['cPronosticoHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==13:
						$laDocumento['cIntervencionHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==15:
						$laDocumento['cRecomendacionesHabla'] .= $laData['DESFNA'];
					break;

					case $laData['INDFNA']==14:
						$laDocumento['cObjetivosHabla'] = $laData['DESFNA'];
					break;

					case $laData['INDFNA']==16:
						$laTemp = explode("~",trim($laData['DESFNA']));
						$laDocumento['cCumplioObjetivosHabla'] = $laTemp[0];
						$laDocumento['cSugerenciasHabla'] = $laTemp[1] ?? '';
					break;

					case $laData['INDFNA']==30:
						$laDocumento['cTextoPandemiaHabla'] .= $laData['DESFNA'];
					break;
				}
			}
		}

		$laKeyTrim = [
			'cTiempointubacionDeglucion','cObservaValoracionDeglucion','cIntervencionDeglucion',
			'cRecomendacionesDeglucion','cCumplioObjetivosDeglucion','cSugerenciasDeglucion',
			'cPronosticoDeglucion','cControlMotor','cDetalleDeglucion','cPronosticoVoz',
			'cIntervencionVoz','cRecomendacionesVoz','cSugerenciasLenguaje','cPronosticoLenguaje',
			'cIntervencionLenguaje','cRecomendacionesLenguaje','cObsLenguajeConversacion',
			'cObsLenguajeComprension','cObsLenguajeExpesionOral','cObsLenguajeValoracionLectura',
			'cObsLenguajeValoracionEscritura','cObsLenguajeSignosLinguisticos','cPronosticoHabla',
			'cIntervencionHabla','cRecomendacionesHabla','cDiagnosticoDeglucion','cDiagnosticoVoz',
			'cObservaCtrMotorDeglucion','cObservaOrofunDeglucion','cObjetivosDeglucion',
			'cTiempointubacionVoz','cObjetivosVoz','cObjetivosLenguaje','cLenguajeConversacional',
			'cObjetivosHabla','cDiagnosticoLenguaje','cDiagnosticoHabla',
			'cValoraLabioDentalesHabla','cValoraLinguoDentalesHabla','cValoraLinguAlveolaresHabla',
			'cValoraPalatalesHabla','cValoraVelaresHabla','cValoraVibraSimpleHabla',
			'cValoraVibraMultiHabla','cValoraBilabialesHabla','cValoraObservacionHabla','cTextoPandemiaDeglucion',
			'cTextoPandemiaVoz','cTextoPandemiaLenguaje','cTextoPandemiaHabla',
		];
		foreach($laKeyTrim as $lcKey)
			$laDocumento[$lcKey] = trim($laDocumento[$lcKey]);


		if (!empty($laDocumento['cControlMotor'])){
			$laCmotor = explode(',',$laDocumento['cControlMotor']);
			foreach($laCmotor as $lcValorMotor){
				$laItem = explode('|', $lcValorMotor);
				$laDocumento['aControlMotor'][$laItem[0]] = $laItem[1];
			}
		}

		if (!empty($laDocumento['cDetalleDeglucion'])){
			$laDeglucion = explode(',',$laDocumento['cDetalleDeglucion']);
			foreach($laDeglucion as $lcValorDeglucion){
				$laItemd = explode('|', $lcValorDeglucion);
				$laDocumento['aListaDeglucion'][$laItemd[0]] = $laItemd[1];
			}
		}

		if (!empty($laDocumento['cLenguajeConversacional'])){
			$laLenguajeConcersacional = explode(',',$laDocumento['cLenguajeConversacional']);
			foreach($laLenguajeConcersacional as $lcValorLenguajeConcersacional){
				$laItemd = explode('|', $lcValorLenguajeConcersacional);
				$laDocumento['aLenguajeConversacional'][$laItemd[0]] = $laItemd[1].$laItemd[2];
			}
		}

		$laTemp = explode("~",$lcObjetivosSugerenciasVoz);
		$laDocumento['cCumplioObjetivosVoz'] = trim($laTemp[0]);
		$laDocumento['cObjetivosSugerenciasVoz'] = trim($laTemp[1] ?? '');

		$this->aDocumento = $laDocumento;
	}


	private function prepararInforme($taData) {
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$laTr=[];

		/*******************************************  DEGLUCION  *******************************************/
		$loListaDiagnostico = new Diagnostico();
		$aDiagnosticos = $loListaDiagnostico->listadiagnosticos($taData['nIngreso']);
		if(is_array($aDiagnosticos)==true){
			if(count($aDiagnosticos)>0){
				foreach($aDiagnosticos as $laDataCie10) {
					$loListaDiagnostico->cargar(trim($laDataCie10['CODIGO']),$laDataCie10['FECHA']);
					$this->cCodDx .= trim($laDataCie10['CODIGO']) .' - ' .$loListaDiagnostico->getTexto() .$lcSL;
				}
			}
		}

		//	INICIA DEGLUCION
		if ($this->lExisteDeglucion){
			$cSeestaAsperandoDeglucion = !empty($this->aDocumento['cSeestaAsperandoDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cSeestaAsperandoDeglucion']] :'';
			$cAlimentacionDeglucion = !empty($this->aDocumento['cAlimentacionDeglucion'])? $this->aParametros['ALI'][$this->aDocumento['cAlimentacionDeglucion']] :'';
			$cSialorreaDeglucion = !empty($this->aDocumento['cSialorreaDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cSialorreaDeglucion']] :'';
			$cSealimentaenformaDeglucion = !empty($this->aDocumento['cSealimentaenformaDeglucion'])? $this->aParametros['ALF'][$this->aDocumento['cSealimentaenformaDeglucion']] :'';
			$cTraqueostomiaDeglucion = !empty($this->aDocumento['cTraqueostomiaDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cTraqueostomiaDeglucion']] :'';
			$cCanulaDeglucion = !empty($this->aDocumento['cCanulaDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cCanulaDeglucion']] :'';
			$cVenturyDeglucion = !empty($this->aDocumento['cVenturyDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cVenturyDeglucion']] :'';
			$cVentilacionmecanicaDeglucion = !empty($this->aDocumento['cVentilacionmecanicaDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cVentilacionmecanicaDeglucion']] :'';
			$cIntubacionDeglucion = !empty($this->aDocumento['cIntubacionDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cIntubacionDeglucion']] :'';
			$cEstadoDeglucion = !empty($this->aDocumento['cEstadoDeglucion'])? $this->aParametros['CNV'][$this->aDocumento['cEstadoDeglucion']] :'';
			if ($this->aDocumento['cDenticionDeglucion']=='1' || $this->aDocumento['cDenticionDeglucion']=='2'){
				$cDenticionDeglucion = !empty($this->aDocumento['cDenticionDeglucion'])? $this->aParametros['DNT'][$this->aDocumento['cDenticionDeglucion']] :'';
			}
			$cCaraenreposoDeglucion = !empty($this->aDocumento['cCaraenreposoDeglucion'])? $this->aParametros['CAR'][$this->aDocumento['cCaraenreposoDeglucion']] :'';
			$cCaraensonrisaDeglucion = !empty($this->aDocumento['cCaraensonrisaDeglucion'])? $this->aParametros['CAS'][$this->aDocumento['cCaraensonrisaDeglucion']] :'';
			$cTosDeglucion = !empty($this->aDocumento['cTosDeglucion'])? $this->aParametros['TOS'][$this->aDocumento['cTosDeglucion']] :'';
			$cNauseasDeglucion = !empty($this->aDocumento['cNauseasDeglucion'])? $this->aParametros['NAU'][$this->aDocumento['cNauseasDeglucion']] :'';
			$cCierregloticoDeglucion = !empty($this->aDocumento['cCierregloticoDeglucion'])? $this->aParametros['CRG'][$this->aDocumento['cCierregloticoDeglucion']] :'';
			$cFrecuenciaDeglucion = !empty($this->aDocumento['cFrecuenciaDeglucion'])? $this->aParametros['SON'][$this->aDocumento['cFrecuenciaDeglucion']] :'';
			$cEstadoConciencia = !empty($this->aDocumento['cEstadoConciencia'])? $this->aParametros['ECN'][$this->aDocumento['cEstadoConciencia']] :'';
			$cImpresionDiagnostica = !empty($this->aDocumento['cImpresionDiagnostica'])? $this->aParImpresion['A'][$this->aDocumento['cImpresionDiagnostica']] :'';
			$lnFechaHoraEvaluacion = AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['cFechaEvaluacion']);
			$laTr['aCuerpo'][] = ['titulo1', AplicacionFunciones::mb_str_pad(' VALORACIÓN FONOAUDIOLOGÍA DEGLUCIÓN ', 90, "-", STR_PAD_BOTH)];

			if (!empty(trim($cEstadoConciencia))) {
				$laTr['aCuerpo'][] = ['texto9',	'Fecha evaluación  : ' .$lnFechaHoraEvaluacion .'    Estado conciencia : ' .$cEstadoConciencia];
			} else {
				$laTr['aCuerpo'][] = ['texto9', 'Fecha evaluación  : ' .$lnFechaHoraEvaluacion];
			}

			if (!empty($this->aDocumento['cTextoPandemiaDeglucion'])){
				$laTr['aCuerpo'][] = ['titulo2', ' '];
				$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cTextoPandemiaDeglucion']];
			}

			if (!empty($this->cCodDx) || !empty($this->aDocumento['cDiagnosticoDeglucion'])) {
				$laTr['aCuerpo'][] = ['titulo1', 'DATOS GENERALES'];
				$laTr['aCuerpo'][] = ['titulo2', 'DIAGNÓSTICO MÉDICO'];
				if(!empty($this->cCodDx))
					$laTr['aCuerpo'][] = ['texto9', $this->cCodDx];
				if(!empty($this->aDocumento['cDiagnosticoDeglucion']))
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cDiagnosticoDeglucion']];
			}

			if (!empty($cSeestaAsperandoDeglucion) || !empty($cAlimentacionDeglucion) ||
				!empty($cSialorreaDeglucion) || !empty($cSealimentaenformaDeglucion) ||
				!empty($cTraqueostomiaDeglucion) || !empty($cCanulaDeglucion) ||
				!empty($cVenturyDeglucion) || !empty($cVentilacionmecanicaDeglucion) ||
				!empty($cIntubacionDeglucion) || !empty($this->aDocumento['cTiempointubacionDeglucion']) )
			{
				$laTr['aCuerpo'][] = ['titulo2', 'ANTECEDENTES GENERALES'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cSeestaAsperandoDeglucion)?     AplicacionFunciones::mb_str_pad('Se esta aspirando    : '. $cSeestaAsperandoDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cAlimentacionDeglucion)?        AplicacionFunciones::mb_str_pad('Alimentación         : '. $cAlimentacionDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cSialorreaDeglucion)?           AplicacionFunciones::mb_str_pad('Sialorrea            : '. $cSialorreaDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cSealimentaenformaDeglucion)?   AplicacionFunciones::mb_str_pad('Se alimenta en forma : '. $cSealimentaenformaDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cTraqueostomiaDeglucion)?       AplicacionFunciones::mb_str_pad('Traqueostomía        : '. $cTraqueostomiaDeglucion, 48, ' ') :'' ;
				$lcValorTexto .= !empty($cCanulaDeglucion)?              AplicacionFunciones::mb_str_pad('Cánula               : '. $cCanulaDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cVenturyDeglucion)?             AplicacionFunciones::mb_str_pad('Ventury              : '. $cVenturyDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cVentilacionmecanicaDeglucion)? AplicacionFunciones::mb_str_pad('Ventilación mecánica : '. $cVentilacionmecanicaDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cIntubacionDeglucion)?                           AplicacionFunciones::mb_str_pad('Intubación           : '. $cIntubacionDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($this->aDocumento['cTiempointubacionDeglucion'])? AplicacionFunciones::mb_str_pad('Tiempo intubación    : '. $this->aDocumento['cTiempointubacionDeglucion'], 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($cEstadoDeglucion) || !empty($cDenticionDeglucion) ||
				!empty($cCaraenreposoDeglucion) || !empty($cCaraensonrisaDeglucion))
			{
				$laTr['aCuerpo'][] = ['titulo1', 'VALORACIÓN ASPECTOS OROFUNCIONALES'];
				$laTr['aCuerpo'][] = ['titulo2', 'ANATOMÍA ORAL'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cEstadoDeglucion)?         AplicacionFunciones::mb_str_pad('Estado               : '. $cEstadoDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cDenticionDeglucion)?      AplicacionFunciones::mb_str_pad('Dentición            : '. $cDenticionDeglucion, 47, ' ') : 'Dentición            : ' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cCaraenreposoDeglucion)?   AplicacionFunciones::mb_str_pad('Cara en reposo       : '. $cCaraenreposoDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cCaraensonrisaDeglucion)?  AplicacionFunciones::mb_str_pad('Cara en sonrisa      : '. $cCaraensonrisaDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($cTosDeglucion) || !empty($cNauseasDeglucion) || !empty($cCierregloticoDeglucion) ||
				!empty($cFrecuenciaDeglucion) || !empty($this->aDocumento['cObservaValoracionDeglucion']))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'REFLEJOS PROTECTIVOS DE LA DEGLUCIÓN'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cTosDeglucion)?            AplicacionFunciones::mb_str_pad('Tos                  : '. $cTosDeglucion, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cNauseasDeglucion)?        AplicacionFunciones::mb_str_pad('Nauseas              : '. $cNauseasDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cCierregloticoDeglucion)?  AplicacionFunciones::mb_str_pad('Cierre glótico       : '. $cCierregloticoDeglucion, 48, ' ') :'' ;
				$lcValorTexto .= !empty($cFrecuenciaDeglucion)?     AplicacionFunciones::mb_str_pad('Frecuencia Deglutoria: '. $cFrecuenciaDeglucion, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				if (!empty($this->aDocumento['cObservaValoracionDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'OBSERVACIÓN ANATOMÍA ORAL / REFLEJOS PROTECTIVOS DE LA DEGLUCIÓN'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cObservaValoracionDeglucion']]; }
			}

			if (!empty($this->aDocumento['cControlMotor']) || !empty($this->aDocumento['cDetalleDeglucion'])) {
				if (!empty($this->aDocumento['cControlMotor'])){
					$laTr['aCuerpo'][] = ['titulo2', 'CONTROL MOTOR'];

					$actual = 0;
					$laClaveMotor= array_keys($this->aDocumento['aControlMotor']);
					foreach($this->aControlMotor as $laDataGrupoControl) {
						if ($laDataGrupoControl['CODIGO_GRUPO'] !== $actual) {
							$lbImprimioGrupo = false;
							if (!empty($lcCMotor)){
								$laTr['aCuerpo'][] = ['texto9', $lcCMotor];
							}
							$lcCMotor=$lcSeparador='';
							$actual = $laDataGrupoControl['CODIGO_GRUPO'];
						}
						if (in_array($laDataGrupoControl['ID'],$laClaveMotor)){
							if (!$lbImprimioGrupo) {
								$laTr['aCuerpo'][] = ['titulo5', AplicacionFunciones::mb_str_pad($laDataGrupoControl['GRUPO'],40).'( L = Logra, LP = Logra parcialmente, NL = No logra )'];
								$lbImprimioGrupo=true;
							}
							$lcValorMotor=$this->aVrControlMotor[$this->aDocumento['aControlMotor'][$laDataGrupoControl['ID']]];
							$lcCMotor.=AplicacionFunciones::mb_str_pad('['.$lcValorMotor.'] '.$laDataGrupoControl['DESCRIPCION'],42,' ').$lcSeparador;
							$lcSeparador=$lcSeparador==''? $lcSL : '';
						}
					}
					if (!empty($lcCMotor)) $laTr['aCuerpo'][] = ['texto9', $lcCMotor];

					if (!empty($this->aDocumento['cObservaCtrMotorDeglucion'])) {
						$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIONES'];
						$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cObservaCtrMotorDeglucion']];
					}
				}

				if (!empty($this->aDocumento['cDetalleDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'PROCESOS DE LA DEGLUCIÓN'];

					$actual = 0;
					$laClaveDetalleDeglucion= array_keys($this->aDocumento['aListaDeglucion']);

					foreach($this->aListaDeglucion as $laDataControlDeglucion) {
						if ($laDataControlDeglucion['CODIGO_GRUPO'] !== $actual) {
							$lbImprimioGrupo = false;
							if (!empty($lcCDeglucion)){
								$laTr['aCuerpo'][] = ['texto9', $lcCDeglucion];
							}
							$lcCDeglucion=$lcSeparador='';
							$actual = $laDataControlDeglucion['CODIGO_GRUPO'];
						}
						if (in_array($laDataControlDeglucion['ID'],$laClaveDetalleDeglucion)){
							if (!$lbImprimioGrupo) {
								$laTr['aCuerpo'][] = ['titulo5', $laDataControlDeglucion['GRUPO']];
								$lbImprimioGrupo=true;
							}
							$lcValorDeglucion=$this->aVrDeglucion[$this->aDocumento['aListaDeglucion'][$laDataControlDeglucion['ID']]];
							$lcCDeglucion.=AplicacionFunciones::mb_str_pad(' - '.$lcValorDeglucion.' '.$laDataControlDeglucion['DESCRIPCION'],42,' ').$lcSeparador;
							$lcSeparador=$lcSeparador==''? $lcSL : '';
						}
					}
					if (!empty($lcCDeglucion)) $laTr['aCuerpo'][] = ['texto9', $lcCDeglucion];

					if (!empty($this->aDocumento['cObservaOrofunDeglucion'])) {
						$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIÓN PROCESOS DE LA DEGLUCIÓN'];
						$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cObservaOrofunDeglucion']];
					}
				}
			}

			if (!empty($cImpresionDiagnostica) || !empty($this->aDocumento['cIntervencionDeglucion'])
				|| !empty($this->aDocumento['cObjetivosDeglucion'])	|| !empty($this->aDocumento['cSugerenciasDeglucion'])
				|| !empty($this->aDocumento['cPronosticoDeglucion'])){
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO E INTERVENCIÓN'];

				if (!empty(trim($cImpresionDiagnostica))){
					$laTr['aCuerpo'][] = ['titulo2', 'IMPRESIÓN DIAGNÓSTICA'];
					$laTr['aCuerpo'][] = ['texto9',$cImpresionDiagnostica]; }

				if (!empty($this->aDocumento['cObjetivosDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'OBJETIVOS'];

					foreach($this->aListaObjetivos as $laDataObj) {
						if ($laDataObj['LITERAL']='A' && strstr($this->aDocumento['cObjetivosDeglucion'], $laDataObj['CODIGO'])){
						$this->nCnsDeglucion = $this->nCnsDeglucion + 1;
						$laTr['aCuerpo'][] = ['texto9', '(' .$this->nCnsDeglucion .'). '.$laDataObj['DESCRIPCION']]; }
					}
				}

				if (!empty(trim($this->aDocumento['cCumplioObjetivosDeglucion']))) {
					$laTr['aCuerpo'][] = ['txthtml9', '¿Cumplió con los objetivos de tratamiento?   '.($this->aDocumento['cCumplioObjetivosDeglucion']=='S'? 'SI': 'NO')]; }

				if (!empty($this->aDocumento['cSugerenciasDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo3', 'SUGERENCIAS'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cSugerenciasDeglucion']]; }

				if (!empty($this->aDocumento['cPronosticoDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'PRONÓSTICO'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cPronosticoDeglucion']]; }

				if (!empty($this->aDocumento['cIntervencionDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'INTERVENCIÓN'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cIntervencionDeglucion']]; }

				if (!empty($this->aDocumento['cRecomendacionesDeglucion'])){
					$laTr['aCuerpo'][] = ['titulo2', 'RECOMENDACIONES'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cRecomendacionesDeglucion']]; }
			}
			$laTr['aCuerpo'][]=['firmas', [
				['usuario' => $this->aDocumento['cUsuarioRealiza'],'prenombre'=>'Fn. '],
			]];
			

			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $this->InsertarNotas($taData, 'A'));
			$laTr['aCuerpo'][] = ['saltol', []];
			
		}


		/**********************************************  VOZ  **********************************************/
		if ($this->lExisteVoz){
			$cEstadoConcienciaVoz = !empty($this->aDocumento['cEstadoConcienciaVoz'])? $this->aParametros['ECN'][$this->aDocumento['cEstadoConcienciaVoz']] :'';
			$lnFechaHoraEvaluacion = AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['cFechaEvaluacionVoz']);
			$cSeestaAsperandoVoz = !empty($this->aDocumento['cSeestaAsperandoVoz'])? $this->aParametros['SON'][$this->aDocumento['cSeestaAsperandoVoz']] :'';
			$cAlimentacionVoz = !empty($this->aDocumento['cAlimentacionVoz'])? $this->aParametros['ALI'][$this->aDocumento['cAlimentacionVoz']] :'';
			$cSialorreaVoz = !empty($this->aDocumento['cSialorreaVoz'])? $this->aParametros['SON'][$this->aDocumento['cSialorreaVoz']] :'';
			$cSealimentaenformaVoz = !empty($this->aDocumento['cSealimentaenformaVoz'])? $this->aParametros['ALF'][$this->aDocumento['cSealimentaenformaVoz']] :'';
			$cTraqueostomiaVoz = !empty($this->aDocumento['cTraqueostomiaVoz'])? $this->aParametros['SON'][$this->aDocumento['cTraqueostomiaVoz']] :'';
			$cCanulaVoz = !empty($this->aDocumento['cCanulaVoz'])? $this->aParametros['SON'][$this->aDocumento['cCanulaVoz']] :'';
			$cVenturyVoz = !empty($this->aDocumento['cVenturyVoz'])? $this->aParametros['SON'][$this->aDocumento['cVenturyVoz']] :'';
			$cVentilacionmecanicaVoz = !empty($this->aDocumento['cVentilacionmecanicaVoz'])? $this->aParametros['SON'][$this->aDocumento['cVentilacionmecanicaVoz']] :'';
			$cIntubacionVoz = !empty($this->aDocumento['cIntubacionVoz'])? $this->aParametros['SON'][$this->aDocumento['cIntubacionVoz']] :'';
			$cTonopercepcionVoz = !empty($this->aDocumento['cTonopercepcionVoz'])? $this->aParametros['TON'][$this->aDocumento['cTonopercepcionVoz']] :'';
			$cTimbreFuentegloticaVoz = !empty($this->aDocumento['cTimbreFuentegloticaVoz'])? $this->aParametros['TIG'][$this->aDocumento['cTimbreFuentegloticaVoz']] :'';
			$cTimbreFiltrosresonVoz = !empty($this->aDocumento['cTimbreFiltrosresonVoz'])? $this->aParametros['TIS'][$this->aDocumento['cTimbreFiltrosresonVoz']] :'';
			$cConfidencialVoz = !empty($this->aDocumento['cConfidencialVoz'])? $this->aParametros['SON'][$this->aDocumento['cConfidencialVoz']] :'';
			$cConversacionalVoz = !empty($this->aDocumento['cConversacionalVoz'])? $this->aParametros['SON'][$this->aDocumento['cConversacionalVoz']] :'';
			$cProyectadaVoz = !empty($this->aDocumento['cProyectadaVoz'])? $this->aParametros['SON'][$this->aDocumento['cProyectadaVoz']] :'';
			$cEsfuerzovocalVoz = !empty($this->aDocumento['cEsfuerzovocalVoz'])? $this->aParametros['SON'][$this->aDocumento['cEsfuerzovocalVoz']] :'';
			$cIngurgitacionvenosaVoz = !empty($this->aDocumento['cIngurgitacionvenosaVoz'])? $this->aParametros['SON'][$this->aDocumento['cIngurgitacionvenosaVoz']] :'';
			$cVozdellamadaVoz = !empty($this->aDocumento['cVozdellamadaVoz'])? $this->aParametros['SON'][$this->aDocumento['cVozdellamadaVoz']] :'';
			$cADerecha = !empty($this->aDocumento['cADerecha'])? $this->aParametros['SIG'][$this->aDocumento['cADerecha']] :'';
			$cAIzquierda = !empty($this->aDocumento['cAIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cAIzquierda']] :'';
			$cEDerecha = !empty($this->aDocumento['cEDerecha'])? $this->aParametros['SIG'][$this->aDocumento['cEDerecha']] :'';
			$cEIzquierda = !empty($this->aDocumento['cEIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cEIzquierda']] :'';
			$cIDerecha = !empty($this->aDocumento['cIDerecha'])? $this->aParametros['SIG'][$this->aDocumento['cIDerecha']] :'';
			$cIIzquierda = !empty($this->aDocumento['cIIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cIIzquierda']] :'';
			$cODerecha = !empty($this->aDocumento['cODerecha'])? $this->aParametros['SIG'][$this->aDocumento['cODerecha']] :'';
			$cOIzquierda = !empty($this->aDocumento['cOIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cOIzquierda']] :'';
			$cUDerecha = !empty($this->aDocumento['cUDerecha'])? $this->aParametros['SIG'][$this->aDocumento['cUDerecha']] :'';
			$cUIzquierda = !empty($this->aDocumento['cUIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cUIzquierda']] :'';
			$cMDerecha = !empty($this->aDocumento['cMDerecha'])? $this->aParametros['SIG'][$this->aDocumento['cMDerecha']] :'';
			$cMIzquierda = !empty($this->aDocumento['cMIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cMIzquierda']] :'';
			$cNDerecha = !empty($this->aDocumento['cNDerecha'])? $this->aParametros['SIG'][$this->aDocumento['cNDerecha']] :'';
			$cNIzquierda = !empty($this->aDocumento['cNIzquierda'])? $this->aParametros['SIG'][$this->aDocumento['cNIzquierda']] :'';
			$cAtaqueVoz = !empty($this->aDocumento['cAtaqueVoz'])? $this->aParametros['ATA'][$this->aDocumento['cAtaqueVoz']] :'';
			$cCuerpoVoz = !empty($this->aDocumento['cCuerpoVoz'])? $this->aParametros['CUE'][$this->aDocumento['cCuerpoVoz']] :'';
			$cFilaturaVoz = !empty($this->aDocumento['cFilaturaVoz'])? $this->aParametros['FIL'][$this->aDocumento['cFilaturaVoz']] :'';
			$cIncordRespiratoriaVoz = !empty($this->aDocumento['cIncordRespiratoriaVoz'])? $this->aParametros['SON'][$this->aDocumento['cIncordRespiratoriaVoz']] :'';
			$cSealteraTonoVoz = !empty($this->aDocumento['cSealteraTonoVoz'])? $this->aParametros['SON'][$this->aDocumento['cSealteraTonoVoz']] :'';
			$cHayesfuerzovocalVoz = !empty($this->aDocumento['cHayesfuerzovocalVoz'])? $this->aParametros['SON'][$this->aDocumento['cHayesfuerzovocalVoz']] :'';
			$cImprecisionarticularVoz = !empty($this->aDocumento['cImprecisionarticularVoz'])? $this->aParametros['SON'][$this->aDocumento['cImprecisionarticularVoz']] :'';
			$cRespiratorioTipoVoz = !empty($this->aDocumento['cRespiratorioTipoVoz'])? $this->aParametros['ERT'][$this->aDocumento['cRespiratorioTipoVoz']] :'';
			$cRespiratorioReposoVoz = !empty($this->aDocumento['cRespiratorioReposoVoz'])? $this->aParametros['MDR'][$this->aDocumento['cRespiratorioReposoVoz']] :'';
			$cRespiratorioFonacionVoz = !empty($this->aDocumento['cRespiratorioFonacionVoz'])? $this->aParametros['MDR'][$this->aDocumento['cRespiratorioFonacionVoz']] :'';
			$cImpresionDiagnosticaVoz = !empty($this->aDocumento['cImpresionDiagnosticaVoz'])? $this->aParImpresion['B'][$this->aDocumento['cImpresionDiagnosticaVoz']] :'';
			$laTr['aCuerpo'][] = ['titulo1', AplicacionFunciones::mb_str_pad(' VALORACIÓN FONOAUDIOLOGÍA VOZ ', 90, "-", STR_PAD_BOTH)];

			if (!empty($cEstadoConcienciaVoz)){
				$laTr['aCuerpo'][] = ['texto9',	'Fecha evaluación  : ' .$lnFechaHoraEvaluacion .'    Estado conciencia : ' .$cEstadoConcienciaVoz]; }
			else{ $laTr['aCuerpo'][] = ['texto9', 'Fecha evaluación  : ' .$lnFechaHoraEvaluacion]; }

			if (!empty($this->aDocumento['cTextoPandemiaVoz'])){
				$laTr['aCuerpo'][] = ['titulo2', ' '];
				$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cTextoPandemiaVoz']];
			}

			if (!empty($this->cCodDx) || !empty($this->aDocumento['cDiagnosticoVoz'])) {
				$laTr['aCuerpo'][] = ['titulo1', 'DATOS GENERALES'];
				$laTr['aCuerpo'][] = ['titulo2', 'DIAGNÓSTICO MÉDICO'];
				if (!empty($this->cCodDx))
					$laTr['aCuerpo'][] = ['texto9', $this->cCodDx];
				if (!empty($this->aDocumento['cDiagnosticoVoz']))
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cDiagnosticoVoz']];
			}

			if (!empty($cSeestaAsperandoVoz) || !empty($cAlimentacionVoz) || !empty($cSialorreaVoz) ||
				!empty($cSealimentaenformaVoz) || !empty($cTraqueostomiaVoz) || !empty($cCanulaVoz) ||
				!empty($cVenturyVoz) || !empty($cVentilacionmecanicaVoz) ||
				!empty($cIntubacionVoz) || !empty($this->aDocumento['cTiempointubacionVoz']))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'ANTECEDENTES GENERALES'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cSeestaAsperandoVoz)?    AplicacionFunciones::mb_str_pad('Se esta aspirando    : '. $cSeestaAsperandoVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cAlimentacionVoz)? 'Alimentación           : '. $cAlimentacionVoz :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cSialorreaVoz)?    AplicacionFunciones::mb_str_pad('Sialorrea            : '. $cSialorreaVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cSealimentaenformaVoz)? 'Se alimenta en forma   : '. $cSealimentaenformaVoz :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cTraqueostomiaVoz)?    AplicacionFunciones::mb_str_pad('Traqueostomía        : '. $cTraqueostomiaVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cCanulaVoz)? 'Canula                 : '. $cCanulaVoz :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cVenturyVoz)?    AplicacionFunciones::mb_str_pad('Ventury              : '. $cVenturyVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cVentilacionmecanicaVoz)? 'Ventilacion mecanica   : '. $cVentilacionmecanicaVoz :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cIntubacionVoz)?    str_pad('Intubación           : '. $cIntubacionVoz, 48, ' ') :'' ;
				$lcValorTexto .= !empty($this->aDocumento['cTiempointubacionVoz'])? 'Tiempo intubacion      : '. $this->aDocumento['cTiempointubacionVoz'] :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto];}
			}

			if (!empty($cTonopercepcionVoz) || !empty($cTimbreFuentegloticaVoz) || !empty($cTimbreFiltrosresonVoz))
			{
				$laTr['aCuerpo'][] = ['titulo1', 'VALORACION'];
				$laTr['aCuerpo'][] = ['titulo2', 'EXPLORACIÓN COMPORTAMIENTO VOCAL'];

				if (!empty($cTonopercepcionVoz)){
					$laTr['aCuerpo'][] = ['texto9',	'Tono percepción              : '. $cTonopercepcionVoz]; }

				if (!empty($cTimbreFuentegloticaVoz)){
					$laTr['aCuerpo'][] = ['texto9',	'Timbre: Fuente glótica       : '. $cTimbreFuentegloticaVoz]; }

				if (!empty($cTimbreFiltrosresonVoz)){
					$laTr['aCuerpo'][] = ['texto9',	'Timbre: Filtros - Resonancia : '. $cTimbreFiltrosresonVoz]; }
			}

			if (!empty($cConfidencialVoz) || !empty($cConversacionalVoz) || !empty($cProyectadaVoz) ||
				!empty($cEsfuerzovocalVoz) || !empty($cIngurgitacionvenosaVoz) || !empty($cVozdellamadaVoz))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'INTENSIDAD'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty(trim($cConfidencialVoz))?    AplicacionFunciones::mb_str_pad('Confidencial         : '. $cConfidencialVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty(trim($cConversacionalVoz))? 'Conversacional         : '. $cConversacionalVoz :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty(trim($cProyectadaVoz))?    AplicacionFunciones::mb_str_pad('Proyectada           : '. $cProyectadaVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty(trim($cEsfuerzovocalVoz))? 'Esfuerzo vocal         : '. $cEsfuerzovocalVoz :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty(trim($cIngurgitacionvenosaVoz))?    AplicacionFunciones::mb_str_pad('Ingurgitación venosa : '. $cIngurgitacionvenosaVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty(trim($cVozdellamadaVoz))? 'Voz de llamada         : '. $cVozdellamadaVoz :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($cADerecha) || !empty($cAIzquierda) || !empty($cEDerecha) || !empty($cEIzquierda) ||
				!empty($cIDerecha) || !empty($cIIzquierda) || !empty($cODerecha) || !empty($cOIzquierda) ||
				!empty($cUDerecha) || !empty($cUIzquierda) || !empty($cMDerecha) || !empty($cMIzquierda) ||
				!empty($cNDerecha) || !empty($cNIzquierda))
			{
				$lcValorTexto='' ;
				$lcValorTexto .= 'Derecha                   ' .str_pad($cADerecha, 6) .str_pad($cEDerecha, 6) .str_pad($cIDerecha, 6) .str_pad($cODerecha, 6) .str_pad($cUDerecha, 6) .str_pad($cMDerecha, 6) .str_pad($cNDerecha, 6) .$lcSL;
				$lcValorTexto .= 'Izquierda                 ' .str_pad($cAIzquierda, 6) .str_pad($cEIzquierda, 6) .str_pad($cIIzquierda, 6) .str_pad($cOIzquierda, 6) .str_pad($cUIzquierda, 6) .str_pad($cMIzquierda, 6) .str_pad($cNIzquierda, 6);
				$laTr['aCuerpo'][] = ['titulo2', 'FUNCION VOCAL / RESPIRATORIO'];
				$laTr['aCuerpo'][] = ['titulo3', 'RESONANCIA (Prueba de Glatzel)'];
				$laTr['aCuerpo'][] = ['texto9',	'NARINA                   /a/   /e/   /i/   /o/   /u/   /m/   /n/  '];
				$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto];
			}

			if (!empty($cAtaqueVoz) || !empty($cCuerpoVoz) || !empty($cFilaturaVoz))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'TIEMPO FÍSICO DE FONACIÓN'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cAtaqueVoz)?    AplicacionFunciones::mb_str_pad('Ataque               : '. $cAtaqueVoz, 48, ' ') :'' ;
				$lcValorTexto .= !empty($cCuerpoVoz)? 'Cuerpo                 : '. $cCuerpoVoz :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cFilaturaVoz)?    AplicacionFunciones::mb_str_pad('Filatura             : '. $cFilaturaVoz, 48, ' ') :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			 }

			if (!empty($this->aDocumento['cAMaximo1']) || !empty($this->aDocumento['cAMaximo2']) ||
				!empty($this->aDocumento['cAMaximo3']) || !empty($this->aDocumento['cAMaximo4']) ||
				!empty($this->aDocumento['cSCompetencia1']) || !empty($this->aDocumento['cSCompetencia2']) ||
				!empty($this->aDocumento['cSCompetencia3']) || !empty($this->aDocumento['cSCompetencia4']) ||
				!empty($this->aDocumento['cZMaximo1']) || !empty($this->aDocumento['cZMaximo2']) ||
				!empty($this->aDocumento['cZMaximo3']) || !empty($this->aDocumento['cZMaximo4']))
			{
				$lcValorTexto='' ;
				$lcValorTexto .= '/a/  '
								.str_pad($this->aDocumento['cAMaximo1'], 1) .' + '
								.str_pad($this->aDocumento['cAMaximo2'], 1) .' + '
								.str_pad($this->aDocumento['cAMaximo3'], 1) .' = '
								.str_pad($this->aDocumento['cAMaximo4'], 31);

				$lcValorTexto .= '/s/  '
								.str_pad($this->aDocumento['cSCompetencia1'], 1) .' + '
								.str_pad($this->aDocumento['cSCompetencia2'], 1) .' + '
								.str_pad($this->aDocumento['cSCompetencia3'], 1) .' = '
								.str_pad($this->aDocumento['cSCompetencia4'], 1)
								.$lcSL;

				$lcValorTexto .= '/z/  '
								.str_pad($this->aDocumento['cZMaximo1'], 1) .' + '
								.str_pad($this->aDocumento['cZMaximo2'], 1) .' + '
								.str_pad($this->aDocumento['cZMaximo3'], 1) .' = '
								.str_pad($this->aDocumento['cZMaximo4'], 1)
								;

				$laTr['aCuerpo'][] = ['titulo2', AplicacionFunciones::mb_str_pad('TIEMPO MÁXIMO DE FONACIÓN', 45, ' ') .AplicacionFunciones::mb_str_pad('COMPETENCIA GLÓTICA', 45, ' ')];
				$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto];
			}

			if (!empty($cIncordRespiratoriaVoz) || !empty($cSealteraTonoVoz) ||
				!empty($cHayesfuerzovocalVoz) || !empty($cImprecisionarticularVoz))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'RESISTENCIA'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cIncordRespiratoriaVoz)? AplicacionFunciones::mb_str_pad('Incoordinación respiratoria  : '. $cIncordRespiratoriaVoz, 47, ' ') : '' ;
				$lcValorTexto .= !empty($cSealteraTonoVoz)? 'Se altera tono e intensidad    : '. $cSealteraTonoVoz : '' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cHayesfuerzovocalVoz)? AplicacionFunciones::mb_str_pad('Hay esfuerzo vocal         	 : '. $cHayesfuerzovocalVoz, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cImprecisionarticularVoz)? 'Imprecisión articulatoria      : '. $cImprecisionarticularVoz :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($cRespiratorioTipoVoz) || !empty($cRespiratorioReposoVoz) || !empty($cRespiratorioFonacionVoz))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'EXAMEN RESPIRATORIO'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cRespiratorioTipoVoz)?    AplicacionFunciones::mb_str_pad('Tipo examen                  : '. $cRespiratorioTipoVoz, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cRespiratorioReposoVoz)?    AplicacionFunciones::mb_str_pad('Modo respiratorio: En reposo : '. $cRespiratorioReposoVoz, 50, ' ') :'' ;
				$lcValorTexto .= !empty($cRespiratorioFonacionVoz)?  AplicacionFunciones::mb_str_pad('Modo respiratorio: En fonación : '. $cRespiratorioFonacionVoz, 50, ' ') :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($cImpresionDiagnosticaVoz) || !empty($this->aDocumento['cPronosticoVoz']) || !empty($this->aDocumento['cObjetivosVoz']))
			{
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO E INTERVENCIÓN'];

				if (!empty(trim($cImpresionDiagnosticaVoz))){
					$laTr['aCuerpo'][] = ['titulo2', 'IMPRESIÓN DIAGNÓSTICA'];
					$laTr['aCuerpo'][] = ['texto9',$cImpresionDiagnosticaVoz];
				}

				if (!empty($this->aDocumento['cObjetivosVoz']) || !empty($this->aDocumento['cCumplioObjetivosVoz']) || !empty($this->aDocumento['cObjetivosSugerenciasVoz'])){
					$laTr['aCuerpo'][] = ['titulo2', 'OBJETIVOS'];

					foreach($this->aListaObjetivos as $laDataObj) {
						if ($laDataObj['LITERAL']='B' && strstr($this->aDocumento['cObjetivosVoz'], $laDataObj['CODIGO'])){
							$this->nCnsVoz = $this->nCnsVoz + 1;
							$laTr['aCuerpo'][] = ['texto9', '(' .$this->nCnsVoz .'). '.$laDataObj['DESCRIPCION']];
						}
					}

					if (!empty($this->aDocumento['cCumplioObjetivosVoz']))
						$laTr['aCuerpo'][] = ['texto9', '¿Cumplió con los objetivos de tratamiento?   '.($this->aDocumento['cCumplioObjetivosVoz']=='S'? 'SI': 'NO')];

					if (!empty($this->aDocumento['cObjetivosSugerenciasVoz'])){
						$laTr['aCuerpo'][] = ['titulo5', 'SUGERENCIAS'];
						$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cObjetivosSugerenciasVoz']];
					}

				}

				if (!empty($this->aDocumento['cPronosticoVoz'])){
					$laTr['aCuerpo'][] = ['titulo2', 'PRONÓSTICO'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cPronosticoVoz']];
				}

				if (!empty($this->aDocumento['cIntervencionVoz'])){
					$laTr['aCuerpo'][] = ['titulo2', 'INTERVENCIÓN'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cIntervencionVoz']];
				}

				if (!empty($this->aDocumento['cRecomendacionesVoz'])){
					$laTr['aCuerpo'][] = ['titulo2', 'RECOMENDACIONES'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cRecomendacionesVoz']];
				}
			}

			$laTr['aCuerpo'][]=['firmas', [
				['usuario' => $this->aDocumento['cUsuarioRealiza'],'prenombre'=>'Fn. '],
			]];
			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $this->InsertarNotas($taData, 'B'));
			$laTr['aCuerpo'][] = ['saltol', []];
		}

		/********************************************  LENGUAJE  *******************************************/
		if ($this->lExisteLenguaje){
			$this->nCnsVoz = 0;
			$cEstadoConciencia = !empty($this->aDocumento['cEstadoConcienciaLenguaje'])? $this->aParametros['ECN'][$this->aDocumento['cEstadoConcienciaLenguaje']] :'';
			$lnFechaHoraEvaluacion = AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['cFechaEvaluacionLenguaje']);
			$cTrastornoLenguaje = !empty($this->aDocumento['cTrastornoLenguaje'])? $this->aParametros['SON'][$this->aDocumento['cTrastornoLenguaje']] :'';
			$cTipoTrastornoLenguaje = !empty($this->aDocumento['cTipoTrastornoLenguaje'])? $this->aParametros['AFA'][$this->aDocumento['cTipoTrastornoLenguaje']] :'';
			$cApraxiaOralLenguaje = !empty($this->aDocumento['cApraxiaOralLenguaje'])? $this->aParametros['LMS'][$this->aDocumento['cApraxiaOralLenguaje']] :'';
			$cImpresionDiagnosticaLenguaje = !empty($this->aDocumento['cImpresionDiagnosticaLenguaje'])? $this->aParImpresion['C'][$this->aDocumento['cImpresionDiagnosticaLenguaje']] :'';
			$laTr['aCuerpo'][] = ['titulo1', AplicacionFunciones::mb_str_pad(' VALORACIÓN FONOAUDIOLOGÍA LENGUAJE ', 90, "-", STR_PAD_BOTH)];

			if (!empty(trim($cEstadoConciencia))){
				$laTr['aCuerpo'][] = ['texto9',	'Fecha evaluación  : ' .$lnFechaHoraEvaluacion .'    Estado conciencia : ' .$cEstadoConciencia];
			}else{
				$laTr['aCuerpo'][] = ['texto9',	'Fecha evaluación  : ' .$lnFechaHoraEvaluacion];
			}

			if (!empty($this->aDocumento['cTextoPandemiaLenguaje'])){
				$laTr['aCuerpo'][] = ['titulo2', ' '];
				$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cTextoPandemiaLenguaje']];
			}

			if (!empty($this->cCodDx) || !empty($this->aDocumento['cDiagnosticoLenguaje'])){
				$laTr['aCuerpo'][] = ['titulo1', 'DATOS GENERALES'];
				$laTr['aCuerpo'][] = ['titulo2', 'DIAGNÓSTICO MÉDICO'];
				if (!empty($this->cCodDx))
					$laTr['aCuerpo'][] = ['texto9',$this->cCodDx];
				if (!empty($this->cCodDx))
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cDiagnosticoLenguaje']];
			}

			if (!empty($cTrastornoLenguaje) || !empty($cTipoTrastornoLenguaje) || !empty($cApraxiaOralLenguaje)) {
				$laTr['aCuerpo'][] = ['titulo2', 'ANTECEDENTES GENERALES'];

				$lcValorTexto='' ;
				$lcValorTexto .= !empty($cTrastornoLenguaje)? AplicacionFunciones::mb_str_pad('Trastorno lenguaje   : '. $cTrastornoLenguaje, 47, ' ') :'' ;
				$lcValorTexto .= !empty($cTipoTrastornoLenguaje)? 'Tipo trastorno       : '. $cTipoTrastornoLenguaje :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto];
				}

				$lcValorTexto='' ;
				$lcValorTexto .= !empty(trim($cApraxiaOralLenguaje))? AplicacionFunciones::mb_str_pad('Apraxia Oral         : '. $cApraxiaOralLenguaje, 47, ' ') :'' ;
				if (!empty($lcValorTexto)){
					$laTr['aCuerpo'][] = ['texto9',	$lcValorTexto];
				}
			}

			if (!empty($this->aDocumento['aLenguajeConversacional'])){
				$laClaveDetalleLenguaje= array_keys($this->aDocumento['aLenguajeConversacional']);

				$laLengConvers = [
					1=>['t'=>'LENGUAJE CONVERSACIONAL',	'o'=>'cObsLenguajeConversacion'],
					2=>['t'=>'COMPRENSIÓN AUDITIVA',	'o'=>'cObsLenguajeComprension'],
					3=>['t'=>'EXPRESIÓN ORAL',			'o'=>'cObsLenguajeExpesionOral'],
					4=>['t'=>'VALORACIÓN DE LECTURA',	'o'=>'cObsLenguajeValoracionLectura'],
					5=>['t'=>'VALORACIÓN DE ESCRITURA',	'o'=>'cObsLenguajeValoracionLectura'],
					6=>['t'=>'SIGNOS LINGUÍSTICOS',		'o'=>'cObsLenguajeSignosLinguisticos'],
				];

				foreach($laLengConvers as $lnKey=>$laLengConv){
					$lbTitulo = true;
					$lnLiteralActual = 0;
					$aContadorLenguaje = 0;
					$aContadorSubtitulo = 0;
					$lcCLenguaje=$lcSeparador='';
					foreach($this->aLenguajeConversacional as $laDataControlLenguaje) {
						if ($laDataControlLenguaje['CODIGO_GRUPO']==$lnKey && in_array($laDataControlLenguaje['ID'],$laClaveDetalleLenguaje)) {
							if ($lbTitulo){
								$laTr['aCuerpo'][] = ['titulo1', $laLengConv['t']];
								$lbTitulo = false;
							}

							if ($laDataControlLenguaje['LITERAL'] !== $lnLiteralActual) {
								$lbImprimioGrupo = false;
								if (!empty($lcCLenguaje)) $laTr['aCuerpo'][] = ['texto9', $lcCLenguaje];
								$lcCLenguaje=$lcSeparador='';
								$lnLiteralActual = $laDataControlLenguaje['LITERAL'];
								$aContadorLenguaje = 0;
							}

							$aContadorLenguaje = $aContadorLenguaje + 1;
							if (!$lbImprimioGrupo) {
								$aContadorSubtitulo = $aContadorSubtitulo + 1;
								$laTr['aCuerpo'][] = ['titulo3','('.$aContadorSubtitulo .'). ' .$laDataControlLenguaje['DESCRIPCION_GRUPO']];
								$lbImprimioGrupo=true;
							}

							if ($lnKey!==3 || ($lnKey==3 && $aContadorSubtitulo<7)){
								$lcValorLenguaje=$this->aVrDeglucion[$this->aDocumento['aLenguajeConversacional'][$laDataControlLenguaje['ID']]];
								$lcCLenguaje.=AplicacionFunciones::mb_str_pad($aContadorLenguaje .'. '.trim($laDataControlLenguaje['DESCRIPCION']), 42, ' ').': '.$lcValorLenguaje .'     '.$lcSeparador;
								$lcSeparador=$lcSeparador==''? $lcSL : '';
							} else {
								$lcValorLenguaje=trim(substr($this->aDocumento['aLenguajeConversacional'][$laDataControlLenguaje['ID']], 1, 42));
								$lcCLenguaje.=$aContadorLenguaje .'. '.trim($laDataControlLenguaje['DESCRIPCION']).': '.$lcValorLenguaje .'   '.$lcSL;
							}
						}
					}
					if (!empty($lcCLenguaje)) $laTr['aCuerpo'][] = ['texto9', $lcCLenguaje];

					if (!empty($this->aDocumento[$laLengConv['o']])){
						$laTr['aCuerpo'][] = ['titulo4', 'OBSERVACIONES'];
						$laTr['aCuerpo'][] = ['texto9',$this->aDocumento[$laLengConv['o']]];
					}
				}
			}

			if (!empty(trim($cImpresionDiagnosticaLenguaje)) || !empty($this->aDocumento['cObjetivosLenguaje'])
				|| !empty($this->aDocumento['cSugerenciasLenguaje']) || !empty($this->aDocumento['cPronosticoLenguaje'])
				|| !empty($this->aDocumento['cIntervencionLenguaje']) || !empty($this->aDocumento['cRecomendacionesLenguaje'])
			){
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO E INTERVENCIÓN'];

				if (!empty(trim($cImpresionDiagnosticaLenguaje))){
					$laTr['aCuerpo'][] = ['titulo2', 'IMPRESIÓN DIAGNOSTICA'];
					$laTr['aCuerpo'][] = ['texto9',$cImpresionDiagnosticaLenguaje];
				}

				if (!empty($this->aDocumento['cObjetivosLenguaje']) || !empty($this->aDocumento['cCumplioObjetivosLenguaje'])){
					$laTr['aCuerpo'][] = ['titulo2', 'OBJETIVOS'];

					foreach($this->aListaObjetivos as $laDataObj) {
						if ($laDataObj['LITERAL']='C' && strstr($this->aDocumento['cObjetivosLenguaje'], $laDataObj['CODIGO'])){
							$this->nCnsVoz = $this->nCnsVoz + 1;
							$laTr['aCuerpo'][] = ['texto9', '(' .$this->nCnsVoz .'). '.$laDataObj['DESCRIPCION']];
						}
					}

					if (!empty(trim($this->aDocumento['cCumplioObjetivosLenguaje']))) {
						$laTr['aCuerpo'][] = ['txthtml9', '¿Cumplió con los objetivos de tratamiento?   '.($this->aDocumento['cCumplioObjetivosLenguaje']=='S'? 'SI': 'NO')];
					}

					if (!empty($this->aDocumento['cSugerenciasLenguaje'])){
						$laTr['aCuerpo'][] = ['titulo4', 'SUGERENCIAS'];
						$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cSugerenciasLenguaje']];
					}
				}

				if (!empty($this->aDocumento['cPronosticoLenguaje'])){
					$laTr['aCuerpo'][] = ['titulo2', 'PRONÓSTICO'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cPronosticoLenguaje']];
				}

				if (!empty($this->aDocumento['cIntervencionLenguaje'])){
					$laTr['aCuerpo'][] = ['titulo2', 'INTERVENCIÓN'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cIntervencionLenguaje']];
				}

				if (!empty($this->aDocumento['cRecomendacionesLenguaje'])){
					$laTr['aCuerpo'][] = ['titulo2', 'RECOMENDACIONES'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cRecomendacionesLenguaje']];
				}
			}
			$laTr['aCuerpo'][]=['firmas', [
				['usuario' => $this->aDocumento['cUsuarioRealiza'],'prenombre'=>'Fn. '],
			]];
			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $this->InsertarNotas($taData, 'C'));
			$laTr['aCuerpo'][] = ['saltol', []];
		}

		/*********************************************  HABLA  *********************************************/
		if ($this->lExisteHabla){
			$this->nCnsDeglucion = 0;
 			$cEstadoConciencia = !empty($this->aDocumento['cEstadoConcienciaHabla'])? $this->aParametros['ECN'][$this->aDocumento['cEstadoConcienciaHabla']] :'';
			$lnFechaHoraEvaluacion = AplicacionFunciones::formatFechaHora('fechahora', $this->aDocumento['cFechaEvaluacionHabla']);
			$cTrastornoHabla = !empty($this->aDocumento['cTrastornoHabla'])? $this->aParametros['SON'][$this->aDocumento['cTrastornoHabla']] :'';
			$cTipoTrastornoHabla = !empty($this->aDocumento['cTipoTrastornoHabla'])? $this->aParametros['DAH'][$this->aDocumento['cTipoTrastornoHabla']] :'';
			$cDiadococinesis = !empty($this->aDocumento['cDiadococinesis'])? $this->aParametros['DIA'][$this->aDocumento['cDiadococinesis']] :'';
			$cIntegibilidad = !empty($this->aDocumento['cIntegibilidad'])? $this->aParametros['ING'][$this->aDocumento['cIntegibilidad']] :'';
			$cImpresionDiagnosticaHabla = !empty($this->aDocumento['cImpresionDiagnosticaHabla'])? $this->aParImpresion['D'][$this->aDocumento['cImpresionDiagnosticaHabla']] :'';
			$laTr['aCuerpo'][] = ['titulo1',	AplicacionFunciones::mb_str_pad(' VALORACIÓN FONOAUDIOLOGÍA HABLA ', 90, "-", STR_PAD_BOTH)];

			if (!empty(trim($cEstadoConciencia))){
				$laTr['aCuerpo'][] = ['texto9',	'Fecha evaluación  : ' .$lnFechaHoraEvaluacion .'    Estado conciencia : ' .$cEstadoConciencia];
			}
			else{ $laTr['aCuerpo'][] = ['texto9', 'Fecha evaluación  : ' .$lnFechaHoraEvaluacion]; }

			if (!empty($this->aDocumento['cTextoPandemiaHabla'])){
				$laTr['aCuerpo'][] = ['titulo2', ' '];
				$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cTextoPandemiaHabla']];
			}

			if (!empty($this->cCodDx) || !empty($this->aDocumento['cDiagnosticoHabla'])){
				$laTr['aCuerpo'][] = ['titulo1', 'DATOS GENERALES'];
				$laTr['aCuerpo'][] = ['titulo2', 'DIAGNÓSTICO MÉDICO'];
				if (!empty($this->cCodDx))
					$laTr['aCuerpo'][] = ['texto9', $this->cCodDx];
				if (!empty($this->aDocumento['cDiagnosticoHabla']))
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cDiagnosticoHabla']];
			}

			if (!empty(trim($cTrastornoHabla)) || !empty(trim($cTipoTrastornoHabla))) {
				$laTr['aCuerpo'][] = ['titulo2', 'ANTECEDENTES GENERALES'];
				$lcValorTexto='' ;
				$lcValorTexto .= !empty(trim($cTrastornoHabla))?    AplicacionFunciones::mb_str_pad('Trastorno del habla   : '. $cTrastornoHabla, 47, ' ') :'' ;
				$lcValorTexto .= !empty(trim($cTipoTrastornoHabla))? 'Tipo trastorno      : '. $cTipoTrastornoHabla :'' ;
				if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
			}

			if (!empty($this->aDocumento['cValoraLabioDentalesHabla']) || !empty($this->aDocumento['cValoraLinguoDentalesHabla']) ||
				!empty($this->aDocumento['cValoraLinguAlveolaresHabla']) || !empty($this->aDocumento['cValoraPalatalesHabla']) ||
				!empty($this->aDocumento['cValoraVelaresHabla']) || !empty($this->aDocumento['cValoraVibraSimpleHabla']) ||
				!empty($this->aDocumento['cValoraVibraMultiHabla']) || !empty($this->aDocumento['cValoraBilabialesHabla']) ||
				!empty($this->aDocumento['cValoraObservacionHabla']) || !empty(trim($cDiadococinesis)) || !empty(trim($cIntegibilidad)))
			{
				$laTr['aCuerpo'][] = ['titulo2', 'VALORACIÓN'];

				if (!empty($this->aDocumento['cValoraLabioDentalesHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'LABIODENTALES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraLabioDentalesHabla']];
				}
				if (!empty($this->aDocumento['cValoraLinguoDentalesHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'LINGUODENTALES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraLinguoDentalesHabla']];
				}
				if (!empty($this->aDocumento['cValoraLinguAlveolaresHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'LINGUALVEOLARES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraLinguAlveolaresHabla']];
				}
				if (!empty($this->aDocumento['cValoraPalatalesHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'PALATALES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraPalatalesHabla']];
				}
				if (!empty($this->aDocumento['cValoraVelaresHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'VELARES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraVelaresHabla']];
				}
				if (!empty($this->aDocumento['cValoraVibraSimpleHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'VIBRANTE SIMPLE'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraVibraSimpleHabla']];
				}
				if (!empty($this->aDocumento['cValoraVibraMultiHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'VIBRANTE MÚLTIPLE'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraVibraMultiHabla']];
				}
				if (!empty($this->aDocumento['cValoraBilabialesHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'BILABIALES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraBilabialesHabla']];
				}
				if (!empty(trim($cDiadococinesis)) || !empty(trim($cIntegibilidad))){
					$laTr['aCuerpo'][] = ['titulo3', 'FLUIDEZ'];
					$lcValorTexto='' ;
					$lcValorTexto .= !empty(trim($cDiadococinesis))? AplicacionFunciones::mb_str_pad('Diadococinesis :  '. $cDiadococinesis, 50, ' ') :'' ;
					$lcValorTexto .= !empty(trim($cIntegibilidad))?  AplicacionFunciones::mb_str_pad('% de Integibilidad :  '  . $cIntegibilidad, 50, ' ') :'' ;
					if (!empty($lcValorTexto)){ $laTr['aCuerpo'][] = ['texto9',	$lcValorTexto]; }
				}
				if (!empty($this->aDocumento['cValoraObservacionHabla'])){
					$laTr['aCuerpo'][] = ['titulo3', 'OBSERVACIONES'];
					$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cValoraObservacionHabla']];
				}
			}

			if (!empty(trim($cImpresionDiagnosticaHabla)) || !empty($this->aDocumento['cObjetivosHabla'])
				|| !empty($this->aDocumento['cSugerenciasHabla']) || !empty($this->aDocumento['cPronosticoHabla'])
				|| !empty($this->aDocumento['cIntervencionHabla']) || !empty($this->aDocumento['cRecomendacionesHabla']))
			{
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO E INTERVENCIÓN'];

				if (!empty(trim($cImpresionDiagnosticaHabla))){
					$laTr['aCuerpo'][] = ['titulo2', 'IMPRESIÓN DIAGNÓSTICA'];
					$laTr['aCuerpo'][] = ['texto9',$cImpresionDiagnosticaHabla]; }

				if (!empty($this->aDocumento['cObjetivosHabla'])){
					$laTr['aCuerpo'][] = ['titulo2', 'OBJETIVOS'];

					foreach($this->aListaObjetivos as $laDataObj) {
						if ($laDataObj['LITERAL']='D' && strstr($this->aDocumento['cObjetivosHabla'], $laDataObj['CODIGO'])){
							$this->nCnsDeglucion = $this->nCnsDeglucion + 1;
							$laTr['aCuerpo'][] = ['texto9', '(' .$this->nCnsDeglucion .'). '.$laDataObj['DESCRIPCION']]; }
					}
				}

				if (!empty(trim($this->aDocumento['cCumplioObjetivosHabla'])))
					$laTr['aCuerpo'][] = ['txthtml9', '¿Cumplió con los objetivos de tratamiento?   '.($this->aDocumento['cCumplioObjetivosHabla']=='S'? 'SI': 'NO')];

				if (!empty($this->aDocumento['cSugerenciasHabla'])) {
					$laTr['aCuerpo'][] = ['titulo4', 'SUGERENCIAS'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cSugerenciasHabla']];
				}

				if (!empty($this->aDocumento['cPronosticoHabla'])) {
					$laTr['aCuerpo'][] = ['titulo2', 'PRONÓSTICO'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cPronosticoHabla']];
				}

				if (!empty($this->aDocumento['cIntervencionHabla'])) {
					$laTr['aCuerpo'][] = ['titulo2', 'INTERVENCIÓN'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cIntervencionHabla']];
				}

				if (!empty($this->aDocumento['cRecomendacionesHabla'])) {
					$laTr['aCuerpo'][] = ['titulo2', 'RECOMENDACIONES'];
					$laTr['aCuerpo'][] = ['texto9',$this->aDocumento['cRecomendacionesHabla']];
				}
			}

			$laTr['aCuerpo'][]=['firmas', [
				['usuario' => $this->aDocumento['cUsuarioRealiza'],'prenombre'=>'Fn. '],
			]];
			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $this->InsertarNotas($taData, 'D'));
		}
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function InsertarNotas($taData, $tcLetra)
	{
		$laForm = 'FIS012'.$tcLetra;
		$laNotas = (new Doc_NotasAclaratorias())->notasAclaratoriasLibro($taData['nIngreso'], $taData['cCUP'], $taData['nConsecCita'], $laForm, 'ASC', false);
		return $laNotas;
	}
	
	private function datosBlanco()
	{
		return [
			'cFechaEvaluacion'=> '',
			'cEstadoConciencia'=> '',
			'cDiagnosticoDeglucion'=>'',
			'cImpresionDiagnostica'=> '',
			'cIntervencionDeglucion'=> '',
			'cRecomendacionesDeglucion'=> '',
			'cSeestaAsperandoDeglucion'=> '',
			'cAlimentacionDeglucion'=> '',
			'cSialorreaDeglucion'=> '',
			'cSealimentaenformaDeglucion'=> '',
			'cTraqueostomiaDeglucion'=> '',
			'cCanulaDeglucion'=> '',
			'cVenturyDeglucion'=> '',
			'cVentilacionmecanicaDeglucion'=> '',
			'cIntubacionDeglucion'=> '',
			'cTiempointubacionDeglucion'=> '',
			'cEstadoDeglucion'=> '',
			'cDenticionDeglucion'=> '',
			'cCaraenreposoDeglucion'=> '',
			'cCaraensonrisaDeglucion'=> '',
			'cTosDeglucion'=> '',
			'cNauseasDeglucion'=> '',
			'cCierregloticoDeglucion'=> '',
			'cFrecuenciaDeglucion'=> '',
			'cObservaValoracionDeglucion'=> '',
			'cObservaCtrMotorDeglucion'=> '',
			'cObservaOrofunDeglucion'=> '',
			'cObjetivosDeglucion'=> '',
			'cCumplioObjetivosDeglucion'=> '',
			'cSugerenciasDeglucion'=> '',
			'cPronosticoDeglucion'=> '',
			'cControlMotor'=> '',
			'cDetalleDeglucion'=> '',
			'cUsuarioRealiza'=> '',
			'cFechaEvaluacionVoz'=> '',
			'cDiagnosticoVoz'=> '',
			'cEstadoConcienciaVoz'=> '',
			'cSeestaAsperandoVoz'=> '',
			'cAlimentacionVoz'=> '',
			'cSialorreaVoz'=> '',
			'cSealimentaenformaVoz'=> '',
			'cTraqueostomiaVoz'=> '',
			'cCanulaVoz'=> '',
			'cVenturyVoz'=> '',
			'cVentilacionmecanicaVoz'=> '',
			'cIntubacionVoz'=> '',
			'cTiempointubacionVoz'=> '',
			'cTonopercepcionVoz'=> '',
			'cTimbreFuentegloticaVoz'=> '',
			'cTimbreFiltrosresonVoz'=> '',
			'cConfidencialVoz'=> '',
			'cConversacionalVoz'=> '',
			'cProyectadaVoz'=> '',
			'cEsfuerzovocalVoz'=> '',
			'cIngurgitacionvenosaVoz'=> '',
			'cVozdellamadaVoz'=> '',
			'cADerecha'=> '',
			'cAIzquierda'=> '',
			'cEDerecha'=> '',
			'cEIzquierda'=> '',
			'cIDerecha'=> '',
			'cIIzquierda'=> '',
			'cODerecha'=> '',
			'cOIzquierda'=> '',
			'cUDerecha'=> '',
			'cUIzquierda'=> '',
			'cMDerecha'=> '',
			'cMIzquierda'=> '',
			'cNDerecha'=> '',
			'cNIzquierda'=> '',
			'cAtaqueVoz'=> '',
			'cCuerpoVoz'=> '',
			'cFilaturaVoz'=> '',
			'cAMaximo1'=> '',
			'cAMaximo2'=> '',
			'cAMaximo3'=> '',
			'cAMaximo4'=> '',
			'cSCompetencia1'=> '',
			'cSCompetencia2'=> '',
			'cSCompetencia3'=> '',
			'cSCompetencia4'=> '',
			'cZMaximo1'=> '',
			'cZMaximo2'=> '',
			'cZMaximo3'=> '',
			'cZMaximo4'=> '',
			'cIncordRespiratoriaVoz'=> '',
			'cSealteraTonoVoz'=> '',
			'cHayesfuerzovocalVoz'=> '',
			'cImprecisionarticularVoz'=> '',
			'cRespiratorioTipoVoz'=> '',
			'cRespiratorioReposoVoz'=> '',
			'cRespiratorioFonacionVoz'=> '',
			'cImpresionDiagnosticaVoz'=> '',
			'cPronosticoVoz'=> '',
			'cIntervencionVoz'=> '',
			'cRecomendacionesVoz'=> '',
			'cObjetivosVoz'=> '',
			'cCumplioObjetivosVoz'=> '',
			'cObjetivosSugerenciasVoz'=> '',
			'cUsuarioRealizaVoz'=> '',
			'cFechaEvaluacionLenguaje'=> '',
			'cEstadoConcienciaLenguaje'=> '',
			'cDiagnosticoLenguaje'=>'',
			'cTrastornoLenguaje'=> '',
			'cTipoTrastornoLenguaje'=> '',
			'cApraxiaOralLenguaje'=> '',
			'cImpresionDiagnosticaLenguaje'=> '',
			'cObjetivosLenguaje'=> '',
			'cCumplioObjetivosLenguaje'=> '',
			'cSugerenciasLenguaje'=> '',
			'cPronosticoLenguaje'=> '',
			'cIntervencionLenguaje'=> '',
			'cRecomendacionesLenguaje'=> '',
			'cLenguajeConversacional'=> '',
			'cObsLenguajeConversacion'=> '',
			'cObsLenguajeComprension'=> '',
			'cObsLenguajeExpesionOral'=> '',
			'cObsLenguajeValoracionLectura'=> '',
			'cObsLenguajeValoracionEscritura'=> '',
			'cObsLenguajeSignosLinguisticos'=> '',
			'cFechaEvaluacionHabla'=> '',
			'cDiagnosticoHabla'=> '',
			'cEstadoConcienciaHabla'=> '',
			'cValoraLabioDentalesHabla'=> '',
			'cValoraLinguoDentalesHabla'=> '',
			'cValoraLinguAlveolaresHabla'=> '',
			'cValoraPalatalesHabla'=> '',
			'cValoraVelaresHabla'=> '',
			'cValoraVibraSimpleHabla'=> '',
			'cValoraVibraMultiHabla'=> '',
			'cValoraBilabialesHabla'=> '',
			'cValoraObservacionHabla'=> '',
			'cTrastornoHabla'=> '',
			'cTipoTrastornoHabla'=> '',
			'cDiadococinesis'=> '',
			'cIntegibilidad'=> '',
			'cImpresionDiagnosticaHabla'=> '',
			'cObjetivosHabla'=> '',
			'cCumplioObjetivosHabla'=> '',
			'cSugerenciasHabla'=> '',
			'cPronosticoHabla'=> '',
			'cIntervencionHabla'=> '',
			'cRecomendacionesHabla'=> '',
			'cTextoPandemiaDeglucion'=> '',
			'cTextoPandemiaVoz'=> '',
			'cTextoPandemiaLenguaje'=> '',
			'cTextoPandemiaHabla'=> '',
		];
	}
	
}
