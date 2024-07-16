import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ServiceEspecialidadesMedicos } from './services/especialidades-medicos.service';
import { especialidadesMedicos } from './interfaces/especialidadesMedicos';
import { especialidadesMedicosFiltros } from './interfaces/especialidadesMedicosFiltros';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    FormsModule,
    ReactiveFormsModule,
    BrowserModule 
  ],
  selector: 'app-especialidades-medicos',
  templateUrl: './especialidades-medicos.component.html',
  styleUrl: './especialidades-medicos.component.css'
})
export class EspecialidadesMedicosComponent  {

  public especialidadesMedicosRespuesta : especialidadesMedicos[] | undefined;
  private filtroEspecialidad : especialidadesMedicosFiltros | undefined;

  @Input() opcion1! : String;
  @Input() opcion2! : String;
  @Output() cambiocodigo = new EventEmitter<string>();

  @Input() forname: String = "especialidadMedico";
  @Input()frmParametro!: FormGroup;
  
  constructor(private especialidadesMedicos : ServiceEspecialidadesMedicos){}

  
  ngOnInit() {
    this.recuperarEspecialidadesMedicos();
  }

  recuperarEspecialidadesMedicos(){

    this.filtroEspecialidad = {
      "opcion1": this.opcion1.toString(),
      "opcion2": this.opcion2.toString()
    }

    this.especialidadesMedicos.recuperarEspecialidadesMedicos(this.filtroEspecialidad).subscribe((result:any) => {
      this.especialidadesMedicosRespuesta = result['listaComun'] ?? [];
    });
  }


  cambioEspecialidad(event : any){
    if(event.target.value !== ''){
      this.cambiocodigo.emit(event.target.value);
    }

  }


}
