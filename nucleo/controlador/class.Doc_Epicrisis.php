<?php
namespace NUCLEO;

require_once ('class.Doc_HC.php');
require_once ('class.Doc_Ordenes.php');
require_once ('class.Texto_Diagnostico.php');
require_once ('class.Especialidad.php');

use NUCLEO\Doc_HC;
use NUCLEO\Doc_Ordenes;
use NUCLEO\Texto_Diagnostico;
use NUCLEO\Especialidad;

class Doc_Epicrisis
{
	protected $oDb;
	protected $cTituloVacios='';
	protected $aDocumento = [];
	protected $laDocumento = [];
	protected $aDiagnostico = [];

	protected $aReporte = [
				'cTitulo' => 'EPICRISIS',
				'lMostrarFechaRealizado' => false,
				'lMostrarViaCama' => false,
				'cTxtAntesDeCup' => '',
				'cTituloCup' => '',
				'cTxtLuegoDeCup' => '',
				'aCuerpo' => [],
				'aFirmas' => [],
				'aNotas' => ['notas'=>false,'codproc'=>'NOTAEPI'],
			];


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	//	Retornar array con los datos del documento
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}

	//	Consulta los datos del documento desde la BD en el array $aDocumento
	private function consultarDatos($taData)
	{
		$lcSL = "\n"; //PHP_EOL;
		$this->laDocumento = $this->datosBlanco();
		$lnReg=0 ;

		$this->laDocumento['nConsecEPI'] = $taData['nConsecDoc'] ;

		// Consulta datos de analisis de epicrisis
		$laAnalisis = $this->oDb
			->select('DESEPA')
			->from('RIAEPIAL01')
			->where([
				'TIDEPA'=>$taData['cTipDocPac'],
				'NIDEPA'=>$taData['nNumDocPac'],
				'NINEPA'=>$taData['nIngreso'],
				'CCNEPA'=>$this->laDocumento['nConsecEPI'],
			])
			->orderBy('colepa')
			->getAll('array');

		foreach($laAnalisis as $laDatoA){
			$this->laDocumento['cAnalisis'] .=$laDatoA['DESEPA'];
		}

		// Consulta de datos del médico que dio salida
		$laMedico = $this->oDb
			->select('RMEEPD, OP1EPD')
			->from('RIAEPHD')
			->where([
				'NINEPD'=>$taData['nIngreso'],
				'CCNEPD'=>$this->laDocumento['nConsecEPI'],
			])
			->get('array');

		$this->laDocumento['cRegMedicoSalida'] .=$laMedico['RMEEPD']??'';
		$this->laDocumento['cEspMedicoSalida'] .=$laMedico['OP1EPD']??'';

		// Diagnosticos
 		$laDiagnostico = $this->oDb
			->select('trim(TRAEDC) TRATAMIENTO, FECEDC FECHADIA, INDEDC INDICE, DIPEDC DIAGNOS, CLIEDC')
			->from('EVODIA')
			->where([
				'INGEDC'=>$taData['nIngreso'],
				'EVOEDC'=>$this->laDocumento['nConsecEPI'],
				'TIPEDC'=>'RF',
			])
			->where('INDEDC','<',5)
			->orderBy('INDEDC')
			->getAll('array');

		$loTextoDiag = new Texto_Diagnostico($laDiagnostico, $taData['nIngreso'],$this->laDocumento['nConsecEPI'],'RF');
		$this->aDiagnostico = $loTextoDiag->retornarDocumento($laDiagnostico, $taData['nIngreso'],$this->laDocumento['nConsecEPI']);

		// Fecha Hora de Ingreso y Egreso
		$laEpicrisis = $this->oDb
			->select('FEIING, HORING, FEEING, HREING')
			->from('RIAINGL15')
			->where(['NIGING'=>$taData['nIngreso']])
			->get('array');

		if (is_array($laEpicrisis)){

			if (count($laEpicrisis)>0){

				$this->laDocumento['nFechaIngreso'] = $laEpicrisis['FEIING'];
				$this->laDocumento['nHoraIngreso'] = $laEpicrisis['HORING'];
				$this->laDocumento['nFechaEgreso'] = $laEpicrisis['FEEING'];
				$this->laDocumento['nHoraEgreso'] = $laEpicrisis['HREING'];
			}
		}

		// Estado Salida
		$laEpicrisis = $this->oDb
			->select('ESADIA')
			->from('RIADIA')
			->where(['INGDIA'=>$taData['nIngreso'],
					])
			->get('array');

		if (is_array($laEpicrisis)){

			if (count($laEpicrisis)>0){
				$this->laDocumento['cEstadoSalida'] = $laEpicrisis['ESADIA'] ;
			}

		}

		// Datos Egreso
		$laEpicrisis = $this->oDb
			->select('FECEPI, HOREPI, COSEPI, DESEPI')
			->from('RIAEPI')
			->where([
				'TIDEPI'=>$taData['cTipDocPac'],
				'NIDEPI'=>$taData['nNumDocPac'],
				'NINEPI'=>$taData['nIngreso'],
				'CCNEPI'=>$this->laDocumento['nConsecEPI'],
			])
			->getAll('array');

		$lnReg = count($laEpicrisis) ;


		foreach($laEpicrisis as $laDatoA){
			$this->laDocumento['nFecRea'] = $laDatoA['FECEPI'];
			$this->laDocumento['nHorRea'] = $laDatoA['HOREPI'] ;

			switch (true){
				case ($laDatoA['COSEPI']> 2100 && $laDatoA['COSEPI']<=2200) :
					// Condiciones Generales
					$this->laDocumento['cCondGenerales'] .= $laDatoA['DESEPI'];
					break ;

				case ($laDatoA['COSEPI']> 2200 && $laDatoA['COSEPI']<=2300) :
					// Plan de Manejo
					$this->laDocumento['cPlanManejo'] .= $laDatoA['DESEPI'];
					break ;
				
				case ($laDatoA['COSEPI']>= 6000 && $laDatoA['COSEPI']< 7000) :
					// Plan de Manejo
					$this->laDocumento['cInterpretacion'] .=  $laDatoA['DESEPI']  ;
					break ;

				// Desde 5000 hasta 2017 FIBRILACION AURICULAR
				case $laDatoA['COSEPI']==5000:
					$this->laDocumento['lTieneFibrilacion'] = true ;
					break ;

				case $laDatoA['COSEPI']==5002:
					$this->laDocumento['nFibrilacionAuricular'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5003:
					$this->laDocumento['nFibrilacionAuricular'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5004:
					$this->laDocumento['nBetaBloqueo'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5005:
					$this->laDocumento['nBetaBloqueo'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5006:
					$this->laDocumento['nHidrocortisona'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5007:
					$this->laDocumento['nHidrocortisona'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5008:
					$this->laDocumento['nAmiodarona'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5009:
					$this->laDocumento['nAmiodarona'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5010:
					$this->laDocumento['nCardioversion'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5011:
					$this->laDocumento['nCardioversion'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5012:
					$this->laDocumento['nAnticoagulacion'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5013:
					$this->laDocumento['nAnticoagulacion'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5014:
					$this->laDocumento['nBetapop'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5015:
					$this->laDocumento['nBetapop'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==5016:
					$this->laDocumento['nEgresoFibrilacion'] = 1 ;
					break ;

				case $laDatoA['COSEPI']==5017:
					$this->laDocumento['nEgresoFibrilacion'] = 2 ;
					break ;

				case $laDatoA['COSEPI']==9000:
					$this->laDocumento['cRegElaboroEpicrisis'] = trim(substr($laDatoA['DESEPI'],0,13)) ;
					$this->laDocumento['cEspElaboroEpicrisis'] = trim(substr($laDatoA['DESEPI'],37,3)) ;
					$loEspecial = new Especialidad($this->laDocumento['cEspElaboroEpicrisis']);
					$this->laDocumento['cServicioEgresa']=$loEspecial->cNombre ;
					break ;
				case $laDatoA['COSEPI']==9001:
					$latemp = Json_decode($laDatoA['DESEPI']);
					$this->laDocumento['cEstadoSalida'] = $latemp->EstSali;
			}
		}

		// 	Fecha y hora de egreso, Médico que elaboro epicrisis
		$laEpicrisis = $this->oDb
			->select('RMEEPH, FEEEPH, HRREPH, FECEPH')
			->from('RIAEPH')
			->where([
				'NINEPH'=>$taData['nIngreso'],
				'CCNEPH'=>$this->laDocumento['nConsecEPI'],
			])
			->get('array');

		if(is_array($laEpicrisis)){

			if (count($laEpicrisis)>0){
				$this->laDocumento['cRegElaboroEpicrisis'] = empty(trim($this->laDocumento['cRegElaboroEpicrisis']))? $laEpicrisis['RMEEPH'] : $this->laDocumento['cRegElaboroEpicrisis'] ;
			}
		}

		// Consulta NO POS de cups
		$laNoPOS = $this->oDb
				->select('SUBSTR(TRIM(B.DESCUP),0,45) DESCRIPCIONCUPS, SUM(A.QCOEST) CANTIDAD')
				->from('RIAESTM38 AS A')
				->leftJoin('RIACUP AS B', 'A.CUPEST=B.CODCUP', null)
				->where([
					'A.INGEST'=>$taData['nIngreso'],
					'A.TINEST'=>'400',
					'A.RF5EST'=>'NOPB',
				])
				->where('A.FECEST*1000000+A.HOREST','<',$this->laDocumento['nFecRea']*1000000+$this->laDocumento['nHorRea'])
				->GroupBy('B.DESCUP')
				->OrderBy('B.DESCUP')
				->getAll("array");


		if (is_array($laNoPOS)){
			if (count($laNoPOS)>0){
				foreach($laNoPOS as $laDatos){
					$this->laDocumento['cTextoNopos'] .= AplicacionFunciones::mb_str_pad(trim($laDatos['DESCRIPCIONCUPS']),60,' ') .
														 str_pad(trim($laDatos['CANTIDAD']),10,' ') . $lcSL ;
				}
			}
		}

		// Servicios NOPOS insumos
		$laNoPOS = $this->oDb
			->select('SUBSTR(TRIM(B.DESDES),0,45) DESCRIPCIONCUPS,
					  TRIM(B.UNDDES) PRESENTACION,
					  TRIM(B.UNIDES) UNIDAD,
					  SUM(A.QCOEST) CANTIDAD')
			->from('RIAESTM38 AS A')
			->leftJoin('INVDESL3 AS B', 'A.ELEEST=B.REFDES', null)
			->where([
				'A.INGEST'=>$taData['nIngreso'],
				'A.RF4EST'=>'NOPOS',
			])
			->where('A.FECEST*1000000+A.HOREST','<=',$this->laDocumento['nFecRea']*1000000+$this->laDocumento['nHorRea'])
			->in('A.TINEST',['500','600'])
			->GroupBy('B.DESDES, B.UNDDES, B.UNIDES')
			->OrderBy('B.DESDES, B.UNDDES, B.UNIDES')
			->getAll("array");


		if (is_array($laNoPOS)){
			if (count($laNoPOS)>0){
				foreach($laNoPOS as $laDatos){
					$this->laDocumento['cTextoNopos'] .= AplicacionFunciones::mb_str_pad(trim($laDatos['DESCRIPCIONCUPS']),60,' ') .
														 str_pad(trim($laDatos['PRESENTACION']),15,' ') .
														 str_pad(trim($laDatos['UNIDAD']),15,' ') .
														 str_pad(trim($laDatos['CANTIDAD']),10,' ') . $lcSL ;

				}
			}
		}
	}

	// Prepara array $aReporte con los datos para imprimir
	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = "\n"; //PHP_EOL;
		$cVacios = $this->cTituloVacios;
		$taDataHC=array_merge($taData,[]);
		$taDataHC['cTipoDocum']='';
		$taDataHC['nConsecCons']='';
		$taDataHC['nConsecDoc']='';
		$taDataHC['nConsecEvol']='';
		$taDataHC['cTipoProgr'] = 'HCPPAL' ;
		$loHC = new Doc_HC();
		$laHC = $loHC->retornarDocumento($taDataHC, true);
		$loEspecial = new Especialidad($laHC['Firmas']['0']['codespecialidad']??'');
		$this->laDocumento['cServicioIngresa']=$loEspecial->cNombre ;

		// Encabezado
		$laTr['cTxtAntesDeCup'] =
				 str_pad('Fecha/Hora: Ingreso: '.AplicacionFunciones::formatFechaHora('fechahora', $this->laDocumento['nFechaIngreso'].' '.$this->laDocumento['nHoraIngreso']), $lnAnchoPagina/3, ' ')
				.str_pad('                         Egreso : '.AplicacionFunciones::formatFechaHora('fechahora', $this->laDocumento['nFechaEgreso'].' '.$this->laDocumento['nHoraEgreso']) . $lcSL, $lnAnchoPagina/2, ' ')
				.str_pad('Servicio de Ingreso : '. $this->laDocumento['cServicioIngresa'], $lnAnchoPagina/3, ' ') . $lcSL
				.str_pad('Servicio de Egreso  : '. $this->laDocumento['cServicioEgresa'], $lnAnchoPagina/3, ' ') ;

		$laTr['aCuerpo'][] = ['titulo1', 'HISTORIA CLÍNICA'];
		foreach($laHC['aCuerpo'] as $laReg) {
			$laTr['aCuerpo'][] = $laReg;
		}
		$laTr['aCuerpo'][] = ['lineah',['superior'=>5,'inferior'=>5,]];

		// Cuerpo
		if(empty($this->laDocumento['cAnalisis'])==false){
			$laTr['aCuerpo'][] = ['titulo1', 'EVOLUCION'];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumento['cAnalisis'])];
		}

		// Interpretación de exámenes
		if(empty($this->laDocumento['cInterpretacion'])==false){
			$laTr['aCuerpo'][] = ['titulo1', 'INTERPRETACION DE EXAMENES'];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumento['cInterpretacion'])];
		}

		// Fibrilacion Auricular
		$lcDetalle = '' ;
		if ($this->laDocumento['lTieneFibrilacion']){
			$lcDetalle .= 'FIBRILACION AURICULAR POP        : ' . ($this->laDocumento['nFibrilacionAuricular']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'BETA BLOQUEO PREOP               : ' . ($this->laDocumento['nBetaBloqueo']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'HIDROCORTISONA                   : ' . ($this->laDocumento['nHidrocortisona']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'AMIODARONA                       : ' . ($this->laDocumento['nAmiodarona']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'CARDIOVERSIÓN ELECTRICA          : ' . ($this->laDocumento['nCardioversion']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'ANTICOAGULACIÓN                  : ' . ($this->laDocumento['nAnticoagulacion']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'BETA BLOQUEO POP                 : ' . ($this->laDocumento['nBetapop']==1? 'SI' : 'NO') . $lcSL ;
			$lcDetalle .= 'EGRESO CON FIBRILACION AURICULAR : ' . ($this->laDocumento['nEgresoFibrilacion']==1? 'SI' : 'NO') . $lcSL ;
		}

		if (!empty($lcDetalle)){
			$laTr['aCuerpo'][] = ['titulo1', 'Fibrilación Auricular'];
			$laTr['aCuerpo'][] = ['texto9',	$lcDetalle];
		}

		// Diagnósticos
		if(is_array($this->aDiagnostico)){
			if(count($this->aDiagnostico)>0){
				$lcDx = $lcDetalleFx = $lcDetalle = '';
				foreach($this->aDiagnostico as $laDiagnos) {
					if($laDiagnos['INDICE'] != 4){
						$laDiagnos = array_map('trim',$laDiagnos);
						if($lcDx!==$laDiagnos['DIAGNOS']){
							$lcDetalle .= $laDiagnos['DIAGNOS'] . '  ' . $laDiagnos['desc_d'] . $lcSL
									. (!empty($laDiagnos['TipoDiag'])?	'      Tipo diagnóstico   : ' . $laDiagnos['TipoDiag'] . $lcSL :'')
									. (!empty($laDiagnos['ClaseDiag'])?	'      Clase Diagnóstico  : ' . $laDiagnos['ClaseDiag'] . $lcSL :'')
									. (!empty($laDiagnos['TipoTrata'])?	'      Tratamiento        : ' . $laDiagnos['TipoTrata'] . $lcSL :'')
									. (!empty($laDiagnos['Analisis'])?	'      Análisis - Conducta: ' . $laDiagnos['Analisis'] . $lcSL :'')
									. (!empty($laDiagnos['Descarte'])?	'      Justificación      : ' . $laDiagnos['Descarte'] . $lcSL :'');
							$lcDx=$laDiagnos['DIAGNOS'];
						}else{
							$lcDetalle .= $laDiagnos['Analisis'] ;
						}
					} else{
						$lcDetalleFx = trim($laDiagnos['DIAGNOS'] . ': ' . $laDiagnos['desc_d']);
					}
				}

				if (!empty($lcDetalle)){
					$laTr['aCuerpo'][] = ['titulo1', 'DIAGNOSTICOS'];
					$laTr['aCuerpo'][] = ['texto9',	$lcDetalle. $lcSL];
				}

				if (!empty($lcDetalleFx)){
					$laTr['aCuerpo'][] = ['titulo1', 'Diagnóstico Fallece'];
					$laTr['aCuerpo'][] = ['texto9',	$lcDetalleFx. $lcSL];
				}
			}
		}

		// Condiciones Generales
		if(empty($this->laDocumento['cCondGenerales'])==false){
			$laTr['aCuerpo'][] = ['titulo1', 'Condiciones Generales'];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumento['cCondGenerales'])];
		}

		// Plan de Manejo
		if(empty($this->laDocumento['cPlanManejo'])==false){
			$laTr['aCuerpo'][] = ['titulo1', 'Plan de Manejo'];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumento['cPlanManejo'])];
		}

		// Estado de Salida
		if(empty($this->laDocumento['cEstadoSalida'])==false){

			$oTabmae = $this->oDb->ObtenerTabMae('DE1TMA', 'DATING', ['CL1TMA'=>'4','CL2TMA'=>$this->laDocumento['cEstadoSalida'], 'ESTTMA'=>' ']);
		    $lcDetalle = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));
			$laTr['aCuerpo'][] = ['titulo1', 'Estado de Salida'];
			$laTr['aCuerpo'][] = ['texto9',	trim($lcDetalle)];
		}

		// Ordenes Ambulatorias
		$laDatos = [
			'nIngreso'		=> $taData['nIngreso'],
			'cTipDocPac' 	=> $taData['cTipDocPac'],
			'cTipDocDesc'   => $taData['oIngrPaciente']->oPaciente->aTipoId['NOMBRE'],
			'nNumDocPac' 	=> $taData['nNumDocPac'],
			'cSexoPaciente' => $taData['oIngrPaciente']->oPaciente->cSexo,
			'cTipoDocum' 	=> '',
			'cTipoProgr' 	=> 'ORDA01A',
			'tFechaHora'	=> $taData['tFechaHora'],
			'nConsecCita'	=> $taData['nConsecCita'],
			'nConsecCons'	=> $taData['nConsecCons'],
			'nConsecEvol'	=> '0',
			'nConsecDoc'	=> '',
			'cCUP'			=> '',
			'cCodVia'		=> $taData['cCodVia'],
			'cSecHab'		=> $taData['cSecHab'],
			'cPlan'		    => $taData['oIngrPaciente']->cPlanDescripcion,
			'cNombre'		=> $taData['oIngrPaciente']->oPaciente->getNombresApellidos(),
			'nFechaIngreso' => $this->laDocumento['nFechaIngreso'],
			'nFechaEgreso'  => $this->laDocumento['nFechaEgreso'],
			'cDescVia'		=> $taData['oIngrPaciente']->cDescVia,
			'cFechaRealizado' => ''
		];

		$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], (new Doc_Ordenes())->ordenesHcEpi($laDatos));


		// Servicios NOPOS
		if(!empty(trim($this->laDocumento['cTextoNopos']))){
			$laTr['aCuerpo'][] = ['titulo1', 'Al paciente se le practicaron los siguientes servicios NO POS'];
			$laTr['aCuerpo'][] = ['titulo2', '               Descripción                          Presentación     Unidad       Cantidad '];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->laDocumento['cTextoNopos'])];
		}

		// Firma
		$laTr['aCuerpo'][] = ['firmas', [
			['registro'=>$this->laDocumento['cRegElaboroEpicrisis'], 'prenombre' => 'Dr. ','codespecialidad' => $this->laDocumento['cEspElaboroEpicrisis'],]
		] ];

		// Notas aclaratorias
		$laNotas = (new Doc_NotasAclaratorias())->notasAclaratoriasLibro($taData['nIngreso'],'NOTAEPI',$taData['nConsecDoc']);
		if(count($laNotas)>0) {
			$laTr['aCuerpo'][] = ['lineah', []];
			$laTr['aCuerpo'] = array_merge($laTr['aCuerpo'], $laNotas);
		}

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	// Array de datos de documento vacío
	private function datosBlanco()
	{
		return [
			'cCondGenerales'		=> '',
			'cAnalisis'				=> '',
			'cPlanManejo'			=> '',
			'cEstadoSalida'			=> '',
			'cRegElaboroEpicrisis'	=> '',
			'cEspElaboroEpicrisis'	=> '',
			'cRegMedicoSalida'		=> '',
			'cEspMedicoSalida'		=> '',
			'cServicioEgresa'		=> '',
			'cDxPrincipal'			=> '',
			'cDxComplica'			=> '',
			'cDxFallece'			=> '',
			'cTextoNopos'			=> '',
			'nFibrilacionAuricular'	=> 0,
			'nBetaBloqueo'			=> 0,
			'nHidrocortisona'		=> 0,
			'nAmiodarona'			=> 0,
			'nCardioversion'		=> 0,
			'nAntiCoagulacion'		=> 0,
			'nBetaPOP'				=> 0,
			'nEgresoFibrilacion'	=> 0,
			'nFechaIngreso'			=> 0,
			'nHoraIngreso'			=> 0,
			'nFechaEgreso'			=> 0,
			'nHoraEgreso'			=> 0,
			'nFecRea' 				=> 0,
			'nHorRea' 				=> 0,
			'cViaEPI'				=> '',
			'cInterpretacion'		=> '',
			'lTieneFibrilacion'		=> false,
		];
	}

}
