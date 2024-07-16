var $loTablePerfiles = $('#tablePerfiles');
var $loTableUsuarioTipos = $('#tableUsuarioTipos');
var $loTableUsuarioEspecialidades = $('#tableUsuarioEspecialidades');
var $loTableUsuarioOpciones = $('#tableUsuarioOpciones');
var $loTableLista = $('#tableLista');
var $loWindowEspecialidadAgregar = $('#windowEspecialidadAgregar');

function consultarLista(tcTipo){
	$.ajax({
		type: 'get',
		cache: false,
		url: 'vista-usuarios/consultas_json',
		data: {p: tcTipo},
		dataType: 'json'
	})
	.done(function(loData) {
		try {
			$loTableLista.bootstrapTable({data: loData.rows});
		} catch(err) {
			alert('No se pudo cargar la lista.');
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
		alert("Se presentó un error al cargar la lista. \n"+jqXHR.responseText+" "+textStatus);
	});
}

function procesarUsuarioOpciones(tcId, name, tipo){
	switch(tipo) {
		case 'add':
			if($.inArray(tcId, ['CREUSUCU','CONPERSA','CONPERBO','CONPERCC','USUCAJCL','OPTIPHAB','OPTIPEPI','OPCHGESP','OPESPALT','OPINFENT','OPTTIPPR','OPCUSUPA','OPCUSUFC','OPCUESAU','OPCENUSU','OPPROACT','OPORDHOS','OPNPSMPR','OPEXPFIL','OPCGPPEH','OPEAPCLH'])>=0){
				
				switch(tcId) {
					case 'CREUSUCU': // CREAR USUARIO CUENTAS MEDICAS
						$('#windowLista label').html('Rol');
						$('#windowLista select').empty();
						$loTableLista.bootstrapTable('destroy');
						
						$.each([{'REV':'Revisor'},{'AUD':'Auditor'},{'FAC':'Facturador'}], function( lnKey, laValue ) {
							$.each(laValue, function( lcKey, lcValue ) {
								$('#windowLista select').append('<option value="' + lcKey + '">' + lcValue + '</option>');
							});
						});
						consultarLista('lista-'+tcId);
						
						$('#buttonModalListaGuardar').unbind("click").click(function(){	
							var lcRol = $('#windowLista select').children("option:selected").val();
							var laListaAux = $.map($loTableLista.bootstrapTable('getSelections'), function (row) {
								return row.ID;
							});

							lvIndex = laUsuarioOpciones.findIndex((obj => obj.ID == tcId));
							laUsuarioOpciones[lvIndex].VALUE = {TIPO:lcRol, PERMISOS: laListaAux};

							$('#windowLista').modal('hide');
						});
						
						$('#windowLista').modal('show');
						break;

					case 'OPCUSUPA': // OPCIONES DE USUARIO PARA TRASLADAR CONSUMOS ENTRE PACIENTES DE TRASPLANTES
						$('#windowLista').modal('show');
						break;

					case 'CONPERSA': // CON PERMISOS A SALAS
						break;

					case 'CONPERCC': // CON PERMISOS A CENTROS DE COSTOS
						break;

					case 'CONPERBO': // CON PERMISOS A BODEGAS
						break;

					case 'USUCAJCL': // USUARIO CAJERO EN LA CLÍNICA
						break;

					case 'OPTIPHAB': // TIPO DE USUARIO PARA HABITACIONES
						break;

					case 'OPTIPEPI': // TIPO DE USUARIO PARA EPICRISIS
						break;

					case 'OPCHGESP': // CAMBIO DE ESPECIALIDAD
						break;

					case 'OPESPALT': // ESPECIALIDAD ALTERNA
						break;

					case 'OPINFENT': // ENTIDAD PREDETERMINADA EN INFORMES
						break;

					case 'OPTTIPPR': // TIPO DE USUARIO PARA PROCEDIMIENTOS Y CONSULTAS
						break;

					case 'OPCUSUFC': // TIPO DE USUARIO FACTURACION
						break;

					case 'OPCUESAU': // PERMISOS DE USUARIO TABLA CUESAU, SE CREA POR CONTINUIDAD DE PERMISOS PARA OPCIONES DE MÉTODOS NO INVASIVOS. ESPECILIDAD 124 - CARDIOLGIA NO INVASIVA
						break;

					case 'OPCENUSU': // OPCIONES DE USUARIO PARA CENSO Y BITACORA DE URGENCIAS
						break;

					case 'OPORDHOS': // OPCIONES DE USUARIO PARA ORDENES HOSPITALARIAS
						break;

					case 'OPPROACT': // ESPECIALIDADES PERMITIDAS PARA ENTREGA DE RESULTADOS
						break;

					case 'OPNPSMPR': // OPCIONES DE USUARIO PARA NOPOS Y MIPRES
						break;

					case 'OPEXPFIL': // OPCIONES DE USUARIO PARA EXPORTAR ARCHIVOS AS400
						break;
							
					case 'OPCGPPEH': // OPCIONES DE USUARIO PARA EGRESO HOSPITALARIO
						break;

					case 'OPNCENSE': // OPCIONES TIPOS DE CONSUMOS A CAMBIAR CENTRO DE SERVICIO
						break;
							
					case 'OPEAPCLH': // OPCIONES DE USUARIO PARA CONSULTA DE ENTIDADES		
						break;
					
					default:
						// code block
				}
			}
			break;
		
		case 'remove':
			try {
				lvIndex = laUsuarioOpciones.findIndex((obj => obj.ID == tcId));
				laUsuarioOpciones[lvIndex].VALUE = null;
				console.log(laUsuarioOpciones[lvIndex]);
			} catch(err) {
				alert('No se encontró el indice de opción');
			}			
			break;
		
		default:
			// code block
	}	
}

function procesarUsuarioTipos(tcId, name, tipo) {
	$f=$('#tipoUsuario');
	if(tipo==='add'){
		$f.append('<option value="'+tcId+'">'+name+'</option>');
		$f.find('option[value="'+$f.data('tipo')+'"]').attr("selected", "selected");
	}else if(tipo==='remove'){
		$f.find('option[value="'+tcId+'"]').remove();
	}else{
		$f.empty();
	}
}

function showPerfiles() {
	$f=$('#seleccionPerfiles');
	$f.html('');
	$.each(laPerfiles, function(index, value) {
		$f.append('<span class="badge badge-light">'+value+'</span> ');
	});	
}

function agregarEspecialidades(){
	$e = $('#tipoUsuarioAgregar');
	$f = $('#especialidad');
	$g = $('#tipoUsuarioNivelAgregar');
	
	lnTipoId = parseInt($e.find("option:selected").val());
	lcTipoNombre = $e.find("option:selected").text();
	lnEspecialidadId = parseInt($f.find("option:selected").val());
	lcEspecialidadNombre = $f.find("option:selected").text();
	lcNivel = $g.find("option:selected").text();
	
	llExiste=false;
	
	if(lnTipoId>0){
		if (jQuery.inArray(lcNivel, ['PRINCIPAL', 'SECUNDARIA', 'ADICIONAL'])>-1){	
			if ((jQuery.inArray(lcNivel, ['SECUNDARIA', 'ADICIONAL'])>-1 && jQuery.inArray(lnTipoId, [1, 12, 13])>-1) || lcNivel=='PRINCIPAL'){
				lnIdenticos=0;
				lnId=0;
				
				$.each(laEspecialidades, function(index, row) {
					if (lcNivel == $.trim(row.NIVEL) && lnTipoId == row.TIPOID) {
						llExiste=true;
					}
					
					if(lcNivel == $.trim(row.NIVEL) && lnTipoId == row.TIPOID && lnEspecialidadId==row.ESPECIALIDADID){
						lnIdenticos+=1;
					}
					lnId = (row.ID>lnId?row.ID:lnId);
				});		
				lnId+=1;
				
				
				if(llExiste==false || $.trim(lcNivel)=='ADICIONAL'){
					if(lnIdenticos==0){
						laEspecialidades.push({"ID":lnId,"TIPOID":lnTipoId,"TIPONOMBRE":lcTipoNombre,"ESPECIALIDADID":lnEspecialidadId,"ESPECIALIDADCODIGO":"","ESPECIALIDADNOMBRE":lcEspecialidadNombre,"NIVEL":lcNivel});
						$loTableUsuarioEspecialidades.bootstrapTable('load', laEspecialidades);
						$loWindowEspecialidadAgregar.modal('hide');
					}else{
						alert('Ya existe '+lnIdenticos+' registro(s) idéntico(s)');
					}
				}else{
					alert('Ya se creo la especialidad '+lcNivel+' para el tipo de usuario '+lcTipoNombre);
				}
			} else {
				alert('No se puede agregar una especialidad '+lcEspecialidadNombre+' para el tipo de usuario '+lcTipoNombre);
			}
		} else {
			alert('Tipo de especialidad '+lcNivel+' no valida');
		}
	}
}

function quitarEspecialidades(){
	var laEspecialidadesAux = $.map($loTableUsuarioEspecialidades.bootstrapTable('getSelections'), function (row) {
		return row.ID;
	});
	
	$loTableUsuarioEspecialidades.bootstrapTable('remove', {
		field: 'ID',
		values: laEspecialidadesAux
	});
	
	$('#confirmEspecialidadQuitar').modal('hide');
}

  
$(function() {
	$loTableUsuarioEspecialidades.bootstrapTable({data: laEspecialidades});
	
	$loTablePerfiles
		.bootstrapTable('destroy')
		.bootstrapTable({
				locale: 'es-ES',
				columns: [
							[
								{checkbox: true},
								{field: 'NAME',title: 'Perfil (Descripci&oacute;n)'},
								{field: 'ID', title: 'ID', visible: false},								
							]
						]
		})
		.on('check.bs.table', function (e, row) {
			lnCuenta=0;
			$.each(laPerfiles, function(index, value) {
				if (value === row.ID) {
					lnCuenta+=1;
				}
			});	
			if(lnCuenta<=0){
				laPerfiles.push(row.ID);
			}
			showPerfiles();
		})
		.on('uncheck.bs.table', function (e, row) {
			$.each(laPerfiles, function(index, value) {
				if (value === row.ID) {
					laPerfiles.splice(index,1);
				}
			});
			showPerfiles();
		})		
		.on('search.bs.table load-success.bs.table', function () {
			$loTablePerfiles.bootstrapTable('checkBy', {field: 'ID', values: laPerfiles});
		});
		
	$loTableUsuarioTipos
		.bootstrapTable('destroy')
		.bootstrapTable({
				locale: 'es-ES',
				columns: [
							[
								{checkbox: true},
								{field: 'NAME',title: 'Tipo de Usuario'},
								{field: 'ID', title: 'ID', visible: false},								
							]
						]
		})
		.on('check.bs.table', function (e, row) {
			lnCuenta=0;
			$.each(laUsuarioTipos, function(index, value) {
				if (value === row.ID) {
					lnCuenta+=1;
				}
			});	
			if(lnCuenta<=0){
				laUsuarioTipos.push(row.ID);
			}
			procesarUsuarioTipos(row.ID, row.NAME, 'add');
		})
		.on('uncheck.bs.table', function (e, row) {
			$.each(laUsuarioTipos, function(index, value) {
				if (value === row.ID) {
					laUsuarioTipos.splice(index,1);
				}
			});
			procesarUsuarioTipos(row.ID, row.NAME, 'remove');
		})
		.on('check-all.bs.table', function (a, b) {
			procesarUsuarioTipos(0,0,'clear');
			lnCuenta=0;
			$.each(b, function(index, row) {
				$.each(laUsuarioTipos, function(c, value) {
					if (value === row.ID) {
						lnCuenta+=1;
					}			
				});
				procesarUsuarioTipos(row.ID, row.NAME, 'add');
			});
			if(lnCuenta<=0){
				laUsuarioTipos.push(row.ID);
			}			
		})
		.on('uncheck-all.bs.table', function () {
			procesarUsuarioTipos(0,0,'clear');
			laUsuarioTipos.splice();
		})		
		.on('search.bs.table load-success.bs.table', function () {
			$loTableUsuarioTipos.bootstrapTable('checkBy', {field: 'ID', values: laUsuarioTipos});
		});

	$loTableUsuarioOpciones
		.bootstrapTable('destroy')
		.bootstrapTable({
				locale: 'es-ES',
				columns: [
							[
								{checkbox: true},
								{field: 'NAME',title: 'Opci&oacute;n de Usuario'},
								{field: 'ID', title: 'ID', visible: true},
								{field: 'TYPE',title: 'Tipo', visible: false},
								{field: 'VALUE',title: 'Valor', visible: false},
								{field: 'STATE',title: 'Estado', visible: false},							
							]
						]
		})
		.on('check.bs.table', function (e, row) {
			if($(this).data('cargado')==true){
				lnCuenta=0;
				$.each(laUsuarioOpciones, function(index, value) {
					if (value === row.ID) {
						lnCuenta+=1;
					}
				});	
				if(lnCuenta<=0){
					laUsuarioOpciones.push(row.ID);
				}
				procesarUsuarioOpciones(row.ID, row.NAME, 'add');
			}
		})
		.on('uncheck.bs.table', function (e, row) {
			if($(this).data('cargado')==true){
				$.each(laUsuarioOpciones, function(index, value) {
					if (value === row.ID) {
						laUsuarioOpciones.splice(index,1);
					}
				});
				procesarUsuarioOpciones(row.ID, row.NAME, 'remove');
			}
		})
		.on('check-all.bs.table', function (a, b) {
			alert('No disponible');			
		})
		.on('uncheck-all.bs.table', function () {
			procesarUsuarioOpciones(0,0,'clear');
			alert('No disponible');	
		})		
		.on('search.bs.table load-success.bs.table', function () {
			$(this).data('cargado',false);
			var f = [];
			$.each(laUsuarioOpciones, function(index, value) {
				f.push(value.ID);
			});	
			$loTableUsuarioOpciones.bootstrapTable('checkBy', {field: 'ID', values: f});
			$(this).data('cargado',true);
		});
		
	 	
	$('#vigencia .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1,
	});
	$('#usuarioArea').change(function() {
		$('#usuarioCargo')
			.data('area',$(this).find("option:selected").data('id'))
			.data('tipo',$(this).find("option:selected").data('tipo'))
			.selectUsuarioCargo();
	});	
	$loWindowEspecialidadAgregar.on('show.bs.modal', function(e) {
		$f=$('#tipoUsuarioAgregar');
		$f.empty();
		$('#tipoUsuario > option').each(function() {
			$f.append('<option value="' + $(this).val() + '">' + $(this).text() + '</option>');
		});		
	});		
	$('#buttonModalEspecialidadAgregar').click(function() {
		agregarEspecialidades();
	});
	$('#buttonModalEspecialidadQuitar').click(function() {
		quitarEspecialidades();
	});
	$('.bootstrap-table').each(function() {
		$(this).find('.input-group').addClass('input-group-sm');
	});	

	$('#signArea').signaturePad({
		drawOnly:true, 
		drawBezierCurves:true, 
		lineTop:200, 
		penColour : '#000000', 
		penCap: 'butt'
	});
	
	$("#buttonModalFirmaGuardar").click(function(e){
		html2canvas(document.getElementById("sign-pad"), {
			onrendered: function (canvas) {
				lcFirmaCanvas = canvas.toDataURL('image/png');
				$('.firmaPadPreview > img').attr('src',lcFirmaCanvas);
				$('.firmaPadPreview > input').empty().val(lcFirmaCanvas.replace(/^data:image\/(png|jpg);base64,/, ""));
				$('#windowFirma').modal('hide');
			}
		});	
	
	});	

	$("#buttonUsuarioGuardar").click(function(e){	
		lcFirma=$('.firmaPadPreview .input').val();
		
		$.ajax({
			type: 'post',
			cache: false,
			url: 'vista-usuarios/guardarUsuario',
			data: {firma: lcFirma},
			dataType: 'json'
		})
		.done(function(loUsuario) {
			try {
				if (loUsuario.error == ''){
					alert(loUsuario.RESULTADO);
				} else {
					alert(loUsuario.error);
				}
			} catch(err) {
				alert('No se pudo guardar el usuario.');
			}
		})
		.fail(function(jqXHR, textStatus, errorThrown) {
			alert("Se presentó un error al guardar el usuario. \n"+jqXHR.responseText+" "+textStatus);
		});
	});

	$("#windowFoto").on('show.bs.modal', function(e) {
		try {
			getFoto('#buttonModalFotoGuardar','.fotoPreview > img','.fotoPreview > input','#windowFoto',148,196);		
		} catch(err) {
			alert('No se pudo cargar la cámara '+err);
		}			
	});

	
	$('#pillsFirma').on('shown.bs.tab', function (e) {
		$('.firmaHtmlPreview textarea').val($.trim($('.firmaHtmlModelo').html()));
	});	

	$('#bitacora-tab').on('shown.bs.tab', function (e) {
		$f = $('#bitacoraHistory');
		if($f.data('loaded')!='loaded'){
			$f.empty();
			$.ajax({
				type: 'get',
				cache: false,
				url: 'vista-usuarios/consultas_json',
				data: {p: 'bitacora', q: $('#usuario').data('q')},
				dataType: 'json'
			})
			.done(function(loUsuario) {
				try {
					$f.html(loUsuario.content);
					$f.data('loaded','loaded');
				} catch(err) {
					alert('No se pudo cargar la bitácora de usuario.');
				}
			})
			.fail(function(jqXHR, textStatus, errorThrown) {
				alert("Se presentó un error al cargar la bitácora de usuario. \n"+jqXHR.responseText+" "+textStatus);
			});
		}
	});		
	
	$('.firmaHtmlExportar').click(function(){
		var loLink = document.createElement('a');
		loLink.setAttribute('download', 'firma.txt');
		loLink.setAttribute('href', 'data:text/plain'  +  ';charset=utf-8,' + $('.firmaHtmlPreview textarea').val());
		loLink.click(); 
	});

	$('.firmaHtmlCopiar').click(function(){
        $('.firmaHtmlPreview textarea').select();
        document.execCommand("copy");
		console.log("copiar");
	});	
});
