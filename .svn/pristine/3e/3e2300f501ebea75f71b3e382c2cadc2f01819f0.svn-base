var oDiagnosticos = {
	gotableDiagnosticos : $('#tblCiePrincipal') ,
	nNumDiagnostico: 0,
	lcTitulo: 'Diagnóstico',
	goCodigoDescartar: '', gcCodigoCieEditar: '', gnIndiceCie: 0, 
	lcMensajeError: '', gaFiltroPrincipal: '',
	llMostrarColumna: true,
	llDxObliga: false,

	inicializar: function(tcTipoRegistro){
		if (typeof aDatosIngreso['TipoEV'] === 'undefined' || aAuditoria['lRequiereAval']) {
			oDiagnosticos.llMostrarColumna=false;
		} else{
			if (typeof aDatosIngreso['DxPpal'][0] === 'undefined') {
				oDiagnosticos.llDxObliga = false;
			}else{
				oDiagnosticos.llDxObliga = aDatosIngreso['DxPpal'][0]['OBLIGA'] == 1 ;
			}
		}
		this.IniciarTablaCieClase();
		this.consultarDiagnostico('txtCodigoCie','cCodigoCie','cDescripcionCie','','tipoDiagnostico','');
		this.cargarListaDiagnosticos('tipoDiagnostico','tipo','tipos de diagnóstico');
		this.cargarListaDiagnosticos('claseDiagnostico','clase','clase de diagnóstico');
		this.cargarListaDiagnosticos('tratamientoDiagnostico','tratamiento','tratamiento de diagnóstico');
		this.cargarListaDiagnosticos('seltipoDescarte','descarte','descartar diagnóstico');
		this.cargarAyudaTipoCie('','ayudaTipoCie','Ayuda tipos de diagnósticos');
		this.cargarAyudaClaseCie('','ayudaClaseCie','Ayuda clase de diagnósticos');
		this.cargarAyudaTratamientoCie('','ayudaTratamientoCie','Ayuda tratamiento de diagnósticos');
		this.consultaValidarCiePrincipal(tcTipoRegistro);
		
		$('#FormDiagnostico').validate({
			rules: {
				cCodigoCie: "required",
				tipoDiagnostico: "required",
				claseDiagnostico: "required",
				tratamientoDiagnostico: "required",
			},
			errorElement: "div",
			errorPlacement: function ( error, element ) {
				error.addClass( "invalid-tooltip" );

				if (element.prop("type")==="radio") {
					error.insertAfter(element.parent("label"));
				} else {
					error.insertAfter(element);
				}
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).addClass("is-valid").removeClass("is-invalid");
			}
		});

		$('#FormDiagnostico').on('submit', function(e){e.preventDefault();});
		$('#AdcionarCie').on('click', this.adicionarDiagnosticos);
		$('#botonAyudaTipo').on('click', function(){fnAlert($("#ayudaTipoDiagnostico").html(), 'TIPO DE DIAGNÓSTICO','fas fa-atlas','blue','large');});
		$('#botonAyudaClase').on('click', function(){fnAlert($("#ayudaClaseDiagnostico").html(), 'APROXIMACIÓN DIAGNÓSTICA DE LA ATENCIÓN HOSPITALARIA','fas fa-atlas','blue','large');});
		$('#botonAyudaTratamiento').on('click', function(){fnAlert($("#ayudaTratamientoDiagnostico").html(), 'TRATAMIENTO','fas fa-atlas','blue','large');});
		$('#btnGuardaDescarte').on('click', this.validaActualizaDescarte);
		$('#btnCancelarDescarte').on('click', this.cancelarDescartarDiagnostico);
		$('#btnConsultaDescarte').on('click', this.verDxDescartados);
	},

	verDxDescartados: function() {
		if(typeof(oDxDescartados) == 'object'){
			oDxDescartados.mostrar(aDatosIngreso['nIngreso']);	
		}
	},

	buscarDiagnosticos: function(tcTipo) {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: 'consultaDiagnostico', lnNroIngreso: aDatosIngreso.nIngreso, lcTipoCie: tcTipo},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					if (loTipos.TIPOS.length > 0) {
						oDiagnosticos.cargarDiagnosticosConsultados(loTipos.TIPOS);
						if(typeof oEscalaCrusade === 'object' ){
							oEscalaCrusade.ConsultarEscalaCrusade();
						}
					}
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de consultar diagnósticos.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar de consultar diagnósticos.');
		});
	},

	cargarAyudaTipoCie: function(id,lcTipo,mensaje){
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: lcTipo},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$("#ayudaTipoDiagnostico").html(loTipos.TIPOS);
				} else {
					fnAlert(loTipos.error + ' ');
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar ' + mensaje +'.');
		});
	},
	
	consultaValidarCiePrincipal: function(tcTipoHc){
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: 'consultarValidaCiePrincipal', lcTipoHc: tcTipoHc},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				oDiagnosticos.gaFiltroPrincipal=loTipos.TIPOS===''?'':loTipos.TIPOS;
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de consulta Validar Cie Principal');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presenta un error al buscar consulta Validar Cie Principal');
		});
	},

	cargarAyudaClaseCie: function(id,lcTipo,mensaje) {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: lcTipo},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$("#ayudaClaseDiagnostico").html(loTipos.TIPOS);
				} else {
					fnAlert(loTipos.error + ' ');
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentá un error al buscar ' + mensaje +'.');
		});
	},

	cargarAyudaTratamientoCie: function(id,lcTipo,mensaje) {
		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: lcTipo},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$("#ayudaTratamientoDiagnostico").html(loTipos.TIPOS);
				} else {
					fnAlert(loTipos.error + ' ');
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentá un error al buscar ' + mensaje +'.');
		});
	},

	cargarListaDiagnosticos: function(id,lcTipo,mensaje) {
		var loSelect = $('#'+id);

		$.ajax({
			type: "POST",
			url: 'vista-comun/ajax/diagnostico.php',
			data: {lcTipoDiagnostico: lcTipo},
			dataType: "json"
		})
		.done(function( loTipos ) {
			try {
				if (loTipos.error == ''){
					$.each(loTipos.TIPOS, function( lcKey, loTipo ) {
						loSelect.append('<option value="' + lcKey + '">' + loTipo + '</option>');
					});
				} else {
					fnAlert(loTipos.error + ' ');
				}

				switch(id){
					case 'tipoDiagnostico':
						loSelect.on('change',function(){
							$('#claseDiagnostico').val('');
							$('#tratamientoDiagnostico').val('');

							if ($(this).val()==3){
								$('#claseDiagnostico option[value=2]').hide();
								$('#tratamientoDiagnostico option[value=1]').hide();
							}else{
								$('#claseDiagnostico option[value=2]').show();
								$('#tratamientoDiagnostico option[value=1]').show();
							}
							$("#claseDiagnostico").removeClass("is-valid");
							$("#tratamientoDiagnostico").removeClass("is-valid");
						});
					break;
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de ' + mensaje +'.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Se presentá un error al buscar ' + mensaje +'.');
		});
		return this;
	},
	
	consultarDiagnostico: function(toObjeto,toCodigo,tcDescripcion,tcTipo,toFocus) {
		var loObjeto = '#'+toObjeto;
		var loCodigoAsigna = '#'+toCodigo;
		var loDescripcionAsigna = '#'+tcDescripcion;
		var loObjetoFocus = '#'+toFocus;

		laDatosPaciente = {
			fecha: 0,
			genero: aDatosIngreso.cSexo,
			edad: aDatosIngreso.aEdad,
			tipoconsulta: tcTipo
		}
		
		lcUrl="vista-comun/ajax/diagnostico?lcTipoDiagnostico=consultarDiagnosticos&lcDatosPacientes="+JSON.stringify(laDatosPaciente)+"";
		$(loObjeto).autoComplete({
			preventEnter: true,
			resolverSettings: {
				url:lcUrl ,
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
			$(loCodigoAsigna).val(item.CODIGO);
			$(loDescripcionAsigna).val(item.DESCRIPCION);
			$(loObjeto).val('');
			$(loObjeto).removeClass("is-valid");
			$(loObjetoFocus).focus();
		}).on('autocomplete.freevalue', function(evt, value) {
			$(loObjeto).val('');
		});
	},

	adicionarDiagnosticos: function(e){
		e.preventDefault();
		if ($('#FormDiagnostico').valid()){
			var lcCodigoCie = $("#cCodigoCie").val();
			var lcDescripcionCie = ($("#cDescripcionCie").val()).trim();
			var lcTipoCie = $("#tipoDiagnostico").val();
			var lcClaseCie = $("#claseDiagnostico").val();
			var lcTratamientoCie = $("#tratamientoDiagnostico").val();
			var lcAnalisisCie = $("#cieObservaciones").val();
			let lcHomologoViaIngreso=aDatosIngreso.cCodVia=='01'?'U':aDatosIngreso.cCodVia=='02'?'C':'H';
			
			if (lcDescripcionCie==''){
				$("#txtCodigoCie,#cCodigoCie,#cDescripcionCie").val("");
				$('#txtCodigoCie').focus();
				fnAlert('Diagnóstico no valido, revise por favor.');
				return false;
			}
			
			lcCodigoCie = lcCodigoCie.trim();
			var lcDiagnostico = {
					lcCodigoCie: lcCodigoCie,
					lcDescripcionCie: lcDescripcionCie,
					lcTipoCie: lcTipoCie,
					lcClaseCie: lcClaseCie,
					lcTratamientoCie: lcTratamientoCie,
					lcAnalisisCie: lcAnalisisCie
				};

			if (lcCodigoCie=='' || lcTipoCie=='' || lcClaseCie=='' || lcTratamientoCie==''){
				fnAlert('Campos obligatorios, revise por favor.');
				return false;
			}
			
			if (lcTipoCie=='1' && (oDiagnosticos.gaFiltroPrincipal!='')){
				lcLetraCie=lcCodigoCie.substr(0, 1);
				laResultado=oDiagnosticos.gaFiltroPrincipal[lcLetraCie];
				laResultado=laResultado===undefined?'':laResultado;
				laVias=laResultado.VIAS===undefined?'':laResultado.VIAS;
				lcTextoMensaje=laResultado.DESCRIPCION===undefined?'':laResultado.DESCRIPCION;
				lcTitulo=lcCodigoCie+'-'+lcDescripcionCie;
				
				if ($.inArray(lcHomologoViaIngreso, laVias)>=0){
					fnAlert(lcTextoMensaje, lcTitulo, false, 'blue', 'medium');
					return false;
				}
			}
			
			var taTablaValida = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
			if (lcTipoCie==1){
				var llverificaPrincipal = oDiagnosticos.verificaDiagnosticoPrincipal(lcCodigoCie,taTablaValida);
				if(!llverificaPrincipal) {
					fnAlert('Ya existe diagnóstico principal, revise por favor.');
					return false;
				}
			}

			var llverificaExiste = oDiagnosticos.verificaCodigoExiste(lcCodigoCie,taTablaValida);
			var llverificaCodigoDescarte = oDiagnosticos.verificaCodigodescarte(lcCodigoCie,taTablaValida);
			//			llverificaExiste = !llverificaCodigoDescarte ? !llverificaCodigoDescarte : llverificaExiste;
			if(llverificaExiste) {
				oDiagnosticos.adicionarFilaCie(lcDiagnostico);
			}
			else{
				if(llverificaCodigoDescarte){
					fnConfirm('Diagnóstico ya ingresado, desea modificarlo?', oDiagnosticos.lcTitulo, false, false, false, function(){
						oDiagnosticos.modificarDiagnostico(lcDiagnostico);
						});
				}else{
					fnAlert('Diagnóstico descartado, Revise por favor !!!', oDiagnosticos.lcTitulo, false, false, false);
					return false;
				}
				
			}
			$('#txtCodigoCie').focus();
			oDiagnosticos.ordenarDiagnosticos();
			$("#FormDiagnostico input, #FormDiagnostico select, #FormDiagnostico textarea").removeClass("is-valid");
		}
	},

	verificaDiagnosticoPrincipal: function(tcCodigoCie, taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']!=oDiagnosticos.gcCodigoCieEditar || loTipo['CODIGO']!=tcCodigoCie){
						if(loTipo['CODTIPO']=='1' && loTipo['CODDESCARTE']==''){
							llRetorno = false;
						}
					}	
				});
			};
			
		return llRetorno ;
	},
	
	verificaCodigoViene: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo && loTipo['MARCAVIENE']==1 && loTipo['CODDESCARTE']==''){
						oDiagnosticos.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},
	
	verificaCodigodescarte: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo && loTipo['CODDESCARTE']!=''){
						oDiagnosticos.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},
	
	ordenarDiagnosticos: function() {
		var laDiagnosticoOrdenar = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
		laDiagnosticoOrdenar = laDiagnosticoOrdenar.sort((a, b) =>{
			if (a.CODTIPO < b.CODTIPO){
				return -1;
			} else if (a.CODTIPO > b.CODTIPO){
				return 1;
			}else{
				return 0;
			}
		});
		$('#tblCiePrincipal').bootstrapTable('refresh');
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('refreshOptions', {data: laDiagnosticoOrdenar});
	},

	verificaCodigoExiste: function(tcCodigo,taTablaValida) {
		var llRetorno = true ;
			if(taTablaValida != ''){
				$.each(taTablaValida, function( lcKey, loTipo ) {
					if(loTipo['CODIGO']==tcCodigo){
						oDiagnosticos.indexedit = lcKey;
						llRetorno = false;
					}
				});
			};
		return llRetorno ;
	},

	modificarDiagnostico: function(camposModificar) {
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateRow', {
			index: oDiagnosticos.indexedit,
			row: {
				CODIGO: (camposModificar.lcCodigoCie).trim(),
				DESCRIP: camposModificar.lcDescripcionCie,
				CODTIPO: $("#tipoDiagnostico option[value="+camposModificar.lcTipoCie+"]").val(),
				TIPO: $("#tipoDiagnostico option[value="+camposModificar.lcTipoCie+"]").text(),
				CODCLASE: $("#tipoDiagnostico option[value="+camposModificar.lcClaseCie+"]").val(),
				CLASE: $("#claseDiagnostico option[value="+camposModificar.lcClaseCie+"]").text(),
				CODTRATA: $("#tipoDiagnostico option[value="+camposModificar.lcTratamientoCie+"]").val(),
				TRATA: $("#tratamientoDiagnostico option[value='"+camposModificar.lcTratamientoCie+"']").text(),
				CODDESCARTE: '',
				DESCARTE: '',
				JUSTIFICACIONDESCARTE: '',
				OBSER: camposModificar.lcAnalisisCie,
				ACCIONES: ''
			}
		});
		oDiagnosticos.inicializaDiagnosticos();
		
		if(typeof oSadPersons === 'object'){
			oSadPersons.habilitar(oDiagnosticos.obtenerDatos());
		}
	},

	adicionarFilaCie: function(camposFilaCie){
		var rows = [];
		oDiagnosticos.nNumDiagnostico=oDiagnosticos.nNumDiagnostico+1;

		rows.push({
			CODIGO: (camposFilaCie.lcCodigoCie).trim(),
			DESCRIP: camposFilaCie.lcDescripcionCie,
			CONTINUA: 1,
			DESCARTAR: 0,
			CODTIPO: $("#tipoDiagnostico option[value="+camposFilaCie.lcTipoCie+"]").val(),
			TIPO: $("#tipoDiagnostico option[value="+camposFilaCie.lcTipoCie+"]").text(),
			CODCLASE: $("#tipoDiagnostico option[value="+camposFilaCie.lcClaseCie+"]").val(),
			CLASE: $("#claseDiagnostico option[value="+camposFilaCie.lcClaseCie+"]").text(),
			CODTRATA: $("#tipoDiagnostico option[value="+camposFilaCie.lcTratamientoCie+"]").val(),
			TRATA: $("#tratamientoDiagnostico option[value='"+camposFilaCie.lcTratamientoCie+"']").text(),
			CODDESCARTE: '',
			DESCARTE: '',
			JUSTIFICACIONDESCARTE: '',
			OBSER: camposFilaCie.lcAnalisisCie,
			MARCAVIENE: 0,
			ACCIONES: '',
			IDENT: oDiagnosticos.nNumDiagnostico
		});
		$('#tblCiePrincipal').bootstrapTable('append', rows);
		oDiagnosticos.inicializaDiagnosticos();

		if(typeof oEscalaHasbled === 'object'){
			if(!oEscalaHasbled.lbDatosConsulta){
				oEscalaHasbled.habilitar(oDiagnosticos.obtenerDatos(true));
				if(typeof oEscalaChadsvas === 'object'){
					if(!oEscalaChadsvas.lbDatosConsulta && oEscalaChadsvas.lnTotalPuntajeChadsvas==0){
						oEscalaChadsvas.habilitar(oDiagnosticos.obtenerDatos(true));
					}
				}
			}
		}

		if(typeof oEscalaCrusade === 'object'){
			if(!oEscalaCrusade.lbDatosConsulta){
				oEscalaCrusade.habilitar(oDiagnosticos.obtenerDatos(true));
			}
		}
		if(typeof oSadPersons === 'object'){
			oSadPersons.habilitar(oDiagnosticos.obtenerDatos());
		}
	},

	// Valida diagnóstico se encuentra en el listado
	validarDiagnosticoMod: function(tcDiagnostico) {
		if(tcDiagnostico !=''){
			var lnidx = oListaDiagnosticos.ListaDx[tcDiagnostico];
			if(lnidx===undefined){
				return false
			}
			return true
		}
	},

	inactivarCampos: function(){
		$("#txtCodigoCie").attr("disabled",true)
		$("#tipoDiagnostico").attr("disabled",true)
		$("#claseDiagnostico").attr("disabled",true)
		$("#tratamientoDiagnostico").attr("disabled",true)
		$("#cieObservaciones").attr("disabled",true)
		$("#AdcionarCie").attr("disabled",true)
		$("#btnGuardar").attr("disabled",true)
	},

	cargarDiagnosticosConsultados: function(taObtieneDiagnosticos) {
		if(taObtieneDiagnosticos != ''){
			$.each(taObtieneDiagnosticos, function( lcKey, loTipo ) {
				oDiagnosticos.incluirDiagnosticos(loTipo);
			});
		};
	},

	incluirDiagnosticos: function(camposDiagnosticos){
		var rows = [{
			CODIGO: (camposDiagnosticos.DIAGNOSTICO),
			CONTINUA: 0,
			DESCARTAR: 0,
			DESCRIP: camposDiagnosticos.DESCRIPCION_CIE,
			CODTIPO: camposDiagnosticos.TIPO,
			TIPO: camposDiagnosticos.DESCRIPCION_TIPO,
			CODCLASE: camposDiagnosticos.CLASE,
			CLASE: camposDiagnosticos.DESCRIPCION_CLASE,
			CODTRATA: camposDiagnosticos.TRATAMIENTO,
			TRATA: camposDiagnosticos.DESCRIPCION_TRATAMIENTO,
			CODDESCARTE: camposDiagnosticos.DESCARTE,
			DESCARTE: camposDiagnosticos.TIPO_DESCARTE,
			JUSTIFICACIONDESCARTE: '',
			OBSER: camposDiagnosticos.ANALISIS,
			MARCAVIENE: 1,
			ACCIONES: '',
			IDENT: 999
		}];
		$('#tblCiePrincipal').bootstrapTable('append', rows);
	},

	validaActualizaDescarte: function(){
		if (($("#seltipoDescarte").val()).trim()==''){
			$('#seltipoDescarte').focus();
			fnAlert('Tipo descarte obligatorio, revise por favor.');
			return false;
		}

		if (($("#txtJustificacionDescarte").val()).trim()==''){
			$('#txtJustificacionDescarte').focus();
			fnAlert('Justificación obligatoria, revise por favor.');
			return false;
		}
		$("#divDescartar").modal("hide");
		$("#txtCodigoCie").focus();

		var loCieDescarta = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
		lnIndice = loCieDescarta.findIndex(loElement => loElement.CODIGO === oDiagnosticos.goCodigoDescartar);

		if (lnIndice>=0){
			oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateRow', {
				index: oDiagnosticos.gnIndiceCie,
				row: {
					CODDESCARTE: $("#seltipoDescarte").val(),
					DESCARTE: $("#seltipoDescarte option[value='"+$("#seltipoDescarte").val()+"']").text(),
					JUSTIFICACIONDESCARTE: $("#txtJustificacionDescarte").val(),
				}
			});
		}
	},

	cancelarDescartarDiagnostico: function () {
		fnConfirm('Desea cancelar el proceso descarte?.', oDiagnosticos.lcTitulo, false, false, false,
			{
				text: 'Si',
				action: function(){
					oDiagnosticos.inicializaDescartar();
					
					var loCieDescarta = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
					lnIndice = loCieDescarta.findIndex(loElement => loElement.CODIGO === oDiagnosticos.goCodigoDescartar);

					if (lnIndice>=0){
						oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateRow', {
							index: oDiagnosticos.gnIndiceCie,
							row: {
								DESCARTAR: 0,
								CODDESCARTE: '',
								DESCARTE: '',
								JUSTIFICACIONDESCARTE: '',
							}
						});
					}
					oDiagnosticos.verificaDiagnosticoActivo();
					
					$("#divDescartar").modal("hide");
					$("#txtCodigoCie").focus();
				}
			},

			{ text: 'No',
				action: function(){
					$('#divDescartar').modal('show');
				}
			}
		);
	},

	verificaDiagnosticoActivo: function () {
		var taTablaValida = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
		
		if(taTablaValida != ''){
			$.each(taTablaValida, function( index, loTipo ) {
				if(loTipo['CODIGO']==oDiagnosticos.goCodigoDescartar && loTipo['MARCAVIENE']==0){
					$('#tblCiePrincipal').bootstrapTable('remove', {
						field: '$index',
						values: [index]
					});
				}
			});
		};
	},
	
	inicializaDiagnosticos: function () {
		oDiagnosticos.gcCodigoCieEditar='';
		$("#txtCodigoCie").removeClass("is-valid");
		$("#txtCodigoCie,#cCodigoCie,#cDescripcionCie,#tipoDiagnostico,#claseDiagnostico,#tratamientoDiagnostico,#cieObservaciones").val('');
		$('#txtCodigoCie').focus();
	},

	inicializaDescartar: function () {
		$("#seltipoDescarte").val('');
		$("#txtJustificacionDescarte").val('');
	},

	IniciarTablaCieClase: function (){
		$('#tblCiePrincipal').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light', // 'thead-dark' 'thead-light'
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLst',
			height: '400',
			pagination: false,
			pageSize: 25,
			pageList: '[10, 20, 50, 100, 250, 500, All]',
			filterAlgorithm: 'and',
			search: false,
			sortName: 'CODTIPO',
			searchOnEnterKey: false,
			visibleSearch: false,
			showSearchButton: false,
			showSearchClearButton: false,
			trimOnSearch: true,
			iconSize: 'sm',
			singleSelect:'true',
			columns: [
				{
					title: 'CONTINUA',
					field: 'CONTINUA',
					align: 'center',
					value: 0,
					visible: oDiagnosticos.llMostrarColumna,
					formatter: oDiagnosticos.formatoCheckboxC,
					events: oDiagnosticos.eventoContinua,
					width: 8, widthUnit: "%"
				},
				{
					title: 'DESCARTAR',
					field: 'DESCARTAR',
					align: 'center',
					value: 0,
					visible: oDiagnosticos.llMostrarColumna,
					formatter: oDiagnosticos.formatoCheckboxD,
					events: oDiagnosticos.eventoDescarte,
					width: 8, widthUnit: "%"
				},{
					title: 'CODIGO',
					field: 'CODIGO',
					align: 'center',
					valign: 'middle',
					width: 5, widthUnit: "%"
				},{
					title: 'DESCRIPCION',
					field: 'DESCRIP',
					halign: 'center',
					valign: 'middle',
					width: 25, widthUnit: "%"
				},{
					title: 'CARACTERIZACION',
					field: 'OBSER',
					halign: 'center',
					valign: 'middle',
					width: 20, widthUnit: "%"
				},{
					title: 'TIPO',
					field: 'TIPO',
					align: 'center',
					width: 9, widthUnit: "%"
				},{
					title: 'CLASE',
					field: 'CLASE',
					align: 'center',
					width: 9, widthUnit: "%"
				},{
					title: 'TRATAMIENTO',
					field: 'TRATA',
					align: 'center',
					width: 9, widthUnit: "%"
				},{
					title: 'DESCARTE',
					field: 'DESCARTE',
					halign: 'center',
					valign: 'middle',
					width: 15, widthUnit: "%"
				},{
					title: 'ACCIONES',
					field: 'ACCIONES',
					align: 'center',
					clickToSelect: false,
					events: this.operateEvents,
					formatter: this.formatoBorrarCie,
					width: 8, widthUnit: "%"
				}
			]
		});
	},

	operateEvents: {
		'click .editar': function (e, value, row, index) {
			oDiagnosticos.editarDiagnostico(row);
		},
		
		'click .removeCie': function (e, value, row, index) {
			lcCodigoBorrar=row.CODIGO
			lnMarcaViene=row.MARCAVIENE;
			lnIdentificador=row.IDENT;
			oDiagnosticos.goCodigoDescartar = '';
			oDiagnosticos.gnIndiceCie = index;
			
			if (lnMarcaViene==0){
				oDiagnosticos.eliminarDiagnostico(lnIdentificador);
			}
		}
	},

	formatoCheckboxC: function(tnValor, toFila){
		return [
			'<a class="intContinua" id="intContinua-'+toFila['CODIGO']+'" href="javascript:void(0)" title="Continua">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'"></i>',
			'</a>'
		].join('');
	},

	eventoContinua: {
		'click .intContinua': function (e, tnValor, toFila, tnIndex) {
			var lnDescartar=toFila['DESCARTAR'];
			if (lnDescartar==1){
				oDiagnosticos.cancelarDescartarDiagnostico()
			}else{
				if(tnValor==1){
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'CONTINUA', value:0});
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:0});
				}else{
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'CONTINUA', value:1});
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:0});
					oDiagnosticos.editarDiagnostico(toFila);
				}
			}			
		}
	},

	formatoCheckboxD: function(tnValor, toFila){
		return [
			'<a class="removeCie" href="javascript:void(0)" title="Descartar diagnóstico">',
			'<i class="fa '+(tnValor==1 ? 'fa-check-square' : 'fa-square')+'"></i>',
			'</a>'
		].join('');
	},

	eventoDescarte: {
		'click .removeCie': function (e, tnValor, toFila, tnIndex) {
			var lnCodigo=toFila['CODIGO'];
			if(toFila['MARCAVIENE']==1){
				if(tnValor==1){
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:0});
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'CONTINUA', value:0});
					oDiagnosticos.cancelarDescartarDiagnostico();
				}else{
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:1});
					oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'CONTINUA', value:0});	
					oDiagnosticos.validaDescartar(toFila,tnIndex);
				}
			}else{
				oDiagnosticos.eliminarDiagnostico(toFila.IDENT);
			}
		}
	},

	editarDiagnostico: function(arow) {
		oDiagnosticos.gcCodigoCieEditar=arow.CODIGO;
		lnMarcaViene=arow.MARCAVIENE;
		
		if (lnMarcaViene==0){
			oDiagnosticos.gotableDiagnosticos.bootstrapTable('remove', {
				field: 'CODIGO',
				values: [oDiagnosticos.gcCodigoCieEditar]
			});
		}	
		$("#cCodigoCie").val(arow.CODIGO);
		$("#cDescripcionCie").val(arow.DESCRIP);
		$("#tipoDiagnostico").val(arow.CODTIPO);
		$("#claseDiagnostico").val(arow.CODCLASE);
		$("#tratamientoDiagnostico").val(arow.CODTRATA);
		$("#cieObservaciones").val(arow.OBSER);
		
		if (arow.CODTIPO=='3'){
			$('#claseDiagnostico option[value=2]').hide();
			$('#tratamientoDiagnostico option[value=1]').hide();
		}else{
			$('#claseDiagnostico option[value=2]').show();
			$('#tratamientoDiagnostico option[value=1]').show();
		}
		$("#txtCodigoCie").addClass("is-valid");
		$("#tipoDiagnostico").addClass("is-valid");
		$("#claseDiagnostico").addClass("is-valid");
		$("#tratamientoDiagnostico").addClass("is-valid");
		
		if (arow.OBSER!=''){
			$("#cieObservaciones").addClass("is-valid");
		}
		$("#txtCodigoCie").focus();
	},
	
	formatoBorrarCie: function (value, row, index) {
		if (row.MARCAVIENE==0){
			return [
				'<a class="editar" href="javascript:void(0)" title="Editar">',
				'<i class="fas fa-pencil-alt"></i>',
				'</a>&nbsp;&nbsp;&nbsp;',
				'<a class="removeCie" href="javascript:void(0)" title="Eliminar diagnóstico">',
				'<i class="fas fa-trash-alt" style="color:#E96B50"></i>',
				'</a>'
			].join('')
		}else{return [].join('')}
	},
	
	validaDescartar: function (tcDatosFila, tnIndex) {
		lcCodigoDescartar = tcDatosFila.CODIGO;
		lcCodigoDescarte = tcDatosFila.CODDESCARTE.trim();
		lcJustificacionDescarte = tcDatosFila.JUSTIFICACIONDESCARTE;
		oDiagnosticos.gnIndiceCie = tnIndex;
		if (lcCodigoDescarte==''){
			fnConfirm('Desea descartar el diagnóstico indicado?.', false, false, false, false,
				{
					text: 'Si',
					action: function(){
						oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:1});
						oDiagnosticos.goCodigoDescartar = lcCodigoDescartar;
						oDiagnosticos.inicializaDescartar();
						$('#divDescartar').modal('show');
						$('#seltipoDescarte').focus();
					}
				},
				{	text: 'No',
					action: function(){
						oDiagnosticos.ActivarDescarte(tnIndex,0);
					} 
				}
			);
		}else{
			fnConfirm('Diagnóstico con descarte ya registrado, desea modificar la información?.', false, false, false, false,
				{
					text: 'Si',
					action: function(){
						oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:1});
						oDiagnosticos.goCodigoDescartar = lcCodigoDescartar;
						$("#seltipoDescarte").val(lcCodigoDescarte);
						$("#txtJustificacionDescarte").val(lcJustificacionDescarte);
						$('#divDescartar').modal('show');
						$('#seltipoDescarte').focus();
					}
				},
				{ text: 'No',
					action: function(){
						oDiagnosticos.ActivarDescarte(tnIndex,0);
					} 
				}
			);
		}	
	},

	ActivarDescarte: function (tnIndex, tnActivar) {
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTAR', value:tnActivar});
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'DESCARTE', value:''});
		oDiagnosticos.gotableDiagnosticos.bootstrapTable('updateCell',{index:tnIndex, field:'JUSTIFICACIONDESCARTE', value:''});

	},

	eliminarDiagnostico: function (tnIdentificador) {

		fnConfirm('Desea eliminar el diagnóstico?', false, false, false, false, function(){
			oDiagnosticos.gotableDiagnosticos.bootstrapTable('remove', {
				field: 'IDENT',
				values: [tnIdentificador]
			});
			
			if(typeof oEscalaHasbled === 'object'){
				if(!oEscalaHasbled.lbDatosConsulta){
					oEscalaHasbled.habilitar(oDiagnosticos.obtenerDatos(true));
				}
			}
			
			if(typeof oEscalaCrusade === 'object'){
				if(!oEscalaCrusade.lbDatosConsulta){
					oEscalaCrusade.habilitar(oDiagnosticos.obtenerDatos(true));
				}
			}
			if(typeof oSadPersons === 'object'){
				oSadPersons.habilitar(oDiagnosticos.obtenerDatos());
			}

		},'');

	},

	validacion: function(){
		var lbValido = true;
		var lnCantidadTipoPrincipal = lnCantidadContinua = lnCantidadDescartar = 0;
		var laDiagnosticos = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
		var lnCantidadTotal = laDiagnosticos.length;
		
		$.each(laDiagnosticos, function( lnIndex, loDiagnostico ) {
			lnCantidadContinua = loDiagnostico.CONTINUA == '1'? lnCantidadContinua+1:lnCantidadContinua;
			lnCantidadDescartar = loDiagnostico.DESCARTAR == '1'? lnCantidadDescartar+1:lnCantidadDescartar;
			if (loDiagnostico.CODTIPO=='1' && loDiagnostico.CODDESCARTE==''){
				lnCantidadTipoPrincipal+=1;
			}
		});
		
		if (lnCantidadTipoPrincipal===0){
			this.lcMensajeError = 'No existe un diagnóstico principal, revise por favor.';
			$("#txtCodigoCie").focus();
			lbValido = false;
			return lbValido;
		}
		
		if (lbValido==true){
			if (lnCantidadTipoPrincipal>1){
				this.lcMensajeError = 'Se registraron varios diagnósticos principales, revise por favor.';
				$("#txtCodigoCie").focus();
				lbValido = false;
				return lbValido;
			}
		}

		if (oDiagnosticos.llMostrarColumna){
			if (oDiagnosticos.llDxObliga || lnCantidadContinua>0 || lnCantidadDescartar>0){
				if(lnCantidadTotal!==lnCantidadContinua+lnCantidadDescartar){
					oDiagnosticos.lcMensajeError = 'Debe indicar si los diagnosticos CONTINUAN o SE DESCARTAN en su totalidad. Revise por favor !';
					lbValido = false ;
					return lbValido;
				}
			}
		}	
		return lbValido;
	},

	datosValidar: function(){
		var laDatos = [];
		var laDiagnosticos = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');

		$.each(laDiagnosticos, function( lnIndex, loDiagnostico ) {
			laDatos.push({
				codigo: loDiagnostico.CODIGO,
				tipo: loDiagnostico.CODTIPO,
				clase: loDiagnostico.CODCLASE,
				tratamiento: loDiagnostico.CODTRATA,
				analisis: loDiagnostico.OBSER
			});
		});
		return laDatos;
	},

	obtenerDatos: function(llSinDescarte){
		var loDiagnosticos=[];
		if(llSinDescarte){
			loTabla = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
			$.each(loTabla, function(lnIndice, loMed){
				if(loMed.CODDESCARTE==''){
					loDiagnosticos.push(loMed);
				}
			});
		}else{
			loDiagnosticos = oDiagnosticos.gotableDiagnosticos.bootstrapTable('getData');
		}
		return loDiagnosticos ;
	}
}
