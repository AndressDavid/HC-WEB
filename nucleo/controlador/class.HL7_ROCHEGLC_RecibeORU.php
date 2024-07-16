<?php
namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';


class HL7_RecibeORU
{
	private $oDB;
	public $cMensajeORU;
	public $oMensaje;
	public $cModelo = 'ROCHEGLC';
	public $aDatosORU;
	public $aDatosRta;
	public $cNumMensaje;
	public $cTipoMensaje;
	public $cTipoMensajeRta = 'ACK';
	public $cEvento = '';
	public $aEventosPermitidos = ['R32','R01'];
	public $aTests;
	public $oMsgRta;
	public $cMsgRta = '';
	public $dFechaHoraRta;
	public $cFechaRta;
	public $cHoraRta;
	public $cFechaRealiza;
	public $cHoraRealiza;
	private $cRMRealiza = '';
	private $cCodEspRea = '';
	private $_componentSeparator = '^';
	private $aCup = [];
	private $cUser = 'SRV_WEB';
	private $cProg = 'INS_ORU';
	private $nEstadoProc = 3;
	private $nConsecCita = 0;
	private $nConsecNota = 0;
	private $cEstadoLog;
	private $nCobrado = 0;
	private $nHorasAntes = 0;
	private $cTextoPlantilla = '';

	// Estado para vía 02 y diferentes a 02
	private $nEstadoProcCE = 3;		// Reporte Final
	private $nEstadoProcNoCE = 3;	// Reporte Final

	/*
	 * Constructor de la clase
	 */
	function __construct()
	{
		global $goDb;
		$this->oDB = $goDb;

		// archivo con el modelo de trabajo
		require_once __DIR__ . "/class.HL7_{$this->cModelo}.php";

		$lcClassName = "NUCLEO\\HL7_{$this->cModelo}";
		$this->oMsgRta = new $lcClassName($this->cModelo);
		$this->cRMRealiza = $this->oMsgRta->aModelo['RTA_DAT']['RMRLZ'];
		$this->cCodEspRea = $this->oMsgRta->aModelo['RTA_DAT']['ESPRLZ'];
		$this->aCup = $this->oMsgRta->aModelo['CUPS']['Glu2'];
		$this->nHorasAntes = intval($this->oDB->obtenerTabMae1('OP2TMA','GLUCOM',"CL1TMA='LIMITE' AND ESTTMA=''",null,'36'));
	}


	/*
	 * CREAR OBJETO MENSAJE ORU
	 */
	function fnCrearMensajeORU($tcMensajeORU)
	{
		$this->cMensajeORU = $tcMensajeORU;

		//Elimina caracteres inicio y fin de bloque
		$lcMsgORU = str_replace(chr(28), '', str_replace(chr(28) . chr(13), '', str_replace(chr(11), '', $this->cMensajeORU)));

		//Crea mensaje
		$this->oMensaje = new \Net_HL7_Message();
		$this->_componentSeparator = $this->oMensaje->_componentSeparator;

		//Adiciona segmentos
		$laSegmentos = explode(chr(13), $lcMsgORU);
		foreach ($laSegmentos as $lcSegmento) {
			if (strlen($lcSegmento)>3) {
				$lcTipoSeg = substr($lcSegmento, 0, 3);
				$laSeg = explode('|', substr($lcSegmento, $lcTipoSeg=='MSH'? 3: 4));
				$this->oMensaje->addSegment(new \Net_HL7_Segment($lcTipoSeg, $laSeg));
			}
		}
		$loSegmento = $this->oMensaje->getSegmentByIndex(0);
		$this->cNumMensaje = $loSegmento->_fields[10];
		$this->cTipoMensaje = substr($loSegmento->_fields[9],0,3);
	}


	/*
	 * VALIDACION MENSAJE ORU
	 */
	function fnValidaMensajeORU()
	{
		//$loSegmento = $this->oMensaje->getSegmentByIndex(0);
		$lnIndexFin = count($this->oMensaje->_segments);
		for ($lnIndex = 0; $lnIndex <= $lnIndexFin; $lnIndex++) {
			$loSegmento = $this->oMensaje->getSegmentByIndex($lnIndex);
			if (!is_null($loSegmento)) {
				if ($loSegmento->getName()=='MSH') {
					break;
				}
			}
		}
		$laDatos = [
			'cCodAcepta' => 'AA',
			'cIdMensajeOriginal' => $loSegmento->_fields[10],
			'cObservacion' => 'Mensaje OK'
		];
		if ($loSegmento->getName()!='MSH') {
			$laDatos['cCodAcepta'] = 'AE';
			$laDatos['cObservacion'] = 'Error Segmento inicial debe ser MSH';
		} else {
			if ($this->cTipoMensaje != 'ORU') {
				$laDatos['cCodAcepta'] = 'AE';
				$laDatos['cObservacion'] = 'Error Tipo de Mensaje debe ser ORU';
			} else {
				if (!in_array(substr($loSegmento->_fields[9],4,3), $this->aEventosPermitidos)) {
					$laDatos['cCodAcepta'] = 'AE';
					$laDatos['cObservacion'] = 'Error Tipo de Evento debe estar en ( ' . implode(", ",$this->aEventosPermitidos) . ' )';
				} else {
					//Segmento con el valor de la glucometría
					$loResultados = $this->oMensaje->getSegmentsByName('OBX');
					if (count($loResultados)<1) {
						$laDatos['cCodAcepta'] = 'AE';
						$laDatos['cObservacion'] = 'Error No hay segmentos OBX';
					} else {
						//Datos generales del procedimiento
						$this->fnObtenerDatosORU(true);
						if (empty($this->aDatosORU['cIngreso']) || !is_numeric($this->aDatosORU['cIngreso'])) {
							$laDatos['cCodAcepta'] = 'AE';
							$laDatos['cObservacion'] = 'Error Falta Numero de Ingreso o es incorrecto';
						}
					}
				}
			}
		}
		//Retorna datos de respuesta
		$this->aDatosRta = $laDatos;
	}


	/*
	 * Crea mensaje de respuesta
	 */
	function fnCrearRespuesta()
	{
		$this->oMsgRta->fnCrearMensaje($this->cTipoMensajeRta, $this->cEvento, $this->aDatosRta);
		$this->cMsgRta = $this->oMsgRta->oMensaje->toString();
		$lcMsgReturn = $this->oMsgRta->cIniBloque . $this->cMsgRta . $this->oMsgRta->cFinBloque;

		//Obtiene parámetros adicionales
		$this->dFechaHoraRta = \DateTime::createFromFormat('YmdHis', $this->oMsgRta->cFechaHora);
		$this->cFechaRta = substr($this->oMsgRta->cFechaHora, 0, 8);
		$this->cHoraRta = substr($this->oMsgRta->cFechaHora, 8, 6);

		return $lcMsgReturn;
	}


	/*
	 * Obtiene datos del mensaje ORU
	 */
	function fnObtenerDatosORU($tlObtenerFechaHora=false)
	{
		if ($tlObtenerFechaHora) {
			$this->dFechaHoraRta = new \DateTime( $this->oDB->FechaHoraSistema() );
			$this->cFechaRta = $this->cFechaRealiza = $this->dFechaHoraRta->format('Ymd');
			$this->cHoraRta = $this->cHoraRealiza = $this->dFechaHoraRta->format('His');
			$this->cEstadoLog = 'RECIBIDO';
			$this->aDatosRta['cCodAcepta'] = 'AA';
		}

		//Limpiar datos
		$this->aDatosORU['cIngreso'] = '';
		$this->aDatosORU['cNumOrden'] = '';
		$this->aDatosORU['cCodigoProc'] = '';
		$this->aDatosORU['cProcedimiento'] = '';
		$this->aDatosORU['cComentario'] = '';
		$this->aTests = ['Glu2'=>'', 'Unid'=>'', 'RegM'=>'', 'User'=>'', 'Time'=>'', ];

		//Datos generales del procedimiento
		$lnIndexFin = count($this->oMensaje->_segments);
		for ($lnIndex = 0; $lnIndex <= $lnIndexFin; $lnIndex++) {
			$loSegmento = $this->oMensaje->getSegmentByIndex($lnIndex);
			if (!is_null($loSegmento)) {
				switch ($loSegmento->getName()) {
					case 'PID':
						$this->aDatosORU['cIngreso'] = $loSegmento->getField(3);
						break;
					case 'PV1':
						// $lcNumOrden = $loSegmento->getField(4);
						// $this->aDatosORU['cNumOrden'] = is_null($lcNumOrden) ? '' : $lcNumOrden;
						// $this->aDatosORU['cCodigoProc'] = $loSegmento->getField(5) ?? '903883';
						break;
					case 'OBR':
						//$this->aDatosORU['cProcedimiento'] = $loSegmento->getField(4);
						$lcTime = $loSegmento->getField(22);
						if (strlen($lcTime)>0)
							$this->aTests['Time'] = $lcTime;
						break;
					case 'OBX':
						if ($loSegmento->getField(3)=='Glu2') {
							$this->aTests['Glu2']=strtoupper($loSegmento->getField(5));
							$this->aTests['Unid']=$loSegmento->getField(6);
							$this->aTests['RegM']=$loSegmento->getField(9);
							$lcTime = $loSegmento->getField(14);
							if (strlen($lcTime)>0)
								$this->aTests['Time'] = $lcTime;
						}
						break;
					case 'NTE':
						$this->aDatosORU['cComentario'] = trim($loSegmento->getField(5));
						break;
				}
			}
		}

		if (!empty($this->aTests['RegM'])) {
			$this->cRMRealiza = str_pad($this->aTests['RegM'],13,'0',STR_PAD_LEFT);
			$this->aTests['User'] = $this->fnObtenerUsuario($this->cRMRealiza);
		}
	}


	/*
	 * GUARDA LOG MENSAJE RECIBIDO
	 */
	function fnLogRecibeORU()
	{
		$this->cEstadoLog = 'RECIBIDO';

		$laDatosLog = [
			'PRVLGC' => $this->cModelo,
			'TIPLGC' => $this->cTipoMensaje,
			'CODLGC' => $this->cNumMensaje,
			'CCILGC' => $this->nConsecCita,
			'CUPLGC' => $this->aCup,
			'INGLGC' => $this->aDatosORU['cIngreso']??0,
			'CORLGC' => $this->aDatosORU['cNumOrden']??'',
			'ESTLGC' => $this->cEstadoLog,
			'MSGLGC' => $this->cMensajeORU,
			'RTALGC' => $this->cMsgRta,
			'TRTLGC' => $this->aDatosRta['cCodAcepta'],
			'USULGC' => $this->cUser,
			'PRGLGC' => $this->cProg,
			'FECLGC' => $this->cFechaRta,
			'HORLGC' => $this->cHoraRta,
		];
		$this->oDB->from('LGSPRV')->insertar($laDatosLog);
	}


	/*
	 * GUARDA RESULTADOS
	 */
	function fnGuardar()
	{
		$llSinError = true;
		if (empty($this->aDatosORU['cIngreso']) || !is_numeric($this->aDatosORU['cIngreso'])) {
			$llSinError = false;
			$this->cEstadoLog = 'ERRPROCS';
		}
		// Obtiene fecha de respuesta temporal
		$lcFechaHora = $this->oDB->fechaHoraSistema();
		$this->dFechaHoraRta = new \DateTime($lcFechaHora);
		$this->cFechaRta = $this->dFechaHoraRta->format('Ymd');
		$this->cHoraRta = $this->dFechaHoraRta->format('His');

		// Guarda log de recibido del ORU
		$this->fnLogRecibeORU();

		if ($llSinError && $this->aDatosRta['cCodAcepta'] == 'AA') {

			// Buscar primera glucometría sin responder del día
			$laDatos = $this->oDB
				->select('CCIORD,RMEORD,VIAORD')
				->from('RIAORD')
				->where([
					'NINORD'=>$this->aDatosORU['cIngreso'],
					'COAORD'=>$this->aCup,
					'ESTORD'=>8,
					'FECORD'=>$this->cFechaRta,
				])
				->orderBy('CCIORD')
				->get('array');
			if (!is_array($laDatos)) $laDatos = [];

			if (count($laDatos)==0 && $this->nHorasAntes>0) {
				// Horas antes de la fecha hora de respuesta
				$lnFecha=(clone $this->dFechaHoraRta)->sub(new \DateInterval("PT{$this->nHorasAntes}H"))->format('YmdHis');
				// Buscar primera glucometría sin responder
				$laDatos = $this->oDB
					->select('CCIORD,RMEORD,VIAORD')
					->from('RIAORD')
					->where([
						'NINORD'=>$this->aDatosORU['cIngreso'],
						'COAORD'=>$this->aCup,
						'ESTORD'=>8,
					])
					->where("FECORD*1000000+HORORD > $lnFecha")
					->orderBy('CCIORD')
					->get('array');
				if (!is_array($laDatos)) $laDatos = [];
			}

			// Guardar resultado
			if (count($laDatos)>0) {
				$this->nConsecCita = $laDatos['CCIORD'];
				$this->aDatosORU['cRMOrdena'] = $laDatos['RMEORD'];
				$this->aDatosORU['cCodVia'] = $laDatos['VIAORD'];
				$this->fnConsultaDatosIngreso();

				if($this->aDatosORU['cCodVia']=='02'){
					$this->nEstadoProc = $this->nEstadoProcCE;
					$this->nConsecNota = 1;
					$this->fnGuardarGlucometria();
					$this->fnActualizaRiaOrd();
					$this->fnGuardaRiaDet();

				} else {
					$this->nEstadoProc = $this->nEstadoProcNoCE;
					$this->fnConsecutivoNota();
					$this->fnGuardarGlucometria();
					$this->fnActualizaRiaOrd();
					$this->fnGuardaRiaDet();
					$this->fnGuardaCobro();
				}
				$this->cEstadoLog = 'PROCESADO';

			} else {
				// No hay glucometrías ordenadas sin responder
				$this->aDatosRta['cCodAcepta'] = 'AE';
				//$this->aDatosRta['cObservacion'] = "Error No hay glucometrias ordenadas sin responder en las ultimas {$this->nHorasAntes} hr";
				$this->aDatosRta['cObservacion'] = "Error No hay glucometrias ordenadas sin responder";
				$this->cEstadoLog = 'NOPROCES';
			}
		}

		// CREAR RESPUESTA
		$this->fnCrearRespuesta();
	}


	/*
	 * Datos ingreso y habitación actual
	 */
	function fnConsultaDatosIngreso()
	{
		$laDatos = $this->oDB
			->select('I.TIDING, I.NIDING, I.VIAING, I.PLAING, F.SECHAB, F.NUMHAB')
			->from('RIAINGL15 I')
			->innerJoin('FACHABL3 F', 'I.NIGING = F.INGHAB')
			->where([
				'NIGING'=>$this->aDatosORU['cIngreso'],
			])
			->get('array');
		if (!is_array($laDatos)) $laDatos = [];

		$this->aDatosORU['cTipoDoc'] = $laDatos['TIDING']??'';
		$this->aDatosORU['cNumDoc'] = $laDatos['NIDING']??0;
		$this->aDatosORU['cCodVia'] = $laDatos['VIAING']??'';
		$this->aDatosORU['cCodPlan'] = trim($laDatos['PLAING']??'');
		$this->aDatosORU['cCama'] = trim($laDatos['SECHAB']??'') . trim($laDatos['NUMHAB']??'');
	}


	/*
	 * Guardar información de la glucometría ENGLUCO y RIAHIS
	 */
	function fnGuardarGlucometria()
	{
		if (substr($this->aTests['Glu2'],0,5)=='MAYOR') {
			$lcSigno = '>';
			$lnValor = trim(substr($this->aTests['Glu2'],7));
		} else {
			$lcSigno = '=';
			$lnValor = $this->aTests['Glu2'];
		}
		$this->cFechaRealiza = substr($this->aTests['Time'], 0, 8);
		$this->cHoraRealiza =  substr($this->aTests['Time'], 8, 6);

		$laDatos = [
			'INGGLU' => $this->aDatosORU['cIngreso'],
			'CONGLU' => $this->nConsecNota,
			'CNTGLU' => $this->nConsecCita,
			'OBSGLU' => $this->aDatosORU['cComentario'],
			'MEDGLU' => $lnValor,
			'UMEGLU' => $this->aTests['Unid'],
			'MAYGLU' => $lcSigno,
			'FDIGLU' => $this->cFechaRealiza,
			'HDIGLU' => $this->cHoraRealiza,
			'USRGLU' => $this->aTests['User'],
			'PGMGLU' => $this->cProg,
			'FECGLU' => $this->cFechaRta,
			'HORGLU' => $this->cHoraRta,
		];
		$this->oDB->from('ENGLUCO')->insertar($laDatos);

		$lcFechaToma = AplicacionFunciones::formatFechaHora('fecha', $this->cFechaRealiza, '/');
		$lcHoraToma  = AplicacionFunciones::formatFechaHora('hora12', $this->cHoraRealiza);
		$laDatos = [
			'NROING' => $this->aDatosORU['cIngreso'],
			'CONCON' => $this->nConsecCita,
			'INDICE' => '70',
			'SUBORG' => $this->aCup,
			'TIDHIS' => $this->aDatosORU['cTipoDoc'],
			'NIDHIS' => $this->aDatosORU['cNumDoc'],
			'USRHIS' => $this->aTests['User'],
			'PGMHIS' => $this->cProg,
			'FECHIS' => $this->cFechaRta,
			'HORHIS' => $this->cHoraRta,
		];
		$lcSL = chr(10);
		$this->oDB->from('RIAHIS')->insertar($laDatos+[
			'CONSEC' => '1',
			//'DESCRI' => "Valor Glucometría: {$lnValor} {$this->aTests['Unid']}",
			'DESCRI' => "Valor Glucometría: {$lnValor}",
		]);
		$this->oDB->from('RIAHIS')->insertar($laDatos+[
			'CONSEC' => '2',
			// 'DESCRI' => "{$lcSL}Fecha Tomado: {$lcFechaToma}{$lcSL}Hora Tomado: {$lcHoraToma}",
			'DESCRI' => "{$lcSL}Fecha Tomado: {$lcFechaToma}",
		]);
		$this->oDB->from('RIAHIS')->insertar($laDatos+[
			'CONSEC' => '3',
			'DESCRI' => "{$lcSL}Hora Tomado: {$lcHoraToma}",
		]);
		$this->oDB->from('RIAHIS')->insertar($laDatos+[
			'CONSEC' => '101',
			'DESCRI' => $this->cRMRealiza,
		]);
	}


	/*
	 * Actualiza RIAORD
	 */
	function fnActualizaRiaOrd()
	{
		$laDatosRiaord = [
			'FERORD'=>$this->cFechaRealiza,
			'HRLORD'=>$this->cHoraRealiza,
			'ESTORD'=>$this->nEstadoProc,
			'RATORD'=>0,
			'UMOORD'=>$this->cUser,
			'PMOORD'=>$this->cProg,
			'FMOORD'=>$this->cFechaRta,
			'HMOORD'=>$this->cHoraRta,
		];
		$laWhere = [
			'NINORD'=>$this->aDatosORU['cIngreso'],
			'CCIORD'=>$this->nConsecCita,
			'COAORD'=>$this->aCup,
		];
		$this->oDB->from('RIAORD')->where($laWhere)->actualizar($laDatosRiaord);
	}


	/*
	 * VERIFICA Y GUARDA COBRO
	 */
	function fnGuardaCobro()
	{
		//Verificar cobrado
		$laDatos = $this->oDB
			->select('COUNT(*) AS CUENTA')
			->from('RIAESTM20')
			->where([
				'INGEST'=>$this->aDatosORU['cIngreso'],
				'CNOEST'=>$this->nConsecCita,
				'CUPEST'=>$this->aCup,
				'TINEST'=>'400',
			])
			->getAll('array');
		if (is_array($laDatos)) {
			//Si no existe cobro lo inserta
			if (($laDatos[0]['CUENTA']??0)==0) {
				//	PARAMETROS
				$taParam = [
					'ingreso'		=> $this->aDatosORU['cIngreso'],
					'codVia'		=> $this->aDatosORU['cCodVia'],
					'codPlan'		=> $this->aDatosORU['cCodPlan'],
					'regMedOrdena'	=> $this->aDatosORU['cRMOrdena'],
					'regMedRealiza'	=> $this->cRMRealiza,
					'numIdPac'		=> $this->aDatosORU['cNumDoc'],
					'codCup'		=> $this->aCup,
					'secCama'		=> $this->aDatosORU['cCama'],
					'espMedRealiza'	=> $this->cCodEspRea,
					'cnsCita'		=> $this->nConsecCita,
					'portatil'		=> '',
				];

				require_once __DIR__ .'/class.Cobros.php';
				$lbCobrado = (new Cobros)->cobrarProcedimiento($taParam);
				$this->nCobrado = $lbCobrado? 1: 0;
			}
		}
	}


	/*
	 * Guarda detalle en RIADET
	 */
	function fnGuardaRiaDet()
	{
		$laDatosRiadet = [
			'TIDDET' => $this->aDatosORU['cTipoDoc'],
			'NIDDET' => $this->aDatosORU['cNumDoc'],
			'INGDET' => $this->aDatosORU['cIngreso'],
			'CCIDET' => $this->nConsecCita,
			'CUPDET' => $this->aCup,
			'FERDET' => $this->cFechaRealiza,
			'HRRDET' => $this->cHoraRealiza,
			'ESTDET' => $this->nEstadoProc,
			'MARDET' => $this->nCobrado,
			'FL2DET' => $this->aDatosORU['cNumOrden'],
			'USRDET' => $this->cUser,
			'PGMDET' => $this->cProg,
			'FECDET' => $this->cFechaRta,
			'HORDET' => $this->cHoraRta,
		];
		$this->oDB->from('RIADET')->insertar($laDatosRiadet);
	}


	/*
	 * Obtiene consecutivo Nota de enfermería actual
	 */
	function fnConsecutivoNota()
	{
		$laDatos = $this->oDB
			->select('CONNOT')
			->from('NCSNOT')
			->where(['INGNOT'=>$this->aDatosORU['cIngreso'],])
			->orderBy('CONNOT DESC')
			->get('array');
		if (!is_array($laDatos)) $laDatos = [];

		$this->nConsecNota = $laDatos['CONNOT']??1;
	}


	/*
	 * Obtiene el usuario a partir del registro
	 */
	function fnObtenerUsuario($tcRegMed)
	{
		$laDatos = $this->oDB
			->select('USUARI')
			->from('RIARGMN')
			->where(['REGMED'=>$tcRegMed,])
			->get('array');
		if (!is_array($laDatos)) $laDatos = [];

		return $laDatos['USUARI']??$this->cUser;
	}


	/*
	 * GUARDA LOG DE PROCESADO DEL MENSAJE
	 */
	function fnLogProcesadoORU()
	{
		// Fecha y hora de procesado
		$ldFechaHoraLog = new \DateTime( $this->oDB->FechaHoraSistema() );
		$lcFecha = $ldFechaHoraLog->format('Ymd');
		$lcHora = $ldFechaHoraLog->format('His');

		$laDatosLog = [
			'ESTLGC' => $this->cEstadoLog,
			'CCILGC' => $this->nConsecCita,
			'RTALGC' => $this->cMsgRta,
			'TRTLGC' => $this->aDatosRta['cCodAcepta'],
			'FPRLGC' => $lcFecha,
			'HPRLGC' => $lcHora,
			'UMOLGC' => $this->cUser,
			'PMOLGC' => $this->cProg,
			'FMOLGC' => $lcFecha,
			'HMOLGC' => $lcHora,
		];
		$laWhere = [
			'PRVLGC'=>$this->cModelo,
			'TIPLGC'=>$this->cTipoMensaje,
			'CODLGC'=>$this->cNumMensaje,
			'INGLGC'=>$this->aDatosORU['cIngreso'],
		];
		$this->oDB->from('LGSPRV')->where($laWhere)->actualizar($laDatosLog);
	}

}
