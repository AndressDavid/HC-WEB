import {
  Component,
  ElementRef,
  EventEmitter,
  Output,
  ViewChild,
} from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Ihab } from '../../interfaces/Ihab';
import { ConsultaService } from '../../servicios/consulta.service';
import { IContenido } from '../../../../common/modal-dinamico/interfaces/contenidoModal';
import { ModalDinamicoComponent } from '../../../../common/modal-dinamico/modal-dinamico.component';
import { IdatosGuardar } from '../../interfaces/IdatosGuardar';
import { ITabla } from '../../interfaces/ITabla';
import { Router } from '@angular/router';

@Component({
  selector: 'app-modal-nuevo',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
  ],
  templateUrl: './modal-nuevo.component.html',
  styleUrls: ['./modal-nuevo.component.css'],
})
export class ModalNuevoComponent {
  @ViewChild('nuevaHabModal', { static: false }) nuevaHabModal?: ElementRef;
  @Output() OutNuevaHabModal: EventEmitter<boolean> = new EventEmitter();

  public fieldSeccion: string = '';
  public fieldHabitacion: string = '';
  public fieldIpValor: string = '';
  public fieldMacValor: string = '';
  public fieldActivo: number = 0;

  public nuevoForm = new FormGroup({
    seccionValor: new FormControl(''),
    habitacionValor: new FormControl(''),
    ipDispValor: new FormControl(''),
    macDispValor: new FormControl(''),
    activo: new FormControl(0),
  });

  close(boolean: false) {
    this.OutNuevaHabModal.emit(false);
  }

  abrir() {
    this.OutNuevaHabModal.emit(true);
  }

  onCancelar() {
    this.OutNuevaHabModal.emit(false);
  }

  secciones: any;

  public listadoHabitaciones: Ihab[] = [];

  @Output() OutListadoHabitaciones: EventEmitter<Ihab[]> = new EventEmitter();

  @Output() OutDatosTabla: EventEmitter<ITabla[]> = new EventEmitter();

  public datosGuardar: IdatosGuardar | undefined;

  constructor(public consultaService: ConsultaService) {}

  ngOnInit(): void {
    this.ObtenerUsuario();
    this.obtenerHabitaciones();
    this.obtenerSecciones();
  }

  public activar: boolean = false;
  public tipoModal: String = 'Alerta';
  public contenido!: IContenido;
  public activarConfir: boolean = false;
  public tipoModalConfir: String = 'Confirmacion';
  public contenidoConfir!: IContenido;
  public activarVal: boolean = false;
  public tipoModalVal: String = 'Confirmacion';
  public contenidoVal!: IContenido;
  public usuario: any;
  public datos: any;

  guardarNuevaHabitacion() {
    this.fieldSeccion = this.nuevoForm.controls.seccionValor.value ?? '';
    this.fieldHabitacion = this.nuevoForm.controls.habitacionValor.value ?? '';
    this.fieldIpValor = this.nuevoForm.controls.ipDispValor.value ?? '';
    this.fieldMacValor = this.nuevoForm.controls.macDispValor.value ?? '';
    this.fieldActivo = this.nuevoForm.controls.activo.value ?? 0;

    if (
      this.fieldSeccion === '' ||
      this.fieldHabitacion === '' ||
      this.fieldIpValor === '' ||
      this.fieldMacValor === '' ||
      this.fieldActivo < 0
    ) {
      let lcMensaje: String = '';

      switch (true) {
        case this.fieldSeccion === '':
          lcMensaje = 'El dato sección es obligatorio.';
          break;
        case this.fieldHabitacion === '':
          lcMensaje = 'El dato habitación es obligatorio.';
          break;
        case this.fieldIpValor === '':
          lcMensaje = 'El dato ip de dispositivo es obligatorio.';
          break;
        case this.fieldMacValor === '':
          lcMensaje = 'El dato mac de dispositivo es obligatorio.';
          break;
        case this.fieldActivo < 0:
          lcMensaje = 'El dato activo es obligatorio.';
          break;
      }
      this.activar = true;
      this.tipoModal = 'alerta';
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

  ValidarDatos() {
    this.activar = false;
  }

  primerBotonEvento(): void {
    this.activar = false;
    this.activarConfir = false;

    this.datosGuardar = {
      tipoDispositivo: 'EQUIPO',
      ipDispositivo: this.fieldIpValor,
      macDispositivo: this.fieldMacValor,
      seccion: this.fieldSeccion,
      habitacion: this.fieldHabitacion,
      activo: this.fieldActivo,
      usuario: this.usuario,
      programa: 'HABI001A',
    };

    console.log(this.datosGuardar);

    this.consultaService
      .guardarHabitaciones(this.datosGuardar)
      .subscribe((respuesta: any) => {
        const datos = respuesta.data;
        this.consultaService.obtenerDatosTablaTodos().subscribe(
          (response) => {
            this.datos = response.data;

            console.log('entro', this.datos);
            this.OutDatosTabla.emit(this.datos);
          },
          (error) => {
            console.log(error);
          }
        );
        this.activarVal = true;
        this.tipoModalVal = 'confirmacion';
        this.contenidoVal = {
          titulo: 'Validación',
          contenido: 'La habitación se ha guardado',
          boton1: 'Aceptar',
          boton2: '',
        };
      });
  }

  CerrarModal() {
    this.activarVal = false;
    this.close(false);
    window.location.href =
      'http://localhost/HCP-PHP-SERVER-2024/hcweb/habitacion-inteligente';
  }

  segundoBotonEvento(): void {
    console.log('segundo boton');
    this.activarConfir = false;
  }

  ObtenerUsuario() {
    this.consultaService.ObtenerUsuario().subscribe((response) => {
      this.usuario = response;
      console.log(this.usuario);
    });
  }

  obtenerSecciones() {
    this.consultaService.obtenerListaSecciones().subscribe((result: any) => {
      console.log(result);
      const data = result['SECCIONES'];
      let dato = new Map();

      for (let [key, value] of Object.entries(data)) {
        let secciones = value as Map<String, String>;
        for (let [key1, value1] of Object.entries(secciones)) {
          if (key1 == 'NOMBRE') {
            dato.set(key, value1);
          }
        }
      }
      this.secciones = dato;
      console.log(this.secciones, 'entro');
    });
  }

  onSeccionChange() {
    const seccion = this.nuevoForm.controls.seccionValor.value;
    if (seccion) {
      this.obtenerHabitacionesPorSeccion(seccion);
    } else {
      this.obtenerHabitaciones();
    }
  }

  obtenerHabitaciones() {
    this.consultaService.obtenerListaHabitaciones().subscribe(
      (respuesta) => {
        const { data } = respuesta;
        this.listadoHabitaciones = data;

        console.log(this.listadoHabitaciones);

        this.OutListadoHabitaciones.emit(this.listadoHabitaciones);
      },
      (error) => {
        console.log(error);
      }
    );
  }

  obtenerHabitacionesPorSeccion(seccion: string) {
    this.consultaService.obtenerListaHabitacionesPorSeccion(seccion).subscribe(
      (respuesta) => {
        const { data } = respuesta;
        this.listadoHabitaciones = data;

        console.log(this.listadoHabitaciones);

        this.OutListadoHabitaciones.emit(this.listadoHabitaciones);
      },
      (error) => {
        console.log(error);
      }
    );
  }
}
