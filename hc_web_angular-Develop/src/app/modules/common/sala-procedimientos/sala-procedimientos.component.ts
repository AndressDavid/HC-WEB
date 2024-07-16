import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ServiceSalaProcedimientos } from './services/sala-procedimientos.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [    
    BrowserModule,
    FormsModule,
    ReactiveFormsModule
  ],
  selector: 'app-sala-procedimientos',
  templateUrl: './sala-procedimientos.component.html',
  styleUrl: './sala-procedimientos.component.css'
})
export class SalaProcedimientosComponent {

  public SalaProcedimientos: any[] | undefined;

  @Input() requerido : boolean = false;
  @Input() forname: String = "salaProcedimiento";
  @Input()frmParametro!: FormGroup;
  @Input() tipoSala : string = 'S';

  constructor(private serviceSalaProcedimientos : ServiceSalaProcedimientos ){}

  @Output() salaSeleccionada = new EventEmitter<string>();

  ngOnInit(){
    this.recuperarSalaComponent();
  }

  recuperarSalaComponent(){
    this.serviceSalaProcedimientos.recuperarSala(this.tipoSala).subscribe((result:any) => {
      this.SalaProcedimientos = result['listaComun'] ?? [];
    });
  }

  cambioSala(event : any ): any{
    this.salaSeleccionada.emit( event.target.value );
  }

}
