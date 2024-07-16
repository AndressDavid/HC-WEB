<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class EstadosHabitacion
{
    public $aTipos = [];

    public function __construct() {
		$this->cargar();
    }

	public function cargar() {
		global $goDb;
		if (isset($goDb)) {
			// Buscando tipos de documento de identificación
			$laTipos = $goDb
						->select('CL1TMA, CL4TMA, DE1TMA, DE2TMA , OP1TMA')
						->from('TABMAE')
						->where('TIPTMA', '=', 'ESTHABI')
						->where('CL1TMA', '<>', '')
						->getAll('array');
			if (is_array($laTipos)) {
				if (count($laTipos) > 0) {
					foreach ($laTipos as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipos[$laTipo['CL4TMA']] = [
							'CODIGO' => $laTipo['CL1TMA'],
							'NOMBRE' => $laTipo['DE1TMA'],
							'COLOR' => $laTipo['DE2TMA'],
							'ESTADO' => $laTipo['OP1TMA'],
							];
					}
				}
			}
		}
	}
}
