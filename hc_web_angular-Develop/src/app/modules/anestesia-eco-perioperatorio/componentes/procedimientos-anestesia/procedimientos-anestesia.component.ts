import { DatosEncabezadoService } from './../../../kardex/pages/paginaPrincipal/servicios/datosEncabezado.service';
import { Component, EventEmitter, Input, Output } from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { Iprocedimientos } from '../interfaces/Iprocedimientos';
import { ServicioAnestesiaService } from '../servicios/servicio-anestesia.service';
import { IContenido } from '../../../common/modal-dinamico/interfaces/contenidoModal';
import { CommonModule } from '@angular/common';
import { ModalDinamicoComponent } from '../../../common/modal-dinamico/modal-dinamico.component';
import { CabeceraPacienteComponent } from '../../../common/cabecera-paciente/cabecera-paciente.component';
import { IngresoComponent } from '../ingreso/ingreso.component';
import { IdatosPaciente } from '../interfaces/IdatosPaciente';

@Component({
  selector: 'app-procedimientos-anestesia',
  standalone: true,
  templateUrl: './procedimientos-anestesia.component.html',
  styleUrl: './procedimientos-anestesia.component.css',
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
    CabeceraPacienteComponent,
    IngresoComponent,
  ],
})
export class ProcedimientosAnestesiaComponent {
  @Input() ingresoEsValido?: boolean = false;
  public ingresoValido: boolean = false;

  public listadoProcedimientos: Iprocedimientos[] = [];
  @Output() OutListadoProcedimientos: EventEmitter<Iprocedimientos[]> =
    new EventEmitter();

  private datosPaciente: IdatosPaciente | undefined;

  constructor(public anestesiaService: ServicioAnestesiaService) {}

  public procForm = new FormGroup({
    procValor: new FormControl(),
    cobroValor: new FormControl(),
    descValor: new FormControl(),
  });

  public activar: boolean = false;
  public activarConfir: boolean = false;
  public tipoAlerta: String = 'Alerta';
  public tipoModal: String = 'Alerta';
  public tipoModalConfir: String = 'Confirmacion';
  public contenido!: IContenido;
  public contenidog!: IContenido;
  public contenidoConfir!: IContenido;

  guardarProcedimiento() {
    let fieldProcedimientos = this.procForm.controls.procValor.value ?? '';
    let fieldCobro = this.procForm.controls.cobroValor.value ?? '';
    let fieldDescripcion = this.procForm.controls.descValor.value ?? '';

    if (fieldProcedimientos === '' || fieldCobro === '' || fieldDescripcion) {
      let lcMensaje = '';
      if (fieldProcedimientos === '') {
        lcMensaje = 'Debe seleccionar un Procedimiento';
      } else {
        if (fieldCobro === '') {
          lcMensaje = 'Debe seleccionar Cobro';
        } else {
          if (fieldDescripcion === '') {
            lcMensaje = 'Debe ingresar una descipción';
          }
        }
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
      let lcMensaje =
        'Si guarda los cambios, <b>NO</b> podra modificarlos después.<br><b>¿Está seguro que desea Guardar los datos?</b>';
      this.tipoModalConfir = 'confirmacion';
      this.activarConfir = true;
      this.activar = false;
      this.contenidoConfir = {
        titulo: 'Confirmacion',
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: 'Cancelar',
      };
    }
  }

  primerBotonEvento(): void {
    this.activar = false;
    this.activarConfir = false;
    this.OrganizarInformacion();

    this.activar = true;
    this.tipoModal = 'alerta';
    this.contenidog = {
      titulo: 'Validación',
      contenido: 'Los procedimientos se han guardado',
      boton1: 'Aceptar',
      boton2: '',
    };
  }

  @Input() tipoidenpaciente!: string;
  @Input() identificacionpaciente!: string;
  @Input() ingreso!: number;
  @Input() especialidadmedico!: string;
  @Input() procedimiento!: string;
  @Input() registromedicorealiza!: string;
  @Input() nitingreso!: string;
  @Input() viaingreso!: string;
  @Input() planingreso!: string;
  @Input() habitacionpaciente!: string;
  @Input() posnopos!: string;
  @Input() cobrable!: string;
  @Input() usuariocreacion!: string;
  @Input() programacreacion!: string;
  @Input() descripcion!: string;
  @Input() consecutivocita!: string;

  OrganizarInformacion() {

    this.datosPaciente = {
      tipoidenpaciente: this.tipoidenpaciente,
      identificacionpaciente: this.identificacionpaciente,
      ingreso: this.anestesiaService.nIngreso,
      especialidadmedico: this.especialidadmedico,
      procedimiento: this.procedimiento,
      registromedicorealiza: this.registromedicorealiza,
      nitingreso: this.nitingreso,
      viaingreso: this.viaingreso,
      planingreso: this.planingreso,
      habitacionpaciente: this.habitacionpaciente,
      posnopos: this.posnopos,
      cobrable: this.cobrable,
      usuariocreacion: this.usuariocreacion,
      programacreacion: this.programacreacion,
      descripcion: this.descripcion,
      consecutivocita: this.consecutivocita,
    };
    console.log(this.datosPaciente);
    this.anestesiaService
      .guardarDatosAnestesia(this.datosPaciente)
      .subscribe((respuesta: any) => {
        const datos = respuesta.data;
        console.log(datos);
      });
  }

  segundoBotonEvento(): void {
    this.activarConfir = false;
  }

  ValidarDatos() {
    this.activar = false;
  }

  onLimpiar() {
    this.procForm.reset();
    console.log(this.listadoProcedimientos);
    return false;
  }

  ngOnInit(): void {
    this.obtenerProcedimientos();
  }

  obtenerProcedimientos() {
    this.anestesiaService.obtenerListaProcedimientos().subscribe(
      (respuesta: any) => {
        const { data } = respuesta;
        this.listadoProcedimientos = data;

        console.log(this.listadoProcedimientos);

        this.OutListadoProcedimientos.emit(this.listadoProcedimientos);
      },
      (error) => {
        console.log(error);
      }
    );
  }
}
