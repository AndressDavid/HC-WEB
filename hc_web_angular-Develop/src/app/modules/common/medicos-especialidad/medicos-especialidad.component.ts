import { Component, EventEmitter, Input, Output } from '@angular/core';
import { filtroMedicosEspecialidades } from './interfaces/filtroMedicosEspecialidades';
import { ServiceMedicosEspecialidades } from './services/medicos-especialidades.service';
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
  selector: 'app-medicos-especialidad',
  templateUrl: './medicos-especialidad.component.html',
  styleUrl: './medicos-especialidad.component.css'
})
export class MedicosEspecialidadComponent {
  private filtro : filtroMedicosEspecialidades | undefined;
  public medicosEspecialidadesRespuesta : any [] = [];
  @Input() opcion1! : String;
  @Input() opcion2! : String;
  @Input() especialidad! : String;
  @Input() titulo : String = "";


  @Input()
  requerido: boolean = false;

  @Input() forname: String = "procedimientos";
  @Input()frmParametro!: FormGroup;

  @Output() textoSeleccionado = new EventEmitter<string>();
 

  constructor(private medicosEspecialidades : ServiceMedicosEspecialidades){}


  ngOnInit() {
    this.recuperarMedicosEspecialidades();
  }


  recuperarMedicosEspecialidades(){
    this.filtro={
      "opcion1": this.opcion1.toString(),
      "opcion2": this.opcion2.toString(),
      "especialidad": this.especialidad.toString()
    }

    this.medicosEspecialidades.recuperarMedicosEspecialidades(this.filtro).subscribe((result:any) => {
      this.medicosEspecialidadesRespuesta = result['listaComun'] ?? [];
    });

  }


  labelSelect(event: any){
    const selectedIndex = event.target.selectedIndex;
    const selectedOption = event.target.options[selectedIndex];
    const nombreSeleccionado = selectedOption.textContent.trim();
    this.textoSeleccionado.emit(nombreSeleccionado);
  }



}
