<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.FormulacionParametros.php';
require_once ('class.Ingreso.php');

use NUCLEO\Db;
use NUCLEO\FormulacionParametros;
use NUCLEO\Ingreso;

class AntecedentesConsulta
{
	protected $oDb;
	protected $aTitulos;
	public $aAntecedentes;

	public function __construct($tcTipoDoc='', $tcNumDoc=0)
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->iniciarOtros();
		$this->consultaAntecedentes($tcTipoDoc, $tcNumDoc);
	}

	public function consultaAntecedentes($tcTipoDoc='', $tcNumDoc=0)
	{
		$this->aAntecedentes=$laIndices=$laDiscapacidad=[];
		$lbExisteConciliacion = false;

		//Parámetros de Discapacidad
		$laParam = $this->oDb
		->select('TRIM(CL3TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
			->from('TABMAE')
			->where("TIPTMA='CATAINHC' AND CL1TMA='DISCAPAC' AND CL2TMA='01' AND ESTTMA = ' '")
			->orderBy('CL3TMA')
			->getAll('array');

			if (is_array($laParam)){
				foreach ($laParam as $laPar) {
					$laDiscapacidad [$laPar['CODIGO']] = $laPar['DESCRIP'];
				}
			}

		if(empty($tcTipoDoc) || empty($tcNumDoc)){return false;}

		$laTabla = $this->oDb->distinct()
			->select('CODAND, SANAND')
			->from('ANTPAD')
			->where(['TIDAND' => $tcTipoDoc,'NIDAND' => $tcNumDoc,])->where('INDAND<>0')
			->getAll('array');
		if(is_array($laTabla)){
			foreach($laTabla as $laFila){
				$laIndice = [];
				$laFila = array_map('intval', $laFila);
				$laIndice['DAPAPA'] = $this->aTitulos[$laFila['CODAND']] ?? '';
				if($laFila['CODAND']==17) $lbExisteConciliacion = true;

				// Descripción Diagnósticos
				if(in_array($laFila['CODAND'], [1,2,14])) {
					$laTemp = $this->oDb
						->select('TRIM(DESRIP) DESRIP')
						->from('RIACIEL1')
						->where("ENFRIP={$laFila['SANAND']}")
						->get('array');
					$laIndice['DASAPA'] = $laTemp['DESRIP'] ?? '';
				}

				// Descripción CUPS
				if(in_array($laFila['CODAND'], [3,6])) {
					$laTemp = $this->oDb
						->select('TRIM(DESCUP) DESCUP')
						->from('RIACUPL0')
						->where("CODCUP={$laFila['SANAND']}")
						->get('array');
					$laIndice['DASAPA'] = $laTemp['DESCUP'] ?? '';
				}

				// Descripción Antecedentes
				if(in_array($laFila['CODAND'], [4,8,10,13,15,16])) {
					$laTemp = $this->oDb
						->select('TRIM(DPRAND) DPRAND, TRIM(DESAND) DESAND')
						->from('ANTDESL02')
						->where("IN1AND={$laFila['CODAND']} AND IN2AND={$laFila['SANAND']}")
						->get('array');
					$laIndice['DAPAPA'] = $laTemp['DPRAND'] ?? '';
					$laIndice['DASAPA'] = $laTemp['DESAND'] ?? '';
				}

				// Conciliación de Medicamentos
				if($laFila['CODAND']==9) $laIndice['DASAPA'] = 'FARMACOLÓGICOS';

				// Descripción Antecedentes
				if($laFila['CODAND']==17) $laIndice['DASAPA'] = 'CONCILIACION DE MEDICAMENTOS';
				
				// Discapacidad
				if($laFila['SANAND']==20) {
					$laIndice['DASAPA'] = 'DISCAPACIDAD';
				}

				$laIndices[$laFila['CODAND']][$laFila['SANAND']] = $laIndice;
			}
			unset($laTemp, $laIndice);
		}

		$laTabla = $this->oDb
			->select('A.FDCAND, A.HDCAND, A.CODAND, A.SANAND, A.DESAND, A.INDAND, A.USRAND, A.LINAND')
			->select('IFNULL(UPPER(TRIM(M.NOMMED)||\' \'||TRIM(M.NNOMED)),\'\') MEDICO')
			->from('ANTPAD A')
			->leftJoin('RIARGMN4 M', 'M.USUARI=A.USRAND', null)
			->where(['A.TIDAND' => $tcTipoDoc,'A.NIDAND' => $tcNumDoc,])->where('A.INDAND<>0')
			->orderBy('A.FDCAND DESC, A.HDCAND DESC, A.CODAND, A.SANAND, A.INDAND, A.LINAND')
			->getAll('array');

		if(is_array($laTabla)){
			$lcIdAntes = '';
			$lnNum = 0;
			foreach($laTabla as $laFila){
				$laFila = array_map('trim', $laFila);
				$lcId = $laFila['FDCAND'].'-'.$laFila['HDCAND'].'-'.$laFila['CODAND'].'-'.$laFila['SANAND'].'-'.$laFila['INDAND'];
				if ($lcIdAntes==$lcId) {
					$this->aAntecedentes[$lnNum-1]['DESAPA'] .= $laFila['DESAND'];
				} else {

					if($laFila['SANAND']==20){
						$lcInformacion = explode("¤", trim($laFila['DESAND']));
						$lnReg = count($lcInformacion);
						$lcDescrip = '';
						if($lcInformacion[0]=='Si'){
							foreach($lcInformacion as $lnKey=>$lcValor){
								$lcDescrip .= trim($lcValor=='Si')?'Si: ':$laDiscapacidad[$lcValor] . ($lnKey==$lnReg-1?'.':' - ');
							}
					   	}else{
							$lcDescrip .= 'No.';
						}
						$laFila['DESAND']=$lcDescrip;						
					}

					$this->aAntecedentes[$lnNum++] = [
						'FCRAPA' => intval($laFila['FDCAND']),
						'HCRAPA' => intval($laFila['HDCAND']),
						'APRAPA' => intval($laFila['CODAND']),
						'ASEAPA' => intval($laFila['SANAND']),
						'DAPAPA' => $laIndices[$laFila['CODAND']][$laFila['SANAND']]['DAPAPA'],
						'DASAPA' => $laIndices[$laFila['CODAND']][$laFila['SANAND']]['DASAPA'],
						'DESAPA' => $laFila['DESAND'],
						'USUAPA' => $laFila['USRAND'],
						'NUSAPA' => $laFila['MEDICO'],
						'INDICE' => intval($laFila['INDAND']),
					];
					$lcIdAntes=$lcId;
				}
			}
		}
		unset($laIndices, $laTabla, $laFila);

		if($lbExisteConciliacion) {
			$laDat = $this->datosConciliacion();

			// Procesar texto de conciliación
			$lnFechaHora = 0;
			$lcTxtConMed = '';
			foreach($this->aAntecedentes as $lnIndice=>$laAntec){
				if($laAntec['APRAPA']==17){
					if($lnFechaHora !== $laAntec['FCRAPA'] * 1000000 + $laAntec['HCRAPA']) {
						$lnFechaHora = $laAntec['FCRAPA'] * 1000000 + $laAntec['HCRAPA'];
						$lnContador = 0;
					}
					switch($laAntec['INDICE']){
						// Medicamento, dosis, Frecuencia, etc
						case 1:
							$lnContador++;
							$lcTexto = trim($laAntec['DESAPA']);
							$lcCanDos = trim(substr($lcTexto, 30, 7));
							$lcPresen = trim(substr($lcTexto, 37, 8));
							$lcViaAdm = '0'.trim(substr($lcTexto, 45, 8));
							$lcFreMed = trim(substr($lcTexto, 53, 4));
							$lcTipFreM = trim(substr($lcTexto, 85, 10));

							// Fecha Última Dosis con Formato
							$lcFecUDo = trim(substr($lcTexto, 57, 8));
							if(!empty($lcFecUDo)){
								$lcFecUDo = substr($lcFecUDo, 0, 4) . '/' . substr($lcFecUDo, 4, 2) . '/' . substr($lcFecUDo, 6, 2);
								$lcHorUDo = trim(substr($lcTexto, 65, 8));
								$lcFecHoraUltDos = ';  se tomo la última dosis el ' . $lcFecUDo . ' a las ' . $lcHorUDo;
							} else {
								$lcFecHoraUltDos = '';
							}

							// Conducta Médica  0='No'  y  1='Si'
							$lcCondu = "_";
							$lcContin = trim(substr($lcTexto, 73, 2));
							$lcSuspen = trim(substr($lcTexto, 75, 2));
							$lcgnModi = trim(substr($lcTexto, 77, 2));
							$lcCondu = $lcContin == '1' ? 'CONTINUAR' : ($lcSuspen == '1' ? 'SUSPENDER' : ($lcgnModi == '1' ? 'MODIFICAR' : ''));
							$lcCondMed = '.  El Paciente debe ' . $lcCondu . ' el medicamento.';
							
							$lcTxtConMed.=$lnContador.') '.trim(substr($lcTexto, 0, 30)).':'	// Nombre
										. (floatval($lcCanDos)>0 ? ' '.$lcCanDos : '')			// Cantidad dosis
										. ' '.($laDat['Present'][$lcPresen] ?? '_')				// Presentación
										. (isset($laDat['ViasAdm'][$lcViaAdm]) ? ' tomado vía '.$laDat['ViasAdm'][$lcViaAdm] : '')	// Via de Administración
										. ' ' . $lcFreMed . ' ' . $lcTipFreM					// Frecuencia
										. $lcFecHoraUltDos . $lcCondMed;
							break;

						// Observacion de la Conducta Médica (Modifica / Suspende)
						case 2:
							if(strlen($laAntec['DESAPA'])>0){
								$lcTxtConMed .= "Observaciones de la Conducta Medica a seguir: {$laAntec['DESAPA']}";
							}
							break;

						// El paciente NO Consume Medicamentos
						case 3: case 4:
							$lcTxtConMed = 'El Paciente informa que no consume ningún medicamento.';
							break;
					}
					if(!empty($lcTxtConMed)){
						$this->aAntecedentes[$lnIndice]['DESAPA'] = $lcTxtConMed;
					}
					$lcTxtConMed = '';
				}
			}
		}
	}


	private function datosConciliacion()
	{
		$loObj = new FormulacionParametros();
		$loObj->obtenerUnidadesDosis();
		$loObj->obtenerViasAdmin();
		//$loObj->obtenerFrecuencias();

		$laData = [];
		foreach ($loObj->unidadesDosis() as $lcKey=>$laValue) {
			$laData['Present'][$lcKey] = $laValue['desc']; // $laValue['abrv'];
		}
		$laData['ViasAdm'] = $loObj->viasAdmin();

		return $laData;
	}

	public function ultimoAntecedente($tcTipoDoc='', $tcNumDoc=0, $tbUsarOp5Covid=true, $taDatos=[])
	{
		$laAntecedentes=[15=>[1=>'',3=>'',4=>'',6=>'',7=>'',8=>'',10=>'',12=>'',14=>'',18=>''], 16=>[], 4=>[]];
		$lcMostrarInfo = $this->oDb->ObtenerTabMae1('OP1TMA', 'HCPARAM', ['CL1TMA'=>'ANTEC', 'ESTTMA'=>''], null, '');

		if($lcMostrarInfo=='1'){
			if(count($taDatos)==0){
				$laUltimo = $this->oDb
					->select('CODANT, SANANT, DESANT, OP5ANT')
					->from('ANTPACL01')
					->where(['TIDANT'=>$tcTipoDoc,'NIDANT'=>$tcNumDoc, 'ESTANT'=>''])
					->in('CODANT', ['4','15','16'])
					->getAll('array');
			}else{
				$laUltimo = $taDatos;
			}

			if(count($laUltimo)>0){
				foreach($laUltimo as $laAntec){
					$lnKey1=intval($laAntec['CODANT']);
					$lnKey2=intval($laAntec['SANANT']);
					if($lnKey1==4 && $lnKey2==24 && $tbUsarOp5Covid){
						$laAntecedentes[$lnKey1][$lnKey2] = ($laAntecedentes[$lnKey1][$lnKey2]??'').trim($laAntec['OP5ANT']).'|';
					}else{
						$laAntecedentes[$lnKey1][$lnKey2] = ($laAntecedentes[$lnKey1][$lnKey2]??'').$laAntec['DESANT'];
					}
				}
				foreach($laAntecedentes as $lnKey=>$laAntecedente){
					$laAntecedentes[$lnKey] = array_map('trim',$laAntecedente);
				}
			}
		}

		return $laAntecedentes;
	}

	public function ultimoAntecedenteIngreso($tcTipoDoc, $tcNumDoc, $tnIngreso)
	{
		$laAntecedentes = [];

		$laUltimo = $this->oDb
			->select('CODAND, SANAND, DESAND, OP5AND')
			->from('ANTPAD')
			->where(['TIDAND'=>$tcTipoDoc,'NIDAND'=>$tcNumDoc, 'NINAND'=>$tnIngreso, 'ESTAND'=>''])
			->in('CODAND', ['4','15','16'])
			->in('PGMAND', ['HCPPAL','HCPPALWEB'])
			->getAll('array');
		if (is_array($laUltimo)){
			$laAntecedentes=[15=>[1=>'',3=>'',4=>'',6=>'',7=>'',8=>'',10=>'',12=>'',14=>'',18=>''], 16=>[], 4=>[]];
			foreach($laUltimo as $laAntec){
				$lnKey1=intval($laAntec['CODAND']);
				$lnKey2=intval($laAntec['SANAND']);
				$laAntecedentes[$lnKey1][$lnKey2] = ($laAntecedentes[$lnKey1][$lnKey2]??'').$laAntec['DESAND'];
			}
			foreach($laAntecedentes as $lnKey=>$laAntecedente){
				$laAntecedentes[$lnKey] = array_map('trim',$laAntecedente);
			}
		}

		return $laAntecedentes;
	}

	public function consultaActividadesFisicas($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laListaActividades = [];
		if(empty($tnFechaInicio)){ return $laListaActividades; }
		if(empty($tnFechaFinal)){ return $laListaActividades; }
		
		global $goDb;
		$laDatos = $goDb
			->select('A.TIDAND TIPOIDE, A.NIDAND NROIDE, A.NINAND INGRESO, A.FECAND FECHA')
			->select('(SELECT upper(trim(DE2TMA)) FROM TABMAE WHERE TIPTMA=\'ACTFIS\' AND CL1TMA=\'REAACT\' AND CL2TMA=A.OP1AND) REALIZA_ACTIVIDAD')
			->select('trim(A.DESAND) RESULTADO, trim(A.OP5AND) ACTIVIDADES')
			->from('ANTPADL03 A')
			->where('A.OP1AND', '<>', '')
			->between('A.FECAND', $tnFechaInicio, $tnFechaFinal)
			->getAll('array');
			
			foreach($laDatos as $laActividades){
				$lcActividades=$laActividadesRegistradas='';
				$lcRealizaActividad=$laActividades['REALIZA_ACTIVIDAD'];
				
				if ($lcRealizaActividad=='SI'){
					$lcActividades=$laActividades['ACTIVIDADES'];
					$laActividadesRegistradas = explode('¢', $lcActividades);
					
					foreach($laActividadesRegistradas as $laDatosActividad){
						$lcTipoActividadFisica=$lcDescActividadFisica=$lcTipoClaseActividad=$lcDescClaseActividad='';
						$lcFrecuenciaActividad=$lcTiempoActividad=$lcIntensidadActividad=$lcDescIntensidadActividad='';
						if ($laDatosActividad!=''){
							$lcTipoActividadFisica = explode('~', $laDatosActividad)[0];
							$lcTipoClaseActividad = explode('~', $laDatosActividad)[1];
							$lcDescActividadFisica = $goDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='TIPOACT' AND CL2TMA='$lcTipoActividadFisica'", null, '');
							$lcDescClaseActividad = $goDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='CLASACT' AND CL2TMA='$lcTipoClaseActividad'", null, '');
							$lcFrecuenciaActividad = explode('~', $laDatosActividad)[2];
							$lcTiempoActividad = explode('~', $laDatosActividad)[3];
							$lcIntensidadActividad = explode('~', $laDatosActividad)[4];
							$lcDescIntensidadActividad = $goDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='INTACT' AND CL2TMA='$lcIntensidadActividad'", null, '');
							
							$laListaActividades[] = [
								'TIPO IDENTIFICACION' => $laActividades['TIPOIDE'],
								'IDENTIFICACION' => $laActividades['NROIDE'],
								'INGRESO' => $laActividades['INGRESO'],
								'FECHA REGISTRO' => $laActividades['FECHA'],
								'REALIZA_ACTIVIDAD' => $laActividades['REALIZA_ACTIVIDAD'],
								'TIPO ACTIVIDAD' => $lcDescActividadFisica,
								'CLASE ACTIVIDAD' => $lcDescClaseActividad,
								'FRECUENCIA' => $lcFrecuenciaActividad,
								'TIEMPO' => $lcTiempoActividad,
								'INTENSIDAD' => $lcDescIntensidadActividad,
								'ACTIVIDAD/INACTIVIDAD' => $laActividades['RESULTADO'],
							];
						}	
					}	
				}else{
					$laListaActividades[] = [
								'TIPO IDENTIFICACION' => $laActividades['TIPOIDE'],
								'IDENTIFICACION' => $laActividades['NROIDE'],
								'INGRESO' => $laActividades['INGRESO'],
								'FECHA REGISTRO' => $laActividades['FECHA'],
								'REALIZA_ACTIVIDAD' => $laActividades['REALIZA_ACTIVIDAD'],
								'TIPO ACTIVIDAD' => '',
								'CLASE ACTIVIDAD' => '',
								'FRECUENCIA' => '',
								'TIEMPO' => '',
								'INTENSIDAD' => '',
								'ACTIVIDAD/INACTIVIDAD' => $laActividades['RESULTADO'],
							];
				}	
			}	
		return $laListaActividades;
	}

	public function consultaTitulos()
	{
		$laTitulos = [
			 4=>['ttl'=>'VACUNAS', 'ant'=>[]],
			15=>['ttl'=>'GENERALES', 'ant'=>[]],
			16=>['ttl'=>'PEDIATRICOS', 'ant'=>[]],
		];
		$laTmpTtl = $this->oDb
			->select('IN1AND,IN2AND,DPRAND,DESAND')
			->from('ANTDES')
			->in('IN1AND',[4,15,16])
			->orderBy('INT(IN1AND),INT(IN2AND)')
			->getAll('array');
		if($this->oDb->numRows()>0){
			foreach ($laTmpTtl as $laAntec) {
				$laAntec = array_map('trim',$laAntec);
				$laTitulos[$laAntec['IN1AND']]['ant'][$laAntec['IN2AND']] = $laAntec['DESAND'];
			}
		}
		return $laTitulos;
	}

	public function iniciarOtros()
	{
		$this->aTitulos = [
			'1' => 'PATOLÓGICOS',
			'2' => 'NEONATAL',
			'3' => 'TRANSFUSIONALES',
			'4' => '',
			'5' => '',
			'6' => 'QUIRÚRGICO',
			'7' => '',
			'8' => '',
			'9' => 'ANTECEDENTES',
			'10'=> '',
			'12'=> '',
			'13'=> '',
			'14'=> 'FAMILIARES CARDIACOS',
			'15'=> '',
			'16'=> '',
			'17'=> 'GENERALES',
			'18'=> '',
			'19'=> '',
			'20'=> '',
		];
	}
	
	public function consultaDiscapacidad($tnFechaInicio=0,$tnFechaFinal=0)
	{
		$laInformeDiscapacidad = [];
		$laDatos = $this->oDb->distinct()
			->select('A.NROING INGRESO, I.FEIING FECHAING, I.TIDING TIPO,  
			          I.NIDING DOCUMENTO, TRIM(P.NM1PAC) PNOMBRE, TRIM(P.NM2PAC) SNOMBRE, 
					  TRIM(P.AP1PAC) PAPELLIDO, TRIM(P.AP2PAC) SAPELLIDO, TRIM(P.SEXPAC) GENERO,
					  P.FNAPAC FNACIMIENTO, I.ENTING CODENTIDAD, TRIM(D.DSCCON) ENTIDAD,   
					  TRIM(P.DR1PAC) DIRECCION, TRIM(P.TELPAC) TELEFONO, TRIM(A.DESCRI) DESCRIP')
			->from('RIAHIS A')
			->innerJoin('RIAING I','I.NIGING = A.NROING')
			->innerJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->innerJoin('FACPLNC D', 'I.PLAING=D.PLNCON', null)
			->where('A.INDICE=10 AND A.SUBIND=15 AND A.CODIGO=20')
			->between('A.FECHIS', $tnFechaInicio, $tnFechaFinal)
			->orderBy('A.NROING')
			->getAll('array');

		if(is_array($laDatos)){
			if(count($laDatos)>0){

				foreach($laDatos as $laDiscapacidad){

					if(substr($laDiscapacidad['DESCRIP'], 0, 2)=='Si'){							
						// Edad
						$loIngreso=new Ingreso;
						$loIngreso->cargarIngreso($laDiscapacidad['INGRESO']);
						$laEdad = $loIngreso->aEdad;
						$lcEdad = $laEdad['y'].'A '. $laEdad['m'].'M '.$laEdad['d'].'D ';

						//Parámetros de Discapacidad
						$laParamDiscapacidad = [];
						$laParam = $this->oDb
						->select('TRIM(CL3TMA) CODIGO, TRIM(DE1TMA) DESCRIP')
							->from('TABMAE')
							->where("TIPTMA='CATAINHC' AND CL1TMA='DISCAPAC' AND CL2TMA='01' AND ESTTMA = ' '")
							->orderBy('CL3TMA')
							->getAll('array');

							if (is_array($laParam)){
								foreach ($laParam as $laPar) {
									$laParamDiscapacidad [$laPar['CODIGO']] = $laPar['DESCRIP'];
								}
							}

						$lcInformacion = explode("¤", $laDiscapacidad['DESCRIP']);
						$lnReg = count($lcInformacion);
						$lcDescrip = '';
						if($lcInformacion[0]=='Si'){
							foreach($lcInformacion as $lnKey=>$lcValor){
								$lcDescrip .= trim($lcValor=='Si')?'Si: ':$laParamDiscapacidad[$lcValor] . ($lnKey==$lnReg-1?'.':' - ');
							}
						}
						
						// Diagnostico
						$latemp = $this->oDb
							->select('TRIM(B.DESRIP) DESDX, TRIM(B.ENFRIP) CODDX')
							->from('RIAHIS A')
							->innerJoin('RIACIE B','A.SUBORG  = B.ENFRIP')
							->where(['A.NROING' => $laDiscapacidad['INGRESO']])
							->where('A.INDICE=25 AND A.SUBIND=1')
							->get('array');

						$laInformeDiscapacidad [] = [
							'INGRESO' => $laDiscapacidad['INGRESO'],
							'FECHA_ING' => $laDiscapacidad['FECHAING'],
							'TIPO' => $laDiscapacidad['TIPO'],
							'NUMERO_ID' => $laDiscapacidad['DOCUMENTO'],
							'NOMBRE_1' => $laDiscapacidad['PNOMBRE'],
							'NOMBRE_2' => $laDiscapacidad['SNOMBRE'],
							'APELLIDO_1' => $laDiscapacidad['PAPELLIDO'],
							'APELLIDO_2' => $laDiscapacidad['SAPELLIDO'],
							'GENERO' => $laDiscapacidad['GENERO'],
							'EDAD' => $lcEdad,
							'DX' => $latemp['CODDX'],
							'DESCRIPCION_DIAGNOSTICO' => $latemp['DESDX'],
							'DESCRIPCION_ENTIDAD' => $laDiscapacidad['ENTIDAD'],
							'DIRECCION' => $laDiscapacidad['DIRECCION'],
							'TELEFONO' => $laDiscapacidad['TELEFONO'],
							'TIPO_DISCAPACIDAD' => $lcDescrip,
						];
					}
				}	
			}
		}

		return $laInformeDiscapacidad;
	}
}