<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Turnos.php';

class AplicacionEnfermeria extends Turnos
{


    const cCampoMayorMenor = 'OP2TMA';
    const cCampoSegContante = 'OP3TMA';
    const cCampoSegInicial = 'OP4TMA';
    const cCampoTabMae = 'INTVAL';
    const cCampoUsuario = "DE1TMA";
    const cClasificacion = 'CL1TMA';
    const cMayorMenor = 'down';
    const cNotasAutomaticas = 'OP1TMA';
    const cPrograma = "MONITOR";
    const cRegistrarNotas = 'OP5TMA';
    const cTiptma = 'NOTASENF';
    const cUserTabMae = 'DRAGER';
    const cUsuario = "SRV_WEB";
    const nConstRegistro = 3600;
    const nIniRegistro = 900;


    private $lcIntvalMayorMenor;
    private $lcPrograma;
    private $lcUser;
    private $lcUserPrograma;
    private $lnCantidadEstado;
    private $lnCantRegistrosIniciales;
    private $lnIntvalConstanteRegistro;
    private $lnIntvalInicialRegistro;
    private $oDB;
    public $laNotasAutomaticas;
    public $laNotasAutomaticasSeccion;

    function __construct()
    {
        global $goDb;
        $this->oDB = $goDb;
        parent::__construct();

        $this->lcIntvalMayorMenor = $this->obtenerValTabMae(self::cCampoMayorMenor, self::cMayorMenor);
        $this->lnIntvalConstanteRegistro = (int) $this->obtenerValTabMae(self::cCampoSegContante, self::nConstRegistro);
        $this->lnIntvalInicialRegistro = (int) $this->obtenerValTabMae(self::cCampoSegInicial, self::nIniRegistro);
        $this->lcUserPrograma = $this->obtenerValTabMae(self::cCampoUsuario, self::cUsuario, true);
        $this->laNotasAutomaticas = $this->obtenerValTabMae(self::cNotasAutomaticas, 1, true);
        $this->laNotasAutomaticasSeccion = $this->obtenerValTabMae(self::cRegistrarNotas, 1, true);

        $this->calcularCantidadRegistrosIniciales();
    }

    private function obtenerValTabMae(string $tcCampo, string $tnValDefecto, bool $isUser = false)
    {
        $lcCampo = self::cCampoTabMae;
        if ($isUser)
            $lcCampo = self::cUserTabMae;

        $loValue = $this->oDB->ObtenerTabMae($tcCampo, self::cTiptma, [self::cClasificacion => $lcCampo]);
        if (empty($loValue))
            return $loValue = $tnValDefecto;
        foreach ($loValue as $value)
            return $loValue = trim($value);
    }
    private function calcularCantidadRegistrosIniciales()
    {
        $this->lnCantRegistrosIniciales = $this->lnIntvalConstanteRegistro / $this->lnIntvalInicialRegistro;
    }

    /**
     * Metodo encargado de validar si el registro de la trama se debe ingresar con base en los signos vitales ya registrados en las notas de enfermeria
     * 
     * @param array $taRegistroSignos
     * @param int $tnFecha
     * @param int $tnHora
     * @return void
     */
    public function validarRegistroNotas(array $taRegistroSignos, string $tnFecha, string $tnHora): bool
    {
        $lnRegistroNuevo = $tnFecha . $this->roundHour($tnHora);
        $lnRegistroExistente = $taRegistroSignos['FDISIG'] . $this->roundHour($taRegistroSignos['HDISIG']);

        if ($lnRegistroExistente >= $lnRegistroNuevo)
            return false;

        $llEsHora = $this->diferenciaHoras($lnRegistroExistente, $lnRegistroNuevo);

        if ((intval($taRegistroSignos['ESTSIG']) < $this->lnCantRegistrosIniciales && intval($taRegistroSignos['ESTSIG']) != 0) || $llEsHora[0]) {
            $this->lnCantidadEstado = ($llEsHora[0]) ? 1 : intval($taRegistroSignos['ESTSIG']) + 1;
            return true;
        } else {
            if ($llEsHora[1]) {
                $this->lnCantidadEstado = "00";
                return true;
            }
        }

        return false;
    }
    private function diferenciaHoras($tnStartDate, $tnEndDate): array
    {
        $ldStartTimeStamp = strtotime($tnStartDate);
        $ldEndTimeStamp = strtotime($tnEndDate);
        $lndiferencia = abs($ldEndTimeStamp - $ldStartTimeStamp);
        return array(
            $lndiferencia > $this->lnIntvalConstanteRegistro ? true : false,
            $lndiferencia == $this->lnIntvalConstanteRegistro ? true : false,

        );
    }

    private function roundHour(string $tcHour): string
    {
        $lcNewHour = $tcHour;
        while (strlen($lcNewHour) <= 5) {
            $lcNewHour = "0" . $lcNewHour;
        }
        return $lcNewHour;
    }

    public function roundTime(string $tcTime)
    {
        $laRound = array('auto' => 'round', 'up' => 'ceil', 'down' => 'floor');
        $laRound = @$laRound[$this->lcIntvalMayorMenor] ? $laRound[$this->lcIntvalMayorMenor] : 'down';
        $seconds = $this->lnIntvalInicialRegistro;
        if (substr_count($tcTime, ":") == 2)
            return date('His', $laRound(strtotime($tcTime) / $seconds) * $seconds);
        else
            return date('His', $laRound(strtotime($tcTime) / $seconds) * $seconds);
    }




    public function obtenerUsuario(): string
    {
        $this->lcUser = explode("¤", $this->lcUserPrograma);
        return $this->lcUser[0];
    }
    public function obtenerPrograma(): string
    {
        $this->lcPrograma = explode("¤", $this->lcUserPrograma);
        return $this->lcPrograma[1];
    }

    public function obtenerEstado()
    {
        return $this->lnCantidadEstado;
    }
}