import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { Environment } from '../../../environments/environment';
import { Observable } from 'rxjs';
import { executeContexToken } from '../../../environments/token/token-interceptor.interceptor';

@Injectable({
  providedIn: 'root'
})
export class NotesService {

  public _datosAnt: String = '';
  public tipo: String = '';
  public documento: String =''
  public Usuario: String =''
  public Nombre: String =''
  url: String = 'http://localhost:8081/shaio/';
  constructor(
    private http: HttpClient, private environment: Environment) { }

  obtenerListaDatosPaciente(tipo: String, documento: String ): Observable<any> {
    this.ObtenerUsuario;
    this.tipo = tipo;
    this.documento = documento;
    return this.http.get(`${this.url}paciente/ExisteDocumento?tipo_Documento=${tipo}&documento=${documento}`,{context : executeContexToken()});
  }

  obtenerNotasAclaratorias( tipoD: String, documentoD: String): Observable<any> {
    this.tipo = tipoD;
    this.documento = documentoD;
    return this.http.get(`${this.url}notasaclaratorias/listartexto?tipo_Documento=${tipoD}&documento=${documentoD}`,{context : executeContexToken()});
  }

  get datosAnt(){
    return this._datosAnt;
  }

  GuardarNotasAclaratorias( tipo: String, documento: String, texto:String, usuario:string, especialidad: String, ): Observable<any> {
    return this.http.get(`${this.url}notasaclaratorias/GuardarNotas?tipo=${tipo}&documento=${documento}&cTextoNota=${texto}&usuario=${usuario}&programa=NA001A&especialidad=${especialidad}`, {context : executeContexToken()});
  }

  ObtenerUsuario() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/obtenerUsuario.php');
  }

  ObtenerEspecialidad() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/obtenerEspecialidad.php');
  }

}
