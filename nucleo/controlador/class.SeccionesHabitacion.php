<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class SeccionesHabitacion
{
	public $aSecciones = array();


	public function __construct($tnTipo=0)
	{
		switch($tnTipo){
			case 1: $this->consultaSeccionesPrmtab(); break;
			case -1: break; // no carga nada
			default: $this->consultaSeccionesTabmae(); break;
		}
	}

	public function consultaSeccionesTabmae()
	{
		global $goDb;
		if(isset($goDb)){
			$laSecciones = $goDb
				->select('CL1TMA, CL2TMA, CL3TMA, ESTTMA, DE1TMA, DE2TMA, OP1TMA, OP5TMA')
				->from('TABMAE')
				->where('TIPTMA=\'SECHAB\'')
				->orderBy('DE1TMA')
				->getAll('array');
			if(is_array($laSecciones)){
				foreach($laSecciones as $laSeccion){
					$laSeccion = array_map('trim',$laSeccion);
					$this->aSecciones[$laSeccion['CL1TMA']] = [
						'TIPO'=>$laSeccion['CL2TMA'],
						'SALA'=>$laSeccion['CL3TMA'],
						'ESTADO'=>$laSeccion['ESTTMA'],
						'NOMBRE'=>$laSeccion['DE1TMA'],
						'DESCRIPCION'=>$laSeccion['DE2TMA'],
						'UBICACION'=>$laSeccion['OP1TMA'],
						'MONITOREO'=>$laSeccion['OP5TMA'],
					];
				}
			}
		}
	}

	public function consultaSeccionesPrmtab()
	{
		global $goDb;
		if(isset($goDb)){
			$laSecciones = $goDb
				->select('TRIM(TABCOD) CODIGO, TRIM(TABDSC) DESCRIPCION')
				->from('PRMTAB02')
				->where('TABTIP=\'041\'')
				->orderBy('TABCOD')
				->getAll('array');
			if(is_array($laSecciones)){
				foreach($laSecciones as $laSeccion){
					$this->aSecciones[$laSeccion['CODIGO']] = $laSeccion;
				}
			}
		}
	}

	public function habitacionesSeccion($tcSeccion='')
	{
		global $goDb;
		if(!isset($goDb) || empty($tcSeccion)) return [];

		$laTabla = $goDb
			->select('TRIM(SECHAB)||\'-\'||TRIM(NUMHAB) CODHABITA, TRIM(NUMHAB) NUMHAB')
			->from('FACHAB')
			->where(['SECHAB'=>$tcSeccion])
			->getAll('array');
		return is_array($laTabla)? $laTabla: [];
	}

	public function consultaSeccionesPrmtabHabilitadas($tcSeccion='')
	{
		$this->aSecciones = [];
		global $goDb;
		if(!isset($goDb)) return;

		if ($tcSeccion=='URGENCIAS') {
			$goDb->where('TABDSC LIKE \'%URG%\'');
		}
		$laSecciones = $goDb
			->select('SUBSTR(TRIM(TABCOD),1,5) AS CODPISO, SUBSTR(TRIM(TABDSC),1,30) AS DESCRIP')
			->from('PRMTAB02')
			->where('TABTIP=\'041\' AND TABCOD IN (SELECT CL1TMA FROM TABMAE WHERE TIPTMA=\'SECHAB\' AND ESTTMA=\'\')')
			->orderBy('TABCOD')
			->getAll('array');
		if(is_array($laSecciones)){
			foreach($laSecciones as $laSeccion){
				$laSeccion = array_map('trim', $laSeccion);
				$this->aSecciones[$laSeccion['CODPISO']] = $laSeccion;
			}
		}
	}

	public function consultaSeccionesHospitalizados()
	{
		global $goDb;
		$this->aSecciones = [];
		if(isset($goDb)){
			$laSecciones = $goDb
				->select('TRIM(CL1TMA) CODPISO, TRIM(DE1TMA) DESCRIP')
				->from('TABMAE')
				->where('TIPTMA', '=', 'SECHAB')
				->in('OP2TMA', ['UNIDAD', 'PISOS'])
				->orderBy('CL1TMA')
				->getAll('array');
			if(is_array($laSecciones)){
				foreach($laSecciones as $laSeccion){
					$this->aSecciones[$laSeccion['CODPISO']] = $laSeccion;
				}
			}
		}
	}
	
	public function ubicacionesSeccion($tcSeccion='')
	{
		$this->consultaSeccionesPrmtabHabilitadas($tcSeccion);
		$laResult = [];

		foreach ($this->aSecciones as $lcCodPiso=>$laSeccion) {
			$laHabitaciones = $this->habitacionesSeccion($lcCodPiso);
			if (count($laHabitaciones)>0) {
				// inserta sección
				$laResult[] = [
					'codigo'=>$laSeccion['CODPISO'],
					'descripcion'=>$laSeccion['DESCRIP'],
					'icono'=>'map-marker-alt',//'location',
					'tipo'=>'root',
				];
				// inserta habitaciones
				foreach ($laHabitaciones as $laFila) {
					if ($tcSeccion=='URGENCIAS') {
						switch ($laSeccion['CODPISO']) {
							case 'UC':
								$lcText='Consultorio '.$laFila['NUMHAB'];
								$lcPicture='stethoscope';
								break;
							case 'TU':
								$lcText='Cama '.$laFila['NUMHAB'];
								$lcPicture='bed';
								break;
							default:
								$lcText=strtoupper($laFila['NUMHAB']);
								switch (substr($lcText,0,1)) {
									case 'C':
										$lcText='Camilla '.substr($lcText,1);
										$lcPicture='procedures';//'stretcher';
										break;
									case 'S':
										$lcText='Silla '.substr($lcText,1);
										$lcPicture='chair';
										break;
									case 'R':
										$lcText='Reanimación '.substr($lcText,1);
										$lcPicture='procedures';//'resuscitation-stretcher';
										break;
									case 'P':
										if (substr($lcText,0,2)=='PS') {
											$lcText='Pasillo '.substr($lcText,2);
										} else {
											$lcText='Camilla pediatría '.substr($lcText,1);
											$lcPicture='procedures';//'stretcher';
										}
										break;
								}
						}
					} else {
						$lcText='Cama '.$laFila['NUMHAB'];
						$lcPicture='bed';
					}
					$lcText=$laSeccion['DESCRIP'].' | '.$lcText;
					$laResult[] = [
						'codigo'=>$laFila['CODHABITA'],
						'descripcion'=>$lcText,
						'icono'=>$lcPicture,
						'tipo'=>'node',
					];
				}
			}
		}

		return $laResult;
	}

	public function habitacionesSeccionEstado($tcSeccion='', $tcEstadoHabitacion='0', $tcEstadoRegistro='0' ){
		$laHabitaciones = array();
		
		global $goDb;
		if(!empty($tcSeccion)){
			if(isset($goDb)==true){
				
				$laCampos = ['TRIM(F.NUMHAB) CODIGO', 'TRIM(F.SECHAB) SECCION', 'TRIM(F.SECHAB)||\'-\'||TRIM(F.NUMHAB) NOMBRE'];
				
				$laHabitaciones = $goDb
					->select($laCampos)
					->from('FACHAB F')
					->where(['F.SECHAB'=>$tcSeccion, 'F.ESTHAB'=>$tcEstadoHabitacion, 'F.IDDHAB'=>$tcEstadoRegistro])
					->getAll('array');
			}
		}
		
		return $laHabitaciones;
	}
	
	public function consultaSeccionesMedicos(){
		
		$laHabitaciones = array();
		global $goDb;
		if(isset($goDb)==true){
			$laHabitaciones = $goDb
				->select('TRIM(CL2TMA) CODPISO, TRIM(DE2TMA) DESCRIP')
				->from('TABMAE')
				->where('TIPTMA', '=', 'CENPAC')
				->where('CL1TMA', '=', 'UBIMED')
				->where('ESTTMA', '=', '')
				->orderBy('DE2TMA')
				->getAll('array');
		}
		return $laHabitaciones;
	}

		
}
