<?php
/* ********************  DOCUMENTO SOPORTE ADQUISICIONES  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FeConsultar.php';


class FeConsultarDSFA extends FeConsultar
{
	protected $aDescCargos = [];
	protected $bCalcularPorLinea = [
		'ReteFte' => false,
		'ReteIca' => false,
		'ReteIva' => false,
	];

	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig)
	{
		parent::__construct($taConfig);

		$this->cTipDocXml = 'DS';
		$this->cTipoFac = 'DSFA';
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayDatos($tnFactura=0, $tnNota=0, $tnDocAdj=0)
	{
		parent::crearArrayDatos();

		$this->consultaFactura($tnFactura);
		return count($this->aError)>0 ? [] : $this->crearArrayFac();
	}


	/*
	 *	Consulta datos de factura
	 */
	private function consultaFactura($tcNum)
	{
		$lcSucursal = '01';
		$lcTipoCbt = 'DS';	// Tipo de documento contable
		$lcDocFac = str_pad($tcNum,6,'0',STR_PAD_LEFT);

		// Consultar fecha y número de Factura
		$laFemov = $this->oDb
			->select('FECC,IDTR,USRC')
			->from('FEMOV')
			->where([ 'FACT'=>$tcNum, 'NOTA'=>0, 'TIPR'=>'DSFA' ])
			->getAll('array');

		// Validar que exista la factura en FEMOV
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró el documento tipo {$this->cTipoFac} número {$tcNum} en FEMOV.",
			];
			return;
		}

		// Consulta movimiento de Factura
		$lnFechaFac = strval($laFemov['0']['FECC']);
		$lcNumFac = ltrim(trim($laFemov['0']['IDTR']),'0');
		$lnAnio = substr($lnFechaFac, 0, 4);
		$lnMes =  substr($lnFechaFac, 4, 2);

 		$laMovsFac = $this->oDb
			->select('MOVSEC, MOVCTA, MOVAUX, MOVVRL, MOVCMP, MOVFCH, MOVHOR, MOVLOC, MOVCAN, MOVUSR')
			->select('(SELECT PLNDSC FROM CTBPLN WHERE MOVCTA=PLNCTA ORDER BY PLNAÑO DESC FETCH FIRST 1 ROWS ONLY) AS DSCCTA')
			->select("CASE WHEN SUBSTR(MOVCTA,1,1) IN ('5', '6') THEN (SELECT TRIM(IFNULL(SUBSTR(TABDSC,1,38),'')) FROM PRMTAB WHERE TABTIP='017' AND TABCOD=SUBSTR(MOVAUX,7,3)) ELSE '' END AS DSC_GC")
			->select("(SELECT IFNULL(USRNBR,'') FROM SEGUSR WHERE USRUSR=MOVUSR FETCH FIRST 1 ROWS ONLY) AS ELABORO")
			->from('CTBMOV')
			->where([ 'MOVSUC'=>$lcSucursal, 'MOVAÑO'=>$lnAnio, 'MOVMES'=>$lnMes, 'MOVCBT'=>$lcTipoCbt, 'MOVDOC'=>$lcDocFac ])
			->where("MOVTIP<>'99'") // Reunión 2022-08-09 Adriana Pinilla
			->getAll('array');


		// Validar que exista la factura
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró el documento tipo {$this->cTipoFac} número {$tcNum}.",
			];
			return;
		}

		$this->aFactura = [
			'NumDS' => $tcNum,
			'Numero' => $lcNumFac,
			'ValorTotal' => 0,
			'ValorBruto' => 0,
			'ReteFte'=>[],
			'ReteIca'=>[],
			'ReteIva'=>[],
		];
		$laRetenciones = [
			'ReteFte'=>[],
			'ReteIca'=>[],
			'ReteIva'=>[],
		];
		$lcReteKeys = [
			'2365' => 'ReteFte',
			'2368' => 'ReteIca',
		];

		foreach ($laMovsFac as $lnKey => $laMov) {
			$laMov = array_map('trim', $laMov);
			$lcCta2 = substr($laMov['MOVCTA'],0,2);
			$lcCta4 = substr($laMov['MOVCTA'],0,4);

			switch (true) {

				/********** Cabecera de factura **********/
				case in_array($lcCta4, ['2205','2335']) && $laMov['MOVCTA']!=='23352502':

					$this->aFactura += [
						'DocContab' => $lcDocFac,
						'Fecha' => $laMov['MOVFCH'],
						'Hora' => $laMov['MOVHOR'].'00',
						'ValorTotal' => abs($laMov['MOVVRL']),
						'DocCliente' => substr($laMov['MOVAUX'],0,13),
						'Moneda' => $laMov['MOVLOC'],
						'Usuario' => $laMov['MOVUSR'],
						'Elaboro' => $laMov['ELABORO'],
					];
					$this->aFactura['ValorTotal'] += abs($laMov['MOVVRL']);
					$this->obtenerDatosTercero();

					break;


				/********** Retenciones **********/
				case in_array($lcCta4, ['2365','2368']):
					$lcReteKey = $lcReteKeys[$lcCta4];
					$this->aFactura['Cod_Ciudad'] = substr($laMov['MOVAUX'], 0, 5);
					$laRetenc = [
						'valor' => abs($laMov['MOVVRL']),
						'base' => abs($laMov['MOVCAN']),
						'porcentaje' => round(floatval(str_replace(',','.',trim($laMov['MOVCMP'])))*($lcCta4=='2368'?10:1),5),
					];
					$lbEsNuevo = true;
					foreach($this->aFactura[$lcReteKey] as $lnKey=>$laRete){
						if ($laRetenc['porcentaje']==$laRete['porcentaje']) {
							$this->aFactura[$lcReteKey][$lnKey]['valor'] += abs($laMov['MOVVRL']);
							$this->aFactura[$lcReteKey][$lnKey]['base'] += abs($laMov['MOVCAN']);
							$lbEsNuevo = false;
							break;
						}
					}
					if ($lbEsNuevo) {
						$this->aFactura[$lcReteKey][] = $laRetenc;
					}
					$laRetenciones[$lcReteKey][] = $laRetenc + ['detalle' => -1,];
					break;


				/********** Descuentos o Cargos **********/
				case in_array($lcCta4, ['1330']): // 13309501 y 13309502
					$this->aDescCargos[] = [
						'Indicador' => $laMov['MOVVRL']>0 ? 'false' : 'true',
						'Justificacion' => $laMov['DSCCTA'], // $laMov['DSC_GC'] ?? $laMov['DSCCTA'],
						'Valor' => abs($laMov['MOVVRL']),
					];
					break;


				/********** Detalles de factura **********/
				case in_array($lcCta2, ['51','52','61']) || $laMov['MOVCTA']=='23352502':
					$this->aFactura['ValorBruto'] += abs($laMov['MOVVRL']);
					$this->aDetalles[] = [
						'Codigo' => $laMov['MOVCTA'],
						'Descrip' => $laMov['DSCCTA'],
						'Detalle' => $laMov['DSC_GC'],
						'Valor' => $laMov['MOVVRL'],
					];
					break;
			}
		}
		//$this->aFactura['Numero'] = str_replace($this->aCnf['resDS'][$this->aCnf['ambiente']]['PrefijoNumeracion'], '', ltrim($this->aFactura['Numero'], '0'));
		//$this->aFactura['Numero'] = ltrim($this->aFactura['Numero'], '0');

		foreach(array_keys($laRetenciones) as $lcReteKey){
			if (count($laRetenciones[$lcReteKey])>0) {
				// Si hay impuestos, recorre para poner los datos en el detalle, se supone que todos los detalles tienen retenciones
				$lbTodos = true;
				foreach ($this->aDetalles as $lnKey=>$laDetalle) {
					$lbEncontrado = false;
					foreach ($laRetenciones[$lcReteKey] as $lnKeyI=>$laRetencion) {
						if ($laRetencion['detalle']==-1 && $laRetencion['base']==$laDetalle['Valor']) {
							$this->aDetalles[$lnKey][$lcReteKey]=$laRetencion;
							$laRetenciones[$lcReteKey][$lnKeyI]['detalle'] = $lnKey;
							$lbEncontrado = true;
							break;
						}
					}
					$lbTodos = $lbTodos && $lbEncontrado;
				}
				// si no encontró todos los detalles debe calcular el retefuente por línea
				$this->bCalcularPorLinea[$lcReteKey] = !$lbTodos;
			}
		}
	}


	/*
	 *	Datos del proveedor
	 */
	private function obtenerDatosTercero()
	{
		$laPrmTer = $this->oDb
			->select('TE1TIP, TE1COD, TE1SOC, TE1PER, TE1DIG')
			->select("IFNULL(TE2COM,'') TE2COM, IFNULL(TE2LEG,'') TE2LEG, IFNULL(TE2DIR,'') TE2DIR, IFNULL(TE2CIU,'') TE2CIU")
			->select("IFNULL(TE2TEL,'') TE2TEL, IFNULL(TE2MAI,'') TE2MAI, IFNULL(TE4IVA,'') TE4IVA, IFNULL(TABDSC,'') CIUDAD")
			->select("IFNULL(SUBSTR(DESPAI, 1, LOCATE(' - ', DESPAI)),'') DESPAI, IFNULL(DESDEP,'') DESDEP, IFNULL(DESCIU,'') CIUDAD2")
			->select("SUBSTR(IFNULL(GENDSC,''), 384, 2) PROCEDE")
			->from('PRMTE1')
			->leftJoin('PRMTE2','TE1COD=TE2COD')
			->leftJoin('PRMTE4','TE1COD=TE4COD')
			->leftJoin('PRMTAB',"TABTIP='010' AND TABCOD=TE2CIU")
			->leftJoin('PRMGEN',"GENTIP='TERMMG' AND TE1COD=GENCOD")
			->leftJoin('COMPAI',"SUBSTR(TE2CIU,1,2)=SUBSTR(DESPAI, LOCATE(' - ', DESPAI) + 3, 2)")
			->leftJoin('COMDEP','CODPAI=PAIDEP AND INTEGER(SUBSTR(TABDSC,36,2))=CODDEP')
			->leftJoin('COMCIU','CODPAI=PAICIU AND CODDEP=DEPCIU AND INTEGER(SUBSTR(TABDSC,38,3))=CODCIU')
			->where([ 'TE1COD' => $this->aFactura['DocCliente'] ])
			->get('array');
		if ( $this->oDb->numRows()>0 ) {
			$laPrmTer = array_map('trim', $laPrmTer);
			if ($laPrmTer['TE1PER']=='J') {
				$lcApelli = '';
				$lcNombre = $laPrmTer['TE1SOC'];
			} else {
				$laNomApe = explode(';', $laPrmTer['TE1SOC']);
				if (count($laNomApe)>1){
					$lcApelli = trim($laNomApe[0].' '.$laNomApe[1]);
					$lcNombre = trim($laNomApe[2].' '.$laNomApe[3]);
				} else {
					$laNomApe = explode(' ', $laPrmTer['TE1SOC']);
					if (count($laNomApe)==2) {
						$lcApelli = $laNomApe[0];
						$lcNombre = $laNomApe[1];
					} else if (count($laNomApe)==3) {
						$lcApelli = trim($laNomApe[0].' '.$laNomApe[1]);
						$lcNombre = $laNomApe[2];
					} else if (count($laNomApe)>3) {
						$lcApelli = trim($laNomApe[0].' '.$laNomApe[1]);
						$lcNombre = trim($laNomApe[2].' '.$laNomApe[3]);
					} else {
						$lcApelli = $laPrmTer['TE1SOC'];
						$lcNombre = $laPrmTer['TE1SOC'];
					}
				}
			}

			$lcDocCli = intval($laPrmTer['TE1COD']).'';
			$this->aAdquiriente = [
				'apellido' => $lcApelli,
				'nombre' => $lcNombre,
				'contacto' => $laPrmTer['TE2COM']!='' ? $laPrmTer['TE2COM'] : ( $laPrmTer['TE2LEG']!='' ? $laPrmTer['TE2LEG'] : '' ),
				'tipoId' => $laPrmTer['TE1TIP'],
				'numeId' => $lcDocCli,
				'digVer' => $this->aCnf['parFac']['calcularDVenDS'] ? FeFunciones::digitoVerificacion($lcDocCli) : $laPrmTer['TE1DIG'],
				'tipoPer' => $laPrmTer['TE1PER']=='J' ? $this->aCnf['codTipoPer']['juridica'] : $this->aCnf['codTipoPer']['natural'],
				'direccion' => $laPrmTer['TE2DIR']=='' ? 'NO EXISTE INFORMACIÓN' : $laPrmTer['TE2DIR'],
				'telefono' => $laPrmTer['TE2TEL'],
				'correo' => $laPrmTer['TE2MAI'],
				'Procedencia' => empty($laPrmTer['PROCEDE']) ? $this->aCnf['cliente']['tipoOperacion'] : $laPrmTer['PROCEDE'],
				//'Procedencia' => $laPrmTer['PROCEDE'],
			];
			if ($this->aAdquiriente['contacto']=='') {
				$this->aAdquiriente['contacto'] = $this->aAdquiriente['nombre'].' '.$this->aAdquiriente['apellido'];
			}
			if ($this->aAdquiriente['tipoId']=='' || !in_array($this->aAdquiriente['tipoId'], array_keys($this->aCnf['tiposId']))) {
				$this->aAdquiriente['tipoId'] = $laPrmTer['TE1PER']=='J' ? 'N' : 'C';
			}

			$lcCodCiudadShaio = $laPrmTer['TE2CIU'];
			$lcCodPais = substr($lcCodCiudadShaio,0,2);
			$lcCiudad = trim(substr($laPrmTer['CIUDAD'],0,35));
			$lcCodCiudad = substr($laPrmTer['CIUDAD'],-5);
			$lcCodDepS = intval(substr($lcCodCiudad,0,2));
			$lcCodCiuS = intval(substr($lcCodCiudad,-3));

			$this->aAdquiriente['codpais'] = $lcCodPais;
			$this->aAdquiriente['pais'] = '';
			$this->aAdquiriente['coddepto'] = $lcCodDepS;
			$this->aAdquiriente['depto'] = '';
			$this->aAdquiriente['codciudad'] = $lcCodCiudad;
			$this->aAdquiriente['ciudad'] = $lcCiudad;

			$laPais = $this->oDb
				->select('CODPAI, SUBSTR(DESPAI, 1, LOCATE(\' - \', DESPAI)) DESPAI')
				->from('COMPAI')
				->where("SUBSTR(DESPAI, LOCATE(' - ', DESPAI) + 3, 2)='$lcCodPais'")
				->get('array');
			if ( $this->oDb->numRows()>0 ) {
				$this->aAdquiriente['pais'] = trim($laPais['DESPAI']);
				$lcCodPaisS = $laPais['CODPAI'];

				$laDpto = $this->oDb
					->select('DESDEP')
					->from('COMDEP')
					->where("PAIDEP=$lcCodPaisS AND CODDEP=$lcCodDepS")
					->get('array');
				if ( $this->oDb->numRows()>0 ) {
					$this->aAdquiriente['depto'] = trim($laDpto['DESDEP']);
				}
				$laCiu = $this->oDb
					->select('DESCIU')
					->from('COMCIU')
					->where("PAICIU=$lcCodPaisS AND DEPCIU=$lcCodDepS AND CODCIU=$lcCodCiuS")
					->get('array');
				if ( $this->oDb->numRows()>0 ) {
					$this->aAdquiriente['ciudad'] = trim($laCiu['DESCIU']);
				}
				if ($lcCodCiudad=='11001') {
					$this->aAdquiriente['depto'] = $this->aAdquiriente['ciudad'];
				}
			}

			// Configuración predeterminada según tipo de persona
			// TributoCliente
			$this->aAdquiriente['tributo'] = $this->aCnf['cliente']['tributo'][$this->aAdquiriente['tipoPer']];
			// ObligacionesCliente
			$this->aAdquiriente['respfiscal'] = $this->aCnf['cliente']['respfiscal'][$this->aAdquiriente['tipoPer']];
			if ($this->aAdquiriente['tipoId']=='N') {
				$lcOblig = $this->obligacionesNit($this->aFactura['DocCliente']);
				if (!empty($lcOblig)) $this->aAdquiriente['respfiscal'] = [ $lcOblig ];
			}
			// Forma y Medio de Pago
			$this->aAdquiriente['FormaDePago'] = $this->aCnf['cliente']['formaPago'][0];
			$this->aAdquiriente['MedioDePago'] = $this->aCnf['cliente']['medioPago'][0];

		}
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayFac()
	{
		$laFactura = [];

		$lbPrueba = $this->aCnf['ambiente']=='Pruebas';

		$lcFechaEmision = FeFunciones::formatFecha($this->aFactura['Fecha']);
		$lcHoraEmision = FeFunciones::formatHora($this->aFactura['Hora']);

		if ($lbPrueba) {
			$this->aFactura['NumDS'] += $this->aCnf['resDS']['Pruebas']['ConsecutivoInicial'];
			$this->aFactura['Numero'] = $this->aCnf['resDS']['Pruebas']['PrefijoNumeracion'] . $this->aFactura['NumDS'];
			$lcFechaEmision = date('Y-m-d');
		}

		$lnNumDiasVence=$this->aCnf['plazoVence'];
		$lcFechaVence = date('Y-m-d', strtotime($lcFechaEmision."+ $lnNumDiasVence days"));

		$lbEsPersonaJurídica = $this->aAdquiriente['tipoPer'] == $this->aCnf['codTipoPer']['juridica'];

		// Contingencia
		$lbNoEsContingencia = $this->cTipDocXml=='DS';
		$lcFormatoContingencia = 'Papel'; // Talonario, Papel
		$lcMoneda = $this->aCnf['divisa']; // Moneda utilizada para facturar

		// Totales
		$lnBruto = $this->aFactura['ValorBruto'];
		$lnBaseImponible = $this->aFactura['ValorBruto'];;
		$lnTotalPagar = $this->aFactura['ValorBruto']; // $this->aFactura['ValorTotal'];

		// Valor factura en letras
		$lcVrLetras = NumerosALetras::convertir($lnTotalPagar,['PESO','PESOS'],['CENTAVO','CENTAVOS']) . $this->aCnf['txtDespuesValorLetras'];



		/********************* Cabecera *********************/
		$lcPrefijo = substr($this->aFactura['Numero'], 0, strlen($this->aFactura['Numero'])-strlen($this->aFactura['NumDS']));
		$laFactura['Documento'] = [
			'Prefijo' => $lcPrefijo,
			'NumDoc' => substr($this->aFactura['Numero'], strlen($lcPrefijo)),
			//'NumDoc' => intval($this->aFactura['NumDS']),
		];
		$lcTipoOperacion = $this->aAdquiriente['Procedencia'];
		$laData = [
			'Numero' => $this->aFactura['Numero'],
			'FechaEmision' => $lcFechaEmision,
			'HoraEmision' => $lcHoraEmision,
			'Vencimiento' => $lcFechaVence,
			'TipoOperacion' => $lcTipoOperacion,
			'TipoDocumentoSoporte' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['tipoFac'],
			'Observaciones' => '',
			'MonedaDocumentoSoporte' => $this->aCnf['divisa'],
			// 'OrdenCompra' => '',
			// 'FechaOrdenCompra' => '',
			'LineasDeDocumentoSoporte' => count($this->aDetalles),
			// 'NitFabricanteSoftware' => '',
			// 'RazonSocialFabricanteSoftware' => '',
			// 'NombreSoftware' => '',
		];
		if (!$lbNoEsContingencia) $laData['FormatoContingencia'] = $lcFormatoContingencia;
		$laFactura['Cabecera'] = $laData;


		/********************* NumeracionDIAN *********************/
		$laFactura['NumeracionDIAN'] = [$this->aFactura['NumDS'], 'RESOLDSP'];


//	*********************************************************************
//	Para TipoOperacion = 10
//		=> TipoIdentificacion debe ser 31 NIT
//		=> Obligatorio informar DV
//		=> CodigoPais = 'CO'
//		=> NombrePais = 'COLOMBIA'
//		=> IdiomaPais = 'es'
//	Para TipoOperacion = 11
//		=> TipoIdentificacion ver tabla 3.1.2
//		=> CodigoPais y NombrePais ver tabla 5.1
//		=> CodigoDepartamento, NombreDepartamento, CodigoMunicipio y NombreCiudad no se envían
//		=> IdiomaPais ver tabla 4.2
//	*********************************************************************
//	En correo del 2022-04-07 16:37 indican que siempre es 10, sin embargo se deja previsto 11
//	*********************************************************************

		if ($lbPrueba) {
			if (empty($this->aAdquiriente['coddepto']) || $this->aAdquiriente['coddepto']==0) {
				$this->aAdquiriente['coddepto'] = '11';
				$this->aAdquiriente['depto'] = 'Bogotá';
				$this->aAdquiriente['codciudad'] = '11001';
				$this->aAdquiriente['ciudad'] = 'Bogotá, D.C.';
			}
		}



		/********************* Emisor *********************/
		if ($lcTipoOperacion=='10') {
			$laDireccion = [
				'Direccion' => $this->aAdquiriente['direccion'],
				'CodigoPais' => 'CO',
				'NombrePais' => 'COLOMBIA',
				'IdiomaPais' => 'es',
				'CodigoDepartamento' => $this->aAdquiriente['coddepto'],
				'NombreDepartamento' => $this->aAdquiriente['depto'],
				'CodigoMunicipio' => $this->aAdquiriente['codciudad'],
				'NombreCiudad' => $this->aAdquiriente['ciudad'],
			];
			$laData = [
				'DV' => $this->aAdquiriente['digVer'],
				'tipoId' => '31',
			];
		} else {
			$laDireccion = [
				'Direccion' => $this->aAdquiriente['direccion'],
				'CodigoPais' => $this->aAdquiriente['codpais'],
				'NombrePais' => $this->aAdquiriente['pais'],
				'NombreCiudad' => $this->aAdquiriente['ciudad'],
				'IdiomaPais' => 'es',
			];
			$laData = [
				'tipoId' => $this->aCnf['tiposId'][$this->aAdquiriente['tipoId']],
			];
		}
		$laData += [
			'tipoPer' => $this->aAdquiriente['tipoPer'],
			'razons' => trim($this->aAdquiriente['apellido'].' '.$this->aAdquiriente['nombre']),
			'numeId' => $this->aAdquiriente['numeId'],
			'direccion' => $laDireccion,
			'respfiscal' => $this->aAdquiriente['respfiscal'],
			'tributo' => $this->aAdquiriente['tributo'],
			'contacto' => '', 'telefono' => '', 'correo' => '',
		];
		//$laFactura['Emisor'] = $this->nodoCliente($laData, false);
		$laFactura['Emisor'] = $laData;


		/********************* NASRelacionadas *********************/
//		$laFactura['NASRelacionadas'] = [
//			'NASRelacionada' => [
//				'Numero' => '',
//				'CUDS' => '',
//				'FechaEmision' => '',
//			],
//		];


		/********************* Cliente *********************/
		$laFactura['Cliente'] = $this->oEmisor;


		/********************* MediosDePago *********************/
		$laData = $this->aCnf['tipoDoc'][$this->cTipDocXml]['medioPago'];
		if ($laData['forma']=='2') {
			$laData['fechv'] = $lcFechaVence;
		}
		$laFactura['MediosDePago'] = $laData;


		/********************* DescuentosOCargos *********************/
		$lnDescuentos = $lnCargos = 0;
		if (count($this->aDescCargos)>0) {
			// Obtener base y calcular porcentaje
			$laDescCargos = [];
			foreach ($this->aDescCargos as $laDescCargo) {
				if ($laDescCargo['Indicador']=='true') {
					$lnCargos += $laDescCargo['Valor'];
				} else {
					$lnDescuentos += $laDescCargo['Valor'];
				}
				$laDescCargos[] = [
					'ID' => '1', // ¿Todos en 1?
					'Indicador' => $laDescCargo['Indicador'],
					'CodigoDescuento' => '',
					'Justificacion' => $laDescCargo['Justificacion'],
					'Valor' => number_format($laDescCargo['Valor'],2,'.',''),
					'Base' => number_format($lnBruto,2,'.',''),
					'Porcentaje' => $lnBruto==0 ? '' : number_format((100*$laDescCargo['Valor']/$lnBruto),4,'.',''),
				];
			}
			$laFactura['DescuentosOCargos'] = $laDescCargos;
		}


		/********************* Retenciones *********************/
		$lcTipoRegRete = $this->aCnf['tipoDoc']['DS']['retenciones'];
		$lbHayRetenciones = false;
		if ($lcTipoRegRete!=='NO') {
			$laData = [];
			$laRetenciones = [
				'ReteIva'=>[0,0,'05','ReteIVA'],
				'ReteFte'=>[0,0,'06','ReteRenta'],
				'ReteIca'=>[0,0,'07','ReteICA'],
			];
			foreach($laRetenciones as $lcReteKey=>$laReteConfig){
				foreach($this->aFactura[$lcReteKey] as $laReteData){
					$laData[] = [
						'tipo' => $laReteConfig[2],
						'nombre' => $laReteConfig[3],
						'valor' => $laReteData['valor'],
						'vrBase' => $laReteData['base'],
						'porcen' => $laReteData['porcentaje'],
					];
					$laRetenciones[$lcReteKey][0] += $laReteData['valor'];
					$laRetenciones[$lcReteKey][1] += $laReteData['porcentaje'];
				}
			}
			if (count($laData)>0) {
				$lbHayRetenciones = true;
				if (in_array($lcTipoRegRete, ['TOTAL','TODO'])) {
					// $laFactura['Retenciones'] = $this->nodoRetenciones($laData);
					$laFactura['Retenciones'] = $laData;
				}
			}
		}


		/********************* Totales *********************/
		$laData = [
			'bruto' => number_format($lnBruto,2,'.',''),
			'basei' => number_format($lnBruto,2,'.',''),
			'impst' => '0',
			'brimp' => number_format($lnBruto,2,'.',''),
			'descu' => $lnDescuentos==0 ? 0 : number_format($lnDescuentos,2,'.',''),
			'cargo' => $lnCargos==0 ? 0 : number_format($lnCargos,2,'.',''),
			'genrl' => number_format($lnTotalPagar,2,'.',''),
			'abono' => '0',
		];
		if ($lbHayRetenciones && $lcTipoRegRete!=='NO') {
			$laData += [
				'rtiva' => $laRetenciones['ReteIva'][0]==0 ? '0' : number_format($laRetenciones['ReteIva'][0],2,'.',''),
				'rtfte' => $laRetenciones['ReteFte'][0]==0 ? '0' : number_format($laRetenciones['ReteFte'][0],2,'.',''),
				'rtica' => $laRetenciones['ReteIca'][0]==0 ? '0' : number_format($laRetenciones['ReteIca'][0],2,'.',''),
			];
		}
		$laFactura['Totales'] = $laData;


//	*********************************************************************
//	Linea/Detalle: Obligatorios adicionales
//		- FechaCompra
//		- CodigoFormaGeneracionTransmision
//		- DescripcionFormaGeneracionTransmision
//			1	Por operación
//			2	Acumulado semanal
//		- ValorTotalItem = Cantidad x PrecioUnitario
//	*********************************************************************
//	En correo del 2022-04-07 16:37 indican que siempre es 1
//	*********************************************************************
		$lnFormaGeneraTrans = '1';

		/********************* Linea *********************/
		$laFactura['Lineas'] = [];
		$lnCns = 0;
		foreach ($this->aDetalles as $laDet) {
			$lnCns++;

			$lcVrTotal = number_format($laDet['Valor'],2,'.','');
			$lcCantidad = 1;
			$lcVrUnidad = number_format($laDet['Valor'] / $lcCantidad,2,'.','');

			$laData = [
				'cons' => $lnCns,
				'codg' => $laDet['Codigo'],
				'desc' => empty($laDet['Detalle']) ? $laDet['Descrip'] : $laDet['Detalle'],
				'cant' => $lcCantidad,
				'undm' => $this->aCnf['um'],
				'vrud' => $lcVrUnidad,
				'vrtt' => $lcVrTotal,
				'fecc' => $lcFechaEmision,
				'gent' => $lnFormaGeneraTrans,
			];

			if ($lbHayRetenciones && in_array($lcTipoRegRete, ['PORLINEA','TODO'])) {
				$laData['rete'] = [];
				foreach($laRetenciones as $lcReteKey=>$laReteConfig){
					if ($this->bCalcularPorLinea[$lcReteKey]) { // Problema los porcentajes
						$laData['rete'][] = [
							'valor' => round($laReteConfig[0] * $laDet['Valor'] / $lnBruto),
							'tipo' => $laReteConfig[2],
							'nombre' => $laReteConfig[3],
							'vrBase' => round($lnBaseImponible * $laDet['Valor'] / $lnBruto),
							// 'vrBase' => $lcVrTotal,
							'porcen' => $laReteConfig[1],
						];
					} else {
						if (isset($laDet[$lcReteKey])) {
							$laData['rete'][] = [
								'valor' => $laDet[$lcReteKey]['valor'],
								'tipo' => $laReteConfig[2],
								'nombre' => $laReteConfig[3],
								'vrBase' => $laDet[$lcReteKey]['base'],
								'porcen' => $laDet[$lcReteKey]['porcentaje'],
							];
						}
					}
				}
			}

			$laFactura['Lineas'][$lnCns] = $laData;
		}


		/********************* Extensiones *********************/
		$laFactura['Extensiones'] = [
			'valorletras' => $lcVrLetras,
			'elaboradopor' => $this->aFactura['Usuario'],
			'elaboronombre' => $this->aFactura['Elaboro'],
		];

		return $laFactura;
	}


	/* Retorna array aError */
	public function aError()
	{
		return $this->aError;
	}

}