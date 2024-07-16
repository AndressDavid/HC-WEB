<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class FormulacionParametros
{
	protected $aMedicamentos = [];
	protected $aMedicamento = [];
	protected $aMedicamentoNOPOS = [];
	protected $cListaMedicaNOPOS = '';
	protected $aUnidadesDosis = [];
	protected $aFrecuencias = [];
	protected $aViasAdmin = [];
	protected $aNoConsume = [];
	protected $aRiesgoCTC = [];
	protected $oDb;
	protected $cNombreMedicamento='';

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	/*
	 *	Obtiene todos los parámetros
	 */
	public function obtenerParametrosTodos()
	{
		$this->obtenerUnidadesDosis();
		$this->obtenerFrecuencias();
		$this->obtenerViasAdmin();
		$this->obtenerNoConsume();
	}

	/*
	 *	Unidades de dosificación
	 */
	public function obtenerUnidadesDosis()
	{
		$laParams = $this->oDb
			->select('SUBSTR(CL2TMA,1,2) AS CODDO2, SUBSTR(DE1TMA,1,40) AS DESDO2, SUBSTR(DE2TMA,1,40) AS ABRDO2')
			->from('TABMAE')
			->where('TIPTMA=\'MEDDOS\' AND OP1TMA=\'S\' AND ESTTMA<>\'1\'')
			->orderBy('UPPER(DE1TMA)')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aUnidadesDosis[$laPar['CODDO2']] = [
					'desc'=>$laPar['DESDO2'],
					'abrv'=>$laPar['ABRDO2'],
				];
			}
		}
	}

	/*
	 *	Frecuencias
	 */
	public function obtenerFrecuencias()
	{
		$laParams = $this->oDb
			->select('CL2TMA CODFRE, DE1TMA DESFRE, CL3TMA ESTFRE, INT(CL4TMA) UNIFRE, SUBSTR(DE2TMA,1,40) ABRFRE, OP1TMA OPCIONAL1, OP3TMA OPCIONAL3')
			->from('TABMAE')
			->where('TIPTMA=\'MEDFRE\' AND ESTTMA<>\'1\'')
			->orderBy('DE1TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aFrecuencias[$laPar['CODFRE']] = [
					'desc'=>$laPar['DESFRE'],
					'abrv'=>$laPar['ABRFRE'],
					'unidad'=>$laPar['UNIFRE'],
					'estado'=>$laPar['ESTFRE'],
					'codigo'=>$laPar['CODFRE'],
					'opcional1'=>$laPar['OPCIONAL1'],
					'opcional3'=>$laPar['OPCIONAL3'],
				];
			}
		}
	}

	/*
	 *	Vías de administración
	 */
	public function obtenerViasAdmin()
	{
		$laParams = $this->oDb
			->select('SUBSTR(CL1TMA,1,2) AS CODVAD, DE1TMA AS DESVAD')
			->from('TABMAE')
			->where('TIPTMA=\'MEDVAD\' AND ESTTMA<>\'1\'')
			->orderBy('DE1TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aViasAdmin['0'.$laPar['CODVAD']] = $laPar['DESVAD'];
			}
		}
	}

	/*
	 *	Obtiene un dato de una unidad de dosis
	 *	@param string $tcIndice : indice del que se desea obtener el dato
	 *	@param string $tcDato : clave del dato a obtener (desc o abrv)
	 */
	public function unidadDosis($tcIndice='', $tcDato='desc')
	{
		return $this->aUnidadesDosis[$tcIndice][$tcDato] ?? '';
	}

	/*
	 *	Obtiene un dato de una frecuencia
	 *	@param string $tcIndice : indice del que se desea obtener el dato
	 *	@param string $tcDato : clave del dato a obtener (desc, abrv, unidad o estado)
	 */
	public function Frecuencia($tcIndice='', $tcDato='desc')
	{
		return $this->aFrecuencias[$tcIndice][$tcDato] ?? '';
	}

	/*
	 *	Obtiene descripción de una vía de administración
	 *	@param string $tcIndice : indice
	 */
	public function viaAdmin($tcIndice='')
	{
		return $this->aViasAdmin['0'.$tcIndice] ?? '';
	}

	/*
	 *	Retorna array con las unidades de dosificación
	 */
	public function unidadesDosis()
	{
		return $this->aUnidadesDosis;
	}

	/*
	 *	Retorna array con las frecuencias
	 */
	public function frecuencias()
	{
		return $this->aFrecuencias;
	}

	/*
	 *	Retorna array con las vías de administración
	 */
	public function viasAdmin()
	{
		return $this->aViasAdmin;
	}

	public function obtenerMedicamentos()
	{
		$this->aMedicamentos = $this->oDb
			->select('A.DESDES AS DesMed, TRIM(A.REFDES) AS CodMed, A.UNCDES AS UniMed, TRIM(A.RFMDES) AS CmiMed, 0 AS EstMed, A.RF4DES AS PosMed, A.RF5DES AS CtrMed')
			->from('INVDES AS A')
			->leftJoin('INVMEDA AS B', 'A.REFDES=B.CODIGO', null)
			->where("A.TINDES='500' OR A.RF2DES='NUCLEA'")
			->orderBy('A.DESDES')
			->getAll('array');
	}

	public function ListaMedicamentos()
	{
		return $this->aMedicamentos;
	}

	public function BuscarMedicamento($tcCodigo='')
	{
		$this->cNombreMedicamento = '';
		if(!empty($tcCodigo)){
			$this->aMedicamento = $this->oDb
				->select('A.DESDES AS DESMED, B.*')
				->from('INVDES AS A')
				->leftJoin('INVMEDA AS B', 'A.REFDES=B.CODIGO')
				->where(['A.REFDES'=>$tcCodigo])
				->get('array');
			
			if(!is_array($this->aMedicamento)){$this->aMedicamento=[];}
			if(isset($this->aMedicamento['DESMED'])){
				$this->aMedicamento = array_map('trim',$this->aMedicamento);
				$this->cNombreMedicamento = $this->aMedicamento['DESMED'];
			}
		}

		return $this->cNombreMedicamento;
	}
		public function EstadoMedicamento($tcCodigo='')
	{
		$laMedicamentos = [];
		if(!empty($tcCodigo)){
			$laMedicamentos = $this->oDb
				->select('TRIM(STSDES) ESTADO, TRIM(RF4DES) POSNOPOS')
				->from('INVDES')
				->where(['REFDES'=>$tcCodigo])
				->get('array');
		}
		return $laMedicamentos;
	}
	
	public function ControladoMedicamento($tcCodigo='')
	{
		$laMedicamentos = [];
		if(!empty($tcCodigo)){
			$laMedicamentos = $this->oDb
				->select('TRIM(CL03DES) CONTROLADO')
				->from('INVATTR')
				->where(['REFDES'=>$tcCodigo])
				->get('array');
		}
		return $laMedicamentos;
	}
	
	public function unirsMedicamento($tcCodigo='')
	{
		$laMedicamentoUnirs = [];
		if(!empty($tcCodigo)){
			$laMedicamentoUnirs = $this->oDb
				->select('TRIM(CL19DES) ESMEDICAMENTOUNIRS')
				->from('INVATTR')
				->where(['REFDES'=>$tcCodigo])
				->get('array');
		}
		return $laMedicamentoUnirs;
	}
	
	public function DatosMedicamento()
	{
		return $this->aMedicamento;
	}

	public function DatosMedicamentoNOPOS($tcCodigo='')
	{
		if(!empty($tcCodigo)){
			$lcNomMedica = $this->BuscarMedicamento($tcCodigo);
			if(!empty(trim($lcNomMedica))){
				$this->aMedicamentoNOPOS = $this->oDb
					->from('RIAJUSA')
					->where(['CODJUS'=>$tcCodigo])
					->get('array');
			}
			$this->aMedicamentoNOPOS = is_array($this->aMedicamentoNOPOS)? array_map('trim',$this->aMedicamentoNOPOS): [];
		}
		$this->aMedicamentoNOPOS = array_merge($this->aMedicamentoNOPOS, $this->aMedicamento);

		return $this->aMedicamentoNOPOS;
	}

	public function ListaMedicamentosNOPOS()
	{
		$laListado = $this->oDb
		->select('TRIM(REFDES) AS CODIGO')
			->from('INVDES')
			->where(['TRIM(RF4DES)'=>'NOPOS'])
			->getAll('array');
		if(is_array($laListado)){
			if(count($laListado)>0){
				$lcSep='';
				foreach($laListado as $laLista){
					$this->cListaMedicaNOPOS .= $lcSep . trim($laLista['CODIGO']);
					$lcSep=',';
				}
			}
		}
		return $this->cListaMedicaNOPOS;
	}

	public function ObtenerRiesgoCTC()
	{
		$laParams = $this->oDb
			->select('TRIM(CL2TMA) AS CODRIE, DE2TMA AS DESRIE')
			->from('TABMAE')
			->where('TIPTMA=\'NOPOS\' AND CL1TMA=\'RIESINM\' AND ESTTMA<>\'1\'')
			->orderBy('CL2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aRiesgoCTC[$laPar['CODRIE']] = $laPar['DESRIE'];
			}
		}
		return $this->aRiesgoCTC;
	}

	public function riesgoInminente($tcIndice='')
	{
		return $this->aRiesgoCTC[$tcIndice] ?? '';
	}

	public function obtenerNoConsume()
	{
		$laParams = $this->oDb
			->select('TRIM(DE2TMA) DESCRIP, TRIM(CL2TMA) AS CODIGO')
			->from('TABMAE')
			->where('TIPTMA=\'CONCILIA\' AND CL1TMA=\'NCONSUME\' AND ESTTMA<>\'1\'')
			->orderBy('CL1TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$this->aNoConsume[$laPar['CODIGO']] = $laPar['DESCRIP'];
			}
		}
	}

	public function NoConsume()
	{
		return $this->aNoConsume;
	}
}
