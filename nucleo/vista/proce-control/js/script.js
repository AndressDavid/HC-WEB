var start = {
	laDatospdf: [],
    inicializar: function()
	{	
		asistenciaId ="";
        url="vista-comun/ajax/diagnostico?lcTipoDiagnostico=consultarDiagnosticos&lcDatosPacientes=";
        this.consultarDiagnosticoProcedimientoConsulta("txtCodigoCie","PROCEDIMIENTO","loCodigoAsigna","loDescripcionAsigna");
        this.consultarDiagnosticoProcedimientoConsulta("txtCodigoRela","PROCEDIMIENTO","loCodigoAsignRela","loDescripcionAsignaRela");
        this.cargarClase("claseDiagnostico", "clase"),
		this.bloquearPDF(),
		$('#saveInformation').on('click', this.PrevioGuardar),
		$('#btnVolver').on('click', this.volver_btn),
		$('#btnLibroHC').on('click', abrirLibro),
		start.crearArraypdf(),
		$('#btnVerPdfHC').on('click', function(){ vistaPreviaPdf({'datos':JSON.stringify(start.laDatospdf)}, null, 'TERAPIA DE REHABILITACIÓN CARDIOVASCULAR', 'TRECAR') });
		$('#btnVistaPrevia').on('click', function(){ oModalVistaPrevia.mostrar(start.laDatospdf[0], 'TERAPIA DE REHABILITACIÓN CARDIOVASCULAR', 'TRECAR'); });
		$("#resolverReha").validate({
			rules: {
				txtCodigoCie: "required",
				loDescripcionAsigna: "required",
				claseDiagnostico: "required",
				selFinalidad: "required",
				AsisteOpc: "required",
				loDescripcionAsigna: "required"
			},
			errorElement: "div",
			errorPlacement: function(error, element) {
				error.addClass("invalid-tooltip");
				if ( element.prop("type") === "checkbox" ) {
					error.insertAfter(element.parent("label") );
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).addClass("is-valid").removeClass("is-invalid");
			},
		});
		$('footer').ready(function(){
			$("footer").css( "margin-bottom", "55px");
		})
	},
	bloquearPDF: function(){
		if(oEst == 8 || oEst== 0){
			$("#btnVerPdfHC").prop('disabled', true);
			$("#btnVistaPrevia").prop('disabled', true);
		}
	},

	crearArraypdf: function (){
		start.laDatospdf ={
			0:{
				'nIngreso': datosIngre['NINORD'],
				'nConsecCita': datosIngre['CCIORD'],  
				'cCUP': 933601,
				'cCodVia': aDatosIngreso['cCodVia'],
				'cSecHab': aDatosIngreso['cSeccion']+' - '+aDatosIngreso['cHabita'],
				'cTipDocPac': aDatosIngreso['cTipId'],
				'nNumDocPac' : aDatosIngreso['nNumId'],
				'nConsecEvol' : datosIngre["CCIORD"],
				'tFechaHora' : aDatosIngreso['nIngresoFecha'],
				'cTipoDocum' : '', 					// const
				'cTipoProgr' : 'RIA022',
				'nConsecCons' : '0', 						// const
				'nConsecDoc' : datosIngre["CCIORD"]
			}
		}
	},

    consultarDiagnosticoProcedimientoConsulta: function(buscar, tipo,codigo,descrip) {
		var loObjeto = '#'+buscar;

		laDatosPaciente = {
			fecha: 0,
			genero: aDatosIngreso.cSexo,
			edad: aDatosIngreso.aEdad,
			tipoconsulta: tipo
		}
		
		Url=url+""+JSON.stringify(laDatosPaciente)+"";
		$(loObjeto).autoComplete({
			preventEnter: true,
			resolverSettings: {
				url:Url ,
				queryKey: 'nombre',
				requestThrottling: 500,
				fail: function (e) {},
			},
	
			formatResult: function (taItem) {
				laItem = { value: '', text: '', html: ''};
				if(taItem.CODIGO!==undefined && taItem.DESCRIPCION!==undefined){
					if(taItem.DESCRIPCION.length>0 && taItem.CODIGO.length>0){
						laItem = {
							value: taItem.CODIGO,
							text: taItem.DESCRIPCION + ' - '+ taItem.CODIGO,
							html: taItem.DESCRIPCION + ' - '+ taItem.CODIGO
						};
					}
				}
				return laItem;
			},
			noResultsText: 'No hay coincidencias',
		})
		.autoComplete('set',
			{CODIGO:'', DESCRIPCION:'', ESPECIALIDAD:''}
		).on('autocomplete.select', function(evt, item) {
			$("#"+codigo).val(item.CODIGO);
			$("#"+descrip).val(item.DESCRIPCION);
			$(loObjeto).val('');
			$(loObjeto).removeClass("is-valid");
		}).on('autocomplete.freevalue', function(evt, value) {
			$(loObjeto).val('');
		});
	},
    cargarClase:function(selectInput, ClaseDiag){
        $.ajax({
            type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: ClaseDiag}
        }).done(function (data){
			$("#"+selectInput).append("<option value=''></option>");
            $.each(data.TIPOS, function(Index, Item) {
				if(clase==Index){
					$("#"+selectInput).append("<option selected value="+Index+">"+Item+"</option>");
				}else{
					$("#"+selectInput).append("<option value="+Index+">"+Item+"</option>");
				}
            });
        }).fail(function(errorThrown){
            console.log(errorThrown);
        });
    },
	cargarSelectAsiste:function(selectInput, idAsistencia =0){
		band = true;
        $.each(datosAsiste, function(Index, Item) {

			if(band){
				$("#"+selectInput).append("<option value=''></option>");
				band=false;
			}

			if( Item.CL3TMA ==idAsistencia){
				$("#"+selectInput).append("<option selected value="+Item.CL3TMA+">"+Item.DE2TMA+"</option>");
			}else{
				$("#"+selectInput).append("<option value="+Item.CL3TMA+">"+Item.DE2TMA+"</option>");
			}

           
        });

	},
	PrevioGuardar:function(){
		if(validarFormularios()){

			lsTexto='Esta seguro de guardar los cambios';

			if($("#AsisteOpc").val() == '01'){
				lsTexto= 'Se marco la opción de asistencia "Paciente asiste a sesión de terapia de rehabilitación cardiopulmonar". Si continua NO podrá volver a guardar cambios';
			}

			fnConfirm(
				lsTexto,
				'¿Desea Guardar el procedimiento?', false, false, false,
				{
					text: 'Si',
					action: function(){
						guardarDatos();
					}
				},
				{ text: 'No'
				}
			);
		}
	},
	volver_btn: function (){
		fnConfirm(
			'Se perderá lo que ha escrito. ¿Desea regresar?',
			'Solicitud de confirmación', false, false, false,
			{
				text: 'Si',
					action: function(){
						window.location.href='\modulo-historiaclinica&cp=proest';
				}
			},
			{ text: 'No',
					action: function(){
					$('#txtRespuestaInterconsulta').focus();
				}
			}
		);
	}
}

function guardarDatos(){
	$("#resolverReha").attr("disabled", true);
	const data={
		ResultExa: $("#ResultExa").val(),
		loCodigoAsigna: $("#loCodigoAsigna").val(),
		claseDiagnostico: $("#claseDiagnostico").val(),
		loCodigoAsignRela: $("#loCodigoAsignRela").val(),
		selFinalidad: $("#selFinalidad").val(),
		AsisteOpc:$("#AsisteOpc").val(),
		DataClient: aDatosIngreso,
		codCita : datosIngre["CCIORD"],
		HOCORD : datosIngre["HOCORD"],
		FRLORD: datosIngre["FRLORD"],
		DESESP: datosIngre["DESESP"]
	}

	$.ajax({
		type:"POST",
		url:"vista-proce-control/controller/saveProcedimientos",
		data: data
	}).done(function(data){
		console.log(data);
		var jsond = JSON.parse(data);
		if(!jsond.success){
			console.log(jsond);
			fnAlert(jsond.message.body, jsond.message.title);
		}
		else{
			
			$("#loCodigoAsigna").prop('disabled', true);
			$("#claseDiagnostico").prop('disabled', true);
			$("#selFinalidad").prop('disabled', true);
			$("#AsisteOpc").prop('disabled', true);
			$("#ResultExa").prop('disabled', true);
			$("#txtCodigoCie").prop('disabled', true);
			$("#txtCodigoRela").prop('disabled', true);
			$("#btnVerPdfHC").prop('disabled', false);
			$("#btnVistaPrevia").prop('disabled', false);
			$("#saveInformation").prop('disabled', true);

			fnConfirm(
				jsond.message.title,
				jsond.message.body, 'exclamation-triangle', 'blue', 'small',
				{
					text: 'Cerrar ventana',
					action: function(){
						window.close();
					}
				},
				{ text: 'No Cerrar',
					action: function(){}
				}
			);
		}
	}).fail({

	});
}

function validarFormularios(){
	$("#txtCodigoCie").addClass("is-valid").removeClass("is-invalid");
	if( $('#loCodigoAsigna').val()==''){
		$("#txtCodigoCie").addClass("is-invalid").removeClass("is-valid");
		fnAlert("El diagnostico principal es obligatorio para registrar la rehabilitación.", "No asigno un diagnostico principal");
		ubicarObjeto('#resolverReha', '#txtCodigoCie');

		return false;
	}

	if(!$('#claseDiagnostico').valid()){
		ubicarObjeto('#resolverReha', '#claseDiagnostico');
		return false;
	}


	if(!$('#loCodigoAsigna').valid()){
		ubicarObjeto('#resolverReha', '#loCodigoAsigna');
		return false;
	}

	if(!$('#selFinalidad').valid()){
		ubicarObjeto('#resolverReha', '#selFinalidad');
		return false;
	}

	if(!$('#AsisteOpc').valid()){
		ubicarObjeto('#resolverReha', '#AsisteOpc');
		return false;
	}


	return true;
}

function ubicarObjeto(toForma, tcObjeto, tcTab){
	tcObjeto = typeof tcObjeto === 'string'? tcObjeto: false;
	var loForm = $(toForma);
	if (tcObjeto===false) {
		//setTimeout(function() { // necesario si los tab-pane tienen fade
			var formerrorList = loForm.data('validator').errorList,
				lcObjeto = formerrorList[0].element.id;
			$('#'+lcObjeto).focus();
		//}, (300));
	} else {
		tcTab = typeof tcTab === 'string'? tcTab: false;
		if (!tcTab===false){
			$(tcTab).tab('show');
			setTimeout(function() {
				$(tcObjeto).focus();
			}, (300));
		}else{
			$(tcObjeto).focus();
		}
	}
}

function dasactivarInputs(){

	$("#loCodigoAsigna").prop('disabled', true);
	$("#claseDiagnostico").prop('disabled', true);
	$("#selFinalidad").prop('disabled', true);
	$("#AsisteOpc").prop('disabled', true);
	$("#ResultExa").prop('disabled', true);
	$("#txtCodigoCie").prop('disabled', true);
	$("#txtCodigoRela").prop('disabled', true);
	
}