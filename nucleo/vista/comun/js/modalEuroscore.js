var oModalEuroscore = {
	lcTitulo : 'Calculo Euroscore',
	fnEjecutar: false,
	nEdad : 0,
	cSexo : '',
	aEuroscoreDatos : {},
	aEuroscoreGrupos : {},
	cEuroScoreAditivo : '',
	cEuroScoreLogistico : '',
	cEuroScoreTotalAditivo : '',
	
	inicializar: function()
	{
		this.nEdad = parseInt(aDatosIngreso['aEdad']['y']);
		this.cSexo = aDatosIngreso['cSexo'];
		$('#txtEdadEuroscore').val(this.nEdad);
		
		$('#txtEdadEuroscore').on('change',function(){
			oModalEuroscore.nEdad = $(this).val();
			oModalEuroscore.calcularEuroscore();
		});
		
		$('#txtEdadEuroscore').on('change',function(){
			oModalEuroscore.cSexo = $(this).val();
			oModalEuroscore.calcularEuroscore();
		});
		$('#chkMenos30Uci').on("click",function(){oModalEuroscore.inhabilitaMayor30()});
		$('#chkFac3050Uci').on("click",function(){oModalEuroscore.inhabilitaMenor30()});
		
		$('.check-euroscore').on('click',function(){
			oModalEuroscore.calcularEuroscore();
		});
		this.obtenerDatosEuroscore();
	},
	
	resultadoFinalEuroscore: function(){
		lcResultadoGrupo = 'Grupo=' + $('#txtGrupoEuroScore').val()+', ';
		lcResultadoMortalidad = 'Mortalidad esperada ' + $('#txtMortalidadEsperadaEuroScore').val();
		lcResultado = oModalEuroscore.cEuroScoreTotalAditivo + '~'+ oModalEuroscore.cEuroScoreAditivo+oModalEuroscore.cEuroScoreLogistico+lcResultadoGrupo+lcResultadoMortalidad;
		return lcResultado;
	},
	
	inhabilitaMayor30: function(){
		if($(chkMenos30Uci).prop('checked')){
			$('#chkFac3050Uci').prop('checked', false);
			lnCodigo = 11;
			oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = 0;
		}
	},	
	
	inhabilitaMenor30: function(){
		if($(chkFac3050Uci).prop('checked')){
			$('#chkMenos30Uci').prop('checked', false);
			lnCodigo = 12;
			oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = 0;
		}
	},	
	
	obtenerDatosEuroscore: function(){
		$.ajax({
			url : 'vista-comun/ajax/modalEuroscore.php',
			data : {accion : 'listasEuroscore'},
			type : 'POST',
			dataType : 'json'
		})
		.done(function(loDatos){
		try{
			$.each(loDatos.datosEuroscore, function( lcKey, loElement ) {
				oModalEuroscore.aEuroscoreDatos[loElement.CODIGO] = {
						Descripcion : (loElement.DESCRP),
						PUNTOS : (loElement.PUNTOS),
						TOTALPUNTOS : parseInt(loElement.TOTAL),
						BETA : (loElement.BETA),
					};
			});
			
			$.each(loDatos.gruposEuroscore, function( lcKey, loElement ) {
				oModalEuroscore.aEuroscoreGrupos[loElement.CODIGO] = {
						Grupo : (loElement.GRUPO),
						Mortal : (loElement.MORTAL),
						VrMinimo: parseInt(loElement.MINIMO),
						VrMaximo: parseInt(loElement.MAXIMO),
					};
			});
			
			loSelect = $("#selSexoEuroscore");
			loSelect.empty();
			loSelect.append('<option value=""></option>');
			$.each(loDatos.generospacientes, function( lcKey, loTipo ) {
				var lcOption = '<option value="' + loTipo.CODIGO + '">' + loTipo.DESCRIPCION + '</option>';
				loSelect.append(lcOption);
			});
				
			oModalEuroscore.calcularEuroscore();
			}catch(err){
				fnAlert('No se puede realizar la busqueda datos Euroscore.', '', 'fas fa-exclamation-circle','red','medium');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown){
			console.log(jqXHR.responseText);
			fnAlert('Se presento un error al realizar la busqueda datos Euroscore.', '', 'fas fa-exclamation-circle','red','medium');
		});
	},
	
	calcularEuroscore: function()
	{
		this.calculoEuroScoreAditivo();
		this.calculoEuroScoreLosgistico();
	},
		
	calculoEuroScoreAditivo: function(){
		lnValorEdad = (this.nEdad<60 ? 0: parseInt(((this.nEdad-55)/5)));
		lnIndice = 1;
		oModalEuroscore.aEuroscoreDatos[lnIndice].TOTALPUNTOS = lnValorEdad;
		
		lnValorSexo = (this.cSexo=="F" ? 1: 0);
		lnIndice = 2;
		oModalEuroscore.aEuroscoreDatos[lnIndice].TOTALPUNTOS = lnValorSexo;
		
		$('.check-euroscore').each(function(lnIndice, loElemento){
			lnCodigo = $(this).attr('data-id');
			lbActivo = $(this).prop('checked');
			oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = lbActivo ? parseInt(oModalEuroscore.aEuroscoreDatos[lnCodigo].PUNTOS): 0;
		});
		
		var lnTotalAditivo = 0;
		var lcDescripcionGrupo ='';
		var lcMortalidadEsperada ='';
		
		$.each(oModalEuroscore.aEuroscoreDatos, function(lnIndex, loElemento){
			lnTotalAditivo += loElemento.TOTALPUNTOS;
		});
		$.each(oModalEuroscore.aEuroscoreGrupos, function(lnIndex, loElemento){
			if(lnTotalAditivo >= loElemento.VrMinimo && lnTotalAditivo <= loElemento.VrMaximo){
				lcDescripcionGrupo = loElemento.Grupo;
				lcMortalidadEsperada = loElemento.Mortal;
			}
		});
		$('#txtEuroScoreAditivo').val(lnTotalAditivo);
		$('#txtGrupoEuroScore').val(lcDescripcionGrupo);
		$('#txtMortalidadEsperadaEuroScore').val(lcMortalidadEsperada);
		oModalEuroscore.cEuroScoreTotalAditivo = lnTotalAditivo.toString();
		oModalEuroscore.cEuroScoreAditivo = 'Aditivo='+lnTotalAditivo.toString()+', ';
	},
	
	calculoEuroScoreLosgistico: function(){
		var lnTotalLosgistico = lnEuroScore = lnExponente = 0;
		var lcResultadoLosgistico = '';
		lnCodigo = 1;
		lnValorEdad = (this.nEdad<59 ? 1: ((this.nEdad-58) * oModalEuroscore.aEuroscoreDatos[lnCodigo].BETA));
		oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = lnValorEdad;
		lnValorSexo = (this.cSexo=="F" ? 1: 0);
		lnCodigo = 2;
		oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = lnValorSexo * oModalEuroscore.aEuroscoreDatos[lnCodigo].BETA;
		
		$('.check-euroscore').each(function(lnIndice, loElemento){
			lnCodigo = $(this).attr('data-id');
			lbActivo = $(this).prop('checked');
			oModalEuroscore.aEuroscoreDatos[lnCodigo].TOTALPUNTOS = lbActivo ? 1 * oModalEuroscore.aEuroscoreDatos[lnCodigo].BETA: 0;
		});
		
		$.each(oModalEuroscore.aEuroscoreDatos, function(lnIndex, loElemento){
			lnTotalLosgistico += loElemento.TOTALPUNTOS;
		});
		lnExponente = Math.exp( lnTotalLosgistico - 4.789594)
		lnEuroScore = (lnExponente / ( 1 + lnExponente ))*100;
		lcResultadoLosgistico = lnEuroScore.toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2}).trim() + '%';
		$('#txtEuroScoreLogistico').val(lcResultadoLosgistico); 
		oModalEuroscore.cEuroScoreLogistico = 'Logistico='+lcResultadoLosgistico+', ';
	},
	
	mostrar: function(tfEjecutar)
	{
		$('#selSexoEuroscore').val(this.cSexo);
		$("#divEuroscore").modal('show');
		oModalEuroscore.fnEjecutar = tfEjecutar;
	},
	
	ocultar: function()
	{
		$("#divEuroscore").modal('hide');
		
		if (typeof oModalEuroscore.fnEjecutar==='function'){
			oModalEuroscore.fnEjecutar();
		}
	}

}