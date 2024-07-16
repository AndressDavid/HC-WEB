<?php
namespace NUCLEO;

class Doc_Enf_Neurologico
{
	protected $oDb;
	protected $aControlNeu = [];
	protected $nEdad=0;
	protected $nFechaInicial=0;
	protected $nFechaFinal=0;
	protected $aOcular = [];
	protected $aVerbal = [];
	protected $aMotora = [];
	protected $aNivelC = [];
	protected $aReflejos = [];
	protected $aPupilas = [];
	protected $aPatronR = [];
	protected $aCrisisC = [];
	protected $aTotales = [];
	protected $aTurno = [];
	protected $aTurnoEnf = [];
	protected $aCtrNeurol = [];

	protected $aReporte = [
					'cTitulo' => 'CONTROL NEUROLOGICO',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>false,],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
	}

	// Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->nEdad = intval($taData['oIngrPaciente']->aEdad['y']);
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	//	Consulta los datos
	private function consultarDatos($taData){

		$lcSL = PHP_EOL;
		$lnFechaInicio = 0;

		$lnHoraInicial = 70000;
		$lnFechaInicio = intval(str_replace('-','',substr($taData['tFechaHora'],0,10)));
		$lnHoraReporte = intval(str_replace(':','',substr($taData['tFechaHora'],10,16)));
		$lnFechaInicio = ($lnHoraReporte<$lnHoraInicial ? intval(date('Ymd',strtotime('-1 day' , strtotime($lnFechaInicio)))) : $lnFechaInicio) ;
		$lnFecHoraInicio = $lnFechaInicio * 1000000 + $lnHoraInicial;
		$lnFechaFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio))));
		$lnFecHoraFinal = intval(date('Ymd',strtotime('+1 day' , strtotime($lnFechaInicio)))) * 1000000 + $lnHoraInicial;
		$lnFechaIng = intval(date('Ymd',strtotime($taData['oIngrPaciente']->nIngresoFecha)));

		$this->nFechaInicial=$lnFechaInicio;
		$this->nFechaFinal=$lnFechaFinal;

		$laHoras = [
			'7_'=>'', '8_'=>'', '9_'=>'', '10_'=>'', '11_'=>'', '12_'=>'', '13_'=>'', '14_'=>'',
			'15_'=>'', '16_'=>'', '17_'=>'', '18_'=>'', '19_'=>'', '20_'=>'', '21_'=>'', '22_'=>'',
			'23_'=>'', '24_'=>'', '1_'=>'', '2_'=>'', '3_'=>'', '4_'=>'', '5_'=>'', '6_'=>'',
		];

		// Consulta de Control neurológico
		$this->aControlNeu = $this->oDb
			->select('A.CTUNEC AS CONSEC, A.FCRNEC AS FECHAD, A.HRRNEC AS HORAD, A.PUPNEC AS DESC1, A.EGLNEC AS ESCALA,
			          A.OBSNEC AS DESC2, A.USRNEC AS USUARIO, IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS USUARIO, B.TPMRGM AS TIPO')
			->from('ENNEURC AS A')
			->leftJoin('RIARGMN AS B', 'A.USRNEC=B.USUARI', null)
			->where(['A.INGNEC'=>$taData['nIngreso'],])
			->between('A.FCRNEC*1000000+A.HRRNEC',$lnFecHoraInicio,$lnFecHoraFinal)
			->orderBy ('A.FCRNEC, A.HRRNEC, A.CTUNEC')
			->getAll('array');

		// Consulta APERTURA OCULAR
		$aTemp = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where(['TIPENF'=>20,
					 'VARENF'=>1,
					])
			->orderBy('REFENF')
			->getAll('array');

		foreach($aTemp as $laRegistro) {
			$this->aOcular[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF']), 'valor'=>trim($laRegistro['REFENF'])],
				$laHoras);
		}

		// Consulta RESPUESTA VERBAL
		if($this->nEdad<2){

			$aTemp = $this->oDb
				->select('DESENF, REFENF')
				->from('TABENF')
				->where(['TIPENF'=>20,
						 'VARENF'=>8,
						])
				->orderBy('REFENF')
				->getAll('array');
		}
		else{

			$aTemp = $this->oDb
				->select('DESENF, REFENF')
				->from('TABENF')
				->where(['TIPENF'=>20,
						 'VARENF'=>2,
						])
				->orderBy('REFENF')
				->getAll('array');

		}

		foreach($aTemp as $laRegistro) {
			$this->aVerbal[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF']), 'valor'=>trim($laRegistro['REFENF'])],
				$laHoras);
		}

		// Consulta RESPUESTA MOTORA
		if($this->nEdad<2){

			$aTemp = $this->oDb
				->select('DESENF, REFENF')
				->from('TABENF')
				->where(['TIPENF'=>20,
						 'VARENF'=>9,
						])
				->orderBy('REFENF')
				->getAll('array');
		}
		else{

			$aTemp = $this->oDb
				->select('DESENF, REFENF')
				->from('TABENF')
				->where(['TIPENF'=>20,
						 'VARENF'=>3,
						])
				->orderBy('REFENF')
				->getAll('array');

		}

		foreach($aTemp as $laRegistro) {
			$this->aMotora[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF']), 'valor'=>trim($laRegistro['REFENF'])],
				$laHoras);
		}

		// Consulta NIVEL DE CONCIENCIA
		$aTemp = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where(['TIPENF'=>4,
					 'VARENF'=>5,
					])
			->orderBy('REFENF')
			->getAll('array');

		foreach($aTemp as $laRegistro) {
			$this->aNivelC[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF'])],
				$laHoras);
		}


		// CREAR CURSOR DE REFLEJOS

		$laTitulo = ['Corneano (+)(-)', 'Palpebral', 'Oculocefálico'];
		for ($lnPosicion=0; $lnPosicion<3; $lnPosicion++){
			$this->aReflejos[$lnPosicion] = array_merge(
				['titulo'=>$laTitulo[$lnPosicion]],
				$laHoras);
		}


		// CREAR CURSOR DE PUPILAS

		$laTitulo = ['Isocóricas','Anisocóricas','Miótica Derecha','Miótica Izquierda','Midiátrica Derecha','Midiátrica Izquierda'];
		for ($lnPosicion=0; $lnPosicion<6; $lnPosicion++){
			$this->aPupilas[$lnPosicion] = array_merge(
				['titulo'=>$laTitulo[$lnPosicion]],
				$laHoras);
		}

		// Consulta PATRON RESPIRATORIO
		$aTemp = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where(['TIPENF'=>20,
					 'VARENF'=>7,
					])
			->orderBy('REFENF')
			->getAll('array');

		foreach($aTemp as $laRegistro) {
			$this->aPatronR[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF'])],
				$laHoras);
		}

		// Consulta CRISIS CONVULSIVA
		$aTemp = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where(['TIPENF'=>4,
					 'VARENF'=>4,
					])
			->orderBy('REFENF')
			->getAll('array');

		foreach($aTemp as $laRegistro) {
			$this->aCrisisC[trim($laRegistro['REFENF'])] = array_merge(
				['titulo'=>trim($laRegistro['DESENF'])],
				$laHoras);
		}

		// ARRAY para manejo de Totales
		$this->aTotales = [
		       	'7_' => '',
				'8_' => '',
				'9_' => '',
				'10_' =>'',
				'11_' =>'',
				'12_' =>'',
				'13_' =>'',
				'14_' =>'',
				'15_' =>'',
				'16_' =>'',
				'17_' =>'',
				'18_' =>'',
				'19_' =>'',
				'20_' =>'',
				'21_' =>'',
				'22_' =>'',
				'23_' =>'',
				'24_' =>'',
				'1_' => '',
				'2_' => '',
				'3_' => '',
				'4_' => '',
				'5_' => '',
				'6_' => '',				
			];

		// Consulta TURNOS
		$laTurnos = $this->oDb
			->select('TRIM(DE1TMA) DIA, DE2TMA, OP2TMA, OP3TMA, OP7TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'TURNOSEN',
					 'ESTTMA'=>'',
					])
			->getAll('array');

		foreach($laTurnos as $laTurno){

			$lcCharReg = '.';
			$laWordsReg = explode($lcCharReg, $laTurno['DIA']);
			if(count($laWordsReg)>0){

				foreach($laWordsReg as $laReg){

					$lnReg = count($this->aTurno);
					$this->aTurno[$lnReg]['Dia'] = $laReg;
					$this->aTurno[$lnReg]['Nombre'] = trim($laTurno['DE2TMA']);
					$this->aTurno[$lnReg]['DiaSig'] = $laTurno['OP2TMA'];
					$this->aTurno[$lnReg]['HoraIni'] = $laTurno['OP3TMA'];
					$this->aTurno[$lnReg]['HoraFin'] = $laTurno['OP7TMA'];

				}

			}

		}

		$this->aTurnoEnf = [
			'TURNO 1' =>['Aux'=>'','Jefe'=>''],
			'TURNO 2' =>['Aux'=>'','Jefe'=>''],
			'TURNO 3' =>['Aux'=>'','Jefe'=>''],
		];

		$this->fnOrganizarDatos();
		$this->fnActualizaTurno();

	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme($taData)
	{

		$laTr['aCuerpo'] = [];
		$lcSL = PHP_EOL;

		$lcFechaInicial = AplicacionFunciones::formatFechaHora('fecha', $this->nFechaInicial);
		$lcFechaFinal = AplicacionFunciones::formatFechaHora('fecha', $this->nFechaFinal);
		$laAnchosTit1 = [90,76,24];
		$laAnchosTit2 = [90,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4];

		// Información Titulos
		$laTituloTbl = [ [ 'w'=>$laAnchosTit1, 'd'=>['FECHA DE VALORACION',$lcFechaInicial,$lcFechaFinal], 'a'=>'C', ],
						 [ 'w'=>$laAnchosTit2, 'd'=>['CRITERIOS DE VALORACION / HORA','  ','7','8','9','10','11',
								    '12','13','14','15','16','17','18','19','20','21','22','23','24','1','2','3','4','5','6'], 'a'=>'C', ],
						 ];

		// Información Contenido
		$laContenidoTbl = [];
		$laContenidoTbl = array_merge($laContenidoTbl,
						$this->fnFilasTabla($this->aOcular,'<b>APERTURA OCULAR</b>' ,true),
						$this->fnFilasTabla($this->aVerbal,'<b>RESPUESTA VERBAL</b>',true),
						$this->fnFilasTabla($this->aMotora,'<b>RESPUESTA MOTORA</b>',true)
						);

		$laAnchos = [94,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4];
		$laAling = 'C';
		$laContenidoTbl[] = ['w'=>$laAnchos, 'a'=>$laAling, 'd'=>array_merge(['<b>T O T A L &nbsp;&nbsp; E S C A L A &nbsp;&nbsp; G L A S G O W</b>'],$this->aTotales)];
		
		$laContenidoTbl = array_merge($laContenidoTbl,
						$this->fnFilasTabla($this->aNivelC,	'<b>NIVEL DE CONCIENCIA</b>'),
						$this->fnFilasTabla($this->aReflejos,'<b>REFLEJOS</b>'),
						$this->fnFilasTabla($this->aPupilas, '<b>PUPILAS</b>'),
						$this->fnFilasTabla($this->aPatronR, '<b>PATRON RESPIRATORIO</b>'),
						$this->fnFilasTabla($this->aCrisisC, '<b>CRISIS CONVULSIVA</b>')
						);
		$laTr['aCuerpo'][] = ['tabla', $laTituloTbl, $laContenidoTbl, ['fs'=>8] ];

		// Tabla Usuario y turnos
		$laAnchos = [30,80,80];
		$laTr['aCuerpo'][] = ['saltol', 3];
		$laTr['aCuerpo'][] = ['tabla',
							[ [ 'w'=>$laAnchos, 'd'=>['TURNO','NOMBRE AUXILIAR','NOMBRE ENFERMERA JEFE'], 'a'=>'C', ] ],
							[ [ 'w'=>$laAnchos, 'd'=>['<b>T.M.</b>',$this->aTurnoEnf['TURNO 1']['Aux'],$this->aTurnoEnf['TURNO 1']['Jefe']], 'a'=>'C', ],
							  [ 'w'=>$laAnchos, 'd'=>['<b>T.T.</b>',$this->aTurnoEnf['TURNO 2']['Aux'],$this->aTurnoEnf['TURNO 2']['Jefe']], 'a'=>'C', ],
							  [ 'w'=>$laAnchos, 'd'=>['<b>T.N.</b>',$this->aTurnoEnf['TURNO 3']['Aux'],$this->aTurnoEnf['TURNO 3']['Jefe']], 'a'=>'C', ] ],
						];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function fnFilasTabla($taData, $tcTitulo, $tbEsGlasgow=false)
	{
		$laWPF = [35];
		$laAPF = ['L'];
		if ($tbEsGlasgow) {
			$laW = [55,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4];
			$laA = ['L','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'];
		} else {
			$laW = [59,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4,4];
			$laA = ['L','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C','C'];
		}
		$laF = [count($taData)];
		$laContenidoTbl = [];
		foreach($taData as $lnKey=>$laData){
			$laContenidoTbl[] = $lnKey==0 ?
					['w'=>array_merge($laWPF,$laW), 'a'=>array_merge($laAPF,$laA), 'f'=>$laF, 'd'=>array_merge([$tcTitulo],$laData)] :
					['w'=>$laW, 'a'=>$laA, 'd'=>$laData];
		}
		return $laContenidoTbl;
	}

	private function fnOrganizarDatos() {

		if(is_array($this->aControlNeu)){

			foreach($this->aControlNeu as $laControl) {
				
				$lnHoraD = intval($laControl['HORAD'] / 10000) ;
				$lnHoraD = $lnHoraD==0?'24_':$lnHoraD.'_';
				 
				$this->aTotales[$lnHoraD] = intval($laControl['ESCALA']);

				// Dato Apertura Ocular
				$lcDato = substr($laControl['DESC2'],17,1);
				$this->aOcular[$lcDato][$lnHoraD]='X';

				// Dato Respuesta Verbal
				$lcDato = substr($laControl['DESC2'],31,1);
				$this->aVerbal[$lcDato][$lnHoraD]='X';

				// Dato Respuesta Motora
				$lcDato = substr($laControl['DESC2'],45,1);
				$this->aMotora[$lcDato][$lnHoraD]='X';

				// Dato Nivel de conciencia
				$lcDato = substr($laControl['DESC2'],59,1);
				$this->aNivelC[$lcDato][$lnHoraD]='X';

				// Dato de reflejos
				if (substr($laControl['DESC2'],71,1)=='1'){
					$this->aReflejos[0][$lnHoraD]='X';
				}
				if (substr($laControl['DESC2'],73,1)=='1'){
					$this->aReflejos[1][$lnHoraD]='X';
				}
				if (substr($laControl['DESC2'],75,1)=='1'){
					$this->aReflejos[2][$lnHoraD]='X';
				}

				// Dato Patron Respiratorio
				$lcDato = substr($laControl['DESC2'],89,1);
				$this->aPatronR[$lcDato][$lnHoraD]='X';

				// Dato Crisis Convulsiva
				$lcDato = substr($laControl['DESC2'],110,1);
				$this->aCrisisC[$lcDato][$lnHoraD]='X';

				// Dato Isocóricas
				if (substr($laControl['DESC1'],5,1)=='S'){
					$this->aPupilas[0][$lnHoraD]='X';
				}

				// Dato Anisocóricas
				if (substr($laControl['DESC1'],12,1)=='S'){
					$this->aPupilas[1][$lnHoraD]='X';
				}

				// Dato Miótica Derecha
				if (substr($laControl['DESC1'],18,1)=='1'){
					$this->aPupilas[2][$lnHoraD]='X';
				}

				// Dato Miótica Izquierda
				if (substr($laControl['DESC1'],24,1)=='1'){
					$this->aPupilas[3][$lnHoraD]='X';
				}

				// Dato Midiátrica Derecha
				if (substr($laControl['DESC1'],30,1)=='1'){
					$this->aPupilas[4][$lnHoraD]='X';
				}

				// Dato Midiátrica Izquierda
				if (substr($laControl['DESC1'],36,1)=='1'){
					$this->aPupilas[5][$lnHoraD]='X';
				}

			}

		}
	
	}

	function fnActualizaTurno(){

		foreach($this->aControlNeu as $lnKey=>$laControl) {

			$lnDia = intval(date('N',strtotime($laControl['FECHAD'])));
			$lnHora = intval($laControl['HORAD']);
			$lcTurno = '';

			foreach($this->aTurno as $laTurno) {
				
				if($lnHora<70000 || $lnHora>=190000 ){

					if($laControl['TIPO']=='9'){
						$this->aTurnoEnf['TURNO 3']['Aux']= $laControl['USUARIO'];
					}

					if($laControl['TIPO']=='91'){
						$this->aTurnoEnf['TURNO 3']['Jefe']= $laControl['USUARIO'];
					}
					break ;

				}

				if ($laTurno['Dia']==$lnDia && $lnHora>=$laTurno['HoraIni'] && $lnHora<$laTurno['HoraFin']){

					$lcTurno = $laTurno['Nombre'];

					if($lcTurno=='TURNO 4' && $lnHora >= 70000 && $lnHora < 130000 ){
						$lcTurno='TURNO 1';
					}

					if($lcTurno=='TURNO 4' && $lnHora >= 130000 && $lnHora < 190000 ){
						$lcTurno='TURNO 2';
					}

					if($laControl['TIPO']=='9'){
						$this->aTurnoEnf[$lcTurno]['Aux']= $laControl['USUARIO'];
					}

					if($laControl['TIPO']=='91'){
						$this->aTurnoEnf[$lcTurno]['Jefe']= $laControl['USUARIO'];
					}
					break;

				}

			}

		}

	}
		
}
