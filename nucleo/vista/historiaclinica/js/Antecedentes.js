var oAntecedentes = {
	nNumVacuna: 0,
	oVacunaCovid: {},
	cDatVacunaCovid: '',
	dInicioCovid: new Date(2021, 01, 17), // 2021-02-17

	inicializar: function()
	{
		if(!(typeof oAval === 'object') && aAuditoria.lRequiereAval==false){
			this.ConsultarUltimoAntecedente(aDatosIngreso['cTipId'], aDatosIngreso['nNumId'], this.iniciarVacunaCovid);
		}else{
			this.IniciarTabla();
			this.iniciarVacunaCovid();
		}		
		this.iniciarPrenatales();
		$("#divOpcDiscapacidad").hide();
		$('#selDiscapacidad').change(this.validarEstado);
	},

	iniciarPrenatales: function()
	{
		if (aDatosIngreso['cCodVia']!='04'){
			$("#divPrenatal").hide();
		}
	},
	
	ConsultarUltimoAntecedente: function(tcTipDocPac, tnNumDocPac, tfFunPost)
	{
		$.ajax({
			type: "POST",
			url: "vista-historiaclinica/ajax/HistoriaClinica.php",
			data: {lcTipo: 'Antecedente', lcTipDocPac: tcTipDocPac, lnNumDocPac: tnNumDocPac},
			dataType: "json"
		})
		.done(function(loDatos) {
			if(loDatos.error.length>0){
				fnAlert(loDatos.error);
				return;
			}
			try {
				$("#antAlergicos").val(loDatos.DATOS[15][8].trim());
				$("#antFamiliares").val(loDatos.DATOS[15][14].trim());
				$("#antPatologicos").val(loDatos.DATOS[15][1].trim());
				$("#antHospitalarios").val(loDatos.DATOS[15][18].trim());
				$("#antQuirurgicos").val(loDatos.DATOS[15][6].trim());
				$("#antToxicos").val(loDatos.DATOS[15][10].trim());
				$("#antTransfusionales").val(loDatos.DATOS[15][3].trim());
				$("#antTraumaticos").val(loDatos.DATOS[15][7].trim());
				$("#antGineco").val(loDatos.DATOS[15][12].trim());
				$("#antVacunas").val(loDatos.DATOS[15][4].trim());
				if(typeof loDatos.DATOS[4] !== 'undefined'){
					if(typeof loDatos.DATOS[4][24] !== 'undefined'){
						oAntecedentes.cDatVacunaCovid=loDatos.DATOS[4][24];
					}
				}
				if(typeof tfFunPost === 'function') {
					tfFunPost();
				}
			} catch(err) {
				fnAlert('No se pudo realizar la busqueda de los ultimos antecedentes.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Error al realizar la busqueda de los ultimos antecedentes.');
		});
	},

	validarEstado: function()
	{
		var lcDiscapacidad = $("#selDiscapacidad").val();
		if (lcDiscapacidad == 'Si'){
			$("#divOpcDiscapacidad").show();
		}else{
			$("#divOpcDiscapacidad").hide();
			$("#chk01").prop('checked', false);
			$("#chk02").prop('checked', false);
			$("#chk03").prop('checked', false);
			$("#chk04").prop('checked', false);
			$("#chk05").prop('checked', false);
			$("#chk06").prop('checked', false);
		}
	
	},

	validacion: function()
	{
		if(!$('#FormAntecedentes').valid()){
			ubicarObjeto('#FormAntecedentes');
			return false;
		}

		var lcDiscapacidad = $("#selDiscapacidad").val();
		if (lcDiscapacidad == 'Si'){

			if(chk01.checked==false && chk02.checked==false && chk03.checked==false && chk04.checked==false && chk05.checked==false && chk06.checked==false){
				$('#selDiscapacidad').focus();
				fnAlert('Debe indicar la discapacidad del paciente', 'Antecedentes', false, false, false);
				return false;	
			}
		}

		if(oAntecedentes.oVacunaCovid.CONFIG.obligar=='SI' && oAntecedentes.oVacunaCovid.ACTIVA){
			var loVacCovid=$('#tblVacunas').bootstrapTable('getData');
			if(loVacCovid.length==0){
				$('#selVacunaCovid').addClass('is-invalid');
				ubicarObjeto('#FormAntecedentes','#selVacunaCovid');
				return false;
			}
		}
		return true;
	},

	obtenerDatos: function()
	{
		//serialización de datos dentro de laDatos
		var loAntec = $('#FormAntecedentes').serializeArray();
		loAntec.push({
			name:"antVacunaCovid",
			value:(oAntecedentes.oVacunaCovid.ACTIVA? $('#tblVacunas').bootstrapTable('getData'): [])
		})
		return loAntec;
	},

	iniciarVacunaCovid: function()
	{
		$.ajax({
			type: "POST",
			url: "vista-historiaclinica/ajax/HistoriaClinica.php",
			data: {lcTipo:'ParVacCov19'},
			dataType: "json"
		})
		.done(function(loDatos) {
			if(loDatos.error.length>0){
				fnAlert(loDatos.error);
				return;
			}
			try {
				oAntecedentes.oVacunaCovid=loDatos.datos;
				oAntecedentes.configurarVacunaCovid();
			} catch(err) {
				console.log(err);
				fnAlert('No se pudo realizar la busqueda de parámetros Covid.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			console.log(jqXHR.responseText);
			fnAlert('Error al realizar la busqueda de los ultimos antecedentes.');
		});
	},

	configurarVacunaCovid: function()
	{
		var activarCovid19 = new Function("return "+oAntecedentes.oVacunaCovid.ACTIVAR+";");
		oAntecedentes.oVacunaCovid.ACTIVA = activarCovid19();

		if(oAntecedentes.oVacunaCovid.ACTIVA){

			// Configura datepicker
			$('#FormAntecedentes .input-group.date').datepicker({
				autoclose: true,
				clearBtn: true,
				daysOfWeekHighlighted: "0,6",
				format: "yyyy-mm-dd",
				language: "es",
				todayBtn: true,
				todayHighlight: true,
				toggleActive: true,
				weekStart: 1,
				startDate: oAntecedentes.oVacunaCovid.CONFIG.fechainicio,
				endDate: gcHoy,
			});
			$("#selVacunaCovid").attr("data-codigo",oAntecedentes.oVacunaCovid.CONFIG.codigo).attr("data-nombre",oAntecedentes.oVacunaCovid.CONFIG.descrip);
			$("#lblVacunaCovid").text(oAntecedentes.oVacunaCovid.CONFIG.titulo);
			var laInicio = oAntecedentes.oVacunaCovid.CONFIG.fechainicio.split('-');
			oAntecedentes.dInicioCovid = new Date(laInicio[0],laInicio[2]-1,laInicio[3])

			var lcOpciones = "<option></option>";
			$.each(oAntecedentes.oVacunaCovid.OPCIONES, function(lcCodigo, lcOpcion){
				lcOpciones += '<option value="'+lcCodigo+'">'+lcOpcion+'</option>';
			});

			$("#selVacunaCovid").html(lcOpciones).val('').on("change",function(){
				$(this).removeClass('is-invalid');
				var lcCodigo = $(this).val();
				$("#selLabVacuna,#selDosisVacuna").html('');
				$("#selLabVacuna,#selDosisVacuna,#fechaVacuna").val('').attr('disabled',true);
				if (lcCodigo=="SI"){
					var lcOpciones = "<option></option>";
					$.each(oAntecedentes.oVacunaCovid.TIPOS, function(lcCodTipo, loTipo){
						lcOpciones += '<option value="'+lcCodTipo+'">'+loTipo.titulo+'</option>';
					});
					$("#selLabVacuna").html(lcOpciones).attr('disabled',false);
				}
			});

			$("#selLabVacuna").on("change",function(){
				var lcTipoVacuna = $(this).val();
				$("#selDosisVacuna").html('');
				$("#selDosisVacuna,#fechaVacuna").val('').attr('disabled',true);
				if (lcTipoVacuna!==""){
					var lcOpciones = "<option></option>";
					$.each(oAntecedentes.oVacunaCovid.TIPOS[lcTipoVacuna].dosis, function(lnNum, lcDosis){
						lcOpciones += '<option value="'+lnNum+'">'+lcDosis+'</option>';
					});
					$("#selDosisVacuna").html(lcOpciones);
					$("#selDosisVacuna,#fechaVacuna").attr('disabled',false);
				}
			});

			oAntecedentes.IniciarTabla();

			// Adicionar últimas vacunas registradas
			if(oAntecedentes.oVacunaCovid.CONFIG.obtener=='SI'){
				var laVC=oAntecedentes.cDatVacunaCovid.split('|'),
					aDataVacCvd=[], lnId=1;
				$.each(laVC, function(lnKey,lcVC){
					if(lcVC.length>0){
						var loVacCov=JSON.parse(lcVC);
						loVacCov.vacuna=oAntecedentes.oVacunaCovid.CONFIG.descrip;
						loVacCov.vacunac=oAntecedentes.oVacunaCovid.CONFIG.codigo;
						loVacCov.id=lnId++;
						aDataVacCvd.push(loVacCov);
					}
				});
				$("#tblVacunas").bootstrapTable('append', aDataVacCvd);
				oAntecedentes.nNumVacuna=lnId;
			}

			$("#btnAddVacuna").on("click",function(e){
				e.preventDefault();

				// Validar información
				if($("#selVacunaCovid").val().length>0){
					if($("#selVacunaCovid").val()=="SI"){
						if($("#selLabVacuna").val().length==0){
							$("#selLabVacuna").focus();
							fnAlert("Debe indicar el Laboratorio.");
							return;
						}
						if($("#selDosisVacuna").val().length==0){
							$("#selDosisVacuna").focus();
							fnAlert("Debe indicar la Dosis que fue administrada.");
							return;
						}
						if($("#fechaVacuna").val().length>0){
							var laFec = $("#fechaVacuna").val().split("-"),
								ldFec = new Date(laFec[0], laFec[1]-1, laFec[2]);
							if(ldFec>gdFechaHoy){
								$("#fechaVacuna").focus();
								fnAlert("Fecha no puede ser superior a la fecha actual.");
								return;
							}
							if(ldFec<oAntecedentes.dInicioCovid){
								$("#fechaVacuna").focus();
								fnAlert("Fecha no puede ser inferior al "+oAntecedentes.dInicioCovid+".");
								return;
							}
						}
					}
				}else{
					$("#selVacunaCovid").focus();
					fnAlert("Debe indicar si al paciente le fue aplicada la vacuna.");
					return;
				}

				loVacuna = {
					vacuna:	$("#selVacunaCovid").attr("data-nombre"),
					vacunac:$("#selVacunaCovid").attr("data-codigo"),
					aplica:	$("#selVacunaCovid option:selected").text(),
					aplicac:$("#selVacunaCovid").val(),
					labrt:	$("#selLabVacuna option:selected").text(),
					labrtc:	$("#selLabVacuna").val(),
					dosis:	$("#selDosisVacuna option:selected").text(),
					dosisc:	$("#selDosisVacuna").val(),
					fecha:	$("#fechaVacuna").val()
				}

				// Valida si ya existe el registro
				var lbInsertar=true, lbCambiaRta=false, lnIndice=-1;
				$loFilas=$("#tblVacunas").bootstrapTable('getData');
				$.each($loFilas, function(lnClave, loFila){
					if (loVacuna.vacunac==loFila.vacunac &&
						loVacuna.aplicac==loFila.aplicac &&
						loVacuna.dosisc==loFila.dosisc)
					{
						if(oAntecedentes.oVacunaCovid.CONFIG.validarlab=="SI"){
							if(loVacuna.labrtc==loFila.labrtc){
								lbInsertar=false;
								lnIndice=lnClave;
								loVacuna.id = loFila.id;
							}
						}else{
							lbInsertar=false;
							lnIndice=lnClave;
							loVacuna.id = loFila.id;
						}
						return;
					}
					lbCambiaRta=!(loVacuna.aplicac==loFila.aplicac);
				});
				$("#selVacunaCovid").focus();

				if(lbCambiaRta){
					var lcMsg='Al adicionar este registro se eliminarán los registros existentes.<br><b>¿Desea continuar?</b>';
					fnConfirm(lcMsg, "Vacuna Covid19", false, false, false,
						function(){
							$("#tblVacunas").bootstrapTable('removeAll');
							loVacuna.id = oAntecedentes.nNumVacuna++;
							$("#tblVacunas").bootstrapTable('append',[loVacuna]);
							$("#selVacunaCovid").val("").change();
						}
					);
				}else{
					if(lbInsertar){
						loVacuna.id = oAntecedentes.nNumVacuna++;
						$("#tblVacunas").bootstrapTable('append',[loVacuna]);
						$("#selVacunaCovid").val("").change();
					}else{
						var lcMsg='Registro para <b>'+(oAntecedentes.oVacunaCovid.CONFIG.validarlab=="SI"?loVacuna.labrt+' - ':'')+loVacuna.dosis+'</b> ya existe.<br><b>¿Desea modificarlo?</b>';
						fnConfirm(lcMsg, "Vacuna Covid19", false, false, false,
							function(){
								$("#tblVacunas").bootstrapTable('updateRow',{
									index: lnIndice,
									row: loVacuna
								});
								$("#selVacunaCovid").val("").change();
							}
						);
					}
				}
			});

		} else {
			$("#selVacunaCovid").attr("disabled",true);
			$("#divVacunaCovid").hide();
		}
	},

	IniciarTabla: function()
	{	$("#tblVacunas").bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-dark',
			locale: 'es-ES',
			undefinedText: '-',
			height: '150',
			pagination: false,
			columns: [
				{
					title: 'Vacuna',
					field: 'vacuna'
				},{
					title: 'Aplicada',
					field: 'aplica'
				},{
					title: 'Laboratorio',
					field: 'labrt'
				},{
					title: 'Dosis',
					field: 'dosis'
				},{
					title: 'Fecha',
					field: 'fecha'
				},{
					title: '',
					field: 'acciones',
					formatter: "oAntecedentes.vacunasFormato",
					events: "oAntecedentes.vacunasEventos"
				} 
			]
		});

	},
	vacunasFormato: function(lcValue, loFila, lnIndice){
		return [
			'<a class="delAntecVacunas" href="javascript:void(0)" title="Eliminar">',
			'<i class="fa fa-trash"></i>',
			'</a>'
		].join('');
	},

	vacunasEventos: {
		'click .delAntecVacunas': function (e, lcValue, loFila, lnIndice) {
			$("#tblVacunas").bootstrapTable('remove', {
				field: 'id',
				values: [loFila.id]
			});
		}
	}

};
