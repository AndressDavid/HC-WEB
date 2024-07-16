import { medicamentos } from "./IMedicamentos";
import { perfusionista } from "./IPerfusion";
import { actoQuirurgico } from "./IActoQuirurgico";
import { procedimientos } from "./IProcedimientos";
import { descripcionQuirurgica } from "./IDescripcionQuirurgica";

export interface informacionGuardar{
    ingreso : string,
    actoQuirurgico : actoQuirurgico,
    procedimientos: procedimientos[],
    descripcionQuirurgica : descripcionQuirurgica,
    perfucion: perfusionista,
    medicamentos : medicamentos[],
    informacionPaciente : any,
    programa : string,
    usuario: string,
    tipoUsuario: string,
    especialidadUsuario: string,
    justificacion : string
}