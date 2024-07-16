var oActividadFisica = {
	gotableActividadHC: $('#tblActividadHC'),
	lcTitulo: 'Actividad Física',
	lcMensajeError: '',
	lcObjetoError: '',
	gcViaingreso: '',
	gcRespuestaAF: '',
	gcVerificarActividad: '',
	gcUrlAjax: 'vista-comun/ajax/escala_actividad_fisica.php',
	lbHabilitar : false,
	
	inicializar: function(tcTipoClinico) 
	{
		oActividadFisica.gcVerificarActividad='';
		this.iniciarTablaActividad();
		this.iniciaActividadFisica();
		oActividadFisica.gcVerificarActividad=tcTipoClinico;
	},
	
	iniciaActividadFisica: function()
	{
		//oActividadFisica.obtenerViaIngreso();
		oActividadFisica.cargarListadosActividadFisica(function() {
			if (oActividadFisica.gcVerificarActividad!=''){
				oActividadFisica.verificarActividadFisica();
			}	
		});
		$('#selRealizaActividad').on('change', oActividadFisica.realizaActividad);
		$('#adicionarActividad').on('click', oActividadFisica.validarActividad);
	},
	
	verificarActividadFisica: function() {
		 $.ajax({
			url : oActividadFisica.gcUrlAjax,
			data: {accion:'verificarActividad', lnIngreso: aDatosIngreso.nIngreso},
			type : 'POST',
			dataType : 'json'
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oActividadFisica.registrarDatos(loTipos.TIPOS);
				} else {
					fnAlert(loTipos.error + ' ');
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta Obtener vía de ingreso actividad física.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentá un error al consultar Obtener vía de ingreso actividad física.');
		});
	},
	
	registrarDatos: function(taDatos) {
		oActividadFisica.gcRespuestaAF=taDatos.respuesta;
		
		if (oActividadFisica.gcRespuestaAF!=''){
			var lcRespuesta=taDatos.respuesta;
			oActividadFisica.gotableActividadHC.bootstrapTable('hideColumn', 'ACCION');
			$('#selRealizaActividad').val(oActividadFisica.gcRespuestaAF);
			$("#selRealizaActividad").attr("disabled",true);
			
			if (oActividadFisica.gcRespuestaAF=='S'){
				$.each(taDatos.actividades, function( lcKey, loTipo ) {
					laDatosActividad = {CODTIPO: loTipo.CODTIPO, 
					TIPO: loTipo.DESCRIPCIONTIPO, 
					CODCLASE: loTipo.CODCLASE, 
					CLASE: loTipo.DESCRIPCIONCLASE, 
					FRECUENCIA: loTipo.FRECUENCIA, 
					TIEMPO: loTipo.TIEMPO, 
					CODINTENSIDAD: loTipo.CODINTENSIDAD, 
					INTENSIDAD: loTipo.DESCRIPCIONINTENSIDAD, 
					FORMULA: loTipo.TOTAL, 
					CODACTIVIDAD: loTipo.CODACTIVIDAD, 
					ACTIVIDAD: loTipo.DESCRIPCIONACTIVIDAD };
					oActividadFisica.registrarActividad(laDatosActividad,false);
				});
			}	
		}
	},
	
	cargarListadosActividadFisica: function(tfPost) {
		 $.ajax({
			url : oActividadFisica.gcUrlAjax,
			data: {accion:'consultarListados', lcViaIngreso: aDatosIngreso.cCodVia},
			type : 'POST',
			dataType : 'json'
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					oActividadFisica.gcViaingreso=loTipos.TIPOS.VIA;
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						if (loTipo.CLASIFICACION1==='REAACT'){ 
							if (loTipo.OPCIONAL2.includes(oActividadFisica.gcViaingreso)){
								$('#selRealizaActividad').append('<option data-ti="' + loTipo.OPCIONAL1 + '" data-va="' + loTipo.OPCIONAL2 + '" data-resp="' + loTipo.OPCIONAL5 + '" value="' + loTipo.CLASIFICACION2 + '">' + loTipo.DESCRIPCION + '</option>'); 
							}	
						}
						
						if (loTipo.CLASIFICACION1==='CLASACT'){ $('#selClaseActividad').append('<option data-ti="' + loTipo.OPCIONAL1 + '" data-va="' + loTipo.OPCIONAL2 + '" data-resp="' + loTipo.OPCIONAL5 + '" value="' + loTipo.CLASIFICACION2 + '">' + loTipo.DESCRIPCION + '</option>'); }
						if (loTipo.CLASIFICACION1==='TIPOACT'){ $('#selTipoActividad').append('<option data-ti="' + loTipo.OPCIONAL1 + '" data-va="' + loTipo.OPCIONAL2 + '" data-resp="' + loTipo.OPCIONAL5 + '" value="' + loTipo.CLASIFICACION2 + '">' + loTipo.DESCRIPCION + '</option>'); }
						if (loTipo.CLASIFICACION1==='INTACT'){ $('#selIntensidadActividad').append('<option data-ti="' + loTipo.OPCIONAL1 + '" data-va="' + loTipo.OPCIONAL2 + '" data-resp="' + loTipo.OPCIONAL5 + '" value="' + loTipo.CLASIFICACION2 + '">' + loTipo.DESCRIPCION + '</option>'); }
						if (loTipo.CLASIFICACION1==='TIPINTM'){ oActividadFisica.gcTipoIntensidadModerada=loTipo.CLASIFICACION2; }
						if (loTipo.CLASIFICACION1==='TIPINTV'){ oActividadFisica.gcTipoIntensidadVigorosa=loTipo.CLASIFICACION2; }	
						if (loTipo.CLASIFICACION1==='ACTINA' && loTipo.CLASIFICACION2==='A'){ oActividadFisica.gcEstadoActivo=loTipo.CLASIFICACION2; oActividadFisica.gcDescripcionActivo=loTipo.DESCRIPCION; }	
						if (loTipo.CLASIFICACION1==='ACTINA' && loTipo.CLASIFICACION2==='I'){ oActividadFisica.gcEstadoInactivo=loTipo.CLASIFICACION2;  oActividadFisica.gcDescripcionInactivo=loTipo.DESCRIPCION; }	
						if (loTipo.CLASIFICACION1==='SEDENTA'){ oActividadFisica.gcEstadoSedentario=loTipo.CLASIFICACION2; oActividadFisica.gcDescripcionSedentario=loTipo.DESCRIPCION; }
					});
					if (typeof tfPost == 'function') {
						tfPost();
					}		
				} else {
					fnAlert(loTipos.error + ' ');
				}
			} catch(err) {
				fnAlert('No se pudo realizar la consulta cargar listados actividad física.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentá un error al consultar cargar listados actividad física.');
		});
	},

	validarActividad: function(e){
		e.preventDefault();
		var loFunction = false;
		var lcActividad='';
		var lcTipo=$("#selTipoActividad").val();
		var lcClase=$("#selClaseActividad").val();
		var lcFrecuencia=$("#selFrecuenciaActividad").val();
		var lcTiempo=$("#selTiempoActividad").val();
		var lcIntensidad=$("#selIntensidadActividad").val();
		var lnFormula=parseInt(lcFrecuencia)*parseInt(lcTiempo);
		var lcDescrActividad='';
		var lcTipoIntensidad=$("#selIntensidadActividad option:selected").attr("data-ti")===undefined ? '' : $("#selIntensidadActividad option:selected").attr("data-ti");
		var lnValorIntensidad=$("#selIntensidadActividad option:selected").attr("data-va")===undefined ? 0 : parseInt($("#selIntensidadActividad option:selected").attr("data-va"));
		
		if (lcTipoIntensidad==oActividadFisica.gcTipoIntensidadVigorosa){ 
			lcActividad=lnFormula>=lnValorIntensidad ? oActividadFisica.gcEstadoActivo : oActividadFisica.gcEstadoInactivo;
			lcDescrActividad=lnFormula>=lnValorIntensidad ? oActividadFisica.gcDescripcionActivo : oActividadFisica.gcDescripcionInactivo;				
		}
		
		if (lcTipoIntensidad==oActividadFisica.gcTipoIntensidadModerada){ 
			lcActividad=lnFormula>=lnValorIntensidad ? oActividadFisica.gcEstadoActivo : oActividadFisica.gcEstadoInactivo; 
			lcDescrActividad=lnFormula>=lnValorIntensidad ? oActividadFisica.gcDescripcionActivo : oActividadFisica.gcDescripcionInactivo;				
		}
		
		if (lcTipo==''){
			fnAlert('Tipo actividad obligatoria, revise por favor.', '', false, false, false);
			$('#selTipoActividad').focus();
			return false;
		}
		var lcDescripcionTipo=$("#selTipoActividad option[value="+lcTipo+"]").text();

		if (lcClase==''){
			fnAlert('Clase actividad obligatoria, revise por favor.', '', false, false, false);
			$('#selClaseActividad').focus();
			return false;
		}
		var lcDescripcionClase=$("#selClaseActividad option[value="+lcClase+"]").text();

		if (lcFrecuencia=='' || parseInt(lcFrecuencia)<=0){
			fnAlert('Frecuencia actividad obligatoria, revise por favor.', '', false, false, false);
			$('#selFrecuenciaActividad').focus();
			return false;
		}
		
		if (lcTiempo=='' || parseInt(lcTiempo)<=0){
			fnAlert('Tiempo actividad obligatoria, revise por favor.', '', false, false, false);
			$('#selTiempoActividad').focus();
			return false;
		}
		
		if (lcIntensidad==''){
			fnAlert('Intensidad actividad obligatoria, revise por favor.', '', false, false, false);
			$('#selIntensidadActividad').focus();
			return false;
		}
		var lcDescripcionIntensidad=$("#selIntensidadActividad option[value="+lcIntensidad+"]").text();

		var laDatosActividad = {CODTIPO: lcTipo, TIPO: lcDescripcionTipo, CODCLASE: lcClase, CLASE: lcDescripcionClase, 
		FRECUENCIA: lcFrecuencia, TIEMPO: lcTiempo, CODINTENSIDAD: lcIntensidad, INTENSIDAD: lcDescripcionIntensidad, 
		FORMULA: lnFormula, CODACTIVIDAD: lcActividad, ACTIVIDAD: lcDescrActividad };
		oActividadFisica.adicionaActividad(lcTipo,laDatosActividad);
		$('#selTipoActividad').focus();
	},

	adicionaActividad: function(tcCodigoTipo,tcDatos){
		var taTablaActividad = oActividadFisica.gotableActividadHC.bootstrapTable('getData');
		var llverificaExiste = oActividadFisica.verificaCodigoExiste(tcCodigoTipo,taTablaActividad);
		if(llverificaExiste) {
			oActividadFisica.registrarActividad(tcDatos,true);
		}
		else{
			fnConfirm('Actividad ya ingresada, desea modificarla?', oActividadFisica.lcTitulo, false, false, 'medium', function(){
				oActividadFisica.modificarActividad(tcDatos);
			});
		}
	},
	
	verificaCodigoExiste: function(tcCodigo,taTablaActividad) {
		var llRetorno = true ;
			if(taTablaActividad != ''){
				$.each(taTablaActividad, function( lcKey, loTipo ) {
					if(loTipo['CODTIPO']==tcCodigo){
						oActividadFisica.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},
				
	registrarActividad: function(taDatosActividad,tlValidar) {
		var rows = []
			rows.push({
			CODTIPO: taDatosActividad.CODTIPO,
			TIPO: taDatosActividad.TIPO,
			CODCLASE: taDatosActividad.CODCLASE,
			CLASE: taDatosActividad.CLASE,
			FRECUENCIA: taDatosActividad.FRECUENCIA,
			TIEMPO: taDatosActividad.TIEMPO,
			CODINTENSIDAD: taDatosActividad.CODINTENSIDAD,
			TOTAL: taDatosActividad.FORMULA,
			INTENSIDAD: taDatosActividad.INTENSIDAD,
			CODACTIVIDAD: taDatosActividad.CODACTIVIDAD,
			ACTIVIDAD: taDatosActividad.ACTIVIDAD,
			ACCION:''
		})
		oActividadFisica.gotableActividadHC.bootstrapTable('append', rows);
		
		if (tlValidar){
			oActividadFisica.habilitarActividadFisica(true,false);
		}	
	},
	
	modificarActividad: function(camposModificar) {
		oActividadFisica.gotableActividadHC.bootstrapTable('updateRow', {
			index: oActividadFisica.indexedit,
			row: {
				CODTIPO: camposModificar.CODTIPO,
				TIPO: camposModificar.TIPO,
				CODCLASE: camposModificar.CODCLASE,
				CLASE: camposModificar.CLASE,
				FRECUENCIA: camposModificar.FRECUENCIA,
				TIEMPO: camposModificar.TIEMPO,
				CODINTENSIDAD: camposModificar.CODINTENSIDAD,
				TOTAL: camposModificar.FORMULA,
				INTENSIDAD: camposModificar.INTENSIDAD,
				CODACTIVIDAD: camposModificar.CODACTIVIDAD,
				ACTIVIDAD: camposModificar.ACTIVIDAD,
				ACCION:''
			}
		 });
		oActividadFisica.habilitarActividadFisica(true,false);
		$("#selTipoActividad").focus();
	},
	
	realizaActividad: function() {
		var lcRealizaActividad=$('#selRealizaActividad').val();
		var taTablaActividad = $('#tblActividadHC').bootstrapTable('getData');
		var tlValidar=lcRealizaActividad=='S' ? false : true;
		oActividadFisica.habilitarActividadFisica(tlValidar,tlValidar);

		if (lcRealizaActividad!='S' && taTablaActividad.length>0){
			fnConfirm('Desea borrar la información registrada?', 'Actividad Física', false, false, false,
				{ 
					text: 'Si',
					action: function(){
						$("#tblActividadHC").bootstrapTable('removeAll');
					}
				},
				{ text: 'No',
					action: function(){
						$('#selRealizaActividad').val('S');
						oActividadFisica.habilitarActividadFisica(false,false);
					}
				}
			);
		}
		$('#selTipoActividad').focus();
	},
	
	habilitarActividadFisica: function(tlValidar,tlHabilitar) {
		if ($('#selRealizaActividad').val()!=''){
			$("#selRealizaActividad").addClass("is-valid").removeClass("is-invalid");
		}else{
			$("#selRealizaActividad").removeClass("is-valid").addClass("is-invalid");
		}
		$("#selClaseActividad,#selTipoActividad,#selFrecuenciaActividad,#selTiempoActividad,#selIntensidadActividad").val('');
		$("#selClaseActividad,#selTipoActividad,#selFrecuenciaActividad,#selTiempoActividad,#selIntensidadActividad,#adicionarActividad").attr("disabled",tlHabilitar);
		$("#selClaseActividad,#selTipoActividad,#selFrecuenciaActividad,#selTiempoActividad,#selIntensidadActividad").removeClass("is-valid").removeClass("is-invalid");
		$("#btnAddActividad").attr("disabled", tlHabilitar);
		$('#selTipoActividad').focus();
	},

	iniciarTablaActividad: function(){
		oActividadFisica.gotableActividadHC.bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
				theadClasses: 'thead-dark',
				locale: 'es-ES',
				undefinedText: '-',
				height: '150',
				pagination: false,
				columns: [
			{
				title: 'Tipo actividad',
				field: 'TIPO',
				width: 25, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Clase actividad',
				field: 'CLASE',
				width: 15, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Frecuencia',
				field: 'FRECUENCIA',
				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Tiempo',
				field: 'TIEMPO',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Total',
				field: 'TOTAL',
			  	width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center'
			},
			{
				title: 'Intensidad',
				field: 'INTENSIDAD',
			  	width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Actividad/Inactividad',
				field: 'ACTIVIDAD',
			  	width: 20, widthUnit: "%",
				halign: 'center',
				align: 'left'
			},
			{
				title: 'Acción',
				field: 'ACCION',
  				width: 10, widthUnit: "%",
				halign: 'center',
				align: 'center',
				clickToSelect: false,
				events: oActividadFisica.eventoActividad,
				formatter: oActividadFisica.formatoActividad
			}
		  ]
		});
	},
	
	eventoActividad:  {
		'click .eliminar': function (e, value, row, index) {
			fnConfirm('Desea eliminar la actividad?', false, false, false, false, function(){
				oActividadFisica.gotableActividadHC.bootstrapTable('remove', {
				field: 'CODTIPO',
				values: [row.CODTIPO]
				});
				oActividadFisica.habilitarActividadFisica(true,false);
				$('#selTipoActividad').focus();
			},'');
		},
		'click .editar': function (e, value, row, index) {
			oActividadFisica.editarActividad(row);
		}
	},
	
	formatoActividad: function (value, row, index) {
		return [
		  '<a class="editar" href="javascript:void(0)" title="Editar">',
		  '<i class="fas fa-pencil-alt"></i>',
		  '</a>&nbsp;&nbsp;&nbsp;',
		  '<a class="eliminar" href="javascript:void(0)" title="Eliminar">',
		  '<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
		  '</a>'
		].join('')
	},
	
	editarActividad: function(arow) {
		$("#selTipoActividad").val(arow.CODTIPO);
		$("#selClaseActividad").val(arow.CODCLASE);
		$("#selFrecuenciaActividad").val(arow.FRECUENCIA);
		$("#selTiempoActividad").val(arow.TIEMPO);
		$("#selIntensidadActividad").val(arow.CODINTENSIDAD);
		$("#selTipoActividad").focus();
	},

	validacion: function()
	{
		var lbValido = true;
		var lcRealizaactividad=$('#selRealizaActividad').val();
		var laActividadFisica = oActividadFisica.gotableActividadHC.bootstrapTable('getData');
		
		if (lbValido){
			if (lcRealizaactividad===undefined){
				$("#selRealizaActividad").addClass("is-invalid");
				oActividadFisica.lcObjetoError = 'selRealizaActividad';
				oActividadFisica.lcMensajeError = 'Realiza actividad física no cargada, debe salir y volver a ingresar del aplicativo HC WEB'
				lbValido = false;
				return false;
			}
		}
		
		if (lbValido){
			if (lcRealizaactividad==''){
				$("#selRealizaActividad").addClass("is-invalid");
				oActividadFisica.lcObjetoError = 'selRealizaActividad';
				oActividadFisica.lcMensajeError = 'Falta registrar "Realiza actividad física" '
				lbValido = false;
				return false;
			}
		}
		
		if (lbValido){
			if (lcRealizaactividad!='S' && lcRealizaactividad!='N' && lcRealizaactividad!='C' && lcRealizaactividad!='V'){
				oActividadFisica.lcObjetoError = 'selRealizaActividad';
				oActividadFisica.lcMensajeError = 'Realiza actividad física, NO registrada '
				lbValido = false;
				return false;
			}
		}
		
		if (lbValido){
			if (lcRealizaactividad=='S' && laActividadFisica.length==0){
				oActividadFisica.lcObjetoError = 'selRealizaActividad';
				oActividadFisica.lcMensajeError = 'No existen actividades registradas '
				lbValido = false;
				return false;
			}
		}
		return true;
	},
	
	obtenerDatos : function(){
		var lcRealizoActividad=$("#selRealizaActividad").val();
		var lcRespuesta='';
		
		if (lcRealizoActividad=='S'){
			var taTablaActividad = oActividadFisica.gotableActividadHC.bootstrapTable('getData');

			$.each(taTablaActividad, function( lcKey, loTipo ) {
				if(loTipo['CODACTIVIDAD']==oActividadFisica.gcEstadoActivo){
					lcRespuesta=oActividadFisica.gcDescripcionActivo;
					return false;					
				}	
			});
		}else{
			lcRespuesta=$("#selRealizaActividad option:selected").attr("data-resp")===undefined ? '' : $("#selRealizaActividad option:selected").attr("data-resp");
		}	
		lcRespuesta=lcRespuesta!='' ? lcRespuesta : oActividadFisica.gcDescripcionInactivo;
		var laDatos = {Realiza: lcRealizoActividad, Respuesta: lcRespuesta, Actividades: $('#tblActividadHC').bootstrapTable('getData')};
		return laDatos ;
	}

};

