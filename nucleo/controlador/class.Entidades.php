<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;


class Entidades
{
	/*
	 *	Objeto de base de datos
	 */
    protected $oDb;


	/*
	 *	Función constructor
	 */
    public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


    /*
	 *	Obtiene la lista de Nits y Entidades
	 *	Si recibe usuario retorna la lista de entidades que puede acceder el usuario
	 *	Si no recibe usuario o viene vacío retorna todas las entidades
	 *
	 *	@param string $tcUsuario: usuraio de historia clínica
	 *	@return array Nit=>Entidad
	 */
	public function listaNitEntidades($tcUsuario = '')
	{
		$tlFiltrarUsu = false;
		if (!empty($tcUsuario)) {
			// Valida si el usuario puede ver todas las entidades
			$laUser = $this->oDb
				->count('USUARI','CUENTA')
				->from('SISMENENT')
				->where([
					'USUARI' => $tcUsuario,
					'ENTENT' => 0,
					'PLAENT' => '*',
					'ESTENT' => 'A',
				])
				->getAll('array');

			if (is_array($laUser)) {
				$tlFiltrarUsu = ($laUser[0]['CUENTA'] ?? 0) == 0;
			}
		}

		$this->oDb
			->select('P.NI1CON, T.TE1SOC')->distinct()
			->from('FACPLNC P')
			->innerJoin('PRMTE1 T', 'DIGITS(P.NI1CON)=T.TE1COD', null)
			->orderBy('T.TE1SOC');

		if ($tlFiltrarUsu) {
			$this->oDb->innerJoin('SISMENENT U', "U.USUARI='{$tcUsuario}' AND U.ENTENT=P.NI1CON AND U.PLAENT='' AND U.ESTENT='A'", null);
		}

		$laLista = $this->oDb->getAll('array');

		$laNitEnt = [];
		if (is_array($laLista)) {
			foreach ($laLista as $laItem) {
				$laNitEnt[ $laItem['NI1CON'] ] = trim($laItem['TE1SOC']);
			}
		}
		return $laNitEnt;
	}
}