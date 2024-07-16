<?php

namespace NUCLEO;

require_once ('class.Db.php');

use NUCLEO\Db;

class Habitacion
{
	public $cEstadoRegistro = '';
	public $cSeccion = '';
	public $cHabitacion = '';
	public $cUbicacion = '';
	public $nIngreso = 0;
	public $cTipoId = '';
	public $nId = 0;
	public $cCentroCosto = '';
	public $cTipoLiquidacion = '';
	public $cEstadoHabitacion = '';
	public $nValorAcompanante = 0;
	public $cBodega = '';
	public $cCUPS = '';
	public $cTipoHabitacion = '';
	public $cAcompanante = '';

    public function __construct($tnIngreso=0, $tcTipoId='',$tnId=0)
	{
		$this->cargarHabitacion($tnIngreso, $tcTipoId,$tnId);
	}

	public function cargarHabitacion($tnIngreso=0, $tcTipoId='',$tnId=0)
	{
		settype($tnIngreso,'integer');
		settype($tcTipoId,'string');
		settype($tnId,'integer');

		global $goDb;
		if(isset($goDb)){
			$laWhere=[];
			if(!empty($tnIngreso))	$laWhere['INGHAB'] = $tnIngreso;
			if(!empty($tcTipoId))	$laWhere['TIDHAB'] = $tcTipoId;
			if(!empty($tnId))		$laWhere['NIDHAB'] = $tnId;

			if(!empty($laWhere)){
				$laCampos = ['IDDHAB', 'SECHAB', 'NUMHAB', 'INGHAB', 'TIDHAB', 'NIDHAB', 'CENHAB', 'LIQHAB', 'ESTHAB', 'PACHAB', 'BODHAB', 'CUPHAB', 'TIPHAB', 'ACMHAB'];
				$laSecciones = $goDb->select($laCampos)->from('FACHAB')->where($laWhere)->orderBy('ESTHAB')->get('array');
				if(is_array($laSecciones)==true){
					$this->cEstadoRegistro = trim($laSecciones['IDDHAB']);
					$this->cSeccion = trim($laSecciones['SECHAB']);
					$this->cHabitacion = trim($laSecciones['NUMHAB']);
					$this->cUbicacion = $this->cSeccion.' - '.$this->cHabitacion;
					$this->nIngreso = ($laSecciones['INGHAB'])+0;
					$this->cTipoId = trim($laSecciones['TIDHAB']);
					$this->nId = ($laSecciones['NIDHAB'])+0;
					$this->cCentroCosto = trim($laSecciones['CENHAB']);
					$this->cTipoLiquidacion = trim($laSecciones['LIQHAB']);
					$this->cEstadoHabitacion = trim($laSecciones['ESTHAB']);
					$this->nValorAcompanante = ($laSecciones['PACHAB'])+0;
					$this->cBodega = trim($laSecciones['BODHAB']);
					$this->cCUPS = trim($laSecciones['CUPHAB']);
					$this->cTipoHabitacion = trim($laSecciones['TIPHAB']);
					$this->cAcompanante = trim($laSecciones['ACMHAB']);
				}
			}
		}
	}
}
?>