var oInterpretaExam = {
	resultado: {
		1: "Normal",
		2: "Anormal"
	},
	lcMensajeError: '',
	existeAmbulatorio: false,

	inicializar: function(){
		this.iniciarForm();
		this.iniciarTabla();
		$('#btnIntrExamAdd').on('click', this.adicionarIE);
	},

	validacion: function(){
		return true;
	},

	obtenerDatos: function(){
		return $("#tblIntrExam").bootstrapTable('getData');
	},

	fechaActual: function(){
		var ldFecha = new Date();
		return ldFecha.getFullYear().toString() + '-' + (ldFecha.getMonth() + 1).toString().padStart(2,'0') + '-' + ldFecha.getDate().toString().padStart(2,'0');
	},

	adicionarIE: function(e){
		e.preventDefault();
		if ($('#formIntrExamEx').valid()) {

			var loInterpreta = $('#tblIntrExam').bootstrapTable('getData'),
				lcProc = $('#txtIntrExamProc').val().trim().toUpperCase(),
				lbExiste = false;

			// valida si es existe cup
			if (oInterpretaExam.existeAmbulatorio) {
				$.each(oAmbulatorio.datosProcedimiento, function(lcValor, lcClave) {
					if (lcProc == lcValor) {
						lbExiste = true;
						return false;
					}
				});
			}
			if (lbExiste) {
				var lnGuion = lcProc.indexOf('-'),
					lcCup = lcProc.substr(0,lnGuion).trim(),
					lnIndice = loInterpreta.findIndex(loElement => loElement.cup === lcCup);
				lcProc = lcProc.substr(lnGuion+1).trim();
			} else {
				var lnIndice = loInterpreta.findIndex(loElement => loElement.procedimiento === lcProc),
					lcCup = '';
			}
			var loFila = {
				fecha: oInterpretaExam.fechaActual(),
				cup: lcCup,
				procedimiento: lcProc,
				resultado: oInterpretaExam.resultado[ $('#selIntrExamResult').val() ],
				codresult: $('#selIntrExamResult').val(),
				interpreta: $('#edtIntrExam').val()
			}

			// validar que no exista el procedimiento
			if (lnIndice>=0){
				fnConfirm('Procedimiento ya fue ingresado, ¿Desea modificarlo?', false, false, false, false,
					{
						text: 'Si',
						action: function(){
							// Modificar interpretación
							$('#tblIntrExam').bootstrapTable('updateRow', {
								index: lnIndice,
								row: loFila
							});
							$("#formIntrExamEx").trigger('reset');
							$('#txtIntrExamProc').focus();
						}
					},
					{ text: 'No' }
				);
			} else {
				// Adicionar interpretación
				$('#tblIntrExam').bootstrapTable('append', [loFila]);
				$("#formIntrExamEx").trigger('reset');
				$('#txtIntrExamProc').focus();
			}
		}
	},

	iniciarForm: function(){
		$.each(this.resultado, function(lcClave, lcValor){
			$('#selIntrExamResult').append('<option value="' + lcClave + '">' + lcValor + '</option>');
		});
		$('#formIntrExamEx').validate({
			rules: {
				txtIntrExamProc: "required",
				selIntrExamResult: "required",
				edtIntrExam: {
					required: function(e){
						return $('#selIntrExamResult').val()=='2';
					}
				}
			},
			onkeyup: false,
			onclick: false,
			errorClass: "is-invalid",
			validClass: "is-valid",
			errorPlacement: function (error, element) {
				error.addClass("invalid-tooltip");

				if (element.prop("type") === "radio") {
					error.insertAfter(element.parent("label") );
				} else {
					error.insertAfter(element);
				}
			}
		});
	},

	iniciarTabla: function (){
		$('#tblIntrExam').bootstrapTable({
			classes: 'table table-bordered table-hover table-striped table-sm table-responsive-sm',
			theadClasses: 'thead-light',
			locale: 'es-ES',
			undefinedText: 'N/A',
			toolbar: '#toolBarLstIntrExam',
			height: '300',
			pagination: false,
			columns: [
			{
				title: 'Fecha',
				field: 'fecha',
				valign: 'middle'
			},{
				title: 'Procedimiento',
				field: 'procedimiento',
				valign: 'middle',
				sortable: true
			},{
				title: 'Resultado',
				field: 'resultado',
				align: 'center',
				valign: 'middle',
				sortable: true
			},{
				title: 'Interpretación',
				field: 'interpreta',
				valign: 'middle',
				sortable: false
			},{
				title: 'Eliminar',
				align: 'center',
				clickToSelect: false,
				events: this.eventoEliminarIE,
				formatter: this.formatoEliminarIE
			} ]
		});
	},

	eventoEliminarIE: {
		'click .eliminarIntrExam': function (toElemento, tcValor, toFila, tnIndice) {
			fnConfirm('¿Desea eliminar el procedimiento?', false, false, false, false, function(){
				$('#tblIntrExam').bootstrapTable('remove', {
					field: 'procedimiento',
					values: [toFila.procedimiento]
				});
			}, false);
		}
	},

	formatoEliminarIE: function(){
		return '<a class="eliminarIntrExam" href="javascript:void(0)" title="Eliminar"><i class="fa fa-trash-alt" style="color:#E96B50"></i></a>'
	},

	validaExisteAmbulatorio: function(){
		oInterpretaExam.existeAmbulatorio = false;
		if (typeof oProcedimientos === 'object') {
			if (typeof oProcedimientos.datosProcedimiento === 'object') {
				oInterpretaExam.existeAmbulatorio = true;
			}
		}
	}
}
