<?php
/* ********************  NOTA CRÉDITO ANULACIÓN FACTURA ASISTENCIAL  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FeConsultar.php';


class FeConsultar06NC extends FeConsultar
{
	/* Códigos  */
	protected $aCenEst = [];
	protected $bPlanShaio = false;
	protected $bPlanParticular = false;
	protected $bEsFacElectr = true;


	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig)
	{
		parent::__construct($taConfig);

		$this->cTipDocXml = 'NC';
		$this->cTipoFac = '06NC';

		// Parámetros
		$laCenEst = $this->oDb
			->select('CL1TMA AS CODIGO, DE2TMA AS LISTA')
			->from('TABMAE')
			->where(['TIPTMA'=>'CENEST', 'ESTTMA'=>''])
			->getAll('array');
		foreach ($laCenEst as $laVal) {
			$laVal = array_map('trim', $laVal);
			$this->aCenEst[$laVal['CODIGO']] = explode(',', $laVal['LISTA']);
		}
		$this->NitCumSiempre();
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayDatos($tnFactura=0, $tnNota=0, $tnDocAdj=0)
	{
		parent::crearArrayDatos();

		$this->consultaCabecera($tnFactura, $tnNota);
		$this->consultaDetalle();
		return count($this->aError)>0 ? [] : $this->crearArrayFac();
	}


	/*
	 *	Consulta de cabecera de nota de anulación de factura asistencial.
	 */
	private function consultaCabecera($tcNumFac, $tcNumNota)
	{
		$lcDocNot = 'A'.substr($tcNumFac, -5);
		$lcDocFac = substr($tcNumFac, -6);

		// Consulta cabecera de Anulación de Factura
		$this->aFactura = $this->oDb
			->select('F.FRACAB, F.CONCAB, F.FEFCAB, F.VAFCAB, F.NITCAB, F.PLNCAB, F.VD1CAB, F.MA1CAB, F.HORCAB, F.USRCAB, FA.FEFCAB AS FEFFAC, IFNULL(FE.CUFE,\'\') CUFE')
			->select('I.NIGING, I.TIDING, I.NIDING, I.FEIING, I.HORING, I.FEEING, I.HREING, TRIM(U.NNOMED) N_ELABORO, TRIM(U.NOMMED) A_ELABORO, U.TIDRGM, U.NIDRGM')
			->select('SUBSTR(S.DESPAI, LOCATE(\' - \', S.DESPAI)+3, 2) CODPAI, SUBSTR(S.DESPAI, 1, LOCATE(\' - \', S.DESPAI)) DESPAI, D.CODDEP, D.DESDEP, C.CODCIU, C.DESCIU')
			->select('P.NM1PAC, P.NM2PAC, P.AP1PAC, P.AP2PAC, P.DR1PAC, P.DR2PAC, P.TELPAC, P.MAIPAC, P.FNAPAC, P.SEXPAC, P.NHCPAC')
			->select('L.DSCCON, L.DCOCON, L.TL1CON, L.OBSCON, L.NI1CON, L.EMLCON, IFNULL(SUBSTR(A.DE1TMA,1,20),\'\') AS TIPAFI, L.FPGCON, L.TENCON')
			->select('SUBSTR(SE.DESPAI, LOCATE(\' - \', SE.DESPAI)+3, 2) CODPAIE, SUBSTR(SE.DESPAI, 1, LOCATE(\' - \', SE.DESPAI)) DESPAIE')
			->select('DE.CODDEP CODDEPE, DE.DESDEP DESDEPE, CE.CODCIU CODCIUE, CE.DESCIU DESCIUE, N.NUMEDA, N.COPBDA, N.MCOPDA')
			->from('FACCABF AS F')
			->innerJoin('FACCABF AS FA',"F.FRACAB=FA.FRACAB AND FA.DOCCAB='$lcDocFac'", null)
			->innerJoin('RIAING  AS I', 'F.INGCAB=I.NIGING', null)
			->innerJoin('RIAPAC  AS P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->innerJoin('FACPLNC AS L', 'F.PLNCAB=L.PLNCON', null)
			->leftJoin('FACPLNDA AS N', 'F.PLNCAB=N.PLANDA', null)
			->leftJoin( 'RIAEPP  AS E', 'I.TIDING=E.TIDEPP AND I.NIDING=E.NIDEPP AND F.PLNCAB=E.PLAEPP', null)
			->leftJoin( 'FEMOV   AS FE',"FE.FACT='$tcNumFac' AND TIPR='06FA'", null)
			->leftJoin( 'COMPAI  AS S', 'P.PARPAC=S.CODPAI', null)
			->leftJoin( 'COMDEP  AS D', 'P.PARPAC=D.PAIDEP AND P.DERPAC=D.CODDEP', null)
			->leftJoin( 'COMCIU  AS C', 'P.PARPAC=C.PAICIU AND P.DERPAC=C.DEPCIU AND P.MURPAC=C.CODCIU', null)
			->leftJoin( 'COMPAI  AS SE','L.PA1CON=SE.CODPAI', null)
			->leftJoin( 'COMDEP  AS DE','L.PA1CON=DE.PAIDEP AND L.DP1CON=DE.CODDEP', null)
			->leftJoin( 'COMCIU  AS CE','L.PA1CON=CE.PAICIU AND L.DP1CON=CE.DEPCIU AND L.CD1CON=CE.CODCIU', null)
			->leftJoin( 'TABMAE  AS A', 'A.TIPTMA=\'DATING\' AND A.CL1TMA=\'2\' AND E.TIAEPP=A.CL2TMA', null)
			->leftJoin( 'RIARGMN AS U', 'F.USRCAB=U.USUARI', null)
			->where([ 'F.FRACAB' => $tcNumFac, 'F.DOCCAB' => $lcDocNot ]) // 'F.NORCAB' => $tcNumNota,
			->get('array');

		// Validar que exista la nota
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró la Nota tipo {$this->cTipoFac} número {$tcNumNota}.",
			];
			return;
		}

		$this->aFactura = array_map('trim', $this->aFactura);
		$this->bPlanParticular = in_array($this->aFactura['TENCON'], ['25','30']);
		$this->aFactura['DOCFAC'] = $lcDocFac;
		$this->aFactura['DOCNOT'] = $lcDocNot;
		$this->aFactura['NRONOT'] = $tcNumNota;
		$this->aFactura['COPAGO'] = 0;
		$this->aFactura['COPAGO_OBS'] = '';

		// Validar la fecha de la factura
		$this->bEsFacElectr = $this->aFactura['FEFFAC'] >= $this->aCnf['parFac']['fechaInicio'];


		// Datos de paciente de investigación
		$laPacInvestiga = $this->oDb
			->select('TIIPIN, IDIPIN, A1IPIN, A2IPIN, N1IPIN, N2IPIN')
			->from('PACINV')
			->where([ 'INGPIN' => $this->aFactura['NIGING'] ])
			->getAll('array');

		if ( $this->oDb->numRows() > 0 ) {
			$laPacInv = array_map('trim', $laPacInvestiga['0']);
			$this->aPaciente = [
				'nombre' => $laPacInv['N1IPIN'].' '.$laPacInv['N2IPIN'],
				'apellido' => $laPacInv['A1IPIN'].' '.$laPacInv['A2IPIN'],
				'nombre1' => $laPacInv['N1IPIN'],
				'nombre2' => $laPacInv['N2IPIN'],
				'apellido1' => $laPacInv['A1IPIN'],
				'apellido2' => $laPacInv['A2IPIN'],
				'tipoId' => $laPacInv['TIIPIN'],
				'numeId' => $laPacInv['IDIPIN'],
			];
		} else {
			$this->aPaciente = [
				'nombre' => $this->aFactura['NM1PAC'].' '.$this->aFactura['NM2PAC'],
				'apellido' => $this->aFactura['AP1PAC'].' '.$this->aFactura['AP2PAC'],
				'nombre1' => $this->aFactura['NM1PAC'],
				'nombre2' => $this->aFactura['NM2PAC'],
				'apellido1' => $this->aFactura['AP1PAC'],
				'apellido2' => $this->aFactura['AP2PAC'],
				'tipoId' => $this->aFactura['TIDING'],
				'numeId' => $this->aFactura['NIDING'],
			];
		}

		// Datos del adquiriente
		$lbPacienteEsAdq = false;
		if ( $this->aFactura['NI1CON']==$this->aCnf['emisor']['NumeroIdentificacion'] ) {
			// Si el plan es particular el adquiriente es el paciente o responsable
			$this->bPlanShaio = true;

			// Si existe pagaré se toman los datos de este
			$laAdquiriente = $this->oDb
				->select('NM1PAG, AP1PAG, TI1PAG, ID1PAG, DR1PAG, TL1PAG')
				->from('PAGARE')
				->where([ 'NIGPAG' => $this->aFactura['NIGING'] ])
				->where('NM1PAG<>\'\' AND ID1PAG>0')
				->getAll('array');
			if ( $this->oDb->numRows() > 0 ) {
				$laAdq = array_map('trim', $laAdquiriente['0']);
				$this->aAdquiriente = [
					'nombre' => $laAdq['NM1PAG'],
					'apellido' => $laAdq['AP1PAG'],
					'contacto' => $laAdq['NM1PAG'].' '.$laAdq['AP1PAG'],
					'tipoId' => $laAdq['TI1PAG'],
					'numeId' => $laAdq['ID1PAG'],
					'tipoPer' => $this->aCnf['codTipoPer']['natural'],
					'direccion' => $laAdq['DR1PAG'],
					'telefono' => $laAdq['TL1PAG'],
					'correo' => $this->aFactura['MAIPAC'],
				];
			} else {
				$lbPacienteEsAdq = true;
				$this->aAdquiriente = [
					'nombre' => $this->aPaciente['nombre'],
					'apellido' => $this->aPaciente['apellido'],
					'contacto' => $this->aPaciente['nombre'].' '.$this->aPaciente['apellido'],
					'tipoId' => $this->aPaciente['tipoId'],
					'numeId' => $this->aPaciente['numeId'],
					'tipoPer' => $this->aCnf['codTipoPer']['natural'],
					'direccion' => $this->aFactura['DR1PAC'].' - '.$this->aFactura['DR2PAC'],
					'telefono' => $this->aFactura['TELPAC'],
					'correo' => $this->aFactura['MAIPAC'],
				];
			}

			$lcCodPais = $this->aFactura['CODPAI'];
			$lcDscPais = $this->aFactura['DESPAI'];
			$lcCodDpto = str_pad($this->aFactura['CODDEP'], 2, '0', STR_PAD_LEFT);
			$lcCodCiud = $lcCodDpto . str_pad($this->aFactura['CODCIU'], 3, '0', STR_PAD_LEFT);
			$this->aAdquiriente += [
				'codpais' => $lcCodPais,
				'pais' => $lcDscPais,
				'coddepto' => $lcCodDpto,
				'depto' => $this->aFactura['DESDEP'],
				'codciudad' => $lcCodCiud,
				'ciudad' => $this->aFactura['DESCIU'],
				'autoriza' => '',
			];

		} else {
			$this->bPlanShaio = false;

			// Obtiene número de autorización
			$laNumAut = $this->oDb
				->select('TRIM(SUBSTR(DESAUS, 18)) NUMAUT')
				->from('AUTASE')
				->where(['INGAUS' => $this->aFactura['NIGING'] ])
				->where('INDAUS=3 AND CNLAUS=2 AND PGMAUS=\'AUT002\'')
				->orderBy('FECAUS DESC')
				->getAll('array');
			$lcNumAut = trim($laNumAut[0]['NUMAUT'] ?? '');

			$lcCodPais = $this->aFactura['CODPAIE'];
			$lcDscPais = $this->aFactura['DESPAIE'];
			$lcCodDpto = str_pad($this->aFactura['CODDEPE'], 2, '0', STR_PAD_LEFT);
			$lcCodCiud = $lcCodDpto . str_pad($this->aFactura['CODCIUE'], 3, '0', STR_PAD_LEFT);

			// El adquiriente es la entidad
			$this->aAdquiriente = [
				'nombre' => $this->aFactura['DSCCON'],
				'apellido' => '',
				'contacto' => '',
				'tipoId' => 'N',
				'numeId' =>  $this->aFactura['NITCAB'],
				'tipoPer' => $this->aCnf['codTipoPer']['juridica'],
				'direccion' => $this->aFactura['DCOCON'],
				'telefono' => $this->aFactura['TL1CON'],
				'correo' => $this->aFactura['EMLCON'],
				'autoriza' => $lcNumAut,
				'codpais' => $lcCodPais,
				'pais' => $lcDscPais,
				'coddepto' => $lcCodDpto,
				'depto' => $this->aFactura['DESDEPE'],
				'codciudad' => $lcCodCiud,
				'ciudad' => $this->aFactura['DESCIUE'],
			];

			$this->bEntidadEnviaCUM = in_array($this->aFactura['NITCAB'], $this->aNitCumSiempre);
		}

		if ($lcCodPais=='CO') {
			// Para Bogotá reporta ciudad y no la localidad
			if ($this->aAdquiriente['coddepto']=='11') {
				$this->aAdquiriente['codciudad'] = '11001';
				$this->aAdquiriente['ciudad'] = $this->aAdquiriente['depto'];
			}
		} else {
			// Para otros países no se reporta depto ni ciudad
			$this->aAdquiriente['coddepto'] = '';
			$this->aAdquiriente['depto'] = '';
			$this->aAdquiriente['codciudad'] = '';
			$this->aAdquiriente['ciudad'] = '';
		}


		$lcNumDoc13 = str_pad($this->aAdquiriente['numeId'],13,'0',STR_PAD_LEFT);
		$laPrmTer = $this->oDb
			->select('TE1SOC, TE1PER')
			->from('PRMTE1')
			->where([ 'TE1COD' => $lcNumDoc13 ])
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			$laPrmTer = array_map('trim', $laPrmTer['0']);
			if ( !$this->bPlanShaio ) {
				$this->aAdquiriente['nombre'] = $laPrmTer['TE1SOC'];
			}
			if ($laPrmTer['TE1PER']=='J') {
				$this->aAdquiriente['tipoPer'] = $this->aCnf['codTipoPer']['juridica'];
				//$this->aAdquiriente['tipoId'] = 'N';
			}
		}
		// Datos de contacto
		$laPrmTer = $this->oDb
			->select('TE2CIU, TE2DIR, TE2TEL, TE2COM, TE2LEG, TE2MAI')
			->from('PRMTE2')
			->where([ 'TE2COD' => $lcNumDoc13 ])
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			$laPrmTer = array_map('trim', $laPrmTer['0']);
			if ($this->aAdquiriente['contacto'] == '')
				$this->aAdquiriente['contacto'] = $laPrmTer['TE2COM']=='' ? $laPrmTer['TE2LEG'] : $laPrmTer['TE2COM'];
			if ($this->aAdquiriente['direccion'] == '')
				$this->aAdquiriente['direccion'] = $laPrmTer['TE2DIR'];
			if ($this->aAdquiriente['telefono'] == '')
				$this->aAdquiriente['telefono'] = $laPrmTer['TE2TEL'];
		}


		// Configuración predeterminada según tipo de persona
		// TributoCliente
		$this->aAdquiriente['tributo'] = $this->aCnf['cliente']['tributo'][$this->aAdquiriente['tipoPer']];
		// ObligacionesCliente
		$this->aAdquiriente['respfiscal'] = $this->aCnf['cliente']['respfiscal'][$this->aAdquiriente['tipoPer']];
		if ($this->aAdquiriente['tipoId']=='N') {
			$lcOblig = $this->obligacionesNit($lcNumDoc13);
			if (!empty($lcOblig)) $this->aAdquiriente['respfiscal'] = [ $lcOblig ];
		}
		// Forma y Medio de Pago
		$this->aAdquiriente['FormaDePago'] = $this->aCnf['cliente']['formaPago'][$this->aAdquiriente['tipoPer']];
		$this->aAdquiriente['MedioDePago'] = $this->aCnf['cliente']['medioPago'][$this->aAdquiriente['tipoPer']];


		// Obtener pasaporte
		if ($this->aPaciente['tipoId'] == 'P') {
			$laPasaporte = $this->oDb
				->select('OP5PAL')
				->from('PACALT')
				->where([ 'TIDPAL' => $this->aPaciente['tipoId'], 'NIDPAL' => $this->aPaciente['numeId'] ])
				->where('OP5PAL', '<>', '')
				->getAll('array');
			if ($this->oDb->numRows() > 0) {
				$this->aPaciente['numeId'] = trim($laPasaporte[0]['OP5PAL']);
				if ( $lbPacienteEsAdq )
					$this->aAdquiriente['numeId'] = $this->aPaciente['numeId'];
			}
		}
		if (!$lbPacienteEsAdq && $this->aAdquiriente['tipoId'] == 'P') {
			$laPasaporte = $this->oDb
				->select('OP5PAL')
				->from('PACALT')
				->where([
					'TIDPAL' => $this->aAdquiriente['tipoId'],
					'NIDPAL' => $this->aAdquiriente['numeId']
				])
				->where('OP5PAL', '<>', '')
				->getAll('array');
			if ($this->oDb->numRows() > 0)
				$this->aAdquiriente['numeId'] = trim($laPasaporte[0]['OP5PAL']);
		}

		// Obtener observaciones especiales
		$this->aFactura['ObsAdic'] = '';
		if (!$lbPacienteEsAdq && $this->aAdquiriente['tipoPer']==$this->aCnf['codTipoPer']['juridica']) {
			$this->aFactura['ObsAdic'] = $this->observacionesEspeciales($this->aFactura['PLNCAB'], $this->aFactura['FRACAB'], $this->aFactura['NUMEDA']);
		}
	}


	/*
	 *	Consulta de factura asistencial.
	 *	Detalle obtenido desde Estado de Cuenta por Cento de Costo y Tipo de Consumo.
	 *	Form INF022.INF013FDN()
	 */
	private function consultaDetalle()
	{
		$this->aDetalles = [];
		$this->consultaDetallePaq();
		$this->consultaDetalleCup();
		$this->consultaDetalleMedEle();
		$this->consultaDetalleCopago();

		// Validar que suma de detalle sea igual a total factura
		$lnTotalDet = 0;
		foreach ( $this->aDetalles as $laDet ) {
			$lnTotalDet += $laDet['vrTotal'];
		}
		$this->aFactura['VAFCAB'] = $this->aFactura['VAFCAB'] * -1;

		if ( !( $lnTotalDet * 1 == $this->aFactura['VAFCAB'] * 1 ) ) {
			// Valores diferentes
			$this->aError = [
				'Num' => '050',
				'Dsc' => "El valor de la cabecera ($ {$this->aFactura['VAFCAB']}) no coindice con la suma de los detalles ($ {$lnTotalDet}).",
			];
		}

		// Corrección del total de la factura, por Copagos, Bonos o Cuotas Moderadoras
		$this->aFactura['VAFCAB'] = $this->aFactura['VAFCAB']*1 + $this->aFactura['COPAGO']*1;
	}


	/*
	 *	Consulta de detalles de paquetes
	 */
	private function consultaDetallePaq()
	{
		$laDetalles = $this->oDb
			->select('D.CUPDFA AS CODIGO, C.DESCUP AS DESCRIP')
			->sum('D.DT1DFA','TOT_DESC')
			->sum('D.VPRDFA','VR_TOTAL')
			->from('FACDETF AS D')
			->leftJoin('RIACUP AS C', 'D.CUPDFA=C.CODCUP', null)
			->leftJoin('PRMTAB AS P', 'P.TABTIP=\'004\' AND D.CCTDFA=P.TABCOD', null)
			->where([
				'D.INGDFA'=>$this->aFactura['NIGING'],
				'D.NFADFA'=>$this->aFactura['FRACAB'],
				'D.DOCDFA'=>$this->aFactura['DOCFAC'],
				//'D.DOCDFA'=>$this->aFactura['DOCNOT'],
				'D.CFADFA'=>$this->aFactura['CONCAB'],
				'D.PLADFA'=>$this->aFactura['PLNCAB'], ])
			->like('D.CUPDFA', 'C%')
			->groupBy('D.CUPDFA, C.DESCUP')
			->orderBy('D.CUPDFA, C.DESCUP')
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			foreach ($laDetalles as $laDet) {
				$laDet = array_map('trim', $laDet);
				$lnVrTot = $laDet['VR_TOTAL'] - $laDet['TOT_DESC'];
				$this->aDetalles[] = [
					'codCentro'	=> '',
					'dscCentro'	=> '',
					'marca'		=> '0',
					'tipoItem'	=> '',
					'codItem'	=> $laDet['CODIGO'],
					'codAux'	=> '',
					'dscItem'	=> FeFunciones::quitarEspacios($laDet['DESCRIP']),
					'cantidad'	=> 1,
					'vrUnidad'	=> $laDet['VR_TOTAL'],
					'vrDesc'	=> $laDet['TOT_DESC'],
					'vrTotal'	=> $lnVrTot,
					'vrUnid0'	=> $lnVrTot,
				];
			}
		}
	}


	/*
	 *	Consulta de detalles de Procedimientos
	 */
	private function consultaDetalleCup()
	{
		$lcTipoCons = '400';

		$laDetalles = $this->oDb
			->select('TRIM(D.CCTDFA) AS CENTRO, TRIM(P.TABDSC) AS DSCCEN, D.CVLDFA AS VALE, TRIM(D.CUPDFA) AS CODIGO')
			->select('TRIM(C.DESCUP) AS DESCRIP, TRIM(S.DESCUP) AS DESCRIP6, D.VUNDFA AS VR_UNIDAD')
			->sum('D.DT1DFA','TOT_DESC')
			->sum('D.QCODFA','TOT_CONS')
			->sum('D.VPRDFA','VR_TOTAL')
			->from('FACDETF AS D')
			->leftJoin('RIACUP AS C', 'D.CUPDFA=C.CODCUP', null)
			->leftJoin('RIACUP AS S', 'SUBSTR(D.CUPDFA,1,6)=S.CODCUP', null)
			->leftJoin('PRMTAB AS P', 'P.TABTIP=\'004\' AND D.CCTDFA=P.TABCOD', null)
			->where([
				'D.INGDFA'=>$this->aFactura['NIGING'],
				'D.NFADFA'=>$this->aFactura['FRACAB'],
				'D.DOCDFA'=>$this->aFactura['DOCFAC'],
				//'D.DOCDFA'=>$this->aFactura['DOCNOT'],
				'D.CFADFA'=>$this->aFactura['CONCAB'],
				'D.PLADFA'=>$this->aFactura['PLNCAB'],
				'D.TINDFA'=>$lcTipoCons ])
			->notLike('D.CUPDFA', 'C%')
			->groupBy('D.CCTDFA, P.TABDSC, D.CVLDFA, D.CUPDFA, C.DESCUP, S.DESCUP, D.VUNDFA')
			->orderBy('D.CCTDFA, P.TABDSC, D.CVLDFA, D.CUPDFA, C.DESCUP, S.DESCUP')
			->getAll('array');

		if ($this->oDb->numRows() > 0) {
			$laDets = [];

			// Recorre los detalles de Cups
			foreach ($laDetalles as $laDet) {
				$laDet = array_map('trim', $laDet);

				// Valida el nivel 0
				$lnNumDet0 = -1;
				$lbExiste = false;
				foreach ($laDets as $lnKey => $laDetalle) {
					if ( $laDetalle['codCentro']==$laDet['CENTRO']
						&& $laDetalle['marca']=='0'
						&& $laDetalle['tipoItem']==$lcTipoCons
						&& $laDetalle['codCup']==$laDet['CODIGO']
						&& $laDetalle['vrUnid0']==$laDet['VR_UNIDAD'] ) {
							$lbExiste = true;
							$lnNumDet0 = $lnKey;
							break;
						}
				}

				if ( $lbExiste ) {
					// Si existe el nivel 0, incrementa los valores
					$laDets[$lnNumDet0]['cantidad'] += $laDet['TOT_CONS'];
					$laDets[$lnNumDet0]['vrTotal'] += $laDet['VR_TOTAL'];
					$laDets[$lnNumDet0]['vrDesc'] += $laDet['TOT_DESC'];
				} else {
					$lcDscDet = FeFunciones::quitarEspacios(FeFunciones::quitarAsteriscos($laDet['DESCRIP']));
					if (strlen($laDet['CODIGO'])>6) {
						$lcCodDet = substr($laDet['CODIGO'], 0, 6);
						$lcDscDet = FeFunciones::quitarEspacios(FeFunciones::quitarAsteriscos($laDet['DESCRIP6'])).$this->cSLD.'('.$laDet['CODIGO'].' - '.$lcDscDet.')';
					} else {
						$lcCodDet = $laDet['CODIGO'];
					}
					// Si no existe el nivel 0, lo inserta en el array
					$laDets[] = [
						'codCentro'	=> $laDet['CENTRO'],
						'dscCentro'	=> $laDet['DSCCEN'],
						'marca'		=> '0',
						'tipoItem'	=> $lcTipoCons,
						'codItem'	=> $lcCodDet,
						'codCup'	=> $laDet['CODIGO'],
						'codAux'	=> '',
						'dscItem'	=> $lcDscDet,
						'dscItem0'	=> $lcDscDet,
						'cantidad'	=> $laDet['TOT_CONS'],
						'vrUnidad'	=> $laDet['VR_UNIDAD'],
						'vrDesc'	=> 0, //$laDet['TOT_DESC'],?
						'vrTotal'	=> $laDet['VR_TOTAL']-$laDet['TOT_DESC'],
						'vrUnid0'	=> $laDet['VR_UNIDAD'],
					];
					$lnNumDet0 = count($laDets)-1;
				}


				// Validación centros y cups que requieren nivel 1
				$lbCup = in_array( $laDet['CODIGO'], $this->aCenEst['2'] ); // El cup está en la lista?
				$lbCen = in_array( $laDet['CENTRO'], $this->aCenEst['1'] ); // En centro de costo está en la lista?

				if ( $lbCen && !$lbCup ) {

					// Trae niveles 1
					$laCups = $this->oDb
						->select('F.ELEDNA, SUBSTR(C.DESCUP,1,109) DESCUP, F.VUNDNA, F.QCODNA, F.VPRDNA')
						->from('FACDETN AS F')
						->leftJoin('RIACUP AS C', 'F.ELEDNA=C.CODCUP')
						->where([
							'F.INGDNA'=>$this->aFactura['NIGING'],
							'F.DOCDNA'=>$this->aFactura['DOCFAC'],
							//'F.DOCDNA'=>$this->aFactura['DOCNOT'],
							'F.CVLDNA'=>$laDet['VALE'],
							'F.CUPDNA'=>$laDet['CODIGO'],
							'F.TINDNA'=>$lcTipoCons,
							'F.NPRDNA'=>'1', ])
						->where('F.VPRDNA', '>', 0)
						->getAll('array');

					// Existen registros en Nivel 1 - reccount(FACDETF)>0
					if ( $this->oDb->numRows() > 0 ) {
						// recorre nivel 1
						foreach ($laCups as $laCup) {
							$laCup = array_map('trim', $laCup);

							$lbExiste = false;
							$lnNumDet1 = -1;
							foreach ($laDets as $lnKey => $laDetalle) {
								if ( $laDetalle['codCentro']==$laDet['CENTRO']
									&& $laDetalle['marca']=='1'
									&& $laDetalle['tipoItem']==$lcTipoCons
									&& $laDetalle['codItem']==$laDet['CODIGO']
									&& $laDetalle['codAux']==$laCup['ELEDNA']
									&& $laDetalle['vrUnid0']==$laDet['VR_UNIDAD'] ) {
										$lbExiste = true;
										$lnNumDet1 = $lnKey;
										break;
									}
							}

							if ( $lbExiste ) {
								$laDets[$lnNumDet1]['cantidad'] += $laCup['QCODNA'];
								$laDets[$lnNumDet1]['vrTotal'] += $laCup['VPRDNA'];
							} else {
								$laDets[] = [
									'codCentro'	=> $laDet['CENTRO'],
									'dscCentro'	=> $laDet['DSCCEN'],
									'marca'		=> '1',
									'tipoItem'	=> $lcTipoCons,
									'codItem'	=> $laDet['CODIGO'],
									'codAux'	=> $laCup['ELEDNA'],
									'dscItem'	=> FeFunciones::quitarEspacios($laCup['DESCUP']),
									'cantidad'	=> $laCup['QCODNA'],
									'vrUnidad'	=> $laCup['VUNDNA'],
									'vrDesc'	=> 0,
									'vrTotal'	=> $laCup['VPRDNA'],
									'vrUnid0'	=> $laDet['VR_UNIDAD'],
								];
							}
						} // Fin recorre nivel 1
					} // Fin existen registros en Nivel 1

				} // Fin validación códigos centros y cups
			} // Fin recorre los detalles de Cups


			// Adicionar subdetalles
			foreach ($laDets as $lnLlave => $laDet) {
				if ( $laDet['marca']=='0' ) {
					$this->aDetalles[] = $laDet;
				} else {
					if ($this->aCnf['parFac']['addSubDetalles']) {
						$lnClave = count($this->aDetalles)-1;
						$this->aDetalles[$lnClave]['dscItem'] .= $this->cSLD
								. ' - Cnt.' . $laDet['cantidad']
								. ' - ' . substr(FeFunciones::quitarEspacios($laDet['dscItem']), 0, 50)
								. ' - Vr: $' . number_format($laDet['vrTotal'],2,',','.');
						if (!isset($this->aDetalles[$lnClave]['subdetalle'])) $this->aDetalles[$lnClave]['subdetalle'] = [];
						$this->aDetalles[$lnClave]['subdetalle'][] = [
							'codigo' => $laDet['codItem'],
							'descrip' => substr(FeFunciones::quitarEspacios($laDet['dscItem']), 0, 50),
							'cantidad' => $laDet['cantidad'],
							'valorund' => $laDet['vrUnidad'],
							'valortot' => $laDet['vrTotal'],
						];
					}
				}
			}
		}
	}


	/*
	 *	Consulta de detalles de Medicamentos y Elementos
	 */
	private function consultaDetalleMedEle()
	{
		$laTipoCons = [
			'500' => ['0015', 'MEDICAMENTOS'],
			'600' => ['0018', 'ELEMENTOS'],
		];
		$laDetalles = $this->oDb
			->select('D.TINDFA AS TIPCONS, D.CNSDFA AS CNSCONS, D.ELEDFA AS CODIGO, I.DESDES AS DESCRIP')
			->select('D.VUNDFA AS VR_UNIDAD, D.QCODFA AS CANTIDAD, D.VPRDFA AS VR_TOTAL, G.RF4EST AS NOPOS')
			->sum('D.DT1DFA','TOT_DESC')
			->from('FACDETF AS D')
			->leftJoin('RIAESTM AS G', 'D.INGDFA=G.INGEST AND D.CNSDFA=G.CNSEST', null)
			->leftJoin('INVDES AS I', 'D.ELEDFA=I.REFDES', null)
			->where([
				'D.INGDFA'=>$this->aFactura['NIGING'],
				'D.NFADFA'=>$this->aFactura['FRACAB'],
				'D.DOCDFA'=>$this->aFactura['DOCFAC'],
				//'D.DOCDFA'=>$this->aFactura['DOCNOT'],
				'D.CFADFA'=>$this->aFactura['CONCAB'],
				'D.PLADFA'=>$this->aFactura['PLNCAB'], ])
			->in('D.TINDFA', array_keys($laTipoCons))
			->notLike('D.CUPDFA', 'C%')
			->groupBy('D.TINDFA, D.CNSDFA, D.ELEDFA, I.DESDES, D.VUNDFA, D.QCODFA, D.VPRDFA, G.RF4EST')
			->orderBy('D.TINDFA, D.ELEDFA, D.VUNDFA')
			->getAll('array');

		if ($this->oDb->numRows() > 0) {
			foreach ($laDetalles as $laDet) {
				$laDet = array_map('trim', $laDet);

				// Obtener CUM
				$lcCodigo = $laDet['CODIGO'];
				if ($laDet['NOPOS']=='NOPOS') {
					$this->aFactura['esNoPOS'] = true;
				}
				if ($laDet['TIPCONS']=='500') {
					if ($laDet['NOPOS']=='NOPOS' || $this->bEntidadEnviaCUM) {
						$lcCum = FuncionesInv::fObtenerCUM($this->aFactura['NIGING'], $laDet['CODIGO'], $laDet['CNSCONS'])['CUM'];
						$lcCodigo = empty($lcCum) ? $laDet['CODIGO'] : $lcCum;
					}
				}

				$lbExiste = false;
				$lnNumDet = -1;
				foreach ($this->aDetalles as $lnKey => $laDetalle) {
					if ( $laDetalle['codCentro']==$laTipoCons[$laDet['TIPCONS']][0]
						&& $laDetalle['tipoItem']==$laDet['TIPCONS']
						&& $laDetalle['codItem']==$lcCodigo
						&& $laDetalle['vrUnidad']==$laDet['VR_UNIDAD'] ) {
							$lbExiste = true;
							$lnNumDet = $lnKey;
							break;
						}
				}

				if ( $lbExiste ) {
					$this->aDetalles[$lnNumDet]['cantidad'] += $laDet['CANTIDAD'];
					$this->aDetalles[$lnNumDet]['vrDesc'] += $laDet['TOT_DESC'];
					$this->aDetalles[$lnNumDet]['vrTotal'] += $laDet['VR_TOTAL'] - $laDet['TOT_DESC'];

				} else {
					$this->aDetalles[] = [
						'codCentro'	=> $laTipoCons[$laDet['TIPCONS']][0],
						'dscCentro'	=> $laTipoCons[$laDet['TIPCONS']][1],
						'marca'		=> '0',
						'tipoItem'	=> $laDet['TIPCONS'],
						'codShaio'	=> $laDet['CODIGO'],
						'codItem'	=> $lcCodigo,
						'codAux'	=> '',
						'dscItem'	=> FeFunciones::quitarEspacios($laDet['DESCRIP']),
						'cantidad'	=> $laDet['CANTIDAD'],
						'vrUnidad'	=> $laDet['VR_UNIDAD'],
						'vrDesc'	=> $laDet['TOT_DESC'],
						'vrTotal'	=> $laDet['VR_TOTAL']-$laDet['TOT_DESC'],
						'vrUnid0'	=> 0,
					];
				}
			}
		}
	}


	/*
	 *	Consulta de detalles copago
	 */
	private function consultaDetalleCopago()
	{
		$lcTipoCons = '900';
		$laDetalles = $this->oDb
			->select('D.ELEDFA AS CODIGO, D.ECODFA AS CODPLAN, D.VUNDFA AS VR_UNIDAD, P.DSCCON AS DSCPLAN')
			->sum('D.QCODFA','CANTIDAD')
			->sum('D.VPRDFA','VR_TOTAL')
			->from('FACDETF AS D')
			->leftJoin('FACPLNC AS P', 'D.ECODFA=P.PLNCON', null)
			->where([
				'D.INGDFA'=>$this->aFactura['NIGING'],
				'D.NFADFA'=>$this->aFactura['FRACAB'],
				'D.DOCDFA'=>$this->aFactura['DOCFAC'],
				//'D.DOCDFA'=>$this->aFactura['DOCNOT'],
				'D.CFADFA'=>$this->aFactura['CONCAB'],
				'D.PLADFA'=>$this->aFactura['PLNCAB'],
				'D.TINDFA'=>$lcTipoCons ])
			->notLike('D.CUPDFA', 'C%')
			->groupBy('D.ELEDFA, D.ECODFA, D.VUNDFA, P.DSCCON')
			->orderBy('D.ELEDFA, D.ECODFA, D.VUNDFA')
			->getAll('array');

		if ($this->oDb->numRows() > 0) {

			foreach ($laDetalles as $laDet) {
				$laDet = array_map('trim', $laDet);
				$this->aDetalles[] = [
					'codCentro'	=> '',
					'dscCentro'	=> '',
					'marca'		=> '2',
					'tipoItem'	=> $lcTipoCons,
					'codItem'	=> $laDet['CODIGO'],
					'codAux'	=> '',
					'dscItem'	=> $laDet['DSCPLAN'],
					'cantidad'	=> 1,
					'vrUnidad'	=> $laDet['VR_TOTAL'],
					'vrDesc'	=> 0,
					'vrTotal'	=> $laDet['VR_TOTAL'],
					'vrUnid0'	=> $laDet['VR_TOTAL'],
				];
				if ($laDet['VR_TOTAL'] < 0) {
					$this->aFactura['COPAGO'] += abs($laDet['VR_TOTAL']);
				}
			}
			if ( $this->aFactura['COPAGO']>0 )
				$this->aFactura['COPAGO_OBS'] = 'A CARGO DEL PACIENTE: $'.number_format($this->aFactura['COPAGO'],2,',','.').' POR CONCEPTO DE '.$laDet['CODIGO'];
		}
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayFac()
	{
		$laFactura = [];

		$lbPrueba = $this->aCnf['ambiente']=='Pruebas';
		if ($lbPrueba) {
			$this->aFactura['FRACAB'] = strval($this->aFactura['FRACAB'] + $this->aCnf['facture']['Pruebas']['sumar']);
			$this->aFactura['NRONOT'] = strval($this->aFactura['NRONOT'] + $this->aCnf['facture']['Pruebas']['sumarnota']);
			$lcFechaEmision = date('Y-m-d');
			$lcHoraEmision = date('H:i:s');
			$lcFechaFactura = date('Y-m-d');

		} else {
			$lcFechaEmision = FeFunciones::formatFecha($this->aFactura['FEFCAB']);
			$lcHoraEmision = FeFunciones::formatHora($this->aFactura['HORCAB']);
			$lcFechaFactura = FeFunciones::formatFecha($this->aFactura['FEFFAC']);
		}

		$laPeriodoFact = $this->obtenerPeriodoFactura($this->aFactura['FRACAB']);
		$laFactura['fechaInicioFacturacion'] = FeFunciones::formatFecha($laPeriodoFact['inicial']);
		$laFactura['fechaFinFacturacion'] = FeFunciones::formatFecha($laPeriodoFact['final']);


		$lnNumDiasVence = intval($this->aFactura['FPGCON']);
		if (empty($lnNumDiasVence)) { $lnNumDiasVence = $this->aCnf['plazoVence']; }
		$lcFechaVence = date('Y-m-d', strtotime($lcFechaFactura."+ $lnNumDiasVence days"));

		$lbEsPersonaJurídica = $this->aAdquiriente['tipoPer'] == $this->aCnf['codTipoPer']['juridica'];;
		$lbHayDescuentos = $this->aFactura['VD1CAB'] > 0;
		$lbHayCopagoCMB = $this->aFactura['COPAGO'] > 0;
		$lbEsFacturaContingencia = false;
		$lcMoneda = $this->aCnf['divisa']; // Moneda utilizada para facturar

		// Observaciones
		$lcDescGlosa = 'Anulación de Factura No.' . $this->aFactura['FRACAB'];
		$lcObservaciones =  $lcDescGlosa . ' del ' . $lcFechaFactura
			. (!empty($this->aFactura['COPAGO_OBS']) ? $this->cSLD . $this->aFactura['COPAGO_OBS'] : '')
			. (empty($this->aFactura['ObsAdic']) ? '' : $this->cSLD . $this->aFactura['ObsAdic']);

		// Totales
		$lnCopagoReal = $this->aFactura['COPAGO'];
		$lnDescuento = $this->aFactura['VD1CAB'];
		$lnTotalBase = $this->aFactura['VAFCAB'];
		$lnTotalPagar = $lnTotalBase;
		$lnTotalBruto = $lnTotalBase + $lnDescuento;
		$lnCopagoAbonado = ($lnTotalBase - $lnCopagoReal<0) ? $lnTotalBase : $lnCopagoReal;
		$lnSaldoPagar = $lnTotalPagar - $lnCopagoAbonado;


		// Valor factura en letras
		$lcVrLetras = NumerosALetras::convertir($lnTotalPagar,['PESO','PESOS'],['CENTAVO','CENTAVOS']) . $this->aCnf['txtDespuesValorLetras'];

		// Fechas ingreso y egreso paciente
		$lcFechaIngreso = FeFunciones::formatFecha($this->aFactura['FEIING']);
		$lcHoraIngreso = FeFunciones::formatHora($this->aFactura['HORING']);
		$lcFechaEgreso = FeFunciones::formatFecha($this->aFactura['FEEING']);
		$lcHoraEgreso = FeFunciones::formatHora($this->aFactura['HREING']);

		// número de líneas
		$lnNumLin = 0;
		foreach ($this->aDetalles as $laDet) {
			if ( $laDet['marca']=='2' && $laDet['vrTotal']<0 ) continue; // omite copago en factura entidad
			$lnNumLin++;
		}

		// Nota es para Administrador de Cuentas Médicas de Transfiriendo
		$lcWhereCM = "CL1TMA='TRANSFIR' AND CL2TMA='NITCM' AND CL3TMA<>'' AND ESTTMA='' AND OP7TMA='{$this->aFactura['NITCAB']}' AND OP4TMA<={$this->aFactura['FEFCAB']} AND DE2TMA LIKE '%{$this->cTipoFac}%' ";
		$lbCuentaMedica = $this->oDb->obtenerTabMae1('OP1TMA', 'CMSOPORT', $lcWhereCM, null, '')=='1';
		$laFactura['Evento'] = $lbCuentaMedica ? 'CM' : 'FAC';

		if ($this->aCnf['obligar_CM']??false) {
			$this->aFactura['Evento'] = 'CM';
		}


		/********************* Cabecera *********************/
		$laFactura['Documento'] = [
			'Prefijo' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'],
			'NumDoc' => $this->aFactura['NRONOT'],
		];
		$laData = [
			'Prefijo' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'],
			'Numero' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'].$this->aFactura['NRONOT'],
			'FechaEmision' => $lcFechaEmision,
			'HoraEmision' => $lcHoraEmision,
			'FormaDePago' => $this->aAdquiriente['FormaDePago'],
			'MonedaNota' => $this->aCnf['divisa'],
			'Observaciones' => $lcObservaciones,
			'TipoOperacion' => $this->aCnf['tipoDoc'][$this->cTipDocXml][($this->bEsFacElectr ?'tipoOperacion':'tipoOperacionNoFe')],
			'LineasDeNota' => $lnNumLin,
		];
		if ($this->aAdquiriente['FormaDePago'] == $this->aCnf['codFormaPago']['credito']) $laData['Vencimiento'] = $lcFechaVence;
		$laFactura['Cabecera'] = $laData;


		/********************* ReferenciasNotas *********************/
		// $lcConcepto = $this->bEsFacElectr ? '2' : '5'; // 2-Anulación de factura electrónica, 5-Otros
		// $laData = [
		// 	'codn' => $lcConcepto,
		// 	'dscr' => $this->aCnf['conceptos']['NC'][$lcConcepto],
		// ];
		// if ($this->bEsFacElectr) $laData['fact'] = $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'].$this->aFactura['FRACAB'];
		// $laFactura['ReferenciasNotas'] = $laData;


		/********************* FacturasRelacionadas *********************/
		$laFactura['FacturasRelacionadas'] = [
			[
				'fact' => $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'].$this->aFactura['FRACAB'],
				'cufe' => $this->bEsFacElectr ? $this->aFactura['CUFE'] : ' ',
				'fech' => $lcFechaFactura,
				'cncp' => $this->bEsFacElectr ? '2' : '5', // 2-Anulación de factura electrónica, 5-Otros
			],
		];


// OJO ES OBLIGATORIO SI ES FACTURA EN CONTINGENCIA
		/********************* DocumentosAdicionalesReferencia *********************/
		// $laData = [
		// 	'Numero' => '',
		// 	'FechaEmision' => '',
		// 	'TipoDocumento' => '',
		// 	'UrlAdjunto' => '',
		// ];
		// $laFactura['DocumentosAdicionalesReferencia'] = $laData;


		/********************* Emisor *********************/
		$laFactura['Emisor'] = $this->oEmisor;


		/********************* Cliente *********************/
		if (!$lbEsPersonaJurídica && in_array($this->aAdquiriente['tipoId'], $this->aCnf['tiposIdConsFinal'])) {
			$laFactura['Cliente'] = $this->aCnf['cliente']['consumidorFinal'];
		} else {
			$laFactura['Cliente'] = [
				'razons' => trim($this->aAdquiriente['apellido'].' '.$this->aAdquiriente['nombre']),
				'nombre' => $this->aAdquiriente['nombre'],
				'apellido' => $this->aAdquiriente['apellido'],
				'tipoId' => $this->aCnf['tiposId'][$this->aAdquiriente['tipoId']],
				'numeId' => $this->aAdquiriente['numeId'],
				'tipoPer' => $this->aAdquiriente['tipoPer'],
				'direccion' => [
					'Direccion' => $this->aAdquiriente['direccion'],
					'CodigoPais' => $this->aAdquiriente['codpais'],
					'NombrePais' => $this->aAdquiriente['pais'],
					'IdiomaPais' => 'es',
					'CodigoDepartamento' => $this->aAdquiriente['coddepto'],
					'NombreDepartamento' => $this->aAdquiriente['depto'],
					'CodigoMunicipio' => $this->aAdquiriente['codciudad'],
					'NombreCiudad' => $this->aAdquiriente['ciudad'],
				],
				'respfiscal' => $this->aAdquiriente['respfiscal'],
				'tributo' => $this->aAdquiriente['tributo'],
				'contacto' => $this->aAdquiriente['contacto'],
				'telefono' => $this->aAdquiriente['telefono'],
				'correo' => $this->aAdquiriente['correo'],
			];
		}


		/********************* MediosDePago *********************/
		$laFactura['MediosDePago'] = [
			'medio' => $this->aAdquiriente['MedioDePago'],
			'forma' => $this->aAdquiriente['FormaDePago'],
			'fechv' => $lcFechaVence,
		];


		/********************* DescuentosOCargos *********************/
		if ($lbHayDescuentos) {
			$lnPorcentaje = $lnDescuento * 100 / $lnTotalBruto;
			$laFactura['DescuentosOCargos'] = [
				'id' => '1',
				'porcen' => number_format($lnPorcentaje,2,'.',''),
				'valor' => number_format($lnDescuento,2,'.',''),
				'vrbase' => number_format($lnTotalBruto,2,'.',''),
			];
		}


		/********************* Totales *********************/
		$laFactura['Totales'] = [
			'bruto' => number_format($lnTotalBruto,2,'.',''),
			'basei' => '0',
			'impst' => '0',
			'brimp' => number_format($lnTotalBase,2,'.',''),
			'descu' => number_format($lnDescuento,2,'.',''),
			'genrl' => number_format($lnTotalPagar,2,'.',''),
			'abono' => number_format($lnCopagoAbonado,2,'.',''),
		];


		/********************* Linea *********************/
		$laFactura['Lineas'] = [];
		$lnCns = 0;
		foreach ($this->aDetalles as $laDet) {
			if ( $laDet['marca']=='2' && $laDet['vrTotal']<0 ) continue; // omite copago en factura entidad
			$lnCns++;
			$lcVrTotal = number_format($laDet['cantidad']*$laDet['vrUnidad'],2,'.','');

			$laFactura['Lineas'][$lnCns] = [
				'cons' => $lnCns,
				'codg' => ($this->aCnf['parFac']['usarCodCentro'] ? (empty($laDet['codCentro']) ? '' : $laDet['codCentro'].'-') : '').$laDet['codItem'],
				'desc0' => $laDet['dscItem0'] ?? $laDet['dscItem'],
				'desc' => $laDet['dscItem'],
				'cant' => number_format($laDet['cantidad']*1,2,'.',''),
				'undm' => $this->aCnf['um'],
				'vrud' => number_format($laDet['vrUnidad'],2,'.',''),
				'vrtt' => $lcVrTotal,
				'cadd' => [
					'conceptoglosa'	=> $lcDescGlosa,
					'valorglosado'	=> $lcVrTotal,
					'unidad' => $this->aCnf['umdsc'],
				],
			];
			if (isset($laDet['subdetalle'])) $laFactura['Lineas'][$lnCns]['asubd'] = $laDet['subdetalle'];
		}


		/********************* Notificacion *********************/
		$lcMailNotifica = $lbPrueba ? $this->aCnf['mailPrueba'] : mb_strtolower($this->aAdquiriente['correo']);
		if (strlen($lcMailNotifica)>0) {
			$laFactura['Notificacion'] = $lcMailNotifica;
		}


		/********************* Extensiones *********************/
		$laFactura['Extensiones'] = [
			// Paciente
				'historiaclinica' => $this->aPaciente['numeId'],
				'documentointerno' => $this->aFactura['NIGING'],
				'paciente' => $this->aPaciente['nombre'].' '.$this->aPaciente['apellido'],
				'tipodocpaciente' => $this->aPaciente['tipoId'],
				'idpaciente' => $this->aPaciente['numeId'],
				'telefonopaciente' => $this->aFactura['TELPAC'],
				//'emailpaciente' => $this->aFactura['MAIPAC'],
				'direccionpaciente' => $this->aFactura['DR1PAC'].' - '.$this->aFactura['DR2PAC'],
				'tipoafiliado' => $this->aFactura['TIPAFI'],
				'plan' => $this->aFactura['PLNCAB'].' - '.$this->aFactura['DSCCON'],
				'poliza' => '', // Póliza no se guarda en AS400
				'autorizacion' => $this->aAdquiriente['autoriza'],
				'fechahoraingreso' => $lcFechaIngreso.' '.$lcHoraIngreso,
				'fechahoraegreso' => $lcFechaEgreso.' '.$lcHoraEgreso,
			// Nota
				'valorletras' => $lcVrLetras,
				'elaboradopor' => $this->aFactura['USRCAB'],
				'elaboronombre' => $this->aFactura['N_ELABORO'].' '.$this->aFactura['A_ELABORO'],
				'totalglosado' => $lnTotalBruto,
				'totalaceptado' => $lnTotalBruto,
		];

		/********************* InformacionAdicional *********************/
		$laFactura['InformacionAdicional'] = array_merge($this->informacionAdicional(),[
		//	'fechaInicioFacturacion'	=> ' ',
		//	'fechaFinFacturacion'		=> ' ',
			'FechaIngresoPaciente'		=> ' ',
			'FechaEgresoPaciente'		=> ' ',
			'TelefonoPaciente'			=> $this->aFactura['TELPAC'],
			'DireccionPaciente'			=> $this->aFactura['DR1PAC'].' - '.$this->aFactura['DR2PAC'],
			'TipoAfiliadoPaciente'		=> $this->aFactura['TIPAFI'],
			'PlanPaciente'				=> mb_substr($this->aFactura['PLNCAB'].' - '.$this->aFactura['DSCCON'], 0, 28),
		]);


		return $laFactura;
	}




	/*
	 *	Retorna información adicional de cabecera factura
	 */
	public function informacionAdicional()
	{
		return [
			'CodigoHabitacion'			=> $this->aCnf['CodMinSalud'],
			'NumeroIngreso'				=> $this->aFactura['NIGING'],
			'NumeroAutorizacion'		=> '('.$this->aAdquiriente['autoriza'].'|||)',
			'TipoDocumentoPaciente'		=> $this->aCnf['Salud']['tiposId'][$this->aPaciente['tipoId']][2],
			'NumeroDocumentoPaciente'	=> $this->aPaciente['numeId'],
			'PrimerApellidoPaciente'	=> $this->aPaciente['apellido1'],
			'SegundoApellidoPaciente'	=> $this->aPaciente['apellido2'],
			'PrimerNombrePaciente'		=> $this->aPaciente['nombre1'],
			'SegundoNombrePaciente'		=> $this->aPaciente['nombre2'],
			'FechaNacimientoPaciente'	=> FeFunciones::formatFecha($this->aFactura['FNAPAC']),
			'SexoPaciente'				=> $this->aFactura['SEXPAC'],
			'TipoServicio'				=> ' ',
			'NumeroAtencionConvenio'	=> ' ',
			'DiasEstancia'				=> ' ',
			'DiagnosticoIngreso'		=> ' ',
			'DiagnosticoEgreso'			=> ' ',
			'SociedadMedica'			=> ' ',
			'PolizaAtencion'			=> ' ',
			'NumeroHistoria'			=> $this->aFactura['NHCPAC'],
			'CodigoConvenio'			=> ' ',
			'Idpacienteconvenio'		=> ' ',
			'CuotaModeradora'			=> '0',
			'Copago'					=> '0',
			'CuotaRecuperacion'			=> '0',
			'tipoPlan'					=> ' ',
			'tipoContrato'				=> ' ',
			'tipoAtencion'				=> ' ',
			'NumeroFacturaReferencia'	=> ' ',
			'causaExterna'				=> ' ',
			'CodigoFacturador'			=> $this->aFactura['USRCAB'],
			'TipoDocumentoFacturador'	=> $this->aCnf['Salud']['tiposId'][$this->aFactura['TIDRGM']][2],
			'NumeroDocumentoFacturador'	=> $this->aFactura['NIDRGM'],
			'NombreFacturador'			=> $this->aFactura['N_ELABORO'],
			'ApellidoFacturador'		=> $this->aFactura['A_ELABORO'],
		];
	}


	/* Retorna array aError */
	public function aError()
	{
		return $this->aError;
	}

}