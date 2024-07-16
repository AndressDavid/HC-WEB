<?php
namespace NUCLEO;
require_once __DIR__ . '/class.HL7.php';

class HL7_AGFA extends HL7
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

		if ($tcTipoMensaje=='ADT' || $tcTipoMensaje=='ORM') {
			// Obtiene datos del ingreso si el mensaje lo requiere
			$this->oIngreso = new Ingreso();
			$this->oIngreso->cargarIngreso($taDatos['nIngreso']);

			//Obtiene fecha y hora y consecutivo para nuevo mensaje
			parent::fnObtenerFechaHoraMensaje();

			$this->fnObtenerViaIngreso();
			$this->fnObtenerSeccionHabitacion();
			$this->fnConsecutivoAgfaOrm();
		}

		// Solo para mensajes ORM
		if ($tcTipoMensaje=='ORM') {
			$this->cTipoMensaje = $tcTipoMensaje;
			$this->cEvento = $tcEvento;

			//Crea objeto mensaje
			$this->oMensaje = new \Net_HL7_Message();

			//Segmentos que se deben utilizar
			$this->aSegmentos = explode('-', $this->aModelo['MENSAJE'][$tcTipoMensaje]);

			//Crea mensaje
			$lnNum = 0;
			foreach($this->aSegmentos as $lcSegmento) {
				$lcPlantilla = $this->aModelo['MOD_SEG'][$lcSegmento];
				$laSegPlant = explode('|', substr($lcPlantilla, $lcSegmento=='MSH'? 3: 4));
				$this->oMensaje->addSegment(new \Net_HL7_Segment($lcSegmento, $laSegPlant));
				$this->fnActualizarSegmento($lcSegmento, $lnNum, $taDatos);
				$lnNum++;
			}
		}else{
			$this->fnConsecutivoAgfaAdt();
			parent::fnCrearMensaje($tcTipoMensaje, $tcEvento, $taDatos);
		}
	}

	/* Actualiza los segmentos con los datos enviados */
	function fnActualizarSegmento($tcSegmento, $tnNum, $taDatos)
	{
		$loSegmento=$this->oMensaje->getSegmentByIndex($tnNum);
		$lcCmpSep=$this->oMensaje->_componentSeparator;
		$loIngreso=$this->oIngreso;
		$lcDescripcionPlan=$loIngreso->obtenerDescripcionPlan();
		$lcConsecutivoCita=$taDatos['nConsecCita']??0;
		$lcEspecialidad=$taDatos['aOtrosDatos']['codigoespecialidad']??'';
		$lcDescripcionCups=$taDatos['aOtrosDatos']['descripcioncups']??'';
		$lcAgfaSala=$taDatos['aOtrosDatos']['salarealiza']??'';
		$lcObservaciones=$taDatos['aOtrosDatos']['observaciones']??'';
		$lcUsuarioOrdena=$taDatos['aOtrosDatos']['usuarioordena']??'';

		switch ($tcSegmento) {
			case 'MSH':
				$loSegmento->setField(7, $this->cFechaHora);
				$loSegmento->setField(9, $this->cTipoMensaje . (empty($this->cEvento)? '': $lcCmpSep . $this->cEvento));
				$loSegmento->setField(10, $this->nConsecutivo);
				break;

			case 'EVN':
				$lcFechaEvento = $this->cEvento=='A01' ? $loIngreso->nIngresoFecha . str_pad($loIngreso->nIngresoHora,6,'0',STR_PAD_LEFT) :
								($this->cEvento=='A03' ? $loIngreso->nEgresoFecha . str_pad($loIngreso->nEgresoHora,6,'0',STR_PAD_LEFT) :
								$this->cFechaHora);
				$loSegmento->setField(1, $this->cEvento);
				$loSegmento->setField(2, $lcFechaEvento);
				$loSegmento->setField(3, $lcFechaEvento);
				break;

			case 'PID':
				$loSegmento->setField(2, $loIngreso->oPaciente->aTipoId['TIPO'] . $loIngreso->oPaciente->nId);
				$loSegmento->setField(3, $loIngreso->oPaciente->aTipoId['TIPO'] . $loIngreso->oPaciente->nId);
				$loSegmento->setField(5, mb_substr($loIngreso->oPaciente->getApellidos(),0,30) . $lcCmpSep . mb_substr($loIngreso->oPaciente->getNombres(),0,30));
				$loSegmento->setField(7, $loIngreso->oPaciente->nNacio);
				$loSegmento->setField(8, $loIngreso->oPaciente->cSexo);
				$loSegmento->setField(11, $loIngreso->oPaciente->cDireccion .$lcCmpSep.$lcCmpSep.$lcCmpSep.$lcCmpSep.$lcCmpSep .'CO'.$lcCmpSep.'P');
				$loSegmento->setField(13, $loIngreso->oPaciente->cTelefono.$lcCmpSep.$lcCmpSep.$lcCmpSep);
				$loSegmento->setField(14, $loIngreso->oPaciente->cTelefono);
				$loSegmento->setField(19, $loIngreso->oPaciente->nNumHistoria);
				break;

			case 'PV1':
				//$loSegmento->setField(1, $this->cTipoMensaje=='ADT' ? $loIngreso->cVia: '');
				$loSegmento->setField(2, $this->cViaIngresoAgility);
				$loSegmento->setField(3, $this->cSeccionCamaAgility);
				$loSegmento->setField(18, $this->cSegmento18Agility);
				$loSegmento->setField(19, $loIngreso->oPaciente->aTipoId['TIPO'] . $loIngreso->oPaciente->nId);
				$loSegmento->setField(43, $this->cFechaHora);
				break;

			case 'ORC':
				$loSegmento->setField(1, 'NW');
				$loSegmento->setField(2, $loIngreso->nIngreso.'-'.$lcConsecutivoCita.$lcCmpSep.$lcEspecialidad);
				$loSegmento->setField(3, $lcEspecialidad);
				$loSegmento->setField(4, $loIngreso->nIngreso.'-'.$lcConsecutivoCita.$lcCmpSep.$lcEspecialidad);
				$loSegmento->setField(5, 'SC');
				$loSegmento->setField(12, $lcUsuarioOrdena . $lcCmpSep . $taDatos['cNombreMedico']);
				$loSegmento->setField(17, $loIngreso->cPlan .$lcCmpSep .$lcDescripcionPlan);
				$loSegmento->setField(24, '');
				break;

			case 'OBR':
				$lcObservaciones = str_replace("\n", " ", $lcObservaciones);
				$lcObservacionFin = $this->fcLimpiarCaracteres($lcObservaciones, $this->aModelo['CHRNOP']['CHRNOP']);
				$lcAccesion = $taDatos['nNumOrden']??0;
				$loSegmento->setField(1, '');
				$loSegmento->setField(2, $loIngreso->nIngreso.'-'.$lcConsecutivoCita.$lcCmpSep.'SHAIO');
				$loSegmento->setField(3, $lcEspecialidad.$lcCmpSep);
				$loSegmento->setField(4, $taDatos['cCodCup'].$lcCmpSep.$lcDescripcionCups.$lcCmpSep.$lcEspecialidad);
				$loSegmento->setField(6, $this->cFechaHora);
				$loSegmento->setField(7, $this->cFechaHora);
				$loSegmento->setField(13, $lcObservacionFin);
				$loSegmento->setField(16, $lcUsuarioOrdena .$lcCmpSep. $taDatos['cNombreMedico']);
				$loSegmento->setField(34, str_repeat($lcCmpSep, 4).$lcAgfaSala);

				break;

			case 'MSA':
				$loSegmento->setField(1, $taDatos['cCodAcepta']);
				$loSegmento->setField(2, $taDatos['cIdMensajeOriginal']);
				$loSegmento->setField(3, $taDatos['cObservacion']);
				break;
		}
	}

	/* Obtiene código vía ingreso Agility */
 	private function fnObtenerViaIngreso()
	{
		$this->cViaIngresoAgility=$this->cSegmento18Agility='';
		$lcViaIngreso = trim($this->oIngreso->cVia);

		$laDatos = $this->oDB
			->select('DE2TMA AS DATO')
			->from('TABMAE')
			->where(['TIPTMA'=>'AGFASO', 'CL1TMA'=>'SECCVIA', 'CL2TMA'=>$lcViaIngreso, 'ESTTMA'=>''])
			->getAll('array');
		foreach ($laDatos as $laDato) {
			$this->cViaIngresoAgility=trim($laDato['DATO']);
		}

		$laDatos = $this->oDB
			->select('DE2TMA AS DATO')
			->from('TABMAE')
			->where(['TIPTMA'=>'AGFASO', 'CL1TMA'=>'SECC18', 'CL2TMA'=>$lcViaIngreso, 'ESTTMA'=>''])
			->getAll('array');
		foreach ($laDatos as $laDato) {
			$this->cSegmento18Agility=trim($laDato['DATO']);
		}
	}

	/* Obtiene consecutivo mensaje ADT*/
 	private function fnConsecutivoAgfaAdt()
	{
		$this->nConsecutivo = $this->oDB->obtenerConsecRiacon(921, 'HCWEBAGFA');
	}

	/* Obtiene consecutivo mensaje ORM*/
 	private function fnConsecutivoAgfaOrm()
	{
		$this->nConsecutivo = $this->oDB->obtenerConsecRiacon(922, 'HCWEBAGFA');
	}

	/* Obtiene sección/habitación Agility */
	private function fnObtenerSeccionHabitacion()
	{
		$this->cSeccionCamaAgility='';
		$lcViaIngreso = trim($this->oIngreso->cVia);
		$lcPlanPaciente = trim($this->oIngreso->cPlan);
		$lcSeccion=trim($this->oIngreso->oHabitacion->cSeccion);
		$lcHabitacion=trim($this->oIngreso->oHabitacion->cHabitacion);
		$this->cSeccionCamaAgility=$lcSeccion!='' ? ($lcSeccion .'^'.$lcHabitacion) :'';

		if ($this->cSeccionCamaAgility=='' && $lcViaIngreso=='01'){
			$laDatos = $this->oDB
			->select('INT(IN4CON) AS DATO')
			->from('FACPLNC')
			->where(['PLNCON'=>$lcPlanPaciente])
			->getAll('array');
			foreach ($laDatos as $laDato) {
				$this->cSeccionCamaAgility=$laDato['DATO']==1 ?'MPP':'POS'.'^';
			}
		}

		if ($this->cSeccionCamaAgility==''){
			$laDatos = $this->oDB
			->select('CL3TMA AS DATO')
			->from('TABMAE')
			->where(['TIPTMA'=>'AGFASO', 'CL1TMA'=>'SECCVIA', 'CL2TMA'=>$lcViaIngreso, 'ESTTMA'=>''])
			->getAll('array');
			foreach ($laDatos as $laDato) {
				$this->cSeccionCamaAgility=trim($laDato['DATO']) .'^';
			}
		}
	}
}
