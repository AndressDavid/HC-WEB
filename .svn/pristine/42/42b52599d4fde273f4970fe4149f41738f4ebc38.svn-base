<?php
namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';
require_once __DIR__ . '/../../webservice/complementos/nusoap-php7/0.95/lib/nusoap.php';

use NUCLEO\Db;


class MailEnviar
{
	protected $cUrlWsdl = '';
	protected $cRutaPlantillas = __DIR__ . '/../../pifs/';
	protected $cRutaSrv;
	protected $lcBar;
	protected $cDirAdjutos;
	protected $cDateTime;
	protected $cError;
	protected $oDb;
	protected $aClaves = [
			'BCC' => ['tcBCC'],
			'CC' => ['tcCC'],
			'PASSWORD' => ['tcPass'],
			'PORT' => ['tnPort'],
			'SENDUSER' => ['tcUser','tcFrom'],
			'SERVER' => ['tcServer'],
			'SUBJECT' => ['tcSubject'],
			'TO' => ['tcTO'],
		];
	public $aConfiguracion;
	public $cPlantilla;


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->cDateTime = date('Y-m-d H:i:s');
		$this->cDirAdjutos = date('YmdHis').uniqid();

		$this->cUrlWsdl = $this->oDb->obtenerTabMae1('DE2TMA', 'MAILSETT', 'cl1tma=\'PARAMETR\' AND cl2tma=\'URLWEB\' AND cl3tma=\'\' AND ESTTMA=\'\'', null, $this->cUrlWsdl);
		if ($this->oDb->soWindows) {
			$this->cRutaSrv = '\\\\HCWP.SHAIO.ORG\\MAILSERVER\\';
			$this->lcBar = '\\';
		} else {
			$this->cRutaSrv = __DIR__ . '/../../webservice/publico/mail/attachs/';
			$this->lcBar = '/';
		}
	}

	public function enviar($taConfiguracion=[])
	{
		$this->cError = $lcResult = '';
		$this->iniciarConfiguracion($taConfiguracion);
		$loCliente = new \nusoap_client($this->cUrlWsdl,'wsdl');
		$this->cError = $loCliente->getError();
		if ($this->cError) {
			return;
		} else {
			$this->aConfiguracion['tcSubject'] = mb_convert_encoding($this->aConfiguracion['tcSubject'],'ISO-8859-1');
			$this->aConfiguracion['tcBody'] = mb_convert_encoding($this->aConfiguracion['tcBody'],'ISO-8859-1');
			if (empty($this->aConfiguracion['tcAttachServerFilesID'])) {
				unset($this->aConfiguracion['tcAttachServerFilesID']);
				$lcResult = $loCliente->call('SendMail', $this->aConfiguracion);
			} else {
				$lcResult = $loCliente->call('SendMailAttach', $this->aConfiguracion);
			}
		}
		return $lcResult;
	}


	/*
	 *	Obtiene los parámetros de configuración de correo en TABMAE con TIPTMA='MAILSETT'
	 *	@param string $tcIndice: clave en cl2tma
	 *	@return array con los elementos config (datos de configuración) y otros (otros datos)
	 */
	public function obtenerConfiguracion($tcIndice)
	{
		$laConfig = $laOtras = [];
		$laTabla = $this->oDb
			->select('TRIM(CL3TMA) CAMPO, TRIM(CL4TMA) INDICE, TRIM(DE2TMA) AS DESCRIP')
			->from('TABMAE')
			->where("TIPTMA='MAILSETT' AND CL1TMA='PARAMETR' AND CL2TMA='$tcIndice' AND CL3TMA<>'' AND ESTTMA='' AND DE2TMA<>''")
			->getAll('array');
		if (is_array($laTabla)) {
			$laClaves = array_keys($this->aClaves);
			$laConfig['tcTO'] = $lcSepMail = '';
			foreach ($laTabla as $laFila) {
				if (in_array($laFila['CAMPO'], $laClaves)) {
					if ($laFila['CAMPO']=='TO') {
						$laConfig['tcTO'] .= $lcSepMail . $laFila['DESCRIP'];
						$lcSepMail = ';';
					} else {
						foreach($this->aClaves[$laFila['CAMPO']] as $lcClave) {
							$laConfig[$lcClave] = $laFila['DESCRIP'];
						}
					}
				} else {
					$laOtras[$laFila['CAMPO']] = $laFila['DESCRIP'];
				}
			}
			if (isset($laOtras['SMTPTEST'])) {
				// si está activo el test, adiciona tcBCC
				if ($laOtras['SMTPTEST']=='SI' && isset($laOtras['TESTMAIL'])) {
					$laConfig['tcBCC'] = (isset($laConfig['tcBCC']) ? ',' : '') . $laOtras['TESTMAIL'];
				}
			}
			unset($laOtras['SMTPTEST'], $laOtras['TESTMAIL']);
		}

		return [
			'config'=>$laConfig,
			'otros'=>$laOtras
		];
	}


	/*
	 *	Recupera plantilla para envío de correo electrónico y la coloca en $this->cPlantilla
	 *	@param string $tcIndice: clave en cl2tma
	 *	@param string $tcIndice: clave en cl3tma
	 */
	public function obtenerPlantilla($tcIndice, $tcSubIndice)
	{
		$this->cPlantilla = '';
		$lcRuta = $this->oDb->obtenerTabmae1('DE2TMA', 'MAILSETT', "CL1TMA='PLANTILL' AND CL2TMA='$tcIndice' AND CL3TMA='$tcSubIndice' AND ESTTMA=''", null, '');
		if (!empty($lcRuta)) {
			$lcRuta = str_replace('\\', '/', $lcRuta);
			$lcArchivo = pathinfo($lcRuta, PATHINFO_BASENAME);
			$this->cPlantilla = file_get_contents($this->cRutaPlantillas . $lcArchivo);
		}
	}


	/*
	 *	Crea carpeta para datos adjuntos
	 *	@return boolean true si se creó la carpeta
	 */
	public function crearCarpetaAdjuntos()
	{
		$lcRuta = $this->cRutaSrv.$this->cDirAdjutos.$this->lcBar;
		if (file_exists($lcRuta)) {
			return true;
		} else {
			$lbCreaDir = mkdir($lcRuta, 0777, true);
			return file_exists($lcRuta);
		}
	}


	/*
	 *	Adjuntar documentos del libro
	 *	@param array $taDatos: array con los datos del documento o documentos a adjuntar
	 *	@param string $tcNombreArchivo: nombre que debe tener el archivo
	 *	@return boolean true si se creó el archivo y se copió a la ruta de adjuntos
	 */
	public function adicionarAdjuntoLibro($taDatos, $tcNombreArchivo)
	{
		$lcReturn = false;
		if ($this->crearCarpetaAdjuntos()) {

			// genera archivo
			$lcRutaOrigen = sys_get_temp_dir() . $this->lcBar . $tcNombreArchivo;
			$lcRutaDestino = $this->cRutaSrv.$this->cDirAdjutos.$this->lcBar.$tcNombreArchivo;

			require_once __DIR__ .'/class.Documento.php';
			$loDocLibro = new Documento();
			$lConsultarIngreso = true;
			$lbDocumentoSolo = false;
			foreach ($taDatos as $taDato) {
				$loDocLibro->obtenerDocumento($taDato, $lConsultarIngreso, $lbDocumentoSolo);
				$lConsultarIngreso = false;
			}
			$lcPassword = $taDatos[0]['nNumDocPac'];
			$loDocLibro->generarPDF($lcRutaOrigen, 'F', $lcPassword);

			// Copiar el archivo
			if (file_exists($lcRutaOrigen)) {
				if (copy($lcRutaOrigen, $lcRutaDestino)) {
					$lcReturn = true;
				}
				unlink($lcRutaOrigen);
			}
		}
		return $lcReturn;
	}


	/*
	 *	Obtiene los datos para generar la epicrisis de un paciente por número de ingreso
	 *	@param integer $tnIngreso: número de ingreso
	 *	@return array datos para obtener la epicrisis
	 */
	public function obtenerDatosEpicrisis($tnIngreso)
	{
		require_once __DIR__ . '/class.ListaDocumentos.php';
		$loLista = new ListaDocumentos();
		$loLista->cargarDatos($tnIngreso, '', 0, '', '', false);
		$loLista->obtenerVia($tnIngreso);
		$loLista->obtenerHabitaciones($tnIngreso);
		$loLista->consultarEpicrisis($tnIngreso);
		$laDocs = $loLista->obtenerDocumentos();
		$laDoc = $laDocs[$tnIngreso][0];
		return [[
			'nIngreso'		=> $tnIngreso,
			'cTipDocPac'	=> $loLista->cTipoId(),
			'nNumDocPac'	=> $loLista->nNumeroId(),
			'cRegMedico'	=> $laDoc['medRegMd'],
			'cTipoDocum'	=> $laDoc['tipoDoc'],
			'cTipoProgr'	=> $laDoc['tipoPrg'],
			'tFechaHora'	=> $laDoc['fecha'],
			'nConsecCita'	=> $laDoc['cnsCita'],
			'nConsecCons'	=> $laDoc['cnsCons'],
			'nConsecEvol'	=> $laDoc['cnsEvo'],
			'nConsecDoc'	=> $laDoc['cnsDoc'],
			'cCUP'			=> $laDoc['codCup'],
			'cCodVia'		=> $laDoc['codvia'],
			'cSecHab'		=> $laDoc['sechab'],
		]];
	}


	/*
	 *	Organiza los datos para enviar al webservice en $this->aConfiguracion
	 *	@param array $taConfiguracion: configuraciones modificadas
	 */
	public function iniciarConfiguracion($taConfiguracion)
	{
		$this->aConfiguracion = $this->configVacia();
		// no se hace array_merge para evitar claves que no se deben enviar al webservice
		foreach ($taConfiguracion as $laClave=>$lcValor) {
			if (isset($this->aConfiguracion[$laClave])) {
				$this->aConfiguracion[$laClave] = $lcValor;
			}
		}
	}


	/*
	 *	Configuración para enviar al webservice
	 */
	public function configVacia()
	{
		return [
			'tcServer' => 'mail.shaio.org',
			'tnPort' => 25,
			'tcUser' => '',
			'tcPass' => '',
			'tcFrom' => '',
			'tcTO' => '',
			'tcCC' => '',
			'tcBCC' => '',
			'tcSubject' => '',
			'tcBody' => '',
			'tnAuthMode' => 0,
			'tnPriority' => 0,
			'tnImportance' => 0,
			'tnDisposition' => 0,
			'tcOrganization' => 'Fundación Clínica Shaio',
			'tcKeywords' => '',
			'tcDescription' => '',
			'tcAttachServerFilesID' => '',
			//'tcCharset' => 'utf-8',
		];
	}


	/*
	 *	Retorna nombre de carpeta para adjuntos
	 */
	public function cDirAdjutos()
	{
		return $this->cDirAdjutos;
	}


	/*
	 *	Retorna true si es valida la dirección de correo electrónico recibida en el parámetro
	 */
	public function validarEmail($tcEmail)
	{
		return (boolean) filter_var($tcEmail, FILTER_VALIDATE_EMAIL);
	}

}