import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { IContenidoPDF } from '../interfaces/IContenidoPDF';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';

@Injectable({
    providedIn: 'root'
})

export class ServiceRecuperarPDF{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarVistaPDF(body : IContenidoPDF){
        return this.http.post(this.url.environmentApiPhp('v1')+'hc/libro/obtenerdocumento', body, {context : executeContexToken()} );
    }

}