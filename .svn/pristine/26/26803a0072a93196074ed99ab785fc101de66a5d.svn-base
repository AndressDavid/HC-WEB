<?php
namespace NUCLEO;
require_once __DIR__ .'/class.Db.php';
require_once __DIR__ .'/class.Especialidad.php';
require_once __DIR__ .'/class.FormulacionParametros.php';
require_once __DIR__ . '/class.MailEnviar.php';
require_once __DIR__ . '/class.Ingreso.php';
require_once __DIR__ . '/class.MedicamentoFormula.php';

use NUCLEO\Db;
use NUCLEO\Especialidad;
use NUCLEO\FormulacionParametros;
use NUCLEO\MailEnviar;
use NUCLEO\Ingreso;
use NUCLEO\MedicamentoFormula;

class Conciliacion
{
    private $cTextoC = '';
	protected $cTipId = 0;
	protected $nNumId = 0;
	protected $nIngreso = 0;
	protected $nConCon = 0;
	protected $cFecCre = '';
	protected $cHorCre = '';
	protected $cUsuCre = '';
	protected $cPrgCre = '';
	protected $aANTPADC = [];
	protected $aANTPACC = [];
	protected $aDatosConsulta = [];
	protected $oDb;
	protected $aErrorC = [
				'Mensaje' => "",
				'Objeto' => "",
				'Valido' => true,
			];


    public function __construct($taConcilia=[],$tnTipo=1)
	{
		global $goDb;
		$this->oDb = $goDb;
		if(count($taConcilia)>0 &&  $tnTipo==1){
				$this->cargar($taConcilia,$tnTipo);
		}else{
			if($tnTipo==9){
				$this->ConciliacionAnterior($taConcilia);
			}else{
				$this->OrganizarConcilia($taConcilia);
			}
		}
    }

	public function cargar($taConcilia=[],$tnTipo=1)
	{
		if(isset($this->oDb)){

			// CALCULA ULTIMO CONSECUTIVO DE CONCILIACION
			if (empty($taConcilia['op7and'])){
				$laConciliaM = $this->oDb
					->select('NINANT NINAND, TIDANT TIDAND, NIDANT NIDAND, DESANT DESAND, CODANT CODAND, SANANT SANAND, INDANT INDAND, OP5ANT Informante, OP6ANT OP6AND, FECANT FECAND, HORANT HORAND, PGMANT PGMAND')
					->from('ANTPAC')
					->where(['NINANT'=>$taConcilia['ninand'],
							 'CODANT'=>$taConcilia['codand'],
							 ])
					->orderBy ('SANANT, INDANT, LINANT')
					->getAll('array');

			}else{
				$laLstPgr = [$taConcilia['pgmand']];
				if ($taConcilia['pgmand']='HCPPAL'){
					$laLstPgr = ['HCPPAL','HCPPALWEB'];
				}

				$laConciliaM = $this->oDb
					->select('NINAND, TIDAND, NIDAND, DESAND, CODAND, SANAND, INDAND, OP5AND Informante, OP6AND, FECAND, HORAND, PGMAND')
					->from('ANTPAD')
					->where(['NINAND'=>$taConcilia['ninand'],
							 'CODAND'=>$taConcilia['codand'],
							 'OP7AND'=>$taConcilia['op7and'],
							])
					->in('PGMAND', $laLstPgr)
					->orderBy ('SANAND, INDAND, LINAND')
					->getAll('array');
			}

			$loObj = new Ingreso();
			$lnHorasIng = $loObj->obtenerHorasIngreso($taConcilia['ninand']);
			$oTabmae = $this->oDb->ObtenerTabMae('OP3TMA', 'CONCILIA', ['CL1TMA'=>'HORACONC', 'CL2TMA'=>'01', 'ESTTMA'=>' ']);
			$lnHorasConcilia = intval(AplicacionFunciones::getValue($oTabmae, 'OP3TMA', ''));
			$this->aDatosConsulta['Habilita']=false;
			if($lnHorasIng>$lnHorasConcilia){$this->aDatosConsulta['Habilita']=true;}
			$this->OrganizarConcilia($laConciliaM);
		}
	}

	public function OrganizarConcilia($taConcilia=[])
	{
		$lcSL = "\n";
		$lcDetalle='';
		$this->aDatosConsulta['Consume']=$this->aDatosConsulta['Informa']='';
		$this->aDatosConsulta['Informante']=$lcDetalle='';
		$lnInd=$lnNoConcialicion=0;

		$loObj = new FormulacionParametros();
		$loObj->obtenerParametrosTodos();
		$laFrecuencia = $loObj->frecuencias();
		$laTiposNoConsume =  $loObj->NoConsume();

		$loFormula = new MedicamentoFormula();

		if (count($taConcilia)>0){

			foreach($taConcilia as $laConcilia){
				switch ($laConcilia['INDAND']) {
					case 0 :
						$this->aDatosConsulta['Consume']=trim(substr($laConcilia['DESAND'],9,2));
						$this->aDatosConsulta['Consume']=($this->aDatosConsulta['Consume']=='Si'?'Si':($this->aDatosConsulta['Consume']=='No'?'No':''));
						$this->aDatosConsulta['Informa']=trim(substr($laConcilia['DESAND'],21,2));
						$this->aDatosConsulta['Informa']=($this->aDatosConsulta['Informa']=='Si'?'Si':($this->aDatosConsulta['Informa']=='No'?'No':''));
						$this->aDatosConsulta['MotivoNC'] = $laConcilia['OP6AND']??'';
						$lnPosicion = mb_strpos($laConcilia['DESAND'],'No Registra',0);
						if ($lnPosicion ==0){
							$lnPosicion = mb_strpos($laConcilia['DESAND'],'INFORMANTE',0);
							$this->aDatosConsulta['Informante'] = $lnPosicion==0?'':trim(substr($laConcilia['DESAND'],$lnPosicion+12,150));
						}else{
							$this->aDatosConsulta['Informante'] = '';	
						}
						break ;
					case 1 :
						$llTitulo = true ;
						$lnInd++;
						$this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO']= trim(substr($laConcilia['DESAND'],105,11));
						$lcCodigoMedicamento=$this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO'];
						$laResultado = $loObj->BuscarMedicamento($this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO']);
						$this->aDatosConsulta['Medicamentos'][$lnInd]['MEDICA']= (empty($laResultado)?trim(substr($laConcilia['DESAND'],0,30)):trim($laResultado));
						$laResultadoMedicamento = $loObj->EstadoMedicamento($this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO']);
						$this->aDatosConsulta['Medicamentos'][$lnInd]['ESTADO']= isset($laResultadoMedicamento['ESTADO']) ? $laResultadoMedicamento['ESTADO'] : '';
						$this->aDatosConsulta['Medicamentos'][$lnInd]['POSNOPOS']= isset($laResultadoMedicamento['POSNOPOS']) ? $laResultadoMedicamento['POSNOPOS'] : '';
						$laResultadoControlado = $loObj->ControladoMedicamento($this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO']);
						$this->aDatosConsulta['Medicamentos'][$lnInd]['CONTROLADO']= isset($laResultadoControlado['CONTROLADO']) ? $laResultadoControlado['CONTROLADO'] : '';
						$this->aDatosConsulta['Medicamentos'][$lnInd]['DIASMAXANTIBIOTICO']=intval($this->oDb->obtenerTabmae1('OP3TMA', 'ANTIBI', "DE1TMA='$lcCodigoMedicamento' AND ESTTMA=''", null, 0));;
						$this->aDatosConsulta['Medicamentos'][$lnInd]['ESANTIBIOTICO']=intval($this->oDb->obtenerTabmae1('OP3TMA', 'ANTIBI', "DE1TMA='$lcCodigoMedicamento' AND ESTTMA=''", null, 0))>0?true:false;
						$this->aDatosConsulta['Medicamentos'][$lnInd]['CONTROLALERTAANTIB']=$this->oDb->obtenerTabmae1('OP1TMA', 'ANTIBI', "DE1TMA='$lcCodigoMedicamento' AND ESTTMA=''", null, '');
						$laDatosGrupoFarmacologico = explode('~', $loFormula->fcGrupoFarmacologico($lcCodigoMedicamento,true,true));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['GRUPOCODFARMACEUTICO']=$laDatosGrupoFarmacologico[0]??'';
						$this->aDatosConsulta['Medicamentos'][$lnInd]['DESCRGRUPOCODFARMACEUTICO']=$laDatosGrupoFarmacologico[1]??'';
						$laResultadoUnirs = $loObj->unirsMedicamento($this->aDatosConsulta['Medicamentos'][$lnInd]['CODIGO']);
						$this->aDatosConsulta['Medicamentos'][$lnInd]['LISTADOUNIRS']= isset($laResultadoUnirs['ESMEDICAMENTOUNIRS']) ? $laResultadoUnirs['ESMEDICAMENTOUNIRS'] : '';

						$lcDetalle .= $lcSL . $lnInd . '). - ' . (empty($laResultado)?trim(substr($laConcilia['DESAND'],0,30)):trim($laResultado)) . '  ' . trim(substr($laConcilia['DESAND'],30,7)) ;
						$lcTemp = trim(substr($laConcilia['DESAND'],37,8)) ;
						$lcDetalle .= ' ' . $loObj->unidadDosis($lcTemp);
						$lcTemp = trim(substr($laConcilia['DESAND'],45,7)) ;
						$lcDetalle .= ' Tomado Vía ' . $loObj->viaAdmin($lcTemp). ' '
									   . trim(substr($laConcilia['DESAND'],53,4)) . ' '
									   . trim(substr($laConcilia['DESAND'],85,20)) . '.' ;
						$lcTemp = trim(substr($laConcilia['DESAND'],73,2))=='1'? 'CONTINUAR' :
								  (trim(substr($laConcilia['DESAND'],75,2))=='1'? 'SUSPENDER' :
								  (trim(substr($laConcilia['DESAND'],77,2))=='1'? 'MODIFICAR' : '' ));
						$lcDetalle .=' El paciente debe ' . $lcTemp . ' el medicamento.' . $lcSL ;

						$this->aDatosConsulta['Medicamentos'][$lnInd]['DOSIS']= trim(substr($laConcilia['DESAND'],30,7));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['TIPODCOD']= trim(substr($laConcilia['DESAND'],37,8));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['TIPOD']= $loObj->unidadDosis(trim(substr($laConcilia['DESAND'],37,8)));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['FRECUENCIA']=trim(substr($laConcilia['DESAND'],53,4));
						$lcTemp = trim(substr($laConcilia['DESAND'],85,20));

						foreach($laFrecuencia as $laDato){
							if ($laDato['desc']==$lcTemp){
								$this->aDatosConsulta['Medicamentos'][$lnInd]['TIPOCODF']=$laDato['codigo'];
								$this->aDatosConsulta['Medicamentos'][$lnInd]['TIPOF']=$laDato['desc'];
								break;
							}
						}
						$this->aDatosConsulta['Medicamentos'][$lnInd]['VIACOD']= trim(substr($laConcilia['DESAND'],45,7));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['VIA']= $loObj->viaAdmin(trim(substr($laConcilia['DESAND'],45,7)));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['CONTINUA']= trim(substr($laConcilia['DESAND'],73,2))=='1'? 'Continua' :
								  (trim(substr($laConcilia['DESAND'],75,2))=='1'? 'Suspende' :
								  (trim(substr($laConcilia['DESAND'],77,2))=='1'? 'Modifica' : '' ));
						$this->aDatosConsulta['Medicamentos'][$lnInd]['OBSERVA']='';
						break ;

					case 2 :
						$lcTitulo = $llTitulo==true?'Observaciones: ':'';
						$llTitulo = false;
						$lcDetalle .= $lcTitulo . trim($laConcilia['DESAND']);
						$this->aDatosConsulta['Medicamentos'][$lnInd]['OBSERVA'].=trim($laConcilia['DESAND']);
						break ;

					case 3 :
						
						$lnNoConcialicion=1;
						if ($this->aDatosConsulta['Consume']==='No'){
							if($laConcilia['OP6AND']===''){
								$lcDetalle .= trim($laConcilia['DESAND']). $lcSL;	
							} else {
								$this->aDatosConsulta['MotivoNC'] = !empty(trim($laConcilia['OP6AND']))?intval($laConcilia['OP6AND']):'';
								$lcDetalle .= !empty(trim($laConcilia['OP6AND']))?$laTiposNoConsume[$laConcilia['OP6AND']]:'';
							}
						}
						break ;
				}
			}
		}

		if(!empty($this->aDatosConsulta['Informante'])){
			$lcDetalle .= $lcSL . $this->aDatosConsulta['Informante'];
		}
		
		if ($this->aDatosConsulta['Informa']=='Si' && !empty($lcDetalle) && $lnNoConcialicion==0){
			$lcDetalle .= $lcSL . 'LA FUNDACION CLINICA SHAIO NO SE HACE RESPONSABLE DE LA ADMINISTRACION DE MEDICAMENTOS' .
					' NATURALES, HOMEOPATICOS, TERAPIAS ALTERNATIVAS O FARMACOLOGÍA VEGETAL, DURANTE LA ESTANCIA' .
					' EN LA INSTITUCION DEL PACIENTE. El Dr. informa al Paciente. ? SI'  ;
		}

		$this->cTextoC = trim($lcDetalle) ;
	}

	public function verificarDatosC($taDatos=[], $taEdad=[])
	{
		$taDatos['Medicamentos']=$taDatos['Medicamentos']??[];

		//VALIDA LA CANTIDAD DE MEDICAMENTOS SI EL PACIENTE CONSUME
		if($taDatos['Consume']=='Si' && count($taDatos['Medicamentos'])==0){
			$this->aErrorC = [
				'Mensaje'=>'El paciente consume medicamentos pero no existe información en la conciliación',
				'Objeto'=>'selConsume',
				'Valido'=>false,
			];
			return $this->aErrorC ;
		}

		// VALIDA QUE TENGA MOTIVO DE RESPUESTA NO 
		if($taDatos['Consume']=='No' && $taDatos['NoConsume']==''){
			$this->aErrorC = [
				'Mensaje'=>'Se debe indicar el motivo por el que el paciente NO consume medicamentos',
				'Objeto'=>'selNoConsume',
				'Valido'=>false,
			];
			return $this->aErrorC ;
		}

		//VALIDA LA CANTIDAD DE MEDICAMENTOS SI EL PACIENTE NO CONSUME
		if($taDatos['Consume']=='No' && count($taDatos['Medicamentos'])>1){
			$this->aErrorC = [
				'Mensaje'=>'El paciente NO consume medicamentos pero existe información en la conciliación',
				'Objeto'=>'selConsume',
				'Valido'=>false,
			];
			return $this->aErrorC ;
		}

		if(count($taDatos['Medicamentos'])>0){
			$loObjC = new FormulacionParametros() ;
			$loObjC->obtenerParametrosTodos();

			foreach($taDatos['Medicamentos'] as $lnKey=>$laConcilia){

				//Valida que el medicamento codificado exista
				if(substr($laConcilia['CODIGO'],0,2) !='NC'){
					$laResultado = $loObjC->BuscarMedicamento(trim($laConcilia['CODIGO']));
					if(empty($laResultado)){
						$this->aErrorC = [
							'Mensaje'=>'No existe en la base de datos el medicamento codificado: '.trim($laConcilia['MEDICA']),
							'Objeto'=>'btnAdicionarM',
							'Valido'=>false,
						];
						break;
					}
				}

				// Validación del tipo de frecuencia
				if(!isset($laConcilia['TIPOCODF'])){
					$this->aErrorC = [
						'Mensaje'=>'No existe el tipo de Frecuencia digitado para el medicamento '.trim($laConcilia['MEDICA']),
						'Objeto'=>'btnAdicionarM',
						'Valido'=>false,
					];
					break;
				}

				$laResultado = $loObjC->Frecuencia($laConcilia['TIPOCODF']??'');
				if(empty($laResultado)){
					$this->aErrorC = [
						'Mensaje'=>'No existe el tipo de Frecuencia digitado para el medicamento '.trim($laConcilia['MEDICA']),
						'Objeto'=>'btnAdicionarM',
						'Valido'=>false,
					];
					break;
				}

				// Validación de la via de administración
				$laResultado = $loObjC->viaAdmin($laConcilia['VIACOD']);
				if(empty($laResultado)){
					$this->aErrorC = [
						'Mensaje'=>'No existe el tipo de vía administración para el medicamento '.trim($laConcilia['MEDICA']),
						'Objeto'=>'btnAdicionarM',
						'Valido'=>false,
					];
					break;
				}

				// Validación de la conducta a seguir
				if(trim($laConcilia['CONTINUA']) != 'Continua' && trim($laConcilia['CONTINUA']) != 'Suspende' && trim($laConcilia['CONTINUA']) != 'Modifica'){
					$this->aErrorC = [
						'Mensaje'=>'No existe el tipo de conducta a seguir para el medicamento '.trim($laConcilia['MEDICA']),
						'Objeto'=>'btnAdicionarM',
						'Valido'=>false,
					];
					break;
				}
			}
			if($this->aErrorC['Valido']==false){
				return $this->aErrorC ;
			}
		}

		if(empty(trim($taDatos['Informante'])) && $taEdad['y']<16){
			$this->aErrorC = [
				'Mensaje'=>'Informante paciente pediátrico es obligatorio',
				'Objeto'=>'txtInformante',
				'Valido'=>false,
			];
		}

		return $this->aErrorC ;
	}

	public function guardarDatosC($taDatos=[], $taIngreso=[], $tcPrograma='', $tnConCon=1)
	{
		$this->aANTPADC = [];
		$this->aANTPACC = [];
		$lcEspecialidad = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getEspecialidad():'');
		$lcRegMed = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getRegistro():'');
		$this->cTipId = $taIngreso['cTipId'];
		$this->nNumId = $taIngreso['nNumId'];
		$this->nIngreso = $taIngreso['nIngreso'];
		$this->nConCon = $tnConCon;

		$ltAhora = new \DateTime( $this->oDb->fechaHoraSistema() );
		$this->cFecCre = $ltAhora->format("Ymd");
		$this->cHorCre = $ltAhora->format("His");
		$this->cUsuCre = (isset($_SESSION[HCW_NAME])?$_SESSION[HCW_NAME]->oUsuario->getUsuario():'');
		$this->cPrgCre = $tcPrograma;

		// Solo debe quedar el último registro guardado en la tabla ANTPAC
		$lcCodAnt = '17';
		$lbRta = $this->oDb->from('ANTPAC')
			->where('TIDANT', '=', $taIngreso['cTipId'])
			->where('NIDANT', '=', $taIngreso['nNumId'])
			->where('CODANT', '=', $lcCodAnt)
			->eliminar();

		// Guardar CABECERA

		$lcCabecera = 'CONSUME: ' . $taDatos['Consume'] . ' INFORMA: ' . $taDatos['Informa'] . ' MEDICO: ' . $lcRegMed . ' MED. ESP: ' . $lcEspecialidad .
					  ' INFORMANTE: ' . (empty(trim($taDatos['Informante']))?'No Registra':trim($taDatos['Informante']));

		$lnIndice = 17;
		$lnCodigo = 0;
		$lnLinea = 1;
		$lnSubInd = 1;

		$this->InsertarRegistro('ANTPAC', $lcCabecera, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea) ;
		$this->InsertarRegistro('ANTPAD', $lcCabecera, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea) ;

		$lcTextoMail = '';
		$taDatos['Medicamentos']=$taDatos['Medicamentos']??[];
		if(count($taDatos['Medicamentos'])>0){

			foreach($taDatos['Medicamentos'] as $laConcilia){
				$lcNombre = substr($laConcilia['MEDICA'],0,30);
				$lcContina = (trim($laConcilia['CONTINUA'])== 'Continua'?'1 0 0 ':(trim($laConcilia['CONTINUA'])== 'Suspende'?'0 1 0 ':(trim($laConcilia['CONTINUA'])== 'Modifica'?'0 0 1 ':'')));
				$lcDescrip = str_pad(trim($lcNombre),30,' ',STR_PAD_RIGHT) . str_pad(trim($laConcilia['DOSIS']),7,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['TIPODCOD']),8,' ',STR_PAD_RIGHT) . str_pad(trim($laConcilia['VIACOD']),8,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['FRECUENCIA']),4,' ',STR_PAD_RIGHT) . str_repeat(' ',16) . $lcContina . str_repeat(' ',2) .
							trim($taDatos['Informa']) . trim($taDatos['Consume']) . str_pad(trim($laConcilia['TIPOF']),20,' ',STR_PAD_RIGHT) .
							str_pad(trim($laConcilia['CODIGO']),11,' ',STR_PAD_RIGHT);

				if(substr($laConcilia['CODIGO'],0,2)=='NC'){
					$lcTextoMail .= '* ' . trim($laConcilia['CODIGO']) . '- ' . $laConcilia['MEDICA'] . ', ' . trim($laConcilia['CONTINUA']) . "<br/>";
				}

				$lnCodigo = 1;
				$this->InsertarRegistro('ANTPAC', $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				$this->InsertarRegistro('ANTPAD', $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);

				if(!empty(trim($laConcilia['OBSERVA']))){
					$lnCodigo = 2;
					$lcObserva = trim($laConcilia['OBSERVA']);
					$this->InsertarDescripcion('ANTPAC', 220, $lcObserva, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
					$this->InsertarDescripcion('ANTPAD', 220, $lcObserva, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea);
				}

				$lnSubInd++;
			}
		}

		if($taDatos['Consume']=='No'){
			$lcMotivo = str_pad($taDatos['NoConsume'],2,'0',STR_PAD_LEFT);        
			$lnCodigo = 3;
			$oTabmae = $this->oDb->ObtenerTabMae('DE2TMA', 'DATING', ['CL1TMA'=>'FEHCONC', 'ESTTMA'=>' ']);
			$lcDescrip = AplicacionFunciones::getValue($oTabmae, 'DE2TMA', '');
			$this->InsertarDescripcion('ANTPAC', 220, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, '', $lcMotivo) ;
			$this->InsertarDescripcion('ANTPAD', 220, $lcDescrip, $lnIndice, $lnSubInd, $lnCodigo, $lnLinea, '', $lcMotivo) ;
		}

		// Insertar datos a las tablas en AS400 ANTPAC
		$lcTabla = 'ANTPAC';
		foreach($this->aANTPACC  as $laANTPAC){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laANTPAC);
		}

		// Insertar datos a las tablas en AS400 ANTPAD
		$lcTabla = 'ANTPAD';
		foreach($this->aANTPADC  as $laANTPAD){
			$llResultado = $this->oDb->tabla($lcTabla)->insertar($laANTPAD);
		}

		//enviar Mail
		if(!empty(trim($taIngreso['cCodVia'])) && $taIngreso['cCodVia']!='02' && !empty(trim($lcTextoMail))){

			// consulta nombre del medico
			$laUsuario = $this->oDb
				->from('RIARGMN')
				->where('USUARI', '=', $this->cUsuCre)
				->get('array');

			//consulta descripción especialidad
			$loEspecial = new Especialidad($lcEspecialidad);
			$lcUsuario = trim($laUsuario['NOMMED']) . ' ' . trim($laUsuario['NOMMED']);

			$this->enviarCorreoNC($lcTextoMail, $lcUsuario, $loEspecial->cNombre, $taIngreso);
		}
	}

	function InsertarDescripcion($tcTabla='', $tnLongitud=0, $tcTexto='', $tnIndice=0, $tnSubIndice=0, $tnCodigo=0, $tnLinea=1, $tcSubOrg='', $tcTipoTratamiento='')
	{
		$laChar = AplicacionFunciones::mb_str_split(trim($tcTexto),$tnLongitud);

		if(is_array($laChar)==true){
			if(count($laChar)>0){
				foreach($laChar as $laDato){
					$this->InsertarRegistro($tcTabla, $laDato, $tnIndice, $tnSubIndice, $tnCodigo, $tnLinea, $tcTipoTratamiento) ;
					$tnLinea++;
				}
			}
		}
	}

	function InsertarRegistro($tcTabla='', $tcDescrip='', $tnIndice=0, $tnSubInd=0, $tnCodigo=0, $tnConsec=0, $tcAdicional='')
	{
		switch (true)
		{
			case $tcTabla=='ANTPAC' :
				$this->aANTPACC[]=[
					'TIDANT'=>$this->cTipId,
					'NIDANT'=>$this->nNumId,
					'NINANT'=>$this->nIngreso,
					'CODANT'=>$tnIndice,
					'SANANT'=>$tnSubInd,
					'INDANT'=>$tnCodigo,
					'LINANT'=>$tnConsec,
					'DESANT'=>$tcDescrip,
					'OP6ANT'=>$tcAdicional,
					'USRANT'=>$this->cUsuCre,
					'PGMANT'=>$this->cPrgCre,
					'FECANT'=>$this->cFecCre,
					'HORANT'=>$this->cHorCre,
				];
				break;

			case $tcTabla=='ANTPAD' :
				$this->aANTPADC[]=[
					'TIDAND'=>$this->cTipId,
					'NIDAND'=>$this->nNumId,
					'NINAND'=>$this->nIngreso,
					'FDCAND'=>$this->cFecCre,
					'HDCAND'=>$this->cHorCre,
					'CODAND'=>$tnIndice,
					'SANAND'=>$tnSubInd,
					'INDAND'=>$tnCodigo,
					'LINAND'=>$tnConsec,
					'DESAND'=>$tcDescrip,
					'OP5AND'=>'', // falta dato '<<thisform.cInformMed>>' = > hace referencia al informante pediatrico en conciliacion,
					'OP6AND'=>$tcAdicional,
					'OP7AND'=>$this->nConCon,
					'USRAND'=>$this->cUsuCre,
					'PGMAND'=>$this->cPrgCre,
					'FECAND'=>$this->cFecCre,
					'HORAND'=>$this->cHorCre,
				];
				break ;
		}
	}

	function enviarCorreoNC($tcListado='', $tcMedico='', $tcEspecialidad='', $taIngreso=[])
	{
		$lcHabitacion = '';
		if(!empty($taIngreso['cPlan'])){
			$laTemp = $this->oDb
					->select('IN4CON')
					->from('FACPLNC4')
					->where('PLNCON', '=', $taIngreso['cPlan'])
					->get('array');
			if(is_array($laTemp)){
				if(count($laTemp)>0){
					$lcHabitacion = ($laTemp['IN4CON']==1?'M.P.P.':($laTemp['IN4CON']==2?'P.O.S.':''));
				}
			}
		}

		$loMailEnviar = new MailEnviar();

		// Obtener plantilla desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PLANTILL'
		$loMailEnviar->obtenerPlantilla('NOTFARCO', 'ALEFARCO');
		$lcPlantilla = $loMailEnviar->cPlantilla;

		// Configuración desde TABMAE con TIPTMA='MAILSETT', CL1TMA='PARAMETR'
		$laConfigToda = $loMailEnviar->obtenerConfiguracion('NOTFARCO');
		$laConfig = $laConfigToda['config'];

		// Reemplazar datos en asunto y plantilla
		$laDatos = [
			'[[Ingreso]]'=>$taIngreso['nIngreso'],
			'[[NombrePaciente]]'=>$taIngreso['cNombre'],
			'[[Nombre]]'=>$taIngreso['cNombre'],
			'[[Via]]'=>$taIngreso['cDesVia'],
			'[[Habitacion]]'=>$lcHabitacion,
			'[[ListaNoCodificados]]'=>$tcListado,
			'[[Medico]]'=>$tcMedico,
			'[[Solicitante]]'=>$tcEspecialidad,
		];
		$lcPlantilla = strtr($lcPlantilla, $laDatos);
		$laConfig['tcSubject'] = strtr($laConfig['tcSubject'], $laDatos);

		// Completa la configuración
		$lcConCopia = 'patricia.parra@shaio.org';
		$laConfig['tcCC'] = $lcConCopia;
		$laConfig['tcBody'] = $lcPlantilla;

		// Enviar el correo
		$lcResult = $loMailEnviar->enviar($laConfig);
	}

	public function TextoConciliacion($taDatos=[])
	{
		$this->cTextoC = $lcDescrip = '';
		$lcSL = "\n";
		$lnIndice = 0;
		$loObj = new FormulacionParametros();
		$loObj->obtenerParametrosTodos();
		$laFrecuencia = $loObj->frecuencias();

		$taDatos['Medicamentos']=$taDatos['Medicamentos']??[];
		if(count($taDatos['Medicamentos'])>0){
			foreach($taDatos['Medicamentos'] as $laConcilia){
				$lnIndice++;
				$lcNombre = substr(trim($laConcilia['CODIGO']),0,2)=='NC'?substr($laConcilia['MEDICA'],0,30):substr(trim($laConcilia['MEDICA']),12,30);
				$lcContina = (trim($laConcilia['CONTINUA'])=='Continua'?'CONTINUAR':(trim($laConcilia['CONTINUA'])=='Suspende'?'SUSPENDER':(trim($laConcilia['CONTINUA'])=='Modifica'?'MODIFICAR':'')));
				$lcDescrip .= $lnIndice . '). - ' . trim($lcNombre) . ' ' . trim($laConcilia['DOSIS']) . ' ' . trim($laConcilia['TIPOD']) . ' Tomado Vía '. trim($laConcilia['VIA']) . ' ' . trim($laConcilia['FRECUENCIA']) . ' ' . trim($laConcilia['TIPOF']) . '.' . 'El paciente debe '. $lcContina . ' el medicamento' . $lcSL . (trim(!empty($laConcilia['OBSERVA']))?'Observaciones: ' . trim($laConcilia['OBSERVA']) . $lcSL:'');
			}
		}

		if($taDatos['Informa']=='Si'){
			$lcDescrip .= $lcSL . 'LA FUNDACION CLINICA SHAIO NO SE HACE RESPONSABLE DE LA ADMINISTRACION DE MEDICAMENTOS NATURALES, HOMEOPATICOS, TERAPIAS ALTERNATIVAS O FARMACOLOGÍA VEGETAL, DURANTE LA ESTANCIA EN LA INSTITUCION DEL PACIENTE. El Dr. informa al Paciente. ? SI';
		}
		$this->cTextoC = $lcDescrip;
	}

	public function ConciliacionAnterior($taConcilia=[])
	{
		$laConciliaM=[];
		$laIngresos = $this->oDb
			->select('NIGING INGRESO')
				->from('RIAING')
				->where(['TIDING'=>$taConcilia['tidand'],
						 'NIDING'=>$taConcilia['nidand'],
						])
				->where('NIGING', '<>', $taConcilia['ninand'])
				->orderBy ('NIGING DESC')
				->getAll('array');

		if(is_array($laIngresos)){

			foreach($laIngresos  as $laIngreso){
				if($laIngreso['INGRESO']>0){

					$laConciliaM = $this->oDb->distinct()
						->select('A.DESAND, A.CODAND, A.SANAND, A.INDAND, A.OP5AND Informante, A.FECAND, A.HORAND')
						->from('ANTPAD A')
						->where(['A.TIDAND'=>$taConcilia['tidand'],
								 'A.NIDAND'=>$taConcilia['nidand'],
								 'A.NINAND'=>$laIngreso['INGRESO'],
								 'A.CODAND'=>$taConcilia['codand'],
								 'A.INDAND'=>1,
								])
						->where('A.FECAND*1000000+A.HORAND=(SELECT MAX(F.FECAND*1000000+F.HORAND) FROM ANTPAD F WHERE F.TIDAND=A.TIDAND AND F.NIDAND=A.NIDAND AND F.NINAND=A.NINAND AND F.CODAND=17 AND F.INDAND=1)')
						->orderBy('A.SANAND')
						->getAll('array');

					if(!is_array($laConciliaM)){$laConciliaM=[];}
					if(count($laConciliaM)>0){break;}
				}
			}
		}

		$this->OrganizarConcilia($laConciliaM);
	}

	public function getTexto() {
		return trim($this->cTextoC);
	}

	public function getDatosConciliacion() {
		return $this->aDatosConsulta;
	}
}
