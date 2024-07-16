<div class="modal fade" id="divVerificaFalllece" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header badge-danger">
				<h5 class="modal-title col-11 text-center" id="exampleModalLabel">ATENCIÓN</h5>
			</div>

			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormFallece" name="FormFallece" class="needs-validation" novalidate>
					<div class="row">
						<div class="col-md-12">
							
							<div class="form-group">
								<label id="lblTextoFallece" for="txtTextoFallece" style="font-size:20px;">Doctor, usted acaba de colocar el paciente fallece, esta segúro?,
								<br>escriba SI o NO.<br><br><br>
								</label>
							</div>
							
							<div class="form-group">
								<label id="lblFallece" for="txtFallece">Escriba "SI" ó "NO", según corresponda</label>
								<input id="txtFallece" type="text" class="form-control form-control-sm" name="Fallece" style="text-transform:uppercase;">
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnAceptaFallece"><u>A</u>ceptar</button>
				<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnCancelaFallece"><u>c</u>ancelar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalAlertaFallece.js"></script>