<?php
namespace NUCLEO;

class Cup
{
	public $cEstado		 =''; // ESTADO REGISTRO
	public $cCup		 =''; // CUP
	public $cRef1		 =''; // REFERENCIA CLASIF 1
	public $cRef2		 =''; // REFERENCIA CLASIF 2
	public $cRef3		 =''; // REFERENCIA CLASIF 3
	public $cRef4		 =''; // REFERENCIA CLASIF 4
	public $cRef5		 =''; // REFERENCIA CLASIF 5
	public $cRef6		 =''; // REFERENCIA CLASIF 6
	public $cDscrCup	 =''; // DESCRIPCION CUP
	public $cUsarProc	 =''; // UTILIZA PROCEDMTO
	public $cPrograma	 =''; // PROGRAMA EJECUTAR
	public $cSiRips		 =''; // INDICADOR SI RIPS
	public $cMarca		 =''; // MARCA
	public $cEspecialidad=''; // ESPECIALIDAD
	public $cSexo		 =''; // SEXO DEBE APLICARSE
	public $cArcAds		 =0 ; // ARCADS
	public $cArchProc	 =0 ; // ARCHPROCEDI
	public $cOtros		 =0 ; // DE OTROS
	public $cCodLab		 =''; // CODIGO LABORATORIO

    function __construct($tcCUP='') {
		$this->cargarDatos($tcCUP);
    }
	
	public function cargarDatos($tcCUP='')
	{
		$this->limpiarDatos();
		if(empty($tcCUP)){ return; }
		$tcCUP=trim($tcCUP);
		if(strlen($tcCUP)>8){ return; }
		global $goDb;
		$laCup = $goDb->from('RIACUP')->where(['CODCUP'=>$tcCUP])->get('array');
		if($goDb->numRows()>0){
			$laCup=array_map('trim',$laCup);
			$this->cEstado		= $laCup['IDDCUP'];
			$this->cCup			= $laCup['CODCUP'];
			$this->cRef1		= $laCup['RF1CUP'];
			$this->cRef2		= $laCup['RF2CUP'];
			$this->cRef3		= $laCup['RF3CUP'];
			$this->cRef4		= $laCup['RF4CUP'];
			$this->cRef5		= $laCup['RF5CUP'];
			$this->cRef6		= $laCup['RF6CUP'];
			$this->cDscrCup		= $laCup['DESCUP'];
			$this->cUsarProc	= $laCup['PROCUP'];
			$this->cPrograma	= $laCup['PGRCUP'];
			$this->cSiRips		= $laCup['RIPCUP'];
			$this->cMarca		= $laCup['MARCUP'];
			$this->cEspecialidad= $laCup['ESPCUP'];
			$this->cSexo		= $laCup['SEXCUP'];
			$this->cArcAds		= $laCup['CADCUP'];
			$this->cArchProc	= $laCup['CAPCUP'];
			$this->cOtros		= $laCup['CATCUP'];
			$this->cCodLab		= $laCup['CLBCUP'];
		}
	}

	public function limpiarDatos()
	{
		$this->cEstado		= '';
		$this->cCup			= '';
		$this->cRef1		= '';
		$this->cRef2		= '';
		$this->cRef3		= '';
		$this->cRef4		= '';
		$this->cRef5		= '';
		$this->cRef6		= '';
		$this->cDscrCup		= '';
		$this->cUsarProc	= '';
		$this->cPrograma	= '';
		$this->cSiRips		= '';
		$this->cMarca		= '';
		$this->cEspecialidad= '';
		$this->cSexo		= '';
		$this->cArcAds		= 0;
		$this->cArchProc	= 0;
		$this->cOtros		= 0;
		$this->cCodLab		= '';
	}
	
}