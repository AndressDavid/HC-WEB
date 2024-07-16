import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';



@Injectable({
    providedIn: 'root'
})

export class ServiceSalaProcedimientos{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarSala(tipoSala : string){

        return this.http.post(this.url.environmentApiJava('his')+'comunes/salas?tipo='+tipoSala,null, {context : executeContexToken()} );
    }
}