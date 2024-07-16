<?php
require_once __DIR__ .'/../comun/ajax/verificasesion.php';

if ($lnContinuar) {

?>
<html>

<head>
	<link rel="stylesheet" href="../../publico/complementos/bootstrap/4.5.0-dist/css/bootstrap.min.css">
	<link rel="stylesheet" href="../../publico/complementos/fontawesome/5.13.1-web/css/all.min.css">
	<script type="text/javascript" src="../../publico/complementos/jquery/3.6.0/jquery-3.6.0.min.js"></script>
	<script type="text/javascript" src="../../publico/complementos/bootstrap/4.5.0-dist/js/bootstrap.min.js"></script>
</head>

<body>
	<div class="container-fluid pt-4">
		<div class="row text-center">
			<div id="divEspere" class="col">
				<img src="../../publico/imagenes/logo/main-logo-app96.png"><br>
				<b>Por favor, espere mientras se consulta la información</b><br>
				<i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;"></i>
			</div>
		</div>
	</div>
	<form id="formVistaPrevia" action="vistaprevia.php" method="POST" id="divPrueba"></form>
</body>

</html>
<?php
} else {
	$lcLocation = 'Location: '.($goDb->soWindows ? '../../../index.php?sesion=cerrada' : '/salir');
	header($lcLocation);
	die();
}
?>