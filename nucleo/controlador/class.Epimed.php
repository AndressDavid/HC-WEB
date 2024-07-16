<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Epimed
{
	public $aConsultaPaciente = [];
	public $aConsultaEpimed = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function consultaEpimed()
	{
		$laCampos = [
					"A.CONEPI CONSECUTIVO","A.INGEPI INGRESO","TRIM(A.ACCEPI) ACCION","B.TIDING TIPO_IDE", "B.NIDING NRO_IDE", "C.SEXPAC GENERO", 
					"C.FNAPAC FECHA_NACIMIENTO", "B.FEIING FECHA_INGRESO", "B.FEEING FECHA_EGRESO", "B.HREING HORA_EGRESO",
					"A.FENEPI FECHA_ENTRADA", "A.HENEPI HORA_ENTRADA", "A.FSAEPI FECHA_SALIDA", "A.HSAEPI HORA_SALIDA",
					"SUBSTR(TRIM(A.HABEPI), 1, 2) SECCION","TRIM(SUBSTR(TRIM(A.HABEPI), 4, 5)) HABITACION", "TRIM(A.MOTEPI) MOTIVO_SALIDA",
					"TRIM(D.DE2TMA) HOM_TIPOIDE",
					"TRIM(C.NM1PAC) || ' ' || TRIM(C.NM2PAC) || ' ' || TRIM(C.AP1PAC) || ' ' || TRIM(C.AP2PAC) NOMBRE_PACIENTE"
					];
		$laEpimed = $this->oDb
			->select($laCampos)
			->from('EPIMED AS A')
			->where('A.ESTEPI', '=', ' ')
			->leftJoin('RIAING AS B', 'B.NIGING=A.INGEPI', null)
			->leftJoin('RIAPAC AS C', 'C.TIDPAC=B.TIDING AND C.NIDPAC=B.NIDING', null)
			->leftJoin("TABMAE AS D", "B.TIDING=D.CL2TMA AND D.TIPTMA='EPIMED' AND D.CL1TMA='TIPIDE'", null)
			->getAll('array');
		if(is_array($laEpimed)==true){
			$this->aConsultaPaciente = $laEpimed;
		}
		unset($laEpimed);
		return $this->aConsultaPaciente;		
	}
	
	public function actualizaEpimed($tnConsecutivo){
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$lcFecha = $ltAhora->format("Ymd");
		$lcHora  = $ltAhora->format("His");
		$lcTabla="EPIMED";
		$lnCantidadEnvios = 0;
		$llResultado = false;
		
		$laRegistro = $this->oDb->select("ENVEPI")
						->tabla($lcTabla)
						->where('CONEPI', '=', $tnConsecutivo)
						->get('array');
		if(isset($laRegistro)==true){
			if(is_array($laRegistro)==true){
				$lnCantidadEnvios = intval($laRegistro['ENVEPI']) + 1;
				
				$laDatos = ['ESTEPI'=>'L',
							'ENVEPI'=>$lnCantidadEnvios,
							'UMOEPI'=>'SERVIDOR',
							'PMOEPI'=>substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10),
							'FMOEPI'=>$lcFecha,
							'HMOEPI'=>$lcHora
							];
				$llResultado = $this->oDb->tabla($lcTabla)->where('CONEPI', '=', $tnConsecutivo)->actualizar($laDatos);	
			}
		}	
		unset($laRegistro);
		return $llResultado;
	}
}
