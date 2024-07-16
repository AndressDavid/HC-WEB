<?php

namespace NUCLEO;

require_once __DIR__ .'/class.Db.php';

use NUCLEO\Db;

class EscalaActividadFisica{

	protected $oDb = null;
	protected $cRealizaAntecedente='';
	protected $bReqAval=false;


	public $aError = [
		'Mensaje' => "",
		'Objeto' => "",
		'Valido' => true,
	];

	public function __construct(){
		global $goDb;
		$this->oDb = $goDb;
		$this->bReqAval = $_SESSION[HCW_NAME]->oUsuario->getRequiereAval();
		$this->cRealizaAntecedente=trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='REAANT' AND ESTTMA=''", null, ''));

	}

	public function consultaViaIngresoActividad($tcViaingreso=''){
		$lcViaIngreso='';

		if(isset($this->oDb)){
			$lcViaIngreso = $this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='VIAING' AND CL2TMA='$tcViaingreso'", null, '');
		}
		return $lcViaIngreso;
	}


	public function consultaRegistroActividad($tnIngreso=0, $taDatos=[]){
		$lcValorRegistrado='';
		$aParametros=[];
		$laDatosEnviar=[];

		if(isset($this->oDb)){
			$lcDatosInactivar=$this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='INACEVO' AND ESTTMA=''", null, '');
			$laDatosInactivar=explode(',', $lcDatosInactivar);
			if(count($taDatos)==0){
				$laActividades = $this->oDb
					->select('trim(OP1ANT) REGISTRO, trim(OP5ANT) DATOS')
					->from('ANTPAC')
					->where('NINANT', '=', $tnIngreso)
					->where('CODANT', '=', '15')
					->where('SANANT', '=', '19')
					->where('INDANT', '=', 10)
					->get('array');
			}else{
				$laActividades = $taDatos;
			}
			$lcValorRegistrado=$laActividades['REGISTRO']??'';

			if (in_array($lcValorRegistrado, $laDatosInactivar)){
				if ($lcValorRegistrado=='S'){
					$lcDatosRegistrados=$laActividades['DATOS'];
					$laDatosRegistrados=explode('¢', $lcDatosRegistrados);

					foreach($laDatosRegistrados as $lcListadoAF){
						if ($lcListadoAF!=''){
							$laListadoAF=explode('~', $lcListadoAF);
							$lcCodigoTipo=$laListadoAF[0];
							$lcDescripcionTipo=$this->descripcionActividad('TIPOACT',$lcCodigoTipo);
							$lcCodigoClase=$laListadoAF[1];
							$lcDescripcionClase=$this->descripcionActividad('CLASACT',$lcCodigoClase);
							$lcFrecuencia=$laListadoAF[2];
							$lcTiempo=$laListadoAF[3];
							$lcCodigoIntensidad=$laListadoAF[4];
							$lcDescripcionIntensidad=$this->descripcionActividad('INTACT',$lcCodigoIntensidad);
							$lcTotal=$laListadoAF[5];
							$lcCodigoActividad=$laListadoAF[6];
							$lcDescripcionActividad=$this->descripcionActividad('ACTINA',$lcCodigoActividad);

							$laDatosEnviar[]=[
								'CODTIPO'=>$lcCodigoTipo,
								'DESCRIPCIONTIPO'=>$lcDescripcionTipo,
								'CODCLASE'=>$lcCodigoClase,
								'DESCRIPCIONCLASE'=>$lcDescripcionClase,
								'FRECUENCIA'=>$lcFrecuencia,
								'TIEMPO'=>$lcTiempo,
								'TOTAL'=>$lcTotal,
								'CODINTENSIDAD'=>$lcCodigoIntensidad,
								'DESCRIPCIONINTENSIDAD'=>$lcDescripcionIntensidad,
								'CODACTIVIDAD'=>$lcCodigoActividad,
								'DESCRIPCIONACTIVIDAD'=>$lcDescripcionActividad,
							];
						}
					}
				}
			}else{
				$lcValorRegistrado='';
			}
		}

		$aParametros=[
			'respuesta'=>$lcValorRegistrado,
			'actividades'=>$laDatosEnviar,
		];
		return $aParametros;
	}

	public function descripcionActividad($tcClase='', $tcCodigo=''){
		$lcDescripcion='';

		if(isset($this->oDb)){
			$lcDescripcion = trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='$tcClase' AND CL2TMA='$tcCodigo'", null, ''));
		}
		return $lcDescripcion;
	}

	public function cargarListasActividad($tcViaingreso=''){
		$laActividades=[];

		if(isset($this->oDb)){
			$laActividades = $this->oDb
				->select('trim(CL1TMA) CLASIFICACION1, trim(CL2TMA) CLASIFICACION2, trim(DE2TMA) DESCRIPCION, trim(OP1TMA) OPCIONAL1				')
				->select('trim(OP2TMA) OPCIONAL2, trim(OP5TMA) OPCIONAL5')
				->from('TABMAE')
				->where('TIPTMA', '=', 'ACTFIS')
				->where('CL1TMA', '<>', '')
				->where('ESTTMA', '=', '')
				->orderBy('CL1TMA, CL3TMA, DE2TMA')
				->getAll('array');
		}
		$laActividades['VIA'] = $this->consultaViaIngresoActividad($tcViaingreso);
		return $laActividades;
	}

	public function validacion($taDatos=[])
	{
		if ($taDatos!='' && is_array($taDatos)){
			$lbRevisar = true;
			$lcRealizaActividad=isset($taDatos['Realiza']) ? $taDatos['Realiza'] : '';
			$lcRespuestaActividad=isset($taDatos['Respuesta']) ? $taDatos['Respuesta'] : '';
			$laActividades='';

			if (empty($lcRealizaActividad)){
				$this->aError = [
					'Mensaje' =>'No existe "Realiza actividad física"',
					'Objeto'  => 'selRealizaActividad',
					'Valido'=>false,
				];
				$lbRevisar = false;
			}

			if ($lbRevisar){
				if (empty($lcRespuestaActividad)){
					$this->aError = [
						'Mensaje' =>'No existe RESPUESTA',
						'Objeto'  => 'selRealizaActividad',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}

			if ($lbRevisar){
				$lcDescrRealizaActividad=trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='REAACT' AND CL2TMA='$lcRealizaActividad'", null, ''));
				if(empty($lcDescrRealizaActividad)){
					$this->aError = [
						'Mensaje' =>'Realiza actividad no registrada en la base de datos',
						'Objeto'  => 'selRealizaActividad',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}


			if (isset($taDatos['Actividades'])) {
				$laActividades=$taDatos['Actividades'];
				if ($lbRevisar){
					if ($lcRealizaActividad=='S' && empty($laActividades)){
						$this->aError = [
							'Mensaje' =>'No existen actividades registradas',
							'Objeto'  => 'selRealizaActividad',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				}

				if ($lbRevisar){
					if ($lcRealizaActividad=='S' && !empty($laActividades)){
						foreach($laActividades as $lcActividades){
							$lcTipoActividad=$lcActividades['CODTIPO'];
							$lcTipoClase=$lcActividades['CODCLASE'];
							$lcTipoIntensidad=$lcActividades['CODINTENSIDAD'];
							$lnFrecuencia=intval($lcActividades['FRECUENCIA']);
							$lnTiempo=intval($lcActividades['TIEMPO']);
							$lnTotal=intval($lcActividades['TOTAL']);

							$lcDescActividad=trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='TIPOACT' AND CL2TMA='$lcTipoActividad'", null, ''));
							if($lbRevisar && empty($lcDescActividad)){
								$this->aError = [
									'Mensaje' =>'Tipo actividad no registrada en la base de datos',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}

							$lcDescClase=trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='CLASACT' AND CL2TMA='$lcTipoClase'", null, ''));
							if($lbRevisar && empty($lcDescClase)){
								$this->aError = [
									'Mensaje' =>'Clase actividad no registrada en la base de datos',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}

							$lcDescIntensidad=trim($this->oDb->obtenerTabmae1('DE2TMA', 'ACTFIS', "CL1TMA='INTACT' AND CL2TMA='$lcTipoIntensidad'", null, ''));
							if($lbRevisar && empty($lcDescIntensidad)){
								$this->aError = [
									'Mensaje' =>'Intensidad no registrada en la base de datos',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}

							if($lbRevisar && (empty($lnFrecuencia) || $lnFrecuencia==0)){
								$this->aError = [
									'Mensaje' =>'Frecuencia no registrada',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}

							if($lbRevisar && (empty($lnTiempo) || $lnTiempo==0)){
								$this->aError = [
									'Mensaje' =>'Tiempo no registrado',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}

							if($lbRevisar && (empty($lnTotal) || $lnTotal==0)){
								$this->aError = [
									'Mensaje' =>'Total no registrado',
									'Objeto'  => 'selRealizaActividad',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}
						}
					}
				}
			}
		}
		return $this->aError;
	}

	public function guardarDatosAF($taDatos=[])
	{
		$tnIngreso=$taDatos['Ingreso'];
		$lcTipoRegistroHc=$taDatos['Tiporeg'];
		$tcCodiVia=$taDatos['Via'];
		$tnConCon=$taDatos['ConsecConsulta'];
		$tnConEvo=$taDatos['ConsecEvolucion'];
		$tnEntidad=$taDatos['Entidad'];
		$tnTidhis=$taDatos['TipoIde'];
		$tnIdenti=$taDatos['NroIde'];
		$tnIndice=$taDatos['Indice'];
		$tnSubIndice=$taDatos['SubIndice'];
		$tnCodigoAntec=$taDatos['CodigoAntec'];
		$tcSubCodigoAntec=$taDatos['SubCodigoAntec'];
		$tcUsuCre=$taDatos['UsuarioCrea'];
		$tcPrgCre=$taDatos['ProgramaCrea'];
		$tcFecCre=$taDatos['FechaCrea'];
		$tcHorCre=$taDatos['HoraCrea'];
		$lcSep='~';
		$lcSimFinal='¢';
		$lcRealizaActividad=isset($taDatos['Datos']['Realiza']) ? $taDatos['Datos']['Realiza'] : '';
		$lcRespuestaaActividad=isset($taDatos['Datos']['Respuesta']) ? $taDatos['Datos']['Respuesta'] : '';
		$laActiviaddesActividad=isset($taDatos['Datos']['Actividades']) ? $taDatos['Datos']['Actividades']:'';
		$lcDescripcionCodigo='';


		if (!empty($lcRealizaActividad) && !empty($lcRespuestaaActividad)){
			if ($lcRealizaActividad=='S'){
				if (!empty($laActiviaddesActividad)){
					foreach($laActiviaddesActividad as $laActFisica){
						$lcDescripcionCodigo.=$laActFisica['CODTIPO'].$lcSep.$laActFisica['CODCLASE']
						.$lcSep.$laActFisica['FRECUENCIA'].$lcSep.$laActFisica['TIEMPO'].$lcSep.$laActFisica['CODINTENSIDAD']
						.$lcSep.$laActFisica['TOTAL'].$lcSep.$laActFisica['CODACTIVIDAD'].$lcSimFinal;
					}
				}
			}else{
				$lcDescripcionCodigo = 'No realiza actividad física';
			}

			if($_SESSION[HCW_NAME]->oUsuario->getRequiereAval()){
				$lnLinea = 1;
				if ($tcPrgCre=='HCPPALWEB'){
					$lcTabla='HISINTL01';
					$lnLongitud=220;
					$laChar = AplicacionFunciones::mb_str_split(trim($lcDescripcionCodigo),$lnLongitud);
					if(is_array($laChar)==true){
						if(count($laChar)>0){
							foreach($laChar as $laDato){
								$laDatosHISINTL01 = [
									'INGHIN' => $tnIngreso,
									'TIPHIN' => $lcTipoRegistroHc,
									'CCOHIN' => $tnConCon,
									'INDHIN' => $tnIndice,
									'SUBHIN' => $tnSubIndice,
									'CODHIN' => $tnCodigoAntec,
									'CLNHIN' => $lnLinea,
									'DESHIN' => $lcRespuestaaActividad,
									'OP1HIN' => $lcRealizaActividad,
									'OP5HIN' => $laDato,
									'USRHIN' => $tcUsuCre,
									'PGMHIN' => $tcPrgCre,
									'FECHIN' => $tcFecCre,
									'HORHIN' => $tcHorCre,
								];
								$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosHISINTL01);
								$lnLinea++;
							}
						}
					}
				} else{
					$lcTabla='REINDE';
					$lnLongitud=500;
					$lnLinea = ($tcPrgCre=='EVOUNWEB'?2:$lnLinea);
					$laChar = AplicacionFunciones::mb_str_split(trim($lcDescripcionCodigo),$lnLongitud);
					$lcTitulo = 'ACTIVIDAD FISICA';
					if(is_array($laChar)==true){
						if(count($laChar)>0){
							foreach($laChar as $laDato){
								$laDatosHISINTL01 = [
									'INGRID' => $tnIngreso,
									'TIPRID' => $lcTipoRegistroHc,
									'CONRID' => $tnConCon,
									'CLIRID' => $lnLinea,
									'INDRID' => $tnSubIndice,
									'IN2RID' => $tnCodigoAntec,
									'DIARID' => $lcTitulo,
									'DESRID' => $laDato,
									'OP1RID' => $lcRealizaActividad,
									'OP5RID' => $lcRespuestaaActividad,
									'USRRID' => $tcUsuCre,
									'PGMRID' => $tcPrgCre,
									'FECRID' => $tcFecCre,
									'HORRID' => $tcHorCre,
								];
								$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosHISINTL01);
								$lnLinea++;
							}
						}
					}
				}
			}else{
				$lcTabla = 'ANTPAC';
				$laWhere = [
					'TIDANT'=>$tnTidhis,
					'NIDANT'=>$tnIdenti,
					'CODANT'=>$tcSubCodigoAntec,
					'SANANT'=>$tnCodigoAntec,
					'INDANT'=>$tnIndice,
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where($laWhere)->eliminar();

				//	GUARDA CABECERA ANTECEDENTES
				$lnLongitud=220;
				$lnLinea = 1;
				$lnInicio = 0;
				$lnInd = 0;
				$lnChar = mb_strlen(trim($lcDescripcionCodigo));
				$lnLineas = (ceil($lnChar/$lnLongitud) == 0 ? 1: ceil($lnChar/$lnLongitud)) + $lnLinea -1;
				for($lnInd = $lnLinea; $lnInd <= $lnLineas; $lnInd++){
					$lcDescrip = substr(trim($lcDescripcionCodigo), $lnInicio, $lnLongitud);
					$laDatosESCHCL = [
						'TIDANT'=>$tnTidhis,
						'NIDANT'=>$tnIdenti,
						'NINANT'=>$tnIngreso,
						'CODANT'=>$tcSubCodigoAntec,
						'SANANT'=>$tnCodigoAntec,
						'INDANT'=>$tnIndice,
						'LINANT'=>$lnInd,
						'DESANT'=>$lcRespuestaaActividad,
						'OP1ANT'=>$lcRealizaActividad,
						'OP5ANT'=>$lcDescrip,
						'USRANT'=>$tcUsuCre,
						'PGMANT'=>$tcPrgCre,
						'FECANT'=>$tcFecCre,
						'HORANT'=>$tcHorCre,
					];
					$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
					$lnInicio = $lnInicio + $lnLongitud;
				}

				$laRealizaAntecedente=explode(',', $this->cRealizaAntecedente);
				if (in_array($lcRealizaActividad, $laRealizaAntecedente) || $lcTipoRegistroHc=='HC'){
					//	DETALLE ANTECEDENTES
					$lcTabla='ANTPAD';
					$lnLongitud=220;
					$lnLinea=1;
					$lnInicio=0;
					$lnInd=0;
					$lnChar = mb_strlen(trim($lcDescripcionCodigo));
					$lnLineas = (ceil($lnChar/$lnLongitud) == 0 ? 1: ceil($lnChar/$lnLongitud)) + $lnLinea -1;
					for($lnInd = $lnLinea; $lnInd <= $lnLineas; $lnInd++){
						$lcDescrip = substr(trim($lcDescripcionCodigo), $lnInicio, $lnLongitud);
						$laDatosESCHCL = [
							'TIDAND'=>$tnTidhis,
							'NIDAND'=>$tnIdenti,
							'NINAND'=>$tnIngreso,
							'FDCAND'=>$tcFecCre,
							'HDCAND'=>$tcHorCre,
							'CODAND'=>$tcSubCodigoAntec,
							'SANAND'=>$tnCodigoAntec,
							'INDAND'=>$tnIndice,
							'LINAND'=>$lnInd,
							'DESAND'=>$lcRespuestaaActividad,
							'OP1AND'=>$lcRealizaActividad,
							'OP5AND'=>$lcDescrip,
							'OP7AND'=>$tnConCon,
							'USRAND'=>$tcUsuCre,
							'PGMAND'=>$tcPrgCre,
							'FECAND'=>$tcFecCre,
							'HORAND'=>$tcHorCre,
						];
						$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatosESCHCL);
						$lnInicio = $lnInicio + $lnLongitud;
					}
				}
			}
		}
	}
}