<?php

namespace NUCLEO;

use Exception;

require_once __DIR__ . '/class.ConsultasEnfermeria.php';
require_once __DIR__ . '/class.AplicacionEnfermeria.php';
require_once __DIR__ . '/class.NotaEnfermeria.php';

class HL7_RecibeORU
{

	const aEventosPermitidos = array('R32', 'R01');
	const nlenghtIngreso = 8;
	const cModelo = 'DRAGER';
	const cpath = __DIR__ . "/class.HL7_" . self::cModelo . ".php";
	const cProg = 'INS_ORU';
	const cTipoMensajeRta = 'ACK';
	const cSeccionDefecto = 'CA';
	const cObservacionNota = 'MONITOREO AUTOMATICO';


	private $cCodEspRea = '';
	private $_componentSeparator = '^';
	private $nConsecCita = 0;
	private $oDB;
	private $aDatosORU;
	private $aDatosRta;
	public $cEstadoLog;
	private $cEvento = '';
	private $cFechaRta;
	private $cHoraRta;
	private $cChrEnter;
	private $cIniBloque;
	private $cFinBloque;
	private $oMensaje;
	private $cMensajeORU;
	private $cNumMensaje;
	private $aTests;
	private $cTipoMensaje;
	private $oSegMSH;
	private $oSegOBX;
	private $oEnfermeria;
	private $oAplicacion;
	private $oNotasEnfermeria;
	private $cUsuario;
	private $cPrograma;
	private $cSeccion;


	public $cMsgRta;
	public $bSalidaLog = true;

	/*
	 * Constructor de la clase
	 */
	function __construct()
	{
		global $goDb;
		$this->oDB = $goDb;

		$this->cChrEnter = chr(13);
		$this->cIniBloque = chr(11);
		$this->cFinBloque = chr(28) . chr(13);

		// archivo con el modelo de trabajo
		require_once self::cpath;
		$this->oEnfermeria = new ConsultasEnfermeria();
		$this->oAplicacion = new AplicacionEnfermeria();
		$this->oNotasEnfermeria = new NotaEnfermeria();
		$this->cUsuario = $this->oAplicacion->obtenerUsuario();
		$this->cPrograma = $this->oAplicacion->obtenerPrograma();
	}


	/*
	 * CREAR OBJETO MENSAJE ORU
	 */
	function fnCrearMensajeORU($tcMensajeORU)
	{
		$this->cMensajeORU = $tcMensajeORU;

		//Elimina caracteres inicio y fin de bloque
		$lcMsgORU = str_replace($this->cFinBloque, '', str_replace($this->cIniBloque, '', $this->cMensajeORU));
		//Crea mensaje
		$this->oMensaje = new \Net_HL7_Message();
		//Adiciona segmentos
		$laSegmentos = explode($this->cChrEnter, $lcMsgORU);
		foreach ($laSegmentos as $lcSegmento) {
			if (strlen($lcSegmento) > 3) {
				$lcTipoSeg = substr($lcSegmento, 0, 3);
				$laSeg = explode('|', substr($lcSegmento, $lcTipoSeg == 'MSH' ? 3 : 4));
				$this->oMensaje->addSegment(new \Net_HL7_Segment($lcTipoSeg, $laSeg));
			}
		}
		$this->oSegMSH = $this->oMensaje->getSegmentByIndex(0);
		$this->oSegOBX = $this->oMensaje->getSegmentsByName('OBX');
		$this->cNumMensaje = $this->oSegMSH->_fields[10];
		$this->cTipoMensaje = substr($this->oSegMSH->_fields[9], 0, 3);
	}


	/*
	 * VALIDACION MENSAJE ORU
	 */
	function fnValidaMensajeORU()
	{
		$loSegmento = $this->oSegMSH;
		$laDatos = [
			'cCodAcepta' => 'AA',
			'cIdMensajeOriginal' => $loSegmento->_fields[10],
			'cObservacion' => 'No error'
		];
		if ($loSegmento->getName() != 'MSH') {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Segmento inicial debe ser MSH';

			return $this->aDatosRta = $laDatos;
		}
		if ($this->cTipoMensaje != 'ORU') {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Tipo de Mensaje debe ser ORU';

			return $this->aDatosRta = $laDatos;
		}
		if (!in_array(substr($loSegmento->_fields[9], 4, 3), self::aEventosPermitidos)) {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Tipo de Evento debe estar en ( ' . implode(", ", self::aEventosPermitidos) . ' )';

			return $this->aDatosRta = $laDatos;
		}
		$loSegOBX = $this->oSegOBX;
		if (count($loSegOBX) < 3) {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error No hay segmentos OBX';

			return $this->aDatosRta = $laDatos;
		}
		//Datos generales del procedimiento
		$this->fnObtenerDatosORU();
		$lnIngreso = $this->aDatosORU['cIngreso'];
		if (empty($lnIngreso) || !ctype_digit(strval($lnIngreso)) || strlen(strval($lnIngreso)) > self::nlenghtIngreso) {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Falta Número de Ingreso o es incorrecto';
			$this->aDatosORU['cIngreso'] = intval($lnIngreso);

			return $this->aDatosRta = $laDatos;
		}
		$lnFechaObs = $this->aDatosORU['cFechaObs'];
		if (empty($lnFechaObs)) {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Falta la fecha de la observación';

			return $this->aDatosRta = $laDatos;
		}
		$lnHoraObs = $this->aDatosORU['cHoraObs'];
		if (empty($lnHoraObs)) {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Falta la hora de la observación';

			return $this->aDatosRta = $laDatos;
		}
		return $this->aDatosRta = $laDatos;
	}


	/*
	 * Obtiene datos del mensaje ORU
	 */
	function fnObtenerDatosORU()
	{
		$this->aDatosORU['cIngreso'] = '';
		$this->aDatosORU['cFechaObs'] = '';
		$this->aDatosORU['cHoraObs'] = '';

		//Datos generales del procedimiento

		$lnIndexFin = count($this->oMensaje->_segments) > 5 ? 5 : count($this->oMensaje->_segments);
		for ($lnIndex = 0; $lnIndex <= $lnIndexFin; $lnIndex++) {
			$loSegmento = $this->oMensaje->getSegmentByIndex($lnIndex);
			switch ($loSegmento->getName()) {
				case 'PID':
					$this->aDatosORU['cIngreso'] = $loSegmento->getField(18);
					break;
				case 'PV1':
					$lcDatosPV1 = explode("^^", $loSegmento->getField(3));
					$this->aDatosORU['cSeccion'] = $lcDatosPV1[0];
					$this->aDatosORU['cCama'] = $lcDatosPV1[1];
					break;
				case 'OBR':
					$this->aDatosORU['cFechaObs'] = substr($loSegmento->getField(7), 0, 8);
					$this->aDatosORU['cHoraObs'] = substr($loSegmento->getField(7), 8, 6);
					break;
			}
		}

		$this->aTests = $this->fnTestsIni();
		$loSegOBX = $this->oSegOBX;
		foreach ($loSegOBX as $laResultado) {
			$lcDatos = explode("^", $laResultado->getField(3));
			$this->aDatosORU[trim(strtoupper($lcDatos[0]))] = trim(str_replace("=", "", str_replace("^", "", $laResultado->getField(5))));
			$this->aTests[$laResultado->getField(3)] = $laResultado->getField(5);
		}
	}

	/*
	 * GUARDA RESULTADOS
	 */
	function fnGuardar()
	{
		if (!$this->registroAutomaticoSeccion($this->aDatosORU['cSeccion'])) {
			return $this->cEstadoLog = 'NOPROCES';
		}

		$llSinError = true;
		if (
			empty($this->aDatosORU['cIngreso']) || empty($this->aDatosORU['cFechaObs'])
			|| !is_numeric($this->aDatosORU['cIngreso']) || !is_numeric($this->aDatosORU['cFechaObs'])
		) {
			$llSinError = false;
			return $this->cEstadoLog = 'ERRPROCS';
		}

		$this->fnCrearRespuesta();
		$llIsertarRegistro = true;
		if ($llSinError && $this->aDatosRta['cCodAcepta'] == 'AA') {
			$loSignos = $this->oEnfermeria->SignosMonitor($this->aDatosORU['cIngreso'], $this->cUsuario, $this->aDatosORU['cFechaObs']);

			if (!empty($loSignos)) {
				$llIsertarRegistro = ($this->oAplicacion->validarRegistroNotas($loSignos, $this->aDatosORU['cFechaObs'], $this->aDatosORU['cHoraObs']));
			}
			if (!$llIsertarRegistro) {
				return $this->cEstadoLog = 'REGNOSIG';
			}

			$this->gestionTurno();
			$loDataEnsign = $this->prepareDataNota();
			if (!$this->oNotasEnfermeria->RegistrarSignos($this->aDatosORU['cIngreso'], $loDataEnsign)) {
				return $this->cEstadoLog = 'ERRINSER';
			}
			;
			return $this->cEstadoLog = 'PROCESADO';
		}
	}



	/****
	 * Date 2023/02/28
	 * *************************** VALIDACIONES ********************************
	 * @author Joan Oliveros
	 */


	public function validarRegistroAutomatico(): bool
	{
		$llRegistrarNotas = $this->oAplicacion->laNotasAutomaticas;
		return ($llRegistrarNotas == "0") ? false : true;
	}

	private function registroAutomaticoSeccion($tcSeccion): bool
	{
		if ($this->oAplicacion->laNotasAutomaticas == 0)
			return false;
		$laSeccionAutomatica = json_decode($this->oAplicacion->laNotasAutomaticasSeccion);
		$loSeccion = $laSeccionAutomatica->$tcSeccion;
		if (is_null($loSeccion))
			return false;

		$this->cSeccion = $loSeccion->name;
		return $loSeccion->active;
	}



	/****
	 * Date 2023/02/28
	 * *************************** MANEJO TURNOS ********************************
	 * @author Joan Oliveros
	 */
	private function gestionTurno()
	{

		$ltAhora = new \DateTime($this->oDB->fechaHoraSistema());
		$lcHoraActual = $ltAhora->format("His");
		$lcDatosTurno = $this->oAplicacion->DatosTurno($lcHoraActual);
		$lcValidarTurno = $this->oAplicacion->EstadoTurno($this->aDatosORU['cIngreso'], $lcDatosTurno);

		if ($lcValidarTurno == "AA") {
			$laDataTurno = $this->prepareDataTurno(false);
			$laCondicion = array(
				"INGNOT" => $this->aDatosORU["cIngreso"],
				"CONNOT" => $this->oNotasEnfermeria->CalcularConsNota($this->aDatosORU["cIngreso"])
			);
			$this->oAplicacion->CerrarTurno($laDataTurno, $laCondicion);
			$laDataTurno = $this->prepareDataTurno(true);
			$this->oAplicacion->AbrirTurno($laDataTurno);
		}

		if ($lcValidarTurno == "C") {
			$laDataTurno = $this->prepareDataTurno(true);
			$this->oAplicacion->AbrirTurno($laDataTurno);
		}
	}

	private function prepareDataTurno(bool $tlIsNuevoTurno): array
	{
		$ltAhora = new \DateTime($this->oDB->fechaHoraSistema());
		$lcFechaActual = $ltAhora->format("Ymd");
		$lcHoraActual = $ltAhora->format("His");

		if ($tlIsNuevoTurno) {
			return array(
				"INGNOT" => $this->aDatosORU["cIngreso"],
				"CONNOT" => $this->oNotasEnfermeria->CalcularConsNota($this->aDatosORU["cIngreso"]),
				"ESTNOT" => 2,
				"SCANOT" => $this->cSeccion,
				"NCANOT" => str_replace("C", "U", $this->aDatosORU["cCama"]),
				"USRNOT" => $this->cUsuario,
				"PGMNOT" => $this->cPrograma,
				"FECNOT" => $lcFechaActual,
				"HORNOT" => $lcHoraActual,
			);
		}
		return array(
			"ESTNOT" => "1",
			"NTANOT" => "S",
			"ADMNOT" => "1",
			"UMONOT" => $this->cUsuario,
			"PMONOT" => $this->cPrograma,
			"FMONOT" => $lcFechaActual,
			"HMONOT" => $lcHoraActual,
		);
	}

	private function prepareDataNota(): object
	{

		$lcHour = $this->oAplicacion->roundTime($this->aDatosORU['cHoraObs']);
		$lcEstado = $this->oAplicacion->obtenerEstado();
		$lcEstado = !is_null($lcEstado) ? $lcEstado : "1";

		return (object) [
			"tcObservacion" => self::cObservacionNota,
			"tnFr" => (!empty($this->aDatosORU['RESP'])) ? $this->aDatosORU['RESP'] : '0',
			"tnSO2" => (!empty($this->aDatosORU['SPO2'])) ? $this->aDatosORU['SPO2'] : '0',
			"tnT" => (!empty($this->aDatosORU['TA'])) ? $this->aDatosORU['TA'] : '0',
			"tnTAS" => (!empty($this->aDatosORU['ART S'])) ? $this->aDatosORU['ART S'] : '0',
			"tnTAD" => (!empty($this->aDatosORU['ART D'])) ? $this->aDatosORU['ART D'] : '0',
			"tnTAM" => (!empty($this->aDatosORU['ART M'])) ? $this->aDatosORU['ART M'] : '0',
			"tnFC" => (!empty($this->aDatosORU['HR'])) ? $this->aDatosORU['HR'] : '0',
			"tcFecha" => $this->aDatosORU['cFechaObs'],
			"tcHora" => $lcHour,
			"tcUser" => $this->cUsuario,
			"tcPrograma" => $this->cPrograma,
			"tcEstado" => $lcEstado
		];

	}
	/****
	 * Date 2023/02/28
	 * *************************** FUNCIONES ********************************
	 * @author Joan Oliveros
	 */

	/*
	 * Crea mensaje de respuesta
	 */
	function fnCrearRespuesta()
	{
		$lcClassName = "NUCLEO\\HL7_" . self::cModelo;
		$loMsgRta = new $lcClassName(self::cModelo);
		$loMsgRta->fnCrearMensaje(self::cTipoMensajeRta, $this->cEvento, $this->aDatosRta);
		$this->cMsgRta = $loMsgRta->oMensaje->toString();
		$lcMsgReturn = $loMsgRta->cIniBloque . $this->cMsgRta . $loMsgRta->cFinBloque;
		//Obtiene parámetros adicionales
		$this->cFechaRta = substr($loMsgRta->cFechaHora, 0, 8);
		$this->cHoraRta = substr($loMsgRta->cFechaHora, 8, 6);
		$this->_componentSeparator = $loMsgRta->oMensaje->_componentSeparator;

		unset($loMsgRta);
		// Guarda log de recibido del ORU
		$this->fnLogRecibeORU();
		return $lcMsgReturn;
	}


	/*
	 * GUARDA LOG MENSAJE RECIBIDO
	 */
	function fnLogRecibeORU()
	{
		$this->cEstadoLog = 'RECIBIDO';

		try {

			$laDatosLog = [
				'PRVLGC' => self::cModelo,
				'TIPLGC' => $this->cTipoMensaje,
				'CODLGC' => $this->cNumMensaje,
				'CCILGC' => $this->nConsecCita,
				'CUPLGC' => "",
				'INGLGC' => intval($this->aDatosORU['cIngreso']) ?? 0,
				'CORLGC' => intval($this->aDatosORU['cNumOrden']) ?? 0,
				'ESTLGC' => $this->cEstadoLog,
				'MSGLGC' => $this->cMensajeORU,
				'RTALGC' => $this->cMsgRta,
				'TRTLGC' => $this->aDatosRta['cCodAcepta'],
				'USULGC' => $this->cUsuario,
				'PRGLGC' => $this->cPrograma,
				'FECLGC' => $this->cFechaRta,
				'HORLGC' => $this->cHoraRta,
			];

			$this->oDB->from('LGSPRV')->insertar($laDatosLog);
		} catch (Exception $lcError) {
			echo $lcError->getMessage();
		}
	}

	/*
	 * GUARDA LOG DE PROCESADO DEL MENSAJE
	 */
	function fnLogProcesadoORU()
	{
		// Fecha y hora de procesado
		$ldFechaHoraLog = new \DateTime($this->oDB->FechaHoraSistema());
		$lcFecha = $ldFechaHoraLog->format('Ymd');
		$lcHora = $ldFechaHoraLog->format('His');

		$laDatosLog = [
			'ESTLGC' => $this->cEstadoLog,
			'CCILGC' => $this->nConsecCita,
			'FPRLGC' => $lcFecha,
			'HPRLGC' => $lcHora,
			'UMOLGC' => $this->cUsuario,
			'PMOLGC' => $this->cPrograma,
			'FMOLGC' => $lcFecha,
			'HMOLGC' => $lcHora,
		];
		$laWhere = [
			'PRVLGC' => self::cModelo,
			'TIPLGC' => $this->cTipoMensaje,
			'CODLGC' => $this->cNumMensaje,
			'INGLGC' => $this->aDatosORU['cIngreso'] ?? 0,
		];
		$this->oDB->from('LGSPRV')->where($laWhere)->actualizar($laDatosLog);
	}


	/*
	 * Retorna datos en blanco para Test
	 */
	function fnTestsIni()
	{
		return [
			'HR' => '0.00',
			'PACED' => '0.00',
			'ARR' => '0.00',
			'PVC/min' => '0.00',
			'STI' => '0.00',
			'STII' => '0.00',
			'STIII' => '0.00',
			'STaVR' => '0.00',
			'STaVF' => '0.00',
			'STaVL' => '0.00',
			'STV' => '0.00',
			'RESP' => '0.00',
			'ART D' => '0.00',
			'ART S' => '0.00',
			'ART M' => '0.00',
			'PLS' => '0.00',
			'SpO2' => '0.00',
			'PI' => '0.00',
			'Ta' => '0.00',
			'Tb' => '0.00',
			'dT' => '0.00',
			'NBP D' => '0.00',
			'NBP S' => '0.00',
			'NBP M' => '0.00',
			'RRc' => '0.00',
			'etCO2' => '0.00',
			'iCO2' => '0.00',
			'SpHb' => '0.00',
			'SpHbv' => '0.00',
			'SpCO' => '0.00',
			'SpOC' => '0.00',
			'SpMet' => '0.00',
			'PVI' => '0.00',
		];
	}
}