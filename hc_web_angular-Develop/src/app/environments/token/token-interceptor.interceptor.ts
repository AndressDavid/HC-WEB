import { HttpInterceptorFn,  HttpContext, HttpContextToken, HttpHandlerFn, HttpRequest, HttpEvent  } from '@angular/common/http';
import { getCookie, setCookie } from 'typescript-cookie';
import { AbstractionToken } from './AbstractionToken';
import { inject } from '@angular/core';
import { Observable, switchMap, tap } from 'rxjs';


const execute_token = new HttpContextToken<boolean>( () => false );
export function executeContexToken(){
    return new HttpContext().set(execute_token, true);
}

export const tokenInterceptorInterceptor: HttpInterceptorFn = (req: HttpRequest<unknown>, next:
  HttpHandlerFn) : Observable<HttpEvent<any>>  => {
    
    let tokenAuth = inject(AbstractionToken);

    if(req.context.get(execute_token)){
      return tokenAuth.obtenerToken().pipe(
        tap((result:any) => {
          tokenAuth.execute(result['estadoHC']);
        }),
        switchMap(() => {
          req = req.clone({
            setHeaders: {
              Authorization: 'Bearer ' +tokenAuth.getToken(),
              //url: 'http://172.20.10.49:9081/shaio/comunes/especialidades'
            }
          });
          tokenAuth.destroyToken();
          return next(req);
        })
      );
    }
    return next(req);

};
