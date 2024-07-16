import { HttpClient, HttpHeaders } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Observable } from 'rxjs';
import { Environment } from '../../../../environments/environment';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';
import { IdatosGuardar } from '../interfaces/IdatosGuardar';

@Injectable({
  providedIn: 'root',
})
export class ConsultaService {
  url: string = 'http://localhost:8081/shaio/';
  public cSeccionB: string = '';
  public cHabitacionB: string = '';
  public cActivoB: string = '';

  constructor(private http: HttpClient, private environment: Environment) {}

  obtenerListaSecciones() {
    return this.http.post(
      this.environment.environmentPhp() +
        'nucleo/vista/comun/ajax/secciones.php',
      null
    );
  }

  obtenerListaHabitaciones(): Observable<any> {
    return this.http.get(`${this.url}hab/lista`, {
      context: executeContexToken(),
    });
  }

  obtenerListaHabitacionesPorSeccion(seccion: string): Observable<any> {
    return this.http.get(`${this.url}hab/lista?filter=${seccion}`, {
      context: executeContexToken(),
    });
  }

  obtenerDatosTablaTodos(): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') + `hab/tabla`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaSeccion(seccion: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?seccion=${seccion}&activo=1`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaHab(cama: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?cama=${cama}&activo=1`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaActivo(activo: number): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') + `hab/tabla?activo=${activo}`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaSecHab(seccion: any, cama: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?seccion=${seccion}&cama=${cama}&activo=1`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaSecAct(seccion: any, activo: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?seccion=${seccion}&activo=${activo}`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTablaCamaAct(cama: any, activo: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?cama=${cama}&activo=${activo}`,
      { context: executeContexToken() }
    );
  }

  obtenerDatosTabla(seccion: any, cama: any, activo: any): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `hab/tabla?seccion=${seccion}&cama=${cama}&activo=${activo}`,
      { context: executeContexToken() }
    );
  }

  obtenerActivo(): Observable<any> {
    return this.http.get(`${this.url}hab/tabla?activo=0`, {
      context: executeContexToken(),
    });
  }

  ObtenerUsuario() {
    return this.http.get<any>(
      this.environment.environmentPhp() +
        'nucleo/vista/comun/ajax/obtenerUsuario.php'
    );
  }

  guardarHabitaciones(body: IdatosGuardar): Observable<any> {
    return this.http.post(
      this.environment.environmentApiJava('his') +
        'hab/guardarDatosHabitaciones',
      body,
      { context: executeContexToken() }
    );
  }
}
