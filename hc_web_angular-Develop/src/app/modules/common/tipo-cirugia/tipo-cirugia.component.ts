import { Component, Input } from '@angular/core';
import { ServiceTipoCirugia } from './services/tipoCirugia.service';
import { BrowserModule } from '@angular/platform-browser';
import { FormsModule, ReactiveFormsModule, FormGroup } from '@angular/forms';

@Component({
  standalone: true,
  imports: [ 
    BrowserModule,
    FormsModule,
    ReactiveFormsModule,
  ],
  selector: 'app-tipo-cirugia',
  templateUrl: './tipo-cirugia.component.html',
  styleUrl: './tipo-cirugia.component.css'
})
export class TipoCirugiaComponent {

  public tiposCirugias: any[] | undefined;
  @Input() requerido : boolean = false;
  @Input() forname: String = "tipoCirugia";
  @Input()frmParametro!: FormGroup

  constructor( private serviceTipoCirugia : ServiceTipoCirugia ){}

  ngOnInit(){
    this.recuperarTiposCirugiasComponent();
  }

  recuperarTiposCirugiasComponent(){
    this.serviceTipoCirugia.recuperarTipoCirugia().subscribe((result:any) => {
      this.tiposCirugias = result['listaComun'] ?? [];
    });
  }
}
