import {
  Component,
  ElementRef,
  EventEmitter,
  Input,
  Output,
  ViewChild,
} from '@angular/core';
import {
  FormGroup,
  FormControl,
  FormsModule,
  ReactiveFormsModule,
  FormBuilder,
  Validators,
} from '@angular/forms';
import { CommonModule } from '@angular/common';
import { Ihab } from '../../interfaces/Ihab';
import { ConsultaService } from '../../servicios/consulta.service';
import { IContenido } from '../../../../common/modal-dinamico/interfaces/contenidoModal';
import { ModalDinamicoComponent } from '../../../../common/modal-dinamico/modal-dinamico.component';
import { ITabla } from '../../interfaces/ITabla';
import { IdatosGuardar } from '../../interfaces/IdatosGuardar';
import { TablaConsultaComponent } from '../tabla-consulta.component';

@Component({
  selector: 'app-modal-editar',
  standalone: true,
  imports: [
    CommonModule,
    FormsModule,
    ReactiveFormsModule,
    ModalDinamicoComponent,
  ],
  templateUrl: './modal-editar.component.html',
  styleUrl: './modal-editar.component.css',
})
export class ModalEditarComponent {
  @ViewChild('editarHabModal', { static: false }) editarHabModal?: ElementRef;

  @Output() OutEditarHabModal: EventEmitter<boolean> = new EventEmitter();
  @Output() OutListadoHabitaciones: EventEmitter<Ihab[]> = new EventEmitter();
  @Output() OutDatosTabla: EventEmitter<ITabla[]> = new EventEmitter();
  @Input() datosHabitacion: ITabla | null = null;
  @Input() datosHab?: TablaConsultaComponent;

  public editarForm: FormGroup;

  constructor(
    private formBuilder: FormBuilder,
    private consultaService: ConsultaService
  ) {
    this.editarForm = this.formBuilder.group({
      seccionValor: [{ value: '', disabled: true }, Validators.required],
      habitacionValor: [{ value: '', disabled: true }, Validators.required],
      ipDispValor: [
        '',
        [Validators.required, Validators.pattern(/^(\d{1,3}\.){3}\d{1,3}$/)],
      ],
      macDispValor: ['', Validators.required],
      activo: [1, Validators.required],
    });
  }

  ngOnInit(): void {
    if (this.datosHabitacion) {
      this.editarForm.patchValue({
        seccionValor: this.datosHabitacion.seccion,
        habitacionValor: this.datosHabitacion.cama,
        ipDispValor: this.datosHabitacion.ipDispositivo,
        macDispValor: this.datosHabitacion.macDispositivo,
        activo: this.datosHabitacion.activo,
      });
    }

    this.obtenerSecciones();
    if (this.datosHabitacion?.seccion) {
      this.obtenerHabitaciones(this.datosHabitacion.seccion);
    }

    this.ObtenerUsuario();
  }

  public fieldSeccion: string = '';
  public fieldHabitacion: string = '';
  public fieldIpValor: string = '';
  public fieldMacValor: string = '';
  public fieldActivo: number = 0;


  close(boolean: false) {
    this.OutEditarHabModal.emit(false);
  }

  abrir() {
    this.OutEditarHabModal.emit(true);
  }

  public datosGuardar: IdatosGuardar | undefined;

  public listadoHabitaciones: Ihab[] = [];

  public activar: boolean = false;
  public tipoModal: String = 'Alerta';
  public contenido!: IContenido;
  public activarConfir: boolean = false;
  public tipoModalConfir: String = 'Confirmacion';
  public contenidoConfir!: IContenido;
  public activarVal: boolean = false;
  public tipoModalVal: String = 'Confirmacion';
  public contenidoVal!: IContenido;
  public secciones: any;
  public usuario: any;
  public datos: any;

  guardarHabitacionEditada() {
    this.fieldSeccion = this.editarForm.controls['seccionValor'].value ?? '';
    this.fieldHabitacion =
      this.editarForm.controls['habitacionValor'].value ?? '';
    this.fieldIpValor = this.editarForm.controls['ipDispValor'].value ?? '';
    this.fieldMacValor = this.editarForm.controls['macDispValor'].value ?? '';
    this.fieldActivo = this.editarForm.controls['activo'].value ?? 0;

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
          lcMensaje = 'El dato Ip del dispositivo es obligatorio.';
          break;
        case this.fieldMacValor === '':
          lcMensaje = 'El dato Mac del dispositivo es obligatorio.';
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

  CerarValidacion() {
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
          contenido: 'La habitación se ha editado correctamente',
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
    const seccion = this.editarForm.controls['seccionValor'].value;
    if (seccion) {
      this.obtenerHabitacionesPorSeccion(seccion);
    } else {
      this.obtenerHabitaciones(seccion);
    }
  }

  obtenerHabitaciones(seccion: string) {
    this.consultaService.obtenerListaHabitaciones().subscribe(
      (respuesta) => {
        const { data } = respuesta;
        this.listadoHabitaciones = data;

        if (this.datosHabitacion) {
          this.editarForm.patchValue({
            habitacionValor: this.datosHabitacion.cama,
          });
        }

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
