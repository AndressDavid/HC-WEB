import { Component, EventEmitter, Input, Output } from '@angular/core';
import { ServiceTipoAnestesia } from './services/tipoAnestesia.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';


@Component({
  standalone: true,
  imports: [  
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  selector: 'app-tipo-anestesia',
  templateUrl: './tipo-anestesia.component.html',
  styleUrl: './tipo-anestesia.component.css'
})
export class TipoAnestesiaComponent {

  public tiposAnestesias : any[] | undefined;
  @Output() codigoTipoCirugia = new EventEmitter<String>();

  @Input() requerido : boolean = false;
  @Input() forname: String = "tipoAnestesia";
  @Input()frmParametro!: FormGroup

  constructor(private serviceTipoAnestesia : ServiceTipoAnestesia){}

  ngOnInit(){
    this.recuperarTipoAnestesiaComponent();
  }

  recuperarTipoAnestesiaComponent(){

    this.serviceTipoAnestesia.recuperarTipoAnestesia().subscribe((result:any) => {
      this.tiposAnestesias = result['listaComun'] ?? [];
    });
  }

  cambioAnestecia(event : any): any{
    this.codigoTipoCirugia.emit(event.target.value);
  }

}
