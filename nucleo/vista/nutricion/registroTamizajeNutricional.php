<?php
	
	require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');
	require_once (__DIR__ .'/../../controlador/class.Nutriciones.php');
	
	$laDistribucionAlimentos = ['desayuno'=>['titulo'=>'Desayuno', 'color'=>'#faffc7'],
								'almuerzo'=>['titulo'=>'Almuerzo', 'color'=>'#cdecce'],
								'merienda'=>['titulo'=>'Merienda', 'color'=>'#fbf3ab'],
								'cena'=>['titulo'=>'Cena', 'color'=>'#ffd79d'],]
?>
<div class="container-fluid">
    <div class="card mt-3">
        <div class="card-header">
            <h5>Nutrición paciente</h5>
            <div id="registroCabecera" class="pb-2"><small>
                    <div id="divCabDatosPac">
                        <div class="row border border-light">
                            <div class="col-lg-4 col-md-6">
                                <div class="form-row">
                                    <div class="col-3 col-md-2">
                                        <span class="float-left">Paciente</span>
                                    </div>
                                    <div class="col-9 col-md-10">
                                        <label id="lblNombre">JIMENA PRUEBA CIRUGIA SEPT 29</label>
                                    </div>
                                    <div class="col-3 col-md-2">
                                        <span class="float-left">Vía</span>
                                    </div>
                                    <div class="col-9 col-md-5">
                                        <label id="lblVia">CIRUGIA AMBU.</label>
                                    </div>
                                    <div class="col-3 col-md-2">
                                        <span class="float-left">Peso</span>
                                    </div>
                                    <div class="col-9 col-md-3">
                                        <label id="lblPesoEncabezado">120.00 g</label>
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-3 col-md-3">
                                <div class="form-row">
                                    <div class="col-md-4 col-3">
                                        <span class="float-left">Género</span>
                                    </div>
                                    <div class="col-md-8 col-9">
                                        <label id="lblGenero">Femenino</label>
                                    </div>
                                    <div class="col-md-4 col-3">
                                        <span class="float-left">Habitación</span>
                                    </div>
                                    <div class="col-md-8 col-9">
                                        <label id="lblcHabitacion">SC - 0006</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3">
                                <div class="form-row">
                                    <div class="col-md-5 col-3">
                                        <span class="float-left">Documento</span>
                                    </div>
                                    <div class="col-md-7 col-9">
                                        <label id="lblDNI">C - 52423172</label>
                                    </div>
                                    <div class="col-md-5 col-3">
                                        <span class="float-left">Edad</span>
                                    </div>
                                    <div class="col-md-7 col-9">
                                        <label id="lblEdad">76A 8M 27D</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-2 col-md-3">
                                <div class="form-row">
                                    <div class="col-lg-5 col-md-4 col-3">
                                        <span class="float-left">Ingreso</span>
                                    </div>
                                    <div class="col-lg-7 col-md-8 col-9">
                                        <label id="lblIngreso">3252055</label>
                                    </div>
                                    <div class="col-lg-5 col-md-4 col-3">
                                        <span class="float-left">Historia</span>
                                    </div>
                                    <div class="col-lg-7 col-md-8 col-9">
                                        <label id="lblHistoria">3222633</label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </small>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="modulo-nutricion&p=listaTamizajeNutricional"><i
                                class="fab fa-nutritionix"></i> Volver</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Ingreso</li>
                </ol>
            </nav>
        </div>
        <div id="registroConteoDietas" class="card-body">

        </div>

        <div class="card-footer text-muted">

        </div>
    </div>
</div>