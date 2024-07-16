<?php
namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';
require_once __DIR__ . '/class.Consecutivos.php';
require_once __DIR__ . '/class.MailEnviar.php';
use NUCLEO\Db;
use NUCLEO\MailEnviar;

class AgendaSalasCirugia
{
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $cTipoId = '';
	protected $nNroide = 0;
	protected $nNroIng = 0;
	protected $nConConsAut = 0;
	protected $cEntidad = '';
	protected $aObjObligatoriosSC = [];
	

	protected $aError = [
				'Mensaje' => '',
				'Objeto' => '',
				'Valido' => true,
			];
	protected $aPaciente = [
				'PrimerNombre' => '',
				'SegundoNombre' => '',
				'PrimerApellido' => '',
				'SegundoApellido' => '',
				'Genero' => '',
				'Email' => '',
				'FechaNacimiento' => 0,
				'Telefono' => '',
				'Ingreso' => 0,
				'Plan' => '',
				'Habitacion' => '',
			];

	public function __construct()
    {
		global $goDb;
		$this->oDb = $goDb;
    }

	public function OrigenSalas(){
		if(isset($this->oDb)){
			$laOrigenes = $this->oDb
				->select(['TRIM(CL1TMA) CODIGO','TRIM(DE1TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'SALORI','ESTTMA' => '','OP1TMA' => 'W',])
				->orderBy('DE1TMA')
				->getAll('array');
			if(is_array($laOrigenes)==true){
				$this->aOrigenSalas=$laOrigenes;
			}
		}
		return $this->aOrigenSalas;
	}

	public function NaturalezaSalas(){
		if(isset($this->oDb)){
			$laNaturaleza = $this->oDb
				->select(['TRIM(CL1TMA) CODIGO','TRIM(DE1TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'SALNAT','ESTTMA' => '',])
				->orderBy('DE1TMA')
				->getAll('array');
			if(is_array($laNaturaleza)==true){
				$this->aNaturalezaSalas=$laNaturaleza;
			}
		}
		return $this->aNaturalezaSalas;
	}

	public function LateralidadSalas(){
		if(isset($this->oDb)){
			$laLateralidad = $this->oDb
				->select(['TRIM(CL1TMA) CODIGO','UPPER(TRIM(DE2TMA)) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'SALLAT','ESTTMA' => '',])
				->orderBy('DE2TMA')
				->getAll('array');
			if(is_array($laLateralidad)==true){
				$this->aLateralidadSalas=$laLateralidad;
			}
		}
		return $this->aLateralidadSalas;
	}

	public function TipoAnestesiaSala(){
		if(isset($this->oDb)){
			$laTipoAnestesia = $this->oDb
				->select(['TRIM(CL1TMA) CODIGO','UPPER(TRIM(DE1TMA)) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'CODTAN','ESTTMA' => '','OP1TMA' => 'W',])
				->orderBy('DE1TMA')
				->getAll('array');
			if(is_array($laTipoAnestesia)==true){
				$this->aTipoAnestesiaSala=$laTipoAnestesia;
			}
		}
		return $this->aTipoAnestesiaSala;
	}

	public function DispositivosCardiacaSalas(){
		if(isset($this->oDb)){
			$laDispositivosCardiaco = $this->oDb
				->select(['TRIM(CL2TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'AGESALW','CL1TMA' => 'DISCARD','ESTTMA' => '',])
				->orderBy('OP2TMA')
				->getAll('array');
			if(is_array($laDispositivosCardiaco)==true){
				$this->aDispositivosCardiacos=$laDispositivosCardiaco;
			}
		}
		return $this->aDispositivosCardiacos;
	}

	public function RequerimientosEspeciales(){
		if(isset($this->oDb)){
			$laRequerimientosEspeciales = $this->oDb
				->select(['TRIM(CL2TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'AGESALW','CL1TMA' => 'REQESPEC','ESTTMA' => '',])
				->orderBy('OP2TMA')
				->getAll('array');
			if(is_array($laRequerimientosEspeciales)==true){
				$this->aRequerimientosEspeciales=$laRequerimientosEspeciales;
			}
		}
		return $this->aRequerimientosEspeciales;
	}

	public function ViasAcceso(){
		if(isset($this->oDb)){
			$laViasAcceso = $this->oDb
				->select(['TRIM(CL2TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'AGESALW','CL1TMA' => 'VIAACCES','ESTTMA' => '',])
				->orderBy('OP2TMA')
				->getAll('array');
			if(is_array($laViasAcceso)==true){
				$this->aViasAcceso=$laViasAcceso;
			}
		}
		return $this->aViasAcceso;
	}

	public function EquiposEspecialesSala(){
		if(isset($this->oDb)){
			$laEquipoEspec = $this->oDb
				->select(['TRIM(CL2TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'AGESALW','CL1TMA' => 'EQUESPEC','ESTTMA' => '',])
				->orderBy('DE2TMA')
				->getAll('array');
			if(is_array($laEquipoEspec)==true){
				$this->aEquiposEspeciales=$laEquipoEspec;
			}
		}
		return $this->aEquiposEspeciales;
	}

	public function TiposCancelacion($tcTipoCancelacion){
		if (!empty($tcTipoCancelacion)){
			if(isset($this->oDb)){
				$laTiposCancelacion = $this->oDb
					->select(['TRIM(CL2TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
					->from('TABMAE')
					->where(['TIPTMA' => 'AGESALW','CL1TMA' => $tcTipoCancelacion,'ESTTMA' => '',])
					->orderBy('DE2TMA')
					->getAll('array');
				if(is_array($laTiposCancelacion)==true){
					$this->aTiposCancelacion=$laTiposCancelacion;
				}
			}
		}
		return $this->aTiposCancelacion;
	}

	public function MotivosCancelacion($tcTipoCancelacion='',$tcTipoMotivo=''){
		if (!empty($tcTipoCancelacion)){
			if(isset($this->oDb)){
				$laMotivosCancelacion = $this->oDb
					->select(['TRIM(CL3TMA) CODIGO','TRIM(DE2TMA) DESCRIPCION'])
					->from('TABMAE')
					->where(['TIPTMA' => 'AGESALW','CL1TMA' => $tcTipoMotivo,'CL2TMA' => $tcTipoCancelacion,'ESTTMA' => '',])
					->orderBy('DE2TMA')
					->getAll('array');
				if(is_array($laMotivosCancelacion)==true){
					$this->aMotivosCancelacion=$laMotivosCancelacion;
				}
			}
		}
		return $this->aMotivosCancelacion;
	}
	
	public function diasDiferenciaSolicitud(){
		$lnDiasSolicitud = 0;
		if(isset($this->oDb)){
			$laDiasSolicitud = $this->oDb
				->select(['OP3TMA'])
				->from('TABMAE')
				->where(['TIPTMA' => 'AGESALW','CL1TMA' => 'DIASOL',])
				->get('array');
			if(is_array($laDiasSolicitud)){
				if(count($laDiasSolicitud)>0){
					$lnDiasSolicitud = $laDiasSolicitud['OP3TMA'];
				}
			}
			unset($laDiasSolicitud);
		}
		return $lnDiasSolicitud;
	}

	public function consultaAgendaPaciente($tcSalaAgenda='', $tnFechaInicial=0, $tnFechaFinal=0)
	{
		$laReturn = [];
		$tnFechaInicial = str_replace('-','',$tnFechaInicial);
		$tnFechaFinal = str_replace('-','',$tnFechaFinal);
		if (!empty($tcSalaAgenda)){
			$this->oDb->where(['P.SALSAL'=>$tcSalaAgenda]);
		}
		$laDatosAgenda = $this->oDb
			->select([
				'P.CONSAL',
				'P.TCUSAL',
				'TRIM(P.CUPSAL) CUPSAL',
				'TRIM(B.DESCUP) DESCUP',
				'TRIM(P.ESPSAL) ESPSAL',
				'TRIM(C.DESESP) DESESP',
				'TRIM(C.CODESP) CODESP',
				'TRIM(P.PLASAL) PLASAL',
				'TRIM(D.REGMED) REGMED',				
				'IFNULL(P.REGSAL, \'\') REGSAL','IFNULL(UPPER(TRIM(D.NOMMED) || \' \' || TRIM(D.NNOMED)), \'\') MEDICO',
				'P.ESTSAL',
				'IFNULL(P.ANESAL, \'\') ANESAL',
				'IFNULL(UPPER(TRIM(E.NOMMED) || \' \' || TRIM(E.NNOMED)), \'\') ANESTESIOLOGO',
				'TRIM(P.SALSAL) SALSAL',
				'P.FPRSAL',
				'P.HPRSAL',
				'TRIM(A.NM1PAL) NM1PAL',
				'TRIM(A.NM2PAL) NM2PAL',
				'TRIM(A.AP1PAL) AP1PAL',
				'TRIM(A.AP2PAL) AP2PAL',
			])
			->from('AGESAL P')
			->leftJoin('PACALT A', 'P.TIDSAL=A.TIDPAL AND P.NIDSAL=A.NIDPAL', null)
			->leftJoin('RIACUP B', 'P.CUPSAL=B.CODCUP', null)
			->leftJoin('RIAESPE C', 'P.ESPSAL=C.CODESP', null)
			->leftJoin('RIARGMN D', 'P.REGSAL=D.REGMED', null)
			->leftJoin('RIARGMN E', 'P.ANESAL=E.REGMED', null)
			->where(['P.ESTSAL' => 'A'])
			->between('P.FPRSAL',$tnFechaInicial, $tnFechaFinal)
			->orderBy('P.SALSAL,P.FPRSAL,P.HPRSAL')
			->getAll('array');
		if (is_array($laDatosAgenda)){
			$laReturn = $laDatosAgenda;
		}
		return $laReturn;
	}

	public function consultaDatosAgendamiento($tnConsecutivo=0)
	{
		$this->aListado = $laReturn = [];
		$lcChrItm = chr(25);
		$laDatosAgendamiento = $this->oDb
			->select([
				'P.CONSAL',
				'TRIM(P.TIDSAL) TIDSAL',
				'P.NIDSAL',
				'P.NIGSAL',
				'TRIM(P.PLASAL) PLASAL',
				'TRIM(IFNULL(F.DSCCON, \'\')) ENTIDAD',
				'P.FSOSAL',
				'TRIM(P.CUPSAL) || \' - \' || TRIM(B.DESCUP) CUPSAL',
				'TRIM(P.HACSAL) HACSAL',
				'TRIM(A.NM1PAL) NM1PAL',
				'TRIM(A.NM2PAL) NM2PAL',
				'TRIM(A.AP1PAL) AP1PAL',
				'TRIM(A.AP2PAL) AP2PAL',
				'A.FNAPAL',
				'A.SEXPAL',
				'A.TP1PAL',
				'TRIM(A.MAIPAL) MAIPAL'
			])
			->from('AGESAL P')
			->leftJoin('PACALT A', 'P.TIDSAL=A.TIDPAL AND P.NIDSAL=A.NIDPAL', null)
			->leftJoin('RIACUP B', 'P.CUPSAL=B.CODCUP', null)
			->leftJoin('FACPLNC F', 'P.PLASAL=F.PLNCON', null)
			->where(['CONSAL'=>$tnConsecutivo])
			->get('array');
		if (is_array($laDatosAgendamiento)){
			$laDatosAdicionales = $this->oDb
				->select('P.INDSAD,P.DESSAD')
				->from('AGESAD P')
				->where(['CONSAD'=>$tnConsecutivo])
				->orderBy('INDSAD,LINSAD')
				->getAll('array');
			if (is_array($laDatosAdicionales)){
				foreach($laDatosAdicionales as $laDatos){
					switch (true){
						case $laDatos['INDSAD']==1:
							$lcDescripcion = explode($lcChrItm, trim($laDatos['DESSAD']));
							$this->aListado = [
								'fechaSolicitud'=>$lcDescripcion[8],
								'origen'=>$lcDescripcion[9],
								'especialidadSolicita'=>$lcDescripcion[10],
								'medicoSolicita'=>$lcDescripcion[11],
								'tipoProcedimiento'=>$lcDescripcion[12],
								'tiempoCups'=>$lcDescripcion[13],
								'tiempoMinutosCups'=>$lcDescripcion[25] ?? '',
								'lateralidad'=>$lcDescripcion[14],
								'anestesiologo'=>$lcDescripcion[26] ?? '',
								'tipoAnestesia'=>$lcDescripcion[15],
								'cirugiaContaminada'=>$lcDescripcion[16],
								'reintervencion'=>$lcDescripcion[17],
								'escalaMents'=>$lcDescripcion[18],
								'pruebaCovid'=>$lcDescripcion[19],
								'dispositivoCardiaca'=>$lcDescripcion[20],
								'requerimientos'=>$lcDescripcion[23],
								'viaacceso'=>$lcDescripcion[24],
								'reservaSangre'=>$lcDescripcion[21],
								'ayudanteQuirurgico'=>$lcDescripcion[22],
								'cups'=>'',
								'equiposEspeciales'=>'',
								'observaciones'=>'',
								'autorizada'=>$lcDescripcion[27] ?? '',
							];

							$laDatosUltimaModificacion = $this->oDb
							->select([
								'P.ESPSAL',
								'P.REGSAL',
								'P.ANESAL'
							])
							->from('AGESAL P')
							->where(['P.CONSAL'=>$tnConsecutivo])
							->get('array');
							
							if (is_array($laDatosUltimaModificacion)){
								$this->aListado ['especialidadSolicita'] = $laDatosUltimaModificacion['ESPSAL'];
								$this->aListado ['medicoSolicita'] = $laDatosUltimaModificacion['REGSAL'];
								$this->aListado ['anestesiologo'] = $laDatosUltimaModificacion['ANESAL'];	
							}
							
						break ;

						case $laDatos['INDSAD']==2:
							$this->aListado['observaciones'] .= $laDatos['DESSAD'];
						break ;

						case $laDatos['INDSAD']==3:
							$this->aListado['equiposEspeciales'] = trim($laDatos['DESSAD']);
						break ;

						case $laDatos['INDSAD']==5:
							$this->aListado['cups'] = trim($laDatos['DESSAD']);
						break ;
					}
				}
			}
			$this->aListado['observaciones'] = trim($this->aListado['observaciones']);
			$laReturn = array_merge($laDatosAgendamiento, $this->aListado) ;
		}
		return $laReturn;
	}

	public function consultaPaciente($tcTipoId='', $tnNumId=0)
	{
		$laReturn = [];
		$lnNroIngreso=0;
		$lcPlanIngreso=$lcHabitacion='';
		$laDatosPaciente = $this->oDb
			->select([	'P.TIDPAC','P.NIDPAC','TRIM(P.NM1PAC) NM1PAC','TRIM(P.NM2PAC) NM2PAC','TRIM(P.AP1PAC) AP1PAC','TRIM(P.AP2PAC) AP2PAC',
						'TRIM(P.MAIPAC) MAIPAC','P.FNAPAC','P.SEXPAC','P.NHCPAC','TRIM(P.DR1PAC) DR1PAC,A.TP1PAL',])
			->from('RIAPAC P')
			->leftJoin('PACALT A', 'P.TIDPAC=A.TIDPAL AND P.NIDPAC=A.NIDPAL', null)
			->where(['P.TIDPAC'=>$tcTipoId, 'P.NIDPAC'=>$tnNumId])
			->get('array');
		if (is_array($laDatosPaciente)){
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'AGESALW', ['CL1TMA'=>'VIASABI', 'ESTTMA'=>' ']);
			$lcViasAbierto = trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
			$laCondiciones = "A.TIDING='$tcTipoId' AND A.NIDING='$tnNumId' AND A.ESTING='2'";
			$laCondiciones = $laCondiciones ." AND (TRIM(IFNULL(A.VIAING, '')) IN($lcViasAbierto))";

			$laDatosIngreso = $this->oDb
			->select('IFNULL(A.NIGING,0) NIGING, TRIM(IFNULL(A.PLAING,\'\')) PLAING, IFNULL(TRIM(B.SECHAB) || \'-\' || TRIM(B.NUMHAB), \'\') HABITACION')
			->from('RIAING A')
			->leftJoin('FACHAB B', 'A.NIGING=B.INGHAB', null)
			->where($laCondiciones)
			->get('array');
			if ($this->oDb->numRows()>0){
				$lnNroIngreso=$laDatosIngreso['NIGING'] ?? 0;
				$lcPlanIngreso=$laDatosIngreso['PLAING'] ?? '';
				$lcHabitacion=$laDatosIngreso['HABITACION'] ?? '';
			}else{
				$oTabmae=$this->oDb->ObtenerTabMae('DE2TMA', 'AGESALW', ['CL1TMA'=>'VIASAMB', 'ESTTMA'=>' ']);
				$lcViasAmbulatorio=trim(AplicacionFunciones::getValue($oTabmae, 'DE2TMA', ''));
				$laCondiciones = "A.TIDING='$tcTipoId' AND A.NIDING='$tnNumId' AND A.ESTING='3'";
				$laCondiciones = $laCondiciones ." AND (TRIM(IFNULL(A.VIAING, '')) IN($lcViasAmbulatorio))";
			
				$laIngresoAmbulatorio = $this->oDb
				->select('IFNULL(A.NIGING,0) NIGING, TRIM(IFNULL(A.PLAING,\'\')) PLAING, IFNULL(TRIM(B.SECHAB) || \'-\' || TRIM(B.NUMHAB), \'\') HABITACION')
				->from('RIAING A')
				->leftJoin('FACHAB B', 'A.NIGING=B.INGHAB', null)
				->where($laCondiciones)
				->orderBy('A.NIGING DESC')
				->get('array');
				if ($this->oDb->numRows()>0){
					$lnNroIngreso=$laIngresoAmbulatorio['NIGING'] ?? 0;
					$lcPlanIngreso=$laIngresoAmbulatorio['PLAING'] ?? '';
					$lcHabitacion=$laIngresoAmbulatorio['HABITACION'] ?? '';
				}
			}
				
			$this->aPaciente = [
				'PrimerNombre'=>$laDatosPaciente['NM1PAC'],
				'SegundoNombre'=>$laDatosPaciente['NM2PAC'],
				'PrimerApellido'=>$laDatosPaciente['AP1PAC'],
				'SegundoApellido'=>$laDatosPaciente['AP2PAC'],
				'Genero'=>$laDatosPaciente['SEXPAC'],
				'Email'=>$laDatosPaciente['MAIPAC'],
				'FechaNacimiento'=>$laDatosPaciente['FNAPAC'],
				'Telefono'=>$laDatosPaciente['TP1PAL'],
				'Ingreso'=>$lnNroIngreso,
				'Plan'=>$lcPlanIngreso,
				'Habitacion'=>$lcHabitacion,
			];
			$laReturn = $this->aPaciente;
		}
		unset($laDatosPaciente);
		unset($laDatosIngreso);
		unset($laIngresoAmbulatorio);
		return $laReturn;
	}

	public function verificarSC($taDatos=[])
	{
		$llEmailPac = false;
		$this->cTipoId = $taDatos['Datospaciente']['TipDocSala'];
		$this->nNroide = $taDatos['Datospaciente']['NumDocSala'];
		$this->nNroIng = $taDatos['Datospaciente']['Ingreso'];
		$this->cEntidad = trim($taDatos['Datospaciente']['EntidadSala']);
		$lcEmailPaciente = isset($taDatos['Datospaciente']['EmailSala']) && trim($taDatos['Datospaciente']['EmailSala'])!='' ? trim($taDatos['Datospaciente']['EmailSala']):'';
		$loMailEnviar = new MailEnviar();
		$llEmailPac = $lcEmailPaciente!='' ? $loMailEnviar->validarEmail($lcEmailPaciente):'';
		unset($loMailEnviar);

		$this->IniciaDatosAuditoria();
		$lbRevisar = true;
		$lcOrigen = trim($taDatos['Datospaciente']['OrigenSala']);
		$lcEspecialidadMedico = trim($taDatos['Datospaciente']['EspecialidadMedico']);
		$lcRegistroMedico = trim($taDatos['Datospaciente']['MedicoPrograma']);
		$lcAnestesiologo = trim($taDatos['Datospaciente']['Anestesiologo']);
		$lcTipoProcedimiento = trim($taDatos['Datospaciente']['TipoProcedimientoSala']);
		$lcLateralidad = trim($taDatos['Datospaciente']['LateralidadSala']);
		$lcTipoAnestesia = trim($taDatos['Datospaciente']['TipoAnestesiaSala']);
		$lcDispositivoCardiaco = trim($taDatos['Datospaciente']['DispositivosCardiacoSala']);
		$lcTipoSala = explode('-', trim($taDatos['Datospaciente']['SalaSeleccionada']));
		$lcSala = trim($lcTipoSala[0]);
		$lcHabitacion = trim($lcTipoSala[1]);

		if ($lbRevisar){
			if (empty($this->cEntidad)){
				$this->aError = [
					'Mensaje'=>'No existe ENTIDAD',
					'Objeto'=>'selEntidadSala',
					'Valido'=>false,
				];
				$lbRevisar = false;
			}
		}
		
		if ($lbRevisar && $lcEmailPaciente!=''){
			if (!$llEmailPac){
				$this->aError = [
					'Mensaje'=>'Correo no válido, verifique por favor.',
					'Objeto'=>'txtEmailSala',
					'Valido'=>false,
				];
				$lbRevisar = false;
			}
		}

		$laErrores = [];
		if ($lbRevisar){
			if (!empty($lcSala)){
				$lcTablaValida = 'FACHAB';
				$laWhere=['IDDHAB'=>'0','SECHAB'=>$lcSala,'NUMHAB'=>$lcHabitacion,];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe SALA A PROGRAMAR en la base de datos.',
							'Objeto'=>'txtHabitacionSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($this->cEntidad)){
				$lcTablaValida = 'FACPLNC';
				$laWhere=['PLNCON'=>$this->cEntidad,'ESTCON'=>'A',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe PLAN creado en la base de datos.',
							'Objeto'=>'selEntidadSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcOrigen)){
				$lcTablaValida = 'TABMAE';
				$laWhere=['TIPTMA'=>'SALORI','CL1TMA'=>$lcOrigen,'ESTTMA'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe ORIGEN en la base de datos.',
							'Objeto'=>'selOrigenSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcEspecialidadMedico)){
				$lcTablaValida = 'RIAESPE';
				$laWhere=['CODESP'=>$lcEspecialidadMedico,'UBIESP'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe ESPECIALIDAD en la base de datos.',
							'Objeto'=>'selEspecialidadMedico',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcRegistroMedico)){
				$lcTablaValida = 'RIARGMN';
				$laWhere=['REGMED'=>$lcRegistroMedico,'ESTRGM'=>1,];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe REGISTRO MÉDICO en la base de datos.',
							'Objeto'=>'selMedicoPrograma',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcAnestesiologo)){
				$lcTablaValida = 'RIARGMN';
				$laWhere=['REGMED'=>$lcAnestesiologo,'ESTRGM'=>1,'TPMRGM'=>6,];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe ANESTESIOLOGO en la base de datos.',
							'Objeto'=>'selAnestesiologo',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcCodigoCups)){
				try {
					if(count($taDatos['Procedimientos'])>0) {
						$lbValidar = false;
						foreach($taDatos['Procedimientos'] as $laCup) {
							$laReg = $this->oDb->select('CODCUP')->from('CODCUP')->where(['CODCUP'=>$laCup['CUP'],'IDDCUP'=>'0',])->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$this->aError = [
									'Mensaje'=>"No existe CÓDIGO CUP {$laCup['CUP']} en la base de datos.",
									'Objeto'=>'buscarCupsSala',
									'Valido'=>false,
								];
								$lbRevisar = false;
								break;
							}
						}
					} else {
						$this->aError = [
							'Mensaje'=>"Se debe adicionar al menos un procedimiento.",
							'Objeto'=>'buscarCupsSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcTipoProcedimiento)){
				$lcTablaValida = 'TABMAE';
				$laWhere=['TIPTMA'=>'SALNAT','CL1TMA'=>$lcTipoProcedimiento,'ESTTMA'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						$this->aError = [
							'Mensaje'=>'No existe TIPO PROCEDIMIENTO en la base de datos.',
							'Objeto'=>'selTipoProcedimientoSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcLateralidad)){
				$lcTablaValida = 'TABMAE';
				$laWhere=['TIPTMA'=>'SALLAT','CL1TMA'=>$lcLateralidad,'ESTTMA'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe LATERALIDAD en la base de datos.',
							'Objeto'=>'selLateralidadSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcTipoAnestesia)){
				$lcTablaValida = 'TABMAE';
				$laWhere=['TIPTMA'=>'CODTAN','CL1TMA'=>$lcTipoAnestesia,'ESTTMA'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe TIPO ANESTESIA en la base de datos.',
							'Objeto'=>'selTipoAnestesiaSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($lcDispositivoCardiaco)){
				$lcTablaValida = 'TABMAE';
				$laWhere=['TIPTMA'=>'AGESALW','CL1TMA'=>'DISCARD','CL2TMA'=>$lcDispositivoCardiaco,'ESTTMA'=>'',];
				try {
					$lbValidar = false;
					$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

					if (!$lbValidar) {
						 $this->aError = [
							'Mensaje'=>'No existe DISPOSITIVO CARDIACO en la base de datos.',
							'Objeto'=>'selTipoAnestesiaSala',
							'Valido'=>false,
						];
						$lbRevisar = false;
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
			}
		}

		if ($lbRevisar){
			if (!empty($taDatos['EquipoEspeciales'])){

				foreach ($taDatos['EquipoEspeciales'] as $laEquipos){
					$lcSeleccion = $laEquipos['SELECCION'];
					$lcCodigoEquipo = $laEquipos['CODIGO'];
					if ($lcSeleccion=='true'){

						$lcTablaValida = 'TABMAE';
						$laWhere=['TIPTMA'=>'AGESALW','CL1TMA'=>'EQUESPEC','CL2TMA'=>$lcCodigoEquipo,'ESTTMA'=>'',];
						try {
							$lbValidar = false;
							$laReg = $this->oDb->from($lcTablaValida)->where($laWhere)->getAll('array');
							if (is_array($laReg)) if (count($laReg)>0) $lbValidar = true;

							if (!$lbValidar) {
								$this->aError = [
									'Mensaje'=>'No existe EQUIPO ESPECIAL en la base de datos.',
									'Objeto'=>'selTipoAnestesiaSala',
									'Valido'=>false,
								];
								$lbRevisar = false;
							}
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}

					}
				}
			}
		}
		
		if ($lbRevisar){
			if (empty($taDatos['Datospaciente']['FechaNacimiento'])){
				$this->aError = [
					'Mensaje'=>"No existe fecha nacimiento.",
					'Objeto'=>'txtFechaNacimiento',
					'Valido'=>false,
				];
				$lbRevisar = false;
			}else{
				if (strlen(str_replace('-','',$taDatos['Datospaciente']['FechaNacimiento']))!=8){
					$this->aError = [
						'Mensaje'=>"Fecha nacimiento NO VALIDA.",
						'Objeto'=>'txtFechaNacimiento',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
		}
		
		if ($lbRevisar){
			if (empty($taDatos['Datospaciente']['FechaSolicitudMedico'])){
				$this->aError = [
					'Mensaje'=>"No existe fecha solicitud.",
					'Objeto'=>'txtFechaSolicitudMedico',
					'Valido'=>false,
				];
				$lbRevisar = false;
			}else{
				if (strlen(str_replace('-','',$taDatos['Datospaciente']['FechaSolicitudMedico']))!=8){
					$this->aError = [
						'Mensaje'=>"Fecha solicitud NO VALIDA.",
						'Objeto'=>'txtFechaSolicitudMedico',
						'Valido'=>false,
					];
					$lbRevisar = false;
				}
			}
		}
		
		return  $this->aError;
	}

	public function GuardarSC($taDatos=[])
	{
		$llRetorno = true;
		$this->nConConsAut = Consecutivos::fCalcularConsecutivoAgendaSalas();

		if($this->nConConsAut>0){
			$this->organizarDatosSC($taDatos);
		}
		return $this->aError;
	}

	function organizarDatosSC($taDatos=[])
	{
		$lcChrItm = chr(25);
		$lnIndice = 0;
		$lnLinea = 0;
		$lnConsecutivo = 0;

		if (!empty($taDatos['Datospaciente'])){
			$lcTabla = 'RIAPAC';
			$lcDatosGuardar = $taDatos['Datospaciente'];
			$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$lnConsecutivo);

			$lcTabla = 'AGESAL';
			$lcDatosGuardar['cup']=$lcListaCups=$lcSep='';
			foreach($taDatos['Procedimientos'] as $laCup){
				$lbEsPrincipal = $laCup['PPL']=='true';
				if ($lbEsPrincipal) {
					$lcDatosGuardar['cup']=$laCup['CUP'];
				}
				$lcListaCups.=$lcSep.$laCup['CUP'].'~'.($lbEsPrincipal?'1':'0');
				$lcSep='|';
			}
			$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$lnConsecutivo);

			$lcTabla = 'AGESAD';
			$lnIndice = 1;
			$lnLinea = 1;

			$lcDatosGuardar = $this->cTipoId.$lcChrItm.$this->nNroide.$lcChrItm.$this->nNroIng.$lcChrItm
				.$taDatos['Datospaciente']['SalaSeleccionada'].$lcChrItm.$taDatos['Datospaciente']['HabitacionSala'].$lcChrItm
				.str_replace('-','',$taDatos['Datospaciente']['FechaProgramadaSeleccionada']).$lcChrItm
				.str_replace(':','',$taDatos['Datospaciente']['HoraSeleccionada']).$lcChrItm.$this->cEntidad.$lcChrItm
				.str_replace('-','',$taDatos['Datospaciente']['FechaSolicitudMedico']).$lcChrItm
				.$taDatos['Datospaciente']['OrigenSala'].$lcChrItm.$taDatos['Datospaciente']['EspecialidadMedico'].$lcChrItm
				.$taDatos['Datospaciente']['MedicoPrograma'].$lcChrItm.$taDatos['Datospaciente']['TipoProcedimientoSala'].$lcChrItm
				.$taDatos['Datospaciente']['TiempoCups'].$lcChrItm.$taDatos['Datospaciente']['LateralidadSala'].$lcChrItm
				.$taDatos['Datospaciente']['TipoAnestesiaSala'].$lcChrItm.$taDatos['Datospaciente']['CirugiaContaminada'].$lcChrItm
				.$taDatos['Datospaciente']['Reintervencion'].$lcChrItm.$taDatos['Datospaciente']['EscalaMents'].$lcChrItm
				.$taDatos['Datospaciente']['PruebaCovid19'].$lcChrItm.$taDatos['Datospaciente']['DispositivosCardiacoSala'].$lcChrItm
				.$taDatos['Datospaciente']['ReservaSangre'].$lcChrItm.$taDatos['Datospaciente']['AyudanteQuirurgico'].$lcChrItm
				.$taDatos['Datospaciente']['Requerimientos'].$lcChrItm.$taDatos['Datospaciente']['ViaAcceso'].$lcChrItm
				.$taDatos['Datospaciente']['TiempoMinutos'].$lcChrItm.$taDatos['Datospaciente']['Anestesiologo'].$lcChrItm
				.$taDatos['Datospaciente']['Autorizada'];
			$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$lnConsecutivo);
		}

		$lcTabla = 'AGESAD';
		$lnLongitud = 220;
		$lnLinea = 1;
		if (!empty($taDatos['Observaciones'])){
			$lnIndice = 2;
			$this->InsertarDescripcion($lcTabla,$lnLongitud,trim($taDatos['Observaciones']),$lnIndice,$lnLinea);
		}

		if (!empty($taDatos['EquipoEspeciales'])){
			$lnIndice = 3;
			$lcDatosGuardar = '';
			foreach ($taDatos['EquipoEspeciales'] as $laEquipos){
				$lcSeleccion = $laEquipos['SELECCION'];
				$lcCodigoEquipo = $laEquipos['CODIGO'];
				if ($lcSeleccion=='true'){
					$lcDatosGuardar .= "-".$lcCodigoEquipo."-,";
				}
			}
			$this->InsertarDescripcion($lcTabla,$lnLongitud,$lcDatosGuardar,$lnIndice,$lnLinea);
		}

		if (!empty($lcListaCups)){
			$lnIndice = 5;
			$this->InsertarDescripcion($lcTabla,$lnLongitud,$lcListaCups,$lnIndice,$lnLinea);
		}
	}

	function InsertarRegistro($tcTabla='', $tcDescripcion='', $tnIndice=0, $tnLinea=0, $tnConsecutivo=0)
	{
		switch (true){
			case $tcTabla=='RIAPAC' :
				$lcPrimerNombre = trim(strtoupper($tcDescripcion['PrimerNombre']));
				$lcSegundoNombre = trim(strtoupper($tcDescripcion['SegundoNombre']));
				$lcPrimerApellido = trim(strtoupper($tcDescripcion['PrimerApellido']));
				$lcSegundoApellido = trim(strtoupper($tcDescripcion['SegundoApellido']));
				$lnFechaNacimiento = str_replace('-','',$tcDescripcion['FechaNacimiento']);
				$lcGenero = $tcDescripcion['GeneroSala'];
				$lcTelefono = $tcDescripcion['TelefonoSala'];
				$lcEmail = $tcDescripcion['EmailSala'];

				$laWhere=['TIDPAC' => $this->cTipoId,'NIDPAC'=>$this->nNroide,];
				$laData=[
					'NM1PAC' => substr(trim($lcPrimerNombre), 0, 15),
					'NM2PAC' => substr(trim($lcSegundoNombre), 0, 15),
					'AP1PAC' => substr(trim($lcPrimerApellido), 0, 15),
					'AP2PAC' => substr(trim($lcSegundoApellido), 0, 15),
					'FNAPAC' => $lnFechaNacimiento,
					'SEXPAC' => $lcGenero,
					'MAIPAC' => substr(trim($lcEmail), 0, 60),
					'TELPAC' => substr(trim($lcTelefono), 0, 60),
				];
				$laDataI=[
					'TIDPAC' => $this->cTipoId,
					'NIDPAC' => $this->nNroide,
					'USRPAC' => $this->cUsuCre,
					'PGMPAC' => $this->cPrgCre,
					'FECPAC' => $this->cFecCre,
					'HORPAC' => $this->cHorCre,
				];
				$laDataU=[
					'UMOPAC' => $this->cUsuCre,
					'PMOPAC' => $this->cPrgCre,
					'FMOPAC' => $this->cFecCre,
					'HMOPAC' => $this->cHorCre,
				];

				try {
					$lbInsertar = true;
					$laReg = $this->oDb->from($tcTabla)->where($laWhere)->get('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

					if ($lbInsertar) {
						$this->oDb->from($tcTabla)->insertar(array_merge($laData,$laDataI));
					} else {
						$this->oDb->from($tcTabla)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}

				$tcTabla = 'PACALT';
				$laWhere=['TIDPAL' => $this->cTipoId,'NIDPAL'=>$this->nNroide,];
				$laData=[
					'NM1PAL' => substr(trim($lcPrimerNombre), 0, 40),
					'NM2PAL' => substr(trim($lcSegundoNombre), 0, 40),
					'AP1PAL' => substr(trim($lcPrimerApellido), 0, 40),
					'AP2PAL' => substr(trim($lcSegundoApellido), 0, 40),
					'SEXPAL' => $lcGenero,
					'MAIPAL' => substr(trim($lcEmail), 0, 220),
					'FNAPAL' => $lnFechaNacimiento,
					'TP1PAL' => $lcTelefono,
				];
				$laDataI=[
					'TIDPAL' => $this->cTipoId,
					'NIDPAL' => $this->nNroide,
					'USRPAL' => $this->cUsuCre,
					'PGMPAL' => $this->cPrgCre,
					'FECPAL' => $this->cFecCre,
					'HORPAL' => $this->cHorCre,
				];
				$laDataU=[
					'UMOPAL' => $this->cUsuCre,
					'PMOPAL' => $this->cPrgCre,
					'FMOPAL' => $this->cFecCre,
					'HMOPAL' => $this->cHorCre,
				];

				try {
					$lbInsertar = true;
					$laReg = $this->oDb->from($tcTabla)->where($laWhere)->get('array');
					if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

					if ($lbInsertar) {
						$this->oDb->from($tcTabla)->insertar(array_merge($laData,$laDataI));
					} else {
						$this->oDb->from($tcTabla)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
					}
				} catch(Exception $loError){
					$laErrores[] = $loError->getMessage();
				} catch(PDOException $loError){
					$laErrores[] = $loError->getMessage();
				}
				$tcTabla = 'PACDET';
				$laData=[
					'TIDPAD' => $this->cTipoId,
					'NIDPAD' => $this->nNroide,
					'NM1PAD' => substr(trim($lcPrimerNombre), 0, 40),
					'NM2PAD' => substr(trim($lcSegundoNombre), 0, 40),
					'AP1PAD' => substr(trim($lcPrimerApellido), 0, 40),
					'AP2PAD' => substr(trim($lcSegundoApellido), 0, 40),
					'FNAPAD' => $lnFechaNacimiento,
					'SEXPAD' => $lcGenero,
					'MAIPAD' => substr(trim($lcEmail), 0, 220),
					'TP1PAD' => $lcTelefono,
					'USRPAD' => $this->cUsuCre,
					'PGMPAD' => $this->cPrgCre,
					'FECPAD' => $this->cFecCre,
					'HORPAD' => $this->cHorCre,
				];
				$llResultado = $this->oDb->from($tcTabla)->insertar($laData);
				break;

			case $tcTabla=='AGESAL' :
				$lcSalaAgendar = $tcDescripcion['SalaSeleccionada'];
				$lcEspecialidad = $tcDescripcion['EspecialidadMedico'];
				$lcRegistroMedico = $tcDescripcion['MedicoPrograma'];
				$lcAnestesiologo = $tcDescripcion['Anestesiologo'];
				$lcHabitacionActual = $tcDescripcion['HabitacionSala'];
				$lnTiempoCups = $tcDescripcion['TiempoCups'].$tcDescripcion['TiempoMinutos'];
				$lnFechaAgendar = str_replace('-','',$tcDescripcion['FechaProgramadaSeleccionada']);
				$lnHoraAgendar = str_replace(':','',$tcDescripcion['HoraSeleccionada']);
				$lnFechaSolicitud = str_replace('-','',$tcDescripcion['FechaSolicitudMedico']);
				$lcCodigoCups = $tcDescripcion['cup'];
				$laData=[
					'CONSAL' => $this->nConConsAut,
					'TIDSAL' => $this->cTipoId,
					'NIDSAL' => $this->nNroide,
					'NIGSAL' => $this->nNroIng,
					'PLASAL' => $this->cEntidad,
					'SALSAL' => $lcSalaAgendar,
					'FPRSAL' => $lnFechaAgendar ,
					'HPRSAL' => $lnHoraAgendar,
					'FSOSAL' => $lnFechaSolicitud,
					'TCUSAL' => $lnTiempoCups,
					'CUPSAL' => $lcCodigoCups,
					'ESPSAL' => $lcEspecialidad,
					'REGSAL' => $lcRegistroMedico,
					'HACSAL' => $lcHabitacionActual,
					'ESTSAL' => 'A',
					'ANESAL' => $lcAnestesiologo,
					'USRSAL' => $this->cUsuCre,
					'PGMSAL' => $this->cPrgCre,
					'FECSAL' => $this->cFecCre,
					'HORSAL' => $this->cHorCre,
				];
				$llResultado = $this->oDb->from($tcTabla)->insertar($laData);
				break;

			case $tcTabla=='AGESAD' :
				$lcConsecutivo = !empty(trim($tnConsecutivo))? $tnConsecutivo : $this->nConConsAut;
				$laData=[
				'CONSAD' => $lcConsecutivo,
				'INDSAD' => $tnIndice,
				'LINSAD' => $tnLinea,
				'DESSAD' => $tcDescripcion,
				'USRSAD' => $this->cUsuCre,
				'PGMSAD' => $this->cPrgCre,
				'FECSAD' => $this->cFecCre,
				'HORSAD' => $this->cHorCre,
				];
				$llResultado = $this->oDb->from($tcTabla)->insertar($laData);
				break;
		}
	}

	public function guardarCancelacion($tnConsecutivo=0,$tcEstadoCancelacion='',$tcTipoMotivoCancelacion=''){
		$this->IniciaDatosAuditoria();
		$lcTabla='AGESAL';
		$laDatos = ['ESTSAL'=>$tcEstadoCancelacion,
					'UMOSAL'=>$this->cUsuCre,
					'PMOSAL'=>$this->cPrgCre,
					'FMOSAL'=>$this->cFecCre,
					'HMOSAL'=>$this->cHorCre
					];
		$llResultado = $this->oDb->from($lcTabla)->where('CONSAL', '=', $tnConsecutivo)->actualizar($laDatos);

		$lcTabla = 'AGESAD';
		$lnIndice = ($tcEstadoCancelacion=='C'? 4: 6);
		$lnLinea = 1;
		$lcDatosGuardar = $tcTipoMotivoCancelacion;
		$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$tnConsecutivo);
	}

	public function guardarReagendar($tnConsecutivo=0,$tcTipoMotivoCancelacion='',$taDatos=[]){
		$lcSalaReagendar = $taDatos['sala'];
		$lnFechaReagendar = str_replace('-','',$taDatos['fecha']);
		$lnHoraReagendar = str_replace(':','',$taDatos['hora']);

		$this->IniciaDatosAuditoria();
		$lcTabla='AGESAL';
		$laDatos = ['SALSAL'=>$lcSalaReagendar,
					'FPRSAL'=>$lnFechaReagendar,
					'HPRSAL'=>$lnHoraReagendar,
					'UMOSAL'=>$this->cUsuCre,
					'PMOSAL'=>$this->cPrgCre,
					'FMOSAL'=>$this->cFecCre,
					'HMOSAL'=>$this->cHorCre
					];
		$llResultado = $this->oDb->from($lcTabla)->where('CONSAL', '=', $tnConsecutivo)->actualizar($laDatos);

		$lcTabla = 'AGESAD';
		$lnIndice = 10;
		$lnLinea = Consecutivos::fCalcularConsecutivoReagendarSalas($tnConsecutivo,$lnIndice);
		$lcDatosGuardar = $tcTipoMotivoCancelacion;
		$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$tnConsecutivo);
	}

	public function guardarAnestesiologo($tnConsecutivo=0,$tcNuevoAnestesiologo=''){
		$this->IniciaDatosAuditoria();
		$lcTabla='AGESAL';
		$laDatos = ['ANESAL'=>$tcNuevoAnestesiologo,
					'UMOSAL'=>$this->cUsuCre,
					'PMOSAL'=>$this->cPrgCre,
					'FMOSAL'=>$this->cFecCre,
					'HMOSAL'=>$this->cHorCre
					];
		$llResultado = $this->oDb->from($lcTabla)->where('CONSAL', '=', $tnConsecutivo)->actualizar($laDatos);
		$lcTabla = 'AGESAD';
		$lnIndice = 11;
		$lnLinea = Consecutivos::fCalcularConsecutivoAnestesiologoSalas($tnConsecutivo,$lnIndice);
		$lcDatosGuardar = $tcNuevoAnestesiologo;
		$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$tnConsecutivo);
	}

	public function guardarCirujano($tnConsecutivo=0,$tcNuevoCirujano='',$tcNuevaEspecialidad=''){
		$this->IniciaDatosAuditoria();
		$lcTabla='AGESAL';
		$laDatos = ['REGSAL'=>$tcNuevoCirujano,
					'ESPSAL'=>$tcNuevaEspecialidad, 
					'UMOSAL'=>$this->cUsuCre,
					'PMOSAL'=>$this->cPrgCre,
					'FMOSAL'=>$this->cFecCre,
					'HMOSAL'=>$this->cHorCre
					];
		$llResultado = $this->oDb->from($lcTabla)->where('CONSAL', '=', $tnConsecutivo)->actualizar($laDatos);
		$lcTabla = 'AGESAD';
		$lnIndice = 11;
		$lnLinea = Consecutivos::fCalcularConsecutivoCirujanoSalas($tnConsecutivo,$lnIndice);
		$lcDatosGuardar = $tcNuevoCirujano;
		$this->InsertarRegistro($lcTabla,$lcDatosGuardar,$lnIndice,$lnLinea,$tnConsecutivo);
	}



	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnIndice=0, $tnLinea=0)
	{
		$laChar = AplicacionFunciones::mb_str_split(trim($tcTexto),$tnLongitud);
		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnIndice, $tnLinea);
					$tnLinea++;
				}
			}
		}
	}

	function IniciaDatosAuditoria()
	{
		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format('Ymd');
		$this->cHorCre = $ltAhora->format('His');
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = 'AGENDAWEB';
	}

	public function ObjetosObligatoriosSC($tcTitulo='')
	{
		// Listado de los campos obligatorios en HC WEB
		$laCondiciones = ['TIPTMA'=>'AGESALW', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>''];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aObjObligatoriosSC = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy ('OP3TMA')
			->getAll('array');
	}

	//	Retorna array con campos obligatorios para HC WEB
	public function ObjObligatoriosSC()
	{
		return $this->aObjObligatoriosSC;
	}
	
	public function permisoRegistrar($tcUsuario=''){
		global $goDb;
		$tcUsuario = trim(strtoupper(strval($tcUsuario)));
		$laPermisos = array();

		if (isset($goDb)) {
			$laPermisosAux = $goDb
						->select('TRIM(DE2TMA) AS PERMISO')
						->tabla('TABMAE')
						->where('TIPTMA', 'AGESALW')
						->where('CL1TMA', 'REGISTRO')
						->where('CL2TMA', $tcUsuario)
						->where("ESTTMA=''")
						->get('array');
			$laPermisos = $laPermisosAux;
		}
		return $laPermisos;		
	}
	
}
