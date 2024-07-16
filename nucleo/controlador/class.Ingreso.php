<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.Paciente.php');
require_once ('class.Habitacion.php');
require_once ('class.AplicacionFunciones.php');
require_once ('class.Medico.php');
require_once ('class.Especialidad.php');
require_once ('class.Responsable.php');
require_once ('class.Plan.php');

use NUCLEO\Db;
use NUCLEO\Paciente;
use NUCLEO\Habitacion;
use NUCLEO\Plan;
use NUCLEO\Medico;
use NUCLEO\Especialidad;
use NUCLEO\Responsable;
use NUCLEO\AplicacionFunciones;

class Ingreso
{
	public $cId = '';
	public $nId = 0;
	public $nIngreso = 0;
	public $nEntidad = 0;
	public $nRegional = 0;
	public $nContrato = 0;
	public $cPlan = '';
	public $cPlanDescripcion = '';
	public $cPlanTipo = '';
	public $cPlanTipoDsc = '';
	public $cEstado = '';
	public $cDescripcioEstadoIngreso = '';
	public $cNumeroCarnet = '';
	public $cVia = '';
	public $cDescVia = '';
	public $cEstadoCivil = '';
	public $cAfiliadoTipo = '';
	public $cAfiliadoUsuario = '';
	public $cDescripcionAfiliadoUsuario = '';
	public $cCentroDeServicio = '';
	public $nIngresoFecha = 0;
	public $nIngresoHora = 0;
	public $nEgresoFecha = 0;
	public $nEgresoHora = 0;
	public $oPaciente = NULL;
	public $oResponsable = NULL;
	public $oHabitacion = NULL;
	public $oPlanIngreso = NULL;
	public $oMedicoTratante = NULL;
	public $aAntecedentes = array();
	public $aEdad = ['y'=>0,'m'=>0,'d'=>0];
	public $nPeso = 0;
	public $cTipoPeso = '';
	public $aTalla = ['valor'=>0, 'unidad'=>'cm'];

	private $cPrograma = '';

	public function __construct()
	{
		$this->oPaciente = new Paciente();
		$this->oResponsable = new Responsable();
		$this->oHabitacion = new Habitacion();
		$this->oPlanIngreso = new Plan();
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),-10);
	}

	/*
	 *	Obtener los datos de un ingreso
	 *	Parametro:	$tnIngreso	 Número ingreso
	 */
	public function cargarIngreso($tnIngreso=0)
	{
		$this->nIngreso=0;

		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){
				$laIngreso = $goDb->from('RIAING')->where('NIGING', '=', $tnIngreso)->get('array');
				if(is_array($laIngreso)==true){
					if(count($laIngreso)>0){
						$this->cId = trim($laIngreso['TIDING']);
						$this->nId = intval($laIngreso['NIDING']);
						$this->nIngreso = intval($laIngreso['NIGING']);
						$this->nEntidad = intval($laIngreso['ENTING']);
						$this->nRegional = intval($laIngreso['REGING']);
						$this->nContrato = intval($laIngreso['CONING']);
						$this->cPlan = trim($laIngreso['PLAING']);
						$this->cEstado = trim($laIngreso['ESTING']);
						$this->obtenerDescripcionEstadoIngreso();
						$this->cNumeroCarnet = trim($laIngreso['NCAING']);
						$this->cVia = trim($laIngreso['VIAING']);
						$this->cAfiliadoTipo = trim($laIngreso['TIAING']);
						$this->cAfiliadoUsuario = trim($laIngreso['TIUING']);
						$this->nIngresoFecha = $laIngreso['FEIING'];
						$this->nIngresoHora = intval($laIngreso['HORING']);
						$this->nEgresoFecha = intval($laIngreso['FEEING']);
						$this->nEgresoHora = intval($laIngreso['HREING']);
						$this->oPaciente->cargarPaciente($this->cId, $this->nId, $this->nIngreso);
						$this->oHabitacion->cargarHabitacion($this->nIngreso, $this->cId, $this->nId);
						$this->obtenerEdad();
						$this->obtenerEstadoCivil();
						$this->obtenerCentroServicio();
						$this->oResponsable->cargarResponsable($this->nIngreso);
						$this->oPlanIngreso->cargarDatos($this->cPlan);
						$lcDescVia = $goDb->from('RIAVIA')->where('CODVIA', '=', $this->cVia)->get('array');
						$this->cDescVia = trim($lcDescVia['DESVIA']);
						$this->oMedicoTratante = $this->obtenerMedicoTratante($tnIngreso);
					}
				}
			}
		}
	}

	public function obtenerDescripcionEstadoIngreso()
	{
		global $goDb;
		$oEstadoIngreso = $goDb
			->select('TRIM(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where(['TIPTMA'=>'DATING', 'CL1TMA'=>'ESTADO', 'CL2TMA'=>$this->cEstado])
			->get('array');
		$this->cDescripcioEstadoIngreso = trim($oEstadoIngreso['DESCRIPCION']??'');
		return $this->cDescripcioEstadoIngreso;
	}
	
	/*
	 *	Obtiene peso y talla - Información de la tabla ENNEUR REG.NOTAS DE NEUROLOGIA
	 */
	public function obtenerPesoTalla($tnIngreso=0)
	{
		global $goDb;
		if(isset($goDb)){
			$lnIngreso = $tnIngreso>0 ? $tnIngreso : $this->nIngreso;
			if($lnIngreso>0){
				$laCampos=['PESNEU AS PESO', 'TIPPES AS TIPO', 'TALNEU AS TALLA'];
				$laDatos = $goDb
					->select($laCampos)
					->from('ENNEUR')
					->where('INGNEU', '=', $lnIngreso )
					->where('PESNEU', '>', 0)
					->orderBy('FECNEU DESC, HORNEU DESC')
					->get('array');
				if(is_array($laDatos)==true){
					$lnLargo = count($laDatos);
					if($lnLargo>0){
						$this->nPeso = $laDatos['PESO'];
						$this->cTipoPeso = trim($laDatos['TIPO'])=='Gramos'? 'g': 'kg';
						$this->aTalla['valor'] = $laDatos['TALLA'];
					}
				} else {
					$laCampos = ['PSOEXF AS PESO, TLLEXF AS TALLA'];
					$laDatos = $goDb
						->select($laCampos)
						->from('RIAEXF')
						->where('NIGEXF', '=', $lnIngreso)
						->where('PSOEXF', '>', 0)
						->orderBy('FECEXF DESC, HOREXF DESC')
						->get('array');
					if(is_array($laDatos)==true){
						$lnLargo = count($laDatos);
						if($lnLargo>0){
							$this->nPeso = $laDatos['PESO'];
							$this->cTipoPeso = 'kg';
							$this->aTalla['valor'] = $laDatos['TALLA'];
						}
					}
				}
			}
		}
	}

	/*
	 *	Obtiene los datos de antecedentes por tipo
	 */
	public function obtenerAntecedentes($tnIngreso, $tcTipoAnte)
	{
		global $goDb;
		if(isset($goDb)){
			$laAntecedente = ['tipoAnte' => '', 'descripcion' => ''];

			// Informacion de la tabla ANTPADL02,  LOGICO 2 - IDENTIFICACION, para obtener las ALERGIAS del paciente.
			if ($tnIngreso==0){
				$lcSql = $goDb
					->select('A.CODAND AS ANT_PPAL, A.SANAND AS ANT_SECUN, D.DESAND AS DSCR_GENERAL')
					->select('A.INDAND AS INDICE, A.LINAND AS CONS_LINEA, A.DESAND AS DESCRIPCION')
					->from('ANTPADL02 AS A')
					->leftJoin('ANTDESL02 AS D', 'D.IN1AND = A.CODAND AND D.IN2AND = A.SANAND', null)
					->where([
						'A.TIDAND'=>$this->cId,
						'A.NIDAND'=>$this->nId,
						'A.NINAND'=>$this->nIngreso,
						'A.SANAND'=>$tcTipoAnte,
						])
					->where('A.CODAND','<>','17')
					->orderBy('A.CODAND, A.SANAND, A.FDCAND, A.HDCAND, A.LINAND')
					->getStatement();
			}else{
				$lcSql = $goDb
					->select('A.CODAND AS ANT_PPAL, A.SANAND AS ANT_SECUN, D.DESAND AS DSCR_GENERAL')
					->select('A.INDAND AS INDICE, A.LINAND AS CONS_LINEA, A.DESAND AS DESCRIPCION')
					->from('ANTPADL02 AS A')
					->leftJoin('ANTDESL02 AS D', 'D.IN1AND = A.CODAND AND D.IN2AND = A.SANAND', null)
					->innerJoin('RIAING AS I', 'A.TIDAND=I.TIDING AND A.NIDAND=I.NIDING AND A.NINAND =I.NIGING' )
					->where([
						'A.NINAND'=>$tnIngreso,
						'A.SANAND'=>$tcTipoAnte,
						])
					->where('A.CODAND','<>','17')
					->orderBy('A.CODAND, A.SANAND, A.FDCAND, A.HDCAND, A.LINAND')
					->getStatement();
			}
			$lcSql = 'SELECT DISTINCT SC.ANT_PPAL,SC.ANT_SECUN,SC.DSCR_GENERAL,SC.INDICE,SC.CONS_LINEA,SC.DESCRIPCION '
					. 'FROM ( ' . $lcSql . ' ) AS SC ';
			$laDatos = $goDb->getBindValue();
			$laAntecedentes = $goDb->query($lcSql, $laDatos, true);
			if(is_array($laAntecedentes)== true){
				if(count($laAntecedentes)>0){
					  $laAntecedente['tipoAnte'] = trim($laAntecedentes[0]['DSCR_GENERAL']);
					 foreach ($laAntecedentes as $laAntecede){
						 $laAntecedente['descripcion'].= trim($laAntecede['DESCRIPCION']). chr(13) . chr(10);
					 }
				}
			}
			$this->aAntecedentes = $laAntecedente;
			return $laAntecedente;
		}
	}

	/*
	 *	Obtiene descripción del plan
	 */
	public function obtenerDescripcionPlan()
	{
		if (!empty($this->cPlan)) {
			global $goDb;
			$oPlan = $goDb
				->select('DSCCON, TENCON, TABDSC')
				->from('FACPLNC')
				->leftJoin('PRMTAB','TENCON=TABCOD AND TABTIP=\'024\'',null)
				->where('PLNCON', '=', $this->cPlan)
				->get('array');
			$this->cPlanDescripcion = trim($oPlan['DSCCON']??'');
			$this->cPlanTipo = trim($oPlan['TENCON']??'');
			$this->cPlanTipoDsc = trim($oPlan['TABDSC']??'');
		} else {
			$this->cPlanDescripcion = $this->cPlanTipo = $this->cPlanTipoDsc = '';
		}
		return $this->cPlanDescripcion;
	}

	/*
	 *	Obtiene estado civil
	 */
	public function obtenerEstadoCivil()
	{
		global $goDb;
		$oEstCiv = $goDb
			->select('A.ECIINA CESTADO, B.TABDSC DESTADO')
			->from('RIAINAD A')
			->innerJoin('PRMTAB B', 'A.ECIINA=B.TABCOD AND B.TABTIP=\'ECI\'', null)
			->where(['A.INGINA'=>$this->nIngreso, 'A.TIDINA'=>$this->cId, 'A.NIDINA'=>$this->nId])
			->get('array');
		$this->cEstadoCivil = trim($oEstCiv['DESTADO']??'');
		return $this->cEstadoCivil;
	}

	/*
	 *	Obtiene centro de servicio
	 */
	public function obtenerCentroServicio()
	{
		global $goDb;
		$this->cCentroDeServicio='';
		$lcSeccionPaciente=$this->oHabitacion->cSeccion;
		$lcViaIngreso=$this->cVia;
		$loCentroDeServicio = $goDb->obtenerPrmtab('TABDSC','SCS', ['TABCOD'=>$lcSeccionPaciente], null);
		$this->cCentroDeServicio=trim(substr(trim(AplicacionFunciones::getValue($loCentroDeServicio, 'TABDSC', '')), 0, 4));

		if (empty($this->cCentroDeServicio) && !empty($lcViaIngreso)){
			$this->cCentroDeServicio = $goDb->obtenerTabmae1('SUBSTR(DE2TMA, 1, 4)', 'DATING', "CL1TMA='CENSERV' AND CL2TMA='$lcViaIngreso' AND ESTTMA=''", null, '');
		}
		return $this->cCentroDeServicio;
	}

	/*
	 *	Es ingreso activo
	 */
	public function esActivo()
	{
		return ($this->cEstado=='2');
	}


	/*
	 *	Es ingreso activo para registros
	 */
	public function esActivoParaRegistros($tnFecha=0)
	{
		global $goDb;
		$lnFecha = intval(date('Ymd'));
		if(isset($goDb)){
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lnFecha = intval($ltAhora->format('Ymd'));
		}
		$tnFecha=($tnFecha==0?$lnFecha:$tnFecha);
		return ($this->cEstado=='2' || $this->nEgresoFecha==0 || (($this->cEstado=='3' || $this->cEstado== '4') && ($this->nEgresoFecha==$tnFecha)));
	}

	/*
	 *	Obtiene array con la edad del paciente
	 *	Parametros:	$tnFecha Número que indica la fecha a la que se calcula la edad del paciente
	 */
	public function obtenerEdad($tnFecha = 0)
	{
		if (empty($tnFecha)){
			global $goDb;
			$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
			$lnFecha = intval($ltAhora->format('Ymd'));
			$lcOpcion=$goDb->obtenerTabMae1('OP1TMA','HCWEB',['CL1TMA'=>'EDAD','CL2TMA'=>'CALCFEC','ESTTMA'=>''],null,'');
			$tnFecha = $lcOpcion==1?$lnFecha:$this->nIngresoFecha;
		}
		$laEdad = explode('-', $this->oPaciente->getEdad($tnFecha, '%y-%m-%d'));
		$this->aEdad = ((is_array($laEdad)?count($laEdad)>=3:false)==true?[ 'y'=>$laEdad[0], 'm'=>$laEdad[1], 'd'=>$laEdad[2] ]:[ 'y'=>0, 'm'=>0, 'd'=>0 ]);
	}

	/*
	 *	Carga datos del ingreso a una fecha determinada
	 *	Parametros:	$tnIngreso	 Número ingreso
	 *				$tnFechaHora Número que indica la fecha y hora a la que se debe recuperar la información
	 */
	public function cargarIngresoPorFecha($tnIngreso=0, $tnFechaHora=0)
	{
		$this->nIngreso=0;

		global $goDb;
		if(isset($goDb)){
			if($tnIngreso>0){

				$lcFechaHora = str_replace( '/', '', str_replace( '-', '', str_replace( ' ', '', str_replace( 'T', '', str_replace( ':', '', $tnFechaHora.'' ) ) ) ) );

				$lcSql = "{$this->sqlIngresoPorFecha()}
					WHERE	d.nigind = :niging
						AND d.fecind*1000000+d.horind <= :fecind
						AND d.tidind <> '' AND d.nidind <> 0
					ORDER BY d.fecind*1000000+d.horind DESC
					FETCH FIRST 1 ROWS ONLY";
				$laData = [
					':niging' => $tnIngreso,
					':fecind' => $lcFechaHora,
				];
				$goDb->clearBindValue();
				$laIngresoSql = $goDb->query($lcSql, $laData, true);
				$goDb->clearBindValue();

				if (is_array($laIngresoSql)==true) {
					if (count($laIngresoSql)>0) {
						$laIngreso = array_map('trim', $laIngresoSql[0]);
						$this->cId = $laIngreso['TIPDOC'];
						$this->nId = intval($laIngreso['NUMDOC']);
						$this->nIngreso = intval($laIngreso['INGRESO']);
						$this->nEntidad = intval($laIngreso['CODENTIDAD']);
						$this->cPlan = $laIngreso['CODPLAN'];
						$this->cEstado = $laIngreso['ESTADOACT'];
						$this->cVia = $laIngreso['CODVIA'];
						$this->nIngresoFecha = intval($laIngreso['FECINGRESO']);
						$this->nIngresoHora = intval($laIngreso['HORAINGRESO']);
						$this->nEgresoFecha = intval($laIngreso['FECEGRESO']);
						$this->nEgresoHora = intval($laIngreso['HORAEGRESO']);
						$this->cEstadoCivil = $laIngreso['DESTCIVIL']??'';
						$this->cAfiliadoUsuario = trim($laIngreso['COD_TIPO_USUARIO']);
						$this->cDescripcionAfiliadoUsuario = trim($laIngreso['DESC_TIPO_USUARIO']);
						$this->oPaciente->cargarPacientePorFecha($this->cId, $this->nId, $tnFechaHora);
						$this->obtenerEdad();
						$this->oResponsable->cargarResponsable($this->nIngreso);

						//Información de la descripción de la vía de ingreso. tabla RIAVIA
						$lcDescVia = $goDb->from('RIAVIA')->where('CODVIA', '=', $this->cVia)->get('array');
						$this->cDescVia = trim($lcDescVia['DESVIA']);
					}
				}
			}
		}
	}

	/*
	 *	Retorna comando SQL para consulta de ingreso por fecha
	 */
	public function sqlIngresoPorFecha()
	{
		return 'SELECT	d.tidind AS TipDoc,
						d.nidind AS NumDoc,
						CONCAT(d.tidind,DIGITS(d.nidind)) AS TipNumDoc,
						d.nigind AS Ingreso,
						hc.nhcind AS NumHC,
						TRIM(d.plaind) AS CodPlan,
						d.viaind AS CodVia,
						TRIM(v.desvia) AS DesVia,
						TRIM(d.facind) AS EstadoFact,
						d.estind AS EstadoAct,
						d.feiind AS FecIngreso,
						d.hinind AS HoraIngreso,
						d.feeind AS FecEgreso,
						d.hreind AS HoraEgreso,
						d.tiuind as cod_tipo_usuario,
						TRIM(UPPER(SUBSTR(IFNULL(g.de1tma, \'\'), 1, 60))) AS DESC_TIPO_USUARIO,
						f.NI1CON AS CodEntidad,
						TRIM(f.dsccon) AS Entidad,
						TRIM(a.eciina) AS cEstCivil,
						TRIM(b.tabdsc) AS dEstCivil,
						d.fecind*1000000+d.horind AS FechaHora
				FROM riaingd AS d
					LEFT JOIN riaingd AS hc ON d.nigind=hc.nigind AND (
							SELECT fecind*1000000+horind FROM riaingd
							WHERE	nigind=d.nigind
								AND nhcind>0
								AND fecind*1000000+horind<=d.fecind*1000000+d.horind
							ORDER BY fecind DESC,horind DESC FETCH FIRST 1 ROWS ONLY
						) = hc.fecind*1000000+hc.horind
					LEFT JOIN facplnc AS f ON (
							SELECT plaind FROM riaingd
							WHERE	nigind=d.nigind
								AND fecind*1000000+horind<=d.fecind*1000000+d.horind
								AND	plaind<>\'\'
							ORDER BY fecind DESC,horind DESC FETCH FIRST 1 ROWS ONLY
						)=f.plncon
					LEFT JOIN riavia  AS v ON d.viaind=v.codvia
					LEFT JOIN riainad AS a ON d.nigind=a.ingina
					LEFT JOIN tabmae AS g ON g.tiptma=\'DATING\' AND g.CL1TMA=\'1\' AND d.tiuind=g.cl2tma
					LEFT JOIN prmtab  AS b ON a.eciina=b.tabcod AND b.tabtip=\'ECI\' ';
	}


	// Obtiene cantidad de horas desde que el paciente ingreso

	public function obtenerHorasIngreso($tnIngreso=0)
	{
		$lnHoras = 0;
		if ($tnIngreso) {
			global $goDb;
			$laIngreso = $goDb
				->select('FECING, HORING')
				->from('RIAING')
				->where('NIGING', '=', $tnIngreso)
				->get('array');
			if(is_array($laIngreso)){
				$lnFechaIng = $laIngreso['FECING'];
				$lnHoraIng = $laIngreso['HORING'];
				$ltAhora = new \DateTime($goDb->fechaHoraSistema());
				$ltFechaHoraIng = new \DateTime(AplicacionFunciones::formatFechaHora('fechahora', $lnFechaIng.' '.$lnHoraIng));
				$dteDiff  = $ltAhora->diff($ltFechaHoraIng);
				$lnHoras = (intval($dteDiff->format("%Y"))*8760) + (intval($dteDiff->format("%M"))*720) + (intval($dteDiff->format("%D"))*24) + (intval($dteDiff->format("%H")));
			}
		}
		return $lnHoras;
	}

	public function obtenerMedicoTratante($tnIngreso=0)
	{
		$tnIngreso = intval($tnIngreso);
		$laMedicoTratante = ['REGISTRO'=>'', 'NOMBRE'=>'', 'ESPECIALIDAD'=>'', 'ESPECIALIDAD_NOMBRE'=>''];
		$lcRegistro=$lcEspecialidad = '';

		if(!empty($tnIngreso)){
			global $goDb;
			if(isset($goDb)){
				if($tnIngreso>0){

					if(empty($lcRegistro)){
						$laCampos = ['O.REGOHO', 'O.ESPOHO'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('ORDHOSL01 O')
							->where('O.INGOHO', '=', $tnIngreso)
							->get('array');
						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$lcRegistro = trim(is_null($laRegistro['REGOHO'])?'':$laRegistro['REGOHO']);
								$lcEspecialidad = trim(is_null($laRegistro['ESPOHO'])?'':$laRegistro['ESPOHO']);
							}
						}
					}

					if(empty($lcRegistro)){
						$laCampos = ['I.MEDINT', 'I.DPTINT'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('RIAINGT I')
							->where('I.NIGINT', '=', $tnIngreso)
							->get('array');
						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$lcRegistro = substr(trim(is_null($laRegistro['MEDINT'])?'':$laRegistro['MEDINT']),0,13);
								$lcEspecialidad = trim(is_null($laRegistro['DPTINT'])?'':$laRegistro['DPTINT']);
							}
						}
					}

					if(empty($lcRegistro)){
						$laCampos = ['O.REGOHD', 'O.ESPOHD'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('ORDHOD O')
							->where('O.INGOHD', '=', $tnIngreso)
							->get('array');
						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$lcRegistro = trim(is_null($laRegistro['REGOHD'])?'':$laRegistro['REGOHD']);
								$lcEspecialidad = trim(is_null($laRegistro['ESPOHD'])?'':$laRegistro['ESPOHD']);
							}
						}
					}

					if(!empty($lcRegistro) && empty($lcEspecialidad)){
						$laCampos = ['R.CODRGM'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('RIARGMN R')
							->where('R.REGMED', '=', $lcRegistro)
							->get('array');
						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$lcEspecialidad = trim(is_null($laRegistro['CODRGM'])?'':$laRegistro['CODRGM']);
							}
						}
					}
					$lcRegistro = (!empty($lcRegistro)?str_pad(trim($lcRegistro), 13, '0', STR_PAD_LEFT):'');
					$lcEspecialidad = trim($lcEspecialidad);
				}
			}

			if(!empty($lcRegistro)){
				$loMedico = new Medico($lcRegistro);
				$loEspecialidad = new Especialidad($lcEspecialidad);
				$laMedicoTratante['REGISTRO'] = $lcRegistro;
				$laMedicoTratante['NOMBRE'] = trim($loMedico->getNombreCompleto());
				$laMedicoTratante['ESPECIALIDAD'] = $lcEspecialidad;
				$laMedicoTratante['ESPECIALIDAD_NOMBRE'] = trim(is_null($loEspecialidad->cNombre)?'':$loEspecialidad->cNombre);
			}
		}
		return $laMedicoTratante;
	}


	public function getParametrosModuloIngreso($tcLogUsu = '')
	{
		$laParametros = array();
		global $goDb;
		if(isset($goDb)){
			$laParametros = [
				'CargarPacienteModoIngreso' => ('SI' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01300103'"),'DE2TMA',''))))),
				'DeshabilitaTriageUrgencias' => ('SI' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301007'"),'DE2TMA',''))))),
				'DiasMaximoEgreso' => intval(AplicacionFunciones::getValue($goDb->obtenerTabMae("TRIM(DE2TMA) || '' || TRIM(OP5TMA) AS DIAS", 'DATING', "CL1TMA='DIAEGRE' AND ESTTMA=''"),'DIAS',8)),
				'EdadMaximaPaciente' => intval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01300102'"),'DE2TMA',0)),
				'EdadMaximaPaciente' => intval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01300102'"),'DE2TMA',0)),
				'EdadMinimaResponsable' => intval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01300101'"),'DE2TMA',0)),
				'ExpresionHabilitaDirecion' => ('T' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('CL2TMA', 'DATING', "CL1TMA='EXPREG' AND ESTTMA=''"),'CL2TMA','F'))))),
				'ExpresionHabilitaNombresApellidos' => ('T' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('CL2TMA', 'DATING', "CL1TMA='EXPREGN' AND ESTTMA=''"),'CL2TMA','F'))))),
				'ExpresionRegularDireccion' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('TRIM(DE2TMA)||TRIM(OP5TMA) AS RULE', 'DATING', "CL1TMA='EXPREG' AND ESTTMA=''"),'RULE',''))),
				'ExpresionRegularNombresApellidos' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('TRIM(DE2TMA)||TRIM(OP5TMA) AS RULE', 'DATING', "CL1TMA='EXPREGN' AND ESTTMA=''"),'RULE',''))),
				'ListaTipoIdePasaporte' => strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('TRIM(DE2TMA) AS LISTA', 'DATING', "CL1TMA='TIPPAS' AND ESTTMA=''"),'LISTA','P')))),
				'ManualJustifica' => ('SI' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301006'"),'DE2TMA',''))))),
				'ManualJustificaAncho' => intval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301005'"),'DE2TMA',0)),
				'ManualJustificaIdTipo' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301004'"),'DE2TMA',''))),
				'NoCargarPlanesPrevios' => ('SI' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01200108'"),'DE2TMA',''))))),
				'PermisoModificarDatos' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('OP1TMA', 'DATPAC', "CL1TMA='DATOS' AND CL2TMA='{$tcLogUsu}' AND ESTTMA=''"),'OP1TMA',''))),
				'PlanMaximo' => 9,
				'PlanParticular' => 'SHAIO1',
				'PlanParticularAfiliadoTipoDisplay' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE1TMA', 'DATING', "CL1TMA='2' AND ESTTMA='' AND SUBSTR(TRIM(CL2TMA),1,1)='A'"),'DE1TMA',''))),
				'PlanParticularTipoDisplay' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE1TMA', 'DATING', "CL1TMA='1' AND ESTTMA='' AND SUBSTR(TRIM(CL2TMA),1,1)='C'"),'DE1TMA',''))),
				'PlanRestringidoUrgencias' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301001'"),'DE2TMA',''))),
				'PlanesControlaAdicionales' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA','DATING',"CL1TMA='8' AND ESTTMA=''"),'DE2TMA',''))),
				'RequiereInformacion' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01200102'"),'DE2TMA',''))),
				'TiposPermitidos' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01200101'"),'DE2TMA',''))),
				'UsuarioConsultoParametros' => $tcLogUsu,
				'ViasFechaIngreso' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae("TRIM(DE2TMA) || '' || TRIM(OP5TMA) AS VIAS", 'DATING', "CL1TMA='VIAINGR' AND ESTTMA=''"),'VIAS',''))),
				'ViasOrden' => trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='VIAINGOR' AND ESTTMA=''"),'DE2TMA',''))),
				'ViasRequierenReferencia' => strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='01301008'"),'DE2TMA','')))),
			];
		}
		return $laParametros;
	}


	private function getDigitoVerificacion($tnIdentificacion = 0)
	{
		$tnIdentificacion = intval($tnIdentificacion);
		$lcIdentificacion = str_pad(trim(strval($tnIdentificacion)), 15, '0', STR_PAD_LEFT);
		$lcBase = '716759534743413729231917130703'; //BASES DE MULTIPLICACION DE DIGITOS
		$lnTT=0;
		$lnRS=0;
		$lnHash=0;

		for($lnCaracter=0; $lnCaracter<15; $lnCaracter++){
			$lnRS = intval(substr($lcIdentificacion, $lnCaracter, 1)) * intval(substr($lcBase, $lnHash, 2));
			$lnHash += 2;
			$lnTT = $lnTT + $lnRS;
		}
		$lnDigito = (($lnTT - (intval($lnTT / 11) * 11)) > 1 ? 11 - ($lnTT - (intval($lnTT / 11) * 11)) : ($lnTT - (intval($lnTT / 11) * 11)));
		return $lnDigito;
	}

	private function nuevoNumeroHc()
	{
		$lnHistoria = 0;
		$lnCodigo = 20;
		$llFlagExiste = true;
		$laRegistro=array();
		global $goDb;
		if(isset($goDb)){
			while($llFlagExiste == true){
				$lnHistoria = $goDb->secuencia('SEQ_NUMHIS', $lnCodigo);
				if($lnHistoria > 0){
					$laRegistro = $goDb
						->select('P.NHCPAC HISTORIA')
						->from('RIAPACL3 P')
						->where('P.NHCPAC', '=', $lnHistoria)
						->get('array');

					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
				}
			}
		}

		return $lnHistoria;
	}

	private function ingresoExiste($tnIngreso = 0, $tcDocumentoTipo = '', $tnDocumentoNumero = 0, $tcAlcance = 'INGRESO')
	{
		$tnIngreso = intval($tnIngreso);
		$tcDocumentoTipo = trim(strval($tcDocumentoTipo));
		$tnDocumentoNumero = intval($tnDocumentoNumero);
		$tcAlcance = trim(strtoupper(strval($tcAlcance)));
		$llFlagExiste = false;

		global $goDb;
		if(isset($goDb)){
			switch($tcAlcance){
				case 'ALTERNA':
					$laCampos = ['I.INGINA INGRESO','I.TIDINA DOCUMENTO_TIPO','I.NIDINA DOCUMENTO_NUMERO'];
					$lcWhere = (!empty($tcDocumentoTipo) && !empty($tnDocumentoNumero) ? sprintf("I.TIDINA='%s' AND I.NIDINA=%s", $tcDocumentoTipo, $tnDocumentoNumero) : "");
					$lcWhere = $lcWhere . (empty($lcWhere)?"":" AND ") . sprintf("I.INGINA=%s", $tnIngreso);

					$laRegistro = $goDb
						->select($laCampos)
						->from('RIAINADL01 I')
						->where($lcWhere)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);

					break;

				default:
					$laCampos = ['I.NIGING INGRESO','I.TIDING DOCUMENTO_TIPO','I.NIDING DOCUMENTO_NUMERO'];
					$lcWhere = (!empty($tcDocumentoTipo) && !empty($tnDocumentoNumero) ? sprintf("I.TIDING='%s' AND I.NIDING=%s", $tcDocumentoTipo, $tnDocumentoNumero) : "");
					$lcWhere = $lcWhere . (empty($lcWhere)?"":" AND ") . sprintf("I.NIGING=%s", $tnIngreso);

					$laRegistro = $goDb
						->select($laCampos)
						->from('RIAING I')
						->where($lcWhere)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
			}
		}

		return $llFlagExiste;
	}

	private function pacienteExiste($tcDocumentoTipo = '', $tnDocumentoNumero = 0, $tcAlcance = 'PRINCIPAL')
	{
		$tcDocumentoTipo = trim(strval($tcDocumentoTipo));
		$tnDocumentoNumero = intval($tnDocumentoNumero);
		$tcAlcance = trim(strtoupper(strval($tcAlcance)));
		$llFlagExiste = false;

		global $goDb;
		if(isset($goDb)){
			switch($tcAlcance){
				case 'ALTERNA':
					$laCampos = ['P.TIDPAL DOCUMENTO_TIPO','P.NIDPAL DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('PACALT P')
						->where('P.TIDPAL', '=', $tcDocumentoTipo)
						->where('P.NIDPAL', '=', $tnDocumentoNumero)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
					break;

				case 'TERCERO':
					$laCampos = ['P.TE1COD DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('PRMTE1 P')
						->where('P.TE1COD', '=', str_pad(trim(strval($tnDocumentoNumero)), 13, '0', STR_PAD_LEFT))
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
					break;

				default:
					$laCampos = ['P.TIDPAC DOCUMENTO_TIPO','P.NIDPAC DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('RIAPAC P')
						->where('P.TIDPAC', '=', $tcDocumentoTipo)
						->where('P.NIDPAC', '=', $tnDocumentoNumero)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
			}
		}

		return $llFlagExiste;
	}

	private function responsableExiste($tnIngreso = 0, $tcDocumentoTipo = '', $tnDocumentoNumero = 0, $tcAlcance = 'PRINCIPAL')
	{
		$tnIngreso = intval($tnIngreso);
		$tcDocumentoTipo = trim(strval($tcDocumentoTipo));
		$tnDocumentoNumero = intval($tnDocumentoNumero);
		$tcAlcance = trim(strtoupper(strval($tcAlcance)));
		$llFlagExiste = false;

		global $goDb;
		if(isset($goDb)){
			switch($tcAlcance){
				case 'PAGARE':
					$laCampos = ['P.TIPPAG DOCUMENTO_TIPO','P.IDPPAG DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('PAGARE P')
						->where('P.NIGPAG', '=', $tnIngreso)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
					break;

				case 'TERCERO':
					$laCampos = ['P.TE1COD DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('PRMTE1 P')
						->where('P.TE1COD', '=', str_pad(trim(strval($tnDocumentoNumero)), 13, '0', STR_PAD_LEFT))
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
					break;

				default:
					$laCampos = ['P.TIDRES DOCUMENTO_TIPO','P.NIDRES DOCUMENTO_NUMERO'];
					$laRegistro = $goDb
						->select($laCampos)
						->from('RIARES P')
						->where('P.NIGRES', '=', $tnIngreso)
						->get('array');
					$llFlagExiste = (is_array($laRegistro)==true ? count($laRegistro)>0 : false);
			}
		}

		return $llFlagExiste;
	}

	private function nuevoNumeroIngreso()
	{
		$lnIngreso = 0;
		$lnCodigo = 1;
		$llFlagExiste = true;

		global $goDb;
		if(isset($goDb)){
			$llUsarSecuencia = ('SI' == strtoupper(trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'DATING', "CL1TMA='INGRESO' AND CL2TMA='09010199'"),'DE2TMA','')))));
			global $goDb;

			if(isset($goDb)){
				while($llFlagExiste == true){
					$lnIngreso = -1;
					while($lnIngreso <= 0){
						$lnIngreso = $goDb->secuencia('SEQ_RIAING', $lnCodigo);
					}

					if($lnIngreso > 0){
						$laRegistro = $goDb
							->count('I.NIGING', 'INGRESOS')
							->from('RIAING I')
							->where('I.NIGING', '=', $lnIngreso)
							->get('array');

						$llFlagExiste = (is_array($laRegistro)==true ? (count($laRegistro)>0 ? intval($laRegistro['INGRESOS'])>0 : false ) : false );
					}
				}
			}
		}

		return $lnIngreso;
	}

	private function getIngresoValidacion($tcValidacion='', &$taIngreso=array(), &$taPlanes= array(), &$tcMensaje = '')
	{
		$tcValidacion = trim(strtolower(strval($tcValidacion)));
		$llResultado = true;
		$tcMensaje = '';

		global $goDb;
		if(isset($goDb)){
			switch($tcValidacion){
				case 'medico-tratante':
					$llResultado = !($taIngreso['cMetodo']<>"MODIFICAR" && in_array($taIngreso['cIngresoVia'],["05","06"])==true && empty(trim($taIngreso['cMedicoTratanteId']))==true);
					if($llResultado == false){
						$tcMensaje = 'El medico tratante es obligatorio, revise por favor.';
					}
					break;

				case 'nueva-via':
					if($taIngreso['cMetodo']=="MODIFICAR"){
						if(in_array($taIngreso['cIngresoVia'],['05','6'])==true){
							if(in_array($taIngreso['cIngresoViaAnterior'],['05','06'])==false){
								$tcMensaje = 'Vía de ingreso no valida.';
								$llResultado = false;
							}
						}
					}
					break;

				case 'estado-ingreso':
					if($taIngreso['cMetodo']=="MODIFICAR"){
						if(intval($taIngreso['cEstadoIngresoAnterior'])!==4 && intval($taIngreso['cEstadoIngreso'])==4){
							$tcMensaje = 'Ingreso estado no permitido, revise por favor.';
							$llResultado = false;
						}else{
							$laCampos = ['P.NIGING INGRESO'];
							$laRegistro = $goDb
								->select($laCampos)
								->from('RIAINGL2 P')
								->where('P.TIDING', '=', $taIngreso['cPacienteId'])
								->where('P.NIDING', '=', $taIngreso['nPacienteId'])
								->where('P.NIGING', '<>', $taIngreso['nIngreso'])
								->where('P.ESTING', '=', '2')
								->get('array');

							if(is_array($laRegistro)==true){
								if(count($laRegistro)==0){
									$tcMensaje = 'Paciente ya tiene ingreso ABIERTO no se puede crear, revise por favor.';
									$llResultado = false;
								}
							}
						}
					}

					break;

				case 'plan':
					$laPropiedadesIngreso = $this->getParametrosModuloIngreso();

					// Validando restriciones de plan
					if(in_array(trim(strtoupper($taIngreso['cPlanUsar'])), explode(',', str_replace("'",'',$laPropiedadesIngreso['PlanRestringidoUrgencias'])))==true && trim(strtoupper($taIngreso['cIngresoVia']))=="01"){
						$llResultado = false;
						$tcMensaje = "Plan no autorizado para la vía actual";
					}

					// Verifica si es plan deshabilitado
					if($llResultado==true){
						$laCampos = ['P.DTECOB VIGENCIA'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('FACCBPL P')
							->where('P.PLNCOB', '=', $taIngreso['cPlanUsar'])
							->where('P.CODCOB', '=', 'URGE')
							->get('array');
						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$llResultado = false;
								$tcMensaje = 'ATENCIÓN: Entidad no tiene contrato vigente desde '.strval($laRegistro['VIGENCIA']).'. Plan Deshabilitado';
							}
						}
					}
					break;

				case 'eps':
					$lnExisteEps = 0;
					foreach($taPlanes as $laPlan){
						$lnExisteEps += (trim($laPlan['TENPLA'])=='05'?1:0);
					}
					$taIngreso['nMapla'] = ($lnExisteEps>0 ? $lnExisteEps : $taIngreso['nMapla']);

					if(strtoupper(trim($taIngreso['cMetodo']))<>"HABITACION"){
						if(trim($taIngreso['cIngresoVia'])<>'02' && $taIngreso['nMapla']==0 && strtoupper(trim($taIngreso['cPacienteNoEPS']))=='NO'){
							$llResultado = false;
							$tcMensaje = "No hay Plan con entidad tipo <b>E.P.S.</b>. Se debe indicar uno, cuando el paciente no tiene, se debe seleccionar SI en el campo <b>Paciente indica NO E.P.S.<b>";
						}
					}

					break;

				case 'habitacion':
					if($taIngreso['cMetodo']<>"MODIFICAR"){
						// Valida si la habitación es de urgencias hay no deja hospitalizar
						if(!empty($taIngreso['cSeccion']) && !empty($taIngreso['cHabitacion'])){
							$laCampos = ['P.LIQHAB TIPO'];
							$laRegistro = $goDb
								->select($laCampos)
								->from('FACHABL0 P')
								->where('P.SECHAB', '=', $taIngreso['cSeccion'])
								->where('P.NUMHAB', '=', $taIngreso['cHabitacion'])
								->get('array');
							$lcTipoHabitacion = strtoupper(trim(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (!is_null($laRegistro['TIPO']) ? $laRegistro['TIPO'] : '' ) : '' ) : '' ));
							if($lcTipoHabitacion=='U'){
								$llResultado = false;
								$tcMensaje = "En cama de urgencias no puede Hospitalizar al paciente";
							}
						}
					}

					break;

				case 'pasaporte':
				case 'fecha-ingreso-egreso':
				case 'fecha-nacimiento':
				case 'consecutivo-historia-paciente':
					break;

			}
		}
		return $llResultado;
	}


	private function guardarIngreso(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0, $tlObligaNuevoIngreso = false)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$tlObligaNuevoIngreso = boolval($tlObligaNuevoIngreso);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			$llExisteIngreso = false;
			$lnFechaEgreso = 0;
			$lnHoraEgreso = 0;

			$lnFechaIngreso = strval($taIngreso['cIngresoVia']=="01" && $taIngreso['cMetodo']=="TRIAGE" ? $tnFecha : ($taIngreso['cMetodo']=="MODIFICAR" ? $taIngreso['nFechaIngreso']: 0));
			$lnFechaIngreso = intval(str_replace('/','',str_replace('-','',$lnFechaIngreso)));

			$lnHoraIngreso = strval($taIngreso['cIngresoVia']=="01" && $taIngreso['cMetodo']=="TRIAGE" ? $tnHora : ($taIngreso['cMetodo']=="MODIFICAR" ? $taIngreso['HoraIngreso']: 0));
			$lnHoraIngreso = intval(str_replace(':','',$lnHoraIngreso));

			$taIngreso['cPacienteCarnet']=trim(substr(trim(strval($taIngreso['nPacienteId'])),-13));
			$lcCentroRemision=(trim($taIngreso['cPlanUsar'])=='SHAICC'?'CC':'');

			if($tlObligaNuevoIngreso==true){
				$llExisteIngreso=$this->ingresoExiste($taIngreso['nIngreso']);
			}

			if($tlObligaNuevoIngreso==false || ($tlObligaNuevoIngreso==true && $llExisteIngreso==false)){
				$lcWhere = '';
				$laCampos = ['TIDING'=>$taIngreso['cPacienteId'],
							  'NIDING'=>$taIngreso['nPacienteId'],
							  'NIGING'=>$taIngreso['nIngreso'],
							  'VIAING'=>$taIngreso['cIngresoVia'],
							  'FEIING'=>$lnFechaIngreso,
							  'ESTING'=>$taIngreso['cEstadoIngreso'],
							  'ENTING'=>$taIngreso['nPlanUsarEntidad'],
							  'REGING'=>$taIngreso['nPlanUsarRegional'],
							  'CONING'=>$taIngreso['nPlanUsarContrato'],
							  'PLAING'=>$taIngreso['cPlanUsar'],
							  'ETTING'=>$taIngreso['nPlanUsarEstrato'],
							  'NCAING'=>$taIngreso['cPacienteCarnet'],
							  'TIAING'=>$taIngreso['nPlanUsarAfiliadoTipo'],
							  'TIUING'=>$taIngreso['nPlanUsarTipo'],
							  'CRMING'=>$lcCentroRemision,
							  'FN1ING'=>0,
							  'PERING'=>0
							];
				$laTabla = 'RIAING';

				$llExisteIngreso=$this->ingresoExiste($taIngreso['nIngreso']);
				if($llExisteIngreso==false){
					$laCampos['USRING'] = $tcUsuario;
					$laCampos['PGMING'] = $tcPrograma;
					$laCampos['FECING'] = $tnFecha;
					$laCampos['HORING'] = $tnHora;
					$llResultado = $goDb->from($laTabla)->insertar($laCampos);

				}else{
					if($lnFechaEgreso>0){
						$laCampos['FEEING']=$lnFechaEgreso;
					}
					if($lnHoraEgreso>0){
						$laCampos['HREING']=$lnHoraEgreso;
					}
					if($taIngreso['cIngresoVia']=="01"){
						$laCampos['USRING']=$tcUsuario;
						$laCampos['PGMING']=$tcPrograma;
					}
					if($lnFechaIngreso>0){
						$laCampos['FECING']=$lnFechaIngreso;
					}
					if($lnHoraIngreso>0){
						$laCampos['HORING']=$lnHoraIngreso;
					}
					$laCampos['UMOING']=$tcUsuario;
					$laCampos['PMOING']=$tcPrograma;
					$laCampos['FMOING']=$tnFecha;
					$laCampos['HMOING']=$tnHora;

					$llResultado = $goDb->from($laTabla)
										->where('NIGING','=', $taIngreso['nIngreso'])
										->actualizar($laCampos);
				}
			}

			// TABLAS COMPLEMENTARIAS
			if($llResultado == true){

				// Tabla alterna RIAINAD
				$llExisteIngreso = $this->ingresoExiste($taIngreso['nIngreso'],$taIngreso['cPacienteId'],$taIngreso['nPacienteId'],'ALTERNA');
				$laCampos = [
								'INGINA'=>$taIngreso['nIngreso'],
								'TIDINA'=>$taIngreso['cPacienteId'],
								'NIDINA'=>$taIngreso['nPacienteId'],
								'CL1INA'=>0,
								'CL2INA'=>0,
								'PREINA'=>$taIngreso['cPacienteRespira'],
								'ECIINA'=>substr(trim($taIngreso['cPacienteEstadoCivil']),0,3),
								'OP1INA'=>substr(trim($taIngreso['cRemitido']),0,1),
								'OP2INA'=>substr(trim($taIngreso['cRemiteEntidad']),0,60),
								'OP3INA'=>str_pad(trim($taIngreso['cRemitePais']),8,"0", STR_PAD_LEFT)." ".str_pad(trim($taIngreso['cRemiteDepartamento']),8,"0", STR_PAD_LEFT)." ".str_pad(trim($taIngreso['cRemiteCiudad']),8,"0", STR_PAD_LEFT),
								'OP4INA'=>(strtoupper(trim($taIngreso['cPacienteNoEPS']))=='SI' ? 1 : 0 )
							];

				$laTabla = 'RIAINADL01';

				if($llExisteIngreso==false){
					$laCampos['USRINA'] = $tcUsuario;
					$laCampos['PGMINA'] = $tcPrograma;
					$laCampos['FECINA'] = $tnFecha;
					$laCampos['HORINA'] = $tnHora;

					$llResultado = $goDb->from($laTabla)->insertar($laCampos);

				}else{
					$laCampos['UMOINA'] = $tcUsuario;
					$laCampos['PMOINA'] = $tcPrograma;
					$laCampos['FMOINA'] = $tnFecha;
					$laCampos['HMOINA'] = $tnHora;

					$llResultado = $goDb->from($laTabla)
										->where('INGINA', '=', $taIngreso['nIngreso'])
										->where('TIDINA', '=', $taIngreso['cPacienteId'])
										->where('NIDINA', '=', $taIngreso['nPacienteId'])
										->actualizar($laCampos);
				}



				// Tabla alterna RIAINGD
				$laCampos = [
								'TIDIND'=>$taIngreso['cPacienteId'],
								'NIDIND'=>$taIngreso['nPacienteId'],
								'NIGIND'=>$taIngreso['nIngreso'],
								'NHCIND'=>$taIngreso['nHistoria'],
								'ENTIND'=>$taIngreso['nPlanUsarEntidad'],
								'REGIND'=>$taIngreso['nPlanUsarRegional'],
								'CONIND'=>$taIngreso['nPlanUsarContrato'],
								'PLAIND'=>$taIngreso['cPlanUsar'],
								'ETTIND'=>$taIngreso['nPlanUsarEstrato'],
								'NCAIND'=>$taIngreso['cPacienteCarnet'],
								'VIAIND'=>$taIngreso['cIngresoVia'],
								'TIAIND'=>$taIngreso['nPlanUsarAfiliadoTipo'],
								'TIUIND'=>$taIngreso['nPlanUsarTipo'],
								'MEDIND'=>$taIngreso['cMedicoTratanteId'],
								'ESTIND'=>$taIngreso['cEstadoIngreso'],
								'FEIIND'=>$lnFechaIngreso,
								'HININD'=>$lnHoraIngreso,
								'FEEIND'=>$lnFechaEgreso,
								'HREIND'=>$lnHoraEgreso,
								'USRIND'=>$tcUsuario,
								'PGMIND'=>$tcPrograma,
								'FECIND'=>$tnFecha,
								'HORIND'=>$tnHora,
							];
				$laTabla = 'RIAINGD';
				$llResultado = $goDb->from($laTabla)->insertar($laCampos);

			}
		}

		return $llResultado;
	}

	private function guardarPaciente(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			// TABLA PRINCIPAL RIAPAC
			$lnMarcaModificaPaciente=1;
			$llPacienteExiste = $this->pacienteExiste($taIngreso['cPacienteId'],$taIngreso['nPacienteId'], 'PRINCIPAL');

			if($llPacienteExiste == true){
				$laCampos = ['P.NHCPAC HISTORIA','P.CCIPAC CITA', 'P.CCOPAC CONSULTA'];
				$laRegistro = $goDb->select($laCampos)
								   ->from('RIAPAC P')
								   ->where('P.TIDPAC', '=', $taIngreso['cPacienteId'])
								   ->where('P.NIDPAC', '=', $taIngreso['nPacienteId'])
								   ->get('array');
				if(is_array($laRegistro)==true){
					if(count($laRegistro)>0){
						$taIngreso['nCita'] = intval(is_null($laRegistro['CITA'])?0:$laRegistro['CITA'])+1;
						$taIngreso['nConsulta'] = intval(is_null($laRegistro['CONSULTA'])?0:$laRegistro['CONSULTA']);
					}
				}
			}

			$laCampos = ['TIDPAC'=>$taIngreso['cPacienteId'],
						 'NIDPAC'=>$taIngreso['nPacienteId'],
						 'NHCPAC'=>$taIngreso['nHistoria'],
						 'NM1PAC'=>substr(trim($taIngreso['cPacienteNombre1']),0,15),
						 'NM2PAC'=>substr(trim($taIngreso['cPacienteNombre2']),0,15),
						 'AP1PAC'=>substr(trim($taIngreso['cPacienteApellido1']),0,15),
						 'AP2PAC'=>substr(trim($taIngreso['cPacienteApellido2']),0,15),
						 'PAIPAC'=>$taIngreso['nNacioPais'],
						 'DEPPAC'=>$taIngreso['nNacioDepartamento'],
						 'MUNPAC'=>$taIngreso['nNacioCiudad'],
						 'FNAPAC'=>$taIngreso['nNacimiento'],
						 'SEXPAC'=>$taIngreso['cPacienteGenero'],
						 'RAZPAC'=>$taIngreso['nRaza'],
						 'OCUPAC'=>$taIngreso['nPacienteLaboralOcupacion'],
						 'MAIPAC'=>substr(trim($taIngreso['cPacienteEmail']),0,60),
						 'PARPAC'=>$taIngreso['nPacienteRecidePais'],
						 'DERPAC'=>$taIngreso['nPacienteRecideDepartamento'],
						 'MURPAC'=>$taIngreso['nPacienteRecideCiudad'],
						 'DR1PAC'=>substr(trim($taIngreso['cPacienteRecideDireccion']),0,30),
						 'DR2PAC'=>substr(trim($taIngreso['cPacienteRecideBarrio']),0,30),
						 'TELPAC'=>substr(trim($taIngreso['cPacienteTelefono1']),0,60),
						 'ZORPAC'=>$taIngreso['nPacienteRecideZona'],
						 'PAOPAC'=>0,
						 'DEOPAC'=>0,
						 'MUOPAC'=>0,
						 'DO1PAC'=>substr(trim($taIngreso['cPacienteLaboralDireccion']),0,30),
						 'DO2PAC'=>'',
						 'TETPAC'=>substr(trim($taIngreso['cPacienteLaboralTelefono']),0,60),
						 'FA1PAC'=>substr(trim($taIngreso['cPacienteEstadoCivil']),0,10),
						 'FA2PAC'=>substr(trim($taIngreso['cPacienteTelefono4']),0,20)
						];

			$laTabla = 'RIAPAC';

			if($llPacienteExiste==false){
				$laCampos['USRPAC'] = $tcUsuario;
				$laCampos['PGMPAC'] = $tcPrograma;
				$laCampos['FECPAC'] = $tnFecha;
				$laCampos['HORPAC'] = $tnHora;

				$llResultado = $goDb->from($laTabla)->insertar($laCampos);

			}else{
				$laCampos['CCOPAC'] = $taIngreso['nConsulta'];
				$laCampos['CCIPAC'] = $taIngreso['nCita'];
				$laCampos['FN1PAC'] = $lnMarcaModificaPaciente;
				$laCampos['UMOPAC'] = $tcUsuario;
				$laCampos['PMOPAC'] = $tcPrograma;
				$laCampos['FMOPAC'] = $tnFecha;
				$laCampos['HMOPAC'] = $tnHora;

				$llResultado = $goDb->from($laTabla)
									->where('TIDPAC', '=', $taIngreso['cPacienteId'])
									->where('NIDPAC', '=', $taIngreso['nPacienteId'])
									->actualizar($laCampos);
			}


			// TABLAS COMPLEMENTARIAS
			if($llResultado == true){

				$lcEtnicoEducativo = substr(trim(str_pad(trim($taIngreso['cPacientePertenenciaEtnica']), 2, "0", STR_PAD_LEFT).str_pad(trim($taIngreso['cPacienteNivelEducativo']), 2, "0", STR_PAD_LEFT)),0,10);

				// Actualización en portal pacientes
				$laCampos = ['CORREO'=>substr(trim($taIngreso['cPacienteEmail']),0,50)];
				$goDb->from('PACMENSEG')
					 ->where('IDTIPO', '=', $taIngreso['cPacienteId'])
					 ->where('IDNUME', '=', $taIngreso['nPacienteId'])
					 ->actualizar($laCampos);


				// Tabla alterna PACALT
				$llPacienteExiste = $this->pacienteExiste($taIngreso['cPacienteId'],$taIngreso['nPacienteId'], 'ALTERNA');
				$laCampos = [
								'TIDPAL'=>$taIngreso['cPacienteId'],
								'NIDPAL'=>$taIngreso['nPacienteId'],
								'NM1PAL'=>substr(trim($taIngreso['cPacienteNombre1']),0,40),
								'NM2PAL'=>substr(trim($taIngreso['cPacienteNombre2']),0,40),
								'AP1PAL'=>substr(trim($taIngreso['cPacienteApellido1']),0,40),
								'AP2PAL'=>substr(trim($taIngreso['cPacienteApellido2']),0,40),
								'FNAPAL'=>$taIngreso['nNacimiento'],
								'SEXPAL'=>$taIngreso['cPacienteGenero'],
								'MAIPAL'=>substr(trim($taIngreso['cPacienteEmail']),0,60),
								'PARPAL'=>$taIngreso['nPacienteRecidePais'],
								'DERPAL'=>$taIngreso['nPacienteRecideDepartamento'],
								'MURPAL'=>$taIngreso['nPacienteRecideCiudad'],
								'LOCPAL'=>$taIngreso['nPacienteRecideLocalidad'],
								'TP1PAL'=>intval($taIngreso['cPacienteTelefono1']),
								'TP2PAL'=>intval($taIngreso['cPacienteTelefono2']),
								'CP1PAL'=>intval($taIngreso['cPacienteTelefono3']),
								'CP2PAL'=>intval($taIngreso['cPacienteTelefono4']),
								'DIRPAL'=>substr(trim($taIngreso['cPacienteRecideDireccion']),0,220),
								'TR1PAL'=>intval($taIngreso['cResponsableTelefono1']),
								'TR2PAL'=>intval($taIngreso['cResponsableTelefono2']),
								'CR1PAL'=>intval($taIngreso['cResponsableTelefono3']),
								'CR2PAL'=>intval($taIngreso['cResponsableTelefono4']),
								'CVRPAL'=>substr(trim($taIngreso['cPacienteRecideCiudadDisplay']),0,220),
								'OP2PAL'=>$lcEtnicoEducativo,
								'OP5PAL'=>substr(trim($taIngreso['cConsultaPasaporte']),0,220),
								'OP6PAL'=>substr(trim($taIngreso['cPacientePermisoPermanencia']),0,220),
								'OP8PAL'=>substr(trim($taIngreso['cPacienteTieneEmail']),0,1),
								'OP9PAL'=>substr(trim($taIngreso['cPacienteGrupoRH']),0,10),
								'OP10AL'=>(strtoupper(trim($taIngreso['cEpicrisisEmail']))=='SI'?1:2),
								'OP12AL'=>substr(trim($taIngreso['cResponsableTieneEmail'])."|".trim($taIngreso['cResponsableEmail']),0,220),
								'OP13AL'=>substr(trim($taIngreso['cPacienteLugarExpedicion']),0,220)
							];

				$laTabla = 'PACALT';

				if($llPacienteExiste==false){
					$laCampos['USRPAL'] = $tcUsuario;
					$laCampos['PGMPAL'] = $tcPrograma;
					$laCampos['FECPAL'] = $tnFecha;
					$laCampos['HORPAL'] = $tnHora;

					$goDb->from($laTabla)->insertar($laCampos);

				}else{
					$laCampos['UMOPAL'] = $tcUsuario;
					$laCampos['PMOPAL'] = $tcPrograma;
					$laCampos['FMOPAL'] = $tnFecha;
					$laCampos['HMOPAL'] = $tnHora;

					$goDb->from($laTabla)
						 ->where('TIDPAL', '=', $taIngreso['cPacienteId'])
						 ->where('NIDPAL', '=', $taIngreso['nPacienteId'])
						 ->actualizar($laCampos);
				}

				// Tabla detalle PACDET
				$lcPasaportePermiso = str_pad(trim($taIngreso['cConsultaPasaporte']), 100, " ", STR_PAD_LEFT) . str_pad(trim($taIngreso['cPacientePermisoPermanencia']), 100, " ", STR_PAD_LEFT);
				$laCampos = [
								'TIDPAD'=>$taIngreso['cPacienteId'],
								'NIDPAD'=>$taIngreso['nPacienteId'],
								'NM1PAD'=>substr(trim($taIngreso['cPacienteNombre1']),0,40),
								'NM2PAD'=>substr(trim($taIngreso['cPacienteNombre2']),0,40),
								'AP1PAD'=>substr(trim($taIngreso['cPacienteApellido1']),0,40),
								'AP2PAD'=>substr(trim($taIngreso['cPacienteApellido2']),0,40),
								'PAIPAD'=>$taIngreso['nNacioPais'],
								'DEPPAD'=>$taIngreso['nNacioDepartamento'],
								'MUNPAD'=>$taIngreso['nNacioCiudad'],
								'FNAPAD'=>$taIngreso['nNacimiento'],
								'SEXPAD'=>$taIngreso['cPacienteGenero'],
								'OCUPAD'=>$taIngreso['nPacienteLaboralOcupacion'],
								'MAIPAD'=>substr(trim($taIngreso['cPacienteEmail']),0,60),
								'CCOPAD'=>$taIngreso['nConsulta'],
								'CCIPAD'=>$taIngreso['nCita'],
								'NHCPAD'=>$taIngreso['nHistoria'],
								'PARPAD'=>$taIngreso['nPacienteRecidePais'],
								'DERPAD'=>$taIngreso['nPacienteRecideDepartamento'],
								'MURPAD'=>$taIngreso['nPacienteRecideCiudad'],
								'LOCPAD'=>$taIngreso['nPacienteRecideLocalidad'],
								'DR1PAD'=>substr(trim($taIngreso['cPacienteRecideDireccion']),220),
								'BARPAD'=>substr(trim($taIngreso['cPacienteRecideBarrio']),0,30),
								'TP1PAD'=>intval($taIngreso['cResponsableTelefono1']),
								'TP2PAD'=>intval($taIngreso['cResponsableTelefono2']),
								'CP1PAD'=>intval($taIngreso['cResponsableTelefono3']),
								'CP2PAD'=>intval($taIngreso['cResponsableTelefono4']),
								'ZORPAD'=>substr(trim($taIngreso['nPacienteRecideZona']),0,220),
								'OP6PAD'=>substr(trim($lcPasaportePermiso),0,220),
								'OP8PAD'=>substr(trim($taIngreso['cPacienteTieneEmail']),0,1),
								'OP9PAD'=>$lcEtnicoEducativo,
								'USRPAD'=>$tcUsuario,
								'PGMPAD'=>$tcPrograma,
								'FECPAD'=>$tnFecha,
								'HORPAD'=>$tnHora
							];

				$goDb->from('PACDET ')->insertar($laCampos);

				// Tabla terceros PRMTE1 (PACIENTE)
				$llPacienteExiste = $this->pacienteExiste($taIngreso['cPacienteId'],$taIngreso['nPacienteId'], 'TERCERO');
				$lcTerceroDigitoVerificacion = trim(strval($this->getDigitoVerificacion($taIngreso['nPacienteId'])));
				$lcTerceroRazonSocial = AplicacionFunciones::fcLimpiarEspacios(trim($taIngreso['cPacienteApellido1']).' '.trim($taIngreso['cPacienteApellido2']).' '.trim($taIngreso['cPacienteNombre1']).' '.trim($taIngreso['cPacienteNombre2']));
				$lcTerceroRazonComercial = $lcTerceroRazonSocial;
				$lcTerceroCodigo = str_pad(trim(strval($taIngreso['nPacienteId'])), 13, '0', STR_PAD_LEFT);

				$laCampos = [
								'TE1DIG'=>$lcTerceroDigitoVerificacion,
								'TE1TIP'=>$taIngreso['cPacienteId'],
								'TE1COD'=>$lcTerceroCodigo,
								'TE1SOC'=>substr(trim($lcTerceroRazonSocial),0,40),
								'TE1COM'=>substr(trim($lcTerceroRazonComercial),0,40),
								'TE1PER'=>'N',
								'TE1RET'=>'N',
							];

				$laTabla = 'PRMTE1';

				if($llPacienteExiste==false){
					$laCampos['TE1USC'] = $tcUsuario;
					$laCampos['TE1FHC'] = $tnFecha;
					$laCampos['TE1HRC'] = intval($tnHora/100);

					$goDb->from($laTabla)->insertar($laCampos);

				}else{
					$laCampos['TE1USM'] = $tcUsuario;
					$laCampos['TE1FHM'] = $tnFecha;
					$laCampos['TE1HRM'] = intval($tnHora/100);

					$goDb->from($laTabla)
						 ->where('TE1COD', '=', $lcTerceroCodigo)
						 ->actualizar($laCampos);
				}


				// Bitácora de entrada de datos
				$laCampos = [
								'TIPREG'=>'CAPTURA',
								'IDTIP1'=>$taIngreso['cPacienteId'],
								'IDNUM1'=>$taIngreso['nPacienteId'],
								'IDTIP2'=>'',
								'IDNUM2'=>0,
								'NIGING'=>$taIngreso['nIngreso'],
								'TIPCAP'=>$taIngreso['cPacienteIdCapturaMetodo'],
								'ESTADO'=>'A',
								'INFORM'=>substr(trim($taIngreso['cCapturaManual']),0,250),
								'UCRBIT'=>$tcUsuario,
								'PCRBIT'=>$tcPrograma,
								'FCRBIT'=>$tnFecha,
								'HCRBIT'=>$tnHora
							];

				$laTabla = 'IDPACBIT';

				$goDb->from($laTabla)->insertar($laCampos);

				if(!empty($taIngreso['cCapturaDistinta'])){
					$laCampos = [
									'TIPREG'=>'CAMBIO-ASI',
									'IDTIP1'=>$taIngreso['cPacienteId'],
									'IDNUM1'=>$taIngreso['nPacienteId'],
									'IDTIP2'=>$taIngreso['cPacienteIdInicial'],
									'IDNUM2'=>$taIngreso['nPacienteIdInicial'],
									'NIGING'=>$taIngreso['nIngreso'],
									'TIPCAP'=>$taIngreso['cPacienteIdCapturaMetodo'],
									'ESTADO'=>'A',
									'INFORM'=>substr(trim($taIngreso['cCapturaDistinta']),0,250),
									'UCRBIT'=>$tcUsuario,
									'PCRBIT'=>$tcPrograma,
									'FCRBIT'=>$tnFecha,
									'HCRBIT'=>$tnHora
								];
					$goDb->from($laTabla)->insertar($laCampos);
				}

			}

		}

		return $llResultado;
	}

	private function guardarResponsable(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			// TABLA PRINCIPAL RIARES
			$llResponsableExiste = $this->responsableExiste($taIngreso['nIngreso'], $taIngreso['cResponsableId'], $taIngreso['nResponsableId'], 'PRINCIPAL');

			$laCampos = [
						 'NIGRES'=>$taIngreso['nIngreso'],
						 'TIPRES'=>$taIngreso['cPacienteId'],
						 'NIPRES'=>$taIngreso['nPacienteId'],
						 'TIDRES'=>$taIngreso['cResponsableId'],
						 'NIDRES'=>$taIngreso['nResponsableId'],
						 'NM1RES'=>substr(trim($taIngreso['cResponsableNombre1']),0,15),
						 'NM2RES'=>substr(trim($taIngreso['cResponsableNombre2']),0,15),
						 'AP1RES'=>substr(trim($taIngreso['cResponsableApellido1']),0,15),
						 'AP2RES'=>substr(trim($taIngreso['cResponsableApellido2']),0,15),
						 'SEXRES'=>$taIngreso['cResponsableGenero'],
						 'FAMRES'=>$taIngreso['cResponsableParentesco'],
						 'PARRES'=>$taIngreso['nResponsableRecidePais'],
						 'DERRES'=>$taIngreso['nResponsableRecideDepartamento'],
						 'MURRES'=>$taIngreso['nResponsableRecideCiudad'],
						 'DR1RES'=>substr(trim($taIngreso['cResponsableRecideDireccion']),0,30),
						 'DR2RES'=>substr(trim($taIngreso['cResponsableRecideCiudadDisplay']),0,30),
						 'TELRES'=>substr(trim($taIngreso['cResponsableTelefono1']),0,60),
						 'DO1RES'=>substr(trim($taIngreso['cResponsableLaboralDireccion']),0,30),
						 'TETRES'=>substr(trim($taIngreso['cResponsableLaboralTelefono']),0,30)
						];

			$laTabla = 'RIARES';

			if($llResponsableExiste==false){
				$laCampos['USRRES'] = $tcUsuario;
				$laCampos['PGMRES'] = $tcPrograma;
				$laCampos['FECRES'] = $tnFecha;
				$laCampos['HORRES'] = $tnHora;

				$llResultado = $goDb->from($laTabla)->insertar($laCampos);

			}else{
				$laCampos['UMORES'] = $tcUsuario;
				$laCampos['PMORES'] = $tcPrograma;
				$laCampos['FMORES'] = $tnFecha;
				$laCampos['HMORES'] = $tnHora;

				$llResultado = $goDb->from($laTabla)
									->where('NIGRES', '=', $taIngreso['nIngreso'])
									->actualizar($laCampos);
			}


			// TABLAS ALTERNAS
			if($llResultado == true){

				// Tabla pagare PAGARE
				$llResponsableExiste = $this->responsableExiste($taIngreso['nIngreso'], $taIngreso['cResponsableId'], $taIngreso['nResponsableId'], 'PAGARE');
				$laCampos = [
							   'NIGPAG'=>$taIngreso['nIngreso'],
							   'TIPPAG'=>$taIngreso['cPacienteId'],
							   'IDPPAG'=>$taIngreso['nPacienteId'],
							   'OCPPAG'=>$taIngreso['cPacienteLaboralTrabajo'],
							   'ETPPAG'=>substr(trim($taIngreso['cPacienteLaboralEmpresa']),0,60),
							   'CAPPAG'=>substr(trim($taIngreso['cPacienteLaboralCargo']),0,30),
							   'ANPPAG'=>substr(trim($taIngreso['cPacienteLaboralAntiguedad']),0,30),
							   'RFPPAG'=>substr(trim($taIngreso['cPacienteReferenciaNombre']),0,30),
							   'DFPPAG'=>substr(trim($taIngreso['cPacienteReferenciaDireccion']),0,60),
							   'TFPPAG'=>substr(trim($taIngreso['cPacienteReferenciaTelefono']),0,60),
							   'FACPAG'=>$tnFecha,
							   'CCRPAG'=>substr(trim($taIngreso['cResponsableLugarExpedicion']),0,30),
							   'BARPAG'=>substr(trim($taIngreso['cResponsableRecideBarrio']),0,30),
							   'OCRPAG'=>$taIngreso['cResponsableLaboralTrabajo'],
							   'EMRPAG'=>substr(trim($taIngreso['cResponsableLaboralEmpresa']),0,60),
							   'CARPAG'=>substr(trim($taIngreso['cResponsableLaboralCargo']),0,30),
							   'ANRPAG'=>substr(trim($taIngreso['cResponsableLaboralAntiguedad']),0,30),
							   'RFRPAG'=>substr(trim($taIngreso['cResponsableReferenciaNombre']),0,60),
							   'DFRPAG'=>substr(trim($taIngreso['cResponsableReferenciaDireccion']),0,60),
							   'TFRPAG'=>substr(trim($taIngreso['cResponsableReferenciaTelefono']),0,60),
							];

				$laTabla = 'PAGARE';

				if($llResponsableExiste==false){
					$laCampos['USRPAG'] = $tcUsuario;
					$laCampos['PGMPAG'] = $tcPrograma;
					$laCampos['FECPAG'] = $tnFecha;
					$laCampos['HORPAG'] = $tnHora;

					$goDb->from($laTabla)->insertar($laCampos);

				}else{
					$laCampos['UMOPAG'] = $tcUsuario;
					$laCampos['PMOPAG'] = $tcPrograma;
					$laCampos['FMOPAG'] = $tnFecha;
					$laCampos['HMOPAG'] = $tnHora;

					$goDb->from($laTabla)
						 ->where('NIGPAG', '=', $taIngreso['nIngreso'])
						 ->actualizar($laCampos);
				}


				// Tabla terceros PRMTE1 (RESPONSABLE)
				if(trim(strtoupper($taIngreso['cResponsablePaciente']))<>'PACIENTE'){
					$llPacienteExiste = $this->pacienteExiste($taIngreso['cResponsableId'],$taIngreso['nResponsableId'], 'TERCERO');
					$lcTerceroDigitoVerificacion = trim(strval($this->getDigitoVerificacion($taIngreso['nResponsableId'])));
					$lcTerceroRazonSocial = AplicacionFunciones::fcLimpiarEspacios(trim($taIngreso['cResponsableApellido1']).' '.trim($taIngreso['cResponsableApellido2']).' '.trim($taIngreso['cResponsableNombre1']).' '.trim($taIngreso['cResponsableNombre2']));
					$lcTerceroRazonComercial = $lcTerceroRazonSocial;
					$lcTerceroCodigo = str_pad(trim(strval($taIngreso['nResponsableId'])), 13, '0', STR_PAD_LEFT);

					$laCampos = [
									'TE1DIG'=>$lcTerceroDigitoVerificacion,
									'TE1TIP'=>$taIngreso['cPacienteId'],
									'TE1COD'=>$lcTerceroCodigo,
									'TE1SOC'=>substr(trim($lcTerceroRazonSocial),0,40),
									'TE1COM'=>substr(trim($lcTerceroRazonComercial),0,40),
									'TE1PER'=>'N',
									'TE1RET'=>'N',
								];

					$laTabla = 'PRMTE1';

					if($llPacienteExiste==false){
						$laCampos['TE1USC'] = $tcUsuario;
						$laCampos['TE1FHC'] = $tnFecha;
						$laCampos['TE1HRC'] = intval($tnHora/100);

						$goDb->from($laTabla)->insertar($laCampos);

					}else{
						$laCampos['TE1USM'] = $tcUsuario;
						$laCampos['TE1FHM'] = $tnFecha;
						$laCampos['TE1HRM'] = intval($tnHora/100);

						$goDb->from($laTabla)
							 ->where('TE1COD', '=', $lcTerceroCodigo)
							 ->actualizar($laCampos);
					}
				}
			}

		}

		return $llResultado;
	}


	private function guardarCitaProcedimiento(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);

		// En caso de Cita y se cambie ID
		if("CITA"==trim(strtoupper($taIngreso['cMetodo']))){
			if($taIngreso['nPacienteId']<>$taIngreso['nPacienteIdInicial'] || trim(strtoupper($taIngreso['cPacienteId']))<>trim(strtoupper($taIngreso['cPacienteIdInicial']))){
				if(!empty($taIngreso['cCodigoProcedimiento']) && !empty($taIngreso['nFechaOrdenadoProcedimiento'])){

					//GUARDA RIAORD
					$laCampos = [
								 'TIDORD'=>$taIngreso['cPacienteId'],
								 'NIDORD'=>$taIngreso['nPacienteId'],
								 'UMOORD'=>$tcUsuario,
								 'PMOORD'=>$tcPrograma,
								 'FMOORD'=>$tnFecha,
								 'HMOORD'=>$tnHora
								];

					$goDb->from('RIAORDL24')
						 ->where('TIDORD', '=', $taIngreso['cPacienteIdInicial'])
						 ->where('NIDORD', '=', $taIngreso['nPacienteIdInicial'])
						 ->where('COAORD', '=', $taIngreso['cCodigoProcedimiento'])
						 ->where('FRLORD', '=', $taIngreso['nFechaOrdenadoProcedimiento'])
						 ->actualizar($laCampos);

					// GUARDA RIACIT
					//Buscar consecutivo de cita para el ID nuevo
					$lnCitaCambioId = 0;
					$laRegistro = $goDb
						->max('CCICIT', 'CITA')
						->from('RIACIT')
						->where('TIDCIT', '=', $taIngreso['cPacienteIdInicial'])
						->where('NIDCIT', '=', $taIngreso['nPacienteIdInicial'])
						->get('array');

					if(is_array($laRegistro)==true){
						if(count($laRegistro)>0){
							$lnCitaCambioId = (is_null($laRegistro['CITA'])?0:$laRegistro['CITA']);
						}
					}


					// Buscar la Cita agendada
					$laCitasPaciente = $goDb->select('CCICIT CITA')
											->from('RIACIT')
											->where('TIDCIT', '=', $taIngreso['cPacienteIdInicial'])
											->where('NIDCIT', '=', $taIngreso['nPacienteIdInicial'])
											->where('NINCIT', '=', 0)
											->where('COACIT', '=', $taIngreso['cCodigoProcedimiento'])
											->where('FRLCIT', '=', $taIngreso['nFechaOrdenadoProcedimiento'])
											->getAll('array');

					if(is_array($laCitasPaciente)){
						foreach($laCitasPaciente as $laCitaPaciente){
							$lnCitaCambioId += 1;

							// Actualización de Cita
							$laCampos = [
											'TIDCIT' => $taIngreso['cPacienteId'],
											'NIDCIT' => $taIngreso['nPacienteId'],
											'CCICIT' => $nCitaCambioId,
											'UMOCIT' => $tcUsuario,
											'PMOCIT' => $tcPrograma,
											'FMOCIT' => $tnFecha,
											'HMOCIT' => $tnHora
										];

							// Actualización de cita en maestro y en jtm.shaio.org
							foreach(['RIACIT', 'JTMCIT'] as $lcTabla){
								$goDb->from($lcTabla)
									 ->where('TIDCIT', '=', $taIngreso['cPacienteIdInicial'])
									 ->where('NIDCIT', '=', $taIngreso['nPacienteIdInicial'])
									 ->where('CCICIT', '=', $laCitaPaciente['CITA'])
									 ->where('COACIT', '=', $taIngreso['cCodigoProcedimiento'])
									 ->where('FRLCIT', '=', $taIngreso['nFechaOrdenadoProcedimiento'])
									 ->actualizar($laCampos);
							}
						}
					}
				}
			}
		}
	}

	private function guardarAdicionCups(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			$laViasOrden = ['01'];

			if(in_array($taIngreso['cIngresoVia'],['01', '05', '06'])==true){
				$lnConsulta = 1;
				$lcEstado = 8;
				$lnCita = 0;
				$lcProcedimiento = ($taIngreso['cIngresoVia']=='01' ? '890702' : (in_array($taIngreso['cIngresoVia'], ['05', '06']) ? '890602' : ''));
				$llPermiteOrden = true;

				if($lnConsulta > 0) { //Controlando conecutivo de cita
					if(!empty($lcProcedimiento)){ // Controlando asignacion de procedimiento

						$laOrden = $goDb
										->count('NINORD', 'REGISTROS')
										->from('RIAORD')
										->where('NINORD', '=', $taIngreso['nIngreso'])
										->in('COAORD', ['890701', '890702', '890602'])
										->get('array');
						$llOrdenExiste = (is_array($laOrden) ? (intval($laOrden['REGISTROS'])>0) : false);

						if($llOrdenExiste==false){

							// Actualiza consecutivo cita - riapac(ccipac)
							$laTabla = 'RIAPACL02';
							$laCita = $goDb
											->max('CCIPAC', 'CONSECUTIVO')
											->from($laTabla)
											->where('TIDPAC', '=', $taIngreso['cPacienteId'])
											->where('NIDPAC', '=', $taIngreso['nPacienteId'])
											->get('array');
							$lnCita = intval(is_array($laCita)==true ? (count($laCita)>0 ? (is_null($laCita['CONSECUTIVO']) ? 0 : $laCita['CONSECUTIVO']) : 0) : 0)+1;

							$laCampos = ['CCIPAC'=>$lnCita];
							$goDb->from($laTabla)
								->where('TIDPAC', '=', $taIngreso['cPacienteId'])
								->where('NIDPAC', '=', $taIngreso['nPacienteId'])
								->actualizar($laCampos);


							// Consulta la via para indicar si se crea el registro
							$llPermiteOrden = (in_array($taIngreso['cIngresoVia'], $laViasOrden));

							if($llPermiteOrden==true){
								if($lnCita>0){

									$taIngreso['nConsulta']=$lnConsulta;
									$taIngreso['nCita']=$lnCita;

									$laCampos = [
													'TIDORD'=>$taIngreso['cPacienteId'],
													'NIDORD'=>$taIngreso['nPacienteId'],
													'EVOORD'=>0,
													'NINORD'=>$taIngreso['nIngreso'],
													'CCOORD'=>$lnConsulta,
													'CCIORD'=>$lnCita,
													'CCEORD'=>0,
													'TPRORD'=>0,
													'CD2ORD'=>0,
													'COAORD'=>$lcProcedimiento,
													'SECORD'=>0,
													'FCOORD'=>$tnFecha,
													'FRLORD'=>$tnFecha,
													'HOCORD'=>$tnHora,
													'FERORD'=>$tnFecha,
													'HRLORD'=>$tnHora,
													'ESTORD'=>$lcEstado,
													'INTORD'=>0,
													'ENTORD'=>$taIngreso['nPlanUsarEntidad'],
													'COUORD'=>0,
													'PATORD'=>0,
													'VIAORD'=>$taIngreso['cIngresoVia'],
													'RATORD'=>0,
													'CATORD'=>0,
													'PLAORD'=>$taIngreso['cPlanUsar'],
													'HOAORD'=>$tnHora,
													'USRORD'=>$tcUsuario,
													'PGMORD'=>$tcPrograma,
													'FECORD'=>$tnFecha,
													'HORORD'=>$tnHora
												];
									$llResultado = $goDb->from('RIAORDL24')->insertar($laCampos);


									if($taIngreso['cIngresoVia']=='01'){
										$lcCup = '890701';
										$lnLinea = 1;
										$lnEstado = 13;
										$lcDiagnostico = trim($taIngreso['cDiagnostico']);

										// Se crea cups archivo autorizaciones
										$laCampos = [
														'INGAAX'=>$taIngreso['nIngreso'],
														'CITAAX'=>$lnCita,
														'PROAAX'=>$lcCup,
														'CNLAAX'=>$lnLinea,
														'FORAAX'=>$tnFecha,
														'ESTAAX'=>$lnEstado,
														'PLAAAX'=>$taIngreso['cPlanUsar'],
														'NITAAX'=>$taIngreso['nPlanUsarEntidad'],
														'DIAGAX'=>trim(substr($lcDiagnostico,0,15)),
														'USRAAX'=>$tcUsuario,
														'PGMAAX'=>$tcPrograma,
														'FECAAX'=>$tnFecha,
														'HORAAX'=>$tnHora
													];
										$goDb->from('AUTANXL01')->insertar($laCampos);


										// Se crea cups archivo autorizaciones - detalle
										$laCampos = [
														'INGAAD'=>$taIngreso['nIngreso'],
														'INDAAD'=>1,
														'CITAAD'=>$lnCita,
														'PROAAD'=>$lcCup,
														'FGRAAD'=>$tnFecha,
														'HGRAAD'=>$tnHora,
														'CNLAAD'=>$lnLinea,
														'ESTAAD'=>$lnEstado,
														'PLAAAD'=>$taIngreso['cPlanUsar'],
														'NITAAD'=>$taIngreso['nPlanUsarEntidad'],
														'DIAAAD'=>trim(substr($lcDiagnostico,0,15)),
														'USRAAD'=>$tcUsuario,
														'PGMAAD'=>$tcPrograma,
														'FECAAD'=>$tnFecha,
														'HORAAD'=>$tnHora
													];
										$goDb->from('AUTANDL01')->insertar($laCampos);

									}
								}
							}
						}
					}
				}
			}
		}

		return $llResultado;
	}

	private function guardarCobroInsumos(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			// Cobros automatios para urgencias
			if($taIngreso['cIngresoVia']=="01"){
				// MANILLA DE INGRESO
				$lcCup ='890701';
				$lnCantidad=0;
				$lnValorPre=0;
				$lnConsecutivoConsumo=0;
				$lnCostoPromedio=0;
				$lcConceptoVenta="";
				$lcCobrable='N';

				// Consecutivo consumo
				$laRegistro = $goDb
					->max('CNSEST', 'CONSECUTIVO')
					->from('RIAESTM')
					->where('INGEST', '=', $taIngreso['nIngreso'])
					->get('array');
				$lnConsecutivoConsumo = intval(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['CONSECUTIVO']) ? 0 : $laRegistro['CONSECUTIVO']) : 0) : 0)+1;

				// Datos del cupins parametros del elemento a cobrar
				$laCampos = ['C.OP5INS CENTROSERVICIO', 'C.ELEINS ELEMENTO', 'C.CANINS CANTIDAD', 'C.BODINS BODEGA', 'C.OP2INS CENTROCOSTOS'];
				$laCups = $goDb
					->select($laCampos)
					->from('CUPINS C')
					->where('C.CCPINS', '=', $lcCup)
					->getAll('array');

				if(is_array($laCups)==true){
					foreach($laCups as $laCup){
						$laCup['CENTROSERVICIO'] = trim(substr(trim($laCup['CENTROSERVICIO']),0,6));
						$laCup['ELEMENTO'] = trim($laCup['ELEMENTO']);
						$laCup['BODEGA'] = trim($laCup['BODEGA']);
						$laCup['CENTROCOSTOS'] = trim($laCup['CENTROCOSTOS']);

						// Datos del inventario
						$laCampos = ['I.CPRCON', 'I.TINDES'];
						$laElemento = $goDb
							->select($laCampos)
							->from('INVDES I')
							->where('I.REFDES', '=', $laCup['ELEMENTO'])
							->get('array');

						$lnCostoPromedio = (is_array($laElemento) ? (count($laElemento)>0 ? (is_null($laElemento['CPRCON']) ? 0 : $laElemento['CPRCON']) : 0 ) : 0);
						$lcConceptoVenta = (is_array($laElemento) ? (count($laElemento)>0 ? (is_null($laElemento['TINDES']) ? 0 : $laElemento['TINDES']) : 0 ) : 0);


						// Busca si es cobrable para este  plan
						$laCampos = ['F.TARCBP', 'F.TPCCBP', 'F.AXCCBP', 'F.CCRCBP'];
						$laCobrable = $goDb
							->count('TARCBP', 'TARIFAS')
							->from('FACCBRP')
							->where('TARCBP', '=', $taIngreso['cPlanUsar'])
							->where('TPCCBP', '=', $lcConceptoVenta)
							->where('AXCCBP', '=', $laCup['ELEMENTO'])
							->where('CCRCBP', '=', $laCup['CENTROSERVICIO'])
							->get('array');

						$llEsCobrablePlan = (is_array($laCobrable) ? !(intval($laCobrable['TARIFAS'])>0) : true);

						if($llEsCobrablePlan==true){
							// Datos tarifario vigente para el plan*** para tomar el tarifario con el que liquida
							$laTarifario = $goDb
								->select('F.TARTRP TARIFA')
								->from('FACTRPL F')
								->where('F.PLNTRP', '=',  $taIngreso['cPlanUsar'])
								->where('F.TIPTRP', '=',  $lcConceptoVenta)
								->where('F.FHDTRP', '<=', $tnFecha)
								->where('F.FHHTRP', '>=', $tnFecha)
								->get('array');

							if(is_array($laTarifario)){
								if(count($laTarifario)>0){
									// Busca la tarifa en el tarifario base encontrado
									$lcTarifario = (is_null($laTarifario['TARIFA']) ? '' : $laTarifario['TARIFA']);

									$laCampos = ['F.CANTAR'];
									$laTarifa = $goDb
										->select($laCampos)
										->from('FACTAR F')
										->where('F.TARTAR', '=', $lcTarifario)
										->where('F.AUXTAR', '=', $laCup['ELEMENTO'])
										->where('F.STSTAR', '=', '0')
										->get('array');

									$lnCantidad = (is_array($laTarifa) ? (count($laTarifa)>0 ? (is_null($laTarifa['CANTAR']) ? 0 : $laTarifa['CANTAR']) : 0 ) : 0);
								}
							}

							$lnValorPre = $lnCantidad * $laCup['CANTIDAD'];
							$lcCobrable = 'S';
						}else{
							$lnCantidad = 0;
							$lnValorPre = $lnCantidad * $laCup['CANTIDAD'];
							$lcCobrable = 'N';
						}


						// Verifica si ya fue cobrado
						$laCobrado = $goDb
							->count('INGEST', 'COBROS')
							->from('RIAESTM')
							->where('INGEST', '=', $taIngreso['nIngreso'])
							->where('ELEEST', '=', $laCup['ELEMENTO'])
							->get('array');
						$llFueCobrado = (is_array($laCobrado) ? (intval($laCobrado['COBROS'])>0) : false);

						if($llFueCobrado==false){
							// Graba el registro en el consumo
							$laCampos = [
											'INGEST'=>$taIngreso['nIngreso'],
											'CNSEST'=>$lnConsecutivoConsumo,
											'FINEST'=>$tnFecha,
											'HINEST'=>$tnHora,
											'CSEEST'=>$laCup['CENTROSERVICIO'],
											'NPREST'=>'0',
											'TINEST'=>$lcConceptoVenta,
											'ELEEST'=>$laCup['ELEMENTO'],
											'CADEST'=>$laCup['CANTIDAD'],
											'QCOEST'=>$laCup['CANTIDAD'],
											'VUNEST'=>$lnCantidad,
											'CUNEST'=>$lnCostoPromedio,
											'NENEST'=>$taIngreso['nPlanUsarEntidad'],
											'PLAEST'=>$taIngreso['cPlanUsar'],
											'VNGEST'=>$taIngreso['cIngresoVia'],
											'BODEST'=>$laCup['BODEGA'],
											'CCTEST'=>$laCup['CENTROCOSTOS'],
											'VPREST'=>$lnValorPre,
											'VLIEST'=>$lnValorPre,
											'CCBEST'=>$lcCobrable,
											'USREST'=>$tcUsuario,
											'PGMEST'=>$tcPrograma,
											'FECEST'=>$tnFecha,
											'HOREST'=>$tnHora,
										];
							$llResultado = $goDb->from('RIAESTM')->insertar($laCampos);
						}
					}
				}
			}
		}

		return $llResultado;
	}

	private function guardarTriage(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){

			if( $taIngreso['cIngresoVia']=='01'){

				$lcRiesgo = '1';
				$lnEstado = 31;
				$lcPrioridad = '';
				$lcPlanParticular = 'SHAIO1';
				$lcPrioridad = (in_array(trim($taIngreso['cTriage']), ['1','2'])==true ? 'T1' : $lcPrioridad);
				$lcPrioridad = (in_array(trim($taIngreso['cTriage']), ['3','4'])==true ? 'T3' : $lcPrioridad);
				$lcPrioridad = (trim($taIngreso['cPlanUsar'])==$lcPlanParticular || trim($taIngreso['cPlanUsarEntidadTipo'])=='15' ? 'T2' : $lcPrioridad);
				$lnTurno = 0;

				// ACTUALIZA IDENTIFICACION EN TRIAGE
				//(Ej. Paciente sin documento que se registra manual en triage y en ingreso se corrige)

				if(!empty($taIngreso['cPacienteIdInicial']) || !empty($taIngreso['nPacienteIdInicial'])){

					// Truiage
					$lcWhere = "NIGTRI=".$taIngreso['nIngreso'];
					$lcWhere.= (!empty($taIngreso['cPacienteIdInicial']) ? ((empty($lcWhere)?"":" AND ") . sprintf("TIDTRI='%s'", $taIngreso['cPacienteIdInicial'])) : '');
					$lcWhere.= (!empty($taIngreso['nPacienteIdInicial']) ? ((empty($lcWhere)?"":" AND ") . sprintf("NIDTRI=%s", $taIngreso['nPacienteIdInicial'])) : '');

					$laCampos = [
									'TIDTRI'=>$taIngreso['cPacienteId'],
									'NIDTRI'=>$taIngreso['nPacienteId'],
									'UMOTRI'=>$tcUsuario,
									'PMOTRI'=>$tcPrograma,
									'FMOTRI'=>$tnFecha,
									'HMOTRI'=>$tnHora
								];
					$goDb->from('TRIAGU')->where($lcWhere)->actualizar($laCampos);

					// HC Triage
					$lcWhere = "INGHTR=".$taIngreso['nIngreso'];
					$lcWhere.= (!empty($taIngreso['cPacienteIdInicial']) ? ((empty($lcWhere)?"":" AND ") . sprintf("TIDHTR='%s'", $taIngreso['cPacienteIdInicial'])) : '');
					$lcWhere.= (!empty($taIngreso['nPacienteIdInicial']) ? ((empty($lcWhere)?"":" AND ") . sprintf("NIDHTR=%s", $taIngreso['nPacienteIdInicial'])) : '');

					$laCampos = [
									'TIDHTR'=>$taIngreso['cPacienteId'],
									'NIDHTR'=>$taIngreso['nPacienteId'],
									'UMOHTR'=>$tcUsuario,
									'PMOHTR'=>$tcPrograma,
									'FMOHTR'=>$tnFecha,
									'HMOHTR'=>$tnHora
								];
					$goDb->from('HISTRI')->where($lcWhere)->actualizar($laCampos);

				}

				// ACTUALIZANDO INFORMACIÓN TRIAGE
				$lcTabla = 'TRIAGUL01';

				$laTriage = $goDb
						->count('NIGTRI', 'REGISTROS')
						->from($lcTabla)
						->where('TIDTRI', '=', $taIngreso['cPacienteId'])
						->where('NIDTRI', '=', $taIngreso['nPacienteId'])
						->where('NIGTRI', '=', $taIngreso['nIngreso'])
						->get('array');
				$llTriageExiste = (is_array($laTriage) ? (intval($laTriage['REGISTROS'])>0) : false);

				$laCampos = [
								'NIGTRI'=>$taIngreso['nIngreso'],
								'TIDTRI'=>$taIngreso['cPacienteId'],
								'NIDTRI'=>$taIngreso['nPacienteId'],
								'CNTTRI'=>$lnTurno,
								'FETTRI'=>$tnFecha,
								'HRTTRI'=>$tnHora,
								'PRCTRI'=>$taIngreso['cTriage'],
								'CLMTRI'=>$taIngreso['cTriage'],
								'CLRTRI'=>$lcRiesgo,
								'CLATRI'=>$lcPrioridad,
								'FACTRI'=>$tnFecha,
								'HACTRI'=>$tnHora,
								'ESTTRI'=>$lnEstado,
								'OP6TRI'=>''
							];

				if($llTriageExiste == false){
					$laCampos['CNSTRI'] = $taIngreso['nTriageId'];
					$laCampos['USRTRI'] = $tcUsuario;
					$laCampos['PGMTRI'] = $tcPrograma;
					$laCampos['FECTRI'] = $tnFecha;
					$laCampos['HORTRI'] = $tnHora;

					$llResultado = $goDb->from($lcTabla)->insertar($laCampos);

				}else{
					$laCampos['UMOTRI'] = $tcUsuario;
					$laCampos['PMOTRI'] = $tcPrograma;
					$laCampos['FMOTRI'] = $tnFecha;
					$laCampos['HMOTRI'] = $tnHora;

					$llResultado = $goDb->from($lcTabla)
										->where('TIDTRI', '=', $taIngreso['cPacienteId'])
										->where('NIDTRI', '=', $taIngreso['nPacienteId'])
										->where('NIGTRI', '=', $taIngreso['nIngreso'])
										->actualizar($laCampos);
				}


				// ENFERMEDAD ACTUAL
				if(!empty($taIngreso['cEnfermedad']) && $llResultado==true && $taIngreso['nTriageId']>0){
					$laHistoria = $goDb
							->count('INGHTR', 'REGISTROS')
							->from('HISTRIL1')
							->where('TIDHTR', '=', $taIngreso['cPacienteId'])
							->where('NIDHTR', '=', $taIngreso['nPacienteId'])
							->where('INGHTR', '=', $taIngreso['nIngreso'])
							->where('INDHTR', '=', 10)
							->get('array');
					$llHistoriaExiste = (is_array($laHistoria) ? (intval($laHistoria['REGISTROS'])>0) : false);

					if($llHistoriaExiste == false){
						$lnIndice = 10;
						$lnLinea = 0;
						$lnBloques = ceil(strlen(trim($taIngreso['cEnfermedad'])) / 220);
						$lnPosicion = 0;

						for($lnBloque=1; $lnBloque<=$lnBloques; $lnBloque++){
							$lnLinea += 1;
							$lcDescripcion = substr(trim($taIngreso['cEnfermedad']), $lnPosicion, 220);
							$laCampos = [
											'TIDHTR'=>$taIngreso['cPacienteId'],
											'NIDHTR'=>$taIngreso['nPacienteId'],
											'CTRHTR'=>$taIngreso['nTriageId'],
											'INGHTR'=>$taIngreso['nIngreso'],
											'INDHTR'=>$lnIndice,
											'CLNHTR'=>$lnLinea,
											'DESHTR'=>$lcDescripcion,
											'USRHTR'=>$tcUsuario,
											'PGMHTR'=>$tcPrograma,
											'FECHTR'=>$tnFecha,
											'HORHTR'=>$tnHora
										];
							$goDb->from('HISTRIL1')->insertar($laCampos);
							$lnPosicion += 220;
						}
					}
				}
			}
		}

		return $llResultado;
	}

	private function guardarOrdenHospitalizacion(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;


		global $goDb;
		if(isset($goDb)){
			$lcDescripcion = trim($taIngreso['cSeccion']) . ' - ' . trim($taIngreso['cHabitacion']);
			$lcListaSecciones = trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae('DE2TMA', 'EVOLUC', "CL1TMA='SECTRAS' AND ESTTMA=''"),'DE2TMA','')));
			$laListaTransitorioUrg = explode(',',trim(strval(AplicacionFunciones::getValue($goDb->obtenerTabMae("OP5TMA", "EVOLUC", "CL1TMA='ESTORDH' AND CL2TMA='2' AND ESTTMA=''"),'OP5TMA',''))));
			$laListaTransitorioUrg = array_map("trim", $laListaTransitorioUrg);

			$laMedicoTratante = $this->obtenerMedicoTratante($taIngreso['nIngreso']);
			$lcMedicoTratante = $laMedicoTratante['REGISTRO'];
			$lcMedicoTratanteEsp = $laMedicoTratante['ESPECIALIDAD'];

			if(!empty($lcListaSecciones)){
				// Consulta orden hospitalización
				$laCampos = ['O.ESTOHO ESTADO'];
				$laOrden = $goDb
					->select($laCampos)
					->from('ORDHOSL01 O')
					->where('O.INGOHO', '=', $taIngreso['nIngreso'])
					->get('array');

				$lcEstadoOrden = trim(strval(is_array($laOrden) ? (count($laOrden)>0 ? (is_null($laOrden['ESTADO']) ? 0 : $laElemento['ESTADO']) : 0 ) : 0));
				$lcEstadoOrden = (!in_array($lcEstadoOrden, $laListaTransitorioUrg) ? $lcEstadoOrden : '18');

				// Actualiza orden
				$laCampos = [
								'ESTOHO'=>$lcEstadoOrden,
								'ESPOHO'=>$lcMedicoTratanteEsp,
								'REGOHO'=>$lcMedicoTratante,
								'UMOOHO'=>$tcUsuario,
								'PMOOHO'=>$tcPrograma,
								'FMOOHO'=>$tnFecha,
								'HMOOHO'=>$tnHora
							];

				$llResultado = $goDb->from('ORDHOSL01')->where('INGOHO', '=', $taIngreso['nIngreso'])->actualizar($laCampos);

				// Registra detalle orden hospitalización
				if($llResultado==true){
					$laRegistro = $goDb
						->max('CONOHD', 'CONSECMAXIMO')
						->from('ORDHODL02')
						->where('INGOHD', '=', $taIngreso['nIngreso'])
						->get('array');

					$lnConsecDetalle = intval(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['CONSECMAXIMO']) ? 0 : $laRegistro['CONSECMAXIMO']) : 0) : 0)+1;
					$lnConsLinea = 1;

					$laCampos = [
									'INGOHD'=>$taIngreso['nIngreso'],
									'CONOHD'=>$lnConsecDetalle,
									'CLIOHD'=>$lnConsLinea,
									'ESTOHD'=>$lcEstadoOrden,
									'ESPOHD'=>$lcMedicoTratanteEsp,
									'REGOHD'=>$lcMedicoTratante,
									'DESOHD'=>$lcDescripcion,
									'UCROHD'=>$tcUsuario,
									'PCROHD'=>$tcPrograma,
									'FCROHD'=>$tnFecha,
									'HCROHD'=>$tnHora
								];

					$goDb->from('ORDHODL01')->insertar($laCampos);
				}
			}
		}

		return $llResultado;
	}


	public function triageNuevo()
	{
		$llAsignado = false;
		$lnConsecutivo = 0;
		$lcPrograma = $this->cPrograma;

		global $goDb;
		if(isset($goDb)){
			while($llAsignado==false){
				$lnConsecutivo = $goDb->obtenerConsecRiacon(95, $lcPrograma);
				$llAsignado = ($lnConsecutivo>0);
			}
		}

		return $lnConsecutivo;
	}

	private function guardarHabitacion(&$taIngreso=array(), $tcUsuario ='', $tcPrograma = '', $tnFecha = 0, $tnHora = 0)
	{
		$tcUsuario = trim(strval($tcUsuario));
		$tcPrograma = trim(strval($tcPrograma));
		$tnFecha = intval($tnFecha);
		$tnHora = intval($tnHora);
		$llResultado = false;


		global $goDb;
		if(isset($goDb)){

			if(in_array(trim($taIngreso['cIngresoVia']), ['05', '06'])==true){
				$lcHabitacionCUP = '890602';
				$lcHabitacionTipoAsignacion = 'A'; //M-MANUAL A=AUTOMAT L=LIQUIDADO
				$lcHabitacionEstado = '1';

				// TU no se admite estado 1 ocupado
				if(in_array(trim(strtoupper($taIngreso['cSeccion'])), ['TU','TT'])==true){
					$lcHabitacionEstado = '1';
				} else {

					// Estado
					$laRegistro = $goDb
						->select('LIQHAB ESTADO')
						->from('FACHAB')
						->where('IDDHAB', '=', '0')
						->where('INGHAB', '=', $taIngreso['nIngreso'])
						->in('SECHAB', ['TU', 'TT'])
						->get('array');
					$lcHabitacionEstado = (is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['ESTADO']) ? $lcHabitacionEstado : trim(strval($laRegistro['ESTADO']))) : $lcHabitacionEstado) : $lcHabitacionEstado);

					// Habitaciones
					$laCampos = [
						'SECHAB',
						'NUMHAB',
						'CUPHAB'
					];
					$laHabitaciones = $goDb
						->select($laCampos)
						->from('FACHAB')
						->where('IDDHAB', '=', '0')
						->where('INGHAB', '=', $taIngreso['nIngreso'])
						->notIn('SECHAB', ['TU', 'TT'])
						->getAll('array');
					$lnHabitaciones = (is_array($laHabitaciones) ? count($laHabitaciones) : 0);

					if($lnHabitaciones>0){
						foreach($laHabitaciones as $laHabitacion){
							$lcHabitacionEstado = '6';

							// Actualiza habitación actual si la tiene
							$laCampos = [
								'INGHAB'=>$taIngreso['nIngreso'],
								'TIDHAB'=>$taIngreso['cPacienteId'],
								'NIDHAB'=>$taIngreso['nPacienteId'],
								'ESTHAB'=>'1',
								'USMHAB'=>$tcUsuario,
								'DTMHAB'=>$tnFecha,
								'HRMHAB'=>$tnHora
							];
							$llResultado = $goDb
								->from('FACHAB')
								->where('SECHAB', '=', $laHabitacion['SECHAB'])
								->where('NUMHAB', '=', $laHabitacion['NUMHAB'])
								->actualizar($laCampos);

							// Registro de habitación en log
							$laCampos = [
								'FMVLOG'=>$tnFecha,
								'HMVLOG'=>$tnHora,
								'SECLOG'=>$laHabitacion['SECHAB'],
								'NUMLOG'=>$laHabitacion['NUMHAB'],
								'ESTLOG'=>$lcHabitacionEstado,
								'INGLOG'=>$taIngreso['nIngreso'],
								'TIDLOG'=>$taIngreso['cPacienteId'],
								'NIDLOG'=>$taIngreso['nPacienteId'],
								'USRLOG'=>$tcUsuario,
								'PGMLOG'=>$tcPrograma,
								'FECLOG'=>$tnFecha,
								'HORLOG'=>$tnHora
							];
							$goDb->from('HABLOG')->insertar($laCampos);

						}
					} else {
						if($taIngreso['cIngresoVia'] == '01'){
							$lcHabitacionEstado = '6';
							$laCampos = [
								'INGIUR'=>$taIngreso['nIngreso'],
								'ESTIUR'=>'1',
								'USRIUR'=>$tcUsuario,
								'PGMIUR'=>$tcPrograma,
								'DTEIUR'=>$tnFecha,
								'HORIUR'=>$tnHora
							];
							$goDb->from('INGURGT')->insertar($laCampos);
						}
					}
				}


				// Actualiza habitación
				$laCampos = [
					'INGHAB'=>$taIngreso['nIngreso'],
					'TIDHAB'=>$taIngreso['cPacienteId'],
					'NIDHAB'=>$taIngreso['nPacienteId'],
					'ESTHAB'=>$lcHabitacionEstado,
					'USMHAB'=>$tcUsuario,
					'DTMHAB'=>$tnFecha,
					'HRMHAB'=>$tnHora
				];
				$llResultado = $goDb
					->from('FACHAB')
					->where('SECHAB', '=', $taIngreso['cSeccion'])
					->where('NUMHAB', '=', $taIngreso['cHabitacion'])
					->actualizar($laCampos);

				// Log habitaciones
				$laCampos = [
					'FMVLOG'=>$tnFecha,
					'HMVLOG'=>$tnHora,
					'SECLOG'=>$taIngreso['cSeccion'],
					'NUMLOG'=>$taIngreso['cHabitacion'],
					'ESTLOG'=>$lcHabitacionEstado,
					'INGLOG'=>$taIngreso['nIngreso'],
					'TIDLOG'=>$taIngreso['cPacienteId'],
					'NIDLOG'=>$taIngreso['nPacienteId'],
					'USRLOG'=>$tcUsuario,
					'PGMLOG'=>$tcPrograma,
					'FECLOG'=>$tnFecha,
					'HORLOG'=>$tnHora
				];
				$goDb->from('HABLOG')->insertar($laCampos);



				// Busca información del medico
				$laCampos = [
					'NIDRGM MEDICO',
					'CODRGM ESPECIALIDAD'
				];
				$laRegistro = $goDb
					->select($laCampos)
					->from('RIARGMN5')
					->where('REGMED', '=', $taIngreso['cMedicoTratanteId'])
					->get('array');
				$lnMedico = intval(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['MEDICO']) ? 0 : $laRegistro['MEDICO']) : 0) : 0);
				$lcMedicoEspecialidad = trim(strval(is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['ESPECIALIDAD']) ? '' : $laRegistro['ESPECIALIDAD']) : '') : ''));

				// Guarda datos medico/diagnostico
				$lcTabla = 'RIAINGT';
				$laIngresoTratante = $goDb
					->count('NIGINT', 'REGISTROS')
					->from($lcTabla)
					->where('NIGINT', '=', $taIngreso['nIngreso'])
					->get('array');
				$llIngresoTratanteExiste = (is_array($laIngresoTratante) ? (intval($laIngresoTratante['REGISTROS'])>0) : false);

				if($llIngresoTratanteExiste==true){
					$laCampos = [
						'DPTINT'=>$lcMedicoEspecialidad,
						'MEDINT'=>$lnMedico,
						'UMOINT'=>$tcUsuario,
						'PMOINT'=>$tcPrograma,
						'FMOINT'=>$tnFecha,
						'HMOINT'=>$tnHora
					];
					$llResultado = $goDb
						->from($lcTabla)
						->where('NIGINT', '=', $taIngreso['nIngreso'])
						->actualizar($laCampos);
				} else {
					$laCampos = [
						'NIDINT'=>$taIngreso['nPacienteId'],
						'NIGINT'=>$taIngreso['nIngreso'],
						'DPTINT'=>$lcMedicoEspecialidad,
						'MEDINT'=>$lnMedico,
						'USRINT'=>$tcUsuario,
						'PGMINT'=>$tcPrograma,
						'FECINT'=>$tnFecha,
						'HORINT'=>$tnHora
					];
					$goDb->from($lcTabla)->insertar($laCampos);
				}


				// Detalle medico tratante
				$laCampos = [
					'INGDME'=>$taIngreso['nIngreso'],
					'SECDME'=>$taIngreso['cSeccion'],
					'CAMDME'=>$taIngreso['cHabitacion'],
					'MEDDME'=>$lnMedico,
					'FTRDME'=>$tnFecha,
					'HTRDME'=>$tnHora,
					'CNSDME'=>1,
					'CLIDME'=>1,
					'USRDME'=>$tcUsuario,
					'PGMDME'=>$tcPrograma,
					'FECDME'=>$tnFecha,
					'HORDME'=>$tnHora,
				];
				$goDb->from('RIAINGTD')->insertar($laCampos);


				// Detalle habitaciones
				$laRegistro = $goDb
					->select('CUPHAB')
					->from('FACHAB')
					->where('IDDHAB', '=', '0')
					->where('SECHAB', '=', $taIngreso['cSeccion'])
					->where('NUMHAB', '=', $taIngreso['cHabitacion'])
					->get('array');
				$lcHabitacionCUP = (is_array($laRegistro)==true ? (count($laRegistro)>0 ? (is_null($laRegistro['CUPHAB']) ? $lcHabitacionCUP : $laRegistro['CUPHAB']) : $lcHabitacionCUP) : $lcHabitacionCUP);

				// Detalle habitaciones
				$lcHabitacionObs = ($lcHabitacionEstado=='2' ? 'Se hospitaliza en Urgencia, queda en por trasladar en TU' : ($lcHabitacionEstado=='6' ? 'Se reserva Habitación en piso/unidad' : ''));

				$laCampos = [
					'INGEPC'=>$taIngreso['nIngreso'],
					'CONEPC'=>0,
					'TIDEPC'=>$taIngreso['cPacienteId'],
					'NIDEPC'=>$taIngreso['nPacienteId'],
					'STREPC'=>$taIngreso['cSeccion'],
					'NTREPC'=>$taIngreso['cHabitacion'],
					'FEIEPC'=>$tnFecha,
					'HOIEPC'=>$tnHora,
					'DTREPC'=>0,
					'OBSEPC'=>$lcHabitacionObs,
					'CUPEPC'=>$lcHabitacionCUP,
					'FTEEPC'=>$lcHabitacionTipoAsignacion,
					'USREPC'=>$tcUsuario,
					'PGMEPC'=>$tcPrograma,
					'FECEPC'=>$tnFecha,
					'HOREPC'=> $tnHora
				];
				$goDb->from('RIAEPC')->insertar($laCampos);
			}
		}

		return $llResultado;
	}

	public function consultaEstadoIngreso($tnIngreso=0)
	{
		$lcEstado='';
		global $goDb;
		if(isset($goDb)){
			$laCampos = ['ESTING ESTADO'];

			$laRegistro = $goDb
			->select('ESTING ESTADO')
			->from('RIAING')
			->where('NIGING', '=', $tnIngreso)
			->get('array');
			if ($goDb->numRows()>0){
				$lcEstado=$laRegistro['ESTADO'];
			}
		}
		unset($laRegistro);
		return $lcEstado;
	}

	public function guadar($taIngreso=array(), $taPlanes=array(), $tcUsuario='')
	{
		$laResultado = array('GUARDADO'=>false, 'MENSAJE'=>'Sin guardar', 'INGRESO'=>$taIngreso, 'PLANES'=>$taPlanes, 'NUMERO_INGRESO'=>0);
		$lcMensaje = '';

		if(is_array($taIngreso)==true && is_array($taPlanes)==true){
			if(count($taIngreso)>0){

				global $goDb;
				if(isset($goDb)){
					$lnTipoEspecial = 0;
					$tcUsuario=trim(strval($tcUsuario));

					$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
					$lnFecha = intval($ltAhora->format('Ymd'));
					$lnHora  = intval($ltAhora->format('His'));
					$lcLog = '';

					$taIngreso['nCita'] = intval(isset($taIngreso['nCita'])?$taIngreso['nCita']:'');
					$taIngreso['nConsulta'] = intval(isset($taIngreso['nConsulta'])?$taIngreso['nConsulta']:'');;
					$taIngreso['cEnfermedadActual'] = trim(isset($taIngreso['cEnfermedadActual'])?$taIngreso['cEnfermedadActual']:'');
					$taIngreso['cEstadoIngreso'] = trim(isset($taIngreso['cEstadoIngreso'])?(!empty(trim($taIngreso['cEstadoIngreso']))? trim($taIngreso['cEstadoIngreso']): '2'):'2');
					$taIngreso['cHabitacion'] = trim(isset($taIngreso['cHabitacion'])?$taIngreso['cHabitacion']:'');
					$taIngreso['cMedicoTratante'] = trim(isset($taIngreso['cMedicoTratante'])?$taIngreso['cMedicoTratante']:'');
					$taIngreso['cPacienteCarnet'] = trim(isset($taIngreso['cPacienteCarnet'])?(!empty(trim($taIngreso['cPacienteCarnet']))? trim($taIngreso['cPacienteCarnet']): substr(trim(strval($taIngreso['nPacienteId'])),-13)):substr(trim(strval($taIngreso['nPacienteId'])),-13));
					$taIngreso['cPacienteEmail'] = strtolower(trim(isset($taIngreso['cPacienteEmail'])?$taIngreso['cPacienteEmail']:''));
					$taIngreso['cPacienteGenero'] = trim(isset($taIngreso['cPacienteGenero'])?$taIngreso['cPacienteGenero']:'');
					$taIngreso['cPacienteGrupoRH'] = trim(isset($taIngreso['cPacienteGrupoRH'])?$taIngreso['cPacienteGrupoRH']:'');
					$taIngreso['cPacienteId'] = trim(isset($taIngreso['cPacienteId'])?$taIngreso['cPacienteId']:'');
					$taIngreso['cPacientePermisoPermanenciaTiene'] = trim(isset($taIngreso['cPacientePermisoPermanenciaTiene'])?$taIngreso['cPacientePermisoPermanenciaTiene']:'');
					$taIngreso['cPacienteTieneEmail'] = trim(isset($taIngreso['cPacienteTieneEmail'])?$taIngreso['cPacienteTieneEmail']:'');
					$taIngreso['cRemiteCiudad'] = trim(isset($taIngreso['cRemiteCiudad'])?$taIngreso['cRemiteCiudad']:'');
					$taIngreso['cRemiteDepartamento'] = trim(isset($taIngreso['cRemiteDepartamento'])?$taIngreso['cRemiteDepartamento']:'');
					$taIngreso['cRemiteEntidad'] = trim(isset($taIngreso['cRemiteEntidad'])?$taIngreso['cRemiteEntidad']:'');
					$taIngreso['cRemitePais'] = trim(isset($taIngreso['cRemitePais'])?$taIngreso['cRemitePais']:'');
					$taIngreso['cResponsableId'] = (trim(strtoupper($taIngreso['cResponsablePaciente']))<>'PACIENTE' ? $taIngreso['cResponsableId'] : $taIngreso['cPacienteId']);
					$taIngreso['cResponsableParentesco'] = trim(isset($taIngreso['cResponsableParentesco'])?(!empty(trim($taIngreso['cResponsableParentesco']))? trim($taIngreso['cResponsableParentesco']): ''):'');
					$taIngreso['cSeccion'] = trim(isset($taIngreso['cSeccion'])?$taIngreso['cSeccion']:'');
					$taIngreso['cTriage'] = trim(isset($taIngreso['cTriage'])?$taIngreso['cTriage']:'');
					$taIngreso['nFechaIngreso'] = intval(str_replace("/","",str_replace("-","",$taIngreso['nFechaIngreso'])));
					$taIngreso['nHoraIngreso'] = intval(str_replace(":","",$taIngreso['nHoraIngreso']));
					$taIngreso['nIngreso'] = intval($taIngreso['nIngreso']);
					$taIngreso['nMapla'] = intval($taIngreso['nMapla']);
					$taIngreso['nNacimiento'] = intval(str_replace("/","",str_replace("-","",$taIngreso['nNacimiento'])));
					$taIngreso['nNacioCiudad'] = intval($taIngreso['nNacioCiudad']);
					$taIngreso['nNacioDepartamento'] = intval($taIngreso['nNacioDepartamento']);
					$taIngreso['nNacioPais'] = intval($taIngreso['nNacioPais']);
					$taIngreso['nPacienteId'] = intval($taIngreso['nPacienteId']);
					$taIngreso['nPacienteLaboralOcupacion'] = intval(isset($taIngreso['nPacienteLaboralOcupacion'])?$taIngreso['nPacienteLaboralOcupacion']:'');
					$taIngreso['nPacienteRecideCiudad'] = intval(isset($taIngreso['nPacienteRecideCiudad'])?$taIngreso['nPacienteRecideCiudad']:'');
					$taIngreso['nPacienteRecideDepartamento'] = intval(isset($taIngreso['nPacienteRecideDepartamento'])?$taIngreso['nPacienteRecideDepartamento']:'');
					$taIngreso['nPacienteRecideLocalidad'] = intval(isset($taIngreso['nPacienteRecideLocalidad'])?$taIngreso['nPacienteRecideLocalidad']:'');
					$taIngreso['nPacienteRecidePais'] = intval($taIngreso['nPacienteRecidePais']);
					$taIngreso['nPacienteRecideZona'] = intval($taIngreso['nPacienteRecideZona']);
					$taIngreso['nRaza'] = intval($taIngreso['nRaza']);
					$taIngreso['nResponsableId'] = (trim(strtoupper($taIngreso['cResponsablePaciente']))<>'PACIENTE' ? (isset($taIngreso['nResponsableId'])?$taIngreso['nResponsableId']:'') : $taIngreso['nPacienteId']);
					$taIngreso['nResponsableRecideCiudad'] = intval($taIngreso['nResponsableRecideCiudad']);
					$taIngreso['nResponsableRecideDepartamento'] = intval($taIngreso['nResponsableRecideDepartamento']);
					$taIngreso['nResponsableRecidePais'] = intval($taIngreso['nResponsableRecidePais']);
					$taIngreso['nTriageId'] = intval($taIngreso['nTriageId']);

					$llObligaNuevoIngreso = false;

					// Validando numero de historia clínica
					$lnHistoria = 0;
					if(!empty($taIngreso['cPacienteId']) && empty($taIngreso['nPacienteId'])){
						$laCampos = ['P.NHCPAC HISTORIA'];
						$laRegistro = $goDb
							->select($laCampos)
							->from('RIAPAC P')
							->where('P.TIDPAC', '=', $taIngreso['cPacienteId'])
							->where('P.NIDPAC', '=', $taIngreso['nPacienteId'])
							->get('array');

						if(is_array($laRegistro)==true){
							if(count($laRegistro)>0){
								$lnHistoria = intval(is_null($laRegistro['HISTORIA'])?0:$laRegistro['HISTORIA']);
							}
						}
					}

					$lnHistoria = (empty($lnHistoria)==true ? $this->nuevoNumeroHc() : $lnHistoria);
					$taIngreso['nHistoria'] = $lnHistoria;


					if($this->getIngresoValidacion('medico-tratante',$taIngreso ,$taPlanes, $lcMensaje)==true){
						if($this->getIngresoValidacion('pasaporte',$taIngreso ,$taPlanes, $lcMensaje)==true){
							if($this->getIngresoValidacion('nueva-via',$taIngreso ,$taPlanes, $lcMensaje)==true){
								if($this->getIngresoValidacion('estado-ingreso',$taIngreso ,$taPlanes, $lcMensaje)==true){
									if($this->getIngresoValidacion('fecha-ingreso-egreso',$taIngreso ,$taPlanes, $lcMensaje)==true){
										if($this->getIngresoValidacion('plan',$taIngreso ,$taPlanes, $lcMensaje)==true){
											if($this->getIngresoValidacion('eps',$taIngreso ,$taPlanes, $lcMensaje)==true){
												if($this->getIngresoValidacion('fecha-nacimiento',$taIngreso ,$taPlanes, $lcMensaje)==true){
													if($this->getIngresoValidacion('habitacion',$taIngreso ,$taPlanes, $lcMensaje)==true){
														if($this->getIngresoValidacion('consecutivo-historia-paciente',$taIngreso ,$taPlanes, $lcMensaje)==true){

															//CONSECUTIVO DE INGRESO
															$taIngreso['nIngreso'] = intval($taIngreso['nIngreso']);
															$llObligaNuevoIngreso = ($taIngreso['nIngreso']==0);
															$llContinuar = true;

															if($llObligaNuevoIngreso){
																$taIngreso['nIngreso'] = $this->nuevoNumeroIngreso();
																if($this->ingresoExiste($taIngreso['nIngreso']) == true){
																	$llContinuar = false;
																	$laResultado['MENSAJE']='PRECAUCIÓN: No se puede guardar el ingreso ya que el consecutivo que se reservo no esta disponible. Por favor presione el botón [Guardar] para solicitar uno nuevo y guardar el ingreso';
																}
															}

															if($llContinuar == true){

																// GUARDAR INGRESO (PRINCIPAL, DETALLE)
																if($this->guardarIngreso($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora, $llObligaNuevoIngreso)==true){

																	// GUARDAR PACIENTE (GENERAL, EXTRA, DETALLES, TERCERO, BITACORA CAPTURA)
																	$this->guardarPaciente($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// GUARDAR MODIFICACIONES EN CITA/PROCEDIMIENTO
																	$this->guardarCitaProcedimiento($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// PLANES DEL PACIENTE
																	//.GuardarPlanesPaciente(tcUsuario,$this->cPrograma,$lnFecha,$lnHora,@$lcLog)


																	// GUARDAR INFORMACION DEL RESPONSABLE
																	$this->guardarResponsable($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// DATOS HOSPITALZIACION/AMBULATORIOS
																	if($taIngreso['cMetodo']<>"MODIFICAR"){
																		$this->guardarHabitacion($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);
																	}

																	// COBRO DE INSUMOS Y OTROS ELEMENTOS
																	$this->guardarCobroInsumos($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// ADICION DE CUPS
																	$this->guardarAdicionCups($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// GUARDAR TRIAGE
																	if($taIngreso['cMetodo']<>"MODIFICAR"){
																		$taIngreso['nTriageId'] = ($taIngreso['nTriageId']>0 ? $taIngreso['nTriageId'] : $this->triageNuevo() );
																		$this->guardarTriage($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);
																	}

																	/* *!* AGFA
																	lcTipoMensaje 	= 'A08'
																	lnTipoRuta 		= 2
																	lcIdeAnterior	= ''
																	= pAgfaUbicacion(lcTipoMensaje,lnTipoRuta,$taIngreso['cPacienteId'],$taIngreso['nPacienteId'],$taIngreso['nIngreso'],lcIdeAnterior,$this->cPrograma)*/

																	// ORDEN DE HOSPITALIZACIÓN
																	$this->guardarOrdenHospitalizacion($taIngreso, $tcUsuario, $this->cPrograma, $lnFecha, $lnHora);

																	// CONTROL DE AUTORIZACIONES
																	//.AutorizacionesPacientes()

																	// NOTIFICACION POR E-MAIL
																	//.NotificarEmail()*/

																	$laResultado['GUARDADO'] = true;
																	$laResultado['NUMERO_INGRESO'] = $taIngreso['nIngreso'];
																	$laResultado['NUMERO_TRIAGE'] = $taIngreso['nTriageId'];
																	$laResultado['INGRESO'] = $taIngreso;
																	$laResultado['PLANES'] = $taPlanes;

																}else{
																	$laResultado['MENSAJE']='PRECAUCIÓN: No se puede guardar el ingreso ya que el consecutivo que se reservo no esta disponible. Por favor presione el botón [Guardar] para solicitar uno nuevo y guardar el ingreso';
																}
															}
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
					$laResultado['MENSAJE'] = $lcMensaje;
				}else{
					$laResultado['MENSAJE']='No hay manejador de datos';
				}
			}else{
				$laResultado['MENSAJE']='No hay datos en la información recibida';
			}
		}else{
			$laResultado['MENSAJE']='Los datos de recibidos no tienen el formato adecuado';
		}
		return $laResultado;
	}
	
	public function listadoEstadosIngreso()
	{
		$laEstados=[];
		global $goDb;
		if(isset($goDb)){

			$laEstados = $goDb
			->select('TRIM(CL2TMA) CODIGO, TRIM(DE2TMA) DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=','DATING')
			->where('CL1TMA', '=','ESTADO')
			->getAll('array');
		}
		return $laEstados;
	}
	
	public function consultaIngresosPaciente($tcTipoIden='',$tnIdentificacion=0,$tnIngreso=0,$tcViaIngreso='',$tcEstado='',$tcPlan='')
	{
		$laIngresos=$laWhere=[];
		global $goDb;
		if(isset($goDb)){

			if ($tcTipoIden) $laWhere['A.TIDING'] = $tcTipoIden;
			if ($tnIdentificacion) $laWhere['A.NIDING'] = $tnIdentificacion;
			if ($tnIngreso) $laWhere['A.NIGING'] = $tnIngreso;
			if ($tcViaIngreso) $laWhere['A.VIAING'] = $tcViaIngreso;
			if ($tcEstado) $laWhere['A.ESTING'] = $tcEstado;
			if ($tcPlan) $laWhere['A.PLAING'] = $tcPlan;
			
			$laIngresos = $goDb
			->select('TRIM(A.TIDING) TIPOID, A.NIDING DOCUMENTO, A.NIGING INGRESO, TRIM(A.PLAING) PLANING')
			->select('(SELECT TRIM(DSCCON) FROM FACPLNC WHERE PLNCON=A.PLAING AND ESTCON=\'A\') AS DESC_PLAN')
			->select('A.VIAING VIAINGRESO, A.FEIING FECINGRESO, A.ESTING ESTADO')
			->select('(SELECT TRIM(DESVIA) FROM RIAVIA WHERE CODVIA=A.VIAING) AS DESC_VIAINGRESO')
			->select('(SELECT TRIM(DE2TMA) FROM TABMAE WHERE TIPTMA=\'DATING\' AND CL1TMA=\'ESTADO\' AND CL2TMA=A.ESTING) AS DESC_ESTADOINGRESO')
			->select("IFNULL(TRIM(B.NM1PAC)||' '||TRIM(B.NM2PAC)||' '||TRIM(B.AP1PAC)||' '||TRIM(B.AP2PAC),'') NOMBRE_PACIENTE")
			->from('RIAING A')
			->leftJoin('RIAPAC B','A.TIDING=B.TIDPAC AND A.NIDING=B.NIDPAC')
			->where($laWhere)
			->orderBy('A.NIGING DESC')
			->getAll('array');
		}
		return $laIngresos;
	}
	
	public function tipoSeccionPaciente($tnIngreso=0, $tcViaIngreso='')
	{
		global $goDb;
		$lcTipoSeccion=$lcSeccion=$lcClasificacion=$lcSubClasificacion='';
		
		if ($tnIngreso>0){
			$lcTipoSeccion=$goDb->obtenerTabmae1('OP1TMA', 'DATING', "CL1TMA='TSEVIA' AND CL2TMA='$tcViaIngreso' AND ESTTMA=''", null, '');
			
			if (empty($lcTipoSeccion)){
				$laHabitacion = $goDb
					->select('TRIM(A.SECHAB) SECCION, TRIM(B.OP2TMA) CLASIFICACION, TRIM(B.CL4TMA) SUBCLASIFICACION')
					->from('FACHAB A')
					->leftJoin("TABMAE B", "A.SECHAB=B.CL1TMA AND B.TIPTMA='SECHAB'", null)
					->where('A.INGHAB', '=', $tnIngreso)
					->where('A.IDDHAB', '=', '0')
					->in('A.ESTHAB', ['1','9'])
				->get('array');
				if ($goDb->numRows()>0){
					$lcSeccion=$laHabitacion['SECCION'];
					$lcSeccionInicial=mb_substr(trim($lcSeccion), 0, 1);
					$lcClasificacion=$laHabitacion['CLASIFICACION'];
					$lcSubClasificacion=$laHabitacion['SUBCLASIFICACION'];
				}
			}

			if (empty($lcTipoSeccion)){
				$lcTipoSeccion=$goDb->obtenerTabmae1('OP1TMA', 'DATING', "CL1TMA='TSESEC' AND CL2TMA='$tcViaIngreso' AND CL3TMA='$lcSeccion' AND ESTTMA=''", null, '');
			}	
			
			if (empty($lcTipoSeccion)){
				if (!empty($lcSeccion) && !empty($lcClasificacion) && !empty($lcSubClasificacion)){
					$lcTipoSeccion=$goDb->obtenerTabmae1('OP1TMA', 'DATING', "CL1TMA='TSETIP' AND CL2TMA='$tcViaIngreso' AND CL3TMA='$lcClasificacion'  AND CL4TMA='$lcSubClasificacion' AND ESTTMA=''", null, '');
				}
			}	
			
			if (empty($lcTipoSeccion)){
				$lcTipoSeccion=$goDb->obtenerTabmae1('OP1TMA', 'DATING', "CL1TMA='TSEEMP' AND CL2TMA='$lcSeccionInicial' AND ESTTMA=''", null, '');
			}
		}
		unset($laHabitacion);
		return $lcTipoSeccion;
	}

}
