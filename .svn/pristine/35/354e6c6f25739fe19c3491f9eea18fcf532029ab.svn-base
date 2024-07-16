<?php
namespace NUCLEO;

class HL7_RecibeORU
{
	var $oDB;
	var $cMensajeORU;
	var $oMensaje;
	var $cModelo = 'RAPID';
	var $aDatosORU;
	var $aDatosRta;
	var $cNumMensaje;
	var $cTipoMensaje;
	var $cTipoMensajeRta = 'ACK';
	var $cEvento = '';
	var $aEventosPermitidos = ['R32','R01'];
	var $aTests;
	var $cMsgRta;
	var $cFechaRta;
	var $cHoraRta;
	var $cRMRealiza = '';
	var $cCodEspRea = '';
	var $_componentSeparator = '^';
	var $aCup = [];
	var $cUser = 'SRV_WEB';
	var $cProg = 'INS_ORU';
	var $nEstadoProc = 52;
	var $nConsecCita = 0;
	var $cEstadoLog;
	var $nCobrado = 0;


	/*
	 * Constructor de la clase
	 */
	function __construct()
	{
		global $goDb;
		$this->oDB = $goDb;

		// archivo con el modelo de trabajo
		require_once __DIR__ . "/class.HL7_{$this->cModelo}.php";
	}


	/*
	 * CREAR OBJETO MENSAJE ORU
	 */
	function fnCrearMensajeORU($tcMensajeORU)
	{
		$this->cMensajeORU = $tcMensajeORU;

		//Elimina caracteres inicio y fin de bloque
		$lcMsgORU = str_replace(chr(28) . chr(13), '', str_replace(chr(11), '', $this->cMensajeORU));

		//Crea mensaje
		$this->oMensaje = new \Net_HL7_Message();

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
		$loSegmento = $this->oMensaje->getSegmentByIndex(0);
		$laDatos = [
			'cCodAcepta' => 'AA',
			'cIdMensajeOriginal' => $loSegmento->_fields[10],
			//'cObservacion' => 'Mensaje OK'
			'cObservacion' => 'No error'
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
					//Segmentos con los valores de los test
					$loResultados = $this->oMensaje->getSegmentsByName('OBX');
					if (count($loResultados)<3) {
						$laDatos['cCodAcepta'] = 'AE';
						$laDatos['cObservacion'] = 'Error No hay segmentos OBX';
					} else {
						//Datos generales del procedimiento
						$this->fnObtenerDatosORU(false);
						$lnIngreso = $this->aDatosORU['cIngreso'];
						if (empty($lnIngreso) || !ctype_digit(strval($lnIngreso)) || strlen(strval($lnIngreso))>8) {
							$laDatos['cCodAcepta'] = 'AE';
							$laDatos['cObservacion'] = 'Error Falta Número de Ingreso o es incorrecto';
							$this->aDatosORU['cIngreso'] = intval($lnIngreso);
						} else {
							$lnNumOrden = $this->aDatosORU['cNumOrden'];
							if (empty($lnNumOrden) || !ctype_digit(strval($lnNumOrden)) || strlen(strval($lnNumOrden))>12 || strpos($lnNumOrden,'.')!==false) {
								$laDatos['cCodAcepta'] = 'AE';
								$laDatos['cObservacion'] = 'Error Falta Número de Orden o es incorrecto';
								$this->aDatosORU['cNumOrden'] = intval($lnNumOrden);
							} else {
								$this->aDatosORU['cNumOrden'] = trim($this->aDatosORU['cNumOrden']);
								if (strlen($this->aDatosORU['cNumOrden'])!==8) {
									$laDatos['cCodAcepta'] = 'AE';
									$laDatos['cObservacion'] = 'Error Número de Orden incorrecto';
								} else {
									if (empty($this->aDatosORU['cCodigoProc'])) {
										$laDatos['cCodAcepta'] = 'AE';
										$laDatos['cObservacion'] = 'Error No se recibió código de procedimiento';
									}
								}
							}
						}
					}
				}
			}
		}
		$this->aDatosRta = $laDatos;
	}


	/*
	 * Crea mensaje de respuesta
	 */
	function fnCrearRespuesta()
	{
		$lcClassName = "NUCLEO\\HL7_{$this->cModelo}";
		$loMsgRta = new $lcClassName($this->cModelo);
		$loMsgRta->fnCrearMensaje($this->cTipoMensajeRta, $this->cEvento, $this->aDatosRta);
		$this->cMsgRta = $loMsgRta->oMensaje->toString();
		$lcMsgReturn = $loMsgRta->cIniBloque . $this->cMsgRta . $loMsgRta->cFinBloque;

		//Obtiene parámetros adicionales
		$this->cFechaRta = substr($loMsgRta->cFechaHora, 0, 8);
		$this->cHoraRta = substr($loMsgRta->cFechaHora, 8, 6);
		$this->cRMRealiza = $loMsgRta->aModelo['GASES']['RMRLZ'];
		$this->cCodEspRea = $loMsgRta->aModelo['GASES']['ESPRLZ'];
		$this->_componentSeparator = $loMsgRta->oMensaje->_componentSeparator;
		$this->aCup = $loMsgRta->aModelo['CUPS'];
		unset($loMsgRta);

		// Guarda log de recibido del ORU
		$this->fnLogRecibeORU();

		return $lcMsgReturn;
	}


	/*
	 * Obtiene datos del mensaje ORU
	 */
	function fnObtenerDatosORU($tlObtenerFechaHora=false)
	{
		if ($tlObtenerFechaHora) {
			$dFechaHora = new \DateTime( $this->oDB->FechaHoraSistema() );
			$cFechaHora = $dFechaHora->format('YmdHis');
			$this->cFechaRta = substr($cFechaHora, 0, 8);
			$this->cHoraRta = substr($cFechaHora, 8, 6);
			$this->cEstadoLog = 'RECIBIDO';
			$this->aDatosRta['cCodAcepta'] = 'AA';
		}

		//Limpiar datos
		$this->aDatosORU['cIngreso'] = '';
		$this->aDatosORU['cNumOrden'] = '';
		$this->aDatosORU['cCodigoProc'] = '';
		$this->aDatosORU['cProcedimiento'] = '';

		//Datos generales del procedimiento
		$lnIndexFin = count($this->oMensaje->_segments)>5? 5: count($this->oMensaje->_segments);
		for ($lnIndex = 0; $lnIndex <= $lnIndexFin; $lnIndex++) {
			$loSegmento = $this->oMensaje->getSegmentByIndex($lnIndex);
			switch ($loSegmento->getName()) {
				case 'PID':
					$this->aDatosORU['cIngreso'] = $loSegmento->getField(3);
					break;
				case 'OBR':
					$this->aDatosORU['cNumOrden'] = $loSegmento->getField(2);
					$this->aDatosORU['cProcedimiento'] = $loSegmento->getField(4);
					$this->aDatosORU['cCodigoProc'] = substr($this->aDatosORU['cProcedimiento'], 0, 3);
					break;
			}
		}

		//Datos de los test
		$this->aTests = $this->fnTestsIni();
		$loResultados = $this->oMensaje->getSegmentsByName('OBX');
		foreach ($loResultados as $laResultado) {
			$this->aTests[$laResultado->getField(3)] = $laResultado->getField(5);
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
			'CUPLGC' => $this->aCup['GASES'],
			'INGLGC' => intval($this->aDatosORU['cIngreso']) ?? 0,
			'CORLGC' => intval($this->aDatosORU['cNumOrden']) ?? 0,
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
		if (empty($this->aDatosORU['cIngreso']) || empty($this->aDatosORU['cNumOrden'])
				|| !is_numeric($this->aDatosORU['cIngreso']) || !is_numeric($this->aDatosORU['cNumOrden'])) {
			$llSinError = false;
			$this->cEstadoLog = 'ERRPROCS';
		}
		// CREAR RESPUESTA
		$this->fnCrearRespuesta();

		if ($llSinError && $this->aDatosRta['cCodAcepta'] == 'AA') {

			//Obtiene el consecutivo de cita
			$laDatos = $this->oDB
				->select('D.CCIDET, O.RMEORD')
				->from('RIADETL06 D')
				->innerJoin('RIAORD O','D.INGDET=O.NINORD AND D.CCIDET=O.CCIORD AND D.CUPDET=O.COAORD')
				->where([
					'D.INGDET'=>$this->aDatosORU['cIngreso'],
					'D.CUPDET'=>$this->aCup['GASES'],
					'D.FL2DET'=>$this->aDatosORU['cNumOrden'],
					'D.ESTDET'=>8,
				])
				->getAll('array');
			if (!is_array($laDatos)) $laDatos = [];

			if (count($laDatos)>0) {
				$this->nConsecCita = $laDatos[0]['CCIDET'];
				$this->aDatosORU['cRMOrdena'] = $laDatos[0]['RMEORD'];
				$this->fnConsultaDatosIngreso();
				$this->fnVerificaDatosEnGases();
				$this->fnActualizarGases();
				$this->fnActualizaRiaOrd();
				if ($this->aDatosORU['cCodigoProc'] == 'ABG') {
					$this->fnGuardarElectrolitos();
				}
				$this->fnGuardaCobro();
				$this->fnGuardaRiaDet();

				$this->cEstadoLog = 'PROCESADO';
			} else {
				$laCupsElectrolitos = [];
				$lcSepara = '';
				foreach ($this->aCup as $valor) {
					if ($valor!=$this->aCup['GASES']) {
						$laCupsElectrolitos[] = $valor;
					}
				}
				$laDatos = $this->oDB
					->select('CCIDET, RMEORD')
					->from('RIADETL06 D')
					->innerJoin('RIAORD O', 'D.INGDET=O.NINORD AND D.CCIDET=O.CCIORD AND D.CUPDET=O.COAORD')
					->where([
						'INGDET'=>$this->aDatosORU['cIngreso'],
						'FL2DET'=>$this->aDatosORU['cNumOrden'],
						'ESTDET'=>8,
					])
					->in('CUPDET', $laCupsElectrolitos)
					->getAll('array');
				if (!is_array($laDatos)) $laDatos = [];

				if (count($laDatos) > 0) {	//&& $this->aDatosORU['cCodigoProc'] == 'ABG'
					$this->fnGuardarElectrolitos();
					$this->cEstadoLog = 'PROCESADO';
				} else {
					$this->cEstadoLog = 'NOPROCES';
				}
			}
		}
	}


	/*
	 * Datos ingreso y habitación actual
	 */
	function fnConsultaDatosIngreso()
	{
		$laDatos = $this->oDB
			->select('I.TIDING, I.NIDING, I.VIAING, I.PLAING, F.SECHAB, F.NUMHAB')
			->from('RIAINGL15 I')
			->leftJoin('FACHABL3 F', 'I.NIGING = F.INGHAB')
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
	 * Si no existe datos en GASES adiciona resultados en blanco
	 */
	function fnVerificaDatosEnGases()
	{
		$laDatos = $this->oDB
			->select('INDGAS, CNLGAS')
			->from('GASESL02')
			->where([
				'NROGAS'=>$this->aDatosORU['cIngreso'],
				'CONGAS'=>$this->nConsecCita,
				'SUBGAS'=>$this->aCup['GASES'],
			])
			->getAll('array');
		if (!is_array($laDatos)) $laDatos = [];
		$lnCtaReg = count($laDatos);

		//Si es necesario insertar registros
		if ($lnCtaReg<5) {
			// Registros existentes
			$laExisten = array();
			foreach ($laDatos as $laDato) {
				$laExisten[] = $laDato['INDGAS'] . $laDato['CNLGAS'];
			}

			// Plantilla de datos
			$lcSql = "INSERT INTO GASESL01 (NROGAS,CONGAS,SUBGAS,INDGAS,CNLGAS,DESGAS,USRGAS,PGMGAS,FECGAS,HORGAS) VALUES ";
			$lcDatGas = $this->aDatosORU['cIngreso'] . ", " . $this->nConsecCita . ", '" . $this->aCup['GASES'] . "'";
			$lcLogUsu = "'$this->cUser', '$this->cProg', $this->cFechaRta, $this->cHoraRta";
			$laModelo = [
				'11' => "($lcDatGas, 1, 1, 'Pac Cri :         0 Reg Med :  " . $this->aDatosORU['cRMOrdena'] . "', $lcLogUsu)",
				'21' => "($lcDatGas, 2, 1, 'AFI :          APH :          APC :          APO :          AHC :          ABE :          ASA :          ANA :', $lcLogUsu)",
				'22' => "($lcDatGas, 2, 2, 'AKK :          ACA :          AHG :          AHA :', $lcLogUsu)",
				'31' => "($lcDatGas, 3, 1, 'VPH :          VPC :          VPO :          VHC :          VSV :          VP5 :          VIC :          VDA :', $lcLogUsu)",
				'41' => "($lcDatGas, 4, 1, 'GLU :          LAC :', $lcLogUsu)",
			];

			// Crear cadena para insertar faltantes
			$lcComa = '';
			foreach ($laModelo as $lcClave => $lcValor) {
				if (in_array($lcClave, $laExisten)) {
				} else {
					$lcSql .= $lcComa . $laModelo[$lcClave];
					$lcComa = ',';
				}
			}
			$this->oDB->query($lcSql);
		}
	}


	/*
	 * Actualiza los datos en GASES
	 */
	function fnActualizarGases()
	{
		$laDatosGas = [
			'UMOGAS'=>$this->cUser,
			'PMOGAS'=>$this->cProg,
			'FMOGAS'=>$this->cFechaRta,
			'HMOGAS'=>$this->cHoraRta
		];
		$laWhere = [
			'NROGAS'=>$this->aDatosORU['cIngreso'],
			'CONGAS'=>$this->nConsecCita,
			'SUBGAS'=>$this->aCup['GASES'],
			'INDGAS'=>0,
			'CNLGAS'=>0,
		];
		switch ($this->aDatosORU['cCodigoProc']) {

			// Gases arteriales
			case 'ABG':
				$laWhere['INDGAS']=2;	// Indice
				$laWhere['CNLGAS']=1;	// Linea
				$laDatosGas['DESGAS'] = ''
					. 'AFI :' . str_replace(",",".",str_pad($this->aTests['FIO2'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'APH :' . str_replace(",",".",str_pad($this->aTests['pH'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'APC :' . str_replace(",",".",str_pad($this->aTests['pCO2'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'APO :' . str_replace(",",".",str_pad($this->aTests['pO2'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'AHC :' . str_replace(",",".",str_pad($this->aTests['HCO3act'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'ABE :' . str_replace(",",".",str_pad($this->aTests['BE(B)'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'ASA :' . str_replace(",",".",str_pad($this->aTests['sO2'], 9, ' ', STR_PAD_LEFT)) . ' '
					. 'ANA :' . str_replace(",",".",str_pad($this->aTests['Na+'], 9, ' ', STR_PAD_LEFT)) . ' ';
				$this->oDB->from('GASESL01')->where($laWhere)->actualizar($laDatosGas);

				$laWhere['INDGAS']=2;	// Indice
				$laWhere['CNLGAS']=2;	// Linea
				$laDatosGas['DESGAS'] = ''
						. 'AKK :' . str_replace(",",".",str_pad($this->aTests['K+'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'ACA :' . str_replace(",",".",str_pad($this->aTests['Ca++'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'AHG :' . str_replace(",",".",str_pad($this->aTests['tHb'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'AHA :' . str_replace(",",".",str_pad($this->aTests['Hct'], 9, ' ', STR_PAD_LEFT)) . ' ';
				$this->oDB->from('GASESL01')->where($laWhere)->actualizar($laDatosGas);

				$laWhere['INDGAS']=4;	// Indice
				$laWhere['CNLGAS']=1;	// Linea
				$laDatosGas['DESGAS'] = ''
						. 'GLU :' . str_replace(",",".",str_pad($this->aTests['Glucose'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'LAC :' . str_replace(",",".",str_pad($this->aTests['Lactate'], 9, ' ', STR_PAD_LEFT)) . ' ';
				$this->oDB->from('GASESL01')->where($laWhere)->actualizar($laDatosGas);

				break;

			// Gases venosos
			case 'VBG':
				$laWhere['INDGAS']=3;	// Indice
				$laWhere['CNLGAS']=1;	// Linea
				$laDatosGas['DESGAS'] = ''
						. 'VPH :' . str_replace(",",".",str_pad($this->aTests['pH'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VPC :' . str_replace(",",".",str_pad($this->aTests['pCO2'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VPO :' . str_replace(",",".",str_pad($this->aTests['pO2'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VHC :' . str_replace(",",".",str_pad($this->aTests['HCO3act'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VSV :' . str_replace(",",".",str_pad($this->aTests['sO2'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VP5 :' . str_replace(",",".",str_pad($this->aTests['p50'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VIC :' . str_replace(",",".",str_pad($this->aTests['VIC'], 9, ' ', STR_PAD_LEFT)) . ' '
						. 'VDA :' . str_replace(",",".",str_pad($this->aTests['VDA'], 9, ' ', STR_PAD_LEFT)) . ' ';
				$this->oDB->from('GASESL01')->where($laWhere)->actualizar($laDatosGas);

				break;
		}
	}


	/*
	 * Actualiza RIAORD
	 */
	function fnActualizaRiaOrd()
	{
		$laDatosRiaord = [
			'RMRORD'=>$this->aDatosORU['cRMOrdena'],
			'FERORD'=>$this->cFechaRta,
			'HRLORD'=>$this->cHoraRta,
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
			'COAORD'=>$this->aCup['GASES'],
		];
		$this->oDB->from('RIAORD')->where($laWhere)->actualizar($laDatosRiaord);
	}


	/*
	 * Guarda los procedimientos correspondientes a electrolitos
	 */
	function fnGuardarElectrolitos()
	{
		$lnEstadoProcIon = 3;
		foreach ($this->aCup as $lcClave => $luCupIon) {
			if ($lcClave != 'GASES' AND $this->aTests[$lcClave] != '0.00') {
				// Consultar si fue ordenado
				$laDatos = $this->oDB
					->select('D.CCIDET')
					->from('RIADET D')
					->innerJoin('RIAORD O', 'D.INGDET=O.NINORD AND D.CCIDET=O.CCIORD AND D.CUPDET=O.COAORD')
					->where([
						'D.INGDET'=>$this->aDatosORU['cIngreso'],
						'D.CUPDET'=>$luCupIon,
						'D.FL2DET'=>$this->aDatosORU['cNumOrden'],
						'D.ESTDET'=>8,
						'O.CODORD'=>'312',
					])
					->get('array');
				if (!is_array($laDatos)) $laDatos=[];
				if (count($laDatos) > 0) {
					$this->nConsecCitaIon = $laDatos['CCIDET'];

					// Actualiza RIAORD
					$laDatosRiaord = [
						'RMRORD' => $this->aDatosORU['cRMOrdena'],
						'FERORD' => $this->cFechaRta,
						'HRLORD' => $this->cHoraRta,
						'ESTORD' => $lnEstadoProcIon,
						'UMOORD' => $this->cUser,
						'PMOORD' => $this->cProg,
						'FMOORD' => $this->cFechaRta,
						'HMOORD' => $this->cHoraRta,
					];
					$laWhere = [
						'NINORD'=>$this->aDatosORU['cIngreso'],
						'CCIORD'=>$this->nConsecCitaIon,
						'COAORD'=>$luCupIon,
					];
					$this->oDB->from('RIAORD')->where($laWhere)->actualizar($laDatosRiaord);

					//Guarda RIADET
					$laWhere = [
						'TIDDET' => $this->aDatosORU['cTipoDoc'],
						'NIDDET' => $this->aDatosORU['cNumDoc'],
						'INGDET' => $this->aDatosORU['cIngreso'],
						'CCIDET' => $this->nConsecCitaIon,
						'CUPDET' => $luCupIon,
						'FERDET' => $this->cFechaRta,
						'HRRDET' => $this->cHoraRta,
						'ESTDET' => $lnEstadoProcIon,
					];
					$laRiaDet = $this->oDB->select('INGDET')->from('RIADET')->where($laWhere)->getAll('array');
					if ($this->oDB->numRows()==0) {
						$laDatosRiadet = array_merge($laWhere, [
							'FL2DET' => $this->aDatosORU['cNumOrden'],
							'USRDET' => $this->cUser,
							'PGMDET' => $this->cProg,
							'FECDET' => $this->cFechaRta,
							'HORDET' => $this->cHoraRta,
						]);
						$this->oDB->from('RIADET')->insertar($laDatosRiadet);
					}

					// Resultado Orden Consecutivos 1 y 101
					for	($lcConsecIon=1; $lcConsecIon<102; $lcConsecIon+=100) {
						$lcDescIon = $lcConsecIon==1? $this->aTests[$lcClave] : $this->aDatosORU['cRMOrdena'];

						$laWhere = [
							'NROING'=>$this->aDatosORU['cIngreso'],
							'CONCON'=>$this->nConsecCitaIon,
							'SUBORG'=>$luCupIon,
							'INDICE'=>70,
							'CONSEC'=>$lcConsecIon,
						];
						$laDatos = $this->oDB
							->select('COUNT(*) AS CUENTA')
							->from('RIAHIS35')
							->where($laWhere)
							->getAll('array');
						if ($laDatos[0]['CUENTA']==0) {
							$laDatosRiaHis = [
								'NROING' => $this->aDatosORU['cIngreso'],
								'CONCON' => $this->nConsecCitaIon,
								'SUBORG' => $luCupIon,
								'DESCRI' => $lcDescIon,
								'TIDHIS' => $this->aDatosORU['cTipoDoc'],
								'NIDHIS' => $this->aDatosORU['cNumDoc'],
								'INDICE' => 70,
								'SUBIND' => 0,
								'CODIGO' => 0,
								'SUBHIS' => 0,
								'CONSEC' => $lcConsecIon,
								'CONHIS' => 0,
								'USRHIS' => $this->cUser,
								'PGMHIS' => $this->cProg,
								'FECHIS' => $this->cFechaRta,
								'HORHIS' => $this->cHoraRta,
							];
							$this->oDB->from('RIAHIS')->insertar($laDatosRiaHis);
						} else {
							$laDatosRiaHis = [
								'DESCRI' => $lcDescIon,
								'UMOHIS' => $this->cUser,
								'PMOHIS' => $this->cProg,
								'FMOHIS' => $this->cFechaRta,
								'HMOHIS' => $this->cHoraRta,
							];
							$this->oDB->from('RIAHIS')->where($laWhere)->actualizar($laDatosRiaHis);
						}
					}
				}
			}
		}
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
				'CUPEST'=>$this->aCup['GASES'],
				'TINEST'=>'400',
			])
			->getAll('array');
		if (is_array($laDatos)) {
			//Si no existe cobro lo inserta
			if ($laDatos[0]['CUENTA']==0) {
				//	PARAMETROS
				$taParam = [
					'ingreso'		=> $this->aDatosORU['cIngreso'],
					'codVia'		=> $this->aDatosORU['cCodVia'],
					'codPlan'		=> $this->aDatosORU['cCodPlan'],
					'regMedOrdena'	=> $this->aDatosORU['cRMOrdena'],
					'regMedRealiza'	=> $this->cRMRealiza,
					'numIdPac'		=> $this->aDatosORU['cNumDoc'],
					'codCup'		=> $this->aCup['GASES'],
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
		$laWhere = [
			'TIDDET' => $this->aDatosORU['cTipoDoc'],
			'NIDDET' => $this->aDatosORU['cNumDoc'],
			'INGDET' => $this->aDatosORU['cIngreso'],
			'CCIDET' => $this->nConsecCita,
			'CUPDET' => $this->aCup['GASES'],
			'FERDET' => $this->cFechaRta,
			'HRRDET' => $this->cHoraRta,
			'ESTDET' => $this->nEstadoProc,
		];
		$laRiadet=$this->oDB
			->select('COUNT(*) CUENTA')
			->from('RIADET')
			->where($laWhere)
			->getAll('array');
		if($this->oDB->numRows()>0){
			if($laRiadet[0]['CUENTA']>0){
				return;
			}
		}
		$laDatosRiadet = array_merge($laWhere, [
			'MARDET' => $this->nCobrado,
			'FL2DET' => $this->aDatosORU['cNumOrden'],
			'USRDET' => $this->cUser,
			'PGMDET' => $this->cProg,
			'FECDET' => $this->cFechaRta,
			'HORDET' => $this->cHoraRta,
		]);
		$this->oDB->from('RIADET')->insertar($laDatosRiadet);
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
			'UMOLGC' => $this->cUser,
			'PMOLGC' => $this->cProg,
			'FMOLGC' => $lcFecha,
			'HMOLGC' => $lcHora,
		];
		$laWhere = [
			'PRVLGC'=>$this->cModelo,
			'TIPLGC'=>$this->cTipoMensaje,
			'CODLGC'=>$this->cNumMensaje,
			'INGLGC'=>$this->aDatosORU['cIngreso'] ?? 0,
		];
		$this->oDB->from('LGSPRV')->where($laWhere)->actualizar($laDatosLog);
	}


	/*
	 * Retorna datos en blanco para Test
	 */
	function fnTestsIni() {
		return [
			'pH'		 => '0.00',
			'pCO2'		 => '0.00',
			'pO2'		 => '0.00',
			'Na+'		 => '0.00',
			'K+'		 => '0.00',
			'Ca++'		 => '0.00',
			'Cl-'		 => '0.00',
			'Glucose'	 => '0.00',
			'Lactate'	 => '0.00',
			'Hct'		 => '0.00',
			'tHb'		 => '0.00',
			'O2Hb'		 => '0.00',
			'COHb'		 => '0.00',
			'MetHb'		 => '0.00',
			'HHb'		 => '0.00',
			'HCO3act'	 => '0.00',
			'BE(B)'		 => '0.00',
			'tCO2'		 => '0.00',
			'sO2'		 => '0.00',
			'O2SAT(est)' => '0.00',
			'O2CAP'		 => '0.00',
			'ctO2(a)'	 => '0.00',
			'ctO2(v)'	 => '0.00',
			'ctO2(Hb)'	 => '0.00',
			'ctO2'		 => '0.00',
			'O2CT'		 => '0.00',
			'AaDO2'		 => '0.00',
			'a/A'		 => '0.00',
			'RI'		 => '0.00',
			'Temp'		 => '0.00',
			'FIO2'		 => '0.00',
			'p50'		 => '0.00',
			'VIC'		 => '0.00',
			'VDA'		 => '0.00',
		];
	}

}
