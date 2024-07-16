<?php
namespace NUCLEO;

require_once ('class.Doc_NotasAclaratorias.php');
use NUCLEO\Doc_NotasAclaratorias;

class Doc_Fisioterapia
{
	protected $oDb;
	protected $aDatos = [];
	protected $aFisio = [];
	protected $aAuscultacion = [];
	protected $aDermatomas = [];
	protected $aObjetivos = [];
	protected $aIndices = [];
	protected $aPrmTab = [];
	protected $aCodAnt = [];
	protected $aAntecedentes = [];
	protected $aDiagnosticos = [];
	protected $aDominios = [];
	protected $aFacial = [];
	protected $aRefleja = [];
	protected $cObjetivosSugerencias = '';
	protected $cCumplioObjetivos = '';
	protected $aValoracion = [];
	protected $cObservacionesGenerales = '';
	protected $cObsMusculoEsqueleticos = '';
	protected $cLenguajeComunicaciones = '';
	protected $cObsSistemaIntegumentario = '';
	protected $cActitudColaboracion = '';
	protected $cAntecedentes = '';
	protected $cTipoCirugia = '';
	protected $cDolorLocalizacion = '';
	protected $cDolorFrecuencia = '';
	protected $cObservacionesDolor = '';
	protected $cObsValoracionAvd = '';
	protected $cObsValoracionAvid = '';
	protected $cObsCaminata = '';
	protected $cUsuarioRealiza = '';
	protected $cCaminataTa = '';
	protected $cSugerencias = '';
	protected $cDatosObjetivos = '';
	protected $cDatosDiagnosticos = '';
	protected $cObsIntervencion = '';
	protected $cObsRecomendaciones = '';
	protected $cObsPropiocepcion = '';
	protected $cObservacionesMarcha = '';
	protected $cObservacionesColorPiel = '';
	protected $cObsEspasmo = '';
	protected $cFlexibilidadControlMotor = '';
	protected $cPosturalControlMotor = '';
	protected $cDermatomasSeleccionados = '';
	protected $cDatosMovilidadArticular = '';
	protected $cDatosMovilidadMuscular = '';
	protected $cDatosValoracionCaraTronco = '';
	protected $cTextoPandemia = '';
	protected $lnContieneBariatrica=0;
	protected $lnContieneDesacondicionamiento=0;
	protected $lnContieneLesionesMusculoEsq=0;
	protected $lnContieneIntegumentario=0;
	protected $cSL = "\n"; //PHP_EOL;

	protected $aReporte = [
					'cTitulo' => 'VALORACION FISIOTERAPEUTICA',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false,],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
	}

	// Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->consultarDatosGenerales();
		$laVal = [
			1 => 'Neurologico',
			2 => 'Bariatrico',
			3 => 'MusculoEsq',
			4 => 'ParalisisFacial',
			5 => 'Vertigo',
			6 => 'Desacondicionamiento',
			7 => 'SistIntegumentario',
			8 => 'EstimulaAdecuada',
		];

		$lnIndice = intval($taData['nConsecDoc']);
		if (empty($lnIndice)) {
			// ciclo para cada uno de los reportes
			foreach ($laVal as $lnVal=>$lcVal) {
				$this->consultarDatos($lnVal, $taData);
				if(is_array($this->aValoracion)){
					if(count($this->aValoracion)>0){
						$lcFuncion = 'fnOrganizarDatos'.$lcVal;
						$this->$lcFuncion($taData);
						$lcFuncion = 'prepararInforme'.$lcVal;
						$this->$lcFuncion($taData);
					}
				}
			}

		} else {
			$this->consultarDatos($lnIndice, $taData);
			if(is_array($this->aValoracion)){
				if(count($this->aValoracion)>0){
					$lcFuncion = 'fnOrganizarDatos'.$laVal[$lnIndice];
					$this->$lcFuncion($taData);
					$lcFuncion = 'prepararInforme'.$laVal[$lnIndice];
					$this->$lcFuncion($taData);
				}
			}
		}

 		return $this->aReporte;
	}

	private function consultarDatosGenerales()
	{
		$laListaDiagnostico = $this->oDb
			->select('SISDOM TIPO_SISTEMA, trim(B.TABDSC) AS DESCRIPCION_TIPO, trim(A.CONDOM) CODIGO, trim(LITDOM) LITERAL, trim(A.DESDOM) DESCRIPCION_ID')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', "A.SISDOM=B.TABCOD", null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 1)
			->where('B.TABTIP', '=', 'DFI')
			->where('B.TABCOD', '<>', '')
			->orderBy ('A.SISDOM, A.CONDOM')
			->getAll('array');
		foreach ($laListaDiagnostico as $laDiagnosticoTabla){
			$this->aTituloDiagnostico[$laDiagnosticoTabla['CODIGO']] = $laDiagnosticoTabla['DESCRIPCION_TIPO'];
			$this->aListaDiagnostico[$laDiagnosticoTabla['CODIGO']] = $laDiagnosticoTabla['LITERAL'] .'. ' .$laDiagnosticoTabla['DESCRIPCION_ID'];
		}

		// IMC
		$this->aConsultaImc = $this->oDb
			->select('ConDom Id, TRIM(SUBSTR(DesDom, 1, 50)) Clasificacion, MinDom IMCinf, MaxDom IMCsup')
			->from('DOMFISL01 AS A')
			->where('IndDom', '=', 2)
			->getAll('array');

		// Array de parámetros
		$this->aPrmTab = $this->oDb
			->select("TABCOD, TABDSC, TABTIP, TRIM(TABTIP)||TRIM(TABCOD) AS CODIGO")
			->from('PRMTAB02')
			->where('TABCOD','<>','')
			->in('TABTIP', ['AFE','AMV','ANA','ART','ATR','AUP','BRM','CAU','CNV','CTZ','DFI','DOL',
						'ECN','EDE','FFN','FOG','FOH','FSB','FSN','FSP','HAH','IOD',
						'LNP','LTR','MQR','MRA','MRC','NDN','NQQ','OOD','POA','PRF',
						'PRS','RMV','ROI','SIM','SIN','SON','TCF','TPD','TPR','GCX','DLC','DFC'])
			->getAll('array');
		foreach($this->aPrmTab as $laPar) {
			$laPar = array_map('trim', $laPar);
			$this->aListaParametros[$laPar['TABTIP']][$laPar['TABCOD']] = $laPar['TABDSC'];
		}

		// Array codigo de antecedentes
		$this->aCodAnt = $this->oDb
			->select("TRIM(DESAND) AS DESANT, INT(IN2AND) AS CODANT, TRIM(FILAND) AS FILANT")
			->from('ANTDESL01')
			->where(['IN1AND'=>'15',
					])
			->orderBy('INT(IN2AND)')
			->getAll('array');
		foreach ($this->aCodAnt as $laAntec){
			$this->aTipoAntecedentes[$laAntec['CODANT']] = $laAntec['DESANT'];
		}

		// Array dominios
		$this->aDominios = $this->oDb
			->select('A.CONDOM AS ID, A.LITDOM AS LITERAL, B.TABDSC AS SISTEMA, A.DESDOM AS DOMINIO')
			->from('DOMFISL01 AS A')
			->leftJoin('PRMTAB AS B', 'CHAR(A.SISDOM)=B.TABCOD', null)
			->where([
				'B.TABTIP'=>'DFI',
				'A.INDDOM'=>'1',
			])
			->where('B.TABCOD','<>','')
			->getAll('array');

		// Consulta Dermatomas
		$this->aDermatomas = $this->oDb
			->select('CONDOM AS ID, LITDOM AS LITERAL, IN2DOM AS ID2,
					 DESDOM AS DESCRIPCION, 0 AS USAR, TRIM(LITDOM)||TRIM(IN2DOM) AS ITEM')
			->from('DOMFISL01')
			->where(['INDDOM'=>'6'])
			->orderBy('LITDOM,IN2DOM')
			->getAll('array');

		// Consulta Paralisis Facial
		$this->aFacial = $this->oDb
			->select('A.CONDOM AS ID, B.TABDSC AS GRUPO, A.DESDOM AS ITEM,
					  0 AS P1, 0 AS P2, 0 AS P3, 0 AS P4, 0 AS P5, 0 AS P6,
					  0 AS TP1, 0 AS TP2, 0 AS USAR')
			->from('DOMFISL01 AS A')
			->leftJoin('PRMTAB AS B', 'CHAR(A.SISDOM)=B.TABCOD', null)
			->where([
				'B.TABTIP'=>'TCF',
				'A.INDDOM'=>'5',
			])
			->where('B.TABCOD','<>','')
			->getAll('array');

		// Consulta Reflejos NIño
		$this->aRefleja = $this->oDb
			->select('CONDOM AS ID, DESDOM AS REFLEJO, MAXDOM AS MAX, MINDOM AS MIN,
					 SPACE(220) AS EDAD, 0 AS P1, 0 AS P2, 0 AS TP1, 0 AS TP2, 0 AS USAR')
			->from('DOMFISL01')
			->where(['INDDOM'=>'7',
			])
			->getAll('array');

		//. DERMATOMAS
		$laListaDermatomas = $this->oDb
		->select('CONDOM CONSECUTIVO, TRIM(LITDOM)||\'\'||TRIM(IN2DOM) LITERAL')
		->from('DomFisL01')
		->where(['INDDOM'=>'6'])
		->getAll('array');
 		foreach ($laListaDermatomas as $laDermatoma){
			$this->aListaDermatomas[$laDermatoma['CONSECUTIVO']] = $laDermatoma['LITERAL'];
		}

		$this->aListaValoracionArtirular = $this->oDb
			->select('trim(A.CONDOM) ID, trim(B.TABDSC) AS ARTICULACION, trim(A.DESDOM) DESCRIPCION, INT(MINDOM) MIN, INT(MAXDOM) MAX')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', "A.SISDOM=B.TABCOD", null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 3)
			->where('B.TABTIP', '=', 'ART')
			->where('B.TABCOD', '<>', '')
			->orderBy ('B.TABDSC, A.CONDOM')
			->getAll('array');

		$this->aListaValoracionMuscular = $this->oDb
			->select('trim(A.CONDOM) ID, trim(B.TABDSC) AS ARTICULACION, trim(A.DESDOM) DESCRIPCION')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', "A.SISDOM=B.TABCOD", null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 4)
			->where('B.TABTIP', '=', 'MQR')
			->where('B.TABCOD', '<>', '')
			->orderBy ('B.TABDSC, A.CONDOM')
			->getAll('array');

		$this->aListaValoracionCaraTronco = $this->oDb
			->select('trim(A.CONDOM) ID, trim(B.TABDSC) AS ARTICULACION, trim(A.DESDOM) DESCRIPCION')
			->from('DOMFIS AS A')
			->leftJoin('PrmTab AS B', "A.SISDOM=B.TABCOD", null)
			->where('A.CONDOM', '<>', 0)
			->where('A.INDDOM', '=', 5)
			->where('B.TABTIP', '=', 'TCF')
			->where('B.TABCOD', '<>', '')
			->orderBy ('B.TABDSC, A.CONDOM')
			->getAll('array');

	}

	private function consultarDatos($tnTipoVal, $taData)
	{
		$this->aIndices = $this->fnCrearClaves($tnTipoVal);

		// Consulta Datos $tnIndice1=>Neurologico $tnIndice=>4 Paralisis Facial $tnIndice=>5 Vértigo $tnIndice=>8 Estimulación Adecuada
		$this->aValoracion = $this->oDb
			->select('A.*, IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS USUARIO, B.TPMRGM AS TIPO')
			->from('FISFISL01 AS A')
			->leftJoin('RIARGMN AS B', 'A.USRTFI=B.USUARI', null)
			->where([
				'A.INGTFI'=>$taData['nIngreso'],
				'A.CEVTFI'=>$taData['nConsecEvol'],
				'A.CCITFI'=>$taData['nConsecCita'],
				'A.IN2TFI'=>$tnTipoVal,
			])
			->orderBy ('A.IN2TFI')
			->getAll('array');

		// Consulta Objetivos
		$this->aObjetivos = $this->oDb
			->select('0 AS SELECCION, \'8\' AS TIPO, trim(CL3TMA) AS CODIGO, DE2TMA AS DESCRIPCION')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'FISIOTER',
				'CL1TMA'=>'FISICA',
				'CL2TMA'=>'OBJETIVO',
			])
			->where('CL3TMA', '<>', '')
			->orderBy('DE2TMA')
			->getAll('array');
		foreach ($this->aObjetivos as $laObjetivos){
			$this->aListaObjetivos[$laObjetivos['CODIGO']] = trim($laObjetivos['DESCRIPCION']);
		}
	}

	private function fnOrganizarDatosNeurologico($taData)
	{
		$lcDermatomas = $lcAntecedentes = $lcObjetivos = $lcDiagnosticos = $lcCumplioObjetivos = $lcUsuario = '' ;
		$lnKey = 0 ;
		$this->aDatos = [];

		foreach($this->aValoracion as $laNeurol)
		{
			$lcUsuario = trim($laNeurol['USRTFI']);

			switch (true)
			{
				case $laNeurol['INDTFI']=='1':
					$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,N¤09,4,N¤10,4,N¤11,4,N¤'.
							   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤20,4,N¤21,4,N¤22,4,N¤'.
							   '23,4,N¤24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤29,4,N¤30,4,N¤31,4,N¤'.
							   '33,4,N¤34,4,N¤36,4,N¤37,4,N¤38,4,N¤39,4,N¤40,4,N¤41,4,N¤42,4,N¤'.
							   '43,4,N¤46,4,N¤47,4,N¤48,4,N¤49,4,N¤51,4,N¤52,4,N¤64,4,N¤65,4,N¤'.
							   '66,4,N¤68,4,N¤69,4,N¤70,4,N¤71,4,N¤74,4,N¤75,4,N¤78,4,N¤79,4,N' ;
					$this->fnSepararPropiedades($lcTexto,$laNeurol['DESTFI'],false);
					break;

				case $laNeurol['INDTFI']=='17':

					$lcDiagnosticos.= $laNeurol['DESTFI'] ;
					break;

				case $laNeurol['INDTFI']=='18':

					$lcDermatomas.= $laNeurol['DESTFI'] ;
					break;

				case $laNeurol['INDTFI']=='20':

					$lcAntecedentes.= $laNeurol['DESTFI'];
					break;

				case $laNeurol['INDTFI']=='24':

					$lcTexto = '74,4,N¤75,4,N¤78,4,N¤79,4,N' ;
					$this->fnSepararPropiedades($lcTexto,$laNeurol['DESTFI'],false);
					break;

				case $laNeurol['INDTFI']=='25':

					$lcObjetivos.= $laNeurol['DESTFI'] ;
					break;

				case $laNeurol['INDTFI']=='26':

					$lcCumplioObjetivos.= $laNeurol['DESTFI'] ;
					break;

				case $laNeurol['INDTFI']=='27':

					$lcOtrosObjetivos.= $laNeurol['DESTFI'] ;
					break;

				case $laNeurol['INDTFI']=='31':
					$this->cTextoPandemia.= $laNeurol['DESTFI'] ;
					break;

				case in_array($laNeurol['INDTFI'], [2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,19,21,22,23]) :

					$laKey = [   2 =>  4,  3 =>  7,  4 => 15,  5 => 16,  6 => 32,  7 => 35,  8 => 44,  9 => 45, 10 => 50,
								11 => 53, 12 => 54, 13 => 55, 14 => 56, 15 => 57, 16 => 58, 19 => 62, 21 => 63, 22 => 67, 23 => 72,  ];
					$lnKey = $laKey[ $laNeurol['INDTFI'] ] ?? 0;
					$this->fnUnirMultilinea($lnKey, $laNeurol['DESTFI'], $laNeurol['INDTFI'],false);
					break;
			}
		}

		$lcTemp = str_pad($this->aDatos[11], 4, "0", STR_PAD_LEFT);
		$this->aAuscultacion[1] = intval(substr($lcTemp,0,2));
		$this->aAuscultacion[2] = intval(substr($lcTemp,2,2));

		// cargar array dermatomas
		$this->fnCargarDermatomas(trim($lcDermatomas));

		// cargar array objetivos
		$this->fnCargarObjetivos(trim($lcObjetivos));
		if(!empty(trim($lcCumplioObjetivos))){
			$laWordsItm = explode('~', $lcCumplioObjetivos);
			$this->cCumplioObjetivos = $laWordsItm[0]=='N'?'NO':'SI';
			$this->cObjetivosSugerencias = $laWordsItm[1]??'';
		}

		// cargar array Antecedentes
		$this->fnCargarAntecedentes(trim($lcAntecedentes));

		// cargar array Diagnosticos
		$this->fnCargarDiagnosticos(trim($lcDiagnosticos));

		// Cargar Médico
		$this->aDatos[97]= trim($lcUsuario) ;

	}

	private function fnOrganizarDatosBariatrico()
	{
		$this->datosBlanco();

		if (is_array($this->aValoracion))
		{
			foreach($this->aValoracion as $laBariatri)
			{
				$lcIndice = intval($laBariatri['INDTFI']);

				switch (true)
				{

					case $lcIndice==1:
						$this->lnContieneBariatrica = 1;
						$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,C¤09,4,N¤10,4,N¤11,4,N¤'.
								   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤20,4,N¤22,4,N¤'.
								   '23,4,N¤24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤29,4,N¤30,4,N¤32,4,N¤'.
								   '33,4,N¤34,4,N¤35,4,N¤36,4,N¤37,4,N¤38,4,N¤39,4,N¤40,4,N¤42,4,N¤'.
								   '43,4,N¤45,4,N¤46,4,N¤47,4,N¤48,4,N¤49,4,N¤50,4,N¤51,4,N¤52,4,N¤53,4,N¤54,4,N¤55,4,N¤'.
								   '56,4,N¤57,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laBariatri['DESTFI']);
						$this->cUsuarioRealiza = trim($laBariatri['USRTFI']) ;
						break;

					case $lcIndice==2:
						$this->cObservacionesGenerales.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==3:
						$lcTexto = '07,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laBariatri['DESTFI']);
						break;

					case $lcIndice==4:
						$this->cLenguajeComunicaciones.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==5:
						$this->cActitudColaboracion.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==6:
						$this->cObsMusculoEsqueleticos.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==7:
						$this->cObsSistemaIntegumentario.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==8:
						$this->cObsCaminata.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==9:
						$this->cCaminataTa.= trim($laBariatri['DESTFI']) ;
						break;

					case $lcIndice==10:
						$this->cObsValoracionAvd.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==11:
						$this->cObsValoracionAvid.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==13:
						$this->cObsIntervencion.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==14:
						$this->cDatosDiagnosticos.= trim($laBariatri['DESTFI']) ;
						break;

					case $lcIndice==15:
						$this->cObsRecomendaciones.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==16:
						$this->cAntecedentes.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==18:
						$this->cObservacionesDolor.= $laBariatri['DESTFI'] ;
						break;

					case $lcIndice==20:
						$lcTexto = '67,4,N¤68,4,N¤69,4,N¤71,4,N¤72,4,N¤73,4,N¤74,4,N¤94,4,N¤95,4,C' ;
						$this->fnSepararPropiedades($lcTexto,$laBariatri['DESTFI']);
						break;

					case $lcIndice==21:
						$this->cDatosObjetivos.= trim($laBariatri['DESTFI']) ;
						break;

					case $lcIndice==22:
						$laItem = explode('~',trim($laBariatri['DESTFI']));
						$this->cCumplioObjetivos.= $laItem[0];
						$this->cSugerencias.= $laItem[1]??'';
						break;

					case $lcIndice==24:
						$lcTexto = '76,4,N¤77,4,N¤78,4,N¤79,4,N¤81,4,N¤82,4,N¤83,4,N¤84,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N¤92,4,N¤93,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laBariatri['DESTFI']);
						break;

					case $lcIndice==25:
						$laItem = explode('~',trim($laBariatri['DESTFI']));
						$this->cTipoCirugia.= $laItem[0];
						$this->cDolorLocalizacion.= $laItem[1]??'';
						$this->cDolorFrecuencia.= $laItem[2]??'';
						break;

					case $lcIndice==31:
						$this->cTextoPandemia.= $laBariatri['DESTFI'] ;
						break;
				}
			}
		}
	}


	private function fnOrganizarDatosMusculoEsq()
	{
		$this->datosBlanco();

		if (is_array($this->aValoracion))
		{
			foreach($this->aValoracion as $laLesionesMusculoEsq)
			{
				switch (true)
				{
					case $laLesionesMusculoEsq['INDTFI']=='1':
						$this->lnContieneLesionesMusculoEsq = 1;
						$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,C¤09,4,N¤10,4,N¤11,4,N¤'.
								   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤21,4,N¤24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤'.
								   '32,4,N¤33,4,N¤34,4,N¤35,4,N¤36,4,N¤37,4,N¤38,4,N¤40,4,N¤41,4,N¤42,4,N¤43,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laLesionesMusculoEsq['DESTFI']);
						$this->cUsuarioRealiza = trim($laLesionesMusculoEsq['USRTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==2:
						$this->cObservacionesGenerales.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==3:
						$lcTexto = '07,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laLesionesMusculoEsq['DESTFI']);
						break;

					case $laLesionesMusculoEsq['INDTFI']==4:
						$this->cLenguajeComunicaciones.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==5:
						$this->cActitudColaboracion.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==6:
						$this->cObservacionesDolor.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==7:
						$this->cObsEspasmo.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==9:
						$this->cObsSistemaIntegumentario.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==10:
						$this->cFlexibilidadControlMotor.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==11:
						$this->cPosturalControlMotor.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==12:
						$this->cObservacionesMarcha.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==13:
						$this->cObsValoracionAvd.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==14:
						$this->cObsValoracionAvid.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==16:
						$this->cObsIntervencion.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==17:
						$this->cDatosDiagnosticos.= trim($laLesionesMusculoEsq['DESTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==18:
						$this->cDatosMovilidadArticular.= trim($laLesionesMusculoEsq['DESTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==19:
							$this->cDatosMovilidadMuscular.= trim($laLesionesMusculoEsq['DESTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==20:
							$this->cDatosValoracionCaraTronco.= trim($laLesionesMusculoEsq['DESTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==21:
							$this->cDermatomasSeleccionados.= trim($laLesionesMusculoEsq['DESTFI']);
						break;

					case $laLesionesMusculoEsq['INDTFI']==22:
						$this->cObsRecomendaciones.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==23:
						$this->cAntecedentes.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==24:
						$lcTexto = '74,4,N¤75,4,N¤78,4,N¤79,4,N¤81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N';
						$this->fnSepararPropiedades($lcTexto,$laLesionesMusculoEsq['DESTFI']);
						break;

					case $laLesionesMusculoEsq['INDTFI']==25:
						$this->cObsPropiocepcion.= $laLesionesMusculoEsq['DESTFI'] ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==28:
						$this->cDatosObjetivos.= trim($laLesionesMusculoEsq['DESTFI']) ;
						break;

					case $laLesionesMusculoEsq['INDTFI']==29:
						$laItem = explode('~',trim($laLesionesMusculoEsq['DESTFI']));
						$this->cCumplioObjetivos.= $laItem[0];
						$this->cSugerencias.= $laItem[1]??'';
						break;

					case $laLesionesMusculoEsq['INDTFI']==31:
						$this->cTextoPandemia.= $laLesionesMusculoEsq['DESTFI'] ;
					break;
				}
			}
		}
	}


	private function fnOrganizarDatosParalisisFacial($taData)
	{
		$this->datosBlanco();
		$this->aDatos = [];
		$lcFacial = $lcAntecedentes = $lcDiagnosticos = $lcObjetivos = $lcCumplioObjetivos = '';

		foreach($this->aValoracion as $laParalisis)
		{
			$lcUsuario = trim($laParalisis['USRTFI']);
			switch (true)
			{
				case $laParalisis['INDTFI']=='1':

					$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,N¤09,4,N¤10,4,N¤11,4,N¤'.
							   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤21,4,N¤22,4,N¤23,4,N¤'.
							   '24,4,N¤25,4,N¤26,4,N';
					$this->fnSepararPropiedades($lcTexto,$laParalisis['DESTFI'],false);
					break;

				case $laParalisis['INDTFI']=='11':

					$lcDiagnosticos.= $laParalisis['DESTFI'] ;
					break;


				case $laParalisis['INDTFI']=='12':

					$lcFacial.= $laParalisis['DESTFI'] ;
					break;

				case $laParalisis['INDTFI']=='14':

					$lcAntecedentes.= $laParalisis['DESTFI'] ;
					break;

				case $laParalisis['INDTFI']=='15':
					$lcTexto = '81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N';
					$this->fnSepararPropiedades($lcTexto,$laParalisis['DESTFI'],false);
					break;

				case $laParalisis['INDTFI']=='19':

					$lcObjetivos.= $laParalisis['DESTFI'] ;
					break;


				case $laParalisis['INDTFI']=='20':

					$lcCumplioObjetivos.= $laParalisis['DESTFI'] ;
					break;

				case in_array($laParalisis['INDTFI'], [2,3,4,5,6,7,8,9,10,13,16,17,18]) :

					$laKey = [   2 =>  4,  3 =>  7,  4 => 15,  5 => 16,  6 => 20,  7 => 27,  8 => 28,  9 => 29, 10 => 30,
								13 => 34, 16 => 80, 17 => 84, 18 => 89,  ];
					$lnKey = $laKey[ $laParalisis['INDTFI'] ] ?? 0;
					$this->fnUnirMultilinea($lnKey, $laParalisis['DESTFI'], $laParalisis['INDTFI'],false);
					break;

				case $laParalisis['INDTFI']=='17':
					break;

				case $laParalisis['INDTFI']=='31':
						$this->cTextoPandemia.= $laParalisis['DESTFI'] ;
					break;
			}
		}
		$lcTemp = str_pad( $this->aDatos[11], 4, "0", STR_PAD_LEFT);
		$this->aAuscultacion[1] = intval(substr($lcTemp,0,2));
		$this->aAuscultacion[2] = intval(substr($lcTemp,2,2));

		// cargar array Antecedentes
		$this->fnCargarAntecedentes(trim($lcAntecedentes));

		//Cargar Valoración Facial
		$this->fnCargarVFacial(trim($lcFacial));

		// cargar array Diagnosticos
		$this->fnCargarDiagnosticos(trim($lcDiagnosticos));

		// cargar array objetivos
		$this->fnCargarObjetivos(trim($lcObjetivos));
		if(!empty(trim($lcCumplioObjetivos))){
			$laWordsItm = explode('~', $lcCumplioObjetivos);
			$this->cCumplioObjetivos = $laWordsItm[0]=='N'?'NO':'SI';
			$this->cObjetivosSugerencias = $laWordsItm[1]??'';
		}
		// Cargar Médico
		$this->aDatos[97]= trim($lcUsuario) ;

	}

	private function fnOrganizarDatosVertigo($taData)
	{
		$this->datosBlanco();
		$this->aDatos = [];
		$lcAntecedentes = $lcDiagnosticos = $lcObjetivos = $lcCumplioObjetivos = '';

		foreach($this->aValoracion as $laVertigo)
		{
			$lcUsuario = trim($laVertigo['USRTFI']);
			switch (true)
			{
				case $laVertigo['INDTFI']=='1':

					$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,N¤09,4,N¤10,4,N¤11,4,N¤'.
							   '12,4,N¤13,4,N¤14,4,N¤18,4,N¤19,4,N¤20,4,N¤21,4,N¤22,4,N¤23,4,N¤'.
							   '24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤29,4,N¤30,4,N¤32,4,N¤33,4,N¤'.
							   '34,4,N¤35,4,N¤36,4,N¤37,4,N¤38,4,N¤39,4,N¤40,4,N¤44,4,N¤45,4,N¤'.
							   '46,4,N¤47,4,N¤48,4,N¤49,4,N¤50,4,N¤51,4,N¤52,4,N' ;
					$this->fnSepararPropiedades($lcTexto,$laVertigo['DESTFI'],false);
					break;

				case $laVertigo['INDTFI']=='16':

					$lcDiagnosticos.= $laVertigo['DESTFI'] ;
					break;

				case $laVertigo['INDTFI']=='18':

					$lcAntecedentes.= $laVertigo['DESTFI'] ;
					break;

				case $laVertigo['INDTFI']=='19':
					$lcTexto = '74,4,N¤75,4,N¤81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N' ;
					$this->fnSepararPropiedades($lcTexto,$laVertigo['DESTFI'],false);
					break;

				case in_array($laVertigo['INDTFI'], [2,3,4,5,6,7,8,9,10,11,12,13,14,15,17,20,21,22]) :

					$laKey = [   2 =>  4,  3 =>  7,  4 => 15,  5 => 16,  6 => 17,  7 => 31,  8 => 41,  9 => 42, 10 => 43,
								11 => 53, 12 => 54, 13 => 55, 13 => 55, 14 => 56, 15 => 57, 17 => 61, 20 => 80, 21 => 84, 22 => 89];
					$lnKey = $laKey[ $laVertigo['INDTFI'] ] ?? 0;
					$this->fnUnirMultilinea($lnKey, $laVertigo['DESTFI'], $laVertigo['INDTFI'],false);
					break;

				case $laVertigo['INDTFI']=='23':

					$lcObjetivos.= $laVertigo['DESTFI'] ;
					break;

				case $laVertigo['INDTFI']=='24':

					$lcCumplioObjetivos.= $laVertigo['DESTFI'] ;
					break;

				case $laVertigo['INDTFI']=='31':
						$this->cTextoPandemia.= $laVertigo['DESTFI'] ;
					break;
			}
		}
		$lcTemp = str_pad( $this->aDatos[11], 4, "0", STR_PAD_LEFT);
		$this->aAuscultacion[1] = intval(substr($lcTemp,0,2));
		$this->aAuscultacion[2] = intval(substr($lcTemp,2,2));

		// cargar array Antecedentes
		$this->fnCargarAntecedentes(trim($lcAntecedentes));

		// cargar array objetivos
		$this->fnCargarObjetivos(trim($lcObjetivos));
		if(!empty(trim($lcCumplioObjetivos))){
			$laWordsItm = explode('~', $lcCumplioObjetivos);
			$this->cCumplioObjetivos = $laWordsItm[0]=='N'?'NO':'SI';
			$this->cObjetivosSugerencias = $laWordsItm[1]??'';
		}

		// cargar array Diagnosticos
		$this->fnCargarDiagnosticos(trim($lcDiagnosticos));

		// Cargar Médico
		$this->aDatos[97]= trim($lcUsuario) ;
	}

	private function fnOrganizarDatosDesacondicionamiento()
	{
		$this->datosBlanco();

		if (is_array($this->aValoracion))
		{
			foreach($this->aValoracion as $laDesacondicionamiento)
			{
				switch (true)
				{

					case $laDesacondicionamiento['INDTFI']=='1':
						$this->lnContieneDesacondicionamiento = 1;
						$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,C¤09,4,N¤10,4,N¤11,4,N¤'.
								   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤20,4,N¤21,4,N¤22,4,N¤'.
								   '23,4,N¤24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤29,4,N¤30,4,N¤31,4,N¤'.
								   '33,4,N¤34,4,N¤35,4,N¤36,4,N¤38,4,N¤39,4,N¤40,4,N¤41,4,N¤42,4,N¤'.
								   '43,4,N¤44,4,N¤45,4,N¤46,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laDesacondicionamiento['DESTFI']);
						$this->cUsuarioRealiza = trim($laDesacondicionamiento['USRTFI']) ;
						break;

					case $laDesacondicionamiento['INDTFI']==2:
						$this->cObservacionesGenerales.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==3:
						$lcTexto = '07,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laDesacondicionamiento['DESTFI']);
						break;

					case $laDesacondicionamiento['INDTFI']==4:
						$this->cLenguajeComunicaciones.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==5:
						$this->cActitudColaboracion.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==6:
						$this->cObservacionesMarcha.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==7:
						$this->cObsMusculoEsqueleticos.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==8:
						$this->cObsSistemaIntegumentario.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==9:
						$this->cObsValoracionAvd.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==10:
						$this->cObsValoracionAvid.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==12:
						$this->cObsIntervencion.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==13:
						$this->cDatosDiagnosticos.= trim($laDesacondicionamiento['DESTFI']) ;
						break;

					case $laDesacondicionamiento['INDTFI']==14:
						$this->cObsRecomendaciones.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==15:
						$this->cAntecedentes.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==16:
						$lcTexto = '74,4,N¤75,4,N¤78,4,N¤79,4,N¤81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N';
						$this->fnSepararPropiedades($lcTexto,$laDesacondicionamiento['DESTFI']);
						break;

					case $laDesacondicionamiento['INDTFI']==17:
						$this->cObsPropiocepcion.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==18:
						$this->cObservacionesDolor.= $laDesacondicionamiento['DESTFI'] ;
						break;

					case $laDesacondicionamiento['INDTFI']==20:
						$this->cDatosObjetivos.= trim($laDesacondicionamiento['DESTFI']) ;
						break;

					case $laDesacondicionamiento['INDTFI']==21:
						$laItem = explode('~',trim($laDesacondicionamiento['DESTFI']));
						$this->cCumplioObjetivos.= $laItem[0];
						$this->cSugerencias.= $laItem[1]??'';
						break;

					case $laDesacondicionamiento['INDTFI']==31:
						$this->cTextoPandemia.= $laDesacondicionamiento['DESTFI'] ;
					break;

				}
			}
		}
	}

	private function fnOrganizarDatosSistIntegumentario()
	{
		$this->datosBlanco();

		if (is_array($this->aValoracion))
		{
			foreach($this->aValoracion as $laSisIntegumentario)
			{
				switch (true)
				{

					case $laSisIntegumentario['INDTFI']=='1':
						$this->lnContieneIntegumentario = 1;
						$lcTexto = '1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,C¤09,4,N¤10,4,N¤11,4,N¤'.
								   '12,4,N¤13,4,N¤14,4,N¤17,4,N¤18,4,N¤19,4,N¤20,4,N¤21,4,N¤22,4,N¤'.
								   '23,4,N¤24,4,N¤25,4,N¤26,4,N¤27,4,N¤28,4,N¤29,4,N¤30,4,N¤31,4,N¤'.
								   '32,4,N¤33,4,N¤34,4,N¤35,4,N¤36,4,N¤37,4,N¤38,4,N¤39,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laSisIntegumentario['DESTFI']);
						$this->cUsuarioRealiza = trim($laSisIntegumentario['USRTFI']) ;
						break;

					case $laSisIntegumentario['INDTFI']==2:
						$this->cObservacionesGenerales.= $laSisIntegumentario['DESTFI'] ;
						break;

						case $laSisIntegumentario['INDTFI']==3:
						$lcTexto = '07,4,N' ;
						$this->fnSepararPropiedades($lcTexto,$laSisIntegumentario['DESTFI']);
						break;

					case $laSisIntegumentario['INDTFI']==4:
						$this->cLenguajeComunicaciones.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==5:
						$this->cActitudColaboracion.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==6:
						$this->cObservacionesColorPiel.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==7:
						$this->cObsValoracionAvd.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==8:
						$this->cObsValoracionAvid.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==10:
						$this->cObsIntervencion.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==11:
						$this->cDatosDiagnosticos.= trim($laSisIntegumentario['DESTFI']) ;
						break;

					case $laSisIntegumentario['INDTFI']==12:
						$this->cObsRecomendaciones.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==13:
						$this->cAntecedentes.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==14:
						$lcTexto = '78,4,N¤79,4,N¤81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N';
						$this->fnSepararPropiedades($lcTexto,$laSisIntegumentario['DESTFI']);
						break;

					case $laSisIntegumentario['INDTFI']==15:
						$this->cObsPropiocepcion.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==16:
						$this->cObservacionesDolor.= $laSisIntegumentario['DESTFI'] ;
						break;

					case $laSisIntegumentario['INDTFI']==18:
						$this->cDatosObjetivos.= trim($laSisIntegumentario['DESTFI']) ;
						break;

					case $laSisIntegumentario['INDTFI']==19:
						$laItem = explode('~',trim($laSisIntegumentario['DESTFI']));
						$this->cCumplioObjetivos.= $laItem[0];
						$this->cSugerencias.= $laItem[1]??'';
						break;

					case $laSisIntegumentario['INDTFI']==31:
						$this->cTextoPandemia.= $laSisIntegumentario['DESTFI'] ;
					break;

				}
			}
		}
	}


	private function fnOrganizarDatosEstimulaAdecuada($taData)
	{
		$this->aDatos = [];
		$this->datosBlanco();
		$lcAntecedentes = $lcDiagnosticos = $lcObjetivos = $lcCumplioObjetivos = $lcRefleja = '';

		foreach($this->aValoracion as $laEstimulacion)
		{
			$lcUsuario = trim($laEstimulacion['USRTFI']);
			switch (true)
			{
				case $laEstimulacion['INDTFI']=='1':

					$lcTexto = 	'1,14,T¤02,4,N¤03,4,N¤05,4,N¤06,4,N¤08,4,N¤09,4,N¤10,4,N¤'.
								'11,4,N¤12,4,N¤13,4,N¤14,4,N' ;

					$this->fnSepararPropiedades($lcTexto,$laEstimulacion['DESTFI'],false);
					break;

				// Ajustando valoracion I
				case $laEstimulacion['INDTFI']=='2':

					$lcTexto = 	'17,4,N¤18,4,N¤19,4,N¤20,4,N¤21,4,N¤22,4,N¤23,4,N¤24,4,N¤25,4,N¤26,4,N¤'.
								'27,4,N¤28,4,N¤29,4,N¤30,4,N¤31,4,N¤32,4,N¤33,4,N¤34,4,N¤35,4,N' ;

					$this->fnSepararPropiedades($lcTexto,$laEstimulacion['DESTFI'],false);
					break;

				// Ajustando valoracion II
				case $laEstimulacion['INDTFI']=='3':

					$lcTexto = 	'37,4,N¤38,4,N¤40,4,N¤41,4,N¤42,4,N¤43,4,N¤44,4,N¤45,4,N¤46,4,N¤'.
								'47,4,N¤48,4,N' ;

					$this->fnSepararPropiedades($lcTexto,$laEstimulacion['DESTFI'],false);
					break;

				// Ajustando flexibilidad / actividad motora
				case $laEstimulacion['INDTFI']=='4':

					$lcTexto = 	'50,4,N¤51,4,N¤52,4,N¤53,4,N¤54,4,N¤55,4,N¤56,4,N¤57,4,N¤58,4,N¤59,4,N¤'.
								'60,4,N¤61,4,N¤62,4,N¤63,4,N' ;

					$this->fnSepararPropiedades($lcTexto,$laEstimulacion['DESTFI'],false);
					break;

				case $laEstimulacion['INDTFI']=='16':

					$lcDiagnosticos.= $laEstimulacion['DESTFI'] ;
					break;

				case $laEstimulacion['INDTFI']=='17':

					$lcRefleja.= $laEstimulacion['DESTFI'] ;
					break;

				case $laEstimulacion['INDTFI']=='19':

					$lcAntecedentes.= $laEstimulacion['DESTFI'] ;
					break;

				case $laEstimulacion['INDTFI']=='20':

					$lcTexto = 	'78,4,N¤79,4,N¤81,4,N¤82,4,N¤83,4,N¤85,4,N¤86,4,N¤87,4,N¤88,4,N' ;
					$this->fnSepararPropiedades($lcTexto,$laEstimulacion['DESTFI'],false);
					break;

				case in_array($laEstimulacion['INDTFI'], [5,6,7,8,9,10,11,12,13,14,15,18,21,22,23]) :

					$laKey = [   5 =>  4,  6 =>  7,  7 => 15,  8 => 16,  9 => 36, 10 => 39, 11 => 49, 12 => 64, 13 => 65,
								14 => 66, 15 => 67, 18 => 71, 21 => 80, 22 => 84, 23 => 89];
					$lnKey = $laKey[ $laEstimulacion['INDTFI'] ] ?? 0;
					$this->fnUnirMultilinea($lnKey, $laEstimulacion['DESTFI'], $laEstimulacion['INDTFI'],false);
					break;

				case $laEstimulacion['INDTFI']=='24':

					$lcObjetivos.= $laEstimulacion['DESTFI'] ;
					break;

				case $laEstimulacion['INDTFI']=='25':

					$lcCumplioObjetivos.= $laEstimulacion['DESTFI'] ;
					break;

				case $laEstimulacion['INDTFI']=='31':
						$this->cTextoPandemia.= $laEstimulacion['DESTFI'] ;
					break;

			}

		}
		$lcTemp = str_pad( $this->aDatos[11], 4, "0", STR_PAD_LEFT);
		$this->aAuscultacion[1] = intval(substr($lcTemp,0,2));
		$this->aAuscultacion[2] = intval(substr($lcTemp,2,2));

		// cargar array Antecedentes
		$this->fnCargarAntecedentes(trim($lcAntecedentes));

		// cargar array objetivos
		$this->fnCargarObjetivos(trim($lcObjetivos));
		if(!empty(trim($lcCumplioObjetivos))){
			$laWordsItm = explode('~', $lcCumplioObjetivos);
			$this->cCumplioObjetivos = $laWordsItm[0]=='N'?'NO':'SI';
			$this->cObjetivosSugerencias = $laWordsItm[1]??'';
		}

		// cargar array Diagnosticos
		$this->fnCargarDiagnosticos(trim($lcDiagnosticos));

		//Cargas reflejos niño
		$lnId = $lcP1 = '';
		$lcCharReg = ',';
		$lcCharItm = '|';
		$laWordsReg = explode($lcCharReg, trim($lcRefleja));

		foreach($laWordsReg as $laRegs) {

			$laWordsItm = explode($lcCharItm, $laRegs);
			$lnId = $laWordsItm[0] ?? '';
			$lcP1 = 'P'.trim($laWordsItm[1] ?? '');
			if($lcP1!='p0' && $lcP1!='p'){
				$key = array_search($lnId, array_column($this->aRefleja, 'ID'));
				if (is_numeric($key)){
					$this->aRefleja[$key][$lcP1]='1';
					$this->aRefleja[$key]['USAR']='1';
					$laRegRef = explode($lcCharReg, trim($this->aRefleja[$key]['REFLEJO']));

					$lnDato = intval($this->aRefleja[$key]['MIN']);
					$this->aRefleja[$key]['EDAD']=$lnDato . ' ' .trim($laRegRef[1]??'');
					$this->aRefleja[$key]['REFLEJO']=trim($laRegRef[0]);
				}
			}
		}

		// Cargar Médico
		$this->aDatos[97]= trim($lcUsuario) ;
	}

	private function prepararInformeNeurologico($taData)
	{
		$lcSL = $this->cSL;
		/* Cuerpo */
		$laTr = [];
		$laTr[] = ['titulo1', str_repeat('-',29).' VALORACIÓN SISTEMA NEUROLÓGICO '.str_repeat('-',29)] ;
		$laTr[] = ['saltol', 3];
		$lcInfGeneral = $this->fnInformacionGeneral() ;
 		if(!empty($lcInfGeneral)){
			$laTr = array_merge($laTr, $lcInfGeneral) ;
		}
		$laTr[] = ['saltol', 1];

		// Texto Pandemia
		$lcTemp = $this->fnInsertarTextopandemia(trim($this->cTextoPandemia ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Observacion General
		$lcTemp = $this->fnInsertarObservacionGeneral(trim($this->aDatos[4] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		 // Antecedentes
		$lcTemp = $this->fnInsertarAntecedente() ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Lenguaje y Comunicación
		$lcTemp = $this->fnInsertarLenguaje(trim($this->aDatos[15] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Actitud (colaboración)
		$lcTemp = $this->fnInsertarActitud(trim($this->aDatos[16] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Propiocepcion
		$lcTemp = $this->InsertarPropiocepcion(78,79,63) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//DOLOR
		$lcTemp = $this->InsertarDolor(64,65,0,67,false) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// CONTROL MOTOR
		// ESTADO DE AFERENCIA MOTORA
		if(!empty(trim($this->aDatos[17] ?? '')) || !empty(trim($this->aDatos[18] ?? '')) || !empty(trim($this->aDatos[19] ?? '')) || !empty(trim($this->aDatos[20] ?? '')) ||
	       !empty(trim($this->aDatos[21] ?? '')) || !empty(trim($this->aDatos[22] ?? '')) || !empty(trim($this->aDatos[23] ?? ''))) {

			$laDatos = [] ;
			$laDatos[] = ['w'=>[47,48,47,48], 'd'=>[$this->fnDescripcionPrmTab('AFE'.trim($this->aDatos[17])),$this->fnDescripcionPrmTab('SIN'.trim($this->aDatos[18])),
													$this->fnDescripcionPrmTab('AFE'.trim($this->aDatos[19])),$this->fnDescripcionPrmTab('SIN'.trim($this->aDatos[20]))
													],'a'=>'C'];

			$laDatos[] = ['w'=>[63,64,63], 'd'=>["Cambios decubito: ".$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[21])),
												 "Adopta bipeda: ".$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[22])),
												 "Adopta sedente: ".$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[23]))
													],'a'=>'C'];

			$laTr[] = ['saltol', 3];
			$laTr[] = ['titulo1', 'CONTROL MOTOR'] ;
			$laTr[] = ['titulo2', 'ESTADO DE AFERENCIA MOTORA'] ;
			$laTr[] = ['saltol', 3];
			$laTr[]= ['tabla',
				[ [ 'w'=>[47,48,47,48], 'd'=>['<b>MMSS</b>','<b>MMSS Sinergia</b>','<b>MMII</b>','<b>MMII Sinergia</b>'], 'a'=>'C', ] ],
				$laDatos,
				];
			$laTr[] = ['saltol', 3];
		}

		// CONTROL MOTOR
		// MARCHA
		$lcTemp = $this->InsertarMarcha(24,32) ;
		if(!empty($lcTemp)){
			$laTr[] = ['titulo2', 'MARCHA'] ;
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Sistema Muscoesqueletico
		$lcTemp = $this->InsertarSistMusculoEsq('CNV', 33, 34, 0, 0, 35) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Sistema Integumentario
		$lcTemp = $this->InsertarIntegumentario(68) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Fuerza Muscular Global
		if(!empty(trim($this->aDatos[36] ?? '')) || !empty(trim($this->aDatos[37] ?? '')) || !empty(trim($this->aDatos[38] ?? '')) || !empty(trim($this->aDatos[39] ?? '')) ||
		   !empty(trim($this->aDatos[40] ?? '')) || !empty(trim($this->aDatos[41] ?? '')) || !empty(trim($this->aDatos[42] ?? '')) || !empty(trim($this->aDatos[43] ?? ''))) {

			$laDatos = [] ;
			$laAnchos = [30,40,40,40,40];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['','Derecha','Izquierda','Derecha','Izquierda'],'a'=>['C','C','C','C','C']];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Proximal',$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[36])),$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[37])),
													   $this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[38])),$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[39]))
													  ],'a'=>'C'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Distancial',$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[40])),$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[41])),
													   $this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[42])),$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[43]))
													  ],'a'=>'C'];

			$laTr[] = ['titulo1', 'FUERZA MUSCULAR GLOBAL'] ;
			$laTr[] = ['saltol', 3];
			$laAnchos = [30,80,80];
			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['','<b>MMSS</b>','<b>MMII</b>'], 'a'=>'C', ] ],
				$laDatos,
				];
		}

		if (!empty(trim($this->aDatos[44] ?? ''))){
			$laTr[] = ['titulo2', 'FLEXIBILIDAD'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[44])];
		}

		if (!empty(trim($this->aDatos[45] ?? ''))){
			$laTr[] = ['titulo2', 'OBSERVACIONES'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[45])];
		}

		// Sistema Neuromuscular
		$lcTemp = $this->InsertarReaccEquilibrio(46) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Postura
		if (!empty(trim($this->aDatos[50] ?? ''))){
			$laTr[] = ['titulo2', 'POSTURA'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[50])];
		}

		//Sensibilidad
		$lcTemp = $this->InsertarSensibilidad(51, 74, 75) ;
		if (!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Actividad Refleja
		if (!empty(trim($this->aDatos[52] ?? ''))){
			$laTr[] = ['titulo2', 'ACTIVIDAD REFLEJA'] ;
			$lcTemp = $this->fnDescripcionPrmTab('ATR'.trim($this->aDatos[52])) ;
			if (!empty(trim($this->aDatos[53] ?? ''))){
				$lcTemp .= ' - '. trim($this->aDatos[53]);
			}
			$laTr[] = ['texto9',	trim($lcTemp)];
		}

		//Dermatomas
		$lcTemp = '';
		foreach($this->aDermatomas as $laDermatomas)
			{
				if($laDermatomas['USAR']=='1'){
					$lcTemp .= empty(trim($lcTemp))?'':', ';
					$lcTemp .= $laDermatomas['ITEM'];
				}
			}
		if (!empty(trim($lcTemp))){
			$laTr[] = ['titulo2', 'DERMATOMAS'] ;
			$laTr[] = ['texto9',	trim($lcTemp)];
		}

		if (!empty(trim($this->aDatos[54] ?? ''))){
			$laTr[] = ['titulo2', 'OBSERVACIONES'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[54])];
		}

		//Valoración Funcional
		$lcTemp = $this->InsertarValoracionFunc(55,56) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Diagnostico e Intervencion
		$lcTemp = $this->InsertarDiagnosticos(57,58,62)	;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// FIRMA
		$laFirma=['usuario'=>$this->aDatos[97]];
		$laTr[] = ['firmas', [ $laFirma, ] ];

		// Notas Aclaratorias
		$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'A'));

		$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
	}


	private function prepararInformeBariatrico($taData)
	{
		$laTr = [];
		if ($this->lnContieneBariatrica == 1){
			$laTr=$this->InsertarInfoGeneral('VALORACIÓN CIRUGÍA BARIÁTRICA');
			$laTr=array_merge($laTr, $this->fnInsertarTextopandemia(trim($this->cTextoPandemia)));
			$laTr=array_merge($laTr, $this->fnInsertarObservacion(trim($this->cObservacionesGenerales)));
			$laTr=array_merge($laTr, $this->fnInsertarAntecedentes(trim($this->cAntecedentes)));
			$laTr=array_merge($laTr, $this->fnInsertarLenguajeCom(trim($this->cLenguajeComunicaciones)));
			$laTr=array_merge($laTr, $this->fnInsertarActitud(trim($this->cActitudColaboracion)));
			$laTr=array_merge($laTr, $this->fnInsertarDolor(2));
			$laTr=array_merge($laTr, $this->fnSistemaMusculoesq());
			$laTr=array_merge($laTr, $this->fnSistemaNeuroMuscular());
			$laTr=array_merge($laTr, $this->fnSistemaIntegumentario());
			$laTr=array_merge($laTr, $this->fnAntropometricas());
			$laTr=array_merge($laTr, $this->fnValoracion());
			$laTr=array_merge($laTr, $this->fnValoracionFuncional());
			$laTr=array_merge($laTr, $this->fnDiagnosticoIntervencion());
			$laTr=array_merge($laTr, $this->InsertarFirma());
			$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'B'));
			$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
		}
	}

	private function prepararInformeMusculoEsq($taData)
	{

		$laTr = [];
		if ($this->lnContieneLesionesMusculoEsq == 1){
			$laTr=$this->InsertarInfoGeneral('VALORACIÓN LESIONES MÚSCULO ESQUELÉTICAS');
			$laTr=array_merge($laTr, $this->fnInsertarTextopandemia(trim($this->cTextoPandemia)));
			$laTr=array_merge($laTr, $this->fnInsertarObservacion(trim($this->cObservacionesGenerales)));
			$laTr=array_merge($laTr, $this->fnInsertarAntecedentes(trim($this->cAntecedentes)));
			$laTr=array_merge($laTr, $this->fnInsertarLenguajeCom(trim($this->cLenguajeComunicaciones)));
			$laTr=array_merge($laTr, $this->fnInsertarActitud(trim($this->cActitudColaboracion)));
			$laTr=array_merge($laTr, $this->fnInsertarPropiocepcion());
			$laTr=array_merge($laTr, $this->fnInsertarDolor(3));
			$laTr=array_merge($laTr, $this->fnInsertarEspasmo());
			$laTr=array_merge($laTr, $this->fnInsertarSensibilidad());
			$laTr=array_merge($laTr, $this->fnSistemaIntegumentario());
			$laTr=array_merge($laTr, $this->fnInsertarMovilidadArticular());
			$laTr=array_merge($laTr, $this->fnInsertarValoracionMuscular());
			$laTr=array_merge($laTr, $this->fnInsertarCaraTronco());
			$laTr=array_merge($laTr, $this->fnInsertarMarcha());
			$laTr=array_merge($laTr, $this->fnInsertarReacciones());
			$laTr=array_merge($laTr, $this->fnValoracionFuncional());
			$laTr=array_merge($laTr, $this->fnDiagnosticoIntervencion());
			$laTr=array_merge($laTr, $this->InsertarFirma());
			$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'C'));
			$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
		}
	}

	private function prepararInformeParalisisFacial($taData)
	{
		/* Cuerpo */
		$laTr = [];
		$laTr[] = ['titulo1', str_repeat('-',30).'  VALORACIÓN PARÁLISIS FACIAL  '.str_repeat('-',30)] ;
		$laTr[] = ['saltol', 3];
		$lcInfGeneral = $this->fnInformacionGeneral() ;
 		if(!empty($lcInfGeneral)){
			$laTr = array_merge($laTr, $lcInfGeneral) ;
		}
		$laTr[] = ['saltol', 1];

		// Texto Pandemia
		$lcTemp = $this->fnInsertarTextopandemia(trim($this->cTextoPandemia ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Observacion General
		$lcTemp = $this->fnInsertarObservacionGeneral(trim($this->aDatos[4] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		 // Antecedentes
		$lcTemp = $this->fnInsertarAntecedente() ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Lenguaje y Comunicación
		$lcTemp = $this->fnInsertarLenguaje(trim($this->aDatos[15] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Actitud (colaboración)
		$lcTemp = $this->fnInsertarActitud(trim($this->aDatos[16] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//DOLOR
		$lcTemp = $this->InsertarDolor(17,18,19,20,false) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// SIGNOS
		if(!empty(trim($this->aDatos[21] ?? '')) || !empty(trim($this->aDatos[22] ?? '')) || !empty(trim($this->aDatos[23] ?? '')) || !empty(trim($this->aDatos[24] ?? ''))	||
           !empty(trim($this->aDatos[25] ?? '')) || !empty(trim($this->aDatos[26] ?? ''))) {

			$laDatos = [] ;
			$laAnchos = [31,32,32,32,31,32];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>[$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[21])),$this->fnDescripcionPrmTab('SIM'.trim($this->aDatos[22])),
												$this->fnDescripcionPrmTab('ANA'.trim($this->aDatos[23])),$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[24])),
												$this->fnDescripcionPrmTab('ANA'.trim($this->aDatos[25])),$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[26]))
											   ],'a'=>'C'];
			$laTr[] = ['titulo1', 'VALORACION'] ;
			$laTr[] = ['saltol', 3];
			$laTr[] = ['titulo2', 'SIGNOS'] ;
			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['<b>Lagrimeo</b>','<b>Expresión</b>','<b>Gusto</b>',
										  '<b>Signo de Bell</b>','<b>Olfato</b>','<b>Cicatriz</b>'], 'a'=>'C', ] ],
				$laDatos,
				];
		}

		// VALORACION FACIAL
		$laDatos = [] ;
		$laAnchos = [100,15,15,15,15,15,15];
		$llImprimir = false ;

		foreach($this->aFacial as $laFacial){
			if($laFacial['USAR']=='1'){
				$llImprimir = true ;
				$laDatos[] = ['w'=>$laAnchos, 'd'=>[$laFacial['ITEM'],$laFacial['P1']=='0'?'':$laFacial['P1'],$laFacial['P2']=='0'?'':$laFacial['P2'],
													$laFacial['P3']=='0'?'':$laFacial['P3'],$laFacial['P4']=='0'?'':$laFacial['P4'],
													$laFacial['P5']=='0'?'':$laFacial['P5'],$laFacial['P6']=='0'?'':$laFacial['P6']
												   ],'a'=>['L','C','C','C','C','C','C']];
			}
		}

		if($llImprimir){
			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['VALORACIÓN MUSCULAR FACIAL','<b>0%</b>','<b>10%</b>',
										  '<b>25%</b>','<b>50%</b>','<b>75%</b>','<b>100%</b>'], 'a'=>['L','C','C','C','C','C','C'], ] ],
				$laDatos,
				];
		}

		//Valoración Funcional
		$lcTemp = $this->InsertarValoracionFunc(27,28) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Diagnostico e Intervencion
		$lcTemp = $this->InsertarDiagnosticos(29,30,34)	;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// FIRMA
		$laFirma=['usuario'=>$this->aDatos[97]];
		$laTr[] = ['firmas', [ $laFirma, ] ];

		// Notas Aclaratorias
		$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'D'));

		$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
	}

	private function prepararInformeVertigo($taData)
	{
		/* Cuerpo */
		$laTr = [];
		$lcSL = $this->cSL;
		$laTr[] = ['titulo1', str_repeat('-',32).'  VALORACIÓN VERTIGO  '.str_repeat('-',32)] ;
		$laTr[] = ['saltol', 3];
		$lcInfGeneral = $this->fnInformacionGeneral() ;
 		if(!empty($lcInfGeneral)){
			$laTr = array_merge($laTr, $lcInfGeneral) ;
		}
		$laTr[] = ['saltol', 1];

		// Texto Pandemia
		$lcTemp = $this->fnInsertarTextopandemia(trim($this->cTextoPandemia ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

 		// Observacion General
		$lcTemp = $this->fnInsertarObservacionGeneral(trim($this->aDatos[4] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		 // Antecedentes
		$lcTemp = $this->fnInsertarAntecedente() ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Posicion que desencadena el vertigo

		if(!empty(trim($this->aDatos[17] ?? ''))){
			$laTr[] = ['titulo1', 'POSICION QUE DESENCADENA EL VERTIGO'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[17])];
		}

		//Lenguaje y Comunicación
		$lcTemp = $this->fnInsertarLenguaje(trim($this->aDatos[15] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//DOLOR
		$lcTemp = $this->InsertarDolor(81,82,83,84,false) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Sensibilidad
		$lcTemp = $this->InsertarSensibilidad(0, 74, 75) ;


 		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Actitud (colaboración)
		$lcTemp = $this->fnInsertarActitud(trim($this->aDatos[16] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// NISTAGMUS
		$lcDatos = '';
		if(!empty(trim($this->aDatos[18] ?? ''))){

			$laTr[] = ['txthtml9', '<b>NISTAGMUS: </b>'.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[18]))];

			if(!empty(trim($this->aDatos[19] ?? ''))){
				$lcDatos = $lcSL . '   Duración: '.($this->aDatos[19]==1?'< 1 Min':($this->aDatos[19]==2?'> 1 Min':$this->aDatos[19])) ;
			}
			if(!empty(trim($this->aDatos[20] ?? ''))){
				$lcDatos .= '   Fatigable: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[20])) ;
			}

			if(!empty(trim($this->aDatos[21] ?? ''))){
				$lcDatos .= '   Dirección: '.$this->fnDescripcionPrmTab('HAH'.trim($this->aDatos[21]));
			}
		}
		if(!empty(trim($lcDatos))){
			$laTr[] = ['texto9',	trim($lcDatos)];
		}

		// TINITUS
		if(!empty(trim($this->aDatos[22] ?? ''))){
			$laTr[] = ['txthtml9', '<b>TINITUS O PERDIDA DE AUDICIÓN: </b>'.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[22]))];
		}

		// NAUSEAS O VOMITO
		if(!empty(trim($this->aDatos[23] ?? ''))){
			$laTr[] = ['txthtml9', '<b>NAUSEAS O VOMITO: </b>'.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[23]))];
		}

		// CONTROL MOTOR
		// MARCHA
		$lcTemp = $this->InsertarMarcha(24,31) ;
		if(!empty($lcTemp)){
			$laTr[] = ['titulo1', 'CONTROL MOTOR'] ;
			$laTr[] = ['titulo2', 'MARCHA'] ;
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Sistema musculoesqueletico Cervical
		$laDatos = [] ;
		if(!empty(trim($this->aDatos[32] ?? '')) || !empty(trim($this->aDatos[33] ?? '')) ||
		   !empty(trim($this->aDatos[34] ?? '')) || !empty(trim($this->aDatos[35] ?? '')) ||
		   !empty(trim($this->aDatos[36] ?? '')) || !empty(trim($this->aDatos[37] ?? '')) ||
		   !empty(trim($this->aDatos[38] ?? '')) || !empty(trim($this->aDatos[39] ?? ''))){
			$laAnchos=[36,36,36,36,36];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Limitado',$this->aDatos[32]=='1'?'X':'',$this->aDatos[33]=='1'?'X':'',
														   $this->aDatos[34]=='1'?'X':'',$this->aDatos[35]=='1'?'X':''
		     								   ],'a'=>'C'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['No Limitado',$this->aDatos[36]=='1'?'X':'',$this->aDatos[37]=='1'?'X':'',
														   $this->aDatos[38]=='1'?'X':'',$this->aDatos[39]=='1'?'X':''
		     								   ],'a'=>'C'];

			$laTr[] = ['titulo1', 'SISTEMA MUSCOESQUELETICO CERVICAL'] ;
			$laTr[] = ['saltol', 3];
			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['','<b>Flexión(65°)</b>','<b>Extensión(60°)</b>','<b>Inclinación(35°)</b>','<b>Rotación(50°)</b>'], 'a'=>'C', ] ],
				$laDatos,
				];

		}

		// OBSERVACIONES
		if(!empty(trim($this->aDatos[40] ?? '')) || !empty(trim($this->aDatos[41] ?? ''))){
			$laTr[] = ['titulo1', 'ESPASMOS: '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[40]))] ;
			if(!empty(trim($this->aDatos[41] ?? ''))){
				$laTr[] = ['texto9',trim($this->aDatos[41])];
			}
		}

		// FLEXIBILIDAD
		if (!empty(trim($this->aDatos[42] ?? ''))){
			$laTr[] = ['titulo2', 'FLEXIBILIDAD'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[42])];
		}

		if (!empty(trim($this->aDatos[43] ?? ''))){
			$laTr[] = ['titulo2', 'OBSERVACIONES'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[43])];
		}

		// Sistema Neuromuscular
		$lcTemp = $this->InsertarReaccEquilibrio(44) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		$lcTemp = $this->InsertarNeuromuscular(48) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Postura
		if (!empty(trim($this->aDatos[53] ?? ''))){
			$laTr[] = ['titulo2', 'POSTURA'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[53])];
		}

		//Valoración Funcional
		$lcTemp = $this->InsertarValoracionFunc(54,55) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Diagnostico e Intervencion
		$lcTemp = $this->InsertarDiagnosticos(56,57,61)	;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// FIRMA
		$laFirma=['usuario'=>$this->aDatos[97]];
		$laTr[] = ['firmas', [ $laFirma, ] ];

		// Notas Aclaratorias
		$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'E'));

		$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
	}

	private function prepararInformeDesacondicionamiento($taData)
	{
		$laTr = [];
		if ($this->lnContieneDesacondicionamiento == 1){
			$laTr=$this->InsertarInfoGeneral('DESACONDICIONAMIENTO FÍSICO');
			$laTr=array_merge($laTr, $this->fnInsertarTextopandemia(trim($this->cTextoPandemia)));
			$laTr=array_merge($laTr, $this->fnInsertarObservacion(trim($this->cObservacionesGenerales)));
			$laTr=array_merge($laTr, $this->fnInsertarAntecedentes(trim($this->cAntecedentes)));
			$laTr=array_merge($laTr, $this->fnInsertarLenguajeCom(trim($this->cLenguajeComunicaciones)));
			$laTr=array_merge($laTr, $this->fnInsertarActitud(trim($this->cActitudColaboracion)));
			$laTr=array_merge($laTr, $this->fnInsertarPropiocepcion());
			$laTr=array_merge($laTr, $this->fnInsertarDolor(6));
			$laTr=array_merge($laTr, $this->fnInsertarSensibilidad());
			$laTr=array_merge($laTr, $this->fnInsertarMarcha());
			$laTr=array_merge($laTr, $this->fnSistemaMusculoesq());
			$laTr=array_merge($laTr, $this->fnSistemaNeuroMuscular());
			$laTr=array_merge($laTr, $this->fnSistemaIntegumentario());
			$laTr=array_merge($laTr, $this->fnValoracionFuncional());
			$laTr=array_merge($laTr, $this->fnDiagnosticoIntervencion());
			$laTr=array_merge($laTr, $this->InsertarFirma());
			$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'F'));
			$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
		}
	}

	private function prepararInformeSistIntegumentario($taData)
	{
		$laTr = [];
		if ($this->lnContieneIntegumentario == 1){
			$laTr=$this->InsertarInfoGeneral('VALORACIÓN SISTEMA INTEGUMENTARIO');
			$laTr=array_merge($laTr, $this->fnInsertarTextopandemia(trim($this->cTextoPandemia)));
			$laTr=array_merge($laTr, $this->fnInsertarObservacion(trim($this->cObservacionesGenerales)));
			$laTr=array_merge($laTr, $this->fnInsertarAntecedentes(trim($this->cAntecedentes)));
			$laTr=array_merge($laTr, $this->fnInsertarLenguajeCom(trim($this->cLenguajeComunicaciones)));
			$laTr=array_merge($laTr, $this->fnInsertarActitud(trim($this->cActitudColaboracion)));
			$laTr=array_merge($laTr, $this->fnInsertarPropiocepcion());
			$laTr=array_merge($laTr, $this->fnInsertarDolor(7));
			$laTr=array_merge($laTr, $this->fnInsertarValoracionColor());
			$laTr=array_merge($laTr, $this->fnValoracionFuncional());
			$laTr=array_merge($laTr, $this->fnDiagnosticoIntervencion());
			$laTr=array_merge($laTr, $this->InsertarFirma());
			$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'G'));
			$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
		}
	}

	private function prepararInformeEstimulaAdecuada($taData)
	{
		/* Cuerpo */
		$lcSL = $this->cSL ;
		$laTr[] = ['titulo1', str_repeat('-',25).'  VALORACIÓN ESTIMULACIÓN ADECUADA  '.str_repeat('-',25)] ;
		$laTr[] = ['saltol', 3];
		$lcInfGeneral = $this->fnInformacionGeneral() ;
 		if(!empty($lcInfGeneral)){
			$laTr = array_merge($laTr, $lcInfGeneral) ;
		}
		$laTr[] = ['saltol', 1];

		// Texto Pandemia
		$lcTemp = $this->fnInsertarTextopandemia(trim($this->cTextoPandemia ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

 		// Observacion General
		$lcTemp = $this->fnInsertarObservacionGeneral(trim($this->aDatos[4] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

 		 // Antecedentes
		$lcTemp = $this->fnInsertarAntecedente() ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Lenguaje y Comunicación
		$lcTemp = $this->fnInsertarLenguaje(trim($this->aDatos[15] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Actitud (colaboración)
		$lcTemp = $this->fnInsertarActitud(trim($this->aDatos[16] ?? '')) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Propiocepcion
		$lcTemp = $this->InsertarPropiocepcion(78,79,80) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//DOLOR
		$lcTemp = $this->InsertarDolor(81,82,83,84,false) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Valoración

		if (!empty(trim($this->aDatos[17] ?? '')) || !empty(trim($this->aDatos[18] ?? '')) || !empty(trim($this->aDatos[19] ?? '')) ||
			!empty(trim($this->aDatos[20] ?? '')) || !empty(trim($this->aDatos[21] ?? '')) || !empty(trim($this->aDatos[22] ?? '')) ||
			!empty(trim($this->aDatos[23] ?? '')) || !empty(trim($this->aDatos[24] ?? '')) || !empty(trim($this->aDatos[25] ?? ''))){

			$laDatos=[];
			$laAnchos=[63,64,63] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Auditivo:   '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[17] ?? '')),
												'Tacto:      '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[20] ?? '')),
												'Salto:      '.$this->fnDescripcionPrmTab('PRF'.trim($this->aDatos[23] ?? ''))
											   ],'a'=>'L'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Seg. Visual: '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[18] ?? '')),
												'Dolor:       '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[21] ?? '')),
												'Carrera:     '.$this->fnDescripcionPrmTab('PRF'.trim($this->aDatos[24] ?? ''))
											   ],'a'=>'L'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Táctil:      '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[19] ?? '')),
												'Temperatura: '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[22] ?? '')),
												'Marcha:      '.$this->fnDescripcionPrmTab('PRF'.trim($this->aDatos[25] ?? ''))
											   ],'a'=>'L'];

			$laTr[] = ['titulo1', 'VALORACIÓN'] ;
			$laTr[]= ['tabla',
								[ [ 'w'=>$laAnchos, 'd'=>['<b>NOGSIAS</b>','<b>SENSIBILIDAD SUPERFICIAL</b>','<b>PATRONES FUNDAMENTALES</b>'], 'a'=>'C', ] ],
								$laDatos,
								];
		}

		// Coordinación
		if (!empty(trim($this->aDatos[26] ?? '')) || !empty(trim($this->aDatos[27] ?? ''))){
			$laDatos=[];
			$laTr[] = ['titulo1', 'COORDINACION'] ;
			$laAnchos=[95,95] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Fina (Dedo-Nariz): '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[26])),
												'Gruesa (Talón-Rodilla): '.$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[27]))
										   ],'a'=>'L'];

			$laTr[]= ['tablaSL',	[ ],$laDatos,];
		}

		// Estado de aferencia Motora o Tono muscular
		if (!empty(trim($this->aDatos[28] ?? '')) || !empty(trim($this->aDatos[29] ?? '')) || !empty(trim($this->aDatos[30] ?? '')) || !empty(trim($this->aDatos[31] ?? ''))){

			$laDatos=[];
			$laTr[] = ['titulo1', 'ESTADO DE AFERENCIA MOTORA O TONO MUSCULAR'] ;
			$laAnchos=[95,95] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['MMSS: '.$this->fnDescripcionPrmTab('AFE'.trim($this->aDatos[28])),
												'MMSS Sinergia: '.$this->fnDescripcionPrmTab('SIN'.trim($this->aDatos[29]))
										   ],'a'=>'L'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['MMII: '.$this->fnDescripcionPrmTab('AFE'.trim($this->aDatos[30])),
												'MMII Sinergia: '.$this->fnDescripcionPrmTab('SIN'.trim($this->aDatos[31]))
										   ],'a'=>'L'];

			$laTr[]= ['tablaSL',	[ ],$laDatos,];
		}

		// Movilidad Articular
		if (!empty(trim($this->aDatos[32] ?? '')) || !empty(trim($this->aDatos[33] ?? '')) || !empty(trim($this->aDatos[34] ?? '')) || !empty(trim($this->aDatos[35] ?? ''))){

			$laDatos=[];
			$laTr[] = ['titulo1', 'MOVILIDAD ARTICULAR'] ;
			$laAnchos=[95,95] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['MMSS Derecho: '.$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[32])),
												'MMSS Izquierdo: '.$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[33]))
										   ],'a'=>'L'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['MMII Derecho: '.$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[34])),
												'MMII Izquierdo : '.$this->fnDescripcionPrmTab('BRM'.trim($this->aDatos[35]))
										   ],'a'=>'L'];

			$laTr[]= ['tablaSL',	[ ],$laDatos,];
		}

		if (!empty(trim($this->aDatos[36] ?? ''))){
			$laTr[] = ['titulo2', 'OBSERVACIONES'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[36])];
		}

		// Lateralidad
		if (!empty(trim($this->aDatos[37] ?? ''))){
			$laTr[] = ['txthtml9', '<b>LATERALIDAD: </b>'.$this->fnDescripcionPrmTab('LTR'.trim($this->aDatos[37]))];
		}

		// Actividad Refleja Anormal
		if (!empty(trim($this->aDatos[38] ?? ''))){
			$laTr[] = ['txthtml9', '<b>ACTIVIDAD REFLEJA ANORMAL: </b>'.$this->fnDescripcionPrmTab('ATR'.trim($this->aDatos[38]))];
		}

		$lcTemp = $this->InsertarReaccEquilibrio(40) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// Sistema Neuromuscular
		$lcTemp = $this->InsertarNeuromuscular(44) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

	 	// Reflejos en el niño
		$laDatos = [] ;
		$laAnchos = [63,64,63];
		$llImprimir = false ;
		 foreach($this->aRefleja as $laRefleja){
			if($laRefleja['USAR']=='1'){
				$llImprimir = true ;
				$laDatos[] = ['w'=>$laAnchos, 'd'=>[$laRefleja['EDAD'],$laRefleja['REFLEJO'],($laRefleja['P1']=='0'?'NO INTEGRADO':'INTEGRADO')
												   ],'a'=>'L'];
			}
		}

		if($llImprimir){
			$laTr[] = ['titulo1', 'ACTIVIDAD REFLEJA'] ;
			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['EDAD','REFLEJO','VALOR'], 'a'=>'C' ] ],
				$laDatos,
				];
		}

		// FLEXIBILIDAD
		if (!empty(trim($this->aDatos[49] ?? ''))){
			$laTr[] = ['titulo2', 'FLEXIBILIDAD'] ;
			$laTr[] = ['texto9',	trim($this->aDatos[49])];
		}

		// Actividad Motora Voluntaria
		if (!empty(trim($this->aDatos[50] ?? '')) || !empty(trim($this->aDatos[51] ?? '')) ||
			!empty(trim($this->aDatos[52] ?? '')) || !empty(trim($this->aDatos[53] ?? '')) ||
			!empty(trim($this->aDatos[54] ?? '')) || !empty(trim($this->aDatos[55] ?? '')) ||
			!empty(trim($this->aDatos[56] ?? ''))){

			$laDatos=[];
			$laTr[] = ['titulo1', 'ACTIVIDAD MOTORA VOLUNTARIA'] ;
			$laAnchos=[95,95] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Control Cefálico: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[50])),
												'Supino a Promo: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[51]))
										   ],'a'=>'L'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Prono a Supino: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[52])),
												'Supino a Sedente: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[53]))
										   ],'a'=>'L'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Sedente a Cuadrupedo: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[54])),
												'Cuadrupedo a Rodillas: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[55]))
										   ],'a'=>'L'];
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Rodillas a Bipedo: '.$this->fnDescripcionPrmTab('AMV'.trim($this->aDatos[56])),
												''
										   ],'a'=>'L'];

			$laTr[]= ['tablaSL',	[ ],$laDatos,] ;
		}

		// Movimientos Anormales
		if (!empty(trim($this->aDatos[57] ?? '')) || !empty(trim($this->aDatos[58] ?? '')) ||
 			!empty(trim($this->aDatos[59] ?? '')) || !empty(trim($this->aDatos[60] ?? '')) ||
			!empty(trim($this->aDatos[61] ?? '')) || !empty(trim($this->aDatos[62] ?? '')) ||
			!empty(trim($this->aDatos[63] ?? ''))){

			$laDatos=[];
			$laTr[] = ['titulo1', 'MOVIMIENTOS ANORMALES'] ;
			$laAnchos=[63,64,63] ;
			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Temblor...: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[57])),
												'Corea.....:   '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[58])),
												'Tics..........: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[59])
												)
												],'a'=>'L'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Balismo...: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[60])),
												'Ataxia....:   '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[61])),
												'Estereotipados: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[62])
												)
												],'a'=>'L'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Atetosis..: '.$this->fnDescripcionPrmTab('SON'.trim($this->aDatos[63])),
												'', ''
												],'a'=>'L'];

			$laTr[]= ['tablaSL',	[ ],$laDatos,] ;

		}

		//Valoración Funcional
		$lcTemp = $this->InsertarValoracionFunc(64,65) ;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		//Diagnostico e Intervencion
		$lcTemp = $this->InsertarDiagnosticos(66,67,71)	;
		if(!empty($lcTemp)){
			$laTr = array_merge($laTr, $lcTemp) ;
		}

		// FIRMA
		$laFirma=['usuario'=>$this->aDatos[97]];
		$laTr[] = ['firmas', [ $laFirma, ] ];

		// Notas Aclaratorias
		$laTr=array_merge($laTr, $this->InsertarNotas($taData, 'H'));

		$this->aReporte['aCuerpo'] = array_merge($this->aReporte['aCuerpo'], $laTr);
	}


	function fnSepararPropiedades($tcCampos, $tcDescripcion, $tlClaves=true)
	{
		$lcSalto = explode('¤',trim($tcCampos));
		$lnPosicion = 0;

		foreach($lcSalto as $laRegs)
		{
			$laWordsItm = explode(',', $laRegs);
			$lnKey = intval($laWordsItm[0]);
			if($tlClaves==true){
				$lcKey = $this->fnObtenerClaves($lnKey);
			} else {
				$lcKey = $lnKey;
			}
			$lnCampoLong = intval($laWordsItm[1]);
			$lcCampoTipo = $laWordsItm[2];
			$lcValor = substr($tcDescripcion,$lnPosicion,$lnCampoLong);

			switch (true)
			{
				case $lcCampoTipo=='T' :
					$lcFecha = substr($lcValor,0,8);
					$lcHora = substr($lcValor,8,6);
					$lcDato = AplicacionFunciones::formatFechaHora('fechahora12', $lcFecha.' '.$lcHora) ;
					break;

				case $lcCampoTipo=='D' :
					$lcFecha = substr($lcValor,0,8);
					$lcDato = AplicacionFunciones::formatFechaHora('fecha', $lcFecha) ;
					break;

				case $lcCampoTipo=='N' :
					$lcDato = floatval($lcValor);
					break;

				case $lcCampoTipo=='C' :
					$lcDato = trim($lcValor);
					break;
			}

			$this->aDatos[$lcKey] = $lcDato ;
			$lnPosicion+=$lnCampoLong;
		}
	}

	function fnUnirMultilinea($tnKey, $tcDescripcion, $tcIndice,$tlClaves=true)
	{
		if($tlClaves==true){
			$lcKey = $this->fnObtenerClaves($tnKey);
		}
		$this->aDatos[$tnKey]=($this->aDatos[$tnKey]??'').$tcDescripcion ;
	}

	function fnCargarObjetivos($tcObjetivos)
	{
		$lcCodigos = explode(',',$tcObjetivos);

		foreach($lcCodigos as $laCodObj)
		{
			$key = array_search($laCodObj, array_column($this->aObjetivos, 'CODIGO'));
			if (is_numeric($key))
			{
				$this->aObjetivos[$key]['SELECCION'] = '1';
			}
		}
	}

	function fnCargarDermatomas($tcDermatomas)
	{
		$lcCodigos = explode(",",trim($tcDermatomas));
		foreach($lcCodigos as $laCodDer)
		{
			$key = array_search($laCodDer, array_column($this->aDermatomas, 'ID'));
			if (is_numeric($key))
			{
				$this->aDermatomas[$key]['USAR'] = '1';
			}
		}
	}

	function fnCargarAntecedentes($tcAntecedentes)
	{
		if(!empty(trim($tcAntecedentes))){
			$aAntecedentes = $laUsuario = [];
			$lnKey = 0 ;
			$lcCharReg = chr(24);
			$lcCharItm = chr(25);
			$laWordsReg = explode($lcCharReg, $tcAntecedentes);
			foreach($laWordsReg as $laRegs) {
				$lnKey+=1;
				$laWordsItm = explode($lcCharItm, $laRegs);
				$lcDesAnt = AplicacionFunciones::lookup($this->aCodAnt, 'DESANT', intval($laWordsItm[1]), 'CODANT');
				$lnIndAnt = intval($laWordsItm[2]);
				$lcUsrAnt = trim(mb_substr(trim($laWordsItm[3]),0,10));
				$laUsuario = $this->oDb
					->select("REGMED,TRIM(NNOMED)||' '||TRIM(NOMMED) NOMBRE")
					->tabla('RIARGMN')
					->where(['USUARI'=>$lcUsrAnt])
					->get('array');
				$lcNusAnt = trim($laUsuario['NOMBRE']??'');
				$this->aAntecedentes[$lnKey]['SUBANT'] = intval($laWordsItm[1]) ;
				$this->aAntecedentes[$lnKey]['CODANT'] = trim($laWordsItm[0]) ;
				$this->aAntecedentes[$lnKey]['DESANT'] = trim($lcDesAnt) ;
				$this->aAntecedentes[$lnKey]['OBSANT'] = trim($laWordsItm[7]) ;
				$this->aAntecedentes[$lnKey]['OBSANT'] = trim($laWordsItm[7]) ;
				$this->aAntecedentes[$lnKey]['ESTANT'] = 'O';
				$this->aAntecedentes[$lnKey]['USUANT'] = $lcUsrAnt ;
				$this->aAntecedentes[$lnKey]['NUSANT'] = $lcNusAnt ;
				$this->aAntecedentes[$lnKey]['PGMANT'] = trim($laWordsItm[4]);
				$this->aAntecedentes[$lnKey]['FECANT'] = intval($laWordsItm[5]);
				$this->aAntecedentes[$lnKey]['HORANT'] = intval($laWordsItm[6]);
			}
		}
	}

	function fnCargarDiagnosticos($tcDiagnosticos)
	{
		$this->aDiagnosticos= [];
		$lnKey = 0 ;
		$lcCharItm = ',';
		$laWordsReg = explode($lcCharItm, $tcDiagnosticos);

		foreach($laWordsReg as $laRegs) {
			$lnKey+=1;
			$this->aDiagnosticos[$lnKey]['ID'] = $laRegs ;
			$this->aDiagnosticos[$lnKey]['USAR'] = 0 ;

			$key = array_search($laRegs, array_column($this->aDominios, 'ID'));
				if (is_numeric($key)){
					$this->aDiagnosticos[$lnKey]['SISTEMA'] = trim($this->aDominios[$key]['SISTEMA']);
					$this->aDiagnosticos[$lnKey]['LITERAL'] = trim($this->aDominios[$key]['LITERAL']);
					$this->aDiagnosticos[$lnKey]['DOMINIO'] = trim($this->aDominios[$key]['DOMINIO']);
				}
		}
	}

	function fnCargarVFacial($tcFacial)
	{
		if(!empty(trim($tcFacial))){
			$lnId = $lcP1 = '';
			$lcCharReg = ',';
			$lcCharItm = '|';
			$laWordsReg = explode($lcCharReg, $tcFacial);

			foreach($laWordsReg as $laRegs) {
				$laWordsItm = explode($lcCharItm, $laRegs);
				$lnId = $laWordsItm[0];
				$lcP1 = 'P'.trim($laWordsItm[1]);
				if($lcP1!='p0' && $lcP1!='p'){
					$key = array_search($lnId, array_column($this->aFacial, 'ID'));
					if (is_numeric($key)){
						$this->aFacial[$key][$lcP1]='X';
						$this->aFacial[$key]['USAR']='1';
					}
				}
			}
		}
	}

	function fnInformacionGeneral()
	{
		// Información General
		$lcFechaEvalua	 = $this->aDatos[1] ;
		$lcEstConciencia = $this->fnDescripcionPrmTab('ECN'.trim($this->aDatos[2])) ;
		$lcEsferaMental  = $this->fnDescripcionPrmTab('OOD'.trim($this->aDatos[3])) ;

		// Sistema Cardiopulmonar
		$lcFC			= $this->aDatos[5] ?? '';
		$lcFR			= $this->aDatos[6] ?? '';
		$lcTA			= $this->aDatos[7] ?? '';
		$lcT			= $this->aDatos[8] ?? '';
		$lcSPO2			= $this->aDatos[9] ?? '';
		$lcDisnea		= $this->aDatos[10] ?? '';
		$lcAusPulmonar	= $this->fnDescripcionPrmTab('AUP'.trim($this->aAuscultacion[2] ?? ''));
		$lcAusPulmonar	= $this->fnDescripcionPrmTab('AUP'.trim($this->aAuscultacion[1] ?? '')).(empty($lcAusPulmonar)?'':': '.trim($lcAusPulmonar));
		$lcExtToraxica	= $this->fnDescripcionPrmTab('SIM'.trim($this->aDatos[12] ?? ''));
		$lcPatronResp	= $this->fnDescripcionPrmTab('PRS'.trim($this->aDatos[13] ?? ''));
		$lcRitmoResp	= $this->fnDescripcionPrmTab('ROI'.trim($this->aDatos[14] ?? ''));

		$laDatos = $laTabla	= [] ;
		$laDatos[] = ['w'=>[190], 'd'=>['Fecha de Evaluación: '.$this->aDatos[1]],'a'=>['L']];
		$laDatos[] = ['w'=>[95,95], 'd'=>['Estado de Conciencia: '.$lcEstConciencia,'Esfera Mental: '.$lcEsferaMental],'a'=>['L']];
		$laDatos[] = ['w'=>[31,32,32,32,32,31],
					  'd'=>['F.C.: '.$lcFC,'F.R.: '.$lcFR,'T.A.: '.$lcTA,'T.: '.$lcT,'SpO2: '.$lcSPO2,'Disnea: '.$lcDisnea],'a'=>['L']];
		$laDatos[] = ['w'=>[190], 'd'=>['Auscultación Pulmonar: '.$lcAusPulmonar],'a'=>['L']];
		$laDatos[] = ['w'=>[63,64,63], 'd'=>['Exc. Toráxica: '.$lcExtToraxica,'Patrón Resp.: '.$lcPatronResp,'Ritmo Resp.: '.$lcRitmoResp],'a'=>['L']];

	 	$laTabla[] =  ['tabla',
						[ [ 'w'=>190, 'd'=>['Información General'], 'a'=>'L', ] ],
						$laDatos,
						];

		return $laTabla;
	}

	private function InsertarInfoGeneral($tcTitulo)
	{
		$laRetorno = [];
		$laRetorno[] = ['titulo1',	AplicacionFunciones::mb_str_pad($tcTitulo, 85, "-", STR_PAD_BOTH)];

		if (!empty($this->aDatos['cEstadoConciencia']) || !empty($this->aDatos['cEsferaMental'])
			|| !empty($this->aDatos['cAuscultacion']) || !empty($this->aDatos['cExcToraxica'])
			|| !empty($this->aDatos['cPatronRespiratorio']) || !empty($this->aDatos['cRitmoRespiratorio'])
			|| !empty($this->aDatos['cFumar']) || !empty($this->cTipoCirugia)
			|| !empty($this->aDatos['cFrecCardiaca']) || !empty($this->aDatos['cFrecRespiratoria']) || !empty($this->aDatos['cTensionArterial'])
			|| !empty($this->aDatos['cTemperatura']) || !empty($this->aDatos['cSPO2'])
			|| !empty($this->aDatos['cFecEvaluacion'])
		){
			$this->aDatos['cFumar'] = $this->aDatos['cFumar']??'';
			$cTipoCirugia = !empty($this->cTipoCirugia)? $this->aListaParametros['GCX'][$this->cTipoCirugia] :'';
			$this->cAuscultacion1 = intval(substr(str_pad($this->aDatos['cAuscultacion'], 4, "0", STR_PAD_LEFT),0,2));
			$this->cAuscultacion2 = intval(substr(str_pad($this->aDatos['cAuscultacion'], 4, "0", STR_PAD_LEFT),2,2));
			$this->cDescipcionAuscultacion1 = !empty($this->cAuscultacion1)? $this->aListaParametros['AUP'][$this->cAuscultacion1] :'';
			$this->cDescipcionAuscultacion2 = !empty($this->cAuscultacion2)? $this->aListaParametros['AUP'][$this->cAuscultacion2] :'';
			$cExcToraxica = !empty($this->aDatos['cExcToraxica'])? $this->aListaParametros['SIM'][$this->aDatos['cExcToraxica']] :'';
			$cPatronRespiratorio = !empty($this->aDatos['cPatronRespiratorio'])? $this->aListaParametros['PRS'][$this->aDatos['cPatronRespiratorio']] :'';
			$cRitmoRespiratorio = !empty($this->aDatos['cRitmoRespiratorio'])? $this->aListaParametros['ROI'][$this->aDatos['cRitmoRespiratorio']] :'';

			if ($tcTitulo=='VALORACIÓN CIRUGÍA BARIÁTRICA'){
				$cFumar = !empty($this->aDatos['cFumar'])? $this->aListaParametros['SON'][$this->aDatos['cFumar']] :'';
				if ($this->aDatos['cFumar']==1) {
					$this->cDatosFumar = $cFumar .',  Veces al dia: ' .$this->aDatos['cVecesFuma'];
				}
				else{
					$this->cDatosFumar = $cFumar;
				}
				$cAlcohol = !empty($this->aDatos['cAlcohol'])? $this->aListaParametros['SON'][$this->aDatos['cAlcohol']] :'';
				$cEjercicio = !empty($this->aDatos['cEjercicio'])? $this->aListaParametros['SON'][$this->aDatos['cEjercicio']] :'';
			}

			if (!empty($this->aDatos['cFecEvaluacion'])){
				$laTbl[] = ['w'=>192, 'd'=>[
					'Fecha de evaluación: ' .$this->aDatos['cFecEvaluacion']]];
			}

			if (!empty($this->aDatos['cEstadoConciencia']) || !empty($this->aDatos['cEsferaMental'])
				){
				$cEstadoConciencia = !empty($this->aDatos['cEstadoConciencia'])? $this->aListaParametros['ECN'][$this->aDatos['cEstadoConciencia']] :'';
				$cEsferaMental = !empty($this->aDatos['cEsferaMental'])? $this->aListaParametros['OOD'][$this->aDatos['cEsferaMental']] :'';

				$laTbl[] = ['w'=>96, 'd'=>[
					'Estado de Conciencia: ' .$cEstadoConciencia , 'Esfera Mental: ' .$cEsferaMental]];
			}

			if ($tcTitulo=='VALORACIÓN CIRUGÍA BARIÁTRICA'){
				$laTbl[] = ['w'=>64, 'd'=>[
					'Fuma: '.$this->cDatosFumar , 'Alcohol: '.$cAlcohol , 'Hace Ejercicio: '.$cEjercicio ]];

				if (!empty($cTipoCirugia)){
					$laTbl[] = ['w'=>192, 'd'=>[
						'Tipo de Cirugia: '.$cTipoCirugia ]];
				}
			}

			if (!empty($this->aDatos['cFrecCardiaca']) || !empty($this->aDatos['cFrecRespiratoria']) || !empty($this->aDatos['cTensionArterial']) ||
				!empty($this->aDatos['cTemperatura']) || !empty($this->aDatos['cSPO2'])){
				$laTbl[] = ['w'=>32, 'd'=>[
					'F.C.: '.$this->aDatos['cFrecCardiaca'] , 'F.R.: '.$this->aDatos['cFrecRespiratoria'] , 'T.A.: '.$this->aDatos['cTensionArterial'] ,
					'T.: '.$this->aDatos['cTemperatura'] , 'SpO2: '.$this->aDatos['cSPO2'] , 'Disnea: ' .$this->aDatos['cDisnea']]];
			}

			if (!empty($this->cDescipcionAuscultacion1)){
				$this->cDescipcionAuscultacion2 = !empty($this->cDescipcionAuscultacion2)? ' : ' .$this->cDescipcionAuscultacion2 : '';
				$laTbl[] = ['w'=>192, 'd'=>[
					'Auscultación Pulmonar: ' .$this->cDescipcionAuscultacion1 .$this->cDescipcionAuscultacion2]];
			}

			if (!empty($cExcToraxica) || !empty($cPatronRespiratorio) || !empty($cRitmoRespiratorio)){
				$laTbl[] = ['w'=>64, 'd'=>[
					'Exc.Toráxica: '.$cExcToraxica , 'Patron Respiratorio: '.$cPatronRespiratorio , 'Ritmo Respiratorio: '.$cRitmoRespiratorio ]];
			}
			$laRetorno[] = ['tabla', [], $laTbl];
		}
		return $laRetorno;
	}

	function fnInsertarAntecedente()
	{
		$laDatos = [];
		$lltitulo = true ;
		foreach($this->aAntecedentes as $laAntec){
			if(!empty(trim($laAntec['OBSANT']))){
				if($lltitulo){
					$lltitulo = false ;
					$laDatos[] = ['titulo1', 'ANTECEDENTES'] ;
				}
				$laDatos[] = ['titulo2', $laAntec['DESANT']];
				$laDatos[] = ['texto9',	trim($laAntec['OBSANT'])];
			}
		}
		return $laDatos ;
	}

	private function fnInsertarAntecedentes($tcDescripcionAntecedente)
	{
		$laRetorno = [];

		if (!empty(trim($tcDescripcionAntecedente))){
			$lcChrRec = CHR(24);
			$lcChrItm  = CHR(25);
			$nCuentaAntecedentes = 0;
			$laRetorno[] = ['titulo2', 'ANTECEDENTES'];
			$aAntecedentes = explode($lcChrRec, $tcDescripcionAntecedente);
			$aDescripcionAntecedente = [];

			foreach($aAntecedentes as $laDataAntecedente) {
				$aListaAntecedentes = explode($lcChrItm, $laDataAntecedente);
				$aListaAntecedentes[] = $this->aTipoAntecedentes[$aListaAntecedentes['1']];
				$aDescripcionAntecedente[] = $aListaAntecedentes;
			}

			foreach($aDescripcionAntecedente as $laDescAntecedente) {
				$laRetorno[] = ['titulo3', $laDescAntecedente[8]];
				$laRetorno[] = ['texto9', trim($laDescAntecedente[7])];
			}
		}
		return $laRetorno;
	}

	function fnInsertarObservacionGeneral($tcObservacion)
	{
		$laDatos = [];
		if(!empty($tcObservacion)){
			$laDatos[] = ['titulo1', 'OBSERVACIÓN GENERAL'];
			$laDatos[] = ['texto9',	$tcObservacion];
		}
		return $laDatos ;
	}

	function fnInsertarObservaciones($tcObservaciones)
	{
		$laDatos = [];
		if(!empty($tcObservaciones)){
			$laDatos[] = ['titulo1', 'OBSERVACIONES '];
			$laDatos[] = ['texto9',	$tcObservaciones];
		}
		return $laDatos ;
	}

	private function fnInsertarObservacion($tcObservacion)
	{
		$laRetorno = [];
		if (!empty(trim($tcObservacion))){
			$laRetorno[] = ['titulo3',	'OBSERVACIÓN GENERAL'];
			$laRetorno[] = ['texto9',	trim($tcObservacion)];
		}
		return $laRetorno;
	}

	function fnInsertarLenguaje($tcLenguaje)
	{
		$laDatos = [];
		if(!empty($tcLenguaje)){
			$laDatos[] = ['titulo1', 'LENGUAJE Y COMUNICACION'];
			$laDatos[] = ['texto9',	$tcLenguaje];
		}
		return $laDatos ;
	}

	private function fnInsertarLenguajeCom($tcLenguajeComunicacion)
	{
		$laRetorno = [];
		if (!empty(trim($tcLenguajeComunicacion))){
			$laRetorno[] = ['titulo2', 'LENGUAJE Y COMUNICACIÓN'];
			$laRetorno[] = ['texto9', $tcLenguajeComunicacion];
		}
		return $laRetorno;
	}

	function fnInsertarActitud($tcActitud)
	{
		$laDatos = [];
		if(!empty($tcActitud)){
			$laDatos[] = ['titulo1', 'ACTITUD(COLABORACIÓN)'];
			$laDatos[] = ['texto9',	$tcActitud];
		}
		return $laDatos ;
	}

	private function fnInsertarDolor($tnTipoDolor)	{
		$laRetorno = [];

		if ($this->aDatos['cIntensidadDolor']>0  || $this->cDolorLocalizacion>0 || $this->cDolorFrecuencia>0 || !empty(trim($this->aDatos['cTipoDolor']))
			|| !empty(trim($this->cObservacionesDolor)) || $this->aDatos['cSensibilidadDolor']>0
		){
			$laRetorno[] = ['titulo2',	'DOLOR'];
			if (!empty(trim($this->aDatos['cIntensidadDolor'])) || !empty(trim($this->aDatos['cTipoDolor']))  || empty(trim($this->aDatos['cSensibilidadDolor']))) {
				$cTipoDolor = !empty($this->aDatos['cTipoDolor']) && $this->aDatos['cTipoDolor'] !='5'? $this->aListaParametros['TPD'][$this->aDatos['cTipoDolor']] :'';
				$cSensibilidadDolor = !empty($this->aDatos['cSensibilidadDolor'])? $this->aListaParametros['FSN'][$this->aDatos['cSensibilidadDolor']] :'';

				$lcDescripIntensidad = (!empty($this->aDatos['cIntensidadDolor'])? ('Intensidad: ' .$this->aListaParametros['DOL'][$this->aDatos['cIntensidadDolor']]) : ' ')
										.(!empty($cTipoDolor)? '        Tipo de dolor: ' .$cTipoDolor : '')
										.(!empty($cSensibilidadDolor)? '        Sensibilidad: ' .$cSensibilidadDolor : '');

				if (!empty(trim($lcDescripIntensidad))){
					$laRetorno[] = ['texto9',	trim($lcDescripIntensidad)];
				}
			}

			if ($this->cDolorLocalizacion>0 || $this->cDolorFrecuencia>0  || !empty(trim($this->aDatos['cPresentaNauceas']))) {
				$lcDescripLocalizacion = (!empty($this->aDatos['cDolorLocalizacion'])? ('Localización: ' .$this->aListaParametros['DLC'][$this->aDatos['cDolorLocalizacion']]) : ' ')
										 .($this->cDolorFrecuencia>0? ('         Frecuencia: ' .$this->aListaParametros['DFC'][$this->cDolorFrecuencia]) :'')
										 .(!empty($this->aDatos['cPresentaNauceas'])? ('           Presenta Nauseas: ' .($this->aDatos['cPresentaNauceas']=='1'? 'SI' : 'NO')) :'');

				if (!empty(trim($lcDescripLocalizacion))){
					$laRetorno[] = ['texto9',	trim($lcDescripLocalizacion)];
				}
			}

			if (!empty(trim($this->cObservacionesDolor))) {

				if ($tnTipoDolor==2){
					$lcTituloObservacionesDolor = 'OBSERVACIONES';
				}else{
					$lcTituloObservacionesDolor = 'LOCALIZACION Y FRECUENCIA DEL DOLOR';
				}
				$laRetorno[] = ['titulo3',	$lcTituloObservacionesDolor];
				$laRetorno[] = ['texto9',	trim($this->cObservacionesDolor)];
			}
		}
		return $laRetorno;
	}

	private function fnInsertarEspasmo(){
		$laRetorno = [];
		if (!empty($this->aDatos['cTipoEspasmo'])){
			$cTipoEspasmo = !empty($this->aDatos['cTipoEspasmo'])? $this->aListaParametros['POA'][$this->aDatos['cTipoEspasmo']] :'';
			$laRetorno[] = ['titulo3',	'ESPASMOS : ' .trim($cTipoEspasmo) ];
		}

		if (!empty(trim($this->cObsEspasmo))) {
				$laRetorno[] = ['texto9',	trim($this->cObsEspasmo)];
		}
		return $laRetorno;
	}

	private function fnInsertarSensibilidad(){
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cSensibilidadSuperficial'])) || !empty(trim($this->aDatos['cSensibilidadProfunda'])) || !empty(trim($this->cDermatomasSeleccionados))
			){

			$laRetorno[] = ['titulo2',	'SENSIBILIDAD'];

			if (!empty(trim($this->cDermatomasSeleccionados))){
				$laRetorno[] = ['titulo3',	'DERMATOMAS'];
				$lcTexto ='';
				$lnCantidadExplode = 0;
				$lnContador = 0;

				$aDermatoma = explode(',', $this->cDermatomasSeleccionados);
				$lnCantidadExplode = count($aDermatoma);
				foreach($aDermatoma as $laDataDermatoma) {
					$lnContador = $lnContador + 1;
					$lcTexto .= $lnCantidadExplode==$lnContador?$this->aListaDermatomas[$laDataDermatoma] ."": $this->aListaDermatomas[$laDataDermatoma] .", ";
				}
				$laRetorno[] = ['texto9',	$lcTexto];
			}

			if (!empty(trim($this->aDatos['cSensibilidadSuperficial'])) || !empty(trim($this->aDatos['cSensibilidadProfunda']))){
				$lcDescripcioSensibilidad = (!empty($this->aDatos['cSensibilidadSuperficial'])? ('Superficial: ' .$this->aListaParametros['CNV'][$this->aDatos['cSensibilidadSuperficial']]) : ' ')
											.(!empty($this->aDatos['cSensibilidadProfunda'])? ('              Profunda: ' .$this->aListaParametros['CNV'][$this->aDatos['cSensibilidadProfunda']]) : ' ');

				if (!empty(trim($lcDescripcioSensibilidad))){
					$laRetorno[] = ['texto9',	trim($lcDescripcioSensibilidad)];
				}
			}
		}
		return $laRetorno;
	}

	function fnDescripcionPrmTab($tcCodigo)
	{
		$lcDescripcion = '';
		$key = array_search($tcCodigo, array_column($this->aPrmTab, 'CODIGO'));
		if (is_numeric($key))
		{
			$lcDescripcion=trim($this->aPrmTab[$key]['TABDSC']);
		}
		Return $lcDescripcion ;
	}

	private function fnAntropometricas()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cPeso'])) || !empty(trim($this->aDatos['cTalla'])) || !empty(trim($this->aDatos['cCintura']))){

			$laRetorno[] = ['titulo2',	'CARACTERÍSTICAS ANTROPOMÉTRICAS'];
			$laRetorno[] = ['titulo3',	'INDICE DE MASA CORPORAL - IMC'];

			$lnImcInferior = 0;
			$lcClasificacionImc=$lnImcInferiorClas=$lnImcSuperiorClas=$lcImcClasificacion=$lcImcRiesgo='';
			$lnTallaImc = $this->aDatos['cTalla']/100;
			$lcIndiceMasaCorporal = round($this->aDatos['cPeso']/(pow($lnTallaImc, 2)), 2);

			if (!empty(trim($this->aDatos['cTalla']))){

				foreach($this->aConsultaImc as $laConsultaImc)
				{
					if ($laConsultaImc['ID']=='1002'){
						$lnImcInferior = $laConsultaImc['IMCINF'];
						$lnImcSuperior = $laConsultaImc['IMCSUP'];
					}

					if ($lcIndiceMasaCorporal > $laConsultaImc['IMCINF'] && $lcIndiceMasaCorporal < $laConsultaImc['IMCSUP']){
						$lcClasificacionImc =  trim($laConsultaImc['CLASIFICACION']);
						$lnImcInferiorClas = $laConsultaImc['IMCINF'];
						$lnImcSuperiorClas = $laConsultaImc['IMCSUP'];
					}
				}
			}

			if (!empty(trim($lcClasificacionImc))){
				$lcImcClasificacion = explode(',',$lcClasificacionImc)[0];
				$lcImcRiesgo = explode(',',$lcClasificacionImc)[1];
			}

			$PesoIdealInferior = round($lnImcInferior*(pow($lnTallaImc, 2)));
			$PesoIdealSuperior = round($lnImcSuperior*(pow($lnTallaImc, 2)));

			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>['Peso (Kg)'  , 'Talla (cm)' , 'IMC = Peso/(Talla^2)', 'Peso Ideal' ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'a'=>'C','d'=>[$this->aDatos['cPeso']  , $this->aDatos['cTalla'] , $lcIndiceMasaCorporal, $PesoIdealInferior .' ~ ' .$PesoIdealSuperior . ' Kg']];

			if (!empty(trim($lcImcClasificacion))  || !empty(trim($lcImcRiesgo)) ||
				!empty(trim($lnImcInferiorClas))  || !empty(trim($lnImcSuperiorClas))
				){
				$lcTextoClasificacion = '{' .$lnImcInferiorClas  .' - ' .$lnImcSuperiorClas .'}  ' .'Clasificación ' .$lcImcClasificacion .' - Riesgo ' .$lcImcRiesgo;
				$laTbl[] = ['w'=>[180, ], 'a'=>'C','d'=>[$lcTextoClasificacion]];
			}
			$laRetorno[] = ['tabla', [], $laTbl];

			if (!empty(trim($this->aDatos['cCintura']))) {
				$laRetorno[] = ['txthtml9',	'<b>CIRCUNFERENCIA CINTURA: </b>' .$this->aDatos['cCintura'] .'(cm)'];
			}
		}
		return $laRetorno;
	}


	private function fnSistemaNeuroMuscular()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cSisNeuroMuscEquilibrio'])) || !empty(trim($this->aDatos['cSisNeuroMuscCoordinacion'])) ||
			!empty(trim($this->aDatos['cSisNeuroMuscDiadococinecia'])) || !empty(trim($this->aDatos['cSisNeuroMuscInestabilidadPostural']))){

			$cSisNeuroMuscEquilibrio = !empty($this->aDatos['cSisNeuroMuscEquilibrio'])? $this->aListaParametros['CNV'][$this->aDatos['cSisNeuroMuscEquilibrio']] :'';
			$cSisNeuroMuscCoordinacion = !empty($this->aDatos['cSisNeuroMuscCoordinacion'])? $this->aListaParametros['CNV'][$this->aDatos['cSisNeuroMuscCoordinacion']] :'';
			$cSisNeuroMuscDiadococinecia = !empty($this->aDatos['cSisNeuroMuscDiadococinecia'])? $this->aListaParametros['ANA'][$this->aDatos['cSisNeuroMuscDiadococinecia']] :'';
			$cSisNeuroMuscInestabilidadPostural = !empty($this->aDatos['cSisNeuroMuscInestabilidadPostural'])? $this->aListaParametros['POA'][$this->aDatos['cSisNeuroMuscInestabilidadPostural']] :'';

			if (!empty(trim($this->aDatos['cSisNeuroMuscRomberg']))) {
				$cSisNeuroMuscRomberg = $this->aDatos['cSisNeuroMuscRomberg']=='1'? '+' : '-';
			}else{
				$cSisNeuroMuscRomberg = 'NO VALORABLE';
			}

			$laRetorno[] = ['titulo2',	'SISTEMA NEUROMUSCULAR'];
			$laTbl[] = ['w'=>[35, 35, 35, 35, 35,], 'a'=>'C', 'd'=>['Romberg'  , 'Equilibrio' , 'Coordinación', 'Diadococinecia', 'Inesta.Postural' ]];
			$laTbl[] = ['w'=>[35, 35, 35, 35, 35,], 'a'=>'C','d'=>[$cSisNeuroMuscRomberg  , $cSisNeuroMuscEquilibrio , $cSisNeuroMuscCoordinacion, $cSisNeuroMuscDiadococinecia, $cSisNeuroMuscInestabilidadPostural ]];
			$laRetorno[] = ['tabla', [], $laTbl];
		}
		return $laRetorno;
	}

	private function fnSistemaIntegumentario()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cSisIntegumentarioTrofica'])) || !empty(trim($this->aDatos['cSisIntegumentarioMecanica'])) ||
			!empty(trim($this->aDatos['cSisIntegumentarioEscaras'])) || !empty(trim($this->aDatos['cSisIntegumentarioEdema'])) || !empty(trim($this->cObsSistemaIntegumentario))){

			$laRetorno[] = ['titulo2',	'SISTEMA INTEGUMENTARIO'];

			$cSisIntegumentarioTrofica = !empty($this->aDatos['cSisIntegumentarioTrofica'])? $this->aListaParametros['CNV'][$this->aDatos['cSisIntegumentarioTrofica']] :'';
			$cSisIntegumentarioMecanica = !empty($this->aDatos['cSisIntegumentarioMecanica'])? $this->aListaParametros['CNV'][$this->aDatos['cSisIntegumentarioMecanica']] :'';
			$cSisIntegumentarioEscaras = !empty($this->aDatos['cSisIntegumentarioEscaras'])? $this->aListaParametros['POA'][$this->aDatos['cSisIntegumentarioEscaras']] :'';
			$cSisIntegumentarioEdema = !empty($this->aDatos['cSisIntegumentarioEdema'])? $this->aListaParametros['EDE'][$this->aDatos['cSisIntegumentarioEdema']] :'';

			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>['Propiedades Tróficas'  , 'Propiedades Mecánicas' , 'Escaras', 'Edema' ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'a'=>'C','d'=>[$cSisIntegumentarioTrofica, $cSisIntegumentarioMecanica , $cSisIntegumentarioEscaras, $cSisIntegumentarioEdema ]];
			$laRetorno[] = ['tabla', [], $laTbl];

			if (!empty(trim($this->cObsSistemaIntegumentario))) {
				$laRetorno[] = ['titulo3',	'OBSERVACIONES'];
				$laRetorno[] = ['texto9',	trim($this->cObsSistemaIntegumentario)];
			}
		}
		return $laRetorno;
	}


	private function fnValoracion()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cMinuto1Fc']))  || !empty(trim($this->aDatos['cMinuto1SpO2']))
			|| !empty(trim($this->aDatos['cMinuto2Fc']))  || !empty(trim($this->aDatos['cMinuto2SpO2']))
			|| !empty(trim($this->aDatos['cMinuto3Fc']))  || !empty(trim($this->aDatos['cMinuto2SpO2']))
			|| !empty(trim($this->aDatos['cMinuto4Fc']))  || !empty(trim($this->aDatos['cMinuto3SpO2']))
			|| !empty(trim($this->aDatos['cMinuto5Fc']))  || !empty(trim($this->aDatos['cMinuto4SpO2']))
			|| !empty(trim($this->aDatos['cMinuto6Fc']))  || !empty(trim($this->aDatos['cMinuto5SpO2']))
			|| !empty(trim($this->aDatos['cCaminataValorable']))  || !empty(trim($this->aDatos['cCaminataCausa']))
			|| !empty(trim($this->cObsCaminata))
			){

			$laRetorno[] = ['titulo2',	'VALORACION'];

			if (!empty(trim($this->aDatos['cCaminataValorable'])) || (!empty(trim($this->aDatos['cCaminataCausa'])))) {
				$cCaminataCausa = !empty($this->aDatos['cCaminataCausa'])? ('      				Causa: ' . $this->aListaParametros['CAU'][$this->aDatos['cCaminataCausa']]) :'';
				$lcDescCaminata = (empty(trim($this->aDatos['cCaminataValorable']))? ' ': (trim($this->aDatos['cCaminataValorable'])=='1'? 'Valorable' : 'No Valorable'))
									. (empty(trim($this->aDatos['cCaminataCausa']))? ' ': $cCaminataCausa)
									;
				$laRetorno[] = ['titulo4',	'CAMINATA DE 6 MINUTOS'];
				$laRetorno[] = ['texto9',	trim($lcDescCaminata)];
			}

			if (!empty(trim($this->cObsCaminata))) {
				$laRetorno[] = ['titulo4',	'CAPACIDAD AEROBICA / RESISTENCIA'];
				$laRetorno[] = ['texto9',	trim($this->cObsCaminata)];
			}

			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'd'=>['<b>TA</b>'  , '<b>FR</b>' , '<b>FC</b>', '<b>SpO2</b>',]];
			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'd'=>[$this->cCaminataTa  , $this->aDatos['cCaminataFr'] , $this->aDatos['cCaminataFc'], $this->aDatos['cCaminataSpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , '<b>MINUTO</b>', '<b>FC</b>', '<b>SpO2</b>']];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 1 ' , $this->aDatos['cMinuto1Fc'], $this->aDatos['cMinuto1SpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 2 ' , $this->aDatos['cMinuto2Fc'], $this->aDatos['cMinuto2SpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 3 ' , $this->aDatos['cMinuto3Fc'], $this->aDatos['cMinuto3SpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 4 ' , $this->aDatos['cMinuto4Fc'], $this->aDatos['cMinuto4SpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 5 ' , $this->aDatos['cMinuto5Fc'], $this->aDatos['cMinuto4SpO2'] ]];
			$laTbl[] = ['w'=>[45, 45, 45, 45, ], 'd'=>[' '  , 'MINUTO 6 ' , $this->aDatos['cMinuto6Fc'], $this->aDatos['cMinuto5SpO2'] ]];
			$laRetorno[] = ['tablaSl', [], $laTbl];
		}
		return $laRetorno;
	}


	private function fnValoracionFuncional()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cAvdIndependiente'])) || !empty(trim($this->aDatos['cAvdBanarse'])) || !empty(trim($this->aDatos['cAvdComer']))
			|| !empty(trim($this->aDatos['cAvdVestirse'])) || !empty(trim($this->aDatos['cAvdTraslado'])) || !empty(trim($this->aDatos['cAvdCambioPosicion']))
			|| !empty(trim($this->cObsValoracionAvd)) || !empty(trim($this->aDatos['cAvidIndependiente'])) || !empty(trim($this->aDatos['cAvidTrabajo']))
			|| !empty(trim($this->aDatos['cAvidVidaSocial'])) || !empty(trim($this->cObsValoracionAvid))){

			$lcAvdIndependiente = (empty(trim($this->aDatos['cAvdIndependiente']))? ' ': ('Independiente: ' .(trim($this->aDatos['cAvdIndependiente'])=='1'? 'Si' : 'No')))
									. (!empty(trim($this->aDatos['cAvdBanarse'])) || !empty(trim($this->aDatos['cAvdComer'])) || !empty(trim($this->aDatos['cAvdVestirse']))
									|| !empty(trim($this->aDatos['cAvdTraslado'])) || !empty(trim($this->aDatos['cAvdCambioPosicion'])) ? '   Para: ' : ' ')
									. (empty(trim($this->aDatos['cAvdBanarse']))? ' ': ' - Bañarse')
									. (empty(trim($this->aDatos['cAvdComer']))? ' ': ' - Comer')
									. (empty(trim($this->aDatos['cAvdVestirse']))? ' ': ' - Vestirse')
									. (empty(trim($this->aDatos['cAvdTraslado']))? ' ': ' - Traslado')
									. (empty(trim($this->aDatos['cAvdCambioPosicion']))? ' ': ' - Cambio Posición')
									;

			$lcAvid = (empty(trim($this->aDatos['cAvidIndependiente']))? ' ': ('Independiente: ' .(trim($this->aDatos['cAvidIndependiente'])=='1'? 'Si' : 'No')))
									. (!empty(trim($this->aDatos['cAvidTrabajo'])) || !empty(trim($this->aDatos['cAvidVidaSocial']))? '   Para: ' : ' ')
									. (empty(trim($this->aDatos['cAvidTrabajo']))? ' ': ' - Trabajo')
									. (empty(trim($this->aDatos['cAvidVidaSocial']))? ' ': ' - Vida Social')
									;

			$laRetorno[] = ['titulo2',	'VALORACIÓN FUNCIONAL'];

			if (!empty(trim($lcAvdIndependiente)) || !empty(trim($this->cObsValoracionAvd))){
				$laRetorno[] = ['titulo3',	'AVD'];

				if (!empty(trim($lcAvdIndependiente))){
					$laRetorno[] = ['texto9',	$lcAvdIndependiente];
				}

				if (!empty(trim($this->cObsValoracionAvd))) {
					$laRetorno[] = ['texto9',	trim($this->cObsValoracionAvd)];
				}
			}

			if (!empty(trim($lcAvid)) || !empty(trim($this->cObsValoracionAvid))){
				$laRetorno[] = ['titulo3',	'AVID'];
				if (!empty(trim($lcAvid))){
					$laRetorno[] = ['texto9',	$lcAvid];
				}

				if (!empty(trim($this->cObsValoracionAvid))) {
					$laRetorno[] = ['texto9',	trim($this->cObsValoracionAvid)];
				}
			}
		}
		return $laRetorno;
	}


	private function fnInsertarPropiocepcion()
	{
		$laRetorno = [];
		if (!empty(trim($this->aDatos['cPropiocepcionEstatico'])) || !empty(trim($this->aDatos['cPropiocepcionDinamico'])) || !empty(trim($this->cObsPropiocepcion))){
			if (!empty(trim($this->aDatos['cPropiocepcionEstatico'])) || !empty(trim($this->aDatos['cPropiocepcionDinamico']))){
				$lcPropiocepcionEstatico = !empty($this->aDatos['cPropiocepcionEstatico'])? ('ESTÁTICA: ' .$this->aListaParametros['CNV'][$this->aDatos['cPropiocepcionEstatico']]) :'';
				$lcPropiocepcionDinamico = !empty($this->aDatos['cPropiocepcionDinamico'])? ('DINÁMICA: ' .$this->aListaParametros['CNV'][$this->aDatos['cPropiocepcionDinamico']]) :'';
				$laRetorno[] = ['titulo2',	'PROPIOCEPCIÓN'];
				$laRetorno[] = ['texto9',	trim($lcPropiocepcionEstatico) .' - ' . trim($lcPropiocepcionDinamico)];
			}

			if (!empty(trim($this->cObsPropiocepcion))) {
					$laRetorno[] = ['texto9',	trim($this->cObsPropiocepcion)];
				}
		}
		return $laRetorno;
	}

	function InsertarPropiocepcion($tnEstatica,$tnDinamica,$tnDatos)
	{
		$laDatos = [];
		$lcSL = $this->cSL ;

		if (!empty(trim($this->aDatos[$tnEstatica] ?? '')) || !empty(trim($this->aDatos[$tnDinamica] ?? ''))){

			$lcEstatica	= 'ESTÁTICA: '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnEstatica]));
			$lcDinamica	= 'DINÁMICA: '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnDinamica]));
			$lcTexto = trim($lcEstatica).' - '.trim($lcDinamica) ;
			if($tnDatos>0){
				$lcTexto .= !empty(trim($this->aDatos[$tnDatos] ?? ''))?$lcSL.($this->aDatos[$tnDatos] ?? ''):'';
			}
			if (!empty(trim($lcTexto))){
				$laDatos[] = ['titulo1', 'PROPIOCEPCIÓN'] ;
				$laDatos[] = ['texto9',	trim($lcTexto)];
			}
		}
		return $laDatos;
	}


	private function fnInsertarValoracionColor()
	{
		$laRetorno = [];
		if (!empty(trim($this->aDatos['cColorPielRojaEritema'])) || !empty(trim($this->aDatos['cColorPielRojaEquimosis'])) || !empty(trim($this->aDatos['cColorPielRosa'])) || !empty(trim($this->aDatos['cColorPielAmarillo']))
			|| !empty(trim($this->aDatos['cColorPielVerde'])) || !empty(trim($this->aDatos['cColorPielAzul'])) || !empty(trim($this->aDatos['cColorPielMarron'])) || !empty(trim($this->aDatos['cColorPielNegro']))
			|| !empty(trim($this->aDatos['cPropTroficasTemperatura'])) || !empty(trim($this->aDatos['cPropTroficasPulsos'])) || !empty(trim($this->aDatos['cPropTroficasSudoriperas'])) || !empty(trim($this->aDatos['cPropTroficasSebaceas']))
			|| !empty(trim($this->aDatos['cPropTroficasCicatriz']))	|| !empty(trim($this->aDatos['cPropTroficasSensibilidad'])) || !empty(trim($this->aDatos['cPropMecanicasElasticidad'])) || !empty(trim($this->aDatos['cPropMecanicasGrosor']))
			|| !empty(trim($this->aDatos['cPropMecanicasExtensibilidad'])) || !empty(trim($this->aDatos['cPropMecanicasMovilidad'])) || !empty(trim($this->aDatos['cPropMecanicasEdema'])) || !empty(trim($this->aDatos['cPropMecanicasManchas']))
			|| !empty(trim($this->aDatos['cPropMecanicasEscaras'])) || !empty(trim($this->aDatos['cPropMecanicasUnas'])) || !empty(trim($this->aDatos['cPropMecanicasPelo'])) || !empty(trim($this->cObservacionesColorPiel)))
		{

			$laRetorno[] = ['titulo2',	'VALORACIÓN'];

			if (!empty(trim($this->aDatos['cColorPielRojaEritema'])) || !empty(trim($this->aDatos['cColorPielRojaEquimosis'])) || !empty(trim($this->aDatos['cColorPielRosa'])) || !empty(trim($this->aDatos['cColorPielAmarillo']))
			|| !empty(trim($this->aDatos['cColorPielVerde'])) || !empty(trim($this->aDatos['cColorPielAzul'])) || !empty(trim($this->aDatos['cColorPielMarron'])) || !empty(trim($this->aDatos['cColorPielNegro']))
			|| !empty(trim($this->aDatos['cPropTroficasTemperatura'])) || !empty(trim($this->aDatos['cPropTroficasPulsos'])) || !empty(trim($this->aDatos['cPropTroficasSudoriperas'])) || !empty(trim($this->aDatos['cPropTroficasSebaceas']))
			|| !empty(trim($this->aDatos['cPropTroficasCicatriz']))	|| !empty(trim($this->aDatos['cPropTroficasSensibilidad'])) || !empty(trim($this->aDatos['cPropMecanicasElasticidad'])) || !empty(trim($this->aDatos['cPropMecanicasGrosor']))
			|| !empty(trim($this->aDatos['cPropMecanicasExtensibilidad'])) || !empty(trim($this->aDatos['cPropMecanicasMovilidad'])) || !empty(trim($this->aDatos['cPropMecanicasEdema'])) || !empty(trim($this->aDatos['cPropMecanicasManchas']))
			|| !empty(trim($this->aDatos['cPropMecanicasEscaras'])) || !empty(trim($this->aDatos['cPropMecanicasUnas'])) || !empty(trim($this->aDatos['cPropMecanicasPelo'])))
			{

				if (!empty(trim($this->aDatos['cColorPielRojaEritema'])) || !empty(trim($this->aDatos['cColorPielRojaEquimosis'])) || !empty(trim($this->aDatos['cColorPielRosa'])) || !empty(trim($this->aDatos['cColorPielAmarillo']))
					|| !empty(trim($this->aDatos['cColorPielVerde'])) || !empty(trim($this->aDatos['cColorPielAzul'])) || !empty(trim($this->aDatos['cColorPielMarron'])) || !empty(trim($this->aDatos['cColorPielNegro']))){
					$laTbl[] = ['tabla', 'w'=>[180 ], 'd'=>['<b>COLOR DE PIEL</b>']];

					if (!empty(trim($this->aDatos['cColorPielRojaEritema'])) || !empty(trim($this->aDatos['cColorPielRojaEquimosis']))){
						$cColorPielRojaEritema = !empty($this->aDatos['cColorPielRojaEritema'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielRojaEritema']] :'';
						$cColorPielRojaEquimosis = !empty($this->aDatos['cColorPielRojaEquimosis'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielRojaEquimosis']] :'';
						$laTbl[] = ['tabla', 'w'=>[50, 40, 50, 40 ], 'd'=>['Roja (Eritema) ..........:'  , $cColorPielRojaEritema , 'Roja (Equimosis) ........:', $cColorPielRojaEquimosis]];
					}

					if (!empty(trim($this->aDatos['cColorPielRosa'])) || !empty(trim($this->aDatos['cColorPielAmarillo']))){
						$cColorPielRosa = !empty($this->aDatos['cColorPielRosa'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielRosa']] :'';
						$cColorPielAmarillo = !empty($this->aDatos['cColorPielAmarillo'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielAmarillo']] :'';
						$laTbl[] = ['tabla', 'w'=>[50, 40, 50, 40 ], 'd'=>['Rosa (Cicatriz reciente).:'  , $cColorPielRosa , 'Amarilla (Hematoma) .....:', $cColorPielAmarillo]];
					}

					if (!empty(trim($this->aDatos['cColorPielVerde'])) || !empty(trim($this->aDatos['cColorPielAzul']))){
						$cColorPielVerde = !empty($this->aDatos['cColorPielVerde'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielVerde']] :'';
						$cColorPielAzul = !empty($this->aDatos['cColorPielAzul'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielAzul']] :'';
						$laTbl[] = ['tabla', 'w'=>[50, 40, 50, 40 ], 'd'=>['Verde (Hematoma) ....... :'  , $cColorPielVerde , 'Azul (Cianosis) .........:', $cColorPielAzul]];
					}

					if (!empty(trim($this->aDatos['cColorPielMarron'])) || !empty(trim($this->aDatos['cColorPielNegro']))){

						$cColorPielMarron = !empty($this->aDatos['cColorPielMarron'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielMarron']] :'';
						$cColorPielNegro = !empty($this->aDatos['cColorPielNegro'])? $this->aListaParametros['POA'][$this->aDatos['cColorPielNegro']] :'';
						$laTbl[] = ['tabla', 'w'=>[50, 40, 50, 40 ], 'd'=>['Marrón (Isquemia) .......:'  , $cColorPielMarron , 'Negro (Necrosis) ........:', $cColorPielNegro]];
					}
				}

				if (!empty(trim($this->aDatos['cPropTroficasTemperatura'])) || !empty(trim($this->aDatos['cPropTroficasPulsos'])) || !empty(trim($this->aDatos['cPropTroficasSudoriperas'])) || !empty(trim($this->aDatos['cPropTroficasSebaceas']))
					|| !empty(trim($this->aDatos['cPropTroficasCicatriz'])) || !empty(trim($this->aDatos['cPropTroficasSensibilidad']))){
					$laTbl[] = ['tabla', 'w'=>[180 ], 'd'=>['<b>PROPIEDADES TRÓFICAS</b>']];

					if (!empty(trim($this->aDatos['cPropTroficasTemperatura'])) || !empty(trim($this->aDatos['cPropTroficasPulsos'])) || !empty(trim($this->aDatos['cPropTroficasSudoriperas']))){
						$cPropTroficasTemperatura = !empty($this->aDatos['cPropTroficasTemperatura'])? $this->aListaParametros['TPR'][$this->aDatos['cPropTroficasTemperatura']] :'';
						$cPropTroficasPulsos = !empty($this->aDatos['cPropTroficasPulsos'])? $this->aListaParametros['SON'][$this->aDatos['cPropTroficasPulsos']] :'';
						$cPropTroficasSudoriperas = !empty($this->aDatos['cPropTroficasSudoriperas'])? $this->aListaParametros['POA'][$this->aDatos['cPropTroficasSudoriperas']] :'';
						$laTbl[] = ['tabla', 'w'=>[35, 30, 35, 30, 35, 30, ], 'd'=>['Temp. Zona . . . :'  , $cPropTroficasTemperatura , 'Pulsos . . . . . :' , $cPropTroficasPulsos, 'Sec. Sudoríparas :', $cPropTroficasSudoriperas,]];
					}

					if (!empty(trim($this->aDatos['cPropTroficasSebaceas'])) || !empty(trim($this->aDatos['cPropTroficasCicatriz'])) || !empty(trim($this->aDatos['cPropTroficasSensibilidad']))){
						$cPropTroficasSebaceas = !empty($this->aDatos['cPropTroficasSebaceas'])? $this->aListaParametros['POA'][$this->aDatos['cPropTroficasSebaceas']] :'';
						$cPropTroficasCicatriz = !empty($this->aDatos['cPropTroficasCicatriz'])? $this->aListaParametros['CTZ'][$this->aDatos['cPropTroficasCicatriz']] :'';
						$cPropTroficasSensibilidad = !empty($this->aDatos['cPropTroficasSensibilidad'])? $this->aListaParametros['FSN'][$this->aDatos['cPropTroficasSensibilidad']] :'';

						$laTbl[] = ['tabla', 'w'=>[35, 30, 35, 30, 35, 30, ], 'd'=>['Sec. Sebáceas  . :'  , $cPropTroficasSebaceas , 'Cicatriz . . . . :' , $cPropTroficasCicatriz, 'Sensibilidad . . :', $cPropTroficasSensibilidad ,]];
					}
				}

				if (!empty(trim($this->aDatos['cPropMecanicasElasticidad'])) || !empty(trim($this->aDatos['cPropMecanicasGrosor'])) || !empty(trim($this->aDatos['cPropMecanicasExtensibilidad'])) || !empty(trim($this->aDatos['cPropMecanicasMovilidad']))
					|| !empty(trim($this->aDatos['cPropMecanicasEdema'])) || !empty(trim($this->aDatos['cPropMecanicasManchas'])) || !empty(trim($this->aDatos['cPropMecanicasEscaras'])) || !empty(trim($this->aDatos['cPropMecanicasUnas']))
					|| !empty(trim($this->aDatos['cPropMecanicasPelo']))){
					$laTbl[] = ['tabla', 'w'=>[180 ], 'd'=>['<b>PROPIEDADES MECÁNICAS</b>']];

					if (!empty(trim($this->aDatos['cPropMecanicasElasticidad'])) || !empty(trim($this->aDatos['cPropMecanicasGrosor'])) || !empty(trim($this->aDatos['cPropMecanicasExtensibilidad']))){
						$cPropMecanicasElasticidad = !empty($this->aDatos['cPropMecanicasElasticidad'])? $this->aListaParametros['SON'][$this->aDatos['cPropMecanicasElasticidad']] :'';
						$cPropMecanicasGrosor = !empty($this->aDatos['cPropMecanicasGrosor'])? $this->aListaParametros['FOG'][$this->aDatos['cPropMecanicasGrosor']] :'';
						$cPropMecanicasExtensibilidad = !empty($this->aDatos['cPropMecanicasExtensibilidad'])? $this->aListaParametros['SON'][$this->aDatos['cPropMecanicasExtensibilidad']] :'';
						$laTbl[] = ['tabla', 'w'=>[35, 30, 35, 30, 35, 30, ], 'd'=>['Elasticidad  . . :'  , $cPropMecanicasElasticidad , 'Grosor . . . . . :' , $cPropMecanicasGrosor, 'Extensibilidad . :', $cPropMecanicasExtensibilidad,]];
					}

					IF (!empty(trim($this->aDatos['cPropMecanicasMovilidad'])) || !empty(trim($this->aDatos['cPropMecanicasEdema'])) || !empty(trim($this->aDatos['cPropMecanicasManchas']))){
						$cPropMecanicasMovilidad = !empty($this->aDatos['cPropMecanicasMovilidad'])? $this->aListaParametros['SON'][$this->aDatos['cPropMecanicasMovilidad']] :'';
						$cPropMecanicasEdema = !empty($this->aDatos['cPropMecanicasEdema'])? $this->aListaParametros['EDE'][$this->aDatos['cPropMecanicasEdema']] :'';
						$cPropMecanicasManchas = !empty($this->aDatos['cPropMecanicasManchas'])? $this->aListaParametros['POA'][$this->aDatos['cPropMecanicasManchas']] :'';
						$laTbl[] = ['tabla', 'w'=>[35, 30, 35, 30, 35, 30, ], 'd'=>['Movilidad  . :'  , $cPropMecanicasMovilidad , 'Edema . . . . :' , $cPropMecanicasEdema, 'Manchas . . :', $cPropMecanicasManchas,]];
					}

					IF (!empty(trim($this->aDatos['cPropMecanicasEscaras'])) || !empty(trim($this->aDatos['cPropMecanicasUnas'])) || !empty(trim($this->aDatos['cPropMecanicasPelo']))){
						$cPropMecanicasEscaras = !empty($this->aDatos['cPropMecanicasEscaras'])? $this->aListaParametros['POA'][$this->aDatos['cPropMecanicasEscaras']] :'';
						$cPropMecanicasUnas = !empty($this->aDatos['cPropMecanicasUnas'])? $this->aListaParametros['NQQ'][$this->aDatos['cPropMecanicasUnas']] :'';
						$cPropMecanicasPelo = !empty($this->aDatos['cPropMecanicasPelo'])? $this->aListaParametros['NQQ'][$this->aDatos['cPropMecanicasPelo']] :'';
						$laTbl[] = ['tabla', 'w'=>[35, 30, 35, 30, 35, 30, ], 'd'=>['Escaras  . :'  , $cPropMecanicasEscaras , 'Fa. Uñas . . . . :' , $cPropMecanicasUnas, 'Fan. Pelo . . :', $cPropMecanicasPelo,]];
					}
				}
				$laRetorno[] = ['tablaSl', [], $laTbl];
			}

			if (!empty(trim($this->cObservacionesColorPiel))) {
				$laRetorno[] = ['titulo5',	'OBSERVACIONES'];
				$laRetorno[] = ['texto9',	trim($this->cObservacionesColorPiel)];
			}

		}
		return $laRetorno;
	}

	function InsertarDolor($tnIntensidad, $tnTipo, $tnSensibilidad, $tnLocalizacion, $tlAdicion)
	{
		$laDatos = [];
		$lcTexto = '';
		if (!empty($this->aDatos[$tnIntensidad] ?? '')){
			$lcTexto = 'INTENSIDAD: '. trim($this->aDatos[$tnIntensidad] ?? '').' - '.$this->fnDescripcionPrmTab('DOL'.(trim($this->aDatos[$tnIntensidad] ?? ''))) ;
		}
		$lcTexto .=!empty(trim($this->aDatos[$tnTipo] ?? ''))?'          TIPO DE DOLOR: '.$this->fnDescripcionPrmTab('TPD'.(trim($this->aDatos[$tnTipo] ?? ''))):'';

		if($tnSensibilidad>0){
			$lcTexto .=!empty(trim($this->aDatos[$tnSensibilidad] ?? ''))?$this->cSL.'SENSIBILIDAD: '.$this->fnDescripcionPrmTab('FSN'.(trim($this->aDatos[$tnSensibilidad] ?? ''))):'';
		}

		if(!empty(trim($lcTexto))){
			$laDatos[] = ['titulo1', 'DOLOR'] ;
			$laDatos[] = ['texto9',trim($lcTexto)];
		}

		if (!empty(trim($this->aDatos[$tnLocalizacion] ?? ''))){
			$laDatos[] = ['titulo2', 'LOCALIZACIÓN Y FRECUENCIA DEL DOLOR'] ;
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnLocalizacion])];
		}
		return $laDatos ;
	}


	private function fnDolor()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cIntensidadDolor'])) || !empty(trim($this->cDolorLocalizacion)) ||
			!empty(trim($this->cDolorFrecuencia)) || !empty(trim($this->aDatos['cTipoDolor'])) || !empty(trim($this->cObservacionesDolor))
		){

			$this->cDescIntensidad = !empty($this->aDatos['cIntensidadDolor'])? $this->aListaParametros['DOL'][$this->aDatos['cIntensidadDolor']+1] :'';
			$cDolorLocalizacion = !empty($this->cDolorLocalizacion)? $this->aListaParametros['DLC'][$this->cDolorLocalizacion] :'';
			$cDolorFrecuencia = !empty($this->cDolorFrecuencia)? $this->aListaParametros['DFC'][$this->cDolorFrecuencia] :'';
			$cTipoDolor = !empty($this->aDatos['cTipoDolor']) && $this->aDatos['cTipoDolor'] !='5'? $this->aListaParametros['TPD'][$this->aDatos['cTipoDolor']] :'';

			$cPresentaNaucesas = !empty($this->aDatos['cPresentaNauceas']) ? $this->aDatos['cPresentaNauceas']=='1'? 'SI' : 'NO' : '';

			$laRetorno[] = ['titulo2',	'DOLOR'];
			$laTbl[] = ['w'=>[90, 90, ], 'd'=>[
					'Intensidad: '.$this->aDatos['cIntensidadDolor'] .'-' .$this->cDescIntensidad, !empty($cTipoDolor)? 'Tipo de dolor: ' .$cTipoDolor : '' ]];

			$laTbl[] = ['w'=>[55, 70, 70, ], 'd'=>[
					'Localización: ' .$cDolorLocalizacion, 'Frecuencia: ' .$cDolorFrecuencia, 'Presenta Nauseas: ' .$cPresentaNaucesas]];
			$laRetorno[] = ['tablaSL', [], $laTbl];

			if (!empty(trim($this->cObservacionesDolor))) {
				$laRetorno[] = ['titulo3',	'OBSERVACIONES'];
				$laRetorno[] = ['texto9',	trim($this->cObservacionesDolor)];
			}
		}
		return $laRetorno;
	}


	function InsertarMarcha($tnDato,$tnObservacion)
	{
		$laDatos = $laTabla = [] ;
		$laAnchos = [47,48,47,48];

		if(!empty(trim($this->aDatos[$tnDato] ?? '')) || !empty(trim($this->aDatos[$tnDato+1] ?? '')) ||
		   !empty(trim($this->aDatos[$tnDato+2] ?? '')) || !empty(trim($this->aDatos[$tnDato+3] ?? ''))){

			$laDatos[] = ['w'=>$laAnchos, 'd'=>[$this->fnDescripcionPrmTab('MRC'.trim($this->aDatos[$tnDato])),$this->fnDescripcionPrmTab('MRA'.trim($this->aDatos[$tnDato+1])),
		     								$this->fnDescripcionPrmTab('SIM'.trim($this->aDatos[$tnDato+2])),$this->fnDescripcionPrmTab('LNP'.trim($this->aDatos[$tnDato+3]))
			    							],'a'=>'C'];

			$laTabla[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['<b>MARCHA</b>','<b>AYUDA</b>','<b>RITMO</b>','<b>LONGITUD PASOS</b>'], 'a'=>'C', ] ],
				$laDatos,
				];

		}

		$laDatos = [] ;
		if(!empty(trim($this->aDatos[$tnDato+4] ?? '')) || !empty(trim($this->aDatos[$tnDato+5] ?? '')) ||
		   !empty(trim($this->aDatos[$tnDato+6] ?? '')) || !empty(trim($this->aDatos[$tnDato+7] ?? ''))){

			$laDatos[] = ['w'=>$laAnchos, 'd'=>[$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+4] ?? '')),$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+5] ?? '')),
		     								    $this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+6] ?? '')),$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+7] ?? ''))
			    							   ],'a'=>'C'];

			$laTabla[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['<b>CIRCUNDUCCION</b>','<b>DISCOCIACION CINTURAS</b>','<b>BALANCEOS MMSS</b>','<b>REAC. ASOCIADAS</b>'], 'a'=>'C', ] ],
				$laDatos,
				];

		}

		if (!empty(trim($this->aDatos[$tnObservacion] ?? ''))){
			$laTabla[] = ['titulo2', 'OBSERVACIONES'] ;
			$laTabla[] = ['texto9',	trim($this->aDatos[$tnObservacion])];
		}
		return $laTabla ;
	}

	function InsertarSistMusculoEsq($tcTipo, $tnMMSS, $tnMMII, $tnFuerza, $tnFlexibilidad, $tnObserva)
	{
		$laDatos = [] ;
		$lcDatos = '';

		if (!empty(trim($this->aDatos[$tnMMSS] ?? '')) || !empty(trim($this->aDatos[$tnMMII] ?? '')) ){

			$lcDatos = 'Rango de Movimiento:   '.
					   'MMSS '.$this->fnDescripcionPrmTab(trim($tcTipo).trim($this->aDatos[$tnMMSS])).'   '.
					   'MMII '.$this->fnDescripcionPrmTab(trim($tcTipo).trim($this->aDatos[$tnMMII])) ;
		}

		if($tnFuerza>0){
			$lcDatos .= 'FUERZA GLOBAL: '.$this->fnDescripcionPrmTab(trim($tcTipo).trim($this->aDatos[$tnFuerza] ?? '')) ;
		}

		if($tnFlexibilidad>0){
			$lcDatos .= 'FLEXIBILIDAD: '.$this->fnDescripcionPrmTab(trim($tcTipo).trim($this->aDatos[$tnFlexibilidad] ?? '')) ;
		}

		if(!empty(trim($lcDatos))){
			$laDatos[] = ['titulo1', 'SISTEMA MUSCOLESQUELETICO'] ;
			$laDatos[] = ['texto9',	trim($lcDatos)];
			if (!empty(trim($this->aDatos[$tnObserva]?? ''))){
				$laDatos[] = ['titulo2', 'OBSERVACIONES'] ;
				$laDatos[] = ['texto9',	trim($this->aDatos[$tnObserva])];
			}
		}
		return $laDatos ;
	}

	private function fnSistemaMusculoesq()
	{
		$laRetorno = [];
		$cRangoMovimientoMmmss = !empty($this->aDatos['cRangoMovimientoMmmss'])? 'MMSS  ' .$this->aListaParametros['BRM'][$this->aDatos['cRangoMovimientoMmmss']] :'';
		$cRangoMovimientoMmmii = !empty($this->aDatos['cRangoMovimientoMmmii'])? '                    MMII  ' .$this->aListaParametros['BRM'][$this->aDatos['cRangoMovimientoMmmii']] :'';
		$cFuerzaGlobal = !empty($this->aDatos['cFuerzaGlobal'])? $this->aListaParametros['CNV'][$this->aDatos['cFuerzaGlobal']] :'';
		$cFlexibilidad = !empty($this->aDatos['cFlexibilidad'])? $this->aListaParametros['CNV'][$this->aDatos['cFlexibilidad']] :'';

		if (!empty(trim($cRangoMovimientoMmmss)) || !empty(trim($cRangoMovimientoMmmii)) || !empty(trim($cFuerzaGlobal)) ||
			!empty(trim($cFlexibilidad)) || !empty(trim($this->cObsMusculoEsqueleticos))) {
			$laRetorno[] = ['titulo2',	'SISTEMA MUSCULOÉSQUELETICO'];
			$laRetorno[] = ['titulo4',	'RANGO GRUESO DE MOVIMIENTO'];

			if (!empty(trim($cRangoMovimientoMmmss)) || !empty(trim($cRangoMovimientoMmmii))) {
				$laRetorno[] = ['texto9',	'Rango movimiento:            ' .$cRangoMovimientoMmmss .$cRangoMovimientoMmmii];
			}

			if (!empty(trim($cFuerzaGlobal))){
				$laRetorno[] = ['txthtml9',	'<b>FUERZA GLOBAL: </b>' .$cFuerzaGlobal];
			}
			if (!empty(trim($cFlexibilidad))){
				$laRetorno[] = ['txthtml9',	'<b>FLEXIBILIDAD: </b>' .$cFlexibilidad];
			}

			if (!empty(trim($this->cObsMusculoEsqueleticos))) {
				$laRetorno[] = ['titulo3',	'OBSERVACIONES'];
				$laRetorno[] = ['texto9',	trim($this->cObsMusculoEsqueleticos)];
			}
		}
		return $laRetorno;
	}

	function InsertarIntegumentario($tnDato)
	{
		$laDatos = $laTabla = [] ;
		$lnPrTro = $tnDato ;
		$lnProMec = $tnDato+1 ;
		$lnEscaras = $tnDato+2 ;
		$lnEdema = $tnDato+3 ;
		$laAnchos = [47,48,47,48];
		$lnObserva = $tnDato+4 ;

		if(!empty(trim($this->aDatos[$lnPrTro] ?? '')) || !empty(trim($this->aDatos[$lnProMec] ?? '')) ||
		   !empty(trim($this->aDatos[$lnEscaras] ?? '')) || !empty(trim($this->aDatos[$lnEdema] ?? ''))){

			$laDatos[] = ['w'=>$laAnchos, 'd'=>[$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$lnPrTro])),$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$lnProMec])),
		     								$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$lnEscaras])),$this->fnDescripcionPrmTab('EDE'.trim($this->aDatos[$lnEdema]))
			    							],'a'=>'C'];
		}
		if (!empty($laDatos)){
			$laTabla[] = ['titulo1', 'SISTEMA INTEGUMENTARIO'] ;
			$laTabla[] = ['saltol', 3];
			$laTabla[] = ['tabla',
					[ [ 'w'=>$laAnchos, 'd'=>['<b>Propiedades Tróficas</b>','<b>Propiedades Mecánicas</b>','<b>Escaras</b>','<b>Edema</b>'], 'a'=>'C', ] ],
					$laDatos,
					];
		}

		if(!empty(trim($this->aDatos[$lnObserva] ?? ''))){
			$laTabla[] = ['titulo2', 'OBSERVACIONES '];
			$laTabla[] = ['texto9',	trim($this->aDatos[$lnObserva])];
		}
		return $laTabla;
	}

	function InsertarReaccEquilibrio($tnDato)
	{
		$laDatos = $laTabla = [] ;
		$laAnchos = [30,80,80] ;

		if(!empty(trim($this->aDatos[$tnDato] ?? '')) || !empty(trim($this->aDatos[$tnDato+1] ?? '')) ||
		   !empty(trim($this->aDatos[$tnDato+2] ?? '')) || !empty(trim($this->aDatos[$tnDato+3] ?? ''))){

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Sedente',$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato])),
												$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+1]))
		     									],'a'=>'C'];

			$laDatos[] = ['w'=>$laAnchos, 'd'=>['Bipeda',$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+2])),
												$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+3]))
		     									],'a'=>'C'];

			$laTabla[] = ['titulo1', 'SISTEMA NEUROMUSCULAR'] ;
			$laTabla[] = ['titulo2', 'REACCIONES DE EQUILIBRIO Y ENDEREZAMIENTO'] ;
			$laTabla[] = ['saltol', 3];
			$laTabla[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['','<b>Estático</b>','<b>Dinámico</b>'], 'a'=>'C', ] ],
				$laDatos,
				];

		}
		return $laTabla ;
	}

	function InsertarSensibilidad($tnDato, $tnSuperficial, $tnProfunda)
	{
		$laDatos = [];
		$lcDatos = '';

		if($tnDato>0){
			if(!empty(trim($this->aDatos[$tnDato] ?? ''))){
				$lcDatos = 'SENSIBILIDAD: '. $this->fnDescripcionPrmTab('FSB'.trim($this->aDatos[$tnDato] ?? '')).chr(13) ;
			}
		}

		if(!empty(trim($this->aDatos[$tnSuperficial] ?? '')) || !empty(trim($this->aDatos[$tnProfunda] ?? ''))){
			$lcDatos .= 'Superficial: '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnSuperficial])).'          ' ;
			$lcDatos .= 'Profunda: '.$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnProfunda])) ;
		}

		if(!empty(trim($lcDatos))){
			$laDatos[] = ['titulo1', 'SENSIBILIDAD'] ;
			$laDatos[] = ['texto9',	trim($lcDatos)];
		}
		return $laDatos ;
	}

	function InsertarValoracionFunc($tnAVD,$tnAVID)
	{
		$laDatos = [] ;
		$llTitulo = True ;

		if(!empty(trim($this->aDatos[$tnAVD] ?? ''))){
			if($llTitulo){
				$laDatos[] = ['titulo1', 'VALORACION FUNCIONAL '];
				$llTitulo = false ;
			}
			$laDatos[] = ['titulo2', 'AVD '];
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnAVD])];
		}

		if(!empty(trim($this->aDatos[$tnAVID] ?? ''))){
			if($llTitulo){
				$laDatos[] = ['titulo1', 'VALORACION FUNCIONAL '];
				$llTitulo = false ;
			}
			$laDatos[] = ['titulo2', 'AVID '];
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnAVID])];
		}

		return $laDatos ;
	}

	function InsertarDiagnosticos($tnObjetivos, $tnIntervencion, $tnRecomendacion)
	{
		$laDatos = [] ;
		$lcSL = $this->cSL ;

		// Diagnostico e intervencion
		$laDatos[] = ['titulo1', 'DIAGNOSTICO E INTERVENCION'] ;
		$lcSistema = trim($this->aDiagnosticos[1]['SISTEMA'] ?? '');
		$lcDatos = '';

		foreach($this->aDiagnosticos as $laDiagnosticos) {

			if(trim($laDiagnosticos['SISTEMA'] ?? '')!=trim($lcSistema)){
				$laDatos[] = ['titulo2', $lcSistema] ;
				$laDatos[] = ['texto9',	trim($lcDatos)];
				$lcDatos = '';
				$lcSistema = $laDiagnosticos['SISTEMA'];
			}
			$lcDatos .= trim($laDiagnosticos['LITERAL'] ?? '').'. '.trim($laDiagnosticos['DOMINIO'] ?? '').$lcSL ;

		}

		if(!empty($lcSistema)){
			$laDatos[] = ['titulo2', $lcSistema] ;
			$laDatos[] = ['texto9',	trim($lcDatos)];
		}

		// OBJETIVOS
		$lcDatos = '';
		$lnKey = 0;
		foreach($this->aObjetivos as $laObjetivos) {
			if($laObjetivos['SELECCION']=='1'){
				$lnKey+=1;
				$lcDatos .= $lcSL.$lnKey.'.'.trim($laObjetivos['DESCRIPCION']);
			}
		}

		if (!empty(trim($this->cCumplioObjetivos))){
			$lcDatos .= $lcSL.'¿Cumplió con los objetivos? '.trim($this->cCumplioObjetivos);
		}

		if (!empty(trim($lcDatos))){
			$laDatos[] = ['titulo2', 'OBJETIVOS PRINCIPALES'] ;
			$laDatos[] = ['texto9',	trim($lcDatos)];
		}
		if (!empty(trim($this->aDatos[$tnObjetivos]??''))){
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnObjetivos])];
		}

		if (!empty(trim($this->cObjetivosSugerencias))){
			$laDatos[] = ['titulo3', 'SUGERENCIAS'] ;
			$laDatos[] = ['texto9',	trim($this->cObjetivosSugerencias)];
		}

		if (isset($this->aDatos[$tnIntervencion]) && !empty(trim($this->aDatos[$tnIntervencion]))){
			$laDatos[] = ['titulo2', 'INTERVENCIÓN'] ;
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnIntervencion])];
		}

		if (isset($this->aDatos[$tnRecomendacion]) && !empty(trim($this->aDatos[$tnRecomendacion]))){
			$laDatos[] = ['titulo2', 'RECOMENDACIONES DE SALIDA'] ;
			$laDatos[] = ['texto9',	trim($this->aDatos[$tnRecomendacion])];
		}
		return $laDatos ;
	}

	private function fnDiagnosticoIntervencion()
	{
		$laRetorno = [];
		$aContadorObjetivos = 0;

		if (!empty(trim($this->cCumplioObjetivos)) || !empty(trim($this->cSugerencias)) || !empty(trim($this->cDatosObjetivos))
			|| !empty(trim($this->cDatosDiagnosticos))){
			$laRetorno[] = ['titulo2',	'DIAGNOSTICO E INTERVENCIÓN'];

			$cActualTituloDiganostico = '';

			if (!empty(trim($this->cDatosDiagnosticos))){
				$aDiagnosticosLista = explode(',', $this->cDatosDiagnosticos);

				foreach($aDiagnosticosLista as $laDiagnosticosLista) {
					if ($this->aTituloDiagnostico[$laDiagnosticosLista] !==  $cActualTituloDiganostico){
						$cActualTituloDiganostico = $this->aTituloDiagnostico[$laDiagnosticosLista];
						$laRetorno[] = ['titulo3', $cActualTituloDiganostico];

						foreach($aDiagnosticosLista as $laDiagnosticosDescp) {
							if ($cActualTituloDiganostico==$this->aTituloDiagnostico[$laDiagnosticosDescp]){
								$laRetorno[] = ['texto9', $this->aListaDiagnostico[$laDiagnosticosDescp]];
							}
						}
					}
				}
			}

			if (!empty(trim($this->cDatosObjetivos))){
				$laRetorno[] = ['titulo3', 'OBJETIVOS PRINCIPALES'];
				$aObjetivos = explode(',', $this->cDatosObjetivos);

				foreach($aObjetivos as $laDataObjetivos) {
					$aContadorObjetivos = $aContadorObjetivos + 1;
					$aListaObjetivos = $this->aListaObjetivos[$laDataObjetivos];
					$laRetorno[] = ['texto9', $aContadorObjetivos .'. '.$aListaObjetivos];
				}
			}

			if (!empty(trim($this->cCumplioObjetivos))) {
				$laRetorno[] = ['texto9', '¿Cumplió con los objetivos? ' .($this->cCumplioObjetivos=='S'? 'SI': 'NO')];
			}

			if (!empty(trim($this->cSugerencias))) {
				$laRetorno[] = ['titulo5', 	'SUGERENCIAS'];
				$laRetorno[] = ['texto9',	trim($this->cSugerencias)];
			}

			if (!empty(trim($this->cObsIntervencion))) {
				$laRetorno[] = ['titulo3',	'INTERVENCIÓN'];
				$laRetorno[] = ['texto9',	trim($this->cObsIntervencion)];
			}

			if (!empty(trim($this->cObsRecomendaciones))) {
				$laRetorno[] = ['titulo3',	'RECOMENDACIONES DE SALIDA'];
				$laRetorno[] = ['texto9',	trim($this->cObsRecomendaciones)];
			}
		}
		return $laRetorno;
	}

	private function fnInsertarMarcha()
	{
		$laRetorno = [];
		if (!empty(trim($this->aDatos['cMarchaMarcha'])) || !empty(trim($this->aDatos['cMarchaAyudas'])) || !empty(trim($this->aDatos['cMarchaRitmo']))
			|| !empty(trim($this->aDatos['cMarchaLongitud'])) || !empty(trim($this->aDatos['cMarchaCircunduccion'])) || !empty(trim($this->aDatos['cMarchaDisociacion']))
			|| !empty(trim($this->aDatos['cMarchaBalanceMmss'])) || !empty(trim($this->cFlexibilidadControlMotor)) || !empty(trim($this->cPosturalControlMotor))
			|| !empty(trim($this->cObservacionesMarcha))
			){

			$laRetorno[] = ['titulo2',	'CONTROL MOTOR'];

			if (!empty(trim($this->cFlexibilidadControlMotor))){
				$laRetorno[] = ['titulo3',	'FLEXIBILIDAD'];
				$laRetorno[] = ['texto9',	trim($this->cFlexibilidadControlMotor)];
			}

			if (!empty(trim($this->cPosturalControlMotor))){
				$laRetorno[] = ['titulo3',	'POSTURA'];
				$laRetorno[] = ['texto9',	trim($this->cPosturalControlMotor)];
			}

			$laRetorno[] = ['titulo3',	'MARCHA'];
			$cMarchaMarcha = !empty($this->aDatos['cMarchaMarcha'])? $this->aListaParametros['MRC'][$this->aDatos['cMarchaMarcha']] :'';
			$cMarchaAyudas = !empty($this->aDatos['cMarchaAyudas'])? $this->aListaParametros['MRA'][$this->aDatos['cMarchaAyudas']] :'';
			$cMarchaRitmo = !empty($this->aDatos['cMarchaRitmo'])? $this->aListaParametros['SIM'][$this->aDatos['cMarchaRitmo']] :'';
			$cMarchaLongitud = !empty($this->aDatos['cMarchaLongitud'])? $this->aListaParametros['LNP'][$this->aDatos['cMarchaLongitud']] :'';

			$cMarchaCircunduccion = !empty($this->aDatos['cMarchaCircunduccion'])? $this->aListaParametros['POA'][$this->aDatos['cMarchaCircunduccion']] :'';
			$cMarchaDisociacion = !empty($this->aDatos['cMarchaDisociacion'])? $this->aListaParametros['POA'][$this->aDatos['cMarchaDisociacion']] :'';
			$cMarchaBalanceMmss = !empty($this->aDatos['cMarchaBalanceMmss'])? $this->aListaParametros['POA'][$this->aDatos['cMarchaBalanceMmss']] :'';


			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>['Marcha'  , 'Ayuda' , 'Ritmo', 'Longitud Pasos',]];
			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>[$cMarchaMarcha  , $cMarchaAyudas , $cMarchaRitmo, $cMarchaLongitud ]];
			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>['Circunducción'  , 'Discociación Cinturas' , 'Balanceos MMSS', '']];
			$laTbl[] = ['tabla', 'w'=>[45, 45, 45, 45, ], 'a'=>'C', 'd'=>[$cMarchaCircunduccion  , $cMarchaDisociacion , $cMarchaBalanceMmss, '' ]];
			$laRetorno[] = ['tabla', [], $laTbl];

			if (!empty(trim($this->cObservacionesMarcha))) {
				$laRetorno[] = ['titulo3',	'OBSERVACIONES'];
				$laRetorno[] = ['texto9',	trim($this->cObservacionesMarcha)];
			}
		}
		return $laRetorno;
	}

	private function fnInsertarReacciones()
	{
		$laRetorno = [];

		if (!empty(trim($this->aDatos['cSedenteEstatico'])) || !empty(trim($this->aDatos['cSedenteDinamico'])) || !empty(trim($this->aDatos['cBipedaEstatico'])) || !empty(trim($this->aDatos['cBipedaDinamico']))
			){
			$cSedenteEstatico = !empty($this->aDatos['cSedenteEstatico'])? $this->aListaParametros['POA'][$this->aDatos['cSedenteEstatico']] :'';
			$cSedenteDinamico = !empty($this->aDatos['cSedenteDinamico'])? $this->aListaParametros['POA'][$this->aDatos['cSedenteDinamico']] :'';
			$cBipedaEstatico = !empty($this->aDatos['cBipedaEstatico'])? $this->aListaParametros['POA'][$this->aDatos['cBipedaEstatico']] :'';
			$cBipedaDinamico = !empty($this->aDatos['cBipedaDinamico'])? $this->aListaParametros['POA'][$this->aDatos['cBipedaDinamico']] :'';

			$laRetorno[] = ['titulo2',	'REACCIONES DE EQUILIBRIO Y ENDEREZAMIENTO'];
			$laRetorno[] = ['tabla', [],
				[
					['w'=>[30, 70, 70 ], 'a'=>'C', 'd'=>[''  , 'Estático' , 'Dinámico']],
					['w'=>[30, 70, 70 ], 'a'=>'C', 'd'=>['Sedente' , $cSedenteEstatico , $cSedenteDinamico]],
					['w'=>[30, 70, 70 ], 'a'=>'C', 'd'=>['Bipeda'  , $cBipedaEstatico , $cBipedaDinamico]],
				],
			];
		}
		return $laRetorno;
	}

	private function fnInsertarMovilidadArticular()
	{
		$laRetorno = [];
		if (!empty($this->cDatosMovilidadArticular)) {

			// Organizar títulos y descripciones
			$laTitulos = [];
			$lnOrden = 0;
			foreach ($this->aListaValoracionArtirular as $laLista) {
				$laTitulos[ $laLista['ID'] ] = [
					'NUM' => $lnOrden++,
					'ARTICULACION' => $laLista['ARTICULACION'],
					'DESCRIPCION'  => "{$laLista['DESCRIPCION']} ({$laLista['MIN']}~{$laLista['MAX']}°)",
				];
			}

			// Organizar tabla
			$lcTitulo = '';
			$laDatosMovArt = [];
			$laW = [90,45,45];
			$laA = ['L','C','C'];
			if (!empty(trim( $this->cDatosMovilidadArticular))){

				$laMovilidadArticular = explode(',', $this->cDatosMovilidadArticular);
				foreach ($laMovilidadArticular as $laDato) {

					$laMovArt = explode('|', $laDato);

					if ($lcTitulo !== $laTitulos[ $laMovArt[0] ]['ARTICULACION']) {
						$lcTitulo = $laTitulos[ $laMovArt[0] ]['ARTICULACION'];

						if ($lcTitulo=='COLUMNA CERVICAL' || $lcTitulo=='COLUMNA DORSOLUMBAR'){
							$laDatosMovArt[] = [ 'd'=>["<b>{$lcTitulo}</b>", '<b>Der.</b>', '<b>Izq.</b>'], 'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laMovArt[0] ]['NUM']*10, ];
						}else{
							$laDatosMovArt[] = [ 'd'=>["<b>{$lcTitulo}</b>", '<b>Miembro Sup Der</b>', '<b>Miembro Sup Izq</b>'], 'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laMovArt[0] ]['NUM']*10, ];
						}
					}
					$laDatosMovArt[] = [ 'd'=>[$laTitulos[ $laMovArt[0] ]['DESCRIPCION'], $laMovArt[2].'°', $laMovArt[1].'°'], 'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laMovArt[0] ]['NUM']*10+1, ];
				}
			}
			AplicacionFunciones::ordenarArrayMulti($laDatosMovArt, 'orden');

			$laRetorno = [
				['titulo2',	'VALORACIÓN MOVILIDAD ARTICULAR'],
				['tabla', [], $laDatosMovArt],
			];
		}
		return $laRetorno;
	}

  	private function fnInsertarValoracionMuscular()
	{
		$laRetorno = [];
		if (!empty($this->cDatosMovilidadMuscular)) {

			// Organizar títulos y descripciones
			$laTitulos = [];
			$lnOrden = 0;
			foreach ($this->aListaValoracionMuscular as $laLista) {
				$laTitulos[ $laLista['ID'] ] = [
					'NUM' => $lnOrden++,
					'ARTICULACION' => $laLista['ARTICULACION'],
					'DESCRIPCION'  => substr($laLista['DESCRIPCION'], 0, 17),
				];
			}

			// Organizar tabla
			$lcTitulo = '';
			$laDatosMovMus = [];
			$laW = [6,6,6,6,6,6,6,6,6,6,6,6,6,34,6,6,6,6,6,6,6,6,6,6,6,6,6];
			$laA = ['C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'];
			if (!empty(trim( $this->cDatosMovilidadMuscular))){

				$laMovilidadMuscular = explode(',', $this->cDatosMovilidadMuscular);
				foreach ($laMovilidadMuscular as $laDato) {

					$laMovMuscul = explode('|', $laDato);

					if ($lcTitulo !== $laTitulos[ $laMovMuscul[0] ]['ARTICULACION']) {
						$lcTitulo = $laTitulos[ $laMovMuscul[0] ]['ARTICULACION'];

						$laDatosMovMus[] = [ 'd'=>['<b>0</b>', '<b>-1</b>', '<b>+1</b>', '<b>-2</b>', '<b>2</b>', '<b>+2</b>','<b>-3</b>', '<b>3</b>', '<b>+3</b>', '<b>-4</b>', '<b>4</b>', '<b>+4</b>','<b>5</b>',
												"<b>{$lcTitulo}</b>", '<b>0</b>', '<b>-1</b>', '<b>+1</b>', '<b>-2</b>', '<b>2</b>', '<b>+2</b>','<b>-3</b>', '<b>3</b>', '<b>+3</b>', '<b>-4</b>', '<b>4</b>', '<b>+4</b>','<b>5</b>'],
										'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laMovMuscul[0] ]['NUM']*10, ];
					}
					$laDatosMovMus[] = [ 'd'=>[$laMovMuscul[1]==1? 'X':'',$laMovMuscul[1]==2? 'X':'',$laMovMuscul[1]==3? 'X':'',$laMovMuscul[1]==4? 'X':'',$laMovMuscul[1]==5? 'X':'',$laMovMuscul[1]==6? 'X':'',$laMovMuscul[1]==7? 'X':'',
											   $laMovMuscul[1]==8? 'X':'',$laMovMuscul[1]==9? 'X':'',$laMovMuscul[1]==10? 'X':'',$laMovMuscul[1]==11? 'X':'',$laMovMuscul[1]==12? 'X':'',$laMovMuscul[1]==13? 'X':'',
											   $laTitulos[ $laMovMuscul[0] ]['DESCRIPCION'],
											   $laMovMuscul[2]==14? 'X':'',$laMovMuscul[2]==15? 'X':'',$laMovMuscul[2]==16? 'X':'',$laMovMuscul[2]==17? 'X':'',$laMovMuscul[2]==18? 'X':'',$laMovMuscul[2]==19? 'X':'',$laMovMuscul[2]==20? 'X':'',
											   $laMovMuscul[2]==21? 'X':'',$laMovMuscul[2]==22? 'X':'',$laMovMuscul[2]==23? 'X':'',$laMovMuscul[2]==24? 'X':'',$laMovMuscul[2]==25? 'X':'',$laMovMuscul[2]==26? 'X':''],
									'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laMovMuscul[0] ]['NUM']*10+1, ];
				}
			}
			AplicacionFunciones::ordenarArrayMulti($laDatosMovMus, 'orden');

			$laRetorno = [
				['titulo2',	'VALORACIÓN MUSCULAR'],
				['tabla', [], $laDatosMovMus],
			];
		}
		return $laRetorno;
	}

	private function fnInsertarCaraTronco()
	{
		$laRetorno = [];
		if (!empty($this->cDatosValoracionCaraTronco)) {

			// Organizar títulos y descripciones
			$laTitulos = [];
			$lnOrden = 0;
			foreach ($this->aListaValoracionCaraTronco as $laLista) {
				$laTitulos[ $laLista['ID'] ] = [
					'NUM' => $lnOrden++,
					'ARTICULACION' => $laLista['ARTICULACION'],
					'DESCRIPCION'  => $laLista['DESCRIPCION'],
				];
			}

			// Organizar tabla
			$lcTitulo = '';
			$laDatosCaraTro = [];
			$laW = [80,12,12,12,12,12,12];
			$laA = ['L','C','C','C','C','C','C'];
			if (!empty(trim( $this->cDatosValoracionCaraTronco))){

				$laMovilidadCaraTro = explode(',', $this->cDatosValoracionCaraTronco);
				foreach ($laMovilidadCaraTro as $laDato) {
					$laCaraTron = explode('|', $laDato);

					if ($lcTitulo !== $laTitulos[ $laCaraTron[0] ]['ARTICULACION']) {
						$lcTitulo = $laTitulos[ $laCaraTron[0] ]['ARTICULACION'];

						if ($lcTitulo=='CARA'){
							$laDatosCaraTro[] = [ 'd'=>["<b>VALORACION {$lcTitulo}</b>", '<b>0%</b>', '<b>10%</b>', '<b>25%</b>', '<b>50%</b>', '<b>75%</b>','<b>100%</b>'], 'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laCaraTron[0] ]['NUM']*10, ];
						}else{
							$laDatosCaraTro[] = [ 'd'=>["<b>VALORACION {$lcTitulo}</b>", '<b>0</b>', '<b>1</b>', '<b>2</b>', '<b>3</b>', '<b>4</b>','<b>5</b>'], 'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laCaraTron[0] ]['NUM']*10, ];
						}
					}
					$laDatosCaraTro[] = [ 'd'=>[$laTitulos[ $laCaraTron[0] ]['DESCRIPCION'], $laCaraTron[1]==1? 'X':'',$laCaraTron[1]==2? 'X':'',$laCaraTron[1]==3? 'X':'',$laCaraTron[1]==4? 'X':'',$laCaraTron[1]==5? 'X':'',$laCaraTron[1]==6? 'X':''],
										'w'=>$laW, 'a'=>$laA, 'orden'=>$laTitulos[ $laCaraTron[0] ]['NUM']*10+1, ];
				}
			}
			AplicacionFunciones::ordenarArrayMulti($laDatosCaraTro, 'orden');

			$laRetorno = [
				['titulo2',	'VALORACIÓN'],
				['tabla', [], $laDatosCaraTro],
			];
		}
		return $laRetorno;
	}

	function InsertarNeuromuscular($tnDato)
	{
		$laDatos = $laTabla = [] ;
		$laAnchos = [38,38,38,38,38] ;

		if(!empty(trim($this->aDatos[$tnDato] ?? '')) || !empty(trim($this->aDatos[$tnDato+1] ?? '')) ||
		   !empty(trim($this->aDatos[$tnDato+2] ?? '')) || !empty(trim($this->aDatos[$tnDato+3] ?? '')) || !empty(trim($this->aDatos[$tnDato+4] ?? ''))){

			$laDatos[] = ['w'=>$laAnchos, 'd'=>[($this->aDatos[$tnDato]==1?'+':($this->aDatos[$tnDato]==2?'-':'NO VALORABLE')),
												$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnDato+1])),
												$this->fnDescripcionPrmTab('CNV'.trim($this->aDatos[$tnDato+2])),
												$this->fnDescripcionPrmTab('ANA'.trim($this->aDatos[$tnDato+3])),
												$this->fnDescripcionPrmTab('POA'.trim($this->aDatos[$tnDato+4]))
		     									],'a'=>'C'];

			$laTabla[] = ['titulo2', 'NEUROMUSCULAR'] ;
			$laTabla[] = ['saltol', 3];
			$laTabla[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['<b>Romberg</b>','<b>Equilibrio</b>','<b>Coordinación</b>','<b>Diadococinecia</b>','<b>Inesta. Postural</b>'], 'a'=>'C', ] ],
				$laDatos,
				];

		}
		return $laTabla ;
	}

	private function fnInsertarTextopandemia($tcTexto)
	{
		$laRetorno = [];
		if (!empty(trim($tcTexto))){
			$laRetorno[] = ['titulo3',	' '];
			$laRetorno[] = ['texto9',	trim($tcTexto)];
		}
		return $laRetorno;
	}


	private function datosBlanco()
	{
		$this->aDatos['cFecEvaluacion'] = '';
		$this->aDatos['cDisnea'] = '';
		$this->aDatos['cAuscultacion'] = '';
		$this->aDatos['cExcToraxica'] = '';
		$this->aDatos['cPatronRespiratorio'] = '';
		$this->aDatos['cRitmoRespiratorio'] = '';
		$this->aDatos['cEstadoConciencia'] = '';
		$this->aDatos['cEsferaMental'] = '';
		$this->aDatos['cFrecCardiaca'] = '';
		$this->aDatos['cFrecRespiratoria'] = '';
		$this->aDatos['cTensionArterial'] = '';
		$this->aDatos['cTemperatura'] = '';
		$this->aDatos['cSPO2'] = '';
		$this->cObservacionesGenerales = '';
		$this->cObservacionesMarcha = '';
		$this->cAntecedentes = '';
		$this->cLenguajeComunicaciones = '';
		$this->cActitudColaboracion = '';
		$this->aDatos['cIntensidadDolor'] = '';
		$this->aDatos['cTipoDolor'] = '';
		$this->aDatos['cSensibilidadDolor'] = '';
		$this->cObservacionesDolor = '';
		$this->laDesacondicionamiento = '';
		$this->aDatos['cRangoMovimientoMmmss'] = '';
		$this->aDatos['cRangoMovimientoMmmii'] = '';
		$this->aDatos['cFuerzaGlobal'] = '';
		$this->aDatos['cFlexibilidad'] = '';
		$this->cObsMusculoEsqueleticos = '';
		$this->aDatos['cSisNeuroMuscRomberg'] = '';
		$this->aDatos['cSisNeuroMuscEquilibrio'] = '';
		$this->aDatos['cSisNeuroMuscCoordinacion'] = '';
		$this->aDatos['cSisNeuroMuscDiadococinecia'] = '';
		$this->aDatos['cSisNeuroMuscInestabilidadPostural'] = '';
		$this->cObsSistemaIntegumentario = '';
		$this->cObsValoracionAvd = '';
		$this->cObsValoracionAvid = '';
		$this->aDatos['cAvdIndependiente'] = '';
		$this->aDatos['cAvdBanarse']= '';
		$this->aDatos['cAvdComer']= '';
		$this->aDatos['cAvdVestirse']= '';
		$this->aDatos['cAvdTraslado']= '';
		$this->aDatos['cAvdCambioPosicion']= '';
		$this->aDatos['cAvidIndependiente']= '';
		$this->aDatos['cAvidTrabajo']= '';
		$this->aDatos['cAvidVidaSocial']= '';
		$this->cDatosDiagnosticos = '';
		$this->cDatosObjetivos = '';
		$this->cCumplioObjetivos = '';
		$this->cSugerencias = '';
		$this->cObsIntervencion = '';
		$this->cObsRecomendaciones = '';
		$this->cTextoPandemia = '';
		$this->cObservacionesColorPiel = '';
		$this->cObsEspasmo = '';
		$this->cDatosMovilidadArticular = '';
		$this->cFlexibilidadControlMotor = '';
		$this->cPosturalControlMotor = '';
		$this->aDatos['cSisIntegumentarioTrofica'] = '';
		$this->aDatos['cSisIntegumentarioMecanica'] = '';
		$this->aDatos['cSisIntegumentarioEscaras'] = '';
		$this->aDatos['cSisIntegumentarioEdema'] = '';
		$this->cObsPropiocepcion = '';
		$this->cDatosMovilidadArticular = '';
		$this->cDatosMovilidadMuscular = '';
		$this->cDatosValoracionCaraTronco = '';
		$this->cDermatomasSeleccionados = '';
		$this->aDatos['cColorPielRojaEritema'] = '';
		$this->aDatos['cColorPielRojaEquimosis'] = '';
		$this->aDatos['cColorPielRosa'] = '';
		$this->aDatos['cColorPielAmarillo'] = '';
		$this->aDatos['cColorPielVerde'] = '';
		$this->aDatos['cColorPielAzul'] = '';
		$this->aDatos['cColorPielMarron'] = '';
		$this->aDatos['cColorPielNegro'] = '';
		$this->aDatos['cPropTroficasTemperatura'] = '';
		$this->aDatos['cPropTroficasPulsos'] = '';
		$this->aDatos['cPropTroficasSudoriperas'] = '';
		$this->aDatos['cPropTroficasSebaceas'] = '';
		$this->aDatos['cPropTroficasCicatriz'] = '';
		$this->aDatos['cPropTroficasSensibilidad'] = '';
		$this->aDatos['cPropMecanicasElasticidad'] = '';
		$this->aDatos['cPropMecanicasGrosor'] = '';
		$this->aDatos['cPropMecanicasExtensibilidad'] = '';
		$this->aDatos['cPropMecanicasMovilidad'] = '';
		$this->aDatos['cPropMecanicasEdema'] = '';
		$this->aDatos['cPropMecanicasManchas'] = '';
		$this->aDatos['cPropMecanicasEscaras'] = '';
		$this->aDatos['cPropMecanicasUnas'] = '';
		$this->aDatos['cPropMecanicasPelo'] = '';
		$this->aDatos['cPropiocepcionEstatico'] = '';
		$this->aDatos['cPropiocepcionDinamico'] = '';
		$this->aDatos['cSensibilidadSuperficial'] = '';
		$this->aDatos['cSensibilidadProfunda'] = '';
		$this->aDatos['cTipoEspasmo'] = '';
		$this->aDatos['cMarchaMarcha'] = '';
		$this->aDatos['cMarchaAyudas'] = '';
		$this->aDatos['cMarchaRitmo'] = '';
		$this->aDatos['cMarchaLongitud'] = '';
		$this->aDatos['cMarchaCircunduccion'] = '';
		$this->aDatos['cMarchaDisociacion'] = '';
		$this->aDatos['cMarchaBalanceMmss'] = '';
		$this->aDatos['cSedenteEstatico'] = '';
		$this->aDatos['cSedenteDinamico'] = '';
		$this->aDatos['cBipedaEstatico'] = '';
		$this->aDatos['cBipedaDinamico'] = '';
		$this->aDatos['cCaminataValorable'] = '';
		$this->aDatos['cCaminataCausa'] = '';
		$this->aDatos['cPresentaNauceas'] = '';
	}

	function fnObtenerClaves($tnKey)
	{
		return $this->aIndices[$tnKey] ?? 'key'.$tnKey;
	}

	function fnCrearClaves($tnIndice)
	{
		// Claves comunes
		$laReturn = [
			1 => 'cFecEvaluacion',
			2 => 'cEstadoConciencia',
			3 => 'cEsferaMental',
			5 => 'cFrecCardiaca',
			6 => 'cFrecRespiratoria',
			7 => 'cTensionArterial',
			8 => 'cTemperatura',
			9 => 'cSPO2',
		];

		// Claves No Comunes
		switch ($tnIndice)
		{
			// Bariatrico
			case 2:
				$laReturn[ 4] = 'key04';
				$laReturn[10] = 'cDisnea';
				$laReturn[11] = 'cAuscultacion';
				$laReturn[12] = 'cExcToraxica';
				$laReturn[13] = 'cPatronRespiratorio';
				$laReturn[14] = 'cRitmoRespiratorio';
				$laReturn[17] = 'cRangoMovimientoMmmss';
				$laReturn[18] = 'cRangoMovimientoMmmii';
				$laReturn[19] = 'cFuerzaGlobal';
				$laReturn[20] = 'cFlexibilidad';
				$laReturn[22] = 'cSisNeuroMuscRomberg';
				$laReturn[23] = 'cSisNeuroMuscEquilibrio';
				$laReturn[24] = 'cSisNeuroMuscCoordinacion';
				$laReturn[25] = 'cSisNeuroMuscDiadococinecia';
				$laReturn[26] = 'cSisNeuroMuscInestabilidadPostural';
				$laReturn[27] = 'cSisIntegumentarioTrofica';
				$laReturn[28] = 'cSisIntegumentarioMecanica';
				$laReturn[29] = 'cSisIntegumentarioEscaras';
				$laReturn[30] = 'cSisIntegumentarioEdema';
				$laReturn[31] = 'key31';
				$laReturn[32] = 'key32';
				$laReturn[33] = 'key33';
				$laReturn[34] = 'key34';
				$laReturn[35] = 'key35';
				$laReturn[36] = 'key36';
				$laReturn[37] = 'key37';
				$laReturn[38] = 'cPeso';
				$laReturn[39] = 'cTalla';
				$laReturn[40] = 'cCintura';
				$laReturn[41] = 'key41';
				$laReturn[42] = 'cCaminataFc';
				$laReturn[43] = 'cCaminataFr';
				$laReturn[45] = 'cCaminataSpO2';
				$laReturn[46] = 'cMinuto1Fc';
				$laReturn[47] = 'cMinuto1SpO2';
				$laReturn[48] = 'cMinuto2Fc';
				$laReturn[49] = 'cMinuto2SpO2';
				$laReturn[50] = 'cMinuto3Fc';
				$laReturn[51] = 'cMinuto3SpO2';
				$laReturn[52] = 'cMinuto4Fc';
				$laReturn[53] = 'cMinuto4SpO2';
				$laReturn[54] = 'cMinuto5Fc';
				$laReturn[55] = 'cMinuto5SpO2';
				$laReturn[56] = 'cMinuto6Fc';
				$laReturn[57] = 'cMinuto6SpO2';
				$laReturn[58] = 'key58';
				$laReturn[59] = 'key59';
				$laReturn[60] = 'key60';
				$laReturn[61] = 'key61';
				$laReturn[62] = 'key62';
				$laReturn[63] = 'key63';
				$laReturn[64] = 'key64';
				$laReturn[65] = 'key65';
				$laReturn[66] = 'key66';
				$laReturn[67] = 'cIntensidadDolor';
				$laReturn[68] = 'cTipoDolor';
				$laReturn[69] = 'cSensibilidadDolor';
				$laReturn[70] = 'key70';
				$laReturn[76] = 'cFumar';
				$laReturn[77] = 'cVecesFuma';
				$laReturn[78] = 'cAlcohol';
				$laReturn[79] = 'cEjercicio';
				$laReturn[81] = 'cAvdIndependiente';
				$laReturn[82] = 'cAvdBanarse';
				$laReturn[83] = 'cAvdComer';
				$laReturn[84] = 'cAvdVestirse';
				$laReturn[85] = 'cAvidIndependiente';
				$laReturn[86] = 'cAvdTraslado';
				$laReturn[87] = 'cAvidTrabajo';
				$laReturn[88] = 'cAvidVidaSocial';
				$laReturn[93] = 'cAvdCambioPosicion';
				$laReturn[89] = 'cLocalizacionDolor';
				$laReturn[92] = 'cPresentaNauceas';
				$laReturn[94] = 'cCaminataValorable';
				$laReturn[95] = 'cCaminataCausa';
				break;

			// Lesiones musculoesqueleticas
			case 3:
				$laReturn[ 4] = 'key04';
				$laReturn[10] = 'cDisnea';
				$laReturn[11] = 'cAuscultacion';
				$laReturn[12] = 'cExcToraxica';
				$laReturn[13] = 'cPatronRespiratorio';
				$laReturn[14] = 'cRitmoRespiratorio';
				$laReturn[17] = 'cIntensidadDolor';
				$laReturn[18] = 'cTipoDolor';
				$laReturn[19] = 'key19';
				$laReturn[20] = 'key20';
				$laReturn[21] = 'cTipoEspasmo';
				$laReturn[22] = 'key22';
				$laReturn[23] = 'key23';
				$laReturn[24] = 'key24';
				$laReturn[25] = 'cSisIntegumentarioTrofica';
				$laReturn[26] = 'cSisIntegumentarioMecanica';
				$laReturn[27] = 'cSisIntegumentarioEscaras';
				$laReturn[28] = 'cSisIntegumentarioEdema';
				$laReturn[29] = 'key29';
				$laReturn[30] = 'key30';
				$laReturn[31] = 'key31';
				$laReturn[32] = 'cMarchaMarcha';
				$laReturn[33] = 'cMarchaAyudas';
				$laReturn[34] = 'cMarchaRitmo';
				$laReturn[35] = 'cMarchaLongitud';
				$laReturn[36] = 'cMarchaCircunduccion';
				$laReturn[37] = 'cMarchaDisociacion';
				$laReturn[38] = 'cMarchaBalanceMmss';
				$laReturn[40] = 'cSedenteEstatico';
				$laReturn[41] = 'cSedenteDinamico';
				$laReturn[42] = 'cBipedaEstatico';
				$laReturn[43] = 'cBipedaDinamico';
				$laReturn[74] = 'cSensibilidadSuperficial';
				$laReturn[75] = 'cSensibilidadProfunda';
				$laReturn[78] = 'cPropiocepcionEstatico';
				$laReturn[79] = 'cPropiocepcionDinamico';
				$laReturn[81] = 'key81';
				$laReturn[82] = 'key82';
				$laReturn[83] = 'key83';
				$laReturn[89] = 'key89';
				$laReturn[92] = 'key92';
			break;

			// Desacondicionamiento
			case 6:
				$laReturn[ 4] = 'key04';
				$laReturn[10] = 'cDisnea';
				$laReturn[11] = 'cAuscultacion';
				$laReturn[12] = 'cExcToraxica';
				$laReturn[13] = 'cPatronRespiratorio';
				$laReturn[14] = 'cRitmoRespiratorio';
				$laReturn[17] = 'key17';
				$laReturn[18] = 'key18';
				$laReturn[19] = 'key19';
				$laReturn[20] = 'key20';
				$laReturn[22] = 'key22';
				$laReturn[23] = 'key23';
				$laReturn[24] = 'cMarchaMarcha';
				$laReturn[25] = 'cMarchaAyudas';
				$laReturn[26] = 'cMarchaRitmo';
				$laReturn[27] = 'cMarchaLongitud';
				$laReturn[28] = 'cMarchaCircunduccion';
				$laReturn[29] = 'cMarchaDisociacion';
				$laReturn[30] = 'cMarchaBalanceMmss';
				$laReturn[31] = 'key31';
				$laReturn[32] = 'key32';
				$laReturn[33] = 'cRangoMovimientoMmmss';
				$laReturn[34] = 'cRangoMovimientoMmmii';
				$laReturn[35] = 'cFuerzaGlobal';
				$laReturn[36] = 'cFlexibilidad';
				$laReturn[37] = 'key37';
				$laReturn[38] = 'cSisNeuroMuscRomberg';
				$laReturn[39] = 'cSisNeuroMuscEquilibrio';
				$laReturn[40] = 'cSisNeuroMuscCoordinacion';
				$laReturn[41] = 'cSisNeuroMuscDiadococinecia';
				$laReturn[42] = 'cSisNeuroMuscInestabilidadPostural';
				$laReturn[43] = 'cSisIntegumentarioTrofica';
				$laReturn[44] = 'cSisIntegumentarioMecanica';
				$laReturn[45] = 'cSisIntegumentarioEscaras';
				$laReturn[46] = 'cSisIntegumentarioEdema';
				$laReturn[74] = 'cSensibilidadSuperficial';
				$laReturn[75] = 'cSensibilidadProfunda';
				$laReturn[78] = 'cPropiocepcionEstatico';
				$laReturn[79] = 'cPropiocepcionDinamico';
				$laReturn[81] = 'cIntensidadDolor';
				$laReturn[82] = 'cTipoDolor';
				$laReturn[83] = 'cSensibilidadDolor';
				$laReturn[89] = 'cLocalizacionDolor';
				$laReturn[92] = 'cPresentaNauceas';
			break;

			// SistIntegumentario
			case 7:
				$laReturn[ 4] = 'key04';
				$laReturn[10] = 'cDisnea';
				$laReturn[11] = 'cAuscultacion';
				$laReturn[12] = 'cExcToraxica';
				$laReturn[13] = 'cPatronRespiratorio';
				$laReturn[14] = 'cRitmoRespiratorio';
				$laReturn[17] = 'cColorPielRojaEritema';
				$laReturn[18] = 'cColorPielRojaEquimosis';
				$laReturn[19] = 'cColorPielRosa';
				$laReturn[20] = 'cColorPielAmarillo';
				$laReturn[21] = 'cColorPielVerde';
				$laReturn[22] = 'cColorPielAzul';
				$laReturn[23] = 'cColorPielMarron';
				$laReturn[24] = 'cColorPielNegro';
				$laReturn[25] = 'cPropTroficasTemperatura';
				$laReturn[26] = 'cPropTroficasPulsos';
				$laReturn[27] = 'cPropTroficasSudoriperas';
				$laReturn[28] = 'cPropTroficasSebaceas';
				$laReturn[29] = 'cPropTroficasCicatriz';
				$laReturn[30] = 'cPropTroficasSensibilidad';
				$laReturn[31] = 'cPropMecanicasElasticidad';
				$laReturn[32] = 'cPropMecanicasGrosor';
				$laReturn[33] = 'cPropMecanicasExtensibilidad';
				$laReturn[34] = 'cPropMecanicasMovilidad';
				$laReturn[35] = 'cPropMecanicasEdema';
				$laReturn[36] = 'cPropMecanicasManchas';
				$laReturn[37] = 'cPropMecanicasEscaras';
				$laReturn[38] = 'cPropMecanicasUnas';
				$laReturn[39] = 'cPropMecanicasPelo';
				$laReturn[78] = 'cPropiocepcionEstatico';
				$laReturn[79] = 'cPropiocepcionDinamico';
				$laReturn[81] = 'cIntensidadDolor';
				$laReturn[82] = 'cTipoDolor';
				$laReturn[83] = 'cSensibilidadDolor';
				$laReturn[89] = 'cLocalizacionDolor';
				$laReturn[92] = 'cPresentaNauceas';
			break;

		}
		return $laReturn;
	}

	/*
	 *	Obtiene las notas aclaratorias para un reporte de fisioterapia
	 *
	 *	@param array $taData: Datos del documento recibidos
	 *	@param string $tcLetra: Letra correspondiente al informe a consultar
	 *	@return array con las notas aclaratorias para incluir en aCuerpo
	 */
	private function InsertarNotas($taData, $tcLetra)
	{
		$laForm = 'FIS013'.$tcLetra;
		$laNotas = (new Doc_NotasAclaratorias())->notasAclaratoriasLibro($taData['nIngreso'], $taData['cCUP'], $taData['nConsecCita'], $laForm, 'ASC', false);
		return $laNotas;
	}

	private function InsertarFirma()
	{
		$laRetorno = [];
		$laRetorno[] =['firmas', [['usuario' => $this->cUsuarioRealiza,'prenombre'=>'Fn. '],]];
 		return $laRetorno;
	}


	function fnEscribirLog($tcMensaje, $tbEcho=false) {
		$lcRuta = __DIR__ . '/../Logs/Log_' . date('Ym');
		if (!is_dir($lcRuta)) { mkdir($lcRuta, 0777, true); }
		$lcFileLog = $lcRuta . '/LogAccion_' . date('Ymd') . '.txt';
		$lcMensaje = PHP_EOL . str_repeat('*',100) . PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL . $tcMensaje . PHP_EOL;
		$lnFile = fopen($lcFileLog, 'a');
		chmod($lcFileLog, 0777);
		fputs($lnFile, $lcMensaje);
		fclose($lnFile);
		if ($tbEcho) { echo $lcMensaje; }
	}
	// $this->fnEscribirLog('$loUser:'. PHP_EOL .var_export($loUser,true));
	// $this->fnEscribirLog('Consulta: '. $this->oDb->getQuery() . PHP_EOL . var_export($this->oDb->getBindValue(),true));

}
