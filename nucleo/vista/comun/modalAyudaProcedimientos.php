<div class="modal fade" id="divAyudaProcedimiento" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">AYUDA PROCEDIMIENTOS</h5>
			</div>
			<div id="divCapturaAyudaProcedimiento" class="modal-body">
				<label id="lblPaquetesAyuda" for="txtPaquetesAyuda" style="font-size: 12pt; color:#FF0000"> 
				*Los paquetes de procedimientos no estan disponibles en esta opción	</label>
				<label id="lblInterconsultasAyuda" for="txtInterconsultasAyuda" style="font-size: 12pt; color:#FF0000"> 
				*Las interconsultas no estan disponibles, debe solicitarlas por la pestaña interconsultas.	</label>
				<div class="row">
					<div class="col-12">
						<table id="tblAyudaProcedimiento"></table>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12">
					<textarea class="form-control" id="txtListaProcedimientos" name="ListaProcedimientos" rows="6" style="font-size: 12px;" disabled></textarea>
				</div>
			</div>
			
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnAceptarAyudaProcedimiento" accesskey="R">Acepta<u>r</u></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalAyudaProcedimientos.js"></script>
