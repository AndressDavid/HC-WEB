<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

use NUCLEO\AplicacionFunciones;
class MedicamentoFormula
{
	public $ltFechaHoraSistema= "";
    public $nCodigoShaio = null;
    public $nCantidadMedicamento = null;
    public $nCantidadDosis = null;
    public $nTipoDosis = null;
    public $nIntervaloFrecuencia = null;
    public $nUnidadFrecuencia = null;
    public $nViaAdministracion = null;
    public $nEstadoMedicamentoFormula = null;
    public $nDias = null;
    public $nCantidad = null;
    public $nDescripcionCantidad = null;
    public $cObservaciones = null;
	public $aMedProgra = array();
	public $aMedNoProgra = array();
	public $aIngresosConFormula = array();
	public $aMediFormulados = array();
	public $nConsecutivoDispensacion = null;
	public $aDispensar = array();

	protected $oDb;
	protected $aobjOblANT = [];
	protected $nAntibioticoHrReiniciaConteo=0;


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->nAntibioticoHrReiniciaConteo=intval($this->oDb->obtenerTabmae1('OP3TMA', 'FORMEDIC', "CL1TMA='ANTIBI' AND CL2TMA='HRREINIC' AND ESTTMA=''", null, 48));

	}

	public function consultaUltimaFormulaIngreso($tnIngreso=0){
		global $goDb;
		$laDatosFormula=[];
		$llNuevaFormula=false;
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lnFechaSistema = intval($ltAhora->format('Ymd'));

		if(isset($goDb)){
			$laFormulaUltima = $goDb
				->select('f.CDNFRD CONSECFORMULA, TRIM(f.MEDFRD) CODIGO, TRIM(f.OBSFRD) OBSERVACIONES, f.DOSFRD DOSIS, TRIM(f.UDOFRD) CODUNIDADDOSIS')
				->select('f.FRCFRD FRECUENCIA, TRIM(f.UFRFRD) CODUNIDADFRECUENCIA, TRIM(f.VIAFRD) VIA')
				->select('INT(f.DADFRD) DIASINGRESAANTIBIOTICO, f.CANFRD CANTIDAD, TRIM(f.MCDFRD) MEDCAMBIO, f.cevfrd CONSECEVOLUCION')
				->select('f.ESTFRD ESTADO, f.FEFFRD FECHA_FORMULA, f.HMFFRD HORA_FORMULA, TRIM(B.STSDES) ESTADO_MEDICAMENTO')
				->select('f.FECFRD FECHA_CREACION_FORMULA, f.HORFRD HORA_CREACION_FORMULA')
				->select('(SELECT TRIM(RF4DES) FROM INVDES WHERE REFDES=f.MEDFRD) AS POSNOPOS')
				->select('(SELECT TRIM(DESDES) FROM INVDES WHERE REFDES=f.MEDFRD) AS DESCRIPCION')
				->select('(SELECT TRIM(DESDES) FROM INVDES WHERE REFDES=f.MCDFRD) AS DESCRIPCION_MEDCAMBIO')
				->select('(SELECT TRIM(CL03DES) FROM INVATTR WHERE REFDES=f.MEDFRD) AS CONTROLADO')
				->select('(SELECT TRIM(CL18DES) FROM INVATTR WHERE REFDES=f.MEDFRD) AS HIPERPULMONAR')
				->select('(SELECT TRIM(CL19DES) FROM INVATTR WHERE REFDES=f.MEDFRD) AS LISTADOUNIRS')
				->select('(SELECT TRIM(CL21DES) FROM INVATTR WHERE REFDES=f.MEDFRD) AS FORMULAPORVIA')
				->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDDOS\' AND CL2TMA=f.UDOFRD AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESCRUNIDADDOSIS')
				->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDFRE\' AND CL2TMA=f.UFRFRD AND CL3TMA=\'F\' AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESCRUNIDADFRECUENCIA')
				->select('(SELECT TRIM(DE1TMA) FROM TABMAE WHERE TIPTMA=\'MEDVAD\' AND CL1TMA=f.VIAFRD AND ESTTMA=\' \' FETCH FIRST 1 ROWS ONLY) AS DESCRVIA')
				->select('(SELECT TRIM(TMUNIA) FROM RIAFARDA WHERE NINFRA=f.NINFRD AND CDNFRA=f.CDNFRD AND CEVFRA=f.CEVFRD AND MEDFRA=f.MEDFRD) AS TIPOMEDICAMENTO')
				->select('	(SELECT DISTINCT TRIM(m.nommed)||\' \'||TRIM(m.nnomed) FROM formed AS fd LEFT JOIN riargmn AS m ON fd.usrfmd = m.usuari WHERE	fd.ninfmd=f.ninfrd AND fd.medfmd=f.medfrd AND fd.cevfmd=(SELECT MAX(r.cevfmd) FROM formed AS r WHERE r.ninfmd=fd.ninfmd AND r.medfmd=fd.medfmd)) AS NOMBRE_MEDICO')
				->from('RIAFARD AS f')
				->leftJoin("INVDES b", "TRIM(f.MEDFRD)=TRIM(b.REFDES)", null)
				->where('f.ninfrd', '=', $tnIngreso)
				->where('f.ESTFRD', '<>', 14)
				->where('f.cdnfrd=(SELECT MAX(rf.cdnfrd) FROM riafard AS rf WHERE rf.ninfrd=f.ninfrd)')
				->getAll('array');

			if($goDb->numRows()>0){
				foreach ($laFormulaUltima as &$laDatos){
					$lnDifFecha=$lnDiasUso=$lnDiasUsadoAntibiotico=$lnBuscarNuevo=$lnCantidadRegistros=$lnDiferenciaHorasAdicional=$lnNumDiasAntib=0;
					$lnObtenerRegistros=$lnRegistroActual=$lnDiferenciaHorasRegistrar=$lnFechaInicioAntibiotico=$lnFechaSuspendeAntibiotico=0;
					$lcCodigoMedicamento=$laDatos['CODIGO'];
					$lnDiasUso=$laDatos['DIASINGRESAANTIBIOTICO'];
					$lcCodigoUnidadFrecuencia=isset($laDatos['CODUNIDADFRECUENCIA']) ? $laDatos['CODUNIDADFRECUENCIA'] : '';
					$lcDescripcionUnidadFrecuencia=isset($laDatos['DESCRUNIDADFRECUENCIA']) ? $laDatos['DESCRUNIDADFRECUENCIA'] : '';
					$lnDosis=number_format($laDatos['DOSIS'],2,'.','');
					$llNuevaFormula=intval($laDatos['FECHA_FORMULA'])<$lnFechaSistema ? true : false;
					$lnEstado=($llNuevaFormula && $laDatos['ESTADO']!=14) ? 99 : $laDatos['ESTADO'];
					if ($lnEstado===6){ $lnEstado=15; }
					if ($lnEstado===2){ $lnEstado=16; }
					if ($lnEstado<11){ $lnEstado=11; }
					$lnDiasMaximoAntibiotico=intval($this->oDb->obtenerTabmae1('OP3TMA', 'ANTIBI', "DE1TMA='$lcCodigoMedicamento' AND ESTTMA=''", null, 0));
					$lcControlAlertaAntibiotico=$this->oDb->obtenerTabmae1('OP1TMA', 'ANTIBI', "DE1TMA='$lcCodigoMedicamento' AND ESTTMA=''", null, '');
					$lnFechaSuspendeAntibiotico=intval($lnEstado)===14?intval($laDatos['FECHA_FORMULA']):'';

					if ($lnDiasMaximoAntibiotico>0){
						$lacrTmpRecetado = $goDb
							->select('trim(MEDFMD) MEDFMD, FECFMD, HORFMD, trim(UD1FMD) UD1FMD, 00000 AS DIF_HORAS, FIAFMD FECHA_INICIO_ANTIBIOTICO, FSAFMD FECHA_SUSPENDE_ANTIBIOTICO')
							->from('formedl1')
							->where('NINFMD', '=', $tnIngreso)
							->where('MEDFMD', '=', $lcCodigoMedicamento)
							->where('FIAFMD', '>', 0)
							->orderBy('FECFMD DESC, HORFMD  DESC')
							->getAll('array');
						$lnCantidadRegistros=$goDb->numRows();

						if($goDb->numRows()>0){
							$lnDiferenciaHoras=0;
							$ltFechaHoraSiguiente = new \DateTime( $this->oDb->fechaHoraSistema());
							$lnNumDiasAntib = intval($lacrTmpRecetado[0]['UD1FMD']);
							$lnFechaInicioAntibiotico = intval($lacrTmpRecetado[0]['FECHA_INICIO_ANTIBIOTICO']);
							$lnFechaSuspendeAntibiotico = intval($lacrTmpRecetado[0]['FECHA_SUSPENDE_ANTIBIOTICO'])>0?intval($lacrTmpRecetado[0]['FECHA_SUSPENDE_ANTIBIOTICO']) : $lnFechaSuspendeAntibiotico;

							foreach ($lacrTmpRecetado as &$laReceta){
								$lnFechaInicioAntibiotico=$lnFechaInicioAntibiotico==0 ? intval($laReceta['FECFMD']): $lnFechaInicioAntibiotico;
								$llFechaFormula = $laReceta['FECFMD'].str_pad($laReceta['HORFMD'], 6, '0', STR_PAD_LEFT);
								$llFechaFormato = date_create_from_format('YmdHis', $llFechaFormula);
								$loIntervalo = $ltFechaHoraSiguiente->diff($llFechaFormato);
								$lnDiferenciaHoras = intval(($loIntervalo->y * 365.25 + $loIntervalo->m * 30 + $loIntervalo->d) * 24 + $loIntervalo->h + $loIntervalo->i/60);
								$ltFechaHoraSiguiente = date_create_from_format('YmdHis', $llFechaFormula);
								$lnDiferenciaHorasAdicional=$lnDiferenciaHoras;
								$laReceta['DIF_HORAS']=$lnDiferenciaHoras;
								$lnDiferenciaHorasRegistrar=$lnDiferenciaHoras;
							}

							foreach ($lacrTmpRecetado as $laReceta2){
								$lnDif_horas=intval($laReceta2['DIF_HORAS']);
								if ($lnDif_horas > $this->nAntibioticoHrReiniciaConteo){
									$lnBuscarNuevo=0;
								}else{
									$lnBuscarNuevo=$lnCantidadRegistros>1 ? 1 : 0;
									break;
								}
							}
							$lnDiferenciaHoras=0;
							if ($lnBuscarNuevo==0){
								if ($lnDiferenciaHorasAdicional > $this->nAntibioticoHrReiniciaConteo){
									$lnDifFecha=0;
									$lnNumDiasAntib=0;
									$lnDiasUso=0;
									$lnDifHoras=$lnDiferenciaHorasAdicional;
								}else{
									$lnDifFecha=1;
									$lnNumDiasAntib=$lnNumDiasAntib;
								}
							}else{
								$lnObtenerRegistros=$lnCantidadRegistros;
								if ($lnDiferenciaHorasAdicional > $this->nAntibioticoHrReiniciaConteo){
									$lnObtenerRegistros=$lnCantidadRegistros - 1;
								}
								// $lnFechaFormula = intval($lacrTmpRecetado[0]['FECFMD']);
								$lnFechaFormula = intval($lnFechaInicioAntibiotico);
								$ldFechaIni = \DateTime::createFromFormat('Ymd', $lnFechaSistema);
								$ldFechaFin = \DateTime::createFromFormat('Ymd', $lnFechaFormula);
								$loDifFecha = $ldFechaIni->diff($ldFechaFin);
								$lnDifFecha = $loDifFecha->days;
								$lnDifFecha = $lnDifFecha<=0 ? 1 : $lnDifFecha;
							}
						}
					}
					$lcCodigoGrupoFarmacologico=$lcDescripcionGrupoFarmacologico='';
					$lcDatosGrupoFarmacologico=$this->fcGrupoFarmacologico($laDatos['CODIGO'],true,true);
					$lcCodigoGrupoFarmacologico=explode('~', $lcDatosGrupoFarmacologico)[0] ?? '';
					$lcDescripcionGrupoFarmacologico=explode('~', $lcDatosGrupoFarmacologico)[1] ?? '';
					$laDatos['GRUPOCODFARMACEUTICO']=$lcCodigoGrupoFarmacologico;
					$laDatos['DESCRGRUPOCODFARMACEUTICO']=$lcDescripcionGrupoFarmacologico;
					$laDatos['DIASMAXANTIBIOTICO']=$lnDiasMaximoAntibiotico;
					$laDatos['ESANTIBIOTICO']=$lnDiasMaximoAntibiotico>0?true:false;
					$laDatos['DIASUSADOANTIB']=$lnDiasUso;
					$laDatos['DIASINGRESAANTIBIOTICO']=$lnDiasUso;
					$laDatos['SELEC1']=$lnDifFecha;
					$laDatos['SUSP']=$laDatos['ESTADO']==14 ? 1 : 0;
					$laDatos['ESTADO']=$lnEstado;
					$laDatos['ESTDETORIG']=$lnEstado;
					$laDatos['CONTROLALERTAANTIB']=$lcControlAlertaAntibiotico;
					$laDatos['HRSINUSO']=$lnDiferenciaHorasRegistrar;
					$laDatos['DOSIS']=$lnDosis;
					$laDatos['FECHAINICIOANTIBIOTICO']=$lnDiasMaximoAntibiotico>0?$lnFechaInicioAntibiotico:'';
					$laDatos['FECHAFINALANTIBIOTICO']=$lnDiasMaximoAntibiotico>0?$lnFechaSuspendeAntibiotico:'';
					$laDatos['CODUNIDADFRECUENCIA']=$lcDescripcionUnidadFrecuencia!='' ? $lcCodigoUnidadFrecuencia : '';
				}
			}
		}
		return $laFormulaUltima;
	}

	public function consultarMedicamentoCambio($tnIngreso=0,$tcMedicamentoActual='',$tnConsecutivoFormula=0,$tcMedicamentoCambiar='',$tcViaCambiar=''){

		$lcObservaciones='';
		$laDatosMedCambio = $this->oDb
			->select('TRIM(OB2FRD) DESCRIPCION')
			->from('RIAFARDIF')
			->where('NINFRD', '=', $tnIngreso)
			->where('MEDFRD', '=', $tcMedicamentoActual)
			->where('CDNFRD', '=', $tnConsecutivoFormula)
			->where('MCDFRD', '=', $tcMedicamentoCambiar)
			->where('VIAFRD', '=', $tcViaCambiar)
			->get('array');
		if ($this->oDb->numRows()>0){
			$lcObservaciones=$laDatosMedCambio['DESCRIPCION'];
		}	
		unset($laDatosMedCambio);
		return $lcObservaciones;
	}

	public function consultarUsoAntibiotico($tnIngreso=0,$tcMedicamento='',$tnDiasAntibioitico=0)
	{
		$laUsoAntibiotico=[];
		$ltAhora=new \DateTime( $this->oDb->fechaHoraSistema() );
		$lnFechaSistema=$ltAhora->format('Ymd');
		$lnFechaInicial=date('Y-m-d', strtotime($lnFechaSistema."+ $tnDiasAntibioitico days"));
		$lnFechaInicial=intval(trim(str_replace('-','',$lnFechaInicial)));

		$laDatosUsoAntibiotico = $this->oDb
			->select('trim(DIGANT) CODDIAGI, trim(DIOANT) CODDIAGA, trim(OTRANT) OTROSDIA, trim(TRAANT) CODTRATA, trim(AJUANT) CODAJUST')
			->select('trim(OBSANT) OBSERVA, trim(MUEANT) CODMUES, FEFANT FECHAFIN, trim(RESANT) RESULTA')
			->from('USOANT')
			->where('INGANT', '=', $tnIngreso)
			->where('MEDANT', '=', $tcMedicamento)
			->where($lnFechaInicial.' BETWEEN FECANT AND FEFANT')
			->in('ESTANT', ['1','2','3','4','9',])
			->get('array');
		return $laDatosUsoAntibiotico;
	}

	public function consultarAlertaAntibiotico($tnIngreso=0,$tcMedicamento='',$tnFechaFormulacion=0,$tnDiasAntibiotico=0)
	{
		$lcDatosAlerta='';
		$tnDiasAntibiotico=intval($tnDiasAntibiotico);
		$lnFechaInicial=intval($tnFechaFormulacion);
		$lnFechaFinal=date('Y-m-d', strtotime($lnFechaInicial."+ $tnDiasAntibiotico days"));
		$lnFechaFinal=intval(trim(str_replace('-','',$lnFechaFinal)));

		$laDatosAlertaAntibiotico = $this->oDb
			->select('A.ESTANT ESTADO, trim(B.DE2TMA) DESCRIPCION, trim(A.OBVANT) OBSERVACION')
			->from('USOANT AS A')
			->leftJoin("TABMAE AS B", "A.MTVANT=B.CL2TMA AND B.TIPTMA='USOANTIB' AND B.CL1TMA = 'MOTIVOS'", null)
			->where('INGANT', '=', $tnIngreso)
			->where('MEDANT', '=', $tcMedicamento)
			->where('ESTANT', '=', '2')
			->between('FEFANT', $lnFechaInicial, $lnFechaFinal)
			->get('array');

			if ($this->oDb->numRows()>0){
				$lcDatosAlerta=$laDatosAlertaAntibiotico['DESCRIPCION'] . ' - ' . $laDatosAlertaAntibiotico['OBSERVACION'];
			}
		return $lcDatosAlerta;
	}

	public function objetosObligatoriosAntibioticos($tcTitulo='')
	{
		$laCondiciones = ['TIPTMA'=>'PAROMWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblANT = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy ('OP3TMA')
			->getAll('array');
	}

	public function ObjObligatoriosAntib()
	{
		return $this->aobjOblANT;
	}

	public function consultarIngresosConFormula($tcFechaFormula, $tcSeccion='', $tnVia=''){
		$ltFechaFormula = str_replace(array('','-'),'',$tcFechaFormula);
		global $goDb;
		if(isset($goDb)){
			$laCondiciones = ['FECHA_FORMULA'=>$ltFechaFormula];
			if ($tcSeccion !== '') $laCondiciones['SECCION'] = $tcSeccion;
			if ($tnVia !== '') $laCondiciones['VIA_INGRESO'] = $tnVia;
			$laCampos =['INGRESO', 'FECHA_FORMULA', 'ESTADO_FORMULA', 'PRIMER_NOMBRE', 'SEGUNDO_NOMBRE',
						'PRIMER_APELLIDO', 'SEGUNDO_APELLIDO', "IFNULL(SECCION, '--') AS SECCION",
						"IFNULL(HABITACION,'--') AS HABITACION, VIA_INGRESO, DESCRIP_VIA"];
			$laIngresosConFormula = $goDb
					->distinct()
					->select($laCampos)
					->from("VW_RIAFARD")
					->where($laCondiciones)
					->orderBy("SECCION, HABITACION ASC")
					->getAll();
				$this->aIngresosConFormula = $laIngresosConFormula;
				return $laIngresosConFormula;
		}
	}
	public function consultarMedicamentosProgramados($tnIngreso=0){
		$this->nIngreso=0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$this->nIngreso = $tnIngreso;
				$ltFechaHoraSistema = new \DateTime( $goDb->fechaHoraSistema() );
				$lcHorasAntes = '3';
				$lcHorasDespues = '3';
				$ltFechaHoraIni = new \DateTime($ltFechaHoraSistema->format("YmdHis"));
				$ltFechaHoraIni->sub(new \DateInterval('PT'.$lcHorasAntes.'H'));
				$lcFechaHoraIni = $ltFechaHoraIni->format("YmdHis");
				$ltFechaHoraFin = new \DateTime($ltFechaHoraSistema->format("YmdHis"));
				$ltFechaHoraFin->add(new \DateInterval('PT'.$lcHorasDespues.'H'));
				$lcFechaHoraFin = $ltFechaHoraFin->format("YmdHis");
				$laDatos = $goDb
					->select("MEDIC.INGADM AS N_Ingreso, MEDIC.NDOADM AS N_DOSIS, MEDIC.ESTADM AS EST_FORM,  MEDIC.DOSADM AS DOSIS")
					->select("MEDIC.DDOADM AS D_DOSIS, MAE2.DE1TMA AS DES_DOSIS, MEDIC.FREADM AS CADA_FREC, MEDIC.DFRADM AS COD_FREC")
					->select("MAE3.DE1TMA AS DESC_FREC, MEDIC.VIAADM AS VIA,  MAE1.DE1TMA AS DESC_VIA, MEDIC.NDFADM AS DIAS_FORMU")
					->select("MEDIC.NDAADM AS DIAS_ADMINI, MEDIC.OBMADM AS OBS_MED, MEDIC.OBSADM AS OBS_ADM, MEDIC.FEPADM AS FEC_PROGRA")
					->select("MEDIC.HDPADM AS HORA_PROGRA, MEDIC.UD1ADM AS USU_ADMIN, MEDIC.FEAADM AS FECHA_ADMIN, MEDIC.HDAADM AS HORA_ADMIN")
					->select("MEDIC.MEDADM AS COD_MEDICA, INVE.DESDES AS MDDADM, MEDIC.CTUADM AS CONS_TURNO, MEDIC.CEVADM AS CONS_EVOLUCI, MEDIC.CCOADM AS CONS_ADMIN")
					->from("ENADMMD AS MEDIC")
					->leftJoin("INVDES AS INVE", "MEDIC.MEDADM=INVE.REFDES", null)
					->leftJoin("TABMAE AS MAE1", "MEDIC.VIAADM = MAE1.CL1TMA AND MAE1.TIPTMA = 'MEDVAD' AND MAE1.ESTTMA='' ", null)
					->leftJoin("TABMAE AS MAE2", "INTEGER(MEDIC.DDOADM) = INTEGER(MAE2.CL1TMA) AND MAE2.TIPTMA = 'MEDDOS' AND MAE2.ESTTMA=''", null)
					->leftJoin("TABMAE AS MAE3", "MEDIC.DFRADM = MAE3.CL2TMA AND MAE3.TIPTMA = 'MEDFRE'  AND MAE3.ESTTMA=''", null)
					->where([
						"MEDIC.INGADM"=>$this->nIngreso,
						])
					->between('MEDIC.FEPADM *1000000+MEDIC.HDPADM', $lcFechaHoraIni, $lcFechaHoraFin)
					->orderBy("MEDIC.FEPADM, MEDIC.HDPADM, INVE.DESDES")
					->getAll();
				$this->aMedProgra = $laDatos;
				return $laDatos;
			}
		}
	}
	public function consultarMedicamentosNoProgramados($tnIngreso=0){
		$this->nIngreso=0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$this->nIngreso = $tnIngreso;
				$lcSql = "SELECT DISTINCT FORM.CDNFRD AS FORMULA, MEDICT.INGADM AS N_Ingreso, MEDICT.NDOADM AS N_DOSIS, MEDICT.ESTADM AS EST_FORM, "
					."MEDICT.DOSADM AS DOSIS, MEDICT.DDOADM AS D_DOSIS, MAE2.DE1TMA AS DES_DOSIS, MEDICT.FREADM AS CADA_FREC, "
					."MEDICT.DFRADM AS COD_FREC, MAE3.DE1TMA AS DESC_FREC, MEDICT.VIAADM AS VIA,  MAE1.DE1TMA AS DESC_VIA, MEDICT.NDFADM AS DIAS_FORMU, "
					."MEDICT.NDAADM AS DIAS_ADMINI, MEDICT.OBMADM AS OBS_MED, MEDICT.OBSADM AS OBS_ADM, MEDICT.FEPADM AS FEC_PROGRA, "
					."MEDICT.HDPADM AS HORA_PROGRA, MEDICT.UD1ADM AS USU_ADMIN, MEDICT.FEAADM AS FECHA_ADMIN, MEDICT.HDAADM AS HORA_ADMIN, "
					."INVE.REFDES AS CODI_MEDIC, INVE.DESDES AS MEDICAMENTO, MEDICT.FEOADM AS FECHA_ORDEN, MEDICT.HDOADM AS HORA_ORDEN, FORM.ESTFRD, FORM.FEFFRD, "
					."FORM.HMFFRD "
				."FROM ENADMMDT AS MEDICT "
					."LEFT JOIN INVDES  AS INVE ON MEDICT.MEDADM=INVE.REFDES "
					."LEFT JOIN RIAFARD AS FORM ON MEDICT.INGADM = FORM.NINFRD AND MEDICT.MEDADM=FORM.MEDFRD "
					."LEFT JOIN TABMAE AS MAE1 ON MEDICT.VIAADM = MAE1.CL1TMA AND MAE1.TIPTMA = 'MEDVAD' "
					."LEFT JOIN TABMAE AS MAE2 ON INTEGER(MEDICT.DDOADM) = INTEGER(MAE2.CL1TMA) AND MAE2.TIPTMA = 'MEDDOS' "
					."LEFT JOIN TABMAE AS MAE3 ON MEDICT.DFRADM = MAE3.CL2TMA AND MAE3.TIPTMA = 'MEDFRE' "
				."WHERE MEDICT.INGADM= '$tnIngreso' AND MEDICT.ESTADM IN ('11','12','13','14') AND MEDICT.ESPAMD='9' AND FORM.ESTFRD != '99' "
					."AND FORM.CDNFRD = (SELECT MAX(CDNFRD) FROM RIAFARD WHERE NINFRD=MEDICT.INGADM AND MEDFRD=MEDICT.MEDADM) "
					."AND MEDICT.CTUADM BETWEEN FORM.CDNFRD-1 AND FORM.CDNFRD "
					."ORDER BY FORM.CDNFRD, MEDICT.INGADM, MEDICT.FEPADM, MEDICT.HDPADM, INVE.DESDES ";
				$laDatos = $goDb->query($lcSql);
				$this->aMedNoProgra = $laDatos;
				return $laDatos;
			}
		}
	}
	public function cargarMedicamentosFormulados($tcFechaFormula, $tnIngreso=0, $tnCodBodega=0){
		$this->nIngreso=0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso > 0){
				$this->nIngreso = $tnIngreso;
				$ltFechaSistema = new \DateTime($goDb->fechaHoraSistema());
				$lcAnioFecha = substr($ltFechaSistema->format("Ymd"),0,4);
				$ltFechaFormula = str_replace(array('/','-'), '', $tcFechaFormula);
				$laCampos = ['INGRESO', 'FECHA_FORMULA', 'COD_MEDI', 'MEDICAMENTO', 'DOSIS', 'UNIDAD', 'DES_DOSIS',
							'COD_FREC', 'DESC_FREC','VIA', 'DESC_VIA', 'DISPENSAR', 'IFNULL(DESPACHADO, 0) AS DESPACHADO',
							'INT(SAIRES+C01RES+C02RES+C03RES+C04RES+C05RES+C06RES+C07RES+C08RES+C09RES+C10RES+C11RES+C12RES) AS SALDO',
							'CDNFRD', 'CCOFRD', 'CEVFRD', 'ESTMED'];
				$laCondiciones = ['INGRESO' => $this->nIngreso, 'FECHA_FORMULA' => $ltFechaFormula];
				$laMediFormulados = $goDb
								->distinct()
								->select($laCampos)
								->from('VW_FARD01 AS V')
								->leftJoin('INVRES AS I', "V.COD_MEDI=I.PRORES  AND ANORES='$lcAnioFecha' AND BODRES='$tnCodBodega' AND TRARES='' AND CCORES=''", null)
								->where($laCondiciones)
								->orderBy('MEDICAMENTO')
								->getAll();
				$this->aMediFormulados = $laMediFormulados;
				return $laMediFormulados;
			}
		}
	}
	public function consecutivoDispensacion($tcFechaFormula='', $tnIngreso=0){
		$this->nIngreso=0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso > 0){
				$this->nIngreso = $tnIngreso;
					$lcSql = "SELECT IFNULL(MAX(CEVFRD),0)+1  AS CONDISP "
							."FROM RIAFARDF "
							."WHERE NINFRD = $this->nIngreso "
							."AND CDNFRD = (SELECT IFNULL(MAX(CDNFRD), 0) AS CONSEFORMULA "
											."FROM RIAFARD "
											."WHERE NINFRD = :lnIngreso "
											."AND FEFFRD = :lnFechaFormula "
											."AND ESTFRD IN('11','12','13','16')) ";
			 	 	$laDatos = [
								'lnIngreso' => $this->nIngreso,
								'lnFechaFormula' => $tcFechaFormula,
								];
					$lnConsecutivo = $goDb->query($lcSql, $laDatos, true);
					$this->nConsecutivoDispensacion = $lnConsecutivo[0]['CONDISP'];
					return $lnConsecutivo;
			}
		}
	}
	public function faDispensarMedicamentos($tcFechaFormula, $tnIngreso, $tnCodBodega, $tnCodigoQr){
		$this->nIngreso = 0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso > 0){
				$this->nIngreso = $tnIngreso;
				$ltFechaHoraSistema = new \DateTime( $goDb->fechaHoraSistema() );
				$lcAnioFecha = substr($ltFechaHoraSistema->format("Ymd"),0,4);
				$lcAnioMesDiaSis = substr($ltFechaHoraSistema->format("Ymd"),0,8);
				$ltFechaFormula = str_replace(array('/', '-'), '', $tcFechaFormula);
				$ltFechaInicia = strtotime( '+5 day' , strtotime($lcAnioMesDiaSis));
				$ltFechaInicia = date('Ymd', $ltFechaInicia);
				$lnCodbar = substr($tnCodigoQr, 0, 20);
				$lnCodBun = substr($tnCodigoQr, 20, 25);
				$lnDatosRia = $goDb
							->select('ESTING')
							->from('RIAING')
							->where(['NIGING'=>$this->nIngreso])
							->get('array');
				if( ($lnDatosRia['ESTING']) == 2){
					$laDatosSim = $goDb
						->select('CODSHA, FECVEN')
						->from('SIMNETI')
						->where(['CODBAR'=>$lnCodbar])
						->get('array');
					if(is_array($laDatosSim)==true){
						if(count($laDatosSim) > 0){
							$laDispensar = [
								'CODSHA' => trim($laDatosSim['CODSHA']),
								'FECVEN' => trim($laDatosSim['FECVEN']),
								'DISPENSAR' => 0,
								'CAN_PENDI' => 0,
								'DESPACHADO' => 0,
								'SALDO' => 0,
								'LNCODBUN'=>$lnCodBun,
								'CODMEN'=>0,
								'MENSAJE'=>'',
							];
							$laCondiciones = ['ANORES'=>$lcAnioFecha, 'PRORES'=>$laDispensar['CODSHA'], 'BODRES'=>$tnCodBodega,'TRARES'=>'','CCORES'=>''];
							$laDatosInv = $goDb
								->select('INT(SAIRES+C01RES+C02RES+C03RES+C04RES+C05RES+C06RES+C07RES+C08RES+C09RES+C10RES+C11RES+C12RES) AS SALDO')
								->from('INVRES')
								->where($laCondiciones)
								->get('array');
							//Valida saldo del medicamento en bodega
							if(is_array($laDatosInv)==true  && $laDatosInv['SALDO'] > 0){
									$laCondiciones = ['R.NINFRD'=>$this->nIngreso, 'R.FEFFRD'=>$ltFechaFormula, 'R.MEDFRD'=>$laDispensar['CODSHA']];
									$laClases = ['11','12','13','15','16'];
									$laDatosRia = $goDb
											->select('INT(R.CANFRD) AS DISPENSAR, INT(R.CNCFRD) AS CAN_PENDI')
											->select('INT(IFNULL( (SELECT SUM(RF.CADFRD)FROM RIAFARDF AS RF WHERE RF.NINFRD=R.NINFRD AND RF.CDNFRD=R.CDNFRD AND RF.MEDFRD=R.MEDFRD ),0)) AS DESPACHADO')
											->select('R.*')
											->from('RIAFARD AS R')
											->where($laCondiciones)
											->in('R.ESTFRD',$laClases)
											->get('array');
									//Valida si fue formulado para el paciente
									if(is_array($laDatosRia)==true){
										$laDispensar['DISPENSAR'] = $laDatosRia['DISPENSAR'];
										$laDispensar['CAN_PENDI'] = $laDatosRia['CAN_PENDI'];
										$laDispensar['DESPACHADO'] = $laDatosRia['DESPACHADO'];
										//Valida si hay pendientes por dispensar
										if($laDispensar['DISPENSAR']>0){
											//Valida que no se dispense mas de lo pendiente por dispensar
											if($laDispensar['DESPACHADO'] < $laDispensar['DISPENSAR']){
												//valida si hay intervencion farmaceutica sin observaciones o justificación
												if(!empty($laDatosRia[0]['MCDFRD']) AND empty($laDatosRia[0]['OB2FRD'])){
													$laDispensar["CODMEN"] = 1;
													$laDispensar["MENSAJE"]= "El medicamento leído tiene intervención farmaceutica sin justificación, por favor realice la justificación.";
												}else{
													$laDispensar['SALDO'] = $laDatosInv['SALDO'];
													//valida la fecha de vencimiento del medicamento
													if(($laDatosSim['FECVEN']) < $lcAnioMesDiaSis){
														$laDispensar["CODMEN"] = 2;
														$laDispensar["MENSAJE"]= "Este medicamento esta vencido.";
													}else if(($laDatosSim['FECVEN'])==$lcAnioMesDiaSis){
														$laDispensar["CODMEN"] = 3;
														$laDispensar["MENSAJE"]= "Este medicamento VENCE HOY.";
													}else if(($laDatosSim['FECVEN'])<=($ltFechaInicia)){
															$laDispensar["CODMEN"] = 4;
															$laDispensar["MENSAJE"]= "Este medicamento VENCE el ".$laDatosSim['FECVEN'];
													}else{
														$laDispensar["CODMEN"] = 5;
														$laDispensar["MENSAJE"]=  "Lectura Exitosa.  No olvide GUARDAR la dispensacion.";
													}
												}
											}else{
												$laDispensar["CODMEN"] = 6;
												$laDispensar["MENSAJE"] = "La cantidad dispensada + Total dispensado, no puede ser mayor que la cantidad a dispensar.";
											}
										}else{
											$laDispensar["CODMEN"] = 7;
											$laDispensar["MENSAJE"] ="Por favor verifique, no hay cantidad a dispensar";
										}
									}else{
										$laDispensar["CODMEN"] = 8;
										$laDispensar["MENSAJE"] = "Por favor verifique, el código QR No...".$tnCodigoQr.", del medicamento leido no fue formulado para el paciente.";
									}
							}else{
									$laDispensar["CODMEN"] = 9;
									$laDispensar["MENSAJE"] ="No existe saldo disponible del medicamento leido en la bodega.";
								}
						}
					}else{
						$laDispensar["CODMEN"] = 10;
						$laDispensar["MENSAJE"] = "Por favor verifique, el código QR No...".$tnCodigoQr.", leído no se encuentra en el inventario.";
					}
				}else if (($lnDatosRia['ESTING'])==3){
					$laDispensar["CODMEN"] = 11;
					$laDispensar["MENSAJE"] = "El ingreso no esta activo, ya fue cerrado.";
				}else if (($lnDatosRia['ESTING'])==4){
					$laDispensar["CODMEN"] = 12;
					$laDispensar["MENSAJE"]  ="El ingreso no esta activo, ya fue facturado.";
				}
				$this->aDispensar = $laDispensar;
				return $laDispensar;
			}
		}
	}
	public function faGuardarDispensacion($tcFechaFormula, $tnIngreso, $tnCodBodega, $tnCenCostos, $taCodigosQr, $tnConsecutivo, $tcUsuario){
		$lnIngreso=ltrim($tnIngreso, '0');
		$this->nIngreso = 0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso > 0){
				$this->nIngreso = $tnIngreso;
				$ltFechaFormula = str_replace(array('/', '-'), '', $tcFechaFormula);
				$ltFechaHoraSistema = new \DateTime($goDb->fechaHoraSistema());
				$lcAnioMesDiaSis = substr($ltFechaHoraSistema->format("Ymd"),0,8);
				$lcHora = $ltFechaHoraSistema->format("His");
				$lnDatosRiai = $goDb
							->select('ESTING')
							->from('RIAING')
							->where(['NIGING'=>$this->nIngreso])
							->get('array');
				if( ($lnDatosRiai['ESTING']) == 2){
					$laCondiciones = ['R.NINFRD'=>$this->nIngreso, 'R.FEFFRD'=>$ltFechaFormula];
					$laClases = ['11','12','13','16'];
					$laDatosRia = $goDb
								->select('RI.NIDING, INT(R.CANFRD) AS DISPENSAR, INT(R.CNCFRD) AS CAN_PENDI')
								->select('INT(IFNULL( (SELECT SUM(RF.CADFRD)FROM RIAFARDF AS RF WHERE RF.NINFRD=R.NINFRD AND RF.CDNFRD=R.CDNFRD AND RF.MEDFRD=R.MEDFRD ),0)) AS DESPACHADO')
								->select('R.*')
								->from('RIAFARD AS R')
								->leftJoin('RIAING AS RI', 'R.NINFRD=RI.NIGING', null)
								->where($laCondiciones)
								->in('R.ESTFRD',$laClases)
								->getAll('array');

					//Valida si hay medicamentos formulados para el paciente
					if(count($laDatosRia)>0){
						$lnIdTransaccion="";
						//Ejecuta procedimiento almacenado para traer el número de documento(Transacción del usuario)
						$lcStProc = 'INVGA058CP';
						$laParamIn = [  'tipoDoc'=>['CT', \PDO::PARAM_STR],
										'Usuario'=>['tcUsuario', \PDO::PARAM_STR]
									 ];
						$laParamOut = [ 'Retorno'=>[\PDO::PARAM_STR, 9]];
						$lnIdTransaccion = $goDb->storedProcedure($lcStProc, $laParamIn, $laParamOut);

						//Valida que traiga número de documento(Transacción del usuario).
						if($lnIdTransaccion['Retorno'] > 0){
							//Se arma el array que va a contener los datos para Insertar en SIMNCONC QUE ES LA CABECERA DEL DOCUMENTO (curInvCtrl)
							$lcStProc = 'INVGA090CP';
							$laParamInSimnconc =[
								'TDOCTR' =>['CT', \PDO::PARAM_STR],
								'NDOCTR' =>[$lnIdTransaccion['Retorno'], \PDO::PARAM_STR],
								'ND1CTR' =>[$lnIngreso, \PDO::PARAM_STR],
								'NI1CTR' =>[$laDatosRia[0]['NIDING'], \PDO::PARAM_STR],
								'FE1CTR' =>[$lcAnioMesDiaSis, \PDO::PARAM_STR],
								'ND2CTR' =>[$laDatosRia[0]['CDNFRD'], \PDO::PARAM_STR],
								'NI2CTR' =>['0', \PDO::PARAM_STR],
								'FE2CTR' =>[0, \PDO::PARAM_STR],
								'ND3CTR' =>[$laDatosRia[0]['CEVFRD'], \PDO::PARAM_STR],
								'NI3CTR' =>['FAR', \PDO::PARAM_STR],
								'FE3CTR' =>[0, \PDO::PARAM_STR],
								'BO1CTR' =>[$tnCodBodega, \PDO::PARAM_STR],
								'BO2CTR' =>['', \PDO::PARAM_STR],
								'CC1CTR' =>[$tnCenCostos, \PDO::PARAM_STR],
								'CC2CTR' =>['', \PDO::PARAM_STR],
								'TO1CTR' =>[0, \PDO::PARAM_STR],
								'TO2CTR' =>[0, \PDO::PARAM_STR],
								'TO3CTR' =>[0, \PDO::PARAM_STR],
								'IM1CTR' =>[0, \PDO::PARAM_STR],
								'IM2CTR' =>[0, \PDO::PARAM_STR],
								'IM3CTR' =>[0, \PDO::PARAM_STR],
								'IM4CTR' =>[0, \PDO::PARAM_STR],
								'IM5CTR' =>[0, \PDO::PARAM_STR],
								'IM6CTR' =>[0, \PDO::PARAM_STR],
								'MNDCTR' =>['', \PDO::PARAM_STR],
								'CPBCTR' =>['', \PDO::PARAM_STR],
								'LEGCTR' =>['', \PDO::PARAM_STR],
								'PAQCTR' =>['', \PDO::PARAM_STR],
								'STSCTR' =>['0', \PDO::PARAM_STR],
								'USCCTR' =>['', \PDO::PARAM_STR],
								'PGCCTR' =>['',	\PDO::PARAM_STR],
								'DTCCTR' =>[0, \PDO::PARAM_STR],
								'HOCCTR' =>[0, \PDO::PARAM_STR],
								'USRCTR' =>[$tcUsuario, \PDO::PARAM_STR],
								'PGMCTR' =>['DISPMEDWEB', \PDO::PARAM_STR],
								'DTECTR' =>[$lcAnioMesDiaSis, \PDO::PARAM_STR],
								'HORCTR' =>[$lcHora, \PDO::PARAM_STR],
								'Accion' =>["01", \PDO::PARAM_STR],
							];
							$laParamOutSimnconc = [ 'Retorno'=>[\PDO::PARAM_STR, 2]];
							$lnResultadoINVGA090CP = $goDb->storedProcedure($lcStProc, $laParamInSimnconc, $laParamOutSimnconc);

							//Valida si se crea la cabecera en SIMNCONC
							if($lnResultadoINVGA090CP['Retorno'] == 1){

								$lnSeqDet=0;
								foreach ($taCodigosQr as $lnCodSha=>$lnValor){
									$lnCodSha = trim(str_replace(array('_',''), '', $lnCodSha));
									$lnCantidad = count($lnValor);
									$lnSeqDet = $lnSeqDet+1;
									$laCondiciones = ['R.NINFRD'=>$this->nIngreso, 'R.FEFFRD'=>$ltFechaFormula, 'R.MEDFRD'=>$lnCodSha];
									$laClases = ['11','12','13','16'];
									$laDatosRia = $goDb
												->select('RI.NIDING, INT(R.CANFRD) AS DISPENSAR, INT(R.CNCFRD) AS CAN_PENDI')
												->select('INT(IFNULL( (SELECT SUM(RF.CADFRD) FROM RIAFARDF AS RF WHERE RF.NINFRD=R.NINFRD AND RF.CDNFRD=R.CDNFRD AND RF.MEDFRD=R.MEDFRD ),0)) AS DESPACHADO')
												->select('R.*, INV.REFDES, INV.DESDES, INV.UNCDES AS UNID_COMPRA, INV.UNDDES AS PRESEN_UNID, INV.UNIDES AS UNICONSUMO, INV.UNIDSI AS UNID_DOSIFI, INV.RF4DES AS INDICADOR')
												->from('RIAFARD AS R')
												->leftJoin('RIAING AS RI', 'R.NINFRD=RI.NIGING', null)
												->leftJoin('INVDES AS INV', 'R.MEDFRD = INV.REFDES', null)
												->where($laCondiciones)
												->in('R.ESTFRD', $laClases)
												->getAll('array');
									//Valida que el medicamento sea formulado para le paciente
									if(count($laDatosRia)>0){

										//Se arma el array que va a contener los datos del detalle de la dispensación despachada para insertar en la tabla RIAFARD
										$lcTabla = 'RIAFARD';
										$laCondiciones = ['NINFRD'=>$this->nIngreso, 'CDNFRD'=>$laDatosRia[0]['CDNFRD'], 'MEDFRD'=>$lnCodSha];
										$laDespachado = [
											'MCDFRD' => $laDatosRia[0]['MCDFRD'],
											'CANFRD' => trim($laDatosRia[0]['DISPENSAR']),
											'CADFRD' => trim($laDatosRia[0]['DESPACHADO']+$lnCantidad),
											'CNCFRD' => trim($laDatosRia[0]['DISPENSAR']) - (trim($laDatosRia[0]['DESPACHADO']+$lnCantidad)),
											'UCAFRD' => $laDatosRia[0]['UCAFRD'],
											'OB2FRD' => $laDatosRia[0]['OB2FRD'],
											'OB3FRD' => $laDatosRia[0]['OB3FRD'],
											'DCSFRD' => $laDatosRia[0]['DCSFRD'],
											'UMOFRD' => $tcUsuario,
											'PMOFRD' => 'DISPMEDWEB',
											'FMOFRD' => $lcAnioMesDiaSis,
											'HMOFRD' => $lcHora,
											'FEDFRD' => $lcAnioMesDiaSis,
											'HMDFRD' => $lcHora,
										];
										if($laDespachado['CNCFRD']>0){
											$laDespachado['ESTFRD'] = 16;
											$laDespachado['FUPFRD'] = $lcAnioMesDiaSis;
										}else{
											$laDespachado['ESTFRD'] = 15;
											$laDespachado['FUPFRD'] = 0;
										}
										$lbRtaActualizarRiafard = $goDb
														->tabla($lcTabla)
														->where($laCondiciones)
														->actualizar($laDespachado);
										if($lbRtaActualizarRiafard == 1){
											$laCondiciones = ['NINFRD'=>$this->nIngreso, 'CDNFRD'=>$laDatosRia[0]['CDNFRD'], 'CCOFRD'=>$laDatosRia[0]['CCOFRD'], 'CEVFRD'=>$tnConsecutivo, 'MEDFRD'=>$lnCodSha];
											$lnRegistro = $goDb
														->select('COUNT(CEVFRD)  AS REGISTROS')
														->from('RIAFARDF')
														->where($laCondiciones)
														->get('array');
											if($lnRegistro['REGISTROS']==0){
												$lcTabla = 'RIAFARDF';
												$laDespachadoRiafardf = [
													'NINFRD' => $this->nIngreso,
													'MEDFRD' => $lnCodSha,										// Codigo medicamento
													'CDNFRD' => $laDatosRia[0]['CDNFRD'],						// Consecutivo formula
													'CANFRD' => trim($laDatosRia[0]['CANFRD']),					// Cantidad formulada
													'CADFRD' => $lnCantidad,									// Despachado
													'CNCFRD' => trim($laDespachado['CNCFRD']),					// Cantidad pendiente
													'CCOFRD' => $laDatosRia[0]['CCOFRD'],						// Consecutivo consulta
													'CEVFRD' => $tnConsecutivo,									// Consecutivo de evolucion en RIAFARDF
													'SOLFRD' => $laDatosRia[0]['SOLFRD'],
													'ESTFRD' => $laDespachado['ESTFRD'],						// Estado de la formula
													'DOSFRD' => $laDatosRia[0]['DOSFRD'],
													'UDOFRD' => trim($laDatosRia[0]['UDOFRD']),
													'FRCFRD' => $laDatosRia[0]['FRCFRD'],
													'UFRFRD' => $laDatosRia[0]['UFRFRD'],
													'VIAFRD' => $laDatosRia[0]['VIAFRD'],
													'UCAFRD' => $laDatosRia[0]['UCAFRD'],
													'OB2FRD' => $laDatosRia[0]['OB2FRD'],
													'OBSFRD' => $laDatosRia[0]['OBSFRD'],
													'FEFFRD' => $ltFechaFormula,
													'HMFFRD' => $laDatosRia[0]['HMFFRD'],
													'FEDFRD' => $lcAnioMesDiaSis,
													'HMDFRD' => $lcHora,
													'MCDFRD' => $laDatosRia[0]['MCDFRD'],
													'DCSFRD' => $laDatosRia[0]['DCSFRD'],
													'FUPFRD' => $laDespachado['FUPFRD'],
													'DADFRD' => $laDatosRia[0]['DADFRD'],
													'AUTFRD' => $laDatosRia[0]['AUTFRD'],
													'JUSFRD' => $laDatosRia[0]['JUSFRD'],
													'OB3FRD' => $laDatosRia[0]['OB3FRD'],
													'USRFRD' => $tcUsuario,
													'PGMFRD' => 'DISPMEDWEB',
													'FECFRD' => $lcAnioMesDiaSis,
													'HORFRD' => $lcHora,
													'UMOFRD' => '',
													'PMOFRD' => '',
													'FMOFRD' => 0,
													'HMOFRD' => 0,
												];
												$lbRtaInsertar = $goDb
															->from($lcTabla)
															->insertar($laDespachadoRiafardf);
												if($lbRtaInsertar !=1){
													$laDispensar["CODMEN"]=985;
													$laDispensar["MENSAJE"]="No se insertó la dispensación en la tabla RIAFARDF";
												}else{
												}
											}else{
												$laCondiciones = ['NINFRD'=>$this->nIngreso, 'CDNFRD'=>$laDatosRia[0]['CDNFRD'], 'CCOFRD'=>$laDatosRia[0]['CCOFRD'], 'CEVFRD'=>$tnConsecutivo, 'MEDFRD'=>$lnCodSha];
												$lnRegistro = $goDb
															->select('CADFRD')
															->from('RIAFARDF')
															->where($laCondiciones)
															->get('array');
												$lcTabla = 'RIAFARDF';
												$laCondiciones = ['NINFRD'=>$this->nIngreso, 'CDNFRD'=>$laDatosRia[0]['CDNFRD'], 'CCOFRD'=>$laDatosRia[0]['CCOFRD'], 'CEVFRD'=>$tnConsecutivo, 'MEDFRD'=>$lnCodSha];
												$laDespachadoRiafardf = [
													'SOLFRD' => $laDatosRia[0]['SOLFRD'],
													'ESTFRD' => $laDespachado['ESTFRD'],
													'CANFRD' => trim($laDatosRia[0]['CANFRD']),
													'CADFRD' => $lnRegistro['CADFRD']+$lnCantidad,
													'CNCFRD' => trim($laDespachado['CNCFRD']),
													'DOSFRD' => $laDatosRia[0]['DOSFRD'],
													'UDOFRD' => trim($laDatosRia[0]['UDOFRD']),
													'FRCFRD' => $laDatosRia[0]['FRCFRD'],
													'UFRFRD' => $laDatosRia[0]['UFRFRD'],
													'VIAFRD' => $laDatosRia[0]['VIAFRD'],
													'UCAFRD' => $laDatosRia[0]['UCAFRD'],
													'OB2FRD' => $laDatosRia[0]['OB2FRD'],
													'OBSFRD' => $laDatosRia[0]['OBSFRD'],
													'FEFFRD' => $ltFechaFormula,
													'HMFFRD' => $laDatosRia[0]['HMFFRD'],
													'FEDFRD' => $lcAnioMesDiaSis,
													'HMDFRD' => $lcHora,
													'MCDFRD' => $laDatosRia[0]['MCDFRD'],
													'DCSFRD' => $laDatosRia[0]['DCSFRD'],
													'FUPFRD' => $laDespachado['FUPFRD'],
													'DADFRD' => $laDatosRia[0]['DADFRD'],
													'AUTFRD' => $laDatosRia[0]['AUTFRD'],
													'JUSFRD' => $laDatosRia[0]['JUSFRD'],
													'OB3FRD' => $laDatosRia[0]['OB3FRD'],
													'UMOFRD' => $tcUsuario,
													'PMOFRD' => 'DISPMEDWEB',
													'FMOFRD' => $lcAnioMesDiaSis,
													'HMOFRD' => $lcHora,
												];
												$lbRtaActualizarRiafardf = $goDb
																->tabla($lcTabla)
																->where($laCondiciones)
																->actualizar($laDespachadoRiafardf);
												if($lbRtaActualizarRiafardf!=1){
													$laDispensar["CODMEN"]=984;
													$laDispensar["MENSAJE"]="No se Actualizó la dispensación en la tabla RIAFARDF";
												}else{
												}
											}
											$laCondiciones = ['NINFRD'=>$this->nIngreso, 'FEFFRD'=>$ltFechaFormula];
											$laClases = [11, 12, 13, 16];
											$lnEstForm = $goDb
														->select('IFNULL(SUM(INT(CNCFRD)),0) AS CAN_PENDI, COUNT(INT(ESTFRD)) AS ESTADOS')
														->from('RIAFARD')
														->where($laCondiciones)
														->get('array');
											$lnCanPen = $lnEstForm['CAN_PENDI'];
											$lnEstados = $lnEstForm['ESTADOS'];
											//if(($lnCanPen > 0) || ($lnEstados > 0) ){
									 		if(($lnCanPen > 0) ){
												$laDespachado['ESTFRD'] = 16;
											}
											$lcTabla = 'RIAFARM';
											$laCondiciones =['INGFAR'=>$this->nIngreso, 'CDNFAR'=>$laDatosRia[0]['CDNFRD']];
											$laDespachadoRiafarm = [
												'ESTFAR' => $laDespachado['ESTFRD'],
												'FEDFAR' => $lcAnioMesDiaSis,
												'HMDFAR' => $lcHora,
												'UMOFAR' => $tcUsuario,
												'PMOFAR' => 'DISPMEDWEB',
												'FMOFAR' => $lcAnioMesDiaSis,
												'HMOFAR' => $lcHora,
											];
											$lbRtaActualizarRiafarm = $goDb
																->tabla($lcTabla)
																->where($laCondiciones)
																->where('ESTFAR','<>',-1)
																->actualizar($laDespachadoRiafarm);
											if($lbRtaActualizarRiafarm == 1){
												$lcStProc = 'INVGA091CP';
												$laParamInSimncond =[
													'TIDDET' =>['CT', \PDO::PARAM_STR],
													'NDODET' =>[$lnIdTransaccion['Retorno'], \PDO::PARAM_STR],
													'SEQDET' =>[$lnSeqDet, \PDO::PARAM_STR],
													'PR1DET' =>[$lnCodSha, \PDO::PARAM_STR],
													'PR2DET' =>[$lnCodSha, \PDO::PARAM_STR],
													'CC1DET' =>[$tnCenCostos, \PDO::PARAM_STR],
													'CC2DET' =>['', \PDO::PARAM_STR],
													'DO1DET' =>[$this->nIngreso, \PDO::PARAM_STR],
													'DO2DET' =>['  '.$tnCenCostos, \PDO::PARAM_STR],
													'DO3DET' =>[$laDatosRia[0]['CDNFRD'], \PDO::PARAM_STR],
													'BO1DET' =>[$tnCodBodega, \PDO::PARAM_STR],
													'BO2DET' =>[$tnCodBodega, \PDO::PARAM_STR],
													'FE1DET' =>[$lcAnioMesDiaSis, \PDO::PARAM_STR],
													'FE2DET' =>[$lcAnioMesDiaSis, \PDO::PARAM_STR],
													'CA1DET' =>[$lnCantidad, \PDO::PARAM_STR],
													'CA2DET' =>['0', \PDO::PARAM_STR],
													'UN1DET' =>[$laDatosRia[0]['UNICONSUMO'], \PDO::PARAM_STR],
													'UN2DET' =>[$laDatosRia[0]['UNICONSUMO'], \PDO::PARAM_STR],
													'VLRDET' =>['0', \PDO::PARAM_STR],
													'VLRDE2' =>['0', \PDO::PARAM_STR],
													'IM1DET' =>['0', \PDO::PARAM_STR],
													'IM2DET' =>['0', \PDO::PARAM_STR],
													'IM3DET' =>['0', \PDO::PARAM_STR],
													'IM4DET' =>['0', \PDO::PARAM_STR],
													'IM5DET' =>['0', \PDO::PARAM_STR],
													'IM6DET' =>['0', \PDO::PARAM_STR],
													'TASDET' =>['0', \PDO::PARAM_STR],
													'COADET' =>['0', \PDO::PARAM_STR],
													'CODDET' =>['0', \PDO::PARAM_STR],
													'SDADET' =>['0', \PDO::PARAM_STR],
													'SDDDET' =>['0', \PDO::PARAM_STR],
													'NIDDET' =>['', \PDO::PARAM_STR],
													'COPDET' =>['', \PDO::PARAM_STR],
													'ESTCON' =>['', \PDO::PARAM_STR],
													'CODBAR' =>['', \PDO::PARAM_STR],
													'STSDET' =>['0', \PDO::PARAM_STR],
													'USRDET' =>[$tcUsuario, \PDO::PARAM_STR],
													'PGMDET' =>['DISPMEDWEB', \PDO::PARAM_STR],		//'INVGA091',
													'DTEDET' =>[$lcAnioMesDiaSis, \PDO::PARAM_STR],
													'HORDET' =>[$lcHora, \PDO::PARAM_STR],
													'Accion' =>["01", \PDO::PARAM_STR],
													'ActCantidad' =>["N", \PDO::PARAM_STR]
												];
												$laParamOutSimncond = ['Retorno'=>[\PDO::PARAM_STR, 2]];
												$lnResultadoINVGA091CP = $goDb->storedProcedure($lcStProc, $laParamInSimncond, $laParamOutSimncond);
												if($lnResultadoINVGA091CP['Retorno'] == 1){
													$lnSeqSimn=0;
													foreach($lnValor as $lnCodBarBun){
														$lnCodbar=substr($lnCodBarBun, 0, 20);
														$lnCodBun = substr($lnCodBarBun, 20, 25);
														$lnCantidadCodBarBun = strlen($lnCodBarBun)>0 ? 1 : 0;
														$lnSeqSimn=$lnSeqSimn+1;
														$laCondicion = ['CODBAR'=>$lnCodbar];
														$laDatosSim = $goDb
																	->select('CODSHA, CODBAR, CODCUM, REGSAN, TIPINV, FECVEN')
																	->from('SIMNETI')
																	->where($laCondicion)
																	->get('array');
														$lcStProc = 'INVGA052CP';
														$laParamInSimnimov  =[
															'TIPDOC' => ['CT', \PDO::PARAM_STR],
															'NUMDOC' => [$lnIdTransaccion['Retorno'], \PDO::PARAM_STR],
															'SECUEN' => [$lnSeqSimn, \PDO::PARAM_STR],
															'CODBAR' => [$lnCodbar, \PDO::PARAM_STR],
															'CODBUN' => [$lnCodBun, \PDO::PARAM_STR],
															'CODSHA' => [$lnCodSha, \PDO::PARAM_STR],
															'CODCUM' => [trim($laDatosSim['CODCUM']), \PDO::PARAM_STR],
															'CANTID' => [$lnCantidadCodBarBun, \PDO::PARAM_STR],
															'INGRES' => [$this->nIngreso, \PDO::PARAM_STR],
															'BO1DET' => [$tnCodBodega, \PDO::PARAM_STR],
															'BO2DET' => [$tnCodBodega, \PDO::PARAM_STR],
															'DO1DET' => [$this->nIngreso, \PDO::PARAM_STR],
															'DO2DET' => [$tnCenCostos, \PDO::PARAM_STR],
															'DO3DET' => [str_pad($laDatosRia[0]['CDNFRD'], 6,"0", STR_PAD_LEFT), \PDO::PARAM_STR], // Consecutivo formula RIAFARD.CDNFRD, //Carro//Documento 3 CHAR
															'NI1DET' => ['', \PDO::PARAM_STR],
															'NI2DET' => ['', \PDO::PARAM_STR],
															'NI3DET' => ['', \PDO::PARAM_STR],
															'CC1DET' => ['', \PDO::PARAM_STR],
															'CC2DET' => ['',	\PDO::PARAM_STR],
															'REGSAN' => [$laDatosSim['REGSAN'], \PDO::PARAM_STR],
															'IDNPOS' => [$laDatosRia[0]['INDICADOR'], \PDO::PARAM_STR],
															'ESTCON' => ['', \PDO::PARAM_STR],
															'ESTREG' => ['C', \PDO::PARAM_STR],
															'USRCRE' => [$tcUsuario, \PDO::PARAM_STR],
															'PGMCRE' => ['DISPMEDWEB', \PDO::PARAM_STR],
															'USRMOD' => ['', \PDO::PARAM_STR],
															'PGMMOD' => ['',	\PDO::PARAM_STR],
															'LCACCION' => [ '01', \PDO::PARAM_STR],
															'CONJUS'=> [ 0, \PDO::PARAM_STR]
														];
														$laParamOutSimnimov = ['Retorno'=>[\PDO::PARAM_STR, 2]];
														$lnResultadoINVGA052CP = $goDb->storedProcedure($lcStProc, $laParamInSimnimov, $laParamOutSimnimov);
														if($lnResultadoINVGA052CP['Retorno'] == 1){
															$laDispensar["CODMEN"]=1;
															$laDispensar["MENSAJE"]="Dispensación exitosa, se almacenó correctamente la información.";
														}else{
															$laDispensar["CODMEN"]=985;
															$laDispensar["MENSAJE"]="<p>Error al guardar la dispensación en la tabla SIMNIMOV.</p>";
														}
													}
												}else{
													$laDispensar["CODMEN"]=990;
													$laDispensar["MENSAJE"]="Error al guardar la dispensación en la tabla SIMNCOND.";
												}
											}else{
												$laDispensar["CODMEN"]=983;
												$laDispensar["MENSAJE"]="No se Actualizó la dispensación en la tabla RIAFARM";
											}
										}else{
											$laDispensar["CODMEN"]=986;
											$laDispensar["MENSAJE"]="No se actualizó la tabla RIAFARD";
										}
									}else{
										$laDispensar["CODMEN"]=987;
										$laDispensar["MENSAJE"]="No se encontro formulado para el paciente, el medicamento con codigo shaio No.".$lnCodSha;
									}
								}
							}else{
								$laDispensar["CODMEN"]=991;
								$laDispensar["MENSAJE"]="Se presentó un error y no se creó la cabecera en la tabla SIMNCONC, para la dispensación.";
							}
						}else{
							$laDispensar["CODMEN"]=988;
							$laDispensar["MENSAJE"]="Disculpe, existió un problema al generar el número de documento y no se obtuvo el resultado deseado";
						}
					}else{
						$laDispensar["CODMEN"]=989;
						$laDispensar["MENSAJE"]="Por favor verifique, el ingreso:No...".$tnIngreso.", no tiene medicamentos formulados, para la fecha seleccionada.";
					}
				}else if (($lnDatosRiai['ESTING'])==3){
					$laDispensar["CODMEN"] = 11;
					$laDispensar["MENSAJE"] = "El ingreso no esta activo, ya fue cerrado.";
				}else if (($lnDatosRiai['ESTING'])==4){
					$laDispensar["CODMEN"] = 12;
					$laDispensar["MENSAJE"]  ="El ingreso no esta activo, ya fue facturado.";
				}
				return $laDispensar;
			}
		}

	}
	public function administrarMedicamentosPaciente($tnIngreso, $tnCodigoQr, $tnCodigoQrDiluyente, $tcUsuario){
		$this->nIngreso=0;
		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$this->nIngreso = $tnIngreso;
				$ltFechaHoraSistema = new \DateTime($goDb->fechaHoraSistema());
				$lcHorasAntes = '3';
				$lcHorasDespues = '1';
				$ltFechaHoraIni = new \DateTime($ltFechaHoraSistema->format("YmdHis"));
				$ltFechaHoraIni->sub(new \DateInterval('PT'.$lcHorasAntes.'H'));
				$lcFechaHoraIni = $ltFechaHoraIni->format("YmdHis");
				$ltFechaHoraFin = new \DateTime($ltFechaHoraSistema->format("YmdHis"));
				$ltFechaHoraFin->add(new \DateInterval('PT'.$lcHorasDespues.'H'));
				$lcFechaHoraFin = $ltFechaHoraFin->format("YmdHis");
				$lcFecha = $ltFechaHoraSistema->format("Ymd");
				$lcHora =  $ltFechaHoraSistema->format("His");
				$lnCodbar = substr($tnCodigoQr, 0, 20);
				$lnCodBun = substr($tnCodigoQr, 20, 25);

				// Consulta para validar que el Código QR leido corresponda y este dispensado para el paciente
				$laCondiciones = ['INGRES'=>$this->nIngreso, 'CODBAR'=>$lnCodbar, 'CODBUN'=>$lnCodBun, 'ESTREG'=>'C'];
				$laDatosSim = $goDb
						->select('ESTREG, USRMOD, PGMMOD, FECMOD, HORMOD, NUMDOC, SECUEN, CODBAR, CODBUN, CODSHA, TIPDOC')
						->from('SIMNIMOV')
						->where($laCondiciones)
						->in('TIPDOC', ['CE', 'CT'])
						->getAll('array');
				if(count($laDatosSim)>0){

					// Consulta para validar que el medicamento se ecuentre programado y que la fecha y hora  esten en el rango para administrar.
					$laCondiciones = ['MEDIC.INGADM'=>$this->nIngreso, 'MEDIC.MEDADM'=>trim($laDatosSim[0]['CODSHA']), 'MEDIC.ESTADM'=>4];
 					$laDatosEna = $goDb
							->select('MEDIC.FEPADM AS FECPRO, MEDIC.HDPADM AS HORPRO, MEDIC.SCAADM AS SECCION_ANTE')
							->select('MEDIC.NCAADM AS CAMA_ANTE, MEDIC.CTUADM AS CON_TURNO, MEDIC.CEVADM AS CON_EVOLU')
							->select('MEDIC.VIAADM AS VIA, MEDIC.CCOADM AS CON_ADMIN, MEDIC.NDOADM AS No_DOSIS')
							->select('MEDIC.OP8ADM AS NUM_DOCU, MEDIC.SECAMD AS SECU_DOCU, MEDIC.DDOADM AS D_DOSIS')
							->select('FAC.SECHAB AS SECCION_ACTU, FAC.NUMHAB AS CAMA_ACTU, INVE.DESDES AS MDDADM')
							->select('MEDIC.DOSADM AS DOSIS, MAE2.DE1TMA AS DESC_DOSIS')
							->from('ENADMMD AS MEDIC')
							->leftJoin('FACHAB AS FAC','MEDIC.INGADM = FAC.INGHAB', null)
							->leftJoin('INVDES  AS INVE', 'MEDIC.MEDADM = INVE.REFDES', null)
							->leftJoin("TABMAE AS MAE2", "INTEGER(MEDIC.DDOADM) = INTEGER(MAE2.CL1TMA) AND MAE2.TIPTMA = 'MEDDOS' ", null)
							->where($laCondiciones)
							->between('MEDIC.FEPADM *1000000+MEDIC.HDPADM', $lcFechaHoraIni, $lcFechaHoraFin)
							->orderBy('MEDIC.FEPADM, MEDIC.HDPADM')
							->getAll('array');
					if(count($laDatosEna)>0){
						$lcVia = trim($laDatosEna[0]['VIA']);
						$lcCodNoDilu = '000000000';
						if($lcVia === '2' AND empty($tnCodigoQrDiluyente)){

							// Consulta para cargar los diluyentes de cada medicamento
							$laCondiciones = ['REFMED'=>trim($laDatosSim[0]['CODSHA']),'LIQMED'=>1];
							$laDatosDilu = $goDb
										->select('DILU.ELEMED AS CODIGODILU, INVE.DESDES AS DILUYENTE,  DILU.CLQMED AS CANTIDAD')
										->from('PARAMEDI AS DILU')
										->leftJoin('INVDES  AS INVE', 'DILU.ELEMED=INVE.REFDES')
										->where($laCondiciones)
										->where('CLQMED', '>', 0)
										->orderBy('DILUYENTE')
										->getAll('array');
							$laNoDilu = [ "0" => [
								'CODIGODILU' => $lcCodNoDilu,
								'DILUYENTE' => 'SIN DILUIR',
								'CANTIDAD' => '00.00',
							]];
							$lnResultado = array_merge($laDatosDilu, $laNoDilu);
						}else{
							if($lcVia === '2' && $tnCodigoQrDiluyente !== $lcCodNoDilu){
								$laCondiciones = ["REFMED"=>trim($laDatosSim[0]['CODSHA']), "DILU.ELEMED"=>$tnCodigoQrDiluyente, "LIQMED"=>1];
								$laDatosDiluIns = $goDb
												->select('DILU.ELEMED AS CODIGODILU, INVE.DESDES AS DILUYENTE,  DILU.CLQMED AS CANTIDAD')
												->from('PARAMEDI AS DILU')
												->leftJoin('INVDES  AS INVE', 'DILU.ELEMED=INVE.REFDES')
												->where($laCondiciones)
												->where('CLQMED', '>', 0)
												->getAll('array');

								$lnMaxCONBAQ = $goDb
										->select('MAX(CONBAQ) AS CONBAQ')
										->from('ENBALQ')
										->where('INGBAQ','=',$this->nIngreso)
										->get('array');
								$laCondiciones = ['INGBAQ'=>$this->nIngreso, 'CONBAQ'=>$lnMaxCONBAQ['CONBAQ']];
								$laDatosEnbalq = $goDb
										->select('CONBAQ, MAX(CNLBAQ) AS CNLBAQ')
										->from('ENBALQ')
										->where($laCondiciones)
										->groupBy('CONBAQ')
										->getAll('array');

								if (count($laDatosEnbalq) == 0) {
									$laDatosEnbalq[0]['CONBAQ']=1;
									$laDatosEnbalq[0]['CNLBAQ']=0;
								}
								$laDatosActualizaAdmiDilu = [
									'OP1ADM' => 1,
									'OP3ADM' => $laDatosEnbalq[0]['CONBAQ'],
									'OP4ADM' => $laDatosEnbalq[0]['CNLBAQ']+1,
								];

								// Se Crea el arreglo asociativo con los datos para insertar en la tabla ENBALQ.
								$lcTabla = 'ENBALQ';
								$laDatosInsertarDilu = [
									'INGBAQ' => $this->nIngreso,
									'CONBAQ' => $laDatosEnbalq[0]['CONBAQ'],
									'CNLBAQ' => $laDatosEnbalq[0]['CNLBAQ']+1,
									'LSEBAQ' => 'A',
									'LIQBAQ' => substr(trim($laDatosDiluIns[0]['DILUYENTE']),0,40),
									'VIABAQ' => 'INTRAVENOSA',
									'CANBAQ' => $laDatosDiluIns[0]['CANTIDAD'],
									'OBSBAQ' => substr(trim($laDatosEna[0]['MDDADM']).'('.trim($laDatosEna[0]['DOSIS']).trim($laDatosEna[0]['DESC_DOSIS']).')'.'-'.trim($laDatosDiluIns[0]['DILUYENTE']),0,220),
									'FDIBAQ' => $laDatosEna[0]['FECPRO'],
									'HDIBAQ' => $laDatosEna[0]['HORPRO'],
									'USRBAQ' => $tcUsuario,
									'PGMBAQ' => 'ADMIMEDWEB',
									'FECBAQ' => $lcFecha,
									'HORBAQ' => $lcHora,
								];

								//se insertan los datos en la tabla ENBALQ
								$lnResultado = $goDb->from($lcTabla)->insertar($laDatosInsertarDilu);
							}else {
								$laDatosActualizaAdmiDilu = [];
							}

							//Crea arreglo asociativo con los datos para modificar la tabla ENADMMD.
							$lcTabla= 'ENADMMD';
							$laDatosActualizaAdmiMedi = array_merge($laDatosActualizaAdmiDilu,
								[
									'SCAADM' => $laDatosEna[0]['SECCION_ACTU'],
									'NCAADM' => $laDatosEna[0]['CAMA_ACTU'],
									'FEAADM' => $lcFecha,
									'HDAADM' => $lcHora,
									'PRGADM' => 'ADMIMEDWEB',
									'ESTADM' => 2,
									'UD1ADM' => $tcUsuario,
									'OP7ADM' => $laDatosSim[0]['CODBUN'],
									'OP8ADM' => $laDatosSim[0]['NUMDOC'],
									'CBAADM' => $laDatosSim[0]['CODBAR'],
									'SECAMD' => $laDatosSim[0]['SECUEN'],
								]) ;
							//$lcWhere = "INGADM = '$tnIngreso' "
							$lcWhere = "INGADM = $this->nIngreso "
									."AND MEDADM = '" .trim($laDatosSim[0]['CODSHA']) . "' "
									."AND ESTADM='4' "
									."AND CTUADM= '" .$laDatosEna[0]['CON_TURNO'] . "' "
									."AND CEVADM= '" .$laDatosEna[0]['CON_EVOLU'] . "' "
									."AND CCOADM= '" .$laDatosEna[0]['CON_ADMIN'] . "' "
									."AND NDOADM= '" .$laDatosEna[0]['NO_DOSIS'] . "' ";

							//se actualizan los datos en la tabla ENADMMD
							$lnResultado = $goDb->from($lcTabla)
							 ->where($lcWhere)
							 ->actualizar($laDatosActualizaAdmiMedi);

							//Crea arreglo asociativo con los datos para modificar la tabla SIMNIMOV.
							$lcTabla = 'SIMNIMOV';
							$laDatosActualizaAdmiMediSim = [
								'ESTREG' => 'E',
								'USRMOD' => $tcUsuario,
								'PGMMOD' => 'ADMIMEDWEB',
								'FECMOD' => $lcFecha,
								'HORMOD' => $lcHora,
							];
							$lcWhere = "INGRES= $this->nIngreso "
									."AND NUMDOC = '" .$laDatosSim[0]['NUMDOC'] ."' "
									."AND SECUEN = '" .$laDatosSim[0]['SECUEN'] ."' "
									."AND CODBAR = '$lnCodbar' "
									."AND CODBUN = '$lnCodBun' "
									."AND CODSHA = '" . trim($laDatosSim[0]['CODSHA']) . "' "
									."AND TIPDOC = '" . trim($laDatosSim[0]['TIPDOC']) . "' "
									."AND ESTREG='C' ";

							//se actualizan los datos en la tabla SIMNIMOV
							$lnResultado = $goDb->from($lcTabla)
							 ->where($lcWhere)
							 ->actualizar($laDatosActualizaAdmiMediSim);
							$tnCodigoQrDiluyente='';
							$lnResultado = 1;  //El medicamento ha sido administrado correctamente
						}
					}else{
						$lnResultado = 0; //no se encuentra programado para administrar, Por favor, verifique la hora y el medicamento que va a administrar
					}
				}else{
					$lnResultado = -1; // no ha sido dispensado para el paciente
				}
				return $lnResultado;
			}
		}
	}

	public function consultaListaMedicamentos($taDescripcion='', $tcCodigo='', $tlIncluirTodosLosEstados=false)
	{
		$laDatosMedicamentos = [];
		$lcDescripcionMedicamento=$taDescripcion;

		if ($taDescripcion!='' || $tcCodigo!=''){
			if(is_array($taDescripcion)==false){
				$taDescripcion = array(trim(strval($taDescripcion)));
			}
			$tcCodigo = trim(strval($tcCodigo));
			$tcCodigo = mb_strtoupper(!empty($tcCodigo)?'%'.$tcCodigo.'%':'');

			if($taDescripcion!=''){
				if(count($taDescripcion)>0){
					$lcWhereAux = '';
					foreach($taDescripcion as $lcNombre){
						if(!empty($lcNombre) && $lcNombre!=='*'){
							$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
							$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("( (TRANSLATE(UPPER(DESDES),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')) OR (TRANSLATE(UPPER(REFDES),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')))", $lcNombre, $lcNombre);
						}
					}
					$lcWhere= (!empty($lcWhereAux)? $lcWhereAux :'');
				}
			}
			if($lcDescripcionMedicamento!=''){
				$lcWhere.= (!empty($tcCodigo)?sprintf(" AND (REFDES LIKE '%s')", $tcCodigo):'');
			}else{
				$lcWhere.= (!empty($tcCodigo)?sprintf("(REFDES LIKE '%s')", $tcCodigo):'');
			}

			$lcWhere.= ($tlIncluirTodosLosEstados==true ?'' : " AND STSDES<>'1'");
			$lcWhere.= (" AND (TINDES='500' OR RF2DES='NUCLEA')");
			$lcOrden='DESDES';

			$laMedicamentos = $this->oDb
				->select('TRIM(DESDES) DESCRIPCION, TRIM(REFDES) CODIGO, TRIM(RF4DES) POSNOPOS')
				->from('INVDES')
				->where($lcWhere)
				->orderBy($lcOrden)
				->getAll('array');

			if (is_array($laMedicamentos) && count($laMedicamentos)>0){
				foreach ($laMedicamentos as $laDatos){
					$laControlado = $this->oDb->select('trim(CL03DES) CONTROLADO, trim(CL19DES) UNIRS, trim(CL21DES) FORMULAPORVIA')->from('INVATTR')->where('REFDES', '=', $laDatos['CODIGO'])->get('array');
					$laParametros = $this->oDb->select('trim(CONCE) CONCENTRACION, trim(UNIDA) UNIDADES, trim(PRESE) PRESENTACION')
					->from('INVMEDA')->where('CODIGO', '=', $laDatos['CODIGO'])->get('array');

					$laDatosMedicamentos[]=[
						'DESCRIPCION'=>$laDatos['DESCRIPCION'],
						'CODIGO'=>$laDatos['CODIGO'],
						'POSNOPOS'=>$laDatos['POSNOPOS'],
						'CONTROLADO'=>isset($laControlado['CONTROLADO']) ? trim($laControlado['CONTROLADO']) : '',
						'CONCENTRACION'=>isset($laParametros['CONCENTRACION']) ? trim($laParametros['CONCENTRACION']) : '',
						'UNIDAD'=>isset($laParametros['UNIDADES']) ? trim($laParametros['UNIDADES']) : '',
						'PRESENTACION'=>isset($laParametros['PRESENTACION']) ? trim($laParametros['PRESENTACION']) : '',
						'UNIRS'=>isset($laControlado['UNIRS']) ? trim($laControlado['UNIRS']) : '',
						'FORMULAPORVIA'=>isset($laControlado['FORMULAPORVIA']) ? trim($laControlado['FORMULAPORVIA']) : '',
					];
				}
			}
		}
		return $laDatosMedicamentos;
	}

	public function consultarParametrosIniciales()
	{
		$aParametros = [];

		if(isset($this->oDb)){
			$lnFrecuenciaMinima=$lnFrecuenciaMaxima=$lnCantidadMinControlados=$lnCantidadMaxControlados=0;
			$lnCantidadMinInmediato=$lnCantidadMaxInmediato=$lnConfirmarnoformulados=0;
			$lcViasInmediato=$lcSeccionesInmediato=$laEstadosnoformular=$laViasInmediato=$laSeccionesInmediato='';
			$lcEstadoFormular=$lcTextoUnirs=$lcRutaArchivoUnirs='';
			$lnDiasParaAntibioticoMaximo=30;
			$llActivarAntibiotico=false;

			$laParametros = $this->oDb
				->select('trim(CL1TMA) CLASIFICACION1, trim(CL2TMA) CLASIFICACION2, trim(OP1TMA) OP1TMA, trim(OP2TMA) OP2TMA, OP3TMA OP3TMA')
				->select('trim(DE1TMA) DESCRIPCION1, trim(DE2TMA) DESCRIPCION2, TRIM(OP5TMA) OPCIONAL5')
				->from('TABMAE')
				->where('TIPTMA', '=', 'FORMEDIC')
				->in('CL1TMA', ['HRFORPRO','ANTIBI', 'MEDCTRL','INMEDIAT', 'CESTFORM', 'CONFNOFR', 'DIAMAXFR', 'TXTUNIRS', 'RUTUNIRS'])
				->orderBy('CL1TMA')
				->getAll('array');

			foreach($laParametros as $laDatos){
				$lcClasificacion1=isset($laDatos['CLASIFICACION1']) ? trim($laDatos['CLASIFICACION1']) : '';
				$lcClasificacion2=isset($laDatos['CLASIFICACION2']) ? trim($laDatos['CLASIFICACION2']) : '';
				$lcDescripcion1=isset($laDatos['DESCRIPCION1']) ? trim($laDatos['DESCRIPCION1']) : '';
				$lcDescripcion2=isset($laDatos['DESCRIPCION2']) ? trim($laDatos['DESCRIPCION2']) : '';
				$lcOpcional1=isset($laDatos['OP1TMA']) ? trim($laDatos['OP1TMA']) : '';
				$lcOpcional2=isset($laDatos['OP2TMA']) ? trim($laDatos['OP2TMA']) : '';
				$lnOpcional3=isset($laDatos['OP3TMA']) ? intval($laDatos['OP3TMA']) : 0;
				$lcOpcional5=isset($laDatos['OPCIONAL5']) ? trim($laDatos['OPCIONAL5']) : '';

				if ($lcClasificacion1=='HRFORPRO'){
					$lnFrecuenciaMinima=$lcOpcional2!='' ? intval(explode('~', $lcOpcional2)['0']) : 0;
					$lnFrecuenciaMaxima=$lcOpcional2!='' ? intval(explode('~', $lcOpcional2)['1']) : 0;
				}

				if ($lcClasificacion1=='ANTIBI' && $lcClasificacion2=='MAXDIAS'){
					$llActivarAntibiotico=$lcOpcional1=='1';
					$lnDiasParaAntibioticoMaximo=$lnOpcional3;
				}

				if ($lcClasificacion1=='MEDCTRL' && $lcClasificacion2=='RANGCNT'){
					$lnCantidadMinControlados=$lcDescripcion2!='' ? intval(explode('~', $lcDescripcion2)['0']) : 0;
					$lnCantidadMaxControlados=$lcDescripcion2!='' ? intval(explode('~', $lcDescripcion2)['1']) : 0;
				}

				if ($lcClasificacion1=='INMEDIAT'){
					$lnCantidadMinInmediato=$lcOpcional2!='' ? intval(explode('~', $lcOpcional2)['0']) : 0;
					$lnCantidadMaxInmediato=$lcOpcional2!='' ? intval(explode('~', $lcOpcional2)['1']) : 0;
					$lcViasInmediato=$lcDescripcion1!='' ? explode('~', $lcDescripcion1)['0'] : '';
					$lcViasInmediato=trim(str_replace('\'', '', $lcViasInmediato));
					$laViasInmediato=explode(',', $lcViasInmediato);
					$lcSeccionesInmediato=$lcDescripcion1!='' ? explode('~', $lcDescripcion1)['1'] : '';
					$lcSeccionesInmediato=trim(str_replace('\'', '', $lcSeccionesInmediato));
					$laSeccionesInmediato=explode(',', $lcSeccionesInmediato);
				}

				if ($lcClasificacion1=='CESTFORM'){
					$laEstadosnoformular=$lcDescripcion2!='' ? $lcDescripcion2 :'';
					$laEstadosnoformular=explode(',', $laEstadosnoformular);
				}

				if ($lcClasificacion1=='CONFNOFR'){
					$lnConfirmarnoformulados=$lnOpcional3;
				}

				if ($lcClasificacion1=='DIAMAXFR'){
					$lnDiasMaximoFrecuencia=$lcDescripcion2!='' ? intval(trim($lcDescripcion2)) :24;
				}
				
				if ($lcClasificacion1=='TXTUNIRS'){
					$lcTextoUnirs=$lcDescripcion2!=''?trim($lcDescripcion2):'';
					$lcTextoUnirs=$lcTextoUnirs.'~'.($lcOpcional5!=''?trim($lcOpcional5):'');
				}
				
				if ($lcClasificacion1=='RUTUNIRS'){
					$lcRutaArchivoUnirs=$lcDescripcion2!=''?trim($lcDescripcion2):'';
				}
			}
			$loTabmaeSolForm = $this->oDb->obtenerTabmae('OP1TMA', 'USOANTIB', ['CL1TMA'=>'CONTROL', 'ESTTMA'=>'']);
			$lcSolicitaFormato = trim(AplicacionFunciones::getValue($loTabmaeSolForm, 'OP1TMA', ''));
			$lcEstadoFormular=trim($this->oDb->obtenerTabmae1('CL1TMA', 'ESTFORM', "OP1TMA='F'", null, ''));

			$aParametros=[
				'frecuenciaminima'=>$lnFrecuenciaMinima,
				'frecuenciamaxima'=>$lnFrecuenciaMaxima,
				'activarrangoantibiotico'=>$llActivarAntibiotico,
				'diasparaantibmax'=>$lnDiasParaAntibioticoMaximo,
				'cantidadmincontrolados'=>$lnCantidadMinControlados,
				'cantidadmaxcontrolados'=>$lnCantidadMaxControlados,
				'solicitaformato'=>$lcSolicitaFormato,
				'cantidadmininmediato'=>$lnCantidadMinInmediato,
				'cantidadmaxinmediato'=>$lnCantidadMaxInmediato,
				'viasjustificarinmediato'=>$laViasInmediato,
				'seccionesexcluidasinmediato'=>$laSeccionesInmediato,
				'estadosnoformular'=>$laEstadosnoformular,
				'estadosformular'=>$lcEstadoFormular,
				'confirmarnoformulados'=>$lnConfirmarnoformulados,
				'diasmaximosfrecuencia'=>$lnDiasMaximoFrecuencia,
				'textounirs'=>$lcTextoUnirs,
				'rutaarchivoounirs'=>$lcRutaArchivoUnirs,
			];
		}
		unset($laParametros);
		return $aParametros;
	}

	public function consultarAntibioticos()
	{
		$laParametros = [];

		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(CL1TMA) CLASIFICACION1, trim(CL2TMA) CODIGO, trim(CL3TMA) CLASIFICACION3')
				->select('trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'USOANTIB')
				->in('CL1TMA', ['DIAGNOS','TIPOTRA','AJUSTES','MUESTRA','MODIFICA','SUSPENDE'])
				->orderBy('CL1TMA, DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function consultarAnexoCieInfeccioso($tcDiagnostico='')
	{
		$laParametros = [];
		$lcDiagnostico=substr($tcDiagnostico, 0, 3);

		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select(' trim(CL2TMA) CODIGO, trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'USOANTIB')
				->where('CL1TMA', '=', 'DIAGNOS')
				->where("CL2TMA like '%$lcDiagnostico%'")
				->where('CL3TMA', '=', '02')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laParametros;
	}

	public function validarAntibioticos($tcClasificacion1='',$tcClasificacion2='',$tcClasificacion3='')
	{
		$lcValidaDato='';
		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(DE2TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'USOANTIB')
				->where('CL1TMA', '=', $tcClasificacion1)
				->where('CL2TMA', '=', $tcClasificacion2)
				->where('CL3TMA', '=', $tcClasificacion3)
				->get('array');

			if ($this->oDb->numRows()>0){
				$lcValidaDato=$tcClasificacion2;
			}
		}
		return $lcValidaDato;
	}

	public function consultaListaDosis($tcCodigo='')
	{
		$laParametros = [];
		if(isset($this->oDb)){
			if (!empty($tcCodigo)){
				$laParametros = $this->oDb
					->select('UNIDAD CODIGO, TRIM(B.DE1TMA) DESCRIPCION')
					->from('INVMEDU AS A')
					->leftJoin("TABMAE B", "A.UNIDAD=INT(B.CL2TMA) AND B.TIPTMA='MEDDOS' AND B.OP1TMA='S' AND B.ESTTMA=''", null)
					->where('A.CODIGO', '=', $tcCodigo)
					->orderBy('B.DE1TMA')
					->getAll('array');
			}
			if (is_array($laParametros) && count($laParametros)>0){
				$laParametros=$laParametros;
			}else{
				$laParametros = $this->oDb
					->select('TRIM(CL2TMA) CODIGO, TRIM(DE1TMA) DESCRIPCION')
					->from('TABMAE')
					->where('TIPTMA', '=', 'MEDDOS')
					->where('OP1TMA', '=', 'S')
					->where('ESTTMA', '<>', '1')
					->orderBy('DE1TMA')
					->getAll('array');
			}

		}
		return $laParametros;
	}

	public function consultaListaViaAdministracion($tcCodigo='')
	{
		$laListaVias = $laParametros = [];
		if(isset($this->oDb)){
			if (!empty($tcCodigo)){
				$laParametros = $this->oDb
					->select('TRIM(OP2TMA) ALTORIESGO, TRIM(DE2TMA) DESCRIPCION')
					->from('TABMAE')
					->where('TIPTMA', '=', 'VIAADM')
					->where('DE1TMA', '=', $tcCodigo)
					->get('array');
			}
			if (is_array($laParametros) && count($laParametros)>0){
				$lcAltoRieso=$laParametros['ALTORIESGO'];
				$laDatosVias = explode(',', $laParametros['DESCRIPCION']);
				foreach($laDatosVias as $laViasAdministracion){
					$lcCodigoVia=trim($laViasAdministracion);
					$lcDescripcionVia=trim($this->oDb->obtenerTabmae1('DE1TMA', 'MEDVAD', "CL1TMA='$lcCodigoVia'", null, ''));

					$laListaVias[]=[
						'CODIGO'=>$lcCodigoVia,
						'DESCRIPCION'=>$lcDescripcionVia,
						'ALTORIESGO'=>$lcAltoRieso,
					];
				}
			}else{
				$laParametros = $this->oDb
				->select('TRIM(CL1TMA) CODIGO, TRIM(DE1TMA) DESCRIPCION')
				->from('TABMAE')
				->where('TIPTMA', '=', 'MEDVAD')
				->orderBy('DE1TMA')
				->getAll('array');
				if (is_array($laParametros) && count($laParametros)>0){
					foreach($laParametros as $laViasAdministracion){
						$lcCodigoVia=$laViasAdministracion['CODIGO'];
						$lcDescripcionVia=$laViasAdministracion['DESCRIPCION'];

						$laListaVias[]=[
							'CODIGO'=>$lcCodigoVia,
							'DESCRIPCION'=>$lcDescripcionVia,
							'ALTORIESGO'=>'',
						];
					}
				}
			}
		}
		return $laListaVias;
	}


	public function consultaIndicacionesInvima($tcCodigo='')
	{
		$laIndicaciones = [];
		if(isset($this->oDb)){
			if (!empty($tcCodigo)){
				$lcIndicaciones='';
				$lcAlertas='';

				$laParametros = $this->oDb
					->select('TRIM(DE2TMA) DESCRIPCION, TRIM(OP5TMA) OPCIONAL5')
					->from('TABMAE')
					->where('TIPTMA', '=', 'INVIMAIM')
					->where('OP2TMA', '=', $tcCodigo)
					->orderBy('CL2TMA')
					->getAll('array');
					if (is_array($laParametros) && count($laParametros)>0){
						foreach($laParametros as $laIndicaciones){
							$lcDescripcion=$laIndicaciones['DESCRIPCION'];
							$lcOpcional5=$laIndicaciones['OPCIONAL5']!='' ? $laIndicaciones['OPCIONAL5'] : '';
							$lcIndicaciones .= $lcDescripcion .$lcOpcional5;
						}
					}

				$laParametros = $this->oDb
					->select('TRIM(INDDES) DESCRIPCION')
					->from('INVATTR')
					->where('REFDES', '=', $tcCodigo)
					->getAll('array');
					if (is_array($laParametros) && count($laParametros)>0){
						foreach($laParametros as $laIndicaciones){
							$lcAlertas .= $laIndicaciones['DESCRIPCION'];
						}
					}

					$laIndicaciones=[
						'INDICACION'=>$lcIndicaciones,
						'ALERTA'=>$lcAlertas,
					];
			}
		}
		return $laIndicaciones;
	}

	public function consultaAlertaInr($tnIngreso=0,$tcCodigo='')
	{
		$laDatos=['RESULTADO'=>'', 'VALOR'=>'', 'FECHARESULTADO'=>'', 'HORARESULTADO'=>'', 'REFERENCIA'=>'', 'CUERPO'=>'',];
		$lcResultado=$lcValor=$lcFechaResultado=$lcHoraResultado=$lcTextoMensaje='';
		$lcSL = "\n"; 

		if(isset($this->oDb)){
			if (!empty($tcCodigo)){
				$laReg = $this->oDb->tabla('INVATTR')->where('REFDES', '=', $tcCodigo)->where('CL17DES', '=', 'ALERTAINR')->get('array');
				if ($this->oDb->numRows()>0){
					$laParametros=$this->oDb
						->select('trim(OP2TMA) TIPO, OP4TMA, TRIM(OP5TMA) CUPS')
						->from('TABMAE')
						->where('TIPTMA', '=', 'FORMEDIC')
						->where('CL1TMA', '=', 'REPLAB')
						->where('CL3TMA', '=', 'INR')
						->get('array');
					if ($this->oDb->numRows()>0){
						$lcCodigoCups=$laParametros['CUPS'];
						$lcTipoValor=$laParametros['TIPO'];
						$lcReferencia=$laParametros['OP4TMA'];
						
						$lcTextoMensaje ='Recuerde verificar el último resultado de INR antes de la prescripción de ' .'desmedicamento'
										.', de evidenciar un INR superior a ' .$lcReferencia .' suspenda la Warfarina o realice ajuste de farmacoterapia. ';

						$laDatosOrden = $this->oDb
							->select('trim(CRESUL) RESULTADO, RESFEC FECHA_RESULTADO, RESHOR HORA_RESULTADO')
							->from('LABRES')
							->where('NIGING', '=', $tnIngreso)
							->where('COAORD', '=', $lcCodigoCups)
							->where('CODVAR', '=', $lcTipoValor)
							->orderBy('RESFEC DESC, RESHOR DESC')
							->get('array');
						if ($this->oDb->numRows()>0){
							$lcResultado='S';

							$lcTextoMensaje=$lcTextoMensaje.'~'.'ÚLTIMO resultado del INR reportado en Historia Clínica es del día ' 
											.AplicacionFunciones::formatFechaHora('fecha',intval(trim($laDatosOrden['FECHA_RESULTADO'])),'/').' a las ' 
											.AplicacionFunciones::formatFechaHora('hora', $laDatosOrden['HORA_RESULTADO'], '', ':', '')
											.' con valor de ' .$laDatosOrden['RESULTADO'];
						}else{
							$lcResultado='N';
						}
					}

					$laDatos=[
						'RESULTADO'=>$lcResultado,
						'VALOR'=>$lcValor,
						'FECHARESULTADO'=>$lcFechaResultado,
						'HORARESULTADO'=>$lcHoraResultado,
						'REFERENCIA'=>$lcReferencia,
						'CUERPO'=>$lcTextoMensaje,
					];
				}	
			}
		}
		unset($laParametros);
		unset($laDatosOrden);
		return $laDatos;
	}

	public function consultarParametrosMedicamento($tcCodigo='')
	{
		$aParametros = [];
		$lcTipoJustificacion=$lcCantidadTotalJustificacion=$lcPosNopos=$lcCodigoGrupoFarmacologico=$lcDescripcionGrupoFarmacologico='';
		$lcControlAntibiotico=$lcDatosGrupoFarmacologico='';
		$lnDiasMaximoAntibiotico=$lnDiasUsadoAntibiotico=0;
		$llEsAntibiotico=false;

		if(isset($this->oDb)){
			$laParametros = $this->oDb
				->select('trim(rf4des ) POSNOPOS')
				->from('INVDES')
				->where('REFDES', '=', $tcCodigo)
				->get('array');
			if (is_array($laParametros) && count($laParametros)>0){
				$lcPosNopos=$laParametros['POSNOPOS'];
			}

			$laParametros = $this->oDb
				->select('trim(IDJUS) TIPOJUS, CANTOT CANTIDADTOTAL')
				->from('INVMEDA')
				->where('CODIGO', '=', $tcCodigo)
				->get('array');
			if (is_array($laParametros) && count($laParametros)>0){
				$lcTipoJustificacion=$laParametros['TIPOJUS'];
				$lcCantidadTotalJustificacion=$laParametros['CANTIDADTOTAL'];
			}
			$lcDatosGrupoFarmacologico=$this->fcGrupoFarmacologico($tcCodigo,true,true);
			$lcCodigoGrupoFarmacologico=explode('~', $lcDatosGrupoFarmacologico)[0] ?? '';
			$lcDescripcionGrupoFarmacologico=explode('~', $lcDatosGrupoFarmacologico)[1] ?? '';

			$laParametros = $this->oDb
				->select('trim(OP1TMA) OP1TMA, OP3TMA')
				->from('TABMAE')
				->where('TIPTMA', '=', 'ANTIBI')
				->where('ESTTMA', '=', '')
				->where('DE1TMA', '=', $tcCodigo)
				->get('array');
			if (is_array($laParametros) && count($laParametros)>0){
				$llEsAntibiotico=true;
				$lcControlAntibiotico=isset($laParametros['OP1TMA']) ? trim($laParametros['OP1TMA']) : '';
				$lnDiasMaximoAntibiotico=isset($laParametros['OP3TMA']) ? intval($laParametros['OP3TMA']) : 0;
			}

			$aParametros=[
				'posnopos'=>$lcPosNopos,
				'tipojustificacion'=>$lcTipoJustificacion,
				'cantidadtotaljustificacion'=>$lcCantidadTotalJustificacion,
				'grupocodigo'=>$lcCodigoGrupoFarmacologico,
				'grupodescripcion'=>$lcDescripcionGrupoFarmacologico,
				'esantibiotico'=>$llEsAntibiotico,
				'controlantibiotico'=>$lcControlAntibiotico,
				'diasmaximoantibiotico'=>$lnDiasMaximoAntibiotico,
				'diasusadoantibiotico'=>$lnDiasUsadoAntibiotico,
			];
		}
		unset($laParametros);
		return $aParametros;

	}

	public function fcGrupoFarmacologico($tcCodigo='', $tlTodosNiveles=false, $tlCodigo=false)
	{
		$lcGrupoFarmacologico='';
		if(isset($this->oDb)){
			if (!empty($tcCodigo)){

				if ($tlTodosNiveles){
					$laGrupo = $this->oDb
						->select('trim(I.GRUDES) GRUPOS, trim(N1.CL1TMA) CODN1, UPPER(trim(N1.DE1TMA)) DESN1')
						->select('trim(N2.CL1TMA) CODN2, UPPER(trim(N2.DE1TMA)) DESN2')
						->select('trim(N3.CL1TMA) CODN3, UPPER(trim(N3.DE1TMA)) DESN3')
						->select('trim(N4.CL1TMA) CODN4, UPPER(trim(N4.DE1TMA)) DESN4')
						->from('INVATTR I')
						->leftJoin("TABMAE AS N1", "N1.TIPTMA='GRUPTERA' AND N1.CL1TMA=SUBSTR(I.GRUDES,1,2)", null)
						->leftJoin("TABMAE AS N2", "N2.TIPTMA='GRUPTERA' AND N2.CL1TMA=SUBSTR(I.GRUDES,1,4)", null)
						->leftJoin("TABMAE AS N3", "N3.TIPTMA='GRUPTERA' AND N3.CL1TMA=SUBSTR(I.GRUDES,1,6)", null)
						->leftJoin("TABMAE AS N4", "N4.TIPTMA='GRUPTERA' AND N4.CL1TMA=SUBSTR(I.GRUDES,1,8)", null)
						->where('REFDES', '=', $tcCodigo)
						->get('array');

					if ($this->oDb->numRows()>0) {
						$lcCodigoGrupoFarmacologico=$laGrupo['GRUPOS'];
						$lcDescripcionGrupoFarmacologico=($laGrupo['DESN1']!='' ? $laGrupo['DESN1'] : '')
							.($laGrupo['CODN1']!=$laGrupo['CODN2'] && $laGrupo['DESN2']!='' ? (' / ' .$laGrupo['DESN2']) : '')
							.($laGrupo['CODN2']!=$laGrupo['CODN3'] && $laGrupo['DESN3']!='' ? (' / ' .$laGrupo['DESN3']) : '')
							.($laGrupo['CODN3']!=$laGrupo['CODN4'] && $laGrupo['DESN4']!='' ? (' / ' .$laGrupo['DESN4']) : '');
						$lcGrupoFarmacologico=$lcCodigoGrupoFarmacologico.'~'.$lcDescripcionGrupoFarmacologico;
					}

				}else{
					$laGrupo = $this->oDb
					->select('trim(I.CL1TMA) CODGRPFAR, UPPER(trim(I.DE1TMA)) GRPFAR')
					->from('TABMAE I')
					->leftJoin("invattr B", "I.CL1TMA=B.GRUDES", null)
					->where('I.TIPTMA', '=', 'GRUPTERA')
					->where('REFDES', '=', $tcCodigo)
					->get('array');
					if ($this->oDb->numRows()>0){
						$lcGrupoFarmacologico=$laGrupo['CODGRPFAR'].'~'.$laGrupo['GRPFAR'];
					}
				}
			}
		}
		return $lcGrupoFarmacologico;
	}

	public function consultarMezclaMedicamento($tcCodigo='',$tnDosis=0)
	{
		$aParametros = [];
		$lcTipoMezcla='N';
		$lcDescripcion='';
		$lcCantidadDiaria=$lcCantidadTotal=0;
		if(isset($this->oDb)){
			if (!empty($tcCodigo)){

				$laParametros = $this->oDb
					->select('CODIGO')
					->from('INVMEZC')
					->where('CODIGO', '=', $tcCodigo)
					->getAll('array');
				if (is_array($laParametros) && count($laParametros)>0){
					$laParametros = $this->oDb
						->select('CANDDI CANTIDADDIARIA, trim(DESDDI) DESCRIPCION, CANTOT CANTIDADTOTAL')
						->from('INVMEZC')
						->where('CODIGO', '=', $tcCodigo)
						->where($tnDosis.' BETWEEN CANDOS AND CANDO2')
						->get('array');

					if (is_array($laParametros) && count($laParametros)>0){
						$lcTipoMezcla='S';
						$lcCantidadDiaria=intval($laParametros['CANTIDADDIARIA']);
						$lcDescripcion=$laParametros['DESCRIPCION'];
						$lcCantidadTotal=intval($laParametros['CANTIDADTOTAL']);
					}else{
						$lcTipoMezcla='E';
					}
				}
			}
		}
		$aParametros=[
			'tipomezcla'=>$lcTipoMezcla,
			'cantidaddiariamezcla'=>$lcCantidadDiaria,
			'descripcionmezcla'=>$lcDescripcion,
			'cantidadtotalmezcla'=>$lcCantidadTotal,
		];

		unset($laParametros);
		return $aParametros;

	}

	public function consultarListaAntibioticos($taDescripcion='', $tcCodigo='', $tlControlados=true)
	{
		$laDatosMedicamentos = [];
		if ($taDescripcion!=''){
			if(is_array($taDescripcion)==false){
				$taDescripcion = array(trim(strval($taDescripcion)));
			}
			$tcCodigo = trim(strval($tcCodigo));
			$tcCodigo = mb_strtoupper(!empty($tcCodigo)?'%'.$tcCodigo.'%':'');

			if(count($taDescripcion)>0){
				$lcWhereAux = '';
				foreach($taDescripcion as $lcNombre){
					if(!empty($lcNombre) && $lcNombre!=='*'){
						$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
						$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("( (TRANSLATE(UPPER(DE2TMA),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')) OR (TRANSLATE(UPPER(DE1TMA),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')))", $lcNombre, $lcNombre);
					}
				}
				$lcWhere= (!empty($lcWhereAux)? $lcWhereAux :'');
			}
			$lcWhere.= (!empty($tcCodigo)?sprintf(" AND (DE2TMA LIKE '%s')", $tcCodigo):'');
			$lcWhere.= ($tlControlados==true ?" AND OP1TMA='S'":'');
			$lcWhere.= (" AND TIPTMA='ANTIBI'");
			$lcOrden='DE2TMA';


			$laMedicamentos = $this->oDb
				->select('TRIM(DE2TMA) DESCRIPCION, TRIM(DE1TMA) CODIGO, ESTTMA POSNOPOS')
				->from('TABMAE')
				->where($lcWhere)
				->orderBy($lcOrden)
				->getAll('array');

			if (is_array($laMedicamentos) && count($laMedicamentos)>0){
				foreach ($laMedicamentos as $laDatos){

					$laDatosMedicamentos[]=[
						'DESCRIPCION'=>$laDatos['DESCRIPCION'],
						'CODIGO'=>$laDatos['CODIGO'],
						'POSNOPOS'=>$laDatos['POSNOPOS'],
						'CONTROLADO'=>'',
						'CONCENTRACION'=>'',
						'UNIDAD'=>'',
						'PRESENTACION'=>'',
					];
				}
			}
		}
		return $laDatosMedicamentos;
	}
}
