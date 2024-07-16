<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';

class Doc_Electrofisiologia05
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
					'cTitulo' => 'ESTUDIO DE POTENCIALES TARDIOS'.PHP_EOL.'Departamento de Electrofisiología',
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
		$this->crearVar(['epthcl','eptmes','eptobs','comobs',], '');
		$this->crearVar(['eptrfi','eptcop','eptdqr','eptrms','eptsab',], 0);


		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec==1:
					$this->aVar['epthcl'] = trim(mb_substr($lcDescri,15,10,'UTF-8'));
					break;
				// RUIDO FINAL
				case $lnConsec==2:
					$this->aVar['eptrfi'] = floatval(mb_substr($lcDescri,15,1,'UTF-8').'.'.mb_substr($lcDescri,16,2,'UTF-8'));
					break;
				// CONDICIONES OPTIMAS
				case $lnConsec==3:
					$this->aVar['eptcop'] = mb_substr($lcDescri,15,2,'UTF-8')=='SI' ? 1 : 0;
					break;
				// DQRS
				case $lnConsec==4:
					$this->aVar['eptdqr'] = floatval(mb_substr($lcDescri,15,3,'UTF-8').'.'.mb_substr($lcDescri,18,2,'UTF-8'));
					break;
				// RMS40
				case $lnConsec==5:
					$this->aVar['eptrms'] = floatval(mb_substr($lcDescri,15,3,'UTF-8').'.'.mb_substr($lcDescri,18,2,'UTF-8'));
					break;
				// SAB
				case $lnConsec==6:
					$this->aVar['eptsab'] = floatval(mb_substr($lcDescri,15,3,'UTF-8').'.'.mb_substr($lcDescri,18,2,'UTF-8'));
					break;
				// MOTIVO ESTUDIO - LINEA 1
				case $lnConsec==7:
					$this->aVar['eptmes'] = mb_substr($lcDescri,0,65,'UTF-8');
					break;
				// MOTIVO ESTUDIO - LINEA 2
				case $lnConsec==8:
					$this->aVar['eptmes'] .= $lcDescri;
					break;
				// OBSERVACIONES
				case $lnConsec>8 && $lnConsec<100:
					$this->aVar['eptobs'] .= $lcDescri;
					break;
				// COMPLICACIONES
				case $lnConsec>900:
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['eptmes'] = trim($this->aVar['eptmes']);
		$this->aVar['eptobs'] = trim($this->aVar['eptobs']);


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
			->orderBy('FERDET DESC,HRRDET DESC')
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

		$this->aReporte['cTitulo'] = $taData['oCup']->cDscrCup . PHP_EOL . 'Departamento de Electrofisiología';


		if (!empty($this->aVar['eptmes'])) {
			$laTr[] = ['titulo1', 'MOTIVO DE ESTUDIO'];
			$laTr[] = ['texto9', $this->aVar['eptmes']];
		}


		$laTr[] = ['titulo1', 'CONDICIONES TÉCNICAS DEL ESTUDIO'];
		$lcDet = ($this->aVar['eptcop']==1 ? 'Condiciones óptimas' : 'Condiciones NO óptimas')
				.(empty($this->aVar['eptrfi']) ? '' : $lcSL.'Ruido Final:  '.$this->fNum('eptrfi',1).' uV');
		$laTr[] = ['texto9', $lcDet];


		$laTr[] = ['titulo1', 'RESULTADO DEL ESTUDIO DE POTENCIALES TARDIOS (FILTRO DE 40 Hz)'];
		$laW = [30,50,50,50,]; $laA = 'C';
		$laTr[] = ['tabla', [ ['d'=>['','RESULTADO','CRITERIO','ANORMAL',],'w'=>$laW,'a'=>$laA] ],
				[
					['d'=>['DQRS',  empty($this->aVar['eptdqr'])?'':$this->fNum('eptdqr',2).' Ms','Mayor de 114 Ms',$this->aVar['eptdqr']>114?'SI':'NO',],'w'=>$laW,'a'=>$laA],
					['d'=>['RMS 40',empty($this->aVar['eptrms'])?'':$this->fNum('eptrms',2).' Ms','Menor de  20 uV',$this->aVar['eptrms']<20 ?'SI':'NO',],'w'=>$laW,'a'=>$laA],
					['d'=>['SAB',   empty($this->aVar['eptsab'])?'':$this->fNum('eptsab',2).' Ms','Mayor de  38 Ms',$this->aVar['eptsab']>38 ?'SI':'NO',],'w'=>$laW,'a'=>$laA],
				] ];
		$laW = [30,8,150,];
		$laTr[] = ['tablaSL', [], [['d'=>['','<b>NOTA:</b>','Un solo criterio indica anormalidad.<br>Dos o mas criterios aumentan sensibilidad y especifidad.'],'w'=>$laW]], ['fs'=>7]];
		$laTr[] = ['texto9', 'Por lo anterior el estudio puede considerarse:  '.($this->aVar['eptdqr']>114 || $this->aVar['eptrms']<20 || $this->aVar['eptsab']>38 ? 'ANORMAL':'NORMAL')];


		if (!empty($this->aVar['eptobs'])) {
			$laTr[] = ['titulo1', 'CONCLUSIONES'];
			$laTr[] = ['texto9', $this->aVar['eptobs']];
		}


		if (!empty($this->aVar['comobs'])) {
			$laTr[] = ['titulo1', 'COMPLICACIONES'];
			$laTr[] = ['texto9', $this->aVar['comobs']];
		}


		$laTr[] = ['firmas', [ ['registro'=>$this->aVar['RegMedico'], 'codespecialidad'=>$this->aVar['Especialidad'],], ], ];


		$laTr[] = ['titulo2', '- Advertencia -', 'C'];
		$lcDet = 'El estudio de potenciales tardíos no es recomendable en presencia de bloqueo de rama derecha o izquierda '
				.'del Haz de His, la activación tardía inherente a un bloqueo de rama puede simular potenciales tardíos '
				.'anormales, lo mismo sucede en pacientes con marcapasos ventriculares o de doble cámara, los marcapasos '
				.'auriculares no alteran significativamente el resultado del electrocardiograma de señal promediada. '
				.'Marcada irregularidad en el ritmo cardiaco o múltiples morfológicas de QRS (por ejemplo: fibrilación '
				.'auricular o extrasístoles ventriculares multifocales) en raras ocasiones causan electrocardiogramas de '
				.'señal promediada falsos positivos.';
		$laTr[] = ['texto9', $lcDet];


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
