import { Component, Input } from '@angular/core';
import { Especialidades } from './services/especialidades.service';
import { IEspecialidades, IFiltroBusqueda } from './interfaces/IEspecialidades';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    FormsModule,
    ReactiveFormsModule,
    BrowserModule 
  ],
  selector: 'app-especialidades',
  templateUrl: './especialidades.component.html',
  styleUrl: './especialidades.component.css'
})
export class EspecialidadesComponent {

  public RespuestaEspecialidades!: IEspecialidades[];

  constructor(private especialidades : Especialidades){

  }

  ngOnInit() {

    this.recuperarEspecialidadComponent();
  }

  @Input()
  form!: FormGroup;

  @Input()
  filtro!: IFiltroBusqueda

  
  documentos: any;
  @Input()
   frmParametro!: FormGroup;

  
   recuperarEspecialidadComponent(){
    this.especialidades.recuperarEspecilidades(this.filtro).subscribe((result:any) => {
      this.RespuestaEspecialidades = result['listaComun'] ?? [];
    });
  }

}
