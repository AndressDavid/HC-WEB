<?php
namespace NUCLEO;

class Doc_NIHSS
{
	protected $oDb;
	protected $aDocumento = [];
	protected $aNIHSSP = [];
	protected $aNIHSSR = [];
	protected $nTotalNIHSS='';
	protected $aErrorN = [
					'Mensaje' => '',
					'Objeto' => '',
					'Valido' => true,
				];
	protected $aReporte = [
					'cTitulo' => 'ESCALA NIHSS',
					'lMostrarFechaRealizado' => false,
					'lMostrarViaCama' => false,
					'cTxtAntesDeCup' => '',
					'cTituloCup' => '',
					'cTxtLuegoDeCup' => '',
					'aCuerpo' => [],
					'aFirmas' => [],
					'aNotas' => [],
				];
	protected $cRealizadoPor = '';


	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
	}


	/*	Retornar array con los datos del documento */
	public function retornarDocumento($taData)
	{
		$this->consultarDatos($taData);
		$this->prepararInforme($taData);

		return $this->aReporte;
	}


	/*	Consulta los datos del documento desde la BD en el array $aDocumento */
	private function consultarDatos($taData)
	{
		$this->nTotalNIHSS = 0 ;
		$this->CargarNihss();
		$laDoc=explode('-',$taData['nConsecDoc']);

		/* Array de Respuestas registradas */
		$laNIHSS = $this->oDb
			->select('E.DESENI')
			->select('IFNULL(M.REGMED,\'\') AS REGMED, IFNULL(M.NOMMED,\'\') AS NOMMED, IFNULL(M.NNOMED,\'\') AS NNOMED')
			->from('ESCNIH AS E')
			->leftJoin('RIARGMN AS M', 'E.USRENI = M.USUARI')
			->where([
				'E.INGENI'=>$taData['nIngreso'],
				'E.TIPENI'=>$laDoc[0],
				'E.CCOENI'=>$laDoc[1],
				'E.INDENI'=>1,
			])
			->orderBy('IN2ENI')
			->getAll('array');

		$this->cRealizadoPor = $laNIHSS[0]['NOMMED'].' '.$laNIHSS[0]['NNOMED'].' - RM.'.$laNIHSS[0]['REGMED'];

		foreach($laNIHSS as $laNS) {
			$lcCodCL2TMA = trim(substr($laNS['DESENI'],10,10)) ;
			$lcCodCL3TMA = trim(substr($laNS['DESENI'],20,10)) ;
			$lnValor = intval(trim(substr($laNS['DESENI'],30,10))) ;
			$this->nTotalNIHSS+= $lnValor ;
			$lcRespuesta = trim(str_pad($lcCodCL2TMA,2,'0',STR_PAD_LEFT) . str_pad($lcCodCL3TMA,2,'0',STR_PAD_LEFT)) ;
			$this->aNIHSSP[$lcCodCL2TMA]['respuesta'] = $lcRespuesta;
			$this->aNIHSSP[$lcCodCL2TMA]['valor'] = $lnValor;
			$this->aNIHSSP[$lcCodCL2TMA]['descresp'] = $this->aNIHSSR[$lcRespuesta]['DESCRIP']??'';
		}

		$this->aDocumento = array_merge($this->aNIHSSP);
	}


	/* Prepara array $aReporte con los datos para imprimir */
	private function prepararInforme($taData)
	{
		$laTr['aCuerpo'][0] = [
			'tabla', [
				[
					'w'=>[120,55,12,],
					'a'=>['L','L','C',],
					'd'=>['Pregunta','Respuesta','Val',]
				]
			], []
		];
		foreach($this->aDocumento as $laDocum) {
			$laTr['aCuerpo'][0][2][] = [
				'w'=>[120,55,12,],
				'a'=>['L','L','C',],
				'd'=>[
					$laDocum['pregunta'],
					$laDocum['descresp'],
					$laDocum['valor'],
				],
			];
		}
		$laTr['aCuerpo'][] = ['titulo5', 'TOTAL ESCALA NIHSS: ' . $this->nTotalNIHSS . '   ', 'R'];
		$laTr['aCuerpo'][] = ['saltol', 5];
		$laTr['aCuerpo'][] = ['txthtml9', '<b>Realizado por:</b> ' . $this->cRealizadoPor];

		$this->aReporte = array_merge($this->aReporte, $laTr);
	}


	// Prepara array de preguntas y respuestas de la escala NISHH
	public function CargarNihss()
	{
		$this->aNIHSSP=$this->aNIHSSR=[];

		/* Array de preguntas NIHSS */
		$laPregs = $this->oDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE2TMA || OP5TMA) PREGUNTA')
			->from('TABMAE')
			->where('TIPTMA=\'NIHSSP\' AND CL3TMA=\'1\'')
			->orderBy ('INT(CL2TMA)')
			->getAll('array');
		foreach($laPregs as $laPreg) {
			$this->aNIHSSP[$laPreg['CODIGO']] = [
				'pregunta'=>$laPreg['PREGUNTA'],
				'respuesta'=>'',
				'descresp'=>'',
				'valor'=>0,
			];
		}
		/* Array de Respuestas NIHSS */
		$laRtas = $this->oDb
			->select('CL2TMA, CL3TMA, TRIM(CL4TMA) CODIGO, TRIM(DE1TMA) PUNTAJE, TRIM(DE2TMA) DESCRIP')
			->from('TABMAE')
			->where('TIPTMA=\'NIHSSR\'')
			->orderBy ('INT(CL2TMA)')
			->getAll('array');
		foreach($laRtas as $laRta){
			$this->aNIHSSR[$laRta['CODIGO']] = $laRta;
		}
	}


	public function verificarDatosN($taDatos=[])
	{
		$this->CargarNihss() ;
		$lnTotalNihss = 0 ;

		for ($lnIndice=1; $lnIndice<=15; $lnIndice++)
		{
			$lcTemp = str_pad(strval($lnIndice),2,'0',STR_PAD_LEFT);
			$lcObjeto = 'selNihss'.$lcTemp;
			$lcRespuesta = 'Resp'.$lcTemp;
			$lcPuntosRespuesta = 'Punto'.$lcTemp;
			$lnValorPuntos = 0;
			$lcNumeroRespuesta = $taDatos[$lcRespuesta];
			$lcValorPuntos = $taDatos[$lcPuntosRespuesta];

			$llEncuentra = false ;
			foreach($this->aNIHSSR as $lnKey=>$laRespuesta)
			{
				if(intval($laRespuesta['CL2TMA'])==$lnIndice)
				{
					if(trim($laRespuesta['CL3TMA'])==trim($lcNumeroRespuesta) && trim($laRespuesta['PUNTAJE'])==trim($lcValorPuntos))
					{
						$llEncuentra = true ;
						$lnValorPuntos = intval($laRespuesta['PUNTAJE']);
					}
				}
			}
			if(!$llEncuentra)
			{
				$this->aErrorN = [
					'Mensaje'=>'Error en la respuesta '.$lnIndice.' de la escala NIHSS',
					'Objeto'=>$lcObjeto,
					'Valido'=>false,
				];
				break;
			}
			$lnTotalNihss += $lnValorPuntos;
		}

		if($this->aErrorN['Valido'])
		{
			if (intval($taDatos['TotalN'])!=$lnTotalNihss)
			{
				$this->aErrorN = [
					'Mensaje'=>'Error en el Total de la escala NIHSS',
					'Objeto'=>'txtTotalN',
					'Valido'=>false,
				];
			}

		}

		return $this->aErrorN ;
	}


	public function guardarDatosN($taDatos=[], $tnIngreso=0, $tcTipo='', $tnConCon=1, $tcFecCre='', $tcHorCre='', $tcUsuCre='', $tcPrgCre='', $tlReqAval=false)
	{
		$lcIndice = '1';
		for ($lnIndice=1; $lnIndice<=15; $lnIndice++)
		{
			$lcTemp = str_pad(strval($lnIndice),2,'0',STR_PAD_LEFT);
			$lcRespuesta = 'Resp'.$lcTemp;
			$lcPuntosRespuesta = 'Punto'.$lcTemp;
			$lcNumeroRespuesta = $taDatos[$lcRespuesta];
			$lcValorPuntos = $taDatos[$lcPuntosRespuesta];
			$lcDescrip = $lcIndice . str_repeat(' ',9) . $lnIndice . str_repeat(' ',9) . $lcNumeroRespuesta . str_repeat(' ',9) . $lcValorPuntos;

			if($tlReqAval==true){
				$lnIndiceN = 20;
				$lnSubInd = 20;
				$lnCodigo = $lnIndice * 1000;
				if($tcPrgCre=='HCPPALWEB'){
					$lcTabla = 'HISINT';
					$lcTipo = 'HC';
					$lnIndiceN = 20;
					$lnSubInd = 20;
					$laDatos = [
						'INGHIN'=>$tnIngreso,
						'TIPHIN'=>$lcTipo,
						'CCOHIN'=>$tnConCon,
						'INDHIN'=>$lnIndiceN,
						'SUBHIN'=>$lnSubInd,
						'CODHIN'=>$lnCodigo,
						'CLNHIN'=>$lnIndice,
						'DESHIN'=>$lcDescrip,
						'USRHIN'=>$tcUsuCre,
						'PGMHIN'=>$tcPrgCre,
						'FECHIN'=>$tcFecCre,
						'HORHIN'=>$tcHorCre,
					];
				}else{
					$lcTipo = ($tcPrgCre=='EVOPIWEB'?'EP':($tcPrgCre=='EVOEVWEB'?'ET':($tcPrgCre=='EVOURWEB'?'ER':($tcPrgCre=='EVOUNWEB'?'EU':''))));
					$lcTabla = 'REINDE';
					$lcTitulo = 'ESCALA NIHSS';
					$laDatos = [
						'INGRID'=>$tnIngreso,
						'TIPRID'=>$lcTipo,
						'CONRID'=>$tnConCon,
						'CEXRID'=>$lnIndice,
						'CLIRID'=>$lnIndice,
						'INDRID'=>$lnIndiceN,
						'IN2RID'=>$lnSubInd,
						'DIARID'=>$lcTitulo,
						'DESRID'=>$lcDescrip,
						'USRRID'=>$tcUsuCre,
						'PGMRID'=>$tcPrgCre,
						'FECRID'=>$tcFecCre,
						'HORRID'=>$tcHorCre,
					];
				}
			}else{
				$lcTabla = 'ESCNIH';
				$laDatos = [
					'INGENI'=>$tnIngreso,
					'TIPENI'=>$tcTipo,
					'CCOENI'=>$tnConCon,
					'INDENI'=>$lcIndice,
					'IN2ENI'=>$lnIndice,
					'DESENI'=>$lcDescrip,
					'USRENI'=>$tcUsuCre,
					'PGMENI'=>$tcPrgCre,
					'FECENI'=>$tcFecCre,
					'HORENI'=>$tcHorCre,
				];
			}
			$llResultado = $this->oDb->from($lcTabla)->insertar($laDatos);
		}
	}


	/* Devuelve Array de preguntas NIHSS */
	public function PreguntasNihss()
	{
		return $this->aNIHSSP ;
	}


	/* Devuelve pregunta NIHSS recibe parametro de id pregunta*/
	public function PreguntaNihss($tcIndice='', $tcDato='pregunta')
	{
		return $this->aNIHSSP[$tcIndice][$tcDato] ?? '';
	}


	/* Devuelve Array de preguntas NIHSS */
	public function RespuestasNihss()
	{
		return $this->aNIHSSR ;
	}

}
