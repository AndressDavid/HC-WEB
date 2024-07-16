<?php
namespace NUCLEO;

class Doc_Enf_RiesgoFuga

{
	protected $oDb;
	protected $aRiesgoFuga = [];
	protected $aFuga = [];
	protected $aReporte = [
		'cTitulo' => 'ESCALA DE VALORACION DE RIESGO DE FUGA',
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

	public function consultarDatos($taData, $tbUltimo=false)
	{
		$this->aFuga = [];
		$this->oDb
			->select('A.NOTFUG AS CNSNOTA, A.CONFUG AS CNSFUG, A.FDIFUG AS FECHAD, A.HDIFUG AS HORAD, A.DETFUG AS DETALLE, '
					.'A.TOTFUG AS PUNTAJE, A.FECFUG AS FECHAC, A.HORFUG AS HORACR, '
					.'IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS USUARIO')
			->from('ENFFUGA AS A')
			->leftJoin('RIARGMN AS B', 'A.USUFUG=B.USUARI', null)
			->where(['A.INGFUG'=>$taData['nIngreso']]);

			if ($tbUltimo) {
			$this->aRiesgoFuga = $this->oDb
				->orderBy('A.FDIFUG DESC, A.HDIFUG DESC')
				->get('array');
		} else {
			$lnFecha = intval(substr(str_replace('-','',$taData['tFechaHora']),0,8));

			$this->aRiesgoFuga = $this->oDb
				->where(['A.FECFUG'=>$lnFecha])
				->orderBy('A.FDIFUG, A.HDIFUG')
				->getAll('array');
		}

		// Consulta de parámetros para riesgo de Fuga
		$this->aParamFuga = [
			'1. Alerta',
			'2. Deambula Sola',
			'3. Involuntaria',
			'4. Agresividad - Agitación',
			'5. Esquizofrenia, Bipolar, Demencias(Alzheimer, Vascular, Frontotemporal)',
			'6. Trastornos de personalidad',
			'7. Consumo de sustancias (Alcoholismo, Estimulantes, Alucinogenos)',
			'8. Agitación',
			'9. Conciencia de Enfermedad',
			'10. Evasión Previa',
			'11. Intento de Suicidio',
		];

		// Organizar Datos
		if(is_array($this->aRiesgoFuga)){
			$this->nKey = 0;
			if ($tbUltimo) {
				$this->fnOrganizarDato($this->aRiesgoFuga, 0);
			} else {
				foreach($this->aRiesgoFuga as $laFuga){
					$this->fnOrganizarDato($laFuga);
				}
			}
		}
	}

	public function fnOrganizarDato($taFuga)
	{
		$this->aReporte ['cTitulo'] = 'ESCALA DE VALORACION DE FUGA';
		$laRiesgoFuga = [
			'NOTA'		=> $taFuga['CNSNOTA'],
			'CNSFUG'	=> $taFuga['CNSFUG'],
			'FECHA'		=> $taFuga['FECHAD'],
			'HORA'		=> $taFuga['HORAD'],
			'TOTAL'		=> $taFuga['PUNTAJE'],
			'USUARIO'	=> $taFuga['USUARIO'],
			'RIESGO'	=> (intval($taFuga['PUNTAJE'])>2? 'ALTO' : 'BAJO'),
			'COMPANIA'	=> (substr($taFuga['DETALLE'],17,1)=='S'? 'Si' : 'No'),
		];

		if (!empty($taFuga['DETALLE'])){
			$lcInformacion = explode("¤", trim($taFuga['DETALLE']));
			foreach($lcInformacion as $lnKey=>$laDato){
				if(!empty(trim($laDato))){
					$lcDato = explode(":", trim($laDato));
					$laRiesgoFuga['PREG'][] = [
						'CRITERIO'	=> $this->aParamFuga[$lnKey],
						'SI'		=> $lnKey==8?($lcDato[1]==0?'X':''):($lcDato[1]==0?'':'X'),
						'NO'		=> $lnKey==8?($lcDato[1]==0?'':'X'):($lcDato[1]==0?'X':''),
						'PUNTAJE'	=> $lcDato[1],
					];
				}				
			}
		}
		$this->aFuga[] = $laRiesgoFuga;
	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme()
	{
		$laTr = [];
		$lcSL = PHP_EOL;

		//	Tabla
		$laAnchos = [28,120,14,14,14]; $laAnchosH = [120,14,14,14];
		$laAnchos1 = [173,17];
		$lcFechaHora = $lcCriterio = '';

		// Datos
		foreach($this->aFuga as $laFuga){

			$lcFechaHora = $laFuga['FECHA'].$laFuga['HORA'];
			$lcFomato = AplicacionFunciones::formatFechaHora('fechahora12', $laFuga['FECHA'].' '.$laFuga['HORA']);

			foreach ($laFuga['PREG'] as $lnKeyP=>$laPregunta) {
				if ($lcCriterio <> $laPregunta['CRITERIO']){
					$lcCriterio = $laPregunta['CRITERIO'];
				} else {
					$laPregunta['CRITERIO'] = '';
				}
				$laDatos[] = $lnKeyP==0 ?
					[
						'w'=>$laAnchos,
						'd'=>[$lcFomato, $laPregunta['CRITERIO'], $laPregunta['SI'], $laPregunta['NO'], $laPregunta['PUNTAJE']],
						'a'=>['C','L','C','C','C'],
						'f'=>[20,1,1,1]
					]:
					[
						'w'=>$laAnchosH,
						'd'=>[$laPregunta['CRITERIO'], $laPregunta['SI'], $laPregunta['NO'], $laPregunta['PUNTAJE']],
						'a'=>['L','C','C','C']
					];
			}

			$laTr[]= ['tabla',
				[ [ 'w'=>$laAnchos, 'd'=>['FECHA - HORA','CRITERIO','SI','NO','PUNTAJE'], 'a'=>'C', ] ],
				$laDatos,
			];

			if(!empty($laFuga['COMPANIA'])){
				$laTr[]= ['tablaSL', [], [
					['w'=>$laAnchos1, 'd'=>['<b>P U N T A J E : </b>',$laFuga['TOTAL']],'a'=>['R','C'] ],
					['w'=>$laAnchos1, 'd'=>['<b>R I E S G O : </b>',$laFuga['RIESGO']],'a'=>['R','C'] ],
					['w'=>[190], 'd'=>['Enfermero/a que realiza: '.$laFuga['USUARIO']],'a'=>['L'] ],
				], ];
			}else{
				$laTr[]= ['tablaSL', [], [
					['w'=>$laAnchos1, 'd'=>['<b>P U N T A J E : </b>',$laFuga['TOTAL']],'a'=>['R','C'] ],
					['w'=>$laAnchos1, 'd'=>['<b>R I E S G O : </b>',$laFuga['RIESGO']],'a'=>['R','C'] ],
					['w'=>[190], 'd'=>['Enfermero/a que realiza: '.$laFuga['USUARIO']],'a'=>['L'] ],
				], ];	
			}

    		$laTr[] = ['saltol', 5];
			$laDatos = [];
		}

		$this->aReporte['aCuerpo'] = $laTr;
	}

	public function aRiesgoFuga()
	{
		return $this->aRiesgoFuga;
	}

	public function aParamFuga()
	{
		return $this->aParamFuga;
	}

	public function aFuga()
	{
		return $this->aFuga;
	}

}