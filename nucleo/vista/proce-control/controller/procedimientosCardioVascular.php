<?php

class ProcedimientoCardioVascular{
    public function getprocedimientos(){
        $procedimientos =  new NUCLEO\Procedimientos;
        return json_encode($procedimientos->GetProcedimientosCardiopulmonares());
    }
}