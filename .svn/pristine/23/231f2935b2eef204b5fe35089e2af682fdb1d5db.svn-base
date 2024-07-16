<?php

namespace NUCLEO;

require_once ('class.Db.php') ;

use NUCLEO\Db;

class Nutricion
{
	private $cPrograma = '';
	private $lEdadConFechaIngreso = false;

    public function __construct() {
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
	}

	public function obtenerEstados(){
		$laEstados = array();
		/*global $goDb;
		if(isset($goDb)){
			$laCampos = ['TRIM(A.CL3TMA) CODIGO','TRIM(A.DE1TMA) DESCRIPCION'];
			$laEstadosAux = $goDb
							->select($laCampos)
							->from('TABMAE A')
							->where("A.TIPTMA='NUTRI' AND A.CL1TMA='ESATDO'")
							->orderBy('TRIM(A.DE1TMA) ASC')
							->getAll('array');
			if(is_array($laEstadosAux)==true){
				foreach($laEstadosAux as $laEstado){
					$laEstados[] = $laEstado;
				}
			}
		}*/
		$laEstados = [
						['CODIGO'=>'3', 'DESCRIPCION'=>'AZUL'],
						['CODIGO'=>'8', 'DESCRIPCION'=>'ROSADO'],
					 ];
		return $laEstados;
	}

	public function buscar($tnIngreso=0, $tnInicio=0, $tnFin=0, $tcEstado=''){
		global $goDb;
		$tcEstado = strtoupper(trim(strval($tcEstado)));
		$laRegistros = $laRegistrosAux = array();

		if (isset($goDb)) {

			$lcWhere = sprintf(" (N.FECNTR>=%s AND N.FECNTR<=%s)", $tnInicio, $tnFin);
			if(!empty($tnIngreso)){ $lcWhere .= sprintf(' AND N.NINNTR=%s',$tnIngreso); }
			if(!empty($tcEstado) && $tcEstado!='TODOS'){ $lcWhere .= sprintf(" AND N.ESTNTR='%s'",trim($tcEstado)); }

			$laCampos = ['P.TIDPAC DOCUMENTO_TIPO',
						'P.NIDPAC DOCUMENTO',
						"TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE",
						'P.FNAPAC FECHA_NACIMIENTO',
						"IFNULL(TRIM(Y.DE2TMA), '') GENERO",
						'P.SEXPAC CODGENERO',
						'N.NINNTR INGRESO',
						'N.FECNTR FECHA_CREACION',
						'N.HORNTR HORA_CREACION',
						'N.RMENTR REGISTRO_MEDICO',
						'N.SCANTR SECCION',
						'N.NCANTR HABITACION',
						'N.CONNTR CONSECUTIVO_NUTRICION',
						'N.ESTNTR ESTADO_NUTRICION',
						"TRIM(TRIM(IFNULL(R.NOMMED,'')) || ' ' || TRIM(IFNULL(R.NNOMED,''))) MEDICO",
						'I.VIAING CODIGO_VIA',
						'V.DESVIA DESCRIPCION_VIA',
						'I.FEIING FECHA_INGRESO',
						'I.ENTING ENTIDAD',
						"IFNULL(TRIM(F.DSCCON), '') PLAN",
						'E.CODESP CODIGO_ESPECIALIDAD',
						'E.DESESP ESPECIALIDAD',
						"IFNULL(H.SECHAB,'') SECCION_ACTUAL",
						"IFNULL(H.NUMHAB,'') HABITACION_ACTUAL",
						"IFNULL(H.INGHAB,0) INGRESO_HABITACION",
						"IFNULL(H.ESTHAB,'') CODIGO_ESTADO_HABITACION",
						"IFNULL(Z.DE1TMA,'-') ESTADO_HABITACION",
						];

			$laRegistrosAux = $goDb
						->select($laCampos)
						->from('RIANUTR N')
						->leftJoin('RIAING I', 'N.NINNTR=I.NIGING', null)
						->leftJoin('RIAINGT D', 'I.NIGING=D.NIGINT', null)
						->leftJoin('RIARGMN R', 'DIGITS(D.MEDINT)=R.REGMED', null)
						->leftJoin('RIAESPE E', 'R.CODRGM=E.CODESP', null)
						->leftJoin('RIAPAC P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
						->leftJoin('RIAVIA V', 'I.VIAING=V.CODVIA', null)
						->leftJoin('FACHAB H', 'H.INGHAB=N.NINNTR', null)
						->leftJoin('FACPLNC F', 'I.PLAING=F.PLNCON', null)
						->leftJoin('TABMAE Y', "P.SEXPAC=Y.CL1TMA AND Y.TIPTMA='SEXPAC'", null)
						->leftJoin('TABMAE Z', "Z.TIPTMA ='ESTHABI' AND Z.CL4TMA=H.ESTHAB", null)
						->where($lcWhere)
						->orderBy('V.DESVIA, N.FECNTR ASC, N.HORNTR')
						->getAll('array');

			if (is_array($laRegistrosAux)) {
				if (count($laRegistrosAux)>0) {
					$ldFechaActual = date_create(date('Y-m-d'));
					foreach($laRegistrosAux as $laFila){
						if ($this->lEdadConFechaIngreso) {
							$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NACIMIENTO']), date_create($laFila['FECHA_INGRESO'])))->format('%y-%m-%d');
						} else {
							$laFila['EDAD'] = (date_diff(date_create($laFila['FECHA_NACIMIENTO']), $ldFechaActual))->format('%y-%m-%d');
						}
						$laRegistros[] = $laFila;
					}
				}
				unset($laRegistrosAux);
			}

		}

		return $laRegistros;
	}


}
?>