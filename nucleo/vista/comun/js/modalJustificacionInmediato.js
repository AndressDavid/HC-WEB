var oModalJustificacioInmediato = {
	lcTitulo: 'Justificacion Inmediato',
	gcUrlAjax: 'vista-comun/ajax/modalJustificacioInmediato.php',
	gaDatosIniciales:'', gnIndex:'', gcJustificacion:'',

	inicializar: function()
	{
		$('#txtJustificacionInmediato').on('keyup',function(){
			var lcJustificacion = $("#txtJustificacionInmediato").val().trim();
			oModalJustificacioInmediato.cantidadTextoJustificacion(lcJustificacion);
		});
		$('#btnGuardaJustificacionInmediato').on('click', this.validarJusInmediato);
		$('#btnCancelarJustificacionInmediato').on('click', this.cancelarJusInmediato); 
	},

	cantidadTextoJustificacion: function(tcJustificacion)
	{
		var lnCaracteres = 0;
		if (tcJustificacion==''){
			lcTextoCaracteres = '(0/' + oMedicamentosOrdMedica.gnCantidadMinInmediato + ')';
			loCantidadJustificacion = $('#lblCaracteresJustificacionInmed');
			loCantidadJustificacion.text(lcTextoCaracteres);
		}else{	
			lnCaracteres = $("#txtJustificacionInmediato").val().length;
			loCantidadJustificacion = $('#lblCaracteresJustificacionInmed');
			lcTextoCaracteres = '(' + lnCaracteres + '/' + oMedicamentosOrdMedica.gnCantidadMinInmediato + ')';
			loCantidadJustificacion.text(lcTextoCaracteres);
		}
		if (lnCaracteres==0 || (lnCaracteres<oMedicamentosOrdMedica.gnCantidadMinInmediato)){
			$('#lblCaracteresJustificacionInmed').addClass("text-danger").removeClass("text-primary");
		}else{
			$('#lblCaracteresJustificacionInmed').addClass("text-primary").removeClass("text-danger");
		}
	},

	validarJusInmediato: function () {
		var lcJustificacion=$("#txtJustificacionInmediato").val();
		oModalJustificacioInmediato.gcJustificacion=lcJustificacion;
		var lnCaracteres=$("#txtJustificacionInmediato").val().length;
		var laDatos=oModalJustificacioInmediato.gaDatosIniciales;
		
		if (lnCaracteres==0 || (lnCaracteres<oMedicamentosOrdMedica.gnCantidadMinInmediato)){
			var lcTextomensaje='La justificación debe ser mínimo de ' + oMedicamentosOrdMedica.gnCantidadMinInmediato +' caracteres. <br> Revise por favor.';
			fnAlert(lcTextomensaje, oModalJustificacioInmediato.lcTitulo, false, false, 'medium');
			$('#txtJustificacionInmediato').focus();
			return false;
		}
		oModalJustificacioInmediato.ocultar();
		lnEstado=(laDatos['ESTDET']=='14' || laDatos['ESTDET']=='99') ? 12 : laDatos['ESTDET'];
		
		if (oModalJustificacioInmediato.gaDatosIniciales.CONTROLADO!=''){
			oModalMedicamentoControlado.mostrar(oModalJustificacioInmediato.gaDatosIniciales,'',oModalJustificacioInmediato.gnIndex,lnEstado,oModalJustificacioInmediato.gaDatosIniciales.ACEPTACAMBIO);		
		}else{
			oMedicamentosOrdMedica.marcarFila(oModalJustificacioInmediato.gnIndex, 1, 1, 0, 3, lnEstado, laDatos['ACEPTACAMBIO'],lcJustificacion);
		}
	},	

	cancelarJusInmediato: function () {
		var laDatos=oModalJustificacioInmediato.gaDatosIniciales;
		oMedicamentosOrdMedica.marcarFila(oModalJustificacioInmediato.gnIndex, 0, 0, 0, laDatos['COLORORG'], laDatos['ESTDETORIG'], laDatos['ACEPTACAMBIO'],'');
		oModalJustificacioInmediato.ocultar();
	},
	
	mostrar: function(tnIndex,taDatos,tcMedicamentos)
	{
		$('#txtJustificacionInmediato').focus();
		lcTextoCaracteres = '(0/' + oMedicamentosOrdMedica.gnCantidadMinInmediato + ')';
		$('#lblCaracteresJustificacionInmed').text(lcTextoCaracteres);
		oModalJustificacioInmediato.gnIndex=tnIndex;
		oModalJustificacioInmediato.gaDatosIniciales=taDatos;
		$("#divJustificacionInmediato").modal('show');
		$('#txtMedicamentoJustificar').html(tcMedicamentos);
		$('#txtJustificacionInmediato').focus();
	},
	
	ocultar: function()
	{
		$("#divJustificacionInmediato").modal('hide');
	}
}