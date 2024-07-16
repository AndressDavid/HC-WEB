<?php
namespace NUCLEO;

class Rips
{
	protected $nIngreso = 0;
	
	function __construct() {
		global $goDb;
		$this->oDb = $goDb;
    }

	public function crearSalida($taIngreso=[],$tcRegMed='',$tcPrgCre)
	{
		$this->nIngreso = $taIngreso['nIngreso'];
		$lcChrRec = chr(24);
		$lcChrItm = ',';
		
		// Buscar seccion y cama
		$lcSecHab = $lcSeccion = $lcHabita = '';
		$laTemp = $this->oDb
			->select('SECHAB, NUMHAB')
				->from('FACHAB')
				->where([
						'INGHAB'=>$taIngreso['nIngreso'],
						'ESTHAB'=>'1',
						])		
				->get('array');
			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$lcSecHab = (empty(trim($laTemp['SECHAB']))?' ':trim($laTemp['SECHAB']).$lcChrItm) .
								(empty(trim($laTemp['NUMHAB']))?' ':trim($laTemp['NUMHAB'])) ;
								
					$lcSeccion = trim($laTemp['SECHAB']);
					$lcHabita = trim($laTemp['NUMHAB']);
				}
			}
		
			$lcString = (empty(trim($taIngreso['cPlan']))?' ':trim($taIngreso['cPlan']).$lcChrItm) .
						(empty(trim($tcRegMed))?' ':trim($tcRegMed).$lcChrItm) . $lcSecHab;
			
			
			// Variables de AS400
			$lnIndRis = 1;
			$lnConRis = $lnIngRis = $lnIn2ris = $lnIn3Ris = $lnCnlRis = $lnOp3Ris = $lnOp4Ris = $lnFecRis = $lnHorRis = $lnOp7Ris = $lnFmoRis = $lnHmoRis = 0 ;
			$lcTidRis = $lnIdeRis = $lcEstRis = $lcDesRis = $lcOp1Ris = $lcOp2Ris = $lcUmoRis = $lcPmoRis = $lcOp5Ris = $lcOp6Ris = $lcUsrRis = $lcPgmRis = '' ;
			
			//$ldFreRis = fecha del dia					
			
			$lnConRis = $this->nuevoID($lnIndRis) ;
			$lnIngRis = $taIngreso['nIngreso'];
			$lcTidRis = $taIngreso['cTipId'];
			$lnIdeRis = $taIngreso['nNumId'];
			$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
			$lnFecRis = $ltAhora->format('Ymd');
			$lnHorRis = $ltAhora->format('His');
			$lcUsrRis = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
			$lcPgmRis = $tcPrgCre;
			$laSegmentos = str_split($lcString,220);

			// validando Información 

			$llExiste = $this->ValidarSalida();
			$lcTabla = 'RIPSAL';
			
			if($llExiste==true)
			{
				if(is_array($laSegmentos)){
					if(count($laSegmentos)>0){
						
						foreach($laSegmentos as $laDato){
							$lnCnlRis++;
							$laDatos = [
								'CONRIS'=>$lnConRis,
								'INGRIS'=>$this->nIngreso,
								'INDRIS'=>$lnIndRis,
								'IN2RIS'=>$lnIn2ris,
								'IN3RIS'=>$lnIn3Ris,
								'TIDRIS'=>$lcTidRis,
								'IDERIS'=>$lnIdeRis,
								'CNLRIS'=>$lnCnlRis,
								'ESTRIS'=>$lcEstRis,
								'FRERIS'=>$lnFecRis,
								'DESRIS'=>$laDato,
								'OP1RIS'=>$lcOp1Ris,
								'OP2RIS'=>$lcOp2Ris,
								'OP3RIS'=>$lnOp3Ris,
								'OP4RIS'=>$lnOp4Ris,
								'OP5RIS'=>$lcOp5Ris,
								'OP6RIS'=>$lcOp6Ris,
								'OP7RIS'=>$lnOp7Ris,
								];
	
							if($this->ExisteRipsSalida($lnIndRis,0,$lnCnlRis)==true){
								
								
								$laWhere = ['CONRIS'=>$lnConRis,
											'INDRIS'=>$lnIndRis,
											'IN2RIS'=>$lnIn2ris,
											'IN3RIS'=>$lnIn3Ris,
											'CNLRIS'=>$lnCnlRis,
											];
								
								$laDataU=[
									'UMORIS'=>$lcUsrRis,
									'PMORIS'=>$lcPgmRis,
									'FMORIS'=>$lnFecRis,
									'HMORIS'=>$lnHorRis,
								];
								$this->oDb->tabla($lcTabla)->where($laWhere)->actualizar(array_merge($laDatos,$laDataU));
								
							}else{
								$laDataI=[
									'USRRIS'=>$lcUsrRis,
									'PGMRIS'=>$lcPgmRis,
									'FECRIS'=>$lnFecRis,
									'HORRIS'=>$lnHorRis,
								];
								$this->oDb->tabla($lcTabla)->insertar(array_merge($laDatos,$laDataI));						
								
							}
								
						}
						
					}
				}
				
			}
	}
	
	function nuevoID($tnIndex=0)
	{
		$lnRipsConsec = $this->oDb->secuencia('SEQ_RIPSAL');
		return $lnRipsConsec;
	}
	
	function ValidarSalida()
	{
	
		$llResultado = !($this->ExisteRipsSalida(1,0,0));
		return $llResultado ;
		
	}
	
	function ExisteRipsSalida($tnIndRis=1,$tnConRis=0,$tnCnlRis=0)
	{
		$llexisteRips = false ;
		
		$laTemp = $this->oDb
			->select('COUNT(CONRIS) AS FIELD')
			->from('RIPSAL')
			->where(['INDRIS'=>$tnIndRis,
				   'INGRIS'=>$this->nIngreso
				 ])
			->in('ESTRIS', [' ','CERRADO'])
			->get('array');
		
		if(is_array($laTemp)){
			if($laTemp['FIELD']>0){
				$llexisteRips = true;
			}
		}
		
		unset($laTemp);
		return $llexisteRips;
	
	}
		
}
