<?php
require_once __DIR__."/../../../controlador/class.rehabilitacionCardioVascular.php";
require_once __DIR__ .'/../../../publico/constantes.php';
require_once __DIR__ .'/../../comun/ajax/verificasesion.php';


if ($lnContinuar) {

    $proce = new NUCLEO\RehabilitacionCardioVascular();
    $rules = [
        'loCodigoAsigna',
        'claseDiagnostico',
        'selFinalidad',
        'AsisteOpc',
        'codCita',
        'HOCORD',
        'FRLORD',
        'DataClient'
    ];

    $rules_client =[
        'nIngreso',
        'cTipId',
        'nNumId'
    ];

    $mensaje = array(
        'loCodigoAsigna'=> "DIAGNOSTICO PRINCIPAL",
        'claseDiagnostico'=>"tIPO DE DIAGNOSTICO",
        'selFinalidad'=>"FINALIDAD",
        'AsisteOpc' => "ASISTENCIA",
        'codCita'=> "CODIGO DE CITA",
        'HOCORD' => "HORA SOLICITUD",
        'FRLORD'=> "FECHA SOLICITUD",
        'DataClient'=>"DATOS DEL PACIENTE",
        "nIngreso"=> "INGRESO",
        'cTipId'=> "TIPO DE DOCUMENTO DEL PACIENTE",
        'nNumId'=> "NUMERO DE DOCUMENTO DEL PACIENTE"
    );


    if($_SERVER["REQUEST_METHOD"] != 'POST'){
        echo json_encode(
            array(
                "success"=>false,
                "message"=>[
                    "title"=>'Error en el metodo',
                    "body"=>'El metodo enviado en el formulario es incorrecto'
                ]
            )
        );
        return false;
    }


    foreach ($rules as $key => $value) {
        if(!array_key_exists($value,$_POST)){
            echo json_encode(
                array(
                    "success"=>false,
                    "message"=>[
                        "title"=>'Error en los campos',
                        "body"=>'El campo '.$mensaje[$value].' es necesario para la ejecución del programa'
                    ]
                )
            );
            return false;
        }
        if($value ='DataClient'){
            foreach ($rules_client as $key1 => $value1) {
                if(!array_key_exists($value1,$_POST["DataClient"])){
                    echo json_encode(
                        array(
                            "success"=>false,
                            "message"=>[
                                "title"=>'Error en los campos',
                                "body"=>'El campo '.$mensaje[$value1].' es necesario para la ejecución del programa'
                            ]
                        )
                    );
                    return false;
                }
            }
        }
    }

    $proce->setDescri($_POST["ResultExa"]);
    $proce->setNroing($_POST["DataClient"]["nIngreso"]);
    $proce->setTidhis($_POST["DataClient"]["cTipId"]);
    $proce->setNidhis($_POST["DataClient"]["nNumId"]);
    $proce->setFechis(date("Ymd"));
    $proce->setHorhis(date("His"));
    $proce->setUsrhis($_SESSION[HCW_NAME]->oUsuario->getUsuario());
    $proce->setConcon($_POST["codCita"]);
    $proce->setHorcord($_POST["HOCORD"]);
    $proce->setFrlord($_POST["FRLORD"]);

    $array = array(
        'RegMed' => $_SESSION[HCW_NAME]->oUsuario->getRegistro(),
        'CodDxPrin' => $_POST["loCodigoAsigna"],
        'CodDxRel' => $_POST["loCodigoAsignRela"],
        'cEspMed' => $_SESSION[HCW_NAME]->oUsuario->getEspecialidad(),
        'finalidad'=> $_POST["selFinalidad"],
        'DESESP'=> $_POST["DESESP"],
        'tipoclasediagnostico' => $_POST["claseDiagnostico"],
        'asistencia' => $_POST["AsisteOpc"]
    );

    $proce->setForm($array);
    $respuestaValidacionCampos = $proce->validarCampos();

    if(!$respuestaValidacionCampos["success"]){
        echo json_encode($respuestaValidacionCampos);
        return false;
    }

    try {
        echo json_encode($proce->guardarDatos());
    }catch(Exception $e){
        echo json_encode(
            array(
                "success"=>false,
                "message"=>[
                    "title"=>'Error interno',
                    "body"=>'Hubo un error interno en el servidor, por favor contactar el área TIC'
                ]
            )
        );
    }

}
else{
    echo json_encode(
        array(
            "success"=>false,
            "message"=>[
                "title"=>'Error session',
                "body"=>'No ha iniciado sesion'
            ]
        )
    );
}