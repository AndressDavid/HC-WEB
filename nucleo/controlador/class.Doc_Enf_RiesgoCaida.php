<?php
namespace NUCLEO;

class Doc_Enf_RiesgoCaida
{
	protected $oDb;
	protected $aRiesgoCaida = [];
	protected $aParamCaida = [];
	protected $aParamCaidaP = [];
	protected $aCaida = [];
	protected $aReporte = [
		'cTitulo' => 'ESCALA DE VALORACION DE RIESGO DE CAIDAS',
		'lMostrarEncabezado' => true,
		'lMostrarFechaRealizado' => true,
		'lMostrarViaCama' => true,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
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
		$this->consultarDatos($taData);
		$this->prepararInforme();

		return $this->aReporte;
	}

	/*
	 *	Consulta los datos
	 *	@param array $taData: array con al menos los siguientes elementos:
	 *		- tFechaHora: string con la fecha y hora de consulta (solo se tiene en cuenta la fecha), se puede omitir si $tbUltimo es true
	 *		- nIngreso: entero con el número de ingreso del paciente
	 *	@param boolean $tbUltimo: Si es true se consulta solo la última escala. Predeterminado false
	 */
	public function consultarDatos($taData, $tbUltimo=false)
	{
		$this->aCaida = [];
		$this->oDb
			->select('A.CONCAI AS CNSNOTA, A.CNTCAI AS CNSERC, A.FDICAI AS FECHAD, A.HDICAI AS HORAD, A.OBSCAI AS DETALLE, '
					.'A.FL1CAI AS PUNTAJE, A.FL2CAI AS TIPO, A.FECCAI AS FECHAC, A.HORCAI AS HORACR, '
					.'IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS USUARIO')
			->from('ENCAIDA AS A')
			->leftJoin('RIARGMN AS B', 'A.USRCAI=B.USUARI', null)
			->where(['A.INGCAI'=>$taData['nIngreso']]);
		if ($tbUltimo) {
			$this->aRiesgoCaida = $this->oDb
				->orderBy('A.FDICAI DESC, A.HDICAI DESC')
				->get('array');
		} else {
			$lnFecha = intval(substr(str_replace('-','',$taData['tFechaHora']),0,8));
			$this->aRiesgoCaida = $this->oDb
				->where(['A.FECCAI'=>$lnFecha])
				->orderBy('A.FDICAI, A.HDICAI')
				->getAll('array');
		}

		// Consulta de parámetros para riesgo de caida adulto
		$this->aParamCaida = $this->oDb
			->select('CL1TMA AS CODIGO, CL2TMA SUBCODIGO, TRIM(DE1TMA) AS DETALLE, TRIM(OP5TMA) AS DETALLE1, OP1TMA AS PUNTAJE')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'CAIDA',
				'ESTTMA'=>'',
			])
			->getAll('array');
		foreach ($this->aParamCaida as &$laParCaida) {
			$laParCaida = array_map('trim',$laParCaida);
		}

		// Consulta de parámetros para riesgo de caida pediatrico
		$this->aParamCaidaP = [];
		$laDatos = $this->oDb
			->select('VARENF, REFENF, DESENF')
			->from('TABENF')
			->where('TIPENF=25')
			->orderBy('VARENF, REFENF')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			foreach ($laDatos as $laDato) {
				$laDato = array_map('trim', $laDato);
				if (!isset($this->aParamCaidaP[$laDato['VARENF']])) $this->aParamCaidaP[$laDato['VARENF']]=[];
				$this->aParamCaidaP[$laDato['VARENF']][$laDato['REFENF']] = $laDato['DESENF'];
			}
		}
		
		// Organizar Datos
		if(is_array($this->aRiesgoCaida)){
			$this->nKey = 0;
			if ($tbUltimo) {
				if($this->aRiesgoCaida['TIPO']=='PD'){
					$this->fnOrganizarDatoPediatrico($this->aRiesgoCaida, 0);
				}else{
					$this->fnOrganizarDato($this->aRiesgoCaida, 0);
				}
			} else {
				foreach($this->aRiesgoCaida as $laCaida){
					if($laCaida['TIPO']=='PD'){
						$this->fnOrganizarDatoPediatrico($laCaida);
					}else{
						$this->fnOrganizarDato($laCaida);
					}
				}
			}
		}
	}

	public function fnOrganizarDato($taCaida)
	{
		$this->aReporte ['cTitulo'] = 'ESCALA DE VALORACION DE RIESGO DE CAIDAS (J.H. DOWNTON)';
		$laRiesgoCaida = [
			'TIPO'		=> 'A',
			'NOTA'		=> $taCaida['CNSNOTA'],
			'CNSERC'	=> $taCaida['CNSERC'],
			'FECHA'		=> $taCaida['FECHAD'],
			'HORA'		=> $taCaida['HORAD'],
			'TOTAL'		=> $taCaida['PUNTAJE'],
			'USUARIO'	=> $taCaida['USUARIO'],
			'RIESGO'	=> (intval($taCaida['PUNTAJE'])>1? 'ALTO' : 'BAJO'),
			'COMPANIA'	=> (substr($taCaida['DETALLE'],17,1)=='S'? 'Si' : 'No'),
		];

		foreach($this->aParamCaida as $laParCaida){
			$llResp = false;
			$llCaidaPr = '';

			switch ($laParCaida['CODIGO']) {

				case '00':
					if ($laParCaida['SUBCODIGO']=='01') {						// Edad
						$lcDato = substr($taCaida['DETALLE'], 5, 1);
						$llResp = $lcDato=='1'? true : false;

					} elseif ($laParCaida['SUBCODIGO']=='02') {					// Caidas Previas
						$lcDato = substr($taCaida['DETALLE'], 29, 1);
						$llResp = true;
						$llCaidaPr = $lcDato=='S'? 'Si' : 'No';
					}
					break;

				case '01':														// Medicamentos
					$lnPos = 42+2*intval($laParCaida['SUBCODIGO']);
					$lcDato = substr($taCaida['DETALLE'], $lnPos, 1);
					$llResp = $lcDato=='1'? true : false;
					break;
					
				case '02':														// Deficiencias
					$lnPos = 71+2*intval($laParCaida['SUBCODIGO']);
					$lcDato = substr($taCaida['DETALLE'], $lnPos, 1);
					$llResp = $lcDato=='1'? true : false;
					break;

				case $laParCaida['CODIGO']=='03':								// Estado mental
					$lcDato = substr($taCaida['DETALLE'],95,2);
					$llResp = $lcDato==$laParCaida['SUBCODIGO'] ? true : false;
					break;

				case $laParCaida['CODIGO']=='04':								// Marcha y deambulacion
					$lcDato = substr($taCaida['DETALLE'],105,2);
					$llResp = $lcDato==$laParCaida['SUBCODIGO'] ? true : false;
					break;
			}

			if ($llResp==true){
				$laRiesgoCaida['PREG'][] = [
					'CRITERIO'	=> $laParCaida['DETALLE1'],
					'VALOR'		=> empty($llCaidaPr)? $laParCaida['DETALLE'] : $llCaidaPr,
					'PUNTAJE'	=> $llCaidaPr=='No'? '0': $laParCaida['PUNTAJE'],
				];
			}
		}

		$this->aCaida[] = $laRiesgoCaida;
	}

	public function fnOrganizarDatoPediatrico($taCaida)
	{	
		$this->aReporte['cTitulo'] = 'ESCALA DE VALORACION DE RIESGO DE CAIDAS (HUMPTY DUMPTY)';
		$laTituloP = [
			1=>'EDAD',
			2=>'GENERO',
			3=>'DIAGNOSTICO',
			4=>'DETERIORO COGNITIVO',
			5=>'FACTORES AMBIENTALES',
			6=>'CIRUGIA O SEDACION ANESTESICA',
			7=>'MEDICACION',
		];

		$laRiesgoCaida = [
			'TIPO'		=> 'P',
			'NOTA'		=> $taCaida['CNSNOTA'],
			'CNSERC'	=> $taCaida['CNSERC'],
			'FECHA'		=> $taCaida['FECHAD'],
			'HORA'		=> $taCaida['HORAD'],
			'TOTAL'		=> $taCaida['PUNTAJE'],
			'USUARIO'	=> $taCaida['USUARIO'],
			'RIESGO'	=> (intval($taCaida['PUNTAJE'])<7? 'SIN RIESGO' : (intval($taCaida['PUNTAJE'])>11? 'RIESGO ALTO' : 'RIESGO BAJO')),
			'COMPANIA'	=> '',
		];

		$lnPosicion = 12;
		$lcDescrip = trim($taCaida['DETALLE']);
		for($lnInd=1; $lnInd<=7; $lnInd++){
			$lcDato = substr($lcDescrip,$lnPosicion,1);
			$lcDetalle = $this->aParamCaidaP[$lnInd][$lcDato];
			$lnPosicion = $lnPosicion+14;
			if(!empty($lcDetalle)){
				$laRiesgoCaida['PREG'][] = [
					'CRITERIO'	=> $laTituloP[$lnInd],
					'VALOR'		=> $lcDetalle,
					'PUNTAJE'	=> $lcDato,
				];
			}
		}
	
		$this->aCaida[] = $laRiesgoCaida;
	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme()
	{
		$laTr = [];
		$lcSL = PHP_EOL;

		//	Tabla
		$laAnchos = [28,70,76,16]; $laAnchosH = [70,76,16];
		$laAnchos1 = [173,17];
		$lcFechaHora = $lcCriterio = '';

		// Datos
		foreach($this->aCaida as $laCaida){

			$lcFechaHora = $laCaida['FECHA'].$laCaida['HORA'];
			$lcFomato = AplicacionFunciones::formatFechaHora('fechahora12', $laCaida['FECHA'].' '.$laCaida['HORA']);

			foreach ($laCaida['PREG'] as $lnKeyP=>$laPregunta) {
				if ($lcCriterio <> $laPregunta['CRITERIO']){
					$lcCriterio = $laPregunta['CRITERIO'];
				} else {
					$laPregunta['CRITERIO'] = '';
				}
				$laDatos[] = $lnKeyP==0 ?
					[
						'w'=>$laAnchos,
						'd'=>[$lcFomato, $laPregunta['CRITERIO'], $laPregunta['VALOR'], $laPregunta['PUNTAJE']],
						'a'=>['C','L','L','C'],
						'f'=>[20,1,1,1]
					]:
					[
						'w'=>$laAnchosH,
						'd'=>[$laPregunta['CRITERIO'], $laPregunta['VALOR'], $laPregunta['PUNTAJE']],
						'a'=>['L','L','C']
					];
			}

			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['FECHA - HORA','CRITERIO','VALOR','PUNTAJE'], 'a'=>'C', ] ],
				$laDatos,
			];

			if(!empty($laCaida['COMPANIA'])){
				$laTr[]= ['tablaSL', [], [
					['w'=>$laAnchos1, 'd'=>['<b>P U N T A J E : </b>',$laCaida['TOTAL']],'a'=>['R','C'] ],
					['w'=>$laAnchos1, 'd'=>['<b>R I E S G O : </b>',$laCaida['RIESGO']],'a'=>['R','C'] ],
					['w'=>$laAnchos1, 'd'=>['<b>Requiere Compañia Permanente ? </b>',$laCaida['COMPANIA']],'a'=>['R','C'] ],
					['w'=>[190], 'd'=>['Enfermero/a que realiza: '.$laCaida['USUARIO']],'a'=>['L'] ],
				], ];
			}else{
				$laTr[]= ['tablaSL', [], [
					['w'=>$laAnchos1, 'd'=>['<b>P U N T A J E : </b>',$laCaida['TOTAL']],'a'=>['R','C'] ],
					['w'=>$laAnchos1, 'd'=>['<b>R I E S G O : </b>',$laCaida['RIESGO']],'a'=>['R','C'] ],
					['w'=>[190], 'd'=>['Enfermero/a que realiza: '.$laCaida['USUARIO']],'a'=>['L'] ],
				], ];	
			}



			$laTr[] = ['saltol', 5];
			$laDatos = [];
		}

		$this->aReporte['aCuerpo'] = $laTr;
	}

	public function aRiesgoCaida()
	{
		return $this->aRiesgoCaida;
	}

	public function aParamCaida()
	{
		return $this->aParamCaida;
	}

	public function aCaida()
	{
		return $this->aCaida;
	}

}

