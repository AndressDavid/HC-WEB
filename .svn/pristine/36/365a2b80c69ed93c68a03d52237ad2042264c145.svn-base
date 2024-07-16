<?php
namespace NUCLEO;

class TipoAfiliado
{
	public $cTipo = null;
	public $cDescripcion = null;


	public function __construct($tcTipo='')
	{
		$this->cargarDatos($tcTipo);
	}

	public function cargarDatos($tcTipo='')
	{
		$this->limpiarDatos();
		if (!empty($tcTipo)) {
			global $goDb;
			$oTabmae = $goDb->ObtenerTabMae('DE1TMA', 'DATING', ['CL1TMA'=>'2', 'CL2TMA'=>$tcTipo, ]);
			$this->cDescripcion = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));
			$this->cTipo = $tcTipo;
		}
	}

	public function limpiarDatos()
	{
		$this->cTipo = '';
		$this->cDescripcion = '';
	}

}