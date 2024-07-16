<?php
/* ********************  NOTA CRÉDITO GLOSAS  ******************** */
namespace NUCLEO;

require_once __DIR__ . '/class.FeConsultar.php';



class FeConsultar27NC extends FeConsultar
{
	protected $bEsFacElectr = true;


	/*
	 *	Constructor de la clase
	 */
	public function __construct($taConfig)
	{
		parent::__construct($taConfig);

		$this->cTipDocXml = 'NC';
		$this->cTipoFac = '27NC';
		$this->NitCumSiempre();
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayDatos($tnFactura=0, $tnNota=0, $tnDocAdj=0)
	{
		parent::crearArrayDatos();

		$this->consultaCabecera($tnFactura, $tnNota);
		if (count($this->aError)==0) $this->consultaValorFac();
		if (count($this->aError)==0) $this->consultaDetalle();
		return count($this->aError)>0 ? [] : $this->crearArrayFac();
	}


	/*
	 *	Consulta de cabecera de nota de anulación de factura asistencial.
	 */
	private function consultaCabecera($tcNumFac, $tcNumNota)
	{
		// Consulta cabecera de Nota
		$this->aFactura = $this->oDb
			->select('F.FRAGLC, F.FEFGLC, F.NOTGLC, F.CONGLC, F.NITGLC, F.PLNGLC, F.FMOGLC, F.HMOGLC, F.VLRACC, F.CONCGLC, F.OBSEGLC, F.USRGLC')
			->select('I.NIGING, I.TIDING, I.NIDING, I.FEIING, I.HORING, I.FEEING, I.HREING, TRIM(U.NNOMED) N_ELABORO, TRIM(U.NOMMED) A_ELABORO, U.TIDRGM, U.NIDRGM')
			->select('SUBSTR(S.DESPAI, LOCATE(\' - \', S.DESPAI)+3, 2) CODPAI, SUBSTR(S.DESPAI, 1, LOCATE(\' - \', S.DESPAI)) DESPAI, D.CODDEP, D.DESDEP, C.CODCIU, C.DESCIU')
			->select('P.NM1PAC, P.NM2PAC, P.AP1PAC, P.AP2PAC, P.DR1PAC, P.DR2PAC, P.TELPAC, P.MAIPAC, P.FNAPAC, P.SEXPAC, P.NHCPAC, T.FEFCAB, T.VAFCAB, IFNULL(M.CUFE,\'\') CUFE')
			->select('L.DSCCON, L.DCOCON, L.TL1CON, L.OBSCON, L.NI1CON, L.EMLCON, L.FPGCON, IFNULL(SUBSTR(A.DE1TMA,1,20),\'\') AS TIPAFI, NC.NUMEDA, NC.COPBDA, NC.MCOPDA')
			->from('AMGLCAB F')
			->innerJoin('RIAING  AS I', 'F.INGGLC=I.NIGING', null)
			->innerJoin('RIAPAC  AS P', 'I.TIDING=P.TIDPAC AND I.NIDING=P.NIDPAC', null)
			->innerJoin('FACPLNC AS L', 'F.PLNGLC=L.PLNCON', null)
			->leftJoin('FACPLNDA AS NC','F.PLNGLC=NC.PLANDA', null)
			->innerJoin('FACCABF AS T', 'F.FRAGLC=T.FRACAB AND F.INGGLC=T.INGCAB AND SUBSTR(F.FRAGLC,2,6)=T.DOCCAB', null)
			->leftJoin(' RIAEPP  AS E', 'I.TIDING=E.TIDEPP AND I.NIDING=E.NIDEPP AND F.PLNGLC=E.PLAEPP', null)
			->leftJoin( 'FEMOV   AS M', 'F.FRAGLC=M.FACT AND M.TIPR=\'06FA\'', null)
			->leftJoin( 'COMPAI  AS S', 'L.PA1CON=S.CODPAI', null)
			->leftJoin( 'COMDEP  AS D', 'L.PA1CON=D.PAIDEP AND L.DP1CON=D.CODDEP', null)
			->leftJoin( 'COMCIU  AS C', 'L.PA1CON=C.PAICIU AND L.DP1CON=C.DEPCIU AND L.CD1CON=C.CODCIU', null)
			->leftJoin( 'TABMAE  AS A', 'A.TIPTMA=\'DATING\' AND A.CL1TMA=\'2\' AND E.TIAEPP=A.CL2TMA', null)
			->leftJoin( 'RIARGMN AS U', 'F.USRGLC=U.USUARI', null)
			->where([ 'F.FRAGLC' => $tcNumFac, 'F.NOTGLC' => $tcNumNota ])
			->where('F.CONGLC', '<', 1000)
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

		// Validar la fecha de la factura
		$this->bEsFacElectr = $this->aFactura['FEFCAB'] >= $this->aCnf['parFac']['fechaInicio'];


		// Datos de paciente de investigación
		$laPacInvestiga = $this->oDb
			->select('TIIPIN, IDIPIN, A1IPIN, A2IPIN, N1IPIN, N2IPIN')
			->tabla('PACINV')
			->where([ 'INGPIN' => $this->aFactura['NIGING'] ])
			->getAll('array');

		if ($this->oDb->numRows() > 0) {
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
		// Obtener pasaporte
		if ($this->aPaciente['tipoId'] == 'P') {
			$laPasaporte = $this->oDb
				->select('OP5PAL')
				->tabla('PACALT')
				->where([ 'TIDPAL' => $this->aPaciente['tipoId'], 'NIDPAL' => $this->aPaciente['numeId'] ])
				->where('OP5PAL', '<>', '')
				->getAll('array');
			if ($this->oDb->numRows() > 0)
				$this->aPaciente['numeId'] = trim($laPasaporte[0]['OP5PAL']);
		}
		// Obtiene número de autorización
		$laNumAut = $this->oDb
			->select('TRIM(SUBSTR(DESAUS, 18)) NUMAUT')
			->from('AUTASE')
			->where(['INGAUS' => $this->aFactura['NIGING'] ])
			->where('INDAUS=3 AND CNLAUS=2 AND PGMAUS=\'AUT002\'')
			->orderBy('FECAUS DESC')
			->getAll('array');
		$lcNumAut = trim($laNumAut[0]['NUMAUT'] ?? '');

		// Códigos de Depto, Ciudad
		$lcCodDpto = str_pad($this->aFactura['CODDEP'], 2, '0', STR_PAD_LEFT);
		$lcCodCiud = $lcCodDpto . str_pad($this->aFactura['CODCIU'], 3, '0', STR_PAD_LEFT);


		// Datos del adquiriente
		$lbPacienteEsAdq = false;
		$this->aAdquiriente = [
			'nombre' => $this->aFactura['DSCCON'],
			'apellido' => '',
			'contacto' => '',
			'tipoId' => 'N',
			'numeId' =>  $this->aFactura['NITGLC'],
			'tipoPer' => $this->aCnf['codTipoPer']['juridica'],
			'direccion' => $this->aFactura['DCOCON'],
			'telefono' => $this->aFactura['TL1CON'],
			'correo' => $this->aFactura['EMLCON'],
			'autoriza' => $lcNumAut,
			'codpais' => $this->aFactura['CODPAI'],
			'pais' => $this->aFactura['DESPAI'],
			'coddepto' => $lcCodDpto,
			'depto' => $this->aFactura['DESDEP'],
			'codciudad' => $lcCodCiud,
			'ciudad' => $this->aFactura['DESCIU'],
		];

		if ($this->aFactura['CODPAI']=='CO') {
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
			->tabla('PRMTE1')
			->where([ 'TE1COD' => $lcNumDoc13 ])
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			$laPrmTer = array_map('trim', $laPrmTer['0']);
			$this->aAdquiriente['nombre'] = $laPrmTer['TE1SOC'];
			if ($laPrmTer['TE1PER']=='J') {
				$this->aAdquiriente['tipoPer'] = $this->aCnf['codTipoPer']['juridica'];
				//$this->aAdquiriente['tipoId'] = 'N';
			}
		}
		// Datos de contacto
		$laPrmTer = $this->oDb
			->select('TE2CIU, TE2DIR, TE2TEL, TE2COM, TE2LEG, TE2MAI')
			->tabla('PRMTE2')
			->where([ 'TE2COD' => $lcNumDoc13 ])
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			$laPrmTer = array_map('trim', $laPrmTer['0']);
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
		$lcOblig = $this->obligacionesNit($lcNumDoc13);
		$this->aAdquiriente['respfiscal'] = empty($lcOblig) ? $this->aCnf['cliente']['respfiscal'][$this->aAdquiriente['tipoPer']] : [ $lcOblig ];
		// Forma y Medio de Pago
		$this->aAdquiriente['FormaDePago'] = $this->aCnf['cliente']['formaPago'][$this->aAdquiriente['tipoPer']];
		$this->aAdquiriente['MedioDePago'] = $this->aCnf['cliente']['medioPago'][$this->aAdquiriente['tipoPer']];


		// Obtener observaciones especiales
		$this->aFactura['ObsAdic'] = '';
		if (!$lbPacienteEsAdq && $this->aAdquiriente['tipoPer']==$this->aCnf['codTipoPer']['juridica']) {
			$this->aFactura['ObsAdic'] = $this->observacionesEspeciales($this->aFactura['PLNGLC'], $this->aFactura['FRAGLC'], $this->aFactura['NUMEDA']);
		}

		$this->bEntidadEnviaCUM = in_array($this->aFactura['NITGLC'], $this->aNitCumSiempre);
	}


	/*
	 *	Consulta del valor de la factura
	 */
	private function consultaValorFac()
	{
		// Detalles de la factura
		$laDetalles = $this->oDb
			->select('D.TINDFA AS TIPO')
			->sum('D.VPRDFA','VR_TOTAL')
			->tabla('FACDETF AS D')
			->leftJoin('RIACUP AS C', 'D.CUPDFA=C.CODCUP', null)
			->leftJoin('PRMTAB AS P', 'P.TABTIP=\'004\' AND D.CCTDFA=P.TABCOD', null)
			->where([
				'D.INGDFA'=>$this->aFactura['NIGING'],
				'D.NFADFA'=>$this->aFactura['FRAGLC'],
				'D.DOCDFA'=>substr(str_pad($this->aFactura['FRAGLC'], 8, '0', STR_PAD_LEFT), 2, 6), ])
			->groupBy('D.TINDFA')
			->getAll('array');

		// Validar que exista la nota
		if ( $this->oDb->numRows()==0 ) {
			$this->aError = [
				'Num' => '010',
				'Dsc' => "No se encontró datos de la Factura número {$this->aFactura['FRAGLC']}.",
			];
			return;
		}

		$lnTotalFac = 0;
		foreach ($laDetalles AS $laDet) {
			if ( !( $laDet['TIPO']=='900' && $laDet['VR_TOTAL']<0 ) )
				$lnTotalFac += $laDet['VR_TOTAL'];
		}
		$this->aFactura['VRTOTALFAC'] = $lnTotalFac;
	}


	/*
	 *	Consulta de factura asistencial.
	 *	Detalle obtenido desde Estado de Cuenta por Cento de Costo y Tipo de Consumo.
	 *	Form INF022.INF013FDN()
	 */
	private function consultaDetalle()
	{
		$this->aDetalles = [];
		$laDetalles = $this->oDb
			->select('D.CCTDET, D.TIPDET, TRIM(D.CUPDET) CUPDET, D.ELEDET, D.QCODET, D.QGLDET, D.VUNDET, D.VLRADET, D.VLRGDET, D.OBS2DET, D.CONCDET, D.CNSDET')
			->select('TRIM(C.DESCUP) DESCUP, TRIM(C.DESCUP) DESCUP6, I.DESDES, CG.DESCONC, G.RF4EST AS NOPOS')
			->from('AMGLDET AS D')
			->leftJoin('RIACUP AS C', 'D.CUPDET=C.CODCUP', null)
			->leftJoin('RIACUP AS S', 'SUBSTR(D.CUPDET,1,6)=S.CODCUP', null)
			->leftJoin('INVDES AS I', 'D.ELEDET=I.REFDES', null)
			->leftJoin('AMCONC AS CG', 'D.CONCDET=CG.CODCONC', null)
			->leftJoin('RIAESTM AS G', 'D.INGDET=G.INGEST AND D.CNSDET=G.CNSEST', null)
			->where(['D.FRADET'=>$this->aFactura['FRAGLC'],
					 'D.CONDET'=>$this->aFactura['CONGLC'], ])
			->where('D.VLRADET','<>',0)
			->orderBy('D.CCTDET, D.TIPDET, D.CUPDET, D.ELEDET')
			->getAll('array');
		if ($this->oDb->numRows() > 0) {
			foreach ($laDetalles as $laDet) {
				$laDet = array_map('trim', $laDet);
				$lcTipoItem = $laDet['TIPDET'];
				$lcCodGlosa = $laDet['CONCDET'];

				$lcDscItem = FeFunciones::quitarEspacios($laDet['TIPDET']=='400' ? $laDet['DESCUP'] : $laDet['DESDES']);
				if ($laDet['TIPDET']=='400') {
					$lcCodItemC = $laDet['CUPDET'];
					if (strlen($lcCodItemC)>6) {
						$lcCodItem = substr($lcCodItemC,0,6);
						$lcDscItem = FeFunciones::quitarEspacios(FeFunciones::quitarAsteriscos($laDet['DESCUP6'])).$this->cSLD.'('.$lcCodItemC.' - '.$lcDscItem.')';
					} else {
						$lcCodItem = $lcCodItemC;
					}
				} elseif ($laDet['TIPDET']=='500' && ($laDet['NOPOS']=='NOPOS' || $this->bEntidadEnviaCUM)) {
					// Obtener CUM
					$lcCum = FuncionesInv::fObtenerCUM($this->aFactura['NIGING'], $laDet['ELEDET'], $laDet['CNSDET'])['CUM'];
					$lcCodItem = empty($lcCum) ? $laDet['ELEDET'] : $lcCum;
					$lcCodItemC = $lcCodItem;
				} else {
					$lcCodItem = $laDet['ELEDET'];
					$lcCodItemC = $lcCodItem;
				}

				$lnCantidad = $laDet['QGLDET']==0 ? $laDet['QCODET'] : $laDet['QGLDET'];
				$lnVrUnidad = $laDet['VLRADET']==$laDet['VUNDET']*$lnCantidad ? $laDet['VUNDET'] : $laDet['VLRADET'] / $lnCantidad;

				$lbExiste = false;
				foreach ($this->aDetalles as $lnKey => $laDetalle) {
					if (   $laDetalle['tipoItem']==$lcTipoItem
						&& $laDetalle['codItemC']==$lcCodItemC
						&& $laDetalle['codGlosa']==$lcCodGlosa
						&& $laDetalle['vrUnidad']==$lnVrUnidad ) {
							$lbExiste = true;
							$lnNumDet = $lnKey;
							break;
					}
				}

				if ($lbExiste) {
					$this->aDetalles[$lnNumDet]['cantidad']	+= $lnCantidad;
					$this->aDetalles[$lnNumDet]['vrTotal']	+= $laDet['VLRADET'];
					$this->aDetalles[$lnNumDet]['vrGlosado']+= $laDet['VLRGDET'];
				} else {
					$this->aDetalles[] = [
						'tipoItem'	=> $lcTipoItem,
						'codItem'	=> $lcCodItem,
						'codItemC'	=> $lcCodItemC,
						'dscItem'	=> $lcDscItem,
						'codGlosa'	=> $lcCodGlosa,
						'dscGlosa'	=> $lcCodGlosa . ' ' . FeFunciones::quitarEspacios($laDet['DESCONC'] . $this->cSLD . $laDet['OBS2DET']),
						'cantidad'	=> $lnCantidad,
						'vrUnidad'	=> $lnVrUnidad,
						'vrTotal'	=> $laDet['VLRADET'],
						'vrGlosado'	=> $laDet['VLRGDET'],
					];
				}
			}
		}


		// Validar que suma de detalle sea igual a total factura
		$lnTotalDet = $lnTotalGlosa = 0;
		foreach ( $this->aDetalles as $laDet ) {
			$lnTotalDet += $laDet['vrTotal'];
			$lnTotalGlosa += $laDet['vrGlosado'];
		}
		$lnTotalDet = abs($lnTotalDet);
		$this->aFactura['VLRTOTGLO'] = $lnTotalGlosa;

		if ( $lnTotalDet*1 !== abs($this->aFactura['VLRACC']*1)) {
			// Valores diferentes
			$this->aError = [
				'Num' => '050',
				'Dsc' => "El valor de la cabecera ($ {$this->aFactura['VLRACC']}) no coindice con la suma de los detalles ($ {$lnTotalDet}).",
			];
		}

		AplicacionFunciones::ordenarArrayMulti($this->aDetalles, ['tipoItem', 'codItem']);
	}


	/*
	 *	Organiza los datos del documento en el array Factura
	 */
	public function crearArrayFac()
	{
		$laFactura = [];

		$lbPrueba = $this->aCnf['ambiente']=='Pruebas';
		if ($lbPrueba) {
			$this->aFactura['FRAGLC'] = strval($this->aFactura['FRAGLC'] + $this->aCnf['facture']['Pruebas']['sumar']);
			$this->aFactura['NOTGLC'] = strval($this->aFactura['NOTGLC'] + $this->aCnf['facture']['Pruebas']['sumarnota']);
			$lcFechaEmision = date('Y-m-d');
			$lcHoraEmision = date('H:i:s');
			$lcFechaFactura = date('Y-m-d');

		} else {
			$lcFechaEmision = FeFunciones::formatFecha($this->aFactura['FMOGLC']);
			$lcHoraEmision = FeFunciones::formatHora($this->aFactura['HMOGLC']);
			$lcFechaFactura = FeFunciones::formatFecha($this->aFactura['FEFCAB']);
		}

		$laPeriodoFact = $this->obtenerPeriodoFactura($this->aFactura['FRAGLC']);
		$laFactura['fechaInicioFacturacion'] = FeFunciones::formatFecha($laPeriodoFact['inicial']);
		$laFactura['fechaFinFacturacion'] = FeFunciones::formatFecha($laPeriodoFact['final']);


		$lbEsPersonaJurídica = $this->aAdquiriente['tipoPer'] == $this->aCnf['codTipoPer']['juridica'];
		$lbEsFacturaContingencia = false;
		$lcMoneda = $this->aCnf['divisa']; // Moneda utilizada para facturar

		$lnNumDiasVence = intval($this->aFactura['FPGCON']);
		if (empty($lnNumDiasVence)) { $lnNumDiasVence = $this->aCnf['plazoVence']; }
		$lcFechaVence = date('Y-m-d', strtotime($lcFechaFactura."+ $lnNumDiasVence days"));

		// Observaciones
		$lcObservaciones = 'Me permito enviar a ustedes respuesta de su objeción sobre la Factura No. ' . $this->aFactura['FRAGLC'] .
							' del ' . FeFunciones::formatFecha($this->aFactura['FEFCAB']) .
							' valor facturado $' .  number_format($this->aFactura['VRTOTALFAC']*1,2,',','.') .
							(empty($this->aFactura['ObsAdic']) ? '' : $this->cSLD . $this->aFactura['ObsAdic']);

		// Totales
		$lnTotalPagar = $this->aFactura['VLRACC'];
		$lnBaseImponible = 0;
		$lnTotalBruto = $lnTotalPagar;
		$lnTotalBase = $lnTotalPagar;
		$lnDescuento = 0;
		$lnCopagoAbonado = 0;

		// Valor factura en letras
		$lcVrLetras = NumerosALetras::convertir($this->aFactura['VLRACC'],['PESO','PESOS'],['CENTAVO','CENTAVOS']) . $this->aCnf['txtDespuesValorLetras'];

		// Fechas ingreso y egreso paciente
		$lcFechaIngreso = FeFunciones::formatFecha($this->aFactura['FEIING']);
		$lcHoraIngreso = FeFunciones::formatHora($this->aFactura['HORING']);
		$lcFechaEgreso = FeFunciones::formatFecha($this->aFactura['FEEING']);
		$lcHoraEgreso = FeFunciones::formatHora($this->aFactura['HREING']);

		// Nota es para Administrador de Cuentas Médicas de Transfiriendo
		$lcWhereCM = "CL1TMA='TRANSFIR' AND CL2TMA='NITCM' AND CL3TMA<>'' AND ESTTMA='' AND OP7TMA='{$this->aFactura['NITGLC']}' AND OP4TMA<={$this->aFactura['FMOGLC']} AND DE2TMA LIKE '%{$this->cTipoFac}%' ";
		$lbCuentaMedica = $this->oDb->obtenerTabMae1('OP1TMA', 'CMSOPORT', $lcWhereCM, null, '')=='1';
		$laFactura['Evento'] = $lbCuentaMedica ? 'CM' : 'FAC';

		if ($this->aCnf['obligar_CM']??false) {
			$this->aFactura['Evento'] = 'CM';
		}


		/********************* Cabecera *********************/
		$laFactura['Documento'] = [
			'Prefijo' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'],
			'NumDoc' => $this->aFactura['NOTGLC'],
		];
		$laData = [
			'Prefijo' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'],
			'Numero' => $this->aCnf['tipoDoc'][$this->cTipDocXml]['prefijo'].$this->aFactura['NOTGLC'],
			'FechaEmision' => $lcFechaEmision,
			'HoraEmision' => $lcHoraEmision,
			'FormaDePago' => $this->aAdquiriente['FormaDePago'],
			'MonedaNota' => $this->aCnf['divisa'],
			'Observaciones' => $lcObservaciones,
			'TipoOperacion' => $this->aCnf['tipoDoc'][$this->cTipDocXml][($this->bEsFacElectr ?'tipoOperacion':'tipoOperacionNoFe')],
			'LineasDeNota' => count($this->aDetalles),
		];
		if ($this->aAdquiriente['FormaDePago'] == $this->aCnf['codFormaPago']['credito']) $laData['Vencimiento'] = $lcFechaVence;
		$laFactura['Cabecera'] = $laData;


		/********************* ReferenciasNotas *********************/
		// $lcConcepto = $this->bEsFacElectr ? '1' : '5'; // 1-Devolución parcial de los bienes y/o no aceptación parcial del servicio, 5-Otros
		// $laData = [
		// 	'codn' => $lcConcepto,
		// 	'dscr' => $this->aCnf['conceptos']['NC'][$lcConcepto],
		// ];
		// if ($this->bEsFacElectr) $laData['fact'] = $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'].$this->aFactura['FRACAB'];
		// $laFactura['ReferenciasNotas'] = $laData;


		/********************* FacturasRelacionadas *********************/
		$laFactura['FacturasRelacionadas'] = [
			[
				'fact' => $this->aCnf['resFactura'][$this->aCnf['ambiente']]['PrefijoNumeracion'].$this->aFactura['FRAGLC'],
				'cufe' => $this->aFactura['CUFE'],
				'fech' => $lcFechaFactura,
				'cncp' => '1', // 1-Devolución parcial de los bienes y/o no aceptación parcial del servicio
			],
		];


// OJO ES OBLIGATORIO SI ES FACTURA EN CONTINGENCIA
		/********************* DocumentosAdicionalesReferencia *********************/
		// $laFactura['DocumentosAdicionalesReferencia'] = [
		// 	'Numero' => '',
		// 	'FechaEmision' => '',
		// 	'TipoDocumento' => '',
		// 	'UrlAdjunto' => '',
		// ];


		/********************* Emisor *********************/
		$laFactura['Emisor'] = $this->oEmisor;


		/********************* Cliente *********************/
		$laFactura['Cliente'] = [
			'razons' => $this->aAdquiriente['nombre'],
			'nombre' => $this->aAdquiriente['nombre'],
			'apellido' => '',
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


		/********************* MediosDePago *********************/
		$laFactura['MediosDePago'] = [
			'medio' => $this->aAdquiriente['MedioDePago'],
			'forma' => $this->aAdquiriente['FormaDePago'],
			'fechv' => $lcFechaVence,
		];


		/********************* Totales *********************/
		$laFactura['Totales'] = [
			'bruto' => number_format($lnTotalBruto,2,'.',''),
			'basei' => number_format($lnBaseImponible,2,'.',''),
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
			$lnCns++;

			// Linea
			$laFactura['Lineas'][$lnCns] = [
				'cons' => $lnCns,
				'codg' => $laDet['codItem'],
				'desc' => $laDet['dscItem'],
				'cant' => number_format($laDet['cantidad']*1,2,'.',''),
				'undm' => $this->aCnf['um'],
				'vrud' => number_format($laDet['vrUnidad'],2,'.',''),
				'vrtt' => number_format($laDet['vrTotal'],2,'.',''),
				'cadd' => [
					'conceptoglosa'	=> $laDet['codGlosa']=='999' ? ' ' : (empty($laDet['dscGlosa']) ? ' ' : $laDet['dscGlosa']),
					'valorglosado'	=> number_format($laDet['vrGlosado']*1,2,',','.'),
					'unidad' => $this->aCnf['umdsc'],
				],
			];
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
				'plan' => $this->aFactura['PLNGLC'].' - '.$this->aFactura['DSCCON'],
				'poliza' => '', // Póliza no se guarda en AS400
				'autorizacion' => $this->aAdquiriente['autoriza'],
				'fechahoraingreso' => $lcFechaIngreso.' '.$lcHoraIngreso,
				'fechahoraegreso' => $lcFechaEgreso.' '.$lcHoraEgreso,
			// Nota
				'valorletras' => $lcVrLetras,
				'elaboradopor' => $this->aFactura['USRGLC'],
				'elaboronombre' => $this->aFactura['N_ELABORO'].' '.$this->aFactura['A_ELABORO'],
				'totalglosado' => $this->aFactura['VLRTOTGLO'],
				'totalaceptado' => $this->aFactura['VLRACC'],
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
			'PlanPaciente'				=> mb_substr($this->aFactura['PLNGLC'].' - '.$this->aFactura['DSCCON'], 0, 28),
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
			'CodigoFacturador'			=> $this->aFactura['USRGLC'],
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