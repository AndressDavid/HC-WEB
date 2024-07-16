<?php
/* ********************  FACTURA VARIOS  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FeConsultar.php';


class FeConsultar25FA extends FeConsultar
{
	protected $bCalcularIvaPorLinea = false;


	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig)
	{
		parent::__construct($taConfig);

		$this->cTipDocXml = 'FA';
		$this->cTipoFac = '25FA';
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
		$lcTipoCbt = '25';	// Tipo de documento contable
		$lcDocFac = substr($tcNum, -6);

		// Consultar fecha de Factura
		$laFemov = $this->oDb
			->select('FECC,USRC')
			->from('FEMOV')
			->where([ 'FACT'=>$tcNum, 'NOTA'=>0, 'TIPR'=>'25FA' ])
			->getAll('array');

		// Validar que exista la factura en FEMOV
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró la factura tipo {$this->cTipoFac} número {$tcNum} en FEMOV.",
			];
			return;
		}

		// Consulta movimiento de Factura
		$lnFechaFac = strval($laFemov['0']['FECC']);
		$lnAnio = substr($lnFechaFac, 0, 4);
		$lnMes =  substr($lnFechaFac, 4, 2);

 		$laMovsFac = $this->oDb
			->select('MOVSUC, MOVAÑO AS MOVANI, MOVMES, MOVDIA, MOVCBT, MOVPAQ, MOVDOC, MOVSEC, MOVCTA, MOVAUX, MOVVRL, MOVCMP, MOVFCH, MOVHOR, MOVLOC, MOVCAN, MOVUSR')
			->select('(SELECT PLNDSC FROM CTBPLN WHERE MOVCTA=PLNCTA ORDER BY PLNAÑO DESC FETCH FIRST 1 ROWS ONLY) AS DSCCTA')
			->select('(SELECT IFNULL(USRNBR,\'\') FROM SEGUSR WHERE USRUSR=MOVUSR FETCH FIRST 1 ROWS ONLY) AS ELABORO')
			->from('CTBMOV')
			->where([ 'MOVSUC'=>$lcSucursal, 'MOVAÑO'=>$lnAnio, 'MOVMES'=>$lnMes, 'MOVCBT'=>$lcTipoCbt, 'MOVDOC'=>$lcDocFac ])
			->getAll('array');

		// Validar que exista la factura
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró la factura tipo {$this->cTipoFac} número {$tcNum}.",
			];
			return;
		}


		$laImpuestos = [];
		$this->aFactura = [
			'IvaValor' => 0,
			'IvaBase' => 0,
			'IvaPocentaje' => 0,
			'Redondeo' => 0,
		];

		foreach ($laMovsFac as $lnKey => $laMov) {
			$laMov = array_map('trim', $laMov);
			$lcTipoCta = substr($laMov['MOVCTA'],0,2);

			switch ($lcTipoCta) {

				/********** Cabecera de factura **********/
				case '13':

					$this->aFactura += [
						'Numero' => $tcNum,
						'DocContab' => $lcDocFac,
						'Fecha' => $laMov['MOVFCH'],
						'Hora' => $laMov['MOVHOR'].'00',
						'ValorTotal' => abs($laMov['MOVVRL']),
						'DocCliente' => substr($laMov['MOVAUX'],0,13),
						'Moneda' => $laMov['MOVLOC'],
						'Usuario' => $laMov['MOVUSR'],
						'Elaboro' => $laMov['ELABORO'],
					];

					// Consulta descripción (una sola descripción por factura, pueden ser varias cuentas)
					$laDes = $this->oDb
						->select('DETDET')
						->from('CTBDET')
						->where([
							'DETSUC'=>$laMov['MOVSUC'],
							'DETAÑO'=>$laMov['MOVANI'],
							'DETMES'=>$laMov['MOVMES'],
							'DETDIA'=>$laMov['MOVDIA'],
							'DETCBT'=>$laMov['MOVCBT'],
							'DETPAQ'=>$laMov['MOVPAQ'],
							'DETDOC'=>$laMov['MOVDOC'],
						])
						->where("DETSEC=(SELECT MIN(DETSEC) CNSDET FROM CTBDET WHERE DETSUC='{$laMov['MOVSUC']}' AND DETAÑO='{$laMov['MOVANI']}' AND DETMES='{$laMov['MOVMES']}' AND DETDIA='{$laMov['MOVDIA']}' AND DETCBT='{$laMov['MOVCBT']}' AND DETPAQ='{$laMov['MOVPAQ']}' AND DETDOC='{$laMov['MOVDOC']}')")
						->getAll('array');
					$this->aFactura['Descripcion'] = '';
					if ($this->oDb->numRows()>0) {
						foreach ($laDes as $laDetalle) {
							$this->aFactura['Descripcion'] .= $laDetalle['DETDET'];
						}
					}
					$this->aFactura['Descripcion'] = trim($this->aFactura['Descripcion']);
					unset($laDes);

					// Datos del adquiriente
					$laPrmTer = $this->oDb
						->select('TE1TIP, TE1COD, TE1SOC, TE1PER')
						->select('IFNULL(TE2COM, \'\') TE2COM, IFNULL(TE2LEG, \'\') TE2LEG, IFNULL(TE2DIR, \'\') TE2DIR, IFNULL(TE2CIU, \'\') TE2CIU')
						->select('IFNULL(TE2TEL, \'\') TE2TEL, IFNULL(TE2MAI, \'\') TE2MAI, IFNULL(TE4IVA, \'\') TE4IVA, IFNULL(TABDSC, \'\') CIUDAD')
						->select('IFNULL(SUBSTR(DESPAI, 1, LOCATE(\' - \', DESPAI)), \'\') DESPAI, IFNULL(DESDEP, \'\') DESDEP, IFNULL(DESCIU, \'\') CIUDAD2')
						->from('PRMTE1')
						->leftJoin('PRMTE2','TE1COD=TE2COD')
						->leftJoin('PRMTE4','TE1COD=TE4COD')
						->leftJoin('PRMTAB','TABTIP=\'010\' AND TABCOD=TE2CIU')
						->leftJoin('COMPAI','SUBSTR(TE2CIU,1,2)=SUBSTR(DESPAI, LOCATE(\' - \', DESPAI) + 3, 2)')
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
							'tipoPer' => $laPrmTer['TE1PER']=='J' ? $this->aCnf['codTipoPer']['juridica'] : $this->aCnf['codTipoPer']['natural'],
							'direccion' => $laPrmTer['TE2DIR']=='' ? 'NO EXISTE INFORMACIÓN' : $laPrmTer['TE2DIR'],
							'telefono' => $laPrmTer['TE2TEL'],
							'correo' => $laPrmTer['TE2MAI'],
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
						$this->aAdquiriente['coddepto'] = str_pad($lcCodDepS,2,'0',STR_PAD_LEFT);
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
						if (isset($this->aCnf['cliente']['formaPagoDoc']['25FA'])) {
							$this->aAdquiriente['FormaDePago'] = $this->aCnf['cliente']['formaPagoDoc']['25FA'];
						} else {
							$this->aAdquiriente['FormaDePago'] = $this->aCnf['cliente']['formaPago'][$this->aAdquiriente['tipoPer']];
						}
						$this->aAdquiriente['MedioDePago'] = $this->aCnf['cliente']['medioPago'][$this->aAdquiriente['tipoPer']];
					}

					break;


				/********** IVA **********/
				case '24':
					$lnImpuestoValor = abs($laMov['MOVVRL']);
					$lnImpuestoBase = abs($laMov['MOVCAN']);
					$lnImpuestoPorc = floatval(substr($laMov['MOVCMP'],0,5));
					$lnRedondeo = $lnImpuestoValor - $lnImpuestoBase*$lnImpuestoPorc/100;

					$this->aFactura['Cod_Ciudad'] = substr($laMov['MOVAUX'], 0, 5);
					$this->aFactura['IvaValor'] += $lnImpuestoValor;
					$this->aFactura['IvaBase'] += $lnImpuestoBase;
					$this->aFactura['IvaPocentaje'] = $lnImpuestoPorc;
					$this->aFactura['Redondeo'] += $lnRedondeo;
					$laImpuestos[] = [
						'IvaValor' => $lnImpuestoValor,
						'IvaBase' => $lnImpuestoBase,
						'IvaPocentaje' => $lnImpuestoPorc ,
						'Redondeo' => $lnRedondeo,
						'detalle' => -1,
					];
					break;


				/********** Detalles de factura **********/
				case '41':
				case '42':
					$this->aDetalles[] = [
						'Codigo' => $laMov['MOVCTA'],
						'Detalle' => $laMov['DSCCTA'],
						'Valor' => abs($laMov['MOVVRL']),
					];
					break;
			}
		}

		if (count($laImpuestos)>0) {
			// Si hay impuestos, recorre para poner los datos en el detalle, se supone que todos los detalles tienen IVA
			$lbTodos = true;
			foreach ($this->aDetalles as $lnKey=>$laDetalle) {
				$lbEncontrado = false;
				foreach ($laImpuestos as $lnKeyI=>$laImpuesto) {
					if ($laImpuesto['detalle']==-1 && $laImpuesto['IvaBase']==$laDetalle['Valor']) {
						$this->aDetalles[$lnKey]+=$laImpuesto;
						$laImpuestos[$lnKeyI]['detalle'] = $lnKey;
						$lbEncontrado = true;
						break;
					}
				}
				$lbTodos = $lbTodos && $lbEncontrado;
			}
			// si no encontró todos los detalles debe calcular el IVA por línea
			$this->bCalcularIvaPorLinea = !$lbTodos;
		}
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayFac()
	{
		$laFactura = [];

		$lcFechaEmision = FeFunciones::formatFecha($this->aFactura['Fecha']);
		$lcHoraEmision = FeFunciones::formatHora($this->aFactura['Hora']);

		$lbPrueba = $this->aCnf['ambiente']=='Pruebas';
		if ($lbPrueba) {
			$this->aFactura['Numero'] = ''.($this->aFactura['Numero']+$this->aCnf['facture']['Pruebas']['sumar']).'';

			if ($this->aFactura['Fecha']<intval(date('Ymd'))) {
				$lcFechaEmision = date('Y-m-d');
			}
		}

		$lnNumDiasVence=$this->aCnf['plazoVence'];
		$lcFechaVence = date('Y-m-d', strtotime($lcFechaEmision."+ $lnNumDiasVence days"));

		$lbEsPersonaJurídica = $this->aAdquiriente['tipoPer'] == $this->aCnf['codTipoPer']['juridica'];

		$lnImpuestos = $this->aFactura['IvaValor'] ?? 0;
		$lbHayImpuestos = $lnImpuestos>0;

		// Contingencia
		$lbNoEsContingencia = $this->cTipDocXml=='FA';
		$lcFormatoContingencia = 'Papel'; // Talonario, Papel
		$lcMoneda = $this->aCnf['divisa']; // Moneda utilizada para facturar

		// Totales
		$lnTotalPagar = $this->aFactura['ValorTotal'];
		$lnBruto = $lnTotalPagar - $lnImpuestos;
		$lnBaseImponible = $this->aFactura['IvaBase'] ?? 0;

		// Valor factura en letras
		$lcVrLetras = NumerosALetras::convertir($lnTotalPagar,['PESO','PESOS'],['CENTAVO','CENTAVOS']) . $this->aCnf['txtDespuesValorLetras'];



		/********************* Cabecera *********************/
		$laFactura['Documento'] = [
			'Prefijo' => $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'],
			'NumDoc' => $this->aFactura['Numero'],
		];
		$laData = [
			'Numero' => $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'].$this->aFactura['Numero'],
			'FechaEmision' => $lcFechaEmision,
			'HoraEmision' => $lcHoraEmision,
			'FormaDePago' => $this->aAdquiriente['FormaDePago'],
			'MonedaFactura' => $this->aCnf['divisa'],
			'Observaciones' => '',
			'TipoFactura' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['tipoFac'],
			'TipoOperacion' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['tipoOperacion'],
			'LineasDeFactura' => count($this->aDetalles),
		];
		if ($this->aAdquiriente['FormaDePago'] == $this->aCnf['codFormaPago']['credito']) $laData['Vencimiento'] = $lcFechaVence;
		if (!$lbNoEsContingencia) $laData['FormatoContingencia'] = $lcFormatoContingencia;
		$laFactura['Cabecera'] = $laData;


		/********************* NumeracionDIAN *********************/
		$laFactura['NumeracionDIAN'] = [$this->aFactura['Numero'], 'RESOLFAC'];


		/********************* Notificacion *********************/
		$lcMailNotifica = $lbPrueba ? $this->aCnf['mailPrueba'] : mb_strtolower($this->aAdquiriente['correo']);
		if (strlen($lcMailNotifica)>0) {
			$laFactura['Notificacion'] = $lcMailNotifica;
		}


		/********************* Cliente *********************/
		$laFactura['Cliente'] = [
			'razons' => trim($this->aAdquiriente['apellido'].' '.$this->aAdquiriente['nombre']),
			'nombre' => $this->aAdquiriente['nombre'],
			'apellido' => $this->aAdquiriente['apellido'],
			'tipoId' => $this->aCnf['tiposId'][$this->aAdquiriente['tipoId']],
			'numeId' => $this->aAdquiriente['numeId'],
			'tipoPer' => $this->aAdquiriente['tipoPer'],
			'direccion' => [
				'Direccion' => $this->aAdquiriente['direccion'],
				'CodigoPais' => $this->aAdquiriente['codpais'],
				'NombrePais' => $this->aAdquiriente['pais'],
				'IdiomaPais' => 'es',
				'CodigoDepartamento' => $this->aAdquiriente['coddepto'],
				'NombreDepartamento' => $this->aAdquiriente['depto'],
				'CodigoMunicipio' => $this->aAdquiriente['codciudad'],
				'NombreCiudad' => $this->aAdquiriente['ciudad'],
			],
			'respfiscal' => $this->aAdquiriente['respfiscal'],
			'tributo' => $this->aAdquiriente['tributo'],
			'contacto' => $this->aAdquiriente['contacto'],
			'telefono' => $this->aAdquiriente['telefono'],
			'correo' => $this->aAdquiriente['correo'],
		];


		/********************* MediosDePago *********************/
		$laFactura['MediosDePago'] = [
			'medio' => $this->aAdquiriente['MedioDePago'],
			'forma' => $this->aAdquiriente['FormaDePago'],
			'fechv' => $lcFechaVence,
		];


		/********************* Impuestos *********************/
		if ($lbHayImpuestos) {
			$laFactura['Impuestos'] = [
				// IVA
				[
					//'valor' => $lnImpuestos-$this->aFactura['Redondeo'],
					'valor' => $lnImpuestos,
					'tipo' => '01',
					'nombre' => 'IVA',
					'vrBase' => $this->aFactura['IvaBase'],
					'porcen' => $this->aFactura['IvaPocentaje'],
					'redond' => 0,
					'codUM' => $this->aCnf['um'],
				],
			];
		}


		/********************* Totales *********************/
		$laFactura['Totales'] = [
			'bruto' => number_format($lnBruto,2,'.',''),
			'basei' => number_format($lnBaseImponible,2,'.',''),
			'impst' => $lbHayImpuestos ? number_format($lnImpuestos,2,'.','') : '0',
			'brimp' => number_format($lnTotalPagar,2,'.',''),
			'descu' => '0',
			'genrl' => number_format($lnTotalPagar,2,'.',''),
			'abono' => '0',
		];


		/********************* Linea *********************/
		$laFactura['Lineas'] = [];
		$lnCns = 0;
		foreach ($this->aDetalles as $laDet) { // SE SUPONE QUE SOLO HAY UNA LINEA, NO DEBERÍA SER NECESARIO
			$lnCns++;
			if ($this->aCnf['parFac']['detalleMasCuenta']) {
				$lcDetalle = ( ($lnCns==1 && $this->aFactura['Descripcion']!='') ? ($this->aFactura['Descripcion'] . $this->cSLD) : '' ) . $laDet['Detalle'];
			} else {
				$lcDetalle = $this->aFactura['Descripcion']!='' ? $this->aFactura['Descripcion'] : $laDet['Detalle'];
			}

			$lcVrTotal = number_format($laDet['Valor'],2,'.','');
			$lcCantidad = 1;
			$lcVrUnidad = number_format($laDet['Valor'] / $lcCantidad,2,'.','');

			$laData = [
				'cons' => $lnCns,
				'codg' => $laDet['Codigo'],
				'desc' => $lcDetalle,
				'cant' => $lcCantidad,
				'undm' => $this->aCnf['um'],
				'vrud' => $lcVrUnidad,
				'vrtt' => $lcVrTotal,
				'cadd' => [
					'unidad' => $this->aCnf['umdsc'],
				],
			];
			if ($lbHayImpuestos) {
				if ($this->bCalcularIvaPorLinea) {
					$laData['impu'] = [
						[
							//'valor' => round($lnImpuestos * $laDet['Valor'] / $lnBruto)-$this->aFactura['Redondeo'],
							'valor' => round($lnImpuestos * $laDet['Valor'] / $lnBruto),
							'tipo' => '01',
							'nombre' => 'IVA',
							'vrBase' => round($lnBaseImponible * $laDet['Valor'] / $lnBruto),
							'vrBase' => $lcVrTotal,
							'porcen' => $this->aFactura['IvaPocentaje'],
							'redond' => 0,
							'codUM' => $this->aCnf['um'],
						],
					];
				} else {
					if (isset($laDet['IvaValor'])) {
						$laData['impu'] = [
							[
								//'valor' => $laDet['IvaValor']-$laDet['Redondeo'],
								'valor' => $laDet['IvaValor'],
								'tipo' => '01',
								'nombre' => 'IVA',
								'vrBase' => $laDet['IvaBase'],
								'porcen' => $laDet['IvaPocentaje'],
								'redond' => 0,
								'codUM' => $this->aCnf['um'],
							],
						];
					}
				}
			}
			$laFactura['Lineas'][$lnCns] = $laData;
		}


		/********************* Extensiones *********************/
		$laFactura['Extensiones'] = [
			// Paciente
				'historiaclinica' => '',
				'documentointerno' => '',
				'paciente' => '',
				'tipodocpaciente' => '',
				'idpaciente' => '',
				'telefonopaciente' => '',
				'direccionpaciente' => '',
				'tipoafiliado' => '',
				'plan' => '',
				'poliza' => '',
				'autorizacion' => '',
				'fechahoraingreso' => '',
				'fechahoraegreso' => '',
			// Factura
				'valorletras' => $lcVrLetras,
				'elaboradopor' => $this->aFactura['Usuario'],
				'elaboronombre' => $this->aFactura['Elaboro'],
				'plazo' => $lnNumDiasVence . ' días',
		];


		/********************* InformacionAdicional *********************/
		$laFactura['InformacionAdicional'] = [
			// 'fechaInicioFacturacion'	=> ' ',
			// 'fechaFinFacturacion'		=> ' ',
			'CodigoFacturador'			=> $this->aFactura['Usuario'],
			// 'TipoDocumentoFacturador'	=> ' ',
			// 'NumeroDocumentoFacturador'	=> ' ',
			'NombreFacturador'			=> $this->aFactura['Elaboro'],
			// 'ApellidoFacturador'		=> ' ',
		];


		return $laFactura;
	}


	/* Retorna array aError */
	public function aError()
	{
		return $this->aError;
	}
}