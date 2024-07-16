<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_Electrofisiologia19
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
				//	'cTitulo' => 'ESTUDIO ELECTROFISIOLÓGICO'.PHP_EOL.'Departamento de Electrofisiología',
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
		$laDatas = $this->oDb	//	riahisR019
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

		// Diagnósticos
		$laDxs = $this->oDb	//	ripapsR019
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');

		// Crear Variables
		$this->crearVar([
			'eelhcl', 'eelrhi', 'eelvia', 'eelrin', 'eelele', 'eelmed', 'eelpap',
			'eelahp', 'eelhvp', 'eelrsp', 'eelscp', 'eelsap', 'eelnwi', 'eelnwp',
			'eelnad', 'eelvdd', 'eelavd', 'eelcav', 'eelcva', 'eelri1', 'eelcf1',
			'eelmi1', 'eelte1', 'eelri2', 'eelcf2', 'eelmi2', 'eelte2', 'eelri3',
			'eelcf3', 'eelmi3', 'eelte3', 'eelcom', 'eelcon', 'RegMedico', 'Especialidad',
			'cAyudante', 'cAnestesiologo', 'comobs', 'lcNombreAyudante', 'lcNombreAnestesiologo',
		], '');
		$this->crearVar([
			'eelpai', 'eelahi', 'eelhvi', 'eelrsi', 'eelsci', 'eelsai', 'eelnae',
			'eelnaf', 'eelvde', 'eelvdf', 'eelave', 'eelavf', 'codDx', 'dscDx',
		], 0);

		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec == 1:
					$this->aVar['eelhcl'] = trim(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// RESUMEN HISTORIA
				case $lnConsec >= 2 && $lnConsec <= 6:
					$this->aVar['eelrhi'] .= $lnConsec == 2 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// VIAS
				case $lnConsec >= 7 && $lnConsec <= 8:
					$this->aVar['eelvia'] .= $lnConsec == 7 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// REGISTROS INTRACARDIACOS
				case $lnConsec >= 9 && $lnConsec <= 10:
					$this->aVar['eelrin'] .= $lnConsec == 9 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// ELECTROCARDIOGRAMA
				case $lnConsec >= 11 && $lnConsec <= 15:
					$this->aVar['eelele'] .= $lnConsec == 11 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// MEDICAMENTO
				case $lnConsec==16:
					$this->aVar['eelmed'] = trim(mb_substr($lcDescri, 20, 20, 'UTF-8'));
					break;
				// PA
				case $lnConsec==17:
					$this->aVar['eelpai'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelpap'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// AH
				case $lnConsec==18:
					$this->aVar['eelahi'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelahp'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// HV
				case $lnConsec==19:
					$this->aVar['eelhvi'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelhvp'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// TRS
				case $lnConsec==20:
					$this->aVar['eelrsi'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelrsp'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// TRSC
				case $lnConsec==21:
					$this->aVar['eelsci'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelscp'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// TCSA
				case $lnConsec==22:
					$this->aVar['eelsai'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelsap'] = trim(mb_substr($lcDescri, 50, 20, 'UTF-8'));
					break;
				// NODO AV WENCKEBACH - INTERVALO
				case $lnConsec==23:
					$this->aVar['eelnwi'] = trim(mb_substr($lcDescri, 30, 40, 'UTF-8'));
					break;
				// NODO AV WENCKEBACH - MEDICAMENTO
				case $lnConsec==24:
					$this->aVar['eelnwp'] = trim(mb_substr($lcDescri, 30, 40, 'UTF-8'));
					break;
				// NODA AV
				case $lnConsec==31:
					$this->aVar['eelnae'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelnaf'] = floatval(mb_substr($lcDescri, 50, 10, 'UTF-8'));
					break;
				// NODA AV OBSERVACIONES
				case $lnConsec==32:
					$this->aVar['eelnad'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// VD
				case $lnConsec==33:
					$this->aVar['eelvde'] = trim(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelvdf'] = trim(mb_substr($lcDescri, 50, 10, 'UTF-8'));
					break;
				// VD OBSERVACIONES
				case $lnConsec==34:
					$this->aVar['eelvdd'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// ANTEROGRADO VIA ACCESORIA
				case $lnConsec==35:
					$this->aVar['eelave'] = floatval(mb_substr($lcDescri, 30, 10, 'UTF-8'));
					$this->aVar['eelavf'] = floatval(mb_substr($lcDescri, 50, 10, 'UTF-8'));
					break;
				// ANTEROGRADO VIA ACCESORIA OBSERVACIONES
				case $lnConsec==36:
					$this->aVar['eelavd'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// CONDUCCION AV
				CASE $lnConsec >= 37 && $lnConsec <= 39:
					$this->aVar['eelcav'] .= $lnConsec == 37 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// CONDUCCION VA
				CASE $lnConsec >= 40 && $lnConsec <= 42:
					$this->aVar['eelcva'] .= $lnConsec == 40 ? trim(mb_substr($lcDescri, 20, 50, 'UTF-8')) : $lcDescri;
					break;
				// RITMO 1
				case $lnConsec==51:
					$this->aVar['eelri1'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// LONG. CICLO/FRECUENCIA 1
				case $lnConsec==52:
					$this->aVar['eelcf1'] = trim(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
			  // METODO INDUCCION 1
				case $lnConsec==53:
					$this->aVar['eelmi1'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// TERMINACION 1
				case $lnConsec==54:
					$this->aVar['eelte1'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// RITMO 2
				case $lnConsec==55:
					$this->aVar['eelri2'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// LONG. CICLO/FRECUENCIA 2
				case $lnConsec==56:
					$this->aVar['eelcf2'] = trim(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// METODO INDUCCION 2
				case $lnConsec==57:
					$this->aVar['eelmi2'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// TERMINACION 2
				case $lnConsec==58:
					$this->aVar['eelte2'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// RITMO 3
				case $lnConsec==59:
					$this->aVar['eelri3'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// LONG. CICLO/FRECUENCIA 3
				case $lnConsec==60:
					$this->aVar['eelcf3'] = trim(mb_substr($lcDescri, 20, 10, 'UTF-8'));
					break;
				// METODO INDUCCION 3
				case $lnConsec==61:
					$this->aVar['eelmi3'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// TERMINACION 3
				case $lnConsec==62:
					$this->aVar['eelte3'] = trim(mb_substr($lcDescri, 20, 50, 'UTF-8'));
					break;
				// COMENTARIOS
				case $lnConsec > 100 && $lnConsec < 200:
					$this->aVar['eelcom'] .= $lcDescri;
					break;
				// CONCLUSIONES
				case $lnConsec > 200 && $lnConsec < 300:
					$this->aVar['eelcon'] .= $lcDescri;
					break;
				// ANESTESIOLOGO
				case $lnConsec == 401:
					$this->aVar['cAyudante'] = trim(mb_substr($lcDescri, 56, 13, 'UTF-8'));
					break;
				// AYUDANTE
				case $lnConsec == 403:
					$this->aVar['cAnestesiologo'] = trim(mb_substr($lcDescri, 19, 13, 'UTF-8'));
					break;
				// COMPLICACIONES
				case $lnConsec > 900:
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['eelcon'] = trim($this->aVar['eelcon']);

		// RIPS
		$this->aVar['codDx'] = trim($laDxs['DG1APS'])=='0'? '': trim($laDxs['DG1APS']);
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

		if(!empty($this->aVar['cAyudante'])){
			$laCondicion= ['REGMED'=>$this->aVar['cAyudante']];
			$lcAyudante = $this->oDb
									->select("UPPER(TRIM(NOMMED) || ' ' || TRIM(NNOMED)) AS NOMBREAYUDANTE")
									->from('RIARGMN')
									->where($laCondicion)
									->get('array');
			$this->aVar['lcNombreAyudante']= 	trim($lcAyudante['NOMBREAYUDANTE']);
		}

		if(!empty($this->aVar['cAnestesiologo'])){
			$laCondicion = ['REGMED'=>$this->aVar['cAnestesiologo']];
			$lcAnestesiologo = $this->oDb
											->select("UPPER(TRIM(NOMMED) || ' ' || TRIM(NNOMED)) AS NOMBREANESTES")
											->from('RIARGMN')
											->where($laCondicion)
											->get('array');
			$this->aVar['lcNombreAnestesiologo'] = trim($lcAnestesiologo['NOMBREANESTES']);
		}
	}

	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$lcSL = PHP_EOL;
		$laTr = [];

		$this->aReporte['cTitulo'] = $taData['oCup']->cDscrCup . PHP_EOL . 'Departamento de Electrofisiología';
		if(!empty($this->aVar['eelrhi'])){
			$laTr[] = ['titulo1', 'RESUMEN DE HISTORIA'];
			$laTr[] = ['texto9', $this->aVar['eelrhi']];
		}
		if(!empty($this->aVar['eelvia'])){
			$laTr[] = ['titulo1', 'VÍAS'];
			$laTr[] = ['texto9', $this->aVar['eelvia']];
		}
		if(!empty($this->aVar['eelrin'])){
			$laTr[] = ['titulo1', 'REGISTROS INTRACARDIACOS'];
			$laTr[] = ['texto9', $this->aVar['eelrin']];
		}
		if(!empty($this->aVar['eelele'])){
			$laTr[] = ['titulo1', 'ELECTROCARDIOGRAMA'];
			$laTr[] = ['texto9', $this->aVar['eelele']];
		}

		$laW = [50, 30, 30, 80];
		$laA = ['L','C','C','L'];
		if (!empty($this->aVar['eelmed']) || !empty($this->aVar['eelpai']) || !empty($this->aVar['eelahi'])
			  || !empty($this->aVar['eelhvi']) || !empty($this->aVar['eelrsi']) || !empty($this->aVar['eelsci'])
		    || !empty($this->aVar['eelsai']) || !empty($this->aVar['eelnwi']) || !empty($this->aVar['eelnwp'])) {

			$laTr[] = ['titulo1', 'PARÁMETROS'];
			$laTtl = ['PA', 'AH','HV','TRS','TRSC', 'TCSA', 'Nodo AV Wenckebach'];
			$laTr[] = ['tabla',
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . 'SITIO','INTERVALO (ms)','NORMALES(ms)',$this->aVar['eelmed']], 'f'=>[2,0,0,0] ],
									],
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[0],$this->fNum('eelpai',0),'(20 - 50)', ' ' . $this->aVar['eelpap']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[1],$this->fNum('eelahi',0),'(50 - 120)', ' ' . $this->aVar['eelahp']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[2],$this->fNum('eelhvi',0),'(35 - 55)', ' ' . $this->aVar['eelhvp']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[3],$this->fNum('eelrsi',0),'(< 1500)', ' ' . $this->aVar['eelrsp']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[4],$this->fNum('eelsci',0),'(< 525)', ' ' . $this->aVar['eelscp']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[5],$this->fNum('eelsai',0),'(< 125)', ' ' . $this->aVar['eelsap']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[6],$this->aVar['eelnwi'],'(> 150/min)', ' ' . $this->aVar['eelnwp']] ],
									],
								];
		}

		if (!empty($this->aVar['eelnae']) || !empty($this->aVar['eelnaf']) || !empty($this->aVar['eelnad'])
			  || !empty($this->aVar['eelvde']) || !empty($this->aVar['eelvdf']) || !empty($this->aVar['eelvdd'])
		    || !empty($this->aVar['eelave']) || !empty($this->aVar['eelavf']) || !empty($this->aVar['eelavd'])) {

			$laTr[] = ['titulo1', 'PERIODOS REFRACTARIOS'];
			$laTtl = ['Nodo AV','VD','Anterogrado Vía Accesoria',];
			$laTr[] = ['tabla',
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . 'SITIO','PRE (ms)','PRF (ms)',''], 'f'=>[2,0,0,0] ],
									],
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[0],$this->fNum('eelnae',0),$this->fNum('eelnaf',0), ' ' . $this->aVar['eelnad']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[1],$this->fNum('eelvde',0),$this->fNum('eelvdf',0), ' ' . $this->aVar['eelvdd']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . $laTtl[2],$this->fNum('eelave',0),$this->fNum('eelavf',0), ' ' . $this->aVar['eelavd']] ],
									],
								];
		}

		if (!(empty($this->aVar['eelcav']) && empty($this->aVar['eelcva']))) {
			$laW1 = [45, 145];
			$laTr[] = ['titulo1', 'CONDUCCION'];
			$laTtl = ['Conducción AV' , 'Conducción VA'];
			if(!empty($this->aVar['eelcav'])){
				$laTr[] = ['tabla',[],[
																['w'=>$laW1, 'a'=>'L', 'd'=>[' ' . $laTtl[0], ' ' . $this->aVar['eelcav']]],
															],
									];                                                                                                                                     ;
			}
			if(!empty($this->aVar['eelcva'])){
				$laTr[] = ['tabla',[],[
																['w'=>$laW1, 'a'=>'L', 'd'=>[' ' . $laTtl[1], ' ' . $this->aVar['eelcva']]],
															],
									];                                                                                                                                     ;
			}
		}
		if (!empty($this->aVar['eelri1']) || !empty($this->aVar['eelcf1']) || !empty($this->aVar['eelmi1'])
			  || !empty($this->aVar['eelte1']) || !empty($this->aVar['eelri2']) || !empty($this->aVar['eelcf2'])
		    || !empty($this->aVar['eelmi2']) || !empty($this->aVar['eelte2']) || !empty($this->aVar['eelri3'])
		    || !empty($this->aVar['eelcf3']) || !empty($this->aVar['eelmi3']) || !empty($this->aVar['eelte3'])) {

			$laTr[] = ['titulo1', 'RITMOS INDUCIDOS'];
			$laTr[] = ['tabla',
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' . 'RITMO','CICLO/FREC','METODO INDUCCION','TERMINACIÓN'], 'f'=>[2,0,0,0] ],
									],
									[
										['w'=>$laW,'a'=>$laA,'d'=>[' ' .$this->aVar['eelri1'],$this->aVar['eelcf1'], ' ' . $this->aVar['eelmi1'], ' ' . $this->aVar['eelte1']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' .$this->aVar['eelri2'],$this->aVar['eelcf2'], ' ' . $this->aVar['eelmi2'], ' ' . $this->aVar['eelte2']] ],
										['w'=>$laW,'a'=>$laA,'d'=>[' ' .$this->aVar['eelri3'],$this->aVar['eelcf3'], ' ' . $this->aVar['eelmi3'], ' ' . $this->aVar['eelte3']] ],
									],
								];
		}
		if(!empty($this->aVar['eelcom'])){
			$laTr[] = ['titulo1', 'COMENTARIOS'];
			$laTr[] = ['texto9', $this->aVar['eelcom']];
		}
		if(!empty($this->aVar['eelcon'])){
			$laTr[] = ['titulo1', 'CONCLUSIONES'];
			$laTr[] = ['texto9', $this->aVar['eelcon']];
		}

		if(!empty($this->aVar['comobs'])){
			$laTr[] = ['titulo1', 'COMPLICACIONES'];
			$laTr[] = ['texto9', $this->aVar['comobs']];
		}
		if(!empty($this->aVar['lcNombreAyudante'])){
			$laTr[] = ['titulo1', 'AYUDANTE'];
			$laTr[] = ['texto9', $this->aVar['lcNombreAyudante']];
		}
		if(!empty($this->aVar['lcNombreAnestesiologo'])){
			$laTr[] = ['titulo1', 'ANESTESIOLOGO'];
			$laTr[] = ['texto9', $this->aVar['lcNombreAnestesiologo']];
		}
		// Firma
		$laTr[] = ['firmas', [ ['registro'=>$this->aVar['RegMedico'], 'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aVar['Especialidad'],], ], ];

		$this->aReporte['aCuerpo'] = $laTr;
		$this->aReporte['cTxtAntesDeCup'] = 'No.Estudio:' . ' ' . $this->aVar['eelhcl'];
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
