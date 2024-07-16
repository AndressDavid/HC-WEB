<?php
namespace NUCLEO;

require_once ('class.AplicacionFunciones.php');
require_once ('class.Especialidad.php');
require_once ('class.Ocupacion.php');
use NUCLEO\Especialidad;
use NUCLEO\Ocupacion;


class Doc_HistoriaNutricion
{
	protected $oDb;
	protected $aDoc = [];
	protected $aReporte = [
					'cTitulo' => 'HISTORIA NUTRICIONAL',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>true,'forma'=>'NUT001'],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';
	protected $aParam=[];
	protected $aHinAnt=[];
	protected $lHayIngNutric = false;
	protected $lHayAntNutric = false;
	protected $lHayOtrosAnt  = false;
	protected $oMedico = null;

	protected $aDxHisNut = [];
	protected $aDatosBioQ = [];
	protected $aFDRNutrientes = [];
	protected $aFDRComidas = [];



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
		$this->consultarParam();
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*
	 *	Consulta los datos del documento desde la BD en el array $aDocumento
	 */
	private function consultarDatos($taData)
	{
		$laDoc = $this->datosBlanco();

		// CurNut001
		$laHisNut = $this->oDb
			->from('INFNUTL01')
			->where([
				'INGNUT'=>$taData['nIngreso'],
				'CCINUT'=>$taData['nConsecCita'],
				'CUPNUT'=>$taData['cCUP'],
			])
			->orderBy('INDNUT, IN2NUT, IN3NUT, CLINUT')
			->getAll('array');
		$lnNumHisNut = count($laHisNut);

		// curIngXPacNut
		$laIngXPacNut = $this->oDb
			->select('N.*')
			->from('INFNUTL01 AS N')
			->innerJoin('RIAING I','N.INGNUT=I.NIGING',null)
			->where([
				'I.TIDING'=>$taData['cTipDocPac'],
				'I.NIDING'=>$taData['nNumDocPac'],
			])
			->orderBy('INGNUT DESC, CCINUT DESC, INDNUT, IN2NUT, IN3NUT, CLINUT')
			->getAll('array');
		$lnNumIngXPacNut = count($laIngXPacNut);

		// hiscliH017
		$laAntecOtr = $this->oDb
			->select('H.*')
			->from('HISCLI H')
			->innerJoin('RIAING I','H.INGHCL=I.NIGING',null)
			->where([
				'I.TIDING'=>$taData['cTipDocPac'],
				'I.NIDING'=>$taData['nNumDocPac'],
				'H.INDHCL'=>10,
				'H.SUBHCL'=>15,
			])
			->orderBy('INGHCL DESC')
			->getAll('array');


		// Buscar Antecedentes para DATO ACTUAL (Ingreso ++ Cons_Cita)
		$laIngXPacNutANT = [];
		foreach($laHisNut as $laHN){
			if($laHN['INDNUT']==23 && !empty($laHN['DESNUT'])){
				$laIngXPacNutANT[]=$laHN;
			}
		}
		$lnNumIngXPacNutANT = count($laIngXPacNutANT);


		if($lnNumIngXPacNut>0){

			if($lnNumIngXPacNutANT>0){
				$this->lHayIngNutric = true;
				$this->lHayAntNutric = true;
				$this->lHayOtrosAnt  = false;

			} else {

				$lnNumIngNut = $laIngXPacNut[0]['INGNUT'];
				$lnNumCnsCit = $laIngXPacNut[0]['CCINUT'];

				$laIngXPacNut111 = [];
				$laIngXPacNut111Ant = [];
				foreach($laIngXPacNut as $laIpn){
					// Registro + reciente en InfNut (Otros_Registros)
					if($laIpn['INGNUT']==$lnNumIngNut && $laIpn['CCINUT']==$lnNumCnsCit){
						$laIngXPacNut111[]=$laIpn;
					}
					// Selecciona Antecedentes
					if($laIpn['INDNUT']==23 && !empty($laIpn['DESNUT'])){
						$laIngXPacNut111Ant[]=$laIpn;
					}
				}

				$lnNumIngNut = $laIngXPacNut111Ant[0]['INGNUT'];
				$lnNumCnsCit = $laIngXPacNut111Ant[0]['CCINUT'];

				$laIngXPacNutANT = [];
				foreach($laIngXPacNut111Ant as $laIpn){
					// Registro + reciente (DE ANTECEDENTES) en InfNut (Otros_Registros)
					if($laIpn['INGNUT']==$lnNumIngNut && $laIpn['CCINUT']==$lnNumCnsCit){
						$laIngXPacNutANT[]=$laIpn;
					}
				}

				if(count($laIngXPacNutANT)>0){
					$this->lHayAntNutric = true;
					$this->lHayIngNutric = true;
					$this->lHayOtrosAnt  = false;
				} else {
					$this->lHayAntNutric = false;
					$this->lHayOtrosAnt  = true;
				}
			}

		} else {
			$this->lHayIngNutric = false;
			$this->lHayAntNutric = false;
			$this->lHayOtrosAnt  = true;
		}



		// ************************************************************************************** //

		//	Variables Datos Generales
		$lcTexAnte=$lcCodAnte=$lcDesDx=$lcCodDx='';

		//	Variables Evaluacion Nutricional ==>> DATOS BIOQUIMICOS
		$lnFecDBioq=$lnIndice1=$lnIndice2=$lnIndice3=0;
		$laAntNut = [];

		if($this->lHayAntNutric){
			$this->lHayOtrosAnt = false;

			$lnIndice2 = $laIngXPacNutANT[0]['IN2NUT']??0;
			$lcCodAnte = trim($lnIndice2);
			foreach($laIngXPacNutANT as $laIpn){
				if($lnIndice2==$laIpn['IN2NUT']){
					$lcTexAnte .= $laIpn['DESNUT'];
				} else {
					$laAntNut[] = [0, $lcCodAnte, $lcTexAnte];
					$lcTexAnte = $laIpn['DESNUT'];
					$lnIndice2 = $laIpn['IN2NUT'];
					$lcCodAnte = trim($lnIndice2);
				}
			}
			if(count($laIngXPacNutANT)>0){
				$laAntNut[] = [0, $lcCodAnte, $lcTexAnte];
			}
		}

		// Buscar Antecedentes en ANTPACL01 (Antecedentes X Paciente)
		if(count($this->aHinAnt)==0 && !$this->lHayAntNutric) $this->antxpa();
		// Registros en Hiscli
		if(count($this->aHinAnt)==0 && !$this->lHayAntNutric && $this->lHayOtrosAnt) $this->anthis();



		// ************************************************************************************** //


		//	DATOS NUTRICIÓN
		if(count($laHisNut)>0){
			
			$laDoc['cMedicoOrdena']=$this->MedicoOrdena($taData);
			$lnNumDatBq = 0;

			foreach($laHisNut as $laHN){
				$laHN = array_map('trim',$laHN);
				switch(true){

					// Datos Generales
					case ($laHN['INDNUT']==10 && $laHN['IN2NUT']==1):
						$lcEsp = trim(substr($laHN['DESNUT'],0,6),' ');
						$lcOcu = trim(substr($laHN['DESNUT'],6,6),' ');
						$laDoc['cCodEspecialidad'] = new Especialidad($lcEsp);
						$laDoc['cCodOcupacion'] = new Ocupacion($lcOcu);
						break;

					// Datos Generales (Dx x Registro)
					case ($laHN['INDNUT']==10 && $laHN['IN2NUT']>1):
						$this->aDxHisNut[] = [
							'coddia'=>trim(substr($laHN['DESNUT'],0,6),' '),
							'desdia'=>trim(substr($laHN['DESNUT'],6,106),' '),
						];
						break;

					// D_Antropometricos
					case ($laHN['INDNUT']==21 && $laHN['IN2NUT']==1):
						$laDoc['aDatAntr']['nPeso']		 = round(substr($laHN['DESNUT'], 0,6), 2);
						$laDoc['aDatAntr']['nTalla']	 = intval(substr($laHN['DESNUT'], 6,6));
						$laDoc['aDatAntr']['nIMC']		 = round(substr($laHN['DESNUT'],12,6), 2);
						$laDoc['aDatAntr']['nPesoIdeal'] = round(substr($laHN['DESNUT'],18,6), 2);
						$laDoc['aDatAntr']['nPesoUsual'] = round(substr($laHN['DESNUT'],24,6), 2);
						$laDoc['aDatAntr']['nCambioPeso']= round(substr($laHN['DESNUT'],30,6), 2);
						$laDoc['aDatAntr']['nCintura']	 = round(substr($laHN['DESNUT'],36,6), 2);
						$laDoc['aDatAntr']['nCirfCarpo'] = round(substr($laHN['DESNUT'],42,6), 1);
						break;

					// D_Varios (1,3,4,5)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==1):
						$laDoc['aDatVar']['cHabIntes']		= trim(substr($laHN['DESNUT'], 0,6),' ');
						$laDoc['aDatVar']['cTipEjerc']		= trim(substr($laHN['DESNUT'], 6,6),' ');
						$laDoc['aDatVar']['cFreEjerc']		= trim(substr($laHN['DESNUT'],12,6),' ');
						$laDoc['aDatAntr']['cDXNutricional']= trim(substr($laHN['DESNUT'],18,50),' ');
						break;

					// D_Varios (EditBox Alim_Rechazados)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==2):
						$laDoc['aDatVar']['cAliRecha'] .= trim($laHN['DESNUT']);
						break;

					// D_Varios (Alcohol)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==3):
						if(strlen(trim($laHN['DESNUT']))>3) {
							$laDoc['aDatVar']['cSNAlcohol']	= trim(substr($laHN['DESNUT'],0,2),' ');
							$laDoc['aDatVar']['cAlcohol']	= trim(substr($laHN['DESNUT'],2,102),' ');
						} else {
							$laDoc['aDatVar']['cSNAlcohol']	= 'No';
							$laDoc['aDatVar']['cAlcohol']	= '';
						}
						break;

					// D_Varios (TINTO)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==4):
						if(strlen(trim($laHN['DESNUT']))>3) {
							$laDoc['aDatVar']['cSNTinto']	= trim(substr($laHN['DESNUT'],0,2),' ');
							$laDoc['aDatVar']['cTinto']		= trim(substr($laHN['DESNUT'],2,102),' ');
						} else {
							$laDoc['aDatVar']['cSNTinto']	= 'No';
							$laDoc['aDatVar']['cTinto']		= '';
						}
						break;

					// D_Varios (GASEOSA)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==5):
						if(strlen(trim($laHN['DESNUT']))>3) {
							$laDoc['aDatVar']['cSNGaseosa']	= trim(substr($laHN['DESNUT'],0,2),' ');
							$laDoc['aDatVar']['cGaseosa']	= trim(substr($laHN['DESNUT'],2,102),' ');
						} else {
							$laDoc['aDatVar']['cSNGaseosa']	= 'No';
							$laDoc['aDatVar']['cGaseosa']	= '';
						}
						break;

					// D_Varios (DULCES)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==6):
						if(strlen(trim($laHN['DESNUT']))>3) {
							$laDoc['aDatVar']['cSNDulces']	= trim(substr($laHN['DESNUT'],0,2),' ');
							$laDoc['aDatVar']['cDulces']	= trim(substr($laHN['DESNUT'],2,102),' ');
						} else {
							$laDoc['aDatVar']['cSNDulces']	= 'No';
							$laDoc['aDatVar']['cDulces']	= '';
						}
						break;

					// D_Varios (CIGARRILLOS)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==7):
						if(strlen(trim($laHN['DESNUT']))>3) {
							$laDoc['aDatVar']['cSNCigarrillo']	= trim(substr($laHN['DESNUT'],0,2),' ');
							$laDoc['aDatVar']['cCigarrillo']	= trim(substr($laHN['DESNUT'],2,102),' ');
						} else {                
							$laDoc['aDatVar']['cSNCigarrillo']	= 'No';
							$laDoc['aDatVar']['cCigarrillo']	= '';
						}
						break;

					// D_Varios (OTROS)
					case ($laHN['INDNUT']==22 && $laHN['IN2NUT']==8):
						$laDoc['aDatVar']['cOtros'] = strlen(trim($laHN['DESNUT']))>1 ? trim($laHN['DESNUT'],' ') : '';
						break;

					// Antecedentes
					case ($laHN['INDNUT']==23): // && $laHN['IN2NUT']==1):
						// Los datos se cargan al Inicio_del_Init  ...
						if(isset($laDoc['aAntecedentes'][$laHN['IN2NUT']])){
							$laDoc['aAntecedentes'][$laHN['IN2NUT']].=$laHN['DESNUT'];
						} else {
							$laDoc['aAntecedentes'][$laHN['IN2NUT']]=$laHN['DESNUT'];
/*
							$laDoc['aAntecedentes'][$laHN['IN2NUT']] = [
								'codigo'=>$laHN['IN2NUT'],
								'descrp'=>$laHN['DESNUT'],
							];
*/						}
						break;

					// D_BioQuimicos (1,2,3,4,5,6,7)
					case ($laHN['INDNUT']==24):
						if ($laHN['IN3NUT']==1){
							$lnNumDatBq++;
							$lcIndice1 = $laHN['INDNUT'];
							$lcIndice2 = $laHN['IN2NUT'];
							$lcIndice3 = $laHN['IN3NUT'];

							$lnFecDBioq		= intval(substr($laHN['DESNUT'],0,10));
							$ldDatBq_Fecha	= AplicacionFunciones::formatFechaHora('fecha',$lnFecDBioq);
							$lnDatBq_Col	= intval(trim(substr($laHN['DESNUT'],10,10)));
							$lnDatBq_HDL	= intval(trim(substr($laHN['DESNUT'],20,10)));
							$lnDatBq_LDL	= intval(trim(substr($laHN['DESNUT'],30,10)));
							$lnDatBq_TGC	= intval(trim(substr($laHN['DESNUT'],40,10)));
							$lnDatBq_Gli	= intval(trim(substr($laHN['DESNUT'],50,10)));
							$lnDatBq_AcUri	= intval(trim(substr($laHN['DESNUT'],60,10)));

							$this->aDatosBioQ[$lnNumDatBq] = [
								'SELECC'=>0,
								'FECHAN'=>$lnFecDBioq,
								'FECHA'=>$ldDatBq_Fecha,
								'COLESTER'=>$lnDatBq_Col,
								'HDL'=>$lnDatBq_HDL,
								'LDL'=>$lnDatBq_LDL,
								'TGC'=>$lnDatBq_TGC,
								'GLICEMIA'=>$lnDatBq_Gli,
								'ACIDOURI'=>$lnDatBq_AcUri,
								'OTROS'=>'',
								'INDIC1'=>$lcIndice1,
								'INDIC2'=>$lcIndice2,
								'INDIC3'=>$lcIndice3,
							];
						} else {
							$this->aDatosBioQ[$lnNumDatBq]['OTROS'].=$laHN['DESNUT'];
						}
						break;

					// D_Anamnesis (Desayuno)
					case ($laHN['INDNUT']==25):
						switch ($laHN['IN2NUT']){
							case 1: $lcAnmAlm='cDesayuno'; break;
							case 2: $lcAnmAlm='cNueves'; break;
							case 3: $lcAnmAlm='cAlmuerzo'; break;
							case 4: $lcAnmAlm='cOnces'; break;
							case 5: $lcAnmAlm='cComida'; break;
							case 6: $lcAnmAlm='cRefrigerio'; break;
						}
						$laDoc['aAnmAlm'][$lcAnmAlm] .= trim($laHN['DESNUT'],' ');
						break;

					// Form_Dietaria
					case ($laHN['INDNUT']==30 && $laHN['IN2NUT']==1):
						$laDoc['aForDiet']['nGrmProt']	= number_format(trim(substr($laHN['DESNUT'], 0,10)),2);
						$laDoc['aForDiet']['nGrmGras']	= number_format(trim(substr($laHN['DESNUT'],10,10)),2);
						$laDoc['aForDiet']['nGrmCarb']	= number_format(trim(substr($laHN['DESNUT'],20,10)),2);
						$laDoc['aForDiet']['nKlcProt']	= number_format(trim(substr($laHN['DESNUT'],30,10)),2);
						$laDoc['aForDiet']['nKlcGras']	= number_format(trim(substr($laHN['DESNUT'],40,10)),2);
						$laDoc['aForDiet']['nKlcCarb']	= number_format(trim(substr($laHN['DESNUT'],50,10)),2);
						$laDoc['aForDiet']['nPorProt']	= number_format(trim(substr($laHN['DESNUT'],60,10)),2);
						$laDoc['aForDiet']['nPorGras']	= number_format(trim(substr($laHN['DESNUT'],70,10)),2);
						$laDoc['aForDiet']['nPorCarb']	= number_format(trim(substr($laHN['DESNUT'],80,10)),2);
						$laDoc['aForDiet']['nTKlc']		= number_format(trim(substr($laHN['DESNUT'],90,10)),2);

						// No se estaba guardando el total de kilocalorias, así que se suman los parciales si viene vacío
						$laDoc['aForDiet']['nTKlc'] = empty($laDoc['aForDiet']['nTKlc']) ?
							$laDoc['aForDiet']['nKlcProt'] + $laDoc['aForDiet']['nKlcGras'] + $laDoc['aForDiet']['nKlcCarb'] :
							$laDoc['aForDiet']['nTKlc'];
						break;

					// Form_Dietaria:  NUTRIENTES del 1 .. 27
					case ($laHN['INDNUT']==30 && $laHN['IN2NUT']==2):
						$laDoc['aForDiet']['cDatNutri'] .= $laHN['DESNUT'];
						break;

					// Form_Dietaria:  COMIDAS del 1 .. 9
					case ($laHN['INDNUT']==30 && $laHN['IN2NUT']==3):
						$laDoc['aForDiet']['cDatComid'] .= $laHN['DESNUT'];
						break;
				}
			}

			// FDR - NUTRIENTES (30,2)		==>> Cargar los Datos en el Cursor de la Clase.
			if(!empty($laDoc['aForDiet']['cDatNutri'])){
				$laSegmento = explode('~',$laDoc['aForDiet']['cDatNutri']);
				if(count($laSegmento) > 0){
					foreach($laSegmento as $lcSegmento){
						$lcClave = trim(substr($lcSegmento, 0, 4)).'-'.trim(substr($lcSegmento, 4, 4));
						if(isset($this->aFDRNutrientes[$lcClave])){
							$lnNumInte = floatval(substr($lcSegmento, 8, 6));
							$this->aFDRNutrientes[$lcClave]['NUMINTER']=$lnNumInte;
						}
					}
				}
			}

			// FDR - COMIDAS (30,3)		==>> Cargar los Datos en el Cursor de la Clase.
			if(!empty($laDoc['aForDiet']['cDatComid'])){
				$laSegmento = explode('~',$laDoc['aForDiet']['cDatComid']);
				if(count($laSegmento) > 0){
					foreach($laSegmento as $lcSegmento){
						$lcIndicea = trim(substr($lcSegmento, 0, 4));
						$lnNumInte = floatval(substr($lcSegmento,  4, 6));
						$lnDesayun = floatval(substr($lcSegmento, 10, 6));
						$lnNueves  = floatval(substr($lcSegmento, 16, 6));
						$lnAlmuerz = floatval(substr($lcSegmento, 22, 6));
						$lnOnces   = floatval(substr($lcSegmento, 28, 6));
						$lnComida  = floatval(substr($lcSegmento, 34, 6));
						$lnRefrige = floatval(substr($lcSegmento, 40, 6));

						foreach($this->aFDRComidas as $lnClave=>$laFDRN){
							if($laFDRN['nIndice2']==$lcIndicea){
								$this->aFDRComidas[$lnClave]['nNumInter']=$lnNumInte;
								$this->aFDRComidas[$lnClave]['nDesayuno']=$lnDesayun;
								$this->aFDRComidas[$lnClave]['nNueves']=$lnNueves;
								$this->aFDRComidas[$lnClave]['nAlmuerzo']=$lnAlmuerz;
								$this->aFDRComidas[$lnClave]['nOnces']=$lnOnces;
								$this->aFDRComidas[$lnClave]['nComida']=$lnComida;
								$this->aFDRComidas[$lnClave]['nRefrigerio']=$lnRefrige;
								break;
							}
						}
					}
				}
			}
			$this->CalculaNutrient();
			$this->CalcTotalNutrie();
		}
		$this->aDoc = $laDoc;
	}


	/*
	 *	Prepara array $aReporte con los datos para imprimir
	 */
	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$laTr = [];

		$this->aReporte['cTitulo'] .= PHP_EOL .'DEPARTAMENTO DE NUTRICIÓN CLÍNICA';

		if(!empty($this->aDoc['cMedicoOrdena'])){
			$this->aReporte['cTxtAntesDeCup'] = 'Solicita  : '.$this->aDoc['cMedicoOrdena'];
		}


		// Cuerpo


		// DATOS GENERALES
		$laTr['aCuerpo'][] = ['titulo1', '1. DATOS GENERALES'];

		$laTr['aCuerpo'][] = ['txthtml9', '<b>Remitido por:</b> '.$this->aDoc['cCodEspecialidad']->cNombre];
		$laTr['aCuerpo'][] = ['txthtml9', '<b>Ocupación:</b> '.$this->aDoc['cCodOcupacion']->cNombre];
		if(count($this->aDxHisNut)>0){
			$laTr['aCuerpo'][] = ['txthtml9', '<b>Diagnóstico Médico:</b>'];
			$lcEsp=str_repeat(' ',12);
			$laTr['aCuerpo'][] = ['texto9', $lcEsp.'Código    Diagnóstico'];
			foreach($this->aDxHisNut as $aDx){
				$laTr['aCuerpo'][]=['texto9', $lcEsp.str_pad($aDx['coddia'],10).$aDx['desdia']];
			}
		}


		// EVALUACIÓN NUTRICIONAL
		$laTr['aCuerpo'][] = ['titulo1', '2. EVALUACIÓN NUTRICIONAL'];

		// EVALUACIÓN NUTRICIONAL - Datos Antropométricos
		$laTr['aCuerpo'][] = ['titulo2', '2.1. Datos Antropométricos'];
		$lcDet = 'Peso:        '.str_pad($this->aDoc['aDatAntr']['nPeso'].' Kg', 12)
				.'Talla:       '.str_pad($this->aDoc['aDatAntr']['nTalla'].' cm', 12)
				.'IMC:         '.str_pad($this->aDoc['aDatAntr']['nIMC'], 12)
				.( empty($this->aDoc['aDatAntr']['nCirfCarpo'])? '': 'Crcnf Carpo: '.str_pad($this->aDoc['aDatAntr']['nCirfCarpo'].' cm', 12) )
				.PHP_EOL
				.'Peso Ideal:  '.str_pad($this->aDoc['aDatAntr']['nPesoIdeal'].' Kg', 12)
				.'Peso Usual:  '.str_pad($this->aDoc['aDatAntr']['nPesoUsual'].' Kg', 12)
				.'Cambio Peso: '.str_pad($this->aDoc['aDatAntr']['nCambioPeso'].' %', 12)
				.'Cintura:     '.str_pad($this->aDoc['aDatAntr']['nCintura'].' cm', 12);
		$laTr['aCuerpo'][]=['texto9', $lcDet];

		// EVALUACIÓN NUTRICIONAL - Datos Varios
		$laTmp = $this->aDoc['aDatVar'];
		$laTr['aCuerpo'][] = ['titulo2', '2.2. Datos Varios'];
		if(!empty($laTmp['cHabIntes'])){
			$lcDet = '<b>Hábito Intestinal:</b> '.$this->aParam['HABINTES'][$laTmp['cHabIntes']]['DE1TMA'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}
		if(!empty($laTmp['cAliRecha'])){
			$lcDet = '<b>Alimentos Rechazados:</b> '.$laTmp['cAliRecha'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}
		if(!empty($laTmp['cTipEjerc'])){
			$lcDet = '<b>Tipo de Ejercicio:</b> '.$this->aParam['TIPEJERC'][$laTmp['cTipEjerc']]['DE1TMA'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}
		if(!empty($laTmp['cFreEjerc'])){
			$lcDet = '<b>Frecuencia Ejercicio:</b> '.$this->aParam['FRECEJER'][$laTmp['cFreEjerc']]['DE1TMA'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}
		if(!empty($this->aDoc['aDatAntr']['cDXNutricional'])){
			$lcDet = '<b>Dx Nutricional:</b> '.$this->aDoc['aDatAntr']['cDXNutricional'];
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}
		$laTr['aCuerpo'][] = ['txthtml9', '<b>Hábitos:<b>'];
		$laTr['aCuerpo'][] = ['tabla', [
				['w'=>[25,70,25,70], 'd'=>['HÁBITOS','FRECUENCIA','HÁBITOS','FRECUENCIA',]],
			], [
				['w'=>[20,5,70,20,5,70], 'd'=>['Alcohol', $laTmp['cSNAlcohol'], $laTmp['cAlcohol'], 'Dulces', $laTmp['cSNDulces'], $laTmp['cDulces'], ]],
				['w'=>[20,5,70,20,5,70], 'd'=>['Tinto', $laTmp['cSNTinto'], $laTmp['cTinto'], 'Cigarrillo', $laTmp['cSNCigarrillo'], $laTmp['cCigarrillo'], ]],
				['w'=>[20,5,70,95], 'd'=>['Gaseosa', $laTmp['cSNGaseosa'], $laTmp['cGaseosa'], 'Otros: '.$laTmp['cOtros'], ]],
			], ];
		if(count($this->aDoc['aAntecedentes'])>0){
			$lcDet='<b>Antecedentes:</b>';
			foreach($this->aParam['ANTECTTL'] as $lnClave=>$laAntec){
				if(isset($this->aDoc['aAntecedentes'][$lnClave])){
					$lcDet.='<br><b><i>'.$laAntec['TITULO'].'</i></b>: '.$this->aDoc['aAntecedentes'][$lnClave];
				}
			}
			$laTr['aCuerpo'][] = ['txthtml9', $lcDet];
		}

		// EVALUACIÓN NUTRICIONAL - Datos Bioquímicos
		if(count($this->aDatosBioQ)>0){
			$laAnchoCol = [20,12,12,12,12,12,12,98];
			$laTr['aCuerpo'][] = ['titulo2', '2.3. Datos Bioquímicos'];
			$lnNumCl=count($laTr['aCuerpo']);
			$laTr['aCuerpo'][$lnNumCl] = ['tabla', [
					['w'=>$laAnchoCol, 'a'=>'C', 'd'=>['Fecha','Colest','HDL','LDL','TGC','Glicem','TSH','Observaciones',]],
				], [ ], ];
			foreach($this->aDatosBioQ as $DatBq){
				$laTr['aCuerpo'][$lnNumCl][2][]=[
					'w'=>$laAnchoCol, 'a'=>['L','C','C','C','C','C','C','L'],
					'd'=>[
						$DatBq['FECHA'],
						$DatBq['COLESTER'],
						$DatBq['HDL'],
						$DatBq['LDL'],
						$DatBq['TGC'],
						$DatBq['GLICEMIA'],
						$DatBq['ACIDOURI'],
						$DatBq['OTROS'],
					],
				];
			}
		}

		// EVALUACIÓN NUTRICIONAL - Anamnesis
		$laTr['aCuerpo'][] = ['titulo2', '2.4. Anamnesis'];
		foreach($this->aDoc['aAnmAlm'] as $lcClave=>$lcAnamnesis){
			if(!empty($lcAnamnesis)){
				$laTr['aCuerpo'][] = ['titulo4', substr($lcClave,1).':'];
				$laTr['aCuerpo'][] = ['texto9', $lcAnamnesis];
			}
		}


		// FORMULA DIETARIA RECOMENDADA
		$laTr['aCuerpo'][] = ['titulo1', '3. FORMULA DIETARIA RECOMENDADA'];
		$laTmp = $this->aDoc['aForDiet'];
		$lnTot = $laTmp['nPorProt']+$laTmp['nPorGras']+$laTmp['nPorCarb'];
		$laTr['aCuerpo'][] = ['tabla', [
				['w'=>[30,30,30,30],'d'=>['NUTRIENTE','Gramos','Kilocalorías','%'],'a'=>'C']
			], [ 
				['w'=>[30,30,30,30],'d'=>['Proteinas',		$laTmp['nGrmProt'],$laTmp['nKlcProt'],$laTmp['nPorProt']],'a'=>['L','C','C','C']],
				['w'=>[30,30,30,30],'d'=>['Grasas',			$laTmp['nGrmGras'],$laTmp['nKlcGras'],$laTmp['nPorGras']],'a'=>['L','C','C','C']],
				['w'=>[30,30,30,30],'d'=>['Carbohidratos',	$laTmp['nGrmCarb'],$laTmp['nKlcCarb'],$laTmp['nPorCarb']],'a'=>['L','C','C','C']],
				['w'=>[60,30,30],	'd'=>['Total Kilocalorías',$laTmp['nTKlc'],$lnTot],'a'=>'C'],
			], ['fs'=>8,'l'=>40] ];

		$laAnchoCol = [40,11,11,11,11,11,11,11,11,11,11,11,11,11,11];
		$lnNumCl=count($laTr['aCuerpo']);
		$laTr['aCuerpo'][$lnNumCl] = ['tabla', [
				[	'w'=>$laAnchoCol,
					'd'=>['Grupo','Inter','Energ','CHO','Prot','Grasa','Sat','Mono','Poli','Col','NA','K','Ca','P','H2O'],'a'=>'C',]
			], [ ], ['fs'=>6.5] ];
		foreach($this->aFDRNutrientes as $aFDRN){
			$laTr['aCuerpo'][$lnNumCl][2][]=[
				'w'=>$laAnchoCol, 'a'=>['L','C','C','C','C','C','C','C','C','C','C','C','C','C','C'],
				'd'=>[
					$aFDRN['GRUPO'],
					$aFDRN['NUMINTER']>0? number_format($aFDRN['NUMINTER'],1) : '',
					$aFDRN['ENERGIA']>0 ? number_format($aFDRN['ENERGIA'],1) : '',
					$aFDRN['CHO']>0		? number_format($aFDRN['CHO'],1) : '',
					$aFDRN['PROT']>0	? number_format($aFDRN['PROT'],1) : '',
					$aFDRN['GRASA']>0	? number_format($aFDRN['GRASA'],1) : '',
					$aFDRN['SAT']>0		? number_format($aFDRN['SAT'],1) : '',
					$aFDRN['MONO']>0	? number_format($aFDRN['MONO'],1) : '',
					$aFDRN['POLI']>0	? number_format($aFDRN['POLI'],1) : '',
					$aFDRN['COL']>0		? number_format($aFDRN['COL'],1) : '',
					$aFDRN['NA']>0		? number_format($aFDRN['NA'],1) : '',
					$aFDRN['K']>0		? number_format($aFDRN['K'],1) : '',
					$aFDRN['CA']>0		? number_format($aFDRN['CA'],1) : '',
					$aFDRN['P']>0		? number_format($aFDRN['P'],1) : '',
					$aFDRN['H2O']>0		? number_format($aFDRN['H2O'],1) : '',
				],
			];
		}
		$laAnchoCol = [30,17,17,17,17,17,17,17];
		$lnNumCl=count($laTr['aCuerpo']);
		$laTr['aCuerpo'][$lnNumCl] = ['tabla', [
				[	'w'=>$laAnchoCol,
					'd'=>['Grupo','Inter','Desayuno','Nueves','Almuerzo','Onces','Comida','Refrigerio'],'a'=>'C',]
			], [ ], ['fs'=>8,'l'=>25] ];
		foreach($this->aFDRComidas as $aFDRN){
			$laTr['aCuerpo'][$lnNumCl][2][]=[
				'w'=>$laAnchoCol, 'a'=>['L','C','C','C','C','C','C','C'],
				'd'=>[
					$aFDRN['cGrupo'],
					$aFDRN['nNumInter']>0	? number_format($aFDRN['nNumInter'],1) : '',
					$aFDRN['nDesayuno']>0	? number_format($aFDRN['nDesayuno'],1) : '',
					$aFDRN['nNueves']>0		? number_format($aFDRN['nNueves'],1) : '',
					$aFDRN['nAlmuerzo']>0	? number_format($aFDRN['nAlmuerzo'],1) : '',
					$aFDRN['nOnces']>0		? number_format($aFDRN['nOnces'],1) : '',
					$aFDRN['nComida']>0		? number_format($aFDRN['nComida'],1) : '',
					$aFDRN['nRefrigerio']>0	? number_format($aFDRN['nRefrigerio'],1) : '',
				],
			];
		}

		// Firma
		$laTr['aCuerpo'][] = ['firmas', [['registro'=>$taData['cRegMedico'], 'especialidad'=>false]]];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}


	/*
	 *	Consultar Antecedentes por Paciente
	 */
	private function antxpa()
	{
		return [];
	}


	/*
	 *	Consultar Antecedentes en H.C.
	 */
	private function anthis()
	{
		return [];
	}


	/*
	 *	Obtener el médico que ordenó
	 */
	private function MedicoOrdena($taData)
	{
		$lcMedOrd='';
		$laMedOrd = $this->oDb
			->select('TRIM(NOMMED)||\' \'||TRIM(NNOMED) AS MED_ORD')
			->from('RIARGMN M')
			->innerJoin('RIAORD O', 'O.RMEORD=M.REGMED')
			->where([
				'O.NINORD'=>$taData['nIngreso'],
				'O.CCIORD'=>$taData['nConsecCita'],
				'O.COAORD'=>$taData['cCUP'],
			])
			->get('array');
		if(is_array($laMedOrd)){
			if(count($laMedOrd)>0){
				$lcMedOrd=trim($laMedOrd['MED_ORD']);
			}
		}
		return $lcMedOrd;
	}


	/*
	 *	Calcular Nutrientes (Toda la tabla)
	 */
	private function CalculaNutrient()
	{
		foreach($this->aFDRNutrientes as $lcClave=>$laFDR){
			// Intercambios > 0  y  Tiene Coeficientes
			if($laFDR['NUMINTER']>0 && $laFDR['CLCLAR']=='CALCULAR' && !empty($laFDR['COEFNT'])){
				$laCoef = array_map('floatval', array_map('trim', explode(' ',$laFDR['COEFNT'])));
				// Tomas valores de la TabMae (COEFICIENTES DE NUTRIENTES)
				$lnEnerg= round($laCoef[ 0] * $laFDR['NUMINTER'],1);
				$lnCHO	= round($laCoef[ 1] * $laFDR['NUMINTER'],1);
				$lnProt	= round($laCoef[ 2] * $laFDR['NUMINTER'],1);
				$lnGrasa= round($laCoef[ 3] * $laFDR['NUMINTER'],1);
				$lnSat	= round($laCoef[ 4] * $laFDR['NUMINTER'],1);
				$lnMono	= round($laCoef[ 5] * $laFDR['NUMINTER'],1);
				$lnPoli	= round($laCoef[ 6] * $laFDR['NUMINTER'],1);
				$lnCol	= round($laCoef[ 7] * $laFDR['NUMINTER'],1);
				$lnNa	= round($laCoef[ 8] * $laFDR['NUMINTER'],1);
				$lnK	= round($laCoef[ 9] * $laFDR['NUMINTER'],1);
				$lnCa	= round($laCoef[10] * $laFDR['NUMINTER'],1);
				$lnP	= round($laCoef[11] * $laFDR['NUMINTER'],1);
				$lnH2O	= round($laCoef[12] * $laFDR['NUMINTER'],1);

				$this->aFDRNutrientes[$lcClave]['ENERGIA']=$lnEnerg;
				$this->aFDRNutrientes[$lcClave]['CHO']=$lnCHO;
				$this->aFDRNutrientes[$lcClave]['PROT']=$lnProt;
				$this->aFDRNutrientes[$lcClave]['GRASA']=$lnGrasa;
				$this->aFDRNutrientes[$lcClave]['SAT']=$lnSat;
				$this->aFDRNutrientes[$lcClave]['MONO']=$lnMono;
				$this->aFDRNutrientes[$lcClave]['POLI']=$lnPoli;
				$this->aFDRNutrientes[$lcClave]['COL']=$lnCol;
				$this->aFDRNutrientes[$lcClave]['NA']=$lnNa;
				$this->aFDRNutrientes[$lcClave]['K']=$lnK;
				$this->aFDRNutrientes[$lcClave]['CA']=$lnCa;
				$this->aFDRNutrientes[$lcClave]['P']=$lnP;
				$this->aFDRNutrientes[$lcClave]['H2O']=$lnH2O;
			}
		}
	}


	/*
	 *	Consultar Antecedentes en H.C.
	 */
	private function CalcTotalNutrie()
	{
		$lnNumInt=$lnEnerg=$lnCHO=$lnProt=$lnGrasa=$lnSat=$lnMono=$lnPoli=$lnCol=$lnNa=$lnK=$lnCa=$lnP=$lnH2O=0;
		foreach($this->aFDRNutrientes as $lcClave=>$laFDRN){
			if($laFDRN['CLCLAR']=='CALCULAR'){
				$lnNumInt	+= $laFDRN['NUMINTER'];
				$lnEnerg	+= $laFDRN['ENERGIA'];
				$lnCHO		+= $laFDRN['CHO'];
				$lnProt		+= $laFDRN['PROT'];
				$lnGrasa	+= $laFDRN['GRASA'];
				$lnSat		+= $laFDRN['SAT'];
				$lnMono		+= $laFDRN['MONO'];
				$lnPoli		+= $laFDRN['POLI'];
				$lnCol		+= $laFDRN['COL'];
				$lnNa		+= $laFDRN['NA'];
				$lnK		+= $laFDRN['K'];
				$lnCa		+= $laFDRN['CA'];
				$lnP		+= $laFDRN['P'];
				$lnH2O		+= $laFDRN['H2O'];
			}
		}
		// Totales
		$lnClave='099-01';
		$this->aFDRNutrientes[$lnClave]['NUMINTER']=$lnNumInt;
		$this->aFDRNutrientes[$lnClave]['ENERGIA']=$lnEnerg;
		$this->aFDRNutrientes[$lnClave]['CHO']=$lnCHO;
		$this->aFDRNutrientes[$lnClave]['PROT']=$lnProt;
		$this->aFDRNutrientes[$lnClave]['GRASA']=$lnGrasa;
		$this->aFDRNutrientes[$lnClave]['SAT']=$lnSat;
		$this->aFDRNutrientes[$lnClave]['MONO']=$lnMono;
		$this->aFDRNutrientes[$lnClave]['POLI']=$lnPoli;
		$this->aFDRNutrientes[$lnClave]['COL']=$lnCol;
		$this->aFDRNutrientes[$lnClave]['NA']=$lnNa;
		$this->aFDRNutrientes[$lnClave]['K']=$lnK;
		$this->aFDRNutrientes[$lnClave]['CA']=$lnCa;
		$this->aFDRNutrientes[$lnClave]['P']=$lnP;
		$this->aFDRNutrientes[$lnClave]['H2O']=$lnH2O;
	}


	/*
	 *	Consultar parámetros
	 */
	private function consultarParam()
	{
		$laParam = $this->oDb
			->select('CL1TMA, CL2TMA, CL3TMA, CL4TMA, DE1TMA, DE2TMA, OP2TMA')
			->from('TABMAE')
			->where('TIPTMA=\'NUTRICIO\' AND CL1TMA IN (\'HABINTES\',\'DIAGNUTR\',\'TIPEJERC\',\'FRECEJER\',\'COMIDAS\',\'NUTRIENT\')')
			->orderBy('CL1TMA, CL2TMA, CL3TMA, CL4TMA')
			->getAll('array');

		foreach($laParam as $laPar){
			$laPar=array_map('trim',$laPar);

			switch($laPar['CL1TMA']){

				case 'NUTRIENT':
					$lcClave = $laPar['CL2TMA'].'-'.$laPar['CL3TMA'];
					$this->aFDRNutrientes[$lcClave] = [
						'GRUPO'		=> $laPar['DE1TMA'],
						'NUMINTER'	=> 0,
						'ENERGIA'	=> 0,
						'CHO'		=> 0,
						'PROT'		=> 0,
						'GRASA'		=> 0,
						'SAT'		=> 0,
						'MONO'		=> 0,
						'POLI'		=> 0,
						'COL'		=> 0,
						'NA'		=> 0,
						'K'			=> 0,
						'CA'		=> 0,
						'P'			=> 0,
						'H2O'		=> 0,
						'CLCLAR'	=> $laPar['OP2TMA'],
						'INDICE2'	=> $laPar['CL2TMA'],
						'INDICE3'	=> $laPar['CL3TMA'],
						'MOSTRAR'	=> $laPar['CL4TMA'],
						'COEFNT'	=> $laPar['DE2TMA'],
					];
					break;

				case 'COMIDAS':
					$lcClave = $laPar['CL2TMA'].'-'.$laPar['CL3TMA'];
					$this->aFDRComidas[$lcClave] = [
						'cGrupo'    => $laPar['DE1TMA'],
						'nNumInter' => 0,
						'nDesayuno' => 0,
						'nNueves'   => 0,
						'nAlmuerzo' => 0,
						'nOnces'    => 0,
						'nComida'   => 0,
						'nRefrigerio' => 0,
						'nIndice2'  => $laPar['CL2TMA'],
					];
					break;

				default:
					$this->aParam[$laPar['CL1TMA']][$laPar['CL2TMA']] = $laPar;
					break;
			}
		}

		$laParam = $this->oDb
			->select('SUBSTR(IN2AND, 1, 2) AS INDICE, DESAND, OP3AND')
			->from('ANTDESL01')
			->where('IN1AND = 15 AND OP3AND > 0')
			->orderBy('OP3AND')
			->getAll('array');
		foreach($laParam as $laPar){
			$laPar=array_map('trim',$laPar);
			$this->aParam['ANTECTTL'][$laPar['INDICE']] = [
				'TITULO'=>$laPar['DESAND'],
				'ORDEN'=>$laPar['OP3AND'],
			];
		}
	}

	/*
	 *	Array de datos de documento vacío
	 */
	private function datosBlanco()
	{
		return [
			'cCodEspecialidad'=>'',
			'cCodOcupacion'=>'',
			'cMedicoOrdena'=>'',
			'aAnmAlm'=>[
				'cDesayuno'=>'',
				'cNueves'=>'',
				'cAlmuerzo'=>'',
				'cOnces'=>'',
				'cComida'=>'',
				'cRefrigerio'=>'',
			],
			'aDatVar'=>[
				'cAliRecha'=>'',
				'cHabIntes'=>'',
				'cTipEjerc'=>'',
				'cFreEjerc'=>'',
				'cSNAlcohol'=>'',
				'cSNCigarrillo'=>'',
				'cSNDulces'=>'',
				'cSNGaseosa'=>'',
				'cSNTinto'=>'',
				'cAlcohol'=>'',
				'cCigarrillo'=>'',
				'cDulces'=>'',
				'cGaseosa'=>'',
				'cOtros'=>'',
				'cTinto'=>'',
			],
			'lHayAntNutric'=>false,
			'lHayIngNutric'=>false,
			'lHayOtrosAnt'=>false,
			'aDatAntr'=>[
				'aTalla'=>0,
				'aPeso'=>0,
				'aPesoIdeal'=>0,
				'aPesoUsual'=>0,
				'aIMC'=>0,
				'aCintura'=>0,
				'aCirfCarpo'=>0,
				'aCambioPeso'=>0,
				'cDXNutricional'=>'',
			],
			'aForDiet'=>[
				'cDatComid'=>'',
				'cDatNutri'=>'',
				'nGrmCarb'=>0,
				'nGrmGras'=>0,
				'nGrmProt'=>0,
				'nKlcCarb'=>0,
				'nKlcGras'=>0,
				'nKlcProt'=>0,
				'nPorCarb'=>0,
				'nPorGras'=>0,
				'nPorProt'=>0,
				'nTKlc'=>0,
			],
			'aAntecedentes'=>[],
		];
	}

}
