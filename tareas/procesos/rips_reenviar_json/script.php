<?php
	$lvResult = null; 

	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionFunciones.php');	
	require_once (__DIR__ .'/../../../nucleo/controlador/class.AplicacionTareaManejador.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Db.php');
	require_once (__DIR__ .'/../../../nucleo/controlador/class.Rips_factura.php') ;

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
			$lnProcesados=$lnActualizados=0;
			$llResultado=true;
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");
			$loAplicacionFunciones = new NUCLEO\AplicacionFunciones();
			$lcAhora = $loAplicacionFunciones->formatFechaHora('fechahora', $lcFecha.$lcHora, '-', ':', ('T'));
				
			if(isset($goDb)){	
				$goAplicacionTareaManejador->evento('Reenviar facturas rips');
				$loRipsJson = new NUCLEO\Rips_factura;
				$laFacturas = $loRipsJson->reenviarRipJson();
				if(isset($laFacturas)==true){
					if (count($laFacturas)>0) {
						foreach($laFacturas as $laFacturasProcesar){
							$lnProcesados += 1;
							$lnActualizados+=($llResultado==true?1:0);
							$goAplicacionTareaManejador->evento(sprintf("Actualizados",$lnActualizados,$lnProcesados));
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
	
	