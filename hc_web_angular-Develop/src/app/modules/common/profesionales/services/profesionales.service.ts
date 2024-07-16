import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';
import { estructuraServicio } from '../interfaces/estructuraServicio';



@Injectable({
    providedIn: 'root'
})

export class ServiceProfesionales{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarProfesionales(body : estructuraServicio){
        return this.http.post(this.url.environmentApiJava('his')+'comunes/ListProfesional',body, {context : executeContexToken()} );
    }
}