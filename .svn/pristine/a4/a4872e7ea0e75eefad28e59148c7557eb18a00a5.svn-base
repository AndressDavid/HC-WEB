<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ . '/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ . '/../../controlador/class.SalasCirugia.php');

	$loIntervalo = new \DateInterval('P1D');
	$loIntervalo->invert = 1;
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer = new \DateTime( $goDb->fechaHoraSistema() );
	$ltAyer->add($loIntervalo);
	$ldFechaInicio = $ltAyer->format("Y-m-d");
	$ldFechaFin = $ltAhora->format("Y-m-d");
	$loListaSalas = (new NUCLEO\SalasCirugia())->aSalasCirugia;
	$lcRepPrefijoAjax='';
	$lcRepPrefijoComplementos='../publico-complementos/';
	$lcRepPrefijoComponentes='';

?><!doctype html>
<html lang="en" class="h-100">
	<head>
		<!-- Cabecera -->
		<?php
			$lcInclude = file_get_contents("../../publico/head.php");
			$lcInclude = str_replace("publico-complementos","../publico-complementos",$lcInclude);
			$lcInclude = str_replace("publico-css","../publico-css",$lcInclude);
			$lcInclude = str_replace("publico-ico","../publico-ico",$lcInclude);
			$lcInclude = str_replace("hcw-manifiest.json.php","../hcw-manifiest.json.php",$lcInclude);

			print($lcInclude);
		?>
	</head>
	<body class="h-100">
		<div class="container-fluid h-100 p-0">
			<?php include('reporte.php'); ?>
		</div>

		<div style="position: fixed; float:right; bottom: 0; right: 0; padding-right: 8px; padding-bottom: 8px;">
			<button id="cmdActualizar" class="btn btn-sm btn-primary"><i id="cmdActualizarIco" class="fas fa-sync fa-spin"></i> <span class="messageActualizar"></span></button>
		</div>

		<?php include(__DIR__ .'/../../publico/footer.php'); ?>
	</body>
</html>
<?php

?>