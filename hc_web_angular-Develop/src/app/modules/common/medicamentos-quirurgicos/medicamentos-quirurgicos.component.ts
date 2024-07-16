import { Component, Input } from '@angular/core';
import { ServiceMedicamentos } from './services/medicamentos.service';
import { filtrosMedicamnetos } from './interfaces/IFiltrosMedicamentos';
import { AutoCompleteModule } from 'primeng/autocomplete';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [
    AutoCompleteModule,
    FormsModule,
    ReactiveFormsModule,
    BrowserModule 
  ],
  selector: 'app-medicamentos-quirurgicos',
  templateUrl: './medicamentos-quirurgicos.component.html',
  styleUrl: './medicamentos-quirurgicos.component.css'
})
export class MedicamentosQuirurgicosComponent {
  public RespuestaMedicamentos : any [] =[];
  private filtrosMedicamnetos : filtrosMedicamnetos | undefined ;

  @Input() requerido: boolean = false;
  @Input() titulo: string = "";
  @Input() forname: String = "medicamentosQuirurgicos";
  @Input()frmParametro!: FormGroup;

  constructor(private serviceMedicamentos : ServiceMedicamentos){}

  recuperarMedicamentos(event : any ) : any {

    this.filtrosMedicamnetos={
      "codigo": event.query.toUpperCase(),
      "descripcion": event.query.toUpperCase(),
      "tipoFiltro": "LIKE"
    }

    if(event.query.length  < 3){
      this.RespuestaMedicamentos = []
    }else{

      this.serviceMedicamentos.recuperarMedicamentos(this.filtrosMedicamnetos).subscribe((result:any) => {

        let medicamentosList: any [] =[];
        for (const medica of result['listaComun']) {
            let medicamento  = medica["codigo"] +"-"+medica["descripcion"]+ "-"+medica["isPos"];
            medicamentosList.push(medicamento);
        }
        this.RespuestaMedicamentos = medicamentosList;
      });


    }
  }

}
