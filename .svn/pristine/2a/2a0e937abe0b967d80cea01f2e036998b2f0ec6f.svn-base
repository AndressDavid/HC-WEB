<?php
include __DIR__ . '/../comun/modalEspera.php';
require_once (__DIR__ .'/../../publico/constantes.php');
require_once __DIR__ .'/../comun/ajax/verificasesion.php';


$btnVolver = '<a class="btn btn-secondary" href="index">Volver</a>';
$divMensaje = '<div class="container-fluid"><div class="card mt-3"><div class="card-header alert-danger"><div class="row"><div class="col"><h5>%s</h5>%s</div></div></div></div></div>';



if (isset($_SESSION[HCW_DATA])) {
	$laInterc = $_SESSION[HCW_DATA];
   unset($_SESSION[HCW_DATA]);
} else {
	$laInterc = $_POST;
}

if(!isset($laInterc['NINORD']) || !isset($laInterc['CCIORD']) ){
	printf($divMensaje, 'No se pudieron obtener los datos del paciente. Por favor intente nuevamente ingresar a procedimientos ordenados.', $btnVolver);
	return false;
}	



/* CLASES CONTROLLER */
require_once __DIR__ . '/../../controlador/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/../../controlador/class.Procedimientos.php';
require_once __DIR__ . '/../../controlador/class.rehabilitacionCardioVascular.php';


/* ARCHIVOS PROPRIOS */
require_once __DIR__."/controller/cabecera.php";
require_once __DIR__."/controller/procedimientosCardioVascular.php";

$cabeza = new Cabecera;
$pro = new ProcedimientoCardioVascular;
$cabeza->setIngreso($laInterc["NINORD"]);


?>

<div class="nav-bar navbar-light sticky-top CabDatosPac container-fluid small" id="divCabDatosPac">
   <?php  include __DIR__."/../comun/cabecera.php" ?>
</div>

<form id="resolverReha" action="" class="p-4 mb-5 mt-1" method='POST'>
   <?php  include __DIR__ . '/template/form.php'; ?>
   <div class="row">
      <div class="col-sm">
         <label for="AsisteOpc" class="required">Asistencia</label>
         <select class="custom-select custom-select-sm col-16" name="AsisteOpc" id="AsisteOpc">
         </select>
      </div>
   </div>
</form>


<div class="card-footer text-muted fixed-bottom bg-light">
	<div class="row justify-content-between">
		<div class="col">
         <button type="button" <?php if($datos["estado"]){ echo 'id="saveInformation"';}else{echo 'disabled';}?> class="btn btn-danger btn-sm">Guardar</button>
		</div>
		<div class="col-auto">
			<button id="btnVolver" type="button" class="btn btn-secondary btn-sm" data-toggle="tooltip" data-placement="top" title="Volver"><i class="fas fa-arrow-left"></i></button>
			<button id="btnVerPdfHC" <?php if($datos["codigoEstado"] == 8 || $datos["codigoEstado"] == 0 ){ echo 'disabled';}  ?> type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Ver PDF" ><i class="fas fa-file-pdf"></i></button>
			<button id="btnVistaPrevia" <?php if($datos["codigoEstado"] == 8 || $datos["codigoEstado"] == 0 ){ echo 'disabled';}  ?> type="button" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Vista Previa"><i class="fas fa-eye"></i></button>
			<button id="btnLibroHC" type="button" class="btn btn-primary btn-sm" data-toggle="tooltip" data-placement="top" title="Libro HC"><i class="fas fa-book-medical"></i></button>
		</div>
	</div>
</div>


<script type="text/javascript" src="publico-complementos/bootstrap-4-autocomplete/1.2.3/dist/bootstrap-4-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-autocomplete/2.3.7-dist/bootstrap-autocomplete.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/jquery.validate.min.js"></script>
<script type="text/javascript" src="publico-complementos/jquery-validation/1.17.0/localization/messages_es.min.js"></script>


<script src="nucleo/vista/proce-control/js/script.js"></script>
<script src="nucleo/vista/comun/js/diagnosticos.js"></script>

<script src="nucleo/vista/historiaclinica/js/finalidad.js"></script>


<script>
   aDatosIngreso = <?php print_r($cabeza->getDatosCabeza())?>;
   clase= <?php if(isset($datos['claseDiag'])){echo $datos['claseDiag'];} ?>;
   datosIngre = <?php echo json_encode($laInterc)?>;
   datosAsiste = <?php print_r($pro->getprocedimientos())?>;
   oCabDatosPac.inicializar();
   oEst = <?php echo $datos["codigoEstado"].';'; ?>
   start.inicializar();
   start.cargarSelectAsiste("AsisteOpc", <?php if(isset($datos['asistencia'])  && $datos["codigoEstado"] == 3  ){echo $datos['asistencia'];} ?>);
   <?php  if(!$datos["estado"]){ echo "dasactivarInputs();";} ?>;
   oFinalidad.inicializar(<?php if($datos['finalidad'] != 0 ){echo "'P','".$datos['finalidad']."'";}else{ echo "'P'";} ?>);
   oModalEspera.nIntervalo = 3;
   oModalEspera.mostrar('Se est&aacute; preparando el entorno de trabajo', 'Espere por favor');

</script>

<script src="nucleo/vista/comun/js/comun.js"></script>
<script src="nucleo/vista/comun/js/modalVistaPrevia.js"></script>