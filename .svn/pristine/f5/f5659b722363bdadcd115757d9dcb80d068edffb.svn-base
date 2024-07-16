<?php

include_once __DIR__ .'/class.ApiAutenticacion.php';
include_once __DIR__ .'/class.Db.php';


class ConsumirServicioMicroMedex
{
    private $url = "https://mdx-shaio.meds.cognys.com/ckoapp/librarian/PFActionId/ckoapp.Request";
    private $xmlEnvio;
    private $XMLResponse;
    private $token;


    public function __construct()
    {
        global $goDb;
        $this->url = $goDb->obtenerTabMae1('TRIM(DE2TMA)', 'FORMEDIC', "CL1TMA='ALERTMED' AND CL2TMA='URL' AND ESTTMA=''", null, $this->url);
        $this->oCh = curl_init();
    }

    public function setXmlEnvio($pXmlEnvio)
    {
        $this->xmlEnvio = $pXmlEnvio;
    }

    public function getXMLResponse()
    {
        return $this->XMLResponse;
    }

    public function consumirXML()
    {
        /*
            ESTA CONFIGURACION ES PARA QUITAR LA VALIDACION SSL DEL AMBIENTE LOCAL
        */

        curl_setopt($this->oCh, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->oCh, CURLOPT_SSL_VERIFYPEER, 0);

        curl_setopt($this->oCh, CURLOPT_URL, $this->url);
        curl_setopt($this->oCh,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->oCh, CURLOPT_HTTPHEADER, array (
            "Content-Type: text/xml; charset=utf-8",
            "Expect: 100-continue",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($this->xmlEnvio)
        ));


        curl_setopt($this->oCh, CURLOPT_POST, 1);
        curl_setopt($this->oCh, CURLOPT_POSTFIELDS, $this->xmlEnvio);
        curl_setopt($this->oCh, CURLOPT_TIMEOUT_MS, 5000);
        

        $response=curl_exec($this->oCh);

        if($errno = curl_errno($this->oCh)){
            $error_message = curl_strerror($errno);

            $pnStatus= ($error_message =='Timeout was reached') ? 408 :  curl_getinfo($this->oCh, CURLINFO_HTTP_CODE); 

            $this->XMLResponse = array(
                'status'=>$pnStatus,
                'respuesta'=> $error_message ?? "Error innterno",
                'mensaje'=> $error_message ?? "Error innterno"
            );
            curl_close($this->oCh);
            return false;
        }

        $this->XMLResponse = $response;

        curl_close($this->oCh);
    }

    private function ValidarTokenAPiShaio()
    {
        $lApiAutenticacion = new ApiAutenticacion;
    }

}