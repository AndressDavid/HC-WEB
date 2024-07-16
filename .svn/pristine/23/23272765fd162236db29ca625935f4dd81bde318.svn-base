var oCabDatosPac = {
	cMenuTab:'',
	bEnviarTop:true,

	inicializar: function(tcMenuTab)
	{
		if (typeof tcMenuTab == 'string') if (tcMenuTab.length>0) this.cMenuTab=tcMenuTab;
		this.topCabecera();
		this.cargarDatosCabecera();
	},

	cargarDatosCabecera: function ()
	{
		$('.CabDatosPac .lblNombre').append(aDatosIngreso['cNombre']);
		$('.CabDatosPac .lblVia').append(aDatosIngreso['cDesVia']);
		$('.CabDatosPac .lblPesoEncabezado').append(aDatosIngreso['cPesoUnidad']);
		$('.CabDatosPac .lblGenero').append(aDatosIngreso['cDescSexo']);
		$('.CabDatosPac .lblcHabitacion').append(aDatosIngreso['cSeccion']+' - ' + aDatosIngreso['cHabita']);
		$('.CabDatosPac .lblEdad').append(aDatosIngreso['aEdad']['y']+'A '+aDatosIngreso['aEdad']['m']+'M '+aDatosIngreso['aEdad']['d']+'D');
		$('.CabDatosPac .lblIngreso').append(aDatosIngreso['nIngreso']);
		$('.CabDatosPac .lblHistoria').append(aDatosIngreso['nHistoria']);
		$('.CabDatosPac .lblDNI').append(aDatosIngreso['cTipId']+' - '+aDatosIngreso['nNumId']);
	},

	topCabecera: function()
	{
		$(".CabDatosPac").css('z-index', this.bEnviarTop ? 10 : 3);
		if (this.cMenuTab.length>0){
			$("#"+this.cMenuTab)
				.css("top", $(".CabDatosPac").outerHeight()+15)
				.css("z-index", $(".CabDatosPac").css('z-index')-1);
		}
	}
}
$(window).resize(function() {
	oCabDatosPac.topCabecera();
});