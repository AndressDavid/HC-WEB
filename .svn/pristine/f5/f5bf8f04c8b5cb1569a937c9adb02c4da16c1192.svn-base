<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.SeccionesHabitacion.php';
require_once __DIR__ . '/class.AplicacionFunciones.php';

use NUCLEO\Db;
use NUCLEO\Ingreso;
use NUCLEO\SeccionesHabitacion;
use NUCLEO\AplicacionFunciones;


class CensoBitacora
{
	protected $oDb;
	public $oIngreso=null;
	public $aInfoCenso=null;
	public $aRegistro=null;
	public $aPlanesManejoAdm=null;
	public $aPlanesManejoMed=null;
	public $aTiposDieta=null;
	public $aUbicacionesSeccion=null;
	public $aTiposPermisos=null;
	protected $cFecCre='';
	protected $cHorCre='';
	protected $cUsuCre='';
	protected $cPrgCre='';


	public function __construct($tnIngreso=0)
	{
		global $goDb;
		$this->oDb = $goDb;
		if ($tnIngreso>0) $this->cargarIngreso($tnIngreso);
    }


	public function cargarIngreso($tnIngreso)
	{
		$this->oIngreso=new Ingreso();
		$this->oIngreso->cargarIngreso($tnIngreso);
		$this->oIngreso->obtenerDescripcionPlan();
	}


	public function cargarInfoIngreso($tnIngreso)
	{
		if (is_null($this->aTiposPermisos)) $this->tiposPermisos();
		$mtt='h5';$mst='h6';
		$laInfo = [
			'ObsAdm'=>'',
			'ObsMed'=>'',
			'Diagnos'=>[],
			'Ubicacion'=>'',
			'Dieta'=>'',
			'PlanAdm'=>'',
			'PlanMed'=>'',
			'Excluir'=>'',
			'Medico'=>'',
			'DxPrincipal'=>'',
		];
		$laKey=[3=>'Ubicacion',4=>'Dieta',5=>'PlanAdm',13=>'PlanMed',14=>'Excluir',15=>'Medico',];
		$laTabla = $this->oDb
			->select('C.IN1CUR, C.IN2CUR, C.IN3CUR, C.CLNCUR, C.USRCUR, C.FECCUR, C.HORCUR, C.DESCUR')
			->select('TRIM(M.NNOMED) NNOMED, TRIM(M.NOMMED) NOMMED, M.REGMED, IFNULL(TRIM(T.NOMMED)||\' \'||TRIM(T.NNOMED),\'\') AS MEDTRA')
			->select('CASE WHEN C.IN1CUR=2 THEN (SELECT TRIM(DESRIP) FROM RIACIE WHERE ENFRIP=C.DESCUR) ELSE \'\' END DESCIE')
			->from('CENURGL1 C')
			->leftJoin('RIARGMN M', 'C.USRCUR=M.USUARI', null)
			->leftJoin('RIARGMN T', 'C.DESCUR=T.REGMED', null)
			->where(['C.INGCUR'=>$tnIngreso])
			->orderBy('C.IN1CUR ASC, C.IN2CUR DESC, C.IN3CUR ASC, C.CLNCUR ASC')
			->getAll('array');
		if(is_array($laTabla)) {

			$lnIndice = $lnSubInd = -1;
			foreach($laTabla as $laFila) {
				$lnIndiceTemp = intval($laFila['IN1CUR']);
				//$lnSubIndTemp = in_array($lnIndiceTemp,[1,11])? intval($laFila['IN3CUR']): intval($laFila['IN2CUR']);
				$lnSubIndTemp = in_array($lnIndiceTemp,[1,11,12])? intval($laFila['IN3CUR']): intval($laFila['IN2CUR']);

				if ($lnIndice == $lnIndiceTemp) {
					if ($lnSubInd == $lnSubIndTemp) {
						$llSubInd = false;
					} else {
						$lnSubInd = $lnSubIndTemp;
						$llSubInd = true;
					}
				} else {
					$lnIndice = $lnIndiceTemp;
					$lnSubInd = $lnSubIndTemp;
					$llSubInd = true;
				}

				switch($lnIndice) {

					// Observaciones Administrativas
					case 1:
						if ($llSubInd) {
							$lcFecha = AplicacionFunciones::formatFechaHora('fechahora',$laFila['FECCUR']*1000000+$laFila['HORCUR']);
							$laInfo['ObsAdm'] .= "<{$mtt}>Por: {$laFila['NNOMED']} {$laFila['NOMMED']}</{$mtt}><{$mst}>{$lnSubInd} - {$lcFecha}</{$mst}>";
						}
						$laInfo['ObsAdm'] .= $laFila['DESCUR'];
						break;

					// Observaciones Asistenciales
					case 11:
						if ($llSubInd) {
							$lcFecha = AplicacionFunciones::formatFechaHora('fechahora',$laFila['FECCUR']*1000000+$laFila['HORCUR']);
							$laInfo['ObsMed'] .= "<{$mtt}>Por: {$laFila['NNOMED']} {$laFila['NOMMED']}</{$mtt}><{$mst}>{$lnSubInd} - {$lcFecha}</{$mst}>";
						}
						$laInfo['ObsMed'] .= $laFila['DESCUR'];
						break;

					// Histórico de diagnósticos
					case 2:
						if ($llSubInd) {
							$lcFecha = AplicacionFunciones::formatFechaHora('fechahora',$laFila['FECCUR']*1000000+$laFila['HORCUR']);
							//$laInfo['Diagnos'][$llSubInd] = "<{$mtt}>Por: {$laFila['NNOMED']} {$laFila['NOMMED']}</{$mtt}><{$mst}>{$lnSubInd} - {$lcFecha}</{$mst}>";
							$laInfo['Diagnos'][$lnSubInd] = "<{$mtt}>Por: {$laFila['NNOMED']} {$laFila['NOMMED']}</{$mtt}><{$mst}>{$lnSubInd} - {$lcFecha}</{$mst}>";
						}
						//$laInfo['Diagnos'][$llSubInd] .= trim($laFila['DESCUR']).' - '.$laFila['DESCIE'].'<br>';
						$laInfo['Diagnos'][$lnSubInd] .= trim($laFila['DESCUR']).' - '.$laFila['DESCIE'].'<br>';
						break;
					case 12:
						//if ($llSubInd) $laInfo['Diagnos'][$llSubInd] .= '<b>Observaciones:</b> ';
						if ($llSubInd) $laInfo['Diagnos'][$lnSubInd] .= '<b>Observaciones:</b> ';
						//$laInfo['Diagnos'][$llSubInd] .= $laFila['DESCUR'];
						$laInfo['Diagnos'][$lnSubInd] .= $laFila['DESCUR'];
						break;

					case 3:  // Ubicación
					case 4:  // Dieta
					case 5:  // Plan de manejo Admin
					case 13: // Plan de manejo Médico
					case 14: // Excluir
						$laInfo[$laKey[$lnIndice]] = trim($laFila['DESCUR']);
						break;
					case 15: // Médico tratante
						$laInfo[$laKey[$lnIndice]] = [
							'regmed'=>trim($laFila['DESCUR']),
							'nombre'=>trim($laFila['MEDTRA']),
						];
						break;
				}
			}

			// Dx Principal
			if (in_array('02',$this->aTiposPermisos)) {
				// Dx de evolución
				$laDx = $this->oDb->select('TRIM(DESEVL) AS DESCRIPCION')->from('EVOLUCL2')->where(['NINEVL'=>$tnIngreso,'CNLEVL'=>5991])->orderBy('CONEVL DESC')->get('array');
				$laInfo['DxPrincipal'] = $laDx['DESCRIPCION']??'';
				// Dx Historia Clínica ¿Es necesario?
				if (empty($laInfo['DxPrincipal'])) {
					$laDx = $this->oDb->select('TRIM(DESHCL) AS DESCRIPCION')->from('HISCLI')->where(['INGHCL'=>$tnIngreso,'INDHCL'=>25,'SUBHCL'=>1])->orderBy('FECHCL')->get('array');
					$laInfo['DxPrincipal'] = $laDx['DESCRIPCION']??'';
				}
				// Dx Historia Clínica Nueva
				if (empty($laInfo['DxPrincipal'])) {
					$laDx = $this->oDb->select('TRIM(SUBORG) AS DESCRIPCION')->from('RIAHIS15')->where(['NROING'=>$tnIngreso,'INDICE'=>25,'SUBIND'=>1])->orderBy('FECHIS')->get('array');
					$laInfo['DxPrincipal'] = $laDx['DESCRIPCION']??'';
				}
			}

			if (empty($laInfo['Excluir'])) $laInfo['Excluir']='NO';
		}

		return $laInfo;
	}


	public function infoCensoHC($tnIngreso, $tcDxPrin, $tcPlanDx)
	{
		if (is_null($this->oIngreso) || $tnIngreso>0) $this->cargarIngreso($tnIngreso);
		if (is_null($this->aPlanesManejoAdm)) $this->planesManejoAdmCenso();
		//if (is_null($this->aPlanesManejoMed)) $this->planesManejoMedCenso();
		if (is_null($this->aTiposDieta)) $this->tiposDieta();
		$this->aInfoCenso = [];

		// Informacion del censo de urgencias
		$laData = [
			1 => 'observaciones',
			4 => 'codDieta',
			5 => 'codPlanAdmin',
			14=> 'tipoRegistro',
		];
		foreach ($laData as $lnIndice=>$lcInfo) {
			$laTabla = $this->oDb
				->select('TRIM(DESCUR)')
				->from('CENURGL1')
				->where(['INGCUR'=>$tnIngreso,'IN1CUR'=>$lnIndice])
				->orderBy('IN1CUR, IN3CUR DESC')
				->get('array');
			$this->aInfoCenso[$lcInfo] = $laTabla['DESCUR']??'';
		}
		$this->aInfoCenso['phd'] = empty($this->aInfoCenso['codPlanAdmin'])? '': $this->aPlanesManejoAdm[$this->aInfoCenso['codPlanAdmin']];

		// Interconsultas
		$laTabla = $this->oDb
			->count('*','CUENTA')
			->from('RIAORD')
			->where(['NINORD'=>$tnIngreso,'ESTORD'=>'8',])
			->like('COAORD','8904%')
			->get('array');
		$this->aInfoCenso['interconsultas'] = ($laTabla['CUENTA']??0)>0 ? 'X': '';

		// Dieta
		$laTabla = $this->oDb
			->select('TRIM(DESEVL) AS DIETA')
			->from('EVOLUCO')
			->where(['NINEVL'=>$tnIngreso,'CNLEVL'=>'1700',])
			->orderBy('CONEVL DESC')
			->get('array');
		$this->aInfoCenso['dieta'] = substr(trim($laTabla['DIETA']??''),7);
		if (empty($this->aInfoCenso['dieta'])) {
			if (!empty($this->aInfoCenso['codDieta'])) {
				$this->aInfoCenso['dieta'] = isset($this->aTiposDieta[$this->aInfoCenso['codDieta']]) ? $this->aTiposDieta[$this->aInfoCenso['codDieta']]['DSC']: '';
			}
		} else {
			if (empty($this->aInfoCenso['codDieta'])) {
				foreach ($this->aTiposDieta as $lcCod=>$lcDieta) {
					if ($lcDieta['DSC']==$this->aInfoCenso['dieta']) {
						$this->aInfoCenso['codDieta'] = $lcCod;
						break;
					}
				}
			}
		}

		$this->aInfoCenso += [
			'ubicacion' => $this->oIngreso->oHabitacion->cUbicacion,
			'seccion' => $this->oIngreso->oHabitacion->cSeccion,
			'ingreso' => $this->oIngreso->nIngreso,
			'fecha' => $this->oIngreso->nIngresoFecha,
			'hora' => $this->oIngreso->nIngresoHora,
			'entidad' => $this->oIngreso->cPlanDescripcion,
			'entidadtipo' => $this->oIngreso->cPlanTipoDsc, // Descripción tipo de entidad PRMTAB con TABTIP='024'
			'paciente' => $this->oIngreso->oPaciente->getNombreCompleto(),
			'genero' => $this->oIngreso->oPaciente->getGenero(),
			'sexo' => $this->oIngreso->oPaciente->cSexo,
			'edad' => $this->oIngreso->oPaciente->getEdad(),
			//'observaciones' => '',  // obs1
			'diagnostico' => $tcDxPrin,
			'plan' => $tcPlanDx,
			//'phd' => '',
			//'dieta' => '',
			'tipoId' => $this->oIngreso->cId,  // tipo
			'numeroId' => $this->oIngreso->nId,  // iden
			'via' => $this->oIngreso->cVia,
			'viades' => $this->oIngreso->cDescVia,
			//'interconsultas' => '',  // interc
			'hay_dx' => !empty($tcDxPrin), // diamed
			'hay_dieta' => !empty($this->aInfoCenso['dieta']),  // diemed
			'hay_plan' => !empty($tcPlanDx),  // phdmed
			//'tipoRegistro' => '',  // tipreg
		];

		return $this->aInfoCenso;
	}


	public function planesManejoAdmCenso()
	{
		$this->aPlanesManejoAdm = [];
		$laTabla = $this->oDb
			->select('TRIM(DE1TMA) AS DESCRIP, TRIM(CL3TMA) AS CODIGO')
			->from('TABMAEL01')
			->where('TIPTMA=\'CENUSU\' AND CL1TMA=\'PARAMETR\' AND CL2TMA=\'PLANADMI\' ')
			->orderBy('DE1TMA')
			->getAll('array');
		if (is_array($laTabla)) {
			foreach($laTabla as $laFila) {
				$this->aPlanesManejoAdm[$laFila['CODIGO']] = $laFila['DESCRIP'];
			}
		}

		return $this->aPlanesManejoAdm;
	}


	public function planesManejoMedCenso()
	{
		$this->aPlanesManejoMed = [];
		$laTabla = $this->oDb
			->select('TRIM(DE1TMA) AS DESCRIP, TRIM(CL3TMA) AS CODIGO')
			->from('TABMAEL01')
			->where('TIPTMA=\'CENUSU\' AND CL1TMA=\'PARAMETR\' AND CL2TMA=\'PLANMEDI\' ')
			->orderBy('DE1TMA')
			->getAll('array');
		if (is_array($laTabla)) {
			foreach($laTabla as $laFila) {
				$this->aPlanesManejoMed[$laFila['CODIGO']] = $laFila['DESCRIP'];
			}
		}

		return $this->aPlanesManejoMed;
	}


	public function ubicacionesSeccion()
	{
		$this->aUbicacionesSeccion = (new SeccionesHabitacion(-1))->ubicacionesSeccion('URGENCIAS');
		return $this->aUbicacionesSeccion;
	}


	public function tiposDieta()
	{
		$this->aTiposDieta = [];
		$laTabla = $this->oDb
			->select('TRIM(SUBSTR(DE2TMA,1,40)) AS DESDIE, TRIM(SUBSTR(DE1TMA,1,15)) AS CODIGO, TRIM(OP5TMA) AS CARDIE')
			->from('TABMAEL01')
			->where('TIPTMA=\'DIESHA\'')
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laTabla)) {
			foreach($laTabla as $laFila) {
				$this->aTiposDieta[$laFila['CODIGO']] = [
					'DSC'=>$laFila['DESDIE'],
					'CAR'=>$laFila['CARDIE'],
				];
			}
		}

		return $this->aTiposDieta;
	}


	public function tiposPermisos()
	{
		$lcUser = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
		$lcTipoPer = $this->oDb->obtenerTabmae1('DE2TMA', 'CENUSU', "CL1TMA='PERMISOS' AND CL2TMA='USUARIOS' AND CL3TMA='$lcUser'", null, '');
		if (empty($lcTipoPer)) {
			$lcTipoUsu = $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario();
			if (in_array($lcTipoUsu, [1,9,91])) {
				$lcTipoPer = '02';
			}
		}
		$this->aTiposPermisos = explode(',',$lcTipoPer);

		return $this->aTiposPermisos;
	}


	public function validarRegistro($taDatos)
	{
		if (is_null($this->aTiposPermisos)) $this->tiposPermisos();

		// ADMINISTRATIVOS
		if (in_array('01', $this->aTiposPermisos)) {
			if (empty($taDatos['planAdmin'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selPlanAdmin',
					'error'=>'Plan Administrativo es obligatorio',
				];
			}
		}

		// ASISTENCIALES
		if (in_array('02', $this->aTiposPermisos)) {
			if (empty($taDatos['planMedico'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selPlanMedico',
					'error'=>'Plan Médico es obligatorio',
				];
			}
			if (empty($taDatos['dieta'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selDieta',
					'error'=>'Dieta es obligatoria',
				];
			}
		}

		// COMBINADOS
		if (in_array('01', $this->aTiposPermisos) || in_array('02', $this->aTiposPermisos)) {
			if (empty($taDatos['ubicacion'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selUbicacion',
					'error'=>'Ubicación es obligatoria',
				];
			}
			if (empty($taDatos['excluirBit'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selExcluirBit',
					'error'=>'Excluir Bitácora es obligatoria',
				];
			}
			if (empty($taDatos['medico'])) {
				return [
					'datosValidos'=>false,
					'objeto'=>'selMedico',
					'error'=>'Mádico es obligatorio',
				];
			}
		}

		return [
			'datosValidos'=>true,
			'objeto'=>'',
			'error'=>'',
		];
	}


	public function guardarRegistro($taDatos)
	{
		$this->obtenerDatosLog();

		$laDatosGuardar = [
			[ 1, 0, 'obsAdmin',],
			[11, 0, 'obsAsist',],
			[ 2, 1, 'dxPrincipal',],
			[ 2, 2, 'dxRelacionado1',],
			[ 2, 3, 'dxRelacionado2',],
			[ 2, 4, 'dxRelacionado3',],
			[12, 0, 'obsDiagnostico',],
			[ 3, 0, 'ubicacion',],
			[ 4, 0, 'dieta',],
			[ 5, 0, 'planAdmin',],
			[13, 0, 'planMedico',],
			[ 6, 0, 'via',], // REGISTRO SIN DOCUMENTAR. SE CREA POR CONTINUIDAD
			[ 7, 0, 'via',],
			[14, 0, 'excluirBit',],
			[15, 0, 'medico',],
		];
		foreach ($laDatosGuardar as $laData) {
			$lnIndice = $laData[0];
			$lnSubIndice = $laData[1];
			$lcDato = $lnIndice==6 ? '1' : $taDatos[$laData[2]]??'';
			if (!empty($lcDato)) {
				$this->insertarRegistro($taDatos['ingreso'], $lcDato, $lnIndice, $lnSubIndice);
			}
		}
	}


	public function obtenerDatosLog()
	{
		$this->cUsuCre = $_SESSION[HCW_NAME]->oUsuario->getUsuario();	// lcUsuario
		$this->cPrgCre = 'CENBITWEB';									// lcPrograma
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format('Ymd');						// lnFecha
		$this->cHorCre = $ltAhora->format('His');						// lnHora
	}


	public function nuevoConsecutivo($tnIngreso, $tnIndice, $tcField)
	{
		$laCns = $this->oDb->max($tcField,'MAYOR')->from('CENURGL1')->where(['INGCUR'=>$tnIngreso,'IN1CUR'=>$tnIndice])->get('array');
		$lnConsecutivo = ($laCns['MAYOR']??0) + 1;
		return $lnConsecutivo;
	}


	public function insertarRegistro($tnIngreso, $tcTexto, $tnIndice, $tnSubIndice)
	{
		$lcTbl = 'CENURGL1';
		$laVariasLineas = [1,11,12];			// Indices con varias líneas
		$laIndActualiza = [3,4,5,6,7,13,14,15];	// Indices para actualizar
		$laSinSubInd = [6,7,15];				// Indices sin subíndice

		$lcField = $tnIndice==2 ? 'IN2CUR': 'IN3CUR';
		$laTextos = AplicacionFunciones::mb_str_split($tcTexto, 220);
		$lnLinea = in_array($tnIndice, $laVariasLineas) ? 1 : 0;

		// Indices que se pueden actualizar
		if (in_array($tnIndice, $laIndActualiza)) {
			$lnConsecutivo = 0;
			$laWhere = [
				'INGCUR'=>$tnIngreso,
				'IN1CUR'=>$tnIndice,
			];
			$laExiste = $this->oDb->count('*','NUMREG')->from($lcTbl)->where($laWhere)->get('array');
			$lbInsertar = ( ($laExiste['NUMREG']??0)>0 ) ? false : true;
		} else {
			$lbInsertar = true;
			$lnConsecutivo = in_array($tnIndice, $laSinSubInd) ? 0 : $this->nuevoConsecutivo($tnIngreso, $tnIndice, $lcField);
		}

		foreach ($laTextos as $lcTexto) {
			if ($lbInsertar) {
				$laDatos = [
					'INGCUR'=>$tnIngreso,
					'IN1CUR'=>$tnIndice,
					'IN2CUR'=>($tnIndice==2 ? $lnConsecutivo : 0),
					'IN3CUR'=>($tnIndice==2 ? $tnSubIndice : $lnConsecutivo),
					'CLNCUR'=>$lnLinea++,
					'DESCUR'=>$lcTexto,
					'USRCUR'=>$this->cUsuCre,
					'PGMCUR'=>$this->cPrgCre,
					'FECCUR'=>$this->cFecCre,
					'HORCUR'=>$this->cHorCre,
				];
				$this->oDb->from($lcTbl)->insertar($laDatos);
			} else {
				$laDatos = [
					'DESCUR'=>$lcTexto,
					'UMOCUR'=>$this->cUsuCre,
					'PMOCUR'=>$this->cPrgCre,
					'FMOCUR'=>$this->cFecCre,
					'HMOCUR'=>$this->cHorCre,
				];
				$this->oDb->from($lcTbl)->where($laWhere)->actualizar($laDatos);
			}
		}

		if ($tnIndice==2 && $tnSubIndice==1) {
			// Eliminar las observacines de diagnósticos
			$this->oDb->from($lcTbl)->where(['INGCUR'=>$tnIngreso,'IN1CUR'=>12,'IN3CUR'=>$lnConsecutivo])->eliminar();
		}
	}

}
