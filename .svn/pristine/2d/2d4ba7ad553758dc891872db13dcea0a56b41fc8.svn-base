<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.AplicacionFunciones.php');
require_once ('class.Ingreso.php');

use NUCLEO\Db;
use NUCLEO\AplicacionFunciones;
use NUCLEO\Ingreso;

class SalaApertura {
	protected $nIngreso = 0;
	protected $nConsecutivo = 0;
	protected $nEstado = 0;
	protected $nFecha = 0;
	protected $nHora = 0;
	protected $cSala = '';
	protected $nTipoCirugia = 0;
	protected $nTipoAnestesia = 0;
	protected $cTipoSangre = '';
	protected $nForma = 0;
	protected $cCentroCostos = '';
	protected $cProcedimiento = '';
	protected $cDiagnosticoEntra = '';
	protected $cDiagnosticoSale = '';
	
	protected $oIngreso = null;

    public function __construct (){
		$this->oIngreso = new Ingreso();
    }


    public function cargarCirugia($tnIngreso = 0, $tnConsecutivo=0){
		$tnIngreso = intval($tnIngreso);
		$tnConsecutivo = intval($tnConsecutivo);

		global $goDb;
		if(isset($goDb)){
			if(empty($tnIngreso)==false && empty($tnConsecutivo)==false){

				$laCampos = ['F.*'];

				$laCirugia = $goDb
								->select($laCampos)
								->from('FACCIRH F')
								->where('F.INGCRH','=',$tnIngreso)
								->where('F.CNSCRH','=',$tnConsecutivo)
								->get('array');

 				if(is_array($laCirugia)==true){

					
					if(count($laCirugia)>0){
						$this->nIngreso = intval($laCirugia['INGCRH']);
						$this->nConsecutivo = intval($laCirugia['CNSCRH']);
						$this->nEstado = intval($laCirugia['ESTCRH']);
						$this->nFecha = intval($laCirugia['FHRCRH']);
						$this->nHora = intval($laCirugia['HRRCRH']);
						$this->cSala = trim(strval($laCirugia['SLRCRH']));
						$this->nTipoCirugia = intval($laCirugia['TPRCRH']);
						$this->nTipoAnestesia = intval($laCirugia['TANCRH']);
						$this->cTipoSangre = trim(strval($laCirugia['TSGCRH']));
						$this->nForma = intval($laCirugia['FORCRH']);
						$this->cCentroCostos = trim(strval($laCirugia['CTRCRH']));
						$this->cProcedimiento = trim(strval($laCirugia['CUPCRH']));
						$this->cDiagnosticoEntra = trim(strval($laCirugia['DG1CRH']));
						$this->cDiagnosticoSale = trim(strval($laCirugia['DG2CRH'])); 
						
						$this->oIngreso->cargarIngreso($this->nIngreso);

						$llResultado = true;
					}
				}
			}
		}
		return $llResultado;
    }


	public function getIngreso($tlObject=false){
		if($tlObject==false){
			return $this->nIngreso;
		}else{
			return $this->oIngreso;
		}
	}
	
	public function getConsecutivo(){
		return $this->nConsecutivo;
	}
	
	public function getEstado(){
		return $this->nEstado;
	}
	
	public function getFecha(){
		return $this->nFecha;
	}
	
	public function getHora(){
		return $this->nHora;
	}
	
	public function getSala(){
		return $this->cSala;
	}
	
	public function getTipoCirugia(){
		return $this->nTipoCirugia;
	}
	
	public function getTipoAnestesia(){
		return $this->nTipoAnestesia;
	}
	
	public function getTipoSangre(){
		return $this->cTipoSangre;
	}
	
	public function getForma(){
		return $this->nForma;
	}
	
	public function getCentroCostos(){
		return $this->cCentroCostos;
	}
	
	public function getProcedimiento(){
		return $this->cProcedimiento;
	}
	
	public function getDiagnosticoEntra(){
		return $this->cDiagnosticoEntra;
	}
	
	public function getDiagnosticoSale(){
		return $this->cDiagnosticoSale;
	}
	

}
?>