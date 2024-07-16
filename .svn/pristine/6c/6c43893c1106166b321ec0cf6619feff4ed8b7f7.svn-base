<?php
namespace NUCLEO_SOCKETSRV;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class HL7_ORU implements MessageComponentInterface
{
	protected $aClientes;
	protected $aMensajes=[];
	protected $cModelo;
	protected $cSM;
	protected $cEM;
	protected $cSL="\n"; // "\n" '<br>'
	public $bSalidaConsola=true;
	public $bSalidaLog=false; // debe ser false en producción


	public function __construct($tcModelo)
	{
		$this->cModelo = $tcModelo;
		$this->aClientes = new \SplObjectStorage;
		// Inicio y fin de mensaje
		$this->cSM = chr(11);
		$this->cEM = chr(28).chr(13);
	}

	public function onOpen(ConnectionInterface $toConn)
	{
		// Almacena la nueva conexión para enviar mensajes posteriormente
		$this->aClientes->attach($toConn);
		$this->aMensajes[$toConn->resourceId] = '';

		$this->salidaConsola("Nueva Conexion: ({$toConn->resourceId}){$this->cSL}");
	}

	public function onMessage(ConnectionInterface $toConn, $tcMsg)
	{
		$this->salidaConsola("Recibiendo mensaje de Conexion {$toConn->resourceId}{$this->cSL}");

		$this->aMensajes[$toConn->resourceId] .= $tcMsg;

		if (substr($this->aMensajes[$toConn->resourceId], -2) == $this->cEM) {
			$lcMensajeRecibido = $this->aMensajes[$toConn->resourceId];

			// Mensaje recibido en LOG
			$this->salidaConsola("Mensaje Recibido Conexion {$toConn->resourceId}{$this->cSL}");
			$this->fnEscribirLog("Mensaje Recibido: Conexion {$toConn->resourceId}:\n{$lcMensajeRecibido}");

			// Reinicia objeto de base de datos
			global $goDb;
			$goDb = null;
			$goDb = new \NUCLEO\Db();
			$goDb->onErrorDie = false;

			// Crear objeto para manejo de mensaje
			$loORU = new \NUCLEO\HL7_RecibeORU();

			// Número de mensajes
			$lnNumMensajes = mb_substr_count($lcMensajeRecibido, $this->cEM, 'UTF-8');
			$lnPosIni = $lnPosFin = 0;
			for ($i=0; $i < $lnNumMensajes; $i++) {

				// Mensaje recibido
				$lnPosFin = mb_strpos($lcMensajeRecibido, $this->cEM, $lnPosIni, 'UTF-8');
				$lcMensajeORU = mb_substr($lcMensajeRecibido, $lnPosIni, $lnPosFin - $lnPosIni, 'UTF-8');
				$lnPosIni = $lnPosFin + 1;

				// Validar mensaje
				$loORU->fnCrearMensajeORU($lcMensajeORU);
				$loORU->fnValidaMensajeORU();

				// Guardar resultados
				$loORU->fnGuardar();
				$loORU->fnLogProcesadoORU();

				// Enviar respuesta
				$toConn->send($this->cSM . $loORU->cMsgRta . $this->cEM);
				$this->fnEscribirLog("Respuesta enviada:\n{$loORU->cMsgRta}");
			}

			$this->aMensajes[$toConn->resourceId] = '';
			// cerrar conexión del cliente
			// $toConn->close(1000);
			unset($loORU);
		}
	}

	public function onClose(ConnectionInterface $toConn)
	{
		// La conexión se cerró, se remueve
		$this->aClientes->detach($toConn);
		unset($this->aMensajes[$toConn->resourceId]);

		$this->salidaConsola("Conexion {$toConn->resourceId} se ha desconectado{$this->cSL}");
	}

	public function onError(ConnectionInterface $toConn, \Exception $toError)
	{
		$this->salidaConsola("Un error ha ocurrido: {$toError->getMessage()}{$this->cSL}");

		$toConn->close();
	}

	private function salidaConsola($tcMensaje)
	{
		if ($this->bSalidaConsola) {
			echo $tcMensaje;
		}
	}

	private function fnEscribirLog($tcMensaje, $tbEcho=false)
	{
		if ($this->bSalidaLog) {
			$lcRuta = __DIR__ . '/Logs/Log_' . date('Ym');
			if (!is_dir($lcRuta)) mkdir($lcRuta, 0777, true);
			$lcFileLog = $lcRuta . '/Log_' . date('Ymd') . '.txt';
			$lcFecha = (new DateTime())->format('Y-m-d H:i:s.u');
			$lcMensaje = $lcFecha . ' | ' . $tcMensaje . PHP_EOL;
			$lnFile = fopen($lcFileLog, 'a');
			chmod($lnFile, 0777);
			fputs($lnFile, $lcMensaje);
			fclose($lnFile);
			if ($tbEcho) {
				$this->salidaConsola($lcMensaje);
			}
		}
	}

}
