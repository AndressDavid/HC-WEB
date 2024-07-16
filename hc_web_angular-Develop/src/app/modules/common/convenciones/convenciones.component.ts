import { Component } from '@angular/core';
import { ColoresEstados } from './services/estadosColores.service';
import { BrowserModule } from '@angular/platform-browser';

@Component({
  standalone: true,
  imports:[BrowserModule ],
  selector: 'app-convenciones',
  templateUrl: './convenciones.component.html',
  styleUrl: './convenciones.component.css'
})
export class ConvencionesComponent {

  public isActive: boolean = false;
  public EstadosColores: any [] =[];

  constructor(public coloresEstados: ColoresEstados){}
  
  public estadoError: string = "";

  activarPop(): void{
    this.isActive = true;
  }

  desactivarPop(): void{
    this.isActive = false;
  }

  ngOnInit() {
    this.recuperarColores();
  }

  recuperarColores() : void {
    this.coloresEstados.recuperarColores().subscribe((result:any) => {
        if(result["error"] != ""){
          this.estadoError = result["error"];
        }else{
          this.EstadosColores = result["estados"];
        }

    });
  }

  color(color: String): String{
    return "#"+color;
  }


}
