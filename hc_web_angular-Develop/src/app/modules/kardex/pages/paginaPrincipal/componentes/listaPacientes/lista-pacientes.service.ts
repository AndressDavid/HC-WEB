import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { IlistaPacientes } from '../../interfaces/IlistaPacientes';
import { IListaPacientesKar } from '../../interfaces/IlistaPacientesKar';

@Injectable({
  providedIn: 'root'
})
export class ListaPacientesService {
  private UrlKardexPac = "http://localhost:8081/shaio/";

  constructor(private http: HttpClient) { }

  private apiurl = 'http://localhost:8081/shaio/paciente/listarActivos?token=6663c0b0-868f-49d7-9cfe-3ed3e86996f5';

  obtenerDatosPacientes(): Observable<{ listPacientes: IlistaPacientes }> {
    const url = `${this.apiurl}`;
    return this.http.get<{ listPacientes: IlistaPacientes }>(url);
  }

  obtenerPacientesKardex(): Observable<{ listaKardexPaciente: IListaPacientesKar }> {
    const headers = new HttpHeaders({
      'Authorization': `Bearer 6663c0b0-868f-49d7-9cfe-3ed3e86996f5`
    });
    return this.http.get<{ listaKardexPaciente: IListaPacientesKar }>(`${this.UrlKardexPac}kardex/listadoKardexPacientes`, { headers });
  }


  

}
