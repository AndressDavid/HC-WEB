$(function() {
	$('#tableUsuarios').bootstrapTable('destroy').bootstrapTable({
		locale: 'es-ES',
		columns: [
					[
						{field: 'RECNO',title: 'Registro'},
						{field: 'USUARI',title: 'Usuario'}, 
						{field: 'TIDRGM', title: 'Tipo'},
						{field: 'NIDRGM', title: 'Numero'},
						{field: 'REGMED', title: 'Registro'}, 
						{field: 'NOMMED', title: 'Apellidos'}, 
						{field: 'NNOMED', title: 'Nombres'}, 
						{field: 'CTPMRGM', title: 'Tipo de Usuario'},
						{field: 'CESTRGM', title: 'Estado'},
						{field: 'CCODRGM', title: 'Especialidad'},
						{field: 'FVDRGM', title: 'V. Inicio'},
						{field: 'FVHRGM', title: 'V.Fin'},
						{field: 'EMAIL', title: 'email'},
						{field: 'LLAVE', title: 'Llave', visible: false}						
					]
				]
	});
	$('#tableUsuarios').on('click-row.bs.table', function (row, e, field) {
		$(location).attr('href','modulo-usuarios&idUsuario='+e['LLAVE']);
	});	  
	$('#documentoTipo2').tiposDocumentos();
})