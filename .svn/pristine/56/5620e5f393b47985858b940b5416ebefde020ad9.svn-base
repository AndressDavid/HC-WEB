<?php

namespace NUCLEO;

require_once __DIR__ . '/class.AplicacionFunciones.php';
require_once __DIR__ . '/class.Cobros.php';
require_once __DIR__ . '/class.Historia_Clinica_Ingreso.php';
require_once __DIR__ . '/class.Medico.php';

class RehabilitacionCardioVascular{
    private $nroing;
    private $concon;
    private $indice = 70;
    private $suborg =933601;
    private $tidhis;
    private $nidhis;
    private $usrhis;
    private $pgmhis = "RIA022W";
    private $fechis;
    private $horhis;
    private $consec = "900001";
    private $descri;
    private $horcord;
    private $frlord;
    private $oMaco = 0;

    /// VARIABLE FORM
    private $form = [];

	public function __construct()
	{
		global $goDb;
		$this->oDb = $goDb;
	}
    //// SET

    public function setHorcord($horcord){
        $this->horcord =$horcord;
    }
    public function setFrlord($frlord){
        $this->frlord = $frlord;
    }
    public function setDescri($descri){
        $this->descri = $descri;
    }

    public function setNroing($nroing){
        $this->nroing = $nroing;
    }

    public function setSuborg($suborg){
        $this->suborg = $suborg;
    }

    public function setTidhis($tidhis){
        $this->tidhis = $tidhis;
    }

    public function setNidhis($nidhis){
        $this->nidhis = $nidhis;
    }

    public function setUsrhis($usrhis){
        $this->usrhis = $usrhis;
    }

    public function setFechis($fecha){
        $this->fechis = $fecha;
    }

    public function setHorhis($horas){
        $this->horhis = $horas;
    }

    public function setConcon($concon){
        $this->concon = $concon;
    }

    public function setForm($form){
        $this->form = $form;  
    }

    public function CrearArrayDescripcion(){
        $array = [];
        $laChar = AplicacionFunciones::mb_str_split(trim($this->descri),$this->indice);

        foreach ($laChar as $key => $value) {
            $array_temp = array(
                "NROING"=>$this->nroing,
                "CONCON"=>$this->concon,
                "INDICE"=>$this->indice,
                "SUBORG"=>$this->suborg,
                "TIDHIS"=>$this->tidhis,
                "NIDHIS"=>$this->nidhis,
                "USRHIS"=>$this->usrhis,
                "PGMHIS"=>$this->pgmhis,
                "FECHIS"=>$this->fechis,
                "HORHIS"=>$this->horhis,
                "CONSEC"=>$key+1,
                "DESCRI"=>$value);
            array_push($array, $array_temp);
        }

        return $array;
    }

    public function createFormArray(){
        $text = str_pad(trim($this->form["RegMed"]),13,' ',STR_PAD_RIGHT) . ' ';
        $text = $text.str_pad(trim($this->form["CodDxPrin"]),4,"0",STR_PAD_LEFT).' ';
        $text = $text.str_pad(trim($this->form["CodDxRel"]),4,"0",STR_PAD_LEFT).' ';
        $text = $text.str_pad(trim($this->form["cEspMed"]),3,"0",STR_PAD_LEFT)." ";
        $text = $text.str_pad(trim($this->form["finalidad"]),4," ",STR_PAD_LEFT)." ";
        $text = $text.str_pad(trim($this->form["tipoclasediagnostico"]),4," ",STR_PAD_LEFT).' ';
        $text = $text.str_pad(trim($this->form["asistencia"]),2," ",STR_PAD_LEFT);

        return array(
            "NROING"=>$this->nroing,
            "CONCON"=>$this->concon,
            "INDICE"=>$this->indice,
            "SUBORG"=>$this->suborg,
            "TIDHIS"=>$this->tidhis,
            "NIDHIS"=>$this->nidhis,
            "USRHIS"=>$this->usrhis,
            "PGMHIS"=>$this->pgmhis,
            "FECHIS"=>$this->fechis,
            "HORHIS"=>$this->horhis,
            "CONSEC"=>$this->consec,
            "DESCRI"=>$text
        );

    }

    public function guardarDatos(){
        $result = false;

        if(!$this->ValidarEstadoOrd()){
           
            return array(
                "success"=>false,
                "message"=>[
                    "title"=>'Procedimiento finalizado',
                    "body"=>'El procedimiento esta atendido y tiene un reporte final en el momento'
                ]
            );
        }

        if($this->validarConsecutivos()){
            $result  = $this->actualizarDatosRiaHis();
        }
        else{
            $result = $this->insertarDatosRiaHis();

        }

        if($result){
            $result = $this->actualizarRiaOrdl25();
        }
        if($result){
            $result = $this->actualizarDatosRiacIt();
        }

        $estado = trim($this->recuperarEstado($this->form["asistencia"])["OP2TMA"]);

        if($result && $estado == 3){
            $this->generarCobros();   
        }

        if($result){
            $result = $this->crearRiadet();
        }

        if($result){
            return array(
                "success"=>true,
                "message"=>[
                    "title"=>'Solicitud realizada',
                    "body"=>'Se guardo con exito el procedimiento'
                ]
            );
        }else{
            return array(
                "success"=>false,
                "message"=>[
                    "title"=>'Error interno',
                    "body"=>'Hubo un error interno en el servidor, por favor contactar el área TIC'
                ]
            );
        }

    }

    public function insertarDatosRiaHis(){
        $resultInsertDescrip=false;

        $arrayform = $this->createFormArray();

        $resultInsertForm= $this->oDb->from("riahis")->insertar($arrayform);
        if($resultInsertForm){

            if($this->descri !=''){
                foreach ($this->CrearArrayDescripcion() as $key => $value) {
                    $resultInsertDescrip= $this->oDb->from("riahis")->insertar($value);
                }
            }else{
                $resultInsertDescrip = true;
            }

        }

        return $resultInsertDescrip;
    }

    public function actualizarDatosRiaHis(){
        $result2 = false;
        $delete = $this->oDb->from("riahis")
        ->where("CONCON", "=",$this->concon)
        ->where("NROING", "=",$this->nroing)
        ->where("INDICE", "=", 70)
        ->where("suborg","=", $this->suborg)
        ->where("subind", "=",0)
        ///->getAll("array");
       ->eliminar();

        if($delete){
            $arrayform = $this->createFormArray();
            $result= $this->oDb->from("riahis")->insertar($arrayform);
            if($result){

                if($this->descri !=''){
                    foreach ($this->CrearArrayDescripcion() as $key => $value) {
                        $result2= $this->oDb->from("riahis")->insertar($value);
                    }
                }
                else{
                    $result2 = true;
                }
            }
        }
        return $result2;
    }

    public function validarConsecutivos(){
        $datosWhere = array(
            'CONCON'=>$this->concon,
            'NROING' =>$this->nroing,
            'INDICE' =>70
        );

        $row = $this->oDb->select("CONCON")
            ->from("riahis")
            ->where($datosWhere)
            ->getAll('array');
        if(count($row)>0){
            return true;
        }else{
            return false;
        }
    }

    private function RecuperarConsecutivo(){
        $datosWhere = array(
            'CONCON'=>$this->concon,
            'NROING' =>$this->nroing,
            'INDICE'=> $this->indice
        );

        $datos = $this->oDb->select("PGMHIS")
        ->from("riahis")
        ->where($datosWhere)
        ->get("array");

        if(!$datos){
            return $this->consec;
        }

        if(trim($datos["PGMHIS"]) == "RIA022W" ){
            return $this->consec;
        }

        return 101;

    }

    public function recuperarInformacionForm(){

        $datosWhere = array(
            'CONCON'=>$this->concon,
            'NROING' =>$this->nroing,
            'CONSEC' => $this->RecuperarConsecutivo(),
            'INDICE'=> $this->indice
        );

        $datos = $this->oDb->select("DESCRI")
        ->from("riahis")
        ->where($datosWhere)
        ->getAll("array");

        
        if(!empty($datos)){
            $text= strtr($datos[0]["DESCRI"]," ","|");
            $arrayRecuperado = explode("|", $text);

            $estado = $this->ValidarEstadoOrd();
            $DxPrnicipal=  $this->recueperarInformacionDx($arrayRecuperado[1]);
            $DxRelacionado=  $this->recueperarInformacionDx($arrayRecuperado[2]);
            $lCodigoEstado = trim($this->recuperarEstado($arrayRecuperado[11])['OP2TMA']);
            $claseDiag = $arrayRecuperado[10];
            $lfinalidad = $arrayRecuperado[6];
            $lcAsistencia = $arrayRecuperado[11];

            $loMedicoSolicitoInterconsulta =  new Medico($arrayRecuperado[0]);
            $lcNombremedico =  $loMedicoSolicitoInterconsulta->getNombreCompleto();
        }
        else{
            $DxPrnicipal= array(
                "ENFRIP"=>'',
                "DESRIP"=>''
            );
            $DxRelacionado= array(
                "ENFRIP"=>'',
                "DESRIP"=>''
            );
            $estado = $this->ValidarEstadoOrd();
            $claseDiag = 0;
            $lfinalidad= 0;
            $lCodigoEstado =0;
            $lcAsistencia  = 0;
            $lcNombremedico="N/A";

        }

        $arrayFinal=array(
            "CodDxPrin"=>trim($DxPrnicipal["ENFRIP"]),
            "CodDxPrinDesc"=>trim($DxPrnicipal["DESRIP"]),
            "CodDxRel"=>trim($DxRelacionado["ENFRIP"]),
            "CodDxRelDesc"=>trim($DxRelacionado["DESRIP"]),
            "Descripcion"=>trim($this->recuperarDescripcion()),
            "estado"=>$estado,
            "codigoEstado"=>$lCodigoEstado,
            "claseDiag"=>$claseDiag,
            "finalidad"=>$lfinalidad,
            "asistencia"=>$lcAsistencia,
            "nombremedico"=>$lcNombremedico
        );

        return $arrayFinal;

    }

    public function ValidarEstadoOrd(){
        $datosWhere = array(
            'CCIORD'=>$this->concon,
            'NINORD' =>$this->nroing,
        );

        $datos = $this->oDb->select("NINORD")
        ->from("RIAORDL25")
        ->where($datosWhere)
        ->where('ESTORD', '<>',3)
        ->getAll("array");

        if(!$datos){
            return false;
        }
        return true;
    }

    public function recuperarDescripcion(){
        $datosWhere = array(
            'CONCON'=>$this->concon,
            'NROING' =>$this->nroing,
        );

        $lConce = $this->RecuperarConsecutivo();

        $datos = $this->oDb->select("DESCRI")
        ->from("riahis")
        ->where($datosWhere)
        ->where('CONSEC','<>',$lConce)
        ->getAll("array");

        $text="";
        foreach ($datos as $key => $value) {
            $text= $text.$value["DESCRI"];
        }

        return $text;

    }
    public function recuperarEstado($pAsiste){

        $where = array(
            "CL3TMA"=>$pAsiste,
            "TIPTMA"=>"PROCORD"
        );

        $datos= $this->oDb->select("OP2TMA, OP1TMA")
        ->from("TABMAE")
        ->where($where)
        ->get("array");

        if(empty($datos)){
            return array(
                "OP2TMA"=> 8,
                "OP1TMA"=>1
            );
        }

        return $datos;
    }

    public function recueperarInformacionDx($codigo){
        $datos = $this->oDb->select("ENFRIP, DESRIP")
        ->from("RIACIE")
        ->where(["ENFRIP"=>$codigo])
        ->get("array");

        if(empty($datos)){
            return array(
                "ENFRIP"=>"",
                "DESRIP"=>""
            );
        }
        return $datos;
    }
    public function actualizarRiaOrdl25(){
        $arrayUpdate = [];
        $array_temp = array(
            "ESTORD"=>trim($this->recuperarEstado($this->form["asistencia"])["OP2TMA"]),
            "CODORD"=>$this->form["cEspMed"],
            "RMRORD"=>$this->form["RegMed"],
            "FERORD"=>$this->getFechaOrden(),
            "HRLORD"=>$this->getHoraOrden(),
            "UMOORD"=>$this->usrhis,
            "PMOORD"=>$this->pgmhis,
            "FMOORD"=>$this->fechis,
            "HMOORD"=>$this->horhis
        );

        if(trim($this->recuperarEstado($this->form["asistencia"])["OP2TMA"]) == 3){
            $arrayUpdate = array_merge($array_temp, [
                'FERORD'=>$this->fechis,
                'HRLORD'=>$this->horhis]
            );
        }else{
           $arrayUpdate = $array_temp;
        }

        $update = $this->oDb->from("RIAORDL25")
        ->where("TIDORD","=",$this->tidhis)
        ->where("NIDORD","=",$this->nidhis)
        ->where("NINORD","=",$this->nroing)
        ->where("CCIORD","=", $this->concon)
        ->where("COAORD","=", $this->suborg)
        ->actualizar($arrayUpdate);

        return $update;
    }

    public function getHoraOrden(){
        if($this->horcord == '' || $this->horcord == null || !isset($this->horcord)){
            return date("His");
        }
        
        return $this->horcord;
    } 
    public function getFechaOrden(){
        if($this->frlord == '' || $this->frlord == null || !isset($this->frlord)){
            return date("Ymd");
        }
        return $this->frlord;
    } 

    public function actualizarDatosRiacIt(){

        $validar = $this->oDb->select("NINCIT")
        ->from("RIACIT")
        ->where("TIDCIT","=",$this->tidhis)
        ->where("NIDCIT","=",$this->nidhis)
        ->where("CCICIT","=", $this->concon)
        ->where("NINCIT","=",$this->nroing)
        ->get("array");

        if(empty($validar) || !$validar  ){
            return true;
        }

        $arrayUpdate = array(
            "ESTCIT"=>trim($this->recuperarEstado($this->form["asistencia"])["OP2TMA"]),
            "UMOCIT"=>$this->usrhis,
            "PMOCIT"=>$this->pgmhis,
            "FMOCIT"=>$this->fechis,
            "HMOCIT"=>$this->horhis
        );

        $update = $this->oDb
        ->from("RIACIT")
        ->where("TIDCIT","=",$this->tidhis)
        ->where("NIDCIT","=",$this->nidhis)
        ->where("CCICIT","=", $this->concon)
        ->where("NINCIT","=",$this->nroing)
        ->actualizar($arrayUpdate);

        return $update;
    }

    public function generarCobros(){

        $loHcIng = new Historia_Clinica_Ingreso();
        $cobro = new Cobros;
        $datosIngreso= $loHcIng->datosIngreso($this->nroing);

        $filtro = "(CUPEST='".$this->suborg."' OR ELEEST='".$this->suborg."')";
        $validarCobro = $this->oDb->select("ingest, cnsest")
        ->from("riaestm20")
        ->where("INGEST", "=",$this->nroing)
        ->where($filtro)
        ->where("CNOEST", "=",$this->concon)
        ->get("array");

        if(!empty($validarCobro)){
            $resultRia= $this->actualizarRiaEstm20();

            if($resultRia){
                $resultadoFac=$this->actualizarFacdetfl($validarCobro["CNSEST"]);
                return $resultadoFac;
            }
        }

        $arrayCobro = array(
            'ingreso'=>$this->nroing,
            'numIdPac'=>$this->nidhis,
            'codCup'=>$this->suborg,
            'codVia'=>$datosIngreso["cCodVia"],
            'codPlan'=>$datosIngreso["cPlan"],
            'regMedOrdena'=> $this->getRmeOrd(),
            'regMedRealiza'=>$this->form["RegMed"],
            'espMedRealiza'=>$this->form["cEspMed"],
            'secCama'=> $datosIngreso["cSeccion"].' '.$datosIngreso["cHabita"],
            'cnsCita'=>$this->concon,
            'portatil'=>''
        );

        $respuesta = $cobro->cobrarProcedimiento($arrayCobro);

        if($respuesta){
            $this->oMaco=1;
            return true;
        }

        return false;
    }

    public function getRmeOrd(){
        $datosWhere = array(
            'CCIORD'=>$this->concon,
            'NINORD' =>$this->nroing
        );

        $row = $this->oDb->select("RMEORD")
            ->from("RIAORD")
            ->where($datosWhere)
            ->get('array');
        
        if(!$row){
            return '';
        }
        return  $row['RMEORD'];

    }

    public function actualizarRiaEstm20(){
        $arrayUpdate = array(
            "RMEEST"=>$this->form["RegMed"],
            "DPTEST"=>$this->form["cEspMed"],
            "UMOEST"=>$this->usrhis,
            "PMOEST"=>$this->pgmhis,
            "FMOEST"=>$this->fechis,
            "HMOEST"=>$this->horhis
        );

        $sql= $this->oDb->from("riaestm20")
        ->where("ingest","=",$this->nroing)
        ->where("cupest","=",$this->suborg)
        ->where("cnoest","=",$this->concon)
        ->actualizar($arrayUpdate);

        return $sql;
    }

    public function actualizarFacdetfl($cnsest){

        $arrayUpdate = array(
            "RMEDFA"=>$this->form["RegMed"],
            "DPTDFA"=>$this->form["cEspMed"],
            "UMODFA"=>$this->usrhis,
            "PMODFA"=>$this->pgmhis,
            "FMODFA"=>$this->fechis,
            "HMODFA"=>$this->horhis
        );

        $sql = $this->oDb->from("facdetfl")
        ->where("ingdfa","=",$this->nroing)
        ->where("cupdfa","=",$this->suborg)
        ->where("cnsdfa","=",$cnsest)
        ->actualizar($arrayUpdate);
        
        return $sql;
    }

    public function crearRiadet(){
        $arrayInser = array(
            "TIDDET"=>$this->tidhis,
            "NIDDET"=>$this->nidhis,
            "INGDET"=>$this->nroing,
            "CCIDET"=>$this->concon,
            "CUPDET"=>$this->suborg,
            "FERDET"=>$this->fechis,
            "HRRDET"=>$this->horhis,
            "ESTDET"=>trim($this->recuperarEstado($this->form["asistencia"])["OP2TMA"]),
            "MARDET"=>$this->oMaco,
            "USRDET"=>$this->usrhis,
            "PGMDET"=>$this->pgmhis,
            "FECDET"=>$this->fechis,
            "HORDET"=>$this->horhis
        );

        $result= $this->oDb->from("riadet")->insertar($arrayInser);

        return $result;

    }

    public function validarCampos(){
        $success = true;
        $arrayError = array(
            "success"=>$success,
            "message"=>[
                "title"=>'Datos correctos',
                "body"=>'Los datos estan correctos'
            ]
        );

        if($this->form["CodDxPrin"] == ''){
            $arrayError["success"] = false;
            $arrayError["message"]["title"]="Error en el campo diagnostico principal";
            $arrayError["message"]["body"]="El diagnostico principal es obligatorio para registrar la rehabilitación.";
        }

        if($this->form["tipoclasediagnostico"] == ''){
            $arrayError["success"] = false;
            $arrayError["message"]["title"]="Error en el campo tipo de diagnostico principal";
            $arrayError["message"]["body"]="El tipo de diagnostico principal es obligatorio para registrar la rehabilitación.";
        }

        if($this->form["asistencia"] ==''){
            $arrayError["success"] = false;
            $arrayError["message"]["title"]="Error en el campo asistencia";
            $arrayError["message"]["body"]="El campo asistencia es obligatorio para registrar la rehabilitación.";
        }
        if($this->form["finalidad"] ==''){
            $arrayError["success"] = false;
            $arrayError["message"]["title"]="Error en el campo finalidad";
            $arrayError["message"]["body"]="El campo finalidad es obligatorio para registrar la rehabilitación.";
            
        }
        return $arrayError;

    }

}