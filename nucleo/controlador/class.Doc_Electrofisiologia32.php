<?php
namespace NUCLEO;
require_once __DIR__ .'/class.AplicacionFunciones.php';
require_once __DIR__ .'/class.Diagnostico.php';

class Doc_Electrofisiologia32
{
	protected $oDb;
	protected $aVar = [];
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => 'Implante de Marcapasos'.PHP_EOL.'Departamento de Electrofisiología',
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
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
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

		// Diagnósticos
		$laDxs = $this->oDb
			->select('FPRAPS,DG1APS,DG2APS,DGCAPS,FAQAPS,AUTAPS')
			->from('RIPAPS')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCita'],
			])
			->get('array');


		// Crear Variables
		$this->crearVar(['historia','motivo','anestesia','marcapFab','marcapMod','marcapSer','marcapEri','marcapTec','auriculFab',
						 'auriculMod','auriculSer','auriculCnf','auriculTec','ventrDFac','ventrDMod','ventrDSer','ventrDCnf','ventrDTec',
						 'ventrIFac','ventrIMod','ventrISer','ventrICnf','ventrITec','modo','otros','complicac',
						 'codDx','dscDx','RegMedico','Especialidad', ], '');
		$this->crearVar(['fevi','umbralmEARU','umbralmEARB','umbralmEVDU','umbralmEVDB','umbralmEVIU','umbralmEVIB','umbralvEARU',
						 'umbralvEARB','umbralvEVDU','umbralvEVDB','umbralvEVIU','umbralvEVIB','resisteEARU','resisteEARB','resisteEVDU',
						 'resisteEVDB','resisteEVIU','resisteEVIB','ampondaEARU','ampondaEARB','ampondaEVDU','ampondaEVDB','ampondaEVIU',
						 'ampondaEVIB','slwrateEARU','slwrateEARB','slwrateEVDU','slwrateEVDB','slwrateEVIU','slwrateEVIB','modoFcMin',
						 'modoFcMax','modoAuAmp','modoAuAp','voltajeBat','modoVdAmp','modoVdAp','modoViAmp','modoViAp', ], 0);
		$this->crearVar(['marcapFec','auriculFec','ventrDFec','ventrIFec'], date('Y-m-d H:i:s'));



		foreach($laDatas as $laData) {

			switch ($laData['CONSEC']) {

		//	**************************
		//	**  DATOS DEL IMPLANTE  **
		//	**************************


			//****************
			//** MARCAPASOS **
			//****************

				// HISTORIA CLINICA
				case 1:
					$this->aVar['historia'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
					break;
				// MOTIVO DEL IMPLANTE 1
				case 2:
					$this->aVar['motivo'] = trim(mb_substr($laData['DESCRI'], 21, 49, 'UTF-8'));
					break;
				// MOTIVO DEL IMPLANTE 2
				case 3:
					$this->aVar['motivo'] .= rtrim($laData['DESCRI']);
					break;
				// FEVI
				case 4:
					$this->aVar['fevi'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					break;
				// ANESTESIA
				case 6:
					$this->aVar['anestesia'] = strtoupper(trim(substr($laData['DESCRI'], 36, 34)));
					break;
				// GENERADOR FABRICANTE
				CASE 7:
					$this->aVar['marcapFab'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR MODELO
				case 8:
					$this->aVar['marcapMod'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR SERIE
				case 9:
					$this->aVar['marcapSer'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// GENERADOR FECHA IMPLANTE
				case 10:
					$this->aVar['marcapFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
					break;
				// TECNICA SUBCUTANEA ****
				case 11:
					$this->aVar['marcapTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Subcutanea' : '';
					break;
				// TECNICA SUBMUSCULAR
				case 12:
					$this->aVar['marcapTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Submuscular' : $this->aVar['marcapTec'];
					break;
				// TECNICA CAMBIO
				case 13:
					$this->aVar['marcapTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Cambio' : $this->aVar['marcapTec'];
					break;

			//*************************
			//** ELECTRODO AURICULAR **
			//*************************

				// ELEC AURICULAR FABRICANTE
				case 14:
					$this->aVar['auriculFab'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR MODELO
				case 15:
					$this->aVar['auriculMod'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR SERIE
				case 16:
					$this->aVar['auriculSer'] = trim(substr($laData['DESCRI'], 36, 34));
					break;
				// ELEC AURICULAR FEC IMPLANTE
				case 17:
					$this->aVar['auriculFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 34))),'/');
					break;
				// TECNICA PUNCION SUBCLAVIA
				case 18:
					$this->aVar['auriculTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Puncion Subclavia' : '';
					break;
				// TECNICA DISECCION CEFALICA
				case 19:
					$this->aVar['auriculTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Diseccion Cefalica' : $this->aVar['auriculTec'];
					break;
				// TECNICA REUBICACION
				case 20:
					$this->aVar['auriculTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Reubicacion' : $this->aVar['auriculTec'];
					break;

			//**************************
			//** ELECTRODO VETRICULAR **
			//**************************

				// ELEC VENTRICU FABRICANTE
				case 21:
					$this->aVar['ventrDFac'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['ventrIFac'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU MODELO
				case 22:
					$this->aVar['ventrDMod'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['ventrIMod'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU SERIE
				case 23:
					$this->aVar['ventrDSer'] = trim(substr($laData['DESCRI'], 36, 16));
					$this->aVar['ventrISer'] = trim(substr($laData['DESCRI'], 53, 16));
					break;
				// ELEC VENTRICU FEC IMPLANTE
				case 24:
					$this->aVar['ventrDFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 36, 16))),'/');
					$this->aVar['ventrIFec'] = AplicacionFunciones::formatFechaHora('fecha',intval(trim(substr($laData['DESCRI'], 53, 34))),'/');
					break;
				// TECNICA PUNCION SUBCLAVIA
				case 25:
					$this->aVar['ventrDTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Punción Subclavia' : '';
					$this->aVar['ventrITec'] = substr($laData['DESCRI'], 32, 1)=='X' ? 'Punción Subclavia' : '';
					break;
				// TECNICA DISECCION CEFALICA
				case 26:
					$this->aVar['ventrDTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Disección Cefálica' : $this->aVar['ventrDTec'];
					$this->aVar['ventrITec'] = substr($laData['DESCRI'], 32, 1)=='X' ? 'Disección Cefálica' : $this->aVar['ventrITec'];
					break;
				// TECNICA REUBICACION
				case 27:
					$this->aVar['ventrDTec'] = substr($laData['DESCRI'], 30, 1)=='X' ? 'Reubicación' : $this->aVar['ventrDTec'];
					$this->aVar['ventrITec'] = substr($laData['DESCRI'], 32, 1)=='X' ? 'Reubicación' : $this->aVar['ventrITec'];
					break;


		//	*******************************
		//	**  PARAMETROS DEL IMPLANTE  **
		//	*******************************

				// UMBRAL (MA) ELEC AURIC UNIPOLAR
				case 28:
					$this->aVar['umbralmEARU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					break;
				// UMBRAL (MA) ELEC AURIC BIPOLAR
				case 29:
					$this->aVar['umbralmEARB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					break;
				// UMBRAL (MA) ELEC VENTR UNIPOLAR
				case 30:
					$this->aVar['umbralmEVDU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					$this->aVar['umbralmEVIU'] = floatval(substr($laData['DESCRI'], 42, 3) . '.' . substr($laData['DESCRI'], 45, 2));
					break;
				// UMBRAL (MA) ELEC VENTR BIPOLAR
				case 31:
					$this->aVar['umbralmEVDB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					$this->aVar['umbralmEVIB'] = floatval(substr($laData['DESCRI'], 42, 3) . '.' . substr($laData['DESCRI'], 45, 2));
					break;
				// UMBRAL (V) ELEC AURIC UNIPOLAR
				case 32:
					$this->aVar['umbralvEARU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					break;
				// UMBRAL (V) ELEC AURIC BIPOLAR
				case 33:
					$this->aVar['umbralvEARB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					break;
				// UMBRAL (V) ELEC VENTR UNIPOLAR
				case 34:
					$this->aVar['umbralvEVDU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					$this->aVar['umbralvEVIU'] = floatval(substr($laData['DESCRI'], 42, 3) . '.' . substr($laData['DESCRI'], 45, 2));
					break;
				// UMBRAL (V) ELEC VENTR BIPOLAR
				case 35:
					$this->aVar['umbralvEVDB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 2));
					$this->aVar['umbralvEVIB'] = floatval(substr($laData['DESCRI'], 42, 3) . '.' . substr($laData['DESCRI'], 45, 2));
					break;
				// RESISTENCIA ELEC AURIC UNIPOLAR
				case 36:
					$this->aVar['resisteEARU'] = floatval(substr($laData['DESCRI'], 36, 4) . '.' . substr($laData['DESCRI'], 40, 2));
					break;
				// RESISTENCIA ELEC AURIC BIPOLAR
				case 37:
					$this->aVar['resisteEARB'] = floatval(substr($laData['DESCRI'], 36, 4) . '.' . substr($laData['DESCRI'], 40, 2));
					break;
				// RESISTENCIA ELEC VENTR UNIPOLAR
				case 38:
					$this->aVar['resisteEVDU'] = floatval(substr($laData['DESCRI'], 36, 4) . '.' . substr($laData['DESCRI'], 40, 2));
					$this->aVar['resisteEVIU'] = floatval(substr($laData['DESCRI'], 43, 4) . '.' . substr($laData['DESCRI'], 47, 2));
					break;
				// RESISTENCIA ELEC VENTR BIPOLAR
				case 39:
					$this->aVar['resisteEVDB'] = floatval(substr($laData['DESCRI'], 36, 4) . '.' . substr($laData['DESCRI'], 40, 2));
					$this->aVar['resisteEVIB'] = floatval(substr($laData['DESCRI'], 43, 4) . '.' . substr($laData['DESCRI'], 47, 2));
					break;
				// AMP ONDA P O E ELEC AURIC UNIPOLAR
				case 40:
					$this->aVar['ampondaEARU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					break;
				// AMP ONDA P O E ELEC AURIC BIPOLAR
				case 41:
					$this->aVar['ampondaEARB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					break;
				// AMP ONDA P O E ELEC VENTR UNIPOLAR
				case 42:
					$this->aVar['ampondaEVDU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					$this->aVar['ampondaEVIU'] = floatval(substr($laData['DESCRI'], 43, 3) . '.' . substr($laData['DESCRI'], 46, 3));
					break;
				// AMP ONDA P O E ELEC VENTR BIPOLAR
				case 43:
					$this->aVar['ampondaEVDB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					$this->aVar['ampondaEVIB'] = floatval(substr($laData['DESCRI'], 43, 3) . '.' . substr($laData['DESCRI'], 46, 3));
					break;
				// SLEW RATE (V/S) ELEC AURIC UNIPOLAR
				case 44:
					$this->aVar['slwrateEARU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					break;
				// SLEW RATE (V/S) ELEC AURIC BIPOLAR
				case 45:
					$this->aVar['slwrateEARB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					break;
				// SLEW RATE (V/S) ELEC VENTR UNIPOLAR
				case 46:
					$this->aVar['slwrateEVDU'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					$this->aVar['slwrateEVIU'] = floatval(substr($laData['DESCRI'], 43, 3) . '.' . substr($laData['DESCRI'], 46, 3));
					break;
				// SLEW RATE (V/S) ELEC VENTR BIPOLAR
				case 47:
					$this->aVar['slwrateEVDB'] = floatval(substr($laData['DESCRI'], 36, 3) . '.' . substr($laData['DESCRI'], 39, 3));
					$this->aVar['slwrateEVIB'] = floatval(substr($laData['DESCRI'], 43, 3) . '.' . substr($laData['DESCRI'], 46, 3));
					break;
				// MODO
				case 48:
					$this->aVar['modo'] = trim(substr($laData['DESCRI'], 21, 49));
					break;
				// FC MINIMA
				case 49:
					$this->aVar['modoFcMin'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					break;
				// FC MAXIMA
				case 50:
					$this->aVar['modoFcMax'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					break;
				// AURICULAR AMP
				case 51:
					$this->aVar['modoAuAmp'] = floatval(substr($laData['DESCRI'], 21, 4) . '.' . substr($laData['DESCRI'], 25, 1));
					break;
				// AURICULAR AP
				case 52:
					$this->aVar['modoAuAp'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					break;
				// VENTRICULAR AMP
				case 53:
					$this->aVar['modoVdAmp'] = floatval(substr($laData['DESCRI'], 21, 4) . '.' . substr($laData['DESCRI'], 25, 1));
					$this->aVar['modoViAmp'] = floatval(substr($laData['DESCRI'], 27, 4) . '.' . substr($laData['DESCRI'], 31, 1));
					break;
				// VENTRICULAR AP
				case 54:
					$this->aVar['modoVdAp'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					$this->aVar['modoViAp'] = floatval(substr($laData['DESCRI'], 27, 3) . '.' . substr($laData['DESCRI'], 30, 2));
					break;


		//	*************
		//	**  OTROS  **
		//	*************

				// OTROS
				case 57:
					$this->aVar['otros'] = substr($laData['DESCRI'], 11, 59);
					break;
				case 58: case 59: case 60:
					$this->aVar['otros'] .= $laData['DESCRI'];
					break;
				// ELEC AURICULAR CONFIGURACION
				case 61:
					$this->aVar['auriculCnf'] = substr($laData['DESCRI'], 36, 34);
					break;
				// ELEC VENTRICU CONFIGURACION
				case 62:
					$this->aVar['ventrDCnf'] = substr($laData['DESCRI'], 36, 16);
					$this->aVar['ventrICnf'] = substr($laData['DESCRI'], 53, 16);
					break;
				// FCERI
				case 63:
					$this->aVar['marcapEri'] = substr($laData['DESCRI'], 36, 34);
					break;
				// VOLTAJE BATERIA
				case 64:
					$this->aVar['voltajeBat'] = floatval(substr($laData['DESCRI'], 21, 3) . '.' . substr($laData['DESCRI'], 24, 2));
					break;
			}
			// COMPLICACIONES
			if ($laData['CONSEC']>900)
				$this->aVar['complicac'] .= $laData['DESCRI'];
		}
		$this->aVar['otros'] = trim($this->aVar['otros']);
		$this->aVar['complicac'] = trim($this->aVar['complicac']);


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
					. (empty($this->aVar['dscDx'])? "": $lcSL . 'Enfermedad: ' . $this->aVar['dscDx'])
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
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['auriculFab'], 'Serie:',  $this->aVar['auriculSer']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['auriculFec'], 'Modelo:', $this->aVar['auriculMod']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['auriculCnf'], 'Técnica:',$this->aVar['auriculTec']] ],
					], ];
		}
		if (!empty($this->aVar['ventrDFac']) || !empty($this->aVar['ventrDMod']) || !empty($this->aVar['ventrDSer'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR DERECHO'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['ventrDFac'], 'Serie:',  $this->aVar['ventrDSer']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['ventrDFec'], 'Modelo:', $this->aVar['ventrDMod']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['ventrDCnf'], 'Técnica:',$this->aVar['ventrDTec']] ],
					], ];
		}
		if (!empty($this->aVar['ventrIFac']) || !empty($this->aVar['ventrIMod']) || !empty($this->aVar['ventrISer'])) {
			$laTr[] = ['titulo1', 'ELECTRODO VENTRICULAR IZQUIERDO'];
			$laTr[] = ['tablaSL', [], [
						['w'=>$laW, 'd'=>['Fabricante:',	$this->aVar['ventrIFac'], 'Serie:',  $this->aVar['ventrISer']] ],
						['w'=>$laW, 'd'=>['Fecha Implante:',$this->aVar['ventrIFec'], 'Modelo:', $this->aVar['ventrIMod']] ],
						['w'=>$laW, 'd'=>['Configuración:', $this->aVar['ventrICnf'], 'Técnica:',$this->aVar['ventrITec']] ],
					], ];
		}

		$laTr[] = ['titulo1', 'PARÁMETROS IMPLANTE'];
		$laWt1 = [40,50,50,50,]; $laWt2 = [25,25,25,25,25,25,];
		$laW = [40,25,25,25,25,25,25,];
		$laA = ['L','R','R','R','R','R','R',];
		$laTtl = ['Umbral (Ma)','Umbral (V)','Resistencia (OHMS)','Amp. Onda P o R','Slew Rate (V/S)',];
		$laTr[] = ['tabla', [
					['w'=>$laWt1,'a'=>'C','d'=>['','ELECTRODO AURICULAR','ELECTRODO VENT. DER.','ELECTRODO VENT. IZQ.',],'f'=>[2,0,0,0] ],
					['w'=>$laWt2,'a'=>'C','d'=>['UNIPOLAR','BIPOLAR','UNIPOLAR','BIPOLAR','UNIPOLAR','BIPOLAR',] ],
				], [
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[0],$this->fNum('umbralmEARU',2),$this->fNum('umbralmEARB',2),$this->fNum('umbralmEVDU',2),$this->fNum('umbralmEVDB',2),$this->fNum('umbralmEVIU',2),$this->fNum('umbralmEVIB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[1],$this->fNum('umbralvEARU',2),$this->fNum('umbralvEARB',2),$this->fNum('umbralvEVDU',2),$this->fNum('umbralvEVDB',2),$this->fNum('umbralvEVIU',2),$this->fNum('umbralvEVIB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[2],$this->fNum('resisteEARU',2),$this->fNum('resisteEARB',2),$this->fNum('resisteEVDU',2),$this->fNum('resisteEVDB',2),$this->fNum('resisteEVIU',2),$this->fNum('resisteEVIB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[3],$this->fNum('ampondaEARU',2),$this->fNum('ampondaEARB',2),$this->fNum('ampondaEVDU',2),$this->fNum('ampondaEVDB',2),$this->fNum('ampondaEVIU',2),$this->fNum('ampondaEVIB',2)] ],
					['w'=>$laW,'a'=>$laA,'d'=>[$laTtl[4],$this->fNum('slwrateEARU',2),$this->fNum('slwrateEARB',2),$this->fNum('slwrateEVDU',2),$this->fNum('slwrateEVDB',2),$this->fNum('slwrateEVIU',2),$this->fNum('slwrateEVIB',2)] ],
				], ];

		$laTr[] = ['titulo1', 'PROGRAMACIÓN INICIAL'];
		$laWt1 = [30,40,40,40,40,]; $laWt2 = [20,20,20,20,20,20,20,20,];
		$laW = [30,20,20,20,20,20,20,20,20,];
		$laA = ['L','C','C','C','C','C','C','C','C',];
		$laTr[] = ['tabla', [
					['w'=>$laWt1,'a'=>'C','d'=>['MODO','FC','AURICULAR','VENTR. DER.','VENTR. IZQ.',],'f'=>[2,0,0,0,0] ],
					['w'=>$laWt2,'a'=>'C','d'=>['Mínima','Máxima','Amp','AP','Amp','AP','Amp','AP',] ],
				], [
					['w'=>$laW,'a'=>$laA,'d'=>[$this->aVar['modo'],
						$this->fNum('modoFcMin',2),$this->fNum('modoFcMax',2),
						$this->fNum('modoAuAmp',2),$this->fNum('modoAuAp',2),
						$this->fNum('modoVdAmp',2),$this->fNum('modoVdAp',2),
						$this->fNum('modoViAmp',2),$this->fNum('modoViAp',2)] ],
				], ];
		if (!empty($this->aVar['voltajeBat']))
			$laTr[] = ['texto9', 'Voltaje Batería:  ' . $this->fNum('voltajeBat',2)];

		if (!empty($this->aVar['complicac'])) {
			$laTr[] = ['titulo1', 'COMPLICACIONES'];
			$laTr[] = ['texto9', $this->aVar['complicac']];
		}

		if (!empty($this->aVar['otros'])) {
			$laTr[] = ['titulo1', 'OTROS'];
			$laTr[] = ['texto9', $this->aVar['otros']];
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
