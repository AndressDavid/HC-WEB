import { Component } from '@angular/core';
import { Environment } from '../../../environments/environment';
import { FooterService } from './services/footer.service';

@Component({
  selector: 'app-footer',
  templateUrl: './footer.component.html',
  styleUrl: './footer.component.css'
})
export class FooterComponent {

  constructor(public environment : Environment, public footerService : FooterService){}

  public servidor = this.environment.entorno();
  public ipCliente: string = "";
  public year:string = "";

  ngOnInit() {
    this.recuperarInfoFooter();
  }


  recuperarServidor() : String{

    switch(this.servidor){
      case 'produccion':
        return 'hcwp.shaio.org';
      case 'desarrollo':
          
        return "hcwd.shaio.org";
      default:
        return "localhost"
    }
  }



  alert(): boolean{
    if(this.servidor !='produccion'){
      return true;
    }

    return false;
  }

  recuperarInfoFooter() : void {
    this.footerService.recuperarInformacionFooter().subscribe((result:any) => {
      if(result['status'] == true){
        this.ipCliente = result['IP'];
        this.year = result['yearVersion'];
      }
    });
  }


}
