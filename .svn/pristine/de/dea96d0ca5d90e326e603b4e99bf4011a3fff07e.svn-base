<?php

include_once 'class.crearXMLInteractionsMicroMedex.php';
include_once 'class.consumirServiciosMicroMedex.php';

class ConsumirXML
{

    private $oCh;
    private $XMLResponse;
    private $json;
    private $xmlCreado;
    private $consumirXml;

    private $objetosRequeridos =[
        "paciente" => ["ingreso"],
        "medicamentos"=>["nuevos", "actuales"],
        "diagnosticos"
    ];

    private $titulo =[
        "paciente" => "Error en el objeto PACIENTE",
        "ingreso" =>"Error en el dato INGRESO",
        "medicamentos" => "Error en el objeto MEDICAMENTO",
        "nuevos" => "Error en el objeto NUEVOS",
        "actuales"=> "Error el el objeto ACTUALES",
        "diagnosticos" => "Error en el objeto DIAGNÓSTICO"
    ];

    private $mensajes =[
        "paciente"=>"Error en el envío de los datos del paciente",
        "ingreso"=>"Error en el envío deL ingreso en el objeto paciente",
        "medicamentos"=>"Error en el envío de los medicamentos",
        "nuevos"=> "Error en el envío de los medicamentos nuevos",
        "actuales"=> "Error en el envío de los medicamentos actuales",
        "diagnosticos"=>"Error en el envío de los diagnósticos"
    ];

    function __construct()
    {
        $this->consumirXml = new ConsumirServicioMicroMedex;
    }

    public function setJson($pJson)
    {
        $this->json = $pJson;
    }

    public function ConsumirServicioMicroMexInteractions()
    {
        $ClaseXML = new ConstruirXmlInteractions;
        $ClaseXML->setJson($this->json);
        $ClaseXML->crearEstructuraXML();
        $this->xmlCreado = $ClaseXML->getXml();

        $this->consumirXml->setXmlEnvio($this->xmlCreado);
        $this->consumirXml->consumirXML();
        $oXMLResponse = $this->consumirXml->getXMLResponse();

        $aArraylist = [];
        $aItemsList=[];
        $itemsListValues=[];
        $aItemsTemp =[];

        if(is_array($oXMLResponse)){
            return [array(
                "TYPE"=>$oXMLResponse['status'],
                "SEVERITY"=>$oXMLResponse['respuesta'],
                "WARNINGTEXT"=>$oXMLResponse['mensaje'],
                "MONOGRAPH_ID"=>0,
                "ITEMLIST"=>[]
            )];
        }

        $doc = new DOMDocument();
        $doc->loadXML($oXMLResponse);

        $oWarning=$doc->getElementsByTagName("Warning");
        foreach ($oWarning as $Warning) {

            $oItemList = $Warning->getElementsByTagName("ItemList");

            $aItemsList=[];


            foreach ($oItemList as $itemlist) {
                $oItem= $itemlist->getElementsByTagName("Item");
                $aItemsTemp = $this->getItem($aItemsTemp, $oItem,$itemlist);
                array_push($aItemsList, $aItemsTemp);
            }


            if( count($aItemsList) > 1 ){
                $result = array_merge($aItemsList[0], $aItemsList[1]);
            }else{
                $result = $aItemsList[0];
            }

            $lDocumentation_ratingWarning = $Warning->getAttributeNode('DOCUMENTATION_RATING');
            $lIdWarning = $Warning->getAttributeNode('ID');
            $lMonograph_idWarning  = $Warning->getAttributeNode('MONOGRAPH_ID');
            $lSeverityWarning = $Warning->getAttributeNode('SEVERITY');
            $lSourceWarning  = $Warning->getAttributeNode('SOURCE');
            $lTypeWarning  = $Warning->getAttributeNode('TYPE');
            $lWarningText  = $Warning->getAttributeNode('WarningText');

            $lcDocumentation = ($lDocumentation_ratingWarning->nodeValue !='UNKNOWN') ? $lDocumentation_ratingWarning->nodeValue : 'NA' ;
            $lcSeverity = ($lSeverityWarning->nodeValue !='UNKNOWN') ? $lSeverityWarning->nodeValue : 'NA' ;

            $aWarning=array(
                "DOCUMENTATION_RATING"=>$lcDocumentation,
                "ID"=>$lIdWarning->nodeValue,
                "MONOGRAPH_ID"=>$lMonograph_idWarning->nodeValue,
                "SEVERITY"=>$lcSeverity,
                "SOURCE"=>$lSourceWarning->nodeValue,
                "TYPE"=>$lTypeWarning->nodeValue,
                "WARNINGTEXT"=>trim($Warning->textContent),
                "ITEMLIST"=>$result
            );

            array_push($aArraylist,$aWarning);
        }
        
        $lbvalidador = self::validarXMLRespuesta($oXMLResponse);

        if($lbvalidador[0] && empty($aArraylist)){
            return $lbvalidador[1];
        }


        return $aArraylist;

    }


    public function getItem($parray, $itemsList, $typetypeWarning)
    {
        $items =[];
        $arr=[];

        $ltypetypeWarning= $typetypeWarning->getAttributeNode("TYPE")->nodeValue;


        foreach ($itemsList as $value4) {

            $id = $value4->getAttributeNode("ID");
            $type = $value4->getAttributeNode("TYPE");

            array_push($items,
                array(
                    $type->nodeValue => $id->nodeValue
                )
            );

        }

        foreach ($items as $key => $item)
        {

            foreach ($item as $key => $valor) {

                $arr[$ltypetypeWarning."_".key($item)] = $valor;

            }
        }

        return $arr;
    }

    public function respuestaPeticion($pStatus, $pTitulo, $pMensajes)
    {
        return array(
            'status'=> $pStatus,
            'respuesta'=> $pTitulo,
            'mensaje'=> $pMensajes
        );
    }

    public function reglasJsonExistencia()
    {
        foreach ($this->objetosRequeridos as $key => $objetos) {

            $lBand = is_array($objetos);
            $lValidador = ($lBand) ? $key  : $objetos ;


            if(!array_key_exists($lValidador, $this->json)){
                return $this->respuestaPeticion(400,$this->titulo[$lValidador],$this->mensajes[$lValidador]);
            }

            if($lBand){
                foreach ($objetos as $key => $value) {
                    if(!array_key_exists($value, $this->json[$lValidador])){
                        return $this->respuestaPeticion(400,$this->titulo[$value],$this->mensajes[$value]);
                    }
                }
            }

        }

        return [
            "status"=>200
        ];
    }

    public function ReglasJsonGenerales()
    {
        if(count($this->json["medicamentos"]["nuevos"]) < 1){
            return $this->respuestaPeticion(406,$this->titulo["nuevos"],  "Se debe enviar un medicamento nuevo como mínimo");
        }

        if(count($this->json["diagnosticos"]) < 1){
            return $this->respuestaPeticion(406,$this->titulo["diagnosticos"],  "Se debe enviar el diagnostico principal");
        }

        return [
            'status' =>200
        ];
    }

    public function validacionIntegridadJson($pJson)
    {
        $error= json_decode($pJson);

        switch(json_last_error()) {
            case JSON_ERROR_NONE:
                return ['status'=>200];
            break;
            case JSON_ERROR_STATE_MISMATCH:
                return $this->respuestaPeticion(400,"Error en los objetos",  "Existe un error en el envio de los objetos");
            break;
            case JSON_ERROR_CTRL_CHAR:
                return $this->respuestaPeticion(400,"Error en un caracter",  "Se encontrado carácter de control no esperado");
            break;
            case JSON_ERROR_SYNTAX:
                return $this->respuestaPeticion(400,"Error en la sintaxis",  "Error de sintaxis, el JSON no se envio de forma corrrecta");

            break;
            default:
                return $this->respuestaPeticion(400,"Error desconocido",  "Error desconocido");
            break;
        }
    }

    private function validarXMLRespuesta($pXml){
        $doc = new DOMDocument();
        $doc->loadXML($pXml);
        $masResult=$doc->getElementsByTagName("ErrorList");
        $laErrores =[];
        $lbBand = false;

        if( $masResult->length >= 1 ){
            
            foreach ($masResult as $key => $result) {

                $lnCountError = $result->getAttribute("SIZE");
                
                if(intval($lnCountError) > 0){
                    $lbBand = true;
                    $errors=$result->getElementsByTagName("Error");
                    foreach ($errors as $key => $error) {
    
                        array_push($laErrores,array(
                            
                            "TYPE"=>$error->getAttribute("DRUGLIST_TYPE"),
                            "SEVERITY"=>'INVALID_INPUT '.$error->getAttribute("INVALID_INPUT"),
                            "WARNINGTEXT"=>$error->getAttribute("ERROR_TEXT"),
                            "MONOGRAPH_ID"=>0,
                            "ITEMLIST"=>[]
                            
                        ));
    
                    }
                }
            }
        }
        
        return [$lbBand, $laErrores];
    
    }

}