lcRuta = "./nucleo/vista/dashboard-fev/ajax/ajax.php";

$(document).ready(function () {

  $("#cmdActualizar").click(function () {
    actualizaManual();
  });

  $(".btnErrores").click(function () {
    modalErrores();
  });

  $(".btnEnviar").click(function () {
    modalPorEnviar();
  });

  $(".btnPendientes").click(function () {
    modalPendientes();
  });

  $(".btnExitosos").click(function () {
    modalExitosos();
  });

  fechaActualInicio();
});

$(".input-group.date").datepicker({
  autoclose: true,
  showOnFocus: true,
  clearBtn: false,
  daysOfWeekHighlighted: "0.6",
  format: "yyyy-mm-dd",
  language: "es",
  todayBtn: false,
  todayHighlight: true,
  toggleActive: false,
  weekStart: 1,
  //startDate: new Date(),
  endDate: '0d'
});

$("#checkFiltroFechas").on("change", function () {
  let ldFechaActual = document.getElementById("fin").value;
  let lbEstadoCheck = document.getElementById('checkFiltroFechas').checked;
  lbEstadoCheck ? $('#blkFechaIni').css('display', 'block') : $('#blkFechaIni').css('display', 'none');
  lbEstadoCheck ? document.getElementById("inicio").value = ldFechaActual : '';
  document.getElementById("fin").value = ldFechaActual;
});


function temporizador() {
  let lnTime = 0;
  let lnTimeMaxSeg = 60;
  let lnIdTimeout = setTimeout(function () {
    obtenerCantidadDocumentosGenerados();
    obtenerParametricas();
    clearInterval(lnIdTimeout);
    lnIdTimeout = null;
    clearInterval(lnIdInterval);
    lnIdInterval = null;
    temporizador();
  }, 1000 * lnTimeMaxSeg);
  let lnIdInterval = setInterval(function () {
    lnTime += 1;
    $(".messageActualizar").html(lnTimeMaxSeg - lnTime);
  }, 1000);
}

function actualizaManual() {
  obtenerCantidadDocumentosGenerados();
  obtenerParametricas();
}

function generarDiagramaBarras(lnCantFactura, lnCantNC, lnCantND, lnCantDocSop, lnCantNADS) {
  let laDatosCantidad = [];
  document.getElementById("docRegistrados").innerHTML = "";
  document.getElementById("docFE").innerHTML = lnCantFactura;
  document.getElementById("docNC").innerHTML = lnCantNC;
  document.getElementById("docND").innerHTML = lnCantND;
  document.getElementById("docSO").innerHTML = lnCantDocSop;
  document.getElementById("docNA").innerHTML = lnCantNADS;
  laDatosCantidad = [lnCantFactura, lnCantNC, lnCantND, lnCantDocSop, lnCantNADS];
  $("#docRegistrados").remove();
  $("#containerCanvasReg").append('<canvas id="docRegistrados" class="mx-auto"></canvas>');

  new Chart(document.getElementById("docRegistrados").getContext("2d"), {
    type: "horizontalBar",
    data: {
      labels: ["Factura", "Nota Crédito", "Nota Débito", "Documento Soporte", "Nota de Ajuste DS"],
      datasets: [
        {
          label: false,
          data: laDatosCantidad,
          backgroundColor: ["#5DADE2", "#5DADE2", "#5DADE2", "#5DADE2", "#5DADE2",],
          borderColor: ["#5499C7", "#5499C7", "#5499C7", "#5499C7", "#5499C7"],
          borderWidth: 1,
        },
      ],
    },
    plugins: [loPluginDiagramas],
    options: {
      title: {
        display: true,
        text: `                     Documentos generados`,
        fontColor: "#000000",
        fontSize: 16,
      },
      //responsive: true,
      scales: {
        yAxes: [
          {
            stacked: true,
            ticks: {
              beginAtZero: true,
            },
            barThickness: 30,
          },
        ],
        xAxes: [
          {
            stacked: true,
          },
        ],
      },
      legend: {
        display: false,
      },
      animation: {
        duration: 500,
        easing: "easeInQuad",
      },
      layout: {
        padding: {
          bottom: 0,
          right: 70,
        },
      },
      plugins: {
        //Valores de las barras
        datalabels: {
          anchor: "end",
          align: "right",
          color: "#212F3D",
          font: {
            weight: "bold",
            size: 16,
          }
        },
      },
    },
  });
}

function generarDiagramaEstados(lnCantExitoso, lnCantPendiente, lnCantEnviar, lnCantError) {
  let laDatosCantidad = [];
  let laDatosColor = [];
  let laDatosLabel = [
    "Exitosos",
    "Pendiente proveedor",
    "Pendiente envío",
    "Errores",
  ];

  if (parseInt(lnCantError) === 0) {
    $(".btnErrores,.numErrores").css("display", "none");
    laDatosLabel = ["Exitosos", "Pendiente proveedor", "Pendiente envío"];
    laDatosCantidad = [lnCantExitoso, lnCantEnviar, lnCantPendiente];
    laDatosColor = ["#27AE60", "#F39C12", "#5DADE2"];
    document.getElementById("estExi").innerHTML = lnCantExitoso;
    document.getElementById("estPen").innerHTML = lnCantPendiente;
    document.getElementById("estEnv").innerHTML = lnCantEnviar;
  } else if (parseInt(lnCantError) > 0) {
    laDatosCantidad = [lnCantExitoso, lnCantEnviar, lnCantPendiente, lnCantError];
    laDatosColor = ["#27AE60", "#F39C12", "#3498DB", "#E74336"];
    $(".btnErrores,.numErrores").css("display", "block");
    document.getElementById("estExi").innerHTML = lnCantExitoso;
    document.getElementById("estPen").innerHTML = lnCantPendiente;
    document.getElementById("estEnv").innerHTML = lnCantEnviar;
    document.getElementById("estErr").innerHTML = lnCantError;
  }
  $("#disEstados").remove();
  $("#containerCanvasDis").append('<canvas id="disEstados" class="mx-auto"></canvas>');
  new Chart(document.getElementById("disEstados").getContext("2d"), {
    type: "doughnut",
    data: {
      labels: laDatosLabel,
      datasets: [
        {
          borderWidth: 0,
          borderColor: "#FBFCFC",
          backgroundColor: laDatosColor,
          data: laDatosCantidad,
        },
      ],
    },
    plugins: [loPluginDiagramas],
    options: {
      title: {
        display: true,
        text: "Distribución estados",
        fontColor: "#000000",
        fontSize: 16,
      },
      animation: {
        duration: 1000,
        easing: "easeInOutCubic",
      },
      legend: {
        display: true,
        position: "bottom",
        labels: {
          fontColor: "#000000",
        },
      },
      plugins: {
        datalabels: {
          color: "#ffffff00",
        },
      },
    },
  });
}

const loPluginDiagramas = {
  beforeDraw: (chart) => {
    const { ctx } = chart;
    ctx.shadowColor = "rgba(0, 0, 0, 0.2)";
    ctx.shadowBlur = 10;
    ctx.shadowOffsetX = 3;
    ctx.shadowOffsetY = 3;
  },
};


function fechaActual() {
  let loTiempo = Date.now();
  let ldFechaHoy = new Date(loTiempo).toISOString().split('T', 1)[0];
  document.getElementById("inicio").value = ldFechaHoy;
  document.getElementById("fin").value = ldFechaHoy;
  document.getElementById('checkFiltroFechas').checked = false;
  $('#blkFechaIni').css('display', 'none');
  obtenerCantidadDocumentosGenerados();
  obtenerParametricas();
}


function fechaFiltro() {
  let lbEstadoCheck = document.getElementById('checkFiltroFechas').checked;
  if (!lbEstadoCheck) {
    let ldFechaFin = document.getElementById("fin").value;
    document.getElementById("inicio").value = ldFechaFin;
  }
  obtenerCantidadDocumentosGenerados();
  obtenerParametricas();
}


function fechaActualInicio() {
  let loTiempo = Date.now();
  let ldFechaHoy = new Date(loTiempo).toISOString().split('T', 1)[0];
  lbEstadoCheck = document.getElementById('checkFiltroFechas').checked;
  if (!lbEstadoCheck) {
    $('#blkFechaIni').css('display', 'none')
    temporizador();
    document.getElementById("inicio").value = ldFechaHoy;
    document.getElementById("fin").value = ldFechaHoy;
  }
  obtenerCantidadDocumentosGenerados();
  obtenerParametricas();
}


function modalErrores() {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  modalInformacionDashboard("Documentos con error", "error", ldFechaInicial, ldFechaFinal,);
}


function modalPorEnviar() {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  modalInformacionDashboard("Documentos pendientes por enviar", "enviar", ldFechaInicial, ldFechaFinal);
}


function modalPendientes() {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  modalInformacionDashboard("Documentos pendientes por proveedor", "pendientes", ldFechaInicial, ldFechaFinal);
}


function modalExitosos() {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  modalInformacionDashboard("Procesados correctamente", "exitosos", ldFechaInicial, ldFechaFinal);
}


function obtenerCantidadDocumentosGenerados() {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  $.ajax({
    type: "POST",
    url: lcRuta,
    data: { accion: 'docGenerados', fechaIni: ldFechaInicial, fechaFin: ldFechaFinal },
    dataType: "json",
  })
    .done(function (loResponse) {
      if (String(loResponse.error).length > 0) {
        console.log(`Error obtenerCantidadDocumentosGenerados: ${loResponse.error}`);
      }
      generarDiagramaBarras(loResponse.cuentaFE, loResponse.cuentaNC, loResponse.cuentaND, loResponse.cuentaDS, loResponse.cuentaNA);
      document.getElementById('totalDocumentos').innerHTML = loResponse.totalDocumentos;

    })
    .fail(function () {
      console.log('No fue posible obtener la información de documentos generados, Error: ' + error.responseText);
    });
}

function obtenerDistribucionEstados(laParametrica) {
  let ldFechaInicial = document.getElementById("inicio").value;
  let ldFechaFinal = document.getElementById("fin").value;
  let loDocPendientes = document.getElementById('btnPendientes');

  $.ajax({
    type: "POST",
    url: lcRuta,
    data: { accion: 'disEstados', fechaIni: ldFechaInicial, fechaFin: ldFechaFinal },
    dataType: "json",
  })
    .done(function (loResponse) {
      if (String(loResponse.error).length > 0) {
        console.log(`Error obtenerDistribucionEstados: ${loResponse['error']}`);
      }
      generarDiagramaEstados(loResponse.conExitoso, loResponse.conEnviar, loResponse.conPendiente, loResponse.conError);
      parseInt(loResponse.conPendiente) > 0 ? loDocPendientes.classList.add('parpadea') : loDocPendientes.classList.remove('parpadea');

      for (loDatoParametrica of laParametrica) {
        if (loDatoParametrica.TIPO_DATO == 'INFOERR') {
          if (parseInt(loResponse.conError) > parseInt(loDatoParametrica.VALOR)) {
            informacionAlerta(parseInt(loDatoParametrica.VALOR), 1, 'error');
          } else {
            informacionAlerta(parseInt(loDatoParametrica.VALOR), 0, 'error');
          }
        } else if (loDatoParametrica.TIPO_DATO == 'INFOPEN') {
          if (parseInt(loResponse.conPendiente) > parseInt(loDatoParametrica.VALOR)) {
            informacionAlerta(parseInt(loDatoParametrica.VALOR), 1, 'pendiente');
          } else {
            informacionAlerta(parseInt(loDatoParametrica.VALOR), 0, 'pendiente');
          }

        }
      }
    })
    .fail(function () {
      console.log('No fue posible obtener la información de estados, Error: ' + error.responseText);
    });
}


function obtenerParametricas() {
  $.ajax({
    type: "POST",
    url: lcRuta,
    data: { accion: 'parametrica' },
    dataType: "json",
  })
    .done(function (loResponse) {
      if (String(loResponse.error).length > 0) {
        console.log(`Error obtenerParametricas: ${loResponse.error}`);
      }
      obtenerDistribucionEstados(loResponse.informacionParametrosAlertas);
    })
    .fail(function () {
      console.log('Error al cargar parametros, Error: ' + error.responseText);
    });
}


function informacionAlerta(lnValor, lnEstado, lcTipo) {
  $(`.info${lcTipo}`).remove();
  let loInfoAlertas = document.getElementById('infoAlertas');

  if (lcTipo == 'error' && lnEstado == 1) {
    loInfoAlertas.innerHTML += `<div class="alert alert-danger border-danger info${lcTipo}" role="alert">` +
      `<h5 class="text-center text-danger my-auto">Se presentan mas de ${lnValor} documentos con errores, por favor verificar. </h5></div>`;
  } else if (lcTipo == 'pendiente' && lnEstado == 1) {
    loInfoAlertas.innerHTML += `<div class="alert alert-warning border-warning info${lcTipo}" role="alert">` +
      `<h5 class="text-center text-warning my-auto">Se presentan mas de ${lnValor} documentos pendientes por respuesta de proveedor, por favor verificar. </h5></div>`;
  } else {
    loInfoAlertas.innerHTML = '';
  }
}

function modalInformacionDashboard(lcTipoInformacion, lcTipoEstado, lcFechaInicial, lcFechaFinal) {

  let loTabla = '';
  let lbVisibleFecha = false;
  let lbVisibleInfo = true;
  let lcCampo = '';
  let lcFechaError;
  
  loTabla = $("#tablaInfoDash");
  document.getElementById('tipoInformacion').innerHTML = lcTipoInformacion;

  if (lcTipoEstado == "error") {
    lbVisibleFecha = true;
    lcCampo = 'Error';
    document.getElementById('modalDialog').classList.remove('modal-lg');
    document.getElementById('modalDialog').classList.add('modal-xl');
    lcFechaError = {
      field: 'FECHAE', title: 'Fecha error',
      formatter: function (valor, fila) { return formatoFechaHoraBD(fila.FECHAE, fila.HORAE) }, sortable: true, visible: lbVisibleFecha
    }
  } else if (lcTipoEstado == "exitosos") {
    lbVisibleFecha = false
    lcCampo = 'Cufe';
    document.getElementById('modalDialog').classList.remove('modal-lg');
    document.getElementById('modalDialog').classList.add('modal-xl');
  } else if (lcTipoEstado == "pendientes" || lcTipoEstado == "enviar") {
    lbVisibleInfo = false;
    lbVisibleFecha = false
    document.getElementById('modalDialog').classList.remove('modal-xl');
    document.getElementById('modalDialog').classList.add('modal-lg');
  }

  $('#modalInformacionDocumentos').modal('show');
  loTabla.bootstrapTable('showLoading');
  $.ajax({
    type: "POST",
    url: lcRuta,
    data: { accion: lcTipoEstado, fechaIni: lcFechaInicial, fechaFin: lcFechaFinal },
    dataType: "json",
  }).done(function (loResponse) {
    loTabla.bootstrapTable('hideLoading');
    if (String(loResponse.error).length > 0) {
      console.log(`Error modalInformacionDashboard: ${loResponse.error}`);
    } else {
      loTabla.bootstrapTable('resetView')
      loTabla.bootstrapTable('destroy').bootstrapTable({
        classes: 'table table-bordered table-hover table-striped table-responsive-sm',
        theadClasses: 'thead-light',
        undefinedText: 'N/A',
        height: '500',
        showPaginationSwitch: false,
        pagination: true,
        pageSize: 20,
        pageList: '[5, 10, 20, 50, 100, 250, 500, All]',
        sortable: true,
        iconSize: 'sm',
        showExport: true,
        exportDataType: 'basic',
        exportTypes: ['txt', 'csv', 'excel'],
        columns: [
          { field: 'FACTURA', title: 'Factura', sortable: true },
          { field: 'NOTA', title: 'Nota', sortable: true },
          {
            field: 'TIPO_DOC', title: 'Tipo',
            formatter: function (valor, fila) { return fila.TIPO_DOC == 'FA' ? 'Factura' : fila.TIPO_DOC == 'NC' ? 'Nota crédito' : fila.TIPO_DOC == 'ND' ? 'Nota débito' : fila.TIPO_DOC == 'DS' ? 'Documento soporte' : 'Nota de ajuste' }, sortable: true
          },
          { field: 'INFO', title: lcCampo, sortable: true, visible: lbVisibleInfo },
          {
            field: 'FECHAG', title: 'Fecha creación',
            formatter: function (valor, fila) { return formatoFechaHoraBD(fila.FECHAG, fila.HORAG) }, sortable: true
          },lcFechaError
          ]
      });
     
      loTabla.bootstrapTable('refreshOptions', {
        data: loResponse.informacionDoc
      });
      
    }
  }).fail(function (error) {
    loTabla.bootstrapTable('hideLoading');
    loTabla.bootstrapTable("removeAll");
    console.log('Error al obtener la información de la tabla, Error: ' + error.responseText);
  });
 
  $('#modalInformacionDocumentos').on('hidden.bs.modal', function () {
    lbVisibleFecha = false;
    loTabla.bootstrapTable('removeAll');
    
  });
}

function formatoFechaHoraBD(lcFecha, lcHora) {
  let ltFormFecha = `${lcFecha.substring(0, 4)}-${lcFecha.substring(4, 6)}-${lcFecha.substring(6, 8)}`;
  let ltFormHora = `${lcHora.substring(0, 2)}:${lcHora.substring(2, 4)}:${lcHora.substring(4, 6)}`;
  if (String(lcHora).length == 5) {
    ltFormHora = `${lcHora.substring(0, 1)}:${lcHora.substring(1, 3)}:${lcHora.substring(3, 5)}`
  }
  return `${ltFormFecha} ${ltFormHora}`;
}