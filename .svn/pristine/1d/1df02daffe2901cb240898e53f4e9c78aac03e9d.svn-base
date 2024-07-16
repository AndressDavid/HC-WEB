<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';

class Doc_Electrofisiologia04
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
					'cTitulo' => 'ESTUDIO ELECTROCARDIOGRAFICO HOLTER'.PHP_EOL.'Departamento de Electrofisiología',
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
		$this->crearVar(['holhcl','holmes','holmed','holder','holekg','holrca','holave',
						 'holasu','holcon','holsst','holcor','holobs','holmal','comobs',], '');
		$this->crearVar(['holfmi','holfma','holfmid','holfmad','holqtc','holvrr','holsi1','holsi2','holsi3','holpro',], 0);
		$this->crearVar(['holfmih','holfmah',], date('Y-m-d'));

		$this->aVar['holder'] = 'DI, V1, V5 ';


		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec==1:
					$this->aVar['holhcl'] = trim(mb_substr($lcDescri, 21, null, 'UTF-8'));
					break;
				// MEDICAMENTOS
				case $lnConsec==2:
					$this->aVar['holmed'] = trim(mb_substr($lcDescri, 21, null, 'UTF-8'));
					break;
				// DERIVACIONES
				case $lnConsec==3:
					$this->aVar['holder'] = trim(mb_substr($lcDescri, 21, null, 'UTF-8'));
					break;
				// EKG BASAL
				case $lnConsec==4: case $lnConsec==5:
					$this->aVar['holekg'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// RITMO
				case $lnConsec==7:
					$this->aVar['holrca'] = trim(mb_substr($lcDescri, 21, 30, 'UTF-8'));
					$this->aVar['holpro'] = intval(mb_substr($lcDescri, 61, 3, 'UTF-8'));
					break;
				// FRECUENCIA MÍNIMA
				case $lnConsec==8:
					$this->aVar['holfmi'] = intval(mb_substr($lcDescri, 13, 3, 'UTF-8'));
					$this->aVar['holfmih'] = mb_substr($lcDescri, 27, 5, 'UTF-8');
					$this->aVar['holfmid'] = intval(mb_substr($lcDescri, 36, 3, 'UTF-8'));
					break;
				// FRECUENCIA MÁXIMA
				case $lnConsec==9:
					$this->aVar['holfma'] = intval(mb_substr($lcDescri, 13, 3, 'UTF-8'));
					$this->aVar['holfmah'] = mb_substr($lcDescri, 27, 5, 'UTF-8');
					$this->aVar['holfmad'] = intval(mb_substr($lcDescri, 36, 3, 'UTF-8'));
					break;
				// ARRITMIA VENTRICULAR
				case $lnConsec>10 && $lnConsec<14:
					$this->aVar['holave'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// ARRITMIA SUPRAVENTRICULAR
				case $lnConsec>13 && $lnConsec<17:
					$this->aVar['holasu'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// CONDUCCIÓN
				case $lnConsec>16 && $lnConsec<20:
					$this->aVar['holcon'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// SEGMENTO ST
				case $lnConsec==20:
					$this->aVar['holsst'] = trim(mb_substr($lcDescri, 21, null, 'UTF-8'));
					break;
				// QTc
				case $lnConsec==21:
					$this->aVar['holqtc'] = intval(mb_substr($lcDescri, 21, 5, 'UTF-8'));
					break;
				// VARIABILIDAD RR:SDNN
				case $lnConsec==22:
					$this->aVar['holvrr'] = intval(mb_substr($lcDescri, 21, 5, 'UTF-8'));
					break;
				// SINTOMAS
				case $lnConsec==23:
					$this->aVar['holsi1'] = mb_substr($lcDescri,39,1,'UTF-8')=='X' ? 1 : 0;
					$this->aVar['holsi2'] = mb_substr($lcDescri,51,1,'UTF-8')=='X' ? 1 : 0;
					$this->aVar['holsi3'] = mb_substr($lcDescri,68,1,'UTF-8')=='X' ? 1 : 0;
					break;
				// CORRELACIÓN
				case $lnConsec==24: case $lnConsec==25:
					$this->aVar['holcor'] .= mb_substr($lcDescri, 20, null, 'UTF-8');
					break;
				// RESUMEN
				case $lnConsec==26: case $lnConsec==27:
					$this->aVar['holmes'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// ARRITMIA VENTRICULAR
				case $lnConsec==30: case $lnConsec==31:
					$this->aVar['holave'] .= $lcDescri;
					break;
				// ARRITMIA SUPRAVENTRICULAR
				case $lnConsec==40: case $lnConsec==41:
					$this->aVar['holasu'] .= $lcDescri;
					break;
				// SEGMENTO ST
				case $lnConsec==51: case $lnConsec==52:
					$this->aVar['holsst'] .= mb_substr($lcDescri, 21, null, 'UTF-8');
					break;
				// OBSERVACIONES
				case $lnConsec>99 && $lnConsec<200:
					$this->aVar['holobs'] .= $lcDescri;
					break;
				// MICROALTERNANCIA
				case $lnConsec==300:
					$this->aVar['holmal'] = trim(mb_substr($lcDescri, 29, null, 'UTF-8'));
					break;
				// COMPLICACIONES
				case $lnConsec>900:
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['holekg'] = trim($this->aVar['holekg']);
		$this->aVar['holave'] = trim($this->aVar['holave']);
		$this->aVar['holasu'] = trim($this->aVar['holasu']);
		$this->aVar['holcon'] = trim($this->aVar['holcon']);
		$this->aVar['holcor'] = trim($this->aVar['holcor']);
		$this->aVar['holmes'] = trim($this->aVar['holmes']);
		$this->aVar['holsst'] = trim($this->aVar['holsst']);
		$this->aVar['holobs'] = trim($this->aVar['holobs']);
		$this->aVar['comobs'] = trim($this->aVar['comobs']);


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


		$laTr[] = ['titulo1', 'RESUMEN HISTORIA CLÍNICA'];
		$lcDet = (empty($this->aVar['holmes']) ? '' : $this->aVar['holmes'].$lcSL)
				.(empty($this->aVar['holmed']) ? '' : 'Medicamentos :  '.$this->aVar['holmed'].$lcSL)
				.(empty($this->aVar['holekg']) ? '' : 'EKG Basal    :  '.$this->aVar['holekg']);
		$laTr[] = ['texto9', $lcDet];


		$laTr[] = ['titulo1', 'DATOS GENERALES'];
		$lcDet = (empty($this->aVar['holder']) ? '' : 'Derivaciones :  '.$this->aVar['holder'].$lcSL)
				.(empty($this->aVar['holrca']) ? '' : 'Ritmo        :  '.$this->aVar['holrca'].$lcSL)
				.(empty($this->aVar['holfmi']) && empty($this->aVar['holfma']) ? '' :
					'Frecuencia   :  Mínima de '.str_pad($this->aVar['holfmi'],3,' ',STR_PAD_LEFT).' lpm a las '.$this->aVar['holfmih'].' día '.$this->aVar['holfmid'].$lcSL.
					'             :  Máxima de '.str_pad($this->aVar['holfma'],3,' ',STR_PAD_LEFT).' lpm a las '.$this->aVar['holfmah'].' día '.$this->aVar['holfmad'].$lcSL)
				.(empty($this->aVar['holpro']) ? '' : 'Promedio     :  '.$this->aVar['holpro'].' lps');
		$laTr[] = ['texto9', $lcDet];


		$laTr[] = ['titulo1', 'ARRITMIAS VENTRICULARES'];
		$laTr[] = ['texto9', empty($this->aVar['holave']) ? 'No se documentaron.' : $this->aVar['holave']];


		$laTr[] = ['titulo1', 'ARRITMIAS SUPRAVENTRICULARES'];
		$laTr[] = ['texto9', empty($this->aVar['holasu']) ? 'No se documentaron.' : $this->aVar['holasu']];


		$laTr[] = ['titulo1', 'CONDUCCIÓN'];
		$laTr[] = ['texto9', empty($this->aVar['holcon']) ? 'Normal.' : $this->aVar['holcon']];


		$laTr[] = ['titulo1', 'SEGMENTO ST'];
		$laTr[] = ['texto9', empty($this->aVar['holsst']) ? 'Sin Cambios significativos.' : $this->aVar['holsst']];


		$laTr[] = ['titulo1', 'INTERVALO QTc'];
		$laTr[] = ['texto9', $this->aVar['holqtc'].' MS'];


		$laTr[] = ['titulo1', 'VARIABILIDAD RR:SDNN'];
		$laTr[] = ['texto9', $this->aVar['holvrr'].' MS'];


		if (!empty($this->aVar['holmal'])) {
			$laTr[] = ['titulo1', 'MICROALTERNANCIA ONDA T'];
			$laTr[] = ['texto9', $this->aVar['holmal']];
		}


		if (!empty($this->aVar['holvrr'])) {
			$laTr[] = ['titulo1', 'SÍNTOMAS'];
			$lcDet = ($this->aVar['holsi1']==1 ? 'No hay Correlación, ' : '')
					.($this->aVar['holsi2']==1 ? 'No Anota, ' : '')
					.($this->aVar['holsi3']==1 ? 'No hay Diario, ' : '');
			$lcDet = mb_substr($lcDet,0,mb_strlen(trim($lcDet),'UTF-8')-1, 'UTF-8').'.';
			$laTr[] = ['texto9', $lcDet];
		}


		if (!empty($this->aVar['holcor'])) {
			$laTr[] = ['titulo1', 'CORRELACIÓN POR:'];
			$laTr[] = ['texto9', $this->aVar['holcor']];
		}


		if (!empty($this->aVar['holobs'])) {
			$laTr[] = ['titulo1', 'CONCLUSIONES'];
			$laTr[] = ['texto9', $this->aVar['holobs']];
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
