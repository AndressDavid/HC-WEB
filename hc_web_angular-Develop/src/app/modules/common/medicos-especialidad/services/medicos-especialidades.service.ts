
import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";

import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";
import { Environment } from "../../../../environments/environment";
import { filtroMedicosEspecialidades } from "../interfaces/filtroMedicosEspecialidades";

@Injectable({
    providedIn: 'root'
})
export  class ServiceMedicosEspecialidades{
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { 
    }

    recuperarMedicosEspecialidades(body : filtroMedicosEspecialidades){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/medicosXespecialidades', body, {context : executeContexToken()} );
    }
}
