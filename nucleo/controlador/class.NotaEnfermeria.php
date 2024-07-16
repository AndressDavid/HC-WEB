<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class NotaEnfermeria
{
	public function __construct(){
	}

	public function CalcularConsNota($tnIngreso=0){
		$lnConsec = 0;
		global $goDb;
		if(isset($goDb)){		
			$laConsecutivo = $goDb->select('NTANOT,ADMNOT,CONNOT')->from('NCSNOT')->where('INGNOT', '=', $tnIngreso)->orderBy('CONNOT DESC')->limit(1)->get("array");
			if(is_array($laConsecutivo)==true){
				if(count($laConsecutivo)>0){
					if($laConsecutivo['NTANOT']=='S' && $laConsecutivo['ADMNOT']=='1'){ 
						$lnConsec = $laConsecutivo['CONNOT'] + 1;
					}else{
						$lnConsec = $laConsecutivo['CONNOT'];
					}
				}
			}
			$lnConsec=($lnConsec==0?1:$lnConsec);
		}
		return $lnConsec;
	}

	public function CalcularConsecutivo($tcTipo='', $tnIngreso=0, $tnCNota=0){
		$lnConsec = 0;
		$tcTipo= trim(ltrim(strtoupper($tcTipo)));

		global $goDb;
		if(isset($goDb)){
			switch ($tcTipo) {
				case 'SIGNOS':
					$laConsecutivo = $goDb->max('CTUSIG', 'MAXIMO')->from('ENSIGN')->where('INGSIG', '=', $tnIngreso)->get("array");
					if(is_array($laConsecutivo)==true){
						if(count($laConsecutivo)>0){
							$lnConsec = $laConsecutivo['MAXIMO']; settype($lnConsec,'integer');
						}
					}			
					break;

				default:
				   $tcTipo = "";
			}		
		}
		
		if(empty($tcTipo)==false){
			$lnConsec = $lnConsec+1;
		}
		return $lnConsec;
	}
	
	public function RegistrarSignos($tnIngreso=0, object $toDataEnsign){
		$llResultado = false;
		
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$ltAhora 	= new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha 	= (empty($toDataEnsign->tcFecha)) ? $ltAhora->format("Ymd") : $toDataEnsign->tcFecha;
				$lcHora  	= (empty($toDataEnsign->tcHora))  ? $ltAhora->format("His") : $toDataEnsign->tcHora;
				$lcFechaSis = $ltAhora->format("Ymd");
				$lcHoraSis 	= $ltAhora->format("His");
				$lcUsuario 	= (isset($_SESSION[HCW_NAME]) ? $_SESSION[HCW_NAME]->oUsuario->getUsuario(): $toDataEnsign->tcUser);
				
				$lcTabla 	= 'ENSIGN';
				$lnNota 	= $this->CalcularConsNota($tnIngreso);
				$lnSigno	= $this->CalcularConsecutivo('SIGNOS', $tnIngreso, $lnNota);
				$lcPrograma = (empty($toDataEnsign->tcPrograma)) ?  substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10) : $toDataEnsign->tcPrograma;
				

				// Insertando la nueva lectura
				if($lnNota>0 && $lnSigno>0 && !empty($lcUsuario)){
					$laDatos = [
						'INGSIG'=>$tnIngreso,
						'CONSIG'=>$lnNota,
						'CTUSIG'=>$lnSigno,
						'FDISIG'=>$lcFecha,	
						'HDISIG'=>$lcHora,				
						'OBSSIG'=>$toDataEnsign->tcObservacion,
						'FRRSIG'=>$toDataEnsign->tnFr,
						'SATSIG'=>$toDataEnsign->tnSO2,
						'TMPSIG'=>floatval(sprintf("%01.1f", $toDataEnsign->tnT)),
						'FRCSIG'=>$toDataEnsign->tnFC,
						'TASSIG'=>$toDataEnsign->tnTAS,
						'TADSIG'=>$toDataEnsign->tnTAD,
						'TAMSIG'=>$toDataEnsign->tnTAM,
						'USRSIG'=>$lcUsuario,
						'PGMSIG'=>$lcPrograma,
						'FECSIG'=>$lcFechaSis,
						'HORSIG'=>$lcHoraSis,
						"ESTSIG"=>$toDataEnsign->tcEstado
					];
					$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);
				}
			}
		}
		
		return $llResultado;
	}
}

?>