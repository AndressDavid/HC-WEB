import { Component } from '@angular/core';
import { ITabla } from './componentes/interfaces/ITabla';

@Component({
  selector: 'app-habitacion-inteligente',
  templateUrl: './habitacion-inteligente.component.html',
  styleUrl: './habitacion-inteligente.component.css',
})
export class HabitacionInteligenteComponent {
  public datosTabla: ITabla[] = [];

  public onGetDatosTabla(datosTabla: ITabla[]) {
    this.datosTabla = datosTabla;
  }

  public nuevaHabModal: boolean = false;

  public onGetNuevaHabModal(nuevaHabModal: boolean) {
    this.nuevaHabModal = nuevaHabModal;
  }

  public editarHabModal: boolean = false;

  public onGetEditarHabModal(editarHabModal: boolean) {
    this.editarHabModal = editarHabModal;
  }
}
