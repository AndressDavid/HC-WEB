<div class="container-fluid">
   <div class="card mt-3">
      <div class="card-header">
         <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-8 col-lg-11">
               <h5>
                  Control de cirugías
               </h5>
            </div>
         </div>
         <form  action="" method="post">
            <div class="row">
               <div class="col-xs-6 col-sm-6 col-md-6 col-lg-3 col-xl-2">
                  <label class="control-label" for="">Ingreso</label>
                  <input  class="form-control" type="number" name="" id="" min="0">
               </div>
               <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
                  <label class="control-label" for="">Identidad</label>
                  <div class="input-group">
                     <select class="form-control"  name="selTipDoc" id="selTipDoc"></select>
                     <input  class="form-control" type="text">
                  </div>
               </div>
               <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
                  <label class="control-label" for="">Nombre</label>
                  <input class="form-control" type="text">
               </div>
               <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-3">
                  <label class="control-label" for="">Apellido</label>
                  <input  class="form-control" type="text">
               </div>
            </div>
            <div class="row">
               <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
                  <label class="control-label" for="">Vía Ingreso</label>
                  <select class="form-control" name="" id=""></select>
               </div>
               <div class="col-xs-12 col-sm-12 col-md-6 col-lg-4 col-xl-4">
                  <label class="control-label" for="">Especialidad</label>
                  <select class="form-control" name="" id=""></select>
               </div>
               <div class="col-xs-12 col-sm-6 col-md-5 col-lg-2 col-xl-2">
                  <label class="control-label" for="">Fecha</label>
                  <div class="form-inline row">
                     <div class="form-group col-4 col-md-5 pr-0">
                        <input type="date" class="form-control" id="txtFechaIni" required="required" value="<?php print(date("Y-m-d")); ?>">
                     </div>
                     <div class="form-group col-4 ml-5">
                        <div>
                           <div class="col mb-0 form-group form-check" style="margin: 0 auto;">
                              <input type="checkbox" class="form-check-input" id="allCheck">
                              Todas
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 col-xl-1">
					<label for="btnBusca" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
					<button id="btnBuscar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="B"><u>B</u>uscar</button>
				</div>

				<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3 col-xl-1">
					<label for="btnLimpia" class="control-label"> &nbsp;  &nbsp;  &nbsp; &nbsp;  &nbsp;  &nbsp; </label>
					<button id="btnLimpiar" type="button" class="btn btn-secondary btn-sm w-100" accesskey="L"><u>L</u>impiar</button>
				</div>
            </div>
         </form>
      </div>
      <div class="card-body">
			<div class="row">
				<div class="col-12">
					<small>
						<table id="tblcirugia"></table>
					</small>
				</div>
			</div>
			<div class="row">
				<div class="col">
					<div class="row justify-content-end">
						<div class="col-12 col-sm-6 col-lg-4 col-xl-2 pt-2">
							<button id="btnConvencion" type="button" class="btn btn-warning btn-sm w-100">Convención</button>
						</div>
                        <div class="col-12 col-sm-6 col-lg-4 col-xl-2 pt-2">
							<button id="btnConvencion" type="button" class="btn btn-secondary btn-sm w-100" accesskey="A"><u>A</u>dicionar Cirugía</button>
						</div>
					</div>
				</div>
			</div>
		</div>
   </div>
</div>

<script type="text/javascript" src="vista-comun/js/tiposDocumentos.js"></script>
<script src="nucleo/vista/cirugia/js/scripts.js"></script>
<script>
    cirugia.inicializar();
</script>