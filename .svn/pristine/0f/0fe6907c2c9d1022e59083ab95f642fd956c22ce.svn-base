<?php
namespace NUCLEO;
require_once __DIR__ . '/../publico/constantes.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.PdfHC.config.php';

class TextHC
{
	protected $cHtml = '';
	protected $cDatLog = '';
	protected $cDatImp = '';
	protected $cDatosPacLibro = '';
	protected $cTituloHtml = '';
	protected $aAlineacionesPermitidas = ['L','C','R',];
	protected $aAlineacionesDestino = ['left','center','right',];
	protected $nNivel = 0;
	protected $lMargin = PDF_MARGIN_LEFT;
	protected $lMarginOriginal = PDF_MARGIN_LEFT;
	protected $cEstiloActual = 'texto9';
	protected $nFontStretching = 92;	// Escala del texto (ancho)
	protected $nCellHeightRatio = 1.2;	// Interlineado
	protected $nColAlctual = 1;
	protected $nYpostImagen = 0;		// Posición última imagen insertada
	protected $aEstilos = [
		'tabla'		=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
		'tablaSL'	=> [PDF_FONT_MONOSPACED,	'',		 9,		8],
	];


	/*
	 *	Constructor
	 */
	public function __construct()
	{
		$this->cDatImp = 'CONSULTA: '.$_SESSION[HCW_NAME]->oUsuario->getUsuario().' - '.date('Y-m-d H:i:s').' - LIBROHCWEB';
	}


	/*
	 *	Retorna HTML
	 *	@param $tcTipo: tipo H=retorna HTML, tipo S=retorna texto
	 */
	public function Output($tcTipo='H')
	{
		$lcHtml = "<div class=\"container-fluid hc-body\">{$this->cHtml}</div>";
		if($tcTipo=='H'){
			echo $lcHtml;
		}elseif($tcTipo=='S'){
			return $lcHtml;
		}else{
			return false;
		}
	}


	/*
	 *	Procesa contenido del documento
	 *	@param $taContenido: array con el contenido a adicionar al documento
	 */
	public function procesar($taContenido=[], $tlReiniciar=true)
	{
		if ($tlReiniciar) {
			$this->cHtml = '';
		} else {
			if(!empty($this->cHtml)){
				$this->cHtml .= '</div><div class="container-fluid hc-body">';
			}
		}
		$this->cDatLog = $taContenido['DatLog'] ?? '';
		$lcEncabezado = isset($taContenido['Cabeza']['texto']) ? $taContenido['Cabeza']['texto'] : 'No se logró recuperar los datos';

		$this->Encabezado($taContenido['Titulo'], $lcEncabezado);
		$this->procesarContenido($taContenido['Cuerpo']);
		$this->procesarContenido($taContenido['Notas']??[]);
	}


	/*
	 *	Adiciona div con texto
	 */
	public function insertDiv($tcTexto, $tcClass='texto9', $tnBorder=0, $tcAling='L')
	{
		$lcAling = 'hc-align'.$tcAling;
		$lcBorder = $tnBorder>0?'hc-border':'';
		$this->cHtml .= "<div class=\"row\"><div class=\"col hc-{$tcClass} {$lcBorder} {$lcAling}\">{$tcTexto}</div></div>";
	}


	/*
	 *	Encabezado
	 */
	public function Encabezado($tcTitulo, $tcEncabezado)
	{
		// Título
		$lnPosSl = ($lnPosSl = mb_strpos($tcTitulo, chr(13)))>0 ? $lnPosSl : mb_strpos($tcTitulo, chr(10));
		if($lnPosSl>0){
			$lcTitulo = trim(mb_substr($tcTitulo, 0, $lnPosSl, 'UTF-8'));
			$lcSubTtl = trim(mb_substr($tcTitulo, $lnPosSl+1, null, 'UTF-8'));
		} else {
			$lcTitulo = $tcTitulo;
			$lcSubTtl = '';
		}
		$this->insertDiv($lcTitulo, 'titulo', 0, 'C');
		if(!empty($lcSubTtl)) {
			$this->insertDiv($lcSubTtl, 'subtitulo', 0, 'C');
		}

		// Log impresión
		$this->insertDiv($this->cDatImp . str_repeat('&nbsp;',7) . $this->cDatLog, 'texto7', 0, 'L');

		// Encabezado
		if (!empty($tcEncabezado)) {
			//$this->insertDiv($tcEncabezado, 1, 'L', 0, 1, $lnXEnc, $lnYEnc);
			$this->insertDiv('<pre class="hc-encabezado">'.$tcEncabezado.'</pre>', 'texto9', 1, 'L');
		}
	}


	/*
	 *	Cuerpo y notas de un documento
	 *	@param $taContenido: array con los datos del documento
	 */
	public function procesarContenido($taContenido=[])
	{
		if (!is_array($taContenido)) $taContenido=[];

		foreach ($taContenido as $laCont) {

			if ( (is_string($laCont[1]) || is_numeric($laCont[1])) && $laCont[0]!=='saltol' ) {

				$lcAling = in_array(($lcAling = $laCont[2] ?? 'L'), $this->aAlineacionesPermitidas) ? $lcAling : 'L';
				$lbEsHtml = substr($laCont[0],0,7)=='txthtml';
				if (!$lbEsHtml) {
					$laCont[1] = $this->cambiaCaracteresEsp($laCont[1]);
				}
				//$this->insertDiv($laCont[1], $laCont[0], 0, $lcAling, false, 1, $this->lMargin, '', true, 0, $lbEsHtml);
				$this->insertDiv($laCont[1], $laCont[0], 0, $lcAling);

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
						//$lnSalto = isset($laCont[1]) ? ( is_numeric($laCont[1]) ? $laCont[1] : 5 ) : 5 ;
						//$this->SetY($this->GetY() + $lnSalto);
						$this->cHtml .= '<br />';
						break;

					// salto de página
					case ($laCont[0]=='saltop'):
						$this->cHtml .= '<br /><hr /><br />';
						break;

					// línea horizontal
					case ($laCont[0]=='lineah'):
						$this->cHtml .= '<hr />';
						break;

					// cuadro de texto
					case ($laCont[0]=='cuadrotxt'):
						$lcTexto = $laCont[1]['text'];
						$this->insertDiv($lcTexto, 'texto9');
/*
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

							// $this->establecerEstiloLinea($lnWBorder, $laCBorder);
							$this->SetFillColor($laCFill[0]??180,$laCFill[1]??180,$laCFill[2]??180,$laCFill[3]??-1);
							$this->SetTextColor($laCText[0]??150,$laCText[1]??150,$laCText[2]??150,$laCText[3]??-1);
							$this->SetFont('',$laStyleText,$laSizeText,'');
							$this->MultiCell($lnW, $lnH, $lcTexto, $lnBorder, $lcAling, $lnFill, 1, $lnX, $lnY, true, 0, $lbHtml);
							$this->SetTextColor(0,0,0,-1);
							$this->SetY($lnYIni);
						}
*/
						break;

					// imagen
					case ($laCont[0]=='imagen'):
						$lcArchivo = $laCont[1]['archivo'] ?? '';
						$lnW = $laCont[1]['w'] ?? 0;
						$lnH = $laCont[1]['h'] ?? 0;

						// Son obligatorios archivo, ancho y alto
						if( !(empty($lcArchivo) || empty($lnW) || empty($lnH)) ){
							if(file_exists($lcArchivo)){
								$lcImagen = "<img height=\"{$lcArchivo}\" height=\"{$lnH}\" width=\"{$lnW}\" />";
								$this->insertDiv($lcImagen, 'imagen');
							}
						}
						break;

					// adjuntos
					case ($laCont[0]=='adjunto'):
						break;

					// html
					case ($laCont[0]=='html'):
						$this->insertDiv($laCont[1], 'texto9');
						break;

					// Laboratorios
					case ($laCont[0]=='laboratorio'):
						$this->insertDiv($laCont[1]['html'], 'texto9');
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
	private function obtenerHcAdjunto($tcFile='', $tcServidor='')
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
		$lnNumFirmas = count($taFirmas);
		if ($lnNumFirmas>0) {
			$lnNumCol = $lnNumFirmas>=3 ? 3 : 2;
			$lcAncho = $lnNumCol==2 ? '50%' : '33%';
			$lnNum = 0;
			$this->cHtml.='<div class="hc-firmas"><table style="width:100%;"><tr>';
			foreach($taFirmas as $laFirma) {
				if ($lnNum >= $lnNumCol) {
					$lnNum = 0;
					$this->cHtml.='</tr><tr>';
				}
				$lnNum++;
				$lcFirma = $this->cambiaCaracteresEsp($laFirma['txt']);
				$this->cHtml.="<td style=\"width:{$lcAncho};\"><br><b>{$lcFirma}</b><br></td>";
			}
			$this->cHtml.='</tr></table></div>';
		}
	}


	/*
	 *	Cambia caracteres especiales a la representación HTML
	 *	@return string
	 */
	private function cambiaCaracteresEsp($tcTexto)
	{
		$laBuscar = ['  ']; // chr(9)
		$laCambio = ['&nbsp;&nbsp;']; // '&#09;'
		return str_replace($laBuscar, $laCambio, nl2br(htmlspecialchars($tcTexto)));
	}


	/*
	 *	Genera tabla html y la imprime usando
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

				//Obtener anchos de columnas
				$lnColMax=$lnAnchoMax=0;
				foreach (array_merge($taTitulos,$taDatos) as $laFila) {
					$lnCol = count($laFila['d']);
					$lnAncho = is_array($laFila['w'])? array_sum($laFila['w']): $laFila['w']*$lnCol;
					if ($lnColMax<$lnCol) {
						$lnColMax = $lnCol;
						$laAnchos = $laFila['w'];
					}
					if ($lnAncho>$lnAnchoMax) {
						$lnAnchoMax=$lnAncho;
					}
				}
				if (!is_array($laAnchos)) {
					$lnAncho = $laAnchos;
					$laAnchos = [];
					for ($lnI=0; $lnI<$lnColMax; $lnI++) {
						$laAnchos[] = $lnAncho;
					}
				}

				$lnWT = round($lnAnchoMax/192, 2)*100;
				$lcTablaIni = '<table cellpadding="1" cellspacing="0" class="hc-'.$tcEstilo.'" style="width:'.$lnWT.'%">';
				$lcBorde = in_array($tcEstilo, ['tabla']) ? 'style="border-color:#969696; border-width:1px;"' : 'border="0"';

				$lcTabla = '';
				$lcTabla .= $this->tablaFilas($taTitulos, $lcBorde, true, $lnColMax, $laAnchos, $lnAnchoMax);
				$lcTabla .= $this->tablaFilas($taDatos, $lcBorde, false, $lnColMax, $laAnchos, $lnAnchoMax);

				$this->cHtml .= "<div class=\"row\"><div class=\"col hc-divTabla\">{$lcTablaIni}{$lcTabla}</table></div></div>";
			}
		}
	}


	// Genera código html de las filas de una tabla, desde un array
	private function tablaFilas($taDatos=[], $tcBorde='border="0"', $tlEsTitulo=false, $tnColMax, $taAnchos, $tnAnchoTabla)
	{
		$lcFilas = '';
		if (count($taDatos)>0) {
			$laCeldasCol = [];
			foreach($taAnchos as $taAncho) { $laCeldasCol[]=0; }
			$laStAling = ['L'=>'','R'=>'align="right"','C'=>'align="center"'];
			if ($tlEsTitulo) {
				$lcFilas .= '<thead>';
				$lcTR = '<tr style="background-color:#EEE;">';
				$lcNI='<b>'; $lcNF='</b>';
			} else {
				$lcFilas .= '<tbody>';
				$lcTR = '<tr>';
				$lcNI=$lcNF='';
			}
			$nTotalFilas = count($taDatos);
			$nNumFila = 0;
			foreach ($taDatos as $laFila) {
				$nNumFila++;
				// Número de celdas en la fila
				$lnCol = count($laFila['d']);
				// Ancho celdas
				$lnAncho = is_numeric($laFila['w']) ? $laFila['w'] : $tnAnchoTabla/$lnCol;
				// Alineación celdas
				$lcAlinea = isset($laFila['a']) ? (is_string($laFila['a']) ? $laFila['a'] : 'L') : 'L';
				// Dibuja la fila
				$lcFilas .= $lcTR;
				$lnIndex = $lnIndexW = $lnIndexCol = -1;
				foreach ($laFila['d'] as $lcCelda) {
					$lnIndex++; $lnIndexW++; $lnIndexCol++;
					while($laCeldasCol[$lnIndexCol]>0) {
						$laCeldasCol[$lnIndexCol]--;
						$lnIndexCol++;
						$lnIndexW++;
					}
					$lnW = $laFila['w'][$lnIndex] ?? $lnAncho;
					$lnAnchoW = $taAnchos[$lnIndexW] ?? $lnW;
					if ($lnW>$lnAnchoW) {
						$lnCol = 1;
						while ($lnW>$lnAnchoW) {
							$lnIndexW++;
							if ($lnIndexW>=$tnColMax){
								break;
							}
							$lnAnchoW += $taAnchos[$lnIndexW];
							$lnCol++;
						}
						$lnW = 'colspan="'.$lnCol.'"';
					} else {
						$lnW = 'width="' . ($lnW/$tnAnchoTabla*100) . '%"';
					}
					$lcA = $laFila['a'][$lnIndex] ?? $lcAlinea;
					$lnF = $laFila['f'][$lnIndex] ?? 0;
					if ($lnF<2) {
						$lcF = '';
					} else {
						$lcF = 'rowspan="'.$lnF.'"';
						$laCeldasCol[$lnIndex] = $lnF-1;
					}
					$lcB = ($laFila['b'][$lnIndex] ?? 'S')=='S' ? $tcBorde : 'border="0"';
					$lcFilas .= "<td {$lnW} {$lcF} {$lcB} {$laStAling[$lcA]}>{$lcNI}{$lcCelda}{$lcNF}</td>";
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

	public function cHtml()
	{
		return $this->cHtml;
	}
}
