

import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";

import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";
import { Environment } from "../../../../environments/environment";
import { filtrosProcedimientos } from "../interfaces/IFiltrosProcedimientos";

@Injectable({
    providedIn: 'root'
})
export  class Serviceprocedimientos{
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { 
    }

    recuperarProcedimientos(body : filtrosProcedimientos){
        return this.http.post(this.url.environmentApiJava('his')+'cups/listaProcedimientosQuirurgicos', body, {context : executeContexToken()} );
    }
}
