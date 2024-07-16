<?php
namespace NUCLEO;

class CitaProcedimiento
{
	public $nIngreso         = 0 ; // Número de Ingreso
	public $nCnsCita         = 0 ; // Consec Cita
	public $nCnsEvolucion    = 0 ; // Consec Evolucion
	public $nCnsConsulta     = 0 ; // Consec Consulta
	public $cCodEspecialidad = ''; // Codigo de la Especialid
	public $cCodCup          = ''; // Codigo de Procedimento
	public $cRegMedOrdena    = ''; // Reg médico que Ordeno
	public $nFechaOrdena     = 0 ; // Fecha
	public $nFechaCita       = 0 ; // Fecha Cita
	public $nHoraCita        = 0 ; // Hora Cita
	public $cRegMedRealiza   = ''; // Reg med que realiza
	public $nFechaRealiza    = 0 ; // Fecha de realizado
	public $nHoraRealiza     = 0 ; // Hora de realizado
	public $nEstado          = 0 ; // Estado procedimiento
	public $nCodEntidad      = 0 ; // Entidad
	public $cCodVia          = ''; // Via de ingreso
	public $cCodPlan         = ''; // Plan
	public $cSeccion         = ''; // Sección de la cama
	public $cHabitacion      = ''; // Número de la cama

    function __construct($tnIngreso=0, $tnCita=0) {
		$this->cargarDatos($tnIngreso, $tnCita);
    }

	public function cargarDatos($tnIngreso=0, $tnCita=0)
	{
		$this->limpiarDatos();
		if (!empty($tnIngreso) && !empty($tnCita)) {
			global $goDb;
			$laCitPr = $goDb->from('RIAORD')->where(['NINORD'=>$tnIngreso,'CCIORD'=>$tnCita,])->get('array');
			if (is_array($laCitPr)) {
				if (count($laCitPr)>0) {
					$laCitPr=array_map('trim',$laCitPr);
					$this->nIngreso          = $laCitPr['NINORD'];
					$this->nCnsCita          = $laCitPr['EVOORD'];
					$this->nCnsEvolucion     = $laCitPr['CCOORD'];
					$this->nCnsConsulta      = $laCitPr['CCIORD'];
					$this->cCodEspecialidad  = $laCitPr['CODORD'];
					$this->cCodCup           = $laCitPr['COAORD'];
					$this->cRegMedOrdena     = $laCitPr['RMEORD'];
					$this->nFechaOrdena      = $laCitPr['FCOORD'];
					$this->nFechaCita        = $laCitPr['FRLORD'];
					$this->nHoraCita         = $laCitPr['HOCORD'];
					$this->cRegMedRealiza    = $laCitPr['RMRORD'];
					$this->nFechaRealiza     = $laCitPr['FERORD'];
					$this->nHoraRealiza      = $laCitPr['HRLORD'];
					$this->nEstado           = $laCitPr['ESTORD'];
					$this->nCodEntidad       = $laCitPr['ENTORD'];
					$this->cCodVia           = $laCitPr['VIAORD'];
					$this->cCodPlan          = $laCitPr['PLAORD'];
					$this->cSeccion          = $laCitPr['SCAORD'];
					$this->cHabitacion       = $laCitPr['NCAORD'];
				}
			}
		}
	}

	public function limpiarDatos()
	{
		$this->nIngreso         = 0 ;
		$this->nCnsCita         = 0 ;
		$this->nCnsEvolucion    = 0 ;
		$this->nCnsConsulta     = 0 ;
		$this->cCodEspecialidad = '';
		$this->cCodCup          = '';
		$this->cRegMedOrdena    = '';
		$this->nFechaOrdena     = 0 ;
		$this->nFechaCita       = 0 ;
		$this->nHoraCita        = 0 ;
		$this->cRegMedRealiza   = '';
		$this->nFechaRealiza    = 0 ;
		$this->nHoraRealiza     = 0 ;
		$this->nEstado          = 0 ;
		$this->nCodEntidad      = 0 ;
		$this->cCodVia          = '';
		$this->cCodPlan         = '';
		$this->cSeccion         = '';
		$this->cHabitacion      = '';		
	}
}