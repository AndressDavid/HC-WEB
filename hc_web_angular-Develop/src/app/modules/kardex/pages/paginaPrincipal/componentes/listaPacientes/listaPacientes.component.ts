import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, OnInit, Output, EventEmitter, Input, ViewChild, ElementRef, Renderer2, ChangeDetectorRef } from '@angular/core';
import { DatosEncabezadoService } from '../../servicios/datosEncabezado.service';
import { ListaPacientesService } from './lista-pacientes.service';
import { IlistaPacientes } from '../../interfaces/IlistaPacientes';
import { IDatosEncabezado } from '../../interfaces/IdatosEncabezado';
import { TableModule } from 'primeng/table';
import { FormsModule } from '@angular/forms';
import { BuscarComponent } from "../buscar/buscar.component";
import { IListaPacientesKar } from '../../interfaces/IlistaPacientesKar';
import { ButtonModule } from 'primeng/button';
import { PaginatorModule } from 'primeng/paginator';
import { SelectButtonModule } from 'primeng/selectbutton';
import { IDatosObservaciones } from '../../interfaces/IdatosObservaciones';

@Component({
  selector: 'app-lista-pacientes',
  standalone: true,
  templateUrl: './listaPacientes.component.html',
  styleUrl: './listaPacientes.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
  imports: [
    CommonModule,
    TableModule,
    FormsModule,
    BuscarComponent,
    ButtonModule,
    PaginatorModule,
    SelectButtonModule
  ]
})
export class ListaPacientesComponent implements OnInit {
  listaKardexPaciente: IListaPacientesKar[] = [];
  dataFiltrada: IListaPacientesKar[] = [];
  totalRecords: number = 0;
  loading: boolean = true;
  rows: number = 10;
  filtro: string = '';
  secciones: any;
  seccionSeleccionada!: string;
  lcConfirmarDatos: string = "Confirmar datos usuario";
  lcIngreso: string = "Ingreso:";
  lcDocumento: string = "Documento:";
  lcPaciente: string = "Paciente:";
  lcHabitacion: string = "Habitación:";
  lcSeccion: string = "Sección:";
  lcOpciones: string = "Opciones";
  lcKardex: string = "Kardex";
  lcObservaciones: string = "Observaciones";
  lcGuardar: string = "Guardar";
  lcTodasSecciones: string = "Todas las secciones";
  lcLimpiar: string = "Limpiar";
  lcCargando: string = "Cargando...";
  lcOpcBoton: string = "Opc.";
  lcNumeroIngreso = "Número de ingreso";
  lcTipoIdentificacion: string = "Tipo identificación";
  lcNumeroIdentificacion: string = "Número Identificación";
  lcNombrePaciente: string = "Nombre Paciente";
  lcNoHayPacientes: string = "No se encontraron pacientes.";
  ingresoPaciente: any;
  tipoDocumento: any;
  numeroDocumento: any;
  habitacion: any;
  primerNombre: any;
  segundoNombre: any;
  primerApellido: any;
  segundoApellido: any;
  seccion: any;
  datosIngresoEncabezados: IDatosEncabezado[] = [];
  InEntradaMostrarModal: boolean = true;
  mostrarModuloBusqueda?: boolean;
  Listpacientes: IlistaPacientes[] = [];
  recibeDatosPacientes: any[] = [];
  mostrarBotonKardex: boolean = true;
  lcObservacionesKar: any;
  datosObservaciones: IDatosObservaciones[] = [];
  lcObservacionKardex: any;
  mostrarAlerta:boolean = false;


  @Output() OutDatosCabeceraDatos: EventEmitter<IDatosEncabezado[]> = new EventEmitter<IDatosEncabezado[]>();
  @Output() OutSalidaModal: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutModuloBusqueda: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutListadoPacientes: EventEmitter<IlistaPacientes[]> = new EventEmitter<IlistaPacientes[]>();

  constructor(private obtieneListaPacientes: ListaPacientesService, private datosEncabezadoService: DatosEncabezadoService,
    private cdr: ChangeDetectorRef) { }



  ngOnInit(): void {
    this.seccionSeleccionada = "Todas las secciones";
    this.obtienePacientesAct()
    this.obtieneTablaPacientes();
    this.cargaListaPacientes();
    this.obtienePacientesAct();
    this.obtenerSecciones();
    this.cargaListaPacientes();
    this.cdr.detectChanges();
  }

  onPageChange(event: any) {
    const firstRecord = event.first;
    const rowsPerPage = event.rows;
    this.cargaListaPacientes();
  }

  obtenerSecciones() {
    this.obtieneListaPacientes.obtenerPacientesKardex().subscribe(
      (respuesta) => {
        if (respuesta && respuesta.listaKardexPaciente && Array.isArray(respuesta.listaKardexPaciente)) {
          const seccionesConDescripcionMap = new Map<string, string>();
          respuesta.listaKardexPaciente.forEach(paciente => {
            if (paciente.seccion && paciente.descripcionSeccion) {
              if (!seccionesConDescripcionMap.has(paciente.seccion)) {
                seccionesConDescripcionMap.set(paciente.seccion, paciente.descripcionSeccion);
              }
            }
          });
          const seccionesFiltradas = Array.from(seccionesConDescripcionMap)
            .filter(([seccion, descripcion]) => descripcion.trim() !== '')
            .map(([seccion, descripcion]) => ({
              seccionDescripcion: `${seccion} - ${descripcion}`
            }));
          this.secciones = seccionesFiltradas;
        } else {
          console.log("La respuesta no contiene datos de pacientes o los datos no son una matriz");
        }
      },
      (error: any) => {
        console.log("Error al obtener datos de pacientes:", error);
      }
    );
  }

  obtenerSeccionDesdeDescripcion(descripcionConcatenada: string): string {
    const partes = descripcionConcatenada.split('-');
    const seccion = partes[0].trim();
    return seccion;
  }

  filtrarPorSeccion() {
    if (this.seccionSeleccionada) {
      this.dataFiltrada = this.listaKardexPaciente.filter(paciente => {
        return paciente.seccion === this.obtenerSeccionDesdeDescripcion(this.seccionSeleccionada);
      });
      this.totalRecords = this.dataFiltrada.length;
    } else {
      this.dataFiltrada = this.listaKardexPaciente;
      this.totalRecords = this.listaKardexPaciente.length;
    }
  }

  abreModal(seccion: any, habitacion: any, ingresoPaciente: any, tipoDocumento: any, numeroDocumento: any, primerNombre: any, segundoNombre: any, primerApellido: any) {
    this.seccion = seccion;
    this.habitacion = habitacion;
    this.ingresoPaciente = ingresoPaciente;
    this.tipoDocumento = tipoDocumento;
    this.numeroDocumento = numeroDocumento;
    this.primerNombre = primerNombre;
    this.segundoNombre = segundoNombre;
    this.primerApellido = primerApellido;
    this.obtenerEstadoTurno(this.ingresoPaciente);
    this.obtenerObservaciones(this.ingresoPaciente);
    this.cdr.detectChanges();
  }

  mensajeError: boolean = false;
  ingresoId: any;
  mostrarModalTurno: boolean = false;
  buscarIngreso(ingresoPaciente: any) {
    setTimeout(() => {
      this.obtenerEstadoTurno(ingresoPaciente);
    }, 1000)
    this.mostrarBotonKardex = false;
    this.datosEncabezadoService.obtenerDatosCabecera(ingresoPaciente).subscribe(
      ({ datos }: { datos: IDatosEncabezado }) => {
        this.datosIngresoEncabezados = [datos];
        this.OutDatosCabeceraDatos.emit(this.datosIngresoEncabezados);
        this.OutModuloBusqueda.emit(true);
        this.cdr.detectChanges();
        this.cerrarModal();
      },
      (error) => {
        this.mensajeError = true;
        this.mostrarBotonKardex;
        console.error('Error al obtener datos de ingreso aafasdfasdfasd :', error);
      },
    );
  }


  cerrarModal() {
    this.InEntradaMostrarModal = false;
    const backdropElement = document.querySelector('.modal-backdrop');
    if (backdropElement) {
      backdropElement.remove();
    }

    document.body.style.overflow = 'auto';
    this.cdr.detectChanges();

  }

  obtenerEstadoTurno(ingresoPaciente: any) {
    this.datosEncabezadoService.obtenerEstadoTurno(ingresoPaciente).subscribe((respuesta) => {
      this.ingresoId = respuesta.Respuesta;
      if (this.ingresoId === "Turno Cerrado") {
        console.log("Esta es la respuesta del turno si es cerrado", this.ingresoId);
        this.cdr.detectChanges();
        return this.mostrarModalTurno = true;
      } else {
        console.log("Esta es la respuesta del turno si es abierto", this.ingresoId);
        this.cdr.detectChanges();
        return this.mostrarModalTurno = false;
      }
    });
  }


  cargaListaPacientes() {
    // this.loading = true;

    this.obtieneListaPacientes.obtenerPacientesKardex().subscribe(
      (respuesta) => {
        if (respuesta && respuesta.listaKardexPaciente && Array.isArray(respuesta.listaKardexPaciente)) {
          this.listaKardexPaciente = respuesta.listaKardexPaciente;
          this.totalRecords = this.listaKardexPaciente.length;
          this.dataFiltrada = this.listaKardexPaciente;
        } else {
          console.log("La respuesta no contiene datos de pacientes o los datos no son una matriz");
        }
        setTimeout(() => {
          this.loading = false;
          this.cdr.detectChanges();
        }, 1000)

      },
      (error: any) => {
        console.log("Error al obtener datos de pacientes:", error);
        this.loading = false;
      }
    );
  }

  filtrar(filtro: string) {
    this.filtro = filtro;
    this.aplicarFiltro();
  }

  private aplicarFiltro() {
    this.dataFiltrada = this.listaKardexPaciente.filter((paciente: IListaPacientesKar) => {
      return paciente.primerNombre.toLowerCase().includes(this.filtro.toLowerCase()) ||
        paciente.primerApellido.toLowerCase().includes(this.filtro.toLowerCase()) ||
        paciente.ingreso.toString().includes(this.filtro.toLowerCase());
    });
    this.totalRecords = this.dataFiltrada.length;
  }

  obtieneTablaPacientes() {
    this.obtieneListaPacientes.obtenerPacientesKardex().subscribe((respuesta) => {
      console.log("Esta es la full respuesta: " + respuesta);
    })
  }

  obtienePacientesAct() {
    this.obtieneListaPacientes.obtenerPacientesKardex().subscribe(
      (respuesta) => {
        if (respuesta && respuesta.listaKardexPaciente && Array.isArray(respuesta.listaKardexPaciente)) {
          this.listaKardexPaciente = respuesta.listaKardexPaciente;
          this.dataFiltrada = this.listaKardexPaciente;
          this.totalRecords = this.listaKardexPaciente.length;
          this.loading = false;
        } else {
          console.log("La respuesta no contiene datos de pacientes o los datos no son una matriz");
        }
      },
      (error: any) => {
        console.log("Error al obtener datos de pacientes:", error);
      }
    );
  }

  ngAfterViewInit() {
    this.seccionSeleccionada = "";
  }

  obtenerObservaciones(ingreso: number) {
    this.datosEncabezadoService.obtenerObservaciones(ingreso).subscribe((response) => {
      this.datosObservaciones = response.obtenerKardexObservaciones;
      if (this.datosObservaciones.length > 0) {
        console.log("hay datos para este ingreso =============>", this.datosObservaciones);
      } else {
        console.log("No hay datos aun de observaciones para este ingreso");
      }
    },
      error => {
        console.error('Error al obtener observaciones:', error);
      }
    );
  }

  mostrarAlertaError:boolean = false;

  guardarDatosObservaciones() {

    if (!this.lcObservacionKardex || this.lcObservacionKardex.trim() === '') {
      console.log("Error: La observación está vacía.");
      // Puedes mostrar una alerta al usuario aquí
      this.mostrarAlertaError = true;
      setTimeout(() => {
        this.mostrarAlertaError = false;
      }, 3000);
      return;
    }
    const fechaIngInst = new Date();
    const datosObservaciones = {
      ingreso: this.ingresoPaciente,
      observaciones: this.lcObservacionKardex,
      usuario_crea: "SIDANVAR",
      fecha_creacion: this.formatearFecha(fechaIngInst),
    }
    this.datosEncabezadoService.guardarDatosObservaciones(datosObservaciones).subscribe(response => {
      if (response) {
        console.log("Estos son los datos guardados");
        this.mostrarAlerta = true;
        setTimeout(() => {
          this.mostrarAlerta = false;
          location.reload();
        }, 2000);
        setTimeout(() => {
          this.cerrarModal();
        }, 1000);
        this.cdr.detectChanges();
      } else {
        console.log("Paila no se guardaron los datos");
      }
    });
  }

  convertirFecha(cadenaFecha: any) {
    const año = parseInt(cadenaFecha.substring(0, 4), 10);
    const mes = parseInt(cadenaFecha.substring(4, 6), 10) - 1;
    const día = parseInt(cadenaFecha.substring(6, 8), 10);
    return new Date(año, mes, día);
  }

  formatearFecha(fecha: any) {
    const año = fecha.getFullYear();
    const mes = ('0' + (fecha.getMonth() + 1)).slice(-2);
    const día = ('0' + fecha.getDate()).slice(-2);
    const horas = ('0' + fecha.getHours()).slice(-2);
    const minutos = ('0' + fecha.getMinutes()).slice(-2);
    const segundos = ('0' + fecha.getSeconds()).slice(-2);

    return `${año}-${mes}-${día} ${horas}:${minutos}:${segundos}`;
  }


}


