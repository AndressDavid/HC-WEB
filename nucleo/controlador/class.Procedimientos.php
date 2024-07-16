<?php
namespace NUCLEO;

class Procedimientos
{
	protected $oDb;
	protected $aInterpretacion;
	protected $aObjetivos = [];
	protected $aUbicacion = [];

	public function __construct($tnIngreso=0)
	{
		global $goDb;
		$this->oDb = $goDb;
		if ($tnIngreso>0){
			$this->CargarProcedimientosPorInterpretar($tnIngreso);
		}
	}

	public function CargarProcedimientosPorInterpretar($tnIngreso=0)
	{
		$lcEspCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
		$laProcedimientos = $this->oDb
			->select([
				'O.TIDORD','O.NIDORD','O.CCOORD','O.CCIORD','O.EVOORD','O.CODORD', 'O.ESTORD',
				'O.ENTORD','O.NINORD','O.FCOORD','O.FRLORD','O.HOCORD','O.RMRORD', 'O.CD2ORD',
				'O.RMEORD','O.FERORD','O.HRLORD','O.INTORD','O.ESCORD','I.TIUING', 'I.FEIING',
				'I.HORING','I.FEEING','I.HREING','I.PLAING','I.NCAING',
				'IFNULL(V.CODVIA,\'\') CODVIA',	'IFNULL(V.DESVIA,\'\') DESVIA',
				'C.CODCUP','C.RF1CUP','C.RF2CUP','C.RF3CUP','C.DESCUP', 'C.PGRCUP',
				'IFNULL(E.DESESP,\'\') DESESP',
				'P.NM1PAC','P.NM2PAC','P.AP1PAC','P.AP2PAC','P.FNAPAC',
				'IFNULL(M.NOMMED,\'\') NOMMED', 'IFNULL(L.DSCCON,\'\') DSCCON',
			])
			->from('RIAORD O')
			->innerJoin('RIAING I', 'O.NINORD=I.NIGING', null)
			->leftJoin('RIAVIA V', 'I.VIAING=V.CODVIA', null)
			->innerJoin('RIACUP C', 'O.COAORD=C.CODCUP', null)
			->leftJoin('RIAESPE E', 'O.CODORD=E.CODESP', null)
			->innerJoin('RIAPAC P', 'O.TIDORD=P.TIDPAC AND O.NIDORD=P.NIDPAC', null)
			->leftJoin('RIARGMN M', 'O.RMRORD=M.REGMED', null)
			->leftJoin('FACPLNC L', 'O.PLAORD=L.PLNCON', null)
			->where(['O.NINORD'=>$tnIngreso, 'O.INTORD'=>0, 'C.RF1CUP'=>'DIAG',])
			->where('O.FRLORD', '>=', 20080728)
			->where('C.RF2CUP', '<>', 'CUID.M')
			->where('C.RF3CUP', '<>', 'TERAPI')
			->in('O.ESTORD', [3, 50, 51, 52, 65, 66, 69])
			->orderBy('O.FERORD, O.HRLORD')
			->getAll('array');


		foreach($laProcedimientos as  $lnKey=>$laDato){

			if($laDato['CODCUP'] !== '872070'){

				// DESCRIPCION ESTADO PROCEDIMIENTO
				$oTabmae= $this->oDb->ObtenerTabMae('DE1TMA', 'ESTPRORD', ['CL1TMA'=>str_pad($laDato['ESTORD'],2,'0',STR_PAD_LEFT), 'ESTTMA'=>'']);
				$lcEstado = trim(AplicacionFunciones::getValue($oTabmae, 'DE1TMA', ''));

				// MEDICO QUE SOLICITA Y ESPECIALIDAD
				$lcCodEsp = $lcEspecialidad = $lcNombreEsp = '';
				$lcRegMed = str_pad($laDato['RMEORD'],13,'0',STR_PAD_LEFT);

				$laTemp = $this->oDb
					->select('CODRGM , NOMMED, NNOMED')
					->from('RIARGMN')
					->where(['REGMED'=>$lcRegMed])
					->get('array');

				if(is_array($laTemp)){
					if(count($laTemp)>0){
						$lcNombreEsp = trim($laTemp['NOMMED']) . ' ' . trim($laTemp['NNOMED']);
						$lcCodEsp = str_pad($laTemp['CODRGM'],3,'0',STR_PAD_LEFT);

						$laTemp = $this->oDb
							->select('DESESP')
							->from('RIAESPE')
							->where(['CODESP'=>$lcCodEsp])
							->get('array');

						$lcEspecialidad = $laTemp['DESESP']??'';
					}
				}

				$lnIndice = 0;
				$lcObserva= '';
				$laTemp = $this->oDb
					->select('IN2RID, DESRID')
					->from('REINDEL01')
					->where([
						'INGRID'=>$tnIngreso,
						'TIPRID'=>'IN',
						'CEXRID'=>$laDato['CCIORD'],
						'INDRID'=>2,
						'ESTRID'=>0,
					])
					->get('array');

				if(is_array($laTemp)){
					if(count($laTemp)>0){
						$lnIndice = $laTemp['IN2RID'];
						$lcObserva= trim($laTemp['DESRID']);
					}
				}

				$laProcedimientos[$lnKey]['OBSJOR'] = $lcObserva;
				$laProcedimientos[$lnKey]['NORJOR'] = ($lnIndice==1? 1: 0);
				$laProcedimientos[$lnKey]['ANOJOR'] = ($lnIndice==2? 1: 0);

			// FALTA DATO PARA VALIDAR INTERPRETACION

				$this->aInterpretacion[] = [
					'NINORD'=>$laDato['NINORD'],
					'TIDORD'=>$laDato['TIDORD'],
					'NIDORD'=>$laDato['NIDORD'],
					'EVOORD'=>$laDato['EVOORD'],
					'RMRORD'=>$laDato['RMRORD'],
					'CODCIT'=>$laDato['CCIORD'],
					'PGRCUP'=>trim($laDato['PGRCUP']),
					'CUPS'=>trim($laDato['CODCUP']),
					'DESCRIPCION'=>trim($laDato['DESCUP']),
					'FECHORD'=>$laDato['FRLORD'],
					'HORAORD'=>$laDato['HOCORD'],
					'VIA'=>$laDato['CODVIA'] . '-'. $laDato['DESVIA'],
					'CAMA'=>'',
					'CODESTADO'=>$laDato['ESTORD'],
					'ESTADO'=>$lcEstado,
					'NORMAL'=>($lnIndice==1? 1: 0),
					'ANORMAL'=>($lnIndice==2? 1: 0),
					'OBSERVA'=>$lcObserva,
					'OBLIGATORIO'=>($laDato['INTORD']=='0'? 'SI':'NO'),
					'CODESP'=>$laDato['CODORD'],
					'ESPSOL'=>$lcCodEsp,
					'ESPSOLICITA'=>$lcEspecialidad,
					'MEDICOSOL'=>$lcNombreEsp,
					'FECHAREA'=>$laDato['FERORD'],
					'HORAREA'=>$laDato['HRLORD'],
					'MEDACT'=>$lcEspCre,
				];
			}
		}
	}

	public function obtenerUbicacion()
	{
		$this->aUbicacion = [];
		$laParams = $this->oDb
			->select('TABDSC AS DESUBI, TABCOD AS CODUBI')
			->from('PRMTAB02')
			->where(['TABTIP'=>'UBI'])
			->getAll('array');

		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aUbicacion[$laPar['CODUBI']] = [
					'desc'=>trim($laPar['DESUBI']),
				];
			}
		}
	}

	public function obtenerObjetivos()
	{
		$this->aObjetivos = [];
		$laParams = $this->oDb
			->select('TABDSC AS DESOBJ, TABCOD AS CODOBJ')
			->from('PRMTAB02')
			->where(['TABTIP'=>'NPO'])
			->getAll('array');

		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aObjetivos[$laPar['CODOBJ']] = [
					'desc'=>trim($laPar['DESOBJ']),
				];
			}
		}
	}

	public function consultaListaProcedimientos($taDescripcion='', $tcCodigo='', $tlIncluirTodosLosEstados=false, $tcDatos='')
	{
		$laProcedimientos=[];
		$loDatos = json_decode($tcDatos);
		$tcSexo = $loDatos->genero;
		$tcEdad = $loDatos->edad;
		$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DATCUP', ['CL1TMA'=>'GENCUP', 'CL2TMA'=>$tcSexo, 'ESTTMA'=>' ']);
		$lcFiltroSexo = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
		
		if ($taDescripcion!=''){
			if(is_array($taDescripcion)==false){
				$taDescripcion = array(trim(strval($taDescripcion)));
			}
			$tcCodigo = trim(strval($tcCodigo));
			$tcCodigo = mb_strtoupper(!empty($tcCodigo)?'%'.$tcCodigo.'%':'');

			$laCampos = [
				"trim(DESCUP) DESCRIPCION",
				'trim(CODCUP) CODIGO',
				'IDDCUP ESTADO',
				'trim(RF1CUP) REFERENCIA1',
				'trim(RF2CUP) REFERENCIA2',
				'trim(RF3CUP) REFERENCIA3',
				'trim(RF4CUP) REFERENCIA4',
				'trim(RF5CUP) POSNOPOS',
				'TRIM(ESPCUP) ESPECIALIDAD',
				'TRIM(CLBCUP) HEXALIS',
				'TRIM(PGRCUP) PAQLAB',
				'CADCUP TIPORIPS'
			];
			$lcWhere='';
			if(count($taDescripcion)>0){
				$lcWhereAux = '';
				foreach($taDescripcion as $lcNombre){
					if(!empty($lcNombre) && $lcNombre!=='*'){
						$lcNombre = mb_strtoupper('%'.trim($lcNombre).'%');
						$lcWhereAux.= (empty($lcWhereAux)?'':' AND '). sprintf("( (TRANSLATE(UPPER(DESCUP),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')) OR (TRANSLATE(UPPER(CODCUP),'AEIOU','ÁÉÍÓÚ') LIKE TRANSLATE('%s','AEIOU','ÁÉÍÓÚ')))", $lcNombre, $lcNombre);
					}
				}
				$lcWhere= (!empty($lcWhereAux)? $lcWhereAux :'');
			}
			$lcWhere.= (!empty($tcCodigo)?sprintf(" AND (CODCUP LIKE '%s')", $tcCodigo):'');
			$lcWhere.= ($tlIncluirTodosLosEstados==true ?'' : (empty($lcWhere)? "IDDCUP='0'" :" AND IDDCUP='0'"));
			$lcWhere.= (!empty($tcSexo)? "AND (TRIM(IFNULL(B.GENCUA, '')) IN($lcFiltroSexo))" : $lcWhere);
			
			$lcOrden='DESCUP';
			$laProcedimientos = $this->oDb
				->select($laCampos)
				->select('(SELECT TRIM(C.CL2TMA) FROM TABMAE AS C WHERE C.TIPTMA=\'LABORTR\' AND C.CL1TMA=\'LABMCRB\' AND C.CL2TMA=CODCUP AND C.ESTTMA=\' \'  FETCH FIRST 1 ROWS ONLY) AS LABESPEC')
				->from('RIACUP')
				->leftJoin('RIACUPA AS B', 'trim(CODCUP)=trim(B.CODCUA)', null)
				->where($lcWhere)
				->orderBy($lcOrden)
				->getAll('array');
					
		}
		return $laProcedimientos;
	}

	public function consultaProcedimientoPos($tcProcedimiento='')
	{
		$lcCupsPos = '';
		$laParametros = $this->oDb
			->select('trim(CODCUP) CODIGO')
			->from('RIACUP')
			->where('CODCUP', '=', $tcProcedimiento)
			->where('IDDCUP', '=', '0')
			->where('RF5CUP', '<>', 'NOPB')
			->get('array');
		if (is_array($laParametros) && count($laParametros)>0){
			$lcCupsPos = $laParametros['CODIGO'];
		}
		return $lcCupsPos;
	}

	public function GetProcedimientosCardiopulmonares(){
		return $this->oDb
		->select('CL3TMA,DE2TMA')
		->where(
			[
				'TIPTMA'=>"PROCORD",
				'OP1TMA' => "1"
			]
			)
		->from('tabmae')
		->getAll('array');
	}


	function getDatosInterpretacion(){
		return $this->aInterpretacion;
	}

	public function getObjetivos()
	{
		return $this->aObjetivos;
	}

	public function getUbicaciones()
	{
		return $this->aUbicacion;
	}

	public function getObjetivo($tcIndice='')
	{
		return $this->aObjetivos[$tcIndice] ?? '';
	}

	public function getUbicacion($tcIndice='')
	{
		return $this->aUbicacion[$tcIndice] ?? '';
	}

}
