<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Laboratorio
{
    public function __construct ()
	{
    }


	public function definirParametro($tcTipoParametro='', $tcSubTipoParametro='', $tcCodigo='', $tcDescripcion='', $tnMaximo=0, $tnMinimo=0, $tcUnidad='', $tcUsuario='')
	{
		$llResultado = false;
		$tcTipoParametro = strtoupper(substr(empty($tcTipoParametro)?'GENERAL':trim($tcTipoParametro),0,8));
		$tcSubTipoParametro = strtoupper(substr(empty($tcSubTipoParametro)?'GENERAL':trim($tcSubTipoParametro),0,8));
		$tcCodigo = strtoupper(substr(trim($tcCodigo),0,8));
		$tcDescripcion = strtoupper(substr(trim($tcDescripcion),0,220));
		$tcUnidad = strtoupper(substr(trim($tcUnidad),0,220));
		$tcUsuario = strtoupper(substr(trim($tcUsuario),0,10));

		global $goDb;
		if(isset($goDb)){
			if(!empty($tcCodigo) && !empty($tcDescripcion)){
				$lcTabla = 'TABMAE';
				$lcTipo = 'LABRES';
				$lcClasificacion ='01010101';
				$lcWhere = sprintf("TIPTMA ='%s' AND CL1TMA='%s' AND CL2TMA='%s' AND CL3TMA='%s' AND CL4TMA='%s'",$lcTipo,$lcClasificacion,$tcTipoParametro,$tcSubTipoParametro,$tcCodigo);
				$laRegistros = $goDb->count('*', 'REGISTROS')->from($lcTabla)->where($lcWhere)->get('array');

				if(isset($laRegistros)==true){
					$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
					$lcFecha = $ltAhora->format("Ymd");
					$lcHora  = $ltAhora->format("His");

					$laDatos = ['TIPTMA'=>$lcTipo,
								'CL1TMA'=>$lcClasificacion,
								'CL2TMA'=>$tcTipoParametro,
								'CL3TMA'=>$tcSubTipoParametro,
								'CL4TMA'=>$tcCodigo,
								'DE1TMA'=>$tcDescripcion,
								'OP4TMA'=>$tnMinimo,
								'OP7TMA'=>$tnMaximo,
								'OP5TMA'=>$tcUnidad
								];

					if($laRegistros["REGISTROS"]<=0){
						$laDatos['USRTMA']=$tcUsuario;
						$laDatos['PGMTMA']=substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
						$laDatos['FECTMA']=$lcFecha;
						$laDatos['HORTMA']=$lcHora;
						$llResultado = $goDb->from($lcTabla)->insertar($laDatos);
					} else {
						$laDatos['UMOTMA']=$tcUsuario;
						$laDatos['PMOTMA']=substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
						$laDatos['FMOTMA']=$lcFecha;
						$laDatos['HMOTMA']=$lcHora;
						$llResultado = $goDb->from($lcTabla)->where($lcWhere)->actualizar($laDatos);
					}
				}
			}
		}
		return $llResultado;
	}


	public function registrarResultado(
		$tnIngreso=0,
		$tnOrden=0,
		$tcIdEstudio='',
		$tcCUPEstudio='',
		$tcTecnica='',
		$tcCodVariable='',
		$tcDescVariable='',
		$tcResultado='',
		$tnMaximo=0,
		$tnMinimo=0,
		$tcUnidad='',
		$tcMicroorganismo='',
		$tcAntiobiograma='',
		$tcAntibiotico='',
		$tcSensibilidad='',
		$tnDatoCritico=0,
		$tnIdMedico=0,
		$tcFirmaMedico='',
		$tcFechaValida='0',
		$tcHoraValida='0',
		$tcUsuario=''
		)
	{
		$llResultado = false;
		$lnConsecutivo = 0;
		$lcPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);

		global $goDb;
		if(isset($goDb)){
			if(!empty($tnIngreso) && !empty($tnOrden) && !empty($tcIdEstudio) && !empty($tcCUPEstudio) && !empty($tcCodVariable)){
				$lcTabla = 'LABRES';

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");
				
				$lcCadena = $tnIngreso.$tnOrden.$tcIdEstudio.$tcCUPEstudio.$tcTecnica.$tcCodVariable.$tcDescVariable.$tcResultado.$tnMaximo.$tnMinimo.$tcUnidad.$tcMicroorganismo.$tcAntiobiograma.$tcAntibiotico.$tcSensibilidad.$tnIdMedico.$tcFirmaMedico.$tcFechaValida.$tcHoraValida.$tnDatoCritico;
				$lcMD5 = md5($lcCadena);

				$llFlagExiste = true;
				while($llFlagExiste == true){
					$lnConsecutivo = $goDb->secuencia('SEQ_LABRES');
					if($lnConsecutivo > 0){
						$laRegistro = $goDb
							->select('CONLAB CONSECUTIVO')
							->from($lcTabla)
							->where('CONLAB', '=', $lnConsecutivo)
							->get('array');	
						$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
					}
				}

				$laDatos = [
					'CONLAB'=>$lnConsecutivo,
					'CNTLAB'=>1,
					'ESPLAB'=>'P', /*ESTADO P=PRO R=REP A=ANU*/
					'ESCLAB'=>'', /*CONTROL LEIDO*/
					'NIGING'=>$tnIngreso,
					'CONORD'=>$tnOrden,
					'CONEST'=>$tcIdEstudio,
					'COAORD'=>$tcCUPEstudio,
					'CODVAR'=>$tcCodVariable,
					'RESFEC'=>$tcFechaValida,
					'RESHOR'=>$tcHoraValida,
					'ESTADO'=>'A',
					'CRESUL'=>$tcResultado,
					'MAXIMO'=>$tnMaximo,
					'MINIMO'=>$tnMinimo,
					'UNIDAD'=>$tcUnidad,
					'MICROO'=>$tcMicroorganismo,
					'ANTIOB'=>$tcAntiobiograma,
					'ANTIBI'=>$tcAntibiotico,
					'SENSIB'=>$tcSensibilidad,
					'DATCRT'=>$tnDatoCritico,
					'IDMLAB'=>$tnIdMedico,
					'RMRLAB'=>$tcFirmaMedico,
					'MD5LAB'=>$lcMD5,
					'USCLAB'=>$tcUsuario,
					'PGCLAB'=>$lcPrograma,
					'FECLAB'=>$lcFecha,
					'HOCLAB'=>$lcHora
				];
				$llResultado = $goDb->from($lcTabla)->insertar($laDatos);


				// Busca si ya existe el parámetro
				$lcTabla = 'LABRSVR';
				$loResultado = $goDb
					->count('*','CUENTA')
					->from($lcTabla)
					->where(['CODVAR'=>$tcCodVariable])
					->get('array');
				if (is_array($loResultado)) {
					if ($loResultado['CUENTA']==0) {
						// Inserta parámetro
						$laDatos = [
							'CODVAR' => $tcCodVariable,
							'DSCVAR' => $tcDescVariable,
						//	'TIPVAR' => '',
						//	'ABRVAR' => '',
						//	'IDUVAR' => 0,
							'UNDVAR' => $tcUnidad,
							'CRIVAR' => 'N',
							'USCVAR' => $tcUsuario,
							'PGCVAR' => $lcPrograma,
							'FECVAR' => $lcFecha,
							'HOCVAR' => $lcHora,
						];
						$llResultado = $goDb->from($lcTabla)->insertar($laDatos);
					}
				}


				// Bacterióloga(o)
				$lcTabla = 'LABRSBC';
				$loResultado = $goDb
					->count('*','CUENTA')
					->from($lcTabla)
					->where(['CODRBC'=>$tnIdMedico])
					->get('array');
				if (is_array($loResultado)) {
					if ($loResultado['CUENTA']==0) {
						// Obtener registro médico -> usuario
						$lcRegMed = '';
						$lcUserMed = '';
						$lnLargoDat = strlen($tcFirmaMedico);
						for ($lnI=0; $lnI<$lnLargoDat; $lnI++) {
							$lcChar = substr($tcFirmaMedico,$lnI,1);
							$lnASCII = ord($lcChar);
							$lcRegMed .= ( ($lnASCII>47 && $lnASCII<58) ? $lcChar : '' );
						}
						if (!empty($lcRegMed)) {
							$loUser = $goDb
								->select('USUARI')
								->from('RIARGMN')
								->where(['REGMED'=>str_pad($lcRegMed,13,"0",STR_PAD_LEFT)])
								->get('array');
							if (is_array($loUser)) {
								if (count($loUser)>0) {
									$lcUserMed = $loUser['USUARI'];
								}
							}
						}

						// Inserta datos bacterióloga(o)
						$laDatos = [
							'CODRBC' => $tnIdMedico,
							'USURBC' => $lcUserMed,
							'FRMRBC' => $tcFirmaMedico,
							'USCRBC' => $tcUsuario,
							'PGCRBC' => $lcPrograma,
							'FECRBC' => $lcFecha,
							'HOCRBC' => $lcHora,
						];
						$llResultado = $goDb->from($lcTabla)->insertar($laDatos);
					}
				}
			}
		}
		return ($llResultado==true?$lnConsecutivo:0);
	}
}
