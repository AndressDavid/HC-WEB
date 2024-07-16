<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.AplicacionFunciones.php');
require_once ('class.Ingreso.php');
require_once ('class.Medico.php');

use NUCLEO\Db;
use NUCLEO\AplicacionFunciones;
use NUCLEO\Ingreso;
use NUCLEO\Medico;

class Cita {
	protected $cIdTipo = '';
	protected $nId = 0;
	protected $nCita = 0;
	protected $nConsulta = 0;
	protected $nEvolucion = 0;
	protected $nIngreso = 0;
	protected $cEspecialidad = '';
	protected $cEspecialidadNombre = '';
	protected $nClasificacionProcedimiento =0;
	protected $cProcedimiento = '';
	protected $cProcedimientoNombre = '';
	protected $cRegistroMedicoOrdeno = '';
	protected $nFecha = 0;
	protected $nCitaFecha = 0;
	protected $nCitaHora = 0;
	protected $cRegistroMedicoRealiza = '';
	protected $nRealizadoFecha = 0;
	protected $nRealizadoHora = 0;
	protected $nRealizadoFinFecha = 0;
	protected $nRealizadoFinHora = 0;	
	protected $cTeleconsulta = '';
	protected $nEstadoCita = 0;
	protected $cUnidadAgenda = '';
	protected $cConsecutivoOrden = '';
	protected $cViaIngreso = '';
	protected $cViaIngresoNombre = '';
	protected $cReunionId = '';
	protected $cReunionKey = '';
	protected $cEstado = '';
	protected $nArchivosPaciente = 0;
	protected $cObservacionPaciente = '';
	protected $cObservacionMedico = '';
	protected $nValoracionPaciente = 0;
	protected $nValoracionMedico = 0;
	protected $aEstados = array();
	protected $oIngreso = null;
	protected $oMedicoRealiza = null;

	private $aArguments = null;
	private $nArguments = 0;
	private $cConstrucName = '';
	private $cPrograma = '';

    public function __construct ($tcModulo='', $tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion=0){
		$this->aEstados = $this->obtenerEstados();
        $this->aArguments = func_get_args();
        $this->nArguments = func_num_args();
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
		$this->oIngreso = new Ingreso();
		$this->oMedicoRealiza = new Medico();

        if(method_exists($this, $this->cConstrucName = '__construct'.$this->nArguments)) {
            call_user_func_array(array($this, $this->cConstrucName), $this->aArguments);
        }
    }
	public function obtenerEstados(){
		$laEstados = array();
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(A.CL3TMA) CODIGO','TRIM(A.DE1TMA) DESCRIPCION'];
			$laEstadosAux = $goDb
							->select($laCampos)
							->from('TABMAE A')
							->where("A.TIPTMA='TELEMED' AND A.CL1TMA='CITAS' AND A.CL2TMA='01010101'")
							->orderBy('TRIM(A.DE1TMA) ASC')
							->getAll('array');
			if(is_array($laEstadosAux)==true){
				foreach($laEstadosAux as $laEstado){
					$laEstados[] = $laEstado;
				}
			}
		}
		return $laEstados;
	}				
	
    public function __construct2($tcModulo='', $tnIngreso=0){
		//if($tcModulo=='JTM'){
			$this->cargarIngresoPortalPacientes($tnIngreso);
		//}
    }

    public function __construct6($tcModulo='', $tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion=0){
		//if($tcModulo=='JTM'){
			$this->cargarCitaPortalPacientes($tcIdTipo, $tnId, $tnCita, $tnConsulta, $tnEvolucion);
		//}
    }

	public function cargarIngresoPortalPacientes($tnIngreso=0){
		$tnIngreso = intval($tnIngreso);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if(empty($tnIngreso)==false){

				$laCampos = ['J.TIDCIT', 'J.NIDCIT', 'J.CCICIT', 'J.CCOCIT', 'J.EVOCIT'];

				$laCita = $goDb
								->select($laCampos)
								->from('JTMCIT J')
								->where('J.NINCIT','=',$tnIngreso)
								->get('array');
 				if(is_array($laCita)==true){
					if(count($laCita)>0){
						$llResultado = $this->cargarCitaPortalPacientes($laCita['TIDCIT'],$laCita['NIDCIT'],$laCita['CCICIT'],$laCita['CCOCIT'],$laCita['EVOCIT']);
					}
				}
			}
		}
		return $llResultado;
	}

    public function cargarCitaPortalPacientes($tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion=0){
		$tcIdTipo = trim(strval($tcIdTipo));
		$tnId = intval($tnId);
		$tnCita = intval($tnCita);
		$tnConsulta = intval($tnConsulta);
		$tnEvolucion = intval($tnEvolucion);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if(empty($tcIdTipo)==false && empty($tnId)==false){

				$laCampos = [
							 'J.*',
							 'E.CODESP','E.DESESP',
							 'C.CODCUP','C.DESCUP',
							 'V.CODVIA','V.DESVIA',
							];

				$laCita = $goDb
								->select($laCampos)
								->from('JTMCIT J')
								->leftJoin('RIAESPE E', 'E.CODESP=J.CODCIT', null)
								->leftJoin('RIACUP C', 'C.CODCUP=J.COACIT', null)
								->leftJoin('RIAVIA V', 'V.CODVIA=J.VIACIT', null)
								->where('J.TIDCIT','=',$tcIdTipo)
								->where('J.NIDCIT','=',$tnId)
								->where('J.CCICIT','=',$tnCita)
								->where('J.CCOCIT','=',$tnConsulta)
								->where('J.EVOCIT','=',$tnEvolucion)
								->get('array');

 				if(is_array($laCita)==true){
					if(count($laCita)>0){
						$this->cIdTipo = $laCita['TIDCIT'];
						$this->nId = $laCita['NIDCIT'];
						$this->nCita = $laCita['CCICIT'];
						$this->nConsulta = $laCita['CCOCIT'];
						$this->nEvolucion = $laCita['EVOCIT'];
						$this->nIngreso = $laCita['NINCIT'];
						$this->cEspecialidad = $laCita['CODCIT'];
						$this->cEspecialidadNombre = trim($laCita['DESESP']);
						$this->nClasificacionProcedimiento = $laCita['CD2CIT'];
						$this->cProcedimiento = trim($laCita['COACIT']);
						$this->cProcedimientoNombre = trim($laCita['DESCUP']);
						$this->cRegistroMedicoOrdeno = trim($laCita['RMECIT']);
						$this->nFecha = $laCita['FCOCIT'];
						$this->nCitaFecha = $laCita['FRLCIT'];
						$this->nCitaHora = $laCita['HOCCIT'];
						$this->cRegistroMedicoRealiza = $laCita['RMRCIT'];
						$this->nRealizadoFecha = $laCita['FERCIT'];
						$this->nRealizadoHora = $laCita['HRLCIT'];
						$this->nRealizadoFinFecha = $laCita['FEFCIT'];
						$this->nRealizadoFinHora = $laCita['HRFCIT'];						
						$this->cTeleconsulta = $laCita['CONCIT'];
						$this->nEstadoCita = $laCita['ESTCIT'];
						$this->cUnidadAgenda = trim($laCita['ESCCIT']);
						$this->cConsecutivoOrden = trim($laCita['INSCIT']);
						$this->cViaIngreso = $laCita['VIACIT'];
						$this->cViaIngresoNombre = trim($laCita['DESVIA']);
						$this->cReunionId = $laCita['JTMUID'];
						$this->cReunionKey = $laCita['JTMKEY'];
						$this->cEstado = $laCita['ESTADO'];
						$this->nArchivosPaciente = $laCita['ARCCAR'];
						$this->cObservacionPaciente = $laCita['OBSPAC'];
						$this->cObservacionMedico = $laCita['OBSMED'];
						$this->nValoracionPaciente = intval($laCita['VALPAC']);
						$this->nValoracionMedico = intval($laCita['VALMED']);
						$this->oIngreso->cargarIngreso($this->nIngreso);
						$this->oMedicoRealiza->cargarRegistroMedico($this->cRegistroMedicoRealiza);
						
						if($this->oIngreso->oPaciente->nId==0){
							$this->oIngreso->oPaciente->cargarPaciente($this->cIdTipo, $this->nId, $this->nIngreso);
						}
						
						$llResultado = true;
					}
				}
			}
		}
		return $llResultado;
    }

	public function actualizarNumeroArchivosCargados($tnArchivos=0){
		$tnArchivos = intval($tnArchivos);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if(empty($this->cIdTipo)==false && empty($this->nId)==false){

				$laDatos = ['ARCCAR'=>$tnArchivos];
				$llResultado = $goDb
								->tabla('JTMCIT')
								->where('TIDCIT','=',$this->cIdTipo)
								->where('NIDCIT','=',$this->nId)
								->where('CCICIT','=',$this->nCita)
								->where('CCOCIT','=',$this->nConsulta)
								->where('EVOCIT','=',$this->nEvolucion)
								->actualizar($laDatos);
			}
		}
		return $llResultado;
	}

	public function actualizarEstadoObservacionCita($tcModulo = '', $tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion = 0, &$tcError, $tcEstado = '', $tnValoracion=0, $tcObservacion = '', $tnFechaRealiza=0, $tnHoraRealiza=0, $tcUsuario=''){
		$tcIdTipo = trim(strval($tcIdTipo));
		$tnId = intval($tnId);
		$tnCita = intval($tnCita);
		$tnConsulta = intval($tnConsulta);
		$tnEvolucion = intval($tnEvolucion);
		$tcUsuario = strval($tcUsuario);
		$tcError = 'Parámetros incompletos';
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lcFecha = $ltAhora->format("Ymd");
			$lcHora  = $ltAhora->format("His");			
			
			if(empty($tcIdTipo)==false && empty($tnId)==false){

				$laDatos = ['ESTADO'=>trim(substr($tcEstado,0,1)), 'VALMED'=>intval($tnValoracion), 'OBSMED'=>trim(substr($tcObservacion,0,500)), 'UMOCIT'=>$tcUsuario, 'PMOCIT'=>$this->cPrograma, 'FMOCIT'=>intval($lcFecha), 'HMOCIT'=>intval($lcHora)];
				$llResultado = $goDb
								->tabla('JTMCIT')
								->where('TIDCIT','=',$tcIdTipo)
								->where('NIDCIT','=',$tnId)
								->where('CCICIT','=',$tnCita)
								->where('CCOCIT','=',$tnConsulta)
								->where('EVOCIT','=',$tnEvolucion)
								->actualizar($laDatos);
								
				$laCita = $goDb
								->from('JTMCIT J')
								->where('J.TIDCIT','=',$tcIdTipo)
								->where('J.NIDCIT','=',$tnId)
								->where('J.CCICIT','=',$tnCita)
								->where('J.CCOCIT','=',$tnConsulta)
								->where('J.EVOCIT','=',$tnEvolucion)
								->get('array');

 				if(is_array($laCita)==true){
					if(count($laCita)>0){
						// Actualizando la fecha y hora de realizado si no se ha definido previamente
						$laDatos = ['FEFCIT'=>intval($lcFecha), 'HRFCIT'=>intval($lcHora), 'UMOCIT'=>$tcUsuario, 'PMOCIT'=>$this->cPrograma, 'FMOCIT'=>intval($lcFecha), 'HMOCIT'=>intval($lcHora)];
						if(intval($tnFechaRealiza)>0){
							if($laCita['FRLCIT']==$laCita['FERCIT'] && $laCita['HOCCIT']==$laCita['HRLCIT']){					
								$laDatos = ['FERCIT'=>intval($tnFechaRealiza), 'HRLCIT'=>intval($tnHoraRealiza), 'FEFCIT'=>intval($lcFecha), 'HRFCIT'=>intval($lcHora), 'UMOCIT'=>$tcUsuario, 'PMOCIT'=>$this->cPrograma, 'FMOCIT'=>intval($lcFecha), 'HMOCIT'=>intval($lcHora)];
							}
						}
						$llResultado = $goDb
										->tabla('JTMCIT')
										->where('TIDCIT','=',$tcIdTipo)
										->where('NIDCIT','=',$tnId)
										->where('CCICIT','=',$tnCita)
										->where('CCOCIT','=',$tnConsulta)
										->where('EVOCIT','=',$tnEvolucion)
										->actualizar($laDatos);						
					}
				}

			}
		}
		return $llResultado;
	}
	
	public function actualizarValoracionPacienteCita($tcModulo = '', $tcIdTipo = '', $tnId = 0, $tnCita = 0, $tnConsulta = 0, $tnEvolucion = 0, &$tcError, $tnValoracion = 0, $tcObservacion = ''){
		$tcIdTipo = trim(strval($tcIdTipo));
		$tnId = intval($tnId);
		$tnCita = intval($tnCita);
		$tnConsulta = intval($tnConsulta);
		$tnEvolucion = intval($tnEvolucion);
		$tcError = 'Parámetros incompletos';
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if(empty($tcIdTipo)==false && empty($tnId)==false){

				$laDatos = ['VALPAC'=>intval($tnValoracion), 'OBSPAC'=>trim(substr($tcObservacion,0,500))];
				$llResultado = $goDb
								->tabla('JTMCIT')
								->where('TIDCIT','=',$tcIdTipo)
								->where('NIDCIT','=',$tnId)
								->where('CCICIT','=',$tnCita)
								->where('CCOCIT','=',$tnConsulta)
								->where('EVOCIT','=',$tnEvolucion)
								->actualizar($laDatos);

			}
		}
		return $llResultado;
	}	

	public function getPortalPacientesLlaveCita(){
		return sprintf('PP-%s-%s', $this->nCita, $this->nCitaFecha);
	}

	public function getIdTipo(){
		return $this->cIdTipo;
	}

	public function getIdNumero(){
		return $this->nId;
	}

	public function getCita(){
		return $this->nCita;
	}

	public function getConsulta(){
		return $this->nConsulta;
	}

	public function getEvolucion(){
		return $this->nEvolucion;
	}

	public function getIngreso($tlObject=false){
		if($tlObject==true){
			return $this->oIngreso;
		}
		return $this->nIngreso;
	}

	public function getEspecialidad(){
		return $this->cEspecialidad;
	}

	public function getEspecialidadNombre(){
		return $this->cEspecialidadNombre;
	}

	public function getClasificacionProcedimient(){
		return $this->nClasificacionProcedimiento;
	}

	public function getProcedimiento(){
		return $this->cProcedimiento;
	}

	public function getProcedimientoNombre(){
		return $this->cProcedimientoNombre;
	}

	public function getRegistroMedicoOrdeno(){
		return $this->cRegistroMedicoOrdeno;
	}

	public function getFecha(){
		return $this->nFecha;
	}

	public function getCitaFecha(){
		return $this->nCitaFecha;
	}

	public function getCitaHora(){
		return $this->nCitaHora;
	}

	public function getCitaFechaHora($tlUTF=false){
		$lcCitaFechaHora = '';
		if(!empty($this->nCitaFecha)){
			$lcCitaFechaHora = AplicacionFunciones::formatFechaHora('fechahora', strval($this->nCitaFecha).strval($this->nCitaHora), '-', ':', ($tlUTF==true?'T':' '));
		}
		return $lcCitaFechaHora;
	}

	public function getMedicoRealiza($tlObject=false){
		return $this->oMedicoRealiza;
	}

	public function getRegistroMedicoRealiza(){
		return $this->cRegistroMedicoRealiza;
	}

	public function getRealizadoFecha(){
		return $this->nRealizadoFecha;
	}

	public function getRealizadoHora(){
		return $this->nRealizadoHora;
	}
	
	public function getRealizadoFinFecha(){
		return $this->nRealizadoFinFecha;
	}

	public function getRealizadoFinHora(){
		return $this->nRealizadoFinHora;
	}	

	public function getTeleconsulta(){
		return $this->cTeleconsulta;
	}

	public function getEstadoCita(){
		return $this->nEstadoCita;
	}

	public function getUnidadAgenda(){
		return $this->cUnidadAgenda;
	}

	public function getConsecutivoOrden(){
		return $this->cConsecutivoOrden;
	}

	public function getViaIngreso(){
		return $this->cViaIngreso;
	}

	public function getViaIngresoNombre(){
		return $this->cViaIngresoNombre;
	}

	public function getReunionId(){
		return sprintf('%s-%s',$this->nIngreso,$this->cReunionId);
	}

	public function getReunionKey(){
		return $this->cReunionKey;
	}

	public function getEstado(){
		return $this->cEstado;
	}

	public function getArchivosPaciente(){
		return $this->nArchivosPaciente;
	}

	public function getObservacionPaciente(){
		return $this->cObservacionPaciente;
	}

	public function getObservacionMedico(){
		return $this->cObservacionMedico;
	}

	public function getValoracionPaciente(){
		return $this->nValoracionPaciente;
	}

	public function getValoracionMedico(){
		return $this->nValoracionMedico;
	}

	public function getEstados(){
		return $this->aEstados;
	}
}
?>