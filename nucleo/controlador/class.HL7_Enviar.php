<?php

namespace NUCLEO;

class HL7_Enviar
{
	protected static $aModelosPermitidos = ['RAPID', 'AGFA', 'ROCHEGLC', 'HEXALIS', 'DRAGER'];
	protected static $aTiposMensajePermitidos = ['ADT', 'ORM'];

	/* Retorna si se debe enviar mensaje para el modelo indicado */
	public static function fnSeDebeEnviar($tcModelo)
	{
		global $goDb;
		return $goDb->obtenerTabMae1('OP1TMA', 'HL7_PRM', "cl1tma='MOD_PRV' AND cl2tma='$tcModelo' AND ESTTMA=''", null, '0') == '1';
	}

	/* Genera mensaje y hace el envío */
	public static function fnGenerarEnviarHL7(
		$tcModelo = '',
		$tcTipoMensaje = '',
		$tcEvento = '',
		$tnIngreso = 0,
		$tnCita = 0,
		$tnNumOrden = 0,
		$tcCodCup = '',
		$tcRegMedico = '',
		$tcMedico = '',
		$tnEnviar = 1,
		$taDatosAdicionales = []
	)
	{
		$laResultado = ['error' => '', 'resultado' => ''];

		// Verifica Modelo de trabajo
		if (in_array($tcModelo, self::$aModelosPermitidos)) {

			// Verifica tipo de mensaje permitido
			if (in_array($tcTipoMensaje, self::$aTiposMensajePermitidos)) {
				$lcRespuesta = '';
				$lcUser = 'SRV_WEB';
				$lcProg = 'ENV_' . $tcTipoMensaje;
				$laDatos = [
					'nIngreso' => $tnIngreso,
					'nConsecCita' => $tnCita,
					'cCodCup' => $tcCodCup,
					'nNumOrden' => $tnNumOrden,
					'cRegMedico' => $tcRegMedico,
					'cNombreMedico' => $tcMedico,
					'aOtrosDatos' => $taDatosAdicionales,
				];

				// Crea mensaje
				require_once __DIR__ . "/class.HL7_{$tcModelo}.php";
				$lcClassName = "NUCLEO\\HL7_{$tcModelo}";
				$loMensaje = new $lcClassName($tcModelo);
				$loMensaje->fnCrearMensaje($tcTipoMensaje, $tcEvento, $laDatos);
				$loMensaje->fnLogCrearMensaje($lcUser, $lcProg, $laDatos);
				$lcMensaje = $loMensaje->cIniBloque . $loMensaje->oMensaje->toString() . $loMensaje->cFinBloque;

				if ($tnEnviar) {
					switch ($loMensaje->aModelo['SERVIDOR']['TIPO']) {
						case 'PUERTO':
							list($lcHost, $lcPort) = explode(':', $loMensaje->aModelo['SERVIDOR'][$tcTipoMensaje]);
							$laResultado = self::enviarMensajeSrvPto($lcMensaje, $lcHost, $lcPort);
							if (empty($laResultado['error'])) {
								$loMensaje->fnLogActualizarMensaje('ENVCONRTA', $laResultado['respuesta'], $lcUser, $lcProg, $laDatos);
							}
							break;
					}
					unset($loMensaje);
				} else {
					$laResultado['error'] = 'Mensaje Generado NO Enviado';
				}
			} else {
				$laResultado['error'] = "Tipo de Mensaje debe ser ADT - ORM ( $tcTipoMensaje )";
			}
		} else {
			$laResultado['error'] = 'Modelo de trabajo falta o no es permitido';
		}

		return $laResultado;
	}

	/*
	 * Envía mensaje y recibe respuesta del Socket servidor
	 * @param string $tcMensaje = Mensaje a enviar
	 * @param string $tcHost = IP del servidor
	 * @param string $tcPort = Puerto a la escucha en el servidor
	 * @param number $tnDominio = Dominio, por defecto AF_INET
	 * @param number $tnTipoSocket = Tipo de socket, por defecto SOCK_STREAM
	 * @param number $tnProtocolo = Protocolo, por defecto SOL_TCP
	 * @param number $tnMaxIntentos = Número máximo de intentos para enviar, por defecto 10
	 * @param number $tnMaxBytesLeidos = Número máximo de bytes leidos, por defecto 2048
	 * @param number $tnTipoLectura = Tipo de lectura, por defecto PHP_BINARY_READ
	 * @return Array con elementos error, resultado y respuesta
	 */
	public static function enviarMensajeSrvPto(
		$tcMensaje,
		$tcHost,
		$tcPort,
		$tnDominio = AF_INET,
		$tnTipoSocket = SOCK_STREAM,
		$tnProtocolo = SOL_TCP,
		$tnMaxIntentos = 10,
		$tnMaxBytesLeidos = 2048,
		$tnTipoLectura = PHP_BINARY_READ
	)
	{
		$lnIntentos = 0;
		$lcReturn = '';
		$laResultado = ['error' => '', 'resultado' => '', 'respuesta' => ''];

		while ($lnIntentos <= $tnMaxIntentos) {

			// Crear Socket
			$loSocket = socket_create($tnDominio, $tnTipoSocket, $tnProtocolo);
			if ($loSocket === FALSE) {
				$lcReturn = 'Error: Creación del socket fallida.';
			} else {
				// Conectar al Socket Servidor
				//$lbConexion = socket_connect($loSocket, $tcHost, (int) $tcPort);
				$lbConexion = socket_connect($loSocket, $tcHost, (int) $tcPort);
				if ($lbConexion === FALSE) {
					$lcReturn = 'Error: Conexión al socket fallida.';
				} else {
					// Enviar mensaje
					socket_write($loSocket, $tcMensaje, strlen($tcMensaje));
					socket_set_option($loSocket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 15, "usec" => 0));
					// Recibir respuesta
					$lcReturn = socket_read($loSocket, $tnMaxBytesLeidos, $tnTipoLectura);

					socket_close($loSocket);

					break;
				}
			}
			$lnIntentos++;
		}

		switch (substr($lcReturn, 0, 5)) {
			case 'Error':
				$laResultado['error'] = $lcReturn;
				break;
			case '':
				$laResultado['error'] = 'Envio fallo';
				break;
			default:
				$laResultado = ['error' => '', 'resultado' => 'Envio exitoso', 'respuesta' => $lcReturn];
				break;
		}

		return $laResultado;
	}
}