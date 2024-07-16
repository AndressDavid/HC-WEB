<?php

require_once(__DIR__ .'/class.Db.php');

class ConstruirXmlInteractions{
    private $body;
    private $dom;
    private $version = '1.0';
    private $encoding = 'UTF-8';
    private $json;
    private $oDb;
    private $caracter ='¤';


    public function  __construct(){
		$this->oDb = new NUCLEO\Db;
        $this->dom = new DOMDocument($this->version, $this->encoding);
    }

    public function setJson($pJson){
        $this->json = $pJson;
    }

    public function getXml(){
        return $this->body;
    }

    public function crearEstructuraXML(){

        $oMasRequest= $this->dom->appendChild(new DOMElement('MasRequest'));
        $oMasRequest->setAttribute("CLASS", "PROFESSIONAL");
        $oMasRequest->appendChild($this->createPatient());
        $oMasRequest->appendChild($this->crearNewDrugListOrCurrentDrugList('NewDrugList'));
        $oMasRequest->appendChild($this->crearNewDrugListOrCurrentDrugList('CurrentDrugList'));
        $oMasRequest->appendChild($this->creatAllergenList());
        $oMasRequest->appendChild($this->crearIndicationList());
        $oMasRequest->appendChild($this->crearFilter());


        $xml = $this->dom->saveXML();
        $this->body= $xml;

    }

    private function createPatient(){

        $oPatient = $this->dom->appendChild(new DOMElement('Patient'));
        $oPatient->setAttribute("GENDER", $this->getDatosPaciente()["SEXPAC"]);
        $oPatient->setAttribute("BD_MONTH", $this->getDatosPaciente()["fechaNacimiento"]["BD_MONTH"]);
        $oPatient->setAttribute("BD_DAY", $this->getDatosPaciente()["fechaNacimiento"]["BD_DAY"]);
        $oPatient->setAttribute("BD_YEAR",$this->getDatosPaciente()["fechaNacimiento"]["BD_YEAR"]);
        $oPatient->setAttribute("LACTATING", $this->json["paciente"]["lactancia"] ?? "FALSE");
        $oPatient->setAttribute("PREGNANT", $this->json["paciente"]["embarazo"] ?? "FALSE");
        $oPatient->setAttribute("SMOKER", $this->json["paciente"]["fuma"] ?? "FALSE");
        return $oPatient;
    }

    private function crearNewDrugListOrCurrentDrugList($pTypeTag){

        $oNewDrugList = $this->dom->appendChild(new DOMElement($pTypeTag));

        $array = ($pTypeTag=='NewDrugList') ? $this->json["medicamentos"]["nuevos"] : $this->json["medicamentos"]["actuales"];
        $oNewDrugList->setAttribute("SIZE", count($array));


        foreach ($array as $key => $value) {

            $aExplodeMedicamentos = explode($this->caracter,trim($this->getMedicamentoHomologado($key)["CL20DES"]) );

            foreach ($aExplodeMedicamentos as $key2 => $homologado) {
                $oDrug = $this->dom->appendChild(new DOMElement("Drug"));
                $oDrug->setAttribute("CODE", $homologado);
                $oDrug->setAttribute("TYPE", "GFC");
                $oDrug->setAttribute("ORDER_ID", $key."|".htmlentities($value)."|".$homologado);

                $oNewDrugList->appendChild($oDrug);
            }


        }
        return $oNewDrugList;
    }
    private function creatAllergenList(){

        $oAllergenList = $this->dom->appendChild(new DOMElement("AllergenList"));
        $oAllergenList->setAttribute("SIZE", "0");

        return $oAllergenList;

    }

    private function crearIndicationList(){

        $aIndication = $this->json["diagnosticos"];

        $oIndicationList = $this->dom->appendChild(new DOMElement("IndicationList"));
        $oIndicationList->setAttribute("SIZE", count($aIndication));


        foreach ($aIndication as $key => $indication) {
            $oIndication = $this->dom->appendChild(new DOMElement("Indication"));
            $oIndication->setAttribute("CODE", $this->crearCodigoPunto($indication)?? 0);
            $oIndication->setAttribute("TYPE", "ICD_10");

            $oIndicationList->appendChild($oIndication);
        }

        return $oIndicationList;

    }

    private function crearCodigoPunto($pCodigo){

        $lCount= strlen($pCodigo);
        $lUltimoCaracter= $pCodigo[$lCount-1];

        $pCodigo[$lCount-1]= ".";
        $pCodigo[$lCount]= $lUltimoCaracter;

        return $pCodigo;

    }

    private function crearFilter(){
        $aTypeFilter =[
            "DRUG","LAB","FOOD","ETHANOL","TOBACCO","PRECAUTIONS",
            "PATIENT_DISEASE","PROXY_DISEASE","LACTATION","PREGNANCY",
            "ALLERGY","INGREDIENT_DUPLICATION","ANTAGONISM","TC_DUPLICATION"
        ];

        $oFilter = $this->dom->appendChild(new DOMElement("Filter"));


        foreach ($aTypeFilter as $key => $filter) {
            $oTypeFilter = $this->dom->appendChild(new DOMElement("TypeFilter"));
            $oTypeFilter->setAttribute("NAME", $filter);

            $oFilter->appendChild($oTypeFilter);
        }

        return $oFilter;
    }

    private function getMedicamentoHomologado($pMedicamento){
        $aMedicamentos = $this->oDb
            ->select("CL20DES")
            ->from('INVATTR')
            ->where("REFDES", "=", $pMedicamento)
            ->get('array');

            if($this->oDb->numRows() < 1){
                return [
                    'CL20DES' => 999999
                ];
            }

            if(trim($aMedicamentos['CL20DES']) == ''){
                return [
                    'CL20DES' => 999999
                ];
            }

        return $aMedicamentos;
    }

    private function getDatosPaciente(){
        $oDatosPaciente = $this->oDb->select("RP.SEXPAC, RP.FNAPAC")
        ->from("RIAPAC RP")
        ->leftjoin("RIAING RI", "RI.TIDING = RP.TIDPAC AND RI.NIDING = RP.NIDPAC ")
        ->where("RI.NIGING", "=", $this->json["paciente"]["ingreso"])
        ->get("array");

        $oDatosPaciente["fechaNacimiento"] = array(
            'BD_MONTH'=>substr($oDatosPaciente["FNAPAC"], 4, -2),
            'BD_DAY'=>substr($oDatosPaciente["FNAPAC"], 6, 7),
            'BD_YEAR'=>substr($oDatosPaciente["FNAPAC"], 0, 4)
        );
        return $oDatosPaciente;
    }

}