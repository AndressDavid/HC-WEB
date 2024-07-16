import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SeccionesService {

  url:string="http://localhost/HCP-PHP-SERVER-2023/";
  constructor(private http: HttpClient) { }

obtenerListaSecciones(){
    return this.http.post(this.url+'nucleo/vista/comun/ajax/secciones.php', null);
}
}
