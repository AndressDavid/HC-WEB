<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.TipoDocumento.php';
use NUCLEO\Db;


class Doc_Laboratorio
{
	protected $aDocumento = [];
	protected $cRutaSrv = 'http://laboratorio.shaio.org/cgi/';
	protected $cRutaLab = 'Usuario.cgi?AccionServidor=AccionImprimirNShaio&Alias=HIS&Clave=HIS&NShaio={{oVar.nNroIng}}&CodProcedimiento={{oVar.nConCit}}';
	protected $cRutaSrvTodo = 'http://172.20.10.74:8050/cgi/';
	protected $cRutaLabTodo = 'Usuario.cgi?AccionServidor=AccionImprimirNShaioFacturacion&Alias=HIS&Clave=HIS&FechaDesde={{FecIni}}&FechaHasta={{FecFin}}&TipoDocumento={{TipDoc}}&NumeroDocumento={{NumDoc}}';
	protected $cHtml = '';
	protected $cMetodo = 'FILEGETCONTENT'; // FILEGETCONTENT o CURL
	protected $nQuitarCodigo = 10;
	protected $cTextoError = '';
	protected $bPaginar = true;
	protected $bPaginarTodos = true;
	protected $aReporte = [
				'cTitulo' => 'LABORATORIO CLÍNICO',
				'lMostrarEncabezado' => false,
				'lMostrarLogoEncabz' => false,
				'lMostrarFechaRealizado' => false,
				'lMostrarViaCama' => false,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aNotas' => ['notas'=>false,],
			];

	public function __construct()
	{
		global $goDb;
		$this->cRutaSrv = trim($goDb->obtenerTabMae1('DE2TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'SERVERW', 'ESTTMA'=>''], null, $this->cRutaSrv));
		$this->cRutaLab = trim($goDb->obtenerTabMae1('OP5TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'SERVERW', 'ESTTMA'=>''], null, $this->cRutaLab));
		$this->cRutaSrvTodo = trim($goDb->obtenerTabMae1('DE2TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'SERVERWT', 'ESTTMA'=>''], null, $this->cRutaSrv));
		$this->cRutaLabTodo = trim($goDb->obtenerTabMae1('OP5TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'SERVERWT', 'ESTTMA'=>''], null, $this->cRutaLab));
		$this->nQuitarCodigo = $goDb->obtenerTabMae1('OP3TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'DELCODIG', 'ESTTMA'=>''], null, $this->nQuitarCodigo);
		$this->bPaginarTodos = $goDb->obtenerTabMae1('OP1TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'PAGTODOS', 'ESTTMA'=>''], null, '1') == '1';
		$this->cTextoError = '<br><hr>No se encuentra o no se puede recuperar el resultado de:<br><b>||CUP||</b>.<br>Por favor comunicarse con laboratorio<hr>';
		$this->cTextoError = trim($goDb->obtenerTabMae1('DE2TMA||OP5TMA', 'LIBROHC', ['CL1TMA'=>'LABORATO', 'CL2TMA'=>'ERRWEB', 'ESTTMA'=>''], null, $this->cTextoError));
	}


	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taDatos, $tbTodosLab=false)
	{
		$lcDisplayErrors = ini_get('display_errors');
		ini_set('display_errors', '0');
		ini_set('max_execution_time', '600');
		ini_set('max_input_time', '600');
		set_time_limit(600);

		$lcCup = $taDatos['cCUP'];
		$lcTxtErr = str_replace('||CUP||', $taDatos['cCUP'], $this->cTextoError);
		$this->obtenerTextoHtml($taDatos, $tbTodosLab);

		if ($this->cHtml=='Error' || empty($this->cHtml)) {
			$this->cHtml = $lcTxtErr;

		} else {
			if (mb_strpos($this->cHtml,'Acceso denegado',0,'UTF-8')===false && mb_strpos($this->cHtml,'Acceso terminado',0,'UTF-8')===false) {
				$this->organizarTextoHtml();
				if ($this->cHtml=='Error') {
					$this->cHtml = $lcTxtErr;
				} else {
					// Para Hemograma realiza sustitución para evitar saltos de página
					//	$lnEsHemograma = mb_strpos($taDatos['cCUP'],'- 902210 -',0,'UTF-8');
					$lnEsHemograma = mb_strpos($taDatos['cCUP'],'902210',0,'UTF-8');
					if (!($lnEsHemograma===false)) {
						$laTxtBuscar = [
							'RECUENTO DE GLOBULOS ROJOS:',
							'PLAQUETAS:',
						];
						foreach ($laTxtBuscar as $lcBuscar) {
							$lcReemplaza = "</font></pre></td></tr><tr><td><pre><font color=\"#000000\" style=\"font-size:9px\" face=\"Courier New\">{$lcBuscar}";
							$this->cHtml = str_replace($lcBuscar, $lcReemplaza, $this->cHtml);
						}
					}
					if ($tbTodosLab && $this->bPaginarTodos) {
						$this->cHtml = $this->paginarLab();
					}
				}
			} else {
				$this->cHtml = $lcTxtErr;
			}
		}

		$this->aReporte['cTitulo'] = '';
		$laCuerpo = [];

		if ($tbTodosLab && is_array($this->cHtml)) {

			foreach ($this->cHtml as $lnNumLab=>$cHtmlLab) {
				if ($lnNumLab > 0) {
					$laCuerpo[] = ['saltop', ['encabezado'=>false]];
				}
				$laCuerpo[] = [
					'laboratorio',
					[
						'descrip' => $lcCup,
						'html' => mb_convert_encoding($cHtmlLab, 'ISO-8859-1', 'UTF-8'),
					],
				];
			}

		} else {
			$laCuerpo[] = [
				'laboratorio',
				[
					'descrip' => $lcCup,
					'html' => mb_convert_encoding($this->cHtml, 'ISO-8859-1', 'UTF-8'),
				],
			];
		}
		$this->aReporte['aCuerpo'] = $laCuerpo;

		ini_set('display_errors', $lcDisplayErrors);

		return $this->aReporte;
	}


	/*
	 *	Retornar texto html del laboratorio clínico
	 */
	private function obtenerTextoHtml($taDatos, $tbTodo=false)
	{
		$this->cHtml = '';
		if ($tbTodo) {
			$lcRutaSrv = $this->cRutaSrvTodo;
			$lcRutaLab = $this->cRutaLabTodo;
			$laDataLab = $this->datosLaboratoriosTodos($taDatos['nIngreso']);
			$lcFechaIni = $this->formatFechaLab($laDataLab['FechaIni']);
			$lcFechaFin = $this->formatFechaLab($laDataLab['FechaFin']);
			$lcTipoDoc = strlen($taDatos['cTipDocPac'])==1 ? (new TipoDocumento($taDatos['cTipDocPac']))->aTipo['ABRV'] : $taDatos['cTipDocPac'];
			$lcRutaLab = str_replace('{{FecIni}}', $lcFechaIni, str_replace('{{FecFin}}', $lcFechaFin, str_replace('{{TipDoc}}', $lcTipoDoc, str_replace('{{NumDoc}}', $taDatos['nNumDocPac'], $lcRutaSrv . $lcRutaLab))));
		} else {
			$lcRutaSrv = $this->cRutaSrv;
			$lcRutaLab = $this->cRutaLab;
			$lcRutaLab = str_replace('{{oVar.nNroIng}}', $taDatos['nIngreso'], str_replace('{{oVar.nConCit}}', $taDatos['nConsecCita'], $lcRutaSrv . $lcRutaLab));
		}

		try {
			//	Obtener página inicial - Esta contiene un iframe que muestra el laboratorio
			$this->obtenerHtml($lcRutaLab);

			if ($this->cHtml!=='Error' && !empty($this->cHtml)) {
				//  Eliminar comentarios
				$lbExiste = true;
				while ($lbExiste) {
					$lnIni = mb_strpos($this->cHtml, '<!--', 0, 'UTF-8');
					if ($lnIni===false) {
						$lbExiste = false;
					} else {
						$lnFin = mb_strpos($this->cHtml, '-->', 0, 'UTF-8');
						$this->cHtml = ($lnIni>0 ? mb_substr($this->cHtml, 0, $lnIni, 'UTF-8') : '') . mb_substr($this->cHtml, $lnFin+3, null, 'UTF-8');
					}
				}
				//	Obtener Ruta del iframe
				$lcURL = mb_substr($this->cHtml, mb_strpos($this->cHtml, 'Impresion.cgi', 0, 'UTF-8'), null, 'UTF-8');
				$lcURL = mb_substr($lcURL, 0, mb_strpos($lcURL, '"', 0, 'UTF-8'), 'UTF-8');
				$lcRutaLab = $lcRutaSrv . str_replace(' ', '%20', $lcURL);
				$this->obtenerHtml($lcRutaLab);
			}

		} catch(\Exception $Exception) {
			// $Exception->getMessage();
			$this->cHtml = 'Error';
		}
	}


	/* Convierte fecha de formato número a formato DD-MM-AAAA */
	public function formatFechaLab($tnFecha, $tcSep='/')
	{
		if(intval($tnFecha)==0) return '';
		$tnFecha = trim($tnFecha);
		return substr($tnFecha, 6, 2).$tcSep.substr($tnFecha, 4, 2).$tcSep.substr($tnFecha, 0, 4);
	}


	/*
	 *	Organiza y retornar texto html del laboratorio clínico
	 */
	private function organizarTextoHtml($tbTodo=false)
	{
		$lcRutaSrv = $tbTodo ? $this->cRutaSrvTodo : $this->cRutaSrv;
		try {
			// Corregir estructura con Tidy
			$laConfigTidy = [
				'indent'         => true,
				'output-xhtml'   => true,
				'wrap'           => 200
			];
			$loTidy = new \tidy;
			$loTidy->parseString($this->cHtml, $laConfigTidy, 'utf8');
			$loTidy->cleanRepair();
			$this->cHtml = $loTidy->html();

			// Obtener contenido el body
			$this->cHtml = mb_substr($this->cHtml, mb_strpos($this->cHtml, '<body', 0, 'UTF-8'), null, 'UTF-8');
			//	$this->cHtml = mb_substr($this->cHtml, mb_strpos($this->cHtml, '</div>', 0, 'UTF-8')+6, null, 'UTF-8');
			$this->cHtml = trim(mb_substr($this->cHtml, 0, mb_strpos($this->cHtml, '</body>', 0, 'UTF-8')-3, 'UTF-8'));

			if ($this->nQuitarCodigo%7==0){
				$lnPosGraf = mb_strpos($this->cHtml, 'caption="Grafico"', 0, 'UTF-8');
				if(!$lnPosGraf===false){
					// Quita Row con gráficos
					$lcHTMLIni = mb_substr($this->cHtml, 0, $lnPosGraf-1, 'UTF-8');
					$lcHTMLIni = mb_substr($lcHTMLIni, 0, mb_strrpos($lcHTMLIni,'<tr>', 0, 'UTF-8')-1, 'UTF-8');
					$lcHTMLFin = mb_substr($this->cHtml, $lnPosGraf, null, 'UTF-8');
					$lcHTMLFin = mb_substr($lcHTMLFin, mb_strpos($lcHTMLFin, '</table>', 0, 'UTF-8')+8, null, 'UTF-8');
					$lcHTMLFin = mb_substr($lcHTMLFin, mb_strpos($lcHTMLFin, '</tr>', 0, 'UTF-8')+5, null, 'UTF-8');
					$this->cHtml = $lcHTMLIni . $lcHTMLFin;
				}
			}

			//
			$this->cHtml = str_replace('<body onkeydown="return Confirmar()" onload="Init();" onscroll="Posicionar()">', '', $this->cHtml);
			// Quitar div Capa
			$lnPosIniCapa = mb_strpos($this->cHtml, '<div id="Capa"', 0, 'UTF-8');
			if (!($lnPosIniCapa===false)) {
				$lnPosFinCapa = mb_strpos($this->cHtml, '</div', $lnPosIniCapa, 'UTF-8');
				$this->cHtml = mb_substr($this->cHtml, 0, $lnPosIniCapa-1, 'UTF-8') . mb_substr($this->cHtml, $lnPosFinCapa+6, null, 'UTF-8');;
			}

			// Modifica ruta de las imágenes y gráficos
			$this->cHtml = str_replace('../Imagenes/', $lcRutaSrv . '../Imagenes/', $this->cHtml);
			$this->cHtml = str_replace('src="Graficos.cgi?', 'src="' . $lcRutaSrv . 'Graficos.cgi?', $this->cHtml);

			// Evita saltos de línea antes de valores de propiedades
			$laCambiar= ['='.chr(13).chr(10).'"','"font-size:14px"','"font-size:13px"','"font-size:12px"','"font-size:11px"','"font-size:10px"',];
			$laPor=		['="',					 '"font-size:11px"','"font-size:10px"','"font-size:9px"', '"font-size:8px"', '"font-size:7px"',];
			$this->cHtml = str_replace($laCambiar, $laPor, $this->cHtml);

			$laCambiar = ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ',];
			$laPor = ['&aacute;','&eacute;','&iacute;','&oacute;','&uacute;','&Aacute;','&Eacute;','&Iacute;','&Oacute;','&Uacute;','&ntilde;','&Ntilde;',];
			$this->cHtml = str_replace($laCambiar, $laPor, $this->cHtml);

			$this->cHtml = '<html><head>'
				.'<style type="text/css">'
				.'TD { font-family: Arial,Verdana; font-size: 9Px} '
				.'.TD { font-family: Arial,Verdana; font-size:10pt } '
				.'BODY { margin-top: 0px; margin-right: 0px; margin-bottom: 0px; margin-left: 0px } '
				//.'A { color:#000000; text-decoration:none } '
				//.'A:HOVER { text-decoration:underline } '
				.'</style>'
				.'</head><body>' . $this->cHtml . '</body></html>';

		} catch(\Exception $Exception) {
			// $Exception->getMessage();
			$this->cHtml = 'Error';
		}
	}


	/*
	 *	Retornar html del laboratorio clínico
	 */
	public function obtenerHtml($tcURL)
	{
		$this->cHtml = '';
		try {
			switch ($this->cMetodo) {
				case 'FILEGETCONTENT':
					$this->cHtml = file_get_contents($tcURL);
					break;
				case 'CURL':
					$lnCurlH = curl_init($tcURL);
					curl_setopt($lnCurlH, CURLOPT_RETURNTRANSFER, TRUE); // Devolver el resultado como cadena
					curl_setopt($lnCurlH, CURLOPT_SSL_VERIFYPEER, FALSE); // No verifique el peer del certificado ya que nuestra URL utiliza el protocolo HTTPS
					$this->cHtml = curl_exec($lnCurlH);
					curl_close($lnCurlH);
					break;
			}
		} catch(\Exception $Exception) {
			$this->cHtml = 'Error';
		}
	}


	/*
	 *	Retorna array con las diferentes páginas de laboratorio clínico
	 */
	private function paginarLab()
	{
		$lcHTMLFin = '</td></tr></table>';
		$laHtml = [];
		$lnNumLab = 0;

		$lbContinuar = true;
		while ($lbContinuar) {
			$lnPos = mb_strpos($this->cHtml, '<table class="salto"', 0, 'UTF-8');
			if ($lnPos===false) {
				$lbContinuar = false;
			} else {
				$lnPos2 = mb_strpos($this->cHtml, '<table class="salto"', $lnPos+10, 'UTF-8');
				if ($lnPos2===false) {
					$laHtml[] = mb_substr($this->cHtml, $lnPos, null, 'UTF-8');
					$lbContinuar = false;
				} else {
					$laHtml[] = mb_substr($this->cHtml, $lnPos, $lnPos2-1, 'UTF-8').$lcHTMLFin;
					$this->cHtml = mb_substr($this->cHtml, $lnPos2, null, 'UTF-8');
				}
			}
		}

		return $laHtml;
	}


	/*
	 *	Retorna array con la mínima y la máxima fecha de orden de laboratorios para un ingreso
	 */
	private function datosLaboratoriosTodos($tnIngreso)
	{
		global $goDb;
		$laIngreso = $goDb
			->select('FEIING FEC_INGRESO, FEEING FEC_EGRESO')
			->from('RIAING')
			->where(['NIGING'=>$tnIngreso])
			->get('array');
		$laFechas = $goDb
			->select('COUNT(*) NUMLAB, MIN(O.FRLORD) MIN_FEC_ORDEN, MAX(O.FRLORD) MAX_FEC_ORDEN, MIN(O.FERORD) MIN_FEC_LAB, MAX(O.FERORD) MAX_FEC_LAB')
			->from('RIAORD O')
			->where(['O.NINORD'=>$tnIngreso])
			->where("O.CODORD='353' AND O.ESTORD IN (3,59) AND (SELECT COUNT(*) FROM RESLAB R WHERE R.INGLAB=O.NINORD AND R.CONLAB=O.CCIORD)>0")
			->get('array');
		return [
			'NumLab'		=> $laFechas['NUMLAB'] ?? 0,
			'FechaIni'		=> min($laFechas['MIN_FEC_ORDEN'] ?? 99999999, $laFechas['MIN_FEC_LAB'] ?? 99999999, $laIngreso['FEC_INGRESO'] ?? 99999999),
			'FechaFin'		=> max($laFechas['MAX_FEC_ORDEN'] ?? 0, $laFechas['MAX_FEC_LAB'] ?? 0, $laIngreso['FEC_EGRESO'] ?? 0),
		];
	}

}
