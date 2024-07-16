<?php
/* ********************  DOCUMENTO BASE PARA FACTURACIÓN ELECTRÓNICA  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FuncionesInv.php';
require_once __DIR__ . '/class.FeFunciones.php';
require_once __DIR__ . '/class.NumerosLetras.php';


class FeConsultar
{
	/* Tipo de documento */
	protected $cTipDocXml = '';
	protected $cTipoFac = '';
	public $cKeyControl = '';

	/* Objeto para consultar base de datos */
	protected $oDb = null;

	/* Objeto de configuración de facturación */
	public $aCnf = null;

	public $oEmisor = null;

	/* Objeto para construir XML */
	protected $oDomDoc = null;

	/* Datos de factura */
	protected $aFactura = [];

	/* Detalles de factura */
	protected $aDetalles = [];

	/* Datos del paciente */
	protected $aPaciente = [];

	/* Datos del adquiriente */
	protected $aAdquiriente = [];

	/* Códigos  */
	protected $aCenEst = [];

	/* Errores  */
	protected $aError = [];

	/* Saltos de línea */
	protected $cSLD = '<br>';

	protected $cObsAdd = [
		'ini'=>'#$',
		'fin'=>'#$',
	];

	/* NITs que siempre se envía CUM */
	protected $bEntidadEnviaCUM = false;
	protected $aNitCumSiempre = [];


	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig)
	{
		global $goDb;
		$this->oDb = $goDb;
		$this->aCnf = $taConfig;
		$this->cSLD = $taConfig['parFac']['saltoLinea'][$taConfig['proveedor']] ?? $this->cSLD;
	}


	/*
	 *	Organizar los datos del documento
	 */
	public function crearArrayDatos($tnFactura=0, $tnNota=0, $tnDocAdj=0) {}


	/*
	 *	Organiza los datos del documento y los retorna en un array
	 */
	protected function crearArrayFac()
	{
		return [];
	}


	/*
	 *	Obtiene obligaciones para el tercero desde la tabla PRMGEN
	 *	@param string $tcNit: Nit con ceros al comienzo hasta 13 dígitos
	 */
	protected function obligacionesNit($tcNit)
	{
		$lcRetorna = '';
		$laObliga = $this->oDb
			->select('SUBSTR(GENDSC, 359, 2) TIPOBL')
			->from('PRMGEN')
			->where(['GENCOD'=>$tcNit, 'GENTIP'=>'TERMMG', 'GENSTS'=>'1', ])
			->get('array');
		if (is_array($laObliga)) {
			$laLst = array_keys($this->aCnf['codigosOblig']);
			if (in_array($laObliga['TIPOBL'], $laLst)) {
				$lcRetorna = $this->aCnf['codigosOblig'][ $laObliga['TIPOBL'] ];
			}
		}
		return $lcRetorna;
	}


	/*
	 *	Retorna observaciones especiales por plan (planes de armada, policía, etc)
	 *	@param string $tcPlan: código del plan
	 *	@param string $tcFactura: número de la factura
	 *	@param string $tcContrato: número del contrato
	 */
	public function observacionesEspeciales($tcPlan, $tcFactura, $tcContrato)
	{
		// Busca configuración por el plan
		$lcObsEsp = '';
		$laCnfObs = $this->oDb
			->select('CODIDB,IDFADB,EMA2DB,RESODB')
			->from('FACPLNDB')
			->where([
				'PLANDB'=>$tcPlan,
				'SECUDB'=>1,
				'ESTCNT'=>0
			])
			->get('array');
		if ($this->oDb->numRows() > 0) {
			$laCnfObs = array_map('trim', $laCnfObs);
			$lcSegunda = trim($laCnfObs['IDFADB']=='R' ? $laCnfObs['RESODB'] : ($laCnfObs['IDFADB']=='F' ? $tcFactura : ($laCnfObs['IDFADB']=='C' ? $tcContrato : '')));
			if (!empty($lcSegunda)) {
				$lcObsEsp = "{$this->cObsAdd['ini']}{$laCnfObs['CODIDB']};{$lcSegunda};{$laCnfObs['EMA2DB']}{$this->cObsAdd['fin']}";
			}
		}

		return $lcObsEsp;
	}


	/*
	 *	Retorna arreglo con la lista de NIT a los que siempre se debe enviar CUM
	 */
	public function NitCumSiempre()
	{
		$laNitCumSiempre = $this->oDb
			->select('OP4TMA NIT')
			->from('TABMAE')
			->where("TIPTMA='FACTELE' AND CL1TMA='ITEMS' AND CL2TMA='MEDICA' AND CL3TMA='ENVCUM' AND CL4TMA<>'' AND ESTTMA=''")
			->getAll('array');
		if ($this->oDb->numRows()>0) {
			$this->aNitCumSiempre = array_column($laNitCumSiempre, 'NIT');
		}
	}


	/*
	 *	Retorna fechas incial y final de periodo de facturación de una factura del sector salud
	 */
	public function obtenerPeriodoFactura($tnFactura)
	{
		$laFechas = [
			'inicial'=> 0,
			'final'  => 0,
		];

		// Consulta cabecera de Factura
		$laFactura = $this->oDb
			->select('F.FRACAB, F.CONCAB, F.INGCAB, F.PLNCAB, F.DOCCAB, I.FEIING, I.FEEING')
			->select("(SELECT MAX(D.FINDFA) FROM FACDETF AS D WHERE D.INGDFA=F.INGCAB AND D.NFADFA=F.FRACAB AND D.DOCDFA=F.DOCCAB AND D.CFADFA=F.CONCAB AND D.PLADFA=F.PLNCAB AND D.TINDFA='400') FECHAMAX")
			->from('FACCABF AS F')
			->innerJoin('RIAING  AS I', 'F.INGCAB=I.NIGING')
			->where([ 'F.FRACAB' => $tnFactura, 'F.DOCCAB' => substr(str_pad($tnFactura, 8, '0', STR_PAD_LEFT), 2, 6) ])
			->get('array');
		if ($this->oDb->numRows()>0) {
			$laFechas = [
				'inicial'=> $laFactura['FEIING'],
				'final'  => $laFactura['FEEING'],
			];

			if ($laFactura['FECHAMAX']>0) {
				$laFechas['final'] = $laFactura['FECHAMAX'];
			} else {
				$laArray = $this->oDb
					->max('FINDFA','FECHAMAX')
					->from('FACDETF')
					->where([
						'INGDFA'=>$laFactura['INGCAB'],
						'NFADFA'=>$laFactura['FRACAB'],
						'DOCDFA'=>$laFactura['DOCCAB'],
						'CFADFA'=>$laFactura['CONCAB'],
						'PLADFA'=>$laFactura['PLNCAB'],
					])
					->get('array');
				if ($this->oDb->numRows()>0) {
					if ($laArray['FECHAMAX']>0) {
						$laFechas['final'] = $laArray['FECHAMAX'];
					}
				}
			}
		}

		return $laFechas;
	}


	/* Datos de factura */
	public function aDatosFactura()
	{
		return $this->aFactura;
	}

	/* Detalles de factura */
	public function aDatosDetalles()
	{
		return $this->aDetalles;
	}

	/* Datos del Adquiriente */
	public function aDatosAdquiriente()
	{
		return $this->aAdquiriente;
	}

	/* Datos del Paciente */
	public function aDatosPaciente()
	{
		return $this->aPaciente;
	}

	/* Retorna array aError */
	public function aError()
	{
		return $this->aError;
	}

}