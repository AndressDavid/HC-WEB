import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, OnInit, Output } from '@angular/core';
import { DatosEncabezadoService } from '../../servicios/datosEncabezado.service';
import { IDatosKardex } from '../../interfaces/IdatosSituacion';

@Component({
  selector: 'app-seleccion',
  standalone: true,
  imports: [
    CommonModule,
  ],
  templateUrl: './seleccion.component.html',
  styleUrl: './seleccion.component.css',
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class SeleccionComponent implements OnInit {

  constructor(private datosEncabezadoService: DatosEncabezadoService) { }

  situacion: string = " Situación";
  antecedentes: string = " Antecedentes";
  evaluacion: string = " Evaluación";
  recomendacion: string = " Recomendación";
  CamposSituacion: boolean = true;
  CamposEvaluacion: boolean = true;
  CamposAntecedente: boolean = true;
  CamposRecomendacion: boolean = true;
  CargaSpinnerSituacion: boolean = true;
  CargaSpinnerAntecedentes: boolean = false;
  CargaSpinnerEvaluacion: boolean = true;
  CargaSpinnerRecomendacion: boolean = true;
  datosKardex: IDatosKardex[] = [];
  datosKardexSituacion: IDatosKardex[] = [];
  cargaEnCurso: boolean[] = [true, false, false, false];
  enlaceSeleccionado: number = 0;

  @Output() OutSeleccionSituacion: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutSeleccionAntedecentes: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutSeleccionEvaluacion: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutSeleccionRecomendacion: EventEmitter<boolean> = new EventEmitter<boolean>();

  @Output() OutDatosDeSituacion: EventEmitter<IDatosKardex[]> = new EventEmitter<IDatosKardex[]>();
  @Output() OutDatosDeAntecedentes: EventEmitter<IDatosKardex[]> = new EventEmitter<IDatosKardex[]>();
  @Output() OutDatosDeEvaluacion: EventEmitter<IDatosKardex[]> = new EventEmitter<IDatosKardex[]>();
  @Output() OutDatosDeRecomendacion: EventEmitter<IDatosKardex[]> = new EventEmitter<IDatosKardex[]>();

  seleccionarEnlace(index: number) {
    this.enlaceSeleccionado = index;
  }

  ngOnInit(): void {
    this.enviaSalidaSituacion();

  }

  enviaSalidaSituacion() {
    this.seleccionarEnlace(0);
    this.datosEncabezadoService.obtenerDatosPrincipales().subscribe(response => {
      if (response.obtenerKardexSituacion && response.obtenerKardexSituacion.length > 0) {
        console.log("Aqui entramos mis perros a JAVA");
        this.datosKardexSituacion = response.obtenerKardexSituacion[0];
        console.log("Estos son los datos", this.datosKardexSituacion);
        this.OutDatosDeSituacion.emit(this.datosKardexSituacion);
        this.OutSeleccionSituacion.emit(this.CamposSituacion);
      } else {
        this.datosEncabezadoService.obtenerNombres().subscribe((respuesta) => {
          console.log("Aqui entramos mis perro a PHP");
          this.datosKardex = respuesta.datosKardex;
          this.OutDatosDeSituacion.emit(this.datosKardex);
          this.OutSeleccionSituacion.emit(this.CamposSituacion);
          console.log("esta es la respuesta muchachos desde seleccion en onbtenerKardex: ===>", this.datosKardex);
        });
      }
    })
  }

  enviaSalidaAntecedente() {
    this.seleccionarEnlace(1);
    this.CargaSpinnerAntecedentes = true;
    this.datosEncabezadoService.obtenerNombres().subscribe((respuesta) => {
      this.datosKardex = respuesta.datosKardex;
      this.OutDatosDeAntecedentes.emit(this.datosKardex);
      this.OutSeleccionAntedecentes.emit(this.CamposAntecedente);
      console.log("esta es la respuesta muchachos desde antecedentes en onbtener Kardex full: ===>", this.datosKardex);
    });
  }


  enviaSalidaEvaluacion() {
    this.seleccionarEnlace(2);
    this.datosEncabezadoService.obtenerNombres().subscribe((respuesta) => {
      this.datosKardex = respuesta.datosKardex;
      this.OutDatosDeEvaluacion.emit(this.datosKardex);
      this.OutSeleccionEvaluacion.emit(this.CamposEvaluacion);
      console.log("esta es la respuesta muchachos desde evaluacion en onbtener Kardex full: ===>", this.datosKardex);
    });
  }

  enviaSalidaRecomendacion() {
    this.seleccionarEnlace(3);
    this.datosEncabezadoService.obtenerNombres().subscribe((respuesta) => {
      this.datosKardex = respuesta.datosKardex;
      this.OutDatosDeRecomendacion.emit(this.datosKardex);
      this.OutSeleccionRecomendacion.emit(this.CamposRecomendacion);
      console.log("esta es la respuesta muchachos desde recomendaciones en onbtener Kardex full: ===>", this.datosKardex);
    });
  }


}
