import { HttpClient } from "@angular/common/http";
import { Environment } from "../../../../environments/environment";
import { Injectable } from "@angular/core";


@Injectable({
    providedIn: 'root'
})
export class DatosEncabezadoService{
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }


    obtenerDatosCabecera(ingresoId: any): any {
        return this.http.get(this.url.environmentApiPhp("v2")+"paciente/consultarDatosCabecera/"+ingresoId);
      }
    
}