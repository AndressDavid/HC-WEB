<?php
namespace NUCLEO;

class Doc_NotasPaciente
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aReporte = [
					'cTitulo' => 'NOTA ACLARATORIA',
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aNotas' => ['notas'=>false,],
				];


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
		$laDoc = ['ttl'=>'', 'dsc'=>'', 'med'=>'', 'esp'=>'', 'usu'=>'', 'fec'=>'', 'hor'=>''];
		$laNota = $this->oDb
			->select('CONNOT,CNLNOT,DESNOT,OP2NOT,OP5NOT,USRNOT,FECNOT,HORNOT')
			->from('NOTACLL01')
			->where(['TIDNOT'=>$taData['cTipDocPac'], 'IDENOT'=>$taData['nNumDocPac'], 'CONNOT'=>$taData['nConsecDoc']])
			->orderBy('CNLNOT')
			->getAll('array');

		if(is_array($laNota))
			if(count($laNota)>0) {
				foreach ($laNota as $lnIndice => $laFila) {
					//if ($lnIndice===0) {
					if (intval($laFila['CNLNOT'])==1) {
						$laDoc['ttl'] = trim($laFila['DESNOT']);
						$laDoc['med'] = trim($laFila['OP5NOT']);
						$laDoc['esp'] = trim($laFila['OP2NOT']);
						$laDoc['usu'] = trim($laFila['USRNOT']);
						$laDoc['fec'] = trim($laFila['FECNOT']);
						$laDoc['hor'] = trim($laFila['HORNOT']);
					} else {
						$laDoc['dsc'] .= $laFila['DESNOT'];
					}
				}
			}

		$this->aDocumento = $laDoc;
	}


	private function prepararInforme($taData)
	{
		$lnAnchoPagina = 90;
		$lcSL = PHP_EOL;
		$laTr = [];

		if(!empty($this->aDocumento['ttl']))
		{
			$laTr[] = ['titulo1', $this->aDocumento['ttl']];
			$laTr[] = ['texto9', $this->aDocumento['dsc']];

			$laFirma = ['usuario'=>$this->aDocumento['usu']];

			if (empty($this->aDocumento['med'])) {
				if (!empty($this->aDocumento['esp'])) { $laFirma['codespecialidad'] = $this->aDocumento['esp']; }
			} else {
				//$laFirma = ['texto_firma'=>str_replace(' - ', PHP_EOL, $this->aDocumento['med'])];
				$laFirma['especialidad'] = mb_substr($this->aDocumento['med'], mb_strrpos($this->aDocumento['med'], ' - ')+3);
			}

			$laTr[] = ['firmas', [ $laFirma ]];
		}

		$this->aReporte['aCuerpo'] = $laTr;
	}

}