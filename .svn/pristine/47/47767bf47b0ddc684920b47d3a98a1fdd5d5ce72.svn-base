var oModalSesionHcWeb = false,
	aModalSHCWAbiertos = [];
window.onfocus = validarSesion;

function modalSesionHcWeb(){
    if (typeof oModalSesionHcWeb == "object") {
        if (oModalSesionHcWeb.isOpen()) {
			return;
        }
    }
	var laListaModal = $(".modal");
	aModalSHCWAbiertos = [];
	$.each(laListaModal, function(lnLlave,loModal){
		if ($(loModal).is(":visible")) {
			aModalSHCWAbiertos.push($(loModal).attr("id"));
			$(loModal).modal("hide");
		}
	});
    oModalSesionHcWeb = $.confirm({
		icon: 'fas fa-info-circle',
		title: 'Inicio de sesion',
		content: function () {
			var self = this;
			return $.ajax({
				url: "vista-comun/sesion.php",
				dataType: "html"
			}).done(function (tcHtml) {
				self.setContent(tcHtml);
				oModalSesionHcWeb.buttons.Aceptar.hide();
				if (typeof oModalEspera === 'object') {
					oModalEspera.ocultar();
				}
			}).fail(function () {
				self.setContent('<div class="alert alert-danger" role="alert">Se presentó un error al mostrar ventana para iniciar sesión.<br>Inicie sesion en una nueva pestaña para evitar perder los datos.</div>');
			});
		},
		type: 'red',
		columnClass: 'l',
		closeIcon: false,
		animateFromElement: false,
		smoothContent: false,
		animationSpeed: 200,
		buttons: {
			Aceptar: function(Aceptar){}
		},
		onOpen: function(){$('body').css({overflow: 'hidden'});},
		onClose: function(){
			$('body').css({overflow: ''});
			$.each(aModalSHCWAbiertos, function(lnLlave,lcIdModal){
				$("#"+lcIdModal).modal("show");
			});
		},
    });
}
function validarSesion() {
    var lcUsr = sessionStorage.getItem('userhcweb');
    if (lcUsr.length == 0) {
        return true;
    }
    $.ajax({
        url: "vista-comun/ajax/sesioncre.php",
        type: "post",
        data: {
            usrhcw: sessionStorage.getItem('userhcweb')
        },
        dataType: "json"
    }).done(function (toRta) {
        if (typeof toRta.sesion !== 'undefined') {
            if (toRta.sesion == false) {
                console.log(toRta.error);
                modalSesionHcWeb();
            }
        } else {
            fnAlert('Ocurrió un problema y no se pudo validar la sesion de HC Web');
            modalSesionHcWeb();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText);
        fnAlert('Se presentó un error al validar la sesión de HC Web.');
    });
}
function getUsuarioSesionHcWeb() {
    return btoObj(sessionStorage.getItem('userhcweb'));
}
function aceptarSesionHcWeb(toForm, toPass) {
    toPass.attr('readonly', true);
    divAlertSesionHcWeb("secondary", 'Espere por favor ... <i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;"></i>');
    $.ajax({
        type: "POST",
        url: "vista-comun/ajax/sesion.php",
        data: toForm.serializeAll(),
        dataType: "json"
    }).done(function (loDatos) {
        if (typeof loDatos.error !== 'undefined') {
            if (loDatos.error.length > 0) {
                divAlertSesionHcWeb("danger", loDatos.error);
                toPass.attr('readonly', false);
                return;
            }
        }
        if (typeof loDatos.sesion !== 'undefined') {
            if (loDatos.sesion == 2) {
                toForm.hide();
                divAlertSesionHcWeb("primary", "Sesión de Usuario correcta, puede continuar.");
                try {
                    setTimeout(function () {
                        oModalSesionHcWeb.close();
                    }, 500);
                } catch (err) {
                    console.log(err);
                }
            } else if (loDatos.sesion == 1) {
                divAlertSesionHcWeb("danger", "Tipo y Especialidad no pudieron ser validados.");
                toPass.attr('readonly', false);
            }
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log(jqXHR.responseText);
        divAlertSesionHcWeb("danger", "Se presentó un error al iniciar sesión. Intente nuevamente por favor.");
        toPass.attr('readonly', false);
    });
}
function divAlertSesionHcWeb(tcAlert, tcMsg) {
    $("#divReportStatusSesionHcWeb").html('<hr/><div class="alert alert-' + tcAlert + '" role="alert">' + tcMsg + '</div>');
}
