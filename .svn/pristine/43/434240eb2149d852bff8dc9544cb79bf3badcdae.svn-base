<?php
namespace NUCLEO;
use setasign\Fpdi;
require_once __DIR__ . '/../publico/constantes.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.PdfHC.config.php';
require_once __DIR__ . '/../publico/complementos/tcpdf/6.2.26/tcpdf.php';
require_once __DIR__ . '/../publico/complementos/fpdi/2.3.3/autoload.php';

//class PdfHCLibro extends \TCPDF
class PdfHCLibro extends Fpdi\Tcpdf\Fpdi
{
	protected $cTitulo = '';
	protected $cDatLog = '';
	protected $cEncabezado = '';
	protected $aEstilos = [
				'titulo1'	=> [PDF_FONT_MONOSPACED, 'B', 11, 0],
				'titulo2'	=> [PDF_FONT_MONOSPACED, 'B', 10, 2],
				'titulo3'	=> [PDF_FONT_MONOSPACED, 'B', 10, 4],
				'titulo4'	=> [PDF_FONT_MONOSPACED, 'B', 10, 6],
				'titulo5'	=> [PDF_FONT_MONOSPACED, 'B',  9, 8],
				'titulo6'	=> [PDF_FONT_MONOSPACED, 'BI', 8, 8],
				'texto7'	=> [PDF_FONT_MONOSPACED, '', 7,  8],
				'texto8'	=> [PDF_FONT_MONOSPACED, '', 8,  8],
				'texto9'	=> [PDF_FONT_MONOSPACED, '', 9,  8],
				'texto10'	=> [PDF_FONT_MONOSPACED, '', 10, 8],
				'txthtml7'	=> [PDF_FONT_MONOSPACED, '', 7,  8],
				'txthtml8'	=> [PDF_FONT_MONOSPACED, '', 8,  8],
				'txthtml9'	=> [PDF_FONT_MONOSPACED, '', 9,  8],
				'txthtml10'	=> [PDF_FONT_MONOSPACED, '', 10, 8],
				'tabla'		=> [PDF_FONT_MONOSPACED, '',  9, 8],
				'tablaSL'	=> [PDF_FONT_MONOSPACED, '',  9, 8],
			];
	protected $bMostrarEncabezado = true;
	protected $bMostrarLogoEncabz = true;
	protected $aPie = PDF_FOOTER_ARRAY;
	protected $aAlineacionesPermitidas = ['L','C','R',];
	protected $nNivel = 0;
	protected $lMargin = PDF_MARGIN_LEFT;
	protected $lMarginOriginal = PDF_MARGIN_LEFT;
	protected $nAltoPagina = 0;
	protected $nAnchoPagina = 0;
	protected $nAnchoTxt = 0;
	protected $nAnchoTexto = 0;
	protected $nYfinEnc = 0;
	protected $cEstiloActual = 'texto9';
	protected $nFontStretching = 92;	// Escala del texto (ancho)
	protected $nCellHeightRatio = 1.2;	// Interlineado
	protected $nColAlctual = 1;
	protected $nYpostImagen = 0;	// Posición última imagen insertada
	protected $oDatSrv = [];		// datos del servidor de firmas


	/*
	 *	Constructor
	 */
	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false)
	{
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
		$this->nAltoPagina = $this->getPageHeight();
		$this->nAnchoPagina = $this->getPageWidth();
		$this->nAnchoTexto = $this->nAnchoPagina - 2 * PDF_MARGIN_LEFT;
		$this->nYfinEnc = PDF_MARGIN_TOP_BODY;
		$this->oDatSrv = $this->fDatosFirmaDigital();
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
		$lnAnchoEnc = ($this->nAnchoTexto>0 ? $this->nAnchoTexto : ($this->getPageWidth() - 4 * PDF_MARGIN_LEFT) / 2) - $lnAnchoImagen - 3;
		$lnAltoEnc = 20;

		// Imagen
		if ($this->bMostrarLogoEncabz) {
			$image_file = K_PATH_IMAGES . PDF_HEADER_LOGO;
			$this->Image($image_file, $lnMargin, $lnYImagen, $lnAnchoImagen, 0, 'JPG');
		}

		$this->setFontStretching($this->nFontStretching);

		// Título
		//$lnPosSl = mb_strpos($this->cTitulo, chr(13));
		//$lnPosSl = $lnPosSl>0 ? $lnPosSl : mb_strpos($this->cTitulo, chr(10));
		$lnPosSl = ($lnPosSl = mb_strpos($this->cTitulo, chr(13)))>0 ? $lnPosSl : mb_strpos($this->cTitulo, chr(10));
		if($lnPosSl>0){
			$lcTitulo = trim(mb_substr($this->cTitulo, 0, $lnPosSl));
			$lcSubTtl = trim(mb_substr($this->cTitulo, $lnPosSl+1));
		} else {
			$lcTitulo = $this->cTitulo;
			$lcSubTtl = '';
		}
		$this->SetFont($lcFontTitulo, 'B', 12);
		$this->SetXY($lnMargin, $lnYTitulo);
		$this->setCellHeightRatio(0.9);	// Interlineado para título principal
		$this->MultiCell($this->nAnchoTexto, 0, $lcTitulo, 0, 'C');
		if(!empty($lcSubTtl)) {
			$this->SetFont($lcFontTitulo, 'B', 9);
			$this->MultiCell($this->nAnchoTexto, 0, $lcSubTtl, 0, 'C');
		}

		if ($this->bMostrarEncabezado) {
			$this->setCellHeightRatio(1.1);	// Interlineado

			// Log impresión
			$this->SetFont($lcFontHeader, '', 6);
			$lnYLogImpp = $lnYEnc-2.5;
			$this->SetXY($lnXEnc, $lnYLogImpp);
			$this->MultiCell($lnAnchoEnc, 0, 'IMPRESIÓN: '.$_SESSION[HCW_NAME]->oUsuario->getUsuario().' - '.date('Y-m-d H:i:s').' - LIBROHCWEB', 0, 'L');
			$this->SetXY($lnXEnc, $lnYLogImpp);
			$this->MultiCell($lnAnchoEnc, 0, $this->cDatLog.str_repeat(' ',7).'Pag. '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 'R');

			$this->establecerEstiloLinea();
			$this->setCellPaddings(2, 1, 1, 1);

			$this->SetFont($lcFontHeader, '', 9);
			$this->MultiCell($lnAnchoEnc, 0, $this->cEncabezado, 1, 'L', 0, 1, $lnXEnc, $lnYEnc);

			$this->nYfinEnc = $this->GetY() + 1;
			$this->nYfinEnc = $this->nYfinEnc<$lnYfinEnc ? $lnYfinEnc : $this->nYfinEnc;
		} else {
			$this->nYfinEnc = $lnYfinEnc;
		}
		$this->setCellHeightRatio($this->nCellHeightRatio);	// Interlineado
		$this->establecerNivel($this->nNivel);
		$this->SetXY($lnMargin, $this->nYfinEnc);
		$this->setFontStyle($this->cEstiloActual);
	}


	/*
	 *	Pie de página
	 */
	public function Footer()
	{
		$this->SetFont(PDF_FONT_NAME_MAIN, '', 8);
		$nAncho = $this->nAnchoTexto / 4;
		$lnY = $this->nAltoPagina - PDF_MARGIN_BOTTOM + 2;
		foreach($this->aPie as $nIndex => $cTexto) {
			$lnX = PDF_MARGIN_LEFT+$nAncho*$nIndex;
			$this->SetXY($lnX, $lnY);
			$this->MultiCell($nAncho, 0, $cTexto, 0, 'C');
			if ($nIndex>0) {
				$this->establecerEstiloLinea();
				$this->Line($lnX, $lnY, $lnX, $lnY+7);
			}
		}
	}


	/*
	 *	Adiciona primera página al libro de HC
	 *	@param string $tcTipoId: tipo de documento del paciente
	 *	@param string $tcNumId: número de documento del paciente
	 *	@param string $tcNombrePac: Nombre y Apellidos del paciente
	 *	@param string $tnIngreso: Número de ingreso del paciente
	 */
	public function adicionarPortada($tcTipoId='', $tcNumId='', $tcNombrePac='', $tnIngreso=0)
	{
		global $goDb;
		$laPortada = [];
		$lcTxtLegal = '';
		$lcDocumento = $tcTipoId.' '.$tcNumId;
		$laLegal = $goDb
			->select('DE2TMA || OP5TMA AS TEXTO')
			->from('TABMAE')
			->where(['TIPTMA'=>'LIBROHC','CL1TMA'=>'LEGAL','CL2TMA'=>'TEXTO'])
			->orderBy('CL3TMA')
			->getAll('array');
		if (is_array($laLegal)) {
			foreach ($laLegal as $laTxtLeg)
				$lcTxtLegal .= $laTxtLeg['TEXTO'];
			$lcTxtLegal = trim($lcTxtLegal);

			$laLegal = $goDb
				->select('DE1TMA, OP5TMA')
				->from('TABMAE')
				->where(['TIPTMA'=>'LIBROHC','CL1TMA'=>'LEGAL','CL2TMA'=>'REEMPLAZ'])
				->getAll('array');
			if (is_array($laLegal)) {
				foreach ($laLegal as $laTxtLeg) {
					$lcStr = '$lcTxtLegal = str_replace("' . trim($laTxtLeg['DE1TMA']) . '", ' . trim($laTxtLeg['OP5TMA']) . ', $lcTxtLegal);';
					eval($lcStr);
				}
				$lcTxtLegal = AplicacionFunciones::FechaNombreMes(AplicacionFunciones::FechaNombreDia($lcTxtLegal));
			}

			$lcTituloHtml = '<span style="font-size:16px;font-weight:bold;">FUNDACIÓN CLÍNICA SHAIO</span><br><span style="font-size:14px;font-weight:bold;">LIBRO DE HISTORIA CLÍNICA</span>';
			$laPortada = [
				['cuadrotxt', ['text'=>$lcTituloHtml, 'w'=>170, 'h'=>20, 'x'=>30, 'y'=>15, 'y_abs'=>true, 'aling'=>'C', 'html'=>true, 'border'=>0]],
				//['lineah', []],
				//['titulo1', 'LIBRO DE HISTORIA CLÍNICA'],
				['lineah', []],
				['txthtml9', "<b>Paciente</b>: $tcNombrePac<br><b>Documento</b>: $lcDocumento" . (empty($tnIngreso) ? '' : "<br><b>Ingreso</b>: $tnIngreso")],
				['lineah', []],
				['saltol', 3],
				['titulo5', 'A QUIEN INTERESE'],
				['saltol', 5],
				['texto9', $lcTxtLegal, 'J'],
				['saltol', 3],
				['lineah', []],
			];
		}

		if (!empty($laPortada)) {
			$this->cTitulo = '';
			$this->bMostrarEncabezado = false;
			$this->AddPage();
			$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			$this->SetMargins(PDF_MARGIN_LEFT, $this->nYfinEnc, PDF_MARGIN_RIGHT);
			$this->SetXY(10,$this->nYfinEnc);
			$this->procesarContenido($laPortada);
		}
	}


	/*
	 *	Establece propiedades de fuente de acuerdo al estilo indicado
	 *	@param $tcEstilo: string o array, estilo para aplicar. Por defecto texto9.
	 */
	private function setFontStyle($tcEstilo='texto9')
	{
		if ($tcEstilo) {
			if (is_array($tcEstilo)) {
				$this->SetFont($tcEstilo[0], $tcEstilo[1], $tcEstilo[2]);
				$this->cEstiloActual=$tcEstilo;
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
					$this->cEstiloActual=$tcEstilo;
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
	public function procesar($taContenido=[])
	{
		$this->cTitulo = $taContenido['Titulo'];
		$this->cDatLog = $taContenido['DatLog'] ?? '';
		$this->cEncabezado = isset($taContenido['Cabeza']['texto']) ? $taContenido['Cabeza']['texto'] : 'No se logró recuperar los datos';
		$this->bMostrarEncabezado = $taContenido['Cabeza']['mostrar'] ?? true;
		$this->bMostrarLogoEncabz = $taContenido['Cabeza']['logo'] ?? true;

		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$this->AddPage();

		$this->SetMargins(PDF_MARGIN_LEFT, $this->nYfinEnc, PDF_MARGIN_RIGHT);
		$this->SetXY(10,$this->nYfinEnc);

		$this->procesarContenido($taContenido['Cuerpo']);
		$this->procesarContenido($taContenido['Notas']);
	}


	/*
	 *	Cuerpo y notas de un documento
	 *	@param $taContenido: array con las notas
	 */
	public function procesarContenido($taContenido=[])
	{
		$this->establecerNivel(0);
		$this->setFontStretching($this->nFontStretching);
		$this->setCellHeightRatio($this->nCellHeightRatio);
		foreach ($taContenido as $laCont) {

			if ( (is_string($laCont[1]) || is_numeric($laCont[1])) && $laCont[0]!=='saltol' ) {
				$this->setFontStyle($laCont[0]);
				if (substr($laCont[0],0,6)=='titulo') {
					$lnSalto = $this->getFontSizePt() / 6;
					$this->SetY($this->GetY() + $lnSalto);
				}
				$lcAling = in_array(($lcAling = $laCont[2] ?? 'L'), $this->aAlineacionesPermitidas) ? $lcAling : 'L';
				$lbEsHtml = substr($laCont[0],0,7)=='txthtml';
				if (!$lbEsHtml) {
					$laCont[1] = str_replace(chr(13), PHP_EOL, str_replace(chr(13).chr(10), chr(13), $laCont[1]));
				}
				$this->MultiCell($this->nAnchoTxt, 0, $laCont[1], 0, $lcAling, false, 1, $this->lMargin, '', true, 0, $lbEsHtml);

			} else {
				switch (true) {
					// tablas
					case (substr($laCont[0],0,5)=='tabla'):
						$this->tabla($laCont[0], $laCont[1], $laCont[2], $laCont[3]??[]);
						break;

					// línea de firmas
					case ($laCont[0]=='firmas'):
						$this->procesarFirmas($laCont[1]);
						break;

					// salto de línea
					case ($laCont[0]=='saltol'):
						$lnSalto = isset($laCont[1]) ? ( is_numeric($laCont[1]) ? $laCont[1] : 5 ) : 5 ;
						$this->SetY($this->GetY() + $lnSalto);
						break;

					// salto de página
					case ($laCont[0]=='saltop'):
						if (isset($laCont[1]['titulo']))
							$this->cTitulo=$laCont[1]['titulo'];
						if (isset($laCont[1]['encabezado']))
							$this->bMostrarEncabezado=$laCont[1]['encabezado'];
/*						if ($this->current_column == 0) {
							$this->selectColumn(1);
						} else {
							$this->AddPage();
							$this->selectColumn(0);
						}
*/
						$this->AddPage();
						break;

					// línea horizontal
					case ($laCont[0]=='lineah'):
						$lnY  = $this->GetY() + ( $laCont[1]['superior'] ?? 1 );
						$lnX1 = PDF_MARGIN_LEFT + ($laCont[1]['x1'] ?? 0);
						$lnX2 = isset($laCont[1]['x2']) ? PDF_MARGIN_LEFT + $laCont[1]['x2'] : $this->nAnchoPagina - $this->rMargin;
						$this->establecerEstiloLinea();
						$this->Line($lnX1, $lnY, $lnX2, $lnY);
						$this->SetY($lnY + ( $laCont[1]['inferior'] ?? 0 ) );
						break;

					// cuadro de texto
					case ($laCont[0]=='cuadrotxt'):
						// obligatorios y, w y text
						if( isset($laCont[1]['y']) && isset($laCont[1]['w']) && isset($laCont[1]['text']) ){
							$lnYIni = $this->GetY();
							$lnX = $laCont[1]['x'] ?? $this->lMargin;
							$lnY = $laCont[1]['y'];
							$lnW = $laCont[1]['w'];
							$lnH = $laCont[1]['h'] ?? 0;
							$lcTexto = $laCont[1]['text'];

							$lbAbs = $laCont[1]['y_abs'] ?? false;
							if(!$lbAbs){
								$lnY = $lnY+$lnYIni;
							}
							$lnBorder = $laCont[1]['border'] ?? 0;
							$lnWBorder= $laCont[1]['w_border'] ?? 0.1;
							$lcAling = $laCont[1]['aling'] ?? 'L';
							$lnFill = $laCont[1]['fill'] ?? 0;
							$laCBorder= $laCont[1]['c_border'] ?? [150, 150, 150, -1];
							$laCFill= $laCont[1]['c_fill'] ?? [180, 180, 180, -1];
							$laCText= $laCont[1]['c_text'] ?? [0, 0, 0];
							$laStyleText= $laCont[1]['style_text'] ?? '';
							$laSizeText= $laCont[1]['size_text'] ?? 9;
							$lbHtml = $laCont[1]['html'] ?? false;

							$this->establecerEstiloLinea($lnWBorder, $laCBorder);
							$this->SetFillColor($laCFill[0]??180,$laCFill[1]??180,$laCFill[2]??180,$laCFill[3]??-1);
							$this->SetTextColor($laCText[0]??150,$laCText[1]??150,$laCText[2]??150,$laCText[3]??-1);
							$this->SetFont('',$laStyleText,$laSizeText,'');
							$this->MultiCell($lnW, $lnH, $lcTexto, $lnBorder, $lcAling, $lnFill, 1, $lnX, $lnY, true, 0, $lbHtml);
							$this->SetTextColor(0,0,0,-1);
							$this->SetY($lnYIni);
						}
						break;

					// imagen
					case ($laCont[0]=='imagen'):
						$this->nYpostImagen = 0;
						$lcArchivo = $laCont[1]['archivo'] ?? '';
						$lnW = $laCont[1]['w'] ?? 0;
						$lnH = $laCont[1]['h'] ?? 0;

						// Son obligatorios archivo, ancho y alto
						if( !(empty($lcArchivo) || empty($lnW) || empty($lnH)) ){
							$lnX = $laCont[1]['x'] ?? $this->lMargin;
							$lnY = $this->GetY() + ($laCont[1]['y'] ?? 0);
							$lcFormato = $laCont[1]['formato'] ?? false;
							$lcNoCambiarY = $laCont[1]['y_nochange'] ?? false;

							if(file_exists($lcArchivo)){
								//$this->Image($cArchivo, $nX, $nY, $nAncho, $nAlto, $cFormato);
								$this->Image($lcArchivo, $lnX, $lnY, $lnW, $lnH, $lcFormato);
								if(!$lcNoCambiarY)
									$this->SetY($lnY + $lnH);
								$this->nYpostImagen = $this->GetY();
							}
						}
						break;

					// adjuntos
					case ($laCont[0]=='adjunto'):
						$lcContenido = $this->fcObtenerHcAdjunto($laCont[1]['file'], $laCont[1]['servidor']);
						if ( !empty($lcContenido) ) {

							// get the page count
							$pageCount = $this->setSourceFile(Fpdi\PdfParser\StreamReader::createByString($lcContenido));

							// iterate through all pages
							for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
								// import a page
								$templateId = $this->importPage($pageNo);

								if ($pageNo > 1) $this->AddPage();
								// use the imported page and adjust the page size
								$this->useTemplate($templateId, ['adjustPageSize' => true]);

								$this->SetXY(20,   5); $this->Write(8, $laCont[1]['notaenc']??'');
								$this->SetXY(20, 230); $this->Write(8, $laCont[1]['notapie']??'');
							}
						}
						break;

					// html (laboratorios)
					case ($laCont[0]=='html'):
						$this->SetY(10);
						$this->MultiCell($this->nAnchoTxt, 0, $laCont[1], 0, 'L', false, 1, $this->lMargin, '', true, 0, true);
						break;

				}
			}
		}
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 *	@param string $tcCarpeta = carpetas en el servidor donde se almacena el archivo
	 *	@param string $tcArchivo = nombre del archivo
	 *	@param boolean $tlCopiaLocal => si es true copia el archivo al servidor local (predeterminado false)
	 *	@return si $tlCopiaLocal=true retorna ruta local, si es false retorna contenido del archivo
	 */
	private function fcObtenerHcAdjunto($tcFile='', $tcServidor='')
	{
		$lcPdfData = $lcTipoMiMe = '';
		$lnFormato = 0;
		$lnEstado = 0;
		$laSrv = $tcServidor['principal'];
		$lcFile = $laSrv['ruta'].'\\'.$tcFile;
		$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
		if ($lnEstado <= 0) {
			$lcPdfData = $lcTipoMiMe = '';
			$lnFormato = $lnEstado = 0;
			$laSrv = $tcServidor['respaldo'];
			$lcFile = $laSrv['ruta'].'\\'.$tcFile;
			$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $laSrv['workgroup'], $laSrv['user'], $laSrv['pass']);
		}
		return $lcPdfData;
	}

	/*
	 *	Firmas del documento
	 *	@param $taFirmas: array con las firmas
	 */
	public function procesarFirmas($taFirmas=[])
	{
		$this->setFontStyle('texto9');

		$lnAnchoFirma = 60;
		$lnAltoFirma = 18;
		$lnEspacFirma = $lnAltoFirma + 1;
		$lnEspEntreFirmas = 2;

		$lnNumFirmas = count($taFirmas);
		$lnNumCol = $lnNumFirmas>=3 ? 3 : 2;
		$lnNumCol = $lnNumFirmas>=3 ? 3 : 2;
		$lnAnchoCol = ($this->nAnchoTexto - $lnNumCol * $lnEspEntreFirmas)/ $lnNumCol;
		$lnNum = 0;

		if ($lnNumFirmas>0) {
			$lnY = $this->getYfirma($lnEspacFirma);
			$lnX = PDF_MARGIN_LEFT;
			foreach($taFirmas as $laFirma) {
				if ($lnNum >= $lnNumCol) {
					$lnNum = 0;
					$lnY = $this->getYfirma($lnEspacFirma);
				}
				$lnNum++;
				$lnX = PDF_MARGIN_LEFT + ($lnNum - 1) * ($lnAnchoCol + $lnEspEntreFirmas);

				if(!empty($laFirma['img'])) {
					$lnFormatoImg = $lnEstadoImg = 0;
					$lcTipoMiMe = '';
					$lcImgData = '@'.AplicacionFunciones::obtenerRemoto($laFirma['img'], $lnFormatoImg, $lnEstadoImg, $lcTipoMiMe, $this->oDatSrv['workgroup'], $this->oDatSrv['user'], $this->oDatSrv['pass']);
					if ($lnEstadoImg > 0) {
						//$this->Image($lcImgData, $lnX + ($lnAnchoCol - $lnAnchoFirma) / 2, $lnY - $lnAltoFirma + 1, $lnAnchoFirma, $lnAltoFirma, '', '', '', false, 300, '', false, false, 0, true);
						$this->Image($lcImgData, $lnX , $lnY - $lnAltoFirma + 1, $lnAnchoFirma, $lnAltoFirma, '', '', '', false, 300, '', false, false, 0, true);
					}
				}
				$this->establecerEstiloLinea();
				$this->Line($lnX, $lnY, $lnX + $lnAnchoCol, $lnY);
				$this->MultiCell($lnAnchoCol, 0, $laFirma['txt'], 0, 'L', false, 1, $lnX, $lnY);
			}
		}
	}


	/*
	 *	Datos del servidor donde se encuentran las firmas digitales
	 *	@return array
	 */
	private function fDatosFirmaDigital()
	{
		global $goDb;
		$loTabmae = $goDb->ObtenerTabMae('OP5TMA', 'FIRMADIG', ['CL1TMA'=>'RUTA','ESTTMA'=>'']);
		$lcSrv = trim(AplicacionFunciones::getValue($loTabmae, 'OP5TMA', ''));
		return $goDb->configServer($lcSrv);
	}


	/*
	 *	Retorna la ubicación vertical para la siguiente firma
	 *	@param $tnEspacio: number, espacio antes de la firma
	 */
	private function getYfirma($tnEspacio=0)
	{
		$lnY = $this->GetY()+$tnEspacio;
		$lnAltoTexto = $this->nAltoPagina - PDF_MARGIN_BOTTOM;
		if ($lnY > $lnAltoTexto) {
			$this->AddPage();
			$lnY = $this->GetY()+$tnEspacio;
		}
		return $lnY;
	}


	/*
	 *	Genera tabla html y la imprime usando el comando MultiCell
	 *	@param $lcEstilo: string, estilo de tabla
	 *	@param $taTitulos: array, títulos de la tabla
	 *	@param $taDatos: array, filas de contenido de la tabla
	 *	@param $taOpciones: array, opciones de la tabla
	 */
	private function tabla($tcEstilo, $taTitulos=[], $taDatos=[], $taOpciones=[])
	{
		if (is_array($taDatos)) {
			if (count($taDatos)>0) {
				$laEstilo=$this->aEstilos[$tcEstilo]??$this->aEstilos['tabla'];
				if(is_array($taOpciones)){
					if(count($taOpciones)>0){
						// [PDF_FONT_MONOSPACED, '', 9, 4],
						$laEstilo[0]=$taOpciones['fn']??$laEstilo[0];
						$laEstilo[1]=$taOpciones['fo']??$laEstilo[1];
						$laEstilo[2]=$taOpciones['fs']??$laEstilo[2];
						$laEstilo[3]=$taOpciones['l'] ??$laEstilo[3];
					}
				} elseif(is_numeric($taOpciones)){
					$laEstilo[3]=$taOpciones;
				} else {
					$laEstilo=$tcEstilo;
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
				$this->SetY($this->GetY()+$lnSpcDespuesTabla);

			}
		}
	}


	// Genera código html de las filas de una tabla, desde un array
	private function tablaFilas($taDatos=[], $tcBorde='border="0"', $tlEsTitulo=false)
	{
		$lcFilas = '';
		if (count($taDatos)>0){
			$laStAling = ['L'=>'','R'=>'align="right"','C'=>'align="center"'];
			if ($tlEsTitulo) {
				$lcFilas .= '<thead>';
				$lcTR = '<tr nobr="true" style="background-color:#EEE;">';
				$lcNI='<b>'; $lcNF='</b>';
			} else {
				$lcFilas .= '<tbody>';
				$lcTR = '<tr nobr="true">';
				$lcNI=$lcNF='';
			}
			foreach ($taDatos as $laFila) {
				// Número de celdas en la fila
				$lnCol = count($laFila['d']);
				// Ancho celdas
				$lnAncho = is_numeric($laFila['w']) ? $laFila['w'] : $this->nAnchoTexto/$lnCol;
				// Alineación celdas
				$lcAlinea = isset($laFila['a']) ? (is_string($laFila['a']) ? $laFila['a'] : 'L') : 'L';
				// Dibuja la fila
				$lcFilas .= $lcTR;
				$lnIndex=0;
				foreach ($laFila['d'] as $lcCelda) {
					$lnW = ($laFila['w'][$lnIndex] ?? $lnAncho) * 2.82;
					$lcA = $laFila['a'][$lnIndex] ?? $lcAlinea;
					$lnF = $laFila['f'][$lnIndex] ?? 0; $lcF = $lnF<2 ? '' : 'rowspan="'.$lnF.'"';
					$lcB = ($laFila['b'][$lnIndex] ?? 'S')=='S' ? $tcBorde : 'border="0"';
					$lcFilas .= "<td width=\"{$lnW}\" {$lcF} {$lcB} {$laStAling[$lcA]}>{$lcNI}{$lcCelda}{$lcNF}</td>";
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
	private function establecerNivel($tnNivel=0)
	{
		$lnSangría = 1;
		$this->nNivel = $tnNivel;
		$this->nAnchoTxt = $this->nAnchoTexto - $tnNivel * $lnSangría;
		$this->lMargin = $this->lMarginOriginal + $tnNivel * $lnSangría;
	}


	// Establece estilo de líneas
	private function establecerEstiloLinea($tnAncho=0.1, $taColor=[150, 150, 150])
	{
		$this->SetLineStyle(['width' => $tnAncho, 'color' => $taColor]);
	}

}
