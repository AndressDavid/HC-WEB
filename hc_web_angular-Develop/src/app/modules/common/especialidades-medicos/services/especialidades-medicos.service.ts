import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";

import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";
import { Environment } from "../../../../environments/environment";
import { especialidadesMedicosFiltros } from "../interfaces/especialidadesMedicosFiltros";

@Injectable({
    providedIn: 'root'
})
export  class ServiceEspecialidadesMedicos {
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { 
    }

    recuperarEspecialidadesMedicos(body : especialidadesMedicosFiltros){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/especialidadesMedicos', body, {context : executeContexToken()} );
    }
}
