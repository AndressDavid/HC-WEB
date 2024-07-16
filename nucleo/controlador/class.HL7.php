<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/../publico/complementos/Net_HL7/1.7/HL7/Message.php';
require_once __DIR__ . '/../publico/complementos/Net_HL7/1.7/HL7/Segment.php';
require_once __DIR__ . '/../publico/complementos/Net_HL7/1.7/HL7/Segments/MSH.php';
require_once __DIR__ . '/../publico/complementos/Net_HL7/1.7/HL7.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;

class HL7
{
	private $oDB;
	public $aModelo;
	public $oMensaje;
	public $nConsecutivo;
	public $cTipoMensaje = '';
	public $cEvento = '';
	public $cViaIngresoAgility = '';
	public $cSegmento18Agility = '';
	public $cSeccionCamaAgility = '';
	public $aSegmentos;
	public $oIngreso = null;
	public $oProcedimiento = null;
	public $dFechaHora;
	public $cFechaHora;
	public $cIniBloque;
	public $cFinBloque;
	
	/*
	 * Constructor de la clase
	 */
	function __construct($tcModelo)
	{
		global $goDb;
		$this->oDB = $goDb;
		$this->aModelo['Modelo'] = $tcModelo;
		$this->fnConsultarParametros();
	}

	/* Consulta los parámetros del modelo indicado en el constructor */
	private function fnConsultarParametros()
	{
		$laDatos = $this->oDB
			->select('CL3TMA, CL4TMA, DE1TMA, DE2TMA || OP5TMA AS DATO')
			->from('TABMAE')
			->where(['TIPTMA'=>'HL7_PRM', 'CL1TMA'=>'MODELO', 'CL2TMA'=>$this->aModelo['Modelo'], 'ESTTMA'=>''])
			->where('CL4TMA','<>','')
			->getAll('array');
		foreach ($laDatos as $laDato) {
			$this->aModelo[trim($laDato['CL3TMA'])][trim($laDato['CL4TMA'])] = trim($laDato['DATO']);
		}
		$this->cIniBloque = chr(11);
		$this->cFinBloque = chr(28) . chr(13);
	}

	/*
	 * Crea el mensaje a partir de las plantillas parametrizadas
	 */
	function fnCrearMensaje($tcTipoMensaje, $tcEvento, $taDatos=[])
	{
		$this->cTipoMensaje = $tcTipoMensaje;
		$this->cEvento = $tcEvento;

		// Obtiene datos del ingreso si el mensaje lo requiere
		if ($tcTipoMensaje=='ADT' || $tcTipoMensaje=='ORM'){
			$this->oIngreso = new Ingreso();
			$this->oIngreso->cargarIngreso($taDatos['nIngreso']);
		}

		// Código homologado para mensajes ORM
		if (isset($taDatos['cCodCup'])) {
			if (isset($this->aModelo['HOMOLOGA'][$taDatos['cCodCup']])) {
				$taDatos['cCodHom'] = $this->aModelo['HOMOLOGA'][$taDatos['cCodCup']];
				$taDatos['bHomologado'] = true;
			} else {
				$taDatos['bHomologado'] = false;
			}
		}
		
		//Obtiene fecha y hora y consecutivo para nuevo mensaje
		$this->fnObtenerFechaHoraMensaje();
		
		if ($this->aModelo['Modelo']!='AGFA'){
			$this->fnObtenerConsecutivoMensajeSEQ(true);
		}	
		$this->fnObtenerViaIngreso();
		$this->fnObtenerSeccionHabitacion();
		
		//Crea objeto mensaje
		$this->oMensaje = new \Net_HL7_Message();

		//Segmentos que se deben utilizar
		$this->aSegmentos = explode('-', $this->aModelo['MENSAJE'][$tcTipoMensaje]);
		
 		$lnNum = 0;
		foreach ($this->aSegmentos as $lcSegmento) {
			$lcPlantilla = $this->aModelo['MOD_SEG'][$lcSegmento];
			$laSegPlant = explode('|', substr($lcPlantilla, $lcSegmento=='MSH'? 3: 4));
			$this->oMensaje->addSegment(new \Net_HL7_Segment($lcSegmento, $laSegPlant));
			$this->fnActualizarSegmento($lcSegmento, $lnNum, $taDatos);
			$lnNum++;
		}
	}

	/*
	 * Actualiza los segmentos con los datos enviados (de acuerdo al modelo)
	 */
	function fnActualizarSegmento($tcSegmento, $tnNum, $taDatos) {}
	private function fnObtenerViaIngreso() {}
	private function fnObtenerSeccionHabitacion() {}
	private function fnConsecutivoAgfaAdt() {}
	private function fnConsecutivoAgfaOrm() {}
	
	/*
	 * Obtiene Fecha y hora del mensaje
	 */
	public function fnObtenerFechaHoraMensaje()
	{
		$lcFechaHora = $this->oDB->fechaHoraSistema();
		$this->dFechaHora = new \DateTime($lcFechaHora);
		$this->cFechaHora = $this->dFechaHora->format('YmdHis');
	}

	/*
	 * Obtiene consecutivo del mensaje de RIACON
	 */
	public function fnObtenerConsecutivoMensaje($tbNuevo=true)
	{
		$error = null;

		if ($this->cTipoMensaje=='ACK') {
			// ACK usa fecha hora
			$this->nConsecutivo = $this->cFechaHora;
		} else {
			// Consulta consecutivo actual
			$lcCodCon = substr($this->aModelo['CONSEC']['MSH'], strpos($this->aModelo['CONSEC']['MSH'], $this->cTipoMensaje) + 4, 3);
			$laWhere = ['CODCON'=>$lcCodCon];
			$laDatos = $this->oDB->select('CONCON')->from('RIACON')->where($laWhere)->getAll('array');

			// Actualiza consecutivo en RIACON
			$this->nConsecutivo = $laDatos[0]['CONCON'] + 1;
			$laActualiza = [
				'CONCON' => $this->nConsecutivo,
				'UMOCON' => 'WEB',
				'PMOCON' => 'CrearHL7',
				'FMOCON' => $this->dFechaHora->format('Ymd'),
				'HMOCON' => $this->dFechaHora->format('His'),
			];
			$this->oDB->from('RIACON')->where($laWhere)->actualizar($laActualiza);
		}
	}

	/*
	 * Obtiene consecutivo del mensaje usando secuencias
	 */
	public function fnObtenerConsecutivoMensajeSEQ($tbActualizarRiacon=false)
	{
		if ($this->cTipoMensaje=='ACK') {
			// ACK usa fecha hora
			$this->nConsecutivo = $this->cFechaHora;
		} else {
			// Código consecutivo en RIACON
			$lcCodCon = substr($this->aModelo['CONSEC']['MSH'], strpos($this->aModelo['CONSEC']['MSH'], $this->cTipoMensaje) + 4);
			$lnPos = strpos($lcCodCon,'~');
			if(!($lnPos===false)){$lcCodCon = substr($lcCodCon, 0, $lnPos);}
			// Nombre de la secuencia en DB2-AS400
			$lcSeq = substr($this->aModelo['CONSEC']['SEQ'], strpos($this->aModelo['CONSEC']['SEQ'], $this->cTipoMensaje) + 4);
			$lnPos = strpos($lcSeq,'~');
			if(!($lnPos===false)){$lcSeq = substr($lcSeq, 0, $lnPos);}

			$this->nConsecutivo = $this->oDB->secuencia($lcSeq, ($tbActualizarRiacon? $lcCodCon: null));
		}
	}

	/*
	 * Guarda Log de Creación del mensaje
	 */
	public function fnLogCrearMensaje($tcUser='SRV_WEB', $tcPrograma='', $taDatos=[])
	{
		$lcCups=(is_array($taDatos['cCodCup'])==true ? '' : $taDatos['cCodCup'] ?? '');
		$lcDescripcion=substr($this->oMensaje->toString(), 0, 29990);
		
		$lcTablaLog = 'LGSPRV';
		$laDatosLog = [
			'PRVLGC' => $this->aModelo['Modelo'],
			'TIPLGC' => $this->cTipoMensaje,
			'CODLGC' => $this->nConsecutivo,
			'INGLGC' => $taDatos['nIngreso'] ?? 0,
			'CCILGC' => $taDatos['nConsecCita'] ?? 0,
			'CUPLGC' => $lcCups,
			'ESTLGC' => 'CREADO',
			'MSGLGC' => $lcDescripcion,
			'USULGC' => $tcUser,
			'PRGLGC' => $tcPrograma,
			'FECLGC' => $this->dFechaHora->format('Ymd'),
			'HORLGC' => $this->dFechaHora->format('His'),
		];
		$this->oDB->from($lcTablaLog)->insertar($laDatosLog);
	}

	/*
	 * Actualiza Log de Creación del mensaje
	 */
	public function fnLogActualizarMensaje($tcEstado, $tcRespuesta='', $tcUser='SRV_WEB', $tcPrograma='', $taDatos=[])
	{
		$ldFechaHoraLog = new \DateTime($this->oDB->FechaHoraSistema());
		$lcFecha = $ldFechaHoraLog->format('Ymd');
		$lcHora = $ldFechaHoraLog->format('His');

		$lcTablaLog = 'LGSPRV';
		$laDatosLog = [
			'ESTLGC' => $tcEstado,
			'UMOLGC' => $tcUser,
			'PMOLGC' => $tcPrograma,
			'FMOLGC' => $lcFecha,
			'HMOLGC' => $lcHora,
		];
		if (!empty($tcRespuesta)) {
			$laDatosLog['RTALGC'] = $tcRespuesta;
		}
		$laWhere = [
			'PRVLGC' => $this->aModelo['Modelo'],
			'TIPLGC' => $this->cTipoMensaje,
			'CODLGC' => $this->nConsecutivo,
			'INGLGC' => ($taDatos['nIngreso']??'0'),
		];
		$this->oDB->from($lcTablaLog)->where($laWhere)->actualizar($laDatosLog);
	}

	/*
	 * Elimina caracteres no deseados de un mensaje
	 */
	function fcLimpiarCaracteres($tcCadena, $tcListaCodigos, $tlRemplazaBlanco=false)
	{
		$lcCadena = $tcCadena;
		$laReemplazar = explode(",", $tcListaCodigos);
		$lcPor = $tlRemplazaBlanco? ' ': '';
		foreach ($laReemplazar as $lcReemplazar) {
			$lcCadena = str_replace($lcReemplazar, $lcPor, $lcCadena);
		}
		return $lcCadena;
	}
	
}
