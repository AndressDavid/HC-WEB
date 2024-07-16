<?php

namespace NUCLEO;

require_once __DIR__ . '/class.Db.php';

abstract class Turnos
{
    const lcTableMaestra = "TABMAE";
    const lcTableNot = "NCSNOT";
    const lcTipo = "TURNOSEN";
    private $lcCambio;
    private $lcEstado;
    private $lnHoraFin;
    private $lnHoraIni;
    private $lnIngreso;
    protected $oDb;

    public function __construct()
    {
        global $goDb;
        $this->oDb = $goDb;
    }



    /*    ***************************************************************************************************************
     *-- Función     :	EstadoTurnoUnidades
     *-- Definición  :	Devuelve el estado del Turno 'C' CERRADO O 'A' ABIERTO  de las unidades
     *-- Fecha Creado:  	25/01/2023
     *-- Creado Por  :	Joan Oliveros
     ****************************************************************************************************************/

    public function EstadoTurnoUnidades(int $tnIngreso): string
    {
        $this->lnIngreso = trim($tnIngreso);

        $laRegistro = $this->oDb->select('*')
            ->from(self::lcTableNot)
            ->where('INGNOT', '=', $this->lnIngreso)
            ->orderBy('CONNOT DESC')
            ->get("array");
        if (!empty($laRegistro)) {
            if (trim($laRegistro['NTANOT']) == "S" && trim($laRegistro['ADMNOT']) == "1") {
                return $this->lcEstado = "C";
            }
            if (trim($laRegistro['NTANOT']) == "S" && empty(trim($laRegistro['ADMNOT']))) {
                return $this->lcEstado = "A";
            }
            return $this->lcEstado = "";
        }

        return $this->lcEstado = "C";
    }

    /**
     * ***************************************************************************************************************
     *-- Función     :	EstadoTurno
     *-- Definición  :	Devuelve el estado del Turno 'C' CERRADO O 'A' ABIERTO 
     *-- Fecha Creado:  25/01/2023
     *-- Creado Por  :	Joan Oliveros
     ***************************************************************************************************************/

    public function EstadoTurno(int $tnIngreso, object $datosTurno): string
    {

        $this->lnIngreso = trim($tnIngreso);
        $this->lnHoraIni = $datosTurno->tnHoraIni;
        $this->lnHoraFin = $datosTurno->tnHoraFin;
        $this->lcCambio = $datosTurno->tcCambio;

        $ltAhora = new \DateTime($this->oDb->fechaHoraSistema());
        $lnFechaAct = $ltAhora->format("Ymd");
        $lnFechaAnt = $ltAhora->format("Ymd") - 1;

        $laRegistro = $this->oDb->select('*')
            ->from(self::lcTableNot)
            ->where('INGNOT', '=', $this->lnIngreso)
            ->orderBy('CONNOT DESC')
            ->get("array");

        if (!empty($laRegistro)) {
            if ($laRegistro['NTANOT'] == 'S' && $laRegistro['ADMNOT'] == 1) {
                return $this->lcEstado = "C";
            }
            if (!empty($this->lcCambio)) {
                if (
                    ($laRegistro['FECNOT'] == $lnFechaAct && $laRegistro['HORNOT'] >= $this->lnHoraIni) ||
                    ($laRegistro['FECNOT'] == $lnFechaAnt && $laRegistro['HORNOT'] < $this->lnHoraFin)
                ) {
                    return $this->lcEstado = "A";
                }
                return $this->lcEstado = "AA";
            }

            return ($this->between($laRegistro['HORNOT'], $this->lnHoraIni, $this->lnHoraFin)) ? 'A' : 'AA';
        }
        return $this->lcEstado = "C";
    }

    /**
     * ***************************************************************************************************************
     *-- Función     :	AbrirTurno
     *-- Definición  :	Adiciona el registro en NCSNOT  para la apertura de Turno
     *-- Fecha Creado:  25/01/2023
     *-- Creado Por  :	Joan Oliveros     
     ***************************************************************************************************************/

    public function AbrirTurno(array $taRegistros): bool
    {
        return $this->oDb->tabla(self::lcTableNot)->insertar($taRegistros);
    }


    /**
     * ***************************************************************************************************************
     *-- Función     :	CerrarTurno
     *-- Definición  :	modifica el registro en NCSNOT para la apertura de Turno
     *-- Fecha Creado:  25/01/2023
     *-- Creado Por  :	Joan Oliveros     
     ***************************************************************************************************************/

    public function CerrarTurno(array $taRegistros, $taCondicion): bool
    {
        return $this->oDb->from(self::lcTableNot)->where($taCondicion)->actualizar($taRegistros);


    }
    /**
     * ***************************************************************************************************************
     *-- Función     :	NombreTurno
     *-- Definición  :	Retorna el Turno de enfermeria dependiendo de la fecha y hora enviada
     *-- 				Turno 1 --> De Lunes a Viernes de 07:00 a.m. a 01:00 p.m.
     *-- 				Turno 2 --> De Lunes a Viernes de 01:00 p.m. a 07:00 p.m.
     *-- 				Turno 3 --> Todos los dias de 07:00 p.m. a 07:00 a.m. dia siguiente
     *-- 				Turno 4 --> Sabado y Domingo de 07:00 a.m a 07.00 p.m.
     *-- Fecha Creado:  25/01/2023
     *-- Creado Por  :	Joan Oliveros     
     ***************************************************************************************************************/

    public function DatosTurno(int $tnHora)
    {
        $laRegistros = $this->oDb->select('*')
            ->from(self::lcTableMaestra)
            ->where('TIPTMA', '=', self::lcTipo)
            ->getAll("array");

        if (!empty($laRegistros)) {
            foreach ($laRegistros as $laRegistro) {
                $lbValHora = $this->between($tnHora, $laRegistro['OP3TMA'], $laRegistro['OP7TMA']);
                $lbValHorDat = (trim($laRegistro['OP2TMA']) == 1 && ($tnHora < $laRegistro['OP7TMA'] || $tnHora > $laRegistro['OP3TMA']));

                if ($lbValHora || $lbValHorDat) {
                    if (strstr(trim($laRegistro['OP5TMA']), Date("N"))) {
                        return (object) [
                            "tnHoraIni" => trim($laRegistro['OP3TMA']),
                            "tnHoraFin" => trim($laRegistro['OP7TMA']),
                            "tcCambio" => trim($laRegistro['OP2TMA']),
                            "tcNombreTurno" => trim($laRegistro['DE2TMA']),
                        ];
                    }
                }
            }
        }
    }

    final public function between($tnValor, $tnMenor, $tnMayor): bool
    {
        return ($tnValor > $tnMenor && $tnValor < $tnMayor);
    }
}