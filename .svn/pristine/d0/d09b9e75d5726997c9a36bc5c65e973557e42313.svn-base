<?php

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';

use NUCLEO\Db;

class EscalaSadPersons{

	protected $oDb = null;
	public $aElemSadpersons = [];
	public $aInterSadPersons = [];
	public $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct(){
		global $goDb;
		$this->oDb = $goDb;
	}

	public function cargarDatosSadPersons(){
		$laCampos = ['trim(CL2TMA) CL2TMA', 'trim(CL3TMA) CL3TMA', 'trim(DE1TMA) DE1TMA', 'trim(DE2TMA) DE2TMA', 'trim(OP1TMA) OP1TMA', 'OP3TMA', 'OP4TMA', 'trim(OP5TMA) OP5TMA', 'trim(OP6TMA) OP6TMA', 'OP7TMA'];
		$laCondiciones = ['TIPTMA'=>'ESCSADP', 'CL1TMA'=>'SADPERSO', 'ESTTMA'=>'', ]; 
		$laElemSadpersons = $this->oDb
							->select($laCampos)
							->from('TABMAE')
							->where($laCondiciones)
							->getAll('array');
							
	
		
		if(is_array($laElemSadpersons ) == true){
			if(count($laElemSadpersons)>0){
				foreach($laElemSadpersons as $lcClave=>$itemSadpersons ){
					$laElemSadpersons[$lcClave] = array_map('trim', $itemSadpersons);
				}
				$this->aElemSadpersons = $laElemSadpersons;
				return $laElemSadpersons;
			}
		}
	}
	
	public function interpretarEscalaSadPersons(){
		$laCampos = ['CL3TMA', 'DE2TMA', 'OP3TMA', 'OP4TMA', 'OP5TMA', 'OP7TMA'];
		$laCondiciones = ['TIPTMA'=>'ESCSADP', 'CL1TMA'=>'SADPERSO', 'CL2TMA'=>'INTERP'];
		$laInterSadPersons = $this->oDb
							->select($laCampos)
							->from('TABMAE')
							->where($laCondiciones)
							->getAll('array');
		if(is_array($laInterSadPersons ) == true){
			if(count($laInterSadPersons)>0){
				foreach($laInterSadPersons as $lcClave=>$itemInterSadPersons ){
					$laInterSadPersons[$lcClave] = array_map('trim', $itemInterSadPersons);
				}
				$this->aInterSadPersons = $laInterSadPersons;
				return $laInterSadPersons;
			}
		}
	}

	public function validarEscalaSadPersons($taDiagnosticos=[], $taDatos=[]){
		$laCampos = ['trim(CL2TMA) CL2TMA', 'trim(CL3TMA) CL3TMA', 'trim(DE2TMA) DE2TMA', 'OP3TMA', 'OP4TMA', 'trim(OP5TMA) OP5TMA', 'trim(OP6TMA) OP6TMA', 'OP7TMA'];
		$laCondiciones = ['TIPTMA'=>'ESCSADP', 'CL1TMA'=>'SADPERSO', 'ESTTMA'=>'', ];
		$laSadPersons = $this->oDb
							->select($laCampos)
							->from('TABMAE')
							->where($laCondiciones)
							->getAll('array');
		if(is_array($laSadPersons ) == true){
			if(count($laSadPersons) > 0){
				foreach($laSadPersons as $lnIndice => $laRegistro){
					if (trim($laRegistro['CL2TMA']) == "ELEM"){
						$laCodigosSadPersons[] = [
							'CL3TMA'=>trim($laRegistro['CL3TMA']),
						];
					}
					if (trim($laRegistro['CL2TMA']) == "DX"){
						$lcLstDxSadPersons = preg_replace("/'/", '', trim($laRegistro['DE2TMA']));
					}
				}
				$laDxSadPersons = explode(",", $lcLstDxSadPersons);
				
				if(count($taDiagnosticos) > 0){
					$lbExiste = 0;
					foreach($taDiagnosticos as $lcDiagnostico){
						if(in_array($lcDiagnostico['CODIGO'], $laDxSadPersons)){
							$lbExiste++;
						}
					}

					if($lbExiste > 0){
						$lnCantidadDatos = (count($taDatos ));
						$lnSadPersons = count($laCodigosSadPersons);
						
						if($lnSadPersons > 0 && $lnCantidadDatos > 0){
							if($lnSadPersons != $lnCantidadDatos){
								$this->aError = [
									'Mensaje' =>'Número incorrecto de valores SAD PERSONS',
									'Objeto'  => 'cboSiNoesad01',
									'Valido'=>false,
								];
							}else{
								foreach($laCodigosSadPersons as $lnIndice => $laRegistro){
									$lcCodigosSadPersons[$lnIndice] = trim($laRegistro['CL3TMA']);
								}
								foreach($taDatos as $lnIndice => $laElemento){
									$lcLetrastaDatos[$lnIndice] = trim($laElemento['lcCodigo']);

									switch(true){
										case !in_array($laElemento['lcCodigo'], $lcCodigosSadPersons):
											$this->aError = [
												'Mensaje' => 'Código no corresponde',
												'Objeto'  => 'cboSiNoesad'.$lnIndice,
												'Valido'  => false,
											];
											break 2;

										case !in_array(intval($laElemento['lnValor']), [0,1]) :
											$this->aError = [
												'Mensaje'=>'Valor no corresponde',
												'Objeto'=>'cboSiNoesad'.$lnIndice,
												'Valido'=>false,];
											break 2;

										case $laElemento['lnValor'] == "" :
											$this->aError = [
												'Mensaje'=>$lcCodigosSadPersons[$lnIndice] . ' debe ser valorado',
												'Objeto'=>'cboSiNoesad'.$lnIndice,
												'Valido'=>false,
											];
											break 2;
									}
								}
							}
						}else{
							$this->aError = [
								'Mensaje' =>'Faltan datos para valorar la escala SAD PERSONS',
								'Objeto'  => 'cboSiNoesad01',
								'Valido'=>false,
							];
						}
					}else{
						$this->aError = [
							'Mensaje' =>'No existe parametrizacion de Dx que requieren puntaje SAD PERSONS',
							'Objeto'  => 'cboSiNoesad01',
							'Valido'=>true,
						];
					}
				}else{
					$this->aError = [
						'Mensaje' =>'No existen Diagnosticos',
						'Objeto'  => 'cboSiNoesad01',
						'Valido'=>false,
					];
				}
			}
		}
		return $this->aError;
	}
	
	public function guardarDatosEscSadPersonsHC($taDatos=[], $tnIngreso=0, $tcCodiVia, $tnConCon=1, $tnConEvo=0,
									$tnEntidad=0, $tnTidhis, $tnIdenti, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre=''){
		$lcTexto = 'Puntaje SADPERSONS: ' . $taDatos[0]['lnPuntajeSad'] . str_repeat(' ', 4) . $taDatos[0]['lcInterpretacionSad'];
		$lcSadPersons ='';
		foreach ($taDatos as $laRegistro){
			$lcSadPersons .= $laRegistro['lcCodigo'] . '=' . $laRegistro['lcCboValor'] . ';';
		}

		if($_SESSION[HCW_NAME]->oUsuario->getRequiereAval()){
			if($tcPrgCre=='HCPPALWEB'){
				$lcTipo = 'HC';
				$lcTabla = 'HISINTL01';
				$laDatosEscala = [
					'INGHIN' => $tnIngreso,
					'TIPHIN' => $lcTipo,
					'CCOHIN' => $tnConCon,
					'INDHIN' => 35,
					'SUBHIN' => 4,
					'CODHIN' => 0,
					'CLNHIN' => 1,
					'DESHIN' => $lcSadPersons,
					'OP5HIN' => $lcTexto,
					'USRHIN' => $tcUsuCre,
					'PGMHIN' => $tcPrgCre,
					'FECHIN' => $tcFecCre,
					'HORHIN' => $tcHorCre,
				];
			}else{
				$lcTipo = ($tcPrgCre=='EVOPIWEB'?'EP':($tcPrgCre=='EVOURWEB'?'ER':($tcPrgCre=='EVOUNWEB'?'EU':'')));
				$lcTabla = 'REINDE';
				$laDatosEscala = [
					'INGRID'=>$tnIngreso,
					'TIPRID'=>$lcTipo,
					'CONRID'=>$tnConCon,
					'CEXRID'=>4,
					'CLIRID'=>4,
					'INDRID'=>30,
					'IN2RID'=>5954,
					'DIARID'=>'ESCALA SAD PERSONS',
					'DESRID'=>$lcTexto,
					'OP5RID'=>$lcSadPersons,
					'USRRID'=>$tcUsuCre,
					'PGMRID'=>$tcPrgCre,
					'FECRID'=>$tcFecCre,
					'HORRID'=>$tcHorCre,
				];
			}
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosEscala);
		}else{
			if($tcPrgCre=='HCPPAL'){
				$lnLongitud = 70;
				$lnLinea = 1;
				$lnInicio = 0;
				$lnInd = 0;
				$tcTipoTratamiento='';
				$lcTipoTratamiento = gettype($tcTipoTratamiento) =='string' ? $tcTipoTratamiento : '';
				$lnChar = mb_strlen(trim($lcTexto));
				$lnLineas = (ceil($lnChar/$lnLongitud) == 0 ? 1: ceil($lnChar/$lnLongitud)) + $lnLinea -1;
				for($lnInd = $lnLinea; $lnInd <= $lnLineas; $lnInd++){
					$lcDescrip = substr(trim($lcTexto), $lnInicio, $lnLongitud);
					$laDatosRIAHISL1 = [
							'NROING' => $tnIngreso,
							'CONCON' =>	$tnConCon,
							'INDICE' => 35,
							'SUBIND' => 4,
							'SUBHIS' => 0,
							'SUBORG' => '',
							'CODIGO' => 0,
							'CONSEC' => $lnInd,
							'DESCRI' => $lcDescrip,
							'NITENT' => $tnEntidad,
							'TIDHIS' => $tnTidhis,
							'NIDHIS' => $tnIdenti,
							'FILLE3' => $lcTipoTratamiento,
							'USRHIS' => $tcUsuCre,
							'PGMHIS' => $tcPrgCre,
							'FECHIS' => $tcFecCre,
							'HORHIS' => $tcHorCre,
					];
					$llResultado = $this->oDb->tabla('RIAHISL1')->insertar($laDatosRIAHISL1);
					$lnInicio = $lnInicio + $lnLongitud;
				}
			}else{
				$lcTipo = ($tcCodiVia=='01'?'HCURG':($tcCodiVia=='02'?'HCCEX':'HCHOS'));
				$laDatosESCHCL = [
						'INGEHC' => $tnIngreso,
						'TIPEHC' => $lcTipo,
						'CHCEHC' => $tnConCon,
						'CEVEHC' => $tnConEvo,
						'INDEHC' => 1,
						'IN2EHC' => 4,
						'DESEHC' => $lcSadPersons,
						'OP5EHC' =>	$lcTexto,
						'USREHC' => $tcUsuCre,
						'PGMEHC' => $tcPrgCre,
						'FECEHC' => $tcFecCre,
						'HOREHC' => $tcHorCre,
				];
				$llResultado = $this->oDb->tabla('ESCHCL')->insertar($laDatosESCHCL);
			}
		}
	}
}