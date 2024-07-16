/*
 *  Retorna formato string de una fecha
 *
 *	@param date tdFecha: fecha a convertir
 *	@param string tcSep: separador, por defecto -. Si viene string vacío retorna formato YYYYmmdd
 *	@return string fecha en formato YYYY-mm-dd
 */
function strDateAFecha(tdFecha, tcSep) {
   tcSep = typeof tcSep == "string" ? tcSep : "-";
   return (
      tdFecha.getFullYear() +
      tcSep +
      (tdFecha.getMonth() + 1).toString().padStart(2, "0") +
      tcSep +
      tdFecha.getDate().toString().padStart(2, "0")
   );
}
/*
 *  Da formato de fecha a un número enviado como YYYYmmdd
 *
 *	@param integer-string tcNum: Número a convertir
 *	@param string tcSep: separador, por defecto -
 *	@param string tcNA: valor a retornar si tcNum=='0'
 *	@return string fecha en formato YYYY-mm-dd
 */
function strNumAFecha(tcNum, tcSep, tcNA) {
   tcSep = tcSep ? tcSep : "-";
   tcNA = tcNA ? tcNA : "";
   try {
      tcNum = typeof tcNum === "string" ? tcNum : tcNum.toString();
      return tcNum == "0"
         ? tcNA
         : tcNum.substr(0, 4) +
         tcSep +
         tcNum.substr(4, 2) +
         tcSep +
         tcNum.substr(6, 2);
   } catch (error) {
      return "";
   }
}
/*
 *  Sumar días a una fecha
 *
 *	@param Date tdFecha: Fecha a la que se va a sumar los días
 *	@param integer tnDias: número de días a sumar
 *	@return Date fecha resultante
 */
function sumarDiasFecha(tdFecha, tnDias) {
   tdFecha.setDate(tdFecha.getDate() + tnDias);
   return tdFecha;
}
/*
 *	Da formato de hora a un número enviado como HHmmss
 *
 *	@param integer-string tcNum: número a convertir
 *	@param string tcSep: separador, por defecto :
 *	@return string hora en formato HH:mm:ss
 */
function strNumAHora(tcNum, tcSep) {
   tcSep = tcSep ? tcSep : ":";
   tcNum = (typeof tcNum === "string" ? tcNum : tcNum.toString()).padStart(
      6,
      "0"
   );
   return (
      tcNum.substr(0, 2) + tcSep + tcNum.substr(2, 2) + tcSep + tcNum.substr(4, 2)
   );
}

/*
 *	Convertir la primera letra de cada palabra a mayúsculas
 */
function ucwords(tcTexto) {
   return tcTexto.replace(
      /^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g,
      function ($1) {
         return $1.toUpperCase();
      }
   );
}

/*
 *	Validar número de identificación
 */
function valNumeroId(tnNumero) {
   loRE = new RegExp(/^\d{1,13}$/);
   return loRE.exec(tnNumero) !== null;
}

/*
 *	Muestra mensaje de alerta en un div
 *	Ej: infoAlert($('#divInfo'), '<b>Error</b>: ocurrió un error', 'danger', true)
 *
 *	@param object toDiv: referencia al objeto div info
 *	@param string tcHtml: texto html a mostrar en el info
 *	@param string tcClase: primary, secondary, success, danger, warning, info, light, dark (predeterminado primary)
 *	@param string tcIcon: ícono font-awesome ej exclamation-triangle, exclamation-circle, bell
 *	@param boolean tbClear: predeterminado true. Si es true limpia primero el divInfo
 */
function infoAlert(toDiv, tcHtml, tcClase, tcIcon, tbClear) {
   if (tbClear) infoAlertClear(toDiv);
   tcClase = tcClase ? tcClase : "primary";
   tcIcon = tcIcon ? tcIcon : "";
   var lcIcon = tcIcon == "" ? "" : '<i class="fas fa-' + tcIcon + '"></i> ';
   var lcHtml =
      '<div class="row alert alert-' +
      tcClase +
      '" role="alert">' +
      lcIcon +
      tcHtml +
      "</div>";
   toDiv.append(lcHtml);
}
/*
 *	Limpiar el div info
 *	Ej: infoAlertClear($('#divInfo'))
 *
 *	@param object toDiv: referencia al objeto div info
 */
function infoAlertClear(toDiv) {
   toDiv
      .html("")
      .removeClass("alert alert-warning alert-danger")
      .removeAttr("role");
}

/*
 *  Muestra cuadro de alerta
 *  Requiere que esté cargado el complemento jquery-confirm
 *
 *	@param string tcMensaje: texto html a mostrar en el modal dialog
 *	@param string tcTitulo: texto en el título del modal dialog (predeterminado 'Alerta')
 *	@param string tcIcon: ícono font-awesome (predeterminado 'fas fa-exclamation-circle')
 *	@param string tcType: default, blue, red, green, orange, purple, dark (predeterminado 'red')
 *	@param string tcWidth: ancho del mensaje xsmall (xs), small (s), medium (m), large (l), xlarge (xl) (predeterminado 'small')
 *				Admite clases bootstrap: 'large' = 'col-md-8 col-md-offset-2' (https://craftpip.github.io/jquery-confirm/#custom-width)
 *	@param object toBotonOk: función que se ejecutará al hacer clic en Aceptar / Objeto con propiedades del botón
 *	@param object toOpciones: opciones adicionales o que reemplazan la funcionalidad predeterminada
 */
function fnAlert(
   tcMensaje,
   tcTitulo,
   tcIcon,
   tcType,
   tcWidth,
   toBotonOk,
   toOpciones
) {
   tcTitulo = tcTitulo ? tcTitulo : "Alerta";
   tcIcon = tcIcon ? tcIcon : "fas fa-exclamation-circle";
   tcType = tcType ? tcType : "red";
   tcWidth = tcWidth ? tcWidth : "small";
   toOpciones = typeof toOpciones === "object" ? toOpciones : {};
   var loBotonOK = $.extend(
      { keys: ["enter"] },
      typeof toBotonOk === "function"
         ? { action: toBotonOk }
         : typeof toBotonOk === "object"
            ? toBotonOk
            : {}
   );
   var loOpciones = $.extend(
      {
         icon: tcIcon,
         title: tcTitulo,
         content: tcMensaje,
         type: tcType,
         columnClass: tcWidth,
         closeIcon: false,
         animateFromElement: false,
         smoothContent: false,
         animationSpeed: 50,
         escapeKey: "Cancelar",
         onOpen: function () {
            $("body").css({ overflow: "hidden" });
         },
         onClose: function () {
            $("body").css({ overflow: "" });
         },
      },
      toOpciones,
      { buttons: { Aceptar: loBotonOK } }
   );
   return $.confirm(loOpciones);
}
function fnInformation(
   tcMensaje,
   tcTitulo,
   tcIcon,
   tcType,
   tcWidth,
   toBotonOk,
   toOpciones
) {
   tcTitulo = tcTitulo ? tcTitulo : "Información";
   tcIcon = tcIcon ? tcIcon : "fas fa-info-circle";
   tcType = tcType ? tcType : "blue";
   return fnAlert(
      tcMensaje,
      tcTitulo,
      tcIcon,
      tcType,
      tcWidth,
      toBotonOk,
      toOpciones
   );
}
/*
 *  Muestra mensaje de confirmación
 *  Requiere que esté cargado el complemento jquery-confirm
 *
 *	@param string tcMensaje: texto html a mostrar en el modal dialog
 *	@param string tcTitulo: texto en el título del modal dialog (predeterminado 'Confirmar')
 *	@param string tcIcon: ícono font-awesome (predeterminado 'fas fa-question-circle')
 *	@param string tcType: default, blue, red, green, orange, purple, dark (predeterminado 'blue')
 *	@param string tcWidth: ancho del mensaje xsmall, small, medium, large, xlarge (predeterminado 'small')
 *	@param object toBotonOk: función que se ejecutará después de Aceptar / objeto con las propiedades del botón
 *	@param object toBotonCancel: función que se ejecutará después de Cancelar / objeto con las propiedades del botón
 *	@param object toOpciones: opciones adicionales o que reemplazan la funcionalidad predeterminada
 */
function fnConfirm(
   tcMensaje,
   tcTitulo,
   tcIcon,
   tcType,
   tcWidth,
   toBotonOk,
   toBotonCancel,
   toOpciones
) {
   tcTitulo = tcTitulo ? tcTitulo : "Confirmar";
   tcIcon = tcIcon ? tcIcon : "fas fa-question-circle";
   tcType = tcType ? tcType : "blue";
   tcWidth = tcWidth ? tcWidth : "small";
   toOpciones = typeof toOpciones === "object" ? toOpciones : {};
   var loBotonOK = $.extend(
      { keys: ["enter"] },
      typeof toBotonOk === "function"
         ? { action: toBotonOk }
         : typeof toBotonOk === "object"
            ? toBotonOk
            : {}
   );
   var loBotonCancel =
      typeof toBotonCancel === "function"
         ? { action: toBotonCancel }
         : typeof toBotonCancel === "object"
            ? toBotonCancel
            : {};
   var loOpciones = $.extend(
      {
         icon: tcIcon,
         title: tcTitulo,
         content: tcMensaje,
         type: tcType,
         columnClass: tcWidth,
         closeIcon: false,
         animateFromElement: false,
         smoothContent: false,
         animationSpeed: 50,
         escapeKey: "Cancelar",
         onOpen: function () {
            $("body").css({ overflow: "hidden" });
         },
         onClose: function () {
            $("body").css({ overflow: "" });
         },
      },
      { buttons: { Aceptar: loBotonOK, Cancelar: loBotonCancel } },
      toOpciones
   );
   return $.confirm(loOpciones);
}
/*
 *  Muestra mensaje de diálogo informativo
 *  Requiere que esté cargado el complemento jquery-confirm
 *
 *	@param string tcMensaje: texto html a mostrar en el modal dialog
 *	@param string tcTitulo: texto en el título del modal dialog (predeterminado 'Confirmar')
 *	@param string tcIcono: ícono font-awesome (predeterminado 'fas fa-question-circle')
 *	@param string tcTipo: default, blue, red, green, orange, purple, dark (predeterminado 'blue')
 *	@param string tcAncho: ancho del mensaje xsmall, small, medium, large, xlarge (predeterminado 'small')
 *	@param function tfFuncionCerrar: función que se ejecutará después de cerrar la ventana
 *	@param object toOpciones: opciones adicionales o que reemplazan la funcionalidad predeterminada
 */
function fnDialog(
   tcMensaje,
   tcTitulo,
   tcIcono,
   tcTipo,
   tcAncho,
   tfFuncionCerrar,
   toOpciones
) {
   tcTitulo = tcTitulo ? tcTitulo : "";
   tcIcono = tcIcono ? tcIcono : "";
   tcTipo = tcTipo ? tcTipo : "blue";
   tcAncho = tcAncho ? tcAncho : "md";
   toOpciones = typeof toOpciones === "object" ? toOpciones : {};
   var loOpciones = $.extend(
      {
         icon: tcIcono,
         title: tcTitulo,
         content: tcMensaje,
         type: tcTipo,
         columnClass: tcAncho,
         closeIcon: true,
         animateFromElement: false,
         smoothContent: false,
         animationSpeed: 50,
         escapeKey: "Cancelar",
         onOpen: function () {
            $("body").css({ overflow: "hidden" });
         },
         onClose: function () {
            $("body").css({ overflow: "" });
            if (typeof tfFuncionCerrar === "function") {
               tfFuncionCerrar();
            }
         },
      },
      toOpciones
   );
   return $.dialog(loOpciones);
}

/*
 *  Organiza array obtenido con SerializeArray en un objeto con elementos name: value
 *
 *	@param array taData: array obtenido de la función SerializeArray
 *	@return object objeto organizado
 */
function OrganizarSerializeArray(taData) {
   var loRetorno = {};
   $.each(taData, function (lcKey, loObj) {
      loRetorno[loObj.name] = loObj.value;
   });
   return loRetorno;
}

function fnReconocimientoVoz(tcBoton, tcTextoEscribir) {
   if (
      !("webkitSpeechRecognition" in window) ||
      !window.hasOwnProperty("webkitSpeechRecognition") ||
      typeof webkitSpeechRecognition != "function"
   ) {
      console.log("No se puede usar la API de reconocimiento de voz");
   }

   goReconocimientoEvo = new webkitSpeechRecognition();
   goReconocimientoEvo.lang = "es-ES";
   goReconocimientoEvo.continous = true;
   goReconocimientoEvo.interimReresults = true;
   goReconocimientoEvo.maxAlternatives = 1;
   goReconocimientoEvo.onerror = function (event) {
      console.error(event);
   };
   goReconocimientoEvo.onStart = function (event) {
      goReconocimientoEvo.start();
   };
   goReconocimientoEvo.onend = function (event) {
      goReconocimientoEvo.stop();
   };
   goReconocimientoEvo.onresult = function (event) {
      const results = event.results;
      const lcFraseDicta = results[results.length - 1][0].transcript;
      const lcFraseAnterior = tcTextoEscribir.val();
      tcTextoEscribir.val(lcFraseAnterior + lcFraseDicta + "." + "\n");
   };
   goReconocimientoEvo.start();
}

/*
 *  Conviente json codificado en Bin64 en objeto
 *	@param string tcJson
 *	@return object objeto
 */
function btoObj(tcJson) {
   return JSON.parse(atob(tcJson));
}

/*
 *  Compara dos objetos
 *	@param object toObjA
 *	@param object toObjtB
 *	@return boolean, true si son iguales
 */
function compareObj(toObjA, toObjtB) {
   var aKeys = Object.keys(toObjA).sort();
   var bKeys = Object.keys(toObjtB).sort();
   if (aKeys.length !== bKeys.length) {
      return false;
   }
   if (aKeys.join("") !== bKeys.join("")) {
      return false;
   }
   for (var i = 0; i < aKeys.length; i++) {
      if (typeof toObjA[aKeys[i]] === "object" && !(toObjA[aKeys[i]] === null)) {
         if (!compareObj(toObjA[aKeys[i]], toObjtB[aKeys[i]])) {
            return false;
         }
      } else {
         if (toObjA[aKeys[i]] !== toObjtB[bKeys[i]]) {
            return false;
         }
      }
   }
   return true;
}

/*
 *	Crea un formulario temporal para enviar datos
 *	@param string tcUrl: dirección del link a abrir
 *	@param object toData: objeto con los datos a enviar
 *	@param boolean tlTargetBlank: true para indicar que se abre en una nueva pestaña
 *	@param function tfFuncionFinal: función que se ejecutará luego de hacer el llamado
 */
function formPostTemp(tcUrl, toData, tlTargetBlank, tfFuncionFinal) {
   var loNewForm = $("<form>", { action: tcUrl, method: "POST" });
   if (tlTargetBlank) loNewForm.attr("target", "_blank");
   $.each(toData, function (tcKey, tcVal) {
      loNewForm.append(
         $("<input>", { type: "hidden", name: tcKey, value: tcVal })
      );
   });
   $(document.body).append(loNewForm);
   loNewForm.show().submit().remove();
   if (typeof tfFuncionFinal === "function") {
      tfFuncionFinal();
   }
}

/*
 *	Muestra documento PDF en una ventana modal. En la ventana se recibe el PDF en base64, en documentos grandes genera error
 *	@param object toDatos: objeto con los datos a enviar
 *	@param function tfFuncionCerrar: función que se ejecutará al cerrar la ventana
 */
function vistaPreviaPdf_b64(toDatos, tfFuncionCerrar) {
   if (typeof MobileDetect == "function") {
      var loMobile = new MobileDetect(window.navigator.userAgent);
      if (loMobile.mobile()) {
         formPostTemp(
            "nucleo/vista/documentos/vistaprevia.php",
            toDatos,
            true,
            tfFuncionCerrar
         );
         return;
      }
   }
   var loEnviar = {};
   if (toDatos.datos) loEnviar.datos = JSON.parse(toDatos.datos);
   if (toDatos.portada) loEnviar.portada = JSON.parse(toDatos.portada);
   var lcEnviar = btoa(JSON.stringify(loEnviar));
   var lnFactor = 0.25 * Math.log($(window).height()) - 0.96;
   lnFactor = lnFactor > 0.81 ? 0.81 : lnFactor < 0.6 ? 0.6 : lnFactor;
   var lnAlto = parseInt($(window).height() * lnFactor);
   oModalDocumento = $.dialog({
      title:
         '<img src="nucleo/publico/imagenes/logo/main-logo-mini.svg"> Documento PDF',
      content:
         '<iframe src="nucleo/vista/documentos/blanco.php" width="100%" height="' +
         lnAlto +
         'px"></iframe>',
      onContentReady: function () {
         var self = this;
         $.ajax({
            type: "POST",
            url: "vista-documentos/ajax/ajax.php",
            data: { accion: "docPDF", datos: lcEnviar },
            dataType: "text",
         })
            .done(function (tcResponse) {
               if (tcResponse.substr(0, 3) == "<br") {
                  self.setContent(tcResponse);
               } else {
                  self.setContent(
                     '<iframe frameborder="0" src="data:application/pdf;base64,' +
                     tcResponse +
                     '" width="100%" height="' +
                     lnAlto +
                     'px"></iframe>'
                  );
               }
            })
            .fail(function () {
               self.setContent("<b>No se pudo obtener el documento</b>");
            });
      },
      type: "blue",
      columnClass: "xl",
      theme: "bootstrap",
      animateFromElement: false,
      smoothContent: false,
      animationSpeed: 50,
      onOpen: function () {
         $("body").css({ overflow: "hidden" });
      },
      onClose: function () {
         $("body").css({ overflow: "" });
         if (typeof tfFuncionCerrar === "function") {
            tfFuncionCerrar();
         }
      },
   });
}

/*
 *	Muestra documento PDF en una ventana modal. En Chrome y Edge presenta error al descargar, se debe imprimir como PDF.
 *	@param object toDatos: objeto con los datos a enviar
 *	@param function tfFuncionCerrar: función que se ejecutará al cerrar la ventana
 */
function vistaPreviaPdf(
   toDatos,
   tfFuncionCerrar,
   tcDescripcionDocumento,
   tcModulo
) {
   var laData = JSON.parse(toDatos.datos);
   var cDscDoc =
      typeof tcDescripcionDocumento == "string"
         ? ("Documento PFD " + tcDescripcionDocumento).trim()
         : "Documento PFD";
   if (laData.length == 1) {
      fnRegMovAudDoc(
         laData[0],
         "VISTAPDF",
         cDscDoc.toUpperCase(),
         typeof tcModulo == "string" ? tcModulo : "FUN_VSTPRV"
      );
   } else {
      fnRegMovAudDoc(
         laData[0],
         "VISTAPDF",
         cDscDoc.toUpperCase(),
         typeof tcModulo == "string" ? tcModulo : "FUN_VSTPRV"
      );
   }
   if (typeof MobileDetect == "function") {
      var loMobile = new MobileDetect(window.navigator.userAgent);
      if (loMobile.mobile()) {
         formPostTemp("nucleo/vista/documentos/vistaprevia.php", toDatos, true);
         return;
      }
   }
   goDatosVistaPreviaPdf = toDatos;

   var lnFactor = 0.25 * Math.log($(window).height()) - 0.96;
   lnFactor = lnFactor > 0.81 ? 0.81 : lnFactor < 0.6 ? 0.6 : lnFactor;
   var lnAlto = parseInt($(window).height() * lnFactor);
   var lcContenido = [
      '<iframe id="frameVistaPreviaDocHC" ',
      'name="frameVistaPreviaDocHC" ',
      'onload="vistaPreviaPdfRun();" ',
      'src="nucleo/vista/documentos/blanco.php" ',
      'width="100%" ',
      'height="' + lnAlto + 'px">',
      "</iframe>",
   ].join("");
   oModalDocumento = $.dialog({
      title:
         '<img src="nucleo/publico/imagenes/logo/main-logo-mini.svg"> Documento PDF',
      content: lcContenido,
      type: "red",
      columnClass: "xl",
      theme: "bootstrap",
      animateFromElement: false,
      smoothContent: false,
      animationSpeed: 50,
      onOpen: function () {
         $("body").css({ overflow: "hidden" });
      },
      onClose: function () {
         $("body").css({ overflow: "" });
         if (typeof tfFuncionCerrar === "function") {
            tfFuncionCerrar();
         }
      },
   });
}
var goDatosVistaPreviaPdf = {};
function vistaPreviaPdfRun() {
   $("#frameVistaPreviaDocHC").attr("onload", "");
   var loDatos = goDatosVistaPreviaPdf;
   goDatosVistaPreviaPdf = {};
   var loForm = $("#frameVistaPreviaDocHC").contents().find("#formVistaPrevia"),
      loDivMsg = $("#frameVistaPreviaDocHC").contents().find("#divEspere");
   $.each(loDatos, function (tcKey, tcVal) {
      loForm.append($("<input>", { type: "hidden", name: tcKey, value: tcVal }));
   });
   loDivMsg.html(
      '<b>Se está obteniendo el documento.<br>Espere por favor ... </b><i class="fas fa-circle-notch fa-spin" style="font-size: 1.5em; color: Tomato;"></i>'
   );
   loForm.submit();
}

/*
 *	@param object toData: {
 *		nIngreso:	integer - Número de ingreso
 *		cTipId:		string - Tipo identificación paciente
 *		nNumId:		integer - Número identificación paciente
 *		nConsulta:	integer - Consecutivo de consulta
 *		nCita:		integer - Consecutivo de cita
 *		cCup:		string - Código de procedimiento
 *		cModulo:	string - Nombre del módulo
 *		cObjeto:	string - Nombre del objeto
 *		nImprime:	integer - 1 si es impresión
 *		cDescrip:	string - Descripción acción
 *		cPrgCrea:	string - Programa Crea
 *	}
 */
function fnRegMovAud(toData) {
   if (typeof toData == "object") {
      toData.accion = "regMovAud";
      $.post("vista-comun/ajax/regmovaud", toData, function (loRta) {
         //
      });
   }
}
function fnRegMovAudDoc(toData, tcObjeto, tcDescripcion, tcModulo) {
   fnRegMovAud({
      nIngreso: toData.nIngreso ?? 0,
      cTipId: toData.cTipDocPac ?? "",
      nNumId: toData.nNumDocPac ?? 0,
      nConsulta: parseInt(toData.nConsecCons ?? 0),
      nCita: parseInt(toData.nConsecCita ?? 0),
      cCup: toData.cCUP ?? "",
      cModulo: tcModulo + "_WEB",
      cObjeto: tcObjeto,
      nImprime: 0,
      cDescrip: tcDescripcion,
      cPrgCrea: tcModulo,
   });
}

/*
 *	Retorna un identificador único basado en la fecha hora actual
 *	@param string tcPrefix: prefijo a adicionar al resultado
 *	@param Boolean tbRandom: si es true adiciona un valor aleatorio al final
 *	@return string identificador
 */
function uniqid(tcPrefix, tbRandom) {
   tcPrefix = tcPrefix ? tcPrefix : "";
   const sec = Date.now() * 1000 + Math.random() * 1000;
   const id = sec.toString(16).replace(/\./g, "").padEnd(14, "0");
   return (
      tcPrefix +
      id +
      (tbRandom ? "." + Math.trunc(Math.random() * 100000000) : "")
   );
}

/*
 *	Convierte color numérico obtenido con la función RGB de foxpro a un color css
 *	@param numero tnColor: número que representa el color
 *	@return string color hexadeximal css
 */
function colorFoxToRGB(tnColor) {
   var tnMB = 256 * 256,
      tnMG = 256,
      tnB = parseInt(tnColor / tnMB),
      tnG = parseInt((tnColor - tnB * tnMB) / tnMG),
      tnR = parseInt(tnColor - tnB * tnMB - tnG * tnMG);
   return (
      tnR.toString(16).padStart(2, "0") +
      tnG.toString(16).padStart(2, "0") +
      tnB.toString(16).padStart(2, "0")
   );
}

/*
 *	Retorna la ruta absoluta del sitio
 */
function getAbsolutePath() {
   var loc = window.location;
   var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf("/") + 1);
   return loc.href.substring(
      0,
      loc.href.length -
      ((loc.pathname + loc.search + loc.hash).length - pathName.length)
   );
}

/*
 *	Crea variables globales a partir de propiedades de un objeto
 */
function CrearVarGlobal(toVar) {
   var loGlb = typeof global !== "undefined" ? global : this;
   for (var lcClave in toVar) {
      if (toVar.hasOwnProperty(lcClave)) {
         loGlb["$" + lcClave] = toVar[lcClave];
      }
   }
}

/*
 *	Serializa un form teniendo en cuenta elementos con disabled true
 *	Forma de uso:
 *		var form_data = $('form').serializeAll();
 */
(function ($) {
   $.fn.serializeAll = function (tlOrganizar) {
      tlOrganizar = typeof tlOrganizar === "boolean" ? tlOrganizar : true;
      var laData = $(this).serializeArray();

      $(":disabled[name]", this).each(function () {
         laData.push({ name: this.name, value: $(this).val() });
      });

      return tlOrganizar ? OrganizarSerializeArray(laData) : laData;
   };
})(jQuery);

// Multimodal
$(document).on(
   {
      "show.bs.modal": function () {
         var zIndex = 1040 + 10 * $(".modal:visible").length;
         $(this).css("z-index", zIndex);
         setTimeout(function () {
            $(".modal-backdrop")
               .not(".modal-stack")
               .css("z-index", zIndex - 1)
               .addClass("modal-stack");
         }, 0);
      },
      "hidden.bs.modal": function () {
         if ($(".modal:visible").length > 0) {
            setTimeout(function () {
               $(document.body).addClass("modal-open");
            }, 0);
         }
      },
   },
   ".modal"
);

function abrirLibro() {
   formPostTemp("modulo-documentos", { ingreso: aDatosIngreso.nIngreso }, true);
}

