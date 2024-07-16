<?php
namespace NUCLEO;

class Triage {

    public function __construct (){
	}


	public function obtenerTriageClasificacion($tnIngreso=0){
		$tnIngreso = intval($tnIngreso);
		$laClasificacion = ['CLASIFICACION'=>''];

		if(!empty($tnIngreso)){
			global $goDb;
			if(isset($goDb)){
				$laClasificacionAux = $goDb
					->select('TRIM(T.CLMTRI) CLASIFIACION')
					->from('TRIAGUL01 T')
					->where('T.NIGTRI', '=', $tnIngreso)
					->get('array');

				if(is_array($laClasificacionAux)==true){
					$laClasificacion = $laClasificacionAux;
				}
			}
		}
		return $laClasificacion;
	}

	public function obtenerTriageEnfermedad($tnIngreso=0){
		$tnIngreso = intval($tnIngreso);
		$laEnfermedad = ['ENFERMEDAD'=>''];

		if(!empty($tnIngreso)){
			global $goDb;
			if(isset($goDb)){
				$laEnfermedadAux = $goDb
					->select('TRIM(H.DESHTR) ENFERMEDAD')
					->from('HISTRI H')
					->where('H.INGHTR', '>', $tnIngreso)
					->where('H.INDHTR', '=', 10)
					->where('H.IN2HTR', '=', 0)
					->get('array');

				if(is_array($laEnfermedadAux)==true){
					$laEnfermedad = $laEnfermedadAux;
				}
			}
		}
		return $laEnfermedad;
	}

	public function listaClasificacionesTriage(){
		$laClasificaciones = array();

		global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(SUBSTR(T.CL2TMA,1,2)) CODIGO', 'TRIM(SUBSTR(T.DE1TMA,1,40)) NOMBRE', 'T.OP3TMA TIEMPO', 'T.OP4TMA COLOR'];
			$laClasificaciones = $goDb
				->select($laCampos)
				->from('TABMAEL01 T')
				->where('T.TIPTMA', '=', 'DATING')
				->where('T.CL1TMA', '=', '7')
				->where('T.ESTTMA', '=', '')
				->getAll('array');
		}

		return $laClasificaciones;
	}

}
?>