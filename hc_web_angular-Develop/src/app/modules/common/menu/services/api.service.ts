import { Injectable } from '@angular/core';
import { Observable} from 'rxjs';
import { HttpClient, HttpParams } from '@angular/common/http';
import { Environment } from '../../../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class MenuService {

  constructor(private http: HttpClient, private environment: Environment) { }
  
  obtenerMenu(): Observable<any> {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/menu.php');
  }

  obtenerEspecialidad(): Observable<any> {
    return this.http.get<any>(this.environment.environmentPhp()+'restapi/server/v1/obtenerEspecialidades');
  }

  obtenerUrlFija() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/traer_url.php');
  }

  obtenerServidor() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/tipoServidor.php')
  }

  obtenerNombres() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/nombresUsuario.php')
  }

  ontenerRecordatorios() {
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/recordatorios.php')
  }

  cambiarEspecialidades(especialidades: any[]): Observable<any> {
  
    let params = new HttpParams();
  
    if (especialidades.length > 0) {
      const especialidad = especialidades[0];
  
      if (especialidad && especialidad.TIPO && especialidad.ESPECIALIDAD) {
        params = params.append('cambioTipo', especialidad.TIPO.nId);
        params = params.append('cambioEspecialidad', especialidad.ESPECIALIDAD.cId);
      } else {
        console.error('La estructura de datos es incorrecta o contiene valores nulos o undefined.');
      }
    } else {
      console.error('El array de especialidades está vacío.');
    }
    return this.http.get<any>(this.environment.environmentPhp()+'nucleo/vista/comun/ajax/menuEspecialidadesAngular.php', { params });
  }
}


