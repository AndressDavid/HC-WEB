<?php
	require_once (__DIR__ .'/../../controlador/class.TiposDocumento.php');
	require_once (__DIR__ .'/../../controlador/class.Especialidades.php');
	require_once (__DIR__ .'/../../controlador/class.SeccionesHabitacion.php') ;
	require_once (__DIR__ .'/../../controlador/class.Nutricion.php');
	
	$lcMensaje = '';
	$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
	$lnFecha = $ltAhora->format("Ymd"); settype($lnFecha,"integer");
	$ltFuturo = new \DateTime( $goDb->fechaHoraSistema() );
	$ltFuturo->add(new DateInterval('P1D'));

	$ldFechaInicio = (isset($_GET['inicio'])?$_GET['inicio']:$ltAhora->format("Y-m-d"));
	$ldFechaFin = (isset($_GET['fin'])?$_GET['fin']:$ltFuturo->format("Y-m-d"));
	
	$loNutricion = new NUCLEO\Nutricion();

?>
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-header">
            <h5>Nutrici&oacute;n por pacientes</h5>
            <div id="filterlistaNutricionPacientes">
                <div class="row">
                    <div class="col-md-6 col-lg-4 col-xl-2 pb-2">
                        <label>Ingreso</label>
                        <input type="number" class="form-control form-control-sm" name="nIngreso" id="nIngreso" min="0"
                            max="99999999" placeholder=""
                            value="<?php print(isset($_GET['nIngreso'])?intval($_GET['nIngreso']):''); ?>">
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-5 pb-2">
                        <label>Documento</label>
                        <div id="documento" class="input-group">
                            <select class="custom-select custom-select-sm col-6" id="cDocumento" name="cDocumento">
                                <option></option>
                                <?php
										$laTiposDocumento = (new NUCLEO\TiposDocumento())->aTipos;
										
										foreach($laTiposDocumento as $lcTipoDocumento => $laTipoDocumento){
											$lcSelected = ((isset($_GET['cDocumento'])?$_GET['cDocumento']:'')==$laTipoDocumento['ABRV']?'selected="selected"':'');											
											printf('<option value="%s" %s>%s - %s</option>',$lcTipoDocumento,$lcSelected,$laTipoDocumento['ABRV'],$laTipoDocumento['NOMBRE']);
										}
									?>
                            </select>
                            <input type="text" id="nDocumento" name="nDocumento"
                                class="form-control form-control-sm col-6"
                                value="<?php print(isset($_GET['nDocumento'])?intval($_GET['nDocumento']):''); ?>">
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 pb-2">
                        <label>Periodo</label>
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
                    <div class="col-md-6 col-lg-4 col-xl-3 pb-2">
                        <label>Secci&oacute;n</label>
                        <select class="custom-select custom-select-sm" id="cEstado" name="cEstado">
                            <option value="">TODAS</option>
                            <?php
								$laSecciones = (new NUCLEO\SeccionesHabitacion())->aSecciones;
								if(is_array($laSecciones)==true){
									foreach($laSecciones as $lcSeccionId => $laSeccion){
										//if($laSeccion['UBICACION']=='P'){
											//if($laSeccion['SALA']<>'S'){
												$lcSelected = ($lcSeccion==$lcSeccionId?' selected':'');
												printf('<option value="%s"%s>%s</option>',$lcSeccionId,$lcSelected,$laSeccion['NOMBRE']);
											//}
										//}
									}
								}
							?>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-4 col-xl-3 pb-2">
                        <label>Estado</label>
                        <select class="custom-select custom-select-sm" id="cEstado" name="cEstado">
                            <option value="TODOS">TODOS</option>
                            <?php 
									foreach($loNutricion->obtenerEstados() as $laEstado){
										$lcSelect = ((isset($_GET['cEstado'])?'a'.$_GET['cEstado']:'')=='a'.$laEstado['CODIGO']?'selected="seleted"':'');
										printf('<option value="%s" %s>%s</option>',$laEstado['CODIGO'], $lcSelect, $laEstado['DESCRIPCION']);
									}
								?>
                        </select>
                    </div>
                    <div class="col-12">
                        <div class="row justify-content-end">
                            <div class="col-md-6 col-lg-4 col-xl-3 pb-2">
                                <div class="row align-items-end pt-3">
                                    <div class="col-12 col-sm-6 pb-2 pb-md-0">
                                        <button id="btnBuscar" type="button" class="btn btn-secondary btn-sm w-100"
                                            accesskey="B"><u>B</u>uscar</button>
                                    </div>
                                    <div class="col-12 col-sm-6 pb-2 pb-md-0">
                                        <a class="btn btn-secondary btn-sm w-100" accesskey="L"
                                            href="modulo-nutricion&p=listaNutricionPacientes"><u>L</u>impiar</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <div id="ingresoInfo"><?php print($lcMensaje); ?></div>
                </div>
            </div>
        </div>
        <div id="registrosCitaTelemedicina" class="card-body">
            <div id="toolbarlistaNutricionPacientes">
                <div class="form-inline">
                    <button id="btnActualizar" type="button" class="btn btn-success" accesskey="A">Actualizar</button>
                </div>
            </div>
            <table id="tableListaNutricionPacientes" data-show-export="true"
                data-toolbar="#toolbarlistaNutricionPacientes" data-show-refresh="true" data-click-to-select="true"
                data-show-export="false" data-show-columns="true" data-show-columns-toggle-all="true"
                data-minimum-count-columns="5" data-pagination="false" data-query-params="queryParams"
                data-row-style="rowStyle" data-url="vista-nutricion/ajax/listaNutricionPacientes.ajax">
            </table>
        </div>



        <div class="card-footer text-muted">
            <p>Si desea ver en detalle una cita, haga doble clic sobre la respectiva fila.</p>
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
        <script type="text/javascript" src="vista-nutricion/js/listaNutricionPacientes.js"></script>

    </div>
</div>