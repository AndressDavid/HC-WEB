import { Component, Input } from '@angular/core';
import { ServiceProfesionales } from './services/profesionales.service';
import { estructuraServicio } from './interfaces/estructuraServicio';
import { BrowserModule } from '@angular/platform-browser';
import { AutoCompleteModule } from 'primeng/autocomplete';
import { StyleClassModule } from 'primeng/styleclass';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [     
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
    AutoCompleteModule,
    StyleClassModule,
  ],
  selector: 'app-profesionales',
  templateUrl: './profesionales.component.html',
  styleUrl: './profesionales.component.css'
})
export class ProfesionalesComponent {

  public listaProfesionales: any[] | undefined;
  private estructuraFiltro? : estructuraServicio;

  constructor(private serviceProfesionales : ServiceProfesionales){}
  
  @Input() tipo! : string;
  @Input() usuario : string = "";
  @Input() estado! : string;
  @Input() titulo! : string;

  
  @Input()
  requerido: boolean = false;

  @Input()
  activarValidations : boolean = false;

  @Input()
  formValid : boolean = false;

  
  @Input() forname: String = "profesional";
  @Input()frmParametro!: FormGroup;


  ngOnInit(){
    this.recuperarProfesionalesComponent();
  }

  recuperarProfesionalesComponent(){

    this.estructuraFiltro = {
      "tipos" : this.tipo,
      "usuario": this.usuario,
      "estado": this.estado
    }

    this.serviceProfesionales.recuperarProfesionales(this.estructuraFiltro).subscribe((result:any) => {
      this.listaProfesionales = result['listaComun'] ?? [];
    });
  }

}
