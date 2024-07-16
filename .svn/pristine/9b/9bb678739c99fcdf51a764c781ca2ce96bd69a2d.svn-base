<?php
namespace NUCLEO;

require_once ('class.Diagnostico.php');
use NUCLEO\Diagnostico;

class Doc_Rayosx
{
	protected $oDb;
	protected $cTipoDocumento='';
	protected $cPrueba='';
	protected $cTipoReporte='RAD002';
	protected $nOpcion=0;			//radopt
	protected $nOpcion2=0;			//radsub
	protected $cNombreMedicoSolicita='';
	protected $aDocumento = [];
	protected $aDatos = [];
	protected $aDatos2 = [];
	protected $aReporte = [
					'cTitulo' => 'RADIOLOGIA E IMAGENES DIAGNOSTICAS',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true],
				];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	 //Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->cTipoDocumento = $taData['cTipoProgr'];
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	 private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();

		$laRayosx = $this->oDb
			->select('INDRAD, CNLRAD, DESRAD, USRRAD, PGMRAD, FECRAD, HORRAD')
			->from('RADIOG')
			->where([
				'NRORAD'=>$taData['nIngreso'],
				'CONRAD'=>$taData['nConsecCita'],
				'SUBRAD'=>$taData['cCUP'],
			])
			->getAll('array');

		foreach($laRayosx as $laData) {
			if ($laData['INDRAD']==70) {
				switch (true) {
					// Resultado
					case ($laData['CNLRAD']>0 AND (($laData['CNLRAD']<101 AND $this->cTipoDocumento=='RAD001') OR ($laData['CNLRAD']<51 AND $this->cTipoDocumento=='RAD002'))):
						$laDocumento['cResultadox'] .= $laData['DESRAD'];
						break;

					case ($laData['CNLRAD']>50 AND $laData['CNLRAD']<101) AND $this->cTipoDocumento=='RAD002':
						$laDocumento['cResultado2'] .= $laData['DESRAD'];
						break;

					case $laData['CNLRAD']==101:
						$laDocumento['cRegistro1'] .= trim(substr($laData['DESRAD'],0,13));
						$laDocumento['cRegistro2'] .= trim(substr($laData['DESRAD'],14,13));
						$laDocumento['cRegistro3'] .= trim(substr($laData['DESRAD'],28,13));
						$laDocumento['cDiagnosticoPrincipal'] .= trim(substr($laData['DESRAD'],42,4));
						$laDocumento['cCodigoTraerImagen'] .= trim(substr($laData['DESRAD'],47,2));
						break;

					case $laData['CNLRAD']==103:
						$laDocumento['cMedicoSolicita'] .= $laData['DESRAD'];
						break;

					case $laData['CNLRAD']>=201 AND $laData['CNLRAD']<=300:
						$laDocumento['cConclusion'] .= $laData['DESRAD'];
						break;
				}
			}

			if (in_array($laData['INDRAD'],[71,72,73])) {
				$this->nOpcion = $laData['INDRAD'] - 70;
				switch	($laData['CNLRAD']) {
				 	case 1:
						for ($lnI = 1; $lnI<=5; $lnI++) {
							$this->aDatos[2][$lnI] = trim(substr($laData['DESRAD'], 15*$lnI-10, 10));
						}
						break;

					case 2:
						for ($lnI = 1; $lnI<=4; $lnI++) {
							$this->aDatos[2][$lnI+5] = trim(substr($laData['DESRAD'], 15*$lnI-10, 10));
						}
						break;

					case 3:
						for ($lnI = 1; $lnI<=9; $lnI++) {
							$this->aDatos[4][$lnI] = intval(substr($laData['DESRAD'], 10*$lnI-5, 10));
						}
						break;
					case 5:
						for ($lnI = 1; $lnI<=5; $lnI++) {
							$this->aDatos[1][$lnI] = trim(substr($laData['DESRAD'], 15*$lnI-10, 10));
						}
						break;

					case 6:
						for ($lnI = 1; $lnI<=4; $lnI++) {
							$this->aDatos[1][$lnI+5] = trim(substr($laData['DESRAD'], 15*$lnI-10, 10));
						}
						break;

					case 7:
						for ($lnI = 1; $lnI<=9; $lnI++) {
							$this->aDatos[3][$lnI] = intval(substr($laData['DESRAD'], 10*$lnI-5, 10));
						}
						break;
				}
			}

			if (in_array($laData['INDRAD'],[74])) {
				$this->nOpcion = $laData['INDRAD'] - 70;

				switch	($laData['CNLRAD']) {
					case $laData['CNLRAD']>=1 and $laData['CNLRAD']<=9:
						$this->aDatos[5][$laData['CNLRAD']] = trim(substr($laData['DESRAD'],  5, 100));
						break;

					case $laData['CNLRAD']>=11 and $laData['CNLRAD']<=19:
						$this->aDatos[6][$laData['CNLRAD']-10] = trim(substr($laData['DESRAD'],  5, 100));
						break;

					case $laData['CNLRAD']>=21 and $laData['CNLRAD']<=29:
						$this->aDatos[7][$laData['CNLRAD']-20] = trim(substr($laData['DESRAD'],  5, 100));
						break;

					case $laData['CNLRAD']>=31 and $laData['CNLRAD']<=39:
						$this->aDatos[8][$laData['CNLRAD']-30] = trim(substr($laData['DESRAD'],  5, 100));
						break;
				}
			}

			if (in_array($laData['INDRAD'],[75])) {
				$this->nOpcion = $laData['INDRAD'] - 70;

				for ($lnI = 1; $lnI<=5; $lnI++) {
						$this->aDatos2[$laData['CNLRAD']][$lnI] = substr($laData['DESRAD'], 15*$lnI-10, 10);
				}
			}
		}
		$laDocumento['cResultadox'] = trim($laDocumento['cResultadox']);
		$laDocumento['cConclusion'] = trim($laDocumento['cConclusion']);
		$laDocumento['cRegistro1'] = trim($laDocumento['cRegistro1']);
		$laDocumento['cDiagnosticoPrincipal'] = trim($laDocumento['cDiagnosticoPrincipal']);
		$laDocumento['cMedicoSolicita'] = trim($laDocumento['cMedicoSolicita']);
		$laDocumento['cCodigoTraerImagen'] = trim($laDocumento['cCodigoTraerImagen']);
		$this->aDocumento = $laDocumento;

		//	MEDICO SOLICITA
		if (!empty(trim($this->aDocumento['cMedicoSolicita']))){
			if (is_numeric($this->aDocumento['cMedicoSolicita'])) {
				$laMedicoOrdena = $this->oDb->select('TRIM(NNOMED)||\' \'||TRIM(NOMMED) NOMBRE_MEDICO')->from('RIARGMN')->where(['REGMED'=>$laDocumento['cMedicoSolicita'],])->get();
				$this->cNombreMedicoSolicita = 'Solicita  : Dr. ' .trim($laMedicoOrdena->NOMBRE_MEDICO ?? '');
			}else{
				$this->cNombreMedicoSolicita='Solicita  : Dr. ' .$this->aDocumento['cMedicoSolicita'];
			}
		}

		//	DIAGNOSTICO
		$loDiagnostico = new Diagnostico(trim($laDocumento['cDiagnosticoPrincipal']),$taData['oCitaProc']->nFechaRealiza);
		$this->aDiagnostico = $loDiagnostico->getTexto();

		if (!empty($this->aDocumento['cCodigoTraerImagen'])) {

			if($this->cTipoDocumento=='RAD001'){
				$laOpcionCodres = $this->oDb->select('OPTRES AS OPTTAR')->from('CODRES')->where(['CODRES'=>'601','CODRES'=>$laDocumento['cCodigoTraerImagen'],])->get();
				$this->nOpcion = $laOpcionCodres->OPTTAR ?? 0;
			}
 			if($this->cTipoDocumento=='RAD002'){
				$laOpcionCodres = $this->oDb->select('OPTRES AS OPTTAR')->from('CODRES')->where(['CODRES'=>'602','CODRES'=>$laDocumento['cCodigoTraerImagen'],'CUPRES'=>$taData['cCUP'],])->get();
				$this->nOpcion = $laOpcionCodres->OPTTAR ?? 0;
			}
		}
		if($this->nOpcion>9) {
			$this->nOpcion2 = intval(substr(trim((string)$this->nOpcion), 1, 1));
			$this->nOpcion = intval(substr(trim((string)$this->nOpcion), 0, 1));
		}
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;

		if($taData['oIngrPaciente']->cVia<>'02'){
			$lcTitulo = 'INTERPRETE SU RESULTADO O CONDUCTA A SEGUIR EN LA HOJA DE EVOLUCION';
		}else{
			$lcTitulo = ' ';
		}

		// Encabezado adicional
		$laTr['cTxtLuegoDeCup'] =
			($this->cNombreMedicoSolicita);
		;

		if($this->nOpcion>0 and $this->nOpcion<4){
			$this->cTipoReporte = 'RAD002A';
		}

		if($this->nOpcion==4 and ($this->nOpcion2==1 OR $this->nOpcion2==2)){
			$this->cTipoReporte = 'RAD002B';
		}

		if($this->nOpcion==4 and $this->nOpcion2==0){
			$this->cTipoReporte = 'RAD002C';
		}

		if($this->nOpcion==5){
			$this->cTipoReporte = 'RAD002D';
		}

		if($this->cTipoReporte == 'RAD002'){
			$laTr['aCuerpo'][] = ['titulo1',	$lcTitulo];

			if (!empty(trim($this->aDiagnostico))){
				$laTr['aCuerpo'][] = ['titulo1', 'DIAGNOSTICO PRINCIPAL'];
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cDiagnosticoPrincipal'] .' ' .trim($this->aDiagnostico)];
			}
		}

		if($this->cTipoReporte == 'RAD002A'){
			$laTr['aCuerpo'][] = ['titulo1', ' '];
			$laTit = [
				1=>'FEMORAL COMUN',
				2=>'PROXIMAL',
				3=>'MEDIA',
				4=>'DISTAL',
				5=>'FEMORAL PROFUNDA',
				6=>'POPLITEA',
				7=>'TIBIAL POSTERIOR',
				8=>'PERONEA',
				9=>'TIBIAL ANTERIOR',
			];
			$laMorf = explode(',', ' ,Monofásica,Bifásica,Trifásica,Ausente');

			$lnCount=count($laTr['aCuerpo']);
			$laTr['aCuerpo'][$lnCount] = ['tabla', [
					[
						'w'=>[60,60,60],
						'a'=>'C',
						'd'=>['', 'VEL. PICO SIST. (cm/sg)', 'MORFOLOGIA DE ONDA']
					],
					[
						'w'=>[60,30,30,30,30],
						'a'=>'C',
						'd'=>['SEGMENTO VASCULAR', 'DERECHA', 'IZQUIERDA' , 'DERECHA', 'IZQUIERDA']
					],
				],
				[],
			];
			$lbRowSpan=false;
			$lcTtlFemSup=($taData['format']=='PDF'? '': '<br>').'FEMORAL SUPERFICIAL';
			for ($lnI = 1; $lnI<10; $lnI++) {
				$laF=[];
				switch($lnI){
					case 2:
						$laW=[40,20,30,30,30,30];
						$laD=[$lcTtlFemSup,$laTit[$lnI]];
						$laF=[3,1,1,1,1,1];
						break;
					case 3: case 4:
						$laW=[20,30,30,30,30];
						$laD=[$laTit[$lnI]];
						break;
					default:
						$laW=[60,30,30,30,30];
						$laD=[$laTit[$lnI]];
				}
				$laTr['aCuerpo'][$lnCount][2][] = [
					'w'=>$laW, 'a'=>'C', 'f'=>$laF,
					'd'=>array_merge($laD, [
						$this->aDatos[1][$lnI],
						$this->aDatos[2][$lnI],
						$laMorf[$this->aDatos[3][$lnI]],
						$laMorf[$this->aDatos[4][$lnI]],
					]),
				];
			}
			//$laTr['aCuerpo'][] = ['texto9', ' '];
		}

		if($this->cTipoReporte == 'RAD002B'){
			$laTr['aCuerpo'][] = ['titulo1',	' '];
			$laTit = [
				1=>'FEMORAL COMUN',
				2=>'FEMORAL SUPER.',
				3=>'FEMORAL PROF.',
				4=>'POPLITEA',
				5=>'TIBIAL POSTERIOR',
				6=>'GEMELARES',
				7=>'SOLEALES',
				8=>'SAFENA INTERNA',
				9=>'SAFENA EXTERNA',
			];

			$lnCount=count($laTr['aCuerpo']);
			$laTr['aCuerpo'][$lnCount] = [
				'tabla',
				[
					[
						'w'=>[40,50,100],
						'a'=>'C',
						'd'=>['SEGMENTO VASCULAR', 'MORFOLOGIA (Calibre pared)' , 'CARACTERISTICAS DE FLUJO']
					],
				],
				[],
			];
			for ($lnI = 1; $lnI<=9; $lnI++) {
				$laTr['aCuerpo'][$lnCount][2][] = [
					'w'=>[40,50,100],
					'd'=>[
						$laTit[$lnI],
						$this->aDatos[7][$lnI],
						$this->aDatos[8][$lnI],
					],
				];
			}
			//$laTr['aCuerpo'][] = ['texto9', ' '];
		}

		if($this->cTipoReporte == 'RAD002C'){
			$laTr['aCuerpo'][] = ['titulo1', ' '];
			$laTit = [
				1=>'FEMORAL COMUN',
				2=>'FEMORAL SUPER.',
				3=>'FEMORAL PROF.',
				4=>'POPLITEA',
				5=>'TIBIAL POSTERIOR',
				6=>'GEMELARES',
				7=>'SOLEALES',
				8=>'SAFENA INTERNA',
				9=>'SAFENA EXTERNA',
			];

			for ($lnValor = 1; $lnValor<=2; $lnValor++) {

				$lcTituloRad002c = $lnValor==1 ? 'MIEMBRO INFERIOR IZQUIERDO': 'MIEMBRO INFERIOR DERECHO';

				$laTr['aCuerpo'][] = ['titulo1', $lcTituloRad002c];
				$lnCount=count($laTr['aCuerpo']);

				$laTr['aCuerpo'][$lnCount] = [
					'tabla',
					[
						[
							'w'=>[40,60,90],
							'a'=>'C',
							'd'=>['<br>SEGMENTO VASCULAR', 'MORFOLOGIA<br>Calibre - Pared' , '<br>CARACTERISTICAS DE FLUJO']
						],
					],
					[],
				];

				for ($lnI = 1; $lnI<=9; $lnI++) {
					$laTr['aCuerpo'][$lnCount][2][] = [
						'w'=>[40,60,90],
						'd'=>[
							$laTit[$lnI],
							$this->aDatos[5][$lnI],
							$this->aDatos[6][$lnI],
						],
					];
				}

				if($lnValor==1){
					$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cResultadox']];
				}else{
					$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cResultado2']];
				}
				$laTr['aCuerpo'][] = ['titulo1', ' '];
				$laTr['aCuerpo'][] = ['titulo1', 'Cordialmente, '];
				$laTr['aCuerpo'][] = ['firmas', [ ['prenombre'=>'Dr. ', 'registro' => $this->aDocumento['cRegistro1'],],], ];
				$laTr['aCuerpo'][] = ['firmas', [ ['prenombre'=>'Dr. ', 'registro' => $this->aDocumento['cRegistro2'],],], ];
				$laTr['aCuerpo'][] = ['firmas', [ ['prenombre'=>'Dr. ', 'registro' => $this->aDocumento['cRegistro3'],],], ];

				if($lnValor==1){
					$laTr['aCuerpo'][] = ['saltop',[]];
				}
			}
		}

		if($this->cTipoReporte == 'RAD002D'){
			if (!isset($laTr['aCuerpo'])) {
				$laTr['aCuerpo'][] = ['titulo1', ' '];
			}
			$laTit = [
				1 => 'CAROTIDA COMUN',
				2 => 'BULBO',
				3 => 'CAROTIDA INT.',
				4 => 'CAROTIDA EXT.',
				5 => 'VERTEBRAL',
			];
			$lnCount=count($laTr['aCuerpo']);
			$laTr['aCuerpo'][$lnCount] = [
				'tabla',
				[
					[
						'w'=>[30,20,25,25,25,22,20,20,],
						'a'=>'C',
						'd'=>[
							'SEGMENTO VASCULAR',
							'DIAMETRO<br>(mm)DER &nbsp; IZQ',
							'GROSOR<br>PARED (mm)<br><br>DER &nbsp; IZQ',
							'VEL. PICO<br>SISTOLICA cm/seg VPS<br>DER &nbsp; IZQ',
							'VEL. FIN<br>DIASTOLE cm/seg VPS<br>DER &nbsp; IZQ',
							'INDICE DE<br>RESISTENCIA<br><br>DER &nbsp; IZQ',
							'RELACION<br>VPS<br>CI/CC<br><br>DER &nbsp; IZQ',
							'RELACION<br>VFD<br>CI/CC<br><br>DER &nbsp; IZQ'
						]
					],
				],
				[],
			];
			for ($lnI = 1; $lnI<=5; $lnI++) {
				$laTemp = [
					'w'=>[30,10,10,12.5,12.5,12.5,12.5,12.5,12.5,10,12,10,10,10,10,],
					'a'=>'C',
					'd'=>[
						$laTit[$lnI],
					],
				];
				switch($lnI){
					case 2:
						for ($lnJ = 1; $lnJ<=14; $lnJ++) {
							$laTemp['d'][] = str_repeat ('', 10);
						}
						break;

					case 3:
						for ($lnJ = 1; $lnJ<=10; $lnJ++) {
							$laTemp['d'][] = trim($this->aDatos2[$lnJ][$lnI]);
						}
						for ($lnJ = 1; $lnJ<=4; $lnJ++) {
							$laTemp['d'][] = trim($this->aDatos2[11][$lnJ]);
						}
						break;

					default:
						for ($lnJ = 1; $lnJ<=10; $lnJ++) {
							$laTemp['d'][] = trim($this->aDatos2[$lnJ][$lnI]);
						}
						for ($lnJ = 11; $lnJ<=14; $lnJ++) {
							$laTemp['d'][] = str_repeat('', 4);
						}
				}
				$laTr['aCuerpo'][$lnCount][2][]=$laTemp;
			}
			//$laTr['aCuerpo'][] = ['texto9', ' '];
		}

		if($this->cTipoReporte <> 'RAD002C'){
			if(!empty(trim($this->aDocumento['cResultadox']))){
				$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cResultadox']];
			}
		}

		if (!empty(trim($this->aDocumento['cConclusion']))){
			$laTr['aCuerpo'][] = ['titulo1', 'CONCLUSION'];
			$laTr['aCuerpo'][] = ['texto9',	$this->aDocumento['cConclusion']];
		}

		if ($this->cTipoReporte <> 'RAD002C'){
			$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['cRegistro1'],'prenombre'=>'Dr. '],
				['registro' => $this->aDocumento['cRegistro2'],'prenombre'=>'Dr. '],
				['registro' => $this->aDocumento['cRegistro3'],'prenombre'=>'Dr. '],
			]];
		}

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'cResultadox'=> '',				//radres
			'cResultado2'=> '',				//radre2
			'cConclusion'=> '',				//radopi
			'cRegistro1'=> '',				//radrm1
			'cRegistro2'=> '',				//radrm2
			'cRegistro3'=> '',				//radrm3
			'cDiagnosticoPrincipal'=> '',	//raddia
			'cDescripcionDiagnostico'=> '',	//raddia
			'cCodigoTraerImagen'=> '',		//radtex
			'cMedicoSolicita'=> '',			//radmso
		];
	}
}