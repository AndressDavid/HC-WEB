<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';

class Doc_Electrofisiologia03
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
			'cTitulo' => 'PRUEBA DE MESA BASCULANTE'.PHP_EOL.'Departamento de Electrofisiología',
			'lMostrarEncabezado' => true,
			'lMostrarFechaRealizado' => true,
			'lMostrarViaCama' => true,
			'cTxtAntesDeCup' => '',
			'cTituloCup' => 'Tipo Atención',
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
		$laDatas = $this->oDb
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

/*
		// Diagnósticos
		$laDxs = $this->oDb
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');
*/

		// Crear Variables
		$this->crearVar(['ctrlPosHor',], []);
		$this->crearVar(['dexrhi','dexrit','dexmed','dexdos','dexsin','dexmc0','dexmc6','dexcon','ramhis','comobs',], '');
		$this->crearVar(['dexta1','dexta2','dexfca','dexmii','dexmif','dexppo',], 0);


		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec==1:
					$this->aVar['ramhis'] = trim(mb_substr($lcDescri, 20, null, 'UTF-8'));
					break;
				// POSISION HORIZONTAL
				case $lnConsec==2:
					$this->aVar['dexta1'] = trim(mb_substr($lcDescri, 24, 3, 'UTF-8'));
					$this->aVar['dexta2'] = trim(mb_substr($lcDescri, 30, 3, 'UTF-8'));
					$this->aVar['dexfca'] = trim(mb_substr($lcDescri, 38, 3, 'UTF-8'));
					$this->aVar['dexrit'] = trim(mb_substr($lcDescri, 54, 15,'UTF-8'));
					break;
				// MASAJE CAROTIDEO A 60°
				case $lnConsec==3:
					$this->aVar['dexmc6'] = mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				case $lnConsec==4:
					$this->aVar['dexmc6'] .= $lcDescri;
					break;
				// MASAJE CAROTIDEO A 0°
				case $lnConsec==5:
					$this->aVar['dexmc0'] = mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				case $lnConsec==9:
					$this->aVar['dexmc0'] .= $lcDescri;
					break;
				// SINTOMAS
				case $lnConsec==6 || $lnConsec==7:
					$this->aVar['dexsin'] .= $lcDescri;
					break;
				// PRUEBA POSITIVA
				case $lnConsec==11:
					$this->aVar['dexppo'] = mb_substr($lcDescri, 21, 2, 'UTF-8')=='SI' ? 1 : 0;
					break;
				// MINUTOS
				case $lnConsec==8:
					$this->aVar['ctrlPosHor'][0] = ['Minuto > '];
					for ($lnI=0;$lnI<10;$lnI++)
						$this->aVar['ctrlPosHor'][0][] = intval(substr($lcDescri,$lnI*4+21,3));

					break;
				// PA SISTOLICA
				case $lnConsec==17:
					$this->aVar['ctrlPosHor'][1] = ['PA Sistólica (mmHg)'];
					for ($lnI=0;$lnI<10;$lnI++)
						$this->aVar['ctrlPosHor'][1][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// PA DIASTOLICA
				case $lnConsec==18:
					$this->aVar['ctrlPosHor'][2] = ['PA Diastólica (mmHg)'];
					for ($lnI=0;$lnI<10;$lnI++)
						$this->aVar['ctrlPosHor'][2][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// FRECUENCIA CARDIACA
				case $lnConsec==19:
					$this->aVar['ctrlPosHor'][3] = ['F.C. (lpm)'];
					for ($lnI=0;$lnI<10;$lnI++)
						$this->aVar['ctrlPosHor'][3][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// CONCLUSIONES
				case $lnConsec>19 && $lnConsec<100:
					$this->aVar['dexcon'] .= $lcDescri;
					break;
				// RESUMEN HISTORIA
				case ($lnConsec>119 && $lnConsec<123) || ($lnConsec>799 && $lnConsec<816):
					$this->aVar['dexrhi'] .= $lcDescri;
					break;
				// MEDICAMENTOS - DOSIS - MIN.I - MIN.F
				case $lnConsec==123:
					$this->aVar['dexmed'] = substr($lcDescri,21,22);
					$this->aVar['dexdos'] = substr($lcDescri,44,12);
					$this->aVar['dexmii'] = intval(substr($lcDescri,60,3));
					$this->aVar['dexmif'] = intval(substr($lcDescri,64,3));
					break;
				// MINUTOS 11-15
				case $lnConsec==124:
					for ($lnI=0;$lnI<5;$lnI++)
						$this->aVar['ctrlPosHor'][0][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// PA SISTOLICA 11-15
				case $lnConsec==125:
					for ($lnI=0;$lnI<5;$lnI++)
						$this->aVar['ctrlPosHor'][1][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// PA DIASTOLICA 11-15
				case $lnConsec==126:
					for ($lnI=0;$lnI<5;$lnI++)
						$this->aVar['ctrlPosHor'][2][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// FRECUENCIA CARDIACA 11-15
				case $lnConsec==127:
					for ($lnI=0;$lnI<5;$lnI++)
						$this->aVar['ctrlPosHor'][3][] = intval(substr($lcDescri,$lnI*4+21,3));
					break;
				// COMPLICACIONES - OBSERVACIONES
				case $lnConsec>900:
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['dexrhi'] = trim($this->aVar['dexrhi']);
		$this->aVar['dexmc6'] = trim($this->aVar['dexmc6']);
		$this->aVar['dexmc0'] = trim($this->aVar['dexmc0']);
		$this->aVar['dexsin'] = trim($this->aVar['dexsin']);
		$this->aVar['dexcon'] = trim($this->aVar['dexcon']);
		$this->aVar['comobs'] = trim($this->aVar['comobs']);
		$this->aVar['ramhis'] = $taData['oIngrPaciente']->oPaciente->nNumHistoria;


		// Registro médico si está cobrado u ordenado
		$laMedico = $this->oDb
			->select('RMEEST')
			->from('RIAESTM')
			->where([
				'INGEST'=>$taData['nIngreso'],
				'CNOEST'=>$taData['nConsecCita'],
				'CUPEST'=>$taData['cCUP'],
				'TINEST'=>400,
				'NPREST'=>'0',
			])
			->get('array');
		$this->aVar['merepr'] = trim($laMedico['RMEEST'] ?? '');

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
			->orderBy('FERDET,HRRDET DESC')
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
	}


	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$lcSL = PHP_EOL;
		$laTr = [];

		if (!empty($this->aVar['dexrhi'])) {
			$laTr[] = ['titulo1', 'RESUMEN HISTORIA CLÍNICA'];
			$laTr[] = ['texto9', $this->aVar['dexrhi']];
		}


		$laTr[] = ['titulo1', 'CONTROL EN POSICIÓN HORIZONTAL'];
		$lcDet = (empty($this->aVar['dexrit']) ? '' : 'Ritmo :  '.$this->aVar['dexrit'].$lcSL)
				.(empty($this->aVar['dexfca']) ? '' : 'F.C.  :  '.$this->aVar['dexfca'].' lpm'.$lcSL)
				.(empty($this->aVar['dexta1']) && empty($this->aVar['dexta2']) ? '' : 'T.A.  :  '.$this->aVar['dexta1'].' / '.$this->aVar['dexta2'].$lcSL);
		if (!empty($lcDet)) $laTr[] = ['texto9', $lcDet];


		if (count($this->aVar['ctrlPosHor'])>0) {
			$laDatos=[];
			$laW = [40,10,10,10,10,10,10,10,10,10,10,10,10,10,10,10,];
			$laAT = ['R','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C',];
			$laA = ['L','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C',];
			for($lnI=1;$lnI<4;$lnI++){
				if(isset($this->aVar['ctrlPosHor'][$lnI])){
					$laDatos[]=['d'=>$this->aVar['ctrlPosHor'][$lnI], 'w'=>$laW, 'a'=>$laA];
				}
			}
			if(count($laDatos)>0){
				$laTr[] = ['texto9', $lcSL.'Se inclina la mesa basculante a 60° con el comportamiento de presión arterial y frecuencia cardiaca que se muestra en la tabla.'];
				$laTr[] = [
					'tabla', 
					[['d'=>$this->aVar['ctrlPosHor'][0], 'w'=>$laW, 'a'=>$laAT]],
					$laDatos,
				];
			}
		}


		if (!empty($this->aVar['dexmed']) || !empty($this->aVar['dexdos']) || !empty($this->aVar['dexmii']) || !empty($this->aVar['dexmif'])) {
			$laTr[] = ['titulo1', 'MEDICAMENTOS'];
			$lcDet = (empty($this->aVar['dexmed']) ? '' : 'Nombre :  '.$this->aVar['dexmed'].$lcSL)
					.(empty($this->aVar['dexdos']) ? '' : 'Dosis  :  '.$this->aVar['dexdos'].$lcSL)
					.(empty($this->aVar['dexmii']) && empty($this->aVar['dexmif']) ? '' : 'Min    :  Inicial: '.$this->aVar['dexmii'].'    Final: '.$this->aVar['dexmif']);
			$laTr[] = ['texto9', $lcDet];
		}


		if (!empty($this->aVar['dexsin'])) {
			$laTr[] = ['titulo1', 'SÍNTOMAS'];
			$laTr[] = ['texto9', $this->aVar['dexsin']];
		}


		if (!empty($this->aVar['dexmc6']) || !empty($this->aVar['dexmc0'])) {
			$laTr[] = ['titulo1', 'MASAJE DE SENO CAROTIDEO'];
			$lcDet = (empty($this->aVar['dexmc0']) ? '' : 'Masaje a  0° :  '.$this->aVar['dexmc0'].$lcSL)
					.(empty($this->aVar['dexmc6']) ? '' : 'Masaje a 60° :  '.$this->aVar['dexmc6']);
			$laTr[] = ['texto9', $lcDet];
		}


		if (!empty($this->aVar['dexcon'])) {
			$laTr[] = ['titulo1', 'CONCLUSIONES'];
			$laTr[] = ['texto9', $this->aVar['dexcon']];
		}


		// Firma
		$laTr[] = ['firmas', [ ['registro'=>$this->aVar['RegMedico'], 'codespecialidad'=>$this->aVar['Especialidad'],], ], ];

		$this->aReporte['aCuerpo'] = $laTr;
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
