<?php
namespace NUCLEO;

require_once ('class.Db.php');
require_once ('class.FormulacionParametros.php');
require_once ('class.Diagnostico.php');
require_once ('class.DatosAmbulatorios.php');
require_once ('class.Consecutivos.php');

use NUCLEO\Db;
use NUCLEO\FormulacionParametros;
use NUCLEO\Diagnostico;
use NUCLEO\DatosAmbulatorios;
use NUCLEO\Consecutivos;

class ParametrosConsulta
{
	protected $aTipoCausa = [];
	protected $aConductaSeguir = [];
	protected $aModalidadGrupoServicio = [];
	protected $aCondicionDestinoEgreso = [];
	protected $aFinalidad = [];
	protected $aEstadoEpicrisis = [];
	protected $aEstadoSalida = [];
	protected $aobjOblHC = [];
	protected $aobjOblOA = [];
	protected $aobjOblEV = [];
	protected $aobjNoVisible = [];
	protected $aobjNoVisibleEV = [];
	protected $aobjOblEPI = [];
	protected $aDolorToracico = [];
	protected $aComplicacionesUci = [];
	protected $aParametrosUci = [];
	protected $aGrupoMedicamentoUci = [];
	protected $aMedicamentosUci = [];
	protected $aRetorno = [];
	protected $aGrupoMedica = [];
	protected $oDb;

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}

	// Obtiene todos los parámetros
	public function obtenerParametrosTodos()
	{
		$this->ObtenerTipoCausa();
		$this->ObtenerConductaSeguir();
		$this->ObtenerModalidadGrupoServicio();
		$this->ObtenerFinalidad();
		$this->ObtenerEstadoEpicrisis();
		$this->ObtenerEstadoSalida();
		$this->ObtenerComplicacionesUci();
		$this->ObtenerParametrosUci();
		$this->ObtenerGruposMedicamentosUci();
		$this->ObtenerMedicamentosUci();
	}

	public function ObtenerTipoCausa($tcViaIngreso='', $tcOrigen='', $tcCodigo='')
	{
		$lcTipo = $this->oDb->obtenerTabmae1('DE2TMA', 'GENRIPS', "CL1TMA='CAUTIPO' AND CL2TMA='$tcViaIngreso' AND ESTTMA=''", null, '');
		$lcTipo=!empty($lcTipo)?trim($lcTipo):'U';

		if (strlen($tcOrigen)>0)
			$this->oDb->where(['OP1TMA'=>$tcOrigen]);
		if (strlen($tcCodigo)>0)
			$this->oDb->where(['CL1TMA'=>$tcCodigo]);

		$laParams = $this->oDb
			->select('TRIM(CL1TMA) AS CODIGO, TRIM(CL1TMA) AS COD_IOHC, TRIM(SUBSTR(DE2TMA,1,120)) AS DESCRIPCION, OP3TMA ORDEN, OP1TMA ORIGEN')
			->from('TABMAE')
			->where("TIPTMA='CODCEX' AND ESTTMA<>'1'")
			->where("OP2TMA like '%$lcTipo%'")
			->orderBy('OP3TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aTipoCausa[$laPar['ORDEN']] = [
					'codigo'=>$laPar['CODIGO'],
					'codigoIOHC'=>$laPar['COD_IOHC'],
					'desc'=>$laPar['DESCRIPCION'],
					'origen'=>$laPar['ORIGEN'],
				];
			}
		}
		unset($laParams);
	}

	public function ObtenerConductaSeguir($tnIngreso=0, $tcVia='', $tcSeccion='', $tcPrograma='', $tcConducta='')
	{
		$lcEstaSalidaOrden = $this->fEstaSalidaOrden($tnIngreso);
		$lcEsUrgencias = $this->fPacienteUrgencias($tnIngreso, $tcVia, $tcSeccion);
		$lcSinConducta=isset($tcConducta) ? trim($tcConducta):'';

		$tcLetra = '';
		$tcPrograma = empty($tcPrograma)?'HC':$tcPrograma;

		switch($tcPrograma){
			case 'URGEN':
				$tcLetra = 'G';
				break;
			case 'HC':
				$tcLetra = ($tcVia=='05' || $tcVia=='06')?'P':'U';
				break;
			case 'EVPISO':
				$tcSeccion = (!empty($lcEsUrgencias)?'':(!empty($lcEstaSalidaOrden)?'ESTSAL':$tcSeccion));
				$tcSeccion = $this->oDb->obtenerTabMae1('DE2TMA', 'CONDUCTA', "CL1TMA='VIASIN' AND CL2TMA='$tcVia' AND ESTTMA=''", null, $tcSeccion);
				$tcLetra = $this->oDb->obtenerTabMae1('DE2TMA', 'CONDUCTA', "CL1TMA='VIAING' AND CL2TMA='$tcVia' AND CL3TMA='$tcSeccion' AND ESTTMA=''", null, 'X');
				break;
			case 'EVUNID':
				$tcSeccion = $this->oDb->obtenerTabMae1('DE2TMA', 'CONDUCTA', "CL1TMA='VIASIN' AND CL2TMA='$tcVia' AND ESTTMA=''", null, $tcSeccion);
				$tcLetra = $this->oDb->obtenerTabMae1('DE2TMA', 'CONDUCTA', "CL1TMA='VIAING' AND CL2TMA='$tcVia' AND CL3TMA='$tcSeccion' AND ESTTMA=''", null, 'X');
				break;
		}

		if (!empty($lcSinConducta)){
			$this->oDb->where("(OP6TMA<>'C')");
		}

		$laConductaSeguir = $this->oDb
			->select('trim(CL1TMA) AS CODIGO, trim(SUBSTR(DE1TMA,1,40)) AS DESCRIPCION, OP3TMA ORDEN')
			->from('TABMAE')
			->where('TIPTMA', '=', 'CNDSGR')
			->where('ESTTMA', '<>', '1')
			->where("OP2TMA like '%$tcLetra%'")
			->orderBy('OP3TMA')
			->getAll('array');
		if (is_array($laConductaSeguir)) {
			foreach($laConductaSeguir as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aConductaSeguir[$laPar['CODIGO']] = [
					'desc'=>$laPar['DESCRIPCION'],
				];
			}
		}
		unset($laConductaSeguir);
	}

	// FINALIDAD
	public function ObtenerFinalidad($tcTipo='', $tcDatos='')
	{
		$lcTipo=empty($tcTipo)?'':$tcTipo;

		if (!empty($tcDatos)){
			$tcSexo = $tcDatos['genero'];
			$lcEdadAño = intval($tcDatos['edad']['y']);
			$lcEdadMeses = intval($tcDatos['edad']['m']);
			$lcEdadDia = intval($tcDatos['edad']['d']);
			$lcEdadDia=($lcEdadAño==0 && $lcEdadMeses==0 && $lcEdadDia==0)?1:$lcEdadDia;
			$lnEdad = 0;
			
			if ($lcEdadAño>0){
				$lnEdad = 400 + $lcEdadAño;
			} else {
				if ($lcEdadMeses>0){
					$lnEdad = 300 + $lcEdadMeses;
				} else {
					if ($lcEdadDia>0){
						$lnEdad = 200 + $lcEdadDia;
					}
				}
			}

			if (!empty($lnEdad)) {
				$this->oDb->where("((OP3TMA=0 AND OP7TMA=0) OR ($lnEdad BETWEEN OP3TMA AND OP7TMA))");
			}

			if (!empty($tcSexo)) {
				$laFiltroGenero = $tcSexo=='M'? ['A','M'] : ['A','F'];
				$this->oDb->in('OP6TMA', $laFiltroGenero);
			} 
		}	
		$laParams = $this->oDb
			->select('TRIM(CL1TMA) CODIGO, TRIM(SUBSTR(DE2TMA,1,80)) DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'CODFIN')
			->where('ESTTMA', '<>', '1')
			->where("OP2TMA like '%$lcTipo%'")
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aFinalidad[$laPar['CODIGO']] = [
					'desc'=>$laPar['DESCRIPCION'],
				];
			}
		}
		unset($laParams);
	}

	public function ObtenerModalidadGrupoServicio()
	{
		$laParams = $this->oDb
			->select('CL2TMA AS CODIGO, SUBSTR(DE2TMA,1,40) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA=\'GENRIPS\' AND CL1TMA=\'MODATEN\' AND ESTTMA<>\'1\'')
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aModalidadGrupoServicio[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
					];
			}
		}
		unset($laParams);
	}

	public function ObtenerCondicionDestinoEgreso($tcEstado='')
	{
		$lcEstado=empty($tcEstado)?'E':$tcEstado;

		$laParams = $this->oDb
			->select('CL2TMA AS CODIGO, SUBSTR(DE2TMA,1,70) AS DESCRIPCION')
			->from('TABMAE')
			->where('TIPTMA', '=', 'GENRIPS')
			->where('CL1TMA', '=', 'CONUSEGR')
			->where('OP1TMA', '=', $lcEstado)
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aCondicionDestinoEgreso[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
					];
			}
		}
		unset($laParams);
	}

	public function ObtenerEstadoEpicrisis()
	{
		// ESTADO PACIENTE EPICRISIS
		$laParams = $this->oDb
			->select('CL2TMA AS CODIGO, SUBSTR(DE1TMA,1,30) AS DESCRIPCION, OP1TMA AS TIPO')
			->from('TABMAE')
			->where('TIPTMA=\'DATING\' AND CL1TMA=\'4\' AND ESTTMA<>\'1\'')
			->orderBy('DE2TMA')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aEstadoEpicrisis[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
						'tipo'=>$laPar['TIPO'],
					];
			}
		}
		unset($laParams);
	}

	public function ObtenerEstadoSalida()
	{
		// ESTADO SALIDA
		$laParams = $this->oDb
			->select('TABCOD AS CODIGO, TABDSC AS DESCRIPCION')
			->from('PRMTAB')
			->where('TABTIP=\'ESL\' AND TABCOD<>\' \'')
			->orderBy('TABDSC')
			->getAll('array');
		if (is_array($laParams)) {
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aEstadoSalida[$laPar['CODIGO']] = [
						'desc'=>$laPar['DESCRIPCION'],
					];
			}
		}
		unset($laParams);
	}

	public function menuHC()
	{
		$laMenu = $this->oDb
			->select('TRIM(DE1TMA) AS TEXTO, TRIM(DE2TMA) AS CONDICION, TRIM(OP5TMA) AS OBJETOS')
			->from('TABMAE')
			->where(['TIPTMA'=>'PARHCWEB', 'CL1TMA'=>'MENUPRIN', 'ESTTMA'=>''])
			->orderBy('OP3TMA')
			->getAll('array');

		return is_array($laMenu)? $laMenu: [];
	}

	public function consultaMedicosTraslados()
	{
		$lcTiposMedicos=$this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='TMDTRAS' AND ESTTMA=''", null, '');
		return $lcTiposMedicos;
	}
	
	public function rangopesoreciennacido()
	{
		$lcRangosRecien=$this->oDb->obtenerTabmae('DE2TMA', 'FORMEDIC', "CL1TMA='RGRECNAC' AND ESTTMA=''", null, '');
		return $lcRangosRecien;
	}

	public function cantidadJustificacionTraslado()
	{
		$lnCantidadCaracteres=intval(trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='CANJUSTR' AND ESTTMA=''", null, '400')));
		return $lnCantidadCaracteres;
	}

	public function valorMaximoNews()
	{
		$lnMaximoNews=intval(trim($this->oDb->obtenerTabmae1('DE2TMA', 'FORMEDIC', "CL1TMA='MAXNEWS' AND ESTTMA=''", null, '6')));
		return $lnMaximoNews;
	}

	public function menuCN()
	{
		$laMenu = $this->oDb
			->select('TRIM(DE1TMA) AS TEXTO, TRIM(DE2TMA) AS CONDICION, TRIM(OP5TMA) AS OBJETOS')
			->from('TABMAE')
			->where(['TIPTMA'=>'PARCNWEB', 'CL1TMA'=>'MENUPRIN', 'ESTTMA'=>''])
			->orderBy('OP3TMA')
			->getAll('array');

		return is_array($laMenu)? $laMenu: [];
	}

	public function ObjetosNoVisibles()
	{
		// Listado de los campos obligatorios en HC WEB
		$laCondiciones = ['TIPTMA'=>'PARHCWEB', 'CL1TMA'=>'OBJNVISI', 'ESTTMA'=>' '];

		$this->aobjNoVisible = $this->oDb
			->select('TRIM(DE1TMA) AS OBJETOS, TRIM(DE2TMA) AS CONDICION')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('OP3TMA')
			->getAll('array');
	}

	public function ObjetosObligatoriosHC($tcTitulo='')
	{
		// Listado de los campos obligatorios en HC WEB
		$laCondiciones = ['TIPTMA'=>'PARHCWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblHC = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('OP3TMA')
			->getAll('array');
	}

	public function ObjetosObligatoriosOA($tcTitulo='')
	{
		$laCondiciones = ['TIPTMA'=>'PAROAWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblOA = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('OP3TMA')
			->getAll('array');
	}

	public function getDolor_Toracico()
	{
		$laCampos = ['CL1TMA', 'CL2TMA', 'CL3TMA', 'DE1TMA',
					 'DE2TMA', 'OP1TMA', 'OP2TMA', 'OP4TMA'];
		$laCondiciones = ['TIPTMA'=>'TORACICO', 'ESTTMA'=>' '];
		$laDoloresToracico = $this->oDb
						->select($laCampos)
						->from('TABMAE')
						->where($laCondiciones)
						->orderBy('INT(OP3TMA)')
						->getAll('array');
		if(is_array($laDoloresToracico) == true){
			foreach($laDoloresToracico as $laDolorToracico){
				$laDolorToracico = array_map('trim', $laDolorToracico);
				$this->aDolorToracico[$laDolorToracico['CL2TMA']] = array(
					'TIPO'=>$laDolorToracico['CL1TMA'],
					'CODIGO'=>$laDolorToracico['CL2TMA'],
					'NIVEL'=>intval($laDolorToracico['CL3TMA']),
					'NOMBRE'=>$laDolorToracico['DE1TMA'],
					'DESCRIP'=>$laDolorToracico['DE2TMA'],
					'VALOR'=>$laDolorToracico['OP1TMA'],
					'POSANT'=>$laDolorToracico['OP2TMA'],
					'LINEA'=>$laDolorToracico['OP4TMA'],
				);
			}
		}
	}

	public function ObjetosObligatoriosEPI($tcTitulo='')
	{
		// Listado de los campos obligatorios en Epicrisis WEB
		$laCondiciones = ['TIPTMA'=>'PAREPWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblEPI = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('INT(OP3TMA)')
			->getAll('array');
	}

	public function ObtenerComplicacionesUci(){
		if(isset($this->oDb)){
			$laParams = $this->oDb
				->select(['OP3TMA CODIGO','TRIM(DE1TMA) DESCRIPCION'])
				->from('TABMAE')
				->where(['TIPTMA' => 'EVOLUNI','CL1TMA' => 'COMPLICA','ESTTMA' => '',])
				->orderBy('OP3TMA')
				->getAll('array');
			if(is_array($laParams)==true){
				$this->aComplicacionesUci=$laParams;
			}
		}
		unset($laParams);
	}

	public function ObtenerParametrosUci($tcGenero=''){
		if(isset($this->oDb)){
			$laParams = $this->oDb
				->select(['DE1TMA','SPACE(30) CARTMA','0000.0 VALTMA','TRIM(DE2TMA) METTMA','OP3TMA','OP6TMA','OP7TMA','TRIM(OP2TMA) OP2TMA'])
				->from('TABMAE')
				->where(['TIPTMA' => 'EVENTOCV','ESTTMA' => '',])
				->orderBy('OP3TMA')
				->getAll('array');
			if(is_array($laParams)==true){
				foreach($laParams as $laParametro){
					$laParametro['OP2TMA'] = str_replace(' ','_',$laParametro['OP2TMA']);
					$this->aParametrosUci[]=$laParametro;
				}
			}
		}
		unset($laParams);
	}

	public function ObtenerGruposMedicamentosUci(){
		if(isset($this->oDb)){
			$laParams = $this->oDb
				->select(['TRIM(DE1TMA) DE1TMA','TRIM(CL2TMA) CL2TMA','TRIM(CL3TMA) CL3TMA','TRIM(CL4TMA) CL4TMA'])
				->from('TABMAE')
				->where(['TIPTMA' => 'GRUPOCV','ESTTMA' => '',])
				->orderBy('OP3TMA')
				->getAll('array');
			foreach($laParams as $laPar){
				$laPar = array_map('trim',$laPar);
				$this->aGrupoMedicamentoUci[$laPar['CL3TMA']] = $laPar['DE1TMA'];
			}
		}
		unset($laParams);
	}

	public function ObtenerMedicamentosUci($tcCodigoGrupo=''){
		if(isset($this->oDb)){
			$laParams = $this->oDb
				->select(['TRIM(A.DESDES) DESDES','TRIM(A.REFDES) CODMED','TRIM(A.CBADES) CODCUM','TRIM(B.CODATC) CODATC'])
				->from('INVDES A')
				->leftJoin('SIMNCUM B', 'A.CBADES=B.CODCUM', null)
				->where("A.STSDES <> '1' AND (A.TINDES='500' OR A.RF2DES='NUCLEA')")
				->where("B.CODATC like '$tcCodigoGrupo%'")
				->orderBy('A.DESDES')
				->getAll('array');

			if(is_array($laParams)==true){

				$this->ObtenerGruposMedicamentosUci();
				foreach($laParams as $laElemento) {
					$laElemento['CODGRP']='Otros';
					$laElemento['DESGRP']='Otros';

					if(!empty(trim($laElemento['CODATC']))){
						foreach($this->aGrupoMedicamentoUci as $lcKey=>$laGrupo) {
							$lnLongitud = strlen(trim($lcKey));
							if(substr($laElemento['CODATC'],0,$lnLongitud)==trim($lcKey)){
								$laElemento['CODGRP']=trim($lcKey);
								$laElemento['DESGRP']=trim($laGrupo);
								break;
							}
						}
					}
					$this->aMedicamentosUci[$laElemento['CODMED']]=$laElemento;
				}
			}
		}
		unset($laParams);
	}

	public function ObjetosObligatoriosEV($tcTitulo='')
	{
		// Listado de los campos obligatorios en EVOLUCIONES WEB
		$laCondiciones = ['TIPTMA'=>'PAREVWEB', 'CL1TMA'=>'OBJOBLIG', 'ESTTMA'=>' '];
		if(!empty(trim($tcTitulo))){
			$laCondiciones['CL2TMA']=$tcTitulo;
		}
		$this->aobjOblEV = $this->oDb
			->select('TRIM(CL2TMA) AS FORMA, TRIM(DE1TMA) AS OBJETO, TRIM(DE2TMA) AS REGLAS, OP1TMA AS CLASE, TRIM(OP5TMA) AS REQUIERE')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('OP3TMA')
			->getAll('array');
	}

	public function ObjetosNoVisiblesEV()
	{
		// Listado de los campos obligatorios en HC WEB
		$laCondiciones = ['TIPTMA'=>'PAREVWEB', 'CL1TMA'=>'OBJNVISI', 'ESTTMA'=>' '];

		$this->aobjNoVisibleEV = $this->oDb
			->select('TRIM(DE1TMA) AS OBJETOS, TRIM(DE2TMA) AS CONDICION')
			->from('TABMAE')
			->where($laCondiciones)
			->orderBy('OP3TMA')
			->getAll('array');

	}

	public function menuEV($tcTipo='', $tcVia='', $tcSeccion='')
	{
		$lcLetra=$tcTipo;
		if(empty(trim($lcLetra))){
			switch ($tcVia) {
				case '01' :
					$lcLetra='U';
					break;
				case '05' :
					if (substr($tcSeccion,0,1)=='C'){
						$lcLetra= ($tcSeccion=='CC'?'C':'V');	// LETRA PARA USAR EN UNIDADES
					}else{
						$lcLetra='P';
					}
					break;
				default :
					$lcLetra='P';
					break;
			}
		}
		$lnTipoUsuario=0;
		$lnTipoUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario():'');
		$lcPermisos= $this->oDb->obtenerTabMae1('DE2TMA', 'PAREVWEB', "CL1TMA='PERMISOS' AND CL2TMA='INTERPRE' AND ESTTMA=''", null,'');
		$laPermisos = explode(',', str_replace("'", '', $lcPermisos));

		if (!in_array($lnTipoUsuario,$laPermisos)) {
			$lcLetra= trim($this->oDb->obtenerTabMae1('DE2TMA', 'PAREVWEB', "CL1TMA='LETSININ' AND CL2TMA='$lcLetra' AND ESTTMA=''", null,''));
		}

		$laMenuEV = $this->oDb
			->select('TRIM(DE1TMA) AS TEXTO, TRIM(DE2TMA) AS CONDICION, TRIM(OP5TMA) AS OBJETOS')
			->from('TABMAE')
			->where(['TIPTMA'=>'PAREVWEB', 'CL1TMA'=>'MENUPRIN', 'ESTTMA'=>''])
			->where("OP2TMA like '%$lcLetra%'")
			->orderBy('OP3TMA')
			->getAll('array');

		return is_array($laMenuEV)? $laMenuEV: [];
	}

	public function ConsultaAutocompletar($tcConsulta='', $taOtros=[])
	{
		switch ($tcConsulta){

			case 'Medicamentos':
				$loObjeto = new FormulacionParametros();
				$loObjeto->obtenerMedicamentos();
				$laListado = $loObjeto->ListaMedicamentos();
				if(is_array($laListado)){
					if(count($laListado)>0){
						$lnIndice=0;
						foreach($laListado as $laLista){
							$lnIndice=$lnIndice+1;
							$this->aRetorno[trim($laLista['CODMED']).' - '. trim($laLista['DESMED'])] = $lnIndice;
						}
					}
				}
				break;

			case 'DxFallece':
				$loObjeto = new Diagnostico();
				$loObjeto->obtenerDxFallece();
				$laListado = $loObjeto->ListaDxFallece();
				if(is_array($laListado)){
					if(count($laListado)>0){
						$lnIndice=0;
						foreach($laListado as $laLista){
							$lnIndice=$lnIndice+1;
							$this->aRetorno[trim($laLista['CODRIP']).' - '. trim($laLista['DESRIP'])] = $lnIndice;
						}
					}
				}
				break;

			case 'Diagnosticos':
				$loObjeto = new Diagnostico();
				$loObjeto->TablaDiagnosticos($taOtros['fecha'], $taOtros['sexo'], $taOtros['edad']);
				$laListado = $loObjeto->ListadoDiagnosticos();
				if(is_array($laListado)){
					if(count($laListado)>0){
						$lnIndice=0;
						foreach($laListado as $laLista){
							$lnIndice=$lnIndice+1;
							$this->aRetorno[trim($laLista['TABCOD']).' - '. trim($laLista['DESCRIPCION'])] = $lnIndice;
						}
					}
				}
				break;

			case 'Interconsultas':
				$loObjeto = new DatosAmbulatorios();
				$loObjeto->TablaInterconsultas($taOtros['filtro']);
				$laListado = $loObjeto->ListadoInterconsultas();
				if(is_array($laListado)){
					if(count($laListado)>0){
						$lnIndice=0;
						foreach($laListado as $laLista){
							$lnIndice=$lnIndice+1;
							$this->aRetorno[trim($laLista['CODIGO']).' - '. trim($laLista['DESCRIPCION'])] = $lnIndice;
						}
					}
				}
				break;

			case 'Procedimientos':
				$loObjeto = new DatosAmbulatorios();
				$loObjeto->TablaProcedimientos($taOtros['filtro'],$taOtros['genero']);
				$laListado = $loObjeto->ListadoProcedimientos();
				if(is_array($laListado)){
					$this->aRetorno = $laListado;
				}
				break;

			case 'Insumos':
				$loObjeto = new DatosAmbulatorios();
				$loObjeto->TablaInsumos();
				$laListado = $loObjeto->ListadoInsumos();
				if(is_array($laListado)){
					if(count($laListado)>0){
						$lnIndice=0;
						foreach($laListado as $laLista){
							$lnIndice=$lnIndice+1;
							$this->aRetorno[trim($laLista['CODIGO']).' ~ '. trim($laLista['DESCRIPCION'])] = $lnIndice;
						}
					}
				}
				break;
		}
		return $this->aRetorno ;
	}

	function fEstaSalidaOrden($tngreso=0)
	{
		$lcEstaSalidaOrden = $lcEstadoOrdenActual = '';
		$laEstaSalidaOrden = $this->oDb->select('ESTOHO')->from('ORDHOS')->where(['INGOHO'=>$tngreso,])->get("array");

		if(is_array($laEstaSalidaOrden)){
			if(count($laEstaSalidaOrden)>0){
				$lcEstadoOrdenActual = $laEstaSalidaOrden['ESTOHO'];
			}
		}

		if (!empty($lcEstadoOrdenActual)){
			$lcEstaSalidaOrden = $this->oDb->obtenerTabMae1('CL2TMA', 'EVOLUC', "CL1TMA='ESTORDH' AND CL2TMA='$lcEstadoOrdenActual' AND OP2TMA='S' AND ESTTMA=''", null, '');
		}
		unset($laEstaSalidaOrden);
		return $lcEstaSalidaOrden;
	}

	function fPacienteUrgencias($tnIngreso=0, $tcViaIngreso='', $tcSeccion='')
	{
		$lcUrgencias = ($tcViaIngreso=='01' || ($tcViaIngreso=='05' && $tcSeccion=='TU'))?'UR':'';

		if (empty($lcUrgencias)){
			if ($tcSeccion=='TU'){
				$lapacienteUrgencias = $this->oDb
				->select('INGIUR')
				->from('INGURGTL01')
				->where(['INGIUR'=>$tnIngreso])
				->getAll('array');

				if(is_array($lapacienteUrgencias)){
					if(count($lapacienteUrgencias)>0){
						$lcUrgencias = 'UR';
					}
				}
				unset($lapacienteUrgencias);
			}
		}

		if (empty($lcUrgencias)){
			if ($tcViaIngreso='05'){
				$lapacienteHospitaliza = $this->oDb
					->select('SECHAB')
					->from('FACHAB')
					->where('IDDHAB', '=', '0')
					->where('INGHAB', '=', $tnIngreso)
					->where('LIQHAB', '=', 'U')
					->where("ESTHAB in ('1', '2')")
					->getAll('array');
				if(is_array($lapacienteHospitaliza)){
					if(count($lapacienteHospitaliza)>0){
						$lcUrgencias = 'UR';
					}
				}
				unset($lapacienteHospitaliza);
			}
		}
		return $lcUrgencias;
	}

	public function ConsultarMedicamentosUcc($tnIngreso=0)
	{
		$this->aGrupoMedica = [];
		$lnConsecutivo = Consecutivos::fConsecutivoUltimaFormula($tnIngreso);

		if(!empty($lnConsecutivo)){
			$laMedicaFrc = $this->oDb
				->select('TRIM(A.MEDFRD) CODMEDICA, TRIM(B.DESDES) AS DESMEDICA')
					->from('RIAFARD AS A')
					->leftJoin('INVDES B', ' A.MEDFRD = B.REFDES', null)
					->where([
							'A.NINFRD'=>$tnIngreso,
							'A.CEVFRD'=>$lnConsecutivo,
							])
					->in('A.ESTFRD', [11,12,13,14,15,16])
					->orderBy('A.FEFFRD DESC, A.HMFFRD ASC')
					->getAll('array');

			if(!is_array($laMedicaFrc)){$laMedicaFrc=[];}

			if (count($laMedicaFrc)>0){
				$this->ObtenerGruposMedicamentosUci();
				$this->ObtenerMedicamentosUci();

				foreach($laMedicaFrc as $lnKey=>$laMedica) {

					if (!empty($laMedica['CODMEDICA'])){

						$laMedicaFrc[$lnKey]['DESGRUPOMED'] = '';
						$laMedicaFrc[$lnKey]['INDICADO'] = '';

						foreach($this->aMedicamentosUci as $laMedicaUci) {
							if($laMedicaUci['CODMED'] == $laMedica['CODMEDICA']){
								$lcCodigo = (empty($laMedicaUci['CODGRP']) ? 'Otros' : $laMedicaUci['CODGRP']) ;
								$laMedicaFrc[$lnKey]['CODGRUPMED'] = $lcCodigo ;
								$laMedicaFrc[$lnKey]['DESGRUPOMED'] = $this->aGrupoMedicamentoUci[$lcCodigo];
								break;
							}
						}

					}

				}

			}
			return $laMedicaFrc;
		}
	}

	// Verifica si el ingreso requiere información de
	public function VerificarFA($tnIngreso=0)
	{
		$llRetorno = false;
		$laFibrilacion = $this->oDb
			->select('NINORD')
				->from('RIAORDL26')
				->where([
						'NINORD'=>$tnIngreso,
						'CODORD'=>'130',
						'COAORD'=>'22',
						])
				->getAll('array');


		if(!is_array($laFibrilacion)){$laFibrilacion=[];}
		$llRetorno = (count($laFibrilacion)>0);
		unset($laFibrilacion);
		return $llRetorno;

	}

	// Verifica si el ingreso tiene informacion de Dx de procedimiento en la tabla EVOUNI
	public function VerificarDxProc($tnIngreso=0)
	{
		$llRetorno = false;
		$laDxEvo = $this->oDb
			->select('INGEUN')
				->from('EVOUNI')
				->where([
						'INGEUN'=>$tnIngreso,
						])
				->getAll('array');


		if(!is_array($laDxEvo)){$laDxEvo=[];}
		$llRetorno = (count($laDxEvo)==0);
		unset($laDxEvo);
		return $llRetorno;
	}

	function fMostrarPlanesPaciente($tcTipoParametro='')
	{
		$lcMostrarPlanes = '';
		$lcMostrarPlanes = trim($this->oDb->obtenerTabMae1('DE2TMA', $tcTipoParametro, "CL1TMA='PLANPAC' AND ESTTMA=''", null, ''));

		return $lcMostrarPlanes;
	}

	public function consultarViasCenso()
	{
		$aParametros = [];
		$aParametros=explode(',', $this->oDb->obtenerTabMae1('DE2TMA', 'CENPAC', "CL1TMA='VIACENSO' AND ESTTMA=''", null, ''));
		return $aParametros;
	}

	// Retorna array con opciones de Hospitalizados
	public function opcionesMenu($tcTipoMenu='MENUHOSP')
	{
		$laOpcHosp=[];
		$laTabla = $this->oDb
			->select('TRIM(OP2TMA) CLAVE, TRIM(CL3TMA) LINEA, TRIM(DE1TMA) TITULO, DE2TMA||OP5TMA OPCIONES')
			->from('TABMAE')
			->where([
				'TIPTMA'=>'PARHCWEB',
				'CL1TMA'=>$tcTipoMenu,
				'ESTTMA'=>'',
			])
			->orderBy('CL2TMA ASC, CL3TMA ASC')
			->getAll('array');
		if ($this->oDb->numRows()>0){
			foreach($laTabla as $laFila){
				if(isset($laOpcHosp[$laFila['CLAVE']])){
					$laOpcHosp[$laFila['CLAVE']]['options'].=$laFila['OPCIONES'];
				}else{
					$laOpcHosp[$laFila['CLAVE']]=[
						'title'=>$laFila['TITULO'],
						'options'=>$laFila['OPCIONES'],
					];
				}
			}
			foreach($laOpcHosp as $lcClave=>$laOpc){
				$laOpcHosp[$lcClave]['options']=json_decode(trim($laOpc['options']),true);
			}
		}
		return $laOpcHosp;
	}


	// Retorna array con parámetros para generar antecedentes de vacuna Covid
	public function parVacunaCovid19()
	{
		$laParCov19=$laTipos=[];
		$laTabla = $this->oDb
			->select('TRIM(CL2TMA) CLAVE, TRIM(CL3TMA) LINEA, DE2TMA||OP5TMA DATO, OP1TMA TIPO')
			->from('TABMAE')
			->where("TIPTMA='COVID19' AND CL1TMA='VACANTEC' AND ESTTMA=''")
			->orderBy('CL2TMA ASC, CL3TMA ASC')
			->getAll('array');
		if ($this->oDb->numRows()>0){
			foreach($laTabla as $laFila){
				$laTipo[$laFila['CLAVE']]=$laFila['TIPO'];
				if(isset($laParCov19[$laFila['CLAVE']])){
					$laParCov19[$laFila['CLAVE']].=$laFila['DATO'];
				}else{
					$laParCov19[$laFila['CLAVE']]=$laFila['DATO'];
				}
			}
			$laParCov19=array_map('trim',$laParCov19);
			foreach($laTipo as $lcClave=>$lcTipo){
				if($lcTipo=='J'){
					$laParCov19[$lcClave]=json_decode($laParCov19[$lcClave],true);
				}
			}
		}
		return $laParCov19;
	}


	//	Retorna array con los Tipos de Causa
	public function TiposCausa()
	{
		return $this->aTipoCausa;
	}

	//	Retorna array con los Tipos de conducta a seguir
	public function ConductasSeguir()
	{
		return $this->aConductaSeguir;
	}

	//	Retorna array con los Tipos de Finalidad
	public function Finalidades()
	{
		return $this->aFinalidad;
	}

	//	Retorna array con los Tipos modalidad grupo servicio
	public function ModalidadGrupoServicio()
	{
		return $this->aModalidadGrupoServicio;
	}

	//	Retorna array con los Tipos de condicion destino egreso
	public function CondicionDestinoEgreso()
	{
		return $this->aCondicionDestinoEgreso;
	}

	//	Retorna array con los Estados para epicrisis
	public function EstadosEpicrisis()
	{
		return $this->aEstadoEpicrisis;
	}

	//	Retorna array con los Tipos de Finalidad
	public function EstadosSalida()
	{
		return $this->aEstadoSalida;
	}

	//	Obtiene descripción de un Tipo de Causa
	public function tipoCausa($tcCodigo='')
	{
		$lcTipoCausa='';
		foreach($this->aTipoCausa as $laTipoCausa){
			if($laTipoCausa['codigo']==$tcCodigo){
				$lcTipoCausa=$laTipoCausa['desc'];
			}
		}
		return $lcTipoCausa;
	}

	//	Obtiene descripción de una conducta a seguir
	public function tipoConductaSeguir($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aConductaSeguir[$tcCodigo] ?? '';
	}

	//	Obtiene descripción de una modalidad grupo servicio
	public function tipoModalidadGrupoServicio($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aModalidadGrupoServicio[$tcCodigo] ?? '';
	}

	//	Obtiene descripción de una condicion destino egreso
	public function tipoCondicionDestinoEgreso($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aCondicionDestinoEgreso[$tcCodigo] ?? '';
	}

	//	Obtiene descripción de un estado de Salida
	public function estadoSalida($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aEstadoSalida[$tcCodigo] ?? '';
	}

	//	Obtiene descripción de una Finalidad
	public function finalidad($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aFinalidad[$tcCodigo] ?? '';
	}

	//	Obtiene descripción de un Estado de epicrisis
	public function estadoEpicrisis($tcCodigo='', $tcDescrip='desc')
	{
		return $this->aEstadoEpicrisis[$tcCodigo] ?? '';
	}

	//	Retorna array con campos obligatorios para HC WEB
	public function ObjObligatoriosHC()
	{
		return $this->aobjOblHC;
	}

	//	Retorna array con campos obligatorios para ORDENES AMBULATORIAS WEB
	public function ObjObligatoriosOA()
	{
		return $this->aobjOblOA;
	}

	//	Retorna array con objetos No visibles para HC WEB
	public function ObjNoVisibles()
	{
		return $this->aobjNoVisible;
	}

	public function DolorToracico()
	{
		return $this->aDolorToracico;
	}

	public function ObjObligatoriosEPI()
	{
		return $this->aobjOblEPI;
	}

	public function TiposComplicacionesUci()
	{
		return $this->aComplicacionesUci;
	}

	public function TiposParametrosUci()
	{
		return $this->aParametrosUci;
	}

	public function TiposGrupoMedicamentoUci()
	{
		return $this->aGrupoMedicamentoUci;
	}

	public function TiposMedicamentosUci()
	{
		return $this->aMedicamentosUci;
	}

	public function DatosMedicamentoUCC($tcCodigo='')
	{
		return $this->aMedicamentosUci[$tcCodigo] ?? '';
	}

		public function ObjObligatoriosEV()
	{
		return $this->aobjOblEV;
	}

	public function ObjNoVisiblesEV()
	{
		return $this->aobjNoVisibleEV;
	}

}
