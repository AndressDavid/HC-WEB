<?php

require_once __DIR__ . '/verificasesion.php';
require_once __DIR__ . '/../../../controlador/class.Db.php';

$resultado = $_SESSION[HCW_NAME]->oUsuario->getOpcionesMenu();

$nuevaEstructura = [];
$submenu = [];
$nuevoItem=[];

foreach ($resultado as $item) {
    $nuevoItem = [
        "APPID" => trim($item["APPID"]),
        "MENU" => trim($item["MENU"]),
        "MENUID" => trim($item["MENUID"]),
        "MENUOF" => trim($item["MENUOF"]),
        "MENUTYPE" => trim($item["MENUTYPE"]),
        "MENUORD" => trim($item["MENUORD"]),
        "CMD" => strtolower(trim($item["CMD"])),
        "PROMPT" => trim($item["PROMPT"]),
        "TOOLBAR" => trim($item["TOOLBAR"]),
        "MESSAGE" => trim($item["MESSAGE"]),
        "PICTURE" => trim($item["PICTURE"]),
    ];

    $nuevoItem["PROMPT"] = str_replace('\-', '', $nuevoItem["PROMPT"]);
    $nuevoItem["CMD"] = str_replace('\-', '', $nuevoItem["CMD"]);

    if (!empty($item["MENUID"]) && is_numeric($item["MENUID"])) {
        $submenu = [];
        foreach ($item as $subitem1) {
            if (!empty($subitem1["MENUOF"])) {
                $nuevoSubitem = array(
                    "APPID" => trim($subitem1["APPID"]),
                    "MENU" => trim($subitem1["MENU"]),
                    "MENUID" => trim($subitem1["MENUID"]),
                    "MENUOF" => trim($subitem1["MENUOF"]),
                    "MENUTYPE" => trim($subitem1["MENUTYPE"]),
                    "MENUORD" => trim($subitem1["MENUORD"]),
                    "CMD" => strtolower(trim($subitem1["CMD"])),
                    "PROMPT" => str_replace('\-', '', trim($subitem1["PROMPT"])),
                    "TOOLBAR" => trim($subitem1["TOOLBAR"]),
                    "MESSAGE" => trim($subitem1["MESSAGE"]),
                    "PICTURE" => trim($subitem1["PICTURE"]),
                    "submenu2" => submenu2($subitem1)
                );

                array_push($submenu, $nuevoSubitem);
                $nuevoItem["submenu"] = $submenu;
            }
        }
    }
    
    array_push($nuevaEstructura, $nuevoItem);
    $nuevoItem=[];
}

function submenu2($taSubmenu){
    $laArrayReturn = [];
    $laItemsubmenu1 = [];

    foreach ($taSubmenu as $subitem1) {
        if( is_array($subitem1) ){
            $laItemsubmenu1 = array(
                "APPID" => trim($subitem1["APPID"]),
                "MENU" => trim($subitem1["MENU"]),
                "MENUID" => trim($subitem1["MENUID"]),
                "MENUOF" => trim($subitem1["MENUOF"]),
                "MENUTYPE" => trim($subitem1["MENUTYPE"]),
                "MENUORD" => trim($subitem1["MENUORD"]),
                "CMD" => strtolower(trim($subitem1["CMD"])),
                "PROMPT" => str_replace('\-', '', trim($subitem1["PROMPT"])),
                "TOOLBAR" => trim($subitem1["TOOLBAR"]),
                "MESSAGE" => trim($subitem1["MESSAGE"]),
                "PICTURE" => trim($subitem1["PICTURE"]),
            );
        }

        if (!empty($laItemsubmenu1)) {
            array_push($laArrayReturn, $laItemsubmenu1);
        }
    }

    return $laArrayReturn;
}

echo json_encode($nuevaEstructura, JSON_PRETTY_PRINT);
?>