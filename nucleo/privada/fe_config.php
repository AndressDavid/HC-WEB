<?php
/* Configuración para Facturación Electrónica */

$config = [
	// Ambiente de operación
	'ambiente' => 'Producción',
	'cod_ambiente' => [
		'Producción'=>1,
		'Pruebas'=>2,
	],

	// Proveedor de facturación electrónica: facture - transfiriendo
	'proveedor' => 'transfiriendo',

	// Documentos activos
	'documentos' => ['06FA','06NC','25FA','25NC','25ND','27NC','27ND','DSFA','DSNC',],

	// parametros facturación
	'parFac' => [
		'usarCodCentro'		=> false,		// En factura asistencia incluir Código del centro de costo con el código del ítem
		'addSubDetalles'	=> true,		// Adicionar subdetalles para CUPS (por ej. en radiografías con portátil)
		'detalleMasCuenta'	=> true,		// En facturas varios, true => detalle + descripción cuenta, false => detalle o descripción cuenta
		'guardarXml'		=> false,		// Guardar archivo XML o JSON siempre. false => solo lo guarda cuando hay error
		'usarPrefijoNotas'	=> true,		// Usar prefijo en Notas
		'fechaInicio'		=> 20200804,	// Fecha Inicio de facturación electrónica
		'fechaInicioDS'		=> 20220801,	// Fecha Inicio de Documentos de Soporte de Adquisiciones
		'restarAnticipos'	=> true,		// Restar anticipos al total de facturas del sector salud
		'connect_timeout'	=> 15,			// Tiempo de espera en segundos para conectarse al proveedor
		'timeout'			=> 60,			// Tiempo de espera en segundos para esperar respuesta del proveedor
		'calcularDVenDS'	=> false,		// Se calcula el DV para proveedores, si es false se obtiene de PRMTE1.TE1DIG
		'saltoLinea' => [					// Salto entre líneas en detalles
			'facture'		=> '<br>',
			'transfiriendo'	=> chr(13),
		],
	],

	// Datos Shaio
	'emisor' => [
		'RazonSocial' => 'FUNDACIÓN ABOOD SHAIO',
		'TipoPersona' => '1', // Persona jurídica
		'TipoIdentificacion' => '31', // NIT
		'NumeroIdentificacion' => '860006656',
		'DV' => '9',
		'NombreComercial' => 'FUNDACIÓN CLÍNICA SHAIO',
	],
	'emisorDS' => [
		'RazonSocial' => 'FUNDACIÓN ABOOD SHAIO',
		'TipoPersona' => '1', // Persona jurídica
		'TipoIdentificacion' => '31', // NIT
		'NumeroIdentificacion' => '860006656',
		'DV' => '9',
	],
	'CodMinSalud' => '110010644701',

	'ciiu' => [],

	'direccion' => [
		'CodigoMunicipio' => '11001',
		'NombreCiudad' => 'Bogotá, D.C.',
	//	'CodigoPostal' => '111121', // http://visor.codigopostal.gov.co/472/visor/ => Código Postal: 111121, Código Postal Ampliado: 111121364
		'CodigoDepartamento' => '11',
		'NombreDepartamento' => 'Bogotá, D.C.',
		'Direccion' => 'Dg. 115A #70C - 75',
	],
	'direccionFiscal' => [
		'CodigoMunicipio' => '11001',
		'NombreCiudad' => 'Bogotá, D.C.',
	//	'CodigoPostal' => '111121', // http://visor.codigopostal.gov.co/472/visor/ => Código Postal: 111121, Código Postal Ampliado: 111121364
		'CodigoDepartamento' => '11',
		'NombreDepartamento' => 'Bogotá, D.C.',
		'Direccion' => 'Dg. 115A #70C - 75',
	],
	'direccionAdicional' => [],

	'telefono' => '5938210',
	'mailNotifica' => 'factura.electronica@shaio.org',
	'mailPrueba' => 'fabian.bejarano@shaio.org',

	'obligaciones' => [
		'O-13' => 'Gran contribuyente',
		'O-23' => 'Agente de retención IVA',
	],

	'tributo' => [
		'01' => 'IVA',
	],

	'contacto' => [
		//'Nombre' => '',
		//'Telefono' => '593 8210 ext 8006',
		//'Telfax' => ''
		//'Email' => 'factura.electronica@shaio.org',
		//'Notas' => '',
	],

	'resFactura' => [
		'Pruebas' => [
			'NumeroResolucion' => '18760000001',
			'FechaInicio' => '2019-01-19',
			'FechaFin' => '2030-01-19',
			'PrefijoNumeracion' => 'SETT',
			'ConsecutivoInicial' => '1',
			'ConsecutivoFinal' =>   '8000000',
		],
		'Producción' => [
			'NumeroResolucion' => '18764031800397',
			'FechaInicio' => '2022-07-28',
			'FechaFin' => '2023-07-28',
			'PrefijoNumeracion' => '',
			'ConsecutivoInicial' => '5888700',
			'ConsecutivoFinal' =>   '8000000',
		],
	],

	'resDS' => [
		'Pruebas' => [
			'NumeroResolucion' => '18760000001',
			'FechaInicio' => '2022-01-01',
			'FechaFin' => '2022-12-31',
			'PrefijoNumeracion' => 'SEDS',
			'ConsecutivoInicial' => '984000000',
			'ConsecutivoFinal' =>   '985000000',
		],
		'Producción' => [
			'NumeroResolucion' => '18764017226934',
			'FechaInicio' => '2021-08-30',
			'FechaFin' => '2022-08-30',
			'PrefijoNumeracion' => 'RS',
			'ConsecutivoInicial' => '6596',
			'ConsecutivoFinal' =>   '7500',
		],
	],

	'extensiones' => [
		// Encabezado
			'telefonoshaio' => '593 8210 ext. 8006',
			'correoshaio' => 'factura.electronica@shaio.org',
			'textoimpuesto' => 'Responsable del impuesto sobre las ventas - IVA',
			'textoretenedor' => 'Agente Retenedor de Impuesto de IVA (Art. 437-2 E.T.) - Los servicios de salud no están sujetos a IVA (Art. 476 E.T., Dec. 624/89)',
			'textograndescontribuyentes' => 'Somos Grandes Contribuyentes (Res. 12220 del 26-12-2022) - Entidad Sin Ánimo de Lucro, Régimen Tributario Especial',
			'textoretencion' => 'No está sujeta a Retención a título de renta (Art 19 y 369 E.T.) ni de ICA (Art. 4 Acuerdo 21/83 Concejo de Bta)',
		// Pie
			'textodian' => 'El ejemplar que reciba la DIAN se utilizará sólo para fines de control, verificación y fiscalización.',
			'textocondiciones' => 'CONDICIONES: RECIBÍ A ENTERA SATISFACCIÓN LOS SERVICIOS, MEDICAMENTOS Y/O ELEMENTOS ARRIBA DESCRITOS. TODOS LOS DERECHOS ECONÓMICOS DERIVADOS DE ESTA FACTURA DE VENTA HAN SIDO CEDIDOS A LA FIDUCIARIA DE OCCIDENTE S.A. MEDIANTE EL PATRIMONIO AUTÓNOMO FIDUOCCIDENTE FID 3-1-197 CON NIT 830.054.076-2 DEL 01/09/99, TODOS LOS PAGOS DEBEN SER GIRADOS A SU NOMBRE. LA FACTURA CUMPLE CON LOS PARÁMETROS ESTABLECIDOS EN LA RESOLUCIÓN 0055 DE 2016.',
			'textoserviciossalud' => 'LOS SERVICIOS DE SALUD NO ESTÁN SUJETOS A IVA (ART. 476  E.T., DEC. 624/89)',
	],

	'extensionesDS' => [
		// Encabezado
			'telefonoshaio' => '593 8210 ext. 8006',
			'correoshaio' => 'factura.electronica@shaio.org',
			'textoimpuesto' => 'Responsable del impuesto sobre las ventas - IVA',
			'textoretenedor' => 'Agente Retenedor de Impuesto de IVA (Art. 437-2 E.T.) - Los servicios de salud no están sujetos a IVA (Art. 476 E.T., Dec. 624/89)',
			'textograndescontribuyentes' => 'Somos Grandes Contribuyentes (Res. 12220 del 26-12-2022) - Entidad Sin Ánimo de Lucro, Régimen Tributario Especial',
	],

	// Tipo documento
	'versionUbl' => 'UBL 2.0',
	'versionFrmDoc' => 'DIAN 1.0',
	'versionXml' => '1.0',
	'encodingXml' => 'UTF-8',
	'tipoDoc' => [ // Tipo de documento
		'FA' => [			// Factura
			'DocXML' => 'Factura',
			'DocRef' => 'FACTURA-UBL',
			'tipoFac' => '01',
			'tipoOperacion' => '10',  // 10-Estandar - Tabla 6.1.5.1 Anexo Técnico v 1.7.-2020
		],
		'FC' => [			// Factura de contingencia
			'DocXML' => 'Factura',
			'DocRef' => 'FACTURA-UBL',
			'tipoFac' => '03',
			'tipoOperacion' => '10',  // 10-Estandar - Tabla 6.1.5.1 Anexo Técnico v 1.7.-2020
		],
		'FD' => [			// Factura de contingencia DIAN
			'DocXML' => 'Factura',
			'DocRef' => 'FACTURA-UBL',
			'tipoFac' => '04',
			'tipoOperacion' => '10',  // 10-Estandar - Tabla 6.1.5.1 Anexo Técnico v 1.7.-2020
		],
		'NC' => [			// Nota Crédito
			'DocXML' => 'NotaCredito',
			'DocRef' => 'NC-UBL',
			'tipoFac' => '91',
			'tipoOperacion' => '20',		// 20-Nota Crédito que referencia una factura electrónica - Tabla 6.1.5.2 Anexo Técnico v 1.7.-2020
			'tipoOperacionNoFe' => '22',	// 22-Nota Crédito sin referencia a facturas
			'prefijo' => 'NC',
		],
		'ND' => [			// Nota Débito
			'DocXML' => 'NotaDebito',
			'DocRef' => 'ND-UBL',
			'tipoFac' => '92',
			'tipoOperacion' => '30',		// 30-Nota Débito que referencia una factura electrónica - Tabla 6.1.5.3 Anexo Técnico v 1.7.-2020
			'tipoOperacionNoFe' => '32',	// 32-Nota Débito sin referencia a facturas
			'prefijo' => 'ND',
		],
		'DS' => [			// Documento Soporte de Adquisiciones (DSA)
			'DocXML' => 'SoporteAdquisiciones',
			'DocRef' => 'SOPORTE-ADQUISICION',
			'tipoFac' => '05',
			'prefijo' => 'RS',
			'retenciones' => 'TOTAL',		// Forma de reportar retenciones: NO, TOTAL, PORLINEA, TODO
			'medioPago' => [
				'medio' => '47',
				'forma' => '2',
			],
		],
		'NS' => [			// Nota de Ajuste al DSA
			'DocXML' => 'NotaSoporteAdquisiciones',
			'DocRef' => 'NA-SOPORTE-ADQUISICION',
			'tipoFac' => '95',
			'prefijo' => 'DSNC',
			'retenciones' => 'TOTAL',		// Forma de reportar retenciones: NO, TOTAL, PORLINEA, TODO
			'medioPago' => [
				'medio' => '47',
				'forma' => '2',
			],
		],
	],

	// Otros datos
	'um' => '94', // Unidad de medida, 94 - unidad
	'umdsc' => 'unidad',
	'txtDespuesValorLetras' => ' MCTE.',
	'rutaGuardarXml' => __DIR__ . '/../../facturae/archivosxml/',
	'rutaGuardarErr' => __DIR__ . '/../../facturae/log_err/',
	'divisa' => 'COP', // Divisa consolidada aplicable a toda la factura
	'plazoVence' => 30, // Número de días por defecto para vencimiento factura
	'tiposId' => [ // Actualizados Anexo Técnico 1.9 DIAN
		'V' => '13', // Certificado de nacido vivo > CONSUMIDOR FINAL
		'R' => '11', // Registro civil
		'T' => '12', // Tarjeta de identidad
		'C' => '13', // Cédula de ciudadanía
		'A' => '13', // Adulto sin identificar > CONSUMIDOR FINAL
		'M' => '13', // Menor sin identificar > CONSUMIDOR FINAL
		'E' => '22', // Cédula de extranjería
		'N' => '31', // Nit
		'P' => '41', // Pasaporte
		'D' => '13', // Salvoconducto > CONSUMIDOR FINAL
		'Q' => '48', // Permiso de proteccion temporal > CONSUMIDOR FINAL
		'X' => '42', // Documento de identificación extranjero > CONSUMIDOR FINAL
		'Y' => '13', // Carnet Diplomático > CONSUMIDOR FINAL
		'X1'=> '21', // Tarjeta de extranjería
		'X2'=> '47', // PEP
		'X3'=> '50', // NIT de otro país
		'X4'=> '91', // NUIP
	],
	// Tipos de Id que se reportan como CONSUMIDOR FINAL
	'tiposIdConsFinal' => [
		'V', // Certificado de nacido vivo
		'M', // Menor sin identificar
		'A', // Adulto sin identificar
		'Q', // Permiso de proteccion temporal
		'X', // Documento de identificación extranjero
		'Y', // Carnet Diplomático
		'D', // Salvoconducto
	],

	// Sector Salud
	'Salud' => [
		'enviar' => true,
		'enviarExtension' => false,
		'separador' => ';',
		'campos' => [
			'01' => ['CODIGO_PRESTADOR', ''],
			// '02' => ['TIPO_DOCUMENTO_IDENTIFICACION', 'salud_identificación.gc'],
			// '03' => ['NUMERO_DOCUMENTO_IDENTIFICACION', ''],
			// '04' => ['PRIMER_APELLIDO', ''],
			// '05' => ['SEGUNDO_APELLIDO', ''],
			// '06' => ['PRIMER_NOMBRE', ''],
			// '07' => ['SEGUNDO_NOMBRE', ''],
			// '08' => ['TIPO_USUARIO', 'salud_tipo_usuario.gc'],
			'09' => ['MODALIDAD_CONTRATACION', 'salud_modalidad_pago.gc'],
			'10' => ['COBERTURA_PLAN_BENEFICIOS', 'salud_cobertura.gc'],
			// '11' => ['NUMERO_AUTORIZACION', ''],
			// '12' => ['NUMERO_MIPRES', ''],
			// '13' => ['NUMERO_ENTREGA_MIPRES', ''],
			'14' => ['NUMERO_CONTRATO', ''],
			'15' => ['NUMERO_POLIZA', ''],
			// '16' => ['COPAGO', ''],
			// '17' => ['CUOTA_MODERADORA', ''],
			// '18' => ['CUOTA_RECUPERACION', ''],
			// '19' => ['PAGOS_COMPARTIDOS', ''],
			// '20' => ['/Invoice/cac:InvoicePeriod/cbc:StartDate', ''],
			// '21' => ['/Invoice/cac:InvoicePeriod/cbc:EndDate', ''],
		],
		'tiposId' => [ // Mapeo 3.0.15 Enviado por Rusbel Oviedo 2024-04-10
			'R' => ['RC', 'Registro civil de nacimiento', 11],
			'T' => ['TI', 'Tarjeta de identidad', 12],
			'C' => ['CC', 'Cédula de ciudadanía', 13],
			'E' => ['CE', 'Cédula de extranjería', 22],
			'P' => ['PA', 'Pasaporte', 41],
			'X' => ['DE', 'Documento extranjero', 42],
			'A' => ['AS', 'Adulto sin identificar', 95],
			'M' => ['MS', 'Menor sin identificar', 96],
			'Y' => ['DC', 'Carné diplomático', 97],
			'V' => ['CN', 'Certificado de nacido vivo', 98],
			'D' => ['SC', 'Salvoconducto', 99],
			'Z1'=> ['PE', 'Permiso especial de permanencia', 100],
			'Q' => ['PT', 'Permiso protección temporal', 101],
			'Z2'=> ['SI', 'Sin identificación', 102],
			'Z3'=> [' ', 'Tarjeta de extranjería', 21],
			'Z4'=> [' ', 'NUIP', 91],
			'Z5'=> [' ', 'NIT', 31],
		],
		'tiposUsuario' => [
			'01' => 'Contributivo cotizante',
			'02' => 'Contributivo beneficiario',
			'03' => 'Contributivo adicional',
			'04' => 'Subsidiado',
			'05' => 'Sin régimen',
			'06' => 'Especiales o de Excepción cotizante',
			'07' => 'Especiales o de Excepción beneficiario',
			'08' => 'Personas privadas de la libertad a cargo del Fondo Nacional de Salud',
			'09' => 'Tomador/Amparado ARL',
			'10' => 'Tomador/Amparado SOAT',
			'11' => 'Tomador/Amparado Planes voluntarios de salud',
			'12' => 'Particular',
		],
		'modPago' => [
			'01' => 'Paquete / Canasta / Conjunto Integral en Salud',
			'02' => 'Grupos Relacionados por Diagnóstico',
			'03' => 'Integral por grupo de riesgo',
			'04' => 'Pago por contacto por especialidad',
			'05' => 'Pago por escenario de atención',
			'06' => 'Pago por tipo de servicio',
			'07' => 'Pago global prospectivo por episodio',
			'08' => 'Pago global prospectivo por grupo de riesgo',
			'09' => 'Pago global prospectivo por especialidad',
			'10' => 'Pago global prospectivo por nivel de complejidad',
			'11' => 'Capitación',
			'12' => 'Por servicio',
		],
		'cobertura' => [
			'01' => 'Plan de beneficios en salud financiado con UPC',
			'02' => 'Presupuesto máximo',
			'03' => 'Prima EPS / EOC, no asegurados SOAT',
			'04' => 'Cobertura Póliza SOAT',
			'05' => 'Cobertura ARL',
			'06' => 'Cobertura ADRES',
			'07' => 'Cobertura Salud Pública',
			'08' => 'Cobertura entidad territorial, recursos de oferta',
			'09' => 'Urgencias población migrante',
			'10' => 'Plan complementario en salud',
			'11' => 'Plan medicina prepagada',
			'12' => 'Otras pólizas en salud',
			'13' => 'Cobertura Régimen Especial o Excepción',
			'14' => 'Cobertura Fondo Nacional de Salud de las Personas Privadas de la Libertad',
			'15' => 'Particular',
		],
		'reqPoliza' => [ '04', '10', '11', '12' ],
	],

	// Regimen, obligaciones
	'codTipoPer' => [
		'juridica'	=> 1,
		'natural'	=> 2,
	],
	'codFormaPago' => [
		'contado'	=> 1,
		'credito'	=> 2,
	],
	'codRegimen' => [
		'respIva'	=> 48,
		'norespIva'	=> 49,
	],
	'conceptos' => [
		'NC' => [
			1=>'Devolución parcial de los bienes y/o no aceptación parcial del servicio',
			2=>'Anulación de factura electrónica',
			3=>'Rebaja o descuento parcial o total',
			4=>'Ajuste de precio',
			5=>'Otros',
		],
		'ND' => [
			1=>'Intereses',
			2=>'Gastos por cobrar',
			3=>'Cambio del valor',
			4=>'Otros',
		],
	],
	'codigosOblig' => [
		'13' => 'O-13',
		'15' => 'O-15',
		'23' => 'O-23',
		'47' => 'O-47',
		'99' => 'R-99-PN',
	],
	'generaTransm' => [
		'1' => 'Por operación',
		'2' => 'Acumulado semanal',
	],

	// valores predeterminados según tipo de persona
	'cliente' => [
		'regimen' => [
			1 => '48', // Responsable IVA
			2 => '49', // No Responsable IVA
		],
		'tributo' => [
			1 => ['ZZ'=>'No aplica',],
			2 => ['ZZ'=>'No aplica',],
		],
		'respfiscal' => [
			1 => ['O-23',],	// Agente de retención IVA
			2 => ['R-99-PN',], // No responsable
		],
		'formaPago' => [
			0 => 2,	// crédito para DS
			1 => 2,	// persona jurídica a crédito
			2 => 1, // persona natural a contado
		],
		'medioPago' => [
			0 => 47,	// Transferencia débito bancaria para DS
			1 => 'ZZZ', // acuerdo mutuo
			2 => 'ZZZ', // acuerdo mutuo
		],
		'formaPagoDoc' => [
			'25FA' => 1, // contado a partir del 2022-09-02, GLPI 88057 - Adriana Pinilla
			'25NC' => 1,
		],
		'consumidorFinal' => [
			'razons' => 'Consumidor final',
			'nombre' => 'Consumidor',
			'apellido' => 'final',
			'tipoId' => '13',
			'numeId' => '222222222222',
			'tipoPer' => '2',
			'regimen' => 'No aplica',
			'respfiscal' => ['R-99-PN'],
			'tributo' => ['ZZ'=>''],
			'telefono' => '',
			'correo' => '',
			'contacto' => '',
		],
		'tipoOperacion' => '10', // Para DS
	],

	// valores para conexión Facture
	'facture' => [
		'Pruebas' => [
			'Usuario' => '860006656',
			'Clave' => 'abc123$$',
			'TenantId' => '6a691922-0254-45cb-b5ac-abc0016ac8bb',
			'email' => 'fabian.bejarano@shaio.org',
			'pkEmision' => '963c6caf744a46329329dc0158bbc3ab',
			'pkEmision2' => '32154260e370442bb0273d442dd46db8',
			'pkRecepcion' => '4b66486bccc042e2b336b61bdced1caa',
			'keyControl' => 'fc8eac422eba16e22ffd8c6f94b3f40a6e38162c',
			'urlBase' => 'https://plcolabbeta.azure-api.net/',
			'urlToken' => 'Auth/Login',
			'urlEmitir' => 'Issue/Xml3',
			'urlConsultar' => '',
			'sumar' => -5000000,
			'sumarnota' => 0,
		],
		'Producción' => [
			'Usuario' => '860006656',
			'Clave' => 'd5Jqch2WVW.',
			'TenantId' => '6D43930F-DBDF-4DF5-8D4A-AC0A0018A17B',
			'email' => 'facture.tecnico@shaio.org',
			'pkEmision' => '05b5820c1f2a4c9caea7a9b835c0df7e',
			'pkEmision2' => 'faafa43c32c64e698091bc046bc037f3',
			'pkRecepcion' => 'f68dffb0acb643cba5168ef4b24f16e6',
			'pkRecepcion2' => 'ff083872b0424028b1866fa4ed0c570a',
			'keyControl' => 'e7cc080cd7353d1821cfb4b3294d452da965304ee1ffe6a7efbfbe08a4f6705f',
			'urlBase' => 'https://plcolab-api.azure-api.net/',
			'urlToken' => 'Auth/Login',
			'urlEmitir' => 'Issue/Xml3',
			'urlConsultar' => '',
		],
	],


	// valores para conexión Transfiriendo
	'transfiriendo' => [
		'Pruebas' => [
			//	'Usuario' => 'admin',
			//	'Clave' => 'super',
			//	'urlBase' => 'https://preifacturaorquestadorwin.azurewebsites.net/api/',
			//	'urlEmitir' => 'procesarDocumento',
			'urlBase' => 'https://multiradicadorpre.transfiriendo.com/multiradicador_2/integraciones_isalud_to_isalud/',
			'urlEmitir' => 'webwook_orquestador_intermediacion',
		],
		'Producción' => [
			//	'Usuario' => 'admin',
			//	'Clave' => 'super',
			//	'urlBase' => 'https://ifacturaorquestadorwin.azurewebsites.net/api/',
			//	'urlEmitir' => 'procesarDocumento',
			'urlBase' => 'https://multiradicador.transfiriendo.com/multiradicador_2/integraciones_isalud_to_isalud/',
			'urlEmitir' => 'webwook_orquestador_intermediacion',
		],
		'PruebasDS' => [
			'Usuario' => 'usrintegracionshaio',
			'Clave' => 'Colombia2022*',
			'urlBase' => 'https://pre.ifactura.transfiriendo.com:8098/ISoporteTransfiriendowebapi/api/',
			'urlEmitir' => 'DocumentoSoporteservice/generarDocumentoSoporte',
		],
		'ProducciónDS' => [
			'Usuario' => 'integracionShaio',
			'Clave' => 'Shaioint22*',
			'urlBase' => 'https://ifacturagruposalud.transfiriendo.com/IsoporteIpsSaludWebApi/api/',
			'urlEmitir' => 'DocumentoSoporteservice/generarDocumentoSoporte',
		],
	],
];

return $config;
