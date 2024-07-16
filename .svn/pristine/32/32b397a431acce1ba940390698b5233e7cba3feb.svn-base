<?php

namespace NUCLEO;

use setasign\Fpdi;

//	require_once __DIR__ . '/../publico/constantes.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.PdfHC.config.php';
require_once __DIR__ . '/../publico/complementos/tcpdf/6.4.4/tcpdf.php';
require_once __DIR__ . '/../publico/complementos/fpdi/2.3.6/autoload.php';
require_once __DIR__ . '/../publico/complementos/snappy/1.2.1.0/vendor/autoload.php';
require_once __DIR__ . '/../publico/complementos/tcpdf/6.2.26/tcpdf_barcodes_2d.php';

use TCPDF2DBarcode;

class PdfHC extends Fpdi\Tcpdf\Fpdi
{
	protected $cTitulo = '';
	protected $cDatLog = '';
	protected $cDatImp = '';
	protected $cEncabezado = '';
	protected $aEstilos = [
		'titulo1'	=> [PDF_FONT_MONOSPACED,	'B',	11,		0],
		'titulo2'	=> [PDF_FONT_MONOSPACED,	'B',	10,		2],
		'titulo3'	=> [PDF_FONT_MONOSPACED,	'B',	10,		4],
		'titulo4'	=> [PDF_FONT_MONOSPACED,	'B',	10,		6],
		'titulo5'	=> [PDF_FONT_MONOSPACED,	'B',	 9,		8],
		'titulo6'	=> [PDF_FONT_MONOSPACED,	'BI',	 8,		8],
		'texto7'	=> [PDF_FONT_MONOSPACED,	'',		 7,		8],
		'texto8'	=> [PDF_FONT_MONOSPACED,	'',		 8,		8],
		'texto9'	=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
		'texto10'	=> [PDF_FONT_MONOSPACED,	'',		10,		8],
		'txthtml7'	=> [PDF_FONT_MONOSPACED,	'',		 7,		8],
		'txthtml8'	=> [PDF_FONT_MONOSPACED,	'',		 8,		8],
		'txthtml9'	=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
		'txthtml10'	=> [PDF_FONT_MONOSPACED,	'',		10,		8],
		'tabla'		=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
		'tablaSL'	=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
	];
	protected $bMostrarEncabezado = true;
	protected $bMostrarLogoEncabz = true;
	protected $bMostrarPie = true;
	protected $cTextoPie = '';
	protected $cDatosPacLibro = '';
	protected $cTituloHtml = '';
	protected $aPie = PDF_FOOTER_ARRAY;
	protected $aAlineacionesPermitidas = ['L', 'C', 'R',];
	protected $nNivel = 0;
	protected $lMargin = PDF_MARGIN_LEFT;
	protected $lMarginOriginal = PDF_MARGIN_LEFT;
	protected $nAltoPagina = 0;
	protected $nAnchoPagina = 0;
	protected $nAnchoTextoTotal = 0;	// Ancho disponible para el texto (Ancho de página - márgenes izq y der)
	protected $nAnchoTexto = 0;			// Ancho disponible para el texto (Ancho de página - márgenes izq y der) cambia para columnas
	protected $nAnchoTxt = 0;			// Ancho para texto quitando sangrías
	protected $nTotalCol = 0;			// Número total de columnas
	protected $nYfinEnc = 0;
	protected $cEstiloActual = 'texto9';
	protected $nFontStretching = 92;	// Escala del texto (ancho)
	protected $nCellHeightRatio = 1.2;	// Interlineado
	protected $nColAlctual = 1;
	protected $nYpostImagen = 0;		// Posición última imagen insertada
	protected $oDatSrv = [];			// datos del servidor de firmas
	protected $cRutaWk = '';			// Ruta al programa wkHtmlToPdf

	// Contraseña de propietario
	protected $cClaveOwner = 'FCSh410_2b24f40f557eced8b93bf51f5ff7462c';
	protected $aPermisos = [
		//'print',
		'copy',
		'modify',
		'extract',
		'assemble',
	];
	protected $bImprimirLineasGuia = false;


	/*
	 *	Constructor
	 */
	public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $unicode = true, $encoding = 'UTF-8', $diskcache = false, $pdfa = false, $tcPassword = null, $tbSinUsuario = false, $tcUsuario = '')
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		$this->nAltoPagina = $this->getPageHeight();
		$this->nAnchoPagina = $this->getPageWidth();
		$this->nAnchoTextoTotal = $this->nAnchoPagina - 2 * PDF_MARGIN_LEFT;
		$this->nAnchoTexto = $this->nAnchoTextoTotal;
		$this->nYfinEnc = PDF_MARGIN_TOP_BODY;
		$this->oDatSrv = $this->fDatosImagenesFirmas();
		$lcUser = $tbSinUsuario ? '' : (empty($tcUsuario) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario() : $tcUsuario) . ' - ';
		$this->cDatImp = 'IMPRESIÓN: ' . $lcUser . date('Y-m-d H:i:s') . ' - LIBROHCWEB'; // Log de impresión
		if ((is_string($tcPassword) || is_numeric($tcPassword)) && !empty($tcPassword)) {
			$this->SetProtection($this->aPermisos, $tcPassword, $this->cClaveOwner, 0, null);
		}
		$this->cRutaWk = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'c:\\wkhtmltopdf\\bin\\wkhtmltopdf' : '/usr/local/bin/wkhtmltopdf';
	}


	/*
	 *	Encabezado
	 */
	public function Header()
	{
		$lnMargin = PDF_HEADER_LOGO_MARGIN; // $this->lMargin;
		$lnYTitulo = PDF_MARGIN_TOP_TITLE;
		$lnYfinEnc = PDF_MARGIN_TOP_BODY;
		$lnAnchoImagen = PDF_HEADER_LOGO_WIDTH;
		$lnYImagen = $lnYTitulo + 7;

		$lcFontTitulo = PDF_FONT_MONOSPACED; // PDF_FONT_NAME_DATA
		$lcFontHeader = PDF_FONT_MONOSPACED;

		$lnXEnc = $lnMargin + $lnAnchoImagen + 2;
		$lnYEnc = $lnYTitulo + 10;
		$lnAnchoEnc = ($this->nAnchoTextoTotal > 0 ? $this->nAnchoTextoTotal : ($this->getPageWidth() - 4 * PDF_MARGIN_LEFT) / 2) - $lnAnchoImagen - 3;
		$lnAltoEnc = 20;

		// Imagen
		if ($this->bMostrarLogoEncabz) {
			$image_file = K_PATH_IMAGES . PDF_HEADER_LOGO;
			$this->Image($image_file, $lnMargin, $lnYImagen, $lnAnchoImagen, 0, 'JPG');
		}

		$this->setFontStretching($this->nFontStretching);

		// Título
		$lnPosSl = ($lnPosSl = mb_strpos($this->cTitulo, chr(13), 0, 'UTF-8')) > 0 ? $lnPosSl : mb_strpos($this->cTitulo, chr(10), 0, 'UTF-8');
		if ($lnPosSl > 0) {
			$lcTitulo = trim(mb_substr($this->cTitulo, 0, $lnPosSl, 'UTF-8'));
			$lcSubTtl = trim(mb_substr($this->cTitulo, $lnPosSl + 1, null, 'UTF-8'));
		} else {
			$lcTitulo = $this->cTitulo;
			$lcSubTtl = '';
		}

		// Busca tamaño de letra desde 13 hasta el ancho del texto
		$lnFontSize = 13;
		if (strlen($lcTitulo) > 30) {
			while (true) {
				$this->SetFont($lcFontTitulo, 'B', $lnFontSize);
				if ($this->GetStringWidth($lcTitulo) < $this->nAnchoTextoTotal || $lnFontSize == 10) break;
				$lnFontSize -= 0.2;
			}
		} else {
			$this->SetFont($lcFontTitulo, 'B', $lnFontSize);
		}

		$this->SetXY($lnMargin, $lnYTitulo);
		$this->setCellHeightRatio(0.9);	// Interlineado para título principal
		$this->MultiCell($this->nAnchoTextoTotal, 0, $lcTitulo, 0, 'C');
		if (!empty($lcSubTtl)) {
			$this->SetFont($lcFontTitulo, 'B', 9);
			$this->MultiCell($this->nAnchoTextoTotal, 0, $lcSubTtl, 0, 'C');
		}

		if ($this->bMostrarEncabezado) {
			$this->setCellHeightRatio(1.1);	// Interlineado

			// Log impresión
			$this->SetFont($lcFontHeader, '', 6);
			$lnYLogImpp = $lnYEnc - 2.5;
			$this->SetXY($lnXEnc, $lnYLogImpp);
			$this->MultiCell($lnAnchoEnc, 0, $this->cDatImp, 0, 'L');
			$this->SetXY($lnXEnc, $lnYLogImpp);
			$this->MultiCell($lnAnchoEnc, 0, $this->cDatLog . str_repeat(' ', 7) . 'Pag. ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 'R');

			$this->establecerEstiloLinea();
			$this->setCellPaddings(2, 1, 1, 1);

			$this->SetFont($lcFontHeader, '', 9);
			$this->MultiCell($lnAnchoEnc, 0, $this->cEncabezado, 1, 'L', 0, 1, $lnXEnc, $lnYEnc);

			$this->nYfinEnc = $this->GetY() + 1;
			$this->nYfinEnc = $this->nYfinEnc < $lnYfinEnc ? $lnYfinEnc : $this->nYfinEnc;
		} else {
			$this->nYfinEnc = $lnYfinEnc;
		}
		$this->setCellHeightRatio($this->nCellHeightRatio);	// Interlineado
		$this->establecerNivel($this->nNivel);
		$this->SetXY($lnMargin, $this->nYfinEnc);
		$this->SetMargins(PDF_MARGIN_LEFT, $this->nYfinEnc, PDF_MARGIN_RIGHT);
		$this->lineasGuia();
		$this->setFontStyle($this->cEstiloActual);
	}


	/*
	 *	Pie de página
	 */
	private function lineasGuia()
	{
		if ($this->bImprimirLineasGuia) {
			$this->setFontStyle('texto8');
			// Guía horizontal
			$lnXMax = $this->getPageWidth();
			$this->line(0, 5, $lnXMax, 5);
			for ($lnX = 10; $lnX < $lnXMax; $lnX += 5) {
				if ($lnX % 10 == 0) {
					$this->MultiCell(10, 3, $lnX, 0, 'C', 0, 0, $lnX - 5, 1);
					$this->line($lnX, 4, $lnX, 8);
				} else {
					$this->line($lnX, 4, $lnX, 6);
				}
			}
			// Guía vertical
			$lnYMax = $this->getPageHeight();
			$this->line(5, 0, 5, $lnYMax);
			for ($lnY = 10; $lnY < $lnYMax; $lnY += 5) {
				if ($lnY % 10 == 0) {
					$this->MultiCell(10, 3, $lnY, 0, 'C', 0, 0, 2, $lnY - 4);
					$this->line(4, $lnY, 8, $lnY);
				} else {
					$this->line(4, $lnY, 6, $lnY);
				}
			}
		}
	}


	/*
	 *	Pie de página
	 */
	public function Footer()
	{
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 8);
		if ($this->bMostrarPie) {
			$nAncho = $this->nAnchoTextoTotal / 4;
			$lnY = $this->nAltoPagina - PDF_MARGIN_BOTTOM + 4;
			foreach ($this->aPie as $nIndex => $cTexto) {
				$lnX = PDF_MARGIN_LEFT + $nAncho * $nIndex;
				$this->SetXY($lnX, $lnY);
				$this->MultiCell($nAncho, 0, $cTexto, 0, 'C');
				if ($nIndex > 0) {
					$this->establecerEstiloLinea();
					$this->Line($lnX, $lnY, $lnX, $lnY + 7);
				}
			}
		} else {
			// Para adjuntos
			if (!empty($this->cTextoPie)) {
				$this->SetFont(PDF_FONT_NAME_MAIN, '', 6);
				$lnY = $this->getPageHeight() - 5;
				$lnX = PDF_MARGIN_LEFT;
				$this->SetFillColor(255, 255, 255);
				$this->MultiCell(0, 0, $this->cTextoPie, 0, 'L', 1, 1, $lnX, $lnY);
			}
		}
	}


	/*
	 *	Adiciona primera página al libro de HC
	 *	@param string $tcTipoId: tipo de documento del paciente
	 *	@param string $tcNumId: número de documento del paciente
	 *	@param string $tcNombrePac: Nombre y Apellidos del paciente
	 *	@param string $tnIngreso: Número de ingreso del paciente
	 *	@param string $tcFiltro: descripción de filtros aplicados
	 */
	public function adicionarPortada($tcTipoId = '', $tcNumId = '', $tcNombrePac = '', $tnIngreso = 0, $tcFiltro = '')
	{
		global $goDb;
		$laPortada = [];
		$lcTxtLegal = '';
		$lcDocumento = $tcTipoId . ' ' . $tcNumId;
		$laLegal = $goDb
			->select('DE2TMA || OP5TMA AS TEXTO')
			->from('TABMAE')
			->where(['TIPTMA' => 'LIBROHC', 'CL1TMA' => 'LEGAL', 'CL2TMA' => 'TEXTO'])
			->orderBy('CL3TMA')
			->getAll('array');
		if (is_array($laLegal)) {
			foreach ($laLegal as $laTxtLeg)
				$lcTxtLegal .= $laTxtLeg['TEXTO'];
			$lcTxtLegal = trim($lcTxtLegal);

			$laLegal = $goDb
				->select('DE1TMA, OP5TMA')
				->from('TABMAE')
				->where(['TIPTMA' => 'LIBROHC', 'CL1TMA' => 'LEGAL', 'CL2TMA' => 'REEMPLAZ'])
				->getAll('array');
			if (is_array($laLegal)) {
				foreach ($laLegal as $laTxtLeg) {
					$lcStr = '$lcTxtLegal = str_replace("' . trim($laTxtLeg['DE1TMA']) . '", ' . trim($laTxtLeg['OP5TMA']) . ', $lcTxtLegal);';
					eval($lcStr);
				}
				$lcTxtLegal = AplicacionFunciones::FechaNombreMes(AplicacionFunciones::FechaNombreDia($lcTxtLegal));
			}

			$lcTtlLibro = 'LIBRO DE HISTORIA CLÍNICA' . (empty($tnIngreso) && empty($tcFiltro) ? '' : ' PARCIAL');
			$tcFiltro = str_replace('|', '<br>- ', $tcFiltro);
			$this->cTituloHtml = '<span style="font-size:16px;font-weight:bold;">FUNDACIÓN CLÍNICA SHAIO</span><br><span style="font-size:14px;font-weight:bold;">' . $lcTtlLibro . '</span>';
			$this->cDatosPacLibro = "<b>Paciente</b>: $tcNombrePac<br><b>Documento</b>: $lcDocumento"
				. (empty($tnIngreso) ? '' : "<br><b>LIBRO PARCIAL</b> - SE MUESTRAN REGISTRO SOLAMENTE DE:<br>- INGRESO: $tnIngreso");
			$laPortada = [
				['cuadrotxt', ['text' => $this->cTituloHtml, 'w' => 170, 'h' => 20, 'x' => 30, 'y' => 15, 'y_abs' => true, 'aling' => 'C', 'html' => true, 'border' => 0]],
				['saltol', 15],
				['lineah', []],
				['txthtml9', $this->cDatosPacLibro . (empty($tcFiltro) ? '' : "<br>- $tcFiltro")],
				['lineah', []],
				['saltol', 3],
				['titulo5', 'A QUIEN INTERESE'],
				['saltol', 5],
				['texto9', $lcTxtLegal, 'J'],
				['saltol', 3],
				['lineah', []],
			];

			$this->cTitulo = '';
			$this->bMostrarEncabezado = false;
			$this->AddPage();
			$this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
			$this->SetMargins(PDF_MARGIN_LEFT, $this->nYfinEnc, PDF_MARGIN_RIGHT);
			$this->SetXY(10, $this->nYfinEnc);
			$this->procesarContenido($laPortada);
		}
	}


	/*
	 *	Establece propiedades de fuente de acuerdo al estilo indicado
	 *	@param $tcEstilo: string o array, estilo para aplicar. Por defecto texto9.
	 */
	private function setFontStyle($tcEstilo = 'texto9')
	{
		if ($tcEstilo) {
			if (is_array($tcEstilo)) {
				$this->SetFont($tcEstilo[0], $tcEstilo[1], $tcEstilo[2]);
				$this->cEstiloActual = $tcEstilo;
				if (isset($tcEstilo[3])) {
					$this->establecerNivel($tcEstilo[3]);
				}
			} else {
				if (isset($this->aEstilos[$tcEstilo])) {
					$laEstilo = $this->aEstilos[$tcEstilo];
					$this->SetFont($laEstilo[0], $laEstilo[1], $laEstilo[2]);
					if (isset($laEstilo[3])) {
						$this->establecerNivel($laEstilo[3]);
					}
					$this->cEstiloActual = $tcEstilo;
				}
			}
		}
		$this->setFontStretching($this->nFontStretching);	// Escala del texto
		$this->setCellHeightRatio($this->nCellHeightRatio);	// Interlineado
	}


	/*
	 *	Procesa contenido del documento
	 *	@param $taContenido: array con el contenido a adicionar al documento
	 */
	public function procesar($taContenido = [], $taDatosPacientes = [])
	{
		$this->cTitulo = $taContenido['Titulo'];
		$this->cDatLog = $taContenido['DatLog'] ?? '';
		$this->cEncabezado = isset($taContenido['Cabeza']['texto']) ? $taContenido['Cabeza']['texto'] : 'No se logró recuperar los datos';
		$this->bMostrarEncabezado = $taContenido['Cabeza']['mostrar'] ?? true;
		$this->bMostrarLogoEncabz = $taContenido['Cabeza']['logo'] ?? true;

		$this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		$this->AddPage();
		$this->bMostrarPie = $taContenido['Cabeza']['mostrarpie'] ?? true;

		$this->SetXY(10, $this->nYfinEnc);
		$this->procesarContenido($taContenido['Cuerpo'], $taDatosPacientes);
		$this->procesarContenido($taContenido['Notas']);
	}


	/*
	 *	Cuerpo y notas de un documento
	 *	@param $taContenido: array con las notas
	 */
	public function procesarContenido($taContenido = [], $taDatosPacientes = [])
	{
		$this->establecerNivel(0);
		$this->setFontStretching($this->nFontStretching);
		$this->setCellHeightRatio($this->nCellHeightRatio);
		foreach ($taContenido as $laCont) {

			if ((is_string($laCont[1]) || is_numeric($laCont[1])) && !in_array($laCont[0], ['columnas', 'saltol', 'html'])) {
				$this->setFontStyle($laCont[0]);
				if (substr($laCont[0], 0, 6) == 'titulo') {
					$lnSalto = floatval($this->getFontSizePt()) / 6;
					$this->SetY($this->GetY() + $lnSalto);
				}
				$lcAling = in_array(($lcAling = $laCont[2] ?? 'L'), $this->aAlineacionesPermitidas) ? $lcAling : 'L';
				$lbEsHtml = substr($laCont[0], 0, 7) == 'txthtml';
				if (!$lbEsHtml) {
					$laCont[1] = str_replace(chr(13), PHP_EOL, str_replace(chr(13) . chr(10), chr(13), $laCont[1]));
				}
				$this->MultiCell($this->nAnchoTxt, 0, $laCont[1], 0, $lcAling, false, 1, $this->lMargin, '', true, 0, $lbEsHtml);
			} else {
				switch (true) {
						// tablas
					case (substr($laCont[0], 0, 5) == 'tabla'):
						$this->tabla($laCont[0], $laCont[1], $laCont[2], $laCont[3] ?? []);
						break;

						// columnas
					case ($laCont[0] == 'columnas'):
						$lnNumCol = isset($laCont[1]) ? (is_numeric($laCont[1]) ? $laCont[1] : 2) : 2;
						$this->nTotalCol = $lnNumCol;
						if ($lnNumCol == 1) {
							$this->resetColumns();
							$this->nAnchoTexto = $this->nAnchoTextoTotal;
							$this->establecerNivel($this->nNivel);
						} else {
							$this->nAnchoTexto = $this->nAnchoTextoTotal / 2 - $this->lMargin;
							$this->establecerNivel($this->nNivel);
							$this->setEqualColumns($lnNumCol, $this->nAnchoTxt);
							$this->selectColumn();
						}
						break;

						// línea de firmas
					case ($laCont[0] == 'firmas'):
						$this->procesarFirmas($laCont[1], $taDatosPacientes);
						break;

						// salto de línea
					case ($laCont[0] == 'saltol'):
						$lnSalto = isset($laCont[1]) ? (is_numeric($laCont[1]) ? $laCont[1] : 5) : 5;
						$this->SetY($this->GetY() + $lnSalto);
						break;

						// salto de página
					case ($laCont[0] == 'saltop'):
						if (isset($laCont[1]['titulo']))
							$this->cTitulo = $laCont[1]['titulo'];
						if (isset($laCont[1]['encabezado']))
							$this->bMostrarEncabezado = $laCont[1]['encabezado'];
						/*
						if ($this->current_column == 0) {
							$this->selectColumn(1);
						} else {
							$this->AddPage();
							$this->selectColumn(0);
						}
*/
						$this->AddPage();
						break;

						// línea horizontal
					case ($laCont[0] == 'lineah'):
						$lnY  = $this->GetY() + ($laCont[1]['superior'] ?? 1);
						$lnX1 = PDF_MARGIN_LEFT + ($laCont[1]['x1'] ?? 0);
						$lnX2 = isset($laCont[1]['x2']) ? PDF_MARGIN_LEFT + $laCont[1]['x2'] : $this->nAnchoPagina - $this->rMargin;
						$this->establecerEstiloLinea();
						$this->Line($lnX1, $lnY, $lnX2, $lnY);
						$this->SetY($lnY + ($laCont[1]['inferior'] ?? 0));
						break;

						// cuadro de texto
					case ($laCont[0] == 'cuadrotxt'):
						// obligatorios y, w y text
						if (isset($laCont[1]['y']) && isset($laCont[1]['w']) && isset($laCont[1]['text'])) {
							$this->establecerNivel($laCont[1]['nivel'] ?? $this->aEstilos['texto9'][3]);
							$lnYIni = $this->GetY();
							$lnX = ($laCont[1]['x'] ?? 0) + $this->lMargin;
							$lnY = $laCont[1]['y'] ?? $this->GetY();
							$lnW = $laCont[1]['w'];
							$lnH = $laCont[1]['h'] ?? 0;
							$lcTexto = $laCont[1]['text'];

							$lbAbs = $laCont[1]['y_abs'] ?? false;
							if (!$lbAbs) {
								$lnY = $lnY + $lnYIni;
							}
							$lnBorder = $laCont[1]['border'] ?? 0;
							$lnWBorder = $laCont[1]['w_border'] ?? 0.1;
							$lcAling = $laCont[1]['aling'] ?? 'L';
							$lnFill = $laCont[1]['fill'] ?? 0;
							$laCBorder = $laCont[1]['c_border'] ?? [150, 150, 150, -1];
							$laCFill = $laCont[1]['c_fill'] ?? [180, 180, 180, -1];
							$laCText = $laCont[1]['c_text'] ?? [0, 0, 0];
							$laFontName = $laCont[1]['font'] ?? PDF_FONT_MONOSPACED;
							$laStyleText = $laCont[1]['style_text'] ?? '';
							$laSizeText = $laCont[1]['size_text'] ?? 9;
							$lbHtml = $laCont[1]['html'] ?? false;

							$this->establecerEstiloLinea($lnWBorder, $laCBorder);
							$this->SetFillColor($laCFill[0] ?? 180, $laCFill[1] ?? 180, $laCFill[2] ?? 180, $laCFill[3] ?? -1);
							$this->SetTextColor($laCText[0] ?? 150, $laCText[1] ?? 150, $laCText[2] ?? 150, $laCText[3] ?? -1);
							$this->SetFont($laFontName, $laStyleText, $laSizeText, '');
							$this->MultiCell($lnW, $lnH, $lcTexto, $lnBorder, $lcAling, $lnFill, 1, $lnX, $lnY, true, 0, $lbHtml);
							$this->SetTextColor(0, 0, 0, -1);
							$this->SetY($lnYIni);
						}
						break;

						// imagen
					case ($laCont[0] == 'imagen'):
						$this->establecerNivel($laCont[1]['nivel'] ?? $this->aEstilos['texto9'][3]);
						$this->nYpostImagen = 0;
						$lcArchivo = $laCont[1]['archivo'] ?? '';
						$lnW = $laCont[1]['w'] ?? 0;
						$lnH = $laCont[1]['h'] ?? 0;

						// Obligatorio archivo y ancho o alto
						if (!(empty($lcArchivo) || (empty($lnW) && empty($lnH)))) {
							$lnX = ($laCont[1]['x'] ?? 0) + $this->lMargin;
							$lnY = $this->GetY() + ($laCont[1]['y'] ?? 0);
							$lcFormato = $laCont[1]['formato'] ?? false;
							$lcNoCambiarY = $laCont[1]['y_nochange'] ?? false;

							//if(file_exists($lcArchivo)){
							$this->Image($lcArchivo, $lnX, $lnY, $lnW, $lnH, $lcFormato);
							if (!$lcNoCambiarY)
								$this->SetY($lnY + $lnH);
							$this->nYpostImagen = $this->GetY();
							//}
						}
						break;

						// adjuntos
					case ($laCont[0] == 'adjunto'):
						$lcContenido = $this->obtenerHcAdjunto($laCont[1]['file'], $laCont[1]['servidor']);
						if (!empty($lcContenido)) {

							$lcPrimerLinea = (explode(chr(10), substr($lcContenido, 0, 500)))[0];
							$lnLenPrLn = strlen($lcPrimerLinea);
							preg_match_all('!\d+!', $lcPrimerLinea, $laMatches);
							$lcPdfVersion = implode('.', $laMatches[0]);
							if ($lcPdfVersion > 1.4 || $lnLenPrLn > 9) {
								$lcRutaTmp = sys_get_temp_dir() . '/tmp' . uniqid() . '.pdf';
								//$lcRutaTmpNew=sys_get_temp_dir() . '/tmp'.uniqid().'.pdf';
								$lcRutaTmpNew = str_replace('.pdf', 'new.pdf', $lcRutaTmp);
								file_put_contents($lcRutaTmp, $lcContenido);
								$lcRutaGS = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'c:/gs/bin/gswin32' : '/usr/bin/gs';
								shell_exec($lcRutaGS . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . $lcRutaTmpNew . ' ' . $lcRutaTmp . '');
								if (file_exists($lcRutaTmpNew) === true) {
									$lcContenido = file_get_contents($lcRutaTmpNew);
								} else {
									// debe retornar un error y no adicionar el archivo
									$this->MultiCell($this->nAnchoTxt, 0, 'Error, el archivo <b>' . $laCont[1]['file'] . '</b> no se puede importar', 0, 'L', 0, 1, $this->lMargin, '', true, 0, true);
									break;
								}
							}

							// Fuente y posición encabezado
							$this->SetFont(PDF_FONT_NAME_MAIN, '', 6);
							$lnX = PDF_MARGIN_LEFT;
							$lnYenc = 3;

							// obtiene el número de páginas
							$pageCount = $this->setSourceFile(Fpdi\PdfParser\StreamReader::createByString($lcContenido));

							// iteración para cada página
							for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
								// importar página
								$templateId = $this->importPage($pageNo);

								if ($pageNo > 1) $this->AddPage();
								// inserta la página importada y ajuta el tamaño
								$this->useTemplate($templateId, ['adjustPageSize' => true]);

								// inserta encabezado y pie
								$this->SetFillColor(255, 255, 255);
								$this->MultiCell(0, 0, 'FUNDACIÓN CLÍNICA SHAIO', 0, 'L', 1, 1, $lnX, $lnYenc);
								$this->MultiCell(0, 0, $this->cDatImp, 0, 'C', 0, 1, $lnX, $lnYenc);
								$this->MultiCell(0, 0, 'Pag. ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 'R', 0, 1, $lnX, $lnYenc);
								$this->cTextoPie = $laCont[1]['descrip'] ?? '';
							}
						}
						break;

						// html
					case ($laCont[0] == 'html'):
						$this->SetY(10);
						$this->MultiCell($this->nAnchoTxt, 0, $laCont[1], 0, 'L', 0, 1, $this->lMargin, '', true, 0, true);
						break;

						// laboratorios
					case ($laCont[0] == 'laboratorio'):
						if (!empty($laCont[1]['html'])) {
							@$this->procesarLaboratorio($laCont[1]['html'], $laCont[1]['descrip'] ?? '');
						}
						break;

						// archivos externos
					case ($laCont[0] == 'urlfile'):
						$lcContenido = AplicacionFunciones::descargarArchivoRemoto($laCont[1]['urlfile'], '', 'getcontent');
						if (!empty($lcContenido)) {
							$lcContenido = $this->validaVersionPdf($lcContenido);
							if (empty($lcContenido)) {
								// debe retornar un error y no adicionar el archivo
								$this->MultiCell($this->nAnchoTxt, 0, 'Error, el archivo <b>' . $laCont[1]['urlfile'] . '</b> no se puede importar', 0, 'L', 0, 1, $this->lMargin, '', true, 0, true);
								break;
							}

							$pageCount = $this->setSourceFile(Fpdi\PdfParser\StreamReader::createByString($lcContenido));
							for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
								$templateId = $this->importPage($pageNo);
								if ($pageNo > 1) $this->AddPage();
								$this->useTemplate($templateId, ['adjustPageSize' => true]);
							}
						}
						break;
				}
			}
		}
	}


	/*
	 *	Retorna contenido PDF válido, versión 1.4
	 *	@param string $tcContenido = Descripción que se coloca en el pie de página del laboratorio
	 */
	private function validaVersionPdf($tcContenido)
	{
		$lcPrimerLinea = (explode(chr(10), substr($tcContenido, 0, 500)))[0];
		$lnLenPrLn = strlen($lcPrimerLinea);
		preg_match_all('!\d+!', $lcPrimerLinea, $laMatches);
		$lcPdfVersion = implode('.', $laMatches[0]);
		if ($lcPdfVersion > 1.4 || $lnLenPrLn > 9) {
			$lcRutaTmp = sys_get_temp_dir() . '/tmp' . uniqid() . '.pdf';
			$lcRutaTmpNew = str_replace('.pdf', 'new.pdf', $lcRutaTmp);
			file_put_contents($lcRutaTmp, $tcContenido);
			$lcRutaGS = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'c:/gs/bin/gswin32' : '/usr/bin/gs';
			shell_exec($lcRutaGS . ' -sDEVICE=pdfwrite -dCompatibilityLevel=1.4 -dNOPAUSE -dQUIET -dBATCH -sOutputFile=' . $lcRutaTmpNew . ' ' . $lcRutaTmp . '');
			if (file_exists($lcRutaTmpNew) === true) {
				$tcContenido = file_get_contents($lcRutaTmpNew);
			} else {
				$tcContenido = '';
			}
		}
		return $tcContenido;
	}


	/*
	 *	Procesa los documentos de laboratorio
	 *	@param string $tcDescripcion = Descripción que se coloca en el pie de página del laboratorio
	 *	@param string $tcHtml = Texto html del laboratorio
	 */
	private function procesarLaboratorio($tcHtml, $tcDescripcion = '')
	{
		$lcDisplayErrors = ini_get('display_errors');
		ini_set('display_errors', '0');
		try {
			$lcOutputFilePdf = sys_get_temp_dir() . '/' . uniqid('lab_', true) . '.pdf';
			$loSnappy = new \Knp\Snappy\Pdf();
			$loSnappy->setBinary($this->cRutaWk);
			$loSnappy->setTimeout(60);
			$loSnappy->setOption('page-size', 'Letter');

			// Fuente y posición encabezado
			$this->SetFont(PDF_FONT_NAME_MAIN, '', 6);
			$lnX = PDF_MARGIN_LEFT;
			$lnYenc = 3;

			try {
				@$loSnappy->generateFromHtml($tcHtml, $lcOutputFilePdf);
				// } catch (ProcessTimedOutException $Exception) {
			} catch (\Exception $Exception) {
				$this->procesarLaboratorioErr($tcDescripcion, $Exception, $lnX, $lnYenc);
				ini_set('display_errors', $lcDisplayErrors);
				return;
			}

			// iteración para cada página
			$pageCount = $this->setSourceFile($lcOutputFilePdf); // número de páginas
			for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
				// importar página
				$templateId = $this->importPage($pageNo);

				if ($pageNo > 1) $this->AddPage();
				// inserta la página importada y ajuta el tamaño
				$this->useTemplate($templateId, ['adjustPageSize' => true]);

				// inserta encabezado y pie
				$this->SetFillColor(255, 255, 255);
				$this->MultiCell(0, 0, 'FUNDACIÓN CLÍNICA SHAIO', 0, 'L', 1, 1, $lnX, $lnYenc);
				$this->MultiCell(0, 0, $this->cDatImp, 0, 'C', 0, 1, $lnX, $lnYenc);
				$this->MultiCell(0, 0, 'Pag. ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 'R', 0, 1, $lnX, $lnYenc);
				$this->cTextoPie = $tcDescripcion;
			}
			unlink($lcOutputFilePdf);
		} catch (\Exception $Exception) {
			$this->procesarLaboratorioErr($tcDescripcion, $Exception, $lnX, $lnYenc);
		} finally {
			ini_set('display_errors', $lcDisplayErrors);
		}
	}

	private function procesarLaboratorioErr($tcDescripcion, $toException, $lnX, $lnYenc)
	{
		$lcTextoError = '<br><hr>No se encuentra o no se puede recuperar el resultado' . (empty($tcDescripcion) ? '' : ' de ' . $tcDescripcion) . '.<br>Por favor comunicarse con laboratorio<hr>';
		$this->MultiCell(0, 0, 'FUNDACIÓN CLÍNICA SHAIO', 0, 'L', 1, 1, $lnX, $lnYenc);
		$this->MultiCell(0, 0, $this->cDatImp, 0, 'C', 0, 1, $lnX, $lnYenc);
		$this->MultiCell(0, 0, 'Pag. ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, 'R', 0, 1, $lnX, $lnYenc);
		$this->MultiCell(0, 0, $lcTextoError, 0, 'L', 0, 1, $lnX, $lnYenc + 20);
		$this->cTextoPie = $tcDescripcion;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 *	@param string $tcCarpeta = carpetas en el servidor donde se almacena el archivo
	 *	@param string $tcArchivo = nombre del archivo
	 *	@param boolean $tlCopiaLocal => si es true copia el archivo al servidor local (predeterminado false)
	 *	@return si $tlCopiaLocal=true retorna ruta local, si es false retorna contenido del archivo
	 */
	private function obtenerHcAdjunto($tcFile = '', $tcServidor = '')
	{
		$lcPdfData = $lcTipoMiMe = '';
		$lnFormato = 0;
		$lnEstado = 0;
		$laSrv = $tcServidor['principal'];
		$lcFile = $laSrv['ruta'] . '\\' . $tcFile;
		$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
		if ($lnEstado <= 0) {
			$lcPdfData = $lcTipoMiMe = '';
			$lnFormato = $lnEstado = 0;
			$laSrv = $tcServidor['respaldo'];
			$lcFile = $laSrv['ruta'] . '\\' . $tcFile;
			$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
		}
		return $lcPdfData;
	}


	/*
	 *	Genera lista de adjuntos
	 *	@param array $taAdjuntos = Listado de adjuntos a generar
	 */
	public function listaAdjuntos($taAdjuntos)
	{
		$laLista = [
			['cuadrotxt', ['text' => $this->cTituloHtml, 'w' => 170, 'h' => 20, 'x' => 30, 'y' => 15, 'y_abs' => true, 'aling' => 'C', 'html' => true, 'border' => 0]],
			['saltol', 15],
			['lineah', []],
			['titulo1', 'DOCUMENTOS ADJUNTOS'],
			['lineah', []],
			['txthtml9', $this->cDatosPacLibro],
			['lineah', []],
			['saltol', 2],
		];
		foreach ($taAdjuntos as $taAdjunto) {
			$laLista[] = ['texto9', mb_substr($taAdjunto['cCUP'], 0, 105, 'UTF-8'), 'J'];
		}
		$laLista[] = ['saltol', 2];
		$laLista[] = ['lineah', []];

		$this->cTitulo = '';
		$this->bMostrarEncabezado = false;
		$this->AddPage();
		$this->SetMargins(PDF_MARGIN_LEFT, $this->nYfinEnc, PDF_MARGIN_RIGHT);
		$this->SetXY(10, $this->nYfinEnc);
		$this->procesarContenido($laLista);
	}

	/*
	 *	Firmas del documento
	 *	@param $taFirmas: array con las firmas
	 */
	public function procesarFirmas($taFirmas = [], $taDatosPacientes = [])
	{
		$this->setFontStyle('texto9');
		$lnColumn = $this->getColumn();

		$lnAltoFirma = 18 * 2;
		$lnEspEntreFirmas = 2;

		$lnNumFirmas = count($taFirmas);
		if ($lnNumFirmas > 0) {

			if ($this->nTotalCol > 1) {
				$lnNumCol = 1;
				$lnAnchoCol = 96;
			} else {
				$lnNumFirmas = count($taFirmas);
				$lnNumCol = $lnNumFirmas >= 3 ? 3 : 2;
				$lnAnchoCol = intval(96 / $lnNumCol);
			}
			$lnNum = 0;

			$lcImgSinFirma = preg_replace('#^data:image/[^;]+;base64,#', '', 'data:image/jpg;base64,/9j/4AAQSkZJRgABAQEAYABgAAD/4QAiRXhpZgAATU0AKgAAAAgAAQESAAMAAAABAAEAAAAAAAD/2wBDAAIBAQEBAQIBAQECAgICAgQDAgICAgUEBAMEBgUGBgYFBgYGBwkIBgcJBwYGCAsICQoKCgoKBggLDAsKDAkKCgr/2wBDAQICAgICAgUDAwUKBwYHCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgr/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFAEBAAAAAAAAAAAAAAAAAAAAAP/EABQRAQAAAAAAAAAAAAAAAAAAAAD/2gAMAwEAAhEDEQA/AL+AA//Z');
			$lcHtml = '<table><tr>';
			foreach ($taFirmas as $laFirma) {
				if ($lnNum >= $lnNumCol) {
					$lnNum = 0;
					$lcHtml .= '</tr><tr>';
				}
				$lnNum++;
				$lbSinFirma = false;
				$lcHtml .= '<td width="' . $lnAnchoCol . '%">';
				if (!empty($laFirma['img'])) {
					$lnFormatoImg = $lnEstadoImg = 0;
					$lcTipoMiMe = '';
					$lcImgBin = AplicacionFunciones::obtenerRemoto($laFirma['img'], $lnFormatoImg, $lnEstadoImg, $lcTipoMiMe, $this->oDatSrv['workgroup'], $this->oDatSrv['user'], $this->oDatSrv['pass']);
					if ($lnEstadoImg > 0) {
						$lcImgData = 'data:image/jpg;base64,' . base64_encode($lcImgBin);
						$lcHtml .= '<table><tr><td><br></td></tr><tr><td>'
							. '<img src="@' . preg_replace('#^data:image/[^;]+;base64,#', '', $lcImgData) . '" height="' . $lnAltoFirma . '">'
							. '</td></tr><tr><td style="border-top: 0.5pt solid #969696;">'
							. str_replace([chr(13) . chr(10), chr(13), chr(10)], '<br>', $laFirma['txt'])
							. '</td></tr></table>';
					} else {
						$lbSinFirma = true;
					}
				} else {
					$lbSinFirma = true;
				}
				if ($lbSinFirma) {
					$lcHtml .= '<table><tr><td><br></td></tr><tr><td>'
						. '<img src="@' . $lcImgSinFirma . '" height="' . $lnAltoFirma . '">'
						. '</td></tr><tr><td style="border-top: 0.5pt solid #969696;">'
						. str_replace([chr(13) . chr(10), chr(13), chr(10)], '<br>', $laFirma['txt'])
						. '</td></tr></table>';
				}
				$lcHtml .= '</td><td width="2%"></td>';
			}
			$lcHtml .= '</tr></table>';
			$lbEsHtml = true;

			$this->establecerEstiloLinea(0);
			$this->MultiCell($this->nAnchoTxt, 0, $lcHtml, 0, 'L', false, 1, $this->lMargin, '', true, 0, $lbEsHtml);

			$InfoQr = $this->generarCodigoQR($taDatosPacientes);

			if ($InfoQr[0]['QR'] && !empty($InfoQr[0]['QR']) && strlen($InfoQr[0]['QR']) > 0) {

				$offsetX = 150;
				$offsetY = -20;
				$finFirmaX = $this->GetX();
				$finFirmaY = $this->GetY();
				$qrX = $finFirmaX + $offsetX;
				$qrY = $finFirmaY + $offsetY;

				$this->Image('@' . $InfoQr[0]['QR'], $qrX, $qrY, 30, 0, 'png', '', '', 'R');
			}
		}
	}

	public function generarCodigoQR($taDatosPacientes = [])
	{
		$detalleConsulta = [];

		global $goDb;
		$laDatos = $goDb
			->select('CODIGO, INGRESO, DETALLE')
			->from('CODIGOSVALIDADOC')
			->where([
				'INGRESO' => $taDatosPacientes[0]['nIngreso'],
				'CONSECUTIVO_DOC' => $taDatosPacientes[0]['nConsecCons'],
			])
			->get('array');

		$laDatos = (is_array($laDatos)) ? $laDatos : [];

		if (count($laDatos) > 0) {
			$codigo = $laDatos['CODIGO'];
			$ingreso = $laDatos['INGRESO'];
			$detalleConsulta = $laDatos['DETALLE'];
			$validado = true;
			$fechaActual = date('Y-m-d H:i:s');
			$urlRedireccion = 'https://ppd.shaio.org/nucleo/vista/validacionQr/validaQr.php';
			//$urlRedireccion = 'https://https://pacientes.shaio.org//nucleo/vista/validacionQr/validaQr.php';
			//$urlRedireccion = 'http://localhost/HCP-PHP-PACIENTE-2023/nucleo/vista/validacionQr/validaQr.php';
			$claveSecreta = 'e466f24f-942a-4141-8fc6-a6a77681610e';
			$datosCifrados = openssl_encrypt(json_encode([
				'validado' => $validado,
				'ingreso' => $ingreso,
				'detalle' => $detalleConsulta,
				'fecha' => $fechaActual
			]), 'AES-128-ECB', $claveSecreta);
			$token = hash_hmac('sha256', $codigo . $datosCifrados, $claveSecreta);
			$codigoQRContenido = $urlRedireccion . '?codigo=' . urlencode($codigo) . '&datos=' . urlencode($datosCifrados) . '&token=' . urlencode($token);
			$loBarcode = new TCPDF2DBarcode($codigoQRContenido, 'QRCODE,L');
			$codigoQRBinario = $loBarcode->getBarcodePNGData(4, 4);
			$laDetalleQr[] = [
				"QR" => $codigoQRBinario,
				"nConsecEvol" => 0
			];
		} else {
			$laDetalleQr[] = [
				"QR" => '',
				"nConsecEvol" => 99999
			];
		}

		return $laDetalleQr;
	}

	function base64url_encode($data)
	{
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}


	/*
	 *	Retorna la ubicación vertical para la siguiente firma
	 *	@param $tnEspacio: number, espacio antes de la firma
	 */
	private function getYfirma($tnEspacio = 0)
	{
		$lnY = $this->GetY() + $tnEspacio;
		$lnAltoTexto = $this->nAltoPagina - PDF_MARGIN_BOTTOM;
		if ($lnY > $lnAltoTexto) {
			$this->AddPage();
			$lnY = $this->GetY() + $tnEspacio;

			if ($this->nTotalCol > 1) {
				$this->resetColumns();
				$this->establecerNivel($this->nNivel);
				$this->setEqualColumns($this->nTotalCol, $this->nAnchoTxt);
				$this->selectColumn();
			} else {
				$this->establecerNivel($this->nNivel);
			}
		}
		return $lnY;
	}


	/*
	 *	Datos del servidor donde se encuentran las firmas digitales
	 *	@return array
	 */
	private function fDatosImagenesFirmas()
	{
		global $goDb;
		$lcSrv = $goDb->ObtenerTabMae1('OP5TMA', 'FIRMADIG', ['CL1TMA' => 'RUTA', 'ESTTMA' => ''], null, '');
		return $goDb->configServer($lcSrv);
	}


	/*
	 *	Genera tabla html y la imprime usando el comando MultiCell
	 *	@param $lcEstilo: string, estilo de tabla
	 *	@param $taTitulos: array, títulos de la tabla
	 *	@param $taDatos: array, filas de contenido de la tabla
	 *	@param $taOpciones: array, opciones de la tabla
	 */
	private function tabla($tcEstilo, $taTitulos = [], $taDatos = [], $taOpciones = [])
	{
		if (is_array($taDatos)) {
			if (count($taDatos) > 0) {
				$laEstilo = $this->aEstilos[$tcEstilo] ?? $this->aEstilos['tabla'];
				if (is_array($taOpciones)) {
					if (count($taOpciones) > 0) {
						// [PDF_FONT_MONOSPACED, '', 9, 4],
						$laEstilo[0] = $taOpciones['fn'] ?? $laEstilo[0];
						$laEstilo[1] = $taOpciones['fo'] ?? $laEstilo[1];
						$laEstilo[2] = $taOpciones['fs'] ?? $laEstilo[2];
						$laEstilo[3] = $taOpciones['l'] ?? $laEstilo[3];
					}
				} elseif (is_numeric($taOpciones)) {
					$laEstilo[3] = $taOpciones;
				} else {
					$laEstilo = $tcEstilo;
				}
				$this->setFontStyle($laEstilo);

				$lcTabla = '<table border="0" cellpadding="1" cellspacing="0">';
				$lcBorde = in_array($tcEstilo, ['tabla']) ? 'style="border-color:#969696; border-width:0.5pt 0.5pt 0.5pt 0.5pt;"' : 'border="0"';
				$lcTabla .= $this->tablaFilas($taTitulos, $lcBorde, true);
				$lcTabla .= $this->tablaFilas($taDatos, $lcBorde, false);
				$lcTabla .= '</table>';

				$lbEsHtml = true;
				$this->establecerEstiloLinea();
				$this->MultiCell($this->nAnchoTxt, 0, $lcTabla, 0, 'L', false, 1, $this->lMargin, '', true, 0, $lbEsHtml);

				$lnSpcDespuesTabla = 1;
				$this->SetY($this->GetY() + $lnSpcDespuesTabla);
			}
		}
	}


	// Genera código html de las filas de una tabla, desde un array
	private function tablaFilas($taDatos = [], $tcBorde = 'border="0"', $tlEsTitulo = false)
	{
		$lcFilas = '';
		if (count($taDatos) > 0) {
			$laStAling = ['L' => '', 'R' => 'align="right"', 'C' => 'align="center"'];
			if ($tlEsTitulo) {
				$lcFilas .= '<thead>';
				$lcTR = '<tr nobr="true" style="background-color:#EEE;">';
				$lcNI = '<b>';
				$lcNF = '</b>';
			} else {
				$lcFilas .= '<tbody>';
				$lcTR = '<tr nobr="true">';
				$lcNI = $lcNF = '';
			}
			foreach ($taDatos as $laFila) {
				// Número de celdas en la fila
				$lnCol = count($laFila['d']);
				// Alto fila
				$lnAlto = is_numeric($laFila['h'] ?? 'x') ? 'height="' . $laFila['h'] . '"' : '';
				// Ancho celdas
				$lnAncho = is_numeric($laFila['w']) ? $laFila['w'] : $this->nAnchoTexto / $lnCol;
				// Alineación celdas
				$lcAlinea = isset($laFila['a']) ? (is_string($laFila['a']) ? $laFila['a'] : 'L') : 'L';
				// Dibuja la fila
				$lcFilas .= $lcTR;
				$lnIndex = 0;
				foreach ($laFila['d'] as $lcCelda) {
					$lnW = ($laFila['w'][$lnIndex] ?? $lnAncho) * 2.82;
					$lcA = $laFila['a'][$lnIndex] ?? $lcAlinea;
					$lnF = $laFila['f'][$lnIndex] ?? 0;
					$lcF = $lnF < 2 ? '' : 'rowspan="' . $lnF . '"';
					$lcB = ($laFila['b'][$lnIndex] ?? 'S') == 'S' ? $tcBorde : 'border="0"';
					$lcFilas .= "<td width=\"{$lnW}\" {$lnAlto} {$lcF} {$lcB} {$laStAling[$lcA]}>{$lcNI}{$lcCelda}{$lcNF}</td>";
					$lnIndex++;
				}
				$lcFilas .= '</tr>';
			}
			if ($tlEsTitulo) {
				$lcFilas .= '</thead>';
			} else {
				$lcFilas .= '</tbody>';
			}
		}
		return $lcFilas;
	}


	// Establece propiedades generales de acuerdo al nivel indicado
	private function establecerNivel($tnNivel = 0)
	{
		$lnColumn = $this->getColumn();
		$lnSangría = 1;
		$this->nNivel = $tnNivel;
		$this->nAnchoTxt = $this->nAnchoTexto - $tnNivel * $lnSangría;
		$this->lMargin = ($this->nAnchoTxt + $this->lMarginOriginal) * $lnColumn + $this->lMarginOriginal + $tnNivel * $lnSangría;
	}


	// Establece estilo de líneas
	private function establecerEstiloLinea($tnAncho = 0.1, $taColor = [150, 150, 150])
	{
		$this->SetLineStyle(['width' => $tnAncho, 'color' => $taColor]);
	}


	public function cDatImp()
	{
		return $this->cDatImp;
	}

	public function establecerDatImp($tcString = '')
	{
		$this->cDatImp = $tcString;
	}



	function fnEscribirLog($tcMensaje, $tbEcho = false)
	{
		$lcRuta = __DIR__ . '/../Logs/Log_' . date('Ym');
		if (!is_dir($lcRuta)) {
			mkdir($lcRuta, 0777, true);
		}
		$lcFileLog = $lcRuta . '/LogAccion_' . date('Ymd') . '.txt';
		$lcMensaje = PHP_EOL . str_repeat('*', 100) . PHP_EOL . date('Y-m-d H:i:s') . PHP_EOL . $tcMensaje . PHP_EOL;
		$lnFile = fopen($lcFileLog, 'a');
		chmod($lcFileLog, 0777);
		fputs($lnFile, $lcMensaje);
		fclose($lnFile);
		if ($tbEcho) {
			echo $lcMensaje;
		}
	}
	// $this->fnEscribirLog('$loUser:'. PHP_EOL .var_export($loUser,true));

}
