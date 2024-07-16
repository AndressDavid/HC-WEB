import { DatePipe } from "@angular/common";
import { HttpClient, HttpHeaders } from "@angular/common/http";
import { Injectable, inject } from "@angular/core";
import {CanActivateFn, UrlTree } from "@angular/router";
import { Observable } from "rxjs";
import { AbstractionToken } from "./environments/token/AbstractionToken";
import { executeContexToken } from "./environments/token/token-interceptor.interceptor";
import { Environment } from "./environments/environment";


@Injectable({
    providedIn: 'root'
  })
class PermissionsService {
    constructor(
        private http: HttpClient,
        private url: Environment,
    ) { }

    validarSessionPhp(): Observable<any>{
      return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/authAngular.php', null);
    }
}

@Injectable({
    providedIn: 'root'
  })
export class SessionUser{
    
    pipe = new DatePipe('en-US');
    date = new Date();
    
    
    constructor(
        private http: HttpClient,
        private url: Environment,
    ){}

    validarSession(session: string): Observable<any>{
        let body = new URLSearchParams();
          body.set('usrhcw', session);

        const httpOptions = {
          headers: new HttpHeaders({
            'Content-Type': 'application/x-www-form-urlencoded'
          })};

        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/sesioncre.php', body, httpOptions );
    }
    
    crearSessionStoroge(){
        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/angularSessionStorage.php', null );
    }

    loginUserModal(Requesbody: any){

        let body = new URLSearchParams();
        body.set('usuario', Requesbody.usuario);
        body.set('password', Requesbody.password);
        body.set('tipo', Requesbody.tipo);
        body.set('especialidad', Requesbody.especialidad);

      const httpOptions = {
        headers: new HttpHeaders({
          'Content-Type': 'application/x-www-form-urlencoded'
        })
      };
        
        console.log(httpOptions);

        return this.http.post(this.url.environmentPhp()+'nucleo/vista/comun/ajax/angularFormLoginModal.php', body, httpOptions );
    }


}

export const canActivateRedirect: CanActivateFn = (
  ):
    Observable<boolean | UrlTree> 
    | Promise<boolean | UrlTree> 
    | boolean 
    | UrlTree=> {
      let url = new  Environment;
      window.location.href = url.environmentPhp().toString();
      return true; 
    }


export const canActivateAuth: CanActivateFn = (
  ):
    Observable<boolean | UrlTree> 
    | Promise<boolean | UrlTree> 
    | boolean 
    | UrlTree=> {

    let auth= inject(PermissionsService);
    let url = new  Environment;
    let abstractionToken = inject(AbstractionToken);

    new Promise(res => {
        auth.validarSessionPhp().subscribe((result:any) => {
            let au = result["status"];
            if(!au){
                window.location.href = url.environmentPhp().toString();
            }
        });
    }).catch(function exits() {
        window.location.href = url.environmentPhp().toString();
        return false;
    }); 

    return true; 
};
