$(document).ready(function(e){
	$(function(){
		$('#divFecha .input-group.date').datepicker({
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
	});
});
