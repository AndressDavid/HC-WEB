<?php

namespace NUCLEO;

require_once ('class.Db.php') ;
require_once ('class.Entidades.php');
require_once ('class.Ingreso.php');
require_once ('class.Habitacion.php');


use NUCLEO\Db;
use NUCLEO\Entidades;

class Bitacoras
{
	private $nConsecutivo = 0;
	private $nInicioFecha = 0;
	private $nInicioHora = 0;
	private $nFinFecha = 0;
	private $nFinHora = 0;
	private $nEstado = 0;
	private $oIngreso = null;
	private $aTiposBitacoras = array();
	private $aTiposDetalleBitacora = array();
	private $aProveedores = array();
	private $aModos = array();
	private $aEstados = array();
	private $aEstadosDetalle = array();
	private $cTipoBitacora = 'UNDEFINE';
	private $aTipoBitacora = array();
	private $oEntidades = null;
	private $nIngreso=0;
	private $nRegistros=0;
	private $nRegistrosSinConfirmar=0;
	private $cPrograma = '';

    public function __construct($tcTipoBitacora='', $tnConsecutivo=0, $tnIngreso=0) {
		$this->aTiposBitacoras = $this->TiposBitacoras();
		$this->cTipoBitacora = $this->validarTipoBitacora($tcTipoBitacora);
		$this->aTiposDetalleBitacora = $this->TiposDetallesBitacora();
		$this->aModos = $this->ModosVisualizar();
		$this->aEstados = $this->Estados();
		$this->aEstadosDetalle = $this->EstadosDetalles();
		$this->aProveedores = $this->Proveedores();

		$tnConsecutivo = intval($tnConsecutivo);
		if($tnConsecutivo>0){
			$this->cargarBitacora($tnConsecutivo);
		}

		$tnIngreso = intval($tnIngreso);
		if(is_null($this->oIngreso)==true && $tnIngreso>0){
			$this->cargarIngreso($tnIngreso);
		}
		
		
		$this->contarSeguimiento();
		$this->cPrograma = substr(pathinfo(__FILE__, PATHINFO_FILENAME),0,10);
	}

	private function Proveedores(){
		global $goDb;
		$aTipos = array();

		if (isset($goDb)) {
			$aTipos = $goDb
						->select('TRIM(CL3TMA) AS INDICE , TRIM(DE1TMA) AS CODIGO , TRIM(DE2TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010104')
						->where("ESTTMA=''")
						->orderBy('TRIM(DE2TMA)')
						->getAll('array');
		}

		return $aTipos;
	}



	private function TiposDetallesBitacora(){
		global $goDb;
		$aTipos = array();

		if (isset($goDb)) {
			$aTipos = $goDb
						->select('TRIM(CL3TMA) AS CODIGO , TRIM(DE1TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010103')
						->where("ESTTMA=''")
						->orderBy('TRIM(DE1TMA)')
						->getAll('array');
		}

		return $aTipos;
	}

	private function TiposBitacoras(){
		global $goDb;
		$aTipos = array();

		if (isset($goDb)) {
			$aTipos = $goDb
						->select('TRIM(CL3TMA) AS CODIGO , TRIM(DE1TMA) AS TITULO, TRIM(DE2TMA) ICONO, TRIM(OP5TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', 'TIPOS')
						->where('CL2TMA', '01010101')
						->where("ESTTMA=''")
						->getAll('array');
		}

		return $aTipos;
	}

	private function ModosVisualizar(){
		global $goDb;
		$laEstados = array();

		if (isset($goDb)) {
			$laEstados = $goDb
						->select('TRIM(CL3TMA) AS CODIGO, TRIM(DE1TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010101')
						->where("ESTTMA=''")
						->getAll('array');
		}

		return $laEstados;
	}

	private function Estados(){
		global $goDb;
		$laEstados = array();

		if (isset($goDb)) {
			$laEstados = $goDb
						->select('TRIM(CL3TMA) AS CODIGO, TRIM(DE1TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010102')
						->where("ESTTMA=''")
						->getAll('array');
		}

		return $laEstados;
	}
	
	private function EstadosDetalles(){
		global $goDb;
		$laEstados = array();

		if (isset($goDb)) {
			$laEstados = $goDb
						->select('TRIM(CL3TMA) AS CODIGO, TRIM(DE1TMA) AS DESCRIPCION')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010105')
						->where("ESTTMA=''")
						->getAll('array');
		}

		return $laEstados;
	}	

	private function validarTipoBitacora($tcTipoBitacora=''){
		$tcTipoBitacora = trim(strval($tcTipoBitacora));

		if(array_search($tcTipoBitacora,array_column($this->aTiposBitacoras,'CODIGO'))===false){
			$tcTipoBitacora = 'UNDEFINE';
		}else{
			$this->aTipoBitacora = $this->aTiposBitacoras[intval(array_search($tcTipoBitacora,array_column($this->aTiposBitacoras,'CODIGO')))];
		}

		return $tcTipoBitacora;
	}
	
	private function contarSeguimiento(){
		if($this->nConsecutivo>0){
			global $goDb;

			if (isset($goDb)) {
				$laRegistros = $goDb
							->count('A.CONCAB','REGISTROS')
							->tabla('BITDET A')
							->where('CONCAB', $this->nConsecutivo)
							->get('array');
				$this->nRegistros = intval($laRegistros['REGISTROS']);
				
				$laRegistros = $goDb
							->count('A.CONCAB','REGISTROS')
							->tabla('BITDET A')
							->where('CONCAB', $this->nConsecutivo)
							->where('FINFEC','=',0)
							->where('FINHOR','=',0)
							->get('array');
				$this->nRegistrosSinConfirmar = intval($laRegistros['REGISTROS']);				
			}
		}
		
	}

	public function cargarIngreso($tnIngreso=0){
		$tnIngreso = intval($tnIngreso);
		if($tnIngreso>0){
			$this->oIngreso = new Ingreso;
			$this->oIngreso->cargarIngreso($tnIngreso);
			$this->nIngreso = $this->oIngreso->nIngreso;
		}
	}

	public function cargarBitacora($tnConsecutivo=0){
		$tnConsecutivo = intval($tnConsecutivo);

		if($tnConsecutivo>0){
			global $goDb;

			if (isset($goDb)) {
				$laCampos = ['A.CONCAB','A.ESTADO','A.NIGING', 'A.INIFEC','A.INIHOR','A.FINFEC','A.FINHOR'];
				$laCabecera = $goDb
							->select($laCampos)
							->tabla('BITCAB A')
							->where('CONCAB', $tnConsecutivo)
							->get('array');
				if(is_array($laCabecera)==true){
					if(count($laCabecera)>0){
						$this->nConsecutivo = $laCabecera['CONCAB'];
						$this->nEstado = $laCabecera['ESTADO'];
						$this->nInicioFecha = $laCabecera['INIFEC'];
						$this->nInicioHora = $laCabecera['INIHOR'];
						$this->nFinFecha = $laCabecera['FINFEC'];
						$this->nFinHora = $laCabecera['FINHOR'];

						if(is_null($this->oIngreso)==true){
							$this->cargarIngreso($laCabecera['NIGING']);
						}
					}
				}
			}
		}
	}

	public function insertar($tcUsuario='')	{
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			if($this->nIngreso>0){

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");
				$lcTabla = 'BITCAB';
				
				if($this->nConsecutivo==0){
					$this->nConsecutivo = $goDb->secuencia('SEQ_BITCAB');

					// Obtener habitación
					$loHab = new Habitacion();
					$loHab->cargarHabitacion($this->nIngreso);

					// Insertando la nueva lectura
					$laDatos = ['CONCAB'=>$this->nConsecutivo,
								'TIPBIT'=>$this->cTipoBitacora,
								'NIGING'=>$this->oIngreso->nIngreso,
								'TIDING'=>$this->oIngreso->cId,
								'NIDING'=>$this->oIngreso->nId,
								'ESTADO'=>$this->nEstado,
								'INIFEC'=>$lcFecha,
								'INIHOR'=>$lcHora,
								'FINFEC'=>$lcFecha,
								'FINHOR'=>$lcHora,
								'USCCAB'=>$tcUsuario,
								'PGCCAB'=>$this->cPrograma,
								'FECCAB'=>$lcFecha,
								'HOCCAB'=>$lcHora
								];

					$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);
				}else{
					$laDatos = ['ESTADO'=>$this->nEstado,
								'FINFEC'=>$lcFecha,
								'FINHOR'=>$lcHora,
								'USMCAB'=>$tcUsuario,
								'PGMCAB'=>$this->cPrograma,
								'FEMCAB'=>$lcFecha,
								'HOMCAB'=>$lcHora
								];	
					$llResultado = $goDb->tabla($lcTabla)->where('CONCAB',$this->nConsecutivo)->where('TIPBIT',$this->cTipoBitacora)->where('NIGING',$this->oIngreso->nIngreso)->actualizar($laDatos);					
				}
			}
		}

		return $llResultado;
	}
	


	public function insertarDetalle($tnConsecutivo=0, $tnEstado=0, $tcEntidad='', $tcProveedor='', $tcTramite='', $tnIniFecha=0, $tnIniHora=0, $tnFinFecha=0, $tnFinHora=0, $tcObservacion='', $tcUsuario=''){
		$llResultado = false;
		$tnConsecutivo=intval($tnConsecutivo);
		$tnEstado=intval($tnEstado);
		$tcEntidad=trim(strval($tcEntidad));
		$tcProveedor=trim(strval($tcProveedor));
		$tcTramite=trim(strval($tcTramite));
		$tnIniFecha=intval($tnIniFecha);
		$tnIniHora=intval($tnIniHora);
		$tnFinFecha=intval($tnFinFecha);
		$tnFinHora=intval($tnFinHora);
		$tcObservacion=trim(strval($tcObservacion));
		$tcUsuario=trim(strval($tcUsuario));		

		global $goDb;
		if(isset($goDb)){

			if($this->nIngreso>0 && $this->nConsecutivo>0){

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");
				$lcTabla = 'BITDET';
				
				if($this->nConsecutivo>0){
					if($tnConsecutivo==0){

						$tnConsecutivo = $goDb->secuencia('SEQ_BITDET');
						$laDatos = ['CONDET'=>$tnConsecutivo,
									'CONCAB'=>$this->nConsecutivo,
									'NIGING'=>$this->nIngreso,
									'ESTADO'=>$tnEstado,
									'ENTDET'=>$tcEntidad,
									'PROVEE'=>$tcProveedor,
									'TRAMIT'=>$tcTramite,
									'SECCIN'=>$this->oIngreso->oHabitacion->cSeccion,
									'HABITA'=>$this->oIngreso->oHabitacion->cHabitacion,
									'INIFEC'=>$tnIniFecha,
									'INIHOR'=>$tnIniHora,
									'FINFEC'=>$tnFinFecha,
									'FINHOR'=>$tnFinHora,
									'EGRFEC'=>$this->oIngreso->nEgresoFecha,
									'EGRHOR'=>$this->oIngreso->nEgresoHora,
									'OBSERV'=>trim(substr($tcObservacion,0,510)),
									'USCDET'=>$tcUsuario,
									'PGCDET'=>$this->cPrograma,
									'FECDET'=>$lcFecha,
									'HOCDET'=>$lcHora
									];

						$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);
					}else{
						$laDatos = ['ESTADO'=>$tnEstado,
									'PROVEE'=>$tcProveedor,
									'FINFEC'=>$tnFinFecha,
									'FINHOR'=>$tnFinHora,
									'EGRFEC'=>$this->oIngreso->nEgresoFecha,
									'EGRHOR'=>$this->oIngreso->nEgresoHora,									
									'OBSERV'=>trim(substr($tcObservacion,0,510)),
									'USMDET'=>$tcUsuario,
									'PGMDET'=>$this->cPrograma,
									'FEMDET'=>$lcFecha,
									'HOMDET'=>$lcHora
									];	
						$llResultado = $goDb->tabla($lcTabla)->where('CONDET',$tnConsecutivo)->where('CONCAB',$this->nConsecutivo)->where('NIGING',$this->oIngreso->nIngreso)->actualizar($laDatos);				
					}

					/*if($llResultado==true){
						if(!empty($tcObservacion)){
							$this->insertarObservacion($tnConsecutivo, $tcObservacion, $tcUsuario);
						}
					}*/					
				}
			}
		}
		
		$this->contarSeguimiento();

		return $llResultado;
	}

	public function insertarObservacion($tnDetalle=0, $tcObservacion='', $tcUsuario=''){
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			$tcObservacion = trim(strval($tcObservacion));
			if($this->nIngreso>0 && $this->nConsecutivo>0 && strlen($tcObservacion)>0){

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");

				$lnConsecutivo = $goDb->secuencia('SEQ_BITOBS');

				// Insertando la nueva lectura
				$laDatos = ['CONOBS'=>$lnConsecutivo,
							'CONCAB'=>$this->nConsecutivo,
							'CONDET'=>$tnDetalle,
							'OBSERV'=>trim(substr($tcObservacion,0,510)),
							'USCOBS'=>$tcUsuario,
							'PGCOBS'=>$this->cPrograma,
							'FECOBS'=>$lcFecha,
							'HOCOBS'=>$lcHora
							];

				$llResultado = $goDb->tabla('BITOBS')->insertar($laDatos);
			}
		}

		return $llResultado;
	}
	
	public function insertarProveedor($tnCodigo=0, $tcNombre='', $tcUsuario=''){
		$llResultado = false;

		global $goDb;
		if(isset($goDb)){
			$tnCodigo = intval($tnCodigo);
			$tcNombre = strtoupper(trim(strval($tcNombre)));
			$lcTabla = 'TABMAE';
			$lcCl2Tma = '01010104';
			
			if(!empty($tnCodigo) && !empty($tcNombre)){
				$lcCodigo = str_pad(trim(strval(intval($tnCodigo))), 13, "0", STR_PAD_LEFT);
				$laProvedores = $goDb->count('*','REGISTROS')->tabla($lcTabla)->where('TIPTMA', 'BITPAR')->where('CL1TMA', $this->cTipoBitacora)->where('CL2TMA', $lcCl2Tma)->where('DE1TMA', $lcCodigo)->get('array');

				$ltAhora = new \DateTime( $goDb->fechaHoraSistema() );
				$lcFecha = $ltAhora->format("Ymd");
				$lcHora  = $ltAhora->format("His");
					
				if($laProvedores['REGISTROS']<=0){

					$laProvedores = $goDb->max('INTEGER(CL3TMA)','REGISTROS')->tabla($lcTabla)->where('TIPTMA', 'BITPAR')->where('CL1TMA', $this->cTipoBitacora)->where('CL2TMA', $lcCl2Tma)->get('array');
					$lnConsecutivo = $laProvedores['REGISTROS']+1;

					$laDatos = ['TIPTMA'=>'BITPAR',
								'CL1TMA'=>$this->cTipoBitacora,
								'CL2TMA'=>$lcCl2Tma,
								'CL3TMA'=>strval($lnConsecutivo),
								'DE1TMA'=>$lcCodigo,
								'DE2TMA'=>$tcNombre,
								'USRTMA'=>$tcUsuario,
								'PGMTMA'=>$this->cPrograma,
								'FECTMA'=>$lcFecha,
								'HORTMA'=>$lcHora
								];
					$llResultado = $goDb->tabla($lcTabla)->insertar($laDatos);
				}else{
					$laDatos = ['ESTTMA'=>'',
								'DE2TMA'=>$tcNombre,
								'UMOTMA'=>$tcUsuario,
								'PMOTMA'=>$this->cPrograma,
								'FMOTMA'=>$lcFecha,
								'HMOTMA'=>$lcHora
								];	
					$llResultado = $goDb->tabla($lcTabla)->where('TIPTMA', 'BITPAR')->where('CL1TMA', $this->cTipoBitacora)->where('CL2TMA', $lcCl2Tma)->where('DE1TMA', $lcCodigo)->actualizar($laDatos);
				}
			}
		}
		
		if($llResultado==true){
			$this->aProveedores = $this->Proveedores();
		}
		
		return $llResultado;		
	}

	public function buscar($tcModo='', $tnInicio=0, $tnFin=0, $tcEstado=''){
		global $goDb;
		$laRegistros = array();
	
		if (isset($goDb)) {
			
			$lcWhere = sprintf("A.TIPBIT='%s'",$this->cTipoBitacora);
			if(!empty($this->nIngreso)){ $lcWhere .= sprintf(' AND A.NIGING=%s',$this->nIngreso); }
			if(!empty($tcEstado)){ $lcWhere .= sprintf(" AND A.ESTADO='%s'",$tcEstado); }
			if(!empty($tnInicio) && !empty($tnFin)){ 
				$lcWhere .= sprintf(' AND ((A.INIFEC>=%s AND A.INIFEC<=%s) OR (A.FINFEC>=%s AND A.FINFEC<=%s))',$tnInicio, $tnFin, $tnInicio, $tnFin);
			}		
				
			if($tcModo=='PACIENTE'){
				$laCampos = ['A.CONCAB CONSECUTIVO',
							 'A.ESTADO ESTADO',
							 'TRIM(W.DE1TMA) ESTADO_NOMBRE',
							 'A.NIGING INGRESO',
							 'A.TIDING DOCTIPO',
							 'A.NIDING DOCNUMERO',
							 "TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE",
							 'A.INIFEC INICIO_FECHA',
							 'A.INIHOR INICIO_HORA',
							 'A.FINFEC FIN_FECHA',
							 'A.FINHOR FIN_HORA',
							 'E.DSCCON ENTIDAD',
							 '(SELECT COUNT(B.CONCAB) FROM BITDET B WHERE B.CONCAB=A.CONCAB) TRAMITES',
							 'H.SECHAB SECCION',
							 'H.NUMHAB HABITACION',
							];
				$laRegistros = $goDb
							->select($laCampos)
							->leftJoin('RIAPAC P', 'A.TIDING=P.TIDPAC AND A.NIDING=P.NIDPAC', null)
							->leftJoin('RIAING I', 'I.NIGING = A.NIGING', null)
							->leftJoin('FACPLNC4 E', 'E.NI1CON = I.ENTING AND E.PLNCON=I.PLAING', null)
							->leftJoin('FACHAB H', 'H.INGHAB = A.NIGING', null)
							->leftJoin('TABMAE W', sprintf("W.TIPTMA='BITPAR' AND W.CL1TMA='%s' AND W.CL2TMA='01010102' AND W.CL3TMA=A.ESTADO", $this->cTipoBitacora), null)
							->tabla('BITCAB A')
							->where($lcWhere)
							->getAll('array');		
			}else{		
				$laCampos = ['A.CONCAB CONSECUTIVO',
							 'A.ESTADO ESTADO',
							 'TRIM(W.DE1TMA) ESTADO_NOMBRE',
							 'A.NIGING INGRESO',
							 'A.TIDING DOCTIPO',
							 'A.NIDING DOCNUMERO',
							 "TRIM(P.NM1PAC) || ' ' || TRIM(P.NM2PAC) || ' ' || TRIM(P.AP1PAC) || ' ' || TRIM(P.AP2PAC) PACIENTE",
							 'A.INIFEC INICIO_FECHA',
							 'A.INIHOR INICIO_HORA',
							 'A.FINFEC FIN_FECHA',
							 'A.FINHOR FIN_HORA',
							 '(SELECT COUNT(B.CONCAB) FROM BITDET B WHERE B.CONCAB=A.CONCAB) TRAMITES',
							 'H.SECHAB SECCION',
							 'H.NUMHAB HABITACION',
							 'B.CONDET DETALLE',
							 'B.ESTADO ESTADO_SEGUIMIENTO',
							 'TRIM(Z.DE1TMA) ESTADO_SEGUIMIENTO_NOMBRE',
							 'B.INIFEC INICIO_FECHA',
							 'B.INIHOR INICIO_HORA',
							 'B.FINFEC FIN_FECHA',
							 'B.FINHOR FIN_HORA',
							 'SPACE(1) DIAGNOSTICO',
							 'B.ENTDET ENTIDAD',
							 'E.TE1SOC ENTIDAD_RAZON_SOCIAL',
							 'E.TE1COM ENTIDAD_RAZON_COMERCIAL',
							 'B.TRAMIT TRAMITE',
							 'TRIM(X.DE1TMA) TRAMITE_NOMBRE',
							 'B.SECCIN SECCION',
							 'B.HABITA HABITACION',
							 'B.PROVEE PROVEEDOR',
							 'TRIM(Y.DE2TMA) PROVEEDOR_NOMBRE',
							 'B.EGRFEC EGRESO_FECHA',
							 'B.EGRHOR EGRESO_HORA',
							 'SPACE(1) OPORTUNIDADTRAMITE',
							 'TRIM(B.OBSERV) OBSERVACION',
							];
				$laRegistros = $goDb
							->select($laCampos)
							->tabla('BITCAB A')
							->leftJoin('BITDET B', 'B.CONCAB=A.CONCAB AND B.NIGING=A.NIGING', null)
							->leftJoin('RIAPAC P', 'A.TIDING=P.TIDPAC AND A.NIDING=P.NIDPAC', null)
							->leftJoin('RIAING I', 'I.NIGING = A.NIGING', null)
							->leftJoin('FACHAB H', 'H.INGHAB = A.NIGING', null)
							->leftJoin('PRMTE1 E', 'E.TE1COD=B.ENTDET', null)
							->leftJoin('TABMAE W', sprintf("W.TIPTMA='BITPAR' AND W.CL1TMA='%s' AND W.CL2TMA='01010102' AND W.CL3TMA=A.ESTADO", $this->cTipoBitacora), null)
							->leftJoin('TABMAE X', sprintf("X.CL3TMA=B.TRAMIT AND X.TIPTMA='BITPAR' AND X.CL1TMA='%s' AND X.CL2TMA='01010103'", $this->cTipoBitacora), null)
							->leftJoin('TABMAE Y', sprintf("Y.DE1TMA=B.PROVEE AND Y.TIPTMA='BITPAR' AND Y.CL1TMA='%s' AND Y.CL2TMA='01010104'", $this->cTipoBitacora), null)
							->leftJoin('TABMAE Z', sprintf("Z.CL3TMA=CHAR(B.ESTADO) AND Z.TIPTMA='BITPAR' AND Z.CL1TMA='%s' AND Z.CL2TMA='01010105'", $this->cTipoBitacora), null)
							->where($lcWhere)
							->getAll('array');				
			}
		}


		return $laRegistros;
	}

	public function buscarDetalles(){
		global $goDb;
		$laRegistros = array();
		
		$lcWhere = ['A.CONCAB'=>$this->nConsecutivo];

	
		if (isset($goDb)) {
			$laCampos = ['A.CONDET CONSECUTIVO',
						 'A.CONCAB BITACORA',
						 'A.ESTADO ESTADO',
						 'TRIM(Z.DE1TMA) ESTADO_NOMBRE',
						 'A.INIFEC INICIO_FECHA',
						 'A.INIHOR INICIO_HORA',
						 'A.FINFEC FIN_FECHA',
						 'A.FINHOR FIN_HORA',
						 'SPACE(1) DIAGNOSTICO',
						 'A.ENTDET ENTIDAD',
						 'E.TE1SOC ENTIDAD_RAZON_SOCIAL',
						 'E.TE1COM ENTIDAD_RAZON_COMERCIAL',
						 'A.TRAMIT TRAMITE',
						 'TRIM(X.DE1TMA) TRAMITE_NOMBRE',
						 'A.SECCIN SECCION',
						 'A.HABITA HABITACION',
						 'A.PROVEE PROVEEDOR',
						 'TRIM(Y.DE2TMA) PROVEEDOR_NOMBRE',
						 'A.EGRFEC EGRESO_FECHA',
						 'A.EGRHOR EGRESO_HORA',
						 'SPACE(1) OPORTUNIDADTRAMITE',
						 'TRIM(A.OBSERV) OBSERVACION',
						];
			$laRegistros = $goDb
						->select($laCampos)
						->tabla('BITDET A')
						->leftJoin('PRMTE1 E', 'E.TE1COD=A.ENTDET', null)
						->leftJoin('TABMAE X', sprintf("X.CL3TMA=A.TRAMIT AND X.TIPTMA='BITPAR' AND X.CL1TMA='%s' AND X.CL2TMA='01010103'", $this->cTipoBitacora), null)
						->leftJoin('TABMAE Y', sprintf("Y.DE1TMA=A.PROVEE AND Y.TIPTMA='BITPAR' AND Y.CL1TMA='%s' AND Y.CL2TMA='01010104'", $this->cTipoBitacora), null)
						->leftJoin('TABMAE Z', sprintf("Z.CL3TMA=CHAR(A.ESTADO) AND Z.TIPTMA='BITPAR' AND Z.CL1TMA='%s' AND Z.CL2TMA='01010105'", $this->cTipoBitacora), null)
						->where($lcWhere)
						->getAll('array');
		}


		return $laRegistros;
	}

	public function buscarProveedor($tnCodigo=0){
		global $goDb;
		$laProveedor = array();

		if (isset($goDb)) {
			if(!empty($tnCodigo)){
				$lcCodigo = str_pad(trim(strval(intval($tnCodigo))), 13, "0", STR_PAD_LEFT);
				$laProveedor = $goDb
							->select('TRIM(CL3TMA) AS INDICE , TRIM(DE1TMA) AS CODIGO , TRIM(DE2TMA) AS DESCRIPCION')
							->tabla('TABMAE')
							->where('TIPTMA', 'BITPAR')
							->where('CL1TMA', $this->cTipoBitacora)
							->where('CL2TMA', '01010104')
							->where('DE1TMA', $lcCodigo)
							->orderBy('TRIM(DE1TMA)')
							->get('array');
			}
		}
		return $laProveedor;		
	}
	
	public function getObservaciones($tnDetalle=0){
		global $goDb;
		$laRegistros = array();

		if (isset($goDb)) {
			$tnDetalle = intval($tnDetalle);
			if($this->nConsecutivo>0){
				$laCampos = ['A.CONOBS CONSECUTIVO',
							 'A.CONCAB BITACORA',
							 'A.CONDET DETALLE',
							 'A.OBSERV OBSERVACION',
							 'A.USCOBS USUARIO',
							 'A.FECOBS FECHA',
							 'A.HOCOBS HORA',
							];
				$laRegistros = $goDb
							->select($laCampos)
							->tabla('BITOBS A')
							->where('A.CONCAB','=', $this->nConsecutivo)
							->where('A.CONDET','=',$tnDetalle)
							->orderBy('A.CONOBS','DESC')
							->getAll('array');
			}
		}


		return $laRegistros;
	}
	
	public function permisos($tcUsuario=''){
		global $goDb;
		$tcUsuario = trim(strtoupper(strval($tcUsuario)));
		$laPermisos = array();

		if (isset($goDb)) {
			$laPermisosAux = $goDb
						->select('TRIM(CL3TMA) AS USUARIO , TRIM(DE2TMA) AS PERMISOS')
						->tabla('TABMAE')
						->where('TIPTMA', 'BITPAR')
						->where('CL1TMA', $this->cTipoBitacora)
						->where('CL2TMA', '01010106')
						->where('CL3TMA', $tcUsuario)
						->where("ESTTMA=''")
						->get('array');
			$laPermisos = explode(",",(is_array($laPermisosAux)?(isset($laPermisosAux['PERMISOS'])?trim(strtoupper($laPermisosAux['PERMISOS'])):''):''));
		}

		return $laPermisos;		
	}

	public function setEstado($tnEstado=0){
		$this->nEstado = intval($tnEstado);
	}

	public function getTiposBitacoras(){
		return $this->aTiposBitacoras;
	}

	public function getTiposDetalleBitacora(){
		return $this->aTiposDetalleBitacora;
	}

	public function getTipoBitacora(){
		return $this->aTipoBitacora;
	}

	public function getModosVisualizar(){
		return $this->aModos;
	}

	public function getConsecutivo(){
		return $this->nConsecutivo;
	}

	public function getEstado(){
		return $this->nEstado;
	}

	public function getEstados(){
		return $this->aEstados;
	}
	
	public function getEstadosDetalle(){
		return $this->aEstadosDetalle;
	}	

	public function getRegistros(){
		return $this->nRegistros;
	}
	
	public function getRegistrosSinConfirmar(){
		return $this->nRegistrosSinConfirmar;
	}	

	public function getIngreso(){
		return $this->oIngreso;
	}
	
	public function getProveedores(){
		return $this->aProveedores;
	}

}
?>