<?php

/***********************************************************************************/
/**********  GENERACIÓN Y ENVÍO DE DOCUMENTOS DE FACTURACIÓN ELECTRÓNICA  **********/
/***********************************************************************************/

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';
require_once __DIR__ .'/class.FeInterfazApi.php';
require_once __DIR__ .'/class.DocumentosCM.php';


class FacturaElectronica
{
	public $aConfig;
	public $aDocFE = [];
	public $aDocAdd = [];
	public $aError = [];
	public $aIPsValidas = [];
	public $oApiClient = null;
	public $cTipFacNot = '';
	public $cKeyControl = '';
	public $cEstado = '';
	public $cDescrp = '';
	public $cError = '';
	public $cResponse = '';
	public $oResponse = null;
	public $oLastEvent = null;
	protected $nLargoError = 1500;

	// Propiedades Facture
	public $oXml = null;
	public $cXml = '';
	public $aCharEliminar = [ 2, 26, ];
	public $cSLFacture = '';

	// Propiedades Transfiriendo
	public $cJson;
	public $cSLTransf = '';


	function __construct()
	{
		date_default_timezone_set('America/Bogota');

		// $this->aConfig = $this->obtenerConfig();
		$this->aConfig = require __DIR__ . '/../privada/fe_config.php';
		$this->obtenerIPsValidas();
	}


	/*
	 *	Consulta, organiza y envía los documentos de FE
	 */
	public function ejecutarDocumentosFE($taWhere=[], $tbEnviar=true, $tbObligarEnviar=false, $tbObligarCM=false, $tcOrdenAutorizaciones='ASC')
	{
		// Consulta los documentos a procesar
		$laListaDoc = $this->consultarFemov($taWhere, $tbEnviar, $tbObligarEnviar);
		if (count($laListaDoc)) {

			$lcAmbiente = $this->aConfig['ambiente'];
			$this->oApiClient = new FeInterfazApi(
				$lcAmbiente,
				$this->aConfig['parFac']['connect_timeout'],
				$this->aConfig['parFac']['timeout'],
				$this->aConfig[$this->aConfig['proveedor']][$lcAmbiente],
				$this->aConfig['proveedor']
			);

			// Recorrido para cada documento
			foreach ($laListaDoc as $laDoc) {
				$laDoc = array_map('trim', $laDoc);
				$this->cXml = $this->cJson = $this->cEstado = $this->cTipFacNot = $this->cDescrp = $this->cError = '';
				$this->aDocFE = [];
				$this->oXml = null;
				$this->aDocAdd = [
					'CUFE' => '',
					'REQUESTID' => '',
					'LDF' => '',
					'URLREQ' => '',
					'DOCNUMBER' => '',
					'IDDOC' => '',
				];

				if (in_array($laDoc['TIPR'], $this->aConfig['documentos'])) {

					$this->cTipFacNot = "{$laDoc['TIPR']}-{$laDoc['FACT']}-{$laDoc['NOTA']}";


					// ********************* Consulta de datos *********************
					$lcDocContable = substr($laDoc['TIPR'], 0, 2);
					if ($lcDocContable=='DS') {
						$laDoc['ES_DS'] = true;
						$laDoc['TIPDOCXML'] = $laDoc['TIPR']=='DSNC' ? 'NS' : $lcDocContable;
					} else {
						$laDoc['ES_DS'] = false;
						$laDoc['TIPDOCXML'] = substr($laDoc['TIPR'], 2, 2);
					}
					$laResult = $this->consultaDocumento($laDoc, $tbObligarCM, $tcOrdenAutorizaciones);
					if (count($laResult)==0 || (is_array($laResult['error']) && count($laResult['error'])>0)) {
						$this->cEstado = '05';
						$this->cDescrp = 'Error al consultar el documento';
						$this->cError = 'Error al consultar el documento - '.($laResult['error'][0]['Dsc']??'');
						$this->guardarEstado($laDoc);
						$this->cError = implode('|', $laResult['error']);
						continue;
					}
					$this->aDocFE = $laResult['datos'];

					switch ($this->aConfig['proveedor']) {

						case 'facture':
							// ********************* Organizar la información *********************
							$this->cXml = $this->generarFactureXML($laDoc);

							if (strlen($this->cXml)==0) {
								$this->cEstado = '01'; // Error Shaio
								$this->cDescrp = 'Error al generar XML';
								$this->cError = 'Error al generar XML' . (count($this->aError)==0 ? '' : ' - ' . $this->aError['Num'] . '-' . $this->aError['Dsc']);
								$this->guardarEstado($laDoc);

							} else {
								if ($this->aConfig['parFac']['guardarXml']) {
									$this->guardarXml();
								}

								// ********************* Envía el documento *********************
								if ($tbEnviar) {
									$this->obtenerKeyControl($laDoc['ES_DS'] ? 'RESOLDSP' : 'RESOLFAC');
									$lbEnviada = $this->enviarFactureXML($laDoc);
									$this->guardarEstado($laDoc);
								}
							}
							break;

						case 'transfiriendo':
							// ********************* Organizar la información *********************
							$this->cJson = $this->generarTransfiriendoJSON($laDoc);

							if (strlen($this->cJson)==0) {
								$this->cEstado = '01'; // Error Shaio
								$this->cDescrp = 'Error al generar JSON';
								$this->cError = 'Error al generar JSON' . (count($this->aError)==0 ? '' : ' - ' . $this->aError['Num'] . '-' . $this->aError['Dsc']);
								$this->guardarEstado($laDoc);

							} else {
								if ($this->aConfig['parFac']['guardarXml']) {
									$this->guardarJSON();
								}

								// ********************* Envía el documento *********************
								if ($tbEnviar) {
									$lbEnviada = $this->enviarTransfiriendoJSON($laDoc);
									$this->guardarEstado($laDoc);
								}
							}
							break;

						default:
							$this->cEstado = '98';
							$this->cDescrp = 'Proveedor no configurado';
							$this->cError = 'Proveedor no configurado - VALIDAR CON EL DEPARTAMENTO DE TI';
							$this->guardarEstado($laDoc);
							return ['success'=>false, 'mensaje'=>'Proveedor no configurado'];
							break;
					}
					if (!empty($this->cError)) {
						$this->guardarErr();
					}

				} else {
					$this->cEstado = '01';
					$this->cDescrp = 'Documento NO activo para enviar';
					$this->cError = 'Documento NO activo para enviar - No se encuentra activo el envío de este tipo de documentos';
					$this->guardarEstado($laDoc);
				}
			}

			//	header('Content-Type: text/xml; charset=UTF-8');
			//	echo $this->cXml;
			//	exit();

		} else {
			return ['success'=>false, 'mensaje'=>'No existen documentos para procesar'];
		}
	}


	/*
	 *	Consulta los documentos en la tabla FEMOV
	 *	@param array/string $taWhere: condiciones a tener en cuenta, si llega vacío se establece en ESTA='00'
	 */
	public function consultarFemov($taWhere=[], $tbEnviar=true, $tbObligarEnviar=false)
	{
		global $goDb;
		$lcWhereDefault = "ESTA='00' AND ((SUBSTR(TIPR,1,2)='DS') OR (SUBSTR(TIPR,1,2)<>'DS' AND FECC = REPLACE(SUBSTR(CHAR(NOW()), 1, 10), '-', '')))";
		if (is_array($taWhere)) {
			if (count($taWhere)==0) {
				$taWhere = $lcWhereDefault;
			}
		} else {
			if (is_string($taWhere)) {
				if (empty($taWhere)) {
					$taWhere = $lcWhereDefault;
				}
			} else {
				$taWhere = $lcWhereDefault;
			}
		}

		try {
			if ($this->aConfig['ambiente']=='Producción' && $tbEnviar===true && !($tbObligarEnviar==true)) {
				$goDb->where("ESTA<>'02'");	// Estado diferente a Enviadas con Éxito
			}
			$laListDoc = $goDb
				->select('TIPR, FACT, NOTA, DOCA')
				->tabla('FEMOV')
				->where($taWhere)
				->getAll('array');

		} catch(\PDOException $loError){
			// No se actualizó la información en la base de datos
			$this->cError = 'No se pudo consultar documentos - '.$loError->getMessage();
		} catch(\Exception $loError){
			// No se actualizó la información en la base de datos
			$this->cError = 'No se pudo consultar documentos - '.$loError->getMessage();
		} finally {
			return isset($laListDoc) && is_array($laListDoc) ? $laListDoc : [];
		}
	}


	/*
	 *	Obtener configuración de la facturación electrónica
	 */
	private function obtenerConfig()
	{
		global $goDb;
		$laReturn = $laTemp = [];
		$laConfig = $goDb
			->select('CL3TMA LINEA, TRIM(DE1TMA) VAR, DE2TMA||OP5TMA CONTENT, OP1TMA TIPO')
			->from('TABMAE')
			->where("TIPTMA='FACTELE' AND CL1TMA='CONFIG' AND ESTTMA=''")
			->orderBy('CL2TMA, CL3TMA')
			->getAll('array');
		if ($goDb->numRows()>0) {
			foreach ($laConfig as $laCnf) {
				if (isset($laTemp[$laCnf['VAR']])) {
					$laTemp[$laCnf['VAR']]['cont'] .= $laCnf['CONTENT'];
				} else {
					$laTemp[$laCnf['VAR']] = [
						'tipo' => $laCnf['TIPO'],
						'cont' => $laCnf['CONTENT'],
					];
				}
			}
			foreach ($laTemp as $lcVar => $laCnf) {
				$lcContent = trim($laCnf['cont']);
				switch ($laCnf['tipo']) {
					case 'J':
						$laReturn[$lcVar] = json_decode($lcContent, true);
						break;
					case 'R':
						$laReturn[$lcVar] = __DIR__ . $lcContent;
						break;
					case 'I':
						$laReturn[$lcVar] = intval($lcContent);
						break;
					//case 'T':
					default:
						$laReturn[$lcVar] = $lcContent;
						break;
				}
			}
		}

		return $laReturn;
	}


	/*
	 *	Guarda estado del documento
	 *	@param array $taDatos: datos del documento
	 */
	private function guardarEstado($taDatos)
	{
		global $goDb;

		if ($this->aConfig['ambiente']=='Pruebas' && $goDb->obtenerEntorno()=='produccion') {
			// Ambiente pruebas en BD producción no guarda estados
			return false;
		}

		if(empty($this->cEstado)){
			$this->cEstado = '05';
		}
		$lbEsError = !in_array($this->cEstado, ['02','03']);
		$lcMensaje = $this->cDescrp . $this->cError;

		// Registro en la base de datos
		try {
			$ltAhora = new \DateTime($goDb->fechaHoraSistema());
			$lcFecha = $ltAhora->format('Ymd');
			$lcHora  = $ltAhora->format('His');
			$lcUser = 'FE_WEB';
			$lcProg = 'FAC_ELEC';
			$laUpdt  = [
				'ESTA' => $this->cEstado,
				'DOEID' => $this->aDocAdd['IDDOC'] ?? '', // id de documento de transfiriendo
				'USRM' => $lcUser,
				'PGMM' => $lcProg,
				'FECM' => $lcFecha,
				'HORM' => $lcHora,
			];
			if ($this->cEstado=='02'){
				$laUpdt += [
					'CUFE'  => $this->aDocAdd['CUFE'],
					'REQID' => $this->aDocAdd['REQUESTID'],
					'DOCNUM'=> $this->aDocAdd['DOCNUMBER'],
					'LDFID' => $this->aDocAdd['LDF'],
					'URLID' => $this->aDocAdd['URLREQ'],
				];
			}

			// Actualizar cabecera
			$lbRta = $goDb
				->tabla('FEMOV')
				->where([
					'FACT'=>$taDatos['FACT'],
					'NOTA'=>$taDatos['NOTA'],
					'TIPR'=>$taDatos['TIPR'],
				])
				->where('ESTA<>\'02\'')
				->actualizar($laUpdt);

			// Crear detalle
			$laDat = $goDb
				->max('SECUD','MAXSEC')
				->tabla('FEMOVD')
				->where([
					'FACTD'=>$taDatos['FACT'],
					'NOTAD'=>$taDatos['NOTA'],
				])
				->get('array');
			$lnConsec = $goDb->numRows()==0 ? 1 : $laDat['MAXSEC']+1;
			$lbRta = $goDb
				->tabla('FEMOVD')
				->insertar([
					'FACTD' => $taDatos['FACT'],
					'NOTAD' => $taDatos['NOTA'],
					'SECUD' => $lnConsec,
					'ESTAD' => $this->cEstado,
					'DESCD' => $this->cDescrp, // $lcMensaje,
					'USRC'  => $lcUser,
					'PGMC'  => $lcProg,
					'FECC'  => $lcFecha,
					'HORC'  => $lcHora,
				]);

			// Guardar mensaje de error
			if (!empty($this->cError)) {
				$lbRta = $goDb
					->tabla('FEMOVE')
					->insertar([
						'FACTE' => $taDatos['FACT'],
						'NOTAE' => $taDatos['NOTA'],
						'SECUE' => $lnConsec,
						'ESTAE' => $this->cEstado,
						'DESCE' => $this->cError, // $lcMensaje,
						'USRC'  => $lcUser,
						'PGMC'  => $lcProg,
						'FECC'  => $lcFecha,
						'HORC'  => $lcHora,
					]);
			}
		} catch(\PDOException $loError){
			// No se actualizó la información en la base de datos
			$this->cError = 'No se actualizó la base de datos - '.$loError->getMessage();
		} catch(\Exception $loError){
			// No se actualizó la información en la base de datos
			$this->cError = 'No se actualizó la base de datos - '.$loError->getMessage();
		}
	}


	/*
	 *	Consulta de datos del documento
	 *	@param array $taDatos: datos del documento
	 */
	private function consultaDocumento($taDatos, $tbObligarCM=false, $tcOrdenAutorizaciones='ASC')
	{
		$lcClass = "NUCLEO\\FeConsultar{$taDatos['TIPR']}";
		require_once __DIR__ . "/class.FeConsultar{$taDatos['TIPR']}.php";
		$loConsFE = new $lcClass(array_merge($this->aConfig, ['obligar_CM'=>$tbObligarCM,'orden_autoriza'=>$tcOrdenAutorizaciones]));
		$laDocFE = $loConsFE->crearArrayDatos($taDatos['FACT'], $taDatos['NOTA'], $taDatos['DOCA']);

		return [
			'datos' => $laDocFE,
			'error' => $loConsFE->aError(),
		];
	}


	/*
	 *	Obtiene el primer key control activo
	 *	@param string $tcTipo: tipo de resolucion
	 */
	private function obtenerKeyControl($tcTipo='RESOLFAC')
	{
		global $goDb;
		$lcAmbiente = strtoupper(substr($this->aConfig['ambiente'],0,8));
		$lcKeyCtrl = $goDb
			->select('TRIM(OP5TMA) KEYCTRL')
			->from('TABMAE')
			->where("TIPTMA='FACTELE' AND CL1TMA='$tcTipo' AND CL2TMA='$lcAmbiente' AND OP1TMA='1' AND ESTTMA<>'0'")
			->orderBy('CL3TMA','ASC')
			->get('array');
		$this->cKeyControl = $lcKeyCtrl['KEYCTRL'] ?? '';
	}


	/*
	 *	Genera XML para enviar a Facture
	 */
	private function generarFactureXML($taDatos)
	{
		require_once __DIR__ .'/class.FeGenerarXMLFacture.php';
		$loXml = new FeGenerarXMLFacture($this->aConfig);
		$loXml->cTipDocXml = $taDatos['TIPDOCXML'];
		$lcTipDocXml = $this->aConfig['tipoDoc'][$taDatos['TIPDOCXML']]['DocXML'];

		try {
			$this->oXml = new \DOMDocument($this->aConfig['versionXml'], $this->aConfig['encodingXml']);
			$this->oXml->loadXML("<?xml version=\"{$this->aConfig['versionXml']}\" encoding=\"{$this->aConfig['encodingXml']}\" ?><{$lcTipDocXml} />");

			$this->importarNodo($loXml->nodoCabecera($this->aDocFE['Cabecera']));
			if (isset($this->aDocFE['NumeracionDIAN'])) {
				$this->importarNodo($loXml->nodoNumeracionDIAN($this->aDocFE['NumeracionDIAN'][0], $this->aDocFE['NumeracionDIAN'][1]));
			}
			if (isset($this->aDocFE['MotivosNota'])) {
				$this->importarNodo($loXml->nodoPadreGenerico('MotivosNota', 'MotivoNota', $this->aDocFE['MotivosNota']));
			}
			if (isset($this->aDocFE['SoporteAdquisicionesRelacionados'])) {
				$this->importarNodo($loXml->nodoPadreGenerico('SoporteAdquisicionesRelacionados', 'SoporteAdquisicionesRelacionado', $this->aDocFE['SoporteAdquisicionesRelacionados']));
			}
			if (isset($this->aDocFE['ReferenciasNotas'])) {
				$this->importarNodo($loXml->nodoReferenciasNotas($this->aDocFE['ReferenciasNotas']));
			}
			if (isset($this->aDocFE['DocumentosAdicionalesReferencia'])) {
				$this->importarNodo($loXml->nodoGenerico('DocumentosAdicionalesReferencia', $this->aDocFE['DocumentosAdicionalesReferencia']));
			}
			if (isset($this->aDocFE['ReferenciasTransacciones'])) {
				if (isset($this->aDocFE['FacturasRelacionadas'])) {
					$this->importarNodo($loXml->nodoPadreGenerico('FacturasRelacionadas', 'FacturaRelacionada', $this->aDocFE['FacturasRelacionadas']));
				}
				$this->importarNodo($loXml->nodoPadreGenerico('ReferenciasTransacciones', 'ReferenciaTransaccion', $this->aDocFE['ReferenciasTransacciones']));
			} else {
				if (isset($this->aDocFE['FacturasRelacionadas'])) {
					$this->importarNodo($loXml->nodoFacturasRelacionadas($this->aDocFE['FacturasRelacionadas']));
				}
			}
			if (isset($this->aDocFE['PeriodoFacturado'])) {
				$this->importarNodo($loXml->nodoGenerico('PeriodoFacturado', $this->aDocFE['PeriodoFacturado']));
			}
			if (isset($this->aDocFE['Notificacion'])) {
				$this->importarNodo($loXml->nodoNotificacion($this->aDocFE['Notificacion']));
			}
			if ($taDatos['ES_DS']) {
				$this->importarNodo($loXml->nodoCliente($this->aDocFE['Emisor'], false));
				$this->importarNodo($loXml->oEmisorDS);
			} else {
				$this->importarNodo($loXml->oEmisorFA);
				$this->importarNodo($loXml->nodoCliente($this->aDocFE['Cliente']));
			}
			$this->importarNodo($loXml->nodoMediosDePago($this->aDocFE['MediosDePago']));
			if (isset($this->aDocFE['Anticipos'])) {
				$this->importarNodo($loXml->nodoAnticipos($this->aDocFE['Anticipos']));
			}
			if (isset($this->aDocFE['DescuentosOCargos'])) {
				$this->importarNodo($loXml->nodoDescuentoOCargo($this->aDocFE['DescuentosOCargos']));
			}
			if (isset($this->aDocFE['Impuestos'])) {
				$this->importarNodo($loXml->nodoImpuestos($this->aDocFE['Impuestos']));
			}
			if (isset($this->aDocFE['Retenciones'])) {
				$this->importarNodo($loXml->nodoRetenciones($this->aDocFE['Retenciones']));
			}
			$this->importarNodo($loXml->nodoTotales($this->aDocFE['Totales']));
			foreach ($this->aDocFE['Lineas'] as $lnNumLinea => $laLinea) {
				$this->importarNodo($loXml->nodoLinea($laLinea));
			}
			$this->importarNodo($loXml->nodoExtensiones($this->aDocFE['Extensiones'], ($this->aDocFE['SectorSalud'] ?? false), 'extensiones'.($taDatos['ES_DS'] ? 'DS' : '')));

			$lcXml = $this->oXml->saveXML();


			foreach ($this->aCharEliminar as $luChar) {
				if (is_numeric($luChar)) {
					$lcXml = str_replace(chr($luChar), '', $lcXml);
				} else {
					$lcXml = str_replace($luChar, '', $lcXml);
				}
			}

		} catch (\Exception $e) {
			$this->cError = "Error 005 - Excepción capturada:  {$e->getMessage()}.";
		}

		return $lcXml;
	}

	/*
	 *	Envía el XML generado a Facture
	 */
	private function enviarFactureXML($taDatos)
	{
		$lcNIT = $this->aConfig['emisor']['NumeroIdentificacion'];
		$laPrmConex = $this->aConfig['facture'][$this->aConfig['ambiente']];
		$lcTipDocXmlRef = $this->aConfig['tipoDoc'][$taDatos['TIPDOCXML']]['DocRef'];
		$lcAPI = $laPrmConex['urlBase'].$laPrmConex['urlEmitir'];
		$laReturn = false;

		try {
			// Enviar documento
			$laHeaders = [
				'Content-Type: application/xml',
				'X-Who: '.$laPrmConex['pkEmision'],
				'Authorization: Bearer '.$this->oApiClient->cTokenFacture(),
				//'REQUEST-ID: ',
				//'X-REF-BRANCH: PRINCIPAL',
				//'X-REF-PROCESS: PRINCIPAL',
				'X-REF-DOCUMENTTYPE: '.$lcTipDocXmlRef,
				//'X-REF-THIRDPARTY: ',
				'X-KEYCONTROL: '.$this->cKeyControl,
			];
			$laQuery_var = [];
			$this->cResponse = $this->oApiClient->consumirAPI($lcAPI, $laHeaders, $laQuery_var, $this->cXml);


			// RETIRAR ESTO - Guardar en LOG
			$this->fnEscribirLog($this->cTipFacNot . ' | ' . $this->cResponse);


			$this->oResponse = json_decode($this->cResponse);
			$this->oLastEvent = $this->oApiClient->oLastEvent();

			// if (is_array($this->oResponse)) {
			if (is_object($this->oResponse)) {
				if (isset($this->oResponse->requestId) && isset($this->oResponse->UUID)) {
					$this->cEstado = '02'; // Enviada

					//	$lcUrlReq = $this->oResponse->UrlPdf;
					$lcUrlReq = str_replace($lcTipDocXmlRef, '[R]', str_replace($lcNIT, '[N]', str_replace($this->oResponse->LDF, '[L]', $this->oResponse->UrlPdf)));
					$this->aDocAdd = [
						'CUFE' => $this->oResponse->UUID,
						'REQUESTID' => $this->oResponse->requestId,
						'LDF' => $this->oResponse->LDF,
						'URLREQ' => $lcUrlReq,
						'DOCNUMBER' => $this->oResponse->documentNumber,
					];
					$this->cDescrp = "Enviado UUID: {$this->aDocAdd['CUFE']}";

				} else {

					if (isset($this->oResponse->requestId)){
						$this->cEstado = '05'; // Pendiente Respuesta DIAN
					} else {
						$this->cEstado = '04'; // Devuelta con error

						// Reportar el error
						$lcErrorCode = $this->oResponse->eventItems[0]->errorCode ?? '';
						$lcSeverityCode = $this->oResponse->eventItems[0]->severityCode ?? '';
						$lcShortDescription = $this->oResponse->eventItems[0]->shortDescription ?? '';
						$this->cDescrp = "severityCode: $lcSeverityCode, errorCode: $lcErrorCode";
						$this->cError = mb_substr($lcShortDescription, 0, $this->nLargoError);

						// Si no se están guardando los XML lo guarda para que se pueda analizar el error
						if (!$this->aConfig['parFac']['guardarXml']) {
							$this->guardarXml();
						}
					}
				}
				$laReturn = true;
			} else {
				$this->cEstado = '01';
				$this->cDescrp = 'Error en el envío';
				$this->cError = 'Error en el envío, respuesta inesperada';
				$laReturn = false;
			}
		} catch(\Exception $loError){
			$this->cEstado = '01';
			$this->cDescrp = 'Error en el envío';
			$this->cError = 'Err: ' . $loError->getMessage();
			$laReturn = false;
		} finally {
			return $laReturn;
		}
	}


	private function importarNodo($toNodo)
	{
		if (is_object($toNodo)) {
			if ($loImportedNode = $this->oXml->importNode($toNodo, true)) {
				if (is_object($loImportedNode)) {
					$this->oXml->documentElement->appendChild($loImportedNode);
				}
			}
		}
	}


	/*
	 *	Genera JSON para enviar a Transfiriendo
	 */
	private function generarTransfiriendoJSON($taDatos)
	{
		require_once __DIR__ .'/class.FeGenerarJSONTransfiriendo.php';
		$loJSON = new FeGenerarJSONTransfiriendo($this->aConfig);

		if (in_array($taDatos['TIPDOCXML'], ['FA','NC','ND'])) {
			//	FACTURACIÓN ELECTRÓNICA
			$laDoc = $loJSON->generarFE($taDatos, $this->aDocFE);

		} elseif (in_array($taDatos['TIPDOCXML'], ['DS','NS'])) {
			//	DOCUMENTO SOPORTE
			$laDoc = $loJSON->generarDS($taDatos, $this->aDocFE);
		}

		return $laDoc;
	}


	/*
	 *	Enviar datos del documento en JSON a Transfiriendo
	 */
	private function enviarTransfiriendoJSON($taDatos)
	{
		$lcNIT = $this->aConfig['emisor']['NumeroIdentificacion'];
		$lcAmbEnvio = $this->aConfig['ambiente'] . ($taDatos['ES_DS'] ? 'DS' : '');
		$laPrmConex = $this->aConfig['transfiriendo'][$lcAmbEnvio];
		$lcAPI = $laPrmConex['urlBase'].$laPrmConex['urlEmitir'];
		$lcTipDocXmlRef = $this->aConfig['tipoDoc'][$taDatos['TIPDOCXML']]['DocRef'];
		$lbReturn = false;

		try {
			// Enviar documento
			$laHeaders = !empty($laPrmConex['Usuario']??'') ? [
				'Content-Type: application/json',
				'Authorization: Basic '. base64_encode($laPrmConex['Usuario'].':'.$laPrmConex['Clave']),
			] : [];
			$laQuery_var = [];
			$this->cResponse = $this->oApiClient->consumirAPI($lcAPI, $laHeaders, $laQuery_var, $this->cJson);


			// RETIRAR ESTO - Guardar en LOG
			$this->fnEscribirLog($this->cTipFacNot . ' | ' . $this->cResponse);


			$this->oResponse = json_decode($this->cResponse);
			// $this->oLastEvent = $this->oApiClient->oLastEvent();


			if (is_object($this->oResponse)) {

				if (isset($this->oResponse->mensaje) && $this->oResponse->mensaje=='OK') {

					if (isset($this->oResponse->data) && isset($this->oResponse->data->idTransaccion)) {
						$this->cEstado = '03'; // Pendiente respuesta Proveedor
						$this->cDescrp = $this->oResponse->data->idTransaccion;
						$this->aDocAdd['IDDOC'] = $this->oResponse->data->idTransaccion;
						$lbReturn = true;
					} else {
						$this->cEstado = '04';
						$this->cDescrp = 'Error en el envío';
						$this->cError = 'Err: No se recibió identificador del documento. ' . ($this->oResponse->mensaje ?? '');
					}

				} elseif (isset($this->oResponse->esExitoso) && $this->oResponse->esExitoso) {

					// Es Documento Soporte
					if ($taDatos['ES_DS']) {
						$this->cEstado = '02'; // Proceso exitoso

						$this->aDocAdd = [
							'CUFE' => $this->oResponse->resultado->UUID,
							'REQUESTID' => $this->oResponse->resultado->identificadorTransaccion,
							'LDF' => $this->oResponse->resultado->fechaValidacionDian,
							'URLREQ' => $this->oResponse->resultado->URLPDF,
							'DOCNUMBER' => $this->oResponse->resultado->numeroDocumento,
							'IDDOC' => $this->oResponse->resultado->identificadorTransaccion,
						];
						$this->cDescrp = "Enviado UUID: {$this->aDocAdd['CUFE']}";

					// Es documento de FE
					} else {
						if (isset($this->oResponse->identificador)) {
							$this->cEstado = '03'; // Pendiente respuesta Proveedor
							$this->cDescrp = $this->oResponse->identificador;
							$this->aDocAdd['IDDOC'] = $this->oResponse->identificador;
							$lbReturn = true;
						} else {
							$this->cEstado = '04';
							$this->cDescrp = 'Error en el envío';
							$this->cError = 'Err: No se recibió identificador del documento. ' . ($this->oResponse->mensaje ?? '');
						}
					}
				} else {
					// Es Documento Soporte
					if (isset($this->oResponse->esExitoso) && $taDatos['ES_DS']) {
						$this->cEstado = '04'; // Devuelta con error
						$this->cDescrp = $this->oResponse->mensaje . '. ';
						if (isset($this->oResponse->errores)) {
							$this->cError = implode(' - ', array_column($this->oResponse->errores, 'mensaje'));
						} else {
							$this->cError = 'No hay mensajes de error a nivel de factura.';
						}

					// Es documento de FE o hubo error en el envío
					} else {
						$this->cEstado = '04';
						$this->cDescrp = 'Error en el envío';
						$this->cError = 'Err: Envío de documento no exitoso.' . ($this->oResponse->mensaje ?? '');
					}
				}
			} else {
				$this->cEstado = '04';
				$this->cDescrp = 'Error en el envío';
				$this->cError = 'Err: Respuesta del envío incorrecta.';
			}

		} catch(\Exception $loError){
			$this->cEstado = '01';
			$this->cDescrp = 'Error en el envío';
			$this->cError = 'Err: ' . $loError->getMessage();

		} finally {
			return $lbReturn;
		}
	}


	/*
	 *	Guarda error del documento
	 */
	private function guardarErr()
	{
	}


	/*
	 *	Guarda XML en un archivo
	 */
	public function guardarXml($tbSubDir=true)
	{
		try {
			$lcNombreArchivo = $this->cTipFacNot . '_' . date('Ymd\THis') . '.xml';
			if ($tbSubDir) {
				//	$lcRuta = $this->aConfig['rutaGuardarXml'] . date('Ym');
				//	if (!is_dir($lcRuta)) { mkdir($lcRuta, 0777, true); }
				$lcRuta = $this->aConfig['rutaGuardarXml'];
				$lcFile = $lcRuta . '/' . $lcNombreArchivo;
			} else {
				$lcFile = $this->aConfig['rutaGuardarXml'] . $lcNombreArchivo;
			}

			$lnFile = fopen($lcFile, 'a');
			fputs($lnFile, $this->cXml);
			fclose($lnFile);
		} catch (\Exception $e) {
			$this->cError = "Err 007 - Excepción capturada:  {$e->getMessage()}.";
		}
	}


	/*
	 *	Guarda JSON en un archivo
	 */
	public function guardarJSON($tbSubDir=true)
	{
		try {
			$lcNombreArchivo = $this->cTipFacNot . '_' . date('Ymd\THis') . '.json';
			if ($tbSubDir) {
				//	$lcRuta = $this->aConfig['rutaGuardarXml'] . date('Ym');
				//	if (!is_dir($lcRuta)) { mkdir($lcRuta, 0777, true); }
				$lcRuta = $this->aConfig['rutaGuardarXml'];
				$lcFile = $lcRuta . '/' . $lcNombreArchivo;
			} else {
				$lcFile = $this->aConfig['rutaGuardarXml'] . $lcNombreArchivo;
			}

			$lnFile = fopen($lcFile, 'a');
			fputs($lnFile, $this->cJson);
			fclose($lnFile);
		} catch (\Exception $e) {
			$this->cError = "Err 007 - Excepción capturada:  {$e->getMessage()}.";
		}
	}


	/*
	 *	Obtiene la lista de IP válidas
	 *	@param string $tcIP: IP desde la que se recibe el mensaje
	 *	@return array indicando si es válida o no la IP
	 */
	public function obtenerIPsValidas()
	{
		global $goDb;
		$lcProvTec = mb_strtoupper(substr($this->aConfig['proveedor'],0,8), 'UTF-8');
		$this->aIPsValidas = [];
		$laValIPs = $goDb
			->select('TRIM(DE2TMA) AS IPVAL')
			->from('TABMAE')
			->where("TIPTMA='FACTELE' AND CL1TMA='IPVALIDA' AND CL2TMA='{$lcProvTec}'")
			->getAll('array');
		if ($goDb->numRows()>0) {
			foreach ($laValIPs as $laValIP) {
				$this->aIPsValidas[] = $laValIP['IPVAL'];
			}
		}
	}

	/*
	 *	Valida la IP desde la que se recibe el mensaje
	 *	@param string $tcIP: IP desde la que se recibe el mensaje
	 *	@return array indicando si es válida o no la IP
	 */
	public function validarIP($tcIP)
	{
		//	return ['success'=>true];
		if (in_array($tcIP, $this->aIPsValidas)) {
			$laReturn = ['success'=>true, 'message'=>'IP válida'];
		} else {
			$laReturn = ['success'=>false, 'message'=>"IP $tcIP NO válida, no se procesa el mensaje"];
		}
		return $laReturn;
	}


	/*
	 *	Respuestas de emisión de documentos de facturación electrónica
	 *	@param array $taRequest: Respuesta recibida de transfiriendo
	 */
	public function emisionFE($taRequest)
	{
		global $goDb;
		$lbSuccess = true;
		$lcMensaje = 'Documento procesado';

		// Buscar documento por identificador
		$lcIdDoc = $taRequest['identificador'] ?? '';
		if (!empty($lcIdDoc)) {
			$laDoc = $goDb
				->select('TIPR, FACT, NOTA, DOCA, ESTA')
				->from('FEMOV')
				->where(['DOEID'=>$lcIdDoc])
				->get('array');
			$lbEncontrado = $goDb->numRows()>0;

			if (!$lbEncontrado) {
				$laDocD = $goDb
					->select('FACTD, NOTAD')
					->from('FEMOVD')
					->where(['DESCD'=>$lcIdDoc])
					->get('array');
				if ($goDb->numRows()>0) {
					$laDoc = $goDb
						->select('TIPR, FACT, NOTA, DOCA, ESTA')
						->from('FEMOV')
						->where(['FACT'=>$laDocD['FACTD'],'NOTA'=>$laDocD['NOTAD']])
						->get('array');
					$lbEncontrado = $goDb->numRows()>0;
				}
			}

			if ($lbEncontrado) {
				$laDoc = array_map('trim', $laDoc);
				if ($laDoc['ESTA']=='02') {
					return ['success'=>true, 'mensaje'=>'Documento enviado exitosamente con anterioridad'];
				}
				$this->aDocAdd['IDDOC'] = $lcIdDoc;


				if (isset($taRequest['factura']) && isset($taRequest['factura']['esExitoso']) && $taRequest['factura']['esExitoso']==true) {

					$lcDocShaio = in_array($laDoc['TIPR'],['06FA','25FA']) ? $laDoc['FACT'] : substr($laDoc['TIPR'],2,2).'-'.$laDoc['NOTA'];
					$lcDocRecibido = $taRequest['factura']['resultado']['numeroDocumento'];
					if (in_array($lcDocRecibido, [$lcDocShaio, '-'.$lcDocShaio])) {

						$this->aDocAdd = [
							'CUFE' => $taRequest['factura']['resultado']['UUID'],
							'REQUESTID' => $taRequest['factura']['resultado']['identificadorTransaccion'],
							'LDF' => $taRequest['factura']['resultado']['fechaValidacionDian'],
							'URLREQ' => $taRequest['factura']['resultado']['URLPDF'],
							'DOCNUMBER' => $lcDocRecibido,
							'IDDOC' => $lcIdDoc,
						];
						$this->cDescrp = "Enviado UUID: {$this->aDocAdd['CUFE']}";

						if (isset($taRequest['esExitoso']) && $taRequest['esExitoso']==false) {
							$this->cEstado = '06'; // Pendiente CM
							$this->cDescrp .= ' | ';
							if (isset($taRequest['cuentaMedica']) && isset($taRequest['cuentaMedica']['errors'])) {
								$this->cDescrp .= $taRequest['factura']['mensaje'].'. ';
								$this->cError .= implode(' - ', array_column($taRequest['cuentaMedica']['errors'], 'message'));
							}

						} else {
							$this->cEstado = '02'; // Proceso exitoso
						}

						// Programar el ingreso para generar soportes
						if ($laDoc['TIPR']=='06FA' && substr($lcIdDoc,0,2)=='CM') {
							$laRta = (new DocumentosCM())->programarFactura($laDoc['FACT'], true);
						}

					} else {
						$this->cEstado = '05'; // Pendiente respuesta DIAN
						$this->cDescrp = "Documento recibido {$lcDocRecibido} no corresponde con {$lcDocShaio}.";
					}

				} else {
					$this->cEstado = '04'; // Devuelta con error
					$this->cDescrp = '';
					$this->cError = '';
					if (isset($taRequest['factura']) && isset($taRequest['factura']['esExitoso']) && $taRequest['factura']['esExitoso']==false) {
						$this->cDescrp = $taRequest['factura']['mensaje'].'. ';
						$this->cError = implode(' - ', array_column($taRequest['factura']['errores'], 'mensaje'));
					}
					if (isset($taRequest['cuentaMedica']) && isset($taRequest['cuentaMedica']['errors'])) {
						$this->cDescrp .= $taRequest['factura']['mensaje'].'. ';
						$this->cError .= implode(' - ', array_column($taRequest['cuentaMedica']['errors'], 'message'));
					}
					//	$this->cError = 'ERRORES: ' . implode(' - ', array_column($taRequest['factura']['errores'], 'mensaje'));
					//	if (count($taRequest['factura']['advertencias'])>0) {
					//		$this->cError .= ' - ADVERTENCIAS: ' . implode(' - ', array_column($taRequest['factura']['advertencias'], 'mensaje'));
					//	}
					if (empty($this->cError)){
						$this->cError = isset($taRequest['mensaje'])?'mensaje: '.$taRequest['mensaje']:'Sin mensajes de error a nivel de factura.';
					}
				}

				if (isset($taRequest['esExitoso']) && $taRequest['esExitoso']==false) {
					if (isset($taRequest['cuentaMedica']) && isset($taRequest['cuentaMedica']['errors'])) {
						$this->cDescrp .= $taRequest['factura']['mensaje'] ?? '';
						$this->cError .= implode(' - ', array_column($taRequest['cuentaMedica']['errors'], 'message'));
					}
					if (empty($this->cError)){
						$this->cError = 'No hay mensajes de error a nivel de factura.';
					}
				}
				$this->guardarEstado([
					'FACT'=>$laDoc['FACT'],
					'NOTA'=>$laDoc['NOTA'],
					'TIPR'=>$laDoc['TIPR'],
				]);

			// No se pudo encontrar documento por identificador
			} else {
				// OJO Guardar error e informar?
				$this->cError = 'No se pudo encontrar documento por identificador.';
				$lbSuccess = false;
				$lcMensaje = 'No se pudo encontrar documento por identificador.';
			}

		// No retorna identificador
		} else {
			// OJO Guardar error e informar?
			$this->cError = 'No existe identificador en el mensaje.';
			$lbSuccess = false;
			$lcMensaje = 'No existe identificador en el mensaje.';
		}

		return ['success'=>true, 'mensaje'=>$lcMensaje];
	}


	// SE ESTA USANDO PARA GUARDAR LOG DE MENSAJES RECIBIDOS
	private function fnEscribirLog($tcMensaje, $tbEcho=false)
	{
		$lcRuta = __DIR__ . '/../../facturae/Logs';
		if (!is_dir($lcRuta)) { mkdir($lcRuta, 0777, true); }
		$lcFileLog = $lcRuta . '/LogAccion_' . date('Ymd') . '.txt';
		$lcMensaje = date('Y-m-d H:i:s') . ' | ' . $tcMensaje . PHP_EOL;
		$lnFile = fopen($lcFileLog, 'a');
		fputs($lnFile, $lcMensaje);
		fclose($lnFile);
		if ($tbEcho) { echo $lcMensaje; }
	}

}