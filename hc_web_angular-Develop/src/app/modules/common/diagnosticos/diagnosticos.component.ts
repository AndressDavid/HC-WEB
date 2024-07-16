import { Component, Input } from '@angular/core';
import { ServicesDiagnosticos } from './services/diagnosticos.service';
import { filtroDiagonostico } from './interfaces/IfiltrosDiagnosticos';

import { BrowserModule } from '@angular/platform-browser';
import { AutoCompleteModule } from 'primeng/autocomplete';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    FormsModule,
    ReactiveFormsModule,
    BrowserModule ,
    AutoCompleteModule
  ],
  selector: 'app-diagnosticos',
  templateUrl: './diagnosticos.component.html',
  styleUrl: './diagnosticos.component.css'
})
export class DiagnosticosComponent {

  public suggestions: any[] =[];

  public filtros : filtroDiagonostico | undefined;
  @Input() sexRips!: string;
  @Input() titulo!: string;
  @Input() tipmae: string ="";
  @Input() cl1tma: string ="";
  @Input()
  requerido!: boolean;

  @Input() forname: String = "dxdiag";
  @Input()frmParametro!: FormGroup;

  // @Input() forname!: string;
  // @Input()
  // frmParametro!: FormGroup;

  constructor(private servicesDiagnosticos : ServicesDiagnosticos){}

  searchDX(event : any){
    if(event.query.length  < 3){
      this.suggestions = []
    }else{

      let sexo : string= "";

      if(this.sexRips == "FEMENINO"){
        sexo = "2";
      }

      if(this.sexRips == "MASCULINO"){
        sexo = "1";
      }


      this.filtros = {
        "sexRips": sexo,
        "codigo": event.query.toUpperCase(),
        "descripcion": event.query.toUpperCase(),
        "tipmae":this.tipmae,
        "cl1tma": this.cl1tma,
        "tipoFiltro":"LIKE" 
      }

      this.servicesDiagnosticos.recuperarDignostico(this.filtros).subscribe((result:any) => {
        let DxList: any [] =[];
        for (const diagnostico of result['listaComun']) {
            let diag  = diagnostico["codigo"] +"-"+diagnostico["descripcion"]
            DxList.push(diag);
        }

        this.suggestions = DxList;
      });
    }
  }
}