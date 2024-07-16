import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { ModalDinamicoComponent } from '../../../common/modal-dinamico/modal-dinamico.component';
import { ModalEditarComponent } from './modal-editar/modal-editar.component';
import { ModalNuevoComponent } from '../consulta/modal-nuevo/modal-nuevo.component';
import { ITabla } from '../interfaces/ITabla';
import { ConsultaService } from '../servicios/consulta.service';
import { ActivoPipe } from '../Pipes/activo.pipe';

@Component({
  selector: 'app-tabla-consulta',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
    ModalEditarComponent,
    ModalNuevoComponent,
    ActivoPipe,
  ],
  templateUrl: './tabla-consulta.component.html',
  styleUrls: ['./tabla-consulta.component.css'],
})
export class TablaConsultaComponent {
  @Input() datosTabla?: ITabla[] = [];

  public selectedDatos: ITabla | null = null;

  constructor(public consultaService: ConsultaService) {}

  public editarHabModal: boolean = false;
  public nuevaHabModal: boolean = false;

  onEditar(datos: ITabla) {
    this.selectedDatos = datos;
    this.editarHabModal = true;
  }

  close(event: boolean) {
    this.editarHabModal = event;
  }
}
