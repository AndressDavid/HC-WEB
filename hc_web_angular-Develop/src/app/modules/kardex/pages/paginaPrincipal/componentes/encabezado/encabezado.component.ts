import { CommonModule } from '@angular/common';
import { ChangeDetectionStrategy, Component, Input } from '@angular/core';
import { IDatosEncabezado } from '../../interfaces/IdatosEncabezado';

@Component({
  selector: 'app-encabezado',
  standalone: true,
  imports: [
    CommonModule,
  ],
  templateUrl: './encabezado.component.html',
  styleUrls: ['./encabezado.component.css'],
  changeDetection: ChangeDetectionStrategy.OnPush,
})
export class EncabezadoComponent {

  lcPaciente: string = "Paciente:";
  lcGenero: string = "Género:";
  lcDocumento: string = "Documento:";
  lcIngreso: string ="Ingreso:";
  lcVia:string="Vía:";
  lcPeso:string="Peso";
  lcHabitacion: string="Habitación:";
  lcEdad:string="Edad";
  lcHistoria: string="Historia:";



  @Input() listaDatosEncabezado?: IDatosEncabezado[] = [];

}
