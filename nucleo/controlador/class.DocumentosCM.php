<?php
namespace NUCLEO;

require_once __DIR__ . '/class.ListaDocumentos.php';
require_once __DIR__ . '/class.Documento.php';
require_once __DIR__ . '/class.PdfHC.php';
require_once __DIR__ . '/class.SmbClient.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

use NUCLEO\ListaDocumentos;
use NUCLEO\Documento;
use NUCLEO\PdfHC;
use NUCLEO\SmbClient;



class DocumentosCM
{
	private $nIngreso = 0;
	private $cTipId = '';
	private $nNumId = 0;
	private $cViaIng = '';
	private $cTipoSoporte = '';

	private $aData = [];
	private $oLista = null;

	private $bIncluirLaboratorios = true;
	private $bIncluirAdjExtraInst = true;

	private $aEstado = [
		'PorGenerar'		=>'00',
		'Generado'			=>'GN',
		'GenerandoSoporte'	=>'GS',
		'Error'				=>'ER',
		'SinSoporte'		=>'NO',
	];
	private $aCarpetaVia = [
		'01' => 'Urgencias',
		'02' => 'ConsultaExterna',
		'04' => 'Hospitalizacion',
		'05' => 'Hospitalizacion',
		'06' => 'CirugiaAmbulatoria',
	];

	public $aServidor = [];


	public function __construct($tcTipoSop='')
	{
		if (!empty($tcTipoSop)) {
			$this->estableceTipoSop($tcTipoSop);
		}
	}


	public function estableceTipoSop($tcTipoSop, $lbSoloTipos=false)
	{
		$this->cTipoSoporte = $tcTipoSop;
		$this->aData = $this->inicioTiposSoporte($lbSoloTipos);
	}


	/*
	 *	Inicializa propiedades
	 */
	public function iniciar($tnIngreso)
	{
		$this->nIngreso = $tnIngreso;

		$this->oLista = new ListaDocumentos();
		$this->oLista->cargarDatos($tnIngreso, '', 0, '', '', false, false);
		$this->oLista->obtenerVia($tnIngreso);
		$this->oLista->obtenerHabitaciones($tnIngreso);
		$this->cTipId = $this->oLista->cTipoId();
		$this->nNumId = $this->oLista->nNumeroId();

		// Obtener la última vía del paciente
		$laViasIng = $this->oLista->getViaIngreso();
		$lnNumVias = count($laViasIng);
		if ($lnNumVias > 0) {
			$this->cViaIng = $laViasIng[$lnNumVias-1]['VIA'] ?? '01';
		} else {
			$this->cViaIng = '01';
		}
	}


	/*
	 *	Generar todos los documentos de un ingreso
	 */
	public function generarDocumentosIngreso($tnIngreso, $taSoportes=[])
	{
		global $goDb;
		$this->iniciar($tnIngreso);
		$laRta = ['error'=>[]];

		if (is_array($taSoportes) && count($taSoportes)>0) {
			$laDocGenerar = $taSoportes;
		} else {
			$laDocGenerar = explode(',', $goDb->obtenerTabMae1('TRIM(DE2TMA || OP5TMA)', 'CMSOPORT', "CL1TMA='{$this->cTipoSoporte}' AND CL2TMA='GENERAR' AND ESTTMA=''", NULL, 'DTC,AUT,EPI,EDX,DQX,RAN,CRC,HTL,OMD,HAU,HAM,RPC,HCL,LAB,COM,SOA,OTR,COV'));
		}

		foreach ($laDocGenerar as $lcTipoSoporte) {
			$laRtaSop = $this->generarDoc($lcTipoSoporte);
			if(isset($laRtaSop['error'])) $laRta['error'][] = $laRtaSop['error'];
			if(isset($laRtaSop['msj'])) $laRta['msj'][] = $laRtaSop['msj'];
		}

		return $laRta;
	}


	/*
	 *	Genera el documento del tipo de soporte indicado
	 */
	private function generarDoc($tcTipoSoporte)
	{
		ini_set('max_execution_time', 60*120);

		global $goDb;
		$laDocs = $laDocLab = [];
		$laRta = ['error'=>'No se pudo finalizar'];
		$lcArchivo = "{$tcTipoSoporte}_{$this->nIngreso}.pdf";
		$lnPaso = 0;
		$lbGenerarLab = false;

		// Obtener lista documentos
		foreach ($this->aData[$tcTipoSoporte]['FNDOCS'] as $lnCodFnDoc => $lcFuncionDoc) {
			if (!empty($lcFuncionDoc)) {
				$laParam = [$this->nIngreso];
				if (isset($this->aData[$tcTipoSoporte]['FNCOND'][$lnCodFnDoc])) {
					$laParam[] = $this->aData[$tcTipoSoporte]['FNCOND'][$lnCodFnDoc];
				}
				call_user_func_array([$this->oLista, "consultar{$lcFuncionDoc}"], $laParam);
			}
		}

		// Obtener lista adjuntos
		if (count($this->aData[$tcTipoSoporte]['ADJUNTOS'])>0) {
			$lcListaAdj = "'".implode("','", $this->aData[$tcTipoSoporte]['ADJUNTOS'])."'";
			$this->oLista->consultarAdjuntos($this->nIngreso, "T.CL2TMA IN ({$lcListaAdj})");
		}

		$laListaDocs = $this->oLista->obtenerDocumentos();

		foreach ($laListaDocs[$this->nIngreso]??[] as $laDoc) {

			// valida si es documento, laboratorio o adjunto
			$lcTipoPrg = $laDoc['tipoPrg'] ?? '';
			$lcTipoDoc = $laDoc['tipoDoc'] ?? '';
			$lbEsAdjunto = $lcTipoPrg == 'ADJUNTOS';

			if (!$this->bIncluirAdjExtraInst) {
				if ($lbEsAdjunto && $lcTipoDoc=='9600') continue;
			}
			if (!$this->bIncluirLaboratorios) {
				if ($lbEsAdjunto && $lcTipoDoc=='1100') continue;
			}

			if ($lcTipoDoc=='1100') {
				if ($lbGenerarLab==false) {
					$lbGenerarLab = true;

					$laDocLab = [
						'nIngreso'		=> $this->nIngreso,
						'cTipDocPac'	=> $this->cTipId,
						'nNumDocPac'	=> $this->nNumId,
						'cTipoDocum'	=> $lcTipoDoc,
						'cTipoProgr'	=> $lcTipoPrg,
					];
					$laDocs[] = $laDocLab;
				}

			} else {
				// Genera propiedades del documento
				$lcCup = $lbEsAdjunto ? $laDoc['fecha'] . ' - ' . $laDoc['descrip'] : ($laDoc['codCup'] ?? '');
				$laDocs[] = [
					'nIngreso'		=> $this->nIngreso,
					'cTipDocPac'	=> $this->cTipId,
					'nNumDocPac'	=> $this->nNumId,
					'cRegMedico'	=> $laDoc['medRegMd'] ?? '',
					'cTipoDocum'	=> $lcTipoDoc,
					'cTipoProgr'	=> $lcTipoPrg,
					'tFechaHora'	=> AplicacionFunciones::formatFechaHora('fechahora', $laDoc['fecha']??'0'),
					'nConsecCita'	=> $laDoc['cnsCita'] ?? '0',
					'nConsecCons'	=> $laDoc['cnsCons'] ?? '0',
					'nConsecEvol'	=> $laDoc['cnsEvo'] ?? '0',
					'nConsecDoc'	=> $laDoc['cnsDoc'] ?? '0',
					'cCUP'			=> $lcCup,
					'cCodVia'		=> $laDoc['codvia'] ?? '',
					'cSecHab'		=> $laDoc['sechab'] ?? '',
				];
			}
		}
		$this->oLista->limpiarDocumentos();

		// Factura de pago compartido
		if ($tcTipoSoporte=='RPC') {
			$laFactPagoComp = $this->obtenerFacPagoCompartido();
			if (isset($laFactPagoComp['urlFact']) && !empty($laFactPagoComp['urlFact'])) {
				$laDocs[] = [
					'nIngreso'		=> $this->nIngreso,
					'cTipDocPac'	=> $this->cTipId,
					'nNumDocPac'	=> $this->nNumId,
					'cTipoDocum'	=> 'urlfile',
					'cTipoProgr'	=> 'ADJUNTOS',
					'tFechaHora'	=> '9999-99-99 99:99:99',
					'cCUP'			=> '',
					'cUrlFile'		=> $laFactPagoComp['urlFact'],
				];
			}
		}

		$laRta['num'] = count($laDocs);
		if ($laRta['num'] == 0) {
			$laRta['error'] = ''; // No hay soportes que generar
			return $laRta;
		}

		$loDocument = new Documento();
		$lcSep = $goDb->soWindows ? '\\' : '/';
		$lcRutaTmp = sys_get_temp_dir() . $lcSep . $lcArchivo;

		try {
			$laPortada = [];
			$lcSalida='F';
			$lcPassword=null;
			$lbSinUsuario=true;
			$lcUsuario='';
			$loDocument->generarVariosPDF($laDocs, $laPortada, $lcRutaTmp, $lcSalida, $lcPassword, $lbSinUsuario, $lcUsuario, $this->bIncluirAdjExtraInst, $lbGenerarLab);

			if ($goDb->soWindows) {

				foreach ($this->aServidor as $lcClave => $laServidor) {
					$lcDestino = $laServidor['server']
						. (empty($laServidor['ruta']) ? '' : $lcSep . $laServidor['ruta'])
						. (empty($laServidor['carpeta']) ? '' : $lcSep . $laServidor['carpeta'])
						. $lcSep . $this->aCarpetaVia[$this->cViaIng]
						. $lcSep . $lcArchivo;
					if (copy($lcRutaTmp, $lcDestino)) {
						$lbExisteArchivo = file_exists($lcDestino);
						$laRta['msj'] = "Archivo {$lcArchivo} generado.";
						$laRta['error'] = '';
					}else{
						$laRta['error'] = "No se pudo copiar el archivo {$lcDestino} al servidor.";
					}
				}

			} else {

				foreach ($this->aServidor as $lcClave => $laServidor) {
					$lcDestino = $laServidor['ruta']
						. (empty($laServidor['carpeta']) ? '' : $lcSep . $laServidor['carpeta'])
						. $lcSep . $this->aCarpetaVia[$this->cViaIng]
						. $lcSep . $lcArchivo;
					$loSmbClient = new \SmbClient($laServidor['server'], $laServidor['user'], $laServidor['pass']);

					if ($loSmbClient->put($lcRutaTmp, $lcDestino)) {
						$laRta['msj'] = "Archivo {$lcArchivo} generado.";
						$laRta['error'] = '';
						$lbExisteArchivo = true;
					} else {
						$laRta['error'] = "No se pudo copiar el archivo {$laServidor['server']} / {$lcDestino}.";
					}
					$loSmbClient = null;
					unset($loSmbClient);
				}
			}
			$loPdf = null;
			unset($loPdf);
			// unlink($lcRutaTmp);

		} catch (\Throwable $loErrorT) {
			$laRta['error'] = ($lnPaso==1 ? 'Error al obtener documentos' : ($lnPaso==2 ? 'Error al obtener adjuntos' : "Error al generar el archivo {$lcDestino}")).' - Error: '.$loErrorT->getMessage();
		} catch( \Exception $loError ) {
			$laRta['error'] = ($lnPaso==1 ? 'Error al obtener documentos' : ($lnPaso==2 ? 'Error al obtener adjuntos' : "Error al generar el archivo {$lcDestino}")).' - Error: '.$loError->getMessage();
		}

		return $laRta;
	}


	public function obtenerFacPagoCompartido()
	{
		$laRta = [];
		global $goDb;
		$laQry = $goDb
			->select('CNSDFA CONSEC, NFADFA FACTURA, SUM(VPRDFA) VALOR')
			->from('FACDETF')
			->where([
				'INGDFA'=>$this->nIngreso,
				'TINDFA'=>'900'
			])
			->groupBy('CNSDFA, NFADFA')
			->having('SUM(VPRDFA)>0')
			->getAll('array');
		if ($goDb->numRows()>0) {
			$lnFacturaPC = $laQry[0]['FACTURA'];
			$laQry = $goDb->select('URLID')->from('FEMOV')->where("TIPR='06FA' AND FACT={$lnFacturaPC}")->get('array');
			if ($goDb->numRows()>0) {
				$laRta['urlFact'] = trim($laQry['URLID']);
			}
		}
		return $laRta;
	}


	public function inicioTiposSoporte($lbSoloTipos = false)
	{
		global $goDb;
		$laDatos = [];
		$laQry = $goDb
			->select('OP7TMA CODIGO, TRIM(CL3TMA) PREFIJO, TRIM(DE1TMA) TITULO, TRIM(DE2TMA||OP5TMA) FNDOCS, OP1TMA ACTIVO')
			->from('TABMAE')
			->where("TIPTMA='CMSOPORT' AND CL1TMA='{$this->cTipoSoporte}' AND CL2TMA='SOPORTE' AND ESTTMA=''")
			->orderBy('OP7TMA')
			->getAll('array');
		if ($goDb->numRows()>0) {
			foreach ($laQry as $laSop) {
				$laDatos[$laSop['PREFIJO']] = [
					// 'CODIGO'=>intval($laSop['CODIGO']),
					// 'PREFIJO'=>$laSop['PREFIJO'],
					'TITULO'=>$laSop['TITULO'],
					'ACTIVO'=>$laSop['ACTIVO']=='1',
					'FNDOCS'=>explode('|', $laSop['FNDOCS']),
					'FNCOND'=>[],
					'ADJUNTOS'=>[],
				];
			}
		}

		if ($lbSoloTipos==false) {
			// Condiciones para soportes
			$laQry = $goDb
				->select('OP7TMA CODIGO, TRIM(CL3TMA) PREFIJO, TRIM(CL4TMA) CODCON, OP3TMA NUMCON, TRIM(DE2TMA||OP5TMA) CONDICION')
				->from('TABMAE')
				->where("TIPTMA='CMSOPORT' AND CL1TMA='{$this->cTipoSoporte}' AND CL2TMA='CONDIC' AND ESTTMA=''")
				->orderBy('CL2TMA,CL3TMA,CL4TMA')
				->getAll('array');
			if ($goDb->numRows()>0) {
				foreach ($laQry as $laCond) {
					if (isset($laDatos[$laCond['PREFIJO']]['FNCOND'][$laCond['NUMCON']])) {
						$laDatos[$laCond['PREFIJO']]['FNCOND'][$laCond['NUMCON']] .= ' '.$laCond['CONDICION'];
					} else {
						$laDatos[$laCond['PREFIJO']]['FNCOND'][$laCond['NUMCON']] = $laCond['CONDICION'];
					}
				}
			}

			// Lista de Adjuntos para transfiriendo
			$laQry = $goDb
				->select('TRIM(OP2TMA) CODIGO, TRIM(CL2TMA) CODADJ')
				->from('TABMAE')
				->where("TIPTMA='HCADJUN' AND CL1TMA='TIPOS' AND ESTTMA=''")
				->getAll('array');
			if ($goDb->numRows()>0) {
				foreach ($laQry as $laAdj) {
					$laDatos[$laAdj['CODIGO']]['ADJUNTOS'][] = $laAdj['CODADJ'];
				}
			}

			$this->obtenerServidor($this->cTipoSoporte);
		}

		return $laDatos;
	}


	public function listaTipos()
	{
		global $goDb;
		$laCampo = ['TRIM(CL2TMA) CODIGO','TRIM(DE1TMA) TITULO'];
		$lcTiptma = 'CMSOPORT';
		$laWhere = ['CL1TMA'=>'TIP_SOP', 'ESTTMA'=>''];
		$lcOrder = 'DE1TMA';
		$lbVariasFilas = true;

		return $goDb->obtenerTabMae($laCampo, $lcTiptma, $laWhere, $lcOrder, $lbVariasFilas);
	}


	public function listaSoportes($tcTipoSoporte)
	{
		global $goDb;
		$laRta = [];
		$laDatos = $goDb->obtenerTabMae(
			['TRIM(CL3TMA) CODIGO','TRIM(DE1TMA) TITULO'],
			'CMSOPORT',
			['CL1TMA'=>$tcTipoSoporte, 'CL2TMA'=>'SOPORTE', 'OP1TMA'=>'1', 'ESTTMA'=>''],
			'OP7TMA',
			true
		);
		foreach ($laDatos as $laDato) {
			$laRta[$laDato->CODIGO] = $laDato->TITULO;
		}
		return $laRta;
	}


	public function listaEstados()
	{
		global $goDb;
		$laRta = [];
		$laDatos = $goDb->obtenerTabMae(
			['TRIM(CL2TMA) CODIGO','TRIM(DE1TMA) TITULO'],
			'CMSOPORT',
			['CL1TMA'=>'ESTADO', 'ESTTMA'=>''],
			'DE1TMA',
			true
		);
		foreach ($laDatos as $laDato) {
			$laRta[$laDato->CODIGO] = $laDato->TITULO;
		}
		return $laRta;
	}


	public function listaEntidades($tcTipoSoporte='TRANSFIR')
	{
		global $goDb;
		$laRta = [];
		$laDatos = $goDb->obtenerTabMae(
			['OP7TMA CODIGO','TRIM(DE1TMA) TITULO'],
			'CMSOPORT',
			"CL1TMA='{$tcTipoSoporte}' AND CL2TMA='NITCM' AND CL3TMA<>'' AND ESTTMA=''",
			'DE2TMA',
			true
		);
		foreach ($laDatos as $laDato) {
			$laRta[$laDato->CODIGO] = $laDato->TITULO;
		}
		return $laRta;
	}


	/*
	 ******************************************************************************************
	 *	FUNCIONES REFERENTES AL MANEJO DE ADMINISTRACIÓN DE LA GENERACIÓN DE SOPORTES
	 ******************************************************************************************
	 */




	/*
	 *	Consulta de generación de soportes
	 *	@param object $toFiltros: objeto con valores a usar para filtrar
	 *			ingreso		número de ingreso
	 *			via			vía de ingreso
	 *			estado		estado de los soportes (N=No Generados, G=Generados, T=Todos)
	 *			facturador	usuario facturador
	 *			fechatipo	tipo de fecha a usar (factura o soporte)
	 *			fechaini	fecha inicial
	 *			fechafin	fecha final
	 */
	public function consultaListaSoportes($toFiltros)
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		if (isset($toFiltros->ingreso) && strlen($toFiltros->ingreso)>0){
			$laIngresosTemp = array_map('trim', explode(',', $toFiltros->ingreso));
			$laIngresos = [];
			foreach ($laIngresosTemp as $lcIngreso) {
				$lnIngreso = intval($lcIngreso);
				if ($lnIngreso>999999 && $lnIngreso<10000000) {
					$laIngresos[] = $lnIngreso;
				}
			}
			if (count($laIngresos)>0) $goDb->in('S.INGCMS', $laIngresos);
		}
		if (isset($toFiltros->via) && strlen($toFiltros->via)>1){
			if (strlen($toFiltros->via)==2) {
				$goDb->where(['I.VIAING'=>$toFiltros->via]);
			} else {
				$goDb->in('I.VIAING', explode(',', $toFiltros->via));
			}
		}
		if (isset($toFiltros->estado)){
			switch ($toFiltros->estado) {
				case 'N':
					$goDb->in('S.ESTCMS', ['00','GS','ER']);
					break;
				case 'G':
					$goDb->in('S.ESTCMS', ['GN','NO']);
					break;
			}
		}
		if (isset($toFiltros->entidad) && is_numeric($toFiltros->entidad)){
			$goDb
				->where('(SELECT COUNT(*) FROM FACCABF WHERE INGCAB=S.INGCMS AND NITCAB=:csEntidad)>0')
				->addBindValue([':csEntidad'=>$toFiltros->entidad]);
		}
		if (isset($toFiltros->facturador) && strlen($toFiltros->facturador)==8){
			$goDb->where(['S.UFACMS'=>$toFiltros->facturador]);
		}
		if (is_numeric($toFiltros->fechaini) && is_numeric($toFiltros->fechafin)){
			$lcTipoFecha = $toFiltros->fechatipo ?? 'factura';
			$lcCampoFecha = $lcTipoFecha=='factura' ? 'S.FFACMS' : 'S.FGNCMS';
			$goDb->between($lcCampoFecha, $toFiltros->fechaini, $toFiltros->fechafin);
		}

		$lbMostrarEntidades = true;
		if ($lbMostrarEntidades) {
			$goDb->select(implode('', [
				"(SELECT LISTAGG(DISTINCT NITCAB || ' | ' || TRIM(TE1SOC), CHR(13)) WITHIN GROUP(ORDER BY TE1SOC) ",
				"FROM FACCABF INNER JOIN PRMTE1 ON DIGITS(NITCAB)=TE1COD ",
				"WHERE INGCAB=S.INGCMS AND MA1CAB<> 'A' ",
					"AND PLNCAB NOT IN (SELECT PLNCON FROM FACPLNC WHERE NI1CON=860006656)) ENTIDAD",
			]));
		}

		$laValida = $goDb
			->select('S.INGCMS INGRESO, TRIM(S.TIPCMS) TIPOSOP, TRIM(S.CDCCMS) SOPORTE, S.ESTCMS ESTADO')
			->select('S.FFACMS FECFACT, TRIM(S.UFACMS) FACTURADOR, S.FGNCMS FECSOP, S.HGNCMS HORASOP')
			->select("I.VIAING VIA, TRIM(P.NM1PAC)||' '||TRIM(P.NM2PAC)||' '||TRIM(P.AP1PAC)||' '||TRIM(P.AP2PAC) PACIENTE")
			->from('CMSPRT S')
			->innerJoin('RIAING I', 'S.INGCMS=I.NIGING')
			->innerJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC')
			->getAll('array');
		if ($goDb->numRows()>0) {
			$laReturn['lista'] = $laValida;

		} else {
			$laReturn['mensaje'] = 'No se encontraron soportes con las condiciones establecidas.';
		}

		return $laReturn;
	}


	/*
	 *	Inserta lista de soportes de un ingreso a la cola para generación
	 *	@param integer $tnIngreso: número de ingreso del paciente
	 *	@param string $tcTipoSoporte: tipo de plantilla de soportes
	 *	@param array $taSoportes: listado de soportes que se deben generar
	 *	@param array $taLog: datos de log (usuario, programa, fecha y hora), no obligatorio
	 */
	public function insertarGenerar($tnIngreso, $tcTipoSoporte, $taSoportes, $taLog=[])
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		$laValida = $goDb
			->select('INGCAB, FEFCAB, USRCAB')
			->from('FACCABF')
			->where([
				'INGCAB'=>$tnIngreso,
				'MA1CAB'=>'',	// facturas no anuladas
			])
			->where("NITCAB IN (SELECT OP7TMA FROM TABMAE WHERE TIPTMA='CMSOPORT' AND CL1TMA='{$tcTipoSoporte}' AND CL2TMA='NITCM' AND ESTTMA='')")
			->get('array');
		if ($goDb->numRows()==0) {
			$laReturn = ['error'=>['No se encontraron facturas activas para el ingreso.']];

		} else {
			$this->insertarSoportesGenerar($tnIngreso, $laValida['FEFCAB'], $laValida['USRCAB'], $tcTipoSoporte, $taSoportes, $taLog);
		}

		return $laReturn;
	}


	/*
	 *	Inserta lista de soportes de un ingreso a la cola para generación
	 *	@param integer $tnIngreso: número de ingreso del paciente
	 *	@param integer $tnFechaFactura: fecha de factura en formato AAAAMMDD
	 *	@param string $tcFacturador: código de usuario que hizo la factura
	 *	@param string $tcTipoSoporte: tipo de plantilla de soportes
	 *	@param array $taSoportes: listado de soportes que se deben generar
	 *	@param array $taLog: datos de log (usuario, programa, fecha y hora), no obligatorio
	 */
	public function insertarSoportesGenerar($tnIngreso, $tnFechaFactura, $tcFacturador, $tcTipoSoporte, $taSoportes, $taLog=[], $tbGenerarInmediato=false)
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		if (count($taSoportes)>0) {
			if (count($taLog)==0) {
				$taLog = $this->generarLog();
			}
			// Calcula el día que debe generar el sosporte
			if ($tbGenerarInmediato) {
				$lcFechaSoporte = $taLog['fecha'];
			} else {
				$lnDias = $goDb->obtenerTabMae1('OP3TMA', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='DIASPAUS' AND ESTTMA=''", null, 10);
				$ltFechaFactura = \DateTime::createFromFormat('Ymd', $tnFechaFactura);
				$lcFechaSoporte = ($ltFechaFactura->add(new \DateInterval("P{$lnDias}D")))->format('Ymd');
			}
			$lnSoportesEnLista = 0;
			foreach ($taSoportes as $lcSoporte) {
				$laValida = $goDb
					->select('INGCMS')
					->from('CMSPRT')
					->where([
						'INGCMS'=>$tnIngreso,
						'TIPCMS'=>$tcTipoSoporte,
						'CDCCMS'=>$lcSoporte,
					])
					->get('array');
				if ($goDb->numRows()==0) {
					$lbResult = $goDb
						->from('CMSPRT')
						->insertar([
							'INGCMS'=>$tnIngreso,
							'TIPCMS'=>$tcTipoSoporte,
							'CDCCMS'=>$lcSoporte,
							'ESTCMS'=>$this->aEstado['PorGenerar'],
							'FFACMS'=>$tnFechaFactura,
							'FGNCMS'=>$lcFechaSoporte,
							'UFACMS'=>$tcFacturador,
							'USCCMS'=>$taLog['usuario'],
							'PGCCMS'=>$taLog['programa'],
							'FECCMS'=>$taLog['fecha'],
							'HOCCMS'=>$taLog['hora'],
						]);
					if ($lbResult) {

					} else {
						$laReturn['error'][] = "$lcSoporte no se pudo adicionar a la lista";
					}
				} else {
					$lnSoportesEnLista++;
					$laReturn['error'][] = "$lcSoporte ya está en lista";
				}
			}
			if ($lnSoportesEnLista==count($taSoportes)) {
				$laReturn = ['error'=>['Todos los soportes ya están en lista']];
			}
		} else {
			$laReturn = ['error'=>['No hay soportes para generar']];
		}

		return $laReturn;
	}


	/*
	 *	Cambia estado de generación de soportes
	 *	@param integer $tnIngreso: número de ingreso del paciente
	 *	@param string $tcTipoSoporte: tipo de plantilla de soportes
	 *	@param array $taSoporte: códigos de soportes a modificar
	 *	@param string $tcEstado: código del estado
	 *	@param integer $tnFecha: nueva fecha para soporte en formato YYYYMMDD, también permite 'HOY' o 'addN' donde N es el número de días a adicionar
	 *	@param array $taLog: datos de log (usuario, programa, fecha y hora), no obligatorio
	 */
	public function actualizarEstadoSoporte($tnIngreso, $tcTipoSoporte, $taSoporte, $tcEstado='', $tnFecha=false, $taLog=[])
	{
		global $goDb;
		$laReturn = ['error'=>''];

		if ($tnFecha=='INMEDIATO') {
			$this->estableceTipoSop($tcTipoSoporte);
			$laReturn = $this->generarSoportesPendientes($tnIngreso, $taSoporte);
			return $laReturn;
		}

		foreach ($taSoporte as $lcSoporte) {
			$laWhere = [
				'INGCMS'=>$tnIngreso,
				'TIPCMS'=>$tcTipoSoporte,
				'CDCCMS'=>$lcSoporte,
			];

			$laValida = $goDb
				->select('FGNCMS')
				->from('CMSPRT')
				->where($laWhere)
				->get('array');
			if ($goDb->numRows()>0) {
				if (count($taLog)==0) {
					$taLog = $this->generarLog();
				}
				$laActualizar = [];
				$tcEstado = trim($tcEstado);
				if (strlen($tcEstado)>0 && in_array($tcEstado, $this->aEstado)) {
					$laActualizar['ESTCMS'] = $tcEstado;
					if ($tcEstado == $this->aEstado['Generado']) {
						$laActualizar['FGNCMS'] = $taLog['fecha'];
						$laActualizar['HGNCMS'] = $taLog['hora'];
					}
				}
				if (is_numeric($tnFecha)) {
					$laActualizar['FGNCMS'] = $tnFecha;
				} elseif (is_string($tnFecha)) {
					switch (true) {
						case $tnFecha=='HOY':
							$laActualizar['FGNCMS'] = $taLog['fecha'];
							break;
						case substr($tnFecha,0,3)=='add':
							$lnDiasAdd = intval(substr($tnFecha,3,3));
							if (is_numeric($lnDiasAdd) && $lnDiasAdd>0 && $lnDiasAdd<30) {
								$lnFechaGeneraActual = $laValida['FGNCMS']>0 ? $laValida['FGNCMS'] : $taLog['fecha'];
								$ldFechaGeneraActual = \DateTime::createFromFormat('Ymd', $lnFechaGeneraActual);
								$laActualizar['FGNCMS'] = ($ldFechaGeneraActual->add(new \DateInterval("P{$lnDiasAdd}D")))->format('Ymd');
							}
							break;
					}
				}
				if (count($laActualizar)>0) {
					$laActualizar += [
						'USMCMS'=>$taLog['usuario'],
						'PGMCMS'=>$taLog['programa'],
						'FEMCMS'=>$taLog['fecha'],
						'HOMCMS'=>$taLog['hora'],
					];
					$goDb->from('CMSPRT')
						->where($laWhere)
						->actualizar($laActualizar);
				}
			}
		}

		return $laReturn;
	}


	/*
	 *	Busca facturas validas, del número de días indicado hacia atrás, para programar la generación de soportes por ingreso
	 */
	public function programarIngresos()
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		$lnDias = $goDb->obtenerTabMae1('OP3TMA', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='DIASPAUS' AND ESTTMA=''", null, 10);
		$lnFechaIni = intval($goDb->obtenerTabMae1('OP2TMA', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='DIASPAUS' AND ESTTMA=''", null, '20230228'));
		$ltFecha = new \DateTime($goDb->fechaHoraSistema());
		$lcFecha = ($ltFecha->sub(new \DateInterval("P{$lnDias}D")))->format('Ymd');

		$lcTipoSop = $goDb->obtenerTabMae1('CL2TMA', 'CMSOPORT', "CL1TMA='TIP_SOP' AND OP1TMA='1' AND ESTTMA=''", null, 'TRANSFIR');
		$this->estableceTipoSop($lcTipoSop, true);
		$laSoportes = array_keys($this->aData);

		$laQuery = $goDb->distinct()
			->select('F.INGCAB INGRESO, F.NITCAB NITFAC')
			->from('FEMOV E')
			->innerJoin('FACCABF F', 'E.FACT=F.FRACAB', null)
			->innerJoin('TABMAE  T', "F.NITCAB=T.OP7TMA AND CL1TMA='{$lcTipoSop}' AND CL2TMA='DIASPAUS' AND ESTTMA=''", null)
			//->where(['FECC'=>$lcFecha, 'ESTA'=>'02'])
			->where(['E.FECM'=>$lcFecha, 'E.TIPR'=>'06FA', 'E.ESTA'=>'02'])
			->getAll('array');
		if ($goDb->numRows()>0) {
			$laIngresos = array_unique(array_column($laQuery, 'INGRESO'));
			foreach ($laIngresos as $laIngreso) {
				$laRta = $this->insertarGenerar($laIngreso['INGRESO'], $lcTipoSop, $laSoportes);
				$laReturn['error'] += $laRta['error'];
			}
		}

		return $laReturn;
	}


	/*
	 *	Si la facturas es de una entidad parametrizada, programa la generación de soportes del ingreso correspondiente
	 */
	public function programarFactura($tnFactura, $tbSinEstado=false)
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		$lcTipoSop = $goDb->obtenerTabMae1('CL2TMA', 'CMSOPORT', "CL1TMA='TIP_SOP' AND OP1TMA='1' AND ESTTMA=''", null, 'TRANSFIR');
		$lcFiltroEstado = $tbSinEstado ? '' : " AND E.ESTA='02'";

		$laQuery = $goDb->distinct()
			->select('F.INGCAB INGRESO, F.FEFCAB FECHAFAC, F.USRCAB FACTURADOR')
			->from('FACCABF F')
			->innerJoin('FEMOV E', "F.FRACAB=E.FACT AND E.TIPR='06FA' {$lcFiltroEstado}", null)
			->innerJoin('TABMAE  T', "F.NITCAB=T.OP7TMA AND TIPTMA='CMSOPORT' AND CL1TMA='{$lcTipoSop}' AND CL2TMA='NITCM' AND ESTTMA=''", null)
			->where(['F.FRACAB'=>$tnFactura])
			->orderBy('F.FEFCAB')
			->getAll('array');
		if ($goDb->numRows()>0) {
			$lnIngreso = $laQuery[0]['INGRESO'];
			$lnFechaFactura = $laQuery[0]['FECHAFAC'];
			$lcFacturador = $laQuery[0]['FACTURADOR'];
			$this->estableceTipoSop($lcTipoSop, true);
			foreach ($this->aData as $lcKey => $laData) {
				if ($laData['ACTIVO']) {
					$laSoportes[] = $lcKey;
				}
			}
			$laRta = $this->insertarSoportesGenerar($lnIngreso, $lnFechaFactura, $lcFacturador, $lcTipoSop, $laSoportes);
			$laReturn['error'] = $laRta['error'];
		} else {
			$laReturn['error'] = ['NIT de factura no configurado o Factura no encontrada'];
		}

		return $laReturn;
	}


	/*
	 *	Colocar soportes que quedaron en GS a estado 00
	 */
	public function resolverGS($tcTipoSoporte='TRANSFIR', $taListaSoportes=['LAB'])
	{
		global $goDb;
		$laArray = $goDb
			->select('INGCMS, TIPCMS, CDCCMS')
			->from('CMSPRT')
			->where("ESTCMS='GS' AND TIPCMS=:tipoSoporte AND FGNCMS<REPLACE(CHAR(CURRENT DATE),'-','')")
			->in('CDCCMS', $taListaSoportes)
			->addBindValue([':tipoSoporte'=>$tcTipoSoporte])
			->getAll('array');
		if ($goDb->numRows()>0) {
			$goDb->from('CMSPRT')
				->where("ESTCMS='GS' AND TIPCMS=:tipoSoporte AND FGNCMS<REPLACE(CHAR(CURRENT DATE),'-','')")
				->in('CDCCMS', $taListaSoportes)
				->addBindValue([':tipoSoporte'=>$tcTipoSoporte])
				->actualizar(['ESTCMS'=>'00']);
		}
	}


	/*
	 *	Busca soportes pendientes por generar
	 *	@param integer $tnIngreso: número de ingreso, opcional si no se envía genera los anteriores a la fecha
	 */
	public function generarSoportesPendientes($tnIngreso=0, $taSoportes=[])
	{
		global $goDb;
		$laReturn = ['error'=>[]];

		if ($tnIngreso>0) {
			$goDb->where(['INGCMS'=>$tnIngreso]);
		} else {
			$goDb->where("FGNCMS < REPLACE(CHAR(CURRENT DATE),'-','')");
		}
		if (count($taSoportes)>0) {
			$goDb->in('CDCCMS', $taSoportes);
		}

		$laSoportes = $goDb
			->select('INGCMS INGRESO, TRIM(TIPCMS) TIPO, TRIM(CDCCMS) SOPORTE')
			->from('CMSPRT')
			->in('ESTCMS', [$this->aEstado['PorGenerar'], $this->aEstado['Error']])
			->orderBy('INGCMS, TIPCMS, CDCCMS')
			->getAll('array');
		if ($goDb->numRows()>0) {

			$lnIngreso = 0;
			$lcTipoSop = '';
			$lbGenerarSoportes = true;

			foreach ($laSoportes as $laSoporte) {

				if ($lnIngreso!==$laSoporte['INGRESO'] || $lcTipoSop!==$laSoporte['TIPO']) {

					if ($lnIngreso!==$laSoporte['INGRESO']) {
						$this->iniciar($laSoporte['INGRESO']);
					}
					if ($lcTipoSop!==$laSoporte['TIPO']) {
						$this->estableceTipoSop($laSoporte['TIPO']);
					}

					$lnIngreso = $laSoporte['INGRESO'];
					$lcTipoSop = $laSoporte['TIPO'];

					$laWhere = ['INGCMS'=>$lnIngreso, 'TIPCMS'=>$lcTipoSop, 'ESTCMS'=>'00'];
					$laRta = $goDb->select('CDCCMS')->from('CMSPRT')->where($laWhere)->getAll('array');
					$lbGenerarSoportes = false;
					if ($goDb->numRows()>0) {
						$taLog = $taLog = $this->generarLog();
						$laRta = $goDb->from('CMSPRT')->where($laWhere)->actualizar([
							'ESTCMS'=>$this->aEstado['GenerandoSoporte'],
							'USMCMS'=>$taLog['usuario'],
							'PGMCMS'=>$taLog['programa'],
							'FEMCMS'=>$taLog['fecha'],
							'HOMCMS'=>$taLog['hora'],
						]);
						if ($laRta) {
							$lbGenerarSoportes = true;
						}
					}
				}
				$lcSoporte = $laSoporte['SOPORTE'];

				if ($lbGenerarSoportes) {
					$laRta = $this->generarDoc($lcSoporte);
					if (is_string($laRta['error']) && strlen($laRta['error'])==0) {
						$lcEstado = $laRta['num']==0 ? $this->aEstado['SinSoporte'] : $this->aEstado['Generado'];
					} else {
						$lcEstado = $this->aEstado['Error'];
						$laReturn['error'][] = array_merge($laReturn['error'], [$laRta['error']]);
					}
					$this->actualizarEstadoSoporte($lnIngreso, $lcTipoSop, [$lcSoporte], $lcEstado);

				}
			}
		}

		return $laReturn;
	}

	public function listaSoportesCM()
	{
		global $goDb;
		$laDatos = [];
		$laQry = $goDb
			->select('TRIM(CL1TMA) TIPOSOP, TRIM(CL3TMA) PREFIJO, TRIM(DE1TMA) TITULO, TRIM(DE2TMA||OP5TMA) FNDOCS, OP1TMA ACTIVO')
			->from('TABMAE')
			->where("TIPTMA='CMSOPORT' AND CL2TMA='SOPORTE' AND ESTTMA=''")
			->orderBy('CL1TMA, OP7TMA')
			->getAll('array');
		if ($goDb->numRows()>0) {
			foreach ($laQry as $laSop) {
				$laDatos[$laSop['TIPOSOP']][$laSop['PREFIJO']] = [
					'TITULO'=>$laSop['TITULO'],
					'ACTIVO'=>$laSop['ACTIVO']=='1',
				];
			}
		}
		return $laDatos;
	}


	public function obtenerServidor($tcTipoSoporte)
	{
		global $goDb;
		$lcServer = $goDb->obtenerTabMae1('TRIM(DE2TMA)', 'CMSOPORT', "CL1TMA='{$tcTipoSoporte}' AND CL2TMA='CARPETA' AND ESTTMA=''", null, "");
		$lcRuta  = $goDb->obtenerTabMae1('TRIM(OP5TMA)', 'CMSOPORT', "CL1TMA='{$tcTipoSoporte}' AND CL2TMA='CARPETA' AND ESTTMA=''", null, "");
		$lcServerPrincipal = strstr(substr($lcServer, 2), '/', true);
		$laConfigPrincipal = $goDb->configServer($lcServerPrincipal);
		$this->aServidor['srv'] = [
			'ruta'=>$lcRuta,
			'carpeta'=>'',
			'server'=>$lcServer,
			'wrkg'=>$laConfigPrincipal['workgroup'],
			'user'=>$laConfigPrincipal['user'],
			'pass'=>$laConfigPrincipal['pass'],
		];
		$lcServer = $goDb->obtenerTabMae1('TRIM(DE2TMA)', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='CARPETA' AND ESTTMA=''", null, "");
		$lcRuta  = $goDb->obtenerTabMae1('TRIM(OP5TMA)', 'CMSOPORT', "CL1TMA='GENERAL' AND CL2TMA='CARPETA' AND ESTTMA=''", null, "");
		$lcServerPrincipal = strstr(substr($lcServer, 2), '/', true);
		$laConfigPrincipal = $goDb->configServer($lcServerPrincipal);
		$this->aServidor['bck'] = [
			'ruta'=>$lcRuta,
			'carpeta'=>'',
			'server'=>$lcServer,
			'wrkg'=>$laConfigPrincipal['workgroup'],
			'user'=>$laConfigPrincipal['user'],
			'pass'=>$laConfigPrincipal['pass'],
		];
	}


	private function generarLog()
	{
		global $goDb;
		$ltAhora = new \DateTime($goDb->fechaHoraSistema());
		return [
			'usuario'=>'HCWEB',
			'programa'=>'CLSDOCCM',
			'fecha'=>$ltAhora->format('Ymd'),
			'hora'=>$ltAhora->format('His'),
		];
	}


	public function aData()
	{
		return $this->aData;
	}

}
