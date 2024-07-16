var oFrmInput={
	cForm:'formFrmInput',
	oForm:null,
	oFiltro:{},
	fnAceptar:null,
	fnCancelar:null,
	oCtrl:null,
	oValidador:null,
	oReglas:null,
	bHayDate:false,

	limpiarForm: function(taForm, tfAceptar, tfCancelar)
	{
		$('#'+oFrmInput.cForm).html('');
		oFrmInput.oFiltro={};
		oFrmInput.fnAceptar=null;
		oFrmInput.fnCancelar=null;
	},

	crearForm: function(taForm, tfFuncionAceptar, tfFuncionCancelar)
	{
		oFrmInput.dialog = $.confirm({
			title: taForm.Opciones.Titulo,
			content: '<form id="'+oFrmInput.cForm+'" name="'+oFrmInput.cForm+'" class="needs-validation small"></form>',
			onContentReady: function(){
				oFrmInput.oForm=$('#'+oFrmInput.cForm);
				oFrmInput.oCtrl=taForm.Controles;
				var lcControles=oFrmInput.crearControles();
				oFrmInput.adicionarValidacion();
			},
			type: 'blue',
			columnClass: 'l',
			theme: 'bootstrap',
			animateFromElement: false,
			smoothContent: false,
			animationSpeed: 50,
			onOpen: function(){
				$('body').css({overflow: 'hidden'});
			},
			onClose: function(){
				$('body').css({overflow: ''});
			},
			buttons: {
				Aceptar: {
					text: 'Aceptar',
					btnClass: 'btn-blue',
					keys: ['enter'],
					action: function(btnAceptar){
						if(oFrmInput.validar()){
							if(typeof tfFuncionAceptar=='function'){
								tfFuncionAceptar();
							}
							oFrmInput.dialog.close()
						}else{
							return false;
						}
					}
				},
				Cancelar: {
					text: 'Cancelar',
					btnClass: 'btn-red',
					keys: ['esc'],
					action: function(btnCancelar){
						oFrmInput.dialog.close()
						if(typeof tfFuncionCancelar==='function'){
							tfFuncionCancelar();
						}
					}
				},
			}
		});
	},

	crearControles: function()
	{
		var lcFilaIni='<div class="form-group form-group-sm row mb-1">',
			lcFilaFin='</div>';
		oFrmInput.oReglas={};
		oFrmInput.bHayDate=false;

		$.each(oFrmInput.oCtrl, function(lcClave, laControl){
			switch(laControl.tipo){
				case "C":
					lcCtrl=oFrmInput.ctrlInputText(laControl);
					break;
				case "M": case "R":
					lcCtrl=oFrmInput.ctrlTextBox(laControl);
					break;
				case "N":
					lcCtrl=oFrmInput.ctrlInputNumber(laControl);
					break;
				case "L": case "S":
					lcCtrl=oFrmInput.ctrlSelect(laControl);
					break;
				case "D":
					lcCtrl=oFrmInput.ctrlDatePicker(laControl);
					oFrmInput.bHayDate=true;
					break;
				case "T":
					lcCtrl=oFrmInput.ctrlDateTimePicker(laControl);
					break;
				case "H":
					lcCtrl=oFrmInput.ctrlTimePicker(laControl);
					break;
				case "Z":
					lcCtrl=oFrmInput.ctrlNinguno(laControl);
					break;
			}
//			if(laControl.tipo!=="Z"){
//				oFrmInput.oFiltro[laControl.variable].label=laControl.Texto;
//			}

			$('#'+oFrmInput.cForm).append(lcFilaIni+lcCtrl+lcFilaFin);
			var lbValidaWeb=true;
			var lcConstruct="constructor";
			if(typeof laControl.ValidaWeb=="string"){
				if(laControl.ValidaWeb.length>0){
					oFrmInput.oReglas[laControl.variable]=JSON.parse(laControl.ValidaWeb.replaceAll('«','"').replaceAll('»','"'));
					lbValidaWeb=false;
				}
			}
			if(laControl.Obligar=="S"){
				if(lbValidaWeb){
					oFrmInput.oReglas[laControl.variable]={required:true};
				}else{
					oFrmInput.oReglas[laControl.variable].required=true;
				}
			}
		});
		$('#'+oFrmInput.cForm).append('<div class="mb-4"></div>');
	},

	adicionarValidacion: function()
	{
		if(oFrmInput.bHayDate){
			$('#'+oFrmInput.cForm+' .input-group.date').datepicker({
				autoclose: true,
				clearBtn: true,
				daysOfWeekHighlighted: "0,6",
				format: "yyyy-mm-dd",
				language: "es",
				todayBtn: 'linked',
				todayHighlight: true,
				toggleActive: true,
				weekStart: 1
			});
		}

		// crear validación
		oFrmInput.oValidador=$('#'+oFrmInput.cForm).validate({
			rules: oFrmInput.oReglas,
			errorElement: "div",
			errorPlacement: function (loError, loElement) {
				loError.addClass("invalid-tooltip");
				if (loElement.prop("type")==="checkbox") {
					loError.insertAfter(loElement.parent("label"));
				} else {
					loError.insertAfter(loElement);
				}
			},
			highlight: function(loElement, lcErrorClass, lcValidClass) {
				$(loElement).addClass("is-invalid").removeClass("is-valid");
			},
			unhighlight: function(loElement, lcErrorClass, lcValidClass) {
				$(loElement).addClass("is-valid").removeClass("is-invalid");
			},
		});
	},

	ctrlInputText: function(taCtrl)
	{
		var lcId='frmInput_'+taCtrl.variable;
		var lcOpc=typeof taCtrl.OpcionesWeb == 'string' ? taCtrl.OpcionesWeb.trim().replaceAll('«','"').replaceAll('»','"') : '';
		return '<label for="'+lcId+'" class="col-sm-5 col-form-label">'+taCtrl.Texto+'</label>'
			+'<div class="col-sm-7">'
				+'<input type="text" class="form-control form-control-sm" id="'+lcId+'" name="'+taCtrl.variable+'" value="'+taCtrl.ValorxDefecto+'" '+lcOpc+'>'
			+'</div>';
	},

	ctrlInputNumber: function(taCtrl)
	{
		var lcId='frmInput_'+taCtrl.variable;
		return '<label for="'+lcId+'" class="col-sm-5 col-form-label">'+taCtrl.Texto+'</label>'
			+'<div class="col-sm-7">'
				+'<input type="number" class="form-control form-control-sm" id="'+lcId+'" name="'+taCtrl.variable+'" value="'+taCtrl.ValorxDefecto+'">'
			+'</div>';
	},

	ctrlTextBox: function(taCtrl)
	{
		var lcId='frmInput_'+taCtrl.variable;
		return '<label for="'+lcId+'" class="col-sm-5 col-form-label">'+taCtrl.Texto+'</label>'
			+'<div class="col-sm-7">'
				+'<textarea class="form-control form-control-sm" id="'+lcId+'" name="'+taCtrl.variable+'" value="'+taCtrl.ValorxDefecto+'"></textarea>'
			+'</div>';
	},

	ctrlSelect: function(taCtrl)
	{
		var lcId='frmInput_'+taCtrl.variable;
		var laOpcs=taCtrl.Lista.split("|"),
			lcOpcs=taCtrl.ValorxDefecto=="" ? '<option></option>': '';
		$.each(laOpcs, function(lcKey, lcOpc){
			var laOpc=lcOpc.split("~"),
				lcSel=taCtrl.ValorxDefecto==laOpc[0] ? " selected " : "";
			lcOpcs+='<option value="'+laOpc[0]+'"'+lcSel+'>'+(laOpc.length>1 ? laOpc[1] : laOpc[0])+'</option>';
		});
		return '<label for="'+lcId+'" class="col-sm-5 col-form-label">'+taCtrl.Texto+'</label>'
			+'<div class="col-sm-7">'
			+'<select class="form-control form-control-sm" id="'+lcId+'" name="'+taCtrl.variable+'">'
				+lcOpcs
			+'</select></div>';
	},

	ctrlDatePicker: function(taCtrl)
	{
		var lcId='frmInput_'+taCtrl.variable;
		return '<label for="'+lcId+'" class="col-sm-5 control-label">'+taCtrl.Texto+'</label>'
			+'<div class="col-sm-7 input-group date">'
				+'<div class="input-group-prepend">'
					+'<span class="input-group-addon input-group-text"><i class="fas fa-calendar-alt"></i></span>'
				+'</div>'
				+'<input type="text" class="form-control form-control-sm" id="'+lcId+'" name="'+taCtrl.variable+'" value="'+taCtrl.ValorxDefecto.replace('/','-').replace('/','-')+'">'
			+'</div>';
	},

	ctrlDateTimePicker: function(taCtrl)
	{
		var lcCtrl = '';
		return lcCtrl;
	},

	ctrlTimePicker: function(taCtrl)
	{
		var lcCtrl = '';
		return lcCtrl;
	},

	ctrlNinguno: function(taCtrl)
	{
		return '<div class="col-sm-12">'+taCtrl.Texto+'</div>';
	},

	validar: function()
	{
		lbRta=$('#'+oFrmInput.cForm).valid();
		return lbRta;
	},

	obtenerDatos: function()
	{
		var laFormData=$('#'+oFrmInput.cForm).serializeAll(true);
		oFrmInput.oFiltro = {};
		$.each(oFrmInput.oCtrl, function(lcClave, loControl){
			oFrmInput.oFiltro[loControl.variable]={titulo: loControl.Texto};
			if(loControl.tipo=='L' || loControl.tipo=='S'){
				oFrmInput.oFiltro[loControl.variable].valor=$('#frmInput_'+loControl.variable+' option:selected').text();
			} else {
				oFrmInput.oFiltro[loControl.variable].valor=laFormData[loControl.variable];
			}
			switch(loControl.tipo){
				case "D":
					laFormData[loControl.variable]=laFormData[loControl.variable].replace('-','').replace('-','');
					break;
			}
		});
		return laFormData;
	},

	mostrar: function()
	{
		oFrmInput.dialog.open();
	},

	ocultar: function()
	{
		oFrmInput.dialog.close();
	},
}