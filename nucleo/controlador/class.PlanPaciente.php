<?php
namespace NUCLEO;

require_once 'class.Plan.php';
require_once 'class.Entidad.php';

use NUCLEO\Plan;

class PlanPaciente
    extends Plan {

	public $cId = '';
	public $nId = 0;
	public $cOrdenAtencion = '';
	public $nRegional = 0;
	public $cEstrato = '';
	public $cCarnet = '';
	public $cEstadoEntidad = '';
	public $cMoneda = '';
	public $nValorCubierto = 0;
	public $nValorConsumido = 0;
	public $cAnoCopagos = '';
	public $nFechaIngreso = 0;
	public $nPresupuesto = 0;
	public $nFiller1 = 0;
	public $cFill1a = '';
	public $cFill2a = '';
	public $oEntidad = null;
	public $aTipoUsuario = array();
	public $aTipoAfiliado = array();

	//Planes del paciente
	public function __construct($tcId="", $tnId=0, $tcCodPlan='')	{
		$this->cargarDatos($tcCodPlan);
		$this->cargarDatosPaciente($tcId, $tnId, $tcCodPlan);
	}

	private function cargarDatosPaciente($tcId="", $tnId=0, $tcCodPlan=''){
		global $goDb;
		if(isset($goDb)){
			if(empty($tcId)==false && $tnId>0 && empty($tcCodPlan)==false){
				$laCampos = ['A.*',
							 'B.NUMCON', 'B.TENCON', 'B.DSCCON', 'A.ORDEPP',
							 "(SELECT TRIM(DE1TMA) FROM TABMAEL01 WHERE TIPTMA='DATING' AND CL1TMA='1' AND ESTTMA='' AND SUBSTR(TRIM(CL2TMA),1,1)=TRIM(A.TIPEPP)) AS DUSPLA",
							 "(SELECT TRIM(DE1TMA) FROM TABMAEL01 WHERE TIPTMA='DATING' AND CL1TMA='2' AND ESTTMA='' AND SUBSTR(TRIM(CL2TMA),1,1)=TRIM(A.TIAEPP)) AS DAFPLA"];
				$laPlanes = $goDb
					->select($laCampos)
					->from('RIAEPP A')
					->innerJoin('FACPLNC B', 'A.PLAEPP=B.PLNCON', null)
					->where(['A.TIDEPP'=>$tcId, 'A.NIDEPP'=>$tnId, 'A.PLAEPP'=>$tcCodPlan])
					->get('array');

				if(is_array($laPlanes)){
					if(count($laPlanes)>0){
						$this->cId = trim($laPlanes['TIDEPP']);
						$this->nId = intval($laPlanes['NIDEPP']);
						$this->cOrdenAtencion = trim($laPlanes['ORDEPP']);
						$this->oEntidad = new Entidad('', $laPlanes['ENTEPP']);
						$this->nRegional = intval($laPlanes['REGEPP']);
						$this->cEstrato = trim($laPlanes['ETTEPP']);
						$this->cCarnet = trim($laPlanes['NCAEPP']);
						$this->aTipoUsuario = array('CODIGO'=>trim($laPlanes['TIPEPP']),'DESCRIPCION'=>trim($laPlanes['DUSPLA']));
						$this->aTipoAfiliado = array('CODIGO'=>trim($laPlanes['TIAEPP']),'DESCRIPCION'=>trim($laPlanes['DAFPLA']));
						$this->cEstadoEntidad = trim($laPlanes['ESTEPP']);
						$this->cMoneda = trim($laPlanes['MDAEPP']);
						$this->nValorCubierto = intval($laPlanes['VALEPP']);
						$this->nValorConsumido = intval($laPlanes['VCSEPP']);
						$this->nValorCopagosAno = intval($laPlanes['SMGEPP']);
						$this->nFechaIngreso = intval($laPlanes['FEIEPP']);
						$this->nPresupuesto = intval($laPlanes['PRESUP']);
						$this->nFiller1 = intval($laPlanes['FILL1N']);
						$this->cFill1a = trim($laPlanes['FILL1A']);
						$this->cFill2a = trim($laPlanes['FILL2A']);
					}
				}
			}
		}
	}
	
	public function planesPaciente($tcId="", $tnId=0)
	{
		global $goDb;
		if(isset($goDb)){
			$laParams = $goDb
				->select('trim(A.PLAEPP) CODIGO, trim(B.DSCCON) DESCRIPCION')
				->from('RIAEPP A')
				->leftJoin('FACPLNC B', 'A.PLAEPP=B.PLNCON', null)
				->where(['A.TIDEPP'=>$tcId, 'A.NIDEPP'=>$tnId])
				->orderBy('ORDEPP')
				->getAll('array');
			if(is_array($laParams)==true){
				$this->aPlanesPaciente=$laParams;
			}
		}
		return $this->aPlanesPaciente;
	}
	
	public function listaPlanesPaciente()
	{
		return $this->aPlanesPaciente;
	}
	
}