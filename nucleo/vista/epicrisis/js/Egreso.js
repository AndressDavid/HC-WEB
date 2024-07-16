var oDatosEgreso = {

	ListaDxFallece : {},
	lcObjetoError : '',
	lcMensajeError : '',

	inicializar: function()
	{
		// se debe inicializar los diagnosticos fallece
		var lcTipo = aEstados['Estado']==1?'':'F';
		$('#selEstado').EstadosSalida({tipo: lcTipo, fnAfter: function() {oDatosEgreso.AdicionarSalida();}});
		$('#selCondicionEgreso').condicionDestinoEgreso(lcTipo);
		$('#selEstado').on('change',function(){
			$('#txtEstado').val($(this).val());
		});

		if(lcTipo=='F'){
			$("#divMuerteEncefalica").css("display","block");
	
			if (aEstados['CieFallece']!=''){
				this.actualizaCausaFallece();
			}else{
				oDiagnosticos.consultarDiagnostico('buscarDxFallece','cCodigoDxFallece','cDescripcionDxFallece','FA','edtCondiciones');
			}
			$('#selCondicionEgreso').attr('disabled',true);
		}else{
			$("#divFecha,#divHora,#divFallece,#divMuerteEncefalica").hide();
			$('#selCondicionEgreso').attr('disabled',false);
		}
	},

	actualizaCausaFallece: function()
	{
		$('#buscarDxFallece').attr('disabled',true);
		$("#cCodigoDxFallece").val(aEstados['CieFallece']);
		$("#cDescripcionDxFallece").val(aEstados['DescripcionCieFallece']);
		$('#FechaFallece').attr("readonly",true);
		$('#HoraFallece').attr("readonly",true);
		
		var ldFechaFallece = parseInt(aEstados['FecFallece']);
		$('#FechaFallece').val(strNumAFecha(ldFechaFallece));
		var ldHoraFallece = strNumAHora(parseInt(aEstados['HorFallece']+'00'), ':');
		$("#HoraFallece").val(ldHoraFallece);
	},

	// Función que adiciona el estado de salida cuando el paciente fallece
	AdicionarSalida: function()
	{
		if(aEstados['CodSalida']!=''){
			$('#selEstado').val(aEstados['CodSalida']).attr('disabled',true);
			$('#txtEstado').val(aEstados['CodSalida']);
		}
	},

	validacion: function()
	{
		var lbValido=true;
		var lcEstadoSalida = ($("#selEstado").val());
			
		if (lcEstadoSalida=='03' || lcEstadoSalida=='04' || lcEstadoSalida=='06'){
			var lcDxFallece = ($("#cCodigoDxFallece").val());
			if(lcDxFallece== ''){
				lbValido = false;
				this.lcObjetoError = "buscarDxFallece" ;
				this.lcMensajeError = 'Diagnóstico Fallece obligatorio, revise por favor.';
				return lbValido;
			}

			var lcHoraFallece = ($("#HoraFallece").val())
			if(lcHoraFallece== ''){
				lbValido = false;
				this.lcObjetoError = "HoraFallece" ;
				this.lcMensajeError = 'Hora Fallece obligatorio, revise por favor.';
				return lbValido;
			}

			var lcfechahorafallece = moment($('#FechaFallece').val() + ' ' + $('#HoraFallece').val() , 'YYYY/MM/DD HH:mm');
			if (moment().isAfter(lcfechahorafallece) == false){
				this.lcMensajeError = 'Dato Fecha y Hora Fallece mayor a la actual. Revise por favor!';
				this.lcObjetoError = "FechaFallece";
				lbValido = false;
				return lbValido;
			} 
			
			let lcMuerteEncefalica = $("#selMuerteEncefalica").val();
			if (lcMuerteEncefalica==''){
				lbValido = false;
				this.lcObjetoError = "selMuerteEncefalica" ;
				this.lcMensajeError = '¿El paciente tuvo signos de muerte encefálica? es obligatorio, revise por favor.';
				return lbValido;
			}
			
			let lcCondicionEgreso = $("#selCondicionEgreso").val();
			if (lcCondicionEgreso==''){
				lbValido = false;
				this.lcObjetoError = "selCondicionEgreso" ;
				this.lcMensajeError = 'Condición destino usuario egreso es obligatorio, revise por favor.';
				return lbValido;
			}
			
		}
		return lbValido;
	},

	obtenerDatos: function()
	{
		laDatosEhgreso =$('#FormEgreso').serializeAll(true);
		return $('#FormEgreso').serializeAll(laDatosEhgreso);
	}

};
