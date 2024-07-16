<?php
/* ********************  DOCUMENTO BASE PARA FACTURACIÓN ELECTRÓNICA  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FuncionesInv.php';


class FeConsultarDocumento
{
	/* Tipo de documento */
	protected $cTipDocXml = '';
	protected $cTipoFac = '';
	public $cKeyControl = '';

	/* Objeto para consultar base de datos */
	protected $oDb = null;

	/* Objeto de configuración de facturación */
	public $aCnf = null;

	public $oEmisor = null;

	/* Objeto para construir XML */
	protected $oDomDoc = null;

	/* Datos de factura */
	protected $aFactura = [];

	/* Detalles de factura */
	protected $aDetalles = [];

	/* Datos del paciente */
	protected $aPaciente = [];

	/* Datos del adquiriente */
	protected $aAdquiriente = [];

	/* Códigos  */
	protected $aCenEst = [];

	/* Errores  */
	protected $aError = [];

	/* Saltos de línea */
	protected $cSLD = '<br>';



	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig, $tcSLD = '')
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->aCnf = $taConfig;
		$this->cSLD = $tcSLD=='' ? $this->cSLD : $tcSLD;
	}


	/*
	 *	Organizar los datos del documento
	 */
	public function crearArrayDatos($tnFactura=0, $tnNota=0, $tnDocAdj=0)
	{
		$this->aError = [];
		$this->aFactura = [];
		$this->oDomDoc = new \DOMDocument($this->aCnf['versionXml'], $this->aCnf['encodingXml']);
	}


	/*
	 *	Organiza los datos del documento y los retorna en un array
	 */
	protected function crearArrayFac()
	{
		return [];
	}


	/*
	 *	Obtiene obligaciones para el tercero desde la tabla PRMGEN
	 *	@param string $tcNit: Nit con ceros al comienzo hasta 13 dígitos
	 */
	protected function obligacionesNit($tcNit)
	{
		$lcRetorna = '';
		$laObliga = $this->oDb
			->select('SUBSTR(GENDSC, 359, 2) TIPOBL')
			->from('PRMGEN')
			->where(['GENCOD'=>$tcNit, 'GENTIP'=>'TERMMG', 'GENSTS'=>'1', ])
			->get('array');
		if (is_array($laObliga)) {
			$laLst = array_keys($this->aCnf['codigosOblig']);
			if (in_array($laObliga['TIPOBL'], $laLst)) {
				$lcRetorna = $this->aCnf['codigosOblig'][ $laObliga['TIPOBL'] ];
			}
		}
		return $lcRetorna;
	}


	/*
	 *	Crear nodo Cabecera
	 *	@param array $taData: array con los siguientes elementos:
	 *			Prefijo (para notas)
	 *			Numero
	 *			FechaEmision
	 *			HoraEmision
	 *			FormaDePago
	 *			Vencimiento
	 *			MonedaFactura o MonedaNota
	 *			Observaciones
	 *			TipoFactura (para facturas)
	 *			TipoOperacion
	 *			SubTipoOperacion
	 *			LineasDeFactura o LineasDeNota
	 *			OrdenCompra (opcional)
	 *			FechaOrdenCompra (opcional
	 *			FormatoContingencia (si es factura de contingencia)
	 */
	protected function nodoCabecera($taData)
	{
		return $this->nodoGenerico('Cabecera', $taData+['Ambiente'=>$this->aCnf['cod_ambiente'][$this->aCnf['ambiente']]]);
	}


	/*
	 *	Crear nodo NumeracionDIAN (Facturas)
	 */
	protected function nodoNumeracionDIAN($tnFactura, $tcTipoRes='RESOLFAC')
	{
		global $goDb;
		$lcAmbiente = strtoupper(substr($this->aCnf['ambiente'],0,8));
		$laResFac = [];
		$lcWhere = "TIPTMA='FACTELE' AND CL1TMA='$tcTipoRes' AND CL2TMA='$lcAmbiente' AND ESTTMA<>'0'";
		// Listado de resoluciones válidas
		$laNumDian = $goDb
			->select('TRIM(CL3TMA) CODIGO, OP1TMA ACTIVO, OP4TMA DESDE, OP7TMA HASTA, TRIM(DE2TMA) RESFAC, TRIM(OP5TMA) KEYCTRL')
			->from('TABMAE')
			->where($lcWhere)
			->orderBy('CL3TMA','DESC')
			->getAll('array');
		if(is_array($laNumDian)){
			$lbDesactivar = false;
			foreach($laNumDian as $laDian){
				if (!$lbDesactivar){
					if ($tnFactura >= $laDian['DESDE'] && $tnFactura <= $laDian['HASTA']){
						$laResFac = json_decode($laDian['RESFAC']);
						$this->cKeyControl = $laDian['KEYCTRL'];
						$lbDesactivar = true;
					}
				} else {
					if($laDian['ACTIVO']=='1'){
						// Desactiva la resolución
						$lcUser = 'FE_WEB';
						$lcProg = 'FE_ENVIAR';
						$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
						$lcFecha = $ltAhora->format('Ymd');
						$lcHora  = $ltAhora->format('His');
						$lbRta = $goDb
							->tabla('TABMAE')
							->where("{$lcWhere} AND CL3TMA='{$laDian['CODIGO']}'")
							->actualizar([
								'OP1TMA'=>'0',
								'UMOTMA'=>$lcUser,
								'PMOTMA'=>$lcProg,
								'FMOTMA'=>$lcFecha,
								'HMOTMA'=>$lcHora,
							]);
					} else {
						break;
					}
				}
			}
		}

		return $this->nodoGenerico('NumeracionDIAN', $laResFac);
	}


	/*
	 *	Crear nodo ReferenciasNotas (Notas)
	 *	@param array $taData: array con los siguientes elementos:
	 *			fact:	Factura Asociada, solo si es factura electrónica
	 *			codn:	Codigo del concepto de la nota
	 *			dscr:	Descripcion del concepto de la nota
	 */
	protected function nodoReferenciasNotas($taData)
	{
		$loReturn = $this->oDomDoc->createElement('ReferenciasNotas');
			$loReferenciasNota = $this->oDomDoc->createElement('ReferenciaNota');
				if (isset($taData['fact'])) $loReferenciasNota->setAttributeNode(new \DOMAttr('FacturaAsociada', $taData['fact']));
				$loReferenciasNota->setAttributeNode(new \DOMAttr('CodigoNota', $taData['codn']));
				$loReferenciasNota->setAttributeNode(new \DOMAttr('DescripcionNota', $taData['dscr']));
			$loReturn->appendChild($loReferenciasNota);
			unset($loReferenciasNota);

		return $loReturn;
	}


	/*
	 *	Crear nodo FacturasRelacionadas (Notas)
	 *	@param array $taData: array de facturas. Cada factura es un array con los siguientes elementos:
	 *			fact:	prefijo y número factura
	 *			cufe:	cufe
	 *			fech:	fecha emisión factura
	 */
	protected function nodoFacturasRelacionadas($taData)
	{
		$loReturn = $this->oDomDoc->createElement('FacturasRelacionadas');
		foreach($taData as $laFactura) {
			$loFacturaRelacionada = $this->oDomDoc->createElement('FacturaRelacionada');
				$loFacturaRelacionada->setAttributeNode(new \DOMAttr('Numero', $laFactura['fact']));
				$loFacturaRelacionada->setAttributeNode(new \DOMAttr('CUFE', $laFactura['cufe']));
				$loFacturaRelacionada->setAttributeNode(new \DOMAttr('FechaEmisionFA', $laFactura['fech']));
			$loReturn->appendChild($loFacturaRelacionada);
			unset($loFacturaRelacionada);
		}

		return $loReturn;
	}


	/*
	 *	Crear nodo Notificacion
	 *	@param string $tcListaCorreos: lista de correos separados por coma
	 */
	protected function nodoNotificacion($tcListaCorreos)
	{
		$loReturn = false;

		if ( $tcListaCorreos!=='' ) {
			$laMailTemp = explode(',', $tcListaCorreos);
			$laMailNotifica = [];
			foreach($laMailTemp as $laMail) {
				if (filter_var($laMail, FILTER_VALIDATE_EMAIL)) {
					$laMailNotifica[] = $laMail;
				}
			}
			if (count($laMailNotifica)>0) {
				$loReturn = $this->oDomDoc->createElement('Notificacion');
					$loReturn->setAttributeNode(new \DOMAttr('Tipo', 'Mail'));
					$loReturn->setAttributeNode(new \DOMAttr('De', $this->aCnf['mailNotifica']));

					foreach($laMailNotifica as $laMail) {
						if (filter_var($laMail, FILTER_VALIDATE_EMAIL)) {
							$loPara = $this->oDomDoc->createElement('Para');
								$loPara->nodeValue = $laMail;
							$loReturn->appendChild($loPara);
							unset($loPara);
						}
					}
			}
		}

		return $loReturn;
	}


	/*
	 *	Crear nodo Cliente
	 *	@param array $taData: array con los siguientes elementos:
	 *			razons:		Razón Social / Nombre Comercial
	 *			nombre:		Nombre (persona natural)
	 *			apellido:	Apellido (persona natural)
	 *			tipoId:		Tipo Identificación
	 *			numeId:		Número Identificación
	 *			tipoPer:	Tipo Persona (1=jurídica, 2=natural)
	 *			regimen:	Régimen (48=resp iva, 49=no resp iva)
	 *			direccion:	Array con los siguientes elementos
	 *						Direccion
	 *						CodigoPais
	 *						NombrePais
	 *						CodigoDepartamento
	 *						NombreDepartamento
	 *						CodigoMunicipio
     *						NombreCiudad
	 *			respfiscal:	Array de obligaciones fiscales
	 *			tributo:	Array de tributos
	 *			contacto:	Nombre de cotacto
	 *			telefono:	Teléfono cotacto
	 *			correo:		Correo electrónico cotacto
	 */
	protected function nodoCliente($taData, $tbNoEsDS=true)
	{
		$loReturn = $this->oDomDoc->createElement($tbNoEsDS ? 'Cliente' : 'Emisor');
			$loReturn->setAttributeNode(new \DOMAttr('TipoPersona', $taData['tipoPer']));
		//	$loReturn->setAttributeNode(new \DOMAttr('TipoRegimen', $taData['regimen']));	// No obligatorio v1.8
			$loReturn->setAttributeNode(new \DOMAttr('TipoIdentificacion', $taData['tipoId']));
			$loReturn->setAttributeNode(new \DOMAttr('NumeroIdentificacion', $taData['numeId']));
			if (isset($taData['DV'])) {
				$loReturn->setAttributeNode(new \DOMAttr('DV', $taData['DV']));
			}
			$loReturn->setAttributeNode(new \DOMAttr('RazonSocial', $taData['razons']));
			if ($tbNoEsDS) {
				$loReturn->setAttributeNode(new \DOMAttr('NombreComercial', $taData['razons']));
			//	$loReturn->setAttributeNode(new \DOMAttr('NumeroMatriculaMercantil', $taData['']));

				if ($taData['tipoPer'] == 2) {
					$loPersonaNatural = $this->oDomDoc->createElement('PersonaNatural');
						$loPersonaNatural->setAttributeNode(new \DOMAttr('PrimerNombre', $taData['nombre']));
						$loPersonaNatural->setAttributeNode(new \DOMAttr('Apellido', $taData['apellido']));
					$loReturn->appendChild($loPersonaNatural);
					unset($loPersonaNatural);
				}
			}

			if (isset($taData['direccion'])) {
				$loReturn->appendChild($this->nodoGenerico('Direccion', $taData['direccion']));
				unset($loDireccion);
			}

			$loObligacionesCliente = $this->oDomDoc->createElement($tbNoEsDS ? 'ObligacionesCliente' : 'ObligacionesEmisor');
				$loCodigoObligacion = $this->oDomDoc->createElement('CodigoObligacion');
				foreach ( $taData['respfiscal'] as $lcCod ) {
					$loCodigoObligacion->nodeValue = $lcCod;
				}
				$loObligacionesCliente->appendChild($loCodigoObligacion);
				unset($loCodigoObligacion);
			$loReturn->appendChild($loObligacionesCliente);
			unset($loObligacionesCliente);


			if ($taData['contacto']!='' || $taData['telefono']!='' || $taData['correo']!='' ) {
				$loContacto = $this->oDomDoc->createElement('Contacto');
					if ( $taData['contacto']!=='' ) $loContacto->setAttributeNode(new \DOMAttr('Nombre',   $taData['contacto']));
					if ( $taData['telefono']!=='' ) $loContacto->setAttributeNode(new \DOMAttr('Telefono', $taData['telefono']));
					if ( $taData['correo']!=='' )   $loContacto->setAttributeNode(new \DOMAttr('Email',    $taData['correo']));
					//$loContacto->setAttributeNode(new \DOMAttr('Telfax', ''));
					//$loContacto->setAttributeNode(new \DOMAttr('Notas', ''));
				$loReturn->appendChild($loContacto);
				unset($loContacto);
			}

			$loTributoCliente = $this->oDomDoc->createElement($tbNoEsDS ? 'TributoCliente' : 'TributoEmisor');
				foreach ( $taData['tributo'] as $lcCod=>$lcDsc ) {
					$loTributoCliente->setAttributeNode(new \DOMAttr('CodigoTributo', $lcCod));
					$loTributoCliente->setAttributeNode(new \DOMAttr('NombreTributo', $lcDsc));
				}
			$loReturn->appendChild($loTributoCliente);
			unset($loTributoCliente);

		return $loReturn;
	}


	/*
	 *	Crear nodo MediosDePago
	 *	@param array $taData: array con los siguientes elementos:
	 *			medio: Medio de pago
	 *			forma: Forma de pago
	 *			fechv: Fecha vencimiento, obligatorio si forma de pago es crédito
	 */
	protected function nodoMediosDePago($taData)
	{
		$loReturn = $this->oDomDoc->createElement('MediosDePago');
			$loReturn->setAttributeNode(new \DOMAttr('CodigoMedioPago', $taData['medio']));
			$loReturn->setAttributeNode(new \DOMAttr('FormaDePago', $taData['forma']));
			if ($taData['forma'] == $this->aCnf['codFormaPago']['credito']) {
				$loReturn->setAttributeNode(new \DOMAttr('Vencimiento', $taData['fechv']));
			}
			/*
			$loIdentificadorPago = $this->oDomDoc->createElement('IdentificadorPago');
				$loReferenciaPago = $this->oDomDoc->createElement('ReferenciaPago');
					$loReferenciaPago->nodeValue = 'Referencia Pago';
				$loIdentificadorPago->appendChild($loReferenciaPago);
				unset($loReferenciaPago);
			$loReturn->appendChild($loIdentificadorPago);
			unset($loIdentificadorPago);
			*/

		return $loReturn;
	}


	/*
	 *	Crear nodo Impuestos
	 *	@param array $taData: array de impuestos. Cada impuesto debe ser un array con los siguientes elementos:
	 *			valor:	Valor del impueto
	 *			tipo:	Código impuesto
	 *			nombre:	Descripción impuesto
	 *			vrBase:	Valor base
	 *			porcen:	Porcentaje
	 *			redond:	Redondeo
	 *			codUM:	Código Unidad Medida Base
	 */
	protected function nodoImpuestos($taData)
	{
		$loReturn = $this->oDomDoc->createElement('Impuestos');
		foreach($taData as $laImpuesto) {
			$loImpuesto = $this->oDomDoc->createElement('Impuesto');
				$loImpuesto->setAttributeNode(new \DOMAttr('Valor', number_format($laImpuesto['valor'],2,'.','')));
				$loImpuesto->setAttributeNode(new \DOMAttr('Tipo', $laImpuesto['tipo']));
				$loImpuesto->setAttributeNode(new \DOMAttr('Nombre', $laImpuesto['nombre']));
				$loImpuesto->setAttributeNode(new \DOMAttr('Redondeo', number_format($laImpuesto['redond'] ?? 0,2,'.','')));
				$loSubtotal = $this->oDomDoc->createElement('Subtotal');
					$loSubtotal->setAttributeNode(new \DOMAttr('ValorBase', number_format($laImpuesto['vrBase'],2,'.','')));
					$loSubtotal->setAttributeNode(new \DOMAttr('Valor', number_format($laImpuesto['valor'] - $laImpuesto['redond'],2,'.','')));
					$loSubtotal->setAttributeNode(new \DOMAttr('Porcentaje', number_format($laImpuesto['porcen'],2,'.','')));
					//$loSubtotal->setAttributeNode(new \DOMAttr('ValorUnidadMedidaBase', $laImpuesto['']));
					$loSubtotal->setAttributeNode(new \DOMAttr('CodigoUnidadMedidaBase', $laImpuesto['codUM']));
					//$loSubtotal->setAttributeNode(new \DOMAttr('ValorTributoXUnidad', $laImpuesto['']));
				$loImpuesto->appendChild($loSubtotal);
				unset($loSubtotal);
			$loReturn->appendChild($loImpuesto);
			unset($loImpuesto);
		}

		return $loReturn;
	}


	/*
	 *	Crear nodo Retenciones
	 *	@param array $taData: array de retenciones. Cada retención debe ser un array con los siguientes elementos:
	 *			valor:	Valor retenido
	 *			tipo:	Código retención
	 *			nombre:	Descripción retención
	 *			vrBase:	Valor base
	 *			porcen:	Porcentaje
	 */
	protected function nodoRetenciones($taData)
	{
		$loReturn = $this->oDomDoc->createElement('Retenciones');
		foreach($taData as $laRetencion) {
			$loRetencion = $this->oDomDoc->createElement('Retencion');
				$loRetencion->setAttributeNode(new \DOMAttr('Valor', number_format($laRetencion['valor'],2,'.','')));
				$loRetencion->setAttributeNode(new \DOMAttr('Tipo', $laRetencion['tipo']));
				$loRetencion->setAttributeNode(new \DOMAttr('Nombre', $laRetencion['nombre']));
				$loSubtotal = $this->oDomDoc->createElement('Subtotal');
					$loSubtotal->setAttributeNode(new \DOMAttr('ValorBase', number_format($laRetencion['vrBase'],2,'.','')));
					$loSubtotal->setAttributeNode(new \DOMAttr('Valor', number_format($laRetencion['valor'],2,'.','')));
					$loSubtotal->setAttributeNode(new \DOMAttr('Porcentaje', number_format($laRetencion['porcen'],4,'.','')));
				$loRetencion->appendChild($loSubtotal);
				unset($loSubtotal);
			$loReturn->appendChild($loRetencion);
			unset($loRetencion);
		}

		return $loReturn;
	}


	/*
	 *	Crear nodo Anticipos
	 *	@param array $taData: array con los siguientes elementos:
	 *			id:     Consecutivo del anticipo
	 *			valor:  Valor
	 *			fechar:  fecha recepción
	 */
	protected function nodoAnticipos($taData)
	{
		$loReturn = $this->oDomDoc->createElement('Anticipos');
			$loAnticipo = $this->oDomDoc->createElement('Anticipo');
				$loAnticipo->setAttributeNode(new \DOMAttr('IDPago', $taData['id']));
				$loAnticipo->setAttributeNode(new \DOMAttr('ValorPagoAnticipo', $taData['valor']));
				$loAnticipo->setAttributeNode(new \DOMAttr('MonedaAnticipo', $this->aCnf['divisa']));
				$loAnticipo->setAttributeNode(new \DOMAttr('FechaRecepcion', $taData['fechar']));
				$loAnticipo->setAttributeNode(new \DOMAttr('InstruccionesAnticipos', ''));
				//$loAnticipo->setAttributeNode(new \DOMAttr('FechaPago', $taData['']));
				//$loAnticipo->setAttributeNode(new \DOMAttr('HoraPago', $taData['']));
			$loReturn->appendChild($loAnticipo);
			unset($loAnticipo);

		return $loReturn;
	}


	/*
	 *	Crear nodo DescuentosOCargos, realizado solo para un descuento
	 *	@param array $taData: array con los siguientes elementos:
	 *			id:     Consecutivo
	 *			porcen: Porcentaje
	 *			valor:  Valor
	 *			vrbase: Valor base
	 */
	protected function nodoDescuentoOCargo($taData)
	{
		$loReturn = $this->oDomDoc->createElement('DescuentosOCargos');
			$loDescuentoOCargo = $this->oDomDoc->createElement('DescuentoOCargo');
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('ID', $taData['id']));
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('Indicador', 'false')); // Descuento
				//$loDescuentoOCargo->setAttributeNode(new \DOMAttr('CodigoDescuento', '00')); // '00' o '01'
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('Justificacion', 'Descuento'));
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('Porcentaje', $taData['porcen']));
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('Valor', $taData['valor']));
				$loDescuentoOCargo->setAttributeNode(new \DOMAttr('ValorBase', $taData['vrbase']));
			$loReturn->appendChild($loDescuentoOCargo);
			unset($loDescuentoOCargo);

		return $loReturn;
	}


	/*
	 *	Crear nodo Totales
	 *	@param array $taTotales: array con los siguientes elementos:
	 *			bruto: Valor bruto
	 *			basei: Valor base imponible
	 *			impst: Valor impuestos - opcional
	 *			brimp: Valor bruto mas impuestos
	 *			descu: Valor descuentos - opcional
	 *			genrl: Valor general
	 *			abono: Valor anticipos - opcional
	 *			rtfte: Valor Retefuente - opcional
	 *			rtiva: Valor ReteIva - opcional
	 *			rtica: Valor ReteIca - opcional
	 */
	protected function nodoTotales($taData)
	{
		$loReturn = $this->oDomDoc->createElement('Totales');
			$loReturn->setAttributeNode(new \DOMAttr('Bruto', $taData['bruto']));
			$loReturn->setAttributeNode(new \DOMAttr('BaseImponible', $taData['basei']));
			$loReturn->setAttributeNode(new \DOMAttr('BrutoMasImpuestos', $taData['brimp']));
			if (isset($taData['impst'])) if (intval($taData['impst'])>0) $loReturn->setAttributeNode(new \DOMAttr('Impuestos', $taData['impst']));
			if (isset($taData['descu']) && !empty($taData['descu'])) if (intval($taData['descu'])>0) $loReturn->setAttributeNode(new \DOMAttr('Descuentos',$taData['descu']));
			if (isset($taData['cargo']) && !empty($taData['cargo'])) if (intval($taData['cargo'])>0) $loReturn->setAttributeNode(new \DOMAttr('Cargos',$taData['descu']));
			if (isset($taData['abono'])) if (intval($taData['abono'])>0) $loReturn->setAttributeNode(new \DOMAttr('Anticipo',  $taData['abono']));
			//$loReturn->setAttributeNode(new \DOMAttr('Retenciones', $taData['']));
			//$loReturn->setAttributeNode(new \DOMAttr('Redondeo', $taData['']));
			//$loReturn->setAttributeNode(new \DOMAttr('TotalDescuentosLineas', $taData['']));
			//$loReturn->setAttributeNode(new \DOMAttr('TotalCargosLineas', $taData['']));
			if (isset($taData['rtfte'])) if (intval($taData['rtfte'])>0) $loReturn->setAttributeNode(new \DOMAttr('TotalReteFuente', $taData['rtfte']));
			if (isset($taData['rtiva'])) if (intval($taData['rtiva'])>0) $loReturn->setAttributeNode(new \DOMAttr('TotalReteIva',	 $taData['rtiva']));
			if (isset($taData['rtica'])) if (intval($taData['rtica'])>0) $loReturn->setAttributeNode(new \DOMAttr('TotalReteIca',	 $taData['rtica']));
			$loReturn->setAttributeNode(new \DOMAttr('General', $taData['genrl']));

		return $loReturn;
	}


	/*
	 *	Crear nodo Linea
	 *	@param array $taData: array con los siguientes elementos:
	 *			cons: consecutivo de línea
	 *			codg: codigo producto
	 *			desc: descripción producto
	 *			cant: cantidad producto
	 *			undm: unidad de medida
	 *			vrud: valor unidad
	 *			vrtt: valor total
	 *			impu: array con los impuestos (ver función nodoImpuestos)
	 *			rete: array con las retenciones (ver función nodoRetenciones)
	 *			cadd: campos adicionales, opcional, array con los siguientes datos
	 *				'conceptoglosa' => descripción glosa,
	 *				'valorglosado' => valor glosado,
	 */
	protected function nodoLinea($taData)
	{
		$loReturn = $this->oDomDoc->createElement('Linea');

			$laDetalle = [
				'NumeroLinea' => $taData['cons'],
				'Cantidad' => $taData['cant'],
				'UnidadMedida' => $taData['undm'],
				'CantidadBase' => $taData['cant'],
				'UnidadCantidadBase' => $taData['undm'],
				'Descripcion' => $taData['desc'],
				'PrecioUnitario' => $taData['vrud'],
				'SubTotalLinea' => $taData['vrtt'],
				//'Nota' => '',
				//'CantidadXEmpaque' => '',
				//'Marca' => '',
				//'NombreModelo' => ''
				//'ValorTotalItem' => $taData[''],
			];
			if ($this->cTipDocXml=='DS') {
				if (isset($taData['fecc'])) {
					$laDetalle['FechaCompra'] = $taData['fecc'];
				}
				if (isset($taData['gent'])) {
					$laDetalle['CodigoFormaGeneracionTransmision'] = $taData['gent'];
					$laDetalle['DescripcionFormaGeneracionTransmision'] = $this->aCnf['generaTransm'][$taData['gent']];
				}
				$laDetalle['ValorTotalItem'] = $taData['vrtt'];
			}
			$loReturn->appendChild($this->nodoGenerico('Detalle', $laDetalle));
			unset($loDetalle);

			$loCodificacionesEstandar = $this->oDomDoc->createElement('CodificacionesEstandar');
				$loCodificacionEstandar = $this->oDomDoc->createElement('CodificacionEstandar');
					$loCodificacionEstandar->setAttributeNode(new \DOMAttr('CodigoArticulo', $taData['codg']));
					$loCodificacionEstandar->setAttributeNode(new \DOMAttr('CodigoEstandar', '999')); // Estándar de adopción del contribuyente
				$loCodificacionesEstandar->appendChild($loCodificacionEstandar);
				unset($loCodificacionEstandar);
			$loReturn->appendChild($loCodificacionesEstandar);
			unset($loCodificacionesEstandar);

			if (isset($taData['impu'])) {
				$loReturn->appendChild($this->nodoImpuestos($taData['impu']));
			}
			if (isset($taData['rete'])) {
				$loReturn->appendChild($this->nodoRetenciones($taData['rete']));
			}

			if (isset($taData['cadd'])) {
				$loDatosAdicionales = $this->oDomDoc->createElement('DatosAdicionales');
				foreach($taData['cadd'] as $lcNombre => $lcValor) {
					$loCampoAdicional = $this->oDomDoc->createElement('CampoAdicional');
						$loCampoAdicional->setAttributeNode(new \DOMAttr('Nombre', $lcNombre));
						$loCampoAdicional->setAttributeNode(new \DOMAttr('Valor', $lcValor));
					$loDatosAdicionales->appendChild($loCampoAdicional);
				}
				$loReturn->appendChild($loDatosAdicionales);
			}

		return $loReturn;
	}


	/*
	 *	Crear nodo Extensiones/DatosAdicionales
	 */
	protected function nodoExtensiones($taData, $taDataSalud=false, $tcDatExt='extensiones')
	{
		$loReturn = $this->oDomDoc->createElement('Extensiones');

			$loDatosAdicionales = $this->oDomDoc->createElement('DatosAdicionales');
				if (is_string($tcDatExt) && strlen($tcDatExt)>0) {
					$laData = array_merge($this->aCnf[$tcDatExt]??[], $taData);
				} else {
					$laData = $taData;
				}
				foreach($laData as $lcNombre => $lcValor) {
					$loCampoAdicional = $this->oDomDoc->createElement('CampoAdicional');
					$loCampoAdicional->setAttributeNode(new \DOMAttr('Nombre', $lcNombre));
					$loCampoAdicional->setAttributeNode(new \DOMAttr('Valor', $lcValor));
					$loDatosAdicionales->appendChild($loCampoAdicional);
					unset($loCampoAdicional);
				}
			$loReturn->appendChild($loDatosAdicionales);
			unset($loDatosAdicionales);

		// Interoperabilidad
		if (is_array($taDataSalud)) {
			$loInteroperabilidad = $this->oDomDoc->createElement('Interoperabilidad');
			//$loInteroperabilidad->setAttributeNode(new \DOMAttr('URLAdjunto', ''));

				$loGrupo = $this->oDomDoc->createElement('Grupo');
				$loGrupo->setAttributeNode(new \DOMAttr('Nombre', 'Sector Salud'));

					$loCategoria = $this->oDomDoc->createElement('Categoria');
					$loCategoria->setAttributeNode(new \DOMAttr('Nombre', 'Usuario'));

					foreach($taDataSalud as $lcKey => $laDato) {
						$loCampoAdicional = $this->oDomDoc->createElement('CampoAdicional');
						$loCampoAdicional->setAttributeNode(new \DOMAttr('Nombre', $this->aCnf['Salud']['campos'][$lcKey][0]));
						$loCampoAdicional->setAttributeNode(new \DOMAttr('Valor', $laDato['Valor']));
						$loCampoAdicional->setAttributeNode(new \DOMAttr('NombreEsquema', $this->aCnf['Salud']['campos'][$lcKey][1]));
						$loCampoAdicional->setAttributeNode(new \DOMAttr('IDEsquema', $laDato['IdEsquema']??''));
						$loCategoria->appendChild($loCampoAdicional);
						unset($loCampoAdicional);
					}

					$loGrupo->appendChild($loCategoria);
					unset($loCategoria);

				$loInteroperabilidad->appendChild($loGrupo);
				unset($loGrupo);

			$loReturn->appendChild($loInteroperabilidad);
			unset($loInteroperabilidad);
		}


		return $loReturn;
	}


	/*
	 *	Crear nodo padre con varios genéricos con solo atributos
	 *	@param string $tcNodoPadre: nombre del nodo Padre
	 *	@param string $tcNodoHijo: nombre de los nodos Hijo
	 *	@param array $taDatas: array que debe contener uno o más array con los elementos nombre->valor
	 */
	protected function nodoPadreGenerico($tcNodoPadre, $tcNodoHijo, $taDatas)
	{
		$loReturn = $this->oDomDoc->createElement($tcNodoPadre);
		foreach ($taDatas as $laData) {
			$loNodoHijo = $this->nodoGenerico($tcNodoHijo, $laData);
			$loReturn->appendChild($loNodoHijo);
			unset($loNodoHijo);
		}

		return $loReturn;
	}


	/*
	 *	Crear nodo genérico con solo atributos
	 *	@param string $tcNodo: nombre del nodo
	 *	@param array $taData: array con los elementos nombre->valor
	 */
	protected function nodoGenerico($tcNodo, $taData)
	{
		$loReturn = $this->oDomDoc->createElement($tcNodo);
		foreach ($taData as $lcNombre=>$lcValor) {
			$loReturn->setAttributeNode(new \DOMAttr($lcNombre, $lcValor));
		}

		return $loReturn;
	}


	/* Datos de factura */
	public function aDatosFactura()
	{
		return $this->aFactura;
	}

	/* Detalles de factura */
	public function aDatosDetalles()
	{
		return $this->aDetalles;
	}

	/* Datos del Adquiriente */
	public function aDatosAdquiriente()
	{
		return $this->aAdquiriente;
	}

	/* Datos del Paciente */
	public function aDatosPaciente()
	{
		return $this->aPaciente;
	}

	/* Retorna array aError */
	public function aError()
	{
		return $this->aError;
	}

}