<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
	require_once (__DIR__ .'/../../../controlador/class.Nutriciones.php');
	
	$lnConsecutivo = intval(isset($_GET['p'])?$_GET['p']:'0');
	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$lnIngreso = intval(isset($_GET['r'])?$_GET['r']:'0');
	
	$loNutriciones = new NUCLEO\Nutriciones($lcTipoBitacora, $lnConsecutivo, $lnIngreso);
	
?>
let goMobile = new MobileDetect(window.navigator.userAgent);
var goValidatorSeguimiento = null;
var goValidatorProveedor = null;
var goConsecutivo =0;

function rowStyle(row, index) {
	return {
		css: {
			cursor: 'pointer'
		}
	}
}

function entidadFormatter(value, row, index) {
	return (row.ENTIDAD_RAZON_SOCIAL === ""?row.ENTIDAD_RAZON_COMERCIAL:row.ENTIDAD_RAZON_SOCIAL);
}

function habitacionFormatter(value, row, index) {
	return [row.SECCION,row.HABITACION].join('-');
}

function consecutivoFormatter(value, row, index) {
	return '<i class="fas fa-edit mr-2"></i>'+row.CONSECUTIVO;
}

function fechaHoraInicioFormatter(value, row, index) {
	return '<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.INICIO_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.INICIO_HORA,':')+'</span>';
}

function fechaHoraFinFormatter(value, row, index) {
	return (row.FIN_FECHA==0 && row.FIN_HORA==0?'Sin confirmaci&oacute;n':'<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.FIN_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.FIN_HORA,':')+'</span>');
}

function fechaHoraEgresoFormatter(value, row, index) {
	return (row.EGRESO_FECHA==0 && row.EGRESO_HORA==0?'Sin egreso':'<span class="p-0 badge badge-light bg-transparent">'+strNumAFecha(row.EGRESO_FECHA,'-')+'</span> <span class="font-weight-bold">'+strNumAHora(row.EGRESO_HORA,':')+'</span>');
}

function reloadBitacora(){
	formPostTemp('modulo-nutricion&p=registroNutriciones&q=<?php print($loNutriciones->getTipoBitacora()['CODIGO']); ?>', {CONSECUTIVO: goConsecutivo, INGRESO: <?php print($loNutriciones->getIngreso()->nIngreso); ?>}, false);	
}

function updateSeguimientoForm($toModal, $toForm, element){
	$toForm.trigger('reset');
	
	$("#cSegumientoEntidad option[value='"+element.ENTIDAD+"']").attr("selected","selected");
	$("#cSegumientoEntidad").attr("disabled","disabled");
	$("#cSegumientoTipo option[value='"+element.TRAMITE+"']").attr("selected","selected");
	$("#cSegumientoTipo").attr("disabled","disabled");
	$("#cSegumientoProveedor option[value='"+element.PROVEEDOR+"']").attr("selected","selected");
	$("#cSegumientoEstado option[value='"+element.ESTADO+"']").attr("selected","selected");
	$("#cSegumientoInicio").val(parseInt(element.INICIO_FECHA)<=0?"":strNumAFecha(element.INICIO_FECHA,"-")+" "+strNumAHora(element.INICIO_HORA,":"));
	$("#cSegumientoInicio").attr("disabled","disabled");
	$("#cSegumientoConfirmacion").val(parseInt(element.FIN_FECHA)<=0?"":strNumAFecha(element.FIN_FECHA,"-")+" "+strNumAHora(element.FIN_HORA,":"));	
	$("#nSegumientoConsecutivo").val(element.CONSECUTIVO);
	$("#nSegumientoConsecutivoBage").html(parseInt(element.CONSECUTIVO)>0?element.CONSECUTIVO:"");
	$("#cSegumientoObservacion").text(element.OBSERVACION);
	

	$toModal.modal("show");
	
}

$(function() {
	$('#tableDetalleBitacora').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped', 
		theadClasses: 'thead-light',		
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'CONSECUTIVO', title: 'ID', sortable: true, visible: false, formatter: consecutivoFormatter, class: 'text-nowrap'},
						{field: 'ENTIDAD',title: 'Entidad', formatter: entidadFormatter, class: 'text-nowrap'}, 
						{field: 'TRAMITE_NOMBRE',title: 'Tramite', class: 'text-nowrap'}, 
						{field: 'HABITACION',title: 'Habitación', formatter: habitacionFormatter, class: 'text-nowrap'}, 
						{field: 'PROVEEDOR_NOMBRE',title: 'Proveedor', class: 'text-nowrap'}, 
						{field: 'ESTADO_NOMBRE',title: 'Estado', class: 'text-nowrap'}, 
						{field: 'INICIO_FECHA',title: 'Inicio', formatter: fechaHoraInicioFormatter, class: 'text-nowrap'}, 
						{field: 'FIN_FECHA',title: 'Confirmaci&oacute;n', formatter: fechaHoraFinFormatter, class: 'text-nowrap'}, 
						{field: 'OPORTUNIDAD',title: 'Oportunidad Aseguradora', sortable: true, visible: false},  
						{field: 'EGRESO',title: 'Egreso', formatter: fechaHoraEgresoFormatter, class: 'text-nowrap'}, 
						{field: 'OBSERVACION',title: 'Observaci&oacute;n', class: 'text-nowrap'}, 
						{field: 'OPORTUNIDADTRAMITE',title: 'Oportunidad del trámite', sortable: true, visible: false, class: 'text-nowrap'}, 
					]
				]
	});
	$('.fixed-table-body').css('min-height','480px');
	
	
	$('.input-group.date').datetimepicker({
		format: 'YYYY-MM-DD HH:mm:ss',
		locale:'es',
		icons: { time: "fas fa-clock"}
	});
	
	// Cabecera
	goValidatorCabecera = $( "#cabeceraForm" ).validate( {
		rules: {
			nIngreso: {
				required: true,
			},
			cEstado: {
				required: true,
			},
		},
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			error.addClass( "invalid-tooltip" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
		},
		submitHandler: function () {
			$('#btnCabeceraGuardar').attr("disabled","disabled");
			$.ajax({
				type: 'POST',
				url: "vista-nutricion/ajax/registroNutriciones.ajax?cabecera&p=<?php print($loNutriciones->getConsecutivo()); ?>&q=<?php print($loNutriciones->getTipoBitacora()['CODIGO']); ?>&r=<?php print($loNutriciones->getIngreso()->nIngreso); ?>",
				data: $("#cabeceraForm").serialize()
			})
			.done(function(response) {
				if(response=='0'){
					infoAlert($('#registroCabecera'),'<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar la bit&aacute;cora', 'danger', true);
				}else{
					goConsecutivo = response;
					fnAlert('Se guardo la bit&aacute;cora '+response, 'Guardado', 'far fa-check-circle', 'green', false, reloadBitacora);
					
				}
				$('#btnCabeceraGuardar').removeAttr("disabled");
			})
			.fail(function(data) {
				$('#registroCabecera').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
				$('#btnCabeceraGuardar').removeAttr("disabled");
			});
		}
	} );
		
	$('#btnCabeceraGuardar').click(function() {
		$("#cabeceraForm").submit();
	});		

	// Seguimiento
	goValidatorSeguimiento = $( "#seguimientoForm" ).validate( {
		rules: {
			cSegumientoEntidad: {
				required: true,
			},
			cSegumientoTipo: {
				required: true,
			},
			cSegumientoEstado: {
				required: true
			},
			cSegumientoInicio: {
				required: true
			},
			cSegumientoObservacion: {
				required: true
			},
		},
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			error.addClass( "invalid-tooltip" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
		},
		submitHandler: function () {
			$('#btnSeguimientoGuardar').attr("disabled","disabled");
			$.ajax({
				type: 'POST',
				url: "vista-nutricion/ajax/registroNutriciones.ajax?p=<?php print($loNutriciones->getConsecutivo()); ?>&q=<?php print($loNutriciones->getTipoBitacora()['CODIGO']); ?>&r=<?php print($loNutriciones->getIngreso()->nIngreso); ?>",
				data: $("#seguimientoForm").serialize()
			})
			.done(function(response) {
				$('#seguimientoModal').modal('hide');
				$('#tableDetalleBitacora').bootstrapTable('refresh');			
				$('#btnSeguimientoGuardar').removeAttr("disabled");
				fnAlert(response, 'Guardado', 'far fa-check-circle', 'green');
			})
			.fail(function(data) {
				$('#registroAlertaInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
				$('#btnSeguimientoGuardar').removeAttr("disabled");
			});
		}
	} );
	
	$("#seguimientoModal").on("hidden.bs.modal", function () {
		$('#seguimientoForm').find('select').each(function(){
			$('#'+this.name+' option').prop("selected",false);
			$('#'+this.name+' option').removeAttr("selected");
		});	
		
		$('#seguimientoForm').trigger('reset');
		$('#seguimientoForm').find('input,select,textarea').each(function(){
			$('#'+this.name).removeClass( "is-valid" ).removeClass( "is-invalid" );
		});		
		
		$('#nSegumientoConsecutivoBage').empty();
		$('#nSegumientoConsecutivo').val(0);
		$('#cSegumientoObservacion').empty();
		$('#cSegumientoEntidad').removeAttr("disabled","disabled");
		$('#cSegumientoTipo').removeAttr("disabled","disabled");
		$('#cSegumientoInicio').removeAttr("disabled","disabled");		
	});
		
	
	$('#btnSeguimientoGuardar').click(function() {
		$("#seguimientoForm").submit();
	});	
	
	
	// Proveedor
	goValidatorProveedor = $( "#proveedorForm" ).validate( {
		rules: {
			cProveedorCodigo: {
				required: true,
				minlength: 3
			},
			cProveedorNombre: {
				required: true,
				minlength: 3
			},
		},
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			error.addClass( "invalid-tooltip" );

			if ( element.prop( "type" ) === "checkbox" ) {
				error.insertAfter( element.parent( "label" ) );
			} else {
				error.insertAfter( element );
			}
		},
		highlight: function ( element, errorClass, validClass ) {
			$( element ).addClass( "is-invalid" ).removeClass( "is-valid" );
		},
		unhighlight: function (element, errorClass, validClass) {
			$( element ).addClass( "is-valid" ).removeClass( "is-invalid" );
		},
		submitHandler: function () {
			$('#btnProveedorGuardar').attr("disabled","disabled");
			$.ajax({
				type: 'POST',
				url: "vista-nutricion/ajax/registroNutriciones.ajax?proveedor&p=<?php print($loNutriciones->getConsecutivo()); ?>&q=<?php print($loNutriciones->getTipoBitacora()['CODIGO']); ?>&r=<?php print($loNutriciones->getIngreso()->nIngreso); ?>",
				data: $("#proveedorForm").serialize()
			})
			.done(function(response) {
				$('#proveedorModal').modal('hide');	
				$('#cSegumientoProveedor').empty().append('<option></option>');
				$.each(response, function(key, row) {   
					 $('#cSegumientoProveedor')
						 .append($("<option></option>")
									.attr("value", row.CODIGO)
									.text(row.DESCRIPCION+" ("+row.CODIGO+")")); 
				});				
				$('#btnProveedorGuardar').removeAttr("disabled");
			})
			.fail(function(data) {
				/*$('#registroAlertaInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");*/
				$('#btnProveedorGuardar').removeAttr("disabled");
			});
		}
	} );	

	$("#proveedorModal").on("show.bs.modal", function () {
		$('#btnProveedorGuardar').attr("disabled","disabled");
	});
	
	$("#proveedorModal").on("hidden.bs.modal", function () {
		$('#btnProveedorGuardar').attr("disabled","disabled");
		$('#proveedorForm').trigger('reset');
		$('#proveedorForm').find('input,select,textarea').each(function(){
			$('#'+this.name).removeClass( "is-valid" ).removeClass( "is-invalid" );
		});		
		$('#proveedorRegistro').empty().removeClass("alert").removeClass("alert-danger").removeAttr("role","alert");
	});
	
	$('#btnProveedorGuardar').click(function() {
		$("#proveedorForm").submit();
	});	
	
	
	$('#cProveedorCodigo').on("change", function() {
		$.ajax({
			type: 'POST',
			url: "vista-nutricion/ajax/registroNutriciones.ajax?proveedorEspecifico&p=<?php print($loNutriciones->getConsecutivo()); ?>&q=<?php print($loNutriciones->getTipoBitacora()['CODIGO']); ?>&r=<?php print($loNutriciones->getIngreso()->nIngreso); ?>",
			data: {PROVEEDOR: $(this).val()}
		})
		.done(function(response) {
			if(response==''){
				$('#proveedorRegistro').empty().removeClass("alert").removeClass("alert-danger").removeAttr("role","alert");
			}else{
				$('#proveedorRegistro').empty().html(response).addClass("alert").addClass("alert-danger").attr("role","alert");
			}
			$('#btnProveedorGuardar').removeAttr("disabled");
		})
		.fail(function(data) {
			$('#proveedorRegistro').empty().html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al validar la existencia del codigo '+$(this).val()).addClass("alert").addClass("alert-danger").attr("role","alert");
		});			
	});
	
	
	$('#tableDetalleBitacora').on((goMobile.mobile()?'click-row.bs.table':'dbl-click-row.bs.table'), function (row, $element, field) {
		updateSeguimientoForm($('#seguimientoModal'), $('#seguimientoForm'), $element);
	});	
})