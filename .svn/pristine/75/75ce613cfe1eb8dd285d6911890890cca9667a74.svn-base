<?php
namespace NUCLEO;

class Doc_AnestesiaEcoPer
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => 'PROCEDIMIENTOS DE ANESTESIA',
					'lcDesEspMed'=>'',
					'lMostrarFechaRealizado' => true,
					'lMostrarViaCama' => true,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => 'Estudio',
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
		$laDocumento = $this->datosBlanco();
		$laCondiciones = ['INGDET'=>$taData['nIngreso'], 'CUPDET'=>$taData['cCUP'], 'CCIDET'=>$taData['nConsecCita']];
		$laRiaDet = $this->oDb
							->select('FL3DET')
							->from('RIADET')
							->where($laCondiciones)
							->get('array');
		if(is_Array($laRiaDet))
		{
			if(count($laRiaDet) > 0)
			{
				$laDocumento['lcCodEsp'] = trim($laRiaDet['FL3DET'] ?? "");
			}
		}
		$laCampos = ['P.SUBECO', 'P.DSCECO', 'P.FECECO', 'P.USRECO', "IFNULL(M.REGMED, '') AS REGMED", "IFNULL(M.NOMMED, '') AS NOMMED", "IFNULL(M.NNOMED, '') AS NNOMED", "IFNULL(M.CODRGM, '') AS CODRGM", "IFNULL(E.DESESP,'') AS DESESP"];
		$laCondiciones = ['P.INGECO'=>$taData['nIngreso'], 'P.CONECO'=>$taData['nConsecCita'], 'P.PROECO'=>$taData['cCUP'], 'P.INDECO'=>30];
		$laEcosHC =$this->oDb
							->select($laCampos )
							->from('ECOS AS P')
							->leftJoin("RIARGMN4 AS M", "P.USRECO=M.USUARI", null)
							->leftJoin("RIAESPEL01 AS E", "E.CODESP = " . ( empty($laDocumento['lcCodEsp']) ? 'M.CODRGM' : "'".$laDocumento['lcCodEsp']."'" ), null)
							->where($laCondiciones)
							->getAll('array');
		
		if(is_Array($laEcosHC))
		{
			if(count($laEcosHC) > 0)
			{
				if (!is_null($laEcosHC[0]['REGMED']))
				{
					$laDocumento['lcRegMed'] = $laEcosHC[0]['REGMED'];
					$laDocumento['lcNomMed'] = trim($laEcosHC[0]['NOMMED']) . ' ' . trim($laEcosHC[0]['NNOMED']);
					$laDocumento['lcCodEsp'] = empty($laDocumento['lcCodEsp']) ? $laEcosHC[0]['CODRGM'] : $laDocumento['lcCodEsp'];
					$laDocumento['lcDesEsp'] = trim($laEcosHC[0]['DESESP']) ?? ' ';
				}else
				{
					$laDocumento['lcRegMed'] = ' ';
					$laDocumento['lcNomMed'] = ' ';
					$laDocumento['lcCodEsp'] = ' ';
					$laDocumento['lcDesEsp'] = ' ';
				}
				foreach($laEcosHC as $laData)
				{
					$laDocumento['lcDescripcion'] .= $laData['DSCECO'];
				}
			}
		}
		$this->aDocumento = $laDocumento;
	}

	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$cVacios = $this->cTituloVacios;
		
		
		$laTr['aCuerpo'][] = ['titulo1',	'PROCEDIMIENTO REALIZADO', 'L'];
		$laTr['aCuerpo'][] = ['texto9', $taData['cCUP'] . ' - '  . $taData['oCup']->cDscrCup];
		if(!empty($this->aDocumento['lcDescripcion']))
		{
			$laTr['aCuerpo'][] = ['titulo1',	'DESCRIPCIÓN', 'L'];
			$laTr['aCuerpo'][] = ['texto9',	trim($this->aDocumento['lcDescripcion'])];
		}

		//FIRMA

		if(empty($this->aDocumento['lcCodEsp']) || $this->aDocumento['lcCodEsp']== " ")
		{
			$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['lcRegMed'],'prenombre'=>'Dr. ']]];
		}else
		{
			$laTr['aCuerpo'][]=['firmas', [
				['registro' => $this->aDocumento['lcRegMed'],'prenombre'=>'Dr. ', 'codespecialidad' => $this->aDocumento['lcCodEsp']]]];
		}
		$this->aReporte = array_merge($this->aReporte, $laTr);
	}

	private function datosBlanco()
	{
		return [
			'lcCodEsp' => '',
			'lcRegMed' => '',
		  'lcNomMed' => '',
		  'lcDesEsp' => '',
		  'lcDescripcion' => '',
		];
	}
}