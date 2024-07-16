import { Component } from '@angular/core';

@Component({
  selector: 'app-anestesia-eco-perioperatorio',
  templateUrl: './anestesia-eco-perioperatorio.component.html',
  styleUrl: './anestesia-eco-perioperatorio.component.css',
})
export class AnestesiaEcoPerioperatorioComponent {

  public ingresoValido: boolean = false;

  public onGetIngresoValido(ingresoValido: boolean) {
    this.ingresoValido = ingresoValido;
  }


}
