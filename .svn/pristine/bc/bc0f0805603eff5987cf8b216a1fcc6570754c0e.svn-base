<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

class Doc_Enf_Sensorica
{
	protected $oDb;
	protected $aNota = [];
	protected $aObsv = [];
	protected $aPrm = [];
	protected $nEdad = 0;
	protected $cTextoPandemia = '';
	protected $aReporte = [];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->consultaParam();
	}


	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData)
	{
		$this->nEdad = intval($taData['oIngrPaciente']->aEdad['y']);
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}


	private function consultarDatos($taData)
	{
		$this->aNota = [
			'ingreso'	=> $taData['nIngreso'],
			'nota'		=> $taData['nConsecDoc'],
			'datos'		=> [],
		];

		$laNota = $this->oDb
			->select('N.ID_SNR,N.FECSNR,N.HORSNR,N.LINSNR,N.DATSNR,N.USCSNR')
			->select("TRIM(E.NNOMED)||' '||TRIM(E.NOMMED) NOMBRE")
			->select('TRIM(TABCOD) TIPOCOD,TRIM(T.TABDSC) TIPOUSU')
			->from('ENFSNSR N')
			->leftJoin('RIARGMN E', 'N.USCSNR=E.USUARI')
			->leftJoin('PRMTAB T', "T.TABTIP='TUS' AND CHAR(E.TPMRGM)=T.TABCOD")
			->where([
				'INGSNR'=>$taData['nIngreso'],
				'NOTSNR'=>$taData['nConsecDoc'],
				'TIPSNR'=>'NOTDE',
			])
			->orderBy('FECSNR, HORSNR, LINSNR')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$lcJson = '';
			$lnFechaHora = 0;
			foreach ($laNota as $laValue) {
				$lnFHItem = $laValue['FECSNR'] * 1000000 +  $laValue['HORSNR'];
				if ($lnFechaHora !== $lnFHItem) {
					if ($lnFechaHora > 0 && mb_strlen($lcJson) > 0) {
						$this->aNota['datos'][$lnFechaHora]['data'] = json_decode($lcJson, true);
						if (isset($this->aNota['datos'][$lnFechaHora]['data']['pandemia']) && mb_strlen($this->cTextoPandemia)==0){
							// Se toma el primer texto pandemia
							$this->cTextoPandemia = $this->aNota[$lnFechaHora]['data']['pandemia']['obs'] ?? '';
						}
					}
					$lnFechaHora = $lnFHItem;
					$this->aNota['datos'][$lnFechaHora] = [
						'fecha'		=> $laValue['FECSNR'],
						'hora'		=> $laValue['HORSNR'],
						'usuario'	=> $laValue['USCSNR'],
						'nombre'	=> $laValue['NOMBRE'],
						'tipocod'	=> $laValue['TIPOCOD'],
						'tipousu'	=> $laValue['TIPOUSU'],
					];
					$lcJson = '';
				}
				$lcJson .= $laValue['DATSNR'];
			}
			if ($lnFechaHora > 0 && mb_strlen($lcJson) > 0) {
				$this->aNota['datos'][$lnFechaHora]['data'] = json_decode($lcJson, true);
			}
		}

		// Observaciones
		$laNota = $this->oDb
			->select('N.ID_SNR,N.FECSNR,N.HORSNR,N.LINSNR,N.DATSNR,N.USCSNR')
			->select("TRIM(E.NNOMED)||' '||TRIM(E.NOMMED) NOMBRE")
			->select('TRIM(TABCOD) TIPOCOD,TRIM(T.TABDSC) TIPOUSU')
			->from('ENFSNSR N')
			->leftJoin('RIARGMN E', 'N.USCSNR=E.USUARI')
			->leftJoin('PRMTAB T', "T.TABTIP='TUS' AND CHAR(E.TPMRGM)=T.TABCOD")
			->where([
				'INGSNR'=>$taData['nIngreso'],
				'NOTSNR'=>$taData['nConsecDoc'],
				'TIPSNR'=>'OBSRV',
			])
			->orderBy('FECSNR, HORSNR, LINSNR')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$lcJson = '';
			$lnFechaHora = 0;
			foreach ($laNota as $laValue) {
				$lnFHItem = $laValue['FECSNR'] * 1000000 +  $laValue['HORSNR'];
				if ($lnFechaHora !== $lnFHItem) {
					if ($lnFechaHora > 0 && mb_strlen($lcJson) > 0) {
						$this->aObsv['datos'][$lnFechaHora]['data'] = json_decode($lcJson, true);
					}
					$lnFechaHora = $lnFHItem;
					$this->aObsv['datos'][$lnFechaHora] = [
						'fecha'		=> $laValue['FECSNR'],
						'hora'		=> $laValue['HORSNR'],
						'usuario'	=> $laValue['USCSNR'],
						'nombre'	=> $laValue['NOMBRE'],
						'tipocod'	=> $laValue['TIPOCOD'],
						'tipousu'	=> $laValue['TIPOUSU'],
					];
					$lcJson = '';
				}
				$lcJson .= $laValue['DATSNR'];
			}
			if ($lnFechaHora > 0 && mb_strlen($lcJson) > 0) {
				$this->aObsv['datos'][$lnFechaHora]['data'] = json_decode($lcJson, true);
			}
		}
	}


	private function prepararInforme($taData)
	{
		$lcSL = "\n"; //PHP_EOL;

		$this->aReporte['cTitulo'] = 'NOTA DE ENFERMERIA SENSÓRICA ' . $taData['nConsecDoc'];

		$laAnchoSV = [20,10,10,10,10,10,10,10,10,10,10,10,10,10,47];
		$laAlingSV = ['C','C','C','C','C','C','C','C','C','C','C','C','C','C','L'];
		$laTr = $laTrSignos = [];
		$lcUsuario = '';
		$laResumenSignos = [
			'tmp' 	=> ['sum'=>0,'cnt'=>0],
			'fc'	=> ['sum'=>0,'cnt'=>0],
			'fr'	=> ['sum'=>0,'cnt'=>0],
			'so2'	=> ['sum'=>0,'cnt'=>0],
			'tas'	=> ['sum'=>0,'cnt'=>0],
			'tad'	=> ['sum'=>0,'cnt'=>0],
			'tam'	=> ['sum'=>0,'cnt'=>0],
			'vs'	=> ['sum'=>0,'cnt'=>0],
			'ic'	=> ['sum'=>0,'cnt'=>0],
			'vfc'	=> ['sum'=>0,'cnt'=>0],
			'gc'	=> ['sum'=>0,'cnt'=>0],
			'rvs'	=> ['sum'=>0,'cnt'=>0],
		];

		if (mb_strlen(trim($this->cTextoPandemia))){
			$laTr[] = ['titulo1', ''];
			$laTr[] = ['texto9', trim($this->cTextoPandemia)];
		}

		foreach ($this->aNota['datos'] as $laNota) {
			if (strlen($lcUsuario)==0) $lcUsuario = $laNota['usuario'];
			$lcFechaHora = AplicacionFunciones::formatFechaHora('fechahora', $laNota['fecha'] * 1000000 + $laNota['hora']);
			$laTm = [];

			if (isset($laNota['data']['estado'])) {
				$laDatos = $laNota['data']['estado'];
				$laTm[] = ['titulo2', 'ESTADO'];
				if (isset($laDatos['estcon']))	$laTm[] = ['texto9', 'Estado de Conciencia: '.AplicacionFunciones::lookup($this->aPrm['EST_CONC'], 'DSC', $laDatos['estcon'], 'COD')];
				if (isset($laDatos['algerSN']))	$laTm[] = ['texto9', 'Alergias: '.$laDatos['algerSN'].(isset($laDatos['alergia']) ? ' - '.$laDatos['alergia'] : '')];
				if (isset($laDatos['talla']))	$laTm[] = ['texto9', "Talla: {$laDatos['talla']} cm"];
				if (isset($laDatos['peso']))	$laTm[] = ['texto9', "Peso: {$laDatos['peso']} {$laDatos['pesoud']}"];
				if (isset($laDatos['obs']))		$laTm[] = ['texto9', "Observaciones: {$laDatos['obs']}"];
			}

			if (isset($laNota['data']['signos'])) {
				$laDatos = $laNota['data']['signos'];
				$lcDolor = isset($laDatos['escdol']) ? AplicacionFunciones::lookup($this->aPrm['ESC_DOLOR'], 'DSC', $laDatos['escdol'], 'COD') : '';

				if (isset($laDatos['tmp'])) 	{ $laResumenSignos['tmp']['sum']+=$laDatos['tmp'];	$laResumenSignos['tmp']['cnt']+=1; }
				if (isset($laDatos['fc']))		{ $laResumenSignos['fc']['sum'] +=$laDatos['fc'];	$laResumenSignos['fc']['cnt'] +=1; }
				if (isset($laDatos['fr']))		{ $laResumenSignos['fr']['sum'] +=$laDatos['fr'];	$laResumenSignos['fr']['cnt'] +=1; }
				if (isset($laDatos['so2']))		{ $laResumenSignos['so2']['sum']+=$laDatos['so2'];	$laResumenSignos['so2']['cnt']+=1; }
				if (isset($laDatos['tas']))		{ $laResumenSignos['tas']['sum']+=$laDatos['tas'];	$laResumenSignos['tas']['cnt']+=1; }
				if (isset($laDatos['tad']))		{ $laResumenSignos['tad']['sum']+=$laDatos['tad'];	$laResumenSignos['tad']['cnt']+=1; }
				if (isset($laDatos['tam']))		{ $laResumenSignos['tam']['sum']+=$laDatos['tam'];	$laResumenSignos['tam']['cnt']+=1; }
				if (isset($laDatos['vs']))		{ $laResumenSignos['vs']['sum'] +=$laDatos['vs'];	$laResumenSignos['vs']['cnt'] +=1; }
				if (isset($laDatos['ic']))		{ $laResumenSignos['ic']['sum'] +=$laDatos['ic'];	$laResumenSignos['ic']['cnt'] +=1; }
				if (isset($laDatos['vfc']))		{ $laResumenSignos['vfc']['sum']+=$laDatos['vfc'];	$laResumenSignos['vfc']['cnt']+=1; }
				if (isset($laDatos['gc']))		{ $laResumenSignos['gc']['sum'] +=$laDatos['gc'];	$laResumenSignos['gc']['cnt'] +=1; }
				if (isset($laDatos['rvs']))		{ $laResumenSignos['rvs']['sum']+=$laDatos['rvs'];	$laResumenSignos['rvs']['cnt']+=1; }

				$laTrSignos[] = [
					'w'=>$laAnchoSV,
					'a'=>$laAlingSV,
					'd'=>[
						$lcFechaHora,
						$laDatos['tmp']??'n/v',
						$laDatos['fc']??'',
						$laDatos['fr']??'',
						$laDatos['so2']??'',
						$lcDolor,
						$laDatos['tas']??'',
						$laDatos['tad']??'',
						$laDatos['tam']??'',
						$laDatos['vs']??'',
						$laDatos['ic']??'',
						$laDatos['vfc']??'',
						$laDatos['gc']??'',
						$laDatos['rvs']??'',
						$laDatos['obs']??'',
					],
				];
			}


			if (isset($laNota['data']['piel'])) {
				$laDatos = $laNota['data']['piel'];
				$laTm[] = ['titulo2', 'PIEL'];
				if (isset($laDatos['estado']))	$laTm[] = ['texto9', 'Estado piel: '.AplicacionFunciones::lookup($this->aPrm['EST_PIEL'], 'DSC', $laDatos['estado'], 'COD')];
				if (isset($laDatos['tipo']))	$laTm[] = ['texto9', 'Acción: '.AplicacionFunciones::lookup($this->aPrm['TIPO_PIEL'], 'DSC', $laDatos['tipo'], 'COD')];
				if (isset($laDatos['dscr']))	$laTm[] = ['texto9', "Descripción: {$laDatos['dscr']}"];
				if (isset($laDatos['obs']))		$laTm[] = ['texto9', "Observaciones: {$laDatos['obs']}"];
			}


			if (isset($laNota['data']['cardio'])) {
				$laDatos = $laNota['data']['cardio'];
				$laTm[] = ['titulo2', 'CARDIOVASCULAR'];
				if (isset($laDatos['disp']))	$laTm[] = ['texto9', 'Dispositivo Cardiaco Implantable: '.AplicacionFunciones::lookup($this->aPrm['DISP_CARD'], 'DSC', $laDatos['disp'], 'COD')];
				if (isset($laDatos['modo']))	$laTm[] = ['texto9', 'Modo: '.AplicacionFunciones::lookup($this->aPrm['MODO_DISP'], 'DSC', $laDatos['modo'], 'COD')];
				if (isset($laDatos['doltrx']))	$laTm[] = ['texto9', 'Dolor Torax: '.($laDatos['doltrx']==1 ? 'Si' : 'No')];
				if (isset($laDatos['obs']))		$laTm[] = ['texto9', "Observaciones: {$laDatos['obs']}"];
			}


			if (isset($laNota['data']['sensori'])) {
				$laDatos = $laNota['data']['sensori'];
				$laTm[] = ['titulo2', 'EDUCACIÓN'];
				if (isset($laDatos['idioma']))	$laTm[] = ['texto9', 'Idioma que habla el paciente: '.($laDatos['idioma']=='999' ? $laDatos['idioma_otro'] : AplicacionFunciones::lookup($this->aPrm['IDIOMA'], 'DSC', $laDatos['idioma'], 'COD'))];
				if (isset($laDatos['barreras'])) {
					if (isset($laDatos['barreras']['ninguna'])) {
						$laTm[] = ['texto9', 'Barreras para la comunicación: Ninguna'];
					} else {
						$laTm[] = ['texto9', 'Barreras para la comunicación:'];
						if (isset($laDatos['visual']))	$laTm[] = ['texto9', ' - Visual: '.($laDatos['visual_obs']??'')];
						if (isset($laDatos['auditv']))	$laTm[] = ['texto9', ' - Auditiva: '.($laDatos['auditv_obs']??'')];
						if (isset($laDatos['lengua']))	$laTm[] = ['texto9', ' - Lenguaje: '.($laDatos['lengua_obs']??'')];
					}
				}
				if (isset($laDatos['educacion'])) {
					if ($laDatos['educacion']['brindo']=='Si') {
						$laTm[] = ['texto9', 'Se brindo educación al paciente o la familia: Si'];
						if (isset($laDatos['educacion']['temas'])) {
							$laTemas = explode(',', $laDatos['educacion']['temas']);
							$lcTemas = '';
							foreach ($laTemas as $lnTema) {
								$lcTemas .= $lcSL . '    - ' . AplicacionFunciones::lookup($this->aPrm['EDU_TEMAS'], 'DSC', $lnTema, 'COD');
							}
							$laTm[] = ['texto9', " - Temas: $lcTemas"];
						}
						if (isset($laDatos['educacion']['ayudas'])) {
							$laAyudas = explode(',', $laDatos['educacion']['ayudas']);
							$lcAyudas = '';
							foreach ($laAyudas as $lnAyuda) {
								$lcAyudas .= $lcSL . '    - ' . AplicacionFunciones::lookup($this->aPrm['EDU_AYUDAS'], 'DSC', $lnAyuda, 'COD');
							}
							$laTm[] = ['texto9', " - Ayudas: $lcAyudas"];
						}
						if ($laDatos['educacion']['evalua']==1) {
							$laTm[] = ['texto9', ' - ¿Realizó evaluación de la educación brindada?: Si'];
							$laTm[] = ['texto9', ' - Método de evaluación: '.AplicacionFunciones::lookup($this->aPrm['EDU_EVALUA'], 'DSC', $laDatos['educacion']['metodo'], 'COD')];
						} else {
							$laTm[] = ['texto9', ' - ¿Realizó evaluación de la educación brindada?: No'];
							$laTm[] = ['texto9', " - ¿Por qué no se realizó?: {$laDatos['educacion']['porqueno']}"];
						}
					} else {
						$laTm[] = ['texto9', 'Se brindo educación al paciente o la familia: No'];
					}
				}
				if (isset($laDatos['obs']))		$laTm[] = ['texto9', "Observaciones: {$laDatos['obs']}"];
			}

			if (count($laTm)>0) {
				$laTr[] = ['titulo1', "$lcFechaHora - {$laNota['data']['ubica']} - {$laNota['nombre']}"];
				$laTr = array_merge($laTr, $laTm);
				$laTr[] = ['lineah', []];
				$laTr[] = ['saltol', 5];
			}
		}

		if (count($laTrSignos)>0) {
			$laTrSignos[] = [
				'w'=>$laAnchoSV,
				'a'=>$laAlingSV,
				'd'=>[
					'<b>Promedios</b>',
					$laResumenSignos['tmp']['sum']>0 ? round($laResumenSignos['tmp']['sum']/$laResumenSignos['tmp']['cnt'],1) : '-',
					$laResumenSignos['fc']['sum']>0 ? round($laResumenSignos['fc']['sum']/$laResumenSignos['fc']['cnt'],0) : '-',
					$laResumenSignos['fr']['sum']>0 ? round($laResumenSignos['fr']['sum']/$laResumenSignos['fr']['cnt'],0) : '-',
					$laResumenSignos['so2']['sum']>0 ? round($laResumenSignos['so2']['sum']/$laResumenSignos['so2']['cnt'],0) : '-',
					'',
					$laResumenSignos['tas']['sum']>0 ? round($laResumenSignos['tas']['sum']/$laResumenSignos['tas']['cnt'],0) : '-',
					$laResumenSignos['tad']['sum']>0 ? round($laResumenSignos['tad']['sum']/$laResumenSignos['tad']['cnt'],0) : '-',
					$laResumenSignos['tam']['sum']>0 ? round($laResumenSignos['tam']['sum']/$laResumenSignos['tam']['cnt'],0) : '-',
					$laResumenSignos['vs']['sum']>0 ? round($laResumenSignos['vs']['sum']/$laResumenSignos['vs']['cnt'],1) : '-',
					$laResumenSignos['ic']['sum']>0 ? round($laResumenSignos['ic']['sum']/$laResumenSignos['ic']['cnt'],1) : '-',
					$laResumenSignos['vfc']['sum']>0 ? round($laResumenSignos['vfc']['sum']/$laResumenSignos['vfc']['cnt'],0) : '-',
					$laResumenSignos['gc']['sum']>0 ? round($laResumenSignos['gc']['sum']/$laResumenSignos['gc']['cnt'],1) : '-',
					$laResumenSignos['rvs']['sum']>0 ? round($laResumenSignos['rvs']['sum']/$laResumenSignos['rvs']['cnt'],1) : '-',
					'',
				],
			];
			$laTr[] = ['titulo1', 'Signos'];
			$laTr[] = [
				'tabla',
				[[
					'w'=>$laAnchoSV,
					'a'=>'C',
					'd'=>[
						'Fecha y<br>hora',
						'T<br>(°C)',
						'FC<br>(lpm)',
						'FR<br>(rpm)',
						'SO<sub>2</sub><br>(%)',
						'Esc<br>Dolor',
						'TAS<br>(mmHg)',
						'TAD<br>(mmHg)',
						'TAM<br>(mmHg)',
						'VS<br>(mL<br>/lat)',
						'IC<br>(L/min/m<sup>2</sup>)',
						'VFC<br>(%)',
						'GC<br>(L<br>/min)',
						'RVS<br>(din*s<br>/cm<sup>5</sup>)',
						'Observaciones'
					]
				]],
				$laTrSignos, ['fs'=>7.5, 'l'=>1],
			];
		}

		if (count($this->aObsv)>0) {
			$laTr[] = ['titulo1', 'OBSERVACIONES'];
			foreach ($this->aObsv['datos'] as $laNota) {
				$lcFechaHora = AplicacionFunciones::formatFechaHora('fechahora', $laNota['fecha'] * 1000000 + $laNota['hora']);
				$laTr[] = ['titulo2', "$lcFechaHora - {$laNota['data']['ubica']} - {$laNota['nombre']}"];
				$laTr[] = ['texto9', $laNota['data']['nota']];
			}
		}

		if (count($laTrSignos)>0 || count($this->aObsv)>0) {
			if (strlen($lcUsuario)>0) $laTr[] = ['firmas', [ ['usuario'=>$lcUsuario], ] ];
		}

		$this->aReporte['aCuerpo'] = $laTr;
	}


	private function consultaParam()
	{
		$this->aPrm = [
			'EST_CONC'	=> $this->consultaData('TIPENF= 4 AND VARENF=1 AND REFENF>0', 'REFENF'),
		//	'UNI_PESO'	=> $this->consultaData('TIPENF= 5 AND VARENF=6 AND REFENF>0', 'REFENF'),
			'ESC_DOLOR'	=> $this->consultaData('TIPENF=14 AND VARENF=1', 'REFENF'),
			'EST_PIEL'	=> $this->consultaData('TIPENF=10 AND VARENF=1 AND REFENF>0', 'ORDENF'),
			'DISP_CARD'	=> $this->consultaData('TIPENF= 6 AND VARENF=3 AND REFENF>0', 'REFENF'),
			'MODO_DISP'	=> $this->consultaData('TIPENF=74 AND VARENF=6 AND REFENF>0', 'REFENF'),
		];
		$laIndices = [
			1 => 'IDIOMA',
			2 => 'EDU_TEMAS',
			3 => 'EDU_AYUDAS',
			4 => 'EDU_EVALUA',
			5 => 'TIPO_PIEL',
		//	99=> 'CUPS',
		];
		$laDatos = $this->oDb
			->select('VARENF TIPO, REFENF COD, TRIM(DESENF) DSC')
			->from('TABENF')
			->where('TIPENF=77 AND REFENF>0')
			->orderBy('VARENF, REFENF')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laKeyIndex = array_keys($laIndices);
			foreach ($laDatos as $laValor) {
				if (!in_array($laValor['TIPO'], $laKeyIndex)) continue;
				$lcIndice = $laIndices[$laValor['TIPO']];
				$this->aPrm[$lcIndice][] = [
					'COD' => $laValor['COD'],
					'DSC' => $laValor['DSC'],
				];
			}
		}
	}


	private function consultaData($tcWhere, $tcOrder)
	{
		$laDatos = $this->oDb
			->select('REFENF COD, TRIM(DESENF) DSC')
			->from('TABENF')
			->where($tcWhere)
			->orderBy($tcOrder)
			->getAll('array');
		if ($this->oDb->numRows()==0) $laDatos=[];
		return $laDatos;
	}


}