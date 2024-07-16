<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Diagnostico.php';
require_once __DIR__ .'/class.AplicacionFunciones.php';


class Doc_Evolucion
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
		'cTitulo' => 'EVOLUCIÓN',
		'lMostrarEncabezado' => true,
		'lMostrarFechaRealizado' => false,
		'lMostrarViaCama' => false,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
		'cTxtLuegoDeCup' => '',
		'aCuerpo' => [],
		'aFirmas' => [],
		'aNotas' => ['notas'=>false,],
	];
	protected $cEvolucion = '';
	protected $aTitulos = [];
	protected $aTr = [];
	protected $aNihss = [];
	protected $nCnsHasta = '6200';
	protected $nCnsConducta = '5995';
	protected $cSL = PHP_EOL;
	protected $aPrioridadesIntCon = [];
	protected $nSinEsp = 217;
	public $bEsConsulta = false;

	// Programas que registran titulo en linea 1
	protected $aPrgTitulo = ['EV0015', 'EV0017', 'EV0019', 'EV0023AN', 'EV0023', 'ORDMEDWEB', 'EXA001', 'EV00171', 'EV0022AN', 'NUT010', 'EVOPIWEB', 'EVOEVWEB', 'EVOUNWEB', 'EVOURWEB'];

	// Programas que solicitan ordenes médicas
	protected $aPrgOrdenes = ['EV0017', 'EV0019', 'EV0023AN', 'EV0023', 'ORDMEDWEB', 'EV00171', 'EVOPIWEB', 'EVOEVWEB', 'EVOUNWEB', 'EVOURWEB'];

	// Programas que interpretan exámenes
	protected $aPrgInterpEx = ['EV0015', 'EV0017', 'EV0019', 'EXA001', 'EV00171', 'EVOPIWEB', 'EVOEVWEB', 'EVOUNWEB', 'EVOURWEB'];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->aTitulos = $this->titulosDoc();
		$laPriIntCon = $this->oDb
			->select('CL1TMA,DE2TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'INCOPRIO','CL2TMA'=>'1','ESTTMA'=>'',])
			->getAll('array');
		if (is_array($laPriIntCon)) {
			if (count($laPriIntCon)>0) {
				foreach($laPriIntCon as $laPriInt){
					$this->aPrioridadesIntCon[trim($laPriInt['CL1TMA'])]=trim($laPriInt['DE2TMA']);
				}
			}
		}
	}


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 */
	private function consultarDatos($taData)
	{
		$laCondiciones = ['NINEVL'=>$taData['nIngreso']];
		if(!empty($taData['nConsecEvol'])) {
			$laCondiciones['CONEVL']=$taData['nConsecEvol'];
		} elseif (isset($taData['nFechaDesde']) && isset($taData['nFechaHasta'])) {
			$this->oDb->between('FECEVL', $taData['nFechaDesde'], $taData['nFechaHasta']);
		} else {
			$this->aReporte = [
				'cTitulo' => 'EVOLUCIÓN',
				'lMostrarEncabezado' => false,
			];
		}

		$laEvoluciones = $this->oDb
			->select("NINEVL,CONEVL,CCIEVL,CNLEVL,DESEVL,USREVL,PGMEVL,FECEVL,HOREVL,IFNULL(USRRIC,'') USRRIC,FECRIC,HORRIC")
			->from('EVOLUC')
			->leftJoin('REINCA',"INGRIC=NINEVL AND CEVRIC=CONEVL AND ESTRIC='VA' AND TIPRIC IN ('EP','EU','ER','ET','EV')")
			->where($laCondiciones)
			->orderBy('CONEVL DESC,CNLEVL')
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$laConsecutivo = $laEvoluciones[0]['CONEVL'];
			$laEvol = [];
			foreach($laEvoluciones as $laClave=>$laEvolucion)
			{
				if($laEvolucion['CONEVL'] != $laConsecutivo){
					$this->Cargar_Evolucion($laEvol, $taData, true);
					$laConsecutivo = $laEvolucion['CONEVL'];
					$laEvol = [];
				}
				$laEvol[] = $laEvolucion;
			}
			$this->Cargar_Evolucion($laEvol, $taData, false);
		}
	}


	function Cargar_Evolucion($taEvol, $taData, $tlDivision)
	{
		if (!is_array($taEvol)) return;
		if (count($taEvol)<=0) return;

		$laOrdMed = [
			'P'=>['txt'=>'', 'ttl'=>'PROCEDIMIENTOS SOLICITADOS'],
			'O'=>['txt'=>'', 'ttl'=>'OXÍGENO'],
			'M'=>['txt'=>'', 'ttl'=>'MEDICAMENTOS'],
			'I'=>['txt'=>'', 'ttl'=>'INTERCONSULTAS'],
			'D'=>['txt'=>'', 'ttl'=>'DIETA'],
			'E'=>['txt'=>'', 'ttl'=>'ÓRDENES A ENFERMERÍA'],
		];

		$lcProg = trim($taEvol[0]['PGMEVL'],' ');
		$lcUser = trim($taEvol[0]['USREVL'],' ');
		$lcFech = $taEvol[0]['FECEVL'];
		$lcHora = $taEvol[0]['HOREVL'];
		$lcProg = $lcProg=='EVOUNWEB'?'EV0019':($lcProg=='EVOURWEB'?'EV0017':($lcProg=='EVOPIWEB'?'EV0017':$lcProg));
		$ldFechora = new \DateTime($taData['tFechaHora']??'');
		$loMpas1 = 0;
		$lcReg1 = '';

		$laCnsc = $this->oDb
			->select('DE2TMA AS CnsHasta, OP2TMA AS Retorno, OP3TMA AS CnsConducta')
			->from('TABMAE')
			->where('TIPTMA=\'RANEVO\' AND '.$ldFechora->format('Ymd').' BETWEEN CL1TMA AND CL2TMA')
			->get('array');
		if (is_array($laCnsc)) {
			$this->nCnsHasta = intval($laCnsc['CNSHASTA'] ?? $this->nCnsHasta);
			$this->nCnsConducta = intval($laCnsc['CNSCONDUCTA'] ?? $this->nCnsConducta);
			$lcConRetorno = trim(($laCnsc['RETORNO'] ?? ''),' ');
			$this->cSL = empty($lcConRetorno) ? '' : $this->cSL;
		}
		unset($laCnsc);

		//	Título del Documento
		$lcDatosEnc ='';
		if (!$tlDivision){
			$this->aReporte['cTitulo'] = $this->aTitulos[$lcProg] ?? $this->aTitulos['DEFAULT'];
			$lcDatosEnc = 'Vía: '.($taData['cDesVia']??'');
		}

		// NIHSS
		$this->aNihss['Cons'] = $taData['nConsecEvol']??0;
		$this->aNihss['Tipo'] =	in_array($lcProg,['EV0017','EVOPIWEB', 'EVOURWEB']) ? 'EVPL' :
								(in_array($lcProg,['EV0019','EVOUNWEB']) ? 'EVUC' :
								(in_array($lcProg,['HISINTWEB','HIS001']) ? 'INCO' : ''));

		// Médico
		$lbMedico = false;
		$lcNombreMedico = '';
		$lcRegistroMedico = '';
		$lcEspecialidadMedico = '';
		$lcUsuarioRealiza = ''; $laUsuarioRealiza = [];
		foreach ($taEvol as $laEv) {
			switch (true) {

				case $lcProg=='EXA001' && $laEv['CNLEVL']==7490 && mb_strtoupper(mb_substr($laEv['DESEVL'],0,4))=='DR.:':
					$lcNombreMedico = trim($laEv['DESEVL'],' ');
					$lbMedico = true;
					break;

				case $lcProg=='EXA001' && $laEv['CNLEVL']==7495 && $lbMedico:
					$lcRegistroMedico = trim($laEv['DESEVL'],' ');
					break;

				case in_array($laEv['CNLEVL'], [1501,900001]) && mb_strtoupper(mb_substr(trim($laEv['DESEVL']),0,3))=='DR.':
					$lnPosRM = mb_strpos($laEv['DESEVL'],' - RME:');
					$lcNombreMedico = trim(mb_substr($laEv['DESEVL'], 0, $lnPosRM),' ');
					$lcRegistroMedico = mb_substr($laEv['DESEVL'], $lnPosRM+3, 18);
					$lcEspecialidadMedico = trim(mb_substr($laEv['DESEVL'], $lnPosRM+21));
					$lbMedico = true;
					break;
			}
			$lcUsuarioRealiza = $laEv['USRRIC'] ?? '';
			$lnFechaRealiza = $laEv['FECRIC'] ?? 0;
			$lnHoraRealiza = $laEv['HORRIC'] ?? 0;
		}
		if (empty($lcNombreMedico)) {
			foreach ($taEvol as $laEv) {
				if (!mb_strpos(mb_strtoupper($laEv['DESEVL']),'DR.')===false && !mb_strpos($laEv['DESEVL'],' - RME:')===false){
					$lnCharMed = mb_strpos(mb_strtoupper($laEv['DESEVL']), 'DR.');
					$lnCharReg = mb_strpos($laEv['DESEVL'], ' - RME:');
					$lcNombreMedico = mb_substr($laEv['DESEVL'], $lnCharMed, $lnCharReg-$lnCharMed);
					$lcRegistroMedico = mb_substr($laEv['DESEVL'], $lnCharReg + 3, 18);
					$lcEspecialidadMedico = trim(mb_substr($laEv['DESEVL'], $lnCharReg + 21));
				}
			}
		}
		if (!empty($lcUsuarioRealiza)) {
			$laUsuRealiza = $this->oDb
				->select('NNOMED,NOMMED,REGMED,TPMRGM,TABDSC,CODRGM,DESESP')
				->from('RIARGMN')
				->leftJoin('PRMTAB', "TABTIP='TUS' AND TABCOD<>'' AND TPMRGM=INTEGER(TABCOD)")
				->leftJoin('RIAESPE', 'CODRGM=CODESP')
				->where('USUARI','=',trim($lcUsuarioRealiza))
				->get('array');
			if ($this->oDb->numRows()>0) {
				$laUsuRealiza = array_map('trim',$laUsuRealiza);
				$laUsuarioRealiza = [
					'nombre'=>$laUsuRealiza['NNOMED'],
					'apellido'=>$laUsuRealiza['NOMMED'],
					'rm'=>$laUsuRealiza['REGMED'],
					'codtipo'=>$laUsuRealiza['TPMRGM'],
					'tipo'=>$laUsuRealiza['TABDSC'],
					'codespecialidad'=>$laUsuRealiza['CODRGM'],
					'especialidad'=>$laUsuRealiza['DESESP'],
					'fecha'=>AplicacionFunciones::formatFechaHora('fecha', $lnFechaRealiza),
					'hora'=>AplicacionFunciones::formatFechaHora('hora', $lnHoraRealiza),
				];
			}
		}

		// Número, fecha y hora de evolución
		if (in_array($lcProg, $this->aPrgTitulo)) {
			foreach ($taEvol as $laEv) {
				if ($laEv['CNLEVL']=='1') {
					$lcReg1 = trim($lcProg=='EV00171' ? mb_substr($laEv['DESEVL'],0,48) : $laEv['DESEVL'],' ');
					$this->aTr[] = ['titulo1', $lcReg1];
					$this->aTr[] = ['texto9', $lcDatosEnc];
					$loMpas1 = 1;
					break;
				}
			}
		}

		// Contenido
		$this->cEvolucion = '';
		switch ($lcProg) {

			// Eventualidades
			case 'EV0015': case 'EVOEVWEB':
				$this->cEvolucion = $this->Eventualidad($taEvol);
				break;

			// Evolución Piso - Justificación NoPOS
			case 'EV0017': case 'EV0022AN': case 'EVOPIWEB': case 'EVOURWEB':
				$this->cEvolucion = $this->EvolucionPisos($taEvol);
				break;

			// Evolución Urgencias
			case 'EV00171':
				$this->cEvolucion = $this->EvolucionUrgencias($taEvol);
				break;

			// Evolución Unidades
			case 'EV0019': case 'EVOUNWEB':
				$this->cEvolucion = $this->EvolucionUnidades($taEvol);
				break;

			// Respuesta Interconsulta			** PARA MOSTRAR TEXTO CON EVOLUCIONES **
			case 'HIS001': case 'HISINTWEB':
				$this->cEvolucion = $this->RespuestaInterconsulta($taEvol, $lcDatosEnc, $lcNombreMedico);
				break;

			// Descripción Quirúrgica			** PARA MOSTRAR TEXTO CON EVOLUCIONES **
			case 'RIA133':
				$this->cEvolucion = $this->DescripcionQuirurgica($taEvol);
				break;

			// Procedimientos Neuroradiología	** PARA MOSTRAR TEXTO CON EVOLUCIONES **
			case 'RIA133E':
				$this->cEvolucion = $this->ProcNeuroradiologia($taEvol);
				break;

			// Junta Médica						** PARA MOSTRAR TEXTO CON EVOLUCIONES **
			case 'RIA050':
				$this->cEvolucion = $this->JuntaMedica($taEvol);
				break;

			// Tamizaje Nutricional
			case 'NUT010':
				$this->cEvolucion = $this->TamizajeNutricional($taEvol);
				break;

			// Ordenes Médicas e Interpretación Exámenes
			case 'EV0023AN': case 'EV0023': case 'ORDMEDWEB': case 'EXA001':
				break;

			// Otros
			default:
				$this->cEvolucion = $this->OtrasEvoluciones($taEvol);
				break;
		}

		if (!empty($this->cEvolucion)) {
			$this->aTr[] = ['titulo2', $this->aReporte['cTitulo']];
			$this->aTr[] = ['texto9', $this->cEvolucion];
		}

		// Interpretación de exámenes
		$lcObsInt = '';
		if (in_array($lcProg, $this->aPrgInterpEx)) {
			foreach ($taEvol as $laEv) {
				if ($laEv['CNLEVL']>7000 && $laEv['CNLEVL']<7451) {
					$lcObsInt .= trim($laEv['DESEVL'],' ').PHP_EOL;
				}
			}
		}
		if(!empty($lcObsInt)){
			$this->aTr[] = ['titulo3', 'INTERPRETACIÓN DE EXÁMENES'];
			$this->aTr[] = ['texto9', $lcObsInt.PHP_EOL];
		}

		// Órdenes Médicas
		if (in_array($lcProg, $this->aPrgOrdenes)) {
			$laOrdMed['P']['txt'] = $this->omProcedimientos($laEv['NINEVL'],$laEv['CONEVL']);
			$laOrdMed['O']['txt'] = $this->omOxigeno($laEv['NINEVL'],$laEv['CONEVL']);
			$laOrdMed['M']['txt'] = $this->omMedicamentos($laEv['NINEVL'],$laEv['CONEVL']);
			$laOrdMed['I']['txt'] = $this->omInterconsultas($laEv['NINEVL'],$laEv['CONEVL']);
			$laOrdMed['D']['txt'] = $this->omDietas($laEv['NINEVL'],$laEv['CONEVL']);
			$laOrdMed['E']['txt'] = $this->omOrdenesEnfermeria($laEv['NINEVL'],$laEv['CONEVL'],$laOrdMed['O']['txt']);
		}
		//	Título órdenes médicas
		if (!empty($laOrdMed['P']['txt'].$laOrdMed['M']['txt'])) {
			if ($loMpas1==0) {
				$this->aTr[] = ['titulo2', $lcReg1];
			}
			$this->aTr[] = ['titulo2', 'SOLICITUD DE ORDENES MÉDICAS'];
		}
		$lcOrdMed='';
		foreach($laOrdMed as $laOrd){ $lcOrdMed.=$laOrd['txt']; }
		if (in_array($lcProg, ['EV0023AN', 'EV0023', 'ORDMEDWEB']) && empty($lcOrdMed)) {
			$this->aTr[] = ['titulo2', 'SOLICITUD DE ORDENES MÉDICAS'];
		}
		// Descripciones órdenes médicas
		foreach($laOrdMed as $laOrd){
			if (!empty(trim($laOrd['txt']))) {
				$this->aTr[] = ['titulo3', $laOrd['ttl']];
				$this->aTr[] = ['texto9', $laOrd['txt']];
			}
		}

		// COMPONENTES SANGUINEOS
		$lcOrdenBancoS = $this->HemocomponentesTrasfusion($laEv['NINEVL'],$laEv['CONEVL']);
		$lcDatosComponentes = $this->HemocomponentesProcedimientos($laEv['NINEVL'],$laEv['CONEVL']);
		if(!empty($lcOrdenBancoS)){
			$this->aTr[] = ['titulo3', 'COMPONENTES SANGUINEOS - DATOS DE LA TRANSFUSIÓN'];
			$this->aTr[] = ['texto9', $lcOrdenBancoS.PHP_EOL];
		}
		if(!empty($lcDatosComponentes)){
			$this->aTr[] = ['titulo3', 'COMPONENTES SANGUINEOS - PROCEDIMIENTOS'];
			$this->aTr[] = ['texto9', $lcDatosComponentes.PHP_EOL];
		}

		// Estudiante que hizo evolución
		if (count($laUsuarioRealiza)>0) {
			$lcPrefijoUR = $this->oDb->obtenerTabmae1('DE1TMA', 'LIBROHC', "CL1TMA='FIRMATIP' AND CL2TMA='{$laUsuarioRealiza['codtipo']}' AND ESTTMA=''", null, '');
			$this->aTr[] = ['saltol', 5];
			$this->aTr[] = ['txthtml9', "<b>Realizado por:</b><br>{$lcPrefijoUR} {$laUsuarioRealiza['nombre']} {$laUsuarioRealiza['apellido']} - Documento: {$laUsuarioRealiza['rm']}"];
		//	$this->aTr[] = ['txthtml9', "  - Fecha realizado: {$laUsuarioRealiza['fecha']} {$laUsuarioRealiza['hora']}"];
			$this->aTr[] = ['saltol', 5];
			$this->aTr[] = ['txthtml9', '<b>Avalado por:</b>'];
		}

		// Firma
		if (empty($lcNombreMedico)) {
			$this->aTr[] = ['firmas', [ ['usuario'=>$lcUser, 'especialidad'=>false, ], ], ];
		} else {
			$this->aTr[] = ['firmas', [ ['texto_firma'=>$lcNombreMedico. PHP_EOL .$lcRegistroMedico. PHP_EOL .$lcEspecialidadMedico, 'registro'=>trim(str_replace(['RME','RM',':','.'],'', $lcRegistroMedico),' '), ], ], ];
		}

		if ($tlDivision){
			$this->aTr[] = ['titulo3', ''];
			$this->aTr[] = ['texto9', str_repeat('-',115)."\n"."\n"];
		}

		$this->aReporte['aCuerpo'] = $this->aTr;
	}

	private function Causa_Fallece($tnIngreso, $tnConsEvo, $tnFecha)
	{
		$lcCausaFallece = '';
		$laFallece = $this->oDb
			->select('OP5EDC AS FALLECE')
			->from('EVODIA')
			->where([ 'INGEDC'=>$tnIngreso, 'EVOEDC'=>$tnConsEvo, ])
			->orderBy('EVOEDC DESC')
			->get('array');
		if (is_array($laFallece)) {
			if (isset($laFallece['FALLECE'])) {
				$lcFallece = explode('=',$laFallece['FALLECE']);
				if(count($lcFallece)>1){
					$lcCodCausa = trim($lcFallece[2]);
					$loCausa = new Diagnostico($lcCodCausa, $tnFecha);
					$lcDesCausa = $loCausa->getTexto();
					$lcFecha = AplicacionFunciones::formatFechaHora('fecha',$lcFallece[1]);
					$lcHora = AplicacionFunciones::formatFechaHora('hora12',$lcFallece[0]);
					$lcCausaFallece = "  Fecha: $lcFecha  Hora: $lcHora". PHP_EOL
								. ( empty($lcDesCausa) ? '' : ' - Causa de Muerte: '.$lcDesCausa. PHP_EOL);
				}
			}
		}
		return $lcCausaFallece;
	}

	private function Eventualidad($taEvol)
	{
		$lcEvolucion = '';
		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']>=7000) { break; }
			if ($laEv['CNLEVL']>5000) {
				// Verifica título análisis para epicrisis
				if ($laEv['CNLEVL']==5500 && !empty($lcEvolucion)) {
					$this->aTr[] = ['titulo2', $this->aReporte['cTitulo']];
					$this->aTr[] = ['texto9', $lcEvolucion];
					$this->aReporte['cTitulo'] = trim($laEv['DESEVL'],' ');
					$lcEvolucion = '';
				} else {
					$lcDsc = rtrim($laEv['DESEVL'], ' ');
					$lcEvolucion .= mb_strlen($lcDsc)>$this->nSinEsp ? $laEv['DESEVL'] : $lcDsc;
				}
			}
		}
		if (!empty($lcEvolucion)) {
			$this->aTr[] = ['titulo2', $this->aReporte['cTitulo']];
			$this->aTr[] = ['texto9', $lcEvolucion];
		}
		$this->aReporte['cTitulo'] = $this->aTitulos['EV0015'];
		return '';
	}

	private function EvolucionPisos($taEvol)
	{
		$lcEvolucion = $this->Texto_Pandemia($taEvol);
		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']>$this->nCnsHasta) { break; }
			if ($laEv['CNLEVL']>1) {
				if (mb_strpos(mb_strtoupper(mb_substr($laEv['DESEVL'],1,4)), 'DR.')===false) {
					$lcDsc = rtrim($laEv['DESEVL'], ' ');
					$lcEvolucion .= mb_strlen($lcDsc)>$this->nSinEsp ? $laEv['DESEVL'] : $lcDsc . $this->cSL;

					if ($laEv['CNLEVL']==$this->nCnsConducta) {
						$lcEvolucion .= $this->Causa_Fallece($laEv['NINEVL'],$laEv['CONEVL'],$laEv['FECEVL']);
					}
				}
			}
		}
		return $lcEvolucion;
	}

	private function EvolucionUrgencias($taEvol)
	{
		$lcEvolucion = $this->Texto_Pandemia($taEvol);
		$lnCasu = 0;
		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']>6000) { break; }
			if ($laEv['CNLEVL']>0) {
				if ( mb_strpos(mb_strtoupper(mb_substr($laEv['DESEVL'],1,4)), 'DR.')===false ){
					$lnCasu++;
					$lcDsc = rtrim($laEv['DESEVL'], ' ');
					$lcEvolucion .= $lnCasu==1 ? mb_substr(trim($laEv['DESEVL'],' '),48) : ( mb_strlen($lcDsc)>$this->nSinEsp ? $laEv['DESEVL'] : $lcDsc . PHP_EOL );
				}
			}
		}
		return $lcEvolucion;
	}

	private function EvolucionUnidades($taEvol)
	{
		$lcEvolucion = $this->Texto_Pandemia($taEvol);
		$lcEvolucion .= $lcTitulo = '';
		$laNumTitulos = [500,1100,1200,1300,1400,1500,1600,1700,1800,2100,2200,2500,2800,3100,3700,3800,3810,3820,3822,3824,3830,5000,5500,6000];
		$laNumTituEsp = [2000,3500,3600,3826];
		$this->aTr[] = ['titulo2', 'Evolución'];

		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']>=7000) { break; }
			if ($laEv['CNLEVL']>99) {
				switch (true) {

					case in_array($laEv['CNLEVL'], $laNumTitulos):
						if (!empty($lcEvolucion)) {
							$this->aTr[] = ['titulo3', $lcTitulo];
							$this->aTr[] = ['texto9', trim($lcEvolucion,'')];
							$lcEvolucion = '';
						}
						$lcTitulo = trim($laEv['DESEVL'],' ');
						break;

					case in_array($laEv['CNLEVL'], $laNumTituEsp):
						if (!empty($lcEvolucion)) {
							$this->aTr[] = ['titulo3', $lcTitulo];
							$this->aTr[] = ['texto9', trim($lcEvolucion,'')];
						}
						$lcTitulo =	($laEv['CNLEVL']==2000 ? PHP_EOL .'SIGNOS VITALES' :
									($laEv['CNLEVL']==3500 ? PHP_EOL .'APACHE/SOFA/PARSONETT' :
									($laEv['CNLEVL']==3600 ? PHP_EOL .'TIMI/TISS-28' :
									($laEv['CNLEVL']==3826 ? PHP_EOL .'EUROSCORE' : ''))));
						$lcEvolucion = trim($laEv['DESEVL'],' ');
						if ($laEv['CNLEVL']==3826) {
							$lcEvolucion = str_replace('EUROSCORE:', '', $laEv['DESEVL']);
						}
						break;

					case $laEv['CNLEVL']==5995:
						if (!empty($lcEvolucion)) {
							$this->aTr[] = ['titulo3', $lcTitulo];
							$this->aTr[] = ['texto9', trim($lcEvolucion,'')];
						}
						$lcTitulo =	PHP_EOL .'CONDUCTA A SEGUIR';
						$lcEvolucion = trim($laEv['DESEVL']) . $this->Causa_Fallece($laEv['NINEVL'],$laEv['CONEVL'],$laEv['FECEVL']);
						$lcEvolucion = trim(str_replace('CONDUCTA A SEGUIR:', '', $lcEvolucion));
						break;

					case $laEv['CNLEVL']==6999:
						if (!empty($lcEvolucion)) {
							$this->aTr[] = ['titulo3', $lcTitulo];
							$this->aTr[] = ['texto9', trim($lcEvolucion,'')];
						}
						$lcTitulo =	PHP_EOL .'REALIZA ACTIVIDAD FÍSICA';
						$lcEvolucion = trim($laEv['DESEVL'],' ');
						$lcEvolucion = trim(str_replace('REALIZA ACTIVIDAD FÍSICA', ' ', $lcEvolucion));
						break;

					default:
						$lcDsc = rtrim($laEv['DESEVL'], ' ');
						$lcEvolucion .= mb_strlen($lcDsc)>$this->nSinEsp ? $laEv['DESEVL'] : $lcDsc. PHP_EOL;

				}
			}
		}
		if (!empty($lcEvolucion)) {
			$this->aTr[] = ['titulo3', $lcTitulo];
			$this->aTr[] = ['texto9', trim($lcEvolucion,'')];
		}
		return '';
	}

	private function omProcedimientos($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';
		$laPros = $this->oDb
			->select('O.CUPPRO,O.CORPRO,O.CONPRO,O.CNLPRO,O.DESPRO,O.FECPRO,O.HORPRO,IFNULL(C.DESCUP,\'\') DESCUP')
			->from('ORDPRO AS O')
			->leftJoin('RIACUP AS C', 'O.CUPPRO=C.CODCUP', null)
			->where(['O.INGPRO'=>$tnIngreso, 'O.CONPRO'=>$tnConsEvo,])
			->orderBy('O.CORPRO,O.CNLPRO')
			->getAll('array');
		if(is_array($laPros)){ if(count($laPros)>0){
			$lnNumEvo=$lnNumCit=-1;
			$lnNumPro=0;
			foreach($laPros as $laPro){
				$lcDesc = $laPro['DESPRO'];
				if ($lnNumEvo==$laPro['CONPRO'] && $lnNumCit==$laPro['CORPRO']) {
					$lcReturn .= trim($lcDesc, '');
				} else {
					// Inserta Procedimientos
					if ($laPro['CNLPRO']== 1) {
						$lnNumPro++;
						$lcDscCup = mb_substr(trim($laPro['DESCUP']),0,50);
						$lcFechaHora = 'Fecha: '.AplicacionFunciones::formatFechaHora('fecha',$laPro['FECPRO'])
									. ' Hora: '.AplicacionFunciones::formatFechaHora('hora',$laPro['HORPRO']);
						$lcReturn = (empty($lcReturn)?'':trim($lcReturn,' ').PHP_EOL) . $lnNumPro.') '.$lcDscCup.'. '.$lcFechaHora. PHP_EOL;
					}
					$lcReturn .= $lcDesc;

					// Justificaciones NOPOS de Procedimientos			-- FALTA --

				}
			}
			$lcReturn = trim($lcReturn);
		}}
		return $lcReturn;
	}

	private function omMedicamentos($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';
		$laMeds = $this->oDb
			->select('F.MEDFMD, IFNULL(I.DESDES,\'\') DESDES, F.DOSFMD, F.DDOFMD, F.FREFMD,F.DFRFMD, F.VIAFMD, F.OBIFMD, F.OBSFMD, F.ESTFMD')
			->select('IFNULL(D.DE1TMA,\'\') AS DESDOS, IFNULL(R.DE1TMA,\'\') AS DESFRE, IFNULL(V.DE1TMA,\'\') AS DESVAD')
			->from('FORMED AS F')
			->leftJoin('INVDES AS I', 'F.MEDFMD=I.REFDES', null)
			->leftJoin('TABMAE AS D', 'D.TIPTMA=\'MEDDOS\' AND F.DDOFMD=D.CL2TMA', null)
			->leftJoin('TABMAE AS R', 'R.TIPTMA=\'MEDFRE\' AND F.DFRFMD=R.CL2TMA', null)
			->leftJoin('TABMAE AS V', 'V.TIPTMA=\'MEDVAD\' AND F.VIAFMD=V.CL1TMA', null)
			->where(['F.NINFMD'=>$tnIngreso, 'F.CEVFMD'=>$tnConsEvo,])
			->getAll('array');
		if(is_array($laMeds)){ if(count($laMeds)>0){
			$lnNumMed=$lnNumSus=0;
			$lcMedSus='';
			foreach($laMeds as $lnKey=>$laMed){
				$laMeds[$lnKey]=array_map('trim',$laMed);
			}
			foreach($laMeds as $laMed){
				$lcDscMed = mb_substr($laMed['DESDES'],0,60);

				// Medicamentos suspendidos
				if ($laMed['ESTFMD']=='14') {
					$lnNumSus++;
					$lcMedSus.=$lnNumSus.') '.$lcDscMed.PHP_EOL;

				// Medicamentos formulados
				} else {
					$lnNumMed++;
					$lcDosis=$laMed['DOSFMD'];
					$lcDscDosis=$laMed['DESDOS'];
					$lcFrec=$laMed['FREFMD'];
					$lcDscFrec=$laMed['DESFRE'];
					$lcViaAdm=$laMed['DESVAD'];
					$lcObsv=trim($laMed['OBSFMD']);

					//	Descripción Medicamento
					$lcReturn.=$lnNumMed.') '.$lcDscMed.' '.number_format($lcDosis,2,',','.')
							.' '.$lcDscDosis.(in_array($laMed['DFRFMD'],[10,19])? ' ': ' CADA '.number_format($lcFrec,2,',','.').' ').$lcDscFrec
							.' '.$lcViaAdm.'. '.$lcObsv.PHP_EOL
							.($laMed['ESTFMD']=='12'? 'INMEDIATO'.(empty($laMed['OBIFMD'])? '': ': '.$laMed['OBIFMD']).PHP_EOL: ''); // Justificación Inmediato
				}
			}
			$lcReturn .= ( empty($lcMedSus)? '': '  *** MEDICAMENTOS SUSPENDIDOS ***'. PHP_EOL .$lcMedSus );
		}}
		return $lcReturn;
	}

	private function omInterconsultas($tnIngreso, $tnConsEvo)
	{
		$lcReturn=$lcPrioridad='';
		$laInts = $this->oDb
			->select('O.CODORD,O.CCIORD,IFNULL(E.DESESP,\'\') DESESP')
			->from('RIAORDL24 AS O')
			->leftJoin('RIAESPE AS E', 'O.CODORD=E.CODESP', null)
			->where(['O.NINORD'=>$tnIngreso, 'O.EVOORD'=>$tnConsEvo,])
			->like('O.COAORD','8904%')
			->getAll('array');
		if(is_array($laInts)){ if(count($laInts)>0){
			$lnNumEvo=$lnNumCit=-1;
			$lnNumPro=0;
			foreach($laInts as $laInt){
				$lcTemp='';
				$laInt=array_map('trim',$laInt);
				$lcReturn .= empty($laInt['DESESP'])? '': 'INTERCONSULTA DE '.$laInt['DESESP'].PHP_EOL;
				$laTxts = $this->oDb
					->select('CNLINT,DESINT')
					->from('INTCON')
					->where(['INGINT'=>$tnIngreso, 'CONINT'=>$tnConsEvo, 'CORINT'=>$laInt['CCIORD'], 'SORINT'=>'S',])
					->getAll('array');
				if(is_array($laTxts)){ if(count($laTxts)>0){
					foreach($laTxts as $laTxt){
						if($laTxt['CNLINT']==600){
							$lcPrioridad=!empty(trim($laTxt['DESINT']))?'Prioridad: '. ($this->aPrioridadesIntCon[trim($laTxt['DESINT'])]??''):'';
						} else {
							$lcTemp.=trim($laTxt['DESINT']);
						}
					}
				}}
				$lcReturn.=$lcTemp.PHP_EOL.(!empty($lcPrioridad)?($lcPrioridad.PHP_EOL.PHP_EOL):PHP_EOL);
			}
			$lcReturn = trim($lcReturn);
		}}
		return $lcReturn;
	}

	private function omDietas($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';
		$laDietas = $this->oDb
			->select('CNLEVL,DESEVL')
			->from('EVOLUCO')
			->where(['NINEVL'=>$tnIngreso, 'CONEVL'=>$tnConsEvo, 'CCIEVL'=>$tnConsEvo])
			->between('CNLEVL',1700,1749)
			->getAll('array');
		if(is_array($laDietas)){ if(count($laDietas)>0){
			foreach($laDietas as $laDieta){
				$lcReturn.=( $laDieta['CNLEVL']==1700 ? trim(mb_substr($laDieta['DESEVL'],8,212),' ') .PHP_EOL : trim($laDieta['DESEVL'],' ') );
			}
		}}
		return $lcReturn;
	}

	private function omOxigeno($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';
		$laOxis = $this->oDb
			->select('DESEVL')
			->from('EVOLUCO')
			->where(['NINEVL'=>$tnIngreso, 'CONEVL'=>$tnConsEvo, 'CCIEVL'=>$tnConsEvo])
			->between('CNLEVL',11501,11510)
			->getAll('array');
		if(is_array($laOxis)){ if(count($laOxis)>0){
			foreach($laOxis as $laOxi){
				$lcReturn.=$laOxi['DESEVL'];
			}
		}}
		return trim($lcReturn,' ');
	}

	private function omOrdenesEnfermeria($tnIngreso, $tnConsEvo, $tcOxigeno)
	{
		$lcReturn = '';
		$laOxis = $this->oDb
			->select('DESEVL')
			->from('EVOLUCO')
			->where(['NINEVL'=>$tnIngreso, 'CONEVL'=>$tnConsEvo, 'CCIEVL'=>$tnConsEvo])
			->between('CNLEVL',1751,1799)
			->getAll('array');
		if(is_array($laOxis)){ if(count($laOxis)>0){
			foreach($laOxis as $laOxi){
				$lcReturn.=trim($laOxi['DESEVL'],' ');
			}
		}}
		$lcReturn=(empty($lcReturn)? '': $lcReturn.PHP_EOL).(empty($tcOxigeno)? '': $tcOxigeno.PHP_EOL).PHP_EOL;
		return $lcReturn;
	}

	/* SOLICITUD COMPONENTES SANGUINEOS - DATOS DE LA TRANSFUSIÓN */
	private function HemocomponentesTrasfusion($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';

		// SOLICITUD
		$laOrdenS = $this->oDb
			->select('INDBSO,LINBSO,DESBSO,FECBSO,HORBSO')
			->from('BANSAO')
			->where(['INGBSO'=>$tnIngreso, 'EVOBSO'=>$tnConsEvo, ])
			->getAll('array');
		if(is_array($laOrdenS)){ if(count($laOrdenS)>0){
			foreach($laOrdenS as $laOrden){
				$lnIndice=intval(trim($laOrden['INDBSO']));
				switch($lnIndice){

					case 1:
						$lcCodTipoRes=trim(mb_substr($laOrden['DESBSO'], 0, 2),' ');
						if (empty($lcCodTipoRes)){
							$lcTipoReserva='';
						} else {
							$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANSANR', "CL1TMA='TIPORES' AND CL2TMA='$lcCodTipoRes'");
							$lcTipoReserva=trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ');
						}
						$lcFecha				= trim(mb_substr($laOrden['DESBSO'], 30, 8));
						$lcHemoclasificacion	= trim(mb_substr($laOrden['DESBSO'], 15, 6),' ');
						$lcFechaProcedimiento	= empty($lcFecha) ? '' : AplicacionFunciones::formatFechaHora('fecha', $lcFecha);
						$lcHb 					= intval(mb_substr($laOrden['DESBSO'], 45, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 45, 15));
						$lcHematocrito			= intval(mb_substr($laOrden['DESBSO'], 60, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 60, 15));
						$lcPlaquetas			= intval(mb_substr($laOrden['DESBSO'], 75, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 75, 15));
						$lcInr					= intval(mb_substr($laOrden['DESBSO'], 90, 15)) ==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 90, 15));
						$lcPt					= intval(mb_substr($laOrden['DESBSO'], 105, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 105, 15));
						$lcPtt					= intval(mb_substr($laOrden['DESBSO'], 120, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 120, 15));
						$lcFibronogeno			= intval(mb_substr($laOrden['DESBSO'], 135, 15))==0 ? '' : trim(mb_substr($laOrden['DESBSO'], 135, 15));
						$lcCodDx				= trim(mb_substr($laOrden['DESBSO'], 150, 5));
						$lcDiagnostico			= empty($lcCodDx)? '' : 'Diagnóstico: '.$lcCodDx.' - '.(new Diagnostico($lcCodDx, $laOrden['FECBSO']))->getTexto();
						$lcRiesgoTransfusional	= trim(mb_substr($laOrden['DESBSO'], 160, 2),' ');
						$lcCodReqFiltro			= trim(mb_substr($laOrden['DESBSO'], 170, 1),' ');
						$lcRequierefiltro		= $lcCodReqFiltro=='S'? 'SI': ( $lcCodReqFiltro=='N'? 'NO': '' );

						$lcReturn.=( empty($lcTipoReserva)? '': AplicacionFunciones::mb_str_pad('Tipo reserva: '.$lcTipoReserva, 40) )
								. ( empty($lcFechaProcedimiento)? '': AplicacionFunciones::mb_str_pad('Fecha estimada procedimiento: '.$lcFechaProcedimiento, 45) ).PHP_EOL
								. ( empty($lcHemoclasificacion)? '': 'Hemoclasificación: '.$lcHemoclasificacion.PHP_EOL )
								. ( empty($lcHb)? '': AplicacionFunciones::mb_str_pad('Hb(g/dl): '.$lcHb,40) )
								. ( empty($lcHematocrito)? '': AplicacionFunciones::mb_str_pad('Hematocrito: '.$lcHematocrito,30).PHP_EOL )
								. ( empty($lcPlaquetas)? '': AplicacionFunciones::mb_str_pad('Plaquetas(mm3): '.$lcPlaquetas,40) )
								. ( empty($lcInr)? '': AplicacionFunciones::mb_str_pad('INR: '.$lcInr,30).PHP_EOL )
								. ( empty($lcPt)? '': AplicacionFunciones::mb_str_pad('PT: '.$lcPt,40) )
								. ( empty($lcPtt)? '': AplicacionFunciones::mb_str_pad('PTT: '.$lcPtt,30).PHP_EOL )
								. ( empty($lcFibronogeno)? '': 'Fibronógeno: '.$lcFibronogeno.PHP_EOL )
								. ( empty($lcRiesgoTransfusional)? '': AplicacionFunciones::mb_str_pad('Riesgo transfusional: '.$lcRiesgoTransfusional,40) )
								. ( empty($lcRequierefiltro)? '': AplicacionFunciones::mb_str_pad('Requiere filtro: '.$lcRequierefiltro,30) .PHP_EOL )
								. ( empty($lcDiagnostico)? '': trim($lcDiagnostico,' ') );
						break;

					case 2:
						if(trim($laOrden['LINBSO'])==1){
							$lcReturn.= PHP_EOL .'Procedimiento a realizar: '.trim($laOrden['DESBSO'],' ').PHP_EOL;
						} else {
							$lcReturn.= trim($laOrden['DESBSO'],' ').PHP_EOL;
						}
						break;
				}
			}
		}}
		return trim($lcReturn,' ');
	}

	/* SOLICITUD COMPONENTES SANGUINEOS - PROCEDIMIENTOS */
	private function HemocomponentesProcedimientos($tnIngreso, $tnConsEvo)
	{
		$lcReturn = '';

		// GRUPO JUSTIFICACION
		$laJusts = $this->oDb
			->distinct()->select('JUSBSO, TJUBSO')
			->from('BANSAO')
			->where(['INGBSO'=>$tnIngreso, 'EVOBSO'=>$tnConsEvo, ])->where('JUSBSO','<>','')
			->orderBy('JUSBSO, TJUBSO')
			->getAll('array');
		if(is_array($laJusts)){ if(count($laJusts)>0){
			foreach($laJusts as $laJust){
				$lcCupsBancoSangre=$lcDescripcionComponente=$lcJustificacionFin=$lcDescripJustificacion=$lcDescJustificacion='';
				$lcJustificacion= trim($laJust['JUSBSO'],' ');
				$lcCodigoJustif	= trim($laJust['TJUBSO'],' ');

				$laHemos = $this->oDb
					->distinct()->select([
						'B.CL4TMA GRUPO',
						'SUBSTR(A.DESBSC, 1, 10) AS COD_CUPS',
						'SUBSTR(C.DESCUP, 1, 70) AS DES_CUPS',
						'SUBSTR(B.OP5TMA, 1, 30) AS DES_GRUPO',
						])
					->from('BANSAC A')
					->leftJoin('TABMAE B', 'SUBSTR(A.DESBSC, 1, 10)=B.CL2TMA AND B.TIPTMA=\'BANSAN\' AND B.CL1TMA=\'APLICA\'', null)
					->leftJoin('RIACUP C', 'SUBSTR(A.DESBSC, 1, 10)=C.CODCUP', null)
					->where(['A.INGBSC'=>$tnIngreso, 'A.EVOBSC'=>$tnConsEvo, 'A.INDBSC'=>1, 'B.CL4TMA'=>$lcJustificacion,])
					->orderBy('B.CL4TMA, SUBSTR(A.DESBSC,1,10), SUBSTR(C.DESCUP,1,70), SUBSTR(B.OP5TMA,1,30)')
					->getAll('array');
				if(is_array($laHemos)){ if(count($laHemos)>0){
					$lcDescripcionComponente = trim($laHemos[0]['DES_GRUPO'],' ');
					foreach($laHemos as $laHemo){
						$lcCupsBancoSangre.= trim($laHemo['COD_CUPS'],' ').' - '.trim($laHemo['DES_CUPS'],' ').PHP_EOL;
					}
				}}

				$laHemos = $this->oDb
					->select('*')
					->from('BANSAO')
					->where(['INGBSO'=>$tnIngreso, 'EVOBSO'=>$tnConsEvo, 'JUSBSO'=>$lcJustificacion, 'INDBSO'=>3,])
					->getAll('array');
				if(is_array($laHemos)){ if(count($laHemos)>0){
					foreach($laHemos as $laHemo){
						$lcDsc=trim($laHemo['DESBSO'],' ');
						if(!empty($lcDsc)) $lcDescJustificacion.= $lcDsc.PHP_EOL;
					}
				}}
				if(empty('$lcCodigoJustif')){
					$lcJustificacionFin='';
				} else {
					$oTabmae=$this->oDb->obtenerTabMae('DE2TMA', 'BANSAN', "CL1TMA='JUSTIF' AND CL3TMA='$lcCodigoJustif' AND CL4TMA='$lcJustificacion'");
					$lcJustificacionFin='Justificación: '.trim(AplicacionFunciones::getValue($oTabmae,'DE2TMA',''),' ').PHP_EOL;
				}
				$lcJustificacionFin.=$lcDescJustificacion;
				$lcReturn.= $lcDescripcionComponente. PHP_EOL .$lcCupsBancoSangre.$lcJustificacionFin;
			}
		}}
		return trim($lcReturn,' ');
	}

	private function OtrasEvoluciones($taEvol)
	{
		$lcEvolucion = '';
		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']!==1) {
				if ( mb_strpos(mb_strtoupper($laEv['DESEVL']), 'DR.')===false ){
					$lcDsc = rtrim($laEv['DESEVL'], ' ');
					$lcEvolucion .= mb_strlen($lcDsc)>$this->nSinEsp ? $laEv['DESEVL'] : $lcDsc;
				}
			}
		}
		return trim($lcEvolucion,' ');
	}

	private function TamizajeNutricional($taEvol)
	{
		$lcEvolucion = '';
		foreach ($taEvol as $laEv) {
			if ($laEv['CNLEVL']>1) {
				if ( mb_strpos($laEv['DESEVL'], 'DR.')===false ){
					$lcEvolucion.=$laEv['DESEVL'];
				}
			}
		}
		return trim($lcEvolucion,' ');
	}

	/* PARA CONSULTA DENTRO DE FORMULARIO DE EVOLUCIONES */
	private function RespuestaInterconsulta($taEvol, $tcDatosEnc, $tcNombreMedico)
	{
		$lcEvolucion=$lcFechaHora=$lcMedicoSol=$lcEspInterc=$lcEspSolicita=$lcPrioridad=$lcTipoSolic='';
		$laTipoSolic=['O'=>'opinión','M'=>'manejo conjunto','T'=>'traslado',];
		$lcTipoSolic='opinión';
		$llAnaEpi=false;
		$laDoc=[];
		$lnIndDoc=0;

		foreach($taEvol as $laEv){
			if($laEv['CNLEVL']>10000 && $laEv['CNLEVL']<50001){
				if(!mb_strpos(strtoupper($laEv['DESEVL']), 'DR.')===false && !mb_strpos($laEv['DESEVL'], ' - RME:')===false){
					continue;
				}

				if(empty($lcFechaHora)){
					$lcFechaHora=AplicacionFunciones::formatFechaHora('fecha',$laEv['FECEVL']).' a las '.AplicacionFunciones::formatFechaHora('hora',$laEv['HOREVL']);

					// Busca Médico que ordena y especialidad de la interconsulta solicitada
					$loSolic=$this->oDb
						->select('O.CCIORD, O.RMEORD,O.CODORD,EC.DESESP,MO.REGMED,MO.NNOMED,MO.NOMMED,EO.DESESP ESPSOL')
						->from('RIAORD O')
						->leftJoin('RIARGMN MO','O.RMEORD = MO.REGMED')
						->leftJoin('RIAESPE EC','O.CODORD = EC.CODESP')
						->leftJoin('RIAESPE EO','MO.CODRGM = EO.CODESP')
						->where([
							'O.NINORD'=>$laEv['NINEVL'],
							'O.CCIORD'=>$laEv['CCIEVL'],
						])
						->like('O.COAORD','8904%')
						->get('array');
					if(is_array($loSolic)){
						$lcMedicoSol=is_null($loSolic['REGMED'])? '': trim($loSolic['NOMMED']).' '.trim($loSolic['NNOMED']);
						$lcEspInterc=is_null($loSolic['DESESP'])? '': trim($loSolic['DESESP']);
						$lcEspSolicita=is_null($loSolic['ESPSOL'])? '': trim($loSolic['ESPSOL']);
					}

					// Busca Prioridad de Interconsulta
					$loSolic=$this->oDb->distinct()
						->select('DESINT')
						->from('INTCON')
						->where([
							'sorint'=>'S',
							'cnlint'=>600,
							'ingint'=>$laEv['NINEVL'],
							'corint'=>$laEv['CCIEVL'],
						])
						->getAll('array');
					if(is_array($loSolic)){
						$lcPrioridad=count($loSolic)>0? (trim($loSolic[0]['DESINT'])=='1'? 'URGENTE':'NO URGENTE'): '';
					}

					// Busca Tipo de Solicitud
					$loSolic=$this->oDb->distinct()
						->select('OTCINT,FECINT,HORINT')
						->from('INTCON')
						->where([
							'sorint'=>'R',
							'ingint'=>$laEv['NINEVL'],
							'corint'=>$laEv['CCIEVL'],
						])
						->getAll('array');
					if(is_array($loSolic)){
						$lcTipoSolic=$laTipoSolic[$loSolic[0]['OTCINT']]??$lcTipoSolic;
						$lcFechaHora=AplicacionFunciones::formatFechaHora('fecha',$loSolic[0]['FECINT']).' a las '.AplicacionFunciones::formatFechaHora('hora',$loSolic[0]['HORINT']);
					}
				}

				switch(true){

					case $laEv['CNLEVL']==10001:
						if($this->bEsConsulta){
							$laDoc[0]=[trim($laEv['DESEVL']), '', $tcDatosEnc];
						}
						break;

					case !mb_strpos($laEv['DESEVL'], 'SOLICITUD DE INTERCONSULTA')===false:
						$lcEvolucion = "El Dr. $lcMedicoSol solicita $lcTipoSolic a $lcEspInterc"
								. (empty($lcPrioridad)? '': ' - Prioridad: '.$lcPrioridad. PHP_EOL .'Respuesta generada el día '.$lcFechaHora);
						$laDoc[10]=['SOLICITUD DE INTERCONSULTA', '', $lcEvolucion];
						$laDoc[11]=['', 'COMENTARIO', ''];
						$lnIndDoc=11;
						break;

					case !mb_strpos($laEv['DESEVL'], 'RESPUESTA DE INTERCONSULTA')===false:
						$lcEvolucion='Dr. '.strtoupper($tcNombreMedico)." responde $lcTipoSolic a $lcEspSolicita";
						$laDoc[20]=['RESPUESTA DE INTERCONSULTA', '', $lcEvolucion];
						$laDoc[21]=['', 'COMENTARIO', ''];
						$lnIndDoc=21;
						break;

					case !(mb_strpos($laEv['DESEVL'], 'ANALISIS PARA EPICRISIS')===false):
						$lcSubTit='ANALISIS PARA EPICRISIS';
						$lcEvolucion=mb_substr($laEv['DESEVL'], mb_strpos($laEv['DESEVL'],$lcSubTit)+strlen($lcSubTit));
						$laDoc[30]=['', $lcSubTit, $lcEvolucion];
						$lnIndDoc=30;
						break;

					default:
						if($lnIndDoc>0) $laDoc[$lnIndDoc][2].=$laEv['DESEVL'];
				}
			}
		}
		foreach($laDoc as $laSub){
			if(!empty($laSub[0])) $this->aTr[]=['titulo2', $laSub[0]];
			if(!empty($laSub[1])) $this->aTr[]=['titulo3', $laSub[1]];
			if(!empty($laSub[2])) $this->aTr[]=['texto9', trim($laSub[2])];
		}

		return '';
	}

	private function DescripcionQuirurgica($taEvol)
	{
		$llAnaEpi=false;
		$laDoc=[];

		foreach($taEvol as $laEv){
			if($laEv['CNLEVL']>5000 && $laEv['CNLEVL']<=10000){

				$lcTrimEv=trim($laEv['DESEVL']);
				if(strlen($lcTrimEv)<$this->nSinEsp) $laEv['DESEVL']=$lcTrimEv. PHP_EOL;

				switch(true){

					case $laEv['CNLEVL']==5001:
						$laDoc[0]=[trim($laEv['DESEVL']), ''];
						break;

					case trim($laEv['DESEVL'])==='HALLAZGOS':
						$laDoc[20]=['HALLAZGOS', ''];
						$lnIndDoc=20;
						$llAnaEpi=true;
						break;

					case trim($laEv['DESEVL'])==='DESCRIPCION':
						$laDoc[30]=['DESCRIPCIÓN QUIRÚRGICA', ''];
						$lnIndDoc=30;
						$llAnaEpi=true;
						break;

					case trim($laEv['DESEVL'])==='ENVIO A PATOLOGIA':
						if($lnIndDoc>0) $laDoc[$lnIndDoc][1].='ENVÍO A PATOLOGÍA: ';
						break;

					case !$llAnaEpi:
						$lcEvolucion=trim(mb_substr($laEv['DESEVL'],0,60)). PHP_EOL
								.'Cirujano : '.trim(mb_substr($laEv['DESEVL'],60)). PHP_EOL . PHP_EOL;
						if(isset($laDoc[10])){
							$laDoc[10][1].=$lcEvolucion;
						}else{
							$laDoc[10]=['PROCEDIMIENTOS REALIZADOS',$lcEvolucion];
						}
						$lnIndDoc=10;
						break;

					default:
						if($lnIndDoc>0) $laDoc[$lnIndDoc][1].=$laEv['DESEVL'];
				}
			}
		}
		foreach($laDoc as $laSub){
			if(!empty($laSub[0])) $this->aTr[]=['titulo2', $laSub[0]];
			if(!empty($laSub[1])) $this->aTr[]=['texto9', trim($laSub[1])];
		}

		return '';
	}

	private function ProcNeuroradiologia($taEvol)
	{
		$llAnaEpi=false;
		$laDoc=[];

		foreach($taEvol as $laEv){
			if($laEv['CNLEVL']>5000 && $laEv['CNLEVL']<=10000){

				switch(true){

					case $laEv['CNLEVL']==5001:
						$laDoc[0]=[trim($laEv['DESEVL']), ''];
						break;

					case $laEv['CNLEVL']==5002:
						$laDoc[10]=['PROCEDIMIENTOS REALIZADOS',''];
						$lnIndDoc=10;
						break;

					case trim($laEv['DESEVL'])==='DESCRIPCION':
						$laDoc[20]=['DESCRIPCIÓN', ''];
						$lnIndDoc=20;
						$llAnaEpi=true;
						break;

					default:
						if($llAnaEpi){
							$laDoc[$lnIndDoc][1].=$laEv['DESEVL'];
						}else{
							$laDoc[$lnIndDoc][1].=trim($laEv['DESEVL']). PHP_EOL. PHP_EOL;
						}
				}
			}
		}
		foreach($laDoc as $laSub){
			if(!empty($laSub[0])) $this->aTr[]=['titulo2', $laSub[0]];
			if(!empty($laSub[1])) $this->aTr[]=['texto9', trim($laSub[1])];
		}

		return '';
	}

	private function JuntaMedica($taEvol)
	{
		$lcEvolucion='';
		$lnIndDoc=0;
		foreach($taEvol as $laEv){
			if($laEv['CNLEVL']<7000){

				$lcTrimEv=trim($laEv['DESEVL']);
				if(strlen($lcTrimEv)<$this->nSinEsp) $laEv['DESEVL']=$lcTrimEv. PHP_EOL;

				switch(true){

					case $laEv['CNLEVL']==1:
						$laDoc[0]=[trim($laEv['DESEVL']), ''];
						break;

					case trim($laEv['DESEVL'])==='PARTICIPANTES':
						$laDoc[10]=['PARTICIPANTES',''];
						$lnIndDoc=10;
						break;

					case trim($laEv['DESEVL'])==='MOTIVO JUNTA':
						$laDoc[20]=['MOTIVO JUNTA', ''];
						$lnIndDoc=20;
						break;

					case trim($laEv['DESEVL'])==='DISCUSION DEL CASO CLINICO':
						$laDoc[30]=['DISCUSION DEL CASO CLINICO', ''];
						$lnIndDoc=30;
						break;

					case trim($laEv['DESEVL'])==='CONCLUSIONES':
						$laDoc[40]=['CONCLUSIONES', ''];
						$lnIndDoc=40;
						break;

					default:
						if($lnIndDoc>0){
							$lcDeme=strtoupper(trim(mb_substr($laEv['DESEVL'],1,3)));
							if($lcDeme!=='DR.'){
								$laDoc[$lnIndDoc][1].=$laEv['DESEVL'];
							}
						}
				}
			}
		}
		foreach($laDoc as $laSub){
			if(!empty($laSub[0])) $this->aTr[]=['titulo2', $laSub[0]];
			if(!empty($laSub[1])) $this->aTr[]=['texto9', trim($laSub[1])];
		}

		$this->aReporte['cTitulo'] = $this->aTitulos['RIA050'];
		return trim($lcEvolucion);
	}

	//	Array de títulos
	private function titulosDoc()
	{
		return [
			'DEFAULT'	=> 'EVOLUCIÓN',
			'EV0015'	=> 'EVENTUALIDAD',
			'EVOEVWEB'	=> 'EVENTUALIDAD',
			'EV0017'	=> 'EVOLUCIÓN',
			'EVOPIWEB'	=> 'EVOLUCIÓN',
			'EVOURWEB'	=> 'EVOLUCIÓN',
			'EV0019'	=> 'EVOLUCIÓN',
			'EVOUNWEB'	=> 'EVOLUCIÓN',
			'EV0022AN'	=> 'JUSTIFICACIÓN DE PRESTACIÓN NO POS',
			'HIS001'	=> 'INTERCONSULTA MÉDICA',
			'HISINTWEB'	=> 'INTERCONSULTA MÉDICA',
			'RIA133'	=> 'DESCRIPCION QUIRÚRGICA (EVOLUCIÓN)',
			'RIA133E'	=> 'PROCEDIMIENTOS NEURORADIOLOGÍA (EVOLUCIÓN)',
			'RIA050'	=> 'JUNTA MÉDICA (EVOLUCIÓN)',
			'EV0023AN'	=> 'SOLICITUD DE ORDENES MÉDICAS',
			'EV0023'	=> 'SOLICITUD DE ORDENES MÉDICAS',
			'ORDMEDWEB'	=> 'SOLICITUD DE ORDENES MÉDICAS',
			'EXA001'	=> 'INTERPRETACIÓN EXÁMENES',
			'NUT010'	=> 'TAMIZAJE NUTRICIONAL',
		];
	}


	function Texto_Pandemia($taEvol)
	{
		$lcTextoPandemia = '';
		if(is_array($taEvol)){
			foreach ($taEvol as $laEv) {
				if ($laEv['CNLEVL']>=90000 && $laEv['CNLEVL']<90100) {
					$lcTextoPandemia .= $laEv['DESEVL'];
				}
			}
		}
		$lcTextoPandemia = trim($lcTextoPandemia);
		$lcTextoPandemia .= !empty($lcTextoPandemia)? "\n"."\n" : '' ;
		return $lcTextoPandemia;
	}


	function Consulta_Evoluciones($tnIngreso)
	{
		$laDatos = $this->oDb
			->select('SUBSTR(TABDSC, 1, 3) AS CONTODO')
			->from('PRMTAB')
			->where(['TABTIP'=>'EVC','TABCOD'=>'EV0050',])
			->get('array');
		if ($this->oDb->numRows>0) {
			$lbTodo = $laDatos['CONTODO']=='.T.';
		}

		if	(!$lbTodo) {
			$laDatos = $this->oDb
				->select('CONEVL, CNLEVL, DESEVL')
				->from('EVOLUCL2')
				->where("NINEVL=$tnIngreso AND CNLEVL<>900010 AND NOT (CNLEVL BETWEEN 8000 AND 8999)")
				->orderBy('CONEVL DESC, CNLEVL ASC')
				->getAll('array');
			if ($this->oDb->numRows>0) {
				$lnCnsEvo = $laDatos[0]['CONEVL'];
				$laEvol = [];
				foreach($laDatos as $laDato) {
					switch(true) {
						case $laDato['CNLEVL']=='1':
							$laEvol[$laDato['CONEVL']] = [
								'ttl'=>trim($laDato['DESEVL']),
								'dsc'=>'',
								'pan'=>'',
							];
							break;
						case $laDato['CNLEVL']>=90000 && $laDato['CNLEVL']<=90100:
							$laEvol[$laDato['CONEVL']]['pan'] .= $laDato['DESEVL'];
							break;
						default:
							$laEvol[$laDato['CONEVL']]['dsc'] .= $laDato['DESEVL'];
					}
				}
				unset($laDatos);
			}
		}
	}

}
