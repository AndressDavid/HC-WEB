<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';

class Doc_Electrofisiologia39
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
					'cTitulo' => 'Revisión (Reprogramacion) de Aparato Marcapasos'.PHP_EOL.'Departamento de Electrofisiología',
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
			->getAll('array');

		// BUSCA DATOS IMPLANTE
		$laOrden = $this->oDb
			->select('NINORD,COAORD,CCIORD')
			->from('RIAORD18')
			->where([
				'TIDORD'=>$taData['cTipDocPac'],
				'NIDORD'=>$taData['nNumDocPac'],
				'ESTORD'=>3,
			])
			->in('COAORD', ['378200','378300'])
			->getAll('array');

		$laDatasB = [];
		if (is_array($laOrden)) {
			if (count($laOrden)>0) {
				$laDatasB = $this->oDb
					->select('CONSEC,DESCRI')
					->from('RIAHIS')
					->where([
						'NROING'=>$laOrden[0]['NINORD'],
						'SUBORG'=>$laOrden[0]['COAORD'],
						'CONCON'=>$laOrden[0]['CCIORD'],
						'INDICE'=>70,
						'SUBIND'=>0,
					])
					->getAll('array');
			}
		}

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
		$this->crearVar(['dimhis','dimmim','dimmfa','dimmmo','dimmse','dimmfc','dimeaf','dimeam','dimeas',
						 'dimeac','dimedf','dimedm','dimeds','dimedc','dimeif','dimeim','dimeis','dimeic',
						 'rammes','ramdep','rampmo','ramhis','merepr','comobs',
						 'RegMedico','Especialidad', ], '');
		$this->crearVar(['ramfmi','ramfim','ramuvd','ramumd','ramuvi','ramumi','ramuav','ramuma',
						 'ramaop','ramaor','ramiea','ramivd','ramivi','rampmi','rampma','rampav',
						 'rampam','ramvdv','ramviv','ramvdm','ramvim','rampp1','rampp2','rampau', ], 0);
		$this->crearVar(['dimmfe','dimead','dimedd','dimeid', ], date('Y-m-d H:i:s'));


		if (is_array($laDatasB) && count($laDatasB)>0) {
			foreach ($laDatasB as $laData) {

				switch ($laData['CONSEC']) {

			//	****************
			//	** MARCAPASOS **
			//	****************

					// HISTORIA CLINICA
					case 1:
						$this->aVar['dimhis'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
						break;
					// MOTIVO DEL IMPLANTE 1
					case 2:
						$this->aVar['dimmim'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
						break;
					// MOTIVO DEL IMPLANTE 2
					case 3:
						$this->aVar['dimmim'] .= rtrim($laData['DESCRI']);
						break;
					// GENERADOR FABRICANTE
					case 7:
						$this->aVar['dimmfa'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// GENERADOR MODELO
					case 8:
						$this->aVar['dimmmo'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// GENERADOR SERIE
					case 9:
						$this->aVar['dimmse'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// GENERADOR FECHA IMPLANTE
					case 9:
						$this->aVar['dimmfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
						break;

			//	*************************
			//	** ELECTRODO AURICULAR **
			//	*************************
					// ELEC AURICULAR FABRICANTE
					case 14:
						$this->aVar['dimeaf'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// ELEC AURICULAR MODELO
					case 15:
						$this->aVar['dimeam'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// ELEC AURICULAR SERIE
					case 16:
						$this->aVar['dimeas'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// ELEC AURICULAR FECHA IMPLANTE
					case 17:
						$this->aVar['dimead'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
						break;

			//	**************************
			//	** ELECTRODO VETRICULAR **
			//	**************************
					// ELEC VENTRICULAR FABRICANTE
					case 21:
						$this->aVar['dimedf'] = trim(mb_substr($laData['DESCRI'], 36, 16, 'UTF-8'));
						$this->aVar['dimeif'] = trim(mb_substr($laData['DESCRI'], 53, 16, 'UTF-8'));
						break;
					// ELEC VENTRICULAR MODELO
					case 22:
						$this->aVar['dimedm'] = trim(mb_substr($laData['DESCRI'], 36, 16, 'UTF-8'));
						$this->aVar['dimeim'] = trim(mb_substr($laData['DESCRI'], 53, 16, 'UTF-8'));
						break;
					// ELEC VENTRICULAR SERIE
					case 23:
						$this->aVar['dimeds'] = trim(mb_substr($laData['DESCRI'], 36, 16, 'UTF-8'));
						$this->aVar['dimeis'] = trim(mb_substr($laData['DESCRI'], 53, 16, 'UTF-8'));
						break;
					// ELEC VENTRICULAR FECHA IMPLANTE
					case 24:
						$this->aVar['dimedd'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 16))),'/');
						$this->aVar['dimeid'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 53, 16))),'/');
						break;


					// ELEC AURICULAR CONFIGURACION
					case 61:
						$this->aVar['dimeac'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
					// ELEC VENTRICULAR CONFIGURACION
					case 62:
						$this->aVar['dimedc'] = trim(mb_substr($laData['DESCRI'], 36, 16, 'UTF-8'));
						$this->aVar['dimeic'] = trim(mb_substr($laData['DESCRI'], 53, 16, 'UTF-8'));
						break;
					// FC ERI
					case 63:
						$this->aVar['dimmfc'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
						break;
				}
			}
		}


		//	LLENA DATOS DE MARCAPASO (REVISIÓN)
		foreach($laDatas as $laData) {

			switch ($laData['CONSEC']) {

			//	****************
			//	** MARCAPASOS **
			//	****************

				// HISTORIA CLINICA
				case 1:
					$this->aVar['dimhis'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
					break;
				// MOTIVO DEL IMPLANTE 1
				case 2:
					$this->aVar['dimmim'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
					break;
				// MOTIVO DEL IMPLANTE 2
				case 3:
					$this->aVar['dimmim'] .= rtrim($laData['DESCRI']);
					break;
				// GENERADOR FABRICANTE
				CASE 7:
					$this->aVar['dimmfa'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR MODELO
				case 8:
					$this->aVar['dimmmo'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR SERIE
				case 9:
					$this->aVar['dimmse'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR FECHA IMPLANTE
				case 10:
					$this->aVar['dimmfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
					break;

			//	*************************
			//	** ELECTRODO AURICULAR **
			//	*************************

				// ELEC AURICULAR FABRICANTE
				case 14:
					$this->aVar['dimeaf'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR MODELO
				case 15:
					$this->aVar['dimeam'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR SERIE
				case 16:
					$this->aVar['dimeas'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR FEC IMPLANTE
				case 17:
					$this->aVar['dimead'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
					break;

			//**************************
			//** ELECTRODO VETRICULAR **
			//**************************

				// ELEC VENTRICU FABRICANTE
				case 21:
					$this->aVar['dimedf'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['dimeif'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU MODELO
				case 22:
					$this->aVar['dimedm'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['dimeim'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU SERIE
				case 23:
					$this->aVar['dimeds'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['dimeis'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU FEC IMPLANTE
				case 24:
					$this->aVar['dimedd'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 16))),'/');
					$this->aVar['dimeid'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 53, 34))),'/');
					break;

				// ELEC AURICULAR CONFIGURACION
				case 61:
					$this->aVar['dimeac'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
					break;
				// ELEC VENTRICULAR CONFIGURACION
				case 62:
					$this->aVar['dimedc'] = trim(mb_substr($laData['DESCRI'], 36, 16, 'UTF-8'));
					$this->aVar['dimeic'] = trim(mb_substr($laData['DESCRI'], 53, 16, 'UTF-8'));
					break;
				// FC ERI
				case 63:
					$this->aVar['dimmfc'] = trim(mb_substr($laData['DESCRI'], 36, 34, 'UTF-8'));
					break;


		//	***************************
		//	**  REVISION MARCAPASOS  **
		//	***************************

				// MARCAPASOS - FC ERI (IMAN)
				case 102:
					$this->aVar['rammfc'] = trim(substr($laData['DESCRI'], 20, 50));
					break;
				// MODO ESTIMULACION
				case 103:
					$this->aVar['rammes'] = trim(substr($laData['DESCRI'], 20, 50));
					break;
				// FC MIN
				case 104:
					$this->aVar['ramfmi'] = intval(substr($laData['DESCRI'], 20, 3));
					break;
				// FC IMAN
				case 105:
					$this->aVar['ramfim'] = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					break;
				// UMBRAL VENTRICULAR V
				case 106:
					$lcUmbVent = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					if ($lcUmbVent>0) {
						$this->aVar['ramuvd'] = $lcUmbVent;
						$this->aVar['ramumd'] = 1;
					}
					$lcUmbVent = floatval(substr($laData['DESCRI'], 30, 3) . '.' . substr($laData['DESCRI'], 33, 2));
					if ($lcUmbVent>0) {
						$this->aVar['ramuvi'] = $lcUmbVent;
						$this->aVar['ramumi'] = 1;
					}
					break;
				// UMBRAL VENTRICULAR MS
				case 107:
					$lcUmbVent = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					if ($lcUmbVent>0) {
						$this->aVar['ramuvd'] = $lcUmbVent;
						$this->aVar['ramumd'] = 2;
					}
					$lcUmbVent = floatval(substr($laData['DESCRI'], 30, 3) . '.' . substr($laData['DESCRI'], 33, 2));
					if ($lcUmbVent>0) {
						$this->aVar['ramuvi'] = $lcUmbVent;
						$this->aVar['ramumi'] = 2;
					}
					break;
				// UMBRAL AURICULAR V
				case 108:
					$this->aVar['ramuav'] = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					if ($this->aVar['ramuav']>0) $this->aVar['ramuma'] = 1;
					break;
				// UMBRAL AURICULAR MS
				case 109:
					$this->aVar['ramuav'] = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					if ($this->aVar['ramuav']>0) $this->aVar['ramuma'] = 2;
					break;
				// ONDA P
				case 110:
					$this->aVar['ramaop'] = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					break;
				// ONDA R
				case 111:
					$this->aVar['ramaor'] = floatval(substr($laData['DESCRI'], 20, 3) . '.' . substr($laData['DESCRI'], 23, 2));
					break;
				// DEPENDENCIA
				case 112:
					$this->aVar['ramdep'] = trim(substr($laData['DESCRI'], 20, 50));
					break;
				// IMPEDANCIA ELECTRODOS AURICULAR
				case 113:
					$this->aVar['ramiea'] = intval(substr($laData['DESCRI'], 19, 4));
					break;
				// IMPEDANCIA ELECTRODOS VENTRICULAR
				case 114:
					$this->aVar['ramivd'] = intval(substr($laData['DESCRI'], 19, 4));
					$this->aVar['ramivi'] = intval(substr($laData['DESCRI'], 29, 4));
					break;
				// PROGRAMACION FINAL - FC MIN
				case 115:
					$this->aVar['rampmi'] = intval(substr($laData['DESCRI'], 20, 3));
					break;
				// PROGRAMACION FINAL - FC MAX
				case 116:
					$this->aVar['rampma'] = intval(substr($laData['DESCRI'], 20, 3));
					break;
				// MODO
				case 117:
					$this->aVar['rampmo'] = trim(substr($laData['DESCRI'], 20, 50));
					break;
				// AURICULA AMPLITUD V
				case 118:
					$this->aVar['rampav'] = floatval(substr($laData['DESCRI'], 20, 2) . '.' . substr($laData['DESCRI'], 22, 1));
					break;
				// AURICULA AMPLITUD MS
				case 119:
					$this->aVar['rampam'] = floatval(substr($laData['DESCRI'], 20, 2) . '.' . substr($laData['DESCRI'], 22, 1));
					break;
				// VENTRICULA AMPLITUD V
				case 120:
					$this->aVar['ramvdv'] = floatval(substr($laData['DESCRI'], 20, 2) . '.' . substr($laData['DESCRI'], 22, 1));
					$this->aVar['ramviv'] = floatval(substr($laData['DESCRI'], 30, 2) . '.' . substr($laData['DESCRI'], 32, 1));
					break;
				// VENTRICULA AMPLITUD MS
				case 121:
					$this->aVar['ramvdm'] = floatval(substr($laData['DESCRI'], 20, 2) . '.' . substr($laData['DESCRI'], 22, 1));
					$this->aVar['ramvim'] = floatval(substr($laData['DESCRI'], 30, 2) . '.' . substr($laData['DESCRI'], 32, 1));
					break;
				// PRESION ARTERIAL
				case 122:
					$this->aVar['rampp1'] = intval(substr($laData['DESCRI'], 20, 3));
					$this->aVar['rampp2'] = intval(substr($laData['DESCRI'], 26, 3));
					$this->aVar['rampau'] = substr($laData['DESCRI'], 34, 1)=='S' ? 1 : 0;
					break;
			}
			// COMPLICACIONES
			if ($laData['CONSEC']>900)
				$this->aVar['comobs'] .= $laData['DESCRI'];
		}
		$this->aVar['comobs'] = trim($this->aVar['comobs']);
		$this->aVar['ramhis'] = $taData['oIngrPaciente']->oPaciente->nNumHistoria;


		// Registro médico si esta cobrado u ordenado
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

		$this->aReporte['cTitulo'] = $taData['oCup']->cDscrCup . $lcSL . 'Departamento de Electrofisiología';

		$laTr[] = ['titulo1', 'MOTIVO DEL IMPLANTE'];
		$laTr[] = ['texto9', $this->aVar['dimmim']];

		$laW = [29,60,18,60];
		if (!empty($this->aVar['dimmfa']) || !empty($this->aVar['dimmse']) || !empty($this->aVar['dimmfc']) || !empty($this->aVar['dimmmo'])) {
			$laTr[] = ['titulo1', 'MARCAPASOS'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['dimmfa'], 'Serie:',  $this->aVar['dimmse']] ],
						['w'=>$laW, 'd'=>['FC ERI:',		$this->aVar['dimmfc'], 'Modelo:', $this->aVar['dimmmo']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['dimmfe']] ],
					] ];
		}
		if (!empty($this->aVar['dimeaf']) || !empty($this->aVar['dimeas']) || !empty($this->aVar['dimeam']) || !empty($this->aVar['dimeac'])) {
			$laTr[] = ['titulo1', 'ELECTRODO AURICULAR'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['dimeaf'], 'Serie:',  $this->aVar['dimeas']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['dimead'], 'Modelo:', $this->aVar['dimeam']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['dimeac']] ],
					] ];
		}
		if (!empty($this->aVar['dimedf']) || !empty($this->aVar['dimeds']) || !empty($this->aVar['dimedm']) || !empty($this->aVar['dimedc'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR DERECHO'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['dimedf'], 'Serie:',  $this->aVar['dimeds']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['dimedd'], 'Modelo:', $this->aVar['dimedm']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['dimedc']] ],
					] ];
		}
		if (!empty($this->aVar['dimeif']) || !empty($this->aVar['dimeis']) || !empty($this->aVar['dimeim']) || !empty($this->aVar['dimeic'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR IZQUIERDO'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['dimeif'], 'Serie:',  $this->aVar['dimeis']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['dimeid'], 'Modelo:', $this->aVar['dimeim']] ],
						['w'=>$laW, 'd'=>['Configuración:',$this->aVar['dimeic'] ] ],
					] ];
		}


		$laTr[] = ['titulo1', 'REVISIÓN'];
		if (!empty($this->aVar['rammes']))
			$laTr[] = ['texto9', 'Modo Estimulación:  ' . $this->aVar['rammes'] ];

		$laDet = [	(empty($this->aVar['ramfmi']) ? '' : $this->fNum('ramfmi',0)),
					(empty($this->aVar['ramfim']) ? '' : $this->fNum('ramfim',2)),
					(empty($this->aVar['ramuav']) ? '' : $this->fNum('ramuav',2).($this->aVar['ramuma']==1?' v':($this->aVar['ramuma']==2?' ms':''))),
					//(empty($this->aVar['ramuvd']) ? '' : $this->fNum('ramuvd',2).($this->aVar['ramumd']==1?' v':($this->aVar['ramumd']==2?' ms':''))), ];
					($this->fNum('ramuvd',2).($this->aVar['ramumd']==1?' v':($this->aVar['ramumd']==2?' ms':''))), ];
		if (empty($this->aVar['ramuvi']) && empty($this->aVar['ramivi'])) {
			$laCab = [	['d'=>['FC','Umbral<br>Auricular','Umbral<br>Ventricular','Onda (mv)','Impedancia Electrodos',],'w'=>[38,19,38,38,57],'a'=>'C','f'=>[0,2,2,0,0,0,0], ],
						['d'=>['Mínima','Imán','P','R','Auricular','Ventric.',],'w'=>[19,19,19,19,28.5,28.5],'a'=>'C', ] ];
		} else {
			$laCab = [	['d'=>['FC','Umbral<br>Auricular','Umbral. Ventric.','Onda (mv)','Impedancia Electrodos',],'w'=>[38,19,38,38,57],'a'=>'C','f'=>[0,2,0,0,0,0,0,0], ],
						['d'=>['Mínima','Imán','DER','IZQ','P','R','Auric.','Vent.D','Vent.I',],'w'=>[19,19,19,19,19,19,19,19,19,19,],'a'=>'C', ] ];
			//$laDet[] = empty($this->aVar['ramuvi']) ? '' : $this->fNum('ramuvi',2).($this->aVar['ramumi']==1?' v':($this->aVar['ramumi']==2?' ms':''));
			$laDet[] = $this->fNum('ramuvi',2).($this->aVar['ramumi']==1?' v':($this->aVar['ramumi']==2?' ms':''));
		}
		$laDet[] = empty($this->aVar['ramaop']) ? '' : $this->fNum('ramaop',2);
		$laDet[] = empty($this->aVar['ramaor']) ? '' : $this->fNum('ramaor',2);
		$laDet[] = empty($this->aVar['ramiea']) ? '' : $this->aVar['ramiea'].' ohm';
		$laDet[] = empty($this->aVar['ramivd']) ? '' : $this->aVar['ramivd'].' ohm';
		if (empty($this->aVar['ramuvi']) && empty($this->aVar['ramivi'])) {
			$laW = [19,19,19,38,19,19,28.5,28.5,];

		} else {
			$laDet[] = empty($this->aVar['ramivi']) ? '' : $this->aVar['ramivi'].' ohm';
			$laW = [19,19,19,19,19,19,19,19,19,19,];
		}
		$laTr[] = ['tabla', $laCab, [ ['d'=>$laDet,'w'=>$laW,'a'=>'C'] ], ];


		$laTr[] = ['titulo1', 'PROGRAMACIÓN FINAL'];
		if (!empty($this->aVar['rampmo']))
			$laTr[] = ['texto9', 'Modo:  ' . $this->aVar['rampmo'] ];

		$laW = [19,19,19,19,19,19,38,];
		$laCab = [	['d'=>['FC','Auricular Amplitud','Ventric. Amplitud','<br>Presión Arterial',],'w'=>[38,38,38,38,],'a'=>'C','f'=>[0,0,0,2] ],
					['d'=>['Mínima','Máxima','V','Ms','V','Ms',],'w'=>[19,19,19,19,19,19,],'a'=>'C' ] ];
		$laDet = [	( empty($this->aVar['rampmi']) ? '' : $this->aVar['rampmi'] ),
					( empty($this->aVar['rampma']) ? '' : $this->aVar['rampma'] ),
					( empty($this->aVar['rampav']) ? '' : $this->fNum('rampav',1) ),
					( empty($this->aVar['rampam']) ? '' : $this->fNum('rampam',1) ),
					( empty($this->aVar['ramvdv']) ? '' : $this->fNum('ramvdv',1) ),
					( empty($this->aVar['ramvdm']) ? '' : $this->fNum('ramvdm',1) ),
					( empty($this->aVar['rampp1']+$this->aVar['rampp2']) ? '' : $this->aVar['rampp1'].' / '.$this->aVar['rampp2'] ),
					];
		$laTr[] = ['tabla', $laCab, [ ['d'=>$laDet,'w'=>$laW,'a'=>'C'] ], ];

		if (!empty($this->aVar['comobs'])) {
			$laTr[] = ['titulo1', 'COMENTARIOS'];
			$laTr[] = ['texto9', $this->aVar['comobs']];
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
