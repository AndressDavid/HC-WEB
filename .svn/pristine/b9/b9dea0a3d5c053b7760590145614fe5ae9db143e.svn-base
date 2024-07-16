<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Doc_Enf_Notas
{
	protected $oDb;
	protected $aResp = [];
	protected $aNota = [];
	protected $aOcular = [];
	protected $aVerbalA = [];
	protected $aVerbalP = [];
	protected $aMotoraA = [];
	protected $aMotoraP = [];
	protected $aNivelC = [];
	protected $aPatronR = [];
	protected $aCrisisC = [];
	protected $aCateter = [];
	protected $aTablaVP = [];
	protected $aParamPAE = [];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';
	protected $nEdad=0;
	protected $cTextoPandemia='';

	protected $aReporte = [
		'cTitulo' => "NOTAS DE ENFERMERIA",
		'lMostrarFechaRealizado' => false,
		'lMostrarViaCama' => true,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
		'cTxtLuegoDeCup' => '',
		'aCuerpo' => [],
		'aFirmas' => [],
	];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData){
		$this->nEdad = intval($taData['oIngrPaciente']->aEdad['y']);
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		$lcTabla = $lcFiltro = $lcOrder = $lcCampoJoin = $lcWhere = $lcCodigo = '';

		// Notas
		$this->aNota = [
			'1' =>['titulo'=>'','descripcion'=>''],
			'2' =>['titulo'=>'','descripcion'=>''],
			'3' =>['titulo'=>'','descripcion'=>''],
			'4' =>['titulo'=>'','descripcion'=>''],
			'5' =>['titulo'=>'','descripcion'=>''],
			'6' =>['titulo'=>'','descripcion'=>''],
			'7' =>['titulo'=>'','descripcion'=>''],
			'8' =>['titulo'=>'','descripcion'=>''],
			'9' =>['titulo'=>'','descripcion'=>''],
			'10' =>['titulo'=>'','descripcion'=>''],
			'11' =>['titulo'=>'','descripcion'=>''],
			'12' =>['titulo'=>'','descripcion'=>''],
			'13' =>['titulo'=>'','descripcion'=>''],
			'14' =>['titulo'=>'','descripcion'=>''],
			'15' =>['titulo'=>'','descripcion'=>''],
			'16' =>['titulo'=>'','descripcion'=>''],
			'17' =>['titulo'=>'','descripcion'=>''],
			'18' =>['titulo'=>'','descripcion'=>''],
			'19' =>['titulo'=>'','descripcion'=>''],
			'20' =>['titulo'=>'','descripcion'=>''],
			'21' =>['titulo'=>'','descripcion'=>''],
			'22' =>['titulo'=>'','descripcion'=>''],
			'23' =>['titulo'=>'','descripcion'=>''],
			'24' =>['titulo'=>'','descripcion'=>''],
			'25' =>['titulo'=>'','descripcion'=>''],
		];

		// Encabezado NOTAS
		$this->aNotasEnc = $this->oDb
			->select('PGMNOT, SCANOT, NCANOT, USRNOT, FECNOT, HORNOT, NTANOT')
			->from('NCSNOT')
			->where([
				'INGNOT'=>$taData['nIngreso'],
				'CONNOT'=>$taData['nConsecDoc'],
			])
			->get('array');

		// Apertura Ocular
		$this->aOcular = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>1,
			])
			->getAll('array');

		//  Respuesta Verbal Adultos
		$this->aVerbalA = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>2,
			])
			->getAll('array');

		//  Respuesta Verbal Pediatria
		$this->aVerbalP = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>8,
			])
			->getAll('array');

		//  Respuesta Motora Adultos
		$this->aMotoraA = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>3,
			])
			->getAll('array');

		//  Respuesta Motora Pediatria
		$this->aMotoraP = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>9,
			])
			->getAll('array');

		//  Nivel de Conciencia
		$this->aNivelC = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>4,
				'VARENF'=>5,
			])
			->getAll('array');

		// Patrón Respiratorio
		$this->aPatronR = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>20,
				'VARENF'=>7,
			])
			->getAll('array');

		// Crísis Convulsiva
		$this->aCrisisC = $this->oDb
			->select('DESENF, REFENF')
			->from('TABENF')
			->where([
				'TIPENF'=>4,
				'VARENF'=>4,
			])
			->getAll('array');

		// Parámetros actividad de la enfermera al ingreso del paciente
		$this->aIngresoEnf = $this->oDb
			->select('REFENF, DESENF')
			->from('TABENF')
			->where([
				'TIPENF'=>70,
				'VARENF'=>1,
			])
			->getAll('array');

		// Tipo de usuario
		$this->aTipoUsu = $this->oDb
			->select('TRIM(TABCOD) TABCOD, TRIM(TABDSC) TABDSC')
			->from('PRMTAB')
			->where(['TABTIP'=>'TUS'])
			->getAll('array');

		// Descripción preguntas Cateter vena periferico
		$this->aCateter = [
			'0101'=>'Dolor a la palpación',
			'0102'=>'Inflamación o edema en el sitio',
			'0103'=>'Ardor al paso de líquidos endovenosos',
			'0104'=>'Rubor o enrojecimiento',
			'0201'=>'Sitio de inserción visible',
			'0202'=>'Bordes adheridos a la piel en su totalidad',
			'0203'=>'Fixomull a lamina transparente',
			'0204'=>'Fecha de inserción, # catéter, turno',
		];

		// Consulta parámetros PAE
		$laTempPAE = $this->oDb
			->select('CL1TMA TIPO, CL2TMA CODIGO, DE2TMA DESCRIP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'DATOSPAE',
				'ESTTMA'=>'',
			])
			->orderBy('CL1TMA')
			->getAll('array');

		foreach($laTempPAE as $laReg){
			$this->aParamPAE[trim($laReg['TIPO']).trim($laReg['CODIGO'])] = trim($laReg['DESCRIP']);
		}

		unset($laTempPAE);

		// Consulta tablas de notas
		$laTablas = $this->oDb
			->select('CL2TMA TABLA, CL3TMA CODIGO, DE2TMA FILTRO, DE1TMA TITULO, OP5TMA ORDEN, OP2TMA CJOIN, OP3TMA ORDENIMP')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'NOTAENFW',
				'CL1TMA'=>'PHP',
				'ESTTMA'=>'',
			])
			->orderBy('OP3TMA')
			->getAll('array');

		foreach($laTablas as $laTablaC){
			$lcTabla = trim($laTablaC['TABLA']) ;
			$lcFiltro = trim($laTablaC['FILTRO']);
			$lcOrder = trim($laTablaC['ORDEN']);
			$lcCampoJoin = trim($laTablaC['CJOIN']);
			$lcCodigo = trim($laTablaC['CODIGO']);
			$aBuscar=['DATO1','DATO2','DATO3','DATO4'];
			$aReempl=[$taData['nIngreso'],$taData['nConsecDoc'],"JE","AU"];
			$lcWhere = str_replace($aBuscar, $aReempl, $lcFiltro);
			$lnOrdenImp = $laTablaC['ORDENIMP'];

			if($lcTabla=='ENCAMBIO'){
				$this->oDb
				->select('A.*, B.DESDES, B.UNIDES')
				->from('ENCAMBIO AS A')
				->leftJoin('INVDES AS B', 'A.INSCAM=B.REFDES', null)
				->where($lcWhere);
				if(!empty($lcOrder)){
					$this->oDb->orderBy($lcOrder);

				}
			}else{
				$this->oDb
				->from($lcTabla)
				->where($lcWhere);
				if(!empty($lcOrder)){
					$this->oDb->orderBy($lcOrder);

				}
			}

			$this->aResp=$this->oDb->getAll('array');

			if($lcCodigo=='22' || $lcCodigo=='23'){
				$this->aNota[$lnOrdenImp]['titulo'] = 'A la salida del paciente la ' ;
			}
			$this->aNota[$lnOrdenImp]['titulo'] .= $laTablaC['TITULO'] ;

			if(count($this->aResp)>0){
				$this->ConsultarTabla($lcCodigo, $lnOrdenImp);
			}
		}
	}

	private function prepararInforme($taData,$tlEpicrisis=false)
	{
		$lcTitulo = '';
		$lnAnchoPagina = 90;
		$lcSL = "\n"; //PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$lcHabita = (!empty($this->aNotasEnc['SCANOT']) && !empty($this->aNotasEnc['NCANOT']))? trim($this->aNotasEnc['SCANOT']) . ' - ' . trim($this->aNotasEnc['NCANOT']) : '' ;

		// Consecutivo NOTAS por día
		$laNotaDia = $this->oDb
			->select('CONCNS')
			->from('ENCONS')
			->where([
				'INGCNS'=>$taData['nIngreso'],
				'CRGCNS'=>$taData['nConsecDoc'],
			])
			->get('array');

		$lcTitulo = 'NOTAS DE ENFERMERIA ' . (is_array($laNotaDia)? $laNotaDia['CONCNS'] : '1') ;
		$this->aReporte['cTitulo'] = $lcTitulo ;

		/* Encabezado */
		$laTr['cTxtAntesDeCup'] = str_pad('Realizado : ' . AplicacionFunciones::formatFechaHora('fechahora12', $this->aNotasEnc['FECNOT'] . $this->aNotasEnc['HORNOT']) , $lnAnchoPagina/3, ' ') ;

		if(!empty(trim($this->cTextoPandemia))){
			$laTr['aCuerpo'][] = ['titulo1',''] ;
			$laTr['aCuerpo'][] = ['texto9', trim($this->cTextoPandemia)];
		}

		/* Cuerpo */
		foreach($this->aNota as $lcKey=>$laNota){
			if(is_string($laNota['descripcion'])){
				if(!empty(trim($laNota['descripcion']))){
					$laTr['aCuerpo'][] = ['titulo1', $laNota['titulo']] ;
					$laTr['aCuerpo'][] = ['texto9', $laNota['descripcion']];
					if($lcKey=='09' && count($this->aTablaVP)>0){

						$laAnchos = [5, 75, 10, 90];
						$laFilas = [];
						foreach($this->aTablaVP as $lcKey=>$laFila){
							$laFilas[substr($lcKey,0,2)][]=[
								'w'=>$laAnchos,
								'd'=>[$laFila['Numero']??'',$laFila['Pregunta']??'',$laFila['Respuesta']??'',$laFila['Observaciones']??'',],
								'a'=>['C','L','C','L',]
							];
						}
						$laTr['aCuerpo'][] = ['titulo2', '   Valoración Accesos Venosos Priféricos' . $lcSL . '   Sitio de Inserción de la vena'];
						$laTr['aCuerpo'][] = ['tabla',
							[ [ 'w'=>$laAnchos, 'd'=>['N°','ITEM','VALOR','OBSERVACIONES'], 'a'=>'C', ] ],
							$laFilas['01'],
						];

						$laTr['aCuerpo'][] = ['titulo2', '   Fijación del acceso venoso'];
						$laTr['aCuerpo'][] = ['tabla',
							[ [ 'w'=>$laAnchos, 'd'=>['N°','ITEM','VALOR','OBSERVACIONES'], 'a'=>'C', ] ],
							$laFilas['02'],
						];
					}
				}
			}elseif(is_array($laNota['descripcion'])){
				$laAnchos = [45,118,27];
				$laTr['aCuerpo'][] = ['titulo2', $laNota['titulo']];
				$laTr['aCuerpo'][]= ['saltol', 3];
				$laTr['aCuerpo'][] = ['tabla', [ [ 'w'=>$laAnchos, 'd'=>['FECHA - HORA','INSUMO','CANTIDAD'], 'a'=>'C', ] ],
					$laNota['descripcion'],
				];
			}
		}
		$laFirma=['usuario'=>$this->aNotasEnc['USRNOT']];
		$laTr['aCuerpo'][] = ['firmas', [ $laFirma, ] ];
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	function ConsultarTabla($tcCodigo, $tnOrdenI){
		$lcSL = "\n"; //PHP_EOL;
		$lcUsuarioR = '';
		switch ($tcCodigo){
			case '01' : // OBSERVACIONES
				if(count($this->aResp)>0){
					$lnInd = $this->aResp[0]['CONOBS'];
					foreach($this->aResp as $laDetalle){
						if($laDetalle['CNLOBS']<9000){
							$this->aNota[$tnOrdenI]['descripcion'] .= ($lnInd==$laDetalle['CONOBS'])? '': '.' . $lcSL;
							$this->aNota[$tnOrdenI]['descripcion'] .= trim($laDetalle['DESOBS']) ;
							$this->aNota[$tnOrdenI]['descripcion'] .= ($laDetalle['CNLOBS']==1)? $lcSL:'';
							if($lnInd !== $laDetalle['CONOBS']){
								$lnInd = $laDetalle['CONOBS'];
							}
						}else {
							if ($laDetalle['CNLOBS'] == 9000) {
								$this->cTextoPandemia = '';
							}
							$this->cTextoPandemia.= $laDetalle['DESOBS'];
						}
					}
				}
				break;

			case '02' :	// ESTADO DEL PACIENTE
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle = $this->fnUsuarioRegistro($laDetalle['USRNEU'],$laDetalle['FECNEU'],$laDetalle['HORNEU']) . $lcSL;
					$lcDetalle .= !empty($laDetalle['ECONEU'])? 'Estado Conciencia: ' . trim($laDetalle['ECONEU']) . ', ':'';
					$lcDetalle .= !empty($laDetalle['GCVNEU'])? 'Sueño: ' . trim($laDetalle['GCVNEU']) . ', ':'';
					$lcDetalle .= !empty($laDetalle['PESNEU'])? 'Peso: ' . trim($laDetalle['PESNEU']) . ' ' . trim($laDetalle['TIPPES']) . ', ':'';
					$lcDetalle .= !empty($laDetalle['TALNEU'])? 'Talla: ' . trim($laDetalle['TALNEU']) . ' cms. ':'';

					$lcTemp = '' ;
					if($laDetalle['RICNEU']<>'9'){
						$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'RICHMOND', ['CL1TMA'=>'01', 'OP3TMA'=>$laDetalle['RICNEU'], 'ESTTMA'=>' ']);
						$lcTemp = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));
					}

					$lcDetalle .= !empty($lcTemp)? 'Escala de RICHMOND: ' . trim($laDetalle['RICNEU']) . ' - ' . trim($lcTemp) . ', ':'' ;
					$lcDetalle .= !empty(trim($laDetalle['CAMNEU']))? 'Escala CAM-ICU: ' . trim($laDetalle['CAMNEU']) . '.':'' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSNEU']))? 'Observaciones: ' . trim($laDetalle['OBSNEU']):'' ;
					$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;

					$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				}
				break;

			case '03' : // VALORACION SISTEMA RESPIRATORIO
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle = !empty(trim($laDetalle['CPLRES']))? 'Color Piel: ' . trim($laDetalle['CPLRES']) . ', ':'' ;
					$lcDetalle .= !empty(trim($laDetalle['RSARES']))? 'Ruidos Sobreagregados: ' . trim($laDetalle['RSARES']) . ', ':'' ;
					$lcDetalle .= trim($laDetalle['CUIRES'])=='SI'? 'Se realiza cuidado de traqueostomia. ' . $lcSL :'' ;
					$lcDetalle .= !empty(trim($laDetalle['OXIRES']))? 'Oxigenoterapia: ' . trim($laDetalle['OXIRES']) . ', ':'' ;
					$lcSigno = (ctype_digit(substr(trim($laDetalle['PORRES']),-1,1))?' % ':' ');
					$lcDetalle .= !empty(trim($laDetalle['PORRES']))? 'Cantidad: ' . trim($laDetalle['PORRES']) . $lcSigno :'' ;
					$lcDetalle .= !empty(trim($laDetalle['TRPRES']))? 'Tipo Respiración: ' . trim($laDetalle['TRPRES']) . '.':'' ;

					if (!empty(trim($lcDetalle))){
						$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
					}
				}
				break;

			case '04' : // SIGNOS VITALES
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= $this->fnUsuarioRegistro($laDetalle['USRSIG'],$laDetalle['FECSIG'],$laDetalle['HORSIG']) . $lcSL ;

					if($laDetalle['OT2SIG']!=='2'){
						$lcDetalle .= !empty(trim($laDetalle['TMPSIG']))? 'Temperatura: ' . $laDetalle['TMPSIG'] . '°C, ': '' ;
						$lcDetalle .= !empty(trim($laDetalle['FRCSIG']))? 'Frec.Cardiaca: ' . $laDetalle['FRCSIG'] . ' lat/min, ': '' ;
						$lcDetalle .= !empty(trim($laDetalle['FRRSIG']))? 'Frec.Respiratoria: ' . $laDetalle['FRRSIG'] . ' Res/min, ': '' ;
						$lcDetalle .= !empty(trim($laDetalle['SATSIG']))? 'Saturación: ' . $laDetalle['SATSIG'] . '%, ': '' ;
						$lcDetalle .= !empty(trim($laDetalle['TASSIG']))? 'Tensión Arterial: ' . $laDetalle['TASSIG'] . '/' . $laDetalle['TADSIG'] . ' mmHg, ': '' ;
						$lcDetalle .= !empty(trim($laDetalle['PRASIG']))? 'Perimetro Abdominal: ' . $laDetalle['PRASIG'] . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['PCFSIG']))? 'Perimetro Cefálico: ' . $laDetalle['PCFSIG'] . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['OBSSIG']))? 'Observaciones: ' . trim($laDetalle['OBSSIG']) . '. ' : '' ;
					}
					else {
						$lcDetalle .= 'NO PRESENTA SIGNOS VITALES' ;
					}

					if (!empty(trim($lcDetalle))){
						$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
						$lcDetalle .= $lcSL ;
					}
				}
				if (!empty(trim($lcDetalle))){
					$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				}
				break;

			case '05' : // REGISTRO DE HERIDAS
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					if(mb_strtoupper(trim($laDetalle['CLAHER']))!=='NO TIENE'){
						$lcDetalle .= !empty(trim($laDetalle['CLAHER']))? 'Clase herida: ' . trim($laDetalle['CLAHER']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['COLHER']))? 'Localización: ' . trim($laDetalle['CLAHER']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['ASPHER']))? 'Aspecto: ' . trim($laDetalle['ASPHER']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['CURHER']))? 'Curación: ' . trim($laDetalle['CURHER']) . ', ' : '' ;
					}
					else{
						$lcDetalle .= 'Clase herida: No presenta. ' ;
					}

					$lcDetalle .= !empty(trim($laDetalle['OBSHER']))? 'Observaciones: ' . trim($laDetalle['OBSHER']) . '.' : '' ;
					if (!empty(trim($lcDetalle))){
						$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
						$lcDetalle .= $lcSL ;
					}
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '06' : // ESTADO DE LA PIEL
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['ESTPIE']))? 'Piel: ' . trim($laDetalle['ESTPIE']) . ' - ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSPIE']))? trim($laDetalle['OBSPIE']) : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OB2PIE']))? $lcSL . 'Observaciones: ' . trim($laDetalle['OB2PIE']) . '.' : '' ;
					$lcDetalle .= $lcSL ;
				}

				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '07' : // SEGURIDAD DEL PACIENTE
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					if(trim($laDetalle['AISSEG'])!=='Ninguno' && !empty(trim($laDetalle['AISSEG']))) {
						$lcDetalle .= 'El paciente tiene aislamiento: Tipo ' . trim($laDetalle['AISSEG']) . ', ' ;
					}
					else{
						$lcDetalle .= 'El paciente no tiene aislamiento, ' ;
					}
					$lcDetalle .= $laDetalle['BARSEG']=='SI'? ' Barandas Laterales Cama/Cuna,' : '' ;
					$lcDetalle .= $laDetalle['TIMSEG']=='SI'? ' Timbre a la Mano,' : '' ;
					$lcDetalle .= $laDetalle['COPSEG']=='SI'? ' Compañia Permanente,' : '' ;
					$lcDetalle .= $laDetalle['CAMSEG']=='SI'? ' Cama Altura Mínima,' : '' ;
					$lcDetalle .= $laDetalle['MIDSEG']=='SI'? ' Manilla de identificación' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['NMISEG']))? ' No ' . trim($laDetalle['NMISEG']) . ', ' : ' ' ;
					$lcDetalle .= $laDetalle['MPASEG']=='SI'? ' Manilla de Paciente Alérgico, ' : '' ;
					$lcDetalle .= $laDetalle['MSGSEG']=='SI'? ' Medidas de Sujeción, ' : '' ;
					$lcDetalle .= $laDetalle['PUNSEG']=='SI'? ' Riesgo por punción, ' : '' ;
					$lcDetalle .= $laDetalle['PAUSEG']=='SI'? ' Realiza pausa de seguridad, ' : '' ;
					$lcDetalle .= $laDetalle['RSASEG']=='SI'? ' Se diligencio Escala de Riesgo de Sangrado fue de: ' . trim($laDetalle['OTRSEG']) . ', ' : '' ;
					$lcDetalle .= $laDetalle['RFUSEG']=='SI'? ' Se diligencio Escala de Riesgo de Fuga fue de: ' . trim($laDetalle['FL2SEG']) . ', ' : '' ;
					$lcDetalle .= $laDetalle['ERGSEG']=='SI'? ' Se diligencio Escala de Riesgo de Caída fue de: ' . trim($laDetalle['CLNSEG']) . ', ' : '' ;
					$lcDetalle .= $laDetalle['EBRSEG']=='SI'? ' Se diligencio Escala de Riesgo de Ulcera (Bradem) fue de: ' . trim($laDetalle['VBRSEG']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSSEG']))? $lcSL . 'Observaciones: ' . trim($laDetalle['OBSSEG']) . '.' : '' ;
					$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
					$lcDetalle .= $lcSL ;
				}

				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '08' : // HIGIENE DEL PACIENTE
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim(substr($laDetalle['BANHIG'],0,13)))? 'Tipo de Baño: ' . trim(substr($laDetalle['BANHIG'],0,13)) . ', ' : '' ;
					$lcDetalle .= !empty(trim(substr($laDetalle['ORAHIG'],0,13)))? 'Higiene Oral: ' . trim(substr($laDetalle['ORAHIG'],0,13)) . ', ' : '' ;
					$lcDetalle .= strtoupper(trim($laDetalle['MANHIG']))=='SI'? ' Tiene medias antiambólicas, ' : 'No tiene medias antiambólicas, ' ;
					$lcDetalle .= strtoupper(trim($laDetalle['CMPHIG']))=='SI'? ' Se realizan cambios de Posición al paciente,' : ' ' ;
					$lcDetalle .= strtoupper(trim($laDetalle['LUBHIG']))=='SI'? ' Se realiza lubricación,' : ' ' ;
					$lcDetalle .= strtoupper(trim($laDetalle['MASHIG']))=='SI'? ' Se hacen masajes,' : ' ' ;
					$lcDetalle .= strtoupper(trim($laDetalle['PPTHIG']))=='SI'? ' Se colocan protectores de protuberancias,' : ' ' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSHIG']))? $lcSL . 'Observaciones: ' . trim($laDetalle['OBSHIG']) . '.' : '' ;
					$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
					$lcDetalle .= $lcSL ;
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '09' : // CATETER
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= $this->fnUsuarioRegistro($laDetalle['USRCAT'],$laDetalle['FECCAT'],$laDetalle['HORCAT']) . $lcSL ;

					if (strtoupper(trim($laDetalle['CATCAT']))!=='NO TIENE'){
						$lcDetalle .= 'Tipo de CATETER: ' . trim(substr($laDetalle['CATCAT'],0,32)) . ', ' ;
						$lcDetalle .= !empty(trim($laDetalle['LOCCAT']))? ' Localización: ' . trim($laDetalle['LOCCAT']) . ',' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['ASPCAT']))? ' Aspecto de la Punción: ' . trim($laDetalle['ASPCAT']) . ',' : '' ;
						$lcDetalle .= strtoupper(trim($laDetalle['CAMCAT']))=='SI'? ' Se Cambio cateter,' : ' No se realiza cambio,' ;
						if(trim($laDetalle['FCMCAT'])!== '0'){
							$lcDetalle .= ' Fecha Cambio: ' . AplicacionFunciones::formatFechaHora('fecha',$laDetalle['FCMCAT']) . ',';
						}
						$lcDetalle .= strtoupper(trim($laDetalle['CURCAT']))=='SI'? ' Se realiza curación,' : ' No se realiza curación,'  ;
						if(trim($laDetalle['FCRCAT'])!== '0'){
							$lcDetalle .= ' Fecha Curación: ' . AplicacionFunciones::formatFechaHora('fecha',$laDetalle['FCRCAT']) . ',';
						}

						if (!empty(trim($laDetalle['EVACAT']))){
							$this->aTablaVP = [];
							$laValor = explode('~',trim($laDetalle['EVACAT']));
							foreach($laValor as $laReg) {
								$laDatos = explode('¤',$laReg);
								$lnNumero = 0;
								foreach($laDatos as $laDato) {
									$lnNumero++;
									$lcCodReg = substr($laDato,0,4);
									$lcResp = substr($laDato,5,1);
									$lnTotal = substr($laDato,0,4)=='01'?$laDetalle['TO1CAT']:$laDetalle['TO2CAT'];
									$this->aTablaVP[$lcCodReg] = [
										'Numero'=> $lnNumero,
										'Pregunta' => $this->aCateter[$lcCodReg],
										'Respuesta'=>($lcResp=='1'?'SI':'NO'),
										'Observaciones'=>'',
										'Total'=>$lnTotal,
									];
								}
							}
						}

						if (!empty(trim($laDetalle['OB1CAT']))){
							$laValor = explode('~',trim($laDetalle['OB1CAT']));
							foreach($laValor as $laReg) {
								$laDatos = explode('¤',$laReg);
								foreach($laDatos as $laDato) {
									$lcCodReg = substr($laDato,0,4);
									$lcResp = substr($laDato,5);
									$this->aTablaVP[$lcCodReg]['Observaciones']=$lcResp;
								}
							}
						}
					}
					else{
						$lcDetalle .= 'CATETER: NO TIENE.' ;
					}
					$lcDetalle .= !empty(trim($laDetalle['OBSCAT']))? $lcSL . 'Observaciones: ' . trim($laDetalle['OBSCAT']) . '.' . $lcSL : $lcSL ;
				}

				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '10' : // REGISTRO CONTROL DEL DOLOR
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= $this->fnUsuarioRegistro($laDetalle['USRDOL'],$laDetalle['FECDOL'],$laDetalle['HORDOL']) . '.' . $lcSL ;
					if (!empty(trim($laDetalle['ESCDOL']))){
						$lcDetalle .= 'Escala de Dolor : ' . trim(substr($laDetalle['ESCDOL'],0,11)) . ', ' ;
						if(trim($laDetalle['ESCDOL'])!=='0/10' && strtoupper(trim($laDetalle['ESCDOL']))!=='NO VALORABLE'){

							$lcDetalle .= !empty(trim($laDetalle['MDODOL']))? ' Se manejo con: ' . trim($laDetalle['MDODOL']) . ',' : '' ;
							$lcDetalle .= !empty(trim($laDetalle['LOCDOL']))? ' Localización: ' . trim($laDetalle['LOCDOL']) . ',' : '' ;
							$lcDetalle .= !empty(trim($laDetalle['RTADOL']))? ' La respuesta fue: ' . trim($laDetalle['RTADOL']) . '.' : '' ;

						}
						$lcDetalle .= !empty(trim($laDetalle['OBSDOL']))? $lcSL . 'Observaciones: ' . trim($laDetalle['OBSDOL']) . '.' : '' ;
						$lcDetalle .= $lcSL ;
					}
				}
				if(!empty(trim($lcDetalle))){
					$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
					$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				}
				break;

			case '11' : // PACIENTE CON PROCEDIMIENTO DE HEMODINAMIA
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['PMDHEM']))? 'Pulso MID: ' . trim($laDetalle['PMDHEM']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['ASPHEM']))? 'Aspecto Sitio Punción: ' . trim($laDetalle['ASPHEM']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['IMVHEM']))? 'Inmovilización: ' . trim($laDetalle['IMVHEM']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['INTHEM']))? 'Introductores: ' . trim($laDetalle['INTHEM']) . '.' : '' ;
				}

				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '12' : // DRENES
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim(substr($laDetalle['DREDRE'],0,32)))? 'Tipo DREN: ' . trim(substr($laDetalle['DREDRE'],0,32)) . $lcSL : '' ;
					$lcDetalle .= !empty(trim($laDetalle['CARDRE']))? 'Localización/Caracteristicas: ' . trim($laDetalle['CARDRE']) . '.' . $lcSL : '' ;
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '13' : // VALORACION RENAL
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['DIUREN']))? 'Diuresis: ' . trim($laDetalle['DIUREN']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['ASPREN']))? 'Aspecto: ' . trim($laDetalle['ASPREN']) . '.' . $lcSL : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSREN']))? 'Observaciones: ' . trim($laDetalle['OBSREN']) . '.' . $lcSL : '' ;
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '14' : // VENOPUNCIONES
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					if (!empty(trim($laDetalle['JELVEN']))){
						$lcDetalle = 'Jelco: ' . trim($laDetalle['JELVEN']) ;
						$lcDetalle .= !empty(trim($laDetalle['SITVEN']))? 'Sitio: ' . trim($laDetalle['SITVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['EQUVEN']))? 'Equipo: ' . trim($laDetalle['EQUVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['BURVEN']))? 'Buretron: ' . trim($laDetalle['BURVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['EXTVEN']))? 'Extensor: ' . trim($laDetalle['EXTVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['LLAVEN']))? 'Llave:  ' . trim($laDetalle['LLAVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['VIAVEN']))? 'Via:  ' . trim($laDetalle['VIAVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['OTOVEN']))? 'Orotraqueal:  ' . trim($laDetalle['OTOVEN']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['COMVEN']))? 'Comisura:  ' . trim($laDetalle['COMVEN']) . '.' .  $lcSL : '' ;
						$lcDetalle .= !empty(trim($laDetalle['OBSVEN']))? 'Observaciones: ' . trim($laDetalle['OBSVEN']) . '.' .  $lcSL : '' ;
					}
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '15' : // DIETA
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['DIEGAS']))? 'Dieta: ' . trim($laDetalle['DIEGAS']) . ', ' : '' ;
					$lcDetalle .= strtoupper(trim($laDetalle['DIEGAS']))=='SI'? 'Vía: ' . trim($laDetalle['DIEGAS']) : '' ;
					$lcDetalle .= !empty(trim($laDetalle['TOLGAS']))? 'Tolerancia: ' . trim($laDetalle['TOLGAS']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['RPRGAS']))? 'Ruido Peristaltico: ' . trim($laDetalle['RPRGAS']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['RGSGAS']))? 'Residuo Gástrico: ' . trim($laDetalle['RGSGAS']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['FILGAS']))? 'Cambio de Inmovilización: ' . trim($laDetalle['FILGAS']) . '.' . $lcSL : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSGAS']))? 'Observación: ' . trim($laDetalle['OBSGAS']) . '.' : '' ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '16' : // ELIMINACION
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['EVCELI']))? 'Evacuación Intestinal: ' . trim($laDetalle['EVCELI']) . ', ' : '' ;
					$lcDetalle .= strtoupper(trim($laDetalle['EVCELI']))=='SI'? 'Aspecto Heces: ' . trim($laDetalle['AHEELI']) : '' ;
					$lcDetalle .= !empty(trim($laDetalle['EDEELI']))? 'Enema/Tipo: ' . trim($laDetalle['EDEELI']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['COLELI']))? 'Colostomia: ' . trim($laDetalle['COLELI']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['RESELI']))? 'Resultado: ' . trim($laDetalle['RESELI']) . '.' . $lcSL : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSELI']))? 'Observación: ' . trim($laDetalle['OBSELI']) . '.' : '' ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '17' : // VALORACION CARDIOVASCULAR
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					if (strtoupper(trim($laDetalle['MARCAR']))!=='NINGUNO'){
						if (!empty(trim($laDetalle['MARCAR']))){
							$lcDetalle = 'El paciente tiene ' ;
							$lcDetalle .= strtoupper(trim($laDetalle['MARCAR']))!=='CARDIODESFIBRILADOR'? 'marcapasos: ' . trim($laDetalle['MARCAR']) . ', ' : trim($laDetalle['MARCAR']) . ', ' ;
						}

						$lcDetalle .= !empty(trim($laDetalle['MODCAR']))? 'Modo: ' . trim($laDetalle['MODCAR']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['FRECAR']))? 'Frecuencia por Min: ' . trim($laDetalle['FRECAR']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['SALCAR']))? 'Salida: ' . trim($laDetalle['SALCAR']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['SENCAR']))? 'Sensibilidad: ' . trim($laDetalle['SENCAR']) . ', ' : '' ;
						$lcDetalle .= !empty(trim($laDetalle['IAVCAR']))? 'Intervalo AV: ' . trim($laDetalle['IAVCAR']) . ', ' : '' ;
					}
					else {
						$lcDetalle = 'El paciente no tiene marcapasos. ' ;
					}

					$lcDetalle .= !empty(trim($laDetalle['RTCCAR']))? 'Ritmo cardiaco: ' . trim($laDetalle['RTCCAR']) . ', ' : '' ;
					$lcDetalle .= strtoupper(trim($laDetalle['DTXCAR']))=='SI'? 'Presenta dolor toráxico, ' : 'No presenta dolor toráxico, ' ;

					if(!empty(trim($laDetalle['MEDCAR']))){
						$lcDetalle .= strtoupper(trim($laDetalle['MEDCAR']))=='SI'? 'Tiene medias antiambólicas, ' : 'No tiene medias antiambólicas, ' ;
					}

					$lcDetalle .= !empty(trim($laDetalle['PRHCAR']))? 'Procedimiento Hemodinamia: ' . trim($laDetalle['PRHCAR']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['ASPCAR']))? 'Aspecto Sitio Punción: ' . trim($laDetalle['ASPCAR']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['PMICAR']))? 'Pulsos MID: ' . trim($laDetalle['PMICAR']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['PMDCAR']))? 'MII: ' . trim($laDetalle['PMDCAR']) . ', ' . $lcSL : '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSCAR']))? 'Observación: ' . trim($laDetalle['OBSCAR']) . '.' . $lcSL : '' ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '18' : // CAMBIOS DE POSICION
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= !empty(trim($laDetalle['TACACT']))? 'Actividad del paciente: ' . trim($laDetalle['TACACT']) . ', ' : '' ;
					$lcDetalle .= strtoupper(trim($laDetalle['TOLACT']))=='NO'? 'El paciente No Tolera la actividad, ' : 'El paciente Tolera la actividad, ';
					$lcDetalle .= strtoupper(trim($laDetalle['CPSACT']))=='NO'? 'No se realiza cambio de posición asistido, ' : 'Se realiza cambio de posición asistido, ';
					$lcDetalle .= !empty(trim($laDetalle['HCPACT']))? 'Se realiza cambio de posición entre las horas ' . trim($laDetalle['HCPACT']) . ', ' : '' ;
					$lcDetalle .= !empty(trim($laDetalle['PPCACT']))? 'Posición del paciente: ' . trim($laDetalle['PPCACT']) . ', ' : '' ;

					if(!empty(trim($laDetalle['CUIACT']))){
						$lcDetalle .= 'Cuidado de Piel: ' ;
						$lcDetalle .= trim(substr($laDetalle['CUIACT'],8,1))!=='0'? 'Protección Protuberancias, ' : '' ;
						$lcDetalle .= trim(substr($laDetalle['CUIACT'],18,1))!=='0'? 'Masaje, ' : '' ;
						$lcDetalle .= trim(substr($laDetalle['CUIACT'],29,1))!=='0'? 'Lubricación, ': '' ;
						$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' . $lcSL : '' ;
					}

					$lcDetalle .= !empty(trim($laDetalle['ACEACT']))? 'Actividad Enfermería: ' . trim($laDetalle['ACEACT']) . '.' . $lcSL : '' ;
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '19' : // HOJA CONTROL NEUROLOGICO
				$lcDetalle = '' ;
				$lnValor = 0 ;
				foreach($this->aResp as $laDetalle){
					$lnGlasgow = 0;
					$lcDetalle .= $this->fnUsuarioRegistro($laDetalle['USRNEC'],$laDetalle['FECNEC'],$laDetalle['HORNEC']) . $lcSL ;
					$lnValor = intval(trim(substr($laDetalle['OBSNEC'],17,1))) ;
					$lnGlasgow += $lnValor ;

					$key = array_search($lnValor, array_column($this->aOcular, 'REFENF'));
					if (is_numeric($key)){
						$lcDetalle .= 'Apertura Ocular: ' . trim($this->aOcular[$key]['DESENF']) . ', ';
					}

					$lnValor = intval(trim(substr($laDetalle['OBSNEC'],31,1))) ;
					$lnGlasgow += $lnValor ;
					$lcDetalle .= 'Respuesta Verbal: ' ;

					if($this->nEdad < 2){
						$key = array_search($lnValor, array_column($this->aVerbalP, 'REFENF'));
						if (is_numeric($key)){
							$lcDetalle .= trim($this->aVerbalP[$key]['DESENF']) . ', ';
						}
					}
					else{

						$key = array_search($lnValor, array_column($this->aVerbalA, 'REFENF'));
						if (is_numeric($key)){
							$lcDetalle .= trim($this->aVerbalA[$key]['DESENF']) . ', ';
						}
					}

					$lnValor = intval(trim(substr($laDetalle['OBSNEC'],45,1))) ;
					$lnGlasgow += $lnValor ;
					$lcDetalle .= 'Respuesta Motora: ' ;

					if($this->nEdad < 2){
						$key = array_search($lnValor, array_column($this->aMotoraP, 'REFENF'));
						if (is_numeric($key)){
							$lcDetalle .= trim($this->aMotoraP[$key]['DESENF']) . ', ';
						}
					}
					else{
						$key = array_search($lnValor, array_column($this->aMotoraA, 'REFENF'));
						if (is_numeric($key)){
							$lcDetalle .= trim($this->aMotoraA[$key]['DESENF']) . ', ';
						}
					}

					$lcDetalle .= 'ESCALA DE GLASGOW: ' . strval($lnGlasgow) . ', ' ;

					$lnValor =  intval(trim(substr($laDetalle['OBSNEC'],59,1))) ;
					$key = array_search($lnValor, array_column($this->aNivelC, 'REFENF'));

					if (is_numeric($key)){
						$lcDetalle .= 'Nivel Conciencia: ' . trim($this->aNivelC[$key]['DESENF']) . ', ';
					}

					$lnValor =  intval(trim(substr($laDetalle['OBSNEC'],89,1))) ;
					$key = array_search($lnValor, array_column($this->aPatronR, 'REFENF'));

					if (is_numeric($key)){
						$lcDetalle .= 'Patrón Respiratorio: ' . trim($this->aPatronR[$key]['DESENF']) . ', ';
					}

					$lnValor =  intval(trim(substr($laDetalle['OBSNEC'],110,1))) ;
					$key = array_search($lnValor, array_column($this->aCrisisC, 'REFENF'));

					if (is_numeric($key)){
						$lcDetalle .= 'Crísis Convulsiva: ' . trim($this->aCrisisC[$key]['DESENF']) . ', ';
					}

					$lcDetalle1 = '';
					$lcDetalle1 .= intval(trim(substr($laDetalle['OBSNEC'],71,1)))==1? 'Corneano -' : '' ;
					$lcDetalle1 .= intval(trim(substr($laDetalle['OBSNEC'],73,1)))==1? 'Palpebral -' : '' ;
					$lcDetalle1 .= intval(trim(substr($laDetalle['OBSNEC'],75,1)))==1? 'Oculocefálico ' : '' ;

					$lcDetalle1 = !empty($lcDetalle1)? substr($lcDetalle1,0,strlen(trim($lcDetalle1))-2) . '.' : '' ;
					$lcDetalle .= !empty(trim($lcDetalle1))? 'Reflejos: ' . trim($lcDetalle1) : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],5,1))=='S'? ' Pupilas Isocóricas: SI, ' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],5,1))=='N'? ' Pupilas Isocóricas: NO, ' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],12,1))=='S'? 'Pupilas Anisocóricas: SI. ' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],12,1))=='N'? 'Pupilas Anisocóricas: NO. ' : '' ;

					$lcDetalle1 = '';
					$lcDetalle1 .= intval(trim(substr($laDetalle['PUPNEC'],18,1)))==1? 'Miótica Derecha ' : '' ;
					$lcDetalle1 .= intval(trim(substr($laDetalle['PUPNEC'],24,1)))==1? '- Miótica izquierda ' : '' ;
					$lcDetalle1 .= intval(trim(substr($laDetalle['PUPNEC'],30,1)))==1? '- Midiátrica Derecha ' : '' ;
					$lcDetalle1 .= intval(trim(substr($laDetalle['PUPNEC'],36,1)))==1? '- Midiátrica Izquierda. ' : '' ;

					$lcDetalle1 = !empty($lcDetalle1)? substr($lcDetalle1,0,strlen(trim($lcDetalle1))-1) . '. ' : '' ;
					$lcDetalle .= !empty(trim($lcDetalle1))? ' --> ' . trim($lcDetalle1) : '' ;

					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],47,1))=='S'? 'Reactivas.' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],47,1))=='N'? 'No Reactivas --> ' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],69,1))=='1'? 'Derecha, ' : '' ;
					$lcDetalle .= trim(substr($laDetalle['PUPNEC'],80,1))=='1'? 'Izquierda.' : '' ;
				}
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '20' : // CUIDADOS DEL PACIENTE
				$lcDetalle = $lcDetalle1 = '' ;
				$lnConsec =	$this->aResp[0]['CNTCUI'] ;
				foreach($this->aResp as $laDetalle){
					if($lnConsec ==	$laDetalle['CNTCUI']){
						$lcDetalle1 .= 	$laDetalle['OBSCUI'] ;
					}
					else{
						$lcDetalle .= $lcDetalle1 . $lcSL ;
						$lcDetalle1 = $laDetalle['OBSCUI'] ;
					}
				}

				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '21' : // ACTIVIDAD DE LA ENFERMERA AL INGRESO DEL PACIENTE
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= '' ;

					$lcDetalle .= !empty(trim($laDetalle['OBSLLE']))? trim($laDetalle['OBSLLE']) . ', ' : '' ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '22' : // ACTIVIDADES DE LA ENFERMERA JEFE AL EGRESO DEL PACIENTE
				$lcDetalle = '' ;
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSSAL']))? trim($laDetalle['OBSSAL']) . ', ' : '' ;
					$this->aNotasEnc['USRNOT'] = !empty(trim($this->aNotasEnc['USRNOT']))? trim($this->aNotasEnc['USRNOT']) : $laDetalle['USRSAL'] ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '23' : // ACTIVIDADES DE LA AUXILIAR ENFERMERA AL EGRESO DEL PACIENTE
				$lcDetalle = '';
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= '' ;
					$lcDetalle .= !empty(trim($laDetalle['OBSSAL']))? trim($laDetalle['OBSSAL']) . ', ' : '' ;
				}
				$lcDetalle = !empty($lcDetalle)? substr($lcDetalle,0,strlen(trim($lcDetalle))-1) . '.' : '' ;
				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;

			case '24' : // PAE
				$lcDetalle = '';
				foreach($this->aResp as $laDetalle){
					$lcDetalle .= $this->fnUsuarioRegistro($laDetalle['USUPAE'],$laDetalle['FECPAE'],$laDetalle['HORPAE']) . '.' . $lcSL ;
					$lcTemp = 'DIAGNOST'. $laDetalle['DIAPAE'];
					$lcDetalle .= 'DIAGNOSTICO: ' . $this->aParamPAE[$lcTemp] . $lcSL;
					// Criterios
					if(!empty(trim($laDetalle['CRIPAE']))){
						$lcDetalle .= 'CRITERIOS NOC: ' . $lcSL;
						$laTemp = explode('¤', $laDetalle['CRIPAE']);
						foreach($laTemp as $lnKey=>$laReg){
							if(!empty(trim($laTemp[$lnKey]))){
								$lcTemp = 'DATOSNOC'. trim($laTemp[$lnKey]);
								$lcDetalle .= '   - '. $this->aParamPAE[$lcTemp] . $lcSL;
							}
						}
					}

					// Intervenciones
					if(!empty(trim($laDetalle['INTPAE']))){
						$lcDetalle .= 'INTERVENCIONES NIC: ' . $lcSL;
						$laTemp = explode('¤', $laDetalle['INTPAE']);
						foreach($laTemp as $lnKey=>$laReg){
							if(!empty(trim($laTemp[$lnKey]))){
								$lcTemp = 'DATOSNIC'. trim($laTemp[$lnKey]);
								$lcDetalle .= '   - '. $this->aParamPAE[$lcTemp] . $lcSL;
							}
						}
					}

					// Actividades
					if(!empty(trim($laDetalle['PROPAE']))){
						$lcDetalle .= 'ACTIVIDADES: ' . $lcSL . trim($laDetalle['PROPAE']). $lcSL. $lcSL ;
					}
				}

				$this->aNota[$tnOrdenI]['descripcion'] .= trim($lcDetalle) . $lcSL ;
				break;
			case '25' : // USO O CAMBIO DE EQUIPOS

				$laDatos = [] ;
				$laAnchos = [45,118,27];
				foreach($this->aResp as $laDetalle){
					$laDatos[] = ['w'=>$laAnchos, 'd'=>[AplicacionFunciones::formatFechaHora('fechahora12', $laDetalle['FDICAM'] . $laDetalle['HDICAM']),$laDetalle['DESDES'], $laDetalle['CANCAM'] . ' ' . $laDetalle['UNIDES']
				   ],'a'=>['C','L','C',]];
				}

				$this->aNota[$tnOrdenI]['descripcion'] = $laDatos ;
				break;
		}
	}

	function fnUsuarioRegistro($tcUsuario='', $tnFecha=0, $tnHora=0)
	{
		if(!empty(trim($tcUsuario))){
			$lcUsuario = '' ;
			$laUsuario = $this->oDb
				->select('REGMED,TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE, TPMRGM')
				->tabla('RIARGMN')
				->where(['USUARI'=>$tcUsuario])
				->get("array");

			if(!is_array($laUsuario)){$laUsuario=[];}
			if(count($laUsuario)>0) {
				$lcTipoUsuario = $laUsuario['TPMRGM'] ;
				$key = array_search($lcTipoUsuario, array_column($this->aTipoUsu, 'TABCOD'));
				if (is_numeric($key)){
					$lcUsuario .= $this->aTipoUsu[$key]['TABDSC'] ;
				}
				$lcUsuario .= ': ' . $laUsuario['NOMBRE'] . ' ' . AplicacionFunciones::formatFechaHora('fechahora12', $tnFecha . $tnHora) ;
			}
		}

		return $lcUsuario ;
	}

	function fnAuditoriaSabana($tnIngreso=0, $tnFechaIni=0, $tnFechaFin=0)
	{
		$laInforme = $laCodigos = $laMedica = [];
		$lcTabla = '';

		$laTemporal = $this->oDb
				->select('INGBLT, CONBLT, COCBLT, CNLBLT, CODBLT, TRIM(CAMBLT) AS CAMPO, VN1BLT, VN2BLT, VC1BLT, VC2BLT, ACUBLT, CANBLT, FECBLT, HORBLT, PRGBLT, USUBLT, FDIBLT, HDIBLT, IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS ENFCAMBIO')
				->from('ENBALT AS A')
				->leftJoin('RIARGMN AS B', 'A.USUBLT=B.USUARI', null)
				->where(['A.INGBLT'=>$tnIngreso,])
				->between('A.FECBLT',$tnFechaIni,$tnFechaFin)
				->orderBy('CONBLT, COCBLT, CNLBLT, FDIBLT, HDIBLT')
				->getAll('array');	

		if(is_array($laTemporal)){
			
			if(count($laTemporal)>0){

				$lcSeccion = substr(trim($laTemporal[0]['PRGBLT']),1,2);
				$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'NOTASENF', ['CL1TMA'=>$lcSeccion, 'ESTTMA'=>' ']);
				$lcTemp = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

				if (!empty($lcTemp)){

					$laTemp = $this->oDb
						->select('CL3TMA CODIGO, DE2TMA DESCRIP, DE1TMA CAMPOS')
						->from('TABMAE')
						->where(['TIPTMA'=>$lcTemp, 'CL2TMA'=>'CORREGIR', 'ESTTMA'=>' '])
						->orderBy('CL3TMA')
						->getAll('array');	

					foreach ($laTemp as $laCodigo){
						$laCodigos[trim($laCodigo['CODIGO'])] = $laCodigo['DESCRIP'];
					}
					$laCodigos['99999'] = 'ACUMULADO';

					$laTemp = $this->oDb
						->select('REFDES, DESDES')
						->from('INVDES')
						->where("SUBSTR(REFDES,1,3)='017'")
						->orderBy('REFDES')
						->getAll('array');	

					foreach ($laTemp as $laCodigo){
						$laCodigos[trim($laCodigo['REFDES'])] = $laCodigo['DESDES'];
					}

					$laTemp = $this->oDb
						->select('REFENF, DESENF')
						->from('TABENF')
						->where([
							'TIPENF'=>74,
							'VARENF'=>1,
						])
						->orderBy('REFENF')
						->getAll('array');

					foreach ($laTemp as $laCodigo){
						$laCodigos['SA'.trim($laCodigo['REFENF'])] = $laCodigo['DESENF'];
					}

					$laTemp = $this->oDb
						->select('REFENF, DESENF')
						->from('TABENF')
						->where([
							'TIPENF'=>74,
							'VARENF'=>7,
						])
						->orderBy('REFENF')
						->getAll('array');

					foreach ($laTemp as $laCodigo){
						$laCodigos['TR'.trim($laCodigo['REFENF'])] = $laCodigo['DESENF'];
					}
				}

				foreach($laTemporal as $laDato){

					$lcDescrip = '';
					$lcTabla = explode('.',  $laDato['CAMPO']);

					if ($lcTabla[0]=='ENBALQ'){
						$laTemp = $this->oDb
							->select('LIQBAQ')
							->from('ENBALQ')
							->where(['INGBAQ'=>$tnIngreso, 'CONBAQ'=>$laDato['CONBLT'], 'CNLBAQ'=>$laDato['CODBLT']])
							->get('array');	

						$lcDescrip = trim($laTemp['LIQBAQ']??'');

					}else{
						$lcDescrip = $laCodigos[trim($laDato['CODBLT'])]??'';
					}

					$lcDescrip = empty(trim($lcDescrip))?$laCodigos[trim($laDato['CODBLT'])]??'':$lcDescrip;
					$lcValorInicial = !empty(trim($laDato['VN1BLT']))?$laDato['VN1BLT']:$laDato['VC1BLT'];
					$lcValorFinal = !empty(trim($laDato['VN2BLT']))?$laDato['VN2BLT']:$laDato['VC2BLT'];
					$laInforme[] = [
						'DESCRIPCION' => $lcDescrip,
						'FECHA DIGITADO' => AplicacionFunciones::formatFechaHora('fecha', intval($laDato['FDIBLT'])),
						'HORA DIGITADO' => AplicacionFunciones::formatFechaHora('hora', $laDato['HDIBLT']),
						'VALOR INICIAL' => str_replace(".000", '', $lcValorInicial),
						'VALOR MODIFICADO' => str_replace(".000", '', $lcValorFinal),
						'CANTIDAD TOTAL' => str_replace(".000", '', $laDato['CANBLT']),
						'AFECTA ACUMULADO' => ($laDato['ACUBLT']=='1'?'SI':'NO'),
						'FECHA REALIZADO' => AplicacionFunciones::formatFechaHora('fecha', intval($laDato['FECBLT'])),
						'HORA REALIZADO' => AplicacionFunciones::formatFechaHora('hora', $laDato['HORBLT']),
						'USUARIO REALIZA' => $laDato['ENFCAMBIO'],										
					];
				}
			}
		}
		return $laInforme;
	}
}
?>
