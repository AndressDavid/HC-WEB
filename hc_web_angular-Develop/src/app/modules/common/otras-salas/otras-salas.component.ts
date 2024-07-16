import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ServiceOtrasSalas } from './services/otrasSalas.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [  
    FormsModule,
    BrowserModule,
    ReactiveFormsModule,  
  ],
  selector: 'app-otras-salas',
  templateUrl: './otras-salas.component.html',
  styleUrl: './otras-salas.component.css'
})
export class OtrasSalasComponent {

  public otraSala: any[] | undefined;

  @Input() requerido : boolean = false;
  @Input() forname: String = "otraSalas";
  @Input()frmParametro!: FormGroup;

  constructor( private serviceOtrasSalas : ServiceOtrasSalas ){}

  @Output() otraSalaSeleccionada = new EventEmitter<string>();
  @Output() otraSalaNombre = new EventEmitter<string>();

  ngOnInit(){
    this.recuperarOtraSalaComponent();
  }

  recuperarOtraSalaComponent() : any{
    this.serviceOtrasSalas.recuperarOtrasSalas().subscribe((result:any) => {
      this.otraSala = result['listaComun'] ?? [];
    });
  }

  cambiarOtraSala(event : any) : any{

    const selectedIndex = event.target.selectedIndex;
    const selectedOption = event.target.options[selectedIndex];
    const nombreSeleccionado = selectedOption.textContent.trim();
    this.otraSalaNombre.emit(nombreSeleccionado);

    this.otraSalaSeleccionada.emit( event.target.value );
  }


}
