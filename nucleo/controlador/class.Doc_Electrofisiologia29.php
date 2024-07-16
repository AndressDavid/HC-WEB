<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_Electrofisiologia29
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
				//	'cTitulo' => 'ESTUDIO ELECTROFISIOLÓGICO'.PHP_EOL.'Departamento de Electrofisiología',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Tipo Atenc',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>true,],
				];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	 *	Retornar array con los datos del documento
	 */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	/*
	 *	Consulta los datos del documento desde la BD
	 */
	private function consultarDatos($taData)
	{
		$laDatas = $this->oDb	//	riahisR029
			->select('CONSEC,DESCRI')
			->from('RIAHIS')
			->where([
				'NROING'=>$taData['nIngreso'],
				'SUBORG'=>$taData['cCUP'],
				'CONCON'=>$taData['nConsecCita'],
				'INDICE'=>70,
				'SUBIND'=>0,
			])
			->orderBy('CONSEC')
			->getAll('array');

		// Diagnósticos   LOAD RIPS
		$laDxs = $this->oDb	//	ripapsR029
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');

		// Crear Variables
		$this->crearVar(['imarhi', 'imavve', 'imauca', 'imasal', 'imaobs', 'imainf1', 'imainf2',
										 'ramhis', 'notant', 'comobs', 'RegMedico', 'Especialidad'], '');
		$this->crearVar(['imalid', 'imafca', 'imasen', 'repfin', 'gnrepfin', 'codDx', 'dscDx',], 0);

		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec == 1:
					$this->aVar['ramhis'] = trim(mb_substr($lcDescri, 21, 49, 'UTF-8'));
					break;
				// RESUMEN HISTORIA - LINEA 1
				case $lnConsec == 2:
					$this->aVar['imarhi'] = mb_substr($lcDescri, 20, 50, 'UTF-8');
					break;
				// RESUMEN HISTORIA - LINEA 2
				case $lnConsec == 2:
					$this->aVar['imarhi'] .= mb_substr($lcDescri, 50, 70, 'UTF-8');
					break;
				// LIDOCAINA
				case $lnConsec == 4:
					$this->aVar['imalid'] = floatval(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// VIA VENOSA
				case $lnConsec == 5:
					$this->aVar['imavve'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// UMBRAL DE CAPTURA
				case $lnConsec == 6:
					$this->aVar['imauca'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// SALIDA
				case $lnConsec == 7:
					$this->aVar['imasal'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// FRECUENCIA CARDIACA
				case $lnConsec == 8:
					$this->aVar['imafca'] = floatval(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// SENSIBILIDAD
				case $lnConsec == 9:
					$this->aVar['imasen'] = floatval(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// OBSERVACIONES
				case $lnConsec >= 101 && $lnConsec <= 900:
					$this->aVar['imaobs'] .= $lcDescri;
					break;
				// COMPLICACIONES
				case $lnConsec > 900:
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['imarhi'] = trim($this->aVar['imarhi']);
		$this->aVar['imaobs'] = trim($this->aVar['imaobs']);
		$this->aVar['comobs'] = trim($this->aVar['comobs']);

		// INIT RIPS
		$this->aVar['codDx'] = trim($laDxs['DG1APS']??'')=='0'? '': trim($laDxs['DG1APS']);
		$loDx = new Diagnostico($this->aVar['codDx']);
		$this->aVar['dscDx'] = $loDx->getTexto();


		// Médico que hace reporte final (estado 3)
		$laMedico = $this->oDb
			->select('FL4DET')
			->from('RIADET')
			->where([
				'INGDET'=>$taData['nIngreso'],
				'CCIDET'=>$taData['nConsecCita'],
				'CUPDET'=>$taData['cCUP'],
				'ESTDET'=>3,
			])
			->orderBy('FERDET, HRRDET DESC')
			->get('array');
		$this->aVar['RegMedico'] = trim($laMedico['FL4DET'] ?? '');

		// Especialidad del médico desde RIAORD
		$laEspAval = $this->oDb
			->select('CODORD')
			->from('RIAORD')
			->where([
				'NINORD'=>$taData['nIngreso'],
				'CCIORD'=>$taData['nConsecCita'],
				'COAORD'=>$taData['cCUP'],
			])
			->get('array');
		$this->aVar['Especialidad'] = trim($laEspAval['CODORD'] ?? '');
		$this->aVar['imainf1'] = 'Bajo efectos de anestesia local con lidocaina al ' .trim($this->fNum('imalid',2)) . ' % ' . 'por vía venosa ' . trim($this->aVar['imavve'])
														 . ' se coloca electrodo de marcapasos transitorio en el ápex del ventriculo derecho con ' . trim($this->aVar['imauca']) . ' MA de umbral de captura.';

		$this->aVar['imainf2'] = 'Se deja la siguiente programación:' . PHP_EOL
														 . '        Salida  . . . . . . : ' . trim($this->aVar['imasal']) . ' Ma' . PHP_EOL
														 . '        Frecuencia Cardiaca : ' . trim($this->fNum('imafca',2)) . ' lpm' . PHP_EOL
														 . '        Sensibilidad  . . . : ' . trim($this->fNum('imasen',2)) . '  mV';
	}
	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$lcSL = PHP_EOL;
		$laTr = [];

		$this->aReporte['cTitulo'] = $taData['oCup']->cDscrCup . PHP_EOL . 'Departamento de Electrofisiología';

		if(!empty($this->aVar['imarhi'])){
			$laTr[] = ['titulo1', 'RESUMEN DE HISTORIA'];
			$laTr[] = ['texto9', $this->aVar['imarhi']];
		}
		$laTr[] = ['titulo1', 'DATOS GENERALES'];
		$laTr[] = ['texto9', $this->aVar['imainf1'] . $lcSL . $this->aVar['imainf2']];

		if(!empty($this->aVar['imaobs'])){
			$laTr[] = ['titulo1', 'OBSERVACIONES'];
			$laTr[] = ['texto9', trim($this->aVar['imaobs'])];
		}

		if(!empty($this->aVar['comobs'])){
			$laTr[] = ['titulo1', 'COMPLICACIONES'];
			$laTr[] = ['texto9', trim($this->aVar['comobs'])];
		}


		// Firma
		$laTr[] = ['firmas', [ ['registro'=>$this->aVar['RegMedico'], 'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aVar['Especialidad'],], ], ];

		$this->aReporte['aCuerpo'] = $laTr;
	//	$this->aReporte['cTxtAntesDeCup'] = 'No.Estudio:' . ' ' . $this->aVar['eelhcl'];
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

	private function fNum($tcVar, $tnDec)
	{
		if(isset($this->aVar[$tcVar])){
			if(is_numeric($this->aVar[$tcVar])){
				return number_format($this->aVar[$tcVar], $tnDec, ',', '.');
			}
		}
		return ' ';
	}

}
