import { Component, EventEmitter, Output } from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
  FormBuilder,
} from '@angular/forms';
import { IContenido } from '../../../common/modal-dinamico/interfaces/contenidoModal';
import { CommonModule } from '@angular/common';
import { ModalDinamicoComponent } from '../../../common/modal-dinamico/modal-dinamico.component';
import { CabeceraPacienteComponent } from '../../../common/cabecera-paciente/cabecera-paciente.component';
import { ServicioAnestesiaService } from '../servicios/servicio-anestesia.service';
import { Iingreso } from '../interfaces/Iingreso';
import { IdatosPaciente } from '../interfaces/IdatosPaciente';

@Component({
  selector: 'app-ingreso',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
    CabeceraPacienteComponent,
  ],
  templateUrl: './ingreso.component.html',
  styleUrl: './ingreso.component.css',
})
export class IngresoComponent {
  constructor(public servicioAnestesia: ServicioAnestesiaService) {}

  @Output() OutListaDatosPaciente: EventEmitter<Iingreso[]> =
    new EventEmitter();

  public ingresoValido: boolean = false;
  @Output() OutIngresoValido: EventEmitter<boolean> = new EventEmitter();

  public ingForm = new FormGroup({
    ingresoValor: new FormControl(0),
  });

  public datosPaciente: IdatosPaciente | undefined;

  public listaDatosPaciente: Iingreso[] = [];
  public contenido!: IContenido;
  public activar: boolean = false;
  public tipoAlerta: string = '';

  IDatosPaciente: any;

  ObtenerDatosPaciente() {
    let fieldIngreso = this.ingForm.controls.ingresoValor.value ?? 0;
    console.log('Ingreso', fieldIngreso);

    if (fieldIngreso === 0) {
      let lcMensaje = '';
      if (fieldIngreso === 0) {
        lcMensaje = 'Debe digitar Ingreso';
      }

      this.activar = true;
      this.tipoAlerta = 'alerta';
      this.contenido = {
        titulo: 'Validación',
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: '',
      };
    } else {
      this.servicioAnestesia.ObtenerDatosIngreso(fieldIngreso).subscribe(
        (respuesta: any) => {
          if (respuesta.success == true) {
            this.ingresoValido = true;
            this.ingForm.controls.ingresoValor.disable();
            const data = respuesta.datos;

            console.log(data);

            this.datosPaciente = {
              tipoidenpaciente: data.cId,
              identificacionpaciente: data.nId,
              ingreso: data.nIngreso,
              especialidadmedico: data.oMedicoTratante.ESPECIALIDAD,
              procedimiento: '',
              registromedicorealiza: data.oMedicoTratante.REGISTRO,
              nitingreso: data.oPlanIngreso.cNit,
              viaingreso: data.cVia,
              planingreso: data.cPlan,
              habitacionpaciente: data.oHabitacion.cHabitacion,
              posnopos: '',
              cobrable: '',
              usuariocreacion: '',
              programacreacion: '',
              descripcion: '',
              consecutivocita: '',
            };

            console.log(this.datosPaciente);
          } else {
            this.ingresoValido = false;
            this.activar = true;
            this.tipoAlerta = 'advertencia';
            this.contenido = {
              titulo: 'Advertencia',
              contenido: 'Paciente no existe',
              boton1: 'Aceptar',
              boton2: '',
            };
            return;
          }
          this.OutIngresoValido.emit(this.ingresoValido);
        },
        (error) => {
          console.log(error);
        }
      );
    }
  }

  primerBotonEvento(evento: boolean): void {
    this.activar = false;
  }

  onLimpiar() {
    this.ingForm.reset();
    this.ingForm.controls.ingresoValor.enable();
    this.ingresoValido = false;

    this.OutIngresoValido.emit(this.ingresoValido);

    return false;
  }
}
