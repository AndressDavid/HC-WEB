<?php
return	[
	'paginas' => [
		'mipres' => [
			'titulo'=>'Prescripciones',
			'icono'=>'fa-file-prescription',
			'descrp'=>'Consulta de las prescripciones realizadas en la Clínica Shaio y sus Novedades.',
			'tipo'=>'mipres',
			],
		'direccion' => [
			'titulo'=>'Direccionamiento',
			'icono'=>'fa-hospital',
			'descrp'=>'Consulta de las prescripciones direccionadas por las EPS a la FCS.',
			'tipo'=>'disprv',
			],
		'programacion' => [
			'titulo'=>'Programación',
			'icono'=>'fa-tasks',
			'descrp'=>'Permite registrar y consultar la programación de las entregas de direccionamientos',
			'tipo'=>'disprv',
			],
		'entrega' => [
			'titulo'=>'Entrega',
			'icono'=>'fa-prescription-bottle-alt',
			'descrp'=>'Permite registrar y consultar Entregas al paciente',
			'tipo'=>'disprv',
			],
		'reporte' => [
			'titulo'=>'Reporte Entrega',
			'icono'=>'fa-receipt',
			'descrp'=>'Permite registrar y consultar Reporte de Entrega',
			'tipo'=>'disprv',
			],
		'factura' => [
			'titulo'=>'Facturación',
			'icono'=>'fa-file-invoice-dollar',
			'descrp'=>'Permite registrar y consultar Facturación',
			'tipo'=>'disprv',
			],
		],
	'condensados' => [
		'prog_rep' => [
			'titulo'=>'Programación - Entrega - Reporte',
			'icono'=>'fa-sitemap',
			'descrp'=>'Programación, Entrega y Reporte de Entrega en un solo paso',
			'tipo'=>'disprv',
			],
		'entramb_rep' => [
			'titulo'=>'Entrega Ambito - Reporte',
			'icono'=>'fa-sitemap',
			'descrp'=>'Programación, Entrega y Reporte de Entrega en un solo paso',
			'tipo'=>'disprv',
			],
		],
	'links' => [
		'aLinkPrescripciones' => [
			'href' => 'modulo-mipres&q=prescripciones',
			'target' => '_blank',
			'icon' => 'fa-prescription',
			'caption' => 'Consulta Prescripciones',
			'permiso' => 'usarput',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkDireccionamientos' => [
			'href' => 'modulo-mipres&q=direccionamientos',
			'target' => '_blank',
			'icon' => 'fa-location-arrow',
			'caption' => 'Consulta Direccionamientos',
			'permiso' => 'usarput',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkMiPres' => [
			'href' => 'https://www.sispro.gov.co/central-prestadores-de-servicios/Pages/MIPRES.aspx',
			'target' => '_blank',
			'icon' => 'fa-landmark',
			'caption' => 'Página web MiPres',
			'permiso' => '',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkMiPres3' => [
			'href' => 'https://www.sispro.gov.co/central-prestadores-de-servicios/Pages/MIPRES-3.aspx',
			'target' => '_blank',
			'icon' => 'fa-landmark',
			'caption' => 'Página web MiPres3',
			'permiso' => '',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkSwaggerP' => [
			'href' => 'https://wsmipres.sispro.gov.co/WSMIPRESNOPBS/Swagger/',
			//'href' => 'https://tablas.sispro.gov.co/WSMIPRESNOPBS/swagger/',
			'target' => '_blank',
			'icon' => 'fa-align-left',
			'caption' => 'Swagger Prescripciones',
			'permiso' => '',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkSwaggerS' => [
			'href' => 'https://wsmipres.sispro.gov.co/WSSUMMIPRESNOPBS/Swagger/',
			//'href' => 'https://tablas.sispro.gov.co/WSSUMMIPRESNOPBS/swagger/',
			'target' => '_blank',
			'icon' => 'fa-align-left',
			'caption' => 'Swagger Suministros',
			'permiso' => '',
			'admin' => false,
			'enabled' => true,
			],
		'aLinkSwaggerF' => [
			'href' => 'https://wsmipres.sispro.gov.co/WSFACMIPRESNOPBS/Swagger/',
			//'href' => 'https://tablas.sispro.gov.co/WSFACMIPRESNOPBS/swagger/',
			'target' => '_blank',
			'icon' => 'fa-align-left',
			'caption' => 'Swagger Facturación',
			'permiso' => '',
			'admin' => false,
			'enabled' => true,
			],
		'aTokenTmp' => [
			'href' => '#',
			'target' => '',
			'icon' => 'fa-key',
			'caption' => 'Token Temporal',
			'permiso' => 'usarput',
			'admin' => false,
			'enabled' => true,
			],
		'aTokenFacTmp' => [
			'href' => '#',
			'target' => '',
			'icon' => 'fa-key',
			'caption' => 'Token Temporal Fact.',
			'permiso' => 'usarput',
			'admin' => false,
			'enabled' => true,
			],
		'aGenTokenTmp' => [
			'href' => '#',
			'target' => '',
			'icon' => 'fa-key',
			'caption' => 'Generar Token Temporal',
			'permiso' => 'usarput',
			'admin' => true,
			'enabled' => false,
			],
		'aGenTokenFacTmp' => [
			'href' => '#',
			'target' => '',
			'icon' => 'fa-key',
			'caption' => 'Generar Token Temporal Fact.',
			'permiso' => 'usarput',
			'admin' => true,
			'enabled' => false,
			],
		'aExportXlsx' => [
			'href' => '#',
			'target' => '',
			'icon' => 'fa-file-excel',
			'caption' => 'Consultar Registros',
			'permiso' => 'conregexp',
			'admin' => false,
			'enabled' => true,
			],
		],
	];
