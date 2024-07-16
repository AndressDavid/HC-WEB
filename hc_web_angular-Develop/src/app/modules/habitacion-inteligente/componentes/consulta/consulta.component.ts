import { Component, EventEmitter, Output } from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
} from '@angular/forms';
import { IContenido } from '../../../common/modal-dinamico/interfaces/contenidoModal';
import { CommonModule } from '@angular/common';
import { ModalDinamicoComponent } from '../../../common/modal-dinamico/modal-dinamico.component';
import { ModalNuevoComponent } from './modal-nuevo/modal-nuevo.component';
import { Ihab } from '../interfaces/Ihab';
import { ConsultaService } from '../servicios/consulta.service';
import { ITabla } from '../interfaces/ITabla';

@Component({
  selector: 'app-consulta',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
    ModalNuevoComponent,
  ],
  templateUrl: './consulta.component.html',
  styleUrl: './consulta.component.css',
})
export class ConsultaComponent {
  public nuevaHabModal: boolean = false;
  @Output() OutNuevaHabModal: EventEmitter<boolean> = new EventEmitter();

  public datosTabla: ITabla[] = [];
  @Output() OutDatosTabla: EventEmitter<ITabla[]> = new EventEmitter();

  public consultaForm = new FormGroup({
    seccionValor: new FormControl(''),
    camaValor: new FormControl(''),
    ipDispValor: new FormControl(''),
    macDispValor: new FormControl(''),
    activoValor: new FormControl('-1'),
  });

  public activar: boolean = false;
  public tipoAlerta: String = 'Alerta';
  public contenido!: IContenido;
  public datos: any;
  public secciones: any;

  public listadoHabitaciones: Ihab[] = [];

  @Output() OutListadoHabitaciones: EventEmitter<Ihab[]> = new EventEmitter();

  constructor(public consultaService: ConsultaService) {}

  ngOnInit(): void {
    this.obtenerHabitaciones();
    this.obtenerSecciones();
    this.consultarDatos();
  }

  onNuevo() {
    this.nuevaHabModal = true;

    this.OutNuevaHabModal.emit(this.nuevaHabModal);
  }

  close(event: boolean) {
    this.nuevaHabModal = event;
  }

  buscarHabitacion() {
    let fieldSeccion = this.consultaForm.controls.seccionValor.value ?? '';
    let fieldHabitacion = this.consultaForm.controls.camaValor.value ?? '';
    let fieldActivo = this.consultaForm.controls.activoValor.value ?? '';

    if (fieldSeccion === '' && fieldHabitacion === '' && fieldActivo === '') {
      let lcMensaje = '';
      lcMensaje = 'Debe seleccionar una Sección o una Cama';

      this.activar = true;
      this.tipoAlerta = 'alerta';
      this.contenido = {
        titulo: 'Validación',
        contenido: lcMensaje,
        boton1: 'Aceptar',
        boton2: '',
      };
    } else {
      this.consultarDatos();
    }
    this.consultarDatos();
  }

  ValidarDatos() {
    this.activar = false;
  }

  consultarDatos() {
    let lcSeccion: any = '';
    lcSeccion = this.consultaForm.controls.seccionValor.value ?? '';

    let lcCama: any = '';
    lcCama = this.consultaForm.controls.camaValor.value ?? '';

    let lcActivo: any = '';
    lcActivo = this.consultaForm.controls.activoValor.value ?? '';

    this.consultaService.cSeccionB = lcSeccion;
    this.consultaService.cHabitacionB = lcCama;
    this.consultaService.cActivoB = lcActivo;

    // Todos los campos vacios

    if (lcCama === '' && lcSeccion === '' && lcActivo === '') {
      this.consultaService.obtenerDatosTablaTodos().subscribe(
        (response) => {
          this.datos = response.data;

          console.log(this.datos);
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }

    // Todos los campos llenos

    if (lcCama !== '' && lcSeccion !== '' && lcActivo !== '') {
      this.consultaService
        .obtenerDatosTabla(lcSeccion, lcCama, lcActivo)
        .subscribe(
          (response) => {
            this.datos = response.data;
            this.OutDatosTabla.emit(this.datos);
          },
          (error) => {
            console.log(error);
          }
        );
    }

    // Solo seccion lleno

    if (lcCama === '' && lcSeccion !== '' && lcActivo === '') {
      this.consultaService.obtenerDatosTablaSeccion(lcSeccion).subscribe(
        (response) => {
          this.datos = response.data;

          console.log(this.datos);
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }

    // Solo habitacion lleno

    if (lcCama !== '' && lcSeccion === '' && lcActivo === '') {
      this.consultaService.obtenerDatosTablaHab(lcCama).subscribe(
        (response) => {
          this.datos = response.data;
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }

    // Solo activo lleno

    if (lcCama === '' && lcSeccion === '' && lcActivo !== '') {
      this.consultaService.obtenerDatosTablaActivo(lcActivo).subscribe(
        (response) => {
          this.datos = response.data;
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }

    // Seccion y habitacion lleno

    if (lcCama !== '' && lcSeccion !== '' && lcActivo === '') {
      this.consultaService.obtenerDatosTablaSecHab(lcSeccion, lcCama).subscribe(
        (response) => {
          this.datos = response.data;
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }

    // Seccion y activo lleno

    if (lcCama === '' && lcSeccion !== '' && lcActivo !== '') {
      this.consultaService
        .obtenerDatosTablaSecAct(lcSeccion, lcActivo)
        .subscribe(
          (response) => {
            this.datos = response.data;
            this.OutDatosTabla.emit(this.datos);
          },
          (error) => {
            console.log(error);
          }
        );
    }

    // Cama y activo lleno

    if (lcCama !== '' && lcSeccion === '' && lcActivo !== '') {
      this.consultaService.obtenerDatosTablaCamaAct(lcActivo, lcCama).subscribe(
        (response) => {
          this.datos = response.data;
          this.OutDatosTabla.emit(this.datos);
        },
        (error) => {
          console.log(error);
        }
      );
    }
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
    console.log(this.consultaForm.controls.seccionValor.value);
    const seccion = this.consultaForm.controls.seccionValor.value;
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
