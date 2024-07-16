<?php
namespace NUCLEO;
require_once __DIR__ . '/class.HL7.php';

class HL7_HEXALIS extends HL7
{
	
	private $oDB;
	/* Constructor de la clase */
	function __construct($tcModelo)
	{
		parent::__construct($tcModelo);
		$this->cIniBloque=$this->cFinBloque='';
		
		global $goDb;
		$this->oDB = $goDb;
	}
	
	function fnCrearMensaje($tcTipoMensaje, $tcEvento, $taDatos=[])
	{
		$this->cUsuarioOrdena=$taDatos['aOtrosDatos']['usuarioordena']??'';
		$this->cViaIngresoHexalis=$taDatos['aOtrosDatos']['viaingresohexalis']??'';
		$this->cNombreUsuario=$taDatos['aOtrosDatos']['apellidosnombresusuario']??'';
		$this->cUbicacionUrgencias=$taDatos['aOtrosDatos']['ubicacionpaciente']??'';
		
		// Solo para mensajes ORM 
		if ($tcTipoMensaje=='ORM') {
			$this->cTipoMensaje = $tcTipoMensaje;
			$this->cEvento = $tcEvento;

			// Obtiene datos del ingreso si el mensaje lo requiere
			$this->oIngreso = new Ingreso();
			$this->oIngreso->cargarIngreso($taDatos['nIngreso']);

			//Obtiene fecha y hora y consecutivo para nuevo mensaje
			parent::fnObtenerFechaHoraMensaje();
			parent::fnObtenerConsecutivoMensajeSEQ(true);

			//Crea objeto mensaje
			$this->oMensaje = new \Net_HL7_Message();

			//Segmentos que se deben utilizar
			$this->aSegmentos = explode('-', $this->aModelo['MENSAJE'][$tcTipoMensaje]);

			//Crea mensaje
			$lnNum=0;
			foreach($this->aSegmentos as $lcSegmento) {
				if ($lcSegmento!='ORC' && $lcSegmento!='OBR'){
					$lcPlantilla = $this->aModelo['MOD_SEG'][$lcSegmento];
					$laSegPlant = explode('|', substr($lcPlantilla, $lcSegmento=='MSH'? 3: 4));
					$this->oMensaje->addSegment(new \Net_HL7_Segment($lcSegmento, $laSegPlant));
					$this->fnActualizarSegmento($lcSegmento, $lnNum, $taDatos);
					$lnNum++;
				}	
			}
			
			foreach($taDatos['cCodCup']['grupolinea'] as $laDatos){
				foreach(['ORC','OBR'] as $lcSegmento) {
					$lcPlantilla = $this->aModelo['MOD_SEG'][$lcSegmento];
					$laSegPlant = explode('|', substr($lcPlantilla, $lcSegmento=='MSH'? 3: 4));
					$this->oMensaje->addSegment(new \Net_HL7_Segment($lcSegmento, $laSegPlant));
					$this->fnActualizarSegmento($lcSegmento, $lnNum, $laDatos);
					$lnNum++;
				}
			}
		}else{
			parent::fnCrearMensaje($tcTipoMensaje, $tcEvento, $taDatos);
		}
	}

	function fnActualizarSegmento($tcSegmento, $tnNum, $taDatos)
	{
		$loSegmento=$this->oMensaje->getSegmentByIndex($tnNum);
		$lcCmpSep=$this->oMensaje->_componentSeparator;
		$lcDescripcionPlan=$this->oIngreso->obtenerDescripcionPlan();
		
		switch ($tcSegmento) {
			case 'MSH':
				$loSegmento->setField(7, $this->cFechaHora);
				$loSegmento->setField(9, $this->cTipoMensaje .(empty($this->cEvento)? '': $lcCmpSep . $this->cEvento));
				$loSegmento->setField(10, $this->nConsecutivo);
				break;

			case 'PID':
				$loSegmento->setField(2, $this->oIngreso->oPaciente->aTipoId['ABRV'] .$this->oIngreso->oPaciente->nId);
				$loSegmento->setField(4, $this->oIngreso->oPaciente->nId.$lcCmpSep.$this->oIngreso->oPaciente->aTipoId['ABRV']);
				$loSegmento->setField(5, mb_substr($this->oIngreso->oPaciente->getNombres(),0,30) .$lcCmpSep .mb_substr($this->oIngreso->oPaciente->getApellidos(),0,30));
				$loSegmento->setField(7, $this->oIngreso->oPaciente->nNacio);
				$loSegmento->setField(8, $this->oIngreso->oPaciente->cSexo);
				$loSegmento->setField(9, mb_substr($this->oIngreso->oPaciente->getNombres(),0,30) .$lcCmpSep . mb_substr($this->oIngreso->oPaciente->getApellidos(),0,30));
				$loSegmento->setField(11, $this->oIngreso->oPaciente->cDireccion .str_repeat($lcCmpSep, 5) .'CO'.$lcCmpSep.'P');
				$loSegmento->setField(13, $this->oIngreso->oPaciente->cTelefono);
				$loSegmento->setField(14, $this->oIngreso->oPaciente->cTelefono);
				$loSegmento->setField(16, 'S');
				break;

			case 'PV1':
				$loSegmento->setField(2, $this->cViaIngresoHexalis);
				$loSegmento->setField(3, $this->cUbicacionUrgencias.$this->oIngreso->oHabitacion->cSeccion .'-'.$this->oIngreso->oHabitacion->cHabitacion);
				$loSegmento->setField(19, $this->cFechaHora);
				$loSegmento->setField(45, $this->cFechaHora);
				break;

			case 'IN1':
				$loSegmento->setField(3, $this->oIngreso->cPlan);
				$loSegmento->setField(4, $lcDescripcionPlan);
				break;

			case 'ORC':
				$lcConsecutivoCita=$taDatos['consecutivocita'];
				$loSegmento->setField(1, 'NW');
				$loSegmento->setField(2, '0'.$this->oIngreso->nIngreso.'-'.$lcConsecutivoCita);
				$loSegmento->setField(4, '0'.$this->oIngreso->nIngreso);
				$loSegmento->setField(5, 'SC');
				$loSegmento->setField(7, str_repeat($lcCmpSep, 3) .$this->cFechaHora .str_repeat($lcCmpSep, 2));
				$loSegmento->setField(12, $this->cUsuarioOrdena.$lcCmpSep.$this->cNombreUsuario	.str_repeat($lcCmpSep, 12));
				break;

			case 'OBR':
				$lcObservaciones = $this->fcLimpiarCaracteres($taDatos['observaciones'], $this->aModelo['CHRNOP']['CHRNOP']);
				$lcConsecutivoCita=$taDatos['consecutivocita'];
				$lcCodigoHexalis=$taDatos['codigohexalis'];
				$lcDescripcionCups=$taDatos['descripcioncups'];
				$loSegmento->setField(2, '0'.$this->oIngreso->nIngreso.'-'.$lcConsecutivoCita.$lcCmpSep.'-');
				$loSegmento->setField(4, $lcCodigoHexalis.$lcCmpSep.$lcDescripcionCups);
				$loSegmento->setField(6, $this->cFechaHora);
				$loSegmento->setField(13, $lcObservaciones);
				$loSegmento->setField(31, $lcCmpSep);
				break;
		}
	}
}
