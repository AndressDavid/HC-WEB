import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';



@Injectable({
    providedIn: 'root'
})

export class ServiceTipoCirugia{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarTipoCirugia(){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/tipoCirugia',null, {context : executeContexToken()} );
    }
}