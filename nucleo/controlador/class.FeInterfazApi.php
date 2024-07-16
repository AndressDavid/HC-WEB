<?php

/***********************************************************************************/
/**********  INTERFAZ PARA CONSUMO DE LAS API DE FACTURACIÓN ELECTRÓNICA  **********/
/***********************************************************************************/

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/../publico/complementos/pear/HTTP_Request2/2.5.1/HTTP/Request2.php';


class FeInterfazApi
{
	protected $cAmbiente;
	protected $aConfig = [];
	protected $aConfigReq = [
		'ssl_verify_peer' => false,
		'ssl_verify_host' => true,
	];
	protected $cError = '';
	protected $cTokenFacture = '';
	protected $cProveedor = '';
	protected $cResponseBody = '';
	protected $oRetornar;
	protected $oLastEvent;
	protected $nTimeOut=20;
	protected $nConnectTimeOut=15;


	public function __construct($tcAmbiente, $tnConnectTimeOut, $tnTimeOut, $taConfig, $tcProveedor)
	{
		global $goDb;
		$this->oDB = $goDb;
		$this->cAmbiente = $tcAmbiente;
		$this->nConnectTimeOut = $tnConnectTimeOut;
		$this->nTimeOut = $tnTimeOut;
		$this->aConfig = $taConfig;
		$this->cProveedor = $tcProveedor;
		if ($this->cProveedor=='facture') {
			$this->consultarTokenFacture();
		}
	}


	/*
	 *	Consume las API de Facturación Electrónica
	 *	@param string $tcAPI: ruta y nombre de la API a consumir
	 *	@param array  $taHeaders: datos a enviar en el encabezado
	 *	@param array  $taQuery_var: variables adicionales a enviar
	 *	@param string $tcParamsBody: cuerpo del envío
	 */
	public function consumirAPI($tcAPI, $taHeaders=[], $taQuery_var=[], $tcParamsBody='')
	{
		$this->cError = '';
		$this->cResponseBody = '';
		$this->oRetornar = false;

		try {
			// Crear objeto
			$loRequest = new \Http_Request2($tcAPI);
			$loRequest->setConfig([
				'connect_timeout' => $this->nConnectTimeOut,
				'timeout' => $this->nTimeOut,
			]);
			$loUrl = $loRequest->getUrl();

			$loRequest->setConfig($this->aConfigReq);

			if (count($taHeaders)>0) {
				$loRequest->setHeader($taHeaders);
			}

			if (count($taQuery_var)>0) {
				$loUrl->setQueryVariables($taQuery_var);
			}

			$laMethod = \HTTP_Request2::METHOD_POST;
			$loRequest->setMethod($laMethod);

			if (!empty($tcParamsBody)) {
				$loRequest->setBody($tcParamsBody);
			}

			$loResponse = $loRequest->send();
			$lnEstado = $loResponse->getStatus();

			if ($lnEstado == 200) {
				$this->cResponseBody = $loResponse->getBody();
				//$this->oRetornar = json_decode($this->cResponseBody);
				//$this->oLastEvent = $loRequest->getLastEvent();
			} else {
				$this->cResponseBody = '{"error":"Estado '.$lnEstado.'"}';

			}

		} catch (HTTP_Request2_Exception $ex) {
			$this->cError = $ex->getMessage();
			$this->cResponseBody = '{"error":"'.$this->cError.'"}';
			//$this->oRetornar = json_decode('{"error":"'.$this->cError.'"}');

		} catch (HttpException $ex) {
			$this->cError = $ex->getMessage();
			$this->cResponseBody = '{"error":"'.$this->cError.'"}';
			//$this->oRetornar = json_decode('{"error":"'.$this->cError.'"}');

		} finally {
			return $this->cResponseBody;
		}
	}


	/*
	 *	Consulta el token almacenado en AS400
	 */
	public function consultarTokenFacture()
	{
		$lcAmbiente = strtoupper(substr($this->cAmbiente,0,8));
		$this->cTokenFacture = '';
		$laToken = $this->oDB
			->select('DE2TMA || OP5TMA AS TOKEN')
			->from('TABMAE')
			->where("TIPTMA='FACTELE' AND CL1TMA='TOKENF' AND CL2TMA='{$lcAmbiente}'")
			->orderBy('CL3TMA')
			->getAll('array');
		if (is_array($laToken)) {
			if (count($laToken)>0) {
				foreach ($laToken as $laLinea) {
					$this->cTokenFacture .= trim($laLinea['TOKEN']);
				}
			} else {
				$this->cError = 'No se pudo recuperar el token';
			}
		} else {
			$this->cError = 'No se puede consultar el token';
		}
	}


	/*
	 *	Obtiene el token desde la interfaz de Facture
	 */
	public function obtenerToken()
	{
		$this->cTokenFacture = '';
		$lcAmbiente = strtoupper(substr($this->cAmbiente,0,8));
		$lcAPI = $this->aConfig['urlBase'].$this->aConfig['urlToken'];
		$laHeaders = [
			'Content-Type: application/json',
			'X-Who: '.$this->aConfig['pkEmision'],
		];
		$laQuery_var = [];
		$laParamsBody = [
			'u' => $this->aConfig['Usuario'],
			'p' => $this->aConfig['Clave'],
			't' => $this->aConfig['TenantId'],
		];
		$laRta = json_decode($this->consumirAPI($lcAPI, $laHeaders, $laQuery_var, json_encode($laParamsBody)));

		if (isset($laRta->accessToken)) {

			$this->cTokenFacture = $laRta->accessToken;

			// Eliminar token
			$this->oDB
				->from('TABMAE')
				->where("TIPTMA='FACTELE' AND CL1TMA='TOKENF' AND CL2TMA='{$lcAmbiente}'")
				->eliminar();
			$lcUsuario = 'FEWEB';
			$lcProgram = 'FEGETTOKEN';
			$ltAhora = new \DateTime( $this->oDB->fechaHoraSistema() );
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');

			// Guardar token
			$lnLineas = ceil(strlen($this->cTokenFacture)/440);
			for ($lnLinea=0; $lnLinea < $lnLineas; $lnLinea++) {
				$lcTexto = substr($this->cTokenFacture, ($lnLinea) * 440, 440);
				$laDatos = [
					'TIPTMA' => 'FACTELE',
					'CL1TMA' => 'TOKENF',
					'CL2TMA' => $lcAmbiente,
					'CL3TMA' => $lnLinea,
					'DE1TMA' => 'Token Ambiente '.$this->cAmbiente,
					'DE2TMA' => substr($lcTexto, 0, 220),
					'OP5TMA' => substr($lcTexto, 220, 220),
					'USRTMA' => $lcUsuario,
					'PGMTMA' => $lcProgram,
					'FECTMA' => $lcFecha,
					'HORTMA' => $lcHora,
				];
				$this->oDB->from('TABMAE')->insertar($laDatos);
			}
		}
	}


	/*
	 *	Retorna token actual
	 */
	public function cTokenFacture()
	{
		return $this->cTokenFacture;
	}


	/*
	 *	Retorna objeto de retorno recibido por la plataforma
	 */
	public function oRetornar()
	{
		return $this->oRetornar;
	}


	/*
	 *	Retorna objeto de retorno recibido por la plataforma
	 */
	public function responseBody()
	{
		return $this->cResponseBody;
	}


	/*
	 *	Retorna información del último evento
	 */
	public function oLastEvent()
	{
		return $this->oLastEvent;
	}

}
