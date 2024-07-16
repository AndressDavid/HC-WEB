import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';
import { IFiltrosBusqueda } from '../interfaces/IFiltrosBusqueda';

@Injectable({
    providedIn: 'root'
})

export class DatosPacienteService{

    constructor(
        private http: HttpClient,
        private url: Environment,
    ) {

    }
    
    recuperarDocumento(filtros: IFiltrosBusqueda){


        let body = new URLSearchParams();

        body.set('accion', 'Ingreso');
        body.set('ingreso', filtros.ingreso.toString());
        body.set('obtenerPlanMedico', 'S');
        body.set('fechahoraingreso',filtros.fechaHoraIngreso.toString() );

      const httpOptions = {
        headers: new HttpHeaders({
          'Content-Type': 'application/x-www-form-urlencoded'
        })};
        

        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/modalDatosPaciente.php', body, httpOptions);
    }
}