<?php
namespace NUCLEO;

class Doc_TrasladosPacientes
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
				'cTitulo' => "TRASLADO PACIENTE",
				'lMostrarFechaRealizado' => false,
				'lMostrarViaCama' => false,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aFirmas' => [],
				'aNotas' => ['notas'=>true,],
			];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}
	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();
		$laTraslado = $this->oDb
			->from('TRAPAC')
			->where([
				'INGTRA'=>$taData['nIngreso'],
				'CONTRA'=>$taData['nConsecDoc']
			])
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laDocumento['nConsecutivo']=$laTraslado['CONTRA'];
			$laDocumento['cAreaTraslado']=trim($laTraslado['ARTTRA']);
			$laDocumento['cAreaTraslada']=trim($laTraslado['SECTRA']);
			$laDocumento['cEspecialidadTraladar']=trim($laTraslado['ESPTRA']);
			$laDocumento['cMedicoRecibe']=trim($laTraslado['MRETRA']);
			$laDocumento['cSeInformaFamiliar']=trim($laTraslado['SIFTRA']);
			$laDocumento['cSeTrasladaFamiliar']=trim($laTraslado['STFTRA']);
			$laDocumento['cJustificacionTraslado']=trim($laTraslado['DESTRA']);
			$laDocumento['cSignos']=trim($laTraslado['SIGTRA']);
			$laDocumento['cUsuarioCrea']=trim($laTraslado['USRTRA']);
			$laDocumento['nFechaRealizado']=$laTraslado['FECTRA'];
			$laDocumento['nHoraRealizado']=$laTraslado['HORTRA'];
			
			if (!empty($laDocumento['cUsuarioCrea'])) {
				$laRegMed = $this->oDb
					->from('RIARGMN4')
					->where(['USUARI'=>$laDocumento['cUsuarioCrea']])
					->get('array');
				if ($this->oDb->numRows()>0) {
					$laDocumento['cNombreCrea'] = strtoupper(trim($laRegMed['NOMMED']).' '.trim($laRegMed['NNOMED']));
					$laDocumento['cRegUsuCrea'] = trim($laRegMed['REGMED']);
				}
			} 
		}
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$laTr = [];

		if(!empty($this->aDocumento['nFechaRealizado']))
		{
			$lcFechaHora=AplicacionFunciones::formatFechaHora('fechahora12', $this->aDocumento['nFechaRealizado'].' '.$this->aDocumento['nHoraRealizado']);
			$lcHabitacion=!empty($this->aDocumento['cAreaTraslada'])?('    Habitación: '.$this->aDocumento['cAreaTraslada']):'';
			$laTr[]=['titulo1', ''.$this->aDocumento['nConsecutivo'] .' - '.$lcFechaHora.$lcHabitacion];
		}

		if(!empty($this->aDocumento['cAreaTraslado']))
		{
			$lcAreaTraslada=$this->aDocumento['cAreaTraslado'];
			$lcDescAreaTraslada=trim($this->oDb->obtenerTabmae1('DE1TMA', 'CNDSGR', "CL1TMA='$lcAreaTraslada'", null, ''));
			$laTr[]=['txthtml10', 'Área trasladar: '.$lcDescAreaTraslada];
		}
		
		if(!empty($this->aDocumento['cEspecialidadTraladar']))
		{
			$lcEspecialidad=$this->aDocumento['cEspecialidadTraladar'];
			$laDescEspecialidad = $this->oDb->select('trim(DESESP) DESCRIPCION')->from('RIAESPE')->where('CODESP', '=', $lcEspecialidad)->get('array');
			$lcDescEspecialidad=isset($laDescEspecialidad['DESCRIPCION'])?trim($laDescEspecialidad['DESCRIPCION']):'';

			if (!empty($lcDescEspecialidad)){
				$laTr[]=['txthtml10','Especialidad a trasladar: '.$lcDescEspecialidad];
			}	
		}
		
		if(!empty($this->aDocumento['cMedicoRecibe']))
		{
			$laTemp = $this->oDb->select('NOMMED,NNOMED')->from('RIARGMN5')->where(['REGMED'=>$this->aDocumento['cMedicoRecibe']])->get('array');
			if ($this->oDb->numRows()>0) $lcMedico = trim(trim($laTemp['NOMMED']).' '.trim($laTemp['NNOMED']));
			$laTr[]=['txthtml10','Médico recibe: '.$lcMedico];
		}
		
		if(!empty($this->aDocumento['cSeInformaFamiliar']))
		{
			$laTr[]=['txthtml10','Se informa a familiar de traslado: '.($this->aDocumento['cSeInformaFamiliar']=='S'?'Si':'No')];
		}
		
		if(!empty($this->aDocumento['cSeTrasladaFamiliar']))
		{
			$laTr[]=['txthtml10','Se traslada en compañia de familiar: '.($this->aDocumento['cSeTrasladaFamiliar']=='S'?'Si':'No')];
		}
		
		if(!empty($this->aDocumento['cSignos']))
		{
			$lcEscalaDolor=$lcCodigoEscalaDolor='';
			$laSignosDatos=explode('~', $this->aDocumento['cSignos']);
			$lcNivelConciencia=trim($laSignosDatos[6]);
			$laDescNivelConciencia = $this->oDb->select('trim(DESENF) DESCRIPCION')->from('TABENF')->where('TIPENF', '=', '4')->where('VARENF', '=', '5')->where('ACTENF', '<>', '1')->where('REFENF', '=', $lcNivelConciencia)->get('array');
			$lcDescNivelConciencia=isset($laDescNivelConciencia['DESCRIPCION'])?trim($laDescNivelConciencia['DESCRIPCION']):'';
			$lcCodigoEscalaDolor=trim($laSignosDatos[9]);
			if (!empty($lcCodigoEscalaDolor)){
				$laEscalaDolor=$this->oDb->select('trim(DESENF) DESCRIPCION')->from('TABENF')->where('TIPENF', '=', '14')->where('VARENF', '=', '1')->where('ACTENF', '<>', '1')->where('REFENF', '=', $lcCodigoEscalaDolor)->get('array');
				$lcEscalaDolor=isset($laEscalaDolor['DESCRIPCION'])?trim($laEscalaDolor['DESCRIPCION']):'';
			}	

			$lcPuntajeNews=!empty($laSignosDatos[8])?('Puntaje NEWS: ' .$laSignosDatos[8].' puntos'):'';
			$laTr[] = ['titulo1', 'Signos vitales:', 'L'];
			$laTr[] = ['txthtml9', 'Frecuencia respiratoria: ' .$laSignosDatos[0]];
			$laTr[] = ['txthtml9', 'Saturación O2 (%): ' .$laSignosDatos[1]];
			$laTr[] = ['txthtml9', 'Temperatura (°C): ' .$laSignosDatos[2]];
			$laTr[] = ['txthtml9', 'Presión arterial sistólica: ' .$laSignosDatos[3]];
			$laTr[] = ['txthtml9', 'Presión arterial diastólica: ' .$laSignosDatos[4]];
			$laTr[] = ['txthtml9', 'Frecuencia cardíaca: ' .$laSignosDatos[5]];
			$laTr[] = ['txthtml9', 'Nivel de conciencia: ' .$lcDescNivelConciencia];
			$laTr[] = ['txthtml9', 'Necesita O2 suplementario: ' .($laSignosDatos[7]=='1'?'Si':'No')];
			$laTr[] = ['txthtml9', $lcPuntajeNews];
			$laTr[] = ['txthtml9', !empty($lcEscalaDolor)? ('Escala dolor: ' .$lcEscalaDolor):''];
		}
		
		if(!empty($this->aDocumento['cJustificacionTraslado']))
		{
			$laTr[] = ['titulo1', 'Justificación de traslado:', 'L'];
			$laTr[] = ['txthtml9', $this->aDocumento['cJustificacionTraslado']];
		}
		
		$laTr[]=['firmas', [
			['registro' => $this->aDocumento['cRegUsuCrea'],'prenombre'=>'Dr. ']]];
		$this->aReporte['aCuerpo'] = $laTr;
	}

	private function datosBlanco()
	{
		return [
			'nConsecutivo' => 0,
			'cAreaTraslado' => '',
			'cAreaTraslada' => '',
			'cEspecialidadTraladar' => '',
			'cMedicoRecibe' => '',
			'cSeInformaFamiliar' => '',
			'cSeTrasladaFamiliar' => '',
			'cJustificacionTraslado' => '',
			'cSignos' => '',
			'cUsuarioCrea' => '',
			'cRegUsuCrea' => '',
			'cNombreCrea' => '',
			'nFechaRealizado' => 0,
			'nHoraRealizado' => 0,
		];
	}
}