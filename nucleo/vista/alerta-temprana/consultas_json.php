<?php
require_once (__DIR__ .'/../../publico/headJSON.php');
require_once (__DIR__ .'/../../publico/constantes.php');
require_once (__DIR__ .'/../../controlador/class.AplicacionFunciones.php');

use NUCLEO\AplicacionFunciones;

// la acción puede venir por $_GET o por $_POST
$lcAccion = $_REQUEST['accion'] ?? '';

switch ($lcAccion)
{
	// Buscar equipo
	case 'equipo':
		require_once (__DIR__ .'/../../controlador/class.Usuarios.php') ;

		if (isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
				$lcWhere='';
				if(isset($_GET['nombre'])){
					$lcNombre=strtolower(trim($_GET['nombre']));
					if(!empty($lcNombre)){
						$laNombre=explode(' ',$lcNombre);

						foreach($laNombre as $lcNombreAux){
							$lcNombreAux=trim($lcNombreAux);
							if(strlen($lcNombreAux)>=3){
								$lcNombreAux='%'.$lcNombreAux.'%';
								$lcWhere.=(empty($lcWhere)?"":" AND ").sprintf("(LOWER(NNOMED) LIKE '%s' OR LOWER(NOMMED) LIKE '%s')",$lcNombreAux,$lcNombreAux);
							}
						}
						$lcWhere=(empty($lcWhere)?"":sprintf("(%s) AND ",$lcWhere))." ESTRGM <>'2' AND TPMRGM IN ('1','11','12','13','4','6')";
					}
				}
				$laBusqueda=(new NUCLEO\Usuarios($lcWhere,true,'NNOMED'))->aUsuarios;
				if(is_array($laBusqueda)){
					if(count($laBusqueda)>0){
						$laUsuarios=array();
						foreach($laBusqueda as $lcUsuario=>$laRegistro){
							$laUsuarios[]=array('USUARIO'=>$lcUsuario, 'NOMBRE'=>$laRegistro['NOMBRE']);
						}
					}
				}
			}
		}
		echo json_encode($laUsuarios??'');
		break;


	// Datos de paciente desde el ingreso
	case 'ingreso':
		require_once (__DIR__ .'/../../controlador/class.Ingreso.php') ;

		$lnIngreso = $_POST['ingreso']??0; settype($lnIngreso,'Integer');
		$laIngreso = [
			'nIngreso'=>0,
			'cNombre'=>'',
			'cEdad'=>'',
			'cSexo'=>'',
			'cUbicacion'=>'-',
			'cEstado'=>'',
			'cTipoId'=>'',
			'nId'=>0,
		];

		if (isset($_SESSION[HCW_NAME])){
			if ($_SESSION[HCW_NAME]->oUsuario->getSesionActiva()==true){
				$loIngreso=new NUCLEO\Ingreso;
				$loIngreso->cargarIngreso($lnIngreso);

				$laIngreso['nIngreso']=$loIngreso->nIngreso;
				$laIngreso['cNombre']=$loIngreso->oPaciente->getNombreCompleto();
				$laIngreso['cEdad']=$loIngreso->oPaciente->getEdad();
				$laIngreso['cSexo']=$loIngreso->oPaciente->cSexo;
				$laIngreso['cEstado']=$loIngreso->cEstado;
				$laIngreso['cUbicacion']=$loIngreso->oHabitacion->cUbicacion;
				$laIngreso['cTipoId']=$loIngreso->oPaciente->aTipoId["TIPO"];
				$laIngreso['nId']=$loIngreso->oPaciente->nId;
			}
		}
		echo json_encode($laIngreso);
		break;


	// Títulos de consulta
	case 'titulos':
		require_once (__DIR__ .'/../../controlador/class.SignosNewsConsulta.php');
		$laReturn = [
			'campos'=>(new NUCLEO\SignosNewsConsulta())->aCampos,
			'respuestas'=>(new NUCLEO\SignosNews())->getRespuestas(),
		];
		echo json_encode($laReturn);
		break;


	// Datos de consulta
	case 'consultar':
		require_once (__DIR__ .'/../../controlador/class.SignosNewsConsulta.php');
		$loSignos = new NUCLEO\SignosNewsConsulta();

		$loSignos->nIngreso = $_POST['ingreso'] ?? '';
		$loSignos->nFechaDesde = str_replace('-','',$_POST['fdesde']) ?? 0;
		$loSignos->nFechaHasta = str_replace('-','',$_POST['fhasta']) ?? 0;
		$loSignos->nPagina = $_POST['pag'] ?? 1;
		$loSignos->nRegPorPag = $_POST['regxpag'] ?? 25;
		$loSignos->cOrden = $_POST['orden'] ?? 'VALFEA';
		$loSignos->cDirOrden = $_POST['dirorden'] ?? 'ASC';

		$loSignos->consultar();

		$laReturn['signos'] = [];
		$lnNum = 0;
		foreach ($loSignos->aSignos as $laSigno) {
			$lnCod = $laSigno['CONALE'];
			foreach ($loSignos->aCampos as $lcIndice=>$laCampo) {
				$lcDato = $laSigno[$lcIndice];
				if(!empty($lcDato)){
					if(isset($laCampo['tipo'])){
						switch($laCampo['tipo']){
							case 'hora':
							case 'fecha':
								$lcDato = AplicacionFunciones::formatFechaHora($laCampo['tipo'],$lcDato);
								break;
							default:
								$lcDato = trim(sprintf($laCampo['formato'], $lcDato));
						}
					}
				}else{
					$lcDato="";
				}

				if (isset($laCampo['lista'])) {
					$lcIndex = sprintf($laCampo['formato'], $lcDato);
					$laReturn['signos'][$lnCod][$lcIndice]['valor'] = $lcDato;
					$laReturn['signos'][$lnCod][$lcIndice]['dato'] = trim($laCampo['lista'][$lcIndex]['NOMBRE']??'');
				} else {
					$laReturn['signos'][$lnCod][$lcIndice]['valor'] = trim(sprintf($laCampo['formato'], $lcDato));
				}
				$laReturn['signos'][$lnCod][$lcIndice]['visible'] = $laCampo['visible']?'S':'N';
			}
			$lnNum++;
		}

		$laReturn['pagina']  = $loSignos->nPagina;
		$laReturn['regxpag'] = $loSignos->nRegPorPag;
		$laReturn['totalreg']= $loSignos->nNumRegistros;
		$laReturn['totalpag']= $loSignos->nTotalPaginas;
		$laReturn['orden']   = $loSignos->cOrden;
		$laReturn['dirorden']= $loSignos->cDirOrden;

		echo json_encode($laReturn);
		break;


	// Exportar consulta
	case 'exportar':
		require_once (__DIR__ .'/../../controlador/class.SignosNewsConsulta.php');
		$loSignos = new NUCLEO\SignosNewsConsulta();

		$loSignos->nIngreso = $_POST['ingreso'] ?? '';
		$loSignos->nFechaDesde = str_replace('-','',$_POST['fdesde']) ?? 0;
		$loSignos->nFechaHasta = str_replace('-','',$_POST['fhasta']) ?? 0;

		$loSignos->exportar(true);
		break;

	// Exportar consulta para estudio
	case 'exportarEstudio':
		$lnFechaDesde = str_replace('-','',$_POST['fdesde']) ?? 0;
		$lnFechaHasta = str_replace('-','',$_POST['fhasta']) ?? 0;

		require_once (__DIR__ . '/../../controlador/class.SignosNewsEstudio.php');
		$loConsulta=new NUCLEO\SignosNewsEstudio($lnFechaDesde, $lnFechaHasta);
		if ($loConsulta->consultar()) {
			$loConsulta->obtener();
		} else {
			echo $loConsulta->cError;
		}
		break;

}
