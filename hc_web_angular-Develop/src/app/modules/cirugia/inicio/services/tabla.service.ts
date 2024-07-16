import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable } from "@angular/core";
import { Environment } from "../../../../environments/environment";
import { IFiltrosControlCirugia } from "../interfaces/IFiltrosControlCirugia";
import { executeContexToken } from "../../../../environments/token/token-interceptor.interceptor";


@Injectable({
    providedIn: 'root'
})
export class TablaControlCirugia{


    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }
    
    recuperarDatosCirugia(filtros : IFiltrosControlCirugia){
        return this.http.post(this.url.environmentApiJava('his')+'control-cirugia/getCigurias',filtros, {context : executeContexToken()} );
    }
}