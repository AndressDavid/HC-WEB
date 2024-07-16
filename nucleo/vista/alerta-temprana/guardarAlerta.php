<?php
	require_once (__DIR__ .'/../../publico/constantes.php') ;

	$lcMensaje = '';

	if (isset($_SESSION[HCW_NAME])){
		if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
			if(isset($_POST)){
				if(isset($_POST['nIngreso'])){

					// Guardando la información en la base de datos
					global $goDb;
					if(isset($goDb)){
						if($_POST['nIngreso']>0){

							$ltAhora = new DateTime( $goDb->fechaHoraSistema() );
							$lcFecha = $ltAhora->format("Ymd");
							$lcHora  = $ltAhora->format("His");

							$lcTabla = 'ALETEMP';

							// Marcar lecturas previas activas como cerradas
							$laDatos = [
								'ESTADO'=>'9',
								'USMALE'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
								'PGMALE'=>substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10),
								'FEMALE'=>$lcFecha,
								'HOMALE'=>$lcHora
								];
							$goDb->tabla($lcTabla)
								->where('NIGING', '=', $_POST['nIngreso'])
								->where('ESTADO', '>', '1')
								->where('ESTADO', '<', '9')
								->actualizar($laDatos);


							$lcEquipo = substr(trim(strtoupper($_POST['cEquipo']??'')),0,250);
							$lnAccion = $_POST['cAccion']+0; settype($lnAccion,'integer');
							$lcDescripcion = substr(trim(strtoupper($_POST['cDescripcion']??'')),0,510);

							// Actualizar las lecturas
							$llResultado = false;
							if(isset($_POST['cTipoAlerta'])){
								foreach($_POST['cTipoAlerta'] as $lcTipoAlerta){
									$laDatos = [
										'ESTADO'=>$lnAccion,
										'EQUIPO'=>$lcEquipo,
										'OBSERV'=>$lcDescripcion,
										'ACCION'=>$lnAccion,
										'ACCION'=>$lnAccion,
										'ACCFEC'=>$lcFecha,
										'ACCHOR'=>$lcHora,
										'USMALE'=>$_SESSION[HCW_NAME]->oUsuario->getUsuario(),
										'PGMALE'=>substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10),
										'FEMALE'=>$lcFecha,
										'HOMALE'=>$lcHora
										];

									$llResultado = $goDb
										->tabla($lcTabla)
										->where('NIGING', '=', $_POST['nIngreso'])
										->where('ESTADO', '<=', '1')
										->where('VAR29N','=',$lcTipoAlerta)
										->actualizar($laDatos);
								}
							}
							

							if($llResultado==true){
?>
<div class="modal fade" id="modalSignosGuardar" tabindex="-1" role="dialog" aria-labelledby="modalSignosGuardar" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered  modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Registro de Acciones para Alertas Tempranas</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<h1>Guardado</h1>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>
<?php
							}else{
								$lcMensaje='No fue posible guardar la informaci&oacute;n';
							}
						}else{
							$lcMensaje='No hay ingreso, no se guardo la infromacion';
						}
					}else{
						$lcMensaje='No hay conexi&oacute;n con la base de datos';
					}
				}else{
					$lcMensaje='La informaci&oacute;n recibida no esta completa';
				}
			}else{
				$lcMensaje='No hay informaci&oacute;n';
			}
		}else{
			$lcMensaje='Sesi&oacute;n caduco';
		}
	}else{
		$lcMensaje = 'No hay sesi&oacute;n';
	}

	if(empty($lcMensaje)==false){
		printf('<div class="alert alert-warning m-3" role="alert">%s</div>',$lcMensaje);
	}
?>