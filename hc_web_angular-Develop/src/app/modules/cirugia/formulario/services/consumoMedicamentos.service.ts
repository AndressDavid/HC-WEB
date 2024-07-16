import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Environment } from "../../../../environments/environment";
import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";
import { consumoMedicamentoFiltro } from "../interfaces/IFiltrosConsumoMedicamentos";
import { informacionGuardar } from "../interfaces/IGuardarDescripcionQuirurgica";


@Injectable({
    providedIn: 'root'
})
export class ServiceConsumoMedicamentos{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarConsumoMedicamentos(body: consumoMedicamentoFiltro){
        return this.http.post(this.url.environmentApiJava('his')+'control-cirugia/getConsumoSalas',
        body, {context : executeContexToken()} );
    }
    
    guardarInformacion(body : informacionGuardar){
        return this.http.post(this.url.environmentApiJava('his')+'control-cirugia/guardarControlQuirurgico',
        body, {context : executeContexToken()} );
    }

    validarProcedimiento(procedimiento: string){

        let body={
            "procedimiento": procedimiento
        }

        return this.http.post(this.url.environmentApiJava('his')+'control-cirugia/validarProcedimiento',
        body, {context : executeContexToken()} );
    }

    extaerMiPres(plan: string, tipoPlan: string){

        let body={
            "plan": plan,
            "tipoPlan": tipoPlan
        }

        return this.http.post(this.url.environmentApiJava('his')+'control-cirugia/obtenerMiPres',
        body, {context : executeContexToken()} );
    }
}