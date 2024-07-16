<?php
namespace NUCLEO;
require_once __DIR__ . '/class.HL7.php';

class HL7_ROCHEGLC extends HL7
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
				break;

			case 'EVN':
				$lcFechaEvento = $this->cEvento=='A03'?
									$loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora,6,'0',STR_PAD_LEFT):
									$loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora,6,'0',STR_PAD_LEFT);
				$loSegmento->setField(1, $this->cEvento);
				$loSegmento->setField(6, $lcFechaEvento);
				break;

			case 'PID':
				$loSegmento->setField(3, $loIngreso->nIngreso);
				$loSegmento->setField(4, $loIngreso->oPaciente->aTipoId['ABRV'] . $loIngreso->oPaciente->nId);
				$loSegmento->setField(5, mb_substr($loIngreso->oPaciente->getApellidos(),0,30) . $lcCmpSep . mb_substr($loIngreso->oPaciente->getNombres(),0,30));
				$loSegmento->setField(7, $loIngreso->oPaciente->nNacio);
				$loSegmento->setField(8, $loIngreso->oPaciente->cSexo);
				break;

			case 'PV1':
				$lcClasePac = $loIngreso->cVia=='01'? 'E': ($loIngreso->cVia=='05'? 'I': 'O');
				$lcServicio = $loIngreso->cVia=='01'? 'URG': ($loIngreso->cVia=='05'? $loIngreso->oHabitacion->cSeccion . $lcCmpSep . $loIngreso->oHabitacion->cHabitacion: 'CEX');
				$loSegmento->setField(2, $lcClasePac);
				$loSegmento->setField(3, $lcServicio);
				$loSegmento->setField(5, $taDatos['cCodCup']);
				break;

			case 'ORC':
				$lcAccesion = $taDatos['nNumOrden']??0;
				$loSegmento->setField(1, 'NW');
				$loSegmento->setField(2, $lcAccesion);
				$loSegmento->setField(5, 'IP');
				$loSegmento->setField(7, '001' . str_repeat($lcCmpSep, 3) . $this->cFechaHora . $lcCmpSep . $this->cFechaHora . $lcCmpSep . 'S');
				$loSegmento->setField(9, $this->cFechaHora);
				$loSegmento->setField(12, $taDatos['cRegMedico'] . $lcCmpSep . $taDatos['cNombreMedico']);
				break;

			case 'OBR':
				$lcAccesion = $taDatos['nNumOrden']??0;
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
