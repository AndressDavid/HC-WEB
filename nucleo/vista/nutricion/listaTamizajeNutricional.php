<?php
	require_once (__DIR__ .'/../../controlador/class.Ingreso.php');
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Nutriciones.php');

	$lcTipoBitacora = (isset($_GET['q'])?$_GET['q']:'UNDEFINE');
	$loNutriciones = new NUCLEO\Nutriciones();		

?>
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-header">
            <h5>Tamizaje Nutricional</h5>
            <form role="form" id="registroTamizajeNutricional" name="registroTamizajeNutricional" method="POST"
                enctype="application/x-www-form-urlencoded" action="modulo-nutricion&p=listaTamizajeNutricional">
                <div id="filtro" class="row">
                    <div class="col-sm-12 col-md-2 pb-2">
                        <label for="ingreso">Ingreso</label>
                        <input type="number" class="form-control form-control-sm" name="ingreso" id="ingreso" min="0"
                            max="99999999" placeholder=""
                            value="<?php print(isset($loIngreso)?$loIngreso->nIngreso:''); ?>">
                    </div>
                    <div class="col-sm-12 col-md-5 col-lg-4 pb-2">
                        <label for="identificacion"><b>Identificaci&oacute;n</b></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group input-group-sm">
                                <select class="custom-select font-weight-bold tipoDocumento" id="cPacienteId"
                                    name="cPacienteId" placeholder="Tipo de documento" autocomplete="off">
                                </select>
                                <input type="number" class="form-control form-control-sm font-weight-bold confirmar"
                                    data-label="Identificación" id="nPacienteId" name="nPacienteId"
                                    aria-describedby="nIdAyuda" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-5 col-lg-3 pb-2">
                        <label for="paciente">Paciente</label>
                        <input type="text" class="form-control form-control-sm font-weight-bold" name="paciente"
                            id="paciente" placeholder=""
                            value="<?php print(isset($loIngreso)?$loIngreso->oPaciente->getNombreCompleto():''); ?>"
                            disabled="disabled">
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-3 pb-2">
                        <div class="form-group form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="periodo" name="periodo"
                                <?php print(isset($_POST['periodo'])?'checked="checked"':''); ?>>
                            <label class="form-check-label" for="periodo">Buscar en este periodo</label>
                        </div>
                        <div class="form-inline row">
                            <div class="form-group col-6 pr-0">
                                <div class="input-group input-group-sm date w-100">
                                    <div class="input-group-prepend">
                                        <span class="input-group-addon input-group-text"><i
                                                class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input id="inicio" name="inicio" type="text" class="form-control"
                                        required="required" value="<?php print($ldFechaInicio); ?>">
                                </div>
                            </div>
                            <div class="form-group col-6 pl-1">
                                <div class="input-group input-group-sm date w-100">
                                    <div class="input-group-prepend">
                                        <span class="input-group-addon input-group-text"><i
                                                class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input id="fin" name="fin" type="text" class="form-control" required="required"
                                        value="<?php print($ldFechaFin); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 pb-2">
                        <label>Estado</label>
                        <select class="custom-select custom-select-sm" id="estado" name="estado">
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4 pb-2">
                        <label for="cSeccion" class="control-label"><b>Secci&oacute;n</b></label>
                        <select id="cSeccion" name="cSeccion" class="form-control form-control-sm">
                        </select>
                    </div>
                    <div class="col-sm-12 col-md-6 col-lg-4 pb-2">
                        <div class="row align-items-end h-100">
                            <div class="col-12 col-sm-4 pb-2 pb-md-0">
                                <button id="btnBuscar" type="submit" class="btn btn-secondary btn-sm w-100"
                                    accesskey="B"><u>B</u>uscar</button>
                            </div>
                            <div class="col-12 col-sm-4 pb-2 pb-md-0">
                                <a class="btn btn-secondary btn-sm w-100" accesskey="L"
                                    href="modulo-nutricion&p=listaTamizajeNutricional"><u>L</u>impiar</a>
                            </div>
                            <div class="col-12 col-sm-4 pb-2 pb-md-0">
                                <a class="btn btn-secondary btn-sm w-100" accesskey="O"
                                    href="modulo-nutricion">V<u>o</u>lver</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col">
                    <div id="ingresoInfo"><?php print($lcMensaje); ?></div>
                </div>
            </div>
        </div>
        <div id="registroNutricion" class="card-body">
            <div id="toolbarlistaTamizajeNutricional"></div>
            </div>
            <table id="tableListaNutriciones" data-show-export="true" data-toolbar="#toolbarlistaTamizajeNutricional"
                data-show-refresh="true" data-click-to-select="true" data-show-export="false" data-show-columns="true"
                data-show-columns-toggle-all="true" data-minimum-count-columns="5" data-pagination="false"
                data-id-field="CONSECUTIVO" data-query-params="queryParams" data-row-style="rowStyle"
                data-url="vista-nutricion/ajax/listaTamizajeNutricional.ajax">
            </table>
        </div>
        <div class="card-footer text-muted">
        </div>

        <!-- Bootstrap Table -->
        <link rel="stylesheet" href="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.css" />
        <script type="text/javascript" src="publico-complementos/jquery-tableexport/tableExport.min.js"></script>
        <script type="text/javascript" src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table.min.js">
        </script>
        <script type="text/javascript"
            src="publico-complementos/bootstrap-table/3.1.3-dist/bootstrap-table-locale-all.min.js"></script>
        <script type="text/javascript"
            src="publico-complementos/bootstrap-table/3.1.3-dist/extensions/export/bootstrap-table-export.min.js">
        </script>
        <script type="text/javascript"
            src="publico-complementos/bootstrap-datepicker/1.9.0-dist/js/bootstrap-datepicker.min.js"></script>
        <script type="text/javascript"
            src="publico-complementos/bootstrap-datepicker/1.9.0-dist/locales/bootstrap-datepicker.es.min.js"></script>


        <script type="text/javascript" src="publico-complementos/mobile-detect.js/1.4.3/mobile-detect.min.js"></script>
        <script type="text/javascript" src="vista-comun/js/comun.js"></script>
        <script type="text/javascript" src="vista-nutricion/js/listaTamizajeNutricional.js"></script>

    </div>
</div>