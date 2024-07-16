import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";

import { IFiltroBusqueda } from "../interfaces/IEspecialidades";
import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";
import { Environment } from "../../../../environments/environment";
import { HttpClientPHP } from "../../../../environments/ApiHelper/ApiHelper.service";

@Injectable({
    providedIn: 'root'
})
export  class Especialidades {
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { 
    }

    recuperarEspecilidades(body : IFiltroBusqueda){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/especialidades',body, {context : executeContexToken()} );
    }
}
