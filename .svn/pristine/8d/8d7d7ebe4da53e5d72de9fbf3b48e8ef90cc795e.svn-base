<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
	
	$lnIngreso = intval(isset($_GET['nIngreso'])?$_GET['nIngreso']:'0');
	$lnPrevio = intval(isset($_GET['nPrevio'])?$_GET['nPrevio']:'0');

?>let goMobile = new MobileDetect(window.navigator.userAgent);

function queryParams() {
	var params = {};
	$('#aperturaForm').find('input,select').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	

function limpiar(){
	let laCampos = ['cIngresoFecha', 'cDocumento', 'nDocumento', 'cPacienteNacimiento', 'cEdad', 'cGenero', 'cHabitacion', 'cPaciente', 'cCentroServicio'];
	$.each(laCampos, function(key, value) {  
		$('#'+value).val('');	
	});
}

function guardarApertura(){
	$.ajax({
		type: 'POST',
		url: "vista-apertura-salas/ajax/registroAperturaSalas.ajax?accion=guardar",
		data: queryParams()
	})
	.done(function(response) {
		if(response.ERROR!==undefined){
			if(response.ERROR==false){
				fnInformation(response.MENSAJE, 'Ingreso');
				$('#nIngreso').attr("readonly", "readonly");
				$('#cSala').attr("disabled", "disabled");
				$('#cSalaNumero').attr("disabled", "disabled");
				$('#btnGuardar').attr("disabled", "disabled");
				$('#abiertasArea').empty();
				$('#commandArea').empty().append($("<div></div>").addClass('alert alert-success').html('<i class="far fa-check-circle pr-3"></i>'+response.MENSAJE+'. Haga clic en volver para ir a la lista de aperturas.')); 
				$('#recordArea').empty().html('Ingreso: '+response.INGRESO+", Cirug&iacute;a: "+response.CONSECUTIVO);
			}else{
				fnAlert(response.MENSAJE, 'Ingreso');
			}
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al guardar la apertura de sala');
	});	
}

function isGuardable(){
	if($('#nSalasAbiertas').val()>0){
		fnConfirm('El ingreso '+$('#nIngreso').val()+' tiene '+$('#nSalasAbiertas').val()+' sala(s) abierta(s). ¿Desea crear la apertura y cerrar la(s) anterior(es)?', 'Abiertas', 'fas fa-question-circle', 'orange', 'smalll', guardarApertura);
	}else{
		guardarApertura();
	}
}

function getPacienteAgregar(){
	limpiar();
	
	$.ajax({
		type: 'GET',
		url: "vista-apertura-salas/ajax/registroAperturaSalas.ajax?accion=paciente",
		data: {nIngreso: $('#nIngreso').val()}
	})
	.done(function(response) {
		if(response.TIPO!==undefined){
			if(response.TIPO!=='' && response.NUMERO>0){
				$('#cIngresoFecha').val(response.FECHA);
				$('#cDocumento option[value="'+response.TIPO+'"]').attr("selected", "selected");
				$('#nDocumento').val(response.NUMERO);
				$('#cPacienteNacimiento').val(response.NACIO);
				$('#cEdad').val(response.EDAD);
				$('#cGenero').val(response.GENERO);
				$('#cHabitacion').val(response.HABITACION);
				$('#cPaciente').val(response.NOMBRE);
				
				$('#cSala').removeAttr("disabled", "disabled").trigger("change");
			}else{
				fnAlert('Ingreso no encontrado', 'Ingreso');
			}
		}
		$('#nIngreso').removeAttr("disabled", "disabled");
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener datos del paciente');
	});		
}

function getCentroServiciosAgregar(){
	$('#cCentroServicio').empty();
	
	$.ajax({
		type: 'GET',
		url: "vista-apertura-salas/ajax/registroAperturaSalas.ajax?accion=cse",
		data: {cSala: $('#cSala').val()}
	})
	.done(function(response) {
		
		if(response.NOMBRE!==undefined && response.CODIGO!=='' && response.NOMBRE!==''){
			$('#cCentroServicio').val(response.CODIGO);
			$('#cCentroServicioNombre').html(response.NOMBRE);
		}else{
			$('#cCentroServicioNombre').text('SIN CSE ASOCIADO');
			fnAlert('No existe Centro de Servicios, Falta parametrizaci&oacute;n', 'CSE');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener datos de las salas');
	});		
}

function getSalasAbiertas(){
	$('#salasAbiertas').empty();
	
	$.ajax({
		type: 'GET',
		url: "vista-apertura-salas/ajax/registroAperturaSalas.ajax?accion=salas-abiertas",
		data: {nIngreso: $('#nIngreso').val(), cSala: $('#cSala').val()}
	})
	.done(function(response) {
		var lnSalas = 0;
		$.each(response, function(key, row) {   
			$('#salasAbiertas')
				 .append($("<span></span>")
							.addClass('badge badge-success')
							.text(row.SALA)); 
			lnSalas+=1;
		});	
		
		$('#nSalasAbiertas').val(lnSalas);
		if(lnSalas==0){			
			$('#salasAbiertas').html('<span class="badge badge-secondary">No hay salas abiertas</span>');
		}

	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener datos de las salas abiertas');
	});		
}

function getSalasAgregar(){
	$('#cSalaNumero').empty();
	
	$.ajax({
		type: 'GET',
		url: "vista-apertura-salas/ajax/registroAperturaSalas.ajax?accion=salas",
		data: {nIngreso: $('#nIngreso').val(), cSala: $('#cSala').val()}
	})
	.done(function(response) {
		var lnSalas = 0;
		$.each(response, function(key, row) {   
			$('#cSalaNumero')
				 .append($("<option></option>")
							.attr("value", row.CODIGO)
							.text(row.CODIGO)); 
			lnSalas+=1;
		});	
		$('#cSala').removeAttr("disabled", "disabled");
		$('#cSalaNumero').removeAttr("disabled", "disabled");
		
		if(lnSalas>0){
			$('#btnGuardar').removeAttr("disabled", "disabled");
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		console.log(jqXHR.responseText);
		alert('Se presentó un error al obtener datos de las salas');
	});		
}

$(function() {	
	<?php if($lnPrevio==0) { ?>
	$('#nIngreso').on("change", function() {
		$(this).attr("disabled", "disabled");
		$("#btnGuardar").attr("disabled", "disabled");
		$('#cSala').attr("disabled", "disabled");
		$('#cSalaNumero').val("").attr("disabled", "disabled");		
		getPacienteAgregar();
	});
	
	$('#cSala').on("change", function() {
		$(this).attr("disabled", "disabled");
		$("#cSalaNumero").attr("disabled", "disabled");
		$("#btnGuardar").attr("disabled", "disabled");
		$('#cCentroServicio').val('');	
		$('#cCentroServicioNombre').text('');	
		getSalasAgregar();
		getSalasAbiertas();
		getCentroServiciosAgregar();
	});	
	goValidatorApertura = $( "#aperturaForm" ).validate( {
		rules: {
			nIngreso: {
				required: true,
				digits: true,
				minlength: 1,
				maxlength: 8
			},
			cSala: {
				required: true
			},
			cSalaNumero: {
				required: true
			},
			cCentroServicio: {
				required: true
			},
			nSalasAbiertas: {
				required: true,
				digits: true,
				minlength: 1,
				maxlength: 8
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
			isGuardable();			
		}
	});		
	<?php } else{ ?>
	$('#nIngreso').attr("disabled", "disabled");
	<?php } ?>
	
	<?php if($lnIngreso>0){ ?>$('#nIngreso').val(<?php print($lnIngreso); ?>).trigger("change");<?php }?>
	

	$('#btnGuardar').click(function() {
		$("#aperturaForm").submit();
	});	
		
});