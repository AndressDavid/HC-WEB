import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';

@Injectable({
    providedIn: 'root'
})

export class ServicePatologias{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) {

    }
    
    recuperarPatologias(){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/listaPatologias', null,{context : executeContexToken()} );
    }
}