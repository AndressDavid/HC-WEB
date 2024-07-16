<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class TiposDocumento
{
    public $aTipos = [];

    public function __construct($tbNumCero = false) {
		$this->cargar($tbNumCero);
    }

	/*
	 *	Busca los tipos de documento y los coloca en aTipos
	 *	@param bool $tbNumCero: Si es true retorna también tipos con HORTI=0
	 */
	public function cargar($tbNumCero = false) {
		global $goDb;
		if (isset($goDb)) {
			// Buscando tipos de documento de identificación
			$laTipos = $goDb
						->select('TIPDOC,DOCUME,DESDOC,HORTI')
						->tabla('RIATI')
						->where('HORTI', '>', ($tbNumCero ? -1 : 0))
						->getAll('array');
			if (is_array($laTipos)) {
				if (count($laTipos) > 0) {
					foreach ($laTipos as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipos[$laTipo['TIPDOC']] = [
							'ABRV' => $laTipo['DOCUME'],
							'NOMBRE' => $laTipo['DESDOC'],
							'NUMERO' => $laTipo['HORTI'],
							];
					}
				}
			}
		}
	}
}
