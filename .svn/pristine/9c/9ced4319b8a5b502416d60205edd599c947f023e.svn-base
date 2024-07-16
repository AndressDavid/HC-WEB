<?php
namespace NUCLEO;

class Doc_Anestesia
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
		'cTitulo' => 'NOTAS ANESTESIA',
		'lcDesEspMed'=>'',
		'lMostrarFechaRealizado' => true,
		'lMostrarViaCama' => true,
		'cTxtAntesDeCup' => '',
		'cTituloCup' => '',
		'cTxtLuegoDeCup' => '',
		'aCuerpo' => [],
		'aFirmas' => [],
		'aNotas' => ['notas'=>true,],
	];
	protected $lTituloVacios=false;
	protected $cTituloVacios='';


	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}


	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);
		return $this->aReporte;
	}


	private function consultarDatos($taData)
	{
		$laDocumento = [
			'lcCodEsp' => '',
			'lcRegMed' => '',
			'lcNomMed' => '',
			'lcDesEsp' => '',
			'lcDescripcion' => '',
			'lcTitulo' => '',
			'lcTexto' => '',
		];
		$laNota=explode('-',$taData['nConsecDoc']);
		$laTabla = $this->oDb
			->select("A.CNLRAN, A.DESRAN, A.FECRAN, A.HORRAN, IFNULL(E.DESESP,'') DESESP, IFNULL(T.DE1TMA,'') DE1TMA")
			->select("IFNULL(M.REGMED,'') REGMED, IFNULL(M.NOMMED,'') NOMMED, IFNULL(M.NNOMED,'') NNOMED, IFNULL(M.CODRGM,'') CODRGM")
			->from('REGANEL01 AS A')
			->leftJoin('TABMAE AS T', "T.TIPTMA='ANSIA' AND T.CL1TMA='CNFNOTAS' AND A.TIPRAN=T.OP2TMA", null)
			->leftJoin('RIARGMN4 AS M', 'A.USRRAN=M.USUARI', null)
			->leftJoin('RIAESPEL01 AS E', 'E.CODESP=M.CODRGM', null)
			->where([
				'INGRAN'=>$taData['nIngreso'],
				'TIPRAN'=>$laNota[0],
				'CONRAN'=>$laNota[1],
			])
			->orderBy('A.CNLRAN')
			->getAll('array');
		if($this->oDb->numRows()>0)
		{
			$laDocumento['lcRegMed'] = $laTabla[0]['REGMED'];
			$laDocumento['lcNomMed'] = trim($laTabla[0]['NOMMED']) . ' ' . trim($laTabla[0]['NNOMED']);
			$laDocumento['lcCodEsp'] = $laTabla[0]['CODRGM'];
			$laDocumento['lcDesEsp'] = $laTabla[0]['DESESP'];
			$laDocumento['lcDescripcion'] = mb_strtoupper($laTabla[0]['DE1TMA'],'UTF-8');
			foreach($laTabla as $laFila)
			{
				$lcClave = $laFila['CNLRAN']=='1'?'lcTitulo':'lcTexto';
				$laDocumento[$lcClave] .= $laFila['DESRAN'];
			}
		}
		$this->aDocumento = array_map('trim',$laDocumento);
	}


	private function prepararInforme($taData)
	{
		$this->aReporte['cTitulo'] = $this->aDocumento['lcDescripcion'];
		$laTr=[];

		$laTr[] = ['titulo1', $this->aDocumento['lcTitulo']];
		$laTr[] = ['texto9', $this->aDocumento['lcTexto']];

		//FIRMA
		$laFirma=['registro' => $this->aDocumento['lcRegMed'],'prenombre'=>'Dr. '];
		if(!empty($this->aDocumento['lcCodEsp'])){
			$laFirma['codespecialidad']=$this->aDocumento['lcCodEsp'];
		}
		$laTr[]=['firmas', [$laFirma]];

		$this->aReporte['aCuerpo'] = $laTr;
	}

}