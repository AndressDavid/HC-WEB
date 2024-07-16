<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
?>
$( document ).ready( function () {
	$("#numeroIngreso" ).focus();

	function countUnchecked() {
		var n = $(".form-check-input:checked").length;
		$('#nTiposAlerta').val(n);
	}
	countUnchecked();
	$(".form-check-input:checkbox").click( countUnchecked );

	var loEquipo = new Bloodhound({
		datumTokenizer: Bloodhound.tokenizers.obj.whitespace('text'),
		queryTokenizer: Bloodhound.tokenizers.whitespace,
		remote:   {
			url: 'vista-alerta-temprana/consultas_json?accion=equipo&nombre=%QUERY',
			wildcard: '%QUERY',
			ajax: {
				type: 'POST',
				data: $.param({q: $('#cEquipo').val()})
			}
		}
	});
	loEquipo.initialize();

	$('#cEquipo').tagsinput({
		itemValue: 'USUARIO',
		itemText: 'NOMBRE',
		typeaheadjs: {
			name: 'loEquipo',
			displayKey: 'NOMBRE',
			hint: true,
			highlight: true,
			hint: true,
			source: loEquipo.ttAdapter()
		},
		freeInput: false,
		trimValue: true
	});

	$('#cEquipo').tagsinput('add', { USUARIO: '<?php print($_SESSION[HCW_NAME]->oUsuario->getUsuario()); ?>', NOMBRE: '<?php print($_SESSION[HCW_NAME]->oUsuario->getNombreCompleto()); ?>' });
	$('#cEquipo').tagsinput('refresh');

	$('#numeroIngreso').on('change', function() {
		var lnIngreso = $(this).val();
		var loObjeto = $(this);

		$.ajax({
			type: 'POST',
			url: "vista-alerta-temprana/consultas_json",
			data: {accion:'ingreso',ingreso:lnIngreso},
			dataType: "json"
		})
		.done(function(loIngreso) {
			try {
				if(loIngreso.nIngreso>0){
					if(loIngreso.cEstado.trim()=="2"){
						$('#nIngresoMostrar').html(loIngreso.nIngreso);
						$('#nIngreso').val(loIngreso.nIngreso);
						$('#cNombre').html(loIngreso.cNombre);
						$('#cEdad').html(loIngreso.cEdad);
						$('#cUbicacion').html(loIngreso.cUbicacion);
						$('#cTipoIdMostrar').html(loIngreso.cTipoId);
						$('#cTipoId').val(loIngreso.cTipoId);
						$('#nIdMostrar').html(loIngreso.nId);
						$('#nId').val(loIngreso.nId);
						$('#filtroIngreso').hide("slow");
						$('#registroAlerta').show(200);
						$('#ingresoInfo').html('').removeClass("alert").removeClass("alert-warning").removeClass("alert-info").removeClass("alert-danger").removeAttr("role");

						$("#registroAlertaForm").find('input, select').each(function(){
							$(this).removeAttr('disabled');
						});

						$("#equipo" ).focus();
					}else{
						$('#ingresoInfo').html('<i class="fa fa-exclamation-triangle"></i> NO esta activo el ingreso '+ lnIngreso).addClass("alert").addClass("alert-info").attr("role","alert");
					}
				}else{
					$('#ingresoInfo').html('<i class="fa fa-exclamation-triangle"></i> No se enontro el numero de ingreso '+ lnIngreso).addClass("alert").addClass("alert-warning").attr("role","alert");
				}
			} catch(err) {
				$('#ingresoInfo').html('<i class="fa fa-exclamation-triangle"></i> No sepudo realizar la busqueda '+ lnIngreso).addClass("alert").addClass("alert-danger").attr("role","alert");
			}
		})
		.fail(function(data) {
			$('#ingresoInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al buscar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
		});
	});

	$( "#registroAlertaForm" ).validate( {
		rules: {
			numeroIngreso: {
				required: true,
				digits: true
			},
			cEquipo: {
				required: true,
				minlength: 5,
			},
			cAccion: {
				required: true
			},
			cDescripcion: {
				required: true
			},
		},
		errorElement: "div",
		errorPlacement: function ( error, element ) {
			// Add the `help-block` class to the error element
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
			$('#cEquipo').tagsinput('refresh');
			var lcEquipo = $("#cEquipo").val();
			if($('#nTiposAlerta').val()>0){
				if(Boolean(lcEquipo)){
					$.ajax({
						type: 'POST',
						url: "vista-alerta-temprana/guardarAlerta",
						data: $("#registroAlertaForm").serialize()
					})
					.done(function(response) {
						$('#registroAlertaInfo').html(response);
						$("#modalSignosGuardar").on("hidden.bs.modal", function () {
							$('#nIngresoMostrar').html('');
							$('#numeroIngreso').val('');
							$('#filtroIngreso').show(200);
							$('#registroAlerta').hide("slow");
							$('#registroAlertaInfo').html('').removeClass("alert").removeClass("alert-warning").removeClass("alert-info").removeClass("alert-danger").removeAttr("role");
							$('#cEquipo').tagsinput('removeAll');
							$("#registroAlertaForm")[0].reset();
							$('#numeroIngreso').focus();
							window.location.href = "modulo-alerta-temprana&p=registroAlerta";
						});
						$('#modalSignosGuardar').modal('show');
					})
					.fail(function(data) {
						$('#registroAlertaInfo').html('<i class="fa fa-exclamation-triangle"></i> Se presento un error al guardar el ingreso').addClass("alert").addClass("alert-danger").attr("role","alert");
					});
				}else{
					$('#registroAlertaInfo').html('<i class="fa fa-exclamation-triangle"></i> No hay <b>Integrantes del equipo de respuesta r&aacute;pida</b>').addClass("alert").addClass("alert-info").attr("role","alert");
				}
			}else{
				$('#registroAlertaInfo').html('<i class="fa fa-exclamation-triangle"></i> No hay selección para el <b>Tipo de alerta a responder</b>').addClass("alert").addClass("alert-info").attr("role","alert");
			}
		}
	} );

	$('.ingresoHabitacion').on('click', function() {
		$(location).attr('href','modulo-alerta-temprana&p=registroAlerta&ingreso='+$(this).data("ingreso")+'&seccion='+$(this).data("seccion"));
	});

} );