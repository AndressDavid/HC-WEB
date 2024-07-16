<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class Via
{
	public $aVias = [];

	public function __construct()
	{
		global $goDb;
		if(isset($goDb)){
			$laVias = $goDb->select('TRIM(CODVIA) CODVIA, TRIM(DESVIA) DESVIA')->from('RIAVIA')->orderBy('DESVIA')->getAll("array");
			
			if(is_array($laVias)==true){
				$this->aVias=$laVias;
			}
		}
	}

	public function obtenerListaVias()
	{
		$laVias = [];
		foreach ($this->aVias as $laVia) {
			$laVias[$laVia['CODVIA']] = $laVia['DESVIA'];
		}
		return $laVias;
	}
}
