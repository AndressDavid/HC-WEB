<?php
/* ********************  GENERACIÓN DE JSON DE FE Y DS PARA TRANSFIRIENDO  ******************** */
namespace NUCLEO;


class FeGenerarJSONTransfiriendo
{
	private $aTipos = [
		'FA'=>1,
		'NC'=>2,
		'ND'=>3,
		'DS'=>5,
		'NS'=>6
	];
	private $aCharEliminarTr = [];
	public $aDoc = [];


	public function __construct($taConfig)
	{
		$this->aConfig = $taConfig;

		// caracteres a eliminar para JSON de Transfiriendo
		$this->aCharEliminarTr = [
			'buscar' => ["\u001A", "\u001a", "\u0002", chr(2), chr(9), chr(13).chr(10), chr(10), chr(13)],
			'cambio' => ['', '', '\n', '', '   ', '\n', '\n', '\n']
		];
	}


	/*
	 *	Genera JSON de documentos de Facturación Electrónica
	 *	@param array $taDatos arreglo con los datos principales del documento
	 *	@param array $taDocFE arreglo con la información del documento
	 */
	public function generarFE($taDatos, $taDocFE)
	{
		$this->aDoc = [
			'evento' => $taDocFE['Evento'] ?? 'FAC',
			'datos' => [
				'documento' => [
					'NITFacturador' => $this->aConfig['emisor']['NumeroIdentificacion'],
					'prefijo' => empty($taDocFE['Documento']['Prefijo']) ? ' ' : $taDocFE['Documento']['Prefijo'],
					'numeroDocumento' => $taDocFE['Documento']['NumDoc'], // $taDocFE['Cabecera']['Numero'],
					'tipoDocumento' => $this->aTipos[$taDatos['TIPDOCXML']],
					'subTipoDocumento' => $taDocFE['Cabecera']['TipoFactura'] ?? $this->aConfig['tipoDoc'][$taDatos['TIPDOCXML']]['tipoFac'],
					'tipoOperacion' => $taDocFE['Cabecera']['TipoOperacion'],
					'plantilla' => in_array($taDatos['TIPR'], ['25FA','25NC','25ND']) ? '1' : '0',
					'generaRepresentacionGrafica' => true,
					'fechaEmision' => $taDocFE['Cabecera']['FechaEmision'],
					'horaEmision' => $taDocFE['Cabecera']['HoraEmision'].'-05:00',
					'fechaVencimiento' => $taDocFE['Cabecera']['Vencimiento'] ?? ' ',
					'fechaInicioPeriodo' => '',
					'horaInicioPeriodo' => '00:00:01-05:00',
					'fechaFinPeriodo' => '',
					'horaFinPeriodo' => '23:59:59-05:00',
					'formaPago' => [
						'tipoPago' => $taDocFE['MediosDePago']['forma'] ?? ' ',
						'codigoMedio' => $taDocFE['MediosDePago']['medio'] ?? ' ',
						'fechaVencimiento' => $taDocFE['MediosDePago']['fechv'] ?? ' ',
					],
					'notificaciones' => [],
					//'documentosAfectados' => [],
					'moneda' => $this->aConfig['divisa'],
					'notas' => [$taDocFE['Cabecera']['Observaciones']],
					'informacionesAdicionales' => [],
				],
				'adquiriente' => [],
				'anticipos' => [],
				'cargosDescuentos' => [],
				'tributos' => [],
				'totales' => [
					'valorBruto' => floatval($taDocFE['Totales']['bruto']),
					'valorCargos' => floatval($taDocFE['Totales']['cargos'] ?? '0'),
					'valorDescuentos' => floatval($taDocFE['Totales']['descu'] ?? '0'),
					'valorAnticipos' => floatval($taDocFE['Totales']['abono'] ?? '0'),
					'valorTotalSinImpuestos' => floatval($taDocFE['Totales']['brimp'] - ($taDocFE['Totales']['impst'] ?? 0)),
					'valorTotalConImpuestos' => floatval($taDocFE['Totales']['brimp'] ?? '0'),
					'valorNeto' => floatval($taDocFE['Totales']['genrl']),
				],
				'detalles' => [],
			],
		];
		// Oscar Vega - no enviar prefijo cuando está vacío
		// Rusbel Oviedo - Enviar por cambio de servidor - 2023-03-08
		// Rusbel Oviedo - No enviar por problemas en DIAN - 2023-03-09
		if ($this->aDoc['datos']['documento']['prefijo']==' ') {
			unset($this->aDoc['datos']['documento']['prefijo']);
		}
		if ($taDocFE['MediosDePago']['forma']!==$this->aConfig['codFormaPago']['credito']) {
			unset($this->aDoc['datos']['documento']['fechaVencimiento'], $this->aDoc['datos']['documento']['formaPago']['fechaVencimiento']);
		}
		if (isset($taDocFE['InformacionAdicional']['fechaInicioFacturacion']) && isset($taDocFE['InformacionAdicional']['fechaFinFacturacion'])) {
			$this->aDoc['datos']['documento']['fechaInicioPeriodo'] = $taDocFE['InformacionAdicional']['fechaInicioFacturacion'];
			$this->aDoc['datos']['documento']['fechaFinPeriodo'] = $taDocFE['InformacionAdicional']['fechaFinFacturacion'];
		} elseif (isset($taDocFE['fechaInicioFacturacion']) && isset($taDocFE['fechaFinFacturacion'])) {
			$this->aDoc['datos']['documento']['fechaInicioPeriodo'] = $taDocFE['fechaInicioFacturacion'];
			$this->aDoc['datos']['documento']['fechaFinPeriodo'] = $taDocFE['fechaFinFacturacion'];
		} else {
			unset(
				$this->aDoc['datos']['documento']['fechaInicioPeriodo'],
				$this->aDoc['datos']['documento']['horaInicioPeriodo'],
				$this->aDoc['datos']['documento']['fechaFinPeriodo'],
				$this->aDoc['datos']['documento']['horaFinPeriodo']
			);
		}

		// Para Consumidor final se envía ubicación predeterminada
		if ($taDocFE['Cliente']['numeId']=='222222222222') {
			$this->aDoc['datos']['adquiriente'] = [
				'tipoIdentificacion' => intval($taDocFE['Cliente']['tipoId']),	//	13,
				'identificacion' => $taDocFE['Cliente']['numeId'],				//	'222222222222',
				'razonSocial' => $taDocFE['Cliente']['razons'],					//	'Consumidor final'
				'nombreSucursal' => ' ',
				'tipoPersona' => strval($taDocFE['Cliente']['tipoPer']),		//	2,
				'responsabilidadesRUT' => $taDocFE['Cliente']['respfiscal'],	//	['R-99-PN'],
				'tributos' => array_keys($taDocFE['Cliente']['tributo']),		//	['ZZ']
			];

		} else {
			$lcCodPaisAdq = $taDocFE['Cliente']['direccion']['CodigoPais'] ?? '';
			$this->aDoc['datos']['adquiriente'] = [
				'tipoIdentificacion' => intval($taDocFE['Cliente']['tipoId']),
				'identificacion' => $taDocFE['Cliente']['numeId'],
				'razonSocial' => $taDocFE['Cliente']['razons'],
				'nombreSucursal' => $taDocFE['Cliente']['razons'],
				'correo' => $taDocFE['Cliente']['correo'],
				'telefono' => trim(substr($taDocFE['Cliente']['telefono'],0,10)),
				'tipoPersona' => strval($taDocFE['Cliente']['tipoPer']),
				'responsabilidadesRUT' => $taDocFE['Cliente']['respfiscal'],
				'tributos' => array_keys($taDocFE['Cliente']['tributo']),
				'ubicacion' => [
					'pais' => $lcCodPaisAdq,
					'codigoMunicipio' => $lcCodPaisAdq=='CO' ? ($taDocFE['Cliente']['direccion']['CodigoMunicipio'] ?? '') : '00000',
					'direccion' => $taDocFE['Cliente']['direccion']['Direccion']??'N/A',
				],
			];

			if (empty($this->aDoc['datos']['adquiriente']['correo'])) {
				unset($this->aDoc['datos']['adquiriente']['correo']);
			}
			if (empty($this->aDoc['datos']['adquiriente']['telefono'])) {
				unset($this->aDoc['datos']['adquiriente']['telefono']);
			}
		}

		if (!empty($taDocFE['Notificacion'])) {
			$this->aDoc['datos']['documento']['notificaciones'][] = [
				'tipo'	=> 1,
				'valor'	=> [$taDocFE['Notificacion']]
			];
		}
		if (count($this->aDoc['datos']['documento']['notificaciones'])==0) {
			unset($this->aDoc['datos']['documento']['notificaciones']);
		}

		if (empty($taDocFE['Cabecera']['Observaciones'])) {
			unset($this->aDoc['datos']['documento']['notas']);
		}

		if (isset($taDocFE['ReferenciasTransacciones'])) {
			if (isset($taDocFE['FacturasRelacionadas'])) {
				foreach ($taDocFE['FacturasRelacionadas'] as $laFacturaRel) {
					$this->aDoc['datos']['documento']['documentosReferencia'][] = [
						'tipo' => 1, // referencia a otros documentos
						'id' => $laFacturaRel['Numero'],
						// 'tipoDocumento' => $laFacturaRel['TipoDocumento'], // Comentado por correo de ovega del 2022-11-09 a las 08:21
						'nombreDocumento' => $laFacturaRel['Descripcion'],
						'fecha' => $laFacturaRel['FechaEmision'],
						'UUID' => ' ', // $laFacturaRel['CUFE'],
					];
				}
			}
		} else {
			if (isset($taDocFE['FacturasRelacionadas'])) {
				foreach ($taDocFE['FacturasRelacionadas'] as $laFacturaRel) {
					$this->aDoc['datos']['documento']['documentosAfectados'][] = [
						'UUID' => strlen($laFacturaRel['cufe'])>0 ? $laFacturaRel['cufe'] : ' ',
						'numeroDocumento' => $laFacturaRel['fact'],
						'codigoCausal' => intval($laFacturaRel['cncp']),
						'fecha' => $laFacturaRel['fech'],
						'observaciones' => ['  '],
					];
				}
			}
		}

		if (isset($taDocFE['Anticipos'])) {
			$this->aDoc['datos']['anticipos'][] = [
				'comprobante' => ' ',
				'valorAnticipo' => floatval($taDocFE['Anticipos']['valor']),
				'fechaPago' => $taDocFE['Anticipos']['fechar'],
			];
		} else {
			unset($this->aDoc['datos']['anticipos']);
		}
		if (isset($taDocFE['DescuentosOCargos'])) {
			$this->aDoc['datos']['cargosDescuentos'][] = [
				'esCargo' => false,
				'codigo' => '01',
				'valorBase' => floatval($taDocFE['DescuentosOCargos']['vrbase'] ?? '0'),
				'valorImporte' => floatval($taDocFE['DescuentosOCargos']['valor'] ?? '0'),
				'porcentaje' => floatval($taDocFE['DescuentosOCargos']['porcen'] ?? '0'),
			];
		} else {
			unset($this->aDoc['datos']['cargosDescuentos']);
		}

		if (isset($taDocFE['Impuestos'])) {
			foreach ($taDocFE['Impuestos'] as $laImpuesto) {
				$this->aDoc['datos']['tributos'][] = [
					'id' => $laImpuesto['tipo'],
					'nombre' => $laImpuesto['nombre'],
					'esImpuesto' => true,
					'valorImporteTotal' => floatval($laImpuesto['valor']),
					'detalles' => [[
						'valorBase' => floatval($laImpuesto['vrBase']),
						'valorImporte' => floatval($laImpuesto['valor']),
						'porcentaje' => floatval($laImpuesto['porcen']),
					]],
				];
			}
		} else {
			unset($this->aDoc['datos']['tributos']);
		}

		// Información adicional
		$laInfoAdicional = $this->infoAdicionalTransfiriendo();
		foreach ($laInfoAdicional as $lcClave => $lcValor) {
			$lcDato = trim(strval($taDocFE['InformacionAdicional'][$lcClave] ?? $lcValor));
			if (mb_strlen($lcDato,'UTF-8')==0) {
				$lcDato = ' ';
			}
			$this->aDoc['datos']['documento']['informacionesAdicionales'][] = [
				'valor'  => $lcDato,
				'nombre' => $lcClave,
			];
		}
		unset($laInfoAdicional);

		// Sector Salud
		if (isset($taDocFE['SectorSalud']) && $this->aConfig['Salud']['enviarExtension']) {
			$this->aDoc['datos']['documento']['extensionesSalud'] = [[
				'codigoPrestador' => $taDocFE['SectorSalud']['01']['Valor'],
				'modalidadPago' => $taDocFE['SectorSalud']['09']['IdEsquema'],
				'cobertura' => $taDocFE['SectorSalud']['10']['IdEsquema'],
				'numeroContrato' => $taDocFE['SectorSalud']['14']['Valor'] ?? ' ',
				'numeroPoliza' => $taDocFE['SectorSalud']['15']['Valor'] ?? ' ',
			]];
			foreach ($this->aDoc['datos']['documento']['extensionesSalud'][0] as $lcClave => $lcValor) {
				if (is_string($lcValor) && empty($lcValor)) {
						$this->aDoc['datos']['documento']['extensionesSalud'][0][$lcClave] = ' ';
				}
			}
		}

		foreach ($taDocFE['Lineas'] as $laLinea) {
			$laDetalle = [
				'tipoDetalle' => 1,
				'valorCodigoInterno' => $laLinea['codg'],
				'codigoEstandar' => '999',
				'valorCodigoEstandar' => $laLinea['codg'],
				'descripcion' => $laLinea['desc0'] ?? $laLinea['desc'],
				'unidades' => floatval($laLinea['cant']),
				'unidadMedida' => $this->aConfig['um'],
				'valorUnitarioBruto' => floatval($laLinea['vrud']),
				'valorBruto' => floatval($laLinea['vrtt']),
			];
			if (isset($laLinea['impu'])) {
				foreach ($laLinea['impu'] as $laImpuesto) {
					$laDetalle['tributos'][] = [
						'id' => $laImpuesto['tipo'],
						'nombre' => $laImpuesto['nombre'],
						'esImpuesto' => true,
						'valorBase' => floatval($laImpuesto['vrBase']),
						'valorImporte' => floatval($laImpuesto['valor']),
						'porcentaje' => floatval($laImpuesto['porcen']),
					];
				}
			} else {
				$laDetalle['tributos'][] = [
					'id' => 'ZZ',
					'nombre' => 'OTROS TRIBUTOS',
					'esImpuesto' => false,
					'valorBase' => floatval($laLinea['vrtt']),
					'valorImporte' => 0,
					'porcentaje' => 0,
				];
			}
			if (isset($laLinea['cadd'])) {
				if (isset($laLinea['cadd']['conceptoglosa']) || isset($laLinea['cadd']['valorglosado'])) {
					$laDetalle['informacionesAdicionales'] = [];
					if (isset($laLinea['cadd']['conceptoglosa'])) {
						$laDetalle['informacionesAdicionales'][] = [
							'valor'  => $laLinea['cadd']['conceptoglosa'],
							'nombre' => 'ConceptoGlosa',
						];
					}
					if (isset($laLinea['cadd']['valorglosado'])) {
						$laDetalle['informacionesAdicionales'][] = [
							'valor'  => $laLinea['cadd']['valorglosado'],
							'nombre' => 'ValorGlosa',
						];
					}
				}
			}
			if (isset($laLinea['asubd'])) {
				$laDetalle['tipoDetalle'] = 3;
				$laDetalle['subDetalles'] = [];
				foreach ($laLinea['asubd'] as $laSubDet) {
					$laDetalle['subDetalles'][] = [
						'tipoDetalle' => 1,
						'valorCodigoInterno' => $laSubDet['codigo'],
						'codigoEstandar' => '999',
						'valorCodigoEstandar' => $laSubDet['codigo'],
						'descripcion' => empty($laSubDet['descrip']) ? ' ' : $laSubDet['descrip'],
						'unidades' => floatval($laSubDet['cantidad']),
						'unidadMedida' => $this->aConfig['um'],
						'valorUnitarioBruto' => floatval($laSubDet['valorund']),
						'valorBruto' => floatval($laSubDet['valortot']),
					];
				}
			}
			if (isset($laLinea['infoCM'])) {
				$laDetalle['informacionesAdicionales'] = $laLinea['infoCM'];
			}
			$this->aDoc['datos']['detalles'][] = $laDetalle;
		}

		return $this->limpiarChar(json_encode($this->aDoc, JSON_INVALID_UTF8_SUBSTITUTE));
	}

	/*
	 *	Genera JSON de Documentos de Soporte de Adquisiciones
	 *	@param array $taDatos arreglo con los datos principales del documento
	 *	@param array $taDocDS arreglo con la información del documento
	 */
	public function generarDS($taDatos, $taDocDS)
	{
		$this->aDoc = [
			'documento' => [
				'NITFacturador' => $taDocDS['Emisor']['numeId'],
				'prefijo' => $taDocDS['Documento']['Prefijo'],
				'numeroDocumento' => strval($taDocDS['Documento']['NumDoc']),
				'tipoDocumento' => $this->aTipos[$taDatos['TIPDOCXML']],
				'subTipoDocumento' => $taDocDS['Cabecera']['TipoFactura'] ?? $this->aConfig['tipoDoc'][$taDatos['TIPDOCXML']]['tipoFac'],
				'tipoOperacion' => $taDocDS['Cabecera']['TipoOperacion'],
				'fechaEmision' => $taDocDS['Cabecera']['FechaEmision'],
				'horaEmision' => $taDocDS['Cabecera']['HoraEmision'].'-05:00',
				'fechaVencimiento' => $taDocDS['Cabecera']['Vencimiento'] ?? ($taDocDS['MediosDePago']['fechv'] ?? ' '),
				'moneda' => $this->aConfig['divisa'],
				'notas' => [],
			//	'documentosReferencia' => [],
			//	'TRM' => [],
				'noObligadoFacturar' => [
					'tipoPersona' => strval($taDocDS['Emisor']['tipoPer']),
					'TipoIdentificacion' => intval($taDocDS['Emisor']['tipoId']),
					'RazonSocial' => $taDocDS['Emisor']['razons'],
					'responsabilidadesRUT' => $taDocDS['Emisor']['respfiscal'],
					'tributos' => array_keys($taDocDS['Emisor']['tributo']),
				],
				'ubicacion' => [
					'direccion' => $taDocDS['Emisor']['direccion']['Direccion'],
					'pais' => $taDocDS['Emisor']['direccion']['CodigoPais'],
				],
				'formaPago' => [
					'tipoPago' => intval($taDocDS['MediosDePago']['forma'] ?? '0'),
					'codigoMedio' => $taDocDS['MediosDePago']['medio'] ?? ' ',
					'fechaVencimiento' => $taDocDS['MediosDePago']['fechv'] ?? ' ',
				],
			],
			'adquiriente' => [
				'tipoIdentificacion' => 31,
				'identificacion' => '860006656',
			],
			'cargosDescuentos' => [],
			'tributos' => [],
			'totales' => [
				'valorBruto' => floatval($taDocDS['Totales']['bruto']),
				'valorCargos' => floatval($taDocDS['Totales']['cargos'] ?? '0'),
				'valorDescuentos' => floatval($taDocDS['Totales']['descu'] ?? '0'),
				'valorTotalSinImpuestos' => floatval($taDocDS['Totales']['brimp'] - ($taDocDS['Totales']['impst'] ?? 0)),
				'valorTotalConImpuestos' => floatval($taDocDS['Totales']['brimp'] ?? '0'),
				'valorNeto' => floatval($taDocDS['Totales']['genrl']),
			],
			'detalles' => [],
		];
		if (empty($taDocDS['Documento']['Prefijo'])) {
			unset($this->aDoc['documento']['prefijo']);
		}
		if (empty($taDocDS['Cabecera']['Observaciones'])) {
			unset($this->aDoc['documento']['notas']);
		} else {
			$this->aDoc['documento']['notas'][] = $taDocDS['Cabecera']['Observaciones'];
		}

		if ($taDocDS['Emisor']['direccion']['CodigoPais']=='CO') {
			$this->aDoc['documento']['ubicacion']['codigoMunicipio'] = $taDocDS['Emisor']['direccion']['CodigoMunicipio'];
		}
		if (isset($taDocDS['Emisor']['direccion']['NombreCiudad']) && !empty($taDocDS['Emisor']['direccion']['NombreCiudad'])) {
			$this->aDoc['documento']['ubicacion']['ciudad'] = $taDocDS['Emisor']['direccion']['NombreCiudad'];
		}
		if (isset($taDocDS['Emisor']['direccion']['NombreDepartamento']) && !empty($taDocDS['Emisor']['direccion']['NombreDepartamento'])) {
			$this->aDoc['documento']['ubicacion']['departamento'] = $taDocDS['Emisor']['direccion']['NombreDepartamento'];
		}

		if (isset($taDocDS['DescuentosOCargos']) && is_array($taDocDS['DescuentosOCargos']) && count($taDocDS['DescuentosOCargos'])>0) {
			foreach ($taDocDS['DescuentosOCargos'] as $laDescCargo) {
				$this->aDoc['cargosDescuentos'][] = [
					'esCargo' => $laDescCargo['Indicador']==true,
					'codigo' => '01',
					'notas' => [$laDescCargo['Justificacion']],
					'valorImporte' => floatval($laDescCargo['Valor'] ?? '0'),
					'valorBase' => floatval($laDescCargo['Base'] ?? '0'),
					'porcentaje' => floatval($laDescCargo['Porcentaje'] ?? '0'),
				];
			}
		} else {
			unset($this->aDoc['cargosDescuentos']);
		}

		$lbVrSinImpuestosCero = true;
		if (isset($taDocDS['Retenciones'])) {
			foreach ($taDocDS['Retenciones'] as $laImpuesto) {
				if (in_array($laImpuesto['tipo'], ['01','05','06'])) {
					$this->aDoc['tributos'][] = [
						'id' => $laImpuesto['tipo'],
						'nombre' => $laImpuesto['nombre'],
						'esImpuesto' => false,
						'valorImporteTotal' => floatval($laImpuesto['valor']),
						'detalles' => [[
							'valorImporte' => floatval($laImpuesto['valor']),
							'valorBase' => floatval($laImpuesto['vrBase']),
							'porcentaje' => round(floatval($laImpuesto['porcen']),3),
						]],
					];
					if ($laImpuesto['tipo']=='01') $lbVrSinImpuestosCero = false;
				}
			}
		}
		if (count($this->aDoc['tributos'])==0) {
			unset($this->aDoc['tributos']);
		}
		if ($lbVrSinImpuestosCero) {
			$this->aDoc['totales']['valorTotalSinImpuestos'] = 0;
		}

		foreach ($taDocDS['Lineas'] as $lnNum => $laLinea) {
			$laDetalle = [
				'tipoDetalle' => 1,
				'valorCodigoInterno' => $laLinea['codg'],
				'codigoEstandar' => '999',
				'valorCodigoEstandar' => $laLinea['codg'],
				'descripcion' => $laLinea['desc0'] ?? $laLinea['desc'],
				'unidades' => floatval($laLinea['cant']),
				'unidadMedida' => $this->aConfig['um'],
				'valorUnitarioBruto' => floatval($laLinea['vrud']),
				'valorBruto' => floatval($laLinea['vrtt']),
				'FechaPago' => [
					'FechaCompra' => $taDocDS['Cabecera']['FechaEmision'],
					'CodigoDescripcion' => intval($laLinea['gent']),
					'Descripcion' => $this->aConfig['generaTransm'][$laLinea['gent']],
				],
				//	'cargosDescuentos' => [],
				//	'tributos' => [],
			];
			if (isset($this->aDoc['cargosDescuentos'])) {
				$laCargosDesc = [];
				foreach ($this->aDoc['cargosDescuentos'][0] as $lcClave=>$laCargoDesc) {
					if ($lcClave!=='codigo') {
						$laCargosDesc[$lcClave] = $laCargoDesc;
					}
				}
				$laDetalle['cargosDescuentos'][] = $laCargosDesc;
			}
			$this->aDoc['detalles'][] = $laDetalle;
		}

		if ($taDatos['TIPDOCXML']=='NS') {
			foreach ($taDocDS['SoporteAdquisicionesRelacionados'] as $taSoporteAdqRel) {
				$this->aDoc['documento']['documentosAfectados'][] = [
					'UUID' => $taSoporteAdqRel['CUDS'],
					'numeroDocumento' => $taSoporteAdqRel['Numero'],
					'fecha' => $taSoporteAdqRel['FechaEmision'],
					'codigoCausal' => intval($taDocDS['MotivosNota'][0]['ConceptoCorreccion']),
				];
			}
		}

		return $this->limpiarChar(json_encode($this->aDoc));
	}


	private function limpiarChar($tcTexto)
	{
		return str_replace($this->aCharEliminarTr['buscar'], $this->aCharEliminarTr['cambio'], $tcTexto);
	}

	/*
	 *	Plantilla de información adicional para transfiriendo
	 */
	private function infoAdicionalTransfiriendo()
	{
		return [
			// transfiriendo
			'CodigoHabitacion'			=> $this->aConfig['CodMinSalud'],
			'NumeroIngreso'				=> ' ',
			'NumeroAutorizacion'		=> ' ',
			'TipoDocumentoPaciente'		=> ' ',
			'NumeroDocumentoPaciente'	=> ' ',
			'PrimerNombrePaciente'		=> ' ',
			'SegundoNombrePaciente'		=> ' ',
			'PrimerApellidoPaciente'	=> ' ',
			'SegundoApellidoPaciente'	=> ' ',
			'FechaNacimientoPaciente'	=> ' ',
			'SexoPaciente'				=> ' ',
			'EsMultipaciente'			=> 'false',
			'TipoServicio'				=> ' ',
			'NumeroAtencionConvenio'	=> ' ',
			'DiasEstancia'				=> ' ',
			'DiagnosticoIngreso'		=> ' ',
			'DiagnosticoEgreso'			=> ' ',
			'SociedadMedica'			=> ' ',
			'PolizaAtencion'			=> ' ',
			'NumeroHistoria'			=> ' ',
			'CodigoConvenio'			=> ' ',
			'CodigoFacturador'			=> ' ',
			'TipoDocumentoFacturador'	=> ' ',
			'NumeroDocumentoFacturador'	=> ' ',
			'NombreFacturador'			=> ' ',
			'ApellidoFacturador'		=> ' ',
			'Idpacienteconvenio'		=> ' ',
			'TotalFacturaDolares'		=> ' ',
			'TotalFacturaDolaresMoneda'	=> ' ',
			'CuotaModeradora'			=> ' ',
			'Copago'					=> ' ',
			'CuotaRecuperacion'			=> ' ',
			'CodDiagnostico'			=> ' ',
			'TipoPlan'					=> ' ',
			'TipoContrato'				=> ' ',
			'TipoAtencion'				=> ' ',
			'CUFE'						=> ' ',
			'NumeroFacturaReferencia'	=> ' ',
			'CausaExterna'				=> ' ',
			// Shaio
			'RazonSocialEmisor'			=> $this->aConfig['emisor']['RazonSocial'],
			'DireccionEmisor'			=> $this->aConfig['direccion']['Direccion'],
			'CiudadEmisor'				=> $this->aConfig['direccion']['NombreCiudad'],
			'TelefonoEmisor'			=> $this->aConfig['extensiones']['telefonoshaio'],
			'EmailEmisor'				=> $this->aConfig['extensiones']['correoshaio'],
			'ResponsabilidadEmisor'		=> $this->aConfig['extensiones']['textoimpuesto'],
			'RetenedorEmisor'			=> $this->aConfig['extensiones']['textoretenedor'],
			'ContribuyenteEmisor'		=> $this->aConfig['extensiones']['textograndescontribuyentes'],
			'RetencionEmisor'			=> $this->aConfig['extensiones']['textoretencion'],
			'TextoDian'					=> $this->aConfig['extensiones']['textodian'],
			'TextoCondiciones'			=> $this->aConfig['extensiones']['textocondiciones'],
			'TextoServiciosSalud'		=> $this->aConfig['extensiones']['textoserviciossalud'],
			'FechaIngresoPaciente'		=> ' ',
			'FechaEgresoPaciente'		=> ' ',
			'TelefonoPaciente'			=> ' ',
			'DireccionPaciente'			=> ' ',
			'TipoAfiliadoPaciente'		=> ' ',
			'PlanPaciente'				=> ' ',
		];
	}


	/*
	 *	Plantilla de información adicional por línea para transfiriendo
	 */
	private function infoAdicionalLineaTransfiriendo()
	{
		return [
			'FechaPrestacion' => ' ',
			'TipoDocumentoPaciente' => $this->aDocFE['InformacionAdicional']['TipoDocumentoPaciente'] ?? ' ',
			'NumeroDocumentoPaciente' => $this->aDocFE['InformacionAdicional']['NumeroDocumentoPaciente'] ?? ' ',
			'PrimerNombrePaciente' => $this->aDocFE['InformacionAdicional']['PrimerNombrePaciente'] ?? ' ',
			'SegundoNombrePaciente' => $this->aDocFE['InformacionAdicional']['SegundoNombrePaciente'] ?? ' ',
			'PrimerApellidoPaciente' => $this->aDocFE['InformacionAdicional']['PrimerApellidoPaciente'] ?? ' ',
			'SegundoApellidoPaciente' => $this->aDocFE['InformacionAdicional']['SegundoApellidoPaciente'] ?? ' ',
			'TarifaContratada' => ' ',
			'ValidarTarifaContratada' => ' ',
			'NumeroAutorizacion' => ' ',
			'NumeroIngreso' =>  $this->aDocFE['InformacionAdicional']['NumeroIngreso'] ?? ' ',
			'NumeroCuenta' => ' ',
			'ValorUnitarioOtraDivisa' => ' ',
			'MonedaOtraDivisa' => ' ',
		];
	}

}