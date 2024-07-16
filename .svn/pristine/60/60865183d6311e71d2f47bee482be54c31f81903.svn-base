<?php
namespace NUCLEO;
require_once __DIR__ .'/class.TextHC.php';
require_once __DIR__ . '/class.Conciliacion.php';
require_once __DIR__ . '/class.EscalaActividadFisica.php';
require_once __DIR__ . '/class.EscalasRiesgoSangrado.php';
require_once __DIR__ . '/class.ParametrosConsulta.php';
require_once __DIR__ . '/class.AntecedentesConsulta.php';
require_once __DIR__ . '/class.Diagnostico.php';

use NUCLEO\Conciliacion;
use NUCLEO\EscalaActividadFisica;
use NUCLEO\EscalasRiesgoSangrado;
use NUCLEO\ParametrosConsulta;
use NUCLEO\AntecedentesConsulta;
use NUCLEO\Diagnostico;

class ConsultaAval
{
	protected $oDb;
	protected $aDatosAval = [];
	protected $aDolor = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	public function ConsultaAvalHCGeneral($tnIngreso)
	{
		$laRetorna = ['error'=>'', 'datos'=>[]];

		if ($tnIngreso > 1000000 && $tnIngreso < 9999999) {
			$lcHoras = trim($this->oDb->obtenerTabMae1('OP3TMA', 'AVAL', 'CL1TMA=\'TIEMPO\' AND ESTTMA=\'\'', null, ''));


			//	CONSULTAS, EVOLUCIONES Y EVENTUALIDADES
			$laConsulta = $this->oDb
				->select('TRIM(C.TIPRIC) TIPRIC, C.INDRIC, C.CONRIC, C.FECRIC, C.HORRIC, C.CEVRIC, C.PGMRIC')
					->select('M.REGMED, IFNULL(TRIM(M.NOMMED)||\' \'||TRIM(M.NNOMED), \'\') NAPMED')
					->select('TRIM(T.TABDSC) TABDSC, M.CODRGM, TRIM(E.DESESP) DESCESP, C.OP1RIC')

					->select('C.OP1RIC, O.RMEORD, O.COAORD, P.DESCUP')

				->from('REINCA AS C')
					->innerJoin('PRMTAB  AS T', 'C.TIPRIC=T.TABCOD AND T.TABTIP=\'TIN\'', null)
					->leftJoin ('RIARGMN AS M', 'C.USRRIC=M.USUARI', null)
					->leftJoin ('RIAESPE AS E', 'M.CODRGM=E.CODESP', null)

					/*  CYAB  */
					->leftJoin('RIAORD  AS O', 'C.INGRIC=O.NINORD AND C.INDRIC=O.CCIORD')
					->leftJoin ('RIACUP  AS P', 'O.COAORD=P.CODCUP', null)
					/*  CYAB  */

					->where(['C.INGRIC'=>$tnIngreso])
					->where("C.ESTRIC='' ")
					->where("C.FECRIC * 1000000 + C.HORRIC > REPLACE(REPLACE(SUBSTR(CHAR(NOW() - {$lcHoras} HOURS), 0, 20), '.', '') ,'-', '')")
				->orderBy ('C.FECRIC DESC, C.HORRIC DESC')
				->getAll('array');
			if(is_array($laConsulta)){
				if(count($laConsulta)>0){
					foreach ($laConsulta as $laRegistro){
						$laRetorna['datos'][] = [
							'CONSEC'=>$laRegistro['CONRIC'],
							'INDICE'=>$laRegistro['INDRIC'],
							'REGIS' => $laRegistro['TABDSC'],
							'FECHA' => $laRegistro['FECRIC'],
							'HORA' => $laRegistro['HORRIC'],
							'USUARIO' => $laRegistro['NAPMED'],
							'TIPO' => $laRegistro['TIPRIC'],
							'LETRA' => $laRegistro['OP1RIC'],
							/*******/
							'CUP'		=> $laRegistro['COAORD'],
							'DESCUP'	=> $laRegistro['DESCUP'],
							'CODESP'	=> $laRegistro['CODRGM'],
							'DSCESP'	=> $laRegistro['DESCESP'],
							'RMEORD'	=> $laRegistro['RMEORD'],
						];
					}
				}
			}

			if(count($laRetorna['datos'])==0){
				$laRetorna['error'] = 'NO EXISTEN DATOS ';
			}
		} else {
			$laRetorna['error'] = "Número de ingreso $tnIngreso incorrecto";
		}
		return $laRetorna;
	}

	public function ConsultaAvalHCDetalle($tnIngreso=0, $tcTipo='HC', $tnConsecutivo=0)
	{
		$laDatos=[];
		$laDatosCV=[];
		$laDatosAF=[];
		$laCondiciones = ['INGHIN'=>$tnIngreso, 'CCOHIN'=>$tnConsecutivo];
		$laTempAval = $this->oDb
			->select('INDHIN AS INDICE, SUBHIN AS SUBINDICE, CODHIN AS CODIGO, CLNHIN AS LINEA, DESHIN AS DESCRIP,
					  OP1HIN, OP2HIN, OP5HIN AS DETALLE, OP6HIN, USRHIN AS USUARIO')
			->from('HISINT')
			->where($laCondiciones)
			->orderBy ('INDHIN, SUBHIN, CODHIN, CLNHIN')
			->getAll('array');

		// Datos examen fisico
		$laCondiciones = ['NIGEFI'=>$tnIngreso, 'CNSEFI'=>$tnConsecutivo];
		$laTempExamen = $this->oDb
			->from('EXFINT')
			->where($laCondiciones)
			->get('array');

		$this->aDatosAval = $this->IniciarDatosAvalHC();
		foreach ($laTempAval as $laAval){
			switch(true){
				case $laAval['INDICE']==5:
					$this->aDatosAval['MotivoC']['selTipoCausa'] = $laAval['SUBINDICE'];
					$this->OrganizarMotivo($laAval);
					break;
				case $laAval['INDICE']==6:
					$this->aDatosAval['edtxtPandemia'] .= $laAval['DESCRIP'];
					break;
				case $laAval['INDICE']==10 && $laAval['SUBINDICE']<15:
					$this->OrganizarRevision($laAval);
					break;
				case $laAval['INDICE']==10 && $laAval['SUBINDICE']==15:
					switch ($laAval['CODIGO']) {
						case 19:
							$laDatosAF = ['REGISTRO'=>trim($laAval['OP1HIN']),
										  'DATOS'=>trim($laAval['DETALLE']),
							];
							break;
						case 24:
							$laDatosCV[] = ['CODANT'=>4,
											'SANANT'=>24,
											'DESANT'=>trim($laAval['DESCRIP']),
											'OP5ANT'=>trim($laAval['DETALLE']),
							];
							break;
						default:
							$this->OrganizarAntecedentes($laAval);
							break;
					}
					break;
				case $laAval['INDICE']==17:
					$laDatos[] = [
						'NINAND' => $tnIngreso,
						'TIDAND' => '',
						'NIDAND' => '',
						'DESAND' => $laAval['DESCRIP'],
						'CODAND' => '17',
						'SANAND' => $laAval['SUBINDICE'],
						'INDAND' => $laAval['CODIGO'],
						'Informante' => '',
						'OP6AND' => trim($laAval['OP2HIN']),
						'FECAND' => 0,
						'HORAND' => 0,
						'PGMAND' => 'HCPPALWEB',
					] ;
					break;
				case $laAval['INDICE']==20 && $laAval['SUBINDICE']<=13:
					$this->OrganizarExamen($laAval);
					break;
				case $laAval['INDICE']==20 && $laAval['SUBINDICE']==20:
					$lcPreg = trim(substr($laAval['DESCRIP'],10,10));
					$lcResp = trim(substr($laAval['DESCRIP'],20,10));
					$lcPuntos = trim(substr($laAval['DESCRIP'],30,10));
					$lcTemp = str_pad(strval($lcPreg),2,'0',STR_PAD_LEFT);
					$this->aDatosAval['Nihss']['selNihss'.$lcTemp]=intval($lcResp);
					$this->aDatosAval['Nihss']['lblPunto'.$lcTemp]=intval($lcPuntos);
					$this->aDatosAval['Nihss']['txtPunto'.$lcTemp]=intval($lcPuntos);
					$this->aDatosAval['Nihss']['txtTotalN']+=intval($lcPuntos);
					break;
				case $laAval['INDICE']==25:
					$lcDescripDX = $lcDescripTipo = $lcDescripClase = $lcDescripTrata = '';
					$loDiagnostico = new Diagnostico();
					$lcDescripDX = $loDiagnostico->consultaDX(trim($laAval['OP2HIN']));
					$lcDescripTipo = $loDiagnostico->codigoTipoDX(trim($laAval['SUBINDICE']));
					$lcDescripClase = $loDiagnostico->codigoClaseDX(trim($laAval['CODIGO']));
					$lcDescripTrata = $loDiagnostico->codigoTratamientoDX(trim($laAval['OP6HIN']));
					$this->aDatosAval['Diagnostico'][] = [
						'CODIGO'=>trim($laAval['OP2HIN']),
						'DESCRIP'=>trim($lcDescripDX),
						'CODTIPO'=>trim($laAval['SUBINDICE']),
						'TIPO'=>trim($lcDescripTipo),
						'CODCLASE'=>trim($laAval['CODIGO']),
						'CLASE'=>trim($lcDescripClase),
						'CODTRATA'=>trim($laAval['OP6HIN']),
						'TRATA'=>trim($lcDescripTrata),
						'JUSTIFICACIONDESCARTE'=>'',
						'OBSER'=>trim($laAval['DESCRIP']),
					];
					break;
				case $laAval['INDICE']==30:
					$this->aDatosAval['Planmanejo']['txtAnalisisPlan'] .= $laAval['DESCRIP'];
					break;
				case $laAval['INDICE']==35: //escalas
					$this->OrganizarEscalas($laAval, 'HC');
					break;
				case $laAval['INDICE']==40:
					$this->aDatosAval['Planmanejo']['SelDoctorInforma'] = trim($laAval['DESCRIP']);
					break;
				case $laAval['INDICE']==50:
					$this->aDatosAval['Planmanejo']['SeltuvoElectro'] = 'Si';
					$this->aDatosAval['Planmanejo']['txtTuvoElectrocardiograma'] .= $laAval['DESCRIP'];
					break;
				case $laAval['INDICE']==90:
					$laTemp = explode("~", trim($laAval['DESCRIP']));
					$this->aDatosAval['Planmanejo']['SelModalidadGrupo'] = $laTemp[0]??'';
					$this->aDatosAval['Planmanejo']['SelAtencionDomiciliaria'] = $laTemp[1]??'';
					break;
			}
			$this->OrganizarOrdenH($tnIngreso,$tnConsecutivo,'HCPPALWEB');
		}

		// Vacuna COVID
		if(count($laDatosCV)>0){
			$loObjAval = new ParametrosConsulta();
			$laParCov19 =  $loObjAval->parVacunaCovid19();
			$this->aDatosAval['COVID']['VACUNA'] = $laParCov19['CONFIG']['codigo']??'';
			$this->aDatosAval['COVID']['VACUNAD'] = $laParCov19['CONFIG']['descrip']??'';
			$loObjAval = new AntecedentesConsulta();
			$laRespuesta = $loObjAval->ultimoAntecedente('', 0, true, $laDatosCV);
			$this->aDatosAval['COVID']['DATOS'] = ($laRespuesta[4][24]??[]);
		}

		// Actividad Fisica
		if(count($laDatosAF)>0){
			$loObjAval = new EscalaActividadFisica();
			$this->aDatosAval['Actividad'] =  $loObjAval->consultaRegistroActividad($tnIngreso, $laDatosAF);
			$this->aDatosAval['Actividad']['selRealizaActividad'] = $this->aDatosAval['Actividad']['respuesta'];
		}

		$loObjAval = new Conciliacion($laDatos,2);
		$this->aDatosAval['Conciliacion'] = $loObjAval->getDatosConciliacion();

		// Datos detallado del examen  fisico
		if(is_array($laTempExamen)){
			if(count($laTempExamen)>0){
				$this->aDatosAval['Examen']['txtEscalaG'] = $laTempExamen['GLCEFI'];
				$this->aDatosAval['Examen']['txtFC'] = $laTempExamen['FRCEFI'];
				$this->aDatosAval['Examen']['txtFR'] = $laTempExamen['FRREFI'];
				$this->aDatosAval['Examen']['txtMasaC'] = $laTempExamen['MASEFI'];
				$this->aDatosAval['Examen']['selNivelCE'] = $laTempExamen['NCNEFI'];
				$this->aDatosAval['Examen']['txtPeso'] = $laTempExamen['PSOEFI'];
				$this->aDatosAval['Examen']['txtSo2'] = trim($laTempExamen['FILEFI']);
				$this->aDatosAval['Examen']['txtSupC'] = $laTempExamen['SUPEFI'];
				$this->aDatosAval['Examen']['txtTAD'] = ($laTempExamen['DSDEFI']==0?'':$laTempExamen['DSDEFI']);
				$this->aDatosAval['Examen']['txtTalla'] = ($laTempExamen['TLLEFI']==0?'':$laTempExamen['TLLEFI']);
				$this->aDatosAval['Examen']['txtTAS'] = ($laTempExamen['SSDEFI']==0?'':$laTempExamen['SSDEFI']);
				$this->aDatosAval['Examen']['txtTemp'] = $laTempExamen['TPREFI'];
				if ($laTempExamen['TPTEFI'] > 0){
					$this->aDatosAval['Examen']['txtTempR'] = $laTempExamen['TPTEFI'];
				}
			}
		}

		// se debe quitar cuando guarde estado
		$laRetorna = ['error'=>''];
		$laRetorna = ['Datos'=>$this->aDatosAval];
		return $laRetorna;
	}

	public function OrganizarMotivo($taDatos)
	{
		if ($taDatos['LINEA']<1000){
			switch ($taDatos['CODIGO']) {
				case 1:
					$this->aDatosAval['MotivoC']['edtMotivo'] .= $taDatos['DESCRIP'];
					break;
				case 2:
					$this->aDatosAval['MotivoC']['edtEvento'] .= $taDatos['DESCRIP'];
					break;
				case 3:
					$this->aDatosAval['MotivoC']['selRemisionIPS'] = "Si";
					$this->aDatosAval['MotivoC']['edtRelacion'] .= $taDatos['DESCRIP'];
					break;
			}
		}else{
			switch ($taDatos['LINEA']) {

				case 1000:
					$laCampos = ['CL1TMA', 'CL2TMA', 'CL3TMA', 'CL4TMA','DE1TMA',
					 'DE2TMA', 'OP1TMA', 'OP2TMA', 'OP4TMA'];
					$laCondiciones = ['TIPTMA'=>'TORACICO', 'ESTTMA'=>' '];
					$laDolor = $this->oDb
						->select($laCampos)
						->from('TABMAE')
						->where($laCondiciones)
						->orderBy('INT(OP3TMA)')
						->getAll('array');
					if(is_array($laDolor) == true){
						foreach($laDolor as $laDolorToracico){
							$laDolorToracico = array_map('trim', $laDolorToracico);
							$this->aDolor[intval($laDolorToracico['OP4TMA'])] = array(
								'TIPO'=>$laDolorToracico['CL1TMA'],
								'CODIGO'=>$laDolorToracico['CL2TMA'],
								'NIVEL'=>intval($laDolorToracico['CL3TMA']),
								'OBJETO'=>$laDolorToracico['CL4TMA'],
								'NOMBRE'=>$laDolorToracico['DE1TMA'],
								'DESCRIP'=>$laDolorToracico['DE2TMA'],
								'VALOR'=>$laDolorToracico['OP1TMA'],
								'POSANT'=>$laDolorToracico['OP2TMA'],
								'LINEA'=>$laDolorToracico['OP4TMA'],
							);
						}
					}
					break;
				case 30201 :
					$this->aDatosAval['MotivoC']['txtSegundosD']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30202 :
					$this->aDatosAval['MotivoC']['txtMinutosD']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30203 :
					$this->aDatosAval['MotivoC']['txtHorasD']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30204 :
					$this->aDatosAval['MotivoC']['txtDiasD']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30301 :
					$this->aDatosAval['MotivoC']['txtIntensidad']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30601 :
					$this->aDatosAval['MotivoC']['txtSegundosE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30602 :
					$this->aDatosAval['MotivoC']['txtMinutosE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30603 :
					$this->aDatosAval['MotivoC']['txtHorasE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30604 :
					$this->aDatosAval['MotivoC']['txtDiasE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30605 :
					$this->aDatosAval['MotivoC']['txtSemanasE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30606 :
					$this->aDatosAval['MotivoC']['txtMesesE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				case 30607 :
					$this->aDatosAval['MotivoC']['txtAnosE']=intval(trim(substr($taDatos['DESCRIP'],42,8)));
					break;
				default :
					$lcDato = $this->aDolor[$taDatos['LINEA']]['OBJETO'];
					$this->aDatosAval['MotivoC'][$lcDato] = 1;
					break;
			}
		}
	}

	public function OrganizarAntecedentes($taDatos)
	{
		$laAntecedentes = [
			1=>'antPatologicos',
			3=>'antTransfusionales',
			4=>'antVacunas',
			6=>'antQuirurgicos',
			7=>'antTraumaticos',
			8=>'antAlergicos',
			10=>'antToxicos',
			12=>'antGineco',
			14=>'antFamiliares',
			18=>'antHospitalarios',
			19=>'antActividad',
			20=>'selDiscapacidad',
			21=>'txtEdadGestacional',
			22=>'txtNroPrenatales',
			25=>'antActividad',
		];

		if(trim($taDatos['CODIGO'])==20){
			$lcDescrip = trim(substr($taDatos['DESCRIP'],0,2));
			$this->aDatosAval['Antecedentes'][$laAntecedentes[trim($taDatos['CODIGO'])]] .= $lcDescrip;
			if($lcDescrip=='Si'){
				$lcInformacion = explode("¤", trim($taDatos['DESCRIP']));
				foreach($lcInformacion as $lnKey=>$lcValor){
					if($lnKey>0){
						$this->aDatosAval['Antecedentes']['chk'.$lcValor]=1;
					}
				}
			}
		}else{
			if (isset($this->aDatosAval['Antecedentes'][$laAntecedentes[trim($taDatos['CODIGO'])]])) {
				$this->aDatosAval['Antecedentes'][$laAntecedentes[trim($taDatos['CODIGO'])]] .= trim($taDatos['DESCRIP']);
			}
		}
	}

	public function OrganizarExamen($taDatos)
	{
		$lcDescrip = in_array(mb_substr($taDatos['DESCRIP'],0,1,'UTF-8'), ['0', '1']) ? mb_substr($taDatos['DESCRIP'],1,NULL,'UTF-8') : $taDatos['DESCRIP'] ;
		$taDatos['DESCRIP'] = $lcDescrip;

		switch ($taDatos['SUBINDICE']) {
			case 0 :
				$this->aDatosAval['Examen']['edtEstadoGeneral'] .= $taDatos['DESCRIP'];
				break;
			case 7 :
				$this->aDatosAval['Examen']['exAbdomen'] .= $taDatos['DESCRIP'];
				break;
			case 8 :
				$this->aDatosAval['Examen']['exGenito'] .= $taDatos['DESCRIP'];
				break;
			case 9 :
				$this->aDatosAval['Examen']['exExtremidades'] .= $taDatos['DESCRIP'];
				break;
			case 10 :

				switch ($taDatos['CODIGO']) {

					case 1 :
						$this->aDatosAval['Examen']['exMotor'] .= $taDatos['DESCRIP'];
						break;
					case 2 :
						$this->aDatosAval['Examen']['exSensitivo'] .= $taDatos['DESCRIP'];
						break;
					case 4 :
						$this->aDatosAval['Examen']['exMental'] .= $taDatos['DESCRIP'];
						break;
					case 5 :
						$this->aDatosAval['Examen']['exCraneales'] .= $taDatos['DESCRIP'];
						break;
					case 6 :
						$this->aDatosAval['Examen']['exReflejos'] .= $taDatos['DESCRIP'];
						break;
					case 7 :
						$this->aDatosAval['Examen']['exMeningeos'] .= $taDatos['DESCRIP'];
						break;
					case 8 :
						$this->aDatosAval['Examen']['exNeurovascular'] .= $taDatos['DESCRIP'];
						break;
				}
				break;
			case 11 :
				$this->aDatosAval['Examen']['exCabeza'] .= $taDatos['DESCRIP'];
				break;
			case 12 :
				$this->aDatosAval['Examen']['exTorax'] .= $taDatos['DESCRIP'];
				break;
			case 13 :
				$this->aDatosAval['Examen']['exOrganos'] .= $taDatos['DESCRIP'];
				break;
		}

		$this->aDatosAval['Examen']['edtEstadoGeneral'] = trim($this->aDatosAval['Examen']['edtEstadoGeneral']);
		$this->aDatosAval['Examen']['exAbdomen'] = trim($this->aDatosAval['Examen']['exAbdomen']);
		$this->aDatosAval['Examen']['exGenito'] = trim($this->aDatosAval['Examen']['exGenito']);
		$this->aDatosAval['Examen']['exExtremidades'] = trim($this->aDatosAval['Examen']['exExtremidades']);
		$this->aDatosAval['Examen']['exMotor'] = trim($this->aDatosAval['Examen']['exMotor']);
		$this->aDatosAval['Examen']['exSensitivo'] = trim($this->aDatosAval['Examen']['exSensitivo']);
		$this->aDatosAval['Examen']['exMental'] = trim($this->aDatosAval['Examen']['exMental']);
		$this->aDatosAval['Examen']['exCraneales'] = trim($this->aDatosAval['Examen']['exCraneales']);
		$this->aDatosAval['Examen']['exReflejos'] = trim($this->aDatosAval['Examen']['exReflejos']);
		$this->aDatosAval['Examen']['exMeningeos'] = trim($this->aDatosAval['Examen']['exMeningeos']);
		$this->aDatosAval['Examen']['exNeurovascular'] = trim($this->aDatosAval['Examen']['exNeurovascular']);
		$this->aDatosAval['Examen']['exCabeza'] =trim($this->aDatosAval['Examen']['exCabeza']);
		$this->aDatosAval['Examen']['exTorax'] = trim($this->aDatosAval['Examen']['exTorax']);
		$this->aDatosAval['Examen']['exOrganos'] = trim($this->aDatosAval['Examen']['exOrganos']);
	}

	public function OrganizarRevision($taDatos)
	{
		switch ($taDatos['SUBINDICE']) {

			case 1:
				$this->aDatosAval['Revision']['sisVisual'] .= $taDatos['DESCRIP'];
				break;
			case 2:
				$this->aDatosAval['Revision']['sisOtorrino'] .= $taDatos['DESCRIP'];
				break;
			case 3:
				$this->aDatosAval['Revision']['sisPulmonar'] .= $taDatos['DESCRIP'];
				break;
			case 4:
				$this->aDatosAval['Revision']['sisCardiovascular'] .= $taDatos['DESCRIP'];
				break;
			case 5:
				$this->aDatosAval['Revision']['sisGastrointestinal'] .= $taDatos['DESCRIP'];
				break;
			case 6:
				$this->aDatosAval['Revision']['sisGenitourinario'] .= $taDatos['DESCRIP'];
				break;
			case 7:
				$this->aDatosAval['Revision']['sisEndocrino'] .= $taDatos['DESCRIP'];
				break;
			case 8:
				$this->aDatosAval['Revision']['sisHematologico'] .= $taDatos['DESCRIP'];
				break;
			case 9:
				$this->aDatosAval['Revision']['sisDermatologico'] .= $taDatos['DESCRIP'];
				break;
			case 10:
				$this->aDatosAval['Revision']['sisOseo'] .= $taDatos['DESCRIP'];
				break;
			case 11:
				$this->aDatosAval['Revision']['sisNervioso'] .= $taDatos['DESCRIP'];
				break;
			case 12:
				$this->aDatosAval['Revision']['sisSiquico'] .= $taDatos['DESCRIP'];
				break;
		}

		$this->aDatosAval['Revision']['sisVisual'] = trim($this->aDatosAval['Revision']['sisVisual']);
		$this->aDatosAval['Revision']['sisOtorrino'] = trim($this->aDatosAval['Revision']['sisOtorrino']);
		$this->aDatosAval['Revision']['sisPulmonar'] = trim($this->aDatosAval['Revision']['sisPulmonar']);
		$this->aDatosAval['Revision']['sisCardiovascular'] = trim($this->aDatosAval['Revision']['sisCardiovascular']);
		$this->aDatosAval['Revision']['sisGastrointestinal'] = trim($this->aDatosAval['Revision']['sisGastrointestinal']);
		$this->aDatosAval['Revision']['sisGenitourinario'] = trim($this->aDatosAval['Revision']['sisGenitourinario']);
		$this->aDatosAval['Revision']['sisEndocrino'] = trim($this->aDatosAval['Revision']['sisEndocrino']);
		$this->aDatosAval['Revision']['sisHematologico'] = trim($this->aDatosAval['Revision']['sisHematologico']);
		$this->aDatosAval['Revision']['sisDermatologico'] = trim($this->aDatosAval['Revision']['sisDermatologico']);
		$this->aDatosAval['Revision']['sisOseo'] = trim($this->aDatosAval['Revision']['sisOseo']);
		$this->aDatosAval['Revision']['sisNervioso'] = trim($this->aDatosAval['Revision']['sisNervioso']);
		$this->aDatosAval['Revision']['sisSiquico'] = trim($this->aDatosAval['Revision']['sisSiquico']);
	}


	public function OrganizarEscalas($taDatos=[], $tcTipo='HC')
	{
		if($tcTipo=='HC'){
			$laDatos = ['DESEHC'=> $taDatos['DESCRIP'],
						'OP5EHC'=> trim($taDatos['DETALLE'])
				   ];
			$lcTipoEscala = $taDatos['SUBINDICE'];
		}else{
			$laDatos = ['DESEHC'=> trim($taDatos['DETALLE']),
						'OP5EHC'=> $taDatos['DESCRIP']
		   ];
			$lcTipoEscala = $taDatos['CONPRO'];
		}

		$loObjAval = new EscalasRiesgoSangrado();
		$laDatosEsc = $loObjAval->ConsultarEscalaSangrado(0, 0,  $laDatos);
		switch ($lcTipoEscala) {
			case 1:
				$this->aDatosAval['escalaHasbled']=$laDatosEsc;
				$this->aDatosAval['escalaHasbled']['EXISTE'] =  (count($laDatosEsc)==0?'NO':'SI');
				break;
			case 2:
				$this->aDatosAval['escalaChadsvas']=$laDatosEsc;
				$this->aDatosAval['escalaChadsvas']['EXISTE'] =  (count($laDatosEsc)==0?'NO':'SI');
				break;
			case 3:
				$this->aDatosAval['escalaCrusade']=$laDatosEsc;
				$this->aDatosAval['escalaCrusade']['EXISTE'] =  (count($laDatosEsc)==0?'NO':'SI');
				break;
			case 4:
				$this->aDatosAval['escalaSadPersons']=$laDatosEsc;
				$this->aDatosAval['escalaSadPersons']['EXISTE'] =  (count($laDatosEsc)==0?'NO':'SI');
				break;
		}
	}


	public function OrganizarOrdenH($tnIngreso=0,$tnConsecutivo=0,$tcPrograma='')
	{
		if(!empty($tnIngreso)){
			$laCondiciones = ['INGHIS'=>$tnIngreso, 'CONHIS'=>$tnConsecutivo, 'PCRHIS'=>$tcPrograma];
			$laTemp = $this->oDb
				->select('TRIM(A.ESPHIS) ESPEC, TRIM(A.REGHIS) REGISTRO, TRIM(A.AREHIS) AREA, TRIM(A.UBIHIS) UBICA, TRIM(A.OBSHIS) AS OBSERVA, TRIM(MC.NOMMED) AS NOMMEDCRE, TRIM(MC.NNOMED) AS NNOMEDCRE, TRIM(E.DESESP) AS NOMESPEC')
					->from('ORDHIS AS A')
					->leftJoin('RIARGMN5 AS MC', 'A.REGHIS=MC.REGMED', null)
					->leftJoin('RIAESPE AS E', 'A.ESPHIS=E.CODESP', null)
					->where($laCondiciones)
					->orderBy('A.FCRHIS DESC')
					->get('array');
			$laTemp['EXISTE'] = $this->oDb->numRows()>0?true:false;
			$this->aDatosAval['OrdenH'] = $laTemp;
		}
	}

	public function IniciarDatosAvalHC()
	{
		return [
			'MotivoC'			    => [
				'edtMotivo'		    => '',
				'edtEvento'	        => '',
				'edtRelacion'	    => '',
				'selRemisionIPS'	    => 'No',
			],
			'edtxtPandemia'		    => '',
			'Antecedentes'          => [
				'antPatologicos'    => '',
				'antTransfusionales'=> '',
				'antVacunas'        => '',
				'antQuirurgicos'    => '',
				'antTraumaticos'    => '',
				'antAlergicos'      => '',
				'antToxicos'        => '',
				'antGineco'         => '',
				'antFamiliares'     => '',
				'antHospitalarios'  => '',
				'selDiscapacidad'   => '',
				'txtEdadGestacional' => '',
				'txtNroPrenatales'  => '',
				'antActividad'      => '',
			],
			'Conciliacion'          => '',
			'Examen'                => [
				'edtEstadoGeneral'            => '',
				'exAbdomen'         => '',
				'exCabeza'          => '',
				'exCraneales'        => '',
				'exExtremidades'    => '',
				'exGenito'          => '',
				'exMeningeos'       => '',
				'exMental'          => '',
				'exMotor'           => '',
				'exNeurovascular'   => '',
				'exOrganos'         => '',
				'exReflejos'        => '',
				'exSensitivo'       => '',
				'exTorax'           => '',
			],
			'Revision'              => [
				'sisCardiovascular' => '',
				'sisDermatologico'  => '',
				'sisEndocrino'      => '',
				'sisGastrointestinal'=> '',
				'sisGenitourinario' => '',
				'sisHematologico'   => '',
				'sisNervioso'       => '',
				'sisOseo'           => '',
				'sisOtorrino'       => '',
				'sisPulmonar'       => '',
				'sisSiquico'        => '',
				'sisVisual'         => '',
			],
			'Planmanejo'            => [
				'txtAnalisisPlan'   => '',
				'txtTuvoElectrocardiograma'=>'',
				'SeltuvoElectro'    => 'No',
				'SelDoctorInforma'  => 'No',
			],
			'Nihss'                 => [
					'txtTotalN'     => 0,

			],
		 ];
	}


	public function IniciarDatosAvalEV()
	{
		return [
			'edtxtPandemia'		        => '',
			'Analisis'			        => [
				'edtManejo'		        => '',
				'edtAnalisis'	        => '',
				'edtExamenFisicoUci'    => '',
				'edtManejoActualUci'    => '',
				'edtExamenSolicitarUci' => '',
				'edtPronosticoUci'      => '',
				'edtObjetivo'           => '',
			],
			'DatosCieCups'		        => [],
			'Nihss'                     => [
				'txtTotalN'             => 0,
			],
			'escalaHasbled'             =>[],
			'escalaChadsvas'            =>[],
			'escalaCrusade'             =>[],
			'RegistroUci'               => [
				'edtAntecedentesUci'    => '',
				'edtSubjetivoUci'       => '',
				'edtResultadosLaboratorioUci' => '',
				'edtEcgUci'             => '',
				'edtRxToraxUci'         => '',
				'edtGasimetriaAvUci'    => '',
				'edtPerfilHemodinamicoUci'    => '',
			],
		];
	}


	public function ConsultaAvalEVDetalle($tnIngreso=0, $tcTipo='EV', $tnConsecutivo=0)
	{
		$laDatosAF=[];
		$lcPrg='';
		$laTempObs = $this->oDb
			->select('TIPOBS, DESOBS AS DESCRIP, TRIM(CIEOBS) AS DX')
			->from('REINOB')
			->where(['INGOBS'=>$tnIngreso, 'CONOBS'=>$tnConsecutivo])
			->orderBy ('TIPOBS, CIEOBS')
			->getAll('array');

		$laTempAval = $this->oDb
			->select('CEXRID AS CONPRO, CORRID AS CONORD, CLIRID AS LINEA, INDRID AS INDICE, IN2RID AS INDICE2,
						TRIM(DIARID) AS DX, TRIM(DESRID) AS DESCRIP, TRIM(OP5RID) AS DETALLE, OP3RID AS OPCION, PGMRID, OP1RID')
			->from('REINDE')
			->where(['INGRID'=>$tnIngreso, 'CONRID'=>$tnConsecutivo])
			->orderBy ('CLIRID, INDRID, IN2RID')
			->getAll('array');

		if($tcTipo=='E'){
			$this->aDatosAval['Eventualidad']['edtEventualidad'] = '';
			$this->aDatosAval['Eventualidad']['edtAnalisisE'] = '';
			foreach ($laTempAval as $laAval){
				switch(true){
					case  $laAval['LINEA'] >= 5000 && $laAval['LINEA'] < 5500 :
						$this->aDatosAval['Eventualidad']['edtEventualidad'] .= $laAval['DESCRIP'];
						break;

					case $laAval['LINEA'] >= 5500 && $laAval['LINEA'] <  5999 :
						$this->aDatosAval['Eventualidad']['edtAnalisisE'] .= $laAval['DESCRIP'];
						break;
				}
			}
		}else{
			$this->aDatosAval = $this->IniciarDatosAvalEV();
			foreach ($laTempAval as $laAval){
				switch(true){
					case ($laAval['INDICE']==1 && $laAval['INDICE2']==2):
						$this->aDatosAval['Analisis']['edtObjetivo'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==2 && $laAval['INDICE2']==1200):
						$this->aDatosAval['RegistroUci']['edtAntecedentesUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==3 && $laAval['INDICE2']==1300):
						$this->aDatosAval['RegistroUci']['edtResultadosLaboratorioUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==4 && $laAval['INDICE2']==1400):
						$this->aDatosAval['RegistroUci']['edtEcgUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==5 && $laAval['INDICE2']==1500):
						$this->aDatosAval['RegistroUci']['edtRxToraxUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==6 && $laAval['INDICE2']==1600):
						$this->aDatosAval['RegistroUci']['edtGasimetriaAvUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==7 && $laAval['INDICE2']==1700):
						$this->aDatosAval['RegistroUci']['edtPerfilHemodinamicoUci'] .= $laAval['DESCRIP'];
						break;

					case $laAval['INDICE']==13 :
						$this->aDatosAval['Analisis']['edtManejo'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==8 && $laAval['INDICE2']==1800):
						$this->aDatosAval['RegistroUci']['edtSubjetivoUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==9 && $laAval['INDICE2']==1900):
						$lcTemp = $laAval['DESCRIP'];
						$this->aDatosAval['Analisis']['txtFcUci'] = (intval(substr($lcTemp,6,3))==0?'':intval(substr($lcTemp,6,3)));
						$this->aDatosAval['Analisis']['txtFrUci'] = (intval(substr($lcTemp,20,3))==0?'':intval(substr($lcTemp,20,3)));
						$this->aDatosAval['Analisis']['txtPasUci'] = (intval(substr($lcTemp,36,3))==0?'':intval(substr($lcTemp,36,3)));
						$this->aDatosAval['Analisis']['txtPadUci'] = (intval(substr($lcTemp,52,3))==0?'':intval(substr($lcTemp,52,3)));
						$this->aDatosAval['Analisis']['txtPamUci'] = (intval(substr($lcTemp,68,3))==0?'':intval(substr($lcTemp,68,3)));
						$this->aDatosAval['Analisis']['txtPvcUci'] = (intval(substr($lcTemp,84,3))==0?'':intval(substr($lcTemp,84,3)));
						$this->aDatosAval['Analisis']['txtPcpUci'] = (intval(substr($lcTemp,100,3))==0?'':intval(substr($lcTemp,100,3)));
						$this->aDatosAval['Analisis']['txtIcUci'] = (intval(substr($lcTemp,114,3))==0?'':intval(substr($lcTemp,114,3)));
						break;

					case ($laAval['INDICE']==10 && $laAval['INDICE2']==2000):
						$this->aDatosAval['Analisis']['edtExamenFisicoUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==11 && $laAval['INDICE2']==2100):
						$this->aDatosAval['Analisis']['edtManejoActualUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==14 && $laAval['INDICE2']==3100):
						$this->aDatosAval['Analisis']['edtExamenSolicitarUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==15 && $laAval['INDICE2']==19):
						$laDatosAF = [
							'REGISTRO'=>trim($laAval['OP1RID']),
							'DATOS'=>trim($laAval['DESCRIP']),
						];
						break;

					case ($laAval['INDICE']==15 && $laAval['INDICE2']==3500):
						$lcTemp = $laAval['DESCRIP'];
						$this->aDatosAval['Analisis']['txtApacheUci'] = intval(substr($lcTemp,8,3));
						$this->aDatosAval['Analisis']['txtSofaUci'] = intval(substr($lcTemp,22,3));
						$this->aDatosAval['Analisis']['txtParsonettUci'] = intval(substr($lcTemp,41,3));
						$this->aDatosAval['Analisis']['txtPocasUci'] = intval(substr($lcTemp,56,3));
						break;
					case ($laAval['INDICE']==16 && $laAval['INDICE2']==3600):
						$lcTemp = $laAval['DESCRIP'];
						$this->aDatosAval['Analisis']['txtTimiUci'] = intval(substr($lcTemp,6,3));
						$this->aDatosAval['Analisis']['txtTissUci'] = intval(substr($lcTemp,23,3));
						break;

					case ($laAval['INDICE']==17 && $laAval['INDICE2']==3700):
						$this->aDatosAval['Analisis']['edtPronosticoUci'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==18 && $laAval['INDICE2']==5500):
						$this->aDatosAval['Analisis']['edtAnalisis'] .= $laAval['DESCRIP'];
						break;

					case ($laAval['INDICE']==19 && $laAval['INDICE2']==3800):
						$lcTemp = trim($laAval['DESCRIP']);
						$this->aDatosAval['DatosCieCups']['codigoProcedimientoUci'] = substr($lcTemp,0,15);
						if (intval(trim(substr($lcTemp,16,5)))==1){
							$this->aDatosAval['DatosCieCups']['chkTotUci'] = 1;
						}
						if (intval(trim(substr($lcTemp,22,5)))==1){
							$this->aDatosAval['DatosCieCups']['chkCvcUci'] = 1;
						}
						if (intval(substr($lcTemp,28,5))==1){
							$this->aDatosAval['DatosCieCups']['chkSvUci'] = 1;
						}
						if (intval(substr($lcTemp,34,5))==1){
							$this->aDatosAval['DatosCieCups']['chkNingunoUci'] = 1;
						}
						$this->aDatosAval['DatosCieCups']['selInfeccionUci'] = (trim(substr($lcTemp,40,5))=='S'?'Si':'No');
						$this->aDatosAval['DatosCieCups']['selNefroproteccionUci'] = (trim(substr($lcTemp,46,5))=='S'?'Si':'No');
						$this->aDatosAval['DatosCieCups']['selProfilaxisUci'] = (trim(substr($lcTemp,52,5))=='S'?'Si':'No');
						$this->aDatosAval['DatosCieCups']['txtEuroescoreUci'] = intval(substr($lcTemp,57,5));
						$latemp =  explode(' - ', $lcTemp);
						$this->aDatosAval['DatosCieCups']['descripcionProcedimientoUci'] = $latemp[1]??'' ;
						break;
					case ($laAval['INDICE']==20 && $laAval['INDICE2']==3830):
						$lcTemp = trim($laAval['DESCRIP']);
						if(trim($lcTemp)=='NINGUNA'){
							$this->aDatosAval['DatosCieCups']['chkSinComplicaciones']=1;
						}else{
							$this->aDatosAval['DatosCieCups']['ListadoComplicaciones'] = explode('  ', $lcTemp);
						}
						break;

					case ($laAval['INDICE']==4 && $laAval['INDICE2']==5991):
						$laTemp = explode(' - ', trim($laAval['DESCRIP']));
						$lcObservacion = $lcJustificacion = '';
						foreach ($laTempObs as $laObserva){
							if($laObserva['TIPOBS']=='OB' && $laObserva['DX']==$laTemp[0]){
								$lcObservacion .=  $laObserva['DESCRIP'];
							}
							if($laObserva['TIPOBS']=='JD' && $laObserva['DX']== $laTemp[0]){
								$lcJustificacion .=  $laObserva['DESCRIP'];
							}
						}
						$lcDescripDX = $lcDescripTipo = $lcDescripClase = $lcDescripTrata = '';
						$loDiagnostico = new Diagnostico();
						$lcDescripTipo = $loDiagnostico->codigoTipoDX(trim($laTemp[2]));
						$lcDescripClase = $loDiagnostico->codigoClaseDX(trim($laTemp[3]));
						$lcDescripTrata = $loDiagnostico->codigoTratamientoDX(trim($laTemp[4]));
						$this->aDatosAval['Diagnostico'][] = [
							'CODIGO'=>trim($laTemp[0]),
							'DESCRIP'=>trim($laTemp[1]),
							'CODTIPO'=>trim($laTemp[2]??''),
							'TIPO'=>trim($lcDescripTipo),
							'CODCLASE'=>trim($laTemp[3]??''),
							'CLASE'=>trim($lcDescripClase),
							'CODTRATA'=>trim(($laTemp[4]??'')),
							'TRATA'=>trim($lcDescripTrata),
							'OBSER'=>trim($lcObservacion),
							'JUSTIFICACIONDESCARTE'=>trim($lcJustificacion),
						];
						break;

					case $laAval['INDICE']==20 && $laAval['INDICE2']==20:
						$lcPreg = trim(substr($laAval['DESCRIP'],10,10));
						$lcResp = trim(substr($laAval['DESCRIP'],20,10));
						$lcPuntos = trim(substr($laAval['DESCRIP'],30,10));
						$lcTemp = str_pad(strval($lcPreg),2,'0',STR_PAD_LEFT);
						$this->aDatosAval['Nihss']['selNihss'.$lcTemp]=intval($lcResp);
						$this->aDatosAval['Nihss']['lblPunto'.$lcTemp]=intval($lcPuntos);
						$this->aDatosAval['Nihss']['txtPunto'.$lcTemp]=intval($lcPuntos);
						$this->aDatosAval['Nihss']['txtTotalN']+=intval($lcPuntos);
						break;

					case $laAval['INDICE']==22:
						$this->aDatosAval['edtxtPandemia'] .= $laAval['DESCRIP'];
						break;

					case $laAval['INDICE']==30  && (in_array($laAval['INDICE2'], [5951,5952,5953,5954])):
						$this->OrganizarEscalas($laAval, 'EV');
						break;

					case ($laAval['INDICE']==21  && $laAval['INDICE2']==5995):
						$lcSeguir = str_pad(trim($laAval['OPCION']), 2, "0", STR_PAD_LEFT);
						$this->aDatosAval['Analisis']['selConductaSeguir'] = ($lcSeguir=='03'?'':$lcSeguir);
						if (trim($laAval['OPCION'])=='1'){
							$laTemp = explode("¥", trim($laAval['DESCRIP']));
							if(count($laTemp)>1){
								$this->aDatosAval['Analisis']['selEstadoSalida']='002';
								$laDato = explode("¤", $laTemp[1]);
								$this->aDatosAval['Analisis']['lcfechaFallece']=$laDato[0];
								$this->aDatosAval['Analisis']['lcHoraFallece']=$laDato[1];
								$this->aDatosAval['Analisis']['cCodigoDxFallece']=$laDato[2];
								$loDiagnostico = new Diagnostico();
								$this->aDatosAval['Analisis']['cDescripcionDxFallece'] = $loDiagnostico->consultaDX($laDato[2]);
							} else {
								$this->aDatosAval['Analisis']['selEstadoSalida']='001';
							}
						}
						break;
			}
				$lcPrg = $laAval['PGMRID'];
			}

			if(count($laDatosAF)>0){
				$loObjAval = new EscalaActividadFisica();
				if($laDatosAF['REGISTRO']=='V'){
					$this->aDatosAval['Actividad']['selRealizaActividad'] = 'V';
				}else{
					$this->aDatosAval['Actividad'] =  $loObjAval->consultaRegistroActividad($tnIngreso, $laDatosAF);
					$this->aDatosAval['Actividad']['selRealizaActividad'] = $this->aDatosAval['Actividad']['respuesta'];
				}
			}
			$this->OrganizarOrdenH($tnIngreso,$tnConsecutivo,$lcPrg);
			$this->aDatosAval['Nihss']['txtTotalN'] = ($this->aDatosAval['Nihss']['txtTotalN']==0?'': $this->aDatosAval['Nihss']['txtTotalN']);
		}
		$laRetorna = ['error'=>''];
		$laRetorna = ['Datos'=>$this->aDatosAval];
		return $laRetorna;
	}
}
