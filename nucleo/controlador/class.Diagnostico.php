<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Db.php';
require_once __DIR__ .'/class.FormulacionParametros.php';
require_once __DIR__ .'/class.Consecutivos.php';
require_once __DIR__ .'/class.Habitacion.php';


class Diagnostico
{
	private $cTextoC = '';
	public $aListaDiagnosticos = [];
	public $aClase_Diagnostico = null;
	public $aTipo_Diagnostico = null;
	public $aTDiagnostico = null;
	public $aTipoDX = null;
	public $aClaseDX = null;
	public $aCDiagnostico = null;
	public $aTrDiagnostico = null;
	public $aTratamientoDX = null;
	public $aTratamiento_Diagnostico = null;
	public $aAyuda_TipoDiagnostico = [];
	public $aAyuda_ClaseDiagnostico = [];
	public $aAyuda_TratamientoDiagnostico = [];
	public $aListadoDiagnosticos = [];
	public $aDxFallece = [];
	public $aTiposDescarte = [];
	public $cMensajeValidar = '';
	public $aDxPpal = [];
	public $aValidacionCieEgreso = [];


	public function __construct($tcCodigo='',$tnFecha=0)
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->cargar($tcCodigo,$tnFecha);
    }

	public function cargar($tcCodigo='',$tnFecha=0)
	{
		if (!empty($tcCodigo) && !is_null($tcCodigo)){
			if(isset($this->oDb)){

				if (empty($tnFecha)){
					$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
					$tnFecha = $ltAhora->format('Ymd');
				}

				$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DESCIE', $tnFecha.' BETWEEN CL1TMA AND CL2TMA');
				$lcTemp = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', '')) ;

				if (!empty($lcTemp)){
					$laDiag = $this->oDb
						->select($lcTemp)
						->from('RIACIE')
						->where(['ENFRIP'=>$tcCodigo,])
						->get('array');
					$this->cTextoC = trim($laDiag['DESCRIPCION'] ?? '') ;
				}
			}
		}
	}

	public function codigoTipoDiagnostico($tcIndice='')
	{
		if (!is_array($this->aTipo_Diagnostico)) {
			$this->TipoDiagnostico();
		}
		if (is_array($this->aTipo_Diagnostico)) {
			if (!is_array($this->aTDiagnostico)) {
				foreach($this->aTipo_Diagnostico as $laPar){
					$laPar = array_map('trim',$laPar);
					$this->aTDiagnostico[$laPar['TABCOD']] = $laPar['TABCOD'];
				}
			}
		}
		return $this->aTDiagnostico[$tcIndice] ?? '';
	}

	public function codigoTipoDX($tcIndice='')
	{
		if (!is_array($this->aTipoDX)) {
			$this->TipoDiagnostico();
		}
		return $this->aTipoDX[$tcIndice] ?? '';
	}

	public function codigoClaseDiagnostico($tcIndice='')
	{
		if (!is_array($this->aClase_Diagnostico)) {
			$this->ClaseDiagnostico();
		}
		if (is_array($this->aClase_Diagnostico)) {
			if (!is_array($this->aCDiagnostico)) {
				foreach($this->aClase_Diagnostico as $laPar){
					$laPar = array_map('trim',$laPar);
					$this->aCDiagnostico[$laPar['TABCOD']] = $laPar['TABCOD'];
				}
			}
		}
		return $this->aCDiagnostico[$tcIndice] ?? '';
	}

	public function codigoClaseDX($tcIndice='')
	{
		if (!is_array($this->aClaseDX)) {
			$this->ClaseDiagnostico();
		}
		return $this->aClaseDX[$tcIndice] ?? '';
	}

	public function codigoTratamientoDiagnostico($tcIndice='')
	{
		if (!is_array($this->aTratamiento_Diagnostico)) {
			$this->TratamientoDiagnostico();
		}
		if (is_array($this->aTratamiento_Diagnostico)) {
			if (!is_array($this->aTrDiagnostico)) {
				foreach($this->aTratamiento_Diagnostico as $laPar){
					$laPar = array_map('trim',$laPar);
					$this->aTrDiagnostico[$laPar['TABCOD']] = $laPar['TABCOD'];
				}
			}
		}
		return $this->aTrDiagnostico[$tcIndice] ?? '';
	}

	public function codigoTratamientoDX($tcIndice='')
	{
		if (!is_array($this->aTratamientoDX)) {
			$this->TratamientoDiagnostico();
		}
		return $this->aTratamientoDX[$tcIndice] ?? '';
	}

	public function validacionCiePrincipal($tcTipoHc='')
	{
		$aParametros=$this->aValidacionCieEgreso=[];
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('TRIM(CL3TMA) CL3TMA, trim(OP2TMA) OP2TMA, trim(DE2TMA) DE2TMA')
				->from('TABMAE')
				->where('TIPTMA', '=', 'DIAGRIPS')
				->where('CL1TMA', '=', 'VALCIEH')
				->where('CL2TMA', '=', $tcTipoHc)
				->where('ESTTMA', '=', '')
				->orderBy('OP3TMA')
				->getAll('array');
			if ($this->oDb->numRows()>0){
				foreach($laParametros as $laListado){
					$aParametros[$laListado['CL3TMA']]=[
							'VIAS'=>explode(',',$laListado['OP2TMA']),
							'DESCRIPCION'=>$laListado['DE2TMA'],
						];
				}
			}
		}
		$this->aValidacionCieEgreso=$aParametros;
		unset($laParametros);
		return $aParametros;
	}
	
	public function validacionCiePrincipalOrden($tcLetraCie='')
	{
		$laRetornar=[];
		$lcMarcaCie='';
		if(isset($this->oDb)){
			$lcMarcaCie=$this->oDb->obtenerTabmae1('OP1TMA', 'DIAGRIPS', "CL1TMA='VALPRIP' AND OP1TMA='$tcLetraCie'", null, '');

			$laParametros = $this->oDb
				->select('TRIM(OP1TMA) LETRA, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'DIAGRIPS')
				->where('CL1TMA', '=', 'VALPRIP')
				->where('ESTTMA', '=', '')
				->orderBy('OP1TMA')
				->getAll('array');
		}
		
		$laRetornar = [
			'mensaje'=>$lcMarcaCie,
			'datoscie'=>$laParametros,
		];
		
		return $laRetornar;
	}

	public function validacion($laDiagnosticos,$tcViaIngreso='',$tcTipoRegistro='')
	{
		$laRetornar = [
			'Mensaje'=>'',
			'Objeto'=>'buscarcodigoCie',
			'Valido'=>true,
		];

		if (!empty($laDiagnosticos)){
			$lbRevisar = false;

			//	VALIDA TIPO DIAGNOSTICO PRINCIPAL EXISTA
			foreach ($laDiagnosticos as $validaTipoDiagnostico){
				if ($validaTipoDiagnostico['CODTIPO']=='1'){
					$lbRevisar = true;
					break;
				}
			}

			if ($lbRevisar) {
				foreach ($laDiagnosticos as $valDiagnostico){
					$lcCodigoValidar = $valDiagnostico['CODIGO'];
					$lcTipoValidar = $valDiagnostico['CODTIPO'];
					$lcClaseValidar = $valDiagnostico['CODCLASE'];
					$lcTratamientoValidar = $valDiagnostico['CODTRATA'];
					$lcDescripcionDiagnostico=isset($valDiagnostico['DESCRIP'])?$valDiagnostico['DESCRIP']:'';

					//	VALIDA CÓDIGO
					if (!empty($lcCodigoValidar)){
						$laErrores = [];
						$lcTablaValida = 'RIACIE';
						$laWhere=['ENFRIP'=>$lcCodigoValidar,];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->tabla($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$laRetornar['Valido'] = false;
								$laRetornar['Mensaje'] = 'NO se encontró el código díagnóstico';
								break;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}

					$laResultado = $this->codigoTipoDiagnostico($lcTipoValidar);
					if(empty($laResultado)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "NO se encontró el tipo diagnóstico para $lcCodigoValidar, revise por favor.";
						break;
					}

					$laResultado = $this->codigoClaseDiagnostico($lcClaseValidar);
					if(empty($laResultado)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = "NO se encontró la clase de diagnóstico para $lcCodigoValidar, revise por favor.";
						break;
					}

					$laResultado = $this->codigoTratamientoDiagnostico($lcTratamientoValidar);
					if(empty($laResultado)){
						$laRetornar['Valido'] = false;
						$laRetornar['Mensaje'] = 'NO se encontró el tratamiento diagnóstico, revise por favor.';
						break;
					}
					
					if ($lcTipoValidar=='1' && !empty($tcTipoRegistro)){
						$lcLetraCiePrincipal=substr(trim($valDiagnostico['CODIGO']), 0, 1);
						if (!empty($lcLetraCiePrincipal)){
							$laViasCie = $this->oDb
								->select('trim(OP2TMA) LETRAS, trim(DE2TMA) DESCRIPCION')
								->from('TABMAE')
								->where('TIPTMA', '=', 'DIAGRIPS')
								->where('CL1TMA', '=', 'VALCIEH')
								->where("CL2TMA='$tcTipoRegistro'")
								->where("CL3TMA='$lcLetraCiePrincipal'")
								->where('ESTTMA', '=', '')
								->get('array');
							if ($this->oDb->numRows()>0){
								$laVias=explode(',',$laViasCie['LETRAS']);
								$lcMensajeValidar=$laViasCie['DESCRIPCION'];
								$lcViaIngreso=$tcViaIngreso=='01'?'U':'H';
								if (in_array($lcViaIngreso, $laVias)){
									$laRetornar['Valido'] = false;
									$lcMensaje="<b>El diagnóstico: </b><br>" .$lcCodigoValidar.'-'.$lcDescripcionDiagnostico. "<br><b>No es permitido por:</b><br>".$lcMensajeValidar;
									$laRetornar['Mensaje'] = $lcMensaje;
									break;
								}
							}	
						}	
					}
				}
			}
			else {
				$laRetornar['Valido'] = false;
				$laRetornar['Mensaje'] = 'NO existe diagnóstico principal';
			}
		}
		else {
			$laRetornar['Valido'] = false;
			$laRetornar['Mensaje'] = 'Falta diagnóstico a ingresar.';
		}
		return $laRetornar;
	}

	public function guardarDiagnostico($taDetalleDiagnosticos, $tnNroIngreso=0, $tnConsecConsulta=0, $tcTipoide='', $tnNroidenti=0,
					$tnNroEntidad=0, $tcTipoRegistro='', $tcConductaSeguir='', $tcDescripcionConducta='',
					$tcUsuarioCrea='', $tcProgramaCrea='', $tnFechaCrea=0, $tnHoraCrea=0, $tcEstadoSalida='', $taDxFallece=null)
	{
		$lcDescripFallece = $lcFechaFallece = $lcHoraFallece = '';
		$lcDxFallece = $taDxFallece['CieFallece']??'';
		if(!empty($lcDxFallece)){
			$lcFechaFallece = $taDxFallece['FecFallece']??'';
			$lcHoraFallece = $taDxFallece['HorFallece']??'';
			$lcDescripFallece = str_pad(str_replace(":", '', $lcHoraFallece), 6, '0') . '=' . str_pad(str_replace("-", '', $lcFechaFallece), 8, ' ') . '=' . str_pad(substr($lcDxFallece,0,4), 9, ' ' ).'=';
		}

		if (is_array($taDetalleDiagnosticos) && !empty($taDetalleDiagnosticos)){
 			if(isset($this->oDb)){
				$lnIndice=25;
				$lcCiePrincipal = $lcRelacionado1 = $lcRelacionado2 = $lcRelacionado3 = $lcModificacion = $lcContinuaDx = $lcDescartaDx = '';
				$lnClasificacion=0;
				foreach ($taDetalleDiagnosticos as $diagnostico){
					$lcCodigoDiagnostico = $diagnostico['CODIGO'];
					$lcTipoDiagnostico = $diagnostico['CODTIPO'];
					$lcClaseDiagnostico = $diagnostico['CODCLASE'];
					$lcTratamientoDiagnostico = $diagnostico['CODTRATA'];
					$lcAnalisisConducta = trim($diagnostico['OBSER']);
					$lcTipoDescarte = $diagnostico['CODDESCARTE'];
					$lcJustificacionDescarte = trim($diagnostico['JUSTIFICACIONDESCARTE']);
					$lcCiePrincipal = intval($lcTipoDiagnostico)==1 ? $diagnostico['CODIGO'] : $lcCiePrincipal;
					$lnClasificacion+=1;
					$lcContinuaDx = $diagnostico['CONTINUA']??'';
					$lcDescartaDx = $diagnostico['DESCARTAR']??'';
					$lcModificacion = $lcContinuaDx == '1'?'MC':($lcDescartaDx == '1'?'MD':'');

					if (intval($lcTipoDiagnostico)==2 && $lcRelacionado1=='' && $lcTipoDescarte==''){
						$lcRelacionado1 = $lcCodigoDiagnostico;
					}else{
						if (intval($lcTipoDiagnostico)==2 && $lcRelacionado2=='' && $lcTipoDescarte==''){
							$lcRelacionado2 = $lcCodigoDiagnostico;
						}else{
							if (intval($lcTipoDiagnostico)==2 && $lcRelacionado3=='' && $lcTipoDescarte==''){
								$lcRelacionado3 = $lcCodigoDiagnostico;
							}
						}
					}

					if ($tnNroIngreso>0 && !empty($lcCodigoDiagnostico) && intval($lcTipoDiagnostico)>0) {
						$laErrores = [];
						$lcTablaCie = 'EVODIA';
						$lcConductaSeguir = strval(intval($tcConductaSeguir));
						$lnEstadoSalida = intval($tcEstadoSalida);

						$laData=[
							'INGEDC' => $tnNroIngreso,
							'TIPEDC' => $tcTipoRegistro,
							'EVOEDC' => $tnConsecConsulta,
							'INDEDC' => intval($lcTipoDiagnostico),
							'CLIEDC' => $lcClaseDiagnostico,
							'TRAEDC' => $lcTratamientoDiagnostico,
							'CLAEDC' => $lnClasificacion,
							'DIPEDC' => $lcCodigoDiagnostico,
							'CONEDC' => $lcConductaSeguir,
							'OP2EDC' => mb_substr($tcDescripcionConducta, 0, 10),
							'OP3EDC' => $lnEstadoSalida,
							'OP5EDC' => $lcDescripFallece,
							'OP6EDC' => $lcModificacion,
							'DCAEDC' => $lcTipoDescarte,
							'USREDC' => $tcUsuarioCrea,
							'PGMEDC' => $tcProgramaCrea,
							'FECEDC' => $tnFechaCrea,
							'HOREDC' => $tnHoraCrea,
						];

						try {
							$this->oDb->tabla($lcTablaCie)->insertar($laData);
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}

						if (!empty($lcAnalisisConducta) && empty($lcTipoDescarte)) {
							$lnLinea = 1;
							$lcTablaCie = 'CIEANA';
							$lnLongitud = 500;
							$laTextos = AplicacionFunciones::mb_str_split(trim($lcAnalisisConducta),$lnLongitud);
							if (is_array($laTextos) && !empty($laTextos)) {
								foreach($laTextos as $lcTexto) {
									$laData=[
										'INGANA' => $tnNroIngreso,
										'TIPANA' => $tcTipoRegistro,
										'CEVANA' => $tnConsecConsulta,
										'CIEANA' => $lcCodigoDiagnostico,
										'LINANA' => $lnClasificacion,
										'DESANA' => $lcTexto,
										'USRANA'  => $tcUsuarioCrea,
										'PGMANA'  => $tcProgramaCrea,
										'FECANA'  => $tnFechaCrea,
										'HORANA'  => $tnHoraCrea,
									];
									try {
										$this->oDb->tabla($lcTablaCie)->insertar($laData);
									} catch(Exception $loError){
										$laErrores[] = $loError->getMessage();
									} catch(PDOException $loError){
										$laErrores[] = $loError->getMessage();
									}
								}
							}
						}

						if (!empty($lcJustificacionDescarte)) {
							$lnLinea = 1;
							$lcTablaCie = 'JUSDES';
							$lnLongitud = 500;
							$laTextos = AplicacionFunciones::mb_str_split(trim($lcJustificacionDescarte), $lnLongitud);
							if (is_array($laTextos) && !empty($laTextos)) {
								foreach($laTextos as $lcTexto) {
									$laData=[
										'INGJUD' => $tnNroIngreso,
										'TIPJUD' => $tcTipoRegistro,
										'CEVJUD' => $tnConsecConsulta,
										'CIEJUD' => $lcCodigoDiagnostico,
										'LINJUD' => $lnLinea++,
										'DESJUD' => $lcTexto,
										'USRJUD'  => $tcUsuarioCrea,
										'PGMJUD'  => $tcProgramaCrea,
										'FECJUD'  => $tnFechaCrea,
										'HORJUD'  => $tnHoraCrea,
									];
									try {
										$this->oDb->tabla($lcTablaCie)->insertar($laData);
									} catch(Exception $loError){
										$laErrores[] = $loError->getMessage();
									} catch(PDOException $loError){
										$laErrores[] = $loError->getMessage();
									}
								}
							}
						}

						// GUARDAR ARCHIVO RESOLUCION
						$oTabmae=$this->oDb->obtenerTabMae('CL2TMA', 'CIERESO', "CL1TMA='$lcCodigoDiagnostico'");
						$lcCieResolucion=trim(AplicacionFunciones::getValue($oTabmae,'CL2TMA',''),' ');
						$lcTablaCie = 'CIERES';
						if (!empty($lcCieResolucion)){

							$laWhere=['INGCIE' => $tnNroIngreso,'CIECIE'=>$lcCodigoDiagnostico,'RESCIE'=>$lcCieResolucion,];
							$laData=[
								'INGCIE' => $tnNroIngreso,
								'CIECIE' => $lcCodigoDiagnostico,
								'RESCIE' => $lcCieResolucion,
								'TIPCIE' => $tcTipoRegistro,
								'CONCIE' => '1',
								'TDICIE' => intval($lcTipoDiagnostico),
								'CLDCIE' => $lcClaseDiagnostico,
								'TRACIE' => $lcTratamientoDiagnostico,
								'ESTCIE' => $lcTipoDescarte,
							];
							$laDataI=[
								'USRCIE' => $tcUsuarioCrea,
								'PGMCIE' => $tcProgramaCrea,
								'FECCIE' => $tnFechaCrea,
								'HORCIE' => $tnHoraCrea,
							];
							$laDataU=[
								'TIPCIE' => $tcTipoRegistro,
								'CONCIE' => '1',
								'TDICIE' => intval($lcTipoDiagnostico),
								'CLDCIE' => $lcClaseDiagnostico,
								'TRACIE' => $lcTratamientoDiagnostico,
								'ESTCIE' => $lcTipoDescarte,
								'UMOCIE' => $tcUsuarioCrea,
								'PMOCIE' => $tcProgramaCrea,
								'FMOCIE' => $tnFechaCrea,
								'HMOCIE' => $tnHoraCrea,
							];

							try {
								$lbInsertar = true;
								$laReg = $this->oDb->tabla($lcTablaCie)->where($laWhere)->get('array');
								if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

								if ($lbInsertar) {
									$this->oDb->tabla($lcTablaCie)->insertar(array_merge($laData,$laDataI));
								} else {
									$this->oDb->tabla($lcTablaCie)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
								}
							} catch(Exception $loError){
								$laErrores[] = $loError->getMessage();
							} catch(PDOException $loError){
								$laErrores[] = $loError->getMessage();
							}
						}

						// GUARDAR ARCHIVO NEUMOLOGIA HAP
						$oTabmae=$this->oDb->obtenerTabMae('CL2TMA', 'CIENEUM', "DE2TMA='$lcCodigoDiagnostico'");
						$lcCieHap=trim(AplicacionFunciones::getValue($oTabmae,'CL2TMA',''),' ');

						$lcTablaCie = 'CIEHAPL01';
						if (!empty($lcCieHap)){
							$lnConsecutivoHap=Consecutivos::fCalcularConsecutivoCieHap($tnNroIngreso);
							$laWhere=['INGHAP' => $tnNroIngreso,'CIEHAP'=>$lcCodigoDiagnostico,];
							$laData=[
								'CONHAP' => $lnConsecutivoHap,
								'INGHAP' => $tnNroIngreso,
								'CIEHAP' => $lcCodigoDiagnostico,
							];
							$laDataI=[
								'USRHAP' => $tcUsuarioCrea,
								'PGMHAP' => $tcProgramaCrea,
								'FECHAP' => $tnFechaCrea,
								'HORHAP' => $tnHoraCrea,
							];

							try {
								$lbInsertar = true;
								$laReg = $this->oDb->tabla($lcTablaCie)->where($laWhere)->get('array');
								if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

								if ($lbInsertar) {
									$this->oDb->tabla($lcTablaCie)->insertar(array_merge($laData,$laDataI));
								}
							} catch(Exception $loError){
								$laErrores[] = $loError->getMessage();
							} catch(PDOException $loError){
								$laErrores[] = $loError->getMessage();
							}
						}
					}
				}
			}

			$llValidar = $this->consultaRegistrarDiagnosticos($tcTipoRegistro);
			if ($llValidar){
				$laDataCie=[
					'PRINCIPAL' => $lcCiePrincipal,
					'RELACIONADO1' => $lcRelacionado1,
					'RELACIONADO2' => $lcRelacionado2,
					'RELACIONADO3' => $lcRelacionado3,
				];
				$this->guardarCieRiadia($tnNroIngreso,$laDataCie,$tcTipoRegistro,$tcUsuarioCrea,$tcProgramaCrea,$tnFechaCrea,$tnHoraCrea,$tcEstadoSalida);
			}
		}

		//Actualiza o crea registro en la tabla RIAMUR y EVODIA
		if(!empty($lcDxFallece)){
			$laTemp= $this->oDb
				->select('INGMOR')
				->from('RIAMUR')
				->where(['TIDMOR'=>$tcTipoide,
						 'NIDMOR'=>$tnNroidenti,
						 'CONMOR'=>1
						])
				->getAll('array');

			if(!is_array($laTemp)){$laTemp=[];}
			$lcTabla = 'RIAMUR';
			if(count($laTemp)>0){
				//INSERTA EL REGISTRO EN LA TABLA RIAMUR
				$laDatos = [
					'CM1MOR'=>$lcDxFallece,
					'UMOMOR'=>$tcUsuarioCrea,
					'PMOMOR'=>$tcProgramaCrea,
					'FMOMOR'=>$tnFechaCrea,
					'HMOMOR'=>$tnHoraCrea
				];
				$llResultado = $this->oDb->tabla($lcTabla)->where(['TIDMOR'=>$tcTipoide, 'NIDMOR'=>$tnNroidenti, 'CONMOR'=>1,])->actualizar($laDatos);
			}else{
				$laDatos = [
					'TIDMOR'=>$tcTipoide,
					'NIDMOR'=>$tnNroidenti,
					'INGMOR'=>$tnNroIngreso,
					'CM1MOR'=>$lcDxFallece,
					'CONMOR'=>1,
					'USRMOR'=>$tcUsuarioCrea,
					'PGMMOR'=>$tcProgramaCrea,
					'FECMOR'=>$tnFechaCrea,
					'HORMOR'=>$tnHoraCrea
				];
				$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
			}

			// Registro tabla EVODIA
			if($tcTipoRegistro=='RF'){
				$lcTabla = 'EVODIA';
				$laDatos = [
					'INGEDC'=>$tnNroIngreso,
					'TIPEDC'=>'RF',
					'EVOEDC'=>$tnConsecConsulta,
					'INDEDC'=>4,
					'CLIEDC'=>1,
					'DIPEDC'=>$lcDxFallece,
					'OP5EDC'=>$lcDescripFallece,
					'USREDC'=>$tcUsuarioCrea,
					'PGMEDC'=>$tcProgramaCrea,
					'FECEDC'=>$tnFechaCrea,
					'HOREDC'=>$tnHoraCrea
				];
				$llResultado = $this->oDb->tabla($lcTabla)->insertar($laDatos);
			}
		}
	}

	public function consultaRegistrarDiagnosticos($tcTipoRegistro='')
	{
		$llValida = false;
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'FORMEDIC', ['CL1TMA'=>'REGCIE', 'ESTTMA'=>' ']);
		$lcTiposRegistros = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
		$laTiposRegistros = explode(',', $lcTiposRegistros);
		$laTiposRegistros = array_map('trim',$laTiposRegistros);

		if (in_array($tcTipoRegistro, $laTiposRegistros)) {
			$llValida = true;
		}
		return $llValida;
	}

	public function guardarCieRiadia($tnNroIngreso=0, $taDiagnosticos=[], $tcTipoRegistro='', $tcUsuarioCrea='', $tcProgramaCrea='', $tnFechaCrea=0, $tnHoraCrea=0, $tcEstadoSalida='')
	{
		$laRIADIA = $this->oDb
			->select('DINDIA, DI1DIA, DI2DIA, DI3DIA')
			->from('RIADIA')
			->where('INGDIA', '=', $tnNroIngreso)
			->get('array');
		$lcPrincipalHC = isset($laRIADIA['DINDIA']) ? $laRIADIA['DINDIA'] : '';
		$lcRelacionado1HC = isset($laRIADIA['DI1DIA']) ? $laRIADIA['DI1DIA'] : '';
		$lcRelacionado2HC = isset($laRIADIA['DI2DIA']) ? $laRIADIA['DI2DIA'] : '';
		$lcRelacionado3HC = isset($laRIADIA['DI3DIA']) ? $laRIADIA['DI3DIA'] : '';
		$lcPrincipalHC = $lcPrincipalHC!='' ? $lcPrincipalHC : ($tcTipoRegistro=='RF' ? '' : $taDiagnosticos['PRINCIPAL']);
		$lcRelacionado1HC = $lcRelacionado1HC!='' ? $lcRelacionado1HC : ($tcTipoRegistro=='RF' ? '' : $taDiagnosticos['RELACIONADO1']);
		$lcRelacionado2HC = $lcRelacionado2HC!='' ? $lcRelacionado2HC : ($tcTipoRegistro=='RF' ? '' : $taDiagnosticos['RELACIONADO2']);
		$lcRelacionado3HC = $lcRelacionado3HC!='' ? $lcRelacionado3HC : ($tcTipoRegistro=='RF' ? '' : $taDiagnosticos['RELACIONADO3']);
		$lcPrincipalEP = $tcTipoRegistro=='RF' ? $taDiagnosticos['PRINCIPAL'] : '';
		$lcRelacionado1EP = $tcTipoRegistro=='RF' ? $taDiagnosticos['RELACIONADO1'] : '';
		$lcRelacionado2EP = $tcTipoRegistro=='RF' ? $taDiagnosticos['RELACIONADO2'] : '';
		$lcRelacionado3EP = $tcTipoRegistro=='RF' ? $taDiagnosticos['RELACIONADO3'] : '';

		$lcTablaCie = 'RIADIA';
		$laWhere=['INGDIA' => $tnNroIngreso,];
		$laData=[
			'INGDIA' => $tnNroIngreso,
			'DINDIA' => $lcPrincipalHC,
			'DI1DIA' => $lcRelacionado1HC,
			'DI2DIA' => $lcRelacionado2HC,
			'DI3DIA' => $lcRelacionado3HC,
			'DEGDIA' => $lcPrincipalEP,
			'DE1DIA' => $lcRelacionado1EP,
			'DE2DIA' => $lcRelacionado2EP,
			'DE3DIA' => $lcRelacionado3EP,
			'ESADIA' => $tcEstadoSalida,
		];
		$laDataI=[
			'USRDIA' => $tcUsuarioCrea,
			'PGMDIA' => $tcProgramaCrea,
			'FECDIA' => $tnFechaCrea,
			'HORDIA' => $tnHoraCrea,
		];
		$laDataU=[
			'UMODIA' => $tcUsuarioCrea,
			'PMODIA' => $tcProgramaCrea,
			'FMODIA' => $tnFechaCrea,
			'HMODIA' => $tnHoraCrea,
		];

		try {
			$lbInsertar = true;
			$laReg = $this->oDb->tabla($lcTablaCie)->where($laWhere)->get('array');
			if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

			if ($lbInsertar) {
				$this->oDb->tabla($lcTablaCie)->insertar(array_merge($laData,$laDataI));
			} else {
				$this->oDb->tabla($lcTablaCie)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
			}
		} catch(Exception $loError){
			$laErrores[] = $loError->getMessage();
		} catch(PDOException $loError){
			$laErrores[] = $loError->getMessage();
		}

	}
	public function listadiagnosticos($tnIngreso=0)
	{
		if (!empty($tnIngreso)){
			if(isset($this->oDb)){
				$listaTempDiagnosticos = [];

				$laListaDiagnosticos = $this->oDb
					->select('A.CONCON CONSECUTIVO, A.CONSEC LINEA, A.SUBIND TIPO, A.CODIGO CLASE, trim(A.SUBORG) CODIGO, trim(A.FILLE3) TRATAMIENTO, A.FECHIS FECHA, trim(A.DESCRI) ANALISIS, trim(B.DE2RIP) DESCRIPCIONCIE')
					->tabla('RIAHIS AS A')
					->leftJoin('RIACIE AS B', 'trim(A.SUBORG)=trim(B.ENFRIP)', null)
					->where('NROING', '=', $tnIngreso)
					->where('INDICE', '=', 25)
					->orderBy('SUBIND, CODIGO, FILLE3, CONSEC')
					->getAll('array');
				foreach ($laListaDiagnosticos as $laDiagnostico){
					$lbNoExiste = true;
					foreach ($listaTempDiagnosticos as $lnIndex=>$laTempDx) {
						if ($laDiagnostico['CODIGO']==$laTempDx['CODIGO']) {
							$lbNoExiste = false;
							$listaTempDiagnosticos[$lnIndex]['ANALISIS'] .= $laDiagnostico['ANALISIS'];
							break;
						}
					}
					if ($lbNoExiste) {
						array_push($listaTempDiagnosticos,$laDiagnostico);
					}
				}
				$laListaDiagnosticos = $listaTempDiagnosticos;
				$lnReg = count($laListaDiagnosticos);

				if (empty($lnReg)){
					$laListaDiagnosticos = $this->oDb
						->select('CCOHOS CONSECUTIVO, SUBHOS TIPO, CODHOS CLASE, trim(DESHOS) CODIGO, SPACE(6) AS TRATAMIENTO, FECHOS FECHA')
						->from('HISHOS')
						->where('INGHOS', '=', $tnIngreso)
						->where('INDHOS', '=', 25)
						->where('PGMHOS', '=', 'HC0007U')
						->orderBy('INDHOS, SUBHOS, CODHOS, CLNHOS')
						->getAll('array');
					$lnReg = count($laListaDiagnosticos);
				}

				if (empty($lnReg)){
					$laListaDiagnosticos = $this->oDb
						->select('CCOHCL CONSECUTIVO, SUBHCL TIPO, CODHCL CLASE, trim(DESHCL) CODIGO, SPACE(6) AS TRATAMIENTO, FECHCL FECHA')
						->from('HISCLI')
						->where('INGHCL', '=', $tnIngreso)
						->where('INDHCL', '=', 25)
						->getAll('array');
				}
				$this->aListaDiagnosticos = $laListaDiagnosticos;
			}
		}
		return $this->aListaDiagnosticos;
	}

	public function consultaDiagnosticos($tnIngreso=0, $lcFiltroCie='')
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFecCre = $ltAhora->format('Ymd');
		$lcHorCre = $ltAhora->format('His');
		$listaTempDiagnosticos = [];
		$tnIngreso = intval($tnIngreso);
		$lcTipoRegistro='';
		$lnEvolucionRegistro=$lnObliga=0;
		$tnIngreso = intval($tnIngreso);
		$laCondiciones = ['INGEDC'=>$tnIngreso];

		$laConsultar = $this->oDb
			->select('trim(TIPEDC) TIPO, EVOEDC CONSEC_EVOLUCION')
			->tabla('EVODIA')
			->where($laCondiciones)
			->where('TIPEDC', '<>', 'RF')
			->where('INDEDC', '>', '0')
			->where('CLIEDC', '>', '0')
			->where('TRAEDC', '<>', ' ')
			->where('DCAEDC', '=', '')
			->orderBy('FECEDC DESC, HOREDC DESC')
			->get('array');
		if(is_array($laConsultar)){
			$lcTipoRegistro =$laConsultar['TIPO']??'';
			$lnEvolucionRegistro =$laConsultar['CONSEC_EVOLUCION']??0;
		}
		unset($laConsultar);

		if (!empty($lcTipoRegistro) && $lnEvolucionRegistro>0) {
			$lcCondicion = trim($this->oDb->obtenerTabMae1('DE2TMA', 'DIAGNOS', "CL1TMA='FILCONS' AND CL2TMA='$lcFiltroCie' AND ESTTMA=''", null, 'A.INDEDC>0'));

			$laObtieneDiagnosticos = $this->oDb
				->select('A.INDEDC TIPO, A.CLIEDC CLASE, TRIM(A.TRAEDC) TRATAMIENTO, TRIM(A.DIPEDC) DIAGNOSTICO, trim(substr(trim(C.DE2RIP), 1, 220)) DESCRIPCION_CIE, TRIM(A.DCAEDC) DESCARTE, FECEDC, HOREDC, 0 AS OBLIGA')
				->select('TRIM(B.DESANA) ANALISIS')
				->select('TRIM(D.DE2TMA) TIPO_DESCARTE')
				->select('(SELECT TRIM(C.TABDSC) FROM PRMTAB AS C WHERE C.TABTIP=\'TDX\' AND C.TABCOD LIKE \'A%\' AND INT(SUBSTR(TABCOD,2,1))=A.INDEDC FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_TIPO')
				->select('(SELECT TRIM(C.TABDSC) FROM PRMTAB AS C WHERE C.TABTIP=\'TDX\' AND C.TABCOD LIKE \'B%\' AND INT(SUBSTR(TABCOD,2,1))=A.CLIEDC FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_CLASE')
				->select('(SELECT TRIM(C.DE2TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'ESTDIAG\' AND C.CL1TMA=\'TIPOTRA\' AND TRIM(C.CL2TMA)=TRIM(A.TRAEDC) FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_TRATAMIENTO')
				->tabla('EVODIA AS A')
				->leftJoin('CIEANA AS B', "A.INGEDC=B.INGANA AND B.TIPANA='$lcTipoRegistro' AND B.CEVANA=$lnEvolucionRegistro AND TRIM(A.DIPEDC)=TRIM(B.CIEANA)", null)
				->leftJoin('RIACIE AS C', "TRIM(A.DIPEDC)=TRIM(C.ENFRIP)", null)
				->leftJoin('TABMAE AS D', "TRIM(A.DCAEDC)=TRIM(D.CL2TMA) AND D.TIPTMA='ESTDIAG' AND D.CL1TMA='DESCARTE'", null)
				->where('A.INGEDC', '=', $tnIngreso)
				->where('A.TIPEDC', '=', $lcTipoRegistro)
				->where('A.EVOEDC', '=', $lnEvolucionRegistro)
				->where('A.CLIEDC', '>', '0')
				->where('A.TRAEDC', '<>', ' ')
				->where('A.DCAEDC', '=', '')
				->where($lcCondicion)
				->getAll('array');

			foreach ($laObtieneDiagnosticos as $laDiagnostico){
				if ($laDiagnostico['TIPO']=='1'){
					$lnFecha = intval(date('Ymd',strtotime('+1 day' , strtotime($laDiagnostico['FECEDC']))));
					$lnFecHoraFinal = $lnFecha * 1000000 + $laDiagnostico['HOREDC'];

					$laCondiciones = ['INGEDC'=>$tnIngreso, 'INDEDC'=>'1', 'OP6EDC'=>'MC'];
					$laTempDX = $this->oDb
						->select('FECEDC, HOREDC')
						->from('EVODIA')
						->where($laCondiciones)
						->orderBy('FECEDC DESC, HOREDC DESC')
						->get('array');
					if(is_array($laTempDX)){
						if(count($laTempDX)>0){
							$lnFecha = intval(date('Ymd',strtotime('+1 day' , strtotime($laTempDX['FECEDC']))));
							$lnFecHoraFinal = $lnFecha * 1000000 + $laTempDX['HOREDC'];
						}
					}

					$lnFecHoraActual= $lcFecCre * 1000000 + $lcHorCre;
					$lnObliga = ($lnFecHoraActual>=$lnFecHoraFinal?1:0);
					$laDiagnostico['OBLIGA']= $lnObliga ;
				}

				$lbNoExiste = true;
				foreach ($listaTempDiagnosticos as $lnIndex=>$laTempDx) {
					if ($laDiagnostico['DIAGNOSTICO']==$laTempDx['DIAGNOSTICO']) {
						$lbNoExiste = false;
						$listaTempDiagnosticos[$lnIndex]['ANALISIS'] .= $laDiagnostico['ANALISIS'];
						break;
					}
				}
				if ($lbNoExiste) {
					$this->aDxPpal[]=[
						'TIPO'=> $laDiagnostico['TIPO'],
						'DIAGNOSTICO'=> $laDiagnostico['DIAGNOSTICO'],
						'DESCRIPCION_CIE'=> $laDiagnostico['DESCRIPCION_CIE'],
						'OBLIGA'=> $laDiagnostico['OBLIGA'],
						];
					array_push($listaTempDiagnosticos,$laDiagnostico);
				}
			}
		}

		return $listaTempDiagnosticos;
	}

	public function DxPpal($tnIngreso=0) {
		if(count($this->aDxPpal)==0){
			$this->consultaDiagnosticos($tnIngreso);
		}
		return $this->aDxPpal;
	}

	public function getTexto() {
		return $this->cTextoC;
	}

	public function getDiagnosticos() {
		return $this->aListaDiagnosticos;
	}

	public function TipoDiagnostico()
	{
		$laTipo_Diagnostico = $this->oDb
			->select(['substr(TABCOD, 2, 1) AS TABCOD', 'trim(TABDSC) AS TABDSC'])
			->from('PRMTAB')
			->where(['TABTIP' => 'TDX'])
			->like('TABCOD', 'A%')
			->orderBy('TABCOD')
			->getAll('array');
		if(is_array($laTipo_Diagnostico)){
			$this->aTipo_Diagnostico=$laTipo_Diagnostico;
			$this->aTipoDX = [];
			foreach ($laTipo_Diagnostico as $laTipo) {
				$this->aTipoDX[$laTipo['TABCOD']] = $laTipo['TABDSC'];
			}
		}
		return $this->aTipo_Diagnostico;
	}

	public function ClaseDiagnostico()
	{
		$laClase_Diagnostico = $this->oDb
			->select(['substr(TABCOD, 2, 1) AS TABCOD', 'trim(TABDSC) AS TABDSC'])
			->from('PRMTAB')
			->where(['TABTIP' => 'TDX'])
			->Like('TABCOD', 'B%')
			->orderBy('TABCOD')
			->getAll('array');
		if(is_array($laClase_Diagnostico)){
			$this->aClase_Diagnostico=$laClase_Diagnostico;
			$this->aClaseDX = [];
			foreach ($laClase_Diagnostico as $laClase) {
				$this->aClaseDX[$laClase['TABCOD']] = $laClase['TABDSC'];
			}
		}
		return $this->aClase_Diagnostico;
	}

	public function TratamientoDiagnostico()
	{
		$laTratamiento_Diagnostico = $this->oDb
			->select(['trim(CL2TMA) AS TABCOD', 'trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'ESTDIAG',
				'CL1TMA' => 'TIPOTRA',
			])
			->orderBy('DE2TMA')
			->getAll('array');
		if(is_array($laTratamiento_Diagnostico)){
			$this->aTratamiento_Diagnostico=$laTratamiento_Diagnostico;
			foreach ($laTratamiento_Diagnostico as $laTratamiento) {
				$this->aTratamientoDX[$laTratamiento['TABCOD']] = $laTratamiento['TABDSC'];
			}
		}
		return $this->aTratamiento_Diagnostico;
	}

	public function TiposDescarte()
	{
		$laDescarteDiagnostico = $this->oDb
			->select(['trim(CL2TMA) AS TABCOD', 'trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'ESTDIAG',
				'CL1TMA' => 'DESCARTE',
			])
			->orderBy('DE2TMA')
			->getAll('array');
		if(is_array($laDescarteDiagnostico)){
			$this->aTiposDescarte=$laDescarteDiagnostico;
		}
		return $this->aTiposDescarte;
	}

	public function TablaDiagnosticos($tnFecha=0, $tcSexo='', $tcEdad='')
	{
		if (empty($tnFecha)){
			$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
			$tnFecha = $ltAhora->format('Ymd');
		}
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DESCIE', $tnFecha.' BETWEEN CL1TMA AND CL2TMA');
		$lcCampoDsc = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', '')) ;

		if (!empty($lcCampoDsc)){
			if (!empty($tcSexo)) {
				$laFiltroGenero = $tcSexo=='M'? ['A','H'] : ['A','M'];
				$this->oDb->in('GE2RIP', $laFiltroGenero);
			}

			$lcEdadAño = intval($tcEdad['y']??0);
			$lcEdadMeses = intval($tcEdad['m']??0);
			$lcEdadDia = intval($tcEdad['d']??0);
			$lnEdad = 0;

			if ($lcEdadAño>0){
				$lnEdad = 400 + $lcEdadAño;
			} else {
				if ($lcEdadMeses>0){
					$lnEdad = 300 + $lcEdadMeses;
				} else {
					if ($lcEdadDia>0){
						$lnEdad = 200 + $lcEdadDia;
					}
				}
			}
			if (!empty($lnEdad)) {
				$this->oDb->where("((EMNRIP=0 AND EMXRIP=0) OR ($lnEdad BETWEEN EMNRIP AND EMXRIP))");
			}

			$laListadoDiagnosticos = $this->oDb
				->select(['trim(ENFRIP) AS TABCOD', $lcCampoDsc])
				->from('RIACIE')
				->where(['ESTRIP'=>'A',])
				->getAll('array');
			if(is_array($laListadoDiagnosticos)){
				$this->aListadoDiagnosticos=$laListadoDiagnosticos;
			}
		}
		return $this->aListadoDiagnosticos;
	}

	public function consultaListaDiagnosticos($taDescripcion, $tcCodigo, $tlIncluirTodosLosEstados, $tcDatos)
	{
		$loDatos = json_decode($tcDatos);
		$tnFecha = $loDatos->fecha;
		$tcSexo = $loDatos->genero;
		$tcEdad = $loDatos->edad;
		$tcTipoConsulta = $loDatos->tipoconsulta;
		$lcParametroProcedimientos = $this->parametrosProcedimientos($tcTipoConsulta);

		if (empty($tnFecha)){
			$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
			$tnFecha = $ltAhora->format('Ymd');
		}
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DESCIE', $tnFecha.' BETWEEN CL1TMA AND CL2TMA');
		$lcCampoDsc = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', '')) ;

		if (!empty($lcCampoDsc)){
			if (!empty($tcSexo)) {
				$laFiltroGenero = $tcSexo=='M'? ['A','H'] : ['A','M'];
				$this->oDb->in('GE2RIP', $laFiltroGenero);
			}
			$lcEdadAño = intval($tcEdad->y??0);
			$lcEdadMeses = intval($tcEdad->m??0);
			$lcEdadDia = intval($tcEdad->d??0);
			$lcEdadDia=($lcEdadAño==0 && $lcEdadMeses==0 && $lcEdadDia==0)?1:$lcEdadDia;
			$lnEdad = 0;

			if ($lcEdadAño>0){
				$lnEdad = 400 + $lcEdadAño;
			} else {
				if ($lcEdadMeses>0){
					$lnEdad = 300 + $lcEdadMeses;
				} else {
					if ($lcEdadDia>0){
						$lnEdad = 200 + $lcEdadDia;
					}
				}
			}

			if (!empty($lnEdad)) {
				$this->oDb->where("((EMNRIP=0 AND EMXRIP=0) OR ($lnEdad BETWEEN EMNRIP AND EMXRIP))");
			}

			if(is_array($taDescripcion)==false){
				$taDescripcion = array(trim(strval($taDescripcion)));
			}
			$tcCodigo = trim(strval($tcCodigo));
			$tcCodigo = mb_strtoupper(!empty($tcCodigo)?'%'.$tcCodigo.'%':'');

			$laCampos = [
				"trim(substr(trim(DE2RIP), 1, 220)) DESCRIPCION",
				'trim(ENFRIP) CODIGO',
			];

			if(count($taDescripcion)>0){
				$lcWhereAux = '';
				foreach($taDescripcion as $lcNombre){
					if(!empty($lcNombre) && $lcNombre!=='*'){
						$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
						$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("( (TRANSLATE(UPPER(DE2RIP),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')) OR (TRANSLATE(UPPER(ENFRIP),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')))", $lcNombre, $lcNombre);
					}
				}
				$lcWhere= (!empty($lcWhereAux)? $lcWhereAux :'');
			}
			$lcWhere.= (!empty($tcCodigo)?sprintf(" AND (ENFRIP LIKE '%s')", $tcCodigo):'');
			$lcWhere.= ($tlIncluirTodosLosEstados==true ?'' : ($lcWhereAux=='' ? " ESTRIP='A'" : " AND ESTRIP='A'"));
			$lcOrden='DE2RIP';

			if ($tcTipoConsulta=='FA'){
				$laDiagnosticos = $this->oDb
					->select($laCampos)
					->from('RIACIE')
					->leftJoin('TABMAE AS B', 'trim(ENFRIP)=trim(B.DE1TMA)', null)
					->where($lcWhere)
					->where('B.TIPTMA', '=', 'USUDXM')
					->where('B.ESTTMA', '=', '')
					->orderBy($lcOrden)
					->getAll('array');

			}elseif ($tcTipoConsulta=='INTERCONSULTAS'){
				$laDiagnosticos = $this->oDb
				->select($laCampos)
				->from('RIACIE')
				->where($lcWhere)
				->where($lcParametroProcedimientos)
				->orderBy($lcOrden)
				->getAll('array');
			}
			elseif ($tcTipoConsulta=='PROCEDIMIENTO'){
				$laDiagnosticos = $this->oDb
				->select($laCampos)
				->from('RIACIE')
				->where($lcWhere)
				->where($lcParametroProcedimientos)
				->orderBy($lcOrden)
				->getAll('array');
			}
			else{
				$laDiagnosticos = $this->oDb
					->select($laCampos)
					->from('RIACIE')
					->where($lcWhere)
					->orderBy($lcOrden)
					->getAll('array');
			}
		}
		return $laDiagnosticos;
	}

	private function parametrosProcedimientos($tcSelect){
		$aParametrosSql =[];
		if($tcSelect == "INTERCONSULTAS" ){
			$aParametrosSql =[
				'TIPTMA' => 'DIAGRIPS',
				'CL1TMA' =>'VALCIEC'
			];
		} else{
			$aParametrosSql =[
				'TIPTMA' => 'DIAGRIPS',
				'CL1TMA' =>'VALCIEP'
			];
		}

		$laParametros = $this->oDb
		->select('DE2TMA')
		->from('TABMAE')
		->where($aParametrosSql)
		->get("array");

		if($this->oDb->numRows() > 0){
			return " 1=1 ".trim($laParametros['DE2TMA']);
		}
		return "1=1". " AND ( ENFRIP NOT LIKE 'V%' AND ENFRIP NOT LIKE 'Y%' AND ENFRIP NOT LIKE 'X%' AND ENFRIP NOT LIKE 'W%')";
	}
	
	private function parametrosCieOrdenes(){

		
		$lcParametros=" AND ( ENFRIP NOT LIKE 'V%' AND ENFRIP NOT LIKE 'Y%' AND ENFRIP NOT LIKE 'X%' AND ENFRIP NOT LIKE 'W%')";
		return $lcParametros;
		
		/* $aParametrosSql =[
			'TIPTMA' => 'DIAGRIPS',
			'CL1TMA' =>'VALCIEP'
		];

		$laParametros = $this->oDb
		->select('DE2TMA')
		->from('TABMAE')
		->where($aParametrosSql)
		->get("array");

		if($this->oDb->numRows() > 1){
			return "1=1".trim($laParametros['DE2TMA']);
		}
		return "1=1". " AND ( ENFRIP NOT LIKE 'R% AND ENFRIP NOT LIKE 'Z%')"; */
		//return "1=1". " AND ( ENFRIP NOT LIKE 'V%' AND ENFRIP NOT LIKE 'Y%' AND ENFRIP NOT LIKE 'X%' AND ENFRIP NOT LIKE 'W%' AND ENFRIP NOT LIKE 'R% AND ENFRIP NOT LIKE 'Z%')";
	}

	public function ListadoDiagnosticos()
	{
		return $this->aListadoDiagnosticos;
	}

	public function AyudaTipoDiagnostico()
	{
		$listaTempAyuda = '';
		$laayudaTipoDiagnostico = $this->oDb
			->select(['trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'CIE10',
				'CL1TMA' => 'DCIEWEB',
				'CL2TMA' => '1',
			])
			->orderBy('CL3TMA')
			->getAll('array');
		if(is_array($laayudaTipoDiagnostico)){
			foreach ($laayudaTipoDiagnostico as $ayudaTipoDiagnostico){
				$listaTempAyuda .=$ayudaTipoDiagnostico['TABDSC'];
			}
			$this->aAyuda_TipoDiagnostico=$listaTempAyuda;
		}
		return $this->aAyuda_TipoDiagnostico;
	}

	public function AyudaClaseDiagnostico()
	{
		$listaTempAyuda = '';
		$laayudaClaseDiagnostico = $this->oDb
			->select(['trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
				'TIPTMA' => 'CIE10',
				'CL1TMA' => 'DCIEWEB',
				'CL2TMA' => '2',
			])
			->orderBy('CL3TMA')
			->getAll('array');
		if(is_array($laayudaClaseDiagnostico)){
			foreach ($laayudaClaseDiagnostico as $ayudaClaseDiagnostico){
				$listaTempAyuda .=$ayudaClaseDiagnostico['TABDSC'];
			}
			$this->aAyuda_ClaseDiagnostico=$listaTempAyuda;
		}
		return $this->aAyuda_ClaseDiagnostico;
	}

	public function AyudaTratamientoDiagnostico()
	{
		$listaTempAyuda = '';
		$laayudaTratamientoDiagnostico = $this->oDb
			->select(['trim(DE2TMA) AS TABDSC'])
			->from('TABMAE')
			->where([
					'TIPTMA' => 'CIE10',
					'CL1TMA' => 'DCIEWEB',
					'CL2TMA' => '3',
					])
			->orderBy('CL3TMA')
			->getAll('array');
		if(is_array($laayudaTratamientoDiagnostico)){
			foreach ($laayudaTratamientoDiagnostico as $ayudaTratamientoDiagnostico){
				$listaTempAyuda .=$ayudaTratamientoDiagnostico['TABDSC'];
			}
			$this->aAyuda_TratamientoDiagnostico=$listaTempAyuda;
		}
		return $this->aAyuda_TratamientoDiagnostico;
	}

	public function obtenerDxFallece()
	{
		$this->aDxFallece = $this->oDb
			->select('TRIM(DESRIP) AS DESRIP, ENFRIP AS CODRIP')
			->from('RIACIEL1')
			->orderBy('DESRIP')
			->getAll('array');
	}

	public function ListaDxFallece()
	{
		return $this->aDxFallece;
	}

	public function buscarDX($tcCodigo='')
	{
		$lcTabla = 'RIACIE';
		$laWhere = ['ENFRIP'=>$tcCodigo];
		$laRetorno = false;

		$laTemp = $this->oDb->tabla($lcTabla)->where($laWhere)->get('array');
		if (is_array($laTemp)){
			if (count($laTemp)>0){
				$laRetorno = true;
			}
		}
		return $laRetorno;
	}

	public function buscarDxFallece($tcCodigo='')
	{
		$lcTabla = 'TABMAE';
		$laWhere = ['TIPTMA'=>'USUDXM', 'ESTTMA'=>'', 'DE1TMA'=>$tcCodigo,];
		$laRetorno = false;

		$laTemp = $this->oDb->tabla($lcTabla)->where($laWhere)->get('array');
		if (is_array($laTemp)){
			if (count($laTemp)>0){
				$laRetorno = true;
			}
		}
		return $laRetorno;
	}

	public function consultaDiagnosticosPaciente($tnIngreso=0)
	{
		$tnIngreso = intval($tnIngreso);
		$laConsultar = $this->oDb->distinct()
			->select('trim(A.DIPEDC) CODIGO, trim(B.DE2RIP) DESCRIPCIONCIE')
			->tabla('EVODIA AS A')
			->leftJoin('RIACIE AS B', 'trim(A.DIPEDC)=trim(B.ENFRIP)', null)
			->where(['INGEDC'=>$tnIngreso, 'DCAEDC'=>''])
			->getAll('array');
		return $laConsultar;
	}

	public function consultaDX($tcDX='')
	{
		$lcDescrip = '';
		if (!empty($tcDX)){
			$laConsultar = $this->oDb
				->select('trim(DESRIP) AS DESCRIP')
				->tabla('RIACIE')
				->where('ENFRIP', '=', $tcDX)
				->get('array');
			$lcDescrip=$laConsultar['DESCRIP'];
		}
		return $lcDescrip;
	}

	public function consultaDxDescartados($tnIngreso=0)
	{
		$laDxDescarte = $this->oDb
			->select('A.INDEDC TIPO, A.CLIEDC CLASE, TRIM(A.TRAEDC) TRATAMIENTO, TRIM(A.DIPEDC) DIAGNOSTICO, trim(substr(trim(C.DE2RIP), 1, 220)) DESCRIPCION_CIE, TRIM(A.DCAEDC) DESCARTE, FECEDC, HOREDC, 0 AS OBLIGA')
			->select('TRIM(D.DE2TMA) TIPO_DESCARTE')
			->select('(SELECT TRIM(C.TABDSC) FROM PRMTAB AS C WHERE C.TABTIP=\'TDX\' AND C.TABCOD LIKE \'A%\' AND INT(SUBSTR(TABCOD,2,1))=A.INDEDC FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_TIPO')
			->select('(SELECT TRIM(C.TABDSC) FROM PRMTAB AS C WHERE C.TABTIP=\'TDX\' AND C.TABCOD LIKE \'B%\' AND INT(SUBSTR(TABCOD,2,1))=A.CLIEDC FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_CLASE')
			->select('(SELECT TRIM(C.DE2TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'ESTDIAG\' AND C.CL1TMA=\'TIPOTRA\' AND TRIM(C.CL2TMA)=TRIM(A.TRAEDC) FETCH FIRST 1 ROWS ONLY) AS DESCRIPCION_TRATAMIENTO')
			->tabla('EVODIA AS A')
			->leftJoin('RIACIE AS C', "TRIM(A.DIPEDC)=TRIM(C.ENFRIP)", null)
			->leftJoin('TABMAE AS D', "TRIM(A.DCAEDC)=TRIM(D.CL2TMA) AND D.TIPTMA='ESTDIAG' AND D.CL1TMA='DESCARTE'", null)
			->where('A.INGEDC', '=', $tnIngreso)
			->where('A.DCAEDC', '<>', ' ')
			->getAll('array');

		return $laDxDescarte;
	}
	
	public function consultarDiagnosticoPrincipal($tnIngreso=0)
	{
		$laDiagnosticoPrincipal=[];
		$laConsultarDiagnostico = $this->oDb
			->select('trim(A.DIPEDC) CODIGO, trim(B.DE2RIP) DESCRIPCIONCIE')
			->tabla('EVODIA A')
			->leftJoin('RIACIE B', 'trim(A.DIPEDC)=trim(B.ENFRIP)', null)
			->where('A.INGEDC', '=', $tnIngreso)
			->where('A.INDEDC', '=', 1)
			->where('A.DCAEDC', '=', '')
			->where('A.DIPEDC', '<>', '')
			->orderBy('A.FECEDC DESC, A.HOREDC DESC')
			->get('array');
		if ($this->oDb->numRows()>0){
			$laDiagnosticoPrincipal=$laConsultarDiagnostico;
		}else{
			$laConsultarDiagnostico = $this->oDb
				->select('trim(A.CIEINC) CODIGO, trim(B.DE2RIP) DESCRIPCIONCIE')
				->tabla('RIAINGC A')
				->leftJoin('RIACIE B', 'trim(A.CIEINC)=trim(B.ENFRIP)', null)
				->where('A.INGINC', '=', $tnIngreso)
				->orderBy('A.IDEINC DESC')
				->get('array');
			if ($this->oDb->numRows()>0){
				$laDiagnosticoPrincipal=$laConsultarDiagnostico;
			}	
		}	
		unset($laConsultarDiagnostico);
		return $laDiagnosticoPrincipal;
	}
	
}