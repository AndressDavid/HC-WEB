<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class TiposAlerta
{
    public $aTipos = [];

    public function __construct() {
		$this->cargar();
    }

	public function cargar() {
		global $goDb;
		if (isset($goDb)) {

			$laTipos = $goDb
						->select('CL2TMA, DE1TMA, ESTTMA, OP5TMA')
						->from('TABMAE')
						->where('TIPTMA', '=', 'ALETEMP')
						->where('CL1TMA', '=', '10200102')
						->where('CL2TMA', '<>','')
						->getAll('array');
			if (is_array($laTipos)) {
				if (count($laTipos) > 0) {
					foreach ($laTipos as $laTipo) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipos[$laTipo['CL2TMA']] = [
							'NOMBRE' => $laTipo['DE1TMA'],
							'ESTADO' => $laTipo['ESTTMA'],
							'ICONOW' => $laTipo['OP5TMA'],
							];
					}
				}
			}
		}
	}
}
