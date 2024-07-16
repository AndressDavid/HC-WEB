var dataMicromedex;
var servicioMicroMedex ={
    poJson: {},
    pFBton: false,
    pWinAlertas: false,

    mostrarAlertasMedicamentos: function(poJson, pFBton)
    {
        $.ajax({
            headers: {
                Authorization: sessionStorage.getItem('coockie'),
                contentType: 'application/json'
            },
            processData: false,
            type: "GET",
            url: "restapi/server/v1/micromedex/activo",
            dataType: "json"
        }).done(function(loRespuesta) {
            var lbEjecutarFunction = false;
            if (typeof loRespuesta.activo !== 'undefined') {
                if (loRespuesta.activo==true) {
                    servicioMicroMedex.mostrarTablaMicroMedex(poJson, pFBton);
                } else {
                    lbEjecutarFunction=true;
                }
            } else {
                lbEjecutarFunction=true;
            }
            if (lbEjecutarFunction) {
                if (typeof pFBton == 'function') pFBton();
            }
        }).fail(function(jqXHR) {
            laRespuesta = jqXHR.responseJSON;
            console.log(this.headers);
            console.log(jqXHR);
        });
    },

    mostrarTablaMicroMedex: function(poJson, pFBton){
        this.poJson = poJson;
        this.opFBton = pFBton;
        this.pWinAlertas = $.confirm({
            content: 'url:vista-comun/tablaMicroMedex.php',
            type: 'red',
            columnClass: 'col-md-12',
            containerFluid: true,
            boxWidth: '90%',
            useBootstrap: false,
            onContentReady: function(){

                dataMicromedex =$("#tablaMicromedex");

                $('#toolbar').find('select').change(function () {
                    dataMicromedex.bootstrapTable('refreshOptions', {
                        exportDataType: $(this).val()
                    });
                });

                dataMicromedex.bootstrapTable('showLoading');
                $(".loading-text").css( "font-size", "23px");

                servicioMicroMedex.cargarTablaMicroMedex().then((dataJson) => {
                    if(dataJson.length < 1){
                        servicioMicroMedex.ejecutarGuardarDatos();
                        $(".jconfirm").remove();
                    }
                    dataMicromedex.bootstrapTable('refreshOptions',{data: dataJson});
                    $(".search-input").css( "margin-right", "20px");
                    $(".activeBt").attr("disabled", false);

                    $("#logMicro").remove();
                    $("#divTablaAlertasMedicamentos").show();
                });

                dataMicromedex.bootstrapTable({
                    classes: 'table table-bordered table-hover table-sm table-responsive-sm table-striped',
                    theadClasses: 'thead-light',
                    locale: 'es-ES',
                    undefinedText: '',
                    pagination:"true",
                    search:"true",
                    searchable:"true",
                    filterControl:"true",
                    showSearchClearButton:"true",
                    columns: [
                        {
                            title: "TIPO",
                            field: 'TYPE',
                            filterDefault: 'DRUG',
                            sortable: true,
                            filterControl: "select",
                            formatter: function (data){ return data=='UNKNOWN' ? 'N/A' : data; }
                        },
                        {
                            title: "GRAVEDAD",
                            field: 'SEVERITY',
                            sortable: true,
                            filterControl: "select",
                            formatter: function (data){ return data=='UNKNOWN' ? 'N/A' : data; }
                        },
                        {
                            title: "MEDICAMENTO 1",
                            field: 'ITEMLIST.INTERACTING_DRUG_GM',
                            sortable: true,
                            filterControl: "input",
                            formatter:  function (data, file){
                                lMedicamento1 = file.ITEMLIST.INTERACTING_DRUG_GM ==undefined ? file.ITEMLIST.INTERACTING_DRUG_NAME : file.ITEMLIST.INTERACTING_DRUG_GM;
                                return lMedicamento1;
                            }
                        },
                        {
                            title: "MEDICAMENTO 2",
                            field: 'ITEMLIST.SECONDARY_ITEM_GM',
                            sortable: true,
                            filterControl: "input",
                            formatter:  function (data, file){
                                lMedicamento2 = file.ITEMLIST.SECONDARY_ITEM_GM ==undefined ? file.ITEMLIST.SECONDARY_ITEM_NAME : file.ITEMLIST.SECONDARY_ITEM_GM;
                                return lMedicamento2;
                            }
                        },
                        {
                            title: "ALERTA",
                            field: 'WARNINGTEXT'
                        },
                        {
                            title: "REFERENCIAS",
                            field: 'MONOGRAPH_ID',
                            formatter: function (data) {
                                if(data == 0){
                                    return '';
                                }
                                return `<a href="javascript:void(0)" onclick="servicioMicroMedex.mostrarDetalleMicroMedex(`+data+`,'`+lMedicamento1+`','`+lMedicamento2+`')"><i class="fas fa-external-link-alt fst-norma text-primary" ><span class="ml-1">Link</span></i></a>`},
                        },
                    ]
                });
            },
            buttons: {
                btNext: {
                    text: 'Continuar',
                    btnClass: 'btn-red activeBt',
                    isDisabled: true,
                    action: function (btNext) {
                        if (typeof servicioMicroMedex.opFBton === 'function') {
                            servicioMicroMedex.opFBton();
                        }
                    }
                },
                btClose: {
                    text: 'Volver',
                    action: function (btClose) {
                    }
                }
            }
        });

        $( "#tableMedex" ).ready(function() {
            $(".jconfirm-title-c").remove();
            $(".jconfirm-box").css( "padding", 0);
            $(".jconfirm-buttons").css( "margin-right", 10);
        });

    },

    mostrarDetalleMicroMedex: async function (pMONOGRAPH, pMedicamento1, pMedicamento2){

        const resultado = await $.ajax({
            headers: {
                Authorization: sessionStorage.getItem('coockie'),
                contentType: 'application/json'
            },
            processData: false,
            type: "GET",
            url: "restapi/server/v1/micromedex/interactions/detail/"+pMONOGRAPH,
        }).done(function(loJsonRespuesta) {
        }).fail(function(jqXHR) {
            laRespuesta = jqXHR.responseJSON;
            console.log(this.headers);
            console.log(jqXHR);
        });
        $.confirm({
            title:'',
            content: '<div class="container-fluid" id="detalleReferencias"><h3 style="text-align: center;">Interacción ' +pMedicamento1+" / "+pMedicamento2+"</h3> </div>"+
            '<div>'+atob(resultado.body)+'<div>',
            type: 'red',
            columnClass: 'col-md-12',
            containerFluid: true,
            boxWidth: '90%',
            useBootstrap: false,
            buttons: {
                btClose: {
                    text: 'Cancelar',
                    action: function (btClose) {
                    }
                }
            }
        });

        $( "#detalleReferencias" ).ready(function() {

            var loContenidoReferencias = document.getElementsByTagName("cite");
            var loElementos = loContenidoReferencias[0].childNodes;
            var liCount = 0
            var lokeyElementos =[];

            loElementos.forEach((element) =>
            {
                if (element.localName == 'h3'){
                    lokeyElementos.push(liCount)
                }
                liCount ++;
            });
            for(i = lokeyElementos[0]; i< lokeyElementos[1]; i++){
                loElementos[0].remove()
            }
        });
    },

    cargarTablaMicroMedex: async function (){
        const resultado = await $.ajax({
            headers: {
                Authorization: sessionStorage.getItem('coockie'),
                contentType: 'application/json'
            },
            processData: false,
            type: "POST",
            url: "restapi/server/v1/micromedex/interactions",
            data: JSON.stringify(servicioMicroMedex.poJson),
        })
        .done(function(loJsonRespuesta) {
        }).fail(function(jqXHR) {
            laRespuesta = jqXHR.responseJSON;
            fnAlert(laRespuesta.mensaje,laRespuesta.respuesta);
        });

        return resultado;
    },

    ejecutarGuardarDatos: function (){

        if(typeof this.opFBton === 'function'){
            this.opFBton();
        }
    },

    // Diagnósticos de un paciente
    aListaDxPaciente: [],
    obtenerListaDiagnosticos: function(tnIngreso) {
        $.ajax({
            type: "POST",
            url: "vista-comun/ajax/diagnostico",
            data: {
                lcTipoDiagnostico:'consultaDiagnostico',
                lnNroIngreso: tnIngreso
            },
            dataType: "json",
            success: function(toDatos) {
                if (toDatos.error.length == 0) {
                    if (toDatos.TIPOS.length>0) {
                        $.each(toDatos.TIPOS, function(tnKey, toDiagnostico) {
                            servicioMicroMedex.aListaDxPaciente.push(toDiagnostico.DIAGNOSTICO);
                        });
                    }
                } else {
                    fnAlert(toDatos.error);
                    activarFiltros(true);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error(jqXHR.responseText);
                fnAlert('Se presentó un error al buscar paciente.');
                activarFiltros(true);
            }
        });
    }

}
