import { Component, Input } from '@angular/core';
import { Serviceprocedimientos } from './services/procedimientos.service';
import { filtrosProcedimientos } from './interfaces/IFiltrosProcedimientos';
import { AutoCompleteModule } from 'primeng/autocomplete';
import { StyleClassModule } from 'primeng/styleclass';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';
@Component({
  standalone: true,
  imports: [
    FormsModule,
    ReactiveFormsModule,
    AutoCompleteModule,
    StyleClassModule,
    BrowserModule 
  ],
  selector: 'app-procedimientos',
  templateUrl: './procedimientos.component.html',
  styleUrl: './procedimientos.component.css'
})
export class ProcedimientosComponent {
  public RespuestaProcedimientos!: any[];
  private filtrosProcedimientos : filtrosProcedimientos | undefined;
  @Input()
  titulo!: string;

  @Input()
  requerido!: boolean;

  @Input() forname: String = "procedimientos";
  @Input()frmParametro!: FormGroup;

  

  constructor(private serviceprocedimientos : Serviceprocedimientos){

  }


   recuperarEspecialidadComponent(event : any){

    this.filtrosProcedimientos = {
      "sexo": "F",
      "exacto" : false,
      "programa": "RIA133",
      "codigo": event.query.toUpperCase(),
      "descripcion": event.query.toUpperCase()
    }

    if(event.query.length  < 3){
      this.RespuestaProcedimientos = []
    }else{

      this.serviceprocedimientos.recuperarProcedimientos(this.filtrosProcedimientos).subscribe((result:any) => {

        let CuspList: any [] =[];
        for (const cup of result['data']) {
            let diag  = cup["codcup"] +"-"+cup["descup"]
            CuspList.push(diag);
        }
        this.RespuestaProcedimientos = CuspList;
      });


    }
  }
}
