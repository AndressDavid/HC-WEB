import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';

@Injectable({
    providedIn: 'root'
})

export class ColoresEstados{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) {

    }
    
    recuperarColores(){
        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/coloresEstados.php', null);
    }
}