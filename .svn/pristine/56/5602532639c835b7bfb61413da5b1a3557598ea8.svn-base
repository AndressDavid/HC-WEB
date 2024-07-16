<?php
namespace NUCLEO;

class Doc_Fisio_Ocupacional
{
	protected $oDb;
	protected $cTipoDocumento='';
	protected $lcTituloPatrones='';
	protected $aDocumento = [];
	protected $aListaParametros = [];
	protected $laListaPatrones = [];

 protected $aReporte = [
					'cTitulo' => 'VALORACIÓN TERAPIA OCUPACIONAL',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,'forma'=>'FIS011'],
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
		$laListaParametros = $this->oDb
			->select('TABCOD, TABDSC, TABTIP')
			->from('PRMTAB')
			->where('TABCOD', '<>', '')
			->in('TABTIP',['IOD','ECN','FFN','BRM','RMV','POA','FSN','NDN'])
			->getAll('array');
		foreach($laListaParametros as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aListaParametros[$laPar['TABTIP']][$laPar['TABCOD']] = $laPar['TABDSC'];
		}

		//	Lista objetivos
		$laListaObjetivos = $this->oDb
			->select('trim(CL3TMA) CODIGO, trim(DE2TMA) Descripcion')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FISIOTER')->where('CL1TMA', '=', 'OCUPA')->where('CL2TMA', '=', 'OBJETIVO')->where('CL3TMA', '<>', '')
			->orderBy ('DE2TMA')
			->getAll('array');
		$this->aListaObjetivos = $laListaObjetivos;

		// Lista Patrones
		$laListaPatrones = $this->oDb
			->select('INT(CL4TMA) ID,INT(CL3TMA) NPATRON, trim(DE1TMA) PATRON, trim(DE2TMA) TITULO')
			->from('TABMAE')
			->where('TIPTMA', '=', 'FISIOTER')
			->where('CL1TMA', '=', 'OCUPA')
			->where('CL2TMA', '=', 'PATRONES')
			->where('CL4TMA', '<>', '')
			->orderBy ('INT(CL3TMA), INT(CL4TMA)')
			->getAll('array');
		foreach($laListaPatrones as $laPatron){
			$laPatron = array_map('trim',$laPatron);
			$this->aListaPatrones[ $laPatron['NPATRON'] ][ $laPatron['ID'] ] = $laPatron['TITULO'];
		}
		//$this->aListaPatrones = $laListaPatrones;

		$laOcupacional = $this->oDb
			->select('INDTOC, SUITOC, CLITOC, DESTOC, USRTOC, PGMTOC, FECTOC, HORTOC')
			->from('FisOcu')
			->where([
				'INGTOC'=>$taData['nIngreso'],
				'CEVTOC'=>$taData['nConsecEvol'],
				'CCITOC'=>$taData['nConsecCita'],
			])
			->getAll('array');

		$laDocumento = $this->datosBlanco();

		foreach($laOcupacional as $laData) {
			switch (true) {

				case $laData['INDTOC']==1:
					$laDocumento['cDominancia'] = trim(substr($laData['DESTOC'],15,5));
					$laDocumento['cEstadoConciencia'] = trim(substr($laData['DESTOC'],20,4));
					$laDocumento['cCordmovsim'] = trim(substr($laData['DESTOC'],24,1));
					$laDocumento['cCordalternos'] = trim(substr($laData['DESTOC'],25,1));
					$laDocumento['cCorddisociados'] = trim(substr($laData['DESTOC'],26,1));
					$laDocumento['cCorddedonariz'] = trim(substr($laData['DESTOC'],27,1));
					$laDocumento['cCordoposiciondigital'] = trim(substr($laData['DESTOC'],28,1));
					$laDocumento['cCorddismetria'] = trim(substr($laData['DESTOC'],29,1));
					$laDocumento['cCordSuperiores'] = trim(substr($laData['DESTOC'],30,5));
					$laDocumento['cUsuarioRealiza'] = $laData['USRTOC'];
					break;

				case $laData['INDTOC']==2:
					$laDocumento['cTono'] = trim(substr($laData['DESTOC'],1,6));
					$laDocumento['cFuerzaMuscular'] = trim(substr($laData['DESTOC'],6,6));
					$laDocumento['cRangoMovimiento'] = trim(substr($laData['DESTOC'],11,6));
					$laDocumento['cEquiProtectivas'] = trim(substr($laData['DESTOC'],16,6));
					break;

				case $laData['INDTOC']==3:
					$laDocumento['cNosiente'] = trim(substr($laData['DESTOC'],0,1));
					$laDocumento['cSientenodiscrimina'] = trim(substr($laData['DESTOC'],1,1));
					$laDocumento['cDiscriminatexturasexternas'] = trim(substr($laData['DESTOC'],2,1));
					$laDocumento['cDiscriminatexturasinternas'] = trim(substr($laData['DESTOC'],3,1));
					$laDocumento['cTermicab'] = trim(substr($laData['DESTOC'],4,1));
					$laDocumento['cTermicaa'] = trim(substr($laData['DESTOC'],5,1));
					$laDocumento['cTermicama'] = trim(substr($laData['DESTOC'],6,1));
					$laDocumento['cTermicam'] = trim(substr($laData['DESTOC'],7,1));
					$laDocumento['cTermicapi'] = trim(substr($laData['DESTOC'],8,1));
					$laDocumento['cTermicap'] = trim(substr($laData['DESTOC'],9,1));
					$laDocumento['cTactob'] = trim(substr($laData['DESTOC'],10,1));
					$laDocumento['cTactoa'] = trim(substr($laData['DESTOC'],11,1));
					$laDocumento['cTactoma'] = trim(substr($laData['DESTOC'],12,1));
					$laDocumento['cTactom'] = trim(substr($laData['DESTOC'],13,1));
					$laDocumento['cTactopi'] = trim(substr($laData['DESTOC'],14,1));
					$laDocumento['cTactop'] = trim(substr($laData['DESTOC'],15,1));
					$laDocumento['cAlgicab'] = trim(substr($laData['DESTOC'],16,1));
					$laDocumento['cAlgicaa'] = trim(substr($laData['DESTOC'],17,1));
					$laDocumento['cAlgicama'] = trim(substr($laData['DESTOC'],18,1));
					$laDocumento['cAlgicam'] = trim(substr($laData['DESTOC'],19,1));
					$laDocumento['cAlgicapi'] = trim(substr($laData['DESTOC'],20,1));
					$laDocumento['cAlgicap'] = trim(substr($laData['DESTOC'],21,1));
					break;

				case $laData['INDTOC']==4:
					$laDocumento['cSentidoPosicion'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==5:
					$laDocumento['cActSegmentarias'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==6:
					$laDocumento['cTipoSensibilidad'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==7:
					$laDocumento['cLocalizacion'] = trim(substr($laData['DESTOC'],0,5));
					$laDocumento['cNominalizacion'] = trim(substr($laData['DESTOC'],5,5));
					$laDocumento['cRelpartescuerpo'] = trim(substr($laData['DESTOC'],10,5));
					$laDocumento['cArriba'] = trim(substr($laData['DESTOC'],15,1));
					$laDocumento['cAbajo'] = trim(substr($laData['DESTOC'],16,1));
					$laDocumento['cDetras'] = trim(substr($laData['DESTOC'],17,1));
					$laDocumento['cAllado'] = trim(substr($laData['DESTOC'],18,1));
					$laDocumento['cAdentro'] = trim(substr($laData['DESTOC'],19,1));
					$laDocumento['cAfuera'] = trim(substr($laData['DESTOC'],20,1));
					$laDocumento['cIdeomotora'] = trim(substr($laData['DESTOC'],21,5));
					$laDocumento['cIdeacional'] = trim(substr($laData['DESTOC'],26,5));
					$laDocumento['cConstructiva'] = trim(substr($laData['DESTOC'],31,5));
					$laDocumento['cVestido'] = trim(substr($laData['DESTOC'],36,5));
					$laDocumento['cHorizontal'] = trim(substr($laData['DESTOC'],41,1));
					$laDocumento['cVertical'] = trim(substr($laData['DESTOC'],42,1));
					$laDocumento['cDiagonal'] = trim(substr($laData['DESTOC'],43,1));
					$laDocumento['cCircular'] = trim(substr($laData['DESTOC'],44,1));
					$laDocumento['cCondoc'] = trim(substr($laData['DESTOC'],45,1));
					$laDocumento['cSindoc'] = trim(substr($laData['DESTOC'],46,1));
					$laDocumento['cHemianopsia'] = trim(substr($laData['DESTOC'],47,5));
					$laDocumento['cAgnosia'] = trim(substr($laData['DESTOC'],52,5));
					$laDocumento['cSinegligencia'] = trim(substr($laData['DESTOC'],57,5));
					$laDocumento['cNofija'] = trim(substr($laData['DESTOC'],62,1));
					break;

				case $laData['INDTOC']==8:
					$laDocumento['cAtencion'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==9:
					$laDocumento['cMemoria'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==10:
					$laDocumento['cComprension'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==12:
					$laDocumento['cJuicio'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==13:
					$laDocumento['cAnalisis'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==14:
					$laDocumento['cSeguimiento'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==15:
					$laDocumento['cAutoestima'] = trim(substr($laData['DESTOC'],0,5));
					$laDocumento['cAutoconcepto'] = trim(substr($laData['DESTOC'],5,5));
					$laDocumento['cAgresion'] = trim(substr($laData['DESTOC'],10,1));
					$laDocumento['cLlanto'] = trim(substr($laData['DESTOC'],11,1));
					$laDocumento['cIndiferencia'] = trim(substr($laData['DESTOC'],12,1));
					$laDocumento['cNegacion'] = trim(substr($laData['DESTOC'],13,1));
					$laDocumento['cVarios'] = trim(substr($laData['DESTOC'],14,1));
					break;

				case $laData['INDTOC']==16:
					$laDocumento['cSecolocasasco'] = trim(substr($laData['DESTOC'],0,1));
					$laDocumento['cSequitapantalon'] = trim(substr($laData['DESTOC'],1,1));
					$laDocumento['cSecolocamedias'] = trim(substr($laData['DESTOC'],2,1));
					$laDocumento['cDesabotonar'] = trim(substr($laData['DESTOC'],3,1));
					$laDocumento['cAmarra'] = trim(substr($laData['DESTOC'],4,1));
					$laDocumento['cSequitasaco'] = trim(substr($laData['DESTOC'],5,1));
					$laDocumento['cSecolocaropainterior'] = trim(substr($laData['DESTOC'],6,1));
					$laDocumento['cSequitamedias'] = trim(substr($laData['DESTOC'],7,1));
					$laDocumento['cAbotonar'] = trim(substr($laData['DESTOC'],8,1));
					$laDocumento['cSecolocapantalon'] = trim(substr($laData['DESTOC'],9,1));
					$laDocumento['cSequitaropainterior'] = trim(substr($laData['DESTOC'],10,1));
					$laDocumento['cSecolocazapatos'] = trim(substr($laData['DESTOC'],11,1));
					$laDocumento['cSubircremallera'] = trim(substr($laData['DESTOC'],12,1));
					$laDocumento['cManipulagrifo'] = trim(substr($laData['DESTOC'],13,1));
					$laDocumento['cSeseca'] = trim(substr($laData['DESTOC'],14,1));
					$laDocumento['cSeenjabona'] = trim(substr($laData['DESTOC'],15,1));
					$laDocumento['cSeccolocachampu'] = trim(substr($laData['DESTOC'],16,1));
					$laDocumento['cUsaesponja'] = trim(substr($laData['DESTOC'],17,1));
					$laDocumento['cControlesfinteres'] = trim(substr($laData['DESTOC'],18,1));
					$laDocumento['cManejacuchara'] = trim(substr($laData['DESTOC'],19,1));
					$laDocumento['cTomaliquidosvaso'] = trim(substr($laData['DESTOC'],20,1));
					$laDocumento['cCortaalimentos'] = trim(substr($laData['DESTOC'],21,1));
					$laDocumento['cManejatenedor'] = trim(substr($laData['DESTOC'],22,1));
					$laDocumento['cBañacara'] = trim(substr($laData['DESTOC'],23,1));
					$laDocumento['cSeafeita'] = trim(substr($laData['DESTOC'],24,1));
					$laDocumento['cCepilladientes'] = trim(substr($laData['DESTOC'],25,1));
					$laDocumento['cSemaquilla'] = trim(substr($laData['DESTOC'],26,1));
					$laDocumento['cPeinarse'] = trim(substr($laData['DESTOC'],27,1));
					$laDocumento['cPosicionbipeda'] = trim(substr($laData['DESTOC'],28,1));
					$laDocumento['cDesplazamientos'] = trim(substr($laData['DESTOC'],29,1));
					$laDocumento['cRolar'] = trim(substr($laData['DESTOC'],30,1));
					$laDocumento['cSedente'] = trim(substr($laData['DESTOC'],31,1));
					break;

				case $laData['INDTOC']==17:
					$laDocumento['cAnamnesisOcupacional'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==18:
					$laDocumento['cPatronesObs'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==19:
					$laDocumento['cTactoObs'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==20:
					$laDocumento['cProfundaObs'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==21:
					$laDocumento['cFactoresObs'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==22:
					$laDocumento['cActividadesObs'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==23:
					$laDocumento['cDiagnostico'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==24:
					$laDocumento['cIntervencion'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==25:
					$laDocumento['cRecomendacion'] .= $laData['DESTOC'];
					break;

				case $laData['INDTOC']==26	:
					$laDocumento['cPatronesContiene'] .= $laData['DESTOC'];
					$laDocumento['aPatrones'][ $laData['SUITOC'] ][ $laData['CLITOC'] ]=substr($laData['DESTOC'],0,10);
					break;

				case $laData['INDTOC']==27 && $laData['SUITOC']==1:
					$laDocumento['cCumplioObjetivos'] = explode("|",trim($laData['DESTOC']))[0];
					$laDocumento['cListaObjetivos'] = explode("|",trim($laData['DESTOC']))[1];
					break;

				case $laData['INDTOC']==27 && $laData['SUITOC']==2:
					$laDocumento['cObjetivosSugerencias'] .= $laData['DESTOC'];
					break;
					
				case $laData['INDTOC']==30:
					$laDocumento['cTextoPandemia'] .= $laData['DESTOC'];
					break;	
			}
		}

		$laKeyTrim=[
			'cDominancia','cEstadoConciencia','cFactoresObs','cAnamnesisOcupacional','cTactoObs',
			'cPatronesObs','cSentidoPosicion','cActSegmentarias','cTipoSensibilidad','cProfundaObs',
			'cLocalizacion','cNominalizacion','cRelpartescuerpo','cIdeomotora','cIdeacional',
			'cConstructiva','cVestido','cActividadesObs','cDiagnostico','cObjetivosSugerencias','cTextoPandemia',
			'cIntervencion','cRecomendacion','cCumplioObjetivos','cListaObjetivos','cPatronesContiene',
			'cAtencion','cMemoria','cComprension','cJuicio','cAnalisis','cSeguimiento',];
		foreach($laKeyTrim as $lcKey)
			$laDocumento[$lcKey] = trim($laDocumento[$lcKey]);

		if ( !empty($laDocumento['cCordmovsim']) || !empty($laDocumento['cCordalternos']) || !empty($laDocumento['cCorddisociados']) ){
			$laKeySiNo = ['cCordmovsim','cCordalternos','cCorddisociados','cCorddedonariz','cCordoposiciondigital','cCorddismetria'];
			foreach($laKeySiNo as $lcKey)
				$laDocumento[$lcKey] = empty($laDocumento[$lcKey]) ? '' : ($laDocumento[$lcKey]=='1' ? 'SI' : 'NO');
		}

		$laKeySiNo = [
			'cNosiente','cSientenodiscrimina','cDiscriminatexturasexternas','cDiscriminatexturasinternas',
			'cTermicab','cTermicaa','cTermicama','cTermicam','cTermicapi','cTermicap',
			'cTactob','cTactoa','cTactoma','cTactom','cTactopi','cTactop',
			'cAlgicab','cAlgicaa','cAlgicama','cAlgicam','cAlgicapi','cAlgicap', ];
		foreach($laKeySiNo as $lcKey)
			$laDocumento[$lcKey] = empty($laDocumento[$lcKey]) ? '' : ($laDocumento[$lcKey]=='1' ? 'SI' : 'NO');
			

		if (!empty($laDocumento['cArriba']) || !empty($laDocumento['cAbajo']) || !empty($laDocumento['cDetras']) ||
			!empty($laDocumento['cAllado']) || !empty($laDocumento['cAdentro']) || !empty($laDocumento['cAfuera']) ||
			!empty($laDocumento['cHorizontal']) || !empty($laDocumento['cVertical']) || !empty($laDocumento['cDiagonal']) ||
			!empty($laDocumento['cCircular']) || !empty($laDocumento['cCondoc']) || !empty($laDocumento['cSindoc'] || !empty($laDocumento['cNofija'])) )
		{
			$laKeySiNo = ['cArriba','cAbajo','cDetras','cAllado','cAdentro','cAfuera','cHorizontal','cVertical','cDiagonal','cCircular','cCondoc','cSindoc','cNofija'];
			foreach($laKeySiNo as $lcKey)
				$laDocumento[$lcKey] = empty($laDocumento[$lcKey]) ? '' : ($laDocumento[$lcKey]=='1' ? 'SI' : 'NO');
		}

		if (!empty($laDocumento['cAutoestima']) || !empty($laDocumento['cAutoconcepto']) || !empty($laDocumento['cAgresion']) ||
			!empty($laDocumento['cIndiferencia']) || !empty($laDocumento['cNegacion']) || !empty($laDocumento['cVarios']))
		{
			$laDocumento['cAutoestima'] = trim($laDocumento['cAutoestima']);
			$laDocumento['cAutoconcepto'] = trim($laDocumento['cAutoconcepto']);
			$laKeySiNo = ['cAgresion','cLlanto','cIndiferencia','cNegacion','cVarios'];
			foreach($laKeySiNo as $lcKey)
				$laDocumento[$lcKey] = empty($laDocumento[$lcKey]) ? '' : ($laDocumento[$lcKey]=='1' ? 'SI' : 'NO');
		}

		$laKeySiNo = [
			'cSecolocasasco','cSequitapantalon','cSecolocamedias','cDesabotonar','cAmarra',
			'cSequitasaco','cSecolocaropainterior','cSequitamedias','cAbotonar',
			'cSecolocapantalon','cSequitaropainterior','cSecolocazapatos','cSubircremallera',
			'cManipulagrifo','cSeseca','cSeenjabona','cSeccolocachampu','cUsaesponja',
			'cControlesfinteres','cManejacuchara','cTomaliquidosvaso','cCortaalimentos',
			'cManejatenedor','cBañacara','cSeafeita','cCepilladientes','cSemaquilla','cPeinarse',
			'cPosicionbipeda','cDesplazamientos','cRolar','cSedente', ];
		$lbMostrar = false;
		foreach($laKeySiNo as $lcKey)
			$lbMostrar = $lbMostrar || !empty($laDocumento[$lcKey]);
		if ($lbMostrar) {
			foreach($laKeySiNo as $lcKey)
				$laDocumento[$lcKey] = empty($laDocumento[$lcKey]) ? '' : ($laDocumento[$lcKey]=='1' ? 'SI' : 'NO');
		}

		$this->aDocumento = $laDocumento;
		unset($laDocumento);
	}


	private function prepararInforme($taData) {
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;

		$cCordSuperiores = empty($this->aDocumento['cCordSuperiores']) ? '' : $this->aListaParametros['POA'][$this->aDocumento['cCordSuperiores']];
		$cDominancia = empty($this->aDocumento['cDominancia']) ? '' : $this->aListaParametros['IOD'][$this->aDocumento['cDominancia']];
		$cEstadoConciencia = !empty($this->aDocumento['cEstadoConciencia'])? $this->aListaParametros['ECN'][$this->aDocumento['cEstadoConciencia']] :'';
		$cTono = !empty($this->aDocumento['cTono'])? $this->aListaParametros['FFN'][$this->aDocumento['cTono']] :'';
		$cFuerzaMuscular = !empty($this->aDocumento['cFuerzaMuscular'])? $this->aListaParametros['NDN'][$this->aDocumento['cFuerzaMuscular']] :'';
		$cRangoMovimiento = !empty($this->aDocumento['cRangoMovimiento'])? $this->aListaParametros['RMV'][$this->aDocumento['cRangoMovimiento']] :'';
		$cEquiProtectivas = !empty($this->aDocumento['cEquiProtectivas'])? $this->aListaParametros['POA'][$this->aDocumento['cEquiProtectivas']] :'';
		$cTipoSensibilidad = !empty($this->aDocumento['cTipoSensibilidad'])? $this->aListaParametros['FSN'][$this->aDocumento['cTipoSensibilidad']] :'';
		$cLocalizacion = !empty($this->aDocumento['cLocalizacion'])? $this->aListaParametros['POA'][$this->aDocumento['cLocalizacion']] :'';
		$cNominalizacion = !empty($this->aDocumento['cNominalizacion'])? $this->aListaParametros['POA'][$this->aDocumento['cNominalizacion']] :'';
		$cRelpartescuerpo = !empty($this->aDocumento['cRelpartescuerpo'])? $this->aListaParametros['POA'][$this->aDocumento['cRelpartescuerpo']] :'';
		$cIdeomotora = !empty($this->aDocumento['cIdeomotora'])? $this->aListaParametros['POA'][$this->aDocumento['cIdeomotora']] :'';
		$cIdeacional = !empty($this->aDocumento['cIdeacional'])? $this->aListaParametros['POA'][$this->aDocumento['cIdeacional']] :'';
		$cConstructiva = !empty($this->aDocumento['cConstructiva'])? $this->aListaParametros['POA'][$this->aDocumento['cConstructiva']] :'';
		$cVestido = !empty($this->aDocumento['cVestido'])? $this->aListaParametros['POA'][$this->aDocumento['cVestido']] :'';
		$cHemianopsia =!empty($this->aDocumento['cHemianopsia'])? $this->aListaParametros['POA'][$this->aDocumento['cHemianopsia']] :'';
		$cAgnosia =!empty($this->aDocumento['cAgnosia'])? $this->aListaParametros['POA'][$this->aDocumento['cAgnosia']] :'';
		$cSinegligencia =!empty($this->aDocumento['cSinegligencia'])? $this->aListaParametros['POA'][$this->aDocumento['cSinegligencia']] :'';
		$cAutoestima =!empty($this->aDocumento['cAutoestima'])? $this->aListaParametros['POA'][$this->aDocumento['cAutoestima']] :'';
		$cAutoconcepto =!empty($this->aDocumento['cAutoconcepto'])? $this->aListaParametros['POA'][$this->aDocumento['cAutoconcepto']] :'';

		if (!empty($this->aDocumento['cTextoPandemia'])){
			$laTr['aCuerpo'][] = ['titulo5', ' '];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cTextoPandemia']];
		}
		
		//	INICIO DATOS GENERALES
		$lbTitulo = true;
		if (!empty($this->aDocumento['cDominancia']) || !empty($this->aDocumento['cEstadoConciencia']) || !empty($this->aDocumento['cAnamnesisOcupacional']))
		{
			$lbTitulo = false;
			$laTr['aCuerpo'][] = ['titulo1', 'DATOS GENERALES'];
			if (!empty($this->aDocumento['cDominancia']) || !empty($this->aDocumento['cEstadoConciencia'])) {
				$laTr['aCuerpo'][] = ['tablaSL', [], [
					[ 'w'=>[70,90], 'd'=>['Dominancia: '.$cDominancia, 'Estado de conciencia: '.$cEstadoConciencia], ],
				]];
			}
			if (!empty($this->aDocumento['cAnamnesisOcupacional'])) {
				$laTr['aCuerpo'][] = ['titulo5', 'ANAMNESIS OCUPACIONAL'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cAnamnesisOcupacional']];
			}
		}

		//	CORDINACION DE MIEMBROS SUPERIORES
		$lbCoordMS = !empty($this->aDocumento['cCordmovsim']) || !empty($this->aDocumento['cCordalternos']) || !empty($this->aDocumento['cCorddisociados']);
		if ($lbCoordMS || !empty($cCordSuperiores) )
		{
			$laTr['aCuerpo'][] = ['titulo1', 'CORDINACIÓN DE MIEMBROS SUPERIORES'];
			if(!empty($cCordSuperiores))
				$laTr['aCuerpo'][] = ['texto9', 'Tipo: '.$cCordSuperiores];
			if($lbCoordMS){
				$laTr['aCuerpo'][] = ['tablaSL', [], [
					[ 'w'=>[53,11,39,11,32,11], 'a'=>['R','L','R','L','R','L',], 'd'=>[
						'Movimientos simultáneos', ' ['.$this->aDocumento['cCordmovsim'].']',
						'Alternos', ' ['.$this->aDocumento['cCordalternos'].']',
						'Disociados', ' ['.$this->aDocumento['cCorddisociados'].']',
					]],
					[ 'w'=>[53,11,39,11,32,11], 'a'=>['R','L','R','L','R','L',], 'd'=>[
						'Dedo nariz', ' ['.$this->aDocumento['cCorddedonariz'].']',
						'Oposición digital', ' ['.$this->aDocumento['cCordoposiciondigital'].']',
						'Dismetría', ' ['.$this->aDocumento['cCorddismetria'].']',
					]],
				]];
			}
		}

		//	FUNCIONALIDAD
		if (!empty($cTono) || !empty($cFuerzaMuscular) || !empty($cRangoMovimiento) || !empty($cEquiProtectivas)) {
			$laTr['aCuerpo'][] = ['titulo1', 'FUNCIONALIDAD'];
			$laTbl = [];
			if (!empty($cTono) || !empty($cFuerzaMuscular))
				$laTbl[] = [ 'w'=>[53,38,38,50], 'a'=>['R','L','R','L'], 'd'=>[ 'Tono:', $cTono, 'Fuerza muscular:', $cFuerzaMuscular, ]];
			if (!empty($cRangoMovimiento))
				$laTbl[] = [ 'w'=>[53,125], 'a'=>['R','L'], 'd'=>[ 'Rango de movimiento:', $cRangoMovimiento, ]];
			if (!empty($cEquiProtectivas))
				$laTbl[] = [ 'w'=>[53,125], 'a'=>['R','L'], 'd'=>[ 'Equi. y Reac. protectivas:', $cEquiProtectivas, ]];
			$laTr['aCuerpo'][] = ['tablaSL', [], $laTbl];

			if (!empty($this->aDocumento['cPatronesObs'])){
				$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIONES'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cPatronesObs']];	}
		}

		//	PATRONES
		if(!empty($this->aDocumento['cPatronesContiene'])){
			$laTr['aCuerpo'][] = ['titulo5', 'PATRONES'];
			$laTituloPatrones = [1=>'PATRONES FUNCIONALES', 2=>'PATRONES INTEGRALES'];

			for ($lnValor = 1; $lnValor<=2; $lnValor++) {
				if($lnValor>1) $laTr['aCuerpo'][] = ['saltol', 2]; // Espacio entre tablas
				$laTabla = [];
				$laWh = [50,28,28,28,28,28];
				$laAh = ['R','C','C','C','C','C'];
				$laW = [50,14,14,14,14,14,14,14,14,14,14];
				$laA = ['R','C','C','C','C','C','C','C','C','C','C'];
				foreach($this->aListaPatrones[$lnValor] AS $lnKey => $lcData) {
					$laTmp = [$lcData];
					for ($lnValor2 = 0; $lnValor2<10; $lnValor2++) {
						$laTmp[] = ($this->aDocumento['aPatrones'][$lnValor][$lnKey][$lnValor2] ?? '')== '1' ? 'X' : ' ';
					}
					$laTabla[] = ['w'=>$laW, 'a'=>$laA, 'd'=>$laTmp];
				}
				$laTr['aCuerpo'][] = ['tabla',
					[[ 'w'=>$laWh, 'a'=>$laAh, 'd'=>[ $laTituloPatrones[$lnValor], 'F', 'SF', 'FI', 'CNF', 'NF' ]],
					 [ 'w'=>$laW, 'a'=>$laA, 'd'=>[ 'Funcion', 'MSI', 'MSD', 'MSI', 'MSD', 'MSI', 'MSD', 'MSI', 'MSD', 'MSI', 'MSD' ]]
					], $laTabla ];
			}
		}

		//	SENSIBILIDAD SUPERFICIAL
		if (!empty($this->aDocumento['cNosiente']) || !empty($this->aDocumento['cSientenodiscrimina'])
			|| !empty($this->aDocumento['cDiscriminatexturasexternas']) || !empty($this->aDocumento['cDiscriminatexturasinternas']) )
		{
			$laTr['aCuerpo'][] = ['titulo1', 'SENSIBILIDAD SUPERFICIAL'];
			$laTr['aCuerpo'][] = ['titulo5', 'TACTO'];
			$laTr['aCuerpo'][] = ['tablaSL', [], [
				[ 'w'=>[9,75,9,75], 'd'=>[
					'['.$this->aDocumento['cNosiente'].']', 'No siente',
					'['.$this->aDocumento['cSientenodiscrimina'].']', 'Siente pero no discrimina' ] ],
				[ 'w'=>[9,75,9,75], 'd'=>[
					'['.$this->aDocumento['cDiscriminatexturasexternas'].']', 'Discrimina texturas externas',
					'['.$this->aDocumento['cDiscriminatexturasinternas'].']', 'Discrimina texturas intermedias' ] ],
			]];
		}

		if (!empty($this->aDocumento['cTermicab']) || !empty($this->aDocumento['cTermicaa']) || !empty($this->aDocumento['cTermicama']) ||
			!empty($this->aDocumento['cTermicam']) || !empty($this->aDocumento['cTermicapi']) || !empty($this->aDocumento['cTermicap']) ||
			!empty($this->aDocumento['cTactob']) || !empty($this->aDocumento['cTactoa']) || !empty($this->aDocumento['cTactoma']) ||
			!empty($this->aDocumento['cTactom']) || !empty($this->aDocumento['cTactopi']) || !empty($this->aDocumento['cTactop']) ||
			!empty($this->aDocumento['cAlgicab']) || !empty($this->aDocumento['cAlgicaa']) || !empty($this->aDocumento['cAlgicama']) ||
			!empty($this->aDocumento['cAlgicam']) || !empty($this->aDocumento['cAlgicapi']) || !empty($this->aDocumento['cAlgicap']) )
		{
			$laW = [35,11,25,21,21,21,27];
			$laA = ['R','C','C','C','C','C','C'];
			$laTr['aCuerpo'][] = ['tablaSL', [],
				[
					[ 'w'=>$laW, 'a'=>$laA, 'd'=>['','<b>BRAZO</b>','<b>ANTEBRAZO</b>','<b>MANO</b>','<b>MUSLO</b>','<b>PIERNA</b>','<b>PIE</b>' ] ],
					[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
						'Térmica',
						'['.$this->aDocumento['cTermicab'].']',
						'['.$this->aDocumento['cTermicaa'].']',
						'['.$this->aDocumento['cTermicama'].']',
						'['.$this->aDocumento['cTermicam'].']',
						'['.$this->aDocumento['cTermicapi'].']',
						'['.$this->aDocumento['cTermicap'].']',
					] ],
					[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
						'Tacto presión',
						'['.$this->aDocumento['cTactob'].']',
						'['.$this->aDocumento['cTactoa'].']',
						'['.$this->aDocumento['cTactoma'].']',
						'['.$this->aDocumento['cTactom'].']',
						'['.$this->aDocumento['cTactopi'].']',
						'['.$this->aDocumento['cTactop'].']',
					] ],
					[ 'w'=>$laW, 'a'=>$laA, 'd'=>[
						'Álgica o dolorosa',
						'['.$this->aDocumento['cAlgicab'].']',
						'['.$this->aDocumento['cAlgicaa'].']',
						'['.$this->aDocumento['cAlgicama'].']',
						'['.$this->aDocumento['cAlgicam'].']',
						'['.$this->aDocumento['cAlgicapi'].']',
						'['.$this->aDocumento['cAlgicap'].']',
					] ],
				] ];

			if (!empty($this->aDocumento['cTactoObs'])){
				$laTr['aCuerpo'][] = ['titulo5',	'OBSERVACIONES'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cTactoObs']];
			}
		}
		//	FIN SENSIBILIDAD SUPERFICIAL

		//	INICIO SENSIBILIDAD PROFUNDA
		if (!empty($this->aDocumento['cSentidoPosicion']) || !empty($this->aDocumento['cActSegmentarias'])
			|| !empty($this->aDocumento['cTipoSensibilidad']) || !empty($this->aDocumento['cProfundaObs']) )
		{
			$laTr['aCuerpo'][] = ['titulo1', 'SENSIBILIDAD PROFUNDA'];
			$laTr['aCuerpo'][] = ['tablaSL', [], [
				[ 'w'=>[45,3,142], 'a'=>['R','L','L'], 'd'=>['Tipo de sensibiliddad:', '', $cTipoSensibilidad] ],
				[ 'w'=>[45,3,142], 'a'=>['R','L','L'], 'd'=>['Actitudes segmentarias:', '', $this->aDocumento['cActSegmentarias']] ],
				[ 'w'=>[45,3,142], 'a'=>['R','L','L'], 'd'=>['Sentido de posición:', '', $this->aDocumento['cSentidoPosicion']] ],
			] ];
			if(!empty($this->aDocumento['cProfundaObs'])){
				$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIONES'];
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cProfundaObs']];
			}
		}
		//	FIN SENSIBILIDAD PROFUNDA

		//	INICIO FACTORES SENSORECEPTIVOS
		if (!empty($this->aDocumento['cLocalizacion']) || !empty($this->aDocumento['cNominalizacion']) || !empty($this->aDocumento['cRelpartescuerpo']) ||
			!empty($this->aDocumento['cArriba']) || !empty($this->aDocumento['cAbajo']) || !empty($this->aDocumento['cDetras']) ||
			!empty($this->aDocumento['cAllado']) || !empty($this->aDocumento['cAdentro']) || !empty($this->aDocumento['cAfuera']) ||
			!empty($this->aDocumento['cIdeomotora']) || !empty($this->aDocumento['cIdeacional']) || !empty($this->aDocumento['cConstructiva']) ||
			!empty($this->aDocumento['cVestido']) ||
			!empty($this->aDocumento['cHorizontal']) || !empty($this->aDocumento['cVertical']) || !empty($this->aDocumento['cDiagonal']) ||
			!empty($this->aDocumento['cCircular']) || !empty($this->aDocumento['cCondoc']) || !empty($this->aDocumento['cSindoc']) ||
			!empty($this->aDocumento['cHemianopsia']) || !empty($this->aDocumento['cAgnosia']) || !empty($this->aDocumento['cSinegligencia']) ||
			!empty($this->aDocumento['cNofija']) )
		{
			$laTr['aCuerpo'][] = ['titulo1', 'FACTORES SENSORECEPTIVOS'];

			if (!empty($this->aDocumento['cLocalizacion']) || !empty($this->aDocumento['cNominalizacion'])
				|| !empty($this->aDocumento['cRelpartescuerpo']))
			{
				$laTr['aCuerpo'][] = ['titulo5', 'IMAGEN CORPORAL'];
				$laTr['aCuerpo'][] = ['tablaSL', [], [
					[ 'w'=>[55,58,75], 'd'=>[
						'Localización: '.$cLocalizacion,
						'Nominalización: '.$cNominalizacion,
						'Rel. partes del cuerpo: '.$cRelpartescuerpo ] ]
				]];
			}

			if (!empty($this->aDocumento['cArriba'])  || !empty($this->aDocumento['cAbajo']) ||
				!empty($this->aDocumento['cDetras'])  || !empty($this->aDocumento['cAllado']) ||
				!empty($this->aDocumento['cAdentro']) || !empty($this->aDocumento['cAfuera']))
			{
				$laTr['aCuerpo'][] = ['titulo5', 'DÉFICIT ESPACIAL'];
				$laTr['aCuerpo'][] = ['tablaSL', [], [
					[ 'w'=>31, 'd'=>[
						'Arriba ['	.$this->aDocumento['cArriba'].']',
						'Abajo ['	.$this->aDocumento['cAbajo'].']',
						'Detras ['	.$this->aDocumento['cDetras'].']',
						'Al lado ['	.$this->aDocumento['cAllado'].']',
						'Adentro ['	.$this->aDocumento['cAdentro'].']',
						'Afuera ['	.$this->aDocumento['cAfuera'].']', ] ]
				]];
			}

			if (!empty($this->aDocumento['cIdeomotora']) || !empty($this->aDocumento['cIdeacional']) ||
				!empty($this->aDocumento['cConstructiva']) || !empty($this->aDocumento['cVestido']))
			{
				$laTr['aCuerpo'][] = ['titulo5', 'APRAXIA'];
				$laTr['aCuerpo'][] = ['tablaSL', [], [
					[ 'w'=>[30,50,30,50], 'a'=>['R','L','R','L'], 'd'=>[
						'Constructiva: ', ' '.$cConstructiva,
						'Ideacional: ', ' '.$cIdeacional ] ],
					[ 'w'=>[30,50,30,50], 'a'=>['R','L','R','L'], 'd'=>[
						'Ideo motora: ', ' '.$cIdeomotora,
						'Del vestido: ', ' '.$cVestido ] ],
				]];
			}

			if (!empty($this->aDocumento['cNofija']) || !empty($this->aDocumento['cHemianopsia']) ||
				!empty($this->aDocumento['cHorizontal']) || !empty($this->aDocumento['cVertical']) ||
				!empty($this->aDocumento['cDiagonal']) || !empty($this->aDocumento['cCircular']) ||
				!empty($this->aDocumento['cCondoc']) || !empty($this->aDocumento['cSindoc']) ||
				!empty($this->aDocumento['cAgnosia']) || !empty($this->aDocumento['cSinegligencia']) ||
				!empty($this->aDocumento['cFactoresObs']))
			{
				$laTr['aCuerpo'][] = ['titulo5', 'MANEJO VISUAL (Seguimiento)'];

				if (!empty($this->aDocumento['cHorizontal']) || !empty($this->aDocumento['cVertical']) ||
					!empty($this->aDocumento['cDiagonal']) || !empty($this->aDocumento['cCircular']) ||
					!empty($this->aDocumento['cCondoc']) || !empty($this->aDocumento['cSindoc'])) {

					$laTr['aCuerpo'][] = ['tablaSL', [], [
						[ 'w'=>33, 'd'=>[
							'Horizontal ['.$this->aDocumento['cHorizontal'].']',
							'Vertical ['.$this->aDocumento['cVertical'].']',
							'Diagonal ['.$this->aDocumento['cDiagonal'].']',
							'Circular ['.$this->aDocumento['cCircular'].']',
							'Con DOC ['.$this->aDocumento['cCondoc'].']',
							'Sin DOC ['.$this->aDocumento['cSindoc'].']', ] ]
					]];
					if(!empty($this->aDocumento['cNofija']))
						$laTr['aCuerpo'][] = ['texto9', 'No fija  ['.$this->aDocumento['cNofija'].']'];
				}

				if (!empty($this->aDocumento['cHemianopsia']) || !empty($this->aDocumento['cAgnosia']) || !empty($this->aDocumento['cSinegligencia'])) {
					$laTr['aCuerpo'][] = ['titulo5', 'MANEJO VISUAL'];

					if(!empty($this->aDocumento['cHemianopsia']))
						$laTr['aCuerpo'][] = ['texto9', 'Hemianopsia:  '.$cHemianopsia];

					if(!empty($this->aDocumento['cAgnosia']))
						$laTr['aCuerpo'][] = ['texto9', 'Agnosia:  '.$cAgnosia];

					if(!empty($this->aDocumento['cSinegligencia']))
						$laTr['aCuerpo'][] = ['texto9',	'Síndrome de Negligencia:  '.$cSinegligencia];
				}

				if(!empty($this->aDocumento['cFactoresObs'])){
					$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIONES'];
					$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cFactoresObs']];
				}
			}
		}

		//	COGNICIÓN
		if (!empty($this->aDocumento['cAtencion']) || !empty($this->aDocumento['cMemoria']) ||
			!empty($this->aDocumento['cComprension']) || !empty($this->aDocumento['cJuicio']) ||
			!empty($this->aDocumento['cAnalisis']) || !empty($this->aDocumento['cSeguimiento']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'COGNICIÓN'];

			if (!empty($this->aDocumento['cAtencion'])){
				$laTr['aCuerpo'][] = ['titulo5', 'ATENCIÓN'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cAtencion']];
			}
			if (!empty($this->aDocumento['cMemoria'])){
				$laTr['aCuerpo'][] = ['titulo5', 'MEMORIA'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cMemoria']];
			}
			if (!empty($this->aDocumento['cComprension'])){
				$laTr['aCuerpo'][] = ['titulo5', 'COMPRENSIÓN'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cComprension']];
			}
			if (!empty($this->aDocumento['cJuicio'])){
				$laTr['aCuerpo'][] = ['titulo5', 'JUICIO'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cJuicio']];
			}
			if (!empty($this->aDocumento['cAnalisis'])){
				$laTr['aCuerpo'][] = ['titulo5', 'ÁNALISIS'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cAnalisis']];
			}
			if (!empty($this->aDocumento['cSeguimiento'])){
				$laTr['aCuerpo'][] = ['titulo5', 'SEGUIMIENTO INSTRUCCIONAL'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cSeguimiento']];
			}
		}


		//	ASPECTOS PSICOLÓGICOS
		if (!empty($this->aDocumento['cAutoestima']) || !empty($this->aDocumento['cAutoconcepto']) ||
			!empty($this->aDocumento['cAgresion']) || !empty($this->aDocumento['cIndiferencia']) ||
			!empty($this->aDocumento['cNegacion']) || !empty($this->aDocumento['cVarios'])) {
			$laTr['aCuerpo'][] = ['titulo1', 'ASPECTOS PSICOLÓGICOS'];
			$laTr['aCuerpo'][] = ['tablaSL', [], [
				[ 'w'=>60, 'd'=>['Autoestima: '.$cAutoestima, 'Autoconcepto: '.$cAutoconcepto] ],
			]];
			$laTr['aCuerpo'][] = ['titulo5', 'MECANISMOS DE DEFENSA'];
			$laTr['aCuerpo'][] = ['tablaSL', [], [
				[ 'w'=>[32,48,38,35,38], 'd'=>[
					'Agresión ['.$this->aDocumento['cAgresion'].']',
					'Labilidad y llanto ['.$this->aDocumento['cLlanto'].']',
					'Indiferencia ['.$this->aDocumento['cIndiferencia'].']',
					'Negación ['.$this->aDocumento['cNegacion'].']',
					'Varios ['.$this->aDocumento['cVarios'].']',
				] ],
			] ];
		}


		//	ACTIVIDADES BASICAS COTIDIANAS
		if (!empty($this->aDocumento['cSecolocasasco']) || !empty($this->aDocumento['cSequitapantalon']) || !empty($this->aDocumento['cSecolocamedias']) ||
			!empty($this->aDocumento['cManipulagrifo']) || !empty($this->aDocumento['cBañacara']) || !empty($this->aDocumento['cSubircremallera']) ||
			!empty($this->aDocumento['cActividadesObs'])
			) {
			$laTr['aCuerpo'][] = ['titulo1', 'ACTIVIDADES BASICAS COTIDIANAS'];
			$laW = [63,63,63];
			$laTr['aCuerpo'][] = ['tablaSL', [], [
				[ 'w'=>$laW, 'd'=>['<b>VESTIDO</b>', '<b>HIGIENE MAYOR</b>', '<b>HIGIENE MENOR</b>', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSecolocasasco'].'] Se coloca saco',
					'['.$this->aDocumento['cManipulagrifo'].'] Manipula grifos de agua',
					'['.$this->aDocumento['cBañacara'].'] Se baña la cara', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSequitapantalon'].'] Se quita pantalón',
					'['.$this->aDocumento['cSeseca'].'] Se seca',
					'['.$this->aDocumento['cSeafeita'].'] Se afeita', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSecolocamedias'].'] Se coloca medias',
					'['.$this->aDocumento['cSeenjabona'].'] Se enjabona',
					'['.$this->aDocumento['cCepilladientes'].'] Cepilla los dientes', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cDesabotonar'].'] Desabotonar',
					'['.$this->aDocumento['cSeccolocachampu'].'] Se coloca champú',
					'['.$this->aDocumento['cSemaquilla'].'] Se maquilla', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cAmarra'].'] Amarrar',
					'['.$this->aDocumento['cUsaesponja'].'] Usa esponja',
					'['.$this->aDocumento['cPeinarse'].'] Peinarse', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSequitasaco'].'] Se quita el saco',
					'['.$this->aDocumento['cControlesfinteres'].'] Control de esfínteres', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSecolocaropainterior'].'] Se coloca ropa interior', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSequitamedias'].'] Se quita medias',
					'<b>ALIMENTACION</b>',
					'<b>TRASLADOS</b>', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cAbotonar'].'] Abotonar',
					'['.$this->aDocumento['cManejacuchara'].'] Maneja cuchara',
					'['.$this->aDocumento['cPosicionbipeda'].'] Posición bípeda', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSecolocapantalon'].'] Se coloca pantalón',
					'['.$this->aDocumento['cTomaliquidosvaso'].'] Toma alim. liq. en vaso',
					'['.$this->aDocumento['cDesplazamientos'].'] Desplazamientos', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSequitaropainterior'].'] Se quita ropa interior',
					'['.$this->aDocumento['cCortaalimentos'].'] Corta alimentos',
					'['.$this->aDocumento['cRolar'].'] Rolar', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSecolocazapatos'].'] Se coloca zapatos',
					'['.$this->aDocumento['cManejatenedor'].'] Maneja tenedor',
					'['.$this->aDocumento['cSedente'].'] Sedente', ] ],
				[ 'w'=>$laW, 'd'=>[
					'['.$this->aDocumento['cSubircremallera'].'] Subir y bajar cremallera' ] ],
			]];

			if (!empty($this->aDocumento['cActividadesObs'])){
				$laTr['aCuerpo'][] = ['titulo5', 'OBSERVACIONES'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cActividadesObs']];
			}
		}


		if (!empty($this->aDocumento['cCumplioObjetivos']) || !empty($this->aDocumento['cListaObjetivos']))
		{
			$laTr['aCuerpo'][] = ['titulo1', 'OBJETIVOS FISIOTERAPIA'];
			if (!empty($this->aDocumento['cListaObjetivos']))
			{
				$laTr['aCuerpo'][] = ['titulo5', 'Objetivos de intervencion'];

				foreach($this->aListaObjetivos as $laDataObj) {
					if (strstr($this->aDocumento['cListaObjetivos'], $laDataObj['CODIGO'])){
						$laTr['aCuerpo'][] = ['texto9', '* ' .$laDataObj['DESCRIPCION']];
					}
				}
			}

			if (!empty($this->aDocumento['cCumplioObjetivos'])) {
				$laTr['aCuerpo'][] = ['titulo5', 'Cumplio con objetivos de tratamiento?'];
				$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cCumplioObjetivos']=='S'? 'SI': 'NO'];
			}

			if (!empty($this->aDocumento['cObjetivosSugerencias'])){
				$laTr['aCuerpo'][] = ['titulo5', 'Sugerencias'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cObjetivosSugerencias']];
			}
		}

		if (!empty($this->aDocumento['cDiagnostico'])){
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNÓSTICO OCUPACIONAL'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDiagnostico']];
			}

		if (!empty($this->aDocumento['cIntervencion'])){
			$laTr['aCuerpo'][] = ['titulo1', 'INTERVENCIÓN'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cIntervencion']];
		}

		if (!empty($this->aDocumento['cRecomendacion'])){
			$laTr['aCuerpo'][] = ['titulo1', 'RECOMENDACIÓN'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cRecomendacion']];
		}

		$laTr['aCuerpo'][]=['firmas', [
			['usuario' => $this->aDocumento['cUsuarioRealiza'],'prenombre'=>'TO. '],
		]];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'cDominancia'=> '',
			'cEstadoConciencia'=> '',
			'cCordSuperiores'=>'',
			'cCordmovsim'=> '',
			'cCordalternos'=> '',
			'cCorddisociados'=> '',
			'cCorddedonariz'=> '',
			'cCordoposiciondigital'=> '',
			'cCorddismetria'=> '',
			'cAnamnesisOcupacional'=> '',
			'cPatronesObs'=> '',
			'cTono'=> '',
			'cFuerzaMuscular'=> '',
			'cRangoMovimiento'=> '',
			'cEquiProtectivas'=> '',
			'cNosiente'=> '',
			'cSientenodiscrimina'=> '',
			'cDiscriminatexturasexternas'=> '',
			'cDiscriminatexturasinternas'=> '',
			'cTermicab'=> '',
			'cTermicaa'=> '',
			'cTermicama'=> '',
			'cTermicam'=> '',
			'cTermicapi'=> '',
			'cTermicap'=> '',
			'cTactob'=> '',
			'cTactoa'=> '',
			'cTactoma'=> '',
			'cTactom'=> '',
			'cTactopi'=> '',
			'cTactop'=> '',
			'cAlgicab'=> '',
			'cAlgicaa'=> '',
			'cAlgicama'=> '',
			'cAlgicam'=> '',
			'cAlgicapi'=> '',
			'cAlgicap'=> '',
			'cTactoObs'=> '',
			'cSentidoPosicion'=> '',
			'cActSegmentarias'=> '',
			'cTipoSensibilidad'=> '',
			'cProfundaObs'=> '',
			'cLocalizacion'=> '',
			'cNominalizacion'=> '',
			'cRelpartescuerpo'=> '',
			'cArriba'=> '',
			'cAbajo'=> '',
			'cDetras'=> '',
			'cAllado'=> '',
			'cAdentro'=> '',
			'cAfuera'=> '',
			'cIdeomotora'=> '',
			'cIdeacional'=> '',
			'cConstructiva'=> '',
			'cVestido'=> '',
			'cHorizontal'=> '',
			'cVertical'=> '',
			'cDiagonal'=> '',
			'cCircular'=> '',
			'cCondoc'=> '',
			'cSindoc'=> '',
			'cHemianopsia'=> '',
			'cAgnosia'=> '',
			'cSinegligencia'=> '',
			'cNofija'=> '',
			'cAtencion'=> '',
			'cMemoria'=> '',
			'cComprension'=> '',
			'cJuicio'=> '',
			'cAnalisis'=> '',
			'cSeguimiento'=> '',
			'cAutoestima'=> '',
			'cAutoconcepto'=> '',
			'cAgresion'=> '',
			'cLlanto'=> '',
			'cIndiferencia'=> '',
			'cNegacion'=> '',
			'cVarios'=> '',
			'cSecolocasasco'=> '',
			'cSequitapantalon'=> '',
			'cSecolocamedias'=> '',
			'cDesabotonar'=> '',
			'cAmarra'=> '',
			'cSequitasaco'=> '',
			'cSecolocaropainterior'=> '',
			'cSequitamedias'=> '',
			'cAbotonar'=> '',
			'cSecolocapantalon'=> '',
			'cSequitaropainterior'=> '',
			'cSecolocazapatos'=> '',
			'cSubircremallera'=> '',
			'cManipulagrifo'=> '',
			'cSeseca'=> '',
			'cSeenjabona'=> '',
			'cSeccolocachampu'=> '',
			'cUsaesponja'=> '',
			'cControlesfinteres'=> '',
			'cManejacuchara'=> '',
			'cTomaliquidosvaso'=> '',
			'cCortaalimentos'=> '',
			'cManejatenedor'=> '',
			'cBañacara'=> '',
			'cSeafeita'=> '',
			'cCepilladientes'=> '',
			'cSemaquilla'=> '',
			'cPeinarse'=> '',
			'cPosicionbipeda'=> '',
			'cDesplazamientos'=> '',
			'cRolar'=> '',
			'cSedente'=> '',
			'cActividadesObs'=> '',
			'cIntervencion'=> '',	// Intervencion
			'cDiagnostico'=> '',	// Diagnostico
			'cRecomendacion'=> '',		// Recomendacion
			'cUsuarioRealiza'=> '',		// Usuario realiza
			'cObjetivos'=> '',
			'cFactoresObs'=> '',
			'cCumplioObjetivos'=> '',
			'cObjetivosSugerencias'=> '',
			'cTextoPandemia'=> '',
			'cListaObjetivos'=> '',
			'cPatronesContiene'=> '',
			];
	}

}
