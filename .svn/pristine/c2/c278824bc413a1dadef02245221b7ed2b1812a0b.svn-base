<?php
namespace NUCLEO;

class Doc_EscalaNas
{
	protected $oDb;
	protected $aEscalaNAS = [];
	protected $aRespuestas = [];
	protected $aTitulos  = [];
	protected $nEdad=0;
	protected $nFechaInicial=0;
	protected $nFechaFinal=0;


	protected $aReporte = [
					'cTitulo' => 'ESCALA NAS',
					'lMostrarEncabezado' => true,
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => true,
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
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	//	Consulta los datos
	private function consultarDatos($taData){

		$lcSL = PHP_EOL;

		// Consulta Escala NAS 
		$this->aRespuestas = $this->oDb
			->select('A.DETNAS AS DETALLE, A.OP1NAS AS NENF, A.TOTNAS AS TOTAL, A.USUNAS AS USUARIO, 
					  IFNULL(TRIM(B.NOMMED)||\' \'||TRIM(B.NNOMED), \'\') AS NOMUSU, FECNAS, HORNAS')
			->from('ENFNAS AS A')
			->leftJoin('RIARGMN AS B', 'A.USUNAS=B.USUARI', null)
			->where(['A.INGNAS'=>$taData['nIngreso'],
					 'A.REGNAS'=>$taData['nConsecDoc'],
					 'A.CONNAS'=>$taData['nConsecCita'],
					])
			->getAll('array');
			
		// Consulta Titulos de los item para la Escala NAS 	
		$this->aTitulos = $this->oDb	
			->select('TRIM(CL2TMA) AS PREGUN, DE2TMA AS TITULO, CL3TMA AS NIVEL')
			->from('TABMAE')
			->where(['TIPTMA'=>'ESCALANA',
					 'CL1TMA'=>'NUMERAL',
					 'ESTTMA'=>'',
					])
			->orderBy ('INT(OP3TMA)')		
			->getAll('array');
			
		// Array que maneja los datos digitados
		for ($lnIndica=1; $lnIndica<=23; $lnIndica+=1){
			
			$this->aDatosEsc[$lnIndica] = ['Pregunta'=>'', 'Valor'=>''];
		}

		$this->OrganizarDatos();
				
	}

	//	Prepara array $aReporte con los datos para imprimir
	private function prepararInforme($taData)
	{
		$laTr['aCuerpo'] = [];
		$lcSL = PHP_EOL;
		$laAnchos = [150, 40];
		$laDatos = [];
		$laTr['aCuerpo'][] = ['titulo1', 'FECHA - HORA REPORTE : '. AplicacionFunciones::formatFechaHora('fechahora', $this->aRespuestas[0]['FECNAS'].$this->aRespuestas[0]['HORNAS'])] ;
	
		foreach($this->aEscalaNas as $laNAS){
			if ($laNAS['Pregunta']=='00') {
				$lcRta = '<b>'.$laNAS['Respuesta'].'</b>';
			} else {
				$lcRta = $laNAS['Respuesta'];
			}
			$laDatos[] = [
				'w'=>$laAnchos,
				'd'=>[$lcRta, $laNAS['Valor']],
				'a'=>[($laNAS['Pregunta']==''?'R':'L'),'R']
				];
			
		}
		
		$laTr['aCuerpo'][]= ['tablaSL',
							[ [ 'w'=>$laAnchos, 'd'=>['ITEM','VALOR'], 'a'=>'C', ] ],
							$laDatos,
							];
					
		$laTr['aCuerpo'][]=['firmas', [
				['usuario' => $this->aRespuestas[0]['USUARIO']]]];
				
		$this->aReporte = array_merge($this->aReporte, $laTr);
			
	}
	
	private function OrganizarDatos()
	{
		
		foreach($this->aRespuestas as $laEscala) {
			
			$lcCharReg = '¤';
			$laWordsReg = explode($lcCharReg, $laEscala['DETALLE']);
			$lnInd = 1 ;
						
			foreach($laWordsReg as $lnIndex => $laRegs) {
				
				$lcPregunta = trim(substr($laRegs,0,4));
				$lcRespuesta= trim(substr($laRegs,4,strlen($laRegs)));
				$this->aDatosEsc[$lnInd]['Pregunta'] = $lcPregunta ;
				$this->aDatosEsc[$lnInd]['Valor'] = $lcRespuesta ;
				$lnInd++;
				
			}
			
		}
				
		$laValores=array_column($this->aDatosEsc, 'Valor');
				
		foreach($this->aTitulos as $laTitulo){	
			$lcValor='';
			if (intval($laTitulo['PREGUN'])>8 && intval($laTitulo['PREGUN'])<24){
				$laTitulo['NIVEL']='';
			}
				
			$lcPregunta	= $laTitulo['PREGUN'] ;	
			$lcDescPreg=(trim($laTitulo['NIVEL'])=='2'?'  ':'') .  $laTitulo['TITULO'];
			$key = array_search($lcPregunta, array_column($this->aDatosEsc, 'Pregunta'));

			if (is_numeric($key)){
				$lcValor=$laValores[$key];
				$key = array_search($lcPregunta, array_column($this->aTitulos, 'PREGUN'));
				if (is_numeric($key)){
					$lcDescPreg=(trim($laTitulo['NIVEL'])=='2'?'  ':'') . $this->aTitulos[$key]['TITULO'] ;
				}
			}
			
			if (intval($laTitulo['PREGUN'])>8 && intval($laTitulo['PREGUN'])<24 && empty($lcValor)){
				$lcDescPreg = '' ;			
			}
			
			if (!empty($lcDescPreg)) {
				if (strlen($lcPregunta)==2 || (strlen($lcPregunta)>2 && !empty($lcValor))){
					$llInsert=true;
					if ($lcPregunta=='00') $llInsert=$this->VerificarTitulo($lcPregunta, $lcDescPreg, $lcValor);
					if ($llInsert) {
						$this->aEscalaNas[]=[
							'Pregunta'=>$lcPregunta,
							'Respuesta'=>$lcDescPreg,
							'Valor'=>$lcValor,
						];
					}
				}
			}
		
		}
		$this->VerificarTitulo() ;		
		$this->aEscalaNas[]=[
						'Pregunta'=>'',
						'Respuesta'=>'<b>PUNTAJE TOTAL:</b>',
						'Valor'=>$this->aRespuestas[0]['TOTAL'],
					];
		$this->aEscalaNas[]=[
						'Pregunta'=>'',
						'Respuesta'=>'<b>CANT. ENFERMERAS:</b>',
						'Valor'=>$this->aRespuestas[0]['NENF'],
					];
	}
	
	function VerificarTitulo($tcPregunta='', $tcDescPreg='', $tcValor='')
	{
		$lnReg = count($this->aEscalaNas) - 1;
			
		if ($this->aEscalaNas[$lnReg]['Pregunta']=='00' || empty($this->aEscalaNas[$lnReg]['Pregunta'])){
		
			$this->aEscalaNas[$lnReg]=[
						'Pregunta'=>$tcPregunta,
						'Respuesta'=>$tcDescPreg,
						'Valor'=>$tcValor,
						];
			return false;
		}
		else{
			$this->insertaNasVacio();
		}
		return true;
	}
	
	function insertaNasVacio()
	{
		$this->aEscalaNas[]=[
						'Pregunta'=>'',
						'Respuesta'=>'',
						'Valor'=>'',
					];
	}
	
}
	 
