<?php
// Verificar sesión y crear $laRetorna['error']
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';

if ($lnContinuar) {

	// Acción a ejecutar
	$lcAccion = $_POST['accion'] ?? '';
	switch ($lcAccion) {


		// Propiedades para manejo del libro
		case 'inicio':

			$lcUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
			$lnTipoUsuario = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getTipoUsuario():0);
			$lcUrlGPC = 'http://172.20.10.84/intranet/index.php?view-explore-folder&P=jgohortiz&S=UTF-8&O=UTF-8&N=Q2FsaWRhZCAtIEdQQyA=&F=ZG9jdW1lbnRzLXNoYXJlZC9Eb2N1bWVudG9zIEluc3RpdHVjaW9uYWxlcy9DYWxpZGFkIC0gR1BDIC8=';
			$laRetorna['datos'] = [
				'FiltroMaxLabPdf'	=> trim($goDb->obtenerTabMae1('OP5TMA', 'LIBROHC', ['CL1TMA'=>'ALMAXREG','CL2TMA'=>'PDFLAB','ESTTMA'=>''], null, '1100')),
				'AlertaMaxLabPdf'	=> intval($goDb->obtenerTabMae1('OP3TMA', 'LIBROHC', ['CL1TMA'=>'ALMAXREG','CL2TMA'=>'PDFLAB','ESTTMA'=>''], null, '5')),
				'PuedeExportarPdf'	=> intval($goDb->obtenerTabMae1('OP3TMA', 'LIBROHC', ['CL1TMA'=>'PDFUSU','CL2TMA'=>'USUARIOS','CL3TMA'=>$lcUsuario,'ESTTMA'=>''], null, '0')),
				'FechaInicioPdf'	=> intval($goDb->obtenerTabMae1('OP2TMA', 'LIBROHC', ['CL1TMA'=>'PDFUSU','CL2TMA'=>'FECINI','ESTTMA'=>''], null, '20170301')),
				'FiltroProfesional'	=> explode('~', trim($goDb->obtenerTabMae1('DE2TMA', 'LIBROHC', ['CL1TMA'=>'PARAM','CL2TMA'=>'FILTRO','CL3TMA'=>'LISTAUSU','ESTTMA'=>''], null, '1,3,4,6,8,10,11,12,13~0~0'))),
				'UrlDocumentosGPC'	=> $lnTipoUsuario==20 ? base64_encode(trim($goDb->obtenerTabMae1('TRIM(DE2TMA||OP5TMA)', 'LIBROHC', ['CL1TMA'=>'DOCGPC','ESTTMA'=>''], null, $lcUrlGPC))) : '',
			];
			break;


		// Retorna los diferentes tipos de documento
		case 'tiposid':

			$lcTipoDsc = $_POST['descrip'] ?? 'T'; // A=abreviatura, D=descripción, T=todo (abreviatura + descripción),

			require_once __DIR__ . '/../../../controlador/class.TiposDocumento.php';
			$laTipos = (new NUCLEO\TiposDocumento(true))->aTipos;
			$laRetorna['TIPOS']=[];

			foreach($laTipos as $lcTipo=>$laTipo)
				$laRetorna['TIPOS'][$lcTipo] = $lcTipoDsc=='T' ? $laTipo['ABRV'].' - '.$laTipo['NOMBRE'] :
											(  $lcTipoDsc=='D' ? $laTipo['NOMBRE'] : $laTipo['ABRV'] ) ;
			unset($laTipos);
			break;


		// Retorna los datos para el ingreso consultado
		case 'ingreso':

			$lnIngreso = intval($_POST['ingreso']??'0');
			$lbUlt24h = intval($_POST['ult24h']??'0')==1;

			// require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			// (new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'LIBROHC_WEB', 'CONSULTA', 0, 'DATOS PACIENTE POR INGRESO LIBRO HC', ($lbUlt24h?'LIBHC24':'LIBROHC'), '', 0);

			if ($lnIngreso>500000 && $lnIngreso<99999999) {
				require_once __DIR__ .'/../../../controlador/class.Ingreso.php';
				$loIngreso=new NUCLEO\Ingreso;
				$loIngreso->cargarIngreso($lnIngreso);
				if ($loIngreso->nIngreso==0) {
					$laRetorna['error'] = "Número de ingreso $lnIngreso no se encontró en la base de datos.";
				} else {
					$laRta = [
						'nIngreso'	=>$loIngreso->nIngreso,
						'cTipId'	=>$loIngreso->oPaciente->aTipoId['ABRV'],
						'nNumId'	=>$loIngreso->nId,
						'cDocumento'=>$loIngreso->oPaciente->aTipoId['ABRV'] .' '. $loIngreso->nId,
						'cNombre'	=>$loIngreso->oPaciente->getNombreCompleto(),
						'nFechaIng' =>$loIngreso->nIngresoFecha,
						'nFechaEgr' =>$loIngreso->nEgresoFecha,
					];
					unset($loIngreso);
					if($lbUlt24h){
						$_SESSION['Ult24h']=$laRta;
					}
					$laRetorna += $laRta;
				}
			} else {
				$laRetorna['error'] = "Número de ingreso $lnIngreso incorrecto.";
			}
			break;



		// Retorna los datos para el documento consultado
		case 'paciente':

			$lcTipId = (isset($_POST['tipId']) ? $_POST['tipId'] : '');
			$lnNumId = (isset($_POST['numId']) ? intval($_POST['numId']) : 0);

			// require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			// (new NUCLEO\Auditoria())->guardarAuditoria(0, 0, 0, '', 'LIBROHC_WEB', 'CONSULTA', 0, 'DATOS PACIENTE POR DOCUMENTO LIBRO HC', 'LIBROHC', $lcTipId, $lnNumId);

			require_once __DIR__ .'/../../../controlador/class.Paciente.php';
			$loPaciente = new NUCLEO\Paciente;
			$loPaciente->cargarPaciente($lcTipId, $lnNumId);

			$laRetorna['cTipId']=$loPaciente->aTipoId['ABRV'];
			$laRetorna['nNumId']=$loPaciente->nId;
			$laRetorna['cDocumento']=$loPaciente->aTipoId['ABRV'] .' '. $loPaciente->nId;
			$laRetorna['cNombre']=$loPaciente->getNombreCompleto();

			unset($loIngreso);
			break;


		// Retorna la lista de documentos
		case 'lista':

			$lnIngreso = intval($_POST['ingreso'] ?? '0');
			$lcTipId = $_POST['tid'] ?? '';
			$lnNumId = intval($_POST['nid'] ?? 0);

			require_once __DIR__ . '/../../../controlador/class.Auditoria.php';
			(new NUCLEO\Auditoria())->guardarAuditoria($lnIngreso, 0, 0, '', 'LIBROHC_WEB', 'LISTADOC', 0, 'CONSULTA DOCUMENTOS LIBRO HC', 'LIBROHC', $lcTipId, $lnNumId);

			require_once __DIR__ .'/../../../controlador/class.ListaDocumentos.php';
			$loListaDocs = new NUCLEO\ListaDocumentos();
			$laRetorna = $loListaDocs->listarDocumentos($lnIngreso, $lcTipId, $lnNumId);
			unset($loListaDocs);

			break;


		// Retorna contenido de documentos de las últimas 24hr
		case 'ult24h':

			$lnIngreso = intval($_POST['ingreso'] ?? '0');
			if(isset($_SESSION['Ult24h'])){
				$lcTipId = $_SESSION['Ult24h']['cTipId'];
				$lnNumId = $_SESSION['Ult24h']['nNumId'];
				$lcPac = $_SESSION['Ult24h']['cNombre'];
				unset($_SESSION['Ult24h']);

				if ($lnIngreso>0) {
					require_once __DIR__ .'/../../../controlador/class.ListaDocumentos.php';
					$loListaDocs = new NUCLEO\ListaDocumentos();
					$laRetorna = $loListaDocs->ultimasHoras($lnIngreso, $lcTipId, $lnNumId);
					if (empty($laRetorna['error'])) {
						require_once __DIR__ .'/../../../controlador/class.Documento.php';
						$loDocLibro = new NUCLEO\Documento();
						$loDocLibro->generarVariosHTML($laRetorna['datos'], SORT_DESC);
						exit;
					}
					unset($loListaDocs);
				} else {
					$laRetorna['error'] = 'Se debe indicar el número de ingreso';
					$laRetorna['tipoerror'] = 'danger';
				}
			} else {
				$laRetorna['error'] = 'Error al obtener datos del paciente.';
				$laRetorna['tipoerror'] = 'danger';
			}

			break;


		// Retorna grupos y subgrupos de documentos (ítems del tree)
		case 'tree':

			$laRetorna['tree'] = [];
			require_once __DIR__ .'/../../../controlador/class.ListaDocumentos.php';
			$laRetorna['tree'] = (new NUCLEO\ListaDocumentos())->obtenerItemsTree();

			break;


		// Retorna tipos de documento
		case 'tiposdoc':

			require_once __DIR__ .'/../../../controlador/class.ListaDocumentos.php';
			$loListaDocs = new NUCLEO\ListaDocumentos();
			$laRetorna['tipos'] = $loListaDocs->obtenerDescripcionTipos();

			break;


		// Retorna listado de entidades permitidas para el usuario
		case 'entidades':

			require_once __DIR__ .'/../../../controlador/class.Entidades.php';
			$loEntidades = new NUCLEO\Entidades();
			$lcUser = $_SESSION[HCW_NAME]->oUsuario->getUsuario();
			$laRetorna['data'] = $loEntidades->listaNitEntidades($lcUser);

			break;


		// Retorna Libros en PDF generados
		case 'listapdf':

			require_once __DIR__ .'/../../../controlador/class.LibroHC_Gen.php';
			$loLibroHC = new NUCLEO\LibroHC_Gen();

			$laRetorna['data'] = $loLibroHC->consultarLibroHcPdf(
					$_POST['ingres'],
					$_POST['tipdoc'],
					$_POST['numdoc'],
					$_POST['fectip'],
					str_replace('-','',$_POST['fecini']),
					str_replace('-','',$_POST['fecfin']),
					$_POST['codent'],
					$_POST['estado'],
					$_SESSION[HCW_NAME]->oUsuario->getEntidadesConsultaLibroHc()
				);

			break;


		// Retorna número de entidades permitidas del usuario
		case 'ctaentidades':

			$laEnt = $_SESSION[HCW_NAME]->oUsuario->getEntidadesConsultaLibroHc();
			$laRetorna['data'] = is_array($laEnt) ? count($laEnt) : 0;
			break;


		// Retorna Libro PDF generado
		case 'abrirpdf':
			require_once __DIR__ .'/../../../controlador/class.LibroHC_Gen.php';
			$loLibroHC = new NUCLEO\LibroHC_Gen();
			$loLibroHC->abrirLibroHcPdf($_POST['ingreso'],$_POST['ruta'],$_POST['archivo']);
			exit;
			break;


		// Retorna PDF en base64
		case 'docPDF':
			ini_set('max_execution_time', 60*600); // 600 minutos de consulta
			require_once __DIR__ .'/../../../controlador/class.Documento.php';
			$loDocLibro = new NUCLEO\Documento();
			$laDatos = (array) json_decode(utf8_encode(base64_decode($_POST['datos']??'')));
			if (isset($laDatos['datos'])) {
				$laDatosPortada = $laDatosDoc = [];
				$laDatos['datos'] = (array) $laDatos['datos'];
				foreach($laDatos['datos'] as $loValor){
					$laDatosDoc[] = (array) $loValor;
				}
				if (isset($laDatos['portada'])) {
					$laDatos['portada'] = (array) $laDatos['portada'];
					$laDatosPortada = (array) $laDatos['portada'];
				}
				ini_set('memory_limit', '1024M');
				echo base64_encode($loDocLibro->generarVariosPDF($laDatosDoc, $laDatosPortada, 'librohc.pdf', 'S', null, false, $tcUsuario));
				exit;
			} else {
				$laRetorna['error'] = 'No hay datos de documentos para retornar';
			}
			break;

	}
}
include __DIR__ .'/../../../publico/headJSON.php';
echo json_encode($laRetorna);
