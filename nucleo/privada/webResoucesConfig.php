<?php
return [
	'jtm' => [
		'domain' => 'jtm.shaio.org',
		'url' => 'https://jtm.shaio.org/'
	],
	'pp' =>	[
		'domain' => 'ppd.shaio.org',
		'url' => 'https://ppd.shaio.org/',
		'rest-api-key' => 'b3906ec421d411746f24448586a211ae0b3a21099e1de0b2707acb97de66f99e'

	],
	'secure' =>	[
		'exclude-ssl' => [
			'nucleo/vista/alerta-temprana/dashboard.php',
			'nucleo/vista/alerta-temprana/monitoreo.php',
			'nucleo/vista/facturacion/monitor.php',
			'nucleo/vista/ldap/monitor.php',
			'nucleo/vista/mxtoolbox/monitor.php',
			'nucleo/vista/programacion-salas/dashboard.php',
			'nucleo/vista/programacion-salas/monitor.php',
			'nucleo/vista/tareas/monitor.php',
			'nucleo/vista/autenticacion/imagenes/captcha/captcha.php',
			'pantalla.php',
			'webservice/publico/alerta-temprana/index.php',
			'webservice/publico/docpdf/index.php',
			'webservice/publico/enviarhl7/index.php',
			'webservice/publico/laboratorio/index.php',
			'webservice/publico/librohc/index.php',
			'webservice/publico/mail/index.php',
			'webservice/publico/mail/test-secure.php',
			'webservice/publico/mail/test.php',
			'webservice/publico/mail/visor.php',
			'webservice/publico/mail/webservice.php',
			'websockets/publico/hl7_recibeoru/index.php',
		],
		'exclude-sesion-pages'=>[
			'cron.php',
			'download-private.php',
			'error.php',
			'hcw-manifiest.json.php',
			'index.php',
			'restapi/server/v1/index.php',
			'restapi/server/v2/index.php',
			'webservice/publico/alerta-temprana/index.php',
			'webservice/publico/docpdf/index.php',
			'webservice/publico/enviarhl7/index.php',
			'webservice/publico/laboratorio/index.php',
			'webservice/publico/librohc/index.php',
			'webservice/publico/mail/index.php',
			'webservice/publico/mail/test-secure.php',
			'webservice/publico/mail/test.php',
			'webservice/publico/mail/visor.php',
			'webservice/publico/mail/webservice.php',
			'websockets/publico/hl7_recibeoru/index.php',
			'nucleo/vista/comun/ajax/sesioncre.php',
			'nucleo/vista/comun/ajax/sesion.php',
			'nucleo/vista/comun/sesion.php',
			'nucleo/vista/autenticacion/forgot.php',
			'nucleo/vista/autenticacion/imagenes/captcha/captcha.php',
		],
		'exclude-sesion-servers'=>[
			'hcwp.shaio.org',
			'localhost'
		],
	]
];