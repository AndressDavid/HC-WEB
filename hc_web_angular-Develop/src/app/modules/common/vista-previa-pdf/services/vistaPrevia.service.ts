import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { IContenidoVistaPrevia } from '../interfaces/IContenidoVistaPrevia';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';

@Injectable({
    providedIn: 'root'
})

export class ServiceRecuperarVistaPrevia{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarVistaPrevia(body : IContenidoVistaPrevia){
        return this.http.post(this.url.environmentApiPhp('v1')+'hc/libro/obtenerdocumento', body,  {context : executeContexToken()} );
    }

}