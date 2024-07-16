<?php
namespace NUCLEO;

class Plan
{
	public $cCodigo = '';
	public $cDescripcion = '';
	public $cNit = '';
	public $cCodRegional = '';
	public $cCodRips = '';
	public $nTipoEntidad = 0;
	public $cTipoEntidad = '';
	public $nNumeroContrato = 0;


	public function __construct($tcCodPlan='')
	{
		$this->cargarDatos($tcCodPlan);
	}

	public function cargarDatos($tcCodPlan='')
	{
		$this->limpiarDatos();
		if (!empty($tcCodPlan)) {
			global $goDb;
			$laPlan = $goDb
				->select('PLNCON, NI1CON, RG1CON, RIACON, POSCON, DSCCON, TENCON, NUMCON')
				->from('FACPLNC')
				->where(['PLNCON'=>$tcCodPlan])
				->get('array');

			if (is_array($laPlan)) {
				if (count($laPlan)>0) {
					$laPlan = array_map('trim',$laPlan);
					$this->cCodRegional = $laPlan['RG1CON'];
					$this->cCodRips = $laPlan['RIACON'];
					$this->cCodigo = $laPlan['PLNCON'];
					$this->cDescripcion = $laPlan['DSCCON'];
					$this->cNit = $laPlan['NI1CON'];
					$this->cTipoEntidad = $laPlan['TENCON'];
					$this->nNumeroContrato = $laPlan['NUMCON'];
					$this->nTipoEntidad = intval($laPlan['POSCON']);
				}
			}
		}
	}

	public function limpiarDatos()
	{
		$this->cCodigo = '';
		$this->cDescripcion = '';
		$this->cNit = '';
		$this->cCodRegional = '';
		$this->cCodRips = '';
		$this->nTipoEntidad = 0;
		$this->cTipoEntidad = '';
		$this->nNumeroContrato = 0;
	}

	public function getPlan(){
		return ["CODIGO" => $this->cCodigo, "CONTRATO" => $this->nNumeroContrato, "DESCRIPCION" => $this->cDescripcion, "NIT" => $this->cNit, "REGIONAL" => $this->cCodRegional, "RIPS" => $this->cCodRips, "TIPO" => $this->nTipoEntidad, "TIPO_NOMBRE" => $this->cTipoEntidad];
	}
}