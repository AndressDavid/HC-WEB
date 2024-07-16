<?php
namespace NUCLEO;

require_once ('class.UsuarioRegMedico.php');
require_once ('class.Diagnostico.php');
require_once ('class.Medico.php');
require_once ('class.Cup.php');
use NUCLEO\UsuarioRegMedico;
use NUCLEO\Diagnostico;
use NUCLEO\Medico;
use NUCLEO\Cup;


class Doc_DescripcionQx
{
	protected $oDb;
	protected $aVar = [];
	protected $aTipoCirugia = [];
	protected $aTipoAnestesia = [];
	protected $aTipoOtrasSalas = [];
	protected $aTipoEstadosDeSalida = [];
	protected $aClasificaCirugia = [];
	protected $dquproR133 = [];

	protected $aReporte = [
				'cTitulo' => 'DESCRIPCIÓN QUIRÚRGICA – CIRUGÍA',
				'lMostrarFechaRealizado' => false,
				'lMostrarViaCama' => false,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aNotas' => ['notas'=>true,],
			];


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->organizarDatos($taData);
		$this->organizarInforme($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		$this->consultarTipoCirugia();
		$this->consultarClasificaCirugia();
		$this->consultarTipoAnestesia();
		$this->consultarOtrasSalasRealizadas();
		$this->consultarEstadosDeSalida();
		$this->crearVar(['lcregmedInt', 'lcespInt', 'lcusuInt', 'lcnomfullInt', 'lctipousuInt', 'ciru1', ], '');
		$this->crearVar(['lnestmedInt', 'lndocInt', 'vEvep', 'ccoev', 'oQuino', ], 0);
		$this->crearVar(['lGuaFac', ], false);

		// Descripción Quirúrgica
		$this->laRIAHIS = $this->oDb
			->select('CONSEC, DESCRI, FECHIS, REGMED')
			->from('RIAHISL0')
			->innerJoin('RIARGMN', 'USUARI=USRHIS', null)
			->where([
				'NROING'=>$taData['nIngreso'],
				'CONCON'=>$taData['nConsecCita'],
				'SUBORG'=>$taData['cCUP'], // '22'
				'INDICE'=>70, 'SUBIND'=>0,
			])
			->orderBy('CONSEC')
			->getAll('array');


		/**********  DATOS DE PROCEDIMIENTOS Y PROFESIONALES  **********/

		// Programación de cirugías
		$this->laFACCIRH = $this->oDb
			->from('CIRCABHL01')
			->where([
				'INGCRH'=>$taData['nIngreso'],
				'CNSCRH'=>$taData['nConsecCons'],
			])
			->getAll('array');
		$lbConsultaAntes = is_array($this->laFACCIRH) ? count($this->laFACCIRH)==0 : true;
		if($lbConsultaAntes){
			$this->laFACCIRH = $this->oDb
				->from('FACCIRH')
				->where([
					'INGCRH'=>$taData['nIngreso'],
					'CNSCRH'=>$taData['nConsecCons'],
				])
				->getAll('array');
		}

		// Procedimientos por cirugía
		$this->laFACCIRP = $this->oDb
			->select('F.*, C.DESCUP')
			->from('CIRCUPPL01 F')
			->innerJoin('RIACUP C', 'F.CUPCRP=C.CODCUP', null)
			->where([
				'F.INGCRP'=>$taData['nIngreso'],
				'F.CNSCRP'=>$taData['nConsecCons'],
			])
			->orderBy('CUPCRP')
			->getAll('array');
		$lbConsultaAntes = is_array($this->laFACCIRP) ? count($this->laFACCIRP)==0 : true;
		if($lbConsultaAntes){
			$this->laFACCIRP = $this->oDb
				->from('FACCIRP F')
				->innerJoin('RIACUP C', 'F.CUPCRP=C.CODCUP', null)
				->where([
					'F.INGCRP'=>$taData['nIngreso'],
					'F.CNSCRP'=>$taData['nConsecCons'],
				])
				->orderBy('CUPCRP')
				->getAll('array');
		}
		if(is_array($this->laFACCIRP)){
			if(count($this->laFACCIRP)>0){
				foreach($this->laFACCIRP as $lnKey => $laFACCIRP){
					$this->laFACCIRP[$lnKey]=array_map('trim',$laFACCIRP);
				}
			}
		}

		// Equipos quirúrgicos por cirugía
		$this->laFACCIRQ = $this->oDb
			->from('CIRMEDQL01')
			->where([
				'INGCRQ'=>$taData['nIngreso'],
				'CNSCRQ'=>$taData['nConsecCons'],
			])
			->getAll('array');
		$lbConsultaAntes = is_array($this->laFACCIRQ) ? count($this->laFACCIRQ)==0 : true;
		if($lbConsultaAntes){
			$this->laFACCIRQ = $this->oDb
				->from('FACCIRQ')
				->where([
					'INGCRQ'=>$taData['nIngreso'],
					'CNSCRQ'=>$taData['nConsecCons'],
				])
				->getAll('array');
		}

		// Datos para procedimientos
		$this->laRIPAPS = $this->oDb
			->from('RIPAPSL2')
			->where([
				'INGAPS'=>$taData['nIngreso'],
				'CSCAPS'=>$taData['nConsecCons'],
			])
			->getAll('array');

		// Paciente muerto
		$this->laRIAMUR = $this->oDb
			->from('RIAMUR')
			->where([
				'TIDMOR'=>$taData['oIngrPaciente']->oPaciente->aTipoId['TIPO'],
				'NIDMOR'=>$taData['nNumDocPac'],
				'INGMOR'=>$taData['nIngreso'],
				'PGMMOR'=>'RIA133',
			])
			->getAll('array');

		$this->laRIAHISMED = $this->oDb
			->select('A.INDICE INDICE, TRIM(A.SUBORG) CODIGO, TRIM(A.DESCRI) CANTIDAD, TRIM(B.DESDES) DESCRIPCION')
			->from('RIAHIS A')
			->leftJoin("INVDES B", "TRIM(A.SUBORG)=TRIM(B.REFDES)", null)
			->where('A.NROING', '=', $taData['nIngreso'])
			->where('A.CONCON', '=', $taData['nConsecCita'])
			->where('A.INDICE', '=', 45)
			->orderBy('A.NROING, A.CONCON, A.INDICE')
			->getAll('array');
				
		$this->aVar['ciru1'] = $taData['cRegMedico'];
	}


	private function organizarDatos($taData)
	{

		/**********  ACTO QUIRURGICO  **********/
		$this->crearVar([
			'aqusci', 'aqutci', 'aquane', 'aquper', 'aqutan', 'aqucci', 'aqurie', 'aquasa', 'aqudco',
			'aquhal', 'lcFer2', 'lcDesi', 'lcDesf', 'lcRegRealiza', 'estadoSalida', 'fechahorainiciacirugia', 
			'registroInstrumentador' ], '');
		$this->crearVar(['aquren', 'aqufin', 'aquppo', 'vBloquea', 'lnFere', ], 0);
		$this->crearVar(['aquvia', 'aquaco', ], 1);
		$this->crearVar(['aquhin', 'aquhsa', ], date('Y-m-d') );


		foreach($this->laRIAHIS as $laRIAHIS){

			switch(true){

				//  1  **** SALA DE CIRIUGIA ****
				case $laRIAHIS['CONSEC']==1:
					$this->aVar['aqusci'] = trim(substr($laRIAHIS['DESCRI'],22,6));
					$this->aVar['aquren'] = intval(substr($laRIAHIS['DESCRI'],28, 1));
					//	Médico realiza descripción quirúrgica
					$this->aVar['lcRegRealiza'] = $laRIAHIS['REGMED'];
					break;

				//  2  **** TIPO DE CIRUGIA ****
				case $laRIAHIS['CONSEC']==2:
					$this->aVar['aqutci'] = trim(substr($laRIAHIS['DESCRI'],21,2));
					$this->aVar['aqucci'] = trim(substr($laRIAHIS['DESCRI'],24,2));
					break;

				//  5  **** ANESTESIÓLOGO ****
				case $laRIAHIS['CONSEC']==5:
					$this->aVar['aquane'] = trim(substr($laRIAHIS['DESCRI'],21,49));
					break;

				//  9  **** PERFUSIONISTA ****
				case $laRIAHIS['CONSEC']==9:
					$this->aVar['aquper'] = trim(substr($laRIAHIS['DESCRI'],21,49));
					break;

				//  10  **** TIPO DE ANESTESIA ****
				case $laRIAHIS['CONSEC']==10:
					$this->aVar['aqutan'] = trim(substr($laRIAHIS['DESCRI'],21,49));
					break;

				//  20  **** TIPO DE ANESTESIA ****
				case $laRIAHIS['CONSEC']==20:
					$this->aVar['aqufin'] = intval(substr($laRIAHIS['DESCRI'],10,4));
					$this->aVar['aquppo'] = intval(substr($laRIAHIS['DESCRI'],25,4));
					$this->aVar['aquvia'] = intval(substr($laRIAHIS['DESCRI'],40,4));
					$this->aVar['aqudco'] = trim(substr($laRIAHIS['DESCRI'],55,4));
					break;

				//  30  **** HORA INICIO - HORA FINAL ****
				case $laRIAHIS['CONSEC']==30:
					$this->aVar['lnFere'] = $laRIAHIS['FECHIS'];
					$this->aVar['lcFer2'] = str_pad($this->aVar['lnFere'], 8, '0', STR_PAD_LEFT);
					$this->aVar['lcDesi'] = str_pad(trim(substr($laRIAHIS['DESCRI'],  9, 10)), 6, '0', STR_PAD_LEFT);
					$this->aVar['lcDesf'] = str_pad(trim(substr($laRIAHIS['DESCRI'], 29, 10)), 6, '0', STR_PAD_LEFT);
					$this->aVar['aquhin'] = AplicacionFunciones::formatFechaHora('fechahora', intval($this->aVar['lcFer2'].$this->aVar['lcDesi']));
					$this->aVar['aquhsa'] = AplicacionFunciones::formatFechaHora('fechahora', intval($this->aVar['lcFer2'].$this->aVar['lcDesf']));
					$this->aVar['aqurie'] = trim(substr($laRIAHIS['DESCRI'],49,5));
					$this->aVar['aquasa'] = trim(substr($laRIAHIS['DESCRI'],64,5));
					break;

				//  301  **** HALLAZGOS ****
				case $laRIAHIS['CONSEC']>300 && $laRIAHIS['CONSEC']<400:
					$this->aVar['aquhal'] .= $laRIAHIS['DESCRI'];
					break;
				
				case $laRIAHIS['CONSEC']==131000:
					$this->aVar['estadoSalida'] = substr(trim($laRIAHIS['DESCRI']), 0, 1);
					$this->aVar['fechahorainiciacirugia'] = AplicacionFunciones::formatFechaHora('fechahora', intval(str_pad(substr(trim($laRIAHIS['DESCRI']), 2,8), 8, '0', STR_PAD_LEFT).$this->aVar['lcDesi']));
					$this->aVar['fechahorafinalcirugia'] = AplicacionFunciones::formatFechaHora('fechahora', intval(str_pad(substr(trim($laRIAHIS['DESCRI']), 11,8), 8, '0', STR_PAD_LEFT).$this->aVar['lcDesf']));
					break;
				
				case $laRIAHIS['CONSEC']==132000:
					$this->aVar['registroInstrumentador'] = trim($laRIAHIS['DESCRI']);
					break;
			}
		}
		$this->aVar['aquhal'] = trim($this->aVar['aquhal']);


		/**********  PROCEDIMIENTOS  **********/
		$cPro = '';
		$this->crearVar(['procod', 'prodpr', 'prodre', 'procir', 'proayu', 'proayu2', ], '');
		$this->crearVar(['provia', 'probil', 'proord', 'nOrd', 'proincru', ], 0);
		$this->crearVar(['procci', 'procay', 'procay2', ], 1);
		$this->aVar['procir'] = !empty($this->aVar['ciru1']) ? $this->aVar['ciru1'] : '';

		if(count($this->laFACCIRH)>0){
			$this->aVar['aqusci'] = trim($this->laFACCIRH[0]['SLRCRH']);
			$this->aVar['aqutci'] = trim($this->laFACCIRH[0]['TPRCRH']);
			$this->aVar['aqutan'] = trim($this->laFACCIRH[0]['TANCRH']);

			foreach($this->laFACCIRP as $laFACCIRP){
				$cPro = str_pad(trim($laFACCIRP['CUPCRP']), 8, '0', STR_PAD_LEFT);
				$lnClave = -1;
				foreach($this->dquproR133 as $lnKey => $dqupro){
					$lcCup = str_pad(trim($dqupro['codpro']), 8, '0', STR_PAD_LEFT);
					if($cPro == $lcCup){
						$lnClave = $lnKey;
						break;
					}
				}
				if($lnClave<0){
					$lnClave = count($this->dquproR133);
					$this->dquproR133[] = $this->itemDatoProc();
				}
				$this->dquproR133[$lnClave]['concir'] = $laFACCIRP['CNSCRP'];
				$this->dquproR133[$lnClave]['ordpro'] = $laFACCIRP['CNPCRP'];
				$this->dquproR133[$lnClave]['codpro'] = trim($laFACCIRP['CUPCRP']);
				//$this->dquproR133[$lnClave]['despro'] = trim(mb_substr($laFACCIRP['DESCUP'], 0, 100, 'UTF-8'));
				$this->dquproR133[$lnClave]['despro'] = trim($laFACCIRP['DESCUP']);
				$this->dquproR133[$lnClave]['equmed'] = $laFACCIRP['EQPCRP'];
				$this->dquproR133[$lnClave]['codvia'] = $laFACCIRP['VIACRP'];
				$this->dquproR133[$lnClave]['coddpr'] = trim($laFACCIRP['DG1CRP']);
				$this->dquproR133[$lnClave]['coddre'] = trim($laFACCIRP['DG2CRP']);
				$this->dquproR133[$lnClave]['codbil'] = trim($laFACCIRP['AY1CRP'])=='S' ? 1 : 0;
				$this->dquproR133[$lnClave]['incru'] =  trim($laFACCIRP['AY2CRP'])=='1' ? 1 : 0;
				$this->dquproR133[$lnClave]['estado'] = 'O';

				if(!empty($this->dquproR133[$lnClave]['coddpr'])){
					$loCIE = new Diagnostico($this->dquproR133[$lnClave]['coddpr'], $taData['oCitaProc']->nFechaRealiza);
					$this->dquproR133[$lnClave]['desdpr'] = $loCIE->getTexto();
				}
				if(!empty($this->dquproR133[$lnClave]['coddre'])){
					$loCIE = new Diagnostico($this->dquproR133[$lnClave]['coddre'], $taData['oCitaProc']->nFechaRealiza);
					$this->dquproR133[$lnClave]['desdre'] = $loCIE->getTexto();
				}
			}

			//
			$anecq = $prfcq = $pancq = 0;
			foreach($this->laFACCIRQ as $laFACCIRQ){
				$laFACCIRQ = array_map('trim', $laFACCIRQ);
				$anecq = $laFACCIRQ['ANECRQ'];
				$prfcq = $laFACCIRQ['PRFCRQ'];
				$pancq = $laFACCIRQ['PANCRQ']==0 ? 0 : 1;
				$this->aVar['aquaco'] = $pancq;

				$lcBuscar = $laFACCIRQ['EQPCRQ'];
				$lbEncontro = false;
				foreach($this->dquproR133 as $lnKey => $dqupro){
					$dqupro = array_map('trim', $dqupro);
					if($dqupro['equmed'] == $lcBuscar) {
						$this->dquproR133[$lnKey]['cedcir']  = $laFACCIRQ['ESPCRQ'] >  0 ? $laFACCIRQ['ESPCRQ'] : 0;
						$this->dquproR133[$lnKey]['cobcir']  = $laFACCIRQ['PESCRQ'] == 0 ? 0 : 1;
						$this->dquproR133[$lnKey]['cedayu']  = $laFACCIRQ['AY1CRQ'] >  0 ? $laFACCIRQ['AY1CRQ'] : 0;
						$this->dquproR133[$lnKey]['cobayu']  = $laFACCIRQ['PA1CRQ'] == 0 ? 0 : 1;
						$this->dquproR133[$lnKey]['cedayu2'] = $laFACCIRQ['AY2CRQ'] >  0 ? $laFACCIRQ['AY2CRQ'] : 0;
						$this->dquproR133[$lnKey]['cobayu2'] = $laFACCIRQ['PA2CRQ'] == 0 ? 0 : 1;
						$this->dquproR133[$lnKey]['diagnosticoprequirurgico'] = trim($laFACCIRQ['DS2CRQ']) ?? '';

						if(!empty($this->dquproR133[$lnKey]['diagnosticoprequirurgico'])){
							$loCIE = new Diagnostico($this->dquproR133[$lnKey]['diagnosticoprequirurgico'], $taData['oCitaProc']->nFechaRealiza);
							$this->dquproR133[$lnKey]['descripciondiagnosticoprequir'] = $loCIE->getTexto();
						} 
						$lbEncontro = true;
					}
					if($lbEncontro){

						// ** CAMBIO REGCCA X REGMED **
						if(!empty($laFACCIRQ['ESPCRQ'])){
							$laRegMed = $this->oDb
								->select('REGMED, TRIM(NOMMED)||\' \'||NNOMED AS NOMMED, CODRGM AS ESPMED')
								->from('RIARGMN3')
								->where([ 'NIDRGM'=>$laFACCIRQ['ESPCRQ'], ])
								->get('array');
							if(is_array($laRegMed)){
								$this->dquproR133[$lnKey]['codcir'] = trim($laRegMed['REGMED']);
								$this->dquproR133[$lnKey]['descir'] = trim($laRegMed['NOMMED']);
								$this->dquproR133[$lnKey]['espcir'] = trim($laRegMed['ESPMED']);
							}
							$laRegMed = null;
						}

						// ** CAMBIO REGAYU X REGMED **
						if(!empty($laFACCIRQ['AY1CRQ'])){
							$laRegMed = $this->oDb
								->select('REGMED, TRIM(NOMMED)||\' \'||NNOMED AS NOMMED, CODRGM AS ESPMED')
								->from('RIARGMN3')
								->where([ 'NIDRGM'=>$laFACCIRQ['AY1CRQ'], ])
								->get('array');
							if(is_array($laRegMed)){
								$this->dquproR133[$lnKey]['codayu'] = trim($laRegMed['REGMED']);
								$this->dquproR133[$lnKey]['desayu'] = trim($laRegMed['NOMMED']);
							}
							$laRegMed = null;
						}
						if(!empty($laFACCIRQ['AY2CRQ'])){
							$laRegMed = $this->oDb
								->select('REGMED, TRIM(NOMMED)||\' \'||NNOMED AS NOMMED, CODRGM AS ESPMED')
								->from('RIARGMN3')
								->where([ 'NIDRGM'=>$laFACCIRQ['AY2CRQ'], ])
								->get('array');
							if(is_array($laRegMed)){
								$this->dquproR133[$lnKey]['codayu2'] = trim($laRegMed['REGMED']);
								$this->dquproR133[$lnKey]['desayu2'] = trim($laRegMed['NOMMED']);
							}
							$laRegMed = null;
						}
						break;
					}
				}
			}

			// Obtiene número de justificaciones
			$laJustifs = $this->oDb
				->select('CNSCLJ, REFCLJ')
				->from('CUPELJUS')
				->where([
					'INGCLJ'=>$taData['nIngreso'],
					'CCTCLJ'=>$taData['nConsecCita'],
				])
				->getAll('array');
			if(is_array($laJustifs)){
				foreach($laJustifs as $laJustif){
					$lcCodPro = trim($laJustif['REFCLJ']);
					foreach($this->dquproR133 as $lnKey => $dqupro){
						if($dqupro['codpro']==$lcCodPro){
							$this->dquproR133['cjuado'] = $laJustif['CNSCLJ'];
							break;
						}
					}
				}
			}

			// Anestesiólogo
			if(!empty($anecq)){
				$loMed = new UsuarioRegMedico();
				if($loMed->cargarFiltro(['NIDRGM'=>$anecq,'TPMRGM'=>'6'], 'RIARGMN3')){
					$this->aVar['aquane'] = $loMed->getRegistro();
					$this->aVar['aquaco'] = $pancq;
				}
				$loMed = null;
			}

			// Perfusionista
			if(!empty($prfcq)){
				$loMed = new UsuarioRegMedico();
				if($loMed->cargarFiltro(['NIDRGM'=>$prfcq,'TPMRGM'=>'5'], 'RIARGMN3')){
					$this->aVar['aquper'] = $loMed->getRegistro();
				}
				$loMed = null;
			}


		// Procedimientos desde RIAHIS
		}else{
			$loCup = new Cup();
			$loMedico = new Medico();
			foreach($this->laRIAHIS as $laRIAHIS){
				if ($laRIAHIS['CONSEC']>400 && $laRIAHIS['CONSEC']<405) {
					$lcCup = trim(substr($laRIAHIS['DESCRI'],22,10));
					if(!empty($lcCup)){
						$loCup->cargarDatos($lcCup);
						$lcCupDsc = $loCup->cDscrCup;
					}
					$lcDxPrin = trim(substr($laRIAHIS['DESCRI'],32,10));
					if(!empty($lcDxPrin)){
						$loCIE = new Diagnostico($lcDxPrin, $taData['oCitaProc']->nFechaRealiza);
						$lcDxPrinDsc = $loCIE->getTexto();
					}
					$lcCirujanoReg = trim(substr($laRIAHIS['DESCRI'],43,13));
					if(!empty($lcCirujanoReg)){
						$loMedico->cargarRegistroMedico($lcCirujanoReg);
						$lcCirujanoDsc = $loMedico->getApellidosNombres();
					}
					$lcAyudanteReg = trim(substr($laRIAHIS['DESCRI'],57,13));
					if(!empty($lcAyudanteReg)){
						$loMedico->cargarRegistroMedico($lcAyudanteReg);
						$lcAyudanteDsc = $loMedico->getApellidosNombres();
					}
					$this->dquproR133[] = [
						'codpro' => $lcCup,
						'despro' => $lcCupDsc,
						'codcir' => $lcCirujanoReg,
						'descir' => $lcCirujanoDsc,
						'codayu' => $lcAyudanteReg,
						'desayu' => $lcAyudanteDsc,
						'coddpr' => $lcDxPrin,
						'desdpr' => $lcDxPrinDsc,
					];
				}
			}
		}


		/**********  DESCRIPCION QUIRURGICA / PERFUSION / COMPLICACIONES  **********/

		$this->crearVar(['dqudqu', 'dquepa', 'comobs', ], '');
		$this->crearVar(['perpre', 'perint', 'perpos', 'percar', 'perant', 'perret', 'persim', 'perpao', 'perper', 'perpto', 'pertre', ], 0);

		$laIndices = [
			100551 => 'perpre', // A.VENT.PREOPERATORIO
			100552 => 'perint', // A.VENT.INTRAOPERATORIO
			100553 => 'perpos', // A.VENT.POSOPERATORIO
			100554 => 'pertre', // TEMPERATURA RECTAL
			100555 => 'percar', // CARDIOPLEGIA
			100556 => 'perant', // CARDIOPLEGIA ANTEROGADA
			100557 => 'perret', // CARDIOPLEGIA RETROGRADA
			100558 => 'persim', // CARDIOPLEGIA SIMULTANEA
			100559 => 'perpao', // PINZA AORTICA
			100560 => 'perper', // TIEMPO DE PERFUSION
			100561 => 'perpto', // PARO TOTAL
		];

		foreach($this->laRIAHIS as $laRIAHIS){

			switch(true){

				//  501  **** DESCRIPCION QUIRURGICA ****
				case $laRIAHIS['CONSEC']>500 && $laRIAHIS['CONSEC']<90000:
					$this->aVar['dqudqu'] .= $laRIAHIS['DESCRI'];
					break;

				//  551 a 561
				case $laRIAHIS['CONSEC']>100550 && $laRIAHIS['CONSEC']<100562:
					$this->aVar[ $laIndices[ $laRIAHIS['CONSEC'] ] ] = floatval(substr($laRIAHIS['DESCRI'],21,49));
					break;

				//  901  **** ENVIO A PATOLOGIA ****
				case $laRIAHIS['CONSEC']>111000 && $laRIAHIS['CONSEC']<119999:
					$this->aVar['dquepa'] .= $laRIAHIS['DESCRI'];
					break;
					
				//  901  **** OBSERVACIONES ****
				case $laRIAHIS['CONSEC']>120000 && $laRIAHIS['CONSEC']<129999:
					$this->aVar['comobs'] .= $laRIAHIS['DESCRI'];
					break;

				//  901  **** COMPLICACIONES SI/NO
				case $laRIAHIS['CONSEC']==130000:
					$this->aVar['cComplicacionesSN'] = $laRIAHIS['DESCRI'];
					break;

			}
		}
		$this->aVar['dqudqu'] = trim($this->aVar['dqudqu']);
		$this->aVar['comobs'] = trim($this->aVar['comobs']);

		if(!empty($this->aVar['cComplicacionesSN'])){
			$this->aVar['cComplicacionesSN'] = trim($this->aVar['cComplicacionesSN']);
			$this->aVar['cComplicacionesSN'] = $this->aVar['cComplicacionesSN']=='S' ? 'SI' : 'NO';
		}

		/**********  PACIENTE FALLECIO  **********/

		$this->crearVar([
			'pfadg1', 'pfadg2', 'pfadg3', 'pfacm1', 'pfacm2', 'pfadg1', 'pfadg3', 'pfadpt',
			'pfafir', 'pfade1', 'pfade2', 'pfade3', 'pfadlg', 'pfamdl', 'pfaclm', 'pfaobs', ], '');
		$this->crearVar(['pfacca', 'pfacnc', 'pfacde', 'pfaaut', 'pfadho', 'pfahho', 'pfance', ], 0);
		$this->crearVar(['pfacon'], false);
		$this->crearVar(['pfafin'], $taData['oIngrPaciente']->nIngresoFecha);
		$this->crearVar(['pfafdf'], date('Y-m-d H:i:s'));

		if(count($this->laRIAMUR)>0){
			$laRIAMUR = $this->laRIAMUR[0];
			if($laRIAMUR['FDFMOR']==$this->laFACCIRH['FHRCRH']){
				$this->aVar['pfafdf'] = AplicacionFunciones::formatFechaHora('fechahora24',$laRIAMUR['FDFMOR']*1000000+$laRIAMUR['HDFMOR']);
				$this->aVar['pfadho'] = $laRIAMUR['NDIMOR'];				// Dias Hospitalizado
				$this->aVar['pfahho'] = $laRIAMUR['NHRMOR'];				// Horas Hospitalizado
				$this->aVar['pfadg1'] = $laRIAMUR['DG1MOR']=='0' ? '' : trim($laRIAMUR['DG1MOR']);
				$this->aVar['pfadg2'] = $laRIAMUR['DG2MOR']=='0' ? '' : trim($laRIAMUR['DG2MOR']);
				$this->aVar['pfadg3'] = $laRIAMUR['DG3MOR']=='0' ? '' : trim($laRIAMUR['DG3MOR']);
				$this->aVar['pfade1'] = $laRIAMUR['DE1MOR']=='0' ? '' : trim($laRIAMUR['DE1MOR']);
				$this->aVar['pfade2'] = $laRIAMUR['DE2MOR']=='0' ? '' : trim($laRIAMUR['DE2MOR']);
				$this->aVar['pfade3'] = $laRIAMUR['DE3MOR']=='0' ? '' : trim($laRIAMUR['DE3MOR']);
				$this->aVar['pfacm1'] = $laRIAMUR['CM1MOR']=='0' ? '' : trim($laRIAMUR['CM1MOR']);
				$this->aVar['pfacm2'] = $laRIAMUR['CM2MOR']=='0' ? '' : trim($laRIAMUR['CM2MOR']);
				$this->aVar['pfacca'] = $laRIAMUR['CCRMOR'];				// Causa Cardiovascular
				$this->aVar['pfacnc'] = $laRIAMUR['CLSMOR']=='S' ? 1 : 0;	// Causa No Clasificable
				$this->aVar['pfacde'] = $laRIAMUR['CRTMOR']=='S' ? 1 : 0;	// Certificado Defuncion
				$this->aVar['pfadlg'] = $laRIAMUR['DLGMOR'];				// Diligencia Adecuada
				$this->aVar['pfamdl'] = $laRIAMUR['MDLMOR'];				// Medicina Legal
				$this->aVar['pfafir'] = trim($laRIAMUR['FIRMOR']);			// Firma Certificado Defuncion
				$this->aVar['pfance'] = $laRIAMUR['NCRMOR'];				// Nro Certificado
				$this->aVar['pfaaut'] = $laRIAMUR['AUPMOR']=='S' ? 1 : 0;	// Autopsia
				$this->aVar['pfaclm'] = $laRIAMUR['CLMMOR'];				// Cont Lib Morgue
				$this->aVar['pfadpt'] = $laRIAMUR['DPTMOR']=='0' ? '' : trim($laRIAMUR['DPTMOR']);

				foreach($this->laRIAMUR as $laRIAMUR){
					$this->aVar['pfaobs'] .= $laRIAMUR['DPTMOR'];
				}
				$this->aVar['pfaobs'] = trim($this->aVar['DESMOR']);
				$this->aVar['pfacon'] = true;
			}
		}
	}

	/*
	 *	Organiza variables para el informe
	 */
	private function organizarInforme($taData)
	{

		$this->crearVar(['infren', 'inftan', 'infane', 'infper', 'infdco', 'inftci', 'infcci', 'infdmu', 'infpro', ';
			infrme', 'infnme', 'infdpr', 'infdr1', 'infdr2', 'infdr3', 'infhin', 'infhsa', 'infrie', 'infasa',
			'plntp', 'rf1tp', 'tarta', 'auxta', 'unili', 'canli', 'lcCodEspRealiza', 'datosMedicamentos', 'descripcionInstrumentador'], '');
		$this->crearVar(['infcpe'], 0);

		$this->aVar['aqusci'] = $this->aVar['aquren']>0 ? '' : $this->aVar['aqusci'];

		// Realizado en
		$this->aVar['infren'] = (empty($this->aVar['aqusci']) && !empty($this->aVar['aquren'])) ?
					($this->aTipoOtrasSalas[ $this->aVar['aquren'] ] ?? ''):
					'SALA '.$this->aVar['aqusci'] ;

		// Riesgo
		$this->aVar['infrie'] = !empty($this->aVar['aqurie']) ? $this->aVar['aqurie'] : '';

		// ASA
		$this->aVar['infasa'] = !empty($this->aVar['aquasa']) ? $this->aVar['aquasa'] : '';

		// Hora inicio / Hora salida
		$this->aVar['infhin'] = substr($this->aVar['aquhin'],11,5);
		$this->aVar['infhsa'] = substr($this->aVar['aquhsa'],11,5);

		// Tipo anestesia
		if(!empty($this->aVar['aqutan'])){
			$this->aVar['infcpe'] = $this->aVar['aqutan']==1 ? 1 : 0;
			$this->aVar['inftan'] = $this->aTipoAnestesia[ $this->aVar['aqutan'] ] ?? '';
		}

		// Anestesiólogo
		if(!empty($this->aVar['aquane'])){
			$loMed = new UsuarioRegMedico();
			if($loMed->cargarRegistro($this->aVar['aquane'])){
				$this->aVar['infane'] = $loMed->nId.' '.$loMed->cApellido1.' '.$loMed->cNombre1;
			}
			$loMed = null;
		}

		// Perfusionista
		if(!empty($this->aVar['aquper'])){
			$loMed = new UsuarioRegMedico();
			if($loMed->cargarRegistro($this->aVar['aquper'])){
				$this->aVar['infper'] = $loMed->nId.' '.$loMed->cApellido1.' '.$loMed->cNombre1;
			}
			$loMed = null;
		
		}
		// Instrumentador
		if(!empty($this->aVar['registroInstrumentador'])){
			$loMed = new UsuarioRegMedico();
			if($loMed->cargarRegistro($this->aVar['registroInstrumentador'])){
				$this->aVar['descripcionInstrumentador'] = $loMed->nId.' '.$loMed->cApellido1.' '.$loMed->cNombre1;
			}
			$loMed = null;
		}

		// Diagnóstico complicación
		if(!empty($this->aVar['aqudco'])){
			$loCIE = new Diagnostico($this->aVar['aqudco'], $taData['oCitaProc']->nFechaRealiza);
			$lcCie = $loCIE->getTexto();
			if(!empty($lcCie))
				$this->aVar['infdco'] = $this->aVar['aqudco'].' '.$lcCie;
			$loCIE = null;
		}

		// TIPO CIRUGIA
		$this->aVar['inftci'] = $this->aTipoCirugia[ $this->aVar['aqutci'] ] ?? '';

		// CLASE CIRUGIA
		$this->aVar['infcci'] = $this->aClasificaCirugia[ $this->aVar['aqucci'] ] ?? '';

		//	Especialidad realiza descripción quirúrgica
		$laEspQx = $this->oDb
			->select('TRIM(SUBSTR(DESEVL, 1, 3)) AS CODESP')
			->from('EVOLUC')
			->where([
				'NINEVL'=>$taData['nIngreso'],
				'CCIEVL'=>$taData['nConsecCita'],
				'CNLEVL'=>900010,
			])
			->get('array');
		if(is_array($laEspQx)){
			if(count($laEspQx)>0){
				$this->aVar['lcCodEspRealiza'] = $laEspQx['CODESP'] ?? '';
			}
		}

		//	Diagnóstico muerte
		if(!empty($this->aVar['pfacm1'])){
			$loCIE = new Diagnostico($this->aVar['pfacm1'], $taData['oCitaProc']->nFechaRealiza);
			$lcCie = $loCIE->getTexto();
			if(!empty($lcCie))
				$this->aVar['infdmu'] = $this->aVar['pfacm1'].' '.$lcCie;
			$loCIE = null;
		}

		// Procedimientos
		$this->aVar['infpro'] = $this->aVar['infrme'] = '';
		foreach($this->dquproR133 as $dquproR133){
			$lcCodCir = $dquproR133['codcir']??'';
			$lcDesCir = $dquproR133['descir']??'';
			$this->aVar['infrme'] = empty($this->aVar['infrme']) ? $lcCodCir : $this->aVar['infrme'];
			$this->aVar['infnme'] = empty($this->aVar['infnme']) ? $lcDesCir : $this->aVar['infnme'];
			$this->aVar['infdpr'] = empty($this->aVar['infdpr']) ? $dquproR133['coddpr'].' '.$dquproR133['desdpr'] : $this->aVar['infdpr'];
			$this->aVar['infdr3'] = (!empty($this->aVar['infdr2']) && empty($this->aVar['infdr3'])) ? trim($dquproR133['coddre']??'').' '.trim($dquproR133['desdre']??'') : $this->aVar['infdr3'];
			$this->aVar['infdr2'] = (!empty($this->aVar['infdr1']) && empty($this->aVar['infdr2'])) ? trim($dquproR133['coddre']??'').' '.trim($dquproR133['desdre']??'') : $this->aVar['infdr2'];
			$this->aVar['infdr1'] = empty($this->aVar['infdr1']) ? trim($dquproR133['coddre']??'').' '.trim($dquproR133['desdre']??'') : $this->aVar['infdr1'];
			$this->aVar['infpro'] .= (empty($this->aVar['infpro']) ? '' : PHP_EOL)
								. str_pad($dquproR133['codpro']??'',9) . trim($dquproR133['despro']??''). PHP_EOL
								. (empty($lcDesCir)				 ? '' : 'Cirujano : '		. $lcDesCir . PHP_EOL)
								. (empty($dquproR133['desayu'] ) ? '' : 'Ayudante 1 : '		. $dquproR133['desayu'] . PHP_EOL)
								. (empty($dquproR133['desayu2']) ? '' : 'Ayudante 2 : '		. $dquproR133['desayu2']. PHP_EOL)
								. (empty($dquproR133['diagnosticoprequirurgico'] ) ? '' : 'Diagnóstico Prequirúrgico   : '	. $dquproR133['diagnosticoprequirurgico'] .' '.$dquproR133['descripciondiagnosticoprequir']. PHP_EOL)
								. (empty($dquproR133['desdpr'] ) ? '' : 'Diagnóstico Postquirúrgico  : '	. $dquproR133['coddpr'] .' '.$dquproR133['desdpr']. PHP_EOL)
								. (empty($dquproR133['desdre'] ) ? '' : 'Dx Relacionado : '	. $dquproR133['coddre'] .' '.$dquproR133['desdre']. PHP_EOL);
		}

		$this->aVar['datosMedicamentos'] = '';
		foreach($this->laRIAHISMED as $medicamentos){
			$lnIndiceInsumo = intval($medicamentos['INDICE'])??0;
			$lcCodigoMedicamento = $medicamentos['CODIGO']??'';
			$lcDescripcionMedicamento = $medicamentos['DESCRIPCION']??'';
			$lcCantidadMedicamento = $medicamentos['CANTIDAD']??'';
			if ($lnIndiceInsumo==45){ $this->aVar['datosMedicamentos'] .= '* '.$lcDescripcionMedicamento. ',  '.$lcCantidadMedicamento.PHP_EOL; }
		}	
	}

	//	dquproR133 - DATOS PROCEDIMIENTOS
	private function itemDatoProc()
	{
		return [
			'selecc' => 0,				// Campo Selección
			'concir' => 0,				// Consecutivo Cirugia
			'ordpro' => 0,				// Orden Procedimiento
			'codpro' => '',				// Codigo Procedimiento
			'despro' => '',				// Descripcion Procedimiento
			'equmed' => 0,				// Consecutivo Equipo Médico
			'uniliq' => '',				// Unidad de Liquidacion
			'canliq' => 0,				// Cantidad de Liquidacion
			'codcir' => '',				// Registro Cirujano
			'cedcir' => 0,				// Cedula Cirujano
			'espcir' => '',				// Especialidad Cirujano
			'descir' => '',				// Nombre Cirujano
			'cobcir' => 0,				// Cobrable Cirujano
			'codayu' => '',				// Registro Ayudante
			'codayu2' => '',			// Registro Ayudante2
			'cedayu' => 0,				// Cedula Ayudante1
			'desayu' => '',				// Nombre Ayudante1
			'cedayu2' => 0,				// Cedula Ayudante2
			'desayu2' => '',			// Nombre Ayudante2
			'cobayu' => 0,				// Cobrable Ayudante 1
			'cobayu2' => 0,				// Cobrable Ayudante 2
			'codvia' => 0,				// Via Ingreso
			'codbil' => 0,				// Bilateralidad
			'incru' => 0,				// incruento
			'codcob' => 0,				// Cobrable
			'coddpr' => '',				// Codigo Dx Principal
			'desdpr' => '',				// Descripcion Dx Principal
			'coddre' => '',				// Codigo Dx Relacionado
			'desdre' => '',				// Descripcion Dx Relacionado
			'diagnosticoprequirurgico' => '',			
			'descripciondiagnosticoprequir' => '',		
			'estado' => '',
			'pnopro' => '',				// Procedimiento POS o NoPOS (P/N)
			'cjuado' => 0,
			'especialidad_cups' => '',
			'cComplicacionesSN' => '',
		];
	}

	//	CODTCI
	private function consultarTipoCirugia()
	{
		$laDatas = $this->oDb
			->select('DSLTAB AS DESTCI, SUBSTR(CDSTAB,1,2) AS CODTCI')
			->from('RIATAB')
			->where('CDTTAB=16 AND CDRTAB=1 AND CDSTAB>0')
			->orderBy('DSLTAB')
			->getAll('array');

		if(is_array($laDatas)){
			if(count($laDatas)>0){
				foreach($laDatas as $laData){
					$laData = array_map('trim',$laData);
					$this->aTipoCirugia[$laData['CODTCI']] = $laData['DESTCI'];
				}
			}
		}
	}

	private function consultarOtrasSalasRealizadas()
	{
		$laDatas = $this->oDb
			->select('TRIM(DE2TMA) DESCRIPCION, INT(CL2TMA) CODIGO')
			->from('TABMAEL01')
			->where('TIPTMA=\'DESCRQX\' AND CL1TMA=\'OTRASAL\' AND ESTTMA<>\'1\'')
			->orderBy('DE1TMA')
			->getAll('array');

		if(is_array($laDatas)){
			if(count($laDatas)>0){
				foreach($laDatas as $laData){
					$laData = array_map('trim',$laData);
					$this->aTipoOtrasSalas[$laData['CODIGO']] = $laData['DESCRIPCION'];
				}
			}
		}
	}
	private function consultarEstadosDeSalida()
	{
		$laDatas = $this->oDb
			->select('TRIM(TABDSC) DESCRIPCION, INT(TABCOD) CODIGO')
			->from('PRMTAB')
			->where('TABTIP=\'ESL\' AND TABCOD<>\' \'')
			->orderBy('TABDSC')
			->getAll('array');

		if(is_array($laDatas)){
			if(count($laDatas)>0){
				foreach($laDatas as $laData){
					$laData = array_map('trim',$laData);
					$this->aTipoEstadosDeSalida[$laData['CODIGO']] = $laData['DESCRIPCION'];
				}
			}
		}
	}

	//	CODANE
	private function consultarTipoAnestesia()
	{
		$laDatas = $this->oDb
			->select('DE1TMA AS DESTAN, SUBSTR(TRIM(CL1TMA),1,2) AS CODTAN')
			->from('TABMAEL01')
			->where('TIPTMA=\'CODTAN\' AND ESTTMA<>\'1\'')
			->orderBy('DE1TMA')
			->getAll('array');

		if(is_array($laDatas)){
			if(count($laDatas)>0){
				foreach($laDatas as $laData){
					$laData = array_map('trim',$laData);
					$this->aTipoAnestesia[$laData['CODTAN']] = $laData['DESTAN'];
				}
			}
		}
	}


	//	CODCCI
	private function consultarClasificaCirugia()
	{
		$this->aClasificaCirugia = [
			'1' => 'Limpia',
			'2' => 'Limpia Contaminada',
			'3' => 'Contaminada',
			'4' => 'Sucia Infectada',
		];
	}


	private function prepararInforme($taData)
	{
		$laTr = [];

		$lcTxt = 'Fecha Realizado: ' . str_replace('-','/',substr($taData['tFechaHora'],0,10)).PHP_EOL
				.( empty($this->aVar['infren']) ? '' : 'Realizando En  : '.$this->aVar['infren'].PHP_EOL )
				.( empty($this->aVar['descripcionInstrumentador']) ? '' : 'Instrumentador : '.$this->aVar['descripcionInstrumentador'].PHP_EOL )
				.( empty($this->aVar['inftan']) ? '' : 'Tipo Anestesia : '.$this->aVar['inftan'].PHP_EOL )
				.( empty($this->aVar['infane']) ? '' : 'Anestesiólogo  : '.$this->aVar['infane'].PHP_EOL )
				.( empty($this->aVar['infper']) ? '' : 'Perfusionista  : '.$this->aVar['infper'].PHP_EOL )
				.( empty($this->aVar['inftci']) ? '' : 'Tipo Cirugía   : '.$this->aVar['inftci'].PHP_EOL )
				.( empty($this->aVar['infcci']) ? '' : 'Clase Cirugía  : '.$this->aVar['infcci'].PHP_EOL )
				.( empty($this->aVar['infdco']) ? '' : 'Diag. Complic. : '.$this->aVar['infdco'].PHP_EOL )
				.( empty($this->aVar['infdmu']) ? '' : 'Diag. Muerte   : '.$this->aVar['infdmu'].PHP_EOL )
				.( empty($this->aVar['infrie']) ? '' : 'Riesgo         : '.$this->aVar['infrie'].PHP_EOL )
				.( empty($this->aVar['infasa']) ? '' : 'Asa            : '.$this->aVar['infasa'].PHP_EOL )
				.( empty($this->aVar['fechahorainiciacirugia']) ? 'Hora de Inicio : '.$this->aVar['infhin'] : 'Fecha/Hora de Inicio cirugía : '.$this->aVar['fechahorainiciacirugia'].PHP_EOL )
				.( empty($this->aVar['fechahorafinalcirugia']) ? 'Hora de Salida : '.$this->aVar['infhsa'] : 'Fecha/Hora de Salida cirugía : '.$this->aVar['fechahorafinalcirugia'].PHP_EOL );
		$laTr[] = ['texto9', $lcTxt];

		$laTr[] = ['titulo1', 'PROCEDIMIENTOS REALIZADOS'];
		$laTr[] = ['texto9', $this->aVar['infpro']];

		if($this->aVar['infcpe']==1){
			$laTr[] = ['titulo1', 'PERFUSIÓN'];
			$lcTxt = ( (!empty($this->aVar['perpre']) || !empty($this->aVar['perint']) || !empty($this->aVar['perpos'])) ?
						 'Asistencia Ventricular'.PHP_EOL
						.'Preoperatorio : '.($this->aVar['perpre']==1 ? 'X' : ' ').'             '
						.'Intraoperatorio : '.($this->aVar['perint']==1 ? 'X' : ' ').'           '
						.'Postperatorio : '.($this->aVar['perpos']==1 ? 'X' : ' ').'             '.PHP_EOL : '')
						.'Cardioplejía : '.($this->aVar['percar']==1 ? 'SI' : 'NO').PHP_EOL
						.( (!empty($this->aVar['perant']) || !empty($this->aVar['perret']) || !empty($this->aVar['persim'])) ?
						 AplicacionFunciones::mb_str_pad('Antegrada : '.$this->aVar['perant'].' cc', 30)
						.AplicacionFunciones::mb_str_pad('Retrógrada : '.$this->aVar['perret'].' cc', 30)
						.AplicacionFunciones::mb_str_pad('Simultánea : '.$this->aVar['persim'].' cc', 30).PHP_EOL : '')
						.( (!empty($this->aVar['perpao']) || !empty($this->aVar['perper']) || !empty($this->aVar['perpto'])) ?
						 'Tiempos'.PHP_EOL
						.AplicacionFunciones::mb_str_pad('Pinza Aórtica : '.$this->aVar['perpao'].' m', 30)
						.AplicacionFunciones::mb_str_pad('Perfusión : '.$this->aVar['perper'].' m', 30)
						.AplicacionFunciones::mb_str_pad('Paro Total : '.$this->aVar['perpto'].' m', 30).PHP_EOL : '')
						.( !empty($this->aVar['pertre']) ? 'Temperatura Rectal : '.number_format(floatval($this->aVar['pertre']),1).' grados' : '' );
			$laTr[] = ['texto9', $lcTxt];
		}

		if(!empty($this->aVar['aquhal'])){
			$laTr[] = ['titulo1', 'HALLAZGOS'];
			$laTr[] = ['texto9', $this->aVar['aquhal']];
		}

		if(!empty($this->aVar['dqudqu'])){
			$laTr[] = ['titulo1', 'DESCRIPCIÓN QUIRÚRGICA'];
			$laTr[] = ['texto9', $this->aVar['dqudqu']];
			if(!empty($this->aVar['dquepa'])){
				$laTr[] = ['texto9', PHP_EOL . 'ENVÍO A PATOLOGÍA: '.$this->aVar['dquepa']];
			}
		}

		if(!empty($this->aVar['comobs']) || !empty($this->aVar['cComplicacionesSN'])){
			$laTr[] = ['titulo1', 'COMPLICACIONES: ' .(empty($this->aVar['cComplicacionesSN']) ? ' ' : $this->aVar['cComplicacionesSN'])];
			$laTr[] = ['texto9', $this->aVar['comobs']];
		}

		if(!empty($this->aVar['estadoSalida'])){
			$laTr[] = ['titulo1', 'Estado de salida'];
			$laTr[] = ['texto9', $this->aTipoEstadosDeSalida[ $this->aVar['estadoSalida'] ] ?? ''];
		}

		if(!empty($this->aVar['datosMedicamentos'])){
			$laTr[] = ['titulo1', 'Medicamentos utilizados'];
			$laTr[] = ['texto9', $this->aVar['datosMedicamentos']];	
		}

		$laTr[] = ['firmas', [ [
				'registro'=>$this->aVar['lcRegRealiza'],
				'codespecialidad'=>$this->aVar['lcCodEspRealiza'],
				'prenombre'=>'Dr.',
				'preregistro'=>'RM:',
			] ] ];

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
