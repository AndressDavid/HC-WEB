<?php
/******* TIPO DE DOCUMENTO *******/
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

class TipoDocumento
{
	public $aTipo = [
			'TIPO' => '',
			'ABRV' => '',
			'NOMBRE' => '',
			'NUMERO' => '',
			];

    public function __construct($tipoId='') {
		$this->cargar($tipoId);
    }

	/*
	 *	Retorna las características del tipo de documento indicado
	 *	@param string $tipoId: Tipo de documento C, T, R, etc
	 */
	public function cargar($tipoId='') {
		global $goDb;
		$tcId=trim($tipoId);

		if (isset($goDb)) {
			if (!empty($tipoId)) {
				// Buscando tipo de documento de identificación
				$laTipo = $goDb
					->select('TIPDOC,DOCUME,DESDOC,HORTI')
					->tabla('RIATI')
					->where(['TIPDOC'=>$tipoId])
					->get('array');
				if (is_array($laTipo)) {
					if (count($laTipo) > 0) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipo = [
							'TIPO' => $laTipo['TIPDOC'],
							'ABRV' => $laTipo['DOCUME'],
							'NOMBRE' => $laTipo['DESDOC'],
							'NUMERO' => $laTipo['HORTI'],
							];
					}
				}
			}
		}
	}

	/*
	 *	Retorna tipo de documento shaio
	 *	@param string $tipoId: Tipo de documento CC, TI, RC, etc
	 */
	public function cargarTipoDoc($tipoId='') {
		global $goDb;
		$tcId=trim($tipoId);

		if (isset($goDb)) {
			if (!empty($tipoId)) {
				// Buscando tipo de documento de identificación
				$laTipo = $goDb
					->select('TIPDOC,DOCUME,DESDOC,HORTI')
					->tabla('RIATI')
					->where(['DOCUME'=>$tipoId])
					->get('array');
				if (is_array($laTipo)) {
					if (count($laTipo) > 0) {
						$laTipo = array_map('trim', $laTipo);
						$this->aTipo = [
							'TIPO' => $laTipo['TIPDOC'],
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
