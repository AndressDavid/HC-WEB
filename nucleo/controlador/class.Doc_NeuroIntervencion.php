<?php
namespace NUCLEO;

require_once ('class.UsuarioRegMedico.php');
require_once ('class.Diagnostico.php');
require_once ('class.CentroCosto.php');
require_once ('class.Cup.php');
use NUCLEO\UsuarioRegMedico;
use NUCLEO\Diagnostico;
use NUCLEO\CentroCosto;
use NUCLEO\Cup;


class Doc_NeuroIntervencion
{
	protected $oDb;
	protected $aVar = [];
	protected $aTipoAnestesia = [];
	protected $aSalas = [];
	protected $aViasEntrada = [];

	protected $aReporte = [
					'cTitulo' => 'PROCEDIMIENTOS NEURORADIOLOGÍA INTERVENCIONISTA',
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => [
						'notas'=>true,
						'forma'=>'RIA133E',
						'codproc'=>'NOTANEUI' ],
				];


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
		$this->consultarSalas();
		$this->consultarTipoAnestesia();
		$this->consultarViasEntrada();

		$this->crearVar(['sala','descripcion','tipoAnestesia'], '');
		$this->crearVar(['fechaRealiza','horaRealiza','cnsCita',], 0);
		$this->crearVar(['via','procedimientos','diagnostico',], []);
		$this->crearVar(['centrocosto',], null);


		// Datos Cirugía
		$laDatas = $this->oDb
			->select('SLRCRH,DG1CRH,DG2CRH')
			->from('FACCIRH')
			->where([
					'INGCRH'=>$taData['nIngreso'],
					'CNSCRH'=>$taData['nConsecCons'],
				])
			->get('array');
		if (is_array($laDatas)) {
			if (count($laDatas)>0) {
				$this->aVar['sala'] = trim($laDatas['SLRCRH']);
				// Diagnóstico
				$lcCodigo = trim($laDatas['DG2CRH']);
				$loDx = new Diagnostico($lcCodigo, $taData['oCitaProc']->nFechaRealiza);
				$this->aVar['diagnostico'] = [
					'codigo'=>$lcCodigo,
					'nombre'=>$loDx->getTexto(),
				];
				// Centro de costo
				$this->aVar['centrocosto'] = new CentroCosto(trim($laDatas['DG1CRH']));
			}
		}

		// Descripción Informe
		$laDatas = $this->oDb
			->select('FECNEU,HORNEU,OBSNEU')
			->from('HISNEUL01')
			->where([
					'INGNEU'=>$taData['nIngreso'],
					'CIRNEU'=>$taData['nConsecCons'],
				])
			->getAll('array');
		if (is_array($laDatas)) {
			if (count($laDatas)>0) {
				$this->aVar['fechaRealiza'] = $laDatas[0]['FECNEU'];
				$this->aVar['horaRealiza'] = $laDatas[0]['HORNEU'];
				foreach ($laDatas as $laFila) {
					$this->aVar['descripcion'] .= $laFila['OBSNEU'];
				}
			}
		}

		// Vía
		$laDatas = $this->oDb
			->select('FILLE3')
			->from('RIAHIS')
			->where([
					'NROING'=>$taData['nIngreso'],
					'CONCON'=>$taData['nConsecCita'],
				])
			->where('INDICE=70 AND CONSEC>1')
			->get('array');
		if (is_array($laDatas)) {
			if (count($laDatas)>0) {
				$lcViaCodigo = trim($laDatas['FILLE3']);
				if (!empty($lcViaCodigo)) {
					$this->aVar['via'] = [
						'codigo' => $lcViaCodigo,
						'nombre' => $this->aViasEntrada[$lcViaCodigo],
					];
				}
			}
		}

		// Procedimientos
		// Número de cita de cada procedimiento
		$laDatas = $this->oDb
			->select('F.DS1CRQ,F.ESPCRQ,F.AY1CRQ,F.ANECRQ,F.TANCRQ,O.CCIORD')
			->from('FACCIRQ F')
			->leftJoin('RIAORD O','F.INGCRQ=O.NINORD AND F.CNSCRQ=O.CCOORD AND F.DS1CRQ=O.COAORD',null)
			->where([
					'F.INGCRQ'=>$taData['nIngreso'],
					'F.CNSCRQ'=>$taData['nConsecCons'],
				])
			->getAll('array');
		if (is_array($laDatas)) {
			if (count($laDatas)>0) {
				$this->aVar['cnsCita'] = 999999999;
				foreach ($laDatas as $laDat) {
					$laDat = array_map('trim',$laDat);
					//$this->aVar['procedimientos'][] = $this->itemDatoProc();
					$lcCUP=$lcCupDescripcion=$lcCirujanoReg=$lcCirujanoNombre=$lcCirujanoEsp=
					$lcAyudanteReg=$lcAyudanteNombre=$lcAnestesiologoReg=$lcAnestesiologoNombre='';
					$lnCirujanoId=$lnAyudanteId=$lnJustificacion=0;

					// Datos CUP
					$lcCUP = $laDat['DS1CRQ'];
					$loCup = new Cup($lcCUP);
					$lcCupDescripcion = substr($loCup->cDscrCup,0,85);

					// Datos Cirujano, Ayudante, Anestesiólogo
					$lcCirujanoReg = str_pad($laDat['ESPCRQ'],13,'0',STR_PAD_LEFT);
					$lnCirujanoId = $laDat['ESPCRQ'];
					$lcAyudanteReg = str_pad($laDat['AY1CRQ'],13,'0',STR_PAD_LEFT);
					$lnAyudanteId = $laDat['AY1CRQ'];
					$lcAnestesiologoReg = str_pad($laDat['ANECRQ'],13,'0',STR_PAD_LEFT);
					$loMed = new UsuarioRegMedico();
					if ($loMed->cargarRegistro($lcCirujanoReg)) {
						$lcCirujanoNombre = trim($loMed->cApellido1.' '.$loMed->cNombre1);
						$lcCirujanoEsp = $loMed->getCodEspecialidad();
						$loMed->cargarEspecialidad($lcCirujanoEsp);
						$lcCirujanoEspDsc = $loMed->getDscEspecialidad();
					}
					if ($loMed->cargarRegistro($lcAyudanteReg))
						$lcAyudanteNombre = trim($loMed->cApellido1.' '.$loMed->cNombre1);
					if ($loMed->cargarRegistro($lcAnestesiologoReg))
						$lcAnestesiologoNombre = trim($loMed->cApellido1.' '.$loMed->cNombre1);
					$loMed = null;

					// Se omite consulta de Número de justificación


					// Adiciona procedimiento
					$this->aVar['procedimientos'][] = [
						'concir' => $taData['nConsecCons'],
						'ccicir' => $laDat['CCIORD'],
						'salcir' => $this->aVar['sala'],
						'codpro' => $lcCUP,
						'despro' => $lcCupDescripcion,
						'pnopro' => ($loCup->cRef5=='NOPB' ? 'N' : ''),
						'codcir' => $lcCirujanoReg,
						'cedcir' => $lnCirujanoId,
						'espcir' => $lcCirujanoEsp,
						'esdcir' => $lcCirujanoEspDsc,
						'descir' => $lcCirujanoNombre,
						'codayu' => $lcAyudanteReg,
						'cedayu' => $lnAyudanteId,
						'desayu' => $lcAyudanteNombre,
						'codvia' => $this->aVar['via']['codigo'] ?? '',
						'dvhvia' => $this->aVar['via']['nombre'] ?? '',
						'coddpr' => $this->aVar['diagnostico']['codigo'] ?? '',
						'desdpr' => $this->aVar['diagnostico']['nombre'] ?? '',
						'cjuado' => $lnJustificacion,
						'rancir' => $lcAnestesiologoReg,
						'nancir' => $lcAnestesiologoNombre,
						'tancir' => $laDat['TANCRQ'],	// Tipo anestesia
						'pejpro' => $loCup->cPrograma,
					];
					if ($this->aVar['cnsCita'] > $laDat['CCIORD']) $this->aVar['cnsCita'] = $laDat['CCIORD'];
					if (!empty($laDat['TANCRQ'])) $this->aVar['tipoAnestesia'] = $laDat['TANCRQ'];
				}
			}
		}
		unset($laDatas);
	}


	private function prepararInforme($taData)
	{
		$laCirujanos = $laCirujanosFirma = $laTr = [];
		$lcSL = PHP_EOL;

		//$lnFechaHora = str_replace(' ','',str_replace(':','',str_replace('-','',$taData['tFechaHora'])));
		$lnFechaHora = intval($taData['oCitaProc']->nFechaRealiza.$taData['oCitaProc']->nHoraRealiza/100)*100;
		$lcFechaHora = str_replace(':00 ',' ',AplicacionFunciones::formatFechaHora('fechahora12',$lnFechaHora,'/'));
		$laTr[] = ['texto9', 'Fecha/Hora Realizado: '.$lcFechaHora];

		$laTr[] = ['titulo1', str_pad(' '.$this->aReporte['cTitulo'].' ', 90, '-', STR_PAD_BOTH)];
		$lcTxt = 'Centro de Costos: '.$this->aVar['centrocosto']->cId.' - '.$this->aVar['centrocosto']->cNombre.$lcSL
				.'Realizado En    : '.$this->aVar['sala'].$lcSL
				.'Tipo Anestesia  : '.(empty($this->aVar['tipoAnestesia'])?'':($this->aTipoAnestesia[$this->aVar['tipoAnestesia']]??'')).$lcSL
				.'Diagnóstico     : '.$this->aVar['diagnostico']['codigo'].' - '.$this->aVar['diagnostico']['nombre'].$lcSL
				.'Vía             : '.$this->aVar['via']['nombre'].$lcSL;
		$laTr[] = ['texto9', $lcTxt];

		if (count($this->aVar['procedimientos'])>0) {
			$laTr[] = ['titulo1', 'PROCEDIMIENTOS REALIZADOS'];
			$lnNum = 0;
			foreach ($this->aVar['procedimientos'] as $laProc) {

				if (!in_array($laProc['codcir'], $laCirujanos)) {
					$laCirujanos[] = $laProc['codcir'];
					$laCirujanosFirma[] = ['texto_firma'=>"Dr. {$laProc['descir']}{$lcSL}Reg.: {$laProc['codcir']}{$lcSL}Especialidad: {$laProc['esdcir']}"];
					//$laCirujanosFirma[] = ['registro'=>$laProc['codcir'],'codespecialidad'=>$laProc['espcir']];
				}

				$lnNum++;
				$laTr[] = ['titulo3', $lnNum.'  '.$laProc['despro']];
				if (!empty($laProc['descir']))
					$laTr[] = ['texto9', 'Cirujano        : '.$laProc['descir']];
				if (!empty($laProc['desayu']))
					$laTr[] = ['texto9', 'Ayudante        : '.$laProc['desayu']];
				if (!empty($laProc['nancir']))
					$laTr[] = ['texto9', 'Anestesiólogo   : '.$laProc['nancir']];
			}
		}


		$laTr[] = ['titulo1', 'DESCRIPCIÓN'];
		$laTr[] = ['texto9', $this->aVar['descripcion']];


		$laTr[] = ['firmas', $laCirujanosFirma];


		$this->aReporte['aCuerpo'] = $laTr;
	}


	//	TIANNE
	private function consultarTipoAnestesia()
	{
		$laDatas = $this->oDb
			->select('SUBSTR(TABDSC, 1, 30) AS DESTAN, SUBSTR(TABCOD, 1, 2) AS CODTAN')
			->from('PRMTAB02')
			->where('TABTIP=\'TAN\'')
			->orderBy('TABDSC')
			->getAll('array');

		if(is_array($laDatas)) {
			if(count($laDatas)>0) {
				foreach($laDatas as $laData) {
					$laData = array_map('trim',$laData);
					$this->aTipoAnestesia[$laData['CODTAN']] = $laData['DESTAN'];
				}
			}
		}
	}


	//	CODSAL
	private function consultarSalas()
	{
		$laDatas = $this->oDb
			->select('TRIM(SECHAB)||TRIM(NUMHAB) AS NUMHAB')
			->from('FACHABL0')
			->where('IDDHAB=\'0\' AND SECHAB LIKE \'SH\'')
			->orderBy('SECHAB, NUMHAB')
			->getAll('array');

		if (is_array($laDatas))
			if (count($laDatas)>0)
				foreach ($laDatas as $laData)
					$this->aSalas[] = trim($laData['NUMHAB']);
	}


	//	HMVIAE
	private function consultarViasEntrada()
	{
		$laDatas = $this->oDb
			->select('DE1TMA AS DESVIA, CL3TMA AS CODVIA')
			->from('TABMAEL01')
			->where('TIPTMA=\'HEMODI\' AND CL1TMA=\'CLASSHM\' AND CL2TMA=\'HMVIAE\' AND CL3TMA<>\'\'')
			->orderBy('DE1TMA')
			->getAll('array');

		if(is_array($laDatas)) {
			if(count($laDatas)>0) {
				foreach($laDatas as $laData) {
					$laData = array_map('trim',$laData);
					$this->aViasEntrada[$laData['CODVIA']] = $laData['DESVIA'];
				}
			}
		}
	}


	/*
	 *	Crea o establece valores a variables en $this->aVar
	 *	@param $taVars: array, lista de variables
	 *	@param $tuValor: valor que deben tomar las variables
	 */
	private function crearVar($taVars=[], $tuValor=null)
	{
		foreach($taVars as $tcVar)
			$this->aVar[$tcVar] = $tuValor;
	}

}
