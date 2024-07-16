<div class="modal fade" id="divJustificacionInmediato" tabindex="-1" role="dialog" aria-labelledby="myExtraLargeModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header badge badge-light">
				<h6 class="modal-title col-11 text-center" id="exampleModalLabel">Justificar Inmediato</h5>
			</div>
			<div id="divCapturaAgendaBody" class="modal-body">
				<form id="FormJustificacionInmediato" name="FormJustificacionInmediato" class="needs-validation" novalidate>
					<div id="txtMedicamentoJustificar" style="font-size:14px;"></div><br>
					<div class="form-group" style="margin-bottom: 0rem">
						<textarea class="form-control" id="txtJustificacionInmediato" name="JustificacionInmediato" rows="3" maxlength="200"></textarea>
						<label id="lblCaracteresJustificacionInmed" for="txtCaracteresJustificacionInmed" class="text-danger"></label>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Guardar información" aria-pressed="true" id="btnGuardaJustificacionInmediato"><u>G</u>uardar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal" data-backdrop="static" data-keyboard="false" aria-pressed="true" id="btnCancelarJustificacionInmediato"><u>C</u>ancelar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript" src="vista-comun/js/modalJustificacionInmediato.js"></script>
