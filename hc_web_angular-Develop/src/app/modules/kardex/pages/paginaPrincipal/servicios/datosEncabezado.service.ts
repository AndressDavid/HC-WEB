import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable, catchError } from 'rxjs';
import { IDatosEncabezado } from '../interfaces/IdatosEncabezado';
import { IDatosObservaciones } from '../interfaces/IdatosObservaciones';

@Injectable({
  providedIn: 'root'
})
export class DatosEncabezadoService {

  private UrlApiObtenerData = 'http://localhost/HCP-PHP-SERVER-2023/restapi/server/v2/kardexEnf/cargaIngresosKardex/';
  private UrlApi = 'http://localhost:8081/shaio/paciente/listarActivos?token=6663c0b0-868f-49d7-9cfe-3ed3e86996f5';
  private UrlAoiTurnos = 'http://localhost:8081/shaio/';
  private urlObtenerDatos = "http://localhost:8081/shaio/kardex/obtenerDatosSituacion";
  private urlObservaciones = "http://localhost:8081/shaio/kardex/obtenerDatosObservaciones";
  private ingresoId: string = '';
  private urlGudarObservaciones :string ="http://localhost:8081/shaio/kardex/guardarKardexObservaciones";

  constructor(private http: HttpClient) { }

  obtenerDatosCabecera(ingresoId: any): Observable<{ datos: IDatosEncabezado }> {
    this.ingresoId = ingresoId
    const url = `${this.UrlApiObtenerData}${this.ingresoId}`;
    return this.http.get<{ datos: IDatosEncabezado }>(url);
  }

  obtenerNombres(): Observable<any> {
    return this.http.get<any>(`${this.UrlApiObtenerData}${this.ingresoId}`);

  }

  obtenerListaPacientes(): Observable<any> {
    return this.http.get(this.UrlApi);
  }

  obtenerEstadoTurno(ingresoId: any): Observable<any> {
    this.ingresoId = ingresoId
    const headers = new HttpHeaders({
      'Authorization': `Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5`
    });
    return this.http.get(`${this.UrlAoiTurnos}kardex/turnos?numeroIngreso=${this.ingresoId}`, { headers });
  }

  obtenerDatosPrincipales() {
    const headers = new HttpHeaders({
      "Authorization": "Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5"
    })
    return this.http.get<any>(`${this.urlObtenerDatos}?numeroIngreso=${this.ingresoId}`, { headers });
  }

  // obtenerObservaciones() : Observable<{listaObservaciones : IDatosObservaciones}>{
  //   const headers = new HttpHeaders({
  //     "Authorization":`Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5`
  //   })
  //   return this.http.get<any>(`${this.urlObservaciones}?numeroIngreso=${this.ingresoId}`, {headers});
  // }

  obtenerObservaciones(numeroIngreso: any) : Observable<{obtenerKardexObservaciones: IDatosObservaciones[]}>{
    this.ingresoId = numeroIngreso;
    const headers = new HttpHeaders({
      "Authorization": `Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5`
    });

    return this.http.get<{obtenerKardexObservaciones: IDatosObservaciones[]}>(`${this.urlObservaciones}?numeroIngreso=${this.ingresoId}`, { headers });
  }

  guardarDatosObservaciones(datos: any): Observable<any> {
    const headers = new HttpHeaders({
      "Authorization": "Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5",
    });
    return this.http.post(this.urlGudarObservaciones, datos, {headers});
  }
  

}


