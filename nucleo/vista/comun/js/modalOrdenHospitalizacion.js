var oModalOrdenHospital = {
	lcTitulo : 'Orden Hospitalización',
	fnEjecutar: false,
	goDataRecibido: {},
	gcUrlajaxOH: 'vista-comun/ajax/modalOrdenHospitalizacion.php',

	inicializar: function()
	{
		this.cargarListadosOrden('selEspecialidadOrden','cargarEspecialidadesOrdenHos','Especialidades orden de hospitalización');
		this.cargarListadosOrden('selAreaTrasladar','cargarAreasOrdenHos','Área orden de hospitalización');

		$('#selMedicoOrden').attr("disabled",true);
		$('#selUbicacionTrasladar').attr("disabled",true);

		$('#selEspecialidadOrden').on('change',function() {
			$lcEspecialidadOrden = $("#selEspecialidadOrden").val();
			oModalOrdenHospital.cargarListadosOrden('selMedicoOrden','cargarMedicosOrdenHos','Médicos orden de hospitalización',$lcEspecialidadOrden);
			$('#selMedicoOrden').attr("disabled",false);
		});

		$('#selAreaTrasladar').on('change',function() {
			$lcUbicacionTrasladar = $("#selAreaTrasladar").val();
			$("#selUbicacionTrasladar").val();
			$("#selUbicacionTrasladar").append('<option value=""></option>');
			$("#selUbicacionTrasladar").empty();
			
			if ($lcUbicacionTrasladar!=''){
				$lcAreaTrasladar = $("#selAreaTrasladar option[value="+$lcUbicacionTrasladar+"]").text();
				oModalOrdenHospital.cargarListadosOrden('selUbicacionTrasladar','cargarUbicacionOrdenHos','Ubicación orden de hospitalización',$lcAreaTrasladar);
			}	
			$('#selUbicacionTrasladar').attr("disabled",false);
		});
		$('#btnGuardaOrdenHos').on('click', this.validarOrdenHospitalizacion);
		$('#btnCancelarOrdenHos').on('click', this.cancelarOrdenHospitalizacion);
	},
	
	cargarListadosOrden: function (id, lcTipo, mensaje, lcCodigoEnvia) {
		var loSelect = $('#'+id);
		loSelect.empty();
		$.ajax({
			type: "POST",
			url: oModalOrdenHospital.gcUrlajaxOH,
			data: {lcListadosOrdenHosp: lcTipo, lcCodigoEnviar: lcCodigoEnvia},
			dataType: "json",
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					loSelect.append('<option value=""></option>');
					if (lcTipo=='cargarEspecialidadesOrdenHos'){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>');
						});
						var lcData = oModalOrdenHospital.goDataRecibido.ESPEC? oModalOrdenHospital.goDataRecibido.ESPEC:'';
						if(lcData != ''){
							oModalOrdenHospital.cargarListadosOrden('selMedicoOrden','cargarMedicosOrdenHos','Médicos orden de hospitalización',lcData);
							oModalOrdenHospital.cargarListadosOrden('selAreaTrasladar','cargarAreasOrdenHos','Área orden de hospitalización');
							$("#selEspecialidadOrden").val(lcData);
							oModalOrdenHospital.goDataRecibido.ESPEC = '';
						}
					}
					
					if (lcTipo=='cargarMedicosOrdenHos' || lcTipo=='cargarUbicacionOrdenHos'){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							loSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
						});

						if (lcTipo=='cargarMedicosOrdenHos'){
							var lcData = oModalOrdenHospital.goDataRecibido.REGISTRO? oModalOrdenHospital.goDataRecibido.REGISTRO:'';
							if(lcData != ''){
								$("#selMedicoOrden").val(lcData);
								oModalOrdenHospital.goDataRecibido.REGISTRO = '';
							}
						}

						if (lcTipo=='cargarUbicacionOrdenHos'){
							var lcData = oModalOrdenHospital.goDataRecibido.UBICA? oModalOrdenHospital.goDataRecibido.UBICA.trim():'';
							
							if(lcData != ''){
								$("#selUbicacionTrasladar").val(lcData);
								oModalOrdenHospital.goDataRecibido.UBICA = '';
							}
						}
					}
					
					if (lcTipo=='cargarAreasOrdenHos'){
						$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
							laLlave = lcKey.trim().split(' ');;
							lcCodigo = laLlave[0].trim();
							loSelect.append('<option value="' + lcCodigo + '">' + loTipo + '</option>');
						});

						var lcData = oModalOrdenHospital.goDataRecibido.AREA?oModalOrdenHospital.goDataRecibido.AREA.trim():'';
						if(lcData != ''){
							oModalOrdenHospital.cargarListadosOrden('selUbicacionTrasladar','cargarUbicacionOrdenHos','Ubicación orden de hospitalización',lcData);
							$("#selAreaTrasladar").val(lcData);
							$("#txtJustificacionordenHos").val(oModalOrdenHospital.goDataRecibido.OBSERVA);
							oModalOrdenHospital.goDataRecibido.AREA = '';
						}
					}
				} else {
					alert(loTipos.error + ' ', "warning");
				}
			} catch(err) {
				alert('No se pudo realizar la busqueda de ' + mensaje +'.', "danger");
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			alert('Se presentó un error al buscar ' + mensaje +'.', "danger");
		});
		return this;
	},
	
	ordenhospitalizacion: function () {
		fnConfirm('Doctor por favor recuerde que es obligatorio comentar el caso con el médico tratante antes de asignarlo, desea continuar?', oModalOrdenHospital.lcTitulo, false, false, 'large',
				{
					text: 'Aceptar',
					action: function(){
						oModalOrdenHospital.mostrar();
					}
				},

				{ text: 'Cancelar',
					action: function(){
						$('#selConductaSeguir').val('');
					}
				}
			);
	},
	
	mostrar: function(tfEjecutar)
	{
		$("#divOrdenHospitaliza").modal('show');
		oModalOrdenHospital.fnEjecutar = tfEjecutar;
	},
	
	ocultar: function()
	{
		$("#divOrdenHospitaliza").modal('hide');
		
		if (typeof oModalOrdenHospital.fnEjecutar==='function'){
			oModalOrdenHospital.fnEjecutar();
		}
	},
	
	inicializaOrdenHospitalizacion: function () {
		$("#selEspecialidadOrden").val('');
		$("#selMedicoOrden").val('');
		$("#selAreaTrasladar").val('');
		$("#selUbicacionTrasladar").val('');
		$('#selMedicoOrden').attr("disabled",true);
		$('#selUbicacionTrasladar').attr("disabled",true);
		$("#txtJustificacionordenHos").val('');
		oModalOrdenHospital.goDataRecibido = {};
	},
	
	validarOrdenHospitalizacion: function () {
		var lcEspecialidad = ($("#selEspecialidadOrden").val()).trim();
		var lcMedicotratante = ($("#selMedicoOrden").val()).trim();
		var lcAreaTrasladar = ($("#selAreaTrasladar").val()).trim();
		var lcUbicacionTrasladar = ($("#selUbicacionTrasladar").val()).trim();

		if (lcEspecialidad==''){
			$('#selEspecialidadOrden').focus();
			fnAlert('Especilidad tratante obligatoria, revise por favor.');
			return false;
		}

		if (lcMedicotratante==''){
			$('#selMedicoOrden').focus();
			fnAlert('Médico tratante obligatorio, revise por favor.');
			return false;
		}

		if (lcAreaTrasladar==''){
			$('#selAreaTrasladar').focus();
			fnAlert('Área a trasladar obligatorio, revise por favor.');
			return false;
		}

		if (lcUbicacionTrasladar==''){
			$('#selUbicacionTrasladar').focus();
			fnAlert('úbicacion trasladar obligatorio, revise por favor.');
			return false;
		}
		$("#divOrdenHospitaliza").modal("hide");
		$("#selConductaSeguir").focus();
	},
	
	cancelarOrdenHospitalizacion: function () {
		fnConfirm('Desea cancelar la orden de hospitalización?', oModalOrdenHospital.lcTitulo, false, false, false,
				{
					text: 'Si',
					action: function(){
						oModalOrdenHospital.inicializaOrdenHospitalizacion();
						$('#selConductaSeguir').val('');
					}
				},

				{ text: 'No',
					action: function(){
						$('#divOrdenHospitaliza').modal('show');
					}
				}
			);
	},

	verificarOrdenH: function(tnIngreso){
		$.ajax({
			type: "POST",
			url: oModalOrdenHospital.gcUrlajaxOH,
			data: {lcListadosOrdenHosp: 'verificarOrdenH', Ingreso: tnIngreso},
			dataType: "json"
		})
		.done(function(loDatos){
			try{
				if(loDatos.error == ''){
					if(loDatos.TIPOS.EXISTE==true){
						oModalOrdenHospital.goDataRecibido = loDatos.TIPOS;
						oModalOrdenHospital.goDataRecibido.OBSERVA = '';
						oModalOrdenHospital.cargarDatos();
						fnConfirm('Paciente con orden de Hospitalización, el médico trantante es ' + loDatos.TIPOS.NOMMEDCRE + ' ' + loDatos.TIPOS.NNOMEDCRE + ' - ' + loDatos.TIPOS.NOMESPEC + ' desea modificar la información ?', oModalOrdenHospital.lcTitulo, false, false, false,
							{
								text: 'Si',
								action: function(){
									oModalOrdenHospital.mostrar();
								}
							},
							{
								text: 'No',
							}
						)
					}else{
						if (typeof oAval === 'object'){
							oModalOrdenHospital.cargarDatosAval();
						}else{
							oModalOrdenHospital.ordenhospitalizacion();
						}
						
					}
				}else{
					fnAlert(loDatos.error)
				}
			}catch(err){
				fnAlert('No se pudo realizar la verificación de orden de Hospitalización.')
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presentó un error en la verificación de orden de Hospitalización.');
		});
	},

 	cargarDatos: function () {
		oModalOrdenHospital.cargarListadosOrden('selEspecialidadOrden','cargarEspecialidadesOrdenHos','Especialidades orden de hospitalización');
	},

	cargarDatosAval: function () {
		if (oAval.loDatosAval.Datos.OrdenH.EXISTE===true){
			oModalOrdenHospital.goDataRecibido = oAval.loDatosAval.Datos.OrdenH;
			oModalOrdenHospital.cargarDatos();
			$("#txtJustificacionordenHos").val(oModalOrdenHospital.goDataRecibido.OBSERVA);
		}
		oModalOrdenHospital.mostrar();
	},
	
	obtenerDatos: function() {
		$('#selMedicoOrden').attr("disabled",false);
		$('#selUbicacionTrasladar').attr("disabled",false);
		var laOrdenHospitalizacion = $('#FormOrdenHospitalizacion').serializeAll(true);
		return laOrdenHospitalizacion;
	},
}

