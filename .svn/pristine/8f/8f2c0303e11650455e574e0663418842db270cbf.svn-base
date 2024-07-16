<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;
	require_once (__DIR__ . '/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ . '/../../controlador/class.Rips_factura.php');
	
	use NUCLEO\Rips_factura;	
	$loFacturacion = new Rips_factura();
	$laOpcionesMenu=$loFacturacion->crearMenuRips();
	
	$llPagina=false;
	$lcPagina = '';
	
	if(isset($_GET)){
		if(isset($_GET['q'])){
			$lcPagina = trim(strtolower($_GET['q']));
		}
	}
	
	if(!empty($lcPagina)){
		$lcPagina = __DIR__ .'/'.$lcPagina.".php";
		if(is_file($lcPagina)){
			include($lcPagina);
			$llPagina=true;
		}
	}	
	
	if($llPagina==false){
		$lcTipoMenu = 'LISTADO'; // LISTADO / ICONOS
?>
<div class="container-fluid">
	<div class="card mt-3">
		<div class="card-header">
			<ul class="nav nav-tabs card-header-tabs" id="hcwTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="alerta-tab" data-toggle="tab" href="#alerta" role="tab" aria-controls="menu" aria-selected="true">Facturaci&oacute;n</a>
				</li>
			</ul>
		</div>
		<div class="tab-content card-body" id="hcwTabContent">
			<div class="tab-pane fade show active" id="alerta" role="tabpanel" aria-labelledby="alerta">
				<div class="list-group">
					<?php foreach($laOpcionesMenu as $laOpcMenu): ?>
						<a href="<?php echo $laOpcMenu['URL']; ?>" class="list-group-item list-group-item-action border-0">
							<div class="media">
								<i class="fas fa-<?php echo $laOpcMenu['ICONO']; ?> align-self-center mr-3"></i>
								<div class="media-body">
									<b><?php echo $laOpcMenu['TITULO']; ?></b><br/><?php echo $laOpcMenu['TEXTO']; ?>
								</div>
							</div>
						</a>
					<?php endforeach; ?>
				</div>
<?php	 
	} //endif;
?>
			</div>
		</div>
	</div>
</div>
<link rel="stylesheet" type="text/css" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css"/>
<link rel="stylesheet" type="text/css" media="screen" href="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.css" />
<script type="text/javascript" src="publico-complementos/jquery-confirm/3.3.4/jquery-confirm.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/tableExport.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="publico-complementos/table-export/1.10.16/libs/js-xlsx/xlsx.core.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/locale/bootstrap-table-es-ES.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
<script type="text/javascript" src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>
<script type="text/javascript" src="publico-complementos/moment-develop/2.29.1-dist/moment.min.js"></script>
<script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
<script type="text/javascript" src="vista-facturacion/js/script_rips.js"></script>
<script type="text/javascript" src="vista-facturacion/js/rips1036.js"></script>

