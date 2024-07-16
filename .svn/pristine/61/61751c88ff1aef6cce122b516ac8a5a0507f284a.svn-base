<?php
include_once 'class.consumirServiciosMicroMedex.php';


class ConsumirXmlDetalle
{
    private $xmlRespuesta;
    private $xmlEnvio;
    private $idReferencia;
    private $dom;
    private $consumirXml;
    private $version = '1.0';
    private $encoding = 'UTF-8';

    public function __construct()
    {
        $this->dom = new DOMDocument($this->version, $this->encoding);
        $this->consumirXml = new ConsumirServicioMicroMedex;
    }

    public function setidReferencia($pIdReferencia)
    {
        $this->idReferencia = $pIdReferencia;
    }

    public function construirXmlDetalle()
    {
        /* SE CREA LA ETIQUETA DocumentRequest */
        $oDocumentRequest=$this->dom->appendChild(new DOMElement('DocumentRequest'));

        /* SE CREA LA ETIQUETA DocumentList CON SUS ATRIBUTOS */
        $oDocumentList= $this->dom->appendChild(new DOMElement('DocumentList'));
        $oDocumentList->setAttribute("SIZE", "1");

        /* SE CREA LA ETIQUETA Document CON SUS ATRIBUTOS Y LA REFERENCIA ID */
        $oDocument= $this->dom->appendChild(new DOMElement('Document'));
        $oDocument->setAttribute("ID", $this->idReferencia);
        $oDocument->setAttribute("TYPE", "MONOGRAPH");

        /**  SE REALIZA LA INSERCION DE LAS ETIQUETAS DONDE CORRESPONEN */
        $oDocumentRequest->appendChild($oDocumentList);
        $oDocumentList->appendChild($oDocument);

        $loXml = $this->dom->saveXML();
        $this->xmlEnvio= $loXml;
    }

    public function consumirServioDetalle()
    {
        $this->consumirXml->setXmlEnvio($this->xmlEnvio);
        $this->consumirXml->consumirXML();
        $loXMLResponse = $this->consumirXml->getXMLResponse();

        $doc = new DOMDocument();
        $doc->loadXML($loXMLResponse);

        $loDocumentResult = $doc->getElementsByTagName("DocumentResult");

        return array(
                "body"=> base64_encode(trim($doc->textContent))
            );
    }

}