<?php

namespace NUCLEO;

require_once __DIR__ . '/class.HL7.php';

class HL7_DRAGER extends HL7
{

	private $oDB;

	function __construct($tcModelo)
	{
		parent::__construct($tcModelo);
		global $goDb;
		$this->oDB = $goDb;
	}
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
				$loSegmento->setField(9, $this->cTipoMensaje . (empty($this->cEvento) ? '' : $lcCmpSep . $this->cEvento));
				$loSegmento->setField(10, $this->nConsecutivo);
				$loSegmento->setField(11, 'P');
				$loSegmento->setField(15, ($this->cTipoMensaje == 'ACK') ? 'NE' : 'AL');
				break;

			case 'EVN':
				$lcFechaEvento = $this->cEvento == 'A03' ? 
					$loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora, 6, '0', STR_PAD_LEFT) :
					$loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora, 6, '0', STR_PAD_LEFT);
				$loSegmento->setField(1, $this->cEvento);
				$loSegmento->setField(6, $lcFechaEvento);
				break;

			case 'PID':
				$loSegmento->setField(3, $loIngreso->nIngreso);
				$loSegmento->setField(5, $loIngreso->oPaciente->getApellidos() . $lcCmpSep . $loIngreso->oPaciente->getNombres());
				$loSegmento->setField(7, $loIngreso->oPaciente->nNacio);
				$loSegmento->setField(8, $loIngreso->oPaciente->cSexo);
				$loSegmento->setField(11, $loIngreso->oPaciente->cDireccion);
				$loSegmento->setField(18, $loIngreso->nIngreso);
				break;

			case 'PV1':
				$lcPatienClass = $loIngreso->cVia == '01' ? 'E' : ($loIngreso->cVia == '05' ? 'I' : 'O');
				$lcCama = ($loIngreso->oHabitacion->cSeccion == '' && $loIngreso->oHabitacion->cHabitacion == '') ? '' : $loIngreso->oHabitacion->cSeccion . $lcCmpSep . $loIngreso->oHabitacion->cHabitacion;
				$loSegmento->setField(2, $lcPatienClass);
				$loSegmento->setField(3, $lcCama);
				$loSegmento->setField(44, $loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora, 6, '0', STR_PAD_LEFT));
				$loSegmento->setField(45, $this->cEvento == 'A03' ? $loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora, 6, '0', STR_PAD_LEFT) : '');
				$loSegmento->setField(46, '|');
				break;

			case 'OBR':
				$lcAccesion = $taDatos['nNumOrden'];
				$loSegmento->setField(1, '1');
				$loSegmento->setField(2, $lcAccesion);
				break;
		}
	}
}