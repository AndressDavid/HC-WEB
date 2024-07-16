<?php
namespace NUCLEO_SOCKETSRV;

class Funciones
{
	/*
	*	Consulta y retorna host y puerto para el modelo consultado
	*	@param string $tcModelo: modelo a iniciar
	*	@return string host:puerto del modelo consultado
	*/
	static function fnHostPort($tcModelo)
	{
		global $goDb;
		// Valores por defecto
		$lcDefault = ':';
		$laWhere = [
			'CL1TMA'=>'MODELO',
			'CL2TMA'=>$tcModelo,
			'CL3TMA'=>'SERVIDOR',
			'CL4TMA'=>'ORU',
			'ESTTMA'=>'',
		];
		$lcDatos = $goDb->obtenerTabMae1('DE2TMA', 'HL7_PRM', $laWhere, null, $lcDefault);
		$laReturn = explode(':', trim($lcDatos));

		return $laReturn;
	}


	/*
	*	Retorna si el puerto en el host indicado está o no abierto
	*	@param string $tcHost: dirección o nombre del host
	*	@param string $tcPuerto: puerto a evaluar
	*	@param string $tcForma: forma de probar la conexión, puede ser fsockopen o stream_socket_client
	*	@return array - abierto: SI o NO - mensaje: mensaje de acuerdo al estado
	*/
	static function puertoAbierto($tcHost, $tcPuerto, $tcForma='fsockopen')
	{
		$lcMsg = "Server: $tcHost - Puerto: $tcPuerto";
		$lnTiempoLimite = 10; // seg
		$laReturn = [
			'abierto' => '',
			'mensaje' => '',
		];

		if ($tcForma=='fsockopen') {
			$loCliente = fsockopen($tcHost,$tcPuerto,$lnError,$lcError,$lnTiempoLimite);
			if (!$loCliente) {
				$laReturn = [
					'abierto' => 'NO',
					'mensaje' => "$lcMsg - Fallo al conectar: $lcError",
				];
			} else {
				$laReturn = [
					'abierto' => 'SI',
					'mensaje' => "$lcMsg - Conexión realizada con éxito",
				];
				fclose($loCliente);
			}
		}

		if ($tcForma=='stream_socket_client') {
			$lcServer="tcp://$tcHost:$tcPuerto";
			$loCliente = stream_socket_client($lcServer, $lnError, $lcError);
			if ($loCliente === false) {
				$laReturn = [
					'abierto' => 'NO',
					'mensaje' => "$lcMsg - Fallo al conectar: $lcError",
				];
			} else {
				$laReturn = [
					'abierto' => 'SI',
					'mensaje' => "$lcMsg - Conexión realizada con éxito",
				];
				fclose($loCliente);
			}
		}

		return $laReturn;
	}

}