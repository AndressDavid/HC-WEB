import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../environments/environment';

@Injectable({
    providedIn: 'root'
})

export class ViasService{
    
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }

    recuperarViasIngreso(){
        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/vias.php', null);
    }

}