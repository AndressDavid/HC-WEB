<?php
	$lvResult = null; 

	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');	
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Reenviar_ordenes_medicas.php') ;

	$goAplicacionTareaManejador = new CRON\AplicacionTareaManejador(__FILE__,__DIR__);
	
	if($goAplicacionTareaManejador){
		try{
			$goAplicacionTareaManejador->evento("Procesando función principal");
			tareaProgramaPrincipal();
			$goAplicacionTareaManejador->evento("Función procesada correctamente");

		} catch (Exception $loError) {
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
		unset($goAplicacionTareaManejador);
	}
	
	function tareaProgramaPrincipal(){
		global $goAplicacionTareaManejador;
		global $goDb;

		try {
			$lnProcesados = 0;
			$lnActualizados = 0;
			$llResultado=true;
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");
			$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
			$lcAhora = $loAplicacionFunciones->formatFechaHora('fechahora', $lcFecha.$lcHora, '-', ':', ('T'));
				
			if(isset($goDb)){	
				$goAplicacionTareaManejador->evento('Consultando ordenenes por generar');
				$loOrdenes = new NUCLEO\Reenviar_ordenes_medicas;
				$laOrdenes = $loOrdenes->consultaReenviarOrdenes();
				
				if(isset($laOrdenes)==true){
					if (count($laOrdenes)>0) {
						foreach($laOrdenes as $laOrdenesReeenviar){
							$lnProcesados += 1;
							$lnActualizados+=($llResultado==true?1:0);
							$goAplicacionTareaManejador->evento(sprintf("%s el Ingreso %s del consecutivo %s",($llResultado==true?"Actualizado":"No actualizado"),$laOrdenesReeenviar['INGRESO'],$laOrdenesReeenviar['CODLGC']));
						}
						$goAplicacionTareaManejador->evento(sprintf("Actualizados %s de %s",$lnActualizados,$lnProcesados));
					}else{
						$goAplicacionTareaManejador->evento("No hay registros para procesar.");
					}
				}else{
					$goAplicacionTareaManejador->evento("No hay registros para procesar..");
				}
			}
		}catch(Throwable $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}catch(Exception $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		} catch(PDOException $loError){
			$goAplicacionTareaManejador->error($loError->getMessage());
		}
	}
	
	