<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Db.php';


class DescripcionQuirurgica
{
	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
    }

	public function consultarConsumosSalas($tcJson)
	{
		$laDatos=[];
		$tcJsonEnviar = json_encode($tcJson);
		$laDatos = $this->oDb->select("Alphildat.F_CONSUMOS_SALAS('{$tcJsonEnviar}') AS DATOS")->from("SYSIBM.SYSDUMMY1")->getAll();
		return $laDatos;
	}
	
}