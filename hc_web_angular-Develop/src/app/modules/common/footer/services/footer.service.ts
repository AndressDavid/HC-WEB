import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Environment } from "../../../../environments/environment";
import { RouterStateSnapshot } from "@angular/router";

@Injectable({
    providedIn: 'root'
})
export  class FooterService{
    constructor(
        private http: HttpClient,
        private url: Environment
    ) { }

    recuperarInformacionFooter(){
        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/datosFooter.php', null );
    }


}
