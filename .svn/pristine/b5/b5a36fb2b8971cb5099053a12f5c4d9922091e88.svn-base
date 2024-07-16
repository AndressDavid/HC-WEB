<?php
namespace NUCLEO;

require_once ('class.Db.php') ;
use NUCLEO\Db;

class OrdenHospitalizacion
{
	protected $aEstadoOrdenHospitaliza = [];
	protected $aEspecialidadesOrdenHos = [];
	protected $aMedicosOrdenHos = [];
	protected $aAreaOrdenHos = [];
	protected $aUbicacionOrdenHos = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}
	
	public function ObtenerEstadosOrdenHospitaliza()
	{
		$laParams = $this->oDb
			->select('trim(CL2TMA) AS CODIGO, trim(SUBSTR(DE2TMA,1,30)) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'EVOLUC')
			->where('CL1TMA', '=', 'ESTORDH')
			->where('ESTTMA', '=', ' ')
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aEstadoOrdenHospitaliza[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
					];
			}
		}
		return $this->aEstadoOrdenHospitaliza;
		unset($laParams);
	}
	
	public function ObtenerEspecialidadesOrdenHospitaliza()
	{
		$laParams = $this->oDb
			->select('trim(A.CODESP) AS CODIGO, trim(A.DESESP) AS DESCRIPCION')
			->from('RIAESPE AS A')
			->leftJoin('RIARGMN AS B', "TRIM(A.CODESP)=TRIM(B.CODRGM)", null)
			->where('A.CODESP<>\'390\'')
			->where('B.ESTRGM', '=', 1)
			->where('B.TPMRGM', '=', 1)
			->groupBy('A.CODESP, A.DESESP')
			->orderBy('A.DESESP')
			->getAll('array');
		
		$this->aEspecialidadesOrdenHos = $laParams;	
		return $this->aEspecialidadesOrdenHos;
		unset($laParams);
	}
	
	public function ObtenerMedicosOrdenHospitaliza($tcCodigoEspec='')
	{
		$lcWhere = "ESTRGM=1 AND REGMED IN (SELECT REGTUS FROM MEDTUS WHERE ESPTUS='{$tcCodigoEspec}' AND TUSTUS IN (1,6,3))";
		$lbActivo = true;
		$lcOrden = 'NOMMED';
		$lnLimite = 0;
		$tbOtrasConsultas = false;

		require_once __DIR__ . '/class.Usuarios.php';
		$laParams = (new Usuarios($lcWhere, $lbActivo, $lcOrden, $lnLimite, $tbOtrasConsultas))->aUsuarios;

		foreach ($laParams as $laPar) {
			$this->aMedicosOrdenHos[$laPar['REGISTRO']] = [
				'desc'=>$laPar['NOMBRE'],
			];
		}
		unset($laParams);

		return $this->aMedicosOrdenHos;
	}
	
	public function ObtenerAreaHospitaliza()
	{
		$laParams = $this->oDb
			->select('trim(SUBSTR(trim(DE2TMA),1,30)) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'EVOLUC')
			->where('CL1TMA', '=', 'HABHOS')
			->where('ESTTMA', '=', ' ')
			->groupBy('DE2TMA')
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aAreaOrdenHos[$laPar['DESCRIPCION']] = [$laPar['DESCRIPCION'],];
			}
		}
		return $this->aAreaOrdenHos;
		unset($laParams);
	}
	
	public function ObtenerUbicacionOrdenHospitaliza($tcCodigoEnvia='')
	{
		$tcCodigoEnvia=$tcCodigoEnvia=='TRASLADO' ? 'TRASLADO CIRUGIA' : $tcCodigoEnvia;
		$laParams = $this->oDb
			->select('trim(CL2TMA) AS CODIGO, trim(SUBSTR(OP5TMA,1,30)) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'EVOLUC')
			->where('CL1TMA', '=', 'HABHOS')
			->where('DE2TMA', '=', $tcCodigoEnvia)
			->where('ESTTMA', '=', ' ')
			->orderBy('OP5TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aUbicacionOrdenHos[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
					];
			}
		}
		return $this->aUbicacionOrdenHos;
		unset($laParams);
	}

	public function validacion($datosOrdenHospitalizacion=[]){
		$laRetornar = [
		'Mensaje'=>'',
		'Objeto'=>'buscarProcedimiento',
		'Valido'=>true,
		];
		$lbRevisar = true;
		
		if (isset($datosOrdenHospitalizacion)) {
			
			if(empty($datosOrdenHospitalizacion['EstadoOrden'])){
				$this->aError = [
					'Mensaje'=>'No existe TIPO ESTADO en orden de hospitalización',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}	
			
			if(empty($datosOrdenHospitalizacion['EspecialidadOrden'])){
				$this->aError = [
					'Mensaje'=>'No existe ESPECIALIDAD en orden de hospitalización',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}	
			
			if(empty($datosOrdenHospitalizacion['medicoOrden'])){
				$this->aError = [
					'Mensaje'=>'No existe REGISTRO MÉDICO en orden de hospitalización',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}	
			
			if(empty($datosOrdenHospitalizacion['AreaTrasladar'])){
				$this->aError = [
					'Mensaje'=>'No existe AREA A TRASLADAR en orden de hospitalización',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}	
			
			if(empty($datosOrdenHospitalizacion['selUbicacionTrasladar'])){
				$this->aError = [
					'Mensaje'=>'No existe UBICACIÓN en orden de hospitalización',
					'Objeto'=>'selConductaSeguir',
					'Valido'=>false,
				];
			}	
			
			if(!empty($datosOrdenHospitalizacion['EstadoOrden'])){
				$loObjHC = new OrdenHospitalizacion();
				$loObjHC->ObtenerEstadosOrdenHospitaliza();
				$laResultado = $loObjHC->tipoEstadoOrdenHospitalizacion($datosOrdenHospitalizacion['EstadoOrden']);
				if(empty($laResultado)){
					$this->aError = [
						'Mensaje'=>'No existe tipo estado de orden de hospitalización en la base de datos',
						'Objeto'=>'selConductaSeguir',
						'Valido'=>false,
					];
				}
			}
			
			if(!empty($datosOrdenHospitalizacion['EspecialidadOrden'])){
					$loObjHC->ObtenerEspecialidadesOrdenHospitaliza();
					$laResultado = $loObjHC->tipoEspecialidadOrdenHos($datosOrdenHospitalizacion['EspecialidadOrden']);
					if(empty($laResultado)){
						$this->aError = [
							'Mensaje'=>'No existe especialidad de orden de hospitalización en la base de datos',
							'Objeto'=>'selConductaSeguir',
							'Valido'=>false,
						];
					}
				}

				if(!empty($datosOrdenHospitalizacion['medicoOrden'])){
					$loObjHC->ObtenerMedicosOrdenHospitaliza($datosOrdenHospitalizacion['EspecialidadOrden']);
					$laResultado = $loObjHC->tipoMedicoOrdenHos($datosOrdenHospitalizacion['medicoOrden']);
					if(empty($laResultado)){
						$this->aError = [
							'Mensaje'=>'No existe médico de orden de hospitalización en la base de datos',
							'Objeto'=>'selConductaSeguir',
							'Valido'=>false,
						];
					}
				}

				if(!empty($datosOrdenHospitalizacion['AreaTrasladar'])){
					$loObjHC->ObtenerAreaHospitaliza();
					$laResultado = $loObjHC->tipoAreaOrdenHos($datosOrdenHospitalizacion['AreaTrasladar']);
					if(empty($laResultado)){
						$this->aError = [
							'Mensaje'=>'No existe Área de orden de hospitalización en la base de datos',
							'Objeto'=>'selConductaSeguir',
							'Valido'=>false,
						];
					}
				}

				if(!empty($datosOrdenHospitalizacion['selUbicacionTrasladar'])){
					$loObjHC->ObtenerUbicacionOrdenHospitaliza($datosOrdenHospitalizacion['AreaTrasladar']);
					$laResultado = $loObjHC->tipoUbicacionOrdenHos($datosOrdenHospitalizacion['selUbicacionTrasladar']);
					if(empty($laResultado)){
						$this->aError = [
							'Mensaje'=>'No existe Ubicación de orden de hospitalización en la base de datos',
							'Objeto'=>'selConductaSeguir',
							'Valido'=>false,
						];
					}
				}
		}		
		return $laRetornar;	
	}
		
	public function guardarOrdenHospitalizacion($taDetalleOrdenHospitalizacion=[],$tnNroIngreso=0,$tnNroIdentificacion=0,$tcCiePrincipal='',$tcUsuarioCrea='',$tcProgramaCrea='',$tnFechaCrea=0,$tnHoraCrea=0){
		$laWhere=['INGOHO' => $tnNroIngreso,];
		if (is_array($taDetalleOrdenHospitalizacion) && !empty($taDetalleOrdenHospitalizacion)){
			$lbInsertar = true;
			$lnFechaOrden = $tnFechaCrea;
			$lnHoraOrden = $tnHoraCrea;
			$lcTablaGuarda = 'ORDHOS';
			$laReg = $this->oDb->tabla($lcTablaGuarda)->where($laWhere)->get('array');
			if (is_array($laReg)) {
				if (count($laReg)>0) {
					$lbInsertar = false;
					$lcEstadoActual = $laReg['ESTOHO'];
					$lnFechaOrden = $laReg['FOROHO'];
					$lnHoraOrden = $laReg['HOROHO'];
				} 
			}

			$tcCiePrincipal=isset($tcCiePrincipal) ? trim($tcCiePrincipal) : '';
			$lcEstadoOrden=isset($taDetalleOrdenHospitalizacion['EstadoOrden']) ? trim($taDetalleOrdenHospitalizacion['EstadoOrden']) : '';
			$lcAreaOrden=isset($taDetalleOrdenHospitalizacion['AreaTrasladar']) ? trim($taDetalleOrdenHospitalizacion['AreaTrasladar']) : '';
			$lcUbicacionOrden=isset($taDetalleOrdenHospitalizacion['selUbicacionTrasladar']) ? trim($taDetalleOrdenHospitalizacion['selUbicacionTrasladar']) : '';
			$lcEspecialidadOrden=isset($taDetalleOrdenHospitalizacion['EspecialidadOrden']) ? trim($taDetalleOrdenHospitalizacion['EspecialidadOrden']) : '';
			$lcMedicoOrden=isset($taDetalleOrdenHospitalizacion['medicoOrden']) ? trim($taDetalleOrdenHospitalizacion['medicoOrden']) : '';
			$lcJustificacionOrden=isset($taDetalleOrdenHospitalizacion['JustificacionordenHos']) ? trim($taDetalleOrdenHospitalizacion['JustificacionordenHos']) : '';

			if($lbInsertar == false){
				$lcEstadosSalida = $this->oDb->obtenerTabMae1('TRIM(DE2TMA)','EVOLUC',['CL1TMA'=>'ESTSALI','ESTTMA'=>''],null,'');
				$laEstadosSalida = explode(',', str_replace("'", '', $lcEstadosSalida));
			
				if (!in_array($lcEstadoActual, $laEstadosSalida)) {
					$lcEstadoOrden = $lcEstadoActual;
				}
			}
			
			//	GUARDA ORDHOS
			$laData=[
				'INGOHO' => $tnNroIngreso,
				'ESTOHO' => $lcEstadoOrden,
				'DIAOHO' => $tcCiePrincipal,
				'AREOHO' => $lcAreaOrden,
				'UBIOHO' => $lcUbicacionOrden,
				'FOROHO' => $lnFechaOrden,
				'HOROHO' => $lnHoraOrden,
				'ESPOHO' => $lcEspecialidadOrden,
				'REGOHO' => $lcMedicoOrden,
			];
			$laDataI=[
				'UCROHO' => $tcUsuarioCrea,
				'PCROHO' => $tcProgramaCrea,
				'FCROHO' => $tnFechaCrea,
				'HCROHO' => $tnHoraCrea,
			];
			$laDataU=[
				'DIAOHO' => $tcCiePrincipal,
				'AREOHO' => $lcAreaOrden,
				'UBIOHO' => $lcUbicacionOrden,
				'ESPOHO' => $lcEspecialidadOrden,
				'REGOHO' => $lcMedicoOrden,
				'UMOOHO' => $tcUsuarioCrea,
				'PMOOHO' => $tcProgramaCrea,
				'FMOOHO' => $tnFechaCrea,
				'HMOOHO' => $tnHoraCrea,
			];

			try {
				if ($lbInsertar) {
					$this->oDb->tabla($lcTablaGuarda)->insertar(array_merge($laData,$laDataI));
				} else {
					$this->oDb->tabla($lcTablaGuarda)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
				}
			} catch(Exception $loError){
				$laErrores[] = $loError->getMessage();
			} catch(PDOException $loError){
				$laErrores[] = $loError->getMessage();
			}
			
			//	GUARDA ORDHOD
			$lnConsecutivo = 0;
			$laConsecutivo = $this->oDb->max('CONOHD', 'MAXIMO')->from('ORDHOD')->where('INGOHD', '=', $tnNroIngreso)->get("array");
			if(is_array($laConsecutivo)==true){
				if(count($laConsecutivo)>0){
					$lnConsecutivo = $laConsecutivo['MAXIMO']; settype($lnConsecutivo,'integer');
				}
			}			
			$lnConsecutivo = $lnConsecutivo+1;
			if ($lnConsecutivo>0) {
				$lnLinea = 1;
				$lcTablaGuarda = 'ORDHOD';
				$lnLongitud = 220;
				$laTextos = AplicacionFunciones::mb_str_split(trim($lcJustificacionOrden), $lnLongitud);
				if (is_array($laTextos) && !empty($laTextos)) {
					foreach($laTextos as $lcTexto) {
						$laData=[
							'INGOHD' => $tnNroIngreso,
							'CONOHD' => $lnConsecutivo,
							'CLIOHD' => $lnLinea++,
							'ESTOHD' => $lcEstadoOrden,
							'DIAOHD' => $tcCiePrincipal,
							'AREOHD' => $lcAreaOrden,
							'UBIOHD' => $lcUbicacionOrden,
							'ESPOHD' => $lcEspecialidadOrden,
							'REGOHD' => $lcMedicoOrden,
							'DESOHD' => $lcTexto,
							'UCROHD' => $tcUsuarioCrea,
							'PCROHD' => $tcProgramaCrea,
							'FCROHD' => $tnFechaCrea,
							'HCROHD' => $tnHoraCrea,
						];
						try {
							$this->oDb->tabla($lcTablaGuarda)->insertar($laData);
						} catch(Exception $loError){
							$laErrores[] = $loError->getMessage();
						} catch(PDOException $loError){
							$laErrores[] = $loError->getMessage();
						}
					}
				}
			}
			
			//	GUARDA RIAINGT
			$lcTablaGuarda = 'RIAINGT';
			$laWhere=['NIGINT' => $tnNroIngreso,];
			$laData=[
				'NIDINT' => $tnNroIdentificacion,
				'NIGINT' => $tnNroIngreso,
				'DPTINT' => $lcEspecialidadOrden,
				'MEDINT' => intval($lcMedicoOrden),
				'DINGNT' => $tcCiePrincipal,
			];
			$laDataI=[
				'USRINT' => $tcUsuarioCrea,
				'PGMINT' => $tcProgramaCrea,
				'FECINT' => $tnFechaCrea,
				'HORINT' => $tnHoraCrea,
			];
			$laDataU=[
				'DPTINT' => $lcEspecialidadOrden,
				'MEDINT' => intval($lcMedicoOrden),
				'DINGNT' => $tcCiePrincipal,
				'UMOINT' => $tcUsuarioCrea,
				'PMOINT' => $tcProgramaCrea,
				'FMOINT' => $tnFechaCrea,
				'HMOINT' => $tnHoraCrea,
			];
			
			try {
				$lbInsertar = true;
				$laReg = $this->oDb->tabla($lcTablaGuarda)->where($laWhere)->get('array');
				if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

				if ($lbInsertar) {
					$this->oDb->tabla($lcTablaGuarda)->insertar(array_merge($laData,$laDataI));
				} else {
					$this->oDb->tabla($lcTablaGuarda)->where($laWhere)->actualizar(array_merge($laData,$laDataU));
				}
			} catch(Exception $loError){
				$laErrores[] = $loError->getMessage();
			} catch(PDOException $loError){
				$laErrores[] = $loError->getMessage();
			}

			//	GUARDA RIAINGTD
			$lnConsecutivoLinea = 1;			
			$lcTablaGuarda = 'RIAINGTD';
			$laWhere=['INGDME' => $tnNroIngreso, 'SECDME' => '', 'CAMDME' => '', 'MEDDME' => intval($lcMedicoOrden), 'FTRDME' => $tnFechaCrea, 'HTRDME' => $tnHoraCrea, 'CNSDME' => $lnConsecutivoLinea, 'CLIDME' => $lnConsecutivoLinea,];
			$laData=[
				'INGDME' => $tnNroIngreso,
				'MEDDME' => intval($lcMedicoOrden),
				'FTRDME' => $tnFechaCrea,
				'HTRDME' => $tnHoraCrea,
				'CNSDME' => $lnConsecutivoLinea,
				'CLIDME' => $lnConsecutivoLinea,
				'DINDME' => $tcCiePrincipal,
				'USRDME' => $tcUsuarioCrea,
				'PGMDME' => $tcProgramaCrea,
				'FECDME' => $tnFechaCrea,
				'HORDME' => $tnHoraCrea,
			];
			
			try {
				$lbInsertar = true;
				$laReg = $this->oDb->tabla($lcTablaGuarda)->where($laWhere)->get('array');
				if (is_array($laReg)) if (count($laReg)>0) $lbInsertar = false;

				if ($lbInsertar) {
					$this->oDb->tabla($lcTablaGuarda)->insertar($laData);
				}
			} catch(Exception $loError){
				$laErrores[] = $loError->getMessage();
			} catch(PDOException $loError){
				$laErrores[] = $loError->getMessage();
			}
		}	
	}

	public function guardarOrdenAval($taDetalleOrdenHospitalizacion=[],$tnNroIngreso=0,$tnNroIdentificacion=0,$tcCiePrincipal='',$tcUsuarioCrea='',$tcProgramaCrea='',$tnFechaCrea=0,$tnHoraCrea=0, $tnConCon=0)
	{
		$lcEstadoOrden=isset($taDetalleOrdenHospitalizacion['EstadoOrden']) ? trim($taDetalleOrdenHospitalizacion['EstadoOrden']) : '';
		$lcAreaOrden=isset($taDetalleOrdenHospitalizacion['AreaTrasladar']) ? trim($taDetalleOrdenHospitalizacion['AreaTrasladar']) : '';
		$lcUbicacionOrden=isset($taDetalleOrdenHospitalizacion['selUbicacionTrasladar']) ? trim($taDetalleOrdenHospitalizacion['selUbicacionTrasladar']) : '';
		$lcEspecialidadOrden=isset($taDetalleOrdenHospitalizacion['EspecialidadOrden']) ? trim($taDetalleOrdenHospitalizacion['EspecialidadOrden']) : '';
		$lcMedicoOrden=isset($taDetalleOrdenHospitalizacion['medicoOrden']) ? trim($taDetalleOrdenHospitalizacion['medicoOrden']) : '';
		$lcJustificacionOrden=isset($taDetalleOrdenHospitalizacion['JustificacionordenHos']) ? trim($taDetalleOrdenHospitalizacion['JustificacionordenHos']) : '';
		$lcTabla='ORDHIS';
		$laDatos=[
			'INGHIS' => $tnNroIngreso,
			'CONHIS' => $tnConCon,
			'ESTHIS' => $lcEstadoOrden, 
			'DIAHIS' => $tcCiePrincipal, 
			'AREHIS' => $lcAreaOrden,
			'UBIHIS' => $lcUbicacionOrden,
			'FORHIS' => $tnFechaCrea,
			'HORHIS' => $tnHoraCrea,
			'ESPHIS' => $lcEspecialidadOrden,
			'REGHIS' => $lcMedicoOrden,
			'OBSHIS' => $lcJustificacionOrden,
			'UCRHIS' => $tcUsuarioCrea, 
			'PCRHIS' => $tcProgramaCrea, 
			'FCRHIS' => $tnFechaCrea,
			'HCRHIS' => $tnHoraCrea,
			];
		$this->oDb->tabla($lcTabla)->insertar($laDatos);
	}
	
	public function verificarExisteOH($tnIngreso=0)
	{
		$laTemp = $this->oDb
			->select('TRIM(A.ESPOHO) ESPEC, TRIM(A.REGOHO) REGISTRO, TRIM(A.AREOHO) AREA, TRIM(A.UBIOHO) UBICA, TRIM(MC.NOMMED) AS NOMMEDCRE, TRIM(MC.NNOMED) AS NNOMEDCRE, TRIM(E.DESESP) AS NOMESPEC')
			->from('ORDHOSL01 AS A')
			->leftJoin('RIARGMN5 AS MC', 'A.REGOHO=MC.REGMED', null)
			->leftJoin('RIAESPE AS E', 'A.ESPOHO=E.CODESP', null)
			->where('A.INGOHO', '=', $tnIngreso)
			->orderBy('A.FCROHO DESC')
			->get('array');
		if($this->oDb->numRows()>0){
			$laTemp['EXISTE'] = true;
		} else {
			$laTemp=['EXISTE'=>false];
		}
		return $laTemp;
	}
	
	//	Tipos orden hospitalización
	public function tipoEstadoOrdenHospitalizacion($tcCodigo='')
	{
		return $this->aEstadoOrdenHospitaliza[$tcCodigo] ?? '';
	}
	
	public function tipoEspecialidadOrdenHos($tcCodigo='')
	{
		return $this->aEspecialidadesOrdenHos[$tcCodigo] ?? '';
	}
	
	public function tipoMedicoOrdenHos($tcCodigo='')
	{
		return $this->aMedicosOrdenHos[$tcCodigo] ?? '';
	}
	
	public function tipoAreaOrdenHos($tcCodigo='')
	{
		return $this->aAreaOrdenHos[$tcCodigo] ?? '';
	}
	
	public function tipoUbicacionOrdenHos($tcCodigo='')
	{
		return $this->aUbicacionOrdenHos[$tcCodigo] ?? '';
	}
	
}
