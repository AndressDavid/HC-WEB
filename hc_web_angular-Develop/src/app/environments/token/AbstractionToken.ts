import { HttpClient } from "@angular/common/http";
import { Environment } from "../environment";
import { Injectable, inject } from "@angular/core";
import { getCookie, setCookie, removeCookie } from 'typescript-cookie'
import { flatMap } from "rxjs";


@Injectable({
    providedIn: 'root'
})
export class  AbstractionToken{

    constructor(private http: HttpClient,
        private url: Environment) {
    }

    public execute(token : string) {

        token = btoa("0ab1-" + token + "0822");

        if(getCookie('E&G}ZAJCUQ*WBP#%WE8?6IZ^')){
            if(getCookie('E&G}ZAJCUQ*WBP#%WE8?6IZ^') !=token ){
                this.destroyToken();
                setCookie("E&G}ZAJCUQ*WBP#%WE8?6IZ^", token, { path: '' , secure: true});
            }
        }else{
            setCookie("E&G}ZAJCUQ*WBP#%WE8?6IZ^", token, { path: '' , secure: true});
        }
    }

    public getToken() : string {

        let token = getCookie("E&G}ZAJCUQ*WBP#%WE8?6IZ^") ?? "000000000000000000000";
        token = atob(token);
        token = token.substring( 0 , token.length -4);
        token = token.substring( 5 , token.length );

        return token;
    }

    public destroyToken(){
        removeCookie('E&G}ZAJCUQ*WBP#%WE8?6IZ^');
    }

    public obtenerToken(){
        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/EstadoHC.php', null);
    }

}
