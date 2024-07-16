<?php
namespace NUCLEO;
require_once __DIR__ . '/class.HL7.php';

class HL7_RAPID extends HL7
{
	/*
	 * Actualiza los segmentos con los datos enviados
	 */
	function fnActualizarSegmento($tcSegmento, $tnNum, $taDatos)
	{
		$loSegmento = $this->oMensaje->getSegmentByIndex($tnNum);
		$lcCmpSep = $this->oMensaje->_componentSeparator;
		$loIngreso = $this->oIngreso;

		switch ($tcSegmento) {
			case 'MSH':
				$loSegmento->setField(7, $this->cFechaHora);
				$loSegmento->setField(9, $this->cTipoMensaje . (empty($this->cEvento)? '': $lcCmpSep . $this->cEvento));
				$loSegmento->setField(10, $this->nConsecutivo);
				$loSegmento->setField(11, 'P');
				$loSegmento->setField(15, ($this->cTipoMensaje=='ACK')? 'NE': 'AL');
				break;

			case 'EVN':
				$lcFechaEvento = $this->cEvento=='A03'?
									$loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora,6,'0',STR_PAD_LEFT):
									$loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora,6,'0',STR_PAD_LEFT);
				$loSegmento->setField(1, $this->cEvento);
				$loSegmento->setField(6, $lcFechaEvento);
				break;

			case 'PID':
				//$lcDireccion = $this->fcLimpiarCaracteres($loIngreso->oPaciente->cDireccion, $this->aModelo['CHRNOP']['CHRNOP']);
				$loSegmento->setField(3, $loIngreso->nIngreso);
				$loSegmento->setField(5, $loIngreso->oPaciente->getApellidos() . $lcCmpSep . $loIngreso->oPaciente->getNombres());
				$loSegmento->setField(7, $loIngreso->oPaciente->nNacio);
				$loSegmento->setField(8, $loIngreso->oPaciente->cSexo);
				break;

			case 'PV1':
				$lcPatienClass = $loIngreso->cVia=='01'? 'E': ($loIngreso->cVia=='05'? 'I': 'O');
				//$lcAdmissionType = $loIngreso->cVia=='01'? 'E': $loIngreso->cVia=='05'? 'I': 'O';
				$lcCama = ($loIngreso->oHabitacion->cSeccion=='' && $loIngreso->oHabitacion->cHabitacion=='')? '': $loIngreso->oHabitacion->cSeccion . $loIngreso->oHabitacion->cHabitacion;
				$loSegmento->setField(2, $lcPatienClass);
				$loSegmento->setField(3, $lcCama);
				//$loSegmento->setField(4, $lcAdmissionType);
				//$loSegmento->setField(18, $lcPatienClass);
				$loSegmento->setField(44, $loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora,6,'0',STR_PAD_LEFT));
				$loSegmento->setField(45, $this->cEvento=='A03'? $loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora,6,'0',STR_PAD_LEFT): '');
				break;

			case 'ORC':
				//$lcAccesion = $loIngreso->nIngreso . '-' . $taDatos['nConsecCita'] . $lcCmpSep . 'SHAIO';
				$lcAccesion = $taDatos['nNumOrden'];
				$loSegmento->setField(1, 'NW');
				$loSegmento->setField(2, $lcAccesion);
				$loSegmento->setField(5, 'IP');
				$loSegmento->setField(7, '001' . str_repeat($lcCmpSep, 3) . $this->cFechaHora . $lcCmpSep . $this->cFechaHora . $lcCmpSep . 'S');
				$loSegmento->setField(9, $this->cFechaHora);
				$loSegmento->setField(12, $taDatos['cRegMedico'] . $lcCmpSep . $taDatos['cNombreMedico']);
				break;

			case 'OBR':
				//$lcAccesion = $loIngreso->nIngreso . '-' . $taDatos['nConsecCita'] . $lcCmpSep . 'SHAIO';
				$lcAccesion = $taDatos['nNumOrden'];
				$loSegmento->setField(1, '1');
				$loSegmento->setField(2, $lcAccesion);
				$loSegmento->setField(4, $taDatos['cCodHom']);
				break;

			case 'MSA':
				$loSegmento->setField(1, $taDatos['cCodAcepta']);
				$loSegmento->setField(2, $taDatos['cIdMensajeOriginal']);
				$loSegmento->setField(3, $taDatos['cObservacion']);
				break;
		}
	}
}
