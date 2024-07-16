<?php
namespace NUCLEO;

class Entidad
{
	public $cId = '';
	public $nId = 0;
	public $cNombre = '';
	public $cDigitoChequeo = '';
	public $aTipoTercero = array(); //N=Nacional E=Exterior
	public $cRazonSocial = '';
	public $cRazonComercial = '';
	public $aPersona = array(); // J=Jurídica/N=Natura
	public $lAutoretenedor = false; // S/N

	public function __construct($tcId='', $tnId=0, $tlInactivos=false){
		$tcId = trim(strval($tcId));
		$tnId = intval($tnId);
		$this->cargarDatos($tcId, $tnId, $tlInactivos);
	}

	public function cargarDatos($tcId='', $tnId=0, $tlInactivos=false)
	{
		$this->limpiarDatos();

		if (!empty($tnId)) {
			$lcId = str_pad(trim(strval($tnId)),13,'0',STR_PAD_LEFT);
			$lcTabla= ($tlInactivos==true?'PRMTE1 A':'PRMTE107 A');
			$laWhere = ['A.TE1COD'=>$lcId];
			if(!empty($tcId)){
				$laWhere = ['A.TE1COD'=>$lcId, 'A.TE1TIP'=>$tcId];
			}

			global $goDb;
			$laEntidad = $goDb
				->select(['A.TE1COD', 'A.TE1DIG', 'A.TE1TIP', 'A.TE1SOC', 'A.TE1COM', 'A.TE1PER', 'A.TE1RET'])
				->from($lcTabla)
				->where($laWhere)
				->get('array');

			if (is_array($laEntidad)) {
				if (count($laEntidad)>0) {
					$laEntidad = array_map('trim',$laEntidad);
					$this->nId = intval($laEntidad['TE1COD']);
					$this->cId = $laEntidad['TE1COD'];
					$this->cRazonSocial = $laEntidad['TE1SOC'];
					$this->cRazonComercial = $laEntidad['TE1COM'];
					$this->cNombre = (empty($this->cRazonSocial)?$this->cRazonComercial:$this->cRazonSocial);
					$this->cDigitoChequeo = $laEntidad['TE1DIG'];
					$this->aTipoTercero = array("CODIGO"=>$laEntidad['TE1TIP'], "DESCRIPCION"=>($laEntidad['TE1TIP']=='N'?'NACIONAL':'EXTERIOR'));
					$this->aPersona = array("CODIGO"=>$laEntidad['TE1PER'], "DESCRIPCION"=>($laEntidad['TE1PER']=='J'?'JURIDICA':'NATURAL'));
					$this->lAutoretenedor = ($laEntidad['TE1RET']=='S');
				}
			}
		}
	}

	public function limpiarDatos()
	{
		$this->cId = '';
		$this->nId = 0;
		$this->cNombre = '';
		$this->cDigitoChequeo = '';
		$this->aTipoTercero = array();
		$this->cRazonSocial = '';
		$this->cRazonComercial = '';
		$this->aPersona = array();
		$this->lAutoretenedor = false;
	}

}
?>