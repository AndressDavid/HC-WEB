import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Environment } from '../../../../environments/environment';
import { Observable } from 'rxjs';
import { executeContexToken } from '../../../../environments/token/token-interceptor.interceptor';
import { IdatosPaciente } from '../interfaces/IdatosPaciente';

@Injectable({
  providedIn: 'root',
})
export class ServicioAnestesiaService {
  endpoint: string = 'http://localhost:8081/shaio/';
  endpointhcweb: string = 'http://localhost/HCP-PHP-SERVER-2024/';
  public nIngreso: number = 0;
  public idatosEnvio: IdatosPaciente[] = [];

  constructor(private http: HttpClient, private environment: Environment) {}

  ObtenerDatosIngreso(tnIngreso: number) {
    this.nIngreso = tnIngreso;
    return this.http.get(
      this.environment.environmentApiPhp('v1') + 'hc/datosingreso/' + tnIngreso
    );
  }

  obtenerListaProcedimientos(): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `anestesiaperioperatorio/list`,
      { context: executeContexToken() }
    );
  }

  validarDatos(body: IdatosPaciente) {
    return this.http.post(
      this.environment.environmentApiJava('his') +
        'anestesiaperioperatorio/validar',
      body,
      { context: executeContexToken() }
    );
  }

  consecutivoCita(): Observable<any> {
    return this.http.get(
      this.environment.environmentApiJava('his') +
        `paciente/consecutivocita?typeDocument=C&document=79846451`,
      { context: executeContexToken() }
    );
  }

  guardarDatosAnestesia(body: IdatosPaciente): Observable<any> {
    return this.http.post(
      this.environment.environmentApiJava('his') +
        'anestesiaperioperatorio/guardar',
      body,
      { context: executeContexToken() }
    );
  }

  cobrarProcedimiento(): Observable<any> {
    return this.http.post(`${this.endpoint}cobros/cobracups`, {
      context: executeContexToken(),
    });
  }

  ObtenerUsuario() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/obtenerUsuario.php');
  }
}
