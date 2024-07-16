var oFormulas = {
	URL: "vista-comun/ajax/formulas.php",
	validaNum: (tnNum, tnDefault=0) => (typeof parseFloat(tnNum)=='number' ? (isNaN(parseFloat(tnNum)) ? tnDefault : parseFloat(tnNum)) : tnDefault),

	/*
	 *	Cálculo del índice de superficie corporal
	 *	@param funcion tfFuncionPost: funcion que se debe ejecutar cuando retorne el valor
	 *	@param integer tnPeso: peso en kg
	 *	@param integer tnTalla: talla en cm, no es obligatorio
	 *	@param string tcMetodo: método a usar, no es obligatorio
	 */
	SuperficieCorporal: function(tfFuncionPost, tnPeso, tnTalla, tcMetodo)
	{
		var lnPeso = oFormulas.validaNum(tnPeso), lnTalla = oFormulas.validaNum(tnTalla);
		if (lnPeso<=0) return 0;
		this.getCalculo(tfFuncionPost, {formula:'SuperficieCorporal', peso:lnPeso, talla:lnTalla, metodo:tcMetodo});
	},

	/*
	 *	Cálculo del índice de masa corporal
	 *	@param funcion tfFuncionPost: funcion que se debe ejecutar cuando retorne el valor
	 *	@param integer tnPeso: peso en kg
	 *	@param integer tnTalla: talla en cm
	 */
	IMC: function(tfFuncionPost, tnPeso, tnTalla)
	{
		var lnPeso = oFormulas.validaNum(tnPeso), lnTalla = oFormulas.validaNum(tnTalla);
		if (lnPeso<=0 || lnTalla<=0) return 0;
		this.getCalculo(tfFuncionPost, {formula:'IMC', peso:lnPeso, talla:lnTalla});
	},

	/*
	 *	Cálculo del índice de peso ideal
	 *	@param funcion tfFuncionPost: funcion que se debe ejecutar cuando retorne el valor
	 *	@param integer tnTalla: talla en cm, no es obligatorio
	 *	@param string tcSexo: F, FEMENINO, M, MASCULINO
	 *	@param string tcMetodo: método a usar, no es obligatorio
	 */
	PesoIdeal: function(tfFuncionPost, tnTalla, tcSexo, tcMetodo)
	{
		var lnTalla = oFormulas.validaNum(tnTalla);
		if (lnTalla<=0) return 0;
		this.getCalculo(tfFuncionPost, {formula:'PesoIdeal', talla:lnTalla, sexo:tcSexo, metodo:tcMetodo});
	},

	/*
	 *	Cálculo del índice de peso ajustado
	 *	@param funcion tfFuncionPost: funcion que se debe ejecutar cuando retorne el valor
	 *	@param integer tnPeso: peso en kg
	 *	@param integer tnPesoIdeal: peso ideal en kg
	 *	@param string tcMetodo: método a usar, no es obligatorio
	 */
	PesoAjustado: function(tfFuncionPost, tnPeso, tnPesoIdeal)
	{
		var lnPeso = oFormulas.validaNum(tnPeso), lnPesoIdeal = oFormulas.validaNum(tnPesoIdeal);
		if (lnPeso<=0 || lnPesoIdeal<=0) return 0;
		this.getCalculo(tfFuncionPost, {formula:'PesoAjustado', peso:lnPeso, pesoideal:lnPesoIdeal});
	},

	/*
	 *	Cálculo del índice de peso ideal y el peso ajustado
	 *	@param funcion tfFuncionPost: funcion que se debe ejecutar cuando retorne el valor
	 *	@param integer tnPeso: peso en kg
	 *	@param integer tnTalla: talla en cm, no es obligatorio
	 *	@param string tcSexo: F, FEMENINO, M, MASCULINO
	 *	@param string tcMetodo: método a usar, no es obligatorio
	 */
	PesoIdealAjustado: function(tfFuncionPost, tnPeso, tnTalla, tcSexo, tcMetodo)
	{
		var lnPeso = oFormulas.validaNum(tnPeso), lnTalla = oFormulas.validaNum(tnTalla);
		if (lnPeso<=0 || lnTalla<=0) return 0;
		this.getCalculo(tfFuncionPost, {formula:'PesoIdealAjustado', peso:lnPeso, talla:lnTalla, sexo:tcSexo, metodo:tcMetodo});
	},

	getCalculo: function(tfFuncionPost, toDatosEnviar)
	{
		$.post(oFormulas.URL, toDatosEnviar, function(loDatos) {
			if(loDatos.error.length>0){
				fnAlert(loDatos.error);
			} else {
				tfFuncionPost(loDatos.valor);
			}
		}, "json")
		.fail(function(jqXHR) {
			console.log(jqXHR.responseText);
			fnAlert("Error al calcular "+toDatosEnviar.formula);
		});
	}
};
