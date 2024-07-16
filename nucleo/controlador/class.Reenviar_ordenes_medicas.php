<?php

namespace NUCLEO;

require_once('class.Db.php');
require_once __DIR__ . '/../../webservice/complementos/nusoap-php8/1.124/nusoap.php';

use NUCLEO\Db;

class Reenviar_ordenes_medicas
{

	private $oDb;
	private $aConsultaOrdenes;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function consultaReenviarOrdenes()
	{
		$lcEnviarDatos = '';
		$lcUser = 'SRVRE_WEB';
		$lcIniBloque = $cFinBloque = '';

		$laCampos = ["TRIM(PRVLGC) PROVEEDOR", "INGLGC INGRESO", "TRIM(TIPLGC) TIPO", "TRIM(CODLGC) CODLGC", "TRIM(ESTLGC) ESTADO", "TRIM(MSGLGC) MENSAJE"];
		$laMensajesReenviar = $this->oDb
			->select($laCampos)
			->from('LGSPRV')
			->where('ESTLGC', '=', 'CREADO')
			->in('PRVLGC', ['AGFA', 'HEXALIS'])
			->orderBy('INGLGC, FECLGC, HORLGC')
			->getAll('array');
		$this->aConsultaOrdenes = $laMensajesReenviar;

		foreach ($laMensajesReenviar as $laDatosEnviar) {
			$lcTipoError = $lcRespuestaError = '';
			$laResultado = ['error' => '', 'resultado' => ''];
			$lcProveedor = $laDatosEnviar['PROVEEDOR'];
			$lnIngreso = intval($laDatosEnviar['INGRESO']);
			$lcMensaje = $laDatosEnviar['MENSAJE'];
			$lcTipoMensaje = $laDatosEnviar['TIPO'];
			$lcConsecutivo = $laDatosEnviar['CODLGC'];
			$lcMensaje = $lcIniBloque . $lcMensaje . $cFinBloque;
			$lcProg = 'ENVR_' . $lcTipoMensaje;
			$lcRespuestaProveedor = '';
			$lcWebServiceUrlWsdl = $this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='$lcProveedor' AND CL3TMA='WSDL' AND CL4TMA='$lcTipoMensaje' AND ESTTMA='' ", null, '');
			$lcFnLlamado = $this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='$lcProveedor' AND CL3TMA='CALL' AND CL4TMA='$lcTipoMensaje' AND ESTTMA='' ", null, '');
			$lcTipoIso = $this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='$lcProveedor' AND CL3TMA='ISO' AND CL4TMA='ISO' AND ESTTMA='' ", null, '');
			$lcEstadoproveedor = $this->oDb->obtenerTabmae1('DE2TMA', 'HL7_PRM', "CL1TMA='MODELO' AND CL2TMA='$lcProveedor' AND CL3TMA='ESTRESP' AND CL4TMA='ESTADO' AND ESTTMA='' ", null, '');

			if (!empty($lcWebServiceUrlWsdl)) {
				$options['http'] = array(
					'method' => "HEAD",
					'ignore_errors' => 1,
					'max_redirects' => 0
				);
				$body = @file_get_contents($lcWebServiceUrlWsdl, NULL, stream_context_create($options));


				if (isset($http_response_header)) {

					$laDatos = [
						'PRVLGC' => $lcProveedor,
						'TIPLGC' => $lcTipoMensaje,
						'CODLGC' => $lcConsecutivo,
						'INGLGC' => $lnIngreso ?? 0,
					];

					if ($lcProveedor == 'HEXALIS') {
						$laDatosMensaje = ['HL7Doc' => $lcMensaje];
						$this->fnLogActualizarMensaje('ENPROCH', '', $lcUser, $lcProg, $laDatos);
						$loClient = new \SoapClient($lcWebServiceUrlWsdl);
						$loResponse = $loClient->__soapCall($lcFnLlamado, $laDatosMensaje);
						$lcRespuestaProveedor = !empty($loResponse) ? $loResponse : 'SIN RESPUESTA HEXALIS';
						$lcEstadoproveedor = !empty($loResponse) ? $lcEstadoproveedor : 'SINRESP';
						$laResultado = explode('|', $lcRespuestaProveedor);
						$lcTipoError = $laResultado[12];
						$lcRespuestaError = $laResultado[14];
					}

					if ($lcProveedor == 'AGFA') {
						$laDatosMensaje = ['payload' => $lcMensaje];
						$laDatosMensaje['payload'] = mb_convert_encoding($laDatosMensaje['payload'], $lcTipoIso);
						$this->fnLogActualizarMensaje('ENPROCA', '', $lcUser, $lcProg, $laDatos);
						$loSoapClient = new \nusoap_client($lcWebServiceUrlWsdl, 'wsdl');
						$loSoapClient->setEndpoint($lcWebServiceUrlWsdl);
						$loResponse = $loSoapClient->call($lcFnLlamado, $laDatosMensaje);
						$lcRespuestaProveedor = !empty($loResponse['payload']) ? $loResponse['payload'] : 'SIN RESPUESTA AGFA';
						$lcEstadoproveedor = !empty($loResponse['payload']) ? $lcEstadoproveedor : 'SINRESP';
					}

				} else {
					$lcEstadoproveedor = 'CREADO';
					$lcRespuestaProveedor = 'URL ' . $lcProveedor . ' Inactiva';
				}
			} else {
				$lcEstadoproveedor = 'CREADO';
				$lcRespuestaProveedor = 'No existe URL ' . $lcProveedor;
			}

			if (!empty($lcRespuestaProveedor)) {
				if ($lcProveedor == 'HEXALIS' && $lcTipoError == 'AE') {
					$lcEstadoproveedor = 'CREADO';
					$lcRespuestaProveedor = $lcRespuestaError;
				}
				$this->fnLogActualizarMensaje($lcEstadoproveedor, $lcRespuestaProveedor, $lcUser, $lcProg, $laDatos);
			}
		}
		unset($laMensajesReenviar);
		return $this->aConsultaOrdenes;
	}

	public function fnLogActualizarMensaje($tcEstado, $tcRespuesta = '', $tcUser = 'SRV_WEB', $tcPrograma = '', $taDatos = [])
	{
		$ldFechaHoraLog = new \DateTime($this->oDb->fechaHoraSistema());
		$lcFecha = $ldFechaHoraLog->format('Ymd');
		$lcHora = $ldFechaHoraLog->format('His');

		$lcTablaLog = 'LGSPRV';
		$laDatosLog = [
			'UMOLGC' => $tcUser,
			'PMOLGC' => $tcPrograma,
			'FMOLGC' => $lcFecha,
			'HMOLGC' => $lcHora,
		];
		if (!empty($tcRespuesta)) {
			$laDatosLog['RTALGC'] = $tcRespuesta;
		}

		if (!empty($tcEstado)) {
			$laDatosLog['ESTLGC'] = $tcEstado;
		}

		$laWhere = [
			'PRVLGC' => ($taDatos['PRVLGC'] ?? ''),
			'TIPLGC' => ($taDatos['TIPLGC'] ?? ''),
			'CODLGC' => ($taDatos['CODLGC'] ?? ''),
			'INGLGC' => ($taDatos['INGLGC'] ?? '0'),
		];
		$this->oDb->from($lcTablaLog)->where($laWhere)->actualizar($laDatosLog);
	}

}