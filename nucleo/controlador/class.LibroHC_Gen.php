<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.ListaDocumentos.php';
require_once __DIR__ . '/class.Documento.php';
require_once __DIR__ . '/class.PdfHC.php';
require_once __DIR__ . '/class.SmbClient.php';


/*
ALPHILDAT.LIBROHC

CAMPO        TIPO          TAMAÑO         DESCRIPCIÓN
====================================================================
NIGDHC       NUMERIC            8         NUMERO INGRESO
TIPDHC       CHARACTER          8         TIPO
ESTDHC       CHARACTER          1         ESTADO DOCUMENTO
FECDHC       NUMERIC            8,0       FECHA GENERADO
HORDHC       NUMERIC            6,0       HORA GENERADO
OBSDHC       CHARACTER        253         OBSERVACIÓN
OUTDHC       CHARACTER        253         DESTINO
FILDHC       CHARACTER         50         NOMBRE DOCUMENTO
TPDDHC       CHARACTER        200         TIPO DOCUMENTO
SIZDHC       NUMERIC           22,3       TAMAÑO DOCUMENTO
DTCDHC       CHARACTER         50         CREADO DOCUMENTO
DTMDHC       CHARACTER         50         MODIFICADO DOCUMENTO
DTLDHC       CHARACTER         50         ULTIMO ACCESO DOCUMENTO
UCRDHC       CHARACTER         10         USUARIO CREÓ
PCRDHC       CHARACTER         10         PROGRAMA CREÓ
FCRDHC       NUMERIC            8,0       FECHA CREÓ
HCRDHC       NUMERIC            6,0       HORA CREÓ
UMODHC       CHARACTER         10         USUARIO MODIFICÓ
PMODHC       CHARACTER         10         PROGRAMA MODIFICÓ
FMODHC       NUMERIC            8,0       FECHA MODIFICÓ
HMODHC       NUMERIC            6,0       HORA MODIFICÓ


ESTADOS:
0   POR GENERAR
P	EN PROCESO
G   GENERADO
E   ERROR
*/


class LibroHC_Gen
{
	/** 
	 * Propiedades del servidor donde se alojan los documentos PDF
	 * @var array
	 */ 
	protected $aServidor = [];

	/** 
	 * Ruta relativa dentro del servidor donde se guardan los archivos
	 * @var string
	 */ 
	protected $cRuta = '';

	/** 
	 * Barra de separación de directorios
	 * @var string
	 */ 
	protected $cBarra = '/';



	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->obtenerServidor();
	}


	/*
	 *	Obtener propiedades del servidor donde se van a almacenar los archivos PDF
	 */
	public function obtenerServidor()
	{
		$loTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'LIBROHC', ['CL1TMA'=>'SERVER', 'CL2TMA'=>'PDF', 'ESTTMA'=>'']);
		$lcRutaPrincipal = trim(AplicacionFunciones::getValue($loTabmae, 'DE2TMA', ''));
		$lcServerPrincipal = strstr(substr($lcRutaPrincipal, 2),'\\',true);
		$laConfigPrincipal = $this->oDb->configServer($lcServerPrincipal);

		$lcRutaPrincipal = str_replace('\\', $this->cBarra, $lcRutaPrincipal);

		$this->aServidor = [
			'ruta'=>$lcRutaPrincipal,
			'server'=>$lcServerPrincipal,
			'wrkg'=>$laConfigPrincipal['workgroup'],
			'user'=>$laConfigPrincipal['user'],
			'pass'=>$laConfigPrincipal['pass'],
		];
	}


	/*
	 *	Crea la estructura carpeta Año/Mes
	 */
	public function crearEstructuraCarpetas()
	{
		$lbReturn = false;
		
		$lcRutaAnio = 'A' . date('Y');
		$lcRutaMes = 'M' . date('m');
		$this->cRuta = $lcRutaAnio . $this->cBarra . $lcRutaMes;
		$lcRuta = $this->aServidor['ruta'] . $this->cBarra . $this->cRuta;

		if ($this->oDb->soWindows) {
			$lbReturn = file_exists($lcRuta);

		} else {
			$loSmbClient = new \SmbClient($this->aServidor['ruta'], $this->aServidor['user'], $this->aServidor['pass']);
			$lbReturn = $loSmbClient->file_exists($lcRutaAnio, $lcRutaMes) == 'dir';
			$loSmbClient = null;
			unset($loSmbClient);
		}

		// No existe la ruta, debe ser creada
		if (!$lbReturn) {
			try {
				if ($this->oDb->soWindows) {
					// En windows mkdir recursivo
					$lbReturn = mkdir($lcRuta, 0777, true);

				} else {
					$loSmbClient = new \SmbClient($this->aServidor['ruta'], $this->aServidor['user'], $this->aServidor['pass']);

					if ($loSmbClient->mkdir($lcRutaAnio)) {
						if ($loSmbClient->mkdir($this->cRuta)) {
							//$laDir = $loSmbClient->dir($lcRutaAnio, $lcRutaMes);
							//$lbReturn = count($laDir)>0;
							$lbReturn = $loSmbClient->file_exists($lcRutaAnio, $lcRutaMes) == 'dir';
						}
					}
					$loSmbClient = null;
					unset($loSmbClient);
				}
			} catch ( \Exception $loError ) {
				// "Error al crear la ruta \"{$lcRuta}\" en el servidor.";
			}
		}

		return $lbReturn;
	}


	/*
	 *	Consulta los ingresos pendientes por generar
	 */
	public function consultarIngresosPorGenerar($tcOrden='FCRDHC,HCRDHC', $tnLimit=100)
	{
		$laList = $this->oDb
			->select('NIGDHC')
			->from('LIBROHC')
			->where(['ESTDHC'=>'0'])
			->where("(SELECT COUNT(*) FROM FACCABF WHERE INGCAB=NIGDHC AND NITCAB='800130907')>0")
			->orderBy($tcOrden)
			->limit($tnLimit)
			->getAll('array');

		return is_array($laList) ? $laList : [];
	}


	/*
	 *	Genera Libro de HC en PDF
	 *	@param integer $tnIngreso Número de ingreso del que se debe generar el Libro
	 */
	public function generarLibroHcPdf($tnIngreso)
	{
		$lbReturn = false;

		$laRta = [
			'error'		=> '',
			'archivo'	=> "{$tnIngreso}.pdf",
			'ruta'		=> $this->cRuta,
			'rutac'		=> '',
		];

		// Ruta para guardar
		$lcSrvRuta = $this->aServidor['ruta'] . $this->cBarra . $laRta['ruta'];
		$laRta['rutac'] = $lcSrvRuta . $this->cBarra . $laRta['archivo'];


		// Marca el registro en estado P = en Proceso
		$lcUsuario = 'SRVWEB';
		$lcProgram = 'GENLIBHCW';
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFecha = $ltAhora->format('Ymd');
		$lcHora  = $ltAhora->format('His');
		$laCampos = [
			'ESTDHC' => 'P',				// ESTADO DOCUMENTO
			'UMODHC' => $lcUsuario,			// USUARIO MODIFICÓ
			'PMODHC' => $lcProgram,			// PROGRAMA MODIFICÓ
			'FMODHC' => $lcFecha,			// FECHA MODIFICÓ
			'HMODHC' => $lcHora,			// HORA MODIFICÓ
		];
		$laWhere = [
			'NIGDHC' => $tnIngreso,
		];
		$this->oDb->from('LIBROHC')->where($laWhere)->actualizar($laCampos);


		// Retorna los datos para el ingreso consultado
		$loIngreso=new Ingreso;
		$loIngreso->cargarIngreso($tnIngreso);

		$laDatIng['cTipId'] = $loIngreso->oPaciente->aTipoId['ABRV'] ?? '';
		$laDatIng['nNumId'] = $loIngreso->nId ?? 0;
		$laDatIng['cNombre']= $loIngreso->oPaciente->getNombreCompleto();
		unset($loIngreso);


		// Obtener lista de documentos
		$loListaDocs = new ListaDocumentos(true);
		$loListaDocs->cargarDatos($tnIngreso, '', 0, ['fecha', 'descrip'], [SORT_ASC, SORT_ASC]);
		$laListaDocs = $loListaDocs->obtenerDocumentos();

		if (count($laListaDocs[$tnIngreso] ?? []) > 0) {

			unset($loListaDocs);

			$laDocs = $laAdjs = [];
			foreach ($laListaDocs[$tnIngreso] as $laDoc) {

				// valida si es documento, laboratorio o adjunto
				$lcTipoPrg = $laDoc['tipoPrg'] ?? '';
				$lcTipoDoc = $laDoc['tipoDoc'] ?? '';
				$lbEsAdjunto = $lcTipoPrg == 'ADJUNTOS';
				$lbEsLaborat = $lcTipoDoc == '1100';

				// Los adjuntos extrainstitucionales no se imprimen
				if ($lbEsAdjunto && $laDoc['tipoDoc']=='9600') continue;

				// Los laboratorios no se están exportando
				if ($lbEsLaborat) continue;

				// Genera propiedades del documento
				$lcCup = $lbEsAdjunto ? $laDoc['fecha'] . ' - ' . $laDoc['descrip'] : $laDoc['codCup']??'';
				$laTemp = [
					'nIngreso'		=> $tnIngreso,
					'cTipDocPac'	=> $laDatIng['cTipId'],
					'nNumDocPac'	=> $laDatIng['nNumId'],
					'cRegMedico'	=> $laDoc['medRegMd']	?? '',
					'cTipoDocum'	=> $lcTipoDoc,
					'cTipoProgr'	=> $lcTipoPrg,
					'tFechaHora'	=> $laDoc['fecha']		?? '',
					'nConsecCita'	=> $laDoc['cnsCita']	?? '0',
					'nConsecCons'	=> $laDoc['cnsCons']	?? '0',
					'nConsecEvol'	=> $laDoc['cnsEvo']		?? '0',
					'nConsecDoc'	=> $laDoc['cnsDoc']		?? '0',
					'cCUP'			=> $lcCup,
					'cCodVia'		=> $laDoc['codvia']		?? '',
					'cSecHab'		=> $laDoc['sechab']		?? '',
				];

				// Clasifica los documentos
				if ($lbEsAdjunto) {
					$laAdjs[] = $laTemp;
				} else {
					$laDocs[] = $laTemp;
				}
			}
			unset($laListaDocs);

			// Objeto Documentos
			$loDocument = new Documento();

			// Objeto PDF
			$loPdf = new PdfHC(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
			$loPdf->adicionarPortada($laDatIng['cTipId'], $laDatIng['nNumId'], $laDatIng['cNombre'], $tnIngreso);

			$lbConsultarIngreso = true;
			$lbDocumentoSolo = false;
			$lbExisteArchivo = false;

			try {

				// Procesar Documentos en AS400
				foreach ($laDocs as $laDoc) {
					$loDocument->obtenerDocumento($laDoc, $lbConsultarIngreso, $lbDocumentoSolo);
					$laDatDoc = $loDocument->retornarDocumento();
					$loPdf->procesar($laDatDoc);
				}

				// Procesar Adjuntos
				if (count($laAdjs)>0) {
					$loPdf->listaAdjuntos($laAdjs);
					foreach ($laAdjs as $laDoc) {
						$loDocument->obtenerDocumento($laDoc, $lbConsultarIngreso, $lbDocumentoSolo);
						$laDatDoc = $loDocument->retornarDocumento();
						$loPdf->procesar($laDatDoc);
					}
				}

				// Genera PDF final
				if ($this->oDb->soWindows) {
					//$loPdf->Output($laRta['rutac'], 'F');
					//$lbExisteArchivo = file_exists($laRta['rutac']);

					// Crea un archivo temporal
					$lcRutaTmp = sys_get_temp_dir() . $this->cBarra . $laRta['archivo'];
					$loPdf->Output($lcRutaTmp, 'F');

					if (copy($lcRutaTmp, $laRta['rutac'])) {
						$lbExisteArchivo = file_exists($laRta['rutac']);
					}else{
						$laRta['error'] = "No se pudo copiar el archivo {$laRta['rutac']} al servidor.";
					}

				} else {
					// Crea un archivo temporal
					$lcRutaTmp = sys_get_temp_dir() . $this->cBarra . $laRta['archivo'];
					$loPdf->Output($lcRutaTmp, 'F');

					// Copiar el archivo a la ruta
					$loSmbClient = new \SmbClient($this->aServidor['ruta'], $this->aServidor['user'], $this->aServidor['pass']);
					$lcDestino = $this->cRuta . $this->cBarra . $laRta['archivo'];

					//$loSmbClient->del($lcDestino); // Es necesario eliminar si existe?
					//if ($loSmbClient->safe_put($lcRutaTmp, $lcDestino)) {
					if ($loSmbClient->put($lcRutaTmp, $lcDestino)) {
						$lbExisteArchivo = true;
						// validando que existe el archivo
						//$lbExisteArchivo = $loSmbClient->file_exists($laRta['ruta'], $laRta['archivo'])=='file';
					} else {
						$laRta['error'] = "No se pudo copiar el archivo {$laRta['rutac']} al servidor.";
					}
					$loSmbClient = null;
					unset($loSmbClient);

					// Eliminar el archivo temporal temporal
					$lnFileSizeLinux = filesize($lcRutaTmp)??0;
					$lcCreadoLinux = date('Y/m/d H:i:s', fileatime($lcRutaTmp));
					unlink($lcRutaTmp);
				}
				$loPdf = null;
				unset($loPdf);

			} catch( \Exception $loError ) {
				$laRta['error'] = "Error al intentar generar el archivo {$laRta['rutac']}";
			}


			if ($lbExisteArchivo) {

				try {
					// Actualiza registro
					$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
					$lcFecha = $ltAhora->format('Ymd');
					$lcHora  = $ltAhora->format('His');
					$fileSize = $this->oDb->soWindows ? (filesize($laRta['rutac'])??0) : ($lnFileSizeLinux ?? 0);
					$fileType = 'Adobe Acrobat Document';
					$creado = $this->oDb->soWindows ? date('Y/m/d H:i:s', fileatime($laRta['rutac'])) : ($lcCreadoLinux ?? '');

					$laCampos = [
						'ESTDHC' => 'G',				// ESTADO DOCUMENTO
						'FECDHC' => $lcFecha,			// FECHA GENERADO
						'HORDHC' => $lcHora,			// HORA GENERADO
						'FILDHC' => $laRta['archivo'],	// NOMBRE DOCUMENTO
						'OUTDHC' => $laRta['ruta'],		// DESTINO
						'TPDDHC' => $fileType,			// TIPO DOCUMENTO
						'SIZDHC' => $fileSize,			// TAMAÑO DOCUMENTO
						'DTCDHC' => $creado,			// CREADO DOCUMENTO
						'DTMDHC' => $creado,			// MODIFICADO DOCUMENTO
						'UMODHC' => $lcUsuario,			// USUARIO MODIFICÓ
						'PMODHC' => $lcProgram,			// PROGRAMA MODIFICÓ
						'FMODHC' => $lcFecha,			// FECHA MODIFICÓ
						'HMODHC' => $lcHora,			// HORA MODIFICÓ
					];
					$laWhere = [
						'NIGDHC' => $tnIngreso,
					];
					$this->oDb->from('LIBROHC')->where($laWhere)->actualizar($laCampos);

				} catch( \Exception $loError ) {
					$laRta['error'] = "Archivo Generado {$laRta['rutac']} - No se pudo hacer el registro en la base de datos";
				}

			} else {
				$laRta['error'] .= "No se generó el archivo {$laRta['rutac']}";
			}

		} else {
			$laRta['error'] = "No se encontraron documentos para el Ingreso {$tnIngreso}";
		}

		return $laRta;
	}


	/*
	 *	Consulta libros de HC Generados
	 *
	 *	@param integer $tnIngreso - número de ingreso
	 *	@param string $tcTipoDoc - tipo de documento del paciente
	 *	@param integer $tnNumDoc - número de documento del paciente
	 *	@param string $tcFechaTipo - Tipo de fecha a usar para filtrar ('', egreso, ingreso, documento, factura)
	 *	@param integer $tnFechaDesde - fecha de egreso desde
	 *	@param integer $tnFechaHasta - fecha de egreso hasta
	 *	@param string $tcNitEntidad - Nit de la entidad
	 *	@param integer $tcEstado - Estado del documento ('G'=Generado, 'P'=En proceso, '0'=Sin generar)
	 *	@param array $taEntidades - Entidades permitidas
	 */
	public function consultarLibroHcPdf($tnIngreso=0, $tcTipoDoc='', $tnNumDoc=0, $tcFechaTipo='', $tnFechaDesde=0, $tnFechaHasta=0, $tcNitEntidad='', $tcEstado='', $taEntidades=[])
	{
		$this->oDb->distinct()
			->select([
				'L.NIGDHC INGRESO',
				'L.ESTDHC ESTADO',
				'I.TIDING TIPDOC',
				'I.NIDING NUMDOC',
				'TRIM(P.NM1PAC)||\' \'||TRIM(P.NM2PAC)||\' \'||TRIM(P.AP1PAC)||\' \'||TRIM(P.AP2PAC) PACIENTE',
				'F.NITCAB NITENT',
				'T.TE1SOC ENTIDAD',
				'L.OUTDHC RUTA',
				'L.FILDHC ARCHIVO',
				])
			->from('LIBROHC L')
			->innerJoin('RIAING I', 'L.NIGDHC=I.NIGING', null)
			->innerJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->innerJoin('FACCABF F', 'L.NIGDHC=F.INGCAB AND I.NIDING<>F.NITCAB', null)
			->innerJoin('PRMTE1 T', 'DIGITS(F.NITCAB)=T.TE1COD', null)
			->where('L.TIPDHC=\'LIBROHC\' AND F.MA1CAB<>\'A\'');

		// Filtros
		if (!empty($tcFechaTipo)) {
			$lcFechaCampo = $tcFechaTipo=='egreso' ? 'I.FEEING' : 
				($tcFechaTipo=='ingreso' ? 'I.FEIING' : 
				($tcFechaTipo=='documento' ? 'L.FECDHC' : 
				($tcFechaTipo=='factura' ? 'F.FEFCAB' : '')));
			if (!empty($tnFechaDesde)) {
				$this->oDb->where($lcFechaCampo, '>=', $tnFechaDesde);
			}
			if (!empty($tnFechaHasta)) {
				$this->oDb->where($lcFechaCampo, '<=', $tnFechaHasta);
			}
		}
		if (!empty($tnIngreso)) {
			$this->oDb->where(['L.NIGDHC'=>$tnIngreso]);
		}
		if (!empty($tcTipoDoc)) {
			$this->oDb->where(['I.TIDING'=>$tcTipoDoc]);
		}
		if (!empty($tnNumDoc)) {
			$this->oDb->where(['I.NIDING'=>$tnNumDoc]);
		}
		if (!empty($tcEstado)) {
			$this->oDb->where(['L.ESTDHC'=>$tcEstado]);
		}
		if (!empty($tcNitEntidad)) {
			$this->oDb->where(['F.NITCAB'=>$tcNitEntidad]);
		}

		// Entidades permitidas
		if (is_array($taEntidades)) {
			if (count($taEntidades)>0) {
				$lcPlan0 = trim($taEntidades[0]['PLAN'] ?? '');
				if ($lcPlan0=='*') {
					// tiene acceso a todas las entidades
				} else {
					// Solo las entidades autorizadas
					$laEnt = [];
					foreach ($taEntidades as $taEntidad) {
						$laEnt[] = $taEntidad['NIT'];
					}
					$this->oDb->in('F.NITCAB', $laEnt);
				}
			} else {
				return [];
			}
		} else {
			return [];
		}


		$laLista = $this->oDb->getAll('array');
		if (is_array($laLista)) {
			foreach ($laLista as $lnIndice=>$laItem) {
				$laLista[$lnIndice] = array_map('trim',$laItem);
			}
			return $laLista;
		} else {
			return [];
		}
	}


	/*
	 *	Abrir libro de HC Generado
	 *
	 *	@param integer $tnIngreso - número de ingreso
	 *	@param string $tcCarpeta - ruta de la carpeta donde se encuentra el archivo
	 *	@param string $tcArchivo - nombre del archivo
	 */
	public function abrirLibroHcPdf($tnIngreso, $tcCarpeta, $tcArchivo)
	{
		$lcPdfData = $lcTipoMiMe = '';
		$lnFormato = $lnEstado = 0;
		$lcFile = $this->aServidor['ruta'].'/'.$tcCarpeta.'/'.$tcArchivo;
		$lcPdfData = AplicacionFunciones::obtenerRemoto($lcFile, $lnFormato, $lnEstado, $lcTipoMiMe, $this->aServidor['wrkg'], $this->aServidor['user'], $this->aServidor['pass']);

		if ($lnEstado <= 0) {
			$lcPdfData = $lcTipoMiMe = '';
			echo 'Ocurrió un error al recuperar el archivo';
		}

		if (!empty($lcPdfData)) {

			$lcNombreArchivo = "LibroHC_$tnIngreso.pdf";

			// forzar descarga
			//header("Content-type: application/octet-stream");
			//header("Content-Type: application/force-download");
			//header("Content-Disposition: attachment; filename=\"$lcNombreArchivo\"\n");
			//readfile($lcPdfData);

			// mostrar el pdf
			header('Content-type: application/pdf');
			header('Content-Disposition: inline; filename="' . $lcNombreArchivo . '"');
			header('Content-Transfer-Encoding: binary');
			header('Accept-Ranges: bytes');
			echo $lcPdfData;
		}
	}


}
