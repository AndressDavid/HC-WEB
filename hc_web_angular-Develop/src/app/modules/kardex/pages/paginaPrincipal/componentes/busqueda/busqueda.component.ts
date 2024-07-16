import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, EventEmitter, Input, Output } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { DatosEncabezadoService } from '../../servicios/datosEncabezado.service';
import { IDatosEncabezado } from '../../interfaces/IdatosEncabezado';
import { FooterKardexComponent } from "../footerKardex/footerKardex.component";

@Component({
    selector: 'app-busqueda',
    standalone: true,
    templateUrl: './busqueda.component.html',
    styleUrls: ['./busqueda.component.css'],
    changeDetection: ChangeDetectionStrategy.OnPush,
    imports: [
        CommonModule,
        FormsModule,
    ]
})
export class BusquedaComponent {

  constructor(private datosEncabezadoService: DatosEncabezadoService) { }

  lcIngreso: string = 'Ingreso';
  lcSeccion: string = 'Seccion'
  lcBuscar: string = 'Buscar';
  lcLimpiar: string = 'Limpiar';
  textoInput: string = '';
  disableBoton: boolean = true;
  datosIngresoEncabezados: IDatosEncabezado[] = [];
  mostrarTablaB:boolean=false;
  mostrarBusqueda:boolean=true;

  @Output() OutDatosCabecera: EventEmitter<IDatosEncabezado[]> = new EventEmitter<IDatosEncabezado[]>();
  @Output() OutMostrarTabla: EventEmitter<boolean> = new EventEmitter<boolean>();
  @Output() OutMostrarBusqueda: EventEmitter<boolean> = new EventEmitter<boolean>();

  verificarTextoInput(): void {
    const soloNumeros = /^[0-9]+$/;
    if (soloNumeros.test(this.textoInput)) {
      this.disableBoton = this.textoInput.trim() === '';
    } else {
      this.disableBoton = true;
    }
  }

  buscarIngreso(): void {
    if (this.textoInput.trim() !== '') {
      this.datosEncabezadoService.obtenerDatosCabecera(this.textoInput).subscribe(  
        ({ datos }: { datos: IDatosEncabezado }) => {
          this.datosIngresoEncabezados = [datos];
           this.mostrarBusqueda = false
          this.OutDatosCabecera.emit([datos]);
          this.OutMostrarTabla.emit(this.mostrarTablaB);
          this.OutMostrarBusqueda.emit(this.mostrarBusqueda);
        },
        (error) => {
          console.error('Error al obtener datos de ingreso:', error);
        }
      );
    }
  }
  
  @Output() filtroChange = new EventEmitter<string>();

  onInputChange(event: any) {
    this.filtroChange.emit(event.target.value);
  }

  @Input() mostrarModuloBusqueda?: boolean;

}