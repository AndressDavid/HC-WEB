import { Injectable, inject } from '@angular/core';
import {ConfigEnvironment} from './config.environment';
import { AbstractionToken } from './token/AbstractionToken';

@Injectable({
    providedIn: 'root'
})
export class Environment{
    private configEnvironment: ConfigEnvironment = new ConfigEnvironment;

    entorno (): string{
        return this.configEnvironment.getConfig().environmentPhp.environment;
    }

    private version = 2024;

    environmentPhp():string{

        switch(this.configEnvironment.getConfig().environmentPhp.environment){
            case 'produccion':
                return 'https://hcwp.shaio.org/';
            case 'desarrollo':
                return 'https://hcwd.shaio.org/';
            case 'local1':
                return 'http://localhost/HCP-PHP-SERVER-'+this.version+"/";
            case 'local2':
                return 'http://localhost/hcp-php-server-'+this.version+"/";
        }

        return 'http://localhost/HCP-PHP-SERVER-'+this.version+"/";
    }

    environmentApiPhp(version: String):string{

            switch(this.configEnvironment.getConfig().environmentApiPhp.environment){
            case 'produccion':
                return 'https://hcwp.shaio.org/restapi/server/'+version+'/';
            case 'desarrollo':
                return 'https://hcwd.shaio.org/restapi/server/'+version+'/';
            case 'local1':
                return 'http://localhost/HCP-PHP-SERVER-'+this.version+'/restapi/server/'+version+'/';
            case 'local2':
                return 'http://localhost/hcp-php-server-'+this.version+'/restapi/server/'+version+'/';
        }

        return 'http://localhost/HCP-PHP-SERVER-'+this.version+'/restapi/server/'+version+'/';
    }

    environmentApiJava(servicio : String){

        let environment = this.configEnvironment.getConfig().environmentApiPhp.environment

        switch(environment){
            case 'produccion':
                return 'https://http://172.20.10.142'+this.serviciosJavaApi(servicio, environment);
            case 'desarrollo':
                return 'http://172.20.10.49'+this.serviciosJavaApi(servicio, environment);
            default:
                return 'http://localhost'+this.serviciosJavaApi(servicio, environment);
        }
    }

    serviciosJavaApi(servicio : String, environment: string):String{

        let puerto : string = "9";

        switch(environment){
            case 'produccion':
                puerto = "5";
                break;   
            case 'desarrollo':
                puerto = "9";
                break;
            default:
                puerto = "8";
                break;
        }

        switch(servicio){
            case 'his':
                return ':'+puerto+'081/shaio/';
            case 'auth':
                return ':'+puerto+'080/shaio/';
            default:
                return '';
        }
    }

}
