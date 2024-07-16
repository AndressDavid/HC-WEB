<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Db.php';

class Doc_RecomendacionesUCC
{
	protected $oDb;
	protected $aDatosEvoluc = [];
	protected $aDatosParametros = [];
	protected $aDatosMedicamentos = '';
	protected $aErrorN = [
					'Mensaje' => '',
					'Objeto' => '',
					'Valido' => true,
				];
	protected $aReporte = [
					'cTitulo' => 'METAS RECOMENDADAS PARA PREVENCION DE UN NUEVO EVENTO CARDIOVASCULAR',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => [],
				];
	protected $cRealizadoPor = '';


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
	}


	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*	Consulta los datos del documento desde la BD en el array $aDocumento */
	private function consultarDatos($taData)
	{
		$this->cRealizadoPor = '';	
		/* Lista de parametros salida UCC */
		$this->aDatosParametros = $this->oDb
			->select('DE1TMA PARAM, TRIM(DE2TMA) METTMAM, TRIM(OP5TMA) METTMAF, OP3TMA, OP6TMA, OP7TMA, OP2TMA')
			->from('TABMAE')
			->where(['TIPTMA'=>'EVENTOCV','ESTTMA'=>' ',])
			->orderBy ('OP3TMA')
			->getAll('array');
			
		if(!is_array($this->aDatosParametros)){$$this->aDatosParametros=[];}
		
		/* Valor parametros, medicamentos y recomendaciones salida UCC */
		$this->aDatosEvoluc = $this->oDb
		
			->select('A.*, IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('EVOLUC AS A')
			->leftJoin('RIARGMN AS M', 'A.USREVL = M.USUARI')
			->where(['A.NINEVL'=>$taData['nIngreso'],'A.CONEVL'=>$taData['nConsecEvol'],])
			->between('A.CNLEVL',8000,8099)
			->orderBy ('A.CNLEVL')
			->getAll('array');
			
		if(!is_array($this->aDatosEvoluc)){$this->aDatosEvoluc=[];}
		
		if (count($this->aDatosEvoluc)>0){
			$this->cRealizadoPor = trim($this->aDatosEvoluc[0]['NOMMED']) . ' ' . trim($this->aDatosEvoluc[0]['NNOMED']) . ' - RM.' . $this->aDatosEvoluc[0]['REGMED'];
			$this->OrganizarDatos();	
		}
	}


	/* Prepara array $aReporte con los datos para imprimir */
	private function prepararInforme($taData)
	{
		// Buscar genero del paciente 
		$laSexo = $this->oDb
		->select('TRIM(B.SEXPAC) AS SEXPAC')
			->from('RIAING AS I')
			->leftJoin('RIAPAC AS B', 'B.TIDPAC = I.TIDING AND B.NIDPAC = I.NIDING', null)
			->where(['I.NIGING'=>$taData['nIngreso'],])
			->get('array');
		$laTr['aCuerpo'][0] = [
			'tabla', [
				[
					'w'=>[59, 71, 50],
					'a'=>['C','C','C'],
					'd'=>['PARAMETRO','META','VALOR',]
				]
			], []
		];
		
		if(count($this->aDatosParametros)>0){
			foreach($this->aDatosParametros as $laParam) {
				$lcMeta = $laSexo['SEXPAC']==='F' ? $laParam['METTMAF'] : $laParam['METTMAM'] ;
				$lcCelda = str_replace(['>','<'], ['&gt;','&lt;'], $lcMeta);
				$laParam['VALOR'] = (trim($laParam['VALOR'])=='1' ? 'SI' : (trim($laParam['VALOR'])=='0' ? 'NO' : $laParam['VALOR']));
				
				$laTr['aCuerpo'][0][2][] = [
					'w'=>[59, 71, 50],
					'a'=>['L','C','C',],
					'd'=>[
						$laParam['PARAM'],
						$lcCelda,						
						$laParam['VALOR'],
					],
				];
			}

			if(!empty($this->aDatosMedicamentos)){

				$laXML = new \SimpleXMLElement(mb_convert_encoding($this->aDatosMedicamentos, 'Windows-1252','UTF-8'));
				$aResultado = [];
				$lnInd = 0;
				foreach($laXML->ccurmedica as $laParam) {
					foreach($laParam->attributes() as $k => $v){
						$aResultado[$lnInd][$k]  = (string)$v;
					}
					$lnInd ++;		
				}	
						
				$laTr['aCuerpo'][1] = [
					'tabla', [
						[
							'w'=>[59, 71, 50],
							'a'=>['C','C','C',],
							'd'=>['GRUPO','NOMBRE MEDICAMENTO','INDICADO PARA:',]
						]
					], []
				];
				
				foreach($aResultado as $laParam) {
							
					$laTr['aCuerpo'][1][2][] = [
						'w'=>[59, 71, 50],
						'a'=>['L','L','C',],
						'd'=>[
							$laParam['nomgrp'],
							$laParam['nommed'],
							$laParam['indmed'],
						],
					];
				}
			}

			$laTr['aCuerpo'][] = ['saltol', 5];
			$laTr['aCuerpo'][] = ['txthtml9', '<b>Realizado por:</b> ' . $this->cRealizadoPor]; 
			$this->aReporte = array_merge($this->aReporte, $laTr);

		}
	}

	// Prepara array parametros y medicamentos para imprimir
	public function OrganizarDatos()
	{
		$this->aDatosMedicamentos = $laRecomendaciones = '';
		foreach($this->aDatosEvoluc as $laData) {
			switch (true){
				// parametros 
				case $laData['CNLEVL']==8000 :
					$this->fnActualizarParametros($laData['DESEVL']) ;
					break;
				
				// Medicamentos 
				case $laData['CNLEVL']>=8001 && $laData['CNLEVL']<8049 :
					$this->aDatosMedicamentos .= $laData['DESEVL'];					
					break;
					
				//Recomendaciones
				case $laData['CNLEVL']>=8050 && $laData['CNLEVL']<8099 :
					$laRecomendaciones .= $laData['DESEVL'];
					break;
			}
		}
	}
	
	public function fnActualizarParametros($taDatos=[])
	{
		$lcCadena = $taDatos;
		foreach($this->aDatosParametros as $lnKey=>$laParametro) {
			$this->aDatosParametros[$lnKey]['VALOR'] = '';
			$lnPosicion = strpos($lcCadena, trim($laParametro['OP2TMA']));
			if($lnPosicion !== false){
				$this->aDatosParametros[$lnKey]['VALOR'] = substr($lcCadena,$lnPosicion+strlen(trim($laParametro['OP2TMA']))+1,3);
				
			}
		}
	}
}
