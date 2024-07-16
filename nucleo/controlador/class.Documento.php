<?php

namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.UsuarioRegMedico.php';
require_once __DIR__ . '/class.CitaProcedimiento.php';
require_once __DIR__ . '/class.Doc_NotasAclaratorias.php';
require_once __DIR__ . '/class.TipoDocumento.php';
require_once __DIR__ . '/class.Cup.php';
require_once __DIR__ . '/class.Via.php';


class Documento
{
	// Datos principales del documento
	protected $aDatos = [];
	protected $aTiposDoc = [];

	// Contenido del documento
	protected $aContenido = [
		'Titulo' => '',
		'Cabeza' => [],
		'Cuerpo' => [],
		'Firmas' => [],
		'Notas'	 => [],
	];

	protected $cSL = PHP_EOL;
	protected $bUsarDatosHistoricos = false;
	protected $nUsarFirmaDigital = 0;
	protected $bUsarFirmaDigital = false;
	protected $cRutaFirma = '';
	protected $nModoEdad = 1;	// 0 = a fecha de ingreso, 1 = a fecha de documento
	protected $aDatosSrvAdjuntos = [];
	protected $aVias = [];
	protected $aConfigFirma = [];
	protected $nIngreso;

	protected $aPropPDF = [
		'Creator' => 'Fundación Clínica Shaio',
		'Author' => 'Fundación Clínica Shaio',
		'Title' => 'Libro de HC',
		'Subject' => 'Libro de HC',
		'Keywords' => 'SHAIO,LIBRO,HISTORIA,CLINICA',
	];



	public function __construct()
	{
		global $goDb;
		$this->bUsarDatosHistoricos = '1' == $goDb->ObtenerTabMae1('OP1TMA', 'LIBROHC', ['CL1TMA' => 'REPORTE', 'CL2TMA' => 'ENCABEZA', 'CL3TMA' => 'USARFEC', 'ESTTMA' => ''], null, '0');
		$this->aDatos = $this->NuevosDatos(true);

		// Firma digital - 0=Nunca, 1=Siempre, 2=Por documento
		$this->nUsarFirmaDigital = intval($goDb->obtenerTabMae1('OP1TMA', 'LIBROHC', ['CL1TMA' => 'FIRMADIG', 'ESTTMA' => ''], null, '0'));

		$this->cRutaFirma = str_replace('\\', '/', trim($goDb->ObtenerTabMae1('DE2TMA', 'FIRMADIG', ['CL1TMA' => 'RUTA', 'ESTTMA' => ''], null, '')));

		$this->aConfigFirma = $this->configFirma();
		$this->aTiposDoc = $this->tiposDoc();

		$this->aDatosSrvAdjuntos = $this->fDatosSrvAdjuntos();

		$loVia = new Via();
		foreach ($loVia->aVias as $laVia) {
			$this->aVias[$laVia['CODVIA']] = trim($laVia['DESVIA']);
		}
	}


	/*
	 *	Obtiene los datos del paciente y carga el documento
	 */
	public function obtenerDocumento($taDatos, $tlCargarDatosPac = false, $tlDocumentoSolo = true, $tlLabHTML = true, $tlEsHTML = false, $tbTodosLab = false)
	{
		if ($taDatos['cTipoProgr'] == 'ADJUNTOS') {

			if ($taDatos['cTipoDocum'] == 'urlfile') {
				$laData = [
					'cTitulo' => '',
					'lMostrarEncabezado' => false,
					'lMostrarLogoEncabz' => false,
					'lMostrarPie' => false,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'cPlan' => 'NOP',
					'aNotas' => ['notas' => false,],
					'aCuerpo' => [['urlfile', ['urlfile' => $taDatos['cUrlFile'],],],],
				];
				$this->Encabezado($laData);
				$this->Cuerpo($laData);
			} else {
				// Documentos Adjuntos
				if ($tlDocumentoSolo) {
					// Solo descarga el archivo
					$this->fcArchivoAdjunto($taDatos);
				} else {
					// Procesa el archivo para unirlo con el resto de PDF
					$this->aDatos = array_merge($this->aDatos, $this->NuevosDatos($tlCargarDatosPac), $taDatos);
					if ($tlCargarDatosPac || $this->bUsarDatosHistoricos) {
						$this->DatosPaciente();
					}

					$laData = $this->fcDocAdjunto($taDatos);
					$this->Encabezado($laData);
					$this->Cuerpo($laData);
				}
			}
		} elseif ($taDatos['cTipoDocum'] == '1100') {

			// Laboratorios
			if ($tlDocumentoSolo && $tlLabHTML) {
				$this->fcAbrirLaboratorio($taDatos['nIngreso'], $taDatos['nConsecCita']);
			} else {
				require_once  __DIR__ . '/class.Doc_Laboratorio.php';
				$this->Cuerpo((new Doc_Laboratorio())->retornarDocumento($taDatos, $tbTodosLab));
				$this->aContenido['Cabeza']['texto'] = '';
				$this->aContenido['Cabeza']['mostrar'] = false;
				$this->aContenido['Cabeza']['logo'] = false;
				$this->aContenido['Cabeza']['mostrarpie'] = false;
			}
		} else if ($taDatos['cTipoDocum'] == '9100') {

			require_once __DIR__ . '/class.ApiConsentimientoInformado.php';

			$loConsentimiento = new \ApiConsentimientoInformado();
			$laResponse = $loConsentimiento->consultarDocumento($taDatos['nConsecDoc']);
			if (!empty($laResponse)) {
				$this->aContenido['Cabeza']['texto'] = '';
				$this->aContenido['Cabeza']['mostrar'] = false;
				$this->aContenido['Cabeza']['logo'] = false;
				$this->aContenido['Cabeza']['mostrarpie'] = false;
				$this->Cuerpo(
					[
						'cTitulo' => '',
						'aCuerpo' => [
							[
								'laboratorio', ['html' => $tlEsHTML ? $laResponse['consentimiento'] : utf8_decode($laResponse['consentimiento'])]
							]
						]
					]
				);
			}
		} else {

			// Documentos en AS400
			$this->aDatos = array_merge($this->aDatos, $this->NuevosDatos($tlCargarDatosPac), $taDatos);

			// Adiciona la descripción de la vía de ingreso
			$this->aDatos['cDesVia'] = isset($taDatos['cCodVia']) ? ($this->aVias[$taDatos['cCodVia']] ?? '') : '';

			if ($tlCargarDatosPac || $this->bUsarDatosHistoricos) {
				$this->DatosPaciente();
			}

			if ($laData = $this->ConsultarDatos($tlEsHTML)) {
				$this->Encabezado($laData);
				$this->Cuerpo($laData);
				$this->Firmas($laData);
				$this->NotasAclaratorias($laData);
			}
		}
	}


	/*
	 *	Documento Adjunto
	 */
	public function fcDocAdjunto($taDatos)
	{
		$lcDescrip = $taDatos['cCUP'];
		$tcCarpeta = $taDatos['nConsecCons'];
		$tcArchivo = $taDatos['nConsecEvol'];
		$lcFile = $tcCarpeta . $tcArchivo;
		return [
			'cTitulo' => '',
			'lMostrarEncabezado' => false,
			'lMostrarLogoEncabz' => false,
			'lMostrarPie' => false,
			'lMostrarFechaRealizado' => false,
			'lMostrarViaCama' => false,
			'cTxtAntesDeCup' => '',
			'cTituloCup' => '',
			'cTxtLuegoDeCup' => '',
			'aNotas' => ['notas' => false,],
			'aCuerpo' => [
				[
					'adjunto',
					[
						'descrip' => $lcDescrip,
						'servidor' => $this->aDatosSrvAdjuntos,
						'file' => $lcFile,
					],
				]
			],
		];
	}


	/*
	 *	Abrir Laboratorio
	 */
	public function fcAbrirLaboratorio($tnIngreso = 0, $tnCita = 0)
	{
		global $goDb;

		$lcRutaLab = ''; //'http://laboratorio.shaio.org/Usuario.cgi?AccionServidor=AccionImprimirNShaio&Alias=HIS&Clave=HIS&NShaio={{oVar.nNroIng}}&CodProcedimiento={{oVar.nConCit}}';
		$lcRutaLab = $goDb->obtenerTabMae1('TRIM(DE2TMA)||TRIM(OP5TMA)', 'LIBROHC', ['CL1TMA' => 'LABORATO', 'CL2TMA' => 'SERVERW', 'ESTTMA' => ''], null, $lcRutaLab);

		if (!empty($lcRutaLab)) {
			$lcRutaLab = str_replace('{{oVar.nNroIng}}', $tnIngreso, str_replace('{{oVar.nConCit}}', $tnCita, $lcRutaLab));
			header('Location: ' . $lcRutaLab);
		}
	}


	/*
	 *	Obtener archivo escaneado
	 */
	public function fcArchivoAdjunto($taDatos)
	{
		$lcArchivo = $this->fcObtenerHcAdjunto($taDatos['nConsecCons'], $taDatos['nConsecEvol'], false);
		if (!empty($lcArchivo)) {
			$lcNombreArchivo = 'AdjuntoHC.pdf';
			// mostrar el pdf
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $lcNombreArchivo . '"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			echo $lcArchivo;
			exit;
		}
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 *	@param string $tcCarpeta = carpetas en el servidor donde se almacena el archivo
	 *	@param string $tcArchivo = nombre del archivo
	 *	@param boolean $tlCopiaLocal => si es true copia el archivo al servidor local (predeterminado false)
	 *	@return si $tlCopiaLocal=true retorna ruta local, si es false retorna contenido del archivo
	 */
	private function fcObtenerHcAdjunto($tcCarpeta = '', $tcArchivo = '', $tlCopiaLocal = false)
	{
		if ($tlCopiaLocal) {


			// Sin copia local
		} else {
			$lcPdfData = $lcTipoMiMe = '';
			$lnFormato = $lnEstado = 0;
			$laSrv = $this->aDatosSrvAdjuntos['principal'];
			$lcFile = $laSrv['ruta'] . '\\' . $tcCarpeta . $tcArchivo;
			$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
			if ($lnEstado <= 0) {
				$lcPdfData = $lcTipoMiMe = '';
				$lnFormato = $lnEstado = 0;
				$laSrv = $this->aDatosSrvAdjuntos['respaldo'];
				$lcFile = $laSrv['ruta'] . '\\' . $tcCarpeta . $tcArchivo;
				$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
			}
			return $lcPdfData;
		}
	}


	/*
	 *	Datos del servidor donde se encuentran los adjuntos
	 *	@return array
	 */
	private function fDatosSrvAdjuntos()
	{
		global $goDb;

		// Ruta de servidor principal
		$loTabmae = $goDb->ObtenerTabMae('DE2TMA', 'HCADJUN', ['CL1TMA' => 'GENERAL', 'CL2TMA' => '01010101', 'ESTTMA' => '']);
		$lcRutaPrincipal = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', '').'');
		$lcServerPrincipal = strstr(substr($lcRutaPrincipal, 2), '\\', true);
		$laConfigPrincipal = $goDb->configServer($lcServerPrincipal);

		// Ruta de servidor de backup
		$loTabmae = $goDb->ObtenerTabMae('DE2TMA', 'HCADJUN', ['CL1TMA' => 'GENERAL', 'CL2TMA' => '01010102', 'ESTTMA' => '']);
		$lcRutaBackup = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', '').'');
		$lcServerBackup = strstr(substr($lcRutaBackup, 2), '\\', true);
		$laConfigBackup = $lcServerPrincipal == $lcServerBackup ? $laConfigPrincipal : $goDb->configServer($lcServerBackup);

		return [
			'principal' => [
				'ruta' => $lcRutaPrincipal,
				'server' => $lcServerPrincipal,
				'workgroup' => $laConfigPrincipal['workgroup'],
				'user' => $laConfigPrincipal['user'],
				'pass' => $laConfigPrincipal['pass'],
			],
			'respaldo' => [
				'ruta' => $lcRutaBackup,
				'server' => $lcServerBackup,
				'workgroup' => $laConfigBackup['workgroup'],
				'user' => $laConfigBackup['user'],
				'pass' => $laConfigBackup['pass'],
			],
		];
	}


	/*
	 *	Obtiene los datos del paciente de acuerdo a la propiedad bUsarDatosHistoricos
	 */
	public function DatosPaciente()
	{
		// Si no existe crea el objeto para el ingreso del paciente
		if (is_null($this->aDatos['oIngrPaciente']))
			$this->aDatos['oIngrPaciente'] = new Ingreso();

		if ($this->bUsarDatosHistoricos)
			$this->DatosPacienteHistoricos();
		else
			$this->DatosPacienteUltimos();

		if ($this->nModoEdad == 1)
			$this->aDatos['oIngrPaciente']->obtenerEdad(str_replace('-', '', substr($this->aDatos['tFechaHora'], 0, 10)));
	}


	/*
	 *	Obtiene los últimos datos registrados del paciente
	 */
	private function DatosPacienteUltimos()
	{
		if ($this->aDatos['nIngreso'] == 0) {
			$loTipoDoc = new TipoDocumento();
			$loTipoDoc->cargarTipoDoc($this->aDatos['cTipDocPac']);
			$this->aDatos['cTipDocPac'] = $loTipoDoc->aTipo['TIPO'];
			$this->aDatos['oIngrPaciente']->oPaciente->cargarPaciente($this->aDatos['cTipDocPac'], $this->aDatos['nNumDocPac']);

			//$lnFecha = str_replace('-','',substr($this->aDatos['tFechaHora'],0,10));
			//$laEdad = explode('-', $this->aDatos['oIngrPaciente']->oPaciente->getEdad($lnFecha, '%y-%m-%d'));
			//$this->aDatos['oIngrPaciente']->aEdad = [ 'y'=>$laEdad[0], 'm'=>$laEdad[1], 'd'=>$laEdad[2] ];
			$this->aDatos['oIngrPaciente']->cId = $this->aDatos['cTipDocPac'];
			$this->aDatos['oIngrPaciente']->nId = $this->aDatos['nNumDocPac'];
		} else {
			$this->aDatos['oIngrPaciente']->cargarIngreso($this->aDatos['nIngreso']);
			$this->aDatos['cTipDocPac'] = $this->aDatos['oIngrPaciente']->cId;
			$this->aDatos['nNumDocPac'] = $this->aDatos['oIngrPaciente']->nId;
			$this->aDatos['oIngrPaciente']->obtenerDescripcionPlan();
			$this->aDatos['oIngrPaciente']->obtenerEstadoCivil();
			$this->aDatos['oIngrPaciente']->oPaciente->obtenerOcupacion();
		}
	}


	/*
	 *	Obtiene los datos históricos del paciente
	 */
	private function DatosPacienteHistoricos()
	{
		if ($this->aDatos['nIngreso'] == 0) {
			$loTipoDoc = new TipoDocumento();
			$loTipoDoc->cargarTipoDoc($this->aDatos['cTipDocPac']);
			$this->aDatos['cTipDocPac'] = $loTipoDoc->aTipo['TIPO'];
			$this->aDatos['oIngrPaciente']->oPaciente->cargarPacientePorFecha($this->aDatos['cTipDocPac'], $this->aDatos['nNumDocPac'], $this->aDatos['tFechaHora']);

			$lnFecha = str_replace('-', '', substr($this->aDatos['tFechaHora'], 0, 10));
			$laEdad = explode('-', $this->aDatos['oIngrPaciente']->oPaciente->getEdad($lnFecha, '%y-%m-%d'));
			$this->aDatos['oIngrPaciente']->aEdad = ['y' => $laEdad[0], 'm' => $laEdad[1], 'd' => $laEdad[2]];
			$this->aDatos['oIngrPaciente']->cId = $this->aDatos['cTipDocPac'];
			$this->aDatos['oIngrPaciente']->nId = $this->aDatos['nNumDocPac'];
		} else {
			$this->aDatos['oIngrPaciente']->cargarIngresoPorFecha($this->aDatos['nIngreso'], $this->aDatos['tFechaHora']);
			$this->aDatos['cTipDocPac'] = $this->aDatos['oIngrPaciente']->cId;
			$this->aDatos['nNumDocPac'] = $this->aDatos['oIngrPaciente']->nId;
		}
	}


	/*
	 *	Consulta los datos del documento a partir de la clase
	 */
	private function ConsultarDatos($tlEsHTML = false)
	{
		if (isset($this->aTiposDoc[$this->aDatos['cTipoProgr']])) {
			if (!empty($this->aDatos['nConsecCita']))
				$this->aDatos['oCitaProc'] = new CitaProcedimiento($this->aDatos['nIngreso'], $this->aDatos['nConsecCita']);

			if (!empty($this->aDatos['cCUP']))
				$this->aDatos['oCup'] = new Cup($this->aDatos['cCUP']);

			$laTipoDoc = $this->aTiposDoc[$this->aDatos['cTipoProgr']];
			$this->bUsarFirmaDigital = $this->nUsarFirmaDigital == 1 || ($this->nUsarFirmaDigital == 2 && $laTipoDoc['firmadig']);
			$lcRutaClass = __DIR__ . "/class.{$laTipoDoc['clase']}.php";
			if (file_exists($lcRutaClass)) {
				require_once $lcRutaClass;
				$className = 'NUCLEO\\' . $laTipoDoc['clase'];
				$oDoc = new $className();
				// formato de retorno HTML o PDF
				$this->aDatos['format'] = $tlEsHTML ? 'HTML' : 'PDF';
				return $oDoc->retornarDocumento($this->aDatos);
			} else {
				return [];
			}
		} else {
			return [];
		}
	}


	/*
	 *	Genera encabezado estándar para el documento
	 */
	private function Encabezado($taData)
	{
		if ($taData['lMostrarEncabezado'] ?? true) {
			$lnCol1 = 65;
			$lnTtl1 = 12;
			$lnEspc = 3;
			$lnC1Es = $lnCol1 - $lnEspc;
			$lnC1Tx = $lnC1Es - $lnTtl1;

			$lcSpc = ' ';
			$lcSpc5 = str_repeat($lcSpc, $lnEspc);
			$lcSpc62 = str_repeat($lcSpc, $lnCol1);
			$loIng = $this->aDatos['oIngrPaciente'];
			$lMostrarFechaRealizado = $taData['lMostrarFechaRealizado'] ?? false;
			$lMostrarViaCama = $taData['lMostrarViaCama'] ?? false;

			$cTituloCup = $taData['cTituloCup'] ?? '';
			$lMostrarCup = !empty($cTituloCup);
			if ($lMostrarCup && !empty($this->aDatos['cCUP'])) {
				$this->aDatos['cDcsCUP'] = trim($this->aDatos['oCup']->cDscrCup);
			}
			$cAntesCup = $taData['cTxtAntesDeCup'] ?? '';
			$cLuegoCup = $taData['cTxtLuegoDeCup'] ?? '';
			$lcPlan = $taData['cPlan'] ?? '';
			if (empty($lcPlan)) {
				$lcPlan = $loIng->cPlanDescripcion;
			}

			$lcFecRea = AplicacionFunciones::formatFechaHora('fechahora12', str_replace('/', '', str_replace('-', '', str_replace(':', '', str_replace(' ', '', $this->aDatos['tFechaHora'])))));

			$lcEdad = $loIng->aEdad['y'] . 'A, ' . $loIng->aEdad['m'] . 'M, ' . $loIng->aEdad['d'] . 'D';

			$lcText = 'Paciente  : ' . AplicacionFunciones::mb_str_pad($loIng->oPaciente->getNombresApellidos(), $lnC1Tx, $lcSpc, STR_PAD_RIGHT, 'UTF-8') . $lcSpc5
				. 'Documento : ' . $loIng->oPaciente->aTipoId['ABRV'] . ' ' . $loIng->nId . $this->cSL
				. 'Género    : ' . AplicacionFunciones::mb_str_pad($loIng->oPaciente->getGenero() . $lcSpc5 . 'Edad: ' . $lcEdad, $lnC1Tx, $lcSpc, STR_PAD_RIGHT, 'UTF-8') . $lcSpc5
				. 'Historia  : ' . $loIng->oPaciente->nNumHistoria . $this->cSL
				. (empty($lcPlan) ? $lcSpc62 : 'Entidad   : ' . AplicacionFunciones::mb_str_pad(mb_substr($lcPlan, 0, $lnC1Tx, 'UTF-8'), $lnC1Tx, $lcSpc, STR_PAD_RIGHT, 'UTF-8') . $lcSpc5)
				. (empty($loIng->nIngreso) ? '' : 'Ingreso   : ' . $loIng->nIngreso) . $this->cSL
				. AplicacionFunciones::mb_str_pad(empty($loIng->oPaciente->cOcupacion) ? '' : 'Ocupación : ' . mb_substr(mb_strtoupper($loIng->oPaciente->cOcupacion, 'UTF-8'), 0, $lnC1Tx, 'UTF-8'), $lnCol1, $lcSpc, STR_PAD_RIGHT, 'UTF-8')
				. (empty($loIng->cEstadoCivil) ? '' : 'Est Civil : ' . $loIng->cEstadoCivil)
				. ($lMostrarFechaRealizado || $lMostrarViaCama ? $this->cSL
					. AplicacionFunciones::mb_str_pad(($lMostrarFechaRealizado ? 'Realizado : ' . $lcFecRea . $lcSpc5 : '')
						. ($lMostrarViaCama && ($this->aDatos['cDescVia'] ?? false) ? ($lMostrarFechaRealizado ? 'Vía : ' : 'Vía       : ') . $this->aDatos['cDescVia'] : ''), $lnCol1, $lcSpc, STR_PAD_RIGHT, 'UTF-8')
					. ($lMostrarViaCama && $this->aDatos['cCodVia'] == '05' ? 'Habitación: ' . $this->aDatos['cSecHab'] : '') : '')
				. (empty($cAntesCup) ? '' : $this->cSL . $cAntesCup)
				. ($lMostrarCup ? $this->cSL . AplicacionFunciones::mb_str_pad($cTituloCup, 10, $lcSpc, STR_PAD_RIGHT, 'UTF-8') . ': ' . $this->aDatos['cCUP'] . ' - ' . $this->aDatos['cDcsCUP'] : '')
				. (empty($cLuegoCup) ? '' : $this->cSL . $cLuegoCup);
			$lnMinSL = 5; // Mínimo número de saltos de línea
			$lnNumSL = mb_substr_count($lcText, $this->cSL, 'UTF-8');
			if ($lnNumSL < $lnMinSL) $lcText .= str_repeat($this->cSL, $lnMinSL - $lnNumSL);
		} else {
			$lcText = '';
		}

		$this->aContenido['DatLog'] = $lcSep = '';
		if (!empty($this->aDatos['nConsecCita'])) {
			$this->aContenido['DatLog'] = 'C' . $this->aDatos['nConsecCita'];
			$lcSep = '-';
		}
		if (!empty($this->aDatos['nConsecDoc'])) {
			$this->aContenido['DatLog'] .= $lcSep . 'D' . $this->aDatos['nConsecDoc'];
			$lcSep = '-';
		}
		if (!empty($this->aDatos['nConsecEvol']) && strpos($this->aDatos['nConsecEvol'], ',') === false) {
			$this->aContenido['DatLog'] .= $lcSep . 'E' . $this->aDatos['nConsecEvol'];
		}
		$this->aContenido['Cabeza']['texto'] = $lcText;
		$this->aContenido['Cabeza']['mostrar'] = ($taData['lMostrarEncabezado'] ?? true) && ($taData['lMostrarEncabezadoPrimeraPagina'] ?? true);
		$this->aContenido['Cabeza']['logo'] = $taData['lMostrarLogoEncabz'] ?? true;
		$this->aContenido['Cabeza']['mostrarpie'] = $taData['lMostrarPie'] ?? true;
	}


	/*
	 *	Consulta datos y genera cuerpo del documento
	 */
	private function Cuerpo($taData)
	{
		$this->aContenido['Titulo'] = $taData['cTitulo'];
		$this->aContenido['Cuerpo'] = is_array($taData['aCuerpo']) ? $taData['aCuerpo'] : [];
		foreach ($this->aContenido['Cuerpo'] as $lnClave => $laCuerpo) {
			if ($laCuerpo[0] == 'firmas') {
				$this->aContenido['Cuerpo'][$lnClave] = ['firmas', $this->obtenerFirmas($laCuerpo[1])];
			}
		}
	}


	/*
	 *	Obtener firmas si son necesarias
	 */
	private function Firmas($taData)
	{
		if (isset($taData['aFirmas'])) {
			$this->aContenido['Cuerpo'][] = ['firmas', $this->obtenerFirmas($taData['aFirmas'])];
		}
	}

	/*
	 *	Obtener firmas si son necesarias
	 */
	private function obtenerFirmas($taFirmas)
	{
		$loUser = new UsuarioRegMedico();
		$laRta = [];
		foreach ($taFirmas as $laFirma) {
			$lcTextoFirma = $laFirma['texto_firma'] ?? '';

			$lcRegistro = $laFirma['registro'] ?? '';
			$lcUsuario = $laFirma['usuario'] ?? '';
			$lcMedico = $laFirma['nombre'] ?? '';
			$lcCodEsp = $laFirma['codespecialidad'] ?? '';
			$lcDscEsp = $laFirma['especialidad'] ?? '';

			if (empty($lcTextoFirma)) {

				$loUser->datosEnBlanco();

				if (empty($lcMedico) || empty($lcRegistro)) {
					if (empty($lcRegistro)) {
						if (!empty($lcUsuario)) {
							if ($loUser->cargarUsuario($lcUsuario)) {
								$lcMedico = empty($lcMedico) ? $loUser->getApellidosNombres() : $lcMedico;
								$lcRegistro = empty($lcRegistro) ? $loUser->getRegistro() : $lcRegistro;
								$lcPreNombre = $laFirma['prenombre'] ?? ($this->aConfigFirma['firmatip'][$loUser->getTipoUsuario() . ''] ??  '');
							}
						}
					} else {
						if ($loUser->cargarRegistro($lcRegistro)) {
							$lcMedico = empty($lcMedico) ? $loUser->getApellidosNombres() : $lcMedico;
							$lcPreNombre = $laFirma['prenombre'] ?? ($this->aConfigFirma['firmatip'][$loUser->getTipoUsuario() . ''] ?? '');
						}
					}
				} else {
					$lcPreNombre = $laFirma['prenombre'] ?? '';
				}
				$lcPreRegistro = $laFirma['preregistro'] ?? $this->aConfigFirma['firmatip']['0'];

				if (!empty($lcMedico)) {
					if ($lcDscEsp === false) {
						$lcDscEsp = '';
					} else if ($lcDscEsp === '') {
						if (empty($lcCodEsp)) {
							$loUser->cargarEspecialidad($loUser->getCodEspecialidad());
						} else {
							$loUser->cargarEspecialidad($lcCodEsp);
						}
						$lcDscEsp = $loUser->getDscEspecialidad();
					}
					$laRta[] = [
						'txt' => trim($lcPreNombre . ' ' . $lcMedico) . $this->cSL
							. $lcPreRegistro . ' ' . $lcRegistro . (empty($lcDscEsp) ? '' : $this->cSL . $lcDscEsp),
						'img' => $this->bUsarFirmaDigital ? $this->fGetRutaFirmaDigital($lcRegistro) : '',
					];
				}
			} else {
				$laRta[] = [
					'txt' => $lcTextoFirma,
					'img' => $this->bUsarFirmaDigital ? $this->fGetRutaFirmaDigital($lcRegistro) : '',
				];
			}
		}
		return $laRta;
	}


	/*
	 *	Obtener notas al pie de documento, si son necesarias
	 */
	private function NotasAclaratorias($taData)
	{
		if (isset($taData['aNotas'])) {
			if (is_array($taData['aNotas'])) {
				$lbCrearNota = $taData['aNotas']['notas'] ?? false;
				if ($lbCrearNota) {
					$lcOrden = $taData['aNotas']['orden'] ?? 'ASC';
					$lcForma = $taData['aNotas']['forma'] ?? '';
					$llObligaForma = $taData['aNotas']['formaobl'] ?? false;

					$laNotas = (new Doc_NotasAclaratorias())->notasAclaratoriasLibro(
						$this->aDatos['nIngreso'],
						($taData['aNotas']['codproc'] ?? $this->aDatos['cCUP']),
						$this->aDatos['nConsecCita'],
						($taData['aNotas']['forma'] ?? ''),
						($taData['aNotas']['orden'] ?? 'ASC'),
						($taData['aNotas']['formaobl'] ?? false)
					);

					if (count($laNotas) > 0) {
						$this->aContenido['Cuerpo'][] = ['lineah', []];
						$this->aContenido['Cuerpo'] = array_merge($this->aContenido['Cuerpo'], $laNotas);
					}
				}
			}
		}
	}


	/*
	 *	Procesa un documento y genera el HTML
	 */
	public function generarHTML()
	{
		require_once __DIR__ . '/class.TextHC.php';
		$loHtml = new TextHC();
		$loHtml->procesar($this->aContenido);
		$loHtml->Output();
	}


	/*
	 *	Procesa varios documentos y genera el HTML
	 *	@param array $taDatos: datos de documentos a consultar
	 *	@param int $tnOrden: dirección de ordenamiento. Puede ser SORT_ASC o SORT_DESC
	 */
	public function generarVariosHTML($taDatos, $tnOrden = SORT_ASC)
	{
		// Omitir Adjuntos y Laboratorios
		$laDocs = [];
		foreach ($taDatos as $laDoc) {
			if (!($laDoc['cTipoProgr'] == 'ADJUNTOS' || $laDoc['cTipoDocum'] == '1100')) {
				$laDocs[] = $laDoc;
			}
		}
		unset($taDatos);

		// Ordenar documentos por fecha hora
		require_once __DIR__ . '/class.AplicacionFunciones.php';
		AplicacionFunciones::ordenarArrayMulti($laDocs, 'tFechaHora', $tnOrden);

		// Objeto PDF
		require_once __DIR__ . '/class.TextHC.php';
		$loHtml = new TextHC();
		// $loHtml->adicionarPortada($taPortada['cTipId'], $taPortada['nNumId'], $taPortada['cNomPac'], $taPortada['nIngreso'], $taPortada['cFiltro']);

		$lConsultarIngreso = true;
		$lbDocumentoSolo = false;

		// Procesar Documentos en AS400
		foreach ($laDocs as $laDoc) {
			if (is_array($laDoc)) {
				$this->obtenerDocumento($laDoc, $lConsultarIngreso, $lbDocumentoSolo, true, true);
				$loHtml->procesar($this->retornarDocumento(), false);
			}
		}

		// Genera PDF final
		return $loHtml->Output();
	}


	/*
	 *	Procesa un documento y genera el PDF
	 *	@param string $tcArchivo: nombre del archivo a generar
	 *	@param string $tcSalida: tipo de salida 'I' navegador, 'D' forzar descarga, 'F' guardar en servidor, 'S' retornar como string, 'FI', 'FD'
	 */
	public function generarPDF($tcArchivo = 'LibroHcShaio.pdf', $tcSalida = 'I', $tcPassword = null)
	{
		require_once __DIR__ . '/class.PdfHC.php';
		$loPdf = new PdfHC(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $tcPassword);
		$loPdf->SetCreator($this->aPropPDF['Creator']);
		$loPdf->SetAuthor($this->aPropPDF['Author']);
		$loPdf->SetTitle($this->aPropPDF['Title']);
		$loPdf->SetSubject($this->aPropPDF['Subject']);
		$loPdf->SetKeywords($this->aPropPDF['Keywords']);
		$loPdf->SetCompression(true);

		$loPdf->procesar($this->aContenido);

		if ($tcSalida == 'S') {
			return $loPdf->Output('', $tcSalida);
		} else {
			$loPdf->Output($tcArchivo, $tcSalida);
		}
	}


	/*
	 *	Procesa varios documentos y genera el PDF
	 *	@param array $taDatos: array de datos de documentos a consultar
	 *	@param array $taPortada: array con propiedades de filtro para colocar en la portada
	 *			nIngreso: número de ingreso
	 *			cTipId: tipo de documento
	 *			nNumId: número de documento
	 *			cNomPac: Nombres y apellidos del paciente
	 *			cFiltro: Filtro aplicado
	 *	@param string $tcArchivo: nombre del archivo a generar
	 *	@param string $tcSalida: tipo de salida 'I' navegador, 'D' forzar descarga, 'F' guardar en servidor, 'S' retornar como string
	 *	@param string $tcPassword: contraseña que se debe colocar al PDF
	 *	@param boolean $tbSinUsuario: no colocar usuario que genera (cuando es generado automáticamente desde el servidor por una tarea)
	 *	@param string $tcUsuario: usuario que se debe colocar
	 *	@param boolean $tbIncluirAdjExtraInst: incluir documentos adjuntos extrainstitucionales
	 *	@param boolean $tbTodoLab: consultar todos los laboratorios en un solo llamado a Tharsis
	 */
	public function generarVariosPDF($taDatos, $taPortada, $tcArchivo = 'LibroHcShaio.pdf', $tcSalida = 'I', $tcPassword = null, $tbSinUsuario = false, $tcUsuario = '', $tbIncluirAdjExtraInst = false, $tbTodoLab = false)
	{
		// Ordenar documentos por fecha hora
		// Adjuntos sin fecha se les coloca 9999-99-99
		$lbDocumentoSolo = count($taDatos) == 1;
		$laDataLab = [];
		$laDatosSave = $taDatos;

		foreach ($taDatos as $nKey => $laDoc) {
			if ($laDoc['cTipoProgr'] == 'ADJUNTOS' && $laDoc['tFechaHora'] == '') {
				$taDatos[$nKey]['tFechaHora'] = '9999-99-99 99:99:99';
			} elseif ($tbTodoLab && $laDoc['cTipoDocum'] == '1100') {
				$laDataLab = [
					'nIngreso'		=> $laDoc['nIngreso'],
					'cTipDocPac'	=> $laDoc['cTipDocPac'],
					'nNumDocPac'	=> $laDoc['nNumDocPac'],
					'cTipoDocum'	=> $laDoc['cTipoDocum'],
					'cTipoProgr'	=> 'TODOSLAB',
				];
				unset($taDatos[$nKey]);
			}
		}
		AplicacionFunciones::ordenarArrayMulti($taDatos, 'tFechaHora');

		// Organiza adjuntos y laboratorios al final
		$laDocs = [];
		$laAdjs = [];
		$lnNumAdj = 0;
		foreach ($taDatos as $laDoc) {
			if ($laDoc['cTipoProgr'] == 'ADJUNTOS' || $laDoc['cTipoDocum'] == '1100') {
				// Adjuntos extrainstitucionales no deben aparecer en el libro
				if ($laDoc['cTipoDocum'] !== '9600' || $lbDocumentoSolo || $tbIncluirAdjExtraInst) {
					if ($laDoc['tFechaHora'] == '9999-99-99 99:99:99') {
						$laDoc['tFechaHora'] = $taDatos[$nKey]['tFechaHora'] = '';
					}
					$lnNumAdj++;
					$laDoc['cCUP'] = $lnNumAdj . ') ' . $laDoc['tFechaHora'] . ' - ' . $laDoc['cCUP'];
					$laAdjs[] = $laDoc;
				}
			} else {
				$laDocs[] = $laDoc;
			}
		}
		if (count($laDataLab)>0) {
			$laDataLab['cCUP'] = ($lnNumAdj+1).") Laboratorios del ingreso {$laDataLab['nIngreso']}";
			$laAdjs[] = $laDataLab;
		}
		$taDatos = null;

		// Objeto PDF
		require_once __DIR__ . '/class.PdfHC.php';
		$loPdf = new PdfHC(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false, false, $tcPassword, $tbSinUsuario, $tcUsuario);
		$loPdf->SetCreator($this->aPropPDF['Creator']);
		$loPdf->SetAuthor($this->aPropPDF['Author']);
		$loPdf->SetTitle($this->aPropPDF['Title']);
		$loPdf->SetSubject($this->aPropPDF['Subject']);
		$loPdf->SetKeywords($this->aPropPDF['Keywords']);
		$loPdf->SetCompression(true);

		if (count($taPortada) > 0) {
			$loPdf->adicionarPortada($taPortada['cTipId'], $taPortada['nNumId'], $taPortada['cNomPac'], $taPortada['nIngreso'], $taPortada['cFiltro']);
			$lbDocumentoSolo = false;
		}
		if (in_array($tcSalida, ['F', 'S'])) {
			$lbDocumentoSolo = false;
		}
		$lConsultarIngreso = true;

		// Procesar Documentos en AS400
		foreach ($laDocs as $laDoc) {
			if (is_array($laDoc)) {
				$this->obtenerDocumento($laDoc, $lConsultarIngreso, $lbDocumentoSolo);
				$laDatDoc = $this->retornarDocumento();
				$loPdf->procesar($laDatDoc, $laDatosSave);  //* UNO
			}
		}

		// Procesar Adjuntos
		if (count($laAdjs) > 0) {
			if (count($taPortada) > 0) {
				$loPdf->listaAdjuntos($laAdjs);
			}
			foreach ($laAdjs as $laDoc) {
				if (is_array($laDoc)) {
					if (!($laDoc['cTipoProgr']=='TODOSLAB')) {
						$this->obtenerDocumento($laDoc, $lConsultarIngreso, $lbDocumentoSolo, false);
						$laDatDoc = $this->retornarDocumento();
						$loPdf->procesar($laDatDoc);
					}
				}
			}
		}

		// Procesar laboratorios en grupo
		if ($tbTodoLab && count($laDataLab) > 0) {
			$this->obtenerDocumento($laDataLab, $lConsultarIngreso, $lbDocumentoSolo, false, false, $tbTodoLab);
			$laDatDoc = $this->retornarDocumento();
			$loPdf->procesar($laDatDoc);
		}

		// Genera PDF final
		if ($tcSalida == 'S') {
			return $loPdf->Output($tcArchivo, $tcSalida);
		} else {
			$loPdf->Output($tcArchivo, $tcSalida);
		}
	}


	/*
	 *	Retorna la variable con el contenido del documento
	 */
	public function retornarDocumento()
	{
		//return array_merge($this->aDatos, $this->aContenido);
		return $this->aContenido;
	}


	/*
	 *	Nuevos datos del documento, limpiar datos de la clase
	 */
	private function fGetRutaFirmaDigital($tnRegistro = '')
	{
		$lcArchivo = '';
		if (!empty($tnRegistro)) {
			$tnRegistro = str_pad(intval(trim($tnRegistro)), 13, '0', STR_PAD_LEFT);

			if (!empty($this->cRutaFirma)) {
				$lcArchivo = $this->cRutaFirma . $tnRegistro . '.JPG';
				if (stripos(strtolower(PHP_OS), 'win') === 0) {
					$lcArchivo = file_exists($lcArchivo) ? $lcArchivo : '';
				}
			}
		}
		return $lcArchivo;
	}


	/*
	 *	Nuevos datos del documento, limpiar datos de la clase
	 */
	private function NuevosDatos($tlCargarDatosPac = false)
	{
		$laDatos = [
			'cTipoDocum' 	=> '',
			'cTipoProgr' 	=> '',
			'tFechaHora'	=> '',
			'nConsecCita'	=> 0,
			'nConsecCons'	=> 0,
			'nConsecEvol'	=> 0,
			'nConsecDoc'	=> 0,
			'oCitaProc'		=> NULL,
			'oRealizadoPor'	=> NULL,
			'oOrdenadoPor'	=> NULL,
			'oCup'			=> NULL,
			'cCUP'			=> '',
			'cDcsCUP'		=> '',
		];
		if ($tlCargarDatosPac) {
			$laDatos = array_merge($laDatos, [
				'nIngreso'		=> 0,
				'cTipDocPac' 	=> '',
				'nNumDocPac' 	=> 0,
				'oIngrPaciente'	=> NULL,
				'cRegMedico' 	=> '',
			]);
		}
		return $laDatos;
	}


	/*
	 *	Configuración para firma digital
	 */
	private function configFirma()
	{
		global $goDb;
		$laReturn = ['firma' => [], 'firmatip' => [],];

		$laRet1 = $goDb
			->select('CL2TMA TIPO,CL3TMA COD,DE1TMA FIRMA,OP4TMA DESDE,OP7TMA HASTA')
			->from('TABMAE')
			->where(['TIPTMA' => 'LIBROHC', 'CL1TMA' => 'FIRMA'])
			->getAll('array');
		if (is_array($laRet1)) {
			$laReturn['firma'] = $laRet1;
		}

		$laRet2 = $goDb
			->select('CL2TMA, DE1TMA')
			->from('TABMAE')
			->where(['TIPTMA' => 'LIBROHC', 'CL1TMA' => 'FIRMATIP'])
			->getAll('array');
		if (is_array($laRet2)) {
			foreach ($laRet2 as $laFirmaTipo) {
				$laFirmaTipo = array_map('trim', $laFirmaTipo);
				$lcIndex = empty($laFirmaTipo['CL2TMA']) ? '0' : $laFirmaTipo['CL2TMA'];
				$laReturn['firmatip'][$lcIndex] = $laFirmaTipo['DE1TMA'];
			}
		}

		return $laReturn;
	}


	/*
	 *	Genera y retorna PDF a partir de un ingreso
	 */
	public function generarPDFxIngreso($tnIngreso = 0, $tcArchivo = 'LibroHcShaio.pdf', $tcSalida = 'I', $tcPassword = null, $tbSinUsuario = false)
	{
		$laReturn = true;
		if ($tnIngreso !== 0) {

			// Si viene un número de ingreso valida que se pueda consultar
			require_once __DIR__ . '/class.ListaDocumentos.php';
			$loListaDocs = new ListaDocumentos($tbSinUsuario);
			$lbContinuar = $tbSinUsuario ? true : (defined('HCW_NAME') ? $loListaDocs->puedeVerDocsIngreso($this->nIngreso) : false);
			if ($lbContinuar) {
				$lcTipId = '';
				$lnNumId = 0;
				$lbObtenerDocs = true;
				$lbObtenerIngresos = false;
				if ($loListaDocs->cargarDatos($tnIngreso, $lcTipId, $lnNumId, ['fecha', 'descrip'], [SORT_DESC, SORT_ASC], $lbObtenerDocs, $lbObtenerIngresos)) {
					$laLista = $loListaDocs->obtenerDocumentos();
					$lcTipId = $loListaDocs->cTipoId();
					$lnNumId = $loListaDocs->nNumeroId();
					unset($loListaDocs);

					if (count($laLista[$tnIngreso]) > 0) {
						if ($tcPassword==='usardocumento')
							$tcPassword = $lcTipId . $lnNumId;
						$laDatosDoc = [];
						foreach ($laLista[$tnIngreso] as $laDoc) {
							$lcCupDsc = $laDoc['tipoDoc'] == '1100' ? $laDoc['codCup'] . ' - ' . $laDoc['descrip'] : ($laDoc['tipoPrg'] == 'ADJUNTOS' ? $laDoc['descrip'] : $laDoc['codCup']);
							$laDatosDoc[] = [
								'nIngreso'		=> $tnIngreso,
								'cTipDocPac'	=> $lcTipId,
								'nNumDocPac'	=> $lnNumId,
								'cRegMedico'	=> $laDoc['medRegMd'],
								'cTipoDocum'	=> $laDoc['tipoDoc'],
								'cTipoProgr'	=> $laDoc['tipoPrg'],
								'tFechaHora'	=> $laDoc['fecha'],
								'nConsecCita'	=> $laDoc['cnsCita'],
								'nConsecCons'	=> $laDoc['cnsCons'],
								'nConsecEvol'	=> $laDoc['cnsEvo'],
								'nConsecDoc'	=> $laDoc['cnsDoc'],
								'cCUP'			=> $lcCupDsc,
								'cCodVia'		=> $laDoc['codvia'],
								'cSecHab'		=> $laDoc['sechab'],
							];
						}
						unset($laLista);

						$loIngreso = new Ingreso();
						$loIngreso->oPaciente->cargarPaciente($lcTipId, $lnNumId);
						$laDatosPortada = [
							'nIngreso'	=> $tnIngreso,
							'cTipId'	=> $lcTipId,
							'nNumId'	=> $lnNumId,
							'cNomPac'	=> $loIngreso->oPaciente->getNombreCompleto(),
							'cFiltro'	=> '',
						];

						if ($tcSalida == 'S') {
							return $this->generarVariosPDF($laDatosDoc, $laDatosPortada, $tcArchivo, $tcSalida, $tcPassword, $tbSinUsuario, '', false, true);
						} else {
							$this->generarVariosPDF($laDatosDoc, $laDatosPortada, $tcArchivo, $tcSalida, $tcPassword, $tbSinUsuario, '', false, true);
						}
					} else {
						$laReturn = ['No se encontraron documentos'];
					}
				} else {
					$laReturn = ['No se cargaron datos.'];
				}
			} else {
				$laReturn = ['Entidad no permitida para el usuario.'];
			}
		} else {
			$laReturn = ['Debe indicar el Número de Ingreso a consultar'];
		}
		return $laReturn;
	}


	/*
	 *	Nuevos datos del documento, limpiar datos de la clase
	 */
	private function tiposDoc()
	{
		global $goDb;
		$laReturn = [];
		$laTipos = $goDb
			->select('CL2TMA TIPO,DE1TMA DESCRIP,DE2TMA CLASE,OP1TMA FIRMA')
			->from('TABMAE')
			->where(['TIPTMA' => 'LIBHCW', 'CL1TMA' => 'TIPODOC', 'ESTTMA' => ''])
			->getAll('array');
		if (is_array($laTipos)) {
			foreach ($laTipos as $laTipo) {
				$laTipo = array_map('trim', $laTipo);
				$laReturn[$laTipo['TIPO']] = [
					'descripcion'	=> $laTipo['DESCRIP'],
					'clase'	=> $laTipo['CLASE'],
					'firmadig' => $laTipo['FIRMA'] == '1',
				];
			}
		}

		return $laReturn;
	}


	public function aContenido()
	{
		return $this->aContenido;
	}
}
