import { Injectable } from "@angular/core";
import { Environment } from "../environment";
import { HttpClient } from "@angular/common/http";

@Injectable({
    providedIn: 'root'
})
export class HttpClientPHP {
    constructor( private http: HttpClient,private url: Environment) { }

    post(url : string, body: any, context: any ) : any{

        const headers = new Headers();  
        headers.append('url', url);

        return this.http.post(this.url.environmentApiPhp('v2')+'apihelper/java',body, {context});
    }
    
    get(url : string, context: any) : any{

    }

}