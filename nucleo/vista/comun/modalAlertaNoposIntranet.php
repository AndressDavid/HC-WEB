<div class="modal fade" id="divAlertaNoposItranet" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header badge-warning">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">ATENCIÓN ESTAMOS EN CONTINGENCIA</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormNoposItranet" name="FormNoposItranet" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							<div class="form-group">
								<label id="lblTextoNopos" for="selTextoNopos">Este paciente pertenece al regimen contributivo. 
								Por favor ingresar a la intranet y diligencia los documentos correspondientes, en la siguiente ruta: 
								<br>Documentos Intranet/Documentación - MIPRES NoPBS (Click sobre el botón Abrir Intranet)
								<br>Realice la prescripción correspondiente a los consumos NOPOS ordenados.</label>
							</div>
							<div class="form-group">
								<label id="lblListadoNoposIntranet" for="txtListadoNoposIntranet">LISTADO MEDICAMENTOS/PROCEDIMIENTOS NOPOS <small>(PUEDE SELECCIONAR Y COPIAR LA INFORMACIÓN)</small></label>
								<textarea class="form-control" id="txtListadoNoposIntranet" name="ListadoNoposIntranet" rows="10" disabled></textarea>
							</div>
						</div>
					</div>
				</form>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-primary" id="btnAbrirIntranet">Abrir Intranet</button>
				<button type="button" class="btn btn-sm btn-secondary" id="btnAceptarIntranet">Aceptar</button>
				<button type="button" class="btn btn-sm btn-secondary" id="btnCerrarIntranet">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalAlertaNoposIntranet.js"></script>
