<?php
	$lcErrHeader="Ups!";
	$lcErrTitulo="Oh, oh, algo sali&oacute; mal...";
	$lcErrDescripcion="Se presento un problema.";
	$lcErrClass="danger";
	
	if(isset($_GET['403'])==true){
		$lcErrClass="warning";
		$lcErrHeader="403";
		$lcErrTitulo="Acceso denegado";
		$lcErrDescripcion="La solicitud fue correcta, pero se detecto un comportamiento extra&ntilde;o.";
	}else if(isset($_GET['423'])==true){
		$lcErrClass="warning";
		$lcErrHeader="423";
		$lcErrTitulo="Acceso denegado";
		$lcErrDescripcion="La solicitud fue correcta, pero no se autoriza el acceso.";		
	}		
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php print($lcErrTitulo); ?></title>
	<style>
		* {
		  -webkit-box-sizing: border-box;
				  box-sizing: border-box;
		}
		
		h1, h2 {color: #ec3a3b !important;}
		
		body {
		  padding: 0;
		  margin: 0;
		}

		#notfound {
		  position: relative;
		  height: 100vh;
		  color: #ec3a3b !important;
		}

		#notfound .notfound {
		  position: absolute;
		  left: 50%;
		  top: 50%;
		  -webkit-transform: translate(-50%, -50%);
			  -ms-transform: translate(-50%, -50%);
				  transform: translate(-50%, -50%);
		}

		.notfound {
		  max-width: 560px;
		  width: 100%;
		  padding-left: 160px;
		  line-height: 1.1;
		  color: #ec3a3b !important;
		}

		.notfound .notfound-404 {
		  position: absolute;
		  left: 0;
		  top: 0;
		  display: inline-block;
		  width: 140px!important;
		  height: 140px!important;
		  background-image: url(publico-imagenes/logo/main-logo-red.svg);
		  background-repeat: no-repeat;
		  background-position-x: center;
		}

		.notfound .notfound-404:before {
		  content: '';
		  position: absolute;
		  width: 100%;
		  height: 100%;
		  -webkit-transform: scale(2.4);
			  -ms-transform: scale(2.4);
				  transform: scale(2.4);
		  border-radius: 50%;
		  background-color: #f2f5f8;
		  z-index: -1;
		}

		.notfound h1 {
		  font-family: 'Nunito', sans-serif;
		  font-size: 65px;
		  font-weight: 700;
		  margin-top: 50px;
		  margin-bottom: 10px;
		  color: #151723;
		  text-transform: uppercase;
		}

		.notfound h2 {
		  font-family: 'Nunito', sans-serif;
		  font-size: 21px;
		  font-weight: 400;
		  margin: 0;
		  text-transform: uppercase;
		  color: #151723;
		}

		.notfound p {
		  font-family: 'Nunito', sans-serif;
		  color: #999fa5;
		  font-weight: 400;
		}

		.notfound a {
		  font-family: 'Nunito', sans-serif;
		  display: inline-block;
		  font-weight: 700;
		  border-radius: 40px;
		  text-decoration: none;
		  color: #ec3a3b !important;
		}
		.description{
			font-size: 24px;
		}
		@media only screen and (max-width: 767px) {
		  .notfound .notfound-404 {
			width: 110px;
			height: 110px;
		  }
		  .notfound {
			padding-left: 15px;
			padding-right: 15px;
			padding-top: 110px;
		  }
		}
		#foot{
			position: fixed;
			bottom: 0;
			margin: 50px;
			color: #999fa5;
			text-align: center;
			width: 100%;
			font-family: 'Nunito', sans-serif;
		    font-size: 9px;
		}
	</style>
</head>
<body>
	<div id="notfound">
		<div class="notfound">
			<div class="notfound-404"></div>
			<h1><?php print($lcErrHeader); ?></h1>
			<h2><?php print($lcErrTitulo); ?></h2>
			<p><span class="description"><?php print($lcErrDescripcion); ?></span></p>
			<p><small>&iquest;Necesita ayuda? Contacte al equipo de soporte por los canales que se ha socializado ampliamente a toda la Cl&iacute;nica mediante Intranet, Historia Cl&iacute;nica e Infoshaio, envi&eacute; su solicitud o ll&aacute;menos al  (+571) 593 8210 extensi&oacute;n 2173.</small></p>
			<a href="index.php">Volver al inicio</a>
		</div>
	</div>
	<div id="foot">
		<?php
			if(isset($_SERVER['REMOTE_ADDR'])==true){
				print("IP reportada: ".$_SERVER['REMOTE_ADDR']." - Nombre: ".trim(strtolower(gethostbyaddr($_SERVER['REMOTE_ADDR']))));
			}
		?>
	</div>
</body>
</html>