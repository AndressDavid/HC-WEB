<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class SalasAperturaSimple{
	private $cPrograma = '';
	
    public function __construct (){
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
    }
	
	public function salasPermitenAperturaSimple(){
		$laSalas=array();
		
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['DE2TMA SALA'];
			$laSalasAux = $goDb
			->select($laCampos)
			->tabla('TABMAE')
			->where('TIPTMA','=','CXCENCOS')
			->where('CL2TMA','=','1')
			->where('CL3TMA','=','1')
			->where('ESTTMA','<>','I')
			->get("array");
			
			if(is_array($laSalas)==true){
				$laSalas = array_map('trim', $laSalas);
				foreach($laSalasAux as $lcSalaAux){
					$lcSalaAux = str_replace("'","",$lcSalaAux);
					$laSalasLista=explode(',',$lcSalaAux);
					foreach($laSalasLista as $lcSalaLista){
						$laSalas[]=trim($lcSalaLista);
					}
				}
			}
		}
		
		return $laSalas;		
	}


	public function salasAutorizadasUsuario($tcUsuario=''){	
		$laSalas=array();
		
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(F.PGMMIT) SALA', 'B.TABDSC NOMBRE'];
			$laSalas = $goDb
			->select($laCampos)
			->tabla('FACSMIT F')
			->leftJoin('PRMTAB B', "B.TABTIP='014' AND F.PGMMIT=B.TABCOD", null)
			->where('F.USRMIT','=',$tcUsuario)
			->where('F.OPCMIT','=','9')
			->getAll("array");
		}
				
		return $laSalas;
	}
	
	public function salasPorTipo($tnIngreso=0, $tcTipo=''){	
		$laSalas=array();
		
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(F.SECHAB) || TRIM(F.NUMHAB) CODIGO', 'F.SECHAB SECCION'];
			$laSalasAux = $goDb
			->select($laCampos)
			->tabla('FACHABL0 F')
			->where('F.SECHAB','=',$tcTipo)
			->where('F.IDDHAB','=','0')
			->orderBy('F.SECHAB, F.NUMHAB')
			->getAll("array");
			
			if(is_array($laSalasAux)==true){
				if($tnIngreso>0){
					$laSalasAbiertas = $this->buscarCirugiasTipoSala($tnIngreso, $tcTipo, true);
					foreach($laSalasAux as $laSalaAux){
						if(in_array($laSalaAux['CODIGO'],$laSalasAbiertas)==false){
							$laSalas[]=['CODIGO'=>$laSalaAux['CODIGO'], 'SECCION'=>$laSalaAux['SECCION']];
						}
					}
				}else{
					$laSalas = $laSalasAux;
				}
			}
			
		}
		
		return $laSalas;
	}


	public function buscarDiagnosticoEntrada($tnIngreso=0){	
		$laDiagnosticos=array();
		
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['H.SUBORG DIAGNOSTICO'];
			$laDiagnosticos = $goDb
				->select($laCampos)
				->tabla('RIAHIS15 H')
				->where('H.NROING','=',$tnIngreso)
				->where('H.INDICE','=',25)
				->get("array");
		}
		
		return $laDiagnosticos;
	}	

		
	public function salaCentroServicio($tcTipo='', $tnModo=1){	
		$laCentroServicio=array();
		
		global $goDb;
		if(isset($goDb)){
			if($tnModo==1){		
				$laCentroServicio = $goDb
					->select(['TRIM(SUBSTR(A.TABDSC, 1, 4)) CODIGO', "TRIM(IFNULL(B.TABDSC, '')) NOMBRE"])
					->tabla('PRMTAB A')
					->where(sprintf("A.TABTIP='SCS' AND A.TABCOD='%s'",$tcTipo))
					->leftJoin('PRMTAB B', "B.TABTIP='CSE' AND B.TABCOD=SUBSTR(A.TABDSC, 1, 4)", null)
					->get("array");			
			}else{
				$laCentroServicio = $goDb
					->select(['TRIM(A.DE2TMA) CODIGO', "TRIM(IFNULL(B.TABDSC, '')) NOMBRE"])
					->tabla('TABMAE A')
					->where("A.TIPTMA='CXCENCOS' AND A.CL2TMA='2' AND A.CL3TMA='2'")
					->leftJoin('PRMTAB B', "B.TABTIP='CSE' AND B.TABCOD=A.DE2TMA", null)
					->get("array");				
			}
			
		}
				
		return $laCentroServicio;
	}
	
	public function buscarCirugiasTipoSala($tnIngreso=0, $tcTipo='', $tlSigla=false){
		$laSalas=array();
		
		global $goDb;
		if(isset($goDb)){
			$laSalasAux = $goDb
				->select(['INGCRH INGRESO', 'CNSCRH CONSECUTIVO', 'ESTCRH ESTADO', 'SLRCRH SALA', 'SALCIR TIPO'])
				->tabla('FACCIRHL01 A')
				->where('A.INGCRH','=',$tnIngreso)
				->where('A.ESTCRH','=',0)
				->where('A.SALCIR','=',$tcTipo)
				->getAll("array");						
			
			if(is_array($laSalasAux)==true){
				if($tlSigla==true){
					foreach($laSalasAux as $laSalaAux){
						$laSalas[]=$laSalaAux['SALA'];
					}
				}else{
					$laSalas = $laSalasAux;
				}
			}
		}
				
		return $laSalas;	

	}
	
	public function buscarCirugias($tnIngreso=0, $tcDocumento='', $tnDocumento=0, $tcInicio='', $tcFin='', $tcEstado='', $tcSala='', $tcCentroServicio=''){
		$laSalas = array();
		$lnInicio = intval(str_replace('-','',$tcInicio));
		$lnFin = intval(str_replace('-','',$tcFin));
		$lcWhere='F.INGCRH>0';

		if(!empty($tnIngreso)){
			$lcWhere .= sprintf(' AND F.INGCRH=%s',$tnIngreso);
		}
		
		if(!empty($tcDocumento) && !empty($lnFin)){
			$lcWhere .= sprintf(" AND I.TIDING='%s' AND I.NIDING=%s", $tcDocumento, $tnDocumento);
		}
		
		if(!empty($lnInicio) && !empty($lnFin)){
			$lcWhere .= sprintf(" AND F.FHRCRH BETWEEN %s AND %s", $lnInicio, $lnFin);
		}	

		if($tcEstado=='0'){
			$lcWhere .= " AND (F.ESTCRH = '0' or F.ESTCRH = '')";
		}else if(!empty(strval($tcEstado))){
			$lcWhere .= sprintf(" AND F.ESTCRH = '%s'",$tcEstado);
		}
		
		if(!empty($tcSala)){
			$lcWhere .= sprintf(" AND F.SLRCRH LIKE '%s'",trim(strval($tcSala)).'%');
		}
		
		if(!empty($tcCentroServicio)){
			$lcWhere .= sprintf(" AND F.CTRCRH = '%s'",$tcCentroServicio);
		}		

		
		global $goDb;
		if(isset($goDb)){
			$laCampos = [
							'F.INGCRH INGRESO','F.CNSCRH CONSECUTIVO', 'F.ESTCRH ESTADO', 'F.FHRCRH FECHA', 'F.HRRCRH HORA', 'F.SLRCRH SALA', 'TRIM(F.CTRCRH) CSE',
							"IFNULL(B.TABDSC,'') CSE_NOMBRE",
							"IFNULL(I.TIDING,'') DOCTIPO", "IFNULL(I.NIDING,0) DOCNUMERO",
							"TRIM(IFNULL(P.NM1PAC,'')) || ' ' || TRIM(IFNULL(P.NM2PAC,'')) || ' ' || TRIM(IFNULL(P.AP1PAC,'')) || ' ' || TRIM(IFNULL(P.AP2PAC,'')) PACIENTE",
							"TRIM(IFNULL(H.SECHAB,'')) || '-' || TRIM(IFNULL(H.NUMHAB,'')) HABITACION",
						 ];
			$laSalas = $goDb
			->select($laCampos)
			->tabla('FACCIRH F')
			->leftJoin('PRMTAB B', "B.TABTIP='CSE' AND F.CTRCRH=B.TABCOD", null)
			->leftJoin('RIAING I', "I.NIGING=F.INGCRH", null)
			->leftJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->leftJoin('FACHAB H', 'H.TIDHAB=P.TIDPAC AND H.NIDHAB=P.NIDPAC AND H.INGHAB=F.INGCRH', null)
			->where($lcWhere)
			->orderBy('F.CNSCRH')
			->getAll("array");
		}
		
		
		return $laSalas;		
	}
	
	private function ultimaAperturaCerrada($tnIngreso=0, $tcEstadoAbierto='0', $tcEstadoBorrado='2', $tcSala=''){
		$lnConsecutivo = 0;
		
		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso) && !empty($tcSala) ){
				$laSalasAux = $goDb
					->select(['A.CNSCRH CONSECUTIVO'])
					->tabla('FACCIRHL01 A')
					->where('A.INGCRH','=',$tnIngreso)
					->where('A.SALCIR','=',$tcSala)
					->notIn('A.ESTCRH',[$tcEstadoAbierto, $tcEstadoBorrado])
					->orderBy('A.CNSCRH', 'DESC')
					->get("array");						
				
				if(is_array($laSalasAux)==true){
					$lnConsecutivo = intval($laSalasAux['CONSECUTIVO']);
				}
			}
		}
				
		return $lnConsecutivo;		
	}
	
	private function cuentaProcedimientosCirugia($tnIngreso=0){
		$lnRegistros = 0;
		
		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso)){
				$laSalasAux = $goDb
					->count('A.INGEST', 'REGISTROS')
					->tabla('RIAESTM A')
					->where('A.INGEST','=',$tnIngreso)
					->where('A.CNCEST','>=',1)
					->where('A.NPREST','=','0')
					->where('A.TINEST','=','400')
					->where('A.ESFEST','<>','5')
					->get("array");						
				
				if(is_array($laSalasAux)==true){
					$lnRegistros = intval($laSalasAux['REGISTROS']);
				}
			}
		}
				
		return $lnRegistros;		
	
	}	
	
	
	private function cuentaElementosCirugia($tnIngreso=0, $tnConsecutivo=0, $taTipos=[]){
		$lnRegistros = 0;
		
		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso) && !empty($tnConsecutivo) ){
				$laSalasAux = $goDb
					->count('A.INGEST', 'REGISTROS')
					->tabla('RIAESTM A')
					->where('A.INGEST','=',$tnIngreso)
					->where('A.CNCEST','=',$tnConsecutivo)
					->where('A.NPREST','=','0')
					->where('A.ESFEST','<>','5')
					->in('A.TINEST',$taTipos)
					->get("array");						
				
				if(is_array($laSalasAux)==true){
					$lnRegistros = intval($laSalasAux['REGISTROS']);
				}
			}
		}
				
		return $lnRegistros;
	}
	
	private function nuevoConsecutovoApertura($tnIngreso=0){
		$lnConsecutivo = 0;
		
		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso)){
				$laSalasAux = $goDb
					->select(['A.CNSCRH CONSECUTIVO'])
					->tabla('FACCIRH A')
					->where('A.INGCRH','=',$tnIngreso)
					->orderBy('A.CNSCRH', 'DESC')
					->get("array");						
				
				if(is_array($laSalasAux)==true){
					$lnConsecutivo = intval($laSalasAux['CONSECUTIVO']);
				}
			}
		}
				
		return $lnConsecutivo+1;		
	}	
	
	private function obtenerDiagnosticoEntrada($tnIngreso=0){
		$lcDiagnostico = '';
		
		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso)){
				$laDiagnsticos = $goDb
					->select(['A.SUBORG DIAGNOSTICO'])
					->tabla('RIAHIS15 A')
					->where('A.NROING','=',$tnIngreso)
					->where('A.INDICE','=',25)
					->get("array");						
				
				if(is_array($laDiagnsticos)==true){
					$lcDiagnostico = trim(strval($laDiagnsticos['DIAGNOSTICO']));
				}
			}
		}
				
		return $lcDiagnostico;	
	}	
		
	public function guardarAperturaSalaSimple($tnIngreso=0, $tcSala='', $tcSalaNumero='', $tcCentroServicio='', $tcUsuario=''){
		$laResultado = ['ERROR'=>false, 'MENSAJE'=>'', 'ANTERIORES'=>false];

		$lcEstadoAbierto = '0';
		$lcEstadoBorrado = '2';
		$llModifica = true;

		
		$tnIngreso = intval($tnIngreso);
		$tcSala = trim(strval($tcSala));
		$tcSalaNumero = trim(strval($tcSalaNumero));
		$tcCentroServicio = trim(strval($tcCentroServicio));

		global $goDb;
		if(isset($goDb)){		
			if (!empty($tnIngreso) && !empty($tcSala) && !empty($tcSalaNumero) && !empty($tcCentroServicio)){

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");
				$lnConsecutivo = $this->ultimaAperturaCerrada($tnIngreso, $lcEstadoAbierto, $lcEstadoBorrado, $tcSala);

				if($lnConsecutivo>0){
					$lnProcedimientosCirugia = $this->cuentaProcedimientosCirugia($tnIngreso);

					if($lnProcedimientosCirugia>0){
						$lnElementosCirugia = $this->cuentaElementosCirugia($tnIngreso, $lnConsecutivo, ['400']);
						
						if($lnElementosCirugia>0){
							
							$lnElementosCirugia = $this->cuentaElementosCirugia($tnIngreso, $lnConsecutivo, ['500', '600']);
							if($lnElementosCirugia<=0){
								$laResultado = ['ERROR'=>true, 'MENSAJE'=>'No existen Medicamentos/Elementos cargados a esta cirugía, revise por favor'];
							}
						}else{							
							$laResultado = ['ERROR'=>true, 'MENSAJE'=>'No existen Procedimientos cargados a esta cirugía, revise por favor'];
						}
					}
				}

				if($laResultado['ERROR']==false){
					
					$laSalasAbiertasTipo = $this->buscarCirugiasTipoSala($tnIngreso, $tcSala);
					$lnConsecutivo = $this->nuevoConsecutovoApertura($tnIngreso);
					$lcDiagnostico = $this->obtenerDiagnosticoEntrada($tnIngreso);

					$laDatos = ['INGCRH'=>$tnIngreso,
								 'CNSCRH'=>$lnConsecutivo,
								 'ESTCRH'=>$lcEstadoAbierto,
								 'FHRCRH'=>$lcFecha,
								 'HRRCRH'=>$lcHora,
								 'SLRCRH'=>$tcSalaNumero,
								 'CTRCRH'=>$tcCentroServicio,
								 'DG1CRH'=>$lcDiagnostico,
								 'USRCRH'=>$tcUsuario,
								 'PGMCRH'=>$this->cPrograma,
								 'FECCRH'=>$lcFecha,
								 'HORCRH'=>$lcHora
								 ];
					$llResultado = $goDb->from('FACCIRH')->insertar($laDatos);
					$laResultado = ['ERROR'=>!$llResultado, 'MENSAJE'=>($llResultado==true?'Registro almacenado':'No se pudo guardar el regustro'), 'INGRESO'=>$tnIngreso, 'CONSECUTIVO'=>$lnConsecutivo];

					if($llModifica==true && $laResultado['ERROR']==false){
						foreach($laSalasAbiertasTipo as $laSalaAbiertaTipo){
							if($lnConsecutivo!=$laSalaAbiertaTipo['CONSECUTIVO']){
								$lcWhere = sprintf("INGCRH=%s AND CNSCRH=%s AND ESTCRH='0' AND SLRCRH='%s'", $tnIngreso, $laSalaAbiertaTipo['CONSECUTIVO'], $laSalaAbiertaTipo['SALA']);
								$laDatos = ['ESTCRH'=>$lcEstadoBorrado,
											 'UMOCRH'=>$tcUsuario,
											 'PMOCRH'=>$this->cPrograma,
											 'FMOCRH'=>$lcFecha,
											 'HMOCRH'=>$lcHora
											];
								$llResultado = $goDb->tabla('FACCIRH')->where($lcWhere)->actualizar($laDatos);
								$laResultado['DEPENDENCIAS'][]=['RESULTADO'=>!$llResultado, 'INGRESO'=>$tnIngreso, 'CONSECUTIVO'=>$laSalaAbiertaTipo['CONSECUTIVO']];
							}
						}
					}
				}
			}else{
				$laResultado = ['ERROR'=>true, 'MENSAJE'=>'Existen campos obligatorios incompleto o no validos'];
			}
		}else{
			$laResultado = ['ERROR'=>true, 'MENSAJE'=>'NO hay conexi&oacute;n'];
		}			

		return $laResultado;
	}
	
}
?>
