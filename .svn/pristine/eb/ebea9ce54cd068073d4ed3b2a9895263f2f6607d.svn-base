<?php
namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Citas {
	
	public $cPrograma = '';

    public function __construct (){
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
    }


	public function crearCitaTelemedicina($tcUsuario='', $tcIdTipo='', $tnId=0, $tnCita=0, $tnConsulta = 0, $tnEvolucion=0, $tnIngreso=0, $tcCodEspecialidad='', $tcCodCups='', $tnFechaCrea=0, $tnFechaIniCita=0, $tnHoraIniCita=0, $tcRegMedico='', $tnFechaFinCita='0', $tnHoraFinCita=0, $tcTeleconsulta='', $tnEstadoCita=0, $tcUnidadAgenda='', $tcConsecOrden='', $tcCodViaIngreso=''){
		
		$tcUsuario = strval($tcUsuario);
		$llResultado = false;
		$lcTabla = 'JTMCIT';

		global $goDb;
		if(isset($goDb)){

			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");			

			$lcJTMUID=$this->generarLlaves(3,'-',3);
			$lcJTMKEY=$this->generarLlaves(1,'',8);
			$tcTeleconsulta = 'S';
			
			if($this->existeCita($tcIdTipo, $tnId, $tnCita, $tnConsulta, $tnEvolucion)==false){

				$laDatos = [
								'TIDCIT'=>$tcIdTipo,
								'NIDCIT'=>$tnId,
								'CCICIT'=>$tnCita,
								'CCOCIT'=>$tnConsulta,
								'EVOCIT'=>$tnEvolucion,
								'NINCIT'=>$tnIngreso,
								'CODCIT'=>$tcCodEspecialidad,
								'COACIT'=>$tcCodCups,
								'FCOCIT'=>$tnFechaCrea,
								'FRLCIT'=>$tnFechaIniCita,
								'HOCCIT'=>$tnHoraIniCita,
								'RMRCIT'=>$tcRegMedico,
								'FERCIT'=>$tnFechaFinCita,
								'HRLCIT'=>$tnHoraFinCita,
								'CONCIT'=>$tcTeleconsulta,
								'ESTCIT'=>$tnEstadoCita,
								'ESCCIT'=>$tcUnidadAgenda,
								'INSCIT'=>$tcConsecOrden,
								'VIACIT'=>$tcCodViaIngreso,
								'JTMUID'=>$lcJTMUID,
								'JTMKEY'=>$lcJTMKEY,
								'USRCIT'=>$tcUsuario,
								'PGMCIT'=>$this->cPrograma,
								'FECCIT'=>$lcFecha,
								'HORCIT'=>$lcHora];
				$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);	


				$laDatos = ['CONCIT'=>$tcTeleconsulta];
				$goDb->tabla('RIACIT')->where('TIDCIT','=',$tcIdTipo)->where('NIDCIT','=',$tnId)->where('CCICIT','=',$tnCita)->where('CCOCIT','=',$tnConsulta)->where('EVOCIT','=',$tnEvolucion)->actualizar($laDatos);	

				
			}
		}
				
		return $llResultado;
	}
	

    private function existeCita($tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion=0){
		$tcIdTipo = trim(strval($tcIdTipo));
		$tnId = intval($tnId);
		$tnCita = intval($tnCita);
		$tnConsulta = intval($tnConsulta);
		$tnEvolucion = intval($tnEvolucion);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if(empty($tcIdTipo)==false && empty($tnId)==false){
				$laRegistros = $goDb->count('*', 'CUENTA')
									->from('JTMCIT J')
									->where('J.TIDCIT','=',$tcIdTipo)
									->where('J.NIDCIT','=',$tnId)
									->where('J.CCICIT','=',$tnCita)
									->where('J.CCOCIT','=',$tnConsulta)
									->where('J.EVOCIT','=',$tnEvolucion)
									->get('array');
					
				if(is_array($laRegistros)){
					$llResultado = ($laRegistros['CUENTA']>0);
				}
			}
		}
		
		return $llResultado;
	}
	
	
	private function generarLlaves($tnFragmentos=1, $tcSeparadorFragmento='', $tnAnchoFragmento=8){
		$tnFragmentos=intval($tnFragmentos);
		$tnFragmentos=(empty($tnFragmentos)?1:$tnFragmentos);
		$tcSeparadorFragmento = trim(strval($tcSeparadorFragmento));
		$tnAnchoFragmento=intval($tnAnchoFragmento);
		$tnAnchoFragmento=(empty($tnAnchoFragmento)?8:$tnAnchoFragmento);
		$lcAlfabeto='23456789ACDEFHJKLMNPRTWXYZ';
		$lcLlave='';
		
		for($lnFragmento=1;$lnFragmento<=$tnFragmentos;$lnFragmento++){
			$lcLlave .= (empty($lcLlave)==false?$tcSeparadorFragmento:'');
			for($lnCaracter=1;$lnCaracter<=$tnAnchoFragmento;$lnCaracter++){
				$lcLlave .= substr($lcAlfabeto,rand(0,strlen($lcAlfabeto)-1),1);
			}
		}

		return $lcLlave;
	} 	


    public function buscarCitasPaciente($tcDocumento='', $tnDocumento=0){
		$laCitas = array();
				
		global $goDb;
		if(isset($goDb)){
			
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			
			$lcWhere = '';
			$lcWhere .= (!empty($tcDocumento) && !empty($tnDocumento)?sprintf("%s J.TIDCIT='%s'",(empty($lcWhere)?'':' AND '),$tcDocumento):'');
			$lcWhere .= (!empty($tnDocumento)?sprintf("%s J.NIDCIT=%s",(empty($lcWhere)?'':' AND '),$tnDocumento):'');
			$lcWhere .= (!empty($tnDocumento)?sprintf("%s J.NIDCIT=%s",(empty($lcWhere)?'':' AND '),$tnDocumento):'');
			$lcWhere .= (empty($lcWhere)?'':' AND ')." J.FRLCIT>=".$lcFecha;
			$lcWhere .= (empty($lcWhere)?'':' AND ')." (SELECT COUNT(*) FROM JTMCIT M WHERE M.TIDCIT=J.TIDCIT AND M.NIDCIT=J.NIDCIT AND M.CCICIT=J.CCICIT AND M.CCOCIT=J.CCOCIT AND M.EVOCIT=J.EVOCIT)<=0";
			
			$laCampos = [
						'J.TIDCIT DOCUMENTO_TIPO','J.NIDCIT DOCUMENTO','J.CCICIT CITA','J.CCOCIT CONSULTA','J.EVOCIT EVOLUCION','J.NINCIT INGRESO',
						'J.CODCIT ESP_CODIGO_CITA','J.CD2CIT CLASE','J.COACIT PRO_CODIGO','J.FRLCIT CITA_FECHA','J.HOCCIT CITA_HORA','J.RMRCIT REGISTRO_MEDICO',
						'J.FERCIT RELIZA_FECHA','J.HRLCIT RELIZA_HORA','J.CONCIT','J.ESTCIT','J.ESCCIT','J.INSCIT','J.VIACIT','J.FCOCIT',
						"TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE",
						"TRIM(R.NOMMED) || ' ' || TRIM(R.NNOMED) MEDICO",
						'E.CODESP ESP_CODIGO','E.DESESP ESPECIALIDAD',
						'C.CODCUP CUP_CODIGO','C.DESCUP CUP_NOMBRE',
						];
			
			$laCitas = $goDb
							->select($laCampos)
							->from('RIACIT J')
							->leftJoin('RIAPAC P', 'J.TIDCIT=P.TIDPAC AND J.NIDCIT=P.NIDPAC', null)
							->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
							->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
							->leftJoin('RIARGMN R', 'R.REGMED=J.RMRCIT', null)
							->where($lcWhere)
							->orderBy('J.FRLCIT ASC, J.HOCCIT ASC')
							->getAll('array');
		}		
		
		return $laCitas;
    }



    public function buscarCitasTelemedicina($tnIngreso=0, $tcDocumento='', $tnDocumento=0, $tcEspecialidad='', $tcProcedimiento='', $tcInicio='', $tcFin='', $tcEstado='', $tcEspeciales='', $tcMedico=''){
		$laCitas = array();
		
		$lcWhere = '';
		$lcWhere .= (!empty($tnIngreso)?sprintf("%s J.NINCIT=%s",(empty($lcWhere)?'':' AND '),$tnIngreso):'');
		$lcWhere .= (!empty($tcDocumento) && !empty($tnDocumento)?sprintf("%s J.TIDCIT='%s'",(empty($lcWhere)?'':' AND '),$tcDocumento):'');
		$lcWhere .= (!empty($tnDocumento)?sprintf("%s J.NIDCIT=%s",(empty($lcWhere)?'':' AND '),$tnDocumento):'');
		$lcWhere .= (!empty($tcEspecialidad)?sprintf("%s J.CODCIT='%s'",(empty($lcWhere)?'':' AND '),$tcEspecialidad):'');
		$lcWhere .= (!empty($tcProcedimiento)?sprintf("%s J.COACIT='%s'",(empty($lcWhere)?'':' AND '),$tcProcedimiento):'');
		$lcWhere .= (!empty($tcMedico)?sprintf("%s (R.REGMED='%s' OR S.REGMED='%s')",(empty($lcWhere)?'':' AND '),$tcMedico,$tcMedico):'');
		
		if($tcEstado<>'TODOS'){
			if(empty($tcEstado)==false){
				$lcWhere .= sprintf("%s J.ESTADO='%s'",(empty($lcWhere)?'':' AND '),$tcEstado);
			}else{
				$lcWhere .= sprintf("%s J.ESTADO=''",(empty($lcWhere)?'':' AND '));
			}
		}
		
		if(intval($tcInicio)>0 && intval($tcFin)>0){
			$lcWhere .= sprintf("%s (J.FRLCIT BETWEEN %s AND %s)",(empty($lcWhere)?'':' AND '),intval(str_replace('-','',$tcInicio)),intval(str_replace('-','',$tcFin)));
		}
		
		if(!empty($tcEspeciales)){
			switch($tcEspeciales){
				case 'ARCOCUSIN':
					$lcWhere .= sprintf("%s J.ARCCAR>0",(empty($lcWhere)?'':' AND '));
					break;
					
				case 'ARCMOSSIN':
					$lcWhere .= sprintf("%s J.ARCCAR<=0",(empty($lcWhere)?'':' AND '));
					break;
					
				case 'INGMOSSIN':
					$lcWhere .= sprintf("%s J.NINCIT<=0",(empty($lcWhere)?'':' AND '));
					break;
					
				case 'INGOCUSIN':
					$lcWhere .= sprintf("%s J.NINCIT>0",(empty($lcWhere)?'':' AND '));
					break;			
			}
		}
		
		$lcWhere = (empty($lcWhere)?'1=1':$lcWhere);
				
		
		global $goDb;
		if(isset($goDb)){
			$laCampos = [
						'D.ESTORD ESTADO_ORDEN_CODIGO',
						"TRIM(IFNULL((SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA ='ESTPRORD' AND INT(CL1TMA)=D.ESTORD), '')) ESTADO_ORDEN",
						'J.TIDCIT DOCUMENTO_TIPO','J.NIDCIT DOCUMENTO','J.CCICIT CITA','J.CCOCIT CONSULTA','J.EVOCIT EVOLUCION','J.NINCIT INGRESO',
						'J.CODCIT ESP_CODIGO_CITA','J.CD2CIT CLASE','J.COACIT PRO_CODIGO','J.FRLCIT CITA_FECHA','J.HOCCIT CITA_HORA',
						'J.FERCIT RELIZA_FECHA','J.HRLCIT RELIZA_HORA','J.CONCIT','J.ESTCIT','J.ESTADO ESTADO_CODIGO','J.ARCCAR ARCHIVOS', 'J.VALPAC VALORACION_PAC', 'J.VALMED VALORACION_MED',
						'J.FEFCIT RELIZA_FIN_FECHA','J.HRFCIT RELIZA_FIN_HORA',
						'J.NOTFEC NOTIFICACION_FECHA','J.RECFEC RECORDATORIO_HORA',
						"TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE",
						'TRIM(P.TELPAC) TELEFONO1', 'TRIM(P.TETPAC) TELEFONO2', 'TRIM(P.FA2PAC) TELEFONO3', 'TRIM(P.DR1PAC) DIRECCION1', 'TRIM(P.DR2PAC) DIRECCION2',
						'TRIM(P.MAIPAC) EMAIL1',
						'J.RMRCIT REGISTRO_MEDICO_PROGRAMADO',
						"TRIM(TRIM(IFNULL(R.NOMMED,'')) || ' ' || TRIM(IFNULL(R.NNOMED,''))) MEDICO_AGENDADO",					
						'D.RMRORD REGISTRO_MEDICO',
						"TRIM(TRIM(IFNULL(S.NOMMED,'')) || ' ' || TRIM(IFNULL(S.NNOMED,''))) MEDICO",
						'I.RMRCIT REGISTRO_MEDICO_CITA',
						"TRIM(TRIM(IFNULL(T.NOMMED,'')) || ' ' || TRIM(IFNULL(T.NNOMED,''))) MEDICO_CITA",							
						'E.CODESP ESP_CODIGO','E.DESESP ESPECIALIDAD',
						'C.CODCUP CUP_CODIGO','C.DESCUP CUP_NOMBRE',
						"TRIM(IFNULL(X.TELEFO,'')) TELEFONO4","TRIM(IFNULL(X.DIRECC,'')) DIRECCION3","TRIM(IFNULL(X.CORREO,'SIN-REGISTRAR-PP')) EMAIL2",
						'Z.DE1TMA ESTADO',
						];
			
			$laCitas = $goDb
							->select($laCampos)
							->from('JTMCIT J')
							->leftJoin('RIAORD D', 'J.TIDCIT=D.TIDORD AND J.NIDCIT=D.NIDORD AND J.CCICIT=D.CCIORD', null)
							->leftJoin('RIACIT I', 'J.TIDCIT=I.TIDCIT AND J.NIDCIT=I.NIDCIT AND J.CCICIT=I.CCICIT', null)
							->leftJoin('RIAPAC P', 'J.TIDCIT=P.TIDPAC AND J.NIDCIT=P.NIDPAC', null)
							->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
							->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
							->leftJoin('RIARGMN R', 'R.REGMED=J.RMRCIT', null)
							->leftJoin('RIARGMN S', 'S.REGMED=D.RMRORD', null)
							->leftJoin('RIARGMN T', 'T.REGMED=I.RMRCIT', null)
							->leftJoin('PACMENSEG X', 'X.IDTIPO=J.TIDCIT AND X.IDNUME=J.NIDCIT', null)
							->leftJoin('TABMAE Z', "Z.CL3TMA=J.ESTADO AND Z.TIPTMA='TELEMED' AND Z.CL1TMA='CITAS' AND Z.CL2TMA='01010101'", null)
							->where($lcWhere)
							->orderBy('J.FRLCIT ASC, J.HOCCIT ASC')
							->getAll('array');
		}		
		
		return $laCitas;
    }

}
?>