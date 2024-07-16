<?php

class Cabecera{

    private $ingreso;
    
    public function getIngreso(){
        return $this->ingreso;
    }

    public function setIngreso($ingreso){
        $this->ingreso = $ingreso;
    }

    public function getDatosCabeza(){
        $loHcIng = new NUCLEO\Historia_Clinica_Ingreso();
        $data= $loHcIng->datosIngreso($this->ingreso);
        return json_encode($data);
    }
}