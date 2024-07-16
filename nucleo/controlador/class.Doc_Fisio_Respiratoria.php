<?php
namespace NUCLEO;

class Doc_Fisio_Respiratoria
{
	protected $oDb;
	protected $cTipoDocumento='';
	protected $cTituloAleteo='';
	protected $cTituloCianosis='';
	protected $cTosCaracter='';
	protected $cSecrecionCaracter='';
	protected $cDescripcionDecanulacion='';
	protected $lcTituloPatrones='';
	protected $aDocumento = [];
	protected $aListaParametros = [];
	protected $aListaSeguimiento = [];
	protected $laListaPatrones = [];

	protected $aReporte = [
					'cTitulo' => 'TERAPIA RESPIRATORIA INTEGRAL',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,'forma'=>'FIS010'],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	 //Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->cTipoDocumento = $taData['cTipoProgr'];
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	 private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();

		$laListaParametros = $this->oDb
			->select('CL2TMA, DE1TMA, CL1TMA')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FISRES')
			->where('CL2TMA', '<>', '')
			->where('ESTTMA', '=', '')
			->in('CL1TMA',['1','2','3','4','5','6','7','8','9','10','11','12','13'])
			->getAll('array');
		foreach($laListaParametros as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aListaParametros[$laPar['CL1TMA']][$laPar['CL2TMA']] = $laPar['DE1TMA'];
		}

		$laListaSeguimiento = $this->oDb
			->select('CL2TMA, DE1TMA, CL1TMA')
			->from('TABMAE')
			->where('TIPTMA', '=', 'DATTRA')
			->where('ESTTMA', '=', '')
			->in('CL1TMA',['01','02','03','04','05','06','07','08','09'])
			->getAll('array');
		foreach($laListaSeguimiento as $laSegui) {
			$laSegui = array_map('trim', $laSegui);
			$this->aListaSeguimiento[$laSegui['CL1TMA']][$laSegui['CL2TMA']] = $laSegui['DE1TMA'];
		}

		//	Lista objetivos
		$laListaObjetivos = $this->oDb
			->select('trim(CL3TMA) CODIGO, trim(DE2TMA) Descripcion')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FISRES')->where('CL1TMA', '=', '24')
			->orderBy ('DE2TMA')
			->getAll('array');
		$this->aListaObjetivos = $laListaObjetivos;

		$laRespiratoria = $this->oDb
			->select('CLITRE, DESTRE, USRTRE')
			->from('FISTRE')
			->where([
				'INGTRE'=>$taData['nIngreso'],
				'CCITRE'=>$taData['nConsecCita'],
				'CUPTRE'=>$taData['cCUP'],
			])
			->orderBy('CLITRE')
			->getAll('array');


		foreach($laRespiratoria as $laData) {
			switch (true) {

				case $laData['CLITRE']==10:
					$laDocumento['cSoporteOxigeno'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cOxigenoCnn'] = trim(substr($laData['DESTRE'],4,2));
					$laDocumento['cOxigenoVen'] = trim(substr($laData['DESTRE'],4,2));
					$laDocumento['cOxigenoOtr'] = trim(substr($laData['DESTRE'],4,2));
					$laDocumento['cEstadoConciencia'] = trim(substr($laData['DESTRE'],8,2));
					$laDocumento['cRitmo'] = trim(substr($laData['DESTRE'],12,2));
					$laDocumento['cFrecCardiaca'] = trim(substr($laData['DESTRE'],16,3));
					$laDocumento['cFrecRespira'] = trim(substr($laData['DESTRE'],21,3));
					$laDocumento['cPatronResp'] = trim(substr($laData['DESTRE'],26,2));
					$laDocumento['cExpansion'] = trim(substr($laData['DESTRE'],30,2));
					$laDocumento['cRuidosResp1'] = trim(substr($laData['DESTRE'],34,2));
					$laDocumento['cRuidosResp2'] = trim(substr($laData['DESTRE'],38,2));
					$laDocumento['cRuidosResp3'] = trim(substr($laData['DESTRE'],42,2));
					$laDocumento['cPatronTos'] = trim(substr($laData['DESTRE'],46,2));
					$laDocumento['cDxPrincipal'] = trim(substr($laData['DESTRE'],50,2));
					$laDocumento['cDxRelacionado'] = trim(substr($laData['DESTRE'],54,2));
					$laDocumento['cUsuarioRealiza'] = trim($laData['USRTRE']);
					break;

				case $laData['CLITRE']==11:
					$laDocumento['cRuidosRespD1'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==12:
					$laDocumento['cRuidosRespD2'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==13:
					$laDocumento['cRuidosRespD3'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==15:
					$laDocumento['cEsputo'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==20:
					$laDocumento['cDisnea'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==25:
					$laDocumento['cDificultadResp'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cTirajes'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cCianosis'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cAleteoNasal'] = trim(substr($laData['DESTRE'],12,1));
					$laDocumento['cPolipnea'] = trim(substr($laData['DESTRE'],16,1));
					$laDocumento['cUsoMuscAcc'] = trim(substr($laData['DESTRE'],20,1));
					break;

				case $laData['CLITRE'] >= 55 && $laData['CLITRE'] <= 99:
					$laDocumento['cIntervFisio'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 100 && $laData['CLITRE'] <= 499:
					$laDocumento['cRecomendaciones'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==500:
					$laDocumento['cTraqueostomizado'] = 'S';
					$laDocumento['cSP_TraumaRaquimedural'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cSP_LesionSupratentorial'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cSP_IOTmas7dias'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cSP_ExtFallidaMas2'] = trim(substr($laData['DESTRE'],12,1));
					break;

				case $laData['CLITRE']==501:
					$laDocumento['cTraqueostomizado'] = 'S';
					$laDocumento['cSeguimientoPac'] = 'S';
					$laDocumento['cSP_TraumaRaquimedural'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cSP_LesionSupratentorial'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cSP_IOTmas7dias'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cSP_ExtFallidaMas2'] = trim(substr($laData['DESTRE'],12,1));
					$laDocumento['cSangradoPostQx'] = trim(substr($laData['DESTRE'],16,2));
					$laDocumento['cInfeccion'] = trim(substr($laData['DESTRE'],20,2));
					$laDocumento['cTosCalidad'] = trim(substr($laData['DESTRE'],24,2));
					$laDocumento['cTosAparicion'] = trim(substr($laData['DESTRE'],28,2));
					$laDocumento['cSecreciones'] = trim(substr($laData['DESTRE'],32,2));
					$laDocumento['cSecrecionAspecto'] = trim(substr($laData['DESTRE'],36,2));
					$laDocumento['cGastrostomia'] = trim(substr($laData['DESTRE'],40,2));
					break;

				case $laData['CLITRE'] >= 502 && $laData['CLITRE'] <= 699:
					$laDocumento['cObsNoSeguimiento'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 700 && $laData['CLITRE'] <= 899:
					$laDocumento['cTraqueostomizado'] = 'S';
					$laDocumento['cObsLavado'] .= $laData['DESTRE'];
					if ($laData['CLITRE']==700) $laDocumento['cSP_LavadoCanula'] = 'S';
					break;

				case $laData['CLITRE']==900:
					$laDocumento['cTraqueostomizado'] = 'S';
					$laDocumento['cDecanulacionSitio'] = trim(substr($laData['DESTRE'],0,2));
					$laDocumento['dDecanulacion'] = trim(substr($laData['DESTRE'],4,8));
					break;

				case $laData['CLITRE']==901:
					$laDocumento['cDecanulacionLugar'] = trim(substr($laData['DESTRE'],0,2));
					break;

				case $laData['CLITRE'] >= 902 && $laData['CLITRE'] <= 1299:
					$laDocumento['cDecanulacionComplica'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 1300 && $laData['CLITRE'] <= 1699:
					$laDocumento['cDecanulacionFisio'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 1700 && $laData['CLITRE'] <= 2099:
					$laDocumento['cDecanulacionFono'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 2100 && $laData['CLITRE'] <= 2499:
					$laDocumento['cDecanulacionMedico'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==2500:
					$laDocumento['cSeguimientoPac'] = 'N';
					$laDocumento['cTraqueostomizado'] = 'S';
					break;

				case $laData['CLITRE']==2800:
					$laDocumento['cSP_LavadoCanula'] = 'N';
					$laDocumento['cTraqueostomizado'] = 'S';
					break;

				case $laData['CLITRE']==3200:
					$laDocumento['cPriorizacion'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cTratamiento'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cEvaluacion'] = trim(substr($laData['DESTRE'],4,1));
					break;

				case $laData['CLITRE']==3201:
					$laDocumento['cAltoFlujoLpm'] = floatval(trim(substr($laData['DESTRE'],0,3)));
					$laDocumento['cAltoFlujoPorcentaje'] = trim(substr($laData['DESTRE'],4,3));
					break;

				case $laData['CLITRE']==3202:
					$laDocumento['cVentilacionInvasiva'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cAeroConVentilacionMecanica'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cSolucionHipertonica'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cIloprost'] = trim(substr($laData['DESTRE'],6,1));
					$laDocumento['cSolucionIsotonica'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cColistina'] = trim(substr($laData['DESTRE'],10,1));
					$laDocumento['cSalbutamol'] = trim(substr($laData['DESTRE'],12,1));
					$laDocumento['cEpinefrina'] = trim(substr($laData['DESTRE'],14,1));
					$laDocumento['cBromuro'] = trim(substr($laData['DESTRE'],16,1));
					$laDocumento['cBudesonida'] = trim(substr($laData['DESTRE'],18,1));
					$laDocumento['cDornasa'] = trim(substr($laData['DESTRE'],20,1));
					$laDocumento['cTobramiana'] = trim(substr($laData['DESTRE'],22,1));
					break;

				case $laData['CLITRE'] >= 3210 && $laData['CLITRE'] <= 3249:
					$laDocumento['cDosificacionConVentilacion'] .= $laData['DESTRE'];
					break;


				case $laData['CLITRE'] >= 3250 && $laData['CLITRE'] <= 3299:
					$laDocumento['cOtrosConVentilacion'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==3300:
					$laDocumento['cVentilacionNoInvasiva'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cAeroSinVentilacionMecanica'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cSolucionHipertonicaSin'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cIloprostSin'] = trim(substr($laData['DESTRE'],6,1));
					$laDocumento['cSolucionIsotonicaSin'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cColistinaSin'] = trim(substr($laData['DESTRE'],10,1));
					$laDocumento['cSalbutamolSin'] = trim(substr($laData['DESTRE'],12,1));
					$laDocumento['cEpinefrinaSin'] = trim(substr($laData['DESTRE'],14,1));
					$laDocumento['cBromuroSin'] = trim(substr($laData['DESTRE'],16,1));
					$laDocumento['cBudesonidaSin'] = trim(substr($laData['DESTRE'],18,1));
					$laDocumento['cDornasaSin'] = trim(substr($laData['DESTRE'],20,1));
					$laDocumento['cTobramianaSin'] = trim(substr($laData['DESTRE'],22,1));
					break;

				CASE $laData['CLITRE'] >= 3310 && $laData['CLITRE'] <= 3349:
					$laDocumento['cDosificacionSinVentilacion'] .= $laData['DESTRE'];
					break;

				CASE $laData['CLITRE'] >= 3350 && $laData['CLITRE'] <= 3399:
					$laDocumento['cOtrosSinVentilacion'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==3400:
					$laDocumento['cInhaloterapiaCon'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cSalbutamolInhaloCon'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cBromuroInhaloCon'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cBeclometasonaInhaloCon'] = trim(substr($laData['DESTRE'],6,1));
					break;

				case $laData['CLITRE'] >= 3410 && $laData['CLITRE'] <= 3449:
					$laDocumento['cDosificacionInhaloCon'] .= trim($laData['DESTRE']);
					break;

				case $laData['CLITRE'] >= 3450 && $laData['CLITRE'] <= 3499:
					$laDocumento['cOtrosInhaloCon'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==3500:
					$laDocumento['cInhaloterapiaSin'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cSalbutamolInhaloSin'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cBromuroInhaloSin'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cBeclometasonaInhaloSin'] = trim(substr($laData['DESTRE'],6,1));
					break;

				case $laData['CLITRE'] >= 3510 && $laData['CLITRE'] <= 3549:
					$laDocumento['cDosificacionInhaloSin'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 3550 && $laData['CLITRE'] <= 3599:
					$laDocumento['cOtrosInhaloSin'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE']==3600:
					$laDocumento['cDrenajePostural'] = trim(substr($laData['DESTRE'],0,1));
					$laDocumento['cHigieneBronquial'] = trim(substr($laData['DESTRE'],2,1));
					$laDocumento['cTosAsistida'] = trim(substr($laData['DESTRE'],4,1));
					$laDocumento['cEstimuloExterno'] = trim(substr($laData['DESTRE'],6,1));
					$laDocumento['cPercusion'] = trim(substr($laData['DESTRE'],8,1));
					$laDocumento['cDrenajeAlveolar'] = trim(substr($laData['DESTRE'],10,1));
					$laDocumento['cVibracion'] = trim(substr($laData['DESTRE'],12,1));
					$laDocumento['cEjerciciosRespiratorios'] = trim(substr($laData['DESTRE'],14,1));
					$laDocumento['cManiobrasLento'] .= trim(substr($laData['DESTRE'],16,1));
					$laDocumento['cFlutter'] = trim(substr($laData['DESTRE'],18,1));
					$laDocumento['cPostiaux'] = trim(substr($laData['DESTRE'],20,1));
					$laDocumento['cIncentivoRespiratorio'] = trim(substr($laData['DESTRE'],22,1));
					$laDocumento['cManiobrasFlujo'] = trim(substr($laData['DESTRE'],24,1));
					$laDocumento['cPresionPositiva'] = trim(substr($laData['DESTRE'],26,1));
					break;

				case $laData['CLITRE']==3700:
					$laDocumento['cListaObjetivos'] = trim($laData['DESTRE']);
					break;

				case $laData['CLITRE']==3800:
					$laDocumento['cCumplioObjetivos'] = trim(substr($laData['DESTRE'],0,1));
					break;

				case $laData['CLITRE'] >= 3801 && $laData['CLITRE'] <= 3899:
					$laDocumento['cObjetivosSugerencias'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 4000 && $laData['CLITRE'] <= 4499:
					$laDocumento['cReformulacion'] .= $laData['DESTRE'];
					break;

				case $laData['CLITRE'] >= 6000 && $laData['CLITRE'] <= 7999:
					$laDocumento['cTextoPandemia'] .= $laData['DESTRE'];
					break;
			}
		}

		$laKeysTrim = [
			'cReformulacion','cObjetivosSugerencias','cOtrosInhaloSin','cDosificacionInhaloSin',
			'cOtrosInhaloCon','cOtrosSinVentilacion','cDosificacionSinVentilacion','cEsputo',
			'cOtrosConVentilacion','cDosificacionConVentilacion','cIntervFisio','cRecomendaciones',
			'cPriorizacion','cTratamiento','cEvaluacion','cAltoFlujoLpm','cAltoFlujoPorcentaje',
			'cDosificacionConVentilacion','cOtrosConVentilacion','cDosificacionSinVentilacion',
			'cOtrosSinVentilacion','cDecanulacionComplica','cDecanulacionFisio','cDecanulacionFono',
			'cDecanulacionMedico','cObsNoSeguimiento','cObsLavado','cTextoPandemia', ];
		foreach($laKeysTrim as $lcKey)
			$laDocumento[$lcKey] = trim($laDocumento[$lcKey]);

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'INCOPRIO', ['CL1TMA'=>$laDocumento['cPriorizacion'], 'ESTTMA'=>' ']);
		$laDocumento['cDescPriorizacion'] = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'PATFIS', ['CL1TMA'=>$laDocumento['cDxPrincipal'], 'ESTTMA'=>' ']);
		$laDocumento['cDescripcionDxPrincipal'] = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));

		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'PATFIS', ['CL1TMA'=>$laDocumento['cDxRelacionado'], 'ESTTMA'=>' ']);
		$laDocumento['cDescripcionDxRelacionado'] = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));

		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData) {
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;

		$cSoporteOxigeno = !empty($this->aDocumento['cSoporteOxigeno'])? $this->aListaParametros['1'][$this->aDocumento['cSoporteOxigeno']] :'';
		switch (true) {
			case $this->aDocumento['cSoporteOxigeno']==1:
				$cTipoOxigeno = !empty($this->aDocumento['cOxigenoCnn'])? $this->aListaParametros['2'][$this->aDocumento['cOxigenoCnn']] :'';
				break;

			case $this->aDocumento['cSoporteOxigeno']==2:
				$cTipoOxigeno = !empty($this->aDocumento['cOxigenoVen'])? $this->aListaParametros['3'][$this->aDocumento['cOxigenoVen']] :'';
				break;

			case $this->aDocumento['cSoporteOxigeno']==3:
				$cTipoOxigeno = !empty($this->aDocumento['cOxigenoOtr'])? $this->aListaParametros['4'][$this->aDocumento['cOxigenoOtr']] :'';
				break;

			case $this->aDocumento['cSoporteOxigeno']==6:
				$cTipoOxigeno = $this->aDocumento['cAltoFlujoLpm'] .' Lpm   ' .$this->aDocumento['cAltoFlujoPorcentaje'] .'%';
				break;

			default:
				$cTipoOxigeno = '';
		}

		switch (true) {
			case !empty($this->aDocumento['cFrecCardiaca']) && !empty($this->aDocumento['cFrecRespira']):
				$cSisCardioPul = 'F.C.: ' .$this->aDocumento['cFrecCardiaca'] .' - F.R.: ' .$this->aDocumento['cFrecRespira'];
				break;

			case !empty($this->aDocumento['cFrecCardiaca']) && empty($this->aDocumento['cFrecRespira']):
				$cSisCardioPul = 'F.C.: ' .$this->aDocumento['cFrecCardiaca'];
				break;

			case empty($this->aDocumento['cFrecCardiaca']) && !empty($this->aDocumento['cFrecRespira']):
				$cSisCardioPul = 'F.R.: ' .$this->aDocumento['cFrecRespira'];
				break;
		}

		$cEstadoConciencia = !empty($this->aDocumento['cEstadoConciencia'])? $this->aListaParametros['5'][$this->aDocumento['cEstadoConciencia']] :'';
		$cRitmo = !empty($this->aDocumento['cRitmo'])? $this->aListaParametros['9'][$this->aDocumento['cRitmo']] :'';
		$cPatronResp = !empty($this->aDocumento['cPatronResp'])? $this->aListaParametros['6'][$this->aDocumento['cPatronResp']] :'';
		$cExpansion = !empty($this->aDocumento['cExpansion'])? $this->aListaParametros['10'][$this->aDocumento['cExpansion']] :'';
		$cRuidosResp1 = !empty($this->aDocumento['cRuidosResp1'])? $this->aListaParametros['7'][$this->aDocumento['cRuidosResp1']] :'';
		$cRuidosResp2 = !empty($this->aDocumento['cRuidosResp2'])? $this->aListaParametros['7'][$this->aDocumento['cRuidosResp2']] :'';
		$cRuidosResp3 = !empty($this->aDocumento['cRuidosResp3'])? $this->aListaParametros['7'][$this->aDocumento['cRuidosResp3']] :'';

		if (!empty(trim($this->aDocumento['cTextoPandemia']))) {
			$laTr['aCuerpo'][] = ['titulo2', ' '];
			$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cTextoPandemia']];
		}
		
		// INICIO DOMINIO CARDIOPULMONAR
		if (!empty(trim($this->aDocumento['cPriorizacion'])) || !empty(trim($this->aDocumento['cTratamiento']))
			 || !empty(trim($this->aDocumento['cEvaluacion'])) || !empty(trim($this->aDocumento['cSoporteOxigeno'])) )
		{
			if (!empty(trim($this->aDocumento['cTratamiento']))){
				$this->aDocumento['cTratamiento'] = trim($this->aDocumento['cTratamiento'])=='S'? 'SI' : 'NO';
			}	
			if (!empty(trim($this->aDocumento['cEvaluacion']))){
				$this->aDocumento['cEvaluacion'] = trim($this->aDocumento['cEvaluacion'])=='S'? 'SI' : 'NO';
			}	

			$laTr['aCuerpo'][] = ['titulo1', 'DOMINIO CARDIOPULMONAR'];
			$laW4 = [35, 4, 48, 48, 48, ];
			$laW = [35, 4, 48, 90, ];
			
			if (!empty(trim($this->aDocumento['cDescPriorizacion']))){
				$laTbl = [
					['w'=>$laW4,'d'=>['Priorización', ':', $this->aDocumento['cDescPriorizacion'], 'Tratamiento: '.$this->aDocumento['cTratamiento'], 'Evaluación: '.$this->aDocumento['cEvaluacion'] ]],
				];
			}	
				
			$laTbl = [
				['w'=>$laW, 'd'=>['Soporte de Oxigeno', ':', $cSoporteOxigeno, $cTipoOxigeno ]],
				['w'=>$laW, 'd'=>['Estado conciencia', ':', $cEstadoConciencia, $cRitmo ]],
				['w'=>$laW, 'd'=>['Sis. CardioPulmonar', ':', $cSisCardioPul, '' ]],
				['w'=>$laW, 'd'=>['Patrón respiratorio', ':', $cPatronResp, 'Expansión: '.$cExpansion ]],
				['w'=>$laW, 'd'=>['Ruidos resp.', ':', $cRuidosResp1, $this->aDocumento['cRuidosRespD1'] ]],
			];
			if (!empty(trim($cRuidosResp2)))
				$laTbl[] = ['w'=>$laW, 'd'=>['Ruidos resp. II', ':', $cRuidosResp2, $this->aDocumento['cRuidosRespD2'] ]];
			if (!empty(trim($cRuidosResp3)))
				$laTbl[] = ['w'=>$laW, 'd'=>['Ruidos resp. III', ':', $cRuidosResp3, $this->aDocumento['cRuidosRespD3'] ]];
			if (!empty($this->aDocumento['cPatronTos'])){
				$cPatronTos = !empty($this->aDocumento['cPatronTos'])? $this->aListaParametros['8'][$this->aDocumento['cPatronTos']] :'';
				$laTbl[] = ['w'=>$laW, 'd'=>['Patrón TOS', ':', $cPatronTos, '' ]];
			}
			$laW = [35, 4, 150, ];
			if (!empty($this->aDocumento['cEsputo']))
				$laTbl[] = ['w'=>$laW, 'd'=>['Esputo', ':', $this->aDocumento['cEsputo'], '' ]];

			if (!empty(trim($this->aDocumento['cDisnea'])) || trim($this->aDocumento['cDisnea']) >=0 ){
				if ($this->aDocumento['cDisnea'] == '0'){
					$cDisnea = 'GRADO 0';
				}else{
					$cDisnea = !empty($this->aDocumento['cDisnea'])? $this->aListaParametros['11'][$this->aDocumento['cDisnea']] :'';
				}
				
				if (!empty(trim($cDisnea))){
					$laTbl[] = ['w'=>$laW, 'd'=>['Disnea', ':', $cDisnea, '' ]];
				}	
			}
			$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];
				
			if (!empty($this->aDocumento['cDificultadResp'])){
				$laTbl = [];
				$cDificultadResp = !empty(trim($this->aDocumento['cDificultadResp'])) ? $this->aDocumento['cDificultadResp']=='S'? 'SI' : 'NO' : '';
				$laTr['aCuerpo'][] = ['titulo2', 'Signos de dificultad respiratoria:  ' .$cDificultadResp];
				
				if ($cDificultadResp=='SI'){
					$cTirajes = !empty($this->aDocumento['cTirajes'])? ('Tirajes: ' . $this->aListaParametros['12'][$this->aDocumento['cTirajes']]) : 'Tirajes: ';
					$cCianosis = !empty($this->aDocumento['cCianosis'])? ('Cianosis:' . $this->aListaParametros['13'][$this->aDocumento['cCianosis']]) : 'Cianosis: ';
					$cAleteoNasal = empty($this->aDocumento['cAleteoNasal'])? 'Aleteo Nasal: ': ('Aleteo Nasal: ' .(trim($this->aDocumento['cAleteoNasal'])=='S'? 'SI' : 'NO'));
					$cPolipnea = empty($this->aDocumento['cPolipnea'])? '': ('Polipnea: ' .(trim($this->aDocumento['cPolipnea'])=='S'? 'SI' : 'NO'));
					$cUsoMuscAcc = empty($this->aDocumento['cUsoMuscAcc'])? '': ('Uso de músculos Acc: ' .(trim($this->aDocumento['cUsoMuscAcc'])=='S'? 'SI' : 'NO'));
					
					if (!empty(trim($this->aDocumento['cTirajes'])) || !empty(trim($this->aDocumento['cCianosis'])) || !empty(trim($this->aDocumento['cAleteoNasal']))
						|| !empty(trim($this->aDocumento['cPolipnea'])) || !empty(trim($this->aDocumento['cUsoMuscAcc']))){
						$laW = [65, 50, 50,];
						if (!empty(trim($cTirajes)) || !empty(trim($cCianosis))){
							
							$laTbl = [
								['w'=>[60, 60, 60, ], 'd'=>[$cTirajes, $cCianosis, '' ]],
								['w'=>[60, 60, 60, ], 'd'=>[ $cAleteoNasal, $cPolipnea,$cUsoMuscAcc ]],
							];
						}
					}	
				}	
				$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];					
			}	
		}
		// FIN DOMINIO CARDIOPULMONAR

		//	INICIO INTERVENCION
		if (!empty(trim($this->aDocumento['cVentilacionInvasiva'])) || !empty(trim($this->aDocumento['cAeroConVentilacionMecanica'])) || !empty(trim($this->aDocumento['cDosificacionConVentilacion'])) 
			|| !empty(trim($this->aDocumento['cOtrosConVentilacion'])) || !empty(trim($this->aDocumento['cInhaloterapiaCon'])) || !empty(trim($this->aDocumento['cDrenajePostural'])) 
			|| !empty(trim($this->aDocumento['cPresionPositiva'])) || !empty(trim($this->aDocumento['cTosAsistida'])) || !empty(trim($this->aDocumento['cPostiaux'])) || !empty(trim($this->aDocumento['cPercusion']))
			|| !empty(trim($this->aDocumento['cManiobrasFlujo'])) || !empty(trim($this->aDocumento['cVibracion']))	
			){
			
			$laTr['aCuerpo'][] = ['titulo1',	'INTERVENCIÓN'];
			
			$cVentilacionInvasiva = !empty(trim($this->aDocumento['cVentilacionInvasiva'])) ? $this->aDocumento['cVentilacionInvasiva']=='S'? 'SI' : 'NO' : '';
			$laTr['aCuerpo'][] = ['titulo2', 'Ventilación mecánica invasiva:  ' .$cVentilacionInvasiva];
			
			$cAeroConVentilacionMecanica = !empty(trim($this->aDocumento['cAeroConVentilacionMecanica'])) ? $this->aDocumento['cAeroConVentilacionMecanica']=='S'? 'SI' : 'NO' : '';
			$laTr['aCuerpo'][] = ['titulo2', 'Aerosolterapía CON ventilación mecánica:  ' .$cAeroConVentilacionMecanica];
			
			if ($cAeroConVentilacionMecanica=='SI'){
				$laW = [52, 4, 24, 40, 4, 25, ];
				$laTbl = [
					['w'=>$laW, 'd'=>[
						'Solución salina hipertónica', ':', ' ['.(trim($this->aDocumento['cSolucionHipertonica'])=='1'? 'X' : '-').']',
						'Iloprost', ':', ' ['.(trim($this->aDocumento['cIloprost'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Solución salina isotónica', ':', ' ['.(trim($this->aDocumento['cSolucionIsotonica'])=='1'? 'X' : '-').']',
						'Colistina', ':', ' ['.(trim($this->aDocumento['cColistina'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Salbutamol', ':', ' ['.(trim($this->aDocumento['cSalbutamol'])=='1'? 'X' : '-').']',
						'Epinefrina racémica', ':', ' ['.(trim($this->aDocumento['cEpinefrina'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Bromuro de ipratropio', ':', ' ['.(trim($this->aDocumento['cBromuro'])=='1'? 'X' : '-').']',
						'Budesonida', ':', ' ['.(trim($this->aDocumento['cBudesonida'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Dornasa alfa', ':', ' ['.(trim($this->aDocumento['cDornasa'])=='1'? 'X' : '-').']',
						'Tobramisina', ':', ' ['.(trim($this->aDocumento['cTobramiana'])=='1'? 'X' : '-').']' ]],
				];
			}
			
			if (!empty(trim($this->aDocumento['cDosificacionConVentilacion']))){
				$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Dosificación', ':', $this->aDocumento['cDosificacionConVentilacion'] ] ];
			}		
			
			if (!empty(trim($this->aDocumento['cOtrosConVentilacion']))){
				$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Otros', ':', $this->aDocumento['cOtrosConVentilacion'] ] ];
				
			}	
			$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];

			if (!empty(trim($this->aDocumento['cInhaloterapiaCon'])) || !empty(trim($this->aDocumento['cDrenajePostural']))
				|| !empty(trim($this->aDocumento['cPresionPositiva'])) || !empty(trim($this->aDocumento['cTosAsistida']))
				|| !empty(trim($this->aDocumento['cPostiaux'])) || !empty(trim($this->aDocumento['cPercusion']))
				|| !empty(trim($this->aDocumento['cManiobrasFlujo'])) || !empty(trim($this->aDocumento['cVibracion']))
				){

				$cInhaloterapiaCon = !empty(trim($this->aDocumento['cInhaloterapiaCon'])) ? $this->aDocumento['cInhaloterapiaCon']=='S'? 'SI' : 'NO' : '';
				$laTr['aCuerpo'][] = ['titulo2', 'Inhaloterapia CON ventilación mecánica: ' .$cInhaloterapiaCon];
				
				if (!empty(trim($cInhaloterapiaCon))){
					$laW = [52, 4, 25, 40, 4, 25, ];
					$laTbl = [
						['w'=>$laW, 'd'=>[
							'Salbutamol',':','['.(trim($this->aDocumento['cSalbutamolInhaloCon'])=='1'? 'X' : '-').']',
							'Bromuro de ipratropio',':','['.(trim($this->aDocumento['cBromuroInhaloCon'])=='1'? 'X' : '-').']' ]],
						['w'=>$laW, 'd'=>[
							'Beclometasona',':','['.(trim($this->aDocumento['cBeclometasonaInhaloCon'])=='1'? 'X' : '-').']', '', '', '' ]],
					];
					if (!empty(trim($this->aDocumento['cDosificacionInhaloCon'])))
						$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Dosificación', ':', $this->aDocumento['cDosificacionInhaloCon'] ] ];
					if (!empty(trim($this->aDocumento['cOtrosInhaloCon'])))
						$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Otros', ':', $this->aDocumento['cOtrosInhaloCon'] ] ];
					$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];
				}	
				
				$laTbl = [];
				$laW = [52, 4, 25, ];
				if (!empty(trim($this->aDocumento['cDrenajePostural'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Drenaje postural', ':', (trim($this->aDocumento['cDrenajePostural'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cPresionPositiva'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Presión positiva intermitente', ':', (trim($this->aDocumento['cPresionPositiva'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cTosAsistida'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Tos asistida', ':', (trim($this->aDocumento['cTosAsistida'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cPostiaux'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Maniobras postiaux', ':', (trim($this->aDocumento['cPostiaux'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cPercusion'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Percusión', ':', (trim($this->aDocumento['cPercusion'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cManiobrasFlujo'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Maniobras de flujo acelerado', ':', (trim($this->aDocumento['cManiobrasFlujo'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cVibracion'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Vibración', ':', (trim($this->aDocumento['cVibracion'])=='S'? 'SI' : 'NO') ]];
				$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];
			}
		}
		// FIN Ventilación mecánica invasiva

		// INICIO Ventilación mecánica NO invasiva
		if (!empty(trim($this->aDocumento['cVentilacionNoInvasiva'])) || !empty(trim($this->aDocumento['cAeroSinVentilacionMecanica']))){
			
			$laTbl = [];
			$cVentilacionNoInvasiva = !empty(trim($this->aDocumento['cVentilacionNoInvasiva'])) ? $this->aDocumento['cVentilacionNoInvasiva']=='S'? 'SI' : 'NO' : '';
			$laTr['aCuerpo'][] = ['titulo2', 'Ventilación mecánica NO invasiva:  ' .$cVentilacionNoInvasiva];

			$cAeroSinVentilacionMecanica = !empty(trim($this->aDocumento['cAeroSinVentilacionMecanica'])) ? $this->aDocumento['cAeroSinVentilacionMecanica']=='S'? 'SI' : 'NO' : '';
			$laTr['aCuerpo'][] = ['titulo2', 'Aerosolterapía SIN ventilación mecánica:  ' .$cAeroSinVentilacionMecanica];
			
			if ($cAeroSinVentilacionMecanica=='SI'){
				$laW = [52, 4, 24, 40, 4, 25, ];
				$laTbl = [
					['w'=>$laW, 'd'=>[
						'Solución salina hipertónica', ':', ' ['.(trim($this->aDocumento['cSolucionHipertonicaSin'])=='1'? 'X' : '-').']',
						'Iloprost', ':', ' ['.(trim($this->aDocumento['cIloprostSin'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Solución salina isotónica', ':', ' ['.(trim($this->aDocumento['cSolucionIsotonicaSin'])=='1'? 'X' : '-').']',
						'Colistina', ':', ' ['.(trim($this->aDocumento['cColistinaSin'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Salbutamol', ':', ' ['.(trim($this->aDocumento['cSalbutamolSin'])=='1'? 'X' : '-').']',
						'Epinefrina racémica', ':', ' ['.(trim($this->aDocumento['cEpinefrinaSin'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Bromuro de ipratropio', ':', ' ['.(trim($this->aDocumento['cBromuroSin'])=='1'? 'X' : '-').']',
						'Budesonida', ':', ' ['.(trim($this->aDocumento['cBudesonidaSin'])=='1'? 'X' : '-').']' ]],
					['w'=>$laW, 'd'=>[
						'Dornasa alfa', ':', ' ['.(trim($this->aDocumento['cDornasaSin'])=='1'? 'X' : '-').']',
						'Tobramisina', ':', ' ['.(trim($this->aDocumento['cTobramianaSin'])=='1'? 'X' : '-').']' ]],
				];
			}
			
			if (!empty(trim($this->aDocumento['cDosificacionSinVentilacion']))){
				$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Dosificación', ':', $this->aDocumento['cDosificacionSinVentilacion'] ] ];
			}
			if (!empty(trim($this->aDocumento['cOtrosSinVentilacion']))){
				$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Otros', ':', $this->aDocumento['cOtrosSinVentilacion'] ] ];
			}	
			$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];

			if (!empty(trim($this->aDocumento['cInhaloterapiaSin'])) || !empty(trim($this->aDocumento['cDosificacionInhaloSin'])) || !empty(trim($this->aDocumento['cOtrosInhaloSin']))
				|| !empty(trim($this->aDocumento['cHigieneBronquial'])) || !empty(trim($this->aDocumento['cFlutter'])) || !empty(trim($this->aDocumento['cEstimuloExterno'])) || !empty(trim($this->aDocumento['cIncentivoRespiratorio']))
				|| !empty(trim($this->aDocumento['cDrenajeAlveolar'])) || !empty(trim($this->aDocumento['cManiobrasLento'])) || !empty(trim($this->aDocumento['cEjerciciosRespiratorios']))
			){	
				$laTbl = [];
				$cInhaloterapiaSin = !empty(trim($this->aDocumento['cInhaloterapiaSin'])) ? $this->aDocumento['cInhaloterapiaSin']=='S'? 'SI' : 'NO' : '';
				$laTr['aCuerpo'][] = ['titulo2', 'Inhaloterapia SIN ventilación mecánica: ' .$cInhaloterapiaSin];
					
				if (trim($cInhaloterapiaSin)=='SI'){
					$laW = [52, 4, 25, 40, 4, 25, ];
					$laTbl = [
						['w'=>$laW, 'd'=>[
							'Salbutamol',':','['.(trim($this->aDocumento['cSalbutamolInhaloSin'])=='1'? 'X' : '-').']',
							'Bromuro de ipratropio',':','['.(trim($this->aDocumento['cBromuroInhaloSin'])=='1'? 'X' : '-').']' ]],
						['w'=>$laW, 'd'=>[
							'Beclometasona',':','['.(trim($this->aDocumento['cBeclometasonaInhaloSin'])=='1'? 'X' : '-').']', '', '', '' ]],
					];
					if (!empty(trim($this->aDocumento['cDosificacionInhaloSin'])))
						$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Dosificación', ':', $this->aDocumento['cDosificacionInhaloSin'] ] ];
					if (!empty(trim($this->aDocumento['cOtrosInhaloSin'])))
						$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Otros', ':', $this->aDocumento['cOtrosInhaloSin'] ] ];
					$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];
				}	

				if (!empty(trim($this->aDocumento['cDosificacionInhaloSin']))){
					$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Dosificación', ':', $this->aDocumento['cDosificacionInhaloSin'] ] ];
				}		
					
				if (!empty(trim($this->aDocumento['cOtrosInhaloSin']))){
					$laTbl[] = ['w'=>[52, 4, 130, ],  'd'=>['Otros', ':', $this->aDocumento['cOtrosInhaloSin'] ] ];
				}		
				
				$laTbl = [];
				$laW = [52, 4, 25, ];
				if (!empty(trim($this->aDocumento['cHigieneBronquial'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Higiene bronquial', ':', (trim($this->aDocumento['cHigieneBronquial'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cFlutter'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Flutter', ':', (trim($this->aDocumento['cFlutter'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cEstimuloExterno'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Estimulo tos externo', ':', (trim($this->aDocumento['cEstimuloExterno'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cIncentivoRespiratorio'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Incentivo respiratorio', ':', (trim($this->aDocumento['cIncentivoRespiratorio'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cDrenajeAlveolar'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Reclutamiento alveolar', ':', (trim($this->aDocumento['cDrenajeAlveolar'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cManiobrasLento'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Maniobras de flujo lento', ':', (trim($this->aDocumento['cManiobrasLento'])=='S'? 'SI' : 'NO') ]];
				if (!empty(trim($this->aDocumento['cEjerciciosRespiratorios'])))
					$laTbl[] = ['w'=>$laW, 'd'=>[ 'Ejercicios rspiratorios', ':', (trim($this->aDocumento['cEjerciciosRespiratorios'])=='S'? 'SI' : 'NO') ]];
				$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];
			}
		}
		// FIN Ventilación mecánica NO invasiva
		//	FIN INTERVENCION

		//	INICIO DIAGNOSTICOS
		if (!empty($this->aDocumento['cDxPrincipal']) || !empty($this->aDocumento['cDxRelacionado'])
			|| !empty($this->aDocumento['cIntervFisio']) || !empty($this->aDocumento['cRecomendaciones']) )
		{
			$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICOS'];
			$laTr['aCuerpo'][] = ['titulo2', 'Fisioterapéutico principal'];
			$laTr['aCuerpo'][] = ['texto9', 'PATRON ' .$this->aDocumento['cDxPrincipal'] .': ' .$this->aDocumento['cDescripcionDxPrincipal']];

			if (!empty(trim($this->aDocumento['cDxRelacionado']))) {
				$laTr['aCuerpo'][] = ['titulo2', 'Fisioterapéutico relacionado'];
				$laTr['aCuerpo'][] = ['texto9', 'PATRON ' .$this->aDocumento['cDxRelacionado'] .': ' .$this->aDocumento['cDescripcionDxRelacionado']];
			}

			if (!empty(trim($this->aDocumento['cIntervFisio']))) {
				$laTr['aCuerpo'][] = ['titulo2', 'Intervención fisioterapéutica'];
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cIntervFisio']];
			}

			if (!empty(trim($this->aDocumento['cRecomendaciones']))) {
				$laTr['aCuerpo'][] = ['titulo2', 'Recomendaciones:'];
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cRecomendaciones']];
			}

			if (!empty(trim($this->aDocumento['cReformulacion']))) {
				$laTr['aCuerpo'][] = ['titulo2', 'Reformulación'];
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cReformulacion']];
			}
		}
		//	FIN DIAGNOSTICOS

		//	INICIO OBJETIVOS
		if (!empty(trim($this->aDocumento['cCumplioObjetivos'])) || !empty(trim($this->aDocumento['cListaObjetivos'])))
		{
			$laTr['aCuerpo'][] = ['titulo1',	'OBJETIVOS FISIOTERAPIA'];
			if (!empty(trim($this->aDocumento['cListaObjetivos'])))
			{
				$laTr['aCuerpo'][] = ['txthtml9', '<b>Objetivos de intervencion </b>'];

				foreach($this->aListaObjetivos as $laDataObj) {
					if (strstr($this->aDocumento['cListaObjetivos'], '"' .$laDataObj['CODIGO'].'"')){
						$laTr['aCuerpo'][] = ['texto9', '* ' .$laDataObj['DESCRIPCION']];
					}
				}
			}

			if (!empty(trim($this->aDocumento['cCumplioObjetivos']))) {
				$laTr['aCuerpo'][] = ['txthtml9', '<b>Cumplio con objetivos de tratamiento? </b>'.'<br>'.($this->aDocumento['cCumplioObjetivos']=='S'? 'SI': 'NO')];
			}

			if (!empty(trim($this->aDocumento['cObjetivosSugerencias']))){
				$laTr['aCuerpo'][] = ['txthtml9', '<b>Sugerencias</b>'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cObjetivosSugerencias']];
			}
		}
		//	FIN OBJETIVOS
		$cSangradoPostQx = !empty($this->aDocumento['cSangradoPostQx'])? $this->aListaSeguimiento['01'][$this->aDocumento['cSangradoPostQx']] :'';
		$cInfeccion = !empty($this->aDocumento['cInfeccion'])? $this->aListaSeguimiento['02'][$this->aDocumento['cInfeccion']] :'';
		$cTosCalidad = !empty($this->aDocumento['cTosCalidad'])? $this->aListaSeguimiento['03'][$this->aDocumento['cTosCalidad']] :'';
		$cTosAparicion = !empty($this->aDocumento['cTosAparicion'])? $this->aListaSeguimiento['04'][$this->aDocumento['cTosAparicion']] :'';
		$cSecreciones = !empty($this->aDocumento['cSecreciones'])? $this->aListaSeguimiento['05'][$this->aDocumento['cSecreciones']] :'';
		$cSecrecionAspecto = !empty($this->aDocumento['cSecrecionAspecto'])? $this->aListaSeguimiento['06'][$this->aDocumento['cSecrecionAspecto']] :'';
		$cGastrostomia = !empty($this->aDocumento['cGastrostomia'])? ($this->aListaSeguimiento['07'][$this->aDocumento['cGastrostomia']]??'') :'';
		$cDecanulacionSitio = !empty($this->aDocumento['cDecanulacionSitio'])? $this->aListaSeguimiento['08'][$this->aDocumento['cDecanulacionSitio']] :'';
		$cDecanulacionLugar = !empty($this->aDocumento['cDecanulacionLugar'])? $this->aListaSeguimiento['09'][$this->aDocumento['cDecanulacionLugar']] :'';

		//	INICIO SEGUIMIENTO PACIENTES
		if ($this->aDocumento['cTraqueostomizado']=='S'){
			$laTr['aCuerpo'][] = ['titulo1', 'SEGUIMIENTO DE PACIENTES'];
			$laTr['aCuerpo'][] = ['titulo2', 'Paciente traqueostomizado  SI'];

			$laW = [42, 34, 58, 28, ];
			$laTbl = [];
			if(!empty($this->aDocumento['cSeguimientoPac']))
				$laTbl[] = ['w'=>$laW, 'd'=>[
					'Seguimiento de paciente', ': '.($this->aDocumento['cSeguimientoPac']=='S'? 'SI' : 'NO')]];
			if(!empty($this->aDocumento['cSP_LavadoCanula']))
				$laTbl[] = ['w'=>$laW, 'd'=>[
					'Lavado Cánula', ': '.($this->aDocumento['cSP_LavadoCanula']=='S'? 'SI' : 'NO')]];

			$laTbl[] = ['w'=>$laW, 'd'=>[
					'Trauma raquimedural', ': '.($this->aDocumento['cSP_TraumaRaquimedural']=='S'? 'SI' : 'NO'),
					'Lesión supratentorial', ': '.($this->aDocumento['cSP_LesionSupratentorial']=='S'? 'SI' : 'NO') ]];
			$laTbl[] = ['w'=>$laW, 'd'=>[
					'IOT > 7 DÍAS', ': '.($this->aDocumento['cSP_IOTmas7dias']=='S'? 'SI' : 'NO'),
					'Extubacion fallida > 2 ocasiones', ': '.($this->aDocumento['cSP_ExtFallidaMas2']=='S'? 'SI' : 'NO') ]];
			$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];

			if (!empty($cSangradoPostQx)){
				$laTr['aCuerpo'][] = ['titulo2', 'Sangrado postquirúrgico'];
				$laTr['aCuerpo'][] = ['texto9',	$cSangradoPostQx];
			}

			if (!empty($cInfeccion)){
				$laTr['aCuerpo'][] = ['titulo2', 'Infección'];
				$laTr['aCuerpo'][] = ['texto9',	$cInfeccion];
			}

			if (!empty($cTosCalidad) || !empty($cTosAparicion)){
				if (!empty($cTosCalidad) && !empty($cTosAparicion)){
					$this->cTosCaracter = ' / ';
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Tos calidad / aparición'];
				$laTr['aCuerpo'][] = ['texto9',	$cTosCalidad .$this->cTosCaracter .$cTosAparicion];
			}

			if (!empty($cSecreciones) || !empty($cSecrecionAspecto)){
				if (!empty($cSecreciones) && !empty($cSecrecionAspecto)){
					$this->cSecrecionCaracter = ' / ';
				}
				$laTr['aCuerpo'][] = ['titulo2', 'Secreciones'];
				$laTr['aCuerpo'][] = ['texto9',	$cSecreciones .$this->cSecrecionCaracter .$cSecrecionAspecto];
			}

			if (!empty($cGastrostomia)){
				$laTr['aCuerpo'][] = ['titulo2', 'Gastrostomía'];
				$laTr['aCuerpo'][] = ['texto9',	$cGastrostomia];
			}

			if (!empty($this->aDocumento['cObsNoSeguimiento'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Observaciones seguimiento'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cObsNoSeguimiento']];
			}

			if (!empty($this->aDocumento['cObsLavado'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Observaciones lavado'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cObsLavado']];
			}


			if (!empty($this->aDocumento['dDecanulacion'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Decanulación'];
				$this->cDescripcionDecanulacion = !empty($cDecanulacionSitio)? '    Sitio: ' .$cDecanulacionSitio :'';
				$laTr['aCuerpo'][] = ['texto9',	'Fecha: ' .AplicacionFunciones::formatFechaHora('fecha', $this->aDocumento['dDecanulacion'], '/') .$this->cDescripcionDecanulacion];
			}

			if (!empty($cDecanulacionLugar)){
				$laTr['aCuerpo'][] = ['titulo2', 'Decanulación accidental'];
				$laTr['aCuerpo'][] = ['texto9',	'Lugar: ' .$cDecanulacionLugar];
			}

			if (!empty($this->aDocumento['cDecanulacionComplica'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Decanulación reporte complicaciones'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDecanulacionComplica']];
			}

			if (!empty($this->aDocumento['cDecanulacionFisio'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Concepto fisioterapeuta'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDecanulacionFisio']];
			}

			if (!empty($this->aDocumento['cDecanulacionFono'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Concepto fonoaudiologo'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDecanulacionFono']];
			}

			if (!empty($this->aDocumento['cDecanulacionMedico'])){
				$laTr['aCuerpo'][] = ['titulo2', 'Concepto medico'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDecanulacionMedico']];
			}
		}
		
		$laTr['aCuerpo'][]=['firmas', [
			['usuario' => trim($this->aDocumento['cUsuarioRealiza']),'prenombre'=>'FS. '],
		]];

		if ($taData['cCUP']=='939402'){
			$this->aReporte['cTitulo']='NEBULIZACION';
		}else{
			$this->aReporte['cTitulo']='TERAPIA RESPIRATORIA INTEGRAL';
		}
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'cPriorizacion'=> '',
			'cDescPriorizacion'=> '',
			'cTratamiento'=> '',
			'cEvaluacion'=> '',
			'cSoporteOxigeno'=> '',
			'cTipoOxigeno'=> '',
			'cOxigenoCnn'=> '',
			'cOxigenoVen'=> '',
			'cOxigenoOtr'=> '',
			'cAltoFlujoLpm'=> '',
			'cAltoFlujoPorcentaje'=> '',
			'cEstadoConciencia'=> '',
			'cRitmo'=> '',
			'cFrecCardiaca'=> '',
			'cFrecRespira'=> '',
			'cSisCardioPul'=> '',
			'cPatronResp'=> '',
			'cExpansion'=> '',
			'cRuidosResp1'=> '',
			'cRuidosRespD1'=> '',
			'cRuidosResp2'=> '',
			'cRuidosRespD2'=> '',
			'cRuidosResp3'=> '',
			'cRuidosRespD3'=> '',
			'cPatronTos'=> '',
			'cEsputo'=> '',
			'cDisnea'=> '',
			'cDificultadResp'=> '',
			'cTirajes'=> '',
			'cCianosis'=> '',
			'cAleteoNasal'=> '',
			'cPolipnea'=> '',
			'cUsoMuscAcc'=> '',
			'cVentilacionInvasiva'=> '',
			'cAeroConVentilacionMecanica'=> '',
			'cSolucionHipertonica'=> '',
			'cIloprost'=> '',
			'cSolucionIsotonica'=> '',
			'cColistina'=> '',
			'cSalbutamol'=> '',
			'cEpinefrina'=> '',
			'cBromuro'=> '',
			'cBudesonida'=> '',
			'cDornasa'=> '',
			'cTobramiana'=> '',
			'cDosificacionConVentilacion'=> '',
			'cOtrosConVentilacion'=> '',
			'cInhaloterapiaCon'=> '',
			'cSalbutamolInhaloCon'=> '',
			'cBromuroInhaloCon'=> '',
			'cBeclometasonaInhaloCon'=> '',
			'cDosificacionInhaloCon'=> '',
			'cOtrosInhaloCon'=> '',
			'cInhaloterapiaSin'=> '',
			'cSalbutamolInhaloSin'=> '',
			'cBromuroInhaloSin'=> '',
			'cBeclometasonaInhaloSin'=> '',
			'cDosificacionInhaloSin'=> '',
			'cOtrosInhaloSin'=> '',
			'cDrenajePostural'=> '',
			'cHigieneBronquial'=> '',
			'cTosAsistida'=> '',
			'cEstimuloExterno'=> '',
			'cPercusion'=> '',
			'cDrenajeAlveolar'=> '',
			'cVibracion'=> '',
			'cEjerciciosRespiratorios'=> '',
			'cManiobrasLento'=> '',
			'cFlutter'=> '',
			'cPostiaux'=> '',
			'cIncentivoRespiratorio'=> '',
			'cManiobrasFlujo'=> '',
			'cPresionPositiva'=> '',
			'cCumplioObjetivos'=> '',
			'cObjetivosSugerencias'=> '',
			'cReformulacion'=> '',
			'cTextoPandemia'=> '',
			'cVentilacionNoInvasiva'=> '',
			'cAeroSinVentilacionMecanica'=> '',
			'cSolucionHipertonicaSin'=> '',
			'cIloprostSin'=> '',
			'cSolucionIsotonicaSin'=> '',
			'cColistinaSin'=> '',
			'cSalbutamolSin'=> '',
			'cEpinefrinaSin'=> '',
			'cBromuroSin'=> '',
			'cBudesonidaSin'=> '',
			'cDornasaSin'=> '',
			'cTobramianaSin'=> '',
			'cDosificacionSinVentilacion'=> '',
			'cOtrosSinVentilacion'=> '',
			'cRegFisioterapeuta'=> '',
			'cDxPrincipal'=> '',
			'cDescripcionDxPrincipal'=> '',
			'cDxRelacionado'=> '',
			'cDescripcionDxRelacionado'=> '',
			'cIntervFisio'=> '',
			'cRecomendaciones'=> '',
			'cListaObjetivos'=> '',
			'cTraqueostomizado'=> '',
			'cSeguimientoPac' => '',
			'cSP_LavadoCanula' => '',
			'cSP_TraumaRaquimedural'=> '',
			'cSP_LesionSupratentorial'=> '',
			'cSP_IOTmas7dias'=> '',
			'cSP_ExtFallidaMas2'=> '',
			'cSangradoPostQx'=> '',
			'cInfeccion'=> '',
			'cTosCalidad'=> '',
			'cTosAparicion'=> '',
			'cSecreciones'=> '',
			'cSecrecionAspecto'=> '',
			'cGastrostomia'=> '',
			'dDecanulacion'=> '',
			'cDecanulacionSitio'=> '',
			'cDecanulacionLugar'=> '',
			'cDecanulacionComplica'=> '',
			'cDecanulacionFisio'=> '',
			'cDecanulacionFono'=> '',
			'cDecanulacionMedico'=> '',
			'cObsNoSeguimiento'=> '',
			'cObsLavado'=> '',
			'cUsuarioRealiza'=> '',
			];
	}

	
}
