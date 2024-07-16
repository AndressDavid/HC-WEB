import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';
import { filtrosMedicamnetos } from '../interfaces/IFiltrosMedicamentos';



@Injectable({
    providedIn: 'root'
})

export class ServiceMedicamentos{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarMedicamentos(body : filtrosMedicamnetos){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/listaMedicamentos',body, {context : executeContexToken()} );
    }
}