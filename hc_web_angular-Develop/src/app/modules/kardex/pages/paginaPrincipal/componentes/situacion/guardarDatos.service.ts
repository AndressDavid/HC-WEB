import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class GuardarDatosService {


  constructor(private http: HttpClient) { }
  urlGudarDatos = "http://localhost:8081/shaio/kardex/guardarKardexSituacion";
  urlObtenerDatos = "http://localhost:8081/shaio/kardex/obtenerDatosSituacion";
  urlActualizarDatos = "http://localhost:8081/shaio/kardex/actualizarKardexSituacion";

  guardarDatosSituacion(datos: any): Observable<any> {
    const headers = new HttpHeaders({
      "Authorization": "Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5",
    });
    return this.http.post(this.urlGudarDatos, datos, {headers});
  }


  validarDatosKardex(ingreso:any){
    const headers = new HttpHeaders({
      "Authorization" : "Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5"
    })
    return this.http.get<any>(`${this.urlObtenerDatos}?numeroIngreso=${ingreso}`,{headers});

  }

  actualizarDatosSituacion(datos:any, ingreso:any){
    const headers = new HttpHeaders ({
      "Authorization": "Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5",
    })
    return this.http.post(`${this.urlActualizarDatos}?numeroIngreso=${ingreso}`, datos, {headers});
 
  }

}
