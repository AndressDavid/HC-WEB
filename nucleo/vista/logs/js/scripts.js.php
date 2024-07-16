<?php
	require_once (__DIR__ .'/../../../publico/constantes.php');
	require_once (__DIR__ .'/../../../publico/headJSCRIPT.php');
?>
hljs.registerLanguage("errores", function(hljs) {
	var LITERALS = { literal: 'PHP' };
	var TYPES = [ hljs.QUOTE_STRING_MODE, ]; //hljs.C_NUMBER_MODE
	var COMMENT = { begin: '\\[', end: '\\]', className: 'comment'};
	var META = { begin: 'PHP Notice', end: '\\:', className: 'meta-notice' };
	var ATTRIBUTE = { begin: 'PHP Stack trace', end: '\\:', className: 'attribute'};
	var NOTE = { begin: '\\--->', end: '\\<---', className: 'meta-note'};
	var QUERYDATABASE = { begin: 'DATABASE', end: '\\|', className: 'meta-database-error'};
	var QUERY = { begin: 'Query:', end: '\\| ', className: 'meta-query'};
	var QUERYBIN = { begin: 'bindValues:', end: '\\| ', className: 'meta-query-bin-value'};
	var QUERYERROR = { begin: 'Error:', end: '\\n', className: 'meta-query-error'};

	TYPES.splice(TYPES.length, 0, COMMENT, META, ATTRIBUTE, NOTE, QUERYDATABASE, QUERY, QUERYBIN, QUERYERROR);
	return { contains: TYPES, keywords: LITERALS, illegal: '\\S'
	};
});


$(function() {
	$('#modalVerLog').on('show.bs.modal', function (event) {
		$("#cargando").css("display","block");
		var button = $(event.relatedTarget);

		var lcMime =  button.data('mime');
		var lcSizeUnit = button.data('size-unit');
		var lcBaseName = button.data('basename');
		var lcCreateTimeDate = button.data('ctime-date');
		var lcModifyTimeDate = button.data('mtime-date');

		var loModal = $(this);
		loModal.find('#cMime').text(lcMime);
		loModal.find('#cSizeUnit').text(lcSizeUnit);
		loModal.find('#cBaseName').text(lcBaseName);
		loModal.find('#cCreateTimeDate').text(lcCreateTimeDate);
		loModal.find('#cModifyTimeDate').text(lcModifyTimeDate);

		$.ajax({
			type: 'GET',
			url: "vista-logs/ajax/logs.ajax?accion=cargar&cBaseName="+lcBaseName,
		})
		.done(function(response) {
			$("#cargando").css("display","none");
			loModal.find('#cLog').text(response);
			$('pre code').each(function(i, block) {
				hljs.highlightBlock(block);
			});
		})
		.fail(function(data) {
			$("#cargando").css("display","none");
			loModal.find('#cLog').html('<div class="alert alert-danger" role="alert">Error al cargar el log</div>');
		});

	});

	$('#modalVerLog').on('hidden.bs.modal', function (event) {
		var loModal = $(this);
		loModal.find('#cLog').empty();
	});

	$('.descargar').click(function() {
		var lcBaseName = $(this).data('basename');
		window.open("vista-logs/ajax/logs.ajax?accion=descargar&cBaseName="+lcBaseName, '_blank');
	});
});