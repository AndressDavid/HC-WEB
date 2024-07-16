<?php
namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';
use NUCLEO\AplicacionFunciones;

class Doc_Enf_RiesgoUlcera
{
	protected $oDb;
	protected $aParam = [];
	protected $aUlcera = [];
	protected $aReporte = [
		'cTitulo' => 'ESCALA DE VALORACIÓN DE RIESGO DE ÚLCERA (BRADEN)',
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
		$this->getParametros();
		$this->aUlcera = [];
		$lcPosUlcImg = '';
		$this->oDb
			->select('A.CONULC CNSNOTA, A.CLNULC CNSLIN, A.FDIULC FECHAD, A.HDIULC HORAD, A.OBSULC DETALLE, '
					.'A.INDULC EXISTE, A.LOCULC LOCALIZA, A.FL3ULC PUNTAJE, A.FECULC FECHAC, A.HORULC HORAC, '
					.'A.CNTULC CNSULC, IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') USUARIO')
			->from('ENULCER A')
			->leftJoin('RIARGMN B', 'A.USRULC=B.USUARI', null)
			->where(['A.INGULC'=>$taData['nIngreso']]);
		if ($tbUltimo) {
			$laRiesgo = $this->oDb
				//->where('A.FECULC*1000000+A.HORULC = (SELECT MAX(F.FECULC*1000000+F.HORULC) FROM ENULCER F WHERE F.INGULC=A.INGULC)')
				->where('A.CNTULC = (SELECT MAX(F.CNTULC) FROM ENULCER F WHERE F.INGULC=A.INGULC)')
				->getAll('array');
		} else {
			$lnFecha = intval(substr(str_replace('-','',$taData['tFechaHora']),0,8));
			$lnHora  = intval(substr(str_replace(':','',$taData['tFechaHora']),11,6));
			$laRiesgo = $this->oDb
				->where(['A.FECULC'=>$lnFecha, 'A.HORULC'=>$lnHora])
				->orderBy('A.FDIULC, A.HDIULC')
				->getAll('array');
		}
		if ($this->oDb->numRows()>0) {
			foreach ($laRiesgo as $laFila) {
				$lcPosUlcImg .= $laFila['LOCALIZA'];
			}
			$laRiesgo = array_map('trim', $laRiesgo[0]);

			// Organizar Datos
			$lnPuntaje = intval($laRiesgo['PUNTAJE']);
			$this->aUlcera = [
				'NOTA'		=> $laRiesgo['CNSNOTA'],
				'CNSULC'	=> $laRiesgo['CNSULC'],
				'FECHA'		=> $laRiesgo['FECHAD'],
				'HORA'		=> $laRiesgo['HORAD'],
				'TOTAL'		=> $lnPuntaje,
				'USUARIO'	=> $laRiesgo['USUARIO'],
				'RIESGO'	=> $lnPuntaje<13 ? 'ALTO' : ($lnPuntaje<15 ? 'MEDIO' : 'BAJO'),
				'EXISTE'	=> $laRiesgo['EXISTE']=='1' ? 'SI' : 'NO',
			];

			// Preguntas
			foreach ($this->aParam as $lcClave => $laParam) {
				$lnPos = strpos($laRiesgo['DETALLE'], $lcClave);
				if (!($lnPos===false)) {
					$lcValor = trim(substr($laRiesgo['DETALLE'], $lnPos + strlen($lcClave) + 2, 2));
					$this->aUlcera['PREG'][$lcClave] = [
						'DSC' => $this->aParam[$lcClave][$lcValor],
						'VLR' => $lcValor,
					];
				}
			}

			// Localización úlceras
			if ($laRiesgo['EXISTE']=='1' && !empty($lcPosUlcImg)) {
				$loXML = simplexml_load_string(utf8_decode($lcPosUlcImg));
				if (!($loXML === FALSE)) {
					$lcJson = json_encode($loXML);
					$laJson = json_decode($lcJson, true);
					$laPos = isset($laJson['oCntpanel']['name']) ? $laJson : $laJson['oCntpanel'];
					foreach ($laPos as $laPosUlc) {
						$lcTipo = substr($laPosUlc['name'],0,8)=='lbltexto' ? 'texto' :
								( substr($laPosUlc['name'],0,15)=='shpmarcacirculo' ? 'circulo' :
								( substr($laPosUlc['name'],0,14)=='shpmarcacuadro' ? 'cuadro' : ''));
						$laPos = [
							'tipo'	=> $lcTipo,
							'top'	=> $laPosUlc['top'],
							'left'	=> $laPosUlc['left'],
							'color'	=> AplicacionFunciones::colorFoxToRGB($laPosUlc['BackColor']),
						];
						if ($lcTipo=='texto') $laPos['ttl'] = $laPosUlc['titulo'];
						$this->aUlcera['ULCERAS'][] = $laPos;
					}
				}
			}
		}
	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme()
	{
		$laTr = [];

		$this->aReporte['aCuerpo'] = $laTr;
	}

	public function getParametros()
	{
		// Parámetros para riesgo de úlcera
		$laTtlPar=[
			1=>['Percepcion', 'Percepción Sensorial'],
			2=>['Exposicion', 'Exposición a la Humedad'],
			3=>['Actividad'	, 'Actividad'],
			4=>['Nutricion'	, 'Nutrición'],
			5=>['Movilidad'	, 'Movilidad'],
			6=>['Riesgo'	, 'Riesgo Lesiones Cutáneas'],
		];
		$laParam = $this->oDb
			->select('VARENF PREG, DESENF DESCR, REFENF VALOR')
			->from('TABENF')
			->where('TIPENF=73')
			->getAll('array');
		foreach ($laParam as $laPar) {
			$laPar = array_map('trim',$laPar);
			$lcTtl = $laTtlPar[$laPar['PREG']][0];
			$this->aParam[$lcTtl]['DSC'] = $laTtlPar[$laPar['PREG']][1];
			$this->aParam[$lcTtl][$laPar['VALOR']] = $laPar['DESCR'];
		}
	}

	public function aUlcera()
	{
		return $this->aUlcera;
	}

	public function aParam()
	{
		return $this->aParam;
	}

}

