import { HttpClient } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Environment } from "../../../../environments/environment";
import { filtroDiagonostico } from "../interfaces/IfiltrosDiagnosticos";
import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";

@Injectable({
    providedIn: 'root'
})

export class ServicesDiagnosticos{
    
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }

    recuperarDignostico(body : filtroDiagonostico){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/diagnosticos',body, {context : executeContexToken()} );
    }

}