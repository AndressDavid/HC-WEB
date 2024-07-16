var oModalTrasladoPacientes = {
	gcTitulo: 'Traslado pacientes',
	gcUrlAjax: 'vista-comun/ajax/modalTrasladosPacientes.php',
	gnNroIngreso:0, gcListaUsuarios:'', gcHabitacion:'', gnCaracteresjustificacion:0, gnValorNews:0, gnMaximoValorNews:0,
	gcDatosAnteriores:'',
	
	inicializar: function()
	{
		this.iniciacampos();
		this.listadoTiposMedicos();
		
		$('#edtRegistrarTraslado').on('keyup',function(){
			let lcJustificacionTraslado = $("#edtRegistrarTraslado").val().trim();
			oModalTrasladoPacientes.cantidadTextoJustificacionTr(lcJustificacionTraslado);
		});
		
		$('#btnGuardaTrasladosPacientes').on('click', this.validarGuardar);
		$('#btnHistoricoTraslados').on('click', this.verhistoricos);
		$('#btnCancelarTrasladosPacientes').on('click', this.ocultar);
	},
	
	cantidadTextoJustificacionTr: function(tcJustificacion)
	{
		var lnCaracteres = 0;
		if (tcJustificacion==''){
			lcTextoCaracteres = 'Máximo caracteres: ' + oModalTrasladoPacientes.gnCaracteresjustificacion;
			loCantidadJustificacion = $('#lblCaracteresTraslado');
			loCantidadJustificacion.text(lcTextoCaracteres);
		}else{	
			lnCaracteres = $("#edtRegistrarTraslado").val().length;
			loCantidadJustificacion = $('#lblCaracteresTraslado');
			lcTextoCaracteres = 'Máximo caracteres: ' + lnCaracteres+' - '+oModalTrasladoPacientes.gnCaracteresjustificacion;
			loCantidadJustificacion.text(lcTextoCaracteres);
		}
		$('#lblCaracteresTraslado').addClass("text-primary");
	},
	
	listadoTiposMedicos: function()
	{
		oModalTrasladoPacientes.gcListaUsuarios='';
		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'consultarTiposMedicos'},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					oModalTrasladoPacientes.gcListaUsuarios=loDatos.datos;
					oModalTrasladoPacientes.iniciarListados();
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta listado tipos médicos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar listado tipos médicos.');
		});
	},
	
	iniciarListados: function()
	{
		lcTipoUsuario=String(aAuditoria.cTipopUsuario);
		laUsuariosPermitos=oModalTrasladoPacientes.gcListaUsuarios.trim().split(',');

		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'consultarListados', lcTiposMedicos:oModalTrasladoPacientes.gcListaUsuarios},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					oModalTrasladoPacientes.gnCaracteresjustificacion=loDatos.caracteresjustificacion;
					lcTextoCaracteres='Máximo caracteres: ' + loDatos.caracteresjustificacion;
					$('#lblCaracteresTraslado').text(lcTextoCaracteres);
					oModalTrasladoPacientes.gnMaximoValorNews=loDatos.valormaximosignonews;
					loSelect = $("#selEspecialidadTrasladarTP");
					loSelect.empty();
					loSelect.append('<option value=""></option>');
					$.each(loDatos.especialidades, function( lcKey, loTipo ) {
						if (loTipo.INTERCONSULTA=='I'){
							var lcOption = '<option value="' + loTipo.CODESP + '">' + loTipo.DESESP + '</option>';
							loSelect.append(lcOption);
						}	
					});
					
					loSelect = $("#selMedicoTrasladaTP");
					loSelect.empty();
					loSelect.append('<option value=""></option>');
					$.each(loDatos.medicos, function( lcKey, loTipo ) {
						var lcOption = '<option value="' + loTipo.REGISTRO + '">' + loTipo.MEDICO + '</option>';
						loSelect.append(lcOption);
					});
					
					loSelect = $("#selEscalaDolorTr");
					loSelect.empty();
					loSelect.append('<option value=""></option>');
					$.each(loDatos.escaladolor['1'], function( lcKey, loTipo ) {
						var lcOption = '<option value="' + lcKey + '">' + loTipo + '</option>';
						loSelect.append(lcOption);
					});
					$("#edtRegistrarTraslado").attr("maxlength", oModalTrasladoPacientes.gnCaracteresjustificacion);
					
					if ($.inArray(lcTipoUsuario, laUsuariosPermitos)<0){
						oModalTrasladoPacientes.inactivaCampos();
					}	
					
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta consultar iniciar Listados.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar consultar iniciar Listados.');
		});
	},
	
	iniciarAreasTraslado: function(tnIngreso)
	{
		lcVia=goFila.CODVIA;
		lcSeccionHabitacion=goFila.SECCION;
		if (goFila.TIPO_HABITACION==='URGENCI' || goFila.TIPO_HABITACION==='' || lcVia==='01'){
			lcTipoEvolucion='URGENCI';
		}else{
			lcTipoEvolucion=goFila.TIPO_HABITACION==='UNIDAD'?'EVUNID':'EVPISO';
		}

		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'consultarAreastrasladar', lnIngreso:tnIngreso, lcViaIngreso:lcVia, lcSeccion:lcSeccionHabitacion, 
					lcModulo:lcTipoEvolucion},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					loSelect = $("#selAreaTrasladarTP");
					loSelect.empty();
					loSelect.append('<option value=""></option>');
					$.each(loDatos.datos, function( lcKey, loTipo ) {
						let lcOption = '<option value="' + lcKey + '">' + loTipo.desc + '</option>';
						loSelect.append(lcOption);
					});
					
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta consultar iniciar Listados.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar consultar iniciar Listadoss.');
		});
	},
	
	validarGuardar: function()
	{
		if ($("#selAreaTrasladarTP").val()==''){
			$("#selAreaTrasladarTP").removeClass("is-valid").addClass("is-invalid");
			$('#edtRegistrarTraslado').focus();
			fnAlert("Área a trasladar obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#selAreaTrasladarTP").removeClass("is-invalid");
		}

		if ($("#selEspecialidadTrasladarTP").val()==''){
			$("#selEspecialidadTrasladarTP").removeClass("is-valid").addClass("is-invalid");
			$('#selEspecialidadTrasladarTP').focus();
			fnAlert("Especialidad a trasladar obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#selEspecialidadTrasladarTP").removeClass("is-invalid");
		}
		
		if ($("#selMedicoTrasladaTP").val()==''){
			$("#selMedicoTrasladaTP").removeClass("is-valid").addClass("is-invalid");
			$('#selMedicoTrasladaTP').focus();
			fnAlert("Médico recibe obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#selMedicoTrasladaTP").removeClass("is-invalid");
		}
		
		if ($("#selSeInformaFamiliarTP").val()==''){
			$("#selSeInformaFamiliarTP").removeClass("is-valid").addClass("is-invalid");
			$('#selSeInformaFamiliarTP').focus();
			fnAlert("Se informa a farmiliar de traslado obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#selSeInformaFamiliarTP").removeClass("is-invalid");
		}
		
		if ($("#SelSetrasladaFamiliarTP").val()==''){
			$("#SelSetrasladaFamiliarTP").removeClass("is-valid").addClass("is-invalid");
			$('#SelSetrasladaFamiliarTP').focus();
			fnAlert("Se traslada en compañia de familiar obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#SelSetrasladaFamiliarTP").removeClass("is-invalid");
		}
		
		if ($("#selEscalaDolorTr").val()==''){
			$("#selEscalaDolorTr").removeClass("is-valid").addClass("is-invalid");
			$('#selEscalaDolorTr').focus();
			fnAlert("Escala dolor obligatoria, revise por favor.",oModalTrasladoPacientes.gcTitulo);
			return false;
		}else{
			$("#selEscalaDolorTr").removeClass("is-invalid");
		}
		
		if ($("#edtRegistrarTraslado").val()==''){
			$("#edtRegistrarTraslado").removeClass("is-valid").addClass("is-invalid");
			$('#edtRegistrarTraslado').focus();
			fnAlert('Debe registrar información de traslado, revise por favor.');
			return false;
		}else{
			$("#edtRegistrarTraslado").removeClass("is-invalid");
		}
		$('#btnGuardaTrasladosPacientes').attr("disabled", true);
		oModalTrasladoPacientes.calcularNews();
	},	

	calcularNews: function()
	{
		oModalTrasladoPacientes.gnValorNews=0;
		let lcDatosSignos=OrganizarSerializeArray($('#FormTrasladosPacientes').serializeArray());
		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'consultaNews', lcSignosNews: lcDatosSignos, lnIngreso: oModalTrasladoPacientes.gnNroIngreso},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.datos>oModalTrasladoPacientes.gnMaximoValorNews){
					oModalTrasladoPacientes.gnValorNews=loDatos.datos;
					lcTextoNews='Signos news con valor de ' + loDatos.datos + ', desea realizar el traslado?';
					fnConfirm(lcTextoNews, oModalTrasladoPacientes.gcTitulo, false, 'orange', 'medium',
						{
							text: 'Si',
							action: function(){
								oModalTrasladoPacientes.guardarTraslado();
							}
						},

						{ text: 'No',
							action: function(){
								$('#btnGuardaTrasladosPacientes').attr("disabled", false);
							}
						}
					);
				}else{
					oModalTrasladoPacientes.guardarTraslado();
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta calcular News.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar calcular News.');
		});
	},
	
	obtenerDatos: function()
	{
		let laDatos = {
			'ingreso': oModalTrasladoPacientes.gnNroIngreso,
			'habitacion': oModalTrasladoPacientes.gcHabitacion,
			'valornews': oModalTrasladoPacientes.gnValorNews,
		};
		laDatos['datos'] = OrganizarSerializeArray($('#FormTrasladosPacientes').serializeArray());
		return laDatos;
	},
	
	mostrar: function(tnIngreso,tcNombrePaciente,tcHabitacion)
	{
		oModalTrasladoPacientes.iniciacampos('');
		$('#btnGuardaTrasladosPacientes').attr("disabled", false);
		oModalTrasladoPacientes.iniciarAreasTraslado(tnIngreso);
		oModalTrasladoPacientes.gnNroIngreso=tnIngreso;
		oModalTrasladoPacientes.gcHabitacion=tcHabitacion;
		$("#divTrasladosPacientes").modal('show');
		$('#txtDatosIngreso').html('Número ingreso: <span class="badge badge-success">'+tnIngreso+'</span>  -  ' +
							'Paciente: <span class="badge badge-success">'+tcNombrePaciente+'</span>  -  ' +
							'Habitación: <span class="badge badge-success">'+tcHabitacion+'</span>'
						);
		let campoFocus = document.getElementById("selAreaTrasladarTP");
		campoFocus.focus();
	},
		
	consultarRegistros: function(tnIngreso,tcNombrePaciente,tcHabitacion)
	{
		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'consultartraslados', lnIngreso: tnIngreso},
			dataType: "json"
		})
		.done(function(loDatos) {
			try {
				if (loDatos.error == '') {
					oModalTrasladoPacientes.gcDatosAnteriores=loDatos.datos;
					oModalTrasladoPacientes.mostrar(tnIngreso,tcNombrePaciente,tcHabitacion);
				} else {
					fnAlert(loDatos.error);
				}
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la consulta consultar registros traslados.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error al consultar consultar registros traslados.');
		});
	},
	
	guardarTraslado: function()
	{
		let loData = JSON.stringify(oModalTrasladoPacientes.obtenerDatos());
		$.ajax({
			type: "POST",
			url: oModalTrasladoPacientes.gcUrlAjax,
			data: {accion: 'Verificar', datos: loData},
			dataType: "json"
		})
		.done(function(toDatos) {
			var lcError=(typeof toDatos['error']=='string')?toDatos['error'].trim():'';
			if(lcError==''){
				try {
					if(toDatos['Valido']){
						oModalTrasladoPacientes.ocultar();
						oModalTrasladoPacientes.iniciacampos('G');
					}else {
						$('#btnGuardaTrasladosPacientes').attr("disabled", false);
						fnAlert(toDatos['Mensaje'], 'TRASLADO PACIENTES', false, false, 'medium');
					}
				} catch(err) {
					console.log(err);
					fnAlert('No se pudo realizar la busqueda para guardar traslado.');
				}
			}else {
				if((typeof toDatos['error_sesion']!=='undefined')?toDatos['error_sesion']:false){
					modalSesionHcWeb();
				} else {
					fnAlert(lcError,'TRASLADO PACIENTES');
				}
				$('#btnGuardaTrasladosPacientes').attr("disabled", false);
			}	
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert("Se presentó un error al buscar guardar traslado.");
		});
	},

	verhistoricos: function()
	{
		let lcMsgHtml = [
			'<div class="container-fluid">',
				'<div class="form-row">',
					'<div class="col-12">',
						'<textarea rows="20" type="text" class="form-control" id="edtHistorico" name="RegistrosTraslado" disabled>'+oModalTrasladoPacientes.gcDatosAnteriores+'</textarea>',
					'</div>',
				'</div>',
		].join('');
		lcMsgHtml += '</div>';
		fnAlert(lcMsgHtml, 'Registros anteriores', false, 'blue', 'xl');		
	},
	
	ocultar: function()
	{
		$("#divTrasladosPacientes").modal('hide');
	},
	
	iniciacampos: function(tcTipo)
	{
		oModalTrasladoPacientes.cantidadTextoJustificacionTr('');
		$('#edtRegistrarTraslado,#selAreaTrasladarTP,#selEspecialidadTrasladarTP,#selMedicoTrasladaTP,#selEscalaDolorTr').val('');
		$('#selSeInformaFamiliarTP,#SelSetrasladaFamiliarTP,#fr,#so2,#t,#tas,#tad,#fc,#nc,#o2sp').val('');
		$('#selAreaTrasladarTP,#selEspecialidadTrasladarTP,#selMedicoTrasladaTP,#edtRegistrarTraslado,#selEscalaDolorTr').removeClass("is-valid");
		$('#selSeInformaFamiliarTP,#SelSetrasladaFamiliarTP,#fr,#so2,#t,#tas,#tad,#fc,#nc,#o2sp').removeClass("is-valid");
		oModalTrasladoPacientes.gnNroIngreso=0;	
		
		if (tcTipo=='G'){
			fnInformation('Registro traslado guardado.', 'TRASLADO PACIENTES', false, false, 'medium');		
		}
	},
	
	inactivaCampos: function()
	{
		$('#edtRegistrarTraslado,#selAreaTrasladarTP,#selEspecialidadTrasladarTP,#selMedicoTrasladaTP').attr("disabled",true);
		$('#selSeInformaFamiliarTP,#SelSetrasladaFamiliarTP,#fr,#so2,#t,#tas,#tad,#fc,#nc,#o2sp,#selEscalaDolorTr').attr("disabled",true);
		$("#btnGuardaTrasladosPacientes").css("visibility","hidden");
		$("#btnGuardaTrasladosPacientes").attr("disabled",true);
	}	
}