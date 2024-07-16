import { Injectable } from '@angular/core';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';



@Injectable({
    providedIn: 'root'
})

export class ServiceOtrasSalas{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarOtrasSalas(){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/otrasSalas',null, {context : executeContexToken()} );
    }
}