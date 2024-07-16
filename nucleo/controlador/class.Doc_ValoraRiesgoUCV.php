<?php
namespace NUCLEO;

class Doc_ValoraRiesgoUCV
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => "VALORACIÓN RIESGO EN UNIDAD DE CIRUGÍA CARDIOVASCULAR",
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => ['notas'=>true,'codproc'=>'NOTAUCV'],
				];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}

	private function consultarDatos($taData)
	{
		$laDocumento = $this->datosBlanco();

		$laCondiciones = ['INGVCV'=>$taData['nIngreso'], 'CONVCV'=>1];
		$laValRiesgoUCV = $this->oDb
				 ->from('HISUCVL01')
				 ->where($laCondiciones)
				 ->orderBy('CNLVCV')
				 ->getAll('array');
		if(is_Array($laValRiesgoUCV)){
			if(count($laValRiesgoUCV) > 0){
				foreach($laValRiesgoUCV as $Dato){
					switch(true){
						case $Dato['INDVCV']==1:
							$laDocumento['cTipoCirugia'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==2:
							$laDocumento['cTipoPatologia'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==3:
							$laDocumento['cListaAntecdentes'] = trim($Dato['DESVCV']);
							break;
						case $Dato['INDVCV']==4:
							$laDocumento['cAntecedentes'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==5:
							$laDocumento['cEcograficos'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==6:
							$laDocumento['lcListaFactores'] = trim($Dato['DESVCV']);
							break;
						case $Dato['INDVCV']==7:
							$laDocumento['cRadiografia'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==8:
							$laDocumento['cElectro'] .= $Dato['DESVCV'];
							break;
						case $Dato['INDVCV']==9:
							$laDocumento['nCreatinina'] = trim(mb_substr( $Dato['DESVCV'], 0, 10));
							$laDocumento['nHemoglobina'] = trim(mb_substr( $Dato['DESVCV'], 10, 10));
							$laDocumento['nGlicosilada'] = trim(mb_substr( $Dato['DESVCV'], 20, 10));
							$laDocumento['nPesoAjustado'] = $Dato['PESVCV'];
							$laDocumento['nPesoReal'] = $Dato['OP4VCV'];
							break;
						case $Dato['INDVCV']==10:
							$laDocumento['nEuroscore'] = trim(mb_substr( $Dato['DESVCV'], 0, 10));
							$laDocumento['nTalla'] = $Dato['PESVCV'];
							$laDocumento['cEuroscoreVal'] = trim($Dato['OP5VCV']);
							break;
						case $Dato['INDVCV']==11:
							$laDocumento['cPronostico'] = trim($Dato['DESVCV']);
							$laDocumento['nIMC'] = $Dato['PESVCV'];
							break;
						case $Dato['INDVCV']==1999:
							$laDocumento['cRegMed'] = $Dato['DESVCV'];
							$laDocumento['cRegMed'] = mb_substr($Dato['DESVCV'], strpos($Dato['DESVCV'], '-')+2, 13);
							$laDocumento['cDesEsp'] = trim(mb_substr($Dato['DESVCV'], strpos($Dato['DESVCV'], '-')+16, 13));
							$laDocumento['cUsuari'] = trim($Dato['USRVCV']);
							break;
						case $Dato['INDVCV']==2000:
							$laDocumento['cCodEsp'] = mb_substr($Dato['DESVCV'], 0, 3);
							$laDocumento['cSecCam'] = trim(mb_substr($Dato['DESVCV'], 6, 2));
							$laDocumento['cNroCam'] = trim(mb_substr($Dato['DESVCV'], 9, 4));
							break;
					}
				}
				$laDocumento['cTipoCirugia'] 	=   trim($laDocumento['cTipoCirugia']);
				$laDocumento['cTipoPatologia'] 	= trim($laDocumento['cTipoPatologia']);
				$laDocumento['cAntecedentes'] 	= trim($laDocumento['cAntecedentes']);
				$laDocumento['cEcograficos'] 	=   trim($laDocumento['cEcograficos']);
				$laDocumento['cRadiografia'] 	=   trim($laDocumento['cRadiografia']);
				$laDocumento['cElectro'] 		=     trim($laDocumento['cElectro']);
			}
		}


		$laCampos = ['0 AS SELECCION', 'substr(CL2TMA, 1, 2) AS CODIGO', 'substr(DE2TMA, 1, 150) AS DESCRIPCION'];
		$laCondiciones = ['TIPTMA'=>'UCIUCV', 'CL1TMA'=>'2'];
		$laFactoresUcv = $this->oDb
									 ->select($laCampos)
									 ->from('TABMAE')
									 ->where($laCondiciones)
									 ->orderBy('OP3TMA, INT(CL2TMA),  DE2TMA')
									 ->getAll('array');
		foreach($laFactoresUcv as $lnIndice=>$Dato){
			$laFactoresUcv[$lnIndice] = array_map('trim', $Dato);
		}
		if(is_Array($laFactoresUcv)){
			if(count($laFactoresUcv)>0){
				$laFactoresUcv = AplicacionFunciones::mapear($laFactoresUcv, 'CODIGO', 'DESCRIPCION');
				$laDocumento['laListaFactores'] = explode(',', trim($laDocumento['lcListaFactores']));
				$laFacRis = [];
				foreach($laDocumento['laListaFactores'] as $DatoCod){
					if(!empty(trim($DatoCod))){
						$laFacRis[$DatoCod] = $laFactoresUcv[trim($DatoCod)];
					}
				}
			}
		}
		$laDocumento['laFactoresRiesgo'] = $laFacRis; 
		foreach($laDocumento['laFactoresRiesgo'] as $DatoFactor){
			if($DatoFactor =='Ninguno'){
				$laDocumento['cRiesgoClasificacion'] = 'Bajo';
			}else{
				$lnCuentaRiesgos= count($laDocumento['laFactoresRiesgo']);
				$laCondiciones = ['TIPTMA'=> 'UCIUCV', 'CL1TMA'=>'4', 'CL2TMA'=>$lnCuentaRiesgos, 'ESTTMA'=>''];
				$laRiesgoClasificacion = $this->oDb
										->select('DE2TMA')
										->from('TABMAE')
										->where($laCondiciones)
										->get('array');
				$laDocumento['cRiesgoClasificacion'] = trim($laRiesgoClasificacion['DE2TMA']);
			}
		}
		$laCampos = ['0 AS SELECCION', 'substr(DE2TMA, 1, 25) AS DESC_PRONOSTICO', 'CL2TMA AS CODIGO_PRONOSTICO'];
		$laCondiciones = ['TIPTMA'=>'UCIUCV', 'CL1TMA'=>'1'];
		$laPronosticos = $this->oDb
									 ->select($laCampos)
									 ->from('TABMAE')
									 ->where($laCondiciones)
									 ->orderBy('OP3TMA, INT(CL2TMA),  DE2TMA')
									 ->getAll('array');
		foreach($laPronosticos as $lnClave=>$lcPronostico){
			$laPronosticos[$lnClave] = array_map('trim', $lcPronostico);
		}
		$laDocumento['lcPronostico'] = AplicacionFunciones::lookup($laPronosticos, 'DESC_PRONOSTICO', $laDocumento['cPronostico'], 'CODIGO_PRONOSTICO');
		$laCampos = ['substr(CL2TMA, 1, 2) AS CODIGO', 'substr(DE2TMA, 1, 150) AS DESCRIPCION'];
		$laCondiciones = ['TIPTMA'=>'UCIUCV', 'CL1TMA'=>'3'];
		$laAntecedentesUcv = $this->oDb
											 ->select($laCampos)
											 ->from('TABMAE')
											 ->where($laCondiciones)
											 ->orderBy('CL2TMA')
											 ->getAll('array');
		foreach ($laAntecedentesUcv as $lnIndice=>$laAntecedenteUcv){
			$laAntecedentesUcv[$lnIndice] = array_map('trim',$laAntecedenteUcv);
		}
		$laAntecedentesUcv = AplicacionFunciones::mapear($laAntecedentesUcv, 'CODIGO', 'DESCRIPCION');
		$laDocumento['lcListaAnteced'] = explode(',', trim($laDocumento['cListaAntecdentes']));
		$laAntec = [];
		foreach($laDocumento['lcListaAnteced'] as $DatoCod){
			if(!empty(trim($DatoCod))){
				$laAntec[$DatoCod] = $laAntecedentesUcv[trim($DatoCod)];
			}
		}
		sort($laAntec);
		foreach ($laAntec as $clave => $valor) {
			$laDocumento['laListaAnteced'] = $laAntec;
		}
			$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$lcDescrip = '';
		$lcTemp = '';
		$lcCod03='';

		if(!empty($this->aDocumento['cTipoCirugia'])){
			$laTr['aCuerpo'][] = ['titulo1', 'VALORACIÓN RIESGO EN UCV'];
			$laTr['aCuerpo'][] = ['titulo2', 'Tipo Cirugía:'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cTipoCirugia']];
		}
		if(!empty($this->aDocumento['cTipoPatologia'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Tipo de Patología:'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cTipoPatologia']];	
		}
		if(!empty($this->aDocumento['laListaAnteced'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Antecedentes:'];
			foreach($this->aDocumento['laListaAnteced'] as $lcAntecedente){
			$laTr['aCuerpo'][] = ['texto9', '* ' . $lcAntecedente];
			}
		}
		if(!empty($this->aDocumento['cAntecedentes'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Otros antecedentes:'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cAntecedentes']];
		}
		if(!empty($this->aDocumento['cEcograficos'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Datos ecocardiograficos:'];
			$laTr['aCuerpo'][] = ['texto9', $this->aDocumento['cEcograficos']];
		}
		if(!empty($this->aDocumento['laFactoresRiesgo'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Factores de riesgo:'];
			foreach($this->aDocumento['laFactoresRiesgo'] as $lcRiesgo){
				$laTr['aCuerpo'][] = ['texto9', '* ' . $lcRiesgo];
			}
		}
		if(!empty($this->aDocumento['cRiesgoClasificacion'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Riesgo:'];
			$laTr['aCuerpo'][] = ['texto9',  $this->aDocumento['cRiesgoClasificacion']];
		}
		if(!empty($this->aDocumento['cRadiografia'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Radiografía:'];
			$laTr['aCuerpo'][] = ['texto9',  $this->aDocumento['cRadiografia']];
		}
		if(!empty($this->aDocumento['cElectro'])){
			$laTr['aCuerpo'][] = ['titulo2', 'EKG:'];
			$laTr['aCuerpo'][] = ['texto9',  $this->aDocumento['cElectro']];
		}
		if(!empty($this->aDocumento['nCreatinina'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Laboratorios:'];
			$laTr['aCuerpo'][] = ['txthtml10',  'Depuración de creatinina (PRE): '. (($this->aDocumento['nCreatinina'])== 0 ?'NA': $this->aDocumento['nCreatinina']) . '&nbsp; &nbsp; &nbsp; &nbsp; '. 'Hemoglobina (PRE): ' . (($this->aDocumento['nHemoglobina'])== 0 ?'NA': $this->aDocumento['nHemoglobina'])];
			$laTr['aCuerpo'][] = ['txthtml10',  'Hemoglobina Glicosilada: '. (($this->aDocumento['nGlicosilada'])== 0 ?'NA': $this->aDocumento['nGlicosilada'])];
		}
		if(!empty($this->aDocumento['nPesoAjustado'])){
			$laTr['aCuerpo'][] = ['titulo2', 'PESO/TALLA/IMC'];
			if(empty($this->aDocumento['nPesoReal'])){
				$laTr['aCuerpo'][] = ['txthtml10',  'Peso Ajustado (Kg): '. $this->aDocumento['nPesoAjustado']. '&nbsp; &nbsp; &nbsp; &nbsp; '. 'Talla (Cms): ' . $this->aDocumento['nTalla'] . '&nbsp; &nbsp; &nbsp; &nbsp; ' . 'IMC: ' . $this->aDocumento['nIMC']];
			}else{
				$laTr['aCuerpo'][] = ['txthtml10',  'Peso Ajustado (Kg): '. $this->aDocumento['nPesoAjustado']. '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;'. 'Peso Real (Kg): ' . $this->aDocumento['nPesoReal']];
				$laTr['aCuerpo'][] = ['txthtml10',  'Talla (Cms): ' . $this->aDocumento['nTalla'] . '&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;' . 'IMC: ' . $this->aDocumento['nIMC']];
			}
		}
		if(!empty($this->aDocumento['nEuroscore'])){
			$laTr['aCuerpo'][] = ['titulo2', 'EUROSCORE II:'];
			$laTr['aCuerpo'][] = ['texto9',  $this->aDocumento['nEuroscore'] . ' %'];
		}
		if(!empty($this->aDocumento['cPronostico'])){
			$laTr['aCuerpo'][] = ['titulo2', 'Pronóstico:'];
			$laTr['aCuerpo'][] = ['texto9',  $this->aDocumento['lcPronostico']];
		}

		$laTr['aCuerpo'][]=['firmas', [
				['registro' => trim($this->aDocumento['cRegMed']),'prenombre'=>'Dr. ', 'codespecialidad' => $this->aDocumento['cCodEsp']]]] ;
		
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}
	private function datosBlanco(){
		return[
			'cTipoCirugia' => '',
			'cTipoPatologia' => '',
			'cListaAntecdentes' => '',
			'lcListaAnteced' => '',
			'laListaAnteced' => '',
			'cAntecedentes' => '',
			'cEcograficos' => '',
			'lcListaFactores' => '',
			'laListaFactores' => '',
			'laFactoresRiesgo' => '',
			'cRiesgoClasificacion' => '',
			'cRadiografia' => '',
			'cElectro' => '',
			'nCreatinina' => 0,
			'nHemoglobina' => 0,
			'nGlicosilada' => 0,
			'nPesoAjustado' => 0,
			'nPesoReal' => 0,
			'nEuroscore' => 0,
			'nTalla' => 0,
			'cEuroscoreVal' => '',
			'cPronostico' => '',
			'lcPronostico' => '',
			'nIMC' => 0,
			'cRegMed' => '',
			'cDesEsp' => '',
			'cUsuari' => '',
			'cCodEsp' => '',
			'cSecCam' => '',
			'cNroCam' => '',
			'lnCuentaSel' => 0,
		];
	}
}