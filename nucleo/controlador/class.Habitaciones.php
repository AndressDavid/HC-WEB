<?php

namespace NUCLEO;

require_once ('class.Db.php');

use NUCLEO\Db;

class Habitaciones
{
    public function __construct()
	{
	}

	public function listarHabitaciones()
	{
		global $goDb;
		if(isset($goDb)){
			$laHabitaciones = $goDb
				->from('FACHAB')
				->getAll('array');
			if ($goDb->numRows()>0) {
				return $laHabitaciones;
			}
		}
		return [];
	}

	public function buscarHabitacionPorIP(string $tcIP)
	{
		global $goDb;
		if(isset($goDb)){
			$laHabitacion = $goDb
				->select('IPH.IP_DISPOSITIVO, IPH.MAC_DISPOSITIVO, IPH.SECCION, IPH.HABITACION, HAB.INGHAB INGRESO, HAB.ESTHAB ESTADO_HAB')
				->from('IP_HABITACIONES IPH')
				->innerJoin('FACHAB HAB', 'IPH.SECCION=HAB.SECHAB AND IPH.HABITACION=HAB.NUMHAB')
				->where(['IPH.ACTIVO'=>1, 'IPH.IP_DISPOSITIVO'=>$tcIP])
				->get('array');
			if ($goDb->numRows()>0) {
				return $laHabitacion;
			}
		}
		return [];
	}
}
