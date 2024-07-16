<?php
require_once __DIR__ . '/verificasesion.php';

if ($lnContinuar) {
    $lnTipoUsuario = isset($_GET['cambioTipo']) ? intval($_GET['cambioTipo']) : 0;
    $lcEspecialidad = isset($_GET['cambioEspecialidad']) ? $_GET['cambioEspecialidad'] : '';

    if ($_SESSION[HCW_NAME]->oUsuario->validarCambioEspecialidad($lnTipoUsuario, $lcEspecialidad)) {
        if ($_SESSION[HCW_NAME]->oUsuario->cargar($_SESSION[HCW_NAME]->oUsuario->getUsuario(), $lnTipoUsuario, $lcEspecialidad)) {
            $lcMenuCambioPerfil = sprintf(
                'Se realizó el cambio de especialidad. Se cargó el modo <b>%s - %s</b>',
                $_SESSION[HCW_NAME]->oUsuario->getTipoUsuario(true)->cNombre,
                $_SESSION[HCW_NAME]->oUsuario->getEspecialidad(true)->cNombre
            );

            echo json_encode(['mensaje' => $lcMenuCambioPerfil]);
            exit();
        }
    }
}

echo json_encode(['error' => 'No se pudo realizar el cambio de especialidad']);
?>