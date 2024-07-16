function queryParams() {
	var params = {};
	$('#formProgramacionSalas').find('input[name],select[name]').each(function () {
		params[$(this).attr('name')] = $(this).val();
	})
	return params;
} 	
	
$(function() {
	
	let cnTimeMaxSeg = 60*10;
	var lnTime=0;
	var lnTimeMaxSeg = cnTimeMaxSeg;	
		
	function badgeFormatter(value, row, index) {
		return '<span class="badge badge-light bg-transparent">'+value+'</span>';
	}
	
	function salaFormatter(value, row, index) {
		return '<span class="'+(row.FUTURA=='S'?'font-weight-bold text-success':'text-secondary')+'">'+row.SALA+'</span>';
	}	
	
	function fechaHoraFormatter(value, row, index) {
		return '<span class="p-0 badge badge-light bg-transparent">'+row.FECHA+'</span><br/><span class="font-weight-bold">'+row.HORA+'</span>';
	}	
	
	function pacienteFormatter(value, row, index) {
		return ['<span class="p-0 badge badge-light bg-transparent">|'+row.ID,
			    (row.INGRESO>0?row.INGRESO:' - '),
				row.EDAD,
				'</span><br/><span>'+row.NOMBRE+'</span>',row.ENTIDAD].join(' | ');
	}
	
	function cirujanoFormatter(value, row, index) {
		return ['<ul class="list-unstyled"><li>'+row.CIRUJANO+'</li>',
			    (row.CIRUJANO>0?'<li>'+row.CIRUJANO+'</li>':''),
				'</ul>'].join('');
	}
	
	function logicoFormatter(value, row, index) {
		return (value=='S'?'SI':'NO');
	}
	
	function recortarFormatter(value, row, index) {
		return value.substring(0,6);
	}
	
	function contaminadaFormatter(value, row, index) {
		return [(row.CONTAMINADA=='S'?'<i class="fas fa-biohazard text-danger"></i>':''),
			    (row.AYUDANTE=='S'?'<i class="fas fa-hand-holding-medical text-primary"></i>':'')].join(' ');
	}

	function procedimientoFormatter(value, row, index) {
		return ('<span class="p-0 badge badge-light bg-transparent">'+row.TIPOPROCEDIMIENTO+'</span><br/>'+row.PROCEDIMIENTO);
	}		
	

	function origenFormatter(value, row, index) {
		return ((row.HABITACION || (row.HABITACION.length>0 && row.HABITACION!=='0')?'<span class="p-0 badge badge-light bg-transparent">'+row.HABITACION+'</span><br/>':'')+row.ORIGEN);
	}
	
	function anestesiaFormatter(value, row, index) {
		return ('<span class="p-0 badge badge-light bg-transparent">'+row.ANESTESIA+'</span><br/>'+row.ANESTESIOLOGO);
	}	
			
	$('#tableProgramacionSalas').bootstrapTable({
		locale: 'es-ES',
		classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
		theadClasses: 'thead-light',			
		exportTypes: ['csv', 'txt', 'excel'],
		columns: [
					[
						{field: 'CONSECUTIVO', title: 'Consecutivo', sortable: true, visible: false, valign: 'middle', rowspan: 2},
						{field: 'SALA',title: 'Sala', sortable: true, formatter: salaFormatter, valign: 'middle', rowspan: 2}, 
						{field: 'FECHAHORA', title: 'Fecha/Hora', sortable: true, formatter: fechaHoraFormatter, valign: 'middle', rowspan: 2},
						{field: 'ID', title: '|Documento|Ingreso|Edad|<br/>Paciente|Entidad', sortable: true, formatter: pacienteFormatter, valign: 'middle', rowspan: 2},
						{field: 'ORIGEN', title: 'Habitaci&oacute;n<br/>Origen', sortable: true, formatter: origenFormatter, valign: 'middle', rowspan: 2}, 
						{field: 'PROCEDIMIENTO', title: 'Procedimiento', formatter: procedimientoFormatter, valign: 'middle', rowspan: 2}, 
						{field: 'LATERALIDAD', title: 'Lateralidad', sortable: true, valign: 'middle', rowspan: 2},
						{field: 'TIEMPO', title: 'Tiempo', sortable: true, valign: 'middle', rowspan: 2},
						{field: 'CIRUJANO', title: 'Cirujano', sortable: true, formatter: cirujanoFormatter, valign: 'middle', rowspan: 2},
						{field: 'ANESTESIA', title: 'Anestesia', sortable: true, formatter: anestesiaFormatter, valign: 'middle', rowspan: 2},
						{field: 'DISPOSITIVO', title: 'Dispositivo', valign: 'middle', rowspan: 2},
						{field: 'AUTORIZADA', title: 'Autorizada', sortable: true, valign: 'middle', align: 'center', rowspan: 2},
						{title: 'Otros', formatter: contaminadaFormatter, valign: 'middle', rowspan: 2},
						{field: 'CONTAMINADA', title: '<i class="fas fa-biohazard"></i>', sortable: true, visible: false, formatter: logicoFormatter, valign: 'middle', rowspan: 2},
						{field: 'AYUDANTE', title: ' <i class="fas fa-hand-holding-medical"></i>', sortable: true, visible: false, formatter: logicoFormatter, valign: 'middle', rowspan: 2},
						{field: 'EQUIPOS', title: 'Equipos', sortable: true, visible: false, valign: 'middle', rowspan: 2},
						{title: 'Detalle Paciente', colspan: 5, align: 'center'},						
					],
					[
						{field: 'ID', title: 'Documento', sortable: true, visible: false, formatter: badgeFormatter},
						{field: 'INGRESO', title: 'Ingreso', sortable: true, visible: false, formatter: badgeFormatter},
						{field: 'EDAD', title: 'Edad', sortable: true, visible: false, formatter: badgeFormatter},
						{field: 'NOMBRE', title: 'Nombre', sortable: true, visible: false, formatter: badgeFormatter},					
						{field: 'ENTIDAD', title: 'Entidad', sortable: true, visible: false, formatter: badgeFormatter}					
					]
				]
	});
	$('.fixed-table-body').css('min-height','480px');
	
	setInterval(function() {
		if(lnTimeMaxSeg>0){
			if((lnTimeMaxSeg-lnTime)>0){
				lnTime+=1;
				$('.messageActualizar').html((lnTimeMaxSeg-lnTime));
			}else{
				lnTime=0;
				$('#tableProgramacionSalas').bootstrapTable('refresh');
			}
		}
	}, 1000);

	$( "#cmdActualizar" ).click(function() {
		if(lnTimeMaxSeg>0){
			lnTimeMaxSeg = 0;
			$('#cmdActualizarIco').removeClass('fa-spin');
		}else{
			lnTimeMaxSeg = cnTimeMaxSeg;
			$('#cmdActualizarIco').addClass('fa-spin');
		}
	});

	$('#btnProgramacionSalasBuscar').click(function () {
		$('#tableProgramacionSalas').bootstrapTable('refresh');
	});

	$('#formProgramacionSalas .input-group.date').datepicker({
		autoclose: true,
		clearBtn: true,
		daysOfWeekHighlighted: "0,6",
		format: "yyyy-mm-dd",
		language: "es",
		todayBtn: true,
		todayHighlight: true,
		toggleActive: true,
		weekStart: 1
	});	
})