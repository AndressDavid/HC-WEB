<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

class Auditoria
{
	/**
	 *	Guarda registros en la tabla de auditoria
	 *
	 *	@param Integer $tnIngreso		Número de ingreso realacionado
	 *	@param Integer $tnConsulta		Consecutivo de consulta o de cirugía
	 *	@param Integer $tnCita			Consecutivo de cita
	 *	@param String  $tcProcedimiento	Código de procedimiento
	 *	@param String  $tcModulo		Módulo desde el cual se registra
	 *	@param String  $tcObjeto		Objeto que registra
	 *	@param Integer $tnImprime		0=No, 1=Si (por defecto cero)
	 *	@param String  $tcDescripcion	Descripcion del evento (por defecto vacío)
	 *	@param String  $tcProgramaCrea	Programa creación (por defecto 'AUDITWEB')
	 *	@param String  $tcTipoId		Tipo documento identificación paciente (por defecto vacío)
	 *	@param Integer $tcNumeroId		Número documento identificación paciente (por defecto cero)
	 */
	public function guardarAuditoria($tnIngreso, $tnConsulta, $tnCita, $tcProcedimiento, $tcModulo, $tcObjeto, $tnImprime=0, $tcDescripcion='', $tcProgramaCrea='AUDITWEB', $tcTipoId='', $tcNumeroId=0)
	{
		$lcModAmo = substr(trim($tcModulo), 0, 30);
		$lcObjAmo = substr(trim($tcObjeto), 0, 30);
		if (!empty($lcModAmo) && !empty($lcObjAmo)) {
			global $goDb;
			if (strlen($tcTipoId)>1) {
				$laTipoDoc = $goDb->select('TIPDOC')->from('RIATI')->where(['DOCUME'=>$tcTipoId])->getAll('array');
				$tcTipoId = $goDb->numRows()>0 ? $laTipoDoc[0]['TIPDOC'] : substr($tcTipoId, 0, 1);
			}
			$lnImprime = abs(intval($tnImprime));
			$lcUsuario = isset($_SESSION[\HCW_NAME]) ? $_SESSION[\HCW_NAME]->oUsuario->getUsuario() : '';
			$ltAhora = new \DateTime($goDb->fechaHoraSistema());
			$laDatos = [
				':INDAMO' => ($lnImprime>0 ? 1 : 0),									// Índice n(8)
				':INGAMO' => (is_numeric($tnIngreso) ? intval($tnIngreso) : 0),			// Número ingreso n(8)
				':CCOAMO' => (is_numeric($tnConsulta) ? intval($tnConsulta) : 0),		// Consecutivo Consulta/Cirugía n(8)
				':CCIAMO' => (is_numeric($tnCita) ? intval($tnCita) : 0),				// Consecutivo cita n(8)
				':PROAMO' => substr(trim($tcProcedimiento), 0, 15),						// Aplicación c(15)
				':MODAMO' => $lcModAmo,													// Módulo c(30)
				':OBJAMO' => $lcObjAmo,													// Objeto c(30)
				':NIPAMO' => AplicacionFunciones::localIp(),							// IP del equipo c(30)
				':IMPAMO' => ($lnImprime>0 ? 1 : 0),									// Imprime n(4)
				':DESAMO' => substr(trim($tcDescripcion), 0, 220),						// Descripcion c(220)
				':OP1AMO' => substr(trim($tcTipoId), 0, 1),								// Opcional 1 c(1)
				':OP4AMO' => $tcNumeroId,												// Opcional 4 n(15,2)
				':USRAMO' => $lcUsuario,												// Usuario de creación c(10)
				':PGMAMO' => substr(trim($tcProgramaCrea), 0, 10),						// Programa creación c(10)
				':FGRAMO' => $ltAhora->format('Ymd'),									// Fecha creación n(8)
				':HGRAMO' => $ltAhora->format('His'),									// Hora creación n(6)
				':FECAMO' => $ltAhora->format('Ymd'),									// Fecha creación n(8)
				':HORAMO' => $ltAhora->format('His'),									// Hora creación n(6)
			];
			//	':NPCAMO' => '',									// Nombre PC c(30)
			//	':NUIAMO' => '',									// Usuario ingresa PC c(30)

			$lcSql = implode(' ', [
				'INSERT INTO AUDMOD (',
					'CONAMO, INDAMO, IN2AMO, INGAMO, CCOAMO, CCIAMO, PROAMO, MODAMO, OBJAMO, FGRAMO, HGRAMO, NIPAMO, IMPAMO, DESAMO, OP1AMO, OP4AMO, USRAMO, PGMAMO, FECAMO, HORAMO',
				') VALUES (',
					'(SELECT MAX(CONAMO)+1 FROM AUDMOD), :INDAMO, 0, :INGAMO, :CCOAMO, :CCIAMO, :PROAMO, :MODAMO, :OBJAMO, :FGRAMO, :HGRAMO,',
					':NIPAMO, :IMPAMO, :DESAMO, :OP1AMO, :OP4AMO, :USRAMO, :PGMAMO, :FECAMO, :HORAMO',
				')',
			]);

			$lbOnErrorDie = $goDb->onErrorDie;
			$goDb->onErrorDie = false;
			$lbContinuar = true; $lnNum = 0;
			while ($lbContinuar) {
				$lbRta = $goDb->query($lcSql, $laDatos);
				$lbContinuar = !$lbRta && $lnNum < 25;
				$lnNum++;
			}
			$goDb->onErrorDie = $lbOnErrorDie;
		}
	}
}