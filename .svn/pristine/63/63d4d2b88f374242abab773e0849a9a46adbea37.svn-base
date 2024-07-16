<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_Electrofisiologia44
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
					//'cTitulo' => 'IMPLANTE DE CARDIOVERSOR DESFIBRILADOR'.PHP_EOL.'Departamento de Electrofisiología',
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
		// Crear Variables
		$this->crearVar(['iimrhc', 'iimhcl','iimmfa', 'iimmmo', 'iimmse', 'iimmet', 'iimafa', 'iimamo', 'iimase',
										 'iimvfa', 'iimvmo', 'iimvse', 'iimude', 'iimtca', 'iimmes', 'iimuea', 'iimiea', 'iimcom',
										 'notant', 'comobs', 'RegMedico', 'Especialidad'], '');
		$this->crearVar(['iimfev', 'iimmev', 'iimihv', 'iimvba', 'iimuih', 'iimatp', 'iimta1', 'iimievd',
										'iimta2', 'iimta3', 'iimmfp', 'iimuev', 'iimiev', 'iimcap', 'iimaap', 'iimcho',
										'tearsi', 'teardi', 'pnIngr', 'repfin', 'gnrepfin', 'iimuevd', 'codDx', 'dscDx',], 0);
		$this->crearVar(['iimmfe', 'iimafe', 'iimvfe'], date('Y-m-d H:i:s'));

	 	$laDatas = $this->oDb	//	riahisR044
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
		if(count($laDatas) == 0 ){
			$laDatasC = $this->oDb // riahisR044c
								->select('CONSEC,DESCRI')
								->from('RIAHIS')
								->where([
								 'TIDHIS'=>$taData['cTipDocPac'],
								 'NIDHIS'=>$taData['nNumDocPac'],
								 'SUBORG'=>'379402',
								 'INDICE'=>70,
								 'SUBIND'=>0,
								 ])
								->orderBy('CONSEC')
								->getAll('array');
			//	Llena datos
			if(count($laDatasC) > 0){  //// riahisR044c
				foreach($laDatasC as $laDataC){
					$lnConsec = $laDataC['CONSEC'];
					$lcDescri = $laDataC['DESCRI'];
					switch (true){

					  // HISTORIA CLINICA
						case $lnConsec == 1 :
							$this->aVar['iimhcl'] = trim(mb_substr($lcDescri, 25, 20, 'UTF-8'));
							break;
						// MOTIVO IMPLANTE - LINEA 1
						case $lnConsec == 2 :
							$this->aVar['iimrhc'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
							break;
						// MOTIVO IMPLANTE - LINEA 2
						case $lnConsec == 3 :
							$this->aVar['iimrhc'] .= trim($lcDescri);
							break;
						// FEVI
						case $lnConsec == 4 :
							$this->aVar['iimfev'] = floatval(mb_substr($lcDescri, 25, 7, 'UTF-8'));
							break;

						// GENERADOR

						// FABRICANTE
						case $lnConsec == 10 :
							$this->aVar['iimmfa'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
						// MODELO
						case $lnConsec == 11 :
							$this->aVar['iimmmo'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
						// SERIE
						case $lnConsec == 12 :
							$this->aVar['iimmse'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
						// FECHA DE IMPLANTE
						case $lnConsec == 13 :
							$this->aVar['iimmfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 25, 8))),'/');
							break;
						// ERI
						case $lnConsec == 15 :
							$this->aVar['iimmev'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;

						//ELECTRODO AURICULAR

						// FABRICANTE
						case $lnConsec == 20 :
							$this->aVar['iimafa'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
					  // MODELO
						case $lnConsec == 21 :
							$this->aVar['iimamo'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
					  // SERIE
						case $lnConsec == 22 :
							$this->aVar['iimase'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
						// FECHA DE IMPLANTE
						case $lnConsec == 23 :
							$this->aVar['iimafe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 25, 8))),'/');
							break;

						// ELECTRODO VENTRICULAR

						// FABRICANTE
						case $lnConsec == 30 :
							$this->aVar['iimvfa'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
					 // MODELO
						case $lnConsec == 31 :
							$this->aVar['iimvmo'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
					 // SERIE
						case $lnConsec == 32 :
							$this->aVar['iimvse'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
							break;
						// FECHA DE IMPLANTE
						case $lnConsec == 33 :
							$this->aVar['iimvfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 25, 8))),'/');
							break;

						// PARAMETROS

						// UMBRAL DE DESFIBRILACION
						case $lnConsec == 53 :
							$this->aVar['iimude'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
							break;
						// IMPEDANCIA HV INICIAL
						case $lnConsec == 54 :
							$this->aVar['iimihv'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
							break;
						// COMENTARIOS
						case $lnConsec >= 101 && $lnConsec <= 200 :
							$this->aVar['iimcom'] .= $lcDescri;
							break;
					}
				}
			}else{
				$laDatasD = $this->oDb // riahisR044d
									->select('CONSEC,DESCRI')
									->from('RIAHIS')
									->where([
									 'TIDHIS'=>$taData['cTipDocPac'],
									 'NIDHIS'=>$taData['nNumDocPac'],
									 'SUBORG'=>'379500',
									 'INDICE'=>70,
									 'SUBIND'=>0,
									 ])
									->orderBy('CONSEC')
									->getAll('array');
				foreach($laDatasD as $laDataD){
						$lnConsec = $laDataD['CONSEC'];
						$lcDescri = $laDataD['DESCRI'];
					switch (true) {

						// HISTORIA CLINICA
						case $lnConsec == 1 :
							$this->aVar['iimhcl'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// MARCA CARDIOVERSOR
						case $lnConsec == 2 :
							$this->aVar['iimmfa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// MODELO CARDIOVERSOR
						case $lnConsec == 3 :
							$this->aVar['iimmmo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// FECHA CARDIOVERSOR
						case $lnConsec == 4 :
							$this->aVar['iimmfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
							break;
						// SERIE CARDIOVERSOR
						case $lnConsec == 5 :
							$this->aVar['iimmse'] = trim(mb_substr($lcDescri, 23, 48, 'UTF-8'));
							break;
						// ERI: VOLTAJE BATERIA
						case $lnConsec == 6 :
							$this->aVar['iimmev'] = mb_substr($lcDescri, 22, 3, 'UTF-8') . '.' . mb_substr($lcDescri, 25, 2, 'UTF-8');
							break;
						// ELEC AURICULAR FABRICANTE
						case $lnConsec == 8 :
							$this->aVar['iimafa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC AURICULAR MODELO
						case $lnConsec == 9 :
							$this->aVar['iimamo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC AURICULAR FEC IMPLANTE
						case $lnConsec == 10 :
							$this->aVar['iimafe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
							break;
						// ELEC VENTRICU FABRICANTE
						case $lnConsec == 11 :
							$this->aVar['iimvfa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC VENTRICU MODELO
						case $lnConsec == 12 :
							$this->aVar['iimvmo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC VENTRICU FEC IMPLANTE
						case $lnConsec == 13 :
							$this->aVar['iimvfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
							break;
						// FEVI 1
						case $lnConsec == 14 :
							$this->aVar['iimfev'] = floatval(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC AURICULAR SERIE
						case $lnConsec == 15 :
							$this->aVar['iimase'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// ELEC VENTRICU SERIE
						case $lnConsec == 16 :
							$this->aVar['iimvse'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// UMBRAL DESFIBRILACION
						case $lnConsec == 19 :
							$this->aVar['iimude'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
							break;
						// IMPEDANCIA HV INICIAL
						case $lnConsec == 20 :
							$this->aVar['iimihv'] = floatval(mb_substr($lcDescri, 22, 3, 'UTF-8'));
							break;
						// RESUMEN LINEA 1
						case $lnConsec == 35 :
							$this->aVar['iimrhc'] = mb_substr($lcDescri, 0, 50, 'UTF-8');
							break;
						// RESUMEN LINEA 2
						case $lnConsec == 36 :
							$this->aVar['iimrhc'] .= trim($lcDescri);
							break;
						// COMENTARIOS
						case $lnConsec > 36 && $lnConsec < 200 :
							$this->aVar['iimcom'] .= trim($lcDescri);
							break;
					}
				}
			}
		}
		foreach($laDatas as $laData){	//riahisR044
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true){

				// HISTORIA CLINICA
				case $lnConsec == 1 :
					$this->aVar['iimhcl'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// MARCA CARDIOVERSOR
				case $lnConsec == 2 :
					$this->aVar['iimmfa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// MODELO CARDIOVERSOR
				case $lnConsec == 3 :
					$this->aVar['iimmmo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// FECHA CARDIOVERSOR
				case $lnConsec == 4 :
					$this->aVar['iimmfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
					break;
				// SERIE CARDIOVERSOR
				case $lnConsec == 5 :
					$this->aVar['iimmse'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ERI: VOLTAJE BATERIA
				case $lnConsec == 6 :
					$this->aVar['iimmev'] = mb_substr($lcDescri, 22, 3, 'UTF-8') . '.' . mb_substr($lcDescri, 25, 2, 'UTF-8');
					break;
				// ERI: TIEMPO DE CARGA
				case $lnConsec == 7 :
					$this->aVar['iimmet'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC AURICULAR FABRICANTE
				case $lnConsec == 8 :
					$this->aVar['iimafa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC AURICULAR MODELO
				case $lnConsec == 9 :
					$this->aVar['iimamo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC AURICULAR FEC IMPLANTE
				case $lnConsec == 10 :
					$this->aVar['iimafe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
					break;
				// ELEC VENTRICU FABRICANTE
				case $lnConsec == 11 :
					$this->aVar['iimvfa'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC VENTRICU MODELO
				case $lnConsec == 12 :
					$this->aVar['iimvmo'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC VENTRICU FEC IMPLANTE
				case $lnConsec == 13 :
					$this->aVar['iimvfe'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($lcDescri, 22, 8))),'/');
					break;
				// FEVI 1
				case $lnConsec == 14 :
					$this->aVar['iimfev'] = floatval(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC AURICULAR SERIE
				case $lnConsec == 15 :
					$this->aVar['iimase'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ELEC VENTRICU SERIE
				case $lnConsec == 16 :
					$this->aVar['iimvse'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// UMBRAL DESFIBRILACION
				case $lnConsec == 19 :
					$this->aVar['iimude'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// IMPEDANCIA HV INICIAL
				case $lnConsec == 20 :
					$this->aVar['iimihv'] = floatval(mb_substr($lcDescri, 22, 3, 'UTF-8'));
					break;
				// ERI: VOLTAJE BATERIA
				case $lnConsec == 21 :
					$this->aVar['iimvba'] = mb_substr($lcDescri, 22, 3, 'UTF-8') . '.' . mb_substr($lcDescri, 25, 2, 'UTF-8');
					break;
				// ERI: TIEMPO DE CARGA
				case $lnConsec == 22 :
					$this->aVar['iimtca'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// ULTIMA IMPEDANCIA HV
				case $lnConsec == 23 :
					$this->aVar['iimuih'] = floatval(mb_substr($lcDescri, 22, 3, 'UTF-8'));
					break;
				// CHOQUES
				case $lnConsec == 24 :
					$this->aVar['iimcho'] = (mb_substr($lcDescri, 22, 2, 'UTF-8')) == 'SI' ? 1 : 0;
					break;
				// CHOQUES - APROPIADO
				case $lnConsec == 25 :
					$this->aVar['iimcap'] = (mb_substr($lcDescri, 22, 2, 'UTF-8')) == 'SI' ? 1 : 0;
					break;
				// ATP
				case $lnConsec == 26 :
					$this->aVar['iimatp'] = (mb_substr($lcDescri, 22, 2, 'UTF-8')) == 'SI' ? 1 : 0;
					break;
				// ATP - APROPIADO
				case $lnConsec == 27 :
					$this->aVar['iimaap'] = (mb_substr($lcDescri, 22, 2, 'UTF-8')) == 'SI' ? 1 : 0;
					break;
				// TERAPIAS ACTIVAS
				case $lnConsec == 28 :
					$this->aVar['iimta1'] = (mb_substr($lcDescri, 26, 1, 'UTF-8')) == 'X' ? 1 : 0;
					$this->aVar['iimta2'] = (mb_substr($lcDescri, 32, 1, 'UTF-8')) == 'X' ? 1 : 0;
					$this->aVar['iimta3'] = (mb_substr($lcDescri, 38, 1, 'UTF-8')) == 'X' ? 1 : 0;
					break;
				// MARCAPASOS: FRECUENCIA PROGRAMADA
				case $lnConsec == 29 :
					$this->aVar['iimmfp'] = floatval(mb_substr($lcDescri, 22, 3, 'UTF-8'));
					$this->aVar['teardi'] = floatval(mb_substr($lcDescri, 26, 3, 'UTF-8'));
					$this->aVar['tearsi'] = floatval(mb_substr($lcDescri, 30, 3, 'UTF-8'));
					break;
				// MODO ESTIMULO
				case $lnConsec == 30 :
					$this->aVar['iimmes'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// UMBRAL ESTIMULO VENTRICULAR
				case $lnConsec == 31 :
					$this->aVar['iimuev'] = mb_substr($lcDescri, 22, 3, 'UTF-8') . '.' . mb_substr($lcDescri, 25, 2, 'UTF-8');
					$this->aVar['iimuevd'] = mb_substr($lcDescri, 53, 3, 'UTF-8') . '.' . mb_substr($lcDescri, 56, 2, 'UTF-8');
					break;
				// UMBRAL ESTIMULO AURICULAR
				case $lnConsec == 32 :
					$this->aVar['iimuea'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// IMPEDANCIA ELECTRODO VENTRICULAR
				case $lnConsec == 33 :
					$this->aVar['iimiev'] = floatval(mb_substr($lcDescri, 22, 3, 'UTF-8'));
					$this->aVar['iimievd'] = floatval(mb_substr($lcDescri, 51, 3, 'UTF-8'));
					break;
				// IMPEDANCIA ELECTRODO AURICULAR
				case $lnConsec == 34 :
					$this->aVar['iimiea'] = trim(mb_substr($lcDescri, 22, 48, 'UTF-8'));
					break;
				// RESUMEN LINEA 1
				case $lnConsec == 35 :
					$this->aVar['iimrhc'] = trim(mb_substr($lcDescri, 0, 50, 'UTF-8'));
					break;
				// RESUMEN LINEA 2
				case $lnConsec == 36 :
					$this->aVar['iimrhc'] .= trim($lcDescri);
					break;
				// COMENTARIOS
				case $lnConsec > 36 &&  $lnConsec < 200 :
					$this->aVar['iimcom'] .= $lcDescri;
					break;
				// COMPLICACIONES
				case $lnConsec > 900 :
					$this->aVar['comobs'] .= $lcDescri;
					break;
			}
		}
		$this->aVar['iimrhc'] = trim($this->aVar['iimrhc']);
		$this->aVar['iimcom'] = trim($this->aVar['iimcom']);

		// Diagnósticos  ******** LOAD RIPS
		$laDxs = $this->oDb	//	ripapsR044
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');

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
	}

	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */

	private function prepararInforme($taData)
	{
		$lcSL = PHP_EOL;
		$laTr = [];

		$this->aReporte['cTitulo'] = $taData['oCup']->cDscrCup . PHP_EOL . 'Departamento de Electrofisiología';

		$laTr[] = ['titulo1', 'RESUMEN HISTORIA CLINICA'];
		$lcDetalle =  $this->aVar['iimrhc']
					. (empty($this->aVar['iimfev'])? "": $lcSL . 'FEVI  . . : ' . $this->fNum('iimfev',2) . ' %');
		$laTr[] = ['texto9', $lcDetalle];

		$laW = [30,60,18,60];

		if (!empty($this->aVar['iimmfa']) || !empty($this->aVar['iimmmo']) || !empty($this->aVar['iimmse'])) {
			$laTr[] = ['titulo1', 'MARCAPASOS'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante . . : ',	$this->aVar['iimmfa'], 'Serie . : ',  $this->aVar['iimmse']] ],
						['w'=>$laW, 'd'=>['Fecha Implante : ',		$this->aVar['iimmfe'], 'Modelo. : ', $this->aVar['iimmmo']] ],
						['w'=>$laW, 'd'=>['ERI: V Bateria : ',$this->fNum('iimmev', 2), 'T Carga : ',$this->aVar['iimmet']] ],
					], ];
		}
		if (!empty($this->aVar['iimafa']) || !empty($this->aVar['iimamo']) || !empty($this->aVar['iimase'])) {
			$laTr[] = ['titulo1', 'ELECTRODO AURICULAR'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante . . : ', $this->aVar['iimafa'], 'Serie . : ', $this->aVar['iimase']] ],
						['w'=>$laW, 'd'=>['Fecha Implante : ', $this->aVar['iimafe'], 'Modelo. : ', $this->aVar['iimamo']] ],
						]];
		}
		if (!empty($this->aVar['iimvfa']) || !empty($this->aVar['iimvmo']) || !empty($this->aVar['iimvse'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante . . : ', $this->aVar['iimvfa'], 'Serie . : ', $this->aVar['iimvse']] ],
						['w'=>$laW, 'd'=>['Fecha Implante : ', $this->aVar['iimvfe'], 'Modelo. : ', $this->aVar['iimvmo']] ],
						]];
		}

		if(!empty($this->aVar['iimude']) || !empty($this->aVar['iimihv'])){
			$laTr[] = ['titulo1', 'DATOS DEL IMPLANTE'];
			$lcDetalle =  (empty($this->aVar['iimude'])? "": 'Umbral de Desfibrilación : ' . $this->aVar['iimude'])
									 .(empty($this->aVar['iimihv'])? "": $lcSL . 'Impedancia HV Inicial. . : ' . $this->fNum('iimihv',0));
			$laTr[] = ['texto9', $lcDetalle];
		}

		$laW1 = [40,50,35,60];
		$laW2 = [40,15,15,15];
		$laW3 = [65,25,35,20];
		$laTr[] = ['titulo1', 'DATOS DEL SEGUIMIENTO CARDIODESFIBRILADOR'];
		$laTr[] = ['tablaSL', [], [
					['w'=>$laW1, 'd'=>['Voltaje Batería. . . :', $this->fNum('iimvba', 2), 'Tiempo Carga . . : ', trim($this->aVar['iimtca'])] ],
					['w'=>$laW1, 'd'=>['Ultima Impedancia HV :', $this->aVar['iimuih'], 'Tensión Arterial : ', $this->aVar['tearsi'] . '/' .$this->aVar['teardi']] ],
					['w'=>$laW1, 'd'=>['Choques. . . . . . . :', $this->aVar['iimcho']==1?'SI':'NO', 'Apropiado. . . . : ', $this->aVar['iimcap'] == 1 ? 'SI' : 'NO'] ],
					['w'=>$laW1, 'd'=>['ATP. . . . . . . . . : ', $this->aVar['iimatp']==1?'SI':'NO', 'Apropiado. . . . : ', $this->aVar['iimaap'] == 1 ? 'SI' : 'NO'] ],
					]];
		$laTr[] = ['titulo1', 'DATOS DEL SEGUIMIENTO MARCAPASOS'];
		$laTr[] = ['tablaSL', [], [
					['w'=>$laW2, 'd'=>['Terapias Activadas . : ', ($this->aVar['iimta1'] == 1 ? 'ATP     ' : '' ) , ($this->aVar['iimta2'] == 1 ? 'CV     ' : '' ), ($this->aVar['iimta3'] == 1 ? 'DF     ' : '')] ],
					['w'=>$laW1, 'd'=>['Frecuencia Programada: ', $this->aVar['iimmfp'], 'Modo Estimulo  . : ', $this->aVar['iimmes']] ],
					['w'=>$laW3, 'd'=>['Umbral Estimulación.  :  Ventricular : ', ( $this->fNum('iimuev', 2)) . (((empty($this->aVar['iimuevd']))|| ($this->aVar['iimuevd'])==0 ) ? ''  : '/' . $this->aVar['iimuevd']), 'Auricular . . . : ', $this->aVar['iimuea']] ],
					['w'=>$laW3, 'd'=>['Impedancia Electrodo :  Ventricular : ',  $this->aVar['iimiev'] . ((empty($this->aVar['iimievd']) ) ? ''  : '/' . $this->aVar['iimievd']), 'Auricular . . . : ', $this->aVar['iimiea']] ],
					]];
		if(!empty($this->aVar['iimcom'])){
			$laTr[] = ['titulo1', 'COMENTARIOS'];
			$laTr[] = ['texto9', $this->aVar['iimcom']];
		}
		if(!empty($this->aVar['comobs'])){
			$laTr[] = ['titulo1', 'COMPLICACIONES'];
			$laTr[] = ['texto9', $this->aVar['comobs']];
		}

	// Firma
		$laTr[] = ['firmas', [ ['registro'=>$this->aVar['RegMedico'], 'prenombre'=>'Dr. ', 'codespecialidad'=>$this->aVar['Especialidad'],], ], ];

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
