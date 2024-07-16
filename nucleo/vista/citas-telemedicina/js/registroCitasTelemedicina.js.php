<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
	
	$lnIngreso = intval(isset($_GET['ingreso'])?$_GET['ingreso']:'');
	$lcId = (isset($_GET['p'])?$_GET['p']:'');
	$lnId = (isset($_GET['q'])?$_GET['q']:0);
	$lnCita = (isset($_GET['r'])?$_GET['r']:0);
	$lnConsulta = (isset($_GET['s'])?$_GET['s']:0);
	$lnEvolucion = (isset($_GET['t'])?$_GET['t']:0);		
	
	$laWebResoucesConfig	= require __DIR__ . '/../../../privada/webResoucesConfig.php';
?>
function nombreFormatter(value, row, index) {
	return ('<a class="btn btn-secondary mr-1 btn-sm" href="<?php printf('%sdownload-private',$laWebResoucesConfig['pp']['url']); ?>?accion=paciente-archivo&paciente='+row.source+'&nombre='+row.name+'" role="button" target="blank"><i class="far fa-file-archive"></i></a><b class="text-uppercase text-muted">'+row.name+'</b>');
}

$(function() {	
	$('#tableListaArchivosCitasTelemedicina').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		columns: [
					[
						{field: 'name', title: 'Nombre', sortable: true, visible: true, class: 'text-nowrap', formatter: nombreFormatter},
						{field: 'date', title: 'Subido', sortable: true, visible: true, class: 'text-nowrap'},
						{field: 'size', title: 'Tama&ntilde;o', sortable: true, visible: true, class: 'text-nowrap'}, 
					]
				]
	});

	<?php if($lnIngreso>0){ ?>
	$('#btnTeleconsulta').on('click', function () {
		let lnIngreso = $('#lblIngreso').html();
		window.open('vista-jtm/index?p=<?php print($lnIngreso); ?>&gestor', "JTM", "height=600,width=800,location=1,status=1,scrollbars=1");
	});
	<?php } ?>
});