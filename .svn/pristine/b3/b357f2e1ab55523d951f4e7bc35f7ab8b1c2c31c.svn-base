<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_Electrofisiologia11
{
	protected $oDb;
	protected $aVar = [];
	protected $aReporte = [
					//'cTitulo' => 'IMPLANTE DE CARDIOVERSOR DESFIBRILADOR'.PHP_EOL.'Departamento de Electrofisiología',
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
		$laDatas = $this->oDb	//	riahisR011
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
		$laDxs = $this->oDb	//	ripapsR011
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');


		// Crear Variables
		$this->crearVar(['motivo', 'anestesia','marcapFab', 'marcapSer', 'marcapEri', 'marcapMod', 'marcapTec', 'auriculFab', 'auriculSer',
										 'auriculMod', 'auriculCnf', 'auriculTec', 'ventrDFac', 'ventrDSer', 'ventrDMod', 'ventrDCnf', 'ventrDTec', 'icdmit',
										 'icdatp', 'icdmif', 'icdude', 'icdihv', 'icdrel', 'icdvbi', 'icdobs', 'comobs', 'icdttc1', 'icdtst1', 'icdtst2',
										 'icdttc2', 'icdfst1', 'icdfst2', 'icdftc1', 'icdftc2', 'RegMedico', 'Especialidad'], '');
		$this->crearVar(['icdhis', 'fevi', 'icdmfc', 'umbralmEARU', 'umbralmEARB', 'umbralmEVEU', 'umbralmEVEB', 'umbralvEARU', 'umbralvEARB',
										 'umbralvEVEU', 'umbralvEVEB', 'resisteEARU', 'resisteEARB', 'resisteEVEU', 'resisteEVEB', 'ampondaEARU', 'ampondaEARB',
										 'ampondaEVEU', 'ampondaEVEB', 'slwrateEARU', 'slwrateEARB', 'slwrateEVEU', 'slwrateEVEB', 'icdtfd1', 'icdtfd2', 'icdthr1',
										 'icdthr2', 'icdtat1', 'icdtat2', 'icdtcv1', 'icdtcv2', 'icdtdf1', 'icdtdf2', 'icdtso1', 'icdtso2',
										 'icdffd1', 'icdffd2', 'icdfhr1', 'icdfhr2', 'icdfat1', 'icdfat2', 'icdfcv1', 'icdfcv2', 'icdfdf1',
										 'icdfdf2', 'icdfso1', 'icdfso2', 'codDx', 'dscDx',], 0);
		$this->crearVar(['marcapFec', 'auriculFec', 'ventrDFec'], date('Y-m-d H:i:s'));

		//	Llena datos
		foreach($laDatas as $laData) {
			$lnConsec = $laData['CONSEC'];
			$lcDescri = $laData['DESCRI'];

			switch (true) {

				// HISTORIA CLINICA
				case $lnConsec==1:
					$this->aVar['icdhis'] = trim(mb_substr($lcDescri, 25, 20, 'UTF-8'));
					break;
				// MOTIVO IMPLANTE - LINEA 1
				case $lnConsec==2:
					$this->aVar['motivo'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
					break;
				// MOTIVO IMPLANTE - LINEA 2
				case $lnConsec==3:
					$this->aVar['motivo'] .= trim($lcDescri);
					break;
				// FEVI
				case $lnConsec==4:
					$this->aVar['fevi'] = trim(mb_substr($lcDescri, 25, 7, 'UTF-8'));
					break;
				// ANESTESIA
				case $lnConsec==5:
					$this->aVar['anestesia'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;

				// GENERADOR
				// FABRICANTE
				case $lnConsec==10:
					$this->aVar['marcapFab'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// MODELO
				case $lnConsec==11:
					$this->aVar['marcapMod'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// SERIE
				case $lnConsec==12:
					$this->aVar['marcapSer'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// FECHA DE IMPLANTE
				case $lnConsec==13:
					if(trim(mb_substr($lcDescri, 25, 8, 'UTF-8')) > 0){
						$this->aVar['marcapFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 25, 8))),'/');
					}
					break;
				// TECNICA
				case $lnConsec==14:
					$this->aVar['marcapTec'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// ERI
				case $lnConsec==15:
					$this->aVar['marcapEri'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;

				// ELECTRODO AURICULAR
				// FABRICANTE
				case $lnConsec==20:
					$this->aVar['auriculFab'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// MODELO
				case $lnConsec==21:
					$this->aVar['auriculMod'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// SERIE
				case $lnConsec==22:
					$this->aVar['auriculSer'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// FECHA DE IMPLANTE
				case $lnConsec==23:
					if(trim(mb_substr($lcDescri, 25, 8, 'UTF-8')) > 0){
						$this->aVar['auriculFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 25, 8))),'/');
					}
					break;
				// TECNICA
				case $lnConsec==24:
						$this->aVar['auriculTec'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
						break;
				// CONFIGURACION
				case $lnConsec==25:
						$this->aVar['auriculCnf'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;

				//ELECTRODO VENTRICULAR
				// FABRICANTE
				case $lnConsec==30:
						$this->aVar['ventrDFac'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// MODELO
				case $lnConsec==31:
						$this->aVar['ventrDMod'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// SERIE
				case $lnConsec==32:
					$this->aVar['ventrDSer'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
					break;
				// FECHA DE IMPLANTE
				case $lnConsec==33:
					if(trim(mb_substr($lcDescri, 25, 8, 'UTF-8')) > 0){
						$this->aVar['ventrDFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 25, 8))),'/');
					}
					break;
				// TECNICA
				case $lnConsec==34:
						$this->aVar['ventrDTec'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
						break;
				// CONFIGURACION
				case $lnConsec==35:
						$this->aVar['ventrDCnf'] = trim(mb_substr($lcDescri, 25, 30, 'UTF-8'));
						break;

				// PARAMETROS
				// UMBRAL (MA) ELECTRODO AURICULAR
				case $lnConsec==40:
						$this->aVar['umbralmEARU'] = floatval(trim(mb_substr($lcDescri, 35, 7, 'UTF-8')));
						$this->aVar['umbralmEARB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				//	UMBRAL (MA) ELECTRODO VENTRICULAR
				case $lnConsec==41:
						$this->aVar['umbralmEVEU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['umbralmEVEB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				//	UMBRAL (V) ELECTRODO AURICULAR
				case $lnConsec==42:
						$this->aVar['umbralvEARU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['umbralvEARB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				//	UMBRAL (V) ELECTRODO VENTRICULAR
				case $lnConsec==43:
						$this->aVar['umbralvEVEU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['umbralvEVEB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	RESISTENCIA ELECTRODO AURICULAR
				case $lnConsec==44:
						$this->aVar['resisteEARU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['resisteEARB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	RESISTENCIA ELECTRODO VENTRICULAR
				case $lnConsec==45:
						$this->aVar['resisteEVEU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['resisteEVEB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	AMPLITUD ELECTRODO AURICULAR
				case $lnConsec==46:
						$this->aVar['ampondaEARU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['ampondaEARB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	AMPLITUD ELECTRODO VENTRICULAR
				case $lnConsec==47:
						$this->aVar['ampondaEVEU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['ampondaEVEB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	SLEW RATE ELECTRODO AURICULAR
				case $lnConsec==48:
						$this->aVar['slwrateEARU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['slwrateEARB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	SLEW RATE ELECTRODO VENTRICULAR
				case $lnConsec==49:
						$this->aVar['slwrateEVEU'] = trim(mb_substr($lcDescri, 35, 7, 'UTF-8'));
						$this->aVar['slwrateEVEB'] = trim(mb_substr($lcDescri, 58, 7, 'UTF-8'));
						break;
				// 	METODO INDUCCION TV
				case $lnConsec==50:
						$this->aVar['icdmit'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	ATP(S) DE PRUEBA(S)
				case $lnConsec==51:
						$this->aVar['icdatp'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	METODO INDUCCION FV
				case $lnConsec==52:
						$this->aVar['icdmif'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	UMBRAL DE DESFIBRILACION
				case $lnConsec==53:
						$this->aVar['icdude'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	IMPEDANCIA HV INICIAL
				case $lnConsec==54:
						$this->aVar['icdihv'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	RESISTENCIA ELECTRODO ALTO VOLTAJE
				case $lnConsec==55:
						$this->aVar['icdrel'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;
				// 	VOLTAJE BATERIA INICIAL
				case $lnConsec==56:
						$this->aVar['icdvbi'] = trim(mb_substr($lcDescri, 25, 45, 'UTF-8'));
						break;

				// 	TAQUICARDIA VENTRICULAR
				// 	FRECUENCIA DETECCION
				case $lnConsec==60:
						$this->aVar['icdtfd1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdtfd2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	HR DETECCION
				case $lnConsec==61:
						$this->aVar['icdthr1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdthr2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	TECNICA ATP
				case $lnConsec==62:
						$this->aVar['icdtat1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdtat2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	TECNICA CV
				case $lnConsec==63:
						$this->aVar['icdtcv1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdtcv2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	TECNICA DF
				case $lnConsec==64:
						$this->aVar['icdtdf1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdtdf2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	SOPORTE ANTIBRADI
				case $lnConsec==65:
						$this->aVar['icdtso1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdtso2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	STATUS BATERIA
				case $lnConsec==66:
						$this->aVar['icdtst1'] = trim(mb_substr($lcDescri, 35, 12, 'UTF-8'));
						$this->aVar['icdtst2'] = trim(mb_substr($lcDescri, 58, 12, 'UTF-8'));
						break;
				// 	TIEMPO DE CARGA
				case $lnConsec==67:
						$this->aVar['icdttc1'] = trim(mb_substr($lcDescri, 35, 12, 'UTF-8'));
						$this->aVar['icdttc2'] = trim(mb_substr($lcDescri, 58, 12, 'UTF-8'));
						break;

				// FIBRILACION VENTRICULAR
				// 	FRECUENCIA DETECCION
				case $lnConsec==70:
						$this->aVar['icdffd1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdffd2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	HR DETECCION
				case $lnConsec==71:
						$this->aVar['icdfhr1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdfhr2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	TECNICA ATP
				case $lnConsec==72:
						$this->aVar['icdfat1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdfat2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	TECNICA CV
				case $lnConsec==73:
						$this->aVar['icdfcv1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdfcv2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	TECNICA DF
				case $lnConsec==74:
						$this->aVar['icdfdf1'] = trim(mb_substr($lcDescri, 35, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						$this->aVar['icdfdf2'] = trim(mb_substr($lcDescri, 58, 2, 'UTF-8')) == 'SI' ? 1 : 0;
						break;
				// 	SOPORTE ANTIBRADI
				case $lnConsec==75:
						$this->aVar['icdfso1'] = trim(mb_substr($lcDescri, 35, 10, 'UTF-8'));
						$this->aVar['icdfso2'] = trim(mb_substr($lcDescri, 58, 10, 'UTF-8'));
						break;
				// 	STATUS BATERIA
				case $lnConsec==76:
						$this->aVar['icdfst1'] = trim(mb_substr($lcDescri, 35, 12, 'UTF-8'));
						$this->aVar['icdfst2'] = trim(mb_substr($lcDescri, 58, 12, 'UTF-8'));
						break;
				// 	TIEMPO DE CARGA
				case $lnConsec==77:
						$this->aVar['icdftc1'] = trim(mb_substr($lcDescri, 35, 12, 'UTF-8'));
						$this->aVar['icdftc2'] = trim(mb_substr($lcDescri, 58, 12, 'UTF-8'));
						break;
				// 	OBSERVACIONES
				case $lnConsec >= 101 && $lnConsec <= 899:
						$this->aVar['icdobs'] .= $lcDescri;
						break;
				// 	COMPLICACIONES
				case $lnConsec > 900:
						$this->aVar['comobs'] .= $lcDescri;
						break;

			}
		}
		$this->aVar['icdhis'] = trim($this->aVar['icdhis']);

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

		$laTr[] = ['titulo1', 'MOTIVO DEL IMPLANTE'];
		$lcDetalle =  $this->aVar['motivo']
					. (empty($this->aVar['fevi'])? "": $lcSL . 'FEVI  . . : ' . $this->fNum('fevi',2) . ' %')
					. (empty($this->aVar['anestesia'])? "": $lcSL . 'Anestesia : ' . $this->aVar['anestesia']);
		$laTr[] = ['texto9', $lcDetalle];

		$laW = [29,60,18,60];

		if (!empty($this->aVar['marcapFab']) || !empty($this->aVar['marcapMod']) || !empty($this->aVar['marcapSer'])) {
			$laTr[] = ['titulo1', 'MARCAPASOS'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['marcapFab'], 'Serie:',  $this->aVar['marcapSer']] ],
						['w'=>$laW, 'd'=>['FC ERI:',		$this->aVar['marcapEri'], 'Modelo:', $this->aVar['marcapMod']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['marcapFec'], 'Técnica:',$this->aVar['marcapTec']] ],
					], ];
		}
		if (!empty($this->aVar['auriculFab']) || !empty($this->aVar['auriculMod']) || !empty($this->aVar['auriculSer'])) {
			$laTr[] = ['titulo1', 'ELECTRODO AURICULAR'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:', $this->aVar['auriculFab'], 'Serie:', $this->aVar['auriculSer']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:', $this->aVar['auriculFec'], 'Modelo:', $this->aVar['auriculMod']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['auriculCnf'], 'Técnica:', $this->aVar['auriculTec']] ],
						]];
		}
		if (!empty($this->aVar['ventrDFac']) || !empty($this->aVar['ventrDMod']) || !empty($this->aVar['ventrDSer'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:', $this->aVar['ventrDFac'], 'Serie . : ', $this->aVar['ventrDSer']] ],
						['w'=>$laW, 'd'=>['Fecha Implante : ', $this->aVar['ventrDFec'], 'Modelo  : ', $this->aVar['ventrDMod']] ],
						['w'=>$laW, 'd'=>['Configuración  : ', $this->aVar['ventrDCnf'], 'Técnica : ', $this->aVar['ventrDTec']] ],
						]];
		}
		$laTr[] = ['titulo1', 'PARÁMETROS IMPLANTE'];
		$laWt1 = [50,70,70];
		$laWt2 = [35,35,35,35];
		$laW = [50,35,35,35,35];
		$laA = ['L','C','C','C','C'];
		$laTtl = ['Umbral (Ma)','Umbral (V)','Resistencia (OHMS)','Amp. Onda P o R','Slew Rate (V/S)',];
		$laTr[] = ['tabla', [
					['w'=>$laWt1,'a'=>'C','d'=>['','ELECTRODO AURICULAR','ELECTRODO VENTRICULAR',],'f'=>[2,0,0] ],
					['w'=>$laWt2,'a'=>'C','d'=>['UNIPOLAR','BIPOLAR','UNIPOLAR','BIPOLAR'] ],
				], [
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[0],$this->fNum('umbralmEARU',2),$this->fNum('umbralmEARB',2),$this->fNum('umbralmEVEU',2),$this->fNum('umbralmEVEB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[1],$this->fNum('umbralvEARU',2),$this->fNum('umbralvEARB',2),$this->fNum('umbralvEVEU',2),$this->fNum('umbralvEVEB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[2],$this->fNum('resisteEARU',2),$this->fNum('resisteEARB',2),$this->fNum('resisteEVEU',2),$this->fNum('resisteEVEB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[3],$this->fNum('ampondaEARU',2),$this->fNum('ampondaEARB',2),$this->fNum('ampondaEVEU',2),$this->fNum('ampondaEVEB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[4],$this->fNum('slwrateEARU',2),$this->fNum('slwrateEARB',2),$this->fNum('slwrateEVEU',2),$this->fNum('slwrateEVEB',2)] ],
				], ];
		$laWtC = [180];
		$laWtC1 = [90, 90];
		$laTr[] = ['titulo1', 'PRUEBAS DE CARDIODESFIBRILADOR'];
		if (!empty($this->aVar['icdmit'])){
			$laTr[] = ['tablaSL', [], [
						['w'=>$laWtC, 'd'=>['Método Inducción TV . . : ' . trim($this->aVar['icdmit'])] ],
					]];
		}
		if (!empty($this->aVar['icdatp'])){
			$laTr[] = ['tablaSL', [], [
						['w'=>$laWtC, 'd'=>['ATP(s) de Prueba(s) . . : ' . trim($this->aVar['icdatp'])] ],
					]];
		}
		if (!empty($this->aVar['icdmif'])){
			$laTr[] = ['tablaSL', [], [
						['w'=>$laWtC, 'd'=>['Método Inducción FV . . : ' . trim($this->aVar['icdmif'])] ],
					]];
		}
		if (!empty($this->aVar['icdude']) || !empty($this->aVar['icdihv'])){
			$laTr[] = ['tablaSL', [], [
						['w'=>$laWtC1, 'd'=>['Umbral Desfibrilación . : ' . trim($this->aVar['icdude']) , ('Impedancia HV Inicial . : ' . trim($this->aVar['icdihv']))] ],
					]];
		}
		if (!empty($this->aVar['icdrel']) || !empty($this->aVar['icdvbi'])){
			$laTr[] = ['tablaSL', [], [
						['w'=>$laWtC1, 'd'=>['Resist. Elect. Alto Vol.: ' . trim($this->aVar['icdrel']) , ('Voltaje Bateria Inicial : ' . trim($this->aVar['icdvbi']))] ],
					]];
		}
		$laTr[] = ['titulo1', 'PROGRAMACION INICIAL: TAQUICARDIA VENTRICULAR'];
		$laWt1 = [26, 26, 60, 26, 26, 26];
		$laW = [26,26,20,20,20, 26, 26, 26];
		$laA = ['C','C','C','C','C', 'C', 'C', 'C'];
		$laTr[] = ['tabla', [
					['w'=>$laWt1,'a'=>'C','d'=>['Frecuencia', 'HR', 'Terapias Habilitadas', 'Soporte', 'Status', 'Tiempo'],/*'f'=>[2,0,0,0, 0, 0] */],
					['w'=>$laWt1,'a'=>'C','d'=>['Detección', 'Detección', ' ', 'Antibradi', 'Bateria', 'de carga'],  ],
					['w'=>$laW,'a'=>'C','d'=>['(ms)', '(ms)', 'ATP', 'CV', 'DF', '(lpm)', ' ', ' '  ], ],
				], [
					['w'=>$laW,'a'=>$laA,'d'=>[$this->fNum('icdtfd1',0), $this->fNum('icdthr1',0), $this->aVar['icdtat1']== 1 ? 'SI' : 'NO', $this->aVar['icdtcv1']== 1 ? 'SI' : 'NO', $this->aVar['icdtdf1']== 1 ? 'SI' : 'NO', $this->fNum('icdtso1',0), $this->aVar['icdtst1'], $this->aVar['icdttc1']] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$this->fNum('icdtfd2',0), $this->fNum('icdthr2',0), $this->aVar['icdtat2']== 1 ? 'SI' : 'NO', $this->aVar['icdtcv2']== 1 ? 'SI' : 'NO', $this->aVar['icdtdf2']== 1 ? 'SI' : 'NO', $this->fNum('icdtso2',0), $this->aVar['icdtst2'], $this->aVar['icdttc2']] ],

				], ];

		$laTr[] = ['titulo1', 'PROGRAMACION INICIAL: FIBRILACION VENTRICULAR'];
				$laTr[] = ['tabla', [
					['w'=>$laWt1,'a'=>'C','d'=>['Frecuencia', 'HR', 'Terapias Habilitadas', 'Soporte', 'Status', 'Tiempo'],/*'f'=>[2,0,0,0, 0, 0] */],
					['w'=>$laWt1,'a'=>'C','d'=>['Detección', 'Detección', ' ', 'Antibradi', 'Bateria', 'de carga'],  ],
					['w'=>$laW,'a'=>'C','d'=>['(ms)', '(ms)', 'ATP', 'CV', 'DF', '(lpm)', ' ', ' '  ], ],
				], [
					['w'=>$laW,'a'=>$laA,'d'=>[$this->fNum('icdffd1',0), $this->fNum('icdfhr1',0), $this->aVar['icdfat1']== 1 ? 'SI' : 'NO', $this->aVar['icdfcv1']== 1 ? 'SI' : 'NO', $this->aVar['icdfdf1']== 1 ? 'SI' : 'NO', $this->fNum('icdfso1',0), $this->aVar['icdfst1'], $this->aVar['icdftc1']] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$this->fNum('icdffd2',0), $this->fNum('icdfhr2',0), $this->aVar['icdfat2']== 1 ? 'SI' : 'NO', $this->aVar['icdfcv2']== 1 ? 'SI' : 'NO', $this->aVar['icdfdf2']== 1 ? 'SI' : 'NO', $this->fNum('icdfso2',0), $this->aVar['icdfst2'], $this->aVar['icdftc2']] ],
				], ];
		if(!empty($this->aVar['icdobs'])){
			$laTr[] = ['titulo1', 'OBSERVACIONES'];
			$laTr[] = ['texto9', $this->aVar['icdobs']];
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
